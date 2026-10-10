
<?php
$player = \player\getplayer($sid,$dblj);
$nowmid=$player->nowmid;
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$player->sid");
// $pgjcmd = $encode->encode("cmd=pvb&canshu=ptgj&bossid=$bossid&sid=$sid");原有的boss攻击

$pgjcmd = $encode->encode("cmd=pvbgj&bossid=$bossid&sid=$player->sid&nowmid=$nowmid");
// Nut ky nang Linh Can (Task 8) — cung mau voi nut tan cong
$lcskillHtmlBoss = '';
if (function_exists('player\\linhcan_skill_for_stage')) {
    $lcs0b = \player\linhcan_skill_for_stage($sid, $dblj);
    if ($lcs0b) {
        $lcskillCmdBoss = $encode->encode("cmd=pvbgj&canshu=uselcskill&bossid=$bossid&sid=$sid&nowmid=$nowmid");
        $lcskillHtmlBoss = '<a class="battle-btn attack" href="?cmd=' . $lcskillCmdBoss . '">'
            . $lcs0b['icon'] . ' ' . $lcs0b['name'] . '</a>';
    }
}
// $pgjcmd = $encode->encode("cmd=pvbgj&bossid=$bossid&sid=$player->sid&nowmid=$nowmid");
$boss = \player\getboss($bossid,$dblj);
$yboss = new \player\boss();
$wgid = $player->wugong;
$cxwg = \player\wgcx($wgid,$sid,$dblj);
$wglx = $cxwg ? $cxwg->wglx : 0;
$cwresthtml='';
// $cxmid = \player\getmid($player->nowmid,$dblj);
// $cxqy = \player\getqy($cxmid->mqy,$dblj);
// $gorehpmid = $encode->encode("cmd=gomid&newmid=$cxqy->mid&sid=$player->sid");
if ($player->wugong!=''&&$wglx==1){
	$tishi = "<a href='?cmd=$gonowmid'>$cxwg->wgname</a><br><br> ";
}



 if ($yboss->bossid){
    
	 $yboss = player\getyboss($yboss->bossid,$dblj);
 }
$huode = '';
$useyp1 = $encode->encode("cmd=pvb&canshu=useyp&ypid=$player->yp1&sid=$sid&bossid=$bossid&nowmid=$nowmid");
$useyp2 = $encode->encode("cmd=pvb&canshu=useyp&ypid=$player->yp2&sid=$sid&bossid=$bossid&nowmid=$nowmid");
$useyp3 = $encode->encode("cmd=pvb&canshu=useyp&ypid=$player->yp3&sid=$sid&bossid=$bossid&nowmid=$nowmid");
$ypname1 = 'Thuốc 1';
$ypname2 = 'Thuốc 2';
$ypname3 = 'Thuốc 3';

if ($player->yp1!=0){
    $yaopin = \player\getplayeryaopin($player->yp1,$sid,$dblj);
    $ypname1 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp2!=0){
    $yaopin = \player\getplayeryaopin($player->yp2,$sid,$dblj);
    $ypname2 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp3!=0){
    $yaopin = \player\getplayeryaopin($player->yp3,$sid,$dblj);
    $ypname3 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
$cwhurt = '';
$tishihtml='';
$tx = '';
$rwts = '';
$cwts = '';
$cwhtml = '';
$tishi = isset($tishi) ? $tishi : '';
if ($boss->bosshp <= 0) {
    // Khi BOSS đã hết máu nhưng người chơi refresh / vào lại link cũ:
    // BOSS bỏ trốn, tăng trưởng chỉ số rồi hồi đầy máu để lần sau đánh tiếp.
    // thứ tự gán quan trọng: bosshp = bossmaxhp phải đặt SAU khi bossmaxhp đã tăng.
    $stmt = $dblj->prepare("UPDATE boss SET bossmaxhp = bossmaxhp + 100, bossgj = bossgj + 5, bosshp = bossmaxhp WHERE bossid = :bossid");
    $stmt->execute(array(':bossid' => $bossid));

    $bossName = isset($boss->bossname) && $boss->bossname !== '' ? $boss->bossname : 'BOSS';
    $bossNameSafe = htmlspecialchars($bossName, ENT_QUOTES, 'UTF-8');

    $html = <<<HTML
<div class="boss-flee-page">
    <div class="boss-flee-hero">
        <div class="boss-flee-imgbox">
            <img src="./images/boss.png" alt="{$bossNameSafe}">
        </div>

        <div class="boss-flee-content">
            <div class="boss-flee-kicker">BOSS THOÁT KHỎI CHIẾN TRƯỜNG</div>
            <div class="boss-flee-title">{$bossNameSafe} đã trốn thoát</div>
            <div class="boss-flee-desc">
                BOSS đã dần dần trốn thoát, học được cách trưởng thành và không còn kêu la nữa.
                Sức chiến đấu của hắn càng ngày càng kinh người!
            </div>

            <div class="boss-flee-grow">
                <div>
                    <span>Sinh lực tối đa</span>
                    <b>+100</b>
                </div>
                <div>
                    <span>Công kích</span>
                    <b>+5</b>
                </div>
            </div>

            <div class="boss-flee-tip">
                Lần sau hãy kết liễu nhanh hơn trước khi hắn kịp phục hồi và mạnh lên.
            </div>
        </div>
    </div>

    <div class="boss-flee-bottom">
        <a class="boss-flee-home" href="?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;
    echo $html;
    exit();
}

if ($nowmid!=$player->nowmid){
    $html = <<<HTML
        Hãy chơi game bình thường nhá!<br/>
        <br/>
        <a href="?cmd=$gonowmid">Đại Sảnh</a>
HTML;
    echo $html;
    exit();
}

if (($boss->sid!=$player->sid && $boss->sid!='') || ($boss->bossid=='')){
        $html = <<<HTML
        Quái vật đã bị những người khác công kích!<br/>
        Mời thiếu hiệp luyện tập một chút tốc độ quay tay a
        <br/>
        <a href="?cmd=$gonowmid">Đại Sảnh</a>
HTML;
        exit($html);
}
$pvbbj = '';
$pvbxx = '';
$pvbsb = '';
$gwbj = '';
if (isset($canshu)){

    if ($canshu == 'useyp'){
        $ret = \player\useyaopin($ypid,1,$sid,$dblj);
        $player =  player\getplayer($sid,$dblj);
    }
}

if($cmd=='pvb' && $boss->sid==''){

    if ($player->ulv >= 1 && $player->uhp <=0){
        $zdjg = -1;
    }else{
        // $sql = "update boss set sid = '$sid' WHERE id='$bossid'";这里删怪，ID写好就行，出现问题，我有点无语12.23
        // $dblj->exec($sql);
        // Không tự hồi HP sủng vật khi vào trận.
        // Nếu sủng vật đang xuất chiến đã trọng thương thì bắt buộc nghỉ ngơi:
        // hồi HP về đầy và tháo khỏi trạng thái xuất chiến.
        $cwRested = \player\auto_rest_dead_chongwu($sid, $dblj);
        if ($cwRested) {
            $cwresthtml = '<font color="#cc0000">Sủng vật ['.$cwRested->cwname.'] đã trọng thương, được đưa về nghỉ ngơi để hồi phục. Cần xuất chiến lại nếu muốn dùng tiếp.</font><br/>';
            $player = \player\getplayer($sid, $dblj);
        }
        if($player->ulv <= 10){
            \player\changeplayersx('uhp',$player->umaxhp,$sid,$dblj);
            $player =  player\getplayer($sid,$dblj);
        }
    }

}

elseif ($cmd == 'pvbgj' && $bossid != 0){
    // Khong cho tan cong khi sinh luong <= 0 (chot nhu ben pve)
    if (intval($player->uhp) <= 0) {
        echo <<<HTML
<div class="boss-result-page boss-result-lose">
    <div class="boss-result-card">
        <div class="boss-result-mark">⚠ TRỌNG THƯƠNG ⚠</div>
        <div class="boss-result-title">Không thể tiếp tục chiến đấu</div>
        <div class="boss-result-desc">Sinh lực đã cạn, cần hồi phục trước khi trở lại.</div>
        <a class="boss-result-btn danger" href="?cmd=$gonowmid">Trở về Đại Sảnh</a>
    </div>
</div>
HTML;
        return;
    }
    //普通攻击
    $hurt = false;
    $ghurt = 0;

    // === KY NANG LINH CAN (Task 8) — switch canshu o day dang bi chu thich hoa nen dung if doc lap ===
    $lcMods = array();
    if (isset($canshu) && $canshu == 'uselcskill') {
        $lcskill = \player\linhcan_skill_for_stage($sid, $dblj);
        $lcnodes = \player\linhcan_node_rows($sid, $dblj);
        $lcdom = \player\linhcan_dominant($lcnodes);
        $lcStage = ($lcdom !== '') ? intval($lcnodes[$lcdom]['stage']) : 0;
        $lcCan = \player\linhcan_skill_can_use($player, $lcskill, $lcStage);
        if (!$lcskill) {
            $tishihtml = "<font color='#cc0000'>Chưa mở kỹ năng — đạt Địa Căn (bậc 2) cho hành chủ đạo.</font><br/>";
        } elseif (!$lcCan['ok']) {
            $tishihtml = "<font color='#cc0000'>⚠ " . $lcCan['msg'] . "</font><br/>";
        } elseif (!\player\linhcan_skill_check_cooldown($sid, $lcskill['id'], $dblj)) {
            $tishihtml = "<font color='#cc0000'>Kỹ năng còn hồi chiêu " . \player\linhcan_skill_cooldown_text($sid, $lcskill['id'], $dblj) . "</font><br/>";
        } else {
            $lcMods = \player\linhcan_skill_combat_mods($lcskill);
            $newHp = max(1, intval($player->uhp) - intval($lcCan['mp_cost']));
            \player\changeplayersx('uhp', $newHp, $sid, $dblj);
            $player->uhp = $newHp;
            \player\linhcan_skill_record_use($sid, $lcskill['id'], $dblj);
            $tishihtml = "<font color='#4CAF50'><b>" . $lcMods['icon'] . " Thi triển</b>: <b>{$lcMods['name']}</b> (−{$lcCan['mp_cost']} khí huyết)!</font><br/>";
        }
    }
    // $jineng = new \player\jineng();

    // if (isset($canshu)){
        // switch ($canshu){
            // case 'usejn':
                // $ret = \player\delejnsum($jnid,1,$sid,$dblj);
                // if ($ret){
                    // $jineng = \player\getplayerjineng($jnid,$sid,$dblj);
                    // $tishihtml = "使用技能：$jineng->jnname<br/>";
                // }else{
                    // $tishihtml = "技能数量不足<br/>";
                // }

                // break;
        // }
   // }
    // $player->ugj +=$jineng->jngj;
    // $player->ufy +=$jineng->jnfy;
    // $player->ubj +=$jineng->jnbj;
    // $player->uxx +=$jineng->jnxx;

    $lvc = $player->ulv - $boss->bosslv;
	//攻击伤害计算
    if ($lvc <= 0){
        $lvc = 0;
    }

    $phurt = 0 ;
	$gwsj = mt_rand(1,100);//控制怪物暴击概率
    if ($boss->bossbj >= $gwsj){
        $boss->bossgj = round($boss->bossgj * 2.25);
        $gwbj = 'Trọng kích';
    }

    // === KY NANG LINH CAN — mod trong luot bam (request = 1 luot) ===
    if (!empty($lcMods['crit_add'])) $player->ubj += intval($lcMods['crit_add']);
    if (!empty($lcMods['lifesteal_add'])) $player->uxx += intval($lcMods['lifesteal_add']);
    if (!empty($lcMods['def_pct'])) {
        $lcDefAdd = round($player->ufy * floatval($lcMods['def_pct']) / 100);
        $player->ufy += $lcDefAdd;
        $tishihtml .= "<font color='#ff9800'>🏔 Thổ Hộ Thuật: phòng ngự +{$lcDefAdd}</font><br/>";
    }
    if (!empty($lcMods['heal_pct'])) {
        $lcHeal = max(1, round($player->umaxhp * floatval($lcMods['heal_pct']) / 100));
        $lcNewHp = min(intval($player->umaxhp), intval($player->uhp) + $lcHeal);
        \player\changeplayersx('uhp', $lcNewHp, $sid, $dblj);
        $player->uhp = $lcNewHp;
        $tishihtml .= "<font color='#4CAF50'>💧 Thủy Hộ Thuật: hồi {$lcHeal} khí huyết</font><br/>";
    }

    $phurt = round($boss->bossgj - ($player->ufy * 0.75),0);//boss攻击计算
	
    if ($phurt < $boss->bossgj*0.15){
        $phurt = round($boss->bossgj*0.25);
    }

    $ran = mt_rand(1,200);//控制暴击概率
    if ($player->ubj >= $ran){
        $player->ugj = round($player->ugj * 1.72);
        $pvbbj = 'Bạo kích';
    }
    // Sủng vật chỉ tham chiến khi đang xuất chiến và HP > 0.
    // Nếu HP <= 0 thì không cộng sát thương, người chơi phải cho nghỉ ngơi.
    $cwgj = 0;
    $cw = false;
    if (intval($player->cw) > 0) {
        $cw = \player\getchongwu($player->cw,$dblj);
        if ($cw && intval($cw->cwhp) > 0) {
            $cwgj = round($cw->cwgj * 1);
        }
    }
	 
    // ===== MACH HUYET (Task 11): 4 chi so mach am dung luc cham dot =====
    $hmCs = \player\huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
    $hmBossFy = \player\huyetmach_combat_pierce_def($hmCs['pen'], $boss->bossfy);
    if ($hmBossFy < $boss->bossfy) {
        $tishihtml .= "<font color='#26c6da'>🏹 Âm Duy Mạch (Xuyên Giảm): phòng ngự {$boss->bossname} bị xuyên ".($boss->bossfy - $hmBossFy)." điểm</font><br/>";
    }
    if (!empty($lcMods['ignore_def_pct'])) {
        $hmBossFy = round($hmBossFy * max(0, 100 - floatval($lcMods['ignore_def_pct'])) / 100);
    }
    $gphurt = round($cwgj + $player->ugj - ($hmBossFy * 0.75),0);//主角攻击怪物扣血情况，本身攻击+宠物攻击
    if ($gphurt < $player->ugj*0.15){
        $gphurt = round( $player->ugj * 0.15);
    }
    if (!empty($lcMods) && $lcMods['atk_mult'] != 1.0) {
        $gphurt = round($gphurt * floatval($lcMods['atk_mult']));
        $tishihtml .= "<font color='#ff5722'>" . $lcMods['icon'] . ' ' . $lcMods['name'] . ": x" . $lcMods['atk_mult'] . " sát thương</font><br/>";
    }

    // Tương khắc ngũ hành: ưu tiên element từ trang bị, fallback linh căn.
    $bossElement = isset($boss->element) ? $boss->element : '';
    $elemMult = 1.0;
    $elemSrcElement = '';
    $elemSrcLabel = '';
    $zbElement = '';
    $zbElemSet = isset($player->zb_element_set) ? $player->zb_element_set : array();
    if (!empty($zbElemSet) && function_exists('player\\zhuangbei_pick_element')) {
        $zbElement = \player\zhuangbei_pick_element($zbElemSet, $bossElement);
    }
    if ($zbElement === '') {
        $zbElement = isset($player->zb_element) ? $player->zb_element : '';
    }
    if (!empty($zbElement) && function_exists('player\\zhuangbei_get_element_multiplier')) {
        $elemMult = \player\zhuangbei_get_element_multiplier($zbElement, $bossElement);
        $elemSrcElement = $zbElement;
        $elemSrcLabel = 'Trang bị';
    } elseif (!empty($player->linhcan_element) && function_exists('player\\linhcan_get_element_multiplier')) {
        $elemMult = \player\linhcan_get_element_multiplier($player->linhcan_element, $bossElement);
        $elemSrcElement = $player->linhcan_element;
        $elemSrcLabel = 'Linh căn';
    }
    if ($elemMult != 1.0 && !empty($elemSrcElement) && !empty($bossElement)) {
        $gphurt = round($gphurt * $elemMult);
        $elemIcon = \player\linhcan_element_icon($elemSrcElement);
        $elemBonusText = \player\linhcan_get_element_bonus_text($elemSrcElement, $bossElement);
        $bossElemName = \player\zhuangbei_element_name($bossElement);
        if ($elemMult > 1.0) {
            $tishihtml .= "{$elemIcon} {$elemSrcLabel} khắc {$bossElemName}! <font color='#4CAF50'>{$elemBonusText}</font><br/>";
        } else {
            $tishihtml .= "{$elemIcon} {$elemSrcLabel} bị {$bossElemName} khắc! <font color='#f44336'>{$elemBonusText}</font><br/>";
        }
    }
    $pvbxx = ceil($gphurt * ($player->uxx/200) );//控制吸血强度

    if ($phurt <= 0){
        $hurt = true;
    }
    $hmIncoming = intval($phurt);
    if ($hmCs['reduce'] > 0 && $hmIncoming > 0) {
        $hmBlocked = \player\huyetmach_combat_phys_reduce($hmCs['reduce'], $hmIncoming);
        if ($hmBlocked < $hmIncoming) {
            $tishihtml .= "<font color='#66bb6a'>🧱 Dương Duy Mạch (Giảm Vật Lý): đòn của {$boss->bossname} bị chặn ".($hmIncoming - $hmBlocked)." sát thương</font><br/>";
            $hmIncoming = $hmBlocked;
        }
    }
    if ($hmCs['dodge'] > 0 && $hmIncoming > 0 && \player\huyetmach_combat_roll_dodge($hmCs['dodge'])) {
        $hmIncoming = 0;
        $tishihtml .= "<font color='#90caf9'>💨 Âm Kiều Mạch (Né Tránh): {$boss->bossname} vung trúng khoảng không</font><br/>";
    }
    $phurt = $hmIncoming;

    if ($phurt < $pvbxx){
        $pvbxx = $phurt - 7;

        if ($pvbxx<0){
            $pvbxx = 0;
			
        }
    }
	$sb = mt_rand(1,100);//引入闪避随机数
	if ($sb > 80){
		$gphurt = 0;
		$pvbxx = 0;
		$pvbsb = '(Né tránh)';
	}
	
	

    $cw = \player\getchongwu($player->cw,$dblj);
    $stmt = $dblj->prepare("UPDATE boss SET bosshp = bosshp - :dmg WHERE bossid = :bossid");
    $stmt->execute(array(':dmg' => $gphurt, ':bossid' => $bossid));
    $boss = player\getboss($bossid,$dblj);

    // Thêm mô tả đòn đánh: mô tả chi tiết giàu hình ảnh khi đánh BOSS.
    if (isset($pvbsb) && $pvbsb != '') {
        $tishihtml .= "<font color='#90caf9'>Ngươi vung chiêu hướng vào {$boss->bossname}, vậy mà hắn vẹo mình 'xoẹt' một cái né sang bên, đòn đánh trượt vào khoảng không!</font><br/>";
    } elseif ($gphurt > 0) {
        $bossHitDesc = array(
            "Ngươi tung mình nhảy vọt lên, thân ảnh xoay tít trong không trung như bánh xe, xong 'vun vút' chém thẳng về phía mắt trái của {$boss->bossname}",
            "Ngươi hét lên một tiếng, sát khí bùng nổ, chưởng ảnh phân chia như sao băng, đánh 'bùng' một tiếng giáng mạnh xuống trên đầu {$boss->bossname}!",
            "Ngươi thét dài, thân pháp lóe lên như điện, một đạo kiếm khí kinh thiên xé toạc không trung, hung hãn chém thẳng vàophần bụng {$boss->bossname}!"
        );
        $tishihtml .= "<font color='#ffab40'>".$bossHitDesc[mt_rand(0, count($bossHitDesc) - 1)]." Kết quả 'xoẹt' một tiếng, rạch ra một vết thương đầm đìa máu, gây <b>{$gphurt}</b> sát thương!</font><br/>";
    }

    if ($boss->bosshp <=0 ){
	    
        $stmt = $dblj->prepare("DELETE FROM boss WHERE bossid = :bossid AND sid = :sid");
        $stmt->execute(array(':bossid' => $bossid, ':sid' => $player->sid));

        // Thông báo hạ gục BOSS: ngươi đã giết chết BOSS!
        $tishihtml .= "<font color='#ff5722'><b>Ngươi đã giết chết BOSS [{$boss->bossname}]!</b></font><br/>";
        $tishihtml .= "<font color='#9e9e9e'>{$boss->bossname} như cây đèn trước gió, thân hình khổng lồ sụp đổ, ma khí tan biến, biến thành tro bụi trong tiếng gầm đau đớn!</font><br/>";

        // Linh căn quest: diệt boss
        if (function_exists('player\\linhcan_quest_update')) {
            \player\linhcan_quest_update($player->sid, 'kill_boss', 1, $dblj);
        }

        // Nhiệm vụ: hạ BOSS
        if (function_exists('player\\nhiemvu_update_all')) {
            $nvHpPct = intval($player->umaxhp) > 0 ? round($player->uhp / $player->umaxhp * 100, 1) : 0;
            \player\nhiemvu_update_all($player->sid, 'boss', 1, $dblj, array(
                'boss_level' => intval($boss->bosslv),
                'player_hp_percent' => $nvHpPct,
            ));
        }

		$stmt = $dblj->prepare("UPDATE mid SET mgtime = :mgtime WHERE mid = :mid");
		$stmt->execute(array(':mgtime' => $nowdate, ':mid' => $player->nowmid));
		//$sql = "update boss set bosshp = bossmaxhp  WHERE bossid='$bossid'";//给boss加血
		//$dblj->exec($sql);
		$sjyxb1 = mt_rand(1,2000);
		$sjyxb = mt_rand(1,5);
        $yxb = round($boss->bosslv*$sjyxb*30) + $sjyxb*110+$sjyxb1;//游戏币计算
        if ($hurt || $lvc >=35){
            $yxb = 0;//大于35五级游戏币为零
        }
		
		$sjczb = mt_rand(1,5);
		$sj = $sjczb+ round($boss->bosslv /10);
        $ret = \player\changeyxb(1,$yxb,$sid,$dblj);//选择1方案（加钱），改变数量=yxb，id，关闭数据库；
		$ret1 = \player\changeczb(1,$sj,$sid,$dblj);//魔石增加
        if ($ret){
            $huode .= "Nhận được: $yxb linh thạch<br/> Nhận được: $sj ma thạch<br>";
        }
        // Kinh nghiệm võ công được cộng ở phần kết quả chiến thắng bên dưới.
        // Không cộng tại đây để tránh nhận EXP võ công 2 lần.

        // $taskarr = \player\getplayerrenwu($sid,$dblj);//这里是任务情况
        // \player\changerwyq1(2,$boss->gyid,1,$sid,$dblj);
        // for ($i=0;$i<count($taskarr);$i++){
            // $rwyq = $taskarr[$i]['rwyq'];
            // $rwid = $taskarr[$i]['rwid'];
            // $rwzl = $taskarr[$i]['rwzl'];
            // $rwzt = $taskarr[$i]['rwzt'];
            // if ($rwyq==$boss->gyid && $rwzl==2 && $rwzt!=3){
                // $rwnowcount = $taskarr[$i]['rwnowcount']+ 1;
                // $rwts = $taskarr[$i]['rwname'].'('.$rwnowcount."/".$taskarr[$i]['rwcount'].')<br/>';
                // break;
            // }
        // }
	
$jisha = <<<HTML

<IMG width='15' height='15' src='./images/jisha.png' >

HTML;

		//装备计算
		$xyz =  $player->tfxy;//天赋幸运加入

        $sjjv = mt_rand(1,120-$xyz);
		
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->dljv) >=$sjjv && $boss->bosszb != ''){
            $retzb = \player\zhuangbei_config_rows_by_ids($boss->bosszb);
            if (!empty($retzb)){
                $sjdl = mt_rand(0,count($retzb)-1);
                $zbname = $retzb[$sjdl]['zbname'];
                $zbid = $retzb[$sjdl]['zbid'];
                $zbnowid = player\addzb($sid,$zbid,$dblj);
				$zbys = \player\getzbkzb($zbid,$dblj);//获取装备ID地址，这是用来获取色彩
                $chakanzb = $encode->encode("cmd=chakanzb&zbnowid=$zbnowid&uid=$player->uid&sid=$sid");
			    $huode .= "Nhận được: ".'<a href="?cmd='.$chakanzb.'"><font color='.$zbys->zbys.' >'.$zbname .'</font></a><br>';//装备要素
			        $zbpz = $zbys->zbgj + $zbys->zbfy + $zbys->zbxx +$zbys->zbbj ;//写一个公告，获得装备
					if ($zbpz >=5){
			        $sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
                    $stmt = $dblj->prepare($sql);
					
$playerMsgSafe = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
$bossMsgSafe = htmlspecialchars($boss->bossname, ENT_QUOTES, 'UTF-8');
$tz = <<<HTML

					[{$playerMsgSafe}]{$jisha}[{$bossMsgSafe}] Nhận được <a href='?cmd=zbinfo_sys&zbid=$zbys->zbid&sid=$sid' style="background-color: #f6f7f7;padding: 0px 0px;"><font style="color:{$zbys->zbys}">[{$zbname}]</font></a> làm cho nhiều tiện nhân hâm mộ.
HTML;
                    $stmt->execute(array('【Hệ Thống】',$tz,'0'));//系统公告
					}
            }
        }
		//道具计算
        $sjjv = mt_rand(1,100);
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->djjv) >= $sjjv && $boss->bossdj != ''){
            $retdj = \player\daoju_config_rows_by_ids($boss->bossdj);
            if (!empty($retdj)){
                $sjdj = mt_rand(0,count($retdj)-1);
                $djname = $retdj[$sjdj]['djname'];
                $djid = $retdj[$sjdj]['djid'];
                if ($djid == 1 && $lvc == 0){
					
                    goto yp;
                }
                $djsum = mt_rand(3,12);
                \player\adddj($sid,$djid,$djsum,$dblj);
                $huode .= "Nhận được: <font class='djys'>$djname x$djsum</font><br>";//色彩要素

             }
        }
        yp:
        $sjjv = mt_rand(1,100);
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->ypjv) >= $sjjv && $boss->bossyp != ''){
            $retyp = \player\yaopin_config_rows_by_ids($boss->bossyp);
            if (!empty($retyp)){
                $sjyp = mt_rand(0, count($retyp) - 1);
                $ypname = $retyp[$sjyp]['ypname'];
                $ypid = $retyp[$sjyp]['ypid'];
                $ypsum = mt_rand(1, 2);//药品掉落数量
                \player\addyaopin($sid,$ypid,$ypsum,$dblj);
                $huode .= "Nhận được: <div class='ypys'>$ypname x$ypsum</div>";
            }
        }
       // $boss->bossexp = round($boss->bossexp / ($lvc+1),0);//经验计算
	    $boss->bossexp = round($boss->bossgj / 2+$player->ugj/2+$player->ufy/2,0);
        if($boss->bossexp < 3){
            $boss->bossexp = 3;
        }
        $zdjg = 1;
    }
	
    $pzssh = $phurt - $pvbxx;
	
    $stmt = $dblj->prepare("UPDATE game1 SET uhp = GREATEST(0, uhp - :dmg) WHERE sid = :sid");
    $stmt->execute(array(':dmg' => $pzssh, ':sid' => $sid));
	
    $player =  player\getplayer($sid,$dblj);
	$cwxl = round($phurt*2.2);// Sát thương sủng vật phải nhận
    if (intval($player->cw) > 0) {
        \player\subchongwuhp($player->cw, $cwxl, $dblj);
        $cw = \player\getchongwu($player->cw,$dblj);
        if ($cw && intval($cw->cwhp) > 0){
            $cwts="(-".$cwxl.')';
        }else{
            $cwts = '<font color="#cc0000"> Trọng thương, cần nghỉ ngơi</font>';
        }
    } else {
        $cwts = '';
    }
	
	
	
	
    if ($player->uhp <= 0){//判断战斗经过，血量过低时候
        $zdjg = 0;//跳转到$zdjg 战斗经过 
    }
}

if ($player->yp1!=0){
    $yaopin = \player\getplayeryaopin($player->yp1,$sid,$dblj);
    $ypname1 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp2!=0){
    $yaopin = \player\getplayeryaopin($player->yp2,$sid,$dblj);
    $ypname2 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp3!=0){
    $yaopin = \player\getplayeryaopin($player->yp3,$sid,$dblj);
    $ypname3 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}

// if ($player->jn1!=0){
    // $jineng = \player\getplayerjineng($player->jn1,$sid,$dblj);
    // if ($jineng){
        // $jnname1 = $jineng->jnname.'('.$jineng->jncount.')';
    // }
// }
// if ($player->jn2!=0){
    // $jineng = \player\getplayerjineng($player->jn2,$sid,$dblj);
    // if ($jineng){
        // $jnname2 = $jineng->jnname.'('.$jineng->jncount.')';
    // }
// }
// if ($player->jn3!=0){
    // $jineng = \player\getplayerjineng($player->jn3,$sid,$dblj);;
    // if ($jineng){
        // $jnname3 = $jineng->jnname.'('.$jineng->jncount.')';
    // }
// }

if (isset($zdjg)){//这里是战斗经过，有三个0 1 -1 选择
    $playerSafe = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
    $bossSafe = htmlspecialchars($boss->bossname, ENT_QUOTES, 'UTF-8');
    switch ($zdjg){
        case 1:

            // Áp dụng buff công pháp: exp_bonus
            $cp_exp_bonus = 0;
            if (!empty($player->cp_effects)) {
                foreach ($player->cp_effects as $cpEff) {
                    if ($cpEff['type'] === 'exp_bonus') $cp_exp_bonus += $cpEff['value'];
                }
            }
            $baseExp = intval($boss->bossexp);
            $cpExpGain = ($cp_exp_bonus > 0) ? max(1, intval($baseExp * $cp_exp_bonus / 100)) : 0;
            $finalExp = $baseExp + $cpExpGain;
            
            // Giảm EXP do đan độc
            $toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
            if ($toxinPenalty > 0) {
                $finalExp = max(1, intval($finalExp * (1 - $toxinPenalty / 100)));
            }

            player\changeexp($sid,$dblj,$finalExp);
            player\changecwexp($player->cw,$finalExp,$dblj);
            $huode.='Nhận được: '.$finalExp.' tu vi';
            if ($cpExpGain > 0) {
                $huode .= ' <font color="#ff9800">[Công pháp +'.$cpExpGain.']</font>';
            }

            $cpExpReward = max(1, intval(round($baseExp * 0.02)));
            $cpExpTotal = \player\congphap_add_exp_to_equipped($sid, $dblj, $cpExpReward);
            if ($cpExpTotal > 0) {
                $huode .= '<br/>Nhận được: <font color="#4caf50">+' . $cpExpTotal . ' kinh nghiệm công pháp</font>';
            }
            $huode .= '<br/>';

            // Cộng kinh nghiệm võ công khi đánh thắng BOSS.
            // Công thức cân bằng:
            // - Tối thiểu 5 EXP.
            // - Không vượt quá cấp BOSS.
            // - Không vượt quá 1% thanh kinh nghiệm võ công hiện tại.
            // - Khi thanh EXP đã đầy thì không cộng thêm, yêu cầu người chơi tu luyện nâng cấp.
            if (isset($player->wugong) && intval($player->wugong) > 0) {
                $activeWgid = intval($player->wugong);
                $cxwg = \player\wgcx($activeWgid, $sid, $dblj);

                if ($cxwg) {
                    $wgxlNow = intval($cxwg->wgxl);
                    $wgxlMax = max(1, intval($cxwg->wgxlmax));

                    if ($wgxlNow >= $wgxlMax) {
                        $huode .= 'Kinh nghiệm võ công đã đầy, hãy tu luyện để nâng cấp.<br/>';
                    } else {
                        $bossLvForWg = isset($boss->bosslv) ? max(1, intval($boss->bosslv)) : 1;

                        $wgExp = max(
                            5,
                            min(
                                $bossLvForWg,
                                intval(round($wgxlMax * 0.01))
                            )
                        );

                        $newWgExp = min($wgxlMax, $wgxlNow + $wgExp);
                        $realWgExp = $newWgExp - $wgxlNow;

                        if ($realWgExp > 0) {
                            $stmt = $dblj->prepare("UPDATE playerwugong SET wgxl = :wgxl WHERE wgid = :wgid AND sid = :sid");
                            $stmt->execute(array(':wgxl' => $newWgExp, ':wgid' => $activeWgid, ':sid' => $sid));

                            $huode .= "Nhận được: <font color='green'>+$realWgExp kinh nghiệm võ công</font> ($newWgExp/$wgxlMax)<br/>";
                        } else {
                            $huode .= 'Kinh nghiệm võ công đã đầy, hãy tu luyện để nâng cấp.<br/>';
                        }
                    }
                }
            }
		$stmt = $dblj->prepare("UPDATE boss SET bossmaxhp = bossmaxhp + 100 WHERE bossid = :bossid");
		$stmt->execute(array(':bossid' => $bossid));
		$stmt = $dblj->prepare("UPDATE boss SET bossgj = bossgj + 5 WHERE bossid = :bossid");
		$stmt->execute(array(':bossid' => $bossid));

            $html = <<<HTML
<div class="boss-result-page boss-result-win boss-victory-page">
    <div class="boss-victory-card">
        <div class="boss-victory-aura"></div>
        <div class="boss-victory-topline">
            <span>✦ TRẤN ÁP MA THÚ ✦</span>
        </div>
        <div class="boss-victory-seal">Thắng</div>
        <div class="boss-victory-title">Chiến Thắng Tuyệt Đối</div>
        <div class="boss-victory-subtitle">$bossSafe đã bị đánh bại, ma khí bị phong ấn dưới chân ngươi.</div>

        <div class="boss-victory-banner">
            <div class="boss-victory-banner-left">
                <span>Đối thủ bị hạ gục</span>
                <b>$bossSafe</b>
            </div>
            <div class="boss-victory-banner-right">ĐÃ DIỆT</div>
        </div>

        <div class="boss-victory-loot">
            <div class="boss-victory-loot-title">Chiến lợi phẩm nhận được</div>
            <div class="boss-victory-loot-body">$huode $rwts</div>
        </div>

        <div class="boss-victory-note">Kinh nghiệm võ công đã được tích lũy. Hãy tiếp tục tu luyện để đột phá cảnh giới.</div>
        <a class="boss-victory-btn" href="?cmd=$gonowmid">Trở về Đại Sảnh</a>
    </div>
</div>
HTML;
            break;
        case 0:
		    if ($player->uhp <= 0){
			$sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array('【Hệ Thống】',"[{$bossSafe}] chọc ghẹo [{$playerSafe}]...",'0'));
			}
            $html = <<<HTML
<div class="boss-result-page boss-result-lose">
    <div class="boss-result-card">
        <div class="boss-result-mark">✖ THẤT BẠI ✖</div>
        <div class="boss-result-title">Bị $bossSafe đánh lui</div>
        <div class="boss-result-desc">Khí huyết hỗn loạn, hãy khôi phục rồi quay lại khiêu chiến.</div>
        <a class="boss-result-btn danger" href="?cmd=$gonowmid">Rút về Đại Sảnh</a>
    </div>
</div>
HTML;
            break;
        case -1:
            $html = <<<HTML
<div class="boss-result-page boss-result-lose">
    <div class="boss-result-card">
        <div class="boss-result-mark">⚠ TRỌNG THƯƠNG ⚠</div>
        <div class="boss-result-title">Không thể tiếp tục chiến đấu</div>
        <div class="boss-result-desc">Sinh lực đã cạn, cần hồi phục trước khi trở lại.</div>
        <a class="boss-result-btn danger" href="?cmd=$gonowmid">Trở về Đại Sảnh</a>
    </div>
</div>
HTML;
            break;
    }
}

//判断，执行无生命值大于零，可进行战斗
else{
    if (isset($gphurt) && $gphurt>0){
        $ghurt='-'.$gphurt;
    }else{
        $ghurt='';
    }
    if (isset($cwhurt) && $cwhurt>0){
        $cwhurt='-'.$cwhurt;
    }else{
        $cwhurt='';
    }
    if (isset($phurt) && $phurt>0){
        $phurt='-'.$phurt;
    }else{
        $phurt='';
    }

    if ($pvbxx>0){
        $pvbxx="(+".$pvbxx.')';
    }else{
        $pvbxx = '';
    }

    if ($player->cw!=0){
        $cw = \player\getchongwu($player->cw,$dblj);
		
        if ($cwhurt!='' || $cw->cwhp>0){
            $cwNameSafe = htmlspecialchars($cw->cwname, ENT_QUOTES, 'UTF-8');
            $cwhtml=<<<HTML
                <div class="battle-log-icon">🐾</div>
                <div class="battle-log-info">
                    <div class="battle-log-label">Linh thú trợ chiến</div>
                    <div class="battle-log-name">$cwNameSafe <em>Lv.$cw->cwlv</em></div>
                    <div class="battle-log-mini">
                        <span>HP <b>$cw->cwhp / $cw->cwmaxhp</b> <strong>$cwhurt$cwts</strong></span>
                        <span>Công <b>$cw->cwgj</b></span>
                        <span>Phòng <b>$cw->cwfy</b></span>
                    </div>
                </div>
HTML;
        }

    }

if($boss->bosshp >=10000&&$boss->bosshp<11000){
$sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
$stmt = $dblj->prepare($sql);
$stmt->execute(array('【Mới nhất】',"Có người phát hiện [{$player->uname}] đang đánh {$boss->bossname}，hãy cùng chờ xem ai sẽ chiến thắng!",'0'));
}
if($boss->bosshp <2200 && $boss->bosshp>1700){
$sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
$stmt = $dblj->prepare($sql);
$stmt->execute(array('【Mới nhất】',"Đoạt BOSS, [{$player->uname}] sắp tiêu diệt {$boss->bossname}, sẽ rơi xuống bảo vật gì đây?",'0'));
}


$tp = $encode->encode("cmd=taopao&bossid=$bossid&sid=$sid");
if ($cmd == 'taopao'){
	$sji = mt_rand(1,100);
    if ($sji>= 80 ){
		
        $tx = "Bỏ chạy thành công!";
		
		
		header("refresh:1;url=?cmd=$gonowmid");//延迟跳转1秒
    }else{
		$tx = 'Bỏ chạy thất bại! Ăn l zồi!<br>';
	}
}

$yaopin = \player\getplayeryaopin($player->yp1,$sid,$dblj);
$ypname1 = $yaopin->ypname.'('.$yaopin->ypsum.')';
$yaopin = \player\getplayeryaopin($player->yp2,$sid,$dblj);
$ypname2 = $yaopin->ypname.'('.$yaopin->ypsum.')';
$yaopin = \player\getplayeryaopin($player->yp3,$sid,$dblj);
$ypname3 = $yaopin->ypname.'('.$yaopin->ypsum.')';
$bossHpPercent = 0;
if (intval($boss->bossmaxhp) > 0) {
    $bossHpPercent = round(($boss->bosshp / $boss->bossmaxhp) * 100, 1);
}
if ($bossHpPercent < 0) $bossHpPercent = 0;
if ($bossHpPercent > 100) $bossHpPercent = 100;

$playerHpPercent = 0;
if (intval($player->umaxhp) > 0) {
    $playerHpPercent = round(($player->uhp / $player->umaxhp) * 100, 1);
}
if ($playerHpPercent < 0) $playerHpPercent = 0;
if ($playerHpPercent > 100) $playerHpPercent = 100;

$bossStatus = '';
if ($pvbsb != '') $bossStatus .= '<span class="battle-tag dodge">'.$pvbsb.'</span>';
if ($pvbbj != '') $bossStatus .= '<span class="battle-tag crit">'.$pvbbj.'</span>';
if ($ghurt != '') $bossStatus .= '<span class="battle-damage">'.$ghurt.'</span>';

$playerStatus = '';
if ($gwbj != '') $playerStatus .= '<span class="battle-tag crit">'.$gwbj.'</span>';
if ($phurt != '') $playerStatus .= '<span class="battle-damage">'.$phurt.'</span>';
if ($pvbxx != '') $playerStatus .= '<span class="battle-heal">'.$pvbxx.'</span>';

$cwBlock = '';
if ($cwhtml != '') {
    $cwBlock = '<div class="battle-log-pet">'.$cwhtml.'</div>';
}

$skillBlock = '';
if ($tishi != '') {
    $skillBlock = '<div class="battle-skill-line"><span>Võ học đang vận chuyển</span><b>'.$tishi.'</b></div>';
}

$noticeBlock = '';
if ($tishihtml != '' || $cwresthtml != '' || $tx != '') {
    $noticeBlock = '<div class="battle-notice">'.$tishihtml.$cwresthtml.$tx.'</div>';
}

$playerNameSafe = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
$bossNameSafe = htmlspecialchars($boss->bossname, ENT_QUOTES, 'UTF-8');

$html=<<<HTML
<div class="boss-battle-page boss-battle-horizontal">
    <div class="boss-battle-shell">
        <div class="boss-battle-header">
            <div>
                <span class="boss-battle-kicker">Huyết chiến BOSS</span>
                <h1>Khiêu Chiến Sinh Tử</h1>
            </div>
            <div class="boss-battle-warning">Ma khí đang bạo động · Không được chủ quan</div>
        </div>

        <div class="boss-battle-arena">
            <div class="arena-row arena-boss-row">
                <div class="arena-side-label">MA THÚ TRẤN ẢI</div>
                <div class="arena-fighter boss-line">
                    <div class="arena-avatar boss"><img src="./images/cqgs.png" alt="Boss"></div>
                    <div class="arena-main">
                        <div class="arena-name danger">$bossNameSafe <em>Lv.$boss->bosslv</em></div>
                        <div class="arena-hp-head"><span>Sinh lực BOSS</span><b>$boss->bosshp / $boss->bossmaxhp</b></div>
                        <div class="arena-hp danger"><i style="width:{$bossHpPercent}%"></i><span>{$bossHpPercent}%</span></div>
                        <div class="arena-stats">
                            <div><span>Công kích</span><b>$boss->bossgj</b></div>
                            <div><span>Phòng ngự</span><b>$boss->bossfy</b></div>
                            <div><span>Áp lực</span><b>Rất cao</b></div>
                        </div>
                        <div class="battle-status-line">$bossStatus</div>
                    </div>
                </div>
            </div>

            <div class="arena-vs-line">
                <span></span><b>VS</b><span></span>
            </div>

            <div class="arena-row arena-player-row">
                <div class="arena-side-label player-label">NGƯỜI KHIÊU CHIẾN</div>
                <div class="arena-fighter player-line">
                    <div class="arena-avatar player">🧑</div>
                    <div class="arena-main">
                        <div class="arena-name player">$playerNameSafe <em>Lv.$player->ulv</em></div>
                        <div class="arena-hp-head"><span>Sinh lực</span><b>$player->uhp / $player->umaxhp</b></div>
                        <div class="arena-hp player"><i style="width:{$playerHpPercent}%"></i><span>{$playerHpPercent}%</span></div>
                        <div class="arena-stats">
                            <div><span>Công kích</span><b>$player->ugj</b></div>
                            <div><span>Phòng ngự</span><b>$player->ufy</b></div>
                            <div><span>Võ học</span><b>$tishi</b></div>
                        </div>
                        <div class="battle-status-line">$playerStatus</div>
                    </div>
                </div>
            </div>
        </div>

        $noticeBlock

        <div class="battle-log-card">
            <div class="battle-log-title">Diễn biến chiến đấu</div>
            <div class="battle-log-body">
                <div class="battle-log-stack">
                    $cwBlock
                    <div class="battle-log-row battle-log-danger">
                        <div class="battle-log-icon">☠</div>
                        <div class="battle-log-info">
                            <div class="battle-log-label">Áp lực chiến trường</div>
                            <div class="battle-log-text">BOSS đang khóa chặt khí tức của ngươi. Mỗi lần ra tay đều có thể quyết định sinh tử.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="battle-actions">
            <a class="battle-btn attack" href="?cmd=$pgjcmd">⚔ Tấn Công</a>
            {$lcskillHtmlBoss}
            <a class="battle-btn escape" href="?cmd=$tp">🏃 Chạy Thôi</a>
        </div>

        <div class="battle-items">
            <a href="?cmd=$useyp1">$ypname1</a>
            <a href="?cmd=$useyp2">$ypname2</a>
            <a href="?cmd=$useyp3">$ypname3</a>
        </div>
    </div>
</div>
HTML;
}
echo $html

?>