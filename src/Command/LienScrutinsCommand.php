<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Rattache chaque scrutin à l'amendement qu'il met aux voix, et à son dossier
 * législatif, sans sortir sur le réseau.
 *
 * L'Assemblée ne publie pas ce lien. L'application d'origine le récupère en
 * grattant `assemblee-nationale.fr/dyn/{legislature}/scrutins/{numero}`
 * (`scripts/daily.php`, `votesAmendements()`). Il se reconstitue pourtant à
 * 98 % depuis les deux dépôts déjà moissonnés, par une clé que rien ne
 * signale : **`seanceDiscussionRef`**, porté par 30 752 amendements, se joint
 * au `seanceRef` que portent les 8 430 scrutins. Le numéro d'amendement, lui,
 * se lit dans l'objet du scrutin — « l'amendement n° 1762 de M. Le Coq à
 * l'article 2 du projet de loi de finances pour 2025 » — et c'est la seule
 * raison pour laquelle cette commande lit encore des fichiers : l'objet est le
 * seul des deux côtés de l'appariement qui ne soit pas en base.
 *
 * Du côté des amendements, {@see ImportAmendementsCommand} porte désormais les
 * clés en colonnes indexées, et l'appariement est une jointure. Il n'a plus à
 * parcourir les 123 224 fichiers du dépôt, ni à en tenir l'index en mémoire.
 *
 * Reste {@see ScraperScrutinsCommand} pour la centaine de scrutins que cette
 * voie ne couvre pas.
 */
#[AsCommand(
    name: 'app:lien:scrutins',
    description: 'Rattache les scrutins à leur amendement et à leur dossier depuis les dépôts moissonnés.',
)]
class LienScrutinsCommand extends ImportTricoteusesCommand
{
    /** Seuls ces scrutins mettent un amendement aux voix. */
    private const NATURES_AMENDEMENT = ['amendement', 'sous-amendement'];

    /**
     * Identifiant de l'amendement fictif que l'import des annexes a créé en
     * reprenant tel quel le `amendmentId` vide de la table `votes_amendments`
     * d'origine : la chaîne « NULL », et non l'absence de valeur.
     */
    private const AMENDEMENT_FANTOME = 'NULL';

    /**
     * Rattachements arbitrés à la main (31 juillet 2026), appliqués d'autorité :
     * aucune des voies automatiques ne sait les produire, et l'une d'elles
     * produit même l'inverse.
     *
     * Les dossiers d'abord. Sur ces pages, le site de l'Assemblée affiche
     * lui-même un dossier qui n'est pas celui du texte mis aux voix (le
     * scrutin 15/2769 vote « l'éthique de l'urgence », sa page renvoie au
     * dossier de la prime de naissance) : un scrape de rattrapage les
     * reprendrait fautifs à chaque rejeu sur base neuve. L'objet du scrutin,
     * qui nomme le texte en toutes lettres, tranche — il coïncide avec le
     * rattachement de datan.fr. Le vote du Congrès (VTCGR5L16V1) est à part :
     * sans séance de l'Assemblée ni page de scrutin, aucune voie ne le couvre.
     *
     * Les deux amendements ensuite : leurs pages de scrutin n'affichent plus
     * de lien, et la jointure par séance de discussion ne les trouve pas ;
     * l'objet nomme pourtant l'amendement sans ambiguïté (« n° 570 de
     * M. Jacobelli », « n° 1 de M. Breton ») et le numéro comme le texte
     * concordent avec l'amendement retenu — le même que datan.fr.
     */
    private const RATTACHEMENTS_ARBITRES = [
        'VTANR5L15V117' => ['dossier' => 'DLR5L15N35824'],
        'VTANR5L15V119' => ['dossier' => 'DLR5L15N35824'],
        'VTANR5L15V2769' => ['dossier' => 'DLR5L15N39819'],
        'VTANR5L15V3640' => ['dossier' => 'DLR5L15N41668'],
        'VTANR5L16V1162' => ['dossier' => 'DLR5L16N46622'],
        'VTANR5L16V2984' => ['dossier' => 'DLR5L16N47781'],
        'VTANR5L17V6758' => ['dossier' => 'DLR5L17N54085'],
        'VTCGR5L16V1' => ['dossier' => 'DLR5L16N49095'],
        'VTANR5L17V6288' => ['amendement' => 'AMANR5L17PO838901BTC2695P0D1N000570'],
        'VTANR5L17V7231' => ['amendement' => 'AMANR5L17PO838901BTC2835P0D1N000001'],
    ];

    /**
     * Mots trop répandus dans un objet de scrutin pour distinguer un auteur.
     * Sans eux, « l'amendement de suppression n° 828 » rapprocherait n'importe
     * quel amendement de suppression.
     */
    private const MOTS_VIDES = [
        'amendement', 'amendements', 'sous', 'article', 'apres', 'avant', 'projet',
        'proposition', 'suppression', 'identique', 'identiques', 'suivant', 'suivants',
        'examen', 'prioritaire', 'premiere', 'seconde', 'lecture', 'nouvelle',
        'resolution', 'unique', 'loi',
    ];

    /**
     * Les candidats d'un scrutin, et le rattachement qu'il porte déjà.
     *
     * Trois branches, dans l'ordre où elles font foi. La séance de discussion
     * d'abord : c'est la clé sûre, un numéro d'amendement y est unique. La date
     * de mise aux voix ensuite, nettement plus ambiguë — plusieurs textes se
     * discutent le même jour et leurs amendements sont numérotés
     * indépendamment — mais elle récupère les quelque deux cents scrutins que la
     * première laisse. La troisième ne cherche rien : elle ramène l'amendement
     * déjà rattaché, pour pouvoir juger s'il dément l'objet du scrutin.
     *
     * L'union sert les deux index de front (`idx_seance_numero`,
     * `idx_sort_numero`) là où un `OR` obligerait à les fusionner.
     *
     * Les paramètres sont positionnels et le numéro y figure deux fois :
     * `Connection::prepare()` remet la requête au pilote telle quelle, sans
     * traduire les paramètres nommés — eux ne valent que pour `executeQuery()`,
     * qui reprépare à chaque appel.
     */
    private const CANDIDATS = <<<'SQL'
        SELECT 0 AS rang, id, numero_ordre, signataires FROM amendement
         WHERE seance_ref = ? AND numero_ordre = ?
        UNION ALL
        SELECT 1, id, numero_ordre, signataires FROM amendement
         WHERE date_sort = ? AND numero_ordre = ?
        UNION ALL
        SELECT 2, id, numero_ordre, signataires FROM amendement
         WHERE id = ?
        SQL;

    protected function depotParDefaut(): string
    {
        return 'scrutins';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tout = (bool) $input->getOption('tout');
        $depotScrutins = $this->chemin($input);

        $io->title('Rattachement des scrutins');

        $purges = $this->purgeFantome();
        if ($purges > 0) {
            $io->text(sprintf('%d rattachements fictifs remis à zéro avant réappariement.', $purges));
        }

        $arbitres = $this->appliqueArbitrages();
        if ($arbitres > 0) {
            $io->text(sprintf('%d rattachements arbitrés appliqués.', $arbitres));
        }

        $scrutins = $this->scrutinsATraiter($depotScrutins, $tout);
        $io->text(sprintf('%d scrutins à examiner.', \count($scrutins)));

        if ($scrutins === []) {
            $io->success('Rien à rattacher.');

            return Command::SUCCESS;
        }

        $dossiers = $this->rattacheDossiers($scrutins);
        if ($dossiers > 0) {
            $io->text(sprintf('%d scrutins rattachés à leur dossier législatif.', $dossiers));
        }

        $aApparier = array_filter(
            $scrutins,
            static fn (array $s) => \in_array($s['nature'], self::NATURES_AMENDEMENT, true),
        );

        if ($aApparier === []) {
            $io->success('Aucun scrutin d\'amendement à apparier.');

            return Command::SUCCESS;
        }

        $bilan = $this->rattacheAmendements($aApparier);

        $io->table(
            ['Issue', 'Scrutins'],
            [
                ['rattachés (aucun lien jusqu\'ici)', $bilan['ecrits']],
                ['rattachement corrigé', $bilan['corriges']],
                ['déjà justes, inchangés', $bilan['inchanges']],
                ['refusés : auteur contredit', $bilan['refus_auteur']],
                ['laissés : plusieurs candidats', $bilan['ambigus']],
                ['laissés : aucun candidat', $bilan['sans_candidat']],
                ['laissés : objet sans numéro', $bilan['sans_numero']],
            ],
        );

        // Le reste à faire se compte sur les scrutins examinés, pas sur toute la
        // table : les législatures 15 et 16 y figurent aussi, sans dépôt
        // d'amendements pour les servir.
        $io->success(sprintf(
            '%d scrutins rattachés à un amendement — %d restent sans lien, pour app:scraper:scrutins.',
            $bilan['ecrits'] + $bilan['corriges'],
            $bilan['restants'],
        ));

        return Command::SUCCESS;
    }

    /**
     * Supprime l'amendement fictif et délie les scrutins qui pointaient dessus.
     *
     * C'est un préalable, pas un nettoyage de confort : 215 scrutins paraissent
     * rattachés alors qu'ils ne le sont pas, et la garde qui protège un
     * rattachement existant protégerait précisément ces faux liens.
     */
    private function purgeFantome(): int
    {
        $id = $this->connection->fetchOne(
            'SELECT id FROM amendement WHERE amendement_id = ?',
            [self::AMENDEMENT_FANTOME],
        );

        if ($id === false || $id === null) {
            return 0;
        }

        $delies = (int) $this->connection->executeStatement(
            'UPDATE scrutin SET amendement_id = NULL WHERE amendement_id = ?',
            [$id],
        );

        $this->connection->executeStatement('DELETE FROM amendement WHERE id = ?', [$id]);

        return $delies;
    }

    /**
     * Applique {@see self::RATTACHEMENTS_ARBITRES}, sans condition : ces liens
     * priment sur toute voie automatique, y compris un rattachement déjà posé —
     * c'est leur raison d'être. Idempotent, l'UPDATE ne compte que ce qui
     * change. Un uid absent de la base (rejeu partiel) est simplement ignoré.
     */
    private function appliqueArbitrages(): int
    {
        $changes = 0;

        foreach (self::RATTACHEMENTS_ARBITRES as $uid => $liens) {
            foreach (['dossier' => 'dossier_id', 'amendement' => 'amendement_id'] as $cle => $colonne) {
                if (!isset($liens[$cle])) {
                    continue;
                }

                $id = $this->connection->fetchOne(
                    sprintf('SELECT id FROM %s WHERE %s = ?', $cle, $cle === 'dossier' ? 'dossier_id' : 'amendement_id'),
                    [$liens[$cle]],
                );

                if ($id === false || $id === null) {
                    continue;
                }

                $changes += (int) $this->connection->executeStatement(
                    sprintf('UPDATE scrutin SET %1$s = ? WHERE uid = ? AND (%1$s IS NULL OR %1$s <> ?)', $colonne),
                    [$id, $uid, $id],
                );
            }
        }

        return $changes;
    }

    /**
     * Les scrutins à examiner, lus dans le dépôt et joints à leur ligne en base.
     *
     * Le delta de la moisson ne suffit pas comme périmètre : un scrutin déjà
     * moissonné peut n'avoir jamais trouvé son amendement, et le dépôt des
     * amendements, lui, continue de s'enrichir. On y ajoute donc tout scrutin
     * encore sans rattachement, ce qui rend la commande convergente — chaque
     * exécution ne peut que réduire le reste à faire.
     *
     * Chaque scrutin est réduit à ses champs d'appariement dès la lecture : un
     * fichier de scrutin porte la totalité du décompte nominatif, et garder les
     * 8 430 objets entiers épuise le tas avant même d'avoir commencé.
     *
     * @return list<array{id: int, nature: ?string, amendement: ?int, dossier: ?int, objet: string, seance: string, date: string, dossierRef: ?string}>
     */
    private function scrutinsATraiter(string $depot, bool $tout): array
    {
        $enBase = [];
        foreach ($this->connection->fetchAllAssociative(
            'SELECT id, uid, nature_vote, amendement_id, dossier_id FROM scrutin',
        ) as $ligne) {
            $enBase[$ligne['uid']] = $ligne;
        }

        // Le périmètre du delta, relevé avant le parcours complet. Sans fichier
        // de delta, `fichiers()` rend tout le dépôt : le périmètre est alors
        // total, ce qui est la consigne attendue.
        $modifies = [];
        if (!$tout) {
            foreach ($this->fichiers($depot, '', tout: false) as $fichier) {
                $modifies[$fichier] = true;
            }
        }

        $retenus = [];

        foreach ($this->fichiers($depot, '', tout: true) as $fichier) {
            $scrutin = $this->lisJson($fichier);
            if ($scrutin === null || !isset($scrutin['uid'], $enBase[$scrutin['uid']])) {
                continue;
            }

            $ligne = $enBase[$scrutin['uid']];

            $manque = $ligne['dossier_id'] === null
                || ($ligne['amendement_id'] === null && \in_array($ligne['nature_vote'], self::NATURES_AMENDEMENT, true));

            if (!$tout && !isset($modifies[$fichier]) && !$manque) {
                continue;
            }

            $retenus[] = [
                'id' => (int) $ligne['id'],
                'nature' => $ligne['nature_vote'],
                'amendement' => $ligne['amendement_id'] === null ? null : (int) $ligne['amendement_id'],
                'dossier' => $ligne['dossier_id'] === null ? null : (int) $ligne['dossier_id'],
                'objet' => (string) ($scrutin['objet']['libelle'] ?? $scrutin['titre'] ?? ''),
                'seance' => (string) ($scrutin['seanceRef'] ?? ''),
                'date' => substr((string) ($scrutin['dateScrutin'] ?? ''), 0, 10),
                'dossierRef' => $scrutin['objet']['dossierLegislatif']['dossierRef'] ?? null,
            ];
        }

        return $retenus;
    }

    /**
     * Rattache au dossier législatif les scrutins qui n'en ont pas.
     *
     * `objet.dossierLegislatif.dossierRef` n'est publié que depuis 2026 et ne
     * couvre qu'un quart des scrutins de la législature ; il ne remplace pas les
     * sources qui alimentent déjà `dossier_id`, il les complète là où elles
     * n'ont rien trouvé. D'où l'écriture conditionnée à `dossier_id IS NULL`.
     *
     * @param list<array<string, mixed>> $scrutins
     */
    private function rattacheDossiers(array $scrutins): int
    {
        $idDossiers = $this->connection->fetchAllKeyValue('SELECT dossier_id, id FROM dossier');
        $ecrits = 0;

        foreach ($scrutins as $scrutin) {
            if ($scrutin['dossier'] !== null || $scrutin['dossierRef'] === null) {
                continue;
            }

            $id = $idDossiers[$scrutin['dossierRef']] ?? null;
            if ($id === null) {
                continue;
            }

            $ecrits += (int) $this->connection->executeStatement(
                'UPDATE scrutin SET dossier_id = ? WHERE id = ? AND dossier_id IS NULL',
                [$id, $scrutin['id']],
            );
        }

        return $ecrits;
    }

    /**
     * @param list<array<string, mixed>> $scrutins
     *
     * @return array<string, int>
     */
    private function rattacheAmendements(array $scrutins): array
    {
        $requete = $this->connection->prepare(self::CANDIDATS);

        $bilan = array_fill_keys(
            ['ecrits', 'corriges', 'inchanges', 'refus_auteur', 'ambigus', 'sans_candidat', 'sans_numero', 'restants'],
            0,
        );

        // Un scrutin que cette commande n'apparie pas n'est pas pour autant sans
        // rattachement : le scraper a pu le servir lors d'un passage précédent.
        $laisse = static function (array $scrutin) use (&$bilan): void {
            if ($scrutin['amendement'] === null) {
                ++$bilan['restants'];
            }
        };

        foreach ($scrutins as $scrutin) {
            if (!\in_array($scrutin['nature'], self::NATURES_AMENDEMENT, true)) {
                continue;
            }

            $objet = $scrutin['objet'];
            $numero = $this->numeroVote($objet);

            if ($numero === null) {
                ++$bilan['sans_numero'];
                $laisse($scrutin);
                continue;
            }

            $requete->bindValue(1, $scrutin['seance'] === '' ? null : $scrutin['seance']);
            $requete->bindValue(2, $numero);
            $requete->bindValue(3, $scrutin['date'] === '' ? null : $scrutin['date']);
            $requete->bindValue(4, $numero);
            $requete->bindValue(5, $scrutin['amendement']);

            $rangs = [0 => [], 1 => [], 2 => []];
            foreach ($requete->executeQuery()->fetchAllAssociative() as $ligne) {
                $rangs[(int) $ligne['rang']][] = $ligne;
            }

            // Le rang 2 ne concourt pas : c'est le rattachement en place.
            $enPlace = $rangs[2][0] ?? null;
            $candidats = $rangs[0] !== [] ? $rangs[0] : $rangs[1];

            if ($candidats === []) {
                ++$bilan['sans_candidat'];
                $laisse($scrutin);
                continue;
            }

            $auteur = $this->auteur($objet);

            if (\count($candidats) > 1) {
                $candidats = array_values(array_filter(
                    $candidats,
                    fn (array $c) => $this->memeAuteur($auteur, (string) $c['signataires']),
                ));

                // Plusieurs amendements peuvent nommer l'auteur cherché : c'est
                // le cas courant d'un groupe qui cosigne tout ce qu'il dépose.
                // Les départager demande de savoir à quel rang chacun le porte.
                if (\count($candidats) > 1) {
                    $candidats = $this->meilleurRang($auteur, $candidats);
                }

                if (\count($candidats) !== 1) {
                    ++$bilan['ambigus'];
                    $laisse($scrutin);
                    continue;
                }
            }

            $trouve = $candidats[0];

            // La garde qui compte. L'objet du scrutin nomme l'auteur de ce qui
            // est mis aux voix ; si les signataires de l'amendement retenu ne le
            // mentionnent pas, l'appariement est faux et on n'écrit rien. Sur un
            // site dont la valeur est éditoriale, un lien faux coûte plus cher
            // que dix liens manquants.
            if ($auteur !== [] && !$this->memeAuteur($auteur, (string) $trouve['signataires'])) {
                ++$bilan['refus_auteur'];
                $laisse($scrutin);
                continue;
            }

            $actuel = $scrutin['amendement'];

            if ($actuel === (int) $trouve['id']) {
                ++$bilan['inchanges'];
                continue;
            }

            if ($actuel === null) {
                $this->ecritAmendement($scrutin['id'], (int) $trouve['id']);
                ++$bilan['ecrits'];
                continue;
            }

            // Un rattachement déjà là ne cède que si l'objet du scrutin le
            // contredit explicitement. Le scraper d'origine se trompe de deux
            // façons, toutes deux reconnaissables ici : il retient l'amendement
            // sous-amendé au lieu du sous-amendement voté — scrutin n° 365,
            // « le sous-amendement n° 3734 de Mme Alexandra Masson à
            // l'amendement n° 3630 du Gouvernement », où il rattache le 3630 du
            // Gouvernement — et il lui arrive de se décaler d'un rang, scrutin
            // n° 4810, objet « l'amendement n° 24 de M. Guitton », rattaché au
            // n° 25. Dans les deux cas le numéro ou l'auteur de l'amendement en
            // place dément l'objet, jamais l'inverse.
            $actuelDement = ($enPlace['numero_ordre'] ?? null) !== $numero
                || ($auteur !== [] && !$this->memeAuteur($auteur, (string) ($enPlace['signataires'] ?? '')));

            if ($actuelDement) {
                $this->ecritAmendement($scrutin['id'], (int) $trouve['id']);
                ++$bilan['corriges'];
            } else {
                ++$bilan['inchanges'];
            }
        }

        return $bilan;
    }

    /** Écriture par ordre dédié : `amendement_id` n'entre dans aucun upsert. */
    private function ecritAmendement(int $scrutinId, int $amendementId): void
    {
        $this->connection->executeStatement(
            'UPDATE scrutin SET amendement_id = ? WHERE id = ?',
            [$amendementId, $scrutinId],
        );
    }

    /**
     * Numéro de l'item mis aux voix, lu dans l'objet du scrutin.
     *
     * Le premier numéro rencontré est le bon, y compris pour un sous-amendement :
     * l'objet énonce d'abord ce qu'on vote, puis ce sur quoi il porte — « le
     * sous-amendement n° 3734 … à l'amendement n° 3630 ». C'est la lecture de
     * `scripts/daily.php`, que son propre appariement ne respecte pourtant pas.
     */
    private function numeroVote(string $objet): ?string
    {
        if (preg_match('/n[°º]\s*(\d+)/iu', $objet, $m) !== 1) {
            return null;
        }

        // `amendement.numero_ordre` est stocké sans rembourrage par
        // {@see ImportAmendementsCommand} ; c'est ce qui rend la comparaison
        // possible en SQL plutôt qu'en PHP.
        $numero = ltrim($m[1], '0');

        return $numero === '' ? '0' : $numero;
    }

    /**
     * Mots significatifs désignant l'auteur, tels que l'objet du scrutin les
     * énonce : « … n° 24 de M. Guitton à l'article 35 » donne « guitton ».
     *
     * @return array<string, true>
     */
    private function auteur(string $objet): array
    {
        if (preg_match('/n[°º]\s*\d+\s+(?:de|du|des|d[\'’])\s*(.{0,55}?)(?:\s+(?:[àa]|apr[èe]s|avant|et)\s|$)/iu', $objet, $m) !== 1) {
            return [];
        }

        return $this->jetons($m[1]);
    }

    /**
     * Les candidats qui portent l'auteur au rang le plus faible.
     *
     * L'objet d'un scrutin nomme celui qui dépose — « l'amendement n° 2 de
     * Mme Taurinya » —, et l'open data publie le déposant en tête de liste. Un
     * amendement où le nom cherché est en première position l'emporte donc sur
     * un amendement où il n'est qu'un cosignataire parmi soixante-dix.
     *
     * Ce départage est un choix, et il faut le dire : la même préférence
     * s'exerçait jusqu'ici par accident, `amendement.signataires` étant un
     * VARCHAR(500) qui coupait les listes de groupe et n'y laissait que les
     * premiers noms. La colonne est passée en TEXT ; la règle devait donc
     * devenir explicite, sans quoi elle disparaissait avec la troncature.
     *
     * Rien n'est décidé quand deux candidats portent l'auteur au même rang :
     * la liste rendue en compte alors plusieurs, et le scrutin part en ambigu.
     *
     * @param array<string, true>              $auteur
     * @param list<array<string, mixed>>       $candidats
     *
     * @return list<array<string, mixed>>
     */
    private function meilleurRang(array $auteur, array $candidats): array
    {
        $parRang = [];
        foreach ($candidats as $candidat) {
            $rang = $this->rangAuteur($auteur, (string) $candidat['signataires']);
            if ($rang !== null) {
                $parRang[$rang][] = $candidat;
            }
        }

        if ($parRang === []) {
            return $candidats;
        }

        ksort($parRang);

        return reset($parRang);
    }

    /**
     * Rang du signataire que l'objet nomme : 0 pour le déposant, 1 pour le
     * premier cosignataire, `null` s'il ne figure pas dans la liste.
     *
     * La liste sépare ses signataires par des virgules et joint le dernier par
     * « et » (« … Mme Reid Arbelot et M. Rimane ») ; les deux sont donc des
     * séparateurs. Découper un nom de groupe qui contiendrait la conjonction
     * est sans effet : ce qui est cherché est un nom de député, et il est en
     * amont dans la liste.
     *
     * @param array<string, true> $auteur
     */
    private function rangAuteur(array $auteur, string $signataires): ?int
    {
        foreach (preg_split('/\s*,\s*|\s+et\s+/u', $signataires) as $rang => $signataire) {
            if (array_intersect_key($auteur, $this->jetons($signataire)) !== []) {
                return $rang;
            }
        }

        return null;
    }

    /**
     * @param array<string, true> $auteur
     */
    private function memeAuteur(array $auteur, string $signataires): bool
    {
        if ($auteur === []) {
            return false;
        }

        return array_intersect_key($auteur, $this->jetons($signataires)) !== [];
    }

    /**
     * Mots d'un libellé, sans accents ni casse — l'open data écrit tantôt
     * « Mme Pirès Beaune », tantôt le nom échappé en entités HTML.
     *
     * @return array<string, true>
     */
    private function jetons(string $libelle): array
    {
        $libelle = html_entity_decode($libelle, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        $sansAccent = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $libelle);

        $jetons = [];
        foreach (preg_split('/[^a-z]+/', (string) $sansAccent) as $mot) {
            if (\strlen($mot) >= 4 && !\in_array($mot, self::MOTS_VIDES, true)) {
                $jetons[$mot] = true;
            }
        }

        return $jetons;
    }
}
