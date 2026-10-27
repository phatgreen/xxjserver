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
					<!--<link rel="stylesheet" href="./css/choujiang.css">//这里开启为抽奖方框-->
		            <script src="./js/jquery.min.js"></script>
	<link rel="stylesheet" href="./chajian/tanchukuang/css/reset.css">
    <link rel="stylesheet" href="./chajian/tanchukuang/css/style.css">
    <script src="./chajian/tanchukuang/js/modernizr.js"></script>
	<script type="text/javascript">
 function delayURL(url,time){
 setTimeout("top.location.href = '" + url + "'",time);
 }
</script>
                    <br/>
					<font color='#00E000'>
					$npc->nname :</font><br>Thôn trường lão bà, cấp 【<font color='#FFA000'>{$lszj}</font>】<br/>
					Đây là ngươi linh thạch 【 $player->uyxb 】<br/>
	===Tuyển Thủ===
	
	<div class="content">
    </div>============
	<div class="button">
    <IMG width='30' height='30' src='./npc/aomenxuanshou.png'>
	<button type="button"  class="choujiang" onClick="delayURL('javascript:location.reload();',4500)">
	<font color="#FC0000">
	Tiếp tục
	</font></button>	
    <!--<a href="?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid">刷新</a>-->
    </div>
	============
    <script src="./js/choujiang.js" type="text/javascript">
	</script>
	<body>
				<a href="#0" class="cd-popup-trigger">Rủ {$npc->nname} đi giải sầu</a>
    <div class="cd-popup" role="alert">
        <div class="cd-popup-container">
            <p>
			
			
			<table class="cd-popup-container" style="background-color:#ff5722">
<td>{$npc->nname}: Nếu không cùng đi ăn bún thập cẩm cay?</td>
</table>
			<!--以下为随机文字
			<div class="list-title text">
			<font face="UTM Swiss Condensed" color="ae2d61">
			<marquee direction="left" style="background: #ffffff;font-size:30 px">
<body> 
<script language="JavaScript">

var a = Math.random() + ""
var rand1 = a.charAt(16)
quotes = new Array
quotes[0] = 'Im lặng là vàng, ta cũng im lặng lâu như vậy, thế méo nào không nhìn thấy vàng.'
quotes[1] = 'Hút nhiều thuốc, ít vận động, nếu ngươi thấy lạnh, ta sẽ giúp ngươi chăm sóc lão bà ^0^'
quotes[2] = 'Ngoại hình của ngươi vẫn ổn, nhưng ta nghĩ ngươi vẫn trông đẹp hơn khi đeo mặt nạ.'
quotes[3] = 'Hãy để duyên trần trôi theo nước, chớ nên đa tình lệ sầu lây, thí chủ hà tất phải đau buồn'
quotes[4] = 'Mọi thứ đều là giả, chỉ có nghèo đói mới là thật.'
quotes[5] = 'Cười cho bản thân xem. Nước mắt chảy cho bản thân. Không dối trá. Không giả tạo.'
quotes[6] = 'Ta thực sự biết ơn những người đã từng tấn công ta và dạy ta cách tấn công những con quái vật khác...'
quotes[7] = 'Sáng sớm cho bản thân một nụ cười, gieo xuống ánh nắng cho cả ngày.'
quotes[8] = 'Đại hiệp, tha cho tôi đi mà! Tôi đáng chết, tôi đáng chết'
quotes[9] = 'Hữu duyên thiên lý năng tương ngộ. Vô duyên đối diện bất tương phùng'
quotes[10] = 'Nếu người phụ nữ không có hoa thì làm sao có thể đẹp như hoa được? Đàn ông không xấu thì làm sao có con cháu?'
quotes[12] = 'Bây giờ ngươi có thể làm bất cứ điều gì ngươi muốn và ăn bất cứ thứ gì ngươi muốn càng sớm càng tốt ...'
quotes[13] = 'Nếu có kiếp sau, ta muốn làm chăn, hoặc nằm trên giường hoặc phơi nắng!'
quotes[14] = 'Cho dù ngươi chiếm được thân xác ta nhưng mãi cũng không chiếm được tâm hồn ta'
quotes[15] = 'Ta thực sự biết ơn những người đã từng tấn công ta và dạy ta cách tấn công những con quái vật khác...'
var quote = quotes[rand1]

</script>
<script language="JavaScript">

document.write(quote)

</script>
</body>
<font color="#89EE00">^.^</font>


			</marquee></font>
		</div>
		以上为随机文字-->
		
			</p>
            <ul class="cd-buttons">
                <li><a href="?cmd=npc&nid=$nid&canshu=czyxb&sid=$sid"">Yes</a></li>
                <li><a href="?cmd=$gonowmid">No</a></li>
            </ul>
            <a href="#0" class="cd-popup-close img-replace">Close</a>
        </div>
        <!-- cd-popup-container -->
    </div>
    <!-- cd-popup -->
    <script src="./chajian/tanchukuang/js/jquery.1.11.1.js"></script>
    <script src="./chajian/tanchukuang/js/main.js"></script>
    <!-- Resource jQuery -->
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
	  <text dy="1em" font-size="55px">Khách Điếm Thôn Trưởng</text>
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