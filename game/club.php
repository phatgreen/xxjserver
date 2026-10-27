<?php
/**
 * Tông môn - giao diện mới
 * Cập nhật:
 * - Chặn clubid rỗng để tránh lỗi SQL "WHERE clubid= ORDER BY".
 * - Dùng prepare/execute thay cho nối chuỗi SQL ở các truy vấn quan trọng.
 * - Bỏ qua dữ liệu rác trong clubplayer nếu uid không còn nhân vật trong game1.
 * - Sửa PDO::FETCH_ASSOC thành \PDO::FETCH_ASSOC để chạy đúng trong namespace.
 * - Mở rộng cấp bậc tông môn: Thánh Tử, Chân Truyền, Thiên Kiêu, Tinh Anh, Nội/Ngoại Môn Đệ Tử.
 * - Thêm hệ thống quyên góp, nâng cấp, lợi ích theo cấp, duyệt thành viên.
 * - Nhiệm vụ tông môn (club quest): random 4-6 quest/ngày, auto progress, claim reward.
 */
require_once __DIR__ . '/../class/function/club_quest.php';
require_once __DIR__ . '/../class/function/club_treasury.php';

/* === CONFIG NÂNG CẤP TÔNG MÔN (thủ công từng cấp) === */
$club_upgrade_config = [
    //  Cấp => [LT cần, MT cần, EXP cần]
    1  => [300000,  30000,   10000],
    2  => [500000,  50000,   25000],
    3  => [700000,  70000,   50000],
    4  => [1000000, 100000,  100000],
    5  => [2000000, 200000,  160000],
    6  => [3000000, 300000,  240000],
    7  => [5000000, 500000,  360000],
    8  => [7000000, 700000,  500000],
    9  => [10000000, 1000000,  700000],
];
$club_max_level = 10;

/* === CONFIG LỢI ÍCH TÔNG MÔN theo cấp === */
$club_benefits_config = [
    //  Cấp => [max thành viên, HP%, ATK%, DEF%, EXP%]
    1  => [8,   2,  1,  1,  105],
    2  => [11,  4,  2,  2,  110],
    3  => [14,  6,  3,  3,  115],
    4  => [17,  8,  4,  4,  120],
    5  => [20, 10,  5,  5,  125],
    6  => [25, 12,  6,  6,  130],
    7  => [30, 14,  7,  7,  135],
    8  => [35, 16,  8,  8,  140],
    9  => [40, 18,  9,  9,  145],
    10 => [50, 20, 10, 10,  150],
];

/* === CONFIG KIẾM EXP TÔNG MÔN === */
$club_exp_config = [
    'donate_rate_lt'    => 0.10,   // Quyên góp LT: 10% số tiền donate
    'donate_rate_mt'    => 1.0,    // Quyên góp MT: 1 MT = 1 EXP
    'donate_min_lt'     => 10,     // Tối thiểu 10 Linh Thạch
    'meditate_rate'     => 0.05,   // Tu luyện: 5% EXP nhận được
    'online_per_hour'   => 50,     // Online: 50 EXP/giờ (mỗi thành viên)
    'quest_base'        => 100,    // Nhiệm vụ cơ bản: 100 EXP
    'quest_daily_max'   => 5,      // Số nhiệm vụ tông môn/ngày
    'boss_base'         => 500,    // Boss tông môn: 500 EXP cơ bản
    'boss_per_player'   => 50,     // Boss: +50 EXP mỗi thành viên tham gia
];

/* === HÀM CỘNG EXP TÔNG MÔN === */
function club_add_exp($clubId, $amount, $dblj) {
    if ($amount <= 0) return;
    $stmt = $dblj->prepare("UPDATE club SET clubexp = clubexp + ? WHERE clubid = ?");
    $stmt->execute(array(intval($amount), intval($clubId)));
}

/* === HÀM LẤY CLUB THEO SID === */
function club_get_by_sid($sid, $dblj) {
    $cp = \player\getclubplayer_once($sid, $dblj);
    if (!$cp || intval($cp->uclv) == 0) return null;
    return \player\getclub(intval($cp->clubid), $dblj);
}

function club_get_upgrade_cost($lv, $config) {
    return isset($config[$lv]) ? $config[$lv] : null;
}
function club_get_benefits($lv, $config) {
    return isset($config[$lv]) ? $config[$lv] : $config[1];
}
function club_max_members($lv, $config) {
    $b = club_get_benefits($lv, $config);
    return $b[0];
}
function club_buff_hp($lv, $config) {
    $b = club_get_benefits($lv, $config);
    return $b[1];
}
function club_buff_atk($lv, $config) {
    $b = club_get_benefits($lv, $config);
    return $b[2];
}
function club_buff_def($lv, $config) {
    $b = club_get_benefits($lv, $config);
    return $b[3];
}
function club_buff_exp($lv, $config) {
    $b = club_get_benefits($lv, $config);
    return $b[4];
}

$clubplayer = \player\getclubplayer_once($sid, $dblj);
$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$clubhtml = '';
$clubmenu = '';
$renzhihtml = '';
$playerlist = '';

/* Chuẩn hóa biến đầu vào */
$clubid = isset($clubid) ? intval($clubid) : 0;
$canshu = isset($canshu) ? $canshu : '';
$zhiwei = isset($zhiwei) ? intval($zhiwei) : 0;
$uid = isset($uid) ? intval($uid) : 0;
$isAjax = $isAjaxEarly
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
    || (isset($_SERVER['HTTP_X_GAME_AJAX']) && $_SERVER['HTTP_X_GAME_AJAX'] === '1');

function ajaxJson($ok, $msg) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    @ini_set('display_errors', '0');
    echo json_encode(array('ok' => $ok, 'msg' => $msg));
    flush();
    die();
}

if ($canshu !== '') {
    switch ($canshu) {
        case "createclub":
            if ($clubplayer) {
                echo '<div class="tcg-club-notice">Ngươi đã có tông môn</div>';
                break;
            }

            $playerLevel = max(1, intval($player->ulv));
            if ($playerLevel < 30) {
                echo '<div class="tcg-club-notice">Cần đạt cấp 30 mới có thể tạo tông môn</div>';
                break;
            }

            $createcostcmd = $encode->encode("cmd=club&canshu=docreateclub&sid=$sid");
            $clublistcmd = $encode->encode("cmd=clublist&sid=$sid");

            $createform = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-hero">
        <div class="tcg-club-hero-bg"><img src="/images/tongmon/bg_tongmon.png" alt=""></div>
        <div class="tcg-club-hero-mask"></div>
        <div class="tcg-club-hero-main">
            <div class="tcg-club-label">⛩ TẠO MÔN PHÁI</div>
            <h1>Sáng Lập Tông Môn</h1>
            <div class="tcg-club-desc">Tạo tông môn riêng của ngươi. Chi phí: 20,000 Ma Thạch <b>hoặc</b> 200,000 Linh Thạch</div>
        </div>
    </div>
    <div class="tcg-club-section">
        <form method="post" action="?cmd=$createcostcmd" onsubmit="return GameAlert.form(this)">
            <div class="tcg-club-form-group">
                <label class="tcg-club-form-label">Tên Tông Môn</label>
                <input type="text" name="clubname" class="tcg-club-form-input" placeholder="Nhập tên tông môn (2-25 ký tự)" maxlength="25" required>
            </div>
            <div class="tcg-club-form-group">
                <label class="tcg-club-form-label">Pháp Hiệu / Mô Tả</label>
                <textarea name="clubinfo" class="tcg-club-form-textarea" placeholder="Giới thiệu ngắn gọn về tông môn" maxlength="100" rows="3"></textarea>
            </div>
            <div class="tcg-club-form-group">
                <label class="tcg-club-form-label">Phương Thức Thanh Toán</label>
                <div class="tcg-club-pay-options">
                    <label class="tcg-club-pay-option">
                        <input type="radio" name="paytype" value="mt" checked>
                        <span class="tcg-club-pay-box">
                            <span class="tcg-club-pay-icon">🔮</span>
                            <span class="tcg-club-pay-name">Ma Thạch</span>
                            <span class="tcg-club-pay-cost">20,000 MT</span>
                        </span>
                    </label>
                    <label class="tcg-club-pay-option">
                        <input type="radio" name="paytype" value="lt">
                        <span class="tcg-club-pay-box">
                            <span class="tcg-club-pay-icon">💎</span>
                            <span class="tcg-club-pay-name">Linh Thạch</span>
                            <span class="tcg-club-pay-cost">200,000 LT</span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="tcg-club-form-actions">
                <a class="tcg-club-btn danger" href="?cmd=$clublistcmd">Hủy</a>
                <button type="submit" class="tcg-club-btn green">Tạo Tông Môn</button>
            </div>
        </form>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
            echo $createform;
            break;

        case "docreateclub":
            $clubbackcmd = $encode->encode("cmd=club&canshu=createclub&sid=$sid");
            if ($clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi đã có tông môn rồi');
                $myClubCmd = $encode->encode("cmd=club&clubid=".intval($clubplayer->clubid)."&sid=$sid");
                echo '<script>window.location.href="?cmd='.$myClubCmd.'";</script>';
                exit;
            }

            $clubname = isset($_POST['clubname']) ? trim($_POST['clubname']) : '';
            $clubinfo = isset($_POST['clubinfo']) ? trim($_POST['clubinfo']) : '';
            $paytype = isset($_POST['paytype']) ? $_POST['paytype'] : 'mt';
            $errMsg = '';

            if (max(1, intval($player->ulv)) < 30) {
                $errMsg = 'Cần đạt cấp 30 mới có thể tạo tông môn';
            } elseif (mb_strlen($clubname, 'UTF-8') < 2 || mb_strlen($clubname, 'UTF-8') > 25) {
                $errMsg = 'Tên tông môn phải từ 2-25 ký tự';
            } elseif (!preg_match('/^[\p{L}0-9 ]+$/u', $clubname)) {
                $errMsg = 'Tên tông môn chỉ được dùng chữ, số và khoảng trắng';
            } else {
                $stmt = $dblj->prepare("SELECT COUNT(*) FROM club WHERE clubname = ?");
                $stmt->execute(array($clubname));
                if ($stmt->fetchColumn() > 0) $errMsg = 'Tên tông môn đã tồn tại';
            }

            if ($errMsg !== '') {
                if ($isAjax) ajaxJson(false, $errMsg);
                echo '<script>window.location.href="?cmd='.$clubbackcmd.'";</script>';
                exit;
            }

            if ($paytype === 'lt') {
                $createCost = 200000;
                $currentCurrency = max(0, intval($player->uyxb));
                if ($currentCurrency < $createCost) {
                    if ($isAjax) ajaxJson(false, 'Không đủ Linh Thạch (cần '.$createCost.' LT)');
                    echo '<script>window.location.href="?cmd='.$clubbackcmd.'";</script>';
                    exit;
                }
                \player\changeyxb(2, $createCost, $sid, $dblj);
            } else {
                $createCost = 20000;
                $currentCurrency = max(0, intval($player->uczb));
                if ($currentCurrency < $createCost) {
                    if ($isAjax) ajaxJson(false, 'Không đủ Ma Thạch (cần '.$createCost.' MT)');
                    echo '<script>window.location.href="?cmd='.$clubbackcmd.'";</script>';
                    exit;
                }
                \player\changeczb(2, $createCost, $sid, $dblj);
            }

            $safeClubName = htmlspecialchars($clubname, ENT_QUOTES, 'UTF-8');
            $safeClubInfo = htmlspecialchars($clubinfo, ENT_QUOTES, 'UTF-8');

            $stmt = $dblj->prepare("INSERT INTO club(clubname, clubinfo, clublv, clubexp, clubno1, clubyxb, clubczb, clubdiamond) VALUES (?, ?, 1, 0, ?, 0, 0, 0)");
            $stmt->execute(array($safeClubName, $safeClubInfo, intval($player->uid)));

            $newClubId = $dblj->lastInsertId();

            $stmt = $dblj->prepare("INSERT INTO clubplayer(clubid, uid, sid, uclv) VALUES (?, ?, ?, 1)");
            $stmt->execute(array($newClubId, intval($player->uid), $sid));

            $clubplayer = \player\getclubplayer_once($sid, $dblj);

            $newclubcmd = $encode->encode("cmd=club&clubid=$newClubId&sid=$sid");
            if ($isAjax) ajaxJson(true, "Tạo tông môn [$safeClubName] thành công!");
            echo '<script>window.location.href="?cmd='.$newclubcmd.'";</script>';
            exit;

        case "joinclub":
            if ($clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi đã có tông môn');
                echo '<div class="tcg-club-notice">Ngươi đã có tông môn</div>';
                break;
            }

            if ($clubid <= 0) {
                if ($isAjax) ajaxJson(false, 'Tông môn không tồn tại');
                echo '<div class="tcg-club-notice">Tông môn không tồn tại</div>';
                break;
            }

            $club = \player\getclub($clubid, $dblj);
            if (!$club) {
                if ($isAjax) ajaxJson(false, 'Tông môn không tồn tại');
                echo '<div class="tcg-club-notice">Tông môn không tồn tại</div>';
                break;
            }

            $checkStmt = $dblj->prepare("SELECT COUNT(*) FROM clubplayer WHERE sid = ? AND clubid = ?");
            $checkStmt->execute(array($sid, $clubid));
            if ($checkStmt->fetchColumn() > 0) {
                if ($isAjax) ajaxJson(false, 'Ngươi đã gửi đơn gia nhập rồi');
                echo '<div class="tcg-club-notice">Ngươi đã gửi đơn gia nhập rồi</div>';
                break;
            }

            // Check member cap
            $clubLv = max(1, intval($club->clublv));
            $maxMembers = club_max_members($clubLv, $club_benefits_config);
            $countStmt = $dblj->prepare("SELECT COUNT(*) FROM clubplayer WHERE clubid = ? AND uclv > 0");
            $countStmt->execute(array($clubid));
            $currentCount = intval($countStmt->fetchColumn());
            if ($currentCount >= $maxMembers) {
                if ($isAjax) ajaxJson(false, "Tông môn đã đầy thành viên! (Tối đa {$maxMembers})");
                echo '<div class="tcg-club-notice">Tông môn đã đầy thành viên! (Tối đa '.$maxMembers.')</div>';
                break;
            }

            $stmt = $dblj->prepare("INSERT INTO clubplayer(clubid, uid, sid, uclv) VALUES (?, ?, ?, 0)");
            $stmt->execute(array($clubid, intval($player->uid), $sid));

            $clubplayer = \player\getclubplayer_once($sid, $dblj);
            if ($isAjax) ajaxJson(true, 'Đã gửi đơn gia nhập! Chờ chưởng môn duyệt');
            echo '<div class="tcg-club-notice success">Đã gửi đơn gia nhập! Chờ chưởng môn duyệt</div>';
            break;

        case "approveclub":
            if (!$clubplayer || intval($clubplayer->uclv) > 2) {
                if ($isAjax) ajaxJson(false, 'Không có quyền duyệt');
                echo '<div class="tcg-club-notice">Không có quyền duyệt</div>';
                break;
            }
            if ($uid <= 0) {
                if ($isAjax) ajaxJson(false, 'Thành viên không hợp lệ');
                echo '<div class="tcg-club-notice">Thành viên không hợp lệ</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $stmt = $dblj->prepare("UPDATE clubplayer SET uclv = 11 WHERE uid = ? AND clubid = ? AND uclv = 0");
            $stmt->execute(array($uid, $myClubId));
            if ($isAjax) ajaxJson(true, 'Duyệt thành viên thành công!');
            echo '<div class="tcg-club-notice success">Duyệt thành viên thành công!</div>';
            break;

        case "rejectclub":
            if (!$clubplayer || intval($clubplayer->uclv) > 2) {
                if ($isAjax) ajaxJson(false, 'Không có quyền từ chối');
                echo '<div class="tcg-club-notice">Không có quyền từ chối</div>';
                break;
            }
            if ($uid <= 0) {
                if ($isAjax) ajaxJson(false, 'Thành viên không hợp lệ');
                echo '<div class="tcg-club-notice">Thành viên không hợp lệ</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $stmt = $dblj->prepare("DELETE FROM clubplayer WHERE uid = ? AND clubid = ? AND uclv = 0");
            $stmt->execute(array($uid, $myClubId));
            if ($isAjax) ajaxJson(true, 'Đã từ chối đơn gia nhập');
            echo '<div class="tcg-club-notice">Đã từ chối đơn gia nhập</div>';
            break;

        case "outclub":
            if ($clubplayer) {
                // Master cannot leave — must transfer or delete club first
                if (intval($clubplayer->uclv) == 1) {
                    if ($isAjax) ajaxJson(false, 'Chưởng Môn không thể thoát tông môn! Hãy chuyển giao hoặc giải tán.');
                    echo '<div class="tcg-club-notice">Chưởng Môn không thể thoát tông môn! Hãy chuyển giao hoặc giải tán.</div>';
                    break;
                }
                $stmt = $dblj->prepare("DELETE FROM clubplayer WHERE sid = ?");
                $stmt->execute(array($sid));
                $clubplayer = \player\getclubplayer_once($sid, $dblj);
            }
            if ($isAjax) ajaxJson(true, 'Đã thoát tông môn');
            break;

        case "deleteclub":
            if ($clubplayer && intval($clubplayer->uclv) == 1) {
                $deleteClubId = intval($clubplayer->clubid);

                $stmt = $dblj->prepare("DELETE FROM club WHERE clubid = ?");
                $stmt->execute(array($deleteClubId));

                $stmt = $dblj->prepare("DELETE FROM clubplayer WHERE clubid = ?");
                $stmt->execute(array($deleteClubId));

                $clubplayer = false;
                if ($isAjax) ajaxJson(true, 'Tông môn giải tán thành công');
                echo '<div class="tcg-club-notice success">Tông môn giải tán thành công</div>';
            }
            break;

        case "renzhi":
            if ($clubplayer) {
                if ($zhiwei > 0) {
                    $stmt = $dblj->prepare("SELECT uid FROM clubplayer WHERE clubid = ? AND uclv > ? ORDER BY uclv ASC");
                    $stmt->execute(array(intval($clubplayer->clubid), intval($clubplayer->uclv)));
                    $retuid = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($retuid as $uiditem) {
                        $targetUid = intval($uiditem['uid']);
                        if ($targetUid <= 0 || $targetUid == intval($player->uid)) {
                            continue;
                        }

                        $otherplayer = \player\getplayer1($targetUid, $dblj);
                        if (!$otherplayer || empty($otherplayer->uid) || empty($otherplayer->uname)) {
                            continue;
                        }

                        $ucmd = $encode->encode("cmd=club&canshu=zhiwei&zhiwei=$zhiwei&uid=$targetUid&sid=$sid");
                        $safeName = htmlspecialchars($otherplayer->uname, ENT_QUOTES, 'UTF-8');
                        $playerlist .= "<a class='tcg-club-member-name' href='?cmd=$ucmd'>$safeName</a>";
                    }

                    if ($playerlist === '') {
                        $playerlist = '<div class="tcg-club-notice">Không có thành viên hợp lệ để nhậm chức</div>';
                    }

                    $renzhihtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-section">
        <div class="tcg-club-section-title">⚜ Chọn nhân viên nhận chức</div>
        <div class="tcg-club-member-grid">$playerlist</div>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-back" href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
                    exit($renzhihtml);
                }

                if (intval($clubplayer->uclv) == 1) {
                    $no2cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=2&sid=$sid");
                    $no3cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=3&sid=$sid");
                    $no4cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=4&sid=$sid");
                    $no5cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=5&sid=$sid");
                    $no6cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=6&sid=$sid");
                    $no7cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=7&sid=$sid");
                    $no8cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=8&sid=$sid");
                    $no9cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=9&sid=$sid");
                    $no10cmd = $encode->encode("cmd=club&canshu=renzhi&zhiwei=10&sid=$sid");
					$no11cmd = $encode->encode("cmd=club&canshu=renzhi&zhiwei=11&sid=$sid");
                    $renzhihtml = "<div class='tcg-club-rank-actions'>"
                        . "<a href='?cmd=$no2cmd'>Phó Chưởng Môn</a>"
                        . "<a href='?cmd=$no3cmd'>Trưởng Lão</a>"
                        . "<a href='?cmd=$no4cmd'>Chấp Sự</a>"
                        . "<a href='?cmd=$no5cmd'>Thánh Tử</a>"
                        . "<a href='?cmd=$no6cmd'>Chân Truyền</a>"
                        . "<a href='?cmd=$no7cmd'>Thiên Kiêu</a>"
                        . "<a href='?cmd=$no8cmd'>Tinh Anh</a>"
                        . "<a href='?cmd=$no9cmd'>Nội Môn Đệ Tử</a>"
                        . "<a href='?cmd=$no10cmd'>Ngoại Môn Đệ Tử</a>"
						. "<a href='?cmd=$no11cmd'>Đệ Tử Tạp Dịch</a>"
                        . "</div>";
                }

                if (intval($clubplayer->uclv) == 2) {
                    $no3cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=3&sid=$sid");
                    $no4cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=4&sid=$sid");
                    $no5cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=5&sid=$sid");
                    $no6cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=6&sid=$sid");
                    $no7cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=7&sid=$sid");
                    $no8cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=8&sid=$sid");
                    $no9cmd  = $encode->encode("cmd=club&canshu=renzhi&zhiwei=9&sid=$sid");
                    $no10cmd = $encode->encode("cmd=club&canshu=renzhi&zhiwei=10&sid=$sid");
					$no11cmd = $encode->encode("cmd=club&canshu=renzhi&zhiwei=11&sid=$sid");
                    $renzhihtml = "<div class='tcg-club-rank-actions'>"
                        . "<a href='?cmd=$no3cmd'>Trưởng Lão</a>"
                        . "<a href='?cmd=$no4cmd'>Chấp Sự</a>"
                        . "<a href='?cmd=$no5cmd'>Thánh Tử</a>"
                        . "<a href='?cmd=$no6cmd'>Chân Truyền</a>"
                        . "<a href='?cmd=$no7cmd'>Thiên Kiêu</a>"
                        . "<a href='?cmd=$no8cmd'>Tinh Anh</a>"
                        . "<a href='?cmd=$no9cmd'>Nội Môn Đệ Tử</a>"
                        . "<a href='?cmd=$no10cmd'>Ngoại Môn Đệ Tử</a>"
						. "<a href='?cmd=$no11cmd'>Đệ Tử Tạp Dịch</a>"
                        . "</div>";
                }
            }
            break;

        case "zhiwei":
            if ($clubplayer && $zhiwei > 0 && $uid > 0) {
                $myClubId = intval($clubplayer->clubid);
                $myRank = intval($clubplayer->uclv);

                if ($zhiwei > $myRank && $zhiwei >= 2 && $zhiwei <= 11) {
                    $stmt = $dblj->prepare("UPDATE clubplayer SET uclv = ? WHERE uid = ? AND clubid = ? AND uclv > ?");
                    $stmt->execute(array($zhiwei, $uid, $myClubId, $myRank));
                }
            }
            $clubcmd = $encode->encode("cmd=club&clubid=".intval($clubplayer ? $clubplayer->clubid : 0)."&sid=$sid");
            header("Location: ?cmd=$clubcmd");
            exit();

        case "donate":
            if (!$clubplayer) {
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $donatecmd = $encode->encode("cmd=club&canshu=donatedo&sid=$sid");
            $clubcmd = $encode->encode("cmd=club&clubid=$myClubId&sid=$sid");

            $donatehtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-hero">
        <div class="tcg-club-hero-bg"><img src="/images/tongmon/bg_tongmon.png" alt=""></div>
        <div class="tcg-club-hero-mask"></div>
        <div class="tcg-club-hero-main">
            <div class="tcg-club-label">💰 QUYÊN GÓP</div>
            <h1>Gửi Tài Nguyên</h1>
            <div class="tcg-club-desc">Đóng góp tài nguyên để xây dựng tông môn. LT: 10 LT = 1 EXP | MT: 1 MT = 1 EXP. Nhận cả EXP cá nhân lẫn EXP tông môn.</div>
        </div>
    </div>
    <div class="tcg-club-section">
        <form method="post" action="?cmd=$donatecmd" onsubmit="return GameAlert.form(this)">
            <div class="tcg-club-form-group">
                <label class="tcg-club-form-label">Loại Tài Nguyên</label>
                <div class="tcg-club-pay-options">
                    <label class="tcg-club-pay-option">
                        <input type="radio" name="donate_type" value="lt" checked>
                        <span class="tcg-club-pay-box">
                            <span class="tcg-club-pay-icon">💎</span>
                            <span class="tcg-club-pay-name">Linh Thạch</span>
                            <span class="tcg-club-pay-cost">Còn: {$player->uyxb} LT</span>
                        </span>
                    </label>
                    <label class="tcg-club-pay-option">
                        <input type="radio" name="donate_type" value="mt">
                        <span class="tcg-club-pay-box">
                            <span class="tcg-club-pay-icon">🔮</span>
                            <span class="tcg-club-pay-name">Ma Thạch</span>
                            <span class="tcg-club-pay-cost">Còn: {$player->uczb} MT</span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="tcg-club-form-group">
                <label class="tcg-club-form-label">Số Lượng</label>
                <input type="number" name="donate_amount" class="tcg-club-form-input" placeholder="LT tối thiểu 10, MT tối thiểu 1" min="1" required>
            </div>
            <div class="tcg-club-form-actions">
                <a class="tcg-club-btn danger" href="?cmd=$clubcmd">Hủy</a>
                <button type="submit" class="tcg-club-btn green">Quyên Góp</button>
            </div>
        </form>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-back" href="?cmd=$clubcmd">← Trở Về</a>
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
            echo $donatehtml;
            break;

        case "donatedo":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $donateType = isset($_POST['donate_type']) ? $_POST['donate_type'] : 'lt';
            $donateAmount = max(0, intval($_POST['donate_amount']));

            if ($donateAmount <= 0) {
                if ($isAjax) ajaxJson(false, 'Số lượng không hợp lệ');
                echo '<div class="tcg-club-notice">Số lượng không hợp lệ</div>';
                break;
            }

            $clubField = '';
            $currencyName = '';
            $currentAmount = 0;
            $errDonate = '';

            switch ($donateType) {
                case 'lt':
                    $clubField = 'clubyxb';
                    $currencyName = 'Linh Thạch';
                    $currentAmount = max(0, intval($player->uyxb));
                    break;
                case 'mt':
                    $clubField = 'clubczb';
                    $currencyName = 'Ma Thạch';
                    $currentAmount = max(0, intval($player->uczb));
                    break;
                default:
                    $errDonate = 'Loại tài nguyên không hợp lệ';
            }

            if ($errDonate !== '') {
                if ($isAjax) ajaxJson(false, $errDonate);
                echo '<div class="tcg-club-notice">'.$errDonate.'</div>';
                break;
            }

            if ($donateType === 'lt' && $donateAmount < $club_exp_config['donate_min_lt']) {
                $errMsg2 = 'Linh Thạch tối thiểu phải 10 LT';
                if ($isAjax) ajaxJson(false, $errMsg2);
                echo '<div class="tcg-club-notice">'.$errMsg2.'</div>';
                break;
            }

            if ($currentAmount < $donateAmount) {
                $errMsg3 = "Không đủ $currencyName (còn $currentAmount)";
                if ($isAjax) ajaxJson(false, $errMsg3);
                echo '<div class="tcg-club-notice">'.$errMsg3.'</div>';
                break;
            }

            if ($donateType === 'lt') {
                \player\changeyxb(2, $donateAmount, $sid, $dblj);
            } elseif ($donateType === 'mt') {
                \player\changeczb(2, $donateAmount, $sid, $dblj);
            }

            $stmt = $dblj->prepare("UPDATE club SET $clubField = $clubField + ? WHERE clubid = ?");
            $stmt->execute(array($donateAmount, $myClubId));

            $donateRate = ($donateType === 'mt') ? $club_exp_config['donate_rate_mt'] : $club_exp_config['donate_rate_lt'];
            $expGain = intval($donateAmount * $donateRate);
            if ($expGain > 0) {
                \player\changeexp($sid, $dblj, $expGain);
                club_add_exp($myClubId, $expGain, $dblj);
            }

            $successMsg = "Quyên góp thành công! Nhận $expGain EXP cá nhân + $expGain EXP tông môn";
            if ($isAjax) ajaxJson(true, $successMsg);
            echo '<div class="tcg-club-notice success">'.$successMsg.'</div>';

            /* Hook quest progress: donate LT */
            if ($donateType === 'lt') {
                \player\club_quest_update($sid, 'donate_lt', intval($donateAmount), $dblj);
            }
            break;

        case "upgrade":
            if (!$clubplayer) {
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            if (intval($clubplayer->uclv) != 1) {
                echo '<div class="tcg-club-notice">Chỉ Chưởng Môn mới được nâng cấp</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $club = \player\getclub($myClubId, $dblj);
            if (!$club) {
                echo '<div class="tcg-club-notice">Tông môn không tồn tại</div>';
                break;
            }
            $currentLv = max(1, intval($club->clublv));
            $nextLv = $currentLv + 1;
            if ($currentLv >= $club_max_level) {
                echo '<div class="tcg-club-notice">Đã đạt cấp tối đa</div>';
                break;
            }
            $cost = club_get_upgrade_cost($currentLv, $club_upgrade_config);
            if (!$cost) {
                echo '<div class="tcg-club-notice">Chưa có cấu hình nâng cấp cấp này</div>';
                break;
            }
            $upgradeCostLT = $cost[0];
            $upgradeCostMT = $cost[1];
            $upgradeCostExp = $cost[2];
            $upgradecmd = $encode->encode("cmd=club&canshu=upgradedo&sid=$sid");
            $clubcmd = $encode->encode("cmd=club&clubid=$myClubId&sid=$sid");

            // Lấy lợi ích cấp tiếp theo
            $nextBenefits = club_get_benefits($nextLv, $club_benefits_config);

            $upgradehtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-hero">
        <div class="tcg-club-hero-bg"><img src="/images/tongmon/bg_tongmon.png" alt=""></div>
        <div class="tcg-club-hero-mask"></div>
        <div class="tcg-club-hero-main">
            <div class="tcg-club-label">⬆ NÂNG CẤP TÔNG MÔN</div>
            <h1>Cấp $currentLv → Cấp $nextLv</h1>
            <div class="tcg-club-desc">Nâng cấp tông môn để nhận nhiều lợi ích hơn</div>
        </div>
    </div>
    <div class="tcg-club-section">
        <div class="tcg-club-form-group">
            <label class="tcg-club-form-label">Chi Phí Nâng Cấp</label>
            <div class="tcg-club-upgrade-req">
                <div class="tcg-club-req-item">💎 Linh thạch: <strong>$club->clubyxb</strong> / $upgradeCostLT</div>
                <div class="tcg-club-req-item">🔮 Ma thạch: <strong>$club->clubczb</strong> / $upgradeCostMT</div>
                <div class="tcg-club-req-item">📈 EXP tông môn: <strong>$club->clubexp</strong> / $upgradeCostExp</div>
            </div>
        </div>
        <div class="tcg-club-form-group">
            <label class="tcg-club-form-label">Lợi Ích Cấp $nextLv</label>
            <div class="tcg-club-benefits">
                <div class="tcg-club-benefit">❤ HP +{$nextBenefits[1]}%</div>
                <div class="tcg-club-benefit">⚔ Tấn công +{$nextBenefits[2]}%</div>
                <div class="tcg-club-benefit">🛡 Phòng thủ +{$nextBenefits[3]}%</div>
                <div class="tcg-club-benefit">📈 Kinh nghiệm +{$nextBenefits[4]}%</div>
                <div class="tcg-club-benefit">👥 Max thành viên: {$nextBenefits[0]}</div>
            </div>
        </div>
        <div class="tcg-club-form-actions">
            <a class="tcg-club-btn danger" href="?cmd=$clubcmd">Hủy</a>
            <a class="tcg-club-btn purple" href="?cmd=$upgradecmd" onclick="return GameAlert.action(this.href)">Xác Nhận Nâng Cấp</a>
        </div>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-back" href="?cmd=$clubcmd">← Trở Về</a>
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
            echo $upgradehtml;
            break;

        case "upgradedo":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            if (intval($clubplayer->uclv) != 1) {
                if ($isAjax) ajaxJson(false, 'Chỉ Chưởng Môn mới được nâng cấp');
                echo '<div class="tcg-club-notice">Chỉ Chưởng Môn mới được nâng cấp</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $club = \player\getclub($myClubId, $dblj);
            if (!$club) {
                if ($isAjax) ajaxJson(false, 'Tông môn không tồn tại');
                echo '<div class="tcg-club-notice">Tông môn không tồn tại</div>';
                break;
            }
            $currentLv = max(1, intval($club->clublv));
            if ($currentLv >= $club_max_level) {
                if ($isAjax) ajaxJson(false, 'Đã đạt cấp tối đa');
                echo '<div class="tcg-club-notice">Đã đạt cấp tối đa</div>';
                break;
            }
            $nextLv = $currentLv + 1;
            $cost = club_get_upgrade_cost($currentLv, $club_upgrade_config);
            if (!$cost) {
                if ($isAjax) ajaxJson(false, 'Chưa có cấu hình nâng cấp cấp này');
                echo '<div class="tcg-club-notice">Chưa có cấu hình nâng cấp cấp này</div>';
                break;
            }
            $upgradeCostLT = $cost[0];
            $upgradeCostMT = $cost[1];
            $upgradeCostExp = $cost[2];

            $currentClubLT = max(0, intval($club->clubyxb));
            $currentClubMT = max(0, intval($club->clubczb));
            $currentClubExp = max(0, intval($club->clubexp));

            if ($currentClubLT < $upgradeCostLT) {
                $upgErr = "Không đủ Linh Thạch tông môn (cần $upgradeCostLT)";
                if ($isAjax) ajaxJson(false, $upgErr);
                echo '<div class="tcg-club-notice">'.$upgErr.'</div>';
                break;
            }
            if ($currentClubMT < $upgradeCostMT) {
                $upgErr2 = "Không đủ Ma Thạch tông môn (cần $upgradeCostMT)";
                if ($isAjax) ajaxJson(false, $upgErr2);
                echo '<div class="tcg-club-notice">'.$upgErr2.'</div>';
                break;
            }
            if ($currentClubExp < $upgradeCostExp) {
                $upgErr3 = "Không đủ EXP tông môn (cần $upgradeCostExp)";
                if ($isAjax) ajaxJson(false, $upgErr3);
                echo '<div class="tcg-club-notice">'.$upgErr3.'</div>';
                break;
            }

            $stmt = $dblj->prepare("UPDATE club SET clublv = ?, clubyxb = clubyxb - ?, clubczb = clubczb - ?, clubexp = clubexp - ? WHERE clubid = ? AND clubyxb >= ? AND clubczb >= ? AND clubexp >= ?");
            $stmt->execute(array($nextLv, $upgradeCostLT, $upgradeCostMT, $upgradeCostExp, $myClubId, $upgradeCostLT, $upgradeCostMT, $upgradeCostExp));
            if ($stmt->rowCount() === 0) {
                $upgErr = "Nguồn tông môn không đủ hoặc đã có người nâng cấp!";
                if ($isAjax) ajaxJson(false, $upgErr);
                echo '<div class="tcg-club-notice">'.$upgErr.'</div>';
                break;
            }

            $upgSuccess = "Nâng cấp tông môn thành công! Đạt cấp $nextLv";
            if ($isAjax) ajaxJson(true, $upgSuccess);
            echo '<div class="tcg-club-notice success">'.$upgSuccess.'</div>';
            break;

        case "claimonline":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $now = time();
            $lastClaimKey = 'club_online_' . $sid;
            $lastClaim = isset($_SESSION[$lastClaimKey]) ? intval($_SESSION[$lastClaimKey]) : 0;
            $hoursPassed = ($now - $lastClaim) / 3600;

            if ($hoursPassed < 1) {
                $nextClaim = ceil((3600 - ($now - $lastClaim)) / 60);
                if ($isAjax) ajaxJson(false, "Còn $nextClaim phút nữa mới nhận được");
                echo '<div class="tcg-club-notice">Còn '.$nextClaim.' phút nữa mới nhận được</div>';
                break;
            }

            $hours = floor($hoursPassed);
            $onlineExp = $hours * $club_exp_config['online_per_hour'];
            club_add_exp($myClubId, $onlineExp, $dblj);
            $_SESSION[$lastClaimKey] = $now;

            /* Hook quest: online */
            \player\club_quest_update($sid, 'online', intval($hours), $dblj);

            if ($isAjax) ajaxJson(true, "Nhận $onlineExp EXP tông môn từ $hours giờ online!");
            echo '<div class="tcg-club-notice success">Nhận '.$onlineExp.' EXP tông môn từ '.$hours.' giờ online!</div>';
            break;

        case "clubquest":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);

            /* Generate quests nếu chưa có hôm nay */
            \player\club_quest_daily_generate($sid, $myClubId, $dblj);

            /* Claim quest nếu có param pqid */
            $pqid = isset($pqid) ? intval($pqid) : 0;
            if ($pqid > 0) {
                $claimResult = \player\club_quest_claim($sid, $pqid, $dblj);
                if ($isAjax) ajaxJson($claimResult['ok'], $claimResult['msg']);
                $cssClass = $claimResult['ok'] ? 'success' : '';
                echo '<div class="tcg-club-notice ' . $cssClass . '">' . $claimResult['msg'] . '</div>';
                break;
            }

            /* Claim completion bonus nếu có param bonus=1 */
            $claimBonus = isset($bonus) ? intval($bonus) : 0;
            if ($claimBonus == 1) {
                $bonusResult = \player\club_quest_claim_completion($sid, $myClubId, $dblj);
                if ($isAjax) ajaxJson($bonusResult['ok'], $bonusResult['msg']);
                $cssClass = $bonusResult['ok'] ? 'success' : '';
                echo '<div class="tcg-club-notice ' . $cssClass . '">' . $bonusResult['msg'] . '</div>';
                break;
            }

            /* Hiển thị quest list (redirect về trang club) */
            $clubcmd = $encode->encode("cmd=club&clubid=$myClubId&sid=$sid");
            header("Location: ?cmd=$clubcmd");
            exit();
            break;

        case "questview":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            \player\club_quest_daily_generate($sid, $myClubId, $dblj);
            $quests = \player\club_quest_daily_get($sid, $dblj);
            $completionInfo = \player\club_quest_check_completion($sid, $dblj);

            $qhtml = '<div class="tcg-club-page"><div class="tcg-club-section">';
            $qhtml .= '<div class="tcg-club-section-title">📋 Nhiệm Vụ Tông Môn</div>';

            if (empty($quests)) {
                $qhtml .= '<div style="text-align:center;color:#8899aa;padding:20px">Chưa có nhiệm vụ hôm nay</div>';
            } else {
                $qhtml .= '<div class="cq-list">';
                foreach ($quests as $q) {
                    $pqId = intval($q['id']);
                    $cur = intval($q['current_value']);
                    $tgt = intval($q['target_value']);
                    $rew = intval($q['reward_exp']);
                    $done = intval($q['claimed']);
                    $pct = $tgt > 0 ? min(100, intval($cur * 100 / $tgt)) : 0;
                    $complete = ($cur >= $tgt);
                    $tName = htmlspecialchars($q['quest_name'], ENT_QUOTES, 'UTF-8');
                    $tDesc = htmlspecialchars($q['quest_desc'], ENT_QUOTES, 'UTF-8');
                    $tier = intval($q['quest_tier']);
                    $tierClass = ($tier == 1) ? 'easy' : (($tier == 2) ? 'medium' : 'hard');

                    /* Hiển thị thông tin donate item */
                    $donateInfo = '';
                    $donateItemType = isset($q['donate_item_type']) ? $q['donate_item_type'] : null;
                    $donateItemId = isset($q['donate_item_id']) ? intval($q['donate_item_id']) : 0;
                    $donateItemAmount = isset($q['donate_item_amount']) ? intval($q['donate_item_amount']) : 0;
                    if ($donateItemType && $donateItemId > 0 && $donateItemAmount > 0) {
                        $dItemName = 'vật phẩm';
                        if ($donateItemType === 'yaodan') {
                            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaodan_config.json'), true);
                            if (isset($cfg[$donateItemId])) $dItemName = $cfg[$donateItemId]['ydname'];
                        } elseif ($donateItemType === 'daoju') {
                            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/daoju_config.json'), true);
                            if (isset($cfg[$donateItemId])) $dItemName = $cfg[$donateItemId]['djname'];
                        } else {
                            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaopin_config.json'), true);
                            if (isset($cfg[$donateItemId])) $dItemName = $cfg[$donateItemId]['ypname'];
                        }
                        $dItemName = htmlspecialchars($dItemName, ENT_QUOTES, 'UTF-8');
                        $donateInfo = '<div class="cq-donate-info">🏦 Đóng góp: <strong>' . $donateItemAmount . 'x ' . $dItemName . '</strong> → Bảo khố tông môn</div>';
                    }

                    $qhtml .= '<div class="cq-item cq-' . $tierClass . '">';
                    $qhtml .= '<div class="cq-info">';
                    $qhtml .= '<div class="cq-name">' . $tName . '</div>';
                    $qhtml .= '<div class="cq-desc">' . $tDesc . '</div>';
                    $qhtml .= $donateInfo;
                    $qhtml .= '<div class="cq-bar-wrap"><div class="cq-bar"><div class="cq-bar-fill" style="width:' . $pct . '%"></div></div>';
                    $qhtml .= '<span class="cq-progress">' . number_format($cur) . '/' . number_format($tgt) . '</span></div>';
                    $qhtml .= '</div>';
                    $qhtml .= '<div class="cq-reward">';
                    $claimLabel = ($donateItemType && $donateItemId > 0) ? 'Nhận + Đóng góp' : 'Nhận';
                    if ($done) {
                        $qhtml .= '<span class="cq-done-badge">✓ Đã nhận</span>';
                    } elseif ($complete) {
                        $claimCmd = $encode->encode("cmd=club&canshu=clubquest&pqid=$pqId&sid=$sid");
                        $qhtml .= '<a class="cq-claim-btn" href="?cmd=' . $claimCmd . '" onclick="return GameAlert.action(this.href)">' . $claimLabel . ' ' . number_format($rew) . ' EXP</a>';
                    } else {
                        $qhtml .= '<span class="cq-exp">+' . number_format($rew) . ' EXP</span>';
                    }
                    $qhtml .= '</div></div>';
                }
                $qhtml .= '</div>';

                if (isset($completionInfo['done']) && $completionInfo['done']) {
                    $bonusExp = isset($completionInfo['bonus_exp']) ? intval($completionInfo['bonus_exp']) : 500;
                    if (isset($completionInfo['claimed_bonus']) && $completionInfo['claimed_bonus']) {
                        $qhtml .= '<div class="cq-bonus cq-bonus-done">🏆 Đã nhận thưởng: +' . number_format($bonusExp) . ' EXP Tông Môn</div>';
                    } else {
                        $bonusCmd = $encode->encode("cmd=club&canshu=clubquest&bonus=1&sid=$sid");
                        $qhtml .= '<div class="cq-bonus"><a class="cq-bonus-btn" href="?cmd=' . $bonusCmd . '" onclick="return GameAlert.action(this.href)">🏆 Hoàn thành: +' . number_format($bonusExp) . ' EXP Tông Môn</a></div>';
                    }
                } else {
                    $claimed = isset($completionInfo['claimed']) ? intval($completionInfo['claimed']) : 0;
                    $total = isset($completionInfo['total']) ? intval($completionInfo['total']) : 0;
                    if ($total > 0) {
                        $qhtml .= '<div class="cq-bonus cq-bonus-pending">🏆 Tiến độ: ' . $claimed . '/' . $total . '</div>';
                    }
                }
            }

            $backCmd = $encode->encode("cmd=club&clubid=$myClubId&sid=$sid");
            $qhtml .= '</div><div class="tcg-club-bottom"><a class="tcg-club-back" href="?cmd=$backCmd">← Trở về</a></div></div>';
            echo $qhtml;
            exit();
            break;

        case "treasuryview":
            require __DIR__ . '/treasury_page.php';
            exit();
            break;

        case "treasury_distribute":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myUclv = intval($clubplayer->uclv);
            if ($myUclv > 2) {
                if ($isAjax) ajaxJson(false, 'Chỉ Tông chủ và Phó chủ mới có quyền phân phát');
                echo '<div class="tcg-club-notice">Chỉ Tông chủ và Phó chủ mới có quyền phân phát</div>';
                break;
            }
            $tid = isset($tid) ? intval($tid) : 0;
            $targetUid = isset($target_uid) ? intval($target_uid) : 0;
            if ($tid <= 0 || $targetUid <= 0) {
                if ($isAjax) ajaxJson(false, 'Thiếu thông tin vật phẩm hoặc chưa chọn thành viên');
                echo '<div class="tcg-club-notice">Thiếu thông tin vật phẩm hoặc chưa chọn thành viên</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $distributeCost = 500;
            $clubLt = isset($club->clubyxb) ? intval($club->clubyxb) : 0;
            if ($clubLt < $distributeCost) {
                if ($isAjax) ajaxJson(false, 'Kho tông môn không đủ 500 Linh Thạch để phân phát');
                echo '<div class="tcg-club-notice">Kho tông môn không đủ 500 Linh Thạch để phân phát</div>';
                break;
            }
            $treasuryResult = \player\club_treasury_claim($tid, $targetUid, $dblj);
            if (!$treasuryResult['ok']) {
                if ($isAjax) ajaxJson(false, $treasuryResult['msg']);
                echo '<div class="tcg-club-notice">' . $treasuryResult['msg'] . '</div>';
                break;
            }
            $updLt = $dblj->prepare("UPDATE club SET clubyxb = clubyxb - ? WHERE clubid = ? AND clubyxb >= ?");
            $updLt->execute(array($distributeCost, $myClubId, $distributeCost));
            $treasuryRedirect = $encode->encode("cmd=club&canshu=treasuryview&sid=$sid");
            if ($isAjax) ajaxJson(true, 'Phân phát thành công! -500 LT. ' . $treasuryResult['msg']);
            echo '<div class="tcg-club-notice success">Phân phát thành công! Trừ 500 Linh Thạch. ' . $treasuryResult['msg'] . '</div>';
            echo '<script>setTimeout(function(){window.location.href="?cmd=' . $treasuryRedirect . '";},1200)</script>';
            break;

        case "clubboss":
            if (!$clubplayer) {
                if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
                echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
                break;
            }
            $myClubId = intval($clubplayer->clubid);
            $today = date('Y-m-d');
            $bossKey = 'club_boss_' . $myClubId . '_' . $today;

            if (isset($_SESSION[$bossKey]) && $_SESSION[$bossKey] > 0) {
                if ($isAjax) ajaxJson(false, 'Boss tông môn đã bị tiêu diệt hôm nay');
                echo '<div class="tcg-club-notice">Boss tông môn đã bị tiêu diệt hôm nay</div>';
                break;
            }

            $stmt = $dblj->prepare("SELECT COUNT(*) FROM clubplayer WHERE clubid = ? AND uclv > 0");
            $stmt->execute(array($myClubId));
            $memberCount = intval($stmt->fetchColumn());

            $bossExp = $club_exp_config['boss_base'] + ($memberCount * $club_exp_config['boss_per_player']);
            club_add_exp($myClubId, $bossExp, $dblj);
            $_SESSION[$bossKey] = 1;

            if ($isAjax) ajaxJson(true, "Boss tông môn bị tiêu diệt! +$bossExp EXP tông môn ($memberCount thành viên tham gia)");
            echo '<div class="tcg-club-notice success">Boss tông môn bị tiêu diệt! +'.$bossExp.' EXP tông môn ('.$memberCount.' thành viên tham gia)</div>';
            break;
    }
}

/* Auto-track online quest progress — chạy mọi lần load trang club */
if ($clubplayer && intval($clubplayer->clubid) > 0) {
    $onlineTrackKey = 'club_online_quest_' . $sid;
    $lastTrack = isset($_SESSION[$onlineTrackKey]) ? intval($_SESSION[$onlineTrackKey]) : 0;
    if ($lastTrack > 0) {
        $trackPassed = (time() - $lastTrack) / 3600;
        if ($trackPassed >= 1) {
            $trackHours = floor($trackPassed);
            \player\club_quest_update($sid, 'online', intval($trackHours), $dblj);
            $_SESSION[$onlineTrackKey] = time();
        }
    } else {
        $_SESSION[$onlineTrackKey] = time();
    }
}

if ($canshu === '' || $canshu === 'renzhi') {

/* Nếu người chơi đã có tông môn mà URL không truyền clubid, dùng clubid từ clubplayer. */
if ($clubid <= 0 && $clubplayer) {
    $clubid = intval($clubplayer->clubid);
}

/* Chỉ vào trang chi tiết tông môn khi clubid hợp lệ. */
if ($clubid > 0) {
    if ($clubplayer) {
        if (intval($clubplayer->clubid) == $clubid) {
            $outclubcmd = $encode->encode("cmd=club&canshu=outclub&sid=$sid");
            $donatecmd = $encode->encode("cmd=club&canshu=donate&sid=$sid");
            $onlinecmd = $encode->encode("cmd=club&canshu=claimonline&sid=$sid");
            $questcmd = $encode->encode("cmd=club&canshu=questview&sid=$sid");
            $treasuryCmd = $encode->encode("cmd=club&canshu=treasuryview&sid=$sid");
            $bosscmd = $encode->encode("cmd=club&canshu=clubboss&sid=$sid");
            $bossFightCmd = $encode->encode("cmd=clubboss_fight&sid=$sid");
            $clubmenu = "<a class='tcg-club-btn green' href='?cmd=$donatecmd'>💰 Quyên góp</a> <a class='tcg-club-btn blue' href='?cmd=$onlinecmd' onclick='return GameAlert.action(this.href)'>⏰ Online EXP</a> <a class='tcg-club-btn blue' href='?cmd=$questcmd'>📋 Nhiệm vụ</a> <a class='tcg-club-btn blue' href='?cmd=$treasuryCmd'>🏦 Bảo khố</a> <a class='tcg-club-btn purple' href='?cmd=$bossFightCmd'>🐉 Boss</a> <a class='tcg-club-btn danger' href='?cmd=$outclubcmd' onclick='return gameConfirm(\"Bạn chắc chắn muốn thoát phái?\",\"?cmd=$outclubcmd\")'>Thoát Phái</a>";

            if (intval($clubplayer->uclv) == 1) {
                $outclubcmd = $encode->encode("cmd=club&canshu=deleteclub&sid=$sid");
                $renzhicmd = $encode->encode("cmd=club&canshu=renzhi&sid=$sid");
                $upgradeCmd = $encode->encode("cmd=club&canshu=upgrade&sid=$sid");
                $clubmenu = "<a class='tcg-club-btn green' href='?cmd=$donatecmd'>💰 Quyên góp</a> <a class='tcg-club-btn purple' href='?cmd=$upgradeCmd'>⬆ Nâng cấp</a> <a class='tcg-club-btn blue' href='?cmd=$onlinecmd' onclick='return GameAlert.action(this.href)'>⏰ Online</a> <a class='tcg-club-btn blue' href='?cmd=$questcmd'>📋 Nhiệm vụ</a> <a class='tcg-club-btn blue' href='?cmd=$treasuryCmd'>🏦 Bảo khố</a> <a class='tcg-club-btn purple' href='?cmd=$bossFightCmd'>🐉 Boss</a> <a class='tcg-club-btn blue' href='?cmd=$renzhicmd'>Nhậm chức</a> <a class='tcg-club-btn danger' href='?cmd=$outclubcmd' onclick='return gameConfirm(\"Giải tán sẽ xóa toàn bộ tông môn!\",\"?cmd=$outclubcmd\")'>Giải tán</a>";
            } elseif (intval($clubplayer->uclv) == 2) {
                $renzhicmd = $encode->encode("cmd=club&canshu=renzhi&sid=$sid");
                $clubmenu = "<a class='tcg-club-btn green' href='?cmd=$donatecmd'>💰 Quyên góp</a> <a class='tcg-club-btn blue' href='?cmd=$onlinecmd' onclick='return GameAlert.action(this.href)'>⏰ Online</a> <a class='tcg-club-btn blue' href='?cmd=$questcmd'>📋 Nhiệm vụ</a> <a class='tcg-club-btn blue' href='?cmd=$treasuryCmd'>🏦 Bảo khố</a> <a class='tcg-club-btn purple' href='?cmd=$bossFightCmd'>🐉 Boss</a> <a class='tcg-club-btn blue' href='?cmd=$renzhicmd'>Nhậm chức</a> <a class='tcg-club-btn danger' href='?cmd=$outclubcmd' onclick='return gameConfirm(\"Bạn chắc chắn muốn thoát phái?\",\"?cmd=$outclubcmd\")'>Thoát Phái</a>";
            }
        } else {
            $clubmenu = '';
        }
    } else {
        $joincmd = $encode->encode("cmd=club&clubid=$clubid&canshu=joinclub&sid=$sid");
        $clubmenu = "<a class='tcg-club-btn green' href='?cmd=$joincmd'>Xin gia nhập</a>";
    }

    $club = \player\getclub($clubid, $dblj);
    if (!$club) {
        $clublist = $encode->encode("cmd=clublist&sid=$sid");
        $clubhtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-empty">
        <div class="tcg-club-empty-icon">⛩</div>
        <div class="tcg-club-empty-title">Tông môn không tồn tại hoặc đã bị giải tán</div>
        <a class="tcg-club-btn blue" href="?cmd=$clublist">Danh sách tông môn</a>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
        exit($clubhtml);
    }

    $clublist = $encode->encode("cmd=clublist&sid=$sid");

    $clubNo1 = isset($club->clubno1) ? intval($club->clubno1) : 0;
    $cboss = ($clubNo1 > 0) ? \player\getplayer1($clubNo1, $dblj) : false;
    $cbosscmd = $encode->encode("cmd=getplayerinfo&uid=$clubNo1&sid=$sid");

    /* Lấy danh sách thành viên bằng prepare để không lỗi khi clubid rỗng. */
    $stmt = $dblj->prepare("SELECT uid,uclv FROM clubplayer WHERE clubid = ? ORDER BY uclv ASC");
    $stmt->execute(array($clubid));
    $retuid = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $memberCount = 0;
    $pendingCount = 0;
    $masterFallback = false;
    $pendingList = '';
    $isLeader = ($clubplayer && intval($clubplayer->uclv) <= 2);

    foreach ($retuid as $uiditem) {
        $memberUid = intval($uiditem['uid']);
        $uclv = intval($uiditem['uclv']);

        if ($memberUid <= 0) {
            continue;
        }

        $otherplayer = \player\getplayer1($memberUid, $dblj);

        /* Bỏ qua dòng rác: clubplayer có uid nhưng game1 không còn nhân vật. */
        if (!$otherplayer || empty($otherplayer->uid) || empty($otherplayer->uname)) {
            continue;
        }

        if ($uclv == 0) {
            $pendingCount++;
            if ($isLeader) {
                $approveCmd = $encode->encode("cmd=club&canshu=approveclub&uid=$memberUid&sid=$sid");
                $rejectCmd = $encode->encode("cmd=club&canshu=rejectclub&uid=$memberUid&sid=$sid");
                $safeName = htmlspecialchars($otherplayer->uname, ENT_QUOTES, 'UTF-8');
                $pendingList .= <<<HTML
<div class="tcg-club-member pending">
    <span class="tcg-club-rank rank-pending">Chờ duyệt</span>
    <span class="tcg-club-member-name">$safeName</span>
    <span class="tcg-club-pending-actions">
        <a class="tcg-club-btn small green" href="?cmd=$approveCmd" onclick="return GameAlert.action(this.href)">✓ Duyệt</a>
        <a class="tcg-club-btn small danger" href="?cmd=$rejectCmd" onclick="return GameAlert.action(this.href)">✗ Từ chối</a>
    </span>
</div>
HTML;
            }
            continue;
        }

        $memberCount++;

        if ($uclv == 1 && !$cboss) {
            $masterFallback = $otherplayer;
            $clubNo1 = $memberUid;
            $cbosscmd = $encode->encode("cmd=getplayerinfo&uid=$clubNo1&sid=$sid");
        }

        $chenhao = "Đệ Tử Tạp Dịch";
        $rankClass = "rank-normal";

        switch ($uclv) {
            case 1:
                $chenhao = "Chưởng Môn";
                $rankClass = "rank-master";
                break;
            case 2:
                $chenhao = "Phó Chưởng Môn";
                $rankClass = "rank-vice";
                break;
            case 3:
                $chenhao = "Trưởng Lão";
                $rankClass = "rank-elder";
                break;
            case 4:
                $chenhao = "Chấp Sự";
                $rankClass = "rank-manager";
                break;
            case 5:
                $chenhao = "Thánh Tử";
                $rankClass = "rank-saint";
                break;
            case 6:
                $chenhao = "Chân Truyền";
                $rankClass = "rank-true";
                break;
            case 7:
                $chenhao = "Thiên Kiêu";
                $rankClass = "rank-genius";
                break;
            case 8:
                $chenhao = "Tinh Anh";
                $rankClass = "rank-elite";
                break;
            case 9:
                $chenhao = "Nội Môn Đệ Tử";
                $rankClass = "rank-inner";
                break;
            case 10:
                $chenhao = "Ngoại Môn Đệ Tử";
                $rankClass = "rank-outer";
                break;
			case 11:
                $chenhao = "Đệ Tử Tạp Dịch";
                $rankClass = "rank-outer";
                break;	
        }

        $ucmd = $encode->encode("cmd=getplayerinfo&uid=$memberUid&sid=$player->sid");
        $safeName = htmlspecialchars($otherplayer->uname, ENT_QUOTES, 'UTF-8');
        $safeRank = htmlspecialchars($chenhao, ENT_QUOTES, 'UTF-8');

        $playerlist .= <<<HTML
<a class="tcg-club-member" href="?cmd=$ucmd">
    <span class="tcg-club-rank $rankClass">$safeRank</span>
    <span class="tcg-club-member-name">$safeName</span>
</a>
HTML;
    }

    if ($playerlist === '') {
        $playerlist = '<div class="tcg-club-notice">Chưa có thành viên hợp lệ</div>';
    }

    if (!$cboss && $masterFallback) {
        $cboss = $masterFallback;
    }

    $safeClubName = htmlspecialchars($club->clubname, ENT_QUOTES, 'UTF-8');
    $safeClubInfo = htmlspecialchars($club->clubinfo, ENT_QUOTES, 'UTF-8');
    $safeBossName = ($cboss && !empty($cboss->uname)) ? htmlspecialchars($cboss->uname, ENT_QUOTES, 'UTF-8') : 'Chưa rõ';

    $clubLv = max(1, intval($club->clublv));
    $maxMembers = club_max_members($clubLv, $club_benefits_config);
    $buffHP = club_buff_hp($clubLv, $club_benefits_config);
    $buffATK = club_buff_atk($clubLv, $club_benefits_config);
    $buffDEF = club_buff_def($clubLv, $club_benefits_config);
    $buffEXP = club_buff_exp($clubLv, $club_benefits_config);

    $clubhtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-hero">
        <div class="tcg-club-hero-bg"><img src="/images/tongmon/bg_tongmon.png" alt=""></div>
        <div class="tcg-club-hero-mask"></div>
        <div class="tcg-club-hero-main">
            <div class="tcg-club-label">⛩ TÔNG MÔN</div>
            <h1>$safeClubName</h1>
            <div class="tcg-club-desc">$safeClubInfo</div>
            <div class="tcg-club-level-badge">Cấp $clubLv</div>
        </div>

        <div class="tcg-club-master-box">
            <div class="tcg-club-master-title">👑 Chưởng môn</div>
            <a class="tcg-club-master-name" href="?cmd=$cbosscmd">$safeBossName</a>
        </div>
    </div>

    <div class="tcg-club-stats four">
        <div class="tcg-club-stat">
            <span>💎 Linh thạch</span>
            <strong>$club->clubyxb</strong>
        </div>
        <div class="tcg-club-stat">
            <span>🔮 Ma thạch</span>
            <strong>$club->clubczb</strong>
        </div>
        <div class="tcg-club-stat">
            <span>💠 Kim cương</span>
            <strong>$club->clubdiamond</strong>
        </div>
        <div class="tcg-club-stat">
            <span>📈 Xây dựng</span>
            <strong>$club->clubexp</strong>
        </div>
    </div>

    <div class="tcg-club-stats four">
        <div class="tcg-club-stat">
            <span>👥 Thành viên</span>
            <strong>$memberCount / $maxMembers</strong>
        </div>
        <div class="tcg-club-stat">
            <span>🎯 Cấp tông môn</span>
            <strong>$clubLv</strong>
        </div>
    </div>

    <div class="tcg-club-section">
        <div class="tcg-club-section-title">⭐ Lợi Ích Tông Môn Cấp $clubLv</div>
        <div class="tcg-club-benefits">
            <div class="tcg-club-benefit">❤ HP +$buffHP% cho toàn thành viên</div>
            <div class="tcg-club-benefit">⚔ Tấn công +$buffATK% cho toàn thành viên</div>
            <div class="tcg-club-benefit">🛡 Phòng thủ +$buffDEF% cho toàn thành viên</div>
            <div class="tcg-club-benefit">📈 Kinh nghiệm +$buffEXP% khi tu luyện</div>
            <div class="tcg-club-benefit">👥 Max thành viên: $maxMembers người</div>
        </div>
    </div>

    <div class="tcg-club-section">
        <div class="tcg-club-section-title">📈 Cách Kiếm EXP Tông Môn</div>
        <div class="tcg-club-benefits">
            <div class="tcg-club-benefit">💰 Quyên góp LT: 10 LT = 1 EXP cá nhân + 1 EXP tông môn</div>
            <div class="tcg-club-benefit">💰 Quyên góp MT: 1 MT = 1 EXP cá nhân + 1 EXP tông môn</div>
            <div class="tcg-club-benefit">🧘 Tu luyện: +5% EXP nhận được</div>
            <div class="tcg-club-benefit">⏰ Online: +50 EXP/giờ (mỗi thành viên)</div>
            <div class="tcg-club-benefit">📋 Nhiệm vụ: +100~500 EXP/ngày</div>
            <div class="tcg-club-benefit">🐉 Boss: +500 + (50 × số thành viên) EXP/ngày</div>
        </div>
    </div>

    <div class="tcg-club-actions">
        $clubmenu
        <a class="tcg-club-btn blue" href="?cmd=$clublist">Danh sách</a>
    </div>

    $renzhihtml

    $pendingList

    <div class="tcg-club-section">
        <div class="tcg-club-section-title">👥 Thành viên tông môn ($memberCount/$maxMembers)</div>
        <div class="tcg-club-member-grid">
            $playerlist
        </div>
    </div>

    <div class="tcg-club-bottom">
        <a class="tcg-club-back" href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;

    exit($clubhtml);
}

/* Không có clubid hợp lệ và người chơi cũng chưa có tông môn. */
if (!$clubplayer) {
    $clublist = $encode->encode("cmd=clublist&sid=$sid");
    $createclubcmd = $encode->encode("cmd=club&canshu=createclub&sid=$sid");
    $clubhtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-empty">
        <div class="tcg-club-empty-icon">⛩</div>
        <div class="tcg-club-empty-title">Ngươi hiện tại còn chưa có tông môn</div>
        <a class="tcg-club-btn purple" href="?cmd=$createclubcmd">Tạo Tông Môn</a>
		<a class="tcg-club-btn green" href="?cmd=$clublist">Gia nhập tông môn</a>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
} else {
    $clublist = $encode->encode("cmd=clublist&sid=$sid");
    $clubhtml = <<<HTML
<div class="tcg-club-page">
    <div class="tcg-club-empty">
        <div class="tcg-club-empty-icon">⛩</div>
        <div class="tcg-club-empty-title">Không tìm thấy dữ liệu tông môn</div>
        <a class="tcg-club-btn blue" href="?cmd=$clublist">Danh sách tông môn</a>
    </div>
    <div class="tcg-club-bottom">
        <a class="tcg-club-home" href="?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
}

echo $clubhtml;

} /* end if ($canshu === '') */
?>

<script>
function gameConfirm(msg, url) {
    if (typeof GameAlert !== 'undefined') {
        GameAlert.confirm(msg, function() {
            GameAlert.action(url);
        });
        return false;
    }
    return confirm(msg);
}
</script>
