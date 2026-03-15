<?php
  class Elections extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('elections_model');
      $this->load->model('deputes_model');
      $this->load->model('groupes_model');
      $this->load->model('city_model');
      $this->load->model('departement_model');
    }

    public function index(){
      // Data
      $data['elections'] = $this->elections_model->get_election_all();
      $data['election_future'] = TRUE; // There is an election in the future

      $data['departements'] = $this->departement_model->get_all_departements();
      $data['communes'] = $this->city_model->get_communes(FALSE, 30);

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
      // JS
      $data['js_to_load'] = array(
        'dist/autocomplete_search',
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('elections/index', $data);
      $this->load->view('elections/links_dpt_cities', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    public function individual($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // State of the election
      $data['state'] = $this->elections_model->get_election_state($data['election']['id']);

      // Get departements and communes for link 
      $data['departements'] = $this->departement_model->get_all_departements();
      $data['communes'] = $this->city_model->get_communes(FALSE, 30);

      // Data
      $data['deputes'] = $this->elections_model->get_all_candidates($data['election']['id'], TRUE, FALSE);
      foreach ($data['deputes'] as $key => $value) {
        if ($value['candidature'] == 1 && $value['district']) {
          $district = $this->elections_model->get_district($value['election_libelleAbrev'], $value['district']);
          $data['deputes'][$key]['cardCenter'] = isset($district['libelle']) ? 'Candidat' . gender($value['civ'])['e'] . ' à ' . $district['libelle']. ' ('.$district['id'].')' : NULL;
          $data['deputes'][$key]['districtId'] = isset($district['id']) ? $district['id'] : NULL;
        } else {
          $data['deputes'][$key]['cardCenter'] = $value['departementNom'] . ' (' . $value['departementCode'] . ')';
          $data['deputes'][$key]['districtId'] = $value['departementCode'];
        }
      }

      $data['districts'] = $this->elections_model->get_all_districts($data['election']['id']);
      $data['electionInfos'] = $this->elections_model->get_election_infos($data['election']['libelleAbrev']);
      $data['candidatsN'] = $this->elections_model->count_candidats($data['election']['id']);
      if ($data['election']['libelleAbrev'] === 'Municipales') {
        $data['candidatsNLeaders'] = $this->elections_model->count_candidats($data['election']['id'], ['position' => 'Tête de liste']);
      }
      $data['nonCandidatsN'] = $this->elections_model->count_non_candidats($data['election']['id'], FALSE, FALSE);
      $data['candidatsN_second'] = $this->elections_model->count_candidats($data['election']['id'], ['second' => TRUE, 'end' => FALSE]);
      $data['candidatsN_elected'] = $this->elections_model->count_candidats($data['election']['id'], ['second' => FALSE, 'end' => TRUE]);
      $data['candidatsN_eliminated'] = $this->elections_model->count_candidats_eliminated($data['election']['id']);
      $data['candidatsN_eliminated'] = count($data['candidatsN_eliminated']);
      $data['mapLegend'] = $this->elections_model->get_map_legend($data['election']['id']);
      $data['today'] = date("Y-m-d");
      $data['results'] = in_array($data['election']['id'], array(1, 2, 3, 4, 5, 6)) ? true : false;
      $data['municipalesResultsCitiesCount'] = 0;

      if ($data['election']['slug'] === 'municipales-2026') {
        $data['municipalesResultsCitiesCount'] = $this->elections_model->count_results_cities_ministry('2026_muni_t1');
      }

      // Election results 
      if ($data['election']['slug'] == 'legislatives-2022') {
        $file = file_get_contents(asset_url() . "data_elections/" . $data['election']['slug'] . ".json");
        $data['groupesSorted'] = json_decode($file, true);
      } elseif ($data['election']['slug'] == 'europeennes-2024') {
        $file = file_get_contents(asset_url() . "data_elections/" . $data['election']['slug'] . ".json");
        $data['groupesSorted'] = json_decode($file, true);
      } elseif ($data['election']['slug'] == 'legislatives-2024') {
        $file = file_get_contents(asset_url() . "data_elections/" . $data['election']['slug'] . "-2.json");
        $data['groupesSorted'] = json_decode($file, true);
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
          if ($data['election']['slug'] === 'municipales-2026' && $value['position'] === 'Tête de liste') {
            $data['deputes'][$key]['badgeCenter'] .= ' tête de liste';
          }
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
      $data['js_to_load_before_datan'] = array('libraries/isotope/isotope.pkgd.min');
      if (in_array($data['election']['id'], array(4, 5, 6))) {
        $data['js_to_load_up_defer'] = array('dist/chart.min.js');
      }
      $data['js_to_load'] = array(
        'dist/autocomplete_search',
      );
      if (in_array($data['election']['id'], array(1, 4, 5, 6, 7))) {
        array_push($data['js_to_load'], 'datan/sorting_select');
      }
      array_push($data['js_to_load'], 'libraries/jvectormap/jquery-jvectormap-2.0.5.min');
      if ($data['election']['libelleAbrev'] == 'Régionales') {
        array_push($data['js_to_load'], 'libraries/jvectormap/jquery-jvectormap-fr_regions_2016-merc');
      } elseif ($data['election']['libelleAbrev'] == 'Départementales') {
        array_push($data['js_to_load'], 'libraries/jvectormap/jquery-jvectormap-fr-merc');
      }
      array_push($data['js_to_load'], "libraries/jvectormap/maps_jvectormap-datan");
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('elections/candidats', $data);
      $this->load->view('elections/links_dpt_cities', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    public function results_city($dpt, $city) {

      $data['ville_all'] = $this->city_model->get_individual($city, $dpt);

      if (empty($data['ville_all'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
        $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
      }

      $data['ville'] = $data['ville_all'][0];
      $insee = $data['ville']['insee'];
      $data['ville_infos'] = $this->city_model->get_city_by_insee($insee);
      $data['mayor'] = $this->city_model->get_mayor($data['ville']['dpt'], $insee, $data['ville']['commune']);
      $data['adjacentes'] = $this->city_model->get_adjacentes($insee);
      $data['communes_dpt'] = $this->city_model->get_communes_by_dpt($data['ville']['dpt_slug'], url_obf_cities_election(), 30);

      // Get listes
      $data['listes'] = $this->elections_model->get_municipales_listes($insee);
      $data['listes'] = $this->elections_model->get_nuances_edited($data['listes']);

      // Get deputes candidates       
      $data['deputes'] = $this->elections_model->get_candidates_by_city($insee);

      // Get current MPs
      $this->load->model('groupes_model');
      $circo = array();
      foreach ($data['ville_all'] as $circo_data) {
        $circo[] = $circo_data['circo'];
      }
      $data['deputes_ville'] = $this->city_model->get_mps(
        $data['ville']['dpt_slug'], $circo, legislature_current());
      if
       (!empty($data['deputes_ville'])) {
        foreach ($data['deputes_ville'] as $key => $value) {
          $data['deputes_ville'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
          $data['deputes_ville'][$key]['electionCircoAbbrev'] = abbrev_n($value['electionCirco'], TRUE);
        }
      }

      // PLM 
      $data['isPLM'] = in_array($insee, ['75056', '13055', '69123']);

      if($data['isPLM']) {
        $data['arrondissements'] = $this->elections_model->get_municipales_listes($insee, TRUE);
        foreach ($data['arrondissements'] as $arr => $lists) {
          $data['arrondissements'][$arr] = $this->elections_model->get_nuances_edited($lists);
        }

        $data['arrondissements_view'] = array();
        foreach ($data['arrondissements'] as $arrLabel => $lists) {
          $data['arrondissements_view'][] = array(
            'label' => $arrLabel,
            'lists' => $lists,
          );
        }
        $data['arrondissements_first_label'] = !empty($data['arrondissements_view']) ? $data['arrondissements_view'][0]['label'] : NULL;
      }

      // Previous elections 
      $data['previous_elections'] = $this->city_model->get_results_elections_full($insee);
      $data['election_rounds'] = array(
        1 => array(
          'title' => 'Premier tour',
          'data' => 'round_1'
        ),
        2 => array(
          'title' => 'Second tour',
          'data' => 'round_2'
        )
      );
      $data['previous_elections_ui'] = $this->prepare_previous_elections_for_view($data['previous_elections'], $data['election_rounds']);

      // Municipales 2026 - Ministry city results
      $municipales_results = $this->elections_model->get_results_city_municipales_ministry($insee, "2024_muni_t1");
      $data['municipales_ministry_results'] = $municipales_results['results'];
      $data['municipales_ministry_election_id'] = $municipales_results['id_election'];

      // Breadcrumb
      $is_paris = ($dpt === 'paris-75');
      $breadcrumb = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Élections", "url" => base_url()."elections", "active" => FALSE
        )
      );
      if (!$is_paris) {
        $breadcrumb[] = array(
          "name"   => $data['ville']['dpt_nom'] . " (" . $data['ville']['dpt'] . ")",
          "url"    => base_url()."elections/resultats/" . $dpt,
          "active" => FALSE
        );
      }
      $breadcrumb[] = array(
        "name"   => $data['ville']['commune_nom'],
        "url"    => base_url() . "elections/resultats/" . $dpt . "/ville_" . $city,
        "active" => TRUE
      );
      $data['breadcrumb'] = $breadcrumb;

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Les candidats et résultats aux élections municipales 2026 à " . $data['ville']['commune_nom'] . " | Datan";
      $data['description_meta'] = "Retrouvez les résultats des élections municipales à " . $data['ville']['commune_nom'] . ". Les élections se tiennent les 15 et 22 mars 2026. Découvrez les candidats, le nombre de votants, l'abstention pour cette commune.";
      $data['title'] = "Élections municipales 2026 à " . $data['ville']['commune_nom'] . "&nbsp;: candidats et résultats";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('elections/results_city', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    private function prepare_previous_elections_for_view($previous_elections, $election_rounds) {
      $prepared = array();

      foreach ($previous_elections as $election) {
        $election_ui = $election;
        $circos = !empty($election['circos']) ? array_values($election['circos']) : array();

        // Backward compatibility for older payloads with direct round_1 / round_2 fields.
        if (empty($circos) && isset($election['round_1'])) {
          $circos[] = array(
            'code_circo' => NULL,
            'libelle_circo' => NULL,
            'round_1' => $election['round_1'],
            'round_2' => isset($election['round_2']) ? $election['round_2'] : array(),
          );
        }

        $election_ui['circos_ui'] = array();
        foreach ($circos as $circo_index => $circo_data) {
          $circo_id_raw = !empty($circo_data['code_circo']) ? (string) $circo_data['code_circo'] : ('circo_' . $circo_index);
          $circo_id = preg_replace('/[^a-zA-Z0-9_-]/', '-', $circo_id_raw);
          $circo_label = !empty($circo_data['libelle_circo'])
            ? $circo_data['libelle_circo']
            : (!empty($circo_data['code_circo']) ? 'Circonscription ' . $circo_data['code_circo'] : 'Commune entiere');

          $circo_ui = array(
            'id' => $circo_id,
            'label' => $circo_label,
            'is_default' => $circo_index === 0,
            'rounds' => array(),
          );

          foreach ($election_rounds as $round_key => $round_meta) {
            $round_infos = isset($circo_data[$round_meta['data']]['infos']) ? $circo_data[$round_meta['data']]['infos'] : array();
            $round_results = isset($circo_data[$round_meta['data']]['results']) ? $circo_data[$round_meta['data']]['results'] : array();
            $has_round_infos = isset($round_infos['votants']) && $round_infos['votants'] !== NULL;

            $circo_ui['rounds'][] = array(
              'key' => (string) $round_key,
              'title' => $round_meta['title'],
              'is_default' => (string) $round_key === '1',
              'infos' => $round_infos,
              'results' => $round_results,
              'show_no_second_round_alert' => ((string) $round_key === '2') && empty($round_results) && !$has_round_infos,
            );
          }

          $election_ui['circos_ui'][] = $circo_ui;
        }

        $election_ui['has_multiple_circos'] = count($election_ui['circos_ui']) > 1;
        $prepared[] = $election_ui;
      }

      return $prepared;
    }

    public function results_dpt($dpt) {

      $data['dpt'] = $this->departement_model->get_departement($dpt);

      if (empty($data['dpt']) || $dpt == 'paris-75') {
        show_404($this->functions_datan->get_404_infos());
      }

      $communes = $this->city_model->get_communes_by_dpt($dpt, FALSE, FALSE, 'alpha');
      $data['communes'] = $this->city_model->group_communes_by_letter($communes);
      $data['big_communes'] = $this->city_model->get_communes_by_dpt($dpt, url_obf_cities_election(), 15);
      $data['big_slugs'] = array();
      foreach ($data['big_communes'] as $b) {
        $data['big_slugs'][] = $b['commune_slug'];
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
          "name" => $data['dpt']['departement_nom'] . " (" . $data['dpt']['departement_code'] . ")", "url" => base_url()."elections/resultats/" . $dpt, "active" => TRUE
        ),
      );

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Les candidats et résultats aux élections municipales 2026 " . $data['dpt']['libelle_1'] . "" . $data['dpt']['departement_nom'] . " (" . $data['dpt']['departement_code'] . ") | Datan";
      $data['description_meta'] = "Retrouvez les résultats des élections municipales " . $data['dpt']['libelle_1'] . "" . $data['dpt']['departement_nom'] . " (" . $data['dpt']['departement_code'] . "). Les élections se tiennent les 15 et 22 mars 2026. Découvrez les candidats, le nombre de votants, l'abstention pour les communes de ce département.";
      $data['title'] = "Élections municipales 2026 " . $data['dpt']['libelle_1'] . "" . $data['dpt']['departement_nom'];
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('elections/results_dpt', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

  }
?>
