<?php
class Search_model extends CI_Model
{
    public function __construct()
    {
    }
    public function searchInAll($search)
    {

        // Pour tester ajouter `score` dans le select et decommenter cote Home
        $sql = "SELECT `source`, `title`, `description`, `url` FROM (
        (SELECT
            'vote' AS source,
            vd.title AS title,
            CONCAT('...', SUBSTRING(vd.description, GREATEST(1, LOCATE('" . $search . "', vd.description) - 100), LEAST(LENGTH(vd.description), 200 + LENGTH('" . $search . "'))), '...') AS description,
            CONCAT('votes/legislature-', vd.legislature, '/vote_', vd.voteNumero) as url,
            MATCH(vd.title, vd.description) AGAINST('" . $search . "') as score
        FROM votes_datan vd
        WHERE MATCH(vd.title, vd.description) AGAINST('" . $search . "')
        LIMIT 5
        )
        /* require: ALTER TABLE `votes_datan` ADD FULLTEXT `idx_search` (`title`, `description`); */
        UNION ALL

        (
          SELECT
            'groupe' AS source,
            CONCAT(o.libelle, ' (', o.libelleAbrev, ')') AS title,
            CONCAT('Législature ', o.legislature) AS description,
            CONCAT('groupes/legislature-', o.legislature, '/', LOWER(o.libelleAbrev)) AS url,
            MATCH(o.libelle) AGAINST('*" . $search . "*' IN BOOLEAN MODE) as score
          FROM organes o
          WHERE o.coteType = 'GP' AND (MATCH(o.libelle) AGAINST('*" . $search . "*' IN BOOLEAN MODE) OR o.libelleAbrev LIKE '" . $search . "%')
          ORDER BY o.dateDebut DESC
          LIMIT 5
          /* require: ALTER TABLE `organes` ADD FULLTEXT `idx_search` (`libelle`); */
        )

        UNION ALL

        (SELECT
            'depute' AS source,
            CONCAT(d.nameFirst, ' ', d.nameLast) AS title,
            d.libelle AS description,
            CONCAT('deputes/', d.dptSlug, '/depute_', d.nameUrl) as url,
            MATCH(d.nameFirst, d.nameLast) AGAINST('*" . $search . "*' IN BOOLEAN MODE) as score
        FROM deputes_last d
        WHERE MATCH(d.nameFirst, d.nameLast) AGAINST('*" . $search . "*' IN BOOLEAN MODE)
        /* require: ALTER TABLE `deputes_last` ADD FULLTEXT `idx_search` (`nameFirst`, `nameLast`); */
        LIMIT 5
    )
        UNION ALL
        (
            SELECT
                'blog' as source,
            title as title,
            CONCAT('...', SUBSTRING(body, GREATEST(1, LOCATE('" . $search . "', body) - 100), LEAST(LENGTH(body), 200 + LENGTH('" . $search . "'))), '...') AS description,
            CONCAT('blog/rapports/', slug) as url,
            MATCH(posts.title, posts.body) AGAINST('*" . $search . "*' IN BOOLEAN MODE) as score
            FROM posts
            WHERE MATCH(posts.title, posts.body) AGAINST('*" . $search . "*' IN BOOLEAN MODE) LIMIT 5
            /* require: ALTER TABLE `posts` ADD FULLTEXT `idx_search` (`title`, `body`); */
        )

        UNION ALL
        (
            SELECT
                'ville' as source,
            c.commune_nom as title,
            d.departement_nom AS description,
            CONCAT('deputes/', d.slug, '/ville_', c.commune_slug) as url,
            ci.pop2017/10000 AS score
            FROM circos c
            LEFT JOIN departement d ON c.dpt = d.departement_code
            LEFT JOIN cities_infos ci ON c.insee = ci.insee
            WHERE c.commune_nom LIKE '" . $search . "%'
            GROUP BY c.commune_nom
            ORDER BY ci.pop2017 DESC
            LIMIT 5
        )

    ) AS combined_results
    ORDER BY score DESC
    LIMIT 10;";

    $data = $this->db->query($sql)->result_array();

        return $data;
    }
    public function searchInVotes($search)
    {
        $sql = '
        SELECT vd.title, SUBSTRING(
            vd.description,
            GREATEST(1, LOCATE("' . $search . '", vd.description) - 100),
            LEAST(LENGTH(vd.description), 200 + LENGTH("' . $search . '"))
        ) AS description, vd.legislature, vd.voteNumero, vd.modified_at
        FROM votes_datan vd
        WHERE vd.title LIKE "%' . $search . '%" OR vd.description LIKE "%' . $search . '%"
        ORDER BY vd.modified_at DESC LIMIT 10';
        $data = $this->db->query($sql)->result_array();

        return $data;
    }
    public function searchInDeputes($search)
    {
        $sql = '
        SELECT d.nameFirst, d.nameLast, d.nameUrl, d.dptSlug, d.libelle
        FROM deputes_all d
        WHERE d.nameFirst LIKE "%' . $search . '%" OR d.nameLast LIKE "%' . $search . '%"
        ORDER BY d.datePriseFonction DESC LIMIT 10';
        $data = $this->db->query($sql)->result_array();

        return $data;
    }
}