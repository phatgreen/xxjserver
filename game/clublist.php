<?php
/**
 * Danh sách môn phái thiên bảng - giao diện mới
 */
$clublist = '';
$allclub = \player\getclub_all($dblj);
$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$createclubcmd = $encode->encode("cmd=club&canshu=createclub&sid=$sid");

$clubplayer = \player\getclubplayer_once($sid, $dblj);
$canCreate = (!$clubplayer && intval($player->ulv) >= 30);
$createBtnHtml = $canCreate ? '<a class="tcg-club-btn purple" href="?cmd='.$createclubcmd.'">Tạo Môn Phái</a>' : '';

$totalClub = 0;
$topClubName = 'Chưa có';

if ($allclub) {
    $i = 0;
    foreach ($allclub as $club) {
        $i++;
        if ($i == 1) {
            $topClubName = $club['clubname'];
        }
        $clubcmd = $encode->encode("cmd=club&clubid={$club['clubid']}&sid=$sid");
        $rankClass = '';
        $rankIcon = '✦';
        if ($i == 1) {
            $rankClass = ' top1';
            $rankIcon = '👑';
        } elseif ($i == 2) {
            $rankClass = ' top2';
            $rankIcon = '🥈';
        } elseif ($i == 3) {
            $rankClass = ' top3';
            $rankIcon = '🥉';
        }

        $clublist .= <<<HTML
<a class="tcg-club-rank-card$rankClass" href="?cmd=$clubcmd">
    <span class="tcg-club-rank-no">$rankIcon</span>
    <span class="tcg-club-rank-main">
        <span class="tcg-club-rank-title">Hạng $i</span>
        <strong>{$club['clubname']}</strong>
    </span>
    <span class="tcg-club-rank-arrow">Xem →</span>
</a>
HTML;
    }
    $totalClub = $i;
}

if ($clublist == '') {
    $clublist = <<<HTML
<div class="tcg-club-rank-empty">
    <div class="tcg-club-rank-empty-icon">🏯</div>
    <div>Hiện chưa có môn phái nào trên thiên bảng.</div>
</div>
HTML;
}

$clubhtml = <<<HTML
<div class="tcg-club-rank-page">
    <div class="tcg-club-rank-hero">
        <img src="./images/menpai.png" alt="Môn phái">
        <div class="tcg-club-rank-hero-mask"></div>
        <div class="tcg-club-rank-hero-text">
            <div class="tcg-club-rank-label">Thiên bảng</div>
            <h1>Môn Phái Thiên Bảng</h1>
            <p>Danh sách các môn phái đang tồn tại trong Tu Chân Giới.</p>
        </div>
    </div>

    <div class="tcg-club-rank-stats">
        <div class="tcg-club-rank-stat">
            <span>Tổng môn phái</span>
            <strong>$totalClub</strong>
        </div>
        <div class="tcg-club-rank-stat gold">
            <span>Đứng đầu thiên bảng</span>
            <strong>$topClubName</strong>
        </div>
    </div>

    <div class="tcg-club-rank-list">
        $clublist
    </div>

    <div class="tcg-club-rank-bottom">
        <a class="tcg-club-rank-back" href="#" onclick="history.back();return false;">← Trở Về</a>
		$createBtnHtml
        <a class="tcg-club-rank-home" href="game.php?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;

echo $clubhtml;
?>
