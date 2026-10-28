<?php
$player = player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$shangdian    = $encode->encode("cmd=shangdian&canshu=gogoumai&sid=$sid");
$shangdian1   = $encode->encode("cmd=shangdian&canshu1=gogoumai1&sid=$sid");
$shopDaoju    = $encode->encode("cmd=shangdian&canshu2=gogoumai2&sid=$sid");
$shopDaojuMa  = $encode->encode("cmd=shangdian&canshu21=gogoumai2ma&sid=$sid");
$shopZhuang   = $encode->encode("cmd=shangdian&canshu3=gogoumai3&sid=$sid");
$shopZhuangMa = $encode->encode("cmd=shangdian&canshu31=gogoumai3ma&sid=$sid");

$currentTab = 'linh';
$noticeHtml = '';
$itemHtml = '';
$paymentModalHtml = '';
$page = isset($p) ? max(1, intval($p)) : 1;
$pageSize = 10;
$pagerHtml = '';

$kw = isset($kw) ? trim($kw) : '';
$kwParam = $kw != '' ? '&kw=' . urlencode($kw) : '';

$tskcg = <<<HTML
<span id="success" style="display:none"></span>
<script type="text/javascript">
setTimeout(function(){
    var el = document.getElementById("success");
    if(!el) return;
    if(document.all){ el.click(); }
    else{
        var e = document.createEvent("MouseEvents");
        e.initEvent("click", true, true);
        el.dispatchEvent(e);
    }
}, 500);
</script>
HTML;

$tsksb = <<<HTML
<span id="error" style="display:none"></span>
<script type="text/javascript">
setTimeout(function(){
    var el = document.getElementById("error");
    if(!el) return;
    if(document.all){ el.click(); }
    else{
        var e = document.createEvent("MouseEvents");
        e.initEvent("click", true, true);
        el.dispatchEvent(e);
    }
}, 500);
</script>
HTML;

function tcg_shop_text($value)
{
    return htmlspecialchars(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
}

function tcg_shop_filter_kw($list, $nameKey)
{
    global $kw;
    if ($kw == '' || !is_array($list)) {
        return $list;
    }
    $out = array();
    foreach ($list as $item) {
        if (isset($item[$nameKey]) && mb_stripos($item[$nameKey], $kw) !== false) {
            $out[] = $item;
        }
    }
    return $out;
}

function tcg_shop_page_cmd($encode, $sid, $mode, $page)
{
    global $kwParam;
    $page = max(1, intval($page));
    switch ($mode) {
        case 'ma':
            $cmd = $encode->encode("cmd=shangdian&canshu1=gogoumai1&p=$page&sid=$sid");
            break;
        case 'daoju':
            $cmd = $encode->encode("cmd=shangdian&canshu2=gogoumai2&p=$page&sid=$sid");
            break;
        case 'zhuang':
            $cmd = $encode->encode("cmd=shangdian&canshu3=gogoumai3&p=$page&sid=$sid");
            break;
        default:
            $cmd = $encode->encode("cmd=shangdian&canshu=gogoumai&p=$page&sid=$sid");
            break;
    }
    return $cmd . $kwParam;
}

function tcg_shop_item_html($index, $name, $color, $price, $unitName, $infoCmd, $payCmd, $isMagic, $img = '', $tipAttr = '')
{
    $icon = $isMagic ? '💠' : '💰';
    $priceClass = $isMagic ? 'purple' : 'gold';
    $safeName = tcg_shop_text($name);
    $safeUnit = tcg_shop_text($unitName);
    $iconHtml = $img != '' ? '<img src="'.$img.'" alt="">' : $icon;

    return <<<HTML
<div class="tcg-shop-item">
    <div class="tcg-shop-icon">$iconHtml</div>
    <div class="tcg-shop-info">
        <div class="tcg-shop-title-line">
            <span class="tcg-shop-index">[$index]</span>
            <a class="tcg-shop-name"{$tipAttr} href="?cmd=$infoCmd" style="color:$color!important">$safeName</a>
        </div>
        <div class="tcg-shop-price $priceClass">$price $safeUnit</div>
    </div>
    <a class="tcg-shop-buy" href="?cmd=$payCmd">Mua</a>
</div>
HTML;
}

function tcg_shop_payment_modal($itemName, $itemColor, $price, $unitName, $balance, $confirmCmd, $cancelCmd, $isMagic)
{
    $safeName = tcg_shop_text($itemName);
    $safeUnit = tcg_shop_text($unitName);
    $moneyClass = $isMagic ? 'purple' : 'gold';
    $afterBalance = $balance - $price;
    $enough = $balance >= $price;
    $statusClass = $enough ? 'ok' : 'fail';
    $statusText = $enough ? 'Số dư đủ để thanh toán' : 'Số dư không đủ';
    $confirmHtml = $enough
        ? '<a class="tcg-pay-confirm" href="?cmd='.$confirmCmd.'">Xác nhận thanh toán</a>'
        : '<span class="tcg-pay-confirm disabled">Không đủ số dư</span>';

    return <<<HTML
<div class="tcg-pay-overlay">
    <div class="tcg-pay-modal">
        <div class="tcg-pay-head">
            <div>
                <div class="tcg-pay-kicker">Đơn thanh toán</div>
                <div class="tcg-pay-title">Xác nhận mua vật phẩm</div>
            </div>
            <a class="tcg-pay-close" href="?cmd=$cancelCmd">×</a>
        </div>

        <div class="tcg-pay-item">
            <div class="tcg-pay-icon">🧾</div>
            <div>
                <div class="tcg-pay-label">Vật phẩm</div>
                <div class="tcg-pay-name" style="color:$itemColor!important">$safeName</div>
            </div>
        </div>

        <div class="tcg-pay-lines">
            <div><span>Giá thanh toán</span><strong class="$moneyClass">$price $safeUnit</strong></div>
            <div><span>Số dư hiện có</span><strong class="$moneyClass">$balance $safeUnit</strong></div>
            <div><span>Số dư sau mua</span><strong class="$statusClass">$afterBalance $safeUnit</strong></div>
        </div>

        <div class="tcg-pay-status $statusClass">$statusText</div>

        <div class="tcg-pay-actions">
            <a class="tcg-pay-cancel" href="?cmd=$cancelCmd">Hủy</a>
            $confirmHtml
        </div>
    </div>
</div>
HTML;
}

function tcg_shop_pager_html($encode, $sid, $mode, $page, $pageSize, $total)
{
    $totalPages = max(1, ceil($total / $pageSize));
    if ($totalPages <= 1) {
        return '';
    }

    $page = min(max(1, intval($page)), $totalPages);
    $prevPage = max(1, $page - 1);
    $nextPage = min($totalPages, $page + 1);
    $prevCmd = tcg_shop_page_cmd($encode, $sid, $mode, $prevPage);
    $nextCmd = tcg_shop_page_cmd($encode, $sid, $mode, $nextPage);
    $disabledPrev = $page <= 1 ? ' disabled' : '';
    $disabledNext = $page >= $totalPages ? ' disabled' : '';

    $pagesHtml = '';
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i != 1 && $i != $totalPages && abs($i - $page) > 1) {
            if ($i == 2 || $i == $totalPages - 1) {
                $pagesHtml .= '<span class="tcg-shop-page-dot">...</span>';
            }
            continue;
        }

        $cmd = tcg_shop_page_cmd($encode, $sid, $mode, $i);
        $active = $i == $page ? ' active' : '';
        $pagesHtml .= '<a class="tcg-shop-page-num'.$active.'" href="?cmd='.$cmd.'">'.$i.'</a>';
    }

    return '<div class="tcg-shop-pager">
        <a class="tcg-shop-page-btn'.$disabledPrev.'" href="?cmd='.$prevCmd.'">Trước</a>
        <div class="tcg-shop-page-nums">'.$pagesHtml.'</div>
        <a class="tcg-shop-page-btn'.$disabledNext.'" href="?cmd='.$nextCmd.'">Sau</a>
    </div>';
}

// ---- ĐAN DƯỢC (Linh thạch / Ma thạch) ----
if (isset($canshu) && $canshu == 'gogoumai') {
    $currentTab = 'dan';

    if (isset($ydcount) && isset($ydid)) {
        $yaodan = \player\getyaodanonce($ydid, $dblj);
        $ydjg = $yaodan->ydjg;
        $ret = \player\changeyxb(2, $ydjg * $ydcount, $sid, $dblj);
        if ($ret) {
            \player\addyaodan($sid, $ydid, $ydcount, $dblj);
            \player\player_cache_clear($sid);
            $player = player\getplayer($sid, $dblj);
            $noticeHtml .= $tskcg;
            \player\nhiemvu_coduyen_event($sid, 'shop_buy', $dblj); // Cơ Duyên: lần đầu mua ở tiệm
        } else {
            $noticeHtml .= $tsksb;
        }
    }

    if (isset($pay) && isset($ydid) && !isset($ydcount)) {
        $yaodan = \player\getyaodanonce($ydid, $dblj);
        $payPrice = $yaodan->ydjg;
        $cancelCmd = tcg_shop_page_cmd($encode, $sid, 'linh', $page);
        $confirmCmd = $encode->encode("cmd=shangdian&canshu=gogoumai&ydcount=1&ydid=$ydid&p=$page&sid=$sid") . $kwParam;
        $paymentModalHtml = tcg_shop_payment_modal($yaodan->ydname, $yaodan->ydys, $payPrice, 'Linh thạch', $player->uyxb, $confirmCmd, $cancelCmd, false);
    }

    $yaodanList = \player\getyaodan($dblj);
    $yaodanList = tcg_shop_filter_kw($yaodanList, 'ydname');
    $itemHtml = tcg_shop_render_yaodan($yaodanList, $encode, $sid, 'linh', $page, $pageSize);
    $pagerHtml = tcg_shop_pager_html($encode, $sid, 'linh', $page, $pageSize, count($yaodanList));
}

if (isset($canshu1) && $canshu1 == 'gogoumai1') {
    $currentTab = 'dan';

    if (isset($ydcount) && isset($ydid)) {
        $yaodan = \player\getyaodanonce($ydid, $dblj);
        $ydjg = $yaodan->ydjgm;
        $ret = \player\changeczb(2, $ydjg * $ydcount, $sid, $dblj);
        if ($ret) {
            \player\addyaodan($sid, $ydid, $ydcount, $dblj);
            \player\player_cache_clear($sid);
            $player = player\getplayer($sid, $dblj);
            $noticeHtml .= $tskcg;
            \player\nhiemvu_coduyen_event($sid, 'shop_buy', $dblj); // Cơ Duyên: lần đầu mua ở tiệm
        } else {
            $noticeHtml .= $tsksb;
        }
    }

    if (isset($pay) && isset($ydid) && !isset($ydcount)) {
        $yaodan = \player\getyaodanonce($ydid, $dblj);
        $payPrice = $yaodan->ydjgm;
        $cancelCmd = tcg_shop_page_cmd($encode, $sid, 'ma', $page);
        $confirmCmd = $encode->encode("cmd=shangdian&canshu1=gogoumai1&ydcount=1&ydid=$ydid&p=$page&sid=$sid") . $kwParam;
        $paymentModalHtml = tcg_shop_payment_modal($yaodan->ydname, $yaodan->ydys, $payPrice, 'Ma thạch', $player->uczb, $confirmCmd, $cancelCmd, true);
    }

    $yaodanList = \player\getyaodan($dblj);
    $yaodanList = tcg_shop_filter_kw($yaodanList, 'ydname');
    $itemHtml = tcg_shop_render_yaodan($yaodanList, $encode, $sid, 'ma', $page, $pageSize);
    $pagerHtml = tcg_shop_pager_html($encode, $sid, 'ma', $page, $pageSize, count($yaodanList));
}

function tcg_shop_render_yaodan($yaodanList, $encode, $sid, $mode, $page, $pageSize)
{
    global $kwParam;
    $itemHtml = '';
    $total = count($yaodanList);
    $totalPages = max(1, ceil($total / $pageSize));
    $page = min(max(1, intval($page)), $totalPages);
    $start = ($page - 1) * $pageSize;
    $end = min($start + $pageSize, $total);

    for ($i = $start; $i < $end; $i++) {
        $oneyaodan = $yaodanList[$i];
        $index = $i + 1;
        $ydname = $oneyaodan['ydname'];
        $ydid = $oneyaodan['ydid'];
        $ydys = $oneyaodan['ydys'];
        $ydcmd = $encode->encode("cmd=ydinfo&ydid=$ydid&sid=$sid") . $kwParam;

        if ($mode == 'ma') {
            $ydjg = $oneyaodan['ydjgm'];
            $payCmd = $encode->encode("cmd=shangdian&canshu1=gogoumai1&pay=1&ydid=$ydid&p=$page&sid=$sid") . $kwParam;
            $itemHtml .= tcg_shop_item_html($index, $ydname, $ydys, $ydjg, 'Ma thạch', $ydcmd, $payCmd, true);
        } else {
            $ydjg = $oneyaodan['ydjg'];
            $payCmd = $encode->encode("cmd=shangdian&canshu=gogoumai&pay=1&ydid=$ydid&p=$page&sid=$sid") . $kwParam;
            $itemHtml .= tcg_shop_item_html($index, $ydname, $ydys, $ydjg, 'Linh thạch', $ydcmd, $payCmd, false);
        }
    }
    return $itemHtml;
}

// ---- ĐẠO CỤ (thường = Linh thạch, cao = Ma thạch) ----
if ((isset($canshu2) && $canshu2 == 'gogoumai2') || (isset($canshu21) && $canshu21 == 'gogoumai2ma')) {
    $currentTab = 'daoju';
    $money = (isset($canshu21) && $canshu21 == 'gogoumai2ma') ? 'ma' : 'linh';
    $isMagic = ($money == 'ma');
    $unit = $isMagic ? 'Ma thạch' : 'Linh thạch';
    $canKey = $isMagic ? 'canshu21=gogoumai2ma' : 'canshu2=gogoumai2';

    if (isset($djid) && isset($buy)) {
        $daoju = \player\getdaoju($djid, $dblj);
        // no_shop: vật phẩm chỉ rơi từ boss (vd Tiên Đạo Tàn Quyển) — không cho mua kể cả gọi thẳng URL
        $djCfg = \player\daoju_config_get(intval($djid));
        if ($daoju && !(isset($daoju->djuse) && $daoju->djuse != '') && empty($djCfg['no_shop'])) {
            $price = max(50, intval($daoju->djyxb));
            if ($isMagic) {
                $ret = \player\changeczb(2, $price, $sid, $dblj);
            } else {
                $ret = \player\changeyxb(2, $price, $sid, $dblj);
            }
            if ($ret) {
                \player\adddj($sid, $djid, 1, $dblj);
                \player\player_cache_clear($sid);
                $player = player\getplayer($sid, $dblj);
                $noticeHtml .= $tskcg;
                \player\nhiemvu_coduyen_event($sid, 'shop_buy', $dblj); // Cơ Duyên: lần đầu mua ở tiệm
            } else {
                $noticeHtml .= $tsksb;
            }
        } else {
            $noticeHtml .= $tsksb;
        }
    }

    if (isset($djid) && isset($pay) && !isset($buy)) {
        $daoju = \player\getdaoju($djid, $dblj);
        if ($daoju) {
            $price = max(50, intval($daoju->djyxb));
            $balance = $isMagic ? $player->uczb : $player->uyxb;
            $cancelCmd = tcg_shop_page_cmd($encode, $sid, 'daoju', $page);
            $confirmCmd = $encode->encode("cmd=shangdian&$canKey&buy=1&djid=$djid&p=$page&sid=$sid") . $kwParam;
            $paymentModalHtml = tcg_shop_payment_modal($daoju->djname, '#cbd5e1', $price, $unit, $balance, $confirmCmd, $cancelCmd, $isMagic);
        }
    }

    $daojuList = \player\daoju_config_all();
    $daojuList = tcg_shop_filter_kw($daojuList, 'djname');
    $filtered = array();
    foreach ($daojuList as $d) {
        if (intval($d['djid']) == 112 || intval($d['djid']) == 113) continue;
        if (!empty($d['no_shop'])) continue;
        $filtered[] = $d;
    }
    $itemHtml = tcg_shop_render_daoju($filtered, $encode, $sid, $page, $pageSize, $isMagic, $canKey);
    $pagerHtml = tcg_shop_pager_html($encode, $sid, 'daoju', $page, $pageSize, count($filtered));
}

function tcg_shop_render_daoju($daojuList, $encode, $sid, $page, $pageSize, $isMagic, $canKey)
{
    global $kwParam;
    $itemHtml = '';
    $unit = $isMagic ? 'Ma thạch' : 'Linh thạch';
    $total = count($daojuList);
    $totalPages = max(1, ceil($total / $pageSize));
    $page = min(max(1, intval($page)), $totalPages);
    $start = ($page - 1) * $pageSize;
    $end = min($start + $pageSize, $total);

    for ($i = $start; $i < $end; $i++) {
        $d = $daojuList[$i];
        $index = $i + 1;
        $djid = $d['djid'];
        $price = max(50, intval($d['djyxb']));
        $djcmd = $encode->encode("cmd=djinfo&djid=$djid&sid=$sid") . $kwParam;
        $payCmd = $encode->encode("cmd=shangdian&$canKey&pay=1&djid=$djid&p=$page&sid=$sid") . $kwParam;
        $itemHtml .= tcg_shop_item_html($index, $d['djname'], '#cbd5e1', $price, $unit, $djcmd, $payCmd, $isMagic);
    }
    return $itemHtml;
}

// ---- TRANG BỊ (thường = Linh thạch, cao = Ma thạch) ----
if ((isset($canshu3) && $canshu3 == 'gogoumai3') || (isset($canshu31) && $canshu31 == 'gogoumai3ma')) {
    $currentTab = 'zhuang';
    $money = (isset($canshu31) && $canshu31 == 'gogoumai3ma') ? 'ma' : 'linh';
    $isMagic = ($money == 'ma');
    $canKey = $isMagic ? 'canshu31=gogoumai3ma' : 'canshu3=gogoumai3';

    if (isset($zbid) && isset($buy)) {
        $zb = \player\getzbkzb($zbid, $dblj);
        if ($zb) {
            $rarity = intval($zb->rarity);
            if ($isMagic) {
                $price = ($rarity - 3) * 50 + 200;
                $ret = \player\changeczb(2, $price, $sid, $dblj);
            } else {
                $price = $rarity * 100;
                $ret = \player\changeyxb(2, $price, $sid, $dblj);
            }
            if ($ret) {
                \player\addzb($sid, $zbid, $dblj);
                \player\player_cache_clear($sid);
                $player = player\getplayer($sid, $dblj);
                $noticeHtml .= $tskcg;
                \player\nhiemvu_coduyen_event($sid, 'shop_buy', $dblj); // Cơ Duyên: lần đầu mua ở tiệm
            } else {
                $noticeHtml .= $tsksb;
            }
        } else {
            $noticeHtml .= $tsksb;
        }
    }

    if (isset($zbid) && isset($pay) && !isset($buy)) {
        $zb = \player\getzbkzb($zbid, $dblj);
        if ($zb) {
            $rarity = intval($zb->rarity);
            if ($isMagic) {
                $price = ($rarity - 3) * 50 + 200;
                $unit = 'Ma thạch';
                $balance = $player->uczb;
            } else {
                $price = $rarity * 100;
                $unit = 'Linh thạch';
                $balance = $player->uyxb;
            }
            $cancelCmd = tcg_shop_page_cmd($encode, $sid, 'zhuang', $page);
            $confirmCmd = $encode->encode("cmd=shangdian&$canKey&buy=1&zbid=$zbid&p=$page&sid=$sid") . $kwParam;
            $color = isset($zb->zbys) && $zb->zbys ? $zb->zbys : '#cbd5e1';
            $paymentModalHtml = tcg_shop_payment_modal($zb->zbname, $color, $price, $unit, $balance, $confirmCmd, $cancelCmd, $isMagic);
        }
    }

    $zbList = \player\zhuangbei_config_all();
    $zbList = tcg_shop_filter_kw($zbList, 'zbname');
    $filtered = array();
    foreach ($zbList as $zb) {
        $filtered[] = $zb;
    }
    $itemHtml = tcg_shop_render_zhuang($filtered, $encode, $sid, $page, $pageSize, $isMagic, $canKey);
    $pagerHtml = tcg_shop_pager_html($encode, $sid, 'zhuang', $page, $pageSize, count($filtered));
}

function tcg_shop_render_zhuang($zbList, $encode, $sid, $page, $pageSize, $isMagic, $canKey)
{
    global $kwParam, $player, $dblj;
    $itemHtml = '';
    $unit = $isMagic ? 'Ma thạch' : 'Linh thạch';
    $total = count($zbList);
    $totalPages = max(1, ceil($total / $pageSize));
    $page = min(max(1, intval($page)), $totalPages);
    $start = ($page - 1) * $pageSize;
    $end = min($start + $pageSize, $total);

    for ($i = $start; $i < $end; $i++) {
        $zb = $zbList[$i];
        $index = $i + 1;
        $zbid = $zb['zbid'];
        $rarity = intval($zb['rarity']);
        if ($isMagic) {
            $price = ($rarity - 3) * 50 + 200;
        } else {
            $price = $rarity * 100;
        }
        $zcmd = $encode->encode("cmd=zbinfo&zbid=$zbid&sid=$sid") . $kwParam;
        $payCmd = $encode->encode("cmd=shangdian&$canKey&pay=1&zbid=$zbid&p=$page&sid=$sid") . $kwParam;
        $color = isset($zb['zbys']) && $zb['zbys'] ? $zb['zbys'] : '#cbd5e1';
        $zbImg = \player\zhuangbei_image_path((object) array('zbid' => intval($zbid)), '');
        $zbTip = \player\itemtip_attr_for($zb, $player, $dblj);
        $itemHtml .= tcg_shop_item_html($index, $zb['zbname'], $color, $price, $unit, $zcmd, $payCmd, $isMagic, $zbImg, $zbTip);
    }
    return $itemHtml;
}

$shopEntered = (isset($canshu) || isset($canshu1) || isset($canshu2) || isset($canshu21) || isset($canshu3) || isset($canshu31));
if ($itemHtml == '' && !$shopEntered) {
    // Mặc định (vào shop lần đầu): hiển thị Đan Dược (Linh thạch)
    $currentTab = 'dan';
    $canshu = 'gogoumai';
    $yaodanList = \player\getyaodan($dblj);
    $yaodanList = tcg_shop_filter_kw($yaodanList, 'ydname');
    $itemHtml = tcg_shop_render_yaodan($yaodanList, $encode, $sid, 'linh', 1, $pageSize);
    $pagerHtml = tcg_shop_pager_html($encode, $sid, 'linh', 1, $pageSize, count($yaodanList));
} elseif ($itemHtml == '') {
    $itemHtml = '<div class="tcg-shop-empty">Không tìm thấy vật phẩm nào khớp với từ khóa "' . htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') . '".</div>';
}

$danActive   = ($currentTab == 'dan') ? ' active' : '';
$daojuActive = ($currentTab == 'daoju') ? ' active' : '';
$zhuangActive = ($currentTab == 'zhuang') ? ' active' : '';

// Sub-tab: Linh thạch / Ma thạch cho mỗi mục
$danSub = '';
$daojuSub = '';
$zhuangSub = '';

if ($currentTab == 'dan') {
    $danLinhActive = (isset($canshu) && $canshu == 'gogoumai') ? ' active' : '';
    $danMaActive   = (isset($canshu1) && $canshu1 == 'gogoumai1') ? ' active' : '';
    $danSub = <<<SUB
    <div class="tcg-shop-subtabs">
        <a class="tcg-shop-subtab gold$danLinhActive" href="?cmd=$shangdian">Linh Thạch</a>
        <a class="tcg-shop-subtab purple$danMaActive" href="?cmd=$shangdian1">Ma Thạch</a>
    </div>
SUB;
}

if ($currentTab == 'daoju') {
    $daojuLinhActive = (isset($canshu2) && $canshu2 == 'gogoumai2') ? ' active' : '';
    $daojuMaActive   = (isset($canshu21) && $canshu21 == 'gogoumai2ma') ? ' active' : '';
    $daojuSub = <<<SUB
    <div class="tcg-shop-subtabs">
        <a class="tcg-shop-subtab gold$daojuLinhActive" href="?cmd=$shopDaoju">Linh Thạch</a>
        <a class="tcg-shop-subtab purple$daojuMaActive" href="?cmd=$shopDaojuMa">Ma Thạch</a>
    </div>
SUB;
}

if ($currentTab == 'zhuang') {
    $zhuangLinhActive = (isset($canshu3) && $canshu3 == 'gogoumai3') ? ' active' : '';
    $zhuangMaActive   = (isset($canshu31) && $canshu31 == 'gogoumai3ma') ? ' active' : '';
    $zhuangSub = <<<SUB
    <div class="tcg-shop-subtabs">
        <a class="tcg-shop-subtab gold$zhuangLinhActive" href="?cmd=$shopZhuang">Linh Thạch</a>
        <a class="tcg-shop-subtab purple$zhuangMaActive" href="?cmd=$shopZhuangMa">Ma Thạch</a>
    </div>
SUB;
}

// Cmd cơ sở cho thanh tìm kiếm (giữ nguyên mục / sub-tab đang chọn)
$searchBaseCmd = $shangdian;
if ($currentTab == 'daoju') {
    $searchBaseCmd = (isset($canshu21) && $canshu21 == 'gogoumai2ma') ? $shopDaojuMa : $shopDaoju;
} elseif ($currentTab == 'zhuang') {
    $searchBaseCmd = (isset($canshu31) && $canshu31 == 'gogoumai3ma') ? $shopZhuangMa : $shopZhuang;
} elseif ($currentTab == 'dan') {
    $searchBaseCmd = (isset($canshu1) && $canshu1 == 'gogoumai1') ? $shangdian1 : $shangdian;
}
$kwSafe = htmlspecialchars($kw, ENT_QUOTES, 'UTF-8');

// Action tuyệt đối: giữ nguyên đường dẫn script hiện tại (game.php hoặc /), chỉ bỏ kw cũ
$searchAction = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$searchAction = preg_replace('/[?&]kw=[^&]*/', '', $searchAction);

$kwClear = $kw != '' ? '<a class="tcg-shop-search-clear" href="'.htmlspecialchars($searchAction, ENT_QUOTES, 'UTF-8').'">Xóa</a>' : '';

$gnhtml = <<<HTML
<div class="tcg-shop-page">
    <div class="tcg-shop-hero">
        <img src="./images/bg/shop.png" alt="Shop">
        <div class="tcg-shop-title">
            Cửa Hàng
            <div class="tcg-shop-subtitle">Đan dược · Đạo cụ · Trang bị — vật phẩm thường trả Linh thạch, phẩm cao trả Ma thạch</div>
        </div>
    </div>

    <div class="tcg-shop-tabs">
        <a class="tcg-shop-tab$danActive" href="?cmd=$shangdian">Đan Dược</a>
        <a class="tcg-shop-tab$daojuActive" href="?cmd=$shopDaoju">Đạo Cụ</a>
        <a class="tcg-shop-tab$zhuangActive" href="?cmd=$shopZhuang">Trang Bị</a>
    </div>

    $danSub$daojuSub$zhuangSub

    <form class="tcg-shop-search" id="tcgShopSearchForm" method="get" action="$searchAction" onsubmit="return tcgShopSearch();">
        <span class="tcg-shop-search-icon">🔍</span>
        <input type="text" name="kw" id="tcgShopKw" class="tcg-shop-search-input" placeholder="Tìm kiếm vật phẩm..." value="$kwSafe">
        <button type="submit" class="tcg-shop-search-btn">Tìm</button>
        $kwClear
    </form>

    $noticeHtml

    <div class="tcg-shop-list">
        $itemHtml
    </div>

    $pagerHtml

    $paymentModalHtml

    <div class="tcg-shop-bottom">
        <a class="tcg-shop-back" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a class="tcg-shop-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
(function(){
    var overlay = document.querySelector('.tcg-pay-overlay');
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
})();
$('#success').click(function(){
    popup({type:'success',msg:"Mua thành công",delay:1200,callBack:function(){ console.log('success'); }});
});
$('#error').click(function(){
    popup({type:'error',msg:"Mua thất bại",delay:2000,bg:true,clickDomCancel:true});
});

function tcgShopSearch(){
    var kw = document.getElementById('tcgShopKw').value;
    var url = new URL(window.location.href);
    kw = (kw || '').trim();
    if (kw !== '') {
        url.searchParams.set('kw', kw);
    } else {
        url.searchParams.delete('kw');
    }
    window.location.href = url.toString();
    return false;
}
</script>
HTML;

echo $gnhtml;
?>
