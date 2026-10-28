<?php
require_once __DIR__ . '/../class/function/club_quest.php';
$player =  player\getplayer($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$player->sid");
$cxmid = \player\getmid($player->nowmid,$dblj);
$cxqy = \player\getqy($cxmid->mqy,$dblj);
$gorehpmid = $encode->encode("cmd=gomid&newmid=$cxqy->mid&sid=$player->sid");
$rwts = '';
$cwhtml='';
$cwresthtml='';
$pgjcmd = $encode->encode("cmd=pvegj&gid=$gid&sid=$player->sid&nowmid=$nowmid");
$guaiwu = player\getguaiwu($gid,$dblj);
$yguaiwu = new \player\guaiwu();

if ($guaiwu->gyid){
    $yguaiwu = player\getyguaiwu($guaiwu->gyid,$dblj);
}
$huode = '';
$useyp1 = $encode->encode("cmd=pve&canshu=useyp&ypid=$player->yp1&sid=$sid&gid=$gid&nowmid=$nowmid");
$useyp2 = $encode->encode("cmd=pve&canshu=useyp&ypid=$player->yp2&sid=$sid&gid=$gid&nowmid=$nowmid");
$useyp3 = $encode->encode("cmd=pve&canshu=useyp&ypid=$player->yp3&sid=$sid&gid=$gid&nowmid=$nowmid");

$usejn1 = $encode->encode("cmd=pvegj&canshu=usejn&jnid=$player->jn1&sid=$sid&gid=$gid&nowmid=$nowmid");
$usejn2 = $encode->encode("cmd=pvegj&canshu=usejn&jnid=$player->jn2&sid=$sid&gid=$gid&nowmid=$nowmid");
$usejn3 = $encode->encode("cmd=pvegj&canshu=usejn&jnid=$player->jn3&sid=$sid&gid=$gid&nowmid=$nowmid");

$lcskillCmd = ''; $lcskillHtml = '';
if (function_exists('player\\linhcan_skill_for_stage')) {
    $lcs0 = \player\linhcan_skill_for_stage($sid, $dblj);
    if ($lcs0) {
        $lcskillCmd = $encode->encode("cmd=pvegj&canshu=uselcskill&sid=$sid&gid=$gid&nowmid=$nowmid");
        $lcskillHtml = "<a href=\"?cmd=$lcskillCmd\">{$lcs0['icon']} {$lcs0['name']}</a>";
    }
}

$ypname1 = 'Thuốc 1';
$ypname2 = 'Thuốc 2';
$ypname3 = 'Thuốc 3';

$jnname1 = 'Phù lục 1';
$jnname2 = 'Phù lục 2';
$jnname3 = 'Phù lục 3';

    $cwhurt = '';
    $tishihtml='';

    // Diễn biến chiến đấu (chuyển từ BOSS DÃ NGOẠI): chỉ hiện khi vừa bước vào trận lần đầu (cmd=pve)
    if ($cmd == 'pve'){
        $fightDescLines = array(
            "Ánh mắt {$yguaiwu->gname} đã đặt thẳm lên trên mặt đất — tiếng rìa khí vang lên, cuộc tranh đấu dưới hoang mộng sẽ khởi đầu!",
            "{$yguaiwu->gname} hiện hữu dưới vương khói, hơi ấm võ khí lướt nhẹ. Linh hồn sát thủ sẽ dần lộ rõ bản chất.",
            "Tiếng gầm rú vang khắp nơi, {$yguaiwu->gname} cười tươi đón chờ. Những kẻ dám xông phạm, hãy sẵn sàng giao thoa sinh tử."
        );
        $tishihtml = $fightDescLines[mt_rand(0, count($fightDescLines) - 1)];
    }

    if ($nowmid!=$player->nowmid){
    $html = <<<HTML
        Chơi game bình thường nhá !<br/>
        <br/>
        <a href="?cmd=$gonowmid">Đại Sảnh</a>
HTML;
    echo $html;
    exit();
}

if (($guaiwu->sid!=$player->sid && $guaiwu->sid!='') || ($guaiwu->gid=='')){
        $html = <<<HTML

<div class="pve-notice-wrap">
    <div class="pve-notice-card">
        <div class="pve-notice-head">
            <div class="pve-notice-icon">!</div>
            <div class="pve-notice-title">Quái vật đã bị công kích</div>
            <div class="pve-notice-status">Đã có người ra tay trước</div>
        </div>
        <div class="pve-notice-body">
            <div class="pve-notice-text">Quái vật đã bị những người khác công kích.</div>
            <div class="pve-notice-hint">Mời thiếu hiệp luyện tập thêm tốc độ ra tay, lần sau quay lại sớm hơn.</div>
            <div class="pve-notice-actions">
                <a class="pve-notice-btn" href="?cmd=$gonowmid">Đại Sảnh</a>
            </div>
        </div>
    </div>
</div>
HTML;
        exit($html);
}
$pvebj = '';
$pvexx = '';

if (isset($canshu)){

    if ($canshu == 'useyp'){
        $ret = \player\useyaopin($ypid,1,$sid,$dblj);
        $player =  player\getplayer($sid,$dblj);
    }
}
// Khởi tạo trạng thái chiến đấu và phục hồi HP thú cưng khi bắt đầu đánh quái
if($cmd=='pve' && $guaiwu->sid==''){

    if ($player->ulv >= 10 && $player->uhp <=0){
        $zdjg = -1;
    }else{
        $stmt = $dblj->prepare("UPDATE midguaiwu SET sid = :sid WHERE id = :gid");
        $stmt->execute(array(':sid' => $sid, ':gid' => $gid));
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

}elseif ($cmd == 'pvegj' && $gid != 0){
    // Xử lý lượt tấn công của người chơi
    $hurt = false;
    $ghurt = 0;
    // Lưu máu trước lượt để client mô phỏng thanh máu tụt dần
    $pveFromGhp = intval($guaiwu->ghp);
    $pveFromUhp = intval($player->uhp);
    $jineng = new \player\jineng();
    $lcMods = array();
    $pveSkillStamina = 0;

    if (isset($canshu)){
        switch ($canshu){
            case 'usejn':
                $jnStamina = intval(\player\combat_rule('stamina_skill', null, 3));
                $ret = ($player->ustamina >= $jnStamina) ? \player\delejnsum($jnid,1,$sid,$dblj) : false;
                if ($ret) $pveSkillStamina = $jnStamina;
                if ($ret){
                    $jineng = \player\getplayerjineng($jnid,$sid,$dblj);
                    $tishihtml = "<font color='#4CAF50'><b>⚡ Tung chiêu</b>: <b>$jineng->jnname</b>!</font><br/>";
                } else {
                    $tishihtml = "<font color='#cc0000'><b>⚠ Không thể tung chiêu</b>: thể lực không đủ bộc phát!</font><br/>";
                }

                break;

            case 'uselcskill':
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
                    $lcCd = \player\linhcan_skill_cooldown_text($sid, $lcskill['id'], $dblj);
                    $tishihtml = "<font color='#cc0000'>Kỹ năng còn hồi chiêu " . $lcCd . "</font><br/>";
                } elseif ($player->ustamina < \player\combat_rule('stamina_lc_skill', $lcStage, 5)) {
                    $tishihtml = "<font color='#cc0000'>Không đủ thể lực để thi triển kỹ năng Linh Căn (cần " . intval(\player\combat_rule('stamina_lc_skill', $lcStage, 5)) . ").</font><br/>";
                } else {
                    $pveSkillStamina = intval(\player\combat_rule('stamina_lc_skill', $lcStage, 5));
                    $lcMods = \player\linhcan_skill_combat_mods($lcskill);
                    $newHp = max(1, intval($player->uhp) - intval($lcCan['mp_cost']));
                    \player\changeplayersx('uhp', $newHp, $sid, $dblj);
                    $player->uhp = $newHp;
                    \player\linhcan_skill_record_use($sid, $lcskill['id'], $dblj);
                    $tishihtml = "<font color='#4CAF50'><b>" . $lcMods['icon'] . " Thi triển</b>: <b>{$lcMods['name']}</b> (−{$lcCan['mp_cost']} khí huyết)!</font><br/>";
                }
                break;
        }
    }

    // === THẾ VÕ: Võ Kỹ (thế 1, đánh thường) / Tuyệt Kỹ (thế 2) / Tuyệt Chiêu (thế 3) ===
    $cbState = \player\combat_state_load($sid, $gid, $dblj);
    $cbWg = (isset($player->wugong) && intval($player->wugong) > 0) ? \player\wgcx(intval($player->wugong), $sid, $dblj) : false;
    $cbMoves = \player\combat_wugong_moves($cbWg);
    $cbMove = isset($cbMoves[1]) ? $cbMoves[1] : null;
    $cbTier = (isset($canshu) && $canshu == 'usewg' && isset($wgt)) ? intval($wgt) : 1;
    $energyBase = 1; // không còn dùng: thể lực mỗi đòn lấy từ stamina_cost của thế (combat_stamina_cost)
    if ($cbTier > 1) {
        $cbNote = '';
        if (!isset($cbMoves[$cbTier])) {
            $cbNote = 'Chưa trang bị võ công.';
        } elseif (!$cbMoves[$cbTier]['unlocked']) {
            $cbNote = $cbMoves[$cbTier]['label'] . ' mở khi võ công đạt cấp ' . $cbMoves[$cbTier]['unlock_lv'] . '.';
        } elseif (!$cbState['ok']) {
            $cbNote = 'Chưa khởi tạo dữ liệu thế võ (bảng player_combat_state).';
        } elseif (\player\combat_move_wait($cbState, $cbTier) > 0) {
            $cbNote = '«' . $cbMoves[$cbTier]['name'] . '» còn hồi ' . \player\combat_move_wait($cbState, $cbTier) . ' lượt.';
        } elseif ($player->ustamina < $cbMoves[$cbTier]['stamina_cost']) {
            $cbNote = 'Không đủ thể lực để thi triển «' . $cbMoves[$cbTier]['name'] . '».';
        } else {
            $cbMove = $cbMoves[$cbTier];
        }
        if ($cbNote !== '') {
            $tishihtml .= "<font color='#9e9e9e'>{$cbNote} Chuyển sang đánh thường.</font><br/>";
        }
    }

    $energyCost = $cbMove ? $cbMove['stamina_cost'] : 1;
    // Kỹ năng bí kíp / Linh Căn: lượt này tốn theo kỹ năng (đã kiểm đủ thể lực trước khi dùng)
    if (!empty($pveSkillStamina)) $energyCost = max($energyCost, intval($pveSkillStamina));
    if ($player->ustamina < $energyCost) {
        $tishihtml = "<font color='#cc0000'>Thể lực không đủ để tấn công (cần $energyCost).</font><br/>";
        $player->ustamina = max(0, intval($player->ustamina));
        $stmt = $dblj->prepare("UPDATE game1 SET ustamina = :stamina WHERE sid = :sid");
        $stmt->execute(array(':stamina' => $player->ustamina, ':sid' => $sid));

        $html = <<<HTML
<div class="pve-notice-wrap">
    <div class="pve-notice-card">
        <div class="pve-notice-head">
            <div class="pve-notice-icon">!</div>
            <div class="pve-notice-title">Không đủ thể lực</div>
        </div>
        <div class="pve-notice-body">
            <div class="pve-notice-text">{$tishihtml}</div>
            <div class="pve-notice-actions">
                <a class="pve-notice-btn" href="?cmd=$gonowmid">Đại Sảnh</a>
            </div>
        </div>
    </div>
</div>
HTML;
        echo $html;
        exit();
    } else {
        $player->ustamina -= $energyCost;
        $stmt = $dblj->prepare("UPDATE game1 SET ustamina = :stamina WHERE sid = :sid");
        $stmt->execute(array(':stamina' => $player->ustamina, ':sid' => $sid));

        // Ghi lượt + hồi chiêu của thế vừa dùng.
        if ($cbState['ok']) {
            $cbState['turn']++;
            if ($cbMove && $cbMove['tier'] > 1) {
                $cbState['cd' . $cbMove['tier']] = $cbState['turn'] + $cbMove['cooldown'] + 1;
            }
            \player\combat_state_save($sid, $cbState, $dblj);
        }
        // Chính tuyến: thi triển Tuyệt Kỹ / Tuyệt Chiêu
        if ($cbMove && $cbMove['tier'] > 1 && function_exists('player\\nhiemvu_main_update')) {
            \player\nhiemvu_main_update($sid, 'use_move', $dblj, array('tier' => intval($cbMove['tier'])));
        }

        $player->ugj += $jineng->jngj;
        $player->ufy += $jineng->jnfy;
        $player->ubj += $jineng->jnbj;

        // Áp dụng penalty đan độc (giảm stats)
        $toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
        if ($toxinPenalty > 0 && $cbWg && $cbWg->effect_type == 'poison_immune') {
            $tishihtml .= "<font color='#42a5f5'>Võ công {$cbWg->wgname}: bách độc bất xâm, đan độc không ảnh hưởng</font><br/>";
            $toxinPenalty = 0;
        }
        if ($toxinPenalty > 0) {
            $player->ugj = round($player->ugj * (1 - $toxinPenalty / 100));
            $player->ufy = round($player->ufy * (1 - $toxinPenalty / 100));
            $player->umaxhp = round($player->umaxhp * (1 - $toxinPenalty / 100));
        }
        $player->uxx += $jineng->jnxx;

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

    // Lấy hiệu ứng võ công đang trang bị để áp dụng trong chiến đấu PVE.
    $wgEffectType = '';
    $wgEffectValue = 0;
    $wgEffectName = '';
    $wgid = isset($player->wugong) ? intval($player->wugong) : 0;
    if ($wgid > 0) {
        $cxwg = \player\wgcx($wgid, $sid, $dblj);
        if ($cxwg) {
            $wgEffectType = trim($cxwg->effect_type);
            $wgEffectValue = intval($cxwg->effect_value);
            $wgEffectName = $cxwg->wgname;
        }
    }

    $lvc = $player->ulv - $guaiwu->glv;
	// Tính chênh lệch cấp để giảm phần thưởng và sát thương khi cần
    if ($lvc <= 0){
        $lvc = 0;
    }

    $phurt = 0 ;
	

    $phurt = round($guaiwu->ggj - ($player->ufy * 0.75),0);
	
    if ($phurt < $guaiwu->ggj*0.15){
        $phurt = round($guaiwu->ggj*0.15);
    }

    // Chiêu phản kích của quái theo hệ + bạo kích theo gbj (guaiwu_skill_config.json).
    $cbMob = \player\combat_mob_move(isset($guaiwu->element) ? $guaiwu->element : 'none');
    $cbMobCfg = \player\combat_cfg('guaiwu_skill_config.json');
    if ($cbMob['mult'] != 1.0) {
        $phurt = round($phurt * $cbMob['mult']);
    }
    $cbMobCrit = false;
    $cbGbj = isset($guaiwu->gbj) ? intval($guaiwu->gbj) : 0;
    if ($cbGbj > 0 && mt_rand(1, 1000) <= round($cbGbj * floatval($cbMobCfg['rules']['crit_ratio']) * 10)) {
        $phurt = round($phurt * floatval($cbMobCfg['rules']['crit_mult']));
        $cbMobCrit = true;
    }
    // Thế "guard": giảm sát thương nhận ở lượt này.
    if ($cbMove && $cbMove['effect'] == 'guard' && $phurt > 0) {
        $cbGuard = round($phurt * $cbMove['value'] / 100);
        $phurt = max(1, $phurt - $cbGuard);
        $tishihtml .= "<font color='#42a5f5'>🛡 «{$cbMove['name']}» hộ thể: chặn {$cbGuard} sát thương</font><br/>";
    }
    // Công pháp dmg_reduce: giảm % sát thương nhận vào.
    $cbCpReduce = \player\combat_cp_value($player, 'dmg_reduce');
    if ($cbCpReduce > 0 && $phurt > 0) {
        $cbCpBlock = round($phurt * $cbCpReduce / 100);
        if ($cbCpBlock > 0) {
            $phurt = max(1, $phurt - $cbCpBlock);
            $tishihtml .= "<font color='#80cbc4'>📜 Công pháp hộ thân: giảm {$cbCpBlock} sát thương</font><br/>";
        }
    }

    // Hiệu ứng võ công: giảm sát thương nhận vào.
    if (($wgEffectType == 'damage_reduce' || $wgEffectType == 'control_reduce_damage') && $wgEffectValue > 0 && $phurt > 0) {
        $wgReduceHurt = ceil($phurt * $wgEffectValue / 100);
        $phurt = max(1, $phurt - $wgReduceHurt);
        $tishihtml .= "<font color='#42a5f5'>Võ công {$wgEffectName} phát huy: giảm {$wgReduceHurt} sát thương</font><br/>";
    }

    $ran = mt_rand(1,200);// Kiểm tra khả năng kích hoạt bạo kích
    if ($player->ubj >= $ran){
        $cbCritMult = floatval(\player\combat_rule('crit_mult', null, 1.72));
        if ($cbWg && $cbWg->effect_type == 'crit_dmg' && intval($cbWg->effect_value) > 0) {
            $cbCritMult += intval($cbWg->effect_value) / 100 * \player\combat_passive_scale($cbWg->wgdj);
        }
        $cbCritMult += \player\combat_cp_value($player, 'crit_dmg') / 100;
        $player->ugj = round($player->ugj * $cbCritMult);
        $pvebj = 'Bạo kích';
        $tishihtml .= "<font color='#ffeb3b'><b>Bạo kích!</b> Đòn đánh thần tốc gây thêm sát thương</font><br/>";
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
	 
	 
    // ===== MACH HUYET (Task 11) =====
    $hmCs = \player\huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
    $tpDropPct = isset($player->tianfu_bonus['drop']) ? floatval($player->tianfu_bonus['drop']) : 0;
    // Công pháp drop_rate cộng chung vào % may mắn rơi đồ (chung trần với Thiên Phú).
    $tpDropPct += \player\combat_cp_value($player, 'drop_rate');
    // Hiệu ứng võ công: xuyên phòng / phá giáp làm giảm phòng ngự quái khi tính sát thương.
    $guaiwuFyForDamage = $guaiwu->gfy;
    if (($wgEffectType == 'pierce_def' || $wgEffectType == 'break_armor') && $wgEffectValue > 0) {
        $guaiwuFyForDamage = round($guaiwu->gfy * max(0, 100 - $wgEffectValue) / 100);
        $tishihtml .= "<font color='#42a5f5'>Võ công {$wgEffectName} phát huy: xuyên {$wgEffectValue}% phòng ngự</font><br/>";
    }

    $hmPierceFrom = $guaiwuFyForDamage;
    $guaiwuFyForDamage = \player\huyetmach_combat_pierce_def($hmCs['pen'], $guaiwuFyForDamage);
    if ($guaiwuFyForDamage < $hmPierceFrom) {
        $tishihtml .= "<font color='#26c6da'>🏹 Âm Duy Mạch (Xuyên Giảm): phòng ngự {$guaiwu->gname} bị xuyên ".($hmPierceFrom - $guaiwuFyForDamage)." điểm</font><br/>";
    }
    // Du phong cho duong `debuff` cua 5 hanh tuong lai (Hư Không Chuong da bi xoa).
    if (!empty($lcMods['ignore_def_pct'])) {
        $guaiwuFyForDamage = round($guaiwuFyForDamage * max(0, 100 - floatval($lcMods['ignore_def_pct'])) / 100);
    }
    $gphurt = round($cwgj + $player->ugj - ($guaiwuFyForDamage * 0.75),0);// Tính tổng sát thương người chơi và thú cưng gây lên quái vật
    if ($gphurt < $player->ugj*0.15){
        $gphurt = round( $player->ugj * 0.15);
    }
    if (!empty($lcMods) && $lcMods['atk_mult'] != 1.0) {
        $gphurt = round($gphurt * floatval($lcMods['atk_mult']));
        $tishihtml .= "<font color='#ff5722'>" . $lcMods['icon'] . ' ' . $lcMods['name'] . ": x" . $lcMods['atk_mult'] . " sát thương</font><br/>";
    }

    // Tương khắc ngũ hành: ưu tiên element từ trang bị, fallback linh căn.
    $monsterElement = isset($guaiwu->element) ? $guaiwu->element : '';
    $elemMult = 1.0;
    $elemSrcElement = '';
    $elemSrcLabel = '';
    $zbElement = '';
    $zbElemSet = isset($player->zb_element_set) ? $player->zb_element_set : array();
    if (!empty($zbElemSet) && function_exists('player\\zhuangbei_pick_element')) {
        $zbElement = \player\zhuangbei_pick_element($zbElemSet, $monsterElement);
    }
    if ($zbElement === '') {
        $zbElement = isset($player->zb_element) ? $player->zb_element : '';
    }
    if (!empty($zbElement) && function_exists('player\\zhuangbei_get_element_multiplier')) {
        $elemMult = \player\zhuangbei_get_element_multiplier($zbElement, $monsterElement);
        $elemSrcElement = $zbElement;
        $elemSrcLabel = 'Trang bị';
    } elseif (!empty($player->linhcan_element) && function_exists('player\\linhcan_get_element_multiplier')) {
        $elemMult = \player\linhcan_get_element_multiplier($player->linhcan_element, $monsterElement);
        $elemSrcElement = $player->linhcan_element;
        $elemSrcLabel = 'Linh căn';
    }
    if ($elemMult != 1.0 && !empty($elemSrcElement) && !empty($monsterElement)) {
        $gphurt = round($gphurt * $elemMult);
        $elemIcon = \player\linhcan_element_icon($elemSrcElement);
        $elemBonusText = \player\linhcan_get_element_bonus_text($elemSrcElement, $monsterElement);
        $monsterElemName = \player\zhuangbei_element_name($monsterElement);
        if ($elemMult > 1.0) {
            $tishihtml .= "{$elemIcon} {$elemSrcLabel} khắc {$monsterElemName}! <font color='#4CAF50'>{$elemBonusText}</font><br/>";
        } else {
            $tishihtml .= "{$elemIcon} {$elemSrcLabel} bị {$monsterElemName} khắc! <font color='#f44336'>{$elemBonusText}</font><br/>";
        }
    }

    // Thế võ: xuyên phòng → hệ số → bạo kích chắc chắn → thiêu đốt.
    if ($cbMove) {
        if ($cbMove['effect'] == 'pierce') {
            $gphurt += round($guaiwuFyForDamage * 0.75 * $cbMove['value'] / 100);
        }
        if ($cbMove['mult'] != 1.0) {
            $gphurt = round($gphurt * $cbMove['mult']);
        }
        if ($cbMove['effect'] == 'crit' && $pvebj == '' && mt_rand(1, 100) <= $cbMove['value']) {
            $gphurt = round($gphurt * floatval(\player\combat_rule('crit_mult', null, 1.72)));
            $pvebj = 'Bạo kích';
            $tishihtml .= "<font color='#ffeb3b'><b>Bạo kích!</b> «{$cbMove['name']}» đánh trúng tử huyệt</font><br/>";
        }
        if ($cbMove['effect'] == 'burn') {
            $cbBurn = max(1, round($gphurt * $cbMove['value'] / 100));
            $gphurt += $cbBurn;
            $tishihtml .= "<font color='#ff7043'>🔥 Thiêu đốt thêm {$cbBurn} sát thương</font><br/>";
        }
    }

    // Hiệu ứng võ công: liên kích, gây thêm một phần sát thương công kích.
    if ($wgEffectType == 'combo_hit' && $wgEffectValue > 0 && mt_rand(1,100) <= $wgEffectValue) {
        $wgComboHurt = max(1, round($player->ugj * 0.35));
        $gphurt += $wgComboHurt;
        $tishihtml .= "<font color='#42a5f5'>Võ công {$wgEffectName}: liên kích +{$wgComboHurt} sát thương</font><br/>";
    }

    $pvexx = ceil($gphurt * ($player->uxx/200) );// Tính lượng HP hồi lại từ hiệu ứng hút máu

    // Hiệu ứng võ công: Cửu Âm/âm nhu tăng thêm hút máu theo phần trăm sát thương.
    if ($wgEffectType == 'counter_lifesteal' && $wgEffectValue > 0) {
        $wgLifeSteal = ceil($gphurt * $wgEffectValue / 100);
        $pvexx += $wgLifeSteal;
        $tishihtml .= "<font color='#42a5f5'>Võ công {$wgEffectName}: hút máu thêm +{$wgLifeSteal}</font><br/>";
    }

    if ($cbMove && $cbMove['effect'] == 'lifesteal') {
        $pvexx += ceil($gphurt * $cbMove['value'] / 100);
    }

    if ($phurt <= 0){
        $hurt = true;
    }

    $hmIncoming = intval($phurt);
    if ($hmCs['reduce'] > 0 && $hmIncoming > 0) {
        $hmBlocked = \player\huyetmach_combat_phys_reduce($hmCs['reduce'], $hmIncoming);
        if ($hmBlocked < $hmIncoming) {
            $tishihtml .= "<font color='#66bb6a'>🧱 Dương Duy Mạch (Giảm Vật Lý): chặn ".($hmIncoming - $hmBlocked)." sát thương từ {$guaiwu->gname}</font><br/>";
            $hmIncoming = $hmBlocked;
        }
    }
    if ($hmCs['dodge'] > 0 && $hmIncoming > 0 && \player\huyetmach_combat_roll_dodge($hmCs['dodge'])) {
        $hmIncoming = 0;
        $tishihtml .= "<font color='#90caf9'>💨 Âm Kiều Mạch (Né Tránh): {$guaiwu->gname} vung trúng khoảng không</font><br/>";
    }
    $phurt = $hmIncoming;
    $cbStunned = false;
    if ($phurt > 0 && $cbMove && $cbMove['effect'] == 'stun' && mt_rand(1, 100) <= $cbMove['value']) {
        $phurt = 0;
        $cbStunned = true;
    }
    // Khinh công (escape): né đòn, mạnh dần theo cấp võ công.
    if ($phurt > 0 && $cbWg && $cbWg->effect_type == 'escape' && intval($cbWg->effect_value) > 0) {
        $cbDodge = intval($cbWg->effect_value) * floatval(\player\combat_rule('escape_ratio', null, 0.1)) * \player\combat_passive_scale($cbWg->wgdj);
        if (mt_rand(1, 1000) <= round($cbDodge * 10)) {
            $phurt = 0;
            $tishihtml .= "<font color='#90caf9'>💨 Võ công {$cbWg->wgname}: thân pháp né khỏi đòn của {$guaiwu->gname}</font><br/>";
        }
    }
    // Hồi máu trong lượt: thế "heal" + bị động hp_regen của võ công.
    $cbHeal = 0;
    if ($cbMove && $cbMove['effect'] == 'heal') {
        $cbHeal += round($player->umaxhp * $cbMove['value'] / 100);
    }
    $cbHeal += round($player->umaxhp * \player\combat_cp_value($player, 'hp_regen') / 100);
    if ($cbWg && $cbWg->effect_type == 'hp_regen' && intval($cbWg->effect_value) > 0) {
        $cbHeal += round($player->umaxhp * intval($cbWg->effect_value) * floatval(\player\combat_rule('hp_regen_ratio', null, 0.25)) / 100 * \player\combat_passive_scale($cbWg->wgdj));
    }
    if ($phurt < $pvexx){
        $pvexx = $phurt - 7;

        if ($pvexx<0){
            $pvexx = 0;
			
        }
    }
     $cw = \player\getchongwu($player->cw,$dblj);
    $sql = "update midguaiwu set ghp = ghp - {$gphurt} WHERE id='$gid'";
    $dblj->exec($sql);
    $guaiwu = player\getguaiwu($gid,$dblj);
    // Thêm mô tả trận chiến: mô tả đòn đánh giàu hình ảnh khi gây sát thương lên quái vật.
    $pveHitDesc = array(
        "Bạn tung mình nhảy vọt lên, luồng chân khí cuồn cuộn ngưng tụ nơi đầu ngón tay, một chưởng ấn khổng lồ đè thẳng xuống, in hằn lên thân thể {$guaiwu->gname} một dấu chưởng nóng rực!",
        "Bạn hét to một tiếng, quanh người bùng lên sát khí lạnh thấu xương, thân ảnh lóe lên trong chớp mắt, binh khí trong tay họa thành vô số tàn ảnh, 'vun vút' chém mạnh về phía {$guaiwu->gname}!",
        "Ngươi dậm mạnh xuống đất, mặt đất rạn nứt, một đạo kiếm khí màu vàng xé toạc không trung, mang theo tiếng rít đinh tai nhứt óc xuyên thẳng vào tim {$guaiwu->gname}!"
    );
    $playerToolDesc = '';
    if (!empty($wgEffectName)) {
        $playerToolDesc .= "<b>{$wgEffectName}</b> (võ công)";
    }
    if (isset($canshu) && $canshu == 'usejn' && !empty($jineng->jnname)) {
        $playerToolDesc .= ($playerToolDesc ? ' + ' : '') . "<b>{$jineng->jnname}</b> (phù lục)";
    }

    if ($cbMove) {
        $cbHitText = "{$cbMove['label']} «<b>{$cbMove['name']}</b>»" . ($cbMove['hits'] > 1 ? " ({$cbMove['hits']} đòn)" : '') . ": " . \player\combat_text($cbMove['text'], $guaiwu->gname);
        $tishihtml .= "<font color='#ffab40'>⚔ {$cbHitText} <b>Gây {$gphurt} sát thương</b>.</font><br/>";
    } else {
        $tishihtml .= "<font color='#ffab40'>" . ($playerToolDesc ? "⚔ Dùng {$playerToolDesc}. " : '') . $pveHitDesc[mt_rand(0, count($pveHitDesc) - 1)] . " <b>Gây {$gphurt} sát thương</b> lên {$guaiwu->gname}.</font><br/>";
    }
    if ($guaiwu->ghp<=0){// Quái vật bị tiêu diệt, bắt đầu xử lý phần thưởng
        $sql = "delete from midguaiwu where id = $gid AND sid='$player->sid'";// Xóa quái vật khỏi bản đồ sau khi bị tiêu diệt
        $dblj->exec($sql);

        // Thông báo hạ gục: ngươi đã giết chết quái vật!
        $tishihtml .= "<font color='#ff5722'><b>Ngươi đã giết chết {$guaiwu->gname}!</b></font><br/>";
        $tishihtml .= "<font color='#9e9e9e'>{$guaiwu->gname} đã như ngọn nến trước gió, lảo đảo ngã xuống, tan thành tro bụi.</font><br/>";

        // Linh căn quest: diệt quái
        if (function_exists('player\\linhcan_quest_update')) {
            \player\linhcan_quest_update($player->sid, 'kill_monsters', 1, $dblj);
        }

        // Nhiệm vụ: diệt quái
        if (function_exists('player\\nhiemvu_update_all')) {
            $nvRv = \player\nhiemvu_update_all($player->sid, 'kill', 1, $dblj, array(
                'monster_level' => intval($guaiwu->glv),
                'monster_id' => intval($guaiwu->gyid),
            ));
            if (!empty($nvRv['ok'])) $tishihtml .= "<font color='#facc15'><b>📜 " . $nvRv['msg'] . "</b></font><br/>";
        }

		$sjyxb = mt_rand(1,200);
        $yxb = round($guaiwu->glv*5) + $sjyxb;// Tính số linh thạch người chơi nhận được
        
		// $hurt = true khi sat thuong cua quai bi lam tron xuong 0. San 15% o ben tren
		// = round(ggj*0.15), nen moi quai co ggj <= 3 LUON luon tra 0 linh thạch,
		// khong phụ thuộc phòng ngự người chơi. (KHÔNG phải "người chơi không gây được
		// sát thương" như comment cũ.)
		if ($hurt) {
			$yxb = 0;
		}
		
		if ($lvc >= 30) {
			$yxb = 0;
			$huode .= "<font color='orange'>Cấp nhân vật quá cao không nhận được linh thạch.</font><br/>";
		}

        // Công pháp stone_bonus: thêm % linh thạch.
        $cbCpStone = \player\combat_cp_value($player, 'stone_bonus');
        if ($cbCpStone > 0 && $yxb > 0) {
            $yxb += max(1, round($yxb * $cbCpStone / 100));
        }
        $ret = \player\changeyxb(1,$yxb,$sid,$dblj);
        if ($ret && $yxb > 0){
            $huode .= "Nhận được linh thạch: $yxb<br/>";
        }

        // Hiệu ứng võ công: thuận thủ khiên dương, có tỷ lệ nhận thêm linh thạch.
        if ($wgEffectType == 'steal_reward' && $wgEffectValue > 0 && $yxb > 0 && mt_rand(1,100) <= $wgEffectValue) {
            $extraYxb = max(1, round($yxb * 0.12));
            \player\changeyxb(1, $extraYxb, $sid, $dblj);
            $huode .= "Võ công {$wgEffectName}: thuận tay lấy thêm linh thạch +{$extraYxb}<br/>";
        }
        $taskarr = \player\getplayerrenwu($sid,$dblj);
        \player\changerwyq1(2,$guaiwu->gyid,1,$sid,$dblj);
        for ($i=0;$i<count($taskarr);$i++){
            $rwyq = $taskarr[$i]['rwyq'];
            $rwid = $taskarr[$i]['rwid'];
            $rwzl = $taskarr[$i]['rwzl'];
            $rwzt = $taskarr[$i]['rwzt'];
            if ($rwyq==$guaiwu->gyid && $rwzl==2 && $rwzt!=3){
                $rwnowcount = $taskarr[$i]['rwnowcount']+ 1;
                $rwts = $taskarr[$i]['rwname'].'('.$rwnowcount."/".$taskarr[$i]['rwcount'].')<br/>';
                break;
            }
        }
		
$jisha = <<<HTML

<IMG width='15' height='15' src='./images/jisha.png' >

HTML;
		
		
		
		// Kiểm tra và xử lý rơi trang bị

        $sjjv = mt_rand(1,120);
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], \player\tianfu_luck_gate($tpDropPct, ($yguaiwu->dljv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0)))) >= $sjjv && $yguaiwu->gzb != ''){
            $retzb = \player\zhuangbei_config_rows_by_ids($yguaiwu->gzb);
            if (!empty($retzb)){
                $sjdl = mt_rand(0,count($retzb)-1);
                $zbname = $retzb[$sjdl]['zbname'];
                $zbid = $retzb[$sjdl]['zbid'];
                $zbnowid = player\addzb($sid,$zbid,$dblj);
				$zbys = \player\getzbkzb($zbid,$dblj);// Lấy màu hiển thị và phẩm chất của trang bị
                $chakanzb = $encode->encode("cmd=chakanzb&zbnowid=$zbnowid&uid=$player->uid&sid=$sid");
			$huode .= "Nhận được: ".'<a href="?cmd='.$chakanzb.'"><font color='.$zbys->zbys.' >'.$zbname .'</font></a><br>';// Hiển thị liên kết xem trang bị vừa nhận
			        $zbpz = $zbys->zbgj + $zbys->zbfy + $zbys->zbxx +$zbys->zbbj ;// Gửi thông báo hệ thống khi nhận được trang bị phẩm chất cao
					if ($zbpz >=5){
			        $sql = "insert into ggliaotian(name,msg,uid) values(?,?,?)";
                    $stmt = $dblj->prepare($sql);
                    $stmt->execute(array('【Phụ】',"[".htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8')."]{$jisha}[{$guaiwu->gname}] nhận được [{$zbname}] làm cho mấy tiện nhân hâm mộ.",'0'));
					}
            }
        }
		// Kiểm tra và xử lý rơi vật phẩm
        $sjjv = mt_rand(1,180);
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], \player\tianfu_luck_gate($tpDropPct, ($yguaiwu->djjv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0)))) >= $sjjv && $yguaiwu->gdj != ''){
            $retdj = \player\daoju_config_rows_by_ids($yguaiwu->gdj);
            if (!empty($retdj)){
                $sjdj = mt_rand(0,count($retdj)-1);
                $djname = $retdj[$sjdj]['djname'];
                $djid = $retdj[$sjdj]['djid'];
                if ($djid == 1 && $lvc == 0){
                    goto yp;
                }
                $djsum = mt_rand(1,2);
                \player\adddj($sid,$djid,$djsum,$dblj);
                $huode .= "Nhận được: <div class='djys'>$djname x$djsum</div>";// Hiển thị vật phẩm nhận được theo định dạng màu giao diện

                for ($i=0;$i<count($taskarr);$i++){
                    $rwyq = $taskarr[$i]['rwyq'];
                    $rwid = $taskarr[$i]['rwid'];
                    $rwzl = $taskarr[$i]['rwzl'];
                    $rwzt = $taskarr[$i]['rwzt'];
                    if ($rwyq==$djid && $rwzl==1 && $rwzt!=3){
                        $rwnowcount = $taskarr[$i]['rwnowcount']+ $djsum;
                        $rwts = $taskarr[$i]['rwname'].'('.$rwnowcount."/".$taskarr[$i]['rwcount'].')<br/>';
                        break;
                    }
                }

            }
        }
        yp:
        $sjjv = mt_rand(1,100);
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], \player\tianfu_luck_gate($tpDropPct, $yguaiwu->ypjv)) >= $sjjv && $yguaiwu->gyp != ''){
            $retyp = \player\yaopin_config_rows_by_ids($yguaiwu->gyp);
            if (!empty($retyp)) {
                $sjdj = mt_rand(0, count($retyp) - 1);
                $ypname = $retyp[$sjdj]['ypname'];
                $ypid = $retyp[$sjdj]['ypid'];
                $ypsum = mt_rand(1, 2);// Số lượng dược phẩm rơi ngẫu nhiên
                \player\addyaopin($sid,$ypid,$ypsum,$dblj);
                $huode .= "Nhận được: <div class='ypys'>$ypname x$ypsum</div>";
            }
        }

        $guaiwu->gexp = round($guaiwu->gexp / ($lvc+1),0);// Tính lượng tu vi nhận được sau khi tiêu diệt quái vật
        if($guaiwu->gexp < 3){
            $guaiwu->gexp = 3;
        }

        // Hiệu ứng võ công: tăng tu vi nhận được sau khi thắng.
        if ($wgEffectType == 'exp_bonus' && $wgEffectValue > 0) {
            $addExpBonus = max(1, round($guaiwu->gexp * $wgEffectValue / 100));
            $guaiwu->gexp += $addExpBonus;
            $huode .= "Võ công {$wgEffectName}: tu vi nhận thêm +{$addExpBonus}<br/>";
        }
        $zdjg = 1;
    }
	
    $pzssh = 0;
    if ($guaiwu->ghp > 0) {
        $pzssh = $phurt - $pvexx;
    }// Tính sát thương cuối cùng người chơi phải nhận sau khi trừ hút máu
    if ($pzssh > 0) {
        $bossHitDesc = array(
            "{$guaiwu->gname} uống hơi sâu, cú vung tay nhanh như sấm, búa khổng lồ chặt chém xuống đập mạnh lên vai vai bạn!",
            "{$guaiwu->gname} vung một cái lật, cánh tay rộn sóng, móng vuốn nhọn như lưỡi dao đâm thẳng vào ngực trước bạn!",
            "{$guaiwu->gname} lao về trước bằng tiếng gầm rền, giẫm xuống mạnh như núi đập, cả khuôn mặt bạn bừng long lẫy!"
        );
        $cbMobText = isset($cbMob) ? \player\combat_text($cbMob['text'], $guaiwu->gname) : $bossHitDesc[mt_rand(0, count($bossHitDesc) - 1)];
        $cbMobName = (isset($cbMob) && $cbMob['name'] !== '') ? "«<b>{$cbMob['name']}</b>» " : '';
        $tishihtml .= "<font color='#f44336'>👹 {$guaiwu->gname} phản công {$cbMobName}" . (!empty($cbMobCrit) ? '<b>(bạo kích)</b> ' : '') . ": " . $cbMobText . " <b>Nhận {$pzssh} sát thương</b>.</font><br/>";
    } elseif ($guaiwu->ghp > 0 && !empty($cbStunned)) {
        $tishihtml .= "<font color='#ce93d8'>💫 {$guaiwu->gname} bị «{$cbMove['name']}» khống chế, không thể phản kích!</font><br/>";
    }
	
    $sql = "update game1 set uhp = GREATEST(0, uhp - $pzssh) WHERE sid = '$sid'";
    if (!empty($cbHeal) && $player->uhp - $pzssh > 0) {
        $cbHeal = max(0, min($cbHeal, intval($player->umaxhp) - (intval($player->uhp) - $pzssh)));
        if ($cbHeal > 0) {
            $sql = "update game1 set uhp = GREATEST(0, uhp - $pzssh) + $cbHeal WHERE sid = '$sid'";
            $tishihtml .= "<font color='#4CAF50'>💚 Hồi phục {$cbHeal} khí huyết</font><br/>";
        }
    }// Clip HP at 0: umaxhp in DB is the base max, the real max is buffed at runtime

    $dblj->exec($sql);                                                     // Thực thi câu lệnh cập nhật cơ sở dữ liệu
    \player\player_cache_clear($sid); // UPDATE uhp bằng SQL thô không xóa cache => đọc lại sẽ ra HP cũ, không nhận ra đã tử vong
    $player =  player\getplayer($sid,$dblj);// Tải lại thông tin người chơi sau khi cập nhật HP
	$cwxl = round($phurt*2.2);// Tính lượng sát thương thú cưng phải nhận
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
	
	
	
	
    if ($player->uhp <= 0){// Kiểm tra người chơi đã tử vong hay chưa
        $zdjg = 0;// Chuyển sang xử lý kết quả chiến đấu thất bại 
    }
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

if ($player->jn1!=0){
    $jineng = \player\getplayerjineng($player->jn1,$sid,$dblj);
    if ($jineng){
        $jnname1 = $jineng->jnname.'('.$jineng->jncount.')';
    }
}
if ($player->jn2!=0){
    $jineng = \player\getplayerjineng($player->jn2,$sid,$dblj);
    if ($jineng){
        $jnname2 = $jineng->jnname.'('.$jineng->jncount.')';
    }
}
if ($player->jn3!=0){
    $jineng = \player\getplayerjineng($player->jn3,$sid,$dblj);;
    if ($jineng){
        $jnname3 = $jineng->jnname.'('.$jineng->jncount.')';
    }
}


/**
 * Tạo giao diện kết quả chiến đấu.
 * $type:
 * - win: thắng trận
 * - lose: thua trận
 * - injured: nhân vật trọng thương
 */
if (!function_exists('pve_result_html')) {
    function pve_result_html($type, $title, $enemyHtml, $rewardHtml, $mainUrl)
    {
        $icon = '🏆';
        $class = 'win';
        $statusText = 'Chiến thắng';

        if ($type == 'lose') {
            $icon = '☠';
            $class = 'lose';
            $statusText = 'Thất bại';
        } elseif ($type == 'injured') {
            $icon = '💔';
            $class = 'injured';
            $statusText = 'Trọng thương';
        }

        return <<<HTML


<div class="pve-result-wrap">
    <div class="pve-result-card {$class}-card">
        <div class="pve-result-head">
            <div class="pve-result-icon">{$icon}</div>
            <div class="pve-result-title">{$title}</div>
            <div class="pve-result-status {$class}">{$statusText}</div>
        </div>
        <div class="pve-result-body">
            {$enemyHtml}
            <div class="pve-result-section">Phần thưởng / Thông báo</div>
            <div class="pve-reward-box">
                {$rewardHtml}
            </div>
        </div>
        <div class="pve-result-actions">
            <a class="pve-main-btn" href="?cmd={$mainUrl}">Đại Sảnh</a>
        </div>
    </div>
</div>
HTML;
    }
}


if (!function_exists('pve_tip_steps')) {
    // Chuyển dòng nhật ký chiến đấu (nối bằng <br/>) thành từng bước riêng biệt
    // để hiển thị "chạy ra từng phần, lần lượt" giữa các lượt đánh.
    function pve_tip_steps($tishihtml) {
        $html = trim((string) $tishihtml);
        if ($html === '') { return ''; }
        $out = '';
        $i = 0;
        foreach (preg_split('/<br\s*\/?\s*>/i', $html) as $part) {
            $part = trim($part);
            if ($part === '') { continue; }
            $i++;
            $out .= '<div class="pve-step" style="--step:' . $i . '">' . $part . '</div>';
        }
        return $out !== '' ? '<div class="pve-tip">' . $out . '</div>' : '';
    }
}


$pveResultHtml = '';
if (isset($zdjg)){// Xử lý kết quả sau khi trận chiến kết thúc
    switch ($zdjg){
        case 1:

            // Áp dụng buff công pháp: exp_bonus
            $cp_exp_bonus = 0;
            if (!empty($player->cp_effects)) {
                foreach ($player->cp_effects as $cpEff) {
                    if ($cpEff['type'] === 'exp_bonus') $cp_exp_bonus += $cpEff['value'];
                }
            }
            $baseExp = intval($guaiwu->gexp);
            $cpExpGain = ($cp_exp_bonus > 0) ? max(1, intval($baseExp * $cp_exp_bonus / 100)) : 0;
            $finalExp = $baseExp + $cpExpGain;
            
            // Giảm EXP do đan độc
            $toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
            if ($toxinPenalty > 0) {
                $finalExp = max(1, intval($finalExp * (1 - $toxinPenalty / 100)));
            }
            // Thien Phu (exp_monster): nhan vao exp that, SAU phat dan doc, TRUOC changeexp.
            $tpExpPct = isset($player->tianfu_bonus['exp_monster']) ? floatval($player->tianfu_bonus['exp_monster']) : 0;
            if ($tpExpPct > 0) {
                $finalExp = max(1, intval(round($finalExp * (100 + $tpExpPct) / 100)));
            }

            player\changeexp($sid,$dblj,$finalExp);
            player\changecwexp($player->cw,$finalExp,$dblj);

            $cpExpReward = max(1, intval(round($baseExp * 0.02)));
            $cpExpTotal = \player\congphap_add_exp_to_equipped($sid, $dblj, $cpExpReward);
            if ($cpExpTotal > 0) {
                $huode .= "Nhận được: <font color='#4caf50'>+{$cpExpTotal} kinh nghiệm công pháp</font><br/>";
            }

            // Tải lại thông tin người chơi sau khi cộng tu vi
            $player = player\getplayer($sid, $dblj);
            $tupocmd = $encode->encode("cmd=tupo&canshu=tupo&sid=$sid");
			$wgid = isset($player->wugong) ? intval($player->wugong) : 0;
			$strxl = $encode->encode("cmd=wgxiulian&canshu=1&wgid=$wgid&sid=$sid");
			
			$tplshtml = "";
			if ($player->uexp >= $player->umaxexp) {
				$tplshtml = '<a href="?cmd=' . $tupocmd . '">【Đột Phá Ngay】</a>';
				$huode .= "Tu vi đã đầy " . $tplshtml . "<br/>";
			} else {
				$expDisplay = "Nhận được: <font color='red'>+{$finalExp} tu vi</font> ({$player->uexp}/{$player->umaxexp})";
				if ($cpExpGain > 0) {
					$expDisplay .= " <font color='#ff9800'>[Công pháp +{$cpExpGain}]</font><br/>";
					$expDisplay .= "Công pháp: tăng +{$cpExpGain} tu vi nhận được<br/>";
				} else {
					$expDisplay .= "<br/>";
				}
				$huode .= $expDisplay;
			}
			
            // Cộng kinh nghiệm võ công khi tiêu diệt quái hoặc Boss.
            // Công thức hiện tại: tối thiểu 5 EXP, tối đa không vượt cấp quái và không vượt 1% mốc EXP võ công.
            $wgid = isset($player->wugong) ? intval($player->wugong) : 0;
            if ($wgid > 0) {
                $cxwg = \player\wgcx($wgid, $sid, $dblj);
                if ($cxwg) {
                    $wgxlNow = intval($cxwg->wgxl);
                    $wgxlMax = max(1, intval($cxwg->wgxlmax));

                    if ($wgxlNow >= $wgxlMax) {
                        $huode .= "Kinh nghiệm võ công đã đầy <a href='?cmd=" . $strxl . "'>【Nâng cấp Võ công】</a><br/>";
                    } else {
                        // Tính lượng EXP võ công sẽ được cộng:
						// - Không thấp hơn 5 EXP
						// - Không vượt quá cấp độ quái vật
						// - Đồng thời không vượt quá 1% EXP yêu cầu của cấp võ công hiện tại
						$addWgExp = max(
							5,
							min(
								intval($guaiwu->glv),
								intval(round($wgxlMax * 0.01))
							)
						);
                        $newWgExp = min($wgxlMax, $wgxlNow + $addWgExp);
                        $realAddWgExp = $newWgExp - $wgxlNow;

                        if ($realAddWgExp > 0) {
                            $sql = "UPDATE playerwugong
                                    SET wgxl = $newWgExp
                                    WHERE wgid = '$wgid' AND sid = '$sid'";
                            $dblj->exec($sql);

                            $huode .= "Nhận được: <font color='green'>+$realAddWgExp kinh nghiệm võ công</font> ($newWgExp/$wgxlMax)<br/>";
                        } else {
                            $huode .= "Kinh nghiệm võ công đã đầy <a href='?cmd=" . $strxl . "'>【Nâng cấp Võ công】</a><br/>";
                        }
                    }
                }
            }

            $enemyHtml = '<div class="pve-result-row"><span>Người đánh chết</span><b><font color="red">'.$guaiwu->gname.'</font></b></div>'
                       . '<div class="pve-result-row"><span>Cấp quái</span><b>'.$guaiwu->glv.'</b></div>';

            $rewardHtml = $huode . $rwts;

            /* Hook quest: pve_win */
            \player\club_quest_update($sid, 'pve_win', 1, $dblj);

            $pveResultHtml = pve_result_html(
                'win',
                'Chiến đấu thắng lợi',
                $enemyHtml,
                $rewardHtml,
                $gonowmid
            );
            break;
        case 0:
            $enemyHtml = '<div class="pve-result-row"><span>Bị đánh bại bởi</span><b><font color="red">'.$guaiwu->gname.'</font></b></div>'
                       . '<div class="pve-result-row"><span>Cấp quái</span><b>'.$guaiwu->glv.'</b></div>';

            $rewardHtml = 'Chiến đấu thất bại!<br/>Mời thiếu hiệp hồi phục rồi thử lại.';

            $pveResultHtml = pve_result_html(
                'lose',
                'Chiến đấu thất bại',
                $enemyHtml,
                $rewardHtml,
                $gorehpmid
            );
            break;
        case -1:
            $enemyHtml = '<div class="pve-result-row"><span>Trạng thái</span><b><font color="red">Trọng thương</font></b></div>';

            $rewardHtml = 'Ngươi đã trọng thương, không thể tiếp tục chiến đấu!<br/>Mời thiếu hiệp khôi phục sau đó thử lại.';

            $pveResultHtml = pve_result_html(
                'injured',
                'Không thể tiếp tục chiến đấu',
                $enemyHtml,
                $rewardHtml,
                $gorehpmid
            );
            break;
    }
}

// Luôn hiển thị giao diện chiến đấu (quái, nhân vật, nhật ký); trận kết thúc thì kết quả hiện dạng popup phía trên.
{
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

    if ($pvexx>0){
        $pvexx="(+".$pvexx.')';
    }else{
        $pvexx = '';
    }

    if ($player->cw!=0){
        $cw = \player\getchongwu($player->cw,$dblj);
		
        if ($cwhurt!='' || $cw->cwhp>0){
            $cwHpPercent = 0;
            if (intval($cw->cwmaxhp) > 0) {
                $cwHpPercent = max(0, min(100, intval($cw->cwhp / $cw->cwmaxhp * 100)));
            }

            $cwhtml=<<<HTML
            <div class="pve-card pet">
                <div class="pve-card-title">
                    <span>🐾 Sủng vật</span>
                    <b>$cw->cwname [lv:$cw->cwlv]</b>
                </div>
                <div class="pve-hpbar small"><div class="pve-hpbar-in pet" style="width:{$cwHpPercent}%"></div></div>
                <div class="pve-stat-row"><span>Khí huyết</span><b class="hpys">$cw->cwhp/$cw->cwmaxhp</b><em>$cwhurt$cwts</em></div>
                <div class="pve-stat-row"><span>Tấn công</span><b>$cw->cwgj</b></div>
                <div class="pve-stat-row"><span>Phòng ngự</span><b>$cw->cwfy</b></div>
            </div>
HTML;
        }

    }
	
	
	
	
$djshtml = <<<HTML
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Máy bấm giờ </title>
<script type="text/javascript" src="./chajian/djs/js/jquery.js"></script>
<script type="text/javascript" src="./chajian/djs/js/time_js.js"></script>
<link type="text/css" rel="stylesheet" href="./chajian/djs/css/time_css.css" />
</head>
<body>
<div class="game_time">
	<div class="hold">
		<div class="pie pie1"></div>
	</div>
	<div class="hold">
		<div class="pie pie2"></div>
	</div>
	<div class="bg"> </div>
	<div class="time"></div>
</div>
<script type="text/javascript">
countDown();
</script>
</body>
HTML;
// Giao diện và script hiển thị đồng hồ đếm ngược
	
	
	
	
	
// Quái đã chết: máu hiển thị không âm.
if (intval($guaiwu->ghp) < 0) {
    $guaiwu->ghp = 0;
}
$gwxl = 0;
if (intval($guaiwu->gmaxhp) > 0) {
    $gwxl = max(0, min(100, intval($guaiwu->ghp / $guaiwu->gmaxhp * 100)));
}

$playerHpPercent = 0;
if (intval($player->umaxhp) > 0) {
    $playerHpPercent = max(0, min(100, intval($player->uhp / $player->umaxhp * 100)));
}
// Giá trị máu đầu lượt (chỉ khác khi vừa đánh) — client chạy từ giá trị này về giá trị hiện tại
$gwxlFrom = $gwxl;
$gHpFromText = $guaiwu->ghp;
if (isset($pveFromGhp) && intval($guaiwu->gmaxhp) > 0) {
    $gHpFromText = max(0, $pveFromGhp);
    $gwxlFrom = max(0, min(100, intval($gHpFromText / $guaiwu->gmaxhp * 100)));
}
$playerHpFrom = $playerHpPercent;
$pHpFromText = $player->uhp;
if (isset($pveFromUhp) && intval($player->umaxhp) > 0) {
    $pHpFromText = max(0, $pveFromUhp);
    $playerHpFrom = max(0, min(100, intval($pHpFromText / $player->umaxhp * 100)));
}

// Chỉ tự động làm mới sau khi người chơi đã bấm Tấn công / dùng kỹ năng.
// Trang vừa mở trận cmd=pve sẽ không tự refresh nữa.
$autoRefresh = '';
if (isset($cmd) && $cmd == 'pvegj' && $player->uhp > 0 && $guaiwu->ghp > 0) {
    $autoRefresh = '<meta http-equiv="refresh" content="2">';
}

$battleTips = '';
if ($tishihtml != '') {
    $battleTips .= pve_tip_steps($tishihtml);
}
if ($cwresthtml != '') {
    $battleTips .= '<div class="pve-alert">'.$cwresthtml.'</div>';
}

$pvePlayerSafe = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');

// Nút Tuyệt Kỹ / Tuyệt Chiêu của võ công đang trang bị (Võ Kỹ = nút Tấn công).
$cbBtnHtml = '';
$cbWgV = isset($cbWg) ? $cbWg : ((isset($player->wugong) && intval($player->wugong) > 0) ? \player\wgcx(intval($player->wugong), $sid, $dblj) : false);
$cbMovesV = \player\combat_wugong_moves($cbWgV);
if (!empty($cbMovesV)) {
    $cbStV = isset($cbState) ? $cbState : \player\combat_state_load($sid, $gid, $dblj);
    $cbBtns = '';
    foreach (array(2, 3) as $t) {
        $mv = $cbMovesV[$t];
        $cost = $mv['stamina_cost'];
        $wait = \player\combat_move_wait($cbStV, $t);
        $sub = '';
        if (!$mv['unlocked']) {
            $sub = '🔒 Võ công cấp ' . $mv['unlock_lv'];
        } elseif (!$cbStV['ok']) {
            $sub = 'Chưa khởi tạo';
        } elseif ($wait > 0) {
            $sub = '⏳ Hồi ' . $wait . ' lượt';
        } elseif ($player->ustamina < $cost) {
            $sub = 'Thiếu thể lực';
        }
        $info = 'x' . $mv['mult'] . ' · ' . $cost . ' thể lực';
        $eff = \player\combat_effect_text($mv['effect'], $mv['value']);
        $inner = "<b>{$mv['label']}</b><span>{$mv['name']}</span><small>" . ($sub !== '' ? $sub : $info) . "</small>"
            . ($eff !== '' ? "<small>{$eff}</small>" : '');
        if ($sub === '') {
            $cbUrl = $encode->encode("cmd=pvegj&canshu=usewg&wgt=$t&gid=$gid&sid=$sid&nowmid=$nowmid");
            $cbBtns .= "<a class=\"pve-move t{$t}\" href=\"?cmd={$cbUrl}\">{$inner}</a>";
        } else {
            $cbBtns .= "<span class=\"pve-move t{$t} is-off\">{$inner}</span>";
        }
    }
    $cbBtnHtml = "<div class=\"pve-section-title\">Thế võ · {$cbWgV->wgname} (cấp " . intval($cbWgV->wgdj) . ")</div>"
        . "<div class=\"pve-move-note\">⚔ Tấn công = Võ Kỹ «{$cbMovesV[1]['name']}»</div>"
        . "<div class=\"pve-move-box\">{$cbBtns}</div>";
}
// Vừa dùng thế võ: tự làm mới bằng đòn thường, không lặp lại thế (tránh tốn thể lực ngoài ý muốn).
if ($autoRefresh !== '' && isset($canshu) && $canshu == 'usewg') {
    $autoRefresh = '<meta http-equiv="refresh" content="2;url=?cmd=' . $pgjcmd . '">';
}

$pveTopHtml = <<<HTML
    $battleTips

    <div class="pve-card enemy">
        <div class="pve-card-title">
            <span>Yêu quái</span>
            <b>$guaiwu->gname [lv:$guaiwu->glv]</b>
        </div>
        <div class="pve-hpbar"><div class="pve-hpbar-in" data-sim="1" data-to="{$gwxl}" style="width:{$gwxlFrom}%"></div></div>
        <div class="pve-stat-row"><span>Khí huyết</span><b class="hpys" data-sim="1" data-to="$guaiwu->ghp/$guaiwu->gmaxhp">$gHpFromText/$guaiwu->gmaxhp</b><em data-sim="1">$pvebj$ghurt</em></div>
        <div class="pve-stat-row"><span>Tấn công</span><b>$guaiwu->ggj</b></div>
        <div class="pve-stat-row"><span>Phòng ngự</span><b>$guaiwu->gfy</b></div>
    </div>

    <div class="pve-card player">
        <div class="pve-card-title">
            <span>Nhân vật</span>
            <b>$pvePlayerSafe [lv:$player->ulv]</b>
        </div>
        <div class="pve-hpbar"><div class="pve-hpbar-in player" data-sim="2" data-to="{$playerHpPercent}" style="width:{$playerHpFrom}%"></div></div>
        <div class="pve-stat-row"><span>Khí huyết</span><b class="hpys" data-sim="2" data-to="$player->uhp/$player->umaxhp">$pHpFromText/$player->umaxhp</b><em data-sim="2">$phurt$pvexx</em></div>
        <div class="pve-stat-row"><span>Tấn công</span><b>$player->ugj</b></div>
        <div class="pve-stat-row"><span>Phòng ngự</span><b>$player->ufy</b></div>
    </div>

HTML;

// Đang đánh (cmd=pvegj): thay bằng màn Chiến Trường — 2 bên đối đầu, mô phỏng từng đòn rồi mới hiện kết quả.
$pveIsFight = (isset($cmd) && $cmd == 'pvegj' && isset($pveFromGhp));
$pveTitle = $pveIsFight ? '⚔ Chiến Trường' : '⚔ Chiến Đấu';
if ($pveIsFight) {
    $pvaElem = isset($guaiwu->element) ? $guaiwu->element : 'none';
    $pvaIcons = array('kim' => '⚔️', 'moc' => '🌿', 'thuy' => '💧', 'hoa' => '🔥', 'tho' => '⛰️');
    $pvaMobIcon = isset($pvaIcons[$pvaElem]) ? $pvaIcons[$pvaElem] : '👹';
    $pvaAvatar = \player\player_avatar_img(isset($player->usex) ? $player->usex : 1);
    $pvaMobName = htmlspecialchars($guaiwu->gname, ENT_QUOTES, 'UTF-8');
    $pvaFast = ($autoRefresh !== '') ? '1' : '0';
    $pveTopHtml = <<<HTML
    <div class="pva" id="pva" data-sim-root="1" data-fast="{$pvaFast}">
        <div class="pva-field">
            <div class="pva-side me" id="pva-me" data-side="2">
                <div class="pva-ava"><img src="$pvaAvatar" alt=""></div>
                <b>$pvePlayerSafe</b><em>Lv $player->ulv</em>
                <div class="pva-hp player"><i data-sim="2" data-to="{$playerHpPercent}" style="width:{$playerHpFrom}%"></i></div>
                <small data-sim="2" data-to="$player->uhp/$player->umaxhp">$pHpFromText/$player->umaxhp</small>
            </div>
            <div class="pva-vs">VS</div>
            <div class="pva-side mob el-$pvaElem" id="pva-mob" data-side="1">
                <div class="pva-ava">$pvaMobIcon</div>
                <b>$pvaMobName</b><em>Lv $guaiwu->glv</em>
                <div class="pva-hp"><i data-sim="1" data-to="{$gwxl}" style="width:{$gwxlFrom}%"></i></div>
                <small data-sim="1" data-to="$guaiwu->ghp/$guaiwu->gmaxhp">$gHpFromText/$guaiwu->gmaxhp</small>
            </div>
        </div>
        <div class="pva-banner" id="pva-banner" data-sim-banner="1"></div>
        <div class="pva-log-title">Diễn biến</div>
        $battleTips
    </div>
HTML;
}

$html = <<<HTML


<div class="pve-wrap">
    <div class="pve-title">{$pveTitle}</div>

    $pveTopHtml

    $cwhtml
    $autoRefresh<!-- Chỉ refresh sau khi đã bấm Tấn công và trận còn đang diễn ra -->

    <div class="pve-actions">
        <a class="pve-btn run" href="?cmd=$gonowmid">↩ Bỏ chạy</a>
        <a class="pve-btn attack" href="?cmd=$pgjcmd">⚔ Tấn công</a>
    </div>

    $cbBtnHtml

    <div class="pve-section-title">Kỹ năng</div>
    <div class="pve-skill-box">
        <a href="?cmd=$usejn1">$jnname1</a>
        <a href="?cmd=$usejn2">$jnname2</a>
        <a href="?cmd=$usejn3">$jnname3</a>
        {$lcskillHtml}
    </div>

    <div class="pve-section-title">Dược phẩm</div>
    <div class="pve-drug-box">
        <a href="?cmd=$useyp1">$ypname1</a>
        <a href="?cmd=$useyp2">$ypname2</a>
        <a href="?cmd=$useyp3">$ypname3</a>
    </div>
</div>
HTML;


}
if ($pveResultHtml !== '') {
    $html .= '<div class="pve-pop show" id="pve-pop" onclick="if(event.target===this)this.className=\'pve-pop\'">'
        . '<div class="pve-pop-box"><a class="pve-pop-x" href="javascript:void(0)" title="Xem lại trận đấu"'
        . ' onclick="document.getElementById(\'pve-pop\').className=\'pve-pop\'">✕</a>' . $pveResultHtml . '</div></div>'
        // Đưa popup ra <body>: khung game có backdrop-filter làm position:fixed bị tính theo khung thay vì màn hình
        . '<script>(function(){var p=document.getElementById(\'pve-pop\');if(p&&p.parentNode!==document.body)document.body.appendChild(p);})();</script>';
}
// Mô phỏng lượt đánh trên màn Chiến Trường (js/battle_sim.js): diễn biến hiện lần lượt, số sát thương bay,
// thanh máu tụt; xong lượt mới bật popup kết quả.
if ($pveIsFight) {
    $html .= '<script src="js/battle_sim.js?v=2026102801"></script>';
}
echo '<style>.pve-step{display:block;margin:3px 0;padding:1px 0 .5px;color:#f5f5f5;font-size:14px;line-height:1.5;animation:pveStepIn .45s ease-out both}.pve-step:nth-child(1){animation-delay:.05s}.pve-step:nth-child(2){animation-delay:.11s}.pve-step:nth-child(3){animation-delay:.17s}.pve-step:nth-child(4){animation-delay:.23s}.pve-step:nth-child(5){animation-delay:.29s}.pve-step:nth-child(6){animation-delay:.35s}@keyframes pveStepIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}.pve-result-wrap{margin-top:14px}.pve-alert{margin:10px 0}</style>';
echo $html;
/**
 * Ghi chú: file xử lý giao diện và logic chiến đấu PVE.
 * Comment đã được chuẩn hóa sang tiếng Việt, không thay đổi logic xử lý.
 */
?>
