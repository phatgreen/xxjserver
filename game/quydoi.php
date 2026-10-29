<?php
/**
 * Quy Đổi Ma Thạch → Linh Thạch
 * 
 * Tỷ giá: 1,000 MT = 1,000,000 LT (1:1000)
 * Giới hạn: Tối đa 100,000 MT/lần, tối đa 5 lần/ngày
 */

if (!isset($sid) || $sid == '') {
    header("refresh:1;url=index.php");
    exit;
}

$dblj = $GLOBALS['dblj'];
$player = \player\getplayer($sid, $dblj);

if (!$player) {
    echo '<div class="tc-card" style="text-align:center;padding:40px 20px;">'
        . '<div style="font-size:48px;margin-bottom:12px">⚠️</div>'
        . '<div style="font-size:16px;font-weight:800;color:#94a3b8">Không tìm thấy nhân vật!</div>'
        . '</div>';
    exit;
}

// Tỷ giá
$EXCHANGE_RATE = 1000; // 1 MT = 1000 LT
$MAX_MT_PER_EXCHANGE = 100000; // Tối đa 100,000 MT/lần
$MAX_EXCHANGES_PER_DAY = 5; // Tối đa 5 lần/ngày

// Xử lý yêu cầu quy đổi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mtAmount = isset($_POST['mt_amount']) ? intval($_POST['mt_amount']) : 0;
    
    if ($mtAmount <= 0) {
        $error = "Số lượng MT không hợp lệ!";
    } elseif ($mtAmount > $MAX_MT_PER_EXCHANGE) {
        $error = "Tối đa " . number_format($MAX_MT_PER_EXCHANGE) . " MT/lần!";
    } elseif ($mtAmount > $player->uczb) {
        $error = "Bạn không đủ MT!";
    } else {
        $today = date('Y-m-d');
        // Thực hiện quy đổi — transaction để chống double-spend
        $ltAmount = $mtAmount * $EXCHANGE_RATE;

        $dblj->beginTransaction();
        try {
            // Check daily limit inside transaction
            $stmtCount = $dblj->prepare("SELECT COUNT(*) FROM exchange_log WHERE sid = ? AND exchange_date = ? FOR UPDATE");
            $stmtCount->execute(array($sid, $today));
            $exchangeCount = intval($stmtCount->fetchColumn());

            if ($exchangeCount >= $MAX_EXCHANGES_PER_DAY) {
                $dblj->rollBack();
                $error = "Bạn đã hết số lần quy đổi hôm nay! (Tối đa {$MAX_EXCHANGES_PER_DAY} lần)";
            } else {
                // Lock player row
                $stmtLock = $dblj->prepare("SELECT uczb FROM game1 WHERE sid = ? FOR UPDATE");
                $stmtLock->execute(array($sid));
                $lockedRow = $stmtLock->fetch(\PDO::FETCH_ASSOC);

                if (!$lockedRow || intval($lockedRow['uczb']) < $mtAmount) {
                    $dblj->rollBack();
                    $error = "Bạn không đủ MT!";
                } else {
                    // Trừ MT
                    $stmtMt = $dblj->prepare("UPDATE game1 SET uczb = uczb - ? WHERE sid = ? AND uczb >= ?");
                    $stmtMt->execute(array($mtAmount, $sid, $mtAmount));
                    if ($stmtMt->rowCount() === 0) {
                        $dblj->rollBack();
                        $error = "Lỗi khi trừ MT! Vui lòng thử lại.";
                    } else {
                        // Cộng LT
                        $stmtLt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?");
                        $stmtLt->execute(array($ltAmount, $sid));

                        // Ghi log
                        $stmtLog = $dblj->prepare("INSERT INTO exchange_log (sid, mt_amount, lt_amount, exchange_date, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmtLog->execute(array($sid, $mtAmount, $ltAmount, $today));

                        $dblj->commit();

                        $success = "Quy đổi thành công! " . number_format($mtAmount) . " MT → " . number_format($ltAmount) . " LT";

                        // Refresh player info
                        $player = \player\getplayer($sid, $dblj);
                    }
                }
            }
        } catch (Exception $e) {
            $dblj->rollBack();
            $error = "Lỗi hệ thống! Vui lòng thử lại.";
        }
    }
}

// Lấy thông tin hiển thị
$today = date('Y-m-d');
$stmt = $dblj->prepare("SELECT COUNT(*) FROM exchange_log WHERE sid = :sid AND exchange_date = :exchange_date");
$stmt->execute([':sid' => $sid, ':exchange_date' => $today]);
$exchangeCount = $stmt->fetchColumn();
$remainingExchanges = $MAX_EXCHANGES_PER_DAY - $exchangeCount;

$gonowmid = $encode->encode("cmd=gomid&sid=$sid");
?>
<style>
.qd-card{background:linear-gradient(135deg,rgba(15,23,62,.8),rgba(8,16,44,.9));border:1px solid rgba(125,211,252,.15);border-radius:16px;padding:20px;margin-bottom:12px}
.qd-header{text-align:center;margin-bottom:16px}
.qd-icon{font-size:48px;margin-bottom:8px}
.qd-title{font-size:22px;font-weight:1000;background:linear-gradient(90deg,#f97316,#eab308);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.qd-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;margin:4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)}
.qd-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0}
.qd-stat{background:rgba(255,255,255,.05);border-radius:10px;padding:10px;text-align:center}
.qd-stat-label{font-size:11px;color:#64748b;margin-bottom:4px}
.qd-stat-val{font-size:18px;font-weight:1000}
.qd-stat-val.mt{color:#f97316}
.qd-stat-val.lt{color:#38bdf8}
.qd-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.qd-row:last-child{border-bottom:0}
.qd-row-label{color:#94a3b8;font-size:14px}
.qd-row-val{color:#fff;font-weight:800;font-size:14px}
.qd-input{width:100%;padding:12px;border:1px solid rgba(255,255,255,.15);border-radius:10px;background:rgba(255,255,255,.08);color:#fff;font-size:16px;font-weight:800;outline:none;transition:border-color .2s}
.qd-input:focus{border-color:#f97316}
.qd-input::placeholder{color:rgba(255,255,255,.4);font-weight:400}
.qd-quick{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-top:10px}
.qd-quick-btn{padding:8px 4px;border:1px solid rgba(255,255,255,.15);border-radius:8px;background:rgba(255,255,255,.08);color:#fff;font-size:11px;font-weight:800;cursor:pointer;transition:all .2s;text-align:center}
.qd-quick-btn:hover{background:rgba(249,115,22,.25);border-color:#f97316}
.qd-preview{background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);border-radius:10px;padding:16px;margin-top:16px;text-align:center}
.qd-preview-label{font-size:12px;color:#94a3b8;margin-bottom:6px}
.qd-preview-val{font-size:28px;font-weight:1000;color:#38bdf8;text-shadow:0 0 20px rgba(56,189,248,.4)}
.qd-btn{width:100%;padding:14px;border:none;border-radius:10px;font-size:16px;font-weight:1000;cursor:pointer;transition:all .2s;text-transform:uppercase;letter-spacing:1px}
.qd-btn-primary{background:linear-gradient(135deg,#f97316,#eab308);color:#fff;box-shadow:0 0 20px rgba(249,115,22,.3)}
.qd-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(249,115,22,.4)}
.qd-btn-primary:disabled{background:linear-gradient(135deg,#475569,#334155);color:#94a3b8;cursor:not-allowed;transform:none;box-shadow:none}
.qd-rate{background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.2);border-radius:10px;padding:12px;margin-top:12px;text-align:center}
.qd-rate-title{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
.qd-rate-val{font-size:20px;font-weight:1000;color:#f97316}
.qd-alert{padding:12px 16px;border-radius:10px;margin-bottom:12px;font-size:14px;font-weight:600}
.qd-alert-error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#ef4444}
.qd-alert-success{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#22c55e}
.qd-back{display:block;text-align:center;color:#64748b;text-decoration:none;font-size:14px;margin-top:16px;padding:10px;border-radius:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);transition:all .2s}
.qd-back:hover{color:#fff;background:rgba(255,255,255,.1)}
</style>

<div class="qd-card">
    <div class="qd-header">
        <div class="qd-icon">💱</div>
        <div class="qd-title">Quy Đổi Ma Thạch</div>
        <div class="qd-badge" style="color:#f97316;border-color:rgba(249,115,22,.3)">1 MT = <?= number_format($EXCHANGE_RATE) ?> LT</div>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="qd-alert qd-alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="qd-alert qd-alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="qd-card">
    <div style="font-weight:800;margin-bottom:8px;color:#c0a050"><img class=money-ico src=./images/daoju/linh_thach.png alt> Tài Sản</div>
    <div class="qd-grid">
        <div class="qd-stat">
            <div class="qd-stat-label">Ma Thạch</div>
            <div class="qd-stat-val mt"><?= number_format($player->uczb) ?></div>
        </div>
        <div class="qd-stat">
            <div class="qd-stat-label">Linh Thạch</div>
            <div class="qd-stat-val lt"><?= number_format($player->uyxb) ?></div>
        </div>
    </div>
    <div class="qd-row">
        <span class="qd-row-label">Đã quy đổi hôm nay</span>
        <span class="qd-row-val"><?= $exchangeCount ?> / <?= $MAX_EXCHANGES_PER_DAY ?></span>
    </div>
    <div class="qd-row">
        <span class="qd-row-label">Còn lại</span>
        <span class="qd-row-val" style="color:<?= $remainingExchanges > 0 ? '#22c55e' : '#ef4444' ?>"><?= $remainingExchanges ?> lần</span>
    </div>
</div>

<div class="qd-card">
    <div style="font-weight:800;margin-bottom:12px;color:#6ee7b7">🔄 Thực hiện quy đổi</div>
    <form method="POST" id="exchangeForm">
        <input type="number" class="qd-input" name="mt_amount" id="mtAmount" 
               min="1" max="<?= $MAX_MT_PER_EXCHANGE ?>" 
               placeholder="Nhập số Ma Thạch..."
               value="<?= isset($_POST['mt_amount']) ? intval($_POST['mt_amount']) : '' ?>">
        
        <div class="qd-quick">
            <button type="button" class="qd-quick-btn" onclick="setAmount(1000)">1K</button>
            <button type="button" class="qd-quick-btn" onclick="setAmount(5000)">5K</button>
            <button type="button" class="qd-quick-btn" onclick="setAmount(10000)">10K</button>
            <button type="button" class="qd-quick-btn" onclick="setAmount(50000)">50K</button>
            <button type="button" class="qd-quick-btn" onclick="setMax()" style="background:linear-gradient(135deg,rgba(249,115,22,.3),rgba(234,179,8,.3))">MAX</button>
        </div>
        
        <div class="qd-preview">
            <div class="qd-preview-label">Bạn sẽ nhận được</div>
            <div class="qd-preview-val" id="previewAmount">0 LT</div>
        </div>
        
        <button type="submit" class="qd-btn qd-btn-primary" id="exchangeBtn" 
                <?= ($remainingExchanges <= 0 || $player->uczb <= 0) ? 'disabled' : '' ?>>
            ⚡ Quy Đổi Ngay
        </button>
    </form>
</div>

<a href="?cmd=<?= $gonowmid ?>" class="qd-back">← Quay lại Đại Sảnh</a>

<script>
const EXCHANGE_RATE = <?= $EXCHANGE_RATE ?>;
const MAX_MT = <?= min($MAX_MT_PER_EXCHANGE, $player->uczb) ?>;

function setAmount(amount) {
    document.getElementById('mtAmount').value = amount;
    updatePreview();
}

function setMax() {
    document.getElementById('mtAmount').value = MAX_MT;
    updatePreview();
}

function updatePreview() {
    const mtAmount = parseInt(document.getElementById('mtAmount').value) || 0;
    const ltAmount = mtAmount * EXCHANGE_RATE;
    document.getElementById('previewAmount').textContent = ltAmount.toLocaleString() + ' LT';
}

document.getElementById('mtAmount').addEventListener('input', updatePreview);

document.getElementById('exchangeForm').addEventListener('submit', function(e) {
    const mtAmount = parseInt(document.getElementById('mtAmount').value) || 0;
    if (mtAmount <= 0) {
        e.preventDefault();
        alert('Vui lòng nhập số MT hợp lệ!');
        return;
    }
    if (mtAmount > MAX_MT) {
        e.preventDefault();
        alert('Số MT vượt quá giới hạn!');
        return;
    }
    if (!confirm('Xác nhận quy đổi ' + mtAmount.toLocaleString() + ' MT → ' + (mtAmount * EXCHANGE_RATE).toLocaleString() + ' LT?')) {
        e.preventDefault();
    }
});

updatePreview();
</script>
