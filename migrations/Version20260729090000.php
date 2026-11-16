<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Parité de la fiche député avec datan.fr :
 *
 * - `depute.date_naissance` / `ville_naissance` — le paragraphe d'ouverture de la
 *   bio (« né le 25 septembre 1989 à Arras ») les affiche ; l'état civil vient du
 *   dépôt d'acteurs des Tricoteuses (`app:import:acteurs`).
 * - `profession_foi` — les documents de campagne du bloc « Ses professions de
 *   foi » ; table vide tant que `app:import:professions-foi` n'a pas été rejouée
 *   contre la vraie base de production (le backup public la livre vide).
 */
final class Version20260729090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Naissance du député (bio) et table profession_foi de la fiche.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE depute ADD date_naissance DATE DEFAULT NULL, ADD ville_naissance VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE profession_foi (id INT AUTO_INCREMENT NOT NULL, mp_id VARCHAR(32) NOT NULL, election_id INT NOT NULL, fichier VARCHAR(255) NOT NULL, tour SMALLINT NOT NULL, UNIQUE INDEX uniq_profession_foi (mp_id, election_id, tour), INDEX idx_profession_foi_mp (mp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE profession_foi');
        $this->addSql('ALTER TABLE depute DROP date_naissance, DROP ville_naissance');
    }
}
