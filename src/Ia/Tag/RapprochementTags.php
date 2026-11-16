<?php

namespace App\Ia\Tag;

use Anthropic\Client;

/**
 * La brique IA du vocabulaire : dire, parmi des mots-clés candidats, lesquels
 * désignent la même notion qu'un mot-clé existant ou qu'un autre candidat.
 *
 * **Stratégie arrêtée par le propriétaire du site : le moins de jetons
 * possible, en privilégiant un tag existant.**
 *
 * - Le vocabulaire n'entre pas dans les requêtes de résumé : il y coûterait sa
 *   longueur multipliée par le nombre d'amendements, et chaque nouveau tag
 *   invaliderait le cache du préfixe.
 * - Ce qui se reconnaît sans IA est reconnu sans IA ({@see NormaliseurTag}).
 * - Les seuls inconnus partent dans **une** requête par traitement, et
 *   chaque décision est mémorisée en alias : une variante ne se paie qu'une
 *   fois.
 * - **La requête ne reçoit pas tout le vocabulaire.** La production en
 *   comptait 3 853 clés, ~14 000 jetons, ~23 000 en régime établi : envoyé à
 *   chaque traitement, il pèserait plus que les résumés eux-mêmes. Chaque
 *   candidat n'est confronté qu'aux mots-clés qui peuvent le concerner
 *   ({@see self::sections()}) : les plus portés dans sa catégorie, et ses
 *   voisins lexicaux de toute catégorie. Rejoué sur l'historique réel par lots
 *   de 20 amendements, cela fait ~1 600 jetons de vocabulaire par requête au
 *   lieu de ~14 000 (~4 800 pour la seule restriction à la catégorie).
 *
 * Le même service sert le nettoyage ponctuel du vocabulaire
 * (`app:ia:tags:nettoyer`) : tous les mots-clés d'une catégorie y sont
 * candidats, sans existants.
 */
final class RapprochementTags
{
    /** Mots-clés les plus portés de la catégorie du candidat, toujours montrés. */
    public const TOP_CATEGORIE = 30;

    /** Voisins lexicaux retenus par candidat, les plus portés d'abord. */
    public const VOISINS_MAX = 30;

    /**
     * Assez pour la plus grosse catégorie du nettoyage (510 mots-clés, dont
     * une moitié rapprochée au pire : ~250 couples d'une quinzaine de jetons),
     * réflexion comprise. Une requête non diffusée au-delà de ~16 000 jetons
     * risque le délai HTTP du SDK.
     */
    public const MAX_TOKENS = 16000;

    /**
     * Effort par défaut de Haiku 5.5, réflexion adaptative active, à l'inverse
     * des résumés : une fusion est durable — tous les amendements du mot-clé
     * absorbé changent d'étiquette — et la requête est unique par traitement,
     * son prix ne se multiplie pas.
     */
    public const EFFORT = 'medium';

    public const CONSIGNES = <<<'CONSIGNES'
        Tu ranges le vocabulaire de mots-clés thématiques de Datan, un site qui explique les votes de l'Assemblée nationale. Chaque section ci-dessous regroupe, pour une catégorie, des mots-clés existants et des mots-clés candidats.

        Rends les candidats qui désignent exactement la même notion qu'un autre mot-clé de la requête, existant ou candidat : synonyme, variante d'écriture, sigle et forme développée (« IVG » et « interruption volontaire de grossesse »), singulier et pluriel. Pour chacun, donne la cible : un mot-clé existant de préférence, sinon le candidat le plus simple et le plus courant du groupe.

        Ne rapproche pas deux notions seulement voisines : une notion plus large ou plus étroite n'est pas un synonyme (« énergie » et « nucléaire », « agriculture » et « agriculture biologique » restent distincts). En cas de doute, ne rapproche pas. N'inclus pas les candidats sans équivalent. Recopie les mots-clés exactement comme ils sont écrits.
        CONSIGNES;

    /**
     * Regroupe les candidats par catégorie et leur adjoint les existants à
     * montrer.
     *
     * @param array<string, array{0: string, 1: ?string}> $candidats         slug => [libellé, catégorie de l'amendement]
     * @param array<string, string>                       $vocabulaire       canoniques existants, slug => libellé
     * @param array<string, int>                          $usage             amendements par canonique
     * @param array<string, array<string, int>>           $usageParCategorie catégorie => slug => amendements, décroissant
     *
     * @return array<string, array{existants: array<string, string>, candidats: array<string, string>}>
     */
    public static function sections(array $candidats, array $vocabulaire, array $usage, array $usageParCategorie): array
    {
        // Index des racines : les cinq premières lettres de chaque mot d'au
        // moins quatre lettres. « retraite-anticipee » rejoint « retraite »,
        // « age-de-retraite », « anticipation »… Grossier à dessein : on
        // cherche des voisins à montrer, la décision revient au modèle.
        $parRacine = [];
        foreach ($vocabulaire as $slug => $libelle) {
            foreach (self::racines($slug) as $racine) {
                $parRacine[$racine][] = $slug;
            }
        }

        $sections = [];
        foreach ($candidats as $slug => [$libelle, $categorie]) {
            $cle = $categorie ?? '';
            $sections[$cle] ??= ['existants' => [], 'candidats' => []];
            $sections[$cle]['candidats'][$slug] = $libelle;

            foreach (\array_slice($usageParCategorie[$cle] ?? [], 0, self::TOP_CATEGORIE, true) as $existant => $n) {
                if (isset($vocabulaire[$existant])) {
                    $sections[$cle]['existants'][$existant] = $vocabulaire[$existant];
                }
            }

            $voisins = [];
            foreach (self::racines($slug) as $racine) {
                foreach ($parRacine[$racine] ?? [] as $existant) {
                    $voisins[$existant] = $usage[$existant] ?? 0;
                }
            }
            arsort($voisins);
            foreach (\array_slice($voisins, 0, self::VOISINS_MAX, true) as $existant => $n) {
                $sections[$cle]['existants'][$existant] = $vocabulaire[$existant];
            }
        }
        ksort($sections);

        return $sections;
    }

    /**
     * @param array<string, array{existants: array<string, string>, candidats: array<string, string>}> $sections
     *
     * @return array<string, mixed> paramètres du SDK PHP
     */
    public static function parametres(string $modele, array $sections): array
    {
        $texte = [];
        foreach ($sections as $categorie => $section) {
            $texte[] = '## ' . ($categorie === '' ? 'Sans catégorie' : $categorie);
            $texte[] = 'Existants : ' . ($section['existants'] === [] ? '(aucun)' : implode(' | ', $section['existants']));
            $texte[] = 'Candidats : ' . implode(' | ', $section['candidats']);
            $texte[] = '';
        }

        return [
            'model' => $modele,
            'maxTokens' => self::MAX_TOKENS,
            'system' => self::CONSIGNES,
            'messages' => [['role' => 'user', 'content' => rtrim(implode("\n", $texte))]],
            'outputConfig' => [
                'effort' => self::EFFORT,
                'format' => ['type' => 'json_schema', 'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'synonymes' => ['type' => 'array', 'items' => [
                            'type' => 'object',
                            'properties' => ['candidat' => ['type' => 'string'], 'cible' => ['type' => 'string']],
                            'required' => ['candidat', 'cible'],
                            'additionalProperties' => false,
                        ]],
                    ],
                    'required' => ['synonymes'],
                    'additionalProperties' => false,
                ]],
            ],
        ];
    }

    /**
     * Envoie la requête et rend les couples proposés, en slugs.
     *
     * @param array<string, array{existants: array<string, string>, candidats: array<string, string>}> $sections
     *
     * @return list<array{0: string, 1: string}>
     */
    public function rapprocher(Client $client, string $modele, array $sections): array
    {
        $message = $client->messages->create(...self::parametres($modele, $sections));

        $texte = null;
        foreach ($message->content as $bloc) {
            if ($bloc->type === 'text') {
                $texte = $bloc->text;
                break;
            }
        }

        if ($message->stopReason !== 'end_turn' || $texte === null) {
            throw new \RuntimeException(sprintf('rapprochement inutilisable (stop_reason %s)', (string) $message->stopReason));
        }

        return self::lire($texte);
    }

    /** @return list<array{0: string, 1: string}> */
    public static function lire(string $texte): array
    {
        $json = json_decode($texte, true);
        $couples = [];
        foreach (\is_array($json['synonymes'] ?? null) ? $json['synonymes'] : [] as $couple) {
            if (\is_string($couple['candidat'] ?? null) && \is_string($couple['cible'] ?? null)) {
                $couples[] = [NormaliseurTag::slug($couple['candidat']), NormaliseurTag::slug($couple['cible'])];
            }
        }

        return $couples;
    }

    /**
     * Ramène les couples proposés à une décision par candidat : le slug de la
     * cible finale, ou null pour « nouveau ».
     *
     * Ce que le modèle propose est contrôlé, pas cru : un couple dont un terme
     * n'était pas dans la requête est écarté, et les chaînes (A → B, B → C)
     * sont aplaties. Les candidats d'un même groupe suivent l'existant que
     * l'un d'eux a désigné — le plus porté s'il y en a plusieurs — ; un groupe
     * sans existant se range sous son membre le plus porté (le plus court à
     * égalité), qui devient canonique. Le choix du canonique revient au PHP et
     * non au modèle : c'est l'usage qui doit décider de l'étiquette affichée.
     *
     * @param list<array{0: string, 1: string}> $couples
     * @param array<string, string>             $candidats slug => libellé
     * @param array<string, string>             $existants slug => libellé, cibles admises
     * @param array<string, int>                $poids     usage de chaque slug (existants et candidats)
     *
     * @return array<string, ?string>
     */
    public static function resoudre(array $couples, array $candidats, array $existants, array $poids): array
    {
        $parent = [];
        $trouver = static function (string $s) use (&$parent, &$trouver): string {
            if (!isset($parent[$s]) || $parent[$s] === $s) {
                return $s;
            }

            return $parent[$s] = $trouver($parent[$s]);
        };
        $versExistant = [];

        foreach ($couples as [$candidat, $cible]) {
            if (!isset($candidats[$candidat]) || $candidat === $cible) {
                continue;
            }
            if (isset($existants[$cible])) {
                $versExistant[$candidat][] = $cible;
            } elseif (isset($candidats[$cible])) {
                $a = $trouver($candidat);
                $b = $trouver($cible);
                if ($a !== $b) {
                    $parent[$a] = $b;
                }
            }
        }

        $groupes = [];
        foreach (array_keys($candidats) as $slug) {
            $groupes[$trouver($slug)][] = $slug;
        }

        $plusPorte = static function (array $slugs, array $libelles) use ($poids): string {
            usort($slugs, static fn (string $a, string $b) => [($poids[$b] ?? 0), mb_strlen($libelles[$a] ?? $a), $a]
                <=> [($poids[$a] ?? 0), mb_strlen($libelles[$b] ?? $b), $b]);

            return $slugs[0];
        };

        $decisions = [];
        foreach ($groupes as $membres) {
            $cibles = [];
            foreach ($membres as $membre) {
                array_push($cibles, ...($versExistant[$membre] ?? []));
            }

            if ($cibles !== []) {
                $existant = $plusPorte(array_values(array_unique($cibles)), $existants);
                foreach ($membres as $membre) {
                    $decisions[$membre] = $existant;
                }

                continue;
            }

            $canonique = \count($membres) > 1 ? $plusPorte($membres, $candidats) : $membres[0];
            foreach ($membres as $membre) {
                $decisions[$membre] = $membre === $canonique ? null : $canonique;
            }
        }

        return $decisions;
    }

    /** @return list<string> */
    private static function racines(string $slug): array
    {
        $racines = [];
        foreach (explode('-', $slug) as $mot) {
            if (\strlen($mot) >= 4) {
                $racines[] = substr($mot, 0, 5);
            }
        }

        return array_values(array_unique($racines));
    }
}
