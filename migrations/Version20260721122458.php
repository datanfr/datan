<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721122458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Domaine décryptage : decryptage (votes_datan), categorie (fields), lecture (readings).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(64) NOT NULL, libelle LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE decryptage (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, lecture_id INT DEFAULT NULL, scrutin_id INT DEFAULT NULL, legislature SMALLINT NOT NULL, vote_numero INT NOT NULL, vote_id VARCHAR(50) DEFAULT NULL, title LONGTEXT NOT NULL, slug VARCHAR(160) NOT NULL, description LONGTEXT NOT NULL, state VARCHAR(20) DEFAULT 'draft' NOT NULL, created_by VARCHAR(16) DEFAULT NULL, modified_by VARCHAR(16) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_67322C60BCF5E72D (categorie_id), INDEX IDX_67322C6035E32FCD (lecture_id), INDEX IDX_67322C608D574414 (scrutin_id), INDEX idx_state (state), UNIQUE INDEX uniq_legislature_vote_numero (legislature, vote_numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE lecture (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD CONSTRAINT FK_67322C60BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD CONSTRAINT FK_67322C6035E32FCD FOREIGN KEY (lecture_id) REFERENCES lecture (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD CONSTRAINT FK_67322C608D574414 FOREIGN KEY (scrutin_id) REFERENCES scrutin (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP FOREIGN KEY FK_67322C60BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP FOREIGN KEY FK_67322C6035E32FCD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP FOREIGN KEY FK_67322C608D574414
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE decryptage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE lecture
        SQL);
    }
}
