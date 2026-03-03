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
        <p><b>Les élections régionales ont lieu tous les six ans</b>, en même temps que les élections départementales.</p>
        <p>Les élections régionales permettent d'<b>élire les conseillers régionaux</b> qui composent le conseil régional. Ce sont les conseillers régionaux qui décident et votent sur les affaires de la région.</p>
        <p><b>Les compétences des régions sont multiples</b>. Il s'agit par exemple du développement économique et social, de l'organisation du transport, de la construction des lycées, ou encore de l'organisation de la formation professionnelle.</p>
        <p>Les candidats se rassemblent sur des listes politiques, en fonction de leur parti d'appartenance. Le jour de l'élection, les électeurs votent donc pour une liste.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si aucune liste obtient plus de 50% des suffrages exprimés, un second tour est organisé avec uniquement les listes ayant récolté plus de 10% des voix.</p>
        <p><b>La liste arrivant en tête se voit attribuer 25% des sièges</b> du conseil régional. Pour les sièges restants, ils sont attribués à la proportionnelle entre toutes les listes ayant obtenu au moins 5% des voix.</p>
        ";
      } elseif ($type == "Départementales") {
        $info = "
        <p><b>Les élections départementales ont lieu tous les six ans</b>, en même temps que les élections régionales.</p>
        <p>Les élections départementales permettent d'<b>élire les conseillers départementaux</b> qui composent le conseil régional. Ce sont les conseillers départementaux qui décident et votent sur les affaires de la région. Avant 2013, les conseillers départementaux étaient appelés conseillers généraux.</p>
        <p><b>Les compétences des régions sont multiples</b>. La compétence la plus importante est sociale. Les conseils départementaux gèrent l'aide sociale à l'enfant, les politiques d'hebergement et d'insertion des personnes handicapées, ou encore la gestion de maisons de retraite. Les conseils départementaux ont également des compétences en matière d'éducation (construction et entretien de collèges), la voirie départementale, ou encore l'action culturelle et sportive.</p>
        <p>Les candidats doivent former un <b>binôme mixte</b> (une candidat femme et un candidat homme). Le jours de l'élection, les électeurs votent pour un binôme candidat.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si un binôme obtient la majorité absolue des voix, alors il est automatiquement élu. Si aucun binôme ne l'emporte, un second tour est organisé.</p>
        <p>Les binômes ayant obtenu au moins 12,5% des voix des électeurs inscrits peuvent se présenter au second tour. <b>Au second tour, le binôme obtenant le plus de voix l'emporte</b>.
        ";
      } elseif ($type == "Législatives") {
        $info = "
        <p>Les élections législatives ont lieu <b>tous les cinq ans</b>, quelques semaines après l'élection présidentielle.</p>
        <p>Les élections législatives permettent <b>d'élire les 577 députés</b> qui composent l'Assemblée nationale.</p>
        <p><b>Comment les députés sont-ils élus ?</b> Lors du premier tour, un candidat peut être élu s'il obtient la majorité des suffrages exprimés (plus de 50%). Si aucun candidat ne remplit ces conditions, un second tour est organisé. Les candidats ayant reçu un nombre de suffrages supérieur à 12,5% des inscrits peuvent se présenter au second tour. Le vainqueur est le candidat qui arrive en tête au second tour.</p>
        <p>Les rôles du député sont multiples. Ils votent la loi, contrôlent le gouvernement, et représentent les intérêts de leur circonscription au niveau national. Pour plus d'informations, <a href='https://datan.fr/faq' target='_blank'>cliquez ici</a>.</p>
        ";
      } elseif ($type == "Présidentielle") {
        $info = "
        <p>L'élection présidentielle a lieu <b>tous les cinq ans</b>, quelques semaines avant les élections législatives. Elle permet d'élire le président de la République.</p>
        <p><b>Comment le président est-il élu ?</b> Pour être candidat, il faut avoir récolté le parrainage de 500 élus (maires, parlementaires, conseillers régionaux, etc.) L'élection se déroule sur deux tours. Lors du premier tour, un candidat peut être élu s'il obtient la majorité des suffrages exprimés (plus de 50%). Si aucun candidat n'y parvient, un second tour est organisé avec les deux candidats arrivés en tête au premier tour. Le vainqueur est le candidat qui arrive en tête du second tour.</p>
        <p>Les rôles du président de la République sont nombreux. Il ou elle est veille au respect de la Constitution, assure le fonctionnement des institutions, nomme le Premier ministre et préside le Conseil des ministres, promulgue les lois, etc. Pour plus d'informations, <a href='https://www.vie-publique.fr/infographie/23816-infographie-quel-est-le-role-du-president-de-la-republique' target='_blank' rel='nofollow noreferrer noopener'>cliquez ici</a>.</p>
        ";
      } elseif ($type == "Européennes") { 
        $info = "
        <p>Les élections européennes ont lieu <b>tous les cinq ans</b>. Elles permettent d'élire les députés européens, qui ont pour rôle d'élire la Commission européenne et d'amender et voter les lois européennes (règlements et directives).</p>
        <p>En France, les députés européens sont élus au scrutin proportionnel. Les listes obtenant au moins 5% des suffrages exprimés se voient attribuer des sièges, en fonction de leur score électoral.</p>
        ";
      } elseif($type == "Municipales") {
        $info = "
        <p>Les élections municipales ont lieu <b>tous les six ans</b> et permettent d'élire les conseillers municipaux qui composent le conseil municipal de chaque commune, ainsi que le maire et ses adjoints. Le nombre de conseillers municipaux varie de 7 à 69 en fonction de la taille des communes.</p>
        <p>Les conseillers municipaux sont élus via un <b>scrutin proportionnel de liste</b>. Au premier tour, si une liste obtient la majorité absolue, elle remporte la moitié des sièges, le reste étant réparti proportionnellement entre toutes les listes ayant obtenu au moins 5% des suffrages. Si aucune liste n'obtient la majorité absolue, un second tour est organisé entre les listes ayant receuilli au moins 10% des suffrages.</p>
        <p>Le conseil municipal élit ensuite le maire.</p>
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

    public function count_candidats($id, $second = FALSE, $end = FALSE){
      $this->db->where('election', $id);
      $this->db->where('visible', 1);
      $this->db->where('candidature', 1);

      if ($second === TRUE) {
        $this->db->where('secondRound', 1);
      }

      if ($end === TRUE) {
        $this->db->where('elected', 1);
      }

      return $this->db->count_all_results('candidate_full');
    }

    public function count_candidats_leader($id){
      $this->db->where('election', $id);
      $this->db->where('visible', 1);
      $this->db->where('candidature', 1);
      $this->db->where('position', 'Tête de liste');
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
      $this->db->select('d.groupeId, o.libelleAbrev, o.legislature, o.couleurAssociee, ge.effectif, COUNT(*) AS candidates_n');
      $this->db->select('ROUND(COUNT(*) / ge.effectif * 100) AS candidates_pct', FALSE);
      $this->db->from('elect_deputes_candidats e');
      $this->db->join('deputes_last d', 'd.mpId = e.mpId', 'left');
      $this->db->join('organes o', 'o.uid = d.groupeId', 'left');
      $this->db->join('groupes_effectif ge', 'ge.organeRef = d.groupeId', 'left');
      $this->db->where('e.election', 7); // Municipales 2026
      $this->db->where('e.visible', 1);
      $this->db->where('o.dateFin IS NULL', NULL, FALSE);
      $this->db->group_by('d.groupeId');
      $this->db->order_by('candidates_pct', 'DESC');
      return $this->db->get()->result_array();
    }

    public function get_municipales_listes($city){
      $where = array('code_circonscription' => $city);
      $rows = $this->db->get_where('elect_municipales_listes', $where)->result_array();

      // Build multidimensional array grouped by list
      $listes = [];
      foreach ($rows as $row) {
        $panneau = $row['numero_panneau'];

        // Init the list if not yet seen
        if (!isset($listes[$panneau])) {
          $listes[$panneau] = [
            'numero_panneau'      => $row['numero_panneau'],
            'libelle_abrege'      => $row['libelle_abrege_liste'],
            'libelle_liste'       => $row['libelle_liste'],
            'code_nuance'         => $row['code_nuance'],
            'nuance'              => $row['nuance'],
            'tete_de_liste'       => null,
            'candidats'           => []
          ];
        }

        // Store the tête de liste
        if ($row['tete_de_liste'] === 'OUI') {
          $listes[$panneau]['tete_de_liste'] = $row['prenom'] . ' ' . $row['nom'];
        }

        // Add candidate to the list
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
        'LFI'  => ['label' => 'LFI',                    'color' => '#cc0000'],
        'LHOR' => ['label' => 'Horizons',               'color' => '#00a0c8'],
        'LLR'  => ['label' => 'Les Républicains',       'color' => '#0033a0'],
        'LMDM' => ['label' => 'MODEM',                  'color' => '#f07800'],
        'LREC' => ['label' => 'Reconquête !',           'color' => '#0d2f6e'],
        'LREG' => ['label' => 'Régionalistes',          'color' => '#66bb6a'],
        'LREN' => ['label' => 'Renaissance',            'color' => '#e8b31a'],
        'LRN'  => ['label' => 'Rassemblement national', 'color' => '#0d378a'],
        'LSOC' => ['label' => 'Parti socialiste',       'color' => '#e5007d'],
        'LUC'  => ['label' => 'Union centre',           'color' => '#ffcc00'],
        'LUD'  => ['label' => 'Union droite',           'color' => '#0a3ec2'],
        'LUDI' => ['label' => 'UDI',                    'color' => '#00adef'],
        'LUDR' => ['label' => 'UDR',                    'color' => '#1a1a6e'],
        'LUG'  => ['label' => 'Union gauche',           'color' => '#cc0000'],
        'LUXD' => ['label' => 'Union extrême droite',   'color' => '#302a6e'],
      ];

      foreach ($listes as $key => $liste) {
        $nuance = $nuanceMap[$liste['code_nuance']] ?? null;
        $listes[$key]['nuance_edited'] = $nuance ? $nuance['label'] : $liste['code_nuance'];
        $listes[$key]['nuance_color']  = $nuance ? $nuance['color'] : '#888888';
      }

      return $listes;
    }

  }
