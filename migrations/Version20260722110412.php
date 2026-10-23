<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Commissions permanentes et mandats qui s'y exercent.
 *
 * `depute.commission` ne portait que le libellé de la commission courante :
 * de quoi afficher une ligne sur une fiche de député, pas de quoi composer le
 * bureau d'une commission ni retracer qui y a siégé. Les mandats COMPER de
 * l'open data comblent les deux.
 */
final class Version20260722110412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Commissions permanentes (organes COMPER) et mandats de commission des députés.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE commission (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(50) NOT NULL, libelle VARCHAR(255) NOT NULL, libelle_abrege VARCHAR(100) DEFAULT NULL, slug VARCHAR(100) NOT NULL, date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_1C650158539B0606 (uid), UNIQUE INDEX UNIQ_1C650158989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE fonction_commission (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, commission_id INT NOT NULL, uid VARCHAR(30) NOT NULL, legislature SMALLINT NOT NULL, code_qualite VARCHAR(50) DEFAULT NULL, libelle_qualite VARCHAR(255) DEFAULT NULL, date_debut DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_4D469D8251A07B9C (depute_id), INDEX IDX_4D469D82202D1EB2 (commission_id), INDEX idx_commission_legislature (commission_id, legislature), INDEX idx_depute_legislature (depute_id, legislature), UNIQUE INDEX uniq_fonction_commission_uid (uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_commission ADD CONSTRAINT FK_4D469D8251A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_commission ADD CONSTRAINT FK_4D469D82202D1EB2 FOREIGN KEY (commission_id) REFERENCES commission (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_commission DROP FOREIGN KEY FK_4D469D8251A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fonction_commission DROP FOREIGN KEY FK_4D469D82202D1EB2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fonction_commission
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commission
        SQL);
    }
}
