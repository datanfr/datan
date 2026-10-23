<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adresse électronique institutionnelle du député, pour « Je contacte mon
 * député ».
 *
 * L'open data la publie (adresse de type `15`), mêlée à des adresses
 * municipales ou personnelles que l'import écarte : seul le domaine
 * `@assemblee-nationale.fr` est retenu — 2 073 députés sur 3 117.
 */
final class Version20260722134053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adresse électronique institutionnelle du député (mail_an)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE depute ADD mail_an VARCHAR(120) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE depute DROP mail_an
        SQL);
    }
}
