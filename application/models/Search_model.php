<?php
class Search_model extends CI_Model
{
    public function __construct()
    {
    }
    public function searchInAll($search)
    {

        $sql = "SELECT * FROM (
        (SELECT
            'vote' AS source,
            vd.title AS title,
            CONCAT('...', SUBSTRING(vd.description, GREATEST(1, LOCATE('" . $search . "', vd.description) - 100), LEAST(LENGTH(vd.description), 200 + LENGTH('" . $search . "'))), '...') AS description,
            CONCAT('votes/legislature-', vd.legislature, '/vote_', vd.voteNumero) as url,
            vd.modified_at AS date_modified
        FROM votes_datan vd
        WHERE vd.title LIKE '%" . $search . "%' OR vd.description LIKE '%" . $search . "%' LIMIT 5
        )

        UNION ALL

        (SELECT
            'depute' AS source,
            CONCAT(d.nameFirst, ' ', d.nameLast) AS title,
            d.libelle AS description,
            CONCAT('deputes/', d.dptSlug, '/depute_', d.nameUrl) as url,
            d.datePriseFonction AS date_modified
        FROM deputes_all d
        WHERE d.nameFirst LIKE '%" . $search . "%' OR d.nameLast LIKE '%" . $search . "%' LIMIT 5
    )
        UNION ALL
        (
            SELECT
                'blog' as source,
            title as title,
            CONCAT('...', SUBSTRING(body, GREATEST(1, LOCATE('" . $search . "', body) - 100), LEAST(LENGTH(body), 200 + LENGTH('" . $search . "'))), '...') AS description,
            CONCAT('blog/rapports/', slug) as url,
            modified_at as date_modified
            FROM posts
            WHERE title LIKE '%" . $search . "%' OR body LIKE '%" . $search . "%' LIMIT 5

        )

        UNION ALL
        (
          SELECT
              'ville' as source,
          c.commune_nom as title,
          c.commune_nom AS description,
          CONCAT('deputes/', d.slug, '/ville_', c.commune_slug) as url,
          NULL AS date_modified
          FROM circos c
          LEFT JOIN departement d ON c.dpt = d.departement_code
          WHERE c.commune_nom LIKE '%" . $search . "%'
          GROUP BY c.commune_nom
          LIMIT 5
        )

    ) AS combined_results
    ORDER BY date_modified DESC
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
