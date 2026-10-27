<?php
//新手村村长
$player = player\getplayer($sid,$dblj);
$npc = player\getnpc($nid,$dblj);
$rehp = $encode->encode("cmd=npc&nid=$nid&canshu=rehp&sid=$sid");
$npchtml =<<<HTML
游戏错误
 <p><a href="?cmd=$gonowmid">返回游戏</a></p>;
HTML;






$sjhtml =<<<HTML
<div class="list-title text">
<font face="UTM Swiss Condensed" color="ae2d61">
<marquee direction="left" style="background: #89EE00;font-size:30 px"></marquee></font><!--动态文字条-->
<font color="#09BDAA">
<body> 
<script language="JavaScript">
<!-- Hide
var a = Math.random() + ""
var rand1 = a.charAt(11)
quotes = new Array
quotes[1] = 'Hút thuốc nhiều lên, vận động ít thôi; anh mà toi rồi thì vợ anh để tôi lo giúp cho. ^0^'
quotes[2] = 'Bán đi linh hồn và nguyên tắc của mình không đáng xấu hổ; xấu hổ là không bán được với một cái giá xứng đáng。'
quotes[3] = 'Mãi đến năm ba mươi tuổi mới hiểu: nói những điều khác nhau với những người khác nhau, thể hiện những thái độ khác nhau, là một năng lực rất đáng quý, chứ không phải là giả tạo。'
quotes[4] = 'Thứ gì cũng có thể là giả, chỉ có nghèo là thật。'
quotes[5] = 'Lương thiện không có tác dụng, vì chỉ khi bạn đẹp trước đã thì người khác mới nhìn thấy sự lương thiện của bạn。'
quotes[6] = 'Miệng nói “anh yêu em” nhưng không có hành động gì, cũng giống như người chưa từng tưới hoa mà nói mình yêu hoa. Yêu là động từ, hành động mới là lời giải thích tốt nhất cho tình yêu。'
quotes[7] = 'Cuộc sống không chỉ có sự cam chịu trước mắt, mà còn có… thiệp mời đám cưới của người yêu cũ。'
quotes[8] = 'Sớm muộn gì bạn cũng sẽ gặp được người mà bạn thực sự thích — và cả người yêu của họ。'
quotes[9] = 'Chính vì quá rảnh nên mới có sức để mất ngủ, mới có tâm trạng để làm màu; hãy tìm cho cuộc sống chút việc để làm, đừng để từng ngày trôi qua vô nghĩa。'
quotes[0] = 'Tự do tài chính không phải là muốn làm gì thì làm, mà là không muốn làm gì thì có khả năng không làm。'
quotes[10] = 'Đại ca, bây giờ muốn làm gì thì làm đi, muốn ăn gì thì tranh thủ ăn nhanh lên...。'
var quote = quotes[rand1]
</script>
<script language="JavaScript">
<!-- Hide
document.write(quote)
</script>
</body>
</font>
<font color="#89EE00">---$npc->nname</font>
</div>
HTML;










$xiaohao = round($player->ulv*15.2);
if ($nid!=''){
    if (isset($canshu)){
        switch ($canshu){
            case 'rehp':
                if ($player->uhp<=1000){
                    \player\changeyxb(2,$xiaohao,$sid,$dblj);
                    \player\changeplayersx('uhp',$player->umaxhp,$sid,$dblj);
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
                    <br/>$npc->nname:少侠，进过老夫不懈努力的抢救，你现在已经恢复了！又可以去打架了<br/>
                    生命：$player->uhp/$player->umaxhp<br/>
HTML;
                }else{
                    $gnhtml ="<br/>大虾！您这毛病不好治啊...<font color='#BC4613'>【抢救失败】</font>$sjhtml"
					;
					
                }
                break;
        }
    }else{
        $gnhtml =<<<HTML
				<link rel="stylesheet" href="./css/choujiang.css">
		<link rel="stylesheet" href="./css/dongtai.css">
		<script src="./js/jquery.min.js"></script>
			<use href="?cmd=$czyxb">
         <svg viewBox="0 0 900 90">
	     <symbol id="id">
	     <text dy="1em">祖传秘术</text>
	     </symbol>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	  <use class="text" xlink:href="#id"></use>
	</svg></use>
		
        <br/><a href="?cmd=$rehp">Bắt mạch chữa bệnh</a><br/>
		
		
HTML;
        }
}
?>



