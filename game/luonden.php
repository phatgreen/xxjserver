<?php
// Luyện Đan Tông Môn - Phiên bản mới
require_once __DIR__ . '/../class/function/club_quest.php';
require_once __DIR__ . '/../class/function/club_treasury.php';
require_once __DIR__ . '/../class/function/luonden.php';

$player = \player\getplayer($sid, $dblj);

// Lấy thông tin luyện đan sư
$alchemist = \player\luonden_get_alchemist($sid, $dblj);
$alchRank = intval($alchemist['alch_rank']);
$alchExp = intval($alchemist['alch_exp']);
$totalCraft = intval($alchemist['total_craft']);
$totalToxin = intval($alchemist['total_toxin']);

// Lấy tên cấp bậc
$rankName = \player\luonden_get_rank_name($alchRank);

// Lấy thông tin Đan Đỉnh
$dinhType = 1;
$dinhLevel = 0;
$dinhStmt = $dblj->prepare("SELECT dinh_type, dinh_level FROM player_dan_dinh WHERE sid = ?");
$dinhStmt->execute(array($sid));
$dinhRow = $dinhStmt->fetch(\PDO::FETCH_ASSOC);
if ($dinhRow) {
    $dinhType = intval($dinhRow['dinh_type']);
    $dinhLevel = intval($dinhRow['dinh_level']);
}

// Tên loại lò
$dinhNames = array(1 => 'Sắt', 2 => 'Đồng', 3 => 'Bạc', 4 => 'Vàng', 5 => 'Bạch Kim', 6 => 'Huyền Thạch', 7 => 'Tử Kim', 8 => 'Long Phượng', 9 => 'Thần');
$dinhName = isset($dinhNames[$dinhType]) ? $dinhNames[$dinhType] : 'Sắt';

// Tính bonus từ lò
$dinhBonus = ($dinhType - 1) * 5 + $dinhLevel * 2;

// Lấy thông tin đan độc
$toxinInfo = \player\luonden_get_toxin_level($totalToxin);

// Xử lý luyện đan
$craftMsg = '';
$craftMsgType = '';
$selectedRecipe = isset($recipe_id) ? intval($recipe_id) : 0;

if ($cmd == 'do_craft_luonden' && $selectedRecipe > 0) {
    $result = \player\luonden_craft_v2($sid, $selectedRecipe, $dblj);
    $craftOk = !empty($result['success']);
    $craftMsgType = $craftOk ? 'ok' : 'bad';
    $craftMsg = $result['msg'];
    $player = \player\getplayer($sid, $dblj);
    
    // Cập nhật lại thông tin
    $alchemist = \player\luonden_get_alchemist($sid, $dblj);
    $alchRank = intval($alchemist['alch_rank']);
    $alchExp = intval($alchemist['alch_exp']);
    $totalToxin = intval($alchemist['total_toxin']);
    $rankName = \player\luonden_get_rank_name($alchRank);
    $toxinInfo = \player\luonden_get_toxin_level($totalToxin);

    /* Hook quest: alchemy (chỉ khi thành công) */
    if ($craftOk) {
        \player\club_quest_update($sid, 'alchemy', 1, $dblj);
        \player\club_quest_update($sid, 'alchemy_craft', 1, $dblj);
        \player\club_quest_update($sid, 'alchemy_donate', 1, $dblj);
        
        // Hook cho đan văn cao (>= 6)
        if (isset($result['dan_van']) && intval($result['dan_van']) >= 6) {
            \player\club_quest_update($sid, 'alchemy_high_van', 1, $dblj);
        }
        
        // Hook cho không đan độc (toxin = 0)
        if (isset($result['toxin']) && intval($result['toxin']) == 0) {
            \player\club_quest_update($sid, 'alchemy_no_toxin', 1, $dblj);
        }

        if (function_exists('player\\nhiemvu_update_all')) {
            $nvAmount = isset($result['amount']) ? max(1, intval($result['amount'])) : 1;
            $nvRecipe = \player\luonden_get_recipe($selectedRecipe);
            \player\nhiemvu_update_all($sid, 'alchemy', $nvAmount, $dblj, array(
                'dan_van' => isset($result['dan_van']) ? intval($result['dan_van']) : 0,
                'success_rate' => isset($nvRecipe['base_success_rate']) ? floatval($nvRecipe['base_success_rate']) : 0,
            ));
        }
    }
}

// Xử lý sử dụng đan giải độc
if ($cmd == 'use_detox_pill') {
    $detoxResult = \player\luonden_use_detox_pill($sid, $dblj);
    if ($isAjax) {
        while (ob_get_level()) ob_end_clean();
        echo json_encode($detoxResult);
        exit;
    }
    // Cập nhật lại thông tin
    $alchemist = \player\luonden_get_alchemist($sid, $dblj);
    $totalToxin = intval($alchemist['total_toxin']);
    $toxinInfo = \player\luonden_get_toxin_level($totalToxin);
}

// Lấy danh sách recipes
$recipes = \player\luonden_get_all_recipes();

// Lấy số lượng nguyên liệu trong inventory
$stmt = $dblj->prepare("SELECT djid, djsum FROM playerdaoju WHERE sid = ?");
$stmt->execute(array($sid));
$inventory = array();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $inventory[intval($row['djid'])] = intval($row['djsum']);
}

// Navigation
$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$chetaoCmd = $encode->encode("cmd=chetao&sid=$sid");
$luondenCmd = $encode->encode("cmd=luonden&sid=$sid");
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

// Element info
$lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
$elementColors = array('kim' => '#C0C0C0', 'moc' => '#228B22', 'thuy' => '#1E90FF', 'hoa' => '#FF4500', 'tho' => '#8B4513');
$elementNames = array('kim' => 'Kim', 'moc' => 'Mộc', 'thuy' => 'Thủy', 'hoa' => 'Hỏa', 'tho' => 'Thổ', 'all' => 'Ngũ Hành', 'none' => 'Vô Hành');
$lcColor = isset($elementColors[$lcElement]) ? $elementColors[$lcElement] : '#888';
$lcName = isset($elementNames[$lcElement]) ? $elementNames[$lcElement] : 'Không';

$recipeCount = count($recipes);

// Build recipe cards
$recipeCards = '';
foreach ($recipes as $rid => $recipe) {
    $rLevel = isset($recipe['level_required']) ? intval($recipe['level_required']) : 1;
    $rType = isset($recipe['type']) ? $recipe['type'] : 'yaopin';
    $rResultId = isset($recipe['result_id']) ? intval($recipe['result_id']) : 0;
    $rResultAmt = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;
    $isOpen = ($selectedRecipe == intval($rid));
    
    // Thông tin mới
    $rName = isset($recipe['name']) ? $recipe['name'] : 'Đan dược';
    $rGrade = isset($recipe['grade']) ? intval($recipe['grade']) : 1;
    $rDifficulty = isset($recipe['difficulty']) ? intval($recipe['difficulty']) : 1;
    $rAlchRankReq = isset($recipe['alch_rank_required']) ? intval($recipe['alch_rank_required']) : 1;
    $rToxinBase = isset($recipe['toxin_base']) ? intval($recipe['toxin_base']) : 0;

    // Lấy tên từ config
    $rColor = '#fff';
    $rIcon = '⚗';
    if ($rType === 'daoju') {
        $djCfg = \player\daoju_config_get($rResultId);
        $rColor = '#fde68a';
        $rIcon = '✧';
    } elseif ($rType === 'yaodan') {
        $ydCfg = \player\yaodan_config_get($rResultId);
        $rColor = '#d8b4fe';
        $rIcon = '✦';
    } else {
        $ypCfg = \player\yaopin_config_get($rResultId);
        $rColor = '#86efac';
        $rIcon = '❋';
    }

    // Type badge
    $typeClass = 'normal';
    $typeText = 'Thuốc';
    if ($rType === 'yaodan') {
        $typeClass = 'daily';
        $typeText = 'Đan Dược';
    } elseif ($rType === 'daoju') {
        $typeClass = 'meta';
        $typeText = 'Thẻ Cường Hóa';
    }

    // Grade badge
    $gradeNames = array(1 => 'Nhất', 2 => 'Nhị', 3 => 'Tam', 4 => 'Tứ', 5 => 'Ngũ', 6 => 'Lục', 7 => 'Thất', 8 => 'Bát', 9 => 'Cửu', 10 => 'Tiên', 11 => 'Thần', 12 => 'Đế');
    $gradeName = isset($gradeNames[$rGrade]) ? $gradeNames[$rGrade] : 'Nhất';
    $gradeColor = '#8ee7ff';
    if ($rGrade >= 10) $gradeColor = '#fbbf24';
    elseif ($rGrade >= 7) $gradeColor = '#c084fc';
    elseif ($rGrade >= 4) $gradeColor = '#34d399';

    // Status
    $statusClass = 'active';
    $statusText = 'Cấp ' . $rLevel;
    $statusIcon = '✦';
    
    // Kiểm tra điều kiện
    $canCraft = true;
    $statusMsg = '';
    
    if (intval($player->ulv) < $rLevel) {
        $canCraft = false;
        $statusMsg = "Cần cấp {$rLevel}";
    } elseif ($alchRank < $rAlchRankReq) {
        $canCraft = false;
        $rankReqName = \player\luonden_get_rank_name($rAlchRankReq);
        $statusMsg = "Cần {$rankReqName}";
    }
    
    if ($isOpen) {
        $statusClass = 'ready';
        $statusText = 'Đang chọn';
        $statusIcon = '!';
    } elseif (!$canCraft) {
        $statusClass = 'locked';
        $statusText = $statusMsg;
        $statusIcon = '🔒';
    }

    // Detail
    $detailHtml = '';
    if ($isOpen) {
        $rDesc = '';
        if ($rType === 'daoju') {
            $djCfg = \player\daoju_config_get($rResultId);
            $rDesc = isset($djCfg['djinfo']) ? $djCfg['djinfo'] : '';
        } elseif ($rType === 'yaodan') {
            $ydCfg = \player\yaodan_config_get($rResultId);
            $rDesc = isset($ydCfg['ydinfo']) ? $ydCfg['ydinfo'] : '';
        } else {
            $ypCfg = \player\yaopin_config_get($rResultId);
            $rDesc = isset($ypCfg['ypinfo']) ? $ypCfg['ypinfo'] : '';
        }
        
        $resultText = "Nhận <b>{$rResultAmt}x</b> {$rName}";
        $rateText = \player\luonden_success_text($player, $recipe);
        $matHtml = '';
        $materials = isset($recipe['materials']) ? $recipe['materials'] : array();
        foreach ($materials as $mat) {
            $matId = intval($mat['id']);
            $matAmount = intval($mat['amount']);
            $have = isset($inventory[$matId]) ? $inventory[$matId] : 0;
            $matConfig = \player\daoju_config_get($matId);
            $matName = isset($matConfig['djname']) ? $matConfig['djname'] : '#' . $matId;
            $matClass = ($have >= $matAmount) ? 'event-mat-ok' : 'event-mat-fail';
            $matHtml .= "<span class='event-mat {$matClass}'>{$matName} {$have}/{$matAmount}</span>";
        }
        
        // Thông tin mới
        $difficultyNames = array(1 => 'Dễ', 2 => 'Trung bình', 3 => 'Khó', 4 => 'Rất khó', 5 => 'Cực khó', 6 => 'Siêu khó', 7 => 'Khó tin', 8 => 'Bất khả thi', 9 => 'Vô cực', 10 => 'Huyền thoại');
        $diffName = isset($difficultyNames[$rDifficulty]) ? $difficultyNames[$rDifficulty] : 'Dễ';
        
        $detailHtml = "<div class='event-detail-box'>
            <div class='event-detail-desc'>{$rDesc}</div>
            <div class='event-detail-result'>{$resultText}</div>
            <div class='event-detail-rate'>⚡ Tỷ lệ: {$rateText}</div>
            <div class='event-detail-info'>
                <span>Phẩm: <b style='color:{$gradeColor}'>{$gradeName} phẩm</b></span>
                <span>Độ khó: <b>{$diffName}</b></span>
                <span>Đan độc: <b>{$rToxinBase}</b></span>
            </div>
            <div class='event-detail-mats'>{$matHtml}</div>
        </div>";
    }

    $href = ($isOpen || !$canCraft) ? '#' : '?cmd=' . $encode->encode("cmd=luonden&recipe_id={$rid}&sid=$sid");

    $recipeCards .= <<<HTML
    <a class="event-task-card {$statusClass}" href="{$href}">
        <span class="event-task-icon">{$rIcon}</span>
        <span class="event-task-main">
            <span class="event-task-name" style="color:{$rColor}">{$rName}</span>
            <span class="event-task-meta">
                <span class="event-task-type {$typeClass}">{$typeText}</span>
                <span class="event-task-grade" style="color:{$gradeColor}">{$gradeName} phẩm</span>
                <span class="event-task-status">{$statusText}</span>
            </span>
            {$detailHtml}
        </span>
    </a>
HTML;
}

$taskView = trim($recipeCards) !== '' ? $recipeCards : '<div class="event-empty">Chưa có công thức luyện đan.</div>';

// Craft button
$doCraftCmd = ($selectedRecipe > 0) ? $encode->encode("cmd=do_craft_luonden&recipe_id={$selectedRecipe}&sid=$sid") : '#';
$btnDisabled = ($selectedRecipe > 0) ? '' : ' disabled';

// Message banner - chuyển sang GameAlert
$craftMsgHtml = '';
if ($craftMsg !== '') {
    $alertType = ($craftMsgType === 'ok') ? 'success' : 'error';
    $safeMsg = htmlspecialchars($craftMsg, ENT_QUOTES, 'UTF-8');
    $safeType = htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8');
    $craftMsgHtml = '<script>setTimeout(function(){ GameAlert.show(' . json_encode($safeMsg) . ', ' . json_encode($safeType) . '); }, 300);</script>';
}

// Phản phệ luyện đan - tách riêng thành panel cảnh báo
$detoxCmd = $encode->encode("cmd=use_detox_pill&sid=$sid");
$hasDetox = false;
$detoxStmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = 8");
$detoxStmt->execute(array($sid));
$detoxRow = $detoxStmt->fetch(\PDO::FETCH_ASSOC);
if ($detoxRow && intval($detoxRow['djsum']) > 0) {
    $hasDetox = true;
}
$detoxBtn = $hasDetox 
    ? "<a class='detox-btn' href='?cmd={$detoxCmd}' onclick='return gameConfirm(\"Sử dụng đan giải độc?\",\"?cmd={$detoxCmd}\")'>💊 Giải Độc</a>"
    : "<span class='detox-btn disabled'>Không có đan giải độc</span>";

$backlashColor = '#4ade80';
$backlashBarWidth = min(100, intval($totalToxin / 10)); // 1000 = 100%
$backlashLevelText = $toxinInfo['level_name'];
$backlashEffect = $toxinInfo['effect'];

if ($totalToxin > 100) {
    $backlashColor = '#fca5a5';
    if ($totalToxin > 500) $backlashColor = '#ef4444';
    elseif ($totalToxin > 300) $backlashColor = '#f97316';
}

$backlashPanelHtml = "<div class='backlash-panel'>
    <div class='backlash-header'>
        <span class='backlash-icon'>⚠️</span>
        <span class='backlash-title'>PHẢN PHỆ LUYỆN ĐAN</span>
        <span class='backlash-value' style='color:{$backlashColor}'>{$totalToxin}/1000</span>
    </div>
    <div class='backlash-bar'>
        <div class='backlash-bar-fill' style='width:{$backlashBarWidth}%;background:{$backlashColor}'></div>
    </div>
    <div class='backlash-info'>
        <span>Mức độ: <b style='color:{$backlashColor}'>{$backlashLevelText}</b></span>
        <span>{$backlashEffect}</span>
    </div>
    <div class='backlash-actions'>{$detoxBtn}</div>
</div>";

$html = <<<HTML
<style>
.event-notice{
    padding:12px 14px;border-radius:14px;margin-bottom:12px;font-weight:900;font-size:13px;text-align:center;
}
.event-notice.ok{border:1px solid rgba(34,197,94,.45);background:rgba(20,83,45,.38);color:#bbf7d0}
.event-notice.bad{border:1px solid rgba(239,68,68,.45);background:rgba(127,29,29,.38);color:#fecaca}
.ld-hero-bg{
    position:relative!important;
    display:grid!important;
    grid-template-columns:minmax(0,1fr) 92px!important;
    gap:16px!important;
    align-items:center!important;
    padding:18px!important;
    border:1px solid rgba(125,211,252,.34)!important;
    border-radius:20px!important;
    overflow:hidden!important;
    margin-bottom:12px!important;
}
.ld-hero-bg::before{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    background:url('./images/mission-banner-v1.png') center/cover no-repeat!important;
    opacity:.5!important;
    z-index:0!important;
}
.ld-hero-bg::after{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    z-index:1!important;
}
.ld-hero-bg>*{position:relative!important;z-index:2!important}
.ld-hero-bg .event-kicker{
    color:#8ee7ff!important;font-size:13px!important;font-weight:1000!important;
    letter-spacing:1.5px!important;text-transform:uppercase!important;
    text-shadow:0 0 10px rgba(56,189,248,.58)!important;
}
.ld-hero-bg .event-title{
    margin:7px 0 8px!important;font-size:38px!important;line-height:1!important;
    font-weight:1000!important;letter-spacing:1.5px!important;
    background:linear-gradient(180deg,#fff 0%,#7bd7ff 38%,#c084fc 70%,#fff 100%)!important;
    -webkit-background-clip:text!important;background-clip:text!important;color:transparent!important;
    text-shadow:0 0 18px rgba(114,198,255,.78),0 0 30px rgba(192,132,252,.48)!important;
}
.ld-hero-bg .event-subtitle{
    max-width:520px!important;color:#dbeafe!important;font-size:14px!important;
    line-height:1.5!important;font-weight:800!important;
}
.ld-hero-bg .event-stats{
    display:flex!important;flex-wrap:wrap!important;gap:10px!important;margin-top:13px!important;
}
.ld-hero-bg .event-stats span{
    min-height:34px!important;display:inline-flex!important;align-items:center!important;
    gap:7px!important;padding:6px 12px!important;
    border:1px solid rgba(125,211,252,.30)!important;border-radius:999px!important;
    background:rgba(7,16,45,.48)!important;color:#cfe9ff!important;
    font-size:13px!important;font-weight:900!important;
}
.ld-hero-bg .event-stats b{
    color:#5df26b!important;font-size:18px!important;
    text-shadow:0 0 10px rgba(93,242,107,.45)!important;
}
.ld-hero-bg .event-seal{
    width:82px!important;height:82px!important;
    display:flex!important;align-items:center!important;justify-content:center!important;
    justify-self:end!important;border-radius:50%!important;
    border:1px solid rgba(250,204,21,.44)!important;
    background:radial-gradient(circle,rgba(250,204,21,.22),rgba(147,51,234,.18) 68%,rgba(7,16,45,.54))!important;
    color:#fde68a!important;font-size:38px!important;font-weight:1000!important;
    text-shadow:0 0 14px rgba(250,204,21,.78),0 0 24px rgba(236,72,153,.42)!important;
    box-shadow:0 0 22px rgba(250,204,21,.18),0 0 14px rgba(255,255,255,.08) inset!important;
}
.event-detail-box{
    margin-top:10px;padding:10px;border-radius:12px;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.50);
}
.event-detail-desc{font-size:12px;color:#9fcaff;margin-bottom:8px}
.event-detail-result{font-size:13px;color:#86efac;font-weight:900;margin-bottom:6px}
.event-detail-rate{font-size:12px;color:#fde68a;font-weight:900;margin-bottom:6px}
.event-detail-info{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:6px;font-size:11px;color:#9fcaff}
.event-detail-info b{color:#fff}
.event-detail-mats{display:flex;flex-wrap:wrap;gap:6px}
.event-mat{
    display:inline-flex;align-items:center;gap:4px;
    padding:3px 8px;border-radius:999px;font-size:11px;font-weight:900;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.60);
}
.event-mat-ok{color:#86efac;border-color:rgba(34,197,94,.36)}
.event-mat-fail{color:#fca5a5;border-color:rgba(239,68,68,.36)}
.event-task-grade{
    display:inline-flex;align-items:center;gap:4px;
    padding:2px 8px;border-radius:999px;font-size:10px;font-weight:900;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.60);
}
.event-craft-btn{
    display:block;width:100%;min-height:44px;
	line-height:2.4;
    border:1px solid rgba(253,230,138,.45);
    background:linear-gradient(180deg,#f59e0b,#d97706);
    color:#fff;font-weight:1000;font-size:16px;
    border-radius:12px;cursor:pointer;
    box-shadow:0 8px 18px rgba(0,0,0,.22),0 0 14px rgba(245,158,11,.18) inset;
    transition:all .2s;text-decoration:none;text-align:center;
}
.event-craft-btn:hover{filter:brightness(1.12);transform:translateY(-1px)}
.event-craft-btn:disabled,.event-craft-btn.disabled{
    background:rgba(60,60,80,.5);color:#666;border-color:rgba(255,255,255,.08);
    box-shadow:none;pointer-events:none;
}
.toxin-warning{
    display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;
    padding:10px 14px;border-radius:12px;margin-bottom:12px;
    border:1px solid rgba(239,68,68,.45);background:rgba(127,29,29,.38);
    font-size:12px;font-weight:900;color:#fecaca;
}
.alchemist-info{
    display:grid;grid-template-columns:repeat(3,1fr);gap:8px;
    margin-bottom:12px;padding:12px;border-radius:12px;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.50);
}
.alchemist-info .info-item{
    text-align:center;padding:8px;border-radius:8px;
    background:rgba(7,16,45,.60);border:1px solid rgba(125,211,252,.18);
}
.alchemist-info .info-label{font-size:10px;color:#9fcaff;margin-bottom:4px}
.alchemist-info .info-value{font-size:14px;font-weight:900;color:#fff}
.alchemist-info .info-value.rank{color:#fbbf24}
.alchemist-info .info-value.exp{color:#86efac}

.backlash-panel{
    margin-bottom:12px;padding:12px 14px;
    border:1px solid rgba(251,191,36,.35);border-radius:14px;
    background:linear-gradient(135deg,rgba(120,53,15,.4),rgba(146,64,14,.3));
    backdrop-filter:blur(6px);
}
.backlash-header{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.backlash-icon{font-size:18px}
.backlash-title{font-size:11px;font-weight:1000;color:#fbbf24;letter-spacing:1px;text-transform:uppercase}
.backlash-value{margin-left:auto;font-size:16px;font-weight:1000}
.backlash-bar{height:6px;border-radius:3px;background:rgba(0,0,0,.4);margin-bottom:8px;overflow:hidden}
.backlash-bar-fill{height:100%;border-radius:3px;transition:width .3s}
.backlash-info{font-size:11px;color:#d4a5a5;display:flex;flex-direction:column;gap:4px}
.backlash-info b{font-weight:900}
.backlash-actions{margin-top:10px;text-align:right}
.detox-btn{
    display:inline-block;padding:6px 14px;font-size:12px;font-weight:900;text-decoration:none;
    border-radius:8px;color:#000;
    background:linear-gradient(135deg,#4ade80,#22c55e);
    border:1px solid rgba(74,222,128,.4);
}
.detox-btn:hover{filter:brightness(1.15)}
.detox-btn.disabled{
    opacity:.5;pointer-events:none;
    background:linear-gradient(135deg,#6b7280,#4b5563);color:#9ca3af;
    border-color:rgba(107,114,128,.3);
}
@media(max-width:760px){
    .ld-hero-bg{grid-template-columns:1fr!important;padding:16px!important}
    .ld-hero-bg .event-seal{display:none!important}
    .ld-hero-bg .event-title{font-size:30px!important}
}
</style>

<div class="event-page">
{$craftMsgHtml}

<div class="ld-hero-bg">
    <div class="event-hero-text">
        <div class="event-kicker">Hệ Thống Luyện Đan Tông Môn</div>
        <div class="event-title">Luyện Đan</div>
        <div class="event-subtitle">Pha chế đan dược, thuốc bôi và thẻ cường hóa từ nguyên liệu quý hiếm.</div>
        <div class="event-stats">
            <span>🔥 <b>{$recipeCount}</b> Công thức</span>
            <span>⚗ <b>{$lcName}</b> Hệ</span>
            <span>👨‍🔬 <b>{$rankName}</b></span>
            <span>🔥 <b>{$dinhName}</b> Lv{$dinhLevel}</span>
        </div>
    </div>
    <div class="event-seal">🔥</div>
</div>

<div class="alchemist-info">
    <div class="info-item">
        <div class="info-label">Cấp Sư</div>
        <div class="info-value rank">{$rankName}</div>
    </div>
    <div class="info-item">
        <div class="info-label">Kinh Nghiệm</div>
        <div class="info-value exp">{$alchExp}</div>
    </div>
    <div class="info-item">
        <div class="info-label">Luyện Thành Công</div>
        <div class="info-value">{$totalCraft}</div>
    </div>
</div>

{$backlashPanelHtml}

<div class="event-divider"><span>◇</span></div>

<div class="event-task-list">
{$taskView}
</div>

<a class="event-craft-btn{$btnDisabled}" href="#" onclick="return craftConfirm();">🔥 LUYỆN ĐAN</a>

<script>
function craftConfirm() {
    if (typeof GameAlert === 'undefined') {
        return confirm('Xác nhận luyện đan?');
    }
    GameAlert.confirm('Xác nhận luyện đan?', function() {
        window.location.href = '?cmd={$doCraftCmd}';
    });
    return false;
}
</script>

<div class="event-bottom">
    <a class="event-btn ghost" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
    <a class="event-btn primary" href="game.php?cmd={$gonowmid}">Đại Sảnh</a>
</div>
</div>
HTML;

echo $html;
?>
