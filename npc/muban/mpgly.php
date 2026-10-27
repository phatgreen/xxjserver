<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 202 1/1 2/26 0026
 * Time: 19:06
 */
$newclub =$encode->encode("cmd=npc&&nid=$nid&canshu=newclub&sid=$sid");
$player = \player\getplayer($sid,$dblj);
$gnhtml =<<<HTML

=======Sáng Tạo Môn Phái=======<br/>
Thiên địa bất nhân  Tu đạo tức là tranh đoạt thiên cơ<br/>
Đại đạo vô tận  Đạo tặc vô biên<br/>
Muốn bước lên tiên lộ  Một mình.. Quá khó..<br/>
<a href="?cmd=$newclub" >Sáng tạo môn phái</a><br/>
HTML;
$clubplayer = \player\getclubplayer_once($sid,$dblj);
if (isset($canshu)){
    switch ($canshu){
        case "newclub":
            if ($clubplayer){
                $gnhtml= "<br/>Ngươi đã có môn phái<br/>";
                break;
            }
            $gnhtml=<<<HTML
<form>
<input type="hidden" name="cmd" value="npc">
<input type="hidden" name="nid" value="$nid">
<input type="hidden" name="sid" value="$sid">
<input type="hidden" name="canshu" value="addclub">
<div align="center">
<input name="clubname" placeholder="Tên Môn Phái"><br><br>
<textarea name="clubinfo" style="height: 80px" placeholder="Tuyên ngôn:"></textarea>

<br/>
<input type="submit" value="Sáng tạo" style="background-color: #ab4d0e;color: white;">
</div>
</form>
HTML;
            break;
        case "addclub":
            if ($clubplayer){
                $gnhtml= "<br/>Ngươi đã có môn phái<br/>";
                break;
            }

            $clubname = htmlspecialchars($clubname);
            $clubinfo = htmlspecialchars($clubinfo);
            if (mb_strlen($clubname, 'UTF-8')<2 || mb_strlen($clubname, 'UTF-8')>25){
                $gnhtml = "<br/>Tên tông môn phải từ 2-25 ký tự<br/>";
                break;
            }
			if ($player->uczb>100){
			$ret1 = \player\changeczb(2,100,$sid,$dblj);//魔石扣除100
            $sql = "insert into club(clubname, clubinfo, clublv, clubexp, clubno1) VALUES (?,?,?,?,?)";
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array($clubname,$clubinfo,1,0,$player->uid));
            $clubid = $dblj->lastInsertId();
            $sql = "insert into clubplayer(clubid, uid, sid, uclv) VALUES (?,?,?,?)";
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array($clubid,$player->uid,$sid,1));
            $clubcmd = $encode->encode("cmd=club&sid=$sid");
            $gnhtml.= "<br/>Môn phái sáng tạo thành công<br/><a href='?cmd=$clubcmd'>Điểm kích tiến vào</a>";}
			else {
				$gnhtml = "<br/>Ma thạch không đủ 100, không thể sáng tạo<br/>";
			}
            break;
    }
}

?>

