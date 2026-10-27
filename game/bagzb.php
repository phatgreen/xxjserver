<?php

$tscg = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<font id="success"></font>
<script type="text/javascript">
setTimeout(function(){
    if(document.all){document.getElementById("success").click();}
    else{
        var e=document.createEvent("MouseEvents");
        e.initEvent("click",true,true);
        document.getElementById("success").dispatchEvent(e);
    }
},500);
$('#success').click(function(){
    popup({type:'success',msg:"Phân giải thành công",delay:2000,bg:true,clickDomCancel:true,width:280,height:140});
});
</script>
HTML;

$tssb = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<font id="error"></font>
<script type="text/javascript">
setTimeout(function(){
    if(document.all){document.getElementById("error").click();}
    else{
        var e=document.createEvent("MouseEvents");
        e.initEvent("click",true,true);
        document.getElementById("error").dispatchEvent(e);
    }
},500);
$('#error').click(function(){
    popup({type:'error',msg:"Thất bại",delay:2000,bg:true,clickDomCancel:true,width:280,height:140});
});
</script>
HTML;

$player = player\getplayer($sid,$dblj);
$tishi = '';
$ts = '';

$dialogLib = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
function tcgConfirm(msgHtml, yesHref){
    var mask = $('<div class="mask"></div>');
    var box = $('<div id="animationTipBox" style="width:300px;height:225px;margin-left:-150px;margin-top:-112px;"></div>');
    var inner = '<div class="tcg-confirm-icon">?</div>'
        + '<div class="dec_txt">' + msgHtml + '</div>'
        + '<div class="tcg-popup-btns">'
        + '<div class="tcg-popup-ok">Xác nhận</div>'
        + '<div class="tcg-popup-cancel">Hủy bỏ</div>'
        + '</div>';
    box.html(inner);
    $('body').append(mask).append(box);
    box.find('.tcg-popup-ok').on('click', function(){ window.location.href = yesHref; });
    box.find('.tcg-popup-cancel').on('click', function(){ box.remove(); mask.remove(); });
    mask.on('click', function(){ box.remove(); mask.remove(); });
}
function tcgAlert(msgHtml){
    var mask = $('<div class="mask"></div>');
    var box = $('<div id="animationTipBox" style="width:300px;height:225px;margin-left:-150px;margin-top:-100px;"></div>');
    var inner = '<div class="tcg-alert-icon">!</div>'
        + '<div class="dec_txt">' + msgHtml + '</div>'
        + '<div class="tcg-popup-btns"><div class="tcg-popup-ok">Xác nhận</div></div>';
    box.html(inner);
    $('body').append(mask).append(box);
    box.find('.tcg-popup-ok').on('click', function(){ box.remove(); mask.remove(); });
    mask.on('click', function(){ box.remove(); mask.remove(); });
}
</script>
HTML;

/**
 * Gỡ trang bị khỏi ô đang mặc trước khi xoá, tránh để lại ô tham chiếu tới trang bị đã mất.
 */
function tcg_bagzb_unequip_if_worn($player, $zbnowid, $dblj, $sid)
{
    for ($i = 1; $i <= 16; $i++) {
        if (intval($player->{"tool$i"}) === intval($zbnowid)) {
            $dblj->exec("UPDATE game1 SET tool$i = 0 WHERE sid = " . $dblj->quote($sid));
            player\player_cache_clear($sid);
            return true;
        }
    }

    return false;
}

if (isset($canshu)){
    if ($canshu=='maichu'){
        $zbnowid = intval($zbnowid);
        if ($zbnowid <= 0) { $tishi = "ID trang bị không hợp lệ."; } else {
        $mczb = \player\getzb($zbnowid,$dblj);
        if (!$mczb) { $tishi = "Không tìm thấy trang bị."; } else {
        $rarity = isset($mczb->rarity) ? intval($mczb->rarity) : 1;
        $qianghua = isset($mczb->qianghua) ? intval($mczb->qianghua) : 0;
        $sxzz = $rarity * 50 + $qianghua * 5;
        $mcls = round($sxzz);
        tcg_bagzb_unequip_if_worn($player, $zbnowid, $dblj, $sid);
        $stmt = $dblj->prepare("DELETE FROM playerzhuangbei WHERE zbnowid = :zbnowid AND sid = :sid");
        $stmt->execute(array(':zbnowid' => $zbnowid, ':sid' => $sid));
        $mcret = $stmt->rowCount();
        if ($mcret){
            $ret = \player\changeyxb(1,$sxzz,$sid,$dblj);
            $tishi = "Bán [$mczb->zbname], nhận được: $mcls linh thạch";
        } else {
            $tishi = "Ngươi không có trang bị này, không thể bán!";
        }
        }}
    }
}

if (!isset($yeshu)){
    $yeshu = 0;
}
$yeshu = max(0, intval($yeshu));

    if ($cmd == 'delezb'){
    $zbnowid = isset($zbnowid) ? intval($zbnowid) : 0;
    if ($zbnowid > 0) {
    $zhuangbei = \player\getzb($zbnowid,$dblj);
    if ($zhuangbei && intval($zhuangbei->uid) === intval($player->uid) && $zhuangbei->sid === $sid) {
    $fjls = $zhuangbei->qianghua * 20 + 20;
    $ret = \player\changeyxb(2,$fjls,$sid,$dblj);
    if ($ret){
        tcg_bagzb_unequip_if_worn($player, $zbnowid, $dblj, $sid);
        $stmt = $dblj->prepare("DELETE FROM playerzhuangbei WHERE zbnowid = :zbnowid AND sid = :sid");
        $stmt->execute(array(':zbnowid' => $zbnowid, ':sid' => $sid));
        if ($stmt->rowCount() === 0) {
            // Nháy đúp hai lần: lần thứ hai không xoá được dòng nào,
            // phải trả lại linh thạch nếu không sẽ thành chỗ farm đồ miễn phí.
            \player\changeyxb(1,$fjls,$sid,$dblj);
            $tishi = "Phân giải thất bại! Trang bị không còn trong túi đồ.";
            $ts .= "".$tssb."";
        } else {
        $qhs = round($zhuangbei->qianghua*$zhuangbei->qianghua+($zhuangbei->zbgj+$zhuangbei->zbfy)/2);
        $sjs = mt_rand(1,100);
        if ($sjs <= 30){
            $sjs = mt_rand(1,100);
            if ($sjs>90){$qhs = $qhs + 3;}
            elseif($sjs>80){$qhs = $qhs + 2;}
            elseif ($sjs>70){$qhs = $qhs + 1;}
        }
        \player\adddj($sid,1,$qhs,$dblj);
        if ($qhs >= 0){
            $tishi .= "Phân giải thành công! Nhận được: ".$qhs." Cường hóa thạch! Mất ".$fjls." linh thạch!";
            $ts .= "".$tscg."";
        }
        }
    }else{
        $tishi = "Linh thạch không đủ!";
        $ts .= "".$tssb."";
    }
    } elseif ($zbnowid > 0) {
        $tishi = "Ngươi không có trang bị này, không thể phân giải!";
        $ts .= "".$tssb."";
    }
    }
}

$cxjg = $dblj->prepare("select * from playerzhuangbei WHERE sid = ? ORDER BY zbid DESC LIMIT $yeshu,10");
$cxjg->execute(array($sid));
$retzb = $cxjg->fetchAll(PDO::FETCH_ASSOC);

$cxjg = $dblj->prepare("select count(*) from playerzhuangbei where sid = ?");
$cxjg->execute(array($sid));
$zbcount = $cxjg->fetchColumn();

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$zbhtml = '';
$fanye='';

if ($yeshu!=0){
    $shangcanshu=$yeshu-10;
    if ($shangcanshu < 0) $shangcanshu = 0;
    $shangyiye = $encode->encode("cmd=getbagzb&yeshu=$shangcanshu&sid=$sid");
    $fanye .= '<a class="bag-page-btn" href="?cmd='.$shangyiye.'">‹ Trang trước</a>';
}
if ($yeshu +10 < $zbcount){
    $xiacanshu=$yeshu+10;
    $xiayiye = $encode->encode("cmd=getbagzb&yeshu=$xiacanshu&sid=$sid");
    $fanye .= '<a class="bag-page-btn" href="?cmd='.$xiayiye.'">Trang sau ›</a>';
}
if ($fanye!=''){
    $fanye = '<div class="bag-pagination">'.$fanye.'</div>';
}

$hangshu = 0;
$arr = array($player->tool1,$player->tool2,$player->tool3,$player->tool4,$player->tool5,$player->tool6,$player->tool7,$player->tool8,$player->tool9,$player->tool10,$player->tool11,$player->tool12,$player->tool13,$player->tool14,$player->tool15,$player->tool16);

for ($i=0;$i<count($retzb);$i++){
    $zbnowid = $retzb[$i]['zbnowid'];
    $hangshu = $hangshu + 1;
    $zhuangbei = \player\getzb($zbnowid,$dblj);
    $zbname = ($zhuangbei && isset($zhuangbei->zbname) && trim($zhuangbei->zbname) != '')
        ? $zhuangbei->zbname
        : (isset($retzb[$i]['zbname']) && trim($retzb[$i]['zbname']) != '' ? $retzb[$i]['zbname'] : 'Trang bị #'.intval($retzb[$i]['zbid']));
    $zbqh = ($zhuangbei && isset($zhuangbei->qianghua)) ? intval($zhuangbei->qianghua) : intval($retzb[$i]['qianghua']);
    $qhhtml = '';
    if($zbqh>0){$qhhtml = "+".$zbqh;}

    $chakanzb = $encode->encode("cmd=chakanzb&zbnowid=$zbnowid&uid=$player->uid&sid=$sid");
    $zbys = isset($zhuangbei->zbys) ? $zhuangbei->zbys : '#ffffff';
    $isEquip = in_array($zbnowid,$arr);

    // Badge nguyên tố + phẩm chất (hệ thống mới)
    $zbElem = isset($zhuangbei->element) ? $zhuangbei->element : 'none';
    $zbRarity = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
    $rarityName = \player\zhuangbei_rarity_name($zbRarity);
    $rarityColor = \player\zhuangbei_rarity_color($zbRarity);
    $zbBadge = '';
    foreach (\player\zhuangbei_elements_parse($zbElem) as $nhEl) {
        $zbBadge .= '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color_ui($nhEl).'">'
            . \player\zhuangbei_element_icon($nhEl) . ' ' . \player\zhuangbei_element_name($nhEl) . '</span>';
    }
    $zbRarityHtml = '<span class="zb-badge rarity" style="color:'.$rarityColor.'">'.$rarityName.'</span>';

    $zbImg = \player\zhuangbei_image_path($zhuangbei, \player\zhuangbei_slot_icon(isset($zhuangbei->tool) ? $zhuangbei->tool : 0));

    $actions = '';
    if (!$isEquip){
        $delezb = $encode->encode("cmd=delezb&zbnowid=$zbnowid&sid=$sid");
        $equipSlot = ($zhuangbei && isset($zhuangbei->tool)) ? intval($zhuangbei->tool) : 0;
        $realmReq = \player\zhuangbei_realm_require($zhuangbei);
        $canEquip = ($equipSlot >= 1 && $equipSlot <= 16 && \player\zhuangbei_realm_ok($zhuangbei, $player));
        $resolveOnclick = "tcgConfirm('Xác nhận phân giải trang bị này?<br/>Hành động không thể hoàn tác.', '?cmd=" . $delezb . "');return false;";
        $actions .= '<a class="bag-btn resolve" href="#" onclick="' . $resolveOnclick . '">Phân Giải</a>';
        if ($canEquip){
            $equipzb = $encode->encode("cmd=setzbwz&zbwz=$equipSlot&zbnowid=$zbnowid&sid=$sid");
            $actions .= '<a class="bag-btn equip" href="?cmd='.$equipzb.'">Trang bị</a>';
        } else {
            $lockMsg = addslashes("Cảnh giới không đủ! Trang bị yêu cầu cấp $realmReq, đẳng cấp hiện tại của ngươi là {$player->ulv}. Không thể trang bị!");
            $onclick = "tcgAlert('" . $lockMsg . "');return false;";
            $actions .= '<a class="bag-btn equip locked" href="#" onclick="' . $onclick . '">Trang bị</a>';
        }
    }

    $statusHtml = $isEquip
        ? '<div class="bag-item-meta"><span class="meta-equipped">Đã trang bị</span></div>'
        : '';

    $zbhtml .= '
    <div class="bag-item-card zhuangbei'.($isEquip ? ' equipped' : '').'">
        <div class="bag-item-index">'.$hangshu.'</div>
        <div class="bag-item-icon"><img src="'.$zbImg.'" alt=""></div>
        <div class="bag-item-main">
            <a class="bag-item-name"'.\player\itemtip_attr_for($zhuangbei, $player, $dblj).' style="color:'.$zbys.'" href="?cmd='.$chakanzb.'">'.$zbname.'<span class="bag-enhance">'.$qhhtml.'</span></a>
            <div class="bag-item-badges">'.$zbRarityHtml.$zbBadge.'</div>
            '.$statusHtml.'
        </div>
        <div class="bag-item-actions">'.$actions.'</div>
    </div>';
}

if ($zbhtml == '') {
    $zbhtml = '<div class="bag-empty"><div class="bag-empty-icon">🎒</div><div>Kho trang bị đang trống</div></div>';
}

$getbagdjcmd = $encode->encode("cmd=getbagdj&sid=$sid");
$getbagypcmd = $encode->encode("cmd=getbagyp&sid=$sid");
$getbagjncmd = $encode->encode("cmd=getbagjn&sid=$sid");
$getbagydcmd = $encode->encode("cmd=getbagyd&sid=$sid");

$fromNum = $zbcount > 0 ? ($yeshu + 1) : 0;
$toNum = min($yeshu + 10, $zbcount);

$noticeHtml = '';
if ($tishi != '') {
    $noticeHtml = '<div class="bag-notice">'.$tishi.'</div>';
}

$toolhtml =<<<HTML
<div class="bag-page">
    <div class="bag-hero">
        <div class="bag-hero-bg"><img src="/images/bg/bag.png" alt=""></div>
        <div class="bag-hero-mask"></div>
        <div>
            <div class="bag-label">TÚI TRANG BỊ</div>
            <h1>Kho Bảo Vật</h1>
            <p>Quản lý trang bị đang sở hữu, bán vật phẩm dư thừa hoặc phân giải để nhận cường hóa thạch.</p>
        </div>
        <div class="bag-count-box">
            <span>Tổng trang bị</span>
            <strong>$zbcount</strong>
            <em>$fromNum - $toNum</em>
        </div>
    </div>

    <div class="bag-tabs">
        <span class="bag-tab active">Trang bị</span>
        <a class="bag-tab" href="?cmd=$getbagdjcmd">Đạo cụ</a>
        <a class="bag-tab" href="?cmd=$getbagypcmd">Dược phẩm</a>
        <a class="bag-tab" href="?cmd=$getbagjncmd">Kỹ năng</a>
        <a class="bag-tab" href="?cmd=$getbagydcmd">Đan dược</a>
    </div>

    $noticeHtml
    $ts
    $dialogLib

    <div class="bag-list">
        $zbhtml
    </div>

    $fanye

    <div class="bag-bottom">
        <a class="bag-back" href="#" onClick="javascript:history.back(-1);">Trở về</a>
        <a class="bag-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $toolhtml;
?>
