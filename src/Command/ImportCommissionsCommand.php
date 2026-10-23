<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Importe les commissions permanentes et les mandats qui s'y exercent, depuis
 * le même dépôt d'acteurs que {@see ImportActeursCommand}.
 *
 * Cet import est séparé parce qu'il sert une page à lui seul : `app:import:acteurs`
 * ne retient des organes COMPER que leur libellé, pour renseigner la colonne
 * `depute.commission`. Ici on garde les 25 000 mandats eux-mêmes — dates,
 * qualité, législature — sans lesquels on ne peut ni composer un bureau ni
 * retracer une carrière en commission.
 */
#[AsCommand(
    name: 'app:import:commissions',
    description: 'Importe les commissions permanentes et les mandats COMPER des députés.',
)]
class ImportCommissionsCommand extends ImportTricoteusesCommand
{
    /** Commission permanente, dans la nomenclature des organes de l'Assemblée. */
    private const TYPE_ORGANE = 'COMPER';

    /** Un lot plus large qu'ailleurs : ces lignes sont étroites et nombreuses. */
    private const TAILLE_LOT_MANDATS = 1000;

    private const COLONNES_COMMISSION = ['uid', 'libelle', 'libelle_abrege', 'slug', 'date_fin'];

    private const COLONNES_FONCTION = [
        'uid', 'depute_id', 'commission_id', 'legislature',
        'code_qualite', 'libelle_qualite', 'date_debut', 'date_fin',
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

        $io->title('Import des commissions permanentes');

        // Les dix organes sont toujours relus : ils sont la table de
        // correspondance des mandats, et un import incrémental qui ne les
        // verrait pas passer rejetterait tous les mandats du jour.
        $commissions = $this->importeCommissions($chemin);
        $io->text(sprintf('%d commissions.', $commissions));

        $mandats = $this->importeMandats($chemin, $tout);
        $io->text(sprintf('%d mandats de commission.', $mandats));

        $io->success(sprintf(
            '%d mandats en base, sur %d commissions.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM fonction_commission'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commission'),
        ));

        return Command::SUCCESS;
    }

    private function importeCommissions(string $chemin): int
    {
        $slugger = new AsciiSlugger();
        $organes = [];

        foreach ($this->fichiers($chemin, 'organes', true) as $fichier) {
            $organe = $this->lisJson($fichier);

            if ($organe === null || !isset($organe['uid']) || ($organe['codeType'] ?? null) !== self::TYPE_ORGANE) {
                continue;
            }

            $organes[] = $organe;
        }

        // Les commissions encore ouvertes d'abord : deux organes peuvent porter
        // le même libellé abrégé — l'Assemblée a redécoupé les affaires
        // culturelles et les affaires économiques en 2009 et rouvert des organes
        // de même nom — et c'est la commission en activité qui doit garder
        // l'adresse simple.
        usort($organes, static fn (array $a, array $b) => [isset($a['viMoDe']['dateFin']), $a['uid']]
            <=> [isset($b['viMoDe']['dateFin']), $b['uid']]);

        $lot = [];
        $slugs = [];

        foreach ($organes as $organe) {
            // Le site n'affiche jamais le libellé complet (« Commission des lois
            // constitutionnelles, de la législation et de l'administration
            // générale de la République ») : c'est la forme abrégée qui sert de
            // nom, et donc d'adresse.
            $abrege = $organe['libelleAbrege'] ?? $organe['libelle'] ?? '';
            $fin = $this->date($organe['viMoDe']['dateFin'] ?? null);
            $slug = strtolower($slugger->slug($abrege)->toString());

            if (isset($slugs[$slug])) {
                $slug .= '-' . ($fin !== null ? substr($fin, 0, 4) : strtolower($organe['uid']));
            }

            $slugs[$slug] = true;

            $lot[] = [$organe['uid'], $organe['libelle'] ?? $abrege, $abrege, $slug, $fin];
        }

        $this->upsert('commission', self::COLONNES_COMMISSION, $lot, ['libelle', 'libelle_abrege', 'slug', 'date_fin']);

        return \count($lot);
    }

    private function importeMandats(string $chemin, bool $tout): int
    {
        $idCommissions = $this->connection->fetchAllKeyValue('SELECT uid, id FROM commission');
        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $lot = [];
        $mandats = 0;

        foreach ($this->fichiers($chemin, 'acteurs', $tout) as $fichier) {
            $acteur = $this->lisJson($fichier);

            if ($acteur === null || !isset($acteur['uid'])) {
                continue;
            }

            $depute = $idDeputes[$acteur['uid']] ?? null;

            // Les fichiers d'acteurs couvrent aussi les ministres et les membres
            // du Conseil constitutionnel, qui n'ont jamais été députés.
            if ($depute === null) {
                continue;
            }

            foreach ($this->mandatsCommission($acteur) as $mandat) {
                $commission = $idCommissions[$mandat['organesRefs'][0] ?? ''] ?? null;
                $legislature = $this->entier($mandat['legislature'] ?? null);

                if ($commission === null || $legislature === null) {
                    continue;
                }

                $qualite = $mandat['infosQualite'] ?? [];

                $lot[] = [
                    $mandat['uid'],
                    $depute,
                    $commission,
                    $legislature,
                    ($qualite['codeQualite'] ?? '') ?: null,
                    ($qualite['libQualite'] ?? '') ?: null,
                    $this->date($mandat['dateDebut'] ?? null),
                    $this->date($mandat['dateFin'] ?? null),
                ];
                ++$mandats;
            }

            if (\count($lot) >= self::TAILLE_LOT_MANDATS) {
                $this->ecris($lot);
                $lot = [];
            }
        }

        $this->ecris($lot);

        return $mandats;
    }

    /**
     * Mandats de commission d'un acteur.
     *
     * L'open data publie une liste quand le député a plusieurs mandats et un
     * objet unique quand il n'en a qu'un.
     *
     * @param array<string, mixed> $acteur
     *
     * @return iterable<array<string, mixed>>
     */
    private function mandatsCommission(array $acteur): iterable
    {
        $mandats = $acteur['mandats'] ?? [];

        if (isset($mandats['mandat'])) {
            $mandats = $mandats['mandat'];
        }

        if (!\is_array($mandats)) {
            return;
        }

        foreach ($mandats as $mandat) {
            if (\is_array($mandat) && ($mandat['typeOrgane'] ?? null) === self::TYPE_ORGANE && isset($mandat['uid'])) {
                yield $mandat;
            }
        }
    }

    /**
     * `date_fin` est réécrite sans condition, à la différence de la règle
     * générale : c'est sa disparition qui signale une nomination prolongée, et
     * sa venue qui clôt le mandat. La garder sur COALESCE figerait à jamais la
     * première date de fin publiée.
     *
     * @param list<list<mixed>> $lot
     */
    private function ecris(array $lot): void
    {
        $this->upsert(
            'fonction_commission',
            self::COLONNES_FONCTION,
            $lot,
            ['depute_id', 'commission_id', 'legislature', 'code_qualite', 'libelle_qualite', 'date_debut', 'date_fin'],
        );
    }
}
