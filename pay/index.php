<?php
/**
 * Notify endpoint cho cong thanh toan (pay callback).
 *
 * Bang no: nguyen ban khong kiem tra gi ca - ai cung Goi duoc
 * GET /pay/index.php?user_id=<uid>&amount=<so> de in ra Ma Thach tuy y.
 *
 * Sau khi va, yeu cau phai co:
 *   - file bi mat  phpstudy_pro/pay_secret.key  (NGOAI webroot, khong phuc vu qua HTTP)
 *   - tham so key=<noi dung file do>
 *   - order_id khong trung (sach doi phat  phpstudy_pro/pay_orders.log)
 *
 * Chua dat file bi mat => moi yeu cau bi tu choi (fail-closed).
 */
require_once "../pdo.php";
require_once "../class/player.php";

$SECRET_FILE = __DIR__ . '/../../pay_secret.key';
$ORDERS_FILE = __DIR__ . '/../../pay_orders.log';

function pay_denied($code) {
    http_response_code(403);
    echo "from choi: " . $code;
    exit;
}

if (!is_file($SECRET_FILE)) {
    pay_denied("chua cau hinh pay_secret.key");
}
$secret = trim(file_get_contents($SECRET_FILE));
if ($secret === '') {
    pay_denied("pay_secret.key rong");
}

$uid = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$amount = isset($_GET['amount']) ? trim($_GET['amount']) : '';
$orderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$key = isset($_GET['key']) ? $_GET['key'] : '';

if (!is_numeric($amount) || floatval($amount) <= 0 || floatval($amount) > 100000) {
    pay_denied("amount khong hop le");
}
if ($uid === '' || $orderId === '' || !preg_match('/^[0-9A-Za-z._-]{1,64}$/', $orderId)) {
    pay_denied("tham so khong hop le");
}
if (!function_exists('hash_equals')) {
    pay_denied("PHP khong ho tro hash_equals");
}
if (!hash_equals($secret, $key)) {
    pay_denied("key sai");
}

$player = \player\getplayer1($uid, $dblj);
if (!$player || empty($player->sid)) {
    pay_denied("nhan vat khong ton tai");
}

$fp = @fopen($ORDERS_FILE, 'a+');
if (!$fp) {
    pay_denied("khong mo duoc sach doi phat");
}
flock($fp, LOCK_EX);
$done = '';
rewind($fp);
while (!feof($fp)) {
    $chunk = fread($fp, 8192);
    if ($chunk === false) { break; }
    $done .= $chunk;
}
if (strpos("\n" . $done, "\n" . $orderId . "\n") !== false) {
    flock($fp, LOCK_UN);
    fclose($fp);
    pay_denied("don hang da xu ly");
}
fseek($fp, 0, SEEK_END);
fwrite($fp, $orderId . "\n");
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

$lt = intval($amount * 10);
\player\changeczb(1, $lt, $player->sid, $dblj);
echo "Nap thanh cong " . $lt;
