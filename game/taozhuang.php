<?php
// Thần Trang - giao diện dùng cấu hình JSON
// -----------------------------------------------------------------------------
// File này chỉ hiển thị giao diện.
// Logic tính buff đã chuyển sang: /class/function/taozhuang.php
// Cấu hình chỉ số nằm ở: /class/config/taozhuang_config.json
// -----------------------------------------------------------------------------

$player = player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=".$player->nowmid."&sid=".$sid);

if (!function_exists('tz_ui_escape')) {
    function tz_ui_escape($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tz_ui_get_prop')) {
    function tz_ui_get_prop($obj, $prop, $default = '')
    {
        if (is_object($obj) && isset($obj->$prop)) {
            return $obj->$prop;
        }
        return $default;
    }
}

if (!function_exists('tz_ui_star_class')) {
    function tz_ui_star_class($tier)
    {
        $tier = intval($tier);
        if ($tier <= 1) return 'tier1';
        if ($tier == 2) return 'tier2';
        if ($tier == 3) return 'tier3';
        if ($tier == 4) return 'tier4';
        return 'tier5';
    }
}

if (!function_exists('tz_ui_star_html')) {
    function tz_ui_star_html($tier, $showText = false)
    {
        $tier = intval($tier);
        if ($tier <= 0) {
            return '<span class="tz-stars empty">☆☆☆☆☆</span>';
        }

        $filled = $tier;
        if ($filled > 5) $filled = 5;

        $html = '<span class="tz-stars '.tz_ui_star_class($tier).'">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= ($i <= $filled) ? '★' : '☆';
        }
        $html .= '</span>';

        if ($showText) {
            $html .= '<span class="tz-tier-text">Bậc '.$tier.'</span>';
        }

        return $html;
    }
}

if (!function_exists('tz_ui_progress')) {
    function tz_ui_progress($count)
    {
        $count = intval($count);
        $percent = min(100, intval($count / 16 * 100));
        return '<div class="tz-progress"><div class="tz-progress-in" style="width:'.$percent.'%"></div></div>';
    }
}

if (!function_exists('tz_ui_attr_line')) {
    function tz_ui_attr_line($label, $value, $icon)
    {
        $value = $value + 0;
        if ($value == 0) return '';

        return '<div class="tz-attr-line"><span>'.$icon.' '.$label.'</span><b>+'.$value.'%</b></div>';
    }
}

$slotNames = array();
$_toolNames = \player\zhuangbei_tool_names();
for ($i = 1; $i <= 16; $i++) {
    $slotNames[$i] = $_toolNames[$i] . ' chưa có';
}

// Lấy danh sách trang bị và summary từ function JSON.
$equips = array();
$equipMap = array();
for ($i = 1; $i <= 16; $i++) {
    $toolField = 'tool'.$i;
    $zbId = intval(tz_ui_get_prop($player, $toolField, 0));
    $zb = player\getzb($zbId, $dblj);
    if ($zb) {
        $equips[] = $zb;
        $equipMap[$i] = $zb;
    } else {
        $equipMap[$i] = false;
    }
}

if (function_exists('player\taozhuang_calc_buff_from_equips')) {
    $summary = player\taozhuang_calc_buff_from_equips($equips);
} else {
    $summary = array(
        'groups' => array(),
        'total_percent' => array('hp' => 0, 'gj' => 0, 'fy' => 0, 'bj' => 0, 'sb' => 0, 'xx' => 0, 'xy' => 0),
        'active_count' => 0,
        'best_tier' => 0,
        'best_stage' => 0
    );
}

$total = isset($summary['total_percent']) ? $summary['total_percent'] : array();
$activeCount = isset($summary['active_count']) ? intval($summary['active_count']) : 0;
$bestTier = isset($summary['best_tier']) ? intval($summary['best_tier']) : 0;
$bestStage = isset($summary['best_stage']) ? intval($summary['best_stage']) : 0;

// Danh sách trang bị đang mặc.
$equipHtml = '';
for ($i = 1; $i <= 16; $i++) {
    $zb = isset($equipMap[$i]) ? $equipMap[$i] : false;

    $name = tz_ui_get_prop($zb, 'zbname', '');
    if ($name == '') $name = $slotNames[$i];

    $color = tz_ui_get_prop($zb, 'zbys', '');
    if ($color == '') $color = '#8a8194';

    $tier = intval(tz_ui_get_prop($zb, 'taozhuang', 0));

    $equipHtml .= '<div class="tz-equip-row">';
    $equipHtml .= '<div class="tz-equip-left">';
    $equipHtml .= '<span class="tz-slot">'.sprintf('%02d', $i).'</span>';
    // Nếu ô này có trang bị thật thì tên trang bị sẽ là link xem chi tiết giống zhuangtai.php.
    // Link mở chức năng chakanzb, truyền zbnowid + uid + sid để xem thông tin trang bị.
    if ($zb && $zbId > 0) {
        $zbInfoCmd = $encode->encode("cmd=chakanzb&zbnowid=".$zbId."&uid=".$player->uid."&sid=".$sid);
        $equipHtml .= '<a class="equip-link" href="?cmd='.$zbInfoCmd.'">';
        $equipHtml .= '<span class="equip-title" style="color:'.tz_ui_escape($color).'">'.tz_ui_escape($name).'</span>';
        $equipHtml .= '</a>';
    } else {
        // Ô trống thì chỉ hiện chữ, không tạo link để tránh mở trang xem trang bị rỗng.
        $equipHtml .= '<span class="tz-equip-name empty" style="color:'.tz_ui_escape($color).'">'.tz_ui_escape($name).'</span>';
    }
    $equipHtml .= '</div>';
    $equipHtml .= '<div class="tz-equip-star">'.tz_ui_star_html($tier, false).'</div>';
    $equipHtml .= '</div>';
}

// Chi tiết nhóm kích hoạt / chưa kích hoạt.
$activeHtml = '';
$inactiveHtml = '';

if (isset($summary['groups']) && is_array($summary['groups'])) {
    foreach ($summary['groups'] as $tier => $group) {
        $tier = intval($tier);
        $count = isset($group['count']) ? intval($group['count']) : 0;
        $stage = isset($group['stage']) ? intval($group['stage']) : 0;
        $title = isset($group['title']) ? $group['title'] : '';
        $percent = isset($group['percent']) && is_array($group['percent']) ? $group['percent'] : array();

        if (!empty($group['active'])) {
            $activeHtml .= '<div class="tz-active-card">';
            $activeHtml .= '<div class="tz-active-head">';
            $activeHtml .= '<div><div class="tz-active-name">'.tz_ui_star_html($tier, true).'</div><div class="tz-active-sub">'.$title.' · Mốc '.$stage.'/16</div></div>';
            $activeHtml .= '<div class="tz-count-badge">'.$count.'/16</div>';
            $activeHtml .= '</div>';
            $activeHtml .= tz_ui_progress($count);
            $activeHtml .= '<div class="tz-attr-grid">';
            $activeHtml .= tz_ui_attr_line('HP', isset($percent['hp']) ? $percent['hp'] : 0, '♥');
            $activeHtml .= tz_ui_attr_line('Công', isset($percent['gj']) ? $percent['gj'] : 0, '⚔');
            $activeHtml .= tz_ui_attr_line('Phòng', isset($percent['fy']) ? $percent['fy'] : 0, '🛡');
            $activeHtml .= tz_ui_attr_line('Bạo', isset($percent['bj']) ? $percent['bj'] : 0, '✦');
            $activeHtml .= tz_ui_attr_line('Hút máu', isset($percent['xx']) ? $percent['xx'] : 0, '♦');
            $activeHtml .= '</div>';
            $activeHtml .= '</div>';
        } else {
            $need = 4 - $count;
            if ($need < 0) $need = 0;

            $inactiveHtml .= '<div class="tz-inactive-row">';
            $inactiveHtml .= '<span>'.tz_ui_star_html($tier, true).'</span>';
            $inactiveHtml .= '<b>'.$count.'/4</b>';
            $inactiveHtml .= '<em>Cần thêm '.$need.' món</em>';
            $inactiveHtml .= '</div>';
        }
    }
}

if ($activeHtml == '') {
    $activeHtml = '<div class="tz-empty">Cần tối thiểu <b>4 món cùng bậc sao</b> để kích hoạt Thần Trang.</div>';
}

// Panel tổng buff.
$totalHp = isset($total['hp']) ? $total['hp'] : 0;
$totalGj = isset($total['gj']) ? $total['gj'] : 0;
$totalFy = isset($total['fy']) ? $total['fy'] : 0;
$totalBj = isset($total['bj']) ? $total['bj'] : 0;
$totalXx = isset($total['xx']) ? $total['xx'] : 0;

$ztcmd = $encode->encode("cmd=zhuangtai&sid=".$sid);
$cwinfo = $encode->encode("cmd=chongwu&cwid=".$player->cw."&canshu=cwinfo&sid=".$sid);
$sz = $encode->encode("cmd=taozhuang&sid=".$sid);
$gm = $encode->encode("cmd=czbgm&canshu2=gaiming2&sid=".$sid);

$tzhtml = <<<HTML


<div class="tz-wrap">
    <div class="tz-tabs">
        <a href="?cmd=$ztcmd">Nhân vật</a>
        <a href="?cmd=$cwinfo">Sủng vật</a>
        <a href="?cmd=$sz" class="active">Thần trang</a>
        <a href="?cmd=$gm">Đổi tên</a>
    </div>

    <div class="tz-hero">
        <div class="tz-hero-title">THẦN TRANG</div>
        <div class="tz-hero-sub">Kích hoạt theo nhóm bậc sao · cộng dồn khi mặc đan xen nhiều bậc</div>
        <div class="tz-hero-row">
            <div class="tz-total-card"><span>♥ Tổng HP</span><b>+$totalHp%</b></div>
            <div class="tz-total-card"><span>⚔ Tổng Công</span><b>+$totalGj%</b></div>
            <div class="tz-rank-card">
                {$bestTier}★
                <small>{$bestStage}/16</small>
            </div>
        </div>
    </div>

    <div class="tz-section-title">Trang bị đang mặc</div>
    <div class="tz-equip-list">
        $equipHtml
    </div>

    <div class="tz-legend" style="margin-top:8px;">
        <span><b class="tz-stars tier1">★☆☆☆☆</b> Bậc 1</span>
        <span><b class="tz-stars tier2">★★☆☆☆</b> Bậc 2</span>
        <span><b class="tz-stars tier3">★★★☆☆</b> Bậc 3</span>
        <span><b class="tz-stars tier4">★★★★☆</b> Bậc 4</span>
        <span><b class="tz-stars tier5">★★★★★</b> Bậc 5+</span>
    </div>

    <div class="tz-section-title">Chi tiết kích hoạt</div>
    $activeHtml

HTML;

if ($inactiveHtml != '') {
    $tzhtml .= <<<HTML
    <div class="tz-section-title">Nhóm chưa đủ mốc</div>
    $inactiveHtml
HTML;
}

$tzhtml .= <<<HTML

    <div class="tz-actions">
        <a href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $tzhtml;
?>
