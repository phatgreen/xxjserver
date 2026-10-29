<?php
namespace player;

/**
 * huyetmach_boss.php - Boss mach huyet theo tung nguoi choi.
 * Khac Boss Linh Can (boss toan server): o day moi nguoi choi co boss cua minh,
 * de checklist thuc tinh o Task 5 chi qua khi danh dung con cua chinh no.
 */

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_config_path')) {
    function huyetmach_boss_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_boss_config.json',
            __DIR__ . '/huyetmach_boss_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_config_all')) {
    function huyetmach_boss_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(huyetmach_boss_config_path());
        $cache = json_decode($json, true);
        if (!is_array($cache)) $cache = array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_row')) {
    function huyetmach_boss_row($key, $stage){
        $cfg = huyetmach_boss_config_all();
        $id = $key . '_' . intval($stage);
        return isset($cfg['bosses'][$id]) ? $cfg['bosses'][$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_enrich')) {
    /** Nap them icon/color/stage/cfg_key vao hang DB de trang sau khong doc config trung. */
    function huyetmach_boss_enrich($row){
        if (!is_array($row)) return false;
        $cfg = huyetmach_boss_config_all();
        $k = isset($row['cfg_key']) ? $row['cfg_key'] : '';
        if (isset($cfg['bosses'][$k])) {
            $row['icon'] = $cfg['bosses'][$k]['icon'];
            $row['color'] = $cfg['bosses'][$k]['color'];
            $row['stage'] = intval($cfg['bosses'][$k]['stage']);
        } else {
            $row['icon'] = '👹';
            $row['color'] = '#9C27B0';
            $row['stage'] = 0;
        }
        return $row;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_fights_today')) {
    function huyetmach_boss_fights_today($sid, $dblj){
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM huyetmach_boss WHERE sid = ? AND spawned_at >= CURDATE()");
        $stmt->execute(array($sid));
        return intval($stmt->fetchColumn());
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_active')) {
    function huyetmach_boss_active($sid, $dblj){
        $cfg = huyetmach_boss_config_all();
        $expire = intval(isset($cfg['boss_config']['expire_minutes']) ? $cfg['boss_config']['expire_minutes'] : 2880);
        $stmt = $dblj->prepare("SELECT * FROM huyetmach_boss WHERE sid = ? AND status = 'active' ORDER BY spawned_at DESC LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return false;
        $elapsed = (time() - strtotime($row['spawned_at'])) / 60;
        if ($elapsed > $expire || intval($row['hp']) <= 0) {
            $dblj->prepare("UPDATE huyetmach_boss SET status = 'inactive' WHERE id = ?")->execute(array($row['id']));
            return false;
        }
        return huyetmach_boss_enrich($row);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_spawn')) {
    function huyetmach_boss_spawn($sid, $dblj){
        $active = huyetmach_boss_active($sid, $dblj);
        if ($active) return $active;

        $cfg = huyetmach_boss_config_all();
        $bc = isset($cfg['boss_config']) ? $cfg['boss_config'] : array();
        $max = intval(isset($bc['max_fights_per_day']) ? $bc['max_fights_per_day'] : 5);
        if (huyetmach_boss_fights_today($sid, $dblj) >= $max) return false;

        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->huyetmach_id) <= 0) return false;

        $stage = intval($player->huyetmach_awaken_stage) + 1;
        if (huyetmach_vessel_tier($stage) === false) return false;
        $key = huyetmach_get_key_by_id(intval($player->huyetmach_id));
        $def = huyetmach_boss_row($key, $stage);
        if (!$def) return false;

        $s = isset($bc['stats']) ? $bc['stats'] : array();
        $lv = max(1, intval($def['level']));
        $mult = 1.0;
        if (isset($bc['stats']['stage_mult'][strval($stage)])) {
            $mult = floatval($bc['stats']['stage_mult'][strval($stage)]);
        }
        $hp = intval(round(intval($s['hp_per_level']) * $lv * $mult));
        $atk = intval(round(intval($s['atk_per_level']) * $lv * $mult));
        $defv = intval(round(intval($s['def_per_level']) * $lv * $mult));
        $critv = intval($s['crit']);
        $cfgKey = $key . '_' . $stage;

        $ins = $dblj->prepare("INSERT INTO huyetmach_boss (sid, cfg_key, name, element, level, hp, maxhp, atk, def, crit, spawned_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");
        $ins->execute(array($sid, $cfgKey, $def['name'], $def['element'], $lv, $hp, $hp, $atk, $defv, $critv));

        return array(
            'id' => intval($dblj->lastInsertId()), 'sid' => $sid, 'cfg_key' => $cfgKey,
            'name' => $def['name'], 'stage' => $stage, 'element' => $def['element'],
            'level' => $lv, 'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => $defv,
            'crit' => $critv, 'icon' => $def['icon'], 'color' => $def['color']
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_calc_damage')) {
    function huyetmach_boss_calc_damage($player, $bossRow){
        $hmCs  = huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
        $hmDef = huyetmach_combat_pierce_def($hmCs['pen'], intval($bossRow['def']));
        $baseDmg = max(1, intval($player->ugj) - intval($hmDef * 0.75));
        $lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $elemMult = 1.0;
        if (function_exists(__NAMESPACE__ . '\\linhcan_get_element_multiplier')) {
            $elemMult = linhcan_get_element_multiplier($lcElement, $bossElement);
        }
        $dmg = max(1, round($baseDmg * $elemMult));
        $isCrit = false;
        if (roll_pct($player->ubj, 100)) {
            $dmg = round($dmg * 1.72);
            $isCrit = true;
        }
        return array('damage' => intval($dmg), 'crit' => $isCrit, 'elem_mult' => floatval($elemMult));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_dodge')) {
    /** Wrapper giu chu ky Task 8; clamp + roll nam o huyetmach_combat_*. */
    function huyetmach_boss_vessel_dodge($bonuses){
        $cs = huyetmach_combat_stats($bonuses);
        return huyetmach_combat_roll_dodge($cs['dodge']);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_reduce')) {
    /** Wrapper Task 8. Khac biet co y nghia: don 0 giu 0 (ham cu ep max(1)). */
    function huyetmach_boss_vessel_reduce($bonuses, $dmg){
        $cs = huyetmach_combat_stats($bonuses);
        return huyetmach_combat_phys_reduce($cs['reduce'], $dmg);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_finish')) {
    /** Thuong + danh dau da giot. boss_id cua player_huyetmach_boss la TEN boss (khoa that cua Task 5). */
    function huyetmach_boss_finish($sid, $dblj, $bossRow){
        $cfg = huyetmach_boss_config_all();
        $stage = intval($bossRow['stage']);
        $d = (isset($cfg['boss_config']['drops']['by_stage'][strval($stage)]))
            ? $cfg['boss_config']['drops']['by_stage'][strval($stage)]
            : array('huyet_thach' => 4, 'linh_thach' => 20000);
        $ht = intval($d['huyet_thach']);
        $lt = intval($d['linh_thach']);
        $htId = intval(isset($cfg['boss_config']['drops']['huyet_thach_id']) ? $cfg['boss_config']['drops']['huyet_thach_id'] : 112);

        $firstKill = !huyetmach_boss_killed($sid, $bossRow['name'], $dblj);
        if ($firstKill && isset($cfg['boss_config']['first_kill'][strval($stage)])) {
            $ht += intval($cfg['boss_config']['first_kill'][strval($stage)]);
        }
        // Van may mach Duong Kieu: nhan so Huyet Thach roi (12% -> x1.12)
        $hmCs = huyetmach_combat_stats(huyetmach_vessel_bonuses($sid, $dblj));
        if ($hmCs['luck'] > 0) {
            $ht = max(1, intval(round($ht * (100 + $hmCs['luck']) / 100)));
        }


        $dblj->beginTransaction();
        try {
            $upd = $dblj->prepare("UPDATE huyetmach_boss SET status='killed', killed_at = NOW() WHERE id = ? AND status <> 'killed'");
            $upd->execute(array($bossRow['id']));
            if ($upd->rowCount() !== 1) throw new Exception('Boss không còn ở trạng thái khiêu chiến.');

            if ($lt > 0) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($lt, $sid));
            }
            if ($firstKill) {
                huyetmach_boss_mark($sid, $bossRow['name'], $dblj);
            }
            $dblj->commit();
        } catch (Exception $e) {
            if ($dblj->inTransaction()) $dblj->rollBack();
            return array('html' => '<font color="#f87171">Lỗi phát thưởng: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</font>',
                'huyet_thach' => 0, 'linh_thach' => 0);
        }
        // Huyet Thach PHAI di qua \player\adddj() (class/player.php:1285), khong nam SQL tran:
        // adddj tim theo sid roi moi INSERT (co uid = player->uid), va no mien nhiem vu 'collect'
        // qua nhiemvu_update_all + changerwyq1. SQL tran trong `linhcan_boss.php` vi thieu buoc
        // nay nen da phat "3 Da" ma khong nao vao playerdaoju. adddj also no-op neu getdaoju()
        // khong thay djid -> 112/113 bat buoc phai co trong daoju_config (Task 4).
        adddj($sid, $htId, $ht, $dblj);
        player_cache_clear($sid);

        $html = "<font color='#FFD700'>🩸 {$ht} Huyết Thạch</font><br/>";
        if ($lt > 0) $html .= "<font color='#4CAF50'>💰 " . number_format($lt) . " Linh Thạch</font><br/>";
        if ($firstKill) $html .= "<font color='#6ee7b7'>✦ Hạ boss mạch huyết lần đầu — checklist Thức Tỉnh đã ghi nhận.</font><br/>";
        return array('html' => $html, 'huyet_thach' => $ht, 'linh_thach' => $lt);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_status')) {
    function huyetmach_boss_status($sid, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->huyetmach_id) <= 0) {
            return array('state' => 'done', 'text' => 'Chưa có Huyết Mạch — chưa mở boss mạch huyết.', 'boss' => false);
        }
        $stage = intval($player->huyetmach_awaken_stage);
        $next = huyetmach_vessel_tier($stage + 1);
        if (!$next || intval($next['boss_require']) !== 1) {
            return array('state' => 'done', 'text' => 'Bậc kế tiếp không cần boss mạch huyết.', 'boss' => false);
        }
        $boss = huyetmach_boss_active($sid, $dblj);
        if ($boss) {
            return array('state' => 'killable', 'text' => 'Đang khiêu chiến: ' . $boss['name'] . ' (cấp ' . intval($boss['level']) . ').', 'boss' => $boss);
        }
        $cfg = huyetmach_boss_config_all();
        $max = intval(isset($cfg['boss_config']['max_fights_per_day']) ? $cfg['boss_config']['max_fights_per_day'] : 5);
        $today = huyetmach_boss_fights_today($sid, $dblj);
        if ($today >= $max) {
            return array('state' => 'blocked', 'text' => 'Hết lượt khiêu chiến hôm nay (' . $max . ' lượt/ngày).', 'boss' => false);
        }
        return array('state' => 'farmer', 'text' => 'Còn ' . ($max - $today) . ' lượt khiêu chiến hôm nay.', 'boss' => false);
    }
}
?>
