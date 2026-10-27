<?php
error_reporting(0);
header("Content-type: text/html; charset=utf-8"); 
?>
<html>
<head>
<title>Tu Chân Giới</title>

<link rel="stylesheet" href="../css/mystyle.css?v=2026101003">
</head>
<body class="gm-admin-page">
<div>
  <div><span>Mã GM: </span><input type='password' value='' id='checknum'></div>
  <div><span>Máy chủ: </span><select id='qu'><option value='1'>S1</option></select></div>
  <div><span>Nhân vật: </span><input type='text' value='' id='uid' placeholder='Nhập trò chơi tên nhân vật'>
  <div><span>Nạp Linh thạch: </span><input type="text" class="form-control" value='' id="chargenum" ><input type='button' value='Nạp tiền' id='chargebtn'></div> 
  <div><span>Nạp Ma thạch: </span><input type="text" class="form-control" value='' id="chargenum1" ><input type='button' value='Nạp tiền' id='chargebtn1'></div> 
  <div><span>Thiết lập đẳng cấp: </span><input type="text" class="form-control" value='' id="chargenum2" ><input type='button' value='Thiết lập' id='chargebtn2'></div> 
  <div><span>Thiết lập kinh nghiệm: </span><input type="text" class="form-control" value='' id="chargenum3" ><input type='button' value='Thiết lập' id='chargebtn3'></div> 
  <div><span>Thiết lập huyết lượng: </span><input type="text" class="form-control" value='' id="chargenum4" ><input type='button' value='Thiết lập' id='chargebtn4'></div> 
  <div><span>Thiết lập HP tối đa: </span><input type="text" class="form-control" value='' id="chargenum5" ><input type='button' value='Thiết lập' id='chargebtn5'></div> 
  <div><span>Thiết lập công kích: </span><input type="text" class="form-control" value='' id="chargenum6" ><input type='button' value='Thiết lập' id='chargebtn6'></div> 
  <div><span>Thiết lập phòng ngự: </span><input type="text" class="form-control" value='' id="chargenum7" ><input type='button' value='Thiết lập' id='chargebtn7'></div> 
 <hr/><br>
</div>
<script src='jquery-1.7.2.min.js'></script>
<script>
  var checknum='';
  var uid='';
  var qu=$('#qu').val();
  $('#checknum').change(function(){
	  checknum=$(this).val();
  });
  $('#uid').change(function(){
	  uid=$.trim($(this).val());
  });
  $('#qu').change(function(){
	  qu=$.trim($(this).val());
  });
  $('#addvipbtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'addvip',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#zhfhbtn').click(function(){
	  if(checknum==''){
		  alert('Nhập hậu mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tài khoản không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'zhfh',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#fhbtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'fh',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#zhjfbtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'zhjf',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#jfbtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'jf',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#jybtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'jy',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#jjbtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'jj',checknum:checknum,uid:uid,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum=$('#chargenum').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge',checknum:checknum,uid:uid,num:chargenum,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn1').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum1').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge1',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn2').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum2').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge2',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn3').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum3').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge3',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn4').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum4').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge4',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn5').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum5').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge5',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn6').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum6').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge6',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#chargebtn7').click(function(){
	  if(checknum==''){
		  alert('Vui lòng nhập mã GM.');
		  return false;
	  }
	  if(uid==''){
		  alert('Tên nhân vật không thể để trống.');
		  return false;
	  }
	  var chargenum1=$('#chargenum7').val();
	  $.ajax({
		  url:'gmquery.php',
		  type:'post',
		  'data':{type:'charge7',checknum:checknum,uid:uid,num:chargenum1,qu:qu},
          'cache':false,
          'dataType':'json',
		  success:function(data){
			  console.log('data',data);
			  alert(data.info);
		  },
		  error:function(){
			  alert('Thao tác thất bại');
		  }
	  });
  });
  $('#mailid').live('change',function(){
	  console.log('test');
	  var desc=$('#mailid option:selected').data('desc');
	  $('#maildesc').html(desc);
  });
</script>
</body>
</html>
