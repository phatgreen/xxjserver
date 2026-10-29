<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/11
 * Time: 18:54
 */
$jnid = isset($jnid) ? intval($jnid) : 0;
$jineng = \player\getjineng_once($jnid,$dblj);
if (!$jineng){
    echo "Không tìm thấy phù lục này.<br/><br/><a href=\"#\" onClick=\"javascript:history.back(-1);\">Trở Về</a><br/>";
    exit();
}
$duihuan = $encode->encode("cmd=jninfo&canshu=duihuan&jnid=$jnid&sid=$sid");
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$htmltishi = '';
$playerjn = \player\getplayerjineng($jnid,$sid,$dblj);

$daoju = \player\getplayerdaoju($sid,$jineng->jndj,$dblj);
$dhdaoju = \player\getdaoju($jineng->jndj,$dblj);
if (!$daoju){
    $daoju = \player\getdaoju($jineng->jndj,$dblj);
    $daoju->djsum = 0;
}

if (isset($canshu)){
    switch ($canshu){
        case 'duihuan':
            $ret = \player\deledjsum($jineng->jndj,$jineng->djcount,$sid,$dblj);
            if ($ret){
                \player\addjineng($jnid,1,$sid,$dblj);
                $htmltishi = "Đổi thành công<br/>";
                $playerjn = \player\getplayerjineng($jnid,$sid,$dblj);
                $daoju = \player\getplayerdaoju($sid,$jineng->jndj,$dblj);
            }else{
                $htmltishi = "Đạo cụ không đủ<br/>";
            }

            break;
        case 'setjn1':
            \player\changeplayersx('jn1',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 1 thành công<br/>";
            break;
        case 'setjn2':
            \player\changeplayersx('jn2',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 2 thành công<br/>";
            break;
        case 'setjn3':
            \player\changeplayersx('jn3',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 3 thành công<br/>";
            break;
    }


}

// ===== Giao diện (cùng khung với trang chi tiết đạo cụ) — logic đổi / gắn phù lục ở trên giữ nguyên =====
$jnH = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$jnName = $jnH($jineng->jnname);
$jnHave = $playerjn ? intval(isset($playerjn->jncount) ? $playerjn->jncount : 1) : 0;
$needName = $dhdaoju ? $jnH($dhdaoju->djname) : 'Đạo cụ';
$needHave = intval($daoju->djsum);
$needCount = intval($jineng->djcount);
$needOk = $needHave >= $needCount;
$equipBtns = '';
if ($playerjn) {
    foreach (array(1, 2, 3) as $slot) {
        $setCmd = $encode->encode("cmd=jninfo&canshu=setjn$slot&jnid=$jnid&sid=$sid");
        $equipBtns .= '<a class="djinfo-btn" href="?cmd=' . $setCmd . '">Gắn ô ' . $slot . '</a>';
    }
}
$jnStats = array(array('⚔️ Công kích', $jineng->jngj), array('🛡️ Phòng ngự', $jineng->jnfy), array('💥 Bạo kích', $jineng->jnbj), array('🩸 Hút máu', $jineng->jnxx));
?>
<div class="djinfo-page">
    <section class="djinfo-hero">
        <div class="zb-art" style="--ra:#f87171"><span class="bag-emo"><img class="item-ico" src="./images/daoju/phu_luc.png" alt=""></span><span class="zb-art-ra">Phù lục</span></div>
        <div class="djinfo-head">
            <div class="djinfo-eyebrow">Thông tin phù lục</div>
            <h1><?php echo $jnName; ?></h1>
            <p>Đang có: <b><?php echo $jnHave; ?></b></p>
        </div>
    </section>

    <?php if ($htmltishi !== '') echo '<div class="djinfo-notice ' . (strpos($htmltishi, 'thành công') !== false ? 'ok' : 'bad') . '">' . strip_tags($htmltishi) . '</div>'; ?>

    <section class="djinfo-stats">
        <?php foreach ($jnStats as $st) echo '<div class="djinfo-stat"><span>' . $st[0] . '</span><b' . (floatval($st[1]) > 0 ? '' : ' style="opacity:.5"') . '>+' . $jnH($st[1]) . '%</b></div>'; ?>
    </section>

    <section class="djinfo-section">
        <div class="djinfo-section-title">Trao đổi</div>
        <div class="jn-trade">
            <span>Cần <b><?php echo $needName; ?></b> <b style="color:<?php echo $needOk ? '#4ade80' : '#f87171'; ?>">(<?php echo $needHave; ?>/<?php echo $needCount; ?>)</b></span>
            <a class="djinfo-btn use-btn" href="?cmd=<?php echo $duihuan; ?>">Trao Đổi</a>
        </div>
    </section>

    <?php if ($equipBtns !== ''): ?>
    <section class="djinfo-section">
        <div class="djinfo-section-title">Trang bị phù lục</div>
        <div class="jn-equip"><?php echo $equipBtns; ?></div>
    </section>
    <?php endif; ?>

    <div class="djinfo-bottom">
        <a class="djinfo-btn" href="#" onClick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="djinfo-btn primary" href="?cmd=<?php echo $gonowmid; ?>">Đại Sảnh</a>
    </div>
</div>
