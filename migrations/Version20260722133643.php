<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Élargit `amendement.signataires`, que 40 357 listes débordaient.
 *
 * En VARCHAR(500), un amendement cosigné par tout un groupe — jusqu'à 2 629
 * caractères — était tronqué, ce qui ne gardait que ses premiers signataires.
 * Le résultat se trouvait être meilleur, l'objet d'un scrutin nommant le
 * déposant, mais ce choix appartient à la règle d'appariement, pas à une
 * largeur de colonne.
 */
final class Version20260722133643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Stocke la liste entière des signataires d\'un amendement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement CHANGE signataires signataires LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement CHANGE signataires signataires VARCHAR(500) DEFAULT NULL
        SQL);
    }
}
