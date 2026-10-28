<?php
/**
 * game/feature_locked.php — trang báo tính năng chưa mở (game.php gán $featureLocked khi lệnh thuộc tính năng chưa đủ cấp).
 */
$fkName  = htmlspecialchars($featureLocked['name'], ENT_QUOTES, 'UTF-8');
$fkIcon  = htmlspecialchars(isset($featureLocked['icon']) ? $featureLocked['icon'] : '🔒', ENT_QUOTES, 'UTF-8');
$fkDesc  = htmlspecialchars(isset($featureLocked['desc']) ? $featureLocked['desc'] : '', ENT_QUOTES, 'UTF-8');
$fkLevel = intval($featureLocked['level']);
$fkLeft  = max(0, $fkLevel - intval($player->ulv));
$fkXlCmd  = $encode->encode("cmd=goxiulian&sid=$sid");
$fkMidCmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
?>
<div class="fk-lock">
    <div class="fk-lock-icon"><?php echo $fkIcon; ?><span>🔒</span></div>
    <div class="fk-lock-title"><?php echo $fkName; ?></div>
    <div class="fk-lock-level">Mở khi đạt <b>cấp <?php echo $fkLevel; ?></b> — còn <b><?php echo $fkLeft; ?></b> cấp</div>
    <div class="fk-lock-desc"><?php echo $fkDesc; ?></div>
    <div class="fk-lock-hint">Tiếp tục tu luyện, diệt quái và làm nhiệm vụ chính tuyến để mau lên cấp.</div>
    <div class="fk-lock-actions">
        <a class="fk-lock-btn" href="?cmd=<?php echo $fkXlCmd; ?>">🧘 Tu Luyện</a>
        <a class="fk-lock-btn alt" href="?cmd=<?php echo $fkMidCmd; ?>">🏠 Đại Sảnh</a>
    </div>
</div>
