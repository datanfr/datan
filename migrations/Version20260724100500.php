<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Inscriptions à la newsletter et campagnes de dons.
 *
 * Les deux tables sont vides dans le backup public de la production : il n'y a
 * rien à récupérer aujourd'hui, mais les abonnés réels devront être repris sur
 * la vraie base au déploiement (TODO §4). Les colonnes reprennent la structure
 * du legacy (`newsletter`, `campaigns`) sous des noms français, pour que la
 * récupération soit un simple transvasement.
 */
final class Version20260724100500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Newsletter (inscriptions) et campagnes de dons.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE newsletter (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, liste_generale SMALLINT NOT NULL, liste_votes SMALLINT NOT NULL, depute LONGTEXT DEFAULT NULL, departement LONGTEXT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, inscrit_le DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', modifie_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_newsletter_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE campagne (id INT AUTO_INCREMENT NOT NULL, texte LONGTEXT NOT NULL, date_debut DATE NOT NULL COMMENT '(DC2Type:date_immutable)', date_fin DATE NOT NULL COMMENT '(DC2Type:date_immutable)', auteur VARCHAR(100) DEFAULT NULL, position SMALLINT DEFAULT NULL, page VARCHAR(100) DEFAULT NULL, active SMALLINT NOT NULL, cree_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', modifie_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE newsletter
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE campagne
        SQL);
    }
}
