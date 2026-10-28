<?php
// Nhiem vu Linh Can tuan — trang theo khuon mach huyet (docs/UI_LINHCAN.md).
// Logic: class/function/linhcan_quest.php | CSS: css/mystyle.css block .lcq-*
// Router game.php:114 parse_str($Dcmd) bien moi cap trong cmd thanh BIEN TOAN CUC.
// FIX bug cu: `if ($cmd == 'claim_reward')` (linhcan_quest.php:8 cu) KHONG BAO GIO khoi
// dong vi $cmd luon la 'linhcan_quest'. Nay dung `isset($claim_reward)` — cung cach
// game/huyetmach_boss.php:16 (`isset($attack)`).

$ztcmd    = $encode->encode("cmd=zhuangtai&sid=$sid");
$lccmd    = $encode->encode("cmd=linhcan&sid=$sid");
$lcbcmd   = $encode->encode("cmd=linhcan_boss&sid=$sid");
$lcqcmd   = $encode->encode("cmd=linhcan_quest&sid=$sid");
$claimCmd = $encode->encode("cmd=linhcan_quest&claim_reward=1&sid=$sid");

$player = \player\getplayer($sid, $dblj);

$questData   = \player\linhcan_quest_get_progress($sid, $dblj);
$lcqcfg      = \player\linhcan_quest_config_all();
$lcquests    = isset($lcqcfg['weekly_quests']) ? $lcqcfg['weekly_quests'] : array();
$lcweek      = \player\linhcan_quest_current_week();

$claimMsg = '';
if (isset($claim_reward)) {
    // Claim chi dung mot lan/tuan — that bai thi `linhcan_quest_claim` tra msg rieng.
    $res = \player\linhcan_quest_claim($sid, $dblj);
    $claimMsg = !empty($res['ok'])
        ? '<div class="lcq-notice lcq-good">✅ ' . $res['msg'] . '</div>'
        : '<div class="lcq-notice lcq-bad">⚠ ' . $res['msg'] . '</div>';
    $questData = \player\linhcan_quest_get_progress($sid, $dblj); // doc lai sau khi claim
}
// TINH SAU KHI CLAIM: neu tinh truoc thi nhan xong thuong nut van con hien (bug Task 11).
$isCompleted = !empty($questData['completed']);

$allComplete = true;
foreach ($lcquests as $qid => $q) {
    $progQ = isset($questData['progress'][$qid]) ? intval($questData['progress'][$qid]) : 0;
    if ($progQ < intval($q['target'])) { $allComplete = false; break; }
}

$ltReward = intval(isset($lcqcfg['total_reward_linhthach']) ? $lcqcfg['total_reward_linhthach'] : 500);
$baMin = intval(isset($lcqcfg['total_reward_binh_anh_min']) ? $lcqcfg['total_reward_binh_anh_min'] : 1);
$baMax = intval(isset($lcqcfg['total_reward_binh_anh_max']) ? $lcqcfg['total_reward_binh_anh_max'] : 2);

$lcqrows = '';
foreach ($lcquests as $qid => $q) {
    $progQ = isset($questData['progress'][$qid]) ? intval($questData['progress'][$qid]) : 0;
    $targetQ = intval($q['target']);
    $doneQ = min($progQ, $targetQ);
    $pctQ = $targetQ > 0 ? min(100, intval($doneQ * 100 / $targetQ)) : 0;
    $okQ = $doneQ >= $targetQ;
    $lcqrows .= '<div class="lcq-item' . ($okQ ? ' lcq-done' : '') . '">'
        . '<div class="lcq-item-head"><span>' . $q['icon'] . ' ' . $q['name'] . '</span>'
        . '<span class="lcq-count">' . $doneQ . '/' . $targetQ . ($okQ ? ' ✅' : '') . '</span></div>'
        . '<div class="lcq-desc">' . $q['description'] . '</div>'
        . '<div class="lcq-bar"><div class="lcq-bar-fill" style="width:' . $pctQ . '%"></div></div>'
        . '</div>';
}
?>
<div class="lcq-wrap">
    <nav class="lc-tabbar">
        <a href="?cmd=<?= $lccmd ?>"><i>☯</i><b>Luyện Căn</b></a>
        <a class="on" href="?cmd=<?= $lcqcmd ?>"><i>📜</i><b>Nhiệm Vụ</b></a>
        <a href="?cmd=<?= $lcbcmd ?>"><i>👹</i><b>Boss Ngũ Hành</b></a>
    </nav>
    <div class="lcq-card">
        <div class="lcq-head">
            <span class="lcq-title">📜 NHIỆM VỤ LINH CĂN</span>
            <span class="lcq-week">Tuần <?= $lcweek ?></span>
        </div>
        <?= $claimMsg ?>
        <div class="lcq-status <?= $isCompleted ? 'is-done' : ($allComplete ? 'is-ready' : 'is-open') ?>">
            <?= $isCompleted ? '✅ Đã nhận thưởng tuần này — quay lại tuần sau'
                : ($allComplete ? '✅ Đã đủ 5/5 nhiệm vụ — nhận thưởng bên dưới'
                                : 'Đang thực hiện — nhận khi đủ 5/5') ?>
        </div>
        <?= $lcqrows ?>
        <div class="lcq-reward">
            <b>🎁 Thưởng hoàn thành:</b> <?= $ltReward ?> Linh thạch ·
            <?= $baMin ?>-<?= $baMax ?> Bình Anh Tinh Thạch (vào túi đồ thật)
        </div>
        <?php if (!$isCompleted && $allComplete): ?>
        <div class="lcq-claimwrap">
            <a class="lcq-btn" href="?cmd=<?= $claimCmd ?>">NHẬN THƯỞNG</a>
        </div>
        <?php endif; ?>
    </div>
</div>
