<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Le maire de chaque commune, en trois colonnes sur `commune`.
 *
 * C'est le dernier paragraphe manquant des fiches de ville — « Le maire de
 * Ambérieu-en-Bugey est Daniel Fabre. » — et la ligne « 🏛️ Maire » de
 * l'encadré des pages de résultats. La donnée vient de `cities_mayors` de
 * l'application d'origine, longtemps réputée vide : elle l'était dans la copie
 * de travail, mais le jeu public `datan.fr/assets/dataset_backup` la sert
 * complète (34 874 communes). Alimentée par `app:import:communes --maires`.
 *
 * Trois colonnes plutôt qu'une table : le site n'affiche que le nom, et la
 * civilité ne sert qu'à accorder « le / la maire ».
 */
final class Version20260808090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'commune : maire (prénom, nom, civilité).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commune
            ADD maire_prenom VARCHAR(100) DEFAULT NULL,
            ADD maire_nom VARCHAR(100) DEFAULT NULL,
            ADD maire_civilite VARCHAR(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commune
            DROP maire_prenom,
            DROP maire_nom,
            DROP maire_civilite');
    }
}
