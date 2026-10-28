<?php
namespace player;

/**
 * dieu_huong.php — khung điều hướng chung (docs/UI_DON_GIAN_HOA_MOBILE.md, UI-2/UI-3):
 * thanh dưới cố định 5 tab, hàng tab con của từng tab, nhóm lối vào "Tiên Đồ".
 * Nút dẫn tới tính năng chưa mở theo cấp vẫn hiện, kèm nhãn 🔒 (feature_lock_mark).
 *
 * Một mục = array('lệnh&tham số', 'icon', 'nhãn'). Lệnh 'index.php' = liên kết thẳng (Rời Khỏi).
 */

if (!function_exists(__NAMESPACE__ . '\\nav_tabs_config')) {
    // 5 tab thanh dưới + các lệnh thuộc tab (để tô tab đang đứng) + tab con (nếu có).
    function nav_tabs_config(){
        return array(
            'tuluyen' => array(
                'item' => array('goxiulian', '🧘', 'Tu Luyện'),
                'cmds' => array('goxiulian', 'startxiulian', 'endxiulian', 'tupo', 'gocp', 'conghuong',
                    'linhcan', 'linhcan_wash', 'linhcan_quest', 'linhcan_boss', 'thechat', 'thechat_upgrade',
                    'huyetmach', 'huyetmach_transform', 'huyetmach_boss', 'huyetmach_upgrade',
                    'tianfu', 'tianfu_collection', 'tianfu_open', 'luonden', 'do_craft_luonden', 'use_detox_pill'),
            ),
            'hanhtau' => array(
                'item' => array('gomid', '🗺', 'Hành Tẩu'),
                'cmds' => array('gomid', 'pve', 'pvegj', 'pvb', 'pvbgj', 'taopao', 'boss', 'npc', 'qydt', 'allmap',
                    'phoban', 'toayeuthap', 'vandinhdai', 'hammo', 'sauthu', 'shangdian', 'thiencoccac', 'fangshi', 'quydoi',
                    'club', 'clublist', 'clubboss_fight', 'im', 'liaotian', 'paihang', 'duihuan', 'getplayerinfo', 'pvp'),
            ),
            'nhanvat' => array(
                'item' => array('zhuangtai', '👤', 'Nhân Vật'),
                'cmds' => array('zhuangtai', 'trangbi', 'xxzb', 'setzbwz', 'quickequip', 'zbinfo', 'chakanzb', 'upzb',
                    'taozhuang', 'getbagzb', 'getbagyp', 'getbagyd', 'getbagjn', 'getbagdj', 'delezb', 'usedaoju',
                    'djinfo', 'ypinfo', 'ydinfo', 'jninfo', 'wgxiulian', 'wgxl', 'jswg', 'xxwg', 'chetao', 'do_craft_chetao'),
                'sub' => array(
                    array(array('zhuangtai', '👤', 'Thông Tin'), array('zhuangtai')),
                    array(array('trangbi', '🗡️', 'Trang Bị'), array('trangbi', 'xxzb', 'setzbwz', 'quickequip', 'zbinfo', 'chakanzb', 'upzb', 'taozhuang')),
                    array(array('getbagzb', '🎒', 'Túi Đồ'), array('getbagzb', 'getbagyp', 'getbagyd', 'getbagjn', 'getbagdj', 'delezb', 'usedaoju', 'djinfo', 'ypinfo', 'ydinfo', 'jninfo')),
                    array(array('wgxiulian', '🥋', 'Võ Công'), array('wgxiulian', 'wgxl', 'jswg')),
                    array(array('xxwg', '📖', 'Bí Tịch'), array('xxwg')),
                    array(array('chetao', '🔨', 'Chế Tạo'), array('chetao', 'do_craft_chetao')),
                ),
            ),
            'linhsung' => array(
                // Không có tab con: Sào Thú vào từ Phó Bản (thuộc tab Hành Tẩu)
                'item' => array('chongwu', '🐾', 'Linh Sủng'),
                'cmds' => array('chongwu'),
            ),
            'nhiemvu' => array(
                'item' => array('nhiemvu', '📜', 'Nhiệm Vụ'),
                'cmds' => array('nhiemvu', 'mytask', 'mytaskinfo', 'task'),
                'sub' => array(
                    array(array('nhiemvu', '📜', 'Nhiệm Vụ'), array('nhiemvu')),
                    array(array('mytask', '🧾', 'Nhiệm Vụ NPC'), array('mytask', 'mytaskinfo', 'task')),
                ),
            ),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_tiendo_groups')) {
    // Nhóm "Tiên Đồ": 'tuthan' ở trang Tu Luyện, còn lại ở Đại Sảnh (thay lưới 21 nút cũ).
    function nav_tiendo_groups(){
        return array(
            'tuthan' => array('🧘 Tu Thân', array(
                array('gocp', '📜', 'Công Pháp'), array('linhcan', '🌱', 'Linh Căn'), array('thechat', '🧎', 'Thể Chất'),
                array('huyetmach', '🩸', 'Huyết Mạch'), array('tianfu', '🌟', 'Thiên Phú'), array('luonden', '🔥', 'Luyện Đan'),
            )),
            // Gộp Thám Hiểm + Khác thành 1 hàng 4 nút (Đại Sảnh còn 3 hàng). Tỏa Yêu Tháp / Vấn Đỉnh Đài / Hầm Mỏ / Sào Thú nằm trong Phó Bản
            'thamhiem' => array('⚔ Thám Hiểm · Tiện Ích', array(
                array('qydt', '🗺', 'Bản Đồ'), array('phoban', '📋', 'Phó Bản'),
                array('duihuan', '🎁', 'Giftcode'), array('index.php', '↪', 'Rời Khỏi'),
            )),
            'giaothuong' => array('💰 Giao Thương', array(
                array('shangdian&canshu=gogoumai', '🛒', 'Cửa Hàng'), array('thiencoccac&tab=wg', '📚', 'Thiên Cơ Các'),
                array('fangshi&fangshi=daoju', '🤝', 'Giao Dịch'), array('quydoi', '💱', 'Quy Đổi'),
            )),
            'ketgiao' => array('🏯 Kết Giao', array(
                array('club', '🏯', 'Tông Môn'), array('im', '👥', 'Hảo Hữu'),
                array('liaotian&ltlx=all', '💬', 'Tán Gẫu'), array('paihang', '🏆', 'Xếp Hạng'),
            )),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_item_html')) {
    // Một nút: <a class="$cls[ on][ is-locked]" href><i>icon</i><b>nhãn</b>[🔒 cấp]</a>
    function nav_item_html($player, $sid, $encode, $item, $cls, $on = false, $dot = false){
        if ($item[0] === 'index.php') {
            $href = 'index.php';
            $lk = array('', '');
        } else {
            $cmd = strtok($item[0], '&');
            $href = '?cmd=' . $encode->encode('cmd=' . $item[0] . '&sid=' . $sid);
            $lk = feature_lock_mark($player, $cmd);
        }
        return '<a class="' . $cls . ($on ? ' on' : '') . $lk[0] . '" href="' . $href . '"><i>' . $item[1] . '</i><b>' . $item[2] . '</b>'
            . ($dot ? '<em class="nav-dot"></em>' : '') . $lk[1] . '</a>';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_tab_for_cmd')) {
    function nav_tab_for_cmd($cmd){
        foreach (nav_tabs_config() as $key => $t) {
            if (in_array($cmd, $t['cmds'], true)) return $key;
        }
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_quest_dot')) {
    // Chấm đỏ tab Nhiệm Vụ: còn nhiệm vụ ngày / tuần / tháng đã xong mà chưa nhận thưởng (1 truy vấn theo khóa chính sid).
    function nav_quest_dot($sid, $dblj){
        // quest_id 'daily_bonus' là dòng đánh dấu đã nhận thưởng ngày (ghi completed = 1, claimed giữ 0) — không phải nhiệm vụ chờ nhận
        $sql = "SELECT 1 FROM player_nhiemvu_daily WHERE sid = ? AND reset_date = ? AND completed = 1 AND claimed = 0 AND quest_id <> 'daily_bonus'"
            . " UNION ALL SELECT 1 FROM player_nhiemvu_weekly WHERE sid = ? AND week_number = ? AND completed = 1 AND claimed = 0"
            . " UNION ALL SELECT 1 FROM player_nhiemvu_monthly WHERE sid = ? AND month = ? AND completed = 1 AND claimed = 0 LIMIT 1";
        try {
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array($sid, date('Y-m-d'), $sid, nhiemvu_current_week_num(), $sid, nhiemvu_current_month()));
            if ($stmt->fetchColumn()) return true;
            // Lộ Trình Cảnh Giới: có mốc đã đạt chưa nhận
            return nhiemvu_realm_has_claimable($sid, nhiemvu_player_level($sid, $dblj), $dblj);
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_quest_tab_dots')) {
    // Chấm đỏ từng tab trong trang Nhiệm Vụ — cùng nguồn với nav_quest_dot() để người chơi tìm được chỗ nhận thưởng.
    function nav_quest_tab_dots($sid, $lv, $dblj){
        $dots = array('daily' => false, 'weekly' => false, 'monthly' => false, 'canhgioi' => false);
        $checks = array(
            'daily'   => array("SELECT 1 FROM player_nhiemvu_daily WHERE sid = ? AND reset_date = ? AND completed = 1 AND claimed = 0 AND quest_id <> 'daily_bonus' LIMIT 1", date('Y-m-d')),
            'weekly'  => array("SELECT 1 FROM player_nhiemvu_weekly WHERE sid = ? AND week_number = ? AND completed = 1 AND claimed = 0 LIMIT 1", nhiemvu_current_week_num()),
            'monthly' => array("SELECT 1 FROM player_nhiemvu_monthly WHERE sid = ? AND month = ? AND completed = 1 AND claimed = 0 LIMIT 1", nhiemvu_current_month()),
        );
        try {
            foreach ($checks as $k => $c) {
                $stmt = $dblj->prepare($c[0]);
                $stmt->execute(array($sid, $c[1]));
                $dots[$k] = (bool)$stmt->fetchColumn();
            }
            $dots['canhgioi'] = nhiemvu_realm_has_claimable($sid, $lv, $dblj);
        } catch (\Exception $e) {
        }
        return $dots;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_bottom_html')) {
    // Thanh dưới cố định 5 tab. $dots = array('tuluyen' => true, ...) — tab có việc cần làm.
    function nav_bottom_html($player, $sid, $encode, $cmd, $dots = array()){
        $cur = nav_tab_for_cmd($cmd);
        $html = '<nav class="tcg-bottom-nav">';
        foreach (nav_tabs_config() as $key => $t) {
            $item = $t['item'];
            if ($key === 'hanhtau') {
                $item[0] = 'gomid&newmid=' . intval($player->nowmid);
            }
            $html .= nav_item_html($player, $sid, $encode, $item, 'bn-tab', $cur === $key, !empty($dots[$key]));
        }
        return $html . '</nav>';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_sub_html')) {
    // Hàng tab con của tab hiện tại (Nhân Vật, Linh Sủng, Nhiệm Vụ); tab không có tab con -> ''.
    function nav_sub_html($player, $sid, $encode, $cmd){
        $tabs = nav_tabs_config();
        $cur = nav_tab_for_cmd($cmd);
        if ($cur === '' || empty($tabs[$cur]['sub'])) return '';
        $sub = $tabs[$cur]['sub'];
        $html = '<nav class="tcg-sub-nav">';
        foreach ($sub as $s) {
            $html .= nav_item_html($player, $sid, $encode, $s[0], 'sn-tab', in_array($cmd, $s[1], true));
        }
        return $html . '</nav>';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nav_tiendo_html')) {
    // Khối "Tiên Đồ": mỗi nhóm một tiêu đề + lưới nút (hiện đầy đủ, chưa mở thì 🔒).
    function nav_tiendo_html($player, $sid, $encode, $keys){
        $groups = nav_tiendo_groups();
        $html = '<div class="tcg-tiendo">';
        foreach ($keys as $k) {
            if (!isset($groups[$k])) continue;
            $html .= '<div class="td-group"><div class="td-title">' . $groups[$k][0] . '</div><div class="td-grid" data-n="' . count($groups[$k][1]) . '">';
            foreach ($groups[$k][1] as $item) {
                $html .= nav_item_html($player, $sid, $encode, $item, 'td-item');
            }
            $html .= '</div></div>';
        }
        return $html . '</div>';
    }
}
