<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/6/17
 * Time: 10:18
 */

$player = player\getplayer($sid,$dblj);
$map = '';

$cxallmap = \player\getqy_all($dblj);
for ($i=0;$i<count($cxallmap);$i++){

    $qyame = $cxallmap[$i]['qyname'];
    $mid = $cxallmap[$i]['mid'];
    if ($mid>0){
        $cxmid = \player\getmid($mid,$dblj);
        $mname = $cxmid->mname;
        $gomid = $encode->encode("cmd=gomid&newmid=$mid&sid=$sid");
        $regionName = htmlspecialchars(strip_tags($qyame), ENT_QUOTES, 'UTF-8');
        $mapName = htmlspecialchars(strip_tags($mname), ENT_QUOTES, 'UTF-8');
        $map .=<<<HTML
        <a class="allmap-card" href="?cmd=$gomid">
            <span class="allmap-region">[$regionName]</span>
            <span class="allmap-name">$mapName</span>
        </a>
HTML;
    }
}

$mapView = trim($map) != '' ? $map : '<div class="allmap-empty">Chưa có khu vực nào được mở.</div>';

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$allmap = <<<HTML
<div class="allmap-page">
    <div class="allmap-hero">
        <div class="allmap-kicker">Thông Báo Sự Kiện</div>
        <div class="allmap-title">Thế Giới</div>
        <div class="allmap-subtitle">Chọn địa vực để dịch chuyển và tiếp tục hành trình.</div>
        <img src="./images/tu-chan-banner-v2.png" alt="Thế giới tu chân">
    </div>

    <div class="allmap-divider"><span>◇</span></div>

    <div class="allmap-grid">
        $mapView
    </div>

    <div class="allmap-bottom">
        <a class="allmap-btn ghost" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a class="allmap-btn primary" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;
echo $allmap;
?>
