<?php
/**
 * PVP PK - giao diện tiên hiệp
 * Cập nhật: 20260630
 */

$gorehpmid = $encode->encode("cmd=npc&nid=17&sid=$player->sid");
$player = \player\getplayer($sid,$dblj);
$pvper = \player\getplayer1($uid,$dblj);
$cxmid = \player\getmid($player->nowmid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$player->sid");

function pvp_safe_text($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function pvp_percent($now, $max){
    $now = intval($now);
    $max = max(1, intval($max));
    $per = round(($now / $max) * 100, 2);
    if ($per < 0) $per = 0;
    if ($per > 100) $per = 100;
    return $per;
}

function pvp_alert_html($type, $title, $msg, $btnText, $btnCmd){
    $title = pvp_safe_text($title);
    $msg = pvp_safe_text($msg);
    $btnText = pvp_safe_text($btnText);
    return '<div class="pvp-page"><div class="pvp-alert '.$type.'">'
        .'<div class="pvp-alert-icon">⚔</div>'
        .'<div class="pvp-alert-title">'.$title.'</div>'
        .'<div class="pvp-alert-msg">'.$msg.'</div>'
        .'<a class="pvp-btn home" href="?cmd='.$btnCmd.'">'.$btnText.'</a>'
        .'</div></div>';
}

if ($cxmid->ispvp == 0){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    exit(pvp_alert_html('warn', 'Cấm PK', 'Khu vực hiện tại không cho phép quyết đấu.', 'Đại Sảnh', $gonowmid));
}

if (!$pvper){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    exit(pvp_alert_html('warn', 'Không tìm thấy đối thủ', 'Người chơi này không tồn tại trong khu vực.', 'Đại Sảnh', $gonowmid));
}

if ($pvper->sfzx == 0){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    exit(pvp_alert_html('warn', 'Đối thủ rời trận', 'Người chơi hiện không online.', 'Đại Sảnh', $gonowmid));
}

if ($pvper->nowmid != $player->nowmid){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    exit(pvp_alert_html('warn', 'Không tìm thấy đối thủ', 'Người chơi không còn trong khu vực này.', 'Đại Sảnh', $gonowmid));
}

if ($player->uhp<=0){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    exit(pvp_alert_html('danger', 'Trọng thương', 'Ngươi bị trọng thương, không thể tiến hành chiến đấu.', 'Vân Du Tiên Y', $gorehpmid));
}

if ($pvper->uhp<=0){
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    \player\changeplayersx("ispvp",0,$pvper->sid,$dblj);
    exit(pvp_alert_html('danger', 'Đối thủ trọng thương', 'Người chơi đã bị trọng thương, đại hiệp tạm thời giơ cao đánh khẽ.', 'Đại Sảnh', $gonowmid));
}

\player\changeplayersx("ispvp",$player->uid,$pvper->sid,$dblj);

$pvperhurt = '';
$tishihtml = '';
$pvpbj = '';
$lastActionClass = '';

if (isset($canshu)){
    switch ($canshu){
        case 'gj':
            $jineng = new \player\jineng();
            if (isset($jnid)){
                $ret = \player\delejnsum($jnid,1,$sid,$dblj);
                if ($ret){
                    $jineng = \player\getplayerjineng($jnid,$sid,$dblj);
                    $tishihtml = '<div class="pvp-log good">✦ Sử dụng kỹ năng: '.pvp_safe_text($jineng->jnname).'</div>';
                }else{
                    $tishihtml = '<div class="pvp-log bad">✧ Kỹ năng không đủ</div>';
                }
            } else {
                $tishihtml = '<div class="pvp-log good">⚔ Ngươi tung ra một đòn công kích</div>';
            }

            $player->ugj +=$jineng->jngj;
            $player->ufy +=$jineng->jnfy;
            $player->ubj +=$jineng->jnbj;
            $player->uxx +=$jineng->jnxx;

            $ran = mt_rand(1,100);
            if ($player->ubj >= $ran){
                $player->ugj = round($player->ugj * 1.82);
                $pvpbj = '<span class="pvp-crit">Bạo kích</span>';
                $lastActionClass = ' crit-flash';
            }

            $hurtNum = round($player->ugj - $pvper->ufy * 0.75,0);
            if ($hurtNum < $player->ugj * 0.05){
                $hurtNum = round($player->ugj*0.05);
            }

            // ===== MACH HUYET (Task 11): mach cua NGUOI BI DAN trung lap =====
            // pen CO Y khong ap dung PvP: do mean +29.59% (nguong 8%) — xem 11.0b + Task 12.
            $hmCsDef = \player\huyetmach_combat_stats(isset($pvper->huyetmach_vessel) ? $pvper->huyetmach_vessel : array());
            $hmDodged = false;
            if ($hmCsDef['reduce'] > 0 && $hurtNum > 0) {
                $hurtNum = \player\huyetmach_combat_phys_reduce($hmCsDef['reduce'], $hurtNum);
            }
            if ($hmCsDef['dodge'] > 0 && $hurtNum > 0 && \player\huyetmach_combat_roll_dodge($hmCsDef['dodge'])) {
                $hurtNum = 0;
                $hmDodged = true;
                $tishihtml .= '<div class="pvp-log bad">💨 Âm Kiều Mạch: đối phương nghiêng người tránh khỏi đòn</div>';
            }
            $pvpxx = round($hurtNum*($player->uxx/100));
            $sql = "update game1 set uhp = GREATEST(0, uhp - $hurtNum) WHERE sid = '$pvper->sid'";
            $dblj->exec($sql);
            \player\addplayersx("uhp",$pvpxx,$sid,$dblj);

            $player =  player\getplayer($sid,$dblj);

            $pvper = \player\getplayer1($uid,$dblj);
            $pvperhurt = $hmDodged ? '<span class="pvp-damage">Né (0)</span>' : '<span class="pvp-damage">-'.$hurtNum.'</span>';
            if ($pvpxx > 0) {
                $tishihtml .= '<div class="pvp-log heal">✚ Hút máu hồi phục +'.$pvpxx.'</div>';
            }

            if ($pvper->uhp<=0){
                \player\changeplayersx("ispvp",0,$sid,$dblj);
                \player\changeplayersx("ispvp",0,$pvper->sid,$dblj);

                $safeP1 = pvp_safe_text($player->uname);
                $safeP2 = pvp_safe_text($pvper->uname);
                $dieinfo = array("Nghe nói $safeP1 đánh chết $safeP2","$safeP2 bị $safeP1 đánh một trận nhừ tử"," $safeP1 đem $safeP2 đánh cho sinh hoạt không thể tự lo liệu");
                $randdie = mt_rand(0,count($dieinfo)-1);
                $msg = $dieinfo[$randdie];
                $sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
                $stmt = $dblj->prepare($sql);
                $stmt->execute(array('【Tin đồn】', $msg, '0'));

                $winName = pvp_safe_text($player->uname);
                $loseName = pvp_safe_text($pvper->uname);
                $html = '<div class="pvp-page"><div class="pvp-result win">'
                    .'<div class="pvp-result-icon">🏆</div>'
                    .'<div class="pvp-result-title">Chiến Thắng</div>'
                    .'<div class="pvp-result-msg">'.$winName.' đã đánh bại '.$loseName.'</div>'
                    .'<a class="pvp-btn home" href="?cmd='.$gonowmid.'">Đại Sảnh</a>'
                    .'</div></div>';
                exit($html);
            }
            break;
    }
}

if ($player->uhp<=0){
    $cxmid = \player\getmid($player->nowmid,$dblj);
    $cxqy = \player\getqy($cxmid->mqy,$dblj);
    \player\changeplayersx("ispvp",0,$sid,$dblj);
    \player\changeplayersx("ispvp",0,$pvper->sid,$dblj);
    $html = '<div class="pvp-page"><div class="pvp-result lose">'
        .'<div class="pvp-result-icon">☠</div>'
        .'<div class="pvp-result-title">Chiến Bại</div>'
        .'<div class="pvp-result-msg">Ngươi bị đánh trọng thương, hãy đến Vân Du Tiên Y [Trị Liệu] rồi tái chiến.</div>'
        .'<a class="pvp-btn home" href="?cmd='.$gorehpmid.'">Vân Du Tiên Y</a>'
        .'</div></div>';
    exit($html);
}

$pgjcmd = $encode->encode("cmd=pvp&canshu=gj&uid=$uid&sid=$player->sid");
$usejn1 = $encode->encode("cmd=pvp&canshu=gj&jnid=$player->jn1&sid=$sid&uid=$uid");
$usejn2 = $encode->encode("cmd=pvp&canshu=gj&jnid=$player->jn2&sid=$sid&uid=$uid");
$usejn3 = $encode->encode("cmd=pvp&canshu=gj&jnid=$player->jn3&sid=$sid&uid=$uid");

$jnname1 = 'Phù lục 1';
$jnname2 = 'Phù lục 2';
$jnname3 = 'Phù lục 3';

if ($player->jn1!=0){
    $jineng = \player\getplayerjineng($player->jn1,$sid,$dblj);
    if ($jineng){ $jnname1 = $jineng->jnname.'('.$jineng->jncount.')'; }
}
if ($player->jn2!=0){
    $jineng = \player\getplayerjineng($player->jn2,$sid,$dblj);
    if ($jineng){ $jnname2 = $jineng->jnname.'('.$jineng->jncount.')'; }
}
if ($player->jn3!=0){
    $jineng = \player\getplayerjineng($player->jn3,$sid,$dblj);
    if ($jineng){ $jnname3 = $jineng->jnname.'('.$jineng->jncount.')'; }
}

$playerHpPer = pvp_percent($player->uhp, $player->umaxhp);
$pvperHpPer = pvp_percent($pvper->uhp, $pvper->umaxhp);
$playerName = pvp_safe_text($player->uname);
$pvperName = pvp_safe_text($pvper->uname);
$jnname1 = pvp_safe_text($jnname1);
$jnname2 = pvp_safe_text($jnname2);
$jnname3 = pvp_safe_text($jnname3);

$html = <<<HTML
<div class="pvp-page">
    <div class="pvp-hero">
        <div>
            <div class="pvp-label">SINH TỬ ĐÀI</div>
            <div class="pvp-title">PK Quyết Đấu</div>
            <div class="pvp-desc">Kẻ thắng lưu danh, kẻ bại trọng thương.</div>
        </div>
        <div class="pvp-vs">VS</div>
    </div>

    <div class="pvp-arena$lastActionClass">
        <div class="pvp-fighter enemy">
            <div class="pvp-side-title">Đối thủ</div>
            <div class="pvp-name">$pvperName <span>Lv.$pvper->ulv</span></div>
            <div class="pvp-hpbar"><i style="width:$pvperHpPer%"></i></div>
            <div class="pvp-hptext">Khí huyết <b>$pvper->uhp</b> / $pvper->umaxhp $pvpbj $pvperhurt</div>
            <div class="pvp-stat-grid">
                <div><span>Công kích</span><b>$pvper->ugj</b></div>
                <div><span>Phòng ngự</span><b>$pvper->ufy</b></div>
            </div>
        </div>

        <div class="pvp-center">
            <div class="pvp-sword">⚔</div>
            <div class="pvp-center-text">Quyết chiến</div>
        </div>

        <div class="pvp-fighter player">
            <div class="pvp-side-title">Nhân vật</div>
            <div class="pvp-name">$playerName <span>Lv.$player->ulv</span></div>
            <div class="pvp-hpbar"><i style="width:$playerHpPer%"></i></div>
            <div class="pvp-hptext">Khí huyết <b>$player->uhp</b> / $player->umaxhp</div>
            <div class="pvp-stat-grid">
                <div><span>Công kích</span><b>$player->ugj</b></div>
                <div><span>Phòng ngự</span><b>$player->ufy</b></div>
            </div>
        </div>
    </div>

    <div class="pvp-battle-log">
        $tishihtml
    </div>

    <div class="pvp-actions">
        <a class="pvp-btn back" href="?cmd=$gonowmid">↩ Bỏ Chạy</a>
        <a class="pvp-btn attack" href="?cmd=$pgjcmd">⚔ Tấn Công</a>
    </div>

    <div class="pvp-skill-box">
        <div class="pvp-section-title">✦ Kỹ năng</div>
        <div class="pvp-skill-grid">
            <a href="?cmd=$usejn1">⚡ $jnname1</a>
            <a href="?cmd=$usejn2">⚡ $jnname2</a>
            <a href="?cmd=$usejn3">⚡ $jnname3</a>
        </div>
    </div>
</div>
HTML;

echo $html;
?>
