<?php
  class Stats_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_ranking_age(){
      $sql = 'SELECT @s:=@s+1 AS "rank",
        da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.legislature, da.img, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.mpId, da.dptSlug, da.age, da.couleurAssociee,
        CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter
        FROM deputes_all da,
        (SELECT @s:= 0) AS s
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ORDER BY age DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_age_mean($legislature){
      if ($legislature == legislature_current()) {
        $sql = 'SELECT ROUND(AVG(A.age), 1) AS mean
          FROM
          (
            SELECT d.age
            FROM deputes_last d
            WHERE d.legislature = ? AND d.dateFin IS NULL
          ) A
        ';
        $result = $this->db->query($sql, $legislature)->row_array();
      } else {
        $sql = 'SELECT ROUND(AVG(A.age), 1) AS mean
          FROM
          (
            SELECT d.birthDate, l.dateFin,
            YEAR(l.dateFin) - YEAR(d.birthDate) - CASE WHEN MONTH(l.dateFin) < MONTH(d.birthDate) OR (MONTH(l.dateFin) = MONTH(d.birthDate) AND DAY(l.dateFin) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
            FROM deputes_all da
            LEFT JOIN deputes d ON d.mpId = da.mpId
            LEFT JOIN legislature l ON l.legislatureNumber = ?
            WHERE da.legislature = ?
        ) A';
        $result = $this->db->query($sql, array($legislature, $legislature))->row_array();
      }

      return $result['mean'];
    }

    public function get_groups_women(){
      $sql = 'SELECT @s:=@s+1 AS "rank", B.*
      FROM
      (
        SELECT A.*,
        ROUND(female / n * 100) AS pct,
        o.uid AS organeRef, o.couleurAssociee, o.legislature, o.uid, ge.effectif
        FROM
        (
          SELECT libelle, libelleAbrev, COUNT(mpId) AS n, groupeId,
          SUM(if(civ = "Mme", 1, 0)) AS female
          FROM deputes_all
          WHERE libelleAbrev != "NI" AND legislature = ? AND dateFin IS NULL
          GROUP BY libelle
        ) A
        LEFT JOIN organes o ON A.groupeId = o.uid
        LEFT JOIN groupes_effectif ge ON A.groupeId = ge.organeRef
      ) B,
      (SELECT @s:= 0) AS s
      ORDER BY B.pct DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_groups_women_more(){
      $sql = 'SELECT @s:=@s+1 AS "rank", B.*
        FROM
        (
        SELECT A.*,
        ROUND(female / n * 100, 2) AS pct
        FROM
        (
        SELECT libelle, libelleAbrev, COUNT(mpId) AS n,
        SUM(if(civ = "Mme", 1, 0)) AS female
        FROM deputes_all
        WHERE libelleAbrev != "NI" AND legislature = ? AND dateFin IS NULL
        GROUP BY libelle
        ) A
        ) B,
        (SELECT @s:= 0) AS s
        ORDER BY B.pct DESC
        LIMIT 3
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_groups_women_less(){
      $sql = 'SELECT D.*
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
        FROM deputes_all
        WHERE libelleAbrev != "NI" AND legislature = ? AND dateFin IS NULL
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
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_mps_loyalty($legislature){
      $sql = 'SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
        SELECT cl.mpId, ROUND(cl.score*100) AS score, cl.votesN, da.civ, da.nameLast, da.nameFirst, da.nameUrl, da.legislature, da.img, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.dptSlug, da.couleurAssociee, da.departementNom, da.departementCode,
        CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter
        FROM class_loyaute cl
        LEFT JOIN deputes_all da ON cl.mpId = da.mpId AND cl.legislature = da.legislature
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.score DESC, A.votesN DESC
      ';
      return $this->db->query($sql, $legislature)->result_array();
    }

    public function get_loyalty_mean($legislature){
      $sql = 'SELECT ROUND(AVG(score)*100) AS mean
        FROM class_loyaute
        WHERE legislature = ?
      ';
      return $this->db->query($sql, $legislature)->row_array();
    }

    public function get_groups_age(){
      $sql = 'SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
          SELECT gs.organeRef, ROUND(gs.age) AS age, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
          FROM groupes_stats gs
          LEFT JOIN organes o ON o.uid = gs.organeRef
          LEFT JOIN groupes_effectif ge ON ge.organeRef  = gs.organeRef
          WHERE dateFin IS NULL AND o.legislature = ? AND o.libelleAbrev != "NI"
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.age DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_groups_cohesion(){
      $query = $this->db->query('SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
          SELECT cg.organeRef, cg.value AS cohesion, cg.active, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
          FROM class_groups cg
          LEFT JOIN organes o ON cg.organeRef = o.uid
          LEFT JOIN groupes_effectif ge ON ge.organeRef  = cg.organeRef
          WHERE cg.active = 1 AND cg.stat = "cohesion"
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.cohesion DESC
      ');

      return $query->result_array();
    }

    public function get_mps_participation(){
      $sql = 'SELECT cp.*, da.nameFirst, da.nameLast, da.civ, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.dptSlug, da.nameUrl, da.couleurAssociee, da.departementNom, da.departementCode
        FROM class_participation cp
        LEFT JOIN deputes_last da ON cp.mpId = da.mpId AND da.legislature = cp.legislature
        WHERE da.active AND cp.legislature = ? AND cp.votesN > 5
        ORDER BY cp.score DESC, cp.votesN DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_mps_participation_solennels($legislature){
      $sql = 'SELECT cp.*, da.nameFirst, da.nameLast, da.civ, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.dptSlug, da.nameUrl, da.couleurAssociee, da.img,
        CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter
        FROM class_participation_solennels cp
        LEFT JOIN deputes_last da ON cp.mpId = da.mpId AND da.legislature = cp.legislature
        WHERE da.active AND cp.legislature = ?
        ORDER BY cp.score DESC, cp.votesN DESC
      ';
      $array = $this->db->query($sql, $legislature)->result_array();
      $i = 1;
      foreach ($array as $key => $value) {
        $array[$key]["rank"] = $i;
        $i++;
      }
      return $array;
    }

    public function get_mps_participation_commission($legislature){
      $sql = 'SELECT cp.mpId, cp.score, cp.votesN, da.nameFirst, da.nameLast, da.civ, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.dptSlug, da.nameUrl, da.couleurAssociee, da.departementNom, da.departementCode, o.libelleAbrege AS commission
        FROM class_participation_commission cp
        LEFT JOIN deputes_all da ON cp.mpId = da.mpId
        LEFT JOIN mandat_secondaire ms ON cp.mpId = ms.mpId
        LEFT JOIN organes o ON ms.organeRef = o.uid
        WHERE da.legislature = ? AND cp.active = 1 AND ms.typeOrgane = "COMPER" AND ms.codeQualite = "Membre" AND ms.dateFin IS NULL
        ORDER BY cp.score DESC, cp.votesN DESC
      ';
      return $this->db->query($sql, $legislature)->result_array();
    }

    public function get_mps_participation_mean($legislature){
      $sql = 'SELECT ROUND(AVG(score) * 100) AS mean
        FROM class_participation
        WHERE legislature = ?
      ';
      return $this->db->query($sql, $legislature)->row_array();
    }

    public function get_mps_participation_solennels_mean($legislature){
      $sql = 'SELECT ROUND(AVG(score) * 100) AS mean
        FROM class_participation_solennels
        WHERE legislature = ?
      ';
      return $this->db->query($sql, $legislature)->row_array();
    }

    public function get_mps_participation_commission_mean($legislature){
      $sql = 'SELECT ROUND(AVG(score) * 100) AS mean
        FROM class_participation_commission
        WHERE legislature = ?
      ';
      return $this->db->query($sql, $legislature)->row_array();
    }

    public function get_groups_participation(){
      $query = $this->db->query('SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
        SELECT cg.organeRef, cg.value, round(cg.value * 100) AS participation, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
        FROM class_groups cg
        LEFT JOIN organes o ON cg.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON cg.organeRef = ge.organeRef
        WHERE cg.active = 1 AND cg.stat = "participation"
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.value DESC
      ');

      return $query->result_array();
    }

    public function get_groups_participation_commission(){
      $query = $this->db->query('SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
        SELECT cg.organeRef, cg.value, round(cg.value * 100) AS participation, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
        FROM class_groups cg
        LEFT JOIN organes o ON cg.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON cg.organeRef = ge.organeRef
        WHERE cg.active = 1 AND cg.stat = "participationCommission"
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.value DESC
      ');

      return $query->result_array();
    }

    public function get_groups_participation_sps(){
      $query = $this->db->query('SELECT @s:=@s+1 AS "rank", A.*
        FROM
        (
        SELECT cg.organeRef, cg.value, round(cg.value * 100) AS participation, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, ge.effectif
        FROM class_groups cg
        LEFT JOIN organes o ON cg.organeRef = o.uid
        LEFT JOIN groupes_effectif ge ON cg.organeRef = ge.organeRef
        WHERE cg.active = 1 AND cg.stat = "participationSPS"
        ) A,
        (SELECT @s:= 0) AS s
        ORDER BY A.value DESC
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
        ),
        array(
          "term" => 16,
          "year_start" => "2022",
          "year_end" => "2027",
          "pct" => 37
        )
      );

      return $array;
    }

  }
?>
