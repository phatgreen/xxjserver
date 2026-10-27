<?php
/**
 * Trang đổi Giftcode
 * Giao diện đã được làm lại để đồng bộ phong cách Tu Chân Giới.
 * Logic nhập code đã tối ưu:
 * - Chuẩn hóa mã nhập: trim + lowercase.
 * - Không dùng htmlspecialchars cho biến xử lý logic, chỉ escape khi hiển thị.
 * - Mọi Giftcode đều ghi log theo sid + dhm để mỗi nhân vật chỉ nhận 1 lần.
 * - Chống double click / refresh bằng UNIQUE KEY + INSERT IGNORE.
 * - Bảng duihuan_log đã tạo sẵn ngoài DB, file này không tự CREATE TABLE nữa.
 * - Toàn bộ trạng thái đã nhận chỉ kiểm tra bằng duihuan_log, không dùng dhvip/dhvip1 nữa.
 * - SQL dùng prepare thay vì nối chuỗi sid.
 */

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid={$player->nowmid}&sid={$sid}");

$tishi = '';
$dhmRaw = isset($dhm) ? trim($dhm) : '';
$dhm = strtolower($dhmRaw);
$dhmSafe = htmlspecialchars($dhmRaw, ENT_QUOTES, 'UTF-8');

/**
 * Escape text khi in ra HTML.
 */
function gift_html($text)
{
    return htmlspecialchars(strval($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo khung thông báo lỗi.
 */
function gift_error($msg)
{
    return '<div class="gift-alert gift-alert-error">' . gift_html($msg) . '</div>';
}

/**
 * Đánh dấu đã nhận Giftcode.
 * Dùng INSERT IGNORE + UNIQUE(sid, dhm) để chống refresh / double click nhận lặp.
 */
function gift_mark_code_used($sid, $dhm, $dblj, &$tishi)
{
    $stmt = $dblj->prepare("INSERT IGNORE INTO duihuan_log (sid, dhm, created_at) VALUES (:sid, :dhm, :created_at)");
    $stmt->execute(array(
        ':sid' => $sid,
        ':dhm' => $dhm,
        ':created_at' => time()
    ));

    if ($stmt->rowCount() <= 0) {
        $tishi = gift_error('Code đã được sử dụng!');
        return false;
    }

    return true;
}

/**
 * Phát thưởng từ cấu hình duihuan.
 */
function gift_send_rewards($sid, $duihuan, $dblj)
{
    $rewardHtml = '';

    // Trang bị.
    $retallzb = explode(',', strval($duihuan->dhzb));
    foreach ($retallzb as $zb) {
        $zb = intval($zb);
        if ($zb > 0) {
            \player\addzb($sid, $zb, $dblj);
            $zhuangbei = \player\getzbkzb($zb, $dblj);
            $zbname = isset($zhuangbei->zbname) ? $zhuangbei->zbname : ('Trang bị #' . $zb);
            $rewardHtml .= '<li><span>Trang bị</span><b>' . gift_html($zbname) . '</b></li>';
        }
    }

    // Đạo cụ.
    $djitem = explode(',', strval($duihuan->dhdj));
    foreach ($djitem as $djinfo) {
        $djinfo = trim($djinfo);
        if ($djinfo !== '') {
            $dj = explode('|', $djinfo);
            $djid = isset($dj[0]) ? intval($dj[0]) : 0;
            $djcount = isset($dj[1]) ? intval($dj[1]) : 0;

            if ($djid > 0 && $djcount > 0) {
                \player\adddj($sid, $djid, $djcount, $dblj);
                $daoju = \player\getdaoju($djid, $dblj);
                $djname = isset($daoju->djname) ? $daoju->djname : ('Đạo cụ #' . $djid);
                $rewardHtml .= '<li><span>Đạo cụ</span><b>' . gift_html($djname) . ' x' . intval($djcount) . '</b></li>';
                \player\changerwyq1(1, $djid, $djcount, $sid, $dblj);
            }
        }
    }

    // Dược phẩm.
    $ypitem = explode(',', strval($duihuan->dhyp));
    foreach ($ypitem as $ypinfo) {
        $ypinfo = trim($ypinfo);
        if ($ypinfo !== '') {
            $yp = explode('|', $ypinfo);
            $ypid = isset($yp[0]) ? intval($yp[0]) : 0;
            $ypcount = isset($yp[1]) ? intval($yp[1]) : 0;

            if ($ypid > 0 && $ypcount > 0) {
                \player\addyaopin($sid, $ypid, $ypcount, $dblj);
                $yaopin = \player\getyaopinonce($ypid, $dblj);
                $ypname = isset($yaopin->ypname) ? $yaopin->ypname : ('Dược phẩm #' . $ypid);
                $rewardHtml .= '<li><span>Dược phẩm</span><b>' . gift_html($ypname) . ' x' . intval($ypcount) . '</b></li>';
            }
        }
    }

    // Linh thạch.
    if (isset($duihuan->dhyxb) && intval($duihuan->dhyxb) > 0) {
        \player\changeyxb(1, intval($duihuan->dhyxb), $sid, $dblj);
        $rewardHtml .= '<li><span>Linh thạch</span><b>' . intval($duihuan->dhyxb) . '</b></li>';
    }

    // Ma thạch.
    if (isset($duihuan->dhczb) && intval($duihuan->dhczb) > 0) {
        \player\changeczb(1, intval($duihuan->dhczb), $sid, $dblj);
        $rewardHtml .= '<li><span>Ma thạch</span><b>' . intval($duihuan->dhczb) . '</b></li>';
    }

    // EXP.
    if (isset($duihuan->dhexp) && intval($duihuan->dhexp) > 0) {
        \player\changeexp($sid, $dblj, intval($duihuan->dhexp));
        $rewardHtml .= '<li><span>EXP</span><b>' . intval($duihuan->dhexp) . '</b></li>';
    }

    if ($rewardHtml == '') {
        $rewardHtml = '<li><span>Thông báo</span><b>Không có vật phẩm hiển thị</b></li>';
    }

    return $rewardHtml;
}

if ($dhm !== '') {
    // Chỉ cho phép ký tự thường dùng trong giftcode để tránh mã rác.
    // Có thể nới regex nếu sau này muốn dùng dấu gạch ngang hoặc ký tự đặc biệt khác.
    if (!preg_match('/^[a-z0-9_-]{3,32}$/', $dhm)) {
        $tishi = gift_error('Giftcode không hợp lệ. Chỉ dùng chữ, số, dấu gạch dưới hoặc gạch ngang.');
    } else {
        $duihuan = \player\getduihuan($dhm, $dblj);

        if ($duihuan) {
            // Đánh dấu đã nhận trước khi phát thưởng.
            // Nếu refresh / nhập lại link cũ, INSERT IGNORE sẽ fail và không phát thưởng nữa.
            if (gift_mark_code_used($sid, $dhm, $dblj, $tishi)) {
                $rewardHtml = gift_send_rewards($sid, $duihuan, $dblj);
                $giftName = isset($duihuan->dhname) ? $duihuan->dhname : $dhm;
                $tishi = '<div class="gift-alert gift-alert-success"><div class="gift-alert-title">Đổi Giftcode【' . gift_html($giftName) . '】thành công</div><div class="gift-reward-box"><ul class="gift-reward-list">' . $rewardHtml . '</ul></div></div>';
            }
        } else {
            $tishi = gift_error('Đổi thất bại, Giftcode không đúng hoặc đã hết hạn.');
        }
    }
}

$dhhtml = <<<HTML
<div class="gift-page">
    <div class="gift-hero">
        <div class="gift-hero-text">
            <div class="gift-kicker">TU CHÂN PHÚC DUYÊN</div>
            <div class="gift-title">Đổi Giftcode</div>
            <div class="gift-desc">Nhập mã nhận thưởng, linh thạch, ma thạch, đạo cụ và tài nguyên tu luyện.</div>
        </div>
        <div class="gift-seal">Lễ</div>
    </div>

    <form class="gift-card" method="get" action="game.php" autocomplete="off">
        <input type="hidden" name="cmd" value="duihuan">
        <input type="hidden" name="sid" value="$sid">

        <div class="gift-banner">
            <img src="./images/duihuan.png" alt="Giftcode">
        </div>

        <div class="gift-code-show">
            <span>Giftcode hiện tại</span>
            <b>$dhmSafe</b>
        </div>

        <div class="gift-code-sample" aria-label="Giftcode gợi ý">
            <span>V</span><span>I</span><span>P</span><span>6</span><span>6</span><span>6</span><span>8</span><span>8</span><span>8</span>
        </div>

        <div class="gift-form-row">
            <input class="gift-input" name="dhm" value="$dhmSafe" placeholder="Nhập Giftcode...">
            <button class="gift-submit" type="submit">Đổi</button>
        </div>

        $tishi
    </form>

    <div class="gift-bottom">
        <a class="gift-back" href="#" onclick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="gift-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $dhhtml;
?>
