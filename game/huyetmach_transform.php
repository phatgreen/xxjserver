<?php
// Huyết Mạch - Biến Thân
$player = \player\getplayer($sid, $dblj);

$hmId = intval($player->huyetmach_id);
$config = \player\huyetmach_config_get($hmId);
$transform = \player\huyetmach_get_transform($hmId);

$hmcmd = $encode->encode("cmd=huyetmach&sid=$sid");

$msg = '';
if (isset($_POST['action']) && $_POST['action'] === 'transform') {
    $result = \player\huyetmach_activate_transform($sid, $dblj);
    $msg = $result['msg'];
    $player = \player\getplayer($sid, $dblj);
}

$msgHtml = '';
if (!empty($msg)) {
    $cls = (strpos($msg, 'thành công') !== false) ? '#6ee7b7' : '#f87171';
    $msgHtml = '<div style="background:rgba(255,255,255,.06);border-radius:10px;padding:12px;margin-bottom:12px;color:' . $cls . ';text-align:center;font-weight:800">' . htmlspecialchars($msg) . '</div>';
}

$transformHtml = '';
if ($transform) {
    $tName = $transform['name'];
    $tIcon = $transform['icon'];
    $tDesc = $transform['description'];
    $tDuration = intval($transform['duration_turns']);
    $tCd = intval($transform['cooldown_minutes']);
    $tHpCost = intval($transform['hp_cost_percent']);
    $effects = $transform['effects'];

    $effectHtml = '';
    if (isset($effects['atk_mult']) && $effects['atk_mult'] > 1) {
        $pct = round(($effects['atk_mult'] - 1) * 100);
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>ATK</span><span class='tc-passive-val'>+{$pct}%</span></div>";
    }
    if (isset($effects['def_mult']) && $effects['def_mult'] > 1) {
        $pct = round(($effects['def_mult'] - 1) * 100);
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>DEF</span><span class='tc-passive-val'>+{$pct}%</span></div>";
    }
    if (isset($effects['hp_mult']) && $effects['hp_mult'] > 1) {
        $pct = round(($effects['hp_mult'] - 1) * 100);
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>HP</span><span class='tc-passive-val'>+{$pct}%</span></div>";
    }
    if (isset($effects['element_dmg_add']) && $effects['element_dmg_add'] > 0) {
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>Element DMG</span><span class='tc-passive-val'>+" . $effects['element_dmg_add'] . "%</span></div>";
    }
    if (isset($effects['hp_regen_percent']) && $effects['hp_regen_percent'] > 0) {
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>Hồi HP/lượt</span><span class='tc-passive-val'>+" . $effects['hp_regen_percent'] . "%</span></div>";
    }
    if (isset($effects['phys_reduce']) && $effects['phys_reduce'] > 0) {
        $effectHtml .= "<div class='tc-passive'><span class='tc-passive-label'>Giảm vật lý</span><span class='tc-passive-val'>+" . $effects['phys_reduce'] . "%</span></div>";
    }

    $transformHtml = <<<HTML
<div class="tc-card">
    <div style="text-align:center;margin-bottom:16px">
        <div style="font-size:64px;margin-bottom:8px">{$tIcon}</div>
        <div style="font-size:22px;font-weight:1000;color:#f97316;text-shadow:0 0 12px rgba(249,115,22,.4)">{$tName}</div>
    </div>
    <div style="font-size:13px;color:#94a3b8;text-align:center;margin-bottom:12px">{$tDesc}</div>
    <div style="background:rgba(255,255,255,.05);border-radius:10px;padding:12px">
        {$effectHtml}
        <div class="tc-passive"><span class='tc-passive-label'>Thời gian</span><span class='tc-passive-val'>{$tDuration} lượt</span></div>
        <div class="tc-passive"><span class='tc-passive-label'>HP tiêu hao</span><span class='tc-passive-val'>{$tHpCost}% HP max</span></div>
        <div class="tc-passive"><span class='tc-passive-label'>Cooldown</span><span class='tc-passive-val'>{$tCd} phút</span></div>
    </div>
    <form method="post" style="text-align:center;margin-top:16px">
        <input type="hidden" name="action" value="transform">
        <button type="submit" class="tc-btn tc-btn-fire" style="width:100%">🔥 Biến Thân!</button>
    </form>
</div>
HTML;
} else {
    $transformHtml = '<div class="tc-card" style="text-align:center;color:#94a3b8">Không tìm thấy thông tin biến thân.</div>';
}
?>
<style>
.tc-card{background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;padding:20px;margin-bottom:12px}
.tc-passive{background:rgba(255,255,255,.05);border-radius:10px;padding:10px;margin:6px 0;font-size:13px;display:flex;justify-content:space-between}
.tc-passive-label{color:#94a3b8}
.tc-passive-val{color:#6ee7b7;font-weight:800}
.tc-btn{display:inline-block;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:800;text-decoration:none;color:#fff;border:none;cursor:pointer}
.tc-btn-fire{background:linear-gradient(135deg,#dc2626,#f97316);box-shadow:0 0 12px rgba(220,38,38,.3)}
.tc-btn-ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-actions{display:flex;gap:10px;margin-top:16px;justify-content:center}
</style>

<?= $msgHtml ?>
<?= $transformHtml ?>
<div class="tc-actions">
    <a class="tc-btn tc-btn-ghost" href="?cmd=<?= $hmcmd ?>">← Quay lại</a>
</div>
