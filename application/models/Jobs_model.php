<?php
  class Jobs_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_stats_individual($famSocPro, $legislature){
      if ($legislature == legislature_current()) {
        $dateFin = "AND da.dateFin IS NULL";
      } else {
        $dateFin = "";
      }

      $sql = 'SELECT A.*, round(A.n / A.total * 100, 2) AS pct, fam.population
        FROM
        (
        	SELECT da.famSocPro, count(da.mpId) AS n,
        	(
        		SELECT COUNT(*) AS total
        		FROM deputes_last
        		WHERE legislature = 15 AND dateFin IS NULL AND famSocPro NOT IN ("", "Autres personnes sans activité professionnelle", "Sans profession déclarée")
        	) AS total
        	FROM deputes_all da
        	WHERE da.famSocPro = ? AND da.legislature = ? ?
        	GROUP BY da.famSocPro
        ) A
        LEFT JOIN famsocpro fam ON A.famSocPro = fam.famille
      ';

      $query = $this->db->query($sql, array($famSocPro, $legislature, $dateFin), 1);
      return $query->row_array();
    }

    public function get_stats_all_mp($legislature){
      $sql = 'SELECT fam.famille, round(A.n / A.total * 100, 2) AS mps, A.n AS mpsCount, fam.population
        FROM
        (
        	SELECT da.famSocPro, count(da.mpId) AS n,
        	(
        		SELECT COUNT(*) AS total
        		FROM deputes_last
        		WHERE legislature = ? AND dateFin IS NULL AND famSocPro NOT IN ("", "Autres personnes sans activité professionnelle", "Sans profession déclarée")
        	) AS total
        	FROM deputes_all da
        	WHERE da.famSocPro NOT IN ("", "Autres personnes sans activité professionnelle", "Sans profession déclarée") AND da.legislature = ? AND da.dateFin IS NULL
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
        SELECT dl.groupeId, dl.libelle, dl.libelleAbrev, COUNT(mpId) AS n, ge.effectif AS total, ROUND(COUNT(mpId) / ge.effectif * 100) AS pct
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


  }
?>
