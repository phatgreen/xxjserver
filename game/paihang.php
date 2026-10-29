<?php
/**
 * Bảng xếp hạng - giao diện tiên hiệp
 * Cập nhật: 20260630
 */
$player = player\getplayer($sid,$dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$backcmd = $gonowmid;

function tcg_calc_chien_luc_rank($rankPlayer)
{
    // Dùng chung công thức với trang Trạng thái (\player\combat_power, class/player.php) — trước đây bỏ 4 trọng số
    // Thể Chất / Huyết Mạch nên Chiến lực ở Xếp hạng khác trang Trạng thái (STAT-3)
    return \player\combat_power($rankPlayer);
}

// Loại bảng xếp hạng. Chỉ dùng field đã whitelist để tránh SQL injection.
$rankType = isset($canshu) ? trim($canshu) : 'level';
$rankMap = array(
    'power' => array('name' => 'Chiến lực', 'icon' => '🔥', 'field' => 'power', 'order' => ''),
    'level' => array('name' => 'Cấp',       'icon' => '♛', 'field' => 'ulv',  'order' => 'ulv DESC, uexp ASC'),
    'gong'  => array('name' => 'Công',      'icon' => '⚔', 'field' => 'ugj',  'order' => 'ugj DESC, ulv DESC'),
    'fang'  => array('name' => 'Phòng',     'icon' => '🛡', 'field' => 'ufy',  'order' => 'ufy DESC, ulv DESC'),
    'exp'   => array('name' => 'Tu vi',     'icon' => '✦', 'field' => 'uexp', 'order' => 'uexp DESC, ulv DESC')
);
if (!isset($rankMap[$rankType])) {
    $rankType = 'level';
}
$rankCfg = $rankMap[$rankType];

$tabsHtml = '';
foreach ($rankMap as $key => $cfg) {
    $tabcmd = $encode->encode("cmd=paihang&canshu=$key&sid=$sid");
    $active = ($key == $rankType) ? ' active' : '';
    $tabsHtml .= '<a class="rank-tab'.$active.'" href="?cmd='.$tabcmd.'"><span>'.$cfg['icon'].'</span>'.$cfg['name'].'</a>';
}

$phlshtml = '';
$ret = array();

if ($rankType == 'power') {
    $sidRows = $dblj->query("SELECT sid FROM game1 WHERE sid IS NOT NULL AND sid <> ''");
    if ($sidRows) {
        $allRows = $sidRows->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $sidRow) {
            $rowSid = isset($sidRow['sid']) ? $sidRow['sid'] : '';
            if ($rowSid == '') {
                continue;
            }

            $rankPlayer = \player\getplayer($rowSid, $dblj);
            if (!$rankPlayer || empty($rankPlayer->sid)) {
                continue;
            }

            $ret[] = array(
                'uname' => $rankPlayer->uname,
                'ulv' => intval($rankPlayer->ulv),
                'uid' => intval($rankPlayer->uid),
                'sid' => $rankPlayer->sid,
                'mainValue' => tcg_calc_chien_luc_rank($rankPlayer)
            );
        }

        usort($ret, function($a, $b) {
            if ($a['mainValue'] == $b['mainValue']) {
                if ($a['ulv'] == $b['ulv']) {
                    return 0;
                }
                return ($a['ulv'] > $b['ulv']) ? -1 : 1;
            }
            return ($a['mainValue'] > $b['mainValue']) ? -1 : 1;
        });

        $ret = array_slice($ret, 0, 10);
    }
} else {
    $sql = 'SELECT * FROM game1 ORDER BY '.$rankCfg['order'].' LIMIT 10';
    $phcxjg = $dblj->query($sql);
    if ($phcxjg) {
        $ret = $phcxjg->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!empty($ret)) {
    for ($i = 0; $i < count($ret); $i++) {
        $uname = isset($ret[$i]['uname']) ? $ret[$i]['uname'] : '';
        $ulv = isset($ret[$i]['ulv']) ? intval($ret[$i]['ulv']) : 0;
        $uid = isset($ret[$i]['uid']) ? intval($ret[$i]['uid']) : 0;
        $cxsid = isset($ret[$i]['sid']) ? $ret[$i]['sid'] : '';
        $mainValue = ($rankType == 'power')
            ? (isset($ret[$i]['mainValue']) ? intval($ret[$i]['mainValue']) : 0)
            : (isset($ret[$i][$rankCfg['field']]) ? intval($ret[$i][$rankCfg['field']]) : 0);
        $mainValueText = ($rankType == 'power') ? number_format($mainValue, 0, '.', '.') : $mainValue;

        $clubName = '';
        $clubp = \player\getclubplayer_once($cxsid,$dblj);
        if ($clubp) {
            $club = \player\getclub($clubp->clubid,$dblj);
            if ($club && isset($club->clubname) && $club->clubname != '') {
                $clubName = '['.$club->clubname.']';
            }
        }

        $ucmd = $encode->encode("cmd=getplayerinfo&uid=$uid&sid=$player->sid");
        $rankNo = $i + 1;
        $rankClass = '';
        $rankTier = 'Tuyệt Thế Thiên Kiêu';
        $rankIcon = '◆';
        $rankStars = '✦';
        if ($rankNo == 1) {
            $rankClass = ' top1';
            $rankTier = 'Vạn Cổ Thiên Kiêu';
            $rankIcon = '👑';
            $rankStars = '✦✦✦✦✦';
        } elseif ($rankNo == 2) {
            $rankClass = ' top2';
            $rankTier = 'Thiên Đạo Chi Tử';
            $rankIcon = '❖';
            $rankStars = '✦✦✦✦';
        } elseif ($rankNo == 3) {
            $rankClass = ' top3';
            $rankTier = 'Trấn Thế Yêu Nghiệt';
            $rankIcon = '✹';
            $rankStars = '✦✦✦';
        } elseif ($rankNo <= 5) {
            $rankClass = ' top4';
            $rankTier = 'Vô Song Thiên Kiêu';
            $rankIcon = '✦';
            $rankStars = '✦✦';
        } elseif ($rankNo <= 10) {
            $rankClass = ' top6';
            $rankTier = 'Thiên Kiêu';
            $rankIcon = '✦';
            $rankStars = '✦';
        } elseif ($rankNo <= 20) {
            $rankClass = ' top11';
            $rankTier = 'Thiên Kiêu';
            $rankIcon = '+';
            $rankStars = '+';
        }

        $safeName = htmlspecialchars($clubName.$uname, ENT_QUOTES, 'UTF-8');
        $phlshtml .= '<div class="rank-row'.$rankClass.'">';
        $phlshtml .= '  <div class="rank-no"><span>'.$rankNo.'</span></div>';
        $phlshtml .= '  <div class="rank-main">';
        $phlshtml .= '    <div class="rank-name-line"><span class="rank-crown">'.$rankIcon.'</span><a class="rank-player" href="?cmd='.$ucmd.'">'.$safeName.'</a></div>';
        $phlshtml .= '    <div class="rank-tier-line"><span class="rank-tier">'.$rankTier.'</span><span class="rank-stars">'.$rankStars.'</span></div>';
        $phlshtml .= '  </div>';
        $phlshtml .= '  <div class="rank-lv">Lv.'.$ulv.'</div>';
        $phlshtml .= '  <div class="rank-value"><span>'.$rankCfg['name'].'</span><b>'.$mainValueText.'</b></div>';
        $phlshtml .= '</div>';
    }
}

if ($phlshtml == '') {
    $phlshtml = '<div class="rank-empty">Chưa có dữ liệu xếp hạng</div>';
}

$phhtml = <<<HTML
<div class="rank-page">
    <div class="rank-hero">
        <div>
            <div class="rank-label">BẢNG VINH DANH</div>
            <div class="rank-title">Thiên Kiêu Bảng</div>
            <div class="rank-desc">Top 10 đạo hữu mạnh nhất hiện tại theo {$rankCfg['name']}.</div>
        </div>
        <div class="rank-medal">{$rankCfg['icon']}</div>
    </div>

    <div class="rank-tabs">$tabsHtml</div>

    <div class="rank-list">
        $phlshtml
    </div>

    <div class="rank-bottom">
        <a class="rank-back" href="#" onClick="javascript:history.back(-1);return false;">↩ Trở Về</a>
        <a class="rank-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $phhtml;
?>
