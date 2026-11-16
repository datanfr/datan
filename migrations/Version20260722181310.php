<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rattache un compte de connexion à un député, pour ouvrir l'espace où il
 * rédige ses explications de vote.
 *
 * L'index n'est volontairement pas unique : l'application d'origine ne
 * l'imposait pas non plus, et un député dont un collaborateur tient le compte
 * peut en avoir plusieurs.
 */
final class Version20260722181310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattache un compte utilisateur à un député (espace de rédaction des explications de vote).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD depute_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B351A07B9C FOREIGN KEY (depute_id) REFERENCES depute (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1D1C63B351A07B9C ON utilisateur (depute_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B351A07B9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1D1C63B351A07B9C ON utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP depute_id
        SQL);
    }
}
