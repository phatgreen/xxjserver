<?php

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$zbnowid = isset($zbnowid) ? intval($zbnowid) : 0;
$zhuangbei = $zbnowid > 0 ? player\getzb($zbnowid, $dblj) : false;

if (!is_object($zhuangbei)) {
    $html = <<<HTML
<div class="pve-notice-wrap">
    <div class="pve-notice-card">
        Trang bị không tồn tại hoặc đã bị phân giải.<br/>
        <a href="?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;
    echo $html;
    exit();
}

$arr = array(
    $player->tool1, $player->tool2, $player->tool3, $player->tool4,
    $player->tool5, $player->tool6, $player->tool7, $player->tool8,
    $player->tool9, $player->tool10, $player->tool11, $player->tool12,
    $player->tool13, $player->tool14, $player->tool15, $player->tool16
);

$setzbwz = '';
$upgj = '';
$upfy = '';
$uphp = '';
$upbj = '';
$upxx = '';
$upts = '';
$qhssum = '';
$uyxb = '';

$tpForgePct = isset($player->tianfu_bonus['forge_cost']) ? floatval($player->tianfu_bonus['forge_cost']) : 0;
$updjsl = intval($zhuangbei->qianghua * 3 + 1);
$upls = \player\tianfu_forge_cost($tpForgePct, intval(round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * round($zhuangbei->qianghua / 4) + 1));
$fjls = intval($zhuangbei->qianghua * 20 + 20);
$player_lv_limit = max(0, intval($player->ulv));
$qh_current = intval($zhuangbei->qianghua);
$qh_limit_reached = ($player_lv_limit <= 0 || $qh_current >= $player_lv_limit);

if (isset($canshu)) {
    // Chỉ chủ sở hữu mới được treo bán. Không chặn dòng này thì ai cũng có thể
    // đoán zbnowid (số tăng dần) để cướp trang bị của người khác.
    $isZbOwner = (intval($zhuangbei->uid) === intval($player->uid) && $zhuangbei->sid === $sid);
    if ($canshu == "chushou" && !$isZbOwner) {
        echo 'Ngươi không có trang bị này, không thể treo bán!';
    }
    if ($canshu == "chushou" && $isZbOwner && !in_array($zhuangbei->zbnowid, $arr) && isset($pay) && $pay > 0) {
        try {
            $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            $dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dblj->beginTransaction();

            $sql = "INSERT INTO `fangshi_zb`(zbname, zbinfo, zbgj, zbfy, zbbj, zbxx, tuposjs, tupoexp, zbid, uid, zbnowid, sid, zbhp, qianghua, zblv, pay, zbys, element) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array(
                $zhuangbei->zbname, $zhuangbei->zbinfo, $zhuangbei->zbgj, $zhuangbei->zbfy,
                $zhuangbei->zbbj, $zhuangbei->zbxx, $zhuangbei->tuposjs, $zhuangbei->tupoexp,
                $zhuangbei->zbid, $player->uid, $zbnowid, $sid, $zhuangbei->zbhp,
                $zhuangbei->qianghua, $zhuangbei->zblv, $pay, $zhuangbei->zbys,
                (isset($zhuangbei->element) ? $zhuangbei->element : 'none')
            ));
            $affected_rows = $stmt->rowCount();
            if (!$affected_rows) {
                throw new PDOException("Trang bị treo bán thất bại<br/>");
            }

            $sql = "UPDATE `playerzhuangbei` SET uid=0, sid='' WHERE zbnowid = ?";
            $stmt2 = $dblj->prepare($sql);
            $stmt2->execute(array($zbnowid));
            $affected_rows = $stmt2->rowCount();
            if (!$affected_rows) {
                throw new PDOException("Trang bị truyền tống thất bại<br/>");
            }

            echo "Treo bán thành công!<br/>";
            $dblj->commit();
        } catch (PDOException $e) {
            // Lỗi SQL không in ra trang (lộ cấu trúc DB) — ghi log, báo người chơi câu chung; thông báo tự đặt ở trên vẫn hiện
            $tbMsg = $e->getMessage();
            if (strpos($tbMsg, 'SQLSTATE') !== false) {
                error_log('zbinfo chushou: ' . $tbMsg);
                $tbMsg = 'Treo bán thất bại, vui lòng thử lại.<br/>';
            }
            echo $tbMsg;
            $dblj->rollBack();
        }

        $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
        $zhuangbei = player\getzb($zbnowid, $dblj);
    }
}

if ($player->uid == $zhuangbei->uid) {
    $uyxb = '/' . $player->uyxb;

    $daoju = player\getplayerdaoju($sid, 1, $dblj);
    $qhssum = '/0';
    $qhs_hienco = 0;
    if ($daoju && isset($daoju->djsum)) {
        $qhs_hienco = intval($daoju->djsum);
        $qhssum = '/' . $qhs_hienco;
    }

    if ($cmd == 'upzb') {
        $can_qhs = intval($updjsl);
        $can_ls = intval($upls);
        $co_ls = intval($player->uyxb);

        if ($qh_limit_reached) {
            $upts = "<font color='red'>Cấp cường hóa không được lớn hơn cấp nhân vật!</font><br/>";
            $upts .= "Cấp nhân vật hiện tại: $player_lv_limit, cường hóa hiện tại: +$qh_current<br/>";
        } elseif ($qhs_hienco < $can_qhs || $co_ls < $can_ls) {
            $upts = "<font color='red'>Không đủ nguyên liệu cường hóa!</font><br/>";

            if ($qhs_hienco < $can_qhs) {
                $upts .= "Cường hóa thạch không đủ: cần $can_qhs, hiện có $qhs_hienco<br/>";
            }

            if ($co_ls < $can_ls) {
                $upts .= "Linh thạch không đủ: cần $can_ls, hiện có $co_ls<br/>";
            }
        } else {
            $upsx = isset($upsx) ? $upsx : '';
            $ret = \player\upzbsx($zbnowid, $upsx, $sid, $dblj);

            if ($ret == -2) {
                $upts = "<font color='red'>Cấp cường hóa không được lớn hơn cấp nhân vật!</font><br/>";
                $upts .= "Cấp nhân vật hiện tại: $player_lv_limit, cường hóa hiện tại: +$qh_current<br/>";
            } elseif ($ret != -1) {
                \player\changeyxb(2, $can_ls, $sid, $dblj);

                if ($ret == 1) {
                    $upts = "<font color='green'>Cường hóa thành công!</font><br/>";
                } elseif ($ret == 0) {
                    $upts = "<font color='red'>Cường hóa thất bại!</font><br/>";
                }

                $zhuangbei = \player\getzb($zbnowid, $dblj);

                if ($ret == 1 && $zhuangbei && function_exists('player\\nhiemvu_update_all')) {
                    \player\nhiemvu_update_all($sid, 'upgrade', 1, $dblj, array(
                        'qianghua' => intval($zhuangbei->qianghua),
                        'slot'     => intval($zhuangbei->zbtool),
                    ));
                }

                $updjsl = intval($zhuangbei->qianghua * 3 + 1);
                $upls = \player\tianfu_forge_cost($tpForgePct, intval(round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * round($zhuangbei->qianghua / 4) + 1));
                $fjls = intval($zhuangbei->qianghua * 20 + 20);

                $player = \player\getplayer($sid, $dblj);
                $player_lv_limit = max(0, intval($player->ulv));
                $qh_current = intval($zhuangbei->qianghua);
                $qh_limit_reached = ($player_lv_limit <= 0 || $qh_current >= $player_lv_limit);
                $uyxb = '/' . $player->uyxb;

                $daoju = player\getplayerdaoju($sid, 1, $dblj);
                $qhssum = '/0';
                $qhs_hienco = 0;
                if ($daoju && isset($daoju->djsum)) {
                    $qhs_hienco = intval($daoju->djsum);
                    $qhssum = '/' . $qhs_hienco;
                }
            } else {
                $upts = "<font color='red'>Không đủ nguyên liệu cường hóa!</font><br/>";
            }
        }
    }

    $upgjcmd = $encode->encode("cmd=upzb&upsx=zbgj&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $upfycmd = $encode->encode("cmd=upzb&upsx=zbfy&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $uphpcmd = $encode->encode("cmd=upzb&upsx=zbhp&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $upbjcmd = $encode->encode("cmd=upzb&upsx=zbbj&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $upxxcmd = $encode->encode("cmd=upzb&upsx=zbxx&zbnowid=$zhuangbei->zbnowid&sid=$sid");

    $upgj = " <a href='?cmd=$upgjcmd'>Cường hóa công kích</a>";
    $upfy = " <a href='?cmd=$upfycmd'>Cường hóa phòng ngự</a>";
    $uphp = " <a href='?cmd=$uphpcmd'>Cường hóa HP</a>";
    $upbj = " <a href='?cmd=$upbjcmd'>Cường hóa bạo kích</a>";
    $upxx = " <a href='?cmd=$upxxcmd'>Cường hóa hút máu</a>";
}

if ($player->uid == $zhuangbei->uid && !in_array($zhuangbei->zbnowid, $arr)) {
    $player = \player\getplayer($sid, $dblj);
    $delezb = $encode->encode("cmd=delezb&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $selllb = $encode->encode("cmd=getbagzb&canshu=maichu&yeshu=0&zbnowid=$zhuangbei->zbnowid&sid=$sid");
    $self = $_SERVER['PHP_SELF'];

    $zbRealmReq = \player\zhuangbei_realm_require($zhuangbei);
    $zbEquipSlot = isset($zhuangbei->tool) ? intval($zhuangbei->tool) : 0;
    $slotOk = ($zbEquipSlot >= 1 && $zbEquipSlot <= 16);
    $canEquip = ($slotOk && \player\zhuangbei_realm_ok($zhuangbei, $player));
    $equipBtn = '';
    if ($canEquip) {
        $equipcmd = $encode->encode("cmd=setzbwz&zbwz=$zbEquipSlot&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $equipBtn = "<a class='zb-action-equip' href='?cmd=$equipcmd'>Trang bị</a>";
    } elseif ($slotOk) {
        $equipBtn = "<span class='zb-action-locked'>Cần cảnh giới cấp $zbRealmReq mới có thể trang bị</span>";
    } else {
        $equipBtn = "<span class='zb-action-locked'>Trang bị chưa xác định được vị trí lắp</span>";
    }

    $setzbwz = "<a class='zb-action-resolve' href='?cmd=$delezb'>Phân giải trang bị</a>$equipBtn";

    if (false && $zhuangbei->tool == 0) {
        $setzbwz1 = $encode->encode("cmd=setzbwz&zbwz=1&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz2 = $encode->encode("cmd=setzbwz&zbwz=2&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz3 = $encode->encode("cmd=setzbwz&zbwz=3&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz4 = $encode->encode("cmd=setzbwz&zbwz=4&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz5 = $encode->encode("cmd=setzbwz&zbwz=5&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz6 = $encode->encode("cmd=setzbwz&zbwz=6&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz7 = $encode->encode("cmd=setzbwz&zbwz=7&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz8 = $encode->encode("cmd=setzbwz&zbwz=8&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz9 = $encode->encode("cmd=setzbwz&zbwz=9&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz10 = $encode->encode("cmd=setzbwz&zbwz=10&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz11 = $encode->encode("cmd=setzbwz&zbwz=11&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz12 = $encode->encode("cmd=setzbwz&zbwz=12&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz13 = $encode->encode("cmd=setzbwz&zbwz=13&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz14 = $encode->encode("cmd=setzbwz&zbwz=14&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz15 = $encode->encode("cmd=setzbwz&zbwz=15&zbnowid=$zhuangbei->zbnowid&sid=$sid");
        $setzbwz16 = $encode->encode("cmd=setzbwz&zbwz=16&zbnowid=$zhuangbei->zbnowid&sid=$sid");

        $setzbwz = "
    <a href='?cmd=$setzbwz1'>Trang bị vị trí【Vũ khí】</a>
    <a href='?cmd=$setzbwz2'>Trang bị vị trí【Mũ】</a><br/>
    <a href='?cmd=$setzbwz3'>Trang bị vị trí【Y Phục】</a>
    <a href='?cmd=$setzbwz4'>Trang bị vị trí【Giày】</a><br/>
    <a href='?cmd=$setzbwz5'>Trang bị vị trí【Găng tay】</a>
    <a href='?cmd=$setzbwz6'>Trang bị vị trí【Đai lưng】</a><br/>
    <a href='?cmd=$setzbwz7'>Trang bị vị trí【Hạng liên】</a><br/>
    <a href='?cmd=$setzbwz8'>Trang bị vị trí【Nhẫn】</a>
    <a href='?cmd=$setzbwz9'>Trang bị vị trí【Vòng tay】</a><br/>
    <a href='?cmd=$setzbwz10'>Trang bị vị trí【Phi phong】</a>
    <a href='?cmd=$setzbwz11'>Trang bị vị trí【Cánh】</a><br/>
    <a href='?cmd=$setzbwz12'>Trang bị vị trí【Pháp bảo】</a>
    <a href='?cmd=$setzbwz13'>Trang bị vị trí【Ám khí】</a><br/>
    <a href='?cmd=$setzbwz14'>Trang bị vị trí【Ngọc Bội】</a>
    <a href='?cmd=$setzbwz15'>Trang bị vị trí【Bùa Chú】</a><br/>
    <a href='?cmd=$setzbwz16'>Trang bị vị trí【Tọa kỵ】</a>";
    }

    $setzbwz .= <<<HTML
    <a class="zb-action-sell" href="?cmd=$selllb">Bán</a>
    <br/>
    <form action="$self">
    <input type="hidden" name="cmd" value="chakanzb">
    <input type="hidden" name="canshu" value="chushou">
    <input type="hidden" name="sid" value="$sid">
    <input type="hidden" name="zbnowid" value="$zhuangbei->zbnowid">
    <div align="center">
    Đấu giá: <br/>
    <input type="number" name="pay">
    <input type="submit" value="Đấu giá">
    </div>
    </form>
HTML;
}

$qianghua = '';
if ($zhuangbei->qianghua > 0) {
    $qianghua = "+" . $zhuangbei->qianghua;
}

$qhcgl_num = round((30 - $zhuangbei->qianghua) / 30, 2) * 100;
if ($qhcgl_num < 0) {
    $qhcgl_num = 0;
}
$qhcgl = $qhcgl_num . '%';
$qhcgl_width = intval($qhcgl_num);
if ($qhcgl_width > 100) $qhcgl_width = 100;
if ($qhcgl_width < 0) $qhcgl_width = 0;

$tools = \player\zhuangbei_tool_names();
$tool = isset($tools[$zhuangbei->tool]) ? $tools[$zhuangbei->tool] : "Không rõ";

$owner = ($player->uid == $zhuangbei->uid);
$player_lv_limit = max(0, intval($player->ulv));
$qh_current = intval($zhuangbei->qianghua);
$qh_limit_reached = ($player_lv_limit <= 0 || $qh_current >= $player_lv_limit);
$uyxb_now = intval($player->uyxb);
$qhs_now = isset($qhs_hienco) ? intval($qhs_hienco) : 0;
$qhs_need = intval($updjsl);
$ls_need = intval($upls);
$fjls_need = intval($fjls);

$qhs_class = ($qhs_now >= $qhs_need) ? 'ok' : 'bad';
$ls_class = ($uyxb_now >= $ls_need) ? 'ok' : 'bad';
$qh_limit_class = $qh_limit_reached ? 'bad' : 'ok';
$can_upgrade = ($owner && !$qh_limit_reached && $qhs_now >= $qhs_need && $uyxb_now >= $ls_need);

$notice_html = '';
if (trim($upts) != '') {
    $notice_html = '<div class="zb-notice">' . $upts . '</div>';
}

$up_buttons = '';
if ($owner && !$qh_limit_reached) {
    $up_buttons = <<<HTML
<div class="zb-upgrade-grid">
    <a class="zb-up-btn" href="?cmd=$upgjcmd">Công kích</a>
    <a class="zb-up-btn" href="?cmd=$upfycmd">Phòng ngự</a>
    <a class="zb-up-btn" href="?cmd=$uphpcmd">HP</a>
    <a class="zb-up-btn" href="?cmd=$upbjcmd">Bạo kích</a>
    <a class="zb-up-btn" href="?cmd=$upxxcmd">Hút máu</a>
</div>
HTML;
} elseif ($owner) {
    $up_buttons = <<<HTML
<div class="zb-notice">Cấp cường hóa không được lớn hơn cấp nhân vật.</div>
HTML;
}

$setzbwz_html = '';
if (trim($setzbwz) != '') {
    $setzbwz_html = <<<HTML
<div class="zb-section zb-action-section">
    <div class="zb-section-title">Thao tác trang bị</div>
    <div class="zb-actions-legacy">
        $setzbwz
    </div>
</div>
HTML;
}
$htmlqianghua = '';
if ($qianghua != '') {
    $htmlqianghua = '<span class="zb-plus">' . $qianghua . '</span>';
}

// Badge nguyên tố + phẩm chất (hệ thống mới)
$zbElem = isset($zhuangbei->element) ? $zhuangbei->element : 'none';
$zbRarity = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
$zbRarityName = \player\zhuangbei_rarity_name($zbRarity);
$zbRarityColor = \player\zhuangbei_rarity_color($zbRarity);
$zbBadgeHtml = '<span class="zb-badge rarity" style="color:'.$zbRarityColor.'">'.$zbRarityName.'</span>';
$zbElemCard = '';
$zbElsList = \player\zhuangbei_elements_parse($zbElem);
$zbElemBadges = '';
$zbElemNames = array();
foreach ($zbElsList as $zbElOne) {
    $zbElemBadges .= '<span class="zb-badge elem" style="color:' . \player\zhuangbei_element_color_ui($zbElOne) . '">'
        . \player\zhuangbei_element_icon($zbElOne) . ' ' . \player\zhuangbei_element_name($zbElOne) . '</span>';
    $zbElemNames[] = \player\zhuangbei_element_name($zbElOne);
}
if ($zbElemBadges !== '') {
    $zbBadgeHtml = $zbElemBadges . $zbBadgeHtml;
    $zbElemCard = '<div class="zb-meta-card"><span>Nguyên tố</span><b>' . implode(' · ', $zbElemNames) . '</b></div>';
}
$zbRealmReq = \player\zhuangbei_realm_require($zhuangbei);
$zbImg = \player\zhuangbei_image_path($zhuangbei, \player\zhuangbei_slot_icon(isset($zhuangbei->tool) ? $zhuangbei->tool : 0));

// ===== Section Thuộc tính Ngũ Hành (chỉ chủ sở hữu thấy) =====
$nhSectionHtml = '';
if ($owner) {
    $nhSectionHtml = \player\nap_hanh_section_html($zhuangbei, $player, $sid, $dblj, $encode);
}

$html = <<<HTML

<!-- CSS moved to mystyle.css -->


<div class="zb-page zb-compact">
    <div class="zb-panel">
        <div class="cws-stage zb-stage" style="--cw-q:$zhuangbei->zbys">
            <div class="cws-tag">🗡 $tool</div>
            <div class="cws-side cws-side-left">
                <div class="cws-stat"><span>⚔️ Công kích</span><b>$zhuangbei->zbgj</b></div>
                <div class="cws-stat"><span>🛡️ Phòng ngự</span><b>$zhuangbei->zbfy</b></div>
                <div class="cws-stat"><span>❤️ HP</span><b>$zhuangbei->zbhp</b></div>
            </div>
            <div class="cws-figure zb-figure"><img src="$zbImg" alt="$zhuangbei->zbname"></div>
            <div class="cws-side cws-side-right">
                <div class="cws-stat"><span>💥 Bạo kích</span><b>$zhuangbei->zbbj%</b></div>
                <div class="cws-stat"><span>🩸 Hút máu</span><b>$zhuangbei->zbxx%</b></div>
                <div class="cws-stat"><span class="zb-lb-sm">🌀 Đột phá · Tu luyện</span><b>$zhuangbei->tuposjs% · $zhuangbei->tupoexp%</b></div>
            </div>
            <div class="cws-id">
                <div class="cws-name"><span style="color:$zhuangbei->zbys;text-shadow:0 0 16px {$zhuangbei->zbys}88">$zhuangbei->zbname</span> $htmlqianghua</div>
                <div class="cws-meta">$zbBadgeHtml<span class="cws-badge">Cấp $zhuangbei->zblv</span><span class="cws-badge">Yêu cầu cảnh giới $zbRealmReq</span></div>
                <div class="zb-desc">$zhuangbei->zbinfo</div>
            </div>
        </div>

        $notice_html

        $setzbwz_html

        <div class="zb-cols">
        <div class="zb-section">
            <div class="zb-section-title">Cường hóa</div>
            <div class="zb-progress"><span style="width:$qhcgl_width%"></span></div>
            <div class="zb-success-line"><span>Tỉ lệ thành công</span><b>$qhcgl</b></div>
            <div class="zb-row"><span>Cường hóa thạch</span><b class="zb-res $qhs_class">$qhs_now / $qhs_need</b></div>
            <div class="zb-row"><span>Linh thạch</span><b class="zb-res $ls_class">$uyxb_now / $ls_need</b></div>
            <div class="zb-row"><span>Giới hạn cấp nhân vật</span><b class="zb-res $qh_limit_class">+$qh_current / $player_lv_limit</b></div>
            <div class="zb-row"><span>Phân giải tốn linh thạch</span><b>$fjls_need</b></div>
            $up_buttons
        </div>

        $nhSectionHtml
        </div>
    </div>
</div>
HTML;

echo $html;
?>
