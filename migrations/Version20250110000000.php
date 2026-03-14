<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration for Order and OrderItem entities
 */
final class Version20250110000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Order and OrderItem entities with relationships';
    }

    public function up(Schema $schema): void
    {
        // Create order table
        $this->addSql('CREATE TABLE `order` (
            id INT AUTO_INCREMENT NOT NULL,
            customer_id INT NOT NULL,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            total NUMERIC(10, 2) NOT NULL DEFAULT \'0.00\',
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            CONSTRAINT FK_F5299398_9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create order_items table
        $this->addSql('CREATE TABLE order_items (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            quantity INT NOT NULL,
            line_total NUMERIC(10, 2) NOT NULL DEFAULT \'0.00\',
            PRIMARY KEY(id),
            CONSTRAINT FK_9174FF8CF37F2B53 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE,
            CONSTRAINT FK_9174FF8C4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create indexes for better query performance
        $this->addSql('CREATE INDEX IDX_F5299398_9395C3F3 ON `order` (customer_id)');
        $this->addSql('CREATE INDEX IDX_9174FF8CF37F2B53 ON order_items (order_id)');
        $this->addSql('CREATE INDEX IDX_9174FF8C4584665A ON order_items (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE `order`');
    }
}
