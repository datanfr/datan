<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Table des communes limitrophes, servant la fiche de ville.
 *
 * **Cette migration ne touche plus à `commune`.** Elle porte un horodatage
 * antérieur à {@see Version20260722143025}, qui *crée* cette table : ses clés
 * étrangères et l'ajout de `population2012` / `code_postal` échouaient donc sur
 * une base vierge (« errno 150 : Foreign key constraint is incorrectly
 * formed »). Le défaut ne se voyait pas sur les bases existantes, où `commune`
 * avait été créée avant, mais rendait le jeu de migrations non rejouable — ce
 * qu'a révélé le rejeu sur base neuve du workflow d'intégration.
 *
 * Ces instructions vivent désormais à la fin de `Version20260722143025`. On les
 * y a déplacées plutôt que de renuméroter cette migration : son identifiant est
 * déjà inscrit dans `doctrine_migration_versions` en production, et le changer
 * la ferait rejouer.
 */
final class Version20260722101210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table des communes limitrophes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE commune_adjacente (commune_id INT NOT NULL, adjacente_id INT NOT NULL, INDEX IDX_C1777978131A4F72 (commune_id), INDEX IDX_C1777978894CF5C0 (adjacente_id), PRIMARY KEY(commune_id, adjacente_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE commune_adjacente
        SQL);
    }
}
