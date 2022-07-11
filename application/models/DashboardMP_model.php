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
      LEFT JOIN explications_mp e ON vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero AND e.mpId = ?
      WHERE vd.state = "published" AND vs.vote IS NOT NULL AND e.text IS NULL
      ORDER BY vi.dateScrutin DESC
    ';
    return $this->db->query($sql, array($depute_id, $depute_id))->result_array();
  }

  public function create_explication($input){
    $data = array(
      'voteNumero' => $input['voteNumero'],
      'legislature' => $input['legislature'],
      'mpId' => $input['depute']['mpId'],
      'text' =>  $this->input->post('explication'),
      'state' => $this->input->post('state'),
    );
    return $this->db->insert('explications_mp', $data);
  }

}
