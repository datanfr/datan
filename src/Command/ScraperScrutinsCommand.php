<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Rattrapage des scrutins que {@see LienScrutinsCommand} ne sait pas rattacher,
 * en lisant leur page sur le site de l'Assemblée.
 *
 * C'est la seule commande du pipeline qui sorte sur le réseau public, et elle
 * n'est pas branchée dans `app:sync:quotidien`. Elle n'existe que parce que
 * l'Assemblée ne publie pas dans son open data le lien entre un scrutin et
 * l'amendement mis aux voix : l'open data en rend 98 %, le reste n'est lisible
 * que sur la page du scrutin.
 *
 * Ce n'est donc pas un aspirateur. Après le premier rattrapage — une centaine
 * de pages — il ne reste que les quelques scrutins de la veille que l'open data
 * n'a pas encore servis. D'où le plafond par lancement, le délai entre deux
 * requêtes, et la mémoire des visites infructueuses : sans elle, la commande
 * redemanderait chaque nuit les mêmes pages sans rien y trouver.
 *
 * L'application d'origine fait le même travail (`scripts/daily.php`,
 * `dossiersVotes()` et `votesAmendements()`) mais retient le dernier lien
 * d'amendement rencontré sur la page, sans le confronter à l'objet du scrutin.
 * C'est ainsi qu'elle rattache l'amendement sous-amendé au lieu du
 * sous-amendement voté. Ici, seul le lien dont le numéro correspond à celui
 * qu'annonce l'objet est retenu.
 */
#[AsCommand(
    name: 'app:scraper:scrutins',
    description: 'Rattrape sur le site de l\'Assemblée les scrutins dont l\'open data ne donne ni le dossier ni l\'amendement.',
)]
class ScraperScrutinsCommand extends Command
{
    private const NATURES_AMENDEMENT = ['amendement', 'sous-amendement'];

    private const PAGE_SCRUTIN = 'https://www.assemblee-nationale.fr/dyn/%d/scrutins/%d';

    /**
     * Une page de scrutin visitée sans rien y trouver n'est pas redemandée
     * avant ce délai. Un scrutin sans amendement — un vote sur un article, une
     * motion — n'en aura jamais ; un scrutin trop récent peut en gagner un
     * quand l'Assemblée complète sa page.
     */
    private const JOURS_AVANT_NOUVELLE_VISITE = 30;

    /** L'exploitant doit être joignable : c'est la moindre des politesses. */
    private const AGENT = 'DatanBot/1.0 (+https://datan.fr/ ; rattachement scrutin-amendement)';

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $client,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('legislature', null, InputOption::VALUE_REQUIRED, 'Législature à rattraper', '17')
            ->addOption('plafond', null, InputOption::VALUE_REQUIRED, 'Nombre maximum de scrutins visités par lancement', '40')
            ->addOption('delai', null, InputOption::VALUE_REQUIRED, 'Millisecondes entre deux requêtes', '1500')
            ->addOption('relance', null, InputOption::VALUE_NONE, 'Revisite aussi les scrutins déjà visités sans résultat')
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Visite et rend compte sans rien écrire en base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $legislature = (int) $input->getOption('legislature');
        $plafond = max(1, (int) $input->getOption('plafond'));
        $delai = max(0, (int) $input->getOption('delai')) * 1000;
        $simulation = (bool) $input->getOption('simulation');

        $journal = $input->getOption('relance') ? [] : $this->journal();
        $aFaire = $this->scrutinsSansLien($legislature, $journal, $plafond);

        $io->title('Rattrapage des rattachements sur assemblee-nationale.fr');

        if ($aFaire === []) {
            $io->success('Aucun scrutin à visiter.');

            return Command::SUCCESS;
        }

        $io->text(sprintf(
            '%d scrutins à visiter%s, %s ms entre deux requêtes.',
            \count($aFaire),
            $simulation ? ' (simulation, aucune écriture)' : '',
            number_format($delai / 1000, 0, ',', ' '),
        ));

        $titresDossiers = $this->connection->fetchAllKeyValue(
            'SELECT titre_chemin, id FROM dossier WHERE titre_chemin IS NOT NULL',
        );

        $bilan = array_fill_keys(['visites', 'dossiers', 'amendements', 'refus_numero',
            'amendement_inconnu', 'bredouille', 'erreurs'], 0);

        foreach ($aFaire as $index => $scrutin) {
            if ($index > 0 && $delai > 0) {
                usleep($delai);
            }

            $url = sprintf(self::PAGE_SCRUTIN, $legislature, $scrutin['numero']);

            try {
                $html = $this->page($url);
            } catch (HttpExceptionInterface|\RuntimeException $e) {
                ++$bilan['erreurs'];
                $io->warning(sprintf('Scrutin n°%d : %s', $scrutin['numero'], $e->getMessage()));
                continue;
            }

            ++$bilan['visites'];
            $trouve = false;

            if ($scrutin['dossier_id'] === null) {
                $id = $this->dossier($html, $titresDossiers);
                if ($id !== null) {
                    $trouve = true;
                    ++$bilan['dossiers'];
                    if (!$simulation) {
                        $this->connection->executeStatement(
                            'UPDATE scrutin SET dossier_id = ? WHERE id = ? AND dossier_id IS NULL',
                            [$id, $scrutin['id']],
                        );
                    }
                }
            }

            if ($scrutin['amendement_id'] === null && \in_array($scrutin['nature_vote'], self::NATURES_AMENDEMENT, true)) {
                $issue = $this->amendement($html, (string) $scrutin['objet'], $delai);
                ++$bilan[$issue['bilan']];

                if ($issue['id'] !== null) {
                    $trouve = true;
                    if (!$simulation) {
                        $this->connection->executeStatement(
                            'UPDATE scrutin SET amendement_id = ? WHERE id = ? AND amendement_id IS NULL',
                            [$issue['id'], $scrutin['id']],
                        );
                    }
                }
                if ($issue['message'] !== null) {
                    $io->text(sprintf('  n°%d : %s', $scrutin['numero'], $issue['message']));
                }
            }

            // La visite est consignée dès qu'elle est faite, et non à la fin :
            // une interruption ne doit pas condamner la commande à refaire tout
            // le lot au lancement suivant.
            if (!$trouve && !$simulation) {
                $journal[$legislature][$scrutin['numero']] = date('Y-m-d');
                $this->ecritJournal($journal);
            }
        }

        $io->table(
            ['Issue', 'Scrutins'],
            [
                ['pages visitées', $bilan['visites']],
                ['dossiers rattachés', $bilan['dossiers']],
                ['amendements rattachés', $bilan['amendements']],
                ['refusés : numéro discordant', $bilan['refus_numero']],
                ['amendement absent de la base', $bilan['amendement_inconnu']],
                ['page sans lien d\'amendement', $bilan['bredouille']],
                ['erreurs réseau', $bilan['erreurs']],
            ],
        );

        $restants = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM scrutin
             WHERE legislature = ?
               AND ((amendement_id IS NULL AND nature_vote IN (?)) OR dossier_id IS NULL)',
            [$legislature, self::NATURES_AMENDEMENT],
            [1 => \Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $io->success(sprintf('%d scrutins restent sans rattachement complet.', $restants));

        return Command::SUCCESS;
    }

    /**
     * Les scrutins encore incomplets, les plus anciens d'abord, moins ceux
     * qu'une visite récente a laissés bredouilles.
     *
     * @param array<int, array<int, string>> $journal
     *
     * @return list<array<string, mixed>>
     */
    private function scrutinsSansLien(int $legislature, array $journal, int $plafond): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT id, numero, objet, titre, nature_vote, amendement_id, dossier_id
             FROM scrutin
             WHERE legislature = ?
               AND ((amendement_id IS NULL AND nature_vote IN (?)) OR dossier_id IS NULL)
             ORDER BY numero',
            [$legislature, self::NATURES_AMENDEMENT],
            [1 => \Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $limite = (new \DateTimeImmutable())->modify('-' . self::JOURS_AVANT_NOUVELLE_VISITE . ' days')->format('Y-m-d');
        $retenus = [];

        foreach ($lignes as $ligne) {
            $visite = $journal[$legislature][(int) $ligne['numero']] ?? null;
            if ($visite !== null && $visite > $limite) {
                continue;
            }

            $retenus[] = $ligne;

            if (\count($retenus) >= $plafond) {
                break;
            }
        }

        return $retenus;
    }

    private function page(string $url, int $delai = 0): string
    {
        if ($delai > 0) {
            usleep($delai);
        }

        $reponse = $this->client->request('GET', $url, [
            'headers' => ['User-Agent' => self::AGENT],
            'timeout' => 15,
            'max_duration' => 30,
        ]);

        if ($reponse->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf('HTTP %d', $reponse->getStatusCode()));
        }

        return $reponse->getContent();
    }

    /**
     * Identifiant du dossier législatif, reconnu par le dernier segment de son
     * URL — le `titre_chemin` que porte déjà la table `dossier`.
     *
     * @param array<string, int|string> $titresDossiers
     */
    private function dossier(string $html, array $titresDossiers): ?int
    {
        foreach ($this->liens($html, 'dossiers') as $lien) {
            $chemin = basename(parse_url($lien, \PHP_URL_PATH) ?: $lien);

            if (isset($titresDossiers[$chemin])) {
                return (int) $titresDossiers[$chemin];
            }
        }

        return null;
    }

    /**
     * Amendement mis aux voix, reconnu par son numéro.
     *
     * La page d'un scrutin porte souvent plusieurs liens d'amendement — celui
     * qu'on vote, et celui qu'un sous-amendement modifie. Seul est retenu celui
     * dont le numéro terminal correspond à celui qu'annonce l'objet du scrutin.
     * C'est cette confrontation qui manque à l'application d'origine.
     *
     * @return array{id: ?int, bilan: string, message: ?string}
     */
    private function amendement(string $html, string $objet, int $delai): array
    {
        if (preg_match('/n[°º]\s*(\d+)/iu', $objet, $m) !== 1) {
            return ['id' => null, 'bilan' => 'refus_numero', 'message' => 'objet sans numéro d\'amendement'];
        }

        $attendu = ltrim($m[1], '0');
        $liens = $this->liens($html, 'amendements');

        if ($liens === []) {
            return ['id' => null, 'bilan' => 'bredouille', 'message' => null];
        }

        $retenu = null;
        foreach ($liens as $lien) {
            $numero = ltrim(basename(parse_url($lien, \PHP_URL_PATH) ?: $lien), '0');
            if ($numero === $attendu) {
                $retenu = $lien;
                break;
            }
        }

        if ($retenu === null) {
            return [
                'id' => null,
                'bilan' => 'refus_numero',
                'message' => sprintf('objet annonce le n° %s, la page ne propose que %s', $attendu, implode(', ', array_map(
                    static fn (string $l) => basename(parse_url($l, \PHP_URL_PATH) ?: $l),
                    \array_slice($liens, 0, 4),
                ))),
            ];
        }

        // Le lien tel quel suffit le plus souvent : la table le porte déjà pour
        // les 11 197 amendements que le scraping d'origine avait relevés.
        $id = $this->connection->fetchOne('SELECT id FROM amendement WHERE href = ?', [$retenu]);
        if ($id !== false && $id !== null) {
            return ['id' => (int) $id, 'bilan' => 'amendements', 'message' => null];
        }

        // Sinon, la page de l'amendement porte son identifiant Assemblée dans le
        // lien vers sa version XML.
        try {
            $page = $this->page($retenu, $delai);
        } catch (HttpExceptionInterface|\RuntimeException) {
            return ['id' => null, 'bilan' => 'amendement_inconnu', 'message' => 'page d\'amendement inaccessible'];
        }

        if (preg_match('/(AMANR5L\d+[A-Z0-9]+)/', $page, $m) !== 1) {
            return ['id' => null, 'bilan' => 'amendement_inconnu', 'message' => 'identifiant introuvable sur la page'];
        }

        $id = $this->connection->fetchOne('SELECT id FROM amendement WHERE amendement_id = ?', [$m[1]]);

        if ($id === false || $id === null) {
            // La table des amendements n'est pas la sienne : elle est alimentée
            // par app:import:amendements, qui le rapportera à la prochaine moisson.
            return ['id' => null, 'bilan' => 'amendement_inconnu', 'message' => sprintf('%s absent de la table amendement', $m[1])];
        }

        return ['id' => (int) $id, 'bilan' => 'amendements', 'message' => null];
    }

    /**
     * Les liens de la page contenant un segment donné, dédoublonnés et dans
     * l'ordre d'apparition.
     *
     * @return list<string>
     */
    private function liens(string $html, string $segment): array
    {
        if (preg_match_all('#href="([^"]*/' . preg_quote($segment, '#') . '/[^"]+)"#i', $html, $m) === 0) {
            return [];
        }

        $liens = [];
        foreach ($m[1] as $lien) {
            $lien = html_entity_decode($lien, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
            if (str_starts_with($lien, '/')) {
                $lien = 'https://www.assemblee-nationale.fr' . $lien;
            }
            $liens[$lien] = true;
        }

        return array_keys($liens);
    }

    /** @return array<int, array<int, string>> */
    private function journal(): array
    {
        $fichier = $this->cheminJournal();

        if (!is_file($fichier)) {
            return [];
        }

        $journal = json_decode((string) file_get_contents($fichier), true);

        return \is_array($journal) ? $journal : [];
    }

    /** @param array<int, array<int, string>> $journal */
    private function ecritJournal(array $journal): void
    {
        file_put_contents($this->cheminJournal(), json_encode($journal, \JSON_PRETTY_PRINT));
    }

    /**
     * Le journal vit sous `var/` : c'est un état d'exécution, pas une donnée.
     * Le mettre en base supposerait une colonne, et c'est exactement ce qu'a
     * fait l'application d'origine — sa ligne « amendement d'identifiant NULL »
     * a fini par rattacher faussement 215 scrutins.
     */
    private function cheminJournal(): string
    {
        return $this->racineProjet . '/var/scraper-scrutins.json';
    }
}
