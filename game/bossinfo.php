<?php
$boss = \player\getboss($bossid, $dblj);
$pvb = $encode->encode("cmd=pvb&bossid=$bossid&sid=$sid");
$backcmd = $encode->encode("cmd=zhuangtai&sid=$sid");

function tcg_bossinfo_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tcg_bossinfo_percent($hp, $maxhp)
{
    $hp = max(0, intval($hp));
    $maxhp = max(1, intval($maxhp));
    $percent = intval(round(($hp / $maxhp) * 100));
    if ($percent < 0) $percent = 0;
    if ($percent > 100) $percent = 100;
    return $percent;
}

if (!$boss || intval($boss->bosshp) <= 0) {
    $html = <<<HTML
<div class="tcg-boss-page">
    <div class="tcg-boss-dead">
        <div class="tcg-boss-dead-icon">☠</div>
        <div class="tcg-boss-dead-title">Quái vật đã bị hạ gục!</div>
        <div class="tcg-boss-dead-text">Mời thiếu hiệp luyện tập thêm tốc độ ra tay, lần sau quay lại sớm hơn.</div>
        <div class="tcg-boss-actions">
            <a class="tcg-boss-btn secondary" href="?cmd=$backcmd">Đại Sảnh</a>
            <a class="tcg-boss-btn primary" onclick="javascript:history.back(-1);">Quay lại</a>
        </div>
    </div>
</div>
HTML;
    echo $html;
    return;
}

$bossName = tcg_bossinfo_e($boss->bossname);
$bossInfo = nl2br(tcg_bossinfo_e($boss->bossinfo));
$bossGj = intval($boss->bossgj);
$bossFy = intval($boss->bossfy);
$bossHp = isset($boss->bosshp) ? intval($boss->bosshp) : 0;
$bossMaxHp = isset($boss->bossmaxhp) ? intval($boss->bossmaxhp) : $bossHp;
$hpPercent = tcg_bossinfo_percent($bossHp, $bossMaxHp);

$dropItems = array();

if ($boss->bosszb != '') {
    $zbarr = explode(',', $boss->bosszb);
    foreach ($zbarr as $newstr) {
        $newstr = trim($newstr);
        if ($newstr === '') continue;

        $zbkzb = \player\getzbkzb($newstr, $dblj);
        if (!$zbkzb) continue;

        $zbcmd = $encode->encode("cmd=zbinfo_sys&zbid=$zbkzb->zbid&sid=$sid");
        $color = tcg_bossinfo_e($zbkzb->zbys);
        $name = tcg_bossinfo_e($zbkzb->zbname);
        $dImg = \player\zhuangbei_image_path((object) array('zbid' => $zbkzb->zbid), \player\zhuangbei_slot_icon(isset($zbkzb->zbtool) ? intval($zbkzb->zbtool) : 0));
        $dropItems[] = "<a class=\"tcg-boss-drop equip\" href=\"?cmd=$zbcmd\" style=\"--drop-color:$color;color:$color;--ra:$color\"><i class=\"item-art\"><img src=\"$dImg\" alt=\"\"></i>$name</a>";
    }
}

if ($boss->bossdj != '') {
    $djarr = explode(',', $boss->bossdj);
    foreach ($djarr as $newstr) {
        $newstr = trim($newstr);
        if ($newstr === '') continue;

        $dj = \player\getdaoju($newstr, $dblj);
        if (!$dj) continue;

        $djinfo = $encode->encode("cmd=djinfo&djid=$dj->djid&sid=$sid");
        $name = tcg_bossinfo_e($dj->djname);
        list($dEmo, $dColor) = \player\item_emoji($dj->djname, '📦');
        $dropItems[] = "<a class=\"tcg-boss-drop daoju\" href=\"?cmd=$djinfo\" style=\"--ra:$dColor\"><i class=\"item-art\"><span class=\"bag-emo\">$dEmo</span></i>$name</a>";
    }
}

if ($boss->bossyp != '') {
    $yparr = explode(',', $boss->bossyp);
    foreach ($yparr as $newstr) {
        $newstr = trim($newstr);
        if ($newstr === '') continue;

        $yp = \player\getyaopinonce($newstr, $dblj);
        if (!$yp) continue;

        $ypinfo = $encode->encode("cmd=ypinfo&ypid=$yp->ypid&sid=$sid");
        $name = tcg_bossinfo_e($yp->ypname);
        list($dEmo, $dColor) = \player\item_emoji($yp->ypname, '💊');
        $dropItems[] = "<a class=\"tcg-boss-drop yaopin\" href=\"?cmd=$ypinfo\" style=\"--ra:$dColor\"><i class=\"item-art\"><span class=\"bag-emo\">$dEmo</span></i>$name</a>";
    }
}

$dropHtml = '';
if (count($dropItems) > 0) {
    $dropHtml = implode('', $dropItems);
} else {
    $dropHtml = '<div class="tcg-boss-empty-drop">Boss này chưa có vật phẩm rơi.</div>';
}

$bossinfohtml = <<<HTML
<div class="tcg-boss-page">
    <div class="tcg-boss-card">
        <div class="tcg-boss-head">
            <div>
                <div class="tcg-boss-kicker">Thông Tin BOSS</div>
                <h1 class="tcg-boss-name">$bossName</h1>
                <div class="tcg-boss-sub">Sát khí đang bao phủ khu vực. Chỉ nên khiêu chiến khi đã chuẩn bị đầy đủ.</div>
                <div class="tcg-boss-danger-line"><span>⚠ Cấp uy hiếp</span><b>NGUY HIỂM</b></div>
            </div>
            <div class="tcg-boss-mark"><span><b class="tcg-boss-emo">👹</b>MA THÚ</span></div>
        </div>

        <div class="tcg-boss-warning">
            <span>⚔ KHÍ TỨC BOSS</span>
            <b>Áp lực chiến đấu cực cao - thất bại có thể trả giá bằng sinh lực và thời gian hồi phục.</b>
        </div>

        <div class="tcg-boss-stats">
            <div class="tcg-boss-stat attack">
                <span>Công kích</span>
                <strong>$bossGj</strong>
            </div>
            <div class="tcg-boss-stat defense">
                <span>Phòng thủ</span>
                <strong>$bossFy</strong>
            </div>
            <div class="tcg-boss-stat hp">
                <span>Sinh lực</span>
                <strong>$bossHp</strong>
            </div>
        </div>

        <div class="tcg-boss-hpbox">
            <div class="tcg-boss-hp-head">
                <span>Trạng thái sinh lực</span>
                <b>$hpPercent%</b>
            </div>
            <div class="tcg-boss-hpbar">
                <i style="width:$hpPercent%;"></i>
            </div>
        </div>

        <div class="tcg-boss-section">
            <div class="tcg-boss-section-title">Mô tả</div>
            <div class="tcg-boss-desc">$bossInfo</div>
        </div>

        <div class="tcg-boss-section">
            <div class="tcg-boss-section-title">Vật phẩm có thể rơi</div>
            <div class="tcg-boss-drops">$dropHtml</div>
        </div>

        <div class="tcg-boss-actions">
            <a class="tcg-boss-btn battle" href="?cmd=$pvb">⚔ Phú quý tại thiên!</a>
            <a class="tcg-boss-btn secondary" onclick="javascript:history.back(-1);">Làm phiền rồi... <img width="15" height="15" src="./images/ct.png"></a>
        </div>
    </div>
</div>
HTML;

echo $bossinfohtml;
?>
