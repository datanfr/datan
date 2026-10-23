<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe, depuis le dépôt d'acteurs des Tricoteuses, les organes de
 * l'Assemblée qui n'ont pas de table dédiée et les mandats qui s'y exercent.
 *
 * **Périmètre : les délégations du Bureau (`DELEGBUREAU`).** Le legacy ne
 * conserve dans sa table `mandat_secondaire` que trois types d'organe
 * (`daily.php:473`) : COMPER, DELEGBUREAU et PARPOL. Chez nous, COMPER vit dans
 * `fonction_commission` ({@see ImportCommissionsCommand}) et PARPOL sur
 * `depute.parti_id` — DELEGBUREAU était le seul « poste Assemblée » sans foyer.
 * Tous les autres types (GA, GE, MISINFO, CMP, CIRCONSCRIPTION…) sont écartés à
 * la source par le legacy et le restent ici : les afficher ferait diverger
 * l'écran « Postes Assemblée » de datan.fr, qui ne les montre pas.
 *
 * Import séparé, sur le modèle de {@see ImportCommissionsCommand} — plutôt que
 * d'alourdir {@see ImportActeursCommand}, dont les règles de slug de personne et
 * de département viennent d'être corrigées et ne doivent pas être frôlées. Il
 * entre en revanche dans `app:sync:quotidien` : une délégation se crée ou
 * s'éteint en cours de législature, la donnée doit se rafraîchir chaque nuit.
 *
 * Clé sur l'uid (organe et mandat), jamais sur un libellé : deux organes
 * homonymes de législatures distinctes s'écraseraient l'un l'autre sous un index
 * de libellé (CLAUDE.md, 3 362 mandats perdus en silence la première fois). Ici
 * le risque n'existe pas — l'uid est toujours unique.
 */
#[AsCommand(
    name: 'app:import:organes',
    description: 'Importe les organes sans table dédiée (délégations du Bureau) et leurs mandats.',
)]
class ImportOrganesCommand extends ImportTricoteusesCommand
{
    /**
     * Types d'organe retenus (codeType / typeOrgane). Restreint à DELEGBUREAU :
     * cf. le docblock de classe pour la justification du périmètre.
     *
     * @var list<string>
     */
    private const TYPES_RETENUS = ['DELEGBUREAU'];

    private const COLONNES_ORGANE = [
        'uid', 'code_type', 'libelle', 'libelle_abrege', 'libelle_abrev',
        'legislature', 'date_debut', 'date_fin',
    ];

    private const COLONNES_MANDAT = [
        'uid', 'depute_id', 'organe_id', 'legislature',
        'code_qualite', 'libelle_qualite', 'nomin_principale', 'date_debut', 'date_fin',
    ];

    protected function depotParDefaut(): string
    {
        return 'acteurs';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chemin = $this->chemin($input);
        $tout = (bool) $input->getOption('tout');

        $io->title('Import des organes (délégations du Bureau) et de leurs mandats');
        $io->text('Types retenus : ' . implode(', ', self::TYPES_RETENUS) . '.');

        // Les organes d'abord, toujours relus intégralement : ils sont la table
        // de correspondance des mandats, et un delta quotidien qui ne les verrait
        // pas passer rejetterait les mandats du jour faute d'organe connu.
        $organes = $this->importeOrganes($chemin);
        $io->text(sprintf('%d organes retenus.', $organes));

        $bilan = $this->importeMandats($chemin, $tout);
        $io->text(sprintf(
            '%d mandats lus, %d écrits — écartés : %d sans député en fiche, %d sans organe connu.',
            $bilan['lus'],
            $bilan['ecrits'],
            $bilan['sansDepute'],
            $bilan['sansOrgane'],
        ));

        $io->success(sprintf(
            '%d mandats en base, sur %d organes.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM mandat_organe'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM organe'),
        ));

        return Command::SUCCESS;
    }

    private function importeOrganes(string $chemin): int
    {
        $lot = [];

        // Toujours `tout: true` : le référentiel complet, indépendamment du delta.
        foreach ($this->fichiers($chemin, 'organes', tout: true) as $fichier) {
            $organe = $this->lisJson($fichier);

            if ($organe === null || !isset($organe['uid'])
                || !\in_array($organe['codeType'] ?? null, self::TYPES_RETENUS, true)) {
                continue;
            }

            $lot[] = [
                $organe['uid'],
                (string) $organe['codeType'],
                $organe['libelle'] ?? '',
                $organe['libelleAbrege'] ?? null,
                $organe['libelleAbrev'] ?? null,
                $this->entier($organe['legislature'] ?? null),
                $this->date($organe['viMoDe']['dateDebut'] ?? null),
                $this->date($organe['viMoDe']['dateFin'] ?? null),
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('organe', self::COLONNES_ORGANE, $lot, \array_slice(self::COLONNES_ORGANE, 1));
                $lot = [];
            }
        }

        $this->upsert('organe', self::COLONNES_ORGANE, $lot, \array_slice(self::COLONNES_ORGANE, 1));

        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM organe WHERE code_type IN (?)',
            [self::TYPES_RETENUS],
            [\Doctrine\DBAL\ArrayParameterType::STRING],
        );
    }

    /**
     * @return array{lus: int, ecrits: int, sansDepute: int, sansOrgane: int}
     */
    private function importeMandats(string $chemin, bool $tout): array
    {
        $idOrganes = $this->connection->fetchAllKeyValue('SELECT uid, id FROM organe');
        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $lot = [];
        $lus = 0;
        $ecrits = 0;
        $sansDepute = 0;
        $sansOrgane = 0;

        foreach ($this->fichiers($chemin, 'acteurs', $tout) as $fichier) {
            $acteur = $this->lisJson($fichier);

            if ($acteur === null || !isset($acteur['uid'])) {
                continue;
            }

            foreach ($this->mandatsRetenus($acteur) as $mandat) {
                ++$lus;

                // Les fichiers d'acteurs couvrent aussi ministres et membres du
                // Conseil constitutionnel, jamais députés : leurs mandats n'ont
                // pas de fiche où se rattacher.
                $depute = $idDeputes[$acteur['uid']] ?? null;
                if ($depute === null) {
                    ++$sansDepute;
                    continue;
                }

                $organe = $idOrganes[$mandat['organesRefs'][0] ?? ''] ?? null;
                if ($organe === null) {
                    // Ne devrait pas arriver : tous les organes retenus sont
                    // relus avant les mandats. On le compte pour le prouver.
                    ++$sansOrgane;
                    continue;
                }

                $qualite = $mandat['infosQualite'] ?? [];

                $lot[] = [
                    $mandat['uid'],
                    $depute,
                    $organe,
                    $this->entier($mandat['legislature'] ?? null),
                    ($qualite['codeQualite'] ?? '') ?: null,
                    ($qualite['libQualite'] ?? '') ?: null,
                    // Absent des moissons anciennes : au doute, principal.
                    (int) ($mandat['nominPrincipale'] ?? 1),
                    $this->date($mandat['dateDebut'] ?? null),
                    $this->date($mandat['dateFin'] ?? null),
                ];
                ++$ecrits;

                if (\count($lot) >= self::TAILLE_LOT) {
                    $this->ecris($lot);
                    $lot = [];
                }
            }
        }

        $this->ecris($lot);

        return ['lus' => $lus, 'ecrits' => $ecrits, 'sansDepute' => $sansDepute, 'sansOrgane' => $sansOrgane];
    }

    /**
     * Mandats retenus d'un acteur (types de {@see TYPES_RETENUS}, munis d'un uid).
     *
     * L'open data publie une liste quand le mandat est multiple, un objet unique
     * sinon : on absorbe les deux formes, comme {@see ImportCommissionsCommand}.
     *
     * @param array<string, mixed> $acteur
     *
     * @return iterable<array<string, mixed>>
     */
    private function mandatsRetenus(array $acteur): iterable
    {
        $mandats = $acteur['mandats'] ?? [];

        if (isset($mandats['mandat'])) {
            $mandats = $mandats['mandat'];
        }

        if (!\is_array($mandats)) {
            return;
        }

        foreach ($mandats as $mandat) {
            if (\is_array($mandat)
                && \in_array($mandat['typeOrgane'] ?? null, self::TYPES_RETENUS, true)
                && isset($mandat['uid'])) {
                yield $mandat;
            }
        }
    }

    /**
     * `date_fin` réécrite sans condition, comme pour les commissions : sa venue
     * clôt le mandat, la garder sous COALESCE figerait la première date publiée.
     *
     * @param list<list<mixed>> $lot
     */
    private function ecris(array $lot): void
    {
        $this->upsert('mandat_organe', self::COLONNES_MANDAT, $lot, \array_slice(self::COLONNES_MANDAT, 1));
    }
}
