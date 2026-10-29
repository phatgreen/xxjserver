<?php

$gmcmd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&sid=$sid");
$yplist = $encode->encode("cmd=npc&nid=$nid&canshu=yplist&sid=$sid");
$gnhtml = <<<HTML
<a class="npc-act" href="?cmd=$gmcmd">💊 Mua dược phẩm</a>
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
                    $gnhtml .= "<div class='npc-result ok'>Mua ".$ypcount . " [".$ypname."] thành công</div>";
                }else{
                    $gnhtml .= "<div class='npc-result bad'>Linh thạch không đủ</div>";
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
                $gm1yp = '<a class="npc-buy" href="?cmd='.$gm1yp.'">Mua 1</a>';
                list($ypEmo, $ypEmoColor) = \player\item_emoji($ypname, '💊');
            //    $gm5yp = '<a href="?cmd='.$gm5yp.'">购买5</a>';
            //    $gm10yp = '<a href="?cmd='.$gm10yp.'">购买10</a>';
                $gnhtml .= <<<HTML
                    <div class="npc-item" style="--ra:$ypEmoColor"><span class="item-art"><span class="bag-emo">$ypEmo</span></span><a class="npc-item-name" href="?cmd=$ypcmd">$ypname</a><span class="npc-item-price">💰 $ypjg</span>$gm1yp</div>
HTML;
            }

            break;
    }
}






