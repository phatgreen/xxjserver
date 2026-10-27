<?php
/**
 * Migration: Fix boss_attack quest data with proper UTF-8 encoding
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=game;charset=utf8mb4', 'root', 'www.aae.ink', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec("SET NAMES utf8mb4");

// Xóa dữ liệu cũ bị lỗi encoding
$pdo->exec("DELETE FROM club_quest_config WHERE quest_type = 'boss_attack'");

// Insert lại với đúng encoding
$stmt = $pdo->prepare("INSERT INTO club_quest_config (quest_type, quest_tier, quest_name, quest_desc, target_value, reward_exp, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");

$data = [
    ['boss_attack', 1, 'Tấn Công Boss (Dễ)', 'Tấn công Boss Tông Môn 1 lần', 1, 200, 4],
    ['boss_attack', 2, 'Tán Công Boss (Trung)', 'Tấn công Boss Tông Môn 3 lần', 3, 400, 41],
    ['boss_attack', 3, 'Tấn Công Boss (Khó)', 'Tấn công Boss Tông Môn 5 lần', 5, 800, 42],
];

foreach ($data as $row) {
    $stmt->execute($row);
}

// Verify
$rows = $pdo->query("SELECT id, quest_type, quest_tier, quest_name, quest_desc, target_value, reward_exp FROM club_quest_config WHERE quest_type = 'boss_attack' ORDER BY quest_tier")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID={$r['id']} | {$r['quest_type']} T{$r['quest_tier']} | {$r['quest_name']} | {$r['quest_desc']} | target={$r['target_value']} | reward={$r['reward_exp']}\n";
}

echo "\nDone! Encoding fix applied.\n";
