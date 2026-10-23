<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * `dossier.commission_fond` : l'organe de la commission saisie au fond, lu dans
 * l'acte `AN1-COM-FOND` des dossiers des Tricoteuses. C'est la donnée qui
 * manquait au troisième score de participation (« Votes par spécialisation »,
 * `class_participation_commission` du legacy) : participation d'un député aux
 * scrutins des textes examinés dans sa commission.
 *
 * `statistique_depute.groupe_id` : le groupe du député résolu pour la
 * législature de la ligne (rattachement le plus récent, principal). La moyenne
 * de groupe d'une fiche se lisait sur `depute.groupe_id`, qui ne porte que
 * l'appartenance courante : pour une législature passée, tous les groupes y
 * sont clos et la moyenne sortait vide.
 */
final class Version20260804090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'dossier.commission_fond (votes par spécialisation) et statistique_depute.groupe_id (fiches des législatures passées).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dossier ADD commission_fond VARCHAR(25) DEFAULT NULL');
        $this->addSql('ALTER TABLE statistique_depute ADD groupe_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_statistique_groupe ON statistique_depute (groupe_id, legislature)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_statistique_groupe ON statistique_depute');
        $this->addSql('ALTER TABLE statistique_depute DROP groupe_id');
        $this->addSql('ALTER TABLE dossier DROP commission_fond');
    }
}
