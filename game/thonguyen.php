<?php
/**
 * game/thonguyen.php — Tuổi & Thọ Nguyên (class/function/thonguyen.php). game.php cũng đưa vào đây khi nhân vật đã Tọa Hóa
 * mà gọi lệnh bị khoá (tu luyện, chiến đấu, đột phá…).
 * Tham số: tnact=buy|use, tnk=dientho|daitinh|cuuchuyen.
 */
$tnNotice = '';
$tnNoticeType = 'ok';
$tnCfg = \player\thonguyen_config();
$tnNames = array('dientho' => 'Diên Thọ Đan', 'daitinh' => 'Đại Tỉnh Hồi Hồn Đan', 'cuuchuyen' => 'Cửu Chuyển Hoàn Hồn Đan');

if (isset($tnact) && isset($tnk) && isset($tnNames[$tnk])) {
    if ($tnact === 'buy') {
        list($tnOk, $tnNotice) = \player\thonguyen_buy($sid, $tnk, $dblj);
    } elseif ($tnact === 'use') {
        list($tnOk, $tnNotice) = \player\thonguyen_use_item($sid, intval($tnCfg['items'][$tnk]), 'thonguyen_' . $tnk, $dblj);
    } else {
        $tnOk = false;
    }
    $tnNoticeType = $tnOk ? 'ok' : 'bad';
    $player = \player\getplayer($sid, $dblj);
}

$tn = \player\thonguyen_status($player, $dblj);
$tnMidCmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$tnBagCmd = $encode->encode("cmd=getbagdj&sid=$sid");
?>
<div class="fk-lock">
<?php if (!$tn): ?>
    <div class="fk-lock-title">⏳ Thọ Nguyên</div>
    <div class="fk-lock-desc">Tính năng Thọ Nguyên chưa bật.</div>
<?php else:
    list($tnBonus, $tnParts) = \player\thonguyen_bonus_pct($player);
    $tnAgeTxt = \player\thonguyen_years_text($tn['age']);
    $tnLifeTxt = \player\thonguyen_years_text($tn['life']);
    $tnLeftPct = round($tn['left_pct'], 1);
    $tnColor = $tn['dead'] ? '#e5534b' : ($tn['cut'] > 0 ? '#d9a33f' : '#2fbf71');
    $tnHours = floatval($tnCfg['hours_per_year']);
?>
    <div class="fk-lock-icon"><?php echo $tn['dead'] ? '🪦' : '⏳'; ?></div>
    <div class="fk-lock-title"><?php echo $tn['dead'] ? 'Đã Tọa Hóa' : 'Tuổi & Thọ Nguyên'; ?></div>
    <?php if ($tnNotice !== ''): ?>
    <div class="fk-lock-level" style="color:<?php echo $tnNoticeType === 'ok' ? '#2fbf71' : '#e5534b'; ?>"><?php echo htmlspecialchars($tnNotice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="fk-lock-level">Tuổi <b><?php echo $tnAgeTxt; ?></b> / Thọ nguyên <b><?php echo $tnLifeTxt; ?></b> năm
        — còn <b style="color:<?php echo $tnColor; ?>"><?php echo $tnLeftPct; ?>%</b></div>
    <?php if ($tn['dead']): ?>
    <div class="fk-lock-desc" style="color:#e5534b">Thọ nguyên đã cạn, hồn phách ly thể. Không thể tu luyện, chiến đấu hay đột phá cho tới khi dùng Hồi Hồn Đan.</div>
    <?php elseif ($tn['cut'] > 0): ?>
    <div class="fk-lock-desc" style="color:#d9a33f">Thọ nguyên sắp cạn: hấp thụ linh khí (tu luyện, bế quan) −<?php echo $tn['cut']; ?>%. Hãy đột phá cảnh giới hoặc dùng Diên Thọ Đan.</div>
    <?php endif; ?>
    <div class="fk-lock-hint">Cứ <?php echo $tnHours == intval($tnHours) ? intval($tnHours) : $tnHours; ?> giờ (kể cả lúc offline) nhân vật thêm 1 tuổi.
        Lên cấp, đột phá cảnh giới và nâng Huyết Mạch / Thể Chất / Thiên Phú / Linh Căn đều tăng thọ nguyên.
        Thọ nguyên còn ≤ 20% / 10% / 5%: hấp thụ linh khí −20% / −30% / −40%.</div>
    <div class="fk-lock-desc">Thọ nguyên thêm từ hệ thống: <b>+<?php echo round($tnBonus, 1); ?>%</b> (tối đa +<?php echo intval($tnCfg['bonus']['max_total']); ?>%)
        <?php foreach ($tnParts as $tnN => $tnV) { if ($tnV > 0) echo ' · ' . $tnN . ' +' . round($tnV, 1) . '%'; } ?></div>
    <div class="fk-lock-actions" style="flex-direction:column;gap:8px">
    <?php foreach ($tnNames as $tnKey => $tnName):
        if ($tn['dead'] ? $tnKey === 'dientho' : $tnKey !== 'dientho') continue;   // Tọa Hóa: chỉ Hồi Hồn Đan; còn sống: chỉ Diên Thọ Đan
        list($tnPrice, $tnCur) = \player\thonguyen_price($tnKey, $player);
        $tnHave = \player\getplayerdaoju($sid, intval($tnCfg['items'][$tnKey]), $dblj);
        $tnHaveN = $tnHave ? intval($tnHave->djsum) : 0;
        $tnBuyCmd = $encode->encode("cmd=thonguyen&tnact=buy&tnk=$tnKey&sid=$sid");
        $tnUseCmd = $encode->encode("cmd=thonguyen&tnact=use&tnk=$tnKey&sid=$sid");
    ?>
        <div><b><?php echo $tnName; ?></b> (có <?php echo $tnHaveN; ?>)<br>
            <?php if ($tnHaveN > 0): ?><a class="fk-lock-btn" href="?cmd=<?php echo $tnUseCmd; ?>">💊 Dùng</a><?php endif; ?>
            <a class="fk-lock-btn alt" href="?cmd=<?php echo $tnBuyCmd; ?>">🛒 Mua 1 viên — <?php echo number_format($tnPrice); ?> <?php echo $tnCur === 'mt' ? 'Ma Thạch' : 'Linh Thạch'; ?></a>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
    <div class="fk-lock-actions">
        <a class="fk-lock-btn alt" href="?cmd=<?php echo $tnBagCmd; ?>">🎒 Túi đồ</a>
        <a class="fk-lock-btn alt" href="?cmd=<?php echo $tnMidCmd; ?>">🏠 Đại Sảnh</a>
    </div>
</div>
