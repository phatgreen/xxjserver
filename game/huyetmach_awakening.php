<?php
// Huyết Mạch - Thức Tỉnh
$player = \player\getplayer($sid, $dblj);

$hmAwaken = intval($player->huyetmach_awaken_stage);
$awakenInfo = \player\huyetmach_get_awakening_info($hmAwaken);

$hmcmd = $encode->encode("cmd=huyetmach&sid=$sid");

$msg = '';
if (isset($_POST['action']) && $_POST['action'] === 'awaken') {
    $result = \player\huyetmach_awaken($sid, $dblj);
    $msg = $result['msg'];
    $player = \player\getplayer($sid, $dblj);
    $hmAwaken = intval($player->huyetmach_awaken_stage);
    $awakenInfo = \player\huyetmach_get_awakening_info($hmAwaken);
}

$msgHtml = '';
if (!empty($msg)) {
    $cls = (strpos($msg, 'thành công') !== false) ? '#6ee7b7' : '#f87171';
    $msgHtml = '<div style="background:rgba(255,255,255,.06);border-radius:10px;padding:12px;margin-bottom:12px;color:' . $cls . ';text-align:center;font-weight:800">' . htmlspecialchars($msg) . '</div>';
}

$awakenHtml = '';
if ($awakenInfo) {
    $toName = isset($awakenInfo['to_name']) ? $awakenInfo['to_name'] : 'Stage ' . $awakenInfo['to_stage'];
    $realmReq = $awakenInfo['realm_require'];
    $realmLvl = intval($awakenInfo['realm_min_level']);
    $passiveDesc = isset($awakenInfo['passive_description']) ? $awakenInfo['passive_description'] : '';

    $matHtml = '';
    if (isset($awakenInfo['material_id']) && intval($awakenInfo['material_id']) > 0) {
        $matName = \player\huyetmach_get_material_name(intval($awakenInfo['material_id']));
        $matAmt = intval($awakenInfo['material_amount']);
        $matHtml = '<div style="color:#94a3b8;margin:4px 0">📦 ' . $matName . ': ' . $matAmt . '</div>';
    }

    $bossHtml = '';
    if (isset($awakenInfo['boss_drop_require']) && $awakenInfo['boss_drop_require']) {
        $bossHtml = '<div style="color:#f87171;margin:4px 0">👹 Cần đánh bại boss tương ứng</div>';
    }

    $questHtml = '';
    if (isset($awakenInfo['quest_require']) && $awakenInfo['quest_require']) {
        $questHtml = '<div style="color:#fbbf24;margin:4px 0">📜 Cần hoàn thành quest thức tỉnh</div>';
    }

    $canAwaken = ($player->ulv >= $realmLvl);
    $btnDisabled = $canAwaken ? '' : 'opacity:0.5;pointer-events:none;';

    $curIcon = \player\huyetmach_awaken_icon($hmAwaken);
    $curName = htmlspecialchars(\player\huyetmach_awaken_name($hmAwaken));
    $nextIcon = \player\huyetmach_awaken_icon(intval($awakenInfo['to_stage']));
    $nextName = htmlspecialchars($toName);

    $awakenHtml = <<<HTML
<div class="tc-card">
    <div style="font-weight:800;margin-bottom:12px;color:#c0a050">🌟 Thức tỉnh Huyết Mạch</div>
    <div style="text-align:center;margin-bottom:12px">
        <span class="tc-badge">{$curIcon} {$curName}</span>
        <span style="color:#94a3b8;margin:0 8px">→</span>
        <span class="tc-badge" style="color:#6ee7b7">{$nextIcon} {$nextName}</span>
    </div>
    <div style="background:rgba(255,255,255,.05);border-radius:10px;padding:12px">
        <div style="color:#ff9800;font-weight:800;margin-bottom:6px">🏔 Cảnh giới: {$realmReq} (cấp {$realmLvl}+)</div>
        {$matHtml}
        {$bossHtml}
        {$questHtml}
        <div style="color:#6ee7b7;font-weight:800;margin-top:8px">💫 {$passiveDesc}</div>
    </div>
    <form method="post" style="text-align:center;margin-top:12px">
        <input type="hidden" name="action" value="awaken">
        <button type="submit" class="tc-btn tc-btn-primary" style="width:100%;{$btnDisabled}">Thức Tỉnh</button>
    </form>
</div>
HTML;
} else {
    $awakenHtml = '<div class="tc-card" style="text-align:center;color:#6ee7b7;font-weight:800">Đã thức tỉnh tối đa!</div>';
}
?>
<style>
.tc-card{background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;padding:20px;margin-bottom:12px}
.tc-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;margin:4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-btn{display:inline-block;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:800;text-decoration:none;color:#fff;border:none;cursor:pointer}
.tc-btn-primary{background:linear-gradient(135deg,#f97316,#eab308);box-shadow:0 0 12px rgba(249,115,22,.3)}
.tc-btn-ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-actions{display:flex;gap:10px;margin-top:16px;justify-content:center}
</style>

<?= $msgHtml ?>
<?= $awakenHtml ?>
<div class="tc-actions">
    <a class="tc-btn tc-btn-ghost" href="?cmd=<?= $hmcmd ?>">← Quay lại</a>
</div>
