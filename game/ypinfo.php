<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021 /6/26
 * Time: 15:57
 */
$yphp = '';
$ypgj = '';
$ypfy = '';
$ypbj = '';
$ypxx = '';
$ypstamina = '';
$ypStatsHtml = '';
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$yaopin = \player\getyaopinonce($ypid,$dblj);
$playeryp = \player\getplayeryaopin($ypid,$sid,$dblj);
$setyp = '';
$tishi = '';
$tishiClass = '';

if (isset($canshu)){
    switch ($canshu){
        case 'setyp1':
            \player\changeplayersx('yp1',$ypid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 1 thành công";
            break;
        case 'setyp2':
            \player\changeplayersx('yp2',$ypid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 2 thành công";
            break;
        case 'setyp3':
            \player\changeplayersx('yp3',$ypid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 3 thành công";
            break;
        case 'useyp':
            $userypret = \player\useyaopin($ypid,1,$sid,$dblj);
            if ($userypret){
                $tishi = "Sử dụng thành công";
            }else{
                $govandutieny = $encode->encode("cmd=npc&nid=17&sid=$sid");
                $tishi = "Sử dụng thất bại<br/>Thiếu hiệp bị trọng thương cần đến <a class=\"ypinfo-link\" href=\"?cmd=$govandutieny\">Vân Du Tiên Y [Trị Liệu]</a>";
                $tishiClass = ' fail';
            }
            break;
    }
}

if ($playeryp){
    $setyp1 = $encode->encode("cmd=ypinfo&canshu=setyp1&ypid=$ypid&sid=$sid");
    $setyp2 = $encode->encode("cmd=ypinfo&canshu=setyp2&ypid=$ypid&sid=$sid");
    $setyp3 = $encode->encode("cmd=ypinfo&canshu=setyp3&ypid=$ypid&sid=$sid");
    $useyp = $encode->encode("cmd=ypinfo&canshu=useyp&ypid=$ypid&sid=$sid");
    $setyp = <<<HTML
    <div class="ypinfo-slot-box">
        <div class="ypinfo-slot-title">💊 Gắn dược phẩm vào ô dùng nhanh</div>
        <div class="ypinfo-slots">
            <a class="ypinfo-slot" href="?cmd=$setyp1">Vị trí 1</a>
            <a class="ypinfo-slot" href="?cmd=$setyp2">Vị trí 2</a>
            <a class="ypinfo-slot" href="?cmd=$setyp3">Vị trí 3</a>
        </div>
    </div>
    <div class="ypinfo-actions">
        <a class="ypinfo-use-btn" href="?cmd=$useyp">✨ Sử dụng dược phẩm</a>
    </div>
HTML;
}

if($yaopin->yphp!=0){
    $yphp = '<div class="ypinfo-stat"><span>❤️ Khôi phục HP</span><strong>'.$yaopin->yphp.'</strong></div>';
}
if ($yaopin->ypgj!=0){
    $ypgj = '<div class="ypinfo-stat"><span>⚔️ Công kích</span><strong>+'.$yaopin->ypgj.'</strong></div>';
}
if ($yaopin->ypfy!=0){
    $ypfy = '<div class="ypinfo-stat"><span>🛡️ Phòng ngự</span><strong>+'.$yaopin->ypfy.'</strong></div>';
}
if ($yaopin->ypbj!=0){
    $ypbj = '<div class="ypinfo-stat"><span>💥 Bạo kích</span><strong>+'.$yaopin->ypbj.'</strong></div>';
}
if ($yaopin->ypxx!=0){
    $ypxx = '<div class="ypinfo-stat"><span>🩸 Hút máu</span><strong>+'.$yaopin->ypxx.'</strong></div>';
}
if (isset($yaopin->ypstamina) && $yaopin->ypstamina!=0){
    $ypstamina = '<div class="ypinfo-stat"><span>⚡ Hồi Thể lực</span><strong>+'.$yaopin->ypstamina.'</strong></div>';
}

$ypStatsHtml = $yphp.$ypgj.$ypfy.$ypbj.$ypxx.$ypstamina;
if ($ypStatsHtml != ''){
    $ypStatsHtml = '<div class="ypinfo-stats">'.$ypStatsHtml.'</div>';
}else{
    $ypStatsHtml = '<div class="ypinfo-empty">Dược phẩm này chưa có thuộc tính hiển thị.</div>';
}

$tishiHtml = '';
if ($tishi != ''){
    $tishiHtml = '<div class="ypinfo-alert'.$tishiClass.'">'.$tishi.'</div>';
}

// Khung biểu tượng dược phẩm theo tên (giống chi tiết trang bị)
list($ypEmo, $ypEmoColor) = \player\item_emoji($yaopin->ypname, '💊');
$ypinfo = <<<HTML
<div class="ypinfo-page">
    <div class="ypinfo-card">
        <div class="ypinfo-hero ypinfo-hero-art">
            <div class="zb-art" style="--ra:$ypEmoColor"><span class="bag-emo">$ypEmo</span><span class="zb-art-ra">Dược phẩm</span></div>
        </div>

        <div class="ypinfo-name">{$yaopin->ypname}</div>

        $tishiHtml
        $ypStatsHtml
        $setyp

        <div class="ypinfo-bottom">
            <a class="ypinfo-nav" href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
            <a class="ypinfo-nav home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
        </div>
    </div>
</div>
HTML;
echo $ypinfo;
