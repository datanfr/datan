<?php

namespace App\Command;

use App\Newsletter\Mailjet;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Reporte en base les désinscriptions faites chez Mailjet
 * (`php index.php newsletter update` du legacy).
 *
 * Les campagnes de la newsletter principale partent de l'interface Mailjet, avec
 * son propre lien de désinscription : qui le suit n'est désinscrit que chez
 * Mailjet. Sans ce report, la base le croirait toujours abonné — et la page
 * d'abonnement lui afficherait une case cochée qui ne dit plus vrai.
 *
 * Seul ce sens est repris : une désinscription chez Mailjet retire l'abonnement
 * en base, jamais l'inverse. La base reste la source ; c'est l'internaute qui
 * l'a voulu, ailleurs.
 *
 * Un appel par abonné, comme le legacy : l'API ne rend l'état de désinscription
 * que contact par contact. Le legacy faisait même un appel par abonné **et** par
 * liste ; un seul suffit, il rend toutes les listes du contact.
 */
#[AsCommand(
    name: 'app:newsletter:desinscriptions',
    description: 'Reporte en base les désinscriptions faites chez Mailjet.',
)]
class NewsletterDesinscriptionsCommand extends Command
{
    /** Liste Mailjet → colonne de la table `newsletter`. */
    private const COLONNES = [
        Mailjet::LISTE_GENERALE => 'liste_generale',
        Mailjet::LISTE_VOTES => 'liste_votes',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly Mailjet $mailjet,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text(sprintf('%s — désinscriptions Mailjet', date('Y-m-d H:i:s')));

        // Sans clés, ce n'est pas un échec : la préproduction tourne sans Mailjet.
        if (!$this->mailjet->estConfigure()) {
            $io->note('Mailjet non configuré (MAILJET_API_KEY, MAILJET_API_SECRET) : rien à faire.');

            return Command::SUCCESS;
        }

        $abonnes = $this->connection->fetchAllAssociative(
            'SELECT email, liste_generale, liste_votes FROM newsletter
             WHERE liste_generale = 1 OR liste_votes = 1 ORDER BY id',
        );

        $inconnus = 0;
        $erreurs = [];
        $retraits = array_fill_keys(self::COLONNES, 0);

        foreach ($abonnes as $abonne) {
            try {
                $listes = $this->mailjet->listesDuContact($abonne['email']);
            } catch (\RuntimeException $e) {
                $erreurs[$abonne['email']] = $e->getMessage();

                continue;
            }

            if ($listes === null) {
                ++$inconnus;

                continue;
            }

            $colonnes = [];
            foreach (self::COLONNES as $liste => $colonne) {
                if ((int) $abonne[$colonne] === 1 && ($listes[$liste]['IsUnsub'] ?? false)) {
                    $colonnes[$colonne] = 0;
                    ++$retraits[$colonne];
                }
            }

            if ($colonnes !== []) {
                $colonnes['modifie_le'] = date('Y-m-d H:i:s');
                $this->connection->update('newsletter', $colonnes, ['email' => $abonne['email']]);
            }
        }

        // Bilan à l'unité : un abonné lu et non retiré est soit encore inscrit,
        // soit inconnu de Mailjet, soit en erreur — chacun se compte.
        $io->text(sprintf(
            '%d abonné(s) lus : %d retiré(s) de la principale, %d de la liste votes ; %d inconnu(s) de Mailjet, %d en erreur.',
            \count($abonnes),
            $retraits['liste_generale'],
            $retraits['liste_votes'],
            $inconnus,
            \count($erreurs),
        ));

        if ($erreurs === []) {
            return Command::SUCCESS;
        }

        foreach (\array_slice($erreurs, 0, 10, true) as $email => $motif) {
            $io->text(sprintf('  %s : %s', $email, $motif));
        }

        return Command::FAILURE;
    }
}
