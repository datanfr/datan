<?php
  class Deputes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_deputes_all($legislature, $active, $departement) {

      if (!is_null($departement)) {
        $this->db->where('dptSlug', $departement);
      }

      if ($active === TRUE) {
        $this->db->where('dateFin IS NULL');
      } elseif ($active === FALSE) {
        $this->db->where('dateFin IS NOT NULL');
      }

      $this->db->where('legislature', $legislature);
      $this->db->order_by('nameLast ASC, nameFirst ASC');
      $query = $this->db->get('deputes_all');

      return $query->result_array();
    }

    public function get_infos($id){
      $query = $this->db->get_where('deputes', array('mpId' => $id), 1);
      return $query->row_array();
    }

    public function get_historique($id){
      $sql = 'SELECT d.nameFirst, d.nameLast, mg.mpId AS id, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle
        FROM mandat_groupe mg
        LEFT JOIN deputes d ON mg.mpId = d.mpId
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE mg.legislature = 15 AND mg.mpId = ?
        ORDER BY mg.dateDebut DESC
      ';
      $query = $this->db->query($sql, $id);

      return $query->result_array();
    }

    public function get_n_deputes_inactive(){
      $this->db->where('legislature = 15');
      $this->db->where('dateFin IS NOT NULL');
      return $this->db->count_all_results('deputes_all');
    }

    public function get_deputes_gender($legislature){
      $this->db->select('COUNT(civ) AS n, ROUND(COUNT(civ)*100/577) AS percentage');
      $this->db->select('CASE WHEN civ = "M." THEN "male" WHEN civ = "Mme" THEN "female" END AS gender', FALSE);
      $this->db->where('legislature', $legislature);
      if ($legislature == legislature_current()) {
        $this->db->where('dateFin IS NULL');
      }
      $query = $this->db->group_by('civ');
      $query = $this->db->get('deputes_all');

      return $query->result_array();
    }

    public function get_groupes_inactifs(){
      $this->db->select('libelle, libelleAbrev');
      $this->db->where('legislature', legislature_current());
      $this->db->where('dateFin IS NOT NULL');
      $this->db->group_by('groupeId');
      $this->db->order_by('libelle', 'ASC');
      $this->db->having('libelle IS NOT NULL');
      $query = $this->db->get('deputes_all');

      return $query->result_array();
    }

    public function get_depute_by_nameUrl($nameUrl) {
      $whereQuery = array(
        'nameUrl' => $nameUrl
      );

      $query = $this->db->get_where('deputes_last', $whereQuery, 1);
      return $query->row_array();
    }

    public function get_depute_by_mpId($mpId) {
      $whereQuery = array(
        'mpId' => $mpId
      );

      $query = $this->db->get_where('deputes_last', $whereQuery, 1);
      return $query->row_array();
    }

    public function get_depute_individual($nameUrl, $dpt){
      $sql = 'SELECT
        dl.*, dl.libelle_2 AS dptLibelle2,
        substr(dl.mpId, 3) AS idImage,
        h.mandatesN, h.mpLength, h.lengthEdited,
        dc.facebook, dc.twitter, dc.website, dc.mailAn,
        date_format(dl.dateFin, "%d %M %Y") AS dateFinMpFR,
        d.birthDate, d.birthCity
        FROM deputes_last dl
        LEFT JOIN history_per_mps_average h ON dl.mpId = h.mpId
        LEFT JOIN deputes_contacts dc ON dl.mpId = dc.mpId
        LEFT JOIN deputes d ON dl.mpId = d.mpId
        WHERE dl.nameUrl = ? AND dl.dptSlug = ?
        LIMIT 1
      ';
      $query = $this->db->query($sql, array($nameUrl, $dpt));

      return $query->row_array();
    }

    public function depute_group_president($depute_uid, $groupe_id){
      $whereQuery = array(
        'mpId' => $depute_uid,
        'organeRef' => $groupe_id,
        'preseance' => 1
      );
      $query = $this->db->get_where('mandat_groupe', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_commission_parlementaire($depute_uid){
      $sql = 'SELECT
        ms.libQualiteSex AS commissionCodeQualiteGender, o.libelle AS commissionLibelle, o.libelleAbrege AS commissionAbrege
        FROM mandat_secondaire ms
        LEFT JOIN organes o ON ms.organeRef = o.uid
        WHERE ms.mpId = ? AND ms.typeOrgane = "COMPER" AND ms.dateFin IS NULL
        AND ms.preseance IN (
          SELECT min(t1.preseance)
          FROM mandat_secondaire t1
          WHERE t1.mpId = ms.mpId AND typeOrgane = "COMPER" AND legislature = 15
      )';
      $query = $this->db->query($sql, $depute_uid, 1);

      return $query->row_array();
    }

    public function get_political_party($depute_uid){
      $where = array(
        'ms.mpId' => $depute_uid,
        'ms.typeOrgane' => 'PARPOL',
        'ms.dateFin' => NULL
      );

      $this->db->select('o.libelle, o.libelleAbrev');
      $this->db->from('mandat_secondaire ms');
      $this->db->join('organes o', 'ms.organeRef = o.uid');
      $this->db->where($where);
      $this->db->limit(1);
      $query = $this->db->get();

      return $query->row_array();
    }

    public function get_electoral_result_mp($dpt, $circo, $nom){
      if (in_array($dpt, ['01','02'], true)) {
        $dpt = substr($dpt, 1);
      } elseif($dpt == 971) {
        $dpt = "ZA";
      } elseif ($dpt == 976) {
        $dpt = "ZM";
      } elseif ($dpt == 973) {
        $dpt = "ZC";
      } elseif ($dpt == 972) {
        $dpt = "ZB";
      } elseif ($dpt == 974) {
        $dpt = "ZD";
      } elseif ($dpt == 987) {
        $dpt = "ZP";
      } elseif ($dpt == 988) {
        $dpt = "ZN";
      }

      $sql = 'SELECT *,
        CASE
          WHEN tour = 2 THEN "2ème"
          WHEN tour = 1 THEN "1er"
        END AS tour_election,
        round(score*100, 2) AS score_pct
        FROM election2017_results
        WHERE dpt = ? AND circo = ? AND nom LIKE "%'.$this->db->escape_like_str($nom).'%"
        LIMIT 1
      ';
      $query = $this->db->query($sql, array($dpt, $circo));

      return $query->row_array();
    }

    public function get_other_deputes($groupe_id, $depute_name, $depute_uid, $active, $legislature){
      if ($active) {
        $sql = 'SELECT *
          FROM deputes_all da
          WHERE da.groupeId = ? AND da.mpId != ? AND da.legislature = ?
          ORDER BY da.nameLast < LEFT(?, 1), da.nameLast
          LIMIT 15
        ';
        $query = $this->db->query($sql, array($groupe_id, $depute_uid, $legislature, $depute_name));
      } else {
        $sql = 'SELECT da.*
          FROM deputes_all da
          WHERE da.mpId != ? AND da.dateFin IS NOT NULL AND da.legislature = 15
          ORDER BY da.nameLast < LEFT (?, 1), da.nameLast
          LIMIT 15
        ';
        $query = $this->db->query($sql, array($depute_uid, $depute_name));
      }
      return $query->result_array();
    }

    public function get_other_deputes_legislature($nameLast, $depute_uid, $legislature){
      $sql = 'SELECT d.nameFirst, d.nameLast, d.nameUrl, d.dptSlug
        FROM deputes_last d
        WHERE d.mpId != ? AND legislature = ?
        ORDER BY d.nameLast < LEFT (?, 1), d.nameLast
        LIMIT 15
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature, $nameLast));

      return $query->result_array();
    }

    public function get_history_all_deputes($legislature){
      $this->db->select('length');
      $query = $this->db->get('history_mps_average', 1);

      return $query->row_array();
    }

    public function get_accord_groupes_actifs($depute_uid, $legislature){
      $sql = 'SELECT t1.accord, o.libelle, o.libelleAbrev, t1.votesN, t1.organeRef, o.positionPolitique
        FROM deputes_accord_cleaned t1
        LEFT JOIN organes o ON t1.organeRef = o.uid
        WHERE t1.mpId = ? AND o.dateFin IS NULL AND libelleAbrev != "NI" AND votesN > 10 AND t1.legislature = ?
        ORDER BY t1.accord DESC
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature));

      return $query->result_array();
    }

    public function get_accord_groupes_all($depute_uid, $legislature){
      $sql = 'SELECT t1.accord, o.libelle, o.libelleAbrev, t1.votesN, CASE WHEN o.dateFin IS NULL THEN 1 ELSE 0 END AS ended
        FROM deputes_accord_cleaned t1
        LEFT JOIN organes o ON t1.organeRef = o.uid
        WHERE t1.mpId = ? AND t1.legislature = ?
        ORDER BY t1.accord DESC
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature));

      return $query->result_array();
    }

    public function get_person_schema($depute){
      $schema = [
        "@context" => "http://schema.org",
        "@type" => "Person",
        "name" => $depute['nameFirst']. ' '.$depute['nameLast'],
        "familyName" => $depute['nameLast'],
        "givenName" => $depute['nameFirst'],
        "url" => base_url().'deputes/'.$depute['dptSlug'].'/depute_'.$depute['nameUrl'],
        "jobTitle" => "Député français",
        "image" => base_url()."assets/imgs/deputes/depute_".$depute['idImage'].".png",
        "memberOf" => [
          "@type" => "Organization",
          "name" => "Assemblée nationale",
          "url" => "http://www.assemblee-nationale.fr/",
          "foundingDate" => "1958-10-04",
          "sameAs" => "https://fr.wikipedia.org/wiki/Assembl%C3%A9e_nationale_(France)",
          "location" => [
            "@type" => "Place",
            "address" => [
              "@type" => "PostalAddress",
              "addressCountry" => "FR",
              "addressLocality" => "Paris",
              "postalCode" => "75355",
              "streetAddress" => "126 rue de l'Université"
            ]
          ]
        ]
      ];

      if ($depute['facebook'] != "" & $depute['twitter'] != "" & $depute['website'] != "") {
        $schema["sameAs"] =  array("https://www.facebook.com/".$depute['facebook'], "https://twitter.com/".$depute['twitter'], $depute['website']);
      } elseif ($depute['facebook'] != "" & $depute['twitter'] == "" & $depute['website'] == "") {
        $schema["sameAs"] =  "https://www.facebook.com/".$depute['facebook'];
      } elseif ($depute['facebook'] == "" & $depute['twitter'] != "" &  $depute['website'] == "") {
        $schema["sameAs"] =  "https://twitter.com/".$depute['twitter'];
      } elseif ($depute['facebook'] != "" & $depute['twitter'] == "" &  $depute['website'] != "") {
        $schema["sameAs"] =  array("https://www.facebook.com/".$depute['facebook'], $depute['website']);
      } elseif ($depute['facebook'] == "" & $depute['twitter'] != "" &  $depute['website'] != "") {
        $schema["sameAs"] =  array("https://twitter.com/".$depute['twitter'], $depute['website']);
      } elseif ($depute['facebook'] != "" & $depute['twitter'] != "" &  $depute['website'] == "") {
        $schema["sameAs"] =  array("https://www.facebook.com/".$depute['facebook'], "https://twitter.com/".$depute['twitter']);
      }

      if ($depute['birthDate'] != "") {
        $schema['birthDate'] = $depute['birthDate'];
      }

      if ($depute['civ'] == 'Mme') {
        $schema['gender'] = "Female";
      } elseif ($depute['civ'] == 'M.') {
        $schema['gender'] = "Male";
      }

      if (!empty($groupe)) {
        $schema['worksFor'] = [
          "@type" => "Organization",
          "name" => $groupe['libelle'],
          "logo" => base_url()."assets/imgs/groupes/".$groupe['libelleAbrev'].".png"
        ];
      }

      if ($depute['mailAn'] != "") {
        $schema['ContactPoint'] = [
          "@type" => "ContactPoint",
          "email" => $depute['mailAn']
        ];
      }

      return $schema;
    }

    public function get_depute_random(){
      $sql = 'SELECT A.*, d.civ
        FROM
        (
          SELECT *
          FROM deputes_all da
          WHERE legislature = ? AND dateFin IS NULL
          ORDER BY RAND()
          LIMIT 1
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_depute_vote_plus(){
      $sql = 'SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM (
          SELECT *
          FROM class_participation_six
          WHERE score IN (
            SELECT MAX(score) AS maximum
            FROM class_participation_six
            WHERE votesN > 10
            ) AND votesN > 10
        ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_depute_vote_moins(){
      $sql = 'SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM (
          SELECT *
          FROM class_participation_six
          WHERE score IN (
            SELECT MIN(score) AS maximum
            FROM class_participation_six
            WHERE votesN > 10
            )
          AND votesN > 10
        ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_depute_loyal_plus(){
      $sql = 'SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM
        (
          SELECT *
          FROM class_loyaute_six
          WHERE score IN (
            SELECT MAX(score)
            FROM class_loyaute_six
            WHERE votesN > 50
          ) AND votesN > 50
          ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_depute_loyal_moins(){
      $sql = 'SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM
        (
          SELECT *
          FROM class_loyaute_six
          WHERE score IN (
            SELECT MIN(score)
            FROM class_loyaute_six
            WHERE votesN > 50
          ) AND votesN > 50
          ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId
        WHERE da.legislature = ? AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_deputes_entrants($limit = false){
      $sql = 'SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.datePriseFonction, d.nameUrl, d.dptSlug
        FROM mandat_principal mp
        LEFT JOIN deputes_last d ON mp.mpId = d.mpId
        WHERE mp.legislature = ? AND codeQualite = "membre"
        ORDER BY mp.datePriseFonction DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($sql, legislature_current());

      return $query->result_array();
    }

    public function get_deputes_sortants(){
      $sql = 'SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, d.nameUrl
        FROM mandat_principal mp
        LEFT JOIN deputes_last d ON mp.mpId = d.mpId
        WHERE mp.legislature = ? AND codeQualite = "membre" AND mp.dateFin IS NOT NULL
        ORDER BY mp.dateFin DESC
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->result_array();
    }

    public function get_postes_assemblee(){
      $sql = 'SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.codeQualite, mp.libQualiteSex
        FROM mandat_principal mp
        LEFT JOIN deputes d ON mp.mpId = d.mpId
        WHERE mp.legislature = ? AND codeQualite != "membre"
        ORDER BY mp.dateDebut DESC
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->result_array();
    }

    public function get_groupes_entrants($limit = false){
      $sql = 'SELECT d.nameFirst, d.nameLast, mg.mpId AS id, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle, d.nameUrl, d.dptSlug
        FROM mandat_groupe mg
        LEFT JOIN deputes_last d ON mg.mpId = d.mpId
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE mg.legislature = ?
        ORDER BY mg.dateDebut DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($sql, legislature_current());

      return $query->result_array();
    }

    public function get_stats_participation_commission($depute_uid){
      $sql = 'SELECT A.*, B.*
        FROM
        (
          SELECT ROUND(score*100) AS score, votesN
          FROM class_participation_commission
          WHERE mpId = ?
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_participation_commission
        ) B
      ';
      $query = $this->db->query($sql, $depute_uid);

      return $query->row_array();

    }

    public function get_stats_loyaute($depute_uid, $legislature){
      $sql = 'SELECT A.*, B.*
        FROM
        (
          SELECT ROUND(score*100) AS score, votesN, legislature
          FROM class_loyaute
          WHERE mpId = ? AND legislature = ?
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_loyaute
          WHERE legislature = ?
        ) B
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature, $legislature));

      return $query->row_array();
    }

    public function get_stats_loyaute_history($depute_uid, $legislature){
      $sql = 'SELECT mg.dateDebut, mg.dateFin, o.libelle, o.libelleAbrev, ROUND(dl.score * 100) AS score, dl.votesN
        FROM deputes_loyaute dl
        LEFT JOIN mandat_groupe mg ON dl.mandatId = mg.mandatId
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE dl.mpId = ? AND dl.legislature = ?
        ORDER BY mg.dateDebut DESC
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature));

      return $query->result_array();
    }

    public function get_stats_majorite($depute_uid, $legislature){
      $sql = 'SELECT A.*, B.*
        FROM
        (
          SELECT ROUND(score*100) AS score, votesN
          FROM class_majorite
          WHERE mpId = ? AND legislature = ?
        ) A,
        (
          SELECT ROUND(AVG(t1.score)*100) AS mean
          FROM class_majorite t1
          LEFT JOIN deputes_all da ON t1.mpId = da.mpId
          WHERE da.groupeId != ?  AND da.legislature = ? AND dateFin IS NULL
        ) B
      ';
      $query = $this->db->query($sql, array($depute_uid, $legislature, majority_group(), $legislature));

      return $query->row_array();
    }

  }
