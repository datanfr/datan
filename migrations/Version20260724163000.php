<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Demandes de compte des députés (table demande_compte_depute) : la file des
 * demandes que la rédaction relit et approuve, l'application d'origine s'en
 * remettant à un courriel d'activation que ce portage n'envoie pas.
 */
final class Version20260724163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Demandes de compte député (demande_compte_depute) : file de traitement admin.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE demande_compte_depute (id INT AUTO_INCREMENT NOT NULL, depute_id INT NOT NULL, email VARCHAR(255) NOT NULL, etat VARCHAR(20) NOT NULL, demandee_le DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', traitee_le DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_demande_depute (depute_id), INDEX idx_demande_etat (etat), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_compte_depute ADD CONSTRAINT FK_demande_depute FOREIGN KEY (depute_id) REFERENCES depute (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_compte_depute DROP FOREIGN KEY FK_demande_depute
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande_compte_depute
        SQL);
    }
}
