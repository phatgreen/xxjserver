<?php
$gmcmd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&sid=$sid");
$ydlist = $encode->encode("cmd=npc&nid=$nid&canshu=ydlist&sid=$sid");
$gnhtml = <<<HTML

<br/><a href="?cmd=$gmcmd">购买药丹</a><br/>
HTML;

if (isset($canshu)){
    switch ($canshu){
        case 'gogoumai':
            $gnhtml='';
            if (isset($ydcount) && isset($ydid)){
                $yaodan = \player\getyaodanonce($ydid,$dblj);
                $ydjg = $yaodan->ydjg;
                $ydname = $yaodan->ydname;
                $ret = \player\changeyxb(2,$ydjg*$ydcount,$sid,$dblj);
                if ($ret){
                    \player\addyaodan($sid,$ydid,$ydcount,$dblj);
                    $gnhtml .= "购买".$ydcount.$ydname."成功<br/>";
                }else{
                    $gnhtml .= "灵石数量不足<br/>";
                }
            }
            $yaodan = \player\getyaodan($dblj);
            foreach ($yaodan as $oneyaodan){
                $ydname = $oneyaodan['ydname'];
                $ydid = $oneyaodan['ydid'];
                $ydjg = $oneyaodan['ydjg'];
                $ydcmd = $encode->encode("cmd=ydinfo&ydid=$ydid&sid=$sid");//cmd=php文件& 文件内某物件 &sid是一个地址 你的身份
                $gm1yd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ydcount=1&ydid=$ydid&sid=$sid");
            //    $gm5yd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ydcount=5&ydid=$ydid&sid=$sid");
            //    $gm10yd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ydcount=10&ydid=$ydid&sid=$sid");
                $gm1yd = '<a href="?cmd='.$gm1yd.'">购买1</a>';
             //   $gm5yd = '<a href="?cmd='.$gm5yd.'">购买5</a>';
            //    $gm10yd = '<a href="?cmd='.$gm10yd.'">购买10</a>';
                $gnhtml .= <<<HTML
                    <br/><a href="?cmd=$ydcmd">$ydname--$ydjg 灵石</a>$gm1yd$gm5yd$gm10yd
HTML;
            }
            $gnhtml .="<br/>";
            break;
    }
}






