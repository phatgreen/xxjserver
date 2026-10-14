<?php
// THIEN PHU — PHU CAC: luoi 18 thien phu, panel chi tiet mo bang cham (server-render theo GET id),
// 3 nut hanh dong: Nang Cap / Dot Pham / Bat vao Phu Vi.
// Logic: class/function/tianfu.php | CSS: css/mystyle.css (block .tpc-*) | Anh: images/tianfu/
// Khuon POST + goi ham: game/tianfu.php (Task 8), game/huyetmach.php:19-32.
// Ten bien deu tpc* vi parse_str (game.php:114) bien moi cap trong chuoi cmd= thanh bien toan cuc.

$tpcImgVer = '2026101201'; // doi khi thay anh trong images/tianfu/ (giong $tpImgVer Task 8)

// ===== 1) GET (parse_str sinh bien toan cuc id/slot): mon dang mo + o dang chon =====
$tpcSel  = isset($id)   ? intval($id)   : 0;
$tpcPick = isset($slot) ? intval($slot) : 0;
if ($tpcPick < 1 || $tpcPick > 4) { $tpcPick = 0; }

// ===== 2) POST: dung 3 hanh dong cua Task 8, giu nguyen panel sau khi ghi =====
$tpcMsg = '';
$tpcMsgOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action'])) {
    $tpcPid = isset($_POST['tf_id']) ? intval($_POST['tf_id']) : 0;
    if ($tpcPid > 0) { $tpcSel = $tpcPid; }
    if ($_POST['tp_action'] === 'lvup') {
        $tpcR = \player\tianfu_lvup($sid, $dblj, $tpcPid);
    } elseif ($_POST['tp_action'] === 'break') {
        $tpcR = \player\tianfu_break($sid, $dblj, $tpcPid);
    } elseif ($_POST['tp_action'] === 'slot') {
        $tpcPslot = isset($_POST['tf_slot']) ? intval($_POST['tf_slot']) : 0;
        $tpcR = \player\tianfu_slot_set($sid, $dblj, $tpcPid, $tpcPslot);
    } else {
        $tpcR = false;
    }
    if (is_array($tpcR)) {
        $tpcMsg = isset($tpcR['msg']) ? $tpcR['msg'] : '';
        $tpcMsgOk = !empty($tpcR['ok']);
    }
}

// ===== 3) DOC DU LIEU (SAU POST de con so la gia tri moi) =====
$tpcOwn      = \player\tianfu_owned($sid, $dblj);
$tpcAct      = \player\tianfu_activated($sid, $dblj);
$tpcW        = \player\tianfu_player_wallet($sid, $dblj);
$tpcB        = \player\tianfu_bonuses($sid, $dblj);
$tpcSlots    = \player\tianfu_slot_defs();
$tpcSlotOpen = \player\tianfu_slot_open(intval($tpcW['ulv']));
$tpcCap      = \player\tianfu_cap_percent();

// Chon mon mo panel: id hop le > mon dang nam o Phu Vi > mon dau tien da so huu > id 1
if ($tpcSel < 1 || !isset($tpcOwn[$tpcSel])) {
    $tpcSel = 0;
    foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['slot']) > 0) { $tpcSel = intval($tpcR0['id']); break; } }
    if ($tpcSel === 0) {
        foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['have']) > 0) { $tpcSel = intval($tpcR0['id']); break; } }
    }
    if ($tpcSel === 0) { $tpcSel = 1; }
}
$tpcDef   = \player\tianfu_def($tpcSel);
$tpcRow   = $tpcOwn[$tpcSel];
$tpcHave  = intval($tpcRow['have']) > 0;
$tpcQ     = intval($tpcRow['quality']);
$tpcLv    = intval($tpcRow['lv']);
$tpcSlot  = intval($tpcRow['slot']);
$tpcCapLv = \player\tianfu_lv_cap($tpcQ);
$tpcColor = $tpcHave ? \player\tianfu_quality_color($tpcQ) : '#9e9e9e';
$tpcGroupName = '';
if ($tpcDef) {
    $tpcGrps = \player\tianfu_groups();
    $tpcGid  = intval($tpcDef['group']);
    if (isset($tpcGrps[$tpcGid])) { $tpcGroupName = $tpcGrps[$tpcGid]['name']; }
}
$tpcMasterQ = (isset($tpcAct[1]) && is_array($tpcAct[1])) ? intval($tpcAct[1]['quality']) : 0;
$tpcFx      = \player\tianfu_fx_class($tpcMasterQ);
$tpcHaveCount = 0;
foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['have']) > 0) { $tpcHaveCount++; } }

// ===== 4) Panel: hang dieu kien Nang Cap (3 dong) + Dot Pham (4 dong) — chi khi da so huu =====
$tpcLvRows = array();
$tpcBrRows = array();
$tpcBrOk   = false;
$tpcNextQ  = false;
if ($tpcHave) {
    $tpcLvRows = \player\tianfu_lvup_rows($sid, $dblj, $tpcSel);
    if ($tpcQ < 10) {
        $tpcBrRows = \player\tianfu_break_rows($sid, $dblj, $tpcSel);
        $tpcBrOk = true;
        foreach ($tpcBrRows as $tpcBr) { if (empty($tpcBr['ok'])) { $tpcBrOk = false; } }
        $tpcNextQ = \player\tianfu_quality_def($tpcQ + 1);
    }
}
$tpcAtCap   = ($tpcLv >= $tpcCapLv);
$tpcCanLvup = ($tpcHave && !$tpcAtCap);
foreach ($tpcLvRows as $tpcLr) { if (empty($tpcLr['ok'])) { $tpcCanLvup = false; } }

// ===== 5) Cong + dong gop cua mon dang mo (cong chinh; nhom Ngo Tinh co ca cong phu) =====
$tpcGateKey  = $tpcDef ? $tpcDef['gate'] : '';
$tpcGateName = '';
$tpcGateUnit = '%';
$tpcContr    = array();
if ($tpcDef) {
    $tpcGateName = \player\tianfu_gate_name($tpcGateKey);
    $tpcGateDef  = \player\tianfu_gate_def($tpcGateKey);
    if ($tpcGateDef && isset($tpcGateDef['unit'])) { $tpcGateUnit = $tpcGateDef['unit']; }
    $tpcContr = \player\tianfu_contributions($tpcDef);
}
$tpcPerTxt  = isset($tpcContr[$tpcGateKey]) ? ('+' . floatval($tpcContr[$tpcGateKey]) . '%/cấp') : '';
$tpcSubTxt  = isset($tpcContr['exp_monster'])
    ? (' · +' . floatval($tpcContr['exp_monster']) . '%/cấp ' . \player\tianfu_gate_name('exp_monster')) : '';
$tpcGateNow = $tpcGateKey !== '' ? floatval($tpcB[$tpcGateKey]) : 0.0;
$tpcLvCost  = \player\tianfu_lv_cost($tpcLv);

// ===== 6) Dan 114 trong tui (in o panel mon chua so huu) — giong Task 8 Step 2 =====
$tpcPill = \player\tianfu_open_pill();
$tpcPillSum = 0;
$tpcPst = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpcPst->execute(array($sid, intval($tpcPill['djid'])));
$tpcProw = $tpcPst->fetch(PDO::FETCH_ASSOC);
if ($tpcProw) { $tpcPillSum = intval($tpcProw['djsum']); }

// ===== 7) URL — MOI lan render phai encode lai (token tieu mot lan) =====
$tpcPostCmd    = $encode->encode("cmd=tianfu_collection&id=$tpcSel&sid=$sid");
$tpcMainCmd    = $encode->encode("cmd=tianfu&sid=$sid");
$tpcOpenPageCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpcZtCmd      = $encode->encode("cmd=zhuangtai&sid=$sid");
?>
<div class="tpc-wrap tp-fx<?= $tpcFx ?>">
<?php if ($tpcMsg !== ''): ?>
    <div class="tp-msg <?= $tpcMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpcMsg) ?></div>
<?php endif; ?>

    <div class="tpc-cover">PHÚ CÁC<i class="tp-emoji">🗄</i><span>18 thiên phú bẩm sinh</span></div>
    <div class="tpc-head">
        <span>Sưu tầm</span>
        <span>Đã có <b><?= $tpcHaveCount ?>/18</b> · tf <b><?= intval($tpcW['tf']) ?></b> · Linh Thạch <b><?= number_format($tpcW['uyxb'], 0, ',', '.') ?></b></span>
    </div>
<?php if ($tpcPick > 0): ?>
    <div class="tp-note">Đang chọn thiên phú cho <b>Phú Vi <?= $tpcPick ?></b> — chạm một ô trong lưới bên dưới.</div>
<?php endif; ?>

<!-- ===== LUOI 18 O (3 cot mobile / 6 cot desktop) ===== -->
    <div class="tpc-grid">
<?php for ($tpcI = 1; $tpcI <= 18; $tpcI++):
    $tpcR2  = $tpcOwn[$tpcI];
    $tpcHas = intval($tpcR2['have']) > 0;
    $tpcRq  = intval($tpcR2['quality']);
    $tpcRs  = intval($tpcR2['slot']);
    $tpcCls = 'tpc-cell' . ($tpcHas ? ' is-have' : ' is-lock')
              . ($tpcRs > 0 ? ' is-active' : '') . ($tpcI === $tpcSel ? ' is-sel' : '');
    $tpcSty = $tpcHas ? ' style="--tp-cell:' . \player\tianfu_quality_color($tpcRq) . '"' : '';
    $tpcCellCmd = $encode->encode("cmd=tianfu_collection&id=$tpcI&sid=$sid");
?>
        <a class="<?= $tpcCls ?>"<?= $tpcSty ?> href="?cmd=<?= $tpcCellCmd ?>">
<?php if ($tpcRs > 0): ?>
            <span class="tpc-slot-tag">PV<?= $tpcRs ?></span>
<?php endif; ?>
            <i class="tp-emoji"><?= $tpcR2['icon'] ?></i>
            <b><?= htmlspecialchars($tpcR2['short']) ?></b>
            <span><?php
                echo $tpcHas
                    ? ('Lv ' . intval($tpcR2['lv']) . ' · ' . \player\tianfu_quality_name($tpcRq))
                    : 'Chưa khai mở';
            ?></span>
        </a>
<?php endfor; ?>
    </div>

<!-- ===== PANEL CHI TIET (mo bang cham, server-render) ===== -->
    <div class="tpc-detail" style="--tp-cell:<?= $tpcColor ?>">
        <div>
            <span class="tpc-detail-name"><i class="tp-emoji"><?= $tpcRow['icon'] ?></i> <?= htmlspecialchars($tpcDef ? $tpcDef['name'] : '?') ?></span>
            <span class="tpc-detail-q"><?php
                echo $tpcHave
                    ? (htmlspecialchars(\player\tianfu_quality_name($tpcQ)) . ' · trần Lv ' . $tpcCapLv)
                    : 'Chưa khai mở';
            ?></span>
        </div>
        <div class="tpc-detail-desc"><?= htmlspecialchars($tpcDef ? $tpcDef['desc'] : '') ?></div>
        <div class="tpc-detail-rows">
            <div><i class="tp-emoji">🧩</i> <?= htmlspecialchars($tpcGroupName) ?> · <b><?= htmlspecialchars($tpcGateName) ?></b> <?= $tpcPerTxt ?><?= $tpcSubTxt ?> <span class="tp-attr-cap">(trần <?= $tpcCap ?>%)</span></div>
<?php if ($tpcHave): ?>
            <div><i class="tp-emoji">📈</i> Lv <b><?= $tpcLv ?></b>/<?= $tpcCapLv ?> · phí kế tiếp <b><?= intval($tpcLvCost["tf"]) ?></b> tf + <b><?= number_format(intval($tpcLvCost["linh_thach"]), 0, ',', '.') ?></b> Linh Thạch</div>
            <div class="<?= $tpcSlot > 0 ? 'ok' : 'no' ?>"><i class="tp-emoji"><?= $tpcSlot > 0 ? '✅' : '⭕' ?></i> <?php
                echo $tpcSlot > 0
                    ? ('Đang phát huy — Phú Vi ' . $tpcSlot)
                    : 'Chưa phát huy — phải đặt vào Phú Vi';
            ?></div>
            <div>Cộng <?= htmlspecialchars($tpcGateName) ?> hiện hưởng: <b><?= $tpcGateNow ?><?= htmlspecialchars($tpcGateUnit) ?></b> (xem trang Thiên Phú)</div>
<?php else: ?>
            <div class="no"><i class="tp-emoji">🔒</i> Chưa khai mở — cần <b><?= htmlspecialchars($tpcPill['name']) ?></b>. Trong túi có <b><?= $tpcPillSum ?></b> viên.</div>
<?php endif; ?>
        </div>
<?php if ($tpcHave && count($tpcLvRows) > 0): ?>
        <div class="tp-reqs"><?php foreach ($tpcLvRows as $tpcLr): ?>
            <div><span class="<?= empty($tpcLr['ok']) ? 'no' : 'ok' ?>"><?= empty($tpcLr['ok']) ? '✗' : '✓' ?></span> <?= htmlspecialchars($tpcLr['text']) ?></div>
<?php endforeach; ?>
        </div>
<?php endif; ?>
<?php if ($tpcHave && $tpcQ < 10 && count($tpcBrRows) > 0): ?>
        <div class="tp-gate" style="--tp-color:<?= $tpcColor ?>"><i class="tp-emoji"><?= \player\tianfu_quality_icon($tpcQ) ?></i> <?= htmlspecialchars(\player\tianfu_quality_name($tpcQ)) ?> → <i class="tp-emoji"><?= $tpcNextQ['icon'] ?></i> <?= htmlspecialchars($tpcNextQ['name']) ?> · trần <?= intval($tpcNextQ['lv_cap']) ?></div>
        <div class="tp-reqs"><?php foreach ($tpcBrRows as $tpcBr): ?>
            <div><span class="<?= empty($tpcBr['ok']) ? 'no' : 'ok' ?>"><?= empty($tpcBr['ok']) ? '✗' : '✓' ?></span> <?= htmlspecialchars($tpcBr['text']) ?></div>
<?php endforeach; ?>
        </div>
<?php elseif ($tpcHave && $tpcQ >= 10): ?>
        <div class="tp-reqs">Đạo Phẩm là cực hạn của thiên phú — không đột phá thêm được.</div>
<?php endif; ?>

<?php if ($tpcHave): ?>
        <div class="tpc-acts">
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="lvup">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <button type="submit" class="tpc-act<?= $tpcCanLvup ? '' : ' is-off' ?>" <?= $tpcCanLvup ? '' : 'disabled' ?>>Nâng Cấp</button>
            </form>
<?php if ($tpcQ < 10): ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="break">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <button type="submit" class="tpc-act<?= $tpcBrOk ? '' : ' is-off' ?>" <?= $tpcBrOk ? '' : 'disabled' ?>>Đột Phá</button>
            </form>
<?php endif; ?>
<?php foreach ($tpcSlots as $tpcSd):
    $tpcSn = intval($tpcSd['i']);
    $tpcSOpen = in_array($tpcSn, $tpcSlotOpen);
    $tpcSHolder = isset($tpcAct[$tpcSn]) ? intval($tpcAct[$tpcSn]['id']) : 0;
    $tpcSConf = ($tpcSOpen && $tpcSHolder !== $tpcSel)
        ? \player\tianfu_slot_conflict($sid, $dblj, $tpcSel, $tpcSn, $tpcAct) : false;
?>
<?php if (!$tpcSOpen): ?>
            <div class="tp-reqs"><span class="no">[Khóa]</span> Phú Vi <?= $tpcSn ?> cần cấp <?= intval($tpcSd['ulv']) ?>.</div>
<?php elseif ($tpcSConf !== false): ?>
            <div class="tp-reqs"><span class="no">[Trùng]</span> Phú Vi <?= $tpcSn ?> trùng môn <?= htmlspecialchars($tpcSConf) ?>.</div>
<?php elseif ($tpcSHolder === $tpcSel): ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <input type="hidden" name="tf_slot" value="0">
                <button type="submit" class="tpc-act">Bỏ PV<?= $tpcSn ?></button>
            </form>
<?php else: ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <input type="hidden" name="tf_slot" value="<?= $tpcSn ?>">
                <button type="submit" class="tpc-act"><?= $tpcPick === $tpcSn ? '▶ ' : '' ?>PV<?= $tpcSn ?><?= $tpcSHolder > 0 ? ' (thay)' : '' ?></button>
            </form>
<?php endif; ?>
<?php endforeach; ?>
        </div>
<?php else: ?>
        <div class="tpc-acts">
            <a class="tpc-act" href="?cmd=<?= $tpcOpenPageCmd ?>">Khai Phú</a>
        </div>
<?php endif; ?>
    </div>

<!-- ===== TABBAR 4 NUT (nut thu 2 dang .on) ===== -->
<nav class="tp-tabbar">
    <a href="?cmd=<?= $tpcMainCmd ?>"><i class="tp-emoji">🌟</i><b>Thiên Phú</b></a>
    <a class="on" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&id=$tpcSel&sid=$sid") ?>"><i class="tp-emoji">🗄</i><b>Phú Các</b></a>
    <a href="?cmd=<?= $tpcOpenPageCmd ?>"><i class="tp-emoji">⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpcZtCmd ?>"><i class="tp-emoji">👤</i><b>Nhân Vật</b></a>
</nav>
</div>
