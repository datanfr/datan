<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Date de dissolution des partis politiques.
 *
 * La page /partis-politiques range les partis en trois blocs : ceux qui ont un
 * député rattaché, ceux qui n'en ont pas, et les anciens partis. Un effectif nul
 * ne suffit pas à séparer les deux derniers — seule la date de fin de l'organe
 * PARPOL le fait, et l'open data la publie pour 31 des 58 partis.
 */
final class Version20260722093015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute parti.date_fin, qui distingue un ancien parti d\'un parti sans député rattaché.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE parti ADD date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE parti DROP date_fin
        SQL);
    }
}
