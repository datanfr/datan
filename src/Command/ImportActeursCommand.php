<?php

namespace App\Command;

use App\Entity\FonctionGroupe;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Importe organes et acteurs depuis le dépôt des Tricoteuses.
 *
 * Un fichier d'acteur porte l'état civil du député et la totalité de ses
 * mandats, toutes législatures confondues. On y puise donc, en une passe, la
 * fiche du député, son historique parlementaire, ses appartenances de groupe
 * et sa commission.
 *
 * L'import n'efface jamais : un député ou un groupe disparu du dernier
 * moissonnage reste en base, sans quoi les pages des législatures passées se
 * videraient.
 */
#[AsCommand(
    name: 'app:import:acteurs',
    description: 'Importe organes, députés, mandats et fonctions de groupe depuis le dépôt des Tricoteuses.',
)]
class ImportActeursCommand extends ImportTricoteusesCommand
{
    /** Mandat de député à l'Assemblée. */
    private const MANDAT_ASSEMBLEE = 'ASSEMBLEE';

    /** Appartenance à un groupe politique. */
    private const MANDAT_GROUPE = 'GP';

    /** Appartenance à une commission permanente. */
    private const MANDAT_COMMISSION = 'COMPER';

    /** Type d'adresse portant une adresse électronique. */
    private const ADRESSE_ELECTRONIQUE = '15';

    private const DOMAINE_ASSEMBLEE = '@assemblee-nationale.fr';

    private const COLONNES_GROUPE = [
        'uid', 'libelle', 'libelle_abrev', 'libelle_abrege', 'couleur',
        'position_politique', 'legislature', 'date_debut', 'date_fin',
        'created_at', 'updated_at',
    ];

    private const COLONNES_PARTI = [
        'uid', 'libelle', 'libelle_abrev', 'couleur', 'created_at', 'updated_at',
    ];

    /**
     * `cat_soc_pro` n'y figure pas : la base y stocke le code INSEE de la
     * catégorie socio-professionnelle alors que l'open data n'en publie que le
     * libellé. Cette colonne reste alimentée par app:import:profils-sociaux.
     */
    private const COLONNES_DEPUTE = [
        'mp_id', 'firstname', 'lastname', 'slug', 'civilite', 'age',
        'date_naissance', 'ville_naissance', 'profession',
        'groupe_id', 'parti_id', 'dpt_slug', 'departement_nom',
        'departement_code', 'circonscription', 'region', 'place_hemicycle',
        'commission', 'mail_an', 'date_fin', 'cause_fin', 'created_at', 'updated_at',
    ];

    /**
     * L'uid du mandat fait la clé (index unique `uniq_mandat_uid`) : un député
     * revenu du Gouvernement porte deux mandats de même date de début dans la
     * même législature, que l'ancienne clé (député, législature, début)
     * fusionnait. `date_prise_fonction` se reprend ici, et non plus seulement
     * dans `app:import:prises-fonction` : c'est elle qui distingue ces mandats.
     */
    private const COLONNES_MANDAT = [
        'uid', 'depute_id', 'legislature', 'date_debut', 'date_fin',
        'departement_nom', 'departement_code', 'circonscription', 'cause_mandat',
        'date_prise_fonction',
    ];

    /** Colonnes réécrites à chaque passage ; la prise de fonction, elle, ne s'efface jamais. */
    private const MANDAT_MIS_A_JOUR = [
        'depute_id', 'legislature', 'date_debut', 'date_fin',
        'departement_nom', 'departement_code', 'circonscription', 'cause_mandat',
    ];

    private const COLONNES_FONCTION = [
        'depute_id', 'groupe_id', 'code_qualite', 'libelle_qualite', 'nomin_principale', 'date_debut', 'date_fin',
    ];

    private AsciiSlugger $slugger;

    /**
     * Le slug de personne est un contrat d'URL : datan.fr le fabrique par deux
     * translittérateurs ICU (daily.php:313-333) qui ÉLIDENT la ponctuation à
     * l'intérieur du prénom et du nom au lieu de la remplacer par un tiret —
     * `marcphilippe-daubresse`, `charles-delaverpilliere`, `benjamin-lucaslundy`.
     * Le seul tiret garanti est celui qui joint prénom et nom. Un AsciiSlugger
     * sur « prénom nom » divergeait sur 449 des 2 119 fiches publiées : autant
     * d'adresses indexées qui tombaient en 404. Règles reprises à l'identique.
     */
    private \Transliterator $translitterateurNom;
    private \Transliterator $translitterateurPrenom;

    /**
     * `departement.slug` (la table tenue à la main du legacy) fait foi pour le
     * segment de département : cinq diffèrent de la fabrication nom-code
     * (CLAUDE.md, « deux jeux de slugs »). Même parade que ImportMandatsCommand ;
     * sans elle, cet import — qui tourne chaque nuit dans app:sync:quotidien —
     * refabriquerait l'ancienne forme et déferait le réalignement à chaque
     * acteur modifié. Indexé en minuscules : la Corse s'écrit « 2A » ici, « 2a »
     * ailleurs, et l'appariement ne doit rien devoir à la casse.
     *
     * @var array<string, string>
     */
    private array $slugsDepartement = [];

    /**
     * Libellé des commissions permanentes, relevé au passage des organes.
     *
     * @var array<string, string>
     */
    private array $libellesCommissions = [];

    /** Rattachements de groupe purgés parce que l'Assemblée les a requalifiés (cf. purgeRequalifies()). */
    private int $rattachementsRequalifies = 0;

    /** Lignes de `mandat` sans uid reprises par leur mandat de la source (cf. adopteMandatsSansUid()). */
    private int $mandatsAdoptes = 0;

    /** Mandats de député écartés faute d'uid dans la source. */
    private int $mandatsSansUid = 0;

    protected function depotParDefaut(): string
    {
        return 'acteurs';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->slugger = new AsciiSlugger();

        // Règles ICU de daily.php : translittérer en latin, ôter les accents,
        // puis pour le NOM supprimer espaces ET ponctuation (« de la
        // Verpillière » → « delaverpilliere »), pour le PRÉNOM supprimer la
        // seule ponctuation (« Marc-Philippe » → « marcphilippe ») en changeant
        // les espaces en tirets.
        $nom = \Transliterator::createFromRules(
            ':: Any-Latin; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;'
            . ':: [:Space:] Remove; :: [:Punctuation:] Remove; :: Lower();'
            . "[:Separator:] > '-';"
        );
        $prenom = \Transliterator::createFromRules(
            ':: Any-Latin; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;'
            . ':: [:Punctuation:] Remove; :: Lower();'
            . "[:Separator:] > '-';"
        );
        if ($nom === null || $prenom === null) {
            $io->error('Règles de translittération ICU refusées : slugs de personne impossibles à fabriquer.');

            return Command::FAILURE;
        }
        $this->translitterateurNom = $nom;
        $this->translitterateurPrenom = $prenom;

        $this->slugsDepartement = [];
        foreach ($this->connection->fetchAllKeyValue('SELECT LOWER(code), slug FROM departement') as $code => $slug) {
            $this->slugsDepartement[$code] = $slug;
        }

        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);
        $maintenant = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $io->title('Import des organes et des acteurs');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les fichiers modifiés.' : 'Import complet du dépôt.');

        // Les organes d'abord : un député se rattache à son groupe et à son
        // parti. Ils sont toujours relus intégralement, même en incrémental :
        // ce sont des données de référence peu nombreuses, et le libellé des
        // commissions doit être connu pour tous les députés traités, pas
        // seulement pour les organes que la moisson a signalés.
        [$groupes, $partis] = $this->importeOrganes($chemin, $maintenant);
        $io->text(sprintf('%d groupes, %d partis.', $groupes, $partis));

        $compteurs = $this->importeActeurs($chemin, $tout, $maintenant);
        $io->text(sprintf(
            '%d députés, %d mandats, %d fonctions de groupe.',
            $compteurs['deputes'],
            $compteurs['mandats'],
            $compteurs['fonctions'],
        ));
        if ($this->mandatsSansUid > 0) {
            $io->warning(sprintf('%d mandat(s) de député sans uid dans la source, écartés : pas de clé pour les écrire.', $this->mandatsSansUid));
        }
        if ($this->mandatsAdoptes > 0) {
            $io->text(sprintf(
                '%d mandat(s) existant(s) rattaché(s) à leur uid de l\'Assemblée (lignes antérieures à la clé par uid).',
                $this->mandatsAdoptes,
            ));
        }
        if ($this->rattachementsRequalifies > 0) {
            $io->text(sprintf(
                '%d rattachement(s) de groupe purgé(s) : requalifiés par l\'Assemblée (membre ↔ apparenté…), l\'ancienne qualité ne vaut plus.',
                $this->rattachementsRequalifies,
            ));
        }

        $io->success(sprintf(
            '%d députés et %d groupes en base.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM depute'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM groupe'),
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int} nombre de groupes et de partis traités
     */
    private function importeOrganes(string $chemin, string $maintenant): array
    {
        $lotGroupe = [];
        $lotParti = [];
        $groupes = 0;
        $partis = 0;

        foreach ($this->fichiers($chemin, 'organes', tout: true) as $fichier) {
            $organe = $this->lisJson($fichier);
            if ($organe === null || !isset($organe['uid'])) {
                continue;
            }

            $debut = $this->date($organe['viMoDe']['dateDebut'] ?? null);
            $fin = $this->date($organe['viMoDe']['dateFin'] ?? null);

            if (($organe['codeType'] ?? null) === 'GP') {
                ++$groupes;
                $lotGroupe[] = [
                    $organe['uid'],
                    $organe['libelle'] ?? null,
                    $organe['libelleAbrev'] ?? null,
                    $organe['libelleAbrege'] ?? null,
                    $organe['couleurAssociee'] ?? null,
                    $organe['positionPolitique'] ?? null,
                    $this->entier($organe['legislature'] ?? null),
                    $debut,
                    $fin,
                    $maintenant,
                    $maintenant,
                ];
            } elseif (($organe['codeType'] ?? null) === self::MANDAT_COMMISSION) {
                // Forme abrégée : c'est « Lois » que le site affiche, pas
                // « Commission des lois constitutionnelles, de la législation… ».
                $this->libellesCommissions[$organe['uid']] = $organe['libelleAbrege'] ?? $organe['libelle'] ?? '';
            } elseif (($organe['codeType'] ?? null) === 'PARPOL') {
                ++$partis;
                $lotParti[] = [
                    $organe['uid'],
                    $organe['libelle'] ?? null,
                    $organe['libelleAbrev'] ?? null,
                    $organe['couleurAssociee'] ?? null,
                    $maintenant,
                    $maintenant,
                ];
            }

            if (\count($lotGroupe) >= self::TAILLE_LOT) {
                $this->upsert('groupe', self::COLONNES_GROUPE, $lotGroupe, \array_slice(self::COLONNES_GROUPE, 1, -2));
                $lotGroupe = [];
            }
            if (\count($lotParti) >= self::TAILLE_LOT) {
                $this->upsert('parti', self::COLONNES_PARTI, $lotParti, \array_slice(self::COLONNES_PARTI, 1, -2));
                $lotParti = [];
            }
        }

        $this->upsert('groupe', self::COLONNES_GROUPE, $lotGroupe, \array_slice(self::COLONNES_GROUPE, 1, -2));
        $this->upsert('parti', self::COLONNES_PARTI, $lotParti, \array_slice(self::COLONNES_PARTI, 1, -2));

        return [$groupes, $partis];
    }

    /**
     * @return array{deputes: int, mandats: int, fonctions: int}
     */
    private function importeActeurs(string $chemin, bool $tout, string $maintenant): array
    {
        $idGroupes = $this->connection->fetchAllKeyValue('SELECT uid, id FROM groupe');
        $idPartis = $this->connection->fetchAllKeyValue('SELECT uid, id FROM parti');

        // On ne retient que le chemin des fichiers : garder en mémoire les
        // mandats des 3 000 acteurs — jusqu'à 120 chacun — épuiserait le tas.
        // Le second passage les relit.
        $fiches = [];
        $lot = [];

        foreach ($this->fichiers($chemin, 'acteurs', $tout) as $fichier) {
            $acteur = $this->lisJson($fichier);
            if ($acteur === null || !isset($acteur['uid'])) {
                continue;
            }

            $mandats = $acteur['mandats'] ?? [];
            $parlementaire = $this->dernierMandat($mandats, self::MANDAT_ASSEMBLEE);

            // Sénateurs et ministres figurent dans le même dépôt : sans mandat
            // de député, la personne n'a pas de fiche sur le site.
            if ($parlementaire === null) {
                continue;
            }

            $fiches[$acteur['uid']] = $fichier;
            $lot[] = $this->ligneDepute($acteur, $mandats, $parlementaire, $idGroupes, $idPartis, $maintenant);

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('depute', self::COLONNES_DEPUTE, $lot, \array_slice(self::COLONNES_DEPUTE, 1, -2));
                $lot = [];
            }
        }

        $this->upsert('depute', self::COLONNES_DEPUTE, $lot, \array_slice(self::COLONNES_DEPUTE, 1, -2));

        if ($fiches === []) {
            return ['deputes' => 0, 'mandats' => 0, 'fonctions' => 0];
        }

        $idDeputes = $this->identifiantsDeputes(array_keys($fiches));

        return [
            'deputes' => \count($fiches),
            'mandats' => $this->importeMandats($fiches, $idDeputes),
            'fonctions' => $this->importeFonctions($fiches, $idDeputes, $idGroupes),
        ];
    }

    /**
     * @param array<string, mixed>      $acteur
     * @param list<array<string,mixed>> $mandats
     * @param array<string, mixed>      $parlementaire
     * @param array<string, int|string> $idGroupes
     * @param array<string, int|string> $idPartis
     *
     * @return list<mixed>
     */
    private function ligneDepute(array $acteur, array $mandats, array $parlementaire, array $idGroupes, array $idPartis, string $maintenant): array
    {
        $ident = $acteur['etatCivil']['ident'] ?? [];
        $prenom = $ident['prenom'] ?? '';
        $nom = $ident['nom'] ?? '';
        $lieu = $parlementaire['election']['lieu'] ?? [];
        $departement = $lieu['departement'] ?? null;
        $code = $lieu['numDepartement'] ?? null;

        // Seul un rattachement encore ouvert vaut appartenance : `groupe_id`
        // porte le groupe des députés en exercice, sur lequel se comptent les
        // effectifs. L'historique complet vit dans fonction_groupe.
        $groupe = $this->dernierMandat($mandats, self::MANDAT_GROUPE, ouvertUniquement: true);

        // Même règle pour le parti : sans cette restriction, un député quitté
        // d'un parti dissous y restait rattaché et gonflait ses effectifs.
        $parti = $this->dernierMandat($mandats, 'PARPOL', ouvertUniquement: true);

        // Un mandat encore ouvert vaut mandat en cours : la date de fin du
        // dernier mandat clos ne dit rien d'une réélection.
        $enCours = $this->dernierMandat($mandats, self::MANDAT_ASSEMBLEE, ouvertUniquement: true) !== null;

        return [
            $acteur['uid'],
            $prenom,
            $nom,
            $this->slugPersonne($prenom, $nom),
            $ident['civ'] ?? null,
            $this->age($acteur['etatCivil']['infoNaissance']['dateNais'] ?? null),
            // État civil de la bio (« né le 25 septembre 1989 à Arras ») ; la
            // ville arrive parfois vide de l'open data, la phrase s'en passe.
            // `dateNais` est un horodatage ISO complet (« 1989-09-25T00:00:00+02:00 ») :
            // on n'en garde que la date, MariaDB refuse le reste dans une colonne DATE.
            isset($acteur['etatCivil']['infoNaissance']['dateNais'])
                ? substr((string) $acteur['etatCivil']['infoNaissance']['dateNais'], 0, 10)
                : null,
            $this->texteOuNul($acteur['etatCivil']['infoNaissance']['villeNais'] ?? null),
            $acteur['profession']['libelleCourant'] ?? null,
            $groupe !== null ? ($idGroupes[$groupe['organesRefs'][0] ?? ''] ?? null) : null,
            $parti !== null ? ($idPartis[$parti['organesRefs'][0] ?? ''] ?? null) : null,
            // `departement.slug` d'abord (cf. $slugsDepartement) ; la
            // fabrication nom-code ne sert plus qu'aux codes que la table du
            // legacy ignore. Le strtolower() enveloppe AUSSI le code : « 2B »
            // doit devenir « 2b », sans quoi les routes ([a-z0-9\-]+) refusent
            // la Corse sans qu'aucune requête SQL ne le montre.
            $departement !== null && $code !== null
                ? ($this->slugsDepartement[strtolower((string) $code)] ?? strtolower($this->slug($departement) . '-' . $code))
                : null,
            $departement,
            $code,
            $this->entier($lieu['numCirco'] ?? null),
            $lieu['region'] ?? null,
            $parlementaire['mandature']['placeHemicycle'] ?? null,
            $this->commission($mandats),
            $this->mailAssemblee($acteur['adresses'] ?? []),
            $enCours ? null : $this->date($parlementaire['dateFin'] ?? null),
            $enCours ? null : ($parlementaire['mandature']['causeFin'] ?? null),
            $maintenant,
            $maintenant,
        ];
    }

    /**
     * @param array<string, string> $fiches    uid de l'acteur → chemin du fichier
     * @param array<string, int>    $idDeputes
     */
    private function importeMandats(array $fiches, array $idDeputes): int
    {
        $lot = [];
        $total = 0;
        $ignores = 0;

        foreach ($fiches as $acteurUid => $fichier) {
            $deputeId = $idDeputes[$acteurUid] ?? null;
            if ($deputeId === null) {
                continue;
            }

            foreach ($this->lisJson($fichier)['mandats'] ?? [] as $mandat) {
                if (($mandat['typeOrgane'] ?? null) !== self::MANDAT_ASSEMBLEE) {
                    continue;
                }

                // Sans uid, plus de clé : la ligne s'ajouterait à chaque
                // passage. Aucun cas dans la source à ce jour ; écarté et
                // compté dans le bilan, plutôt que de doubler la table.
                if (empty($mandat['uid'])) {
                    ++$ignores;
                    continue;
                }

                $lieu = $mandat['election']['lieu'] ?? [];
                $lot[] = [
                    (string) $mandat['uid'],
                    $deputeId,
                    $this->entier($mandat['legislature'] ?? null),
                    $this->date($mandat['dateDebut'] ?? null),
                    $this->date($mandat['dateFin'] ?? null),
                    $lieu['departement'] ?? null,
                    $lieu['numDepartement'] ?? null,
                    $this->entier($lieu['numCirco'] ?? null),
                    $mandat['election']['causeMandat'] ?? null,
                    $this->date($mandat['mandature']['datePriseFonction'] ?? null),
                ];
                ++$total;

                if (\count($lot) >= self::TAILLE_LOT) {
                    $this->ecritMandats($lot);
                    $lot = [];
                }
            }
        }

        $this->ecritMandats($lot);
        $this->mandatsSansUid += $ignores;

        return $total;
    }

    /**
     * @param list<list<mixed>> $lot lignes dans l'ordre de {@see self::COLONNES_MANDAT}
     */
    private function ecritMandats(array $lot): void
    {
        if ($lot === []) {
            return;
        }

        $this->adopteMandatsSansUid($lot);
        $this->upsert('mandat', self::COLONNES_MANDAT, $lot, self::MANDAT_MIS_A_JOUR, ['date_prise_fonction']);
    }

    /**
     * Passage à la clé par uid (migration Version20261116140000) : les lignes
     * écrites avant elle n'ont pas d'uid, et un upsert par uid les doublerait.
     * Avant d'écrire, chaque mandat de la source qui n'a pas encore de ligne à
     * son uid reprend donc la ligne sans uid de même (député, législature, date
     * de début), s'il en reste une. Le premier mandat d'un couple autrefois
     * fusionné reprend la ligne existante, le second s'insère : ni perte, ni
     * doublon. Une fois la base passée, plus aucune ligne n'est sans uid et
     * cette étape ne fait plus rien.
     *
     * @param list<list<mixed>> $lot
     */
    private function adopteMandatsSansUid(array $lot): void
    {
        $sansUid = [];
        foreach ($this->connection->fetchAllAssociative(
            'SELECT id, depute_id, legislature, date_debut FROM mandat WHERE uid IS NULL AND depute_id IN (?) ORDER BY id',
            [array_values(array_unique(array_column($lot, 1)))],
            [ArrayParameterType::INTEGER],
        ) as $ligne) {
            $sansUid[$ligne['depute_id'] . '|' . $ligne['legislature'] . '|' . ($ligne['date_debut'] ?? '')][] = (int) $ligne['id'];
        }

        if ($sansUid === []) {
            return;
        }

        $connus = array_flip($this->connection->fetchFirstColumn(
            'SELECT uid FROM mandat WHERE uid IN (?)',
            [array_column($lot, 0)],
            [ArrayParameterType::STRING],
        ));

        foreach ($lot as [$uid, $deputeId, $legislature, $debut]) {
            $cle = $deputeId . '|' . $legislature . '|' . ($debut ?? '');
            if (isset($connus[$uid]) || empty($sansUid[$cle])) {
                continue;
            }

            $this->connection->executeStatement(
                'UPDATE mandat SET uid = ? WHERE id = ?',
                [$uid, array_shift($sansUid[$cle])],
            );
            ++$this->mandatsAdoptes;
        }
    }

    /**
     * @param array<string, string>     $fiches    uid de l'acteur → chemin du fichier
     * @param array<string, int>        $idDeputes
     * @param array<string, int|string> $idGroupes
     */
    private function importeFonctions(array $fiches, array $idDeputes, array $idGroupes): int
    {
        $lot = [];
        $total = 0;
        $sources = [];

        foreach ($fiches as $acteurUid => $fichier) {
            $deputeId = $idDeputes[$acteurUid] ?? null;
            if ($deputeId === null) {
                continue;
            }

            foreach ($this->lisJson($fichier)['mandats'] ?? [] as $mandat) {
                if (($mandat['typeOrgane'] ?? null) !== self::MANDAT_GROUPE) {
                    continue;
                }

                $groupeId = $idGroupes[$mandat['organesRefs'][0] ?? ''] ?? null;
                if ($groupeId === null) {
                    continue;
                }

                $qualite = $mandat['infosQualite']['codeQualite'] ?? FonctionGroupe::QUALITE_MEMBRE;
                $sources[$deputeId][$groupeId . '|' . ($this->date($mandat['dateDebut'] ?? null) ?? '')][$qualite] = true;

                $lot[] = [
                    $deputeId,
                    $groupeId,
                    $qualite,
                    $mandat['infosQualite']['libQualite'] ?? null,
                    // Absent des moissons anciennes : au doute, un rattachement
                    // est principal, c'est le cas de l'immense majorité.
                    (int) ($mandat['nominPrincipale'] ?? 1),
                    $this->date($mandat['dateDebut'] ?? null),
                    $this->date($mandat['dateFin'] ?? null),
                ];
                ++$total;

                if (\count($lot) >= self::TAILLE_LOT) {
                    $this->upsert('fonction_groupe', self::COLONNES_FONCTION, $lot, ['libelle_qualite', 'nomin_principale', 'date_fin']);
                    $lot = [];
                }
            }
        }

        $this->upsert('fonction_groupe', self::COLONNES_FONCTION, $lot, ['libelle_qualite', 'nomin_principale', 'date_fin']);
        $this->rattachementsRequalifies += $this->purgeRequalifies($sources);

        return $total;
    }

    /**
     * Retire les rattachements que l'Assemblée a requalifiés.
     *
     * Quand elle change la qualité d'un mandat de groupe — membre devenu
     * apparenté, par exemple —, elle garde le mandat (même uid, même date de
     * début) et n'en publie plus que la nouvelle qualité. Or notre clé d'upsert
     * contient la qualité (`depute_id, groupe_id, code_qualite, date_debut`) :
     * la nouvelle ligne s'ajoute, l'ancienne survit, toujours ouverte. Emmanuel
     * Riotte figurait ainsi à la fois membre et apparenté d'Horizons, deux
     * rattachements ouverts de rang principal, et la composition du groupe le
     * rangeait au hasard d'un côté ou de l'autre.
     *
     * Ce qu'on sait faux se purge (cf. CLAUDE.md) : une ligne dont le groupe et
     * la date de début désignent un mandat que la source décrit encore, mais
     * sous une autre qualité. Rien d'autre — un mandat absent d'une moisson ne
     * dit pas qu'il est faux. La présidence et le mandat de membre d'un
     * président partagent souvent groupe et date : la source publie les deux,
     * aucun ne part.
     *
     * @param array<int, array<string, array<string, true>>> $sources député → « groupe|début » → qualités publiées
     */
    private function purgeRequalifies(array $sources): int
    {
        $purges = 0;

        foreach (array_chunk(array_keys($sources), 1000) as $deputes) {
            $lignes = $this->connection->fetchAllAssociative(
                'SELECT id, depute_id, groupe_id, code_qualite, date_debut FROM fonction_groupe WHERE depute_id IN (?)',
                [$deputes],
                [ArrayParameterType::INTEGER],
            );

            $aPurger = [];
            foreach ($lignes as $ligne) {
                $publiees = $sources[(int) $ligne['depute_id']][$ligne['groupe_id'] . '|' . ($ligne['date_debut'] ?? '')] ?? null;
                if ($publiees !== null && !isset($publiees[$ligne['code_qualite']])) {
                    $aPurger[] = (int) $ligne['id'];
                }
            }

            if ($aPurger !== []) {
                $purges += $this->connection->executeStatement(
                    'DELETE FROM fonction_groupe WHERE id IN (?)',
                    [$aPurger],
                    [ArrayParameterType::INTEGER],
                );
            }
        }

        return $purges;
    }

    /**
     * Le mandat le plus récent d'un type donné, d'après sa date de début, puis
     * sa prise de fonction.
     *
     * Le second critère n'est pas un raffinement : un député revenu du
     * Gouvernement porte deux mandats de député de MÊME date de début, que seule
     * la prise de fonction départage. Sans lui, le premier lu l'emportait —
     * souvent le mandat clos par la nomination —, et la fiche d'Astrid
     * Panosyan-Bouvet, sortie le 04/08/2026 par « prolongation de mission
     * temporaire », annonçait un départ « pour cause de nomination au
     * Gouvernement », la cause de fin de son PREMIER mandat.
     *
     * @param list<array<string, mixed>> $mandats
     *
     * @return array<string, mixed>|null
     */
    private function dernierMandat(array $mandats, string $type, bool $ouvertUniquement = false): ?array
    {
        $retenu = null;

        foreach ($mandats as $mandat) {
            if (($mandat['typeOrgane'] ?? null) !== $type) {
                continue;
            }
            if ($ouvertUniquement && ($mandat['dateFin'] ?? null) !== null) {
                continue;
            }
            if ($retenu === null || $this->ordreMandat($mandat) > $this->ordreMandat($retenu)) {
                $retenu = $mandat;
            }
        }

        return $retenu;
    }

    /**
     * Clé de tri chronologique d'un mandat : début, puis prise de fonction
     * (dates ISO, comparables comme chaînes).
     *
     * @param array<string, mixed> $mandat
     */
    private function ordreMandat(array $mandat): string
    {
        return ($mandat['dateDebut'] ?? '') . '|' . ($mandat['mandature']['datePriseFonction'] ?? '');
    }

    /**
     * Adresse électronique institutionnelle du député.
     *
     * Le type `15` regroupe toutes les adresses électroniques déclarées, y
     * compris celles d'une mairie ou d'un compte personnel. On ne retient que
     * le domaine de l'Assemblée : c'est l'adresse qu'un citoyen peut écrire, et
     * publier une adresse municipale à sa place serait une indiscrétion.
     *
     * @param list<array<string, mixed>> $adresses
     */
    private function mailAssemblee(array $adresses): ?string
    {
        foreach ($adresses as $adresse) {
            $valeur = $adresse['valElec'] ?? null;

            if (($adresse['type'] ?? null) === self::ADRESSE_ELECTRONIQUE
                && \is_string($valeur)
                && str_ends_with(mb_strtolower($valeur), self::DOMAINE_ASSEMBLEE)) {
                return $valeur;
            }
        }

        return null;
    }

    /**
     * Commission permanente du député, telle que l'affichent la carte de profil
     * et le bloc biographique : **celle où il siège aujourd'hui**, c'est-à-dire
     * son mandat COMPER encore ouvert — la règle de datan.fr
     * (`Deputes_model::get_commission_parlementaire`).
     *
     * La règle précédente (la commission où il a cumulé le plus de jours sur sa
     * dernière législature) visait à ignorer les remplacements de quelques
     * jours, mais elle retardait d'autant tout vrai changement de commission :
     * Yaël Braun-Pivet, en Finances depuis novembre 2025, restait affichée en
     * Développement durable (432 jours contre 331). Au 16/11/2026, 67 des 574
     * députés en exercice y recevaient une autre commission que sur datan.fr,
     * dont 38 installés depuis plus d'un mois dans la nouvelle, certains depuis
     * plus d'un an ; 17 seulement relevaient d'un remplacement d'une semaine. La
     * phrase « … est membre de la Commission … » du bloc biographique, qui
     * cherche un mandat ouvert dans CETTE commission, disparaissait en prime.
     * Un remplacement de quelques jours s'affiche donc, comme sur datan.fr :
     * c'est la commission où le député siège ce jour-là.
     *
     * Entre deux mandats ouverts (membre et secrétaire de la même commission),
     * la plus petite préséance l'emporte, comme au legacy. Mais le legacy prend
     * ce minimum sur TOUS les mandats de la législature, clos compris : un
     * ancien vice-président redevenu simple membre n'y trouve plus aucune
     * commission (30 députés en exercice sans la ligne). Ici, le minimum se
     * prend sur les seuls mandats ouverts.
     *
     * Sans mandat ouvert — un ancien député —, on retombe sur la commission la
     * plus fréquentée de sa dernière législature.
     *
     * @param list<array<string, mixed>> $mandats
     */
    private function commission(array $mandats): ?string
    {
        $legislature = 0;
        foreach ($mandats as $mandat) {
            if (($mandat['typeOrgane'] ?? null) === self::MANDAT_COMMISSION) {
                $legislature = max($legislature, (int) ($mandat['legislature'] ?? 0));
            }
        }

        if ($legislature === 0) {
            return null;
        }

        $ouvert = null;
        $jours = [];
        foreach ($mandats as $mandat) {
            if (($mandat['typeOrgane'] ?? null) !== self::MANDAT_COMMISSION
                || (int) ($mandat['legislature'] ?? 0) !== $legislature) {
                continue;
            }

            $organe = $mandat['organesRefs'][0] ?? null;
            if ($organe === null) {
                continue;
            }

            if (empty($mandat['dateFin'])) {
                $cle = [(int) ($mandat['preseance'] ?? 99), -(strtotime($mandat['dateDebut'] ?? '') ?: 0)];
                if ($ouvert === null || $cle < $ouvert['cle']) {
                    $ouvert = ['cle' => $cle, 'organe' => $organe];
                }
            }

            $debut = strtotime($mandat['dateDebut'] ?? '') ?: time();
            $fin = strtotime($mandat['dateFin'] ?? '') ?: time();
            $jours[$organe] = ($jours[$organe] ?? 0) + max(1, (int) (($fin - $debut) / 86400));
        }

        if ($ouvert !== null) {
            return $this->libellesCommissions[$ouvert['organe']] ?? null;
        }

        if ($jours === []) {
            return null;
        }

        arsort($jours);

        return $this->libellesCommissions[array_key_first($jours)] ?? null;
    }

    /**
     * @param list<string> $uids
     *
     * @return array<string, int>
     */
    private function identifiantsDeputes(array $uids): array
    {
        $identifiants = [];

        foreach (array_chunk($uids, 1000) as $paquet) {
            $identifiants += $this->connection->fetchAllKeyValue(
                'SELECT mp_id, id FROM depute WHERE mp_id IN (?)',
                [$paquet],
                [\Doctrine\DBAL\ArrayParameterType::STRING],
            );
        }

        return array_map(intval(...), $identifiants);
    }

    private function slug(string $valeur): string
    {
        return $this->slugger->slug($valeur)->lower()->toString();
    }

    /**
     * Le slug de personne tel que datan.fr le sert : prénom et nom
     * translittérés séparément (cf. $translitterateurNom), joints par le seul
     * tiret garanti de l'adresse. Ne pas « simplifier » vers un slugger
     * ordinaire : la ponctuation intérieure s'élide, elle ne se tiretise pas.
     */
    private function slugPersonne(string $prenom, string $nom): string
    {
        return $this->translitterateurPrenom->transliterate($prenom)
            . '-'
            . $this->translitterateurNom->transliterate($nom);
    }

    private function age(?string $naissance): ?int
    {
        if ($naissance === null) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($naissance))->diff(new \DateTimeImmutable())->y;
        } catch (\Exception) {
            return null;
        }
    }

    /** L'open data écrit parfois une chaîne vide là où l'information manque. */
    private function texteOuNul(?string $texte): ?string
    {
        $texte = $texte !== null ? trim($texte) : null;

        return $texte === '' ? null : $texte;
    }
}
