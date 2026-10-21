<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Http\RechauffeurCache;
use App\Legislature;
use App\Repository\ResultatElectoralRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fiche de ville (City::index de l'application CodeIgniter d'origine, vue
 * application/views/departement/commune.php).
 *
 * Un bloc de la page du site reste hors de portée faute de données : le nom du
 * maire, dont la table de l'application d'origine est vide. Il est signalé là
 * où il devrait prendre place.
 */
class CommuneController extends AbstractController
{
    /** Le découpage ne bouge qu'aux élections. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Nombre de communes limitrophes proposées en haut de page. */
    private const VOISINES = 4;

    /** Seuil de population du bloc « Communes les plus peuplées ». */
    private const POPULATION_MINIMALE = DepartementController::POPULATION_MINIMALE;

    /** Voyelles devant lesquelles « de » s'élide. */
    private const VOYELLES = ['A', 'E', 'I', 'O', 'U', 'Y'];

    /** Un député ne représente la commune que s'il siège encore. */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    private const CARTE_SQL = 'd.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug,
                               d.departement_nom, d.departement_code, d.circonscription, d.civilite,
                               g.legislature, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                               ' . CouleurGroupe::SQL . ' AS groupe_couleur';

    /**
     * La législative dont la fiche détaille les résultats dans la commune, et
     * qui a son bloc à elle. Les scrutins antérieurs se contentent d'une carte
     * dans le bloc « Les dernières élections ».
     */
    private const LEGISLATIVE_COURANTE = 2024;

    /**
     * Adresses officielles des résultats, une par carte du bloc « Les dernières
     * élections » — c'est le lien « Plus d'infos » du pied de carte.
     */
    private const ARCHIVES = 'https://www.archives-resultats-elections.interieur.gouv.fr/resultats/';

    /**
     * Les deux finalistes des présidentielles de 2017 et de 2022 — les mêmes
     * deux fois. La table de résultats ne les note que par leur patronyme, seul
     * discriminant nécessaire à un second tour.
     */
    private const CANDIDATS = ['Macron' => 'Emmanuel Macron', 'LePen' => 'Marine Le Pen'];

    public function __construct(
        private readonly Connection $connection,
        private readonly ResultatElectoralRepository $resultats,
    ) {
    }

    #[Route(
        '/deputes/{departement}/ville_{commune}',
        name: 'commune_individual',
        // Le motif accepte les parenthèses : vingt-quatre slugs en portent, et
        // sans elles la fiche répond 404 là où le site sert la page — voir
        // {@see ElectionController::SLUG_COMMUNE}. Aucune de ces communes ne
        // dépasse le seuil de peuplement, le plan de site est donc inchangé.
        requirements: ['departement' => DepartementController::SLUG, 'commune' => ElectionController::SLUG_COMMUNE],
        methods: ['GET'],
    )]
    public function individual(string $departement, string $commune): Response
    {
        $ville = $this->commune($departement, $commune);

        if ($ville === null) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

        // La collation de MariaDB ignore la casse : `Corse-du-Sud-2A/ville_Ajaccio`
        // a donc trouvé sa ligne. On renvoie sur l'orthographe de la base plutôt
        // que de servir la même page sous deux adresses ({@see DepartementController::SLUG}).
        if ($departement !== $ville['dpt_slug'] || $commune !== $ville['slug']) {
            return $this->redirectToRoute(
                'commune_individual',
                ['departement' => $ville['dpt_slug'], 'commune' => $ville['slug']],
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        $circonscriptions = $this->connection->fetchFirstColumn(
            // Ordre numérique : la colonne de l'application d'origine est du
            // texte, ce qui range les circonscriptions de Paris 1, 10, 11 … 2, 3.
            'SELECT circonscription FROM commune_circonscription WHERE commune_id = :commune ORDER BY circonscription',
            ['commune' => $ville['id']],
        );

        $deputes = $this->deputes((string) $ville['dpt_code'], $circonscriptions);
        $autres = $this->autresDeputes((string) $ville['dpt_code'], array_column($deputes, 'mp_id'));

        $legislatives = $this->resultats->legislativesParCommune($insee = (string) $ville['code_insee']);
        $presidentielle = $this->resultats->presidentielleParCommune($insee);

        $response = $this->render('departement/commune.html.twig', [
            'legislature' => Legislature::COURANTE,
            'ville' => $ville + [
                'de' => $this->elision((string) $ville['nom']),
                'population_arrondie' => $this->populationArrondie($population = $this->entierOuNull($ville['population'])),
                'evolution' => $this->evolutionSurDixAns($population, $this->entierOuNull($ville['population2012'])),
            ],
            'circonscriptions' => $circonscriptions,
            'deputes' => $deputes,
            'autres_deputes' => $autres,
            'voisines' => $this->voisines((int) $ville['id']),
            'legislatives' => $this->derniereLegislative($legislatives[self::LEGISLATIVE_COURANTE] ?? []),
            'elections' => $this->dernieresElections($insee, $legislatives, $presidentielle, \count($circonscriptions)),
            'presidentielle_texte' => $this->recitPresidentielle((string) $ville['nom'], $presidentielle),
            'communes' => \in_array($ville['dpt_code'], DepartementController::SANS_COMMUNES, true)
                ? []
                : $this->communesDuDepartement((int) $ville['dpt_id']),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Députés', 'url' => $this->generateUrl('deputes_index')],
                [
                    'nom' => $ville['dpt_nom'] . ' (' . $ville['dpt_code'] . ')',
                    'url' => $this->generateUrl('departement_individual', ['departement' => $ville['dpt_slug']]),
                ],
                [
                    'nom' => $ville['nom'],
                    'url' => $this->generateUrl('commune_individual', ['departement' => $ville['dpt_slug'], 'commune' => $ville['slug']]),
                ],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Un slug de commune n'est unique ni en France ni dans son département :
     * Château-Chinon (Ville) et Château-Chinon (Campagne) — 58062 et 58063 —
     * partagent `chateau-chinon` dans la Nièvre. C'est le seul doublon du pays,
     * et une ambiguïté réelle de l'adresse, pas un défaut d'import : les deux
     * communes existent. L'application d'origine les fond en une ligne
     * (`GROUP BY c.circo`) et sert donc indifféremment l'une des deux ; nous
     * servons la plus peuplée, ce qui revient au même ici puisqu'elles
     * partagent la 2e circonscription.
     *
     * @return array<string, mixed>|null
     */
    private function commune(string $departement, string $commune): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT c.id, c.code_insee, c.nom, c.slug, c.population, c.population2012, c.code_postal,
                    c.maire_prenom, c.maire_nom, c.maire_civilite,
                    d.id AS dpt_id, d.code AS dpt_code, d.nom AS dpt_nom, d.slug AS dpt_slug,
                    d.libelle_dans, d.libelle_de, d.region
             FROM commune c
             JOIN departement d ON d.id = c.departement_id
             WHERE c.slug = :commune AND d.slug = :departement
             ORDER BY c.population DESC
             LIMIT 1',
            ['commune' => $commune, 'departement' => $departement],
        );

        return $ligne === false ? null : $ligne;
    }

    /**
     * Députés des circonscriptions que la commune recouvre.
     *
     * Rangés par circonscription, dans l'ordre du bloc qui les énumère juste
     * au-dessus. L'application d'origine ne les trie pas du tout : les cartes
     * sortent dans l'ordre d'insertion de sa table `deputes_all`, qu'aucune
     * requête ne peut reproduire.
     *
     * @param list<int> $circonscriptions
     *
     * @return list<array<string, mixed>>
     */
    private function deputes(string $code, array $circonscriptions): array
    {
        if ($circonscriptions === []) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            // `mail_an` n'alimente que le bloc de contact, absent de la liste
            // des autres députés du département : il reste hors de CARTE_SQL.
            'SELECT ' . self::CARTE_SQL . ', d.mail_an
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.departement_code = :code
               AND d.circonscription IN (:circonscriptions)
               AND ' . self::EN_EXERCICE . '
             ORDER BY d.circonscription',
            ['code' => $code, 'circonscriptions' => $circonscriptions, 'legislature' => Legislature::COURANTE],
            ['circonscriptions' => ArrayParameterType::INTEGER],
        );
    }

    /**
     * Autres députés du département, rangés par nom comme sur la page du
     * département, dont ce bloc est le prolongement.
     *
     * @param list<mixed> $exclus
     *
     * @return list<array<string, mixed>>
     */
    private function autresDeputes(string $code, array $exclus): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT ' . self::CARTE_SQL . '
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.departement_code = :code
               AND d.mp_id NOT IN (:exclus)
               AND ' . self::EN_EXERCICE . '
             ORDER BY d.lastname, d.firstname',
            // La liste ne peut pas être vide : une circonscription vacante
            // laisse une commune sans aucun député.
            ['code' => $code, 'exclus' => $exclus ?: [''], 'legislature' => Legislature::COURANTE],
            ['exclus' => ArrayParameterType::STRING],
        );
    }

    /**
     * Communes limitrophes, de la plus peuplée à la plus petite. La table porte
     * les deux sens du voisinage, un seul côté suffit donc à la lecture.
     *
     * @return list<array<string, mixed>>
     */
    private function voisines(int $commune): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT v.nom, v.slug, d.slug AS dpt_slug
             FROM commune_adjacente a
             JOIN commune v ON v.id = a.adjacente_id
             JOIN departement d ON d.id = v.departement_id
             WHERE a.commune_id = :commune
             ORDER BY v.population DESC
             LIMIT ' . self::VOISINES,
            ['commune' => $commune],
        );
    }

    /**
     * Résultats de la dernière législative, une carte par circonscription.
     *
     * Le site n'interroge que le second tour et ne se rabat sur le premier que
     * s'il ne trouve rien du tout — une seule ligne alors, celle du candidat
     * élu dès le premier tour, ce dont parle le paragraphe voisin.
     *
     * **Une commune partagée entre plusieurs circonscriptions n'en montre donc
     * que les circonscriptions allées au second tour** : Paris, décidé au
     * premier tour dans neuf de ses dix-huit, n'affiche que les neuf autres. Le
     * repli ne se déclenche pas, puisque le second tour a bien rendu quelque
     * chose. C'est le comportement du site, reproduit tel quel plutôt que
     * corrigé au jugé : compléter avec les vainqueurs du premier tour
     * ajouterait à la page des cartes qu'elle n'a jamais portées.
     *
     * @param array<int, array<int, array<string, mixed>>> $tours
     *
     * @return list<array<string, mixed>>
     */
    private function derniereLegislative(array $tours): array
    {
        $tour = 2;
        $blocs = $tours[2] ?? [];

        if ($blocs === []) {
            $tour = 1;
            $blocs = $tours[1] ?? [];

            if ($blocs !== []) {
                // `LIMIT 1` sur un tri par circonscription décroissante puis
                // voix décroissantes : une seule carte, un seul candidat.
                $derniere = array_key_last($blocs);
                $blocs = [$derniere => [
                    'circonscription' => $blocs[$derniere]['circonscription'],
                    'participation' => $blocs[$derniere]['participation'],
                    'candidats' => [$blocs[$derniere]['candidats'][0]],
                ]];
            }
        }

        return array_map(static fn (array $bloc) => $bloc + ['tour' => $tour], array_values($blocs));
    }

    /**
     * Les six cartes du bloc « Les dernières élections », de la plus récente à
     * la plus ancienne.
     *
     * Les législatives n'y figurent que si la commune tient dans une seule
     * circonscription : au-delà, une carte unique mêlerait des scrutins
     * distincts, et le bloc qui précède les détaille déjà.
     *
     * @param array<int, array<int, array<string, mixed>>> $legislatives
     * @param array<int, list<array<string, mixed>>>       $presidentielle
     *
     * @return list<array<string, mixed>>
     */
    private function dernieresElections(
        string $insee,
        array $legislatives,
        array $presidentielle,
        int $circonscriptions,
    ): array {
        $europeennes = $this->resultats->europeennesParCommune($insee);
        $cartes = [$this->carteEuropeennes($europeennes, 2024)];

        if ($circonscriptions === 1) {
            $cartes[] = $this->carteLegislatives($legislatives, 2022);
        }

        $cartes[] = $this->cartePresidentielle($presidentielle, 2022);
        $cartes[] = $this->carteEuropeennes($europeennes, 2019);

        if ($circonscriptions === 1) {
            $cartes[] = $this->carteLegislatives($legislatives, 2017);
        }

        $cartes[] = $this->cartePresidentielle($presidentielle, 2017);

        return $cartes;
    }

    /**
     * @param array<int, list<array<string, mixed>>> $presidentielle
     *
     * @return array<string, mixed>
     */
    private function cartePresidentielle(array $presidentielle, int $annee): array
    {
        return [
            'titre' => 'Élection présidentielle ' . $annee,
            'sous_titre' => 'Résultat du 2<sup>nd</sup> tour',
            'lien' => self::ARCHIVES . 'presidentielle-' . $annee . '/index.php',
            'resultats' => array_map(
                static fn (array $r) => ['label' => self::CANDIDATS[$r['candidat']] ?? $r['candidat'], 'valeur' => $r['part']],
                $presidentielle[$annee] ?? [],
            ),
        ];
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $legislatives
     *
     * @return array<string, mixed>
     */
    private function carteLegislatives(array $legislatives, int $annee): array
    {
        // Le sous-titre annonce le second tour même quand la commune n'en a pas
        // eu, comme sur le site : c'est le tour décisif qu'il désigne.
        $tours = $legislatives[$annee] ?? [];
        $blocs = $tours[2] ?? $tours[1] ?? [];
        $bloc = $blocs === [] ? null : reset($blocs);

        return [
            'titre' => 'Élections législatives ' . $annee,
            'sous_titre' => 'Résultat du 2<sup>nd</sup> tour',
            'lien' => self::ARCHIVES . 'legislatives-' . $annee . '/index.php',
            'resultats' => array_map(
                static fn (array $c) => [
                    'label' => $c['prenom'] . ' ' . ucfirst(mb_strtolower((string) $c['nom'])),
                    'valeur' => $c['part'],
                ],
                $bloc === null ? [] : $bloc['candidats'],
            ),
        ];
    }

    /**
     * @param array<int, list<array<string, mixed>>> $europeennes
     *
     * @return array<string, mixed>
     */
    private function carteEuropeennes(array $europeennes, int $annee): array
    {
        return [
            'titre' => 'Élections européennes ' . $annee,
            'sous_titre' => 'Les 3 premiers partis politiques',
            // L'adresse de 2024 n'a pas de tiret devant l'année, contrairement
            // aux cinq autres : c'est ainsi que le ministère l'a publiée.
            'lien' => self::ARCHIVES . 'europeennes' . ($annee === 2024 ? '' : '-') . $annee . '/index.php',
            'resultats' => array_map(
                static fn (array $l) => ['label' => $l['parti'], 'valeur' => $l['part']],
                \array_slice($europeennes[$annee] ?? [], 0, 3),
            ),
        ];
    }

    /**
     * Phrase qui commente la présidentielle de 2022 dans la commune, en la
     * comparant à celle de 2017.
     *
     * Elle compare le vainqueur de 2022 à lui-même cinq ans plus tôt : les deux
     * scrutins ont opposé les deux mêmes personnes, ce qui rend la comparaison
     * possible et n'a rien d'une généralité.
     *
     * @param array<int, list<array<string, mixed>>> $presidentielle
     */
    private function recitPresidentielle(string $commune, array $presidentielle): ?string
    {
        $tete = $presidentielle[2022][0] ?? null;

        if ($tete === null || $tete['votants'] === 0) {
            return null;
        }

        $anterieur = null;

        foreach ($presidentielle[2017] ?? [] as $ligne) {
            if ($ligne['candidat'] === $tete['candidat'] && $ligne['votants'] > 0) {
                $anterieur = $ligne;
            }
        }

        if ($anterieur === null) {
            return null;
        }

        $feminin = $tete['candidat'] !== 'Macron';
        $part = round((float) $tete['part']);
        $part2017 = round((float) $anterieur['part']);

        return sprintf(
            '<b>%s</b> est arrivé%s en tête à l\'élection présidentielle 2022 à %s. %s a récolté %d%% des voix.'
            . ' C\'est %s que son score en 2017, qui était de %d%%.',
            self::CANDIDATS[$tete['candidat']] ?? $tete['candidat'],
            $feminin ? 'e' : '',
            $commune,
            $feminin ? 'Elle' : 'Il',
            $part,
            match (true) {
                $part > $part2017 => 'plus',
                $part < $part2017 => 'moins',
                default => 'autant',
            },
            $part2017,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function communesDuDepartement(int $departement): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT nom, slug, population
             FROM commune
             WHERE departement_id = :departement AND population > :seuil
             ORDER BY population DESC, nom',
            ['departement' => $departement, 'seuil' => self::POPULATION_MINIMALE],
        );
    }

    /**
     * Population telle que le site l'annonce : arrondie au rang supérieur en ne
     * gardant que quatre chiffres significatifs (`dec_round()`). Amiens et ses
     * 133 625 habitants s'affichent donc « 133 700 ». Les communes de moins de
     * dix mille habitants sont données au nombre près.
     */
    private function populationArrondie(?int $population): ?int
    {
        if ($population === null) {
            return null;
        }

        $precision = \strlen((string) $population) - 4;

        if ($precision <= 0) {
            return $population;
        }

        $pas = 10 ** $precision;

        return (int) ceil($population / $pas) * $pas;
    }

    /**
     * Évolution de la population depuis le recensement de 2012, en points de
     * pourcentage et sans signe : la phrase porte déjà le sens.
     *
     * @return array{sens: string, valeur: float}|null
     */
    private function evolutionSurDixAns(?int $population, ?int $population2012): ?array
    {
        if ($population === null || $population2012 === null || $population2012 === 0) {
            return null;
        }

        $evolution = ($population - $population2012) / $population2012 * 100;

        return ['sens' => $evolution > 0 ? 'augmenté' : 'diminué', 'valeur' => abs($evolution)];
    }

    /**
     * « de » ou « d' » devant le nom de la commune. L'application d'origine
     * n'élide que devant un nom purement alphabétique : « d'Amiens » mais
     * « de Aix-en-Provence », le trait d'union suffisant à écarter le test.
     */
    private function elision(string $nom): string
    {
        return ctype_alpha($nom) && \in_array(strtoupper($nom[0]), self::VOYELLES, true) ? "d'" : 'de ';
    }

    /** 794 communes n'ont pas de population, et Paris pas de recensement 2012. */
    private function entierOuNull(mixed $valeur): ?int
    {
        return $valeur === null ? null : (int) $valeur;
    }
}
