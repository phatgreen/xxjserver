<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/11
 * Time: 18:54
 */
$jnid = isset($jnid) ? intval($jnid) : 0;
$jineng = \player\getjineng_once($jnid,$dblj);
if (!$jineng){
    echo "Không tìm thấy phù lục này.<br/><br/><a href=\"#\" onClick=\"javascript:history.back(-1);\">Trở Về</a><br/>";
    exit();
}
$duihuan = $encode->encode("cmd=jninfo&canshu=duihuan&jnid=$jnid&sid=$sid");
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$htmltishi = '';
$playerjn = \player\getplayerjineng($jnid,$sid,$dblj);

$daoju = \player\getplayerdaoju($sid,$jineng->jndj,$dblj);
$dhdaoju = \player\getdaoju($jineng->jndj,$dblj);
if (!$daoju){
    $daoju = \player\getdaoju($jineng->jndj,$dblj);
    $daoju->djsum = 0;
}

if (isset($canshu)){
    switch ($canshu){
        case 'duihuan':
            $ret = \player\deledjsum($jineng->jndj,$jineng->djcount,$sid,$dblj);
            if ($ret){
                \player\addjineng($jnid,1,$sid,$dblj);
                $htmltishi = "Đổi thành công<br/>";
                $playerjn = \player\getplayerjineng($jnid,$sid,$dblj);
                $daoju = \player\getplayerdaoju($sid,$jineng->jndj,$dblj);
            }else{
                $htmltishi = "Đạo cụ không đủ<br/>";
            }

            break;
        case 'setjn1':
            \player\changeplayersx('jn1',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 1 thành công<br/>";
            break;
        case 'setjn2':
            \player\changeplayersx('jn2',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 2 thành công<br/>";
            break;
        case 'setjn3':
            \player\changeplayersx('jn3',$jnid,$sid,$dblj);
            $htmltishi = "Thiết lập Phù lục 3 thành công<br/>";
            break;
    }


}

$dhhtml = "Trao đổi cần: $dhdaoju->djname($daoju->djsum/$jineng->djcount)<a href='?cmd=$duihuan'>Trao Đổi</a><br/><br/>";
if ($playerjn){
    $setjn1 = $encode->encode("cmd=jninfo&canshu=setjn1&jnid=$jnid&sid=$sid");
    $setjn2 = $encode->encode("cmd=jninfo&canshu=setjn2&jnid=$jnid&sid=$sid");
    $setjn3 = $encode->encode("cmd=jninfo&canshu=setjn3&jnid=$jnid&sid=$sid");
    $dhhtml .=
        '<a href="?cmd='.$setjn1.'">Trang bị phù lục 1</a>'.
        '<a href="?cmd='.$setjn2.'">Trang bị phù lục 2</a>'.
        '<a href="?cmd='.$setjn3.'">Trang bị phù lục 3</a><br/>';
}

?>
Tên phù lục: <?php echo $jineng->jnname; ?><br/>
Công kích +<?php echo $jineng->jngj; ?>%<br/>
Phòng ngự +<?php echo $jineng->jnfy; ?>%<br/>
Bạo kích +<?php echo $jineng->jnbj; ?>%<br/>
Hút máu +<?php echo $jineng->jnxx; ?>%<br/>
<?php echo $htmltishi; ?>
<?php echo $dhhtml; ?>
<br/>
<a href="#" onClick="javascript:history.back(-1);">Trở Về</a><br/>
<a href="?cmd=<?php echo $gonowmid; ?>">Đại Sảnh</a><br/>
