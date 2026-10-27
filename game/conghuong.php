<?php
/**
 * conghuong.php — Cộng Hưởng (Tàng Kinh Các): tiến độ sưu tầm võ công + công pháp theo nhóm hệ.
 * Logic: class/function/conghuong.php · Config: class/config/conghuong_config.json
 */
require_once __DIR__ . '/wugong_utils.php';

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=" . $player->nowmid . "&sid=" . $sid);
$chWgCmd = $encode->encode("cmd=wgxl&sid=" . $sid);
$chCpCmd = $encode->encode("cmd=gocp&sid=" . $sid);

$chRes = \player\conghuong_progress($sid, $dblj);
$chCfg = \player\conghuong_cfg();
$chWgNeed = isset($chCfg['vien_man']['wugong_lv']) ? intval($chCfg['vien_man']['wugong_lv']) : 10;

// Tổng thưởng đang nhận
$chSum = '';
foreach ($chRes['bonus'] as $stat => $pct) {
    $chSum .= '<div class="ch-sum-item"><span>' . \player\conghuong_stat_name($stat) . '</span><b>+' . number_format($pct, 1) . '%</b></div>';
}

$chGroups = '';
foreach ($chRes['groups'] as $g) {
    $statName = \player\conghuong_stat_name($g['stat']);
    $tiers = '';
    foreach ($g['tiers'] as $t) {
        $label = $t['all'] ? 'Toàn bộ' : $t['need'] . ' bộ';
        $tiers .= '<span class="ch-tier' . ($t['reached'] ? ' on' : '') . '">' . ($t['reached'] ? '✓ ' : '') . $label
            . ' · +' . number_format($t['pct'], 1) . '% ' . $statName . '</span>';
    }
    $items = '';
    foreach ($g['members'] as $m) {
        $cls = $m['done'] ? ' done' : ($m['owned'] ? ' owned' : '');
        $state = $m['done'] ? 'Viên Mãn' : ($m['owned'] ? 'Cấp ' . $m['lv'] . '/' . $m['need'] : 'Chưa học');
        $items .= '<div class="ch-item' . $cls . '">'
            . '<em>' . ($m['type'] == 'wg' ? 'Võ công' : 'Công pháp') . '</em>'
            . '<b style="color:' . wg_safe_text($m['color']) . '">' . wg_safe_text($m['name']) . '</b>'
            . '<small>' . $state . '</small></div>';
    }
    $pctDone = $g['total'] > 0 ? round($g['done'] * 100 / $g['total']) : 0;
    // Thu gọn: bấm tiêu đề nhóm mới xổ danh sách bộ (trang đỡ dài).
    $chGroups .= '<details class="ch-group"><summary>'
        . '<div class="ch-group-head"><span class="ch-group-icon">' . $g['icon'] . '</span>'
        . '<div class="ch-group-title"><b>' . wg_safe_text($g['name']) . '</b><small>Viên Mãn ' . $g['done'] . '/' . $g['total']
        . ' · đang nhận +' . number_format($g['pct'], 1) . '% ' . $statName . '</small></div><span class="ch-toggle">▾</span></div>'
        . '<div class="ch-bar"><i style="width:' . $pctDone . '%"></i></div>'
        . '<div class="ch-tiers">' . $tiers . '</div></summary>'
        . '<div class="ch-items">' . $items . '</div></details>';
}

echo <<<HTML
<div class="ch-page">
    <div class="ch-hero">
        <div class="ch-kicker">Tàng Kinh Các</div>
        <h1>Cộng Hưởng</h1>
        <p>Sưu tầm võ công và công pháp cùng hệ. Mỗi bộ luyện đến <b>Viên Mãn</b> (võ công cấp {$chWgNeed}, công pháp cấp tối đa) đều được tính — kể cả bộ không trang bị. Đạt mốc số bộ trong nhóm sẽ nhận chỉ số vĩnh viễn.</p>
    </div>
    <div class="ch-tabs">
        <a href="?cmd={$chWgCmd}">Võ Công</a>
        <a href="?cmd={$chCpCmd}">Công Pháp</a>
        <a class="active" href="javascript:void(0)">Cộng Hưởng</a>
    </div>
    <div class="ch-sum">{$chSum}</div>
    {$chGroups}
    <div class="ch-bottom"><a href="?cmd={$gonowmid}">Trở về trò chơi</a></div>
</div>
HTML;
