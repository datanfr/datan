<?php
class Admin_model extends CI_Model
{
  public function __construct()
  {
  }

  public function get_votes_datan()
  {
    $query = $this->db->query('
        SELECT vd.id, vd.voteNumero AS vote_id, vd.title, vd.slug, f.name AS category, vd.description, vd.state, vd.created_at, vd.modified_at, vd.created_by, vd.modified_by, u1.name AS created_by_name, u2.name AS modified_by_name
        FROM votes_datan vd
        LEFT JOIN users u1 ON vd.created_by = u1.id
        LEFT JOIN users u2 ON vd.modified_by = u2.id
        LEFT JOIN fields f ON vd.category = f.id
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
      'nuance' =>  $this->input->post('nuance'),
      'source' =>  $this->input->post('source'),
      'visible' => $this->input->post('visible') ? true : false
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
      'district' => $this->input->post('district'),
      'position' =>  $this->input->post('position'),
      'nuance' =>  $this->input->post('nuance'),
      'source' =>  $this->input->post('source'),
      'visible' => $this->input->post('visible') ? true : false
    );

    $this->db->set($data);
    $this->db->where('mpId', $this->input->post('mpId'));
    $this->db->where('election', $this->input->post('election'));
    $this->db->update('elect_deputes_candidats');
  }

  public function delete_candidat()
  {
    $this->db->where('mpId', $this->input->post('mpId'));
    $this->db->where('election', $this->input->post('election'));
    $this->db->delete('elect_deputes_candidats');
  }

  public function create_vote($user_id)
  {
    $slug = url_title($this->input->post('title'));

    $data = array(
      'title' => $this->input->post('title'),
      'slug' => $slug,
      'legislature' => $this->input->post('legislature'),
      'voteNumero' => $this->input->post('vote_id'),
      'vote_id' => "VTANR5L" . $this->input->post('legislature') . "V" . $this->input->post('vote_id'),
      'category' => $this->input->post('category'),
      'description' => $this->input->post('description'),
      'contexte' => strip_tags($this->input->post('contexte')),
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
    $query = $this->db->query('
        SELECT vd.*, f.name AS category_name
        FROM votes_datan vd
        LEFT JOIN fields f ON f.id = vd.category
        WHERE vd.id = "' . $id . '"
      ');

    return $query->row_array();
  }

  public function modify_vote($vote, $user_id)
  {
    $slug = url_title($this->input->post('title'));

    $data = array(
      'title' => $this->input->post('title'),
      'slug' => $slug,
      'category' => $this->input->post('category'),
      'description' => $this->input->post('description'),
      'contexte' => strip_tags($this->input->post('contexte')),
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

  public function get_votes_an_position($limit = false)
  {
    $queryString = "
        SELECT B.*,
          round((B.max_value - 0.5*((B.decomptePour + B.decompteContre + B.decompteAbs) - B.max_value)) / (B.decomptePour + B.decompteContre + B.decompteAbs),2) AS cohesion
        FROM
        (
        SELECT A.*, vi.voteId, vi.dateScrutin, vi.sortCode, vi.titre, vi.demandeur, vi.nombreVotants, round((vi.decomptePour*100)/vi.nombreVotants) AS pours, round((vi.decompteAbs*100)/vi.nombreVotants) AS abstentions, round((vi.decompteContre*100)/vi.nombreVotants) AS contres, vi.decomptePour, vi.decompteAbs, vi.decompteContre,
        CASE
          WHEN vi.decomptePour > vi.decompteContre AND vi.decomptePour > vi.decompteAbs THEN vi.decomptePour
            WHEN vi.decompteContre > vi.decompteAbs AND vi.decompteContre > vi.decomptePour THEN vi.decompteContre
            WHEN vi.decompteAbs > vi.decomptePour AND vi.decompteAbs > vi.decompteContre THEN vi.decompteAbs
            WHEN vi.decomptePour = vi.decompteContre THEN vi.decomptePour
            ELSE 0
        END AS max_value,
        CASE WHEN vd.title IS NOT NULL THEN 'vote_datan' ELSE NULL END AS vote_datan
        FROM
        (
        SELECT voteNumero, voteSort, legislature,
        SUM(CASE WHEN organeRef = 'PO723569' THEN positionGroupe ELSE NULL END) AS 'PO723569',
        SUM(CASE WHEN organeRef = 'PO730934' THEN positionGroupe ELSE NULL END) AS 'PO730934',
        SUM(CASE WHEN organeRef = 'PO730940' THEN positionGroupe ELSE NULL END) AS 'PO730940',
        SUM(CASE WHEN organeRef = 'PO730946' THEN positionGroupe ELSE NULL END) AS 'PO730946',
        SUM(CASE WHEN organeRef = 'PO730952' THEN positionGroupe ELSE NULL END) AS 'PO730952',
        SUM(CASE WHEN organeRef = 'PO730958' THEN positionGroupe ELSE NULL END) AS 'PO730958',
        SUM(CASE WHEN organeRef = 'PO730964' THEN positionGroupe ELSE NULL END) AS 'PO730964',
        SUM(CASE WHEN organeRef = 'PO730970' THEN positionGroupe ELSE NULL END) AS 'PO730970',
        SUM(CASE WHEN organeRef = 'PO767217' THEN positionGroupe ELSE NULL END) AS 'PO767217',
        SUM(CASE WHEN organeRef = 'PO744425' THEN positionGroupe ELSE NULL END) AS 'PO744425',
        SUM(CASE WHEN organeRef = 'PO758835' THEN positionGroupe ELSE NULL END) AS 'PO758835',
        SUM(CASE WHEN organeRef = 'PO759900' THEN positionGroupe ELSE NULL END) AS 'PO759900',
        SUM(CASE WHEN organeRef = 'PO765636' THEN positionGroupe ELSE NULL END) AS 'PO765636'
        FROM groupes_cohesion
        WHERE legislature = " . legislature_current() . "
        GROUP BY voteNumero
        ) A
        LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
        LEFT JOIN votes_datan vd ON A.voteNumero = vd.voteNumero AND A.legislature = vd.legislature
        ) B
        ORDER BY B.dateScrutin DESC";
    if ($limit) {
      $queryString .= ' LIMIT ' . $limit;
    }
    $query = $this->db->query($queryString);

    return $query->result_array();
  }

  public function get_votes_an_cohesion()
  {
    $query = $this->db->query("
      SELECT B.*,
      	round((B.max_value - 0.5*((B.decomptePour + B.decompteContre + B.decompteAbs) - B.max_value)) / (B.decomptePour + B.decompteContre + B.decompteAbs),2) AS cohesion
      FROM
      (
      SELECT A.*, vi.voteId, vi.dateScrutin, vi.sortCode, vi.titre, vi.demandeur, vi.nombreVotants, round((vi.decomptePour*100)/vi.nombreVotants) AS pours, round((vi.decompteAbs*100)/vi.nombreVotants) AS abstentions, round((vi.decompteContre*100)/vi.nombreVotants) AS contres, vi.decomptePour, vi.decompteAbs, vi.decompteContre,
      CASE
      	WHEN vi.decomptePour > vi.decompteContre AND vi.decomptePour > vi.decompteAbs THEN vi.decomptePour
          WHEN vi.decompteContre > vi.decompteAbs AND vi.decompteContre > vi.decomptePour THEN vi.decompteContre
          WHEN vi.decompteAbs > vi.decomptePour AND vi.decompteAbs > vi.decompteContre THEN vi.decompteAbs
          WHEN vi.decomptePour = vi.decompteContre THEN vi.decomptePour
          ELSE 0
      END AS max_value,
      CASE WHEN vd.title IS NOT NULL THEN 'vote_datan' ELSE NULL END AS vote_datan
      FROM
      (
      SELECT voteNumero, voteSort, legislature,
      SUM(CASE WHEN organeRef = 'PO723569' THEN cohesion ELSE NULL END) AS 'PO723569',
      SUM(CASE WHEN organeRef = 'PO730934' THEN cohesion ELSE NULL END) AS 'PO730934',
      SUM(CASE WHEN organeRef = 'PO730940' THEN cohesion ELSE NULL END) AS 'PO730940',
      SUM(CASE WHEN organeRef = 'PO730946' THEN cohesion ELSE NULL END) AS 'PO730946',
      SUM(CASE WHEN organeRef = 'PO730952' THEN cohesion ELSE NULL END) AS 'PO730952',
      SUM(CASE WHEN organeRef = 'PO730958' THEN cohesion ELSE NULL END) AS 'PO730958',
      SUM(CASE WHEN organeRef = 'PO730964' THEN cohesion ELSE NULL END) AS 'PO730964',
      SUM(CASE WHEN organeRef = 'PO730970' THEN cohesion ELSE NULL END) AS 'PO730970',
      SUM(CASE WHEN organeRef = 'PO767217' THEN cohesion ELSE NULL END) AS 'PO767217',
      SUM(CASE WHEN organeRef = 'PO744425' THEN cohesion ELSE NULL END) AS 'PO744425',
      SUM(CASE WHEN organeRef = 'PO758835' THEN cohesion ELSE NULL END) AS 'PO758835',
      SUM(CASE WHEN organeRef = 'PO759900' THEN cohesion ELSE NULL END) AS 'PO759900',
      SUM(CASE WHEN organeRef = 'PO765636' THEN cohesion ELSE NULL END) AS 'PO765636'
      FROM groupes_cohesion
      WHERE legislature = " . legislature_current() . "
      GROUP BY voteNumero
      ) A
      LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
      LEFT JOIN votes_datan vd ON A.voteNumero = vd.voteNumero AND A.legislature = vd.legislature
      ) B
      ORDER BY B.dateScrutin DESC
      ");

    return $query->result_array();
  }

  public function get_groupes_libelle($groupes)
  {
    foreach ($groupes as $groupe) {
      $query = $this->db->query('
          SELECT libelleAbrev, uid
          FROM organes
          WHERE uid = "' . $groupe . '"
      ');
      $result = $query->row_array();
      //print_r($result);

      $array[] = [
        "libelle" => $result["libelleAbrev"],
        "uid" => $result["uid"]
      ];
    }

    return $array;
  }

  public function get_votes_em_lost()
  {
    $query = $this->db->query('
      SELECT B.voteNumero, B.positionMajoritaire AS positionGroupe, B.sortCode AS sortVote, B.dateScrutin AS dateVote, B.pour, B.contre, B.abstention, gc.cohesion, B.titre, B.voteType, gc.cohesion, CASE WHEN vd.title IS NOT NULL THEN "vote_datan" ELSE NULL END AS vote_datan
      FROM
      (
      SELECT A.*, CASE WHEN A.positionMajoritaire = A.sortCode THEN 1 ELSE 0 END AS winning
      FROM
      (
      SELECT vi.voteId, vg.voteNumero, vg.legislature, vg.positionMajoritaire, vg.nombrePours AS pour, vg.nombreContres AS contre, vg.nombreAbstentions AS abstention, date_format(vi.dateScrutin, "%d %M %Y") AS dateScrutin, CASE WHEN vi.sortCode = "rejeté" THEN "contre" ELSE "pour" END AS sortCode, REPLACE(vi.titre, "n?", "n°") AS titre, vi.voteType
      FROM votes_groupes vg
      LEFT JOIN votes_info vi ON vg.voteNumero = vi.voteNumero AND vg.legislature = vi.legislature
      WHERE vg.organeRef = "PO730964"
      ) A
      ) B
      LEFT JOIN groupes_cohesion gc ON B.voteNumero = gc.voteNumero AND gc.organeRef = "PO730964"
      LEFT JOIN votes_datan vd ON B.voteNumero = vd.voteNumero AND B.legislature = vd.legislature
      WHERE B.winning = 0
      ORDER BY B.voteNumero DESC
      ');

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
    $slug = skip_accents($this->input->post('name'));
    $slug = mb_strtolower($slug);
    $slug = url_title($slug);


    $data = array(
      'name' => $this->input->post('name'),
      'slug' => $slug,
      'created_at' => date("Y-m-d H:i:s"),
      'state' => 'draft',
      'created_by' => $user
    );
    return $this->db->insert('fields', $data);
  }

  public function get_classement_loyaute_group($libelle)
  {
    $query = $this->db->query('
        SELECT @s:=@s+1 AS "ranking", B.*
        FROM
        (
        SELECT ROUND(cl.score * 100, 1) AS score, cl.votesN, a.average, da.nameLast, da.nameFirst, da.mpId, da.libelle, da.libelleAbrev
        FROM class_loyaute cl
        LEFT JOIN deputes_all da ON da.mpId = cl.mpId AND da.legislature = cl.legislature
        JOIN (
        	SELECT ROUND(AVG(t2.score) * 100, 1) AS average, libelleAbrev
            FROM class_loyaute t2
            LEFT JOIN deputes_all da2 ON da2.mpId = t2.mpId AND da2.legislature = t2.legislature
            WHERE t2.legislature = ' . legislature_current() . ' AND da2.libelleAbrev = \'' . $libelle . '\' AND da2.dateFin IS NULL
        ) a ON a.libelleAbrev = da.libelleAbrev
        WHERE cl.legislature = ' . legislature_current() . ' AND da.libelleAbrev = \'' . $libelle . '\' AND da.dateFin IS NULL
        ORDER BY cl.score DESC
        ) B,
        (SELECT @s:= 0) AS s
      ');

    return $query->result_array();
  }
}
