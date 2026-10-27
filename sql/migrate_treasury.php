<?php
require_once __DIR__ . '/../db.php';
try {
    $dblj->exec('ALTER TABLE club_player_quest ADD COLUMN crafted_items TEXT DEFAULT NULL');
    echo "Migration OK: crafted_items column added\n";
} catch (Exception $e) {
    echo "Migration result: " . $e->getMessage() . "\n";
}
?>
