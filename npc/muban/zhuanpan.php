<?php
//新手村村长
$player = player\getplayer($sid,$dblj);
$czyxb = $encode->encode("cmd=npc&nid=$nid&canshu=czyxb&sid=$sid");
$czsl = 0;
$npchtml =<<<HTML
Trò chơi lỗi
 <p><a href="?cmd=$gonowmid">Trượt rồi trượt rồi</a></p>;

HTML;

if ($nid!=''){
	$czsl = 0;
    if (isset($canshu)){
        switch ($canshu){
            case 'czyxb':
                if ($player->uyxb<=20000){//判断执行情况，对于某数值的大小进行比较，sjs=随机数
					$sjs = mt_rand(1,4);//倍数在这里调整，随机mt-
					$czsl = 100+ $player->uyxb*(7-$sjs) / 10 +2000;//计算逻辑，增加数量
					$lszj = $czsl-round($player->uyxb);
					
                    \player\changeyxb(1,$czsl,$sid,$dblj);//选择方案，变化率，id，地址；
                    \player\changeplayersx("uyxb",$czsl,$sid,$dblj);//充值地址，充值变化量，id，地址；
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
					<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
					<!--<link rel="stylesheet" href="./css/choujiang.css">//这里开启为抽奖方框-->
		            <script src="./js/jquery.min.js"></script>
					<link rel="stylesheet" type="text/css" href="./css/main.css">
	<script type="text/javascript" src="./js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="./js/main.js">
                    <br/>$npc->nname : Vận khí không tệ, nhận được【 $lszj 】<br/>
                    
					Linh thạch:【 $player->uyxb 】<br/>
					===Tuyển Thủ===
					<!--<div class="content">
    </div>============
    <div class="button" >
    <button type="button ?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid" href="?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid" class="choujiang" ><font color=#FC0000>Tiếp tục</font></button>
    <!--<a href="?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid">刷新</a>-->
    <a href="javascript:location.reload();">Đổi mới</a>
    </div>
	============
    <script src="./js/choujiang.js" type="text/javascript">-->
	</script>
	<body>
	<div class="turnplate_box">
		<canvas id="myCanvas" width="300px" height="300px">Thật có lỗi! Trình duyệt không ủng hộ.</canvas>
		<canvas id="myCanvas01" width="200px" height="200px">Thật có lỗi! Trình duyệt không ủng hộ.</canvas>
		<canvas id="myCanvas03" width="200px" height="200px">Thật có lỗi! Trình duyệt không ủng hộ.</canvas>
		<canvas id="myCanvas02" width="150px" height="150px">Thật có lỗi! Trình duyệt không ủng hộ.</canvas>
		<button id="tupBtn" class="turnplatw_btn"></button>
	</div>
	<!-- 更改系统默认弹窗 -->
	
</body>
HTML;

                }else{
                    $gnhtml ="<font color=#FC0000>===Tạm dừng kinh doanh===</font>
                    <br/><font color=#46B853>Lão bản ly kỳ mất tích, chúng ta đang nỗ lực tìm...</font><font color=#BED89B>Đừng hốt hoảng, đừng có gấp, mời cùng tin, lão bản nhất định không phải đi đường. . .</font>
                    <br/>Ngày mai lại đến xem. . . Lão bản nhất định <IMG width='30' height='30' src='./images/hui.jpg'> sẽ về.";}
                break;
				
        }
    }else{
        $gnhtml =<<<HTML
		<link rel="stylesheet" href="./css/choujiang.css">
		<link rel="stylesheet" href="./css/dongtai.css">
		<script src="./js/jquery.min.js"></script>
		<!--图片显示<IMG width="100" height="100" src="./npc/gxzc.png">-->
		Linh thạch còn</font>：$player->uyxb<br/>
			  <use href="?cmd=$czyxb">
      <svg viewBox="0 0 900 90">
	  <symbol id="id">
	  <text font-size="66px" dy="1em">Vòng Quay May Mắn</text>
	  </symbol>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	</svg></use>
		<br/>
		<a href="?cmd=$czyxb">
		<font color=#F03930>Vào </font><font color=#F2553C>Khách Điếm</font></a><br/>
HTML;
        }
}
?>