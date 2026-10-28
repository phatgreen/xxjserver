<?php
function makePopup($type, $msg) {
    return <<<HTML
<link rel="stylesheet" type="text/css" href="./chajian/tishikuang/style/dialog.css">
<script src="./chajian/tishikuang/javascript/zepto.min.js"></script>
<script src="./chajian/tishikuang/javascript/dialog.min.js"></script>
<font id="popup_btn"></font>
<script>
setTimeout(function() {
    var el = document.getElementById("popup_btn");
    if (document.all) {
        el.click();
    } else {
        var e = document.createEvent("MouseEvents");
        e.initEvent("click", true, true);
        el.dispatchEvent(e);
    }
}, 500);

$('#popup_btn').click(function(){
   popup({type:'$type',msg:"$msg",delay:2000,bg:true,clickDomCancel:true});
});
</script>
HTML;
}

function tupo_result($type, $title, $body) {
    $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    return '<div class="break-result '.$type.'"><b>'.$title.'</b><span>'.$body.'</span></div>';
}

function makeBreakthroughEffect($title, $realm, $body, $statHtml, $major) {
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $realm = htmlspecialchars($realm, ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    $class = $major ? ' major' : '';

    return <<<HTML
<div class="break-success-overlay$class" id="breakSuccessEffect">
    <div class="break-success-aura"></div>
    <i class="break-success-ray r1"></i>
    <i class="break-success-ray r2"></i>
    <i class="break-success-ray r3"></i>
    <i class="break-success-dot d1"></i>
    <i class="break-success-dot d2"></i>
    <i class="break-success-dot d3"></i>
    <i class="break-success-dot d4"></i>
    <section class="break-success-card">
        <div class="break-success-seal">
            <span></span>
            <b>Phá</b>
        </div>
        <div class="break-success-kicker">Thiên đạo cộng minh</div>
        <div class="break-success-title">$title</div>
        <div class="break-success-realm">$realm</div>
        <p>$body</p>
        <div class="break-success-stats">$statHtml</div>
        <button type="button" class="break-success-close" onclick="document.getElementById('breakSuccessEffect').className += ' closing';setTimeout(function(){var el=document.getElementById('breakSuccessEffect');if(el){el.parentNode.removeChild(el);}},420);">Thu công</button>
    </section>
</div>
<script>
setTimeout(function(){
    var el = document.getElementById('breakSuccessEffect');
    if (el) {
        el.className += ' ready';
    }
}, 60);
</script>
HTML;
}

function makeBreakFailEffect($title, $body, $lossHtml) {
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<div class="break-success-overlay fail" id="breakFailEffect">
    <div class="break-success-aura"></div>
    <i class="break-success-ray r1"></i>
    <i class="break-success-ray r2"></i>
    <i class="break-success-ray r3"></i>
    <i class="break-success-dot d1"></i>
    <i class="break-success-dot d2"></i>
    <i class="break-success-dot d3"></i>
    <i class="break-success-dot d4"></i>
    <section class="break-success-card">
        <div class="break-success-seal">
            <span></span>
            <b>Bại</b>
        </div>
        <div class="break-success-kicker">Tâm ma phản phệ</div>
        <div class="break-success-title">$title</div>
        <div class="break-success-realm">Cảnh giới chưa ổn định</div>
        <p>$body</p>
        <div class="break-success-stats">$lossHtml</div>
        <button type="button" class="break-success-close" onclick="document.getElementById('breakFailEffect').className += ' closing';setTimeout(function(){var el=document.getElementById('breakFailEffect');if(el){el.parentNode.removeChild(el);}},420);">Ổn định đạo tâm</button>
    </section>
</div>
<script>
setTimeout(function(){
    var el = document.getElementById('breakFailEffect');
    if (el) {
        el.className += ' ready';
    }
}, 60);
</script>
HTML;
}

$player = \player\getplayer($sid, $dblj);
$tssb1 = makePopup('error', 'Tu vi không đủ');

$ts = '';
$resultHtml = '';
$zbsjs = intval($player->tuposjs);
$tpBreak = isset($player->tianfu_bonus['breakthrough']) ? floatval($player->tianfu_bonus['breakthrough']) : 0;
$psjs = $player->ulv % 50 == 0 ? 10 : 0;
// Success rate: starts ~75%, gradually decreases, minimum 5%
$baseRate = 75;
$levelPenalty = $player->ulv * 0.6;
$realmPenalty = $player->jingjieValue * 3;
// Cấp > 93: công thức cũ âm quá sâu (cấp 500 = -258%) khiến mọi khoản cộng vô tác dụng.
// Gốc (Cơ bản - Cấp độ - Cảnh giới) bám mốc dưới, nội suy tuyến tính; chia phần trừ 90% Cấp độ / 10% Cảnh giới.
// Xem docs/KHUNG_TONG_500_4300.md mục 4.
if ($player->ulv > 93) {
    $tpPts = array(93 => 13.2, 100 => 10, 500 => -20, 1000 => -40, 2000 => -70, 4300 => -110);
    $tpTarget = -110;
    $tpPrevL = null;
    foreach ($tpPts as $tpL => $tpV) {
        if ($tpPrevL !== null && $player->ulv <= $tpL) {
            $tpTarget = $tpPts[$tpPrevL] + ($tpV - $tpPts[$tpPrevL]) * ($player->ulv - $tpPrevL) / ($tpL - $tpPrevL);
            break;
        }
        $tpPrevL = $tpL;
    }
    $tpPenalty = $baseRate - $tpTarget;
    $levelPenalty = round($tpPenalty * 0.9, 1);
    $realmPenalty = round($tpPenalty - $levelPenalty, 1);
}
// Đột Phá Đan (đúng cảnh giới, tối đa max_pills viên, mất khi thử) + Sự kiện theo ngày — class/config/tupo_bonus_config.json
$tpPill = \player\tupo_pill_for_level($player->ulv);
$danOwned = 0;
if ($tpPill) {
    $danRow = \player\getplayerdaoju($sid, $tpPill['djid'], $dblj);
    $danOwned = $danRow ? intval($danRow->djsum) : 0;
}
$danUse = ($tpPill && isset($tpdan)) ? max(0, min(intval($tpdan), $tpPill['max_pills'], $danOwned)) : 0;
$danBonus = $tpPill ? $danUse * $tpPill['per_pill'] : 0;
list($evBonus, $evName) = \player\tupo_event_bonus();
$rate =min(99, max(5, intval($baseRate - $levelPenalty - $realmPenalty + $psjs + $zbsjs + $tpBreak + $danBonus + $evBonus)));

// Giảm tỷ lệ đột phá do đan độc (>1000 toxin = -1% mỗi 100 điểm)
$toxinEffects = \player\luonden_apply_toxin_effects($sid, $dblj);
foreach ($toxinEffects as $eff) {
    if ($eff['type'] === 'breakthrough_penalty') {
        $rate = max(1, $rate - $eff['value']);
        $ts .= '<div style="color:#ff6b6b;margin-top:5px">⚠️ Đan độc giảm '.$eff['value'].'% tỷ lệ đột phá</div>';
    }
    if ($eff['type'] === 'level_lock') {
        $rate = 0;
        $ts .= '<div style="color:#ff4444;margin-top:5px">⛔ Đan độc quá nặng, không thể đột phá!</div>';
    }
}

$tupocmd = $encode->encode("cmd=tupo&canshu=tupo&tpdan=$danUse&sid=$sid");
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$tupo = \player\istupo($sid, $dblj);
$tpls = 0;

if ($tupo == 1) {
    $tpls = $player->ulv * $player->ulv * 3;
}
if ($tupo == 2) {
    $tpls = $player->ulv * ($player->ulv + 1);
}

if ($tupo != 8) {
    if ($player->uexp >= $player->umaxexp) {
        if (isset($canshu) && $canshu == 'tupo') {
            $ret = \player\changeyxb(2, $tpls, $sid, $dblj);
            if ($ret) {
                // Uống Đột Phá Đan: mất dù thành công hay thất bại. Trừ không được (vừa dùng ở tab khác) -> bỏ khoản Đan.
                if ($danUse > 0 && $rate > 0) {
                    if (\player\deledjsum($tpPill['djid'], $danUse, $sid, $dblj)) {
                        $danOwned -= $danUse;
                    } else {
                        $rate = max(5, $rate - $danBonus);
                        $danUse = 0;
                        $danBonus = 0;
                    }
                }
                // Two independent rolls. $btRoll decides success against the displayed
                // rate; $rand only grades how hard a failure lands. Sharing one roll
                // made the real success rate = displayed rate - 6 (branch 1..6 pre-empted).
                $rand = mt_rand(1, 100);
                $btRoll = mt_rand(1, 100);

                if ($rand <= 1 && $btRoll > $rate) {
                    $resultHtml = tupo_result('bad', 'Dục tốc bất đạt', $player->uname.' nền tảng không vững, tâm ma quấy nhiễu, cấp độ -1 và tu vi trở về 0.');
                    $failStats = '<div><span>Cấp độ</span><b>-1</b></div><div><span>Tu vi</span><b>về 0</b></div><div><span>Tâm ma</span><b>bạo phát</b></div><div><span>Căn cơ</span><b>chấn động</b></div>';
                    $ts .= makeBreakFailEffect('Dục tốc bất đạt', 'Nền tảng chưa vững, tâm ma thừa cơ phản phệ. Hãy ổn định căn cơ trước khi thử lại.', $failStats);
                    $player->uexp = 0;
                    \player\updateAttr($sid, $dblj, 'uexp', $player->uexp);
                    if ($player->ulv > 1) {
                        $player->ulv = $player->ulv - 1;
                        \player\updateAttr($sid, $dblj, 'ulv', $player->ulv);
                    }
                } elseif ($rand <= 2 && $btRoll > $rate) {
                    $resultHtml = tupo_result('bad', 'Thiên lôi giáng xuống', 'Kinh mạch chấn động, tu vi trở về 0 nhưng giữ lại căn nguyên để tu luyện tiếp.');
                    $failStats = '<div><span>Tu vi</span><b>về 0</b></div><div><span>Kinh mạch</span><b>chấn động</b></div><div><span>Căn nguyên</span><b>giữ lại</b></div><div><span>Lôi kiếp</span><b>chưa qua</b></div>';
                    $ts .= makeBreakFailEffect('Thiên lôi giáng xuống', 'Lôi kiếp đánh tan linh khí đang tụ, nhưng căn nguyên vẫn còn để tu luyện lại.', $failStats);
                    $player->uexp = 0;
                    \player\updateAttr($sid, $dblj, 'uexp', $player->uexp);
                } elseif ($rand <= 3 && $btRoll > $rate) {
                    $resultHtml = tupo_result('bad', 'Đạo tâm không vững', 'Linh khí tiêu tán 75%, cần tu luyện lại để ổn định căn cơ.');
                    $failStats = '<div><span>Tu vi</span><b>-75%</b></div><div><span>Đạo tâm</span><b>không vững</b></div><div><span>Linh khí</span><b>tan mạnh</b></div><div><span>Căn cơ</span><b>cần dưỡng</b></div>';
                    $ts .= makeBreakFailEffect('Đạo tâm không vững', 'Linh khí tán loạn trong kinh mạch, cần tích tụ lại trước khi tiếp tục xung cảnh.', $failStats);
                    $player->uexp = $player->uexp / 4;
                    \player\updateAttr($sid, $dblj, 'uexp', $player->uexp);
                } elseif ($rand <= 4 && $btRoll > $rate) {
                    $resultHtml = tupo_result('bad', 'Đạo tâm dao động', 'Linh khí tiêu tán 50%, cảnh giới chưa thể khai mở.');
                    $failStats = '<div><span>Tu vi</span><b>-50%</b></div><div><span>Đạo tâm</span><b>dao động</b></div><div><span>Linh khí</span><b>đứt mạch</b></div><div><span>Cảnh giới</span><b>khép lại</b></div>';
                    $ts .= makeBreakFailEffect('Đạo tâm dao động', 'Một niệm bất ổn khiến linh khí đứt mạch, cảnh giới mới tạm thời khép lại.', $failStats);
                    $player->uexp = $player->uexp / 2;
                    \player\updateAttr($sid, $dblj, 'uexp', $player->uexp);
                } elseif ($rand <= 6 && $btRoll > $rate) {
                    $miracleAtk = 999;
                    $miracleDef = 999;
                    $miracleHp = 999;
                    $resultHtml = tupo_result('good', 'Kỳ tích xuất hiện', 'Thiên đạo chiếu cố, linh quang đột khởi. Nhận khí vận đặc biệt: Công +'.$miracleAtk.', Phòng +'.$miracleDef.', Huyết +'.$miracleHp.'. Thôi đừng mơ nữa tỉnh lại đi :))))');
                } elseif ($btRoll > $rate) {
                    $resultHtml = tupo_result('bad', 'Đột phá thất bại', 'Chia buồn '.$player->uname.', linh khí chưa đủ ổn định để vượt qua lần khảo nghiệm này.');
                    $failStats = '<div><span>Kết quả</span><b>thất bại</b></div><div><span>Linh thạch</span><b>đã hao</b></div><div><span>Linh khí</span><b>chưa ổn</b></div><div><span>Cảnh giới</span><b>giữ nguyên</b></div>';
                    $ts .= makeBreakFailEffect('Đột phá thất bại', 'Linh khí chưa đủ ổn định để vượt qua lần khảo nghiệm này. Hãy tích lũy thêm trước khi thử lại.', $failStats);
                } else {
                    // Đợt F: mọi lần đột phá cộng số cố định theo cấp. Bản cũ (giai thường) cộng
                    // 1% tổng công/phòng + 5% máu hiện tại => lũy tiến theo cấp số nhân (máu vô hạn từ ~cấp 150).
                    $uphp = 4 + round($player->ulv / 1.2);
                    $upgj = 2 + round($player->ulv / 2.5);
                    $upfy = 3 + round($player->ulv / 2);

                    \player\upplayerlv($sid, $dblj);
                    \player\addplayersx("umaxhp", $uphp, $sid, $dblj);
                    \player\addplayersx("ugj", $upgj, $sid, $dblj);
                    \player\addplayersx("ufy", $upfy, $sid, $dblj);
                    \player\addplayersx("tf", 5, $sid, $dblj);

                    $player = \player\getplayer($sid, $dblj);

                    if (function_exists('player\\nhiemvu_update_all')) {
                        \player\nhiemvu_update_all($sid, 'breakthrough', 1, $dblj, array(
                            'level' => intval($player->ulv),
                        ));
                    }

                    $resultHtml = tupo_result('good', 'Đột phá thành công', 'Linh quang đột khởi. Nhận thuộc tính: Công +'.$upgj.', Phòng +'.$upfy.', HP +'.$uphp.', Thiên phú +5.');
                    $successRealm = trim(strip_tags(str_replace(array('<br/>', '<br>', '<br />'), ' ', $player->jingjie.' '.$player->cengci)));
                    $successTitle = ($tupo == 1) ? 'Đại cảnh giới khai mở' : 'Đột phá thành công';
                    $successBody = ($tupo == 1) ? 'Lôi kiếp tan, thiên môn mở. Căn cơ đã vượt khỏi giới hạn cũ.' : 'Linh khí quy nguyên, kinh mạch thông suốt. Cảnh giới mới đã ổn định.';
                    $successStats = '<div><span>Công</span><b>+'.$upgj.'</b></div><div><span>Phòng</span><b>+'.$upfy.'</b></div><div><span>HP</span><b>+'.$uphp.'</b></div><div><span>Thiên phú</span><b>+5</b></div>';
                    $ts .= makeBreakthroughEffect($successTitle, $successRealm, $successBody, $successStats, $tupo == 1);
                }
            } else {
                $resultHtml = tupo_result('bad', 'Linh thạch không đủ', 'Cần '.$tpls.' linh thạch để tiến hành đột phá.');
            }
        }
    } else {
        $resultHtml = tupo_result('bad', 'Tu vi không đủ', 'Cần đạt '.$player->umaxexp.' tu vi để mở đột phá.');
        $ts .= $tssb1;
    }
}

$zhanbi = 0;
if ($player->umaxexp > 0) {
    $zhanbi = round($player->uexp / $player->umaxexp * 100);
}
if ($zhanbi > 100) {
    $zhanbi = 100;
}
if ($zhanbi < 0) {
    $zhanbi = 0;
}

$rateBar = max(0, min(100, intval($rate)));
$canBreak = ($tupo != 8 && $player->uexp >= $player->umaxexp && intval($player->uyxb) >= intval($tpls));
$expClass = ($player->uexp >= $player->umaxexp) ? 'ok' : 'bad';
$stoneClass = (intval($player->uyxb) >= intval($tpls)) ? 'ok' : 'bad';
$breakType = $tupo == 1 ? 'Đột phá cảnh giới' : 'Đột phá cấp độ';
$realmStep = max(0, intval(floor(intval($player->ulv) / 50)));
$innerLevel = intval($player->ulv) % 50;
$isMajorBreak = ($tupo == 1);
$realmName = trim(strip_tags(str_replace(array('<br/>', '<br>', '<br />'), ' ', $player->jingjie.' '.$player->cengci)));
$realmName = htmlspecialchars($realmName, ENT_QUOTES, 'UTF-8');

if ($isMajorBreak) {
    $breakArt = 'images/breakthrough/major-lightning.png';
    $breakArtClass = 'major';
    $breakArtTitle = 'Đại Đột Phá';
    $breakArtDesc = 'Lôi kiếp mở thiên môn, vượt khỏi đỉnh phong để khai sinh cảnh giới mới.';
} elseif ($realmStep <= 1) {
    $breakArt = 'images/breakthrough/stage-initiate.webp';
    $breakArtClass = 'initiate';
    $breakArtTitle = 'Khai Mạch';
    $breakArtDesc = 'Linh khí tụ ở đan điền, thích hợp các tầng nhập môn và luyện thể.';
} elseif ($realmStep <= 8) {
    $breakArt = 'images/breakthrough/stage-core.jfif';
    $breakArtClass = 'core';
    $breakArtTitle = 'Ngưng Khí';
    $breakArtDesc = 'Khí toàn xoay chuyển, phù hợp khi vượt các tầng trong tiểu cảnh giới.';
} elseif ($realmStep <= 18) {
    $breakArt = 'images/breakthrough/stage-cosmic.jpg';
    $breakArtClass = 'cosmic';
    $breakArtTitle = 'Tinh Hà Dẫn Đạo';
    $breakArtDesc = 'Linh quang hóa dòng sao, cảnh giới cao mở hiệu ứng mênh mông hơn.';
} elseif ($realmStep <= 38) {
    $breakArt = 'images/breakthrough/stage-dna.jpg';
    $breakArtClass = 'dna';
    $breakArtTitle = 'Tiên Cốt Tái Sinh';
    $breakArtDesc = 'Mạch lạc và căn cốt cộng hưởng, dành cho tiên cảnh và thánh cảnh.';
} else {
    $breakArt = 'images/breakthrough/stage-chakra.jpg';
    $breakArtClass = 'chakra';
    $breakArtTitle = 'Đạo Tâm Viên Chiếu';
    $breakArtDesc = 'Luân quang đồng hiện, dùng cho các tầng gần đại đạo tối cao.';
}

if ($tupo == 8) {
    $actionHtml = '<span class="break-btn disabled">Đã đạt giới hạn</span>';
} elseif ($canBreak) {
    $actionHtml = '<a class="break-btn" href="?cmd='.$tupocmd.'">Nhấn đột phá</a>';
} else {
    $actionHtml = '<span class="break-btn disabled">Chưa đủ điều kiện</span>';
}

// Khu chọn Đột Phá Đan (0..max viên, chỉ chọn được tới số viên đang có)
$danHtml = '';
if ($tupo != 8) {
    if ($tpPill) {
        $danHtml = '<div class="break-dan"><div class="break-dan-head">💊 ' . htmlspecialchars($tpPill['name'], ENT_QUOTES, 'UTF-8')
            . ' <span>có ' . $danOwned . ' viên · +' . $tpPill['per_pill'] . '%/viên, tối đa ' . $tpPill['max_pills'] . '</span></div><div class="break-dan-pick">';
        for ($k = 0; $k <= $tpPill['max_pills']; $k++) {
            if ($k > $danOwned) {
                $danHtml .= '<span class="off">' . $k . '</span>';
            } else {
                $danHtml .= '<a class="' . ($k == $danUse ? 'on' : '') . '" href="?cmd=' . $encode->encode("cmd=tupo&tpdan=$k&sid=$sid") . '">' . $k . '</a>';
            }
        }
        $danHtml .= '</div><div class="break-dan-note">Đan mất dù thành công hay thất bại.</div></div>';
    } else {
        $danHtml = '<div class="break-dan"><div class="break-dan-note">'
            . (\player\tupo_pill_no_effect($player->ulv) ? 'Từ Độ Kiếp trở đi, đan dược không còn tác dụng với đột phá — chỉ dựa vào đạo hạnh.' : 'Cảnh giới này chưa có Đột Phá Đan.')
            . '</div></div>';
    }
    if ($evBonus > 0) {
        $danHtml .= '<div class="break-event">🎉 Sự kiện ' . htmlspecialchars($evName, ENT_QUOTES, 'UTF-8') . ': +' . $evBonus . '% tỉ lệ đột phá</div>';
    }
}

if (trim($resultHtml) == '') {
    $resultHtml = '<div class="break-note">Linh khí đã viên mãn thì có thể thử đột phá. Thất bại vẫn tiêu hao linh thạch, hãy cân nhắc trước khi xuất thủ.</div>';
}

$tupohtml = <<<HTML
$ts


<div class="break-page" style="--break-progress:$zhanbi%;--break-rate:$rateBar%;">
    <section class="break-art $breakArtClass">
        <div class="break-art-stage">
            <img src="$breakArt" alt="$breakArtTitle">
            <i class="break-orbit one"></i>
            <i class="break-orbit two"></i>
            <i class="break-spark s1"></i>
            <i class="break-spark s2"></i>
            <i class="break-spark s3"></i>
        </div>
        <div class="break-art-info">
            <div class="break-kicker">Lôi kiếp khai cảnh</div>
            <div class="break-title">Đột Phá</div>
            <div class="break-sub">$breakType để nâng cảnh giới, mở thêm sức mạnh và nhận thiên phú sau mỗi lần thành công.</div>
            <div class="break-art-meta">
                <span>$breakArtTitle</span>
                <b>$realmName</b>
                <p>$breakArtDesc</p>
            </div>
        </div>
    </section>

    <div class="break-layout">
        <section class="break-card">
            <h2>Trạng thái tu luyện</h2>
            <div class="break-stat-grid">
                <div class="break-stat full"><span>Cấp hiện tại: </span><b style="font-size:14px">$player->ulv</b><br><span>Cảnh giới: </span><b style="font-size:14px">$player->jingjie $player->cengci</b></div>
                <div class="break-stat $expClass"><span>Tu vi</span><b>$player->uexp / $player->umaxexp</b></div>
                <div class="break-stat $stoneClass"><span>Linh thạch</span><b>$tpls / $player->uyxb</b></div>
            </div>
            <div class="break-progress">
                <div class="break-progress-top"><span>Tiến độ tu vi</span><b>$zhanbi%</b></div>
                <div class="break-bar"><i></i></div>
            </div>
            <div class="break-bless">
                <div><span>Cơ bản</span><b>$baseRate%</b></div>
                <div><span>Cấp độ</span><b>-$levelPenalty%</b></div>
                <div><span>Cảnh giới</span><b>-$realmPenalty%</b></div>
                <div><span>Milestone</span><b>+$psjs%</b></div>
                <div><span>Trang bị</span><b>+$zbsjs%</b></div>
                <div><span>Thiên Phú</span><b>+$tpBreak%</b></div>
                <div><span>Đan</span><b>+$danBonus%</b></div>
                <div><span>Sự kiện</span><b>+$evBonus%</b></div>
            </div>
        </section>

        <section class="break-card">
            <h2>Tỉ lệ đột phá</h2>
            <div class="break-rate">
                <div class="break-ring">
                    <div class="break-ring-inner"><b>$rate%</b><span>thành công</span></div>
                </div>
            </div>
            $danHtml
            <div class="break-actions">$actionHtml</div>
        </section>
    </div>

    $resultHtml

    <div class="break-bottom">
        <a class="break-nav" href="#" onClick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="break-nav primary" href="game.php?cmd=$gonowmid">Đại Sảnh</a>
    </div>
</div>
HTML;

echo $tupohtml;
?>
