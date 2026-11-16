<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721210439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nature de l\'objet du scrutin et code de procédure du dossier, nécessaires au taux de soutien au gouvernement.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE dossier ADD procedure_code SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin ADD nature_vote VARCHAR(50) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_nature_vote ON scrutin (nature_vote)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE dossier DROP procedure_code
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_nature_vote ON scrutin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scrutin DROP nature_vote
        SQL);
    }
}
