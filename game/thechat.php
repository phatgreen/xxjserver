<?php
// NHUC THAN — Toi luyen luc bo phan + dot pha cot cach.
// Logic: class/function/thechat.php (thechat_body_*)
// Config: class/config/thechat_body_config.json
// CSS:   css/mystyle.css (block .nb-*)

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$upcmd = $encode->encode("cmd=thechat_upgrade&sid=$sid");
$nbImgVer = '2026100901'; // bump khi doi anh trong images/thechat/ de thong bay dem cua trinh duyet

// ===== XU LY LUA CHON =====
$nbMsg = '';
$nbMsgOk = false;
$nbActionNode = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nb_action'])) {
    if ($_POST['nb_action'] === 'refine') {
        $nbActionNode = isset($_POST['nb_node']) ? intval($_POST['nb_node']) : 0;
        $nbResult = \player\thechat_body_refine($sid, $dblj, $nbActionNode);
    } elseif ($_POST['nb_action'] === 'break') {
        $nbResult = \player\thechat_body_break($sid, $dblj);
    } else {
        $nbResult = false;
    }
    if (is_array($nbResult)) {
        $nbMsg = $nbResult['msg'];
        $nbMsgOk = !empty($nbResult['ok']);
    }
}

$player = \player\getplayer($sid, $dblj);

$nbNodes  = \player\thechat_body_nodes();
$nbBody   = \player\thechat_body_bonuses($sid, $dblj);
$nbLevels = $nbBody['levels'];
$nbTotal  = intval($nbBody['total_lv']);
$nbCap    = intval($nbBody['cap']);
$nbStage  = intval($nbBody['stage']);
$nbMaxStage   = \player\thechat_body_tier_max_stage();
$nbTier       = \player\thechat_body_tier($nbStage);
$nbNextTier   = \player\thechat_body_tier($nbStage + 1);
$nbPhysiqueId = intval($player->thechat_id);

// Bo phang dang chon: POST > GET > bo dau tien chua dat tran
$nbNode = $nbActionNode;
if ($nbNode <= 0 && isset($node)) $nbNode = intval($node);
if (!isset($nbNodes[$nbNode])) {
    $nbNode = 0;
    foreach ($nbNodes as $nbId => $nbDef) {
        if (intval($nbLevels[intval($nbId)]) < $nbCap) { $nbNode = intval($nbId); break; }
    }
    if ($nbNode <= 0) {
        $nbKeys = array_keys($nbNodes);
        $nbNode = intval($nbKeys[0]);
    }
}

// Bac cot cach dang xem: POST dot pha > GET > bac hien tai
$nbView = $nbStage;
if (isset($_POST['nb_action']) && $_POST['nb_action'] === 'break' && $nbMsgOk && $nbNextTier) {
    $nbView = intval($nbNextTier['stage']);
}
if (isset($view)) {
    $nbGetView = intval($view);
    if (\player\thechat_body_tier($nbGetView) !== false) $nbView = $nbGetView;
}
$nbViewTier = \player\thechat_body_tier($nbView);

// ===== CHI PHI LUYEN HOA CUA BO PHANG DANG CHON =====
$nbNodeDef  = $nbNodes[$nbNode];
$nbNodeLv   = intval($nbLevels[$nbNode]);
$nbCost     = \player\thechat_body_refine_cost($nbNodeLv);
$nbMatName  = \player\thechat_get_material_name($nbCost['material_id']);
$nbMatHave  = \player\thechat_body_daoju_have($sid, $dblj, $nbCost['material_id']);
$nbLinhThach = intval($player->uyxb);
$nbAtCap    = ($nbNodeLv >= $nbCap);
$nbCanRefine = (!$nbAtCap
    && $nbMatHave >= $nbCost['material_amount']
    && $nbLinhThach >= $nbCost['linh_thach_cost']);

// ===== CHECK LIST DOT PHA =====
$nbBreakRows = array();
$nbBreakReady = true;
if ($nbNextTier) {
    $nbNeedTotal = intval($nbNextTier['total_lv_require']);
    $nbBreakReady = $nbBreakReady && ($nbTotal >= $nbNeedTotal);
    $nbBreakRows[] = array(
        'ok' => ($nbTotal >= $nbNeedTotal),
        'text' => 'Tổng cấp Lục Bộ Phận: ' . $nbTotal . ' / ' . $nbNeedTotal
    );

    $nbNeedLvl = intval($nbNextTier['realm_min_level']);
    $nbBreakReady = $nbBreakReady && (intval($player->ulv) >= $nbNeedLvl);
    $nbBreakRows[] = array(
        'ok' => (intval($player->ulv) >= $nbNeedLvl),
        'text' => 'Cảnh giới: cấp ' . intval($player->ulv) . ' / ' . $nbNeedLvl
    );

    $nbBmId = intval($nbNextTier['material_id']);
    $nbBmAmt = intval($nbNextTier['material_amount']);
    if ($nbBmId > 0 && $nbBmAmt > 0) {
        $nbBmHave = \player\thechat_body_daoju_have($sid, $dblj, $nbBmId);
        $nbBreakReady = $nbBreakReady && ($nbBmHave >= $nbBmAmt);
        $nbBreakRows[] = array(
            'ok' => ($nbBmHave >= $nbBmAmt),
            'text' => \player\thechat_get_material_name($nbBmId) . ': ' . $nbBmHave . ' / ' . $nbBmAmt
        );
    }

    $nbBmLt = intval($nbNextTier['linh_thach_cost']);
    if ($nbBmLt > 0) {
        $nbBreakReady = $nbBreakReady && ($nbLinhThach >= $nbBmLt);
        $nbBreakRows[] = array(
            'ok' => ($nbLinhThach >= $nbBmLt),
            'text' => 'Linh Thạch: ' . number_format($nbLinhThach) . ' / ' . number_format($nbBmLt)
        );
    }
}

// ===== DINH DANG CHI SO =====
$nbStatOrder = array('hp', 'atk', 'def', 'crit', 'lifesteal', 'exp');
$nbStatIcon  = array('hp' => '💗', 'atk' => '🗡️', 'def' => '🛡️', 'crit' => '💥', 'lifesteal' => '🩸', 'exp' => '🌀');
$nbStatDec   = array('hp' => 0, 'atk' => 0, 'def' => 0, 'crit' => 1, 'lifesteal' => 1, 'exp' => 1);

$nbAttrRows = array();
foreach ($nbStatOrder as $nbStat) {
    $nbVal = floatval($nbBody[$nbStat]);
    $nbTxt = ($nbStatDec[$nbStat] > 0) ? number_format($nbVal, $nbStatDec[$nbStat]) : number_format(round($nbVal));
    $nbAttrRows[] = array(
        'label' => \player\thechat_body_stat_label($nbStat),
        'icon'  => $nbStatIcon[$nbStat],
        'text'  => '+' . $nbTxt . ($nbStat === 'exp' ? '%' : '')
    );
}

$nbTierColor = \player\thechat_awaken_color($nbStage);
$nbTierIcon  = \player\thechat_awaken_icon($nbStage);
$nbTierName  = \player\thechat_awaken_name($nbStage);
// Bac hien tai -> class phan tang hieu ung (nb-fx0 .. nb-fx5), xem docs/UI_THECHAT.md muc 12
$nbFx = 'nb-fx' . max(0, min(5, $nbStage));

// Mot dong tom tat bac Cot Cach dang xem (thay cho khoi nb-tierinfo 4 dong cu)
$nbViewParts = array();
if ($nbView == $nbStage) {
    $nbViewParts[] = 'trần <b>' . intval($nbViewTier['lv_cap']) . '</b>/bộ phận';
} else {
    $nbViewParts[] = 'Cần tổng <b>' . intval($nbViewTier['total_lv_require']) . ' cấp</b>';
    $nbViewParts[] = 'cảnh giới <b>' . intval($nbViewTier['realm_min_level']) . '</b>';
    if (intval($nbViewTier['material_amount']) > 0) {
        $nbViewParts[] = htmlspecialchars(\player\thechat_get_material_name(intval($nbViewTier['material_id']))) . ' ×' . intval($nbViewTier['material_amount']);
    }
    if (intval($nbViewTier['linh_thach_cost']) > 0) {
        $nbViewParts[] = number_format(intval($nbViewTier['linh_thach_cost'])) . ' Linh Thạch';
    }
}
$nbViewLine = \player\thechat_awaken_icon($nbView) . ' ' . htmlspecialchars($nbViewTier['name'])
    . ($nbView == $nbStage ? ' — đang tu' : ' — cần') . ' · ' . implode(' · ', $nbViewParts);

$nbMsNextLv = intval($nbBody['next_milestone_lv']);
$nbMsPct = 100;
if ($nbMsNextLv > 0) {
    $nbMsPct = min(100, round(($nbTotal / $nbMsNextLv) * 100));
}

// Chuoi dinh dang cho bo phang dang chon
$nbNodeStat = $nbNodeDef['stat'];
$nbNodeLabel = \player\thechat_body_stat_label($nbNodeStat);
$nbNodeDec = $nbStatDec[$nbNodeStat];
$nbNodePerLvTxt = number_format(floatval($nbNodeDef['per_lv']), $nbNodeDec);
$nbNodeCurTxt = number_format(floatval($nbNodeDef['per_lv']) * $nbNodeLv * (1 + floatval($nbBody['milestone_percent']) / 100), $nbNodeDec);
?>
<div class="nb-wrap <?= $nbFx ?>" style="--nb-tier:<?= $nbTierColor ?>">
<!-- Thanh tab đầu trang (giống Linh Căn); bỏ tab Nhân Vật — đã có ở thanh trên -->
<nav class="nb-tabbar">
    <a class="on" href="?cmd=<?= $encode->encode("cmd=thechat&sid=$sid") ?>"><i>🧎</i><b>Thể Chất</b></a>
<?php if ($nbPhysiqueId > 0): ?>
    <a href="?cmd=<?= $upcmd ?>"><i>🔼</i><b>Nâng Phẩm Chất</b></a>
<?php endif; ?>
</nav>
<?php if ($nbMsg !== ''): ?>
    <div class="nb-msg <?= $nbMsgOk ? 'ok' : 'err' ?>"><?= htmlspecialchars($nbMsg) ?></div>
<?php endif; ?>

<!-- ===== DAI 1: dinh danh + ray Cot Cach + than hinh + lo luyen ===== -->
<div class="nb-card nb-card-body">
    <div class="nb-head">
        <div class="nb-hrow">
            <span class="nb-title">NHỤC THÂN</span>
            <span class="nb-badge" style="border-color:<?= $nbTierColor ?>66;color:<?= $nbTierColor ?>"><i class="nb-badge-ico"><?= $nbTierIcon ?></i><?= htmlspecialchars($nbTierName) ?> · trần <?= $nbCap ?></span>
        </div>

        <div class="nb-rail">
<?php for ($nbT = 0; $nbT <= $nbMaxStage; $nbT++):
    $nbRow = \player\thechat_body_tier($nbT);
    if (!$nbRow) continue;
    $nbRowCls = 'nb-tier nb-t' . $nbT;
    if ($nbT < $nbStage) $nbRowCls .= ' is-done';
    elseif ($nbT == $nbStage) $nbRowCls .= ' is-cur';
    else $nbRowCls .= ' is-lock';
    if ($nbT == $nbView) $nbRowCls .= ' is-view';
    $nbRowCmd = $encode->encode("cmd=thechat&view=$nbT&sid=$sid");
    if ($nbT < $nbStage) $nbRowState = 'Đã đạt';
    elseif ($nbT == $nbStage) $nbRowState = 'Trần ' . intval($nbRow['lv_cap']);
    else $nbRowState = 'Cấp ' . intval($nbRow['realm_min_level']);
?>
<?php if ($nbT > 0): ?>
        <span class="nb-link<?= $nbT <= $nbStage ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
        <a class="<?= $nbRowCls ?>" style="border-color:<?= \player\thechat_awaken_color($nbT) ?>66;--tcolor:<?= \player\thechat_awaken_color($nbT) ?>" href="?cmd=<?= $nbRowCmd ?>">
            <span class="nb-tier-ico"><?= \player\thechat_awaken_icon($nbT) ?></span>
            <span class="nb-tier-name"><?= htmlspecialchars($nbRow['name']) ?></span>
            <span class="nb-tier-state"><?= $nbRowState ?></span>
        </a>
<?php endfor; ?>
    </div>
        <div class="nb-viewline"><span class="nb-phys"><?php
            if ($nbPhysiqueId > 0) echo $player->thechat_icon . ' ' . htmlspecialchars($player->thechat_name) . ' · ' . htmlspecialchars(\player\thechat_quality_name(intval($player->thechat_quality)));
            else echo 'Chưa có Thể Chất';
        ?></span> · <?= $nbViewLine ?></div>
    </div>

    <div class="nb-forge-wrap">
        <div class="nb-stage">
        <img class="nb-figure" src="./images/thechat/nhuc-than.jpg?v=<?= $nbImgVer ?>" alt="Nhục Thân ngồi thiền">
        <span class="nb-aura" aria-hidden="true"></span>
        <span class="nb-sweep" aria-hidden="true"></span>
<?php foreach ($nbNodes as $nbGid => $nbGdef):
    $nbGid = intval($nbGid);
    $nbGlv = intval($nbLevels[$nbGid]);
    $nbGstat = $nbGdef['stat'];
    $nbGimg = 'ngoc-' . $nbGstat . '-khoa';
    if ($nbGlv >= $nbCap) $nbGimg = 'ngoc-' . $nbGstat . '-day';
    elseif ($nbGlv > 0) $nbGimg = 'ngoc-' . $nbGstat . '-sang';
    $nbGcls = 'nb-gem slot-' . $nbGdef['slot'];
    if ($nbGid == $nbNode) $nbGcls .= ' is-sel';
    if ($nbGlv >= $nbCap) $nbGcls .= ' is-max';
    $nbGcmd = $encode->encode("cmd=thechat&node=$nbGid&sid=$sid");
    $nbGglow = ($nbCap > 0) ? round(min(1, $nbGlv / $nbCap) * 16) : 0;
?>
        <a class="<?= $nbGcls ?>" href="?cmd=<?= $nbGcmd ?>" title="<?= htmlspecialchars($nbGdef['desc']) ?>">
            <span class="nb-gem-disc" style="--ring:<?= $nbGlv >= $nbCap ? '#6ee7b7' : ($nbGlv > 0 ? \player\thechat_awaken_color($nbStage) : '#8b93a0') ?>;--glow:<?= $nbGglow ?>px">
                <img class="nb-gem-img" src="./images/thechat/<?= $nbGimg ?>.png?v=<?= $nbImgVer ?>" alt="">
            </span>
            <span class="nb-gem-tag"><?= htmlspecialchars($nbGdef['short']) ?> <b><?= $nbGlv ?></b></span>
        </a>
<?php endforeach; ?>
    </div>

        <div class="nb-forge">
        <div class="nb-forge-top">
            <span class="nb-forge-name" style="color:<?= $nbTierColor ?>"><?= htmlspecialchars($nbNodeDef['name']) ?></span>
            <span class="nb-forge-lv">Cấp <b><?= $nbNodeLv ?></b> / <?= $nbCap ?></span>
            <span class="nb-forge-gain">+<?= $nbNodePerLvTxt ?>/cấp · đang +<?= $nbNodeCurTxt ?> <?= htmlspecialchars($nbNodeLabel) ?></span>
        </div>
<?php if ($nbAtCap): ?>
        <div class="nb-capnote">Trần <?= $nbCap ?> cấp — phải đột phá Cốt Cách mới luyện tiếp được.</div>
<?php else: ?>
        <div class="nb-forge-row">
            <span class="nb-cost-item <?= $nbMatHave >= $nbCost['material_amount'] ? 'nb-cost-ok' : 'nb-cost-no' ?>">📦 <?= htmlspecialchars($nbMatName) ?> <b><?= $nbMatHave ?>/<?= $nbCost['material_amount'] ?></b></span>
            <span class="nb-cost-item <?= $nbLinhThach >= $nbCost['linh_thach_cost'] ? 'nb-cost-ok' : 'nb-cost-no' ?>">💰 Linh Thạch <b><?= number_format($nbLinhThach) ?>/<?= number_format($nbCost['linh_thach_cost']) ?></b></span>
        </div>
<?php endif; ?>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=thechat&node=$nbNode&sid=$sid") ?>">
            <input type="hidden" name="nb_action" value="refine">
            <input type="hidden" name="nb_node" value="<?= $nbNode ?>">
            <button type="submit" class="nb-btn nb-btn-refine<?= $nbCanRefine ? '' : ' is-off' ?>" <?= $nbCanRefine ? '' : 'disabled' ?>>Luyện Hóa</button>
        </form>
    </div>
    </div>
</div>

<!-- ===== DAI 2: ket qua + canh cua (2 cot tren desktop) ===== -->
<div class="nb-card nb-cols">
    <div class="nb-col">
        <div class="nb-h3">Thuộc Tính Kích Hoạt</div>
        <div class="nb-attrs">
<?php foreach ($nbAttrRows as $nbAttr): ?>
            <div class="nb-attr"><span><?= $nbAttr['icon'] ?> <?= htmlspecialchars($nbAttr['label']) ?></span><b><?= $nbAttr['text'] ?></b></div>
<?php endforeach; ?>
        </div>
        <div class="nb-ms">
            <div class="nb-ms-top">
                <span><?php
                    if ($nbBody['milestone_name'] !== '') echo htmlspecialchars($nbBody['milestone_name']) . ' +' . floatval($nbBody['milestone_percent']) . '%';
                    else echo 'Chưa mở mốc nào';
                ?></span>
                <span>Tổng cấp <?= $nbTotal ?><?php if ($nbMsNextLv > 0) echo ' / ' . $nbMsNextLv; ?></span>
            </div>
            <div class="nb-ms-bar"><div class="nb-ms-fill" style="width:<?= $nbMsPct ?>%"></div></div>
            <div class="nb-ms-note"><?php
                if ($nbBody['next_milestone_name'] !== '') {
                    echo 'Mốc kế: ' . htmlspecialchars($nbBody['next_milestone_name']) . ' (+' . floatval($nbBody['next_milestone_percent']) . '%) khi tổng cấp đạt ' . $nbMsNextLv . '.';
                } else {
                    echo 'Toàn bộ mốc đã mở.';
                }
            ?></div>
        </div>
    </div>

    <div class="nb-col">
        <div class="nb-h3">Đột Phá Cốt Cách</div>
<?php if ($nbNextTier): ?>
        <div class="nb-gate">
            <span><?= $nbTierIcon ?> <?= htmlspecialchars($nbTierName) ?></span>
            <span class="nb-gate-arr">→</span>
            <span style="color:<?= \player\thechat_awaken_color(intval($nbNextTier['stage'])) ?>"><?= \player\thechat_awaken_icon(intval($nbNextTier['stage'])) ?> <?= htmlspecialchars($nbNextTier['name']) ?></span>
            <span class="nb-gate-cap">trần <?= intval($nbNextTier['lv_cap']) ?></span>
        </div>
        <div class="nb-reqs">
<?php foreach ($nbBreakRows as $nbReqRow): ?>
            <div class="nb-req <?= $nbReqRow['ok'] ? 'ok' : 'no' ?>"><?= $nbReqRow['ok'] ? "<span class='lc-req ok'>✅ Đủ</span>" : "<span class='lc-req bad'>❌ Thiếu</span>" ?> <?= htmlspecialchars($nbReqRow['text']) ?></div>
<?php endforeach; ?>
        </div>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=thechat&view=" . intval($nbNextTier['stage']) . "&sid=$sid") ?>">
            <input type="hidden" name="nb_action" value="break">
            <button type="submit" class="nb-btn nb-btn-break<?= $nbBreakReady ? '' : ' is-off' ?>" <?= $nbBreakReady ? '' : 'disabled' ?>>Đột Phá</button>
        </form>
<?php else: ?>
        <div class="nb-gate">
            <span><?= $nbTierIcon ?> <?= htmlspecialchars($nbTierName) ?></span>
            <span class="nb-gate-cap">trần <?= $nbCap ?></span>
        </div>
        <div class="nb-node-desc">Cốt cách đã đạt cực hạn.</div>
<?php endif; ?>
    </div>
</div>

</div>
