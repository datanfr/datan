<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721190516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE amendement (id INT AUTO_INCREMENT NOT NULL, amendement_id VARCHAR(60) NOT NULL, legislature SMALLINT DEFAULT NULL, href VARCHAR(500) DEFAULT NULL, expose LONGTEXT DEFAULT NULL, resume_ia LONGTEXT DEFAULT NULL, titre_ia VARCHAR(255) DEFAULT NULL, resume_relu TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_DAF098C4F4C20978 (amendement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE dossier (id INT AUTO_INCREMENT NOT NULL, dossier_id VARCHAR(100) NOT NULL, legislature SMALLINT DEFAULT NULL, titre LONGTEXT DEFAULT NULL, titre_chemin VARCHAR(300) DEFAULT NULL, procedure_parlementaire LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_3D48E037611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE explication (id INT AUTO_INCREMENT NOT NULL, scrutin_id INT NOT NULL, depute_id INT NOT NULL, texte LONGTEXT NOT NULL, publiee TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_755EEB78D574414 (scrutin_id), INDEX IDX_755EEB751A07B9C (depute_id), UNIQUE INDEX uniq_scrutin_depute (scrutin_id, depute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE explication ADD CONSTRAINT FK_755EEB78D574414 FOREIGN KEY (scrutin_id) REFERENCES scrutin (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE explication ADD CONSTRAINT FK_755EEB751A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin ADD dossier_id INT DEFAULT NULL, ADD amendement_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin ADD CONSTRAINT FK_5CB2D700611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin ADD CONSTRAINT FK_5CB2D700F4C20978 FOREIGN KEY (amendement_id) REFERENCES amendement (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5CB2D700611C0C56 ON scrutin (dossier_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5CB2D700F4C20978 ON scrutin (amendement_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin DROP FOREIGN KEY FK_5CB2D700F4C20978
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin DROP FOREIGN KEY FK_5CB2D700611C0C56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE explication DROP FOREIGN KEY FK_755EEB78D574414
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE explication DROP FOREIGN KEY FK_755EEB751A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE amendement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE dossier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE explication
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5CB2D700611C0C56 ON scrutin
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5CB2D700F4C20978 ON scrutin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin DROP dossier_id, DROP amendement_id
        SQL);
    }
}
