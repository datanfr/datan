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
 * Depuis la clé par uid (migration Version20261116140000),
 * `ImportActeursCommand::importeMandats` reprend lui-même la prise de fonction :
 * cette commande ne sert plus qu'à la reposer sans réimporter les acteurs. Elle
 * vise le mandat par son uid : la date de début ne suffit pas, un député revenu
 * du Gouvernement porte deux mandats de même début dans la législature, et
 * c'est justement leur prise de fonction qui les distingue.
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
                if ($prise === null || empty($mandat['uid'])) {
                    continue;
                }

                $aPoser[(string) $mandat['uid']] = $prise;
            }
        }

        $renseignes = 0;
        $this->connection->beginTransaction();

        try {
            foreach ($aPoser as $uid => $prise) {
                // Clé unique `uid` : l'UPDATE ne touche qu'une ligne. Les lignes
                // affectées ne comptent que les changements réels — 0 à la
                // seconde exécution (idempotence).
                $renseignes += (int) $this->connection->executeStatement(
                    'UPDATE mandat SET date_prise_fonction = ? WHERE uid = ?',
                    [$prise, (string) $uid],
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
