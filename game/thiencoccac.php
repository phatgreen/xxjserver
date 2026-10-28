<?php
if (!isset($cmd)) $cmd = '';
if (!isset($canshu)) $canshu = '';
if (!isset($buy_type)) $buy_type = '';
if (!isset($buy_id)) $buy_id = 0;
if (!isset($tien)) $tien = '';   // công pháp bán cả 2 loại tiền: 'lt' = trả Linh thạch, còn lại trả Ma thạch

require_once __DIR__ . '/wugong_utils.php';
require_once __DIR__ . '/../class/function/wugong.php';
require_once __DIR__ . '/../class/function/congphap.php';

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=" . $player->nowmid . "&sid=" . $sid);

$jj = isset($player->jingjieValue) ? intval($player->jingjieValue) : 0;

$tscg = '';
$tssb = '';

$rangesjjClean = array();
require __DIR__ . '/../class/rangesjj.php';
foreach ($rangesjj as $idx => $fontHtml) {
    $clean = preg_replace('#<[^>]+>#', '', $fontHtml);
    // realm_require / jingjieValue tính từ 1 (= chỉ số cảnh giới + 1) => lưu tên theo chỉ số + 1.
    $rangesjjClean[$idx + 1] = trim($clean);
}
$rangesjjClean[0] = isset($rangesjjClean[1]) ? $rangesjjClean[1] : 'Nhập Môn';
$playerRealmName = isset($rangesjjClean[$jj]) ? $rangesjjClean[$jj] : 'Nhập Môn';

$giaiNames = array(
    0 => 'Phàm Giai',   // nhập môn (chủ dự án 2026-10-28)
    1 => 'Hoàng Giai',
    2 => 'Huyền Giai',
    3 => 'Địa Giai',
    4 => 'Thiên Giai',
    5 => 'Tiên Giai',
    6 => 'Thần Giai',
    7 => 'Thánh Giai',
    8 => 'Đế Giai',     // làn 5 (docs/LAN5_CAP_2000_2500.md)
    9 => 'Đạo Giai',    // làn 7 (docs/LAN7_CAP_3000_3500.md)
    10 => 'Chí Tôn Giai', // làn 9 (docs/LAN9_CAP_4000_4300.md)
);
$phamNames = array(
    1 => 'Sơ Phẩm',
    2 => 'Trung Phẩm',
    3 => 'Cao Phẩm',
    4 => 'Thượng Phẩm',
);
$giaiCss = array(
    0 => 'pham',
    1 => 'hoang',
    2 => 'huyen',
    3 => 'dia',
    4 => 'thien',
    5 => 'tien',
    6 => 'than',
    7 => 'thanh',
    8 => 'de',      // làn 5
    9 => 'dao',     // làn 7
    10 => 'chiton', // làn 9
);
$giaiCurrency = array(
    0 => 'Linh Thạch / Ma Thạch',   // giai 0–3: mua bằng 1 trong 2 (Linh thạch = 3 × Ma thạch)
    1 => 'Linh Thạch / Ma Thạch',
    2 => 'Linh Thạch / Ma Thạch',
    3 => 'Linh Thạch / Ma Thạch',
    4 => 'Ma Thạch',
    5 => 'Ma Thạch',
    6 => 'Ma Thạch',
    7 => 'Ma Thạch',
    8 => 'Ma Thạch',
    9 => 'Ma Thạch',
    10 => 'Ma Thạch',
);

function tcc_full_name($giai, $pham) {
    global $giaiNames, $phamNames;
    $g = isset($giaiNames[$giai]) ? $giaiNames[$giai] : 'Giai ' . $giai;
    $p = isset($phamNames[$pham]) ? $phamNames[$pham] : 'Phẩm ' . $pham;
    return $g . ' ' . $p;
}

function tcc_training_text($mult) {
    $mult = floatval($mult);
    if ($mult <= 1.0) return '';
    $hours = round($mult * 10);
    return '⏱ Tu luyện ~' . $hours . ' phút';
}

$wgCatNames = array(
    'khinh_cong' => 'Khinh Công',
    'noi_cong'    => 'Nội Công / Tâm Pháp',
    'ngoai_cong'  => 'Ngoại Công / Chiêu Thức',
    'tap_ky'      => 'Tuyệt Kỹ / Tạp Kỹ',
);
$wgCatOrder = array('khinh_cong', 'noi_cong', 'ngoai_cong', 'tap_ky');
$wgCatCss = array(
    'khinh_cong' => 'wcat-khinh',
    'noi_cong'    => 'wcat-noi',
    'ngoai_cong'  => 'wcat-ngoai',
    'tap_ky'      => 'wcat-tap',
);
$wgRarityNames = array(
    'nhap_mon'   => 'Cơ Bản',
    'ha_thua'    => 'Hạ Thừa',
    'trung_cap'  => 'Trung Cấp',
    'thuong_thua'=> 'Thượng Thừa',
    'than_cong'  => 'Tuyệt Thế',
    'tien_dao'   => 'Tiên Đạo',
    'hon_nguyen' => 'Hỗn Nguyên',   // làn 5
    'dai_dao'    => 'Đại Đạo',      // làn 8
);
$wgRarityCss = array(
    'nhap_mon'   => 'wrar-nhap',
    'ha_thua'    => 'wrar-ha',
    'trung_cap'  => 'wrar-trung',
    'thuong_thua'=> 'wrar-thuong',
    'than_cong'  => 'wrar-than',
    'tien_dao'   => 'wrar-tien',
    'hon_nguyen' => 'wrar-hn',
    'dai_dao'    => 'wrar-dd',
);
$elementNames = array(
    'kim'  => 'Kim',
    'moc'  => 'Mộc',
    'thuy' => 'Thủy',
    'hoa'  => 'Hỏa',
    'tho'  => 'Thổ',
    'all'  => 'Ngũ Hành',
    'none' => 'Vô Hành',
);
$elementCss = array(
    'kim'  => 'elem-kim',
    'moc'  => 'elem-moc',
    'thuy' => 'elem-thuy',
    'hoa'  => 'elem-hoa',
    'tho'  => 'elem-tho',
    'all'  => 'elem-all',
    'none' => 'elem-none',
);
$elementColors = array(
    'kim'  => '#C0C0C0',
    'moc'  => '#228B22',
    'thuy' => '#1E90FF',
    'hoa'  => '#FF4500',
    'tho'  => '#8B4513',
    'all'  => '#FFD700',
    'none' => '#8B0000',
);
$purposeNames = array(
    'pk'      => 'PK',
    'farm'    => 'Farm',
    'train'   => 'Tu Luyện',
    'utility' => 'Tiện Ích',
);
$purposeCss = array(
    'pk'      => 'pur-pk',
    'farm'    => 'pur-farm',
    'train'   => 'pur-train',
    'utility' => 'pur-utility',
);

if ($canshu != '') {
    switch ($canshu) {
        case 'buy_wg':
            // Tra theo wgid (key JSON là số thứ tự, KHÔNG trùng wgid — tra theo key sẽ lấy nhầm bộ khác).
            $wgCfg = \player\wugong_config_get(intval($buy_id));
            if (!$wgCfg) {
                $tssb = wg_popup_html('error', 'Võ công không tồn tại!');
                break;
            }
            $realmReq = isset($wgCfg['realm_require']) ? intval($wgCfg['realm_require']) : 0;
            $priceLT = isset($wgCfg['price_linhthong']) ? intval($wgCfg['price_linhthong']) : 0;
            $priceMT = isset($wgCfg['price_masthach']) ? intval($wgCfg['price_masthach']) : 0;

            if ($jj < $realmReq) {
                $reqName = isset($rangesjjClean[$realmReq]) ? $rangesjjClean[$realmReq] : 'Cảnh giới ' . $realmReq;
                $tssb = wg_popup_html('error', 'Cần đạt ' . $reqName . ' mới có thể mua!');
                break;
            }

            // Đợt D: võ công Tiên Đạo đổi bằng Tiên Đạo Tàn Quyển thay vì tiền
            $tdNeed = \player\tiendao_need(intval($buy_id));
            if ($tdNeed > 0) {
                $tdDjid = \player\tiendao_djid_for(intval($buy_id));   // làn 5+: có bộ đổi bằng quyển riêng
                if (!\player\deledjsum($tdDjid, $tdNeed, $sid, $dblj)) {
                    $tdRow = \player\getplayerdaoju($sid, $tdDjid, $dblj);
                    $tdDjCfg = \player\daoju_config_get($tdDjid);
                    $tssb = wg_popup_html('error', 'Không đủ ' . ($tdDjCfg ? $tdDjCfg['djname'] : 'Tàn Quyển') . '! (cần ' . $tdNeed . ', đang có ' . ($tdRow ? intval($tdRow->djsum) : 0) . ')');
                    break;
                }
            } elseif ($priceLT > 0) {
                if (intval($player->uyxb) < $priceLT) {
                    $tssb = wg_popup_html('error', 'Không đủ Linh thạch! (cần ' . number_format($priceLT) . ')');
                    break;
                }
                \player\changeyxb(2, $priceLT, $sid, $dblj);
            } elseif ($priceMT > 0) {
                if (intval($player->uczb) < $priceMT) {
                    $tssb = wg_popup_html('error', 'Không đủ Ma thạch! (cần ' . number_format($priceMT) . ')');
                    break;
                }
                \player\changeczb(2, $priceMT, $sid, $dblj);
            } else {
                $tssb = wg_popup_html('error', 'Vật phẩm này không thể mua!');
                break;
            }

            \player\cqwg(intval($buy_id), $sid, $dblj);
            $wgName = isset($wgCfg['wgname']) ? $wgCfg['wgname'] : 'Võ công';
            $tscg = wg_popup_html('success', 'Mua thành công: ' . $wgName);
            if (function_exists('player\\nhiemvu_main_update')) {
                \player\nhiemvu_main_update($sid, 'learn_book', $dblj); // Chính tuyến: mua bí tịch
            }
            \player\nhiemvu_coduyen_event($sid, 'learn_book', $dblj); // Cơ Duyên: cầu bí tịch
            $player = \player\getplayer($sid, $dblj);
            break;

        case 'buy_cp':
            $cpCfgAll = \player\congphap_config_all();
            $cpKey = (string)intval($buy_id);
            if (!isset($cpCfgAll[$cpKey])) {
                $tssb = wg_popup_html('error', 'Công pháp không tồn tại!');
                break;
            }
            $cpCfg = $cpCfgAll[$cpKey];
            $realmReq = isset($cpCfg['realm_require']) ? intval($cpCfg['realm_require']) : 0;
            $priceLT = isset($cpCfg['price_linhthong']) ? intval($cpCfg['price_linhthong']) : 0;
            $priceMT = isset($cpCfg['price_masthach']) ? intval($cpCfg['price_masthach']) : 0;

            if ($jj < $realmReq) {
                $reqName = isset($rangesjjClean[$realmReq]) ? $rangesjjClean[$realmReq] : 'Cảnh giới ' . $realmReq;
                $tssb = wg_popup_html('error', 'Cần đạt ' . $reqName . ' mới có thể mua!');
                break;
            }

            // Bán cả 2 loại tiền (giai 0–3): trả theo $tien, mặc định Ma thạch
            if ($priceLT > 0 && $priceMT > 0) {
                if ($tien === 'lt') $priceMT = 0; else $priceLT = 0;
            }
            if ($priceLT > 0) {
                if (intval($player->uyxb) < $priceLT) {
                    $tssb = wg_popup_html('error', 'Không đủ Linh thạch! (cần ' . number_format($priceLT) . ')');
                    break;
                }
                \player\changeyxb(2, $priceLT, $sid, $dblj);
            } elseif ($priceMT > 0) {
                if (intval($player->uczb) < $priceMT) {
                    $tssb = wg_popup_html('error', 'Không đủ Ma thạch! (cần ' . number_format($priceMT) . ')');
                    break;
                }
                \player\changeczb(2, $priceMT, $sid, $dblj);
            } else {
                $tssb = wg_popup_html('error', 'Vật phẩm này không thể mua!');
                break;
            }

            \player\cqcp(intval($buy_id), $sid, $dblj);
            $cpName = isset($cpCfg['cpname']) ? $cpCfg['cpname'] : 'Công pháp';
            $tscg = wg_popup_html('success', 'Mua thành công: ' . $cpName);
            if (function_exists('player\\nhiemvu_main_update')) {
                \player\nhiemvu_main_update($sid, 'learn_book', $dblj); // Chính tuyến: mua bí tịch
            }
            \player\nhiemvu_coduyen_event($sid, 'learn_book', $dblj); // Cơ Duyên: cầu bí tịch
            $player = \player\getplayer($sid, $dblj);
            break;
    }
}

$currentTab = isset($tab) ? $tab : 'wg';
if (!in_array($currentTab, array('wg', 'cp'))) {
    $currentTab = 'wg';
}

$wgCfgAll = \player\wugong_config_all();
$cpCfgAll = \player\congphap_config_all();

$tabWgCmd = $encode->encode("cmd=thiencoccac&tab=wg&sid=" . $sid);
$tabCpCmd = $encode->encode("cmd=thiencoccac&tab=cp&sid=" . $sid);

$itemsHtml = '';

if ($currentTab === 'wg') {
    $grouped = array();
    // Đợt D: số Tiên Đạo Tàn Quyển đang có (hiện cạnh giá đổi)
    // Số quyển đang có theo từng loại Tàn Quyển (Tiên Đạo, Hỗn Nguyên …) — đọc 1 lần mỗi loại
    $tdHaveBy = array();
    $tdNameBy = array();
    foreach ($wgCfgAll as $key => $cfg) {
        if (intval($cfg['id']) <= 0) continue;
        $cat = isset($cfg['category']) ? $cfg['category'] : 'noi_cong';
        $rarity = isset($cfg['rarity']) ? $cfg['rarity'] : 'nhap_mon';
        $realmReq = isset($cfg['realm_require']) ? intval($cfg['realm_require']) : 0;
        $priceLT = isset($cfg['price_linhthong']) ? intval($cfg['price_linhthong']) : 0;
        $priceMT = isset($cfg['price_masthach']) ? intval($cfg['price_masthach']) : 0;
        $trainMult = isset($cfg['training_time_mult']) ? floatval($cfg['training_time_mult']) : 1.0;
        $tdNeed = \player\tiendao_need(isset($cfg['wgid']) ? $cfg['wgid'] : 0);
        if ($priceLT <= 0 && $priceMT <= 0 && $tdNeed <= 0) continue;
        if ($tdNeed > 0) {
            $tdD = \player\tiendao_djid_for(isset($cfg['wgid']) ? $cfg['wgid'] : 0);
            if (!isset($tdHaveBy[$tdD])) {
                $tdR = \player\getplayerdaoju($sid, $tdD, $dblj);
                $tdHaveBy[$tdD] = $tdR ? intval($tdR->djsum) : 0;
                $tdC = \player\daoju_config_get($tdD);
                $tdNameBy[$tdD] = $tdC ? $tdC['djname'] : 'Tàn Quyển';
            }
        }
        if (!isset($grouped[$cat])) $grouped[$cat] = array();
        $grouped[$cat][] = array(
            'key' => $key,
            'cfg' => $cfg,
            'rarity' => $rarity,
            'realm_req' => $realmReq,
            'price_lt' => $priceLT,
            'price_mt' => $priceMT,
            'td_need' => $tdNeed,
            'td_djid' => $tdNeed > 0 ? \player\tiendao_djid_for(isset($cfg['wgid']) ? $cfg['wgid'] : 0) : 0,
            'train_mult' => $trainMult,
        );
    }

    foreach ($wgCatOrder as $cat) {
        if (!isset($grouped[$cat]) || count($grouped[$cat]) == 0) continue;
        $items = $grouped[$cat];
        $rarityOrder = array('nhap_mon', 'ha_thua', 'trung_cap', 'thuong_thua', 'than_cong', 'tien_dao', 'hon_nguyen', 'dai_dao');
        usort($items, function($a, $b) use ($rarityOrder) {
            $ai = array_search($a['rarity'], $rarityOrder);
            $bi = array_search($b['rarity'], $rarityOrder);
            if ($ai === false) $ai = 99;
            if ($bi === false) $bi = 99;
            return $ai - $bi;
        });

        $catLabel = isset($wgCatNames[$cat]) ? $wgCatNames[$cat] : $cat;
        $catCss = isset($wgCatCss[$cat]) ? $wgCatCss[$cat] : 'wcat-noi';

        $itemsHtml .= '<div class="tcg-tcc-tier">';
        $itemsHtml .= '<div class="tcg-tcc-tier-head tcg-tcc-wcat ' . $catCss . '">';
        $itemsHtml .= '<span class="tcg-tcc-tier-badge">' . $catLabel . '</span>';
        $itemsHtml .= '</div>';

        foreach ($items as $item) {
            $cfg = $item['cfg'];
            $rarity = $item['rarity'];
            $wgname = wg_safe_text($cfg['wgname']);
            $wgys = wg_safe_text($cfg['wgys']);
            $qualityName = wg_color_name($cfg['wgys']);
            $rarityLabel = isset($wgRarityNames[$rarity]) ? $wgRarityNames[$rarity] : $rarity;
            $rarityCss = isset($wgRarityCss[$rarity]) ? $wgRarityCss[$rarity] : 'wrar-nhap';
            $realmReq = $item['realm_req'];
            $priceLT = $item['price_lt'];
            $priceMT = $item['price_mt'];
            $trainMult = $item['train_mult'];
            $canBuy = ($jj >= $realmReq);
            $reqName = isset($rangesjjClean[$realmReq]) ? wg_safe_text($rangesjjClean[$realmReq]) : 'Lv' . $realmReq;
            $trainText = tcc_training_text($trainMult);

            $buyCmd = $encode->encode("cmd=thiencoccac&canshu=buy_wg&buy_type=wg&buy_id=" . $cfg['id'] . "&tab=wg&sid=" . $sid);

            $priceText = '';
            $priceTextPlain = '';
            if ($item['td_need'] > 0) {
                $priceText = '<span class="tcg-tcc-price mt">' . $item['td_need'] . ' Quyển</span>';
                $priceTextPlain = $item['td_need'] . ' ' . $tdNameBy[$item['td_djid']];
            } elseif ($priceLT > 0) {
                $priceText = '<span class="tcg-tcc-price lt">' . number_format($priceLT) . ' LT</span>';
                $priceTextPlain = number_format($priceLT) . ' Linh thạch';
            } else {
                $priceText = '<span class="tcg-tcc-price mt">' . number_format($priceMT) . ' MT</span>';
                $priceTextPlain = number_format($priceMT) . ' Ma thạch';
            }

            $disabledClass = $canBuy ? '' : ' disabled';

            $itemsHtml .= '<div class="tcg-tcc-item' . $disabledClass . '" data-f="' . $rarity . '" data-ok="' . ($canBuy ? 1 : 0) . '">';
            $itemsHtml .= '<div class="tcg-tcc-item-color" style="background:' . $wgys . '"></div>';
            $itemsHtml .= '<div class="tcg-tcc-item-info">';
            $itemsHtml .= '<div class="tcg-tcc-item-name" style="color:' . $wgys . '">' . $wgname . '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-meta">';
            $itemsHtml .= '<span class="tcg-tcc-rarity-badge ' . $rarityCss . '">' . $rarityLabel . '</span>';
            $elem = isset($cfg['element']) ? $cfg['element'] : 'none';
            $elemLabel = isset($elementNames[$elem]) ? $elementNames[$elem] : $elem;
            $elemCss = isset($elementCss[$elem]) ? $elementCss[$elem] : 'elem-none';
            $elemColor = isset($elementColors[$elem]) ? $elementColors[$elem] : '#888';
            $itemsHtml .= '<span class="tcg-tcc-elem-badge ' . $elemCss . '" style="color:' . $elemColor . '">' . $elemLabel . '</span>';
            $purpose = isset($cfg['purpose']) ? $cfg['purpose'] : 'pk';
            $purLabel = isset($purposeNames[$purpose]) ? $purposeNames[$purpose] : $purpose;
            $purCss = isset($purposeCss[$purpose]) ? $purposeCss[$purpose] : 'pur-pk';
            $itemsHtml .= '<span class="tcg-tcc-purpose-badge ' . $purCss . '">' . $purLabel . '</span>';
            $itemsHtml .= '<span class="tcg-tcc-dot">·</span>';
            $itemsHtml .= '<span class="tcg-tcc-quality" style="color:' . $wgys . '">' . $qualityName . '</span>';
            if ($trainText !== '') {
                $itemsHtml .= '<span class="tcg-tcc-dot">·</span>';
                $itemsHtml .= '<span class="tcg-tcc-train">' . $trainText . '</span>';
            }
            $itemsHtml .= '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-sub">Yêu cầu: ' . $reqName . ($item['td_need'] > 0 ? ' · Đang có ' . $tdHaveBy[$item['td_djid']] . ' ' . wg_safe_text($tdNameBy[$item['td_djid']]) : '') . '</div>';
            $itemsHtml .= '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-right">';
            $itemsHtml .= $priceText;
            $confirmOpts = array(
                'msg' => 'Mua ' . $wgname . ' với ' . $priceTextPlain . '?',
                'title' => 'Mua Võ Công',
                'icon' => '⚔',
                'confirmText' => 'Mua ngay',
                'type' => 'info',
            );
            $confirmJson = htmlspecialchars(json_encode($confirmOpts, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            if ($canBuy) {
                $itemsHtml .= '<a class="tcg-tcc-buy-btn" href="?cmd=' . $buyCmd . '" onclick="return false;" data-wg-click=\'' . $confirmJson . '\'>Mua</a>';
            } else {
                $itemsHtml .= '<span class="tcg-tcc-lock">🔒</span>';
            }
            $itemsHtml .= '</div>';
            $itemsHtml .= '</div>';
        }
        $itemsHtml .= '</div>';
    }
} else {
    $grouped = array();
    foreach ($cpCfgAll as $key => $cfg) {
        $cpid = intval($cfg['cpid']);
        if ($cpid <= 0) continue;
        $giai = isset($cfg['tier']) ? intval($cfg['tier']) : 1;
        $pham = isset($cfg['subtier']) ? intval($cfg['subtier']) : 1;
        $realmReq = isset($cfg['realm_require']) ? intval($cfg['realm_require']) : 0;
        $priceLT = isset($cfg['price_linhthong']) ? intval($cfg['price_linhthong']) : 0;
        $priceMT = isset($cfg['price_masthach']) ? intval($cfg['price_masthach']) : 0;
        $trainMult = isset($cfg['training_time_mult']) ? floatval($cfg['training_time_mult']) : 1.0;
        if ($priceLT <= 0 && $priceMT <= 0) continue;
        if (!isset($grouped[$giai])) $grouped[$giai] = array();
        $grouped[$giai][] = array(
            'key' => $key,
            'cfg' => $cfg,
            'pham' => $pham,
            'realm_req' => $realmReq,
            'price_lt' => $priceLT,
            'price_mt' => $priceMT,
            'train_mult' => $trainMult,
        );
    }
    ksort($grouped);

    foreach ($grouped as $giai => $items) {
        usort($items, function($a, $b) { return $a['pham'] - $b['pham']; });

        $giaiLabel = isset($giaiNames[$giai]) ? $giaiNames[$giai] : 'Giai ' . $giai;
        $cssClass = isset($giaiCss[$giai]) ? $giaiCss[$giai] : 'hoang';
        $currencyName = isset($giaiCurrency[$giai]) ? $giaiCurrency[$giai] : 'Ma Thạch';

        $itemsHtml .= '<div class="tcg-tcc-tier">';
        $itemsHtml .= '<div class="tcg-tcc-tier-head tcg-tcc-tier-' . $cssClass . '">';
        $itemsHtml .= '<span class="tcg-tcc-tier-badge">' . $giaiLabel . '</span>';
        $itemsHtml .= '<span class="tcg-tcc-tier-currency">' . $currencyName . '</span>';
        $itemsHtml .= '</div>';

        foreach ($items as $item) {
            $cfg = $item['cfg'];
            $pham = $item['pham'];
            $cpname = wg_safe_text($cfg['cpname']);
            $cpys = wg_safe_text($cfg['cpys']);
            $cpsort = wg_safe_text($cfg['cpsort']);
            $fullName = tcc_full_name($giai, $pham);
            $realmReq = $item['realm_req'];
            $priceLT = $item['price_lt'];
            $priceMT = $item['price_mt'];
            $trainMult = $item['train_mult'];
            $canBuy = ($jj >= $realmReq);
            $reqName = isset($rangesjjClean[$realmReq]) ? wg_safe_text($rangesjjClean[$realmReq]) : 'Lv' . $realmReq;
            $trainText = tcc_training_text($trainMult);

            $buyCmd = $encode->encode("cmd=thiencoccac&canshu=buy_cp&buy_type=cp&buy_id=" . $cfg['cpid'] . "&tab=cp&sid=" . $sid);
            $isDual = ($priceLT > 0 && $priceMT > 0);

            $priceText = '';
            $priceTextPlain = '';
            if ($isDual) {
                $priceText = '';   // 2 nút mua, mỗi nút ghi giá riêng
            } elseif ($priceLT > 0) {
                $priceText = '<span class="tcg-tcc-price lt">' . number_format($priceLT) . ' LT</span>';
                $priceTextPlain = number_format($priceLT) . ' Linh thạch';
            } else {
                $priceText = '<span class="tcg-tcc-price mt">' . number_format($priceMT) . ' MT</span>';
                $priceTextPlain = number_format($priceMT) . ' Ma thạch';
            }

            $disabledClass = $canBuy ? '' : ' disabled';

            $itemsHtml .= '<div class="tcg-tcc-item' . $disabledClass . '" data-f="g' . $giai . '" data-ok="' . ($canBuy ? 1 : 0) . '">';
            $itemsHtml .= '<div class="tcg-tcc-item-color" style="background:' . $cpys . '"></div>';
            $itemsHtml .= '<div class="tcg-tcc-item-info">';
            $itemsHtml .= '<div class="tcg-tcc-item-name" style="color:' . $cpys . '">' . $cpname . '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-meta">';
            $itemsHtml .= '<span class="tcg-tcc-tier-label">' . $fullName . '</span>';
            $cpElem = isset($cfg['element']) ? $cfg['element'] : 'all';
            $cpElemLabel = isset($elementNames[$cpElem]) ? $elementNames[$cpElem] : $cpElem;
            $cpElemCss = isset($elementCss[$cpElem]) ? $elementCss[$cpElem] : 'elem-all';
            $cpElemColor = isset($elementColors[$cpElem]) ? $elementColors[$cpElem] : '#888';
            $itemsHtml .= '<span class="tcg-tcc-elem-badge ' . $cpElemCss . '" style="color:' . $cpElemColor . '">' . $cpElemLabel . '</span>';
            $itemsHtml .= '<span class="tcg-tcc-dot">·</span>';
            $itemsHtml .= '<span class="tcg-tcc-quality" style="color:' . $cpys . '">' . $cpsort . '</span>';
            if ($trainText !== '') {
                $itemsHtml .= '<span class="tcg-tcc-dot">·</span>';
                $itemsHtml .= '<span class="tcg-tcc-train">' . $trainText . '</span>';
            }
            $itemsHtml .= '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-sub">Yêu cầu: ' . $reqName . '</div>';
            $itemsHtml .= '</div>';
            $itemsHtml .= '<div class="tcg-tcc-item-right">';
            $itemsHtml .= $priceText;
            $cpConfirmOpts = array(
                'msg' => 'Mua ' . $cpname . ' với ' . $priceTextPlain . '?',
                'title' => 'Mua Công Pháp',
                'icon' => '📖',
                'confirmText' => 'Mua ngay',
                'type' => 'info',
            );
            $cpConfirmJson = htmlspecialchars(json_encode($cpConfirmOpts, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            if ($canBuy && $isDual) {
                $itemsHtml .= '<div class="tcg-tcc-dual">';
                foreach (array('mt' => array($priceMT, 'Ma thạch', 'MT'), 'lt' => array($priceLT, 'Linh thạch', 'LT')) as $tk => $tv) {
                    $dualCmd = $encode->encode("cmd=thiencoccac&canshu=buy_cp&buy_type=cp&buy_id=" . $cfg['cpid'] . "&tien=" . $tk . "&tab=cp&sid=" . $sid);
                    $dualOpts = $cpConfirmOpts;
                    $dualOpts['msg'] = 'Mua ' . $cpname . ' với ' . number_format($tv[0]) . ' ' . $tv[1] . '?';
                    $dualJson = htmlspecialchars(json_encode($dualOpts, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $itemsHtml .= '<a class="tcg-tcc-buy-btn ' . $tk . '" href="?cmd=' . $dualCmd . '" onclick="return false;" data-wg-click=\'' . $dualJson . '\'>' . number_format($tv[0]) . ' ' . $tv[2] . '</a>';
                }
                $itemsHtml .= '</div>';
            } elseif ($canBuy) {
                $itemsHtml .= '<a class="tcg-tcc-buy-btn" href="?cmd=' . $buyCmd . '" onclick="return false;" data-wg-click=\'' . $cpConfirmJson . '\'>Mua</a>';
            } else {
                $itemsHtml .= '<span class="tcg-tcc-lock">🔒</span>';
            }
            $itemsHtml .= '</div>';
            $itemsHtml .= '</div>';
        }
        $itemsHtml .= '</div>';
    }
}

// Thanh lọc (chỉ ẩn/hiện phía trình duyệt): Mua được / Tất cả / theo phẩm (võ công) hoặc giai (công pháp).
$tccFilters = array('ok' => 'Mua được', 'all' => 'Tất cả');
if ($currentTab === 'wg') {
    foreach ($wgRarityNames as $rk => $rn) $tccFilters[$rk] = $rn;
} else {
    foreach ($giaiNames as $gk => $gn) $tccFilters['g' . $gk] = $gn;
}
$tccFilterHtml = '';
foreach ($tccFilters as $fk => $fn) {
    $tccFilterHtml .= '<a class="tcg-tcc-chip" data-k="' . $fk . '" href="javascript:void(0)" onclick="tccFilter(\'' . $fk . '\')">' . $fn . '</a>';
}

$tabWgActive = ($currentTab === 'wg') ? ' active' : '';
$tabCpActive = ($currentTab === 'cp') ? ' active' : '';

$html = <<<HTML
<div class="tcg-tcc-page">
    <div class="tcg-wg-hero" style="background:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url('./images/bg/thiencocac.png') center/cover no-repeat!important;grid-template-columns:1fr!important;padding:32px 18px!important">
        <div class="tcg-wg-hero-info" style="position:relative!important;z-index:2!important">
            <div class="tcg-wg-kicker" style="text-shadow:0 1px 6px rgba(0,0,0,.7)!important">Thương Lương Bát Vạn</div>
            <h2 style="text-shadow:0 2px 10px rgba(0,0,0,.8)!important">Thiên Cơ Các</h2>
            <p style="text-shadow:0 1px 4px rgba(0,0,0,.6)!important">Chốn lưu trữ bảo kinh thiên hạ, nơi tu sĩ tìm kiếm bí tịch võ công và công pháp tu luyện.</p>
            <div class="tcg-wg-current" style="text-align:left!important;display:block!important;font-size:13px!important;width: 50%;">
                <span style="display:inline!important;margin:0 25px 0 0!important">Cảnh giới</span><b style="display:inline!important;margin:0!important">{$playerRealmName}</b>
            </div>
        </div>
    </div>

    <div class="tcg-tcc-tabs">
        <a class="tcg-tcc-tab {$tabWgActive}" href="?cmd={$tabWgCmd}">⚔ Võ Công</a>
        <a class="tcg-tcc-tab {$tabCpActive}" href="?cmd={$tabCpCmd}">📖 Công Pháp</a>
    </div>

    {$tssb}
    {$tscg}

    <div class="tcg-tcc-filter">{$tccFilterHtml}</div>

    <div class="tcg-tcc-list" id="tcc-list">
        {$itemsHtml}
    </div>
    <div class="tcg-tcc-empty" id="tcc-empty" style="display:none">Không có bí tịch nào trong mục này.</div>
    <script>
    function tccFilter(k){
        var list=document.getElementById('tcc-list'); if(!list) return;
        var tiers=list.querySelectorAll('.tcg-tcc-tier'), shown=0;
        for(var i=0;i<tiers.length;i++){
            var items=tiers[i].querySelectorAll('.tcg-tcc-item'), n=0;
            for(var j=0;j<items.length;j++){
                var it=items[j], ok=(k==='all')||(k==='ok'&&it.getAttribute('data-ok')==='1')||it.getAttribute('data-f')===k;
                it.style.display=ok?'':'none'; if(ok) n++;
            }
            tiers[i].style.display=n?'':'none'; shown+=n;
        }
        document.getElementById('tcc-empty').style.display=shown?'none':'block';
        var chips=document.querySelectorAll('.tcg-tcc-chip');
        for(var c=0;c<chips.length;c++) chips[c].className='tcg-tcc-chip'+(chips[c].getAttribute('data-k')===k?' on':'');
        return shown;
    }
    // Mặc định chỉ hiện bí tịch mua được; nếu không có thì hiện tất cả.
    if(!tccFilter('ok')) tccFilter('all');
    </script>

    <div class="tcg-tcc-footer">
        <a class="tcg-wg-navbtn" href="#" onClick="javascript:history.back(-1);">Trở về</a>
        <a class="tcg-wg-navbtn primary" href="game.php?cmd={$gonowmid}">Đại sảnh</a>
    </div>
</div>
HTML;

echo $html;
echo wg_confirm_modal();
?>
<script>
document.querySelectorAll('[data-wg-click]').forEach(function(el){
    el.addEventListener('click', function(e){
        e.preventDefault();
        try {
            var opts = JSON.parse(this.getAttribute('data-wg-click'));
        } catch(ex) { return; }
        var url = this.getAttribute('href');
        opts.onYes = function(){ window.location.href = url; };
        wgConfirm(opts);
    });
});
</script>
<style>
.tcg-tcc-page { padding-bottom: 80px; }
.tcg-tcc-tabs {
    display: flex;
    gap: 10px;
    padding: 0 16px;
    margin-bottom: 16px;
}
.tcg-tcc-tab {
    flex: 1;
    text-align: center;
    padding: 14px 10px;
    font-size: 15px;
    font-weight: 1000;
    color: #7a8ba8;
    text-decoration: none;
    border-radius: 14px;
    border: 1.5px solid rgba(125,211,252,.15);
    background: linear-gradient(180deg, rgba(15,23,62,.6), rgba(8,16,44,.7));
    transition: all .25s;
    position: relative;
    overflow: hidden;
}
.tcg-tcc-tab::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 14px;
    opacity: 0;
    transition: opacity .25s;
    background: radial-gradient(circle at 50% 0%, rgba(56,189,248,.15), transparent 70%);
}
.tcg-tcc-tab:hover::before { opacity: 1; }
.tcg-tcc-tab:hover {
    color: #93c5fd;
    border-color: rgba(125,211,252,.30);
}
.tcg-tcc-tab.active {
    color: #fff;
    border-color: rgba(56,189,248,.55);
    background: linear-gradient(180deg, rgba(14,165,233,.18), rgba(168,85,247,.12));
    box-shadow: 0 0 20px rgba(56,189,248,.18), inset 0 1px 0 rgba(255,255,255,.08);
    text-shadow: 0 0 14px rgba(56,189,248,.55);
}
.tcg-tcc-tab.active::before {
    opacity: 1;
    background: radial-gradient(circle at 50% 0%, rgba(56,189,248,.22), transparent 70%);
}

.tcg-tcc-list { padding: 0 12px; }

.tcg-tcc-tier { margin-bottom: 16px; }
.tcg-tcc-tier-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    border-radius: 10px;
    margin-bottom: 8px;
}
.tcg-tcc-tier-hoang {
    background: linear-gradient(135deg, rgba(192,192,192,.15), rgba(192,192,192,.05));
    border: 1px solid rgba(192,192,192,.30);
}
.tcg-tcc-tier-huyen {
    background: linear-gradient(135deg, rgba(56,189,248,.15), rgba(56,189,248,.05));
    border: 1px solid rgba(56,189,248,.30);
}
.tcg-tcc-tier-dia {
    background: linear-gradient(135deg, rgba(16,191,97,.15), rgba(16,191,97,.05));
    border: 1px solid rgba(16,191,97,.30);
}
.tcg-tcc-tier-thien {
    background: linear-gradient(135deg, rgba(249,197,8,.15), rgba(249,197,8,.05));
    border: 1px solid rgba(249,197,8,.30);
}
.tcg-tcc-tier-tien {
    background: linear-gradient(135deg, rgba(167,139,250,.15), rgba(167,139,250,.05));
    border: 1px solid rgba(167,139,250,.30);
}
.tcg-tcc-tier-than {
    background: linear-gradient(135deg, rgba(255,49,0,.15), rgba(255,49,0,.05));
    border: 1px solid rgba(255,49,0,.30);
}
.tcg-tcc-tier-thanh {
    background: linear-gradient(135deg, rgba(255,215,0,.18), rgba(255,215,0,.06));
    border: 1px solid rgba(255,215,0,.35);
}
/* Làn 5: Đế Giai */
.tcg-tcc-tier-de {
    background: linear-gradient(135deg, rgba(192,132,252,.22), rgba(192,132,252,.06));
    border: 1px solid rgba(192,132,252,.45);
}
/* Phàm Giai (nhập môn) */
.tcg-tcc-tier-pham {
    background: linear-gradient(135deg, rgba(158,158,158,.15), rgba(158,158,158,.05));
    border: 1px solid rgba(158,158,158,.30);
}
/* Làn 9: Chí Tôn Giai */
.tcg-tcc-tier-chiton {
    background: linear-gradient(135deg, rgba(255,23,68,.22), rgba(255,23,68,.06));
    border: 1px solid rgba(255,23,68,.45);
}
/* Làn 7: Đạo Giai */
.tcg-tcc-tier-dao {
    background: linear-gradient(135deg, rgba(255,179,0,.22), rgba(255,179,0,.06));
    border: 1px solid rgba(255,179,0,.45);
}
.tcg-tcc-wcat.tcg-tcc-tier-head { justify-content: flex-start; }
.wcat-khinh {
    background: linear-gradient(135deg, rgba(56,189,248,.15), rgba(56,189,248,.05));
    border: 1px solid rgba(56,189,248,.30);
}
.wcat-noi {
    background: linear-gradient(135deg, rgba(167,139,250,.15), rgba(167,139,250,.05));
    border: 1px solid rgba(167,139,250,.30);
}
.wcat-ngoai {
    background: linear-gradient(135deg, rgba(255,49,0,.15), rgba(255,49,0,.05));
    border: 1px solid rgba(255,49,0,.30);
}
.wcat-tap {
    background: linear-gradient(135deg, rgba(255,215,0,.15), rgba(255,215,0,.05));
    border: 1px solid rgba(255,215,0,.30);
}
.tcg-tcc-rarity-badge {
    display: inline-block;
    padding: 1px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .3px;
}
.wrar-nhap {
    background: rgba(148,163,184,.20);
    color: #94a3b8;
    border: 1px solid rgba(148,163,184,.30);
}
.tcg-tcc-filter { display:flex; gap:6px; overflow-x:auto; padding:2px 0 10px; margin-bottom:4px; -webkit-overflow-scrolling:touch; }
/* Ẩn thanh cuộn ngang mặc định; desktop: chip tự xuống dòng, mobile: vuốt ngang (mờ mép phải ở css/mystyle.css UI-1) */
.tcg-tcc-filter { scrollbar-width:none; }
.tcg-tcc-filter::-webkit-scrollbar { display:none; }
@media (min-width:761px) { .tcg-tcc-filter { flex-wrap:wrap; overflow-x:visible; } }
.main a.tcg-tcc-chip, .tcg-tcc-chip { flex:0 0 auto; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; text-decoration:none; color:#cbd5e1 !important; background:rgba(30,41,59,.85); border:1px solid rgba(148,163,184,.35); }
.main a.tcg-tcc-chip.on, .tcg-tcc-chip.on { color:#1e1b4b !important; background:linear-gradient(135deg,#fde68a,#f59e0b); border-color:#fde68a; }
.tcg-tcc-empty { text-align:center; color:#94a3b8; padding:20px 0; }
.wrar-ha {
    background: rgba(56,189,248,.16);
    color: #7dd3fc;
    border: 1px solid rgba(56,189,248,.30);
}
.wrar-tien {
    background: rgba(255,49,0,.18);
    color: #ff7a59;
    border: 1px solid rgba(255,49,0,.40);
    text-shadow: 0 0 8px rgba(255,49,0,.35);
}
/* Làn 8: phẩm Đại Đạo (trên Hỗn Nguyên) */
.wrar-dd {
    background: rgba(253,224,71,.18);
    color: #fef08a;
    border: 1px solid rgba(253,224,71,.55);
    text-shadow: 0 0 10px rgba(253,224,71,.6);
}
/* Làn 5: phẩm Hỗn Nguyên (trên Tiên Đạo) */
.wrar-hn {
    background: rgba(168,85,247,.20);
    color: #e9d5ff;
    border: 1px solid rgba(192,132,252,.55);
    text-shadow: 0 0 10px rgba(192,132,252,.6);
}
.wrar-trung {
    background: rgba(16,191,97,.18);
    color: #10bf61;
    border: 1px solid rgba(16,191,97,.30);
}
.wrar-thuong {
    background: rgba(167,139,250,.18);
    color: #a78bfa;
    border: 1px solid rgba(167,139,250,.30);
}
.wrar-than {
    background: rgba(255,215,0,.18);
    color: #ffd700;
    border: 1px solid rgba(255,215,0,.35);
    text-shadow: 0 0 8px rgba(255,215,0,.3);
}
.tcg-tcc-elem-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .3px;
}
.elem-kim { background: rgba(192,192,192,.18); border: 1px solid rgba(192,192,192,.30); }
.elem-moc { background: rgba(34,139,34,.18); border: 1px solid rgba(34,139,34,.30); }
.elem-thuy { background: rgba(30,144,255,.18); border: 1px solid rgba(30,144,255,.30); }
.elem-hoa { background: rgba(255,69,0,.18); border: 1px solid rgba(255,69,0,.30); }
.elem-tho { background: rgba(139,69,19,.18); border: 1px solid rgba(139,69,19,.30); }
.elem-all { background: rgba(255,215,0,.18); border: 1px solid rgba(255,215,0,.30); }
.elem-none { background: rgba(139,0,0,.18); border: 1px solid rgba(139,0,0,.30); }
.tcg-tcc-purpose-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .3px;
}
.pur-pk { background: rgba(255,49,0,.18); color: #ff3100; border: 1px solid rgba(255,49,0,.30); }
.pur-farm { background: rgba(16,191,97,.18); color: #10bf61; border: 1px solid rgba(16,191,97,.30); }
.pur-train { background: rgba(56,189,248,.18); color: #38bdf8; border: 1px solid rgba(56,189,248,.30); }
.pur-utility { background: rgba(148,163,184,.18); color: #94a3b8; border: 1px solid rgba(148,163,184,.30); }
.tcg-tcc-tier-badge {
    font-size: 14px;
    font-weight: 1000;
    color: #e2e8f0;
    letter-spacing: .5px;
    text-shadow: 0 0 8px rgba(255,255,255,.15);
}
.tcg-tcc-tier-currency {
    font-size: 11px;
    font-weight: 800;
    color: #94a3b8;
}

.tcg-tcc-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 6px;
    border-radius: 12px;
    border: 1px solid rgba(93,151,255,.12);
    background: radial-gradient(circle at 0% 0%,rgba(56,189,248,.06),transparent 38%),
                linear-gradient(180deg,rgba(18,36,82,.65),rgba(11,24,62,.72));
    transition: border-color .2s, background .2s;
}
.tcg-tcc-item:hover {
    border-color: rgba(125,211,252,.35);
}
.tcg-tcc-item.disabled {
    opacity: .5;
}
.tcg-tcc-item-color {
    width: 4px;
    height: 42px;
    border-radius: 2px;
    flex-shrink: 0;
}
.tcg-tcc-item-info {
    flex: 1;
    min-width: 0;
}
.tcg-tcc-item-name {
    font-size: 14px;
    font-weight: 1000;
    text-shadow: 0 0 8px rgba(255,255,255,.12), 0 0 14px currentColor;
    /* Tên dài (vd Tiên Đạo) xuống dòng thay vì bị cắt "…" */
    white-space: normal;
    overflow-wrap: break-word;
    line-height: 1.3;
}
.tcg-tcc-item-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    color: #7a8ba8;
    margin-top: 2px;
    flex-wrap: wrap;
}
.tcg-tcc-tier-label {
    color: #c0a050;
    font-weight: 1000;
}
.tcg-tcc-quality { font-weight: 1000; }
.tcg-tcc-train { color: #6ee7b7; font-weight: 800; }
.tcg-tcc-dot { opacity: .4; }
.tcg-tcc-item-sub {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    margin-top: 1px;
}
.tcg-tcc-item-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.tcg-tcc-price {
    font-size: 12px;
    font-weight: 1000;
    white-space: nowrap;
}
.tcg-tcc-price.lt { color: #38bdf8; }
.tcg-tcc-price.mt { color: #a78bfa; }
/* Giai 0–3: 2 nút mua (Ma thạch / Linh thạch) xếp dọc */
.tcg-tcc-dual { display: flex; flex-direction: column; gap: 4px; }
.tcg-tcc-dual .tcg-tcc-buy-btn { padding: 4px 10px; font-size: 11px; text-align: center; white-space: nowrap; }
.tcg-tcc-dual .tcg-tcc-buy-btn.lt { background: linear-gradient(135deg, #0ea5e9, #38bdf8); box-shadow: 0 0 10px rgba(56,189,248,.25); }
.tcg-tcc-buy-btn {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 1000;
    color: #fff;
    text-decoration: none;
    background: linear-gradient(135deg, #f97316, #eab308);
    box-shadow: 0 0 10px rgba(249,115,22,.25);
    transition: filter .18s, transform .18s;
}
.tcg-tcc-buy-btn:hover {
    filter: brightness(1.15);
    box-shadow: 0 0 18px rgba(249,115,22,.45);
}
.tcg-tcc-buy-btn:active { transform: scale(.95); }
.tcg-tcc-lock {
    font-size: 16px;
    opacity: .5;
}

.tcg-tcc-footer {
    display: flex;
    gap: 10px;
    padding: 16px;
    justify-content: center;
}
</style>