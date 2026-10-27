<?php
/**
 * Hảo Hữu - giao diện tiên hiệp
 * Cập nhật: 20260630
 */
if (isset($canshu)) {
    if ($canshu == "deim") {
        $uid = intval($uid);
        $stmt = $dblj->prepare("DELETE FROM im WHERE imuid = :uid AND sid = :sid");
        $stmt->execute(array(':uid' => $uid, ':sid' => $sid));
    }
}

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$stmt = $dblj->prepare("SELECT * FROM im WHERE sid = :sid");
$stmt->execute(array(':sid' => $sid));
$ret = $stmt;
$imitem = $ret ? $ret->fetchAll(PDO::FETCH_ASSOC) : array();

$imlist = '';
$totalFriend = 0;
$onlineFriend = 0;

foreach ($imitem as $im) {
    $imuid = isset($im['imuid']) ? intval($im['imuid']) : 0;
    if ($imuid <= 0) {
        continue;
    }

    $implayer = \player\getplayer1($imuid, $dblj);
    if (!$implayer || !isset($implayer->uid)) {
        continue;
    }

    $totalFriend++;

    $isOnline = (isset($implayer->sfzx) && intval($implayer->sfzx) == 1);
    if ($isOnline) {
        $onlineFriend++;
    }

    $playercmd = $encode->encode("cmd=getplayerinfo&uid=$imuid&sid=$sid");
    $delcmd = $encode->encode("cmd=im&canshu=deim&uid=$imuid&sid=$sid");

    $uname = htmlspecialchars($implayer->uname, ENT_QUOTES, 'UTF-8');
    $ulv = isset($implayer->ulv) ? intval($implayer->ulv) : 0;
    $ugj = isset($implayer->ugj) ? intval($implayer->ugj) : 0;
    $ufy = isset($implayer->ufy) ? intval($implayer->ufy) : 0;
    $uhp = isset($implayer->uhp) ? intval($implayer->uhp) : 0;
    $umaxhp = isset($implayer->umaxhp) ? intval($implayer->umaxhp) : 0;

    $statusClass = $isOnline ? ' online' : ' offline';
    $statusText = $isOnline ? 'Đang online' : 'Rời mạng';
    $avatarChar = function_exists('mb_substr') ? mb_substr($uname, 0, 1, 'UTF-8') : substr($uname, 0, 1);

    $imlist .= '<div class="im-friend-card'.$statusClass.'">';
    $imlist .= '  <div class="im-avatar">'.$avatarChar.'</div>';
    $imlist .= '  <div class="im-friend-main">';
    $imlist .= '      <div class="im-friend-top">';
    $imlist .= '          <a class="im-friend-name" href="?cmd='.$playercmd.'">'.$uname.'</a>';
    $imlist .= '          <span class="im-status">'.$statusText.'</span>';
    $imlist .= '      </div>';
    $imlist .= '      <div class="im-friend-stats">';
    $imlist .= '          <span>Lv.<b>'.$ulv.'</b></span>';
    $imlist .= '          <span>Công <b>'.$ugj.'</b></span>';
    $imlist .= '          <span>Phòng <b>'.$ufy.'</b></span>';
    $imlist .= '          <span>HP <b>'.$uhp.'/'.$umaxhp.'</b></span>';
    $imlist .= '      </div>';
    $imlist .= '  </div>';
    $imlist .= '  <div class="im-friend-actions">';
    $imlist .= '      <a class="im-btn view" href="?cmd='.$playercmd.'">Xem</a>';
    $imlist .= '      <a class="im-btn delete" href="?cmd='.$delcmd.'" onclick="return confirm(\'Xóa hảo hữu này?\');">Xóa</a>';
    $imlist .= '  </div>';
    $imlist .= '</div>';
}

if ($imlist == '') {
    $imlist = '
    <div class="im-empty">
        <div class="im-empty-icon">☯</div>
        <div class="im-empty-title">Chưa có hảo hữu</div>
        <div class="im-empty-desc">Kết giao thêm đạo hữu để cùng hành tẩu tu chân giới.</div>
    </div>';
}

$imhtml = <<<HTML
<div class="im-page">
    <div class="im-hero">
        <div class="im-hero-main">
            <div class="im-label">ĐẠO HỮU ĐỒNG HÀNH</div>
            <div class="im-title">Hảo Hữu</div>
            <div class="im-desc">Danh sách bằng hữu đã kết giao trong Tu Chân Giới.</div>
        </div>
        <div class="im-seal">Bạn</div>
    </div>

    <div class="im-summary">
        <div class="im-summary-item">
            <span>Tổng hảo hữu</span>
            <b>$totalFriend</b>
        </div>
        <div class="im-summary-item online">
            <span>Đang online</span>
            <b>$onlineFriend</b>
        </div>
        <div class="im-summary-item">
            <span>Trạng thái</span>
            <b>Kết giao</b>
        </div>
    </div>

    <div class="im-list">
        $imlist
    </div>

    <div class="im-bottom">
        <a class="im-back" href="#" onClick="javascript:history.back(-1);return false;">↩ Trở Về</a>
        <a class="im-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $imhtml;
?>