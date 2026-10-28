<?php
if (!isset($cmd)) $cmd = '';
if (!isset($canshu)) $canshu = 1;
if (!isset($buycount)) $buycount = 0;

require_once __DIR__ . '/wugong_utils.php';

$player = \player\getplayer($sid, $dblj);
$wgid = isset($player->wugong) ? intval($player->wugong) : 0;
$cxwg = \player\wgcx($wgid, $sid, $dblj);

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$strxl = $encode->encode("cmd=wgxiulian&canshu=1&wgid=$wgid&sid=$sid");
$wgxl = $encode->encode("cmd=wgxl&sid=$sid");
$wgxx = $encode->encode("cmd=xxwg&sid=$sid");
$chcmd = $encode->encode("cmd=conghuong&sid=$sid");

$buyBookPrice = 400;
$strbuybook = $encode->encode("cmd=wgxl&canshu=buybook&wgid=$wgid&sid=$sid");
$strbuybookCancel = $encode->encode("cmd=wgxl&sid=$sid");

$tishi = '';
$buyBookModal = '';
$forceRefreshWg = false;

$wuwugong = '';
$jieshao = '';
$wgys = '#ffffff';
$wgname = 'Chua hoc vo cong';
$wgdj = 0;
$wgxl_now = 0;
$wgxlmax = 1;
$wgsum = 0;
$wginfo = '||||';
$xiaohao = 1;

if ($cxwg) {
    $xiaohao = max(1, intval($cxwg->wgdj) + 1);
}

if ($cmd == 'wgxl' && ($canshu == 'buybook' || $canshu == 'buybook_confirm') && $cxwg) {
    $nowExp = intval($cxwg->wgxl);
    $needExp = max(1, intval($cxwg->wgxlmax));
    $bookNum = intval($cxwg->wgsum);
    $needBook = max(1, intval($cxwg->wgdj) + 1);
    $bookLack = max(0, $needBook - $bookNum);

    if ($nowExp < $needExp) {
        $lackExp = $needExp - $nowExp;
        $tishi = 'Kinh nghiệm võ công chưa đầy, còn thiếu '.$lackExp.' EXP.';
    } elseif ($bookLack <= 0) {
        $tishi = 'Bí tịch hiện đã đủ để nâng cấp.';
    } elseif ($canshu == 'buybook') {
        $safeWgName = wg_safe_text($cxwg->wgname);
        $totalCost = $bookLack * $buyBookPrice;
        $buyBookLinks = array();
        for ($i = 1; $i <= $bookLack; $i++) {
            $buyBookLinks[] = '"'.$i.'":"?cmd='.$encode->encode("cmd=wgxl&canshu=buybook_confirm&wgid=$wgid&buycount=$i&sid=$sid").'"';
        }
        $buyBookLinkMap = '{'.implode(',', $buyBookLinks).'}';

        $buyBookModal = <<<HTML
<div class="tcg-pay-overlay">
    <div class="tcg-pay-modal tcg-wg-buy-modal">
        <div class="tcg-pay-head">
            <div>
                <div class="tcg-pay-kicker">Mua bí tịch</div>
                <div class="tcg-pay-title">Nhập số lượng cần mua</div>
            </div>
            <a class="tcg-pay-close" href="?cmd=$strbuybookCancel">×</a>
        </div>

        <div class="tcg-pay-item">
            <div class="tcg-pay-icon">📕</div>
            <div>
                <div class="tcg-pay-label">Bí tịch tương ứng</div>
                <div class="tcg-pay-name">$safeWgName</div>
            </div>
        </div>

        <form class="tcg-wg-buy-form" method="get" action="game.php" onsubmit="return false;">
            <div class="tcg-pay-lines">
                <div><span>Giá mỗi bí tịch</span><strong class="purple">$buyBookPrice Ma thạch</strong></div>
                <div><span>Đang có / cần</span><strong>$bookNum / $needBook bí tịch</strong></div>
                <div><span>Còn thiếu</span><strong class="fail">$bookLack bí tịch</strong></div>
            </div>

            <label class="tcg-wg-buy-label" for="tcg-wg-buycount">Số lượng mua</label>
            <input id="tcg-wg-buycount" class="tcg-wg-buy-input" type="number" name="buycount" min="1" max="$bookLack" value="$bookLack">
            <div class="tcg-pay-status ok">Mặc định mua đủ số còn thiếu, dự kiến $totalCost Ma thạch.</div>

            <div class="tcg-pay-actions">
                <a class="tcg-pay-cancel" href="?cmd=$strbuybookCancel">Hủy</a>
                <a id="tcg-wg-buy-confirm" class="tcg-pay-confirm" href="?cmd=$strbuybookCancel">Xác nhận mua</a>
            </div>
        </form>
    </div>
</div>
<script>
(function(){
    var overlay = document.querySelector('.tcg-pay-overlay');
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
    var links = $buyBookLinkMap;
    var input = document.getElementById('tcg-wg-buycount');
    var confirm = document.getElementById('tcg-wg-buy-confirm');
    function syncBuyLink(){
        if (!input || !confirm) return;
        var count = parseInt(input.value, 10);
        if (!count || count < 1) count = 1;
        if (count > $bookLack) count = $bookLack;
        input.value = count;
        if (links[count]) confirm.href = links[count];
    }
    if (input) {
        input.addEventListener('input', syncBuyLink);
        input.addEventListener('change', syncBuyLink);
    }
    syncBuyLink();
})();
</script>
HTML;
    } else {
        $buyCount = intval($buycount);
        if ($buyCount < 1) $buyCount = $bookLack;
        if ($buyCount > $bookLack) $buyCount = $bookLack;
        $totalCost = $buyCount * $buyBookPrice;

        try {
            $dblj->beginTransaction();

            $payStmt = $dblj->prepare("UPDATE game1 SET uczb = uczb - ? WHERE sid = ? AND uczb >= ?");
            $payStmt->execute(array($totalCost, $sid, $totalCost));

            if ($payStmt->rowCount() < 1) {
                $dblj->rollBack();
                $tishi = 'Không đủ Ma thạch để mua '.$buyCount.' bí tịch. Cần '.$totalCost.' Ma thạch.';
            } else {
                $bookStmt = $dblj->prepare("UPDATE playerwugong SET wgsum = wgsum + ? WHERE wgid = ? AND sid = ?");
                $bookStmt->execute(array($buyCount, $wgid, $sid));

                if ($bookStmt->rowCount() < 1) {
                    $dblj->rollBack();
                    $tishi = 'Không thể cập nhật bí tịch, giao dịch đã hủy.';
                } else {
                    $dblj->commit();
                    \player\player_cache_clear($sid);
                    $player = \player\getplayer($sid, $dblj);
                    $forceRefreshWg = true;
                    $tishi = 'Mua thành công '.$buyCount.' bí tịch. Tiêu hao '.$totalCost.' Ma thạch.';
                }
            }
        } catch (\Exception $e) {
            if ($dblj->inTransaction()) {
                $dblj->rollBack();
            }
            $tishi = 'Giao dịch mua bí tịch thất bại, vui lòng thử lại.';
        }
    }
}

if ($cmd == 'wgxiulian' && $cxwg) {
    $nowExp = intval($cxwg->wgxl);
    $needExp = max(1, intval($cxwg->wgxlmax));
    $bookNum = intval($cxwg->wgsum);
    $needBook = max(1, intval($cxwg->wgdj) + 1);
    $addMax = max(10, intval(round($needExp * 0.2)));

    if ($nowExp < $needExp) {
        $lackExp = $needExp - $nowExp;
        $tishi = 'Kinh nghiệm võ công chưa đầy, còn thiếu '.$lackExp.' EXP.';
    } elseif ($bookNum < $needBook) {
        $tishi = 'Thiếu bí tịch, không thể tu luyện.';
    } else {
        $leftExp = $nowExp - $needExp;
        $sql = "UPDATE playerwugong
                SET wgxl = ?,
                    wgdj = wgdj + 1,
                    wgxlmax = wgxlmax + ?,
                    wgsum = wgsum - ?,
                    xlzt = 0
                WHERE wgid = ? AND sid = ?";
        $stmt = $dblj->prepare($sql);
        $stmt->execute(array($leftExp, $addMax, $needBook, $wgid, $sid));
        \player\player_cache_clear($sid);
        $forceRefreshWg = true;
        $tishi = 'Tu luyện thành công, võ công tăng 1 cấp. Tiêu hao bí tịch: '.$needBook;
        $player = \player\getplayer($sid, $dblj);
    }
}

$cxwg = \player\wgcx($wgid, $sid, $dblj, $forceRefreshWg);

if (!$cxwg || $wgid == 0) {
    $wuwugong = 'Không có võ công';
    $jieshao = 'Đi thu thập bí tịch để học võ công.';
    $xlcz = "<span class='tcg-wg-btn disabled'>Chưa có võ công</span>";
} else {
    $wgys = $cxwg->wgys;
    $wgname = $cxwg->wgname;
    $wgdj = intval($cxwg->wgdj);
    $wgxl_now = intval($cxwg->wgxl);
    $wgxlmax = max(1, intval($cxwg->wgxlmax));
    $wgsum = intval($cxwg->wgsum);
    $wginfo = $cxwg->wginfo;
    $xiaohao = max(1, $wgdj + 1);

    if ($tishi == '') {
        $tishi = ($wgxl_now >= $wgxlmax)
            ? 'Kinh nghiệm đã đầy, có thể tiêu hao bí tịch để nâng cấp.'
            : 'Đang tích lũy kinh nghiệm qua đánh BOSS...';
    }

    if ($wgxl_now >= $wgxlmax && $wgsum >= $xiaohao) {
        $xlcz = "<a class='tcg-wg-btn primary' href='?cmd=$strxl'>Nâng cấp võ công</a>";
    } elseif ($wgxl_now < $wgxlmax) {
        $lackExp = $wgxlmax - $wgxl_now;
        $xlcz = "<span class='tcg-wg-btn disabled'>Thiếu $lackExp EXP</span>";
    } else {
        $xlcz = "<span class='tcg-wg-btn disabled'>Thiếu bí tịch</span>";
    }
}

if ($wgxl_now >= $wgxlmax && $wgsum < $xiaohao) {
    $xlcz .= "<a class='tcg-wg-btn buybook' href='?cmd=$strbuybook'>Mua ngay $buyBookPrice Ma thạch</a>";
}

$exCxwg = explode('|', $wginfo);
$ex0 = isset($exCxwg[0]) ? wg_safe_text($exCxwg[0]) : '';
$ex1 = isset($exCxwg[1]) ? wg_safe_text($exCxwg[1]) : '';
$ex2 = isset($exCxwg[2]) ? wg_safe_text($exCxwg[2]) : '';
$ex3 = isset($exCxwg[3]) ? wg_safe_text($exCxwg[3]) : '';
$ex4 = isset($exCxwg[4]) ? wg_safe_text($exCxwg[4]) : '';
$shortIntro = trim($ex4) != '' ? $ex4 : $jieshao;

$imgPath = './images/wugong/'.$wgid.'.png';
$progress = ($wgxlmax > 0) ? min(100, round(($wgxl_now / $wgxlmax) * 100, 2)) : 0;
$statusClass = ($wgxl_now >= $wgxlmax) ? 'ready' : 'training';

$wgColorName = wg_color_name($wgys);

$safePlayerName = wg_safe_text($player->uname);

// Thế võ (Võ Kỹ / Tuyệt Kỹ / Tuyệt Chiêu) — class/config/wugong_skill_config.json
$cbMoveHtml = '';
$cbMovesP = ($cxwg && $wgid != 0) ? \player\combat_wugong_moves($cxwg) : array();
foreach ($cbMovesP as $mv) {
    $eff = \player\combat_effect_text($mv['effect'], $mv['value']);
    $lock = $mv['unlocked'] ? '' : ' is-lock';
    $state = $mv['unlocked'] ? 'Đã lĩnh ngộ' : '🔒 Võ công cấp ' . $mv['unlock_lv'];
    $cd = $mv['cooldown'] > 0 ? ' · hồi ' . $mv['cooldown'] . ' lượt' : '';
    $cbMoveHtml .= "<div class=\"tcg-wg-move t{$mv['tier']}{$lock}\">"
        . "<div class=\"tcg-wg-move-head\"><b>{$mv['label']}</b><em>{$state}</em></div>"
        . "<div class=\"tcg-wg-move-name\">{$mv['name']}" . ($mv['hits'] > 1 ? " <small>({$mv['hits']} đòn)</small>" : '') . "</div>"
        . "<div class=\"tcg-wg-move-stat\">Sát thương x{$mv['mult']} · {$mv['stamina_cost']} thể lực{$cd}</div>"
        . ($eff !== '' ? "<div class=\"tcg-wg-move-eff\">{$eff}</div>" : '')
        . "<div class=\"tcg-wg-move-text\">" . wg_safe_text(\player\combat_text($mv['text'], 'đối thủ')) . "</div>"
        . "</div>";
}
if ($cbMoveHtml !== '') {
    $cbMoveHtml = "<div class=\"tcg-wg-moves-card\"><div class=\"tcg-wg-card-title\">Chiêu thức</div>"
        . "<div class=\"tcg-wg-moves-note\">Nâng cấp võ công để mở thế mới và tăng sát thương. Trong trận: nút Tấn công dùng Võ Kỹ, hai thế còn lại có nút riêng.</div>"
        . "<div class=\"tcg-wg-moves\">{$cbMoveHtml}</div></div>";
}
$safeWgName = wg_safe_text($wgname);

$xlhtml = <<<HTML
<div class="tcg-wg-page wg-compact">
    <div class="tcg-wg-tabs">
        <a class="tcg-wg-tab active" href="?cmd=$wgxl">Tu luyện võ công</a>
        <a class="tcg-wg-tab" href="?cmd=$wgxx">Bí tịch</a>
        <a class="tcg-wg-tab" href="?cmd=$chcmd">Cộng Hưởng</a>
    </div>

    <div class="cws-stage wgs-stage" style="--cw-q:$wgys">
        <div class="cws-tag">🥋 Đang tu luyện</div>
        <div class="cws-side cws-side-left">
            <div class="cws-stat"><span>📈 Cấp võ công</span><b>$wgdj</b></div>
            <div class="cws-stat"><span>📖 Bí tịch</span><b>$wgsum</b></div>
            <div class="cws-stat"><span>🔥 Tiêu hao / cấp</span><b>$xiaohao bí tịch</b></div>
        </div>
        <div class="cws-figure wgs-figure"><img src="$imgPath" alt="Võ công"></div>
        <div class="cws-side cws-side-right">
            <div class="cws-stat"><span>✨ Phẩm chất</span><b style="color:$wgys">$wgColorName</b></div>
            <div class="cws-stat"><span>⚔️ Loại vũ khí</span><b class="wgs-small">$ex0</b></div>
            <div class="cws-stat"><span>🐉 Tăng EXP</span><b class="wgs-small">Đánh thắng BOSS</b></div>
        </div>
        <div class="cws-id">
            <div class="cws-name" style="color:$wgys;text-shadow:0 0 16px {$wgys}88">$wuwugong$safeWgName</div>
            <div class="wgs-status $statusClass">$tishi</div>
            <div class="wgs-actions">$xlcz</div>
        </div>
    </div>

    <div class="profile-bar wgs-bar">
        <div class="profile-bar-title"><span>✨ Kinh nghiệm võ công</span><span>$wgxl_now / $wgxlmax · $progress%</span></div>
        <div class="profile-bar-track"><div class="profile-bar-fill exp" style="width:$progress%"></div></div>
    </div>

    $cbMoveHtml

    <div class="tcg-wg-info-card wgs-info">
        <div class="tcg-wg-card-title">Thông tin võ công</div>
        <div class="wgs-lines">
            <div class="tcg-wg-line"><span>Đặc điểm</span><b>$ex1</b></div>
            <div class="tcg-wg-line"><span>Hiệu quả</span><b>$ex2</b></div>
            <div class="tcg-wg-line"><span>Phong cách</span><b>$ex3</b></div>
        </div>
        <div class="tcg-wg-desc">$shortIntro</div>
        <div class="wgs-note">Kinh nghiệm đầy + đủ bí tịch thì nâng cấp; thiếu bí tịch có thể mua đúng cuốn hiện tại giá $buyBookPrice Ma thạch.</div>
    </div>
</div>

<style>
.tcg-wg-page { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.tcg-wg-progress-bar { transition: width 0.6s ease; position: relative; overflow: hidden; }
.tcg-wg-progress-bar.ready { animation: wgPulse 1.5s ease-in-out infinite; }
.tcg-wg-progress-bar.ready::after {
    content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: wgShimmer 2s ease-in-out infinite;
}
@keyframes wgPulse { 0%,100% { opacity:1; } 50% { opacity:0.85; } }
@keyframes wgShimmer { 0% { left:-100%; } 100% { left:100%; } }
.tcg-wg-btn.primary { animation: wgBtnGlow 2s ease-in-out infinite; }
@keyframes wgBtnGlow { 0%,100% { box-shadow: 0 0 5px rgba(255,200,0,0.3); } 50% { box-shadow: 0 0 15px rgba(255,200,0,0.6); } }
.tcg-wg-quality { cursor: help; }
.tcg-wg-quality span { position: relative; }
</style>
HTML;

echo $xlhtml . $buyBookModal;
?>
