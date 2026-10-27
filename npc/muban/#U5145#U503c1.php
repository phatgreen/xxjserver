<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/24
 * Time: 20:30
 */
$gmcmd = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&sid=$sid");
$djlist = $encode->encode("cmd=npc&nid=$nid&canshu=djlist&sid=$sid");
$gnhtml = <<<HTML
<br/><a href="?cmd=$gmcmd"><font color=#F5771E>【</font><font color=#F35A1F>充</font><font color=#F13D20>值</font><font color=#EF2021>】</font></a><br/>
HTML;

if (isset($canshu)){
    switch ($canshu){
        case 'gogoumai':
            $gnhtml='';
            if (isset($djcount) && isset($djid)){
                $daoju = \player\getdaojuonce($djid,$dblj);
                $djjg = $daoju->djjg;
                $djname = $daoju->djname;
                $ret = \player\changeyxb(2,$djjg*$djcount,$sid,$dblj);
                if ($ret){
                    \player\adddaoju($sid,$djid,$djcount,$dblj);
                    $gnhtml .= "购买".$djcount.$djname."成功<br/>";
                }else{
                    $gnhtml .= "灵石数量不足<br/>";
                }
            }
            $daoju = \player\getdaoju($dblj);
            foreach ($daoju as $onedaoju){
                $djname = $onedaoju['djname'];
                $djid = $onedaoju['djid'];
                $djjg = $onedaoju['djjg'];
                $djcmd = $encode->encode("cmd=djinfo&djid=$djid&sid=$sid");
                $gm1dj = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&djcount=1&djid=$djid&sid=$sid");
                $gm5dj = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&djcount=5&djid=$djid&sid=$sid");
                $gm10dj = $encode->encode("cmd=npc&nid=$nid&canshu=gogoumai&djcount=10&djid=$djid&sid=$sid");
                $gm1dj = '<a href="?cmd='.$gm1dj.'">购买1</a>';
                $gm5dj = '<a href="?cmd='.$gm5dj.'">购买5</a>';
                $gm10dj = '<a href="?cmd='.$gm10dj.'">购买10</a>';
                $gnhtml .= <<<HTML
                    <br/><a href="?cmd=$djcmd">$djname--$djjg 灵石</a>$gm1dj$gm5dj$gm10dj
HTML;
            }
            $gnhtml .="<br/>";
            break;
    }
}






