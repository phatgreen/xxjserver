<?php

$playertask = \player\getplayerrenwu($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$taskhtml = '';
$activeCount = 0;
$finishCount = 0;

for ($n=0;$n<count($playertask);$n++){
    $rwid = $playertask[$n]['rwid'];
    $mytaskinfo = $encode->encode("cmd=mytaskinfo&rwid=$rwid&sid=$sid");
    $rwname = htmlspecialchars(strip_tags($playertask[$n]['rwname']), ENT_QUOTES, 'UTF-8');
    $rwlx = $playertask[$n]['rwlx'];
    $rwzt = $playertask[$n]['rwzt'];

    if ($rwzt == 3){
        continue;
    }

    $activeCount++;
    $typeClass = 'normal';
    $typeText = 'Thường';
    if ($rwlx == 2){
        $typeClass = 'daily';
        $typeText = 'Ngày';
    } elseif ($rwlx == 3){
        $typeClass = 'main';
        $typeText = 'Chính';
    }

    $statusClass = 'active';
    $statusText = 'Đang làm';
    $statusIcon = '✦';
    if ($rwzt == 2){
        $finishCount++;
        $statusClass = 'ready';
        $statusText = 'Có thể trả';
        $statusIcon = '!';
    }

    $taskhtml .=<<<HTML
    <a class="event-task-card $statusClass" href="?cmd=$mytaskinfo">
        <span class="event-task-icon">$statusIcon</span>
        <span class="event-task-main">
            <span class="event-task-name">$rwname</span>
            <span class="event-task-meta">
                <span class="event-task-type $typeClass">$typeText</span>
                <span class="event-task-status">$statusText</span>
            </span>
        </span>
    </a>
HTML;
}

$taskView = trim($taskhtml) != '' ? $taskhtml : '<div class="event-empty">Hiện chưa có nhiệm vụ hoặc sự kiện đang mở.</div>';
$eventhtml = <<<HTML
<div class="event-page">
    <div class="event-hero">
        <div class="event-hero-text">
            <div class="event-kicker">Sự Kiện Tu Chân</div>
            <div class="event-title">Nhiệm Vụ</div>
            <div class="event-subtitle">Theo dõi nhiệm vụ đang nhận, hoàn thành mục tiêu và quay về NPC để nhận thưởng.</div>
            <div class="event-stats">
                <span><b>$activeCount</b> Đang mở</span>
                <span><b>$finishCount</b> Có thể trả</span>
            </div>
        </div>
        <div class="event-seal">✬</div>
    </div>

    <div class="event-banner">
        <img src="./images/mission-banner-v1.png" alt="Nhiệm vụ tu chân">
    </div>

    <div class="event-divider"><span>◇</span></div>

    <div class="event-task-list">
        $taskView
    </div>

    <div class="event-bottom">
        <a class="event-btn ghost" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a class="event-btn primary" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;
echo $eventhtml;
?>
