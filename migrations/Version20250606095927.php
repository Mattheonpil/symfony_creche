<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606095927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD lundi_a TIME DEFAULT NULL, ADD lundi_d TIME DEFAULT NULL, ADD mardi_a TIME DEFAULT NULL, ADD mardi_d TIME DEFAULT NULL, ADD mercredi_a TIME DEFAULT NULL, ADD mercredi_d TIME DEFAULT NULL, ADD jeudi_a TIME DEFAULT NULL, ADD jeudi_d TIME DEFAULT NULL, ADD vendredi_a TIME DEFAULT NULL, ADD vendredi_d TIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP lundi_a, DROP lundi_d, DROP mardi_a, DROP mardi_d, DROP mercredi_a, DROP mercredi_d, DROP jeudi_a, DROP jeudi_d, DROP vendredi_a, DROP vendredi_d
        SQL);
    }
}
