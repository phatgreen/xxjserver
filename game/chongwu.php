<?php
// Sủng vật - giao diện mới
// Giữ nguyên logic cũ, chỉ tối ưu lại phần hiển thị cho đẹp và dễ nhìn hơn.

$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

$cwhtml = '';
$cwnamehtml = '';
$qr = '';
$tscg = '';
$tssb = '';

$chouqucmd = $encode->encode("cmd=sauthu&sid=$sid");
$queren    = $encode->encode("cmd=sauthu&sid=$sid");
$cwcmd     = $encode->encode("cmd=chongwu&sid=$sid");

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$cwtab = $encode->encode("cmd=chongwu&cwid=$player->cw&canshu=cwinfo&sid=$sid");
$sz    = $encode->encode("cmd=taozhuang&sid=$sid");
$gm    = $encode->encode("cmd=czbgm&canshu2=gaiming2&sid=$sid");

/* =========================
   HÀM HỖ TRỢ HIỂN THỊ
========================= */
if (!function_exists('cw_e')) {
    function cw_e($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cw_quality_info')) {
    function cw_quality_info($pz)
    {
        $pz = intval($pz);
        $arr = array(
            0 => array('name' => 'Bình thường', 'short' => 'Thường',     'color' => '#00C000', 'star' => '★☆☆☆☆☆'),
            1 => array('name' => 'Ưu tú',       'short' => 'Ưu tú',      'color' => '#1a80da', 'star' => '★★☆☆☆☆'),
            2 => array('name' => 'Trác việt',   'short' => 'Trác việt',  'color' => '#a08f0a', 'star' => '★★★☆☆☆'),
            3 => array('name' => 'Phi phàm',    'short' => 'Phi phàm',   'color' => '#14b8b9', 'star' => '★★★★☆☆'),
            4 => array('name' => 'Hoàn mỹ',     'short' => 'Hoàn mỹ',    'color' => '#f16613', 'star' => '★★★★★☆'),
            5 => array('name' => 'Nghịch thiên','short' => 'Nghịch thiên','color' => '#ec0909', 'star' => '★★★★★★')
        );

        return isset($arr[$pz]) ? $arr[$pz] : $arr[0];
    }
}

if (!function_exists('cw_exp_bar')) {
    function cw_exp_bar($now, $max)
    {
        $now = intval($now);
        $max = max(1, intval($max));
        $percent = min(100, max(0, intval($now / $max * 100)));

        return '<div class="cw-progress"><div class="cw-progress-in" style="width:'.$percent.'%"></div></div>';
    }
}


/**
 * Chuẩn hóa HP sủng vật để không hiển thị số âm.
 * Lưu ý:
 * - Combat có thể trừ máu xuống dưới 0.
 * - Giao diện chỉ nên hiển thị tối thiểu là 0.
 * - Không tự ý ghi DB ở đây để tránh hồi máu ngoài ý muốn.
 */
if (!function_exists('cw_safe_hp')) {
    function cw_safe_hp($hp, $maxhp)
    {
        $hp = intval($hp);
        $maxhp = max(1, intval($maxhp));

        if ($hp < 0) $hp = 0;
        if ($hp > $maxhp) $hp = $maxhp;

        return $hp;
    }
}

if (!function_exists('cw_nav_html')) {
    function cw_nav_html($ztcmd, $cwtab, $sz, $gm)
    {
        return '
        <div class="cw-tabs">
            <a href="?cmd='.$ztcmd.'">Nhân vật</a>
            <a href="?cmd='.$cwtab.'" class="active">Sủng vật</a>
            <a href="?cmd='.$sz.'">Thần trang</a>
            <a href="?cmd='.$gm.'">Đổi tên</a>
        </div>
        <div class="cw-divider"></div>';
    }
}


if (!function_exists('cw_is_mobile')) {
    function cw_is_mobile()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        return (bool)preg_match('/android|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile/i', $ua);
    }
}

if (!function_exists('cw_page_url')) {
    function cw_page_url($encode, $sid, $page, $perPage)
    {
        $page = max(1, intval($page));
        $perPage = (intval($perPage) == 5) ? 5 : 10;
        return '?cmd='.$encode->encode("cmd=chongwu&cwpage=$page&cwper=$perPage&sid=$sid");
    }
}

if (!function_exists('cw_pagination_html')) {
    function cw_pagination_html($encode, $sid, $page, $totalPage, $total, $perPage)
    {
        $page = max(1, intval($page));
        $totalPage = max(1, intval($totalPage));
        $perPage = (intval($perPage) == 5) ? 5 : 10;

        $html  = '<div class="cw-pagination">';

        if ($totalPage <= 1) {
            $html .= '<span class="cw-page-current">Trang <b>1</b>/<b>1</b></span>';
            $html .= '</div>';
            $html .= '<div class="cw-page-info">Tổng <b>'.intval($total).'</b> sủng vật · Mỗi trang <b>'.intval($perPage).'</b> con</div>';
            return $html;
        }

        $prevPage = max(1, $page - 1);
        $nextPage = min($totalPage, $page + 1);

        $prevClass = ($page <= 1) ? ' disabled' : '';
        $nextClass = ($page >= $totalPage) ? ' disabled' : '';

        $prevHref = ($page <= 1) ? 'javascript:void(0);' : cw_page_url($encode, $sid, $prevPage, $perPage);
        $nextHref = ($page >= $totalPage) ? 'javascript:void(0);' : cw_page_url($encode, $sid, $nextPage, $perPage);

        $html .= '<a class="cw-page-btn prev'.$prevClass.'" href="'.$prevHref.'">‹ Trước</a>';
        $html .= '<span class="cw-page-current">Trang <b>'.$page.'</b>/<b>'.$totalPage.'</b></span>';
        $html .= '<a class="cw-page-btn next'.$nextClass.'" href="'.$nextHref.'">Sau ›</a>';
        $html .= '</div>';
        $html .= '<div class="cw-page-info">Tổng <b>'.intval($total).'</b> sủng vật · Mỗi trang <b>'.intval($perPage).'</b> con</div>';

        return $html;
    }
}

/* =========================
   POPUP THÔNG BÁO
========================= */
$tskcg = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<font id="success"></font>
<script type="text/javascript">
setTimeout(function() {
    if(document.all) {
        document.getElementById("success").click();
    } else {
        var e = document.createEvent("MouseEvents");
        e.initEvent("click", true, true);
        document.getElementById("success").dispatchEvent(e);
    }
}, 300);
$('#success').click(function(){
   popup({type:'success',msg:"Thành công",delay:1800});
});
</script>
HTML;

$tsksb = <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<font id="error"></font>
<script type="text/javascript">
setTimeout(function() {
    if(document.all) {
        document.getElementById("error").click();
    } else {
        var e = document.createEvent("MouseEvents");
        e.initEvent("click", true, true);
        document.getElementById("error").dispatchEvent(e);
    }
}, 300);
$('#error').click(function(){
   popup({type:'error',msg:"Ma thạch không đủ!",delay:1800,bg:true,clickDomCancel:true});
});
</script>
HTML;

$qr1 = '';

/* =========================
   XỬ LÝ THAO TÁC
========================= */
if (isset($canshu)) {
    switch ($canshu) {
        case 'chuzhan':
            \player\changeplayersx('cw', $cwid, $sid, $dblj);
            $player = \player\getplayer($sid, $dblj);
            break;

        case 'shouhui':
            \player\changeplayersx('cw', 0, $sid, $dblj);
            $player = \player\getplayer($sid, $dblj);
            break;

        case 'fangsheng':
            \player\delechongwu($cwid, $sid, $dblj);
            break;

        case 'cwinfo':
            $chongwu = \player\getchongwu($cwid, $dblj);
            $q = cw_quality_info($chongwu->cwpz);
            $qClass = 'cw-q-'.intval($chongwu->cwpz);

            $bonusPercent = intval($chongwu->cwpz) * 10;

            // HP có thể bị âm sau chiến đấu, nên chuẩn hóa trước khi hiển thị.
            $hpMax = max(1, intval($chongwu->cwmaxhp));
            $hpNow = cw_safe_hp($chongwu->cwhp, $hpMax);
            $hpPercent = min(100, max(0, intval($hpNow / $hpMax * 100)));
            $hpNowStatus = ($hpNow <= 0) ? '<font color="#cc0000">Trọng thương</font>' : '<font color="#008000">Khỏe mạnh</font>';

            $nav = cw_nav_html($ztcmd, $cwtab, $sz, $gm);
            $expBar = cw_exp_bar($chongwu->cwexp, $chongwu->cwmaxexp);

            $petAvatarStyle = 'background:linear-gradient(135deg,' . $q['color'] . '88,' . $q['color'] . '44)';
            $petAvatarBorder = 'border-color:' . $q['color'];
            $hpColor = $hpNow <= 0 ? '#ef4444' : '#4ade80';
            $hpIcon = $hpNow <= 0 ? '💀' : '❤️';

            $cwhtml = <<<HTML
<div class="cw-wrap">
    $nav

    <div class="cw-detail-card">
        <div class="cw-detail-head" style="border-bottom-color:{$q['color']}44">
            <div class="cw-head-left">
                <div class="cw-pet-avatar {$qClass}" style="{$petAvatarStyle};{$petAvatarBorder}">
                    <span>🐾</span>
                </div>
            </div>
            <div class="cw-head-right">
                <div class="cw-head-title-row">
                    <div class="cw-name" style="color:{$q['color']};text-shadow:0 0 18px {$q['color']}88">{$chongwu->cwname}</div>
                    <span class="cw-quality-badge" style="background:{$q['color']}22;border-color:{$q['color']};color:{$q['color']}">{$q['name']}</span>
                </div>
                <div class="cw-head-stars {$qClass}">{$q['star']}</div>
                <div class="cw-head-meta">
                    <span class="cw-head-level">Cấp <b>{$chongwu->cwlv}</b></span>
                    <span class="cw-head-hp" style="color:{$hpColor}">{$hpIcon} {$hpNow}/{$hpMax}</span>
                </div>
            </div>
        </div>

        <div class="cw-section-title"><span>▶</span> Thông tin cơ bản</div>
        <div class="cw-info-grid">
            <div class="cw-info-item">
                <span class="cw-info-label">Cấp độ</span>
                <span class="cw-info-value">{$chongwu->cwlv}</span>
            </div>
            <div class="cw-info-item">
                <span class="cw-info-label">Kinh nghiệm</span>
                <span class="cw-info-value">{$chongwu->cwexp}/{$chongwu->cwmaxexp}</span>
            </div>
        </div>
        $expBar
        <div class="cw-info-grid">
            <div class="cw-info-item">
                <span class="cw-info-label">HP hiện tại</span>
                <span class="cw-info-value" style="color:{$hpColor}">{$hpNow}/{$hpMax} ({$hpPercent}%)</span>
            </div>
            <div class="cw-info-item">
                <span class="cw-info-label">Trạng thái</span>
                <span class="cw-info-value">{$hpNowStatus}</span>
            </div>
        </div>

        <div class="cw-divider-line"></div>

        <div class="cw-section-title"><span>▶</span> Thuộc tính chiến đấu</div>
        <div class="cw-stat-grid">
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>HP</span>
                <b style="color:{$q['color']}">{$chongwu->cwmaxhp}</b>
            </div>
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>Công</span>
                <b style="color:{$q['color']}">{$chongwu->cwgj}</b>
            </div>
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>Phòng</span>
                <b style="color:{$q['color']}">{$chongwu->cwfy}</b>
            </div>
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>Bạo kích</span>
                <b style="color:{$q['color']}">{$chongwu->cwbj}</b>
            </div>
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>Né / Hút máu</span>
                <b style="color:{$q['color']}">{$chongwu->cwxx}</b>
            </div>
            <div class="cw-stat" style="border-color:{$q['color']}44;background:{$q['color']}11">
                <span>Tăng trưởng</span>
                <b style="color:{$q['color']}">+{$bonusPercent}%</b>
            </div>
        </div>

        <div class="cw-divider-line"></div>

        <div class="cw-section-title"><span>▶</span> Tăng khi lên cấp</div>
        <div class="cw-up-grid">
            <div class="cw-up-item">
                <span class="cw-up-icon">❤️</span>
                <span class="cw-up-label">HP</span>
                <span class="cw-up-value">+{$chongwu->uphp}</span>
            </div>
            <div class="cw-up-item">
                <span class="cw-up-icon">⚔️</span>
                <span class="cw-up-label">Công</span>
                <span class="cw-up-value">+{$chongwu->upgj}</span>
            </div>
            <div class="cw-up-item">
                <span class="cw-up-icon">🛡️</span>
                <span class="cw-up-label">Phòng</span>
                <span class="cw-up-value">+{$chongwu->upfy}</span>
            </div>
        </div>
        <div class="cw-note">
            Phẩm chất <b class="{$qClass}" style="color:{$q['color']}">[{$q['name']}]</b> khi lên cấp tăng thêm <b style="color:{$q['color']}">{$bonusPercent}%</b>.
        </div>
    </div>

    <div class="cw-actions">
        <a href="#" onClick="javascript:history.back(-1);">← Trở Về</a>
        <a href="game.php?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;
            echo $cwhtml;
            exit();
            break;
    }
}

/* =========================
   DANH SÁCH SỦNG VẬT
========================= */
$allcw = \player\getchongwuall($sid, $dblj);

/*
 * Đưa sủng vật đang xuất chiến lên đầu danh sách trước khi phân trang.
 * Cách làm này giữ nguyên thứ tự các sủng vật còn lại, chỉ kéo pet đang xuất chiến lên vị trí đầu tiên.
 */
if (!empty($allcw) && intval($player->cw) > 0) {
    $cwDangXuatChien = array();
    $cwConLai = array();

    foreach ($allcw as $cwItem) {
        $itemCwid = 0;

        if (is_array($cwItem) && isset($cwItem['cwid'])) {
            $itemCwid = intval($cwItem['cwid']);
        } elseif (is_object($cwItem) && isset($cwItem->cwid)) {
            $itemCwid = intval($cwItem->cwid);
        }

        if ($itemCwid == intval($player->cw)) {
            $cwDangXuatChien[] = $cwItem;
        } else {
            $cwConLai[] = $cwItem;
        }
    }

    if (!empty($cwDangXuatChien)) {
        $allcw = array_merge($cwDangXuatChien, $cwConLai);
    }
}

$isMobile = cw_is_mobile();

// Ưu tiên cwper do link/JS truyền vào để xử lý đúng theo kích thước màn hình thực tế.
// Mobile: 5 con/trang. Máy tính: 5 hàng x 2 cột = 10 con/trang.
$cwperReq = isset($cwper) ? intval($cwper) : (isset($_GET['cwper']) ? intval($_GET['cwper']) : 0);
if ($cwperReq == 5) {
    $perPage = 5;
} elseif ($cwperReq == 10) {
    $perPage = 10;
} else {
    $perPage = $isMobile ? 5 : 10;
}

$page     = isset($cwpage) ? intval($cwpage) : (isset($_GET['cwpage']) ? intval($_GET['cwpage']) : 1);
$page     = max(1, $page);

$totalPet  = $allcw ? count($allcw) : 0;
$totalPage = max(1, intval(ceil($totalPet / $perPage)));

if ($page > $totalPage) {
    $page = $totalPage;
}

$startIndex = ($page - 1) * $perPage;
$pagePets   = $allcw ? array_slice($allcw, $startIndex, $perPage) : array();
$pagehtml   = cw_pagination_html($encode, $sid, $page, $totalPage, $totalPet, $perPage);

// JS đổi chế độ phân trang theo viewport thật. Tránh trường hợp mobile vẫn bị server nhận là desktop.
$mobilePage = min(max(1, intval(ceil($startIndex / 5) + 1)), max(1, intval(ceil($totalPet / 5))));
$desktopPage = min(max(1, intval(ceil($startIndex / 10) + 1)), max(1, intval(ceil($totalPet / 10))));
$mobileViewUrl  = '?cmd='.$encode->encode("cmd=chongwu&cwpage=$mobilePage&cwper=5&sid=$sid");
$desktopViewUrl = '?cmd='.$encode->encode("cmd=chongwu&cwpage=$desktopPage&cwper=10&sid=$sid");

if ($pagePets) {
    foreach ($pagePets as $cw) {
        $cwid = intval($cw['cwid']);
        $chongwu = \player\getchongwu($cwid, $dblj);
        $q = cw_quality_info($chongwu->cwpz);
        $qClass = 'cw-q-'.intval($chongwu->cwpz);

        $cwinfo = $encode->encode("cmd=chongwu&cwid=$cwid&canshu=cwinfo&sid=$sid");

        if ($cwid != $player->cw) {
            $cz = $encode->encode("cmd=chongwu&canshu=chuzhan&cwid=$cwid&sid=$sid");
            $fs = $encode->encode("cmd=chongwu&canshu=fangsheng&cwid=$cwid&sid=$sid");

            $actionHtml = '<a class="cw-mini-btn green" href="?cmd='.$cz.'">Xuất chiến</a>'
                        . '<a class="cw-mini-btn red" href="?cmd='.$fs.'" onclick="return confirm(\'Phóng sinh sủng vật này?\')">Phóng sinh</a>';
        } else {
            $sh = $encode->encode("cmd=chongwu&canshu=shouhui&cwid=$cwid&sid=$sid");
            $actionHtml = '<span class="cw-fighting">Đã xuất chiến</span>'
                        . '<a class="cw-mini-btn" href="?cmd='.$sh.'">Thu hồi</a>';
        }

        $expBar = cw_exp_bar($chongwu->cwexp, $chongwu->cwmaxexp);

        $cwnamehtml .= '<div class="cw-pet-card">';
        $cwnamehtml .= '<a class="cw-pet-main" href="?cmd='.$cwinfo.'">';
        $cwnamehtml .= '<div class="cw-avatar '.$qClass.'">Thú</div>';
        $cwnamehtml .= '<div class="cw-pet-info">';
        $cwnamehtml .= '<div class="cw-pet-name '.$qClass.'">'.cw_e($cw['cwname']).'</div>';
        $cwnamehtml .= '<div class="cw-pet-meta"><span>'.$q['short'].'</span><span>Cấp '.$chongwu->cwlv.'</span><span>Công '.$chongwu->cwgj.'</span></div>';
        $cwnamehtml .= '<div class="cw-pet-star">'.$q['star'].'</div>';
        $cwnamehtml .= $expBar;
        $cwnamehtml .= '</div>';
        $cwnamehtml .= '</a>';
        $cwnamehtml .= '<div class="cw-pet-actions">'.$actionHtml.'</div>';
        $cwnamehtml .= '</div>';
    }
} else {
    $cwnamehtml = '<div class="cw-empty">Hiện tại không có sủng vật.</div>';
}

$nav = cw_nav_html($ztcmd, $cwtab, $sz, $gm);

$cwhtml = <<<HTML
<div class="cw-wrap">
    $nav
    <div class="cw-banner">
        <div class="cw-banner-icon">🐾</div>
    </div>
    <div class="cw-section-title">Danh sách sủng vật</div>
    <div class="cw-pet-grid">
        $cwnamehtml
    </div>
    $pagehtml
    $tssb
    $tscg
    $qr
    <div class="cw-actions">
        <a href="#" onClick="javascript:history.back(-1);">Trở Về</a>
        <a href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0" />
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script type="text/javascript" src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
(function(){
    var desired = (window.innerWidth && window.innerWidth <= 760) ? 5 : 10;
    var current = {$perPage};
    if (desired !== current) {
        window.location.href = (desired === 5) ? '{$mobileViewUrl}' : '{$desktopViewUrl}';
        return;
    }
})();

$('#load').click(function(){
    popup({type:'load',msg:"Vui lòng chờ",delay:1000});
});
</script>
HTML;

echo $cwhtml;
?>