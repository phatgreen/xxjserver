<?php

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$teleportNotice = '';

if (isset($canshu) && $canshu == 'gonpc') {
    $goNpcId = isset($nid) ? intval($nid) : 0;
    $goMid = isset($newmid) ? intval($newmid) : 0;
    $teleportCost = round($player->ulv * 12 + 500);

    if ($goNpcId > 0 && $goMid > 0) {
        if ($player->uyxb >= $teleportCost) {
            \player\changeyxb(2, $teleportCost, $sid, $dblj);
            \player\changeplayersx('nowmid', $goMid, $sid, $dblj);
            $npcCmd = $encode->encode("cmd=npc&nid=$goNpcId&sid=$sid");
            header("Location: game.php?cmd=$npcCmd");
            exit();
        }

        $teleportNotice = 'Không đủ '.number_format($teleportCost, 0, '.', '.').' Linh thạch để di chuyển nhanh đến NPC.';
    }
}

function tcg_taskguide_h($value)
{
    return htmlspecialchars(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
}

function tcg_taskguide_npc_mid($npcId, $dblj)
{
    $stmt = $dblj->prepare("SELECT mid,mname FROM mid WHERE FIND_IN_SET(?, mnpc) LIMIT 1");
    $stmt->execute(array(intval($npcId)));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return array('mid' => '', 'mname' => 'Chưa rõ khu vực');
    }

    return array(
        'mid' => isset($row['mid']) ? $row['mid'] : '',
        'mname' => isset($row['mname']) ? $row['mname'] : 'Chưa rõ khu vực'
    );
}

function tcg_taskguide_target_text($task, $dblj)
{
    $rwzl = intval($task['rwzl']);
    $count = max(1, intval($task['rwcount']));

    if ($rwzl == 1) {
        $item = \player\getdaoju($task['rwyq'], $dblj);
        $name = ($item && isset($item->djname)) ? $item->djname : 'đạo cụ';
        return 'Thu thập '.$count.' '.$name;
    }

    if ($rwzl == 2) {
        $monster = \player\getyguaiwu($task['rwyq'], $dblj);
        $name = ($monster && isset($monster->gname)) ? $monster->gname : 'quái vật';
        return 'Tiêu diệt '.$count.' '.$name;
    }

    if ($rwzl == 3) {
        $npc = \player\getnpc($task['rwcount']);
        $name = ($npc && isset($npc->nname)) ? $npc->nname : 'NPC chỉ định';
        return 'Đi tìm '.$name;
    }

    return 'Hoàn thành yêu cầu nhiệm vụ';
}

function tcg_taskguide_city_mids()
{
    return array(
        239 => true, // Tụ Tiên Thành
        272 => true, // Viêm Dương Thành
        278 => true, // Vân Thành
        283 => true, // Vũ Thiên Đô
        284 => true, // Lăng Tiêu Thành
        285 => true, // Thánh Kinh Thành
        286 => true, // Thiên Đế Thành
        287 => true, // Thanh Vân Thành
        289 => true, // Vân Tiêu Bảo Đô
        293 => true, // Tịch Dương Cổ Đô
        297 => true, // Bát Hoang Thành
        319 => true, // Giang Đô
        327 => true  // Hải Để Thành
    );
}

function tcg_taskguide_is_city_area($mid, $name)
{
    $cityMids = tcg_taskguide_city_mids();
    if (isset($cityMids[intval($mid)])) {
        return true;
    }

    return false;
}

function tcg_taskguide_source_npc($task, $npcByTask, $dblj)
{
    $rwid = (string)intval($task['rwid']);

    if (!isset($npcByTask[$rwid])) {
        return false;
    }

    $fallback = false;

    foreach ($npcByTask[$rwid] as $npc) {
        $npcId = intval($npc['id']);

        $stmt = $dblj->prepare("SELECT mid,mname FROM mid WHERE FIND_IN_SET(?, mnpc) ORDER BY mid ASC");
        $stmt->execute(array($npcId));
        $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $mid = count($maps) > 0 ? $maps[0] : tcg_taskguide_npc_mid($npcId, $dblj);

        foreach ($maps as $mapRow) {
            if (tcg_taskguide_is_city_area($mapRow['mid'], $mapRow['mname'])) {
                $mid = $mapRow;
                break;
            }
        }

        $source = array(
            'id' => $npcId,
            'name' => isset($npc['nname']) ? $npc['nname'] : 'NPC',
            'map_id' => $mid['mid'],
            'map_name' => $mid['mname']
        );

        if (tcg_taskguide_is_city_area($mid['mid'], $mid['mname'])) {
            return $source;
        }

        if (!$fallback) {
            $fallback = $source;
        }
    }

    return $fallback;
}

function tcg_taskguide_can_show($task, $sid, $dblj)
{
    $current = \player\getplayerrenwuonce($sid, $task['rwid'], $dblj);
    if ($current) {
        return false;
    }

    $lastrwid = intval($task['lastrwid']);
    if (intval($task['rwlx']) == 3 && $lastrwid > 0) {
        $previous = \player\getplayerrenwuonce($sid, $lastrwid, $dblj);
        return $previous && intval($previous->rwzt) == 3;
    }

    return $lastrwid <= 0 || intval($task['rwlx']) != 3;
}

function tcg_taskguide_is_main($task, $sourceNpc)
{
    return tcg_taskguide_is_city_area($sourceNpc['map_id'], $sourceNpc['map_name']);
}

function tcg_taskguide_card($task, $sourceNpc, $targetText, $targetMap, $cmd, $kindClass, $kindText, $teleportCost, $stateText, $isOpen = false, $toggleCmd = '')
{
    $taskName = tcg_taskguide_h($task['rwname']);
    $taskInfo = tcg_taskguide_h($task['rwinfo']);
    $npcName = tcg_taskguide_h($sourceNpc['name']);
    $mapName = tcg_taskguide_h($sourceNpc['map_name']);
    $targetText = tcg_taskguide_h($targetText);
    $targetMap = tcg_taskguide_h($targetMap);
    $exp = intval($task['rwexp']);
    $linh = intval($task['rwyxb']);
    $teleportCostText = number_format($teleportCost, 0, '.', '.');
    $stateHtml = $stateText != '' ? '<span class="news-task-state">'.tcg_taskguide_h($stateText).'</span>' : '';
    if (strpos($stateText, 'Đang khóa') === 0) {
        $actionHtml = '<span class="news-task-action news-task-locked-btn">Chưa mở</span>';
    } else {
        $actionHtml = '<a class="news-task-action" href="?cmd='.$cmd.'">Gặp NPC · '.$teleportCostText.'</a>';
    }

    $rewardText = "Thưởng: $exp tu vi · $linh linh thạch";
    $openClass = $isOpen ? ' open' : '';
    $arrow = $isOpen ? '▾' : '▸';
    $linkCmd = $toggleCmd != '' ? $toggleCmd : $cmd;

    if ($isOpen) {
        $detailHtml = <<<HTML
        <div class="news-task-detail">
            <p>$taskInfo</p>
            <div class="news-task-guide">
                <div><span>Đi đến</span><b>$mapName</b></div>
                <div><span>Gặp NPC</span><b>$npcName</b></div>
                <div><span>Mục tiêu</span><b>$targetText</b></div>
                <div><span>Khu nhiệm vụ</span><b>$targetMap</b></div>
            </div>
            <div class="news-task-foot">
                <span>$rewardText</span>
                $actionHtml
            </div>
        </div>
HTML;
    } else {
        $detailHtml = <<<HTML
        <div class="news-task-summary">
            <span class="news-task-reward">$rewardText</span>
            $actionHtml
        </div>
HTML;
    }

    return <<<HTML
    <article class="news-task-card $kindClass $openClass">
        <a class="news-task-toggle" href="?cmd=$linkCmd">
            <span class="news-task-badge">$kindText</span>
            <span class="news-task-name">$taskName</span>
            <span class="news-task-arrow">$arrow</span>
            <span class="news-task-id">#{$task['rwid']}</span>
        </a>
		{$stateHtml}
        {$detailHtml}
    </article>
HTML;
}

$allTasks = \player\renwu_config_all();
$allNpc = \player\getallnpc();
$npcByTask = array();

foreach ($allNpc as $npc) {
    if (!isset($npc['taskid']) || trim($npc['taskid']) == '') {
        continue;
    }

    $ids = explode(',', $npc['taskid']);
    foreach ($ids as $taskId) {
        $taskId = (string)intval(trim($taskId));
        if ($taskId == '0') {
            continue;
        }

        if (!isset($npcByTask[$taskId])) {
            $npcByTask[$taskId] = array();
        }
        $npcByTask[$taskId][] = $npc;
    }
}

$teleportCost = round($player->ulv * 12 + 500);
$openTaskId = isset($task_id) ? intval($task_id) : 0;
$filterType = isset($ttype) ? trim($ttype) : 'all';
if (!in_array($filterType, array('all', 'main', 'side'))) {
    $filterType = 'all';
}
$currentPage = isset($pg) ? max(1, intval($pg)) : 1;
$perPage = 5;

$mainTasks = array();
$sideTasks = array();
$mainLockedTasks = array();

foreach ($allTasks as $task) {
    if (!isset($task['rwid'])) {
        continue;
    }

    $sourceNpc = tcg_taskguide_source_npc($task, $npcByTask, $dblj);
    if (!$sourceNpc) {
        continue;
    }

    $targetMid = \player\getmid($task['rwqy'], $dblj);
    $targetMap = ($targetMid && isset($targetMid->mname) && $targetMid->mname != '') ? $targetMid->mname : 'Chưa rõ khu vực';
    $npcCmd = $encode->encode("cmd=taskguide&canshu=gonpc&nid=".$sourceNpc['id']."&newmid=".$sourceNpc['map_id']."&sid=$sid");
    $isOpen = ($openTaskId == intval($task['rwid']));
    $toggleCmd = $isOpen
        ? $encode->encode("cmd=taskguide&ttype=$filterType&pg=$currentPage&sid=$sid")
        : $encode->encode("cmd=taskguide&task_id=".$task['rwid']."&ttype=$filterType&pg=$currentPage&sid=$sid");
    $targetText = tcg_taskguide_target_text($task, $dblj);
    $isMainTask = tcg_taskguide_is_main($task, $sourceNpc);
    $canShow = tcg_taskguide_can_show($task, $sid, $dblj);
    $currentTask = \player\getplayerrenwuonce($sid, $task['rwid'], $dblj);
    $stateText = '';

    if ($currentTask) {
        if (intval($currentTask->rwzt) == 3) {
            continue;
        }
        $stateText = intval($currentTask->rwzt) == 2 ? 'Đã hoàn thành, có thể trả' : 'Đang nhận';
    } elseif ($isMainTask && !$canShow) {
        $lastId = intval($task['lastrwid']);
        if ($lastId > 0) {
            $lastTask = \player\gettask($lastId, $dblj);
            $lastName = ($lastTask && isset($lastTask->rwname)) ? $lastTask->rwname : ('#'.$lastId);
            $stateText = 'Đang khóa, cần hoàn thành NV: '.$lastName;
        } else {
            continue;
        }
    }

    $item = array(
        'task' => $task, 
		'sourceNpc' => $sourceNpc, 
		'targetText' => $targetText,
        'targetMap' => $targetMap, 
		'npcCmd' => $npcCmd, 
		'isOpen' => $isOpen,
        'toggleCmd' => $toggleCmd, 
		'stateText' => $stateText
    );

    if ($isMainTask) {
        if ($stateText == '') {
            $mainTasks[] = $item;
        } else {
            $mainLockedTasks[] = $item;
        }
    } else {
        if (!$canShow || $currentTask) {
            continue;
        }
        $sideTasks[] = $item;
    }
}

$allMainTasks = array_merge($mainTasks, $mainLockedTasks);
$mainCount = count($mainTasks);
$sideCount = count($sideTasks);
$mainTotal = count($allMainTasks);
$sideTotal = count($sideTasks);

function tcg_taskguide_build_page_links($totalPages, $currentPage, $filterType, $sid, $encode)
{
    if ($totalPages <= 1) {
        return '';
    }
    $links = '';
    for ($i = 1; $i <= $totalPages; $i++) {
        $cmd = $encode->encode("cmd=taskguide&ttype=$filterType&pg=$i&sid=$sid");
        $cls = ($i == $currentPage) ? ' current' : '';
        $links .= '<a class="news-task-page-link'.$cls.'" href="?cmd='.$cmd.'">'.$i.'</a>';
    }
    return $links;
}

if ($filterType == 'all') {
    $displayTasks = array_merge($allMainTasks, $sideTasks);
    $totalTasks = $mainTotal + $sideTotal;
} elseif ($filterType == 'main') {
    $displayTasks = $allMainTasks;
    $totalTasks = $mainTotal;
} else {
    $displayTasks = $sideTasks;
    $totalTasks = $sideTotal;
}

$totalPages = max(1, ceil($totalTasks / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$pageStart = ($currentPage - 1) * $perPage;
$pageTasks = array_slice($displayTasks, $pageStart, $perPage);

$pageHtml = '';
foreach ($pageTasks as $item) {
    $kindClass = (strpos($item['task']['rwid'], 'main') !== false || $item['stateText'] == '') ? 'news-main' : 'news-main';
    if ($item['stateText'] != '' && strpos($item['stateText'], 'Đang khóa') === 0) {
        $kindClass = 'news-main news-locked';
    } elseif ($filterType == 'side' || ($filterType == 'all' && !tcg_taskguide_is_main($item['task'], $item['sourceNpc']))) {
        $kindClass = 'news-side';
    }
    $kindText = ($kindClass == 'news-side') ? 'Phụ tuyến' : 'Chính tuyến';
    $pageHtml .= tcg_taskguide_card(
        $item['task'], $item['sourceNpc'], $item['targetText'], $item['targetMap'],
        $item['npcCmd'], $kindClass, $kindText, $teleportCost, $item['stateText'],
        $item['isOpen'], $item['toggleCmd']
    );
}

if ($pageHtml == '') {
    $pageHtml = '<div class="news-task-empty">Hiện chưa có nhiệm vụ nào.</div>';
}

$paginationLinks = tcg_taskguide_build_page_links($totalPages, $currentPage, $filterType, $sid, $encode);
$paginationHtml = $paginationLinks != '' ? '<div class="news-task-pagination">'.$paginationLinks.'</div>' : '';

$filterCmdMain = $encode->encode("cmd=taskguide&ttype=main&sid=$sid");
$filterCmdSide = $encode->encode("cmd=taskguide&ttype=side&sid=$sid");
$filterCmdAll = $encode->encode("cmd=taskguide&sid=$sid");
$btnMainCls = ($filterType == 'main') ? ' active' : '';
$btnSideCls = ($filterType == 'side') ? ' active' : '';
$btnAllCls = ($filterType == 'all') ? ' active' : '';

$noticeHtml = $teleportNotice != '' ? '<div class="news-task-notice">'.tcg_taskguide_h($teleportNotice).'</div>' : '';
$taskBannerFile = __DIR__ . '/../images/bg/taskguide.png';
$taskBanner = './images/bg/taskguide.png';
if (is_file($taskBannerFile)) {
    $taskBanner .= '?v=' . filemtime($taskBannerFile);
}

$html = <<<HTML


<div class="news-task-page">
    <section class="news-task-hero news-task-banner-hero">
        <div class="news-task-banner"><img src="$taskBanner" alt="Bảng Nhiệm Vụ"></div>
        <div class="news-task-hero-content">
        <div class="news-task-badges">
            <div class="news-task-mark">$teleportCost Linh / lượt</div>
            <a class="news-task-ribbon" href="game.php?cmd=mytask&sid=$sid">NHIỆM VỤ</a>
        </div>
        <div class="news-task-kicker">Tin tức tu chân</div>
        <div class="news-task-title">Bảng Nhiệm Vụ</div>
        <div class="news-task-subtitle">Danh sách nhiệm vụ và chỉ dẫn gặp NPC. Nút Gặp NPC là di chuyển nhanh, phí hiện tại: $teleportCost Linh thạch.</div>
        <div class="news-task-stats">
            <span><b>$mainCount</b> Chính tuyến</span>
            <span><b>$sideCount</b> Phụ tuyến</span>
        </div>
        </div>
    </section>
    $noticeHtml

    <div class="news-task-filters">
        <a class="news-task-filter-btn$btnAllCls" href="?cmd=$filterCmdAll">Tất cả</a>
        <a class="news-task-filter-btn$btnMainCls" href="?cmd=$filterCmdMain">Chính tuyến</a>
        <a class="news-task-filter-btn$btnSideCls" href="?cmd=$filterCmdSide">Phụ tuyến</a>
    </div>

    <section class="news-task-section">
        <div class="news-task-section-head"><h1>Nhiệm vụ ($totalTasks)</h1><span>Trang $currentPage/$totalPages</span></div>
        <div class="news-task-grid">$pageHtml</div>
        $paginationHtml
    </section>

    <div class="news-task-bottom">
        <a class="news-task-btn" href="#" onClick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="news-task-btn primary" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $html;
?>
