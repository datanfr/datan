<?php

namespace App\Ia\Amendement;

/**
 * Le prompt de résumé d'un amendement — le seul du dépôt.
 *
 * **Le texte est celui de la rédaction**, repris à la lettre du script
 * `generate_amendements_ia_submit_batch.php` du legacy : les six champs, leurs
 * formulations imposées (« Cet amendement vise à », « Les auteurs de
 * l'amendement estiment que »), l'échelle de simplicité et ses deux repères,
 * les dix-huit catégories, l'exemple. C'est un choix éditorial, pas un détail
 * technique : on ne le retouche pas ici sans la rédaction. Trois écarts
 * seulement, tous mécaniques :
 *
 * - « retourne uniquement un objet JSON valide » devient « retourne un objet
 *   JSON » : la forme est désormais garantie par la sortie structurée
 *   ({@see self::schema()}), qui rend aussi impossible une catégorie hors liste —
 *   la production en a écrit trois (« Énergie et environnement », « Tourisme et
 *   loisirs », chaîne vide) faute de cette contrainte ;
 * - l'exposé quitte la fin du prompt pour le message utilisateur. Les
 *   consignes, identiques d'une requête à l'autre, forment alors un préfixe
 *   que le cache de prompt sert à 0,1× le prix de l'entrée ; l'exposé, qui
 *   change à chaque fois, vient après ;
 * - « par les  auteurs » perd son espace double, coquille du legacy.
 *
 * Le titre du scrutin, que le legacy sélectionnait puis ne passait pas au
 * prompt, n'y entre pas davantage : le prompt de la rédaction ne parle que de
 * l'exposé.
 */
final class PromptResumeAmendement
{
    /**
     * Le legacy tronque l'exposé à 300 caractères « pour réduire les coûts ».
     * Choix de la rédaction, gardé tel quel : c'est sur cette longueur que ses
     * 6 500 résumés ont été produits. Le relever est le premier levier de
     * qualité si la justification paraît maigre — à ~0,05 $ le million de
     * jetons d'entrée en lot, 1 000 caractères de plus coûtent ~0,000 017 $
     * par amendement.
     */
    public const MAX_EXPOSE = 300;

    /**
     * Le legacy plafonnait à 300 jetons avec Haiku 4.5. Le tokeniseur de Haiku
     * 5.5 compte ~30 % de jetons de plus pour le même texte, et la sortie
     * mesurée sur la production (titre 80 caractères, résumé 250, justification
     * 210, catégorie, trois tags) avoisine déjà 220 jetons : 300 couperait le
     * JSON en route, et une réponse tronquée est payée sans rien rapporter.
     * Le plafond n'est pas facturé, seul ce qui est produit l'est.
     */
    public const MAX_TOKENS = 600;

    /**
     * Effort le plus bas, et réflexion coupée : c'est de l'extraction guidée
     * par un gabarit, pas du raisonnement. Sur Haiku 5.5 la réflexion est
     * active par défaut (effort par défaut `medium`) et ses jetons se paient
     * comme la sortie ; `disabled` n'y est accepté qu'aux efforts `low`,
     * `medium` et `high`, d'où la paire. Sans outil dans la requête, la mise
     * en garde de la documentation (un appel d'outil sauté quand la réflexion
     * est coupée sous sortie JSON) ne s'applique pas.
     */
    public const EFFORT = 'low';

    /**
     * Durée de vie du cache des consignes. Une heure et non cinq minutes, sur
     * le conseil de la documentation des lots : leurs requêtes passent dans le
     * désordre et parfois sur plus de cinq minutes. L'écriture d'une heure
     * coûte 2× l'entrée (1,25× pour cinq minutes) : elle n'est rentable qu'au-
     * delà d'environ 53 % de lectures dans le lot, contre 22 % pour cinq
     * minutes. La documentation annonce 30 à 98 % : les compteurs de `lot_ia`
     * diront, dès les premiers lots, si ce choix tient.
     */
    public const DUREE_CACHE = '1h';

    /** Libellés autorisés pour `categorie`, dans l'ordre du prompt de la rédaction. */
    public const CATEGORIES = [
        'Fiscalité et budget',
        'Protection sociale',
        'Emploi et travail',
        'Éducation et recherche',
        'Santé publique',
        'Environnement et énergie',
        'Sécurité et justice',
        'Logement et urbanisme',
        'Économie et entreprises',
        'Agriculture et alimentation',
        'Institutions et démocratie',
        'Relations internationales',
        'Union européenne',
        'Défense',
        'Culture, médias et sport',
        'Droits et libertés',
        'Immigration',
        'Numérique',
    ];

    public const CONSIGNES = <<<'CONSIGNES'
        Analyse cet exposé des motifs d'un amendement parlementaire français et retourne un objet JSON avec ces six champs :

        - "titre" : titre court (max 90 caractères), commençant par un verbe à l'infinitif, décrivant l'objectif concret de l'amendement pour une personne non experte. L'objet de l'amendement doit être décrit, pas le procédé législatif.
        - "resume" : résumé (1 à 3 phrases maximum) décrivant l'acte juridique (ce que l'amendement ajoute, modifie ou supprime), sans justification politique ni effets attendus, sans référence à des numéros d'articles ou dispositions non explicités, rédigé pour une personne non experte, et commençant obligatoirement par ce type de phrase : 'Cet amendement vise à' ou 'Cet amendement appelle à'.
        - "justification" : 2 phrases maximum reformulant fidèlement l'argument avancé par les auteurs de l'amendement pour défendre leur proposition (pas l'acte juridique lui-même), sans prise de position, sans jugement sur le bien-fondé, en restant strictement au contenu de l'exposé des motifs. Commencer par "Les auteurs de l'amendement estiment que" ou "Selon les auteurs de l'amendement,". Si aucune justification, mettre null;
        - "simplicite" : un entier de 1 à 5 notant la simplicité de compréhension de la mesure proposée par l'amendement (1 = très technique/jargon, 5 = très accessible). Un amendement sur la TVA alimentaire vaudrait 4, un amendement sur les instruments dérivés de crédit vaudrait 1.
        - "categorie" : une seule valeur parmi la liste ci-dessous, choisie selon le thème principal de l'amendement. Catégories autorisées (utilise exactement ces libellés) : Fiscalité et budget | Protection sociale | Emploi et travail | Éducation et recherche | Santé publique | Environnement et énergie | Sécurité et justice | Logement et urbanisme | Économie et entreprises | Agriculture et alimentation | Institutions et démocratie | Relations internationales | Union européenne | Défense | Culture, médias et sport | Droits et libertés | Immigration | Numérique
        - "tags" : array de 1 à 3 mots-clés thématiques très généralistes et grand public au sujet de l'amendement (plus précis que la catégorie). Utiliser uniquement des mots ou expressions courtes en français, avec un maximum de 2 mots par tag, sans doublon. Chaque tag doit être une étiquette simple, pas une expression développée. Exemples valides : ["aviation"], ["retraites"], ["IVG"], ["élevage"], ["agriculture biologique"], ["méga-bassines"], ["nucléaire"], ["apprentissage"], ["pêche artisanale"]. Si un concept nécessite plus de deux mots, le reformuler en version abrégée respectant la contrainte de 2 mots maximum. Si aucun tag pertinent, retourner [].

        Exemple :
        {"titre": "Augmenter le plafond pour le taux réduit de l'impôt sur les sociétés", "resume": "Cet amendement vise à porter de 42 500€ à 60 000€ le seuil de bénéfices imposés au taux réduit de 15% pour les PME, au lieu du taux normal de 25%.", "justification": "Les auteurs de l'amendement estiment que le plafond actuel, inchangé depuis 2002, ne tient plus compte de l'inflation et pénalise les petites entreprises. Selon eux, cette revalorisation permettrait de soutenir le tissu des PME françaises.", "simplicite": 3, "categorie": "Fiscalité et budget", "tags": ["impôt sur les sociétés", "PME", "entreprises"]}
        CONSIGNES;

    /**
     * Schéma de la sortie structurée. Sans description : les consignes disent
     * déjà tout, et chaque mot ici serait payé à chaque requête. Le schéma
     * contraint ce que le format permet (types, `enum`) ; le reste — trois tags
     * au plus, simplicité de 1 à 5 — ne s'y exprime pas (`maxItems`,
     * `minimum` ne sont pas pris en charge) et se vérifie à la lecture
     * ({@see LecteurReponse}).
     *
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'titre' => ['type' => 'string'],
                'resume' => ['type' => 'string'],
                'justification' => ['anyOf' => [['type' => 'string'], ['type' => 'null']]],
                'simplicite' => ['type' => 'integer', 'enum' => [1, 2, 3, 4, 5]],
                'categorie' => ['type' => 'string', 'enum' => self::CATEGORIES],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['titre', 'resume', 'justification', 'simplicite', 'categorie', 'tags'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Exposé tel que le modèle le reçoit : entités HTML décodées, balises
     * retirées (comme le legacy), blancs resserrés — ils ne portent aucun sens
     * et se paient —, tronqué à {@see self::MAX_EXPOSE} caractères.
     */
    public static function expose(?string $brut): string
    {
        // Une fin de paragraphe vaut une espace : retirées seules, les balises
        // collent la dernière phrase d'un paragraphe à la première du suivant
        // (« …nationale.Dans le texte »), ce que le legacy envoyait tel quel.
        $texte = (string) preg_replace('#<br\s*/?>|</(p|div|li|h[1-6]|tr|td)>#i', ' ', (string) $brut);
        $texte = strip_tags(html_entity_decode($texte, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        $texte = trim((string) preg_replace('/\s+/u', ' ', $texte));

        if (mb_strlen($texte) > self::MAX_EXPOSE) {
            $texte = mb_substr($texte, 0, self::MAX_EXPOSE) . '…';
        }

        return $texte;
    }

    /**
     * Paramètres d'une requête, dans la forme du SDK PHP (clés camelCase, que
     * le SDK traduit) — les mêmes pour une requête de lot et pour un essai
     * synchrone.
     *
     * Ordre de rendu côté API : `system` puis `messages`. Le bloc `system`
     * porte le point de cache et ne contient que du texte figé : un seul octet
     * variable avant lui (une date, un identifiant) et plus rien ne se lirait
     * en cache.
     *
     * @return array<string, mixed>
     */
    public static function parametres(string $modele, string $expose): array
    {
        return [
            'model' => $modele,
            'maxTokens' => self::MAX_TOKENS,
            'system' => [[
                'type' => 'text',
                'text' => self::CONSIGNES,
                'cacheControl' => ['type' => 'ephemeral', 'ttl' => self::DUREE_CACHE],
            ]],
            'messages' => [['role' => 'user', 'content' => "Exposé des motifs :\n" . $expose]],
            'thinking' => ['type' => 'disabled'],
            'outputConfig' => [
                'effort' => self::EFFORT,
                'format' => ['type' => 'json_schema', 'schema' => self::schema()],
            ],
        ];
    }
}
