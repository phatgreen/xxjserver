<?php
// error_reporting(0);

$sqlname = 'root';
$sqlpass = 'www.aae.ink';
$dbhost  = 'localhost';
$dbname  = 'game';

function create_pdo_connection($dbhost, $dbname, $sqlname, $sqlpass)
{
    $dsn = "mysql:host=$dbhost;dbname=$dbname";

    $pdo = new PDO($dsn, $sqlname, $sqlpass, array(
        PDO::ATTR_PERSISTENT       => true,
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_WARNING,
        PDO::ATTR_EMULATE_PREPARES => false
    ));

    $pdo->exec("SET NAMES utf8mb4");

    return $pdo;
}

try {
    $dblj = create_pdo_connection($dbhost, $dbname, $sqlname, $sqlpass);

    // Test connection cũ. Nếu chết thì tạo lại.
    $dblj->query("SELECT 1");

} catch (Exception $e) {
    try {
        $dblj = create_pdo_connection($dbhost, $dbname, $sqlname, $sqlpass);
    } catch (Exception $e2) {
        die('DB CONNECT ERROR: ' . $e2->getMessage());
    }
}
?>