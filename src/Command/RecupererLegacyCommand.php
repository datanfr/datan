<?php

namespace App\Command;

use App\Entity\RecuperationLegacy;
use App\Http\RechauffeurCache;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Rejoue toute la récupération depuis la base du legacy, dans l'ordre des
 * dépendances, à partir des TSV qu'a écrits `bin/recuperation/exporter-legacy.sh`.
 *
 *     bin/recuperation/exporter-legacy.sh                     # sur le serveur
 *     php bin/console app:recuperation:legacy --dossier=…     # puis ceci
 *
 * Préalable : la base est déjà nourrie par le sync (`app:sync:quotidien`) —
 * députés et scrutins, auxquels tout se rattache. La commande le vérifie.
 *
 * Chaque étape rend son bilan ({@see ImportLegacyCommand::consigner()}) :
 * lignes lues, écrites, écartées et pourquoi. La commande les additionne,
 * signale tout fichier dont le compte ne tombe pas juste — une ligne ni écrite
 * ni écartée est une règle qui manque —, compare les lignes lues au
 * `MANIFESTE.tsv` de l'export (un fichier tronqué ou retouché se voit), et
 * consigne le tout dans `recuperation_legacy`.
 *
 * **Elle réaligne sur la production : c'est un outil d'avant la bascule.**
 * Après, l'application est la source — un rédacteur publie, un député écrit,
 * un lecteur change son mot de passe ou se désabonne — et un rejeu rendrait à
 * chacun l'état figé du legacy. `--clore`, lancé le jour de la bascule juste
 * après la dernière récupération, inscrit une ligne `bascule` au journal ; dès
 * lors la commande refuse de tourner. Il n'y a pas d'option pour passer outre :
 * qui veut vraiment rejouer supprime cette ligne à la main, sciemment.
 *
 * Point d'extension : une table de plus à récupérer, c'est un fichier SQL dans
 * `bin/recuperation/legacy/` et une étape dans {@see ETAPES}. Les municipales
 * 2026 n'en font pas partie : elles se lisent chez le ministère et sur
 * data.gouv (`app:import:municipales`, `app:import:noms-communes`), pas dans
 * la base du legacy.
 */
#[AsCommand(
    name: 'app:recuperation:legacy',
    description: 'Rejoue toute la récupération depuis les exports TSV du legacy, avec bilan chiffré (avant la bascule seulement).',
)]
class RecupererLegacyCommand extends Command
{
    /**
     * Les étapes, dans l'ordre des dépendances : commande, options → fichier
     * (relatif au dossier d'export), libellé.
     *
     * - la géographie avant les résultats, qui s'y rattachent par commune ;
     * - les comptes avant les décryptages, qui y retrouvent leur auteur par
     *   `id_origine`, et avant la newsletter (`id_user`) ;
     * - les décryptages avant les explications, qui y résolvent le vote du
     *   Congrès ;
     * - les amendements des législatures closes avant leurs résumés ;
     * - les votes MODEM de la 15e en dernier : ce ne sont pas des
     *   ImportLegacyCommand (TSV sans en-tête, option `--file`, pas de bilan
     *   consigné — leur sortie donne lues, écrites et ignorées), mais ils
     *   reprennent une donnée du legacy que rien d'autre ne restitue, et leur
     *   upsert se rejoue sans dommage. Ils n'y reçoivent que `--file` :
     *   `--truncate` viderait la table `vote` entière.
     *
     * Les étapes des législatures closes exigent leurs scrutins en base
     * (dépôts figés, importés à part du sync) : {@see verifierPrealables()}.
     *
     * @var list<array{0: string, 1: array<string, string>, 2: string}>
     */
    public const ETAPES = [
        ['app:import:communes', [
            '--departements' => 'departements.tsv',
            '--communes' => 'communes.tsv',
            '--adjacentes' => 'communes_adjacentes.tsv',
            '--maires' => 'maires.tsv',
        ], 'Géographie électorale'],
        ['app:import:elections', [
            '--elections' => 'elections.tsv',
            '--candidatures' => 'candidatures.tsv',
        ], 'Élections et candidatures'],
        ['app:import:circonscriptions', [
            '--participation' => 'circonscriptions_participation.tsv',
            '--resultats' => 'circonscriptions_resultats.tsv',
            '--partielles' => 'circonscriptions_partielles.tsv',
        ], 'Résultats par circonscription'],
        ['app:import:resultats-electoraux', [
            '--legislatives' => 'resultats_legislatives.tsv',
            '--presidentielle' => 'resultats_presidentielle.tsv',
            '--listes' => 'listes_europeennes.tsv',
            '--europeennes' => 'resultats_europeennes.tsv',
        ], 'Résultats par commune'],
        ['app:import:utilisateurs', ['--fichier' => 'utilisateurs.tsv'], 'Comptes'],
        ['app:import:decryptages', [
            '--categories' => 'decryptages_categories.tsv',
            '--lectures' => 'decryptages_lectures.tsv',
            '--decryptages' => 'decryptages.tsv',
        ], 'Décryptages'],
        ['app:import:explications', ['--fichier' => 'explications.tsv'], 'Explications de vote'],
        ['app:import:articles', [
            '--categories' => 'article_categories.tsv',
            '--articles' => 'articles.tsv',
        ], 'Blog'],
        ['app:import:faq', [
            '--categories' => 'faq_categories.tsv',
            '--questions' => 'faq_posts.tsv',
        ], 'FAQ'],
        ['app:import:quiz', ['--questions' => 'quiz.tsv'], 'Questionnaire'],
        ['app:import:campagnes', ['--fichier' => 'campagnes.tsv'], 'Campagnes de dons'],
        ['app:import:newsletter', ['--fichier' => 'newsletter.tsv'], 'Newsletter'],
        ['app:import:parrainages', ['--fichier' => 'parrainages.tsv'], 'Parrainages 2022'],
        ['app:import:reseaux-sociaux', ['--contacts' => 'deputes_contacts.tsv'], 'Contacts des députés'],
        ['app:import:professions-foi', ['--fichier' => 'professions_foi.tsv'], 'Professions de foi'],
        ['app:import:amendements-clos', ['--fichier' => 'amendements_clos.tsv'], 'Amendements des 15e et 16e législatures'],
        ['app:import:resumes-amendements', [
            '--resumes' => 'amendements_ia.tsv',
            '--tags' => 'amendements_tags.tsv',
        ], 'Résumés IA et mots-clés de la 17e'],
        ['app:import:exposes', ['--fichier' => 'exposes.tsv'], 'Résumés d\'exposé des motifs'],
        ['app:import:votes', ['--file' => 'votes_l15_modem.tsv'], 'Votes MODEM de la 15e (sept scrutins)'],
        ['app:import:vote-groupes', ['--file' => 'votes_groupes_l15_modem.tsv'], 'Ventilation MODEM de la 15e'],
        // Point d'extension : une étape de plus, avec sa requête dans
        // bin/recuperation/legacy/<nom>.sql.
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly RechauffeurCache $rechauffeur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dossier', null, InputOption::VALUE_REQUIRED, 'Dossier des exports TSV (sortie de exporter-legacy.sh)')
            ->addOption('etape', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'N\'exécuter que ces commandes (ex. app:import:newsletter)')
            ->addOption('verifier', null, InputOption::VALUE_NONE, 'Vérifier le dossier et les préalables sans rien écrire')
            ->addOption('clore', null, InputOption::VALUE_NONE, 'Inscrire la bascule au journal : plus aucun rejeu possible ensuite');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $bascule = $this->connection->fetchAssociative(
            'SELECT lancee_le FROM recuperation_legacy WHERE statut = ? ORDER BY id DESC LIMIT 1',
            [RecuperationLegacy::BASCULE],
        );

        if ($bascule !== false) {
            $io->error([
                sprintf('La bascule a été inscrite le %s : l\'application est la source, un rejeu écraserait ses écritures.', $bascule['lancee_le']),
                'Pour rejouer malgré tout, supprimer sciemment cette ligne de recuperation_legacy.',
            ]);

            return Command::FAILURE;
        }

        if ($input->getOption('clore')) {
            return $this->clore($io);
        }

        $dossier = rtrim((string) $input->getOption('dossier'), '/');
        if ($dossier === '' || !is_dir($dossier)) {
            $io->error('--dossier=… : le dossier des exports TSV est requis (bin/recuperation/exporter-legacy.sh).');

            return Command::INVALID;
        }

        $etapes = $this->etapes((array) $input->getOption('etape'));
        if ($etapes === null) {
            $io->error('Étape inconnue. Étapes : ' . implode(', ', array_column(self::ETAPES, 0)));

            return Command::INVALID;
        }

        if (!$this->verifierPrealables($io, $dossier, $etapes)) {
            return Command::FAILURE;
        }

        if ($input->getOption('verifier')) {
            $io->success('Dossier complet et préalables réunis. Rien n\'a été écrit.');

            return Command::SUCCESS;
        }

        return $this->recuperer($io, $output, $dossier, $etapes);
    }

    /**
     * @param list<array{0: string, 1: array<string, string>, 2: string}> $etapes
     */
    private function recuperer(SymfonyStyle $io, OutputInterface $output, string $dossier, array $etapes): int
    {
        $this->connection->insert('recuperation_legacy', [
            'statut' => RecuperationLegacy::EN_COURS,
            'lancee_le' => date('Y-m-d H:i:s'),
            'dossier' => $dossier,
        ]);
        $journal = (int) $this->connection->lastInsertId();
        $manifeste = $this->manifeste($dossier);

        $bilan = [];
        $sansBilan = [];
        $anomalies = [];
        $debut = microtime(true);

        foreach ($etapes as [$nom, $fichiers, $libelle]) {
            $io->section($libelle);

            $commande = $this->getApplication()?->find($nom);
            // Les commandes déclarées par #[AsCommand] arrivent enveloppées
            // (chargement paresseux) : c'est l'instance réelle qui tient le bilan.
            if ($commande instanceof LazyCommand) {
                $commande = $commande->getCommand();
            }
            $arguments = array_map(static fn (string $f) => $dossier . '/' . $f, $fichiers);

            try {
                $code = $commande->run(new ArrayInput($arguments), $output);
            } catch (\Throwable $e) {
                $io->error(sprintf('%s : %s', $nom, $e->getMessage()));
                $code = Command::FAILURE;
            }

            // Les imports anciens (votes, ventilations) ne consignent pas de
            // bilan : on garde au moins, au tableau final, ce que l'export leur
            // a donné à lire, et leur propre sortie dit le reste.
            if (!$commande instanceof ImportLegacyCommand) {
                foreach ($fichiers as $fichier) {
                    $sansBilan[] = [$libelle, $fichier, $manifeste[$fichier] ?? '?'];
                }
            }

            foreach ($commande instanceof ImportLegacyCommand ? $commande->bilans() : [] as $b) {
                $b['commande'] = $nom;
                $bilan[] = $b;

                if ($b['lues'] !== $b['ecrites'] + array_sum($b['ecartees'])) {
                    $anomalies[] = sprintf('%s : %d ligne(s) ni écrite(s) ni écartée(s)', $b['etape'], $b['lues'] - $b['ecrites'] - array_sum($b['ecartees']));
                }
            }

            if ($code !== Command::SUCCESS) {
                $this->terminer($journal, RecuperationLegacy::ECHEC, $bilan);
                $io->error(sprintf('Étape « %s » en échec (code %d) — récupération interrompue. Rien de ce qui suit n\'a été joué.', $nom, $code));

                return $code === Command::INVALID ? Command::FAILURE : $code;
            }
        }

        $this->terminer($journal, RecuperationLegacy::REUSSIE, $bilan);

        $io->section('Bilan');
        $io->table(
            ['Étape', 'Lues', 'Écrites', 'Écartées', 'Pourquoi'],
            array_map(static fn (array $b) => [
                $b['etape'],
                $b['lues'],
                $b['ecrites'],
                array_sum($b['ecartees']),
                implode(' ; ', array_map(static fn (string $r, int $n) => $n . ' ' . $r, array_keys($b['ecartees']), $b['ecartees'])),
            ], $bilan),
        );

        if ($sansBilan !== []) {
            $io->text('Sans bilan consigné (voir la sortie de l\'étape : lues, écrites, ignorées) :');
            $io->table(['Étape', 'Fichier', 'Lignes exportées'], $sansBilan);
        }

        $ecartsManifeste = $this->comparerAuManifeste($bilan, $manifeste, $etapes);
        $anomalies = array_merge($anomalies, $ecartsManifeste);

        // Décryptages, explications, articles, comptes : des pages publiques
        // changent d'un coup. Même règle qu'après une publication.
        // La récupération est faite et journalisée : un échec de la purge ne
        // doit pas la faire passer pour ratée. On le dit, et on donne la main.
        try {
            $this->rechauffeur->apresPublication(tout: true);
        } catch (\Throwable $e) {
            $io->warning(sprintf('Purge du cache HTTP impossible (%s) : lancer app:cache:rechauffer.', $e->getMessage()));
        }

        if ($anomalies !== []) {
            $io->warning(array_merge(['Récupération jouée, mais le compte ne tombe pas juste :'], $anomalies));

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Récupération terminée en %d s (journal n° %d). Après la bascule : app:recuperation:legacy --clore.',
            (int) (microtime(true) - $debut),
            $journal,
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<string> $choisies
     *
     * @return list<array{0: string, 1: array<string, string>, 2: string}>|null
     */
    private function etapes(array $choisies): ?array
    {
        if ($choisies === []) {
            return self::ETAPES;
        }

        $etapes = array_values(array_filter(self::ETAPES, static fn (array $e) => \in_array($e[0], $choisies, true)));

        return \count($etapes) === \count(array_unique($choisies)) ? $etapes : null;
    }

    /**
     * @param list<array{0: string, 1: array<string, string>, 2: string}> $etapes
     */
    private function verifierPrealables(SymfonyStyle $io, string $dossier, array $etapes): bool
    {
        $problemes = [];

        foreach ($etapes as [$nom, $fichiers]) {
            if ($this->getApplication()?->has($nom) !== true) {
                $problemes[] = sprintf('commande %s introuvable', $nom);
            }
            foreach ($fichiers as $fichier) {
                if (!is_readable($dossier . '/' . $fichier)) {
                    $problemes[] = sprintf('fichier %s absent (%s)', $fichier, $nom);
                }
            }
        }

        // Tout se rattache aux députés et aux scrutins : sans le sync, chaque
        // étape écarterait ses lignes une à une, et le bilan le dirait trop tard.
        foreach (['depute' => 'députés', 'scrutin' => 'scrutins'] as $table => $libelle) {
            if ((int) $this->connection->fetchOne(sprintf('SELECT COUNT(*) FROM %s', $table)) === 0) {
                $problemes[] = sprintf('aucun %s en base : lancer app:sync:quotidien d\'abord', rtrim($libelle, 's'));
            }
        }

        // Le sync ne tient que la 17e. Les amendements clos, leurs résumés et
        // les votes MODEM se rattachent à des scrutins des 15e et 16e, qui
        // viennent des dépôts figés : sans eux, ces étapes écarteraient tout.
        $closes = ['app:import:amendements-clos', 'app:import:exposes', 'app:import:votes', 'app:import:vote-groupes'];
        if (array_intersect($closes, array_column($etapes, 0)) !== []) {
            foreach ([15, 16] as $legislature) {
                if ((int) $this->connection->fetchOne('SELECT COUNT(*) FROM scrutin WHERE legislature = ?', [$legislature]) === 0) {
                    $problemes[] = sprintf(
                        'aucun scrutin de la %de législature : app:tricoteuses:sync --depot=scrutins-%s puis app:import:scrutins --depot=scrutins-%2$s --tout',
                        $legislature,
                        $legislature === 15 ? 'xv' : 'xvi',
                    );
                }
            }
        }

        if (!is_file($dossier . '/MANIFESTE.tsv')) {
            $io->note('Pas de MANIFESTE.tsv dans le dossier : les lignes lues ne seront pas comparées à l\'export.');
        }

        if ($problemes !== []) {
            $io->error(array_merge(['Récupération impossible :'], $problemes));

            return false;
        }

        return true;
    }

    /** @return array<string, int> fichier → lignes de données annoncées par l'export */
    private function manifeste(string $dossier): array
    {
        $chemin = $dossier . '/MANIFESTE.tsv';
        if (!is_file($chemin)) {
            return [];
        }

        $lignes = [];
        foreach (\array_slice(file($chemin, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [], 1) as $ligne) {
            [$fichier, $nombre] = explode("\t", $ligne) + [1 => '0'];
            $lignes[$fichier] = (int) $nombre;
        }

        return $lignes;
    }

    /**
     * Les bilans viennent dans l'ordre des fichiers de chaque étape (une entrée
     * par fichier lu) : on les apparie ainsi au manifeste de l'export.
     *
     * @param list<array<string, mixed>>                                   $bilan
     * @param array<string, int>                                           $manifeste
     * @param list<array{0: string, 1: array<string, string>, 2: string}> $etapes
     *
     * @return list<string>
     */
    private function comparerAuManifeste(array $bilan, array $manifeste, array $etapes): array
    {
        if ($manifeste === []) {
            return [];
        }

        $ecarts = [];
        foreach ($etapes as [$nom, $fichiers]) {
            $bilans = array_values(array_filter($bilan, static fn (array $b) => $b['commande'] === $nom));
            foreach (array_values($fichiers) as $i => $fichier) {
                if (!isset($bilans[$i], $manifeste[$fichier])) {
                    continue;
                }
                if ($bilans[$i]['lues'] !== $manifeste[$fichier]) {
                    $ecarts[] = sprintf('%s : %d ligne(s) lue(s), %d exportée(s)', $fichier, $bilans[$i]['lues'], $manifeste[$fichier]);
                }
            }
        }

        return $ecarts;
    }

    /** @param list<array<string, mixed>> $bilan */
    private function terminer(int $journal, string $statut, array $bilan): void
    {
        $this->connection->update('recuperation_legacy', [
            'statut' => $statut,
            'terminee_le' => date('Y-m-d H:i:s'),
            'bilan' => json_encode($bilan, \JSON_UNESCAPED_UNICODE),
        ], ['id' => $journal]);
    }

    private function clore(SymfonyStyle $io): int
    {
        $derniere = $this->connection->fetchAssociative(
            'SELECT id, statut, terminee_le FROM recuperation_legacy WHERE statut <> ? ORDER BY id DESC LIMIT 1',
            [RecuperationLegacy::BASCULE],
        );

        if ($derniere === false || $derniere['statut'] !== RecuperationLegacy::REUSSIE) {
            $io->warning('La dernière récupération n\'a pas abouti (ou il n\'y en a eu aucune). La bascule est inscrite quand même : c\'est un fait, pas une vérification.');
        }

        $this->connection->insert('recuperation_legacy', [
            'statut' => RecuperationLegacy::BASCULE,
            'lancee_le' => date('Y-m-d H:i:s'),
            'terminee_le' => date('Y-m-d H:i:s'),
        ]);

        $io->success('Bascule inscrite : app:recuperation:legacy refusera désormais de tourner.');

        return Command::SUCCESS;
    }
}
