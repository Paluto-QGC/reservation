<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Dotenv\Dotenv;

@date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json; charset=utf-8');

try {
  // .env (same folder as this file)
    use Dotenv\Dotenv;

    $envDir = __DIR__;                 // adjust if file lives in subfolder
    $envPath = $envDir . '/.env';
    if (is_readable($envPath)) {
        Dotenv::createImmutable($envDir)->load();
    }

  $SHEET_ID   = $_ENV['GOOGLE_SHEET_ID'] ?? '';
  $SHEET_NAME = $_ENV['GOOGLE_SHEET_NAME'] ?? 'UNLI_PALUTO';
  $CREDS_PATH = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? 'config/credentials.json';
  if (!$SHEET_ID) throw new RuntimeException('Missing GOOGLE_SHEET_ID');

  // read "code" from JSON or GET/POST
  $raw = file_get_contents('php://input') ?: '';
  $body = json_decode($raw, true);
  $code = '';
  if (is_array($body) && isset($body['code']))      $code = trim((string)$body['code']);
  elseif (isset($_GET['code']))                     $code = trim((string)$_GET['code']);
  elseif (isset($_POST['code']))                    $code = trim((string)$_POST['code']);
  // accept {"resNo":"PLT-123456"} too
  if ($code === '' && is_array($body) && isset($body['resNo'])) $code = trim((string)$body['resNo']);
  if ($code === '') { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Missing reservation code']); exit; }

  // normalize to PLT-###### if digits only
  if (preg_match('~^\d{6}$~', $code)) $code = 'PLT-'.$code;
  $code = strtoupper($code);

  // Google client (same creds path as your submit script)
  $credPathAbs = $CREDS_PATH;
  if (!preg_match('~^([A-Za-z]:\\\\|/).+~', $credPathAbs)) $credPathAbs = __DIR__.'/'.$credPathAbs;
  if (!file_exists($credPathAbs)) throw new RuntimeException('Credentials not found at '.$credPathAbs);

  $client = new GoogleClient();
  $client->setAuthConfig($credPathAbs);
  $client->setScopes([GoogleSheets::SPREADSHEETS]);
  $sheets = new GoogleSheets($client);

  // Load all rows (A..Z) so we can match by header names
  $resp = $sheets->spreadsheets_values->get($SHEET_ID, $SHEET_NAME.'!A1:Z');
  $values = $resp->getValues() ?? [];
  if (!$values) throw new RuntimeException('Sheet has no data.');

  $headers = $values[0];
  $rows    = array_slice($values, 1);

  // normalize header -> index
  $norm = static function(string $s): string { return strtolower(preg_replace('/[^a-z0-9]+/','', $s)); };
  $idx = [];
  foreach ($headers as $i => $h) $idx[$norm((string)$h)] = $i;

  // columns (use synonyms that match your screenshot)
  $colCode   = $idx['code']        ?? $idx['reservationno'] ?? $idx['resno'] ?? null;
  $colName   = $idx['name']        ?? null;
  $colEmail  = $idx['email']       ?? null;
  $colPhone  = $idx['phone']       ?? $idx['phonenumber'] ?? null;
  $colDate   = $idx['date']        ?? null;
  $colTime   = $idx['time']        ?? null;
  $colAdult  = $idx['adult']       ?? $idx['adults'] ?? null;
  $colKid    = $idx['kid']         ?? $idx['kids'] ?? $idx['children'] ?? null;
  $colTotal  = $idx['totalpax']    ?? $idx['totalguests'] ?? $idx['total'] ?? null;
  $colNotes  = $idx['statusnotes'] ?? $idx['status'] ?? $idx['notes'] ?? null;

  // checked columns — include your exact labels:
  $colChecked = $idx['checkedin']   ?? $idx['checked'] ?? null;     // "Checked In"
  $colChkTime = $idx['checkintime'] ?? $idx['checkedtime'] ?? $idx['timechecked'] ?? $idx['checkedinat'] ?? null; // "Check-in Time"

  if ($colCode === null) throw new RuntimeException('No "Code" column in header row.');

  // find row by Code
  $rowIndex = null; $rowData = null;
  foreach ($rows as $i => $row) {
    $val = isset($row[$colCode]) ? strtoupper(trim((string)$row[$colCode])) : '';
    if ($val === $code) { $rowIndex = $i + 2; $rowData = $row; break; } // +2: header + 1-index
  }
  if (!$rowIndex) { echo json_encode(['status'=>'error','message'=>'Reservation code not found.']); exit; }

  $rowData = array_pad($rowData, count($headers), '');

  // details for UI
  $name   = $colName  !== null ? trim((string)$rowData[$colName])  : '';
  $email  = $colEmail !== null ? trim((string)$rowData[$colEmail]) : '';
  $phone  = $colPhone !== null ? trim((string)$rowData[$colPhone]) : '';
  $date   = $colDate  !== null ? trim((string)$rowData[$colDate])  : '';
  $time   = $colTime  !== null ? trim((string)$rowData[$colTime])  : '';
  $adults = $colAdult !== null ? (int)$rowData[$colAdult] : 0;
  $kids   = $colKid   !== null ? (int)$rowData[$colKid]   : 0;
  $total  = $colTotal !== null ? (int)$rowData[$colTotal] : ($adults + $kids);

  // already checked?
  $already=false; $prevAt='';
  if ($colChecked !== null) {
    $already = strtoupper(trim((string)($rowData[$colChecked] ?? ''))) === 'YES';
    if ($already && $colChkTime !== null) $prevAt = trim((string)($rowData[$colChkTime] ?? ''));
  } elseif ($colNotes !== null) {
    $notesVal = strtoupper((string)($rowData[$colNotes] ?? ''));
    if (strpos($notesVal,'CHECKED') !== false) $already = true;
  }

  if ($already) {
    echo json_encode([
      'status'      => 'ok',
      'already'     => true,
      'checkedInAt' => $prevAt,
      'code'        => $code,
      'name'        => $name,
      'email'       => $email,
      'phone'       => $phone,
      'date'        => $date,
      'time'        => $time,
      'guests'      => trim($adults.' Adult'.($adults>1?'s':'').($kids>0?(', '.$kids.' Child'.($kids>1?'ren':'') ):'').' • Total: '.$total),
    ]);
    exit;
  }

  // write check-in (prefer Checked In + Check-in Time)
  $now = date('Y-m-d H:i:s');
  $toA1 = static function(int $n): string { $s=''; $n+=1; while($n>0){ $m=($n-1)%26; $s=chr(65+$m).$s; $n=intdiv($n-1,26);} return $s; };

  if ($colChecked !== null && $colChkTime !== null) {
    $startCol = min($colChecked, $colChkTime);
    $endCol   = max($colChecked, $colChkTime);
    $range = $SHEET_NAME.'!'.$toA1($startCol).$rowIndex.':'.$toA1($endCol).$rowIndex;
    $vals  = array_fill(0, $endCol-$startCol+1, '');
    $vals[$colChecked - $startCol] = 'YES';
    $vals[$colChkTime - $startCol] = $now;
    $body = new Google\Service\Sheets\ValueRange(['values' => [ $vals ]]);
    $sheets->spreadsheets_values->update($SHEET_ID, $range, $body, ['valueInputOption'=>'USER_ENTERED']);
  } elseif ($colChecked !== null) {
    // at least set the flag
    $range = $SHEET_NAME.'!'.$toA1($colChecked).$rowIndex.':'.$toA1($colChecked).$rowIndex;
    $body  = new Google\Service\Sheets\ValueRange(['values' => [[ 'YES' ]]]);
    $sheets->spreadsheets_values->update($SHEET_ID, $range, $body, ['valueInputOption'=>'USER_ENTERED']);
    if ($colNotes !== null) {
      $existing = (string)($rowData[$colNotes] ?? '');
      $note = trim($existing.(strlen($existing)?' • ':'').'Checked at '.$now);
      $rangeN = $SHEET_NAME.'!'.$toA1($colNotes).$rowIndex.':'.$toA1($colNotes).$rowIndex;
      $bodyN  = new Google\Service\Sheets\ValueRange(['values' => [[ $note ]]]);
      $sheets->spreadsheets_values->update($SHEET_ID, $rangeN, $bodyN, ['valueInputOption'=>'USER_ENTERED']);
    }
  } elseif ($colNotes !== null) {
    $existing = (string)($rowData[$colNotes] ?? '');
    $note = trim($existing.(strlen($existing)?' • ':'').'Checked at '.$now);
    $range = $SHEET_NAME.'!'.$toA1($colNotes).$rowIndex.':'.$toA1($colNotes).$rowIndex;
    $body  = new Google\Service\Sheets\ValueRange(['values' => [[ $note ]]]);
    $sheets->spreadsheets_values->update($SHEET_ID, $range, $body, ['valueInputOption'=>'USER_ENTERED']);
  } else {
    echo json_encode(['status'=>'error','message'=>'Add "Checked In" and "Check-in Time" or a "Status" column to store check-ins.']);
    exit;
  }

  echo json_encode([
    'status'      => 'ok',
    'already'     => false,
    'checkedInAt' => $now,
    'code'        => $code,
    'name'        => $name,
    'email'       => $email,
    'phone'       => $phone,
    'date'        => $date,
    'time'        => $time,
    'guests'      => trim($adults.' Adult'.($adults>1?'s':'').($kids>0?(', '.$kids.' Child'.($kids>1?'ren':'') ):'').' • Total: '.$total),
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
