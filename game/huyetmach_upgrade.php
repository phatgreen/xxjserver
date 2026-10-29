<?php
// HUYET MACH — trang nang pham chat (9 buoc 1->10).
// Logic: class/function/huyetmach.php (huyetmach_upgrade*), CSS: .hmq-* trong css/mystyle.css
$player = \player\getplayer($sid, $dblj);

$hmUpBloodId = intval($player->huyetmach_id);
$hmUpQuality = intval($player->huyetmach_quality);
$hmUpInfo = \player\huyetmach_get_upgrade_info($hmUpQuality);

$hmcmd = $encode->encode("cmd=huyetmach&sid=$sid");

$hmUpMsg = '';
if (isset($_POST['hm_action']) && $_POST['hm_action'] === 'upgrade' && $hmUpBloodId > 0) {
    $res = \player\huyetmach_upgrade($sid, $dblj);
    $hmUpMsg = $res['msg'];
    $player = \player\getplayer($sid, $dblj);
    $hmUpQuality = intval($player->huyetmach_quality);
    $hmUpInfo = \player\huyetmach_get_upgrade_info($hmUpQuality);
}

// Pham hien tai -> class phan tang hieu ung (hmq-q1 .. hmq-q10)
$hmUpFx = 'hmq-q' . max(1, min(10, $hmUpQuality));
$hmUpColor = \player\huyetmach_quality_color($hmUpQuality);
$hmUpToColor = $hmUpInfo ? \player\huyetmach_quality_color(intval($hmUpInfo['to_quality'])) : $hmUpColor;
list($hR, $hG, $hB) = sscanf($hmUpColor, '#%02x%02x%02x');
list($hTR, $hTG, $hTB) = sscanf($hmUpToColor, '#%02x%02x%02x');
$hmUpRgb = intval($hR) . ',' . intval($hG) . ',' . intval($hB);
$hmUpToRgb = intval($hTR) . ',' . intval($hTG) . ',' . intval($hTB);

$hmUpMsgHtml = '';
if ($hmUpMsg !== '') {
    $isOk = (strpos($hmUpMsg, 'thành công') !== false);
    $hmUpMsgHtml = '<div class="hmq-msg" style="--msg-rgb:' . ($isOk ? '110,231,183' : '248,113,113') . '">'
        . '<div class="hmq-msg-in" style="color:' . ($isOk ? '#6ee7b7' : '#f87171') . '">'
        . htmlspecialchars($hmUpMsg, ENT_QUOTES, 'UTF-8') . '</div></div>';
}

if ($hmUpBloodId <= 0) {
    // Chua co huyet mach: empty-state nhu man chinh (Task 7), khong cho hoi thieu du lieu
    echo '<div class="hm-wrap"><div class="hm-card hmq-card ' . $hmUpFx . '" style="--hmq:' . $hmUpColor
        . ';--hmq-rgb:' . $hmUpRgb . '"><div class="hm-note">Huyết Mạch chưa khai mở — dùng <b>Tố Thể Đan</b> để nhận huyết mạch.</div></div>'
        . '<a class="hm-act hm-act-ghost" style="width:100%;margin-top:8px" href="?cmd=' . $hmcmd . '">← Quay lại Mạch Huyết</a></div>';
    return;
}

$hmUpHtml = '';
if ($hmUpInfo) {
    $hmUpLt = number_format(intval($hmUpInfo['linh_thach_cost']));
    $hmUpMatId = intval($hmUpInfo['material_id']);
    $hmUpMatAmt = intval($hmUpInfo['material_amount']);
    $hmUpMatName = \player\huyetmach_get_material_name($hmUpMatId);
    $hmUpMatHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmUpMatId);
    $hmUpRate = intval($hmUpInfo['success_rate']);
    $hmUpGate = intval(isset($hmUpInfo['unlock_realm_min_level']) ? $hmUpInfo['unlock_realm_min_level'] : 0);
    $hmUpToName = \player\huyetmach_quality_name(intval($hmUpInfo['to_quality']));

    $hmUpExtraHtml = '';
    if (isset($hmUpInfo['extra_material_id']) && intval($hmUpInfo['extra_material_id']) > 0) {
        $hmUpExtraId = intval($hmUpInfo['extra_material_id']);
        $hmUpExtraAmt = intval($hmUpInfo['extra_material_amount']);
        $hmUpExtraHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmUpExtraId);
        $hmUpExtraCls = ($hmUpExtraHave >= $hmUpExtraAmt) ? 'hm-cost-ok' : 'hm-cost-no';
        $hmUpExtraHtml = '<div class="hm-cost-item ' . $hmUpExtraCls . '">📦 '
            . \player\huyetmach_get_material_name($hmUpExtraId) . ': ' . $hmUpExtraHave . '/' . $hmUpExtraAmt . '</div>';
    }

    $hmUpMatCls = ($hmUpMatHave >= $hmUpMatAmt) ? 'hm-cost-ok' : 'hm-cost-no';
    $hmUpGateOk = (intval($player->ulv) >= $hmUpGate);
    $hmUpGateHtml = $hmUpGateOk ? '' : '<div class="hm-req hm-cost-no">🔒 Cần cấp ' . $hmUpGate . ' (hiện ' . intval($player->ulv) . ')</div>';

    $hmUpFromIcon = \player\huyetmach_quality_icon($hmUpQuality);
    $hmUpFromName = htmlspecialchars(\player\huyetmach_quality_name($hmUpQuality), ENT_QUOTES, 'UTF-8');
    $hmUpToIcon = \player\huyetmach_quality_icon(intval($hmUpInfo['to_quality']));
    $hmUpToName = htmlspecialchars($hmUpToName, ENT_QUOTES, 'UTF-8');
    $hmUpDesc = htmlspecialchars(isset($hmUpInfo['description']) ? $hmUpInfo['description'] : '', ENT_QUOTES, 'UTF-8');

    $hmUpHtml = <<<HTML
<div class="hm-card hmq-card {$hmUpFx}" style="--hmq:{$hmUpColor};--hmq-rgb:{$hmUpRgb};--hmq-to:{$hmUpToColor};--hmq-to-rgb:{$hmUpToRgb}">
    <div class="hmq-head"><i class="hmq-ico">⬆</i> Nâng phẩm chất huyết mạch</div>
    <div class="hmq-row">
        <span class="hm-badge hmq-badge hmq-badge-from"><i class="hmq-ico">{$hmUpFromIcon}</i> {$hmUpFromName}</span>
        <span class="hmq-arrow">→</span>
        <span class="hm-badge hmq-badge hmq-badge-to"><i class="hmq-ico">{$hmUpToIcon}</i> {$hmUpToName}</span>
    </div>
    <div class="hmq-cost">
        <div class="hmq-lt"><img class=money-ico src=./images/daoju/linh_thach.png alt> Linh Thạch: {$hmUpLt}</div>
        <div class="hm-cost-item {$hmUpMatCls}">🩸 {$hmUpMatName}: {$hmUpMatHave}/{$hmUpMatAmt}</div>
        {$hmUpExtraHtml}
        {$hmUpGateHtml}
        <div class="hmq-rate">🎯 Tỷ lệ thành công: {$hmUpRate}%</div>
    </div>
    <div class="hm-node-desc">{$hmUpDesc}</div>
    <form method="post" class="hmq-form">
        <input type="hidden" name="hm_action" value="upgrade">
        <button type="submit" class="hm-act hm-act-refine hmq-btn">Nâng Phẩm</button>
    </form>
</div>
HTML;
} else {
    $hmUpHtml = '<div class="hm-card hmq-card hmq-max ' . $hmUpFx . '" style="--hmq:' . $hmUpColor . ';--hmq-rgb:' . $hmUpRgb
        . ';--hmq-to:' . $hmUpColor . ';--hmq-to-rgb:' . $hmUpRgb . '"><span class="hmq-max-in">Đạo Phẩm — huyết mạch đã tới tận cùng phẩm chất.</span></div>';
}
?>
<?= $hmUpMsgHtml ?>
<?= $hmUpHtml ?>
<a class="hm-act hm-act-ghost" style="width:100%;margin-top:8px" href="?cmd=<?= $hmcmd ?>">← Quay lại Mạch Huyết</a>
