<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/11
 * Time: 16:14
 */
$jnlistcmd = $encode->encode("cmd=npc&nid=$nid&canshu=jnlist&sid=$sid");
$jnlist='<div class="npc-list-title">🧧 Danh sách phù lục có thể đổi</div>';
$gnhtml = <<<HTML
<a class="npc-act" href="?cmd=$jnlistcmd">🧧 Phù lục hối đoái</a>
HTML;
$suoyin = 0;
if (isset($canshu)){
    switch ($canshu){
        case 'jnlist':
            $retjn = \player\getjineng_all($dblj);
            foreach ($retjn as $jn){
                $suoyin += 1;
                $jnid = $jn['jnid'];
                $jnname= $jn['jnname'];
                $jncmd = $encode->encode("cmd=jninfo&jnid=$jnid&sid=$sid");
                $jnlist .= "<div class='npc-item' style='--ra:#f87171'><span class='item-art'><span class='bag-emo'>🧧</span><b class='bag-qty'>$suoyin</b></span><a class='npc-item-name' href='?cmd=$jncmd'>$jnname</a><a class='npc-buy' href='?cmd=$jncmd'>Xem</a></div>";
            }
            $gnhtml = $jnlist;
            break;
    }

}