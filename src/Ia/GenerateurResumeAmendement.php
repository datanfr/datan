<?php

namespace App\Ia;

use Psr\Log\LoggerInterface;

/**
 * Génère le résumé grand public d'un amendement mis aux voix : un titre
 * reformulé, une phrase de résumé et une note de simplicité de 1 à 5.
 *
 * C'est la boucle que PoliticAnalysis (même auteur) faisait de l'extérieur en
 * poussant sur /api/amendements_ia ; elle est désormais interne. Le résultat
 * remplit amendement.titre_ia / resume_ia / simplicite_ia, que la rédaction
 * relit dans /admin/amendements — le résumé ne s'affiche au public qu'une
 * fois relu (resume_relu), rien ne se publie seul.
 */
class GenerateurResumeAmendement
{
    /**
     * Au-delà, l'exposé des motifs est tronqué : la suite n'apporte rien à une
     * phrase de résumé et coûte des tokens.
     */
    private const MAX_EXPOSE = 1500;

    private const SCHEMA = [
        'type' => 'object',
        'properties' => [
            'titre' => [
                'type' => 'string',
                'description' => "Titre court (max 90 caractères) reformulé pour le grand public, en français, sans les mots « amendement », « article » ni référence juridique : l'objet du vote, pas le procédé.",
            ],
            'resume' => [
                'type' => 'string',
                'description' => 'Une seule phrase (max 150 caractères) résumant ce vote pour le grand public, en français.',
            ],
            'simplicite' => [
                'type' => 'integer',
                'description' => 'Simplicité de compréhension, de 1 (très technique, jargon) à 5 (très accessible).',
            ],
        ],
        'required' => ['titre', 'resume', 'simplicite'],
        'additionalProperties' => false,
    ];

    public function __construct(
        private readonly MoteurIa $moteur,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array{titre: ?string, objet: ?string, expose: ?string} $contexte
     *        titre et objet du scrutin, exposé des motifs de l'amendement
     *
     * @return array{titre: string, resume: string, simplicite: int}|null
     */
    public function generer(array $contexte): ?array
    {
        $expose = trim(strip_tags((string) ($contexte['expose'] ?? '')));
        if (mb_strlen($expose) > self::MAX_EXPOSE) {
            $expose = mb_substr($expose, 0, self::MAX_EXPOSE) . '…';
        }

        $parties = ['Titre du scrutin : ' . trim((string) ($contexte['titre'] ?? ''))];
        if (!empty($contexte['objet'])) {
            $parties[] = 'Objet : ' . trim(strip_tags((string) $contexte['objet']));
        }
        if ($expose !== '') {
            $parties[] = "Exposé des motifs de l'amendement : " . $expose;
        }

        $prompt = <<<PROMPT
            Tu travailles pour Datan, un site qui rend les votes de l'Assemblée nationale compréhensibles par tous. Voici un scrutin sur un amendement :

            PROMPT . implode("\n", $parties) . <<<'PROMPT'


            Produis :
            - "titre" : un titre court (max 90 caractères) pour le grand public, sans les mots « amendement » ni « article », sans référence juridique — décris l'objet du vote, pas le procédé ;
            - "resume" : une seule phrase (max 150 caractères) qui dit ce que le vote change ou proposait de changer ;
            - "simplicite" : un entier de 1 (très technique) à 5 (très accessible).

            Exemple du ton attendu : {"titre": "Origine des viandes affichée dans les restaurants", "resume": "Vote sur l'obligation de mentionner l'origine des viandes dans les restaurants.", "simplicite": 4}
            PROMPT;

        $json = $this->moteur->appeler($prompt, self::SCHEMA, 500);
        if ($json === null) {
            return null;
        }

        $reponse = json_decode($json, true);
        if (!\is_array($reponse) || !isset($reponse['titre'], $reponse['resume'], $reponse['simplicite'])) {
            $this->logger->error('Résumé IA : réponse hors schéma', ['reponse' => mb_substr($json, 0, 300)]);

            return null;
        }

        return [
            'titre' => mb_substr(trim(strip_tags((string) $reponse['titre'])), 0, 255),
            'resume' => mb_substr(trim(strip_tags((string) $reponse['resume'])), 0, 220),
            'simplicite' => max(1, min(5, (int) $reponse['simplicite'])),
        ];
    }
}
