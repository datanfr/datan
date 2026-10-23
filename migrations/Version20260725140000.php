<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Tables précalculées des statistiques de comportement de la fiche député.
 *
 * `statistique_depute` (participation solennelle + loyauté) et `accord_groupe`
 * (proximité par groupe) reproduisent les tables `class_*` / `deputes_accord_cleaned`
 * de l'application d'origine, remplies par `app:calcul:statistiques-deputes`.
 * La fiche s'y lit en DBAL : les moyennes et l'agrégation sur 1,27 M de votes ne
 * peuvent pas se recalculer à chaque affichage.
 *
 * Migration écrite à la main — et non générée — pour ne capturer que ces deux
 * tables, sans embarquer les diffs de schéma en cours d'autres chantiers
 * (cf. CLAUDE.md).
 */
final class Version20260725140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables précalculées des statistiques de la fiche député (participation, loyauté, proximité par groupe).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE statistique_depute (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, legislature SMALLINT NOT NULL, participation_score SMALLINT DEFAULT NULL, participation_votes INT NOT NULL, loyaute_score SMALLINT DEFAULT NULL, loyaute_votes INT NOT NULL, actif SMALLINT NOT NULL, UNIQUE INDEX uniq_statistique_depute (depute_id, legislature), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE accord_groupe (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, groupe_id INT NOT NULL, legislature SMALLINT NOT NULL, accord SMALLINT NOT NULL, votes_n INT NOT NULL, INDEX idx_accord_depute (depute_id, legislature), UNIQUE INDEX uniq_accord_groupe (depute_id, groupe_id, legislature), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE accord_groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE statistique_depute
        SQL);
    }
}
