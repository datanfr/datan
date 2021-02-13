<?php
  class Deputes_act_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function active($id){
      $query = $this->db->query('
        SELECT mpId
        FROM deputes_actifs
        WHERE mpId = "'.$id.'"
      ');

      return $query;
    }

    public function get_deputes_actifs($departement) {
      if (is_null($departement)) {
        $query = $this->db->query('
          SELECT *
          FROM deputes_actifs
        ');
      } else {
        $query = $this->db->query('
          SELECT *
          FROM deputes_actifs
          WHERE dptSlug = "'.$departement.'"
        ');
      }

        return $query->result_array();
    }

    public function get_deputes_all($departement = NULL){
      if (is_null($departement)) {
        $query = $this->db->query('
          SELECT nameLast, nameFirst, dptSlug, nameUrl, mpId
          FROM deputes_actifs
          UNION ALL
          SELECT nameLast, nameFirst, dptSlug, nameUrl, mpId
          FROM deputes_inactifs
        ');
      } else {
        $query = $this->db->query('
          SELECT nameLast, nameFirst, dptSlug, nameUrl, mpId
          FROM deputes_actifs
          WHERE dptSlug = "'.$departement.'"
          UNION ALL
          SELECT nameLast, nameFirst, dptSlug, nameUrl, mpId
          FROM deputes_inactifs
          WHERE dptSlug = "'.$departement.'"
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

    // MIGHT BE DELETED
    public function get_groupes_actifs(){
      $query = $this->db->query('
        SELECT libelle, libelleAbrev
        FROM deputes_actifs
        GROUP BY libelle
      ');
      return $query->result_array();
    }

    public function get_deputes_inactifs(){
      $query = $this->db->query('
      SELECT di.*, mg.organeRef, o.libelle, o.libelleAbrev, o.couleurAssociee
      FROM deputes_inactifs di
      LEFT JOIN (SELECT mpId, MAX(dateFin) AS maxDateFin FROM mandat_groupe GROUP BY mpId) AS S ON di.mpId = S.mpId
      LEFT JOIN mandat_groupe mg ON di.mpId = mg.mpId AND mg.dateFin = S.maxDateFin
      LEFT JOIN organes o ON o.uid = mg.organeRef
      ');
      return $query->result_array();
    }

    public function get_n_deputes_inactive(){
      $query = $this->db->query('
      SELECT COUNT(A.mpId) AS total
      FROM
      (
        SELECT di.mpId
        FROM deputes_inactifs di
        GROUP BY di.mpId
      ) A
      ');
      return $query->row_array();
    }

    public function get_deputes_gender(){
      $query = $this->db->query('
        SELECT COUNT(da.civ) AS n, ROUND(COUNT(da.civ)*100/577) AS percentage,
          CASE
            WHEN da.civ = "M." THEN "male"
            WHEN da.civ = "Mme" THEN "female"
          END AS gender
        FROM deputes_actifs da
        GROUP BY da.civ
      ');
      return $query->result_array();
    }

    public function get_groupes_inactifs(){
      $query = $this->db->query('
        SELECT A.libelle, A.libelleAbrev
        FROM
        (
        SELECT di.*, mg.organeRef, o.libelle, o.libelleAbrev, o.couleurAssociee
        FROM deputes_inactifs di
        LEFT JOIN (SELECT mpId, MAX(dateFin) AS maxDateFin FROM mandat_groupe GROUP BY mpId) AS S ON di.mpId = S.mpId
        LEFT JOIN mandat_groupe mg ON di.mpId = mg.mpId AND mg.dateFin = S.maxDateFin
        LEFT JOIN organes o ON o.uid = mg.organeRef
        ) A
        GROUP BY libelle
      ');
      return $query->result_array();
    }

    public function get_depute_individual($nameUrl, $departement) {
      $query = $this->db->query('
      SELECT da.*,
        substr(da.mpId, 3) AS idImage,
        YEAR(current_timestamp()) - YEAR(da.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(da.birthDate) OR (MONTH(current_timestamp()) = MONTH(da.birthDate) AND DAY(current_timestamp()) < DAY(da.birthDate)) THEN 1 ELSE 0 END AS age,
        dc.facebook, dc.twitter, dc.website, dc.mailAn,
        h.mpLength, h.mandatesN, h.lengthEdited
      FROM deputes_actifs da
      LEFT JOIN mandat_groupe mg ON mg.mpId = da.mpId
      LEFT JOIN organes o ON mg.organeRef = o.uid
      LEFT JOIN deputes_contacts_cleaned dc ON dc.mpId = da.mpId
      LEFT JOIN history_per_mps_average h ON h.mpId = da.mpId
      WHERE da.nameUrl = "'.$nameUrl.'" AND da.dptSlug = "'.$departement.'" AND mg.preseance IN (SELECT MIN(preseance) FROM mandat_groupe WHERE mpId = da.mpId AND dateFin IS NULL)
      ');

      $result = $query->row_array();

      if (empty($result)) {
        $query = $this->db->query('
          SELECT d.*,
            substr(d.mpId, 3) AS idImage,
            YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age,
            dc.facebook, dc.twitter, dc.website, dc.mailAn,
            h.mpLength, h.mandatesN, h.lengthEdited,
            date_format(d.dateFinMP, "%d %M %Y") AS dateFinMpFR
          FROM deputes_inactifs d
          LEFT JOIN deputes_contacts_cleaned dc ON dc.mpId = d.mpId
          LEFT JOIN history_per_mps_average h ON h.mpId = d.mpId
          WHERE d.nameUrl = "'.$nameUrl.'" AND d.dptSlug = "'.$departement.'"
        ');
        $result = $query->row_array();
        if (!empty($result)) {
          $result['active'] = FALSE;
        }
      } else {
        $result['active'] = TRUE;
      }

      return $result;
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

    public function get_deputes_groupes($depute_uid) {
      $query = $this->db->query('
        SELECT mg.organeRef, mg.codeQualite, o.libelle, o.libelleAbrev, o.couleurAssociee
        FROM mandat_groupe mg
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE mg.mpId = "'.$depute_uid.'" AND mg.typeOrgane = "GP" AND mg.legislature = 15 AND mg.dateFin is null
        ORDER BY mg.dateDebut DESC, mg.preseance ASC
        LIMIT 1
      ');
      return $query->row_array();
    }

    public function get_depute_groupe_inactif($depute_uid){
      $query = $this->db->query('
      SELECT mg.organeRef AS groupeId, o.libelle, o.libelleAbrev, o.couleurAssociee
      FROM deputes_inactifs di
      LEFT JOIN (SELECT mpId, MAX(dateFin) AS maxDateFin FROM mandat_groupe GROUP BY mpId) AS S ON di.mpId = S.mpId
      LEFT JOIN mandat_groupe mg ON di.mpId = mg.mpId AND mg.dateFin = S.maxDateFin
      LEFT JOIN organes o ON o.uid = mg.organeRef
      WHERE di.mpId = "'.$depute_uid.'"
      ');

      return $query->row_array();
    }


    public function get_other_deputes($groupe_id, $depute_name, $depute_uid, $active){
      if ($active == TRUE) {
        $query = $this->db->query('
        SELECT *
        FROM deputes_actifs da
        WHERE da.groupeId = "'.$groupe_id.'" AND da.mpId != "'.$depute_uid.'"
        ORDER BY da.nameLast < LEFT("'.$depute_name.'", 1), da.nameLast
        LIMIT 15
        ');
      } else {
        $query = $this->db->query('
        SELECT di.*
        FROM deputes_inactifs di
        WHERE di.mpId != "'.$depute_uid.'"
        ORDER BY di.nameLast < LEFT ("'.$depute_name.'", 1), di.nameLast
        LIMIT 15
        ');
      }
      return $query->result_array();
    }

    public function get_mandats($depute_uid){
      $query = $this->db->query('
      SELECT A.*,
      	CASE
          WHEN ROUND(A.duree/365) = 1 THEN CONCAT(ROUND(A.duree/365), " an")
          WHEN ROUND(A.duree/365) > 1 THEN CONCAT(ROUND(A.duree/365), " ans")
          WHEN ROUND(A.duree/30) != 0 THEN CONCAT(ROUND(A.duree/30), " mois")
          ELSE CONCAT(A.duree, " jours")
          END AS duree_edito,
          @s:=@s+1 AS "classement"
      FROM
      (
      	SELECT mp.dateDebut, mp.dateFin, mp.preseance, mp.causeFin, mp.datePriseFonction,
      		CASE
      		WHEN dateFin IS NOT NULL THEN datediff(dateFin, datePriseFonction)
      		ELSE datediff(curdate(), datePriseFonction)
      	END AS duree
      	FROM mandat_principal mp
      	WHERE mpId = "'.$depute_uid.'" AND typeOrgane = "ASSEMBLEE" AND legislature = 15 AND codeQualite = "membre"
      	ORDER BY datePriseFonction DESC
      ) A,
      (SELECT @s:= 0) AS s
      ');

      $results = $query->result_array();
      setlocale(LC_TIME, 'french');

      //print_r($results);

      //If one mandate or two
      if (count($results) == 1) {
        $result = $query->row_array();
        $result['fonction_lettres'] = utf8_encode(strftime('%B %Y', strtotime($result['datePriseFonction'])));
      } else {
        //print_r($results);
        $current = $results[0];
        $current['fonction_lettres'] = utf8_encode(strftime('%B %Y', strtotime($results[0]['datePriseFonction'])));
        $current['election_lettres'] = utf8_encode(strftime('%B %Y', strtotime($results[0]['dateDebut'])));
        $last = $results[count($results)-1];
        $last['fonction_lettres'] = utf8_encode(strftime('%B %Y', strtotime($last['datePriseFonction'])));

        $elections_inval = array('PA721816', 'PA724827', 'PA721166', 'PA721036', 'PA642764', 'PA267289');
        $gvt_left = array('PA721560', 'PA717167', 'PA607395', 'PA332747');

        if (in_array($depute_uid, $elections_inval)) {
          $current["reason"] = "invalidation";
        } elseif (in_array($depute_uid, $gvt_left)) {
          $current["reason"] = "gvt";
        } elseif ($last['causeFin'] == "Nomination comme membre du Gouvernement") {
          $current["reason"] = "gvt";
        } else {
          $current["reason"] = "XXX";
        }

        $result = [
          "current" => $current,
          "last" => $last
        ];
      }

      return $result;
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

    public function get_mandats_nombre($depute_uid){
      $query = $this->db->query('
        SELECT count(A.legislature) AS nombre
        FROM
        (
          SELECT legislature
          FROM mandat_principal
          WHERE mpId = "'.$depute_uid.'" AND codeQualite = "membre" AND typeOrgane = "ASSEMBLEE"
          GROUP BY legislature
        ) A
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
        SELECT *
        FROM deputes_actifs
        ORDER BY RAND()
        LIMIT 1
      ');
      return $query->row_array();
    }

    public function get_depute_vote_plus($six){
      if ($six == TRUE) {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
          FROM (
            SELECT *
            FROM class_participation_six
            WHERE score IN (
            	SELECT MAX(score) AS maximum
            	FROM class_participation_six
              WHERE votesN > 200
            	) AND votesN > 200
          ) A
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
          ORDER BY RAND()
          LIMIT 1
        ');
      } else {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
          FROM
          (
            SELECT *
            FROM class_participation
            WHERE classement = 1
          ) A
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ');
      }

      return $query->row_array();
    }

    public function get_depute_vote_moins($six){
      if ($six == TRUE) {
        $query = $this->db->query('
        SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
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
        LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ORDER BY RAND()
        LIMIT 1
        ');
      } else {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
          FROM
          (
            SELECT *
            FROM class_participation
            ORDER BY classement DESC
            LIMIT 1
          ) A
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ');
      }

      return $query->row_array();
    }

    public function get_depute_loyal_plus($six){
      if ($six == TRUE) {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
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
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
          ORDER BY RAND()
          LIMIT 1
        ');
      } else {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
          FROM
          (
            SELECT *
            FROM class_loyaute
            WHERE classement = 1
          ) A
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ');
      }
      return $query->row_array();
    }

    public function get_depute_loyal_moins($six){
      if ($six == TRUE) {
        $query = $this->db->query('
        SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
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
        LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ORDER BY RAND()
        LIMIT 1
        ');
      } else {
        $query = $this->db->query('
          SELECT A.mpId, A.score, A.classement, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
          FROM
          (
            SELECT *
            FROM class_loyaute
            ORDER BY classement DESC
            LIMIT 1
          ) A
          LEFT JOIN deputes_actifs da ON da.mpId = A.mpId
        ');
      }

      return $query->row_array();
    }

    public function get_deputes_entrants(){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.datePriseFonction
      FROM mandat_principal mp
      LEFT JOIN deputes d ON mp.mpId = d.mpId
      WHERE mp.legislature = 15 AND codeQualite = "membre"
      ORDER BY mp.datePriseFonction DESC
      ');

      return $query->result_array();
    }

    public function get_deputes_sortants(){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin
      FROM mandat_principal mp
      LEFT JOIN deputes d ON mp.mpId = d.mpId
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

    public function get_groupes_entrants(){
      $query = $this->db->query('
      SELECT d.nameFirst, d.nameLast, mg.mpId AS id, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle
      FROM mandat_groupe mg
      LEFT JOIN deputes d ON mg.mpId = d.mpId
      LEFT JOIN organes o ON mg.organeRef = o.uid
      WHERE mg.legislature = 15
      ORDER BY mg.dateDebut DESC
      ');

      return $query->result_array();
    }

    public function get_stats_participation($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT classement, ROUND(score*100) AS score, votesN
        FROM class_participation
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_participation
      ) B
      ');

      $result = $query->row_array();

      if (empty($result)) {
        $query = $this->db->query('
        SELECT A.*, B.*
        FROM
        (
          SELECT classement, ROUND(score*100) AS score, votesN
          FROM class_participation_all
          WHERE mpId = "'.$depute_uid.'"
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_participation
        ) B
        ');

        $result = $query->row_array();
      }

      return $result;

    }

    public function get_stats_participation_commission($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT classement, ROUND(score*100) AS score, votesN
        FROM class_participation_commission
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_participation_commission
      ) B
      ');

      $result = $query->row_array();

      if (empty($result)) {
        $query = $this->db->query('
        SELECT A.*, B.*
        FROM
        (
          SELECT classement, ROUND(score*100) AS score, votesN
          FROM class_participation_all
          WHERE mpId = "'.$depute_uid.'"
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_participation
        ) B
        ');

        $result = $query->row_array();
      }

      return $result;

    }

    public function get_stats_loyaute($depute_uid){
      $query = $this->db->query('
      SELECT A.*, B.*
      FROM
      (
        SELECT classement, ROUND(score*100) AS score, votesN
        FROM class_loyaute
        WHERE mpId = "'.$depute_uid.'"
      ) A,
      (
        SELECT ROUND(AVG(score)*100) AS mean
        FROM class_loyaute
      ) B
      ');

      $result = $query->row_array();

      if (empty($result)) {
        $query = $this->db->query('
        SELECT A.*, B.*
        FROM
        (
          SELECT classement, ROUND(score*100) AS score, votesN
          FROM class_loyaute_all
          WHERE mpId = "'.$depute_uid.'"
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_loyaute
        ) B
        ');

        $result = $query->row_array();
      }

      return $result;
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
          SELECT classement, ROUND(score*100) AS score, votesN
          FROM class_majorite
          WHERE mpId = "'.$depute_uid.'"
        ) A,
        (
          SELECT ROUND(AVG(t1.score)*100) AS mean
          FROM class_majorite t1
          LEFT JOIN deputes_actifs da ON t1.mpId = da.mpId
          WHERE da.groupeId != "PO730964"
        ) B
      ');

      $result = $query->row_array();

      if (empty($result)) {
        $query = $this->db->query('
          SELECT A.*, B.*
          FROM
          (
            SELECT classement, ROUND(score*100) AS score, votesN
            FROM class_majorite_all
            WHERE mpId = "'.$depute_uid.'"
          ) A,
          (
            SELECT ROUND(AVG(t1.score)*100) AS mean
            FROM class_majorite t1
            LEFT JOIN deputes_actifs da ON t1.mpId = da.mpId
            WHERE da.groupeId != "PO730964"
          ) B
        ');

        $result = $query->row_array();
      }

      return $result;
    }

  }
?>
