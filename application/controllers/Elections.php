<?php
  class Elections extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('elections_model');
      $this->load->model('deputes_model');
      $this->load->model('groupes_model');
      //$this->password_model->security_password(); Former login protection
    }

    public function index(){
      // Data
      $data['elections'] = $this->elections_model->get_election_all();

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Élections", "url" => base_url()."elections", "active" => TRUE
        )
      );

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Les élections en France - Candidats et résultats | Datan";
      $data['description_meta'] = "Retrouvez toutes les informations sur les différentes élections en France (présidentielle, législative, régionales). Découvrez les députés candidats et les résultats.";
      $data['title'] = "Les élections en France";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('elections/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    public function individual($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // STATE
      $data['state'] = 0;
      $data['state'] = $data['election']['id'] == 1 ? 2 : $data['state']; // Régionales 2021
      $data['state'] = $data['election']['id'] == 4 ? 2 : $data['state']; // Législatives 2022

      // Data
      $data['deputes'] = $this->elections_model->get_all_candidates($data['election']['id'], TRUE, FALSE);
      foreach ($data['deputes'] as $key => $value) {
        if ($value['candidature'] == 1) {
          $district = $this->elections_model->get_district($value['election_libelleAbrev'], $value['district']);
          $data['deputes'][$key]['cardCenter'] = isset($district['libelle']) ? $district['libelle']. ' ('.$district['id'].')' : NULL;
          $data['deputes'][$key]['districtId'] = isset($district['id']) ? $district['id'] : NULL;
        } else {
          $data['deputes'][$key]['cardCenter'] = $value['departementNom'] . ' (' . $value['departementCode'] . ')';
          $data['deputes'][$key]['districtId'] = $value['departementCode'];
        }
      }

      $data['districts'] = $this->elections_model->get_all_districts($data['election']['id']);
      $data['electionInfos'] = $this->elections_model->get_election_infos($data['election']['libelleAbrev']);
      $data['candidatsN'] = $this->elections_model->count_candidats($data['election']['id'], FALSE, FALSE);
      $data['nonCandidatsN'] = $this->elections_model->count_non_candidats($data['election']['id'], FALSE, FALSE);
      $data['candidatsN_second'] = $this->elections_model->count_candidats($data['election']['id'], TRUE, FALSE);
      $data['candidatsN_elected'] = $this->elections_model->count_candidats($data['election']['id'], FALSE, TRUE);
      $data['candidatsN_eliminated'] = $this->elections_model->count_candidats_eliminated($data['election']['id']);
      $data['candidatsN_eliminated'] = count($data['candidatsN_eliminated']);
      $data['mapLegend'] = $this->elections_model->get_map_legend($data['election']['id']);
      $data['today'] = date("Y-m-d");

      // If legislative
      if ($data['election']['slug'] == 'legislatives-2022') {
        // OLD DATA FOR PREVIOUS ELECTION
        //$data['groupes'] = $this->groupes_model->get_groupes_all(TRUE, legislature_current());
        //$data['groupesSorted'] = $this->groupes_model->get_groupes_sorted($data['groupes']);

        // NEW DATA FOR 2022 LEGISLATIVE RESULTS
        $file = file_get_contents(asset_url() . "data_elections/" . $data['election']['slug'] . ".json");
        $array = json_decode($file, true);
        $data['groupesSorted'] = $array;
      }

      // badgeCenter
      foreach ($data['deputes'] as $key => $value) {
        $state = $this->elections_model->get_state($value['secondRound'], $value['elected']);
        $data['deputes'][$key]['electionState'] = $state;
        if ($state == 'lost') {
          $data['deputes'][$key]['badgeCenter'] = 'Éliminé' . gender($value['civ'])['e'];
          $data['deputes'][$key]['badgeCenterColor'] = "badge-danger";
        }
        if ($state == 'second') {
          $data['deputes'][$key]['badgeCenter'] = "Second tour";
          $data['deputes'][$key]['badgeCenterColor'] = "badge-primary";
        }
        if ($state == 'elected') {
          $data['deputes'][$key]['badgeCenter'] = 'Élu' . gender($value['civ'])['e'];
          $data['deputes'][$key]['badgeCenterColor'] = "badge-primary";
        }
        if (!isset($data['deputes'][$key]['badgeCenter'])) {
          $data['deputes'][$key]['badgeCenter'] = $value['candidature'] == 1 ? 'Candidat' : 'Non candidat';
          $data['deputes'][$key]['badgeCenter'] .= gender($value['civ'])['e'];
          $data['deputes'][$key]['badgeCenterColor'] = $value['candidature'] == 1 ? 'badge-secondary' : 'badge-danger';
        }
      }

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Élections", "url" => base_url()."elections", "active" => FALSE
        ),
        array(
          "name" => $data['election']['libelleAbrev'] . " " . $data['election']['dateYear'], "url" => base_url()."elections/" . $data['election']['slug'], "active" => TRUE
        )
      );

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['election']['libelleAbrev'] == 'Présidentielle') {
        $data['title_meta'] = "L'" . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'] . " - Candidats et résultats | Datan";
        $data['title'] = "L'" . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'];
        $data['description_meta'] = "Retrouvez toutes les informations sur l'" . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'] . " en France. Découvrez les députés candidats et les résultats.";
      } else {
        $data['title_meta'] = "Les " . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'] . " - Candidats et résultats | Datan";
        $data['title'] = "Les " . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'];
        $data['description_meta'] = "Retrouvez toutes les informations sur les " . mb_strtolower($data['election']['libelle']) . " de " . $data['election']['dateYear'] . " en France. Découvrez les députés candidats et les résultats.";
      }

      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => asset_url() . "css/jquery-jvectormap-2.0.5.css",
          "async" => FALSE
        )
      );
      // JS
      $data['js_to_load_before_datan'] = array('isotope.pkgd.min');
      if ($data['election']['slug'] == 'legislatives-2022') {
        $data['js_to_load_up_defer'] = array('chart.min.js', 'chartjs-plugin-datalabels@2.1.js');
      }
      $data['js_to_load'] = array();
      if (in_array($data['election']['id'], array(1, 4, 5))) {
        array_push($data['js_to_load'], 'datan/sorting_select');
      }
      array_push($data['js_to_load'], 'jvectormap/jquery-jvectormap-2.0.5.min');
      if ($data['election']['libelleAbrev'] == 'Régionales') {
        array_push($data['js_to_load'], 'jvectormap/jquery-jvectormap-fr_regions_2016-merc');
      } elseif ($data['election']['libelleAbrev'] == 'Départementales') {
        array_push($data['js_to_load'], 'jvectormap/jquery-jvectormap-fr-merc');
      }
      array_push($data['js_to_load'], "jvectormap/maps_jvectormap-datan");
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('elections/candidats', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }
  }
?>
