<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les abonnés à la newsletter de la base de production.
 *
 * Ni le dump de la base Symfony ni le backup public n'en contiennent un seul :
 * sans cet import, la lettre mensuelle des votes ne partirait vers personne
 * après la bascule. Les abonnés sont déjà dans les listes Mailjet — le legacy
 * les y a inscrits —, rien n'est donc reporté chez Mailjet ici.
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *   "SELECT email, general, votes, depute, departement, id_user, created_at, updated_at \
 *    FROM newsletter ORDER BY id" > var/legacy/newsletter.tsv
 * ```
 *
 * `general` et `votes` sont NOT NULL au legacy, mais sa reprise des
 * désinscriptions Mailjet y écrit NULL (`update_list($email, NULL, …)`), que
 * MariaDB range en 0 hors mode strict : NULL et 0 valent tous deux « désabonné ».
 *
 * Les adresses que le legacy a acceptées sans les valider (avant juillet 2026,
 * il insérait ce que le navigateur laissait passer) sont reprises telles
 * quelles — c'est une donnée de production —, et comptées : Mailjet les
 * refusera à l'envoi, la lettre les rendra en échec.
 *
 * Import de récupération : il réaligne sur la production. Une fois le site
 * basculé, des internautes s'inscrivent et se désinscrivent ici ; le rejouer
 * réabonnerait ceux qui sont partis entre-temps.
 */
#[AsCommand(
    name: 'app:import:newsletter',
    description: 'Récupère les abonnés à la newsletter depuis un export TSV de la production.',
)]
class ImportNewsletterCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'TSV des abonnés', 'var/legacy/newsletter.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lus = 0;
        $sansAdresse = 0;
        $invalides = 0;
        $lot = [];
        $ecrits = 0;

        foreach ($this->lignes((string) $input->getOption('fichier')) as $ligne) {
            ++$lus;
            [$email, $generale, $votes, $depute, $departement, $utilisateur, $creeLe, $modifieLe] = array_pad($ligne, 8, null);

            $email = $this->texte($email);
            if ($email === null) {
                ++$sansAdresse;

                continue;
            }

            if (filter_var(trim($email), \FILTER_VALIDATE_EMAIL) === false) {
                ++$invalides;
            }

            $lot[] = [
                $email,
                (int) ($generale === '1'),
                (int) ($votes === '1'),
                $this->texte($depute),
                $this->texte($departement),
                $this->entier($utilisateur),
                $this->texte($creeLe) ?? date('Y-m-d H:i:s'),
                $this->texte($modifieLe),
            ];

            if (\count($lot) === self::TAILLE_LOT) {
                $ecrits += $this->ecrire($lot);
                $lot = [];
            }
        }

        $ecrits += $this->ecrire($lot);

        $io->success(sprintf('%d abonné(s) lus, %d écrit(s).', $lus, $ecrits));

        // Bilan à l'unité : lus = écrits + sans adresse.
        if ($sansAdresse > 0) {
            $io->text(sprintf('%d ligne(s) sans adresse écartée(s).', $sansAdresse));
        }
        if ($invalides > 0) {
            $io->text(sprintf('%d adresse(s) mal formée(s) reprise(s) telles quelles.', $invalides));
        }

        $io->text(vsprintf('En base : %d abonné(s), %d à la principale, %d à la lettre des votes.', $this->connection->fetchNumeric(
            'SELECT COUNT(*), COALESCE(SUM(liste_generale), 0), COALESCE(SUM(liste_votes), 0) FROM newsletter',
        )));

        return Command::SUCCESS;
    }

    /** @param list<list<mixed>> $lot */
    private function ecrire(array $lot): int
    {
        $this->upsert(
            'newsletter',
            ['email', 'liste_generale', 'liste_votes', 'depute', 'departement', 'utilisateur_id', 'inscrit_le', 'modifie_le'],
            $lot,
            ['liste_generale', 'liste_votes', 'inscrit_le', 'modifie_le'],
            ['depute', 'departement', 'utilisateur_id'],
        );

        return \count($lot);
    }
}
