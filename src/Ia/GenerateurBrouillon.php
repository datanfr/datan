<?php

namespace App\Ia;

use Psr\Log\LoggerInterface;

/**
 * Génère un brouillon de décryptage pour un scrutin : un titre et un texte en
 * paragraphes, écrits à partir du scrutin, du dossier, de l'amendement et des
 * morceaux de discours tenus en séance ({@see CollecteurDecryptage}).
 *
 * Le brouillon PRÉ-REMPLIT le formulaire de la rédaction ; il ne se publie
 * jamais seul. Le décryptage est la seule donnée que Datan produit au lieu de
 * la recevoir : l'IA propose, la rédaction dispose.
 *
 * L'appel au modèle passe par {@see MoteurIa} (Anthropic ou Ollama selon
 * `IA_MODELE`, sortie contrainte à un schéma JSON). `IA_MODELE` vide
 * désactive la fonctionnalité : le bouton n'apparaît pas, le site vit sans
 * elle.
 *
 * Chaque citation du texte produit est vérifiée contre le compte rendu
 * ({@see ValidateurCitations}) ; le rapport accompagne le brouillon pour que
 * la rédaction repère une citation inventée avant de publier.
 */
class GenerateurBrouillon
{
    /**
     * Au-delà, les paroles les plus longues sont tronquées une à une : les
     * discussions fleuves n'apportent plus rien au brouillon et coûtent des
     * tokens (le seuil vient d'alinea, même auteur).
     */
    private const MAX_CARACTERES_DEBAT = 60000;

    private const SCHEMA = [
        'type' => 'object',
        'properties' => [
            'titre' => [
                'type' => 'string',
                'description' => "Titre court (max 90 caractères) pour le grand public, en français, sans les mots « amendement », « article » ni référence juridique : l'objet du vote, pas le procédé.",
            ],
            'paragraphes' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Le décryptage, en 4 à 8 paragraphes de texte nu (sans HTML ni Markdown).',
            ],
        ],
        'required' => ['titre', 'paragraphes'],
        'additionalProperties' => false,
    ];

    public function __construct(
        private readonly CollecteurDecryptage $collecteur,
        private readonly ValidateurCitations $validateur,
        private readonly MoteurIa $moteur,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** La fonctionnalité n'existe que si un modèle est configuré. */
    public function estActif(): bool
    {
        return $this->moteur->estActif();
    }

    public function modele(): string
    {
        return $this->moteur->modele();
    }

    /**
     * @return array{titre: string, description: string, validation: array<string, mixed>, paroles: int}|null
     *         null quand le scrutin est introuvable ou que le moteur a échoué
     */
    public function generer(int $legislature, int $numero): ?array
    {
        $donnees = $this->collecteur->collecter($legislature, $numero);
        if ($donnees === null) {
            return null;
        }

        $json = $this->moteur->appeler($this->construirePrompt($donnees), self::SCHEMA);
        if ($json === null) {
            return null;
        }

        $brouillon = json_decode($json, true);
        if (!\is_array($brouillon) || !isset($brouillon['titre'], $brouillon['paragraphes']) || !\is_array($brouillon['paragraphes'])) {
            $this->logger->error('Brouillon IA : réponse hors schéma', ['reponse' => mb_substr($json, 0, 500)]);

            return null;
        }

        $paragraphes = array_values(array_filter(array_map(
            static fn ($p) => trim(strip_tags((string) $p)),
            $brouillon['paragraphes'],
        ), static fn (string $p) => $p !== ''));

        // Le texte du décryptage est du HTML (CKEditor côté rédaction) : des
        // paragraphes simples, échappés — le modèle ne doit pas injecter de
        // balises dans l'éditeur.
        $description = implode('', array_map(
            static fn (string $p) => '<p>' . htmlspecialchars($p, \ENT_QUOTES | \ENT_SUBSTITUTE) . '</p>',
            $paragraphes,
        ));

        $texteNu = implode("\n\n", $paragraphes);

        return [
            'titre' => mb_substr(trim(strip_tags((string) $brouillon['titre'])), 0, 255),
            'description' => $description,
            'validation' => $this->validateur->valider($texteNu, $donnees),
            'paroles' => \count($donnees['paroles']),
        ];
    }

    /**
     * @param array{
     *   scrutin: array<string, mixed>,
     *   dossier: array<string, mixed>|null,
     *   amendement: array<string, mixed>|null,
     *   votes_par_groupe: list<array<string, mixed>>,
     *   paroles: list<array<string, mixed>>,
     *   est_vote_texte: bool,
     * } $donnees
     */
    private function construirePrompt(array $donnees): string
    {
        $scrutin = $donnees['scrutin'];
        $parties = [];

        $parties[] = '=== SCRUTIN ===';
        $parties[] = 'Titre : ' . ($scrutin['titre'] ?? '');
        $parties[] = 'Date : ' . ($scrutin['date_scrutin'] ?? '');
        $parties[] = sprintf('Résultat : %s (%s)', $scrutin['sort_code'] ?? '', $scrutin['sort_libelle'] ?? '');
        $parties[] = sprintf(
            'Votants : %s, Pour : %s, Contre : %s, Abstentions : %s',
            $scrutin['nombre_votants'] ?? '?',
            $scrutin['nombre_pour'] ?? '?',
            $scrutin['nombre_contre'] ?? '?',
            $scrutin['nombre_abstentions'] ?? '?',
        );
        if (!empty($scrutin['demandeur'])) {
            $parties[] = 'À la demande de : ' . $scrutin['demandeur'];
        }

        if ($donnees['amendement'] !== null) {
            $amendement = $donnees['amendement'];
            $parties[] = "\n=== AMENDEMENT MIS AUX VOIX ===";
            if (!empty($amendement['numero_ordre'])) {
                $parties[] = 'Numéro : ' . $amendement['numero_ordre'];
            }
            if (!empty($amendement['signataires'])) {
                $parties[] = 'Signataires : ' . strip_tags((string) $amendement['signataires']);
            }
            if (!empty($amendement['expose'])) {
                $parties[] = 'Exposé des motifs : ' . strip_tags((string) $amendement['expose']);
            }
        }

        if ($donnees['dossier'] !== null) {
            $parties[] = "\n=== DOSSIER LÉGISLATIF ===";
            $parties[] = 'Titre : ' . strip_tags((string) ($donnees['dossier']['titre'] ?? ''));
            if (!empty($donnees['dossier']['procedure_parlementaire'])) {
                $parties[] = 'Procédure : ' . $donnees['dossier']['procedure_parlementaire'];
            }
        }

        if ($donnees['paroles'] !== []) {
            $etiquette = $donnees['est_vote_texte'] ? 'EXPLICATIONS DE VOTE ET DISCUSSION' : 'DÉBAT EN SÉANCE';
            $parties[] = "\n=== $etiquette (extraits du compte rendu officiel) ===";
            foreach ($this->parolesBornees($donnees['paroles']) as $parole) {
                // Le compte rendu balise ses italiques (<italique>…</italique>) :
                // du bruit pour le modèle, on les retire du prompt seulement —
                // la base garde le texte tel que publié.
                $texte = trim(strip_tags((string) $parole['texte']));
                if ($texte === '') {
                    continue;
                }
                if ($parole['orateur_nom'] !== null) {
                    $entete = $parole['orateur_nom'];
                    if (!empty($parole['groupe_abrev'])) {
                        $entete .= ' [' . $parole['groupe_abrev'] . ']';
                    }
                    $parties[] = "\n$entete :\n$texte";
                } else {
                    $parties[] = "[Didascalie] $texte";
                }
            }
        }

        if ($donnees['votes_par_groupe'] !== []) {
            $parties[] = "\n=== VOTES PAR GROUPE ===";
            foreach ($donnees['votes_par_groupe'] as $groupe) {
                $parties[] = sprintf(
                    '%s (%s) : Pour %d, Contre %d, Abstention %d',
                    $groupe['libelle'],
                    $groupe['libelle_abrev'],
                    $groupe['nombre_pours'],
                    $groupe['nombre_contres'],
                    $groupe['nombre_abstentions'],
                );
            }
        }

        $structure = $donnees['est_vote_texte']
            ? <<<'TXT'
                1. Une ou deux phrases qui présentent le texte de loi : son objectif, ce qu'il vise à changer, porté par qui, dans quel cadre.
                2. Le contexte politique et les enjeux, à partir de la discussion en séance.
                3. Les arguments des députés POUR puis CONTRE, en citant chaque député par son nom, son groupe entre parenthèses et une citation courte entre guillemets français. Une intervention par paragraphe.
                4. Le résultat du vote : adopté ou rejeté, les voix pour et contre, et quels groupes ont voté dans quel sens.
                TXT
            : <<<'TXT'
                1. Une ou deux phrases qui présentent l'amendement : son objectif, déposé par qui, dans quel cadre (dossier législatif).
                2. Le contexte et les motifs, à partir de l'exposé des motifs.
                3. Les arguments des députés POUR puis CONTRE, en citant chaque député par son nom, son groupe entre parenthèses et une citation courte entre guillemets français. Une intervention par paragraphe.
                4. Le résultat du vote : adopté ou rejeté, les voix pour et contre, et quels groupes ont voté dans quel sens.
                TXT;

        return <<<PROMPT
            Tu es un journaliste parlementaire de la rédaction de Datan, un site qui rend les votes de l'Assemblée nationale compréhensibles par tous. À partir des seules données ci-dessous, rédige un décryptage de ce vote.

            STRUCTURE ATTENDUE :
            $structure

            RÈGLES :
            - Texte fluide, sans titres, sans gras, sans listes.
            - Entre 4 et 8 paragraphes courts.
            - Les citations reprennent LES MOTS EXACTS du compte rendu fourni, entre guillemets français (« … »), sans reformuler.
            - Pour chaque citation : le nom du député et son groupe entre parenthèses.
            - Ton neutre et factuel, en français.
            - N'utilise que les informations fournies. N'invente rien ; si une donnée manque, adapte le texte sans l'inventer.

            DONNÉES :

            PROMPT . implode("\n", $parties);
    }

    /**
     * Tronque les paroles les plus longues d'abord, jusqu'à tenir dans le
     * budget : couper la fin du débat ferait disparaître le résultat du vote
     * et les dernières explications, les plus utiles au texte.
     *
     * @param list<array<string, mixed>> $paroles
     *
     * @return list<array<string, mixed>>
     */
    private function parolesBornees(array $paroles): array
    {
        $total = array_sum(array_map(static fn (array $p) => mb_strlen((string) $p['texte']), $paroles));

        if ($total <= self::MAX_CARACTERES_DEBAT) {
            return $paroles;
        }

        $indices = array_keys($paroles);
        usort($indices, static fn (int $a, int $b) => mb_strlen((string) $paroles[$b]['texte']) <=> mb_strlen((string) $paroles[$a]['texte']));

        foreach ($indices as $i) {
            if ($total <= self::MAX_CARACTERES_DEBAT) {
                break;
            }
            $longueur = mb_strlen((string) $paroles[$i]['texte']);
            $garde = max(2000, (int) ($longueur / 4));
            if ($garde >= $longueur) {
                continue;
            }
            $paroles[$i]['texte'] = mb_substr((string) $paroles[$i]['texte'], 0, $garde) . ' […]';
            $total -= $longueur - $garde - 4;
        }

        return $paroles;
    }

}
