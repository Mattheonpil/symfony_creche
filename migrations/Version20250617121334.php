<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617121334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE recovery_child (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, recovery_id INT DEFAULT NULL, relation VARCHAR(50) NOT NULL, is_responsable TINYINT(1) NOT NULL, INDEX IDX_36E99053DD62C21B (child_id), INDEX IDX_36E990534D86A1FF (recovery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child ADD CONSTRAINT FK_36E99053DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child ADD CONSTRAINT FK_36E990534D86A1FF FOREIGN KEY (recovery_id) REFERENCES recovery (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child DROP FOREIGN KEY FK_36E99053DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child DROP FOREIGN KEY FK_36E990534D86A1FF
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recovery_child
        SQL);
    }
}
