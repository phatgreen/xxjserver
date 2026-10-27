<?php
require_once __DIR__ . '/../class/function/club_treasury.php';
$rwid = isset($rwid) ? intval($rwid) : 0;
$nid = isset($nid) ? intval($nid) : 0;
$task = \player\gettask($rwid,$dblj);
$player = \player\getplayer($sid,$dblj);
$ptask = \player\getplayerrenwuonce($sid,$rwid,$dblj);
$rwdjarr = explode(',',$task->rwdj);
$rwyparr = explode(',',$task->rwyp);
$rwjlhtml = '';
$rewardHtml = '';
$jldjidarr = array();
$jldjslarr = array();
$jlypidarr = array();
$jlypslarr = array();
$jlzbslarr = array();

function tcg_task_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tcg_task_reward_item($type, $name, $countHtml)
{
    return '<div class="task-reward-item '.$type.'"><span>'.tcg_task_h($name).'</span><b>'.$countHtml.'</b></div>';
}

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$jieshourw = $encode->encode("cmd=task&nid=$nid&canshu=jieshou&rwid=$rwid&sid=$sid");
$tijiaorw = $encode->encode("cmd=task&nid=$nid&canshu=tijiao&rwid=$rwid&sid=$sid");
$rwhtml = '';
$tishi = '';
if ($ptask){
    if ($ptask->rwzt == 3){//任务状态选择
        echo "<a href=\"?cmd=$gonowmid\">Đại Sảnh</a>";
        exit();
    }
}

if ($task->rwdj!=''){
    for ($i=0;$i<count($rwdjarr);$i++){
        $djarr = explode('|',$rwdjarr[$i]);
        $djid = $djarr[0];
        $djcount = $djarr[1];
        array_push($jldjidarr,$djid);
        array_push($jldjslarr,$djcount);
        $rwdj = \player\getdaoju($djid,$dblj);
        $djinfo = $encode->encode("cmd=djinfo&djid=$rwdj->djid&sid=$sid");
        $rewardHtml .= "<a class='task-reward-item daoju' href='?cmd=$djinfo'><span>".tcg_task_h($rwdj->djname)."</span><b>x".intval($djcount)."</b></a>";
    }
}

if ($task->rwyp!=''){
    for ($i=0;$i<count($rwyparr);$i++){
        $yparr = explode('|',$rwyparr[$i]);
        $ypid = $yparr[0];
        $ypcount = $yparr[1];
        array_push($jlypidarr,$ypid);
        array_push($jlypslarr,$ypcount);
        $rwyp = \player\getyaopinonce($ypid,$dblj);
        $ypcmd = $encode->encode("cmd=ypinfo&ypid=$ypid&sid=$sid");
        $rewardHtml .= "<a class='task-reward-item yaopin' href='?cmd=$ypcmd'><span>".tcg_task_h($rwyp->ypname)."</span><b>x".intval($ypcount)."</b></a>";
    }
}

if ($task->rwzb!=''){
    $ret = \player\zhuangbei_config_rows_by_ids($task->rwzb);
    for ($i=0;$i<count($ret);$i++){
        $zbid = $ret[$i]['zbid'];
        $zbname = $ret[$i]['zbname'];
        array_push($jlzbslarr,$zbid);
        $zbkzb = \player\getzbkzb($zbid,$dblj);
        $zbcmd = $encode->encode("cmd=zbinfo_sys&zbid=$zbkzb->zbid&sid=$sid");
        $rewardHtml .= "<a class='task-reward-item zhuangbei' href='?cmd=$zbcmd'><span>".tcg_task_h($zbname)."</span><b>Trang bị</b></a>";
    }
}
if ($task->rwexp!=''){
    $rewardHtml .= tcg_task_reward_item('exp', 'Kinh nghiệm', intval($task->rwexp));
}
if ($task->rwyxb!=''){
    $rewardHtml .= tcg_task_reward_item('linh', 'Linh thạch', intval($task->rwyxb));
}
$rwjlhtml = $rewardHtml != '' ? '<div class="task-reward-grid">'.$rewardHtml.'</div>' : '<div class="task-empty">Không có phần thưởng.</div>';


if (isset($canshu)){
    switch ($canshu){
        case 'jieshou':
            if ($ptask){
                $tishi = 'Vui lòng không nhận nhiệm vụ nhiều lần';
                break;
            }
            $day = 0;
            if ($task->rwlx==2){
                $day = date('Y-m-d');
            }
            $stmt = $dblj->prepare("INSERT INTO playerrenwu(sid, rwzt, rwid, rwnowcount, `data`, `nid`) VALUES (:sid, 1, :rwid, 0, :data, :nid)");
            $stmt->execute(array(':sid' => $sid, ':rwid' => $rwid, ':data' => $day, ':nid' => $nid));
            $tishi = 'Tiếp nhận thành công';
            if ($task->rwzl==1){
                $daoju = \player\getplayerdaoju($sid,$task->rwyq,$dblj);
                if ($daoju){
                    if ($daoju->djsum>0){
                        \player\changerwyq($rwid,$daoju->djsum,$sid,$dblj);
                    }
                }
            }
            if ($task->rwzl==4){
                $daoju = \player\getplayerdaoju($sid,$task->rwyq,$dblj);
                if ($daoju){
                    if ($daoju->djsum>0){
                        \player\changerwyq($rwid,$daoju->djsum,$sid,$dblj);
                    }
                }
            }
            if ($task->rwzl==3){
                $sql = "update `playerrenwu` set rwzt = 2 WHERE rwid = $rwid and sid ='$sid'";
                $dblj->exec($sql);
            }
            break;
        case 'tijiao':
            if ($ptask && $ptask->rwid==$rwid && $ptask->rwzt == 2){
                if ($ptask->rwnowcount>= $ptask->rwcount || $ptask->rwzl == 3){
                    $sql = "update playerrenwu set rwzt=3,rwnowcount=0 WHERE sid='$sid' AND rwid = $rwid";
                    $dblj->exec($sql);
                    \player\changeexp($sid,$dblj,$task->rwexp);
                    \player\changeyxb(1,$task->rwyxb,$sid,$dblj);
                    if ($ptask->rwzl==1){
                        \player\deledjsum($ptask->rwyq,$ptask->rwcount,$sid,$dblj);
                    }
                    if ($ptask->rwzl==4){
                        $itemType = 'daoju';
                        $ydCheck = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaodan_config.json'), true);
                        if (isset($ydCheck[intval($ptask->rwyq)])) {
                            $itemType = 'yaodan';
                        }
                        $ypCheck = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaopin_config.json'), true);
                        if (isset($ypCheck[intval($ptask->rwyq)])) {
                            $itemType = 'yaopin';
                        }
                        if ($itemType === 'yaodan') {
                            \player\deleyaodan($sid, intval($ptask->rwyq), intval($ptask->rwcount), $dblj);
                        } elseif ($itemType === 'yaopin') {
                            \player\deleyaopin($sid, intval($ptask->rwyq), intval($ptask->rwcount), $dblj);
                        } else {
                            \player\deledjsum($ptask->rwyq, $ptask->rwcount, $sid, $dblj);
                        }
                        $clubp = \player\getclubplayer_once($sid, $dblj);
                        if ($clubp && intval($clubp->clubid) > 0) {
                            \player\club_treasury_add(
                                intval($clubp->clubid),
                                $itemType,
                                intval($ptask->rwyq),
                                intval($ptask->rwcount),
                                $sid,
                                $dblj
                            );
                        }
                    }
                    for ($i=0;$i<count($jldjidarr);$i++){
                        \player\adddj($sid,$jldjidarr[$i],$jldjslarr[$i],$dblj);
                    }
                    for ($i=0;$i<count($jlypidarr);$i++){
                        \player\addyaopin($sid,$jlypidarr[$i],$jlypslarr[$i],$dblj);
                    }
                    foreach ($jlzbslarr as $jlzbid){
                        \player\addzb($sid,$jlzbid,$dblj);
                    }
                    echo '<div class="task-page"><section class="task-complete"><div class="task-eyebrow">Hoàn thành nhiệm vụ</div><h1>Nhận thưởng thành công</h1><div class="task-section-title">Phần thưởng</div>'.$rwjlhtml.'<div class="task-actions"><a class="task-btn primary" href="?cmd='.$gonowmid.'">Đại Sảnh</a></div></section></div>';
                    exit();
                }

            }
            break;

    }
}

switch ($task->rwzl){
    case 1://收集
        $rwyq = \player\getdaoju($task->rwyq,$dblj);
		
        $rwhtml ="Thu thập $task->rwcount*$rwyq->djname";
        break;
    case 2://打怪
        $gwmid = new \player\clmid();
		
        $rwyq = \player\getyguaiwu($task->rwyq,$dblj);
		
        $rwhtml ="Tiêu diệt $task->rwcount*$rwyq->gname";
		
        break;
    case 3://对话
        $tjnpc = \player\getnpc($task->rwcount,$dblj);
		//$clmid = player\getmid($task->rwqy,$dblj); //获取地图信息，在这里多余
		//$upmidlj = $encode->encode("cmd=gomid&newmid=$task->rwqy&sid=$sid");//地图//获取到 任务 地图，进行跳转。<--<a href='?cmd=$upmidlj'>传送</a>-->
        $rwhtml ="Đi tìm $tjnpc->nname";//显示任务情况，跳转任务区域，区域在sql，renwu里面写rwqy
        break;
    case 4://炼丹宗门任务 - Luyện đan tông môn
        $rwyq = \player\getdaoju($task->rwyq,$dblj);
        if (!$rwyq || !isset($rwyq->djname)) {
            $rwyqyd = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaodan_config.json'), true);
            if (isset($rwyqyd[intval($task->rwyq)])) {
                $rwhtml ="Luyện đan $task->rwcount*".$rwyqyd[intval($task->rwyq)]['ydname']." (đóng góp bảo khố)";
            } else {
                $rwhtml ="Luyện đan $task->rwcount*vật phẩm (đóng góp bảo khố)";
            }
        } else {
            $rwhtml ="Luyện đan $task->rwcount*$rwyq->djname (đóng góp bảo khố)";
        }
        break;
}
$ptask = \player\getplayerrenwuonce($sid,$rwid,$dblj);
$rwzthtml='';
$progressHtml = '';
    if ($ptask){
        if($ptask->rwzl != 3){
            $done = intval($ptask->rwnowcount);
            $need = max(1, intval($ptask->rwcount));
            $percent = min(100, floor($done * 100 / $need));
            $progressHtml = '<div class="task-progress"><div class="task-progress-top"><span>Tiến độ</span><b>'.$done.' / '.$need.'</b></div><div class="task-progress-bar"><i style="width:'.$percent.'%"></i></div></div>';
            $rwzthtml = '<a class="task-btn submit" href="?cmd='.$tijiaorw.'">Gửi nhiệm vụ</a>';
        }elseif($ptask->rwcount == $nid){
            $rwzthtml.= '<a class="task-btn submit" href="?cmd='.$tijiaorw.'">Gửi nhiệm vụ</a>';
        }
    }else{
        $rwzthtml = <<<HTML
        <a class="task-btn accept" href="?cmd=$jieshourw">Tiếp nhận</a>
HTML;
    }

$taskName = tcg_task_h($task->rwname);
$taskInfo = tcg_task_h($task->rwinfo);
$taskTarget = tcg_task_h($rwhtml);
$noticeHtml = trim($tishi) != '' ? '<div class="task-notice">'.tcg_task_h($tishi).'</div>' : '';
$taskhtml = <<<HTML
<div class="task-page">
    <section class="task-hero">
        <div class="task-icon">📋</div>
        <div>
            <div class="task-eyebrow">Nhiệm vụ khả dụng</div>
            <h1>$taskName</h1>
            <p>$taskInfo</p>
        </div>
    </section>

    $noticeHtml

    <section class="task-section">
        <div class="task-section-title">Mục tiêu</div>
        <div class="task-target">$taskTarget</div>
        $progressHtml
    </section>

    <section class="task-section">
        <div class="task-section-title">Phần thưởng nhiệm vụ</div>
        $rwjlhtml
    </section>

    <div class="task-actions">
        $rwzthtml
        <a class="task-btn leave" href="?cmd=$gonowmid">Rời khỏi</a>
    </div>
</div>

HTML;
echo $taskhtml;
?>
