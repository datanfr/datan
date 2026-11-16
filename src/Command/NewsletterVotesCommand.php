<?php

namespace App\Command;

use App\Newsletter\LettreVotes;
use App\Newsletter\Mailjet;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Lettre mensuelle des votes (`php index.php newsletter votes <action>` du
 * legacy), sur le mois écoulé.
 *
 *     app:newsletter:votes apercu     MJML (et HTML si l'API MJML est configurée) dans var/
 *     app:newsletter:votes test       à info@datan.fr seulement, sujet préfixé [TEST]
 *     app:newsletter:votes envoyer    à tous les abonnés de la liste « votes »
 *
 * Le mois écoulé se calcule depuis le premier du mois courant. Le legacy
 * retranchait un mois à la date du jour (`strtotime("-1 months")`), ce qui, lancé
 * un 31, saute février ou tout mois de trente jours : le 31 mars, « il y a un
 * mois » est le 3 mars.
 *
 * **Un envoi ne se rejoue pas** : chaque exécution de `envoyer` écrit à tous les
 * abonnés. Un envoi interrompu se reprend à la main, sur la liste des échecs
 * qu'il affiche, pas en relançant la commande.
 */
#[AsCommand(
    name: 'app:newsletter:votes',
    description: 'Compose et envoie la lettre mensuelle des votes (apercu | test | envoyer).',
)]
class NewsletterVotesCommand extends Command
{
    private const ACTIONS = ['apercu', 'test', 'envoyer'];

    /** Destinataire des envois d'essai, comme au legacy. */
    private const ADRESSE_TEST = 'info@datan.fr';

    /** Échecs détaillés dans le bilan ; au-delà, on les compte seulement. */
    private const ECHECS_AFFICHES = 20;

    public function __construct(
        private readonly LettreVotes $lettre,
        private readonly Mailjet $mailjet,
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, implode(' | ', self::ACTIONS))
            ->addOption('mois', null, InputOption::VALUE_REQUIRED, 'Mois couvert, AAAA-MM (défaut : le mois écoulé).')
            ->addOption('destinataire', null, InputOption::VALUE_REQUIRED, 'Adresse de l\'envoi d\'essai.', self::ADRESSE_TEST);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = (string) $input->getArgument('action');

        if (!\in_array($action, self::ACTIONS, true)) {
            $io->error(sprintf('Action inconnue « %s » : %s.', $action, implode(', ', self::ACTIONS)));

            return Command::INVALID;
        }

        $mois = $this->mois($input->getOption('mois'));
        if ($mois === null) {
            $io->error('--mois attend la forme AAAA-MM.');

            return Command::INVALID;
        }

        if ($action !== 'apercu' && !$this->mailjet->estConfigure()) {
            $io->error('Mailjet n\'est pas configuré (MAILJET_API_KEY, MAILJET_API_SECRET) : rien n\'est envoyé.');

            return Command::FAILURE;
        }

        $io->text(sprintf('%s — lettre de %s', date('Y-m-d H:i:s'), $mois->format('Y-m')));

        $donnees = $this->lettre->donnees($mois);
        if ($donnees === null) {
            // Pas une erreur : un mois sans décryptage (vacances parlementaires)
            // n'a pas de lettre. Le legacy disait de même.
            $io->note('Aucun vote décrypté ce mois-là : pas de lettre.');

            return Command::SUCCESS;
        }

        $sujet = sprintf('%s %d | Les derniers votes à l\'Assemblée nationale', ucfirst($donnees['mois']), $donnees['annee']);
        $titre = sprintf('Les votes de l\'Assemblée nationale - %s %d | Newsletter Datan', $donnees['mois'], $donnees['annee']);
        $mjml = $this->lettre->mjml($donnees, $titre);

        $io->text(sprintf(
            '%d scrutins, %d décryptés, %d présentés ; député le plus assidu : %s.',
            $donnees['votes_total'],
            $donnees['votes_decryptes'],
            \count($donnees['votes']),
            $donnees['depute'] ? $donnees['depute']['firstname'] . ' ' . $donnees['depute']['lastname'] : 'aucun (mois trop court)',
        ));

        if ($action === 'apercu') {
            return $this->apercu($io, $mois, $mjml);
        }

        $html = $this->lettre->html($mjml);

        if ($action === 'test') {
            $sujet = '[TEST] ' . $sujet;
            $destinataires = [(string) $input->getOption('destinataire')];
        } else {
            $destinataires = $this->connection->fetchFirstColumn('SELECT email FROM newsletter WHERE liste_votes = 1 ORDER BY id');
        }

        $io->text(sprintf('Envoi de « %s » à %d adresse(s)…', $sujet, \count($destinataires)));
        $bilan = $this->mailjet->envoyerATous($destinataires, $sujet, $html);

        $echecs = \count($bilan['echecs']);
        $io->text(sprintf('%d envoyé(s), %d échec(s), sur %d.', $bilan['envoyes'], $echecs, \count($destinataires)));

        if ($echecs === 0) {
            $io->success('Lettre envoyée.');

            return Command::SUCCESS;
        }

        $io->table(['Adresse', 'Motif'], array_map(
            static fn (string $email, string $motif) => [$email, $motif],
            array_keys(\array_slice($bilan['echecs'], 0, self::ECHECS_AFFICHES, true)),
            \array_slice($bilan['echecs'], 0, self::ECHECS_AFFICHES, true),
        ));
        if ($echecs > self::ECHECS_AFFICHES) {
            $io->text(sprintf('… et %d autre(s).', $echecs - self::ECHECS_AFFICHES));
        }

        return Command::FAILURE;
    }

    private function apercu(SymfonyStyle $io, \DateTimeImmutable $mois, string $mjml): int
    {
        $base = sprintf('%s/var/newsletter-votes-%s', $this->racineProjet, $mois->format('Y-m'));
        file_put_contents($base . '.mjml', $mjml);
        $io->text('MJML : ' . $base . '.mjml');

        if (!$this->lettre->mjmlEstConfigure()) {
            $io->note('API MJML non configurée (MJML_APP_ID, MJML_SECRET_KEY) : pas de HTML. Le MJML se relit sur https://mjml.io/try-it-live.');

            return Command::SUCCESS;
        }

        $html = $this->lettre->html($mjml);
        file_put_contents($base . '.html', $html);
        $io->text(sprintf('HTML : %s.html (%d Ko)', $base, intdiv(\strlen($html), 1024)));

        return Command::SUCCESS;
    }

    private function mois(?string $option): ?\DateTimeImmutable
    {
        if ($option === null) {
            return new \DateTimeImmutable('first day of last month');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $option)) {
            return null;
        }

        return new \DateTimeImmutable($option . '-01');
    }
}
