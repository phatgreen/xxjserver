<?php
$player = \player\getplayer($sid, $dblj);
$_SERVER['PHP_SELF'];

if (!isset($ltlx) || $ltlx == '') {
    $ltlx = 'all';
}

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$player->sid");
$goliaotian = $encode->encode("cmd=liaotian&ltlx=all&sid=$sid");
$imliaotian = $encode->encode("cmd=liaotian&ltlx=im&sid=$sid");

$lthtml = '';

// tcg_chat_escape / tcg_chat_format_msg / tcg_chat_player_link nam ở
// class/function/chatmsg.php (dung chung voi nowmid.php), da load qua class/player.php.

if ($ltlx == 'all') {
    $sql = 'SELECT * FROM ggliaotian ORDER BY id DESC LIMIT 10';
    $ltcxjg = $dblj->query($sql);

    if ($ltcxjg) {
        $ret = $ltcxjg->fetchAll(PDO::FETCH_ASSOC);

        $lthtml = '<div class="tcg-chat-page">';
        $lthtml .= '<div class="tcg-chat-tabs">';
        $lthtml .= '<span class="tcg-chat-tab active">🌐 Công cộng</span>';
        $lthtml .= '<a class="tcg-chat-tab" href="?cmd='.$imliaotian.'">✉ Nhắn riêng</a>';
        $lthtml .= '</div>';

        $lthtml .= '<div class="tcg-chat-box">';

        if (count($ret) <= 0) {
            $lthtml .= '<div class="tcg-chat-empty">Chưa có tin nhắn nào.</div>';
        }

        for ($i = 0; $i < count($ret); $i++) {
            $row = $ret[count($ret) - $i - 1];
            $uname = isset($row['name']) ? $row['name'] : '';
            $umsg  = isset($row['msg']) ? $row['msg'] : '';
            $uid   = isset($row['uid']) ? intval($row['uid']) : 0;

            if ($uid) {
                $safeMsg = tcg_chat_format_msg($umsg, false);
                $ucmd = $encode->encode("cmd=getplayerinfo&uid=$uid&sid=$player->sid");
                $lthtml .= '<div class="tcg-chat-row public">';
                $lthtml .= '<span class="tcg-chat-icon">💬</span>';
                $lthtml .= tcg_chat_player_link($ucmd, $uname);
                $lthtml .= '<span class="tcg-chat-colon">:</span>';
                $lthtml .= '<span class="tcg-chat-msg">'.$safeMsg.'</span>';
                $lthtml .= '</div>';
            } else {
                $safeMsg = tcg_chat_format_msg($umsg, true);
                $lthtml .= '<div class="tcg-chat-row system-row">';
                $lthtml .= '<span class="tcg-chat-icon">📣</span>';
                $lthtml .= '<span class="tcg-chat-system">【Hệ thống】</span>';
                $lthtml .= '<span class="tcg-chat-colon">:</span>';
                $lthtml .= '<span class="tcg-chat-msg">'.$safeMsg.'</span>';
                $lthtml .= '</div>';
            }
        }

        $lthtml .= '</div>';

        $lthtml .= '<form class="tcg-chat-form" method="get">';
        $lthtml .= '<input type="hidden" name="cmd" value="sendliaotian">';
        $lthtml .= '<input type="hidden" name="ltlx" value="all">';
        $lthtml .= '<input type="hidden" name="sid" value="'.tcg_chat_escape($sid).'">';
        $lthtml .= '<input class="tcg-chat-input" type="text" name="ltmsg" maxlength="50" autocomplete="off" placeholder="Nhập nội dung chat...">';
        $lthtml .= '<button class="tcg-chat-send" type="submit">Gửi</button>';
        $lthtml .= '<a class="tcg-chat-refresh" href="?cmd='.$goliaotian.'">Đổi mới</a>';
        $lthtml .= '</form>';
        $lthtml .= '</div>';
    }
}

if ($ltlx == 'im') {
    $stmt = $dblj->prepare("SELECT * FROM imliaotian WHERE uid = :uid OR imuid = :uid2 ORDER BY id DESC LIMIT 10");
    $stmt->execute(array(':uid' => $player->uid, ':uid2' => $player->uid));
    $ltcxjg = $stmt;

    if ($ltcxjg) {
        $ret = $ltcxjg->fetchAll(PDO::FETCH_ASSOC);

        $lthtml = '<div class="tcg-chat-page">';
        $lthtml .= '<div class="tcg-chat-tabs">';
        $lthtml .= '<a class="tcg-chat-tab" href="?cmd='.$goliaotian.'">🌐 Công cộng</a>';
        $lthtml .= '<span class="tcg-chat-tab active">✉ Nhắn riêng</span>';
        $lthtml .= '</div>';

        $lthtml .= '<div class="tcg-chat-box">';

        if (count($ret) <= 0) {
            $lthtml .= '<div class="tcg-chat-empty">Chưa có tin nhắn riêng nào.</div>';
        }

        for ($i = 0; $i < count($ret); $i++) {
            $row = $ret[count($ret) - $i - 1];
            $uname = isset($row['name']) ? $row['name'] : '';
            $umsg  = isset($row['msg']) ? $row['msg'] : '';
            $uid   = isset($row['uid']) ? intval($row['uid']) : 0;
            $imuid = isset($row['imuid']) ? intval($row['imuid']) : 0;

            if ($uid) {
                $safeMsg = tcg_chat_format_msg($umsg, false);
                $uplayer = \player\getplayer1($imuid, $dblj);
                $ucmd = $encode->encode("cmd=getplayerinfo&uid=$uid&sid=$player->sid");
                $imucmd = $encode->encode("cmd=getplayerinfo&uid=$imuid&sid=$player->sid");
                $toName = ($uplayer && isset($uplayer->uname)) ? $uplayer->uname : $imuid;

                $lthtml .= '<div class="tcg-chat-row private">';
                $lthtml .= '<span class="tcg-chat-icon">✉</span>';
                $lthtml .= '<span class="tcg-chat-private-tag">Nhắn riêng</span>';
                $lthtml .= tcg_chat_player_link($ucmd, $uname);
                $lthtml .= '<span class="tcg-chat-arrow">→</span>';
                $lthtml .= tcg_chat_player_link($imucmd, $toName);
                $lthtml .= '<span class="tcg-chat-colon">:</span>';
                $lthtml .= '<span class="tcg-chat-msg">'.$safeMsg.'</span>';
                $lthtml .= '</div>';
            }
        }

        $lthtml .= '</div>';
        $lthtml .= '</div>';
    }
}

$html = <<<HTML
$lthtml
<div class="tcg-chat-bottom">
    <a class="tcg-chat-back" href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
    <a class="tcg-chat-home" href="game.php?cmd=$gonowmid">Đại Sảnh →</a>
</div>
HTML;

echo $html;
?>
