<?php
include 'pdo.php';
require_once 'class/encode.php';
$encode = new \encode\encode();

$msg = '';
$popupType = 'error';

if (isset($_POST['submit'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $userpass = isset($_POST['userpass']) ? trim($_POST['userpass']) : '';

    if (strlen($username) < 6 || strlen($userpass) < 6) {
        $msg = 'Tài khoản hoặc mật khẩu tối thiểu 6 ký tự';
    } else {
        $sql = 'SELECT username,userpass,token FROM userinfo WHERE username=? LIMIT 1';
        $stmt = $dblj->prepare($sql);
        $stmt->execute(array($username));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $msg = 'Tài khoản hoặc mật khẩu sai';
        } elseif (
            (strpos($user['userpass'], '$2y$') === 0 && password_verify($userpass, $user['userpass']))
            || (strcmp($userpass, $user['userpass']) === 0)
        ) {
            if (strpos($user['userpass'], '$2y$') !== 0) {
                $stmt = $dblj->prepare('UPDATE userinfo SET userpass=? WHERE username=?');
                $stmt->execute(array(password_hash($userpass, PASSWORD_BCRYPT), $username));
            }
            $token = $user['token'];
            $stmt = $dblj->prepare('SELECT sid FROM game1 WHERE token=? LIMIT 1');
            $stmt->execute(array($token));
            $game = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$game) {
                $cmd = 'cmd=cj&token=' . $token;
            } else {
                $sid = $game['sid'];
                $cmd = 'cmd=login&sid=' . $sid;
                $nowdate = date('Y-m-d H:i:s');
                $stmt = $dblj->prepare('UPDATE game1 SET endtime=?,sfzx=1 WHERE sid=?');
                $stmt->execute(array($nowdate, $sid));
            }

            $cmd = $encode->encode($cmd);
            header('refresh:1;url=game.php?cmd=' . $cmd);
            $msg = 'Đăng nhập thành công';
            $popupType = 'success';
        } else {
            $msg = 'Tài khoản hoặc mật khẩu sai';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Tu Chân Giới</title>
    <link rel="stylesheet" href="./chajian/tishikuang/style/dialog.css">
    <link rel="stylesheet" href="css/logincss.css?v=2026070801">
    <script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
    <script src="./chajian/tishikuang/javascript/dialog.min.js"></script>
</head>
<body>

<div class="login-page">
    <div class="brand-block">
        <img src="images/bg/logo.png" class="main-logo" alt="Tu Chân Giới">
		<div class="brand-subtitle">TIÊN HIỆP · TU CHÂN · VẠN GIỚI</div>
    </div>

    <div class="login-panel">
        <div class="panel-gem"></div>
        <div class="panel-corner corner-lt"></div>
        <div class="panel-corner corner-rt"></div>
        <div class="panel-corner corner-lb"></div>
        <div class="panel-corner corner-rb"></div>

        <div class="panel-title">ĐĂNG NHẬP TU CHÂN GIỚI</div>
        <div class="panel-line"><span>NGUYỆT LÃNH THIÊN SƠN</span></div>

        <form method="post" class="login-form" autocomplete="off">
            <div class="input-box input-user">
                <input type="text" name="username" placeholder="Tài khoản" class="input" required autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="input-box input-pass">
                <input type="password" name="userpass" placeholder="Mật khẩu" class="input" required autocomplete="current-password">
                <span class="pass-eye">◉</span>
            </div>

            <div class="login-options">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="javascript:void(0);">Quên mật khẩu?</a>
            </div>

            <button type="submit" name="submit" class="btn-login"><span>ĐĂNG NHẬP</span></button>
            <a href="reguser.php" class="btn-register">ĐĂNG KÝ NGAY</a>
        </form>

        <div class="panel-bottom-gem"></div>
    </div>

    <div class="gift-wrap">
        <div class="giftcode"><i></i> Giftcode: <b>vip666888</b></div>
    </div>

    <div class="feature-bar">
        <div class="feature-item">
            <div class="feature-icon shield-icon"></div>
            <div>
                <strong>BẢO MẬT TUYỆT ĐỐI</strong>
                <p>An toàn thông tin</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon lightning-icon"></div>
            <div>
                <strong>KẾT NỐI ỔN ĐỊNH</strong>
                <p>Trải nghiệm mượt mà</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon community-icon"></div>
            <div>
                <strong>CỘNG ĐỒNG ĐÔNG ĐẢO</strong>
                <p>Kết giao bằng hữu</p>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.pass-eye').forEach(function(eye) {
    eye.setAttribute('role', 'button');
    eye.setAttribute('tabindex', '0');
    eye.setAttribute('title', 'Hiện mật khẩu');

    function togglePassword() {
        var box = eye.closest('.input-box');
        var input = box ? box.querySelector('input') : null;
        if (!input) {
            return;
        }

        var visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        eye.textContent = visible ? '◉' : '◎';
        eye.classList.toggle('is-visible', visible);
        eye.setAttribute('title', visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    }

    eye.addEventListener('click', togglePassword);
    eye.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            togglePassword();
        }
    });
});

<?php if (!empty($msg)) { ?>
popup({
    type:'<?php echo $popupType; ?>',
    msg:'<?php echo addslashes($msg); ?>',
    delay:2000,
    bg:true,
    clickDomCancel:true
});
<?php } ?>
</script>
</body>
</html>