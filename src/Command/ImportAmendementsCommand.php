<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les amendements de la législature en cours depuis le dépôt des
 * Tricoteuses.
 *
 * Le site n'affiche que les amendements soumis à un scrutin — 5 464 sur les
 * 123 224 que compte la 17e législature. Le dépôt est tout de même repris en
 * entier : c'est le corpus dans lequel se retrouve, à terme, le rattachement
 * d'un scrutin à son amendement, que l'Assemblée ne publie pas et que
 * l'application d'origine va chercher en grattant `assemblee-nationale.fr/dyn/`
 * (`scripts/daily.php`, `matchAmendments()`). Les clés nécessaires à ce
 * rapprochement — `texteLegislatifRef`, `examenRef`, `numeroOrdreDepot` — sont
 * dans ces fichiers ; aucune colonne ne les porte encore.
 *
 * Quatre colonnes ne servent qu'à l'appariement d'un scrutin à son amendement
 * ({@see LienScrutinsCommand}) : `seance_ref`, `numero_ordre`, `date_sort` et
 * `signataires`. Elles vivaient dans les seuls fichiers, ce qui obligeait
 * l'appariement à relire les 123 224 amendements du dépôt à chaque exécution ;
 * portées en base et indexées, elles le ramènent à une jointure.
 *
 * L'exposé sommaire est publié en HTML à entités numériques
 * (« d&#x00E9;lai ») et stocké tel quel, comme le fait la base de production :
 * la page l'affiche avec `|raw` (`vote/individual.html.twig`), où entités et
 * caractères accentués rendent à l'identique. Les décoder n'apporterait rien à
 * l'affichage et transformerait en balises actives le `&lt;` par lequel un
 * auteur a échappé du texte.
 */
#[AsCommand(
    name: 'app:import:amendements',
    description: 'Importe les amendements depuis le dépôt des Tricoteuses.',
)]
class ImportAmendementsCommand extends ImportTricoteusesCommand
{
    /**
     * `resume_ia`, `titre_ia` et `resume_relu` sont absents des colonnes mises
     * à jour : ce sont des résumés rédigés puis relus par la rédaction, au même
     * titre que les décryptages. L'open data ne les connaît pas et ne doit
     * jamais les effacer. Ils ne sont écrits qu'à la création, avec leur valeur
     * neutre.
     *
     * `href` en est absent pour une autre raison : les 11 197 liens déjà en base
     * ont été relevés sur le site de l'Assemblée, qui suffixe certains numéros
     * de texte (« 0324A ») d'une manière que le dépôt ne permet pas de
     * reconstituer. Le lien déduit ici sert aux amendements nouveaux ; il ne
     * remplace pas un lien déjà constaté.
     */
    private const COLONNES = [
        'amendement_id', 'legislature', 'href', 'expose', 'resume_relu',
        'seance_ref', 'numero_ordre', 'date_sort', 'signataires',
    ];

    protected function depotParDefaut(): string
    {
        return 'amendements';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);

        $io->title('Import des amendements');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les fichiers modifiés.' : 'Import complet du dépôt.');

        $avant = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM amendement');
        $lus = 0;
        $sansExpose = 0;
        $lot = [];

        // Les amendements sont rangés par texte législatif — un dossier par
        // proposition ou projet de loi, jusqu'à quelques milliers de fichiers.
        foreach ($this->fichiers($chemin, '', $tout) as $fichier) {
            $amendement = $this->lisJson($fichier);
            if ($amendement === null || !isset($amendement['uid'])) {
                continue;
            }
            ++$lus;

            $expose = $this->texte($amendement['corps']['contenuAuteur']['exposeSommaire'] ?? null);
            if ($expose === null) {
                ++$sansExpose;
            }

            // La liste est reprise dans son ordre de publication, et cet ordre
            // porte une information : le déposant vient en tête, les
            // cosignataires ensuite. C'est sur lui que {@see LienScrutinsCommand}
            // départage deux amendements de même numéro.
            $signataires = $this->texte($amendement['signataires']['libelle'] ?? null);

            $lot[] = [
                $amendement['uid'],
                $this->entier($amendement['legislature'] ?? null),
                $this->lien($amendement),
                $expose,
                0,
                $this->texte($amendement['seanceDiscussionRef'] ?? null),
                $this->numero($amendement['identification']['numeroOrdreDepot'] ?? null),
                $this->date($amendement['cycleDeVie']['dateSort'] ?? null),
                $signataires,
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->enregistre($lot);
                $lot = [];
            }
        }

        $this->enregistre($lot);

        $apres = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM amendement');

        if ($sansExpose > 0) {
            $io->text(sprintf('%d amendements sans exposé sommaire publié.', $sansExpose));
        }
        $cles = $this->connection->fetchAssociative(
            'SELECT COUNT(expose) AS exposes, COUNT(seance_ref) AS seances,
                    COUNT(numero_ordre) AS numeros, COUNT(date_sort) AS sorts,
                    SUM(resume_relu) AS relus
             FROM amendement',
        ) ?: [];

        $io->success(sprintf(
            '%d amendements lus, %d créés — %d en base, dont %d avec un exposé et %d avec un résumé relu.',
            $lus,
            $apres - $avant,
            $apres,
            $cles['exposes'] ?? 0,
            $cles['relus'] ?? 0,
        ));

        $io->text(sprintf(
            'Clés d\'appariement : %d séances de discussion, %d numéros d\'ordre, %d dates de mise aux voix.',
            $cles['seances'] ?? 0,
            $cles['numeros'] ?? 0,
            $cles['sorts'] ?? 0,
        ));

        return Command::SUCCESS;
    }

    /**
     * Lien vers l'amendement sur le site de l'Assemblée, construit comme le fait
     * l'application d'origine (`scripts/daily.php:3298`) : numéro du texte
     * législatif, puis numéro d'ordre de dépôt.
     *
     * @param array<string, mixed> $amendement
     */
    private function lien(array $amendement): ?string
    {
        $numeroOrdre = $amendement['identification']['numeroOrdreDepot'] ?? null;

        if ($numeroOrdre === null
            || !preg_match('/\d+$/', (string) ($amendement['texteLegislatifRef'] ?? ''), $texte)) {
            return null;
        }

        return sprintf(
            'https://www.assemblee-nationale.fr/dyn/%d/amendements/%s/AN/%s',
            $this->entier($amendement['legislature'] ?? null),
            $texte[0],
            $numeroOrdre,
        );
    }

    /**
     * Numéro d'ordre de dépôt, sans son rembourrage de zéros.
     *
     * La normalisation se fait ici, à l'écriture, et non au moment d'apparier :
     * le dépôt écrit « 000024 » là où l'objet d'un scrutin annonce « n° 24 », et
     * une jointure ne peut pas rapprocher deux valeurs qu'il faudrait d'abord
     * transformer. Le lien vers le site, lui, garde la forme rembourrée : c'est
     * celle que l'Assemblée attend dans ses URL.
     */
    private function numero(mixed $valeur): ?string
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        $numero = ltrim((string) $valeur, '0');

        return $numero === '' ? '0' : $numero;
    }

    /**
     * Aucune colonne n'est réécrite sans condition : `href` et les colonnes
     * rédactionnelles gardent ce qu'une autre source leur a donné, et un exposé
     * absent d'une moisson n'efface pas celui déjà connu.
     *
     * Les quatre clés d'appariement suivent le même régime, pour une raison qui
     * leur est propre : `seanceDiscussionRef` et `cycleDeVie.dateSort` ne sont
     * renseignés qu'une fois l'amendement inscrit puis mis aux voix. Une moisson
     * qui les rendrait à nouveau vides — texte réexaminé, fiche republiée en
     * amont de sa discussion — délierait autant de scrutins.
     *
     * @param list<list<mixed>> $lot
     */
    private function enregistre(array $lot): void
    {
        $this->upsert('amendement', self::COLONNES, $lot, [], [
            'legislature', 'expose', 'seance_ref', 'numero_ordre', 'date_sort', 'signataires',
        ]);
    }

    /** Un exposé vide vaut absence de donnée, pas exposé vide. */
    private function texte(?string $valeur): ?string
    {
        return $valeur === null || trim($valeur) === '' ? null : $valeur;
    }
}
