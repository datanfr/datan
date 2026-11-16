<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Résultats des législatives par circonscription (resultat_circonscription,
 * participation_circonscription, partielle_legislative) pour le bloc « Son
 * élection » de la fiche d'un député ; récupération de la FAQ (faq_categorie,
 * faq_post) et du questionnaire (question_quiz) depuis la base de production.
 */
final class Version20260723094426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Résultats de circonscription (fiche député), FAQ et questionnaire : récupération de la production.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE faq_categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, ordre SMALLINT NOT NULL, UNIQUE INDEX uniq_faq_categorie_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE faq_post (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, reponse LONGTEXT NOT NULL, etat VARCHAR(15) DEFAULT NULL, ordre SMALLINT NOT NULL, cree_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modifie_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_faq_post_categorie (categorie_id), UNIQUE INDEX uniq_faq_post_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participation_circonscription (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, code_departement VARCHAR(3) NOT NULL, circonscription SMALLINT NOT NULL, tour SMALLINT NOT NULL, inscrits INT DEFAULT NULL, abstentions INT DEFAULT NULL, votants INT DEFAULT NULL, blancs INT DEFAULT NULL, nuls INT DEFAULT NULL, exprimes INT DEFAULT NULL, UNIQUE INDEX uniq_participation_circonscription (annee, code_departement, circonscription, tour), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE partielle_legislative (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, code_departement VARCHAR(3) NOT NULL, circonscription SMALLINT NOT NULL, tour SMALLINT NOT NULL, date_scrutin DATE NOT NULL COMMENT '(DC2Type:date_immutable)', nuance VARCHAR(5) DEFAULT NULL, candidat VARCHAR(255) NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, sexe VARCHAR(5) DEFAULT NULL, voix INT DEFAULT NULL, part_exprimes DOUBLE PRECISION DEFAULT NULL, elu SMALLINT NOT NULL, INDEX idx_partielle_depute (code_departement, circonscription, date_scrutin), UNIQUE INDEX uniq_partielle (code_departement, circonscription, tour, date_scrutin, candidat), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE question_quiz (id INT AUTO_INCREMENT NOT NULL, source_id SMALLINT NOT NULL, numero_quiz SMALLINT NOT NULL, legislature SMALLINT NOT NULL, scrutin_numero SMALLINT NOT NULL, titre VARCHAR(255) NOT NULL, explication LONGTEXT DEFAULT NULL, pour1 LONGTEXT DEFAULT NULL, pour2 LONGTEXT DEFAULT NULL, pour3 LONGTEXT DEFAULT NULL, contre1 LONGTEXT DEFAULT NULL, contre2 LONGTEXT DEFAULT NULL, contre3 LONGTEXT DEFAULT NULL, categorie_slug VARCHAR(100) DEFAULT NULL, categorie_nom VARCHAR(100) DEFAULT NULL, inverse SMALLINT NOT NULL, etat VARCHAR(15) DEFAULT NULL, cree_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modifie_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_question_quiz_source (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat_circonscription (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, code_departement VARCHAR(3) NOT NULL, circonscription SMALLINT NOT NULL, tour SMALLINT NOT NULL, nuance VARCHAR(5) DEFAULT NULL, candidat VARCHAR(255) NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, sexe VARCHAR(5) DEFAULT NULL, voix INT DEFAULT NULL, part_inscrits DOUBLE PRECISION DEFAULT NULL, part_exprimes DOUBLE PRECISION DEFAULT NULL, elu SMALLINT NOT NULL, INDEX idx_circonscription_depute (code_departement, circonscription, annee), UNIQUE INDEX uniq_circonscription (annee, code_departement, circonscription, tour, candidat), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE faq_post ADD CONSTRAINT FK_5F9404EBBCF5E72D FOREIGN KEY (categorie_id) REFERENCES faq_categorie (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE faq_post DROP FOREIGN KEY FK_5F9404EBBCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE faq_categorie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE faq_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation_circonscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE partielle_legislative
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE question_quiz
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat_circonscription
        SQL);
    }
}
