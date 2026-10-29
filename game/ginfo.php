<?php
$player = player\getplayer($sid,$dblj);
$html = '';
$backcmd=$encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
if ($nowmid!=$player->nowmid){
    $html = <<<HTML
        Chơi game bình thường nhá!<br/>
        <br/>
        <a href="?cmd=$backcmd">Đại Sảnh</a>
HTML;
    echo $html;
    exit();
}
if (isset($gid)){
    $guaiwu = player\getguaiwu($gid,$dblj);
    $yguaiwu = \player\getyguaiwu($gyid,$dblj);
    $pvecmd=$encode->encode("cmd=pve&gid=$gid&sid=$sid&nowmid=$nowmid");
    if ($yguaiwu->ginfo==''){
        $yguaiwu->ginfo = 'Chưa có Danh Khí';
    }
    
    if ($guaiwu->sid !='' or $guaiwu->gname==''){
        $html = <<<HTML
        Quái vật đã bị những người khác công kích!<br/>
        Mời thiếu hiệp luyện tập một chút tốc độ quay tay a
        <br/>
        <a href="?cmd=$backcmd">Đại Sảnh</a>
HTML;
    }  else{
        $dlhtml = '';
        $zbhtml = '';
        $djhtml = '';
        $yphtml = '';
        if ($yguaiwu->gzb!=''){
            $zbarr = explode(',',$yguaiwu->gzb);
            foreach($zbarr as $newstr){
                $zbkzb = \player\getzbkzb($newstr,$dblj);
				//$zhuangbei = new \player\zhuangbei();
                $zbcmd = $encode->encode("cmd=zbinfo_sys&zbid=$zbkzb->zbid&sid=$sid");
                if (!$zbkzb) continue;
                $dImg = \player\zhuangbei_image_path((object) array('zbid' => $zbkzb->zbid), \player\zhuangbei_slot_icon(isset($zbkzb->zbtool) ? intval($zbkzb->zbtool) : 0));
                $zbhtml .= "<a class='g-drop' style='--ra:{$zbkzb->zbys}' href='?cmd=$zbcmd'><i class='item-art'><img src='$dImg' alt=''></i><font color='{$zbkzb->zbys}'>$zbkzb->zbname</font></a>";
            }
            $dlhtml .=$zbhtml;
        }
        if ($yguaiwu->gdj!=''){
            $djarr = explode(',',$yguaiwu->gdj);
            foreach($djarr as $newstr){
                $dj = \player\getdaoju($newstr,$dblj);
                $djinfo = $encode->encode("cmd=djinfo&djid=$dj->djid&sid=$sid");
                list($dEmo, $dCol) = \player\item_emoji($dj->djname, '📦');
                $djhtml .= "<a class='g-drop' style='--ra:$dCol' href='?cmd=$djinfo'><i class='item-art'><span class='bag-emo'>$dEmo</span></i>$dj->djname</a>";
            }
            $dlhtml .=$djhtml;
        }
        if ($yguaiwu->gyp!=''){
            $yparr = explode(',',$yguaiwu->gyp);
            foreach($yparr as $newstr){
                $yp = \player\getyaopinonce($newstr,$dblj);
                $ypinfo = $encode->encode("cmd=ypinfo&ypid=$yp->ypid&sid=$sid");

                list($dEmo, $dCol) = \player\item_emoji($yp->ypname, '💊');
                $yphtml .= "<a class='g-drop' style='--ra:$dCol' href='?cmd=$ypinfo'><i class='item-art'><span class='bag-emo'>$dEmo</span></i>$yp->ypname</a>";
            }
            $dlhtml .=$yphtml;
        }

        if ($dlhtml == ''){
            $dlhtml = 'Quái vật nói ngươi muốn làm gì thì làm, nhưng ta sẽ không cho ngươi trang bị, thà chết cũng không theo.<br/>';
        }
	


	
$sjhtml =<<<HTML
<font face="UTM Swiss Condensed" color="ae2d61">
<!--<marquee direction="left" style="background: #89EE00;font-size:30 px"></marquee></font>--><!--动态文字条-->
<font color="#09BDAA">
<body> 
<script language="JavaScript">
<!-- Hide
var a = Math.random() + ""
var rand1 = a.charAt(15)
quotes = new Array
quotes[0] = 'Im lặng là vàng, ta cũng im lặng lâu như vậy, thế méo nào không nhìn thấy vàng.'
quotes[1] = 'Hút nhiều thuốc, ít vận động, nếu ngươi thấy lạnh, ta sẽ giúp ngươi chăm sóc lão bà ^0^'
quotes[2] = 'Ngoại hình của ngươi vẫn ổn, nhưng ta nghĩ ngươi vẫn trông đẹp hơn khi đeo mặt nạ.'
quotes[3] = 'Hãy để duyên trần trôi theo nước, chớ nên đa tình lệ sầu lây, thí chủ hà tất phải đau buồn'
quotes[4] = 'Mọi thứ đều là giả, chỉ có nghèo đói mới là thật.'
quotes[5] = 'Cười cho bản thân xem. Nước mắt chảy cho bản thân. Không dối trá. Không giả tạo.'
quotes[6] = 'Ta thực sự biết ơn những người đã từng tấn công ta và dạy ta cách tấn công những con quái vật khác...'
quotes[7] = 'Sáng sớm cho bản thân một nụ cười, gieo xuống ánh nắng cho cả ngày.'
quotes[8] = 'Đại hiệp, tha cho tôi đi mà! Tôi đáng chết, tôi đáng chết'
quotes[9] = 'Hữu duyên thiên lý năng tương ngộ. Vô duyên đối diện bất tương phùng'
quotes[10] = 'Nếu người phụ nữ không có hoa thì làm sao có thể đẹp như hoa được? Đàn ông không xấu thì làm sao có con cháu?'
quotes[12] = 'Bây giờ ngươi có thể làm bất cứ điều gì ngươi muốn và ăn bất cứ thứ gì ngươi muốn càng sớm càng tốt ...'
quotes[13] = 'Nếu có kiếp sau, ta muốn làm chăn, hoặc nằm trên giường hoặc phơi nắng!'
quotes[14] = 'Cho dù ngươi chiếm được thân xác ta nhưng mãi cũng không chiếm được tâm hồn ta'
quotes[15] = 'Ta thực sự biết ơn những người đã từng tấn công ta và dạy ta cách tấn công những con quái vật khác...'
var quote = quotes[rand1]
</script>
<script language="JavaScript">
<!-- Hide
document.write(quote)
</script>
</body>
</font>
<font color="#89EE00">---$yguaiwu->gname</font>
</font>
HTML;
		
		
		
		
		
		
		
		
		
        $gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
        $html = <<<HTML
<div class="bossinfo-page">
    <div class="bossinfo-hero">
        <div class="bossinfo-aura"></div>
        <div class="bossinfo-rank">BOSS DÃ NGOẠI</div>
        <h1>👹 {$yguaiwu->gname}</h1>
        <div class="bossinfo-sub">
            <span>{$yguaiwu->gsex}</span>
            <em>•</em>
            <span>Cấp {$yguaiwu->glv}</span>
            <em>•</em>
            <span>{$guaiwu->jingjie}</span>
        </div>
    </div>

    <div class="bossinfo-stats">
        <div class="bossinfo-stat atk">
            <span>⚔ Công Kích</span>
            <strong>{$guaiwu->ggj}</strong>
        </div>
        <div class="bossinfo-stat def">
            <span>🛡 Phòng Ngự</span>
            <strong>{$guaiwu->gfy}</strong>
        </div>
        <div class="bossinfo-stat lv">
            <span>📈 Cấp Độ</span>
            <strong>{$yguaiwu->glv}</strong>
        </div>
        <div class="bossinfo-stat realm">
            <span>☯ Cảnh Giới</span>
            <strong>{$guaiwu->jingjieClass}</strong>
        </div>
    </div>

    <div class="bossinfo-section bossinfo-intro">
        <div class="bossinfo-section-title">📜 Thông Tin Boss</div>
        <div class="bossinfo-info">{$yguaiwu->ginfo}</div>
        <div class="bossinfo-talk">{$sjhtml}</div>
    </div>

    <div class="bossinfo-section bossinfo-drop">
        <div class="bossinfo-section-title">🎁 Chiến Lợi Phẩm Có Thể Rơi</div>
        <div class="bossinfo-drop-list">{$dlhtml}</div>
    </div>

    <div class="bossinfo-fight">
        <a href="?cmd={$pvecmd}" class="bossinfo-fight-btn">⚔ Khiêu Chiến Boss</a>
    </div>

    <div class="bossinfo-bottom">
        <a href="javascript:history.back();" class="bossinfo-nav-btn">← Trở Về</a>
        <a href="game.php?cmd={$gonowmid}" class="bossinfo-nav-btn home">🏠 Đại Sảnh</a>
    </div>
</div>
HTML;
    }
}
echo $html;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 202 1 myj /6/11
 * Time: 10:08
 */
?>

