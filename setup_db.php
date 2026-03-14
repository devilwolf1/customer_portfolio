<?php
$host = '127.0.0.1';
$user = 'root';
$db = 'ecommerce_store';
$pdo = new PDO("mysql:host=$host", $user, '');
$pdo->exec("DROP DATABASE IF EXISTS $db");
$pdo->exec("CREATE DATABASE $db");
echo "Database recreated\n";

$pdo = new PDO("mysql:host=$host;dbname=$db", $user, '');
$pdo->exec("CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
$pdo->exec("CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B3BA5A5A12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
$pdo->exec("ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)");
echo "Tables created\n";

$pdo->exec("CREATE TABLE doctrine_migration_versions (version VARCHAR(191) NOT NULL, executed_at DATETIME DEFAULT NULL, execution_time INT DEFAULT NULL, PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
$pdo->exec("INSERT INTO doctrine_migration_versions (version, executed_at) VALUES ('DoctrineMigrations\\\\Version20250107163000', NOW())");
echo "Migration marked as executed\n";
