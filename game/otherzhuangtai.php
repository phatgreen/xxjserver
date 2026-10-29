<?php
$player = player\getplayer($sid, $dblj);
$player1 = player\getplayer1($uid, $dblj);

if (!function_exists('tcg_other_e')) {
    function tcg_other_e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tcg_other_clean_text')) {
    function tcg_other_clean_text($value) {
        $value = (string)$value;
        $value = str_replace(array('<br>', '<br/>', '<br />'), ' ', $value);
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }
}


if (!function_exists('tcg_other_color_value')) {
    function tcg_other_color_value($color) {
        $color = trim((string)$color);
        $color = html_entity_decode($color, ENT_QUOTES, 'UTF-8');
        $color = str_replace(array('"', "'"), '', $color);

        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            return $color;
        }

        if (preg_match('/^rgba?\([0-9\s,.%]+\)$/i', $color)) {
            return $color;
        }

        if (preg_match('/^[a-zA-Z]+$/', $color)) {
            return $color;
        }

        return '#f1f7ff';
    }
}

if (!function_exists('tcg_other_jingjie_html')) {
    function tcg_other_jingjie_html($value) {
        $value = (string)$value;
        $value = str_replace(array('<br>', '<br/>', '<br />'), ' ', $value);
        $parts = array();

        $value = preg_replace_callback('/<font\b[^>]*color\s*=\s*["\']?([^"\' >]+)["\']?[^>]*>(.*?)<\/font>/is', function($m) use (&$parts) {
            $color = tcg_other_color_value($m[1]);
            $text = htmlspecialchars(trim(strip_tags($m[2])), ENT_QUOTES, 'UTF-8');
            $key = '%%TCG_JJ_' . count($parts) . '%%';
            $parts[$key] = '<span class="tcg-jingjie-color" style="color:' . $color . '">' . $text . '</span>';
            return $key;
        }, $value);

        $value = preg_replace_callback('/<span\b[^>]*style\s*=\s*["\'][^"\']*color\s*:\s*([^;"\']+)[^"\']*["\'][^>]*>(.*?)<\/span>/is', function($m) use (&$parts) {
            $color = tcg_other_color_value($m[1]);
            $text = htmlspecialchars(trim(strip_tags($m[2])), ENT_QUOTES, 'UTF-8');
            $key = '%%TCG_JJ_' . count($parts) . '%%';
            $parts[$key] = '<span class="tcg-jingjie-color" style="color:' . $color . '">' . $text . '</span>';
            return $key;
        }, $value);

        $value = htmlspecialchars(trim(strip_tags($value)), ENT_QUOTES, 'UTF-8');

        foreach ($parts as $key => $html) {
            $value = str_replace($key, $html, $value);
        }

        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }
}

if (!function_exists('tcg_other_equipment_slot')) {
    function tcg_other_equipment_slot($slotName, $icon, $toolId, $viewUid, $sid, $encode, $dblj) {
        if (intval($toolId) == 0) {
            return "<div class='tcg-profile-equip empty'><div class='tcg-profile-equip-label'>{$icon} {$slotName}</div><div class='tcg-profile-equip-empty'>Chưa trang bị</div></div>";
        }

        $zhuangbei = player\getzb($toolId, $dblj);
        $qhs = '';
        if (isset($zhuangbei->qianghua) && $zhuangbei->qianghua > 0) {
            $qhs = '+' . intval($zhuangbei->qianghua);
        }

        $zbcmd = $encode->encode("cmd=chakanzb&zbnowid=$toolId&uid=$viewUid&sid=$sid");
        $zbys = isset($zhuangbei->zbys) ? $zhuangbei->zbys : '#d9e5ff';
        $zbname = isset($zhuangbei->zbname) ? tcg_other_e($zhuangbei->zbname) : 'Trang bị';
        $zbImg = player\zhuangbei_image_path($zhuangbei, player\zhuangbei_slot_icon(isset($zhuangbei->tool) ? $zhuangbei->tool : 0));

        // Thẻ trang bị: ảnh vật phẩm lớn làm trọng tâm (viền + phát sáng theo phẩm chất), +cường hóa trên góc ảnh
        $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
        $raColor = player\zhuangbei_rarity_color($zbRa);
        $raName = tcg_other_e(player\zhuangbei_rarity_name($zbRa));
        $qhBadge = $qhs !== '' ? "<b class='tcg-eq-qh'>{$qhs}</b>" : '';
        return "<a class='tcg-profile-equip tcg-eq-card' href='?cmd={$zbcmd}' style='--ra:{$raColor}'>
                    <span class='tcg-eq-art'><img src='{$zbImg}' alt=''>{$qhBadge}</span>
                    <span class='tcg-eq-info'>
                        <span class='tcg-eq-slot'>{$icon} {$slotName}</span>
                        <span class='tcg-eq-name' style='color:{$zbys}'>{$zbname}</span>
                        <span class='tcg-eq-ra'>{$raName}</span>
                    </span>
                </a>";
    }
}

$immenu = '';
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$pkcmd = $encode->encode("cmd=pvp&uid=$uid&sid=$sid");

$clubplayer = \player\getclubplayer_once($player1->sid, $dblj);

// Hảo Hữu mở theo cấp (feature_unlock.json): chưa mở thì không cho kết giao qua trang này
$imLock = \player\feature_lock_mark($player, 'im');
if (isset($canshu)) {
    if ($canshu == 'addim' && $imLock[0] === '') {
        \player\addim($uid, $sid, $dblj);
    }
}

if ($clubplayer) {
    $club = \player\getclub($clubplayer->clubid, $dblj);
    $clubcmd = $encode->encode("cmd=club&clubid=$club->clubid&sid=$sid");
    $clubname = "<a class='tcg-profile-link' href='?cmd=$clubcmd'>" . tcg_other_e($club->clubname) . "</a>";
} else {
    $clubname = "<span class='tcg-profile-muted'>Không môn phái</span>";
}

if ($player->sid != $player1->sid) {
    $immenu = "<div class='tcg-profile-social'>";
    $immenu .= "<a class='tcg-profile-action danger' href='?cmd=$pkcmd'>⚔️ Tấn Công</a>";

    $ret = \player\isim($uid, $sid, $dblj);
    if (!$ret) {
        $addim = $encode->encode("cmd=getplayerinfo&canshu=addim&uid=$uid&sid=$sid");
        $immenu .= "<a class='tcg-profile-action friend{$imLock[0]}' href='?cmd=$addim'>🤝 Kết Giao{$imLock[1]}</a>";
    } else {
        $deim = $encode->encode("cmd=im&canshu=deim&uid=$uid&sid=$sid");
        $immenu .= "<a class='tcg-profile-action remove' href='?cmd=$deim'>🗑 Xóa Bạn</a>";
        $immenu .= <<<HTML
        <form class="tcg-profile-private-form">
            <input type="hidden" name="cmd" value="sendliaotian">
            <input type="hidden" name="ltlx" value="im">
            <input type="hidden" name="sid" value="$sid">
            <input type="hidden" name="imuid" value="$uid">
            <input class="tcg-profile-private-input" name="ltmsg" maxlength="50" placeholder="Nhập tin nhắn riêng...">
            <input class="tcg-profile-private-submit" type="submit" value="Gửi">
        </form>
HTML;
    }
    $immenu .= "</div>";
}

$tishi = '';
$wgid = isset($player1->wugong) ? intval($player1->wugong) : 0;
if ($wgid != 0) {
    $cxwg = \player\wgcx($wgid, $player1->sid, $dblj);
    if ($cxwg && isset($cxwg->wglx) && intval($cxwg->wglx) == 0) {
        $wgys = isset($cxwg->wgys) ? $cxwg->wgys : '#ff5577';
        $wgname = isset($cxwg->wgname) ? tcg_other_e($cxwg->wgname) : '';
        $tishi = " <span class='tcg-profile-wugong' style='color:$wgys'>$wgname</span>";
    }
}

$cwinfo = $encode->encode("cmd=chongwu&cwid=$player1->cw&canshu=cwinfo&sid=$sid");

$equipHtml = '';
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(1), '⚔️', $player1->tool1, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(2), '🛡️', $player1->tool2, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(3), '👑', $player1->tool3, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(4), '🥋', $player1->tool4, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(5), '🧤', $player1->tool5, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(6), '💍', $player1->tool6, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(7), '🪢', $player1->tool7, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(8), '🧣', $player1->tool8, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(9), '📜', $player1->tool9, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(10), '👁️', $player1->tool10, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(11), '📿', $player1->tool11, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(12), '🦺', $player1->tool12, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(13), '🎗️', $player1->tool13, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(14), '🧿', $player1->tool14, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(15), '🐎', $player1->tool15, $player1->uid, $sid, $encode, $dblj);
$equipHtml .= tcg_other_equipment_slot(\player\zhuangbei_tool_name(16), '💎', $player1->tool16, $player1->uid, $sid, $encode, $dblj);

$hpPercent = 0;
if (intval($player1->umaxhp) > 0) {
    $hpPercent = min(100, max(0, round($player1->uhp / $player1->umaxhp * 100)));
}

$expPercent = 0;
if (intval($player1->umaxexp) > 0) {
    $expPercent = min(100, max(0, round($player1->uexp / $player1->umaxexp * 100)));
}

$staminaValue = isset($player1->ustamina) ? $player1->ustamina : 100;
$staminaMax = isset($player1->umaxstamina) ? $player1->umaxstamina : 100;
$staminaPercent = $staminaMax > 0 ? min(100, max(0, round($staminaValue / $staminaMax * 100))) : 0;

$uname = tcg_other_e($player1->uname);
$chenhao = 'Đạo hữu';

if ($clubplayer) {

    switch ((int)$clubplayer->uclv) {

        case 1:
            $chenhao = '👑 Chưởng môn';
            break;

        case 2:
            $chenhao = '⚜ Phó chưởng môn';
            break;

        case 3:
            $chenhao = '📜 Trưởng lão';
            break;

        case 4:
            $chenhao = '🏮 Chấp sự';
            break;

        case 5:
            $chenhao = '⭐ Tinh anh';
            break;

        default:
            $chenhao = '🥋 Đệ tử';
            break;

    }

}

$jingjie = tcg_other_jingjie_html($player1->jingjie . $player1->cengci);
if ($jingjie == '') {
    $jingjie = 'Chưa rõ cảnh giới';
}
$otherAvatar = \player\player_avatar_img(isset($player1->usex) ? $player1->usex : 1);

$html = <<<HTML
<div class="tcg-profile-page">
    <div class="tcg-profile-tabs">
        <span class="tcg-profile-tab active">👤 Thông tin cá nhân</span>
        <a class="tcg-profile-tab" href="?cmd=$cwinfo">🐾 Thông tin sủng vật</a>
    </div>

    <div class="tcg-profile-hero">
        <div class="tcg-profile-avatar"><img src="./{$otherAvatar}" alt="avatar"></div>
        <div class="tcg-profile-hero-info">
            <div class="tcg-profile-small">{$chenhao}</div>
            <h1>{$uname}</h1>
            <div class="tcg-profile-sub">{$jingjie} · Cấp {$player1->ulv}</div>
            <div class="tcg-profile-club">⛩ Môn phái: {$clubname}</div>
        </div>
    </div>

    <div class="tcg-profile-bars">
        <div class="tcg-profile-bar-card">
            <div class="tcg-profile-bar-head"><span>❤️ Khí huyết</span><b>{$player1->uhp}/{$player1->umaxhp}</b></div>
            <div class="tcg-profile-bar"><i style="width:{$hpPercent}%"></i></div>
        </div>
        <div class="tcg-profile-bar-card">
            <div class="tcg-profile-bar-head"><span>⚡ Thể lực</span><b>{$staminaValue}/{$staminaMax}</b></div>
            <div class="tcg-profile-bar energy"><i style="width:{$staminaPercent}%"></i></div>
        </div>
        <div class="tcg-profile-bar-card full-width">
            <div class="tcg-profile-bar-head"><span>✨ Tu vi</span><b>{$player1->uexp}/{$player1->umaxexp}</b></div>
            <div class="tcg-profile-bar exp"><i style="width:{$expPercent}%"></i></div>
        </div>
    </div>

    <div class="tcg-profile-stats">
        <div class="tcg-profile-stat"><span>⚔️ Công</span><b>{$player1->ugj}</b></div>
        <div class="tcg-profile-stat"><span>🛡️ Phòng</span><b>{$player1->ufy}</b></div>
        <div class="tcg-profile-stat"><span>💥 Bạo</span><b>{$player1->ubj}</b></div>
        <div class="tcg-profile-stat"><span>🩸 Hút máu</span><b>{$player1->uxx}</b></div>
        <div class="tcg-profile-stat"><span><img class=money-ico src=./images/daoju/linh_thach.png alt> Linh thạch</span><b>{$player1->uyxb}{$tishi}</b></div>
        <div class="tcg-profile-stat"><span><img class=money-ico src=./images/daoju/ma_thach.png alt> Ma thạch</span><b>{$player1->uczb}</b></div>
    </div>

    <div class="tcg-profile-section">
        <div class="tcg-profile-section-title">🎒 Trang bị đang mặc</div>
        <div class="tcg-profile-equip-grid">
            {$equipHtml}
        </div>
    </div>

    {$immenu}

    <div class="tcg-profile-bottom">
        <a class="tcg-profile-back" href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
        <a class="tcg-profile-home" href="game.php?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;

echo $html;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/ 12/01
 * Time: 18:10
 */
?>
