<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526094603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, day VARCHAR(10) NOT NULL, week INT NOT NULL, is_weekend TINYINT(1) NOT NULL, is_closed TINYINT(1) NOT NULL, closure_justification VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE child (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, birth_date DATE NOT NULL, registration_date DATETIME NOT NULL, unsubscription_date DATETIME DEFAULT NULL, allergy VARCHAR(255) DEFAULT NULL, medical_specificity LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, calendar_id INT DEFAULT NULL, child_id INT DEFAULT NULL, date DATE DEFAULT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, meal TINYINT(1) NOT NULL, actual_arrival TIME DEFAULT NULL, actual_departure TIME DEFAULT NULL, absence TINYINT(1) NOT NULL, absence_justification VARCHAR(255) DEFAULT NULL, INDEX IDX_D499BFF6A40A2C8 (calendar_id), INDEX IDX_D499BFF6DD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recovery (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, phone VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recovery_child (recovery_id INT NOT NULL, child_id INT NOT NULL, INDEX IDX_36E990534D86A1FF (recovery_id), INDEX IDX_36E99053DD62C21B (child_id), PRIMARY KEY(recovery_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, mail VARCHAR(50) NOT NULL, phone VARCHAR(10) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_child (id INT AUTO_INCREMENT NOT NULL, child_id INT DEFAULT NULL, user_id INT DEFAULT NULL, relation VARCHAR(50) NOT NULL, INDEX IDX_C071AF71DD62C21B (child_id), INDEX IDX_C071AF71A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6A40A2C8 FOREIGN KEY (calendar_id) REFERENCES calendar (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child ADD CONSTRAINT FK_36E990534D86A1FF FOREIGN KEY (recovery_id) REFERENCES recovery (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child ADD CONSTRAINT FK_36E99053DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child ADD CONSTRAINT FK_C071AF71DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child ADD CONSTRAINT FK_C071AF71A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6A40A2C8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child DROP FOREIGN KEY FK_36E990534D86A1FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recovery_child DROP FOREIGN KEY FK_36E99053DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child DROP FOREIGN KEY FK_C071AF71DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child DROP FOREIGN KEY FK_C071AF71A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE child
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE planning
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recovery
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recovery_child
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_child
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
