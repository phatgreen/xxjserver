<?php
//新手村村长
$player = player\getplayer($sid,$dblj);
$rehp = $encode->encode("cmd=npc&nid=$nid&canshu=rehp&sid=$sid");
$npchtml =<<<HTML
Lỗi trò chơi
 <p><a href="?cmd=$gonowmid">Quay lại trò chơi</a></p>
HTML;
$xiaohao = round($player->ulv*(8.2 + $player->ulv/2));
if ($nid!=''){
    if (isset($canshu)){
        switch ($canshu){
            case 'rehp':
                if ($player->uhp<=0){
                    \player\changeyxb(2,$xiaohao,$sid,$dblj);
                    \player\changeplayersx('uhp',$player->umaxhp,$sid,$dblj);
                    $player = player\getplayer($sid,$dblj);
                    $gnhtml =<<<HTML
                    <div class="npc-result ok">❤️ <b>$npc->nname:</b> Thiếu hiệp, khí huyết của ngươi đã hồi phục!<br/>Sinh mệnh: <b>$player->uhp/$player->umaxhp</b></div>
HTML;
                }else{
                    $gnhtml ="<div class='npc-result bad'>Ta chỉ tiếp người bị trọng thương (khí huyết bằng 0).</div>";
                }
                break;
        }
    }else{
        $gnhtml =<<<HTML
        <a class="npc-act" href="?cmd=$rehp">❤️ Hồi phục sinh mệnh — <b>$xiaohao</b> Linh thạch (không đủ Linh thạch thì miễn phí)</a>
HTML;
        }
}
?>