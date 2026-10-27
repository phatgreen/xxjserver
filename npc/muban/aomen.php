<?php
//新手村村长
$player = player\getplayer($sid,$dblj);
$aomen = $encode->encode("cmd=npc&nid=$nid&canshu=aomen&sid=$sid");
$czyxb = $encode->encode("cmd=npc&nid=$nid&canshu=czyxb&sid=$sid");//访问$czyxb.html这个段的网页代码，
$xiaohao = round($player->ulv*20);
$czsl = 0;
$ii = mt_rand(1,9);
//$iii = mt_rand($a1,$a9);
$a1=1;$a2=2;$a3=3;$a4=4;$a5=5;$a6=6;$a7=7;$a8=8;$a9=9;
$npchtml =<<<HTML
Trò chơi lỗi
 <p><a href="?cmd=$gonowmid">Trượt rồi trượt rồi</a></p>;
HTML;
$xiaohao = round($player->ulv*20);
if ($nid!=''){
    if (isset($canshu)){
        switch ($canshu){
            case 'aomen':
                if ($player->uyxb>=100){
                    \player\changeyxb(2,$xiaohao,$sid,$dblj);
                    \player\changeplayersx('uhp',$player->umaxhp,$sid,$dblj);
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
                    <br/>$npc->nname:Thiếu hiệp, nhìn xem ngươi hôm nay tướng mạo hồng thụy, tất có cao trung!<br/>
                    Linh thạch: $player->uyxb<br/>
					Hiện tại vận khí: $ii <br>
					<br/><a href="?cmd=$czyxb">Cần [$xiaohao] Linh thạch tiến vào</a><br/>
HTML;
                }else{
                    $gnhtml ="<br/>
				<font color=#e8690e>=={$player->uyxb}==Còn nghĩ đến cưa gái?</font><br><FONT style='TEXT-DECORATION: line-through' color=#18dc72>Ngươi có thể giống như một con cóc so với một con ếch, ngươi nghĩ đến cái đẹp nhưng ngươi vẫn đùa giỡn với nó! !</FONT>
					<br/>";
                }
                break;
        }
	}
}
HTML;
if ($nid!=''){
	$czsl = '';
    if (isset($canshu)){
        switch ($canshu){
            case 'czyxb':
                if ($player->uyxb<=20000){//判断执行情况，对于某数值的大小进行比较，sjs=随机数
					$sjs = mt_rand(1,4);//倍数在这里调整，随机mt-
					//$czsl = 100+ $player->uyxb*(7-$sjs) / 10 +2000;//计算逻辑，增加数量
					$czsl = 0 + 1000 * ($ii-4) ;
					$lszj = $czsl - ($player->uyxb);
                    \player\changeyxb(1,$czsl,$sid,$dblj);//选择方案，变化率，id，地址；
                    \player\changeyxb("uyxb",$czsl,$sid,$dblj);//充值地址，充值变化量，id，地址；
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
					<!--<link rel="stylesheet" href="./css/choujiang.css">//这里开启为抽奖方框-->
					
		            <script src="./js/jquery.min.js"></script>
					<script type="text/javascript">function delayURL(url,time){setTimeout("top.location.href ='?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid'",time);}</script>

                    <br/>$npc->nname : Vận khí không tệ, nhận được【 $czsl 】<br/>
					Hiện tại linh thạch: 【 $player->uyxb 】<br/>
					===Úc môn tuyển thủ===
					
<div class="content"></div>

	
	============
	<div class="button">
	<button type="button"  class="choujiang" onClick="delayURL('javascript:location.reload();',4500)">
	<font color=#FC0000 http-equiv="refresh" content="5">Tiếp tục</font>
	</button>
	
	</div>
	============
	<script src="./js/choujiang.js" type="text/javascript"></script>
HTML;

                }else{
                    $gnhtml ="<font color=#FC0000>===Tạm dừng kinh doanh===</font>
                    <br/><font color=#46B853>Lão bản mất tích ly kỳ, chúng ta đang nỗ lực tìm...</font><font color=#BED89B>Đừng hốt hoảng, đừng có gấp, mời cùng tin, lão bản nhất định không phải đi đường. . .</font>
                    <br/>Ngày mai lại đến xem. . . Lão bản nhất định <IMG width='30' height='30' src='./images/hui.jpg'>trở về.";}
                break;
				
        }
    }
    else{
        $gnhtml =<<<HTML
		<link rel="stylesheet" href="./css/choujiang.css">
		<link rel="stylesheet" href="./css/dongtai.css">
		<script src="./js/jquery.min.js"></script>
		<!--图片显示<IMG width="100" height="100" src="./npc/gxzc.png">-->
		Linh thạch còn</font>：$player->uyxb<br/>
		Phí vào cửa: $xiaohao<br>
		
	<use href="?cmd=$czyxb">
         <svg viewBox="0 0 900 90">
	     <symbol id="id">
	     <text dy="1em" font-size="88px">Khách Điếm</text>
	     </symbol>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	</svg></use>
	
		<br/>
		<a href="?cmd=$aomen">
		<font color=#F03930>Vào </font><font color=#F2553C>Khách Điếm</font></a><br/>
HTML;
        }
}
?>