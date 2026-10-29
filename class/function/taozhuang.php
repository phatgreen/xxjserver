<?php
namespace player;

/*
|--------------------------------------------------------------------------
| HỆ THỐNG THẦN TRANG JSON
|--------------------------------------------------------------------------
| File này thay thế việc đọc bảng taozhuang trong MySQL.
|
| Cách hoạt động:
| 1. Trang bị đang mặc có cột / thuộc tính `taozhuang`.
| 2. Hàm sẽ gom trang bị theo từng bậc taozhuang.
|    Ví dụ:
|    - Bậc 1: 5 món
|    - Bậc 2: 8 món
|    - Bậc 3: 3 món
| 3. Mỗi nhóm bậc đạt mốc 4 / 8 / 12 / 16 món sẽ kích hoạt buff riêng.
| 4. Nếu mặc đan xen nhiều bậc, các nhóm đủ mốc sẽ được cộng dồn.
|
| Ví dụ cấu hình mặc định:
| - Bậc 1 có 4 món  => HP +1%, Công +1%
| - Bậc 1 có 8 món  => HP +2%, Công +2%
| - Bậc 2 có 4 món  => HP +2%, Công +2%
| - Bậc 2 có 8 món  => HP +3%, Công +3%
|
| Muốn chỉnh buff:
| - Mở file /class/config/taozhuang_config.json
| - Sửa `base_percent` của từng bậc.
| - Sửa `stage_rules` nếu muốn mốc 8/12/16 cộng thêm nhiều hoặc ít hơn.
|
| Lưu ý PHP 5.6:
| - Không dùng cú pháp type hint mới.
| - Không dùng toán tử ??.
| - Không dùng short closure.
*/

/**
 * Trả về đường dẫn file cấu hình Thần Trang.
 *
 * Ưu tiên:
 * 1. /class/config/taozhuang_config.json
 * 2. /class/taozhuang_config.json
 */
function taozhuang_config_path()
{
    $paths = array(
        __DIR__ . '/../config/taozhuang_config.json',
        __DIR__ . '/../taozhuang_config.json'
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

/**
 * Đọc toàn bộ cấu hình JSON.
 *
 * Có cache tĩnh để mỗi request chỉ đọc file 1 lần, tránh lag.
 */
function taozhuang_config_all()
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $file = taozhuang_config_path();
    if (!is_file($file)) {
        $cache = taozhuang_default_config();
        return $cache;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $cache = taozhuang_default_config();
        return $cache;
    }

    if (!isset($data['stage_rules']) || !is_array($data['stage_rules'])) {
        $data['stage_rules'] = taozhuang_default_stage_rules();
    }

    if (!isset($data['tiers']) || !is_array($data['tiers'])) {
        $data['tiers'] = array();
    }

    $cache = $data;
    return $cache;
}

/**
 * Cấu hình dự phòng khi chưa có JSON hoặc JSON lỗi.
 */
function taozhuang_default_config()
{
    return array(
        'mode' => 'percent',
        'stage_rules' => taozhuang_default_stage_rules(),
        'tiers' => array()
    );
}

/**
 * Mốc kích hoạt mặc định.
 *
 * extra_percent:
 * - Mốc 4 món không cộng thêm.
 * - Mốc 8 món cộng thêm HP/Công +1%.
 * - Mốc 12 món cộng thêm HP/Công +2%, Phòng +1%.
 * - Mốc 16 món cộng thêm HP/Công +3%, Phòng +1%, Bạo +1%.
 */
function taozhuang_default_stage_rules()
{
    return array(
        '4'  => array('title' => 'Sơ thành',    'extra_percent' => array('hp' => 0, 'gj' => 0, 'fy' => 0, 'bj' => 0, 'sb' => 0, 'xx' => 0, 'xy' => 0)),
        '8'  => array('title' => 'Trung thành', 'extra_percent' => array('hp' => 1, 'gj' => 1, 'fy' => 0, 'bj' => 0, 'sb' => 0, 'xx' => 0, 'xy' => 0)),
        '12' => array('title' => 'Đại thành',   'extra_percent' => array('hp' => 2, 'gj' => 2, 'fy' => 1, 'bj' => 0, 'sb' => 0, 'xx' => 0, 'xy' => 0)),
        '16' => array('title' => 'Hoàn chỉnh',  'extra_percent' => array('hp' => 3, 'gj' => 3, 'fy' => 1, 'bj' => 1, 'sb' => 0, 'xx' => 0, 'xy' => 0))
    );
}

/**
 * Lấy cấu hình riêng của một bậc Thần Trang.
 *
 * Nếu JSON chưa khai báo bậc đó, tự tạo cấu hình mặc định:
 * - Bậc N mặc 4 món = HP +N%, Công +N%.
 */
function taozhuang_config_get_tier($tier)
{
    $tier = intval($tier);
    $all = taozhuang_config_all();

    if (isset($all['tiers'][(string)$tier]) && is_array($all['tiers'][(string)$tier])) {
        return $all['tiers'][(string)$tier];
    }

    return array(
        'tzid' => $tier,
        'name' => 'Thần Trang ' . $tier . ' Sao',
        'info' => 'Cấu hình mặc định tự sinh.',
        'color' => '#f5a400',
        'base_percent' => array(
            'hp' => $tier,
            'gj' => $tier,
            'fy' => 0,
            'bj' => 0,
            'sb' => 0,
            'xx' => 0,
            'xy' => 0
        )
    );
}

/**
 * Lấy giá trị số trong mảng an toàn.
 */
function taozhuang_num($arr, $key, $default)
{
    if (is_array($arr) && isset($arr[$key]) && is_numeric($arr[$key])) {
        return $arr[$key] + 0;
    }
    return $default;
}

/**
 * Xác định mốc kích hoạt theo số món.
 */
function taozhuang_stage_by_count($count)
{
    $count = intval($count);

    // Mốc "Hoàn chỉnh" (khóa 16 trong config) đạt khi đủ 15 món: ô 16 Bảo Vật chưa có trang bị (đợt F).
    if ($count >= 15) return 16;
    if ($count >= 12) return 12;
    if ($count >= 8)  return 8;
    if ($count >= 4)  return 4;

    return 0;
}

/**
 * Lấy tiêu đề mốc kích hoạt.
 */
function taozhuang_stage_title($stage)
{
    $all = taozhuang_config_all();
    $key = (string)intval($stage);

    if (isset($all['stage_rules'][$key]['title'])) {
        return $all['stage_rules'][$key]['title'];
    }

    if ($stage == 16) return 'Hoàn chỉnh';
    if ($stage == 12) return 'Đại thành';
    if ($stage == 8)  return 'Trung thành';
    if ($stage == 4)  return 'Sơ thành';

    return 'Chưa kích hoạt';
}

/**
 * Lấy phần cộng thêm theo mốc 4/8/12/16.
 */
function taozhuang_stage_extra_percent($stage)
{
    $all = taozhuang_config_all();
    $key = (string)intval($stage);

    if (isset($all['stage_rules'][$key]['extra_percent']) && is_array($all['stage_rules'][$key]['extra_percent'])) {
        return $all['stage_rules'][$key]['extra_percent'];
    }

    $defaults = taozhuang_default_stage_rules();
    return isset($defaults[$key]['extra_percent']) ? $defaults[$key]['extra_percent'] : array();
}

/**
 * Gom danh sách trang bị theo bậc taozhuang.
 *
 * $equips là mảng object trang bị lấy từ playerzhuangbei.
 */
function taozhuang_group_equips($equips)
{
    $groups = array();

    if (!is_array($equips)) {
        return $groups;
    }

    foreach ($equips as $zb) {
        if (!is_object($zb) || !isset($zb->taozhuang)) {
            continue;
        }

        $tier = intval($zb->taozhuang);
        if ($tier <= 0) {
            continue;
        }

        if (!isset($groups[$tier])) {
            $groups[$tier] = array(
                'tier' => $tier,
                'count' => 0,
                'items' => array()
            );
        }

        $groups[$tier]['count']++;
        $groups[$tier]['items'][] = $zb;
    }

    ksort($groups);
    return $groups;
}

/**
 * Tính buff cho một nhóm bậc.
 *
 * Công thức:
 * final_percent = base_percent của bậc + extra_percent của mốc.
 */
function taozhuang_calc_group_buff($tier, $count)
{
    $tier = intval($tier);
    $count = intval($count);

    $stage = taozhuang_stage_by_count($count);
    $tierConfig = taozhuang_config_get_tier($tier);
    $base = isset($tierConfig['base_percent']) && is_array($tierConfig['base_percent']) ? $tierConfig['base_percent'] : array();
    $extra = taozhuang_stage_extra_percent($stage);

    $attrs = array('hp', 'gj', 'fy', 'bj', 'sb', 'xx', 'xy');
    $percent = array();

    foreach ($attrs as $attr) {
        $percent[$attr] = taozhuang_num($base, $attr, 0) + taozhuang_num($extra, $attr, 0);
    }

    return array(
        'active' => ($stage > 0),
        'tier' => $tier,
        'count' => $count,
        'stage' => $stage,
        'title' => taozhuang_stage_title($stage),
        'name' => isset($tierConfig['name']) ? $tierConfig['name'] : ('Thần Trang ' . $tier . ' Sao'),
        'info' => isset($tierConfig['info']) ? $tierConfig['info'] : '',
        'color' => isset($tierConfig['color']) ? $tierConfig['color'] : '#f5a400',
        'percent' => $percent
    );
}

/**
 * Tính tổng buff Thần Trang từ danh sách trang bị.
 *
 * Return gồm:
 * - groups: chi tiết từng bậc.
 * - total_percent: tổng % cộng dồn của tất cả nhóm đã kích hoạt.
 * - active_count: số nhóm đã kích hoạt.
 */
function taozhuang_calc_buff_from_equips($equips)
{
    $groups = taozhuang_group_equips($equips);

    $total = array('hp' => 0, 'gj' => 0, 'fy' => 0, 'bj' => 0, 'sb' => 0, 'xx' => 0, 'xy' => 0);
    $details = array();
    $activeCount = 0;
    $bestTier = 0;
    $bestStage = 0;

    foreach ($groups as $tier => $group) {
        $buff = taozhuang_calc_group_buff($tier, $group['count']);
        $details[$tier] = $buff;

        if ($buff['active']) {
            $activeCount++;

            foreach ($total as $attr => $value) {
                $total[$attr] += isset($buff['percent'][$attr]) ? $buff['percent'][$attr] : 0;
            }

            if ($buff['stage'] > $bestStage || ($buff['stage'] == $bestStage && $tier > $bestTier)) {
                $bestStage = $buff['stage'];
                $bestTier = $tier;
            }
        }
    }

    return array(
        'groups' => $details,
        'total_percent' => $total,
        'active_count' => $activeCount,
        'best_tier' => $bestTier,
        'best_stage' => $bestStage
    );
}

/**
 * Lấy trang bị đang mặc của người chơi bằng các ô tool1 -> tool16.
 *
 * Hàm này dùng cho giao diện hoặc nơi chưa có sẵn danh sách trang bị.
 */
function taozhuang_get_player_equips($player, $dblj)
{
    $toolIds = array();

    for ($i = 1; $i <= 16; $i++) {
        $key = 'tool' . $i;
        if (is_object($player) && isset($player->$key) && intval($player->$key) > 0) {
            $toolIds[] = intval($player->$key);
        }
    }

    if (empty($toolIds)) {
        return array();
    }

    if (function_exists(__NAMESPACE__ . '\getzbsByIds')) {
        $map = getzbsByIds($toolIds, $dblj);
        $equips = array();

        foreach ($toolIds as $id) {
            if (isset($map[$id]) && $map[$id]) {
                $equips[] = $map[$id];
            }
        }

        return $equips;
    }

    $equips = array();
    foreach ($toolIds as $id) {
        $zb = getzb($id, $dblj);
        if ($zb) {
            $equips[] = $zb;
        }
    }

    return $equips;
}

/**
 * Tính tóm tắt Thần Trang của người chơi.
 */
function taozhuang_get_player_summary($player, $dblj)
{
    $equips = taozhuang_get_player_equips($player, $dblj);
    return taozhuang_calc_buff_from_equips($equips);
}

/**
 * Áp dụng buff Thần Trang vào thuộc tính tổng của player.
 *
 * Chỉ cộng vào object trong runtime, không UPDATE vào game1.
 * Lý do:
 * - Tránh cộng dồn sai khi load nhiều lần.
 * - Trang bị thay đổi thì buff tự thay đổi theo.
 *
 * Thuộc tính được cộng:
 * - hp  -> umaxhp
 * - gj  -> ugj
 * - fy  -> ufy
 * - bj  -> ubj
 * - xx  -> uxx
 * - sb/xy hiện chỉ lưu vào biến phụ để sau này dùng nếu có né tránh/may mắn.
 */
function taozhuang_apply_buff_to_player($player, $equips)
{
    if (!is_object($player)) {
        return $player;
    }

    $summary = taozhuang_calc_buff_from_equips($equips);
    $p = $summary['total_percent'];

    $hpPercent = isset($p['hp']) ? $p['hp'] : 0;
    $gjPercent = isset($p['gj']) ? $p['gj'] : 0;
    $fyPercent = isset($p['fy']) ? $p['fy'] : 0;
    $bjPercent = isset($p['bj']) ? $p['bj'] : 0;
    $xxPercent = isset($p['xx']) ? $p['xx'] : 0;

    $player->shenzhuang_active_count = $summary['active_count'];
    $player->shenzhuang_best_tier = $summary['best_tier'];
    $player->shenzhuang_best_stage = $summary['best_stage'];

    $player->shenzhuang_hp_percent = $hpPercent;
    $player->shenzhuang_gj_percent = $gjPercent;
    $player->shenzhuang_fy_percent = $fyPercent;
    $player->shenzhuang_bj_percent = $bjPercent;
    $player->shenzhuang_xx_percent = $xxPercent;
    $player->shenzhuang_sb_percent = isset($p['sb']) ? $p['sb'] : 0;
    $player->shenzhuang_xy_percent = isset($p['xy']) ? $p['xy'] : 0;

    $player->shenzhuang_add_hp = intval(round(intval($player->umaxhp) * $hpPercent / 100));
    $player->shenzhuang_add_gj = intval(round(intval($player->ugj) * $gjPercent / 100));
    $player->shenzhuang_add_fy = intval(round(intval($player->ufy) * $fyPercent / 100));
    $player->shenzhuang_add_bj = round(floatval($player->ubj) * $bjPercent / 100, 2);
    $player->shenzhuang_add_xx = round(floatval($player->uxx) * $xxPercent / 100, 2);

    $player->umaxhp += $player->shenzhuang_add_hp;
    $player->ugj    += $player->shenzhuang_add_gj;
    $player->ufy    += $player->shenzhuang_add_fy;
    $player->ubj    += $player->shenzhuang_add_bj;
    $player->uxx    += $player->shenzhuang_add_xx;

    return $player;
}
?>
