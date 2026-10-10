<?php
// Huyết Mạch - Chi tiết
$player = \player\getplayer($sid, $dblj);

$hmId = intval($player->huyetmach_id);
$hmQuality = intval($player->huyetmach_quality);
$hmAwaken = intval($player->huyetmach_awaken_stage);

$config = \player\huyetmach_config_get($hmId);

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$awcmd = $encode->encode("cmd=huyetmach_awakening&sid=$sid");
$trcmd = $encode->encode("cmd=huyetmach_transform&sid=$sid");

if (!$config) {
    echo '<div style="text-align:center;padding:40px 20px;background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;margin:12px;">'
        . '<div style="font-size:48px;margin-bottom:12px">🩸</div>'
        . '<div style="font-size:16px;font-weight:800;color:#94a3b8;margin-bottom:8px">Bạn chưa có Huyết Mạch</div>'
        . '<div style="font-size:13px;color:#64748b;margin-bottom:20px">Sử dụng <b style="color:#dc2626">Tố Thể Đan</b> để tái tạo thân thể và phục hồi hệ thống huyết mạch</div>'
        . '<a href="?cmd=' . $ztcmd . '" style="display:inline-block;padding:10px 24px;border-radius:10px;font-size:14px;font-weight:800;text-decoration:none;color:#94a3b8;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);transition:all .2s">← Quay lại Nhân vật</a>'
        . '</div>';
    return;
}

$hmName = $config['name'];
$hmIcon = $config['icon'];
$hmColor = $config['color'];
$hmElement = $config['element'];
$hmQName = \player\huyetmach_quality_name($hmQuality);
$hmQIcon = \player\huyetmach_quality_icon($hmQuality);
$hmAName = \player\huyetmach_awaken_name($hmAwaken);
$hmAIcon = \player\huyetmach_awaken_icon($hmAwaken);

$bonuses = \player\huyetmach_calc_bonuses($sid, $dblj);
$stats = $bonuses['stats'];

$transformName = isset($config['transform_name']) ? $config['transform_name'] : 'Không có';
$transformIcon = isset($config['transform_icon']) ? $config['transform_icon'] : '';
$canTransform = $bonuses['can_transform'];
?>
<style>
.tc-card{background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;padding:20px;margin-bottom:12px}
.tc-header{text-align:center;margin-bottom:16px}
.tc-icon{font-size:48px;margin-bottom:8px}
.tc-name{font-size:22px;font-weight:1000;color:<?= $hmColor ?>;text-shadow:0 0 12px <?= $hmColor ?>44}
.tc-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;margin:4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-desc{font-size:13px;color:#94a3b8;margin:12px 0;line-height:1.5}
.tc-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0}
.tc-stat{background:rgba(255,255,255,.05);border-radius:10px;padding:10px;text-align:center}
.tc-stat-label{font-size:11px;color:#64748b;margin-bottom:4px}
.tc-stat-val{font-size:18px;font-weight:1000;color:#38bdf8}
.tc-passive{background:rgba(255,255,255,.05);border-radius:10px;padding:10px;margin:6px 0;font-size:13px;display:flex;justify-content:space-between}
.tc-passive-label{color:#94a3b8}
.tc-passive-val{color:#6ee7b7;font-weight:800}
.tc-actions{display:flex;gap:10px;margin-top:16px;justify-content:center}
.tc-btn{display:inline-block;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:800;text-decoration:none;color:#fff;border:none;cursor:pointer}
.tc-btn-primary{background:linear-gradient(135deg,#f97316,#eab308);box-shadow:0 0 12px rgba(249,115,22,.3)}
.tc-btn-ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-btn-fire{background:linear-gradient(135deg,#dc2626,#f97316);box-shadow:0 0 12px rgba(220,38,38,.3)}
</style>

<div class="tc-card">
    <div class="tc-header">
        <div class="tc-icon"><?= $hmIcon ?></div>
        <div class="tc-name"><?= htmlspecialchars($hmName) ?></div>
        <span class="tc-badge" style="color:<?= $hmColor ?>;border-color:<?= $hmColor ?>44"><?= $hmQIcon ?> <?= htmlspecialchars($hmQName) ?></span>
        <span class="tc-badge"><?= $hmAIcon ?> <?= htmlspecialchars($hmAName) ?></span>
        <span class="tc-badge" style="color:#38bdf8">🌊 <?= htmlspecialchars(\player\huyetmach_element_name($hmElement)) ?></span>
    </div>
    <div class="tc-desc"><?= htmlspecialchars($config['description']) ?></div>
</div>

<div class="tc-card">
    <div style="font-weight:800;margin-bottom:8px;color:#c0a050">✨ Thuộc tính</div>
    <div class="tc-grid">
        <div class="tc-stat"><div class="tc-stat-label">Công Kích</div><div class="tc-stat-val">+<?= number_format($stats['atk']) ?></div></div>
        <div class="tc-stat"><div class="tc-stat-label">HP</div><div class="tc-stat-val">+<?= number_format($stats['hp']) ?></div></div>
        <div class="tc-stat"><div class="tc-stat-label">Bạo Kích</div><div class="tc-stat-val">+<?= number_format($stats['crit']) ?></div></div>
        <div class="tc-stat"><div class="tc-stat-label">Hút Máu</div><div class="tc-stat-val">+<?= number_format($stats['lifesteal']) ?></div></div>
    </div>
    <?php if ($bonuses['element_dmg'] > 0): ?>
    <div class="tc-passive"><span class="tc-passive-label">Element DMG (<?= htmlspecialchars(\player\huyetmach_element_name($hmElement)) ?>)</span><span class="tc-passive-val">+<?= $bonuses['element_dmg'] ?>%</span></div>
    <?php endif; ?>
</div>

<div class="tc-card">
    <div style="font-weight:800;margin-bottom:8px;color:#6ee7b7">💫 Passive</div>
    <?php if ($bonuses['passive_element'] > 0): ?>
    <div class="tc-passive"><span class="tc-passive-label">Element Bonus</span><span class="tc-passive-val">+<?= $bonuses['passive_element'] ?>%</span></div>
    <?php endif; ?>
    <?php if ($bonuses['passive_crit'] > 0): ?>
    <div class="tc-passive"><span class="tc-passive-label">Crit Rate</span><span class="tc-passive-val">+<?= $bonuses['passive_crit'] ?>%</span></div>
    <?php endif; ?>
    <?php if ($bonuses['passive_exp'] > 0): ?>
    <div class="tc-passive"><span class="tc-passive-label">EXP Bonus</span><span class="tc-passive-val">+<?= $bonuses['passive_exp'] ?>%</span></div>
    <?php endif; ?>
    <?php if ($bonuses['passive_drop'] > 0): ?>
    <div class="tc-passive"><span class="tc-passive-label">Drop Rate</span><span class="tc-passive-val">+<?= $bonuses['passive_drop'] ?>%</span></div>
    <?php endif; ?>
</div>

<div class="tc-card">
    <div style="font-weight:800;margin-bottom:8px;color:#f97316">🔥 Biến Thân</div>
    <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:32px"><?= $transformIcon ?></span>
        <div>
            <div style="font-weight:800;color:#e2e8f0"><?= htmlspecialchars($transformName) ?></div>
            <div style="font-size:12px;color:#94a3b8"><?= $canTransform ? 'Đã mở khóa' : 'Cần thức tỉnh Chân Thân (Stage 3)' ?></div>
        </div>
    </div>
</div>

<div class="tc-actions">
    <a class="tc-btn tc-btn-primary" href="?cmd=<?= $awcmd ?>">🌟 Thức Tỉnh</a>
    <?php if ($canTransform): ?>
    <a class="tc-btn tc-btn-fire" href="?cmd=<?= $trcmd ?>">🔥 Biến Thân</a>
    <?php endif; ?>
    <a class="tc-btn tc-btn-ghost" href="?cmd=<?= $ztcmd ?>">Nhân Vật</a>
</div>
