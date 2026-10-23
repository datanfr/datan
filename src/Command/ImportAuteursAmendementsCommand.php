<?php

namespace App\Command;

use App\Tricoteuses\Catalogue;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Renseigne l'auteur principal de chaque amendement (`amendement.auteur_type`
 * et `auteur_ref`), pour la carte « L'auteur de l'amendement » de la page de
 * vote.
 *
 * **Aucun scraping ici, et c'est un choix documenté.** La consigne du chantier
 * supposait que l'auteur d'amendement ne s'obtienne qu'en grattant
 * `assemblee-nationale.fr/dyn/` ; le code du legacy dit autre chose — et c'est
 * lui qui fait foi. `amendementsAuteurs()` (daily.php:3753) ne visite aucune
 * page : il lit les archives XML de l'open data (`Amendements_XVII.xml.zip`) et
 * en tire `signataires/auteur` — `typeAuteur`, `acteurRef` ou `gouvernementRef`.
 * Notre clone des Tricoteuses porte exactement la même structure en JSON
 * (`signataires.auteur`) : la donnée est locale, complète, et se lit en une
 * passe là où un scraping aurait demandé des milliers de requêtes pour un
 * résultat identique.
 *
 * Comme le legacy, seul l'auteur PRINCIPAL est retenu : un député ou un
 * rapporteur par son `acteurRef`, le Gouvernement par son `gouvernementRef`.
 * Les cosignataires restent dans `amendement.signataires` (libellé en toutes
 * lettres), et `groupePolitiqueRef` n'est pas repris — la carte affiche le
 * groupe actuel du député, résolu à l'affichage comme partout ailleurs.
 *
 * La commande reprend aussi, du dépôt des acteurs, les organes
 * `GOUVERNEMENT` : la carte du Gouvernement affiche son nom et sa date de
 * formation (« Gouvernement Lecornu ii — Formé le 11 octobre 2025 »), et la
 * table `organe` ne les portait pas.
 *
 * Toujours en passe complète, jamais sur le delta de la moisson : lancée à la
 * main et rarement, elle raterait les fichiers des moissons intermédiaires —
 * chaque moisson écrase `.datan-modifies`. Elle n'écrit que des lignes
 * d'amendement déjà en base : celles que le dépôt connaît mais pas la table
 * sont comptées et rendues, à reprendre après `app:import:amendements`. Hors
 * `app:sync:quotidien`, comme tout ce chantier : à lancer sciemment après un
 * import d'amendements.
 */
#[AsCommand(
    name: 'app:import:auteurs-amendements',
    description: "Renseigne l'auteur principal des amendements depuis le dépôt des Tricoteuses.",
)]
class ImportAuteursAmendementsCommand extends ImportTricoteusesCommand
{
    /** Types d'auteur portés par un acteur ; tout autre type est gouvernemental. */
    private const TYPES_ACTEUR = ['Député', 'Rapporteur'];

    public function __construct(
        Connection $connection,
        private readonly string $racineTricoteuses,
    ) {
        parent::__construct($connection, $racineTricoteuses);
    }

    protected function depotParDefaut(): string
    {
        return 'amendements';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chemin = $this->chemin($input);

        $io->title('Auteurs des amendements');

        $gouvernements = $this->importeGouvernements();
        $io->text(sprintf('%d organes GOUVERNEMENT repris du dépôt des acteurs.', $gouvernements));

        // Seules les lignes déjà en base sont mises à jour : insérer ici une
        // ligne squelette contournerait app:import:amendements et ses colonnes
        // obligatoires. La table fait ~129 000 identifiants, la map tient en
        // mémoire sans peine.
        $connus = $this->connection->fetchAllKeyValue('SELECT amendement_id, 1 FROM amendement');

        $lus = 0;
        $ecrits = 0;
        $sansAuteur = 0;
        $absents = 0;
        $lot = [];

        foreach ($this->fichiers($chemin, '', tout: true) as $fichier) {
            $amendement = $this->lisJson($fichier);
            if ($amendement === null || !isset($amendement['uid'])) {
                continue;
            }
            ++$lus;

            $auteur = $amendement['signataires']['auteur'] ?? null;
            $type = $auteur['typeAuteur'] ?? null;
            if ($type === null || $type === '') {
                ++$sansAuteur;
                continue;
            }

            if (!isset($connus[$amendement['uid']])) {
                ++$absents;
                continue;
            }

            // La branche est celle d'amendementsAuteurs() : acteur pour un
            // député ou un rapporteur, organe gouvernemental sinon.
            $ref = \in_array($type, self::TYPES_ACTEUR, true)
                ? ($auteur['acteurRef'] ?? null)
                : ($auteur['gouvernementRef'] ?? null);

            $lot[] = [$amendement['uid'], 0, $type, $ref !== '' ? $ref : null];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->enregistre($lot);
                $lot = [];
            }
        }

        $this->enregistre($lot);

        // Le bilan rend l'écart à l'unité : lus = écrits + sans auteur + absents.
        $io->success(sprintf(
            '%d amendements lus : %d auteurs écrits, %d fichiers sans auteur, %d absents de la table (à reprendre après app:import:amendements).',
            $lus,
            $ecrits,
            $sansAuteur,
            $absents,
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<list<mixed>> $lot
     */
    private function enregistre(array $lot): void
    {
        // La clé unique `amendement_id` fait tomber chaque ligne dans la
        // branche UPDATE — la map `$connus` garantit qu'aucune n'est neuve.
        // Réécriture sans condition : la donnée vient toujours du même champ
        // du même dépôt, il n'y a pas d'autre source à protéger.
        //
        // `resume_relu` (0) figure dans la liste d'insertion sans figurer dans
        // la mise à jour : en mode strict, MariaDB construit la ligne candidate
        // AVANT de constater le doublon, et refuse une colonne NOT NULL sans
        // défaut absente de la liste — même quand la ligne existe et que seul
        // l'UPDATE s'appliquera. La valeur n'est donc jamais écrite.
        $this->upsert('amendement', ['amendement_id', 'resume_relu', 'auteur_type', 'auteur_ref'], $lot, ['auteur_type', 'auteur_ref']);
    }

    /**
     * Reprise des organes `GOUVERNEMENT` du dépôt des acteurs — dix-sept
     * lignes, de Fillon à Lecornu II. `libelle_abrege` porte le nom d'usage en
     * capitales (« LECORNU II ») : c'est lui que la carte abaisse en
     * « Lecornu ii », comme le site.
     */
    private function importeGouvernements(): int
    {
        $dossier = Catalogue::get('acteurs')->chemin($this->racineTricoteuses) . '/organes';
        $lot = [];

        foreach ($this->fichiers($dossier, '', tout: true) as $fichier) {
            $organe = $this->lisJson($fichier);
            if ($organe === null || ($organe['codeType'] ?? null) !== 'GOUVERNEMENT' || !isset($organe['uid'])) {
                continue;
            }

            $lot[] = [
                $organe['uid'],
                'GOUVERNEMENT',
                $organe['libelle'] ?? 'Gouvernement',
                $organe['libelleAbrege'] ?? null,
                $organe['libelleAbrev'] ?? null,
                $this->date($organe['viMoDe']['dateDebut'] ?? null),
                $this->date($organe['viMoDe']['dateFin'] ?? null),
            ];
        }

        $this->upsert(
            'organe',
            ['uid', 'code_type', 'libelle', 'libelle_abrege', 'libelle_abrev', 'date_debut', 'date_fin'],
            $lot,
            ['libelle', 'libelle_abrege', 'libelle_abrev', 'date_debut', 'date_fin'],
        );

        return \count($lot);
    }
}
