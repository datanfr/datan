<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * `dossier_acteur` : les initiateurs et les rapporteurs d'un dossier législatif
 * (`dossiers_acteurs` de l'application d'origine).
 *
 * C'est ce qui manquait aux deux variantes du bloc auteur de la page de vote
 * servies sur les scrutins **sans** amendement — « L'auteur de la proposition de
 * loi » et « Le rapporteur ». La donnée est dans les mêmes fichiers de dossier
 * des Tricoteuses que `dossier.commission_fond` : aucun scraping, aucune source
 * nouvelle. Alimentée par `app:import:dossiers-acteurs`.
 */
final class Version20260805120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'dossier_acteur : initiateurs et rapporteurs des dossiers législatifs.';
    }

    public function up(Schema $schema): void
    {
        // `etape` est NOT NULL DEFAULT '' et non nullable : elle entre dans la
        // clé unique, et MariaDB admet autant de NULL qu'on veut dans un index
        // unique — un initiateur, qui n'a pas d'étape, se dédoublerait à chaque
        // réexécution de l'import.
        $this->addSql('CREATE TABLE dossier_acteur (
            id INT AUTO_INCREMENT NOT NULL,
            dossier_id INT NOT NULL,
            legislature SMALLINT DEFAULT NULL,
            role VARCHAR(20) NOT NULL,
            type VARCHAR(40) NOT NULL,
            ref VARCHAR(30) NOT NULL,
            etape VARCHAR(30) DEFAULT \'\' NOT NULL,
            mandat_ref VARCHAR(30) DEFAULT NULL,
            UNIQUE INDEX uniq_dossier_acteur (dossier_id, role, type, ref, etape),
            INDEX idx_dossier_acteur_role (dossier_id, role),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dossier_acteur');
    }
}
