<?php
  class Elections_model extends CI_Model {
    public function __construct() {
      $this->load->database();
      $this->load->model('city_model');
    }

    public function get_election($slug){
      $where = array(
        'slug' => $slug
      );
      $this->db->select('*, date_format(dateFirstRound, "%d %M %Y") as dateFirstRoundFr, date_format(dateSecondRound, "%d %M %Y") as dateSecondRoundFr');

      $query = $this->db->get_where('elect_libelle', $where, 1);

      return $query->row_array();
    }

    public function get_election_by_id($id){
      $where = array(
        'id' => $id
      );

      $query = $this->db->get_where('elect_libelle', $where, 1);

      return $query->row_array();
    }

    public function get_election_all(){
      $query = $this->db->query('
      SELECT *, date_format(e.dateFirstRound, "%d %M") as dateFirstRoundFr, date_format(e.dateSecondRound, "%d %M") as dateSecondRoundFr, candidatsN
      FROM elect_libelle e
      LEFT JOIN (
        SELECT election,
          COUNT(mpId) AS candidatsN,
          SUM(case when secondRound = 1 then 1 else 0 end) as secondRoundN,
          SUM(case when elected = 1 then 1 else 0 end) as electedN
        FROM elect_deputes_candidats
        WHERE visible = 1 AND candidature = 1
        GROUP BY election) c ON e.id = c.election
      ORDER BY e.dateYear DESC
      ');

      $results = $query->result_array();
      foreach($results as $x => $value){
        $results[$x]['state'] = $this->elections_model->get_election_state($value['id']);
      }

      return $results;
    }

    public function get_election_infos($type){
      if ($type == "Régionales") {
        $info = "
        <p>Les élections régionales ont lieu <b>tous les six ans</b>, en même temps que les départementales. Elles permettent d'élire les <b>conseillers régionaux</b> qui composent le conseil régional.</p>
        <p>Les compétences des régions incluent le développement économique, les transports, la construction des lycées et la formation professionnelle.</p>
        <p>Les électeurs votent pour une <b>liste politique</b>. L'élection se déroule en <b>deux tours</b> : si aucune liste n'obtient plus de 50% au premier tour, un second tour est organisé entre les listes ayant recueilli au moins <b>10%</b> des suffrages.</p>
        <p>La liste arrivée en tête reçoit <b>25% des sièges</b>, le reste étant réparti à la proportionnelle entre toutes les listes ayant obtenu au moins <b>5%</b>.</p>
        ";
      } elseif ($type == "Départementales") {
        $info = "
        <p>Les élections départementales ont lieu <b>tous les six ans</b>, en même temps que les régionales. Elles permettent d'élire les <b>conseillers départementaux</b> qui composent le conseil départemental.</p>
        <p>Les conseils départementaux ont des compétences variées : action sociale (aide à l'enfance, handicap, maisons de retraite), éducation (construction et entretien des collèges), voirie départementale et action culturelle.</p>
        <p>Les candidats se présentent en <b>binôme mixte</b> (un homme, une femme). L'élection se déroule en <b>deux tours</b> : un binôme est élu au premier tour s'il obtient la majorité absolue, sinon un second tour est organisé entre les binômes ayant recueilli au moins <b>12,5% des inscrits</b>. Le binôme arrivé en tête l'emporte.</p>
        ";
      } elseif ($type == "Législatives") {
        $info = "
        <p>Les élections législatives ont lieu <b>tous les cinq ans</b>, quelques semaines après la présidentielle. Elles permettent d'élire les <b>577 députés</b> de l'Assemblée nationale.</p>
        <p>L'élection se déroule en <b>deux tours</b> : un candidat est élu au premier tour s'il obtient la majorité absolue, sinon un second tour est organisé entre les candidats ayant recueilli au moins <b>12,5% des inscrits</b>. Le candidat arrivé en tête est élu.</p>
        <p>Les députés votent la loi, contrôlent le gouvernement et représentent leur circonscription. <a href='https://datan.fr/faq' target='_blank'>En savoir plus</a>.</p>
        ";
      } elseif ($type == "Présidentielle") {
        $info = "
        <p>L'élection présidentielle a lieu <b>tous les cinq ans</b>, quelques semaines avant les législatives. Elle permet d'élire le président de la République.</p>
        <p>Pour être candidat, il faut obtenir le parrainage de <b>500 élus</b>. L'élection se déroule en <b>deux tours</b> : un candidat est élu au premier tour s'il obtient la majorité absolue, sinon un second tour est organisé entre les deux candidats arrivés en tête.</p>
        <p>Le président veille au respect de la Constitution, nomme le Premier ministre, préside le Conseil des ministres et promulgue les lois. <a href='https://www.vie-publique.fr/infographie/23816-infographie-quel-est-le-role-du-president-de-la-republique' target='_blank' rel='nofollow noreferrer noopener'>En savoir plus</a></p>
        ";
      } elseif ($type == "Européennes") { 
        $info = "
        <p>Les élections européennes ont lieu <b>tous les cinq ans</b> et permettent d'élire les députés européens, chargés d'élire la Commission européenne et de voter les lois européennes (règlements et directives).</p>
        <p>En France, l'élection se fait au <b>scrutin proportionnel de liste</b>. Les listes obtenant au moins <b>5%</b> des suffrages se voient attribuer des sièges proportionnellement à leur score.</p>
        ";
      } elseif($type == "Municipales") {
        $info = "
        <p>Les élections municipales ont lieu <b>tous les six ans</b> et permettent d'élire les conseillers municipaux de chaque commune, ainsi que le maire et ses adjoints.</p>
        <p>L'élection se fait au <b>scrutin proportionnel de liste</b>. Si une liste obtient la majorité absolue au premier tour, elle remporte la moitié des sièges, le reste étant réparti entre les listes ayant obtenu au moins 5%. Sinon, un second tour est organisé entre les listes ayant recueilli au moins 10% des suffrages. Le conseil municipal élit ensuite le maire.</p>
        ";
      } else {
        $info = NULL;
      }

      return $info;
    }

    public function get_map_legend($id){
      if ($id == 1 /*regionales-2021*/) {
        $array = array(
          array("party" => "Parti socialiste (PS)", "color" => "#ff8080"),
          array("party" => "Divers gauche", "color" => "#ffc0c0"),
          array("party" => "Les Centristes", "color" => "#0FFFFF"),
          array("party" => "Divers droite", "color" => "#adc1fd"),
          array("party" => "Les Républicains", "color" => "#0066cc"),
          array("party" => "Régionalistes", "color" => "#FBCC33"),
        );
        return $array;
      }
      if ($id == 2 /*departementales-2021*/) {
        $array = array(
          array("party" => "Les Républicains (LR)", "color" => "#0066CC"),
          array("party" => "Union des démocrates et indépendants (UDI)", "color" => "#00FFFF"),
          array("party" => "Divers droite", "color" => "#ADC1FD"),
          array("party" => "La République en marche (LREM)", "color" => "#FFEB00"),
          array("party" => "Mouvement démocrate (MoDEM)", "color" => "#FF9900"),
          array("party" => "Divers", "color" => "#CCCCCC"),
          array("party" => "Divers gauche", "color" => "#FFC0C0"),
          array("party" => "Parti radical de gauche (PRG)", "color" => "#FFD1DC"),
          array("party" => "Parti socialiste (PS)", "color" => "#FF8080"),
        );
        return $array;
      }
    }

    public function get_candidate_election($mpId, $election, $visible = FALSE, $candidature = FALSE){
      $where = array(
        'mpId' => $mpId,
        'election' => $election
      );
      if ($visible) {
        $where['visible'] = 1;
      }
      if ($candidature) {
        $where['candidature'] = 1;
      }

      $this->db->join('elect_libelle', 'elect_libelle.id = elect_deputes_candidats.election', 'left');
      $result = $this->db->get_where('elect_deputes_candidats', $where, 1)->row_array();

      if($result){
        if ($result['elected'] == "1") {
          $result['color'] = 'results-success';
        } elseif ($result['elected'] == "0" || $result['secondRound'] == "0") {
          $result['color'] = 'results-fail';
        } elseif ($result['secondRound'] == "1") {
          $result['color'] = 'information-success';
        } elseif ($result['candidature'] == "1") {
          $result['color'] = 'information-success';
        } elseif ($result['candidature'] == "0") {
          $result['color'] = 'information-fail';
        }
      }

      return $result;
    }

    public function get_candidate_elections($mpId, $visible = FALSE, $candidature = FALSE){
      $where = array(
        'mpId' => $mpId,
      );
      if ($visible) {
        $where['visible'] = 1;
      }
      if ($candidature) {
        $where['candidature'] = 1;
      }

      $this->db->join('elect_libelle', 'elect_libelle.id = elect_deputes_candidats.election', 'left');
      $this->db->order_by('dateYear', 'DESC');
      return $this->db->get_where('elect_deputes_candidats', $where)->result_array();
    }

    public function get_candidate_full($mpId, $election){
      $where = array(
        'mpId' => $mpId,
        'election' => $election
      );

      $query = $this->db->get_where('candidate_full', $where, 1);

      return $query->row_array();
    }


    public function get_all_candidates($election, $visible = FALSE, $candidature = FALSE, $state = FALSE){
      $where['election'] = $election;
      if ($visible) {
        $where['visible'] = 1;
      }
      if ($candidature) {
        $where['candidature'] = 1;
      }

      if ($state == 'second') {
        $where['secondRound'] = '1';
      }

      if ($state == 'elected') {
        $where['elected'] = '1';
      }

      $this->db->select('*, candidate_full.depute_libelle AS libelle, candidate_full.depute_libelleAbrev AS libelleAbrev');
      $this->db->select('DATE_FORMAT(modified_at, "%d/%m/%Y") AS modified_at');
      $this->db->select('legislature AS legislature_last');
      $this->db->order_by('nameLast', 'ASC');
      $this->db->order_by('nameFirst', 'ASC');
      $query = $this->db->get_where('candidate_full', $where);

      return $query->result_array();
    }

    public function get_mps_not_done($election){
      $sql = 'SELECT *
        FROM deputes_last dl
        WHERE dl.legislature = ?
        AND dl.mpId NOT IN (
          SELECT c.mpId
          FROM candidate_full c
          WHERE c.election = ?
        )
      ';
      return $this->db->query($sql, array(legislature_current(), $election))->result_array();
    }

    public function count_candidats($id, $filters = []) {
      $defaults = [
        'election'    => $id,
        'visible'     => 1,
        'candidature' => 1,
      ];

      $optional = [
        'secondRound' => !empty($filters['second']),
        'elected'     => !empty($filters['end']),
        'active'      => !empty($filters['active']),
        'position'    => $filters['position'] ?? null,
      ];

      foreach (array_merge($defaults, array_filter($optional)) as $key => $value) {
        $this->db->where($key, $value);
      }

      return $this->db->count_all_results('candidate_full');
    }

    public function count_candidats_eliminated($id){
      $sql = 'SELECT *
        FROM candidate_full c
        WHERE c.election = ?
          AND candidature = 1
          AND visible = 1
          AND (
            elected = 0 OR secondRound = 0
          )
      ';
      return $this->db->query($sql, $id)->result_array();
    }

    public function count_non_candidats($id){
      $where = array(
        'election' => $id,
        'visible' => 1,
        'candidature'=> 0
      );
      $this->db->where($where);
      return $this->db->count_all_results('candidate_full');
    }

    public function get_all_districts($election){
      if ($election == 1/*regionales 2021*/) {
        $this->db->order_by('libelle', 'ASC');
        $query = $this->db->get('regions');
        return $query->result_array();
      } elseif (in_array($election, array(2, 4, 6))/*departementales 2021 & législatives 2022*/) {
        $this->db->select('departement_code AS id, CONCAT(departement_code, " - ", departement_nom) AS libelle');
        $this->db->order_by('departement_code', 'ASC');
        $query = $this->db->get('departement');
        return $query->result_array();
      } 
    }

    public function get_district($type, $district){
      if ($type === 'Régionales') {
        $result = $this->db->get_where('regions', array('id' => $district))->row_array();
        $array = array(
          'id' => $result['id'],
          'libelle' => $result['libelle']
        );
        return $array;
      }

      if ($type === 'Législatives') {
        $result = $this->db->get_where('departement', array('departement_code' => $district))->row_array();

        if($result){
          return [
            'id' => $result['departement_code'],
            'libelle' => $result['departement_nom']
          ];
        }
        return null;
      }

      if ($type === 'Municipales') {
        $result = $this->city_model->get_city_by_insee($district);
        if($result){
          return [
            'id' => $result['dep_code'],
            'libelle' => $result['nom_standard']
          ];
        }
        return null;
      }
    }

    public function get_election_state($id){
      // State values:
      // 0 - First round not yet started.
      // 1 - First round completed.
      // 2 - Second round completed, or only one round is required.

      switch ($id) {
        case 1: // Régionales 2021
          return 2;
          break;

        case 2: // Départementales 2021
          return 2;
          break;

        case 3: // Présidentielle 2022;
          return 2;
          break;
        
        case 4: // Législatives 2022 
          return 2;
          break;

        case 5: // Européennes 2024
          return 2;
          break;
        
        case 6: // Législatives 2024
          return 2;
          break;

        case 7: // Municipales 2026
          return 0;
          break;
        
        default:
          return 0;
          break;
      }
    }

    public function get_state($second, $elected){
      if ($second === "1" & $elected == NULL) {
        return "second";
      } elseif ($second === "0" & $elected == NULL) {
        return "lost";
      } elseif ($elected === "1") {
        return "elected";
      } elseif ($elected === "0") {
        return "lost";
      }
    }

    public function get_candidates_by_city($city){
      $election = 7; // Municipales 2026
      $where = array(
        'e.election' => $election,
        'e.visible' => 1,
        'e.district' => $city,
      );
      $this->db->join('deputes_last d', 'd.mpId = e.mpId', 'left');
      return $this->db->get_where('elect_deputes_candidats e', $where)->result_array();
    }

    public function get_candidates_by_dpt($dpt){
      $election = 7; // Municipales 2026
      $where = array(
        'e.election' => $election,
        'e.visible' => 1,
        'c.dpt' => $dpt
      );
      $this->db->join('deputes_last d', 'd.mpId = e.mpId', 'left');
      $this->db->join('circos c', 'c.insee = e.district', 'left');
      $this->db->group_by('c.dpt');
      return $this->db->get_where('elect_deputes_candidats e', $where)->result_array();
    }

    public function get_n_candidates_by_group($group) {
      $where = array(
        'e.election' => 7, // Municipales 2026
        'e.visible' => 1,
        'd.groupeId' => $group
      );
      $this->db->join('deputes_last d', 'd.mpId = e.mpId', 'left');
      $this->db->where($where);
      return $this->db->count_all_results('elect_deputes_candidats e');
    }

    public function get_n_candidates_all_groups() {
      $this->db->select('c.groupeId, c.depute_libelleAbrev AS libelleAbrev, c.couleurAssociee, ge.effectif, COUNT(*) AS candidates_n, c.legislature');
      $this->db->select('ROUND(COUNT(*) / ge.effectif * 100) AS candidates_pct', FALSE);
      $this->db->from('candidate_full c');
      $this->db->join('groupes_effectif ge', 'ge.organeRef = c.groupeId', 'left');
      $this->db->where('c.election', 7); // Municipales 2026
      $this->db->where('c.visible', 1);
      $this->db->where('c.active', TRUE);
      $this->db->group_by('c.groupeId');
      $this->db->order_by('candidates_pct', 'DESC');
      return $this->db->get()->result_array();
    }

    public function get_municipales_listes($city, $arrondissements = FALSE){
      
      if ($arrondissements) {
        $this->db->like('code_circonscription', $city, 'after'); // starts with $city
        $this->db->where('code_circonscription !=', $city); // should not be exactly $city
        $this->db->order_by('code_circonscription', 'ASC');
        $this->db->order_by('numero_panneau', 'ASC');
        $this->db->order_by('ordre', 'ASC');
      } else {
        $this->db->where('code_circonscription', $city);
        $this->db->order_by('numero_panneau', 'ASC');
        $this->db->order_by('ordre', 'ASC');
      }

      $rows = $this->db->get('elect_municipales_listes')->result_array();

      if ($arrondissements) {
        
        // 3 dimensional: arrondissement / listes / candidats
        $result = [];
        foreach ($rows as $row) {
          // Extract last 2 digits of code_circonscription e.g. "75056SR01" -> "01"
          $arr_number = intval(substr($row['code_circonscription'], -2));
          $arr_label  = $arr_number . 'e arrondissement';
          $panneau    = $row['code_circonscription'] . '_' . $row['numero_panneau']; // unique key per arrondissement+list

          // Init arrondissement
          if (!isset($result[$arr_label])) {
            $result[$arr_label] = [];
          }

          // Init list inside arrondissement
          if (!isset($result[$arr_label][$panneau])) {
            $result[$arr_label][$panneau] = [
              'numero_panneau' => $row['numero_panneau'],
              'libelle_abrege' => $row['libelle_abrege_liste'],
              'libelle_liste'  => $row['libelle_liste'],
              'code_nuance'    => $row['code_nuance'],
              'nuance'         => $row['nuance'],
              'tete_de_liste'  => null,
              'candidats'      => []
            ];
          }

          // Store tête de liste
          if ($row['tete_de_liste'] === 'OUI') {
            $result[$arr_label][$panneau]['tete_de_liste'] = $row['prenom'] . ' ' . mb_convert_case(mb_strtolower($row['nom'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
          }

          // Add candidate
          $result[$arr_label][$panneau]['candidats'][] = [
            'ordre'             => $row['ordre'],
            'nom'               => $row['nom'],
            'prenom'            => $row['prenom'],
            'sexe'              => $row['sexe'],
            'nationalite'       => $row['nationalite'],
            'code_personnalite' => $row['code_personnalite']
          ];
        }

        // Sort by arrondissement number and reindex lists
        uksort($result, function($a, $b) {
          return intval($a) - intval($b);
        });

        return $result;
      
      } else {
        
        // 2 dimensional: listes / candidats (original behavior)
        $listes = [];
        foreach ($rows as $row) {
          $panneau = $row['numero_panneau'];
          
          if (!isset($listes[$panneau])) {
            $listes[$panneau] = [
              'numero_panneau' => $row['numero_panneau'],
              'libelle_abrege' => $row['libelle_abrege_liste'],
              'libelle_liste'  => $row['libelle_liste'],
              'code_nuance'    => $row['code_nuance'],
              'nuance'         => $row['nuance'],
              'tete_de_liste'  => null,
              'candidats'      => []
            ];
          }
          
          if ($row['tete_de_liste'] === 'OUI') {
            $listes[$panneau]['tete_de_liste'] = $row['prenom'] . ' ' . mb_convert_case(mb_strtolower($row['nom'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
          }

          $listes[$panneau]['candidats'][] = [
            'ordre'             => $row['ordre'],
            'nom'               => $row['nom'],
            'prenom'            => $row['prenom'],
            'sexe'              => $row['sexe'],
            'nationalite'       => $row['nationalite'],
            'code_personnalite' => $row['code_personnalite']
          ];
        }

        return array_values($listes);

      }
    }

    public function get_nuances_edited($listes){
      $nuanceMap = [
        'LCOM' => ['label' => 'PCF',                    'color' => '#e4001f'],
        'LDIV' => ['label' => 'Divers',                 'color' => '#888888'],
        'LDSV' => ['label' => 'Droite souverainiste',   'color' => '#003189'],
        'LDVC' => ['label' => 'Divers centre',          'color' => '#ffcc00'],
        'LDVD' => ['label' => 'Divers droite',          'color' => '#0a3ec2'],
        'LDVG' => ['label' => 'Divers gauche',          'color' => '#ff8c69'],
        'LECO' => ['label' => 'Divers écologiste',      'color' => '#2e9e44'],
        'LEXD' => ['label' => 'Extrême droite',         'color' => '#302a6e'],
        'LEXG' => ['label' => 'Extrême gauche',         'color' => '#8B0000'],
        'LFI'  => ['label' => 'LFI',                    'color' => '#cc0000'],
        'LHOR' => ['label' => 'Horizons',               'color' => '#00a0c8'],
        'LLR'  => ['label' => 'Les Républicains',       'color' => '#0033a0'],
        'LMDM' => ['label' => 'MODEM',                  'color' => '#f07800'],
        'LREC' => ['label' => 'Reconquête !',           'color' => '#0d2f6e'],
        'LREG' => ['label' => 'Régionalistes',          'color' => '#66bb6a'],
        'LREN' => ['label' => 'Renaissance',            'color' => '#e8b31a'],
        'LRN'  => ['label' => 'Rassemblement national', 'color' => '#0d378a'],
        'LSOC' => ['label' => 'Parti socialiste',       'color' => '#e5007d'],
        'LUC'  => ['label' => 'Union du centre',           'color' => '#ffcc00'],
        'LUD'  => ['label' => 'Union de la droite',           'color' => '#0a3ec2'],
        'LUDI' => ['label' => 'UDI',                    'color' => '#00adef'],
        'LUDR' => ['label' => 'UDR',                    'color' => '#1a1a6e'],
        'LUG'  => ['label' => 'Union de la gauche',           'color' => '#cc0000'],
        'LUXD' => ['label' => 'Union d\'extrême droite',   'color' => '#302a6e'],
      ];

      foreach ($listes as $key => $liste) {
        $nuance = $nuanceMap[$liste['code_nuance']] ?? null;
        $listes[$key]['nuance_edited'] = $nuance ? $nuance['label'] : $liste['code_nuance'];
        $listes[$key]['nuance_color']  = $nuance ? $nuance['color'] : '#888888';
      }

      return $listes;
    }

    public function get_results_city($election, $city){
      $where = array(
        'r.id_election' => $election,
        'r.code_commune' => $city
      );
      $this->db->select('r.*, SUM(r.voix) as voix_total');
      $this->db->group_by('r.no_panneau');
      $this->db->order_by('voix_total', 'desc');
      $results = $this->db->get_where('elect_bv_results r', $where)->result_array();

      $total = array_sum(array_column($results, 'voix_total'));

      foreach ($results as &$row) {
        $row['voix_pct'] = $total > 0 ? round($row['voix_total'] / $total * 100, 2) : 0;
      }
      
      return $results;
    }

    public function get_results_city_municipales_ministry($city, $id_election){

      $where = array(
        'id_election' => $id_election,
        'code_commune' => $city,
      );

      $this->db->select('id_election, code_commune, no_panneau, voix, ratio_voix_inscrits, ratio_voix_exprimes, nuance, sexe, nom, prenom, seats');
      $this->db->order_by('voix', 'desc');
      $this->db->order_by('no_panneau', 'asc');
      $results = $this->db->get_where('elect_results_cities_ministry', $where)->result_array();

      if (empty($results)) {
        return array(
          'id_election' => $id_election,
          'results' => array(),
        );
      }

      $total = array_sum(array_map(function($row) {
        return (int) ($row['voix'] ?? 0);
      }, $results));

      foreach ($results as &$row) {
        
        $ratio_exprimes = isset($row['ratio_voix_exprimes']) ? (float) $row['ratio_voix_exprimes'] : NULL;
        $row['voix_pct'] = $ratio_exprimes !== NULL
          ? round($ratio_exprimes, 2)
          : ($total > 0 ? round(((int) ($row['voix'] ?? 0) / $total) * 100, 2) : 0);
        $row['code_nuance'] = $row['nuance'];        
      }
      unset($row);

      $results = $this->elections_model->get_nuances_edited($results);
      
      return array(
        'id_election' => $id_election,
        'results' => $results,
      );
    }

    public function get_infos_city_municipales_ministry($city, $id_election){
      $this->db->select('code_commune, inscrits, abstentions, votants, blancs, nuls, exprimes, pourvu');
      $this->db->where('id_election', $id_election);
      $this->db->where('code_commune', $city);

      $result = $this->db->get('elect_results_cities_ministry_infos')->row_array();

      if (empty($result)) {
        return array();
      }

      $result['inscrits'] = isset($result['inscrits']) ? (int) $result['inscrits'] : null;
      $result['abstentions'] = isset($result['abstentions']) ? (int) $result['abstentions'] : 0;
      $result['votants'] = isset($result['votants']) ? (int) $result['votants'] : 0;
      $result['blancs'] = isset($result['blancs']) ? (int) $result['blancs'] : 0;
      $result['nuls'] = isset($result['nuls']) ? (int) $result['nuls'] : 0;
      $result['exprimes'] = isset($result['exprimes']) ? (int) $result['exprimes'] : 0;
      $result['blancs_nuls'] = $result['blancs'] + $result['nuls'];
      $result['abstention_pct'] = $result['inscrits'] > 0
        ? round($result['abstentions'] / $result['inscrits'] * 100, 2)
        : 0;

      return $result;
    }

    public function count_results_cities_ministry($id_election){
      $this->db->select('COUNT(DISTINCT code_commune) AS total', FALSE);
      $this->db->where('id_election', $id_election);
      $this->db->where('code_commune <>', '');
      $this->db->where('code_commune IS NOT NULL', NULL, FALSE);

      $result = $this->db->get('elect_results_cities_ministry')->row_array();

      return isset($result['total']) ? (int) $result['total'] : 0;
    }

    public function get_city_circos($election, $city){
      $where = array(
        'id_election' => $election,
        'code_commune' => $city
      );
      $this->db->distinct();
      $this->db->select('code_circo, libelle_circo');
      $this->db->order_by('code_circo', 'asc');

      return $this->db->get_where('elect_bv_circo', $where)->result_array();
    }

    public function get_results_city_legislatives($election, $city, $code_circo = NULL){
      $where = array(
        'r.id_election' => $election,
        'r.code_commune' => $city
      );
      $this->db->select('r.*, c.code_circo, c.libelle_circo, SUM(r.voix) as voix_total');
      $this->db->join(
        'elect_bv_circo c', 
        'c.id_election = r.id_election AND c.code_commune = r.code_commune AND c.code_bv = r.code_bv', 
        'left');
      if ($code_circo !== NULL) {
        $this->db->where('c.code_circo', $code_circo);
      }
      $this->db->group_by('r.no_panneau');
      $this->db->order_by('voix_total', 'desc');
      $results = $this->db->get_where('elect_bv_results r', $where)->result_array();

      $total = array_sum(array_column($results, 'voix_total'));
      foreach ($results as &$row) {
          $row['voix_pct'] = $total > 0 ? round($row['voix_total'] / $total * 100, 2) : 0;
      }
      
      return $results;
    }

    public function get_infos_city($election, $city, $code_circo = NULL){
      $this->db->select('SUM(i.inscrits) as inscrits');
      $this->db->select('SUM(i.abstentions) as abstentions');
      $this->db->select('SUM(i.votants) as votants');
      $this->db->select('SUM(i.blancs) as blancs');
      $this->db->select('SUM(i.nuls) as nuls');
      $this->db->select('SUM(i.exprimes) as exprimes');
      $this->db->where('i.id_election', $election);
      $this->db->where('i.code_commune', $city);

      if ($code_circo !== NULL) {
        $this->db->join(
          'elect_bv_circo c',
          'c.id_election = i.id_election AND c.code_commune = i.code_commune AND c.code_bv = i.code_bv',
          'inner'
        );
        $this->db->where('c.code_circo', $code_circo);
      }

      $result = $this->db->get('elect_bv_infos i')->row_array();

      if (!empty($result)) {
        $result['blancs_nuls'] = $result['blancs'] + $result['nuls'];
        $result['abstention_pct'] = $result['inscrits'] > 0 ? round($result['abstentions'] / $result['inscrits'] * 100, 2) : 0;
      }

      return $result;
    }

  }
