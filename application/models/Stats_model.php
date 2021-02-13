<?php
  class Stats_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_mps_oldest($limit = 3){
      $query = $this->db->query('
        SELECT A.*
        FROM
        (
        	SELECT @s:=@s+1 AS "rank",
        	da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.libelle, da.libelleAbrev, da.mpId, da.dptSlug, da.couleurAssociee, da.departementNom, da.departementCode,
        	YEAR(current_timestamp()) - YEAR(da.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(da.birthDate) OR (MONTH(current_timestamp()) = MONTH(da.birthDate) AND DAY(current_timestamp()) < DAY(da.birthDate)) THEN 1 ELSE 0 END AS age
        	FROM deputes_actifs da,
        	(SELECT @s:= 0) AS s
        	ORDER BY age DESC
        ) A
        LIMIT '.$limit.'
      ');
      if ($limit == 1) {
        return $query->row_array();
      } else {
        return $query->result_array();
      }
    }

    public function get_mps_youngest($limit = 3){
      $query = $this->db->query('
        SELECT B.*
        FROM
        (
        SELECT A.*
        FROM
        (
        	SELECT @s:=@s+1 AS "rank",
        	da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.libelle, da.libelleAbrev, da.mpId, da.dptSlug, da.couleurAssociee, da.departementNom, da.departementCode,
        	YEAR(current_timestamp()) - YEAR(da.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(da.birthDate) OR (MONTH(current_timestamp()) = MONTH(da.birthDate) AND DAY(current_timestamp()) < DAY(da.birthDate)) THEN 1 ELSE 0 END AS age
        	FROM deputes_actifs da,
        	(SELECT @s:= 0) AS s
        	ORDER BY age DESC
        ) A
        ORDER BY rank DESC
        LIMIT '.$limit.'
        ) B
        ORDER BY rank
      ');
      if ($limit == 1) {
        return $query->row_array();
      } else {
        return $query->result_array();
      }
    }

    public function get_ranking_age(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank",
        da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.libelle, da.libelleAbrev, da.mpId, da.dptSlug, da.age
        FROM deputes_actifs da,
        (SELECT @s:= 0) AS s
        ORDER BY age DESC
      ');
      return $query->result_array();
    }

    public function get_age_mean(){
      $query = $this->db->query('
        SELECT ROUND(AVG(A.age), 1) AS mean
        FROM
        (
        SELECT YEAR(current_timestamp()) - YEAR(da.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(da.birthDate) OR (MONTH(current_timestamp()) = MONTH(da.birthDate) AND DAY(current_timestamp()) < DAY(da.birthDate)) THEN 1 ELSE 0 END AS age
        FROM deputes_actifs da
        ) A
      ');

      return $query->row_array();
    }

    public function get_groups_women(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank", B.*
        FROM
        (
          SELECT A.*,
          ROUND(female / n * 100) AS pct,
          o.couleurAssociee, ge.effectif
          FROM
          (
            SELECT libelle, libelleAbrev, COUNT(mpId) AS n, groupeId,
            SUM(if(civ = "Mme", 1, 0)) AS female
            FROM deputes_actifs
            WHERE libelleAbrev != "NI"
            GROUP BY libelle
          ) A
          LEFT JOIN organes o ON A.groupeId = o.uid
          LEFT JOIN groupes_effectif ge ON A.groupeId = ge.organeRef
        ) B,
        (SELECT @s:= 0) AS s
        ORDER BY B.pct DESC
      ');

      return $query->result_array();
    }

    public function get_groups_women_more(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank", B.*
        FROM
        (
        SELECT A.*,
        ROUND(female / n * 100, 2) AS pct
        FROM
        (
        SELECT libelle, libelleAbrev, COUNT(mpId) AS n,
        SUM(if(civ = "Mme", 1, 0)) AS female
        FROM deputes_actifs
        WHERE libelleAbrev != "NI"
        GROUP BY libelle
        ) A
        ) B,
        (SELECT @s:= 0) AS s
        ORDER BY B.pct DESC
        LIMIT 3
      ');

      return $query->result_array();
    }

    public function get_groups_women_less(){
      $query = $this->db->query('
        SELECT D.*
        FROM
        (
        SELECT C.*
        FROM
        (
        SELECT @s:=@s+1 AS "rank", B.*
        FROM
        (
        SELECT A.*,
        ROUND(female / n * 100, 2) AS pct
        FROM
        (
        SELECT libelle, libelleAbrev, COUNT(mpId) AS n,
        SUM(if(civ = "Mme", 1, 0)) AS female
        FROM deputes_actifs
        WHERE libelleAbrev != "NI"
        GROUP BY libelle
        ) A
        ) B,
        (SELECT @s:= 0) AS s
        ORDER BY B.pct DESC
        ) C
        ORDER BY C.rank DESC
        LIMIT 3
        ) D
        ORDER BY D.rank ASC
      ');

      return $query->result_array();
    }

    public function get_mps_loyalty(){
      $query = $this->db->query('
        SELECT cl.*, ROUND(cl.score*100) AS score, da.civ, da.nameLast, da.nameFirst, da.nameUrl, da.libelle, da.libelleAbrev, da.dptSlug, da.couleurAssociee, da.departementNom, da.departementCode
        FROM class_loyaute cl
        LEFT JOIN deputes_actifs da ON cl.mpId = da.mpId
      ');

      return $query->result_array();
    }

    public function get_mps_loyalty_more(){
      $query = $this->db->query('
        SELECT A.classement AS rank, ROUND(A.score * 100) AS score, da.nameLast, da.nameFirst, da.nameUrl, da.libelle, da.libelleAbrev, da.groupeId, da.dptSlug
        FROM
        (
        SELECT *
        FROM class_loyaute
        LIMIT 3
        ) A
        LEFT JOIN deputes_actifs da ON A.mpId = da.mpId
        ORDER BY classement ASC
      ');

      return $query->result_array();
    }

    public function get_mps_loyalty_less(){
      $query = $this->db->query('
        SELECT A.classement AS rank, ROUND(A.score * 100) AS score, da.nameLast, da.nameFirst, da.nameUrl, da.libelle, da.libelleAbrev, da.groupeId, da.dptSlug
        FROM
        (
        SELECT *
        FROM class_loyaute
        ORDER BY classement DESC
        LIMIT 3
        ) A
        LEFT JOIN deputes_actifs da ON A.mpId = da.mpId
        ORDER BY classement ASC
      ');

      return $query->result_array();
    }

    public function get_loyalty_mean(){
      $query = $this->db->query('
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_loyaute
      ');

      return $query->row_array();
    }

    public function get_groups_age(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
          SELECT gs.organeRef, ROUND(gs.age) AS age, o.libelle, o.libelleAbrev, o.couleurAssociee, ge.effectif
          FROM groupes_stats gs
          LEFT JOIN organes o ON o.uid = gs.organeRef
          LEFT JOIN groupes_effectif ge ON ge.organeRef  = gs.organeRef
          WHERE dateFin IS NULL
          ORDER BY gs.age DESC
        ) A,
        (SELECT @s:= 0) AS s
      ');

      return $query->result_array();
    }

    public function get_groups_cohesion(){
      $query = $this->db->query('
      SELECT @s:=@s+1 AS "rank", A.*
      FROM
      (
        SELECT gc.*, o.libelle, o.libelleAbrev, o.couleurAssociee, ge.effectif
        FROM class_groups_cohesion gc
        LEFT JOIN organes o ON gc.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON ge.organeRef  = gc.organeRef
      ) A,
      (SELECT @s:= 0) AS s
      ORDER BY A.cohesion DESC
      ');

      return $query->result_array();
    }

    public function get_mps_participation(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
          SELECT cp.*, da.nameFirst, da.nameLast, da.civ, da.libelle, da.libelleAbrev, da.dptSlug, da.nameUrl, da.couleurAssociee, da.departementNom, da.departementCode
          FROM class_participation cp
          LEFT JOIN deputes_actifs da ON cp.mpId = da.mpId
          WHERE votesN >= 100
        ) A,
        (SELECT @s:= 0) AS s
      ');

      return $query->result_array();
    }

    public function get_mps_participation_commission(){
      $query = $this->db->query('
        SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
          SELECT cp.*, da.nameFirst, da.nameLast, da.civ, da.libelle, da.libelleAbrev, da.dptSlug, da.nameUrl, da.couleurAssociee, da.departementNom, da.departementCode, o.libelleAbrege AS commission
          FROM class_participation_commission cp
          LEFT JOIN deputes_actifs da ON cp.mpId = da.mpId
          LEFT JOIN mandat_secondaire ms ON cp.mpId = ms.mpId
          LEFT JOIN organes o ON ms.organeRef = o.uid
          WHERE ms.dateFin IS NULL AND ms.typeOrgane = "COMPER" AND ms.codeQualite = "Membre"
          ORDER BY cp.classement ASC
        ) A,
        (SELECT @s:= 0) AS s
      ');

      return $query->result_array();
    }

    public function get_mps_participation_mean(){
      $query = $this->db->query('
        SELECT ROUND(AVG(score) * 100) AS mean
        FROM class_participation
      ');

      return $query->row_array('');
    }

    public function get_mps_participation_commission_mean(){
      $query = $this->db->query('
        SELECT ROUND(AVG(score) * 100) AS mean
        FROM class_participation_commission
      ');

      return $query->row_array('');
    }

    public function get_groups_participation(){
      $query = $this->db->query('
        SELECT cp.classement AS rank, cp.organeRef, ROUND(cp.participation * 100) AS participation, o.libelle, o.libelleAbrev, o.couleurAssociee, ge.effectif
        FROM class_groups_participation cp
        LEFT JOIN organes o ON cp.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON cp.organeRef = ge.organeRef
        ORDER BY cp.participation DESC
      ');

      return $query->result_array();
    }

    public function get_women_history(){
      $array = array(
        array(
          "term" => 8,
          "year_start" => "1986",
          "year_end" => "1988",
          "pct" => 6
        ),
        array(
          "term" => 9,
          "year_start" => "1988",
          "year_end" => "1993",
          "pct" => 7
        ),
        array(
          "term" => 10,
          "year_start" => "1993",
          "year_end" => "1997",
          "pct" => 7
        ),
        array(
          "term" => 11,
          "year_start" => "1997",
          "year_end" => "2002",
          "pct" => 13
        ),
        array(
          "term" => 12,
          "year_start" => "2002",
          "year_end" => "2007",
          "pct" => 13
        ),
        array(
          "term" => 13,
          "year_start" => "2007",
          "year_end" => "2012",
          "pct" => 20
        ),
        array(
          "term" => 14,
          "year_start" => "2012",
          "year_end" => "2017",
          "pct" => 27
        ),
        array(
          "term" => 15,
          "year_start" => "2017",
          "year_end" => "2022",
          "pct" => 41
        )
      );

      return $array;
    }

  }
?>
