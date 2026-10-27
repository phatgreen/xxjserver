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

$chTiles = '';
$chPopups = '';
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
    // Lưới ô nhỏ (chỉ tên + tiến độ); bấm ô mở popup chi tiết mốc + danh sách bộ.
    $chKey = preg_replace('/[^a-z_]/', '', $g['key']);
    $chTiles .= '<a class="ch-tile' . ($g['pct'] > 0 ? ' on' : '') . '" href="javascript:void(0)" onclick="chOpen(\'' . $chKey . '\')">'
        . '<span class="ch-tile-icon">' . $g['icon'] . '</span>'
        . '<b>' . wg_safe_text($g['name']) . '</b>'
        . '<small>' . $g['done'] . '/' . $g['total'] . ' Viên Mãn</small>'
        . '<span class="ch-bar"><i style="width:' . $pctDone . '%"></i></span>'
        . '<em>+' . number_format($g['pct'], 1) . '% ' . $statName . '</em></a>';
    $chPopups .= '<div class="ch-pop" id="ch-pop-' . $chKey . '" onclick="if(event.target===this)chClose(this)">'
        . '<div class="ch-pop-box"><a class="ch-pop-x" href="javascript:void(0)" onclick="chClose(this.parentNode.parentNode)">✕</a>'
        . '<div class="ch-group-head"><span class="ch-group-icon">' . $g['icon'] . '</span>'
        . '<div class="ch-group-title"><b>' . wg_safe_text($g['name']) . '</b><small>Viên Mãn ' . $g['done'] . '/' . $g['total']
        . ' · đang nhận +' . number_format($g['pct'], 1) . '% ' . $statName . '</small></div></div>'
        . '<div class="ch-bar"><i style="width:' . $pctDone . '%"></i></div>'
        . '<div class="ch-tiers">' . $tiers . '</div>'
        . '<div class="ch-items">' . $items . '</div></div></div>';
}

echo <<<HTML
<div class="ch-page">
    <div class="ch-hero">
        <div class="ch-kicker">Tàng Kinh Các</div>
        <h1>Cộng Hưởng</h1>
        <p>Luyện võ công cấp {$chWgNeed} / công pháp cấp tối đa = <b>Viên Mãn</b> (không cần trang bị). Đủ mốc trong nhóm nhận chỉ số vĩnh viễn. Bấm từng nhóm để xem chi tiết.</p>
    </div>
    <div class="ch-tabs">
        <a href="?cmd={$chWgCmd}">Võ Công</a>
        <a href="?cmd={$chCpCmd}">Công Pháp</a>
        <a class="active" href="javascript:void(0)">Cộng Hưởng</a>
    </div>
    <div class="ch-sum">{$chSum}</div>
    <div class="ch-grid">{$chTiles}</div>
    {$chPopups}
    <div class="ch-bottom"><a href="?cmd={$gonowmid}">Trở về trò chơi</a></div>
</div>
<script>
function chOpen(k){var e=document.getElementById('ch-pop-'+k);if(e)e.className='ch-pop show';}
function chClose(e){if(e)e.className='ch-pop';}
</script>
HTML;
