<?php
$task = \player\getplayerrenwuonce($sid,$rwid,$dblj);
$cs = \player\gettask($rwid,$dblj);
$player = \player\getplayer($sid,$dblj);

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$rwdjarr = explode(',',$task->rwdj);
$rwjlhtml = '';
$rewardHtml = '';
$rwhtml='';
function ptask_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ptask_reward_item($type, $name, $countHtml)
{
    return '<div class="ptask-reward-item '.$type.'"><span>'.ptask_h($name).'</span><b>'.$countHtml.'</b></div>';
}

if ($task->rwdj!=''){
    for ($i=0;$i<count($rwdjarr);$i++){
        $djarr = explode('|',$rwdjarr[$i]);
        $djid = $djarr[0];
        $djcount = $djarr[1];
        $rwdj = \player\getdaoju($djid,$dblj);
        $djinfo = $encode->encode("cmd=djinfo&djid=$rwdj->djid&sid=$sid");
        $rewardHtml .= "<a class='ptask-reward-item daoju' href='?cmd=$djinfo'><span>".ptask_h($rwdj->djname)."</span><b>x".intval($djcount)."</b></a>";
    }
}
if ($task->rwzb!=''){
    $ret = \player\zhuangbei_config_rows_by_ids($task->rwzb);
    for ($i=0;$i<count($ret);$i++){
        $zbname = $ret[$i]['zbname'];
        $zbid = $ret[$i]['zbid'];
        $zbcmd = $encode->encode("cmd=zbinfo_sys&zbid=$zbid&sid=$sid");
        $rewardHtml .= "<a class='ptask-reward-item zhuangbei' href='?cmd=$zbcmd'><span>".ptask_h($zbname)."</span><b>Trang bị</b></a>";
    }
}
if ($task->rwexp!=''){
    $rewardHtml .= ptask_reward_item('exp', 'Kinh nghiệm', intval($task->rwexp));
}
if ($task->rwyxb!=''){
    $rewardHtml .= ptask_reward_item('linh', 'Linh thạch', intval($task->rwyxb));
}
$rwjlhtml = $rewardHtml != '' ? '<div class="ptask-reward-grid">'.$rewardHtml.'</div>' : '<div class="ptask-empty">Không có phần thưởng.</div>';


//下面想进行一个收费传送搭建
$cs = \player\gettask($rwid,$dblj);//传送地址ID
$upmidlj = $encode->encode("cmd=gomid&newmid=$cs->rwqy&sid=$sid");//传送链接获取
$dt = player\getmid($cs->rwqy,$dblj);
$xiaohao = round($player->ulv*12+500);
if ($player->uyxb>=$xiaohao){
                    $sfhtml =<<<HTML
					<a class="ptask-btn teleport" href="?cmd=$upmidlj">Truyền tống <span>{$xiaohao} LT</span></a>
HTML;
                }else{
                    $sfhtml = '<div class="ptask-teleport-closed"><b>Truyền tống đóng</b><span>Không đủ '.$xiaohao.' Linh thạch</span><em>Bản đồ nhiệm vụ: '.ptask_h($dt->mname).'</em></div>'
					;	
                }
//2021 12 1 8 写的（抄），就这样吧，毛一角 11710 20 663 嘎嘎嘎



switch ($task->rwzl){
    case 1://收集
        $daoju = \player\getplayerdaoju($sid,$task->rwyq,$dblj);
        $rwyq = \player\getdaoju($cs->rwyq,$dblj);
		//$cs = \player\gettask($rwid,$dblj);传送地址获取,意思是找到SQL 任务中的某条ID， 下面进行该条ID元素上面进行查找关键词
		//$upmidlj = $encode->encode("cmd=gomid&newmid=$cs->rwqy&sid=$sid");//传送链接获取 <a href='?cmd=$upmidlj'>传送</a>
        $rwhtml ="Thu thập $task->rwcount$rwyq->djname";
        break;
    case 2://打怪
        $rwyq = \player\getyguaiwu($task->rwyq,$dblj);
		//$upmidlj = $encode->encode("cmd=gomid&newmid=$cs->rwqy&sid=$sid");
        $rwhtml ="Tiêu diệt $task->rwcount*$rwyq->gname";
        break;
    case 3://Đối thoại
        $tjnpc = \player\getnpc($task->rwcount,$dblj);
        
		//$upmidlj = $encode->encode("cmd=gomid&newmid=$cs->rwqy&sid=$sid");//地图
        $rwhtml ="Đi tìm $tjnpc->nname";
        break;
}

$tjnpc = \player\getnpc($task->nid,$dblj);
//$clmid = player\getmid($player->nowmid,$dblj);
    $sql = "SELECT * FROM mid WHERE FIND_IN_SET('". $task->nid ."', mnpc)";//获取npc
    $cxjg = $dblj->query($sql);
    $clmid = $cxjg->fetch(PDO::FETCH_ASSOC);
	
	$cmname = $clmid['mname'];

$progressCurrent = intval($task->rwnowcount);
$progressNeed = max(1, intval($task->rwcount));
$progressPercent = min(100, floor($progressCurrent * 100 / $progressNeed));
$taskName = ptask_h($task->rwname);
$taskTarget = ptask_h($rwhtml);
$npcName = ptask_h($tjnpc ? $tjnpc->nname : '');
$mapName = ptask_h($cmname);
$targetMap = ptask_h($dt->mname);

$taskinfo = <<<HTML
<div class="ptask-page">
    <section class="ptask-hero">
        <div class="ptask-icon">📜</div>
        <div>
            <div class="ptask-eyebrow">Nhiệm vụ đang nhận</div>
            <h1>$taskName</h1>
            <p>Tiếp nhận từ: <b>$npcName</b> · $mapName</p>
        </div>
    </section>

    <section class="ptask-section">
        <div class="ptask-section-title">Mục tiêu</div>
        <div class="ptask-target">$taskTarget</div>
        <div class="ptask-location"><span>Vị trí</span><b>$targetMap</b></div>
        <div class="ptask-progress">
            <div class="ptask-progress-top"><span>Tiến độ</span><b>$progressCurrent / $progressNeed</b></div>
            <div class="ptask-progress-bar"><i style="width:$progressPercent%"></i></div>
        </div>
    </section>

    <section class="ptask-section">
        <div class="ptask-section-title">Phần thưởng</div>
        $rwjlhtml
    </section>

    <section class="ptask-section">
        <div class="ptask-section-title">Di chuyển nhanh</div>
        $sfhtml
    </section>

    <div class="ptask-bottom">
        <a class="ptask-btn" href="#" onClick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="ptask-btn primary" href="?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;
echo $taskinfo;
?>
