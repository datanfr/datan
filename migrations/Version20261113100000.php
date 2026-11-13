<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Réécrit le lien de chaque amendement vers le site de l'Assemblée sous sa
 * forme par identifiant, la seule qui aboutisse à coup sûr.
 *
 * Les liens en base étaient fabriqués en « numéro de texte + numéro d'ordre »
 * (`/dyn/17/amendements/1906/AN/1`). Cette forme rend une 404 dès que le site
 * suffixe le numéro de texte (« 1906C », « 0324A »), et désigne un amendement
 * de séance là où l'identifiant est celui d'un amendement de commission. Le
 * site redirige en revanche depuis l'identifiant seul : cf.
 * `App\Scrutin\LienAmendement`, dont cette requête reprend la règle à l'identique.
 *
 * Une migration et non un réimport : `app:import:amendements --tout` ne
 * réécrirait que la législature moissonnée, pas les amendements repris de la
 * base de production pour les législatures closes.
 */
final class Version20261113100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'amendement.href : lien par identifiant, que le site de l\'Assemblée redirige.';
    }

    public function up(Schema $schema): void
    {
        // Le motif écarte l'amendement fictif dont l'identifiant est la chaîne
        // « NULL » : il garde le lien, ou l'absence de lien, qu'il avait.
        $this->addSql(<<<'SQL'
            UPDATE amendement
            SET href = CONCAT('https://www.assemblee-nationale.fr/dyn/', legislature, '/amendements/', amendement_id)
            WHERE legislature IS NOT NULL
              AND amendement_id REGEXP '^AMANR[0-9]+L[0-9]+[A-Z0-9]+$'
            SQL);
    }

    public function down(Schema $schema): void
    {
        // Sans retour : l'ancienne forme ne se retrouve pas depuis la nouvelle,
        // et il n'y a aucune raison de vouloir la rétablir. Laissé vide plutôt
        // qu'en erreur, pour ne pas bloquer le retour arrière d'une migration
        // ultérieure.
    }
}
