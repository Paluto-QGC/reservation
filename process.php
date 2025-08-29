<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Dotenv\Dotenv;

/* -----------------------------------------------------------------------------
   Bootstrap / Config
----------------------------------------------------------------------------- */
@date_default_timezone_set('Asia/Manila');

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Dotenv\Dotenv;

/** Trim + strip tags */
function clean(string $s): string { return trim(strip_tags($s)); }

/* Load .env only if present locally (Render uses real env vars) */
$envDir  = __DIR__;
$envFile = $envDir.'/.env';
if (is_file($envFile) && is_readable($envFile)) {
    Dotenv::createImmutable($envDir)->load();
} else {
    Dotenv::createImmutable($envDir)->safeLoad();
}

/* Helper to read env reliably on Render (where $_ENV can be empty) */
function envv(string $key, string $default = ''): string {
    $v = $_ENV[$key] ?? getenv($key);
    return ($v !== false && $v !== null && $v !== '') ? (string)$v : $default;
}

$APP_TZ = envv('APP_TZ', 'Asia/Manila');
@date_default_timezone_set($APP_TZ);

$SHEET_ID   = envv('GOOGLE_SHEET_ID');                         // REQUIRED
$SHEET_NAME = envv('GOOGLE_SHEET_NAME', 'UNLI_PALUTO');
$CREDS_PATH = envv('GOOGLE_APPLICATION_CREDENTIALS', 'config/credentials.json');
$BASE_URL   = envv('BASE_URL', '/');

if ($SHEET_ID === '') {
  http_response_code(500);
  exit('Missing GOOGLE_SHEET_ID (set it in Render → Environment).');
}

/* -----------------------------------------------------------------------------
   Validate POST input
----------------------------------------------------------------------------- */
$required = [
  'selectedDate','selectedTime','customerName','phoneNumber','email','adults','kids','agreeTerms'
];
foreach ($required as $k) {
  if (!isset($_POST[$k]) || trim((string)$_POST[$k]) === '') {
    http_response_code(400);
    exit('Please complete all required fields.');
  }
}

// Read + sanitize
$dateRaw     = clean((string)$_POST['selectedDate']); // expected ISO: YYYY-MM-DD
$timeRaw     = clean((string)$_POST['selectedTime']); // expected HH:MM (24h)
$name        = clean((string)$_POST['customerName']);
$phone       = clean((string)$_POST['phoneNumber']);
$email       = strtolower(trim((string)$_POST['email']));
$adults      = (int)$_POST['adults'];
$kids        = (int)$_POST['kids'];
$requests    = isset($_POST['specialRequests']) ? clean((string)$_POST['specialRequests']) : '';

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Please enter a valid email address.');
}
if ($adults < 1 || $adults > 20) {
  http_response_code(400);
  exit('Adults must be between 1 and 20.');
}
if ($kids < 0 || $kids > 10) {
  http_response_code(400);
  exit('Kids must be between 0 and 10.');
}
if (!in_array($timeRaw, $ALLOWED_TIMES, true)) {
  http_response_code(400);
  exit('Please select a valid time slot.');
}

// Parse date (expecting YYYY-MM-DD)
$dt = DateTime::createFromFormat('Y-m-d', $dateRaw, new DateTimeZone($APP_TZ));
if (!$dt) {
  http_response_code(400);
  exit('Invalid date format.');
}
$today = new DateTime('today', new DateTimeZone($APP_TZ));
if ((int)$dt->format('Ymd') < (int)$today->format('Ymd')) {
  http_response_code(400);
  exit('Selected date is in the past.');
}
$dayOfWeek = (int)$dt->format('w'); // 0=Sun, 6=Sat
if (!in_array($dayOfWeek, [0,6], true)) {
  http_response_code(400);
  exit('Reservations are available on weekends only.');
}

// Formatters for display
$prettyDate = $dt->format('l, F j, Y');
$timeObj    = DateTime::createFromFormat('H:i', $timeRaw, new DateTimeZone($APP_TZ));
$prettyTime = $timeObj ? $timeObj->format('g:i A') : $timeRaw;

$totalGuests = $adults + $kids;
$PRICE_ADULT = 599;
$PRICE_KID   = 299; // ages 3–11
$totalAmount = ($adults * $PRICE_ADULT) + ($kids * $PRICE_KID);

/* -----------------------------------------------------------------------------
   Generate Reservation No + QR
----------------------------------------------------------------------------- */
$reservationNo = 'PLT-' . str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

$qrPayload = json_encode([
  'resNo'  => $reservationNo,
  'name'   => $name,
  'email'  => $email,
  'phone'  => $phone,
  'date'   => $dt->format('Y-m-d'),
  'time'   => $timeRaw,
  'guests' => ['adults' => $adults, 'kids' => $kids, 'total' => $totalGuests],
], JSON_UNESCAPED_UNICODE);

$qrUrl    = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=0&data=' . urlencode((string)$qrPayload);
$qrBinary = @file_get_contents($qrUrl);
if ($qrBinary === false) {
  http_response_code(500);
  exit('Failed to generate QR.');
}
$qrDataUri = 'data:image/png;base64,' . base64_encode($qrBinary);
$tmpQrPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $reservationNo . '.png';
file_put_contents($tmpQrPath, $qrBinary);

/* -----------------------------------------------------------------------------
   Append to Google Sheet
----------------------------------------------------------------------------- */
try {
  $client = new GoogleClient();
  $credPathAbs = $CREDS_PATH;
  if (!str_starts_with($credPathAbs, DIRECTORY_SEPARATOR) && !preg_match('~^[A-Za-z]:\\\\~', $credPathAbs)) {
    $credPathAbs = __DIR__ . '/' . $credPathAbs; // make relative to project root
  }
  if (!file_exists($credPathAbs)) {
    throw new RuntimeException('Google credentials file not found at ' . $credPathAbs);
  }

  $client->setAuthConfig($credPathAbs);
  $client->setScopes([GoogleSheets::SPREADSHEETS]);
  $sheets = new GoogleSheets($client);

  // Expected headers in your sheet (row 1):
  // Timestamp | Reservation No | Name | Email | Phone | Date | Time | Adults | Kids | Total Guests | Total Amount | Special Requests | Status/Notes
  $range  = $SHEET_NAME . '!A:M';
  $values = [[
    date('Y-m-d H:i:s'),
    $reservationNo,
    $name,
    $email,
    $phone,
    $dt->format('Y-m-d'),
    $timeRaw,
    $adults,
    $kids,
    $totalGuests,
    $requests,
    'QR sent via email',
  ]];

  $body   = new Google\Service\Sheets\ValueRange(['values' => $values]);
  $params = ['valueInputOption' => 'USER_ENTERED'];
  $sheets->spreadsheets_values->append($SHEET_ID, $range, $body, $params);

} catch (Throwable $e) {
  @unlink($tmpQrPath);
  http_response_code(500);
  exit('Sheet error: ' . $e->getMessage());
}

/* -----------------------------------------------------------------------------
   Send confirmation email
----------------------------------------------------------------------------- */
try {
  $host      = $_ENV['SMTP_HOST'] ?? '';
  $port      = (int)($_ENV['SMTP_PORT'] ?? 587);
  $user      = $_ENV['SMTP_USER'] ?? '';
  $pass      = $_ENV['SMTP_PASS'] ?? '';
  $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? $user;
  $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'PALUTO PH';

  if (!$host || !$user || !$pass) {
    throw new RuntimeException('SMTP settings missing in .env');
  }

  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = $host;
  $mail->SMTPAuth   = true;
  $mail->Username   = $user;
  $mail->Password   = $pass;
  $mail->SMTPSecure = ($port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = $port;

  $mail->setFrom($fromEmail, $fromName);
  $mail->addAddress($email, $name);
  $mail->Subject = "Reservation Confirmed - $reservationNo";

  // Embed + attach QR
  $mail->addEmbeddedImage($tmpQrPath, 'qr', $reservationNo . '.png', 'base64', 'image/png');
  $mail->addAttachment($tmpQrPath, $reservationNo . '.png');

  // Safe values for HTML
  $safeName     = htmlspecialchars($name, ENT_QUOTES);
  $safeEmail    = htmlspecialchars($email, ENT_QUOTES);
  $safePhone    = htmlspecialchars($phone, ENT_QUOTES);
  $safeDate     = htmlspecialchars($prettyDate, ENT_QUOTES);
  $safeTime     = htmlspecialchars($prettyTime, ENT_QUOTES);
  $safeReq      = htmlspecialchars($requests, ENT_QUOTES);

  // Brand-aligned (red/orange) email
  $html = '
  <div style="margin:0;padding:0;background:#fff7ed;font-family:Arial,Helvetica,sans-serif;color:#111827">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#fff7ed">
      <tr>
        <td align="center" style="padding:24px">
          <table width="640" cellpadding="0" cellspacing="0" role="presentation" style="max-width:640px;background:#ffffff;border-radius:16px;border:1px solid #fee2e2;box-shadow:0 10px 30px rgba(249,115,22,.15);overflow:hidden">
            <tr>
              <td style="padding:20px 24px;background:linear-gradient(135deg,#f97316,#dc2626);">
                <table width="100%" role="presentation">
                  <tr>
                    <td style="color:#fff;font-weight:800;font-size:22px;letter-spacing:.3px">Reservation Confirmed</td>
                    <td align="right" style="color:#fff;font-size:12px;opacity:.9">No: <strong>' . $reservationNo . '</strong></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td style="padding:22px 24px 8px 24px">
                <p style="margin:0 0 8px 0;color:#111827;font-size:14px">Hi <strong>' . $safeName . '</strong>,</p>
                <p style="margin:0 0 14px 0;color:#374151;font-size:14px">Your table at <strong>Paluto Seafood Grill & Restaurant</strong> is confirmed. Show this QR upon arrival.</p>
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:0 24px 16px 24px">
                <img src="cid:qr" alt="QR" width="200" height="200" style="display:block;border:1px solid #e5e7eb;border-radius:10px;background:#fff;padding:6px;box-shadow:0 8px 20px rgba(0,0,0,.08)" />
                <div style="font-size:12px;color:#6b7280;margin-top:8px">Present this QR at the entrance</div>
              </td>
            </tr>

            <tr>
              <td style="padding:0 24px 20px 24px">
                <table cellpadding="0" cellspacing="0" role="presentation" width="100%" style="border-collapse:separate;border-spacing:0 8px;font-size:14px">
                  <tr><td style="width:160px;color:#6b7280">Name</td><td style="color:#111827">' . $safeName . '</td></tr>
                  <tr><td style="width:160px;color:#6b7280">Email</td><td style="color:#111827">' . $safeEmail . '</td></tr>
                  <tr><td style="width:160px;color:#6b7280">Phone</td><td style="color:#111827">' . $safePhone . '</td></tr>
                  <tr><td style="width:160px;color:#6b7280">Date</td><td style="color:#111827">' . $safeDate . '</td></tr>
                  <tr><td style="width:160px;color:#6b7280">Time</td><td style="color:#111827">' . $safeTime . '</td></tr>
                  <tr><td style="width:160px;color:#6b7280">Guests</td><td style="color:#111827">' . $adults . ' Adult' . ($adults>1?'s':'') . ($kids>0?(', ' . $kids . ' Child' . ($kids>1?'ren':'') ):'') . ' (Total: ' . $totalGuests . ')</td></tr>
                  ' . ($safeReq !== '' ? '<tr><td style="width:160px;color:#6b7280">Requests</td><td style="color:#111827">' . $safeReq . '</td></tr>' : '') . '
                </table>
              </td>
            </tr>

            <tr>
              <td style="padding:14px 24px 20px 24px;border-top:1px solid #f3f4f6">
                <div style="font-size:12px;color:#6b7280;line-height:1.5">
                  <div><strong>Operating Hours:</strong> Lunch 10:00AM–3:00PM (last order 2:00PM) • Dinner 5:00PM–9:00PM (last order 8:00PM)</div>
                  <div>Adults ₱599 • Kids (3–11) ₱299 • Kids below 3ft: Free</div>
                  <div>Dine-in only • No outside food & drinks • Excessive leftovers are chargeable</div>
                </div>
              </td>
            </tr>

          </table>
        </td>
      </tr>
    </table>
  </div>';

  $mail->isHTML(true);
  $mail->Body    = $html;
  $mail->AltBody = "Reservation No: $reservationNo\nName: $name\nEmail: $email\nPhone: $phone\nDate: $prettyDate\nTime: $prettyTime\nGuests: $adults adult(s)";

  $mail->send();

} catch (Throwable $e) {
  @unlink($tmpQrPath);
  http_response_code(500);
  exit('Email error: ' . $e->getMessage());
}

/* -----------------------------------------------------------------------------
   Cleanup & render confirmation page
----------------------------------------------------------------------------- */
@unlink($tmpQrPath);

$safeName  = htmlspecialchars($name, ENT_QUOTES);
$safeEmail = htmlspecialchars($email, ENT_QUOTES);
$safePhone = htmlspecialchars($phone, ENT_QUOTES);
$safeReq   = htmlspecialchars($requests, ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reservation Confirmed • <?php echo $reservationNo; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="https://i.ibb.co/0RPhcmb2/logo-pal.png" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body{font-family:'Inter',sans-serif}
    .bg-sunrise{background:linear-gradient(135deg,#fff7ed 0%,#ffe4e6 100%)}
  </style>
</head>
<body class="bg-sunrise min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-3xl">
    <div class="bg-white rounded-2xl shadow-2xl border border-orange-200 overflow-hidden">
      <div class="bg-orange-400 p-6 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img src="https://i.ibb.co/0RPhcmb2/logo-pal.png" class="w-10 h-10" onerror="this.style.display='none'"/>
          <h1 class="text-2xl font-extrabold tracking-tight">Reservation Confirmed</h1>
        </div>
        <div class="text-sm">No: <span class="font-bold"><?php echo $reservationNo; ?></span></div>
      </div>

      <div class="p-6 grid md:grid-cols-2 gap-6">
        <!-- QR + tip -->
        <div class="text-center">
          <img src="<?php echo $qrDataUri; ?>" alt="QR Code" class="mx-auto w-48 h-48 rounded-xl border border-gray-200 p-2 bg-white shadow" />
          <p class="text-xs text-gray-500 mt-2">Show this QR at the entrance</p>
        </div>

        <!-- Details -->
        <div>
          <div class="grid grid-cols-3 gap-x-4 gap-y-2 text-sm">
            <div class="text-gray-500">Name</div><div class="col-span-2 font-medium"><?php echo $safeName; ?></div>
            <div class="text-gray-500">Email</div><div class="col-span-2 font-medium break-all"><?php echo $safeEmail; ?></div>
            <div class="text-gray-500">Phone</div><div class="col-span-2 font-medium"><?php echo $safePhone; ?></div>
            <div class="text-gray-500">Date</div><div class="col-span-2 font-medium"><?php echo $prettyDate; ?></div>
            <div class="text-gray-500">Time</div><div class="col-span-2 font-medium"><?php echo $prettyTime; ?></div>
            <div class="text-gray-500">Guests</div><div class="col-span-2 font-medium"><?php echo $adults; ?> Adult<?php echo $adults>1?'s':''; ?><?php echo $kids>0?', '.$kids.' Child'.($kids>1?'ren':''):''; ?> (Total: <?php echo $totalGuests; ?>)</div>
            <?php if ($safeReq !== ''): ?>
              <div class="text-gray-500">Requests</div><div class="col-span-2 font-medium"><?php echo $safeReq; ?></div>
            <?php endif; ?>
          </div>

          <div class="mt-4 text-xs text-gray-500">
            Lunch 10:00AM–3:00PM (last order 2:00PM) • Dinner 5:00PM–9:00PM (last order 8:00PM) • Dine-in only • No outside food & drinks • Excessive leftovers are chargeable
          </div>
        </div>
      </div>

      <div class="px-6 pb-6 flex flex-wrap items-center justify-center gap-3">
        <a href="<?php echo htmlspecialchars($BASE_URL, ENT_QUOTES); ?>" class="px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold">Back to Reservation</a>
        <a href="<?php echo $qrDataUri; ?>" download="<?php echo $reservationNo; ?>.png" class="px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold">Download QR</a>
        <a href="mailto:<?php echo $safeEmail; ?>?subject=Reservation%20<?php echo urlencode($reservationNo); ?>" class="px-5 py-3 rounded-xl bg-white border text-gray-800 hover:bg-gray-50 font-semibold">Open Mail App</a>
      </div>
    </div>
  </div>
</body>
</html>
