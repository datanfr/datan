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
    $this->db->join('users u1', 'u1.id = votes_datan.created_by', 'left');
    $this->db->join('users u2', 'u2.id = votes_datan.modified_by', 'left');
    $this->db->select('votes_datan.*, fields.name AS category_name, readings.name AS reading_name, u1.name AS created_by_name, u2.name AS modified_by_name');
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

  /**
   * Liste les votes de type amendement de la dernière législature,
   * avec résumé IA, score de simplicité et statut de décryptage.
   *
   * @param string $sort       Colonne de tri : 'date'|'votants'|'disparite'|'simplicite'|'decrypte'
   * @param string $direction  'ASC'|'DESC'
   * @param array  $filters    ['period' => '7'|'30'|'90'|'180'|'365'|'all',
   *                            'date_start' => 'YYYY-MM-DD',
   *                            'date_end'   => 'YYYY-MM-DD']
   */
  public function get_amendements_list($sort = 'date', $direction = 'DESC', $filters = array())
  {
    $allowed_sorts = array(
      'date'       => 'vi.dateScrutin',
      'votants'    => 'vi.nombreVotants',
      'disparite'  => 'disparite',
      'interet'    => 'interet',
      'simplicite' => 'aia.simplicite_ia',
      'decrypte'   => 'decrypte',
    );

    $order_col = isset($allowed_sorts[$sort]) ? $allowed_sorts[$sort] : 'vi.dateScrutin';
    $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

    // Dernière législature disponible
    $last_leg = $this->db->query('SELECT MAX(legislature) AS leg FROM votes_info')->row_array();
    $legislature = $last_leg['leg'] ?? 17;

    // Filtres date
    $where_date = '';
    $params     = array($legislature);

    $period     = isset($filters['period'])     ? (string)$filters['period']     : 'all';
    $date_start = isset($filters['date_start']) ? (string)$filters['date_start'] : '';
    $date_end   = isset($filters['date_end'])   ? (string)$filters['date_end']   : '';

    $is_valid_date = function ($d) {
      return $d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
    };

    if ($is_valid_date($date_start) || $is_valid_date($date_end)) {
      if ($is_valid_date($date_start)) {
        $where_date .= ' AND vi.dateScrutin >= ?';
        $params[]    = $date_start;
      }
      if ($is_valid_date($date_end)) {
        $where_date .= ' AND vi.dateScrutin <= ?';
        $params[]    = $date_end;
      }
    } elseif (in_array($period, array('7', '30', '90', '180', '365'), true)) {
      $where_date = ' AND vi.dateScrutin >= DATE_SUB(CURDATE(), INTERVAL ? DAY)';
      $params[]   = (int)$period;
    }

    // Filtre "cacher les reviewed"
    $where_reviewed = '';
    if (!empty($filters['hide_reviewed'])) {
      $where_reviewed = ' AND COALESCE(aia.reviewed, 0) = 0';
    }

    $sql = "
      SELECT
        vi.voteNumero,
        vi.legislature,
        vi.dateScrutin,
        date_format(vi.dateScrutin, '%d/%m/%Y') AS dateScrutinFR,
        vi.nombreVotants,
        vi.decomptePour AS pour,
        vi.decompteContre AS contre,
        vi.decompteAbs AS abstention,
        CASE
          WHEN vi.nombreVotants > 0
          THEN ROUND(ABS(vi.decomptePour - vi.decompteContre) * 100 / vi.nombreVotants, 1)
          ELSE 0
        END AS disparite,
        CASE
          WHEN vi.nombreVotants > 0
          THEN ROUND(
            LEAST(vi.nombreVotants / 250, 1)
            * (1 - ABS(vi.decomptePour - vi.decompteContre) / vi.nombreVotants)
            * 100, 1)
          ELSE 0
        END AS interet,
        aia.titre_ia,
        aia.resume_ia,
        aia.simplicite_ia,
        COALESCE(aia.reviewed, 0) AS reviewed,
        COALESCE(vd.title, vi.titre, vi.seanceRef) AS titre
      FROM votes_info vi
      LEFT JOIN amendements_ia aia
        ON aia.voteNumero = vi.voteNumero AND aia.legislature = vi.legislature
      LEFT JOIN votes_datan vd
        ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
      WHERE vi.voteType IN ('amendement', 'les amen')
        AND vi.legislature = ?
        AND vd.id IS NULL
        $where_date
        $where_reviewed
      ORDER BY $order_col $direction
    ";

    return $this->db->query($sql, $params)->result_array();
  }

  /**
   * Marque un amendement comme reviewed (ou non).
   * Crée la ligne dans amendements_ia si elle n'existe pas encore.
   *
   * @return bool true si la requête a réussi, false en cas d'erreur SQL
   */
  public function set_amendement_reviewed($legislature, $voteNumero, $reviewed)
  {
    $legislature = (int) $legislature;
    $voteNumero  = (string) $voteNumero;
    $reviewed    = $reviewed ? 1 : 0;

    $ok = $this->db->query(
      "INSERT INTO amendements_ia (legislature, voteNumero, reviewed)
       VALUES (?, ?, ?)
       ON DUPLICATE KEY UPDATE reviewed = VALUES(reviewed), updated_at = NOW()",
      array($legislature, $voteNumero, $reviewed)
    );

    return $ok !== FALSE;
  }
}
