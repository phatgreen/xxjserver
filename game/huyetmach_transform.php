<?php
// Huyết Mạch - Biến Thân (dùng chung khối .hm-* với màn Mạch Huyết)
$player = \player\getplayer($sid, $dblj);

$hmId = intval($player->huyetmach_id);
$transform = \player\huyetmach_get_transform($hmId);

$hmcmd = $encode->encode("cmd=huyetmach&sid=$sid");

$msg = '';
$msgOk = false;
if (isset($_POST['action']) && $_POST['action'] === 'transform') {
    $result = \player\huyetmach_activate_transform($sid, $dblj);
    $msg = $result['msg'];
    $msgOk = !empty($result['ok']);
    $player = \player\getplayer($sid, $dblj);
}

$msgHtml = '';
if ($msg !== '') {
    $msgHtml = '<div class="hm-msg ' . ($msgOk ? 'ok' : 'err') . '">' . htmlspecialchars($msg) . '</div>';
}

$effectHtml = '';
if ($transform) {
    $tName = htmlspecialchars($transform['name']);
    $tIcon = $transform['icon'];
    $tDesc = htmlspecialchars($transform['description']);
    $tDuration = intval($transform['duration_turns']);
    $tCd = intval($transform['cooldown_minutes']);
    $tHpCost = intval($transform['hp_cost_percent']);
    $effects = $transform['effects'];

    $hmEffectRows = array(
        'atk_mult'          => array('Công Kích',    1, '%'),
        'def_mult'          => array('Phòng Thủ',    1, '%'),
        'hp_mult'           => array('Khí Huyết',    1, '%'),
        'element_dmg_add'   => array('Sát Thương Hành', 0, '%'),
        'hp_regen_percent'  => array('Hồi HP/lượt',  0, '%'),
        'phys_reduce'       => array('Giảm vật lý',  0, '%'),
    );
    foreach ($hmEffectRows as $hmKey => $hmRow) {
        if (!isset($effects[$hmKey])) continue;
        if ($hmRow[1] === 1) {
            if (floatval($effects[$hmKey]) <= 1) continue;
            $hmVal = '+' . round((floatval($effects[$hmKey]) - 1) * 100) . '%';
        } else {
            if (floatval($effects[$hmKey]) <= 0) continue;
            $hmVal = '+' . floatval($effects[$hmKey]) . '%';
        }
        $effectHtml .= '<div class="hm-attr"><span>' . $hmRow[0] . '</span><b>' . $hmVal . '</b></div>';
    }
    $effectHtml .= '<div class="hm-attr"><span>Thời gian</span><b>' . $tDuration . ' lượt</b></div>';
    $effectHtml .= '<div class="hm-attr"><span>HP tiêu hao</span><b>' . $tHpCost . '% HP max</b></div>';
    $effectHtml .= '<div class="hm-attr"><span>Hồi chiêu</span><b>' . $tCd . ' phút</b></div>';
}
?>
<?= $msgHtml ?>
<div class="hm-wrap">
<?php if ($transform): ?>
    <div class="hm-card">
        <div style="text-align:center;margin-bottom:14px">
            <div style="font-size:64px;margin-bottom:8px"><?= $tIcon ?></div>
            <div style="font-size:22px;font-weight:1000;color:#f97316;text-shadow:0 0 12px rgba(249,115,22,.4)"><?= $tName ?></div>
        </div>
        <div style="font-size:13px;color:#94a3b8;text-align:center;margin-bottom:12px;line-height:1.5"><?= $tDesc ?></div>
        <div style="background:rgba(255,255,255,.05);border-radius:10px;padding:10px 12px">
            <?= $effectHtml ?>
        </div>
        <form method="post" style="margin-top:12px">
            <input type="hidden" name="action" value="transform">
            <button type="submit" class="hm-act hm-act-break" style="width:100%">Biến Thân!</button>
        </form>
        <a class="hm-act hm-act-ghost" href="?cmd=<?= $hmcmd ?>" style="width:100%;margin-top:8px">← Quay lại Mạch Huyết</a>
    </div>
<?php else: ?>
    <div class="hm-card hm-note">Không tìm thấy thông tin biến thân.</div>
    <a class="hm-act hm-act-ghost" href="?cmd=<?= $hmcmd ?>" style="width:100%">← Quay lại Mạch Huyết</a>
<?php endif; ?>
</div>
