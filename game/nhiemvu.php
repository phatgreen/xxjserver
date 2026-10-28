<?php
$player = \player\getplayer($sid, $dblj);
$tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($tab) ? $tab : 'daily');

$msg = '';
$action = isset($action) && $action !== '' ? $action : (isset($_GET['action']) ? $_GET['action'] : '');
if ($action === 'claim_daily') {
    $r = \player\nhiemvu_claim_daily_bonus($sid, $dblj);
    $msg = $r['ok']
        ? '<div class="nv-success">'.$r['msg'].'</div>'
        : '<div class="nv-error">'.$r['msg'].'</div>';
} elseif ($action === 'claim_quest') {
    $qid = isset($qid) && $qid !== '' ? $qid : (isset($_GET['qid']) ? $_GET['qid'] : '');
    $tbl = isset($tbl) && $tbl !== '' ? $tbl : (isset($_GET['tbl']) ? $_GET['tbl'] : '');
    $allowed = array('player_nhiemvu_daily', 'player_nhiemvu_weekly', 'player_nhiemvu_monthly');
    if ($qid && in_array($tbl, $allowed)) {
        $r = \player\nhiemvu_claim_quest($sid, $tbl, $qid, $dblj);
        $msg = $r['ok']
            ? '<div class="nv-success">'.$r['msg'].'</div>'
            : '<div class="nv-error">'.$r['msg'].'</div>';
    }
} elseif ($action === 'claim_online') {
    $oidx = isset($oidx) ? intval($oidx) : (isset($_GET['oidx']) ? intval($_GET['oidx']) : -1);
    $r = \player\online_reward_claim($sid, $oidx, $player->ulv, $dblj);
    $msg = '<div class="' . ($r['ok'] ? 'nv-success' : 'nv-error') . '">' . $r['msg'] . '</div>';
} elseif ($action === 'claim_offline') {
    $r = \player\offline_reward_claim($sid, $dblj);
    $msg = '<div class="' . ($r['ok'] ? 'nv-success' : 'nv-error') . '">' . $r['msg'] . '</div>';
    $player = \player\getplayer($sid, $dblj);
} elseif ($action === 'claim_achievement') {
    $aid = isset($aid) && $aid !== '' ? $aid : (isset($_GET['aid']) ? $_GET['aid'] : '');
    if ($aid) {
        $r = \player\nhiemvu_achievement_claim($sid, $aid, $dblj);
        $msg = $r['ok']
            ? '<div class="nv-success">'.$r['msg'].'</div>'
            : '<div class="nv-error">'.$r['msg'].'</div>';
    }
}

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$gocmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$tabDefs = array(
    'daily'        => 'Hằng Ngày',
    'weekly'       => 'Hằng Tuần',
    'monthly'      => 'Hằng Tháng',
    'achievements' => 'Thành Tựu',
    'hidden'       => 'Ẩn',
    'main'         => 'Chính Tuyến',
    'online'       => 'Trực Tuyến',
);
$nvUnitVi = array(
    'minutes'   => 'phút tu luyện',
    'monsters'  => 'quái đã diệt',
    'pills'     => 'đan đã luyện',
    'items'     => 'món đã chế tạo',
    'upgrades'  => 'lần cường hóa',
    'materials' => 'nguyên liệu đã thu thập',
    'bosses'    => 'BOSS đã hạ',
    'wins'      => 'trận thắng tỷ võ',
);
$nvTypeVi = array(
    'cultivate'     => 'phút tu luyện',
    'kill'          => 'quái đã diệt',
    'alchemy'       => 'đan đã luyện',
    'craft'         => 'món đã chế tạo',
    'upgrade'       => 'lần cường hóa',
    'upgrade_total' => 'lần cường hóa',
    'collect'       => 'nguyên liệu đã thu thập',
    'boss'          => 'BOSS đã hạ',
    'pvp'           => 'trận thắng tỷ võ',
    'complete_all'  => 'nhiệm vụ đã hoàn thành',
);
$nvGoal = function ($q) use ($nvUnitVi, $nvTypeVi) {
    $unit = isset($q['unit']) ? $q['unit'] : '';
    $noun = isset($nvUnitVi[$unit]) ? $nvUnitVi[$unit] : '';
    if ($noun === '' && isset($q['type']) && isset($nvTypeVi[$q['type']])) $noun = $nvTypeVi[$q['type']];
    if ($noun === '') return '';
    return 'Mục tiêu: ' . intval($q['target']) . ' ' . $noun;
};
$nvRewardLine = function ($q) {
    $r = array();
    if (intval($q['exp']) > 0) $r[] = number_format($q['exp']) . ' EXP';
    if (intval($q['lt']) > 0)  $r[] = number_format($q['lt']) . ' LT';
    if (intval($q['mt']) > 0)  $r[] = $q['mt'] . ' MT';
    return implode(' | ', $r);
};
$nvClaimBtn = function ($q, $tbl, $tabName) use ($encode, $sid) {
    if (!empty($q['claimed']))    return '<span class="nv-btn nv-btn-done">✅</span>';
    if (empty($q['completed']))   return '<span class="nv-btn nv-btn-lock">⏳</span>';
    $u = $encode->encode("cmd=nhiemvu&action=claim_quest&tbl={$tbl}&qid={$q['id']}&tab={$tabName}&sid=$sid");
    return '<a class="nv-btn nv-btn-claim" href="?cmd=' . $u . '">Nhận</a>';
};

$tabHtml = '';
foreach ($tabDefs as $tk => $tl) {
    $tc = $encode->encode("cmd=nhiemvu&tab=$tk&sid=$sid");
    $tabHtml .= '<a class="nv-tab' . ($tab === $tk ? ' active' : '') . '" href="?cmd=' . $tc . '">' . $tl . '</a>';
}

$html = <<<HTML
<style>
.nv-wrap{max-width:520px;margin:0 auto;padding:10px;font-size:13px;font-family:'Segoe UI',sans-serif}
.nv-tabs{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:14px;justify-content:center}
/* Nền + viền rõ để nút không lẫn vào khung */
.nv-tab{display:inline-block;padding:7px 14px;border-radius:8px;background:linear-gradient(180deg,rgba(59,84,160,.85),rgba(36,52,110,.9));color:#dbe6ff!important;text-decoration:none;font-size:12px;font-weight:700;transition:.15s;border:1px solid rgba(125,170,255,.45)}
.nv-tab:hover{background:linear-gradient(180deg,rgba(79,110,200,.9),rgba(50,72,150,.95));color:#fff!important}
.nv-tab.active{background:rgba(250,204,21,.15);color:#fde68a!important;border-color:rgba(250,204,21,.4)}
.nv-card{background:rgba(16,24,50,.92);border-radius:14px;border:1px solid rgba(255,255,255,.08);padding:16px;margin-bottom:14px}
.nv-title{font-size:15px;font-weight:1000;color:#f0e6d3;text-align:center;margin-bottom:12px}
.nv-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);gap:8px}
.nv-item:last-child{border-bottom:none}
.nv-info{flex:1;min-width:0}
.nv-name{font-weight:700;font-size:13px;color:#ddd}
.nv-desc{font-size:11px;color:#888;margin-top:2px}
.nv-bar-wrap{flex-shrink:0;width:100px;text-align:right}
.nv-bar{height:5px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden;margin-top:3px}
.nv-bar-fill{height:100%;border-radius:4px;transition:width .3s}
.nv-bar-done .nv-bar-fill{background:#4CAF50}
.nv-bar-progress .nv-bar-fill{background:linear-gradient(90deg,#f59e0b,#facc15)}
.nv-num{font-size:11px;color:#aaa;display:block;margin-bottom:2px}
.nv-btn{display:inline-block;padding:5px 12px;border-radius:7px;border:none;font-size:11px;font-weight:800;text-decoration:none;cursor:pointer}
.nv-btn-claim{background:linear-gradient(135deg,#f59e0b,#facc15);color:#000}
.nv-btn-claim:hover{filter:brightness(1.1)}
.nv-btn-done{background:rgba(76,175,80,.2);color:#4CAF50;cursor:default}
.nv-btn-lock{background:rgba(100,100,100,.15);color:#555;cursor:default}
.nv-success{color:#4CAF50;background:rgba(76,175,80,.12);border:1px solid #4CAF50;border-radius:8px;padding:10px;text-align:center;margin-bottom:12px;font-weight:700}
.nv-error{color:#ef4444;background:rgba(239,68,68,.12);border:1px solid #ef4444;border-radius:8px;padding:10px;text-align:center;margin-bottom:12px;font-weight:700}
.nv-empty{text-align:center;padding:20px;color:#666;font-size:13px}
.nv-reward-tag{display:inline-block;padding:2px 6px;border-radius:4px;background:rgba(250,204,21,.1);font-size:10px;color:#fde68a;margin-right:4px}
.nv-nav{text-align:center;margin-top:14px;display:flex;justify-content:center;gap:10px}
.nv-nav a{display:inline-block;padding:8px 18px;border-radius:8px;background:linear-gradient(180deg,rgba(59,84,160,.85),rgba(36,52,110,.9));color:#dbe6ff!important;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(125,170,255,.45)}
.nv-nav a:hover{background:linear-gradient(180deg,rgba(79,110,200,.9),rgba(50,72,150,.95));color:#fff!important}
.nv-achieve-group{margin-bottom:12px}
.nv-achieve-group:last-child{margin-bottom:0}
.nv-achieve-cat{font-weight:700;color:#f0e6d3;font-size:13px;margin-bottom:6px}
.nv-achieve-cat small{font-weight:400;color:#888;font-size:11px}
.nv-claim-all-wrap{text-align:center;padding:12px 0 4px}
.nv-ch>summary{cursor:pointer;list-style:none;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.nv-ch>summary::-webkit-details-marker{display:none}
.nv-ch>summary:before{content:'▸ ';color:#888}
.nv-ch[open]>summary:before{content:'▾ '}
</style>
<div class="nv-wrap">
    <div class="nv-tabs">{$tabHtml}</div>
    {$msg}
HTML;

// ─── Daily ─────────────────────────────────────────────────────
if ($tab === 'daily') {
    $daily = \player\nhiemvu_apply_reward_list(\player\nhiemvu_daily_get($sid, $dblj), 'daily', $player->ulv);
    $allDone = true;
    $html .= '<div class="nv-card"><div class="nv-title">📋 Nhiệm Vụ Hằng Ngày</div>';
    if (empty($daily)) {
        $html .= '<div class="nv-empty">Không có nhiệm vụ nào hôm nay.</div>';
    } else {
        foreach ($daily as $q) {
            $done = $q['completed'];
            if (!$done) $allDone = false;
            $pct = $q['target'] > 0 ? min(100, intval($q['progress']*100/$q['target'])) : 0;
            $barClass = $done ? 'nv-bar-done' : 'nv-bar-progress';
            $goal = $nvGoal($q);
            $rw = $nvRewardLine($q);
            $descHtml = trim($goal . ($goal !== '' && $rw !== '' ? ' — ' : '') . $rw);
            $html .= '<div class="nv-item">
                <div class="nv-info">
                    <div class="nv-name">'.$q['name'].'</div>
                    '.($descHtml !== '' ? '<div class="nv-desc">'.$descHtml.'</div>' : '').'
                </div>
                <div class="nv-bar-wrap">
                    <span class="nv-num">'.min($q['progress'],$q['target']).'/'.$q['target'].'</span>
                    <div class="nv-bar '.$barClass.'"><div class="nv-bar-fill" style="width:'.$pct.'%"></div></div>
                </div>
                <div>'.$nvClaimBtn($q, 'player_nhiemvu_daily', 'daily').'</div>
            </div>';
        }
        if ($allDone) {
            $claimCmd = $encode->encode("cmd=nhiemvu&action=claim_daily&tab=daily&sid=$sid");
            $html .= '<div class="nv-claim-all-wrap"><a class="nv-btn nv-btn-claim" href="?cmd='.$claimCmd.'">NHẬN THƯỞNG NGÀY</a></div>';
        }
        $dCfg = \player\nhiemvu_daily_config();
        $dBonus = \player\nhiemvu_reward(isset($dCfg['daily_bonus']) ? $dCfg['daily_bonus'] : array(), 'daily', $player->ulv, 'bonus_pct');
        $html .= '<div class="nv-desc" style="text-align:center">Hoàn thành tất cả: thưởng thêm '.$nvRewardLine($dBonus).'</div>';
    }
    $html .= '</div>';
}

// ─── Weekly ────────────────────────────────────────────────────
elseif ($tab === 'weekly') {
    $weekly = \player\nhiemvu_apply_reward_list(\player\nhiemvu_weekly_get($sid, $dblj), 'weekly', $player->ulv);
    $html .= '<div class="nv-card"><div class="nv-title">📅 Nhiệm Vụ Hằng Tuần</div>';
    if (empty($weekly)) {
        $html .= '<div class="nv-empty">Không có nhiệm vụ tuần này.</div>';
    } else {
        foreach ($weekly as $q) {
            $done = $q['completed'];
            $pct = $q['target'] > 0 ? min(100, intval($q['progress']*100/$q['target'])) : 0;
            $barClass = $done ? 'nv-bar-done' : 'nv-bar-progress';
            $btnHtml = $nvClaimBtn($q, 'player_nhiemvu_weekly', 'weekly');
            $goal = $nvGoal($q);
            $rewards = array();
            if ($q['exp'] > 0) $rewards[] = number_format($q['exp']).' EXP';
            if ($q['lt'] > 0) $rewards[] = number_format($q['lt']).' LT';
            if ($q['mt'] > 0) $rewards[] = $q['mt'].' MT';
            $rewardStr = !empty($rewards) ? '<div class="nv-desc">'.implode(' | ', $rewards).'</div>' : '';
            $html .= '<div class="nv-item">
                <div class="nv-info">
                    <div class="nv-name">'.$q['name'].'</div>
                    '.($goal !== '' ? '<div class="nv-desc">'.$goal.'</div>' : '').'
                    '.$rewardStr.'
                </div>
                <div class="nv-bar-wrap">
                    <span class="nv-num">'.min($q['progress'],$q['target']).'/'.$q['target'].'</span>
                    <div class="nv-bar '.$barClass.'"><div class="nv-bar-fill" style="width:'.$pct.'%"></div></div>
                </div>
                <div>'.$btnHtml.'</div>
            </div>';
        }
    }
    $html .= '</div>';
}

// ─── Monthly ───────────────────────────────────────────────────
elseif ($tab === 'monthly') {
    $monthly = \player\nhiemvu_apply_reward_list(\player\nhiemvu_monthly_get($sid, $dblj), 'monthly', $player->ulv);
    $html .= '<div class="nv-card"><div class="nv-title">📆 Nhiệm Vụ Hằng Tháng</div>';
    if (empty($monthly)) {
        $html .= '<div class="nv-empty">Không có nhiệm vụ tháng này.</div>';
    } else {
        foreach ($monthly as $q) {
            $done = $q['completed'];
            $pct = $q['target'] > 0 ? min(100, intval($q['progress']*100/$q['target'])) : 0;
            $barClass = $done ? 'nv-bar-done' : 'nv-bar-progress';
            $btnHtml = $nvClaimBtn($q, 'player_nhiemvu_monthly', 'monthly');
            $goal = $nvGoal($q);
            $rewards = array();
            if ($q['exp'] > 0) $rewards[] = number_format($q['exp']).' EXP';
            if ($q['lt'] > 0) $rewards[] = number_format($q['lt']).' LT';
            if ($q['mt'] > 0) $rewards[] = $q['mt'].' MT';
            $rewardStr = !empty($rewards) ? '<div class="nv-desc">'.implode(' | ', $rewards).'</div>' : '';
            $html .= '<div class="nv-item">
                <div class="nv-info">
                    <div class="nv-name">'.$q['name'].'</div>
                    '.($goal !== '' ? '<div class="nv-desc">'.$goal.'</div>' : '').'
                    '.$rewardStr.'
                </div>
                <div class="nv-bar-wrap">
                    <span class="nv-num">'.min($q['progress'],$q['target']).'/'.$q['target'].'</span>
                    <div class="nv-bar '.$barClass.'"><div class="nv-bar-fill" style="width:'.$pct.'%"></div></div>
                </div>
                <div>'.$btnHtml.'</div>
            </div>';
        }
    }
    $html .= '</div>';
}

// ─── Achievements ──────────────────────────────────────────────
elseif ($tab === 'achievements') {
    $achieves = \player\nhiemvu_achievement_get_all($sid, $dblj);
    $html .= '<div class="nv-card"><div class="nv-title">🏆 Thành Tựu</div>';
    if (empty($achieves)) {
        $html .= '<div class="nv-empty">Chưa có thành tựu nào.</div>';
    } else {
        foreach ($achieves as $group) {
            $cat = $group['cat'];
            $html .= '<div class="nv-achieve-group">
                <div class="nv-achieve-cat">'.$cat['icon'].' '.$cat['name'].' <small>'.$cat['description'].'</small></div>';
            foreach ($group['tiers'] as $t) {
                $done = $t['claimed'];
                $reach = intval($t['progress']) >= intval($t['target']);
                $pct = $t['target'] > 0 ? min(100, intval($t['progress']*100/$t['target'])) : 0;
                $barClass = $done ? 'nv-bar-done' : ($reach ? 'nv-bar-done' : 'nv-bar-progress');
                    $btnHtml = '';
                if ($done) {
                    $btnHtml = '<span class="nv-btn nv-btn-done">✅</span>';
                } elseif ($reach) {
                    $claimCmd = $encode->encode("cmd=nhiemvu&action=claim_achievement&aid={$t['id']}&tab=achievements&sid=$sid");
                    $btnHtml = '<a class="nv-btn nv-btn-claim" href="?cmd='.$claimCmd.'">Nhận</a>';
                } else {
                    $btnHtml = '<span class="nv-btn nv-btn-lock">🔒</span>';
                }
                $html .= '<div class="nv-item">
                    <div class="nv-info">
                        <div class="nv-name" style="font-size:12px">'.$t['name'].'</div>
                        <div class="nv-desc">'.number_format($t['exp']).' EXP | '.number_format($t['lt']).' LT'.($t['mt']>0?' | '.$t['mt'].' MT':'').'</div>
                    </div>
                    <div class="nv-bar-wrap" style="width:80px">
                        <span class="nv-num">'.min($t['progress'],$t['target']).'/'.$t['target'].'</span>
                        <div class="nv-bar '.$barClass.'"><div class="nv-bar-fill" style="width:'.$pct.'%"></div></div>
                    </div>
                    <div>'.$btnHtml.'</div>
                </div>';
            }
            $html .= '</div>';
        }
    }
    $html .= '</div>';
}

// ─── Hidden ────────────────────────────────────────────────────
elseif ($tab === 'hidden') {
    $hidden = \player\nhiemvu_hidden_get_all($sid, $dblj);
    $html .= '<div class="nv-card"><div class="nv-title">👻 Nhiệm Vụ Ẩn</div>';
    if (empty($hidden)) {
        $html .= '<div class="nv-empty">Chưa có nhiệm vụ ẩn nào.</div>';
    } else {
        foreach ($hidden as $q) {
            $discovered = $q['discovered'];
            $completed = $q['completed'];
            if (!$discovered) {
                $html .= '<div class="nv-item"><div class="nv-info"><div class="nv-name" style="color:#555">???</div></div><div class="nv-bar-wrap"><span class="nv-btn nv-btn-lock">🔒</span></div></div>';
            } else {
                $rewards = array();
                if ($q['exp'] > 0) $rewards[] = number_format($q['exp']).' EXP';
                if ($q['lt'] > 0) $rewards[] = number_format($q['lt']).' LT';
                if ($q['mt'] > 0) $rewards[] = $q['mt'].' MT';
                $rewardStr = !empty($rewards) ? ' <span class="nv-reward-tag">'.implode(' ', $rewards).'</span>' : '';
                $statusIcon = $completed ? '✅' : '🔓';
                $hTarget = max(1, intval($q['target']));
                $hProgress = min(intval($q['progress']), $hTarget);
                if ($completed) {
                    // Nhan qua xong thi progress bi reset ve 0, giu thanh tien do day de khoi mau thuan voi bich ✅
                    $hProgress = $hTarget;
                }
                $hPct = intval(round($hProgress * 100 / $hTarget));
                $btnHtml = $completed ? '<span class="nv-btn nv-btn-done">✅</span>' : '<span class="nv-btn nv-btn-lock">⏳</span>';
                $html .= '<div class="nv-item">
                    <div class="nv-info">
                        <div class="nv-name">'.$statusIcon.' '.$q['name'].$rewardStr.'</div>
                        <div class="nv-desc">'.$q['hint'].'</div>
                    </div>
                    <div class="nv-bar-wrap">
                        <span class="nv-num">'.$hProgress.'/'.$hTarget.'</span>
                        <div class="nv-bar '.($completed ? 'nv-bar-done' : 'nv-bar-progress').'"><div class="nv-bar-fill" style="width:'.$hPct.'%"></div></div>
                    </div>
                    <div>'.$btnHtml.'</div>
                </div>';
            }
        }
    }
    $html .= '</div>';
}

// ─── Main ──────────────────────────────────────────────────────
elseif ($tab === 'main') {
    $mainNotice = '';
    if (function_exists('player\\nhiemvu_update_all')) {
        $rv = \player\nhiemvu_update_all($sid, 'dialogue', 1, $dblj, array('npc' => 'nhiem_vu'));
        if (!empty($rv['ok'])) $mainNotice = '<div class="nv-success">' . $rv['msg'] . '</div>';
    }
    // Cơ Duyên: nhiệm vụ hướng dẫn tính năng đã mở theo cấp (độc lập chính tuyến)
    $cdList = \player\nhiemvu_coduyen_get($sid, $player->ulv, $dblj);
    $cdNext = \player\feature_next_locked($player->ulv, 1);
    if (!empty($cdList) || !empty($cdNext)) {
        $cdDone = 0;
        foreach ($cdList as $cq) if ($cq['status'] == 2) $cdDone++;
        $html .= '<div class="nv-card"><div class="nv-title">🌟 Cơ Duyên <small style="font-size:12px;color:#94a3b8">' . $cdDone . '/' . count($cdList) . ' — hướng dẫn tính năng mới mở</small></div>';
        // Chưa xong lên trước
        usort($cdList, function($a, $b){ return ($a['status'] == 2) - ($b['status'] == 2); });
        foreach ($cdList as $q) {
            $rw = \player\nhiemvu_reward($q, 'main', $player->ulv);
            $rwTxt = array();
            if ($rw['exp'] > 0) $rwTxt[] = number_format($rw['exp']) . ' tu vi';
            if ($rw['lt'] > 0)  $rwTxt[] = number_format($rw['lt']) . ' LT';
            $done = $q['status'] == 2;
            $html .= '<div class="nv-item" style="opacity:' . ($done ? '0.6' : '1') . '">
                <div class="nv-info">
                    <div class="nv-name" style="color:' . ($done ? '#4CAF50' : '#fde68a') . ';font-size:12px">' . ($done ? '✅' : '✨') . ' ' . htmlspecialchars($q['name'], ENT_QUOTES, 'UTF-8')
                    . ' <span style="font-size:11px;color:#888">[' . implode(', ', $rwTxt) . ']</span></div>
                    <div class="nv-desc">' . htmlspecialchars($q['desc'], ENT_QUOTES, 'UTF-8') . '</div>
                </div>
                <div class="nv-bar-wrap" style="width:80px"><span class="nv-num">' . ($done ? 'Xong' : min($q['progress'], $q['target']) . '/' . $q['target']) . '</span></div>
            </div>';
        }
        if (!empty($cdNext)) {
            $html .= '<div class="nv-desc" style="text-align:center;margin-top:8px">Cơ duyên kế tiếp: 🔒 ' . htmlspecialchars($cdNext[0]['name'], ENT_QUOTES, 'UTF-8') . ' — mở ở cấp ' . intval($cdNext[0]['level']) . '</div>';
        }
        $html .= '</div>';
    }
    $mainQuests = \player\nhiemvu_main_get_all($sid, $dblj);
    $html .= '<div class="nv-card"><div class="nv-title">⚔ Nhiệm Vụ Chính Tuyến</div>';
    $html .= $mainNotice;
    if (empty($mainQuests)) {
        $html .= '<div class="nv-empty">Chưa có nhiệm vụ chính tuyến.</div>';
    } else {
        // Chỉ mở chương đang làm; chương đã xong / chưa tới thu gọn (bấm tiêu đề để xem)
        $curFound = false;
        foreach ($mainQuests as $ch) {
            $ch['quests'] = \player\nhiemvu_apply_reward_list($ch['quests'], 'main', \player\nhiemvu_main_ref_level($ch, $player->ulv));
            $chDone = 0;
            foreach ($ch['quests'] as $cq) if ($cq['status'] == 2) $chDone++;
            $chAll = count($ch['quests']);
            $isCur = (!$curFound && $chDone < $chAll);
            if ($isCur) $curFound = true;
            $chTag = $chDone >= $chAll ? '✅ Hoàn thành' : ($isCur ? '⏳ Đang làm ' . $chDone . '/' . $chAll : '🔒 Chưa mở');
            $html .= '<details class="nv-achieve-group nv-ch"' . ($isCur ? ' open' : '') . '>
                <summary class="nv-achieve-cat" style="color:#facc15">CH'.$ch['id'].': '.$ch['name'].' <small>Cấp '.$ch['level_range'][0].'-'.$ch['level_range'][1].' · '.$chTag.'</small></summary>';
            foreach ($ch['quests'] as $q) {
                $status = $q['status'];
                if ($status == 2) {
                    $icon = '✅';
                    $color = '#4CAF50';
                    $label = 'Xong';
                } elseif ($status == 1) {
                    $icon = '⏳';
                    $color = '#f59e0b';
                    $label = 'Đang làm';
                } else {
                    $icon = '🔒';
                    $color = '#555';
                    $label = 'Khoá';
                }
                $rewards = array();
                if (intval($q['exp']) > 0) $rewards[] = number_format($q['exp']).' EXP';
                if (intval($q['lt']) > 0) $rewards[] = number_format($q['lt']).' LT';
                if (intval($q['mt']) > 0) $rewards[] = $q['mt'].' MT';
                $rewardStr = !empty($rewards) ? ' <span style="font-size:11px;color:#888">['.implode(', ', $rewards).']</span>' : '';
                $goalTxt = \player\nhiemvu_main_goal_text($q);
                $mTarget = intval($q['target']);
                $mHave   = min(intval($q['progress']), $mTarget);
                $mPct    = $mTarget > 0 ? min(100, intval($mHave * 100 / $mTarget)) : 0;
                $mBar    = $status == 2 ? 'nv-bar-done' : 'nv-bar-progress';
                $html .= '<div class="nv-item" style="opacity:'.($status==2?'0.6':'1').'">
                    <div class="nv-info">
                        <div class="nv-name" style="color:'.$color.';font-size:12px">'.$icon.' '.$q['name'].$rewardStr.'</div>
                        '.($goalTxt !== '' ? '<div class="nv-desc">'.$goalTxt.'</div>' : '').'
                    </div>
                    <div class="nv-bar-wrap" style="width:80px">
                        <span class="nv-num">'.$mHave.'/'.$mTarget.' · '.$label.'</span>
                        <div class="nv-bar '.$mBar.'"><div class="nv-bar-fill" style="width:'.$mPct.'%"></div></div>
                    </div>
                </div>';
            }
            $html .= '</details>';
        }
    }
    $html .= '</div>';
}

// ─── Trực tuyến + Bế Quan ──────────────────────────────────────
if ($tab === 'online') {
    $off = \player\offline_reward_pending($sid, $dblj);
    $nvRules = \player\nhiemvu_rules();
    $offCfg = isset($nvRules['offline']) ? $nvRules['offline'] : array();
    $html .= '<div class="nv-card"><div class="nv-title">🧘 Bế Quan (ngoại tuyến)</div>';
    $html .= '<div class="nv-desc" style="text-align:center;margin-bottom:8px">Offline từ ' . intval($offCfg['min_hours']) . ' giờ: mỗi giờ nhận '
        . floatval($offCfg['exp_pct_per_hour']) . '% tu vi cấp hiện tại, tối đa ' . intval($offCfg['max_hours']) . ' giờ/ngày (cấp ≥ '
        . intval($offCfg['min_level']) . ', không tính khi đang Tu Luyện).</div>';
    if ($off['exp'] > 0) {
        $offCmd = $encode->encode("cmd=nhiemvu&action=claim_offline&tab=online&sid=$sid");
        $html .= '<div class="nv-item"><div class="nv-info"><div class="nv-name">Đã bế quan ' . $off['hours'] . ' giờ</div><div class="nv-desc">'
            . number_format($off['exp']) . ' tu vi</div></div><div><a class="nv-btn nv-btn-claim" href="?cmd=' . $offCmd . '">Nhận</a></div></div>';
    } else {
        $html .= '<div class="nv-empty">Chưa có tu vi bế quan.</div>';
    }
    $html .= '</div>';

    $on = \player\online_reward_status($sid, $player->ulv, $dblj);
    $html .= '<div class="nv-card"><div class="nv-title">⏱ Thưởng Trực Tuyến Hôm Nay</div>';
    $html .= '<div class="nv-desc" style="text-align:center;margin-bottom:8px">Đã hoạt động <b style="color:#fde68a">' . $on['minutes']
        . ' phút</b> (treo máy không thao tác quá 5 phút không được tính).</div>';
    foreach ($on['list'] as $m) {
        $rw = array();
        if ($m['lt'] > 0) $rw[] = number_format($m['lt']) . ' LT';
        if ($m['mt'] > 0) $rw[] = $m['mt'] . ' MT';
        if ($m['stamina'] > 0) $rw[] = $m['stamina'] . ' thể lực';
        foreach ($m['items'] as $it) $rw[] = \player\online_reward_item_name($it) . ' x' . intval($it['count']);
        if ($m['claimed']) {
            $btn = '<span class="nv-btn nv-btn-done">✅</span>';
        } elseif ($m['reached']) {
            $oc = $encode->encode("cmd=nhiemvu&action=claim_online&oidx={$m['idx']}&tab=online&sid=$sid");
            $btn = '<a class="nv-btn nv-btn-claim" href="?cmd=' . $oc . '">Nhận</a>';
        } else {
            $btn = '<span class="nv-btn nv-btn-lock">⏳</span>';
        }
        $pct = min(100, intval($on['minutes'] * 100 / max(1, $m['minutes'])));
        $html .= '<div class="nv-item"><div class="nv-info"><div class="nv-name">Trực tuyến ' . $m['minutes'] . ' phút</div><div class="nv-desc">'
            . implode(' | ', $rw) . '</div></div><div class="nv-bar-wrap"><span class="nv-num">' . min($on['minutes'], $m['minutes']) . '/' . $m['minutes']
            . '</span><div class="nv-bar ' . ($m['reached'] ? 'nv-bar-done' : 'nv-bar-progress') . '"><div class="nv-bar-fill" style="width:' . $pct . '%"></div></div></div><div>' . $btn . '</div></div>';
    }
    $html .= '</div>';
}

$html .= <<<HTML
    <div class="nv-nav">
        <a href="?cmd={$gocmd}">Đại Sảnh</a>
        <a href="?cmd={$ztcmd}">Nhân Vật</a>
    </div>
</div>
HTML;

echo $html;
