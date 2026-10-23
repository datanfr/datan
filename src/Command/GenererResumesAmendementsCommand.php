<?php

namespace App\Command;

use App\Ia\GenerateurResumeAmendement;
use App\Ia\MoteurIa;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Remplit les résumés IA des amendements mis aux voix (titre_ia, resume_ia,
 * simplicite_ia) — la boucle que PoliticAnalysis faisait de l'extérieur via
 * /api/amendements_ia, désormais interne.
 *
 * Ne touche jamais un résumé existant : la rédaction a pu le corriger, et un
 * résumé relu (resume_relu) est du contenu approuvé. On ne génère que le
 * manquant ; pour regénérer, on efface d'abord la colonne, sciemment.
 *
 * Cette garde a un revers : un lot passé au modèle d'essai bloque pour toujours
 * un lot plus propre, puisque la colonne n'est plus nulle. D'où `--simulation`,
 * qui génère et affiche sans rien écrire — le seul moyen d'éprouver un modèle
 * (et l'échelle de simplicité qu'il rend) avant d'arrêter `IA_MODELE`.
 *
 * Hors du sync quotidien : chaque exécution appelle un modèle (local ou
 * facturé) — on la lance sciemment, comme les imports de récupération.
 * L'écran /admin/amendements montre le résultat, à relire avant affichage
 * public.
 */
#[AsCommand(
    name: 'app:ia:resumes-amendements',
    description: 'Génère les résumés IA manquants des amendements mis aux voix (à relire dans /admin/amendements).',
)]
class GenererResumesAmendementsCommand extends Command
{
    /** Au-delà, le moteur est considéré hors service : inutile d'insister. */
    private const ECHECS_CONSECUTIFS_MAX = 3;

    public function __construct(
        private readonly Connection $connection,
        private readonly MoteurIa $moteur,
        private readonly GenerateurResumeAmendement $generateur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('legislature', null, InputOption::VALUE_REQUIRED, 'Législature des scrutins', (string) Legislature::COURANTE)
            ->addOption('jours', null, InputOption::VALUE_REQUIRED, 'Fenêtre en jours avant aujourd\'hui', '30')
            ->addOption('limite', null, InputOption::VALUE_REQUIRED, 'Nombre maximal de résumés générés par exécution', '50')
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Génère et affiche sans rien écrire : éprouve un modèle sans consommer le lot');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Résumés IA des amendements');

        if (!$this->moteur->estActif()) {
            $io->error('Aucun modèle configuré : renseignez IA_MODELE (et ANTHROPIC_API_KEY pour un modèle claude-*).');

            return Command::FAILURE;
        }

        $legislature = (int) $input->getOption('legislature');
        $jours = max(1, (int) $input->getOption('jours'));
        $limite = max(1, (int) $input->getOption('limite'));
        $simulation = (bool) $input->getOption('simulation');
        $depuis = (new \DateTimeImmutable())->modify(sprintf('-%d days', $jours))->format('Y-m-d');

        // Un amendement peut porter plusieurs scrutins (rectifications) : le
        // GROUP BY garde une ligne par amendement, le scrutin le plus récent.
        $amendements = $this->connection->fetchAllAssociative(
            'SELECT a.id, a.expose, MAX(s.titre) AS titre, MAX(s.objet) AS objet,
                    MAX(s.numero) AS numero
             FROM amendement a
             JOIN scrutin s ON s.amendement_id = a.id
             WHERE s.legislature = :legislature
               AND s.date_scrutin >= :depuis
               AND a.resume_ia IS NULL
             GROUP BY a.id
             ORDER BY MAX(s.date_scrutin) DESC
             LIMIT ' . $limite,
            ['legislature' => $legislature, 'depuis' => $depuis],
        );

        if ($amendements === []) {
            $io->success(sprintf('Rien à générer : tous les amendements votés depuis %d jours (L%d) ont leur résumé.', $jours, $legislature));

            return Command::SUCCESS;
        }

        $io->text(sprintf('%d amendement%s sans résumé (modèle : %s)%s.',
            \count($amendements), \count($amendements) > 1 ? 's' : '', $this->moteur->modele(),
            $simulation ? ' — simulation, rien ne sera écrit' : '',
        ));

        $generes = 0;
        $echecs = 0;
        $echecsConsecutifs = 0;
        /** @var array<int, int> notes obtenues, pour rendre l'échelle réellement produite */
        $echelle = array_fill_keys(range(1, 5), 0);

        foreach ($amendements as $amendement) {
            $resume = $this->generateur->generer([
                'titre' => $amendement['titre'],
                'objet' => $amendement['objet'],
                'expose' => $amendement['expose'],
            ]);

            if ($resume === null) {
                ++$echecs;
                if (++$echecsConsecutifs >= self::ECHECS_CONSECUTIFS_MAX) {
                    $io->error(sprintf('%d échecs consécutifs : le moteur ne répond pas, arrêt. Détail dans les journaux.', $echecsConsecutifs));
                    break;
                }
                continue;
            }
            $echecsConsecutifs = 0;

            if (!$simulation) {
                // resume_ia IS NULL rejoué à l'écriture : si la rédaction a rempli
                // la colonne entre la sélection et maintenant, elle gagne.
                $this->connection->executeStatement(
                    'UPDATE amendement
                     SET titre_ia = ?, resume_ia = ?, simplicite_ia = ?
                     WHERE id = ? AND resume_ia IS NULL',
                    [$resume['titre'], $resume['resume'], $resume['simplicite'], $amendement['id']],
                );
            }
            ++$generes;
            ++$echelle[$resume['simplicite']];

            $io->text(sprintf('  scrutin n° %s → « %s » (simplicité %d/5)', $amendement['numero'], $resume['titre'], $resume['simplicite']));
            if ($simulation) {
                // En simulation, rien ne sera relu dans /admin/amendements : le
                // résumé ne se juge qu'ici.
                $io->text(sprintf('    %s', $resume['resume']));
            }
        }

        if ($generes > 0) {
            $io->newLine();
            $io->text('Échelle de simplicité obtenue (1 très technique → 5 très accessible) :');
            foreach ($echelle as $note => $nombre) {
                $io->text(sprintf('  %d/5 : %s %d', $note, str_pad(str_repeat('█', $nombre), 20, '·'), $nombre));
            }
        }

        $io->success(sprintf('%d résumé%s généré%s, %d échec%s — %s',
            $generes, $generes > 1 ? 's' : '', $generes > 1 ? 's' : '',
            $echecs, $echecs > 1 ? 's' : '',
            $simulation ? 'simulation : aucune écriture, le lot reste disponible.' : 'relecture dans /admin/amendements.',
        ));

        return $echecs > 0 && $generes === 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
