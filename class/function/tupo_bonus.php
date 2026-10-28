<?php
namespace player;

/**
 * tupo_bonus.php — khoản cộng tỉ lệ đột phá: Đột Phá Đan + Sự kiện theo ngày (class/config/tupo_bonus_config.json).
 * Dùng ở game/tupo.php.
 */

if (!function_exists(__NAMESPACE__ . '\\tupo_bonus_config')) {
    function tupo_bonus_config(){
        static $cache = null;
        if ($cache === null) {
            $file = __DIR__ . '/../config/tupo_bonus_config.json';
            $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
            $cache = is_array($data) ? $data : array();
        }
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tupo_pill_for_level')) {
    // Đột Phá Đan dùng được khi đột phá ở cấp $lv (đúng cảnh giới floor(cấp/50)); không có -> false.
    function tupo_pill_for_level($lv){
        $cfg = tupo_bonus_config();
        if (empty($cfg['pill']['items'])) return false;
        $realm = (int)floor(max(0, intval($lv)) / 50);
        foreach ($cfg['pill']['items'] as $djid => $r) {
            if (intval($r) === $realm) {
                $dj = daoju_config_get(intval($djid));
                return array(
                    'djid' => intval($djid),
                    'name' => ($dj && isset($dj['djname'])) ? $dj['djname'] : ('Đột Phá Đan #' . $djid),
                    'per_pill' => floatval($cfg['pill']['per_pill']),
                    'max_pills' => intval($cfg['pill']['max_pills']),
                );
            }
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tupo_event_bonus')) {
    // Sự kiện theo thứ trong tuần: array(phần trăm, tên ngày) — không có sự kiện -> array(0, '').
    function tupo_event_bonus($ts = null){
        $cfg = tupo_bonus_config();
        if (empty($cfg['event']['enabled'])) return array(0, '');
        $w = date('w', $ts === null ? time() : $ts);
        $pct = isset($cfg['event']['weekday'][$w]) ? floatval($cfg['event']['weekday'][$w]) : 0;
        $name = isset($cfg['event']['names'][$w]) ? $cfg['event']['names'][$w] : '';
        return array($pct, $pct > 0 ? $name : '');
    }
}
