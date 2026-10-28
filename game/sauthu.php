<?php
if (!isset($sid) || $sid == '') {
    exit('Thiếu mã phiên nhân vật');
}

function sth_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sth_load_config($file) {
    $path = __DIR__ . '/../class/config/' . $file;
    if (is_file($path)) {
        return json_decode(file_get_contents($path), true);
    }
    return null;
}

function sth_get_player_item_count($sid, $djid, $dblj) {
    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
    $stmt->execute(array($sid, $djid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? max(0, intval($row['djsum'])) : 0;
}

function sth_remove_item($sid, $djid, $dblj) {
    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
    $stmt->execute(array($sid, $djid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || intval($row['djsum']) <= 0) {
        return false;
    }
    $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - 1 WHERE sid = ? AND djid = ? AND djsum > 0");
    $stmt->execute(array($sid, $djid));
    return $stmt->rowCount() > 0;
}

function sth_give_item($sid, $djid, $amount, $dblj) {
    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
    $stmt->execute(array($sid, $djid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
        $stmt->execute(array($amount, $sid, $djid));
    } else {
        $stmt = $dblj->prepare("INSERT INTO playerdaoju (sid, djid, djsum) VALUES (?, ?, ?)");
        $stmt->execute(array($sid, $djid, $amount));
    }
    return true;
}

$player = \player\getplayer($sid, $dblj);
$state = \player\sauthu_state($sid, $dblj);
$yeuthuConfig = sth_load_config('yeuthu_config.json');
$petConfig = sth_load_config('pet_config.json');
$buaConfig = sth_load_config('bua_config.json');

$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$sthCmd = $encode->encode("cmd=sauthu&sid=$sid");
$cwCmd = $encode->encode("cmd=chongwu&sid=$sid");
$shopCmd = $encode->encode("cmd=shangdian&canshu2=gogoumai2&sid=$sid");

// Xử lý mua bua nhanh
$paymentModalHtml = '';
if (isset($canshu) && $canshu == 'buy_bua' && isset($buy_bua_id)) {
    $buaId = intval($buy_bua_id);
    $buyQty = max(1, min(99, intval($buy_qty)));
    if (isset($buaConfig[$buaId])) {
        $buaInfo = $buaConfig[$buaId];
        $ltCost = intval($buaInfo['lt_cost']) * $buyQty;
        if (\player\changeyxb(2, $ltCost, $sid, $dblj)) {
            \player\adddj($sid, $buaId, $buyQty, $dblj);
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $notice = '<div class="sth-notice win">Mua thành công '.$buyQty.'x '.sth_h($buaInfo['name']).'!</div>';
        } else {
            $notice = '<div class="sth-notice fail">Linh thạch không đủ! Cần '.$ltCost.' LT.</div>';
        }
    }
}

// Hiển thị popup thanh toán mua bua
if (isset($canshu) && $canshu == 'pay_bua' && isset($pay_bua_id)) {
    $payId = intval($pay_bua_id);
    if (isset($buaConfig[$payId])) {
        $payInfo = $buaConfig[$payId];
        $payUnitPrice = intval($payInfo['lt_cost']);
        $payBalance = max(0, intval($player->uyxb));
        $cancelCmd = $sthCmd;

        $paymentModalHtml = <<<HTML
<div class="sth-pay-overlay">
    <div class="sth-pay-modal">
        <div class="sth-pay-head">
            <div>
                <div class="sth-pay-kicker">Đơn thanh toán</div>
                <div class="sth-pay-title">Xác nhận mua vật phẩm</div>
            </div>
            <a class="sth-pay-close" href="?cmd=$cancelCmd">×</a>
        </div>
        <div class="sth-pay-item">
            <div class="sth-pay-icon">📋</div>
            <div>
                <div class="sth-pay-label">Vật phẩm</div>
                <div class="sth-pay-name" style="color:#cbd5e1!important">{$payInfo['name']}</div>
                <div class="sth-pay-unit">Đơn giá: <b>{$payUnitPrice} LT</b></div>
            </div>
        </div>
        <div class="sth-pay-qty">
            <span>Số lượng</span>
            <div class="sth-pay-qty-btns">
                <a class="sth-pay-qty-btn" onclick="sthQtyChange(-10)">-10</a>
                <a class="sth-pay-qty-btn" onclick="sthQtyChange(-1)">-</a>
                <input type="number" id="sthPayQty" class="sth-pay-qty-input" value="1" min="1" max="99" onchange="sthQtyUpdate()">
                <a class="sth-pay-qty-btn" onclick="sthQtyChange(1)">+</a>
                <a class="sth-pay-qty-btn" onclick="sthQtyChange(10)">+10</a>
            </div>
        </div>
        <div class="sth-pay-lines">
            <div><span>Đơn giá</span><strong class="gold">{$payUnitPrice} LT</strong></div>
            <div><span>Số lượng</span><strong class="gold" id="sthPayQtyText">1</strong></div>
            <div><span>Tổng thanh toán</span><strong class="gold" id="sthPayTotal">{$payUnitPrice} LT</strong></div>
            <div><span>Số dư hiện có</span><strong class="gold">{$payBalance} LT</strong></div>
            <div><span>Số dư sau mua</span><strong id="sthPayAfter">{$payBalance} LT</strong></div>
        </div>
        <div class="sth-pay-status" id="sthPayStatus"></div>
        <div class="sth-pay-actions">
            <a class="sth-pay-cancel" href="?cmd=$cancelCmd">Hủy</a>
            <a class="sth-pay-confirm" id="sthPayConfirm" href="#">Xác nhận thanh toán</a>
        </div>
    </div>
</div>
<script>
var sthUnitPrice = $payUnitPrice;
var sthBalance = $payBalance;
var sthPayId = $payId;
var sthSid = '$sid';
function sthQtyChange(d){var el=document.getElementById('sthPayQty');var v=Math.max(1,Math.min(99,parseInt(el.value)+d));el.value=v;sthQtyUpdate()}
function sthQtyUpdate(){
    var qty=Math.max(1,Math.min(99,parseInt(document.getElementById('sthPayQty').value)||1));
    document.getElementById('sthPayQty').value=qty;
    var total=sthUnitPrice*qty;
    var after=sthBalance-total;
    var ok=sthBalance>=total;
    document.getElementById('sthPayQtyText').textContent=qty;
    document.getElementById('sthPayTotal').textContent=total+' LT';
    document.getElementById('sthPayAfter').textContent=after+' LT';
    document.getElementById('sthPayAfter').className=ok?'ok':'fail';
    var st=document.getElementById('sthPayStatus');
    st.textContent=ok?'Số dư đủ để thanh toán':'Số dư không đủ';
    st.className='sth-pay-status '+(ok?'ok':'fail');
    var btn=document.getElementById('sthPayConfirm');
    if(ok){btn.className='sth-pay-confirm';btn.href='?cmd=sauthu&canshu=buy_bua&buy_bua_id='+sthPayId+'&buy_qty='+qty+'&sid='+sthSid}
    else{btn.className='sth-pay-confirm disabled';btn.href='#'}
}
sthQtyUpdate();
</script>
HTML;
    }
}

$notice = '';
$battleHtml = '';
$rewardHtml = '';
$selectedMap = isset($map) ? max(1, min(10, intval($map))) : 0;

// Xử lý vào phó bản
if (isset($canshu) && $canshu == 'enter' && $selectedMap > 0) {
    $mapConfig = isset($yeuthuConfig['maps'][$selectedMap]) ? $yeuthuConfig['maps'][$selectedMap] : null;
    if (!$mapConfig) {
        $notice = '<div class="sth-notice fail">Map không tồn tại!</div>';
    } elseif ($state['best_map'] < $selectedMap - 1 && $selectedMap > 1) {
        $notice = '<div class="sth-notice fail">Bạn chưa vượt qua map trước đó!</div>';
    } else {
        $levelRange = isset($mapConfig['level_range']) ? $mapConfig['level_range'] : array(1, 999);
        $playerLevel = max(1, intval($player->ulv));
        if ($playerLevel < intval($levelRange[0])) {
            $notice = '<div class="sth-notice fail">Level không đủ! Cần cấp tối thiểu '.$levelRange[0].'</div>';
        } else {
            $buaId = intval($mapConfig['bua_id']);
            $mtCost = intval($mapConfig['mt_cost']);
            $buaCount = sth_get_player_item_count($sid, $buaId, $dblj);
            $mtCount = max(0, intval($player->uczb));

            if ($buaCount < 1) {
                $notice = '<div class="sth-notice fail">Bạn không có Bùa Thu Phục cho map này!</div>';
            } elseif ($mtCount < $mtCost) {
                $notice = '<div class="sth-notice fail">Ma thạch không đủ! Cần '.$mtCost.' MT.</div>';
            } else {
                if (!\player\changeczb(2, $mtCost, $sid, $dblj)) {
                    $notice = '<div class="sth-notice fail">Ma Thạch không đủ! Vui lòng thử lại.</div>';
                } elseif (!sth_remove_item($sid, $buaId, $dblj)) {
                    $notice = '<div class="sth-notice fail">Bùa Thu Phục không đủ! Vui lòng thử lại.</div>';
                } else {
                    $monsters = \player\sauthu_get_monsters($selectedMap);
                    if (empty($monsters)) {
                        $notice = '<div class="sth-notice fail">Không tìm thấy yêu thú!</div>';
                    } else {
                        $allWin = true;
                        $battleLog = array();
                        $player = \player\getplayer($sid, $dblj);

                        foreach ($monsters as $idx => $monster) {
                            $result = \player\sauthu_battle($player, $monster);
                            $battleLog[] = array('monster' => $monster, 'result' => $result);
                            if (!$result['win']) {
                                $allWin = false;
                                break;
                            }
                            $player->uhp = $result['player_hp'];
                        }

                        if ($allWin) {
                            $quality_tier = intval($mapConfig['quality_tier']);
                            $captureRate = \player\sauthu_get_capture_rate($quality_tier);
                            $captured = (mt_rand(1, 100) <= $captureRate);

                            $expReward = intval($mapConfig['exp_reward']);
                            $ltReward = intval($mapConfig['lt_reward']);
                            \player\changeexp($sid, $dblj, $expReward);
                            \player\changeyxb(1, $ltReward, $sid, $dblj);

                            $bestMap = max(intval($state['best_map']), $selectedMap);
                            $stmt = $dblj->prepare("UPDATE player_sauthu SET best_map = ?, total_fights = total_fights + 1, updated_at = NOW() WHERE sid = ?");
                            $stmt->execute(array($bestMap, $sid));

                            $captureMsg = '';
                            if ($captured) {
                                $capturedMonster = $monsters[mt_rand(0, count($monsters) - 1)];
                                \player\addchongwu_from_pet($sid, $capturedMonster['pet_config_id'], $quality_tier, $dblj);
                                $stmt = $dblj->prepare("UPDATE player_sauthu SET total_captures = total_captures + 1 WHERE sid = ?");
                                $stmt->execute(array($sid));
                                $qualityName = \player\sauthu_get_quality_name($quality_tier);
                                $captureMsg = '<div class="sth-capture success">Thu phục thành công: <b>'.sth_h($capturedMonster['name']).'</b> ['.$qualityName.']!</div>';
                            } else {
                                $captureMsg = '<div class="sth-capture fail">Thu phục thất bại! Yêu thú đã chạy trốn.</div>';
                            }

                            $drops = isset($mapConfig['drops']) ? $mapConfig['drops'] : array();
                            $dropMsg = '';
                            foreach ($drops as $drop) {
                                if (mt_rand(1, 100) <= intval($drop['rate'])) {
                                    $dropAmt = max(1, intval($drop['amount']));
                                    sth_give_item($sid, intval($drop['djid']), $dropAmt, $dblj);
                                    $dropMsg .= ' +'.$dropAmt.' Bùa Thu Phục';
                                }
                            }

                            $notice = '<div class="sth-notice win">Thắng! Nhận +'.$expReward.' tu vi, +'.$ltReward.' Linh thạch.'.$dropMsg.$captureMsg.'</div>';
                        } else {
                            $stmt = $dblj->prepare("UPDATE player_sauthu SET total_fights = total_fights + 1, updated_at = NOW() WHERE sid = ?");
                            $stmt->execute(array($sid));
                            $notice = '<div class="sth-notice fail">Thất bại! Bạn đã bị đánh bại bởi yêu thú.</div>';
                        }

                        foreach ($battleLog as $log) {
                            $m = $log['monster'];
                            $r = $log['result'];
                            $battleHtml .= '<div class="sth-round">';
                            $battleHtml .= '<div class="sth-round-head">'.sth_h($m['name']).' ('.strtoupper($m['element']).')</div>';
                            foreach ($r['rounds'] as $line) {
                                $battleHtml .= '<div class="sth-round-line">';
                                $battleHtml .= '<b>Hiệp '.$line['round'].'</b>';
                                $battleHtml .= '<span>Gây '.$line['damage'].' st';
                                if ($line['heal'] > 0) $battleHtml .= ', hồi '.$line['heal'].' HP';
                                $battleHtml .= '</span>';
                                $battleHtml .= '<em>Yêu: '.number_format($line['monster_hp'], 0, ',', '.').' HP / Bạn: '.number_format($line['player_hp'], 0, ',', '.').' HP</em>';
                                $battleHtml .= '</div>';
                            }
                            $battleHtml .= '</div>';
                        }

                        $player = \player\getplayer($sid, $dblj);
$state = \player\sauthu_state($sid, $dblj);
                    }
                }
            }
        }
    }
}

$mapHtml = '';
if ($yeuthuConfig && isset($yeuthuConfig['maps'])) {
    for ($i = 1; $i <= 10; $i++) {
        $map = isset($yeuthuConfig['maps'][$i]) ? $yeuthuConfig['maps'][$i] : null;
        if (!$map) continue;

        $unlocked = ($i == 1) || ($state['best_map'] >= $i - 1);
        $tier = intval($map['quality_tier']);
        $tierNames = array(0 => 'Bình Thường', 1 => 'Ưu Tuấn', 2 => 'Trác Việt', 3 => 'Phi Phàm', 4 => 'Hoàn Mỹ', 5 => 'Nghịch Thiên');
        $tierColors = array(0 => '#00C000', 1 => '#1a80da', 2 => '#a08f0a', 3 => '#14b8b9', 4 => '#f16613', 5 => '#ec0909');
        $tierName = isset($tierNames[$tier]) ? $tierNames[$tier] : 'Binh Thuong';
        $tierColor = isset($tierColors[$tier]) ? $tierColors[$tier] : '#00C000';

        $enterCmd = $encode->encode("cmd=sauthu&canshu=enter&map=$i&sid=$sid");
        $buaId = intval($map['bua_id']);
        $buaCount = sth_get_player_item_count($sid, $buaId, $dblj);
        $mtCost = intval($map['mt_cost']);
        $mtHave = max(0, intval($player->uczb));
        $levelRange = isset($map['level_range']) ? $map['level_range'] : array(1, 999);
        $playerLevel = max(1, intval($player->ulv));
        $levelOk = ($playerLevel >= intval($levelRange[0]) && $playerLevel <= intval($levelRange[1]));

        $statusClass = $unlocked ? 'unlocked' : 'locked';
        $buaLtCost = isset($buaConfig[$buaId]) ? intval($buaConfig[$buaId]['lt_cost']) : 0;
        $showPayCmd = $encode->encode("cmd=sauthu&canshu=pay_bua&pay_bua_id=$buaId&sid=$sid");
        $buyBuaCmd = $encode->encode("cmd=sauthu&canshu=buy_bua&buy_bua_id=$buaId&sid=$sid");

        $mapHtml .= '<div class="sth-map-card '.$statusClass.'">';
        $mapHtml .= '<div class="sth-map-num">'.$i.'</div>';
        $mapHtml .= '<div class="sth-map-info">';
        $mapHtml .= '<div class="sth-map-name">'.sth_h($map['name']).'</div>';
        $mapHtml .= '<div class="sth-map-tier" style="color:'.$tierColor.'">'.$tierName.'</div>';
        $mapHtml .= '<div class="sth-map-req">Lv >= '.$levelRange[0].' | Bùa: '.$buaCount.'/1 | MT: '.$mtHave.'/'.$mtCost.'</div>';
        $mapHtml .= '</div>';
        $mapHtml .= '<div class="sth-map-btns">';
        $mapHtml .= '<a class="sth-btn enter" href="?cmd='.$enterCmd.'">Vào phó bản</a>';
        if ($buaLtCost > 0) {
            $mapHtml .= '<a class="sth-btn buy" href="?cmd='.$showPayCmd.'">Mua bùa ('.number_format($buaLtCost).' LT)</a>';
        }
        $mapHtml .= '</div>';
        $mapHtml .= '</div>';
    }
}

$safeName = sth_h($player->uname);
$bestText = intval($state['best_map']).' / 10';
$fightsText = intval($state['total_fights']);
$capturesText = intval($state['total_captures']);

$html = <<<HTML
<div class="sth-page">
    <section class="sth-hero" style="background:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url('./images/bg/phobansanyeuthu.png') center/cover no-repeat!important;padding:16px 18px!important;position:relative!important">
        <div class="tyt-pk-ribbon" onclick="document.getElementById('sth-guide-modal').classList.add('show')" style="cursor:pointer">HƯỚNG DẪN</div>
        <div class="sth-hero-content" style="line-height: 1.5;position:relative!important;z-index:2!important">
            <div class="sth-kicker" style="text-shadow:0 1px 6px rgba(0,0,0,.7)!important">Phó bản săn yêu thú</div>
            <h1 style="text-shadow:0 2px 10px rgba(0,0,0,.8)!important">Sào Thủ</h1>
            <p style="text-shadow:0 1px 4px rgba(0,0,0,.6)!important">Đi sâu vào rừng để tìm và thu phục yêu thú. Mỗi map cần Bùa Thu Phục và Ma Thạch để vào. Tiêu diệt hết yêu thú để có cơ hội thu phục.</p>
            <div class="sth-stats" style="padding:10px 16px!important;border-radius:12px!important;border:1px solid rgba(250,204,21,.32)!important;background:rgba(250,204,21,.08)!important;box-shadow:0 0 14px rgba(250,204,21,.10) inset!important;text-shadow:0 1px 4px rgba(0,0,0,.6)!important">
                <div class="sth-stat"><span style="color:#fde68a!important">Người chơi</span><b style="color:#fff7ad!important">$safeName</b></div>
                <div class="sth-stat"><span style="color:#fde68a!important">Best Map</span><b style="color:#fff7ad!important">$bestText</b></div>
                <div class="sth-stat"><span style="color:#fde68a!important">Lượt đánh</span><b style="color:#fff7ad!important">$fightsText</b></div>
                <div class="sth-stat"><span style="color:#fde68a!important">Thu phục</span><b style="color:#fff7ad!important">$capturesText</b></div>
            </div>
            $notice
        </div>
    </section>

    <div class="sth-layout">
        <section class="sth-panel">
            <div class="sth-kicker">Chọn map</div>
            <h2>Danh sách map</h2>
            <div class="sth-map-list">
                $mapHtml
            </div>
        </section>

        $battleHtml

    </div>
</div>

<!-- Hướng dẫn: popup mở từ nút góc banner (cùng kiểu "Thưởng Tầng" của Tỏa Yêu Tháp) -->
<div id="sth-guide-modal" class="vdd-reward-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="vdd-reward-popup" style="align-self:flex-start">
        <div class="vdd-reward-popup-head">
            <div class="vdd-reward-popup-icon">📜</div>
            <h3>Luật chơi</h3>
            <p>Thu phục yêu thú ở Sào Thú</p>
        </div>
        <div class="vdd-reward-popup-list">
            <div class="sth-info-grid" style="padding:10px 0">
                <div><span>Điều kiện</span><b>Có Bùa Thu Phục + đủ Ma Thạch</b></div>
                <div><span>Tỷ lệ thu phục</span><b>80% -> 5% (tăng cao = khó hơn)</b></div>
                <div><span>Thưởng</span><b>Tu vi + Linh thạch</b></div>
                <div><span>Giới hạn</span><b>Không giới hạn lượt chơi</b></div>
            </div>
        </div>
        <div class="vdd-reward-popup-foot">
            <a class="vdd-reward-popup-close" href="javascript:void(0)" onclick="document.getElementById('sth-guide-modal').classList.remove('show')">Đóng</a>
        </div>
    </div>
</div>

<style>
.sth-page{max-width:800px;margin:0 auto;padding:10px;font-family:inherit;color:#e0d6c2}
.sth-hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);border-radius:16px;padding:24px;margin-bottom:16px;border:1px solid rgba(255,255,255,.1)}
.sth-hero-content{position:relative;z-index:1}
.sth-kicker{color:#d8c7a6;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
.sth-hero h1{margin:0 0 8px;font-size:28px;font-weight:1000;color:#fff}
.sth-hero p{margin:0 0 16px;color:#a09880;font-size:13px;line-height:1.5}
.sth-stats{display:flex;gap:16px;flex-wrap:wrap}
.sth-stat{flex:1;min-width:100px;text-align:center}
@media(max-width:600px){.sth-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;max-width:320px}}
.sth-stat span{display:block;color:#d8c7a6;font-size:11px;font-weight:900}
.sth-stat b{display:block;margin-top:4px;color:#fff;font-size:16px;font-weight:1000}
.sth-notice{margin-top:12px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600}
.sth-notice.win{background:rgba(0,200,100,.15);border:1px solid rgba(0,200,100,.3);color:#00c864}
.sth-notice.fail{background:rgba(200,0,0,.15);border:1px solid rgba(200,0,0,.3);color:#f5d9d9}
.sth-layout{display:grid;grid-template-columns:1fr;gap:16px}
.sth-panel{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:16px}
.sth-panel h2{margin:0 0 12px;font-size:18px;font-weight:1000;color:#fff}
.sth-map-list{display:flex;flex-direction:column;gap:8px}
.sth-map-card{display:flex;align-items:center;gap:12px;padding:12px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px}
.sth-map-card.locked{opacity:.5}
.sth-map-num{width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.1);border-radius:8px;font-weight:1000;color:#d8c7a6;font-size:16px}
.sth-map-info{flex:1}
.sth-map-name{font-weight:1000;color:#fff;font-size:14px}
.sth-map-tier{font-size:12px;font-weight:700}
.sth-map-req{font-size:11px;color:#a09880;margin-top:2px}
.sth-btn{display:inline-block;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap}
.sth-btn.enter{background:linear-gradient(135deg,#f16613,#ec0909);color:#fff}
.sth-btn.buy{background:linear-gradient(135deg,#1a80da,#06b6d4);color:#fff;font-size:11px;padding:4px 10px}
.sth-map-btns{display:flex;flex-direction:column;gap:6px;flex-shrink:0}
.sth-round{margin-top:12px;padding:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px}
.sth-round-head{font-weight:1000;color:#d8c7a6;margin-bottom:6px}
.sth-round-line{display:flex;gap:8px;align-items:center;padding:4px 0;font-size:12px;border-bottom:1px solid rgba(255,255,255,.05)}
.sth-round-line:last-child{border:none}
.sth-round-line b{color:#fff;min-width:50px}
.sth-round-line span{flex:1;color:#a09880}
.sth-round-line em{color:#666;font-style:normal}
.sth-capture{margin-top:8px;padding:8px 12px;border-radius:8px;font-size:13px;font-weight:600}
.sth-capture.success{background:rgba(0,200,100,.15);border:1px solid rgba(0,200,100,.3);color:#00c864}
.sth-capture.fail{background:rgba(200,150,0,.15);border:1px solid rgba(200,150,0,.3);color:#c89600}
.sth-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.sth-info-grid div{padding:8px;background:rgba(255,255,255,.05);border-radius:8px}
.sth-info-grid span{display:block;color:#d8c7a6;font-size:11px;font-weight:900}
.sth-info-grid b{display:block;margin-top:4px;color:#fff;font-size:13px;font-weight:700}
@media(max-width:600px){.sth-stats{flex-direction:column;gap:8px}.sth-info-grid{grid-template-columns:1fr}}
.sth-pay-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:flex;justify-content:center;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
.sth-pay-modal{background:linear-gradient(180deg,#1a1a2e,#16213e);height: fit-content;margin-top: 10%;border:1px solid rgba(250,204,21,.3);border-radius:16px;padding:24px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.sth-pay-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.sth-pay-kicker{color:#facc15;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px}
.sth-pay-title{color:#fff;font-size:18px;font-weight:1000;margin-top:2px}
.sth-pay-close{color:#666;font-size:24px;text-decoration:none;line-height:1}
.sth-pay-close:hover{color:#fff}
.sth-pay-item{display:flex;align-items:center;gap:12px;padding:12px;background:rgba(255,255,255,.05);border-radius:10px;margin-bottom:16px}
.sth-pay-icon{font-size:28px}
.sth-pay-label{color:#a09880;font-size:11px;font-weight:900;text-transform:uppercase}
.sth-pay-name{color:#fff;font-size:15px;font-weight:1000}
.sth-pay-lines{margin-bottom:12px}
.sth-pay-lines div{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);font-size:13px}
.sth-pay-lines span{color:#a09880}
.sth-pay-lines strong{font-weight:1000}
.sth-pay-lines strong.gold{color:#facc15}
.sth-pay-lines strong.ok{color:#00c864}
.sth-pay-lines strong.fail{color:#c80000}
.sth-pay-status{text-align:center;padding:8px;border-radius:8px;font-size:12px;font-weight:700;margin-bottom:16px}
.sth-pay-status.ok{background:rgba(0,200,100,.1);color:#00c864}
.sth-pay-status.fail{background:rgba(200,0,0,.1);color:#c80000}
.sth-pay-actions{display:flex;gap:10px;justify-content:flex-end}
.sth-pay-cancel,.sth-pay-confirm{padding:8px 18px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer}
.sth-pay-cancel{background:rgba(255,255,255,.1);color:#a09880}
.sth-pay-cancel:hover{background:rgba(255,255,255,.2)}
.sth-pay-confirm{background:linear-gradient(135deg,#f16613,#ec0909);color:#fff}
.sth-pay-confirm:hover{filter:brightness(1.1)}
.sth-pay-confirm.disabled{background:rgba(255,255,255,.1);color:#666;cursor:not-allowed;pointer-events:none}
.sth-pay-unit{color:#a09880;font-size:12px;margin-top:2px}
.sth-pay-unit b{color:#facc15}
.sth-pay-qty{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:8px}
.sth-pay-qty>span{color:#a09880;font-size:13px}
.sth-pay-qty-btns{display:flex;align-items:center;gap:6px}
.sth-pay-qty-btn{width:32px;height:32px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;user-select:none;text-decoration:none}
.sth-pay-qty-btn:hover{background:rgba(255,255,255,.2)}
.sth-pay-qty-input{width:50px;height:32px;text-align:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;color:#fff;font-size:15px;font-weight:1000;outline:none;-moz-appearance:textfield}
.sth-pay-qty-input::-webkit-outer-spin-button,.sth-pay-qty-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
</style>
HTML;

echo $html;
echo $paymentModalHtml;
?>
