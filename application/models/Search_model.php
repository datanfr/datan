<?php
class Search_model extends CI_Model
{
    public function __construct()
    {
    }

    public function searchInAll($search, $category_max, $total_max)
    {

        // Pour tester ajouter `score` dans le select et decommenter cote Home
        $category_max = $category_max ?? 999999999;
        $total_max = $total_max ?? 999999999;

        $search = $this->db->escape($search);
        $searchString = $this->db->escape_str($search);
        $searchLike = $this->db->escape_like_str($search);
        $category_max = $this->db->escape($category_max);

        $sql = "SELECT `source`, `title`, `description`, `url` FROM (
        (SELECT
            'vote' AS source,
            vd.title AS title,
            CONCAT('...', SUBSTRING(vd.description, GREATEST(1, LOCATE(" . $search . ", vd.description) - 100), LEAST(LENGTH(vd.description), 200 + LENGTH(" . $search . "))), '...') AS description,
            CONCAT('votes/legislature-', vd.legislature, '/vote_', vd.voteNumero) as url,
            MATCH(vd.title, vd.description) AGAINST(" . $search . ") as score
        FROM votes_datan vd
        WHERE MATCH(vd.title, vd.description) AGAINST(" . $search . ")
        LIMIT " . $category_max . "
        )
        /* require: ALTER TABLE `votes_datan` ADD FULLTEXT `idx_search` (`title`, `description`); */
        UNION ALL

        (
          SELECT
            'groupe' AS source,
            CONCAT(o.libelle, ' (', o.libelleAbrev, ')') AS title,
            CONCAT('Législature ', o.legislature) AS description,
            CONCAT('groupes/legislature-', o.legislature, '/', LOWER(o.libelleAbrev)) AS url,
            MATCH(o.libelle) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE) as score
          FROM organes o
          WHERE o.coteType = 'GP' AND (MATCH(o.libelle) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE) OR o.libelleAbrev LIKE '" . $searchLike . "%')
          ORDER BY o.dateDebut DESC
          LIMIT " . $category_max . "
          /* require: ALTER TABLE `organes` ADD FULLTEXT `idx_search` (`libelle`); */
        )

        UNION ALL

        (SELECT
            'depute' AS source,
            CONCAT(d.nameFirst, ' ', d.nameLast) AS title,
            d.libelle AS description,
            CONCAT('deputes/', d.dptSlug, '/depute_', d.nameUrl) as url,
            MATCH(d.nameFirst, d.nameLast) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE) as score
        FROM deputes_last d
        WHERE MATCH(d.nameFirst, d.nameLast) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE)
        /* require: ALTER TABLE `deputes_last` ADD FULLTEXT `idx_search` (`nameFirst`, `nameLast`); */
        LIMIT " . $category_max . "
    )
        UNION ALL
        (
            SELECT
                'blog' as source,
            title as title,
            CONCAT('...', SUBSTRING(body, GREATEST(1, LOCATE(" . $search . ", body) - 100), LEAST(LENGTH(body), 200 + LENGTH(" . $search . "))), '...') AS description,
            CONCAT('blog/rapports/', slug) as url,
            MATCH(posts.title, posts.body) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE) as score
            FROM posts
            WHERE MATCH(posts.title, posts.body) AGAINST('*" . $searchString . "*' IN BOOLEAN MODE)
            LIMIT " . $category_max . "
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
            WHERE c.commune_nom LIKE '" . $searchLike . "%'
            GROUP BY c.commune_nom
            ORDER BY ci.pop2017 DESC
            LIMIT " . $category_max . "
        )

    ) AS combined_results
    ORDER BY score DESC
    LIMIT " . $total_max. "
    ";

        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function sort($input){

      $output = array(
        'depute' => array('name' => 'Députés', 'results' => array()),
        'groupe' => array('name' => 'Groupes politiques', 'results' => array()),
        'ville' => array('name' => 'Villes', 'results' => array()),
        'vote' => array('name' => 'Votes', 'results' => array()),
        'blog' => array('name' => 'Articles sur Datan', 'results' => array()),
      );

      foreach ($input as $key => $value) {
        array_push($output[$value['source']]['results'], $value);
      }
      return $output;
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
