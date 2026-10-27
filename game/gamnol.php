<?php
// Cẩm Nang Tổng Quan - Game Overview

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

// Element info
$lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
$elementColors = array('kim' => '#C0C0C0', 'moc' => '#228B22', 'thuy' => '#1E90FF', 'hoa' => '#FF4500', 'tho' => '#8B4513');
$elementNames = array('kim' => 'Kim', 'moc' => 'Mộc', 'thuy' => 'Thủy', 'hoa' => 'Hỏa', 'tho' => 'Thổ');
$lcColor = isset($elementColors[$lcElement]) ? $elementColors[$lcElement] : '#888';
$lcName = isset($elementNames[$lcElement]) ? $elementNames[$lcElement] : 'Chưa có';

$html = <<<HTML
<style>
.gn-page{width:100%;padding:10px}
.gn-hero{
    position:relative;display:grid;grid-template-columns:minmax(0,1fr) 92px;gap:16px;align-items:center;
    padding:18px;border:1px solid rgba(125,211,252,.34);border-radius:20px;overflow:hidden;margin-bottom:12px;
}
.gn-hero::before{
    content:"";position:absolute;inset:0;
    background:url('./images/tu-chan-banner-v2.png') center/cover no-repeat;opacity:.5;z-index:0;
}
.gn-hero::after{
    content:"";position:absolute;inset:0;
}
.gn-hero>*{position:relative;z-index:2}
.gn-kicker{color:#8ee7ff;font-size:13px;font-weight:1000;letter-spacing:1.5px;text-transform:uppercase;text-shadow:0 0 10px rgba(56,189,248,.58)}
.gn-title{
    margin:7px 0 8px;font-size:38px;line-height:1;font-weight:1000;letter-spacing:1.5px;
    background:linear-gradient(180deg,#fff 0%,#7bd7ff 38%,#c084fc 70%,#fff 100%);
    -webkit-background-clip:text;background-clip:text;color:transparent;
    text-shadow:0 0 18px rgba(114,198,255,.78),0 0 30px rgba(192,132,252,.48);
}
.gn-subtitle{max-width:520px;color:#dbeafe;font-size:14px;line-height:1.5;font-weight:800}
.gn-stats{display:flex;flex-wrap:wrap;gap:10px;margin-top:13px}
.gn-stats span{
    min-height:34px;display:inline-flex;align-items:center;gap:7px;padding:6px 12px;
    border:1px solid rgba(125,211,252,.30);border-radius:999px;
    background:rgba(7,16,45,.48);color:#cfe9ff;font-size:13px;font-weight:900;
}
.gn-stats b{color:#5df26b;font-size:18px;text-shadow:0 0 10px rgba(93,242,107,.45)}
.gn-seal{
    width:82px;height:82px;display:flex;align-items:center;justify-content:center;justify-self:end;
    border-radius:50%;border:1px solid rgba(250,204,21,.44);
    background:radial-gradient(circle,rgba(250,204,21,.22),rgba(147,51,234,.18) 68%,rgba(7,16,45,.54));
    color:#fde68a;font-size:38px;font-weight:1000;
    text-shadow:0 0 14px rgba(250,204,21,.78),0 0 24px rgba(236,72,153,.42);
    box-shadow:0 0 22px rgba(250,204,21,.18),0 0 14px rgba(255,255,255,.08) inset;
}

/* Section */
.gn-section{
    padding:14px;border:1px solid rgba(125,211,252,.26);border-radius:18px;
    background:rgba(9,22,58,.56);box-shadow:0 0 18px rgba(56,189,248,.08) inset;margin-bottom:12px;
}
.gn-section-title{
    color:#8ff1ff;font-size:13px;font-weight:1000;letter-spacing:2px;text-transform:uppercase;
    text-shadow:0 0 10px rgba(125,211,252,.55);margin-bottom:10px;
}
.gn-section-desc{color:#dbeafe;font-size:14px;line-height:1.6;font-weight:700}

/* Element grid */
.gn-elem-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:10px}
.gn-elem{
    text-align:center;padding:12px 8px;border-radius:14px;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.50);
}
.gn-elem-icon{font-size:28px;margin-bottom:4px}
.gn-elem-name{font-size:13px;font-weight:1000;color:#fff}
.gn-elem-desc{font-size:11px;color:#9fcaff;margin-top:4px}

/* Feature cards */
.gn-feature-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:10px}
.gn-feature{
    padding:14px;border-radius:14px;
    border:1px solid rgba(125,211,252,.24);background:rgba(7,16,45,.50);
}
.gn-feature-icon{font-size:28px;margin-bottom:6px}
.gn-feature-name{font-size:15px;font-weight:1000;color:#fff;margin-bottom:4px}
.gn-feature-desc{font-size:12px;color:#9fcaff;line-height:1.5}

/* Divider */
.gn-divider{height:22px;margin:12px 0;display:flex;align-items:center;justify-content:center;color:#36c5ff;text-shadow:0 0 10px #36c5ff}
.gn-divider:before,.gn-divider:after{content:"";height:1px;flex:1;background:linear-gradient(90deg,transparent,rgba(59,191,255,.62))}
.gn-divider:after{background:linear-gradient(90deg,rgba(59,191,255,.62),transparent)}
.gn-divider span{padding:0 13px}

/* Bottom */
.gn-bottom{display:flex;justify-content:space-between;gap:12px;margin-top:16px;padding-top:14px;border-top:1px solid rgba(125,211,252,.32)}
.gn-btn,.gn-btn:visited{
    min-width:118px;height:40px;display:inline-flex;align-items:center;justify-content:center;
    padding:0 16px;border-radius:10px;font-size:15px;font-weight:1000;text-decoration:none;
}
.gn-btn.ghost{border:1px solid rgba(125,211,252,.45);background:rgba(16,33,78,.72);color:#48c7ff;box-shadow:0 8px 16px rgba(0,0,0,.18),0 0 10px rgba(255,255,255,.05) inset}
.gn-btn.primary{border:1px solid rgba(187,247,208,.36);background:linear-gradient(180deg,#22c55e,#15803d);color:#fff;box-shadow:0 8px 18px rgba(0,0,0,.22),0 0 14px rgba(34,197,94,.18) inset}
.gn-btn:hover{color:#fff;filter:brightness(1.12);transform:translateY(-1px)}

@media(max-width:760px){
    .gn-hero{grid-template-columns:1fr;padding:16px}
    .gn-seal{display:none}
    .gn-title{font-size:30px}
    .gn-elem-grid{grid-template-columns:repeat(3,1fr)}
    .gn-feature-grid{grid-template-columns:1fr}
}
</style>

<div class="gn-page">

<div class="gn-hero">
    <div>
        <div class="gn-kicker">Cẩm Nang Tu Chân</div>
        <div class="gn-title">Tổng Quan</div>
        <div class="gn-subtitle">Giới thiệu tổng quát về các hệ thống tính năng trong game Tu Chân Giới.</div>
        <div class="gn-stats">
            <span>⚗ Linh căn: <b>{$lcName}</b></span>
            <span>📊 Cấp: <b>{$player->ulv}</b></span>
        </div>
    </div>
    <div class="gn-seal">📖</div>
</div>

<div class="gn-divider"><span>◇</span></div>

<!-- Linh Căn -->
<div class="gn-section">
    <div class="gn-section-title">🔮 Hệ Thống Linh Căn</div>
    <div class="gn-section-desc">
        Linh căn là yếu tố quyết định tiềm năng tu luyện của nhân vật. Mỗi linh căn thuộc một trong 5 hệ ngũ hành:
        <b style="color:#C0C0C0">Kim</b>, <b style="color:#228B22">Mộc</b>, <b style="color:#1E90FF">Thủy</b>, <b style="color:#FF4500">Hỏa</b>, <b style="color:#8B4513">Thổ</b>.
        Linh căn có 5 bậc từ Phàm đến Tiên, bậc càng cao chỉ số càng mạnh. Hệ ngũ hành tương tác:
        Kim khắc Mộc → Mộc khắc Thổ → Thổ khắc Thủy → Thủy khắc Hỏa → Hỏa khắc Kim.
    </div>
    <div class="gn-elem-grid">
        <div class="gn-elem"><div class="gn-elem-icon">⚔</div><div class="gn-elem-name" style="color:#C0C0C0">Kim</div><div class="gn-elem-desc">Tăng công kích, bạo kích</div></div>
        <div class="gn-elem"><div class="gn-elem-icon">🌿</div><div class="gn-elem-name" style="color:#228B22">Mộc</div><div class="gn-elem-desc">Tăng HP, hồi phục</div></div>
        <div class="gn-elem"><div class="gn-elem-icon">💧</div><div class="gn-elem-name" style="color:#1E90FF">Thủy</div><div class="gn-elem-desc">Cân bằng, hấp thu tốt</div></div>
        <div class="gn-elem"><div class="gn-elem-icon">🔥</div><div class="gn-elem-name" style="color:#FF4500">Hỏa</div><div class="gn-elem-desc">Bạo kích cao, phòng yếu</div></div>
        <div class="gn-elem"><div class="gn-elem-icon">🪨</div><div class="gn-elem-name" style="color:#8B4513">Thổ</div><div class="gn-elem-desc">Tăng phòng thủ, chắc chắn</div></div>
    </div>
</div>

<!-- Tính Năng -->
<div class="gn-section">
    <div class="gn-section-title">⚡ Các Hệ Thống Tính Năng</div>
    <div class="gn-feature-grid">
        <div class="gn-feature">
            <div class="gn-feature-icon">🔥</div>
            <div class="gn-feature-name">Luyện Đan</div>
            <div class="gn-feature-desc">Pha chế đan dược, thuốc bôi và thẻ cường hóa từ nguyên liệu quý hiếm.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">⚒</div>
            <div class="gn-feature-name">Chế Tạo</div>
            <div class="gn-feature-desc">Chế tạo trang bị và vật phẩm đặc biệt từ nguyên liệu và công thức.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">🐾</div>
            <div class="gn-feature-name">Thú Cưng</div>
            <div class="gn-feature-desc">Thu phục và nuôi dưỡng linh thú, tăng cường sức mạnh đồng hành.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">⚔</div>
            <div class="gn-feature-name">Võ Công</div>
            <div class="gn-feature-desc">Học tập và nâng cấp kỹ năng chiến đấu, mở bí kỹ mới.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">🏆</div>
            <div class="gn-feature-name">Xếp Hạng</div>
            <div class="gn-feature-desc">Thi đấu với người chơi khác, leo bảng xếp hạng nhận thưởng.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">🧘</div>
            <div class="gn-feature-name">Tu Luyện</div>
            <div class="gn-feature-desc">Tích lũy kinh nghiệm, nâng cấp cảnh giới tu luyện.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">⛩</div>
            <div class="gn-feature-name">Tông Môn</div>
            <div class="gn-feature-desc">gia nhập tông môn, nhận nhiệm vụ và giao dịch với NPC.</div>
        </div>
        <div class="gn-feature">
            <div class="gn-feature-icon">🗼</div>
            <div class="gn-feature-name">Tỏa Yêu Tháp</div>
            <div class="gn-feature-desc">Thử thách quái vật theo tầng, nhận thưởng quý hiếm.</div>
        </div>
    </div>
</div>

<!-- Phó Bản -->
<div class="gn-section">
    <div class="gn-section-title">🗺 Phó Bản & Khu Vực</div>
    <div class="gn-section-desc">
        Thế giới Tu Chân Giới được chia thành nhiều khu vực với các phó bản và thử thách khác nhau.
        di chuyển giữa các khu vực bằng bản đồ, chiến đấu với quái vật và boss để nhận kinh nghiệm và vật phẩm.
    </div>
</div>

<!-- Hướng Dẫn -->
<div class="gn-section">
    <div class="gn-section-title">📚 Hướng Dẫn Cơ Bản</div>
    <div class="gn-section-desc">
        <b>1.</b> Hoàn thành nhiệm vụ chính tuyến để mở khóa tính năng.<br>
        <b>2.</b> Chiến đấu với quái vật để nhận kinh nghiệm và nguyên liệu.<br>
        <b>3.</b> Luyện đan và chế tạo trang bị mạnh hơn.<br>
        <b>4.</b> Nâng cấp linh căn để tăng sức mạnh.<br>
        <b>5.</b> Tham gia phó bản và thử thách boss để nhận thưởng quý.
    </div>
</div>

<div class="gn-bottom">
    <a class="gn-btn ghost" href="#" onClick="javascript:history.back(-1);">Trở Về</a>
    <a class="gn-btn primary" href="game.php?cmd={$gonowmid}">Đại Sảnh</a>
</div>
</div>
HTML;

echo $html;
?>
