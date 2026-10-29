<?php
$selfym = $_SERVER['PHP_SELF'];
$tishi = isset($tishi) ? $tishi : '';

// Trang tao nhan vat chi hop le khi dang nhap bang tai khoan chua co nhan vat (token that).
// Token phải thuộc 1 tài khoản đã đăng ký (userinfo), không chỉ đủ độ dài
$cjTokenOk = false;
if (isset($token) && is_string($token) && strlen($token) >= 32) {
    $cjStmt = $dblj->prepare("SELECT 1 FROM userinfo WHERE token = ? LIMIT 1");
    $cjStmt->execute(array($token));
    $cjTokenOk = (bool)$cjStmt->fetchColumn();
}
if (!$cjTokenOk) {
    echo <<<HTML
<div class="create-character-page">
    <div class="create-message">Phiên đăng nhập không hợp lệ, hãy quay lại đăng nhập lại.</div>
    <a class="create-submit" href="index.php"><span>Về Trang Đăng Nhập</span></a>
</div>
HTML;
    return;
}

$html = <<<HTML
<div class="create-character-page">
    <div class="create-character-hero">
        <img src="./images/rw.png" alt="Tu chân nhập đạo">
        <div class="create-hero-glow"></div>
        <div class="create-character-title">
            <span>Thiên môn đã mở</span>
            <strong>Tạo Nhân Vật</strong>
            <em>Chọn đạo hiệu, định thân phận, bước vào con đường tranh phong.</em>
        </div>
    </div>

    <form class="create-character-form" action="$selfym" method="get" autocomplete="off">
        <input type="hidden" name="cmd" value="cjplayer">
        <input type="hidden" name="token" value="$token">

        <div class="create-form-head">
            <span>Khởi nguyên</span>
            <b>Tân thủ nhập giới</b>
        </div>

        <label class="create-field">
            <span>Đạo hiệu</span>
            <input type="text" name="username" maxlength="12" placeholder="Nhập tên nhân vật" required>
        </label>

        <div class="create-section">
            <div class="create-section-title">Giới tính</div>
            <div class="create-choice-grid two">
                <label class="create-choice">
                    <input type="radio" name="sex" value="1" checked>
                    <span class="choice-icon gender male">♂</span>
                    <b>Nam</b>
                    <em>Kiếm ý cương liệt</em>
                </label>
                <label class="create-choice">
                    <input type="radio" name="sex" value="2">
                    <span class="choice-icon gender female">♀</span>
                    <b>Nữ</b>
                    <em>Linh khí thanh tuyệt</em>
                </label>
            </div>
        </div>

        <div class="create-section">
            <div class="create-section-title">Thân phận</div>
            <div class="create-choice-grid three">
                <label class="create-choice">
                    <input type="radio" name="shenfen" value="1" checked>
                    <span class="choice-icon">⚔</span>
                    <b>Chiến sĩ</b>
                    <em>Cân bằng công thủ</em>
                </label>
                <label class="create-choice">
                    <input type="radio" name="shenfen" value="2">
                    <span class="choice-icon">✦</span>
                    <b>Thiếu hiệp</b>
                    <em>Linh hoạt nhập môn</em>
                </label>
                <label class="create-choice">
                    <input type="radio" name="shenfen" value="3">
                    <span class="choice-icon">☯</span>
                    <b>Ẩn sĩ</b>
                    <em>Tu tâm dưỡng khí</em>
                </label>
            </div>
        </div>

        <div class="create-message">$tishi</div>

        <div class="create-bonus-line">
            <span>✦ Tặng tân thủ tài nguyên nhập môn</span>
            <span>✦ Mở khóa tiên đồ sau khi tạo</span>
        </div>

        <button class="create-submit" type="submit">
            <span>Khởi Tạo Tiên Đồ</span>
        </button>
    </form>
</div>
HTML;

echo $html;
?>
