<?php
//新手村村长
$player = player\getplayer($sid,$dblj);
$czyxb = $encode->encode("cmd=npc&nid=$nid&canshu=czyxb&sid=$sid");
$czsl = 0;
$npchtml =<<<HTML
游戏错误
 <p><a href="?cmd=$gonowmid">溜啦溜啦</a></p>;

HTML;

if ($nid!=''){
	$czsl = 0;
    if (isset($canshu)){
        switch ($canshu){
            case 'czyxb':
                if ($player->uyxb<=20000){//判断执行情况，对于某数值的大小进行比较，sjs=随机数
					$sjs = mt_rand(1,4);//倍数在这里调整，随机mt-
					$czsl = 1+ $player->uyxb*$sjs;//计算逻辑，增加数量
					$lszj = $czsl-round($player->uyxb);
					
                    \player\changeyxb(1,$czsl,$sid,$dblj);//选择方案，变化率，id，地址；
                    \player\changeplayersx("uyxb",$czsl,$sid,$dblj);//充值地址，充值变化量，id，地址；
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
					<!--<link rel="stylesheet" href="./css/choujiang.css">//这里开启为抽奖方框-->
		            <script src="./js/jquery.min.js"></script>
                    <br/>$npc->nname ：运气不错,获得【 $lszj 】<br/>
                    
					当前灵石：【 $player->uyxb 】<br/>
					===澳门选手===
					<div class="content">
    </div>============
    <div class="button">
    <button type="button" class="choujiang"><font color=#FC0000>Tiếp tục</font></button>
    </div>
	============
    <script src="./js/choujiang.js" type="text/javascript">
	</script>
HTML;

                }else{
                    $gnhtml ="<font color=#FC0000>你</font><font color=#F90000>比</font><font color=#F60000>我</font><font color=#F30000>有</font><font color=#F00000>钱</font><font color=#ED0000>，</font><font color=#EA0000>我</font><font color=#E70000>给</font><font color=#E40000>不</font><font color=#E10000>了</font><font color=#DE0000>你</font><font color=#DB0000>钱</font><font color=#D80000>！</font><br/><font color=#46B853>给你个建议：打不过</font><font color=#55BC5C>别人的时候</font><font color=#64C065></font><font color=#73C46E>！就</font><font color=#82C877>赶紧</font><font color=#91CC80>的</font><font color=#A0D089>逃</font><font color=#AFD492>跑</font><font color=#BED89B>嘛！！</font><br/>等你囊中羞涩再来看看吧";}
                break;
				
        }
    }else{
        $gnhtml =<<<HTML
		<link rel="stylesheet" href="./css/choujiang.css">
		<script src="./js/jquery.min.js"></script>
		<IMG width="100" height="100" src="./npc/gxzc.png">
        <br/>
		Linh thạch còn</font>：$player->uyxb<br/>
		<a href="?cmd=$czyxb">
		<font color=#F03930>澳门</font><font color=#F2553C>企业</font></a><br/>
HTML;
        }
}
?>