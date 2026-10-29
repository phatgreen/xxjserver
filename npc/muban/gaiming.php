<?php
/**
 * Created by PhpStorm.
 * User: 九 零 一 起玩www. 9 0 1 75.com
 * Date: 202 1/1 2/30 
 * Time:16:10 
 */
$player = \player\getplayer($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$gaiming =$encode->encode("cmd=npc&&nid=$nid&canshu=gaiming&sid=$sid");
//$gm =  $encode->encode("cmd=gaiming&canshu2=gaiming2&sid=$sid");
$gnhtml=<<<HTML
<div class="npc-quote">✨ Làm lại cuộc đời — mai danh ẩn tích, không màng thế sự… hoặc đổi tên tung hoành tứ phương.</div>
<a class="npc-act" href="?cmd=$gaiming">✏️ Đổi tên — <b>200</b> Ma thạch</a>
HTML;
$ts = '';


$czb = $player->uczb;

if (isset($canshu)){
    switch ($canshu){
        case "gaiming":
            if ($czb<200){
                $gnhtml= "<div class='npc-result bad'>Nghèo cũng là một loại bệnh — đổi tên cần <b>200 Ma thạch</b>!</div>";
                break;}
				
$gnhtml=<<<HTML
<form>
<input type="hidden" name="cmd" value="npc">
<input type="hidden" name="nid" value="$nid">
<input type="hidden" name="sid" value="$sid">
<input type="hidden" name="canshu" value="addclub">

<div class="npc-form">
<input name="gaibian" placeholder="Tên mới (6–12 byte)" maxlength="12">
<button type="submit" class="npc-act">Đổi tên (−200 Ma thạch)</button>
</div>
</form>
HTML;
				
             break;
             case "addclub":
            if ($czb<200){
                $ts= "<div class='npc-result bad'>Ma thạch không đủ 200, không thể đổi tên.</div>";
                break;
            }

           $gaibian = htmlspecialchars($gaibian);

            if (strlen($gaibian)<6 || strlen($gaibian)>12){
                $ts = "<div class='npc-result bad'>Tên quá dài hoặc quá ngắn.</div>";
                break;
            }
			if ($player->uczb>200){
			     $ret1 = \player\changeczb(2,200,$sid,$dblj);//魔石扣除100
			
			     $sql = "update game1 set uname = ? WHERE sid = ?";//改变玩家属性
                 $stmt = $dblj->prepare($sql);
                 $stmt->execute(array($gaibian, $sid));
                 $ret = $stmt->rowCount();

            $ts.= "<div class='npc-result ok'>Đại hiệp, đổi tên thành công: <b>$gaibian</b></div>";}
			else {
				$ts = "<div class='npc-result bad'>Ma thạch không đủ 200, không thể đổi tên.</div>";
			}
            break;
    }

}
$gnhtml = $ts . $gnhtml;   // thông báo hiện trong khung Tương tác của NPC
?>


