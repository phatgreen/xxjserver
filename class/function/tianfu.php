<?php
namespace player;

/**
 * tianfu.php — Thien Phu: Phu Cac suu tam (18 thien phu / 6 nhom / 7 cong van khi).
 *
 * Thien phu KHONG cong chi so chien dau (ugj/ufy/umaxhp/ubj/uxx). No chi tac dong
 * 7 cong van khi trong §5 cua spec; do la ranh gioi voi thechat / huyetmach / linhcan.
 * Config: class/config/tianfu_config.json. UI: game/tianfu*.php (prefix .tp-*).
 */

if (!function_exists(__NAMESPACE__ . '\\tianfu_config_path')) {
    function tianfu_config_path(){
        $paths = array(
            __DIR__ . '/../config/tianfu_config.json',
            __DIR__ . '/tianfu_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_config_all')) {
    function tianfu_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = tianfu_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $raw = file_get_contents($file);
        $decoded = json_decode($raw, true);
        $cache = is_array($decoded) ? $decoded : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_cfg_key')) {
    /** Doc mot khoa top-level, bao dam mang. */
    function tianfu_cfg_key($key){
        $cfg = tianfu_config_all();
        return isset($cfg[$key]) && is_array($cfg[$key]) ? $cfg[$key] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_groups')) {
    function tianfu_groups(){ return tianfu_cfg_key('groups'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_defs')) {
    function tianfu_defs(){ return tianfu_cfg_key('talents'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_def')) {
    function tianfu_def($id){
        $id = intval($id);
        $defs = tianfu_defs();
        return isset($defs[$id]) ? $defs[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_def')) {
    function tianfu_gate_def($gate){
        $gates = tianfu_cfg_key('gates');
        return isset($gates[$gate]) ? $gates[$gate] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_name')) {
    function tianfu_gate_name($gate){
        $d = tianfu_gate_def($gate);
        return $d ? $d['name'] : (string)$gate;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_unit')) {
    function tianfu_gate_unit($gate){
        $d = tianfu_gate_def($gate);
        return $d ? $d['unit'] : '%';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_def')) {
    function tianfu_quality_def($q){
        $q = intval($q);
        $qs = tianfu_cfg_key('qualities');
        return isset($qs[$q]) ? $qs[$q] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_name')) {
    function tianfu_quality_name($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['name'] : '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_color')) {
    function tianfu_quality_color($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['color'] : '#9e9e9e';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_icon')) {
    function tianfu_quality_icon($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['icon'] : '⚪';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_cap_percent')) {
    function tianfu_cap_percent(){
        $cfg = tianfu_config_all();
        return isset($cfg['cap_percent']) ? floatval($cfg['cap_percent']) : 30.0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lv_cap')) {
    /** Tran cap cua mot thien phu tai pham chat $q. */
    function tianfu_lv_cap($q){
        $d = tianfu_quality_def($q);
        return $d ? intval($d['lv_cap']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lv_cost')) {
    function tianfu_lv_cost($lv){
        $c = tianfu_cfg_key('lv_cost');
        $base = isset($c['linh_thach_base']) ? intval($c['linh_thach_base']) : 200;
        $per  = isset($c['linh_thach_per_lv']) ? intval($c['linh_thach_per_lv']) : 25;
        $tf   = isset($c['tf']) ? intval($c['tf']) : 1;
        return array('tf' => $tf, 'linh_thach' => $base + $per * intval($lv));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break_cost')) {
    /** Gia Dot Pham tu pham $q len $q+1; $q = 10 -> false (da tran). */
    function tianfu_break_cost($q){
        $q = intval($q);
        $b = tianfu_cfg_key('break_quality');
        if (!isset($b[$q])) return false;
        return array(
            'tf' => intval($b[$q]['tf']),
            'linh_thach' => intval($b[$q]['linh_thach']),
            'ulv_require' => intval($b[$q]['ulv_require'])
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_defs')) {
    function tianfu_slot_defs(){ return tianfu_cfg_key('slots'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_open')) {
    /** Cac so thu tu Phu Vi da mo theo cap do nhan vat. */
    function tianfu_slot_open($ulv){
        $ulv = intval($ulv);
        $out = array();
        foreach (tianfu_slot_defs() as $s) {
            if ($ulv >= intval($s['ulv'])) $out[] = intval($s['i']);
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_contributions')) {
    /** gate => per_lv. exp_monster la PHU CONG cua nhom Ngo Tinh (per_lv_sub), khong cong gop. */
    function tianfu_contributions($def){
        $out = array();
        if (!is_array($def) || !isset($def['gate'])) return $out;
        $out[$def['gate']] = isset($def['per_lv']) ? floatval($def['per_lv']) : 0.0;
        if (isset($def['per_lv_sub']) && floatval($def['per_lv_sub']) > 0) {
            $out['exp_monster'] = floatval($def['per_lv_sub']);
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_percent_calc')) {
    /**
     | Cong thuc DUY NHAT cho moi cong:
     |   % = min(cap, lv * per_lv * mult * (1 + milestone/100))
     | Clamp dat SAU phep nhan milestone (khac loi `stat_cap` cua Mach Huyet
     | — o do tram 15% khong bao gio binding, `docs/UI_HUYETMACH.md` §4.4).
     */
    function tianfu_percent_calc($lv, $perLv, $mult, $milestonePct){
        $raw = floatval($lv) * floatval($perLv) * floatval($mult) * (1 + floatval($milestonePct) / 100);
        $cap = tianfu_cap_percent();
        return round(min($cap, $raw), 2);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_fx_class')) {
    /** Bac hieu ung 0..5 theo pham chat chu phu (bac 0 van PHAI thay halo tinh). */
    function tianfu_fx_class($quality){
        $q = intval($quality);
        if ($q <= 2) return 0;
        if ($q <= 4) return 1;
        if ($q === 5) return 2;
        if ($q === 6) return 3;
        if ($q <= 8) return 4;
        return 5;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open_pill')) {
    function tianfu_open_pill(){
        $c = tianfu_cfg_key('open_pill');
        return array(
            'djid' => isset($c['djid']) ? intval($c['djid']) : 114,
            'name' => isset($c['name']) ? $c['name'] : 'Khai Phú Đan'
        );
    }
}
