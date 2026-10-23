<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Index de `messenger_messages` tel que le déclare Symfony 7.4.
 *
 * La table n'est pas une entité du projet : son schéma est celui que le
 * transport Doctrine de Messenger ajoute lui-même. Depuis la 7.4, il remplace
 * ses trois index simples par un seul index composite, qui sert directement la
 * requête de dépilage (file, date de disponibilité, livraison). Sans cette
 * migration, `doctrine:schema:validate` — contrôlé par la CI — échoue dès la
 * montée de version.
 */
final class Version20261023090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'messenger_messages : index composite de Symfony 7.4.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }
}
