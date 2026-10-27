<?php
// Thể Chất - Nâng Cấp
$player = \player\getplayer($sid, $dblj);

$thechatId = intval($player->thechat_id);
$thechatQuality = intval($player->thechat_quality);

$config = \player\thechat_config_get($thechatId);
$upgradeInfo = \player\thechat_get_upgrade_info($thechatQuality);

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$thcmd = $encode->encode("cmd=thechat&sid=$sid");

$msg = '';
if (isset($_POST['action']) && $_POST['action'] === 'upgrade') {
    $result = \player\thechat_upgrade($sid, $dblj);
    $msg = $result['msg'];
    $player = \player\getplayer($sid, $dblj);
    $thechatQuality = intval($player->thechat_quality);
    $upgradeInfo = \player\thechat_get_upgrade_info($thechatQuality);
}

// Phẩm hiện tại -> class phân tầng hiệu ứng (tcq-q1 .. tcq-q10): docs/UI_THECHAT.md §13
$tcqFx = 'tcq-q' . max(1, min(10, $thechatQuality));
$tcqColor = \player\thechat_quality_color($thechatQuality);
$tcqToColor = $upgradeInfo ? \player\thechat_quality_color(intval($upgradeInfo['to_quality'])) : $tcqColor;
list($tcqR, $tcqG, $tcqB) = sscanf($tcqColor, '#%02x%02x%02x');
list($tcqTr, $tcqTg, $tcqTb) = sscanf($tcqToColor, '#%02x%02x%02x');
$tcqRgb = intval($tcqR) . ',' . intval($tcqG) . ',' . intval($tcqB);
$tcqToRgb = intval($tcqTr) . ',' . intval($tcqTg) . ',' . intval($tcqTb);

$msgHtml = '';
if (!empty($msg)) {
    $isSuccess = (strpos($msg, 'thành công') !== false);
    $cls = $isSuccess ? '#6ee7b7' : '#f87171';
    $msgRgb = $isSuccess ? '110,231,183' : '248,113,113';
    $msgHtml = '<div class="tcq-msg" style="--msg-rgb:' . $msgRgb . '"><div class="tcq-msg-in" style="color:' . $cls . '">' . htmlspecialchars($msg) . '</div></div>';
}

$upgradeHtml = '';
if ($upgradeInfo) {
    $ltCost = number_format(intval($upgradeInfo['linh_thach_cost']));
    $matId = intval($upgradeInfo['material_id']);
    $matAmt = intval($upgradeInfo['material_amount']);
    $matName = \player\thechat_get_material_name($matId);
    $rate = intval($upgradeInfo['success_rate']);
    $toName = isset($upgradeInfo['to_name']) ? $upgradeInfo['to_name'] : 'Phẩm ' . $upgradeInfo['to_quality'];

    $extraHtml = '';
    if (isset($upgradeInfo['extra_material_id']) && intval($upgradeInfo['extra_material_id']) > 0) {
        $extraName = \player\thechat_get_material_name(intval($upgradeInfo['extra_material_id']));
        $extraAmt = intval($upgradeInfo['extra_material_amount']);
        $extraHtml = '<div style="margin:4px 0;color:#94a3b8">📦 ' . $extraName . ': ' . $extraAmt . '</div>';
    }

    $fromIcon = \player\thechat_quality_icon($thechatQuality);
    $fromBadgeName = htmlspecialchars(\player\thechat_quality_name($thechatQuality));
    $toIcon = \player\thechat_quality_icon(intval($upgradeInfo['to_quality']));
    $toBadgeName = htmlspecialchars($toName);

    $upgradeHtml = <<<HTML
<div class="tc-card tcq-card {$tcqFx}" style="--tcq:{$tcqColor};--tcq-rgb:{$tcqRgb};--tcq-to:{$tcqToColor};--tcq-to-rgb:{$tcqToRgb}">
    <div class="tcq-head"><i class="tcq-ico">⬆</i> Nâng cấp phẩm chất</div>
    <div class="tcq-row">
        <span class="tc-badge tcq-badge tcq-badge-from"><i class="tcq-ico">{$fromIcon}</i> {$fromBadgeName}</span>
        <span class="tcq-arrow" style="color:#94a3b8;margin:0 8px">→</span>
        <span class="tc-badge tcq-badge tcq-badge-to"><i class="tcq-ico">{$toIcon}</i> {$toBadgeName}</span>
    </div>
    <div class="tcq-cost" style="background:rgba(255,255,255,.05);border-radius:10px;padding:12px">
        <div style="color:#38bdf8;font-weight:800;margin-bottom:6px">💎 Linh Thạch: {$ltCost}</div>
        <div style="color:#94a3b8;margin:4px 0">📦 {$matName}: {$matAmt}</div>
        {$extraHtml}
        <div class="tcq-rate" style="color:#6ee7b7;font-weight:800;margin-top:8px">🎯 Tỷ lệ: {$rate}%</div>
    </div>
    <form method="post" style="text-align:center;margin-top:12px">
        <input type="hidden" name="action" value="upgrade">
        <button type="submit" class="tc-btn tc-btn-primary tcq-btn" style="width:100%">Nâng Cấp</button>
    </form>
</div>
HTML;
} else {
    $upgradeHtml = '<div class="tc-card tcq-card tcq-max ' . $tcqFx . '" style="--tcq:' . $tcqColor . ';--tcq-rgb:' . $tcqRgb . ';--tcq-to:' . $tcqColor . ';--tcq-to-rgb:' . $tcqRgb . '"><span class="tcq-max-in">Đã đạt phẩm chất tối đa!</span></div>';
}

?>
<style>
.tc-card{background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;padding:20px;margin-bottom:12px}
.tc-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;margin:4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
/* .main button (css/gamecss.css:122) đặc cách 0-1-1 > .tc-btn-primary 0-1-0, nên nút
   "Nâng Cấp" từ nãy đến giờ IN TRẮNG theo nút mặc định trình duyệt, mảng gradient
   cam-vàng bị ẩn hoàn toàn. Nâng lên 0-2-0 để trang hiển thị đúng thiết kế của nó. */
.main .tc-btn{display:inline-block;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:800;text-decoration:none;color:#fff;border:none;cursor:pointer}
.main .tc-btn-primary{background:linear-gradient(135deg,#f97316,#eab308);box-shadow:0 0 12px rgba(249,115,22,.3)}
.main .tc-btn-ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.tc-actions{display:flex;gap:10px;margin-top:16px;justify-content:center}

/* ===== Hiệu ứng phân tầng theo phẩm cấp (tcq-q1 .. tcq-q10) ===== */
/* Ba bẫy đã trả giá ở màn Nhục Thân, ghi lại ở đây:
   1) Aura dùng z-index:-1 nên thẻ BẮT BUỘC có isolation:isolate, không thì nó tụt ra
      sau background của thẻ và vô hình.
   2) Tắt animation phải viết animation:none; để --dur:0s thì Chrome vẫn giữ khung hình 0%.
   3) Clip gradient lên chữ phải đặt `background:` ngắn gọn TRƯỚC `-webkit-background-clip`,
      và emoji phải nhét vào <i class="tcq-ico"> vì color:transparent làm emoji biến mất.
   Chỉ dùng box-shadow/text-shadow/pseudo-element/opacity/transform: kích thước thẻ giữ nguyên. */
.tcq-head{font-weight:800;margin-bottom:12px;color:#c0a050}
.tcq-row{text-align:center;margin-bottom:12px}
.tcq-badge-to{color:#c0a050}
.tcq-max{text-align:center;color:#6ee7b7;font-weight:800}
.tcq-ico{font-style:normal}
.tcq-card{position:relative;isolation:isolate;overflow:hidden;box-shadow:0 0 var(--glow) rgba(var(--tcq-rgb),.3)}
.tcq-card::before{content:"";position:absolute;left:-18%;right:-18%;top:-45%;bottom:-45%;z-index:-1;pointer-events:none;
    background:radial-gradient(58% 42% at 50% 40%,rgba(var(--tcq-rgb),.32),rgba(var(--tcq-rgb),0) 72%);
    opacity:var(--aura-op);animation:tcqBreath var(--aura-dur) ease-in-out infinite}
.tcq-card::after{content:"";position:absolute;left:-42%;top:-60%;bottom:-60%;width:34%;z-index:0;pointer-events:none;opacity:0;
    background:linear-gradient(105deg,rgba(255,255,255,0),rgba(255,255,255,.15),rgba(255,255,255,0));
    transform:translateX(0) rotate(8deg);animation:tcqSweep var(--sweep-dur) linear infinite}
.tcq-badge{position:relative;box-shadow:0 0 var(--halo) rgba(var(--tcq-rgb),.5)}
.tcq-badge-to{box-shadow:0 0 var(--halo) rgba(var(--tcq-to-rgb),.5)}
/* .tcq-cost dùng inset ring chứ không dùng border: border cộng thêm 2px chiều cao panel,
   tức là xê dịch hình dáng cũ. Lớp hiệu ứng này chỉ được thêm sáng, không thêm cỡ. */
.tcq-cost{position:relative;box-shadow:inset 0 0 0 1px rgba(var(--tcq-rgb),.22)}
.tcq-rate{position:relative;text-shadow:0 0 var(--rate-glow) rgba(var(--tcq-rgb),.55)}
/* Mũi tên chỉ đổi opacity — không thêm display:inline-block vì nó dời baseline của dòng chữ. */
.tcq-arrow{animation:tcqFade var(--arrow-dur) ease-in-out infinite}
.tcq-btn{position:relative;overflow:hidden}
.tcq-btn::after{content:"";position:absolute;left:-50%;top:0;bottom:0;width:40%;pointer-events:none;opacity:0;
    background:linear-gradient(100deg,rgba(255,255,255,0),rgba(255,255,255,.42),rgba(255,255,255,0));
    transform:translateX(0);animation:tcqGlint var(--glint-dur) linear infinite}

/* Bảng cường độ: bậc càng cao càng nhanh và càng sáng; bậc 1 tĩnh nhưng VẪN PHẢI thấy hiệu ứng. */
.tcq-q1{--aura-op:.24;--aura-dur:6s;--glow:8px;--halo:0px;--rate-glow:0px;--sweep-op:0;--sweep-dur:6s;--glint-dur:6s;--arrow-dur:6s}
.tcq-q2{--aura-op:.30;--aura-dur:10s;--glow:10px;--halo:5px;--rate-glow:5px;--sweep-op:0;--sweep-dur:10s;--glint-dur:10s;--arrow-dur:10s}
.tcq-q3{--aura-op:.36;--aura-dur:8s;--glow:13px;--halo:7px;--rate-glow:7px;--sweep-op:0;--sweep-dur:8s;--glint-dur:8s;--arrow-dur:8s}
.tcq-q4{--aura-op:.44;--aura-dur:6.5s;--glow:16px;--halo:9px;--rate-glow:9px;--sweep-op:0;--sweep-dur:6.5s;--glint-dur:4.5s;--arrow-dur:6.5s}
.tcq-q5{--aura-op:.52;--aura-dur:5s;--glow:19px;--halo:11px;--rate-glow:11px;--sweep-op:.16;--sweep-dur:4.6s;--glint-dur:4s;--arrow-dur:5s}
.tcq-q6{--aura-op:.60;--aura-dur:4.2s;--glow:22px;--halo:13px;--rate-glow:13px;--sweep-op:.20;--sweep-dur:4s;--glint-dur:3.6s;--arrow-dur:4.2s}
.tcq-q7{--aura-op:.66;--aura-dur:3.4s;--glow:25px;--halo:15px;--rate-glow:15px;--sweep-op:.24;--sweep-dur:3.4s;--glint-dur:3.2s;--arrow-dur:5s}
.tcq-q8{--aura-op:.74;--aura-dur:2.8s;--glow:30px;--halo:18px;--rate-glow:17px;--sweep-op:.28;--sweep-dur:3s;--glint-dur:2.8s;--arrow-dur:4.2s}
.tcq-q9{--aura-op:.82;--aura-dur:2.4s;--glow:34px;--halo:20px;--rate-glow:19px;--sweep-op:.32;--sweep-dur:2.6s;--glint-dur:2.5s;--arrow-dur:3.6s}
.tcq-q10{--aura-op:.92;--aura-dur:2s;--glow:40px;--halo:24px;--rate-glow:22px;--sweep-op:.38;--sweep-dur:2.2s;--glint-dur:2.1s;--arrow-dur:3s}

/* Tat dong hieu ung theo tung bac — phai viet animation:none, khong dung --dur:0s.
   Bac 1: chi con aura tinh + glow tinh (van thay du, vi nguoi choi dang o bac nay).
   Que ngang: tu Thien Pham (5). Lat nut: tu Dia Pham (4). Mui ten nhap nhanh: tu Tien Pham (7). */
.tcq-q1.tc-card::before{animation:none}
.tcq-q1.tc-card::after,.tcq-q2.tc-card::after,.tcq-q3.tc-card::after,.tcq-q4.tc-card::after{animation:none;opacity:0}
.tcq-q1.tc-card .tcq-btn::after,.tcq-q2.tc-card .tcq-btn::after,.tcq-q3.tc-card .tcq-btn::after{animation:none;opacity:0}
.tcq-q1.tc-card .tcq-arrow,.tcq-q2.tc-card .tcq-arrow,.tcq-q3.tc-card .tcq-arrow,
.tcq-q4.tc-card .tcq-arrow,.tcq-q5.tc-card .tcq-arrow,.tcq-q6.tc-card .tcq-arrow{animation:none}

/* Chữ cầu vồng: DUY NHẤT Đạo Phẩm (chủ server chốt). Màu đặt trong class chứ không
   đặt inline, vì inline color thắng được màu trong của background-clip:text.
   Đạo Phẩm là phẩm cuối nên thẻ hiển thị là thẻ "tối đa" -> clip lên span bên trong,
   không clip lên .tcq-card (nó có background gradient riêng, clip vào là mất nền). */
.tcq-q10.tc-card .tcq-max-in,.tcq-q10.tc-card .tcq-head{
    background:linear-gradient(90deg,#ff6b6b,#ffb347,#fff45c,#6ee7b7,#5cc8ff,#c58cff,#ff6b6b);
    background-size:300% 100%;-webkit-background-clip:text;background-clip:text;
    color:transparent;-webkit-text-fill-color:transparent;animation:tcqRain 6s linear infinite}
.tcq-q10.tc-card .tcq-ico{-webkit-text-fill-color:#ffe9a8}

/* Ket qua nang cap: lop sang theo mau thanh bai + hieu ung xuat hien mot lan. */
.tcq-msg{position:relative;isolation:isolate;overflow:hidden;background:rgba(255,255,255,.06);border-radius:10px;padding:12px;margin-bottom:12px}
.tcq-msg::before{content:"";position:absolute;left:-20%;right:-20%;top:-70%;bottom:-70%;z-index:-1;pointer-events:none;
    background:radial-gradient(58% 46% at 50% 50%,rgba(var(--msg-rgb),.3),rgba(var(--msg-rgb),0) 72%);
    opacity:.85;animation:tcqBreath 3.4s ease-in-out infinite;--aura-op:.85}
.tcq-msg-in{text-align:center;font-weight:800;animation:tcqPop .45s ease-out 1}

@keyframes tcqBreath{0%,100%{opacity:calc(var(--aura-op) * .42)}50%{opacity:var(--aura-op)}}
@keyframes tcqSweep{0%{transform:translateX(0) rotate(8deg);opacity:0}12%{opacity:var(--sweep-op)}86%{opacity:var(--sweep-op)}100%{transform:translateX(340%) rotate(8deg);opacity:0}}
@keyframes tcqGlint{0%{transform:translateX(0);opacity:0}20%{opacity:.6}100%{transform:translateX(360%);opacity:0}}
@keyframes tcqFade{0%,100%{opacity:1}50%{opacity:.5}}
@keyframes tcqRain{0%{background-position:0% 50%}100%{background-position:300% 50%}}
@keyframes tcqPop{0%{opacity:0;transform:translateY(-4px)}100%{opacity:1;transform:translateY(0)}}

@media (prefers-reduced-motion:reduce){
    .tcq-card::before,.tcq-card::after,.tcq-btn::after,.tcq-arrow,.tcq-msg::before,.tcq-msg-in,
    .tcq-q10.tc-card .tcq-head,.tcq-q10.tc-card .tcq-max-in{animation:none}
    .tcq-card::after,.tcq-btn::after{opacity:0}
}
</style>

<?= $msgHtml ?>
<?= $upgradeHtml ?>
<div class="tc-actions">
    <a class="tc-btn tc-btn-ghost" href="?cmd=<?= $thcmd ?>">← Quay lại</a>
</div>
