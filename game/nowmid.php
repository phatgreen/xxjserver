<?php
// Bản Đồ
$player = player\getplayer($sid,$dblj);//获取玩家信息
$lastmid = $player->nowmid;

if (isset($newmid)){
    if ($player->nowmid!=$newmid){
        $clmid = player\getmid($newmid,$dblj); //获取即将走的地图信息
	$playerinfo = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8')."Hướng $clmid->mname. Đi đến";
        if ($playerinfo != $clmid->playerinfo){
            $stmt = $dblj->prepare("UPDATE mid SET playerinfo = :playerinfo WHERE mid = :mid");
            $stmt->execute(array(':playerinfo' => $playerinfo, ':mid' => $lastmid));
        }
        if ($player->uhp<=0){
            $retmid = \player\getmid($player->nowmid,$dblj);
            $retqy = \player\getqy($retmid->mqy,$dblj);
            $gonowmid = $encode->encode("cmd=gomid&newmid=$retqy->mid&sid=$sid");
            $govandutieny = $encode->encode("cmd=npc&nid=17&sid=$sid");
            if ($newmid != $retqy->mid){
                exit('<div class="tcg-injured-page">
                    <section class="tcg-injured-card">
                        <div class="tcg-injured-mark">!</div>
                        <div class="tcg-injured-kicker">Khí huyết cạn kiệt</div>
                        <h1>Đạo hữu đang trọng thương</h1>
                        <p>Thân thể chưa ổn định, tạm thời không thể tiếp tục di chuyển. Hãy tìm Vân Du Tiên Y [Trị Liệu] để trị thương trước khi lên đường.</p>
                        <div class="tcg-injured-actions">
                            <a class="tcg-injured-btn" href="?cmd='.$gonowmid.'">Về Đại Sảnh</a>
                            <a class="tcg-injured-btn medicine" href="?cmd='.$govandutieny.'">Vân Du Tiên Y</a>
                        </div>
                    </section>
                </div>');
            }

        }
		//想在这添加怪物纠缠，或者随机事件
		// if ($player->uhp<=0){
            // $retmid = \player\getmid($player->nowmid,$dblj);
            // $retqy = \player\getqy($retmid->mqy,$dblj);
            // $gonowmid = $encode->encode("cmd=gomid&newmid=$retqy->mid&sid=$sid");
            // if ($newmid != $retqy->mid){
                // exit("Gặp được quái vật dây dưa, không thể thoát thân.<br/>".'<a href="?cmd='.$gonowmid.'">Đại Sảnh</a>');
            // }

        // }
        \player\changeplayersx('nowmid',$newmid,$sid,$dblj);
        $player = player\getplayer($sid,$dblj);//获取玩家信息
        // Chính tuyến: đến khu vực mới
        if (function_exists('player\\nhiemvu_main_update')) {
            $nvRv = \player\nhiemvu_main_update($sid, 'reach_region', $dblj, array('region' => intval($clmid->mqy)));
            if (!empty($nvRv['ok'])) $nvMapNotice = $nvRv['msg'];
        }
    }

}

if ($player->nowmid=='' || $player->nowmid==0){//判断角色是否出现在非法地图
    $gameconfig = \player\getgameconfig($dblj);
    $sql = "update game1 set nowmid='$gameconfig->firstmid' WHERE sid='$sid'";
    $dblj->exec($sql);
    $player->nowmid=$gameconfig->firstmid;
}
$clmid = player\getmid($player->nowmid,$dblj); //获取地图信息
// Chính tuyến: nhiệm vụ đột phá tính theo cấp hiện tại (kể cả khi lên cấp không qua trang Đột Phá)
if (!isset($nvMapNotice) && function_exists('player\\nhiemvu_main_update')) {
    $nvRv = \player\nhiemvu_main_update($sid, 'breakthrough', $dblj, array('level' => intval($player->ulv)));
    if (!empty($nvRv['ok'])) $nvMapNotice = $nvRv['msg'];
}
$nvMapNoticeHtml = isset($nvMapNotice) ? '<div class="tcg-nv-notice">📜 ' . htmlspecialchars($nvMapNotice, ENT_QUOTES, 'UTF-8') . '</div>' : '';
// Bế Quan: có tu vi ngoại tuyến chưa nhận thì nhắc
$nvOff = \player\offline_reward_pending($sid, $dblj);
if ($nvOff['exp'] > 0) {
    $nvOffCmd = $encode->encode("cmd=nhiemvu&tab=online&sid=$sid");
    $nvMapNoticeHtml .= '<a class="tcg-nv-notice" style="display:block;text-decoration:none" href="?cmd=' . $nvOffCmd . '">🧘 Bế quan '
        . $nvOff['hours'] . ' giờ — nhận ' . number_format($nvOff['exp']) . ' tu vi ›</a>';
}
if ($clmid->playerinfo != ''){
    $clmid->playerinfo .='<br/>';
}
$pvphtml = '<span class="tcg-badge safe">【 Khu vực an toàn 】</span>';
if ($clmid->ispvp){
    $pvphtml = '<span class="tcg-badge pvp">【 PVP 】</span>';
}

$hpPercent = 0;
if (intval($player->umaxhp) > 0) {
    $hpPercent = min(100, max(0, intval(round($player->uhp * 100 / $player->umaxhp))));
}

$expPercent = 0;
if (intval($player->umaxexp) > 0) {
    $expPercent = min(100, max(0, intval(round($player->uexp * 100 / $player->umaxexp))));
}

$energyValue = isset($player->ustamina) ? intval($player->ustamina) : 100;
$energyMax = isset($player->umaxstamina) ? intval($player->umaxstamina) : 100;
$energyPercent = $energyMax > 0 ? min(100, max(0, intval(round($energyValue * 100 / $energyMax)))) : 0;

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$trangbicmd = $encode->encode("cmd=trangbi&sid=$sid");
$goliaotian = $encode->encode("cmd=liaotian&ltlx=all&sid=$sid");
$gonowmid = $encode->encode("cmd=gomid&newmid=$clmid->mid&sid=$sid");
$phcmd = $encode->encode("cmd=paihang&sid=$sid");
$getbagcmd = $encode->encode("cmd=getbagzb&sid=$sid");
$cwcmd = $encode->encode("cmd=chongwu&sid=$sid");
$cxall = '';

$upmidlj = $encode->encode("cmd=gomid&newmid=$clmid->upmid&sid=$sid");//上地图
$downmidlj = $encode->encode("cmd=gomid&newmid=$clmid->downmid&sid=$sid");
$leftmidlj = $encode->encode("cmd=gomid&newmid=$clmid->leftmid&sid=$sid");
$rightmidlj = $encode->encode("cmd=gomid&newmid=$clmid->rightmid&sid=$sid");
$upmid = player\getmid($clmid->upmid,$dblj);
$downmid = player\getmid($clmid->downmid,$dblj);
$leftmid = player\getmid($clmid->leftmid,$dblj);
$rightmid = player\getmid($clmid->rightmid,$dblj);

$lukouhtml ='';
$bosshtml = '';//boss Địa chỉ dẫn vào
if ($clmid->midinfo == ''){
    $clmid->midinfo = $clmid->mname;
}
$clmid = player\getmid($player->nowmid,$dblj); // Lấy lại đúng bản đồ hiện tại sau khi di chuyển
$boss = player\getboss($clmid->midboss,$dblj);
$bossxl = $boss->bosshp;
$second=floor((strtotime($nowdate) - strtotime($clmid->mgtime))%86400);
if ($bossxl<0 && $clmid->midboss != 0 && $second > $clmid->ms){
	// $sql = "update mid set mgtime='$nowdate' WHERE mid='$player->nowmid'";//增加时间，计数
    // $dblj->exec($sql);
	$sql = "update boss set bosshp = bossmaxhp  WHERE bossid='$clmid->midboss'";//给boss加血,上面判断刷新时间
	$dblj->exec($sql);
    $boss = \player\getboss($clmid->midboss,$dblj);
    $bossinfo = $encode->encode("cmd=boss&bossid=$boss->bossid&sid=$sid");
    $bossNameSafe = htmlspecialchars($boss->bossname, ENT_QUOTES, 'UTF-8');
    $bosshtml = <<<HTML
<a class="tcg-pill boss-pill" href="?cmd=$bossinfo">$bossNameSafe</a>
HTML;
}
    if($bossxl>0 && $clmid->midboss != 0){
    $boss = \player\getboss($clmid->midboss,$dblj);
    $bossinfo = $encode->encode("cmd=boss&bossid=$boss->bossid&sid=$sid");
    $bossNameSafe2 = htmlspecialchars($boss->bossname, ENT_QUOTES, 'UTF-8');
    $bosshtml = <<<HTML
<a class="tcg-pill boss-pill" href="?cmd=$bossinfo">$bossNameSafe2</a>
HTML;
    }

if ($upmid->mname != ''){
    $lukouhtml .= <<<HTML
<div class="tcg-dir-row"><div class="tcg-dir-label north"><span class="tcg-dir-icon">✦</span>Bắc:</div><a class="tcg-dir-link" href="?cmd=$upmidlj">$upmid->mname ↑</a></div>
HTML;
}

if ($leftmid->mname!=''){
    $lukouhtml .= <<<HTML
<div class="tcg-dir-row"><div class="tcg-dir-label west"><span class="tcg-dir-icon">✦</span>Tây:</div><a class="tcg-dir-link" href="?cmd=$leftmidlj">$leftmid->mname ←</a></div>
HTML;
}

if ($rightmid->mname!=''){
    $lukouhtml .= <<<HTML
<div class="tcg-dir-row"><div class="tcg-dir-label east"><span class="tcg-dir-icon">✦</span>Đông:</div><a class="tcg-dir-link" href="?cmd=$rightmidlj">$rightmid->mname →</a></div>
HTML;
}

if ($downmid->mname!=''){
    $lukouhtml .= <<<HTML
<div class="tcg-dir-row"><div class="tcg-dir-label south"><span class="tcg-dir-icon">✦</span>Nam:</div><a class="tcg-dir-link" href="?cmd=$downmidlj">$downmid->mname ↓</a></div>
HTML;
}

$sql = "select * from midguaiwu where mid='$player->nowmid' AND sid = ''";//获取当前地图怪物
$cxjg = $dblj->query($sql);
$cxallguaiwu = $cxjg->rowCount();
$nowdate = date('Y-m-d H:i:s');
$second=floor((strtotime($nowdate)-strtotime($clmid->mgtime))%86400);//获取刷新间隔86400
if ($second > $clmid->ms  && $cxallguaiwu<= 3 && $clmid->mgid!=''){//刷新怪物,剩余多少怪物刷新，可以在修改修改，改成怪物死亡啥在刷新啥
    $sql = "update mid set mgtime='$nowdate' WHERE mid='$player->nowmid'";
    $dblj->exec($sql);
    $retgw = explode(",",$clmid->mgid);
    foreach ($retgw as $itemgw){
        $gwinfo = explode("|",$itemgw);
        $guaiwu = \player\getyguaiwu($gwinfo[0],$dblj);
        $guaiwu->gyid = $gwinfo[0];
        $sjexp = mt_rand(6,8) + 100;//原有加成0.5，我改成100..
        $guaiwu->gexp = round($guaiwu->glv * $sjexp,0);
        for ($n=0;$n<$gwinfo[1];$n++){
            $sql = "insert into midguaiwu(mid,gname,glv,ghp,ggj,gfy,gbj,gxx,gexp,gyid,gmaxhp) 
                    values('$player->nowmid',
                    '$guaiwu->gname',
                    '$guaiwu->glv',
                    '$guaiwu->ghp',
                    '$guaiwu->ggj',
                    '$guaiwu->gfy',
                    '$guaiwu->gbj',
                    '$guaiwu->gxx',
                    '$guaiwu->gexp',
                    '$guaiwu->gyid',
                    '$guaiwu->ghp')";
            $cxjg = $dblj->exec($sql);
        }

    }
}
//倒计时，已经抛弃
// if ($clmid->ms == 600){
// $djshtml = <<<HTML
// <head>
// <script type="text/javascript" src="./chajian/djs/js/jquery.js"></script>
// <script type="text/javascript" src="./chajian/djs/js/time_js.js"></script>
// <link type="text/css" rel="stylesheet" href="./chajian/djs/css/time_css.css" />
// </head>
// <body>	
// <div class="time"></div>
// <script type="text/javascript">
// countDown();
// </script>
// </body>

// HTML;
// }else{
        // ;
    // }
	
	// if ($clmid->ms > 0){
        // $djshtml;
    // }else{
        // $djshtml ;
    // }
//上面是倒计时的一段




$sql = "select * from midguaiwu where mid='$player->nowmid' AND sid = ''";//获取当前地图怪物
$cxjg = $dblj->query($sql);
$cxallguaiwu = $cxjg->fetchAll(PDO::FETCH_ASSOC);
$gwhtml = '';
for ($i = 0;$i<count($cxallguaiwu);$i++){
    $gwcmd = $encode->encode("cmd=getginfo&gid=".$cxallguaiwu[$i]['id']."&gyid=".$cxallguaiwu[$i]['gyid']."&sid=$sid&nowmid=$player->nowmid");
    $gwhtml .="<a class='tcg-pill monster-pill' href='?cmd=$gwcmd'>".$cxallguaiwu[$i]['gname']."</a>";
}

$sql = "select * from game1 where nowmid='$player->nowmid' AND sfzx = 1";//获取当前地图玩家
$cxjg = $dblj->query($sql);
$playerhtml = '';
if ($cxjg){
    $cxallplayer = $cxjg->fetchAll(PDO::FETCH_ASSOC);
    $nowdate = date('Y-m-d H:i:s');
    for ($i = 0;$i<count($cxallplayer);$i++){
        if ($cxallplayer[$i]['uname']!=""){
            $cxtime = $cxallplayer[$i]['endtime'];
            $cxuid = $cxallplayer[$i]['uid'];
            $cxsid = $cxallplayer[$i]['sid'];
            $cxuname = htmlspecialchars($cxallplayer[$i]['uname'], ENT_QUOTES, 'UTF-8');
            $cxuname = htmlspecialchars($cxallplayer[$i]['uname'], ENT_QUOTES, 'UTF-8');
            $second=floor((strtotime($nowdate)-strtotime($cxtime))%86400);//获取刷新间隔
            if ($second > 3000){
                $sql = "update game1 set sfzx=0 WHERE sid='$cxsid'";
                $dblj->exec($sql);
            }else{
                $clubp = \player\getclubplayer_once($cxsid,$dblj);
                if ($clubp){
                    $club = \player\getclub($clubp->clubid,$dblj);
                    $club->clubname ="[".htmlspecialchars($club->clubname, ENT_QUOTES, 'UTF-8')."]";
                }else{
                    $club = new \player\club();
                    $club->clubname ="";
                }
                $playercmd = $encode->encode("cmd=getplayerinfo&uid=$cxuid&sid=$sid");
                $playerhtml .="<a class='tcg-pill player-pill' href='?cmd=$playercmd'>{$club->clubname}$cxuname</a>";
            }

        }
    }
}


$npchtml='';
$task = \player\getplayerrenwu($sid,$dblj);//玩家任务数组

$taskcount = 0;
for ($n=0;$n<count($task);$n++){
    if (isset($task[$n]['rwlx']) && intval($task[$n]['rwlx']) == 2 && $task[$n]['data'] != date('Y-m-d')){
        $rwid = intval($task[$n]['rwid']);
        $stmt = $dblj->prepare("DELETE FROM playerrenwu WHERE rwid = :rwid AND sid = :sid");
        $stmt->execute(array(':rwid' => $rwid, ':sid' => $sid));
        continue;
    }

    if (!isset($task[$n]['rwzt']) || intval($task[$n]['rwzt']) != 3) {
        $taskcount++;
    }
}

if ($clmid->mnpc != ""){
    // NPC đã chuyển sang JSON nên không truy vấn bảng npc trong MySQL nữa.
    // Lấy danh sách NPC theo các ID được lưu trong cột mid.mnpc.
    $cxnpcall = array();
    $npcIds = explode(',', $clmid->mnpc);

    for ($n = 0; $n < count($npcIds); $n++){
        $npcId = intval(trim($npcIds[$n]));
        if ($npcId <= 0){
            continue;
        }

        $npc = \player\getnpc($npcId);
        if (!$npc){
            continue;
        }

        $cxnpcall[] = array(
            'id'     => isset($npc->id) ? $npc->id : $npcId,
            'nname'  => isset($npc->nname) ? $npc->nname : '',
            'taskid' => isset($npc->taskid) ? $npc->taskid : '',
            'nsex'   => isset($npc->nsex) ? $npc->nsex : '',
            'ninfo'  => isset($npc->ninfo) ? $npc->ninfo : '',
            'muban'  => isset($npc->muban) ? $npc->muban : '',
            'mz'     => isset($npc->mz) ? $npc->mz : '',
            'rwqy'   => isset($npc->rwqy) ? $npc->rwqy : ''
        );
    }

    for ($i=0;$i < count($cxnpcall);$i++){
        $nname = $cxnpcall[$i]['nname'];
        $nid = $cxnpcall[$i]['id'];
        $taskid = $cxnpcall[$i]['taskid'];
        $taskarr = explode(',',$taskid);
        $yrw = false;
        $npcMarkStart = strlen($npchtml); // dấu ?/! của NPC này — gộp thành 1 biểu tượng trong nút
        if ($taskid!=''){
            for ($l=0;$l<count($taskarr);$l++){
                $nowrw = \player\gettask($taskarr[$l],$dblj);
                $rwret = \player\getplayerrenwuonce($sid,$taskarr[$l],$dblj);
                $lastrwid = $nowrw->lastrwid;

                if ($nowrw->rwlx == 1 || $nowrw->rwlx == 2){
                    if (!$rwret){
                        if ($nowrw->rwzl != 3){
                            $npchtml .='<img src="images/wen.gif" />';
                        }elseif($nowrw->rwyq == $nid){
                            $npchtml .='<img src="images/wen.gif" />';
                        }else{
                            continue;
                        }
                    }elseif ($rwret->rwzt==2){
                        if ($nowrw->rwzl != 3){
                            $npchtml .='<img src="images/tan.gif" />';
                        }elseif ($nowrw->rwcount == $nid){
                            $npchtml .='<img src="images/tan.gif" />';
                        }else{
                            continue;
                        }

                    }
                }
                if ($nowrw->rwlx == 3){
                    if ($rwret){
                        if ($rwret->rwzt==2){
                            if ($nowrw->rwzl != 3){
                                $npchtml .='<img src="images/tan.gif" />';
                            }elseif ($nowrw->rwcount == $nid){
                                $npchtml .='<img src="images/tan.gif" />';
                            }else{
                                continue;
                            }
                        }
                    }else{
                        if ($lastrwid<=0 ){
                            if ($nowrw->rwzl != 3){
                                $npchtml .='<img src="images/wen.gif" />';
                            }elseif ($nowrw->rwyq == $nid){
                                $npchtml .='<img src="images/wen.gif" />';
                            }else{
                                continue;
                            }
                        }else{
                            $rwret = \player\getplayerrenwuonce($sid,$lastrwid,$dblj);
                            if ($rwret){
                                if ($rwret->rwzt==3){
                                    if ($nowrw->rwzl != 3){
                                        $npchtml .='<img src="images/wen.gif" />';
                                    }elseif ($nowrw->rwyq == $nid){
                                        $npchtml .='<img src="images/wen.gif" />';
                                    }else{
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $npccmd = $encode->encode("cmd=npc&nid=$nid&sid=$player->sid");
        $npcMarks = substr($npchtml, $npcMarkStart);
        $npchtml = substr($npchtml, 0, $npcMarkStart);
        $npcMarkHtml = strpos($npcMarks, 'tan.gif') !== false ? '<img src="images/tan.gif" alt="!"> '
            : (strpos($npcMarks, 'wen.gif') !== false ? '<img src="images/wen.gif" alt="?"> ' : '');
		
        $npchtml .= <<<HTML
      
<a class="tcg-pill npc-pill" href="?cmd=$npccmd">{$npcMarkHtml}{$nname}</a>
HTML;
    }//这个是$npchtml.....任务NPC的问号提示...
}

$texiao = <<<HTML

<IMG width='15' height='15' src='./images/texiao.png'>

HTML;


$sql = 'SELECT * FROM ggliaotian ORDER BY id DESC LIMIT 2';//聊天列表获取<!--xiamian下面为公共聊天前称【公共】，我已经取消--!>
$ltcxjg = $dblj->query($sql);
$lthtml='';
if ($ltcxjg){
    $ret = $ltcxjg->fetchAll(PDO::FETCH_ASSOC);
    for ($i=0;$i < count($ret);$i++){
        $uname = $ret[count($ret) - $i-1]['name'];
        $umsg = $ret[count($ret) - $i-1]['msg'];
        $uid = $ret[count($ret) - $i-1]['uid'];
        $ucmd = $encode->encode("cmd=getplayerinfo&uid=$uid&sid=$player->sid");
        if ($uid){
            // Tên người chơi trong chat là link xem thông tin, nhưng hiển thị cùng style với .diamond.
            $chatName = htmlspecialchars($uname, ENT_QUOTES, 'UTF-8');
            $umsgSafe = tcg_chat_format_msg($umsg, false);
            $lthtml .= "<div class='tcg-chat-line'><a class='diamond chat-player-link' href='?cmd=$ucmd'><span class='sys-icon'>$texiao</span>【".$chatName."】:</a> $umsgSafe</div>";
        }else{
            // Tin hệ thống/không có uid: giữ màu tên nhưng căn theo cùng cấu trúc dòng chat.
            $chatName = htmlspecialchars($uname, ENT_QUOTES, 'UTF-8');
			
            //$lthtml .= "<div class='tcg-chat-line'>$texiao<span class='diamond'>💎 $chatName :</span> $umsg</div>";
			$umsgSafe = tcg_chat_format_msg($umsg, true);
			$lthtml .= "<div><span class='system'><span class='sys-icon'>📣</span> 【Hệ thống】:</span> $umsgSafe</div>";
        }

    }
}
//新的倒计时，配合BOSS攻击计算时间
$second=floor((strtotime($nowdate)-strtotime($clmid->mgtime))%86400);//获取刷新间隔86400
$sj = $clmid->ms - $second ;
$sx = $sj+1;
    if ($sj < 0){
        $sj = 0;
	}
    if ($sj>0 && $bossxl<0){  
$djs =<<<HTML
<html>
<body>
<div align="center">
<span class="STYLE7" id="clock" style="color: #f50e0e;">$sj</span>
<strong><span class="STYLE8"> giây sau đổi mới BOSS</span>
<script type="text/javascript">
var oclock=document.getElementById("clock");
var start1 = oclock.innerHTML;
var finish = "0";
var timer = null;
run();
function run() {//定义时间函数，让秒表每100ms变化一次
timer =setInterval("onTimer()", 1000);//100ms的定时器
}
function onTimer()
{
if (start1 == finish)//如果倒计时结束清除时间函数
{
clearInterval(timer);
start1="1";//(清除时间函数后还是会执行一次 所以多给一个10ms再动态赋值)
}
start1 -= 1;//每次执行ms减10
oclock.innerHTML = start1;//重新给oclock赋值
}
</script>
</strong>
<meta http-equiv="refresh" content="$sx">
</div>
</body>
</html>
HTML;
}
	




$mapcmd = $encode->encode("cmd=allmap&sid=$sid");
$xiuliancmd = $encode->encode("cmd=goxiulian&sid=$sid");
$mytask = $encode->encode("cmd=mytask&sid=$sid");
$getbagjncmd = $encode->encode("cmd=getbagjn&sid=$sid");
$fangshi = $encode->encode("cmd=fangshi&fangshi=daoju&sid=$sid");
$shangdian = $encode->encode("cmd=shangdian&canshu=gogoumai&sid=$sid");
$clubcmd = $encode->encode("cmd=club&sid=$sid");
$toayeuthapcmd = $encode->encode("cmd=toayeuthap&sid=$sid");
$vandinhdaicmd = $encode->encode("cmd=vandinhdai&sid=$sid");
$hammocmd = $encode->encode("cmd=hammo&sid=$sid");
$phobancmd = $encode->encode("cmd=phoban&sid=$sid");
$thiencoccaccmd = $encode->encode("cmd=thiencoccac&tab=wg&sid=$sid");

// Lấy tên môn phái của người chơi hiện tại để hiển thị thay cho tên hardcode.
$myclubname = 'Tán Tu';
$myclubplayer = \player\getclubplayer_once($sid,$dblj);
if ($myclubplayer){
    $myclubinfo = \player\getclub($myclubplayer->clubid,$dblj);
    if ($myclubinfo && isset($myclubinfo->clubname) && $myclubinfo->clubname != ''){
        $myclubname = $myclubinfo->clubname;
    }
}
$myclubnamehtml = htmlspecialchars($myclubname, ENT_QUOTES, 'UTF-8');
$myunamehtml = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');

$wgxlcmd = $encode->encode("cmd=wgxl&sid=$sid");
$duihuancmd = $encode->encode("cmd=duihuan&sid=$sid");
$quydoicmd = $encode->encode("cmd=quydoi&sid=$sid");
$imcmd = $encode->encode("cmd=im&sid=$sid");
$lb = $encode->encode("cmd=getbagyd&sid=$sid");
$qydt = $encode->encode("cmd=qydt&sid=$sid");
$luondenCmd = $encode->encode("cmd=luonden&sid=$sid");
$chetaoCmd = $encode->encode("cmd=chetao&sid=$sid");
$gwhtmlView = trim($gwhtml) != '' ? $gwhtml : '<div class="tcg-pill">Không có quái vật</div>';
$bosshtmlView = trim($bosshtml) != '' ? $bosshtml : '';
$npcView = trim($npchtml) != '' ? $npchtml : '<div class="tcg-pill">Không có NPC</div>';
// Hiện NPC của bản đồ ngay ở Đại Sảnh (map không có NPC thì không hiện khối này)
$npcBlockView = trim($npchtml) != '' ? '<div class="tcg-npc-row"><span class="tcg-npc-label">NPC</span>' . $npchtml . '</div>' : '';
$playerView = trim($playerhtml) != '' ? $playerhtml : '<div class="tcg-pill">Chưa có người chơi khác</div>';
$newsLine = trim($clmid->midinfo.' '.$clmid->playerinfo);
if ($newsLine == '') {
    $newsLine = 'Chào mừng '.htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8').' bước lên tiên đồ';
}
$djsView = '';
if (isset($djs)) {
    $djsView = $djs;
}
if (isset($djshtml)) {
    $djsView .= $djshtml;
}

$lukouhtmlView = trim($lukouhtml) != '' 
    ? $lukouhtml 
    : '<div class="tcg-dir-empty"><center>Không có lối đi từ khu vực này</center></div>';

// Nhãn 🔒 cho nút dẫn tới tính năng chưa mở theo cấp (nút vẫn hiện đủ) — class/config/feature_unlock.json
$lk_wgxl = \player\feature_lock_mark($player, 'wgxl');
$lk_phoban = \player\feature_lock_mark($player, 'phoban');
$lk_thiencoccac = \player\feature_lock_mark($player, 'thiencoccac');
$lk_luonden = \player\feature_lock_mark($player, 'luonden');
$lk_chetao = \player\feature_lock_mark($player, 'chetao');
$lk_liaotian = \player\feature_lock_mark($player, 'liaotian');
$lk_chongwu = \player\feature_lock_mark($player, 'chongwu');
$lk_paihang = \player\feature_lock_mark($player, 'paihang');
$lk_fangshi = \player\feature_lock_mark($player, 'fangshi');
$lk_club = \player\feature_lock_mark($player, 'club');
$lk_im = \player\feature_lock_mark($player, 'im');
$lk_quydoi = \player\feature_lock_mark($player, 'quydoi');
$lk_shangdian = \player\feature_lock_mark($player, 'shangdian');
$nowhtml = <<<HTML
<div class="tcg-title">$clmid->mname</div>
<div class="tcg-badges">
    <!-- <span class="tcg-badge server">【 $clmid->mname 】</span> !-->
    $pvphtml
</div>
$nvMapNoticeHtml

<div class="tcg-marquee">
    <marquee behavior="scroll"
             direction="left"
             scrollamount="3"
             onmouseover="this.stop();"
             onmouseout="this.start();">

        📣 $newsLine

    </marquee>
</div>

<div class="tcg-status-panel">
    <div class="tcg-profile-bars">
        <div class="tcg-profile-bar-card">
            <div class="tcg-profile-bar-head"><span>❤️ Khí huyết</span><b>$player->uhp / $player->umaxhp</b></div>
            <div class="tcg-profile-bar"><i style="width:{$hpPercent}%"></i></div>
        </div>
        <div class="tcg-profile-bar-card">
            <div class="tcg-profile-bar-head"><span>⚡ Thể lực</span><b>$energyValue / $energyMax</b></div>
            <div class="tcg-profile-bar energy"><i style="width:{$energyPercent}%"></i></div>
        </div>
        <div class="tcg-profile-bar-card full-width">
            <div class="tcg-profile-bar-head"><span>✨ Tu vi</span><b>$player->uexp / $player->umaxexp</b></div>
            <div class="tcg-profile-bar exp"><i style="width:{$expPercent}%"></i></div>
        </div>
    </div>
</div>

<div class="tcg-section">
    <div class="tcg-entity-grid">
        $gwhtmlView
        $bosshtmlView
    </div>
    $npcBlockView
    $djsView
</div>

<div class="tcg-section tcg-directions">
    $lukouhtmlView
</div>

<div class="tcg-character">
    <a class="tcg-char-badge" href="?cmd=$clubcmd">【 $myclubnamehtml 】 $myunamehtml</a>
</div>

<div class="tcg-section tcg-chat">
    <div><span class="system"><span class="sys-icon">📣</span> 【Hệ thống】:</span> $newsLine</div>
    $lthtml
</div>

<div class="tcg-actions">
    <a class="tcg-action cyan" href="?cmd=$xiuliancmd">🧘 Tu Luyện</a>
    <a class="tcg-action gold{$lk_wgxl[0]}" href="?cmd=$wgxlcmd">⚔ Võ Công{$lk_wgxl[1]}</a>
    <a class="tcg-action green{$lk_phoban[0]}" href="?cmd=$phobancmd">📋 Phó Bản{$lk_phoban[1]}</a>
	<!-- <a class="tcg-action green" href="?cmd=$mapcmd">🌐 Thế Giới</a> -->
    <a class="tcg-action purple" href="?cmd=$mytask">📜 Nhiệm Vụ ($taskcount)</a>
    <a class="tcg-action red" href="?cmd=$qydt">🗺 Bản Đồ</a>
    <a class="tcg-action green{$lk_thiencoccac[0]}" href="?cmd=$thiencoccaccmd">📚 Thiên Cơ Các{$lk_thiencoccac[1]}</a>
</div>

<div class="tcg-divider"><span>◇</span></div>

<div class="tcg-menu-grid">
    <a class="tcg-menu-item" href="?cmd=$ztcmd">ⓘ Thông Tin</a>
    <a class="tcg-menu-item" href="?cmd=$trangbicmd">⚔️ Trang Bị</a>
    <a class="tcg-menu-item" href="?cmd=$getbagcmd">🎒 Túi Đồ</a>
    <a class="tcg-menu-item{$lk_luonden[0]}" href="?cmd=$luondenCmd">🔥 Luyện Đan{$lk_luonden[1]}</a>
    <a class="tcg-menu-item{$lk_chetao[0]}" href="?cmd=$chetaoCmd">⚒ Chế Tạo{$lk_chetao[1]}</a>
    <a class="tcg-menu-item{$lk_liaotian[0]}" href="?cmd=$goliaotian">💬 Tán Gẫu{$lk_liaotian[1]}</a>
    <a class="tcg-menu-item{$lk_chongwu[0]}" href="?cmd=$cwcmd">🐾 Thú Cưng{$lk_chongwu[1]}</a>
    <a class="tcg-menu-item{$lk_paihang[0]}" href="?cmd=$phcmd">🏆 Xếp Hạng{$lk_paihang[1]}</a>
    <a class="tcg-menu-item{$lk_fangshi[0]}" href="?cmd=$fangshi">🤝 Giao Dịch{$lk_fangshi[1]}</a>
    <a class="tcg-menu-item{$lk_club[0]}" href="?cmd=$clubcmd">⛩ Môn Phái{$lk_club[1]}</a>
    <a class="tcg-menu-item{$lk_im[0]}" href="?cmd=$imcmd">👥 Hảo Hữu{$lk_im[1]}</a>
    <a class="tcg-menu-item" href="?cmd=$duihuancmd">🎁 Giftcode</a>
    <a class="tcg-menu-item{$lk_quydoi[0]}" href="?cmd=$quydoicmd">💱 Quy Đổi{$lk_quydoi[1]}</a>
    <a class="tcg-menu-item{$lk_shangdian[0]}" href="?cmd=$shangdian">🛒 Cửa Hàng{$lk_shangdian[1]}</a>
    <a class="tcg-menu-item" href="index.php">↪ Rời Khỏi</a>
</div>
HTML;
echo $nowhtml;
?>
<!--<font color="#FF2800">铺</font><font color="#FF5000">子</font>--!>
<!--<a href="http://playdreamer.cn/alipay/?id=9&user_id=$player->uid" target="_blank">充值</a>--!>
