<?php
  class Jobs_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_stats_individual($famSocPro, $legislature){
      if ($legislature == legislature_current()) {
        $this->db->where('dateFin IS NULL');
      }
      $this->db->where('legislature', $legislature);
      $total = $this->db->count_all_results('deputes_all');

      $sql = 'SELECT A.*, fam.population
        FROM
        (
        	SELECT da.famSocPro, count(da.mpId) AS n
        	FROM deputes_all da
        	WHERE da.famSocPro = ? AND da.legislature = ?
        	GROUP BY da.famSocPro
        ) A
        LEFT JOIN famsocpro fam ON A.famSocPro = fam.famille
      ';

      $result = $this->db->query($sql, array($famSocPro, $legislature), 1)->row_array();
      $result['pct'] = round($result['n'] / $total * 100);
      return $result;
    }

    public function get_stats_all_mp($legislature){
      $sql = 'SELECT fam.famille, round(A.n / A.total * 100, 2) AS mps, A.n AS mpsCount, fam.population
        FROM
        (
        	SELECT CASE WHEN da.famSocPro IN ("", "Autres personnes sans activité professionnelle", "Sans profession déclarée") THEN "Autre" ELSE da.famSocPro END AS famSocPro, count(da.mpId) AS n,
        	(
        		SELECT COUNT(*) AS total
        		FROM deputes_last
        		WHERE legislature = ? AND dateFin IS NULL
        	) AS total
        	FROM deputes_all da
        	WHERE da.legislature = ? AND da.dateFin IS NULL
        	GROUP BY da.famSocPro
        ) A
        RIGHT JOIN famsocpro fam ON A.famSocPro = fam.famille
      ';

      $query = $this->db->query($sql, array($legislature, $legislature));
      return $query->result_array();
    }

    public function get_stats_jobs($legislature, $limit){
      $sql = 'SELECT A.job, count(A.job) as total
        FROM
        (
        SELECT
        	CASE
        		WHEN job LIKE "%vocat%" THEN "Avocat"
                WHEN job LIKE "Médecin%" THEN "Médecin"
                WHEN job LIKE "%Cadre%" THEN "Cadre"
                WHEN job LIKE "Professeur%" THEN "Professeur"
                WHEN job LIKE "%conférence%" THEN "Professeur"
                WHEN job LIKE "%Chef d\'entreprise%" THEN "Chef d\'entreprise"
                WHEN job LIKE "%Agriculteur%" THEN "Agriculteur"
                WHEN job LIKE "%Pharmacienne%" THEN "Pharmacien"
        		ELSE job
        	END AS job
        FROM deputes_last
        WHERE legislature = ? AND dateFin IS NULL AND job NOT IN ("Sans profession déclarée", "", "Autre profession libérale")
        ) A
        GROUP BY A.job
        ORDER BY count(A.job) DESC
        LIMIT ?
      ';

      $query = $this->db->query($sql, array($legislature, $limit));
      return $query->result_array();
    }

    public function get_mps($legislature){
      $where = array(
        'legislature' => $legislature,
        'dateFin' => NULL
      );
      $this->db->select('nameFirst, nameLast, libelleAbrev, job, famSocPro');
      $this->db->order_by('nameLast', 'ASC');
      $this->db->order_by('nameFirst', 'ASC');
      $query = $this->db->get_where('deputes_last', $where);
      return $query->result_array();
    }

    public function get_groups_category($category){
      $sql = 'SELECT *
        FROM
        (
        SELECT dl.groupeId, dl.libelle, dl.libelleAbrev, COUNT(mpId) AS n, ge.effectif AS total, ROUND(COUNT(mpId) / ge.effectif * 100) AS pct, ge.legislature
        FROM deputes_last dl
        LEFT JOIN groupes_effectif ge ON dl.groupeId = ge.organeRef
        WHERE dl.legislature = ? AND dl.active AND dl.famSocPro = ? AND dl.libelleAbrev != "NI"
        GROUP BY dl.groupeId
        ) A
        ORDER BY A.pct DESC
      ';

      $query = $this->db->query($sql, array(legislature_current(), $category));
      return $query->result_array();
    }

    public function get_groups_rose(){
      $sql = 'SELECT gs.organeRef, gs.rose_index, o.libelle, o.libelleAbrev AS libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
        FROM groupes_stats gs
        LEFT JOIN organes o ON gs.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON gs.organeRef = ge.organeRef
        WHERE o.legislature = 16 and o.dateFin IS NULL AND o.libelleAbrev != "NI"
        ORDER BY gs.rose_index DESC
      ';

      $query = $this->db->query($sql);
      return $query->result_array();
    }

    public function get_group_category_random($groupe_uid){
      $sql = 'SELECT A.famille, A.n, ROUND(A.n / ge.effectif * 100) AS pct, A.population
        FROM
        (
        SELECT fam.famille, fam.population, COUNT(dl.mpId) AS n
        FROM famsocpro fam
        LEFT JOIN deputes_last dl ON dl.famSocPro = fam.famille AND groupeId = ? AND dl.active AND dl.legislature = 15
        GROUP BY fam.famille
        ORDER BY rand()
        LIMIT 1
        ) A
        LEFT JOIN groupes_effectif ge ON ge.organeRef = ?
      ';

      $query = $this->db->query($sql, array($groupe_uid, $groupe_uid));
      return $query->row_array();
    }

    public function get_groups_representativite(){
        $sql = 'SELECT B.*
        FROM
        (
        	SELECT A.libelleAbrev, A.libelle, A.famSocPro, round(count(A.mpId) / ge.effectif * 100) AS pct
        	FROM
        	(
        		SElECT mpId, legislature, groupeId, libelle, libelleAbrev, active,
        		CASE
        			WHEN famSocPro = "" THEN "Sans profession déclarée"
        			WHEN famSocPro = "Autres personnes sans activité professionnelle" THEN "Sans profession déclarée"
        			ELSE famSocPro
        		END AS famSocPro
        		FROM deputes_last
        	) A
        	LEFT JOIN groupes_effectif ge ON A.groupeId = ge.organeRef
        	WHERE A.legislature = ? AND A.active AND A.libelleAbrev != "NI"
        	GROUP BY A.groupeId, A.famSocPro
        ) B
        ORDER BY B.libelleAbrev ASC';

      $query = $this->db->query($sql, legislature_current());
      return $query->result_array();
    }


  }
?>
