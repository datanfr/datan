<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Note de simplicité sur les amendements (amendements_ia.simplicite_ia du
 * legacy, que PoliticAnalysis renseignait) : de 1 (très technique) à 5 (très
 * accessible). Remplie par app:ia:resumes-amendements, affichée en étoiles
 * dans l'écran /admin/amendements.
 *
 * Migration écrite à la main plutôt que par doctrine:migrations:diff : deux
 * autres chantiers modifient des entités dans le même arbre en ce moment, le
 * diff embarquerait leurs changements avec les miens.
 */
final class Version20260724180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'amendement.simplicite_ia : note de simplicité des résumés générés par IA.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement ADD simplicite_ia SMALLINT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement DROP simplicite_ia
        SQL);
    }
}
