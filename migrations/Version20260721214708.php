<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721214708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index couvrant pour les moyennes de cohésion et de participation par groupe.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_groupe_decompte ON vote_groupe (groupe_id, nombre_pours, nombre_contres, nombre_abstentions, nombre_membres_groupe)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_groupe_decompte ON vote_groupe
        SQL);
    }
}
