<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les votes nominatifs de deux scrutins de la 16e législature — l'IVG
 * dans la Constitution (n° 629) et le projet de loi immigration (n° 3213) — que
 * la fiche député mettait en avant dans « Ses positions importantes ».
 *
 * Doublement périmée. Écrite quand `vote` ne couvrait que la 17e législature,
 * pour servir le seul bloc des positions importantes ; depuis l'import des
 * dépôts Scrutins_XIV/XV/XVI_nettoye (`app:import:scrutins --depot=scrutins-xvi
 * --tout`), ces deux scrutins arrivent avec tous les autres. Et depuis le
 * 29 juillet 2026 (commits 67beea0b et a257c0ed du site), la sélection
 * éditoriale ne retient plus aucun vote de la 16e : les quatre scrutins de
 * {@see \App\Depute\ComportementDepute::VOTES_CLES} sont tous de la 17e.
 * Conservée parce qu'inoffensive — son upsert réécrit des lignes identiques —,
 * en attendant d'être supprimée.
 *
 * Fichiers à régénérer depuis le dépôt des Tricoteuses (législature close, ils
 * ne bougent plus) :
 *
 *   curl -o var/legacy/scrutins-cles/VTANR5L16V629.json \
 *     https://git.en-root.org/tricoteuses/data/assemblee-nettoye/Scrutins_XVI_nettoye/-/raw/master/AN/R5/L16/000/VTANR5L16V629.json
 *   curl -o var/legacy/scrutins-cles/VTANR5L16V3213.json \
 *     https://git.en-root.org/tricoteuses/data/assemblee-nettoye/Scrutins_XVI_nettoye/-/raw/master/AN/R5/L16/003/VTANR5L16V3213.json
 *
 * Seul le décompte nominatif officiel est repris — pas les mises au point : le
 * legacy calcule lui aussi ses scores sur `voteType = "decompteNominatif"`.
 */
#[AsCommand(
    name: 'app:import:votes-cles',
    description: 'Importe les votes nominatifs des deux scrutins-clés de la 16e législature (positions importantes).',
)]
class ImportVotesClesCommand extends Command
{
    private const POSITIONS = [
        'pour' => 'pour',
        'contre' => 'contre',
        'abstentions' => 'abstention',
        'nonVotants' => 'nonVotant',
    ];

    private const COLONNES = [
        'depute_id', 'scrutin_id', 'position', 'vote_type', 'cause_position',
        'par_delegation', 'scrutin_date', 'created_at', 'updated_at',
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dossier', null, InputOption::VALUE_REQUIRED, 'Répertoire des JSON de scrutin', 'var/legacy/scrutins-cles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des votes des scrutins-clés de la 16e législature');

        $fichiers = glob(rtrim((string) $input->getOption('dossier'), '/\\') . '/VTANR5L16V*.json') ?: [];
        if ($fichiers === []) {
            $io->error('Aucun fichier VTANR5L16V*.json : voir le docblock pour les régénérer.');

            return self::FAILURE;
        }

        $deputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute WHERE mp_id IS NOT NULL');
        $maintenant = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $total = 0;
        $ecartes = 0;

        foreach ($fichiers as $fichier) {
            $scrutin = json_decode((string) file_get_contents($fichier), true);
            if (!\is_array($scrutin) || !isset($scrutin['uid'])) {
                $io->warning(sprintf('%s illisible, ignoré.', basename($fichier)));
                continue;
            }

            $scrutinId = $this->connection->fetchOne(
                'SELECT id FROM scrutin WHERE uid = :uid',
                ['uid' => $scrutin['uid']],
            );
            if ($scrutinId === false) {
                $io->warning(sprintf('Scrutin %s absent de la base, ignoré.', $scrutin['uid']));
                continue;
            }

            $date = isset($scrutin['dateScrutin'])
                ? (new \DateTimeImmutable($scrutin['dateScrutin']))->format('Y-m-d')
                : null;

            $lot = [];
            foreach ($scrutin['ventilationVotes']['groupes'] ?? [] as $groupe) {
                foreach (self::POSITIONS as $cle => $position) {
                    foreach ($groupe['vote']['decompteNominatif'][$cle] ?? [] as $votant) {
                        $deputeId = $deputes[$votant['acteurRef'] ?? ''] ?? null;
                        if ($deputeId === null) {
                            ++$ecartes;
                            continue;
                        }

                        $lot[] = [
                            (int) $deputeId,
                            (int) $scrutinId,
                            $position,
                            'decompteNominatif',
                            $votant['causePositionVote'] ?? null,
                            !empty($votant['parDelegation']) ? 1 : 0,
                            $date,
                            $maintenant,
                            $maintenant,
                        ];
                    }
                }
            }

            if ($lot !== []) {
                $tuple = '(' . implode(', ', array_fill(0, \count(self::COLONNES), '?')) . ')';
                $this->connection->executeStatement(
                    'INSERT INTO vote (' . implode(', ', self::COLONNES) . ') VALUES '
                    . implode(', ', array_fill(0, \count($lot), $tuple))
                    . ' ON DUPLICATE KEY UPDATE position = VALUES(position), scrutin_date = VALUES(scrutin_date), updated_at = VALUES(updated_at)',
                    array_merge(...$lot),
                );
            }

            $io->text(sprintf('%s : %d votes.', $scrutin['uid'], \count($lot)));
            $total += \count($lot);
        }

        if ($ecartes > 0) {
            $io->text(sprintf('%d votants écartés : acteur inconnu de la table depute.', $ecartes));
        }

        $io->success(sprintf('%d votes importés.', $total));

        return self::SUCCESS;
    }
}
