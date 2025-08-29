<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Dotenv\Dotenv;

@date_default_timezone_set($_ENV['APP_TZ'] ?? 'Asia/Manila');
header('Content-Type: application/json; charset=utf-8');

try {
  // Load .env locally; on Render we mostly rely on real env vars.
  if (is_file(__DIR__.'/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
  }

  $SHEET_ID   = $_ENV['GOOGLE_SHEET_ID'] ?? '';
  $SHEET_NAME = $_ENV['GOOGLE_SHEET_NAME'] ?? 'UNLI_PALUTO';
  if (!$SHEET_ID) throw new RuntimeException('Missing GOOGLE_SHEET_ID');

  // ---- read "code" from JSON / GET / POST (also accept resNo) ----
  $raw  = file_get_contents('php://input') ?: '';
  $body = json_decode($raw, true);
  $code = '';
  if (is_array($body) && isset($body['code']))      $code = trim((string)$body['code']);
  elseif (isset($_GET['code']))                     $code = trim((string)$_GET['code']);
  elseif (isset($_POST['code']))                    $code = trim((string)$_POST['code']);
  if ($code === '' && is_array($body) && isset($body['resNo'])) $code = trim((string)$body['resNo']);
  if ($code === '') { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Missing reservation code']); exit; }

  if (preg_match('~^\d{6}$~', $code)) $code = 'PLT-'.$code;
  $code = strtoupper($code);

  // ---- Google client: prefer JSON env, else file path ----
  $client = new GoogleClient();
  $credsJson = $_ENV['GOOGLE_APPLICATION_CREDENTIALS_JSON'] ?? '';
  if (trim($credsJson) !== '') {
    $data = json_decode($credsJson, true);
    if (!is_array($data)) throw new RuntimeException('GOOGLE_APPLICATION_CREDENTIALS_JSON is not valid JSON.');
    $client->setAuthConfig($data);
  } else {
    $credPathAbs = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? 'config/credentials.json';
    if (!preg_match('~^([A-Za-z]:\\\\|/).+~', $credPathAbs)) $credPathAbs = __DIR__.'/'.$credPathAbs;
    if (!is_readable($credPathAbs)) throw new RuntimeException('Google credentials file not readable at '.$credPathAbs);
    $client->setAuthConfig($credPathAbs);
  }
  $client->setScopes([GoogleSheets::SPREADSHEETS]);
  $sheets = new GoogleSheets($client);

  // ---- Pull A:Z and map headers ----
  $resp    = $sheets->spreadsheets_values->get($SHEET_ID, $SHEET_NAME.'!A1:Z');
  $values  = $resp->getValues() ?? [];
  if (!$values) throw new RuntimeException('Sheet has no data');

  $headers = $values[0];
  $rows    = array_slice($values, 1);

  // Debug to Render logs
  error_log('[checkin] Raw headers: ' . json_encode($headers));

  $norm = static fn(string $s): string => strtolower(preg_replace('/[^a-z0-9]+/','', $s));
  $idx = [];
  foreach ($headers as $i => $h) { $idx[$norm((string)$h)] = $i; }

  // Debug normalized keys
  error_log('[checkin] Normalized header map: ' . json_encode($idx));

  // Accept common synonyms for the code column
  $colCode   = $idx['code']
            ?? $idx['reservationno']
            ?? $idx['resno']
            ?? $idx['reservationnumber']
            ?? null;

  $colName   = $idx['name']           ?? null;
  $colEmail  = $idx['email']          ?? null;
  $colPhone  = $idx['phone']          ?? $idx['phonenumber'] ?? null;
  $colDate   = $idx['date']           ?? null;
  $colTime   = $idx['time']           ?? null;
  $colAdult  = $idx['adult']          ?? $idx['adults'] ?? null;
  $colKid    = $idx['kid']            ?? $idx['kids']   ?? $idx['children'] ?? null;
  $colTotal  = $idx['totalpax']       ?? $idx['totalguests'] ?? $idx['total'] ?? null;
  $colNotes  = $idx['statusnotes']    ?? $idx['status'] ?? $idx['notes'] ?? null;

  $colChecked = $idx['checkedin']     ?? $idx['checked'] ?? null;
  $colChkTime = $idx['checkintime']   ?? $idx['checkedtime'] ?? $idx['timechecked'] ?? $idx['checkedinat'] ?? null;

  if ($colCode === null) throw new RuntimeException('No "Code" column in header row');

  // ---- Find row by Code ----
  $rowIndex = null; $rowData = null;
  foreach ($rows as $i => $row) {
    $val = isset($row[$colCode]) ? strtoupper(trim((string)$row[$colCode])) : '';
    if ($val === $code) { $rowIndex = $i + 2; $rowData = $row; break; } // +2: header + 1-index
  }
  if (!$rowIndex) { echo json_encode(['status'=>'error','message'=>'Reservation code not found.']); exit; }

  $rowData = array_pad($rowData, count($headers), '');

  // ---- Details for UI ----
  $name   = $colName  !== null ? trim((string)$rowData[$colName])  : '';
  $email  = $colEmail !== null ? trim((string)$rowData[$colEmail]) : '';
  $phone  = $colPhone !== null ? trim((string)$rowData[$colPhone]) : '';
  $date   = $colDate  !== null ? trim((string)$rowData[$colDate])  : '';
  $time   = $colTime  !== null ? trim((string)$rowData[$colTime])  : '';
  $adults = $colAdult !== null ? (int)$rowData[$colAdult] : 0;
  $kids   = $colKid   !== null ? (int)$rowData[$colKid]   : 0;
  $total  = $colTotal !== null ? (int)$rowData[$colTotal] : ($adults + $kids);

  // ---- Already checked? ----
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
      'status'=>'ok','already'=>true,'checkedInAt'=>$prevAt,
      'code'=>$code,'name'=>$name,'email'=>$email,'phone'=>$phone,
      'date'=>$date,'time'=>$time,
      'guests'=>trim($adults.' Adult'.($adults>1?'s':'').($kids>0?(', '.$kids.' Child'.($kids>1?'ren':'') ):'').' • Total: '.$total),
    ]);
    exit;
  }

  // ---- Mark checked-in ----
  $now  = date('Y-m-d H:i:s');
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
    echo json_encode(['status'=>'error','message'=>'Add "Checked In" + "Check-in Time" or a "Status" column to store check-ins.']);
    exit;
  }

  echo json_encode([
    'status'=>'ok','already'=>false,'checkedInAt'=>$now,
    'code'=>$code,'name'=>$name,'email'=>$email,'phone'=>$phone,
    'date'=>$date,'time'=>$time,
    'guests'=>trim($adults.' Adult'.($adults>1?'s':'').($kids>0?(', '.$kids.' Child'.($kids>1?'ren':'') ):'').' • Total: '.$total),
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
