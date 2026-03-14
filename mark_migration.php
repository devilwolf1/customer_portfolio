<?php
$host = '127.0.0.1';
$db = 'ecommerce_store';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->exec("INSERT IGNORE INTO doctrine_migration_versions (version, executed_at) VALUES ('DoctrineMigrations\\\\Version20250107163000', NOW())");
    echo "Migration marked as executed\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
