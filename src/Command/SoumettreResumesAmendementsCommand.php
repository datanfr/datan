<?php

namespace App\Command;

use App\Ia\Amendement\LotsResumes;
use App\Ia\FabriqueClientAnthropic;
use App\Ia\TransporteurSimulation;
use App\Legislature;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Première moitié de la chaîne des résumés d'amendements : soumet un lot à
 * l'API de lots d'Anthropic (`generate_amendements_ia_submit_batch.php` du
 * legacy). La seconde, `app:ia:resumes-amendements:traiter`, en intègre les
 * résultats quand le lot est terminé.
 *
 * Remplace l'ancienne commande synchrone `app:ia:resumes-amendements`, et son
 * prompt avec elle : il ne reste qu'un prompt de résumé dans le dépôt, celui
 * de la rédaction ({@see \App\Ia\Amendement\PromptResumeAmendement}). La
 * commande synchrone écrivait trois champs sur six, et la garde « jamais
 * écraser un résumé » aurait ensuite interdit à jamais la justification, la
 * catégorie et les mots-clés des amendements qu'elle avait touchés ; elle
 * payait en outre le double, hors lot et hors cache. Ce qu'elle offrait de
 * précieux — éprouver un modèle sans consommer le lot — est repris par
 * `--essai`.
 *
 * Trois modes :
 * - par défaut, soumet ;
 * - `--simulation` affiche la requête exacte (sérialisée par le SDK) sans
 *   l'envoyer ni rien écrire, sans clé ;
 * - `--essai=N` résume N amendements en direct et affiche le résultat sans
 *   rien écrire (au prix plein, sans remise de lot).
 */
#[AsCommand(
    name: 'app:ia:resumes-amendements:soumettre',
    description: 'Soumet à l\'API de lots d\'Anthropic les amendements mis aux voix qui n\'ont pas de résumé.',
)]
class SoumettreResumesAmendementsCommand extends Command
{
    /** Taille de lot du legacy (`batch_size`, 20 par défaut, un lot par heure). */
    private const TAILLE_DEFAUT = 20;

    public function __construct(
        private readonly LotsResumes $lots,
        private readonly FabriqueClientAnthropic $fabrique,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('legislature', null, InputOption::VALUE_REQUIRED, 'Législature des scrutins', (string) Legislature::COURANTE)
            ->addOption('taille', null, InputOption::VALUE_REQUIRED, 'Amendements par lot', (string) self::TAILLE_DEFAUT)
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Affiche la requête sans l\'envoyer ni rien écrire')
            ->addOption('essai', null, InputOption::VALUE_REQUIRED, 'Résume N amendements en direct, sans rien écrire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Résumés IA des amendements — soumission');

        $simulation = (bool) $input->getOption('simulation');
        $essai = $input->getOption('essai') !== null ? max(1, (int) $input->getOption('essai')) : null;
        $modele = $this->fabrique->modele();

        if (!$simulation && !$this->fabrique->estConfiguree()) {
            $io->error('ANTHROPIC_API_KEY est vide, ou IA_MODELE_RESUMES ne désigne pas un modèle claude-*. `--simulation` fonctionne sans clé.');

            return Command::FAILURE;
        }

        $legislature = (int) $input->getOption('legislature');
        $selection = $this->lots->aResumer($legislature, $essai ?? max(1, (int) $input->getOption('taille')));
        $amendements = $selection['amendements'];

        if ($selection['sans_expose'] > 0) {
            $io->note(sprintf('%d amendement%s sans exposé des motifs écarté%s : rien à résumer.',
                $selection['sans_expose'], $selection['sans_expose'] > 1 ? 's' : '', $selection['sans_expose'] > 1 ? 's' : ''));
        }
        if ($amendements === []) {
            $io->success(sprintf('Rien à soumettre en L%d.', $legislature));

            return Command::SUCCESS;
        }

        if ($essai !== null) {
            return $this->essayer($io, $modele, $amendements);
        }

        if ($simulation) {
            $transporteur = new TransporteurSimulation();
            $this->fabrique->client($transporteur)->messages->batches->create(requests: LotsResumes::requetes($modele, $amendements));
            $corps = $transporteur->corps();

            $io->text(sprintf('%d requête%s, modèle %s. Première requête, telle que le SDK l\'enverrait :', \count($corps['requests']), \count($corps['requests']) > 1 ? 's' : '', $modele));
            $io->writeln(json_encode($corps['requests'][0], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
            $io->text(sprintf('Corps complet : %s octets. Rien n\'a été envoyé ni écrit.', number_format(\strlen((string) $transporteur->requetes[0]->getBody()), 0, ',', ' ')));

            return Command::SUCCESS;
        }

        $identifiant = $this->lots->soumettre($this->fabrique->client(), $modele, $amendements);
        $io->success(sprintf('Lot %s soumis : %d amendement%s (modèle %s). Résultats : app:ia:resumes-amendements:traiter.',
            $identifiant, \count($amendements), \count($amendements) > 1 ? 's' : '', $modele));

        return Command::SUCCESS;
    }

    /** @param list<array{id: int, expose: string}> $amendements */
    private function essayer(SymfonyStyle $io, string $modele, array $amendements): int
    {
        $io->text(sprintf('Essai direct sur %d amendement%s (modèle %s) — rien ne sera écrit.', \count($amendements), \count($amendements) > 1 ? 's' : '', $modele));

        $echecs = 0;
        foreach ($this->lots->essayer($this->fabrique->client(), $modele, $amendements) as $resultat) {
            $io->section(sprintf('Amendement #%d', $resultat['id']));
            if ($resultat['champs'] === null) {
                ++$echecs;
                $io->warning($resultat['motif']);
                continue;
            }
            $champs = $resultat['champs'];
            $io->definitionList(
                ['Titre' => $champs['titre']],
                ['Résumé' => $champs['resume']],
                ['Justification' => $champs['justification'] ?? '—'],
                ['Simplicité' => $champs['simplicite'] . '/5'],
                ['Catégorie' => $champs['categorie'] ?? '—'],
                ['Tags' => implode(', ', $champs['tags']) ?: '—'],
                ['Jetons' => sprintf('entrée %d (cache lu %d, écrit %d), sortie %d',
                    $resultat['usage']['entree'], $resultat['usage']['cache_lus'], $resultat['usage']['cache_ecrits'], $resultat['usage']['sortie'])],
            );
        }

        return $echecs === \count($amendements) ? Command::FAILURE : Command::SUCCESS;
    }
}
