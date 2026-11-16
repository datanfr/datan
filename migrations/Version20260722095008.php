<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Porte en colonnes les clés d'appariement d'un scrutin à son amendement.
 *
 * `seanceDiscussionRef`, `numeroOrdreDepot`, `cycleDeVie.dateSort` et les
 * signataires vivaient jusqu'ici dans les seuls fichiers JSON du dépôt :
 * rattacher un scrutin imposait de relire les 123 000 fichiers à chaque
 * exécution, soit dix-neuf secondes. En base, l'appariement devient une
 * jointure indexée.
 */
final class Version20260722095008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clés d\'appariement scrutin ↔ amendement portées en colonnes indexées';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement ADD seance_ref VARCHAR(60) DEFAULT NULL, ADD numero_ordre VARCHAR(20) DEFAULT NULL, ADD date_sort DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', ADD signataires VARCHAR(500) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_seance_numero ON amendement (seance_ref, numero_ordre)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_sort_numero ON amendement (date_sort, numero_ordre)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_seance_numero ON amendement
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_sort_numero ON amendement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE amendement DROP seance_ref, DROP numero_ordre, DROP date_sort, DROP signataires
        SQL);
    }
}
