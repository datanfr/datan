<?php
class DashboardMP_model extends CI_Model
{
  public function __construct() {
  }

  public function get_votes_explanation($depute_id){
    $sql = 'SELECT vd.voteNumero, vd.legislature, vd.title AS vote_titre, vi.sortCode, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, r.name AS reading, doss.titre AS dossier,
      CASE
        WHEN vs.vote = 0 THEN "abstention"
        WHEN vs.vote = 1 THEN "pour"
        WHEN vs.vote = -1 THEN "contre"
        WHEN vs.vote IS NULL THEN "absent"
        ELSE vs.vote
      END AS vote_depute
      FROM votes_datan vd
      LEFT JOIN votes_scores vs ON vd.voteNumero = vs.voteNumero AND vd.legislature = vs.legislature AND vs.mpId = ?
      LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
      LEFT JOIN readings r ON r.id = vd.reading
      LEFT JOIN votes_dossiers vdoss ON vd.legislature = vdoss.legislature AND vd.voteNumero = vdoss.voteNumero
      LEFT JOIN dossiers doss ON vdoss.dossier = doss.titreChemin
      WHERE vd.state = "published" AND vs.vote IS NOT NULL
      ORDER BY vi.dateScrutin DESC
    ';
    return $this->db->query($sql, array($depute_id))->result_array();
  }

  public function create_explication(){
    echo "yes";
  }

}
