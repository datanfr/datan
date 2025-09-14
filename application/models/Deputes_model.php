<?php
  class Deputes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_deputes_all($legislature, $active, $departement) {
      if (!is_null($departement)) {
        $this->db->where('da.dptSlug', $departement);
      }

      if ($active == TRUE && dissolution() == FALSE) {
        $this->db->where('da.dateFin IS NULL');
      } else {
        $this->db->where('da.dateFin IS NOT NULL');
      }

      $this->db->select('da.*');
      $this->db->select('da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter');
      $this->db->select('dl.legislature AS legislature_last');
      $this->db->join('deputes_last dl', 'da.mpId = dl.mpId', 'left');
      $this->db->where('da.legislature', $legislature);
      $this->db->order_by('da.nameLast ASC, da.nameFirst ASC');
      return $this->db->get('deputes_all da')->result_array();
    }

    public function get_deputes_last($legislature){
      $where = array('legislature' => $legislature);
      return $this->db->get_where('deputes_last', $where)->result_array();
    }

    public function get_infos($id){
      return $this->db->get_where('deputes', array('mpId' => $id), 1)->row_array();
    }

    public function get_historique($id){
      $sql = 'SELECT d.nameFirst, d.nameLast, mg.dateDebut, mg.dateFin, mg.codeQualite, o.libelle
        FROM mandat_groupe mg
        LEFT JOIN deputes d ON mg.mpId = d.mpId
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE mg.legislature >= 15 AND mg.mpId = ?
        ORDER BY mg.dateDebut DESC
      ';
      return $this->db->query($sql, $id)->result_array();
    }

    public function get_historique_mandats($id){
      $sql = 'SELECT *
        FROM deputes_all
        WHERE mpId = ?
        ORDER BY legislature DESC
      ';
      return $this->db->query($sql, $id)->result_array();
    }

    public function get_n_deputes_inactive($legislature){
      $this->db->where('legislature', $legislature);
      $this->db->where('dateFin IS NOT NULL');
      return $this->db->count_all_results('deputes_all');
    }

    public function get_deputes_gender($legislature){
      $datePriseFonction = array(
        12 => "2002-06-19",
        13 => "2007-06-20",
        14 => "2012-06-20",
        15 => "2017-06-21",
        16 => "2022-06-22"
      );
      $this->db->select('COUNT(d.civ) AS n');
      $this->db->select('CASE WHEN d.civ = "M." THEN "male" WHEN civ = "Mme" THEN "female" END AS gender', FALSE);
      $this->db->where('m.legislature', $legislature);
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('m.dateFin IS NULL');
      } else {
        $this->db->where('m.datePriseFonction', $datePriseFonction[$legislature]);
      }
      $this->db->join('deputes_all d', 'd.mpId = m.mpId AND d.legislature = m.legislature', 'left');
      $this->db->group_by('d.civ');
      $return = $this->db->get('mandat_principal m')->result_array();

      $total = 0;
      foreach ($return as $key => $value) {
        $total += $value['n'];
      }
      foreach ($return as $key => $value) {
        $return[$key]['total'] = $total;
        $return[$key]['percentage'] = round($value['n'] / $total * 100);
      }

      return $return;
    }

    public function get_groupes_inactifs(){
      $this->db->select('libelle, libelleAbrev');
      $this->db->where('legislature', legislature_current());
      $this->db->where('dateFin IS NOT NULL');
      $this->db->group_by('groupeId');
      $this->db->order_by('libelle', 'ASC');
      $this->db->having('libelle IS NOT NULL');
      return $this->db->get('deputes_all')->result_array();
    }

    public function get_depute_by_nameUrl($nameUrl) {
      $where = array(
        'nameUrl' => $nameUrl
      );
      return $this->db->get_where('deputes_last', $where, 1)->row_array();
    }

    public function get_depute_by_mpId($mpId) {
      $this->db->select('*, substr(mpId, 3) AS idImage');
      $where = array(
        'mpId' => $mpId
      );
      return $this->db->get_where('deputes_last', $where, 1)->row_array();
    }

    public function get_depute_by_email($email){
      $where = array(
        'mailAn' => $email
      );
      $this->db->join('deputes_last', 'deputes_last.mpId = deputes_contacts.mpId', 'right');
      return $this->db->get_where('deputes_contacts', $where, 1)->row_array();
    }

    public function get_depute_by_legislature($mpId, $legislature){
      $where = array(
        'da.mpId' => $mpId,
        'da.legislature' => $legislature
      );
      $this->db->select('da.*');
      $this->db->select('dl.legislature AS legislature_last');
      $this->db->join('deputes_last dl', 'dl.mpId = da.mpId', 'left');
      return $this->db->get_where('deputes_all da', $where, 1)->row_array();
    }

    public function get_depute_contacts($mpId){
      return $this->db->get_where('deputes_contacts', array('mpId' => $mpId), 1)->row_array();
    }

    public function update_facebook($data){
      switch ($data['mpId']) {
        case 'PA794434': // Christophe Bentz
          return 'ChristopheBentzDepute';
          break;

        default:
          return $data['facebook'];
          break;
      }
    }

    public function update_website($data){
      switch ($data['mpId']) {
        case 'PA794786': // Emmanuel Fernandes
          return 'emmanuel-fernandes.fr/';
          break;

        case 'PA795100': // Eric Martineau
          return 'eric-martineau72.fr/';
          break;

        default:
          return $data['website'];
          break;
      }
    }

    public function get_mp_latest_dpt($mp, $dpt){
      switch ($mp) {
        case 'PA610002':
          $dpt = 'paris-75';
          break;
      }
      return $dpt;
    }

    public function get_depute_individual($nameUrl, $dpt){
      $sql = 'SELECT
        dl.*, mg.preseance AS preseanceGroupe,
        dl.libelle_2 AS dptLibelle2,
        substr(dl.mpId, 3) AS idImage,
        h.mandatesN, h.mpLength, h.lengthEdited,
        dc.facebook, dc.twitter, dc.bluesky, dc.website, dc.mailAn,
        date_format(dl.dateFin, "%d %M %Y") AS dateFinMpFR,
        d.birthDate, d.birthCity
        FROM deputes_last dl
        LEFT JOIN history_per_mps_average h ON dl.mpId = h.mpId
        LEFT JOIN deputes_contacts dc ON dl.mpId = dc.mpId
        LEFT JOIN deputes d ON dl.mpId = d.mpId
        LEFT JOIN mandat_groupe mg ON mg.mandatId = dl.groupeMandat AND mg.nominPrincipale = 1
        WHERE dl.nameUrl = ? AND dl.dptSlug = ?
        LIMIT 1
      ';
      $results = $this->db->query($sql, array($nameUrl, $dpt))->row_array();
      $results['facebook'] = $this->update_facebook($results);
      $results['website'] = $this->update_website($results);
      return $results;
    }

    public function get_hatvp_url($mpId){
      $where = array(
        "mpId" => $mpId
      );
      $this->db->select('hatvp');
      $result = $this->db->get_where('deputes', $where)->row_array();
      return $result['hatvp'];
    }

    public function get_last_hatvp_job($mpId){
      $where = array(
        "mpId" => $mpId,
        "category" => "activProf"
      );
      $this->db->select('*, date_format(dateDebut, "%M %Y") AS dateDebut');
      $this->db->select('date_format(dateFin, "%M %Y") AS dateFin');
      $this->db->order_by('dateFin', 'DESC');
      return $this->db->get_where('hatvp', $where)->result_array();
    }

    public function check_depute_legislature($nameUrl, $legislature){
      $where = array(
        "mpId" => $nameUrl,
        "legislature" => $legislature
      );
      $this->db->where($where);
      return $this->db->count_all_results("deputes_all");
    }

    public function get_depute_individual_historique($nameUrl, $dpt, $legislature){
      $sql = 'SELECT
        dl.*,
        substr(dl.mpId, 3) AS idImage,
        h.mandatesN, h.mpLength, h.lengthEdited,
        dc.facebook, dc.twitter, dc.bluesky, dc.website, dc.mailAn,
        date_format(dl.dateFin, "%d %M %Y") AS dateFinMpFR,
        d.birthDate, d.birthCity, last.active, dpt.libelle_1 AS dptLibelle1, dpt.libelle_2 AS dptLibelle2
        FROM deputes_all dl
        LEFT JOIN history_per_mps_average h ON dl.mpId = h.mpId
        LEFT JOIN deputes_contacts dc ON dl.mpId = dc.mpId
        LEFT JOIN deputes d ON dl.mpId = d.mpId
        LEFT JOIN deputes_last last ON dl.mpId = last.mpId
        LEFT JOIN departement dpt ON dpt.departement_code = dl.departementCode
        WHERE dl.nameUrl = ? AND dl.dptSlug = ? AND dl.legislature = ?
        LIMIT 1
      ';
      return $this->db->query($sql, array($nameUrl, $dpt, $legislature))->row_array();
    }

    public function depute_group_president($depute_uid, $groupe_id){
      $where = array(
        'mpId' => $depute_uid,
        'organeRef' => $groupe_id,
        'preseance' => 1
      );
      return $this->db->get_where('mandat_groupe', $where, 1)->row_array();
    }

    public function get_president_an(){
      $this->db->where('m.codeQualite', 'Président');
      $this->db->where('m.dateFin IS NULL');
      $this->db->join('deputes_last da', 'da.mpId = m.mpId', 'left');
      return $this->db->get('mandat_principal m', 1)->row_array();
    }

    public function get_commission_parlementaire($depute_uid, $legislature){
      $sql = 'SELECT
        ms.libQualiteSex AS commissionCodeQualiteGender, o.libelle AS commissionLibelle, o.libelleAbrege AS commissionAbrege
        FROM mandat_secondaire ms
        LEFT JOIN organes o ON ms.organeRef = o.uid
        WHERE ms.mpId = ? AND ms.typeOrgane = "COMPER" AND ms.dateFin IS NULL
        AND ms.preseance IN (
          SELECT min(t1.preseance)
          FROM mandat_secondaire t1
          WHERE t1.mpId = ms.mpId AND typeOrgane = "COMPER" AND legislature = ?
      )';
      return $this->db->query($sql, array($depute_uid, $legislature), 1)->row_array();
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
      return $this->db->get()->row_array();
    }

    public function get_election_result($dpt, $circo, $nom, $legislature){
      $year = (new DateTime($legislature['dateDebut']))->format('Y');
      $escapedNom = '%' . $this->db->escape_like_str($nom) . '%';
      $legislatureNumber = $legislature['legislatureNumber'];

      // Build common parts of SELECT
      $selectCommon = 'nameFirst, nameLast, voix, pct_exprimes, tour,
        CASE
            WHEN tour = 2 THEN "2nd"
            WHEN tour = 1 THEN "1er"
        END AS tour_election';

      // First check if there is an 'election partielle'
      $dateDebut = $legislature['dateDebut'];
      $dateFin = $legislature['dateFin'] ?? date('Y-m-d');

      $sql = "SELECT $selectCommon, date
            FROM elect_legislatives_partielles
            WHERE dpt = ?
              AND circo = ?
              AND elected = 1
              AND nameLast LIKE ?
              AND date BETWEEN ? AND ?
            LIMIT 1";

      $params = [$dpt, $circo, $escapedNom, $dateDebut, $dateFin];
      $result = $this->db->query($sql, $params)->row_array();

      if ($result) {
        $result['candidat'] = $result['nameFirst'] . ' ' . ucfirst(strtolower($result['nameLast']));
        $result['partielle'] = true;
        $result['dateFr'] = utf8_encode(strftime('%B %Y', strtotime($result['date'])));
        return $result;
      }

      // If no 'election partielle', get main election results
      $searchField = $legislatureNumber >= 17 ? 'nameLast' : 'candidat';
      $sql = "SELECT candidat, $selectCommon
            FROM elect_legislatives_results
            WHERE dpt = ? AND circo = ? AND year = ? AND elected = 1 AND $searchField LIKE ?
            LIMIT 1";

      $params = [$dpt, $circo, $year, $escapedNom];
      $result = $this->db->query($sql, $params)->row_array();

      if ($result) {
        // Normalize name only if nameFirst/nameLast are available
        if (!empty($result['nameFirst']) && !empty(['nameLast'])) {
            $result['candidat'] = $result['nameFirst'] . ' ' . ucfirst(strtolower($result['nameLast']));
        }
        $result['partielle'] = false;
        return $result;
      }     

    }

    public function get_election_opponent($dpt, $circo, $tour, $legislature, $partielle){
      $year = (new DateTime($legislature['dateDebut']))->format('Y');
      $legislatureNumber = $legislature['legislatureNumber'];
      $result = [];

      // Common SELECT clause
      $selectFields = 'nameLast, nameFirst, sexe, voix, pct_exprimes,
        CASE
            WHEN tour = 2 THEN "2nd"
            WHEN tour = 1 THEN "1er"
        END AS tour_election';      

      if (!$partielle){
        // Get data if a normal election
        
        $sql = "SELECT candidat, $selectFields
                FROM elect_legislatives_results
                WHERE dpt = ? AND circo = ? AND year = ? AND tour = ? AND elected = 0
                ORDER BY voix DESC";

        $params = [$dpt, $circo, $year, $tour];
        $result = $this->db->query($sql, $params)->result_array();

        // Format candidate name if legislature number >= 17
        if (!empty($result) && $legislature['legislatureNumber'] >= 17) {
            foreach ($result as &$row) {
                $row['candidat'] = $row['nameFirst'] . ' ' . ucfirst(strtolower($row['nameLast']));
            }
        }

      } else {
        // Election partielle
        
        $dateDebut = $legislature['dateDebut'] ?? date('Y-m-d');
        $dateFin = $legislature['dateFin'] ?? date('Y-m-d');

        $sql = "SELECT $selectFields
                FROM elect_legislatives_partielles
                WHERE dpt = ? AND circo = ? AND tour = ? AND elected = 0 AND date BETWEEN ? AND ?
                ORDER BY voix DESC";

        $params = [$dpt, $circo, $tour, $dateDebut, $dateFin];
        $result = $this->db->query($sql, $params)->result_array();

        if (!empty($result)) {
            foreach ($result as &$row) {
                $row['candidat'] = $row['nameFirst'] . ' ' . ucfirst(strtolower($row['nameLast']));
            }
        }
      }
      return $result;      
    }

    public function get_election_infos($dpt, $circo, $tour, $legislature){
      // Add some data
      $legislatureNumber = $legislature['legislatureNumber'];

      // Extract year from legislature start date
      $year = (new DateTime($legislature['dateDebut']))->format('Y');

      $sql = 'SELECT *
        FROM elect_legislatives_infos
        WHERE dpt = ? AND circo = ? AND year = ? AND tour = ?
      ';
      $params = [$dpt, $circo, $year, $tour];
      $result = $this->db->query($sql, $params)->row_array();

      // Add participation_nationale
      $participationMap = [
        17 => [1 => 68, 2 => 67],
        16 => [1 => 48, 2 => 46],
        15 => [1 => 49, 2 => 43]
      ];
      if (isset($participationMap[$legislatureNumber][$tour])) {
        $result['participation_nationale'] = $participationMap[$legislatureNumber][$tour];
      }
      
      return $result;
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
      return $this->db->query($sql, array($depute_uid, $legislature, $nameLast))->result_array();
    }

    public function get_average_length_as_mp($legislature){
      $where = array('legislature' => $legislature);
      $this->db->select('length');
      $result = $this->db->get_where('history_mps_average', $where, 1)->row_array();
      return isset($result['length']) ? $result['length'] : 0;
    }

    public function get_accord_groupes_actifs($depute_uid, $legislature){
      $sql = 'SELECT t1.accord, o.libelle, o.libelleAbrev, t1.votesN, t1.organeRef, o.positionPolitique
        FROM deputes_accord_cleaned t1
        LEFT JOIN organes o ON t1.organeRef = o.uid
        WHERE t1.mpId = ? AND o.dateFin IS NULL AND libelleAbrev != "NI" AND votesN > 10 AND t1.legislature = ?
        ORDER BY t1.accord DESC
      ';
      return $this->db->query($sql, array($depute_uid, $legislature))->result_array();
    }

    public function get_accord_groupes_all($depute_uid, $legislature){
      $sql = 'SELECT t1.accord, o.libelle, o.libelleAbrev, t1.votesN, CASE WHEN o.dateFin IS NULL THEN 0 ELSE 1 END AS ended
        FROM deputes_accord_cleaned t1
        LEFT JOIN organes o ON t1.organeRef = o.uid
        WHERE t1.mpId = ? AND t1.legislature = ?
        ORDER BY t1.accord DESC
      ';
      return $this->db->query($sql, array($depute_uid, $legislature))->result_array();
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
        "image" => base_url()."assets/imgs/deputes_original/depute_".$depute['idImage'].".png",
        "memberOf" => [
          [
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
        ]
      ];

      if($depute['libelleAbrev'] != 'NI') {
        $links = $this->groupes_model->get_groupe_social_media($depute['libelleAbrev']);

        $party = [
          '@type' => 'PoliticalParty',
          'name' => name_group($depute['libelle']),
          'url' => $links['website'] ?? null,
          'sameAs' => $links['wikipedia'] ?? null
        ];

        $party = array_filter($party);

        $schema['memberOf'][] = $party;
      }

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
      if (dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $this->db->where('da.legislature', legislature_current());
      $this->db->select('da.*, CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter');
      $this->db->select('dl.legislature AS legislature_last');
      $this->db->join('deputes_last dl', 'dl.mpId = da.mpId', 'left');
      $this->db->limit(1);
      $this->db->order_by('rand()');
      return $this->db->get('deputes_all da')->row_array();
    }

    public function get_depute_vote_plus(){
      $sql = 'SELECT A.mpId, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.legislature, da.dptSlug, da.couleurAssociee, da.img, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero,
      CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter,
      A.score * 100 AS score
        FROM (
          SELECT *
          FROM class_participation_six c
          WHERE c.index > (SELECT round(MAX(c.index) * 0.75) AS maxIndex FROM class_participation_six c)
          ORDER BY c.score DESC
          LIMIT 1
        ) A
        LEFT JOIN deputes_last da ON da.mpId = A.mpId
      ';
      if (dissolution() === false) {
        $sql .= " WHERE da.dateFin IS NULL";
      }
      return $this->db->query($sql)->row_array();
    }

    public function get_depute_vote_plus_month($legislature, $year, $month, $limit){
      $sql = 'SELECT A.*, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.couleurAssociee, da.img, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero
        FROM
        (
          SELECT vp.mpId, sum(participation) AS votesN, round(avg(participation)*100, 3) AS score
          FROM votes_participation vp
          LEFT JOIN votes_info vi ON vi.legislature = vp.legislature AND vi.voteNumero = vp.voteNumero
          WHERE vp.legislature = ? AND YEAR(vi.dateScrutin) = ? AND MONTH(vi.dateScrutin) = ?
          GROUP BY vp.mpId
        ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = ?
        WHERE A.votesN >= ?
        ORDER BY score DESC
        LIMIT 1
      ';
      return $this->db->query($sql, array($legislature, $year, $month, $legislature, $limit))->row_array();
    }

    public function get_depute_vote_moins(){
      $sql = 'SELECT A.mpId, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.legislature, da.dptSlug, da.couleurAssociee, da.img, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero,
      CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter,
      A.score * 100 AS score
        FROM (
          SELECT *
          FROM class_participation_six c
          WHERE c.index > (SELECT round(MAX(c.index) * 0.75) AS maxIndex FROM class_participation_six c)
          ORDER BY c.score ASC
          LIMIT 1
        ) A
        LEFT JOIN deputes_last da ON da.mpId = A.mpId
      ';
      if (dissolution() === false) {
        $sql .= " WHERE da.dateFin IS NULL";
      }
      return $this->db->query($sql)->row_array();
    }

    public function get_depute_loyal_plus(){
      $sql = 'SELECT A.mpId, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.legislature, da.dptSlug, da.couleurAssociee, da.img, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero,
      CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter,
      A.score * 100 AS score
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
        LEFT JOIN deputes_last da ON da.mpId = A.mpId
    ';
    if (dissolution() === false) {
      $sql .= " WHERE da.dateFin IS NULL";
    } 
    $sql .= " ORDER BY RAND() LIMIT 1";
    return $this->db->query($sql)->row_array();
  }

    public function get_depute_loyal_moins(){
      $sql = 'SELECT A.mpId, A.votesN, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.legislature, da.dptSlug, da.couleurAssociee, da.img, da.libelle, da.libelleAbrev, da.groupeId AS organeRef, da.departementNom AS electionDepartement, da.departementCode AS electionDepartementNumero,
      CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter,
      A.score * 100 AS score
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
        LEFT JOIN deputes_last da ON da.mpId = A.mpId
      ';
      if (dissolution() === false) {
        $sql .= " WHERE da.dateFin IS NULL";
      }
      $sql .= " ORDER BY RAND() LIMIT 1";
      return $this->db->query($sql)->row_array();
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
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_deputes_sortants(){
      $sql = 'SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, d.nameUrl
        FROM mandat_principal mp
        LEFT JOIN deputes_last d ON mp.mpId = d.mpId
        WHERE mp.legislature = ? AND codeQualite = "membre" AND mp.dateFin IS NOT NULL
        ORDER BY mp.dateFin DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_postes_assemblee(){
      $sql = 'SELECT d.nameFirst, d.nameLast, mp.mpId AS id, mp.dateDebut, mp.dateFin, mp.codeQualite, o.libelle, o.libelleAbrev, o.libelleAbrege
        FROM mandat_secondaire mp
        LEFT JOIN deputes d ON mp.mpId = d.mpId
        LEFT JOIN organes o ON o.uid = mp.organeRef
        WHERE mp.legislature = ?
        ORDER BY mp.dateDebut DESC
      ';
      return $this->db->query($sql, legislature_current())->result_array();
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
      return $this->db->query($sql, legislature_current())->result_array();
    }

    public function get_stats_participation_solennels($depute_uid, $legislature){
      $this->db->select('ROUND(score*100) AS score, votesN');
      $this->db->where('mpId', $depute_uid);
      $this->db->where('legislature', $legislature);
      return $this->db->get('class_participation_solennels')->row_array();
    }

    public function get_stats_participation_solennels_all($legislature){
      $this->db->select('round(avg(score) * 100) AS score');
      $this->db->where('legislature', $legislature);
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('active', 1);
      }
      $result = $this->db->get('class_participation_solennels')->row_array();
      return($result['score']);
    }

    public function get_stats_participation_solennels_group($legislature, $group){
      $this->db->select('round(avg(cps.score) * 100) AS score');
      $this->db->join('deputes_all da', 'da.mpId = cps.mpId AND da.legislature = cps.legislature');
      $this->db->where('da.groupeId', $group);
      $this->db->where('cps.legislature', $legislature);
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('cps.active', 1);
      }
      $result = $this->db->get('class_participation_solennels cps')->row_array();
      return($result['score']);
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
      return $this->db->query($sql, $depute_uid)->row_array();
    }

    public function get_stats_participation($depute_uid, $legislature){
      $sql = 'SELECT A.*, B.*
        FROM
        (
          SELECT ROUND(score*100) AS score, votesN
          FROM class_participation
          WHERE mpId = ? AND legislature = ?
        ) A,
        (
          SELECT ROUND(AVG(score)*100) AS mean
          FROM class_participation
          WHERE legislature = ?
        ) B
      ';
      return $this->db->query($sql, array($depute_uid, $legislature, $legislature))->row_array();
    }

    public function get_stats_loyaute($depute_uid, $legislature){
      $this->db->select('ROUND(score*100) AS score, votesN');
      $this->db->where('mpId', $depute_uid);
      $this->db->where('legislature', $legislature);
      return $this->db->get('class_loyaute')->row_array();
    }

    public function get_stats_loyaute_all($legislature){
      $this->db->select('ROUND(AVG(c.score*100)) AS score');
      $this->db->where('c.legislature', $legislature);
      $this->db->join('deputes_all da', 'da.mpId = c.mpId AND da.legislature = c.legislature', 'left');
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $result = $this->db->get('class_loyaute c')->row_array();
      return($result['score']);
    }

    public function get_stats_loyaute_group($legislature, $group){
      $this->db->select('ROUND(AVG(c.score*100)) AS score');
      $this->db->where('c.legislature', $legislature);
      $this->db->where('da.groupeId', $group);
      $this->db->join('deputes_all da', 'da.mpId = c.mpId AND da.legislature = c.legislature', 'left');
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $result = $this->db->get('class_loyaute c')->row_array();
      return($result['score']);
    }

    public function get_stats_loyaute_history($depute_uid, $legislature){
      $sql = 'SELECT mg.dateDebut, mg.dateFin, o.libelle, o.libelleAbrev, ROUND(dl.score * 100) AS score, dl.votesN
        FROM deputes_loyaute dl
        LEFT JOIN mandat_groupe mg ON dl.mandatId = mg.mandatId
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE dl.mpId = ? AND dl.legislature = ?
        ORDER BY mg.dateDebut DESC
      ';
      return $this->db->query($sql, array($depute_uid, $legislature))->result_array();
    }

    public function get_stats_majorite($depute_uid, $legislature){
      $this->db->select('ROUND(score*100) AS score, votesN');
      $this->db->where('mpId', $depute_uid);
      $this->db->where('legislature', $legislature);
      return $this->db->get('class_majorite')->row_array();
    }

    public function get_stats_majorite_all($legislature){
      $majority_groups = $this->groupes_model->get_all_groupes_majority();
      $this->db->select('ROUND(AVG(c.score*100)) AS score');
      $this->db->where('c.legislature', $legislature);
      $this->db->where_not_in('da.groupeId', $majority_groups);
      $this->db->join('deputes_all da', 'da.mpId = c.mpId AND da.legislature = c.legislature', 'left');
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $result = $this->db->get('class_majorite c')->row_array();
      return($result['score']);
    }

    public function get_stats_majorite_group($legislature, $group){
      $this->db->select('ROUND(AVG(c.score*100)) AS score');
      $this->db->where('c.legislature', $legislature);
      $this->db->where('da.groupeId', $group);
      $this->db->join('deputes_all da', 'da.mpId = c.mpId AND da.legislature = c.legislature', 'left');
      if ($legislature == legislature_current() && dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $result = $this->db->get('class_majorite c')->row_array();
      return($result['score']);
    }

    public function get_twitter_accounts($legislature){
      $where = array(
        'd.legislature' => $legislature,
        'd.active' => 1
      );
      $this->db->order_by('d.nameLast', 'ASC');
      $this->db->join('deputes_contacts dc', 'dc.mpId = d.mpId', 'left');
      return $this->db->get_where('deputes_last d', $where)->result_array();
    }

    public function get_professions($mpId){
      $where = array(
        'p.mpId' => $mpId
      );
      $this->db->select('e.id, e.libelle, e.dateYear, p.file, p.tour');
      $this->db->join('elect_libelle e', 'e.id = p.electionId', 'left');
      $this->db->order_by('e.dateYear', 'DESC');
      $results = $this->db->get_where('profession_foi p', $where)->result_array();

      if ($results) {
        foreach ($results as $key => $value) {
          $return[$value['id']]['election'] = $value['libelle'] . ' ' . $value['dateYear'];
          if ($value['tour'] == 1) {
            $return[$value['id']]['round1'] = asset_url() . "data/professions/election_" . $value['id'] . "/" . $value['file'];
          }
          if ($value['tour'] == 2) {
            $return[$value['id']]['round2'] = asset_url() . "data/professions/election_" . $value['id'] . "/" . $value['file'];
          }
        }
      } else {
        $return = NULL;
      }

      return $return;
    }

    public function get_last_explication($mpId, $legislature){
      $this->db->select('e.*, vd.title, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR');
      $this->db->select('CASE WHEN vs.vote = 0 THEN "abstention" WHEN vs.vote = 1 THEN "pour" WHEN vs.vote = -1 THEN "contre" WHEN vs.vote IS NULL THEN "absent" ELSE vs.vote END AS vote_depute', FALSE);
      $this->db->where('e.mpId', $mpId);
      $this->db->where('e.state', 1);
      $this->db->join('votes_info vi', 'vi.legislature = e.legislature AND vi.voteNumero = e.voteNumero');
      $this->db->join('votes_datan vd', 'vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero');
      $this->db->join('votes_scores vs', 'vs.legislature = e.legislature AND vs.voteNumero = e.voteNumero AND vs.mpId = e.mpId');
      $this->db->order_by('e.id', 'DESC');
      return $this->db->get('explications_mp e')->row_array();
    }

    public function get_explication($mpId, $legislature, $voteNumero){
      $where = array(
        'e.mpId' => $mpId,
        'e.legislature' => $legislature,
        'e.voteNumero' => $voteNumero,
        'e.state' => 1
      );
      $this->db->join('deputes_all d', 'e.mpId = d.mpId AND e.legislature = d.legislature', 'left');
      return $this->db->get_where('explications_mp e', $where, 1)->row_array();
    }


    public function get_photo_square($legislature)
    {
        return $legislature >= 17 ? TRUE : FALSE;
    }

    public function get_dptslug_by_name_url(string $name_url): ?string
    {
        $query = $this->db->query("SELECT dptslug FROM deputes_last WHERE nameUrl = ?", array($name_url));
        $result = $query->row();

        return $result ? $result->dptslug : null;
    }


  }
