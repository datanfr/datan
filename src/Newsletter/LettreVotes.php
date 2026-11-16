<?php

namespace App\Newsletter;

use App\Legislature;
use App\Twig\DatanExtension;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

/**
 * La lettre mensuelle des votes (`Newsletter::votes` du legacy) : ce qui s'est
 * voté le mois écoulé, les votes décryptés par la rédaction, le député le plus
 * assidu.
 *
 * Composée en MJML ({@see templates/newsletter/lettre_votes.mjml.twig}), puis
 * convertie en HTML par l'API de mjml.io, comme au legacy (`mjml_helper.php`) :
 * le compilateur MJML est un programme Node, absent de l'hébergement.
 *
 * Les requêtes reprennent celles de `Votes_model` et `Deputes_model`, sur nos
 * tables. Les votes du Congrès en sont exclus : la table `votes_info` du legacy
 * ne les contient pas, et ils n'ont rien à faire dans « les votes de
 * l'Assemblée nationale ».
 */
class LettreVotes
{
    /** En deçà, pas de lettre : il n'y a rien de décrypté à présenter. */
    private const MIN_DECRYPTES = 1;

    /** Au-delà de trois décryptés, la lettre retient les plus suivis plutôt que les plus récents. */
    private const VOTES_PRESENTES = 3;

    /**
     * Le bloc du député le plus assidu ne paraît qu'au-delà de 25 scrutins dans
     * le mois, et ne retient qu'un député ayant pris part à au moins 3 d'entre
     * eux : sur un mois creux, 100 % de participation ne dit rien.
     */
    private const MIN_SCRUTINS_POUR_DEPUTE = 25;
    private const MIN_VOTES_DEPUTE = 3;

    /** Mots gardés du contexte d'un vote (`word_limiter($description, 30)`). */
    private const MOTS_CONTEXTE = 30;

    private const API_MJML = 'https://api.mjml.io/v1/render';

    public function __construct(
        private readonly Connection $connection,
        private readonly Environment $twig,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'MJML_APP_ID')] private readonly string $mjmlId,
        #[Autowire(env: 'MJML_SECRET_KEY')] private readonly string $mjmlCle,
    ) {
    }

    public function mjmlEstConfigure(): bool
    {
        return $this->mjmlId !== '' && $this->mjmlCle !== '';
    }

    /**
     * Les données de la lettre pour un mois, ou null quand aucun vote décrypté
     * n'y a été tenu — le legacy n'envoie rien dans ce cas.
     *
     * @return array<string, mixed>|null
     */
    public function donnees(\DateTimeImmutable $mois): ?array
    {
        $debut = $mois->modify('first day of this month')->setTime(0, 0);
        $bornes = [
            'legislature' => Legislature::COURANTE,
            'debut' => $debut->format('Y-m-d H:i:s'),
            'fin' => $debut->modify('+1 month')->format('Y-m-d H:i:s'),
            'assemblee' => 'VTANR%',
        ];
        $periode = 's.legislature = :legislature AND s.date_scrutin >= :debut AND s.date_scrutin < :fin AND s.uid LIKE :assemblee';

        $votesDecryptes = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM decryptage d JOIN scrutin s ON s.id = d.scrutin_id
             WHERE d.state = \'published\' AND ' . $periode,
            $bornes,
        );

        if ($votesDecryptes < self::MIN_DECRYPTES) {
            return null;
        }

        $bilan = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS total,
                    SUM(s.sort_code = \'adopté\') AS adoptes,
                    SUM(s.sort_code = \'rejeté\') AS rejetes
             FROM scrutin s WHERE ' . $periode,
            $bornes,
        );

        $importants = $votesDecryptes > self::VOTES_PRESENTES;

        // Plus de trois décryptés : les plus suivis, au nombre de votants ;
        // sinon tous, du plus récent au plus ancien. Le numéro départage les
        // ex aequo, que le legacy laissait au hasard du moteur.
        $votes = $this->connection->fetchAllAssociative(
            'SELECT d.title AS titre, d.description, s.id AS scrutin_id, s.numero, s.legislature,
                    s.sort_code, s.date_scrutin, s.nombre_votants, s.nombre_pour, s.nombre_contre
             FROM decryptage d JOIN scrutin s ON s.id = d.scrutin_id
             WHERE d.state = \'published\' AND ' . $periode . '
             ORDER BY ' . ($importants ? 's.nombre_votants DESC, s.numero DESC' : 's.numero DESC') . '
             LIMIT ' . self::VOTES_PRESENTES,
            $bornes,
        );

        foreach ($votes as &$vote) {
            $vote['contexte'] = $this->contexte((string) $vote['description']);
            $vote['groupes_pour'] = $this->connection->fetchAllAssociative(
                'SELECT g.libelle_abrev, g.libelle
                 FROM vote_groupe vg JOIN groupe g ON g.id = vg.groupe_id
                 WHERE vg.scrutin_id = :scrutin AND vg.position_majoritaire = \'pour\'
                 ORDER BY vg.nombre_membres_groupe DESC',
                ['scrutin' => $vote['scrutin_id']],
            );
        }
        unset($vote);

        $total = (int) $bilan['total'];

        return [
            'mois' => DatanExtension::MOIS[(int) $debut->format('n')],
            'annee' => (int) $debut->format('Y'),
            'votes_total' => $total,
            'votes_decryptes' => $votesDecryptes,
            'bilan' => $this->phraseBilan((int) $bilan['adoptes'], (int) $bilan['rejetes']),
            'importants' => $importants,
            'votes' => $votes,
            'depute' => $total > self::MIN_SCRUTINS_POUR_DEPUTE ? $this->deputeLePlusAssidu($bornes, $periode) : null,
        ];
    }

    /** Le document MJML de la lettre. */
    public function mjml(array $donnees, string $titre): string
    {
        return $this->twig->render('newsletter/lettre_votes.mjml.twig', $donnees + ['titre' => $titre]);
    }

    /**
     * Compile le MJML en HTML par l'API de mjml.io, puis ramène chaque suite de
     * blancs à un seul espace.
     *
     * Cette réduction tient lieu du `TinyMinify` du legacy, et elle n'est pas
     * cosmétique : Gmail tronque tout message de plus de 102 Ko derrière un
     * « Afficher l'intégralité du message », et la sortie de MJML, très
     * indentée, s'en approche vite. Un seul espace garde le sens du HTML (une
     * lettre n'a ni `<pre>` ni `<textarea>`).
     */
    public function html(string $mjml): string
    {
        if (!$this->mjmlEstConfigure()) {
            throw new \LogicException('L\'API MJML n\'est pas configurée (MJML_APP_ID, MJML_SECRET_KEY).');
        }

        $reponse = $this->httpClient->request('POST', self::API_MJML, [
            'auth_basic' => [$this->mjmlId, $this->mjmlCle],
            'json' => ['mjml' => $mjml],
            'timeout' => 60,
        ]);

        $corps = $reponse->toArray(false);

        if ($reponse->getStatusCode() !== 200 || !isset($corps['html'])) {
            throw new \RuntimeException(sprintf('L\'API MJML a répondu %d : %s', $reponse->getStatusCode(), $corps['message'] ?? json_encode($corps)));
        }

        return trim(preg_replace('/\s+/u', ' ', $corps['html']));
    }

    /**
     * La phrase « Au total, … » ; rien quand adoptés et rejetés s'équilibrent,
     * comme au legacy. Le plus grand nombre vient en tête.
     */
    private function phraseBilan(int $adoptes, int $rejetes): ?string
    {
        if ($adoptes === $rejetes) {
            return null;
        }

        $adopte = $adoptes <= 1 ? [$adoptes, 'vote', 'a été adopté'] : [$adoptes, 'votes', 'ont été adoptés'];
        $rejete = $rejetes <= 1 ? [$rejetes, 'vote', 'a été rejeté'] : [$rejetes, 'votes', 'ont été rejetés'];
        [$premier, $second] = $adoptes > $rejetes ? [$adopte, $rejete] : [$rejete, $adopte];

        return sprintf('Au total, %d %s %s par les députés tandis que %d %s %s.', ...[...$premier, ...$second]);
    }

    /**
     * Le premier paragraphe du décryptage, sans balises, coupé à trente mots.
     *
     * Le legacy coupait au premier `</p>` sans vérifier qu'il existe : un
     * décryptage d'un seul tenant, sans paragraphe, ne gardait que ses quatre
     * premiers caractères. Défaut corrigé — le texte entier tient alors lieu de
     * premier paragraphe.
     */
    private function contexte(string $description): string
    {
        $fin = stripos($description, '</p>');
        $paragraphe = $fin === false ? $description : substr($description, 0, $fin);
        $texte = html_entity_decode(strip_tags($paragraphe), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        // L'éditeur de la rédaction sème des `&nbsp;`, que `\s` ne reconnaît pas.
        $mots = preg_split('/[\s\x{00A0}]+/u', $texte, -1, \PREG_SPLIT_NO_EMPTY);
        $contexte = implode(' ', \array_slice($mots, 0, self::MOTS_CONTEXTE));

        return \count($mots) > self::MOTS_CONTEXTE ? $contexte . '…' : $contexte;
    }

    /**
     * Le député qui a pris part au plus grand nombre de scrutins du mois, en
     * proportion (`get_depute_vote_plus_month`, sur la table `votes_participation`
     * du legacy).
     *
     * Les règles de cette table, qui sont celles de toute participation sur le
     * site (cf. `CalculClassementsCommand::participationDeputes()`) : les
     * scrutins tenus pendant son mandat, motions de censure exclues ; un scrutin
     * où il est non-votant (présidence de séance, Gouvernement) ne compte ni au
     * numérateur ni au dénominateur.
     *
     * @param array<string, mixed> $bornes
     *
     * @return array<string, mixed>|null
     */
    private function deputeLePlusAssidu(array $bornes, string $periode): ?array
    {
        $depute = $this->connection->fetchAssociative(
            'SELECT t.depute_id,
                    SUM(t.exprime) AS votes,
                    100 * SUM(t.exprime) / (COUNT(*) - SUM(t.retire)) AS score
             FROM (SELECT m.depute_id, s.id,
                          COALESCE(MAX(v.position IN (\'pour\', \'contre\', \'abstention\')), 0) AS exprime,
                          COALESCE(MAX(v.position = \'nonVotant\'), 0) AS retire
                   FROM scrutin s
                   JOIN mandat m ON m.legislature = s.legislature
                    AND DATE(s.date_scrutin) BETWEEN COALESCE(m.date_prise_fonction, m.date_debut)
                                                 AND COALESCE(m.date_fin, \'9999-12-31\')
                   LEFT JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = m.depute_id
                    AND v.vote_type = \'decompteNominatif\'
                   WHERE ' . $periode . ' AND (s.code_type_vote IS NULL OR s.code_type_vote <> \'MOC\')
                   GROUP BY m.depute_id, s.id) t
             GROUP BY t.depute_id
             HAVING votes >= :minVotes AND COUNT(*) > SUM(t.retire)
             ORDER BY score DESC, votes DESC
             LIMIT 1',
            $bornes + ['minVotes' => self::MIN_VOTES_DEPUTE],
        );

        if ($depute === false) {
            return null;
        }

        $fiche = $this->connection->fetchAssociative(
            'SELECT mp_id, firstname, lastname, slug, dpt_slug, civilite FROM depute WHERE id = :id',
            ['id' => $depute['depute_id']],
        );

        $femme = $fiche['civilite'] === 'Mme';

        return $fiche + [
            'votes' => (int) $depute['votes'],
            'score' => (int) round((float) $depute['score']),
            'le' => $femme ? 'la' : 'le',
            'depute' => $femme ? 'députée' : 'député',
            'pronom' => $femme ? 'elle' : 'il',
        ];
    }
}
