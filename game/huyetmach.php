<?php
// HUYET MACH — Mang mach huyet 8 mach + thuc tinh huyet mach.
// Logic: class/function/huyetmach.php (huyetmach_vessel_*)
// Config: class/config/huyetmach_vessel_config.json
// CSS:   css/mystyle.css (block .hm-*)

$ztcmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$upcmd   = $encode->encode("cmd=huyetmach_upgrade&sid=$sid");
$bosscmd = $encode->encode("cmd=huyetmach_boss&sid=$sid");
$tfcmd   = $encode->encode("cmd=huyetmach_transform&sid=$sid");
$hmImgVer = '2026100901'; // bump khi doi anh trong images/huyetmach/

// ===== XU LY LUA CHON =====
$hmMsg = '';
$hmMsgOk = false;
$hmActionNode = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hm_action'])) {
    if ($_POST['hm_action'] === 'refine') {
        $hmActionNode = isset($_POST['hm_node']) ? intval($_POST['hm_node']) : 0;
        $hmResult = \player\huyetmach_vessel_refine($sid, $dblj, $hmActionNode);
    } elseif ($_POST['hm_action'] === 'break') {
        $hmResult = \player\huyetmach_awaken($sid, $dblj);
    } else {
        $hmResult = false;
    }
    if (is_array($hmResult)) {
        $hmMsg = $hmResult['msg'];
        $hmMsgOk = !empty($hmResult['ok']);
    }
}

$player = \player\getplayer($sid, $dblj);

$hmDefs      = \player\huyetmach_vessel_defs();
$hmB         = \player\huyetmach_vessel_bonuses($sid, $dblj);
$hmLevels    = $hmB['levels'];
$hmTotal     = intval($hmB['total_lv']);
$hmCap       = intval($hmB['cap']);
$hmStage     = intval($hmB['stage']);
$hmMaxStage  = \player\huyetmach_vessel_stage_max();
$hmTier      = \player\huyetmach_vessel_tier($hmStage);
$hmNextTier  = \player\huyetmach_vessel_tier($hmStage + 1);
$hmBloodId   = intval($player->huyetmach_id);
$hmQuality   = intval($player->huyetmach_quality);

// Mach dang chon: POST > GET > mach dau tien chua dat tran
$hmNode = $hmActionNode;
if ($hmNode <= 0 && isset($node)) $hmNode = intval($node);
if (!isset($hmDefs[$hmNode])) {
    $hmNode = 0;
    foreach ($hmDefs as $hmId => $hmDef) {
        if (intval($hmLevels[intval($hmId)]) < $hmCap) { $hmNode = intval($hmId); break; }
    }
    if ($hmNode <= 0) {
        $hmKeys = array_keys($hmDefs);
        $hmNode = intval($hmKeys[0]);
    }
}

// Bac thuc tinh dang xem: POST success > GET > bac hien tai
$hmView = $hmStage;
if (isset($_POST['hm_action']) && $_POST['hm_action'] === 'break' && $hmMsgOk && $hmNextTier) {
    $hmView = intval($hmNextTier['stage']);
}
if (isset($view)) {
    $hmGetView = intval($view);
    if (\player\huyetmach_vessel_tier($hmGetView) !== false) $hmView = $hmGetView;
}
$hmViewTier = \player\huyetmach_vessel_tier($hmView);

// ===== CHI PHI LUYEN HOA CUA MACH DANG CHON =====
$hmNodeDef = $hmDefs[$hmNode];
$hmNodeLv  = intval($hmLevels[$hmNode]);
$hmCost    = \player\huyetmach_vessel_refine_cost($hmNodeLv);
$hmMatName = \player\huyetmach_get_material_name($hmCost['material_id']);
$hmMatHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmCost['material_id']);
$hmLinhThach = intval($player->uyxb);
$hmAtCap   = ($hmNodeLv >= $hmCap);
$hmCanRefine = (!$hmAtCap
    && $hmMatHave >= $hmCost['material_amount']
    && $hmLinhThach >= $hmCost['linh_thach_cost']);

// ===== CHECK LIST THUC TINH =====
$hmGate   = \player\huyetmach_vessel_break_rows($sid, $dblj);
$hmReqRows = $hmGate['rows'];
$hmReady     = $hmGate['ready'];
$hmBossName  = $hmGate['boss_name'];

// ===== DINH DANG CHI SO =====
$hmStatOrder = array('atk', 'hp', 'crit', 'lifesteal', 'dodge', 'pen', 'reduce', 'luck');
$hmStatIcon  = array('atk' => '🗡️', 'hp' => '💗', 'crit' => '💥', 'lifesteal' => '🩸',
    'dodge' => '💨', 'pen' => '🏹', 'reduce' => '🧱', 'luck' => '🍀');
$hmStatDec   = array('atk' => 0, 'hp' => 0, 'crit' => 1, 'lifesteal' => 1,
    'dodge' => 1, 'pen' => 1, 'reduce' => 1, 'luck' => 1);
$hmStatSuf   = array('atk' => '', 'hp' => '', 'crit' => '%', 'lifesteal' => '%',
    'dodge' => '%', 'pen' => '%', 'reduce' => '%', 'luck' => '%');

$hmAttrRows = array();
foreach ($hmStatOrder as $hmStat) {
    $hmVal = floatval($hmB[$hmStat]);
    $hmTxt = ($hmStatDec[$hmStat] > 0) ? number_format($hmVal, $hmStatDec[$hmStat]) : number_format(round($hmVal));
    $hmAttrRows[] = array(
        'label' => \player\huyetmach_vessel_stat_label($hmStat),
        'icon'  => $hmStatIcon[$hmStat],
        'text'  => '+' . $hmTxt . $hmStatSuf[$hmStat]
    );
}

$hmTierColor = $hmTier['color'];
$hmTierIcon  = $hmTier['icon'];
$hmTierName  = $hmTier['name'];
// Bac thuc tinh -> class phan tang hieu ung (hm-fx0 .. hm-fx4), xem docs/UI_HUYETMACH.md
$hmFx = 'hm-fx' . max(0, min(4, $hmStage));
// Dao Pham (pham chat 10) -> chu chay cau vong tren badge
$hmDao = ($hmQuality >= 10) ? ' is-dao' : '';

// Dong tom tat bac dang xem
$hmViewParts = array();
if ($hmView == $hmStage) {
    $hmViewParts[] = 'trần <b>' . intval($hmViewTier['lv_cap']) . '</b>/mạch';
} else {
    $hmViewParts[] = 'cần tổng <b>' . intval($hmViewTier['total_lv_require']) . ' cấp</b>';
    $hmViewParts[] = 'cảnh giới <b>' . intval($hmViewTier['realm_min_level']) . '</b>';
    if (intval($hmViewTier['material_amount']) > 0) {
        $hmViewParts[] = htmlspecialchars(\player\huyetmach_get_material_name(intval($hmViewTier['material_id']))) . ' ×' . intval($hmViewTier['material_amount']);
    }
    if (intval($hmViewTier['linh_thach_cost']) > 0) {
        $hmViewParts[] = number_format(intval($hmViewTier['linh_thach_cost'])) . ' Linh Thạch';
    }
    if (intval($hmViewTier['boss_require']) === 1) $hmViewParts[] = 'giết boss mạch huyết';
}
$hmViewLine = $hmViewTier['icon'] . ' ' . htmlspecialchars($hmViewTier['name'])
    . ($hmView == $hmStage ? ' — đang tu' : ' — cần') . ' · ' . implode(' · ', $hmViewParts);

$hmMsNextLv = intval($hmB['next_milestone_lv']);
$hmMsPct = 100;
if ($hmMsNextLv > 0) {
    $hmMsPct = min(100, round(($hmTotal / $hmMsNextLv) * 100));
}

$hmNodeStat = $hmNodeDef['stat'];
$hmNodeLabel = \player\huyetmach_vessel_stat_label($hmNodeStat);
$hmNodeDec = $hmStatDec[$hmNodeStat];
$hmNodePerLvTxt = number_format(floatval($hmNodeDef['per_lv']), $hmNodeDec);

// ===== TOM TAT BIEN THAN (chi khi co huyet mach va stage >= 3) =====
$hmTfHtml = '';
if ($hmBloodId > 0 && $hmStage >= 3) {
    $hmTf = \player\huyetmach_get_transform($hmBloodId);
    if ($hmTf) {
        $hmTfCan = \player\huyetmach_can_transform($sid, $dblj);
        $hmTfCls = $hmTfCan['ok'] ? 'ok' : 'no';
        $hmTfTxt = $hmTfCan['ok'] ? 'Sẵn sàng' : htmlspecialchars($hmTfCan['msg']);
        $hmTfHtml = '<div class="hm-transform">'
            . '<span class="hm-transform-ico">' . $hmTf['icon'] . '</span>'
            . '<span><span class="hm-transform-name">' . htmlspecialchars($hmTf['name']) . '</span>'
            . ' · ' . intval($hmTf['cooldown_minutes']) . ' phút CD · ' . intval($hmTf['hp_cost_percent']) . '% HP</span>'
            . '<span class="hm-transform-state ' . $hmTfCls . '">' . $hmTfTxt . '</span>'
            . '<a class="hm-act hm-act-transform" href="?cmd=' . $tfcmd . '">Mở Biến Thân</a></div>';
    }
}
?>
<div class="hm-wrap <?= $hmFx . $hmDao ?>" style="--hm-tier:<?= $hmTierColor ?>;--hm-color:<?= $player->huyetmach_color ?>">
<!-- Thanh tab đầu trang (giống Linh Căn); bỏ tab Nhân Vật — đã có ở thanh trên -->
<nav class="hm-tabbar">
    <a class="on" href="?cmd=<?= $encode->encode("cmd=huyetmach&sid=$sid") ?>"><i>🩸</i><b>Huyết Mạch</b></a>
<?php if ($hmBloodId > 0): ?>
    <a href="?cmd=<?= $upcmd ?>"><i>🔼</i><b>Nâng Phẩm Chất</b></a>
    <a href="?cmd=<?= $bosscmd ?>"><i>👹</i><b>Boss Mạch Huyết</b></a>
<?php endif; ?>
</nav>
<?php if ($hmMsg !== ''): ?>
    <div class="hm-msg <?= $hmMsgOk ? 'ok' : 'err' ?>"><?= htmlspecialchars($hmMsg) ?></div>
<?php endif; ?>

<!-- ===== DAI 1: dinh danh + ray thuc tinh + mang mach + lo luyen ===== -->
<div class="hm-card hm-card-body">
    <div class="hm-head">
        <div class="hm-hrow">
            <span class="hm-title">HUYẾT MẠCH</span>
            <span class="hm-badge" style="border-color:<?= $hmTierColor ?>66;color:<?= $hmTierColor ?>"><i class="hm-badge-ico"><?= $hmTierIcon ?></i><?= htmlspecialchars($hmTierName) ?> · trần <?= $hmCap ?></span>
        </div>

        <div class="hm-rail">
<?php for ($hmT = 0; $hmT <= $hmMaxStage; $hmT++):
    $hmRow = \player\huyetmach_vessel_tier($hmT);
    if (!$hmRow) continue;
    $hmRowCls = 'hm-tier hm-t' . $hmT;
    if ($hmT < $hmStage) $hmRowCls .= ' is-done';
    elseif ($hmT == $hmStage) $hmRowCls .= ' is-cur';
    else $hmRowCls .= ' is-lock';
    if ($hmT == $hmView) $hmRowCls .= ' is-view';
    $hmRowCmd = $encode->encode("cmd=huyetmach&view=$hmT&sid=$sid");
    if ($hmT < $hmStage) $hmRowState = 'Đã đạt';
    elseif ($hmT == $hmStage) $hmRowState = 'Trần ' . intval($hmRow['lv_cap']);
    else $hmRowState = 'Cấp ' . intval($hmRow['realm_min_level']);
?>
<?php if ($hmT > 0): ?>
        <span class="hm-link<?= $hmT <= $hmStage ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
        <a class="<?= $hmRowCls ?>" style="border-color:<?= $hmRow['color'] ?>66;--tcolor:<?= $hmRow['color'] ?>" href="?cmd=<?= $hmRowCmd ?>">
            <span class="hm-tier-ico"><?= $hmRow['icon'] ?></span>
            <span class="hm-tier-name"><?= htmlspecialchars($hmRow['short']) ?></span>
            <span class="hm-tier-state"><?= $hmRowState ?></span>
        </a>
<?php endfor; ?>
    </div>
        <div class="hm-viewline"><span class="hm-phys"><?php
            if ($hmBloodId > 0) echo $player->huyetmach_icon . ' ' . htmlspecialchars($player->huyetmach_name) . ' · ' . htmlspecialchars(\player\huyetmach_quality_name($hmQuality));
            else echo 'Chưa có Huyết Mạch';
        ?></span> · <?= $hmViewLine ?></div>
<?php if ($hmBloodId <= 0): ?>
        <div class="hm-note">Bát Mạch vẫn luyện được khi chưa khai mở huyết mạch — dùng <b>Tố Thể Đan</b> để nhận huyết mạch.</div>
<?php endif; ?>
    </div>

    <div class="hm-forge-wrap">
        <div class="hm-stage">
        <img class="hm-figure" src="./images/huyetmach/kinh-mach.jpg?v=<?= $hmImgVer ?>" alt="Mạng mạch huyết">
        <span class="hm-aura" aria-hidden="true"></span>
        <span class="hm-sweep" aria-hidden="true"></span>
<?php foreach ($hmDefs as $hmGid => $hmGdef):
    $hmGid = intval($hmGid);
    $hmGlv = intval($hmLevels[$hmGid]);
    $hmGstat = $hmGdef['stat'];
    // 4 mach co anh rieng, 4 mach am dung ngoc chung + mau --ring
    $hmGfam = in_array($hmGstat, array('atk', 'hp', 'crit', 'lifesteal')) ? $hmGstat : '';
    $hmGimg = 'ngoc-' . ($hmGfam !== '' ? $hmGfam . '-' : '') . ($hmGlv >= $hmCap ? 'day' : ($hmGlv > 0 ? 'sang' : 'khoa'));
    $hmGcls = 'hm-gem slot-' . $hmGdef['slot'];
    if ($hmGid == $hmNode) $hmGcls .= ' is-sel';
    if ($hmGlv >= $hmCap) $hmGcls .= ' is-max';
    $hmGcmd = $encode->encode("cmd=huyetmach&node=$hmGid&sid=$sid");
    $hmGglow = ($hmCap > 0) ? round(min(1, $hmGlv / $hmCap) * 16) : 0;
?>
        <a class="<?= $hmGcls ?>" href="?cmd=<?= $hmGcmd ?>" title="<?= htmlspecialchars($hmGdef['desc']) ?>">
            <span class="hm-gem-disc" style="--ring:<?= $hmGlv >= $hmCap ? '#6ee7b7' : ($hmGlv > 0 ? $hmTierColor : '#8b93a0') ?>;--glow:<?= $hmGglow ?>px">
                <img class="hm-gem-img" src="./images/huyetmach/<?= $hmGimg ?>.png?v=<?= $hmImgVer ?>" alt="">
            </span>
            <span class="hm-gem-tag"><?= htmlspecialchars($hmGdef['short']) ?> <b><?= $hmGlv ?></b></span>
        </a>
<?php endforeach; ?>
    </div>

        <div class="hm-forge">
        <div class="hm-forge-top">
            <span class="hm-forge-name" style="color:<?= $hmTierColor ?>"><?= htmlspecialchars($hmNodeDef['name']) ?></span>
            <span class="hm-forge-lv">Cấp <b><?= $hmNodeLv ?></b> / <?= $hmCap ?></span>
            <span class="hm-forge-gain">+<?= $hmNodePerLvTxt ?>/cấp · đang <?= htmlspecialchars($hmAttrRows[array_search($hmNodeStat, $hmStatOrder)]['text']) ?> <?= htmlspecialchars($hmNodeLabel) ?></span>
        </div>
<?php if ($hmAtCap): ?>
        <div class="hm-capnote">Trần <?= $hmCap ?> cấp — phải Thức Tỉnh mới luyện tiếp được.</div>
<?php else: ?>
        <div class="hm-forge-row">
            <span class="hm-cost-item <?= $hmMatHave >= $hmCost['material_amount'] ? 'hm-cost-ok' : 'hm-cost-no' ?>">📦 <?= htmlspecialchars($hmMatName) ?> <b><?= $hmMatHave ?>/<?= $hmCost['material_amount'] ?></b></span>
            <span class="hm-cost-item <?= $hmLinhThach >= $hmCost['linh_thach_cost'] ? 'hm-cost-ok' : 'hm-cost-no' ?>">💰 Linh Thạch <b><?= number_format($hmLinhThach) ?>/<?= number_format($hmCost['linh_thach_cost']) ?></b></span>
        </div>
<?php endif; ?>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=huyetmach&node=$hmNode&sid=$sid") ?>">
            <input type="hidden" name="hm_action" value="refine">
            <input type="hidden" name="hm_node" value="<?= $hmNode ?>">
            <button type="submit" class="hm-act hm-act-refine<?= $hmCanRefine ? '' : ' is-off' ?>" <?= $hmCanRefine ? '' : 'disabled' ?>>Luyện Mạch</button>
        </form>
    </div>
    </div>
</div>

<!-- ===== DAI 2: thuoc tinh | thuc tinh (2 cot tren desktop) ===== -->
<div class="hm-card hm-cols">
    <div class="hm-col">
        <div class="hm-h3">Thuộc Tính Kích Hoạt</div>
        <div class="hm-attrs">
<?php foreach ($hmAttrRows as $hmAttr): ?>
            <div class="hm-attr"><span><?= $hmAttr['icon'] ?> <?= htmlspecialchars($hmAttr['label']) ?></span><b><?= $hmAttr['text'] ?></b></div>
<?php endforeach; ?>
        </div>
        <div class="hm-ms">
            <div class="hm-ms-top">
                <span><?php
                    if ($hmB['milestone_name'] !== '') echo htmlspecialchars($hmB['milestone_name']) . ' +' . floatval($hmB['milestone_percent']) . '%';
                    else echo 'Chưa mở mốc nào';
                ?></span>
                <span>Tổng cấp <?= $hmTotal ?><?php if ($hmMsNextLv > 0) echo ' / ' . $hmMsNextLv; ?></span>
            </div>
            <div class="hm-ms-bar"><div class="hm-ms-fill" style="width:<?= $hmMsPct ?>%"></div></div>
            <div class="hm-ms-note"><?php
                if ($hmB['next_milestone_name'] !== '') {
                    echo 'Mốc kế: ' . htmlspecialchars($hmB['next_milestone_name']) . ' (+' . floatval($hmB['next_milestone_percent']) . '%) khi tổng cấp đạt ' . $hmMsNextLv . '.';
                } else {
                    echo 'Toàn bộ mốc đã mở.';
                }
            ?></div>
        </div>
    </div>

    <div class="hm-col">
        <div class="hm-h3">Thức Tỉnh Huyết Mạch</div>
<?php if ($hmNextTier): ?>
        <div class="hm-gate">
            <span><?= $hmTierIcon ?> <?= htmlspecialchars($hmTierName) ?></span>
            <span class="hm-gate-arr">→</span>
            <span style="color:<?= $hmNextTier['color'] ?>"><?= $hmNextTier['icon'] ?> <?= htmlspecialchars($hmNextTier['name']) ?></span>
            <span class="hm-gate-cap">trần <?= intval($hmNextTier['lv_cap']) ?></span>
        </div>
        <div class="hm-reqs">
<?php foreach ($hmReqRows as $hmReqRow): ?>
            <div class="hm-req <?= $hmReqRow['ok'] ? 'ok' : 'no' ?>">[<?= $hmReqRow['ok'] ? 'Đủ' : 'Thiếu' ?>] <?= htmlspecialchars($hmReqRow['text']) ?></div>
<?php endforeach; ?>
        </div>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=huyetmach&view=" . intval($hmNextTier['stage']) . "&sid=$sid") ?>">
            <input type="hidden" name="hm_action" value="break">
            <button type="submit" class="hm-act hm-act-break<?= $hmReady ? '' : ' is-off' ?>" <?= $hmReady ? '' : 'disabled' ?>>Thức Tỉnh</button>
        </form>
<?php else: ?>
        <div class="hm-gate">
            <span><?= $hmTierIcon ?> <?= htmlspecialchars($hmTierName) ?></span>
            <span class="hm-gate-cap">trần <?= $hmCap ?></span>
        </div>
        <div class="hm-node-desc">Huyết thống đã đạt cực hạn của giống loài.</div>
<?php endif; ?>
    </div>
    <?= $hmTfHtml ?>
</div>

</div>
