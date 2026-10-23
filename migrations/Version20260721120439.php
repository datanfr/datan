<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721120439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial datan : depute, groupe (GP), parti (PARPOL), scrutin, vote.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE depute (id INT AUTO_INCREMENT NOT NULL, groupe_id INT DEFAULT NULL, parti_id INT DEFAULT NULL, mp_id VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, cat_soc_pro SMALLINT DEFAULT NULL, age SMALLINT DEFAULT NULL, date_fin DATETIME DEFAULT NULL, cause_fin VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_3CFB37D07A45358C (groupe_id), INDEX IDX_3CFB37D0712547C6 (parti_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, libelle_abrev VARCHAR(255) DEFAULT NULL, libelle_abrege VARCHAR(255) DEFAULT NULL, couleur VARCHAR(32) DEFAULT NULL, position_politique VARCHAR(64) DEFAULT NULL, legislature SMALLINT DEFAULT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_4B98C21539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE parti (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, libelle_abrev VARCHAR(255) DEFAULT NULL, couleur VARCHAR(32) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_94225E84539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE scrutin (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, numero INT DEFAULT NULL, legislature SMALLINT DEFAULT NULL, date_scrutin DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', titre LONGTEXT DEFAULT NULL, objet LONGTEXT DEFAULT NULL, sort_code VARCHAR(64) DEFAULT NULL, sort_libelle VARCHAR(255) DEFAULT NULL, type_vote VARCHAR(255) DEFAULT NULL, code_type_vote VARCHAR(32) DEFAULT NULL, demandeur VARCHAR(255) DEFAULT NULL, nombre_votants INT DEFAULT NULL, suffrages_exprimes INT DEFAULT NULL, nombre_pour INT DEFAULT NULL, nombre_contre INT DEFAULT NULL, nombre_abstentions INT DEFAULT NULL, nombre_non_votants INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_5CB2D700539B0606 (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, scrutin_id INT NOT NULL, position VARCHAR(20) NOT NULL, par_delegation TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5A10856451A07B9C (depute_id), INDEX IDX_5A1085648D574414 (scrutin_id), UNIQUE INDEX uniq_depute_scrutin (depute_id, scrutin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE depute ADD CONSTRAINT FK_3CFB37D07A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE depute ADD CONSTRAINT FK_3CFB37D0712547C6 FOREIGN KEY (parti_id) REFERENCES parti (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote ADD CONSTRAINT FK_5A10856451A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote ADD CONSTRAINT FK_5A1085648D574414 FOREIGN KEY (scrutin_id) REFERENCES scrutin (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE depute DROP FOREIGN KEY FK_3CFB37D07A45358C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE depute DROP FOREIGN KEY FK_3CFB37D0712547C6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote DROP FOREIGN KEY FK_5A10856451A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote DROP FOREIGN KEY FK_5A1085648D574414
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE depute
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE parti
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE scrutin
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vote
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
