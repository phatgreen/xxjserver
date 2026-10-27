<?php
// Chế Tạo - Crafting System

$player = \player\getplayer($sid, $dblj);

// Xử lý chế tạo
$craftMsg = '';
$craftMsgType = '';
$selectedRecipe = isset($recipe_id) ? intval($recipe_id) : 0;

if ($cmd == 'do_craft_chetao' && $selectedRecipe > 0) {
    $result = \player\chetao_craft($sid, $selectedRecipe, $dblj);
    $craftOk = !empty($result['success']);
    $craftMsgType = $craftOk ? 'ok' : 'bad';
    $craftMsg = $result['msg'];
    $player = \player\getplayer($sid, $dblj);

    if ($craftOk && function_exists('player\\nhiemvu_update_all')) {
        \player\nhiemvu_update_all($sid, 'craft', 1, $dblj, array(
            'rarity' => isset($result['rarity']) ? intval($result['rarity']) : 0,
            'slot'   => isset($result['zbtool']) ? intval($result['zbtool']) : 0,
        ));
    } elseif (!$craftOk && function_exists('player\\nhiemvu_update_all')) {
        // That bai thi reset chuoi "che tao lien tiep" (nhiem vu an H2).
        \player\nhiemvu_update_all($sid, 'craft_fail', 1, $dblj, array());
    }
}

// Lấy danh sách recipes
$recipes = \player\chetao_get_all_recipes();

// Lấy số lượng nguyên liệu
$stmt = $dblj->prepare("SELECT djid, djsum FROM playerdaoju WHERE sid = ?");
$stmt->execute(array($sid));
$inventory = array();
while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $inventory[intval($row['djid'])] = intval($row['djsum']);
}

// Navigation
$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$luondenCmd = $encode->encode("cmd=luonden&sid=$sid");
$chetaoCmd = $encode->encode("cmd=chetao&sid=$sid");
$nowmidCmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

// Element info
$lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
$elementColors = array('kim' => '#C0C0C0', 'moc' => '#228B22', 'thuy' => '#1E90FF', 'hoa' => '#FF4500', 'tho' => '#8B4513');
$elementNames = array('kim' => 'Kim', 'moc' => 'Mộc', 'thuy' => 'Thủy', 'hoa' => 'Hỏa', 'tho' => 'Thổ', 'all' => 'Ngũ Hành', 'none' => 'Vô Hành');
$lcColor = isset($elementColors[$lcElement]) ? $elementColors[$lcElement] : '#888';
$lcName = isset($elementNames[$lcElement]) ? $elementNames[$lcElement] : 'Không';

// Đếm số lượng recipe
$recipeCount = count($recipes);

$html = <<<HTML
<style>
.ct-page { width:100%; padding:10px; }

/* Hero */
.ct-hero {
    position:relative; overflow:hidden;
    display:grid; grid-template-columns:64px minmax(0,1fr); gap:14px; align-items:center;
    padding:18px; margin-bottom:12px;
    border:1px solid rgba(125,211,252,.32); border-radius:18px;
    background: linear-gradient(180deg, rgba(12,27,70,.92), rgba(15,23,62,.94));
    box-shadow: 0 0 0 1px rgba(255,255,255,.04) inset, 0 0 26px rgba(56,189,248,.14);
}
.ct-hero-bg{position:absolute;inset:0;z-index:0}
.ct-hero-bg img{width:100%;height:100%;object-fit:cover;display:block;filter:saturate(1.15) contrast(1.08)}
.ct-hero-mask{position:absolute;inset:0;background:linear-gradient(90deg, rgb(12 27 70 / 34%), rgba(12, 27, 70, .48), rgb(12 27 70 / 0%)), linear-gradient(180deg, #00000000 5%, rgb(12 27 70 / 0%) 100%) !important;pointer-events:none!important;pointer-events:none;z-index:1}
.ct-hero-icon {
    position:relative; z-index:2;
    width:64px; height:64px; display:flex; align-items:center; justify-content:center;
    font-size:32px; border-radius:16px;
    border:1px solid rgba(125,211,252,.36); background:rgba(15,40,92,.76);
}
.ct-hero-text {position:relative; z-index:2;}
.ct-hero-kicker {
    color:#8ee7ff; font-size:12px; font-weight:1000; letter-spacing:1.1px;
    text-transform:uppercase; text-shadow:0 0 10px rgba(56,189,248,.55); margin-bottom:2px;
}
.ct-hero-title {
    color:#fff; font-size:26px; font-weight:1000; margin:0;
    text-shadow:0 0 14px rgba(125,211,252,.45), 0 0 24px rgba(168,85,247,.26);
}
.ct-hero-desc { color:#d7e8ff; font-size:13px; font-weight:700; margin-top:4px; }
.ct-hero-badge {
    display:inline-flex; align-items:center; min-height:26px; padding:4px 10px;
    border-radius:999px; border:1px solid rgba(125,211,252,.36);
    background:rgba(15,40,92,.76); color:#dbeafe; font-weight:900; font-size:12px;
    margin-top:6px;
}

/* Tabs */
.ct-tabs {
    display:flex; justify-content:center; gap:10px; padding:6px; margin-bottom:12px;
    border:1px solid rgba(94,208,255,.22); border-radius:14px;
    background:rgba(7,16,45,.44); box-shadow:0 0 18px rgba(56,189,248,.10) inset;
}
.ct-tab {
    min-height:36px; padding:0 16px; border-radius:11px;
    border:1px solid rgba(100,146,255,.30); background:rgba(27,43,91,.72);
    color:#cfe9ff; font-weight:900; font-size:13px; text-decoration:none;
    display:flex; align-items:center;line-height: 2.5;transition:all .2s;
}
.ct-tab.active {
    color:#fff; border-color:rgba(125,211,252,.78);
    background:linear-gradient(180deg, rgba(42,115,210,.80), rgba(33,69,156,.74));
    box-shadow:0 0 16px rgba(56,189,248,.28), inset 0 0 12px rgba(255,255,255,.08);
    text-shadow:0 0 10px rgba(125,211,252,.9);
}

/* Craft message */
.ct-notice {
    padding:12px 14px; border-radius:14px; margin-bottom:12px; font-weight:900; font-size:13px; text-align:center;
}
.ct-notice.ok { border:1px solid rgba(34,197,94,.45); background:rgba(20,83,45,.38); color:#bbf7d0; }
.ct-notice.bad { border:1px solid rgba(239,68,68,.45); background:rgba(127,29,29,.38); color:#fecaca; }

/* Recipe list */
.ct-list { display:flex; flex-direction:column; gap:8px; margin-bottom:12px; }
.ct-recipe {
    border:1px solid rgba(93,151,255,.24); border-radius:15px;
    background: radial-gradient(circle at 0% 0%, rgba(56,189,248,.08), transparent 35%),
                linear-gradient(180deg, rgba(18,36,82,.74), rgba(11,24,62,.78));
    box-shadow:0 0 0 1px rgba(255,255,255,.03) inset, 0 8px 22px rgba(0,0,0,.15);
    padding:12px 14px; transition:all .2s;
}
.ct-recipe.open {
    border-color:rgba(125,211,252,.50);
    box-shadow:0 0 0 1px rgba(255,255,255,.04) inset, 0 0 18px rgba(56,189,248,.16);
}
.ct-recipe-toggle {
    display:flex; align-items:center; justify-content:space-between;
    text-decoration:none; padding:2px 0; border:none; background:none;
}
.ct-recipe-name {
    font-size:15px; font-weight:1000; color:#fff;
    text-shadow:0 0 12px rgba(125,211,252,.45);
}
.ct-recipe-arrow { font-size:13px; color:#7da8cc; }
.ct-recipe-desc { font-size:12px; color:#9fcaff; margin-top:8px; margin-bottom:6px; }
.ct-recipe-rate { font-size:12px; color:#fde68a; margin-bottom:6px; font-weight:900; }
.ct-recipe-result { font-size:12px; color:#86efac; margin-bottom:6px; font-weight:900; }
.ct-recipe-level { font-size:11px; color:#7da8cc; margin-bottom:6px; }
.ct-materials { display:flex; flex-wrap:wrap; gap:6px; }
.ct-mat {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 8px; border-radius:999px; font-size:11px; font-weight:900;
    border:1px solid rgba(125,211,252,.24); background:rgba(7,16,45,.60);
}
.ct-mat-ok { color:#86efac; border-color:rgba(34,197,94,.36); }
.ct-mat-fail { color:#fca5a5; border-color:rgba(239,68,68,.36); }

/* Craft button */
.ct-craft-btn {
    display:block; width:100%; padding:14px; text-align:center; text-decoration:none;
    border-radius:14px; font-weight:1000; font-size:15px; margin-top:4px;
    background:linear-gradient(180deg, #22c55e, #15803d);
    border:1px solid rgba(187,247,208,.35); color:#fff;
    box-shadow:0 8px 18px rgba(0,0,0,.22), 0 0 14px rgba(34,197,94,.18) inset;
    transition:all .2s;
}
.ct-craft-btn:hover { box-shadow:0 8px 24px rgba(0,0,0,.28), 0 0 20px rgba(34,197,94,.30) inset; }
.ct-craft-btn.disabled { background:rgba(60,60,80,.5); color:#666; border-color:rgba(255,255,255,.08); box-shadow:none; pointer-events:none; }

/* Footer */
.ct-footer { display:flex; gap:10px; margin-top:12px; }
.ct-footer a {
    flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
    min-height:40px; border-radius:12px; font-weight:900; font-size:13px; text-decoration:none;
}
.ct-back {
    background:rgba(16,33,78,.72); border:1px solid rgba(125,211,252,.45); color:#48c7ff;
}
.ct-home {
    background:linear-gradient(180deg, #22c55e, #15803d);
    border:1px solid rgba(187,247,208,.35); color:#fff;
}
</style>

<div class="ct-page">
HTML;

// Craft message
if ($craftMsg !== '') {
    $msgClass = ($craftMsgType === 'ok') ? 'ok' : 'bad';
    $html .= '<div class="ct-notice ' . $msgClass . '">' . $craftMsg . '</div>';
}

// Hero
$html .= <<<HTML
<div class="ct-hero">
    <div class="ct-hero-bg"><img src="/images/chetao/bg_chetao.png" alt=""></div>
    <div class="ct-hero-mask"></div>
    <div class="ct-hero-icon">⚒</div>
    <div class="ct-hero-text">
        <div class="ct-hero-kicker">Hệ thống chế tạo</div>
        <h1 class="ct-hero-title">Chế Tạo</h1>
        <div class="ct-hero-desc">Rèn vũ khí và trang bị từ nguyên liệu quý hiếm</div>
        <div class="ct-hero-badge">🔥 Linh căn {$lcName} · {$recipeCount} công thức</div>
    </div>
</div>
HTML;

// Tabs
$html .= <<<HTML
<div class="ct-tabs">
    <a class="ct-tab active" href="?cmd={$chetaoCmd}">⚒ Chế Tạo</a>
    <a class="ct-tab" href="?cmd={$luondenCmd}">🔥 Luyện Đan</a>
    <a class="ct-tab" href="?cmd={$ztcmd}">👤 Nhân Vật</a>
</div>
HTML;

// Recipe list
$html .= '<div class="ct-list">';

foreach ($recipes as $rid => $recipe) {
    $rType = isset($recipe['type']) ? $recipe['type'] : 'equipment';
    $rLevel = isset($recipe['level_required']) ? intval($recipe['level_required']) : 1;
    $isOpen = ($selectedRecipe == intval($rid));

    // Lấy tên từ config
    $rName = 'Vật phẩm';
    $rColor = '#fff';
    if ($rType === 'equipment') {
        $zbid = isset($recipe['result_zbid']) ? intval($recipe['result_zbid']) : 0;
        $zbConfig = \player\getzbkzb($zbid, $dblj);
        $rName = isset($zbConfig->zbname) ? $zbConfig->zbname : 'Trang bị #' . $zbid;
        $rColor = isset($zbConfig->zbys) ? $zbConfig->zbys : '#fff';
    } elseif ($rType === 'special') {
        $djId = isset($recipe['result_daoju_id']) ? intval($recipe['result_daoju_id']) : 0;
        $djConfig = \player\daoju_config_get($djId);
        $rName = isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
        $rColor = '#fde68a';
    }

    // Detail
    $detailHtml = '';
    if ($isOpen) {
        $rDesc = '';
        $resultText = '';
        if ($rType === 'equipment') {
            $zbid = isset($recipe['result_zbid']) ? intval($recipe['result_zbid']) : 0;
            $zbConfig = \player\getzbkzb($zbid, $dblj);
            $rDesc = isset($zbConfig->zbinfo) ? $zbConfig->zbinfo : '';
            $slotNames = array(1=>'Vũ khí',2=>'Mũ',3=>'Giáp',4=>'Giày',5=>'Găng',7=>'Dây chuyền',8=>'Nhẫn');
            $zbtool = isset($zbConfig->zbtool) ? intval($zbConfig->zbtool) : 0;
            $slotName = isset($slotNames[$zbtool]) ? $slotNames[$zbtool] : 'Trang bị';
            $zbColor = isset($zbConfig->zbys) ? $zbConfig->zbys : '#fff';
            $stats = array();
            if (isset($zbConfig->zbgj) && $zbConfig->zbgj > 0) $stats[] = "ATK +{$zbConfig->zbgj}";
            if (isset($zbConfig->zbfy) && $zbConfig->zbfy > 0) $stats[] = "DEF +{$zbConfig->zbfy}";
            if (isset($zbConfig->zbhp) && $zbConfig->zbhp > 0) $stats[] = "HP +{$zbConfig->zbhp}";
            if (isset($zbConfig->zbbj) && $zbConfig->zbbj > 0) $stats[] = "Crit +{$zbConfig->zbbj}";
            if (isset($zbConfig->zbxx) && $zbConfig->zbxx > 0) $stats[] = "Hút máu +{$zbConfig->zbxx}";
            $resultText = "<span style='color:{$zbColor}'>[{$slotName}] {$rName}</span> <span style='font-size:11px;color:#9fcaff'>" . implode(' · ', $stats) . "</span>";
        } elseif ($rType === 'special') {
            $djId = isset($recipe['result_daoju_id']) ? intval($recipe['result_daoju_id']) : 0;
            $djAmount = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;
            $djConfig = \player\daoju_config_get($djId);
            $rDesc = isset($djConfig['djinfo']) ? $djConfig['djinfo'] : '';
            $resultText = "Nhận <b>{$djAmount}x</b> {$rName}";
        }
        $rateText = \player\chetao_success_text($player, $recipe);
        $matHtml = '';
        $materials = isset($recipe['materials']) ? $recipe['materials'] : array();
        foreach ($materials as $mat) {
            $matId = intval($mat['id']);
            $matAmount = intval($mat['amount']);
            $have = isset($inventory[$matId]) ? $inventory[$matId] : 0;
            $matConfig = \player\daoju_config_get($matId);
            $matName = isset($matConfig['djname']) ? $matConfig['djname'] : '#' . $matId;
            $matClass = ($have >= $matAmount) ? 'ct-mat-ok' : 'ct-mat-fail';
            $matHtml .= "<span class='ct-mat {$matClass}'>{$matName} {$have}/{$matAmount}</span>";
        }
        $detailHtml = "<div class='ct-recipe-desc'>{$rDesc}</div>
        <div class='ct-recipe-result'>{$resultText}</div>
        <div class='ct-recipe-rate'>⚡ Tỷ lệ: {$rateText}</div>
        <div class='ct-recipe-level'>📊 Yêu cầu: Cấp {$rLevel}</div>
        <div class='ct-materials'>{$matHtml}</div>";
    }

    $openClass = $isOpen ? ' open' : '';
    $href = $isOpen ? '#' : '?cmd=' . $encode->encode("cmd=chetao&recipe_id={$rid}&sid=$sid");
    $arrow = $isOpen ? '▾' : '▸';

    $html .= <<<HTML
    <div class="ct-recipe{$openClass}">
        <a class="ct-recipe-toggle" href="{$href}">
            <span class="ct-recipe-name" style="color:{$rColor}">{$rName}</span>
            <span class="ct-recipe-arrow">{$arrow}</span>
        </a>
        {$detailHtml}
    </div>
HTML;
}

$html .= '</div>';

// Craft button
$doCraftCmd = ($selectedRecipe > 0) ? $encode->encode("cmd=do_craft_chetao&recipe_id={$selectedRecipe}&sid=$sid") : '#';
$btnClass = ($selectedRecipe > 0) ? 'ct-craft-btn' : 'ct-craft-btn disabled';

$html .= <<<HTML
<a class="{$btnClass}" href="?cmd={$doCraftCmd}" onclick="return confirm('Xác nhận chế tạo?')">⚒ CHẾ TẠO</a>
HTML;

// Footer
$html .= <<<HTML
<div class="ct-footer">
    <a class="ct-back" href="#" onclick="history.back();return false;">← Trở về</a>
    <a class="ct-home" href="?cmd={$nowmidCmd}">🏠 Đại Sảnh</a>
</div>
</div>
HTML;

echo $html;
?>
