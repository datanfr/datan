<?php

namespace App\Groupe;

use Doctrine\DBAL\Connection;

/**
 * Segment d'adresse d'un groupe parlementaire : le « udr » de
 * `/groupes/legislature-17/udr`.
 *
 * **Un sigle ne désigne pas toujours un seul groupe dans une législature.** En
 * novembre 2026, l'Assemblée a rendu à l'Union des droites pour la République
 * (PO872880, abrégée « UDDPLR » jusque-là) le sigle « UDR » de son
 * prédécesseur dissous (PO847173). Deux groupes de la 17e répondent alors au
 * même sigle, et une adresse bâtie sur le seul sigle ne peut plus en désigner
 * qu'un. C'est le piège des slugs d'organes de CLAUDE.md (« Un slug dérivé
 * d'un libellé n'est pas unique »), et la même parade : le groupe qui compte
 * garde l'adresse simple, l'homonyme reçoit une adresse suffixée.
 *
 * **Qui garde l'adresse simple** : parmi les homonymes d'une législature, le
 * groupe en activité, puis le plus récemment créé. L'Assemblée n'attribue
 * jamais un même sigle à deux groupes en activité au même moment : une liste
 * de groupes en activité peut donc toujours bâtir ses liens sur le sigle seul,
 * et seuls les liens qui peuvent désigner un groupe disparu passent par ce
 * service.
 *
 * **Le suffixe est l'uid de l'Assemblée** : `udr-po847173`. C'est le seul
 * identifiant du groupe qui ne change jamais — l'Assemblée a justement montré
 * qu'elle change les sigles —, il est unique par construction, ne demande
 * aucune table tenue à la main, et vaut pour tout sigle réemployé demain. Une
 * date (« udr-2024 ») se lirait mieux, mais deux homonymes peuvent naître la
 * même année et l'Assemblée corrige parfois ses dates de début. La forme
 * suffixée se résout pour TOUT groupe, y compris celui qui détient l'adresse
 * simple (qui y redirige) : une adresse suffixée une fois publiée ne meurt
 * jamais, même si son groupe hérite un jour de l'adresse simple.
 *
 * Toutes les autres adresses de groupe — 62 groupes sur 63 aujourd'hui — sont
 * inchangées au caractère près : la règle ne touche que les sigles partagés.
 */
final class AdresseGroupe
{
    /**
     * Expression SQL du segment d'adresse, `g` étant l'alias attendu sur
     * `groupe`. Est suffixé le groupe dont un homonyme de la même législature
     * le précède — en activité quand lui ne l'est pas, ou à activité égale
     * plus récent (puis d'identifiant plus grand, pour que deux homonymes ne
     * puissent jamais se disputer l'adresse simple).
     *
     * Écrite en SQL pour servir telle quelle aux requêtes qui produisent des
     * adresses en nombre (plan du site, recherche), et lue par ce service pour
     * les gabarits : une seule définition de la règle.
     */
    public const SQL = "CASE WHEN EXISTS (
                SELECT 1 FROM groupe homonyme
                WHERE homonyme.legislature = g.legislature
                  AND LOWER(homonyme.libelle_abrev) = LOWER(g.libelle_abrev)
                  AND homonyme.id <> g.id
                  AND (homonyme.date_fin IS NULL, COALESCE(homonyme.date_debut, '1000-01-01'), homonyme.id)
                    > (g.date_fin IS NULL, COALESCE(g.date_debut, '1000-01-01'), g.id)
            )
            THEN LOWER(CONCAT(g.libelle_abrev, '-', g.uid))
            ELSE LOWER(g.libelle_abrev) END";

    /** Ordre SQL qui place en tête le détenteur de l'adresse simple, alias `g`. */
    public const ORDRE_DETENTEUR = 'g.date_fin IS NULL DESC, g.date_debut DESC, g.id DESC';

    /** Suffixe d'uid d'une adresse : « -po847173 ». */
    private const MOTIF_SUFFIXE = '/-(po\d+)$/';

    /**
     * Segments des seuls groupes homonymes, par id et par uid, chargés à la
     * première demande : quelques lignes, une requête par page au plus.
     *
     * @var array{id: array<int, string>, uid: array<string, string>}|null
     */
    private ?array $homonymes = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Segment d'adresse d'un groupe connu par son sigle et, quand la ligne le
     * porte, par son id (entier) ou son uid (« PO… »). Sans identifiant, le
     * sigle seul : c'est le cas des listes de groupes en activité.
     */
    public function segment(string $sigle, int|string|null $groupe = null): string
    {
        $simple = mb_strtolower($sigle);

        if ($groupe === null || $groupe === '') {
            return $simple;
        }

        $homonymes = $this->homonymes();

        return \is_int($groupe) || ctype_digit($groupe)
            ? $homonymes['id'][(int) $groupe] ?? $simple
            : $homonymes['uid'][mb_strtoupper($groupe)] ?? $simple;
    }

    /**
     * Le groupe désigné par une adresse suffixée, et son adresse canonique :
     * celle qu'il faut servir, ou vers laquelle rediriger si elle diffère de
     * l'adresse demandée. Nul si l'adresse n'a pas la forme suffixée ou ne
     * désigne aucun groupe de la législature.
     *
     * La résolution se fait sur l'uid seul, pas sur le sigle qui le précède :
     * si l'Assemblée rebaptise un jour le groupe, son ancienne adresse
     * suffixée le retrouve encore et redirige vers la nouvelle.
     *
     * @return array{id: int, adresse: string}|null
     */
    public function parSuffixe(int $legislature, string $adresse): ?array
    {
        if (preg_match(self::MOTIF_SUFFIXE, mb_strtolower($adresse), $m) !== 1) {
            return null;
        }

        $ligne = $this->connection->fetchAssociative(
            'SELECT g.id, ' . self::SQL . ' AS adresse
             FROM groupe g
             WHERE g.uid = :uid AND g.legislature = :legislature',
            ['uid' => mb_strtoupper($m[1]), 'legislature' => $legislature],
        );

        return $ligne === false ? null : ['id' => (int) $ligne['id'], 'adresse' => (string) $ligne['adresse']];
    }

    /**
     * @return array{id: array<int, string>, uid: array<string, string>}
     */
    private function homonymes(): array
    {
        if ($this->homonymes !== null) {
            return $this->homonymes;
        }

        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.id, g.uid, ' . self::SQL . ' AS adresse
             FROM groupe g
             WHERE EXISTS (SELECT 1 FROM groupe autre
                           WHERE autre.legislature = g.legislature
                             AND LOWER(autre.libelle_abrev) = LOWER(g.libelle_abrev)
                             AND autre.id <> g.id)',
        );

        $this->homonymes = ['id' => [], 'uid' => []];
        foreach ($lignes as $ligne) {
            $this->homonymes['id'][(int) $ligne['id']] = (string) $ligne['adresse'];
            $this->homonymes['uid'][(string) $ligne['uid']] = (string) $ligne['adresse'];
        }

        return $this->homonymes;
    }
}
