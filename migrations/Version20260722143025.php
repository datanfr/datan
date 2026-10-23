<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Géographie électorale reprise de l'application d'origine : départements avec
 * leurs articles, communes avec leur population et leurs circonscriptions.
 *
 * Aucune de ces données ne vient de l'open data de l'Assemblée, qui s'arrête au
 * numéro de circonscription. Elles sont chargées par `app:import:communes`.
 */
final class Version20260722143025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Départements (articles, région) et communes (population, circonscriptions).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(15) NOT NULL, nom VARCHAR(255) NOT NULL, slug VARCHAR(100) NOT NULL, libelle_dans VARCHAR(50) DEFAULT NULL, libelle_de VARCHAR(50) DEFAULT NULL, region VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_C1765B6377153098 (code), UNIQUE INDEX UNIQ_C1765B63989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commune (id INT AUTO_INCREMENT NOT NULL, departement_id INT NOT NULL, code_insee VARCHAR(10) NOT NULL, nom VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, population INT DEFAULT NULL, UNIQUE INDEX UNIQ_E2E2D1EE1649A761 (code_insee), INDEX IDX_E2E2D1EECCF9E01E (departement_id), INDEX idx_departement_slug (departement_id, slug), INDEX idx_departement_population (departement_id, population), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commune_circonscription (id INT AUTO_INCREMENT NOT NULL, commune_id INT NOT NULL, circonscription SMALLINT NOT NULL, INDEX IDX_9FD8F66B131A4F72 (commune_id), UNIQUE INDEX uniq_commune_circonscription (commune_id, circonscription), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EECCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_circonscription ADD CONSTRAINT FK_9FD8F66B131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id) ON DELETE CASCADE
        SQL);

        // Repris de Version20260722101210, dont l'horodatage est antérieur à
        // cette migration alors que ces instructions supposent `commune` créée.
        // Sur une base vierge, elles y échouaient (errno 150). Voir le docbloc
        // de cette migration-là.
        $this->addSql(<<<'SQL'
            ALTER TABLE commune ADD population2012 INT DEFAULT NULL, ADD code_postal VARCHAR(40) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_adjacente ADD CONSTRAINT FK_C1777978131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_adjacente ADD CONSTRAINT FK_C1777978894CF5C0 FOREIGN KEY (adjacente_id) REFERENCES commune (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_adjacente DROP FOREIGN KEY FK_C1777978131A4F72
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_adjacente DROP FOREIGN KEY FK_C1777978894CF5C0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune DROP population2012, DROP code_postal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune_circonscription DROP FOREIGN KEY FK_9FD8F66B131A4F72
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commune DROP FOREIGN KEY FK_E2E2D1EECCF9E01E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commune_circonscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commune
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE departement
        SQL);
    }
}
