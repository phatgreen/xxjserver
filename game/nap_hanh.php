<?php
/**
 * game/nap_hanh.php — route cmd=nap_hanh: rèn nạp 1 hệ ngũ hành vào 1 món trang bị.
 * Biến $he / $zbnowid / $sid do game.php parse_str từ chuỗi cmd đã mã hoá.
 */
$he = isset($he) ? strtolower(trim($he)) : '';
$nhZbnowid = isset($zbnowid) ? intval($zbnowid) : 0;

$result = \player\nap_hanh_do($sid, $nhZbnowid, $he, $dblj);
// Cache nhân vật đã bị nạp_hanh_do xoá → đọc lại để có linh thạch + nội tại sau rèn.
$nhPlayer = \player\getplayer($sid, $dblj);
echo \player\nap_hanh_result_html($result, $nhZbnowid, $nhPlayer, $sid, $dblj, $encode);
?>
