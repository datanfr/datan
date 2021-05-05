<?php
  class Jobs_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_stats_individual($famSocPro, $legislature){
      $sql = 'SELECT A.*, round(A.n / A.total * 100, 2) AS pct
        FROM
        (
        	SELECT da.famSocPro, count(da.mpId) AS n,
        	(
        		SELECT COUNT(*) AS total
        		FROM deputes_last
        		WHERE legislature = 15 AND dateFin IS NULL AND famSocPro NOT IN ("", "Autres personnes sans activité professionnelle", "Sans profession déclarée")
        	) AS total
        	FROM deputes_all da
        	WHERE da.famSocPro = ? AND da.legislature = ? AND da.dateFin IS NULL
        	GROUP BY da.famSocPro
        ) A
      ';

      $query = $this->db->query($sql, array($famSocPro, $legislature), 1);
      return $query->row_array();
    }

    public function get_stats_all_mp($legislature){
      $sql = 'SELECT fam.famille, round(A.n / A.total * 100, 2) AS mps, fam.population
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
        RIGHT JOIN famSocPro fam ON A.famSocPro = fam.famille
      ';

      $query = $this->db->query($sql, array($legislature, $legislature));
      return $query->result_array();
    }


  }
?>
