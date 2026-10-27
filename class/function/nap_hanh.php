<?php
namespace player;

/**
 * nap_hanh.php — Engine rèn nạp ngũ hành cho trang bị (đa hệ tối đa 5, không đè).
 * Spec: docs/DESIGN_NGUHANH_KICHHOAT.md
 */

if (!function_exists(__NAMESPACE__ . '\\zhuangbei_elements_parse')) {
    function zhuangbei_elements_parse($str) {
        if (!is_string($str) || $str === '' || $str === 'none') return array();
        $parts = array_map('trim', explode(',', $str));
        $out = array();
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, $parts, true)) $out[] = $el;
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\zhuangbei_elements_join')) {
    function zhuangbei_elements_join($arr) {
        if (!is_array($arr)) return 'none';
        $out = array();
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, $arr, true)) $out[] = $el;
        }
        return empty($out) ? 'none' : implode(',', $out);
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_cfg')) {
    function nguhanh_cfg() {
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/nguhanh_config.json');
        $cfg = $json ? json_decode($json, true) : false;
        if (!is_array($cfg)) {
            // Fallback đúng giá trị trong class/config/nguhanh_config.json
            $cfg = array(
                'tiers' => array(1, 3, 6, 9, 12),
                'bonus_pct' => array(3, 6, 10, 15, 22),
                'stones' => array('hoa' => 32, 'thuy' => 33, 'moc' => 34, 'kim' => 35, 'tho' => 36),
                'elements' => array('kim' => 'ugj', 'moc' => 'ufy', 'thuy' => 'uxx', 'hoa' => 'ubj', 'tho' => 'umaxhp'),
                'linhcan_bonus' => array('kim' => 15, 'moc' => 5, 'thuy' => 0, 'hoa' => 10, 'tho' => 8, 'all' => 10, 'none' => 0),
                'base_rate' => 90, 'rate_step' => 10, 'rate_floor' => 40, 'rate_cap' => 95, 'thach_base' => 500,
            );
        }
        $cache = $cfg;
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_cost')) {
    // $he là hệ sắp nạp thêm. Đá = 1 + n hệ hiện có; linh thạch = thach_base * rarity * (1 + n).
    // Trả stone_djid = 0 khi $he không hợp lệ hoặc món đã mang hệ đó.
    function nap_hanh_cost($itemElementStr, $rarity, $he) {
        $cfg = nguhanh_cfg();
        $cur = zhuangbei_elements_parse($itemElementStr);
        $n = count($cur);
        $zero = array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => $n);
        if (!isset($cfg['stones'][$he])) return $zero;
        if (in_array($he, $cur, true)) return $zero;
        $qty = 1 + $n;
        return array(
            'stone_djid' => intval($cfg['stones'][$he]),
            'stone_qty'  => $qty,
            'linhthach'  => intval($cfg['thach_base']) * max(1, intval($rarity)) * $qty,
            'n'          => $n,
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_rate')) {
    function nap_hanh_rate($itemElementStr, $linhcanElement) {
        $cfg = nguhanh_cfg();
        $n = count(zhuangbei_elements_parse($itemElementStr));
        $bonus = isset($cfg['linhcan_bonus'][$linhcanElement]) ? intval($cfg['linhcan_bonus'][$linhcanElement]) : 0;
        $rate = intval($cfg['base_rate']) - intval($cfg['rate_step']) * $n + $bonus;
        return min(intval($cfg['rate_cap']), max(intval($cfg['rate_floor']), $rate));
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_match_count')) {
    // Đếm số món đang mặc có element chứa hệ linh căn. 'all' hợp mọi hệ; 'none'/rỗng: 0.
    function nguhanh_match_count($player, $zbs) {
        $lc = (is_object($player) && isset($player->linhcan_element)) ? $player->linhcan_element : '';
        if ($lc === '' || $lc === 'none' || !is_array($zbs)) return 0;
        $n = 0;
        foreach ($zbs as $zb) {
            $els = zhuangbei_elements_parse(isset($zb->element) ? $zb->element : '');
            if (empty($els)) continue;
            if ($lc === 'all' || in_array($lc, $els, true)) $n++;
        }
        return $n;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_apply_passive')) {
    // Cộng % chỉ số theo nấc tiers. % áp trên TỔNG chỉ số trang bị của các món hợp căn
    // (không phải chỉ số gốc nhân vật) — spec §4. Ghi $player->nguhanh_info để Trạng thái show.
    function nguhanh_apply_passive($player, $zbs) {
        $player->nguhanh_info = array('match_count' => 0, 'tier' => 0, 'pct' => 0, 'gains' => array());
        $lc = isset($player->linhcan_element) ? $player->linhcan_element : '';
        if ($lc === '' || $lc === 'none' || !is_array($zbs) || empty($zbs)) return;
        $matched = array();
        foreach ($zbs as $zb) {
            $els = zhuangbei_elements_parse(isset($zb->element) ? $zb->element : '');
            if (empty($els)) continue;
            if ($lc === 'all' || in_array($lc, $els, true)) $matched[] = array($zb, $els);
        }
        $n = count($matched);
        if ($n === 0) return;
        $cfg = nguhanh_cfg();
        $tierIdx = -1;
        for ($t = 0; $t < count($cfg['tiers']); $t++) {
            if ($n >= intval($cfg['tiers'][$t])) $tierIdx = $t;
        }
        if ($tierIdx < 0) return;
        $pct = intval($cfg['bonus_pct'][$tierIdx]);
        $statOf = $cfg['elements']; // kim => ugj, thuy => uxx...
        $srcOf = array('ugj' => 'zbgj', 'ufy' => 'zbfy', 'umaxhp' => 'zbhp', 'ubj' => 'zbbj', 'uxx' => 'zbxx');
        $sums = array('zbgj' => 0, 'zbfy' => 0, 'zbhp' => 0, 'zbbj' => 0, 'zbxx' => 0);
        $elsInPlay = array();
        foreach ($matched as $m) {
            foreach ($sums as $k => $v) $sums[$k] += intval($m[0]->$k);
            foreach ($m[1] as $el) if (!in_array($el, $elsInPlay, true)) $elsInPlay[] = $el;
        }
        foreach ($elsInPlay as $el) {
            if (!isset($statOf[$el])) continue;
            $field = $statOf[$el];
            if (!isset($player->$field)) continue;
            $add = intval($sums[$srcOf[$field]] * $pct / 100);
            if ($add !== 0) {
                $player->$field += $add;
                $player->nguhanh_info['gains'][] = \player\zhuangbei_element_name($el) . ' +' . $add;
            }
        }
        $player->nguhanh_info['match_count'] = $n;
        $player->nguhanh_info['tier'] = $tierIdx + 1;
        $player->nguhanh_info['pct'] = $pct;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_do')) {
    function nap_hanh_do($sid, $zbnowid, $he, $dblj) {
        $out = array('ok' => false, 'msg' => '', 'rate' => 0, 'element' => 'none',
                     'he' => $he, 'cost' => array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 0));
        $cfg = nguhanh_cfg();
        if (!isset($cfg['stones'][$he])) { $out['msg'] = 'Hệ ngũ hành không hợp lệ.'; return $out; }

        $zbnowid = intval($zbnowid);
        $stmt = $dblj->prepare("SELECT sid, element FROM playerzhuangbei WHERE zbnowid = ? LIMIT 1");
        $stmt->execute(array($zbnowid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        // Món detach khi treo bán có sid='' — không bao giờ khớp sid người mua.
        if (!$row || $row['sid'] !== $sid) {
            $out['msg'] = 'Trang bị không tồn tại hoặc không thuộc về ngươi.';
            return $out;
        }
        $cur = zhuangbei_elements_parse($row['element']);
        $out['element'] = zhuangbei_elements_join($cur);
        $elName = \player\zhuangbei_element_name($he);
        if (in_array($he, $cur, true)) {
            $out['msg'] = "Món đã mang hệ {$elName}, không thể kích hoạt trùng.";
            return $out;
        }
        if (count($cur) >= 5) { $out['msg'] = 'Trang bị đã đủ 5 hệ ngũ hành.'; return $out; }

        $player = \player\getplayer($sid, $dblj);
        $zb = \player\getzb($zbnowid, $dblj);
        if (!is_object($zb)) { $out['msg'] = 'Không đọc được trang bị (config thiếu?).'; return $out; }
        $cost = nap_hanh_cost($row['element'], intval($zb->rarity), $he);
        $out['cost'] = $cost;
        $out['zbname'] = isset($zb->zbname) ? $zb->zbname : '';
        $out['zbys'] = isset($zb->zbys) ? $zb->zbys : '#fff';

        $stone = \player\getplayerdaoju($sid, $cost['stone_djid'], $dblj);
        $have = ($stone && isset($stone->djsum)) ? intval($stone->djsum) : 0;
        if ($have < $cost['stone_qty']) {
            $out['msg'] = "Không đủ Tinh Thạch hệ {$elName}: cần {$cost['stone_qty']}, có {$have}.";
            return $out;
        }
        if (intval($player->uyxb) < $cost['linhthach']) {
            $out['msg'] = "Linh thạch không đủ: cần {$cost['linhthach']}.";
            return $out;
        }

        // Spec §3.5: thất bại CHỈ mất nguyên liệu → trừ đá + linh thạch TRƯỚC khi roll.
        if (!\player\deledjsum($cost['stone_djid'], $cost['stone_qty'], $sid, $dblj)) {
            $out['msg'] = 'Tinh thạch đã bị tiêu hao ở thao tác khác, vui lòng thử lại.';
            return $out;
        }
        if (!\player\changeyxb(2, $cost['linhthach'], $sid, $dblj)) {
            \player\adddj($sid, $cost['stone_djid'], $cost['stone_qty'], $dblj); // hoàn đá, không mất kép
            $out['msg'] = 'Linh thạch không đủ.';
            return $out;
        }

        $rate = nap_hanh_rate($row['element'], isset($player->linhcan_element) ? $player->linhcan_element : '');
        $roll = isset($GLOBALS['nguhanh_force_roll']) ? intval($GLOBALS['nguhanh_force_roll']) : mt_rand(1, 100);
        $success = ($roll <= $rate);
        $out['rate'] = $rate;

        $dblj->prepare("INSERT INTO player_chetao_log (sid, recipe_id, success, created_at, log_type) VALUES (?, 0, ?, NOW(), 'nap_hanh')")
             ->execute(array($sid, $success ? 1 : 0));

        if (!$success) {
            $out['msg'] = "Kích hoạt hành {$elName} thất bại — đã mất nguyên liệu.";
            return $out;
        }

        $cur[] = $he;
        $newStr = zhuangbei_elements_join($cur);
        $upd = $dblj->prepare("UPDATE playerzhuangbei SET element = ? WHERE zbnowid = ? AND sid = ?");
        $upd->execute(array($newStr, $zbnowid, $sid));
        if ($upd->rowCount() === 0) {
            // Đổi chủ giữa lúc rèn (hiếm): hoàn đủ đá + linh thạch.
            \player\adddj($sid, $cost['stone_djid'], $cost['stone_qty'], $dblj);
            \player\changeyxb(1, $cost['linhthach'], $sid, $dblj);
            $out['msg'] = 'Trang bị đổi chủ giữa lúc rèn, đã hoàn nguyên liệu.';
            return $out;
        }
        \player\zhuangbei_cache_clear($zbnowid);
        \player\player_cache_clear($sid);
        $out['ok'] = true;
        $out['element'] = $newStr;
        $out['msg'] = "Kích hoạt hành {$elName} thành công!";
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_section_html')) {
    /**
     * Section "Thuộc tính Ngũ Hành" trong popup trang bị (game/zbinfo.php).
     * Caller đã kiểm người xem là chủ sở hữu món đồ.
     */
    function nap_hanh_section_html($zhuangbei, $player, $sid, $dblj, $encode) {
        $zbElem = isset($zhuangbei->element) ? $zhuangbei->element : 'none';
        $rarity = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
        $lc     = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $cur    = zhuangbei_elements_parse($zbElem);

        $curText = array();
        foreach ($cur as $el) {
            $curText[] = '<b style="color:' . zhuangbei_element_color_ui($el) . '">'
                . zhuangbei_element_icon($el) . ' ' . zhuangbei_element_name($el) . '</b>';
        }
        $curHtml = empty($curText) ? '<b class="nh-empty">Chưa có hành nào</b>' : implode(' ', $curText);

        $rows = '';
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, $cur, true)) continue;
            $c = nap_hanh_cost($zbElem, $rarity, $el);
            if ($c['stone_djid'] <= 0) continue;
            $rate  = nap_hanh_rate($zbElem, $lc);
            $stone = getplayerdaoju($sid, $c['stone_djid'], $dblj);
            $have  = ($stone && isset($stone->djsum)) ? intval($stone->djsum) : 0;
            $cmd   = $encode->encode('cmd=nap_hanh&zbnowid=' . $zhuangbei->zbnowid . '&he=' . $el . '&sid=' . $sid);
            $chip  = ($lc !== '' && $lc === $el) ? '<i class="nh-chip">hợp linh căn</i>' : '';
            $rows .= '<div class="nh-row">'
                . '<span class="nh-el" style="color:' . zhuangbei_element_color_ui($el) . '">'
                . zhuangbei_element_icon($el) . ' ' . zhuangbei_element_name($el) . $chip . '</span>'
                . '<span class="nh-cost' . ($have < $c['stone_qty'] ? ' bad' : '') . '">'
                . $c['stone_qty'] . ' đá (có ' . $have . ') · ' . $c['linhthach'] . ' LT'
                . ' · <span class="nh-rate">Tỉ lệ ' . $rate . '%</span></span>'
                . '<a class="nh-btn" href="?cmd=' . $cmd . '">Kích hoạt</a>'
                . '</div>';
        }
        if ($rows === '') {
            $rows = '<div class="nh-done">Đã đủ 5 hành — không kích hoạt thêm được nữa.</div>';
        }

        return '<div class="zb-section">'
            . '<div class="zb-section-title">Thuộc tính Ngũ Hành</div>'
            . '<div class="zb-row"><span class="nh-count">Đã có ' . count($cur) . '/5 hành</span><span class="nh-cur">' . $curHtml . '</span></div>'
            . '<div class="nh-list">' . $rows . '</div>'
            . '<div class="zb-desc">Hành trùng linh căn sẽ cộng chỉ số khi mặc. Thất bại chỉ mất nguyên liệu; treo bán / phân giải không hoàn lại nguyên liệu đã dùng.</div>'
            . '</div>';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_result_html')) {
    /**
     * Popup kết quả rèn ngũ hành (game/nap_hanh.php). Dung lại bo .pve-result-* cua mystyle.css.
     * $player phai la object doc SAU khi rua (getplayer() da gan nguhanh_info).
     */
    function nap_hanh_result_html($out, $zbnowid, $player, $sid, $dblj, $encode) {
        $he    = isset($out['he']) ? $out['he'] : '';
        $ok    = !empty($out['ok']);
        $rate  = isset($out['rate']) ? intval($out['rate']) : 0;
        $paid  = ($rate > 0);
        $cost  = isset($out['cost']) ? $out['cost'] : array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 0);
        $elems = zhuangbei_elements_parse(isset($out['element']) ? $out['element'] : 'none');
        $lc    = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $elName  = ($he !== '') ? zhuangbei_element_name($he) : 'Ngũ Hành';
        $elColor = zhuangbei_element_color_ui($he);
        $statLabels = array('kim' => 'Công kích', 'moc' => 'Phòng thủ', 'thuy' => 'Hút máu',
                            'hoa' => 'Bạo kích', 'tho' => 'Khí huyết');

        if ($ok)          { $state = 'win';   $stateText = 'Kích hoạt thành công'; }
        elseif ($paid)    { $state = 'lose';  $stateText = 'Rèn thất bại'; }
        else              { $state = 'block'; $stateText = 'Chưa thể kích hoạt'; }

        $headIcon = $ok ? zhuangbei_element_icon($he) : ($paid ? '✕' : '!');
        $headTint = $ok ? $elColor : ($paid ? '#ff6b7a' : '#94a3b8');

        $zbname = isset($out['zbname']) ? $out['zbname'] : '';
        $zbys   = (isset($out['zbys']) && $out['zbys'] !== '') ? $out['zbys'] : '#fff';

        $rows = '';
        if ($zbname !== '') {
            $rows .= '<div class="pve-result-row"><span>Trang bị</span><b style="color:' . $zbys . '">'
                  . htmlspecialchars($zbname, ENT_QUOTES, 'UTF-8') . '</b></div>';
        }
        if ($he !== '') {
            $rows .= '<div class="pve-result-row"><span>Hệ kích hoạt</span><b style="color:' . $elColor . '">'
                  . zhuangbei_element_icon($he) . ' ' . $elName . '</b></div>';
        }
        if ($paid) {
            $rows .= '<div class="pve-result-row"><span>Tỉ lệ rèn</span><b>' . $rate . '%</b></div>';
        }
        if ($paid && $cost['stone_qty'] > 0) {
            $stone = getplayerdaoju($sid, $cost['stone_djid'], $dblj);
            $have  = ($stone && isset($stone->djsum)) ? intval($stone->djsum) : 0;
            $rows .= '<div class="pve-result-row"><span>Tinh thạch đã dùng</span><b>-' . $cost['stone_qty']
                  . ' <em class="nh-remain">(còn ' . $have . ')</em></b></div>';
        }
        if ($paid && $cost['linhthach'] > 0) {
            $rows .= '<div class="pve-result-row"><span>Linh thạch đã dùng</span><b>-' . $cost['linhthach']
                  . ' <em class="nh-remain">(còn ' . intval($player->uyxb) . ')</em></b></div>';
        }

        $badges = '';
        foreach ($elems as $el) {
            $badges .= '<i class="nh-badge" style="color:' . zhuangbei_element_color_ui($el) . '">'
                    . zhuangbei_element_icon($el) . ' ' . zhuangbei_element_name($el) . '</i>';
        }
        if ($badges === '') $badges = '<b class="nh-empty">Chưa có hành nào</b>';
        $rows .= '<div class="pve-result-row pve-result-row--wrap"><span>Hệ đang mang (' . count($elems) . '/5)</span>'
              . '<b class="nh-badges">' . $badges . '</b></div>';

        $info = (isset($player->nguhanh_info) && is_array($player->nguhanh_info)) ? $player->nguhanh_info : null;
        if ($info && intval($info['match_count']) > 0) {
            $rows .= '<div class="pve-result-row"><span>Nội tại hợp linh căn</span><b>'
                  . intval($info['match_count']) . ' món · +' . intval($info['pct']) . '% '
                  . implode(', ', $info['gains']) . '</b></div>';
        }

        if ($ok && $lc !== '' && $lc === $he) {
            $hint = 'Hành ' . $elName . ' trùng linh căn — khi mặc, ' . $statLabels[$he] . ' được cộng thêm theo nấc nội tại.';
        } elseif ($ok) {
            $hint = 'Hành ' . $elName . ' chưa hợp linh căn hiện tại; nạp thêm hành trùng căn để mở nội tại.';
        } elseif ($paid) {
            $hint = 'Thất bại chỉ mất nguyên liệu — hệ của trang bị giữ nguyên, có thể rèn lại ngay.';
        } else {
            $hint = 'Bổ sung tinh thạch và linh thạch rồi quay lại rèn.';
        }

        $backCmd   = $encode->encode("cmd=chakanzb&zbnowid=$zbnowid&uid=$player->uid&sid=$sid");
        $bagzbCmd  = $encode->encode("cmd=getbagzb&sid=$sid");

        return '<div class="pve-result-wrap nh-result">'
             . '<div class="pve-result-card">'
             . '<div class="pve-result-head">'
             . '<div class="pve-result-icon" style="color:' . $headTint . ';border-color:' . $headTint . '">' . $headIcon . '</div>'
             . '<div class="pve-result-title">Rèn Ngũ Hành — ' . $elName . '</div>'
             . '<div class="pve-result-status ' . $state . '">' . $stateText . '</div>'
             . '</div>'
             . '<div class="pve-result-body">'
             . '<div class="pve-notice-text">' . htmlspecialchars(isset($out['msg']) ? $out['msg'] : '', ENT_QUOTES, 'UTF-8') . '</div>'
             . $rows
             . '<div class="pve-notice-hint">' . $hint . '</div>'
             . '</div>'
             . '<div class="pve-result-actions">'
             . '<a class="pve-main-btn" href="?cmd=' . $backCmd . '">Xem lại trang bị</a>'
             . '<a class="pve-main-btn nh-ghost" href="?cmd=' . $bagzbCmd . '">Túi trang bị</a>'
             . '</div>'
             . '</div></div>';
    }
}
?>
