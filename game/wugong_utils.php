<?php
/**
 * Hàm dùng chung cho hệ thống võ công / bí tịch / công pháp
 */

if (!function_exists('wg_safe_text')) {
    function wg_safe_text($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wg_color_name')) {
    function wg_color_name($color)
    {
        $map = array(
            '#647b5d' => 'Phàm',
            '#288dc7' => 'Lam',
            '#39b512' => 'Lục',
            '#10bf61' => 'Linh',
            '#f9c508' => 'Hoàng Kim',
            '#ff3100' => 'Đỏ',
        );
        return isset($map[$color]) ? $map[$color] : 'Không rõ';
    }
}

if (!function_exists('wg_popup_html')) {
    function wg_popup_html($type, $msg)
    {
        return '
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<script type="text/javascript">
$(function(){
    popup({
        type:"' . $type . '",
        msg:"' . $msg . '",
        delay:2000,
        bg:true,
        clickDomCancel:true
    });
});
</script>';
    }
}

if (!function_exists('wg_info_field')) {
    function wg_info_field($wginfo, $index)
    {
        $parts = explode('|', $wginfo);
        return isset($parts[$index]) ? wg_safe_text($parts[$index]) : '';
    }
}

if (!function_exists('wg_quality_badge_html')) {
    function wg_quality_badge_html($color)
    {
        $name = wg_color_name($color);
        $safeColor = wg_safe_text($color);
        return '<span class="wg-quality-badge" style="color:' . $safeColor . ';border-color:' . $safeColor . '" title="' . $name . '">' . $name . '</span>';
    }
}

if (!function_exists('wg_progress_bar_html')) {
    function wg_progress_bar_html($current, $max, $id = '', $extraClass = '')
    {
        $max = max(1, intval($max));
        $current = max(0, intval($current));
        $pct = min(100, round(($current / $max) * 100, 2));
        $cls = ($current >= $max) ? 'ready' : 'training';
        $idAttr = $id ? ' id="' . $id . '"' : '';
        $class = 'wg-pbar ' . $cls . ' ' . $extraClass;
        return '<div class="' . trim($class) . '"' . $idAttr . '>'
             . '<div class="wg-pbar-fill" style="width:' . $pct . '%"></div>'
             . '<span class="wg-pbar-text">' . number_format($current) . ' / ' . number_format($max) . ' (' . $pct . '%)</span>'
             . '</div>';
    }
}

if (!function_exists('wg_confirm_modal')) {
    function wg_confirm_modal()
    {
        static $injected = false;
        if ($injected) return '';
        $injected = true;
        return '
<style>
.wg-cf-overlay {
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    background: rgba(2,6,23,.82);
    backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
    opacity: 0; transition: opacity .25s ease;
}
.wg-cf-overlay.wg-cf-show { opacity: 1; }
.wg-cf-modal {
    width: 340px; max-width: 90vw;
    border: 1px solid rgba(125,211,252,.32);
    border-radius: 18px;
    background: radial-gradient(circle at 20% 0%,rgba(56,189,248,.12),transparent 38%),
                linear-gradient(180deg,rgba(14,28,72,.94),rgba(8,18,50,.96));
    box-shadow: 0 0 0 1px rgba(255,255,255,.04) inset,
                0 0 32px rgba(56,189,248,.18),
                0 20px 40px rgba(0,0,0,.45);
    transform: scale(.88) translateY(18px);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
    overflow: hidden;
}
.wg-cf-overlay.wg-cf-show .wg-cf-modal { transform: scale(1) translateY(0); }
.wg-cf-head {
    padding: 22px 22px 0;
    text-align: center;
}
.wg-cf-icon {
    width: 52px; height: 52px; margin: 0 auto 14px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    border: 1px solid rgba(251,191,36,.45);
    background: rgba(251,191,36,.10);
    font-size: 24px;
    box-shadow: 0 0 18px rgba(251,191,36,.20);
}
.wg-cf-title {
    margin: 0 0 6px;
    color: #fff;
    font-size: 17px;
    font-weight: 1000;
    text-shadow: 0 0 10px rgba(125,211,252,.35);
}
.wg-cf-msg {
    margin: 0;
    color: #9fcaff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.5;
}
.wg-cf-body { padding: 18px 22px 0; }
.wg-cf-actions {
    display: flex; gap: 10px;
    padding: 18px 22px 22px;
    align-items: center;
}
.wg-cf-btn {
    flex: 1; min-height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    text-align: center;
    border-radius: 12px;
    font-size: 19px; font-weight: 1000;
    line-height: 2;
    text-decoration: none; cursor: pointer;
    border: 1px solid; transition: filter .18s, transform .18s;
    padding: 0 14px;
    box-sizing: border-box;
}
.wg-cf-btn:active { transform: scale(.96); }
.wg-cf-cancel {
    border-color: rgba(148,163,184,.32) !important;
    background: rgba(30,41,59,.72) !important;
    color: #cbd5e1 !important;
}
.wg-cf-cancel:hover { filter: brightness(1.12); color: #fff !important; }
.wg-cf-confirm-danger {
    border-color: rgba(248,113,113,.52) !important;
    background: linear-gradient(135deg,rgba(220,38,38,.72),rgba(153,27,27,.78)) !important;
    color: #fff !important;
    box-shadow: 0 0 16px rgba(239,68,68,.28);
}
.wg-cf-confirm-danger:hover { filter: brightness(1.12); box-shadow: 0 0 22px rgba(239,68,68,.42); }
.wg-cf-confirm-warning {
    border-color: rgba(251,191,36,.52) !important;
    background: linear-gradient(135deg,rgba(245,158,11,.72),rgba(180,83,9,.78)) !important;
    color: #fff !important;
    box-shadow: 0 0 16px rgba(245,158,11,.28);
}
.wg-cf-confirm-warning:hover { filter: brightness(1.12); box-shadow: 0 0 22px rgba(245,158,11,.42); }
.wg-cf-confirm-info {
    border-color: rgba(56,189,248,.52) !important;
    background: linear-gradient(135deg,rgba(34,211,238,.72),rgba(37,99,235,.78)) !important;
    color: #fff !important;
    box-shadow: 0 0 16px rgba(56,189,248,.28);
}
.wg-cf-confirm-info:hover { filter: brightness(1.12); box-shadow: 0 0 22px rgba(56,189,248,.42); }
</style>
<script>
(function(){
    if (window.__wgConfirmReady) return;
    window.__wgConfirmReady = true;
    window.wgConfirm = function(opts) {
        var msg     = opts.msg     || "Bạn có chắc?";
        var title   = opts.title   || "Xác nhận";
        var icon    = opts.icon    || "⚠";
        var confirmText = opts.confirmText || "Xác nhận";
        var type    = opts.type    || "danger";
        var onYes   = opts.onYes   || function(){};
        var onNo    = opts.onNo    || null;

        var overlay = document.createElement("div");
        overlay.className = "wg-cf-overlay";
        overlay.innerHTML =
            \'<div class="wg-cf-modal">\' +
                \'<div class="wg-cf-head">\' +
                    \'<div class="wg-cf-icon">\' + icon + \'</div>\' +
                    \'<h3 class="wg-cf-title">\' + title + \'</h3>\' +
                    \'<p class="wg-cf-msg">\' + msg + \'</p>\' +
                \'</div>\' +
                \'<div class="wg-cf-actions">\' +
                    \'<a class="wg-cf-btn wg-cf-cancel" href="javascript:void(0)">Hủy</a>\' +
                    \'<a class="wg-cf-btn wg-cf-confirm-\' + type + \'" href="javascript:void(0)">\' + confirmText + \'</a>\' +
                \'</div>\' +
            \'</div>\';
        document.body.appendChild(overlay);
        requestAnimationFrame(function(){ overlay.classList.add("wg-cf-show"); });

        var cancelBtn = overlay.querySelector(".wg-cf-cancel");
        var confirmBtn = overlay.querySelector(".wg-cf-btn[class*=wg-cf-confirm]");

        function close(result) {
            overlay.classList.remove("wg-cf-show");
            setTimeout(function(){ overlay.remove(); }, 300);
            if (result === true) onYes();
            else if (onNo) onNo();
        }
        cancelBtn.onclick = function(){ close(false); };
        confirmBtn.onclick = function(){ close(true); };
        overlay.onclick = function(e){ if (e.target === overlay) close(false); };
    };
})();
</script>';
    }
}
