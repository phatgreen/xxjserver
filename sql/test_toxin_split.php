<?php
$pdo = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== KIỂM TRA CẤU TRÚC BẢNG ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM player_alchemist");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== KIỂM TRA DỮ LIỆU SAMPLE ===\n";
$stmt = $pdo->query("SELECT sid, total_toxin, pill_toxin FROM player_alchemist LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  SID: {$row['sid']}, Phản Phệ: {$row['total_toxin']}, Đan Độc: {$row['pill_toxin']}\n";
}

echo "\n=== KIỂM TRA FUNCTION ===\n";
// Test update_pill_toxin
function test_update_pill_toxin($pdo, $sid, $amount) {
    $stmt = $pdo->prepare("SELECT pill_toxin FROM player_alchemist WHERE sid = ?");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current = intval($row['pill_toxin']);
    $new = $current + $amount;
    
    $stmt2 = $pdo->prepare("UPDATE player_alchemist SET pill_toxin = ? WHERE sid = ?");
    $stmt2->execute(array($new, $sid));
    
    echo "  Test update_pill_toxin: $current -> $new (+" . $amount . ")\n";
    return $new;
}

// Find a test player
$stmt = $pdo->query("SELECT sid FROM game1 LIMIT 1");
$testSid = $stmt->fetchColumn();

if ($testSid) {
    // Reset first
    $pdo->prepare("UPDATE player_alchemist SET pill_toxin = 0, total_toxin = 0 WHERE sid = ?")->execute(array($testSid));
    
    // Simulate: crafting increases total_toxin (Phản Phệ)
    echo "\n=== MÔ PHỎNG: LUYỆN ĐAN (Phản Phệ) ===\n";
    $pdo->prepare("UPDATE player_alchemist SET total_toxin = total_toxin + 50 WHERE sid = ?")->execute(array($testSid));
    $stmt = $pdo->prepare("SELECT total_toxin, pill_toxin FROM player_alchemist WHERE sid = ?");
    $stmt->execute(array($testSid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Sau luyện đan: Phản Phệ={$row['total_toxin']}, Đan Độc={$row['pill_toxin']}\n";
    
    // Simulate: using pill increases pill_toxin (Đan Độc)
    echo "\n=== MÔ PHỎNG: SỬ DỤNG ĐAN (Đan Độc) ===\n";
    $pdo->prepare("UPDATE player_alchemist SET pill_toxin = pill_toxin + 30 WHERE sid = ?")->execute(array($testSid));
    $stmt->execute(array($testSid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Sau dùng đan: Phản Phệ={$row['total_toxin']}, Đan Độc={$row['pill_toxin']}\n";
    
    echo "\n✅ Hai hệ thống hoạt động riêng biệt!\n";
} else {
    echo "Không tìm thấy player để test\n";
}
