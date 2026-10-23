<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Un code INSEE de métropole ou d'outre-mer tient sur cinq caractères, mais le
 * référentiel des communes range aussi les 224 pays où votent les Français
 * établis hors de France, sous des codes à six — `099069` pour Dubaï. Sans cette
 * largeur, les 15 200 résultats européens de l'étranger sont muets.
 */
final class Version20260722165231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Élargit code_insee à six caractères, pour les Français de l\'étranger.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_europeenne CHANGE code_insee code_insee VARCHAR(6) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_legislative CHANGE code_insee code_insee VARCHAR(6) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_presidentielle CHANGE code_insee code_insee VARCHAR(6) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_europeenne CHANGE code_insee code_insee VARCHAR(5) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_legislative CHANGE code_insee code_insee VARCHAR(5) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_presidentielle CHANGE code_insee code_insee VARCHAR(5) NOT NULL
        SQL);
    }
}
