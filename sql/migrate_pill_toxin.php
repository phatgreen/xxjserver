<?php
$pdo = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if column already exists
$stmt = $pdo->query("SHOW COLUMNS FROM player_alchemist LIKE 'pill_toxin'");
if ($stmt->rowCount() === 0) {
    $pdo->exec("ALTER TABLE player_alchemist ADD COLUMN pill_toxin INT(11) DEFAULT 0 COMMENT 'Pill toxin from using pills'");
    echo "OK: pill_toxin column added\n";
} else {
    echo "SKIP: pill_toxin column already exists\n";
}

// Verify
$stmt = $pdo->query("SHOW COLUMNS FROM player_alchemist");
echo "\nCurrent columns:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
