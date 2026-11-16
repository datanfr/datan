<?php

namespace App\Ia\Amendement;

use App\Ia\Tag\NormaliseurTag;

/**
 * Lit la réponse du modèle à {@see PromptResumeAmendement} et la ramène aux
 * colonnes de `amendement`, ou dit pourquoi elle est inutilisable.
 *
 * La sortie structurée garantit la forme du JSON, pas tout : un refus
 * (`stop_reason: refusal`) ou une réponse coupée (`max_tokens`) peuvent ne pas
 * respecter le schéma, et ce que le format ne sait pas exprimer — trois tags au
 * plus, des chaînes non vides — se contrôle ici.
 */
final class LecteurReponse
{
    /** Le legacy n'insérait que les trois premiers tags. */
    public const MAX_TAGS = 3;

    /**
     * @return array{titre: string, resume: string, justification: ?string, simplicite: int, categorie: ?string, tags: list<string>}
     *
     * @throws ReponseInvalide
     */
    public static function lire(?string $texte, ?string $motifArret): array
    {
        if ($motifArret === 'refusal') {
            throw new ReponseInvalide('refus du modèle (stop_reason refusal)');
        }
        if ($motifArret === 'max_tokens') {
            throw new ReponseInvalide(sprintf('réponse coupée au plafond de %d jetons', PromptResumeAmendement::MAX_TOKENS));
        }
        if ($texte === null || trim($texte) === '') {
            throw new ReponseInvalide('aucun bloc texte dans la réponse');
        }

        $json = json_decode($texte, true);
        if (!\is_array($json)) {
            throw new ReponseInvalide('JSON illisible : ' . mb_substr($texte, 0, 120));
        }

        $titre = self::chaine($json['titre'] ?? null, 255);
        $resume = self::chaine($json['resume'] ?? null);
        if ($titre === null || $resume === null) {
            throw new ReponseInvalide('titre ou résumé vide');
        }

        $categorie = \is_string($json['categorie'] ?? null) && \in_array($json['categorie'], PromptResumeAmendement::CATEGORIES, true)
            ? $json['categorie']
            : null;

        // Le legacy ramenait toute note hors bornes dans [1, 5], 3 à défaut :
        // même règle.
        $simplicite = is_numeric($json['simplicite'] ?? null) ? max(1, min(5, (int) $json['simplicite'])) : 3;

        $tags = [];
        foreach (\is_array($json['tags'] ?? null) ? $json['tags'] : [] as $tag) {
            $libelle = NormaliseurTag::libelle($tag);
            if ($libelle !== null && !\in_array($libelle, $tags, true)) {
                $tags[] = $libelle;
            }
            if (\count($tags) >= self::MAX_TAGS) {
                break;
            }
        }

        return [
            'titre' => $titre,
            'resume' => $resume,
            'justification' => self::chaine($json['justification'] ?? null),
            'simplicite' => $simplicite,
            'categorie' => $categorie,
            'tags' => $tags,
        ];
    }

    /**
     * Texte nettoyé, ou null s'il est vide — y compris la chaîne « null » que
     * certains modèles écrivent au lieu du null JSON. Les balises sont
     * retirées : la page du vote affiche ces champs, et rien de ce que le
     * modèle écrit n'a à y entrer comme HTML.
     */
    private static function chaine(mixed $valeur, ?int $longueurMax = null): ?string
    {
        if (!\is_string($valeur)) {
            return null;
        }

        $texte = trim(strip_tags($valeur));
        if ($texte === '' || strcasecmp($texte, 'null') === 0) {
            return null;
        }

        return $longueurMax === null ? $texte : mb_substr($texte, 0, $longueurMax);
    }
}
