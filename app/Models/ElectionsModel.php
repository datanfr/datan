<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionsModel extends Model {

  public function get_election($slug){
    return $this->db->table('elect_libelle')
      ->select('*, date_format(dateFirstRound, "%d %M %Y") as dateFirstRoundFr, date_format(dateSecondRound, "%d %M %Y") as dateSecondRoundFr')
      ->where('slug', $slug)
      ->get(1)
      ->getRowArray();
  }

  public function get_election_by_id($id){
    return $this->db->table('elect_libelle')
      ->where('id', $id)
      ->get(1)
      ->getRowArray();
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

    $results = $query->getResultArray();
    foreach($results as $x => $value){
      $results[$x]['state'] = $this->get_election_state($value['id']);
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

    $result = $this->db->table('elect_deputes_candidats')
      ->join('elect_libelle', 'elect_libelle.id = elect_deputes_candidats.election', 'left')
      ->where($where)
      ->get(1)
      ->getRowArray();

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

    return $this->db->table('elect_deputes_candidats')
      ->join('elect_libelle', 'elect_libelle.id = elect_deputes_candidats.election', 'left')
      ->orderBy('dateYear', 'DESC')
      ->where($where)
      ->get()
      ->getResultArray();
  }

  public function get_candidate_full($mpId, $election){
    $where = array(
      'mpId' => $mpId,
      'election' => $election
    );

    return $this->db->table('candidate_full')
      ->where($where)
      ->get(1)
      ->getRowArray();
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

    return $this->db->table('candidate_full')
      ->select('*, candidate_full.depute_libelle AS libelle, candidate_full.depute_libelleAbrev AS libelleAbrev')
      ->select('DATE_FORMAT(modified_at, "%d/%m/%Y") AS modified_at')
      ->select('legislature AS legislature_last')
      ->orderBy('nameLast', 'ASC')
      ->orderBy('nameFirst', 'ASC')
      ->where($where)
      ->get()
      ->getResultArray();
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
    return $this->db->query($sql, [legislature_current(), $election])->getResultArray();
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

    $builder = $this->db->table('candidate_full');
    foreach (array_merge($defaults, array_filter($optional)) as $key => $value) {
      $builder->where($key, $value);
    }

    return $builder->countAllResults();
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
    return $this->db->query($sql, [$id])->getResultArray();
  }

  public function count_non_candidats($id){
    $where = array(
      'election' => $id,
      'visible' => 1,
      'candidature'=> 0
    );
    return $this->db->table('candidate_full')
      ->where($where)
      ->countAllResults();
  }

  public function get_all_districts($election){
    if ($election == 1/*regionales 2021*/) {
      return $this->db->table('regions')
        ->orderBy('libelle', 'ASC')
        ->get()
        ->getResultArray();
    } elseif (in_array($election, array(2, 4, 6))/*departementales 2021 & législatives 2022*/) {
      return $this->db->table('departement')
        ->select('departement_code AS id, CONCAT(departement_code, " - ", departement_nom) AS libelle')
        ->orderBy('departement_code', 'ASC')
        ->get()
        ->getResultArray();
    } 
  }

  public function get_district($type, $district){
    if ($type === 'Régionales') {
      $result = $this->db->table('regions')->where('id', $district)->get()->getRowArray();
      $array = array(
        'id' => $result['id'],
        'libelle' => $result['libelle']
      );
      return $array;
    }

    if ($type === 'Législatives') {
      $result = $this->db->table('departement')->where('departement_code', $district)->get()->getRowArray();

      if($result){
        return [
          'id' => $result['departement_code'],
          'libelle' => $result['departement_nom']
        ];
      }
      return null;
    }

    if ($type === 'Municipales') {
      $overseasDepartments = ['975', '986', '977', '978', '987', '988'];
      if (in_array(substr($district, 0, 3), $overseasDepartments, true)) {
        $result = $this->db->table('circos c')
          ->select('c.*, c.dpt AS dep_code, c.dpt_nom AS dep_nom, ci.pop2017 AS population, CONCAT("de ", c.commune_nom) AS nom_de')
          ->join('departement d', 'c.dpt = d.departement_code')
          ->join('cities_infos ci', 'ci.insee = c.insee', 'left')
          ->where('c.insee', $district)
          ->get(1)
          ->getRowArray();
      } else {
        $result = $this->db->table('cities c')
          ->select('c.*, cc.commune_slug, d.slug AS dpt_slug')
          ->join('circos cc', 'c.code_insee = cc.insee')
          ->join('departement d', 'c.dep_code = d.departement_code')
          ->where('c.code_insee', $district)
          ->groupBy('c.code_insee')
          ->get(1)
          ->getRowArray();
      }
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
    return $this->db->table('elect_deputes_candidats e')
      ->join('deputes_last d', 'd.mpId = e.mpId', 'left')
      ->where([
        'e.election' => $election,
        'e.visible' => 1,
        'e.district' => $city,
      ])
      ->get()
      ->getResultArray();
  }

  public function get_candidates_by_dpt($dpt){
    $election = 7; // Municipales 2026
    return $this->db->table('elect_deputes_candidats e')
      ->join('deputes_last d', 'd.mpId = e.mpId', 'left')
      ->join('circos c', 'c.insee = e.district', 'left')
      ->groupBy('c.dpt')
      ->where([
        'e.election' => $election,
        'e.visible' => 1,
        'c.dpt' => $dpt,
      ])
      ->get()
      ->getResultArray();
  }

  public function get_n_candidates_by_group($group) {
    return $this->db->table('elect_deputes_candidats e')
      ->join('deputes_last d', 'd.mpId = e.mpId', 'left')
      ->where([
        'e.election' => 7, // Municipales 2026
        'e.visible' => 1,
        'd.groupeId' => $group,
      ])
      ->countAllResults();
  }

  public function get_n_candidates_all_groups() {
    return $this->db->table('candidate_full c')
      ->select('c.groupeId, c.depute_libelleAbrev AS libelleAbrev, c.couleurAssociee, ge.effectif, COUNT(*) AS candidates_n, c.legislature')
      ->select('ROUND(COUNT(*) / ge.effectif * 100) AS candidates_pct', false)
      ->join('groupes_effectif ge', 'ge.organeRef = c.groupeId', 'left')
      ->where('c.election', 7) // Municipales 2026
      ->where('c.visible', 1)
      ->where('c.active', true)
      ->groupBy('c.groupeId')
      ->orderBy('candidates_pct', 'DESC')
      ->get()
      ->getResultArray();
  }

  public function get_municipales_listes($id_election, $city, $arrondissements = FALSE){
    
    $builder = $this->db->table('elect_municipales_listes')
      ->where('id_election', $id_election);

    if ($arrondissements) {
      $builder->like('code_circonscription', $city, 'after') // starts with $city
        ->where('code_circonscription !=', $city) // should not be exactly $city
        ->orderBy('code_circonscription', 'ASC')
        ->orderBy('numero_panneau', 'ASC')
        ->orderBy('ordre', 'ASC');
    } else {
      $builder->where('code_circonscription', $city)
        ->orderBy('numero_panneau', 'ASC')
        ->orderBy('ordre', 'ASC');
    }

    $rows = $builder->get()->getResultArray();

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
    $results = $this->db->table('elect_bv_results r')
      ->select('r.*, SUM(r.voix) as voix_total')
      ->groupBy('r.no_panneau')
      ->orderBy('voix_total', 'desc')
      ->where(['r.id_election' => $election, 'r.code_commune' => $city])
      ->get()
      ->getResultArray();

    $total = array_sum(array_column($results, 'voix_total'));

    foreach ($results as &$row) {
      $row['voix_pct'] = $total > 0 ? round($row['voix_total'] / $total * 100, 2) : 0;
    }
    
    return $results;
  }

  public function get_results_city_municipales_ministry($city, $id_election){

    $results = $this->db->table('elect_results_cities_ministry')
      ->select('id_election, code_commune, no_panneau, voix, ratio_voix_inscrits, ratio_voix_exprimes, nuance, sexe, nom, prenom, seats, qualified')
      ->orderBy('voix', 'desc')
      ->orderBy('no_panneau', 'asc')
      ->where(['id_election' => $id_election, 'code_commune' => $city])
      ->get()
      ->getResultArray();

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

    // Fallback: if the ministry data didn't populate the `qualified` column for any row,
    // derive it from ratio_voix_inscrits (French municipal rule: >= 10% of registered voters).
    $hasAnyQualified = false;
    foreach ($results as $row) {
      if ((int) ($row['qualified'] ?? 0) === 1) {
        $hasAnyQualified = true;
        break;
      }
    }
    if (!$hasAnyQualified) {
      foreach ($results as &$row) {
        $ratioInscrits = isset($row['ratio_voix_inscrits']) ? (float) $row['ratio_voix_inscrits'] : null;
        if ($ratioInscrits !== null) {
          $row['qualified'] = $ratioInscrits >= 0.10 ? 1 : 0;
        } elseif ($row['voix_pct'] >= 10) {
          // last-resort fallback when ratio_voix_inscrits is also absent
          $row['qualified'] = 1;
        }
      }
      unset($row);
    }

    $results = $this->get_nuances_edited($results);
    
    return array(
      'id_election' => $id_election,
      'results' => $results,
    );
  }

  public function get_infos_city_municipales_ministry($city, $id_election){
    $result = $this->db->table('elect_results_cities_ministry_infos')
      ->select('code_commune, inscrits, abstentions, votants, blancs, nuls, exprimes, pct_saisis, pourvu')
      ->where('id_election', $id_election)
      ->where('code_commune', $city)
      ->get()
      ->getRowArray();

    if (empty($result)) {
      return array();
    }

    $result['inscrits'] = isset($result['inscrits']) ? (int) $result['inscrits'] : null;
    $result['abstentions'] = isset($result['abstentions']) ? (int) $result['abstentions'] : 0;
    $result['votants'] = isset($result['votants']) ? (int) $result['votants'] : 0;
    $result['blancs'] = isset($result['blancs']) ? (int) $result['blancs'] : 0;
    $result['nuls'] = isset($result['nuls']) ? (int) $result['nuls'] : 0;
    $result['exprimes'] = isset($result['exprimes']) ? (int) $result['exprimes'] : 0;
    $result['pct_saisis'] = isset($result['pct_saisis']) ? (float) $result['pct_saisis'] : null;
    $result['blancs_nuls'] = $result['blancs'] + $result['nuls'];
    $result['abstention_pct'] = $result['inscrits'] > 0
      ? round($result['abstentions'] / $result['inscrits'] * 100, 2)
      : 0;

    return $result;
  }

  public function isMunicipalesRoundPourvuFromResults($roundResults){
    $totalExprimes = 0;
    $maxVoix = 0;

    foreach ((array) $roundResults as $candidate) {
      $voix = isset($candidate['voix']) ? (int) $candidate['voix'] : 0;
      if ($voix < 0) {
        $voix = 0;
      }

      $totalExprimes += $voix;
      if ($voix > $maxVoix) {
        $maxVoix = $voix;
      }
    }

    if ($totalExprimes <= 0) {
      return FALSE;
    }

    return ($maxVoix / $totalExprimes) > 0.5;
  }

  public function count_results_cities_ministry($id_election, $onlyFullyCounted = FALSE){
    $builder = $this->db->table('elect_results_cities_ministry e')
      ->select('COUNT(DISTINCT e.code_commune) AS total', false)
      ->where('e.id_election', $id_election)
      ->where('e.code_commune <>', '')
      ->where('e.code_commune IS NOT NULL', null, false);

    if ($onlyFullyCounted) {
      $builder->join(
        'elect_results_cities_ministry_infos i',
        'i.id_election = e.id_election AND i.code_commune = e.code_commune',
        'inner'
      );
      $builder->where('i.pct_saisis', 100);
    }

    $result = $builder->get()->getRowArray();

    return isset($result['total']) ? (int) $result['total'] : 0;
  }

  public function get_city_circos($election, $city){
    return $this->db->table('elect_bv_circo')
      ->distinct()
      ->select('code_circo, libelle_circo')
      ->orderBy('code_circo', 'asc')
      ->where(['id_election' => $election, 'code_commune' => $city])
      ->get()
      ->getResultArray();
  }

  public function get_results_city_legislatives($election, $city, $code_circo = NULL){
    $builder = $this->db->table('elect_bv_results r')
      ->select('r.*, c.code_circo, c.libelle_circo, SUM(r.voix) as voix_total')
      ->join(
      'elect_bv_circo c', 
      'c.id_election = r.id_election AND c.code_commune = r.code_commune AND c.code_bv = r.code_bv', 
      'left');
    if ($code_circo !== NULL) {
      $builder->where('c.code_circo', $code_circo);
    }
    $results = $builder
      ->groupBy('r.no_panneau')
      ->orderBy('voix_total', 'desc')
      ->where(['r.id_election' => $election, 'r.code_commune' => $city])
      ->get()
      ->getResultArray();

    $total = array_sum(array_column($results, 'voix_total'));
    foreach ($results as &$row) {
        $row['voix_pct'] = $total > 0 ? round($row['voix_total'] / $total * 100, 2) : 0;
    }
    
    return $results;
  }

  public function get_infos_city($election, $city, $code_circo = NULL){
    $builder = $this->db->table('elect_bv_infos i')
      ->select('SUM(i.inscrits) as inscrits')
      ->select('SUM(i.abstentions) as abstentions')
      ->select('SUM(i.votants) as votants')
      ->select('SUM(i.blancs) as blancs')
      ->select('SUM(i.nuls) as nuls')
      ->select('SUM(i.exprimes) as exprimes')
      ->where('i.id_election', $election)
      ->where('i.code_commune', $city);

    if ($code_circo !== NULL) {
      $builder->join(
        'elect_bv_circo c',
        'c.id_election = i.id_election AND c.code_commune = i.code_commune AND c.code_bv = i.code_bv',
        'inner'
      );
      $builder->where('c.code_circo', $code_circo);
    }

    $result = $builder->get()->getRowArray();

    if (!empty($result)) {
      $result['blancs_nuls'] = $result['blancs'] + $result['nuls'];
      $result['abstention_pct'] = $result['inscrits'] > 0 ? round($result['abstentions'] / $result['inscrits'] * 100, 2) : 0;
    }

    return $result;
  }

  /**
   * Determine the T2 situation of each T1 list head.
   *
   * - Maintien   : voix_pct > 10 % AND same panneau exists in T2.
   * - Fusion     : voix_pct > 5 %  AND panneau absent from T2, but head of list
   *                (or any candidate from the T1 list) appears as a candidate on
   *                a different T2 list.
   *                The receiving list is also labelled fusion.
   * - Désistement: voix_pct > 10 % AND panneau absent from T2, and no candidate
   *                from the T1 list found on any T2 list.
   *
   * @param  array $t1Results  First-round rows from get_results_city_municipales_ministry().
   * @param  array $t2Listes   Second-round rows from get_municipales_listes().
   * @param  array $t1Listes   First-round rows from get_municipales_listes() (optional).
   * @return array             Each element: head_name, nuance, voix_pct, situation,
   *                           and (for fusion) fusion_with_head, fusion_with_nuance.
   */
  public function get_list_situations($t1Results, $t2Listes, $t1Listes = array())
  {
    $normalizeName = function ($name) {
      $name = trim((string) $name);
      if ($name === '') {
        return '';
      }
      return mb_strtolower(preg_replace('/\s+/u', ' ', $name), 'UTF-8');
    };

    // Index T2 by panneau number for O(1) lookup
    $t2ByPanneau = array();
    foreach ($t2Listes as $liste) {
      $t2ByPanneau[(string) ($liste['numero_panneau'] ?? '')] = $liste;
    }

    // Build a normalized-name → liste map for all T2 candidates
    $t2CandidateToListe = array();
    foreach ($t2Listes as $liste) {
      foreach (($liste['candidats'] ?? array()) as $candidat) {
        $fullName = trim((string) (($candidat['prenom'] ?? '') . ' ' . ($candidat['nom'] ?? '')));
        $key = $normalizeName($fullName);
        if ($key !== '') {
          $t2CandidateToListe[$key] = $liste;
        }
      }
    }

    // Index T1 listes candidates by panneau for fallback fusion detection
    $t1CandidatesByPanneau = array();
    foreach ($t1Listes as $liste) {
      $panneau = (string) ($liste['numero_panneau'] ?? '');
      if ($panneau === '') {
        continue;
      }
      foreach (($liste['candidats'] ?? array()) as $candidat) {
        $fullName = trim((string) (($candidat['prenom'] ?? '') . ' ' . ($candidat['nom'] ?? '')));
        $key = $normalizeName($fullName);
        if ($key !== '') {
          $t1CandidatesByPanneau[$panneau][] = $key;
        }
      }
    }

    // First pass: detect donor fusions and build receiver index by panneau
    $donorFusionByPanneau = array();
    $receivedByPanneau = array();

    foreach ($t1Results as $candidate) {
      $score    = (float) ($candidate['voix_pct'] ?? 0);
      $panneau  = (string) ($candidate['no_panneau'] ?? '');
      $headName = trim((string) (($candidate['prenom'] ?? '') . ' ' . ($candidate['nom'] ?? '')));
      $headKey  = $normalizeName($headName);
      $nuance   = (string) ($candidate['nuance_edited'] ?? $candidate['nuance'] ?? '');

      if ($score <= 5 || isset($t2ByPanneau[$panneau]) || $headKey === '') {
        continue;
      }

      // Try to find a matching T2 list: first by head name, then by any T1 list candidate
      $fusionListe = null;
      if (isset($t2CandidateToListe[$headKey])) {
        $fusionListe = $t2CandidateToListe[$headKey];
      } elseif (!empty($t1CandidatesByPanneau[$panneau])) {
        foreach ($t1CandidatesByPanneau[$panneau] as $candidateKey) {
          if (isset($t2CandidateToListe[$candidateKey])) {
            $fusionListe = $t2CandidateToListe[$candidateKey];
            break;
          }
        }
      }

      if ($fusionListe === null) {
        continue;
      }

      $receiverPanneau = (string) ($fusionListe['numero_panneau'] ?? '');

      if ($receiverPanneau === '' || $receiverPanneau === $panneau) {
        continue;
      }

      $fusionHead = trim((string) ($fusionListe['tete_de_liste'] ?? ''));
      if ($fusionHead === '' && !empty($fusionListe['candidats'][0])) {
        $fusionHead = trim((string) (($fusionListe['candidats'][0]['prenom'] ?? '') . ' ' . ($fusionListe['candidats'][0]['nom'] ?? '')));
      }

      $donorFusionByPanneau[$panneau] = array(
        'head_name'           => $headName,
        'nuance'              => $nuance,
        'voix_pct'            => round($score, 2),
        'situation'           => 'fusion',
        'fusion_role'         => 'donor',
        'fusion_with_head'    => $fusionHead,
        'fusion_with_nuance'  => (string) ($fusionListe['nuance_edited'] ?? $fusionListe['nuance'] ?? ''),
      );

      if (!isset($receivedByPanneau[$receiverPanneau])) {
        $receivedByPanneau[$receiverPanneau] = array();
      }
      $receivedByPanneau[$receiverPanneau][] = array(
        'head_name' => $headName,
        'nuance' => $nuance,
      );
    }

    $situations = array();

    foreach ($t1Results as $candidate) {
      $score    = (float) ($candidate['voix_pct'] ?? 0);
      $panneau  = (string) ($candidate['no_panneau'] ?? '');
      $headName = trim((string) (($candidate['prenom'] ?? '') . ' ' . ($candidate['nom'] ?? '')));
      $nuance   = (string) ($candidate['nuance_edited'] ?? $candidate['nuance'] ?? '');

      if (isset($donorFusionByPanneau[$panneau])) {
        $situations[] = $donorFusionByPanneau[$panneau];
        continue;
      }

      if (isset($t2ByPanneau[$panneau])) {
        if ($score > 10) {
          if (!empty($receivedByPanneau[$panneau])) {
            $fusionFromHeads = array();
            foreach ($receivedByPanneau[$panneau] as $received) {
              $fusionFromHeads[] = $received['head_name'];
            }

            $situations[] = array(
              'head_name'           => $headName,
              'nuance'              => $nuance,
              'voix_pct'            => round($score, 2),
              'situation'           => 'fusion',
              'fusion_role'         => 'receiver',
              'fusion_from_heads'   => $fusionFromHeads,
              // Kept for backward compatibility where a single counterpart is expected.
              'fusion_with_head'    => $fusionFromHeads[0] ?? '',
              'fusion_with_nuance'  => '',
            );
          } else {
            $situations[] = array(
              'head_name'  => $headName,
              'nuance'     => $nuance,
              'voix_pct'   => round($score, 2),
              'situation'  => 'maintien',
            );
          }
        }
        continue;
      }

      if ($score > 10) {
        $situations[] = array(
          'head_name'  => $headName,
          'nuance'     => $nuance,
          'voix_pct'   => round($score, 2),
          'situation'  => 'desistement',
        );
      }
    }

    return $situations;
  }

}