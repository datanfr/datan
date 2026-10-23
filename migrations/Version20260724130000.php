<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Parrainages de l'élection présidentielle (table parrainage) : récupération de
 * la base de production pour la page /parrainages-2022.
 */
final class Version20260724130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Parrainages présidentielle (parrainage) : récupération de la production.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE parrainage (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, civilite VARCHAR(10) NOT NULL, nom VARCHAR(75) NOT NULL, prenom VARCHAR(75) NOT NULL, mandat VARCHAR(100) NOT NULL, circonscription LONGTEXT DEFAULT NULL, departement VARCHAR(100) DEFAULT NULL, candidat VARCHAR(100) NOT NULL, date_publication DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', annee SMALLINT NOT NULL, mp_id VARCHAR(35) DEFAULT NULL, date_maj DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', INDEX idx_parrainage_mp (mp_id), INDEX idx_parrainage_annee (annee), UNIQUE INDEX uniq_parrainage_source (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE parrainage
        SQL);
    }
}
