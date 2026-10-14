<?php
// KHAI PHU — uong Khai Phu Dan (114): 1 lan = 1 thien phu MOI, hoac +1 pham cho thien phu trung lai.
// Logic: class/function/tianfu.php (tianfu_open) | Config: class/config/tianfu_config.json
// CSS: css/mystyle.css (block .tpo-*) | Route: cmd=tianfu_open (dang ky o Step 2)
// GET khong bao gio roll — chi POST tp_action=open (F5 tren GET = mat them 1 vien dan).

$tpoImgVer = '2026101201'; // bump khi doi anh trong images/tianfu/

// ===== POST: Khai Mo (khuon game/huyetmach.php:19-32) =====
$tpoMsg   = '';
$tpoMsgOk = false;
$tpoRes   = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action']) && $_POST['tp_action'] === 'open') {
    $tpoRes = \player\tianfu_open($sid, $dblj);
    if (is_array($tpoRes)) {
        $tpoMsg   = isset($tpoRes['msg']) ? $tpoRes['msg'] : '';
        $tpoMsgOk = !empty($tpoRes['ok']);
    }
}

$player = \player\getplayer($sid, $dblj);

// ===== Dan 114: so vien trong tui + gia that (dung cong thuc gia cua shop) =====
$tpoPill = \player\tianfu_open_pill();               // array('djid'=>114,'name'=>'Khai Phú Đan')
$tpoDjid = intval($tpoPill['djid']);
$tpoPst  = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpoPst->execute(array($sid, $tpoDjid));
$tpoHave = intval($tpoPst->fetchColumn());           // khong co dong -> false -> 0
$tpoDj   = \player\getdaoju($tpoDjid, $dblj);
$tpoPrice = $tpoDj ? max(50, intval($tpoDj->djyxb)) : 50000;   // = game/shangdian.php:309
$tpoW     = \player\tianfu_player_wallet($sid, $dblj);
$tpoCount = \player\tianfu_open_count($sid, $dblj);  // so lan da khai mo (player_tianfu_log kind='open')

// ===== Bang ty le 10 pham (doc config, chia tong — xem [CHOT]) =====
$tpoQs  = \player\tianfu_cfg_key('qualities');
$tpoSum = 0.0;
foreach ($tpoQs as $tpoQd) { $tpoSum += floatval($tpoQd['weight']); }

// ===== Ket qua (chi ton tai sau POST thanh cong) =====
$tpoROk    = (is_array($tpoRes) && !empty($tpoRes['ok']) && is_array($tpoRes['talent']));
$tpoRQ     = $tpoROk ? intval($tpoRes['quality']) : 0;
$tpoRQd    = $tpoROk ? \player\tianfu_quality_def($tpoRQ) : false;
$tpoRColor = $tpoROk ? \player\tianfu_quality_color($tpoRQ) : '#9e9e9e';
$tpoRFx    = $tpoROk ? \player\tianfu_fx_class($tpoRQ) : 0;   // tra VE SO (0..5), markup noi "tp-fx"
$tpoRName  = $tpoROk ? $tpoRes['talent']['name'] : '';
$tpoRIcon  = $tpoROk ? $tpoRes['talent']['icon'] : '';
$tpoRQName = $tpoROk ? \player\tianfu_quality_name($tpoRQ) : '';
$tpoRCap   = ($tpoROk && $tpoRQd) ? intval($tpoRQd['lv_cap']) : 0;
$tpoRNew   = ($tpoROk && !empty($tpoRes['is_new']));

// ===== URL — token ?cmd= dung MOT LAN, encode lai moi lan render =====
$tpoPostCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpoMainCmd = $encode->encode("cmd=tianfu&sid=$sid");
$tpoColCmd  = $encode->encode("cmd=tianfu_collection&sid=$sid");
$tpoZtCmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$tpoShopCmd = $encode->encode("cmd=shangdian&canshu2=gogoumai2&sid=$sid");
?>
<div class="tpo-wrap">
<?php if ($tpoMsg !== ''): ?>
    <div class="tp-msg <?= $tpoMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpoMsg) ?></div>
<?php endif; ?>

    <div class="tpo-card">
        <div class="tpo-title"><i class="tp-emoji">⚗</i> KHAI PHÚ</div>
        <div class="tpo-pill">
            <?= htmlspecialchars($tpoPill['name']) ?> trong túi: <b><?= $tpoHave ?></b>
            · đã khai mở <b><?= $tpoCount ?></b> lần
            · Linh Thạch <b><?= number_format($tpoW['uyxb'], 0, ',', '.') ?></b>
        </div>

<?php if ($tpoROk): ?>
        <div class="tpo-result tp-fx<?= $tpoRFx ?>" style="--tp-tier:<?= $tpoRColor ?>">
            <div class="tpo-result-name"><i class="tp-emoji"><?= $tpoRIcon ?></i> <?= htmlspecialchars($tpoRName) ?></div>
            <div class="tpo-result-q"><?= htmlspecialchars($tpoRQName) ?> · trần Lv <?= $tpoRCap ?></div>
            <div class="tpo-result-new"><?= $tpoRNew ? 'Thiên phú MỚI — đã vào Phú Các' : 'Đã có sẵn — tăng 1 phẩm chất' ?></div>
        </div>
<?php endif; ?>

        <div class="tpo-truth">Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy.</div>

        <form method="post" action="?cmd=<?= $tpoPostCmd ?>">
            <input type="hidden" name="tp_action" value="open">
            <button type="submit" class="tpo-act<?= $tpoHave > 0 ? '' : ' is-off' ?>" <?= $tpoHave > 0 ? '' : 'disabled' ?>>Khai Mở</button>
        </form>

        <table class="tpo-table">
            <tr><th>Phẩm</th><th>Danh xưng</th><th>Trần Lv</th><th>Tỷ lệ</th></tr>
<?php foreach ($tpoQs as $tpoQi => $tpoQd):
    $tpoPct = $tpoSum > 0 ? round(floatval($tpoQd['weight']) / $tpoSum * 100, 2) : 0.0;
?>
            <tr>
                <td class="q" style="color:<?= htmlspecialchars($tpoQd['color']) ?>"><b><i class="tp-emoji"><?= $tpoQd['icon'] ?></i> <?= intval($tpoQi) ?></b></td>
                <td><?= htmlspecialchars($tpoQd['name']) ?></td>
                <td><?= intval($tpoQd['lv_cap']) ?></td>
                <td><?= number_format($tpoPct, 2, ',', '.') ?>%</td>
            </tr>
<?php endforeach; ?>
        </table>

        <div class="tpo-foot">
            <?= $tpoHave > 0
                ? ('Giá ' . number_format($tpoPrice, 0, ',', '.') . ' Linh Thạch / viên · mua ở Thương Điếm, tab Đạo Cụ.')
                : ('<b>Hết đan</b> — mua ở Thương Điếm, tab Đạo Cụ, giá ' . number_format($tpoPrice, 0, ',', '.') . ' Linh Thạch.') ?><br>
            <a class="tp-act tp-act-ghost" href="?cmd=<?= $tpoShopCmd ?>">Mua đan</a>
        </div>
    </div>

<!-- ===== TABBAR 4 NUT (nut thu 3 dang .on) ===== -->
<nav class="tp-tabbar">
    <a href="?cmd=<?= $tpoMainCmd ?>"><i class="tp-emoji">🌟</i><b>Thiên Phú</b></a>
    <a href="?cmd=<?= $tpoColCmd ?>"><i class="tp-emoji">🗄</i><b>Phú Các</b></a>
    <a class="on" href="?cmd=<?= $encode->encode("cmd=tianfu_open&sid=$sid") ?>"><i class="tp-emoji">⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpoZtCmd ?>"><i class="tp-emoji">👤</i><b>Nhân Vật</b></a>
</nav>
</div>
