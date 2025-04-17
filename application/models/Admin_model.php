<?php
class Admin_model extends CI_Model
{
  public function __construct()
  {
  }

  public function get_votes_datan()
  {
    $query = $this->db->query('
        SELECT vd.id, vd.voteNumero AS vote_id, vd.title, vd.slug, f.name AS category, vd.description, vd.state, vd.created_at, vd.modified_at, vd.created_by, vd.modified_by, u1.name AS created_by_name, u2.name AS modified_by_name, r.id AS reading
        FROM votes_datan vd
        LEFT JOIN users u1 ON vd.created_by = u1.id
        LEFT JOIN users u2 ON vd.modified_by = u2.id
        LEFT JOIN fields f ON vd.category = f.id
        LEFT JOIN readings r ON r.id = vd.reading
      ');

    return $query->result_array();
  }

  public function create_candidat($user_id, $depute)
  {
    $url = parse_url($this->input->post('depute_url'));

    $data = array(
      'mpId' => $depute['mpId'],
      'election' => $this->input->post('election'),
      'district' => $this->input->post('district'),
      'position' =>  $this->input->post('position'),
      'secondRound' => $this->input->post('secondRound') == 99 ?  NULL : $this->input->post('secondRound'),
      'elected' => $this->input->post('elected') == 99 ?  NULL : $this->input->post('elected'),
      'visible' => $this->input->post('visible') ? true : false,
      'candidature' => $this->input->post('candidature'),
      'link' => $this->input->post('link')
    );

    $query = $this->db->get_where(
      'elect_deputes_candidats',
      array('mpId' => $data['mpId'], 'election' => $data['election'])
    );

    if ($query->num_rows() > 0) {
      return NULL;
    } else {
      return $this->db->insert('elect_deputes_candidats', $data);
    }
  }

  public function modify_candidat()
  {
    $data = array(
      'candidature' => $this->input->post('candidature'),
      'district' => $this->input->post('district'),
      'position' =>  $this->input->post('position'),
      'secondRound' => $this->input->post('secondRound') == 99 ?  NULL : $this->input->post('secondRound'),
      'elected' => $this->input->post('elected') == 99 ?  NULL : $this->input->post('elected'),
      'link' => $this->input->post('link'),
      'visible' => $this->input->post('visible') ? true : false
    );

    $this->db->set($data);
    $this->db->where('mpId', $this->input->post('mpId'));
    $this->db->where('election', $this->input->post('election'));
    $this->db->update('elect_deputes_candidats');
  }

  public function modify_candidat_as_mp($mpId, $election)
  {
    // Check if MP is already in the database
    $query = $this->db->get_where(
      'elect_deputes_candidats',
      array('mpId' => $mpId, 'election' => $election)
    );

    if ($query->num_rows() > 0) {
      // Modify a candidate
      $data = array(
        'district' => $this->input->post('district'),
        'candidature' => $this->input->post('candidature'),
        'link' => $this->input->post('link')
      );
      $this->db->set($data);
      $this->db->where('mpId', $mpId);
      $this->db->where('election', $election);
      $this->db->update('elect_deputes_candidats');
    } else {
      // Create a candidate
      $data = array(
        'mpId' => $mpId,
        'election' => $election,
        'district' => $this->input->post('district'),
        'candidature' => $this->input->post('candidature'),
      );
      $this->db->insert('elect_deputes_candidats', $data);
    }
  }

  public function delete_candidat()
  {
    $this->db->where('mpId', $this->input->post('mpId'));
    $this->db->where('election', $this->input->post('election'));
    $this->db->delete('elect_deputes_candidats');
  }

  public function create_vote($user_id)
  {
    $slug = convert_accented_characters($this->input->post('title'));
    $slug = url_title($slug, 'dash', TRUE);
    $reading = $this->input->post('reading') != "" ? $this->input->post('reading') : NULL;

    $legislature = $this->input->post('legislature');
    $vote_id_post = $this->input->post('vote_id');

    if ($vote_id_post > 0) {
      $vote_id = "VTANR5L" . $legislature . "V" . $vote_id_post;
    } elseif (condition) {
      $vote_id = "VTCGR5L" . $legislature . "V" . abs($vote_id_post);
    }

    $data = array(
      'title' => $this->input->post('title'),
      'slug' => $slug,
      'legislature' => $legislature,
      'voteNumero' => $this->input->post('vote_id'),
      'vote_id' => $vote_id,
      'category' => $this->input->post('category'),
      'reading' => $reading,
      'description' => $this->input->post('description'),
      'created_at' => date("Y-m-d H:i:s"),
      'state' => 'draft',
      'created_by' => $user_id
    );

    $query = $this->db->get_where('votes_datan', array('voteNumero' => $data['voteNumero'], 'legislature' => $data['legislature']));

    if ($query->num_rows() > 0) {
      return NULL;
    } else {
      return $this->db->insert('votes_datan', $data);
    }
  }

  public function get_vote_datan($id)
  {
    $this->db->join('fields', 'fields.id = votes_datan.category', 'left');
    $this->db->join('readings', 'readings.id = votes_datan.reading', 'left');
    $this->db->select('votes_datan.*, fields.name AS category_name, readings.name AS reading_name');
    $query = $this->db->get_where('votes_datan', array('votes_datan.id' => $id), 1);

    return $query->row_array();
  }

  public function modify_vote($vote, $user_id)
  {
    $slug = convert_accented_characters($this->input->post('title'));
    $slug = url_title($slug, 'dash', TRUE);
    $reading = $this->input->post('reading') != "" ? $this->input->post('reading') : NULL;

    $data = array(
      'title' => $this->input->post('title'),
      'slug' => $slug,
      'category' => $this->input->post('category'),
      'reading' => $reading,
      'description' => $this->input->post('description'),
      'state' => $this->input->post('state'),
      'modified_at' => date("Y-m-d H:i:s"),
      'modified_by' => $user_id
    );

    $this->db->set($data);
    $this->db->where('id', $vote);
    $this->db->update('votes_datan');
  }

  public function delete_vote($vote)
  {
    $this->db->where('id', $vote);
    $this->db->delete('votes_datan');
  }

  public function get_votes_datan_user($user, $published)
  {
    if (!$published) {
      $queryWhere = array(
        'created_by' => $user,
        'state' => 'draft'
      );
      $queryLimit = NULL;
    } else {
      $queryWhere = array(
        'created_by' => $user,
        'state' => 'published'
      );
      $queryLimit = 3;
    }

    $this->db->select('vd.*, vi.voteNumero, f.name AS categoryName');
    $this->db->from('votes_datan vd');
    $this->db->join('votes_info vi', 'vi.voteId = vd.vote_id', 'left');
    $this->db->join('fields f', 'f.id = vd.category', 'left');
    $this->db->where($queryWhere);
    $this->db->order_by('id', 'DESC');
    $this->db->limit($queryLimit);
    $query = $this->db->get();

    return $query->result_array();
  }

  public function create_category($user)
  {
    function skip_accents($str, $charset = 'utf-8')
    {
      $str = htmlentities($str, ENT_NOQUOTES, $charset);
      $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
      $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
      $str = preg_replace('#&[^;]+;#', '', $str);
      return $str;
    }
    $slug = convert_accented_characters($this->input->post('name'));
    $slug = url_title($slug, 'dash', TRUE);

    $data = array(
      'name' => $this->input->post('name'),
      'slug' => $slug,
      'created_at' => date("Y-m-d H:i:s"),
      'state' => 'draft',
      'created_by' => $user
    );
    return $this->db->insert('fields', $data);
  }

  public function table_changes($table, $toInsert){
    $data = array(
      'table' => $table,
      'user' => $toInsert['user'],
      'col' => $toInsert['col'],
      'value_old' => $toInsert['value_old'],
      'value_new' => $toInsert['value_new']
    );
    $this->db->insert('table_changes', $data);
  }
}
