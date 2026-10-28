<?php
/** @var string $sid */
/** @var PDO $dblj */
/** @var object $player */
/** @var object $encode */

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$getbagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");
$getbagdjcmd = $encode->encode("cmd=getbagdj&sid=$sid");
$getbagypcmd = $encode->encode("cmd=getbagyp&sid=$sid");
$getbagydcmd = $encode->encode("cmd=getbagyd&sid=$sid");

$jineng = \player\getplayerjineng_all($sid, $dblj);

$alljn = '';
$suoyin = 0;
$totalJn = 0;
$totalCount = 0;

if ($jineng) {
    foreach ($jineng as $jn) {
        $jnsum = intval($jn['jncount']);
        if ($jnsum > 0) {
            $suoyin += 1;
            $totalJn += 1;
            $totalCount += $jnsum;

            $jnid = intval($jn['jnid']);
            $jnname = htmlspecialchars($jn['jnname'], ENT_QUOTES, 'UTF-8');
            $jncmd = $encode->encode("cmd=jninfo&jnid=$jnid&sid=$sid");

            $alljn .= <<<HTML
<div class="bag-item-card jineng">
    <div class="bag-item-index">$suoyin</div>
    <div class="bag-item-icon jn">✦</div>
    <div class="bag-item-main">
        <a class="bag-item-name" href="?cmd=$jncmd">$jnname</a>
        <div class="bag-item-meta">Số lượng: <b>x$jnsum</b></div>
    </div>
    <div class="bag-item-actions">
        <a class="bag-btn view" href="?cmd=$jncmd">Xem</a>
    </div>
</div>
HTML;
        }
    }
}

if ($alljn == '') {
    $alljn = <<<HTML
<div class="bag-empty">
    <div class="bag-empty-icon">✦</div>
    <div>Chưa có sách kỹ năng nào trong túi.</div>
</div>
HTML;
}

$bagjnhtml = <<<HTML
<div class="bag-page bag-jineng-page bag-compact">
    <div class="bag-hero jineng">
        <div>
            <div class="bag-label">Tàng Kỹ Các</div>
            <h1>Túi Kỹ Năng</h1>
            <p>Quản lý sách kỹ năng, xem thông tin và chuẩn bị lĩnh ngộ chiêu thức mới.</p>
        </div>
        <div class="bag-count-box jineng">
            <span>Tổng kỹ năng</span>
            <strong>$totalJn</strong>
            <small>Tổng số lượng: $totalCount</small>
        </div>
    </div>

    <div class="bag-tabs">
        <a class="bag-tab" href="?cmd=$getbagzbcmd">Trang bị</a>
        <a class="bag-tab" href="?cmd=$getbagdjcmd">Đạo cụ</a>
        <a class="bag-tab" href="?cmd=$getbagypcmd">Dược phẩm</a>
        <span class="bag-tab active">Kỹ năng</span>
        <a class="bag-tab" href="?cmd=$getbagydcmd">Đan dược</a>
    </div>


    <div class="bag-list">
        $alljn
    </div>

</div>
HTML;

echo $bagjnhtml;
?>
