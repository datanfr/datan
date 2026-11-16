<?php

namespace App\Command;

use App\Ia\FabriqueClientAnthropic;
use App\Ia\Tag\RapprochementTags;
use App\Ia\Tag\VocabulaireTags;
use App\Ia\TransporteurSimulation;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Nettoyage ponctuel du vocabulaire des mots-clés : rapproche les canoniques
 * qui désignent la même notion, par la brique du traitement des lots
 * ({@see RapprochementTags}), et fait des absorbés des alias.
 *
 * Indispensable après l'import de récupération : la production a accumulé
 * 4 124 libellés distincts pour 13 258 rattachements, dont 58 % ne servent
 * qu'une fois. La normalisation les ramène à 3 853 clés sans IA ; ce qui reste
 * — synonymes, sigles, formulations voisines — demande le modèle.
 *
 * **Découpage par catégorie** : chaque mot-clé est rangé dans la catégorie où
 * il sert le plus, et chaque catégorie fait une requête. La plus grosse
 * (Fiscalité et budget, 510 clés) tient en ~3 000 jetons d'entrée ; le tout,
 * dix-neuf requêtes et ~25 000 jetons, coûte de l'ordre du centime. Le prix
 * de ce découpage : deux synonymes rangés dans deux catégories différentes ne
 * se voient pas. Le traitement courant, qui montre au candidat ses voisins
 * lexicaux de toute catégorie, en rattrape une partie.
 *
 * Par défaut, **simulation** : les rapprochements sont demandés au modèle (un
 * appel payant, minime) et affichés, rien n'est écrit. `--appliquer` écrit.
 * `--sans-appel` n'interroge pas le modèle : il montre le découpage et la
 * première requête, sans clé.
 */
#[AsCommand(
    name: 'app:ia:tags:nettoyer',
    description: 'Rapproche les mots-clés synonymes du vocabulaire, catégorie par catégorie (simulation par défaut).',
)]
class NettoyerTagsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly VocabulaireTags $vocabulaire,
        private readonly RapprochementTags $rapprochement,
        private readonly FabriqueClientAnthropic $fabrique,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('appliquer', null, InputOption::VALUE_NONE, 'Écrit les rapprochements (sinon : simulation)')
            ->addOption('sans-appel', null, InputOption::VALUE_NONE, 'N\'interroge pas le modèle : découpage et première requête seulement')
            ->addOption('categorie', null, InputOption::VALUE_REQUIRED, 'Une seule catégorie');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Nettoyage du vocabulaire des mots-clés');

        $appliquer = (bool) $input->getOption('appliquer');
        $sansAppel = (bool) $input->getOption('sans-appel');
        $seule = $input->getOption('categorie');

        if (!$sansAppel && !$this->fabrique->estConfiguree()) {
            $io->error('ANTHROPIC_API_KEY est vide, ou IA_MODELE_RESUMES ne désigne pas un modèle claude-*. `--sans-appel` fonctionne sans clé.');

            return Command::FAILURE;
        }

        $this->vocabulaire->charger();
        $canoniques = $this->vocabulaire->canoniques();
        [$usage, $usageParCategorie] = $this->vocabulaire->usages();

        $sections = self::parCategorieDominante($canoniques, $usageParCategorie);
        if ($seule !== null) {
            $sections = array_intersect_key($sections, [$seule => true]);
        }

        $avant = $this->vocabulaire->compter();
        $io->text(sprintf('%d mots-clés canoniques (%d alias), répartis en %d catégorie%s.',
            $avant['canoniques'], $avant['alias'], \count($sections), \count($sections) > 1 ? 's' : ''));
        $io->table(['Catégorie', 'Mots-clés', '~ jetons'], array_map(
            static fn (string $c, array $s) => [$c === '' ? '(sans catégorie)' : $c, \count($s['candidats']), (int) ceil(mb_strlen(implode(' | ', $s['candidats'])) / 3)],
            array_keys($sections), $sections,
        ));

        if ($sansAppel) {
            $premiere = array_key_first($sections);
            if ($premiere !== null) {
                $transporteur = new TransporteurSimulation();
                $transporteur->repondreJson(TransporteurSimulation::message('{"synonymes":[]}'));
                $this->rapprochement->rapprocher($this->fabrique->client($transporteur), $this->fabrique->modele(), [$premiere => $sections[$premiere]]);
                $io->text('Première requête, telle que le SDK l\'enverrait :');
                $io->writeln(json_encode($transporteur->corps(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
            }

            return Command::SUCCESS;
        }

        $client = $this->fabrique->client();
        $fusions = [];
        foreach ($sections as $categorie => $section) {
            try {
                $couples = $this->rapprochement->rapprocher($client, $this->fabrique->modele(), [$categorie => $section]);
            } catch (\Throwable $e) {
                $io->warning(sprintf('%s : %s — catégorie passée.', $categorie, $e->getMessage()));
                continue;
            }
            foreach (RapprochementTags::resoudre($couples, $section['candidats'], [], $usage) as $slug => $cible) {
                if ($cible !== null) {
                    $fusions[$slug] = $cible;
                }
            }
            $io->text(sprintf('%s : %d couple%s proposé%s.', $categorie === '' ? '(sans catégorie)' : $categorie, \count($couples), \count($couples) > 1 ? 's' : '', \count($couples) > 1 ? 's' : ''));
        }

        if ($fusions === []) {
            $io->success('Aucun rapprochement proposé.');

            return Command::SUCCESS;
        }

        $io->table(['Absorbé', 'Amendements', '→ Cible', 'Amendements'], array_map(
            static fn (string $a, string $c) => [$canoniques[$a], $usage[$a] ?? 0, $canoniques[$c], $usage[$c] ?? 0],
            array_keys($fusions), $fusions,
        ));

        if (!$appliquer) {
            $io->success(sprintf('%d rapprochement%s proposé%s ; il resterait %d mots-clés. Simulation : rien n\'est écrit (--appliquer pour écrire).',
                \count($fusions), \count($fusions) > 1 ? 's' : '', \count($fusions) > 1 ? 's' : '', $avant['canoniques'] - \count($fusions)));

            return Command::SUCCESS;
        }

        $this->connection->transactional(function () use ($fusions): void {
            foreach ($fusions as $slug => $cible) {
                $this->vocabulaire->fusionner((int) $this->vocabulaire->canonique($slug), (int) $this->vocabulaire->canonique($cible));
            }
        });
        $apres = $this->vocabulaire->compter();
        $io->success(sprintf('%d rapprochement%s écrit%s : %d mots-clés canoniques, %d alias.',
            \count($fusions), \count($fusions) > 1 ? 's' : '', \count($fusions) > 1 ? 's' : '', $apres['canoniques'], $apres['alias']));

        return Command::SUCCESS;
    }

    /**
     * Chaque canonique dans la catégorie où il sert le plus ; ceux qui ne
     * servent nulle part dans « sans catégorie ».
     *
     * @param array<string, string>             $canoniques
     * @param array<string, array<string, int>> $usageParCategorie
     *
     * @return array<string, array{existants: array<string, string>, candidats: array<string, string>}>
     */
    public static function parCategorieDominante(array $canoniques, array $usageParCategorie): array
    {
        $meilleure = [];
        foreach ($usageParCategorie as $categorie => $comptes) {
            foreach ($comptes as $slug => $n) {
                if (!isset($meilleure[$slug]) || $n > $meilleure[$slug][1]) {
                    $meilleure[$slug] = [$categorie, $n];
                }
            }
        }

        $sections = [];
        foreach ($canoniques as $slug => $libelle) {
            $categorie = $meilleure[$slug][0] ?? '';
            $sections[$categorie] ??= ['existants' => [], 'candidats' => []];
            $sections[$categorie]['candidats'][$slug] = $libelle;
        }
        ksort($sections);

        return $sections;
    }
}
