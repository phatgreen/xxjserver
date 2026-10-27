<?php
// 提示框：成功
$tscg = <<<HTML
<font id="success" style="display:none;"></font>
<script type="text/javascript">
setTimeout(function(){
    if(document.all){
        document.getElementById("success").click();
    }else{
        var e=document.createEvent("MouseEvents");
        e.initEvent("click",true,true);
        document.getElementById("success").dispatchEvent(e);
    }
},500);
</script>
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
$('#success').click(function(){
    popup({type:'success',msg:"Thành công",delay:2000,callBack:function(){
        console.log('callBack~~~');
    }});
});
</script>
HTML;

// 提示框：失败
$tssb = <<<HTML
<font id="error" style="display:none;"></font>
<script type="text/javascript">
setTimeout(function(){
    if(document.all){
        document.getElementById("error").click();
    }else{
        var e=document.createEvent("MouseEvents");
        e.initEvent("click",true,true);
        document.getElementById("error").dispatchEvent(e);
    }
},500);
</script>
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
$('#error').click(function(){
    popup({type:'error',msg:"Thất bại",delay:2000,bg:true,clickDomCancel:true});
});
</script>
HTML;

$ydhp = '';
$ydgj = '';
$ydfy = '';
$ydbj = '';
$ydxx = '';
$setyd = '';
$tishi = '';
$ts = '';
$wgdaoli = '';

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$yaodan = \player\getyaodanonce($ydid,$dblj);
$playeryd = \player\getplayeryaodan($ydid,$sid,$dblj);

if (isset($canshu)){
    switch ($canshu){
        case 'setyd1':
            \player\changeplayersx('yd1',$ydid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 1 thành công";
            break;

        case 'setyd2':
            \player\changeplayersx('yd2',$ydid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 2 thành công";
            break;

        case 'setyd3':
            \player\changeplayersx('yd3',$ydid,$sid,$dblj);
            $tishi = "Thiết lập Thuốc 3 thành công";
            break;

        case 'useyd':
            if ($playeryd && $playeryd->ydsum > 0){
                $sjs = mt_rand(0,10);
                if ($sjs > 2){
                    if ($sjs > 3){
                        $userydret = \player\useyaodan($ydid,1,$sid,$dblj);
                        $tishi = "Tăng thành công";
                        $ts .= $tscg;
                        
                        // Tích lũy đan độc khi sử dụng đan
                        $ydConfig = \player\getyaodanonce($ydid, $dblj);
                        if ($ydConfig) {
                            $toxinBase = isset($ydConfig->toxin_base) ? intval($ydConfig->toxin_base) : 0;
                            if ($toxinBase > 0) {
                                \player\luonden_update_pill_toxin($sid, $toxinBase, $dblj);
                            }
                        }
                    }else{
                        $userydret = \player\deleyaodan($sid,$ydid,1,$dblj);
                        $tishi = "Tăng thất bại, vận khí không đủ";
                        $ts .= $tssb;
                    }
                }else{
                    $userydret = \player\deleyaodan($sid,$ydid,1,$dblj);
                    $tishi = "Tẩu hỏa nhập ma, tăng thất bại";
                    $ts .= $tssb;
                }
            }else{
                $tishi = "Đan dược không đủ";
                $ts .= $tssb;
            }
            break;

        case 'daoli':
            if ($playeryd && $playeryd->ydsum > 0){
                $sjs = mt_rand(0,10);
                if ($sjs > 0){
                    if ($sjs > 1){
                        $userydret = \player\useyaodan($ydid,1,$sid,$dblj);
                        $tishi = "Là kẻ ngoan nhân, tăng thành công";
                        $ts .= $tscg;
                        
                        // Tích lũy đan độc khi thôn phệ đan
                        $ydConfig = \player\getyaodanonce($ydid, $dblj);
                        if ($ydConfig) {
                            $toxinBase = isset($ydConfig->toxin_base) ? intval($ydConfig->toxin_base) : 0;
                            if ($toxinBase > 0) {
                                \player\luonden_update_pill_toxin($sid, $toxinBase, $dblj);
                            }
                        }
                    }else{
                        $userydret = \player\deleyaodan($sid,$ydid,1,$dblj);
                        $tishi = "Lành lạnh, thử phương thức khác xem";
                        $ts .= $tssb;
                    }
                }else{
                    $userydret = \player\deleyaodan($sid,$ydid,1,$dblj);
                    $tishi = "Tẩu hỏa nhập ma, tăng thất bại";
                    $ts .= $tssb;
                }
            }else{
                $tishi = "Không còn đan dược để dùng";
                $ts .= $tssb;
            }
            break;
    }

    // Cập nhật lại số lượng sau khi dùng / mất đan.
    $playeryd = \player\getplayeryaodan($ydid,$sid,$dblj);
}

if ($playeryd){
    $setyd1 = $encode->encode("cmd=ydinfo&canshu=setyd1&ydid=$ydid&sid=$sid");
    $setyd2 = $encode->encode("cmd=ydinfo&canshu=setyd2&ydid=$ydid&sid=$sid");
    $setyd3 = $encode->encode("cmd=ydinfo&canshu=setyd3&ydid=$ydid&sid=$sid");
    $useyd = $encode->encode("cmd=ydinfo&canshu=useyd&ydid=$ydid&sid=$sid");
    $daoli = $encode->encode("cmd=ydinfo&canshu=daoli&ydid=$ydid&sid=$sid");

    if(isset($player->wugong) && $player->wugong == 3){
        $wgdaoli = '<a id="load" class="yd-action yd-action-purple" href="?cmd='.$daoli.'">Nghịch luyện</a>';
    }

    $setyd = '
    <div class="yd-actions">
        <a id="load" class="yd-action yd-action-primary" href="?cmd='.$useyd.'">⚡ Sử dụng</a>
        <a id="load" class="yd-action yd-action-danger" href="?cmd='.$daoli.'">☯ Thôn phệ</a>
        '.$wgdaoli.'
    </div>';
}

if($yaodan->ydhp != 0){
    $ydhp = '<div class="yd-stat"><span>Huyết</span><strong>+'.$yaodan->ydhp.'</strong></div>';
}
if ($yaodan->ydgj != 0){
    $ydgj = '<div class="yd-stat"><span>Công</span><strong>+'.$yaodan->ydgj.'</strong></div>';
}
if ($yaodan->ydfy != 0){
    $ydfy = '<div class="yd-stat"><span>Phòng</span><strong>+'.$yaodan->ydfy.'</strong></div>';
}
if ($yaodan->ydbj != 0){
    $ydbj = '<div class="yd-stat"><span>Bạo</span><strong>+'.$yaodan->ydbj.'</strong></div>';
}
if ($yaodan->ydxx != 0){
    $ydxx = '<div class="yd-stat"><span>Hút máu</span><strong>+'.$yaodan->ydxx.'</strong></div>';
}

$ydsx = $ydhp.$ydgj.$ydfy.$ydbj.$ydxx;
if ($ydsx == '') {
    $ydsx = '<div class="yd-stat empty"><span>Thuộc tính</span><strong>Không có</strong></div>';
}


$ydEffectText = trim(strip_tags(str_replace('</div>', ' ', $ydsx)));
if ($ydEffectText == '') {
    $ydEffectText = 'Tăng thuộc tính tương ứng của đan dược';
}

$ydGuideHtml = '
<div class="yd-guide">
    <div class="yd-guide-title">Cách dùng đan dược</div>

    <div class="yd-guide-grid">
        <div class="yd-guide-card safe">
            <div class="yd-guide-head">
                <span>Sử dụng</span>
                <strong>Ổn định</strong>
            </div>
            <div class="yd-guide-rate">
                <b>Thành công:</b> 7/11 ≈ 63.6%
            </div>
            <div class="yd-guide-desc">
                Hấp thu đan dược theo cách thông thường. Thành công sẽ nhận hiệu quả gốc:
                <em>'.$ydEffectText.'</em>.
            </div>
            <div class="yd-guide-risk">
                Thất bại vẫn mất 1 đan dược.
            </div>
        </div>

        <div class="yd-guide-card devour">
            <div class="yd-guide-head">
                <span>Thôn phệ</span>
                <strong>Mạo hiểm</strong>
            </div>
            <div class="yd-guide-rate">
                <b>Thành công:</b> 9/11 ≈ 81.8%
            </div>
            <div class="yd-guide-desc">
                Cưỡng ép hấp thu đan dược. Tỷ lệ thành công cao hơn Sử dụng.
            </div>
            <div class="yd-guide-risk">
                Thất bại vẫn mất 1 đan dược, có khả năng tẩu hỏa nhập ma.
            </div>
        </div>
    </div>
</div>';

$ydsum = ($playeryd && isset($playeryd->ydsum)) ? intval($playeryd->ydsum) : 0;

$noticeHtml = '';
if ($tishi != '') {
    $noticeHtml = '<div class="yd-notice">'.$tishi.'</div>';
}

$ydinfo = <<<HTML
<div class="ydinfo-page ydinfo-page-v3">

    <div class="ydinfo-hero ydinfo-no-image">
        <div class="ydinfo-main">
            <div class="ydinfo-label">THÔNG TIN ĐAN DƯỢC</div>
            <h1 style="color:{$yaodan->ydys}">{$yaodan->ydname}</h1>

            <div class="ydinfo-count">
                <span>Còn lại</span>
                <strong>{$ydsum}</strong>
            </div>

            {$noticeHtml}

            <div class="yd-stat-grid">
                {$ydsx}
            </div>

            {$ydGuideHtml}

            {$setyd}
        </div>
    </div>

    {$ts}

    <div class="yd-bottom">
        <a class="yd-back" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a class="yd-home" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>

</div>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0" />
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
$('#load').click(function(){
    popup({type:'load',msg:"Vui lòng chờ",delay:1500,callBack:function(){
        popup({type:"success",msg:"Tải thành công",delay:1000});
    }});
});
</script>
HTML;

echo $ydinfo;
?>
