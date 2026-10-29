<?php

if (!isset($cmd)) $cmd = '';
if (!isset($canshu)) $canshu = '';
if (!isset($wgid)) $wgid = 0;

require_once __DIR__ . '/wugong_utils.php';

$player = \player\getplayer($sid, $dblj);

$gonowmid  = $encode->encode("cmd=gomid&newmid=".$player->nowmid."&sid=".$sid);
$cwhtml = '';
$cwnamehtml = '';
$tscg = '';
$tssb = '';
$tiss = '';

if ($canshu != '') {

    switch ($canshu) {

        case 'xuexi':

            // Chỉ cho luyện võ công đã sở hữu (trước đây wgid từ URL không kiểm tra — BUG-09)
            if ($wgid > 0 && \player\wgcx($wgid, $sid, $dblj, true)) {
                \player\changeplayersx('wugong', $wgid, $sid, $dblj);
                $player = \player\getplayer($sid, $dblj);
            }
            break;

        case 'biguan':

            \player\changeplayersx('wugong', 0, $sid, $dblj);
            $player = \player\getplayer($sid, $dblj);
            break;

        case 'fangsheng':

            if ($wgid > 0) {
                \player\delewugong($wgid, $sid, $dblj);

                if ($player->wugong == $wgid) {
                    \player\changeplayersx('wugong', 0, $sid, $dblj);
                    $player = \player\getplayer($sid, $dblj);
                }
            }
            break;

        case 'cwinfo':

            $cx = \player\wgcx($wgid, $sid, $dblj);

            if (!$cx) {
                echo '<div class="tcg-wg-page"><div class="tcg-wg-alert error">Không tìm thấy võ công.</div></div>';
                exit;
            }

            $wgxx       = $encode->encode("cmd=xxwg&sid=".$sid);
            $xiuliancmd = $encode->encode("cmd=goxiulian&sid=".$sid);
            $wgxl       = $encode->encode("cmd=wgxl&sid=".$sid);

            $wgName      = wg_safe_text($cx->wgname);
            $wgWeapon    = '';
            $wgFeature   = '';
            $wgEffect    = '';
            $wgStyle     = '';
            $wgIntro     = '';
            $wgQuality   = wg_color_name($cx->wgys);
            $wgColor     = wg_safe_text($cx->wgys);
            $wgLevel     = wg_safe_text($cx->wgdj);
            $wgExp       = wg_safe_text($cx->wgxl);
            $wgExpMax    = wg_safe_text($cx->wgxlmax);
            $wgPercent   = ($cx->wgxlmax > 0) ? min(100, round(($cx->wgxl / $cx->wgxlmax) * 100, 2)) : 0;

            $exCxwg = explode("|", $cx->wginfo);
            $wgWeapon  = isset($exCxwg[0]) ? wg_safe_text($exCxwg[0]) : '';
            $wgFeature = isset($exCxwg[1]) ? wg_safe_text($exCxwg[1]) : '';
            $wgEffect  = isset($exCxwg[2]) ? wg_safe_text($exCxwg[2]) : '';
            $wgStyle   = isset($exCxwg[3]) ? wg_safe_text($exCxwg[3]) : '';
            $wgIntro   = isset($exCxwg[4]) ? wg_safe_text($exCxwg[4]) : '';

            $cwhtml = <<<HTML
<div class="tcg-wg-page tcg-wg-detail">
    <div class="tcg-wg-hero">
        <div class="tcg-wg-imgbox">
            <img src="./images/wugong/$wgid.png" alt="$wgName">
        </div>
        <div class="tcg-wg-hero-info">
            <div class="tcg-wg-kicker">Võ công đang xem</div>
            <h2>$wgName</h2>
            <div class="tcg-wg-quality" style="--wg-color:$wgColor">
                <span class="tcg-wg-quality-dot"></span>
                Phẩm chất: <b>$wgQuality</b>
            </div>
        </div>
    </div>

    <div class="tcg-wg-tabs">
        <a href="?cmd=$xiuliancmd">Ngồi thiền</a>
        <a href="?cmd=$wgxl">Tu hành</a>
        <a class="active" href="?cmd=$wgxx">Bí tịch</a>
    </div>

    <div class="tcg-wg-info-grid">
        <div class="tcg-wg-info-card">
            <span>Vũ khí</span>
            <b>$wgWeapon</b>
        </div>
        <div class="tcg-wg-info-card">
            <span>Đẳng cấp</span>
            <b>$wgLevel</b>
        </div>
        <div class="tcg-wg-info-card">
            <span>Phong cách</span>
            <b>$wgStyle</b>
        </div>
        <div class="tcg-wg-info-card">
            <span>Hiệu quả</span>
            <b>$wgEffect</b>
        </div>
    </div>

    <div class="tcg-wg-section">
        <div class="tcg-wg-section-title">Đặc điểm võ công</div>
        <div class="tcg-wg-desc">$wgFeature</div>
    </div>

    <div class="tcg-wg-section">
        <div class="tcg-wg-section-title">Kinh nghiệm tu hành</div>
        <div class="tcg-wg-exp-head">
            <span>$wgExp / $wgExpMax</span>
            <b>$wgPercent%</b>
        </div>
        <div class="tcg-wg-expbar">
            <i style="width:$wgPercent%;"></i>
        </div>
    </div>

    <div class="tcg-wg-section">
        <div class="tcg-wg-section-title">Giới thiệu</div>
        <div class="tcg-wg-desc">$wgIntro</div>
    </div>

    <div class="tcg-wg-bottom">
        <a class="tcg-wg-navbtn" href="#" onClick="javascript:history.back(-1);">Trở về</a>
        <a class="tcg-wg-navbtn primary" href="game.php?cmd=$gonowmid">Đại sảnh</a>
    </div>
</div>
HTML;

            echo $cwhtml;
            return; // dừng file nhưng để game.php vẽ tiếp thanh dưới
            break;
    }
}

$allcw = \player\wgsl($sid, $dblj);

if ($allcw) {

    foreach ($allcw as $cw) {

        $thisWgid   = isset($cw['wgid']) ? $cw['wgid'] : 0;
        $thisWgName = isset($cw['wgname']) ? $cw['wgname'] : '';
        $thisWgYs   = isset($cw['wgys']) ? $cw['wgys'] : '#ffffff';
        $thisWgSum  = isset($cw['wgsum']) ? $cw['wgsum'] : 0;

        $wginfo = $encode->encode("cmd=xxwg&canshu=cwinfo&wgid=".$thisWgid."&sid=".$sid);

        $safeName = wg_safe_text($thisWgName);
        $safeColor = wg_safe_text($thisWgYs);
        $safeSum = intval($thisWgSum);
        $qualityName = wg_color_name($thisWgYs);

        $isActive = ($thisWgid == $player->wugong);
        $rowClass = $isActive ? ' tcg-wg-row-active' : '';
        $activeBadge = $isActive ? '<span class="tcg-wg-meta-active">Đang học</span>' : '';

        $cwnamehtml .= '<div class="tcg-wg-card'.$rowClass.'">';
        $cwnamehtml .= '<div class="tcg-wg-card-main">';
        $cwnamehtml .= '<a class="tcg-wg-card-scroll" href="?cmd='.$wginfo.'">📜</a>';
        $cwnamehtml .= '<div class="tcg-wg-card-info">';
        $cwnamehtml .= '<a class="tcg-wg-card-name" style="color:'.$safeColor.'" href="?cmd='.$wginfo.'">'.$safeName.'</a>';
        $cwLevel = isset($cw['wgdj']) ? intval($cw['wgdj']) : 0;
        $cwExp = isset($cw['wgxl']) ? intval($cw['wgxl']) : 0;
        $cwExpMax = isset($cw['wgxlmax']) ? intval($cw['wgxlmax']) : 100;
        if ($cwExpMax <= 0) {
            $cwExpMax = 100;
        }
        $cwPct = intval(round($cwExpMax > 0 ? min(100, max(0, $cwExp / $cwExpMax * 100)) : 0));

        $cwnamehtml .= '<div class="cp-meta">';
        $cwnamehtml .= '<span>Lv '.$cwLevel.'</span>';
        $cwnamehtml .= '<span class="cp-dot">•</span>';
        $cwnamehtml .= '<span style="color:'.$safeColor.'">'.$qualityName.'</span>';
        $cwnamehtml .= $activeBadge;
        $cwnamehtml .= '</div>';
        $cwnamehtml .= '<div class="cp-exp-bar">';
        $cwnamehtml .= '<div class="cp-exp-fill" style="width:'.$cwPct.'%"></div>';
        $cwnamehtml .= '<span class="cp-exp-text">'.number_format($cwExp).' / '.number_format($cwExpMax).'</span>';
        $cwnamehtml .= '</div>';
        $cwnamehtml .= '</div>';
        $cwnamehtml .= '</div>';

        $cwnamehtml .= '<div class="tcg-wg-card-actions">';

        if (!$isActive) {

            $xuexicmd = $encode->encode("cmd=xxwg&canshu=xuexi&wgid=".$thisWgid."&sid=".$sid);
            $fscmd    = $encode->encode("cmd=xxwg&canshu=fangsheng&wgid=".$thisWgid."&sid=".$sid);

            $cwnamehtml .= '<a class="tcg-wg-mini-btn use" href="?cmd='.$xuexicmd.'">Thi Triển</a>';
            $cwnamehtml .= '<a class="tcg-wg-mini-btn remove" href="?cmd='.$fscmd.'" onclick="return false;" data-wg-confirm=\'{"msg":"Bạn có chắc muốn vứt bỏ bí tịch này?<br>Hành động không thể hoàn tác.","title":"Vứt bỏ bí tịch","icon":"🗑","confirmText":"Vứt bỏ","type":"danger"}\'>Vứt</a>';

        } else {

            $shcmd = $encode->encode("cmd=xxwg&canshu=biguan&wgid=".$thisWgid."&sid=".$sid);

            $cwnamehtml .= '<a class="tcg-wg-mini-btn stop" href="?cmd='.$shcmd.'" onclick="return false;" data-wg-confirm=\'{"msg":"Bế quan sẽ ngừng tu luyện công pháp này.","title":"Bế quan","icon":"🔒","confirmText":"Bế quan","type":"warning"}\'>Bế quan</a>';
        }

        $cwnamehtml .= '</div>';
        $cwnamehtml .= '</div>';
    }

} else {

    $cwnamehtml = '<div class="tcg-wg-empty">Bạn hiện không có bí tịch võ công. Phiên bản hiện tại bí tịch võ công đang được thử nghiệm nội bộ. Nếu cần trải nghiệm hãy liên hệ GM.</div>';
}

$wgid = $player->wugong;

if ($wgid == '') {
    $wgid = 0;
}

$xiuliancmd = $encode->encode("cmd=goxiulian&sid=".$sid);
$wgxl       = $encode->encode("cmd=wgxl&sid=".$sid);
$wgxx       = $encode->encode("cmd=xxwg&sid=".$sid);

$activeName = 'Chưa học võ công';
$activeWg = false;
if ($wgid > 0) {
    $activeWg = \player\wgcx($wgid, $sid, $dblj);
    if ($activeWg) {
        $activeName = $activeWg->wgname;
    }
}
$activeName = wg_safe_text($activeName);

// Khung trung tâm: võ công đang học (giống trang Võ Công / Nhân Vật, dùng lại .cws-*)
$bookKinds = $allcw ? count($allcw) : 0;
if ($activeWg) {
    $aYs = wg_safe_text($activeWg->wgys);
    $aMax = max(1, intval($activeWg->wgxlmax));
    $aPct = min(100, intval(round(intval($activeWg->wgxl) * 100 / $aMax)));
    $aInfo = explode('|', $activeWg->wginfo);
    $aType = isset($aInfo[0]) ? wg_safe_text($aInfo[0]) : '';
    $stageHtml = '<div class="cws-stage wgs-stage" style="--cw-q:' . $aYs . '">'
        . '<div class="cws-tag">📖 Đang học</div>'
        . '<div class="cws-side cws-side-left">'
        . '<div class="cws-stat"><span>📈 Cấp võ công</span><b>' . intval($activeWg->wgdj) . '</b></div>'
        . '<div class="cws-stat"><span>📖 Bí tịch</span><b>' . intval($activeWg->wgsum) . '</b></div>'
        . '<div class="cws-stat"><span>📚 Võ công sở hữu</span><b>' . $bookKinds . '</b></div>'
        . '</div>'
        . '<div class="cws-figure wgs-figure"><img src="./images/wugong/' . intval($wgid) . '.png" alt="Võ công"></div>'
        . '<div class="cws-side cws-side-right">'
        . '<div class="cws-stat"><span>✨ Phẩm chất</span><b style="color:' . $aYs . '">' . wg_color_name($activeWg->wgys) . '</b></div>'
        . '<div class="cws-stat"><span>⚡ Kinh nghiệm</span><b>' . $aPct . '%</b></div>'
        . '<div class="cws-stat"><span>⚔️ Loại vũ khí</span><b class="wgs-small">' . $aType . '</b></div>'
        . '</div>'
        . '<div class="cws-id"><div class="cws-name" style="color:' . $aYs . ';text-shadow:0 0 16px ' . $aYs . '88">' . $activeName . '</div>'
        . '<div class="wgs-actions"><a class="tcg-wg-btn primary" href="?cmd=' . $wgxl . '">🥋 Tu luyện võ công</a></div></div>'
        . '</div>';
} else {
    $stageHtml = '<div class="cws-stage cws-empty wgs-stage"><div class="cws-tag">📖 Bí tịch</div>'
        . '<div class="cws-figure wgs-figure"><img src="./images/wugong/0.png" alt="Võ công"></div>'
        . '<div class="cws-id"><div class="cws-name">Chưa học võ công — chọn "Thi Triển" ở danh sách bên dưới</div></div></div>';
}

$cwhtml = <<<HTML
<div class="tcg-wg-page wg-compact bt-compact">
    <div class="tcg-wg-tabs">
        <a class="tcg-wg-tab" href="?cmd=$xiuliancmd">Ngồi thiền</a>
        <a class="tcg-wg-tab" href="?cmd=$wgxl">Tu luyện võ công</a>
        <a class="tcg-wg-tab active" href="?cmd=$wgxx">Bí tịch</a>
    </div>

    $stageHtml

    <div class="bt-title">Kho bí tịch ($bookKinds)</div>
    <div class="tcg-wg-list">
        $cwnamehtml
    </div>

    $tssb
    $tscg
    $tiss

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0" />
</div>

<style>
.tcg-wg-card {
    display: grid !important;
    grid-template-columns: minmax(0,1fr) auto !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 10px 12px !important;
    border: 1px solid rgba(93,151,255,.18) !important;
    border-radius: 14px !important;
    background: radial-gradient(circle at 0% 0%,rgba(56,189,248,.08),transparent 38%),
                linear-gradient(180deg,rgba(18,36,82,.70),rgba(11,24,62,.76)) !important;
    box-shadow: 0 0 14px rgba(0,0,0,.12) inset !important;
    transition: border-color .2s, background .2s !important;
}
.tcg-wg-card:hover {
    border-color: rgba(125,211,252,.42) !important;
    background: linear-gradient(180deg,rgba(26,52,112,.78),rgba(13,30,78,.80)) !important;
}
.tcg-wg-card-active {
    border-color: rgba(34,211,238,.58) !important;
    box-shadow: 0 0 16px rgba(34,211,238,.16),0 0 18px rgba(34,211,238,.08) inset !important;
}
.tcg-wg-card-main {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    min-width: 0 !important;
}
.tcg-wg-card-scroll {
    flex: 0 0 auto !important;
    width: 40px !important;
    height: 40px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 10px !important;
    border: 1px solid rgba(125,211,252,.30) !important;
    background: rgba(255,255,255,.06) !important;
    font-size: 25px !important;
    text-decoration: none !important;
}
.tcg-wg-card-info {
    min-width: 0 !important;
	margin-left: 10px;
}
.tcg-wg-card-name {
    font-weight: 1000 !important;
    text-decoration: none !important;
    font-size: 15px !important;
    display: block !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    text-shadow: 0 0 8px rgba(255,255,255,.15), 0 0 16px currentColor !important;
    margin: -2px -8px !important;
    padding: 2px 8px !important;
    border-radius: 8px !important;
    border: none !important;
    background: none !important;
    transition: all .25s ease !important;
    letter-spacing: .3px !important;
}
.tcg-wg-card-name:hover {
    text-shadow: 0 0 10px rgba(255,255,255,.25), 0 0 22px currentColor, 0 0 36px currentColor !important;
    background: rgba(255,255,255,.06) !important;
}
.tcg-wg-card-meta {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    color: #9fcaff !important;
    margin-top: 3px !important;
	margin-left: 5px;
    flex-wrap: wrap !important;
}
.tcg-wg-card-dot {
    opacity: .5 !important;
}
.tcg-wg-meta-active {
    color: #2dd4bf !important;
    font-weight: 1000 !important;
    text-shadow: 0 0 8px rgba(45,212,191,.45) !important;
}
.tcg-wg-card-actions {
    display: flex !important;
    gap: 6px !important;
    flex-shrink: 0 !important;
}
.cp-exp-bar {
    position: relative !important;
    height: 6px !important;
    border-radius: 3px !important;
    background: rgba(255,255,255,.08) !important;
    margin-top: 4px !important;
    overflow: hidden !important;
}
.cp-exp-fill {
    height: 100% !important;
    border-radius: 3px !important;
    background: linear-gradient(90deg,#ff9800,#ffc107) !important;
    transition: width .3s !important;
}
.cp-exp-text {
    position: absolute !important;
    right: 0 !important;
    top: -7px !important;
    font-size: 7px !important;
    color: #9fcaff !important;
}
</style>
<script>
document.querySelectorAll('[data-wg-confirm]').forEach(function(el){
    el.addEventListener('click', function(e){
        e.preventDefault();
        try {
            var opts = JSON.parse(this.getAttribute('data-wg-confirm'));
        } catch(ex) { return; }
        var url = this.getAttribute('href');
        var self = this;
        opts.onYes = function(){ window.location.href = url; };
        wgConfirm(opts);
    });
});
</script>
HTML;

echo wg_confirm_modal() . $cwhtml;
?>
