<?php
// THIEN PHU — man chinh: Chủ Phu (Phu Vi 1) + rail 6 nhom + dai kich 4 Phu Vi + 7 cong.
// Logic: class/function/tianfu.php | Config: class/config/tianfu_config.json
// CSS: css/mystyle.css (block .tp-*) | Anh: images/tianfu/
// Khuon goi ham + POST: game/huyetmach.php:1-60. Trang cu (7 o chia diem) da cat — spec §6.

$tpImgVer = '2026101201'; // bump khi doi anh trong images/tianfu/

// ===== POST: Nang Cap / Dot Pham / Bat-Thao Phu Vi (khuon game/huyetmach.php:19-32) =====
$tpMsg = '';
$tpMsgOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action'])) {
    $tpPid = isset($_POST['tf_id']) ? intval($_POST['tf_id']) : 0;
    if ($_POST['tp_action'] === 'lvup') {
        $tpR = \player\tianfu_lvup($sid, $dblj, $tpPid);
    } elseif ($_POST['tp_action'] === 'break') {
        $tpR = \player\tianfu_break($sid, $dblj, $tpPid);
    } elseif ($_POST['tp_action'] === 'slot') {
        $tpPslot = isset($_POST['tf_slot']) ? intval($_POST['tf_slot']) : 0;
        $tpR = \player\tianfu_slot_set($sid, $dblj, $tpPid, $tpPslot);
    } else {
        $tpR = false;
    }
    if (is_array($tpR)) {
        $tpMsg = isset($tpR['msg']) ? $tpR['msg'] : '';
        $tpMsgOk = !empty($tpR['ok']);
    }
}

// ===== DOC DU LIEU (sau POST de so lieu la gia tri moi) =====
$player   = \player\getplayer($sid, $dblj);
$tpGrps   = \player\tianfu_groups();
$tpGates  = \player\tianfu_cfg_key('gates');
$tpOwn    = \player\tianfu_owned($sid, $dblj);
$tpAct    = \player\tianfu_activated($sid, $dblj);
$tpB      = \player\tianfu_bonuses($sid, $dblj);
$tpW      = \player\tianfu_player_wallet($sid, $dblj);
$tpCap    = \player\tianfu_cap_percent();
$tpSlots  = \player\tianfu_slot_defs();
$tpOpened = \player\tianfu_slot_open(intval($player->ulv));

// Nhom dang xem: cmd=tianfu&grp=N > nhom cua chu phu > 1.  ($grp do parse_str sinh ra)
$tpView = isset($grp) ? intval($grp) : 0;
if (!isset($tpGrps[$tpView])) {
    $tpView = 1;
    $tpMid0 = intval($player->tianfu_id);
    if ($tpMid0 > 0 && isset($tpOwn[$tpMid0])) {
        $tpView = intval($tpOwn[$tpMid0]['group']);
    }
}
$tpG = $tpGrps[$tpView];

// Chu phu = o Phu Vi 1 (guong cot game1.tianfu_id — Task 3 Step "recalc_mirror")
$tpMasterId  = intval($player->tianfu_id);
$tpMaster    = ($tpMasterId > 0 && isset($tpOwn[$tpMasterId])) ? $tpOwn[$tpMasterId] : false;
$tpMasterDef = $tpMaster ? \player\tianfu_def($tpMasterId) : false;
$tpQ         = $tpMaster ? intval($tpMaster['quality']) : 0;
$tpLv        = $tpMaster ? intval($tpMaster['lv']) : 0;
$tpLvCap     = \player\tianfu_lv_cap($tpQ);
$tpFx        = \player\tianfu_fx_class($tpQ);
$tpTierColor = $tpQ > 0 ? \player\tianfu_quality_color($tpQ) : '#9e9e9e';
$tpQName     = $tpQ > 0 ? \player\tianfu_quality_name($tpQ) : 'Chưa khai phú';
$tpQIcon     = $tpQ > 0 ? \player\tianfu_quality_icon($tpQ) : '⚪';

// Dem so mon da so huu trong tung nhom — dung cho rail (is-done) + do sang cua noi (is-lit)
$tpGrpHave = array();
foreach ($tpOwn as $tpRow) {
    $tpGi = intval($tpRow['group']);
    if (!isset($tpGrpHave[$tpGi])) { $tpGrpHave[$tpGi] = 0; }
    if (intval($tpRow['have']) > 0) { $tpGrpHave[$tpGi]++; }
}

// Nang Cap: cong thuc phi doc tu config, tinh theo Lv HIEN TAI
$tpLvCost  = \player\tianfu_lv_cost($tpLv);
$tpCostTf  = intval($tpLvCost['tf']);
$tpCostLt  = intval($tpLvCost['linh_thach']);
$tpAtCap   = ($tpMaster && $tpLv >= $tpLvCap);
$tpTfOk    = ($tpW['tf'] >= $tpCostTf);
$tpLtOk    = ($tpW['uyxb'] >= $tpCostLt);
$tpCanLvup = ($tpMaster && !$tpAtCap && $tpTfOk && $tpLtOk);

// Cong chinh cua chu phu: %/cap (marginal) + gia tri gate dang chay (da cong moi o + clamp 30)
$tpGateKey  = $tpMasterDef ? $tpMasterDef['gate'] : '';
$tpGateName = $tpGateKey !== '' ? \player\tianfu_gate_name($tpGateKey) : '';
$tpGateUnit = $tpGateKey !== '' ? \player\tianfu_gate_unit($tpGateKey) : '%';
$tpPerTxt   = '';
if ($tpMasterDef) {
    $tpContr  = \player\tianfu_contributions($tpMasterDef);
    $tpPerTxt = '+' . floatval($tpContr[$tpGateKey]) . '%/cấp';
}
$tpNowPct = $tpGateKey !== '' ? floatval($tpB[$tpGateKey]) : 0.0;

// Moc tong Lv (thanh do)
$tpMsTotal = intval($tpB['total_lv']);
$tpMsNext  = intval($tpB['next_milestone_lv']);
$tpMsWidth = $tpMsNext > 0 ? min(100, round($tpMsTotal / $tpMsNext * 100, 1)) : 100;

// Dot Pham (chi khi co chu phu): 4 dong dieu kien + tinh tran cho nut
$tpBreakRows = array();
$tpBreakOk   = false;
$tpNextQd    = false;
if ($tpMaster && $tpQ < 10) {
    $tpBreakRows = \player\tianfu_break_rows($sid, $dblj, $tpMasterId);
    $tpBreakOk   = true;
    foreach ($tpBreakRows as $tpBr) { if (empty($tpBr['ok'])) { $tpBreakOk = false; } }
    $tpNextQd = \player\tianfu_quality_def($tpQ + 1);
}

// Dan 114 trong tui — in o trang thai "chua lap chu phu" (Task 4: djid tu config)
$tpPill    = \player\tianfu_open_pill();
$tpPillSum = 0;
$tpPst = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpPst->execute(array($sid, intval($tpPill['djid'])));
$tpProw = $tpPst->fetch(PDO::FETCH_ASSOC);
if ($tpProw) { $tpPillSum = intval($tpProw['djsum']); }

// URL: MOI lan render phai encode lai (token tieu mot lan — Global Constraints)
$tpSelfCmd = $encode->encode("cmd=tianfu&sid=$sid");
$tpCollCmd = $encode->encode("cmd=tianfu_collection&sid=$sid");
$tpOpenCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpZtCmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$tpPostCmd = $encode->encode("cmd=tianfu&grp=$tpView&sid=$sid");

?>
<div class="tp-wrap tp-fx<?= $tpFx ?>" style="--tp-tier:<?= $tpTierColor ?>;--tp-color:<?= $tpG['color'] ?>">
<?php if ($tpMsg !== ''): ?>
    <div class="tp-msg <?= $tpMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpMsg) ?></div>
<?php endif; ?>

<!-- ===== CARD 1: chu phu + rail 6 nhom + dai kich ===== -->
<div class="tp-card tp-card-main">
    <div class="tp-head">
        <div class="tp-hrow">
            <span class="tp-title">THIÊN PHÚ</span>
            <span class="tp-badge" style="border-color:<?= $tpTierColor ?>66;color:<?= $tpTierColor ?>"><i class="tp-badge-ico"><?= $tpQIcon ?></i><?= htmlspecialchars($tpQName) ?> · trần <?= $tpLvCap ?></span>
        </div>
    </div>

    <div class="tp-rail">
<?php $tpGl = array_values($tpGrps); $tpLast = count($tpGl) - 1;
foreach ($tpGl as $tpKi => $tpGrp):
    $tpGi   = intval($tpGrp['id']);
    $tpHave = isset($tpGrpHave[$tpGi]) ? $tpGrpHave[$tpGi] : 0;
    $tpCls  = 'tp-tier' . ($tpHave > 0 ? ' is-done' : '') . ($tpGi === intval($tpG['id']) ? ' is-view' : '');
    $tpGcmd = $encode->encode("cmd=tianfu&grp=$tpGi&sid=$sid");
?>
        <a class="<?= $tpCls ?>" href="?cmd=<?= $tpGcmd ?>"><span><i class="tp-emoji"><?= $tpGrp['icon'] ?></i></span><b><?= htmlspecialchars($tpGrp['name']) ?></b><em><?= $tpHave ?>/3</em></a>
<?php if ($tpKi < $tpLast): ?>
        <span class="tp-link<?= $tpHave > 0 ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
<?php endforeach; ?>
    </div>

    <div class="tp-viewline">Đang xem: <span style="color:<?= $tpG['color'] ?>"><i class="tp-emoji"><?= $tpG['icon'] ?></i> <?= htmlspecialchars($tpG['name']) ?></span> · Chủ phú: <?php
        if ($tpMaster) {
            echo '<span style="color:' . $tpTierColor . '"><i class="tp-emoji">' . $tpMaster['icon'] . '</i> ' . htmlspecialchars($tpMaster['name']) . '</span>';
        } else {
            echo '<span style="color:#64748b">chưa lập</span>';
        }
    ?></div>
<?php if (!$tpMaster): ?>
    <div class="tp-note">Chưa có Chủ Phú — vào <b>Phú Các</b> để lập, hoặc <b>Khai Phú</b> để uống <?= htmlspecialchars($tpPill['name']) ?>.</div>
<?php elseif (count($tpOpened) < 4): ?>
    <div class="tp-note dim">Còn <?= 4 - count($tpOpened) ?> Phú Vi chưa mở — cần cấp <?= intval($tpSlots[count($tpOpened)]['ulv']) ?>.</div>
<?php endif; ?>

    <div class="tp-forge-wrap">
        <div class="tp-stage">
            <img class="tp-stage-img" src="./<?= $tpG['img'] ?>?v=<?= $tpImgVer ?>" alt="<?= htmlspecialchars($tpG['name']) ?>">
            <span class="tp-aura" aria-hidden="true"></span>
            <span class="tp-sweep" aria-hidden="true"></span>
<?php if ($tpMaster && intval($tpMaster['group']) === intval($tpG['id'])): ?>
            <a class="tp-stage-chip" style="--tp-color:<?= $tpTierColor ?>" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&id=$tpMasterId&sid=$sid") ?>"><i class="tp-emoji"><?= $tpMaster['icon'] ?></i><b><?= htmlspecialchars($tpMaster['short']) ?></b></a>
<?php endif; ?>
        </div>

        <div class="tp-forge">
<?php if ($tpMaster): ?>
            <div class="tp-forge-name"><i class="tp-emoji"><?= $tpMaster['icon'] ?></i> <?= htmlspecialchars($tpMaster['name']) ?></div>
            <div class="tp-forge-lv">Cấp <b><?= $tpLv ?></b> / <?= $tpLvCap ?></div>
            <div class="tp-forge-pct"><?= $tpPerTxt ?> · <?= htmlspecialchars($tpGateName) ?>: <b><?= $tpNowPct ?><?= htmlspecialchars($tpGateUnit) ?></b> <span class="tp-attr-cap">(trần <?= $tpCap ?>%)</span></div>
<?php if ($tpAtCap): ?>
            <div class="tp-note dim">Trần <?= $tpLvCap ?> cấp — phải <b>Đột Phá</b> phẩm chất mới luyện tiếp được.</div>
<?php else: ?>
            <div class="tp-forge-cost">
                <span class="<?= $tpTfOk ? 'ok' : 'no' ?>"><i class="tp-emoji">🎯</i> Điểm tf <b><?= intval($tpW['tf']) ?>/<?= $tpCostTf ?></b></span>
                <span class="<?= $tpLtOk ? 'ok' : 'no' ?>"><i class="tp-emoji">💰</i> Linh Thạch <b><?= number_format($tpW['uyxb'], 0, ',', '.') ?>/<?= number_format($tpCostLt, 0, ',', '.') ?></b></span>
            </div>
            <form method="post" action="?cmd=<?= $tpPostCmd ?>">
                <input type="hidden" name="tp_action" value="lvup">
                <input type="hidden" name="tf_id" value="<?= $tpMasterId ?>">
                <button type="submit" class="tp-act tp-act-up<?= $tpCanLvup ? '' : ' is-off' ?>" <?= $tpCanLvup ? '' : 'disabled' ?>>Nâng Cấp</button>
            </form>
<?php endif; ?>
<?php else: ?>
            <div class="tp-forge-name">Chưa lập Chủ Phú</div>
            <div class="tp-forge-pct">Trong túi có <b><?= $tpPillSum ?></b> <?= htmlspecialchars($tpPill['name']) ?>. Mỗi lần khai mở là một thiên phú; trùng sẽ tăng thêm một phẩm chất.</div>
            <a class="tp-act tp-act-open" href="?cmd=<?= $tpOpenCmd ?>">Khai Phú</a>
<?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== CARD 2: 2 cot — ky nang kich hoat | 4 Phu Vi + Dot Pham ===== -->
<div class="tp-card tp-cols">
    <div class="tp-col">
        <div class="tp-h3">Kỹ Năng Kích Hoạt</div>
<?php foreach ($tpGates as $tpGkey => $tpGd):
    $tpVal = floatval($tpB[$tpGkey]);
    $tpVcls = $tpVal <= 0 ? ' is-zero' : ($tpVal >= $tpCap ? ' is-max' : '');
?>
        <div class="tp-attr">
            <span class="tp-attr-name"><?= htmlspecialchars($tpGd['name']) ?></span>
            <b class="tp-attr-val<?= $tpVcls ?>"><?= $tpVal ?><?= htmlspecialchars($tpGd['unit']) ?></b>
            <span class="tp-attr-cap">trần <?= $tpCap ?>%</span>
        </div>
<?php endforeach; ?>
        <div class="tp-ms">
            <div class="tp-ms-label"><span><?php
                    if ($tpB['milestone_name'] !== '') {
                        echo htmlspecialchars($tpB['milestone_name']) . ' +' . floatval($tpB['milestone_percent']) . '%';
                    } else {
                        echo 'Chưa đạt mốc nào';
                    }
                ?></span><span>Tổng cấp <?= $tpMsTotal ?><?php if ($tpMsNext > 0) echo ' / ' . $tpMsNext; ?></span></div>
            <div class="tp-ms-bar"><div class="tp-ms-fill" style="width:<?= $tpMsWidth ?>%"></div></div>
            <div class="tp-ms-done"><?php
                if ($tpB['next_milestone_name'] !== '') {
                    echo 'Mốc kế: ' . htmlspecialchars($tpB['next_milestone_name'])
                        . ' (+' . floatval($tpB['next_milestone_percent']) . '%) khi tổng cấp ' . $tpMsNext . '.';
                } else {
                    echo 'Toàn bộ mốc đã mở.';
                }
            ?></div>
        </div>
    </div>

    <div class="tp-col">
        <div class="tp-h3">4 Phú Vi</div>
<?php foreach ($tpSlots as $tpSd):
    $tpSi     = intval($tpSd['i']);
    $tpARow   = isset($tpAct[$tpSi]) ? $tpAct[$tpSi] : false;
    $tpSOpen  = in_array($tpSi, $tpOpened);
    $tpScls   = 'tp-slot' . ($tpARow ? ' is-on' : ($tpSOpen ? '' : ' is-locked'));
    $tpSColor = $tpARow ? \player\tianfu_quality_color(intval($tpARow['quality'])) : '#7dd3fc';
?>
        <div class="<?= $tpScls ?>" style="--tp-color:<?= $tpSColor ?>">
            <div class="tp-slot-head"><span><?= htmlspecialchars($tpSd['name']) ?></span><b>Ô <?= $tpSi ?></b></div>
<?php if ($tpARow): ?>
            <div class="tp-slot-name"><i class="tp-emoji"><?= $tpARow['icon'] ?></i> <?= htmlspecialchars($tpARow['name']) ?></div>
            <span class="tp-gate"><?= htmlspecialchars(\player\tianfu_gate_name($tpARow['gate'])) ?> · <?= htmlspecialchars(\player\tianfu_quality_name(intval($tpARow['quality']))) ?> · Lv <?= intval($tpARow['lv']) ?></span>
            <form method="post" action="?cmd=<?= $tpPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="0">
                <input type="hidden" name="tf_slot" value="<?= $tpSi ?>">
                <button type="submit" class="tp-act tp-act-ghost">Bỏ</button>
            </form>
<?php elseif ($tpSOpen): ?>
            <div class="tp-slot-name empty">— trống —</div>
            <a class="tp-act tp-act-ghost" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&slot=$tpSi&sid=$sid") ?>">Chọn ở Phú Các</a>
<?php else: ?>
            <div class="tp-slot-name empty">Chưa mở</div>
            <div class="tp-reqs"><span class="no">[Khóa]</span> Cần cấp <?= intval($tpSd['ulv']) ?>.</div>
<?php endif; ?>
        </div>
<?php endforeach; ?>

        <div class="tp-h3" style="margin-top:12px">Đột Phá Phẩm Chất</div>
<?php if (!$tpMaster): ?>
        <div class="tp-reqs">Chưa có Chủ Phú để đột phá.</div>
<?php elseif ($tpQ >= 10): ?>
        <div class="tp-gate" style="--tp-color:<?= $tpTierColor ?>"><?= $tpQIcon ?> <?= htmlspecialchars($tpQName) ?></div>
        <div class="tp-reqs">Đạo Phẩm là cực hạn của thiên phú.</div>
<?php else: ?>
        <div class="tp-gate" style="--tp-color:<?= $tpNextQd['color'] ?>"><i class="tp-emoji"><?= $tpQIcon ?></i> <?= htmlspecialchars($tpQName) ?> → <i class="tp-emoji"><?= $tpNextQd['icon'] ?></i> <?= htmlspecialchars($tpNextQd['name']) ?> · trần <?= intval($tpNextQd['lv_cap']) ?></div>
        <div class="tp-reqs">
<?php foreach ($tpBreakRows as $tpBr): ?>
            <div><span class="<?= $tpBr['ok'] ? 'ok' : 'no' ?>">[<?= $tpBr['ok'] ? 'Đủ' : 'Thiếu' ?>]</span> <?= htmlspecialchars($tpBr['text']) ?></div>
<?php endforeach; ?>
        </div>
        <form method="post" action="?cmd=<?= $tpPostCmd ?>">
            <input type="hidden" name="tp_action" value="break">
            <input type="hidden" name="tf_id" value="<?= $tpMasterId ?>">
            <button type="submit" class="tp-act tp-act-break<?= $tpBreakOk ? '' : ' is-off' ?>" <?= $tpBreakOk ? '' : 'disabled' ?>>Đột Phá</button>
        </form>
<?php endif; ?>
    </div>
</div>

<nav class="tp-tabbar">
    <a class="on" href="?cmd=<?= $tpSelfCmd ?>"><i>🌟</i><b>Thiên Phú</b></a>
    <a href="?cmd=<?= $tpCollCmd ?>"><i>🗄</i><b>Phú Các</b></a>
    <a href="?cmd=<?= $tpOpenCmd ?>"><i>⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpZtCmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
</div>
