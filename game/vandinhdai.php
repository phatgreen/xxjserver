<?php
if (!isset($sid) || $sid == '') {
    exit('Missing sid');
}

require_once __DIR__ . '/../class/function/vandinhdai_reward.php';

function vdd_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function vdd_state($sid, $dblj)
{
    $stmt = $dblj->prepare("SELECT * FROM arena_player WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $stmt = $dblj->prepare("INSERT INTO arena_player(sid, score, wins, losses, today_fights, last_fight_date, updated_at) VALUES(?, 1000, 0, 0, 0, CURDATE(), NOW())");
        $stmt->execute(array($sid));
        $row = array('sid' => $sid, 'score' => 1000, 'wins' => 0, 'losses' => 0, 'today_fights' => 0, 'last_fight_date' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s'));
    }

    if ($row['last_fight_date'] != date('Y-m-d')) {
        $stmt = $dblj->prepare("UPDATE arena_player SET today_fights = 0, last_fight_date = CURDATE(), updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($sid));
        $row['today_fights'] = 0;
        $row['last_fight_date'] = date('Y-m-d');
    }

    $row['score'] = intval($row['score']);
    $row['wins'] = intval($row['wins']);
    $row['losses'] = intval($row['losses']);
    $row['today_fights'] = intval($row['today_fights']);
    return $row;
}

function vdd_rank($sid, $dblj)
{
    $stmt = $dblj->prepare("SELECT score FROM arena_player WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $score = $stmt->fetchColumn();
    if ($score === false) return 0;

    $stmt = $dblj->prepare("SELECT COUNT(*) FROM arena_player WHERE score > ?");
    $stmt->execute(array(intval($score)));
    return intval($stmt->fetchColumn()) + 1;
}

function vdd_sync_recent_players($dblj)
{
    $rows = $dblj->query("SELECT sid FROM game1 WHERE sid IS NOT NULL AND sid <> '' LIMIT 200");
    if (!$rows) return;
    $stmt = $dblj->prepare("INSERT IGNORE INTO arena_player(sid, score, wins, losses, today_fights, last_fight_date, updated_at) VALUES(?, 1000, 0, 0, 0, CURDATE(), NOW())");
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $stmt->execute(array($row['sid']));
    }
}

function vdd_opponents($sid, $score, $dblj)
{
    $stmt = $dblj->prepare("
        SELECT ap.sid, ap.score, ap.wins, ap.losses, g.uname, g.ulv, g.usex
        FROM arena_player ap
        INNER JOIN game1 g ON g.sid = ap.sid
        WHERE ap.sid <> ?
        ORDER BY ABS(ap.score - ?) ASC, ap.score DESC
        LIMIT 8
    ");
    $stmt->execute(array($sid, intval($score)));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function vdd_top($dblj)
{
    $stmt = $dblj->query("
        SELECT ap.sid, ap.score, ap.wins, ap.losses, g.uname, g.ulv
        FROM arena_player ap
        INNER JOIN game1 g ON g.sid = ap.sid
        ORDER BY ap.score DESC, ap.wins DESC
        LIMIT 20
    ");
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
}

function vdd_power($player)
{
    return max(1, intval($player->umaxhp / 5) + intval($player->ugj * 12) + intval($player->ufy * 9) + intval($player->ubj * 25) + intval($player->uxx * 18) + intval($player->ulv * 35));
}

function vdd_simulate($attacker, $defender)
{
    $aHp = max(1, intval($attacker->umaxhp));
    $dHp = max(1, intval($defender->umaxhp));
    $rounds = array();

    for ($round = 1; $round <= 35 && $aHp > 0 && $dHp > 0; $round++) {
        $aDamage = max(1, intval(round(intval($attacker->ugj) - intval($defender->ufy) * 0.62)));
        if ($aDamage < intval($attacker->ugj * 0.12)) $aDamage = max(1, intval(round($attacker->ugj * 0.12)));
        if (\player\roll_pct($attacker->ubj, 80)) $aDamage = intval(round($aDamage * 1.6));
        $dHp -= $aDamage;
        $aHeal = max(0, intval(round($aDamage * max(0, floatval($attacker->uxx)) / 230)));
        $aHp = min(intval($attacker->umaxhp), $aHp + $aHeal);

        $dDamage = 0;
        $dHeal = 0;
        if ($dHp > 0) {
            $dDamage = max(1, intval(round(intval($defender->ugj) - intval($attacker->ufy) * 0.62)));
            if ($dDamage < intval($defender->ugj * 0.12)) $dDamage = max(1, intval(round($defender->ugj * 0.12)));
            if (\player\roll_pct($defender->ubj, 80)) $dDamage = intval(round($dDamage * 1.6));
            $aHp -= $dDamage;
            $dHeal = max(0, intval(round($dDamage * max(0, floatval($defender->uxx)) / 230)));
            $dHp = min(intval($defender->umaxhp), $dHp + $dHeal);
        }

        if ($round <= 6 || $aHp <= 0 || $dHp <= 0) {
            $rounds[] = array('round' => $round, 'a_damage' => $aDamage, 'd_damage' => $dDamage, 'a_hp' => max(0, $aHp), 'd_hp' => max(0, $dHp));
        }
    }

    if ($aHp == $dHp) {
        $attackerPower = vdd_power($attacker) + mt_rand(1, 100);
        $defenderPower = vdd_power($defender) + mt_rand(1, 100);
        $win = $attackerPower >= $defenderPower;
    } else {
        $win = $aHp > $dHp;
    }

    return array('win' => $win, 'rounds' => $rounds, 'a_hp' => max(0, $aHp), 'd_hp' => max(0, $dHp));
}

function vdd_add_wugong_exp($sid, $dblj, $wgid, $amount)
{
    $wgid = intval($wgid);
    $amount = max(0, intval($amount));
    if ($wgid <= 0 || $amount <= 0) return array('add' => 0, 'now' => 0, 'max' => 0);

    $cxwg = \player\wgcx($wgid, $sid, $dblj);
    if (!$cxwg) return array('add' => 0, 'now' => 0, 'max' => 0);

    $now = intval($cxwg->wgxl);
    $max = max(1, intval($cxwg->wgxlmax));
    if ($now >= $max) return array('add' => 0, 'now' => $now, 'max' => $max);

    $new = min($max, $now + $amount);
    $real = $new - $now;
    $stmt = $dblj->prepare("UPDATE playerwugong SET wgxl = ? WHERE wgid = ? AND sid = ?");
    $stmt->execute(array($new, $wgid, $sid));
    return array('add' => $real, 'now' => $new, 'max' => $max);
}

vdd_sync_recent_players($dblj);
vdd_issue_daily_rewards($dblj);

$player = \player\getplayer($sid, $dblj);
$state = vdd_state($sid, $dblj);
$rank = vdd_rank($sid, $dblj);
$myDailyReward = vdd_my_daily_reward($sid, $dblj);
$dailyLimit = 10;
$leftFights = max(0, $dailyLimit - intval($state['today_fights']));
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$arenaCmd = $encode->encode("cmd=vandinhdai&sid=$sid");
$wgcmd = $encode->encode("cmd=wgxl&sid=$sid");
$notice = '';
$battleHtml = '';
$rewardTimeNotice = '<div class="vdd-notice reward">Chốt thưởng top 100 mỗi ngày lúc 21:00.</div>';
$rewardNotice = '';
if ($myDailyReward) {
    $rewardNotice = '<div class="vdd-notice reward">Thưởng top '.intval($myDailyReward['rank_no']).' hôm nay: +'.number_format(intval($myDailyReward['yxb_reward']), 0, ',', '.').' Linh thạch, +'.number_format(intval($myDailyReward['czb_reward']), 0, ',', '.').' Ma thạch'
        . (($vddDt = vdd_daily_reward_table()) && !empty($vddDt[intval($myDailyReward['rank_no'])]['dientho']) ? ', +' . intval($vddDt[intval($myDailyReward['rank_no'])]['dientho']) . ' Diên Thọ Đan' : '') . '.</div>';
}

if (isset($canshu) && $canshu == 'fight' && isset($target) && $target != '') {
    if ($leftFights <= 0) {
        $notice = '<div class="vdd-notice fail">Hôm nay đã hết lượt khiêu chiến.</div>';
    } elseif ($target == $sid) {
        $notice = '<div class="vdd-notice fail">Không thể tự khiêu chiến chính mình.</div>';
    } else {
        $defender = \player\getplayer($target, $dblj);
        if (!isset($defender->sid) || $defender->sid == '') {
            $notice = '<div class="vdd-notice fail">Đối thủ không tồn tại.</div>';
        } else {
            $defState = vdd_state($target, $dblj);
            $result = vdd_simulate($player, $defender);
            $diff = intval($defState['score']) - intval($state['score']);
            $base = $result['win'] ? 18 : -8;
            $bonus = $result['win'] ? intval(max(-6, min(12, round($diff / 55)))) : intval(max(-8, min(4, round($diff / 80))));
            $scoreChange = $base + $bonus;
            if ($result['win'] && $scoreChange < 10) $scoreChange = 10;
            if (!$result['win'] && $scoreChange > -3) $scoreChange = -3;

            $defChange = -intval(round($scoreChange * 0.55));
            $newScore = max(0, intval($state['score']) + $scoreChange);
            $newDefScore = max(0, intval($defState['score']) + $defChange);

            if ($result['win']) {
                $baseExpReward = 80 + intval($defender->ulv * 8) + max(0, intval(round($diff / 8)));
                $cp_exp_bonus = 0;
                if (!empty($player->cp_effects)) {
                    foreach ($player->cp_effects as $cpEff) {
                        if ($cpEff['type'] === 'exp_bonus') $cp_exp_bonus += $cpEff['value'];
                    }
                }
                $cpExpGain = ($cp_exp_bonus > 0) ? max(1, intval($baseExpReward * $cp_exp_bonus / 100)) : 0;
                $expReward = $baseExpReward + $cpExpGain;
                $wgReward = 6 + intval(max(1, $defender->ulv) / 3);
                \player\changeexp($sid, $dblj, $expReward);
                $wgGain = vdd_add_wugong_exp($sid, $dblj, isset($player->wugong) ? $player->wugong : 0, $wgReward);
                $stmt = $dblj->prepare("UPDATE arena_player SET score = ?, wins = wins + 1, today_fights = today_fights + 1, last_fight_date = CURDATE(), updated_at = NOW() WHERE sid = ?");
                $stmt->execute(array($newScore, $sid));
                $stmt = $dblj->prepare("UPDATE arena_player SET score = ?, losses = losses + 1, updated_at = NOW() WHERE sid = ?");
                $stmt->execute(array($newDefScore, $target));

                if (function_exists('player\\nhiemvu_update_all')) {
                    \player\nhiemvu_update_all($sid, 'pvp', 1, $dblj, array());
                }
                $wgLine = $wgGain['add'] > 0 ? '+'.$wgGain['add'].' kinh nghiệm võ công' : 'võ công chưa nhận thêm EXP';
                $cpLine = $cpExpGain > 0 ? ' <span style="color:#ff9800">[Công pháp +'.$cpExpGain.']</span>' : '';
                $notice = '<div class="vdd-notice win">Chiến thắng! Điểm hạng +'.$scoreChange.', nhận +'.$expReward.' tu vi'.$cpLine.', '.$wgLine.'.</div>';
            } else {
                $baseExpReward = max(12, intval(30 + $defender->ulv * 2));
                $cp_exp_bonus = 0;
                if (!empty($player->cp_effects)) {
                    foreach ($player->cp_effects as $cpEff) {
                        if ($cpEff['type'] === 'exp_bonus') $cp_exp_bonus += $cpEff['value'];
                    }
                }
                $cpExpGain = ($cp_exp_bonus > 0) ? max(1, intval($baseExpReward * $cp_exp_bonus / 100)) : 0;
                $expReward = $baseExpReward + $cpExpGain;
                $wgReward = max(2, intval($defender->ulv / 10));
                \player\changeexp($sid, $dblj, $expReward);
                $wgGain = vdd_add_wugong_exp($sid, $dblj, isset($player->wugong) ? $player->wugong : 0, $wgReward);
                $stmt = $dblj->prepare("UPDATE arena_player SET score = ?, losses = losses + 1, today_fights = today_fights + 1, last_fight_date = CURDATE(), updated_at = NOW() WHERE sid = ?");
                $stmt->execute(array($newScore, $sid));
                $stmt = $dblj->prepare("UPDATE arena_player SET score = ?, wins = wins + 1, updated_at = NOW() WHERE sid = ?");
                $stmt->execute(array($newDefScore, $target));
                if (function_exists('player\\nhiemvu_update_all')) {
                    // Thua tran thi reset chuoi "thang lien tiep" (nhiem vu an H8).
                    \player\nhiemvu_update_all($sid, 'pvp_lose', 1, $dblj, array());
                }
                $wgLine = $wgGain['add'] > 0 ? '+'.$wgGain['add'].' kinh nghiệm võ công' : 'võ công chưa nhận thêm EXP';
                $cpLine = $cpExpGain > 0 ? ' <span style="color:#ff9800">[Công pháp +'.$cpExpGain.']</span>' : '';
                $notice = '<div class="vdd-notice fail">Thất bại. Điểm hạng '.$scoreChange.', nhận an ủi +'.$expReward.' tu vi'.$cpLine.', '.$wgLine.'.</div>';
            }

            $stmt = $dblj->prepare("INSERT INTO arena_log(attacker_sid, defender_sid, winner_sid, score_change, exp_reward, wg_reward, created_at) VALUES(?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute(array($sid, $target, $result['win'] ? $sid : $target, $scoreChange, $expReward, $wgReward));

            $vddSkillNames = array('Vô Ảnh Thần Quyền', 'Truy Phong Kiếm Quyết', 'Huyền Thiên Chưởng', 'Băng Phách Hàn Quang', 'Lôi Đình Phá', 'Cửu Chuyển Phá Quân');
            $vddDefSkillNames = array('Kim Cương Bất Hoại', 'Thiên La Địa Võng', 'Thái Ất Hộ Thể', 'Huyền Quy Giáp', 'Vô Cực Phong Bế');
            // Có võ công: dùng tên thế võ thật của 2 bên (chỉ hiển thị, sát thương giữ nguyên công thức đài).
            $vddLastRound = 0;
            foreach ($result['rounds'] as $line) { $vddLastRound = max($vddLastRound, intval($line['round'])); }
            $vddAMoves = \player\combat_wugong_moves((isset($player->wugong) && intval($player->wugong) > 0) ? \player\wgcx(intval($player->wugong), $sid, $dblj) : false);
            $vddDMoves = \player\combat_wugong_moves((isset($defender->wugong) && intval($defender->wugong) > 0) ? \player\wgcx(intval($defender->wugong), $target, $dblj) : false);
            $vddASeq = !empty($vddAMoves) ? \player\combat_auto_sequence($vddAMoves, $vddLastRound) : array();
            $vddDSeq = !empty($vddDMoves) ? \player\combat_auto_sequence($vddDMoves, $vddLastRound) : array();
            foreach ($result['rounds'] as $line) {
                $atkSkill = isset($vddASeq[$line['round']]) ? $vddAMoves[$vddASeq[$line['round']]]['name'] : $vddSkillNames[mt_rand(0, count($vddSkillNames) - 1)];
                $defSkill = isset($vddDSeq[$line['round']]) ? $vddDMoves[$vddDSeq[$line['round']]]['name'] : $vddDefSkillNames[mt_rand(0, count($vddDefSkillNames) - 1)];
                $battleHtml .= '<div class="vdd-round"><b>Hiệp '.$line['round'].'</b><span>⚡ Bạn tung chiêu <font color="#4CAF50"><b>'.$atkSkill.'</b></font> gây '.$line['a_damage'].' / đối thủ dùng <font color="#f87171"><b>'.$defSkill.'</b></font> gây '.$line['d_damage'].'</span><em>HP bạn: '.number_format($line['a_hp'], 0, ',', '.').' - HP đối thủ: '.number_format($line['d_hp'], 0, ',', '.').'</em></div>';
            }

            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $state = vdd_state($sid, $dblj);
            $rank = vdd_rank($sid, $dblj);
            $leftFights = max(0, $dailyLimit - intval($state['today_fights']));
        }
    }
}

$opponents = vdd_opponents($sid, intval($state['score']), $dblj);
$opponentHtml = '';
foreach ($opponents as $op) {
    $fight = $encode->encode("cmd=vandinhdai&canshu=fight&target=".$op['sid']."&sid=$sid");
    $name = vdd_h($op['uname']);
    $lv = intval($op['ulv']);
    $score = intval($op['score']);
    $wl = intval($op['wins']).' / '.intval($op['losses']);
    $btn = $leftFights > 0 ? '<a class="vdd-btn fight" href="?cmd='.$fight.'">Khiêu chiến</a>' : '<span class="vdd-btn disabled">Hết lượt</span>';
    // Ảnh đại diện đối thủ làm điểm nhấn (giống ô vật phẩm Túi đồ)
    $opAvatar = \player\player_avatar_img(isset($op['usex']) ? $op['usex'] : 1);
    $opponentHtml .= '<div class="vdd-row"><span class="vdd-ava item-art" style="--ra:#f97316"><img src="./'.$opAvatar.'" alt=""></span><div class="vdd-row-info"><b>'.$name.'</b><span>Cấp '.$lv.' · Điểm '.$score.' · Thắng/Thua '.$wl.'</span></div>'.$btn.'</div>';
}
if ($opponentHtml == '') {
    $opponentHtml = '<div class="vdd-empty">Chưa có đối thủ phù hợp.</div>';
}

$topRows = vdd_top($dblj);
$topHtml = '';
$pos = 0;
foreach ($topRows as $row) {
    $pos++;
    $active = ($row['sid'] == $sid) ? ' active' : '';
    $medal = $pos == 1 ? '🥇' : ($pos == 2 ? '🥈' : ($pos == 3 ? '🥉' : '#'.$pos));   // Top 3 huy chương
    $topHtml .= '<div class="vdd-top-row'.$active.($pos <= 3 ? ' top'.$pos : '').'"><span>'.$medal.'</span><b>'.vdd_h($row['uname']).'</b><em>'.intval($row['score']).' điểm</em></div>';
}

$battleBlock = $battleHtml != '' ? '<section class="vdd-panel"><div class="vdd-kicker">Diễn biến gần nhất</div>'.$battleHtml.'</section>' : '';
$rewardRows = '';
foreach (vdd_daily_reward_table() as $rewardRank => $reward) {
    $rewardRows .= '<div class="vdd-reward-row"><span>Top '.$rewardRank.'</span><b>'.number_format($reward['yxb'], 0, ',', '.').' Linh thạch</b><em>'.number_format($reward['czb'], 0, ',', '.').' Ma thạch'.(!empty($reward['dientho']) ? ' · '.intval($reward['dientho']).' Diên Thọ Đan' : '').'</em></div>';
}

$safeName = vdd_h($player->uname);
$score = intval($state['score']);
$wins = intval($state['wins']);
$losses = intval($state['losses']);
$arenaBannerFile = __DIR__ . '/../images/bg/vandinhdai.png';
$arenaBanner = './images/bg/vandinhdai.png';
if (is_file($arenaBannerFile)) {
    $arenaBanner .= '?v=' . filemtime($arenaBannerFile);
}
$rankBadge = $rank > 0 ? 'Hạng #'.$rank : 'Chưa xếp hạng';

$html = <<<HTML
<style>
.vdd-page{max-width:860px;margin:0 auto;padding:14px 12px 22px;color:#f8fbff;font-family:"Segoe UI",Arial,sans-serif}.vdd-hero{border:1px solid rgba(250,204,21,.45);background:linear-gradient(180deg,rgba(72,35,105,.96),rgba(14,23,55,.98));border-radius:10px;padding:18px 16px;box-shadow:0 14px 34px rgba(0,0,0,.30)}.vdd-kicker{font-size:12px;color:#facc15;font-weight:1000;text-transform:uppercase;letter-spacing:0}.vdd-hero h1{font-size:28px;margin:4px 0 8px}.vdd-hero p{margin:0;color:#ddd6fe;line-height:1.55}.vdd-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:14px}.vdd-stat{border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.08);border-radius:8px;padding:9px}.vdd-stat span{display:block;color:#c4b5fd;font-size:12px;font-weight:900}.vdd-stat b{display:block;margin-top:4px;color:#fff;font-size:16px}.vdd-layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(260px,.8fr);gap:10px;margin-top:10px}.vdd-panel{border:1px solid rgba(125,211,252,.28);background:linear-gradient(180deg,rgba(18,31,78,.92),rgba(8,17,43,.97));border-radius:10px;padding:14px}.vdd-row{display:flex;align-items:center;justify-content:space-between;gap:10px;border-top:1px solid rgba(255,255,255,.12);padding:10px 0}.vdd-row:first-of-type{border-top:0}.vdd-row b{display:block;color:#fff}.vdd-row span{display:block;color:#bfdbfe;font-size:12px;margin-top:3px}.vdd-btn{display:inline-flex;align-items:center;justify-content:center;min-height:34px;line-height: 1.6;padding:0 12px;border-radius:8px;text-decoration:none;color:#fff!important;font-weight:1000;border:1px solid rgba(255,255,255,.18);white-space:nowrap}.vdd-btn.fight{background:linear-gradient(135deg,#dc2626,#f97316)}.vdd-btn.home{background:linear-gradient(135deg,#2563eb,#06b6d4)}.vdd-btn.wg{background:linear-gradient(135deg,#7c3aed,#c026d3)}.vdd-btn.disabled{background:#475569;color:#cbd5e1!important}.vdd-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.vdd-top-row{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:8px;align-items:center;border-top:1px solid rgba(255,255,255,.10);padding:8px 0}.vdd-top-row:first-of-type{border-top:0}.vdd-top-row span{color:#fde68a;font-weight:1000}.vdd-top-row b{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.vdd-top-row em{font-style:normal;color:#a7f3d0}.vdd-top-row.active{color:#facc15}.vdd-notice{margin-top:10px;border-radius:8px;padding:10px 12px;font-weight:900}.vdd-notice.win{background:rgba(22,163,74,.24);border:1px solid rgba(74,222,128,.42);color:#bbf7d0}.vdd-notice.fail{background:rgba(185,28,28,.24);border:1px solid rgba(248,113,113,.42);color:#fecaca}.vdd-round{border-top:1px solid rgba(255,255,255,.12);padding:9px 0;display:grid;gap:3px}.vdd-round:first-of-type{border-top:0}.vdd-round b{color:#fde68a}.vdd-round span{color:#e0f2fe}.vdd-round em{font-style:normal;color:#c7d2fe;font-size:12px}.vdd-empty{color:#cbd5e1;padding:12px 0}@media(max-width:760px){.vdd-layout,.vdd-stats{grid-template-columns:1fr}.vdd-hero h1{font-size:24px}.vdd-row{align-items:flex-start;flex-direction:column}.vdd-btn{width:100%}}
</style>
<style>
.vdd-reward-title{margin:12px 0 4px;color:#fde68a;font-weight:1000}
.vdd-reward-row{display:grid;grid-template-columns:64px minmax(0,1fr) auto;gap:8px;align-items:center;border-top:1px solid rgba(255,255,255,.10);padding:7px 0;font-size:12px}
.vdd-reward-row span{color:#fde68a;font-weight:1000}
.vdd-reward-row b{color:#e0f2fe}
.vdd-reward-row em{font-style:normal;color:#c4b5fd}
.vdd-btn.reward{background:linear-gradient(135deg,#f59e0b,#d97706);border-color:rgba(250,204,21,.52);box-shadow:0 0 14px rgba(245,158,11,.25)}
.vdd-btn.reward:hover{box-shadow:0 0 22px rgba(245,158,11,.45);filter:brightness(1.12)}
.vdd-reward-overlay{position:fixed;inset:0;z-index:9999;display:flex;padding-top:15%;justify-content:center;background:rgba(2,6,23,.82);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity .25s ease}
.vdd-reward-overlay.show{opacity:1;pointer-events:auto}
.vdd-reward-popup{width:380px;max-width:92vw;max-height:80vh;display:flex;flex-direction:column;border:1px solid rgba(250,204,21,.40);border-radius:16px;background:radial-gradient(circle at 20% 0%,rgba(250,204,21,.10),transparent 38%),linear-gradient(180deg,rgba(20,35,80,.96),rgba(10,18,50,.98));box-shadow:0 0 0 1px rgba(255,255,255,.04) inset,0 0 32px rgba(250,204,21,.15),0 20px 40px rgba(0,0,0,.45);transform:scale(.9) translateY(14px);transition:transform .28s cubic-bezier(.34,1.56,.64,1);overflow:hidden}
.vdd-reward-overlay.show .vdd-reward-popup{transform:scale(1) translateY(0)}
.vdd-reward-popup-head{padding:22px 20px 16px;text-align:center;border-bottom:1px solid rgba(255,255,255,.10)}
.vdd-reward-popup-icon{width:52px;height:52px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:50%;border:1px solid rgba(250,204,21,.45);background:rgba(250,204,21,.10);font-size:24px;box-shadow:0 0 18px rgba(250,204,21,.20)}
.vdd-reward-popup-head h3{margin:0 0 6px;color:#fff;font-size:18px;font-weight:1000;text-shadow:0 0 10px rgba(250,204,21,.35)}
.vdd-reward-popup-head p{margin:0;color:#fde68a;font-size:13px;font-weight:800}
.vdd-reward-popup-list{flex:1;overflow-y:auto;padding:14px 20px}
.vdd-reward-popup-list .vdd-reward-row{border-radius:8px;padding:9px 10px;transition:background .15s}
.vdd-reward-popup-list .vdd-reward-row:hover{background:rgba(255,255,255,.06)}
.vdd-reward-popup-foot{padding:14px 20px 18px;text-align:center;border-top:1px solid rgba(255,255,255,.10)}
.vdd-reward-popup-close{display:inline-flex;align-items:center;justify-content:center;min-width:120px;min-height:40px;line-height:2;border-radius:10px;border:1px solid rgba(125,211,252,.45);background:rgba(16,33,78,.72);color:#48c7ff;font-weight:1000;text-decoration:none;transition:filter .18s,transform .18s}
.vdd-reward-popup-close:hover{filter:brightness(1.15);color:#fff;transform:translateY(-1px)}
.vdd-pk-ribbon[onclick]{transition:transform .2s ease,box-shadow .2s ease}
.vdd-pk-ribbon[onclick]:hover{transform:rotate(3deg) scale(1.05);box-shadow:0 6px 20px rgba(250,204,21,.3)}
</style>

<div class="vdd-page">
    <section class="vdd-hero vdd-banner-hero">
        <div class="vdd-banner"><img src="$arenaBanner" alt="Vấn Đỉnh Đài"></div>
        <div class="vdd-pk-ribbon" onclick="document.getElementById('vdd-reward-modal').classList.add('show')" style="cursor:pointer">THƯỞNG TOP</div>
        <div class="vdd-hero-content">
        <div class="vdd-floor-mark">$rankBadge</div>
        <div class="vdd-kicker">Đấu trường Thiên Kiêu</div>
        <h1>Vấn Đỉnh Đài</h1>
        <p>Khiêu chiến đạo hữu khác để tranh bảng Thiên Kiêu. Chiến thắng tăng hạng, nhận tu vi và kinh nghiệm võ công đang trang bị.</p>
        <div class="vdd-stats">
            <div class="vdd-stat"><span>Đạo hữu</span><b>$safeName</b></div>
            <div class="vdd-stat"><span>Hạng</span><b>#$rank</b></div>
            <div class="vdd-stat"><span>Điểm</span><b>$score</b></div>
            <div class="vdd-stat"><span>Lượt hôm nay</span><b>$leftFights / $dailyLimit</b></div>
        </div>
        $rewardTimeNotice
        $notice
        $rewardNotice
        </div>
    </section>

    <div class="vdd-layout">
        <section class="vdd-panel">
            <div class="vdd-kicker">Đối thủ gần hạng</div>
            $opponentHtml
            <div class="vdd-actions">
                <a class="vdd-btn home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
                <a class="vdd-btn wg" href="?cmd=$wgcmd">Võ Công</a>
                <a class="vdd-btn home" href="?cmd=$arenaCmd">Làm mới</a>
            </div>
        </section>
        <section class="vdd-panel">
            <div class="vdd-kicker">Thiên Kiêu Bảng</div>
            <div class="vdd-stat"><span>Thắng / Thua</span><b>$wins / $losses</b></div>
            $topHtml
        </section>
    </div>
    $battleBlock
</div>

<div id="vdd-reward-modal" class="vdd-reward-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="vdd-reward-popup">
        <div class="vdd-reward-popup-head">
            <div class="vdd-reward-popup-icon">🏆</div>
            <h3>Thưởng Top Ngày</h3>
            <p>Chốt thưởng top 100 mỗi ngày lúc 21:00</p>
        </div>
        <div class="vdd-reward-popup-list">
            $rewardRows
        </div>
        <div class="vdd-reward-popup-foot">
            <a class="vdd-reward-popup-close" href="javascript:void(0)" onclick="document.getElementById('vdd-reward-modal').classList.remove('show')">Đóng</a>
        </div>
    </div>
</div>
HTML;

echo $html;
?>
