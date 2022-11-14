<?php
  class Home extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('deputes_model');
      $this->load->model('groupes_model');
      $this->load->model('depute_edito');
      $this->load->model('votes_model');
      $this->load->model('departement_model');
      $this->load->model('city_model');
      $this->load->model('post_model');
      $this->load->model('elections_model');
      $this->load->helper('form');
      //$this->password_model->security_password(); Former login protection
    }

    public function index() {
      //Get random data
      $data['commune_random'] = $this->city_model->get_random();
      $data['depute_random'] = $this->deputes_model->get_depute_random();
      $data['depute_random'] = array_merge($data['depute_random'], gender($data['depute_random']['civ']));
      $data['depute_random']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_random']['libelleAbrev'], $data['depute_random']['couleurAssociee']));
      // Remove for the moment
      $data['groupe_random'] = $this->groupes_model->get_groupe_random();
      if (!empty($data['groupe_random'])) {
        $data['groupe_random']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe_random']['libelleAbrev'], $data['groupe_random']['couleurAssociee']));
      }

      //Get groups (cached)
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost())){
        $this->db->cache_on();
        $data['groupes'] = $this->groupes_model->get_groupes_hemicycle();
        $this->db->cache_off();
      } else {
        $data['groupes'] = $this->groupes_model->get_groupes_hemicycle();
      }
      $data['groupesSorted'] = $this->groupes_model->get_groupes_sorted($data['groupes']);

      //Get stats ( Has been removed because < 1 years)
      $data['stats'] = FALSE;
      /*
      $data['depute_vote_plus'] = $this->deputes_model->get_depute_vote_plus();
      if (!empty($data['depute_vote_plus'])) {
        $data['depute_vote_plus'] = array_merge($data['depute_vote_plus'], gender($data['depute_vote_plus']['civ']));
        $data['depute_vote_plus']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_vote_plus']['libelleAbrev'], $data['depute_vote_plus']['couleurAssociee']));
      }
      $data['depute_vote_moins'] = $this->deputes_model->get_depute_vote_moins();
      if (!empty($data['depute_vote_moins'])) {
        $data['depute_vote_moins'] = array_merge($data['depute_vote_moins'], gender($data['depute_vote_moins']['civ']));
        $data['depute_vote_moins']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_vote_moins']['libelleAbrev'], $data['depute_vote_moins']['couleurAssociee']));
      }
      $data['depute_loyal_plus'] = $this->deputes_model->get_depute_loyal_plus();
      if (!empty($data['depute_loyal_plus'])) {
        $data['depute_loyal_plus'] = array_merge($data['depute_loyal_plus'], gender($data['depute_loyal_plus']['civ']));
        $data['depute_loyal_plus']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_loyal_plus']['libelleAbrev'], $data['depute_loyal_plus']['couleurAssociee']));
      }
      $data['depute_loyal_moins'] = $this->deputes_model->get_depute_loyal_moins();
      if (!empty($data['depute_loyal_moins'])) {
        $data['depute_loyal_moins'] = array_merge($data['depute_loyal_moins'], gender($data['depute_loyal_moins']['civ']));
        $data['depute_loyal_moins']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_loyal_moins']['libelleAbrev'], $data['depute_loyal_moins']['couleurAssociee']));
      }
      */

      //Get votes (cached)
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost())){
          $this->db->cache_on();
          $data['votes'] = $this->votes_model->get_last_votes_datan(5);
          $this->db->cache_off();
      } else {
        $data['votes'] = $this->votes_model->get_last_votes_datan(5);
      }

      // Get elections
      $data['candidatsN'] = $this->elections_model->count_candidats(4, FALSE, FALSE);
      $data['elected'] = $this->elections_model->get_all_candidates(4, TRUE, TRUE, 'elected');
      $data['electedN'] = count($data['elected']);
      $randKey = array_rand($data['elected']);
      $data['candidatRandom'] = $data['elected'][$randKey];
      $district = $this->elections_model->get_district($data['candidatRandom']['election_libelleAbrev'], $data['candidatRandom']['district']);
      $data['candidatRandom']['cardCenter'] = isset($district['libelle']) && $district['libelle'] != '' ? $district['libelle'] . ' (' . $district['id'] . ')' : '';

      //Get posts (needs to be cached)
      $data['posts'] = $this->post_model->get_last_posts();

      // Composition
      $data['composition'] = TRUE;

      //Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Datan | Comprendre l'Assemblée nationale";
      $data['description_meta'] = "L'Assemblée nationale enfin compréhensible ! Découvrez les résultats de vote de chaque député et groupe parlementaire.";
      //Open Graph
      $data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['critical_css'] = "index";
      $data['css_to_load']= array(
        array(
          "url" => "https://unpkg.com/flickity@2.3.0/dist/flickity.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load_up_defer'] = array('chart.min.js', 'chartjs-plugin-datalabels@2.1.js');
      $data['js_to_load']= array(
        "datan/map_france.min",
        "datan/autocomplete_deputes",
        "datan/autocomplete_cities_api",
        "flickity.pkgd.min",
      );
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('home/index', $data);
      $this->load->view('templates/footer', $data);
    }

  }
?>
