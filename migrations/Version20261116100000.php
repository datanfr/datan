<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Récupération du legacy :
 *  - `utilisateur.id_origine` : le `users.id` du legacy, par lequel ses tables
 *    désignent leurs auteurs (`votes_datan.created_by`, `newsletter.id_user`) ;
 *    unique, nul pour un compte né dans cette application ;
 *  - `recuperation_legacy` : journal des récupérations, dont la ligne
 *    `bascule` interdit tout rejeu une fois l'application devenue la source.
 */
final class Version20261116100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Récupération du legacy : utilisateur.id_origine, journal recuperation_legacy.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD id_origine INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_utilisateur_id_origine ON utilisateur (id_origine)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recuperation_legacy (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(20) NOT NULL, lancee_le DATETIME NOT NULL, terminee_le DATETIME DEFAULT NULL, dossier VARCHAR(255) DEFAULT NULL, bilan JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE recuperation_legacy
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_utilisateur_id_origine ON utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP id_origine
        SQL);
    }
}
