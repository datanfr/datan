<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721230905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Comptes de la rédaction et attribution des décryptages.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(180) NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', mot_de_passe VARCHAR(255) NOT NULL, cree_le DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_identifiant (identifiant), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD auteur_id INT DEFAULT NULL, ADD modifie_par_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD CONSTRAINT FK_67322C6060BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage ADD CONSTRAINT FK_67322C60553B2554 FOREIGN KEY (modifie_par_id) REFERENCES utilisateur (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67322C6060BB6FE6 ON decryptage (auteur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67322C60553B2554 ON decryptage (modifie_par_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP FOREIGN KEY FK_67322C6060BB6FE6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP FOREIGN KEY FK_67322C60553B2554
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67322C6060BB6FE6 ON decryptage
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67322C60553B2554 ON decryptage
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE decryptage DROP auteur_id, DROP modifie_par_id
        SQL);
    }
}
