<?php
error_reporting(0);
header("Content-type: text/html; charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
if($_POST){
	include 'config.php';
	$gmcode=trim($_POST['checknum']);
	if(!password_verify($gmcode, GM_CODE_HASH)){
		$return=array(
			'errcode'=>1,
			'info'=>'Mã GM sai',
		);
		exit(json_encode($return));
	}
	$quid=trim($_POST['qu']);
	if($quid==''){
		$return=array(
			'errcode'=>1,
			'info'=>'Máy chủ sai',
		);
		exit(json_encode($return));
	}
	$qu=$quarr[$quid];
	if(!$qu['ip']){
		$return=array(
			'errcode'=>1,
			'info'=>'Không có máy chủ này',
		);
		exit(json_encode($return));
	}
	$uid=trim($_POST['uid']);
	if($uid==''){
		$return=array(
			'errcode'=>1,
			'info'=>'ID nhân vật không đúng',
		);
		exit(json_encode($return));
	}
	$uid=mysqli_real_escape_string(mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']),$uid);
	$srvid=$qu['srvid'];
	$act=$_POST['type'];
	switch($act){
		case 'charge':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET uyxb=uyxb+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Nạp tiền thành công!');
			exit(json_encode($return));
			break;
		case 'charge1':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET uczb=uczb+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Nạp tiền thành công!');
			exit(json_encode($return));
			break;
		case 'charge2':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET ulv=ulv+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Nâng cấp thành công!');
			exit(json_encode($return));
			break;
		case 'charge3':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET uexp=uexp+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Cộng tu vi thành công!');
			exit(json_encode($return));
			break;
		case 'charge4':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET uhp=GREATEST(0, uhp+?) WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Cộng HP thành công!');
			exit(json_encode($return));
			break;
		case 'charge5':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET umaxhp=umaxhp+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Cộng MaxHP thành công!');
			exit(json_encode($return));
			break;
		case 'charge6':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET ugj=ugj+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Cộng Công Kích thành công!');
			exit(json_encode($return));
			break;
		case 'charge7':
			$num=intval($_POST['num']);
			if(!$num){
				$return=array('errcode'=>1,'info'=>'Nạp tiền số lượng lỗi');
				exit(json_encode($return));
			}
            $conn = mysqli_connect($qu['ip'],$qu['user'],$qu['pswd']);
            if(!$conn){
				$return=array('errcode'=>1,'info'=>'Kết nối cơ sở dữ liệu không thành công!');
				exit(json_encode($return));
            }
            mysqli_select_db($conn,$qu['db']);
            $stmt = mysqli_prepare($conn, "SELECT * FROM game1 WHERE uname = ?");
            mysqli_stmt_bind_param($stmt, "s", $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if(!$row){
				mysqli_close($conn);
				$return=array('errcode'=>0,'info'=>'Tài khoản không tồn tại!');
				exit(json_encode($return));
            }else{
			    $stmt2 = mysqli_prepare($conn, "UPDATE game1 SET ufy=ufy+? WHERE uname=?");
			    $stmt2_num = intval($num);
			    mysqli_stmt_bind_param($stmt2, "is", $stmt2_num, $uid);
			    mysqli_stmt_execute($stmt2);
			}
			mysqli_close($conn);
			$return=array('errcode'=>0,'info'=>'Cộng Phòng Thủ thành công!');
			exit(json_encode($return));
			break;
		default:
			$return=array('errcode'=>1,'info'=>'Dữ liệu lỗi');
			exit(json_encode($return));
			break;
	}
}else{
	$return=array('errcode'=>1,'info'=>'Gửi lỗi');
	exit(json_encode($return));
}
