<?php
// Tẩy Linh Đan (dj 111) — trang chọn hành để rửa. Task 7; Task 10/11 sẽ sơn lại CSS.
// Boot theo khuôn game/linhcan_quest.php:1-4 (router game.php:111 decode + :114 parse_str
// biến thành TOAN CUC, KHONG doc $_GET).

$player = \player\getplayer($sid, $dblj);

$notice = '';
$sel = isset($el) ? strtolower(trim($el)) : '';
if ($sel !== '') {
    $ret = \player\linhcan_wash($sid, $dblj, $sel);
    $notice = $ret['ok']
        ? "<font color='#4ade80'>" . $ret['msg'] . "</font>"
        : "<font color='#f87171'>" . $ret['msg'] . "</font>";
}
$pill = \player\getplayerdaoju($sid, 111, $dblj);
$pillHave = $pill ? intval($pill->djsum) : 0;
$nodesW = \player\linhcan_node_rows($sid, $dblj);
$html = "<div class='lcw-wrap'>";
$html .= "<div class='lcw-head'>Tẩy Linh Đan <font color='#a78bfa'>🌀</font> — còn <b>{$pillHave}</b> viên</div>";
$html .= $notice ? "<div class='lcw-notice'>{$notice}</div>" : '';
$html .= "<div class='lcw-list'>";
foreach ($nodesW as $el => $nd) {
    $def = \player\linhcan_element_def($el);
    $costW = \player\linhcan_train_cost_sum(0, intval($nd['level']));
    $backS = intval(floor($costW['stone'] * 0.8));
    $backL = intval($costW['linh_thach']);
    $canW = ($pillHave >= 1 && intval($nd['level']) > 0);
    $nameW = "<font color='{$def['color']}'>{$def['icon']} {$def['name']}</font>";
    if ($canW) {
        $lnk = $encode->encode("cmd=linhcan_wash&sid=$sid&el=$el");
        $html .= "<div class='lcw-row'>{$nameW} · Lv " . intval($nd['level']) . " · bậc " . intval($nd['stage'])
               . " — hoàn {$backS} đá + {$backL} LT <a href='?cmd=$lnk'>Tẩy</a></div>";
    } else {
        $html .= "<div class='lcw-row lcw-off'>{$nameW} · Lv " . intval($nd['level']) . " — "
               . (intval($nd['level']) <= 0 ? 'chưa luyện' : 'hết đan') . "</div>";
    }
}
$html .= "</div>";
$html .= "<div class='lcw-note'>Lưu ý: tẩy chỉ hoàn nguyên liệu LUYỆN (80% đá, 100% linh thạch); Bình Anh và linh khí đã nạp khi đột phá KHÔNG hoàn lại.</div>";
$backW = $encode->encode("cmd=linhcan&sid=$sid");
$html .= "<a class='lcw-back' href='?cmd=$backW'>← Về Vòng Ngũ Hành</a>";
$html .= "</div>";
echo $html;
