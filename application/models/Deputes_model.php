<?php
  class Deputes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_deputes_all($legislature, $active, $departement) {
      if (is_null($departement)) {
        if ($legislature == legislature_current()) {
          if ($active) {
            $query = $this->db->query('
              SELECT *
              FROM deputes_all
              WHERE legislature = '.legislature_current().'
              ORDER BY nameLast ASC, nameFirst ASC
            ');
          }
          if ($active === TRUE) {
            // IF CURRENT LEGISLATURE AND ACTIVE
            $query = $this->db->query('
              SELECT *
              FROM deputes_all
              WHERE legislature = '.legislature_current().' AND dateFin IS NULL
              ORDER BY nameLast ASC, nameFirst ASC
            ');
          } elseif ($active === FALSE) {
            $query = $this->db->query('
              SELECT *
              FROM deputes_all
              WHERE legislature = '.legislature_current().' AND dateFin IS NOT NULL
              ORDER BY nameLast ASC, nameFirst ASC
            ');
          } elseif($active === NULL) {
            $query = $this->db->query('
              SELECT *
              FROM deputes_all
              WHERE legislature = '.legislature_current().'
              ORDER BY nameLast ASC, nameFirst ASC
            ');
          }
        } else {
          $query = $this->db->query('
            SELECT *
            FROM deputes_all
            WHERE legislature = '.$legislature.'
            ORDER BY nameLast ASC, nameFirst ASC
          ');
        }
      } else {
        // IF DEPARTEMENT QUERY
        $query = $this->db->query('
          SELECT *
          FROM deputes_all
          WHERE dptSlug = "'.$departement.'" AND legislature = '.legislature_current().' AND dateFin IS NULL
          ORDER BY nameLast ASC, nameFirst ASC
        ');
      }

        return $query->result_array();
    }

    public function get_infos($id){
      $query = $this->db->query('
        SELECT *
        FROM deputes
        WHERE mpId = "'.$id.'"
      ');
      return $query->row_array();
    }

    public function get_historique($id){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mg.mpId AS id, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle
      FROM mandat_groupe mg
      LEFT JOIN deputes d ON mg.mpId = d.mpId
      LEFT JOIN organes o ON mg.organeRef = o.uid
      WHERE mg.legislature = 15 AND mg.mpId = "'.$id.'"
      ORDER BY mg.dateDebut DESC
      ');

      return $query->result_array();
    }

    public function get_n_deputes_inactive(){
      $query = $this->db->query('
        SELECT count(mpId) AS total
        FROM deputes_all
        WHERE legislature = 15 AND dateFin IS NOT NULL
      ');
      return $query->row_array();
    }

    public function get_deputes_gender($legislature){
      if ($legislature == legislature_current()) {
        $query = $this->db->query('
          SELECT COUNT(da.civ) AS n, ROUND(COUNT(da.civ)*100/577) AS percentage,
            CASE
              WHEN da.civ = "M." THEN "male"
              WHEN da.civ = "Mme" THEN "female"
            END AS gender
          FROM deputes_all da
          WHERE da.legislature = '.$legislature.' AND da.dateFin IS NULL
          GROUP BY da.civ
        ');
      } else {
        $query = $this->db->query('
          SELECT COUNT(da.civ) AS n, ROUND(COUNT(da.civ)*100/577) AS percentage,
            CASE
              WHEN da.civ = "M." THEN "male"
              WHEN da.civ = "Mme" THEN "female"
            END AS gender
          FROM deputes_all da
          WHERE da.legislature = '.$legislature.'
          GROUP BY da.civ
        ');
      }

      return $query->result_array();
    }

    public function get_groupes_inactifs(){
      $query = $this->db->query('
        SELECT A.*
        FROM
        (
          SELECT da.libelle, da.libelleAbrev
          FROM deputes_all da
          WHERE da.legislature = 15 AND da.dateFin IS NOT NULL
          GROUP BY da.groupeId
        ) A
        WHERE A.libelle IS NOT NULL
      ');
      return $query->result_array();
    }

    public function get_depute_individual($nameUrl, $dpt){
      $query = $this->db->query('
        SELECT
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
        WHERE dl.nameUrl = "'.$nameUrl.'" AND dl.dptSlug = "'.$dpt.'"
      ');

      return $query->row_array();
    }

    public function depute_group_president($depute_uid, $groupe_id){
      $query = $this->db->query('
        SELECT *
        FROM mandat_groupe
        WHERE mpId = "'.$depute_uid.'" AND organeRef = "'.$groupe_id.'" AND preseance = 1
      ');

      return $query->row_array();
    }

    public function get_commission_parlementaire($depute_uid){
      $query = $this->db->query('
        SELECT
        ms.libQualiteSex AS commissionCodeQualiteGender, o.libelle AS commissionLibelle, o.libelleAbrege AS commissionAbrege
        FROM mandat_secondaire ms
        LEFT JOIN organes o ON ms.organeRef = o.uid
        WHERE ms.mpId = "'.$depute_uid.'" AND ms.typeOrgane = "COMPER" AND ms.dateFin IS NULL
        AND ms.preseance IN (
            SELECT min(t1.preseance)
            FROM mandat_secondaire t1
            WHERE t1.mpId = ms.mpId AND typeOrgane = "COMPER" AND legislature = 15
          )
      ');

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

      /*
      echo $dpt;
      echo $circo;
      echo $nom;
      */

      $query = $this->db->query('
        SELECT *,
        CASE
          WHEN tour = 2 THEN "2ème"
          WHEN tour = 1 THEN "1er"
        END AS tour_election,
        round(score*100, 2) AS score_pct
        FROM election2017_results
        WHERE dpt = "'.$dpt.'" AND circo = "'.$circo.'" AND nom LIKE ("%'.$nom.'%")
      ');

      return $query->row_array();
    }

    public function get_other_deputes($groupe_id, $depute_name, $depute_uid, $active, $legislature){
      if ($active) {
        $query = $this->db->query('
          SELECT *
          FROM deputes_all da
          WHERE da.groupeId = "'.$groupe_id.'" AND da.mpId != "'.$depute_uid.'" AND da.legislature = '.$legislature.'
          ORDER BY da.nameLast < LEFT("'.$depute_name.'", 1), da.nameLast
          LIMIT 15
        ');
      } else {
        $query = $this->db->query('
          SELECT da.*
          FROM deputes_all da
          WHERE da.mpId != "'.$depute_uid.'" AND da.dateFin IS NOT NULL AND da.legislature = 15
          ORDER BY da.nameLast < LEFT ("'.$depute_name.'", 1), da.nameLast
          LIMIT 15
        ');
      }
      return $query->result_array();
    }

    public function get_other_deputes_legislature($nameLast, $depute_uid, $legislature){
      $query = $this->db->query('
        SELECT d.nameFirst, d.nameLast, d.nameUrl, d.dptSlug
        FROM deputes_last d
        WHERE d.mpId != "'.$depute_uid.'" AND legislature = '.$legislature.'
        ORDER BY d.nameLast < LEFT ("'.$nameLast.'", 1), d.nameLast
        LIMIT 15
      ');

      return $query->result_array();
    }

    public function get_history_all_deputes($legislature){
      $query = $this->db->query('
        SELECT length
        FROM history_mps_average
      ');
      return $query->row_array();
    }

    public function get_mandats_all($depute_uid){
      $query = $this->db->query('
        SELECT *
        FROM history_per_mps_average
        WHERE mpId = "'.$depute_uid.'"
      ');
      return $query->row_array();
    }

    public function get_accord_groupes_actifs($depute_uid){
      $query = $this->db->query('
      SELECT t1.accord, t1.libelle, t1.libelleAbrev, t1.organeRef, t1.positionPolitique, t1.votesN
      FROM deputes_accord_cleaned t1
      WHERE t1.mpId = "'.$depute_uid.'" AND ended = 0 AND libelleAbrev != "NI" AND votesN > 10
      ORDER BY t1.accord DESC
      ');
      return $query->result_array();
    }

    public function get_accord_groupes_all($depute_uid){
      $query = $this->db->query('
      SELECT t1.accord, t1.libelle, t1.libelleAbrev, t1.organeRef, t1.positionPolitique, t1.ended, t1.votesN
      FROM deputes_accord_cleaned t1
      WHERE t1.mpId = "'.$depute_uid.'"
      ORDER BY t1.accord DESC
      ');
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
      $query = $this->db->query('
        SELECT A.*, d.civ
        FROM
        (
          SELECT *
          FROM deputes_all da
          WHERE legislature = '.legislature_current().' AND dateFin IS NULL
          ORDER BY RAND()
          LIMIT 1
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
      ');
      return $query->row_array();
    }

    public function get_depute_vote_plus(){
      $query = $this->db->query('
        SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM (
          SELECT *
          FROM class_participation_six
          WHERE score IN (
            SELECT MAX(score) AS maximum
            FROM class_participation_six
            WHERE votesN > 200
            ) AND votesN > 200
        ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId
        WHERE da.legislature = '.legislature_current().' AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ');

      return $query->row_array();
    }

    public function get_depute_vote_moins(){
      $query = $this->db->query('
      SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
      FROM (
        SELECT *
        FROM class_participation_six
        WHERE score IN (
          SELECT MIN(score) AS maximum
          FROM class_participation_six
          WHERE votesN > 200
          )
        AND votesN > 200
      ) A
      LEFT JOIN deputes_all da ON da.mpId = A.mpId
      WHERE da.legislature = '.legislature_current().' AND da.dateFin IS NULL
      ORDER BY RAND()
      LIMIT 1
      ');

      return $query->row_array();
    }

    public function get_depute_loyal_plus(){
      $query = $this->db->query('
        SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
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
        WHERE da.legislature = '.legislature_current().' AND da.dateFin IS NULL
        ORDER BY RAND()
        LIMIT 1
      ');

      return $query->row_array();
    }

    public function get_depute_loyal_moins(){
      $query = $this->db->query('
      SELECT A.mpId, A.score, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
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
      WHERE da.legislature = '.legislature_current().' AND da.dateFin IS NULL
      ORDER BY RAND()
      LIMIT 1
      ');

      return $query->row_array();
    }

    public function get_deputes_entrants($limit = false){
      $queryString = '
      SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.datePriseFonction, d.nameUrl, d.dptSlug
      FROM mandat_principal mp
      LEFT JOIN deputes_last d ON mp.mpId = d.mpId
      WHERE mp.legislature = 15 AND codeQualite = "membre"
      ORDER BY mp.datePriseFonction DESC
      ';
      if ($limit){
        $queryString .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($queryString);
      return $query->result_array();
    }

    public function get_deputes_sortants(){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, d.nameUrl
      FROM mandat_principal mp
      LEFT JOIN deputes_last d ON mp.mpId = d.mpId
      WHERE mp.legislature = 15 AND codeQualite = "membre" AND dateFin IS NOT NULL
      ORDER BY dateFin DESC
      ');

      return $query->result_array();
    }

    public function get_postes_assemblee(){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.codeQualite, mp.libQualiteSex
      FROM mandat_principal mp
      LEFT JOIN deputes d ON mp.mpId = d.mpId
      WHERE mp.legislature = 15 AND codeQualite != "membre"
      ORDER BY mp.dateDebut DESC
      ');

      return $query->result_array();
    }

    public function get_groupes_entrants($limit = false){
      $queryString = '
      SELECT d.nameFirst, d.nameLast, mg.mpId AS id, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle, d.nameUrl, d.dptSlug
      FROM mandat_groupe mg
      LEFT JOIN deputes_last d ON mg.mpId = d.mpId
      LEFT JOIN organes o ON mg.organeRef = o.uid
      WHERE mg.legislature = 15
      ORDER BY mg.dateDebut DESC
      ';
      if ($limit){
        $queryString .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($queryString);

      return $query->result_array();
    }

    public function get_stats_participation($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT ROUND(score*100) AS score, votesN
        FROM class_participation
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_participation
      ) B
      ');

      return $query->row_array();

    }

    public function get_stats_participation_commission($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT ROUND(score*100) AS score, votesN
        FROM class_participation_commission
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_participation_commission
      ) B
      ');

      return $query->row_array();

    }

    public function get_stats_loyaute($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT ROUND(score*100) AS score, votesN
        FROM class_loyaute
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_loyaute
      ) B
      ');

      return $query->row_array();
    }

    public function get_stats_loyaute_history($depute_uid){
      $query = $this->db->query('
      SELECT mg.dateDebut, mg.dateFin, o.libelle, o.libelleAbrev, ROUND(dl.score * 100) AS score, dl.votesN
      FROM deputes_loyaute dl
      LEFT JOIN mandat_groupe mg ON dl.mandatId = mg.mandatId
      LEFT JOIN organes o ON mg.organeRef = o.uid
      WHERE dl.mpId = "'.$depute_uid.'"
      ORDER BY mg.dateDebut DESC
      ');

      return $query->result_array();
    }

    public function get_stats_majorite($depute_uid){
      $query = $this->db->query('
        SELECT A.*, B.*
        FROM
        (
          SELECT ROUND(score*100) AS score, votesN
          FROM class_majorite
          WHERE mpId = "'.$depute_uid.'"
        ) A,
        (
          SELECT ROUND(AVG(t1.score)*100) AS mean
          FROM class_majorite t1
          LEFT JOIN deputes_all da ON t1.mpId = da.mpId
          WHERE da.groupeId != "'.majority_group().'"  AND da.legislature = 15 AND dateFin IS NULL
        ) B
      ');

      return $query->row_array();
    }

  }
?>
