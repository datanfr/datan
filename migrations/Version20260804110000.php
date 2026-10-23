<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * `amendement.auteur_type` / `auteur_ref` : l'auteur principal de l'amendement
 * (`signataires.auteur` des fichiers des Tricoteuses) — « Député », « Rapporteur »
 * ou « Gouvernement », et la référence d'acteur (`PA…`) ou d'organe (`PO…`).
 * C'est la donnée de la carte « L'auteur de l'amendement » de la page de vote,
 * que `amendement.signataires` (le libellé en toutes lettres) ne suffit pas à
 * construire. Alimentées par `app:import:auteurs-amendements`.
 */
final class Version20260804110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "amendement.auteur_type et auteur_ref (carte « L'auteur de l'amendement »).";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE amendement ADD auteur_type VARCHAR(30) DEFAULT NULL, ADD auteur_ref VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE amendement DROP auteur_type, DROP auteur_ref');
    }
}
