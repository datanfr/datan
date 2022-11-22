<?php
class DashboardMP_model extends CI_Model
{
  public function __construct() {
  }

  public function get_votes_to_explain($mpId){
    $sql = 'SELECT vd.voteNumero, vd.legislature, vd.title AS vote_titre, vi.sortCode, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, doss.titre AS dossier,
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
      LEFT JOIN votes_dossiers vdoss ON vd.legislature = vdoss.legislature AND vd.voteNumero = vdoss.voteNumero
      LEFT JOIN dossiers doss ON vdoss.dossier = doss.titreChemin
      LEFT JOIN explications_mp e ON vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero AND e.mpId = ?
      WHERE vd.state = "published" AND vs.vote IS NOT NULL AND e.text IS NULL AND vd.legislature >= 16
      ORDER BY vi.dateScrutin DESC
    ';
    return $this->db->query($sql, array($mpId, $mpId))->result_array();
  }

  public function get_votes_explained($mpId, $published = NULL){
    $sql = 'SELECT e.id, e.voteNumero, e.legislature, e.text AS explication, vd.title AS vote_titre,
      CASE WHEN e.state = 1 THEN "publié" ELSE "brouillon" END AS state,
      CASE
        WHEN vs.vote = 0 THEN "abstention"
        WHEN vs.vote = 1 THEN "pour"
        WHEN vs.vote = -1 THEN "contre"
        WHEN vs.vote IS NULL THEN "absent"
        ELSE vs.vote
      END AS vote_depute
      FROM explications_mp e
      LEFT JOIN votes_scores vs ON e.voteNumero = vs.voteNumero AND e.legislature = vs.legislature AND vs.mpId = e.mpId
      LEFT JOIN votes_datan vd ON e.voteNumero = vd.voteNumero AND e.legislature = vd.legislature
      LEFT JOIN votes_info vi ON e.voteNumero = vi.voteNumero AND e.legislature = vi.legislature
      WHERE e.mpId = ?
    ';
    if ($published === TRUE) {
      $sql .= ' AND e.state = 1';
    } elseif ($published === FALSE) {
      $sql .= ' AND e.state = 0';
    }
    $sql .= ' ORDER BY e.id DESC';
    $query = $this->db->query($sql, $mpId);
    return $query->result_array();
  }

  public function get_vote_explained($mpId, $legislature, $voteNumero){
    echo $voteNumero;
    $sql = 'SELECT e.id, e.voteNumero, e.legislature, e.text AS explication, vd.title AS vote_titre,
      CASE WHEN e.state = 1 THEN "publié" ELSE "brouillon" END AS state,
      CASE
        WHEN vs.vote = 0 THEN "abstention"
        WHEN vs.vote = 1 THEN "pour"
        WHEN vs.vote = -1 THEN "contre"
        WHEN vs.vote IS NULL THEN "absent"
        ELSE vs.vote
      END AS vote_depute
      FROM explications_mp e
      LEFT JOIN votes_scores vs ON e.voteNumero = vs.voteNumero AND e.legislature = vs.legislature AND vs.mpId = e.mpId
      LEFT JOIN votes_datan vd ON e.voteNumero = vd.voteNumero AND e.legislature = vd.legislature
      LEFT JOIN votes_info vi ON e.voteNumero = vi.voteNumero AND e.legislature = vi.legislature
      WHERE e.mpId = ? AND e.legislature = ? AND e.voteNumero = ?
    ';
    $query = $this->db->query($sql, array($mpId, $legislature, $voteNumero));
    return $query->row_array();
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

  public function modify_explication($input){
    $data = array(
      'text' => $this->input->post('explication'),
      'state' => $this->input->post('state'),
      'modified_at' => date("Y-m-d H:i:s")
    );
    $this->db->set($data);
    $this->db->where('mpId', $input['depute']['mpId']);
    $this->db->where('legislature', $input['legislature']);
    $this->db->where('voteNumero', $input['voteNumero']);
    $this->db->update('explications_mp');
  }

  public function delete_explanation($input){
    $this->db->where('mpId', $input['depute']['mpId']);
    $this->db->where('legislature', $input['legislature']);
    $this->db->where('voteNumero', $input['voteNumero']);
    $this->db->delete('explications_mp');
  }

}
