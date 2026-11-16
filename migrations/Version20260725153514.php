<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Organes de l'Assemblée sans table dédiée et les mandats qui s'y exercent.
 *
 * À ce jour : les délégations du Bureau (`DELEGBUREAU`), seul type d'organe que
 * le legacy retient dans `mandat_secondaire` (avec COMPER et PARPOL, déjà portés
 * ailleurs) et qui n'avait pas de foyer chez nous. Elles alimentent l'écran
 * « Postes Assemblée » de l'espace de rédaction.
 *
 * Clés sur l'uid (organe et mandat) : jamais sur un libellé, que deux organes
 * homonymes de législatures différentes pourraient partager (CLAUDE.md).
 */
final class Version20260725153514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Organes hors table dédiée (délégations du Bureau) et leurs mandats.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE mandat_organe (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, organe_id INT NOT NULL, uid VARCHAR(50) NOT NULL, legislature SMALLINT DEFAULT NULL, code_qualite VARCHAR(100) DEFAULT NULL, libelle_qualite VARCHAR(255) DEFAULT NULL, nomin_principale SMALLINT DEFAULT 1 NOT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_CA9FE551539B0606 (uid), INDEX IDX_CA9FE55151A07B9C (depute_id), INDEX IDX_CA9FE551B5E5B09D (organe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE organe (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(50) NOT NULL, code_type VARCHAR(25) NOT NULL, libelle VARCHAR(255) NOT NULL, libelle_abrege VARCHAR(255) DEFAULT NULL, libelle_abrev VARCHAR(100) DEFAULT NULL, legislature SMALLINT DEFAULT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_E23012D0539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat_organe ADD CONSTRAINT FK_CA9FE55151A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat_organe ADD CONSTRAINT FK_CA9FE551B5E5B09D FOREIGN KEY (organe_id) REFERENCES organe (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat_organe DROP FOREIGN KEY FK_CA9FE55151A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat_organe DROP FOREIGN KEY FK_CA9FE551B5E5B09D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mandat_organe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE organe
        SQL);
    }
}
