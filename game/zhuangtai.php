<?php
$ts = '';
if (isset($jzjr)){
    switch ($jzjr){
        case "xy":
            if ($player->ulv < 30){
                $ts = "Đẳng cấp nhỏ hơn 30, dễ tẩu hỏa nhập ma! Không thể tu luyện $tssb <hr>";
                break;
            } else {
                echo "<hr>Tu vi hiện chưa đạt tới cảnh giới này!<hr>";
                break;
            }
    }
}

if ($player->ulv >= 30){
    $tianfu = $encode->encode("cmd=tianfu&sid=$sid");
} else {
    $tianfu = $encode->encode("cmd=zhuangtai&jzjr=xy&sid=$sid");
}

$player = player\getplayer($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$getbagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");

$tssb = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
function tcgAlert(msgHtml){
    var mask = $('<div class="mask"></div>');
    var box = $('<div id="animationTipBox" style="width:300px;height:200px;margin-left:-150px;margin-top:-100px;"></div>');
    var inner = '<div class="tcg-alert-icon">!</div>'
        + '<div class="dec_txt">' + msgHtml + '</div>'
        + '<div class="tcg-popup-btns"><div class="tcg-popup-ok">Xác nhận</div></div>';
    box.html(inner);
    $('body').append(mask).append(box);
    box.find('.tcg-popup-ok').on('click', function(){ box.remove(); mask.remove(); });
    mask.on('click', function(){ box.remove(); mask.remove(); });
}
</script>
<font id="error"></font>
<script type="text/javascript">
setTimeout(function(){
    if(document.all){document.getElementById("error").click();}
    else{
        var e=document.createEvent("MouseEvents");
        e.initEvent("click",true,true);
        document.getElementById("error").dispatchEvent(e);
    }
},500);
$('#error').click(function(){
    popup({type:'error',msg:"Thất bại",delay:2000,bg:true,clickDomCancel:true,width:280,height:140});
});
</script>
HTML;

function tcg_redirect_to_zhuangtai($encode, $sid)
{
    $zt = $encode->encode("cmd=zhuangtai&sid=".$sid);
    $url = "game.php?cmd=".$zt;

    if (!headers_sent()) {
        header("Location: ".$url);
        exit;
    }

    echo '<script>window.location.href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'";</script>';
    echo '<meta http-equiv="refresh" content="0;url='.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">';
    exit;
}

function tcg_zb_image_path($zhuangbei, $fallback)
{
    return player\zhuangbei_image_path($zhuangbei, $fallback);
}

function tcg_zb_image_with_version($src)
{
    if (preg_match('/^(https?:)?\/\//i', $src)) {
        return $src;
    }

    $clean = preg_replace('/\?.*$/', '', $src);
    $relative = ltrim($clean, './');
    $file = __DIR__ . '/../' . $relative;
    if (is_file($file)) {
        $separator = (strpos($src, '?') === false) ? '?' : '&';
        return $src . $separator . 'v=' . filemtime($file);
    }

    return $src;
}

function tcg_quick_equip_score($zhuangbei)
{
    return player\zhuangbei_quick_score($zhuangbei);
}

function tcg_quick_equip_slot($zhuangbei)
{
    return player\zhuangbei_quick_slot($zhuangbei);
}

function tcg_error_popup($msg)
{
    $msg = addslashes($msg);
    return <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
tcgAlert('$msg');
</script>
HTML;
}



/* =========================
   THÁO TRANG BỊ
========================= */

if ($cmd == 'xxzb'){
    if (isset($zbwz)){
        $zbwz = intval($zbwz);
        $sql = "UPDATE game1 SET tool$zbwz = 0 WHERE sid = ".$dblj->quote($sid);
        $dblj->exec($sql);
        tcg_redirect_to_zhuangtai($encode, $sid);
    }
}


/* =========================
   MẶC TRANG BỊ
========================= */

if ($cmd == 'setzbwz'){

    $arr = [
        $player->tool1,$player->tool2,$player->tool3,$player->tool4,
        $player->tool5,$player->tool6,$player->tool7,$player->tool8,
        $player->tool9,$player->tool10,$player->tool11,$player->tool12,
        $player->tool13,$player->tool14,$player->tool15,$player->tool16
    ];

    if (isset($zbnowid) && isset($zbwz)){

        if (!in_array($zbnowid,$arr)){

            $nowzb = player\getzb($zbnowid,$dblj);

            if (!$nowzb || $nowzb->uid != $player->uid){
                $ts = tcg_error_popup("Ngươi không có trang bị này, không thể trang bị!");
            }
            elseif (!player\zhuangbei_realm_ok($nowzb, $player)){
                $need = player\zhuangbei_realm_require($nowzb);
                $ts = tcg_error_popup("Cảnh giới không đủ! Trang bị yêu cầu cấp $need, đẳng cấp hiện tại của ngươi là {$player->ulv}. Không thể trang bị!");
            }
            elseif($nowzb->tool != $zbwz && $nowzb->tool){
                $ts = tcg_error_popup("Trang bị chủng loại không phù hợp, không thể trang bị!");
            }
            else{
                $zbwz = intval($zbwz);
                $zbnowid = intval($zbnowid);
                $sql = "UPDATE game1 SET tool{$zbwz} = {$zbnowid} WHERE sid = ".$dblj->quote($sid);
                $dblj->exec($sql);
                tcg_redirect_to_zhuangtai($encode, $sid);
            }
        }
    }
}


/* =========================
   LOAD TRANG BỊ
========================= */

if ($cmd == 'quickequip'){
    $currentScores = array();
    for ($i=1;$i<=16;$i++){
        $currentScores[$i] = -1;
        if (intval($player->{"tool$i"}) > 0) {
            $currentZb = player\getzb($player->{"tool$i"}, $dblj);
            $currentScores[$i] = tcg_quick_equip_score($currentZb);
        }
    }

    $bestBySlot = array();
    $sql = "SELECT zbnowid FROM playerzhuangbei WHERE sid = ".$dblj->quote($sid)." AND uid = ".intval($player->uid);
    $rows = $dblj->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $candidateId = intval($row['zbnowid']);
        if ($candidateId <= 0) {
            continue;
        }

        $candidate = player\getzb($candidateId, $dblj);
        if (!$candidate || intval($candidate->uid) != intval($player->uid)) {
            continue;
        }
        if (!player\zhuangbei_realm_ok($candidate, $player)) {
            continue;
        }

        $slot = tcg_quick_equip_slot($candidate);
        if ($slot <= 0) {
            continue;
        }

        $score = tcg_quick_equip_score($candidate);
        if ($score <= $currentScores[$slot]) {
            continue;
        }
        if (!isset($bestBySlot[$slot]) || $score > $bestBySlot[$slot]['score']) {
            $bestBySlot[$slot] = array('id' => $candidateId, 'score' => $score);
        }
    }

    if (!empty($bestBySlot)) {
        $sets = array();
        foreach ($bestBySlot as $slot => $item) {
            $sets[] = "tool".intval($slot)." = ".intval($item['id']);
        }
        if (!empty($sets)) {
            $sql = "UPDATE game1 SET ".implode(',', $sets)." WHERE sid = ".$dblj->quote($sid);
            $dblj->exec($sql);
            if (function_exists('player\\player_cache_clear')) {
                player\player_cache_clear($sid);
            }
        }
    }

    tcg_redirect_to_zhuangtai($encode, $sid);
}

$equipDefaultIcons = player\zhuangbei_slot_icons();

for($i=1;$i<=16;$i++){

    ${"tool$i"} = '';
    ${"tool".$i."_remove"} = '';
    ${"tool".$i."_icon"} = isset($equipDefaultIcons[$i]) ? $equipDefaultIcons[$i] : './images/slot/zb_slot_01.png';

    $zhuangbei = $player->{"tool$i"} != 0 ? player\getzb($player->{"tool$i"}, $dblj) : false;

    if ($zhuangbei){

        ${"tool".$i."_icon"} = tcg_zb_image_path($zhuangbei, ${"tool".$i."_icon"});

        $qhs = '';
		if ($zhuangbei->qianghua > 0){
			$qhs = '<span class="equip-qh">+'.intval($zhuangbei->qianghua).'</span>';
		}

        // Badge nguyên tố + phẩm chất (hệ thống mới)
        $zbElem = isset($zhuangbei->element) ? $zhuangbei->element : 'none';
        $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
        $zbBadge = '<span class="zb-badge rarity" style="color:'.\player\zhuangbei_rarity_color($zbRa).'">'.\player\zhuangbei_rarity_name($zbRa).'</span>';
        foreach (\player\zhuangbei_elements_parse($zbElem) as $nhEl) {
            $zbBadge = '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color_ui($nhEl).'">'
                . \player\zhuangbei_element_icon($nhEl) . ' ' . \player\zhuangbei_element_name($nhEl) . '</span>' . $zbBadge;
        }

        ${"tool".$i."_remove"} =
        '<a class="remove-btn" href="?cmd='.$encode->encode("cmd=xxzb&zbwz=$i&sid=$sid").'">Tháo</a>';

        ${"tool$i"} =
		'<a class="equip-link"'.\player\itemtip_attr_for($zhuangbei, $player, $dblj).' href="?cmd='.$encode->encode("cmd=chakanzb&zbnowid=".$player->{"tool$i"}."&uid=".$player->uid."&sid=$sid").'">
			<span class="equip-title" style="color:'.$zhuangbei->zbys.'">'.$zhuangbei->zbname.$qhs.'</span>
			<div class="bag-item-badges">'.$zbBadge.'</div>
		</a>';
    }

    ${"tool".$i."_icon"} = tcg_zb_image_with_version(${"tool".$i."_icon"});
}


/* =========================
   THÔNG TIN NHÂN VẬT
========================= */

$shenfen = ['Chiến sĩ','Chiến sĩ','Hiệp sĩ','Ẩn sĩ'];
$sf = $shenfen[$player->shenfen];

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$quickEquipCmd = $encode->encode("cmd=quickequip&sid=$sid");
$cwinfo = $encode->encode("cmd=chongwu&cwid=$player->cw&canshu=cwinfo&sid=$sid");
$gm = $encode->encode("cmd=czbgm&canshu2=gaiming2&sid=$sid");
$sz = $encode->encode("cmd=taozhuang&sid=$sid");
$lccmd = $encode->encode("cmd=linhcan&sid=$sid");
$thechatcmd = $encode->encode("cmd=thechat&sid=$sid");
$huyetmachcmd = $encode->encode("cmd=huyetmach&sid=$sid");


/* =========================
   HTML
========================= */
$menuNhanVat  = ($cmd == 'zhuangtai' || empty($cmd))
    ? '<font color="#9c27b0">Nhân vật</font>'
    : 'Nhân vật';

$menuSungVat = ($cmd == 'chongwu')
    ? '<font color="#9c27b0">Sủng vật</font>'
    : 'Sủng vật';

$menuThanTrang = ($cmd == 'taozhuang')
    ? '<font color="#9c27b0">Thần trang</font>'
    : 'Thần trang';

$menuDoiTen = ($cmd == 'czbgm')
    ? '<font color="#9c27b0">Đổi tên</font>'
    : 'Đổi tên';

$menuLinhCan = ($cmd == 'linhcan')
    ? '<font color="#9c27b0">Linh Căn</font>'
    : 'Linh Căn';


for ($i=1;$i<=16;$i++){
    if (${"tool$i"} == ''){
        ${"tool$i"} = '<span class="equip-empty">Chưa trang bị</span>';
    }
}

$menuNhanVatClass   = ($cmd == 'zhuangtai' || empty($cmd)) ? ' active' : '';
$menuSungVatClass   = ($cmd == 'chongwu') ? ' active' : '';
$menuThanTrangClass = ($cmd == 'taozhuang') ? ' active' : '';
$menuTianfuClass    = ($cmd == 'tianfu') ? ' active' : '';
$menuDoiTenClass    = ($cmd == 'czbgm') ? ' active' : '';
$menuLinhCanClass   = ($cmd == 'linhcan') ? ' active' : '';
$menuTheChatClass   = ($cmd == 'thechat') ? ' active' : '';
$menuHuyetMachClass = ($cmd == 'huyetmach') ? ' active' : '';

$hpPercent = 0;
if (intval($player->umaxhp) > 0) {
    $hpPercent = min(100, max(0, intval(round($player->uhp * 100 / $player->umaxhp))));
}

$expPercent = 0;
if (intval($player->umaxexp) > 0) {
    $expPercent = min(100, max(0, intval(round($player->uexp * 100 / $player->umaxexp))));
}

$energyValue = isset($player->ustamina) ? intval($player->ustamina) : 100;
$energyMax = isset($player->umaxstamina) ? intval($player->umaxstamina) : 100;
$energyPercent = $energyMax > 0 ? min(100, max(0, intval(round($energyValue * 100 / $energyMax)))) : 0;

$chienLuc = \player\combat_power($player);
$chienLucText = number_format($chienLuc, 0, '.', '.');
$vipLevel = isset($player->vip) ? intval($player->vip) : 0;
$vipLevel = max(0, min(16, $vipLevel));
$vipImg = './images/vip'.$vipLevel.'.png';
$ztPlayerSafe = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
$ztAvatar = \player\player_avatar_img($player->usex);

$html = <<<HTML


<div class="profile-page">
    $ts
    <div class="profile-tabs">
        <a class="profile-tab$menuNhanVatClass" href="?cmd=$ztcmd">Nhân vật</a>
        <a class="profile-tab$menuSungVatClass" href="?cmd=$cwinfo">Sủng vật</a>
        <a class="profile-tab$menuThanTrangClass" href="?cmd=$sz">Thần trang</a>
        <a class="profile-tab$menuTianfuClass" href="?cmd=$tianfu">Thiên phú</a>
        <a class="profile-tab$menuLinhCanClass" href="?cmd=$lccmd">Linh Căn</a>
        <a class="profile-tab$menuTheChatClass" href="?cmd=$thechatcmd">Thể Chất</a>
        <a class="profile-tab$menuHuyetMachClass" href="?cmd=$huyetmachcmd">Huyết Mạch</a>
        <a class="profile-tab$menuDoiTenClass" href="?cmd=$gm">Đổi tên</a>
    </div>

    <div class="profile-hero">
        <div class="profile-avatar">
            <img src="./$ztAvatar" alt="avatar">
            <span class="profile-vip-badge"><img src="$vipImg" alt="VIP $vipLevel"></span>
        </div>
        <div class="profile-hero-info">
            <h1 class="profile-name">*** $ztPlayerSafe ***</h1>
            <div class="profile-meta">
                <span class="profile-badge realm">✦ $player->jingjie $player->cengci</span>
                <span class="profile-badge">🎖️ $sf</span>
                <span class="profile-badge">Cấp $player->ulv</span>
            </div>
            <div class="profile-power-panel">
                <span class="profile-power-mark">🔥</span>
                <span class="profile-power-copy"><em>Chiến lực</em><strong>$chienLucText</strong></span>
            </div>
            <div class="profile-meta">
                <span class="profile-badge">💎 Ma thạch: $player->uczb</span>
                <span class="profile-badge">💰 Linh thạch: $player->uyxb</span>
            </div>
        </div>
    </div>

    <div class="profile-bars">
        <div class="profile-bar">
            <div class="profile-bar-title"><span>❤️ Khí huyết</span><span>$player->uhp / $player->umaxhp</span></div>
            <div class="profile-bar-track"><div class="profile-bar-fill" style="width:$hpPercent%"></div></div>
        </div>
        <div class="profile-bar">
            <div class="profile-bar-title"><span>⚡ Thể lực</span><span>$energyValue / $energyMax</span></div>
            <div class="profile-bar-track"><div class="profile-bar-fill energy" style="width:$energyPercent%"></div></div>
        </div>
        <div class="profile-bar profile-bar-fullwidth">
            <div class="profile-bar-title"><span>✨ Tu vi</span><span>$player->uexp / $player->umaxexp</span></div>
            <div class="profile-bar-track"><div class="profile-bar-fill exp" style="width:$expPercent%"></div></div>
        </div>
    </div>

    <div class="profile-stat-grid">
        <div class="profile-stat"><span>⚔️ Công kích</span><strong>$player->ugj</strong></div>
        <div class="profile-stat"><span>🛡 Phòng ngự</span><strong>$player->ufy</strong></div>
        <div class="profile-stat"><span>💥 Bạo kích</span><strong>$player->ubj</strong></div>
        <div class="profile-stat"><span>🩸 Hút máu</span><strong>$player->uxx</strong></div>
        <div class="profile-stat"><span>🧬 Thiên phú</span><strong>$player->tf</strong></div>
        <div class="profile-stat"><span>🍀 Vận may / Né tránh</span><strong>$player->tfxy / $player->tfsb</strong></div>
    </div>

HTML;

if (!empty($player->linhcan_id) && !empty($player->linhcan_name)) {
    $lcColor = $player->linhcan_color;
    $lcElement = $player->linhcan_element;
    $lcName = $player->linhcan_name;

    $lcAbsorb = $player->linhcan_absorb;
    $lcExpBonus = $player->linhcan_exp_bonus;

    $tierNames = [0=>'Phàm', 1=>'Huyền', 2=>'Địa', 3=>'Thiên', 4=>'Thánh', 5=>'Niết'];
    $tierIcons = [0=>'', 1=>'✦', 2=>'✦✦', 3=>'✦✦✦', 4=>'✧✧✧✧', 5=>'✧✧✧✧✧'];
    $tierName = isset($tierNames[$player->linhcan_tier]) ? $tierNames[$player->linhcan_tier] : 'Phàm';
    $tierIcon = isset($tierIcons[$player->linhcan_tier]) ? $tierIcons[$player->linhcan_tier] : '';

    $elementIcons = ['kim'=>'🪙','mộc'=>'🌿','thủy'=>'💧','hỏa'=>'🔥','thổ'=>'🏔','không'=>'🌀'];
    $lcIcon = isset($elementIcons[$lcElement]) ? $elementIcons[$lcElement] : '🌀';

    $lcDetails = '';
    if ($lcAbsorb != 0) {
        $absorbSign = $lcAbsorb > 0 ? '+' : '';
        $lcDetails .= "<div class='profile-stat'><span>Hấp thụ linh khí</span><strong>{$absorbSign}{$lcAbsorb}%</strong></div>";
    }
    if ($lcExpBonus != 0) {
        $expSign = $lcExpBonus > 0 ? '+' : '';
        $lcDetails .= "<div class='profile-stat'><span>Tốc độ tu luyện</span><strong>{$expSign}{$lcExpBonus}%</strong></div>";
    }

    $stats = $player->linhcan_stats;
    $statNames = ['atk'=>'Công','def'=>'Thủ','hp'=>'Huyết','crit'=>'Bạo','lifesteal'=>'Hút'];
    foreach ($stats as $k => $v) {
        if ($v != 0 && isset($statNames[$k])) {
            $sign = $v > 0 ? '+' : '';
            $lcDetails .= "<div class='profile-stat'><span>{$statNames[$k]}</span><strong>{$sign}{$v}</strong></div>";
        }
    }

    // Nội tại nạp ngũ hành (spec docs/DESIGN_NGUHANH_KICHHOAT.md §4) — chỉ hiện khi có món hợp căn.
    if (isset($player->nguhanh_info) && intval($player->nguhanh_info['match_count']) > 0) {
        $ngi = $player->nguhanh_info;
        $ngGain = empty($ngi['gains']) ? '' : ' (' . implode(', ', $ngi['gains']) . ')';
        $lcDetails .= "<div class='profile-stat'><span>☯ Nội tại ngũ hành</span><strong>Hợp căn "
            . intval($ngi['match_count']) . ' món — bậc ' . intval($ngi['tier']) . ' +'
            . intval($ngi['pct']) . '%' . $ngGain . '</strong></div>';
    }

    $html .= <<<HTML
    <div class="profile-section">
        <div class="profile-section-title" style="color:{$lcColor}">{$lcIcon} Linh Căn: {$lcName} <span style="font-size:12px;opacity:0.8">{$tierIcon} {$tierName} {$lcElement}</span></div>
        <div class="profile-stat-grid">
            {$lcDetails}
        </div>
    </div>

HTML;
}

// ===== THỂ CHẤT =====
if (!empty($player->thechat_name)) {
    $tcColor = $player->thechat_color;
    $tcName = $player->thechat_name;
    $tcIcon = $player->thechat_icon;
    $tcQuality = intval($player->thechat_quality);
    $tcStage = intval($player->thechat_awaken_stage);
    $tcCategoryName = isset($player->thechat_category_name) ? $player->thechat_category_name : '';

    if (function_exists('player\\thechat_quality_name')) {
        $tcQName = player\thechat_quality_name($tcQuality);
        $tcQIcon = player\thechat_quality_icon($tcQuality);
    } else {
        $tcQName = 'Phẩm ' . $tcQuality;
        $tcQIcon = '⚪';
    }
    if (function_exists('player\\thechat_awaken_name')) {
        $tcAName = player\thechat_awaken_name($tcStage);
        $tcAIcon = player\thechat_awaken_icon($tcStage);
    } else {
        $tcAName = 'Chưa Thức Tỉnh';
        $tcAIcon = '🔒';
    }

    $tcStats = $player->thechat_stats;
    $tcStatNames = array('hp'=>'Huyết','atk'=>'Công','def'=>'Thủ','crit'=>'Bạo');
    $tcDetails = '';
    foreach ($tcStats as $k => $v) {
        if ($v != 0 && isset($tcStatNames[$k])) {
            $tcDetails .= "<div class='profile-stat'><span>{$tcStatNames[$k]}</span><strong>+" . number_format($v) . "</strong></div>";
        }
    }
    if ($player->thechat_passive_exp > 0) {
        $tcDetails .= "<div class='profile-stat'><span>EXP Tu luyện</span><strong>+" . $player->thechat_passive_exp . "%</strong></div>";
    }
    if ($player->thechat_passive_regen > 0) {
        $tcDetails .= "<div class='profile-stat'><span>Hồi phục</span><strong>+" . $player->thechat_passive_regen . "%</strong></div>";
    }
    if ($player->thechat_passive_resist > 0) {
        $tcDetails .= "<div class='profile-stat'><span>Miễn nhiễm</span><strong>+" . $player->thechat_passive_resist . "%</strong></div>";
    }
    if ($player->thechat_passive_reduce > 0) {
        $tcDetails .= "<div class='profile-stat'><span>Giảm dmg</span><strong>+" . $player->thechat_passive_reduce . "%</strong></div>";
    }

    $html .= <<<HTML
    <div class="profile-section">
        <div class="profile-section-title" style="color:{$tcColor}">{$tcIcon} Thể Chất: {$tcName} <span style="font-size:12px;opacity:0.8">{$tcQIcon} {$tcQName}</span></div>
        <div style="padding:2px 12px;font-size:11px;opacity:0.6">📂 {$tcCategoryName}</div>
        <div class="profile-stat-grid">
            {$tcDetails}
        </div>
        <div style="padding:4px 12px;font-size:11px;opacity:0.7">{$tcAIcon} {$tcAName}</div>
    </div>

HTML;
}

// ===== HUYẾT MẠCH =====
if (!empty($player->huyetmach_name)) {
    $hmColor = $player->huyetmach_color;
    $hmName = $player->huyetmach_name;
    $hmIcon = $player->huyetmach_icon;
    $hmQuality = intval($player->huyetmach_quality);
    $hmStage = intval($player->huyetmach_awaken_stage);

    if (function_exists('player\\huyetmach_quality_name')) {
        $hmQName = player\huyetmach_quality_name($hmQuality);
        $hmQIcon = player\huyetmach_quality_icon($hmQuality);
    } else {
        $hmQName = 'Phẩm ' . $hmQuality;
        $hmQIcon = '⚪';
    }
    if (function_exists('player\\huyetmach_awaken_name')) {
        $hmAName = player\huyetmach_awaken_name($hmStage);
        $hmAIcon = player\huyetmach_awaken_icon($hmStage);
    } else {
        $hmAName = 'Vĩnh Viễn Trầm Mệnh';
        $hmAIcon = '🔒';
    }

    $hmStats = $player->huyetmach_stats;
    $hmStatNames = array('atk'=>'Công','hp'=>'Huyết','crit'=>'Bạo','lifesteal'=>'Hút máu');
    $hmDetails = '';
    foreach ($hmStats as $k => $v) {
        if ($v != 0 && isset($hmStatNames[$k])) {
            $hmDetails .= "<div class='profile-stat'><span>{$hmStatNames[$k]}</span><strong>+" . number_format($v) . "</strong></div>";
        }
    }
    if ($player->huyetmach_element_dmg > 0) {
        $hmElName = function_exists('player\\huyetmach_element_name') ? player\huyetmach_element_name($player->huyetmach_element) : $player->huyetmach_element;
        $hmDetails .= "<div class='profile-stat'><span>Element DMG ({$hmElName})</span><strong>+" . $player->huyetmach_element_dmg . "%</strong></div>";
    }
    if ($player->huyetmach_passive_element > 0) {
        $hmDetails .= "<div class='profile-stat'><span>Element Bonus</span><strong>+" . $player->huyetmach_passive_element . "%</strong></div>";
    }
    if ($player->huyetmach_passive_crit > 0) {
        $hmDetails .= "<div class='profile-stat'><span>Crit Rate</span><strong>+" . $player->huyetmach_passive_crit . "%</strong></div>";
    }
    if ($player->huyetmach_passive_exp > 0) {
        $hmDetails .= "<div class='profile-stat'><span>EXP Bonus</span><strong>+" . $player->huyetmach_passive_exp . "%</strong></div>";
    }

    $html .= <<<HTML
    <div class="profile-section">
        <div class="profile-section-title" style="color:{$hmColor}">{$hmIcon} Huyết Mạch: {$hmName} <span style="font-size:12px;opacity:0.8">{$hmQIcon} {$hmQName}</span></div>
        <div class="profile-stat-grid">
            {$hmDetails}
        </div>
        <div style="padding:4px 12px;font-size:11px;opacity:0.7">{$hmAIcon} {$hmAName}</div>
    </div>

HTML;
}

// ===== ĐAN ĐỘC (pill_toxin - dùng đan) =====
$toxinStmt = $dblj->prepare("SELECT pill_toxin FROM player_alchemist WHERE sid = ?");
$toxinStmt->execute(array($sid));
$toxinRow = $toxinStmt->fetch(\PDO::FETCH_ASSOC);
$pillToxin = $toxinRow ? intval($toxinRow['pill_toxin']) : 0;

if ($pillToxin > 0) {
    // Tính hiệu ứng đan độc (pill_toxin)
    $toxinColor = '#4ade80';
    $toxinPenalty = '';
    
    if ($pillToxin > 100) {
        $statPenalty = min(50, intval($pillToxin / 10) * 0.1);
        $toxinPenalty .= "-{$statPenalty}% tất cả stats";
        $toxinColor = '#fca5a5';
    }
    if ($pillToxin > 500) {
        $pillPenalty = min(50, intval(($pillToxin - 500) / 10));
        $toxinPenalty .= " | -{$pillPenalty}% hiệu quả đan dược";
        $toxinColor = '#f97316';
    }
    if ($pillToxin > 1000) {
        $breakPenalty = min(10, intval(($pillToxin - 1000) / 100));
        $toxinPenalty .= " | -{$breakPenalty}% tỷ lệ đột phá";
        $toxinColor = '#ef4444';
    }
    if ($pillToxin > 2000) {
        $toxinPenalty .= " | Khóa level";
    }
    
    $toxinBarWidth = min(100, intval($pillToxin / 20)); // 2000 = 100%
    
    $html .= <<<HTML
    <div class="profile-section" style="border-color:rgba(239,68,68,.35);background:linear-gradient(135deg,rgba(127,29,29,.4),rgba(153,27,27,.3))">
        <div class="profile-section-title" style="color:#fca5a5">☠️ Đan Độc: {$pillToxin}/2000</div>
        <div style="padding:8px 12px">
            <div style="height:6px;border-radius:3px;background:rgba(0,0,0,.4);margin-bottom:8px;overflow:hidden">
                <div style="height:100%;width:{$toxinBarWidth}%;background:{$toxinColor};border-radius:3px;transition:width .3s"></div>
            </div>
            <div style="font-size:11px;color:#d4a5a5">
                <div>Mức độ: <b style="color:{$toxinColor}">{$toxinPenalty}</b></div>
                <div style="margin-top:4px;opacity:0.7">Tích lũy khi sử dụng đan dược. Dùng đan giải độc để giảm.</div>
            </div>
        </div>
    </div>

HTML;
}

$html .= <<<HTML

    <div class="profile-bottom">
        <a class="profile-back" href="game.php?cmd=$gonowmid">← Đại Sảnh</a>
        <a class="profile-home" href="?cmd=$getbagzbcmd">Túi đồ →</a>
    </div>
</div>
HTML;

echo $html;
?>
