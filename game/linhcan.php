<?php
// LINH CAN — VÒNG NGŨ HÀNH (spec docs/superpowers/specs/2026-10-10-linhcan-vong-ngu-hanh-design.md)
// Logic: class/function/linhcan.php · Config: class/config/linhcan_element_config.json
// CSS: css/mystyle.css (khối .lc-*) · Ảnh: images/linhcan/vong-ngu-hanh.jpg
// $sid/$dblj/$encode + biến $el/$canshu do game.php parse_str (:114) — trang KHÔNG đọc $_GET.

$ztcmd    = $encode->encode("cmd=zhuangtai&sid=$sid");
$questcmd = $encode->encode("cmd=linhcan_quest&sid=$sid");
$bosscmd  = $encode->encode("cmd=linhcan_boss&sid=$sid");
$lcImgVer = '2026101101'; // bump khi đổi ảnh vòng

$lcnodes = \player\linhcan_node_rows($sid, $dblj);
$lcdefs  = \player\linhcan_element_defs();
$lccfgAll = \player\linhcan_element_config_all();
$lcStatLbl = isset($lccfgAll['stat_labels']) ? $lccfgAll['stat_labels'] : array();

// Tổng Lv + bậc cao nhất (quyết định fx ladder) — tính lại sau mỗi hành động.
$lcscale = function ($nodes) {
    $total = 0; $maxStage = 0;
    foreach ($nodes as $nd) {
        $total += intval($nd['level']);
        if (intval($nd['stage']) > $maxStage) { $maxStage = intval($nd['stage']); }
    }
    return array('total' => $total, 'max_stage' => $maxStage);
};

$lcs  = $lcscale($lcnodes);
$lctotal = $lcs['total']; $lcmaxStage = $lcs['max_stage'];
$lctitle = \player\linhcan_title($lcnodes);
$lcdom   = \player\linhcan_dominant($lcnodes);
$lcms    = \player\linhcan_milestone($lctotal);
$lcColorDom = $lcdom !== '' ? $lcdefs[$lcdom]['color'] : '#22d3ee';

// Hành đang xem: ?el=<hanh>, mặc định = hành chủ đạo
$lcview = isset($el) ? strtolower(trim($el)) : '';
if (!isset($lcdefs[$lcview])) { $lcview = $lcdom !== '' ? $lcdom : 'kim'; }
$ndv  = $lcnodes[$lcview];
$defv = $lcdefs[$lcview];
$capv = \player\linhcan_stage_cap(intval($ndv['stage']));

// ---- hành động (luyện / đột phá) ----
$lcmsg = ''; $lcmsgBad = false;
$lccanshu = isset($canshu) ? $canshu : '';
if ($lccanshu === 'train' || $lccanshu === 'break') {
    if ($lccanshu === 'train') {
        $lcret = \player\linhcan_train($sid, $dblj, $lcview);
    } else {
        $lcret = \player\linhcan_break($sid, $dblj, $lcview);
    }
    $lcmsg = $lcret['msg'];
    $lcmsgBad = !$lcret['ok'];
    $lcnodes = \player\linhcan_node_rows($sid, $dblj);
    $lcs = $lcscale($lcnodes);
    $lctotal = $lcs['total']; $lcmaxStage = $lcs['max_stage'];
    $lctitle = \player\linhcan_title($lcnodes);
    $lcdom   = \player\linhcan_dominant($lcnodes);
    $lcms    = \player\linhcan_milestone($lctotal);
    $ndv  = $lcnodes[$lcview];
    $capv = \player\linhcan_stage_cap(intval($ndv['stage']));
    \player\linhcan_calc_bonuses($sid, $dblj); // cache player vừa bị node_set clear
}

$lclink = function ($elk, $act = '') use ($encode, $sid) {
    $q = 'cmd=linhcan&sid=' . $sid . '&el=' . $elk;
    if ($act !== '') { $q .= '&canshu=' . $act; }
    return '?cmd=' . $encode->encode($q);
};

// ---- rail 5 chip bậc hành ----
$lcchips = '';
foreach ($lcdefs as $elk => $defc) {
    $ndc = $lcnodes[$elk];
    $cls = 'lc-stage-chip';
    if ($elk === $lcview) $cls .= ' is-view';
    if (intval($ndc['stage']) > 0) $cls .= ' is-done';
    if (intval($ndc['level']) === 0 && intval($ndc['stage']) === 0) $cls .= ' is-lock';
    if ($elk === $lcdom) $cls .= ' is-cur';
    $lcchips .= '<a class="' . $cls . '" href="' . $lclink($elk) . '">'
        . '<span class="lc-chip-icon">' . $defc['icon'] . '</span>'
        . '<span class="lc-chip-name">' . $defc['name'] . '</span>'
        . '<span class="lc-chip-stage">bậc ' . intval($ndc['stage']) . '</span></a>';
}

// ---- vòng 5 ngọc ----
$lcgems = '';
foreach ($lcdefs as $elk => $defg) {
    $ndg = $lcnodes[$elk];
    $capg = \player\linhcan_stage_cap(intval($ndg['stage']));
    $glow = $capg > 0 ? intval(round(min(1, intval($ndg['level']) / $capg) * 16)) : 0;
    $cls = 'lc-gem slot-' . $elk;
    if ($elk === $lcview) $cls .= ' is-view';
    if (intval($ndg['level']) === 0 && intval($ndg['stage']) === 0) $cls .= ' is-lock';
    $lcgems .= '<a class="' . $cls . '" style="--glow:' . $glow . 'px;'
        . '--gem:' . $defg['color'] . '" href="' . $lclink($elk) . '" title="' . $defg['name'] . '">'
        . '<span class="lc-gem-icon">' . $defg['icon'] . '</span>'
        . '<span class="lc-gem-lv">Lv' . intval($ndg['level']) . '</span></a>';
}
$lcVoCuc = ($lctitle['id'] === 'vo_cuc') ? ' lc-core-vo' : '';
$lccore = '<div class="lc-core' . $lcVoCuc . '" style="--lc-color:' . $lctitle['color'] . '">'
    . '<span class="lc-core-icon">' . $lctitle['icon'] . '</span>'
    . '<span class="lc-core-name">' . $lctitle['name'] . '</span></div>';

// ---- viewline + forge ----
$lcskillView = \player\linhcan_skill_for_element($lcview);
$lcStageV = intval($ndv['stage']);
$lcStageDef = \player\linhcan_stage_def($lcStageV);
$lcSkillTxt = ($lcskillView && $lcStageV >= intval($lcskillView['unlock_stage']))
    ? $lcskillView['icon'] . ' ' . $lcskillView['name']
    : '🔒 kỹ năng cần ' . \player\linhcan_stage_def(2)['name'];
$lcviewline = $defv['icon'] . ' ' . $defv['name'] . ' · bậc ' . $lcStageV
    . ' (' . $lcStageDef['name'] . ') — Lv ' . intval($ndv['level'])
    . '/' . $capv . ' — ' . $lcSkillTxt;

$lcbtnTrain = '';
if (intval($ndv['level']) >= $capv) {
    $lcbtnTrain = '<span class="lc-btn lc-btn-off">ĐÃ CHẠM TRẦN Lv ' . $capv . '</span>';
} else {
    $costv = \player\linhcan_train_cost(intval($ndv['level']));
    $havev = \player\getplayerdaoju($sid, \player\linhcan_da_id($lcview), $dblj);
    $haveSum = $havev ? intval($havev->djsum) : 0;
    $lcbtnTrain = '<a class="lc-btn lc-btn-train" href="' . $lclink($lcview, 'train') . '">LUYỆN '
        . (function_exists('mb_strtoupper') ? mb_strtoupper($defv['name'], 'UTF-8') : $defv['name'])
        . ' <small>' . $costv['stone'] . ' đá (có ' . $haveSum
        . ') · ' . number_format($costv['linh_thach'], 0, ',', '.') . ' LT</small></a>';
}
$lcReqs = \player\linhcan_break_requirements($sid, $dblj, $lcview);
$lcbtnBreak = '';
if ($lcStageV >= \player\linhcan_stage_max()) {
    $lcbtnBreak = '<span class="lc-btn lc-btn-off">' . \player\linhcan_stage_def(4)['name'] . ' — tối đa</span>';
} elseif ($lcReqs['ok']) {
    $lcbtnBreak = '<a class="lc-btn lc-btn-break" href="' . $lclink($lcview, 'break') . '">✦ ĐỘT PHÁ LÊN '
        . \player\linhcan_stage_def(intval($lcReqs['to_stage']))['name'] . '</a>';
} else {
    $lcBossUrl = '?cmd=' . $encode->encode("cmd=linhcan_boss&sid=$sid&el=$lcview");
    $lcbtnBreak = '<a class="lc-btn lc-btn-boss" href="' . $lcBossUrl . '">⚔ Boss đột phá ' . $defv['name'] . '</a>';
}

// ---- cột trái: thuộc tính kích hoạt ----
$lcsrowLeft = '';
foreach ($lcdefs as $elk => $defl) {
    $ndl = $lcnodes[$elk];
    $contrib = array();
    foreach ($defl['per_lv'] as $statL => $perL) {
        $lbl = isset($lcStatLbl[$statL]) ? $lcStatLbl[$statL] : $statL;
        $valL = floatval($perL) * intval($ndl['level']);
        if (round($valL) == 0 && abs($valL) < 1) { $txt = number_format($valL, 2, ',', '.'); }
        else { $txt = number_format(round($valL), 0, ',', '.'); }
        if (intval(round($valL * 100)) !== 0) { $contrib[] = $lbl . ' +' . $txt; }
    }
    $lcsrowLeft .= '<div class="lc-attr-row' . ($elk === $lcdom ? ' is-dom' : '') . '">'
        . $defl['icon'] . ' <b>' . $defl['name'] . '</b> Lv ' . intval($ndl['level'])
        . ' · ' . ($contrib ? implode(', ', $contrib) : 'chưa đóng góp') . '</div>';
}
$lcmsLine = '<div class="lc-ms">Cộng hưởng tổng Lv ' . $lctotal . ': <b>+'
    . rtrim(rtrim(number_format($lcms['percent'], 2, ',', '.'), '0'), ',') . '% ' . $lcms['name'] . '</b>'
    . ($lcms['next_total'] > 0 ? ' — mốc kế: Lv ' . $lcms['next_total'] . ' (+' . $lcms['next_percent'] . '% ' . $lcms['next_name'] . ')' : ' — đã đạt đỉnh')
    . '</div>';

// ---- cột phải: checklist đột phá ----
$lcsrowRight = '';
foreach ($lcReqs['rows'] as $req) {
    $mark = $req['ok'] ? "<span class='lc-req ok'>✅ Đủ</span>" : "<span class='lc-req bad'>❌ Thiếu</span>";
    $lcsrowRight .= '<div class="lc-reqs-row">' . $mark . ' ' . $req['label']
        . ' <span class="lc-reqs-num">' . number_format($req['cur'], 0, ',', '.')
        . '/' . number_format($req['need'], 0, ',', '.') . '</span></div>';
}

// ---- the card ky nang (mo khoa theo bac cua HANH NAY, khong phai ulv) ----
$lcSkillCard = 'Hành này chưa có kỹ năng ngũ hành.';
if ($lcskillView) {
    $lcGateOpen = $lcStageV >= intval($lcskillView['unlock_stage']);
    $lcGateStage = \player\linhcan_stage_def(intval($lcskillView['unlock_stage']));
    $lcSkillCard = $lcskillView['icon'] . ' <b>' . $lcskillView['name'] . '</b> — '
        . $lcskillView['description'] . ' — <span class="lc-skillgate">'
        . ($lcGateOpen
            ? 'đã mở · hồi ' . intval($lcskillView['cooldown_minutes']) . ' phút · tốn ' . intval($lcskillView['mp_cost_percent']) . '% khí huyết'
            : '🔒 cần ' . ($lcGateStage ? $lcGateStage['name'] : 'bậc ' . intval($lcskillView['unlock_stage'])))
        . '</span>';
}

$html = '<div class="lc-wrap lc-fx' . $lcmaxStage . '" style="--lc-tier:' . $lcmaxStage . ';--lc-color:' . $lcColorDom . '">';
if ($lcmsg !== '') {
    $html .= '<div class="lc-notice' . ($lcmsgBad ? ' bad' : '') . '">' . $lcmsg . '</div>';
}
// Tab bar đặt đầu trang (giống các trang khác; 3 trang Linh Căn cùng vị trí). Bỏ tab Nhân Vật — đã có ở thanh trên.
$html .= '<nav class="lc-tabbar">'
    . '<a class="on" href="' . $lclink($lcview) . '"><i>☯</i><b>Luyện Căn</b></a>'
    . '<a href="?cmd=' . $questcmd . '"><i>📜</i><b>Nhiệm Vụ</b></a>'
    . '<a href="?cmd=' . $bosscmd . '"><i>👹</i><b>Boss Ngũ Hành</b></a></nav>';
$html .= '<div class="lc-card lc-card-ring lc-compact">';
$html .= '<div class="lc-head"><span class="lc-title">LINH CĂN</span>'
    . '<span class="lc-badge" style="--lc-color:' . $lctitle['color'] . '">' . $lctitle['icon'] . ' '
    . $lctitle['name'] . ' · tổng Lv ' . $lctotal . '</span></div>';
$html .= '<div class="lc-rail">' . $lcchips . '</div>';
$html .= '<div class="lc-viewline">' . $lcviewline . '</div>';
// Khung giữa: vòng Ngũ Hành làm trung tâm, thuộc tính trái / đột phá phải (giống trang Nhân Vật)
$html .= '<div class="lc-main">'
    . '<div class="lc-col lc-col-left"><div class="lc-col-title">Thuộc Tính Kích Hoạt</div>' . $lcsrowLeft . $lcmsLine . '</div>'
    . '<div class="lc-forge-wrap"><div class="lc-stage">'
    . '<img class="lc-ring-img" src="images/linhcan/vong-ngu-hanh.jpg?v=' . $lcImgVer . '" alt="Vòng Ngũ Hành">'
    . $lcgems . $lccore . '</div>'
    . '<div class="lc-forge">' . $lcbtnTrain . $lcbtnBreak . '</div></div>'
    . '<div class="lc-col lc-col-right"><div class="lc-col-title">Đột Phá ' . $defv['icon'] . ' ' . $defv['name'] . '</div>'
    . '<div class="lc-reqs">' . $lcsrowRight . '</div>'
    . '<div class="lc-skillcard">' . $lcSkillCard . '</div></div>'
    . '</div>';
$html .= '</div>'; // lc-card-ring
$html .= '</div>'; // lc-wrap
echo $html;
