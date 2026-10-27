<?php
/* Đổi tên nhân vật - giao diện mới */

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$gm = $encode->encode("cmd=czbgm&canshu2=gaiming2&sid=$sid");

$czb = intval($player->uczb);
$vip = intval($player->vip);
$czbhtml = '';

if (!function_exists('czbgm_e')) {
    function czbgm_e($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('czbgm_message')) {
    function czbgm_message($type, $title, $text)
    {
        $icon = ($type == 'success') ? '✓' : (($type == 'error') ? '!' : 'i');
        return '
        <div class="gm-message '.$type.'">
            <div class="gm-message-icon">'.$icon.'</div>
            <div>
                <b>'.$title.'</b>
                <span>'.$text.'</span>
            </div>
        </div>';
    }
}

if (isset($canshu2)) {
    switch ($canshu2) {
        case "gaiming2":
            if ($vip < 8) {
                $czbhtml = czbgm_message(
                    'error',
                    'Chưa đủ điều kiện đổi tên',
                    'SVIP hiện tại: '.$vip.'. Cần đạt VIP8 mới có thể đổi tên.'
                );
                break;
            }

            $czbhtml = '
            <div class="gm-form-card">
                <div class="gm-form-title">Mai danh ẩn tích</div>
                <div class="gm-form-desc">
                    Đổi pháp danh để bắt đầu một thân phận mới. Tên mới cần từ <b>6 đến 12 ký tự</b>.
                </div>

                <form method="get" class="gm-form">
                    <input type="hidden" name="cmd" value="czbgm">
                    <input type="hidden" name="uid" value="'.czbgm_e($uid).'">
                    <input type="hidden" name="sid" value="'.czbgm_e($sid).'">
                    <input type="hidden" name="canshu2" value="gm">

                    <label>Tên mới</label>
                    <input class="gm-input" name="gaibian" maxlength="12" placeholder="Nhập tên mới...">

                    <button class="gm-submit" type="submit">Đổi tên - 200 Ma thạch</button>
                </form>
            </div>';
            break;

        case "gm":
            $gaibian = isset($gaibian) ? trim($gaibian) : '';
            $gaibian_safe = htmlspecialchars($gaibian, ENT_QUOTES, 'UTF-8');

            if ($czb < 200) {
                $czbhtml = czbgm_message('error', 'Ma thạch không đủ', 'Cần 200 Ma thạch để đổi tên.');
                break;
            }

            if (strlen($gaibian) < 6 || strlen($gaibian) > 12) {
                $czbhtml = czbgm_message('error', 'Tên không hợp lệ', 'Tên mới cần từ 6 đến 12 ký tự.');
                break;
            }

            if ($player->uczb >= 200) {
                $ret1 = \player\changeczb(2, 200, $sid, $dblj);

                $stmt = $dblj->prepare("UPDATE game1 SET uname = :uname WHERE sid = :sid");
                $ret = $stmt->execute(array(':uname' => $gaibian_safe, ':sid' => $sid));

                if ($ret) {
                    $czbhtml = czbgm_message('success', 'Đổi tên thành công', 'Pháp danh mới: '.$gaibian_safe);
                    $player = \player\getplayer($sid, $dblj);
                    $czb = intval($player->uczb);
                } else {
                    $czbhtml = czbgm_message('error', 'Đổi tên thất bại', 'Không thể cập nhật tên nhân vật.');
                }
            } else {
                $czbhtml = czbgm_message('error', 'Ma thạch không đủ', 'Cần 200 Ma thạch để đổi tên.');
            }
            break;
    }
}

if ($czbhtml == '') {
    $czbhtml = '
    <div class="gm-info-card">
        <div class="gm-info-title">Quy tắc đổi tên</div>
        <div class="gm-rule-list">
            <div><span>Điều kiện</span><b>VIP8</b></div>
            <div><span>Tiêu hao</span><b>200 Ma thạch</b></div>
            <div><span>Độ dài tên</span><b>6 - 12 ký tự</b></div>
        </div>
        <a class="gm-primary-link" href="?cmd='.$gm.'">Tiến hành đổi tên</a>
    </div>';
}

$displayName = czbgm_e($player->uname);
$html = <<<HTML
<div class="gm-page">
    <div class="gm-hero">
        <img src="./images/gaiming.png" alt="Đổi tên">
        <div class="gm-hero-mask"></div>
        <div class="gm-hero-content">
            <span>Danh tính tu sĩ</span>
            <h2>Đổi Tên Nhân Vật</h2>
            <p>Mai danh ẩn tích, tái nhập tu chân giới với pháp danh mới.</p>
        </div>
    </div>

    <div class="gm-stats">
        <div>
            <span>Nhân vật</span>
            <b>{$displayName}</b>
        </div>
        <div>
            <span>VIP hiện tại</span>
            <b>VIP {$vip}</b>
        </div>
        <div>
            <span>Ma thạch</span>
            <b>{$czb}</b>
        </div>
    </div>

    {$czbhtml}

    <div class="gm-actions">
        <a href="#" onClick="javascript:history.back(-1);">← Trở về</a>
        <a href="game.php?cmd={$gonowmid}">Đại sảnh →</a>
    </div>
</div>
HTML;

echo $html;
?>
