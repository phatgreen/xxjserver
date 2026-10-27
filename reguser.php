<?php
include 'pdo.php';

$msg = '';
$popupType = 'error';
$oldUsername = '';

if (!empty($_POST['submit'])) {
    $username  = isset($_POST['username']) ? trim($_POST['username']) : '';
    $userpass  = isset($_POST['userpass']) ? trim($_POST['userpass']) : '';
    $userpass2 = isset($_POST['userpass2']) ? trim($_POST['userpass2']) : '';
    $oldUsername = $username;

    if ($userpass !== $userpass2) {
        $msg = 'Hai lần nhập mật khẩu không giống nhau';
    } elseif (strlen($username) < 6 || strlen($userpass) < 6) {
        $msg = 'Tài khoản và mật khẩu phải có ít nhất 6 ký tự';
    } else {
        $stmt = $dblj->prepare("SELECT username FROM userinfo WHERE username = ? LIMIT 1");
        $stmt->execute(array($username));

        if ($stmt->fetch()) {
            $msg = "Tài khoản $username đã tồn tại";
        } else {
            $token = md5($username . $userpass . time());
            $hashedPass = password_hash($userpass, PASSWORD_BCRYPT);

            $stmt = $dblj->prepare("INSERT INTO userinfo(username,userpass,token) VALUES(?,?,?)");
            $stmt->execute(array($username, $hashedPass, $token));

            $msg = 'Đăng ký thành công - Chuẩn bị chuyển hướng';
            $popupType = 'success';
            header("refresh:3;url=index.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Tu Chân Giới - Đăng ký</title>
    <link rel="stylesheet" href="./chajian/tishikuang/style/dialog.css">
    <link rel="stylesheet" href="css/logincss.css?v=2026070801">
    <script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
    <script src="./chajian/tishikuang/javascript/dialog.min.js"></script>
</head>
<body>

<div class="login-page register-page">
    <div class="brand-block">
        <img src="images/bg/logo.png" class="main-logo" alt="Tu Chân Giới">
        <div class="brand-subtitle">TIÊN HIỆP · TU CHÂN · VẠN GIỚI</div>
    </div>

    <div class="login-panel register-panel">
        <div class="panel-gem"></div>
        <div class="panel-corner corner-lt"></div>
        <div class="panel-corner corner-rt"></div>
        <div class="panel-corner corner-lb"></div>
        <div class="panel-corner corner-rb"></div>

        <div class="panel-title">ĐĂNG KÝ TU CHÂN GIỚI</div>
        <div class="panel-line"><span>KHAI MỞ TIÊN LỘ</span></div>

        <form method="post" class="login-form register-form" autocomplete="off">
            <div class="input-box input-user">
                <input type="text" name="username" placeholder="Tài khoản" class="input" required autocomplete="username" value="<?php echo htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="input-box input-pass">
                <input type="password" name="userpass" placeholder="Mật khẩu" class="input" required autocomplete="new-password">
                <span class="pass-eye">◎</span>
            </div>

            <div class="input-box input-pass input-pass-confirm">
                <input type="password" name="userpass2" placeholder="Nhập lại mật khẩu" class="input" required autocomplete="new-password">
                <span class="pass-eye">◎</span>
            </div>

            <div class="register-note">Tài khoản và mật khẩu tối thiểu 6 ký tự.</div>

            <button type="submit" name="submit" value="1" class="btn-register register-submit"><span>ĐĂNG KÝ</span></button>
            <a href="index.php" class="btn-login register-login-link"><span>ĐĂNG NHẬP</span></a>
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
