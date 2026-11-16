<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721144233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE vote_groupe (id INT AUTO_INCREMENT NOT NULL, scrutin_id INT NOT NULL, groupe_id INT NOT NULL, nombre_membres_groupe INT NOT NULL, position_majoritaire VARCHAR(20) DEFAULT NULL, nombre_pours INT NOT NULL, nombre_contres INT NOT NULL, nombre_abstentions INT NOT NULL, non_votants INT NOT NULL, non_votants_volontaires INT NOT NULL, INDEX IDX_85EFE7B8D574414 (scrutin_id), INDEX IDX_85EFE7B7A45358C (groupe_id), UNIQUE INDEX uniq_scrutin_groupe (scrutin_id, groupe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote_groupe ADD CONSTRAINT FK_85EFE7B8D574414 FOREIGN KEY (scrutin_id) REFERENCES scrutin (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote_groupe ADD CONSTRAINT FK_85EFE7B7A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vote_groupe DROP FOREIGN KEY FK_85EFE7B8D574414
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote_groupe DROP FOREIGN KEY FK_85EFE7B7A45358C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vote_groupe
        SQL);
    }
}
