<?php
$toayeuthapcmd = $encode->encode("cmd=toayeuthap&sid=$sid");
$vandinhdaicmd = $encode->encode("cmd=vandinhdai&sid=$sid");
$hammocmd = $encode->encode("cmd=hammo&sid=$sid");
$sauthucmd = $encode->encode("cmd=sauthu&sid=$sid");

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$safeName = htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8');
$playerLevel = max(1, intval($player->ulv));

$dungeons = array(
    array(
        'name' => 'Tỏa Yêu Tháp',
        'desc' => 'Leo tháp vượt tầng, nhận phần thưởng quý giá. Thử thách giới hạn.',
        'cmd' => $toayeuthapcmd,
        'color' => '#00C000',
        'icon' => '🗼',
        'req' => 'Lv >= 10',
        'type' => 'PVE'
    ),
    array(
        'name' => 'Vấn Đỉnh Đài',
        'desc' => 'Đấu trường PVP, so tài cùng người chơi khác trên toàn server.',
        'cmd' => $vandinhdaicmd,
        'color' => '#ec0909',
        'icon' => '⚔️',
        'req' => 'Lv >= 15',
        'type' => 'PVP'
    ),
    array(
        'name' => 'Hầm Mỏ',
        'desc' => 'Đào khoáng sản, thu thập nguyên liệu hiếm. Tự động đào theo lượt.',
        'cmd' => $hammocmd,
        'color' => '#06b6d4',
        'icon' => '⛏️',
        'req' => 'Lv >= 5',
        'type' => 'PVE'
    ),
    array(
        'name' => 'Sào Thủ',
        'desc' => 'Săn yêu thú hoang dã, thu phục pet mới. Cần Bùa + Ma Thạch.',
        'cmd' => $sauthucmd,
        'color' => '#f16613',
        'icon' => '🐾',
        'req' => 'Lv >= 1',
        'type' => 'PVE'
    )
);

$listHtml = '';
foreach ($dungeons as $idx => $d) {
    $typeBg = $d['type'] === 'PVP' ? 'rgba(236,9,9,.15)' : 'rgba(0,192,0,.12)';
    $typeColor = $d['type'] === 'PVP' ? '#ff6b6b' : '#5ddf5d';
    $listHtml .= '<a class="pb-card" href="?cmd='.$d['cmd'].'">';
    $listHtml .= '<div class="pb-card-icon" style="background:linear-gradient(135deg,'.$d['color'].',transparent)">'.$d['icon'].'</div>';
    $listHtml .= '<div class="pb-card-main">';
    $listHtml .= '<div class="pb-card-head"><span class="pb-card-name">'.$d['name'].'</span><span class="pb-card-type" style="background:'.$typeBg.';color:'.$typeColor.'">'.$d['type'].'</span></div>';
    $listHtml .= '<div class="pb-card-desc">'.$d['desc'].'</div>';
    $listHtml .= '<div class="pb-card-req">Yêu cầu: '.$d['req'].'</div>';
    $listHtml .= '</div>';
    $listHtml .= '<div class="pb-card-arrow">›</div>';
    $listHtml .= '</a>';
}

$html = <<<HTML
<div class="pb-page">
    <div class="pb-hero" style="background:linear-gradient(rgba(0,0,0,.5),rgba(0,0,0,.5)),url('./images/bg/tonghopphoban.png') center/cover no-repeat!important;padding:32px 18px!important">
        <div class="pb-hero-badge">Khu vực phó bản</div>
        <h1 style="text-shadow:0 2px 12px rgba(0,0,0,.8)!important">Phó Bản</h1>
        <p style="text-shadow:0 1px 4px rgba(0,0,0,.6)!important">Chọn phó bản muốn tham gia. Mỗi phó bản có phần thưởng và thử thách riêng.</p>
        <div class="pb-hero-stats">
            <div class="pb-hero-stat">
                <span>Đẳng cấp</span>
                <b>$playerLevel</b>
            </div>
            <div class="pb-hero-stat">
                <span>Phó bản</span>
                <b>4</b>
            </div>
        </div>
    </div>

    <div class="pb-list">
        <div class="pb-list-head">
            <span class="pb-list-title">Danh sách phó bản</span>
        </div>
        $listHtml
    </div>

    <div class="pb-tips">
        <div class="pb-tip-title">Lưu ý</div>
        <div class="pb-tip-item">Phó bản PVE yêu cầu nguyên liệu, không giới hạn lượt chơi.</div>
        <div class="pb-tip-item">PVP thi đấu theo mùa, xếp hạng nhận thưởng.</div>
    </div>

    <a class="pb-home" href="?cmd=$gonowmid">← Đại Sảnh</a>
</div>

<style>
.pb-page{margin:0 auto;padding:12px;font-family:inherit;color:#e0d6c2}
.pb-hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border-radius:16px;padding:28px 20px 24px;margin-bottom:14px;border:1px solid rgba(255,255,255,.08);position:relative;overflow:hidden}
.pb-hero::before{content:'';position:absolute;top:-40%;right:-20%;width:200px;height:200px;background:radial-gradient(circle,rgba(241,102,19,.15) 0%,transparent 70%);pointer-events:none}
.pb-hero::after{content:'';position:absolute;bottom:-30%;left:-10%;width:160px;height:160px;background:radial-gradient(circle,rgba(0,192,0,.1) 0%,transparent 70%);pointer-events:none}
.pb-hero-badge{display:inline-block;margin-bottom: 10px;padding:7px 12px;background:rgba(241,102,19,.15);border:1px solid rgba(241,102,19,.3);border-radius:20px;color:#f16613;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;position:relative;z-index:2}
.pb-hero h1{margin:10px 0 6px;font-size:30px;font-weight:1000;color:#fff;position:relative;z-index:2;text-shadow:0 2px 12px rgba(0,0,0,.4)}
.pb-hero p{margin:0 0 16px;color:#a09880;font-size:13px;line-height:1.6;position:relative;z-index:2}
.pb-hero-stats{display:flex;gap:12px;position:relative;z-index:2}
.pb-hero-stat{flex:1;padding:10px 14px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:10px;text-align:center}
.pb-hero-stat span{display:block;color:#a09880;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.5px}
.pb-hero-stat b{display:block;margin-top:4px;color:#fff;font-size:18px;font-weight:1000}

.pb-list{margin-bottom:14px}
.pb-list-head{margin-bottom:10px;padding:0 4px}
.pb-list-title{color:#d8c7a6;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:1px}
.pb-list{display:flex;flex-direction:column;gap:10px}

.pb-card{display:grid!important;grid-template-columns:50px 1fr auto!important;gap:12px;align-items:start;padding:16px!important;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.02) 100%)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:14px!important;text-decoration:none;transition:all .25s ease;position:relative;overflow:hidden;margin:0!important}
.pb-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.03) 0%,transparent 50%);opacity:0;transition:opacity .25s}
.pb-card:hover{border-color:rgba(255,255,255,.18)!important;transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.pb-card:hover::before{opacity:1}
.pb-card-icon{width:50px!important;height:50px!important;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:26px;position:relative;z-index:1}
.pb-card-main{min-width:0;position:relative;z-index:1}
.pb-card-head{display:flex;align-items:center;gap:8px}
.pb-card-name{color:#fff;font-size:15px;font-weight:1000}
.pb-card-type{padding:2px 8px;border-radius:6px;font-size:10px;font-weight:900;letter-spacing:.5px}
.pb-card-desc{color:#a09880;font-size:12px;line-height:1.5;margin-top:4px}
.pb-card-req{color:#d8c7a6;font-size:11px;font-weight:700;margin-top:2px}
.pb-card-arrow{font-size:22px;color:#555;transition:all .25s;position:relative;z-index:1}
.pb-card:hover .pb-card-arrow{color:#fff;transform:translateX(2px)}

.pb-tips{padding:14px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px}
.pb-tip-title{color:#d8c7a6;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.pb-tip-item{color:#a09880;font-size:12px;line-height:1.6;padding-left:12px;position:relative}
.pb-tip-item::before{content:'·';position:absolute;left:0;color:#666;font-weight:900}
.pb-home{display:block;text-align:center;padding:14px;margin-top:14px;background:linear-gradient(135deg,rgba(255,255,255,.05) 0%,rgba(255,255,255,.02) 100%);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#d8c7a6;font-size:14px;font-weight:700;text-decoration:none;transition:all .2s}
.pb-home:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:#fff}
</style>
HTML;

echo $html;
?>