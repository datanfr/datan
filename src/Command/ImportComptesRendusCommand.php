<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les comptes rendus des séances publiques depuis le dépôt des
 * Tricoteuses (Comptes_Rendus_Seances_XVII_nettoye, un fichier par séance).
 *
 * C'est le corpus des débats : les sections (points de l'ordre du jour) et les
 * paroles (interventions) alimentent le brouillon de décryptage généré par IA,
 * qui cite les orateurs à partir de ce texte — et se vérifie contre lui.
 *
 * Une séance se réimporte en bloc : l'Assemblée republie le compte rendu
 * corrigé au Journal officiel (`version` passe d'`avant_JO` à `JO`), et les
 * éléments n'ont pas d'identifiant stable d'une publication à l'autre. On
 * efface donc sections et paroles de la séance avant de les réinsérer, plutôt
 * que d'espérer un appariement ligne à ligne qui n'existe pas.
 *
 * Le lien de hiérarchie (section mère, section d'une parole) passe par
 * `ordre_absolu_seance`, unique au sein d'une séance : les lots s'insèrent
 * ainsi d'un seul bloc, sans relire les identifiants auto-incrémentés.
 */
#[AsCommand(
    name: 'app:import:comptes-rendus',
    description: 'Importe les comptes rendus des séances publiques (sections et paroles) depuis le dépôt des Tricoteuses.',
)]
class ImportComptesRendusCommand extends ImportTricoteusesCommand
{
    private const COLONNES_CR = [
        'uid', 'seance_ref', 'session_ref', 'legislature', 'date_seance',
        'titre_journee', 'session', 'version', 'cree_le', 'modifie_le',
    ];

    private const COLONNES_MAJ_CR = [
        'seance_ref', 'session_ref', 'legislature', 'date_seance',
        'titre_journee', 'session', 'version', 'modifie_le',
    ];

    private const COLONNES_SECTION = [
        'compte_rendu_id', 'parent_ordre', 'ordre_absolu_seance', 'nivpoint',
        'valeur_ptsodj', 'code_grammaire', 'titre',
    ];

    private const COLONNES_PAROLE = [
        'compte_rendu_id', 'section_ordre', 'ordre_absolu_seance',
        'code_grammaire', 'code_style', 'role_debat', 'orateur_nom',
        'acteur_ref', 'depute_id', 'texte',
    ];

    /** @var array<string, int|string> mp_id (« PA1001 ») → id de la table depute */
    private array $deputes = [];

    protected function depotParDefaut(): string
    {
        return 'comptes-rendus';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);

        $io->title('Import des comptes rendus de séance');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seules les séances modifiées.' : 'Import complet du dépôt.');

        $this->deputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');
        $maintenant = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $seances = 0;
        $sections = 0;
        $paroles = 0;
        $sansTexte = 0;
        $orateursInconnus = 0;

        foreach ($this->fichiers($chemin, 'AN', $tout) as $fichier) {
            $cr = $this->lisJson($fichier);
            if ($cr === null || !isset($cr['uid'], $cr['seanceRef'])) {
                continue;
            }

            $meta = $cr['metadonnees'] ?? [];
            $this->upsert('compte_rendu', self::COLONNES_CR, [[
                $cr['uid'],
                $cr['seanceRef'],
                $cr['sessionRef'] ?? null,
                $this->entier($meta['legislature'] ?? null),
                $this->dateSeance($meta['dateSeance'] ?? null),
                mb_substr((string) ($cr['contenu']['quantiemes']['journee'] ?? $meta['quantiemeJournee'] ?? ''), 0, 255) ?: null,
                mb_substr((string) ($meta['session'] ?? ''), 0, 255) ?: null,
                $meta['version'] ?? null,
                $maintenant,
                $maintenant,
            ]], self::COLONNES_MAJ_CR);

            $compteRenduId = (int) $this->connection->fetchOne('SELECT id FROM compte_rendu WHERE uid = ?', [$cr['uid']]);

            // Réimport en bloc : la séance repart de zéro (cf. docblock).
            $this->connection->executeStatement('DELETE FROM cr_parole WHERE compte_rendu_id = ?', [$compteRenduId]);
            $this->connection->executeStatement('DELETE FROM cr_section WHERE compte_rendu_id = ?', [$compteRenduId]);

            $lotSections = [];
            $lotParoles = [];

            foreach ($this->noeudsRacine($cr['contenu'] ?? []) as $noeud) {
                $this->parcours($noeud, null, $compteRenduId, $lotSections, $lotParoles, $sansTexte, $orateursInconnus);
            }

            foreach (array_chunk($lotSections, self::TAILLE_LOT) as $lot) {
                $this->upsert('cr_section', self::COLONNES_SECTION, $lot, []);
                $sections += \count($lot);
            }
            foreach (array_chunk($lotParoles, self::TAILLE_LOT) as $lot) {
                $this->upsert('cr_parole', self::COLONNES_PAROLE, $lot, []);
                $paroles += \count($lot);
            }

            ++$seances;
        }

        if ($seances === 0) {
            $io->success('Aucune séance à importer.');

            return Command::SUCCESS;
        }

        $io->text(sprintf('%d séances, %d sections, %d paroles.', $seances, $sections, $paroles));
        $io->text(sprintf(
            'Écartés : %d éléments sans texte (respirations du compte rendu). Orateurs sans référence acteur : %d (présidence et fonctionnaires de séance, sans page député).',
            $sansTexte,
            $orateursInconnus,
        ));

        $io->success(sprintf('%d comptes rendus en base.', (int) $this->connection->fetchOne('SELECT COUNT(*) FROM compte_rendu')));

        return Command::SUCCESS;
    }

    /**
     * Les points de départ du parcours : l'ouverture de séance, les points de
     * l'ordre du jour, la clôture. Le dépôt « nettoye » publie les listes comme
     * des listes, à une exception près : `finSeance.point` reste un objet quand
     * il est seul — d'où la normalisation.
     *
     * @return list<array<string, mixed>>
     */
    private function noeudsRacine(array $contenu): array
    {
        $racines = [];

        foreach (['ouvertureSeance', 'point'] as $cle) {
            foreach ($this->liste($contenu[$cle] ?? null) as $noeud) {
                $racines[] = $noeud;
            }
        }

        foreach ($this->liste($contenu['finSeance']['point'] ?? null) as $noeud) {
            $racines[] = $noeud;
        }

        return $racines;
    }

    /**
     * Descend un point (section) : enregistre la section, ses paroles, puis ses
     * sous-points, en liant tout par `ordre_absolu_seance`.
     *
     * @param list<list<mixed>> $lotSections
     * @param list<list<mixed>> $lotParoles
     */
    private function parcours(
        array $noeud,
        ?int $parentOrdre,
        int $compteRenduId,
        array &$lotSections,
        array &$lotParoles,
        int &$sansTexte,
        int &$orateursInconnus,
    ): void {
        $ordre = $this->entier($noeud['ordre_absolu_seance'] ?? null);
        if ($ordre === null) {
            return;
        }

        $lotSections[] = [
            $compteRenduId,
            $parentOrdre,
            $ordre,
            $this->entier($noeud['nivpoint'] ?? null),
            $this->entier($noeud['valeur_ptsodj'] ?? null),
            $noeud['code_grammaire'] ?? null,
            $this->texteBrut($noeud['texte'] ?? null) ?: null,
        ];

        foreach ($this->liste($noeud['paragraphe'] ?? null) as $paragraphe) {
            $ligne = $this->ligneParole($paragraphe, $ordre, $compteRenduId, $orateursInconnus);
            if ($ligne === null) {
                ++$sansTexte;
                continue;
            }
            $lotParoles[] = $ligne;
        }

        foreach ($this->liste($noeud['point'] ?? null) as $sousPoint) {
            $this->parcours($sousPoint, $ordre, $compteRenduId, $lotSections, $lotParoles, $sansTexte, $orateursInconnus);
        }
    }

    /**
     * @return list<mixed>|null null quand la parole n'a pas de texte
     */
    private function ligneParole(array $paragraphe, int $sectionOrdre, int $compteRenduId, int &$orateursInconnus): ?array
    {
        $ordre = $this->entier($paragraphe['ordre_absolu_seance'] ?? null);
        $texte = $this->texteBrut($paragraphe['texte'] ?? null);

        if ($ordre === null || $texte === '') {
            return null;
        }

        $orateurNom = null;
        $acteurRef = null;
        $deputeId = null;

        // `orateurs` vaut '' quand personne ne parle (didascalie). L'identifiant
        // est le numéro syceron de l'acteur : préfixé « PA », c'est la référence
        // de l'open data, celle que porte depute.mp_id.
        $orateurs = $this->liste($paragraphe['orateurs']['orateur'] ?? null);
        if ($orateurs !== []) {
            $orateur = $orateurs[0];
            $orateurNom = mb_substr(trim((string) ($orateur['nom'] ?? '')), 0, 150) ?: null;

            $id = trim((string) ($orateur['id'] ?? ''));
            if ($id !== '') {
                $acteurRef = 'PA' . $id;
                $deputeId = isset($this->deputes[$acteurRef]) ? (int) $this->deputes[$acteurRef] : null;
                if ($deputeId === null) {
                    ++$orateursInconnus;
                }
            }
        }

        return [
            $compteRenduId,
            $sectionOrdre,
            $ordre,
            $paragraphe['code_grammaire'] ?? null,
            $paragraphe['code_style'] ?? null,
            $paragraphe['roledebat'] ?? null,
            $orateurNom,
            $acteurRef,
            $deputeId,
            $texte,
        ];
    }

    /**
     * Texte d'un élément du compte rendu : une chaîne nue, ou un objet
     * `{_: texte, stime: horodatage vidéo}` dont seul le texte nous concerne.
     */
    private function texteBrut(mixed $texte): string
    {
        if (\is_string($texte)) {
            return trim($texte);
        }

        if (\is_array($texte)) {
            return trim((string) ($texte['_'] ?? ''));
        }

        return '';
    }

    /**
     * Normalise en liste : le dépôt « nettoye » publie presque toujours des
     * listes, mais un élément seul reste parfois un objet (`finSeance.point`).
     *
     * @return list<array<string, mixed>>
     */
    private function liste(mixed $valeur): array
    {
        if (!\is_array($valeur) || $valeur === []) {
            return [];
        }

        return array_is_list($valeur) ? $valeur : [$valeur];
    }

    /** « 20260701140000000 » (YmdHis + millisecondes) en datetime SQL. */
    private function dateSeance(mixed $valeur): ?string
    {
        $valeur = (string) $valeur;
        if (\strlen($valeur) < 14) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('YmdHis', substr($valeur, 0, 14));

        return $date === false ? null : $date->format('Y-m-d H:i:s');
    }
}
