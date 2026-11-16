<?php

namespace App\Ia\Tag;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Accès en base au vocabulaire des mots-clés (`tag`, `amendement_tag`).
 *
 * Tout le vocabulaire tient en mémoire — 3 853 clés après normalisation pour
 * les 4 124 libellés distincts que la production avait accumulés —, et une
 * clé se résout alors par un `isset()`, sans requête par mot-clé.
 *
 * Invariant tenu ici : un alias pointe vers un canonique. Une fusion repointe
 * donc aussi les alias de l'absorbé, sans quoi ils mèneraient à un alias.
 */
final class VocabulaireTags
{
    /** @var array<string, array{id: int, canonique: int, libelle: string}>|null */
    private ?array $parSlug = null;

    /** @var array<int, string> slug de chaque id */
    private array $slugParId = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function charger(): void
    {
        $lignes = $this->connection->fetchAllAssociative('SELECT id, slug, libelle, synonyme_de_id FROM tag');

        $cible = [];
        $this->parSlug = [];
        $this->slugParId = [];
        foreach ($lignes as $l) {
            $cible[(int) $l['id']] = $l['synonyme_de_id'] === null ? null : (int) $l['synonyme_de_id'];
            $this->slugParId[(int) $l['id']] = (string) $l['slug'];
        }
        foreach ($lignes as $l) {
            $id = (int) $l['id'];
            // L'invariant veut un seul saut ; on suit quand même la chaîne, au
            // plus dix pas, pour qu'une écriture interrompue ne fasse pas
            // rattacher un amendement à un alias.
            $canonique = $id;
            for ($pas = 0; $cible[$canonique] !== null && isset($cible[$cible[$canonique]]) && $pas < 10; ++$pas) {
                $canonique = $cible[$canonique];
            }
            $this->parSlug[(string) $l['slug']] = ['id' => $id, 'canonique' => $canonique, 'libelle' => (string) $l['libelle']];
        }
    }

    /** Id du canonique auquel mène ce slug, ou null s'il est inconnu. */
    public function canonique(string $slug): ?int
    {
        return $this->parSlug()[$slug]['canonique'] ?? null;
    }

    public function slug(int $id): ?string
    {
        $this->parSlug();

        return $this->slugParId[$id] ?? null;
    }

    /**
     * Les canoniques : slug => libellé.
     *
     * @return array<string, string>
     */
    public function canoniques(): array
    {
        $resultat = [];
        foreach ($this->parSlug() as $slug => $tag) {
            if ($tag['id'] === $tag['canonique']) {
                $resultat[$slug] = $tag['libelle'];
            }
        }

        return $resultat;
    }

    /**
     * Nombre d'amendements par canonique, au total et par catégorie de
     * l'amendement — de quoi restreindre le vocabulaire envoyé au modèle aux
     * mots-clés qui comptent dans la catégorie du candidat.
     *
     * @return array{0: array<string, int>, 1: array<string, array<string, int>>}
     */
    public function usages(): array
    {
        $total = [];
        $parCategorie = [];
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT t.slug, COALESCE(a.categorie_ia, \'\') AS categorie, COUNT(*) AS n
             FROM amendement_tag at
             JOIN tag t ON t.id = at.tag_id
             JOIN amendement a ON a.id = at.amendement_id
             GROUP BY t.slug, a.categorie_ia',
        );
        foreach ($lignes as $l) {
            $total[$l['slug']] = ($total[$l['slug']] ?? 0) + (int) $l['n'];
            $parCategorie[$l['categorie']][$l['slug']] = (int) $l['n'];
        }
        foreach ($parCategorie as &$compte) {
            arsort($compte);
        }

        return [$total, $parCategorie];
    }

    /**
     * Crée un canonique (`$canonique` null) ou un alias, et rend son id. Un
     * slug déjà présent rend la ligne existante sans la modifier : deux
     * traitements concurrents ne se marchent pas dessus.
     */
    public function creer(string $libelle, string $slug, ?int $canonique = null): int
    {
        $this->connection->executeStatement(
            'INSERT INTO tag (libelle, slug, synonyme_de_id, cree_le) VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
            [mb_substr($libelle, 0, NormaliseurTag::LONGUEUR_MAX), $slug, $canonique],
        );
        $id = (int) $this->connection->lastInsertId();

        $this->parSlug();
        $this->parSlug[$slug] ??= ['id' => $id, 'canonique' => $canonique ?? $id, 'libelle' => $libelle];
        $this->slugParId[$id] = $slug;

        return $this->parSlug[$slug]['canonique'];
    }

    /**
     * Rattache des canoniques à un amendement ; rend le nombre de liens
     * nouveaux. Les liens déjà là sont ignorés : rejouer ne double rien.
     *
     * @param list<int> $tagIds
     */
    public function rattacher(int $amendementId, array $tagIds): int
    {
        $tagIds = array_values(array_unique($tagIds));
        if ($tagIds === []) {
            return 0;
        }

        $valeurs = [];
        foreach ($tagIds as $tagId) {
            array_push($valeurs, $amendementId, $tagId);
        }

        return (int) $this->connection->executeStatement(
            'INSERT IGNORE INTO amendement_tag (amendement_id, tag_id) VALUES '
            . implode(', ', array_fill(0, \count($tagIds), '(?, ?)')),
            $valeurs,
        );
    }

    /**
     * Fait d'un canonique l'alias d'un autre : ses amendements passent à la
     * cible, ses propres alias aussi.
     */
    public function fusionner(int $absorbe, int $cible): void
    {
        if ($absorbe === $cible) {
            return;
        }

        $this->connection->executeStatement(
            'INSERT IGNORE INTO amendement_tag (amendement_id, tag_id)
             SELECT amendement_id, ? FROM amendement_tag WHERE tag_id = ?',
            [$cible, $absorbe],
        );
        $this->connection->executeStatement('DELETE FROM amendement_tag WHERE tag_id = ?', [$absorbe]);
        $this->connection->executeStatement(
            'UPDATE tag SET synonyme_de_id = ? WHERE id = ? OR synonyme_de_id = ?',
            [$cible, $absorbe, $absorbe],
        );

        foreach ($this->parSlug() as $slug => $tag) {
            if ($tag['id'] === $absorbe || $tag['canonique'] === $absorbe) {
                $this->parSlug[$slug]['canonique'] = $cible;
            }
        }
    }

    /**
     * Nombre de canoniques et d'alias.
     *
     * @return array{canoniques: int, alias: int}
     */
    public function compter(): array
    {
        $alias = 0;
        foreach ($this->parSlug() as $tag) {
            $alias += (int) ($tag['id'] !== $tag['canonique']);
        }

        return ['canoniques' => \count($this->parSlug()) - $alias, 'alias' => $alias];
    }

    /**
     * Ids des amendements, par identifiant Assemblée — utile aux imports.
     *
     * @param list<string> $identifiants
     *
     * @return array<string, int>
     */
    public function amendementsParIdentifiant(array $identifiants): array
    {
        if ($identifiants === []) {
            return [];
        }

        return array_map('intval', $this->connection->fetchAllKeyValue(
            'SELECT amendement_id, id FROM amendement WHERE amendement_id IN (?)',
            [$identifiants],
            [ArrayParameterType::STRING],
        ));
    }

    /** @return array<string, array{id: int, canonique: int, libelle: string}> */
    private function parSlug(): array
    {
        if ($this->parSlug === null) {
            $this->charger();
        }

        return $this->parSlug;
    }
}
