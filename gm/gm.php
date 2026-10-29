<?php
/**
 * gm/gm.php — Trang GM: Nhân vật · Phát quà · Giftcode · Vật phẩm · Nhật ký.
 * Đăng nhập bằng mã GM (GM_CODE_HASH trong gm/config.php), phiên tự thoát sau 2 giờ, mọi thao tác ghi bảng gm_log.
 * Đổi mã GM: php -r "echo password_hash('ma_moi', PASSWORD_BCRYPT);" rồi dán vào gm/config.php.
 */
define('GM_PAGE', 1);
require __DIR__ . '/lib.php';

$tab = gm_req('tab', 'player');
$tabs = array('player' => '👤 Nhân vật', 'gift' => '🎁 Phát quà', 'code' => '🎟 Giftcode', 'items' => '📦 Vật phẩm', 'log' => '📜 Nhật ký');
if (!isset($tabs[$tab])) $tab = 'player';
$flash = array();   // array(loại ok|bad, nội dung)

if (gm_req('act') === 'logout') { gm_log($db, 'logout', '', ''); $_SESSION = array(); session_destroy(); header('Location: gm.php'); exit; }
if (!gm_logged_in()) {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gmcode'])) {
        if (gm_login($db, trim((string)$_POST['gmcode']))) { header('Location: gm.php'); exit; }
        $err = 'Mã GM sai.';
    }
    gm_page_head('Đăng nhập GM');
    echo '<form method="post" class="card login"><h1>⚙ Trang GM — Tu Chân Giới</h1>'
        . ($err !== '' ? '<div class="flash bad">' . gm_h($err) . '</div>' : '')
        . '<label>Mã GM<input type="password" name="gmcode" autofocus autocomplete="current-password"></label>'
        . '<button class="btn" type="submit">Đăng nhập</button></form>';
    gm_page_foot();
    exit;
}

// Mọi POST phải có CSRF đúng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !gm_csrf_ok()) {
    $flash[] = array('bad', 'Phiên làm việc hết hạn hoặc form không hợp lệ — tải lại trang rồi thử lại.');
    // bỏ hẳn dữ liệu POST: các tab đọc tham số qua $_REQUEST (gm_req) và kiểm REQUEST_METHOD
    $_POST = array();
    $_REQUEST = $_GET;
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

ob_start();
require __DIR__ . '/tab_' . $tab . '.php';
$body = ob_get_clean();

gm_page_head($tabs[$tab]);
echo '<header class="top"><b>⚙ GM Tu Chân Giới</b><nav>';
foreach ($tabs as $k => $label) echo '<a class="' . ($k === $tab ? 'on' : '') . '" href="' . gm_url(array('tab' => $k)) . '">' . $label . '</a>';
echo '</nav><a class="out" href="' . gm_url(array('act' => 'logout')) . '">Đăng xuất</a></header><main>';
foreach ($flash as $f) echo '<div class="flash ' . $f[0] . '">' . $f[1] . '</div>';
echo $body . '</main>';
echo gm_datalists();
gm_page_foot();

function gm_page_head($title) {
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . gm_h(strip_tags($title)) . ' · GM</title><style>
:root{--bg:#0f1420;--card:#171e2e;--line:#2a3550;--txt:#e6ebf5;--mut:#93a0bb;--acc:#4f8cff;--ok:#2fbf71;--bad:#e5534b;--warn:#d9a33f}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--txt);font:14px/1.45 system-ui,Segoe UI,Arial,sans-serif}
a{color:#8fb4ff;text-decoration:none}a:hover{text-decoration:underline}
.top{display:flex;gap:16px;align-items:center;flex-wrap:wrap;padding:10px 16px;background:#121a2b;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:5}
.top nav{display:flex;gap:4px;flex-wrap:wrap;flex:1}.top nav a{padding:6px 12px;border-radius:6px;color:var(--txt)}.top nav a.on{background:var(--acc);color:#fff}
.top .out{color:var(--mut)}main{max-width:1280px;margin:0 auto;padding:16px}
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:14px 16px;margin-bottom:14px}
.card h2{margin:0 0 10px;font-size:16px}.card h3{margin:12px 0 6px;font-size:14px;color:var(--mut)}
.login{max-width:360px;margin:12vh auto;display:flex;flex-direction:column;gap:12px}.login h1{font-size:18px;margin:0}
label{display:flex;flex-direction:column;gap:4px;color:var(--mut);font-size:12px}
input,select,textarea{background:#0d1322;color:var(--txt);border:1px solid var(--line);border-radius:6px;padding:7px 9px;font:inherit;min-width:0}
textarea{min-height:70px;width:100%}input:focus,select:focus,textarea:focus{outline:2px solid var(--acc);border-color:transparent}
.btn{background:var(--acc);color:#fff;border:0;border-radius:6px;padding:8px 14px;font:inherit;cursor:pointer;font-weight:600}
.btn.ghost{background:transparent;border:1px solid var(--line);color:var(--mut);font-weight:400;padding:5px 10px}
.btn.red{background:var(--bad)}.btn.green{background:var(--ok)}.btn.sm{padding:4px 9px;font-size:12px}
.grid{display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(170px,1fr))}
.flex{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end}
.flash{padding:9px 12px;border-radius:8px;margin-bottom:12px;border:1px solid}.flash.ok{background:#12301f;border-color:#1f6b3f}.flash.bad{background:#3a1717;border-color:#7a2a2a}
table{width:100%;border-collapse:collapse;font-size:13px}th,td{padding:6px 8px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
th{color:var(--mut);font-weight:600;background:#131a29;position:sticky;top:0;z-index:1}tr:hover td{background:#1b2337}
.tbl{overflow:auto;max-height:70vh;border:1px solid var(--line);border-radius:8px}
.tag{display:inline-block;padding:1px 7px;border-radius:10px;font-size:11px;border:1px solid var(--line);color:var(--mut);margin:1px 2px 1px 0}
.tag.red{border-color:#7a2a2a;color:#ff9b95}.tag.green{border-color:#1f6b3f;color:#7fe0a8}.tag.yel{border-color:#7a6224;color:#f1c96a}
.mut{color:var(--mut)}.num{text-align:right;font-variant-numeric:tabular-nums}
.rows{display:flex;flex-direction:column;gap:6px}.rows .row{display:flex;gap:6px}.rows .pick{flex:1}.rows .cnt{width:90px}
.kv{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:6px}.kv div{background:#121a2b;border-radius:6px;padding:6px 8px}.kv b{display:block;font-size:15px}
.pager{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}.pager a,.pager span{padding:4px 10px;border:1px solid var(--line);border-radius:6px}.pager span{background:var(--acc);color:#fff}
</style></head><body>';
}
function gm_page_foot() {
    // "+ dòng": nhân bản dòng cuối của ô danh sách thưởng
    echo '<script>document.addEventListener("click",function(e){var b=e.target;if(!b.classList||!b.classList.contains("addrow"))return;'
        . 'var box=b.parentNode,rows=box.querySelectorAll(".row"),last=rows[rows.length-1],c=last.cloneNode(true);'
        . 'c.querySelectorAll("input").forEach(function(i){i.value="";});box.insertBefore(c,b);});'
        . 'document.addEventListener("submit",function(e){var m=e.target.getAttribute("data-confirm");if(m&&!confirm(m))e.preventDefault();});</script></body></html>';
}
