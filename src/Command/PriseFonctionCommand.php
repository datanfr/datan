<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Renseigne `mandat.date_prise_fonction` (la `mandature.datePriseFonction` de
 * l'Assemblée) depuis le dépôt d'acteurs des Tricoteuses.
 *
 * La date figure dans les mêmes fichiers que {@see ImportActeursCommand} lit
 * pour peupler `mandat` — mais celui-là n'en retient que `dateDebut` (la date
 * d'élection). Plutôt que d'y toucher (il est modifié en parallèle), on relit
 * les fichiers à part pour n'y prendre que la prise de fonction. À terme, replier
 * l'extraction dans `ImportActeursCommand::importeMandats` fera une passe de moins.
 *
 * `date_prise_fonction` n'entre PAS dans l'upsert de mandats du sync (il ne
 * réécrit que `array_slice(COLONNES_MANDAT, 3)`), donc la valeur posée ici
 * survit à `app:sync:quotidien`. À lancer une fois, après `app:import:acteurs`,
 * avec `--tout` pour la première passe (sans delta de moisson à suivre) :
 *
 *     APP_ENV=prod php bin/console app:import:prises-fonction --tout
 */
#[AsCommand(
    name: 'app:import:prises-fonction',
    description: 'Renseigne mandat.date_prise_fonction depuis les acteurs des Tricoteuses.',
)]
class PriseFonctionCommand extends ImportTricoteusesCommand
{
    /** Mandat de député à l'Assemblée — le seul dont on veut la prise de fonction. */
    private const MANDAT_ASSEMBLEE = 'ASSEMBLEE';

    protected function depotParDefaut(): string
    {
        return 'acteurs';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Dates de prise de fonction');

        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les fichiers modifiés.' : 'Import complet du dépôt.');

        // mp_id (uid PA…) → depute_id : la clé de correspondance acteur ↔ fiche.
        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        // On regroupe d'abord par clé unique du mandat (depute_id, legislature,
        // date_debut) : quelques acteurs listent deux mandats ASSEMBLEE sur la
        // même législature et la même date de début (suppléance devenue titulaire).
        // Sans ce dédoublonnage, chaque passe réécrit la ligne deux fois — état
        // final stable mais compteur trompeur, et seconde exécution jamais nulle.
        $aPoser = [];
        foreach ($this->fichiers($chemin, 'acteurs', $tout) as $fichier) {
            $acteur = $this->lisJson($fichier);
            if ($acteur === null || !isset($acteur['uid'])) {
                continue;
            }

            $deputeId = $idDeputes[$acteur['uid']] ?? null;
            if ($deputeId === null) {
                continue;
            }

            foreach ($acteur['mandats'] ?? [] as $mandat) {
                if (($mandat['typeOrgane'] ?? null) !== self::MANDAT_ASSEMBLEE) {
                    continue;
                }

                $prise = $this->date($mandat['mandature']['datePriseFonction'] ?? null);
                $legislature = $this->entier($mandat['legislature'] ?? null);
                $debut = $this->date($mandat['dateDebut'] ?? null);
                if ($prise === null || $legislature === null || $debut === null) {
                    continue;
                }

                $aPoser[$deputeId . '|' . $legislature . '|' . $debut] = [$prise, $deputeId, $legislature, $debut];
            }
        }

        $renseignes = 0;
        $this->connection->beginTransaction();

        try {
            foreach ($aPoser as [$prise, $deputeId, $legislature, $debut]) {
                // Clé unique (depute_id, legislature, date_debut) : l'UPDATE ne
                // touche qu'une ligne. Les lignes affectées ne comptent que les
                // changements réels — 0 à la seconde exécution (idempotence).
                $renseignes += (int) $this->connection->executeStatement(
                    'UPDATE mandat SET date_prise_fonction = ?
                     WHERE depute_id = ? AND legislature = ? AND date_debut = ?',
                    [$prise, $deputeId, $legislature, $debut],
                );
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->success(sprintf('%d mandats, %d dates de prise de fonction posées.', \count($aPoser), $renseignes));

        return Command::SUCCESS;
    }
}
