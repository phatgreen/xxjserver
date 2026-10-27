<?php
if (!isset($cmd)) $cmd = '';
if (!isset($canshu)) $canshu = '';
if (!isset($wgid)) $wgid = 0;
if (!isset($cpid)) $cpid = 0;

require_once __DIR__ . '/wugong_utils.php';

$player = \player\getplayer($sid, $dblj);

$gonowmid   = $encode->encode("cmd=gomid&newmid=".$player->nowmid."&sid=".$sid);
$xiuliancmd = $encode->encode("cmd=goxiulian&sid=".$sid);
$gocp       = $encode->encode("cmd=gocp&sid=".$sid);

$tscg = '';
$tssb = '';
$tiss = '';

$maxEquip = \player\congphap_max_equip($player);
$equipCount = \player\congphap_equip_count($sid, $dblj);

if ($canshu != '') {
    switch ($canshu) {
        case 'equip':
            if ($cpid > 0) {
                if ($equipCount >= $maxEquip) {
                    $tiss = '<div class="cp-message error">Đã đạt số slot vận công tối đa ('.$maxEquip.'). Hãy bỏ vận công một công pháp khác.</div>';
                } else {
                    \player\congphap_unequip_all($sid, $dblj);
                    \player\congphap_equip($cpid, $sid, $dblj, true);
                    $tscg = wg_popup_html('success', 'Vận công thành công');
                }
            }
            $player = \player\getplayer($sid, $dblj);
            $equipCount = \player\congphap_equip_count($sid, $dblj);
            break;

        case 'unequip':
            if ($cpid > 0) {
                \player\congphap_equip($cpid, $sid, $dblj, false);
                $tscg = wg_popup_html('success', 'Bỏ vận công thành công');
            }
            $player = \player\getplayer($sid, $dblj);
            $equipCount = \player\congphap_equip_count($sid, $dblj);
            break;

        case 'delete':
            if ($cpid > 0) {
                \player\congphap_delete($cpid, $sid, $dblj);
                $tscg = wg_popup_html('success', 'Đã xóa công pháp');
            }
            $player = \player\getplayer($sid, $dblj);
            $equipCount = \player\congphap_equip_count($sid, $dblj);
            break;

        case 'cpinfo':
            $cx = \player\congphap_cx($cpid, $sid, $dblj);
            if (!$cx) {
                echo '<div class="cp-message error">Không tìm thấy công pháp.</div>';
                exit;
            }

            $cpinfo = isset($cx->cpinfo) ? $cx->cpinfo : '';
            $parts = explode('|', $cpinfo);
            $effectName = \player\congphap_effect_name($cx->effect_type);
            $realVal = \player\congphap_real_value($cx, $cx->cpdj);
            $maxLevel = intval($cx->max_level);
            $cpColor = isset($cx->cpys) ? $cx->cpys : '#f9c508';

            $penaltyText = '';
            if ($cx->effect_type === 'absorb_speed' && floatval($cx->penalty_atk_percent) > 0) {
                $penaltyAtk = $realVal * floatval($cx->penalty_atk_percent);
                $penaltyDef = $realVal * floatval($cx->penalty_def_percent);
                $penaltyText = '<div class="cp-penalty">⚠ Giảm '.round($penaltyAtk, 1).'% Công, '.round($penaltyDef, 1).'% Thủ</div>';
            }
            if ($cx->effect_type === 'exp_bonus' && floatval(isset($cx->penalty_atk_percent) ? $cx->penalty_atk_percent : 0) > 0) {
                $penaltyAtk = $realVal * floatval($cx->penalty_atk_percent);
                $penaltyDef = $realVal * floatval(isset($cx->penalty_def_percent) ? $cx->penalty_def_percent : 0);
                $penaltyText = '<div class="cp-penalty">⚠ Giảm '.round($penaltyAtk, 1).'% Công, '.round($penaltyDef, 1).'% Thủ</div>';
            }

            $cpIsEquipped = (isset($cx->cpequipped) && intval($cx->cpequipped) == 1);

            $cpImg = './images/congphap/'.min(12, max(1, intval($cx->cpid))).'.svg';

            $cwhtml = '
<div class="cp-page">
    <div class="tcg-wg-hero">
        <div class="tcg-wg-imgbox">
            <img src="'.$cpImg.'" alt="'.$cx->cpname.'">
        </div>
        <div class="tcg-wg-hero-info">
            <div class="tcg-wg-kicker">Công pháp đang xem</div>
            <h2 style="color:'.$cpColor.'">'.$cx->cpname.'</h2>
            <p>'.wg_safe_text($cx->cpinfo).'</p>
            <div class="tcg-wg-current">
                <span>Phẩm chất</span>
                <b style="color:'.$cpColor.'">'.$cx->cpsort.'</b>
            </div>
        </div>
    </div>

    <div class="cp-tabs">
        <a href="?cmd='.$xiuliancmd.'">Ngồi Thiền</a>
        <a class="active" href="?cmd='.$gocp.'">Công Pháp</a>
    </div>

    <div class="cp-detail-card">
        <div class="cp-detail-head">
            <div>
                <div class="cp-subtitle">Chi tiết công pháp</div>
                <h2 style="color:'.$cpColor.'">'.$cx->cpname.'</h2>
            </div>
            <span class="cp-quality" style="border-color:'.$cpColor.';color:'.$cpColor.'">'.$cx->cpsort.'</span>
        </div>

        <div class="cp-detail-grid">
            <div><b>Loại</b><span>'.wg_safe_text($cx->cpsort).'</span></div>
            <div><b>Hiệu ứng</b><span style="color:#4caf50">+'.number_format($realVal, 1).'% '.$effectName.'</span></div>
            <div><b>Cấp độ</b><span>'.$cx->cpdj.' / '.$maxLevel.'</span></div>
            <div><b>Kinh nghiệm</b><span>'.number_format($cx->cpexp).' / '.number_format($cx->cpexpmax).'</span></div>
        </div>

        '.$penaltyText.'

        <div class="cp-desc">
            <b>Giới thiệu</b>
            <p>'.wg_safe_text(isset($parts[2]) ? $parts[2] : $cpinfo).'</p>
        </div>
    </div>

    <div class="cp-footer">
        <a class="cp-footer-btn" href="#" onclick="history.back();return false;">Trở Về</a>
        <a class="cp-footer-btn primary" href="game.php?cmd='.$gonowmid.'">Đại Sảnh</a>
    </div>
</div>';
            echo $cwhtml;
            exit;
    }
}

// Load danh sach cong phap cua player
$allCp = \player\congphap_sl($sid, $dblj);
$cwnamehtml = '';

if (!empty($allCp)) {
    foreach ($allCp as $cp) {
        $thisCpid  = intval($cp->cpid);
        $thisName  = isset($cp->cpname) ? $cp->cpname : 'Không rõ';
        $thisColor = isset($cp->cpys) ? $cp->cpys : '#ffffff';
        $thisDj    = isset($cp->cpdj) ? intval($cp->cpdj) : 1;
        $thisExp   = isset($cp->cpexp) ? intval($cp->cpexp) : 0;
        $thisMaxExp= isset($cp->cpexpmax) ? intval($cp->cpexpmax) : 100;
        $thisMaxLv = isset($cp->max_level) ? intval($cp->max_level) : 10;
        $isEquipped= (isset($cp->cpequipped) && intval($cp->cpequipped) == 1);
        $effectType= isset($cp->effect_type) ? $cp->effect_type : '';
        $effectName= \player\congphap_effect_name($effectType);
        $realVal   = \player\congphap_real_value($cp, $thisDj);

        $cpinfo = $encode->encode("cmd=gocp&canshu=cpinfo&cpid=".$thisCpid."&sid=".$sid);

        $pct = min(100, round(($thisExp / max(1, $thisMaxExp)) * 100, 1));

        if ($isEquipped) {
            $actionBtn = '<a class="tcg-wg-mini-btn stop" href="?cmd='.$encode->encode("cmd=gocp&canshu=unequip&cpid=".$thisCpid."&sid=".$sid).'" onclick="return false;" data-wg-confirm=\'{"msg":"Bỏ vận công công pháp này?","title":"Bỏ vận công","icon":"🔓","confirmText":"Xác nhận","type":"warning"}\'>Thu Pháp</a>';
            $activeClass = ' active';
            $activeBadge = '<span class="cp-current-badge">Đang vận công</span>';
        } else {
            $equipCmd = $encode->encode("cmd=gocp&canshu=equip&cpid=".$thisCpid."&sid=".$sid);
            $deleteCmd = $encode->encode("cmd=gocp&canshu=delete&cpid=".$thisCpid."&sid=".$sid);
            $actionBtn = '<a class="tcg-wg-mini-btn use" href="?cmd='.$equipCmd.'">Vận công</a>';
            $actionBtn .= '<a class="tcg-wg-mini-btn remove" href="?cmd='.$deleteCmd.'" onclick="return false;" data-wg-confirm=\'{"msg":"Bạn có chắc muốn xóa công pháp này?<br>Hành động không thể hoàn tác.","title":"Xóa công pháp","icon":"🗑","confirmText":"Xóa","type":"danger"}\'>Xóa</a>';
            $activeClass = '';
            $activeBadge = '';
        }

        $penaltyInfo = '';
        if ($effectType === 'absorb_speed' && floatval(isset($cp->penalty_atk_percent) ? $cp->penalty_atk_percent : 0) > 0) {
            $penaltyAtk = $realVal * floatval($cp->penalty_atk_percent);
            $penaltyInfo = ' <span style="color:#ef5350;font-size:12px">('.round($penaltyAtk,1).'% ATK/DEF)</span>';
        }

        $cwnamehtml .= '
        <div class="cp-card'.$activeClass.'">
            <div class="cp-card-main">
                <a class="cp-scroll" href="?cmd='.$cpinfo.'">📜</a>
                <div class="cp-card-info">
                    <div class="cp-name-row">
                        <a class="cp-name" style="color:'.$thisColor.'" href="?cmd='.$cpinfo.'">'.$thisName.'</a>
                        '.$activeBadge.'
                    </div>
                    <div class="cp-meta">
                        <span>Lv '.$thisDj.'/'.$thisMaxLv.'</span>
                        <span class="cp-dot">•</span>
                        <span style="color:'.$thisColor.'">'.$effectName.' +'.number_format($realVal,1).'%</span>
                        '.$penaltyInfo.'
                    </div>
                    <div class="cp-exp-bar">
                        <div class="cp-exp-fill" style="width:'.$pct.'%"></div>
                        <span class="cp-exp-text">'.number_format($thisExp).' / '.number_format($thisMaxExp).'</span>
                    </div>
                </div>
            </div>
            <div class="cp-actions">'.$actionBtn.'</div>
        </div>';
    }
} else {
    $cwnamehtml = '<div class="cp-empty">Bạn chưa có công pháp nào. Hãy đến Thiên Cơ Các để tìm kiếm công pháp phù hợp!</div>';
}

// Lay danh sach cong phap dang van cong
$equippedCp = \player\congphap_equipped($sid, $dblj);
$equippedNames = array();
if (!empty($equippedCp)) {
    foreach ($equippedCp as $eqCp) {
        $equippedNames[] = wg_safe_text($eqCp->cpname);
    }
}
$equipText = !empty($equippedNames) ? implode(', ', $equippedNames) : 'Chưa vận công';

// Hien thi thong tin penalty
$cpBuffs = \player\congphap_calc_buffs($sid, $dblj);
$penaltyAtkDisplay = round($cpBuffs['penalty_atk'], 1);
$penaltyDefDisplay = round($cpBuffs['penalty_def'], 1);
$absorbDisplay = round($cpBuffs['absorb_speed'], 1);

$penaltyHtml = '';
if ($penaltyAtkDisplay > 0 || $penaltyDefDisplay > 0) {
    $penaltyHtml = '<div class="cp-penalty-summary">
        <span style="color:#ef5350">⚠ Giảm '.$penaltyAtkDisplay.'% Công, -'.$penaltyDefDisplay.'% Thủ</span>
    </div>';
}

$cwhtml = <<<HTML
<div class="cp-page">
    <div class="tcg-wg-hero">
        <div class="tcg-wg-imgbox">
            <img src="./images/congphap/1.svg" alt="Công Pháp">
        </div>
        <div class="tcg-wg-hero-info">
            <div class="tcg-wg-kicker">Phương pháp tu luyện bị động</div>
            <h2>Công Pháp</h2>
            <p>Pháp môn tu luyện bị động, tăng tốc hấp thu linh khí, bạo kích, phòng ngự và nhiều hiệu ứng quý giá khác.</p>
            <div class="tcg-wg-current">
                <span>Vận Công</span>
                <b>$equipText</b>
            </div>
        </div>
    </div>

    <div class="cp-tabs">
        <a href="?cmd=$xiuliancmd">Ngồi Thiền</a>
        <a class="active" href="?cmd=$gocp">Công Pháp</a>
    </div>

    $penaltyHtml

    <div class="cp-list">
        $cwnamehtml
    </div>

    $tssb
    $tscg

    <div class="cp-footer">
        <a class="cp-footer-btn" href="#" onclick="history.back();return false;">Trở Về</a>
        <a class="cp-footer-btn primary" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>

<style>
.cp-slot-info{padding:8px 16px;color:#9fcaff;font-size:13px;font-weight:700;display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.cp-penalty-summary{color:#ef5350;font-size:12px;padding:8px 16px}
.cp-penalty{color:#ef5350;font-size:13px;font-weight:700;padding:6px 0}
.cp-exp-bar{position:relative;height:6px;border-radius:3px;background:rgba(255,255,255,.08);margin-top:4px;overflow:hidden}
.cp-exp-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#ff9800,#ffc107);transition:width .3s}
.cp-exp-text{position:absolute;right:0;top:-7px;font-size:7px;color:#9fcaff}
.cp-hero-avatar{width:100%;height:100%;border-radius:15px;background:linear-gradient(135deg,#f97316,#eab308,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:42px;font-weight:900;color:#fff;min-height:132px}
.cp-hero-sub{font-size:12px;color:#9fcaff;margin-top:2px}
.cp-name-row{display:flex!important;align-items:center!important;gap:8px!important;min-width:0!important}
.cp-name-row .cp-name{flex:1!important;min-width:0!important}
</style>
<script type="text/javascript">
document.querySelectorAll('[data-wg-confirm]').forEach(function(el){
    el.addEventListener('click', function(e){
        e.preventDefault();
        try {
            var opts = JSON.parse(this.getAttribute('data-wg-confirm'));
        } catch(ex) { return; }
        var url = this.getAttribute('href');
        opts.onYes = function(){ window.location.href = url; };
        wgConfirm(opts);
    });
});
</script>
HTML;

echo wg_confirm_modal() . $cwhtml;
?>