<?php

$gmcmd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&sid=$sid");
$yplist = $encode->encode("cmd=npc&nid=$nid&canshu=yplist&sid=$sid");
$gnhtml = <<<HTML
<br/><a href="?cmd=$gmcmd">Mua dược phẩm</a><br/>
HTML;

if (isset($canshu)){
    switch ($canshu){
        case 'gogoumai':
            $gnhtml='';
            if (isset($ypcount) && isset($ypid)){
                $yaopin = \player\getyaopinonce($ypid,$dblj);
                $ypjg = $yaopin->ypjg;
                $ypname = $yaopin->ypname;
                $ret = \player\changeyxb(2,$ypjg*$ypcount,$sid,$dblj);
                if ($ret){
                    \player\addyaopin($sid,$ypid,$ypcount,$dblj);
                    $gnhtml .= "Mua ".$ypcount . " [".$ypname."] Thành công<br/>";
                }else{
                    $gnhtml .= "Linh thạch không đủ<br/>";
                }
            }
            $yaopin = \player\getyaopin($dblj);
            foreach ($yaopin as $oneyaopin){
                $ypname = $oneyaopin['ypname'];
                $ypid = $oneyaopin['ypid'];
                $ypjg = $oneyaopin['ypjg'];
                $ypcmd = $encode->encode("cmd=ypinfo&ypid=$ypid&sid=$sid");
                $gm1yp = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ypcount=1&ypid=$ypid&sid=$sid");
            //    $gm5yp = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ypcount=5&ypid=$ypid&sid=$sid");
            //    $gm10yp = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&ypcount=10&ypid=$ypid&sid=$sid");
                $gm1yp = '<a href="?cmd='.$gm1yp.'">Mua 1</a>';
            //    $gm5yp = '<a href="?cmd='.$gm5yp.'">购买5</a>';
            //    $gm10yp = '<a href="?cmd='.$gm10yp.'">购买10</a>';
                $gnhtml .= <<<HTML
                    <br/><a href="?cmd=$ypcmd">$ypname--$ypjg Linh thạch</a>$gm1yp$gm5yp$gm10yp
HTML;
            }
            $gnhtml .="<br/>";
            break;
    }
}






