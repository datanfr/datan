<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Tables des pages de classement (/statistiques) : les palmarès précalculés et
 * le profil socio-professionnel des députés dont deux d'entre eux dépendent.
 */
final class Version20260721220703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Classements précalculés des députés et des groupes, et profils socio-professionnels.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE classement (id INT AUTO_INCREMENT NOT NULL, depute_id INT DEFAULT NULL, groupe_id INT DEFAULT NULL, type VARCHAR(40) NOT NULL, legislature SMALLINT NOT NULL, rang INT NOT NULL, score NUMERIC(8, 3) NOT NULL, numerateur INT DEFAULT NULL, denominateur INT DEFAULT NULL, calcule_le DATE NOT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_55EE9D6D51A07B9C (depute_id), INDEX IDX_55EE9D6D7A45358C (groupe_id), INDEX idx_type_legislature_rang (type, legislature, rang), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE profil_social (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, date_naissance DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', metier VARCHAR(255) DEFAULT NULL, cat_soc_pro VARCHAR(150) DEFAULT NULL, fam_soc_pro VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_9E68D551A07B9C (depute_id), INDEX idx_fam_soc_pro (fam_soc_pro), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE classement ADD CONSTRAINT FK_55EE9D6D51A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE classement ADD CONSTRAINT FK_55EE9D6D7A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profil_social ADD CONSTRAINT FK_9E68D551A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE classement DROP FOREIGN KEY FK_55EE9D6D51A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE classement DROP FOREIGN KEY FK_55EE9D6D7A45358C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profil_social DROP FOREIGN KEY FK_9E68D551A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE classement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE profil_social
        SQL);
    }
}
