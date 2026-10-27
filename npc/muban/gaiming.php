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
<div align="center">
==重新做人==<br/>
隐姓埋名<br/>
不问世事<br/>
又或者...<br/>
大杀四方<br>
<a href="?cmd=$gaiming">修改名字</a>
<!--<a href="?cmd=$gm">修改名字2</a>-->

</div>
HTML;


$czb = $player->uczb;

if (isset($canshu)){
    switch ($canshu){
        case "gaiming":
            if ($czb<200){
                $gnhtml= "<hr/>穷是一种病，穷疯了还想着改名字？<br/>改名需要支付魔石200！<hr>";
                break;}
				
$gnhtml=<<<HTML
<form>
<input type="hidden" name="cmd" value="npc">
<input type="hidden" name="nid" value="$nid">
<input type="hidden" name="sid" value="$sid">
<input type="hidden" name="canshu" value="addclub">

<div align="center">
<input name="gaibian" placeholder="新大名:">
<br><br>
<input type="submit" value="更改" style="background-color: #49bb7c;color: white;border-radius: 4px;">
</div>
</form>
HTML;
				
             break;
             case "addclub":
            if ($czb<200){
                $ts= "<hr>魔石不足200，无法修改<hr>";
                break;
            }

           $gaibian = htmlspecialchars($gaibian);

            if (strlen($gaibian)<6 || strlen($gaibian)>12){
                $ts = "<hr/>名称过长或过短<hr/>";
                break;
            }
			if ($player->uczb>200){
			     $ret1 = \player\changeczb(2,200,$sid,$dblj);//魔石扣除100
			
			     $sql = "update game1 set uname = ? WHERE sid = ?";//改变玩家属性
                 $stmt = $dblj->prepare($sql);
                 $stmt->execute(array($gaibian, $sid));
                 $ret = $stmt->rowCount();

            $ts.= "<hr/>大虾，名字更改成功[$gaibian]<hr/>";}
			else {
				$ts = "<hr/>魔石不足200，无法创建<hr/>";
			}
            break;
    }

}
echo $ts;
?>


