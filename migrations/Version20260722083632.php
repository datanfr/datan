<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Distingue le rattachement principal d'un député à un groupe.
 *
 * Un député peut porter plusieurs mandats de groupe ouverts en même temps.
 * Sur la 17e législature, il y en a 588, dont 577 principaux — le nombre exact
 * de sièges. Sans cette colonne, la liste des membres d'un groupe compterait
 * onze députés qui siègent ailleurs.
 */
final class Version20260722083632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattachement principal d\'un député à un groupe (nominPrincipale de l\'open data)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe ADD nomin_principale TINYINT(1) DEFAULT 1 NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_groupe DROP nomin_principale
        SQL);
    }
}
