<?php
class DashboardMP_model extends CI_Model
{
  public function __construct() {
  }

  public function get_votes_to_explain($mpId){
    $sql = 'SELECT vd.voteNumero, vd.legislature, vd.title AS vote_titre, vi.sortCode,
    vi.dateScrutin, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, doss.titre AS dossier,
    vi.nombreVotants, e2.totalExplication,
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
      LEFT JOIN dossiers_votes vdoss ON vd.legislature = vdoss.legislature AND vd.voteNumero = vdoss.voteNumero
      LEFT JOIN dossiers doss ON vdoss.dossierId = doss.dossierId
      LEFT JOIN explications_mp e ON vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero AND e.mpId = ?
      LEFT JOIN ( SELECT legislature, voteNumero, COUNT(*) as "totalExplication" FROM explications_mp WHERE state = 1 GROUP BY legislature, voteNumero) e2
        ON vd.voteNumero = e2.voteNumero AND vd.legislature = e2.legislature
      WHERE vd.state = "published" AND vs.vote IS NOT NULL AND e.text IS NULL AND vd.legislature >= 16
      ORDER BY vi.dateScrutin DESC
    ';

    return $this->db->query($sql, array($mpId, $mpId))->result_array();
  }

  public function get_votes_to_explain_suggestion($array){
    array_multisort(array_column($array, 'nombreVotants'), SORT_DESC, $array);
    $array = array_slice($array, 0, 2);
    return $array;
  }

  public function get_votes_explained($mpId, $published = NULL){
    $sql = 'SELECT e.id, e.voteNumero, e.legislature, e.text AS explication, vd.title AS vote_titre,
      CASE WHEN e.state = 1 THEN "publié" ELSE "brouillon" END AS state, vs.vote,
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
    $results = $this->db->query($sql, $mpId)->result_array();

    foreach ($results as $key => $value) {
      switch ($value['vote']) {
        case 0:
          $results[$key]['position_tweeter'] = "je me suis abstenu";
          break;

        case 1:
          $results[$key]['position_tweeter'] = "j'ai voté pour";
          break;

        case -1:
          $results[$key]['position_tweeter'] = "j'ai voté contre";
          break;

        default:
          $results[$key]['position_tweeter'] = "";
          break;
      }
    }

    return $results;
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

  public function get_last_explanations(){
    $this->db->select('e.id, e.voteNumero, e.legislature, e.mpId, e.text, e.modified_at, d.nameFirst, d.nameLast, d.libelleAbrev, vd.title AS vote');
    $this->db->where('e.state', 1);
    $this->db->order_by('e.created_at', 'DESC');
    $this->db->join('deputes_last d', 'd.mpId = e.mpId', 'left');
    $this->db->join('votes_datan vd', 'vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero', 'left');
    return $this->db->get('explications_mp e')->result_array();
  }

  public function get_explanations_by_mp($mpId)
  {
    $this->db->where('mpId', $mpId);
    $query = $this->db->get('explications_mp');
    return $query->result_array();
  }

}