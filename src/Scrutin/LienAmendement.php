<?php

namespace App\Scrutin;

/**
 * Adresse d'un amendement sur le site de l'Assemblée nationale.
 *
 * L'adresse lisible — `/dyn/17/amendements/2247/AN/685` — ne se reconstitue pas
 * depuis l'open data. Le numéro de texte y prend parfois une lettre que rien ne
 * publie (« 1906C », « 0324A », « 2984S »), et le segment suivant dépend de
 * l'organe d'examen : `AN` en séance, `CION_FIN/CF1` ou `CION-CEDU/AC1` en
 * commission. La forme « numéro de texte + numéro d'ordre », longtemps
 * fabriquée ici comme dans l'application d'origine, rendait donc des 404 sur
 * les deux budgets, et une même adresse pour l'amendement de séance et ses
 * homonymes de commission (22 120 adresses partagées par 60 800 amendements).
 *
 * Le site accepte en revanche l'identifiant seul et redirige vers la bonne
 * page : c'est documenté dans la foire aux questions de l'open data
 * (« Redirection vers la page de l'amendement sur le site »), et c'est ce que
 * fait l'application d'origine depuis novembre 2026 (`scripts/daily.php`,
 * `votesAmendements()`). L'identifiant est la seule clé qui ne se devine pas.
 */
final class LienAmendement
{
    private const ADRESSE = 'https://www.assemblee-nationale.fr/dyn/%d/amendements/%s';

    /** Ce que le site sait rediriger : un identifiant d'amendement de l'Assemblée. */
    private const IDENTIFIANT = '/^AMANR\d+L\d+[A-Z0-9]+$/';

    /**
     * Rend `null` plutôt qu'une adresse douteuse : la table porte un amendement
     * fictif dont l'identifiant est la chaîne « NULL », et un lien absent
     * masque le bouton là où un lien faux mène à une 404.
     */
    public static function depuisIdentifiant(?int $legislature, ?string $identifiant): ?string
    {
        if ($legislature === null || $identifiant === null || preg_match(self::IDENTIFIANT, $identifiant) !== 1) {
            return null;
        }

        return sprintf(self::ADRESSE, $legislature, $identifiant);
    }
}
