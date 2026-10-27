<?php
namespace player;

/**
 * conghuong.php — Cộng Hưởng (Tàng Kinh Các): sưu tầm võ công + công pháp theo nhóm hệ,
 * đạt mốc số bộ Viên Mãn thì nhận % chỉ số vĩnh viễn. Không cần bảng mới:
 * đọc playerwugong (wgdj) và playercongphap (cpdj). Config: class/config/conghuong_config.json.
 * Tương thích PHP 5.6.
 */

if (!function_exists(__NAMESPACE__ . '\\conghuong_cfg')) {
    function conghuong_cfg(){
        static $cfg = null;
        if ($cfg === null) {
            $path = __DIR__ . '/../config/conghuong_config.json';
            $cfg = is_file($path) ? json_decode(file_get_contents($path), true) : array();
            if (!is_array($cfg)) $cfg = array();
        }
        return $cfg;
    }
}

if (!function_exists(__NAMESPACE__ . '\\conghuong_members')) {
    /**
     * Danh sách thành viên từng nhóm (từ 2 file config võ công / công pháp).
     * Trả array(group_key => array(array('type' => 'wg'|'cp', 'id', 'name', 'color', 'need'))).
     */
    function conghuong_members(){
        static $out = null;
        if ($out !== null) return $out;
        $cfg = conghuong_cfg();
        $out = array();
        $byElem = array();
        foreach (isset($cfg['groups']) ? $cfg['groups'] : array() as $g) {
            $out[$g['key']] = array();
            foreach ($g['elements'] as $el) $byElem[$el] = $g['key'];
        }
        $wgNeed = isset($cfg['vien_man']['wugong_lv']) ? intval($cfg['vien_man']['wugong_lv']) : 10;
        foreach (wugong_config_all() as $w) {
            $id = intval($w['wgid']);
            $el = isset($w['element']) ? $w['element'] : 'none';
            if ($id <= 0 || !isset($byElem[$el])) continue;
            $out[$byElem[$el]][] = array('type' => 'wg', 'id' => $id, 'name' => $w['wgname'],
                'color' => isset($w['wgys']) ? $w['wgys'] : '#fff', 'need' => $wgNeed);
        }
        foreach (congphap_config_all() as $c) {
            $id = intval($c['cpid']);
            $el = isset($c['element']) ? $c['element'] : 'none';
            if ($id <= 0 || !isset($byElem[$el])) continue;
            $out[$byElem[$el]][] = array('type' => 'cp', 'id' => $id, 'name' => $c['cpname'],
                'color' => isset($c['cpys']) ? $c['cpys'] : '#fff', 'need' => max(1, intval($c['max_level'])));
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\conghuong_progress')) {
    /**
     * Tiến độ Cộng Hưởng của 1 nhân vật (2 query). Mỗi nhóm: members (kèm owned/lv/done),
     * done (số bộ Viên Mãn), total, tiers (kèm reached, need thực), pct (tổng % đạt).
     * Thêm 'bonus' => array('atk' => %, 'def' => %, 'hp' => %).
     */
    function conghuong_progress($sid, $dblj){
        $wgLv = array();
        $stmt = $dblj->prepare('SELECT wgid, wgdj FROM playerwugong WHERE sid = ?');
        if ($stmt && $stmt->execute(array($sid))) {
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) $wgLv[intval($r['wgid'])] = intval($r['wgdj']);
        }
        $cpLv = array();
        $stmt = $dblj->prepare('SELECT cpid, cpdj FROM playercongphap WHERE sid = ?');
        if ($stmt && $stmt->execute(array($sid))) {
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) $cpLv[intval($r['cpid'])] = intval($r['cpdj']);
        }
        return conghuong_eval($wgLv, $cpLv);
    }
}

if (!function_exists(__NAMESPACE__ . '\\conghuong_eval')) {
    // Phần tính thuần (không DB) — tách riêng để test.
    function conghuong_eval($wgLv, $cpLv){
        $cfg = conghuong_cfg();
        $members = conghuong_members();
        $res = array('groups' => array(), 'bonus' => array('atk' => 0, 'def' => 0, 'hp' => 0));
        foreach (isset($cfg['groups']) ? $cfg['groups'] : array() as $g) {
            $list = isset($members[$g['key']]) ? $members[$g['key']] : array();
            $done = 0;
            foreach ($list as $i => $m) {
                $src = ($m['type'] == 'wg') ? $wgLv : $cpLv;
                $owned = isset($src[$m['id']]);
                $lv = $owned ? $src[$m['id']] : 0;
                $list[$i]['owned'] = $owned;
                $list[$i]['lv'] = $lv;
                $list[$i]['done'] = $owned && $lv >= $m['need'];
                if ($list[$i]['done']) $done++;
            }
            $total = count($list);
            $pct = 0;
            $tiers = array();
            foreach ($g['tiers'] as $t) {
                $need = ($t['need'] === 'all') ? $total : min($total, intval($t['need']));
                $reached = $total > 0 && $done >= $need;
                if ($reached) $pct += floatval($t['pct']);
                $tiers[] = array('need' => $need, 'all' => ($t['need'] === 'all'), 'pct' => floatval($t['pct']), 'reached' => $reached);
            }
            if (isset($res['bonus'][$g['stat']])) $res['bonus'][$g['stat']] += $pct;
            $res['groups'][] = array('key' => $g['key'], 'name' => $g['name'], 'icon' => $g['icon'], 'stat' => $g['stat'],
                'members' => $list, 'done' => $done, 'total' => $total, 'tiers' => $tiers, 'pct' => round($pct, 2));
        }
        foreach ($res['bonus'] as $k => $v) $res['bonus'][$k] = round($v, 2);
        return $res;
    }
}

if (!function_exists(__NAMESPACE__ . '\\conghuong_stat_name')) {
    function conghuong_stat_name($stat){
        $map = array('atk' => 'Công', 'def' => 'Phòng', 'hp' => 'Khí huyết');
        return isset($map[$stat]) ? $map[$stat] : $stat;
    }
}
