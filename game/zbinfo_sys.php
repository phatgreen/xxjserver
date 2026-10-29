<?php
/**
 * Trang thông tin trang bị hệ thống
 * Giao diện đồng bộ phong cách Tu Chân Giới.
 */

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid={$player->nowmid}&sid={$sid}");

$zbid = isset($zbid) ? intval($zbid) : 0;
$zhuangbei = \player\getzbkzb($zbid, $dblj);

if (!function_exists('zbinfo_sys_html')) {
    function zbinfo_sys_html($text)
    {
        return htmlspecialchars(strval($text), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('zbinfo_sys_num')) {
    function zbinfo_sys_num($num)
    {
        return intval($num);
    }
}

if (!$zhuangbei) {
    $html = <<<HTML
<div class="zbinfo-sys-page">
    <div class="zbinfo-sys-panel">
        <div class="zbinfo-sys-empty">Không tìm thấy thông tin trang bị.</div>
        <div class="zbinfo-sys-bottom">
            <a href="#" onclick="javascript:history.back(-1);return false;">Trở Về</a>
            <a href="game.php?cmd=$gonowmid">Đại Sảnh</a>
        </div>
    </div>
</div>
HTML;
    echo $html;
    return;
}

$tools = \player\zhuangbei_tool_names();
$tools[0] = 'Vô hạn';

$toolIndex = isset($zhuangbei->tool) ? intval($zhuangbei->tool) : 0;
$tool = isset($tools[$toolIndex]) ? $tools[$toolIndex] : 'Không rõ';

$zbName = isset($zhuangbei->zbname) ? $zhuangbei->zbname : ('Trang bị #' . $zbid);
$zbColor = isset($zhuangbei->zbys) && trim($zhuangbei->zbys) != '' ? trim($zhuangbei->zbys) : '#38d5ff';

// Chỉ nhận mã màu dạng #rgb / #rrggbb để tránh phá HTML.
if (!preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $zbColor)) {
    $zbColor = '#38d5ff';
}

$zbgj = isset($zhuangbei->zbgj) ? zbinfo_sys_num($zhuangbei->zbgj) : 0;
$zbfy = isset($zhuangbei->zbfy) ? zbinfo_sys_num($zhuangbei->zbfy) : 0;
$zbhp = isset($zhuangbei->zbhp) ? zbinfo_sys_num($zhuangbei->zbhp) : 0;
$zbbj = isset($zhuangbei->zbbj) ? zbinfo_sys_num($zhuangbei->zbbj) : 0;
$zbxx = isset($zhuangbei->zbxx) ? zbinfo_sys_num($zhuangbei->zbxx) : 0;
$tuposjs = isset($zhuangbei->tuposjs) ? zbinfo_sys_num($zhuangbei->tuposjs) : 0;
$tupoexp = isset($zhuangbei->tupoexp) ? zbinfo_sys_num($zhuangbei->tupoexp) : 0;
$zbinfo = isset($zhuangbei->zbinfo) && trim($zhuangbei->zbinfo) != '' ? $zhuangbei->zbinfo : 'Chưa có mô tả cho trang bị này.';

$zbNameHtml = zbinfo_sys_html($zbName);
$toolHtml = zbinfo_sys_html($tool);
$zbinfoHtml = nl2br(zbinfo_sys_html($zbinfo));

// Ảnh trang bị + phẩm chất làm trọng tâm (giống trang chi tiết trang bị đang có)
$zbsRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
$zbsRaColor = \player\zhuangbei_rarity_color($zbsRa);
$zbsRaName = zbinfo_sys_html(\player\zhuangbei_rarity_name($zbsRa));
$zbsImg = \player\zhuangbei_image_path((object) array('zbid' => $zbid), \player\zhuangbei_slot_icon($toolIndex));

$html = <<<HTML
<div class="zbinfo-sys-page">
    <div class="zbinfo-sys-panel">
        <div class="zbinfo-sys-hero">
            <div class="zb-art" style="--ra:$zbsRaColor"><img src="$zbsImg" alt=""><span class="zb-art-ra">$zbsRaName</span></div>
            <div class="zbinfo-sys-kicker">THÔNG TIN TRANG BỊ</div>
            <div class="zbinfo-sys-title" style="--zb-color:$zbColor;">
                <span>【 $zbNameHtml 】</span>
            </div>
            <div class="zbinfo-sys-sub">
                <span>Loại trang bị</span>
                <b>$toolHtml</b>
            </div>
        </div>

        <div class="zbinfo-sys-stat-grid">
            <div class="zbinfo-sys-stat atk"><span>Công kích</span><b>$zbgj</b></div>
            <div class="zbinfo-sys-stat def"><span>Phòng ngự</span><b>$zbfy</b></div>
            <div class="zbinfo-sys-stat hp"><span>Khí huyết</span><b>$zbhp</b></div>
            <div class="zbinfo-sys-stat crit"><span>Bạo kích</span><b>$zbbj%</b></div>
            <div class="zbinfo-sys-stat blood"><span>Hút máu</span><b>$zbxx%</b></div>
            <div class="zbinfo-sys-stat break"><span>Tỉ lệ đột phá</span><b>$tuposjs%</b></div>
            <div class="zbinfo-sys-stat speed"><span>Tốc độ tu luyện</span><b>$tupoexp%</b></div>
            <div class="zbinfo-sys-stat type"><span>Thuộc loại</span><b>$toolHtml</b></div>
        </div>

        <div class="zbinfo-sys-desc-box">
            <div class="zbinfo-sys-section-title">Thông tin</div>
            <div class="zbinfo-sys-desc">$zbinfoHtml</div>
        </div>

        <div class="zbinfo-sys-bottom">
            <a class="zbinfo-sys-back" href="#" onclick="javascript:history.back(-1);return false;">Trở Về</a>
            <a class="zbinfo-sys-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
        </div>
    </div>
</div>
HTML;

echo $html;
?>
