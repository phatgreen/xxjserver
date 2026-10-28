<?php
/* ================== LOAD PLAYER ================== */
$player  = \player\getplayer($sid, $dblj);
$nowdate = date('Y-m-d H:i:s');

/* ================== LINK ================== */
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
require_once __DIR__ . '/../class/function/club_quest.php';
$strxl0   = $encode->encode("cmd=startxiulian&canshu=1&sid=$sid"); // Thường
$strxl1   = $encode->encode("cmd=startxiulian&canshu=2&sid=$sid"); // Linh Thạch x2
$strxl2   = $encode->encode("cmd=startxiulian&canshu=3&sid=$sid"); // Ma Thạch x3
$endxl    = $encode->encode("cmd=endxiulian&sid=$sid");

/* ================== XÁC ĐỊNH LOẠI TU LUYỆN ================== */
if ($cmd == 'startxiulian') {
    $current_type = (int)$canshu;
} elseif ((int)$player->sfxl > 0) {
    $current_type = (int)$player->sfxl;
} else {
    // Khi đang nhàn rỗi thì theo nút chế độ người chơi vừa chọn (?mode=2)
    $current_type = (isset($mode) && in_array((int)$mode, array(1, 2, 3), true)) ? (int)$mode : 1;
}

function xl_rate_text($value) {
    $value = round(floatval($value), 2);
    $text = number_format($value, 2, '.', '');
    return rtrim(rtrim($text, '0'), '.');
}

/* Ten canh gioi luon di 1 dong: phan chu thich dai nam sau <br/> (chi co o trang thai
   Vien Man) tach ra thanh dong rieng, giu nguyen noi dung va mau cua getJingJieHtml(). */
function xl_realm_label($raw) {
    $parts = preg_split('/<br\s*\/?>/i', (string)$raw, 2);
    $main = preg_replace('/\s+/', ' ', (string)$parts[0]);
    $note = '';
    if (count($parts) > 1) {
        $note = trim(preg_replace('/\s+/', ' ', strip_tags($parts[1])));
        $note = trim($note, " ()\t\n\r\0\x0B");
    }
    return array('main' => $main, 'note' => $note);
}

// Số gọn kiểu 11.96M / 239.4K / 1.2B
function xl_num($value) {
    $value = floatval($value);
    if (abs($value) >= 1000000000) {
        $text = number_format($value / 1000000000, 2, '.', '');
        return rtrim(rtrim($text, '0'), '.') . 'B';
    }
    if (abs($value) >= 1000000) {
        $text = number_format($value / 1000000, 2, '.', '');
        return rtrim(rtrim($text, '0'), '.') . 'M';
    }
    if (abs($value) >= 1000) {
        $text = number_format($value / 1000, 2, '.', '');
        return rtrim(rtrim($text, '0'), '.') . 'K';
    }
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}

function xl_time($seconds) {
    $seconds = max(0, intval($seconds));
    if ($seconds < 60) return $seconds . 's';
    $min = intval(floor($seconds / 60));
    if ($min < 60) return $min . "'" . str_pad($seconds % 60, 2, '0', STR_PAD_LEFT) . '"';
    return intval(floor($min / 60)) . 'h' . str_pad($min % 60, 2, '0', STR_PAD_LEFT) . "'";
}

/* ================== CONFIG & HỆ SỐ ================== */
$vip_bonus = ($player->vip > 0) ? 1.10 : 1.00;
$pb_reduce = ($player->tool11 > 0) ? 0.10 : 0;

// Thiết lập thông số theo từng loại
$exp_multiplier = 1;
$cost_per_min   = 0;
$resource_name  = 0; // Mặc định là miễn phí
$bar_color      = "#4caf50"; // Mặc định xanh lá

switch ($current_type) {
    case 2: // Linh Thạch x2
        $exp_multiplier = 2;
        $cost_per_min   = ceil(max(1, ceil(pow($player->ulv, 1.15) / 5)) * (1 - $pb_reduce));
        $resource_name  = "Linh thạch";
        $bar_color      = "#2196f3"; // Xanh dương
        break;
    case 3: // Ma Thạch x3
        $exp_multiplier = 3;
        $cost_per_min   = max(1, ceil($player->ulv / 3));
        $resource_name  = "Ma thạch";
        $bar_color      = "#9c27b0"; // Tím
        break;
    default: // Thường x1
        $exp_multiplier = 1;
        $cost_per_min   = 0;
        $resource_name  = "Miễn phí";
        $bar_color      = "#4caf50"; 
        break;
}


$cp_absorb = isset($player->cp_absorb_speed) ? floatval($player->cp_absorb_speed) : 0;
$lc_absorb = isset($player->linhcan_absorb) ? floatval($player->linhcan_absorb) : 0;
$lc_exp_bonus = isset($player->linhcan_exp_bonus) ? floatval($player->linhcan_exp_bonus) : 0;
$tc_exp_bonus = isset($player->thechat_passive_exp) ? floatval($player->thechat_passive_exp) : 0;
$tp_exp_bonus = isset($player->tianfu_bonus['exp_cultivate']) ? floatval($player->tianfu_bonus['exp_cultivate']) : 0;

// Breakthrough prompt
$tupoCmd = $encode->encode("cmd=tupo&sid=$sid");
$tpts = ($player->uexp >= $player->umaxexp && $player->umaxexp > 0)
    ? '<a href="?cmd=' . $tupoCmd . '" style="color:#ff9800;font-weight:800">💥 Đột Phá Cảnh Giới</a>'
    : '';
$base_exp_per_sec = round($player->ulv * $vip_bonus * $exp_multiplier * min(1.3, 1 + $cp_absorb / 100) * min(1.3, 1 + $lc_absorb / 100) * min(1.3, 1 + $lc_exp_bonus / 100) * min(1.5, 1 + $tc_exp_bonus / 100), 4); // linh khí gốc mỗi giây
$equip_bonus_percent = min(30, max(0, floatval($player->tupoexp))); // trần 30% như các buff tu luyện khác
$exp_per_sec = round($base_exp_per_sec * (1 + $equip_bonus_percent / 100) * (1 + $tp_exp_bonus / 100), 4); // linh khí thực nhận mỗi giây (Thiên Phú nhân NGOÀI chuỗi min() — Task 11 [CHOT])
$base_exp_per_min = round($base_exp_per_sec * 60, 2);
$exp_per_min = round($exp_per_sec * 60, 2);
$base_exp_rate_text = xl_rate_text($base_exp_per_sec);
$real_exp_rate_text = xl_rate_text($exp_per_sec);

/* ================== THỜI GIAN TỐI ĐA (MAX TIME) ================== */
if ($current_type == 2) {
	// Linh thạch max 3 tiếng
    $real_max_min = ($cost_per_min > 0) ? min(180, floor($player->uyxb / $cost_per_min)) : 180;
} elseif ($current_type == 3) {
	// Ma Thạch max 6 tiếng
    $real_max_min = ($cost_per_min > 0) ? min(360, floor($player->uczb / $cost_per_min)) : 360;
} else {
    $real_max_min = 30; // Tối đa 30 phút cho loại thường
}

/* ================== LOGIC XỬ LÝ ================== */
$xl_running_text = '<span class="tu-luyen">
			  <span style="animation-delay:0s;color:#A0A000">H</span>
			  <span style="animation-delay:0.05s;color:#A0A000">ấ</span>
			  <span style="animation-delay:0.1s;color:#A0A000">p</span>
			  <span style="animation-delay:0.15s;color:#A0A000"> </span>
			  <span style="animation-delay:0.2s;color:#F5A000">T</span>
			  <span style="animation-delay:0.25s;color:#F5A000">h</span>
			  <span style="animation-delay:0.3s;color:#F5A000">u</span>
			  <span style="animation-delay:0.35s;color:#FFA000"> </span>
			  <span style="animation-delay:0.4s;color:#FFA000">L</span>
			  <span style="animation-delay:0.45s;color:#FFA000">i</span>
			  <span style="animation-delay:0.5s;color:#FFA000">n</span>
			  <span style="animation-delay:0.55s;color:#FFA000">h</span>
			  <span style="animation-delay:0.6s;color:#FFA000"> </span>
			  <span style="animation-delay:0.65s;color:#FFA000">K</span>
			  <span style="animation-delay:0.70s;color:#FFA000">h</span>
			  <span style="animation-delay:0.75s;color:#FFA000">í</span>
			  <span style="animation-delay:0.8s;color:#FFA000">.</span>
			  <span style="animation-delay:0.85s;color:#FFA000">.</span>
			  <span style="animation-delay:0.9s;color:#FFA000">.</span>
			</span>
			<br/>';
$tishi = 'Chưa bắt đầu tu luyện';
$xlsjc = 0; $xlsec = 0; $xlexp = 0; $gain_exp = 0;

// Bắt đầu tu luyện
if ($cmd == 'startxiulian') {
    if ($player->sfxl > 0) {
        $tishi = $xl_running_text;
    } elseif ($real_max_min <= 0 && $current_type > 1) {
        $tishi = '<span style="color:red">Không đủ '.$resource_name.' để bắt đầu!</span>';
    } else {
        \player\changeplayersx('sfxl', $current_type, $sid, $dblj);
        \player\changeplayersx('xiuliantime', $nowdate, $sid, $dblj);
        $player = \player\getplayer($sid, $dblj);
        $tishi = $xl_running_text;
    }
}
// Đang chạy
if ($player->sfxl > 0 && !empty($player->xiuliantime)) {
    $tishi = $xl_running_text;
    $diff  = min(86400, max(0, strtotime($nowdate) - strtotime($player->xiuliantime)));
    $xlsjc = floor($diff / 60);
    $xlsec = $diff % 60;
    $xlexp = round(($diff / 60) * $exp_per_min, 2);

    if ($real_max_min > 0 && $xlsjc >= $real_max_min) {
        $cmd = 'endxiulian';
    }
}
//echo $exp_per_min;
// Kết thúc
if ($cmd == 'endxiulian' && $player->sfxl > 0) {
    $real_min = min($xlsjc, $real_max_min);
	// 🚫 Chưa đủ 1s → KHÔNG DỪNG
    if ($diff < 5) {
        $tishi = '<span style="color:red">Tu luyện tối thiểu 5 giây để tránh tẩu hỏa nhập ma!</span>';
    } else {
		 // ===== Trừ tài nguyên =====
        $minutes = max(1, $diff / 60);
        $cost = round($minutes * $cost_per_min);
        if ($player->sfxl == 2) \player\changeyxb(2, $cost, $sid, $dblj);
        if ($player->sfxl == 3) \player\changeczb(2, $cost, $sid, $dblj);
        
		// ===== Cộng tu vi =====
        $gain_exp = $diff >= 60 ? min($real_min * ($exp_per_sec * 60), $player->umaxexp) : min($diff * $exp_per_sec, $player->umaxexp); //round($real_min * $exp_per_min, 2);
        
        // Giảm EXP do đan độc (>100 toxin = -0.1% mỗi 10 điểm)
        $toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
        if ($toxinPenalty > 0) {
            $gain_exp = max(1, intval($gain_exp * (1 - $toxinPenalty / 100)));
        }
        
        \player\changeexp($sid, $dblj, $gain_exp);
        \player\changeplayersx('sfxl', 0, $sid, $dblj);

        // ===== Cộng EXP tông môn (5% tu vi nhận được) =====
        $clubPlayer = \player\getclubplayer_once($sid, $dblj);
        if ($clubPlayer && intval($clubPlayer->uclv) > 0) {
            $clubExpGain = intval($gain_exp * 0.05);
            if ($clubExpGain > 0) {
                $stmt = $dblj->prepare("UPDATE club SET clubexp = clubexp + ? WHERE clubid = ?");
                $stmt->execute(array($clubExpGain, intval($clubPlayer->clubid)));
            }
        }

        /* Hook quest: meditate (phút) */
        $medMin = max(1, intval($diff / 60));
        \player\club_quest_update($sid, 'meditate', $medMin, $dblj);

        if (function_exists('player\\nhiemvu_update_all')) {
            \player\nhiemvu_update_all($sid, 'cultivate', $medMin, $dblj, array());
        }

        $tishi = "Nhận được <b style='color:#ff9800'>$gain_exp</b> tu vi sau $xlsjc phút $xlsec giây.<br>" . (($cost_per_min > 0) ? ("Tiêu hao: " . (ceil($diff / 60) * $cost_per_min) . " " . $resource_name) : "Tiêu hao: " . $resource_name);
        $player = \player\getplayer($sid, $dblj);
    }
}
/* ================== GIAO DIỆN (UI) ================== */

$progress     = ($real_max_min > 0) ? min(100, round(($xlsjc / $real_max_min) * 100, 2)) : 0;
$exp_progress = ($player->umaxexp > 0) ? min(100, round(($player->uexp / $player->umaxexp) * 100, 2)) : 0;
$active_type  = (int)$player->sfxl > 0 ? (int)$player->sfxl : (int)$current_type;
$running      = (int)$player->sfxl > 0;
$break_ready  = ($player->umaxexp > 0 && $player->uexp >= $player->umaxexp);

$chien_luc  = \player\combat_power($player);
$absorb_total = floatval($cp_absorb) + floatval($lc_absorb) + floatval($lc_exp_bonus);
$break_left = ($exp_per_sec > 0 && $player->umaxexp > $player->uexp)
    ? ceil(($player->umaxexp - $player->uexp) / $exp_per_sec)
    : 0;

/* Link tới các hệ thống THẬT đang có trong game */
$nav = array();
$nav_names = array(
    'gocp', 'linhcan', 'thechat', 'huyetmach', 'luonden',
    'trangbi', 'chetao', 'wgxiulian', 'xxwg', 'tianfu',
    'zhuangtai', 'allmap', 'club', 'nhiemvu', 'shangdian', 'getbagyp', 'tupo', 'goxiulian'
);
foreach ($nav_names as $nav_name) {
    $nav[$nav_name] = '?cmd=' . $encode->encode("cmd=$nav_name&sid=$sid");
}

/* Nút hành động chính: đang tu luyện thì DỪNG, tu vi viên mãn thì ĐỘT PHÁ, còn lại BẮT ĐẦU */
$orb_break = (!$running && $break_ready);
if ($running) {
    $orb_href = '?cmd=' . $endxl;
} elseif ($orb_break) {
    $orb_href = '?cmd=' . $tupoCmd;
} elseif ($active_type == 2) {
    $orb_href = '?cmd=' . $strxl1;
} elseif ($active_type == 3) {
    $orb_href = '?cmd=' . $strxl2;
} else {
    $orb_href = '?cmd=' . $strxl0;
}

/* Cột trái = tu thân, cột phải = trui rèn */
$rail_left = array(
    array('gocp', '📜', 'Công Pháp'),
    array('linhcan', '🌱', 'Linh Căn'),
    array('thechat', '🧎', 'Thể Chất'),
    array('huyetmach', '🩸', 'Huyết Mạch'),
    array('luonden', '🔥', 'Luyện Đan')
);
$rail_right = array(
    array('trangbi', '🗡️', 'Trang Bị'),
    array('chetao', '🔨', 'Chế Tạo'),
    array('wgxiulian', '🥋', 'Võ Công'),
    array('xxwg', '📖', 'Bí Tịch'),
    array('tianfu', '🌟', 'Thiên Phú')
);

/* Chip hai bên orb: khi orb đã là ĐỘT PHÁ thì tách đôi 2 nút đan cho cân */
if ($orb_break) {
    $chips_left = array(array('getbagyp', '💊', 'Dùng Đan'));
    $chips_right = array(array('luonden', '🔥', 'Lò Đan'));
} else {
    $chips_left = array(array('getbagyp', '💊', 'Dùng Đan'), array('luonden', '🔥', 'Lò Đan'));
    $chips_right = array(array('tupo', '💥', 'Đột Phá', ' xl-side-break'));
}

$mode_names = array(1 => 'Thường', 2 => 'Linh Thạch x2', 3 => 'Ma Thạch x3');
$realm_label = xl_realm_label($player->jingjie);
?>
<div class="xl-page">

    <div class="xl-stage">
        <div class="xl-stage-sky"></div>

        <div class="xl-rail xl-rail-left">
            <?php foreach ($rail_left as $item): ?>
            <?php $lk = \player\feature_lock_mark($player, $item[0]); ?>
            <a class="xl-node<?= $lk[0] ?>" href="<?= $nav[$item[0]] ?>"><i><?= $item[1] ?></i><b><?= $item[2] ?></b><?= $lk[1] ?></a>
            <?php endforeach; ?>
        </div>

        <div class="xl-rail xl-rail-right">
            <?php foreach ($rail_right as $item): ?>
            <?php $lk = \player\feature_lock_mark($player, $item[0]); ?>
            <a class="xl-node<?= $lk[0] ?>" href="<?= $nav[$item[0]] ?>"><i><?= $item[1] ?></i><b><?= $item[2] ?></b><?= $lk[1] ?></a>
            <?php endforeach; ?>
        </div>

        <div class="xl-figure">
            <img class="xl-figure-art" src="./images/xiulian/nen-ngoi-thien.jpg" alt="Ngồi thiền hút linh khí">
            <div class="xl-figure-ring"></div>
        </div>

        <div class="xl-power">
            <span>Chiến Lực</span><b><?= xl_num($chien_luc) ?></b>
        </div>

        <div class="xl-res">
            <div class="xl-res-chip"><span>💰 Linh thạch</span><b id="yxb_val"><?= number_format($player->uyxb) ?></b></div>
            <div class="xl-res-chip"><span>💎 Ma thạch</span><b id="czb_val"><?= number_format($player->uczb) ?></b></div>
        </div>
    </div>

  <div class="xl-grid">
    <div class="xl-col xl-col-left">
    <div class="xl-realm">
        <img class="xl-realm-frame" src="./images/xiulian/banner-lua.png" alt="">
        <div class="xl-realm-name"><?= $realm_label['main'] ?> <span><?= $player->cengci ?></span></div>
        <?php if ($realm_label['note'] != ''): ?>
        <div class="xl-realm-note"><?= $realm_label['note'] ?></div>
        <?php endif; ?>

        <div class="xl-exp">
            <div class="xl-exp-track">
                <div id="xlprogress" class="xl-exp-fill" style="width:<?= $exp_progress ?>%;--xl-bar:<?= $bar_color ?>"></div>
                <div class="xl-exp-pct"><span id="xlprogress_pct"><?= number_format($exp_progress, 1, '.', '') ?></span>%</div>
            </div>
            <div id="xlprogress_text" class="xl-exp-text"><?= number_format($player->uexp) ?> / <?= number_format($player->umaxexp) ?></div>
        </div>
    </div>

    <div class="xl-modes">
        <?php foreach ($mode_names as $mode_id => $mode_label): ?>
            <?php if ($running): ?>
                <span class="xl-mode<?= ($active_type == $mode_id) ? ' on' : '' ?>"><?= $mode_label ?></span>
            <?php else: ?>
                <a class="xl-mode<?= ($active_type == $mode_id) ? ' on' : '' ?>" href="?cmd=<?= $encode->encode("cmd=goxiulian&mode=$mode_id&sid=$sid") ?>"><?= $mode_label ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    </div>

    <div class="xl-col xl-col-right">
    <div class="xl-stats">
        <div class="xl-stat"><span>Tu luyện</span><b><?= $real_exp_rate_text ?>/s</b></div>
        <div class="xl-stat"><span>Đột phá</span><b id="xl_eta"><?= $break_ready ? 'Sẵn sàng' : xl_time($break_left) ?></b></div>
        <div class="xl-stat"><span>Tấn Công</span><b><?= xl_num($player->ugj) ?></b></div>
        <div class="xl-stat"><span>Phòng Thủ</span><b><?= xl_num($player->ufy) ?></b></div>
        <div class="xl-stat"><span>Khí Huyết</span><b><?= xl_num($player->umaxhp) ?></b></div>
        <div class="xl-stat"><span>Bạo Kích</span><b><?= xl_num($player->ubj) ?></b></div>
        <div class="xl-stat"><span>Hút Máu</span><b><?= xl_num($player->uxx) ?></b></div>
        <div class="xl-stat"><span>Trang bị</span><b class="xl-plus">+<?= xl_rate_text($equip_bonus_percent) ?>%</b></div>
        <div class="xl-stat"><span>Hấp thụ</span><b class="<?= $absorb_total >= 0 ? 'xl-plus' : 'xl-minus' ?>"><?= ($absorb_total >= 0 ? '+' : '') ?><?= xl_rate_text($absorb_total) ?>%</b></div>
    </div>
    </div>
  </div>

    <div class="xl-action">
        <div class="xl-action-side xl-action-side-left">
            <?php foreach ($chips_left as $chip): ?>
            <?php $lk = \player\feature_lock_mark($player, $chip[0]); ?>
            <a class="xl-side-btn<?= isset($chip[3]) ? $chip[3] : '' ?><?= $lk[0] ?>" href="<?= $nav[$chip[0]] ?>"><i><?= $chip[1] ?></i><b><?= $chip[2] ?></b><?= $lk[1] ?></a>
            <?php endforeach; ?>
        </div>

        <a class="xl-orb<?= $running ? ' running' : '' ?><?= $orb_break ? ' break' : '' ?>" href="<?= $orb_href ?>">
            <span class="xl-orb-ring"></span>
            <span class="xl-orb-core">
                <em><?= $running ? 'DỪNG' : ($orb_break ? 'ĐỘT PHÁ' : 'TU LUYỆN') ?></em>
                <small><?= $running ? 'thu hồi linh khí' : ($orb_break ? 'tu vi viên mãn' : 'hấp thu linh khí') ?></small>
            </span>
            <?php if (!$orb_break): ?>
            <span class="xl-orb-meta"><b id="xlexp"><?= number_format($xlexp, 2) ?></b> tu vi</span>
            <?php endif; ?>
        </a>

        <div class="xl-action-side xl-action-side-right">
            <?php foreach ($chips_right as $chip): ?>
            <?php $lk = \player\feature_lock_mark($player, $chip[0]); ?>
            <a class="xl-side-btn<?= isset($chip[3]) ? $chip[3] : '' ?><?= $lk[0] ?>" href="<?= $nav[$chip[0]] ?>"><i><?= $chip[1] ?></i><b><?= $chip[2] ?></b><?= $lk[1] ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="xl-status">
        <div class="xl-status-clock">
            <span>Đã ngồi</span>
            <b><span id="xlsjc"><?= $xlsjc ?></span>'<span id="xlsec"><?= str_pad($xlsec, 2, '0', STR_PAD_LEFT) ?></span>s</b>
        </div>
        <div class="xl-status-text">
            <?php if ($break_ready && $tpts != ''): ?>
                <b class="xl-warn">Tu vi viên mãn — nhấn ĐỘT PHÁ</b>
            <?php elseif ($running): ?>
                <b><?= $xl_running_text ?></b>
            <?php else: ?>
                <b><?= $tishi ?></b>
            <?php endif; ?>
        </div>
        <div class="xl-status-cost">
            <?= $running ? 'Đang tiêu' : 'Tiêu hao' ?>
            <b><?= ($cost_per_min > 0 ? $cost_per_min . ' ' . $resource_name . '/phút' : 'Miễn phí') ?></b>
            · Giới hạn <b><?= $real_max_min ?>'</b>
        </div>
    </div>

    <nav class="xl-tabbar">
        <a class="on" href="<?= $nav['goxiulian'] ?>"><i>🧘</i><b>Tu Luyện</b></a>
        <a href="<?= $nav['zhuangtai'] ?>"><i>👤</i><b>Nhân Vật</b></a>
        <?php $lk = \player\feature_lock_mark($player, 'allmap'); ?><a class="<?= trim($lk[0]) ?>" href="<?= $nav['allmap'] ?>"><i>🗺</i><b>Bản Đồ</b><?= $lk[1] ?></a>
        <?php $lk = \player\feature_lock_mark($player, 'club'); ?><a class="<?= trim($lk[0]) ?>" href="<?= $nav['club'] ?>"><i>🏯</i><b>Tông Môn</b><?= $lk[1] ?></a>
        <a href="<?= $nav['nhiemvu'] ?>"><i>📜</i><b>Nhiệm Vụ</b></a>
        <?php $lk = \player\feature_lock_mark($player, 'shangdian'); ?><a class="<?= trim($lk[0]) ?>" href="<?= $nav['shangdian'] ?>"><i>🛒</i><b>Cửa Hàng</b><?= $lk[1] ?></a>
    </nav>
</div>

<script>
(function () {
    function xlNum(v) {
        v = Number(v) || 0;
        var a = Math.abs(v), t;
        if (a >= 1000000000) { t = (v / 1000000000).toFixed(2); return t.replace(/\.?0+$/, '') + 'B'; }
        if (a >= 1000000) { t = (v / 1000000).toFixed(2); return t.replace(/\.?0+$/, '') + 'M'; }
        if (a >= 1000) { t = (v / 1000).toFixed(2); return t.replace(/\.?0+$/, '') + 'K'; }
        t = v.toFixed(2);
        return t.replace(/\.?0+$/, '');
    }
    function put(id, txt) {
        var el = document.getElementById(id);
        if (el) el.innerText = txt;
    }
    function clock(sec) {
        sec = Math.max(0, Math.floor(sec));
        if (sec < 60) return sec + 's';
        var m = Math.floor(sec / 60);
        if (m < 60) return m + "'" + ('0' + (sec % 60)).slice(-2) + '"';
        return Math.floor(m / 60) + 'h' + ('0' + (m % 60)).slice(-2) + "'";
    }

    var maxExp = <?= (float)$player->umaxexp ?>;
    var bar = document.getElementById('xlprogress');

    // Nhat canh gioi 1 dong: do bang Range vi scrollWidth khong phat hien chu nowrap
    // tran ra ngoai the. San 13px; neu van thua thi ngat dong, khong de tran the.
    function fitRealm() {
        var nm = document.querySelector('.xl-realm-name');
        if (!nm || !document.createRange) return;
        nm.style.fontSize = '';
        nm.style.whiteSpace = '';
        var st = window.getComputedStyle(nm);
        var avail = nm.clientWidth - (parseFloat(st.paddingLeft) || 0) - (parseFloat(st.paddingRight) || 0);
        var range = document.createRange();
        range.selectNodeContents(nm);
        var textW = function () { return range.getBoundingClientRect().width; };
        var fs = parseFloat(st.fontSize) || 16;
        while (textW() > avail && fs > 13) {
            fs -= 0.5;
            nm.style.fontSize = fs + 'px';
        }
        if (textW() > avail) {
            nm.style.fontSize = '';
            nm.style.whiteSpace = 'normal';
        }
    }
    fitRealm();
    if (window.addEventListener) window.addEventListener('resize', fitRealm);

    function paint(exp) {
        var pct = maxExp > 0 ? Math.min(100, exp * 100 / maxExp) : 0;
        if (bar) bar.style.width = pct + '%';
        put('xlprogress_pct', pct.toFixed(1));
        put('xlprogress_text', Math.floor(exp).toLocaleString() + ' / ' + Math.round(maxExp).toLocaleString());
        return pct;
    }
<?php if ($running) { ?>
    var currentExp = <?= (float)($player->uexp + $diff * $exp_per_sec) ?>;
    var expPerSec  = <?= (float)$exp_per_sec ?>;
    var totalSec   = <?= (int)$diff ?>;
    var maxSec     = <?= (int)$real_max_min ?> * 60;
    var sfxlType   = <?= (int)$player->sfxl ?>;
    var curRes     = <?php
        if ($player->sfxl == 2) echo (float)$player->uyxb;
        elseif ($player->sfxl == 3) echo (float)$player->uczb;
        else echo 0;
    ?>;
    var resPerSec  = <?= (float)$cost_per_min ?> / 60;
    var endUrl     = '?cmd=<?= $endxl ?>';

    var timer = setInterval(function () {
        totalSec++;
        currentExp += expPerSec;

        put('xlsjc', Math.floor(totalSec / 60));
        put('xlsec', ('0' + (totalSec % 60)).slice(-2));
        put('xlexp', (totalSec / 60 * <?= (float)$exp_per_min ?>).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        paint(currentExp);
        put('xl_eta', currentExp >= maxExp ? 'Sẵn sàng' : clock((maxExp - currentExp) / expPerSec));

        if (sfxlType > 1) {
            curRes -= resPerSec;
            put(sfxlType == 2 ? 'yxb_val' : 'czb_val', Math.max(0, Math.floor(curRes)).toLocaleString());
        }

        if (currentExp >= maxExp || (maxSec > 0 && totalSec >= maxSec) || (sfxlType > 1 && curRes <= 0)) {
            clearInterval(timer);
            window.location.href = endUrl;
        }
    }, 1000);
<?php } else { ?>
    paint(<?= (float)$player->uexp ?>);
<?php } ?>
})();
</script>
