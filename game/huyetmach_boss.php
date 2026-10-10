<?php
// BOSS MACH HUYET — mot boss rieng cho moi nguoi choi.
// Logic: class/function/huyetmach_boss.php
// CSS:   css/mystyle.css (block .hm-*)

$hmcmd     = $encode->encode("cmd=huyetmach&sid=$sid");
$ztcmd     = $encode->encode("cmd=zhuangtai&sid=$sid");
$attackCmd = $encode->encode("cmd=huyetmach_boss&attack=1&sid=$sid");

$player = \player\getplayer($sid, $dblj);
$boss   = \player\huyetmach_boss_spawn($sid, $dblj);
$st     = \player\huyetmach_boss_status($sid, $dblj);
$hmVb   = \player\huyetmach_vessel_bonuses($sid, $dblj);   // doc 1 lan, dung cho Ne + Giam

$battleLog = '';
$hmCanFight = isset($attack) && $boss && intval($boss['hp']) > 0;
if ($hmCanFight && intval($player->uhp) <= 0) {
    // Cung cach pve.php:500 - het Khi Huyet thi khong duoc danh tiep.
    $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
} elseif ($hmCanFight) {
    $res = \player\huyetmach_boss_calc_damage($player, $boss);
    $dmg = intval($res['damage']);
    $critText = $res['crit'] ? " <font color='#FFD700'>[Bạo kích!]</font>" : '';
    $elemText = '';
    if ($res['elem_mult'] > 1.0) $elemText = " <font color='#4CAF50'>[Khắc chế! +" . intval(($res['elem_mult'] - 1) * 100) . '%]</font>';
    elseif ($res['elem_mult'] < 1.0) $elemText = " <font color='#f44336'>[Bị khắc! -" . intval((1 - $res['elem_mult']) * 100) . '%]</font>';

    $newHp = max(0, intval($boss['hp']) - $dmg);
    $dblj->prepare("UPDATE huyetmach_boss SET hp = ? WHERE id = ?")->execute(array($newHp, intval($boss['id'])));
    $battleLog .= "Ngươi gây <font color='#FF5722'>{$dmg}</font> sát thương lên " . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

    if ($newHp <= 0) {
        $boss['hp'] = 0;
        $drop = \player\huyetmach_boss_finish($sid, $dblj, $boss);
        $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " đã bị tiêu diệt!</font><br/>" . $drop['html'];
        // mau thong bao he thong: game/club_boss.php:387-389
        $stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
        $stmt->execute(array('【Hệ Thống】',
            htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8') . ' đã hạ Boss mạch huyết [' . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . ']', 0));
        \player\player_cache_clear($sid);
        $player = \player\getplayer($sid, $dblj);
        $boss = false;
        $st = \player\huyetmach_boss_status($sid, $dblj);
    } else {
        if (\player\huyetmach_boss_vessel_dodge($hmVb)) {
            $battleLog .= "<font color='#6ee7b7'>Mạch Né Tranh — ngươi tránh được đòn của " . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "!</font><br/>";
        } else {
            $bDmg = max(1, intval(round(intval($boss['atk']) - intval($player->ufy) * 0.75)));
            $bDmg = \player\huyetmach_boss_vessel_reduce($hmVb, $bDmg);
            $newPlayerHp = max(0, intval($player->uhp) - $bDmg);
            $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($newPlayerHp, $sid));
            $battleLog .= htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " phản đòn <font color='#f44336'>{$bDmg}</font> sát thương<br/>";
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
        }
        $boss['hp'] = $newHp;
    }
}
?>
<div class="hm-wrap hm-fx<?= max(0, min(4, intval($player->huyetmach_awaken_stage))) ?>" style="--hm-tier:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>">
<div class="hm-card">
    <div class="hm-hrow">
        <span class="hm-title">BOSS MẠCH HUYẾT</span>
        <span class="hm-badge" style="border-color:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>66;color:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>"><i class="hm-badge-ico"><?= $boss ? $boss['icon'] : '👹' ?></i><?= $boss ? htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') : 'Chưa khiêu chiến' ?></span>
    </div>
<?php if ($boss): ?>
    <div class="hm-viewline"><span class="hm-phys"><?= $player->huyetmach_icon ?> <?= htmlspecialchars($player->huyetmach_name, ENT_QUOTES, 'UTF-8') ?></span>
        · cấp <b><?= intval($boss['level']) ?></b> · hệ <b><?= \player\huyetmach_element_name($boss['element']) ?></b>
        · Công <b><?= number_format(intval($boss['atk'])) ?></b> · Phòng <b><?= number_format(intval($boss['def'])) ?></b> · Bạo <b><?= intval($boss['crit']) ?>%</b></div>
    <div class="hm-ms">
        <div class="hm-ms-top">
            <span>Huyết mạch đối phương</span>
            <span><?= number_format(intval($boss['hp'])) ?> / <?= number_format(intval($boss['maxhp'])) ?></span>
        </div>
        <div class="hm-ms-bar"><div class="hm-ms-fill" style="width:<?= intval($boss['maxhp']) > 0 ? min(100, intval(intval($boss['hp']) * 100 / intval($boss['maxhp']))) : 0 ?>%"></div></div>
        <div class="hm-ms-note"><?= htmlspecialchars($st['text'], ENT_QUOTES, 'UTF-8') ?> · HP của ngươi: <b><?= number_format(intval($player->uhp)) ?></b></div>
    </div>
    <a class="hm-act hm-act-break" href="?cmd=<?= $attackCmd ?>" style="width:100%;margin-top:10px">Tấn Công</a>
<?php else: ?>
    <div class="hm-node-desc"><?= htmlspecialchars($st['text'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
</div>
<?php if ($battleLog !== ''): ?>
<div class="hm-card">
    <div class="hm-h3">⚔ Chiến đấu</div>
    <div class="hm-node-desc"><?= $battleLog ?></div>
</div>
<?php endif; ?>
<nav class="hm-tabbar">
    <a href="?cmd=<?= $hmcmd ?>"><i>🩸</i><b>Mạch Huyết</b></a>
    <a class="on" href="?cmd=<?= $ztcmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
</div>
