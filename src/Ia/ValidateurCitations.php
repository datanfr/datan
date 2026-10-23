<?php

namespace App\Ia;

/**
 * Vérifie que les citations d'un brouillon généré par IA sont ancrées dans les
 * textes sources — paroles du compte rendu et exposé des motifs.
 *
 * Repris du CitationValidator de PoliticAnalysis (même auteur) : trois niveaux
 * de correspondance, du plus strict au plus souple :
 *  1. sous-chaîne exacte (après normalisation typographique) ;
 *  2. bigramme — une séquence de deux mots significatifs consécutifs de la
 *     citation se retrouve dans la source ;
 *  3. recouvrement lexical — 60 % des mots significatifs de la citation sont
 *     dans la source, avec tolérance sur les désinences (radical de 5 lettres).
 *
 * Le verdict est indicatif : il aide la rédaction à repérer une citation
 * inventée avant publication, il ne remplace pas sa relecture.
 */
class ValidateurCitations
{
    /**
     * @param array{paroles?: list<array<string, mixed>>, amendement?: array<string, mixed>|null} $donnees
     *
     * @return array{citations: list<array{texte: string, valide: bool, source: ?string}>, total: int, valides: int, invalides: int}
     */
    public function valider(string $texteIa, array $donnees): array
    {
        $citations = $this->extraireCitations($texteIa);
        $sources = $this->collecterSources($donnees);

        $resultats = [];
        foreach ($citations as $citation) {
            $source = $this->chercherDansSources($citation, $sources);
            $resultats[] = [
                'texte' => $citation,
                'valide' => $source !== null,
                'source' => $source,
            ];
        }

        $valides = \count(array_filter($resultats, static fn (array $r) => $r['valide']));

        return [
            'citations' => $resultats,
            'total' => \count($resultats),
            'valides' => $valides,
            'invalides' => \count($resultats) - $valides,
        ];
    }

    /** @return list<string> */
    private function extraireCitations(string $texte): array
    {
        $citations = [];

        // Guillemets français « … », typographiques “…” et droits "…".
        if (preg_match_all('/\x{00AB}\s*(.+?)\s*\x{00BB}/u', $texte, $m)) {
            $citations = [...$citations, ...$m[1]];
        }
        if (preg_match_all('/\x{201C}(.+?)\x{201D}/u', $texte, $m)) {
            $citations = [...$citations, ...$m[1]];
        }
        if (preg_match_all('/"([^"]{10,}?)"/u', $texte, $m)) {
            $citations = [...$citations, ...$m[1]];
        }

        return array_values(array_unique($citations));
    }

    /**
     * @param array{paroles?: list<array<string, mixed>>, amendement?: array<string, mixed>|null} $donnees
     *
     * @return list<array{texte: string, source: string}>
     */
    private function collecterSources(array $donnees): array
    {
        $sources = [];

        foreach ($donnees['paroles'] ?? [] as $parole) {
            $texte = trim(strip_tags((string) ($parole['texte'] ?? '')));
            if ($texte !== '') {
                $sources[] = [
                    'texte' => $texte,
                    'source' => 'Parole de ' . ($parole['orateur_nom'] ?? 'intervenant inconnu'),
                ];
            }
        }

        if (!empty($donnees['amendement']['expose'])) {
            $sources[] = [
                'texte' => strip_tags((string) $donnees['amendement']['expose']),
                'source' => 'Exposé des motifs',
            ];
        }

        return $sources;
    }

    /**
     * @param list<array{texte: string, source: string}> $sources
     */
    private function chercherDansSources(string $citation, array $sources): ?string
    {
        $citationNorm = $this->normaliser($citation);

        // Trop courte pour être discriminante : un faux positif est garanti.
        if (mb_strlen($citationNorm) < 8) {
            return null;
        }

        foreach ($sources as $source) {
            if (str_contains($this->normaliser($source['texte']), $citationNorm)) {
                return $source['source'];
            }
        }

        foreach ($sources as $source) {
            if ($this->bigrammePresent($citationNorm, $this->normaliser($source['texte']))) {
                return $source['source'] . ' (approx.)';
            }
        }

        foreach ($sources as $source) {
            if ($this->recouvrementLexical($citationNorm, $this->normaliser($source['texte']))) {
                return $source['source'] . ' (approx.)';
            }
        }

        return null;
    }

    private function bigrammePresent(string $citationNorm, string $sourceNorm): bool
    {
        $mots = array_values(array_filter(
            preg_split('/\s+/', $citationNorm) ?: [],
            static fn (string $mot) => mb_strlen($mot) > 2,
        ));

        for ($i = 0; $i < \count($mots) - 1; ++$i) {
            if (str_contains($sourceNorm, $mots[$i] . ' ' . $mots[$i + 1])) {
                return true;
            }
        }

        return false;
    }

    private function recouvrementLexical(string $citationNorm, string $sourceNorm): bool
    {
        $motsCitation = array_values(array_filter(
            preg_split('/\s+/', $citationNorm) ?: [],
            static fn (string $mot) => mb_strlen($mot) > 3,
        ));

        if (\count($motsCitation) < 2) {
            return false;
        }

        $motsSource = [];
        foreach (preg_split('/\s+/', $sourceNorm) ?: [] as $mot) {
            if ($mot === '') {
                continue;
            }
            $motsSource[$mot] = true;
            if (mb_strlen($mot) >= 5) {
                $motsSource[mb_substr($mot, 0, 5)] = true;
            }
        }

        $trouves = 0;
        foreach ($motsCitation as $mot) {
            $radical = mb_strlen($mot) >= 5 ? mb_substr($mot, 0, 5) : $mot;
            if (isset($motsSource[$mot]) || isset($motsSource[$radical])) {
                ++$trouves;
            }
        }

        return $trouves / \count($motsCitation) >= 0.6;
    }

    private function normaliser(string $texte): string
    {
        $texte = mb_strtolower(strip_tags($texte));
        $texte = str_replace(["\u{2019}", "\u{2018}", "\u{02BC}"], "'", $texte);
        $texte = str_replace(["\u{2013}", "\u{2014}", "\u{2012}"], '-', $texte);
        $texte = str_replace("\u{00A0}", ' ', $texte);
        $texte = (string) preg_replace("/[^\p{L}\p{N}'\s-]/u", ' ', $texte);

        return trim((string) preg_replace('/\s+/', ' ', $texte));
    }
}
