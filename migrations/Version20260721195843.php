<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721195843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE fonction_groupe (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, groupe_id INT NOT NULL, code_qualite VARCHAR(50) NOT NULL, libelle_qualite VARCHAR(100) DEFAULT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_83D387D451A07B9C (depute_id), INDEX IDX_83D387D47A45358C (groupe_id), INDEX idx_groupe_qualite (groupe_id, code_qualite), UNIQUE INDEX uniq_depute_groupe_qualite_debut (depute_id, groupe_id, code_qualite, date_debut), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe ADD CONSTRAINT FK_83D387D451A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe ADD CONSTRAINT FK_83D387D47A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe DROP FOREIGN KEY FK_83D387D451A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe DROP FOREIGN KEY FK_83D387D47A45358C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fonction_groupe
        SQL);
    }
}
