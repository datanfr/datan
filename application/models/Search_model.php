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

        $search = urldecode($search);
        $search = $this->db->escape_str($search);
        $category_max = $this->db->escape($category_max);
        $total_max = $this->db->escape($total_max);

        $sql = "SELECT `source`, `title`, `description_search`, `description`, `url` FROM (
          (
            SELECT
              'depute' AS source,
              CONCAT(d.nameFirst, ' ', d.nameLast) AS title,
              CONCAT('(', d.libelleAbrev, ')') AS description_search,
              CONCAT('Législature ', d.legislature, ' - ', d.libelleAbrev) AS description,
              CONCAT('deputes/', d.dptSlug, '/depute_', d.nameUrl) as url
          FROM deputes_last d
          WHERE d.legislature >= 14
          AND (CONCAT(d.nameFirst, ' ', d.nameLast) LIKE '" . $search . "%' OR CONCAT(d.nameLast, ' ', d.nameFirst) LIKE '" . $search . "%')
          LIMIT " . $category_max . "
        )

        UNION ALL

        (
          SELECT
            'groupe' AS source,
            CONCAT(o.libelle, ' (', o.libelleAbrev, ')') AS title,
            CONCAT('Leg. ', o.legislature) AS description_search,
            CONCAT('Législature ', o.legislature) AS description,
            CONCAT('groupes/legislature-', o.legislature, '/', LOWER(o.libelleAbrev)) AS url
          FROM organes o
          WHERE o.coteType = 'GP' AND (MATCH(o.libelle) AGAINST('*" . $search . "*' IN BOOLEAN MODE) OR o.libelleAbrev LIKE '" . $search . "%') AND o.legislature >= 14
          ORDER BY o.dateDebut DESC
          LIMIT " . $category_max . "
          /* require: ALTER TABLE `organes` ADD FULLTEXT `idx_search` (`libelle`); */
        )

        UNION ALL

        (
          SELECT
            'ville' as source,
            c.commune_nom as title,
            CONCAT('(', d.departement_code,')') AS description_search,
            CONCAT(d.departement_nom, ' (', d.departement_code,')') AS description,
            CONCAT('deputes/', d.slug, '/ville_', c.commune_slug) as url
          FROM circos c
          LEFT JOIN departement d ON c.dpt = d.departement_code
          LEFT JOIN cities_infos ci ON c.insee = ci.insee
          WHERE c.commune_nom LIKE '" . $search . "%'
          GROUP BY c.commune_nom
          ORDER BY LENGTH(c.commune_nom) - LENGTH('" . $search . "'), ci.pop2017 DESC
          LIMIT " . $category_max . "
        )

        UNION ALL

        (
          SELECT
            'vote' AS source,
            vd.title AS title,
            '' AS description_search,
            vd.description AS description,
            CONCAT('votes/legislature-', vd.legislature, '/vote_', vd.voteNumero) as url
          FROM votes_datan vd
          WHERE MATCH(vd.title, vd.description) AGAINST('" . $search . "')
          ORDER BY MATCH(vd.title, vd.description) AGAINST('*" . $search . "*' IN BOOLEAN MODE) DESC
          LIMIT " . $category_max . "
        )
        /* require: ALTER TABLE `votes_datan` ADD FULLTEXT `idx_search` (`title`, `description`); */

        UNION ALL

        (
          SELECT
            'blog' as source,
            title as title,
            '' AS description_search,
            body AS description,
            CONCAT('blog/rapports/', slug) as url
          FROM posts
          WHERE MATCH(posts.title, posts.body) AGAINST('*" . $search . "*' IN BOOLEAN MODE)
          ORDER BY MATCH(posts.title, posts.body) AGAINST('*" . $search . "*' IN BOOLEAN MODE) DESC
          LIMIT " . $category_max . "
          /* require: ALTER TABLE `posts` ADD FULLTEXT `idx_search` (`title`, `body`); */
        )

      ) AS combined_results
      LIMIT " . $total_max. "
    ";

        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function sort($input, $search){
      $output = array(
        'depute' => array('name' => 'Députés', 'icon' => 'person-fill', 'results' => array()),
        'groupe' => array('name' => 'Groupes politiques', 'icon' => 'people-fill', 'results' => array()),
        'ville' => array('name' => 'Villes', 'icon' => 'house-door-fill', 'results' => array()),
        'vote' => array('name' => 'Votes', 'icon' => 'file-text-fill', 'results' => array()),
        'blog' => array('name' => 'Articles sur Datan', 'icon' => 'file-text-fill', 'results' => array()),
      );

      foreach ($input as $key => $value) {
        $value['description'] = strip_tags($value['description']);

        // Return new description field for vote and blog types
        if (in_array($value['source'], array('vote', 'blog'))) {
          $position = stripos($value['description'], $search);
          if ($position) {
            $start = $position - 100 < 0 ? 0 : $position - 100;
            $value['description_new'] = substr($value['description'], $start, 200);
            $value['description_new'] = $start != 0 ? '...' . $value['description_new'] : $value['description_new'];
            $value['description_new'] = $position + 100 < strlen($value['description']) ? $value['description_new'] . '...' : $value['description_new'];
            $value['description'] = $value['description_new'];

            $value['description'] = $value['description_new'];
          }
        }

        // Color the results
        $value['title'] = highlight_phrase($value['title'], $search, '<span class="text-primary">', '</span>');
        $value['description'] = highlight_phrase($value['description'], $search, '<span class="text-primary">', '</span>');

        array_push($output[$value['source']]['results'], $value);
      }
      return $output;
    }

}
