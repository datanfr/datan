<?php

namespace App\Command;

use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\String\u;

/**
 * Cherche sur Bluesky les poignées des députés qui n'en ont pas encore, en
 * interrogeant l'API publique de l'AppView (`app.bsky.actor.searchActors`).
 *
 * L'application d'origine ne fait pas cet appel dans `daily.php` : sa fonction
 * `addBsky()` se contente d'ingérer un CSV (`data/deputes_bluesky.csv`) que la
 * moisson avait été faite ailleurs. Ce portage reconstitue l'étape manquante —
 * la recherche elle-même — et rend le CSV inutile.
 *
 * **Interroge un service tiers : à lancer sciemment, hors `app:sync:quotidien`.**
 *
 * La correspondance nom → poignée n'est jamais sûre (homonymes, usurpations) :
 * comme le CSV du legacy portait une colonne `active` de validation humaine, la
 * commande **ne fait que proposer par défaut** et n'écrit qu'avec `--ecrire`,
 * et seulement les correspondances franches (le nom complet du député se
 * retrouve dans le nom affiché ou la poignée du candidat). Les autres candidats
 * sont listés pour relecture, pas enregistrés.
 *
 * Elle ne touche que les députés **sans** poignée, et l'écriture est gardée par
 * `COALESCE` : une poignée déjà saisie à la main n'est jamais écrasée.
 */
#[AsCommand(
    name: 'app:moissonner:bluesky',
    description: 'Cherche sur Bluesky les poignées des députés qui n\'en ont pas (API publique, hors chaîne quotidienne).',
)]
class MoissonnerBlueskyCommand extends Command
{
    private const RECHERCHE = 'https://public.api.bsky.app/xrpc/app.bsky.actor.searchActors';

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('ecrire', null, InputOption::VALUE_NONE, 'Enregistre les correspondances franches (sinon simple proposition).')
            ->addOption('limite', null, InputOption::VALUE_REQUIRED, 'Ne traiter que les N premiers députés (0 = tous).', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $ecrire = (bool) $input->getOption('ecrire');
        $limite = (int) $input->getOption('limite');

        $io->title('Moisson des poignées Bluesky');
        $io->text($ecrire ? 'Mode écriture : les correspondances franches seront enregistrées.' : 'Mode proposition : rien n\'est écrit (utiliser --ecrire).');

        // Députés de la législature en cours sans poignée Bluesky. Le `IN` sur les
        // mandats évite de dédoubler un député aux mandats interrompus.
        $deputes = $this->connection->fetchAllAssociative(
            'SELECT d.id, d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId
             FROM depute d
             LEFT JOIN contact_depute c ON c.depute_id = d.id
             WHERE d.id IN (SELECT depute_id FROM mandat WHERE legislature = :leg)
               AND (c.bluesky IS NULL OR c.bluesky = \'\')
             ORDER BY d.lastname ASC, d.firstname ASC',
            ['leg' => Legislature::COURANTE],
        );

        if ($limite > 0) {
            $deputes = \array_slice($deputes, 0, $limite);
        }

        $trouves = 0;
        $ecrits = 0;
        $proposes = 0;
        $sans = 0;
        $erreurs = 0;
        $aRelire = [];

        foreach ($deputes as $depute) {
            $nomComplet = trim($depute['prenom'] . ' ' . $depute['nom']);

            $candidats = $this->chercher($nomComplet, $erreurs);
            $poignee = $this->correspondance($nomComplet, $candidats);

            if ($poignee !== null) {
                ++$trouves;
                if ($ecrire && $this->enregistre((int) $depute['id'], $poignee)) {
                    ++$ecrits;
                }
                $io->writeln(sprintf('  <info>✓</info> %s → %s', $nomComplet, $poignee));
            } elseif ($candidats !== []) {
                ++$proposes;
                $premier = $candidats[0];
                $aRelire[] = [$nomComplet, $premier['handle'] ?? '?', $premier['displayName'] ?? ''];
            } else {
                ++$sans;
            }

            // Courtoisie envers le service public : un léger palier entre appels.
            usleep(200_000);
        }

        if ($aRelire !== []) {
            $io->section('Candidats à relire (non enregistrés)');
            $io->table(['Député', 'Poignée proposée', 'Nom affiché'], $aRelire);
        }

        $io->success(sprintf(
            '%d députés interrogés — %d correspondances franches (%d écrites), %d à relire, %d sans candidat, %d erreurs réseau.',
            \count($deputes),
            $trouves,
            $ecrits,
            $proposes,
            $sans,
            $erreurs,
        ));

        return Command::SUCCESS;
    }

    /**
     * Candidats renvoyés par l'AppView pour un nom, du plus pertinent au moins.
     *
     * @return list<array<string, mixed>>
     */
    private function chercher(string $nom, int &$erreurs): array
    {
        try {
            $reponse = $this->httpClient->request('GET', self::RECHERCHE, [
                'query' => ['q' => $nom, 'limit' => 5],
                'timeout' => 10,
            ]);

            if ($reponse->getStatusCode() !== 200) {
                ++$erreurs;

                return [];
            }

            return $reponse->toArray()['actors'] ?? [];
        } catch (\Throwable) {
            ++$erreurs;

            return [];
        }
    }

    /**
     * Poignée du premier candidat dont le nom complet du député se retrouve dans
     * le nom affiché ou dans la partie locale de la poignée — la seule
     * correspondance qu'on ose écrire. `null` sinon.
     *
     * @param list<array<string, mixed>> $candidats
     */
    private function correspondance(string $nomComplet, array $candidats): ?string
    {
        $cible = $this->compacte($nomComplet);

        if ($cible === '') {
            return null;
        }

        foreach ($candidats as $candidat) {
            $handle = (string) ($candidat['handle'] ?? '');
            $affiche = $this->compacte((string) ($candidat['displayName'] ?? ''));
            $local = $this->compacte(explode('.', $handle)[0] ?? '');

            if (str_contains($affiche, $cible) || str_contains($local, $cible)) {
                return $handle;
            }
        }

        return null;
    }

    /** Nom réduit à ses lettres et chiffres ASCII, en minuscules : « jeandupont ». */
    private function compacte(string $valeur): string
    {
        return (string) u($valeur)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '');
    }

    /**
     * Écrit la poignée sans jamais écraser une valeur existante (`COALESCE`),
     * qu'une fiche existe déjà ou non pour ce député.
     */
    private function enregistre(int $deputeId, string $poignee): bool
    {
        // `mis_a_jour_le` est affecté AVANT `bluesky` : son `IF` lit ainsi
        // l'ancienne poignée (la date n'est touchée que si l'on remplit vraiment
        // un vide). `bluesky = COALESCE(bluesky, …)` ne remplace jamais l'existant.
        $affectees = $this->connection->executeStatement(
            'INSERT INTO contact_depute (depute_id, bluesky, mis_a_jour_le)
             VALUES (:id, :bsky, CURDATE())
             ON DUPLICATE KEY UPDATE mis_a_jour_le = IF(bluesky IS NULL, VALUES(mis_a_jour_le), mis_a_jour_le),
                                     bluesky = COALESCE(bluesky, VALUES(bluesky))',
            ['id' => $deputeId, 'bsky' => $poignee],
        );

        return $affectees > 0;
    }
}
