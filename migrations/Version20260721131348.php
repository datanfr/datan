<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721131348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_date_scrutin ON scrutin (date_scrutin)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_legislature_numero ON scrutin (legislature, numero)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_depute_type_position ON vote (depute_id, vote_type, position)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_scrutin_type_position ON vote (scrutin_id, vote_type, position)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_date_scrutin ON scrutin
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_legislature_numero ON scrutin
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_depute_type_position ON vote
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_scrutin_type_position ON vote
        SQL);
    }
}
