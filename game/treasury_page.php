<?php
/* === BAO KHO TONG MON === */
if (!$clubplayer) {
    if ($isAjax) ajaxJson(false, 'Ngươi chưa có tông môn');
    echo '<div class="tcg-club-notice">Ngươi chưa có tông môn</div>';
    return;
}

$myClubId = intval($clubplayer->clubid);
$myUclv = intval($clubplayer->uclv);
$isLeader = ($myUclv <= 2);

$treasuryItems = \player\club_treasury_list($myClubId, $dblj);
$treasuryCount = count($treasuryItems);
$treasuryTotal = \player\club_treasury_count($myClubId, $dblj);
$treasuryHistory = \player\club_treasury_history($myClubId, 50, $dblj);
$treasuryHistoryCount = count($treasuryHistory);

$claimedTotal = $treasuryHistoryCount;
$allTotal = $treasuryTotal + $claimedTotal;

$backCmd = $encode->encode("cmd=club&clubid=$myClubId&sid=$sid");
$homeCmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$memberOptions = '';
if ($isLeader) {
    $mStmt = $dblj->prepare("SELECT uid, uclv FROM clubplayer WHERE clubid = ? AND uclv > 0 ORDER BY uclv ASC");
    $mStmt->execute(array($myClubId));
    $mMembers = $mStmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($mMembers as $m) {
        $mUid = intval($m['uid']);
        if ($mUid <= 0 || $mUid == $sid) continue;
        $mPlayer = \player\getplayer1($mUid, $dblj);
        if (!$mPlayer || empty($mPlayer->uname)) continue;
        $mRank = '';
        switch (intval($m['uclv'])) {
            case 1: $mRank = ' [Chuong mon]'; break;
            case 2: $mRank = ' [Pho chu]'; break;
            case 3: $mRank = ' [Truong lao]'; break;
        }
        $safeMName = htmlspecialchars($mPlayer->uname, ENT_QUOTES, 'UTF-8');
        $memberOptions .= '<option value="' . $mUid . '">' . $safeMName . $mRank . '</option>';
    }
}

$pendingItemsHtml = '';
foreach ($treasuryItems as $tItem) {
    $tId = intval($tItem['id']);
    $tType = $tItem['item_type'];
    $tAmount = intval($tItem['item_amount']);
    $tDonor = htmlspecialchars($tItem['donor_name'], ENT_QUOTES, 'UTF-8');
    $tTime = $tItem['created_at'];
    $tName = 'Vat pham';
    if ($tType === 'yaodan') {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaodan_config.json'), true);
        if (isset($cfg[intval($tItem['item_id'])])) $tName = $cfg[intval($tItem['item_id'])]['ydname'];
    } elseif ($tType === 'daoju') {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/daoju_config.json'), true);
        if (isset($cfg[intval($tItem['item_id'])])) $tName = $cfg[intval($tItem['item_id'])]['djname'];
    } else {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaopin_config.json'), true);
        if (isset($cfg[intval($tItem['item_id'])])) $tName = $cfg[intval($tItem['item_id'])]['ypname'];
    }
    $typeIcon = ($tType === 'yaodan') ? '💊' : (($tType === 'daoju') ? '📦' : '🧪');
    $safeTName = htmlspecialchars($tName, ENT_QUOTES, 'UTF-8');
    $pendingItemsHtml .= '<div class="tcg-treasury-item" data-type="' . $tType . '">';
    $pendingItemsHtml .= '<div class="tcg-treasury-item-icon">' . $typeIcon . '</div>';
    $pendingItemsHtml .= '<div class="tcg-treasury-item-info">';
    $pendingItemsHtml .= '<div class="tcg-treasury-item-name">' . $safeTName . '</div>';
    $pendingItemsHtml .= '<div class="tcg-treasury-item-meta">Đóng góp bởi: <strong>' . $tDonor . '</strong> • ' . $tTime . '</div>';
    $pendingItemsHtml .= '</div>';
    $pendingItemsHtml .= '<div class="tcg-treasury-item-amount">x' . $tAmount . '</div>';
    if ($isLeader) {
        $distributeCmd = $encode->encode("cmd=club&canshu=treasury_distribute&tid=$tId&sid=$sid");
        $pendingItemsHtml .= '<div class="tt-dist-row">';
        $pendingItemsHtml .= '<form method="get" class="tt-dist-form" onsubmit="return ttDistributeConfirm(this)">';
        $pendingItemsHtml .= '<input type="hidden" name="cmd" value="' . $distributeCmd . '">';
        $pendingItemsHtml .= '<select name="target_uid" class="tt-member-select">';
        $pendingItemsHtml .= '<option value="0">-- Chọn thành viên --</option>';
        $pendingItemsHtml .= $memberOptions;
        $pendingItemsHtml .= '</select>';
        $pendingItemsHtml .= '<button type="submit" class="tcg-treasury-claim-btn">Phân phát</button>';
        $pendingItemsHtml .= '</form>';
        $pendingItemsHtml .= '</div>';
    }
    $pendingItemsHtml .= '</div>';
}
if ($treasuryCount === 0) {
    $pendingItemsHtml = '<div class="tcg-treasury-empty">';
    $pendingItemsHtml .= '<div class="tcg-treasury-empty-icon">🏦</div>';
    $pendingItemsHtml .= '<div class="tcg-treasury-empty-text">Bảo khố đang trống</div>';
    $pendingItemsHtml .= '<div class="tcg-treasury-empty-sub">Đan dược từ nhiệm vụ luyện đan tông môn sẽ được đưa vào đây.</div>';
    $pendingItemsHtml .= '</div>';
}

$historyItemsHtml = '';
foreach ($treasuryHistory as $hItem) {
    $hType = $hItem['item_type'];
    $hAmount = intval($hItem['item_amount']);
    $hDonor = htmlspecialchars($hItem['donor_name'], ENT_QUOTES, 'UTF-8');
    $hClaimer = htmlspecialchars($hItem['claimer_name'], ENT_QUOTES, 'UTF-8');
    $hTime = $hItem['claimed_at'];
    $hName = 'Vat pham';
    if ($hType === 'yaodan') {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaodan_config.json'), true);
        if (isset($cfg[intval($hItem['item_id'])])) $hName = $cfg[intval($hItem['item_id'])]['ydname'];
    } elseif ($hType === 'daoju') {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/daoju_config.json'), true);
        if (isset($cfg[intval($hItem['item_id'])])) $hName = $cfg[intval($hItem['item_id'])]['djname'];
    } else {
        $cfg = @json_decode(@file_get_contents(__DIR__ . '/../class/config/yaopin_config.json'), true);
        if (isset($cfg[intval($hItem['item_id'])])) $hName = $cfg[intval($hItem['item_id'])]['ypname'];
    }
    $hIcon = ($hType === 'yaodan') ? '💊' : (($hType === 'daoju') ? '📦' : '🧪');
    $safeHName = htmlspecialchars($hName, ENT_QUOTES, 'UTF-8');
    $historyItemsHtml .= '<div class="tcg-treasury-history-item" data-type="' . $hType . '">';
    $historyItemsHtml .= '<div class="tcg-treasury-item-icon">' . $hIcon . '</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-item-info">';
    $historyItemsHtml .= '<div class="tcg-treasury-item-name">' . $safeHName . '</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-item-meta">Đóng góp: <strong>' . $hDonor . '</strong> • ' . $hItem['created_at'] . '</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-history-claimer">Phân phát cho: <strong>' . $hClaimer . '</strong> • ' . $hTime . '</div>';
    $historyItemsHtml .= '</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-item-amount">x' . $hAmount . '</div>';
    $historyItemsHtml .= '</div>';
}
if ($treasuryHistoryCount === 0) {
    $historyItemsHtml = '<div class="tcg-treasury-empty">';
    $historyItemsHtml .= '<div class="tcg-treasury-empty-icon">📜</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-empty-text">Chưa có lịch sử phân phát</div>';
    $historyItemsHtml .= '<div class="tcg-treasury-empty-sub">Lịch sử phân phát sẽ xuất hiện ở đây.</div>';
    $historyItemsHtml .= '</div>';
}

$treasuryUrl = './images/tongmon/baokho/bg_baokho.png';
$bgFile = __DIR__ . '/../images/tongmon/baokho/bg_baokho.png';
if (is_file($bgFile)) { $treasuryUrl .= '?v=' . filemtime($bgFile); }
$clubLt = isset($club->clubyxb) ? intval($club->clubyxb) : 0;
$roleNote = $isLeader
    ? 'Mỗi lần phân phát tốn <strong>500 Linh Thạch</strong> từ kho tông môn.'
    : 'Chỉ <strong>Tông chủ</strong> và <strong>Phó chủ</strong> mới có quyền phân phát.';

echo '<div class="tcg-treasury-page">';
echo '<div class="tcg-treasury-hero">';
echo '<div class="tcg-treasury-hero-bg"><img src="'.$treasuryUrl.'" alt="Bảo Khố"></div>';
echo '<div class="tcg-treasury-hero-mask"></div>';
echo '<div class="tcg-treasury-hero-content">';
echo '<div class="tcg-treasury-badge">🏦 Khu vực tông môn</div>';
echo '<h1>Bảo Khố</h1>';
echo '<p>Kho báu chung của tông môn. Vật phẩm từ nhiệm vụ luyện đan sẽ được đưa vào đây.</p>';
echo '</div></div>';

echo '<div class="tcg-treasury-stats">';
echo '<div class="tcg-treasury-stat"><span>⏳ Chờ nhận</span><strong class="gold">'.$treasuryCount.'</strong></div>';
echo '<div class="tcg-treasury-stat"><span>✅ Đã phát</span><strong>'.$claimedTotal.'</strong></div>';
echo '<div class="tcg-treasury-stat"><span>💎 Linh thạch kho</span><strong>'.$clubLt.'</strong></div>';
echo '</div>';

echo '<div class="tcg-treasury-tabs">';
echo '<a class="tcg-treasury-tab active" href="javascript:void(0)" onclick="ttTabSwitch(\'pending\')">⏳ Chờ nhận<span class="tcg-treasury-tab-badge">'.$treasuryCount.'</span></a>';
echo '<a class="tcg-treasury-tab" href="javascript:void(0)" onclick="ttTabSwitch(\'history\')">📜 Lịch sử<span class="tcg-treasury-tab-badge">'.$treasuryHistoryCount.'</span></a>';
echo '</div>';

echo '<div id="tt-pending-panel">';
echo '<div class="tcg-treasury-filters">';
echo '<a class="tcg-treasury-filter active" href="javascript:void(0)" onclick="ttFilter(\'all\',this)">Tất cả</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilter(\'yaodan\',this)">💊 Đan dược</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilter(\'daoju\',this)">📦 Đạo cụ</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilter(\'yaopin\',this)">🧪 Dược phẩm</a>';
echo '</div>';
echo '<div class="tcg-treasury-section">';
echo '<div class="tcg-treasury-section-title">📦 Vật phẩm trong kho ('.$treasuryCount.')</div>';
echo '<div class="tcg-treasury-list" id="tt-pending-list">'.$pendingItemsHtml.'</div>';
echo '</div></div>';

echo '<div id="tt-history-panel" style="display:none">';
echo '<div class="tcg-treasury-filters">';
echo '<a class="tcg-treasury-filter active" href="javascript:void(0)" onclick="ttFilterH(\'all\',this)">Tất cả</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilterH(\'yaodan\',this)">💊 Đan dược</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilterH(\'daoju\',this)">📦 Đạo cụ</a>';
echo '<a class="tcg-treasury-filter" href="javascript:void(0)" onclick="ttFilterH(\'yaopin\',this)">🧪 Dược phẩm</a>';
echo '</div>';
echo '<div class="tcg-treasury-section">';
echo '<div class="tcg-treasury-section-title">📜 Lịch sử phân phát ('.$treasuryHistoryCount.')</div>';
echo '<div class="tcg-treasury-list" id="tt-history-list">'.$historyItemsHtml.'</div>';
echo '</div></div>';

echo '<div class="tcg-treasury-guide">';
echo '<div class="tcg-treasury-guide-title">📖 Hướng dẫn sử dụng</div>';
echo '<div class="tcg-treasury-guide-item">🔹 Vật phẩm trong bảo khố đến từ <strong>nhiệm vụ luyện đan tông môn</strong>. Khi hoàn thành nhiệm vụ, đan dược sẽ được chuyển vào kho chung.</div>';
echo '<div class="tcg-treasury-guide-item">🔹 <strong>Chỉ Tông chủ và Phó chủ</strong> mới có quyền phân phát vật phẩm cho thành viên.</div>';
echo '<div class="tcg-treasury-guide-item">🔹 Mỗi lần phân phát tốn <strong>500 Linh Thạch</strong> từ kho tông môn.</div>';
echo '<div class="tcg-treasury-guide-item">🔹 Cách phân phát: Chọn thành viên trong danh sách dropdown → nhấn <strong>Phân phát</strong>.</div>';
echo '<div class="tcg-treasury-guide-note">⚠ '.$roleNote.'</div>';
echo '</div>';

echo '<div class="tcg-treasury-bottom">';
echo '<a class="tcg-treasury-back" href="?cmd='.$backCmd.'">← Trở về</a>';
echo '<a class="tcg-treasury-home" href="?cmd='.$homeCmd.'">Đại Sảnh →</a>';
echo '</div></div>';

echo '<script>';
echo 'function ttTabSwitch(tab){var pp=document.getElementById("tt-pending-panel");var hp=document.getElementById("tt-history-panel");var tabs=document.querySelectorAll(".tcg-treasury-tab");if(tab==="pending"){pp.style.display="";hp.style.display="none";tabs[0].className="tcg-treasury-tab active";tabs[1].className="tcg-treasury-tab"}else{pp.style.display="none";hp.style.display="";tabs[1].className="tcg-treasury-tab active";tabs[0].className="tcg-treasury-tab"}}';
echo 'function ttFilter(type,el){var items=document.querySelectorAll("#tt-pending-list .tcg-treasury-item");var btns=el.parentNode.querySelectorAll(".tcg-treasury-filter");for(var i=0;i<btns.length;i++)btns[i].className="tcg-treasury-filter";el.className="tcg-treasury-filter active";for(var i=0;i<items.length;i++){if(type==="all"||items[i].getAttribute("data-type")===type){items[i].style.display=""}else{items[i].style.display="none"}}}';
echo 'function ttFilterH(type,el){var items=document.querySelectorAll("#tt-history-list .tcg-treasury-history-item");var btns=el.parentNode.querySelectorAll(".tcg-treasury-filter");for(var i=0;i<btns.length;i++)btns[i].className="tcg-treasury-filter";el.className="tcg-treasury-filter active";for(var i=0;i<items.length;i++){if(type==="all"||items[i].getAttribute("data-type")===type){items[i].style.display=""}else{items[i].style.display="none"}}}';
echo 'function ttDistributeConfirm(f){var sel=f.querySelector("select[name=target_uid]");if(!sel||sel.value==="0"){alert("Vui lòng chọn thành viên cần phân phát!");return false}var name=sel.options[sel.selectedIndex].text;return confirm("Phân phát vật phẩm cho "+name+"?\nMỗi lần phân phát tốn 500 Linh Thạch.")}';
echo '</script>';
