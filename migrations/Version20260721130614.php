<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721130614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3CFB37D055BFF85F ON depute (mp_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_depute_scrutin ON vote
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote ADD vote_type VARCHAR(32) DEFAULT 'decompteNominatif' NOT NULL, ADD cause_position VARCHAR(10) DEFAULT NULL, CHANGE position position VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_depute_scrutin_type ON vote (depute_id, scrutin_id, vote_type)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_3CFB37D055BFF85F ON depute
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_depute_scrutin_type ON vote
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote DROP vote_type, DROP cause_position, CHANGE position position VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_depute_scrutin ON vote (depute_id, scrutin_id)
        SQL);
    }
}
