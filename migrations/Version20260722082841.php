<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Supprime `depute.cat_soc_pro`, colonne sans source ni lecteur.
 *
 * Elle stockait le code INSEE de la catégorie socioprofessionnelle, que
 * l'open data ne publie plus : il n'en reste que le libellé, désormais porté
 * par `profil_social.cat_soc_pro` (2176 députés renseignés, contre 859 pour
 * cette colonne). Aucune page ne la lisait. Deux députés y avaient un code
 * sans libellé correspondant — un code jamais affiché, la perte est nulle.
 */
final class Version20260722082841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime depute.cat_soc_pro, remplacée par profil_social.cat_soc_pro';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE depute DROP cat_soc_pro
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE depute ADD cat_soc_pro SMALLINT DEFAULT NULL
        SQL);
    }
}
