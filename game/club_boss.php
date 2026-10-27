<?php
/**
 * Boss Tông Môn - Hệ thống raid theo khung giờ
 * - 18:00 - 18:30 mỗi ngày
 * - Toàn bộ thành viên cùng tấn công
 * - Xếp hạng sát thương + thưởng
 * - Hook quest: boss_damage, boss_kill
 */
require_once __DIR__ . '/../class/function/club_quest.php';

$isAjax = $isAjaxEarly;

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$clubcmd = $encode->encode("cmd=club&sid=$sid");
$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");

/* === CONFIG === */
$bossCfg = [
    'open_hour'      => 0,
    'open_minute'    => 0,
    'duration_min'   => 1440,
    'base_hp'        => 100000, // Máu BOSS
    'hp_per_lv'      => 500000,
    'base_atk'       => 800,  // Công BOSS
    'atk_per_lv'     => 15,
    'base_def'       => 1500, // BOSS Thủ
    'def_per_lv'     => 5,
    'base_crit'      => 12,
    'crit_per_lv'    => 1,
    'reward_exp'     => 300,
    'reward_lt'      => 150,
    'reward_mt'      => 15,
	
    'kill_bonus_exp' => 2000,
    'kill_bonus_lt'  => 500,
	
    'top1_bonus_exp' => 1500,
    'top1_bonus_lt'  => 800,
    'top1_bonus_mt'  => 50,
	
    'top2_bonus_exp' => 1000,
    'top2_bonus_lt'  => 500,
    'top2_bonus_mt'  => 30,
	
    'top3_bonus_exp' => 500,
    'top3_bonus_lt'  => 250,
    'top3_bonus_mt'  => 15,
	
    'cooldown_sec'   => 3, // Thời gian delay nút tấn công
];

/* === TIME HELPERS === */
function cb_now() {
    return new DateTime();
}

function cb_open_time($cfg) {
    $t = new DateTime('today');
    $t->setTime($cfg['open_hour'], $cfg['open_minute'], 0);
    return $t;
}

function cb_close_time($cfg) {
    $t = cb_open_time($cfg);
    $t->modify('+' . $cfg['duration_min'] . ' minutes');
    return $t;
}

function cb_is_open($cfg) {
    $now = cb_now();
    return $now >= cb_open_time($cfg) && $now < cb_close_time($cfg);
}

function cb_is_before($cfg) {
    return cb_now() < cb_open_time($cfg);
}

function cb_time_left($cfg) {
    $diff = cb_now()->diff(cb_close_time($cfg));
    return max(0, $diff->s + $diff->i * 60 + $diff->h * 3600);
}

function cb_seconds_until_open($cfg) {
    $diff = cb_now()->diff(cb_open_time($cfg));
    return max(0, $diff->s + $diff->i * 60 + $diff->h * 3600);
}

/* === CHECK CLUB === */
$clubplayer = null;
$stmt = $dblj->prepare("SELECT * FROM clubplayer WHERE uid = ?");
$stmt->execute(array($player->uid));
$clubplayer = $stmt->fetch(\PDO::FETCH_OBJ);

if (!$clubplayer || intval($clubplayer->clubid) <= 0) {
    if ($isAjax) ajaxJson(false, 'Ngươi chưa gia nhập tông môn nào!');
    echo '<div class="tcg-club-notice">Ngươi chưa gia nhập tông môn nào!</div>';
    return;
}

$myClubId = intval($clubplayer->clubid);
$today = date('Y-m-d');

/* === GET OR CREATE BOSS === */
$stmt = $dblj->prepare("SELECT * FROM club_boss WHERE clubid = ? AND boss_date = ?");
$stmt->execute(array($myClubId, $today));
$bossRow = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$bossRow) {
    /* Tính stats theo cấp tông môn */
    $stmt2 = $dblj->prepare("SELECT clublv FROM club WHERE clubid = ?");
    $stmt2->execute(array($myClubId));
    $clubLv = max(1, intval($stmt2->fetchColumn()));

    $bossHp  = $bossCfg['base_hp'] + ($clubLv - 1) * $bossCfg['hp_per_lv'];
    $bossAtk = $bossCfg['base_atk'] + ($clubLv - 1) * $bossCfg['atk_per_lv'];
    $bossDef = $bossCfg['base_def'] + ($clubLv - 1) * $bossCfg['def_per_lv'];
    $bossCrit = $bossCfg['base_crit'] + ($clubLv - 1) * $bossCfg['crit_per_lv'];

    $openT = sprintf('%02d:%02d:00', $bossCfg['open_hour'], $bossCfg['open_minute']);
    $closeDT = cb_close_time($bossCfg);
    $closeT = $closeDT->format('H:i:s');

    $stmt3 = $dblj->prepare("INSERT IGNORE INTO club_boss (clubid, boss_name, boss_lv, boss_hp, boss_maxhp, boss_atk, boss_def, boss_crit, boss_date, open_time, close_time, status) VALUES (?, 'Boss Tông Môn', ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt3->execute(array($myClubId, $clubLv, $bossHp, $bossHp, $bossAtk, $bossDef, $bossCrit, $today, $openT, $closeT));

    $stmt4 = $dblj->prepare("SELECT * FROM club_boss WHERE clubid = ? AND boss_date = ?");
    $stmt4->execute(array($myClubId, $today));
    $bossRow = $stmt4->fetch(\PDO::FETCH_ASSOC);
}

$bossId = intval($bossRow['id']);
$bossHp = intval($bossRow['boss_hp']);
$bossMaxHp = intval($bossRow['boss_maxhp']);
$bossAtk = intval($bossRow['boss_atk']);
$bossDef = intval($bossRow['boss_def']);
$bossCrit = intval($bossRow['boss_crit']);
$bossLv = intval($bossRow['boss_lv']);
$bossStatus = intval($bossRow['status']);

/* Tính stats theo cấp tông môn */
$clubLvRow = $dblj->prepare("SELECT clublv FROM club WHERE clubid = ?");
$clubLvRow->execute(array($myClubId));
$clubLvCalc = max(1, intval($clubLvRow->fetchColumn()));
$newMaxHp = $bossCfg['base_hp'] + ($clubLvCalc - 1) * $bossCfg['hp_per_lv'];
$newAtk = $bossCfg['base_atk'] + ($clubLvCalc - 1) * $bossCfg['atk_per_lv'];
$newDef = $bossCfg['base_def'] + ($clubLvCalc - 1) * $bossCfg['def_per_lv'];
$newCrit = $bossCfg['base_crit'] + ($clubLvCalc - 1) * $bossCfg['crit_per_lv'];

/* Nếu maxhp trong config thay đổi → sync lại boss chưa chết */
if ($bossStatus < 2 && $bossHp > 0) {
    if ($newMaxHp != $bossMaxHp || $newAtk != $bossAtk || $newDef != $bossDef || $newCrit != $bossCrit) {
        $hpDiff = $newMaxHp - $bossMaxHp;
        $syncHp = max(1, $bossHp + $hpDiff);
        $stmtSync = $dblj->prepare("UPDATE club_boss SET boss_maxhp=?, boss_hp=?, boss_atk=?, boss_def=?, boss_crit=?, boss_lv=? WHERE id=?");
        $stmtSync->execute(array($newMaxHp, $syncHp, $newAtk, $newDef, $newCrit, $clubLvCalc, $bossId));
        $bossMaxHp = $newMaxHp;
        $bossHp = $syncHp;
        $bossAtk = $newAtk;
        $bossDef = $newDef;
        $bossCrit = $newCrit;
        $bossLv = $clubLvCalc;
    }
}
$isOpen = cb_is_open($bossCfg);
$hpPercent = $bossMaxHp > 0 ? min(100, round($bossHp * 100 / $bossMaxHp)) : 0;

/* === HANDLE ATTACK (AJAX) === */
if ($isAjax && isset($_POST['attack']) && $_POST['attack'] == '1') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    /* Kiểm tra thời gian */
    if (!$isOpen) {
        echo json_encode(['ok' => false, 'msg' => 'Boss chưa mở cửa hoặc đã hết giờ!']);
        exit();
    }

    /* Kiểm tra boss đã chết chưa */
    if ($bossHp <= 0 || $bossStatus >= 2) {
        echo json_encode(['ok' => false, 'msg' => 'Boss đã bị tiêu diệt!']);
        exit();
    }

    /* Cooldown check */
    $cdKey = 'club_boss_cd_' . $myClubId . '_' . $today;
    if (isset($_SESSION[$cdKey]) && (time() - $_SESSION[$cdKey]) < $bossCfg['cooldown_sec']) {
        $wait = $bossCfg['cooldown_sec'] - (time() - $_SESSION[$cdKey]);
        echo json_encode(['ok' => false, 'msg' => "Tấn công quá nhanh! Chờ {$wait}s"]);
        exit();
    }
    $_SESSION[$cdKey] = time();

    /* Tính sát thương player lên boss */
    $wugongId = intval($player->wugong);
    $petDamage = 0;
    if ($wugongId > 0) {
        $cxwg = \player\wgcx($wugongId, $sid, $dblj);
        if ($cxwg && intval($cxwg->wglx) == 1) {
            $petDamage = max(1, round(intval($cxwg->wgjl) * 0.3));
        }
    }

    // Áp dụng penalty đan độc
    $toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
    $effectiveAtk = intval($player->ugj);
    $effectiveDef = intval($player->ufy);
    if ($toxinPenalty > 0) {
        $effectiveAtk = round($effectiveAtk * (1 - $toxinPenalty / 100));
        $effectiveDef = round($effectiveDef * (1 - $toxinPenalty / 100));
    }

    $playerAtk = $effectiveAtk;
    $reduced = round($playerAtk - ($bossDef * 0.75));
    $damage = max(1, $reduced);
    $isCrit = false;
    $isDodge = false;

    /* Bạo kích player */
    if (intval($player->ubj) > 0 && mt_rand(1, 100) <= min(75, intval($player->ubj))) {
        $damage = round($damage * 1.65);
        $isCrit = true;
    }

    /* Né tránh của boss (15% chance boss né) */
    if (mt_rand(1, 100) > 85) {
        $damage = 0;
        $isDodge = true;
    }

    /* Hút máu (lifesteal) */
    $heal = 0;
    if ($damage > 0 && intval($player->uxx) > 0) {
        $heal = max(0, intval(round($damage * intval($player->uxx) / 220)));
    }

    /* Boss phản đòn */
    $playerDef = $effectiveDef;
    $bossDmgRaw = round($bossAtk - ($playerDef * 0.65));
    $bossDamage = max(round($bossAtk * 0.12), $bossDmgRaw);
    $bossIsCrit = false;
    $bossIsDodge = false;

    /* Boss bạo kích */
    if ($bossCrit > 0 && mt_rand(1, 200) <= $bossCrit) {
        $bossDamage = round($bossDamage * 1.5);
        $bossIsCrit = true;
    }

    /* Player né tránh boss (15%) */
    if (mt_rand(1, 100) > 85) {
        $bossDamage = 0;
        $bossIsDodge = true;
    }

    /* Áp dụng damage lên boss — atomic update (player + pet) */
    $totalDmgToBoss = $damage + $petDamage;
    $stmt5 = $dblj->prepare("UPDATE club_boss SET boss_hp = GREATEST(0, boss_hp - ?) WHERE id = ? AND boss_hp > 0");
    $stmt5->execute(array($totalDmgToBoss, $bossId));
    if ($stmt5->rowCount() === 0 && $totalDmgToBoss > 0) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'msg' => 'Boss đã bị tiêu diệt!']);
        exit();
    }
    $newHp = max(0, $bossHp - $totalDmgToBoss);

    /* Boss damage applied to player HP */
    if ($bossDamage > 0 && !$isDodge) {
        $stmtPlayerHp = $dblj->prepare("SELECT uhp, umaxhp FROM game1 WHERE sid = ?");
        $stmtPlayerHp->execute(array($sid));
        $playerHpRow = $stmtPlayerHp->fetch(\PDO::FETCH_ASSOC);
        $currentHp = intval($playerHpRow['uhp']);
        $newPlayerHp = max(0, $currentHp - $bossDamage);
        $stmtUpdateHp = $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?");
        $stmtUpdateHp->execute(array($newPlayerHp, $sid));
        $heal = 0; // Boss hit, no lifesteal this round
    }

    /* Cập nhật damage stats */
    $stmt6 = $dblj->prepare("INSERT INTO club_boss_damage (boss_id, clubid, uid, sid, total_damage, hit_count, crit_count, dodge_count, pet_damage, last_hit_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE total_damage = total_damage + VALUES(total_damage), hit_count = hit_count + 1, crit_count = crit_count + VALUES(crit_count), dodge_count = dodge_count + VALUES(dodge_count), pet_damage = pet_damage + VALUES(pet_damage), last_hit_at = NOW()");
    $stmt6->execute(array($bossId, $myClubId, $player->uid, $sid, $totalDmgToBoss, $isCrit ? 1 : 0, $isDodge ? 1 : 0, $petDamage));

    /* Hook quest: boss_damage */
    if ($totalDmgToBoss > 0) {
        \player\club_quest_update($sid, 'boss_damage', intval($totalDmgToBoss), $dblj);
    }

    /* Hook quest: boss_attack (mỗi lần tấn công) */
    \player\club_quest_update($sid, 'boss_attack', 1, $dblj);

    /* Đọc HP thực tế sau atomic update */
    $stmtHp = $dblj->prepare("SELECT boss_hp FROM club_boss WHERE id = ?");
    $stmtHp->execute(array($bossId));
    $actualHp = intval($stmtHp->fetchColumn());

    /* Log hit */
    $stmt7 = $dblj->prepare("INSERT INTO club_boss_log (boss_id, uid, uname, damage, is_crit, is_dodge, pet_damage, boss_hp_after) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt7->execute(array($bossId, $player->uid, $player->uname, $totalDmgToBoss, $isCrit ? 1 : 0, $isDodge ? 1 : 0, $petDamage, $actualHp));
    $bossKilled = false;
    $rewards = [];
    if ($actualHp <= 0) {
        $bossKilled = true;
        $stmt8 = $dblj->prepare("UPDATE club_boss SET status = 2, killed_by = ?, killed_at = NOW() WHERE id = ?");
        $stmt8->execute(array($player->uid, $bossId));

        /* Hook quest: boss_kill */
        \player\club_quest_update($sid, 'boss_kill', 1, $dblj);

        /* Thưởng cho tất cả thành viên tham gia */
        $stmt9 = $dblj->prepare("SELECT d.uid, d.total_damage, g.uname FROM club_boss_damage d JOIN game1 g ON d.uid = g.uid WHERE d.boss_id = ?");
        $stmt9->execute(array($bossId));
        $damagers = $stmt9->fetchAll(\PDO::FETCH_ASSOC);

        $totalDmgAll = 0;
        foreach ($damagers as $d) $totalDmgAll += intval($d['total_damage']);
        if ($totalDmgAll <= 0) $totalDmgAll = 1;

        /* Sort theo damage giảm dần để xác định top 3 */
        usort($damagers, function($a, $b) {
            return intval($b['total_damage']) - intval($a['total_damage']);
        });

        foreach ($damagers as $idx => $d) {
            $dUid = intval($d['uid']);
            $dDmg = intval($d['total_damage']);
            $dRatio = $dDmg / $totalDmgAll;

            $expReward = $bossCfg['reward_exp'] + round($dRatio * $bossCfg['reward_exp'] * 3);
            $ltReward = $bossCfg['reward_lt'] + round($dRatio * $bossCfg['reward_lt'] * 2);
            $mtReward = $bossCfg['reward_mt'];
            $bonusMsg = [];

            /* Top 3 bonus */
            if ($idx == 0) {
                $expReward += $bossCfg['top1_bonus_exp'];
                $ltReward += $bossCfg['top1_bonus_lt'];
                $mtReward += $bossCfg['top1_bonus_mt'];
                $bonusMsg[] = '🥇 Top 1';
            } elseif ($idx == 1) {
                $expReward += $bossCfg['top2_bonus_exp'];
                $ltReward += $bossCfg['top2_bonus_lt'];
                $mtReward += $bossCfg['top2_bonus_mt'];
                $bonusMsg[] = '🥈 Top 2';
            } elseif ($idx == 2) {
                $expReward += $bossCfg['top3_bonus_exp'];
                $ltReward += $bossCfg['top3_bonus_lt'];
                $mtReward += $bossCfg['top3_bonus_mt'];
                $bonusMsg[] = '🥉 Top 3';
            }

            /* Cộng EXP */
            $toxinPenalty = \player\luonden_get_stat_penalty($dUid, $dblj);
            $effectiveExp = ($toxinPenalty > 0) ? max(1, intval($expReward * (1 - $toxinPenalty / 100))) : intval($expReward);
            $stmt10 = $dblj->prepare("UPDATE game1 SET uexp = uexp + ? WHERE uid = ?");
            $stmt10->execute(array($effectiveExp, $dUid));

            /* Cộng LT */
            $stmt11 = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE uid = ?");
            $stmt11->execute(array(intval($ltReward), $dUid));

            /* Cộng MT */
            $stmt12 = $dblj->prepare("UPDATE game1 SET uczb = uczb + ? WHERE uid = ?");
            $stmt12->execute(array(intval($mtReward), $dUid));

            /* Bonus cho người kết liễu */
            if ($dUid == $player->uid) {
                $killExp = $bossCfg['kill_bonus_exp'];
                $killLt = $bossCfg['kill_bonus_lt'];
                $stmt13 = $dblj->prepare("UPDATE game1 SET uexp = uexp + ?, uyxb = uyxb + ? WHERE uid = ?");
                $stmt13->execute(array(intval($killExp), intval($killLt), $dUid));
                $bonusMsg[] = "🎯 Kết Liễu +{$killExp}EXP +{$killLt}LT";
            }

            $rName = htmlspecialchars(isset($d['uname']) ? $d['uname'] : '???', ENT_QUOTES, 'UTF-8');
            $bonusStr = count($bonusMsg) ? ' (' . implode(' ', $bonusMsg) . ')' : '';
            $rewards[] = "{$rName}: +{$expReward} EXP, +{$ltReward} LT, +{$mtReward} MT{$bonusStr}";
        }

        /* Cộng EXP tông môn: 500 + 50 × số người tham gia */
        $memberCount = count($damagers);
        $clubExpBonus = 500 + (50 * $memberCount);
        $stmtClubExp = $dblj->prepare("UPDATE club SET clubexp = clubexp + ? WHERE clubid = ?");
        $stmtClubExp->execute(array(intval($clubExpBonus), $myClubId));
        $rewards[] = "🏛 Tông môn: +{$clubExpBonus} EXP tông môn (500 + 50 × {$memberCount})";

        /* Thông báo global */
        $stmt14 = $dblj->prepare("INSERT INTO ggliaotian(name, msg, uid) VALUES(?, ?, 0)");
        $safeName = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
        $stmt14->execute(array('【Hệ Thống】', "[{$safeName}] đã tiêu diệt Boss Tông Môn! Tông môn nhận +{$clubExpBonus} EXP"));
    }

    /* Lấy ranking hiện tại */
    $stmtRank = $dblj->prepare("SELECT d.uid, d.total_damage, g.uname FROM club_boss_damage d JOIN game1 g ON d.uid = g.uid WHERE d.boss_id = ? ORDER BY d.total_damage DESC LIMIT 10");
    $stmtRank->execute(array($bossId));
    $ranking = $stmtRank->fetchAll(\PDO::FETCH_ASSOC);

    $rankHtml = '';
    $rank = 1;
    foreach ($ranking as $r) {
        $rName = htmlspecialchars($r['uname'], ENT_QUOTES, 'UTF-8');
        $rDmg = number_format(intval($r['total_damage']));
        $medal = '';
        if ($rank == 1) $medal = '🥇';
        elseif ($rank == 2) $medal = '🥈';
        elseif ($rank == 3) $medal = '🥉';
        $isMe = (intval($r['uid']) == $player->uid) ? ' cb-rank-me' : '';
        $rankHtml .= "<div class='cb-rank-row{$isMe}'><span class='cb-rank-num'>{$medal}{$rank}</span><span class='cb-rank-name'>{$rName}</span><span class='cb-rank-dmg'>{$rDmg}</span></div>";
        $rank++;
    }

    $critText = $isCrit ? ' <span class="cb-crit-badge">Bạo Kích!</span>' : '';
    $dodgeText = $isDodge ? '<span class="cb-dodge-badge">Né Tránh!</span>' : '';

    $result = [
        'ok' => true,
        'damage' => $totalDmgToBoss,
        'is_crit' => $isCrit,
        'is_dodge' => $isDodge,
        'pet_damage' => $petDamage,
        'boss_damage' => $bossDamage,
        'boss_is_crit' => $bossIsCrit,
        'boss_is_dodge' => $bossIsDodge,
        'heal' => $heal,
        'player_atk' => $playerAtk,
        'player_def' => $playerDef,
        'boss_hp' => $newHp,
        'boss_maxhp' => $bossMaxHp,
        'hp_percent' => $hpPercent,
        'boss_killed' => $bossKilled,
        'rank_html' => $rankHtml,
        'player_name' => htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8'),
        'boss_name' => 'Boss Tông Môn',
    ];

    if ($bossKilled) {
        $result['rewards'] = $rewards;
    }

    echo json_encode($result);
    exit();
}

/* === AJAX: GET STATUS (for auto-refresh) === */
if ($isAjax && isset($_GET['status']) && $_GET['status'] == '1') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    $stmt15 = $dblj->prepare("SELECT boss_hp, boss_maxhp, status FROM club_boss WHERE id = ?");
    $stmt15->execute(array($bossId));
    $status = $stmt15->fetch(\PDO::FETCH_ASSOC);

    $hpPct = $status['boss_maxhp'] > 0 ? min(100, round($status['boss_hp'] * 100 / $status['boss_maxhp'])) : 0;

    $stmtRank2 = $dblj->prepare("SELECT d.uid, d.total_damage, g.uname FROM club_boss_damage d JOIN game1 g ON d.uid = g.uid WHERE d.boss_id = ? ORDER BY d.total_damage DESC LIMIT 10");
    $stmtRank2->execute(array($bossId));
    $ranking2 = $stmtRank2->fetchAll(\PDO::FETCH_ASSOC);

    $rankHtml2 = '';
    $rank2 = 1;
    foreach ($ranking2 as $r2) {
        $rName2 = htmlspecialchars($r2['uname'], ENT_QUOTES, 'UTF-8');
        $rDmg2 = number_format(intval($r2['total_damage']));
        $medal2 = '';
        if ($rank2 == 1) $medal2 = '🥇';
        elseif ($rank2 == 2) $medal2 = '🥈';
        elseif ($rank2 == 3) $medal2 = '🥉';
        $isMe2 = (intval($r2['uid']) == $player->uid) ? ' cb-rank-me' : '';
        $rankHtml2 .= "<div class='cb-rank-row{$isMe2}'><span class='cb-rank-num'>{$medal2}{$rank2}</span><span class='cb-rank-name'>{$rName2}</span><span class='cb-rank-dmg'>{$rDmg2}</span></div>";
        $rank2++;
    }

    $logs = [];
    if (intval($status['status']) < 2) {
        $stmtLog = $dblj->prepare("SELECT uname, damage, is_crit, is_dodge, created_at FROM club_boss_log WHERE boss_id = ? ORDER BY id DESC LIMIT 8");
        $stmtLog->execute(array($bossId));
        $logRows = $stmtLog->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($logRows as $lr) {
            $logs[] = [
                'name' => htmlspecialchars($lr['uname'], ENT_QUOTES, 'UTF-8'),
                'damage' => intval($lr['damage']),
                'crit' => intval($lr['is_crit']),
                'dodge' => intval($lr['is_dodge']),
            ];
        }
    }

    echo json_encode([
        'ok' => true,
        'boss_hp' => intval($status['boss_hp']),
        'boss_maxhp' => intval($status['boss_maxhp']),
        'hp_percent' => $hpPct,
        'status' => intval($status['status']),
        'rank_html' => $rankHtml2,
        'logs' => $logs,
        'time_left' => cb_time_left($bossCfg),
        'is_open' => $isOpen,
    ]);
    exit();
}

/* === RENDER PAGE === */

/* Time info */
$timeLeft = cb_time_left($bossCfg);
$secondsUntilOpen = cb_seconds_until_open($bossCfg);

/* Attack button state */
$attackEnabled = $isOpen && $bossHp > 0 && $bossStatus < 2;
$attackLabel = '⚔ Tấn Công';
$attackSub = '';
if ($bossStatus >= 2) {
    $attackLabel = '✓ Boss Đã Chết';
    $attackSub = 'Hẹn gặp lại ngày mai!';
} elseif (!$isOpen && cb_is_before($bossCfg)) {
    $hours = floor($secondsUntilOpen / 3600);
    $mins = floor(($secondsUntilOpen % 3600) / 60);
    $secs = $secondsUntilOpen % 60;
    $attackLabel = '⏰ Chưa Mở Cửa';
    $attackSub = "Mở cửa sau: {$hours}h {$mins}m {$secs}s";
} elseif (!$isOpen) {
    $attackLabel = '⏰ Hết Giờ';
    $attackSub = 'Boss đã hết thời gian mở cửa. Hẹn gặp lại ngày mai!';
}

$attackBtnCss = 'cb-attack-btn disabled';
if ($attackEnabled) $attackBtnCss = 'cb-attack-btn ready';

$attackEnabledJs = $attackEnabled ? 'true' : 'false';
$isOpenJs = $isOpen ? 'true' : 'false';

$hpCssClass = 'high';
if ($hpPercent <= 60) $hpCssClass = 'mid';
if ($hpPercent <= 30) $hpCssClass = 'low';
if ($hpPercent <= 10) $hpCssClass = 'critical';

$bossImgCss = 'cb-boss-img';
if ($bossStatus >= 2 || $bossHp <= 0) $bossImgCss = 'cb-boss-img dead';

$timeBadgeCss = 'cb-time-badge waiting';
$timeBadgeText = 'CHỜ MỞ CỬA';
if ($isOpen) { $timeBadgeCss = 'cb-time-badge open'; $timeBadgeText = 'ĐANG MỞ CỬA'; }
elseif ($bossStatus >= 2) { $timeBadgeCss = 'cb-time-badge closed'; $timeBadgeText = 'ĐÃ KẾT THÚC'; }

$stmtRank3 = $dblj->prepare("SELECT d.uid, d.total_damage, d.hit_count, d.crit_count, g.uname FROM club_boss_damage d JOIN game1 g ON d.uid = g.uid WHERE d.boss_id = ? ORDER BY d.total_damage DESC LIMIT 10");
$stmtRank3->execute(array($bossId));
$ranking3 = $stmtRank3->fetchAll(\PDO::FETCH_ASSOC);

$rankHtml3 = '';
$rank3 = 1;
foreach ($ranking3 as $r3) {
    $rName3 = htmlspecialchars($r3['uname'], ENT_QUOTES, 'UTF-8');
    $rDmg3 = number_format(intval($r3['total_damage']));
    $rHits = intval($r3['hit_count']);
    $medal3 = '';
    if ($rank3 == 1) $medal3 = '🥇';
    elseif ($rank3 == 2) $medal3 = '🥈';
    elseif ($rank3 == 3) $medal3 = '🥉';
    $isMe3 = (intval($r3['uid']) == $player->uid) ? ' cb-rank-me' : '';
    $rankHtml3 .= "<div class='cb-rank-row{$isMe3}'><span class='cb-rank-num'>{$medal3}{$rank3}</span><span class='cb-rank-name'>{$rName3}</span><span class='cb-rank-dmg'>{$rDmg3}</span><span class='cb-rank-hits'>{$rHits} hit(s)</span></div>";
    $rank3++;
}
if ($rankHtml3 === '') $rankHtml3 = '<div class="cb-empty">Chưa có sát thương nào</div>';

$stmtLog2 = $dblj->prepare("SELECT uname, damage, is_crit, is_dodge, created_at FROM club_boss_log WHERE boss_id = ? ORDER BY id DESC LIMIT 8");
$stmtLog2->execute(array($bossId));
$logRows2 = $stmtLog2->fetchAll(\PDO::FETCH_ASSOC);
$logHtml = '';
foreach ($logRows2 as $lr2) {
    $lName = htmlspecialchars($lr2['uname'], ENT_QUOTES, 'UTF-8');
    $lDmg = number_format(intval($lr2['damage']));
    $cls = 'cb-log-row';
    if (intval($lr2['is_crit'])) $cls .= ' crit';
    if (intval($lr2['is_dodge'])) $cls .= ' dodge';
    $badge = '';
    if (intval($lr2['is_crit'])) $badge = ' <span class="cb-crit-badge">Bạo</span>';
    if (intval($lr2['is_dodge'])) $badge = ' <span class="cb-dodge-badge">Né</span>';
    $logHtml .= "<div class='{$cls}'><span class='cb-log-name'>{$lName}</span> gây <span class='cb-log-dmg'>{$lDmg}</span>{$badge}</div>";
}
if ($logHtml === '') $logHtml = '<div class="cb-empty">Chưa có lịch sử tấn công</div>';

echo <<<HTML
<style>
.cb-page{max-width:560px;margin:0 auto;padding:8px 10px}
.cb-card{background:linear-gradient(135deg,rgba(8,20,60,.92),rgba(20,10,50,.88));border:1px solid rgba(125,211,252,.22);border-radius:18px;padding:18px;margin-bottom:14px;position:relative;overflow:hidden}
.cb-card::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 0%,rgba(125,211,252,.08),transparent 60%);pointer-events:none}
.cb-header{text-align:center;margin-bottom:12px}
.cb-title{font-size:22px;font-weight:1000;color:#fff;text-shadow:0 0 12px rgba(125,211,252,.5)}
.cb-title::before{content:"⚔ ";color:#f59e0b}
.cb-subtitle{font-size:13px;color:#8ba4c8;margin-top:4px}
.cb-time-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:800;margin-top:8px}
.cb-time-badge.open{background:rgba(34,197,94,.18);border:1px solid rgba(34,197,94,.4);color:#4ade80;animation:cbPulse 2s ease-in-out infinite}
.cb-time-badge.closed{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171}
.cb-time-badge.waiting{background:rgba(250,204,21,.15);border:1px solid rgba(250,204,21,.3);color:#fde68a}

.cb-boss-area{text-align:center;padding:10px 0}
.cb-boss-img{width:200px;max-width:80%;height:auto;border-radius:14px;filter:drop-shadow(0 0 20px rgba(125,211,252,.3));transition:transform .15s}
.cb-boss-img.shake{animation:cbShake .35s ease-in-out}
.cb-boss-img.dead{filter:brightness(.4) saturate(0);transform:scale(.95)}

.cb-hp-section{margin-top:14px}
.cb-hp-bar{height:28px;background:rgba(255,255,255,.08);border-radius:14px;overflow:hidden;position:relative;border:1px solid rgba(255,255,255,.08)}
.cb-hp-fill{height:100%;border-radius:14px;transition:width .5s ease,background .5s;position:relative}
.cb-hp-fill.high{background:linear-gradient(90deg,#22c55e,#16a34a)}
.cb-hp-fill.mid{background:linear-gradient(90deg,#f59e0b,#d97706)}
.cb-hp-fill.low{background:linear-gradient(90deg,#ef4444,#dc2626)}
.cb-hp-fill.critical{background:linear-gradient(90deg,#dc2626,#991b1b);animation:cbHpFlash .6s ease-in-out infinite}
.cb-hp-text{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:1000;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.6)}
.cb-hp-label{font-size:12px;color:#8ba4c8;text-align:center;margin-top:4px}
.cb-boss-stats{display:flex;justify-content:center;gap:16px;margin-top:8px;font-size:12px;color:#8ba4c8}
.cb-boss-stats span b{color:#fde68a}

.cb-attack-section{text-align:center;margin:14px 0}
.cb-attack-btn{display:inline-flex;align-items:center;gap:8px;padding:14px 48px;border:none;border-radius:14px;font-size:17px;font-weight:1000;cursor:pointer;transition:all .2s;text-decoration:none;color:#fff}
.cb-attack-btn.ready{background:linear-gradient(180deg,#ef4444,#991b1b);box-shadow:0 0 20px rgba(239,68,68,.3),0 4px 12px rgba(0,0,0,.3)}
.cb-attack-btn.ready:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(239,68,68,.5),0 6px 20px rgba(0,0,0,.4)}
.cb-attack-btn.ready:active{transform:translateY(0);box-shadow:0 0 10px rgba(239,68,68,.3)}
.cb-attack-btn.disabled{background:linear-gradient(180deg,#6b7280,#4b5563);cursor:not-allowed;opacity:.7}
.cb-attack-sub{font-size:12px;color:#8ba4c8;margin-top:6px}

.cb-tabs{display:flex;gap:2px;margin-bottom:10px}
.cb-tab{flex:1;padding:10px;text-align:center;border-radius:10px 10px 0 0;font-size:13px;font-weight:800;cursor:pointer;background:rgba(255,255,255,.04);color:#8ba4c8;border:1px solid transparent;border-bottom:none;transition:all .2s}
.cb-tab.active{background:rgba(125,211,252,.08);color:#7dd3ff;border-color:rgba(125,211,252,.2)}
.cb-tab-content{min-height:120px}
.cb-tab-panel{display:none}
.cb-tab-panel.active{display:block}

.cb-rank-row{display:flex;align-items:center;padding:8px 10px;border-radius:8px;margin-bottom:3px;background:rgba(255,255,255,.03);font-size:13px}
.cb-rank-row.cb-rank-me{background:rgba(125,211,252,.1);border:1px solid rgba(125,211,252,.2)}
.cb-rank-num{width:50px;font-weight:800;color:#fde68a}
.cb-rank-name{flex:1;color:#dcecff;font-weight:700}
.cb-rank-dmg{color:#f87171;font-weight:800;min-width:70px;text-align:right}
.cb-rank-hits{color:#8ba4c8;font-size:11px;min-width:60px;text-align:right}

.cb-log-row{padding:5px 10px;font-size:12px;color:#8ba4c8;border-bottom:1px solid rgba(255,255,255,.03)}
.cb-log-row.crit{color:#fde68a}
.cb-log-row.dodge{color:#6b7280;font-style:italic}
.cb-log-name{font-weight:700;color:#dcecff}
.cb-log-dmg{color:#f87171;font-weight:800}

.cb-crit-badge{display:inline-block;padding:1px 6px;border-radius:6px;background:rgba(245,158,11,.2);color:#fbbf24;font-size:10px;font-weight:800;animation:cbCritPop .4s ease,textShadowGlow .6s ease .1s}
.cb-dodge-badge{display:inline-block;padding:1px 6px;border-radius:6px;background:rgba(107,114,128,.2);color:#9ca3af;font-size:10px;font-weight:800}

.cb-dmg-float{position:fixed;pointer-events:none;font-weight:1000;font-size:22px;z-index:9999;animation:cbFloatUp 1.2s ease-out forwards}
.cb-dmg-float.crit{color:#fbbf24;font-size:28px}
.cb-dmg-float.normal{color:#ef4444}
.cb-dmg-float.dodge{color:#6b7280;font-style:italic;font-size:18px}
.cb-dmg-float.heal{color:#4ade80}

.cb-empty{text-align:center;padding:24px;color:#6b7280;font-size:13px}

#cb-combat-feed{margin-bottom:10px}
#cb-server-log{border-top:1px solid rgba(125,211,252,.1);padding-top:8px}

.cb-battle-entry{padding:10px 12px;margin-bottom:8px;border-radius:10px;border:1px solid rgba(125,211,252,.12);background:linear-gradient(135deg,rgba(8,20,60,.6),rgba(15,8,40,.5));animation:cbCombatIn .25s ease}
.cb-line{padding:2px 0;font-size:13px;line-height:1.5}
.cb-line-icon{margin-right:4px}
.cb-line-player{color:#dcecff}
.cb-line-pet{color:#c4b5fd}
.cb-line-boss{color:#fca5a5}
.cb-line-heal{color:#86efac}
.cb-line-detail{color:#6b7280;font-size:12px;padding-left:22px}
.cb-line-arrow{color:#4b5563;margin-right:2px}
.cb-dmg-red{color:#ef4444}
.cb-dmg-purple{color:#a78bfa}
.cb-dmg-orange{color:#fb923c}
.cb-dmg-green{color:#4ade80}

@keyframes cbCombatIn{0%{opacity:0;transform:translateY(-6px)}100%{opacity:1;transform:translateY(0)}}
.cb-reward-card{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:12px;padding:14px;margin-top:10px}
.cb-reward-title{color:#4ade80;font-weight:800;margin-bottom:6px}
.cb-reward-item{font-size:13px;color:#dcecff;padding:3px 0}

@keyframes cbShake{0%,100%{transform:translateX(0)}15%{transform:translateX(-8px) rotate(-1deg)}30%{transform:translateX(6px) rotate(1deg)}45%{transform:translateX(-4px)}60%{transform:translateX(3px)}75%{transform:translateX(-1px)}}
@keyframes cbPulse{0%,100%{opacity:1}50%{opacity:.7}}
@keyframes cbHpFlash{0%,100%{opacity:1}50%{opacity:.6}}
@keyframes cbFloatUp{0%{opacity:1;transform:translateY(0) scale(1)}50%{opacity:1;transform:translateY(-40px) scale(1.1)}100%{opacity:0;transform:translateY(-80px) scale(.8)}}
@keyframes cbCritPop{0%{transform:scale(.5)}50%{transform:scale(1.3)}100%{transform:scale(1)}}
@keyframes textShadowGlow{0%{text-shadow:0 0 2px rgba(251,191,36,.4)}40%{text-shadow:0 0 12px rgba(251,191,36,.9),0 0 24px rgba(251,191,36,.5),0 0 40px rgba(251,191,36,.3)}100%{text-shadow:0 0 2px rgba(251,191,36,.4)}}
</style>

<div class="cb-page">
    <div class="cb-card">
        <div class="cb-header">
            <div class="cb-title">Boss Tông Môn</div>
            <div class="cb-subtitle">Cấp {$bossLv} • Sát thương tích lũy toàn tông môn</div>
            <div class="{$timeBadgeCss}" id="cb-time-badge">
                {$timeBadgeText}
            </div>
        </div>

        <div class="cb-boss-area">
            <img id="cb-boss-img" class="{$bossImgCss}" src="images/tongmon/bossbang.png" alt="Boss Tông Môn" />
            <div class="cb-hp-section">
                <div class="cb-hp-bar">
                    <div id="cb-hp-fill" class="cb-hp-fill {$hpCssClass}" style="width:{$hpPercent}%"></div>
                    <div class="cb-hp-text" id="cb-hp-text">{$bossHp} / {$bossMaxHp}</div>
                </div>
                <div class="cb-hp-label" id="cb-hp-label">HP còn lại: {$hpPercent}%</div>
            </div>
            <div class="cb-boss-stats">
                <span>⚔ ATK: <b>{$bossAtk}</b></span>
                <span>🛡 DEF: <b>{$bossDef}</b></span>
                <span>💥 Crit: <b>{$bossCrit}%</b></span>
            </div>
        </div>

        <div class="cb-attack-section">
            <a id="cb-attack-btn" class="{$attackBtnCss}" href="javascript:void(0)" onclick="cbAttack()">
                {$attackLabel}
            </a>
            <div class="cb-attack-sub" id="cb-attack-sub">{$attackSub}</div>
        </div>
    </div>

    <div class="cb-card">
        <div class="cb-tabs">
            <div class="cb-tab active" onclick="cbSwitchTab('log')">📋 Lịch Sử</div>
            <div class="cb-tab" onclick="cbSwitchTab('rank')">🏆 Xếp Hạng</div>
        </div>
        <div class="cb-tab-content">
            <div id="cb-tab-log" class="cb-tab-panel active">
                <div id="cb-combat-feed"></div>
                <div id="cb-server-log">{$logHtml}</div>
            </div>
            <div id="cb-tab-rank" class="cb-tab-panel">
                {$rankHtml3}
            </div>
        </div>
    </div>

    <div style="text-align:center;display:flex;gap:10px;justify-content:center">
        <a class="npc-nav-btn" href="?cmd=$clubcmd">🏛 Tông Môn</a>
        <a class="npc-nav-btn" href="?cmd=$ztcmd">👤 Nhân Vật</a>
        <a class="npc-nav-btn primary" href="?cmd=$gonowmid">🏠 Đại Sảnh</a>
    </div>
</div>

<script>
var CB_CONFIG = {
    clubid: {$myClubId},
    boss_id: {$bossId},
    boss_date: '{$today}',
    uid: {$player->uid},
    refresh_interval: 2000,
    attack_enabled: {$attackEnabledJs},
    time_left: {$timeLeft},
    is_open: {$isOpenJs},
    cooldown: {$bossCfg['cooldown_sec']}
};
</script>
<script src="js/club_boss.js"></script>
HTML;
