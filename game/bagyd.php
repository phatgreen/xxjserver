<?php
/*
Nơi này ta bố trí ba lô, chủ yếu là cửa hàng công năng cất giữ
*/

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$getbagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");
$getbagdjcmd = $encode->encode("cmd=getbagdj&sid=$sid");
$getbagypcmd = $encode->encode("cmd=getbagyp&sid=$sid");
$getbagjncmd = $encode->encode("cmd=getbagjn&sid=$sid");
$getbagydcmd = $encode->encode("cmd=getbagyd&sid=$sid");

$shangdian = $encode->encode("cmd=shangdian&canshu=gogoumai&sid=$sid");
$shangdian1 = $encode->encode("cmd=shangdian&canshu1=gogoumai1&sid=$sid");
$beibaocmd = $encode->encode("cmd=getbagyd&sid=$sid");
$luondenCmd = $encode->encode("cmd=luonden&sid=$sid");
$chetaoCmd = $encode->encode("cmd=chetao&sid=$sid");

$yaodan = player\getplayeryaodanall($sid, $dblj);

$allyd = '';
$suoyin = 0;
$totalYd = 0;
$totalStack = 0;

if ($yaodan) {
    foreach ($yaodan as $yd) {
        $ydsum = isset($yd['ydsum']) ? intval($yd['ydsum']) : 0;
        if ($ydsum > 0) {
            $suoyin += 1;
            $totalYd += 1;
            $totalStack += $ydsum;

            $ydid = isset($yd['ydid']) ? intval($yd['ydid']) : 0;
            $ydnameRaw = isset($yd['ydname']) ? $yd['ydname'] : '';
            $ydname = htmlspecialchars($ydnameRaw, ENT_QUOTES, 'UTF-8');
            $ydcmd = $encode->encode("cmd=ydinfo&ydid=$ydid&sid=$sid");
            $useyd = $encode->encode("cmd=ydinfo&canshu=useyd&ydid=$ydid&sid=$sid");

            $ydColor = '#facc15';
            $yaodan1 = \player\getyaodanonce($ydid, $dblj);
            if ($yaodan1 && isset($yaodan1->ydys) && $yaodan1->ydys != '') {
                $ydColor = htmlspecialchars($yaodan1->ydys, ENT_QUOTES, 'UTF-8');
            }

            list($ydEmo) = \player\item_emoji($ydnameRaw, '⚗');
            $allyd .= <<<HTML
<div class="bag-item-card yaodan" style="--ra:$ydColor">
    <div class="bag-item-index">$suoyin</div>
    <div class="bag-item-icon emo"><span class="bag-emo">$ydEmo</span><b class="bag-qty">x$ydsum</b></div>
    <div class="bag-item-main">
        <a class="bag-item-name" style="color:$ydColor" href="?cmd=$ydcmd">$ydname</a>
        <div class="bag-item-meta">Số lượng <b>x$ydsum</b> · Đan dược tu luyện</div>
    </div>
    <div class="bag-item-actions">
        <a class="bag-btn detail" href="?cmd=$ydcmd">Xem</a>
        <a class="bag-btn use-pill" href="?cmd=$useyd">Sử dụng</a>
    </div>
</div>
HTML;
        }
    }
}

if ($allyd == '') {
    $allyd = <<<HTML
<div class="bag-empty">
    <div class="bag-empty-icon">⚗</div>
    <div>Hiệu thuốc hiện chưa có đan dược nào.</div>
</div>
HTML;
}

$bagydhtml = <<<HTML
<div class="bag-page bag-yd-page bag-compact">
    <div class="bag-hero yaodan">
        <div>
            <div class="bag-label">Hiệu thuốc</div>
            <h1>Đan Dược</h1>
            <p>Quản lý đan dược, mua vật phẩm bằng Linh Thạch hoặc Ma Thạch và sử dụng nhanh.</p>
        </div>
        <div class="bag-count-box yaodan">
            <span>Tổng loại</span>
            <strong>$totalYd</strong>
            <em>$totalStack viên đan</em>
        </div>
    </div>

    <div class="bag-shop-tabs">
        <a class="bag-shop-tab linh" href="?cmd=$shangdian">Linh Thạch</a>
        <a class="bag-shop-tab ma" href="?cmd=$shangdian1">Ma Thạch</a>
        <span class="bag-shop-tab active">Hiệu Thuốc</span>
        <a class="bag-shop-tab" href="?cmd=$luondenCmd">🔥 Luyện Đan</a>
    </div>

    <div class="bag-tabs">
        <a class="bag-tab" href="?cmd=$getbagzbcmd">Trang bị</a>
        <a class="bag-tab" href="?cmd=$getbagdjcmd">Đạo cụ</a>
        <a class="bag-tab" href="?cmd=$getbagypcmd">Dược phẩm</a>
        <a class="bag-tab" href="?cmd=$getbagjncmd">Kỹ năng</a>
        <span class="bag-tab active">Đan dược</span>
        <a class="bag-tab" href="?cmd=$chetaoCmd">⚒ Chế tạo</a>
    </div>


    <div class="bag-list">
        $allyd
    </div>

</div>
HTML;

echo $bagydhtml;
?>
