<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Les résultats électoraux : catalogue des scrutins, candidatures des députés,
 * et résultats par commune des législatives, de la présidentielle et des
 * européennes.
 *
 * Ces données ne sont pas dans l'open data de l'Assemblée. Elles viennent de la
 * base de production, par les exports que décrivent
 * {@see \App\Command\ImportElectionsCommand} et
 * {@see \App\Command\ImportResultatsElectorauxCommand}.
 */
final class Version20260722143441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Résultats électoraux : élections, candidatures et résultats par commune.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, election_id INT NOT NULL, district VARCHAR(10) DEFAULT NULL, position VARCHAR(50) DEFAULT NULL, nuance VARCHAR(25) DEFAULT NULL, candidat TINYINT(1) DEFAULT NULL, visible TINYINT(1) NOT NULL, second_tour TINYINT(1) DEFAULT NULL, elu TINYINT(1) DEFAULT NULL, lien VARCHAR(1000) DEFAULT NULL, source LONGTEXT DEFAULT NULL, INDEX IDX_E33BD3B851A07B9C (depute_id), INDEX IDX_E33BD3B8A708DAFF (election_id), INDEX idx_election_visible (election_id, visible), UNIQUE INDEX uniq_depute_election (depute_id, election_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE election (id INT AUTO_INCREMENT NOT NULL, identifiant SMALLINT NOT NULL, slug VARCHAR(100) NOT NULL, libelle VARCHAR(100) NOT NULL, libelle_abrege VARCHAR(50) NOT NULL, annee SMALLINT NOT NULL, date_tour1 DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_tour2 DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', candidats TINYINT(1) NOT NULL, url_resultats VARCHAR(500) DEFAULT NULL, UNIQUE INDEX UNIQ_DCA03800C90409EC (identifiant), UNIQUE INDEX UNIQ_DCA03800989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE liste_europeenne (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, numero SMALLINT NOT NULL, nom VARCHAR(255) NOT NULL, tete_de_liste VARCHAR(255) DEFAULT NULL, parti VARCHAR(255) DEFAULT NULL, UNIQUE INDEX uniq_liste_europeenne (annee, numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat_europeenne (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, code_insee VARCHAR(5) NOT NULL, numero_liste SMALLINT NOT NULL, part NUMERIC(5, 2) DEFAULT NULL, INDEX idx_europeenne_commune (code_insee, annee), UNIQUE INDEX uniq_europeenne (annee, code_insee, numero_liste), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat_legislative (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, tour SMALLINT NOT NULL, code_insee VARCHAR(5) NOT NULL, code_departement VARCHAR(3) NOT NULL, circonscription SMALLINT NOT NULL, candidat VARCHAR(5) NOT NULL, nom VARCHAR(50) DEFAULT NULL, prenom VARCHAR(50) DEFAULT NULL, sexe VARCHAR(5) DEFAULT NULL, nuance VARCHAR(5) DEFAULT NULL, voix INT DEFAULT NULL, inscrits INT DEFAULT NULL, abstentions INT DEFAULT NULL, votants INT DEFAULT NULL, blancs INT DEFAULT NULL, nuls INT DEFAULT NULL, exprimes INT DEFAULT NULL, INDEX idx_legislative_commune (code_insee, annee, tour), UNIQUE INDEX uniq_legislative (annee, tour, code_insee, circonscription, candidat), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat_presidentielle (id INT AUTO_INCREMENT NOT NULL, annee SMALLINT NOT NULL, code_insee VARCHAR(5) NOT NULL, candidat VARCHAR(25) NOT NULL, voix INT DEFAULT NULL, part NUMERIC(5, 2) DEFAULT NULL, votants INT DEFAULT NULL, abstention_part NUMERIC(5, 2) DEFAULT NULL, INDEX idx_presidentielle_commune (code_insee, annee), UNIQUE INDEX uniq_presidentielle (annee, code_insee, candidat), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B851A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8A708DAFF FOREIGN KEY (election_id) REFERENCES election (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B851A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8A708DAFF
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE election
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE liste_europeenne
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat_europeenne
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat_legislative
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat_presidentielle
        SQL);
    }
}
