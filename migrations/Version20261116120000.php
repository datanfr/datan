<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Municipales 2026 : résultats par commune et par tour, tels que les publie le
 * ministère de l'Intérieur, et article des communes pour les phrases de la page
 * de résultats.
 *
 * Deux tables plutôt qu'une : la participation (inscrits, votants…) ne vaut
 * qu'une fois par commune et par tour, quand une commune peut aligner jusqu'à
 * quinze listes. Elles s'alimentent par `app:import:municipales` ; `nom_de` par
 * `app:import:noms-communes` ; `candidature.ordre_saisie`, l'ordre où le site
 * énumère les députés candidats d'une commune, par `app:import:elections`.
 */
final class Version20261116120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Municipales 2026 : tables municipale_resultat et municipale_participation ; commune.nom_de ; candidature.ordre_saisie.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE municipale_resultat (
                id INT AUTO_INCREMENT NOT NULL,
                tour SMALLINT NOT NULL,
                code_commune VARCHAR(10) NOT NULL,
                numero_panneau SMALLINT NOT NULL,
                libelle_liste VARCHAR(255) DEFAULT NULL,
                voix INT DEFAULT NULL,
                rapport_inscrits NUMERIC(6, 2) DEFAULT NULL,
                rapport_exprimes NUMERIC(6, 2) DEFAULT NULL,
                code_nuance VARCHAR(10) DEFAULT NULL,
                sexe VARCHAR(1) DEFAULT NULL,
                nom VARCHAR(120) DEFAULT NULL,
                prenom VARCHAR(120) DEFAULT NULL,
                sieges SMALLINT DEFAULT NULL,
                qualifiee SMALLINT DEFAULT NULL,
                situation VARCHAR(12) DEFAULT NULL,
                fusion_role VARCHAR(10) DEFAULT NULL,
                fusion_tete VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX uniq_municipale_resultat (tour, code_commune, numero_panneau),
                INDEX idx_municipale_resultat_commune (code_commune, tour),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE municipale_participation (
                id INT AUTO_INCREMENT NOT NULL,
                tour SMALLINT NOT NULL,
                code_commune VARCHAR(10) NOT NULL,
                inscrits INT DEFAULT NULL,
                abstentions INT DEFAULT NULL,
                votants INT DEFAULT NULL,
                blancs INT DEFAULT NULL,
                nuls INT DEFAULT NULL,
                exprimes INT DEFAULT NULL,
                pct_saisis NUMERIC(6, 2) DEFAULT NULL,
                pourvu VARCHAR(5) DEFAULT NULL,
                UNIQUE INDEX uniq_municipale_participation (tour, code_commune),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql('ALTER TABLE commune ADD nom_de VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidature ADD ordre_saisie INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE municipale_resultat');
        $this->addSql('DROP TABLE municipale_participation');
        $this->addSql('ALTER TABLE commune DROP nom_de');
        $this->addSql('ALTER TABLE candidature DROP ordre_saisie');
    }
}
