<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Comptes rendus des séances publiques (compte_rendu, cr_section, cr_parole)
 * et référence de séance sur le scrutin.
 *
 * Ces tables portent les débats de l'Assemblée — les morceaux de discours sur
 * lesquels s'appuie le brouillon de décryptage généré par IA. Le lien avec un
 * vote passe par la séance : scrutin.seance_ref = compte_rendu.seance_ref.
 * Source : dépôt Tricoteuses Comptes_Rendus_Seances_XVII_nettoye.
 */
final class Version20260724120739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Comptes rendus de séance (débats) et seance_ref sur le scrutin, pour le brouillon de décryptage par IA.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE compte_rendu (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(60) NOT NULL, seance_ref VARCHAR(60) NOT NULL, session_ref VARCHAR(30) DEFAULT NULL, legislature SMALLINT DEFAULT NULL, date_seance DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', titre_journee VARCHAR(255) DEFAULT NULL, session VARCHAR(255) DEFAULT NULL, version VARCHAR(20) DEFAULT NULL, cree_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modifie_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_D39E69D2539B0606 (uid), INDEX idx_compte_rendu_seance_ref (seance_ref), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cr_parole (id INT AUTO_INCREMENT NOT NULL, compte_rendu_id INT NOT NULL, section_ordre INT DEFAULT NULL, ordre_absolu_seance INT NOT NULL, code_grammaire VARCHAR(60) DEFAULT NULL, code_style VARCHAR(40) DEFAULT NULL, role_debat VARCHAR(20) DEFAULT NULL, orateur_nom VARCHAR(150) DEFAULT NULL, acteur_ref VARCHAR(20) DEFAULT NULL, depute_id INT DEFAULT NULL, texte MEDIUMTEXT NOT NULL, INDEX idx_cr_parole_cr_ordre (compte_rendu_id, ordre_absolu_seance), INDEX idx_cr_parole_section (compte_rendu_id, section_ordre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cr_section (id INT AUTO_INCREMENT NOT NULL, compte_rendu_id INT NOT NULL, parent_ordre INT DEFAULT NULL, ordre_absolu_seance INT NOT NULL, nivpoint SMALLINT DEFAULT NULL, valeur_ptsodj INT DEFAULT NULL, code_grammaire VARCHAR(60) DEFAULT NULL, titre LONGTEXT DEFAULT NULL, INDEX idx_cr_section_cr_ordre (compte_rendu_id, ordre_absolu_seance), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin ADD seance_ref VARCHAR(60) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE compte_rendu
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cr_parole
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cr_section
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin DROP seance_ref
        SQL);
    }
}
