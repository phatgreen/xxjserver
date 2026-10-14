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

// ======================================================================
// Task 4 — Khai Phu Dan
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_weighted_pick')) {
    /**
     | Chon 1 key theo ti le. $weights: key => so duong (co the le).
     | Truong hop le: tong = 0 -> false (khong bao gio xay ra vi Task 1
     | test bat weight 18 o = 72 va 10 pham = 100.00).
     */
    function tianfu_weighted_pick($weights){
        if (!is_array($weights) || count($weights) === 0) return false;
        $scaled = array(); $sum = 0;
        foreach ($weights as $k => $w) {
            $w = floatval($w);
            if ($w <= 0) continue;
            $scaled[$k] = $w;
            $sum += $w;
        }
        if ($sum <= 0) return false;
        // Lam tron ve so nguyen de mt_rand khong bi mat do chinh xac: bac thap nhat
        // cua 10 pham chat la 0.09 -> nhan 100 = 9 lan trong 10009.
        $factor = 100;
        $pick = mt_rand(1, intval(round($sum * $factor)));
        $acc  = 0;
        foreach ($scaled as $k => $w) {
            $acc += intval(round($w * $factor));
            if ($pick <= $acc) return $k;
        }
        $keys = array_keys($scaled);
        return $keys[count($keys) - 1];
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open')) {
    /**
     | Uong 1 Khai Phu Dan (114) -> mo 1 thien phu.
     |   - Chon thien phu theo weight cua 18 o (config.talents[*].weight).
     |   - Chon pham chat theo weight cua 10 bac (config.qualities[*].weight).
     |   - O CHUA so huu  -> INSERT voi pham chat vua roll (lan dau co the ra
     |     Phàm Phẩm; do la y nghia cua "khai mo", khong phai trung phat).
     |   - O DA so huu    -> +1 pham chat, tran 10 (spec §5: trung khong mat).
     | Tru dan bang UPDATE co dieu kien djsum >= 1 + rowCount() -> chong double-click
     | (route `?cmd=` da 1 lan, nhung cron/f2 ngam van co the gui POST trung).
     */
    function tianfu_open($sid, $dblj){
        $pill   = tianfu_open_pill();
        $djid   = $pill['djid'];
        $bad    = array('ok' => false, 'talent' => false, 'quality' => 0,
                        'is_new' => false, 'msg' => '');

        // 1) Tru dan truoc khi roll — neu khong co dan thi khong roll, khong ghi gi.
        $ded = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - 1
                               WHERE sid = ? AND djid = ? AND djsum >= 1");
        $ded->execute(array($sid, $djid));
        if ($ded->rowCount() !== 1) {
            $bad['msg'] = 'Không còn ' . $pill['name'] . ' trong túi.';
            return $bad;
        }

        // 2) Roll
        $talentWeights = array();
        foreach (tianfu_defs() as $id => $d) $talentWeights[$id] = floatval($d['weight']);
        $picked = tianfu_weighted_pick($talentWeights);
        if ($picked === false) {
            // Ly ra khong xay ra; hoan lai dan de nguoi choi khong mat oan.
            $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + 1 WHERE sid = ? AND djid = ?")
                 ->execute(array($sid, $djid));
            $bad['msg'] = 'Khai mở thất bại, đan đã được hoàn lại.';
            return $bad;
        }
        $id  = intval($picked);
        $def = tianfu_def($id);

        $qWeights = array();
        foreach (tianfu_cfg_key('qualities') as $q => $qd) $qWeights[$q] = floatval($qd['weight']);
        $rolledQ = intval(tianfu_weighted_pick($qWeights));

        // 3) Ghi — MOT lenh duoc, khong can transaction, khong the tao dong trung
        //    (PK (sid, tianfu_id)); `FOR UPDATE` da bi loai vi vo nghia voi dong chua ton tai.
        $pre = $dblj->prepare("SELECT quality FROM player_tianfu WHERE sid = ? AND tianfu_id = ?");
        $pre->execute(array($sid, $id));
        $curQ = $pre->fetch(\PDO::FETCH_ASSOC);
        $isNew = ($curQ === false);

        $qIns = ($rolledQ >= 1 && $rolledQ <= 10) ? $rolledQ : 1;
        $dblj->prepare("INSERT INTO player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at)
                        VALUES (?, ?, ?, 0, 0, NOW())
                        ON DUPLICATE KEY UPDATE quality = LEAST(10, quality + 1)")
             ->execute(array($sid, $id, $qIns));

        // Pham chat THUC TE sau khi ghi (o da co san 10 thi van la 10, khong phai 11).
        $after = $dblj->prepare("SELECT quality FROM player_tianfu WHERE sid = ? AND tianfu_id = ?");
        $after->execute(array($sid, $id));
        $quality = intval($after->fetchColumn());
        $fromQ   = $isNew ? 0 : intval($curQ['quality']);

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'open', $id, $fromQ, $quality,
                   $isNew ? 'khai mo' : 'luyen pham', $dblj);

        $qdef = tianfu_quality_def($quality);
        return array(
            'ok'      => true,
            'talent'  => $def,
            'quality' => $quality,
            'is_new'  => $isNew,
            'msg'     => ($isNew ? 'Khai mở được ' : 'Luyện thêm phẩm chất cho ')
                        . $def['name'] . ' — ' . $qdef['name'],
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open_count')) {
    function tianfu_open_count($sid, $dblj){
        $st = $dblj->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'open'");
        $st->execute(array($sid));
        return intval($st->fetchColumn());
    }
}

// ======================================================================
// Task 5 — Luyen Lv va Dot Pham (ca hai 100% thanh cong, spec §5.2/§5.3)
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_player_wallet')) {
    /** Doc truc tiep game1: khong phu thuoc `$player` (CLI test + nut UI dung duoc). */
    function tianfu_player_wallet($sid, $dblj){
        $st = $dblj->prepare("SELECT tf, uyxb, ulv FROM game1 WHERE sid = ? LIMIT 1");
        $st->execute(array($sid));
        $r = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$r) return array('tf' => 0, 'uyxb' => 0, 'ulv' => 0);
        return array('tf' => intval($r['tf']), 'uyxb' => intval($r['uyxb']),
                     'ulv' => intval($r['ulv']));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_row_state')) {
    /** Dong UI: `ok` quyet nut co bam duoc khong; `text` co dau vi hien cho nguoi choi. */
    function tianfu_row_state($text, $ok){
        return array('text' => $text, 'ok' => (bool)$ok);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lvup')) {
    function tianfu_lvup($sid, $dblj, $id){
        $empty = array('ok' => false, 'lv' => 0, 'cap' => 0, 'cost' => array(), 'msg' => '');
        $def = tianfu_def($id);
        if (!$def) { $empty['msg'] = 'Thiên phú không tồn tại.'; return $empty; }

        $O = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (intval($O[$id]['have']) !== 1) {
            $empty['msg'] = 'Chưa khai mở thiên phú này — cần Khai Phú Đan.';
            return $empty;
        }
        $lv  = intval($O[$id]['lv']);
        $cap = tianfu_lv_cap(intval($O[$id]['quality']));
        if ($lv >= $cap) {
            $empty['cap'] = $cap; $empty['lv'] = $lv;
            $empty['msg'] = $def['name'] . ' đã đạt trần ' . $cap . ' cấp — phải Dot Pham để luyện tiếp.';
            return $empty;
        }

        $cost = tianfu_lv_cost($lv);
        $W = tianfu_player_wallet($sid, $dblj);
        if ($W['tf'] < intval($cost['tf'])) {
            $empty['msg'] = 'Điểm Thiên Phú không đủ: cần ' . $cost['tf'] . ', có ' . $W['tf'] . '.';
            return $empty;
        }
        if ($W['uyxb'] < intval($cost['linh_thach'])) {
            $empty['msg'] = 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach'], 0, ',', '.')
                          . ', có ' . number_format($W['uyxb'], 0, ',', '.') . '.';
            return $empty;
        }

        $dblj->beginTransaction();
        try {
            // Mot lenh UPDATE dua tren chinh dieu kien du tr -> chong hai nut bam cung luc
            $st = $dblj->prepare("UPDATE game1 SET tf = tf - ?, uyxb = uyxb - ?
                                  WHERE sid = ? AND tf >= ? AND uyxb >= ?");
            $st->execute(array(intval($cost['tf']), intval($cost['linh_thach']), $sid,
                               intval($cost['tf']), intval($cost['linh_thach'])));
            if ($st->rowCount() !== 1) throw new Exception('thieu tf/linh thach');

            $dblj->prepare("UPDATE player_tianfu SET lv = lv + 1 WHERE sid = ? AND tianfu_id = ?")
                 ->execute(array($sid, $id));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $empty['msg'] = 'Luyện cấp thất bại, thử lại sau.';
            return $empty;
        }

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'lvup', $id, $lv, $lv + 1, 'cost tf=' . $cost['tf']
                  . ' lt=' . $cost['linh_thach'], $dblj);
        player_cache_clear($sid);   // ham cua class/player.php, cung namespace player

        return array('ok' => true, 'lv' => $lv + 1, 'cap' => $cap, 'cost' => $cost,
                     'msg' => 'Luyện ' . $def['name'] . ' thành công — cấp ' . ($lv + 1) . '.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break')) {
    function tianfu_break($sid, $dblj, $id){
        $empty = array('ok' => false, 'quality' => 0, 'cost' => array(), 'msg' => '');
        $def = tianfu_def($id);
        if (!$def) { $empty['msg'] = 'Thiên phú không tồn tại.'; return $empty; }

        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (intval($O[$id]['have']) !== 1) {
            $empty['msg'] = 'Chưa khai mở thiên phú này.';
            return $empty;
        }
        $q   = intval($O[$id]['quality']);
        $lv  = intval($O[$id]['lv']);
        $cap = tianfu_lv_cap($q);

        $cost = tianfu_break_cost($q);
        if ($cost === false) {
            $empty['quality'] = $q;
            $empty['msg'] = $def['name'] . ' đã ở phẩm chất cao nhất (Thần/Đế/… Phẩm 10).';
            return $empty;
        }
        if ($lv < $cap) {
            $empty['quality'] = $q;
            $empty['msg'] = 'Cần đạt trần cấp ' . $cap . ' trước khi Dot Pham — hiện ' . $lv . '.';
            return $empty;
        }

        $W = tianfu_player_wallet($sid, $dblj);
        if ($W['ulv'] < intval($cost['ulv_require'])) {
            $empty['quality'] = $q;
            $empty['msg'] = 'Cảnh giới chưa đủ: cần cấp ' . $cost['ulv_require'] . ', đang cấp ' . $W['ulv'] . '.';
            return $empty;
        }
        if ($W['tf'] < intval($cost['tf'])) {
            $empty['msg'] = 'Điểm Thiên Phú không đủ: cần ' . $cost['tf'] . ', có ' . $W['tf'] . '.';
            return $empty;
        }
        if ($W['uyxb'] < intval($cost['linh_thach'])) {
            $empty['msg'] = 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach'], 0, ',', '.')
                          . ', có ' . number_format($W['uyxb'], 0, ',', '.') . '.';
            return $empty;
        }

        $dblj->beginTransaction();
        try {
            $st = $dblj->prepare("UPDATE game1 SET tf = tf - ?, uyxb = uyxb - ?
                                  WHERE sid = ? AND tf >= ? AND uyxb >= ?");
            $st->execute(array(intval($cost['tf']), intval($cost['linh_thach']), $sid,
                               intval($cost['tf']), intval($cost['linh_thach'])));
            if ($st->rowCount() !== 1) throw new Exception('thieu tf/linh thach');

            $up = $dblj->prepare("UPDATE player_tianfu SET quality = quality + 1
                                  WHERE sid = ? AND tianfu_id = ? AND quality = ?");
            $up->execute(array($sid, $id, $q));
            if ($up->rowCount() !== 1) throw new Exception('quality bi doi');
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $empty['msg'] = 'Đột phá phẩm chất thất bại, thử lại sau.';
            return $empty;
        }

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'break', $id, $q, $q + 1, 'cost tf=' . $cost['tf']
                   . ' lt=' . $cost['linh_thach'], $dblj);
        player_cache_clear($sid);   // ham cua class/player.php, cung namespace player

        return array('ok' => true, 'quality' => $q + 1, 'cost' => $cost,
                     'msg' => 'Đột phá ' . $def['name'] . ' lên '
                            . tianfu_quality_name($q + 1) . '.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lvup_rows')) {
    /** 3 dong cho panel Nang Cap (Task 8/9 dung de in va chan nut). */
    function tianfu_lvup_rows($sid, $dblj, $id){
        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (!isset($O[$id]) || intval($O[$id]['have']) !== 1) return array();
        $cost = tianfu_lv_cost(intval($O[$id]['lv']));
        $cap  = tianfu_lv_cap(intval($O[$id]['quality']));
        $W    = tianfu_player_wallet($sid, $dblj);
        return array(
            tianfu_row_state('Điểm Thiên Phú: ' . $W['tf'] . ' / ' . $cost['tf'],
                             $W['tf'] >= $cost['tf']),
            tianfu_row_state('Linh Thạch: ' . number_format($W['uyxb'], 0, ',', '.') . ' / '
                             . number_format($cost['linh_thach'], 0, ',', '.'),
                             $W['uyxb'] >= $cost['linh_thach']),
            tianfu_row_state('Cấp: ' . intval($O[$id]['lv']) . ' / ' . $cap . ' (trần '
                             . tianfu_quality_name(intval($O[$id]['quality'])) . ')',
                             intval($O[$id]['lv']) < $cap),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break_rows')) {
    /** 4 dong cho panel Dot Pham. */
    function tianfu_break_rows($sid, $dblj, $id){
        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (!isset($O[$id]) || intval($O[$id]['have']) !== 1) return array();
        $q    = intval($O[$id]['quality']);
        $lv   = intval($O[$id]['lv']);
        $cap  = tianfu_lv_cap($q);
        $cost = tianfu_break_cost($q);
        $W    = tianfu_player_wallet($sid, $dblj);
        if ($cost === false) {
            return array(tianfu_row_state('Đã ở phẩm chất cao nhất', false));
        }
        return array(
            tianfu_row_state('Cấp trần: ' . $lv . ' / ' . $cap, $lv >= $cap),
            tianfu_row_state('Điểm Thiên Phú: ' . $W['tf'] . ' / ' . $cost['tf'],
                             $W['tf'] >= $cost['tf']),
            tianfu_row_state('Linh Thạch: ' . number_format($W['uyxb'], 0, ',', '.') . ' / '
                             . number_format($cost['linh_thach'], 0, ',', '.'),
                             $W['uyxb'] >= $cost['linh_thach']),
            tianfu_row_state('Cảnh giới: ' . $W['ulv'] . ' / ' . $cost['ulv_require'],
                             $W['ulv'] >= $cost['ulv_require']),
        );
    }
}

// ======================================================================
// Task 6 — Phu Vi (slot)
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_conflict')) {
    /**
     | Hai thien phu xung dot neu tap cong (gate chinh + gate phu) giao nhau.
     | $act: mang slot => row (nhu tianfu_activated()), LA BUOI DA QUYET
     | (goi truoc khi ghi). false = khong xung dot.
     */
    function tianfu_slot_conflict($sid, $dblj, $id, $slot, $act){
        $def = tianfu_def($id);
        if (!$def) return 'Thiên phú không tồn tại.';
        $gates = array_keys(tianfu_contributions($def));
        foreach ($act as $s => $row) {
            if (intval($s) === intval($slot)) continue;      // chinh cho do se bi ghi de
            if (intval($row['id']) === intval($id)) continue; // dung mot o nay
            $d2 = tianfu_def($row['id']);
            if (!$d2) continue;
            $share = array_intersect($gates, array_keys(tianfu_contributions($d2)));
            if (count($share) > 0) {
                return tianfu_gate_name(reset($share));
            }
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_set')) {
    /**
     | $id = 0, $slot > 0 : xoa o dang dung trong $slot.
     | $id > 0,  $slot = 0: thao thien phu $id.
     | $id > 0,  $slot 1..4: bat $id vao $slot; neu $id dang o cho khac thi
     | hoan vi voi o dang nam trong $slot (neu hoan vi van khong xung dot).
     */
    function tianfu_slot_set($sid, $dblj, $id, $slot){
        $id   = intval($id);
        $slot = intval($slot);
        $bad  = array('ok' => false, 'slot' => $slot, 'moved' => array(), 'msg' => '',
                      'mirror' => array());

        if ($slot < 0 || $slot > 4) { $bad['msg'] = 'Phu Vi không hợp lệ.'; return $bad; }
        if ($id === 0 && $slot === 0) { $bad['msg'] = 'Không có thay đổi.'; return $bad; }

        $O   = tianfu_owned($sid, $dblj);
        $W   = tianfu_player_wallet($sid, $dblj);
        $act = tianfu_activated($sid, $dblj);

        if ($id > 0) {
            if (intval($O[$id]['have']) !== 1) {
                $bad['msg'] = 'Chưa khai mở thiên phú này — cần Khai Phú Đan.';
                return $bad;
            }
            if ($slot > 0) {
                if (!in_array($slot, tianfu_slot_open($W['ulv']), true)) {
                    $sd = array();
                    foreach (tianfu_slot_defs() as $s) $sd[intval($s['i'])] = intval($s['ulv']);
                    $bad['msg'] = 'Phu Vi ' . $slot . ' chưa mở — cần cấp ' . $sd[$slot]
                                . ', đang cấp ' . $W['ulv'] . '.';
                    return $bad;
                }
                $clash = tianfu_slot_conflict($sid, $dblj, $id, $slot, $act);
                if ($clash !== false) {
                    $bad['msg'] = 'Không thể kích hoạt hai thiên phú cùng cổng "'
                                . $clash . '" — chọn Phu Vi thuộc nhóm khác.';
                    return $bad;
                }
            }
        }

        // Tinh toan bo mapping slot => id moi (van trong bo nho, chua ghi)
        $target = intval($O[$id]['slot']);          // $id = 0 -> 0
        $plan   = array();                          // slot => id|0
        foreach ($act as $s => $row) $plan[$s] = intval($row['id']);

        if ($slot === 0) {                          // thao do
            if ($target === 0) { $bad['msg'] = 'Thiên phú này chưa được kích hoạt.'; return $bad; }
            $plan[$target] = 0;
        } elseif ($id === 0) {                      // xoa o
            if (!isset($plan[$slot])) { $bad['msg'] = 'Phu Vi này đang trống.'; return $bad; }
            $plan[$slot] = 0;
        } else {
            $prev = isset($plan[$slot]) ? intval($plan[$slot]) : 0;
            $plan[$slot] = $id;
            if ($target > 0) {
                $plan[$target] = $prev;             // hoan vi
                if ($prev === 0) unset($plan[$target]);
            }
        }
        // Don cac o = 0
        $final = array();
        foreach ($plan as $s => $v) { if ($v > 0) $final[$s] = $v; }

        $dblj->beginTransaction();
        try {
            // Xoa toan bo cac o cua nhan vat roi ghi lai theo $final: don gian,
            // khong lo thu tu UPDATE trung gate (MySQL 5.5 khong co "UPDATE ... SET slot
            // = CASE WHEN" de doc). Toi da 4 dong -> chi 2 cau lenh.
            $dblj->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ?")
                 ->execute(array($sid));
            $ins = $dblj->prepare("UPDATE player_tianfu SET slot = ? WHERE sid = ? AND tianfu_id = ?");
            foreach ($final as $s => $v) $ins->execute(array($s, $sid, $v));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $bad['msg'] = 'Thay đổi Phu Vi thất bại, thử lại sau.';
            return $bad;
        }

        $moved = array();
        foreach ($act as $s => $row) {
            $nowIn = 0;
            foreach ($final as $fs => $fv) { if ($fv === intval($row['id'])) $nowIn = $fs; }
            if ($nowIn !== intval($s)) $moved[intval($row['id'])] = $nowIn;
        }
        if ($id > 0 && !isset($moved[$id])) {
            $nowIn = 0;
            foreach ($final as $fs => $fv) { if ($fv === $id) $nowIn = $fs; }
            if ($nowIn !== $target) $moved[$id] = $nowIn;
        }

        $mirror = tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'slot', $id, $target, $slot,
                   'activated=' . implode(',', $final), $dblj);
        player_cache_clear($sid);

        return array('ok' => true, 'slot' => $slot, 'moved' => $moved,
                     'msg' => $slot === 0 ? 'Đã thu hồi thiên phú.' : 'Đã kích hoạt thiên phú.',
                     'mirror' => $mirror);
    }
}
