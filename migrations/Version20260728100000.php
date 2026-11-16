<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Date de prise de fonction sur chaque mandat.
 *
 * `mandat.date_debut` porte la date d'élection (30 juin 2024 pour la 17e) ;
 * l'Assemblée publie, elle, une `mandature.datePriseFonction` postérieure
 * (1er juillet 2024) — c'est celle que datan.fr affiche (« entré en fonction en
 * juillet 2024 ») et sur laquelle il calcule l'ancienneté. On la stocke à part
 * pour ne pas dénaturer `date_debut`, qui sert aussi de clé d'upsert des mandats.
 */
final class Version20260728100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Date de prise de fonction sur le mandat (mandat.date_prise_fonction).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat ADD date_prise_fonction DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE mandat DROP date_prise_fonction
        SQL);
    }
}
