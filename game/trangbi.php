<?php
$ts = '';
$player = player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

function tcg_redirect_to_trangbi($encode, $sid)
{
    $zt = $encode->encode("cmd=trangbi&sid=".$sid);
    $url = "game.php?cmd=".$zt;

    if (!headers_sent()) {
        header("Location: ".$url);
        exit;
    }

    echo '<script>window.location.href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'";</script>';
    echo '<meta http-equiv="refresh" content="0;url='.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">';
    exit;
}

function tcg_zb_image_path_tb($zhuangbei, $fallback)
{
    return player\zhuangbei_image_path($zhuangbei, $fallback);
}

function tcg_zb_image_with_version_tb($src)
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


/* =========================
   THÁO TRANG BỊ
========================= */

if ($cmd == 'xxzb'){
    if (isset($zbwz)){
        $zbwz = intval($zbwz);
        if ($zbwz >= 1 && $zbwz <= 16){
            $sql = "UPDATE game1 SET tool$zbwz = 0 WHERE sid = ".$dblj->quote($sid);
            $dblj->exec($sql);
            tcg_redirect_to_trangbi($encode, $sid);
        }
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

        $wornNow = intval($zbnowid) > 0 && in_array(intval($zbnowid), array_map('intval', $arr), true);

        if (!$wornNow){

            $nowzb = player\getzb($zbnowid,$dblj);

            if (!$nowzb || $nowzb->uid != $player->uid){
                $ts = '<script>popup({type:"error",msg:"Ngươi không có trang bị này, không thể trang bị!",delay:2000,bg:true,clickDomCancel:true});</script>';
            }
            elseif (!player\zhuangbei_realm_ok($nowzb, $player)){
                $need = player\zhuangbei_realm_require($nowzb);
                $ts = '<script>popup({type:"error",msg:"Cảnh giới không đủ! Trang bị yêu cầu cấp '.$need.', đẳng cấp hiện tại của ngươi là '.$player->ulv.'. Không thể trang bị!",delay:2000,bg:true,clickDomCancel:true});</script>';
            }
            elseif($nowzb->tool != $zbwz && $nowzb->tool){
                $ts = '<script>popup({type:"error",msg:"Trang bị chủng loại không phù hợp, không thể trang bị!",delay:2000,bg:true,clickDomCancel:true});</script>';
            }
            elseif(intval($zbwz) >= 1 && intval($zbwz) <= 16){
                $zbwz = intval($zbwz);
                $zbnowid = intval($zbnowid);
                $sql = "UPDATE game1 SET tool{$zbwz} = {$zbnowid} WHERE sid = ".$dblj->quote($sid);
                $dblj->exec($sql);
                tcg_redirect_to_trangbi($encode, $sid);
            }
        } else {
            $ts = '<script>popup({type:"info",msg:"Trang bị này đang được mặc!",delay:2000,bg:true,clickDomCancel:true});</script>';
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
            $currentScores[$i] = player\zhuangbei_quick_score($currentZb);
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

        $slot = player\zhuangbei_quick_slot($candidate);
        if ($slot <= 0) {
            continue;
        }

        $score = player\zhuangbei_quick_score($candidate);
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

    tcg_redirect_to_trangbi($encode, $sid);
}


/* =========================
   LOAD EQUIPMENT VARIABLES
========================= */

$equipDefaultIcons = player\zhuangbei_slot_icons();
$toolNames = player\zhuangbei_tool_names();
$equipGroups = player\zhuangbei_equip_groups();

$equipCards = array();
$wornCount = 0;
$qhTotal = 0; // tổng cấp cường hóa các món đang mặc
for($i=1;$i<=16;$i++){

    $slotName    = $toolNames[$i];
    $defaultIcon = isset($equipDefaultIcons[$i]) ? $equipDefaultIcons[$i] : './images/slot/zb_slot_01.png';
    $zhuangbei   = $player->{"tool$i"} != 0 ? player\getzb($player->{"tool$i"}, $dblj) : false;

    if ($zhuangbei){
        $wornCount++;
        $icon = tcg_zb_image_with_version_tb(tcg_zb_image_path_tb($zhuangbei, $defaultIcon));
        $qh     = intval($zhuangbei->qianghua);
        $qhTotal += $qh;
        $qhHtml = $qh > 0 ? ' <span class="equip-qh">+' . $qh . '</span>' : '';
        $tipAttr    = \player\itemtip_attr_for($zhuangbei, $player, $dblj);
        $detailHref = '?cmd=' . $encode->encode("cmd=chakanzb&zbnowid=" . $player->{"tool$i"} . "&uid=" . $player->uid . "&sid=$sid");

        // Hover/cham vao ANH moi mo tooltip (data-tip nam tren <a> boc <img>).
        $qhTag  = $qh > 0 ? '<span class="tb-qh">+' . $qh . '</span>' : '';
        $thumb  = '<a class="tb-thumb"' . $tipAttr . ' href="' . $detailHref . '">'
                . '<img src="' . $icon . '" alt="' . $zhuangbei->zbname . '">' . $qhTag . '</a>';
        $cap    = '<div class="tb-name" style="color:' . $zhuangbei->zbys . '" title="' . $zhuangbei->zbname . '">' . $zhuangbei->zbname . '</div>';
        $remove = '<a class="tb-x" href="?cmd=' . $encode->encode("cmd=xxzb&zbwz=$i&sid=$sid") . '" title="Th&aacute;o">&times;</a>';

        // Màu phẩm chất cho viền / phát sáng ô (giống thẻ trang bị trang xem người khác)
        $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
        $cap .= '<div class="tb-ra">' . htmlspecialchars(player\zhuangbei_rarity_name($zbRa), ENT_QUOTES, 'UTF-8') . '</div>';
        $equipCards[$i] = '<div class="tb-slot is-worn" style="--ra:' . player\zhuangbei_rarity_color($zbRa) . '">' . $thumb . $cap . $remove . '</div>';
    } else {
        $icon  = tcg_zb_image_with_version_tb($defaultIcon);
        $thumb = '<div class="tb-thumb"><img src="' . $icon . '" alt="' . $slotName . '"></div>';
        $cap   = '<div class="tb-name">' . $slotName . '</div>';
        $equipCards[$i] = '<div class="tb-slot is-empty" title="' . $slotName . ' — ch&#432;a trang b&#7883;">' . $thumb . $cap . '</div>';
    }
}


/* =========================
   HTML
========================= */

$quickEquipCmd = $encode->encode("cmd=quickequip&sid=$sid");
$bagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");

/* Bố cục búp bê nhân vật (giống trang Nhân Vật): 8 ô nhóm 1 bên trái, chân dung giữa, 8 ô nhóm 2 bên phải */
$zbSides = array();
foreach ($equipGroups as $groupName => $groupSlots) {
    $groupCards = '';
    foreach ($groupSlots as $slot) {
        $groupCards .= $equipCards[$slot];
    }
    $zbSides[] = '<div class="tb-side-title">' . $groupName . '</div><div class="tb-grid">' . $groupCards . '</div>';
}
$tbVip = intval($player->vip) > 0 ? intval($player->vip) : 0;
$tbAvatar = \player\player_avatar_img(isset($player->usex) ? $player->usex : 1);
$tbName = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
// Chiến lực + bảng thuộc tính (cùng kiểu trang Tu Luyện, dùng lại .xl-stats); số gọn bằng tcg_num_short() của game.php
$tbPower = tcg_num_short(\player\combat_power($player));
$tbStats = '<div class="xl-stats tb-stats">'
    . '<div class="xl-stat"><span>Tấn Công</span><b>' . tcg_num_short($player->ugj) . '</b></div>'
    . '<div class="xl-stat"><span>Phòng Thủ</span><b>' . tcg_num_short($player->ufy) . '</b></div>'
    . '<div class="xl-stat"><span>Khí Huyết</span><b>' . tcg_num_short($player->umaxhp) . '</b></div>'
    . '<div class="xl-stat"><span>Bạo Kích</span><b>' . htmlspecialchars($player->ubj, ENT_QUOTES, 'UTF-8') . '</b></div>'
    . '<div class="xl-stat"><span>Hút Máu</span><b>' . htmlspecialchars($player->uxx, ENT_QUOTES, 'UTF-8') . '</b></div>'
    . '<div class="xl-stat"><span>Đột phá</span><b class="xl-plus">+' . intval($player->tuposjs) . '%</b></div>'
    . '<div class="xl-stat"><span>Tu luyện</span><b class="xl-plus">+' . intval($player->tupoexp) . '%</b></div>'
    . '<div class="xl-stat"><span>Cường hóa</span><b>+' . $qhTotal . '</b></div>'
    . '<div class="xl-stat"><span>Đã mặc</span><b>' . $wornCount . ' / 16</b></div>'
    . '</div>';

$html = '<div class="profile-page tb-compact">'
    . '<div class="tb-stage">'
    . '<div class="tb-side tb-side-left">' . $zbSides[0] . '</div>'
    . '<div class="tb-center">'
    . '<div class="tb-power"><span>Chiến Lực</span><b>' . $tbPower . '</b></div>'
    . '<div class="tb-figure"><div class="pf-figure-ring"></div><img class="tb-figure-art" src="./' . $tbAvatar . '" alt="avatar"></div>'
    . '<div class="tb-pname">' . $tbName . '</div>'
    . '<div class="tb-meta"><span class="tb-badge">Cấp ' . intval($player->ulv) . '</span></div>'
    . '<a class="tb-quick" href="?cmd=' . $quickEquipCmd . '">⚡ Trang bị nhanh</a>'
    . '<div class="tb-hint">Chỉ mặc được trang bị có yêu cầu cảnh giới không vượt quá cấp hiện tại</div>'
    . '</div>'
    . '<div class="tb-side tb-side-right">' . (isset($zbSides[1]) ? $zbSides[1] : '') . '</div>'
    . '</div>'
    . $tbStats
    . $ts
    . '</div>';

echo $html;
