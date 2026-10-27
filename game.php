<?php
ob_start();
// error_reporting(0); // Tắt hiển thị lỗi (có thể bật khi debug)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

// Nạp các class cần thiết
require_once 'class/player.php';
require_once 'class/encode.php';
// Kết nối PDO database
include_once 'pdo.php';

// --- HEADER CHỐNG CACHE ---
// Tránh trình duyệt lưu cache trang
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Khởi tạo các class
$encode = new \encode\encode();   // class mã hóa command
$player = new \player\player();   // class player
$guaiwu = new \player\guaiwu();   // class quái vật
$clmid  = new \player\clmid();    // class map
$npc    = new \player\npc();      // class npc

// File mặc định sẽ include
$ym = 'game/nowmid.php';
// Lấy toàn bộ query string
$Dcmd = $_SERVER['QUERY_STRING'];
$pvpts =''; // thông báo pvp
$tpts  = ''; // thông báo đột phá
// Bắt đầu session
session_start();


/*
|--------------------------------------------------------------------------
| 1. CHỐNG SPAM CLICK (ANTI FLOOD)
|--------------------------------------------------------------------------
| Giới hạn người chơi click quá nhanh
| Nếu click liên tục dưới khoảng thời gian cho phép thì chặn
*/

$allow_sep = 10; // khoảng cách tối thiểu giữa 2 lần click (ms)

// Lấy thời gian hiện tại tính theo millisecond
function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

// Detect AJAX sớm nhất có thể (trước anti-flood)
$isAjaxEarly = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
    || (isset($_SERVER['HTTP_X_GAME_AJAX']) && $_SERVER['HTTP_X_GAME_AJAX'] === '1');

// Debug AJAX detection
if ($isAjaxEarly) {
    error_log('[GA] AJAX detected. X-Requested-With=' . (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : 'NONE') . ' X-Game-Ajax=' . (isset($_SERVER['HTTP_X_GAME_AJAX']) ? $_SERVER['HTTP_X_GAME_AJAX'] : 'NONE'));
}

$current_time = getMillisecond();

// Kiểm tra nếu click quá nhanh
if (isset($_SESSION["post_sep"]) && ($current_time - $_SESSION["post_sep"] < $allow_sep)) {
    if ($isAjaxEarly) {
        error_log('[GA] Anti-flood BLOCKED ajax request. Diff=' . ($current_time - $_SESSION["post_sep"]) . 'ms');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('ok' => false, 'msg' => 'Ngươi nhấn quá nhanh!'));
        exit();
    }
    $msg = '<meta name="viewport" content="width=device-width,user-scalable=no">
    Ngươi nhấn quá nhanh! <br/><a href="?'.$Dcmd.'">Tiếp tục</a>';
    exit($msg);
}

// Lưu lại thời gian click
$_SESSION["post_sep"] = $current_time;
/*
|--------------------------------------------------------------------------
| 2. XỬ LÝ COMMAND
|--------------------------------------------------------------------------
*/

// Phân tích query string thành biến
parse_str($Dcmd);
// Nếu không có cmd thì quay về index
if (!isset($cmd)){
	header("refresh:1;url=index.php");
    exit();
}

/*
| Các lệnh cần phải mã hóa để tránh người chơi sửa URL
*/
$needEncode = array(
    'cjplayer','djinfo','zbinfo','npc','duihuan','shangdian','sendliaotian'
);

// Nếu command thuộc nhóm cần mã hóa
if (in_array($cmd, $needEncode)) {
	$safe_cmd = $encode->encode($Dcmd);
	header("Location: ?cmd=" . $safe_cmd);
	exit();
}

// Giải mã command
$Dcmd = $encode->decode($cmd);

// Parse lại biến
parse_str($Dcmd);



/*
|--------------------------------------------------------------------------
| SWITCH COMMAND
|--------------------------------------------------------------------------
*/

switch ($cmd){
	/*
	|--------------------------------------------------------------------------
	| LOGIN
	|--------------------------------------------------------------------------
	*/
	case 'login':
		// Lấy thông tin player
		$player = \player\getplayer($sid,$dblj);

		// command vào map hiện tại
		$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

		$nowdate = date('Y-m-d H:i:s');

		// Bế Quan: tích tu vi theo giờ offline (phải tính TRƯỚC khi làm mới endtime)
		\player\offline_reward_on_login($player, $dblj);

		// update trạng thái online
		$stmt = $dblj->prepare("UPDATE game1 SET endtime=?,sfzx=1 WHERE sid=?");
		$stmt->execute([$nowdate,$sid]);

		header("refresh:1;url=?cmd=$gonowmid");
		exit();
	break;

	/*
	|--------------------------------------------------------------------------
	| TẠO NHÂN VẬT
	|--------------------------------------------------------------------------
	*/
	case 'cjplayer':
		if(isset($token,$username,$sex)){
			// Kiểm tra trùng tên
			$stmt = $dblj->prepare("SELECT uname FROM game1 WHERE uname=?");
			$stmt->execute([$username]);
			$ret = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($ret){
				$tishi = "Người chơi:【{$ret['uname']}】đã tồn tại<br><br>";
				$ym = 'game/cj.php';
				break;
			}

			// Kiểm tra độ dài tên
			if(strlen($username)<6 || strlen($username)>12){
				$tishi = "Tên người dùng không thể quá ngắn hoặc quá dài<br><br>";
				$ym = 'game/cj.php';
				break;
			}

			// Chống XSS
			$username = htmlspecialchars($username);
			// Tạo SID
			$sid = md5($username.$token.'229');

			// Kiểm tra token đã có player chưa
			$stmt = $dblj->prepare("SELECT sid FROM game1 WHERE token=?");
			$stmt->execute([$token]);
			$player = $stmt->fetch(PDO::FETCH_ASSOC);

			if(!$player){
				// Lấy config game
				$gameconfig = \player\getgameconfig($dblj);
				$firstmid = $gameconfig->firstmid;
				$nowdate = date('Y-m-d H:i:s');

				// Nhân vật mới khởi đầu trắng: không có Linh Căn / Thể Chất / Huyết Mạch,
				// đạt được về sau qua đan, nhiệm vụ hoặc boss.
				// Insert player mới
				$sql = "INSERT INTO game1
				(token,sid,uname, ulv,uyxb,uczb, uexp,uhp,umaxhp,ustamina,umaxstamina,ugj,ufy,uwx,usex, vip,nowmid,endtime,sfzx,shenfen,linhcan_id,linhcan_quality,thechat_id,thechat_quality,huyetmach_id,huyetmach_quality)
				VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$stmt = $dblj->prepare($sql);
				$stmt->execute([
                    $token,
                    $sid,
                    $username,
                    1,      // level
                    2000,   // linh thạch
                    100,    // ma thạch
                    0,      // exp
                    35,     // hp
                    35,     // Giới hạn hp
                    100,    // năng lượng hiện tại
                    100,    // năng lượng tối đa
                    12,     // công
                    5,      // phòng
                    0,      // ngũ hành
                    $sex,   // Giới tính
                    0,      // VIP
                    $firstmid, // Map đầu tiên
                    $nowdate, // Thời gian đăng nhập
                    1,      // Trạng thái online
                    $shenfen, // Danh tính
                    0, // Linh căn
                    0, // Phẩm chất linh căn
                    0, // Thể chất
                    0, // Phẩm chất thể chất
                    0, // Huyết mạch
                    0 // Phẩm chất huyết mạch
				]);

				// vào map đầu
				$gonowmid = $encode->encode("cmd=gomid&newmid=$firstmid&sid=$sid");
				echo '<meta charset="utf-8">';
				echo "{$username} <font color=#8EBE67>Hoan Nghênh Ngươi Đến Tu Chân Giới</font><br>";

				// gửi thông báo chat hệ thống
				$stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
				$stmt->execute(['【Hệ thống】',"Chào mừng {$username} bước lên tiên đồ",0]);
				header("refresh:2;url=?cmd=$gonowmid");
			}
			exit();
		}
	break;

	/*
	|--------------------------------------------------------------------------
	| CHAT
	|--------------------------------------------------------------------------
	*/
	case 'sendliaotian':
		if(isset($ltlx,$ltmsg)){
			$player = player\getplayer($sid,$dblj);
			if(!$player->uname) break;
			
			// chống XSS
			$ltmsg = htmlspecialchars($ltmsg);

			// Chat toàn server
			if($ltlx=='all'){
				$stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
				$stmt->execute([
					$player->uname,
					$ltmsg,
					$player->uid
				]);
			}

			// Chat riêng
			elseif($ltlx=='im'){
				$stmt = $dblj->prepare(
					"INSERT INTO imliaotian(name,msg,uid,imuid) VALUES(?,?,?,?)"
				);
				$stmt->execute([
					$player->uname,
					$ltmsg,
					$player->uid,
					$imuid
				]);
			}

			$ym='game/liaotian.php';
		}
	break;

	/*
	|--------------------------------------------------------------------------
	| DEFAULT COMMAND ROUTER
	|--------------------------------------------------------------------------
	*/
	default:

		$cmdMap = [
			'cj'=>'game/cj.php',
			'zhuangtai'=>'game/zhuangtai.php', // Thông tin cá nhân
			'trangbi'=>'game/trangbi.php', // Trang bị đang mặc
			'gomid'=>'game/nowmid.php', // Đại sảnh
			'getginfo'=>'game/ginfo.php',
			'pve'=>'game/pve.php',
			'pvp'=>'game/pvp.php',
			'pvegj'=>'game/pve.php',
			'pvbgj'=>'game/boss.php',
			'liaotian'=>'game/liaotian.php', // Tán gẫu
			'taskguide'=>'game/taskguide.php', // Tin tức -> nhiệm vụ có thể nhận
			'getplayerinfo'=>'game/otherzhuangtai.php',
			'zbinfo'=>'game/zbinfo.php',
			'npc'=>'npc/npc.php',
			'paihang'=>'game/paihang.php',
			'chakanzb'=>'game/zbinfo.php',
			'djinfo'=>'game/djinfo.php',
			'getbagzb'=>'game/bagzb.php', // Túi đồ
			'getbagyp'=>'game/bagyp.php', // Túi đồ -> Dược phẩm
			'getbagyd'=>'game/bagyd.php', // Túi đồ -> Đan dược ( Hiệu Thuốc )
			'getbagjn'=>'game/bagjn.php', // Túi đồ -> kỹ năng
			'xxzb'=>'game/trangbi.php',
			'setzbwz'=>'game/trangbi.php',
			'quickequip'=>'game/trangbi.php',
			'allmap'=>'game/allmap.php',
			'gamnol'=>'game/gamnol.php',
			'delezb'=>'game/bagzb.php',
			'getbagdj'=>'game/bagdj.php', // Túi đồ -> Đạo cụ
			'usedaoju'=>'game/bagdj.php', // Dùng đạo cụ (đan) từ túi
			'upzb'=>'game/zbinfo.php',
			'goxiulian'=>'game/xiulian.php', // Tu luyện -> Ngồi Thiền
			'wgxiulian' =>'game/wugong.php',
			'wgxl'      =>'game/wugong.php', // Tu luyện -> Tu hành -> Tu hành võ công
			'jswg'      =>'game/wugong.php',
			'taopao'=>'game/boss.php',
			'xxwg'=>'game/xxwg.php', // Tu luyện -> Tu hành -> Bí Tịch
			'startxiulian'=>'game/xiulian.php', // bắt đầu ngồi thiền tu luyện ( hấp thụ linh khí )
			'endxiulian'=>'game/xiulian.php',
			'task'=>'game/task.php',
			'mytask'=>'game/playertask.php',
			'mytaskinfo'=>'game/playertaskinfo.php',
			'boss'=>'game/bossinfo.php',
			'ypinfo'=>'game/ypinfo.php',
			'ydinfo'=>'game/ydinfo.php',
			'pvb'=>'game/boss.php',
			'chongwu'=>'game/chongwu.php',
			'jninfo'=>'game/jninfo.php',
			'zbinfo_sys'=>'game/zbinfo_sys.php',
			'tupo'=>'game/tupo.php',
			'fangshi'=>'game/fangshi.php',
			'club'=>'game/club.php',
			'clubboss_fight'=>'game/club_boss.php',
			'clublist'=>'game/clublist.php',
			'duihuan'=>'game/duihuan.php',
			'im'=>'game/im.php',
			'shangdian'=>'game/shangdian.php',  // Cửa hàng -> Ma Thạch
			'qydt'=>'game/qydt.php',
			'toayeuthap'=>'game/toayeuthap.php',
			'vandinhdai'=>'game/vandinhdai.php',
			'hammo'=>'game/hammo.php',
			'ditu'=>'dt/ditu.html',
			'gaiming'=>'npc/muban/gaiming.php',
			'czbgm'=>'npc/muban/czbgm.php',
			'tianfu'=>'game/tianfu.php',
			'tianfu_collection'=>'game/tianfu_collection.php',
			'tianfu_open'=>'game/tianfu_open.php',
			'taozhuang'=>'game/taozhuang.php',
			'gocp'=>'game/congphap.php',
			'conghuong'=>'game/conghuong.php', // Cộng Hưởng (Tàng Kinh Các)
			'linhcan'=>'game/linhcan.php',
			'linhcan_wash'=>'game/linhcan_wash.php',
			'linhcan_quest'=>'game/linhcan_quest.php',
			'linhcan_boss'=>'game/linhcan_boss.php',
			'luonden'=>'game/luonden.php',
			'do_craft_luonden'=>'game/luonden.php',
			'use_detox_pill'=>'game/luonden.php',
			'chetao'=>'game/chetao.php',
			'do_craft_chetao'=>'game/chetao.php',
			'nap_hanh'=>'game/nap_hanh.php',
			'use_dj'=>'game/use_dj.php',
			'nhiemvu'=>'game/nhiemvu.php',
			'thiencoccac'=>'game/thiencoccac.php',
			'thechat'=>'game/thechat.php',
			'thechat_upgrade'=>'game/thechat_upgrade.php',
			'huyetmach'=>'game/huyetmach.php',
			'huyetmach_transform'=>'game/huyetmach_transform.php',
			'huyetmach_boss'=>'game/huyetmach_boss.php',
			'huyetmach_upgrade'=>'game/huyetmach_upgrade.php',
			'quydoi'=>'game/quydoi.php',
			'sauthu'=>'game/sauthu.php',
			'phoban'=>'game/phoban.php'
		];

		// router
		if(isset($cmdMap[$cmd])){
			$ym = $cmdMap[$cmd];
		}
	break;
}

/*
|--------------------------------------------------------------------------
| KIỂM TRA SID + TRẠNG THÁI ONLINE
|--------------------------------------------------------------------------
*/

if (!isset($sid) || $sid==''){
	if ($cmd!='cj' && $cmd!=='cjplayer'){
		header("refresh:1;url=index.php");
		exit();
	}
}elseif (!preg_match('/^[0-9a-f]{32}$/i', $sid)){
	// sid chỉ là chuỗi hex 32 ký tự do server sinh ra. Chặn mọi giá trị lạ
	// trước khi sid kịp đi vào câu truy vấn.
	header("refresh:1;url=index.php");
	exit();
}else{
	// Không phải PVE thì xóa quái map
	if ($cmd != 'pve' && $cmd!='pvegj'){
		$stmt = $dblj->prepare("DELETE FROM midguaiwu WHERE sid = ?");
		if ($stmt !== false) {
			$stmt->execute(array($sid));
		}
	}

	// Không phải boss thì xóa boss
	if ($cmd != 'pvb' && $cmd!='pvbgj'){
		$stmt = $dblj->prepare("DELETE FROM boss WHERE sid = ?");
		if ($stmt !== false) {
			$stmt->execute(array($sid));
		}
	}

	// Lấy thông tin player
	$player = \player\getplayer($sid,$dblj);

	/*
	| Nếu đang bị PVP
	*/
	if ($player->ispvp!=0){
		$pvper = \player\getplayer1($player->ispvp,$dblj);
		$pvpcmd = $encode->encode("cmd=pvp&uid=$pvper->uid&sid=$sid");
		$pvpcmd = "<a href='?cmd=$pvpcmd'>Đánh trả</a>";
		$pvpts = "$pvper->uname đang tấn công ngươi: $pvpcmd<br/>";
	}

	/*
	| Thông báo đột phá
	*/
	if ($cmd != 'tupo' && \player\istupo($sid,$dblj) !=8 && $player->uexp >= $player->umaxexp){
		$tupocmd = $encode->encode("cmd=tupo&sid=$sid");
		//$tupocmd = "<a href='?cmd=$tupocmd' class='btn-break'>Đột Phá</a>";
		$tpts =  "<a href='?cmd=$tupocmd' class='btn-break'>Đột Phá</a>";
		if($cmd == 'startxiulian'){
			$cmd = 'endxiulian';
		}
	}

	/*
	| Kiểm tra timeout login
	*/
	$nowdate = date('Y-m-d H:i:s');

	/*
	| Thể lực tự hồi: mỗi 5 phút hồi 1 điểm (Thiên Phú công 6 có thể rút còn 210 giây), không vượt quá giới hạn.
	| Dùng hàm chuẩn settle_stamina để thống nhất công thức với mọi nơi khác
	| (bao gồm cron tùy chọn). Không ghi nếu không có bước hồi nào.
	*/
	$nowTs = strtotime($nowdate);
	$settle = \player\settle_stamina(
		isset($player->ustamina)    ? $player->ustamina    : 0,
		isset($player->umaxstamina) ? $player->umaxstamina : 0,
		isset($player->stamina_time) ? $player->stamina_time : null,
		$nowTs,
		300,
		isset($player->tianfu_bonus['stamina']) ? floatval($player->tianfu_bonus['stamina']) : 0
	);
	if ($settle['steps'] > 0) {
		$stmt = $dblj->prepare("UPDATE game1 SET ustamina = ?, stamina_time = ? WHERE sid = ?");
		$stmt->execute(array($settle['value'], date('Y-m-d H:i:s', $settle['mark']), $sid));
		$player->ustamina = $settle['value'];
		$player->stamina_time = date('Y-m-d H:i:s', $settle['mark']);
		\player\player_cache_clear($sid);
	}

	$endTs = strtotime($player->endtime);
	if ($endTs === false || $endTs === null) {
		// endtime chưa có (mới đăng nhập) -> coi như vừa hoạt động, không kick
		$second = 0;
	} else {
		$second = floor(strtotime($nowdate) - $endTs);
	}

	// Chỉ đá ra login nếu thực sự rảnh RẤT lâu (1 tiếng = 3600s) không có request nào.
	// Quan trọng: endtime được làm mới MỖI request bên dưới, nên người chơi đang hoạt động
	// (kể cả chỉ ngồi đọc/tính toán trong túi đồ) KHÔNG bao giờ bị kick giữa chừng,
	// và action (vd: dùng đan) luôn được xử lý trước khi có thể bị đá.
	if ($second >= 3600){
		echo '<meta charset="utf-8" content="width=device-width,user-scalable=no" name="viewport">';
		echo $player->uname."Offline thời gian quá dài, mời đăng nhập lại";
		header("refresh:1;url=index.php");
		exit();
	}

	// Làm mới endtime trên MỌI request hợp lệ (không bị kick) -> không bao giờ hết hạn khi đang chơi.
	$stmt = $dblj->prepare("UPDATE game1 SET endtime = ?, sfzx = 1 WHERE sid = ?");
	$stmt->execute(array($nowdate, $sid));
	// Thưởng trực tuyến: cộng thời gian hoạt động trong ngày
	\player\online_reward_tick($sid, $dblj);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Tu Chân Giới</title>
    <link rel="stylesheet" href="css/gamecss.css?v=2026063001">
    <link rel="stylesheet" href="css/mystyle.css?v=2026102803">
    <link rel="stylesheet" href="css/game-alert.css?v=2026072401">
    <link rel="stylesheet" href="css/itemtip.css?v=2026100401">
    <script src="js/game-alert.js?v=2026072401"></script>
    <script src="js/itemtip.js?v=2026100402"></script>
</head>
<body class="bg-body tcg-bg-body">
<?php
$tcg_header_name = 'Khách';
$tcg_header_id = '';
$tcg_header_avatar = 'images/avatar_nam.png';
$tcg_header_linh = 0;
$tcg_header_ma = 0;
$tcg_home_href = 'index.php';
$tcg_news_href = '#';
$tcg_event_href = '#';
$tcg_guide_href = '#';
$tcg_comm_href = '#';
$tcg_active_nav = 'home';
if (isset($sid) && isset($player) && is_object($player)) {
    if (isset($player->uname) && $player->uname != '') {
        $tcg_header_name = $player->uname;
    }
    if (isset($player->uid) && $player->uid != '') {
        $tcg_header_id = 'ID: '.$player->uid;
    } elseif (isset($player->sid)) {
        $tcg_header_id = 'ID: '.substr($player->sid, 0, 6);
    }
    if (isset($player->nowmid)) {
        $tcg_home_href = '?cmd='.$encode->encode('cmd=gomid&newmid='.$player->nowmid.'&sid='.$sid);
    }
    if (isset($player->uyxb)) {
        $tcg_header_linh = intval($player->uyxb);
    }
    if (isset($player->uczb)) {
        $tcg_header_ma = intval($player->uczb);
    }
    $tcg_header_avatar = \player\player_avatar_img(isset($player->usex) ? $player->usex : 1);
    $tcg_news_href = '?cmd='.$encode->encode('cmd=taskguide&sid='.$sid);
    $tcg_event_href = '?cmd='.$encode->encode('cmd=allmap&sid='.$sid);
    $tcg_guide_href = '?cmd='.$encode->encode('cmd=gamnol&sid='.$sid);
    $tcg_comm_href = '?cmd='.$encode->encode('cmd=club&sid='.$sid);
}
switch ($cmd) {
    case 'taskguide':
        $tcg_active_nav = 'news';
        break;
    case 'mytask':
        $tcg_active_nav = 'event';
        break;
    case 'allmap':
        $tcg_active_nav = 'event';
        break;
    case 'gamnol':
        $tcg_active_nav = 'guide';
        break;
    case 'club':
        $tcg_active_nav = 'comm';
        break;
}
?>
<header class="tcg-topbar">
    <div class="tcg-logo">Tu Chân Giới</div>
    <nav class="tcg-nav">
        <a class="<?php echo $tcg_active_nav === 'home' ? 'active' : ''; ?>" href="<?php echo $tcg_home_href; ?>"><span class="nav-ico">🏠</span> Trang Chủ</a>
        <a class="<?php echo $tcg_active_nav === 'news' ? 'active' : ''; ?>" href="<?php echo $tcg_news_href; ?>"><span class="nav-ico">📰</span> Tin Tức</a>
        <a class="<?php echo $tcg_active_nav === 'event' ? 'active' : ''; ?>" href="<?php echo $tcg_event_href; ?>"><span class="nav-ico">🎁</span> Sự Kiện</a>
        <a class="<?php echo $tcg_active_nav === 'guide' ? 'active' : ''; ?>" href="<?php echo $tcg_guide_href; ?>"><span class="nav-ico">📖</span> Cẩm Nang</a>
        <a class="<?php echo $tcg_active_nav === 'comm' ? 'active' : ''; ?>" href="<?php echo $tcg_comm_href; ?>"><span class="nav-ico">💬</span> Cộng Đồng</a>
    </nav>
    <div class="tcg-userbox">
        <div class="tcg-avatar">
            <img src="<?php echo $tcg_header_avatar; ?>" alt="Ảnh nhân vật">
        </div>
        <div>
            <div class="tcg-username"><?php echo htmlspecialchars($tcg_header_name, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="tcg-userid"><?php echo htmlspecialchars($tcg_header_id, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="tcg-money tcg-linh"><span>💰</span><b><?php echo number_format($tcg_header_linh, 0, ',', '.'); ?></b></div>
        <div class="tcg-diamond"><span>💎</span><b><?php echo number_format($tcg_header_ma, 0, ',', '.'); ?></b></div>
    </div>
</header>
<div class="tcg-page">
    <div class="main tcg-main-panel">
        <?php
            if ($ym != ''){
                if ($ym!="game/pvp.php" && $pvpts != ''){
                    echo '<div class="tcg-alert">'.$pvpts.'</div>';
                }

                if ($tpts != '' && $ym != 'game/xiulian.php'){
                    echo '<div class="tcg-tip">'.$tpts.'</div>';
                }

                include "$ym";
            }
        ?>
    </div>

    <div class="footer tcg-footer">
        <footer>
            <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
            <script>
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });

            function changetime(){
                var ary = Array("Chủ Nhật","Thứ 2","Thứ 3","Thứ 4","Thứ 5","Thứ 6","Thứ 7");
                var Timehtml = document.getElementById('CurrentTime');
                if (!Timehtml) return;
                var date = new Date();
                Timehtml.innerHTML = '' + date.toLocaleString() + ' ' + ary[date.getDay()];
            }
            window.onload = function(){
                changetime();
                setInterval(changetime,1000);
            }
            </script>
            <div id="CurrentTime"><?php echo date('Y-m-d H:i:s') ?></div>
        </footer>
    </div>
</div>
</body>
</html>