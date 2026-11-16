<?php

namespace App\Http;

use App\Command\DetourerPhotosCommand;
use App\Command\ImportPhotosCommand;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * L'empreinte de tout ce que lisent les pages figées.
 *
 * Une page figée n'expire pas et la purge quotidienne l'épargne
 * ({@see MagasinDouble}). Encore faut-il qu'elle dise toujours vrai : même les
 * résultats d'une élection de 2022 affichent le groupe **d'aujourd'hui** du
 * député candidat, sa photo, et « Ancien député » s'il a quitté son siège.
 * Plutôt que de décréter ces pages immuables, on les garde tant que leurs
 * sources n'ont pas bougé : la synchronisation recalcule cette empreinte et ne
 * vide le magasin figé que si elle a changé — un député qui change de groupe,
 * un suppléant qui arrive. De mai à novembre 2026, un mandat ou un
 * rattachement a commencé ou fini 29 jours sur 184 : quelques purges par
 * mois, contre une par jour auparavant.
 *
 * **D'où la règle pour ranger une page dans le magasin figé : tout ce qu'elle
 * affiche doit être dans le périmètre ci-dessous.** Ce périmètre couvre :
 *
 * - les référentiels écrits à la main — géographie, élections, résultats ;
 * - la composition de l'Assemblée, toutes législatures : députés, mandats,
 *   rattachements, groupes ;
 * - les portraits publiés ;
 * - les scrutins, votes, dossiers et statistiques des législatures closes.
 *
 * Il ne couvre **pas** : les scrutins, votes, dossiers, statistiques et
 * classements de la législature en cours, ni ce que la rédaction écrit sur
 * elle. Une écriture de la rédaction ou d'un député qui touche une page figée
 * — un décryptage d'une législature close, un réseau social, un parrainage —
 * vide le magasin sur-le-champ par `RechauffeurCache::apresPublication(tout:
 * true)`, sans attendre la synchronisation.
 *
 * Trois familles de pages restent donc vivantes quoi qu'elles décrivent : la
 * page d'un scrutin, même de 2013, porte le carrousel des derniers
 * décryptages publiés ; les statistiques d'un groupe dissous se comparent à
 * son héritier de la législature en cours ; et la fiche d'un député sorti
 * pendant la législature en cours se mesure à des moyennes recalculées chaque
 * nuit.
 *
 * Trois colonnes sont écartées à dessein, parce qu'elles bougent presque
 * chaque jour et qu'aucune page figée ne les affiche :
 *
 * - les horodatages techniques (`created_at`, `updated_at`), réécrits à chaque
 *   import sans que rien ne change à l'écran ;
 * - `depute.age`, qui avance chaque jour pour quelqu'un ;
 * - `depute.commission` : de mai à novembre 2026, un mandat de commission a
 *   commencé ou fini 121 jours sur 184 — les remplacements de quelques jours
 *   — contre 29 pour les sièges et les groupes. Avec elle, le magasin figé
 *   serait vidé deux jours sur trois.
 *
 * Âge et commission ne paraissent que sur la fiche d'un député en exercice
 * (`depute/_carte_profil.html.twig`), qui est vivante. **Une page qui en
 * afficherait un ne peut donc pas être figée.**
 */
final class EmpreinteFigee
{
    /**
     * Tables lues en entier. Une table ajoutée au schéma et lue par une page
     * figée doit entrer ici, ou dans {@see self::PARTIELLES}.
     *
     * @var list<string>
     */
    private const ENTIERES = [
        // Géographie et élections : imports lancés à la main.
        'departement', 'commune', 'commune_circonscription', 'commune_adjacente',
        'election', 'candidature', 'liste_europeenne',
        'resultat_legislative', 'resultat_presidentielle', 'resultat_europeenne',
        'resultat_circonscription', 'participation_circonscription', 'partielle_legislative',
        'municipale_resultat', 'municipale_participation',
        'parrainage', 'profession_foi',
        // Composition de l'Assemblée : la synchronisation y écrit chaque nuit,
        // mais n'y change quelque chose que quelques jours par mois.
        'depute', 'mandat', 'fonction_groupe', 'groupe', 'parti',
        // Écrit par l'administration, qui vide elle-même le magasin figé :
        // ici en filet, si un écran futur l'oubliait.
        'contact_depute',
    ];

    /**
     * Tables dont seules les lignes d'une législature close sont figées.
     * La valeur est la clause qui les borne, `:courante` valant la législature
     * en cours.
     *
     * @var array<string, string>
     */
    private const PARTIELLES = [
        'scrutin' => 'legislature < :courante',
        'vote' => 'scrutin_id IN (SELECT id FROM scrutin WHERE legislature < :courante)',
        'vote_groupe' => 'scrutin_id IN (SELECT id FROM scrutin WHERE legislature < :courante)',
        'statistique_depute' => 'legislature < :courante',
        'accord_groupe' => 'legislature < :courante',
        // Le dépôt de la législature en cours porte des dossiers antérieurs,
        // que l'import nocturne réécrit sans filtre.
        'dossier' => 'legislature < :courante',
        // Comme `contact_depute` : la rédaction purge d'elle-même, ceci rattrape.
        'decryptage' => 'legislature < :courante',
    ];

    /** @var array<string, list<string>> */
    private const COLONNES_ECARTEES = [
        '*' => ['created_at', 'updated_at'],
        'depute' => ['age', 'commission'],
    ];

    /**
     * Dossiers de portraits, relatifs à `public/` : la fonction Twig
     * `photo_depute` choisit le fichier selon ce qui s'y trouve, et son
     * adresse porte l'empreinte du contenu.
     *
     * @var list<string>
     */
    private const PORTRAITS = [
        'assets/imgs/deputes_original',
        'assets/imgs/deputes_nobg',
        DetourerPhotosCommand::DOSSIER_PUBLIC,
        ImportPhotosCommand::DOSSIER_PUBLIC,
    ];

    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $racinePublique,
    ) {
    }

    /**
     * 150 ms sur la base locale (1,4 M de votes, 54 000 résultats). La
     * production porte 2,4 M de votes et 3,1 M de résultats par commune :
     * compter quelques secondes — donc à calculer dans une commande, jamais
     * dans une requête web.
     */
    public function calculer(): string
    {
        $existantes = array_flip($this->connection->fetchFirstColumn('SHOW TABLES'));
        $parties = [];

        foreach (self::ENTIERES as $table) {
            $parties[$table] = isset($existantes[$table]) ? $this->table($table, '1 = 1') : 'absente';
        }

        foreach (self::PARTIELLES as $table => $borne) {
            $parties[$table] = isset($existantes[$table]) ? $this->table($table, $borne) : 'absente';
        }

        $parties['portraits'] = $this->portraits();

        return hash('xxh128', json_encode($parties, \JSON_THROW_ON_ERROR));
    }

    /**
     * Les sources ont-elles bougé depuis la dernière consignation ? Vrai aussi
     * quand rien n'a été consigné : dans le doute, on vide. L'empreinte
     * présente est consignée au passage.
     */
    public function aChange(string $fichier): bool
    {
        $consignee = is_file($fichier) ? trim((string) file_get_contents($fichier)) : '';
        $presente = $this->calculer();

        if ($consignee === $presente) {
            return false;
        }

        $this->ecrire($fichier, $presente);

        return true;
    }

    /**
     * À poser quand le magasin figé est vide et qu'aucune empreinte n'est
     * consignée : c'est de cet état des sources qu'il repart.
     */
    public function consignerSiAbsente(string $fichier): void
    {
        if (!is_file($fichier)) {
            $this->ecrire($fichier, $this->calculer());
        }
    }

    /**
     * Oublie l'empreinte consignée : le prochain réchauffage la reposera.
     * Vider le magasin figé se fait aussi depuis une requête web — un
     * rédacteur qui publie —, où l'on n'a pas trois secondes à donner au
     * calcul.
     */
    public function oublier(string $fichier): void
    {
        if (is_file($fichier)) {
            @unlink($fichier);
        }
    }

    /**
     * Nombre de lignes, et deux agrégats du CRC de chaque ligne : le OU
     * exclusif seul laisserait deux modifications se compenser.
     */
    private function table(string $table, string $borne): string
    {
        $ecartees = array_merge(self::COLONNES_ECARTEES['*'], self::COLONNES_ECARTEES[$table] ?? []);
        $colonnes = [];

        // Les colonnes sont relues à chaque calcul : une colonne ajoutée au
        // schéma entre d'elle-même dans l'empreinte.
        foreach ($this->connection->fetchFirstColumn('SHOW COLUMNS FROM ' . $this->connection->quoteSingleIdentifier($table)) as $colonne) {
            if (!\in_array($colonne, $ecartees, true)) {
                // CONCAT_WS saute les NULL : sans marqueur, (a, NULL) et
                // (NULL, a) donneraient la même ligne.
                $colonnes[] = sprintf("COALESCE(%s, '\\\\N')", $this->connection->quoteSingleIdentifier($colonne));
            }
        }

        $ligne = sprintf("CRC32(CONCAT_WS('|', %s))", implode(', ', $colonnes));

        return implode(':', $this->connection->fetchNumeric(
            sprintf(
                'SELECT COUNT(*), COALESCE(BIT_XOR(%1$s), 0), COALESCE(SUM(%1$s), 0) FROM %2$s WHERE %3$s',
                $ligne,
                $this->connection->quoteSingleIdentifier($table),
                $borne,
            ),
            str_contains($borne, ':courante') ? ['courante' => Legislature::COURANTE] : [],
        ) ?: []);
    }

    /**
     * Nom, poids et date de chaque portrait : quelques milliers de `stat()`.
     * Les dossiers alimentés par la synchronisation sont partagés entre
     * versions, leurs dates ne bougent donc pas d'un déploiement à l'autre.
     */
    private function portraits(): string
    {
        $lignes = [];

        foreach (self::PORTRAITS as $dossier) {
            foreach (glob($this->racinePublique . '/' . $dossier . '/*') ?: [] as $fichier) {
                if (is_file($fichier)) {
                    $lignes[] = $dossier . '/' . basename($fichier) . ':' . filesize($fichier) . ':' . filemtime($fichier);
                }
            }
        }

        sort($lignes);

        return hash('xxh128', implode("\n", $lignes));
    }

    private function ecrire(string $fichier, string $empreinte): void
    {
        if (!is_dir(\dirname($fichier))) {
            mkdir(\dirname($fichier), 0777, true);
        }

        file_put_contents($fichier, $empreinte . "\n", \LOCK_EX);
    }
}
