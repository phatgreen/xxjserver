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

// ======================================================================
// Task 3 — doc/ghi DB + tong hop hieu ung
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_owned')) {
    /** 18 o luon du; chua so huu => have 0. DUNG MOT query cho toan mang. */
    function tianfu_owned($sid, $dblj){
        $rows = array();
        foreach (tianfu_defs() as $id => $d) {
            $rows[$id] = array(
                'id'      => intval($id),
                'have'    => 0,
                'quality' => 0,
                'lv'      => 0,
                'slot'    => 0,
                'name'    => $d['name'],
                'short'   => $d['short'],
                'group'   => intval($d['group']),
                'gate'    => $d['gate'],
                'icon'    => $d['icon'],
                'color'   => $d['color'],
            );
        }
        $st = $dblj->prepare("SELECT tianfu_id, quality, lv, slot FROM player_tianfu WHERE sid = ?");
        $st->execute(array($sid));
        foreach ($st->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $id = intval($r['tianfu_id']);
            if (!isset($rows[$id])) continue;          // id da bi xoa khoi config: bo qua
            $rows[$id]['have']    = 1;
            $rows[$id]['quality'] = intval($r['quality']);
            $rows[$id]['lv']      = intval($r['lv']);
            $rows[$id]['slot']    = intval($r['slot']);
        }
        return $rows;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_activated')) {
    /** Chi cac o dang bat; key = so thu tu Phu Vi (1..4). */
    function tianfu_activated($sid, $dblj){
        $out = array();
        foreach (tianfu_owned($sid, $dblj) as $row) {
            if ($row['slot'] > 0) $out[$row['slot']] = $row;
        }
        ksort($out);
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_total_lv')) {
    /** Tong Lv cua MOI thien phu da so huu (ke ca o nam trong Phu Cac). */
    function tianfu_total_lv($rows){
        $sum = 0;
        foreach ($rows as $r) {
            if (intval($r['have']) === 1) $sum += intval($r['lv']);
        }
        return $sum;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_power_of')) {
    /** Trong so chien luc cua mot thien phu dang bat (spec §2.2). */
    function tianfu_power_of($quality, $lv){
        return intval($quality) * 150 + intval($lv) * 8;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_milestone')) {
    /**
     | Moc tong Lv hien tai + moc ke tiep.
     | Milestones doc tu config, sap xep tang dan theo total_lv de
     | chu du an sau co them moc ma khong phai sua ham.
     */
    function tianfu_milestone($total_lv){
        $total_lv = intval($total_lv);
        $list = array();
        foreach (tianfu_cfg_key('milestones') as $m) {
            $list[] = array(
                'lv'  => intval($m['total_lv']),
                'pct' => floatval($m['bonus_percent']),
                'name'=> $m['name'],
            );
        }
        usort($list, function($a, $b){ return $a['lv'] - $b['lv']; });

        $cur  = array('lv' => 0, 'pct' => 0.0, 'name' => '');
        $next = array('lv' => 0, 'pct' => 0.0, 'name' => '');
        foreach ($list as $m) {
            if ($total_lv >= $m['lv']) {
                $cur = $m;
            } elseif ($next['lv'] === 0) {
                $next = $m;
            }
        }
        return array(
            'name'        => $cur['name'],
            'percent'     => $cur['pct'],
            'next_name'   => $next['name'],
            'next_percent'=> $next['pct'],
            'next_lv'     => intval($next['lv']),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_bonuses')) {
    /**
     | HAM DUY NHAT tra hieu ung Thien Phu cho moi noi dung (UI, player, 7 cong).
     | 15 key. CHI query player_tianfu MOT LAN (via tianfu_owned).
     |
     | Cong thuc van la `lv * per_lv * mult * (1 + milestone/100)` roi clamp
     | `cap_percent` — nhung vi mot cong co the nhieu hon mot thien phu giong
     | nhau (nhom Ngo Tinh co 3 o cung exp_cultivate), phan bo theo kieu:
     |   raw[g] = tong(lv_i * per_lv_i * mult_i)   -> tianfu_percent_calc(1, raw, 1, ms)
     | Ba tham so dau cua `tianfu_percent_calc` khi nhan (1, raw, 1) cho ra
     | dung `raw * (1 + ms/100)` va clamp MOT LAN — khong tao cong thuc thu hai.
     */
    function tianfu_bonuses($sid, $dblj){
        $gates = array('exp_cultivate', 'exp_monster', 'drop', 'alchemy',
                       'forge_cost', 'stamina', 'breakthrough');
        $out = array();
        foreach ($gates as $g) $out[$g] = 0.0;

        $rows      = tianfu_owned($sid, $dblj);
        $total_lv  = tianfu_total_lv($rows);
        $ms        = tianfu_milestone($total_lv);
        $raw       = array();
        foreach ($gates as $g) $raw[$g] = 0.0;

        $power = 0;
        $count = 0;
        foreach ($rows as $r) {
            if (intval($r['slot']) <= 0) continue;
            $def = tianfu_def($r['id']);
            if (!$def) continue;
            $qd  = tianfu_quality_def($r['quality']);
            $mult = $qd ? floatval($qd['mult']) : 1.0;
            foreach (tianfu_contributions($def) as $g => $perLv) {
                if (!isset($raw[$g])) continue;
                $raw[$g] += floatval($r['lv']) * floatval($perLv) * $mult;
            }
            $power += tianfu_power_of($r['quality'], $r['lv']);
            $count++;
        }

        foreach ($gates as $g) {
            $out[$g] = tianfu_percent_calc(1, $raw[$g], 1, $ms['percent']);
        }

        $out['total_lv']               = $total_lv;
        $out['milestone_name']         = $ms['name'];
        $out['milestone_percent']      = $ms['percent'];
        $out['next_milestone_name']    = $ms['next_name'];
        $out['next_milestone_percent'] = $ms['next_percent'];
        $out['next_milestone_lv']      = $ms['next_lv'];
        $out['activated_count']        = $count;
        $out['power']                  = $power;
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_log')) {
    /** $kind: open | lvup | break | slot | migrate. Khong lam bai hoc fail — log la phu. */
    function tianfu_log($sid, $kind, $tianfu_id, $from_q, $to_q, $detail, $dblj){
        $st = $dblj->prepare("INSERT INTO player_tianfu_log
            (sid, kind, tianfu_id, from_q, to_q, detail, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $st->execute(array($sid, $kind, intval($tianfu_id), intval($from_q),
                           intval($to_q), $detail === null ? null : mb_substr($detail, 0, 200)));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_recalc_mirror')) {
    /**
     | Viet lai 4 cot game1.tianfu_* tu player_tianfu.
     | Ba cot dau = ban sao Phu Vi 1 (chu phu); cot thu tu = tong power ca 4 o bat.
     | Goi sau moi lan open / lvup / break / slot va khi `getplayer()` phat hien lech.
     */
    function tianfu_recalc_mirror($sid, $dblj){
        $act   = tianfu_activated($sid, $dblj);
        $mid   = isset($act[1]) ? intval($act[1]['id'])      : 0;
        $mq    = isset($act[1]) ? intval($act[1]['quality']) : 0;
        $mlv   = isset($act[1]) ? intval($act[1]['lv'])      : 0;
        $power = 0;
        foreach ($act as $r) {
            $power += tianfu_power_of($r['quality'], $r['lv']);
        }
        $st = $dblj->prepare("UPDATE game1 SET tianfu_id = ?, tianfu_quality = ?,
                                     tianfu_lv = ?, tianfu_power = ? WHERE sid = ?");
        $st->execute(array($mid, $mq, $mlv, $power, $sid));
        return array('tianfu_id' => $mid, 'tianfu_quality' => $mq,
                     'tianfu_lv' => $mlv, 'tianfu_power' => $power);
    }
}
