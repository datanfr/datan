<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Comptes lecteurs et fin du flux de compte député.
 *
 * Trois changements portés ensemble, tous au service des espaces /register,
 * /password et /mon-compte :
 *  - utilisateur.code_postal : le `zipcode` des lecteurs de l'origine ;
 *  - demande_compte_depute.token : le jeton d'activation restitué à l'approbation,
 *    que `/register/{token}` résout (l'ancien `users_mp_link`) ;
 *  - reinitialisation_mot_de_passe : la table des jetons de « mot de passe oublié »
 *    (l'ancienne `password_resets`), valables une heure.
 */
final class Version20260724190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Comptes lecteurs : code_postal, jeton d\'activation député, table de réinitialisation de mot de passe.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD code_postal VARCHAR(10) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_compte_depute ADD token VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_demande_token ON demande_compte_depute (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reinitialisation_mot_de_passe (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, token VARCHAR(100) NOT NULL, cree_le DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_reinitialisation_token (token), INDEX IDX_reinitialisation_utilisateur (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reinitialisation_mot_de_passe ADD CONSTRAINT FK_reinitialisation_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE reinitialisation_mot_de_passe DROP FOREIGN KEY FK_reinitialisation_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reinitialisation_mot_de_passe
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_demande_token ON demande_compte_depute
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_compte_depute DROP token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP code_postal
        SQL);
    }
}
