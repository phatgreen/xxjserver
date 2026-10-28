<?php
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$getbagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");
$getbagdjcmd = $encode->encode("cmd=getbagdj&sid=$sid");
$getbagjncmd = $encode->encode("cmd=getbagjn&sid=$sid");
$getbagydcmd = $encode->encode("cmd=getbagyd&sid=$sid");

$yaopin = player\getplayeryaopinall($sid, $dblj);
$allyp = '';
$suoyin = 0;
$totalYp = 0;
$totalStack = 0;

if ($yaopin) {
    foreach ($yaopin as $yp) {
        $ypsum = isset($yp['ypsum']) ? intval($yp['ypsum']) : 0;
        if ($ypsum > 0) {
            $suoyin += 1;
            $totalYp += 1;
            $totalStack += $ypsum;

            $ypid = isset($yp['ypid']) ? intval($yp['ypid']) : 0;
            $ypnameRaw = isset($yp['ypname']) ? $yp['ypname'] : '';
            $ypname = htmlspecialchars($ypnameRaw, ENT_QUOTES, 'UTF-8');
            $ypcmd = $encode->encode("cmd=ypinfo&ypid=$ypid&sid=$sid");
            $useypcmd = $encode->encode("cmd=ypinfo&canshu=useyp&ypid=$ypid&sid=$sid");

            $allyp .= <<<HTML
<div class="bag-item-card yaopin">
    <div class="bag-item-index">$suoyin</div>
    <div class="bag-item-icon yp">💊</div>
    <div class="bag-item-main">
        <a class="bag-item-name" href="?cmd=$ypcmd">$ypname</a>
        <div class="bag-item-meta">Số lượng <b>x$ypsum</b> · Dược phẩm hồi phục</div>
    </div>
    <div class="bag-item-actions">
        <a class="bag-btn use" href="?cmd=$ypcmd">Xem</a>
        <a class="bag-btn use-now" href="?cmd=$useypcmd">Sử dụng</a>
    </div>
</div>
HTML;
        }
    }
}

if ($allyp == '') {
    $allyp = <<<HTML
<div class="bag-empty">
    <div class="bag-empty-icon">🍶</div>
    <div>Chưa có dược phẩm nào trong túi.</div>
</div>
HTML;
}

$bagyphtml = <<<HTML
<div class="bag-page bag-yp-page bag-compact">
    <div class="bag-hero yaopin">
        <div>
            <div class="bag-label">Túi đồ</div>
            <h1>Dược Phẩm</h1>
            <p>Quản lý linh đan, tiên dược và vật phẩm hồi phục đang sở hữu.</p>
        </div>
        <div class="bag-count-box yaopin">
            <span>Tổng loại</span>
            <strong>$totalYp</strong>
            <em>$totalStack vật phẩm</em>
        </div>
    </div>

    <div class="bag-tabs">
        <a class="bag-tab" href="?cmd=$getbagzbcmd">Trang bị</a>
        <a class="bag-tab" href="?cmd=$getbagdjcmd">Đạo cụ</a>
        <span class="bag-tab active">Dược phẩm</span>
        <a class="bag-tab" href="?cmd=$getbagjncmd">Kỹ năng</a>
        <a class="bag-tab" href="?cmd=$getbagydcmd">Đan dược</a>
    </div>


    <div class="bag-list">
        $allyp
    </div>

</div>
HTML;

echo $bagyphtml;
?>
