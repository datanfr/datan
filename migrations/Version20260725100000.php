<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Coordonnées et réseaux sociaux des députés (table contact_depute) : site,
 * courriels et comptes X / Facebook / Bluesky, données tenues à la main par
 * Datan et absentes de l'open data. Fiche annexe 1:1 du député, à côté de
 * `profil_social` (qui porte, elle, le profil socio-professionnel).
 */
final class Version20260725100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Coordonnées et réseaux sociaux des députés (contact_depute).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE contact_depute (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, site_web VARCHAR(255) DEFAULT NULL, mail_an VARCHAR(255) DEFAULT NULL, mail_perso VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, bluesky VARCHAR(255) DEFAULT NULL, mis_a_jour_le DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_AC69005151A07B9C (depute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact_depute ADD CONSTRAINT FK_contact_depute FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE contact_depute DROP FOREIGN KEY FK_contact_depute
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contact_depute
        SQL);
    }
}
