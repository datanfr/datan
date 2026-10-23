<?php

namespace App\Command;

use App\CorrectionTitreScrutin;
use App\NatureVote;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les scrutins depuis le dépôt des Tricoteuses.
 *
 * Un fichier de scrutin porte à lui seul tout ce que le site affiche d'un
 * vote : la synthèse, le dossier législatif auquel il se rattache, la
 * ventilation par groupe et le détail nominatif des votes. Les trois tables
 * sont donc remplies en une passe, ce qui garantit qu'elles restent cohérentes
 * entre elles.
 */
#[AsCommand(
    name: 'app:import:scrutins',
    description: 'Importe scrutins, ventilations par groupe et votes nominatifs depuis le dépôt des Tricoteuses.',
)]
class ImportScrutinsCommand extends ImportTricoteusesCommand
{
    /** Vote officiel, par opposition aux rectifications déposées après coup. */
    private const VOTE_OFFICIEL = 'decompteNominatif';

    /** Rectification d'un député affichée par le site à côté du vote officiel. */
    private const MISE_AU_POINT = 'miseAuPoint';

    /** Position majoritaire d'un groupe dont aucun membre ne s'est exprimé. */
    private const NON_VOTANT = 'nv';

    /**
     * Positions du décompte nominatif, et le nom qu'elles portent dans la base.
     * L'open data emploie le pluriel pour les listes, le site le singulier.
     */
    private const POSITIONS = [
        'pour' => 'pour',
        'contre' => 'contre',
        'abstentions' => 'abstention',
        'nonVotants' => 'nonVotant',
    ];

    private const COLONNES_SCRUTIN = [
        'uid', 'numero', 'legislature', 'date_scrutin', 'titre', 'objet',
        'sort_code', 'sort_libelle', 'type_vote', 'code_type_vote', 'nature_vote',
        'demandeur', 'seance_ref', 'nombre_votants', 'suffrages_exprimes', 'nombre_pour',
        'nombre_contre', 'nombre_abstentions', 'nombre_non_votants', 'dossier_id',
        'created_at', 'updated_at',
    ];

    /**
     * Colonnes réécrites quand le scrutin existe déjà.
     *
     * `dossier_id` en est délibérément absent : l'Assemblée ne publie le
     * dossier dans le scrutin que dans un cas sur trois, et un import qui
     * réécrirait cette colonne effacerait les rattachements établis par
     * ailleurs. Le rattachement se fait donc à part, et jamais en retirant.
     */
    private const COLONNES_MAJ_SCRUTIN = [
        'numero', 'legislature', 'date_scrutin', 'titre', 'objet',
        'sort_code', 'sort_libelle', 'type_vote', 'code_type_vote', 'nature_vote',
        'demandeur', 'seance_ref', 'nombre_votants', 'suffrages_exprimes', 'nombre_pour',
        'nombre_contre', 'nombre_abstentions', 'nombre_non_votants', 'updated_at',
    ];

    private const COLONNES_VENTILATION = [
        'scrutin_id', 'groupe_id', 'nombre_membres_groupe', 'position_majoritaire',
        'nombre_pours', 'nombre_contres', 'nombre_abstentions', 'non_votants',
        'non_votants_volontaires',
    ];

    private const COLONNES_VOTE = [
        'depute_id', 'scrutin_id', 'position', 'vote_type', 'cause_position',
        'par_delegation', 'scrutin_date', 'created_at', 'updated_at',
    ];

    /** Votes nominatifs écartés faute de député connu (acteurRef absent de `depute`). */
    private int $votesEcartes = 0;

    protected function depotParDefaut(): string
    {
        return 'scrutins';
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('sans-votes', null, InputOption::VALUE_NONE, 'N\'importe pas le détail nominatif des votes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');
        $sansVotes = (bool) $input->getOption('sans-votes');
        $chemin = $this->chemin($input);

        $io->title('Import des scrutins');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les scrutins modifiés.' : 'Import complet du dépôt.');

        $maintenant = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $dossiers = $this->connection->fetchAllKeyValue('SELECT dossier_id, id FROM dossier');
        $groupes = $this->connection->fetchAllKeyValue('SELECT uid, id FROM groupe');
        $deputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        // Premier passage : les scrutins, dont on a besoin des identifiants
        // pour rattacher ventilations et votes.
        $uids = [];
        $refsDossier = [];
        $lot = [];
        // Les dépôts n'ont pas tous la même charpente : Scrutins XVI et XVII
        // séparent l'Assemblée (`AN/`) du Congrès (`CG/`), que l'on écarte —
        // ses votes vivent sous `vote_c<n>` et son numéro entre en collision
        // avec celui d'un scrutin ordinaire. Scrutins XIV et XV rangent tout à
        // la racine (`R5/`), sans aucun fichier du Congrès : on y lit tout.
        $sousDossier = is_dir($chemin . '/AN') ? 'AN' : '';
        foreach ($this->fichiers($chemin, $sousDossier, $tout) as $fichier) {
            $scrutin = $this->lisJson($fichier);
            if ($scrutin === null || !isset($scrutin['uid'])) {
                continue;
            }

            $uids[$scrutin['uid']] = $fichier;

            $dossierId = $dossiers[$scrutin['objet']['dossierLegislatif']['dossierRef'] ?? ''] ?? null;
            if ($dossierId !== null) {
                $refsDossier[$scrutin['uid']] = (int) $dossierId;
            }

            $lot[] = $this->ligneScrutin($scrutin, $dossiers, $maintenant);

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('scrutin', self::COLONNES_SCRUTIN, $lot, self::COLONNES_MAJ_SCRUTIN);
                $lot = [];
            }
        }
        $this->upsert('scrutin', self::COLONNES_SCRUTIN, $lot, self::COLONNES_MAJ_SCRUTIN);

        if ($uids === []) {
            $io->success('Aucun scrutin à importer.');

            return Command::SUCCESS;
        }

        $io->text(sprintf('%d scrutin%s lu%s.', \count($uids), \count($uids) > 1 ? 's' : '', \count($uids) > 1 ? 's' : ''));

        $identifiants = $this->identifiantsScrutins(array_keys($uids));

        if ($refsDossier !== []) {
            $io->text(sprintf('Dossiers rattachés depuis les scrutins : %d.', $this->rattacheDossiers($refsDossier, $identifiants)));
        }

        $ventilations = 0;
        $ventilationsDeduites = 0;
        $ventilationsParElimination = 0;
        $ventilationsSansIndice = 0;
        $ventilationsEcartees = 0;
        $votes = 0;
        $this->votesEcartes = 0;
        $lotVentilation = [];
        $lotVote = [];

        foreach ($uids as $uid => $fichier) {
            $scrutin = $this->lisJson($fichier);
            if ($scrutin === null) {
                continue;
            }

            $scrutinId = $identifiants[$uid] ?? null;
            if ($scrutinId === null) {
                continue;
            }

            $date = $this->date($scrutin['dateScrutin'] ?? null);
            // Un groupe ne peut tenir qu'une ligne par scrutin (clé unique
            // scrutin_id + groupe_id). Sans cette garde, deux blocs ramenés au
            // même groupe — par la réattribution SOC ci-dessous ou par une
            // déduction fausse — se recouvriraient en silence dans l'upsert.
            $groupesVus = [];
            $orphelins = [];

            foreach ($scrutin['ventilationVotes']['groupes'] ?? [] as $groupe) {
                $decompteNominatif = $groupe['vote']['decompteNominatif'] ?? [];

                // Les votes nominatifs se lisent avant toute résolution de
                // groupe : une ligne `vote` est (député, scrutin, position) et
                // ne doit rien au bloc qui la porte. Les lire dans la foulée du
                // groupe faisait perdre 1 916 votes des quatorze scrutins à
                // l'organeRef illisible (cf. plus bas) — le détail nominatif
                // d'un scrutin entier disparaissait avec sa ventilation.
                if (!$sansVotes) {
                    foreach ($this->votesNominatifs($decompteNominatif, $deputes, $scrutinId, $date, self::VOTE_OFFICIEL, $maintenant) as $ligne) {
                        $lotVote[] = $ligne;
                        ++$votes;
                    }
                }

                // L'Assemblée publie les ventilations du groupe socialiste de
                // la 16e sous PO800496 même après son changement de nom en
                // SOC-A (PO830170) le 19/10/2023 : sans réattribution, 581
                // ventilations restent au groupe dissous, qui « vote » alors
                // cinq mois après sa disparition — participation, cohésion et
                // proximités des deux SOC en sortent faussées. Même correctif
                // que le legacy (daily.php:1430, « Bug fix for socialist
                // group »).
                $organeRef = $groupe['organeRef'] ?? '';
                if ($organeRef === 'PO800496' && $date !== null && $date >= '2023-10-19') {
                    $organeRef = 'PO830170';
                }

                $groupeId = $groupes[$organeRef] ?? null;
                $deduit = false;
                if ($groupeId === null) {
                    $groupeId = $this->groupeDeduit($decompteNominatif, $date);
                    $deduit = $groupeId !== null;
                }

                if ($groupeId === null) {
                    // Bloc muet : aucun votant à interroger. Mis de côté plutôt
                    // qu'écarté — l'élimination, elle, le retrouvera une fois
                    // tous les autres blocs du scrutin placés.
                    $orphelins[] = $groupe;
                    continue;
                }

                if (isset($groupesVus[$groupeId])) {
                    ++$ventilationsEcartees;
                    continue;
                }

                $groupesVus[$groupeId] = true;
                $lotVentilation[] = $this->ligneVentilation($groupe, $scrutinId, (int) $groupeId);
                ++$ventilations;
                $ventilationsDeduites += $deduit ? 1 : 0;

                if (\count($lotVentilation) >= self::TAILLE_LOT) {
                    $this->upsert('vote_groupe', self::COLONNES_VENTILATION, $lotVentilation, \array_slice(self::COLONNES_VENTILATION, 2));
                    $lotVentilation = [];
                }
            }

            // Second passage : les blocs muets se déduisent des groupes que le
            // premier n'a pas consommés. Il faut que tous les autres soient
            // placés pour que le reste soit sûr — d'où deux passages.
            if ($orphelins !== []) {
                $parElimination = $this->groupesParElimination($orphelins, $groupesVus, $date);

                foreach ($orphelins as $rang => $groupe) {
                    $groupeId = $parElimination[$rang] ?? null;
                    if ($groupeId === null) {
                        ++$ventilationsSansIndice;
                        continue;
                    }

                    $groupesVus[$groupeId] = true;
                    $lotVentilation[] = $this->ligneVentilation($groupe, $scrutinId, $groupeId);
                    ++$ventilations;
                    ++$ventilationsParElimination;
                }
            }

            // Les mises au point sont des rectifications déposées après le
            // scrutin ; le site les affiche à côté du vote officiel, qu'elles
            // ne remplacent pas — d'où leur propre vote_type.
            if (!$sansVotes) {
                foreach ($this->votesNominatifs($scrutin['miseAuPoint'] ?? [], $deputes, $scrutinId, $date, self::MISE_AU_POINT, $maintenant) as $ligne) {
                    $lotVote[] = $ligne;
                    ++$votes;
                }
            }

            if (\count($lotVote) >= 2000) {
                $this->upsert('vote', self::COLONNES_VOTE, $lotVote, ['position', 'cause_position', 'par_delegation', 'scrutin_date', 'updated_at']);
                $lotVote = [];
            }
        }

        $this->upsert('vote_groupe', self::COLONNES_VENTILATION, $lotVentilation, \array_slice(self::COLONNES_VENTILATION, 2));
        $this->upsert('vote', self::COLONNES_VOTE, $lotVote, ['position', 'cause_position', 'par_delegation', 'scrutin_date', 'updated_at']);

        $io->text(sprintf('%d ventilations par groupe, %d votes nominatifs.', $ventilations, $votes));

        if ($ventilationsDeduites > 0) {
            $io->text(sprintf('Dont %d au groupe déduit de ses votants, faute d\'un organeRef exploitable.', $ventilationsDeduites));
        }

        if ($ventilationsParElimination > 0) {
            $io->text(sprintf('Dont %d au groupe retrouvé par élimination, faute du moindre votant à interroger.', $ventilationsParElimination));
        }

        if ($ventilationsSansIndice > 0) {
            $io->text(sprintf(
                'Non reconstituables : %d ventilation%s muette%s que l\'élimination n\'a pas su trancher.',
                $ventilationsSansIndice,
                $ventilationsSansIndice > 1 ? 's' : '',
                $ventilationsSansIndice > 1 ? 's' : '',
            ));
        }

        // Un import qui écarte des lignes rend l'écart à l'unité : sans ce
        // bilan, une règle fausse ressemble en tout point à une source
        // incomplète (cf. CLAUDE.md).
        if ($ventilationsEcartees > 0 || $this->votesEcartes > 0) {
            $io->warning(sprintf(
                'Écartés : %d ventilation%s (groupe inconnu de la table `groupe`), %d vote%s (député inconnu de la table `depute`).',
                $ventilationsEcartees,
                $ventilationsEcartees > 1 ? 's' : '',
                $this->votesEcartes,
                $this->votesEcartes > 1 ? 's' : '',
            ));
        }

        $rattaches = $this->rattacheDecryptages();
        $io->text(sprintf('Décryptages rattachés à leur scrutin : %d.', $rattaches));

        $io->success(sprintf('%d scrutins en base.', (int) $this->connection->fetchOne('SELECT COUNT(*) FROM scrutin')));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, int|string> $dossiers
     *
     * @return list<mixed>
     */
    private function ligneScrutin(array $scrutin, array $dossiers, string $maintenant): array
    {
        $objet = $scrutin['objet']['libelle'] ?? null;
        $dossierRef = $scrutin['objet']['dossierLegislatif']['dossierRef'] ?? null;
        $decompte = $scrutin['syntheseVote']['decompte'] ?? [];

        return [
            $scrutin['uid'],
            $this->entier($scrutin['numero'] ?? null),
            $this->entier($scrutin['legislature'] ?? null),
            $this->date($scrutin['dateScrutin'] ?? null),
            // Coquilles et intitulés périmés de l'open data, corrigés à la
            // main comme sur datan.fr. L'objet reste brut : il n'est affiché
            // nulle part et app:lien:scrutins y lit le numéro d'amendement.
            CorrectionTitreScrutin::corriger($scrutin['titre'] ?? null),
            $objet,
            $scrutin['sort']['code'] ?? null,
            $scrutin['sort']['libelle'] ?? null,
            $scrutin['typeVote']['libelleTypeVote'] ?? null,
            $scrutin['typeVote']['codeTypeVote'] ?? null,
            NatureVote::depuisLibelle($objet ?? $scrutin['titre'] ?? null),
            $scrutin['demandeur']['texte'] ?? null,
            // La séance relie le scrutin à son compte rendu, donc aux débats
            // qui l'ont précédé (brouillon de décryptage par IA).
            $scrutin['seanceRef'] ?? null,
            $this->entier($scrutin['syntheseVote']['nombreVotants'] ?? null),
            $this->entier($scrutin['syntheseVote']['suffragesExprimes'] ?? null),
            $this->entier($decompte['pour'] ?? null),
            $this->entier($decompte['contre'] ?? null),
            $this->entier($decompte['abstentions'] ?? null),
            $this->entier($decompte['nonVotants'] ?? null),
            $dossierRef !== null ? ($dossiers[$dossierRef] ?? null) : null,
            $maintenant,
            $maintenant,
        ];
    }

    /**
     * @return list<mixed>
     */
    private function ligneVentilation(array $groupe, int $scrutinId, int $groupeId): array
    {
        $voix = $groupe['vote']['decompteVoix'] ?? [];
        $pour = (int) ($voix['pour'] ?? 0);
        $contre = (int) ($voix['contre'] ?? 0);
        $abstentions = (int) ($voix['abstentions'] ?? 0);

        // Ne PAS reprendre le `positionMajoritaire` publié par l'Assemblée : il ne
        // départage que pour et contre, en ignorant les abstentions — un groupe à
        // 4 pour / 1 contre / 17 abstentions y est déclaré « pour ». Le site
        // recalcule depuis toujours la pluralité stricte sur les trois positions
        // (daily.php:1439-1449) : égalité ou personne d'exprimé → « nv ». S'écarter
        // de cette règle avait décalé loyautés et proximités de tout le site
        // (6 983 ventilations divergentes, Bernalicis à 98 % au lieu de 100 %).
        $position = match (true) {
            $pour + $contre + $abstentions === 0 => self::NON_VOTANT,
            $pour > $contre && $pour > $abstentions => 'pour',
            $contre > $pour && $contre > $abstentions => 'contre',
            $abstentions > $pour && $abstentions > $contre => 'abstention',
            default => self::NON_VOTANT,
        };

        return [
            $scrutinId,
            $groupeId,
            (int) ($groupe['nombreMembresGroupe'] ?? 0),
            $position,
            $pour,
            $contre,
            $abstentions,
            (int) ($voix['nonVotants'] ?? 0),
            (int) ($voix['nonVotantsVolontaires'] ?? 0),
        ];
    }

    /**
     * Retrouve le groupe d'un bloc de ventilation par les députés qu'il liste,
     * quand l'Assemblée n'en publie pas l'organeRef.
     *
     * Elle écrit parfois le code sentinelle `PO0` à la place : quatorze scrutins
     * de la 17e, dont les douze du 2 décembre 2024 où **tous** les groupes le
     * portent. Les Tricoteuses recopient le fichier tel quel — c'est leur rôle —
     * et le legacy n'en traite rien. Le bloc reste pourtant identifiable : ses
     * votants appartiennent à un seul groupe, qu'on lit dans leur rattachement
     * principal à la date du scrutin. Sur les quatorze fichiers, les blocs
     * ressortent unanimes (36/36 RN, 41/41 EPR, 22/22 LFI-NFP…) et leurs
     * effectifs reproduisent ceux du scrutin voisin du même jour.
     *
     * La déduction ne peut rien inventer : elle passe par `fonction_groupe`, donc
     * ne rend jamais qu'un groupe déjà en base. Et elle exige l'unanimité — un
     * bloc de ventilation est homogène par construction, si ses députés divergent
     * c'est notre datation des rattachements qui est en cause, et une ligne
     * fabriquée fausserait cohésion et proximités bien plus qu'une ligne absente.
     *
     * Restent 12 blocs sur 146 dont aucun membre n'a voté : sans votant, plus
     * rien à interroger. Ceux-là se retrouvent par élimination, une fois tous
     * les autres placés — voir {@see groupesParElimination()}.
     *
     * @param array<string, mixed> $decompteNominatif
     */
    private function groupeDeduit(array $decompteNominatif, ?string $date): ?int
    {
        if ($date === null) {
            return null;
        }

        $acteurs = [];
        foreach (array_keys(self::POSITIONS) as $cle) {
            foreach ($decompteNominatif[$cle] ?? [] as $votant) {
                if (isset($votant['acteurRef'])) {
                    $acteurs[$votant['acteurRef']] = true;
                }
            }
        }

        if ($acteurs === []) {
            return null;
        }

        // `nomin_principale` est indispensable : onze députés de la 17e portent
        // deux rattachements ouverts, et sans ce filtre le bloc paraît partagé
        // entre deux groupes — donc non unanime, donc écarté.
        $groupes = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT fg.groupe_id
               FROM fonction_groupe fg
               JOIN depute d ON d.id = fg.depute_id
              WHERE d.mp_id IN (?)
                AND fg.nomin_principale = 1
                AND (fg.date_debut IS NULL OR fg.date_debut <= ?)
                AND (fg.date_fin IS NULL OR fg.date_fin >= ?)',
            [array_keys($acteurs), $date, $date],
            [\Doctrine\DBAL\ArrayParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING],
        );

        return \count($groupes) === 1 ? (int) $groupes[0] : null;
    }

    /**
     * Retrouve les blocs qu'aucun votant ne désigne, par élimination.
     *
     * Une ventilation liste chaque groupe en activité une fois et une seule.
     * Les blocs dont personne n'a voté ne portent donc pas moins d'information
     * que les autres : ils occupent les places que les groupes déjà identifiés
     * ont laissées libres. Sur les dix scrutins à un seul bloc muet, il ne reste
     * qu'un groupe disponible et la réponse est forcée, sans même regarder les
     * effectifs.
     *
     * Les scrutins 492 et 493 en ont deux (GDR et NI, tous deux silencieux) : là
     * l'effectif départage. Notre décompte des rattachements diverge d'une unité
     * de celui de l'Assemblée à cette date — GDR à 16 chez nous contre 17 dans
     * le fichier — mais l'écart ne crée aucune ambiguïté, l'autre candidat étant
     * un groupe de 9. D'où l'exigence ci-dessous : le meilleur appariement doit
     * être *strictement* meilleur que le suivant, sans quoi on renonce au
     * scrutin entier plutôt que d'en placer une partie au jugé.
     *
     * Deux garde-fous encadrent la méthode. Le nombre de blocs muets doit égaler
     * exactement celui des groupes libres : sinon notre liste de groupes actifs
     * ne décrit pas la même Assemblée que le fichier, et l'élimination ne veut
     * plus rien dire. Et un renoncement est total, jamais partiel — un bloc mal
     * placé fausse cohésion et proximités bien plus qu'un bloc absent.
     *
     * @param list<array<string, mixed>> $orphelins
     * @param array<int, bool>           $groupesVus groupes déjà pris sur ce scrutin
     *
     * @return array<int, int> rang de l'orphelin → id du groupe
     */
    private function groupesParElimination(array $orphelins, array $groupesVus, ?string $date): array
    {
        if ($date === null) {
            return [];
        }

        $actifs = $this->connection->fetchAllKeyValue(
            'SELECT fg.groupe_id, COUNT(*) AS effectif
               FROM fonction_groupe fg
              WHERE fg.nomin_principale = 1
                AND (fg.date_debut IS NULL OR fg.date_debut <= ?)
                AND (fg.date_fin IS NULL OR fg.date_fin >= ?)
              GROUP BY fg.groupe_id',
            [$date, $date],
        );

        $libres = array_diff_key(array_map(intval(...), $actifs), $groupesVus);
        if (\count($libres) !== \count($orphelins)) {
            return [];
        }

        // Du bloc le plus fourni au plus modeste : les grands effectifs se
        // distinguent le mieux, et les placer d'abord réduit d'autant le choix
        // laissé aux suivants.
        $rangs = array_keys($orphelins);
        usort($rangs, static fn (int $a, int $b) => (int) $orphelins[$b]['nombreMembresGroupe'] <=> (int) $orphelins[$a]['nombreMembresGroupe']);

        $paires = [];
        foreach ($rangs as $rang) {
            $effectifBloc = (int) ($orphelins[$rang]['nombreMembresGroupe'] ?? 0);

            $ecarts = [];
            foreach ($libres as $groupeId => $effectif) {
                $ecarts[$groupeId] = abs($effectif - $effectifBloc);
            }
            asort($ecarts);

            $candidats = array_keys($ecarts);
            $meilleur = $candidats[0];
            if (isset($candidats[1]) && $ecarts[$meilleur] === $ecarts[$candidats[1]]) {
                return [];
            }

            $paires[$rang] = (int) $meilleur;
            unset($libres[$meilleur]);
        }

        return $paires;
    }

    /**
     * @param array<string, mixed>      $decompte
     * @param array<string, int|string> $deputes
     *
     * @return iterable<list<mixed>>
     */
    private function votesNominatifs(array $decompte, array $deputes, int $scrutinId, ?string $date, string $type, string $maintenant): iterable
    {
        foreach (self::POSITIONS as $cle => $position) {
            foreach ($decompte[$cle] ?? [] as $votant) {
                $deputeId = $deputes[$votant['acteurRef'] ?? ''] ?? null;
                if ($deputeId === null) {
                    ++$this->votesEcartes;
                    continue;
                }

                yield [
                    $deputeId,
                    $scrutinId,
                    $position,
                    $type,
                    $votant['causePositionVote'] ?? null,
                    !empty($votant['parDelegation']) ? 1 : 0,
                    $date,
                    $maintenant,
                    $maintenant,
                ];
            }
        }
    }

    /**
     * Rattache les scrutins à leur dossier législatif.
     *
     * Écrit par un UPDATE dédié plutôt que par l'upsert, pour ne jamais
     * effacer un rattachement que l'Assemblée aurait omis de republier.
     *
     * @param array<string, int> $refsDossier  uid du scrutin → id du dossier
     * @param array<string, int> $identifiants uid du scrutin → id du scrutin
     */
    private function rattacheDossiers(array $refsDossier, array $identifiants): int
    {
        $paires = [];
        foreach ($refsDossier as $uid => $dossierId) {
            if (isset($identifiants[$uid])) {
                $paires[$identifiants[$uid]] = $dossierId;
            }
        }

        $rattaches = 0;
        foreach (array_chunk($paires, 500, true) as $paquet) {
            $cas = '';
            $parametres = [];
            foreach ($paquet as $scrutinId => $dossierId) {
                $cas .= ' WHEN ? THEN ?';
                $parametres[] = $scrutinId;
                $parametres[] = $dossierId;
            }

            $rattaches += (int) $this->connection->executeStatement(
                sprintf(
                    'UPDATE scrutin SET dossier_id = CASE id%s END WHERE id IN (%s)',
                    $cas,
                    implode(', ', array_fill(0, \count($paquet), '?')),
                ),
                [...$parametres, ...array_keys($paquet)],
            );
        }

        return $rattaches;
    }

    /**
     * @param list<string> $uids
     *
     * @return array<string, int>
     */
    private function identifiantsScrutins(array $uids): array
    {
        $identifiants = [];

        foreach (array_chunk($uids, 1000) as $paquet) {
            $identifiants += $this->connection->fetchAllKeyValue(
                'SELECT uid, id FROM scrutin WHERE uid IN (?)',
                [$paquet],
                [\Doctrine\DBAL\ArrayParameterType::STRING],
            );
        }

        return array_map(intval(...), $identifiants);
    }

    /**
     * Rattache les décryptages éditoriaux aux scrutins. Idempotent.
     *
     * On privilégie l'identifiant de scrutin (vote_id ↔ uid) : c'est la clé la
     * plus sûre, et la seule qui fonctionne pour les votes du Congrès, que
     * l'application d'origine stockait avec un numéro sentinelle -1 parce que
     * leur numérotation entre en collision avec celle de l'Assemblée.
     */
    private function rattacheDecryptages(): int
    {
        $rattaches = (int) $this->connection->executeStatement(
            'UPDATE decryptage d
             JOIN scrutin s ON s.uid = d.vote_id
             SET d.scrutin_id = s.id
             WHERE d.vote_id IS NOT NULL AND d.vote_id <> \'\'
               AND (d.scrutin_id IS NULL OR d.scrutin_id <> s.id)'
        );

        return $rattaches + (int) $this->connection->executeStatement(
            'UPDATE decryptage d
             JOIN scrutin s ON s.legislature = d.legislature AND s.numero = d.vote_numero
             SET d.scrutin_id = s.id
             WHERE d.scrutin_id IS NULL'
        );
    }
}
