<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les coordonnées et réseaux sociaux des députés — site, courriels et
 * comptes X / Facebook / Bluesky — depuis un export de la base de production.
 *
 * Ces données ne sont dans aucun dépôt des Tricoteuses : l'open data de
 * l'Assemblée ne publie pas les réseaux sociaux, et Datan les tient à la main
 * (cf. CLAUDE.md). La base de production les range dans `deputes_contacts`,
 * une ligne par député (`mpId`), et c'est de là qu'elles se récupèrent.
 *
 * **Import de récupération**, pour le chargement initial : il réaligne sur la
 * production. Une fois la rédaction devenue la source — un compte saisi par
 * l'écran d'édition, une poignée trouvée par `app:moissonner:bluesky` —, le
 * rejouer réaligne à nouveau. Il n'a donc rien à faire dans
 * `app:sync:quotidien` ; on le lance sciemment.
 *
 * La production n'est pas joignable depuis l'application — le port 3307 de
 * l'hôte est pris par la base de développement, la production ne vit que dans
 * le conteneur `datan-db`. On passe donc par un fichier, régénéré ainsi :
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT mpId, website, mailAn, mailPerso, twitter, facebook, bluesky, dateMaj
 *   FROM deputes_contacts ORDER BY mpId;" > var/legacy/deputes_contacts.tsv
 * ```
 *
 * Les valeurs sont reprises **telles quelles** — le pseudo X garde son arobase
 * (« @dupont »), le site son absence de protocole (« dupont.fr ») : c'est à
 * l'affichage de la fiche publique de les normaliser, comme le fait le legacy
 * (`ltrim($twitter, '@')`, « https:// » préfixé au site).
 */
#[AsCommand(
    name: 'app:import:reseaux-sociaux',
    description: 'Importe les coordonnées et réseaux sociaux des députés depuis un export de la base de production.',
)]
class ImportReseauxSociauxCommand extends ImportLegacyCommand
{
    private const COLONNES = ['depute_id', 'site_web', 'mail_an', 'mail_perso', 'twitter', 'facebook', 'bluesky', 'mis_a_jour_le'];

    protected function configure(): void
    {
        $this->addOption('contacts', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table deputes_contacts', 'var/legacy/deputes_contacts.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des coordonnées et réseaux sociaux des députés');

        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $lus = 0;
        $inconnus = 0;
        $lot = [];

        // Toutes les colonnes sauf la clé passent en COALESCE : un réimport ne
        // remplace jamais une valeur déjà en base par du vide. C'est ce qui fait
        // qu'un compte saisi à la main par la rédaction survit à la récupération
        // suivante, si la production ne le connaît pas.
        $misAJourSiRenseigne = \array_slice(self::COLONNES, 1);

        foreach ($this->lignes($input->getOption('contacts')) as $ligne) {
            ++$lus;
            [$mpId, $site, $mailAn, $mailPerso, $twitter, $facebook, $bluesky, $dateMaj] = array_pad($ligne, 8, null);

            // Les contacts couvrent plusieurs législatures d'acteurs ; sans fiche
            // de député chez nous, il n'y a rien à rattacher.
            $deputeId = $idDeputes[$mpId] ?? null;
            if ($deputeId === null) {
                ++$inconnus;
                continue;
            }

            $lot[] = [
                $deputeId,
                $this->texte($site),
                $this->texte($mailAn),
                $this->texte($mailPerso),
                $this->texte($twitter),
                $this->texte($facebook),
                $this->texte($bluesky),
                $this->texte($dateMaj),
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('contact_depute', self::COLONNES, $lot, [], $misAJourSiRenseigne);
                $lot = [];
            }
        }

        $this->upsert('contact_depute', self::COLONNES, $lot, [], $misAJourSiRenseigne);

        if ($inconnus > 0) {
            $io->text(sprintf('%d contacts ignorés : mpId sans fiche de député chez nous.', $inconnus));
        }

        $enBase = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS fiches, COUNT(site_web) AS sites, COUNT(mail_an) AS mails_an,
                    COUNT(mail_perso) AS mails_perso, COUNT(twitter) AS x, COUNT(facebook) AS facebook,
                    COUNT(bluesky) AS bluesky
             FROM contact_depute',
        ) ?: [];

        $io->success(sprintf(
            '%d contacts lus (%d écartés) — %d fiches en base : %d sites, %d courriels AN, '
            . '%d courriels perso, %d comptes X, %d Facebook, %d Bluesky.',
            $lus,
            $inconnus,
            $enBase['fiches'] ?? 0,
            $enBase['sites'] ?? 0,
            $enBase['mails_an'] ?? 0,
            $enBase['mails_perso'] ?? 0,
            $enBase['x'] ?? 0,
            $enBase['facebook'] ?? 0,
            $enBase['bluesky'] ?? 0,
        ));

        return Command::SUCCESS;
    }
}
