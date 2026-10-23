<?php

namespace App\Command;

use App\Entity\DossierActeur;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les initiateurs et les rapporteurs des dossiers législatifs
 * (`dossier_acteur`), pour les deux variantes du bloc auteur que la page de vote
 * sert sur les scrutins **sans** amendement : « L'auteur de la proposition de
 * loi » et « Le rapporteur ».
 *
 * **Aucun scraping, et rien à moissonner de plus.** La donnée est dans les
 * fichiers de dossier des Tricoteuses que `app:import:dossiers` lit déjà pour
 * `commission_fond` — structure identique au XML que parcourt le legacy
 * (`dossiersActeurs()`, daily.php:3318) : `initiateur.acteurs[].acteurRef` d'un
 * côté, `rapporteurs[]` des actes législatifs de l'autre.
 *
 * Deux différences de forme avec le XML, sans conséquence sur la donnée :
 * l'initiateur organe se lit en `initiateur.organeRef` (une chaîne) là où le XML
 * imbriquait `initiateur/organes/organe/organeRef/uid`, et les rapporteurs sont
 * groupés dans un tableau `rapporteurs` au lieu d'éléments `<rapporteur>`
 * répétés. Comme le legacy, les rapporteurs se cherchent en profondeur sous
 * chaque acte de premier niveau — un texte navette imbrique ses étapes — et
 * l'`etape` retenue est le `codeActe` de cet acte de premier niveau.
 *
 * Les quatre `typeRapporteur` de la source sont repris tels quels
 * (« rapporteur », « pour avis », « spécial », « général ») : le site ne les
 * distingue pas, et les 116 rapporteurs spéciaux d'un projet de loi de finances
 * remplissent bien le carrousel de sa page de vote.
 *
 * Hors `app:sync:quotidien`, comme `app:import:auteurs-amendements` : à lancer
 * sciemment après un import de dossiers. Toujours en passe complète, jamais sur
 * le delta de la moisson — lancée à la main et rarement, elle raterait les
 * fichiers des moissons intermédiaires, chaque moisson écrasant `.datan-modifies`.
 *
 * L'écriture est un upsert : les lignes ne font que s'ajouter. C'est le bon
 * régime ici — les actes d'un dossier sont un historique, l'open data en ajoute
 * mais n'en retire pas —, là où le legacy vide la législature avant de réinsérer.
 */
#[AsCommand(
    name: 'app:import:dossiers-acteurs',
    description: 'Importe les initiateurs et rapporteurs des dossiers depuis le dépôt des Tricoteuses.',
)]
class ImportDossiersActeursCommand extends ImportTricoteusesCommand
{
    private const COLONNES = ['dossier_id', 'legislature', 'role', 'type', 'ref', 'etape', 'mandat_ref'];

    /**
     * Tout est dans la clé unique sauf `legislature` et `mandat_ref`, qui ne se
     * réécrivent que renseignées : une moisson qui tairait l'une n'invalide pas
     * celle qu'on a déjà.
     */
    private const COLONNES_MAJ_SI_RENSEIGNE = ['legislature', 'mandat_ref'];

    protected function depotParDefaut(): string
    {
        return 'dossiers';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chemin = $this->chemin($input);

        $io->title('Initiateurs et rapporteurs des dossiers');

        // Le dépôt est clé par uid de dossier, la table par identifiant interne.
        // 14 000 dossiers : la map tient en mémoire sans peine.
        $identifiants = $this->connection->fetchAllKeyValue('SELECT dossier_id, id FROM dossier');

        $lus = 0;
        $initiateurs = 0;
        $rapporteurs = 0;
        $sansActeur = 0;
        $absents = 0;
        $repetes = 0;
        $lot = [];

        foreach ($this->fichiers($chemin, 'dossiers', tout: true) as $fichier) {
            $dossier = $this->lisJson($fichier);
            if ($dossier === null || !isset($dossier['uid'])) {
                continue;
            }
            ++$lus;

            $lignes = $this->lignes($dossier);
            if ($lignes === []) {
                ++$sansActeur;
                continue;
            }

            // Un dossier que le dépôt connaît mais pas la table : compté et
            // rendu, à reprendre après `app:import:dossiers`.
            $id = $identifiants[$dossier['uid']] ?? null;
            if ($id === null) {
                ++$absents;
                continue;
            }

            $legislature = $this->entier($dossier['legislature'] ?? null);
            $vues = [];

            foreach ($lignes as $ligne) {
                [$role, $type, $ref, $etape, $mandat] = $ligne;

                // La source répète un rapporteur autant de fois qu'il apparaît
                // dans le sous-arbre d'une même étape. La clé unique les
                // fondrait de toute façon en une ligne ; les compter ici évite
                // d'annoncer plus d'écritures qu'il n'y en a.
                $cle = $role . '|' . $type . '|' . $ref . '|' . $etape;
                if (isset($vues[$cle])) {
                    ++$repetes;
                    continue;
                }
                $vues[$cle] = true;

                $lot[] = [$id, $legislature, $role, $type, $ref, $etape, $mandat];

                if ($role === DossierActeur::ROLE_INITIATEUR) {
                    ++$initiateurs;
                } else {
                    ++$rapporteurs;
                }
            }

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('dossier_acteur', self::COLONNES, $lot, [], self::COLONNES_MAJ_SI_RENSEIGNE);
                $lot = [];
            }
        }

        $this->upsert('dossier_acteur', self::COLONNES, $lot, [], self::COLONNES_MAJ_SI_RENSEIGNE);

        // Le bilan rend l'écart à l'unité : lus = traités + sans acteur +
        // absents, et écrits = lignes de la source - répétitions.
        $io->success(sprintf(
            "%d dossiers lus : %d initiateurs et %d rapporteurs écrits, %d dossiers sans acteur, %d absents de la table (à reprendre après app:import:dossiers), %d répétitions de la source fondues.\n%d lignes en base, sur %d dossiers.",
            $lus,
            $initiateurs,
            $rapporteurs,
            $sansActeur,
            $absents,
            $repetes,
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM dossier_acteur'),
            (int) $this->connection->fetchOne('SELECT COUNT(DISTINCT dossier_id) FROM dossier_acteur'),
        ));

        return Command::SUCCESS;
    }

    /**
     * Les lignes d'un dossier : ses initiateurs, puis ses rapporteurs.
     *
     * @param array<string, mixed> $dossier
     *
     * @return list<array{string, string, string, string, string|null}> role, type, ref, etape, mandat
     */
    private function lignes(array $dossier): array
    {
        $lignes = [];

        foreach ($dossier['initiateur']['acteurs'] ?? [] as $acteur) {
            $ref = \is_array($acteur) ? (string) ($acteur['acteurRef'] ?? '') : '';
            if ($ref !== '') {
                $lignes[] = [DossierActeur::ROLE_INITIATEUR, DossierActeur::TYPE_ACTEUR, $ref, '', $acteur['mandatRef'] ?? null];
            }
        }

        // L'organe initiateur — le Gouvernement d'un projet de loi. La carte du
        // site ne le montre pas (elle ne retient que les initiateurs acteurs),
        // mais sa présence est ce qui distingue un dossier déposé par personne
        // d'un dossier déposé par le Gouvernement : sans lui, la table laisserait
        // croire à un trou de moisson.
        $organe = $dossier['initiateur']['organeRef'] ?? '';
        if (\is_string($organe) && $organe !== '') {
            $lignes[] = [DossierActeur::ROLE_INITIATEUR, DossierActeur::TYPE_ORGANE, $organe, '', null];
        }

        foreach ($dossier['actesLegislatifs'] ?? [] as $acte) {
            if (\is_array($acte)) {
                $this->collecteRapporteurs($acte, (string) ($acte['codeActe'] ?? ''), $lignes);
            }
        }

        return $lignes;
    }

    /**
     * Parcourt un acte et tous ses sous-actes à la recherche de rapporteurs.
     *
     * Le legacy fait le même parcours par un XPath `.//rapporteur` évalué depuis
     * l'acte de premier niveau : c'est bien tout le sous-arbre qui est fouillé,
     * et l'étape reste celle de la racine du parcours.
     *
     * @param array<string, mixed>                                     $noeud
     * @param list<array{string, string, string, string, string|null}> $lignes
     */
    private function collecteRapporteurs(array $noeud, string $etape, array &$lignes): void
    {
        foreach ($noeud['rapporteurs'] ?? [] as $rapporteur) {
            $ref = \is_array($rapporteur) ? (string) ($rapporteur['acteurRef'] ?? '') : '';
            if ($ref === '') {
                continue;
            }

            $type = (string) ($rapporteur['typeRapporteur'] ?? '');

            $lignes[] = [
                DossierActeur::ROLE_RAPPORTEUR,
                $type !== '' ? $type : DossierActeur::ROLE_RAPPORTEUR,
                $ref,
                $etape,
                null,
            ];
        }

        foreach ($noeud as $cle => $valeur) {
            if ($cle !== 'rapporteurs' && \is_array($valeur)) {
                $this->collecteRapporteurs($valeur, $etape, $lignes);
            }
        }
    }
}
