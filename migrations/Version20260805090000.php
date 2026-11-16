<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * `statistique_depute.majorite_score` / `majorite_votes` : proximité du député
 * avec la majorité gouvernementale de sa législature — la table `class_majorite`
 * de l'application d'origine, dernière carte manquante de la fiche
 * `/deputes/…/legislature-N`.
 *
 * Colonnes nulles là où la législature ne déclare aucun groupe majoritaire :
 * depuis la dissolution de 2024, l'Assemblée ne publie plus de
 * `positionPolitique` sur ses groupes, et la 17e n'a donc pas de majorité à
 * laquelle se comparer (cf. CLAUDE.md). Alimentées par
 * `app:calcul:statistiques-deputes --legislature=N`.
 */
final class Version20260805090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'statistique_depute.majorite_score et majorite_votes (carte « Proximité avec la majorité gouvernementale »).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statistique_depute ADD majorite_score SMALLINT DEFAULT NULL, ADD majorite_votes INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statistique_depute DROP majorite_score, DROP majorite_votes');
    }
}
