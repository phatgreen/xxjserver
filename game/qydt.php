<?php
$player = player\getplayer($sid, $dblj);
$clmid = player\getmid($player->nowmid, $dblj);

function qydt_h($value)
{
    return htmlspecialchars(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
}

$gonowmid = $encode->encode("cmd=gomid&newmid=$clmid->mid&sid=$sid");
$tab = isset($canshu) ? trim($canshu) : 'map';
if ($tab != 'guai' && $tab != 'npc' && $tab != 'map') {
    $tab = 'map';
}
$qydtGuai = $encode->encode("cmd=qydt&canshu=guai&sid=$sid");
$qydtNpc = $encode->encode("cmd=qydt&canshu=npc&sid=$sid");
$qydtMap = $encode->encode("cmd=qydt&canshu=map&sid=$sid");

$currentMid = intval($clmid->mid);
$currentName = qydt_h($clmid->mname);
$currentQy = player\getqy(intval($clmid->mqy), $dblj);
$currentRegion = qydt_h($currentQy->qyname ? $currentQy->qyname : $clmid->mqy);
$mapImage = './dt/ditu/'.intval($clmid->mqy).'.png';
$mapImageFile = __DIR__ . '/../dt/ditu/'.intval($clmid->mqy).'.png';
if (!is_file($mapImageFile)) {
    $mapImage = './dt/ditu/default.png';
    $mapImageFile = __DIR__ . '/../dt/ditu/default.png';
}
if (is_file($mapImageFile)) {
    $mapImage .= '?v=' . filemtime($mapImageFile);
}

$directions = array(
    array('label' => 'Bắc', 'icon' => '↑', 'mid' => intval($clmid->upmid)),
    array('label' => 'Tây', 'icon' => '←', 'mid' => intval($clmid->leftmid)),
    array('label' => 'Đông', 'icon' => '→', 'mid' => intval($clmid->rightmid)),
    array('label' => 'Nam', 'icon' => '↓', 'mid' => intval($clmid->downmid))
);

$routeHtml = '';
foreach ($directions as $dir) {
    if ($dir['mid'] <= 0) {
        continue;
    }
    $target = player\getmid($dir['mid'], $dblj);
    if (!$target || trim($target->mname) == '') {
        continue;
    }
    $go = $encode->encode("cmd=gomid&newmid=".$dir['mid']."&sid=$sid");
    $targetName = qydt_h($target->mname);
    $routeHtml .= '<a class="qydt-route" href="?cmd='.$go.'"><span>'.$dir['label'].'</span><b>'.$targetName.'</b><i>'.$dir['icon'].'</i></a>';
}
if (trim($routeHtml) == '') {
    $routeHtml = '<div class="qydt-empty">Không có lối đi trực tiếp từ vị trí này.</div>';
}

$sameRegionHtml = '';
$allPlaceHtml = '';
$allMid = \player\getqy_dt($dblj);
foreach ($allMid as $row) {
    $mid = isset($row['mid']) ? intval($row['mid']) : 0;
    if ($mid <= 0) {
        continue;
    }

    $mname = isset($row['mname']) ? qydt_h($row['mname']) : '';
    if ($mname == '') {
        continue;
    }

    $mqy = isset($row['mqy']) ? intval($row['mqy']) : 0;
    $go = $encode->encode("cmd=gomid&newmid=$mid&sid=$sid");
    $active = ($mid == $currentMid) ? ' active' : '';
    $item = '<a class="qydt-place'.$active.'" href="?cmd='.$go.'"><span>Khu '.$mqy.'</span><b>'.$mname.'</b></a>';

    if ($mqy == intval($clmid->mqy)) {
        $sameRegionHtml .= $item;
    }
    $allPlaceHtml .= $item;
}

if (trim($sameRegionHtml) == '') {
    $sameRegionHtml = '<div class="qydt-empty">Chưa có địa điểm trong khu vực này.</div>';
}
if (trim($allPlaceHtml) == '') {
    $allPlaceHtml = '<div class="qydt-empty">Chưa có dữ liệu bản đồ.</div>';
}

$monsterHtml = '';
$sql = "select * from midguaiwu where mid='$player->nowmid' AND sid = ''";
$monsterRows = $dblj->query($sql)->fetchAll(PDO::FETCH_ASSOC);
foreach ($monsterRows as $row) {
    $gid = isset($row['id']) ? intval($row['id']) : 0;
    $gyid = isset($row['gyid']) ? intval($row['gyid']) : 0;
    if ($gid <= 0) {
        continue;
    }
    $gname = isset($row['gname']) ? qydt_h($row['gname']) : 'Quái vật';
    $glv = isset($row['glv']) ? intval($row['glv']) : 0;
    $ghp = isset($row['ghp']) ? intval($row['ghp']) : 0;
    $gmaxhp = isset($row['gmaxhp']) ? intval($row['gmaxhp']) : 0;
    $gwcmd = $encode->encode("cmd=getginfo&gid=$gid&gyid=$gyid&sid=$sid&nowmid=$player->nowmid");
    $monsterHtml .= '<a class="qydt-entity monster" href="?cmd='.$gwcmd.'"><div class="qydt-entity-name"><span>Quái vật:</span><b>'.$gname.'</b></div><i>Cấp '.$glv.'</i><em>HP '.$ghp.'/'.$gmaxhp.'</em></a>';
}
if (trim($monsterHtml) == '') {
    $monsterHtml = '<div class="qydt-empty">Khu vực này hiện chưa có quái vật.</div>';
}

$npcHtml = '';
if (trim($clmid->mnpc) != '') {
    $npcIds = explode(',', $clmid->mnpc);
    foreach ($npcIds as $npcIdRaw) {
        $npcId = intval(trim($npcIdRaw));
        if ($npcId <= 0) {
            continue;
        }
        $npc = \player\getnpc($npcId);
        if (!$npc) {
            continue;
        }
        $nname = isset($npc->nname) ? qydt_h($npc->nname) : 'NPC';
        $ninfo = isset($npc->ninfo) ? qydt_h($npc->ninfo) : '';
        $npccmd = $encode->encode("cmd=npc&nid=$npcId&sid=$sid");
        $npcHtml .= '<a class="qydt-entity npc" href="?cmd='.$npccmd.'"><div class="qydt-entity-name"><span>NPC:</span><b>'.$nname.'</b></div><i>'.($ninfo != '' ? $ninfo : 'Có thể trò chuyện').'</i></a>';
    }
}
if (trim($npcHtml) == '') {
    $npcHtml = '<div class="qydt-empty">Khu vực này hiện chưa có NPC.</div>';
}

$tabGuaiClass = $tab == 'guai' ? ' class="active"' : '';
$tabNpcClass = $tab == 'npc' ? ' class="active"' : '';
$tabMapClass = $tab == 'map' ? ' class="active"' : '';

$mainContent = '';
if ($tab == 'guai') {
    $mainContent = <<<HTML
    <div class="qydt-section">
        <div class="qydt-section-title">Quái vật trong khu vực</div>
        <div class="qydt-entity-grid">$monsterHtml</div>
    </div>
HTML;
} elseif ($tab == 'npc') {
    $mainContent = <<<HTML
    <div class="qydt-section">
        <div class="qydt-section-title">NPC trong khu vực</div>
        <div class="qydt-entity-grid">$npcHtml</div>
    </div>
HTML;
} else {
    $mainContent = <<<HTML
    <div class="qydt-section">
        <div class="qydt-section-title">Lối đi gần nhất</div>
        <div class="qydt-route-grid">$routeHtml</div>
    </div>

    <div class="qydt-section">
        <div class="qydt-section-title">Địa điểm trong khu vực</div>
        <div class="qydt-place-grid">$sameRegionHtml</div>
    </div>

    <details class="qydt-all">
        <summary>Toàn bộ bản đồ</summary>
        <div class="qydt-place-grid">$allPlaceHtml</div>
    </details>
HTML;
}

$qydthtml = <<<HTML
<div class="qydt-page">
    <div class="qydt-tabs">
        <a$tabGuaiClass href="?cmd=$qydtGuai">Quái vật</a>
        <a$tabNpcClass href="?cmd=$qydtNpc">NPC</a>
        <a$tabMapClass href="?cmd=$qydtMap">Bản đồ</a>
    </div>

    <div class="qydt-hero">
        <div class="qydt-copy">
            <div class="qydt-kicker">Khu vực bản đồ</div>
            <h1>$currentName</h1>
            <p>Theo dõi vị trí hiện tại, lối đi kế cận và các điểm dịch chuyển trong khu vực.</p>
            <div class="qydt-meta">
                <span>Vị trí: <b>$currentName</b></span>
                <span>Khu: <b>$currentRegion</b></span>
            </div>
        </div>
        <div class="qydt-map-frame">
            <img src="$mapImage" alt="Bản đồ">
        </div>
    </div>

    $mainContent

    <div class="qydt-bottom">
        <a class="qydt-back" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a class="qydt-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $qydthtml;
?>
