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
for($i=1;$i<=16;$i++){

    $slotName    = $toolNames[$i];
    $defaultIcon = isset($equipDefaultIcons[$i]) ? $equipDefaultIcons[$i] : './images/slot/zb_slot_01.png';
    $zhuangbei   = $player->{"tool$i"} != 0 ? player\getzb($player->{"tool$i"}, $dblj) : false;

    if ($zhuangbei){
        $wornCount++;
        $icon = tcg_zb_image_with_version_tb(tcg_zb_image_path_tb($zhuangbei, $defaultIcon));
        $qh     = intval($zhuangbei->qianghua);
        $qhHtml = $qh > 0 ? ' <span class="equip-qh">+' . $qh . '</span>' : '';
        $tipAttr    = \player\itemtip_attr_for($zhuangbei, $player, $dblj);
        $detailHref = '?cmd=' . $encode->encode("cmd=chakanzb&zbnowid=" . $player->{"tool$i"} . "&uid=" . $player->uid . "&sid=$sid");

        // Hover/cham vao ANH moi mo tooltip (data-tip nam tren <a> boc <img>).
        $thumb  = '<a class="equip-thumb"' . $tipAttr . ' href="' . $detailHref . '">'
                . '<img src="' . $icon . '" alt="' . $zhuangbei->zbname . '"></a>';
        $cap    = '<div class="equip-cap">'
                . '<div class="equip-item-name" style="color:' . $zhuangbei->zbys . '">' . $zhuangbei->zbname . $qhHtml . '</div>'
                . '<div class="equip-sub">C&#7845;p ' . intval($zhuangbei->zblv) . '</div>'
                . '</div>';
        $remove = '<div class="equip-remove"><a class="remove-btn" href="?cmd=' . $encode->encode("cmd=xxzb&zbwz=$i&sid=$sid") . '">Th&aacute;o</a></div>';

        $equipCards[$i] = '<div class="equip-card is-worn">' . $thumb . $cap . $remove . '</div>';
    } else {
        $icon  = tcg_zb_image_with_version_tb($defaultIcon);
        $thumb = '<div class="equip-thumb equip-thumb-empty"><img src="' . $icon . '" alt="' . $slotName . '"></div>';
        $cap   = '<div class="equip-cap">'
               . '<div class="equip-item-name equip-slotname">' . $slotName . '</div>'
               . '<div class="equip-sub equip-sub-empty">Ch&#432;a trang b&#7883;</div>'
               . '</div>';
        $equipCards[$i] = '<div class="equip-card is-empty">' . $thumb . $cap . '</div>';
    }
}


/* =========================
   HTML
========================= */

$quickEquipCmd = $encode->encode("cmd=quickequip&sid=$sid");
$bagzbcmd = $encode->encode("cmd=getbagzb&sid=$sid");

/* Hai cot, moi cot 8 o theo nhom. */
$zbColsHtml = '';
foreach ($equipGroups as $groupName => $groupSlots) {
    $groupCards = '';
    foreach ($groupSlots as $slot) {
        $groupCards .= $equipCards[$slot];
    }
    $zbColsHtml .= '<div class="zb-col">'
        . '<div class="zb-col-title">' . $groupName
        . '<span class="zb-col-count">' . count($groupSlots) . ' &ocirc;</span></div>'
        . '<div class="equip-grid">' . $groupCards . '</div></div>';
}

$html = '<div class="profile-page">'
    . '<div class="profile-header">'
    . '<div class="profile-realm">Cấp ' . $player->ulv . '</div>'
    . '<div class="profile-hint">Chỉ mặc được trang bị có yêu cầu cảnh giới không vượt quá cấp hiện tại</div>'
    . '</div>'
    . '<div class="profile-section">'
    . '<div class="profile-section-title">🎒 Trang bị đang mặc <span class="zb-worn-count">Đã mặc ' . $wornCount . '/16</span></div>'
    . '<div class="profile-section-actions">'
    . '<a class="quick-equip-btn" href="?cmd=' . $quickEquipCmd . '">Trang bị nhanh</a>'
    . '</div>'
    . '<div class="zb-cols">' . $zbColsHtml . '</div>'
    . '</div>'
    . '<div class="profile-bottom">'
    . '<a class="profile-back" href="game.php?cmd=' . $gonowmid . '">← Đại Sảnh</a>'
    . '<a class="profile-home" href="?cmd=' . $bagzbcmd . '">Túi đồ →</a>'
    . '</div>'
    . $ts
    . '</div>';

echo $html;
