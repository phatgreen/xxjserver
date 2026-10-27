<?php

$player = player\getplayer($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$taskhtml = '';
$rwztt='';
$npc = player\getnpc($nid,$dblj);

function tcg_npc_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tcg_npc_task_mark($html)
{
    if (strpos($html, 'tan.gif') !== false) {
        return '<img class="npc-task-mark-img" src="images/tan.gif" alt="!">';
    }
    if (strpos($html, 'wen.gif') !== false) {
        return '<img class="npc-task-mark-img" src="images/wen.gif" alt="?">';
    }
    return '';
}

if ($npc->taskid!=''){
    $taskarr = explode(',',$npc->taskid);
    $taskhtml = '';
    for ($i=0;$i<count($taskarr);$i++){
        $task = \player\gettask($taskarr[$i],$dblj);
        $rwztt ='';
        if (!$task){
            continue;
        }
        if ($task->rwlx==1 || $task->rwlx==2){
            $reztarr = array('Bình thường','Hàng ngày');
            $rwzttext = $reztarr[ $task->rwlx - 1];
            $nowptrw = \player\getplayerrenwuonce($sid,$task->rwid,$dblj);
            if (!$nowptrw){
                if ($task->rwzl != 3){
                    
                    $rwztt = $rwztt."[$rwzttext]";
					$rwztt = '<img src="images/wen.gif" />';
                }elseif($task->rwyq == $nid){
                    
                    $rwztt = $rwztt."[$rwzttext]";
					$rwztt = '<img src="images/wen.gif" />';
                }else{
                    continue;
                }
            }else{
                if($nowptrw->rwzt==2){
                    if ($nowptrw->rwzl != 3){
                        $rwztt = '<img src="images/tan.gif" />';
                        $rwztt = $rwztt . "[$rwzttext]";
                    }elseif($nowptrw->rwcount == $nid){
                        $rwztt = '<img src="images/tan.gif" />';
                        $rwztt = $rwztt . "[$rwzttext]";
                    }else{
                        continue;
                    }
                }else{
                    continue;
                }
            }
        }

        if ($task->rwlx==3){
            $nowzxrw = \player\getplayerrenwuonce($sid,$task->rwid,$dblj);
            if ($nowzxrw){
                if ($nowzxrw->rwzt==2){
                    if ($nowzxrw->rwzl != 3){
                        $rwztt = '<img src="images/tan.gif" />';
                        $rwztt = $rwztt . "[Chính]";
                    }elseif($nowzxrw->rwcount == $nid){
                        $rwztt = '<img src="images/tan.gif" />';
                        $rwztt = $rwztt . "[Chính]";
                    }else{
                        continue;
                    }
                }else{
                    continue;
                }
            }else{
                if ($task->lastrwid <= 0){
                    if ($task->rwzl != 3){
                        $rwztt = '<img src="images/wen.gif" />';
                        $rwztt = $rwztt.'[Chính]';
                    }elseif($task->rwyq == $nid){
                        $rwztt = '<img src="images/wen.gif" />';
                        $rwztt = $rwztt.'[Chính]';
                    }else{
                        continue;
                    }

                }else{
                    $lastrw = \player\getplayerrenwuonce($sid,$task->lastrwid,$dblj);
                    if ($lastrw){
                        if ($lastrw->rwzt==3){
                            if ($task->rwzl != 3){
                                $rwztt = '<img src="images/wen.gif" />';
                                $rwztt = $rwztt.'[Chính]';
                            }elseif($task->rwyq == $nid){
                                $rwztt = '<img src="images/wen.gif" />';
                                $rwztt = $rwztt.'[Chính]';
                            }else{
                                continue;
                            }
                        }else{
                            continue;
                        }
                    }else{
                        continue;
                    }
                }
            }
        }
        $rwcmd = $encode->encode("cmd=task&nid=$nid&rwid=$taskarr[$i]&sid=$sid");
        $taskNameHtml = tcg_npc_h($task->rwname);
        $taskMarkHtml = tcg_npc_task_mark($rwztt);
        $taskhtml .=<<<HTML
        <a class="npc-task-link" href="?cmd=$rwcmd"><span>$taskNameHtml</span>$taskMarkHtml</a>
HTML;
    }
}
if ($taskhtml!=''){
    $taskhtml = '<div class="npc-task-list">'.$taskhtml.'</div>';
}
$gnhtml='';
if ($npc->muban != ''){
    $mubanitem =  explode(',',$npc->muban);
    foreach ($mubanitem as $muban){
        $muban = iconv('UTF-8','GB2312',$muban);
        if (file_exists("./npc/muban/$muban")){
            include "./npc/muban/$muban";
        }
    }
}

$npcName = isset($npc->nname) ? $npc->nname : 'NPC';
$npcSex = isset($npc->nsex) ? $npc->nsex : '';
$npcInfo = isset($npc->ninfo) ? $npc->ninfo : '';
$npcImgFile = './npc/' . $npcName . '.png';
$npcImg = file_exists($npcImgFile) ? './npc/' . tcg_npc_h($npcName . '.png') : './npc/talkNpc_1.png';
$wallet = isset($player->uyxb) ? number_format(intval($player->uyxb), 0, ',', '.') : '0';
$npcNameHtml = tcg_npc_h($npcName);
$npcSexHtml = tcg_npc_h($npcSex);
$npcInfoHtml = tcg_npc_h($npcInfo);
$actionVisibleText = trim(strip_tags($gnhtml));
$actionSection = ($actionVisibleText != '' || strpos($gnhtml, '<a') !== false)
    ? '<section class="npc-section npc-action-section"><div class="npc-section-title">Tương tác</div><div class="npc-actions-box">'.$gnhtml.'</div></section>'
    : '';
$taskSection = trim($taskhtml) != ''
    ? '<section class="npc-section npc-task-section"><div class="npc-section-title">Nhiệm vụ</div>'.$taskhtml.'</section>'
    : '';

$npchtml =<<<HTML
<div class="npc-page">
    <section class="npc-hero">
        <div class="npc-portrait">
            <img src="$npcImg" alt="$npcNameHtml">
        </div>
        <div class="npc-hero-info">
            <div class="npc-eyebrow">NPC trong khu vực</div>
            <h1>$npcNameHtml</h1>
            <div class="npc-desc">$npcInfoHtml</div>
            <div class="npc-meta-grid">
                <div class="npc-meta"><span>Giới tính</span><b>$npcSexHtml</b></div>
                <div class="npc-meta"><span>Linh thạch còn</span><b>$wallet</b></div>
            </div>
        </div>
    </section>

    $actionSection

    $taskSection

    <div class="npc-bottom">
        <a class="npc-nav-btn" href="#" onClick="javascript:history.back(-1);return false;">Trở về</a>
        <a class="npc-nav-btn primary" href="game.php?cmd=$gonowmid">Đại sảnh</a>
    </div>
</div>
HTML;
echo $npchtml
?>
