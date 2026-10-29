<?php
/**
 * Túi Đạo Cụ - giao diện mới
 * Giữ nguyên logic bán đạo cụ, chỉ cập nhật HTML/CSS.
 */

$danPopup = '';

function is_daoju_usable($djid, $dblj){
    $cfg = \player\getdaoju($djid, $dblj);
    if (!$cfg) {
        return false;
    }
    if (isset($cfg->djuse) && $cfg->djuse != '') {
        return true;
    }
    if (isset($cfg->use_effect) && !empty($cfg->use_effect)) {
        return true;
    }
    return false;
}

if (!isset($player)) {
    $player = \player\getplayer($sid, $dblj);
}

$self = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'game.php';

$stmt = $dblj->prepare("SELECT djid, djsum, djused FROM playerdaoju WHERE sid = :sid");
$stmt->execute(array(':sid' => $sid));
$cxjg = $stmt;
$retdj = array();
if ($cxjg){
    $retdj = $cxjg->fetchAll(PDO::FETCH_ASSOC);
}

// Ưu tiên vật phẩm có thể dùng (có djuse hoặc use_effect) lên đầu
foreach ($retdj as $k => $row) {
    $retdj[$k]['_idx'] = $k;
}
usort($retdj, function($a, $b) use ($dblj){
    $ua = is_daoju_usable(intval($a['djid']), $dblj) ? 0 : 1;
    $ub = is_daoju_usable(intval($b['djid']), $dblj) ? 0 : 1;
    if ($ua != $ub) {
        return ($ua < $ub) ? -1 : 1;
    }
    $ia = isset($a['_idx']) ? intval($a['_idx']) : 0;
    $ib = isset($b['_idx']) ? intval($b['_idx']) : 0;
    return ($ia < $ib) ? -1 : 1;
});

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$tishi = '';

if (isset($canshu)){
    $getyxb = 0;
    // Giá bán NPC theo daoju_sell_config.json (Đột Phá Đan 10%, Cường Hóa Thạch không bán) — kiểm TRƯỚC khi trừ đạo cụ
    $sellDj = \player\getdaoju($djid,$dblj);
    $sellUnit = $sellDj ? \player\daoju_sell_price($djid, $sellDj->djyxb) : 0;
    $sellMap = array('maichu1' => 1, 'maichu5' => 5, 'maichu10' => 10);
    if ($sellUnit <= 0 && isset($sellMap[$canshu])) {
        $tishi = "Vật phẩm này không bán cho NPC được (có thể treo ở Phường Thị)";
    } else {
    if ($canshu == "maichu1"){
        $ret = \player\deledjsum($djid,1,$sid,$dblj);
        if ($ret){
            \player\changeyxb(1,$sellUnit,$sid,$dblj);
            $getyxb = $sellUnit;
        }
    }
    if ($canshu == "maichu5"){
        $ret = \player\deledjsum($djid,5,$sid,$dblj);
        if ($ret){
            \player\changeyxb(1,$sellUnit*5,$sid,$dblj);
            $getyxb = $sellUnit*5;
        }
    }
    if ($canshu == "maichu10"){
        $ret = \player\deledjsum($djid,10,$sid,$dblj);
        if ($ret){
            \player\changeyxb(1,$sellUnit*10,$sid,$dblj);
            $getyxb = $sellUnit*10;
        }
    }
    $tishi = "Bán thành công, nhận được {$getyxb} Linh thạch";
    }
}

if ($cmd == 'usedaoju'){
    $djid = isset($djid) ? intval($djid) : 0;
    $djRow = null;
    foreach ($retdj as $r){
        if (intval($r['djid']) == $djid){ $djRow = $r; break; }
    }
    $djsum = $djRow ? intval($djRow['djsum']) : 0;
    $ret = \player\usedaoju($djid, $djsum, $sid, $dblj);
    $tishi = $ret['msg'];
    $danMsg = addslashes($ret['msg']);
    $danCls = $ret['ok'] ? 'success' : 'error';
    $danIcon = $ret['ok'] ? '✓' : '!';
    $danPopup = '<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">'
        . '<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>'
        . '<script type="text/javascript">'
        . 'function tcgAlert(msgHtml){var mask=$(\'<div class="mask"></div>\');var box=$(\'<div id="animationTipBox" class="' . $danCls . '" style="width:300px;height:200px;margin-left:-150px;margin-top:-100px;"></div>\');var inner=\'<div class="tcg-alert-icon">' . $danIcon . '</div><div class="dec_txt">\'+msgHtml+\'</div><div class="tcg-popup-btns"><div class="tcg-popup-ok">Xác nhận</div></div>\';box.html(inner);$(\'body\').append(mask).append(box);box.find(\'.tcg-popup-ok\').on(\'click\',function(){box.remove();mask.remove();});mask.on(\'click\',function(){box.remove();mask.remove();});}'
        . 'setTimeout(function(){tcgAlert(\'' . $danMsg . '\');},300);'
        . '</script>';
}

if (isset($_GET['use_notice']) && $_GET['use_notice'] != '') {
    $tishi = urldecode($_GET['use_notice']);
}

$djhtml = '';
$hangshu = 0;
$totalKind = 0;
$totalNum = 0;
// Chia trang: 20 loại / trang (lưới 5 x 4); tổng loại / tổng số lượng vẫn đếm toàn bộ
$djPer = 20;
$yeshu = isset($yeshu) ? max(0, intval($yeshu)) : 0;

for ($i=0;$i<count($retdj);$i++){
    $djid = $retdj[$i]['djid'];
    $djsum = intval($retdj[$i]['djsum']);
    $daojuConfig = \player\getdaoju($djid, $dblj);
    $djname = $daojuConfig && isset($daojuConfig->djname) ? htmlspecialchars($daojuConfig->djname, ENT_QUOTES, 'UTF-8') : ('Đạo cụ #' . intval($djid));

    if ($djsum > 0){
        $hangshu = $hangshu + 1;
        $totalKind++;
        $totalNum += $djsum;
        if ($hangshu <= $yeshu || $hangshu > $yeshu + $djPer) continue;

        $chakandj = $encode->encode("cmd=djinfo&djid=$djid&uid=$player->uid&sid=$sid");
        $maichu1 = $encode->encode("cmd=getbagdj&canshu=maichu1&djid=$djid&yeshu=$yeshu&uid=$player->uid&sid=$sid");
        $maichu5 = $encode->encode("cmd=getbagdj&canshu=maichu5&djid=$djid&yeshu=$yeshu&uid=$player->uid&sid=$sid");
        $maichu10 = $encode->encode("cmd=getbagdj&canshu=maichu10&djid=$djid&yeshu=$yeshu&uid=$player->uid&sid=$sid");
        $usedj = $encode->encode("cmd=usedaoju&djid=$djid&yeshu=$yeshu&uid=$player->uid&sid=$sid");
        $usedjEffect = $encode->encode("cmd=use_dj&djid=$djid&uid=$player->uid&sid=$sid");
        $daojuCfg = $daojuConfig;
        $canUseDjuse = ($daojuCfg && isset($daojuCfg->djuse) && $daojuCfg->djuse != '');
        $canUseEffect = ($daojuCfg && isset($daojuCfg->use_effect) && !empty($daojuCfg->use_effect));
        $djused = intval($retdj[$i]['djused']);
        $useLeft = max(0, 2 - $djused);

        $useBtn = '';
        if ($canUseDjuse) {
            $useBtn = '<a class="bag-btn use-pill" href="'.$self.'?cmd='.$usedj.'">Dùng'.($useLeft>0 ? " ($useLeft)" : ' (2/2)').'</a>';
        } elseif ($canUseEffect) {
            $useBtn = '<a class="bag-btn use-pill" href="'.$self.'?cmd='.$usedjEffect.'">Dùng</a>';
        }

        $djhtml .= '
        <div class="bag-item-card daoju">
            <div class="bag-item-index">'.$hangshu.'</div>
            <div class="bag-item-icon">'.$djsum.'</div>
            <div class="bag-item-main">
                <a class="bag-item-name" href="'.$self.'?cmd='.$chakandj.'">'.$djname.'</a>
                <div class="bag-item-meta">Đạo cụ'.(($canUseDjuse && $useLeft <= 0) ? ' • <b style="color:#fca5a5">Đã dùng 2/2</b>' : '').'</div>
            </div>
            <div class="bag-item-actions">
                '.$useBtn.'
                <a class="bag-btn sell-one" href="'.$self.'?cmd='.$maichu1.'">Bán 1</a>
                <!-- <a class="bag-btn sell" href="'.$self.'?cmd='.$maichu5.'">Bán 5</a><a class="bag-btn sell" href="'.$self.'?cmd='.$maichu10.'">Bán 10</a> -->
            </div>
        </div>';
    }
}

$djPager = '';
if ($totalKind > $djPer) {
    $djPrev = $yeshu > 0 ? $encode->encode("cmd=getbagdj&yeshu=" . max(0, $yeshu - $djPer) . "&sid=$sid") : '';
    $djNext = $yeshu + $djPer < $totalKind ? $encode->encode("cmd=getbagdj&yeshu=" . ($yeshu + $djPer) . "&sid=$sid") : '';
    $djPager = '<div class="bag-pagination">'
        . ($djPrev !== '' ? '<a class="bag-page-btn" href="?cmd=' . $djPrev . '">‹ Trang trước</a>' : '')
        . '<span class="bag-page-num">' . (intval($yeshu / $djPer) + 1) . ' / ' . intval(ceil($totalKind / $djPer)) . '</span>'
        . ($djNext !== '' ? '<a class="bag-page-btn" href="?cmd=' . $djNext . '">Trang sau ›</a>' : '')
        . '</div>';
}

if ($djhtml == '') {
    $djhtml = '<div class="bag-empty"><div class="bag-empty-icon">🎒</div><div>Kho đạo cụ đang trống</div></div>';
}

$getbagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");
$getbagypcmd = $encode->encode("cmd=getbagyp&sid=$sid");
$getbagjncmd = $encode->encode("cmd=getbagjn&sid=$sid");
$getbagydcmd = $encode->encode("cmd=getbagyd&sid=$sid");

$noticeHtml = '';
if ($tishi != '') {
    $noticeHtml = '<div class="bag-notice">'.$tishi.'</div>';
}

$bagdjhtml =<<<HTML
<div class="bag-page bag-compact">
    <div class="bag-hero">
        <div>
            <div class="bag-label">TÚI ĐẠO CỤ</div>
            <h1>Kho Linh Vật</h1>
            <p>Quản lý các loại đạo cụ đang sở hữu, xem thông tin vật phẩm và bán bớt để nhận Linh thạch.</p>
        </div>
        <div class="bag-count-box">
            <span>Loại đạo cụ</span>
            <strong>$totalKind</strong>
            <em>Tổng x$totalNum</em>
        </div>
    </div>

    <div class="bag-tabs">
        <a class="bag-tab" href="?cmd=$getbagzbcmd">Trang bị</a>
        <span class="bag-tab active">Đạo cụ</span>
        <a class="bag-tab" href="?cmd=$getbagypcmd">Dược phẩm</a>
        <a class="bag-tab" href="?cmd=$getbagjncmd">Kỹ năng</a>
        <a class="bag-tab" href="?cmd=$getbagydcmd">Đan dược</a>
    </div>


    $noticeHtml

    <div class="bag-list">
        $djhtml
    </div>

    $djPager

</div>
$danPopup
HTML;

echo $bagdjhtml;
?>
