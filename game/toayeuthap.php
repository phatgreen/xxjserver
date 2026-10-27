<?php
if (!isset($sid) || $sid == '') {
    exit('Thiếu mã phiên nhân vật');
}

function tyt_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tyt_state($sid, $dblj)
{
    $stmt = $dblj->prepare("SELECT * FROM player_toayeuthap WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $row['floor'] = max(1, min(151, intval($row['floor'])));
        $row['best_floor'] = max(0, intval($row['best_floor']));
        $row['wins'] = max(0, intval($row['wins']));
        $row['last_reset_week'] = isset($row['last_reset_week']) ? intval($row['last_reset_week']) : 0;

        $currentWeek = intval(date('W'));
        $todayDow = intval(date('N'));
        if ($todayDow == 1 && $row['last_reset_week'] != $currentWeek && $row['floor'] > 1) {
            $stmt = $dblj->prepare("UPDATE player_toayeuthap SET floor = 1, last_reset_week = ?, updated_at = NOW() WHERE sid = ?");
            $stmt->execute(array($currentWeek, $sid));
            $row['floor'] = 1;
            $row['last_reset_week'] = $currentWeek;
        }

        return $row;
    }

    $currentWeek = intval(date('W'));
    $stmt = $dblj->prepare("INSERT INTO player_toayeuthap(sid, floor, best_floor, wins, last_reset_week, updated_at) VALUES(?, 1, 0, 0, ?, NOW())");
    $stmt->execute(array($sid, $currentWeek));
    return array('sid' => $sid, 'floor' => 1, 'best_floor' => 0, 'wins' => 0, 'last_reset_week' => $currentWeek, 'updated_at' => date('Y-m-d H:i:s'));
}

function tyt_boss($floor)
{
    $floor = max(1, min(150, intval($floor)));
    $names = array(
        'Yêu Linh Canh Cổng', 'Hắc Viêm Xà Vương', 'Thạch Cốt Ma Tướng',
        'U Minh Quỷ Tướng', 'Xích Luyện Hồ Ly', 'Thiên Độc Ma Tôn'
    );
    return array(
        'name' => $names[($floor - 1) % count($names)],
        'lv' => $floor + 8,
        'hp' => 520 + ($floor * 170) + intval(pow($floor, 2) * 18),
        'gj' => 42 + ($floor * 15) + intval(pow($floor, 1.25) * 4),
        'fy' => 18 + ($floor * 9) + intval(pow($floor, 1.18) * 3),
        'bj' => min(35, 3 + intval($floor / 8)),
        'xx' => min(25, intval($floor / 10))
    );
}

function tyt_simulate($player, $boss)
{
    $playerHp = max(1, intval($player->uhp));
    $playerMaxHp = max(1, intval($player->umaxhp));
    $bossHp = max(1, intval($boss['hp']));
    $rounds = array();
    $win = false;
    $round = 0;

    while ($round < 40 && $playerHp > 0 && $bossHp > 0) {
        $round++;
        $playerAtk = max(1, intval($player->ugj));
        $bossDef = max(0, intval($boss['fy']));
        $damage = max(1, intval(round($playerAtk - ($bossDef * 0.62))));
        if ($damage < intval($playerAtk * 0.12)) {
            $damage = max(1, intval(round($playerAtk * 0.12)));
        }
        if (mt_rand(1, 100) <= min(75, max(0, intval($player->ubj)))) {
            $damage = intval(round($damage * 1.65));
        }
        $bossHp -= $damage;
        $heal = max(0, intval(round($damage * max(0, intval($player->uxx)) / 220)));
        $playerHp = min($playerMaxHp, $playerHp + $heal);

        $bossDamage = 0;
        if ($bossHp > 0) {
            $bossDamage = max(1, intval(round(intval($boss['gj']) - (intval($player->ufy) * 0.68))));
            if ($bossDamage < intval($boss['gj'] * 0.12)) {
                $bossDamage = max(1, intval(round($boss['gj'] * 0.12)));
            }
            if (mt_rand(1, 100) <= intval($boss['bj'])) {
                $bossDamage = intval(round($bossDamage * 1.5));
            }
            $bossHeal = max(0, intval(round($bossDamage * intval($boss['xx']) / 240)));
            $bossHp += $bossHeal;
            if ($bossHp > intval($boss['hp'])) {
                $bossHp = intval($boss['hp']);
            }
            $playerHp -= $bossDamage;
        }

        if ($round <= 6 || $playerHp <= 0 || $bossHp <= 0) {
            $rounds[] = array(
                'round' => $round,
                'damage' => $damage,
                'heal' => $heal,
                'boss_damage' => $bossDamage,
                'player_hp' => max(0, $playerHp),
                'boss_hp' => max(0, $bossHp)
            );
        }
    }

    if ($bossHp <= 0 && $playerHp > 0) {
        $win = true;
    }

    return array(
        'win' => $win,
        'player_hp' => max(0, $playerHp),
        'boss_hp' => max(0, $bossHp),
        'round' => $round,
        'rounds' => $rounds
    );
}

function tyt_add_wugong_exp($sid, $dblj, $wgid, $amount)
{
    $wgid = intval($wgid);
    $amount = max(0, intval($amount));
    if ($wgid <= 0 || $amount <= 0) {
        return array('add' => 0, 'now' => 0, 'max' => 0, 'full' => false);
    }

    $cxwg = \player\wgcx($wgid, $sid, $dblj);
    if (!$cxwg) {
        return array('add' => 0, 'now' => 0, 'max' => 0, 'full' => false);
    }

    $now = intval($cxwg->wgxl);
    $max = max(1, intval($cxwg->wgxlmax));
    if ($now >= $max) {
        return array('add' => 0, 'now' => $now, 'max' => $max, 'full' => true);
    }

    $new = min($max, $now + $amount);
    $real = $new - $now;
    $stmt = $dblj->prepare("UPDATE playerwugong SET wgxl = ? WHERE wgid = ? AND sid = ?");
    $stmt->execute(array($new, $wgid, $sid));

    return array('add' => $real, 'now' => $new, 'max' => $max, 'full' => ($new >= $max));
}

function tyt_give_item($sid, $player, $dblj, $djid, $amount)
{
    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
    $stmt->execute(array($sid, $djid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
        $stmt->execute(array($amount, $sid, $djid));
    } else {
        $stmt = $dblj->prepare("INSERT INTO playerdaoju (sid, djid, djsum) VALUES (?, ?, ?)");
        $stmt->execute(array($sid, $djid, $amount));
    }

    $djConfig = \player\daoju_config_get($djid);
    return isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
}

function tyt_floor_rewards($floor)
{
    $ltReward = 50 + ($floor * 5);
    $items = array();

    if ($floor % 50 == 0) {
        $ltReward += 1000;
        for ($i = 0; $i < 5; $i++) {
            $items[] = array('id' => 32 + mt_rand(0, 4), 'count' => 1);
        }
    } elseif ($floor % 10 == 0) {
        $ltReward += 200;
        $items[] = array('id' => 32 + mt_rand(0, 4), 'count' => 1);
    } elseif ($floor % 2 == 0) {
        $ltReward += 50;
        $items[] = array('id' => 1, 'count' => 1);
    }

    return array('lt' => $ltReward, 'items' => $items);
}

function tyt_lock_if_dead($player, $encode, $sid)
{
    if (intval($player->uhp) > 0) {
        return;
    }
    $govandutieny = $encode->encode("cmd=npc&nid=17&sid=$sid");
    exit('<div class="tcg-injured-page">
        <section class="tcg-injured-card">
            <div class="tcg-injured-mark">!</div>
            <div class="tcg-injured-kicker">Khí huyết cạn kiệt</div>
            <h1>Đạo hữu đang trọng thương</h1>
            <p>Thân thể chưa ổn định, không thể tiếp tục leo Tỏa Yêu Tháp. Hãy tìm Vân Du Tiên Y [Trị Liệu] để trị thương trước khi tái chiến.</p>
            <div class="tcg-injured-actions">
                <a class="tcg-injured-btn medicine" href="?cmd='.$govandutieny.'">Vân Du Tiên Y</a>
            </div>
        </section>
    </div>');
}

$player = \player\getplayer($sid, $dblj);

$useyp1 = $encode->encode("cmd=toayeuthap&canshu=useyp&ypid=$player->yp1&sid=$sid");
$useyp2 = $encode->encode("cmd=toayeuthap&canshu=useyp&ypid=$player->yp2&sid=$sid");
$useyp3 = $encode->encode("cmd=toayeuthap&canshu=useyp&ypid=$player->yp3&sid=$sid");

if (isset($canshu) && $canshu == 'useyp') {
    \player\useyaopin($ypid, 1, $sid, $dblj);
    $player = \player\getplayer($sid, $dblj);
}

tyt_lock_if_dead($player, $encode, $sid);

$state = tyt_state($sid, $dblj);
$floor = intval($state['floor']);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$towerCmd = $encode->encode("cmd=toayeuthap&sid=$sid");
$fightCmd = $encode->encode("cmd=toayeuthap&canshu=fight&sid=$sid");
$wgcmd = $encode->encode("cmd=wgxl&sid=$sid");
$autoSkipCmd = $encode->encode("cmd=toayeuthap&canshu=autoskip&sid=$sid");
$boss = ($floor <= 150) ? tyt_boss($floor) : false;
$notice = '';
$battleHtml = '';

if (isset($canshu) && $canshu == 'autoskip' && $boss) {
    $maxSkip = max(1, intval($state['best_floor'] * 0.8));
    if ($floor >= $maxSkip) {
        $notice = '<div class="tyt-notice fail">Đã ở tầng cao, không cần tự động vượt.</div>';
    } else {
        $totalExp = 0;
        $totalLt = 0;
        $itemList = array();
        for ($f = $floor; $f < $maxSkip; $f++) {
            $totalExp += 140 + ($f * 42) + intval(pow($f, 2) * 2.2);
            $fReward = tyt_floor_rewards($f);
            $totalLt += $fReward['lt'];
            foreach ($fReward['items'] as $ri) {
                $found = false;
                foreach ($itemList as &$entry) {
                    if ($entry['id'] == $ri['id']) {
                        $entry['count'] += $ri['count'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $itemList[] = $ri;
                }
            }
        }
        \player\changeexp($sid, $dblj, $totalExp);
        \player\changeyxb(1, $totalLt, $sid, $dblj);
        foreach ($itemList as $ri) {
            tyt_give_item($sid, $player, $dblj, $ri['id'], $ri['count']);
        }
        $stmt = $dblj->prepare("UPDATE player_toayeuthap SET floor = ?, best_floor = ?, wins = wins + ?, last_reset_week = ?, updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($maxSkip, max(intval($state['best_floor']), $maxSkip), $maxSkip - $floor, intval(date('W')), $sid));

        $itemText = '';
        foreach ($itemList as $ri) {
            $djConfig = \player\daoju_config_get($ri['id']);
            $name = isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
            if ($itemText != '') $itemText .= ', ';
            $itemText .= '+' . $ri['count'] . ' ' . $name;
        }
        $notice = '<div class="tyt-notice win">Tự động vượt từ tầng '.$floor.' lên tầng '.$maxSkip.'! Nhận +'.number_format($totalExp, 0, ',', '.').' tu vi, +'.number_format($totalLt, 0, ',', '.').' Linh thạch'.($itemText != '' ? ', '.$itemText : '').'.</div>';
        $state = tyt_state($sid, $dblj);
        $floor = intval($state['floor']);
        $boss = ($floor <= 150) ? tyt_boss($floor) : false;
    }
}

if (isset($canshu) && $canshu == 'fight' && $boss) {
    $result = tyt_simulate($player, $boss);
    $tytFoughtName = $boss['name']; // giữ tên boss vừa đánh (thắng thì $boss chuyển sang tầng sau)
    $newHp = $result['win'] ? max(0, intval($result['player_hp'])) : 0;
    $stmt = $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?");
    $stmt->execute(array($newHp, $sid));
    \player\player_cache_clear($sid);

    if ($result['win']) {
        $baseExpReward = 140 + ($floor * 42) + intval(pow($floor, 2) * 2.2);
        $cp_exp_bonus = 0;
        if (!empty($player->cp_effects)) {
            foreach ($player->cp_effects as $cpEff) {
                if ($cpEff['type'] === 'exp_bonus') $cp_exp_bonus += $cpEff['value'];
            }
        }
        $cpExpGain = ($cp_exp_bonus > 0) ? max(1, intval($baseExpReward * $cp_exp_bonus / 100)) : 0;
        $expReward = $baseExpReward + $cpExpGain;
        $wgReward = 8 + intval($floor * 1.6);
        \player\changeexp($sid, $dblj, $expReward);
        $wgGain = tyt_add_wugong_exp($sid, $dblj, isset($player->wugong) ? $player->wugong : 0, $wgReward);

        $floorRewards = tyt_floor_rewards($floor);
        $rewardLines = array();
        $rewardLines[] = '+' . $expReward . ' tu vi';

        if ($floorRewards['lt'] > 0) {
            \player\changeyxb(1, $floorRewards['lt'], $sid, $dblj);
            $rewardLines[] = '+' . $floorRewards['lt'] . ' Linh thạch';
        }

        $itemLines = array();
        foreach ($floorRewards['items'] as $ri) {
            $iname = tyt_give_item($sid, $player, $dblj, $ri['id'], $ri['count']);
            $itemLines[] = '+' . $ri['count'] . ' ' . $iname;
        }
        if ($itemLines) {
            $rewardLines[] = implode(', ', $itemLines);
        }

        $cpLine = $cpExpGain > 0 ? ' <span style="color:#ff9800">[Công pháp +'.$cpExpGain.']</span>' : '';
        $wgLine = $wgGain['add'] > 0
            ? '+'.$wgGain['add'].' kinh nghiệm võ công ('.$wgGain['now'].'/'.$wgGain['max'].')'
            : 'Võ công chưa trang bị hoặc kinh nghiệm đã đầy';
        $rewardText = implode(' · ', $rewardLines);
        $notice = '<div class="tyt-notice win">Thông quan tầng '.$floor.'! Nhận '.$rewardText.$cpLine.', '.$wgLine.'.</div>';

        $nextFloor = min(151, $floor + 1);
        $best = max(intval($state['best_floor']), $floor);
        $stmt = $dblj->prepare("UPDATE player_toayeuthap SET floor = ?, best_floor = ?, wins = wins + 1, last_reset_week = ?, updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($nextFloor, $best, intval(date('W')), $sid));
        $state = tyt_state($sid, $dblj);
        $state = tyt_state($sid, $dblj);
        $floor = intval($state['floor']);
        $boss = ($floor <= 150) ? tyt_boss($floor) : false;
    } else {
        $notice = '<div class="tyt-notice fail">Thất bại ở tầng '.$floor.'. Boss còn '.number_format(max(0, intval($result['boss_hp'])), 0, ',', '.').' HP. Bạn đã trọng thương, hãy đến Vân Du Tiên Y [Trị Liệu] trước khi tái chiến.</div>';
    }

    $tytSkillNames = array('Kiếm Khí Ngút Trời', 'Chưởng Phong Sát', 'Thiên Lôi Chấn', 'Huyền Ảnh Bộ', 'Liệt Hỏa Quyền', 'Cửu Dương Chưởng', 'Hàn Băng Chưởng');
    // Có võ công: hiệp đánh dùng tên + văn thế võ thật (chỉ hiển thị, sát thương giữ nguyên công thức Tháp).
    $tytMoves = \player\combat_wugong_moves((isset($player->wugong) && intval($player->wugong) > 0) ? \player\wgcx(intval($player->wugong), $sid, $dblj) : false);
    $tytSeq = !empty($tytMoves) ? \player\combat_auto_sequence($tytMoves, intval($result['round'])) : array();
    foreach ($result['rounds'] as $line) {
        $skillName = $tytSkillNames[mt_rand(0, count($tytSkillNames) - 1)];
        $tytHitDesc = array(
            "khí lạnh hóa thành một đạo huyền quang, vun vút xé toạc hư không, thẳng tiến về phía đối thủ",
            "chân khí bùng nổ như cuồng phong, xoay tít như bánh xe, mang theo tiếng vù rít chói tai quét tới yếu huyệt kẻ địch",
            "ánh binh khí lóe lên như tia chớp, đâm thẳng vào tử huyệt, kết quả 'xoẹt' một tiếng rạch ra một vết thương đầm đìa máu"
        );
        $tytDesc = $tytHitDesc[mt_rand(0, count($tytHitDesc) - 1)];
        if (isset($tytSeq[$line['round']])) {
            $tytMv = $tytMoves[$tytSeq[$line['round']]];
            $skillName = $tytMv['label'] . ' · ' . $tytMv['name'];
            $tytDesc = rtrim(\player\combat_text($tytMv['text'], tyt_h($tytFoughtName)), '!.');
        }
        $battleHtml .= '<div class="tyt-round"><b>Hiệp '.$line['round'].'</b><span>⚡ Tung chiêu <font color="#4CAF50"><b>'.$skillName.'</b></font>! '.$tytDesc.', gây '.$line['damage'].' sát thương';
        if ($line['heal'] > 0) {
            $battleHtml .= ', hồi '.$line['heal'].' HP';
        }
        $battleHtml .= '</span><em>Boss: '.number_format($line['boss_hp'], 0, ',', '.').' HP / Bạn: '.number_format($line['player_hp'], 0, ',', '.').' HP</em></div>';
    }
}

$player = \player\getplayer($sid, $dblj);
tyt_lock_if_dead($player, $encode, $sid);

$floorText = ($floor > 150) ? 'Đã phá tháp' : 'Tầng '.$floor.' / 150';
$bestText = intval($state['best_floor']).' / 150';
$bossPanel = '';
if ($boss) {
    $bossName = tyt_h($boss['name']);
    $maxSkip = max(1, intval($state['best_floor'] * 0.8));
    $autoSkipAvailable = ($floor < $maxSkip && $maxSkip > 1);
    $fightControl = '<a class="tyt-btn fight" href="?cmd='.$fightCmd.'">Khiêu chiến tầng '.$floor.'</a>';
    if ($autoSkipAvailable) {
        $fightControl .= ' <a class="tyt-btn auto" href="?cmd='.$autoSkipCmd.'">Tự động vượt lên tầng '.$maxSkip.'</a>';
    }
    $bossPanel = <<<HTML
<section class="tyt-panel boss">
    <div class="tyt-boss-badge">Thủ tầng</div>
    <div class="tyt-kicker">Trận tiếp theo</div>
    <h2>$bossName</h2>
    <div class="tyt-boss-grid">
        <div><span>Cấp</span><b>{$boss['lv']}</b></div>
        <div><span>HP</span><b>{$boss['hp']}</b></div>
        <div><span>Công</span><b>{$boss['gj']}</b></div>
        <div><span>Thủ</span><b>{$boss['fy']}</b></div>
    </div>
    $fightControl
</section>
HTML;
} else {
    $bossPanel = <<<HTML
<section class="tyt-panel boss">
    <div class="tyt-kicker">Đỉnh tháp</div>
    <h2>Đã trấn áp hết yêu ma</h2>
    <p>Bạn đã vượt qua toàn bộ 150 tầng Tỏa Yêu Tháp.</p>
</section>
HTML;
}

$battleBlock = $battleHtml != '' ? '<section class="tyt-panel tyt-battle-panel"><div class="tyt-kicker">Diễn biến gần nhất</div>'.$battleHtml.'</section>' : '';
$wgName = 'Chưa trang bị';
if (isset($player->wugong) && intval($player->wugong) > 0) {
    $cxwg = \player\wgcx($player->wugong, $sid, $dblj);
    if ($cxwg) {
        $wgName = $cxwg->wgname.' '.$cxwg->wgxl.'/'.$cxwg->wgxlmax;
    }
}
$ypname1 = 'Trống';
$ypname2 = 'Trống';
$ypname3 = 'Trống';
if ($player->yp1 != 0) {
    $yaopin = \player\getplayeryaopin($player->yp1, $sid, $dblj);
    $ypname1 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp2 != 0) {
    $yaopin = \player\getplayeryaopin($player->yp2, $sid, $dblj);
    $ypname2 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
if ($player->yp3 != 0) {
    $yaopin = \player\getplayeryaopin($player->yp3, $sid, $dblj);
    $ypname3 = $yaopin->ypname.'('.$yaopin->ypsum.')';
}
$safeName = tyt_h($player->uname);
$safeWg = tyt_h($wgName);
$progressPercent = min(100, max(0, intval(round(min($floor, 150) * 100 / 150))));
$floorBadge = ($floor > 150) ? 'Đỉnh tháp' : 'Tầng '.$floor;
$towerBannerFile = __DIR__ . '/../images/bg/toayeuthap.png';
$towerBanner = './images/bg/toayeuthap.png';
if (is_file($towerBannerFile)) {
    $towerBanner .= '?v=' . filemtime($towerBannerFile);
}

$html = <<<HTML


<div class="tyt-page">
    <section class="tyt-hero">
        <div class="tyt-banner"><img src="$towerBanner" alt="Tỏa Yêu Tháp"></div>
        <div class="tyt-pk-ribbon" onclick="document.getElementById('tyt-reward-modal').classList.add('show')" style="cursor:pointer">THƯỞNG TẦNG</div>
        <div class="tyt-hero-content">
        <div class="tyt-floor-mark">$floorBadge</div>
        <div class="tyt-kicker">Phó bản leo tháp</div>
        <h1>Tỏa Yêu Tháp</h1>
        <p>150 tầng trấn áp yêu ma. Mỗi tầng nhận tu vi + Linh thạch, tầng chẵn thêm Cường Hóa Thạch, tầng ×10 thêm Đá Nguyên Tố. Reset vào thứ 2 hằng tuần.</p>
        <div class="tyt-progress" style="--progress:$progressPercent%"><span></span></div>
        <div class="tyt-stats">
            <div class="tyt-stat"><span>Người chơi</span><b>$safeName</b></div>
            <div class="tyt-stat"><span>Tiến độ</span><b>$floorText</b></div>
            <div class="tyt-stat"><span>Kỷ lục</span><b>$bestText</b></div>
        </div>
        $notice
        </div>
    </section>

    <div class="tyt-layout">
        $bossPanel
        <section class="tyt-panel">
            <div class="tyt-kicker">Trạng thái</div>
            <h2>Chuẩn bị chiến đấu</h2>
            <div class="tyt-boss-grid">
                <div><span>HP</span><b>{$player->uhp} / {$player->umaxhp}</b></div>
                <div><span>Công</span><b>{$player->ugj}</b></div>
                <div><span>Thủ</span><b>{$player->ufy}</b></div>
                <div><span>Võ công</span><b>$safeWg</b></div>
            </div>
            <div class="tyt-actions">
                <a class="tyt-btn home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
                <a class="tyt-btn wg" href="?cmd=$wgcmd">Võ Công</a>
            </div>
        </section>
        <section class="tyt-panel" style="grid-column: 1 / -1;">
            <div class="tyt-kicker">Nhanh</div>
            <h2>Dược phẩm</h2>
            <div class="tyt-actions">
                <a class="tyt-btn heal" href="?cmd=$useyp1">$ypname1</a>
                <a class="tyt-btn heal" href="?cmd=$useyp2">$ypname2</a>
                <a class="tyt-btn heal" href="?cmd=$useyp3">$ypname3</a>
            </div>
        </section>
    </div>
    $battleBlock
</div>

<div id="tyt-reward-modal" class="vdd-reward-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="vdd-reward-popup">
        <div class="vdd-reward-popup-head">
            <div class="vdd-reward-popup-icon">🏆</div>
            <h3>Thưởng Tầng</h3>
            <p>Reset thứ 2 hằng tuần, tự động vượt đến 80% kỷ lục</p>
        </div>
        <div class="vdd-reward-popup-list">
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;padding:10px 0">
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">LT cơ bản</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">50 + tầng×5</b></div>
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">Tầng chẵn</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">+50 LT + Cường Hóa Thạch</b></div>
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">Tầng ×10</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">+200 LT + Đá Nguyên Tố</b></div>
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">Tầng 50/100/150</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">+1000 LT + 5 Đá Nguyên Tố</b></div>
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">Reset tuần</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">Thứ 2 hằng tuần</b></div>
                <div style="border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.075);border-radius:12px;padding:11px"><span style="display:block;color:#d8c7a6;font-size:12px;font-weight:900">Tự động vượt</span><b style="display:block;margin-top:5px;color:#fff;font-size:16px;font-weight:1000">Đến 80% kỷ lục</b></div>
            </div>
        </div>
        <div class="vdd-reward-popup-foot">
            <a class="vdd-reward-popup-close" href="javascript:void(0)" onclick="document.getElementById('tyt-reward-modal').classList.remove('show')">Đóng</a>
        </div>
    </div>
</div>
HTML;

echo $html;
?>
