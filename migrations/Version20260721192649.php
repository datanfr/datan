<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721192649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE mandat (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, legislature SMALLINT NOT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', departement_nom VARCHAR(100) DEFAULT NULL, departement_code VARCHAR(5) DEFAULT NULL, circonscription SMALLINT DEFAULT NULL, cause_mandat VARCHAR(255) DEFAULT NULL, INDEX IDX_1E53EFD551A07B9C (depute_id), UNIQUE INDEX uniq_depute_legislature_debut (depute_id, legislature, date_debut), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat ADD CONSTRAINT FK_1E53EFD551A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE depute ADD dpt_slug VARCHAR(100) DEFAULT NULL, ADD departement_nom VARCHAR(100) DEFAULT NULL, ADD departement_code VARCHAR(5) DEFAULT NULL, ADD circonscription SMALLINT DEFAULT NULL, ADD region VARCHAR(100) DEFAULT NULL, ADD place_hemicycle SMALLINT DEFAULT NULL, ADD profession VARCHAR(255) DEFAULT NULL, ADD commission VARCHAR(100) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat DROP FOREIGN KEY FK_1E53EFD551A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mandat
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE depute DROP dpt_slug, DROP departement_nom, DROP departement_code, DROP circonscription, DROP region, DROP place_hemicycle, DROP profession, DROP commission
        SQL);
    }
}
