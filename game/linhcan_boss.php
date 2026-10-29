<?php
// Boss Linh Can — hai che do trong mot trang:
//   khong co `el`  : boss THE GIOI (song 2h, chung toan server, nguon da hiem — Task 5).
//   `el=<hanh>`    : boss CA NHAN dot pha bac hanh do (Task 4, khong the nop chay —
//                    linhcan_break doi bang killed). Khuon: game/huyetmach_boss.php.
// Tham so qua parse_str (game.php:114): $attack_lc_boss (the gioi), $el + $pattack (ca nhan).
// FIX bug cu: `if ($cmd == 'attack_lc_boss')` (linhcan_boss.php:37 cu) la nhanh CHET.
// Hook quest (kill_boss/receive_stone) DA nam TRONG lcboss_drops / linhcan_boss_drops
// + linhcan_give_stone — trang TUYET khong goi linhcan_quest_update lap lai.
// .lcb-icon chi dung icon HANH: 2 bang boss khong cot `icon` nen `$row['icon']` chi ton
// tai tren request vua spawn (ham spawn tra mang in-memory) -> trang doi hinh lien tuc.

$ztcmd  = $encode->encode("cmd=zhuangtai&sid=$sid");
$lccmd  = $encode->encode("cmd=linhcan&sid=$sid");
$lcqcmd = $encode->encode("cmd=linhcan_quest&sid=$sid");
$lcbcmd = $encode->encode("cmd=linhcan_boss&sid=$sid");
$player = \player\getplayer($sid, $dblj);

$lcv    = isset($el) ? strtolower(trim($el)) : '';
$lcdefP = ($lcv !== '') ? \player\linhcan_element_def($lcv) : false;
$isPersonal = is_array($lcdefP);

$battleLog = '';
$bossHtml  = '';
$hintHtml  = '';

if ($isPersonal) {
    // ==================== BOSS CA NHAN THEO HANH ====================
    $boss = \player\lcboss_active($sid, $dblj, $lcv);
    if (!$boss) { $boss = \player\lcboss_spawn($sid, $dblj, $lcv); } // gate trong ham, khong spawn sai

    $pattackCmd = $encode->encode("cmd=linhcan_boss&sid=$sid&el=$lcv&pattack=1");
    $backCmd    = $encode->encode("cmd=linhcan&sid=$sid&el=$lcv");
    $canFight   = isset($pattack) && $boss && intval($boss['hp']) > 0;

    if ($canFight && intval($player->uhp) <= 0) {
        // nhu huyetmach_boss.php:17 — trong thuong thi khong duoc danh tiep.
        $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
    } elseif ($canFight) {
        $res = \player\lcboss_calc_damage($player, $boss);
        $dmg = intval($res['damage']);
        $critText = $res['crit'] ? " <font color='#FFD700'>[Bạo kích!]</font>" : '';
        $elemText = '';
        if ($res['elem_mult'] > 1.0) {
            $elemText = " <font color='#4CAF50'>[Khắc chế! +" . intval(($res['elem_mult'] - 1) * 100) . '%]</font>';
        } elseif ($res['elem_mult'] < 1.0) {
            $elemText = " <font color='#f44336'>[Bị khắc! -" . intval((1 - $res['elem_mult']) * 100) . '%]</font>';
        }
        $battleLog = "Ngươi gây <font color='#FF5722'>{$dmg}</font> sát thương lên "
            . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

        $newHp = max(0, intval($boss['hp']) - $dmg);
        $dblj->prepare("UPDATE linhcan_boss_personal SET hp = ? WHERE id = ?")
             ->execute(array($newHp, intval($boss['id'])));

        if ($newHp <= 0) {
            $boss['hp'] = 0;
            \player\lcboss_mark_killed($sid, $dblj, $boss);   // tu set status='killed', hp=0
            $drop = \player\lcboss_drops($sid, $dblj, $boss);  // da + hook kill_boss/receive_stone ben trong
            $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8')
                . " đã bị tiêu diệt!</font><br/>" . $drop['html'];
            $battleLog .= "<font color='#FFD700'>→ Mở khóa Đột Phá bậc " . intval($boss['to_stage'])
                . ' (' . $lcdefP['name'] . ')</font><br/>';
            $hintHtml = '<div class="lcb-hint">Boss đã bị hạ — về Vòng Ngũ Hành bấm <b>✦ ĐỘT PHÁ</b>.</div>';
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $boss = false;
        } else {
            $bDmg = intval(\player\lcboss_boss_counter($player, $boss));
            $newPlayerHp = max(0, intval($player->uhp) - $bDmg);
            $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($newPlayerHp, $sid));
            $battleLog .= htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8')
                . " phản đòn <font color='#f44336'>{$bDmg}</font> sát thương<br/>";
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $boss['hp'] = $newHp;
        }
    }

    if ($boss) {
        $hpPct = intval($boss['maxhp']) > 0
            ? min(100, intval(intval($boss['hp']) * 100 / intval($boss['maxhp']))) : 0;
        $bossHtml = '<div class="lcb-card" style="--lcb-color:' . $lcdefP['color'] . '">'
            . '<div class="lcb-icon">' . $lcdefP['icon'] . '</div>'
            . '<div class="lcb-name">' . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="lcb-level">Lv ' . intval($boss['level']) . ' · ' . $lcdefP['name']
            . ' căn · chặn đột phá bậc ' . intval($boss['to_stage']) . '</div>'
            . '<div class="lcb-hpbar"><div class="lcb-hpfill" style="width:' . $hpPct . '%"></div></div>'
            . '<div class="lcb-hptext">HP ' . intval($boss['hp']) . ' / ' . intval($boss['maxhp']) . '</div>'
            . '<div class="lcb-stats"><span>⚔ ' . intval($boss['atk']) . '</span><span>🛡 '
            . intval($boss['def']) . '</span><span>💥 ' . intval($boss['crit']) . '%</span></div>'
            . '<a class="lcb-btn" href="?cmd=' . $pattackCmd . '">TẤN CÔNG</a>'
            . '</div>';
    } else {
        // Tai khong co boss — noi ro dieu kien (dung cach gate cua Task 4).
        $ndP = \player\linhcan_node_get($sid, $lcv, $dblj);
        $stP = intval($ndP['stage']); $lvP = intval($ndP['level']);
        $capP = \player\linhcan_stage_cap($stP);
        if ($stP >= \player\linhcan_stage_max()) {
            $hintHtml = '<div class="lcb-hint">Hành <b>' . $lcdefP['name']
                . '</b> đã đạt bậc cao nhất — không còn boss đột phá.</div>';
        } elseif ($lvP < $capP) {
            $hintHtml = '<div class="lcb-hint">Cần <b>Lv ' . $capP . '</b> (đang Lv ' . $lvP
                . ') để khiêu chiến boss bậc ' . ($stP + 1)
                . '. <a href="?cmd=' . $backCmd . '">Luyện tiếp →</a></div>';
        } else {
            $hintHtml = '<div class="lcb-hint">Boss đã bị hạ từ trước — giờ có thể <a href="?cmd='
                . $backCmd . '">✦ ĐỘT PHÁ</a>.</div>';
        }
        $bossHtml = '<div class="lcb-empty">Không có boss đang hoạt động</div>';
    }
} else {
    // ==================== BOSS THE GIOI (2h, chung toan server) ====================
    $bossData = \player\linhcan_boss_check_spawn($dblj);
    if (!$bossData) { $bossData = \player\linhcan_boss_spawn($dblj); }
    $wattackCmd = $encode->encode("cmd=linhcan_boss&attack_lc_boss=1&sid=$sid");

    if (is_array($bossData) && intval($bossData['hp']) > 0 && isset($attack_lc_boss)) {
        if (intval($player->uhp) <= 0) {
            $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
        } else {
            $result = \player\linhcan_boss_calc_damage($player, $bossData, $dblj);
            $playerDamage = intval($result['damage']);
            $critText = $result['crit'] ? ' <font color="#FFD700">[Bạo kích!]</font>' : '';
            $elemText = '';
            if ($result['elem_mult'] > 1.0) {
                $elemText = ' <font color="#4CAF50">[Khắc chế! +' . intval(($result['elem_mult'] - 1) * 100) . '%]</font>';
            } elseif ($result['elem_mult'] < 1.0) {
                $elemText = ' <font color="#f44336">[Bị khắc! -' . intval((1 - $result['elem_mult']) * 100) . '%]</font>';
            }
            $battleLog = "Ngươi gây <font color='#FF5722'>{$playerDamage}</font> sát thương lên "
                . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

            $newHpW = max(0, intval($bossData['hp']) - $playerDamage);
            $dblj->prepare("UPDATE linhcan_boss SET hp = ? WHERE id = ?")
                 ->execute(array($newHpW, intval($bossData['id'])));

            if ($newHpW <= 0) {
                \player\linhcan_boss_mark_killed(intval($bossData['id']), $sid, $dblj);
                $drops = \player\linhcan_boss_drops($player, $bossData, $dblj); // drop THAT vao playerdaoju + hook quest (Task 5)
                $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8')
                    . " đã bị tiêu diệt!</font><br/>" . $drops['html'];
                // mau thong bao he thong: game/huyetmach_boss.php:36-39
                $stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
                $stmt->execute(array('【Hệ Thống】',
                    htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8') . ' đã tiêu diệt Boss Linh Căn ['
                    . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . ']', 0));
                \player\player_cache_clear($sid);
                $player = \player\getplayer($sid, $dblj);
                $bossData['hp'] = 0;
            } else {
                $bossDamage = max(1, round(intval($bossData['atk']) - intval($player->ufy) * 0.75)); // dung nhu page cu
                $playerNewHp = max(0, intval($player->uhp) - $bossDamage);
                $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($playerNewHp, $sid));
                $battleLog .= htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8')
                    . " gây <font color='#f44336'>{$bossDamage}</font> sát thương lên ngươi<br/>";
                \player\player_cache_clear($sid);
                $player = \player\getplayer($sid, $dblj);
                $bossData['hp'] = $newHpW;
            }
        }
    }

    if (is_array($bossData)) {
        $bwName  = htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8');
        $bwElem  = isset($bossData['element']) ? $bossData['element'] : '';
        $bwIcon  = \player\linhcan_element_icon($bwElem);
        $bwColor = \player\linhcan_element_color($bwElem);
        $bwNameE = \player\linhcan_element_name($bwElem);
        $bwHp    = intval($bossData['hp']); $bwMax = intval($bossData['maxhp']);
        $bwPct   = $bwMax > 0 ? min(100, intval($bwHp * 100 / $bwMax)) : 0;
        $bossHtml = '<div class="lcb-card" style="--lcb-color:' . $bwColor . '">'
            . '<div class="lcb-icon">' . $bwIcon . '</div>'
            . '<div class="lcb-name">' . $bwName . '</div>'
            . '<div class="lcb-level">Lv ' . intval($bossData['level']) . ' · Hệ ' . $bwNameE . '</div>'
            . '<div class="lcb-hpbar"><div class="lcb-hpfill" style="width:' . $bwPct . '%"></div></div>'
            . '<div class="lcb-hptext">HP ' . $bwHp . ' / ' . $bwMax . '</div>'
            . '<div class="lcb-stats"><span>⚔ ' . intval($bossData['atk']) . '</span><span>🛡 '
            . intval($bossData['def']) . '</span><span>💥 ' . intval($bossData['crit']) . '%</span></div>';
        if ($bwHp > 0) {
            $bossHtml .= '<a class="lcb-btn" href="?cmd=' . $wattackCmd . '">TẤN CÔNG</a>';
        } else {
            $bwLeft = isset($bossData['respawn_in']) ? intval($bossData['respawn_in']) : 0;
            $bossHtml .= '<div class="lcb-dead">BOSS ĐÃ BỊ TIÊU DIỆT — '
                . ($bwLeft > 0 ? 'xuất hiện lại sau ' . max(1, intval(ceil($bwLeft / 60))) . ' phút' : 'hồi sau xuất hiện lại') . '</div>';
        }
        $bossHtml .= '</div>';
    } else {
        $bossHtml = '<div class="lcb-empty">Chưa có boss — quay lại sau</div>';
    }
}

// Chips chon boss ca nhan (hien o CA HAI che do; link dan vao chinh trang nay voi el=)
$lcvh = array('kim', 'moc', 'thuy', 'hoa', 'tho');
$lcdefsB = \player\linhcan_element_defs();
$lcchipsB = '';
foreach ($lcvh as $ckB) {
    if (!isset($lcdefsB[$ckB])) continue;
    $defB = $lcdefsB[$ckB];
    $clsB = ($lcv === $ckB) ? ' is-active' : '';
    $lcchipsB .= '<a class="lcb-chip' . $clsB . '" style="--lcb-color:' . $defB['color'] . '"'
        . ' href="?cmd=' . $encode->encode("cmd=linhcan_boss&sid=$sid&el=$ckB") . '">'
        . $defB['icon'] . ' ' . $defB['name'] . '</a>';
}
?>
<div class="lcb-wrap">
    <nav class="lc-tabbar">
        <a href="?cmd=<?= $lccmd ?>"><i>☯</i><b>Luyện Căn</b></a>
        <a href="?cmd=<?= $lcqcmd ?>"><i>📜</i><b>Nhiệm Vụ</b></a>
        <a class="on" href="?cmd=<?= $lcbcmd ?>"><i>👹</i><b>Boss Ngũ Hành</b></a>
    </nav>
    <div class="lcb-head"><span class="lcb-title"><?= $isPersonal ? 'BOSS ĐỘT PHÁ · ' . $lcdefP['name'] : 'BOSS LINH CĂN THẾ GIỚI' ?></span></div>
    <div class="lcb-chips">
        <a class="lcb-chip<?= $isPersonal ? '' : ' is-active' ?>" href="?cmd=<?= $lcbcmd ?>">🌍 Thế Giới</a>
        <?= $lcchipsB ?>
    </div>
    <?= $bossHtml ?>
    <?php if ($battleLog): ?>
    <div class="lcb-log"><?= $battleLog ?></div>
    <?php endif; ?>
    <?= $hintHtml ?>
    <div class="lcb-footnote">Boss cá nhân mỗi hành xuất hiện khi hành đó đạt Lv trần bậc; hạ boss mới ĐỘT PHÁ được bậc kế. Boss thế giới (2h) rơi đá ngũ hành + Bình Anh thật vào túi đồ.</div>
</div>
