<?php

namespace App\Command;

use App\Election\Municipales;
use App\Election\SituationListes;
use App\Http\RechauffeurCache;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Importe les résultats des municipales 2026, commune par commune et tour par
 * tour, depuis les fichiers que publie le ministère de l'Intérieur.
 *
 * C'est le portage des deux scripts du dépôt `datanfr/scripts` qui alimentent
 * l'application d'origine (`get_electoral_results.php`,
 * `get_electoral_candidates.php`), lus à la même source publique :
 * <https://www.resultats-elections.interieur.gouv.fr/telechargements/MUN2026/>.
 * Par département et par tour, quatre fichiers XML :
 *
 * - `resultatsT{n}/{dpt}/R{n}{dpt}CIR.xml` : participation et score de chaque
 *   liste, par circonscription électorale — c'est-à-dire par commune ;
 * - `resultatsT{n}/{dpt}/INDEX{n}{dpt}.xml` : état du dépouillement
 *   (`PourcentageBureauxDeVoteSaisis`) et siège pourvu ou non (`Pourvu`) ;
 * - `candidatsT2/{dpt}/C2{dpt}.xml` : listes déposées pour le second tour,
 *   avec leurs colistiers. Elles ne s'affichent pas — les résultats du second
 *   tour en tiennent lieu — mais disent ce que chaque liste du premier tour est
 *   devenue ({@see SituationListes}) ;
 * - les colistiers du premier tour, que la même règle consulte à défaut de
 *   trouver la tête de liste, sont pris dans le fichier de résultats du premier
 *   tour, qui les publie tous : inutile de télécharger `candidatsT1` (110 Mo)
 *   pour y relire les mêmes noms.
 *
 * Les fichiers sont gardés sous `var/municipales/`, à la même arborescence que
 * sur le serveur du ministère : un second passage ne retélécharge rien, sauf
 * `--rafraichir`. Les résultats sont définitifs depuis mai 2026 ; la commande
 * n'a pas sa place dans le sync quotidien.
 *
 * **Ce que l'import laisse de côté**, et que le bilan compte :
 *
 * - les scrutins de secteur de Paris, Lyon et Marseille, publiés dans d'autres
 *   fichiers (`R1{dpt}{commune}.xml`) : le site ne montre que le scrutin à
 *   l'échelle de la ville, celui du conseil municipal ;
 * - Saint-Pierre-et-Miquelon (975) n'a pas de dossier au second tour, ni la
 *   Mayenne : aucune de leurs communes n'en a eu ;
 * - les communes sans liste déclarée — l'index du ministère les connaît, mais
 *   n'y publie ni participation ni résultat. Elles gardent leur page actuelle.
 */
#[AsCommand(
    name: 'app:import:municipales',
    description: 'Importe les résultats des municipales 2026 depuis les fichiers du ministère de l\'Intérieur.',
)]
class ImportMunicipalesCommand extends Command
{
    public const SOURCE = 'https://www.resultats-elections.interieur.gouv.fr/telechargements/MUN2026';

    private const TAILLE_LOT = 1000;

    private const AGENT = 'Datan (+https://datan.fr)';

    private const COLONNES_RESULTAT = [
        'tour', 'code_commune', 'numero_panneau', 'libelle_liste', 'voix',
        'rapport_inscrits', 'rapport_exprimes', 'code_nuance', 'sexe', 'nom',
        'prenom', 'sieges', 'qualifiee', 'situation', 'fusion_role', 'fusion_tete',
    ];

    private const COLONNES_PARTICIPATION = [
        'tour', 'code_commune', 'inscrits', 'abstentions', 'votants', 'blancs',
        'nuls', 'exprimes', 'pct_saisis', 'pourvu',
    ];

    /** @var array<string, int> */
    private array $bilan = [];

    /** @var array<string, bool> codes du ministère sans commune au référentiel */
    private array $horsReferentiel = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $client,
        private readonly RechauffeurCache $cacheHttp,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('departement', null, InputOption::VALUE_REQUIRED, 'Un seul département (code du ministère : 01, 2A, 976…)')
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, 'Dossier où garder les fichiers téléchargés', 'var/municipales')
            ->addOption('rafraichir', null, InputOption::VALUE_NONE, 'Retélécharger les fichiers déjà présents dans le cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des municipales 2026');

        $cache = (string) $input->getOption('cache');
        if (!str_starts_with($cache, '/')) {
            $cache = $this->racineProjet . '/' . $cache;
        }
        $rafraichir = (bool) $input->getOption('rafraichir');

        $departements = [
            1 => $this->departements('resultatsT1'),
            2 => $this->departements('resultatsT2'),
        ];
        $listesT2 = $this->departements('candidatsT2');

        $seul = $input->getOption('departement');
        $tous = $departements[1];
        if ($seul !== null) {
            $tous = array_values(array_intersect($tous, [strtoupper((string) $seul)]));
        }

        $codesConnus = array_change_key_case(
            array_fill_keys($this->connection->fetchFirstColumn('SELECT code_insee FROM commune'), true),
        );

        $io->progressStart(\count($tous));

        foreach ($tous as $dpt) {
            $fichier = fn (string $chemin) => $this->fichier($cache, $chemin, $rafraichir);

            $t1 = $this->lisResultats($fichier("resultatsT1/{$dpt}/R1{$dpt}CIR.xml"), 1);
            $index1 = $this->lisIndex($fichier("resultatsT1/{$dpt}/INDEX1{$dpt}.xml"));

            $t2 = ['communes' => [], 'colistiers' => []];
            $index2 = [];
            if (\in_array($dpt, $departements[2], true)) {
                $t2 = $this->lisResultats($fichier("resultatsT2/{$dpt}/R2{$dpt}CIR.xml"), 2);
                $index2 = $this->lisIndex($fichier("resultatsT2/{$dpt}/INDEX2{$dpt}.xml"));
            }

            $candidatsT2 = \in_array($dpt, $listesT2, true)
                ? $this->lisListes($fichier("candidatsT2/{$dpt}/C2{$dpt}.xml"))
                : [];

            // Un département à la fois, en transaction : les listes d'une
            // commune y sont effacées puis réécrites ({@see ecris()}).
            $this->connection->transactional(function () use ($t1, $t2, $index1, $index2, $candidatsT2, $codesConnus): void {
                $this->ecris(1, $t1['communes'], $index1, $this->situations($t1, $index1, $t2['communes'], $candidatsT2), $codesConnus);
                $this->ecris(2, $t2['communes'], $index2, [], $codesConnus);
            });

            // Communes que l'index connaît sans qu'un résultat soit publié :
            // au premier tour, aucune liste ne s'est présentée (68 en 2026) ;
            // au second, c'est l'immense majorité, pourvue dès le premier.
            // Restent au second les communes sans liste et une quarantaine de
            // petites communes dont la liste élue était incomplète : un siège
            // reste vacant (`Pourvu` = `NON`), sans second tour pour autant.
            $this->compte('t1_index_sans_resultat', \count(array_diff_key($index1, $t1['communes'])));
            foreach (array_diff_key($index2, $t2['communes']) as $etat) {
                $this->compte($etat['pourvu'] === 'T1' ? 't2_index_pourvu_t1' : 't2_index_sans_resultat');
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $b = fn (string $cle) => $this->bilan[$cle] ?? 0;

        $io->table(['', 'Premier tour', 'Second tour'], [
            ['Communes lues (fichier de résultats)', $b('t1_communes'), $b('t2_communes')],
            ['  dont absentes du référentiel des communes', $b('t1_hors_referentiel'), $b('t2_hors_referentiel')],
            ['Listes écrites', $b('t1_listes'), $b('t2_listes')],
            ['Communes de l\'index sans résultat : pourvues au 1er tour', '', $b('t2_index_pourvu_t1')],
            ['Communes de l\'index sans résultat : autres', $b('t1_index_sans_resultat'), $b('t2_index_sans_resultat')],
            ['Communes pourvues à ce tour (index)', $b('t1_pourvu'), $b('t2_pourvu')],
        ]);
        $io->text(sprintf(
            'Situations entre les deux tours : %d maintiens, %d fusions, %d désistements.',
            $b('situation_maintien'), $b('situation_fusion'), $b('situation_desistement'),
        ));

        if ($b('sans_panneau') > 0) {
            $io->text(sprintf(
                '%d listes sans numéro de panneau (Nouvelle-Calédonie), rangées sous leur numéro de liste.',
                $b('sans_panneau'),
            ));
        }

        if ($b('t1_hors_referentiel') + $b('t2_hors_referentiel') > 0) {
            $io->note(sprintf(
                'Codes sans commune au référentiel, écrits quand même mais sans page pour les montrer : %s.',
                implode(', ', array_slice(array_keys(array_filter($this->horsReferentiel)), 0, 40)),
            ));
        }

        $io->success(sprintf(
            '%d lignes de résultats et %d lignes de participation en base.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM municipale_resultat'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM municipale_participation'),
        ));

        // Les pages de résultats sont en cache un jour : sans purge, l'import
        // n'apparaîtrait que le lendemain. Le réchauffage reste à
        // `app:cache:rechauffer`, qu'un import ponctuel n'a pas à attendre.
        $this->cacheHttp->purger();
        $io->text('Cache HTTP purgé ; lancer app:cache:rechauffer pour le remplir.');

        return Command::SUCCESS;
    }

    /**
     * Départements publiés pour un jeu de fichiers, d'après l'index du
     * répertoire. Le motif est celui des scripts d'origine : deux ou trois
     * chiffres, ou 2A/2B.
     *
     * @return list<string>
     */
    private function departements(string $repertoire): array
    {
        $html = $this->client->request('GET', self::SOURCE . '/' . $repertoire . '/', [
            'headers' => ['User-Agent' => self::AGENT],
        ])->getContent();

        preg_match_all('#href="((?:\d{2,3}|2A|2B))/"#i', $html, $m);

        $codes = array_map('strtoupper', $m[1]);
        sort($codes, SORT_STRING);

        return array_values(array_unique($codes));
    }

    private function fichier(string $cache, string $chemin, bool $rafraichir): \SimpleXMLElement
    {
        $local = $cache . '/' . $chemin;

        if ($rafraichir || !is_file($local)) {
            if (!is_dir(\dirname($local))) {
                mkdir(\dirname($local), 0775, true);
            }

            $contenu = $this->client->request('GET', self::SOURCE . '/' . $chemin, [
                'headers' => ['User-Agent' => self::AGENT],
            ])->getContent();

            file_put_contents($local . '.part', $contenu);
            rename($local . '.part', $local);
        }

        $xml = simplexml_load_file($local);

        if ($xml === false) {
            throw new \RuntimeException(sprintf('XML illisible : « %s ».', $chemin));
        }

        return $xml;
    }

    /**
     * Participation et listes d'un tour, par commune, et noms des colistiers
     * par commune et par panneau.
     *
     * Le fichier du second tour reprend aussi le premier : seul le tour
     * demandé est retenu, comme dans le script d'origine.
     *
     * @return array{
     *     communes: array<string, array{participation: array<string, int|null>, listes: list<array<string, mixed>>}>,
     *     colistiers: array<string, array<int, list<string>>>,
     * }
     */
    private function lisResultats(\SimpleXMLElement $xml, int $tour): array
    {
        $communes = [];
        $colistiers = [];

        foreach ($xml->xpath('//Circonscription') ?: [] as $circonscription) {
            $code = $this->texte($circonscription->CodCirElec);

            foreach ($circonscription->Tours->Tour ?? [] as $t) {
                if ($this->entier($t->NumTour) !== $tour || $code === null) {
                    continue;
                }

                $m = $t->Mentions;
                $listes = [];

                foreach ($t->Resultats->Listes->Liste ?? [] as $liste) {
                    $panneau = $this->panneau($liste);
                    $tete =$liste->Candidats->Candidat[0] ?? null;
                    $etat = $tete === null ? '' : strtoupper(trim($tete->EluCM . ' ' . $tete->EluCC));

                    $listes[] = [
                        'panneau' => $panneau,
                        'libelle' => $this->texte($liste->NomListe),
                        'voix' => $this->entier($liste->NbVoix),
                        'rapport_inscrits' => $this->decimal($liste->RapportInscrits),
                        'rapport_exprimes' => $this->decimal($liste->RapportExprimes),
                        'nuance' => $this->texte($liste->CodNuaListe),
                        'sexe' => $this->sexe($liste->CiviliteTeteListe),
                        'nom' => $this->texte($liste->NomTeteListe),
                        'prenom' => $this->texte($liste->PrenomTeteListe),
                        'sieges' => $this->entier($liste->NbSiegesCM ?? $liste->NbSieges),
                        // Qualifiée si la tête de liste l'est, au conseil
                        // municipal ou communautaire : règle du script.
                        'qualifiee' => (int) str_contains($etat, 'QUALIFIE'),
                    ];

                    foreach ($liste->Candidats->Candidat ?? [] as $candidat) {
                        $colistiers[$code][$panneau][] = trim($candidat->PrenomPsn . ' ' . $candidat->NomPsn);
                    }
                }

                $communes[$code] = [
                    'participation' => [
                        'inscrits' => $this->entier($m->Inscrits->Nombre ?? null),
                        'abstentions' => $this->entier($m->Abstentions->Nombre ?? null),
                        'votants' => $this->entier($m->Votants->Nombre ?? null),
                        'blancs' => $this->entier($m->Blancs->Nombre ?? null),
                        'nuls' => $this->entier($m->Nuls->Nombre ?? null),
                        'exprimes' => $this->entier($m->Exprimes->Nombre ?? null),
                    ],
                    'listes' => $listes,
                ];
            }
        }

        return ['communes' => $communes, 'colistiers' => $colistiers];
    }

    /**
     * État du dépouillement par commune. Le script d'origine écrit `NON` quand
     * l'index ne dit rien.
     *
     * @return array<string, array{pct_saisis: ?string, pourvu: string}>
     */
    private function lisIndex(\SimpleXMLElement $xml): array
    {
        $index = [];

        foreach ($xml->xpath('//Departement/Circonscriptions/Circonscription') ?: [] as $c) {
            $code = $this->texte($c->CodCirElec);
            if ($code !== null) {
                $index[$code] = [
                    'pct_saisis' => $this->decimal($c->PourcentageBureauxDeVoteSaisis),
                    'pourvu' => $this->texte($c->Pourvu) ?? 'NON',
                ];
            }
        }

        return $index;
    }

    /**
     * Listes déposées au second tour, par commune puis par panneau, dans
     * l'ordre du panneau.
     *
     * La tête de liste s'écrit comme sur le site : prénom, puis nom remis en
     * casse de titre (« Benoît Payan »). C'est sous cette forme qu'elle
     * apparaît dans la situation d'une liste fusionnée.
     *
     * @return array<string, array<int, array{tete: ?string, colistiers: list<string>}>>
     */
    private function lisListes(\SimpleXMLElement $xml): array
    {
        $listes = [];

        foreach ($xml->xpath('//Circonscription') ?: [] as $circonscription) {
            $code = $this->texte($circonscription->CodCirElec);
            if ($code === null) {
                continue;
            }

            foreach ($circonscription->Listes->Liste ?? [] as $liste) {
                $panneau = $this->panneau($liste);
                $listes[$code][$panneau] ??= ['tete' => null, 'colistiers' => []];

                foreach ($liste->Candidats->Candidat ?? [] as $candidat) {
                    $prenom = trim((string) $candidat->PrenomPsn);
                    $nom = trim((string) $candidat->NomPsn);

                    if ($this->entier($candidat->NumOrdCand) === 1) {
                        $listes[$code][$panneau]['tete'] = $prenom . ' ' . mb_convert_case(mb_strtolower($nom), MB_CASE_TITLE);
                    }
                    $listes[$code][$panneau]['colistiers'][] = $prenom . ' ' . $nom;
                }
            }

            if (isset($listes[$code])) {
                ksort($listes[$code], SORT_NUMERIC);
            }
        }

        return $listes;
    }

    /**
     * Situation de chaque liste du premier tour, pour les communes qui ont eu
     * un second tour.
     *
     * « Second tour » au sens du site : aucune liste n'a passé la moitié des
     * suffrages exprimés au premier ({@see Municipales::pourvuAuPremierTour()},
     * qui dit aussi l'exception corrigée).
     *
     * @param array{communes: array<string, array<string, mixed>>, colistiers: array<string, array<int, list<string>>>} $t1
     * @param array<string, array{pct_saisis: ?string, pourvu: string}>                                               $index1
     * @param array<string, mixed>                                                                                    $communesT2
     * @param array<string, array<int, array{tete: ?string, colistiers: list<string>}>>                               $candidatsT2
     *
     * @return array<string, array<int, array{situation: string, role: ?string, tete: ?string}>>
     */
    private function situations(array $t1, array $index1, array $communesT2, array $candidatsT2): array
    {
        $situations = [];

        foreach ($t1['communes'] as $code => $commune) {
            if (Municipales::pourvuAuPremierTour(
                array_column($commune['listes'], 'voix'),
                $index1[$code]['pourvu'] ?? null,
                isset($communesT2[$code]),
            )) {
                continue;
            }

            $premierTour = array_map(fn (array $l) => [
                'panneau' => $l['panneau'],
                'tete' => trim($l['prenom'] . ' ' . $l['nom']),
                'score' => Municipales::score($l['rapport_exprimes'], $l['voix'], $commune['listes']),
            ], Municipales::ordonne($commune['listes']));

            $situations[$code] = SituationListes::calcule(
                $premierTour,
                $candidatsT2[$code] ?? [],
                $t1['colistiers'][$code] ?? [],
            );
        }

        return $situations;
    }

    /**
     * @param array<string, array<string, mixed>>                                         $communes
     * @param array<string, array{pct_saisis: ?string, pourvu: string}>                   $index
     * @param array<string, array<int, array{situation: string, role: ?string, tete: ?string}>> $situations
     * @param array<string, bool>                                                         $codesConnus
     */
    private function ecris(int $tour, array $communes, array $index, array $situations, array $codesConnus): void
    {
        $resultats = [];
        $participations = [];

        // Les listes d'une commune publiée se remplacent en bloc : un upsert
        // seul laisserait en place une liste que le fichier ne porte plus —
        // ainsi des panneaux « 0 » d'un import antérieur à la correction de
        // la Nouvelle-Calédonie ({@see panneau()}). Une commune absente du
        // fichier, elle, n'est pas touchée.
        foreach (array_chunk(array_keys($communes), self::TAILLE_LOT) as $codes) {
            $this->connection->executeStatement(
                'DELETE FROM municipale_resultat WHERE tour = ? AND code_commune IN (?)',
                [$tour, array_map('strval', $codes)],
                [ParameterType::INTEGER, ArrayParameterType::STRING],
            );
        }

        foreach ($communes as $code => $commune) {
            $this->compte("t{$tour}_communes");

            // Le référentiel écrit la Corse en minuscules (`2a004`) : la
            // comparaison se fait en minuscules des deux côtés.
            if (!isset($codesConnus[strtolower($code)])) {
                $this->compte("t{$tour}_hors_referentiel");
                $this->horsReferentiel[$code] = true;
            }

            $etat = $index[$code] ?? ['pct_saisis' => null, 'pourvu' => null];
            if ($etat['pourvu'] === 'T' . $tour) {
                $this->compte("t{$tour}_pourvu");
            }

            $p = $commune['participation'];
            $participations[] = [
                $tour, $code, $p['inscrits'], $p['abstentions'], $p['votants'],
                $p['blancs'], $p['nuls'], $p['exprimes'], $etat['pct_saisis'], $etat['pourvu'],
            ];

            foreach ($commune['listes'] as $l) {
                $situation = $situations[$code][$l['panneau']] ?? null;
                if ($situation !== null) {
                    $this->compte('situation_' . $situation['situation']);
                }

                $resultats[] = [
                    $tour, $code, $l['panneau'], $l['libelle'], $l['voix'],
                    $l['rapport_inscrits'], $l['rapport_exprimes'], $l['nuance'], $l['sexe'],
                    $l['nom'], $l['prenom'], $l['sieges'], $l['qualifiee'],
                    $situation['situation'] ?? null, $situation['role'] ?? null, $situation['tete'] ?? null,
                ];
                $this->compte("t{$tour}_listes");

                if (\count($resultats) >= self::TAILLE_LOT) {
                    $this->upsert('municipale_resultat', self::COLONNES_RESULTAT, $resultats, 3);
                    $resultats = [];
                }
            }
        }

        $this->upsert('municipale_resultat', self::COLONNES_RESULTAT, $resultats, 3);
        $this->upsert('municipale_participation', self::COLONNES_PARTICIPATION, $participations, 2);
    }

    /**
     * Upsert par lot. Toutes les colonnes hors clé sont réécrites : le
     * ministère est la seule source de ces chiffres, et une colonne qu'il cesse
     * de publier — la situation recalculée d'une liste — doit pouvoir
     * redevenir vide.
     *
     * @param list<string>      $colonnes
     * @param list<list<mixed>> $lignes
     */
    private function upsert(string $table, array $colonnes, array $lignes, int $tailleCle): void
    {
        if ($lignes === []) {
            return;
        }

        $tuple = '(' . implode(', ', array_fill(0, \count($colonnes), '?')) . ')';

        $this->connection->executeStatement(
            sprintf(
                'INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s',
                $table,
                implode(', ', $colonnes),
                implode(', ', array_fill(0, \count($lignes), $tuple)),
                implode(', ', array_map(
                    static fn (string $c) => sprintf('%1$s = VALUES(%1$s)', $c),
                    \array_slice($colonnes, $tailleCle),
                )),
            ),
            array_merge(...$lignes),
        );
    }

    /**
     * Numéro de panneau d'une liste, qui l'identifie dans sa commune.
     *
     * Quatre communes de Nouvelle-Calédonie (Belep, Farino, Moindou,
     * Sarraméa) n'ont pas de panneau : le ministère y laisse
     * `NumPanneauListe` vide. Le script d'origine l'écrit 0 pour toutes leurs
     * listes, qui s'écrasent alors l'une l'autre sur la clé de la table : le
     * site n'en montre que la dernière lue, et la déclare gagnante — à Belep,
     * « La liste dirigée par Gianni TEAMBOUEON a remporté les élections […]
     * avec 12,46 % des voix » (vérifié le 2026-11-16), quand la liste de tête
     * en a 49 %. On se rabat sur le numéro d'ordre de la liste (`NumListe`),
     * unique dans la commune.
     */
    private function panneau(\SimpleXMLElement $liste): int
    {
        if ($this->entier($liste->NumPanneauListe) === null) {
            $this->compte('sans_panneau');

            return (int) $this->entier($liste->NumListe);
        }

        return (int) $this->entier($liste->NumPanneauListe);
    }

    private function compte(string $cle, int $n = 1): void
    {
        $this->bilan[$cle] = ($this->bilan[$cle] ?? 0) + $n;
    }

    private function texte(mixed $valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return $valeur === '' ? null : $valeur;
    }

    private function entier(mixed $valeur): ?int
    {
        $valeur = $this->texte($valeur);

        return $valeur === null ? null : (int) $valeur;
    }

    /** « 39,41 » du ministère, rendu « 39.41 » pour la colonne décimale. */
    private function decimal(mixed $valeur): ?string
    {
        $valeur = $this->texte($valeur);
        if ($valeur === null) {
            return null;
        }
        $valeur = str_replace(',', '.', $valeur);

        return is_numeric($valeur) ? $valeur : null;
    }

    private function sexe(mixed $civilite): ?string
    {
        return match (strtoupper(trim((string) $civilite))) {
            'M', 'M.' => 'M',
            'MME', 'MME.', 'MADAME' => 'F',
            default => null,
        };
    }
}
