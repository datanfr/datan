<?php
  class Groupes extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('groupes_model');
      $this->load->model('groupes_edito');
      $this->load->model('votes_model');
      $this->load->model('depute_edito');
      $this->load->model('stats_model');
      $this->load->model('deputes_model');
      $this->load->model('functions_datan');
      $this->load->model('fields_model');
      $this->load->model('jobs_model');
      //$this->password_model->security_password(); Former login protection
      setlocale(LC_TIME, 'french');
    }

    public function get_data($data){
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }
      $data['infos_groupes'] = groups_position_edited();
      $data['president'] = $this->groupes_model->get_groupes_president($data['groupe']['uid'], $data['groupe']['legislature'], $data['active']);
      if (!empty($data['president'])) {
        $data['president'] = array_merge($data['president'], gender($data['president']['civ']));
      }
      $data['groupe']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe']['libelleAbrev'], $data['groupe']['couleurAssociee']));
      $data['membres'] = $this->groupes_model->get_groupe_membres($data['groupe']['uid'], $data['groupe']['dateFin']);
      if (!in_array($data['groupe']['uid'], $this->groupes_model->get_all_groupes_ni())) {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($data['groupe']['uid'], $data['active']);
      if (!in_array($data['groupe']['uid'], $this->groupes_model->get_all_groupes_ni())) {
        $data['apparentes'] = array_slice($data['apparentes'], 0, 20);
      }
      $data['groupesActifs'] = $this->groupes_model->get_groupes_all(TRUE, legislature_current());
      return $data;
    }

    public function get_data_stats($data){
      $data['stats'] = $this->groupes_model->get_stats($data['groupe']['uid']);
      $data['statsAverage'] = $this->groupes_model->get_stats_avg($data['groupe']['legislature']);
      if (!empty($data['stats']['cohesion'])) {
        $data['cohesionAverage'] = $data['statsAverage']['cohesion'];
        $data['edito_cohesion'] = $this->groupes_edito->cohesion($data['stats']['cohesion']['value'], $data['cohesionAverage']);
        $data['no_cohesion'] = FALSE;
      } else {
        $data['no_cohesion'] = TRUE;
      }
      if (!empty($data['stats']['participation'])) {
        $data['participationAverage'] = $data['statsAverage']['participation'];
        $data['edito_participation'] = $this->groupes_edito->participation($data['stats']['participation']['value'], $data['participationAverage']);
        $data['no_participation'] = FALSE;
      } else {
        $data['no_participation'] = TRUE;
      }
      if (!empty($data['stats']['majority'])) {
        $data['majoriteAverage'] = $data['statsAverage']['majority'];
        $data['edito_majorite'] = $this->groupes_edito->majority($data['stats']['majority']['value'], $data['majoriteAverage']);
        $data['no_majorite'] = FALSE;
      } else {
        $data['no_majorite'] = TRUE;
      }
      // Get age data
      $data['ageMean'] = $this->stats_model->get_age_mean($data['groupe']['legislature']);
      $data['ageMean'] = round($data['ageMean']);
      $data['ageEdited'] = $this->functions_datan->more_less($data['groupe']['age'], $data['ageMean']);
      // Get women data
      $data['womenPctTotal'] = $this->deputes_model->get_deputes_gender($data['groupe']['legislature']);
      $data['womenPctTotal'] = $data['womenPctTotal'][1]['percentage'];
      $data['womenEdited'] = $this->functions_datan->more_less($data['groupe']['womenPct'], $data['womenPctTotal']);
      return $data;
    }

    //INDEX - Homepage with all groups//
    public function index($legislature = NULL){

      if ($legislature == legislature_current()) {
        redirect('groupes');
      }

      if ($legislature == NULL) {
        $legislature = legislature_current();
      }

      if ($legislature < 14) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['active'] = TRUE;
      $data['legislature'] = $legislature;
      $data['groupes'] = $this->groupes_model->get_groupes_all($data['active'], $data['legislature']);
      $data['number_groupes_inactive'] = $this->groupes_model->get_number_inactive_groupes();
      $data['number'] = count($data['groupes']);
      $data['number_in_groupes'] = $this->groupes_model->get_number_mps_groupes();
      $data['number_unattached'] = $this->groupes_model->get_number_mps_unattached();

      // Groupe color
      foreach ($data['groupes'] as $key => $value) {
        $data['groupes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      // Meta and Breadcrum
      $data['url'] = $this->meta_model->get_url();
      if ($data['legislature'] == legislature_current()) {
        // Meta
        $data['title_meta'] = "Groupes Parlementaires - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les groupes parlementaires en activité de la " . $data['legislature'] . "ème législature. Résultats de vote et analyses pour chaque groupe.";
        $data['title'] = "Les groupes politiques à l'Assemblée nationale";
        // Breadcrum
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => TRUE
          )
        );
      } else {
        // Meta
        $data['title_meta'] = "Groupes Parlementaires " . $data['legislature'] . "ème legislature - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les groupes parlementaires de la " . $data['legislature'] . "ème législature. Résultats de vote et analyses pour chaque groupe.";
        $data['title'] = "Les groupes politiques de la " . $data['legislature'] . "ème législature";
        // Breadcrum
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
          ),
          array(
            "name" => "Législature " . $data['legislature'], "url" => base_url()."groupes/legislature-" . $data['legislature'], "active" => TRUE
          )
        );
      }
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('groupes/all', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    //INDEX - Homepage with all inactive groups //
    public function inactifs(){
      $data['active'] = FALSE;
      $data['legislature'] = legislature_current();
      $data['groupes'] = $this->groupes_model->get_groupes_all($data['active'], legislature_current());
      $data['number_groupes_inactive'] = $this->groupes_model->get_number_inactive_groupes();
      $data['number_groupes_active'] = $this->groupes_model->get_number_active_groupes();

      // Groupe color
      foreach ($data['groupes'] as $key => $value) {
        $data['groupes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Anciens groupes politiques - Assemblée Nationale | Datan";
      $data['description_meta'] = "Retrouvez tous les anciens groupes parlementaires de la " . legislature_current() . "ème législature. Résultats de vote et analyses pour chaque groupe.";
      $data['title'] = "Les anciens groupes politiques de la " . legislature_current() . "ème législature";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
        ),
        array(
          "name" => "Anciens groupes", "url" => base_url()."groupes/inactifs", "active" => TRUE
        ),
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('groupes/all', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    //INDIVIDUAL - individual page by group //
    public function individual($legislature, $groupe_slug){
      $data['legislature'] = $legislature;
      $legislature = $data['legislature'];

      if ($legislature < 14) {
        show_404($this->functions_datan->get_404_infos());
      }

      // Query 1 Informations principales
      $groupe_slug = mb_strtoupper($groupe_slug);
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe_slug, $legislature);
      $data['history'] = $this->groupes_model->get_history($data['groupe']['uid']);

      if (empty($data['groupe'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // Caching
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
          $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
      }

      $data = $this->get_data($data);

      $data['dateDebutMois'] = strftime('%B %Y', strtotime($data['groupe']['dateDebut']));

      // Query nbr of groups
      $data['groupesN'] = $this->groupes_model->get_number_active_groupes();
      $data['groupesN'] = $data['groupesN']['n'];
      // Get origine-sociale
      $data['origineSociale'] = $this->jobs_model->get_group_category_random($data['groupe']['uid']);
      if (round($data['origineSociale']['pct']) > round($data['origineSociale']['population'])) {
        $data['origineSociale']['edited'] = 'plus';
      } elseif (round($data['origineSociale']['pct']) < round($data['origineSociale']['population'])) {
        $data['origineSociale']['edited'] = 'moins';
      } else {
        $data['origineSociale']['edited'] = 'autant';
      }

      // Get majority group
      $data['groupMajority'] = $this->groupes_model->get_majority_group($legislature);

      // Social Media du groupe
      $data['groupe'] = $this->groupes_model->get_groupe_social_media($data['groupe']);

      //Query 3 Statistiques
      $data = $this->get_data_stats($data);
      if ($data['history']) {
        $data['stats_history'] = $this->groupes_model->get_stats_history($data['history']);
      }

      // ACCORD AVEC GROUPES
      $data['accord_groupes_actifs'] = $this->groupes_model->get_stats_proximite($data['groupe']['uid']); // PROXIMITÉ TOUS LES GROUPES
      $data['accord_groupes_all'] = $this->groupes_model->get_stats_proximite_all($data['groupe']['uid']);

      if ($data['groupe']['legislature'] == legislature_current()) {
        $data['accord_groupes_featured'] = $data['accord_groupes_actifs'];
      } else {
        $data['accord_groupes_featured'] = $data['accord_groupes_all'];
      }

      $accord_groupes_n = count($data['accord_groupes_featured']);
      $accord_groupes_divided = round($accord_groupes_n / 2, 0, PHP_ROUND_HALF_UP);
      $data['accord_groupes_first'] = array_slice($data['accord_groupes_featured'], 0, $accord_groupes_divided);
      $data['accord_groupes_first'] = array_slice($data['accord_groupes_first'], 0, 3);
      $data['accord_groupes_last'] = array_slice($data['accord_groupes_featured'], $accord_groupes_divided, $accord_groupes_n);
      $data['accord_groupes_last'] = array_slice($data['accord_groupes_last'], -3);
      if (!empty($data['accord_groupes_featured'])) {
        $data['edito_proximite'] = $this->groupes_edito->proximite($data['accord_groupes_featured'], $data['infos_groupes']);
        $data['no_proximite'] = FALSE;
      } else {
        $data['no_proximite'] = TRUE;
      }

      // Query 4 Votes
      $data['votes_datan'] = $this->votes_model->get_votes_datan_groupe($data['groupe']['uid'], 5);

      // Query 5 - Edito
      $data['edito'] = $this->groupes_edito->edito($data['groupe']['libelleAbrev'], $data['groupe']['positionPolitique']);

      // GET ALL OTHER GROUPES
      $data['groupesActifs'] = $this->groupes_model->get_groupes_all(TRUE, $legislature);

      // If NI : edito
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['groupe']['ni_edited'] = $this->groupes_edito->get_ni($legislature);
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelle'] == "Non inscrit") {
        $data['title_meta'] = 'Députés non inscrits - Asssemblée Nationale | Datan';
        $data['description_meta'] = "Découvrez les résultats de vote des députés non inscrits (NI) : taux de participation, cohésion au sein groupe, proximité avec les autres groupes parlementaires.";
        $data['title'] = 'Députés non inscrits';
      } else {
        $data['title_meta'] = name_group($data['groupe']['libelle'])." - Assemblée Nationale | Datan";
        $data['description_meta'] = "Découvrez les résultats de vote du groupe " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].") : taux de participation, cohésion au sein groupe, proximité avec les autres groupes parlementaires.";
        $data['title'] = $data['groupe']['libelle'];
      }
      // Breadcrumb
      if ($legislature == legislature_current()) {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
          ),
          array(
            "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => TRUE
          ),
        );
      } else {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
          ),
          array(
            "name" => "Législature " . $legislature, "url" => base_url()."groupes/legislature-" . $legislature, "active" => FALSE
          ),
          array(
            "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => TRUE
          ),
        );
      }
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Microdata Person
      if (!in_array($data['groupe']['uid'], $this->groupes_model->get_all_groupes_ni())) {
        $data['schema'] = $this->groupes_model->get_organization_schema($data['groupe'], $data['president'], NULL);
      }
      // CSS
      $data['critical_css'] = "groupe_individual";
      $data['css_to_load']= array(
        array(
          "url" => css_url()."circle.css",
          "async" => FALSE
        ),
        array(
          "url" => "https://unpkg.com/flickity@2.3.0/dist/flickity.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load']= array("flickity.pkgd.min");
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/individual', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function individual_membres($legislature, $groupe){
      if ($legislature < 14) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, $legislature);

      if (empty($data['groupe'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data = $this->get_data($data);

      $data['membres'] = $this->groupes_model->get_groupe_membres($data['groupe']['uid'], $data['groupe']['dateFin']);
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($data['groupe']['uid'], $data['active']);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés en activité non inscrits (NI) de la 15e législature.";
        $data['title'] = "Députés non incrits (NI)";
      } else {
        $data['title_meta'] = "Députés " . name_group($data['groupe']['libelle']) . " - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés membres du groupe parlementaire " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].").";
        $data['title'] = "Députés membres du groupe " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].")";
      }
      // Breadcrumb
      if ($legislature == legislature_current()) {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
          ),
          array(
            "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
          ),
          array(
            "name" => "Membres", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/membres", "active" => TRUE
          )
        );
      } else {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
          ),
          array(
            "name" => "Législature " . $legislature, "url" => base_url()."groupes/legislature-" . $legislature, "active" => FALSE
          ),
          array(
            "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
          ),
          array(
            "name" => "Membres", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/membres", "active" => TRUE
          )
        );
      }
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Microdata Person
      if (!in_array($data['groupe']['uid'], $this->groupes_model->get_all_groupes_ni())) {
        $data['schema'] = $this->groupes_model->get_organization_schema($data['groupe'], $data['president'], array('members' => $data['membres'], 'apparentes' => $data['apparentes']));
      }
      // JS
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/individual_membres', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    /* page: groupes/x/votes */
    public function individual_votes_datan($legislature, $groupe){
      if ($legislature < 15) {
        show_404($this->functions_datan->get_404_infos());
      };

      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, $legislature);

      if (empty($data['groupe'])) {
        show_404($this->functions_datan->get_404_infos());
      };

      $data = $this->get_data($data);

      // Get active fields
      $data['fields'] = $this->fields_model->get_active_fields();

      // Get votes
      $data['votes'] = $this->votes_model->get_votes_datan_groupe($data['groupe']['uid']);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions des députés non inscrits (NI) quand ils votent à l'Assemblée nationale.";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Groupe " . name_group($data['groupe']['libelle']) . " - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions du groupe " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].") quand il vote à l'Assemblée nationale.";
        $data['title'] = $data['groupe']['libelle'];
      }
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
        ),
        array(
          "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_before_datan'] = array("isotope.pkgd.min");
      $data['js_to_load']= array("datan/sorting");
      // CSS
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('groupes/votes_datan', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    /* page: groupes/votes/all */
    public function individual_votes_all($legislature, $groupe){
      if ($legislature < 15) {
        show_404($this->functions_datan->get_404_infos());
      };

      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, $legislature);

      if (empty($data['groupe'])) {
        show_404($this->functions_datan->get_404_infos());
      };

      $data = $this->get_data($data);

      // Query - get all votes
      $data['votes'] = $this->votes_model->get_votes_all_groupe($data['groupe']['uid'], $legislature);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Votes | Datan";
        $data['description_meta'] = "Retrouvez tous les votes des députés non inscrits (NI) : positions politiques, cohésion interne, participation.";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Groupe " . name_group($data['groupe']['libelle']) . " - Votes | Datan";
        $data['description_meta'] = "Retrouvez tous les votes du groupe " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].") : ses positions politiques, sa cohésion interne, sa participation.";
        $data['title'] = $data['groupe']['libelle'];
      }
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
        ),
        array(
          "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes", "active" => FALSE
        ),
        array(
          "name" => "Tous les votes", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes/all", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => FALSE
        )
      );
      /// JS
      $data['js_to_load']= array("moment.min", "datatable-datan.min", "datetime-moment");
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/votes');
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }

    /* page: stats */
    public function individual_stats($legislature, $groupe){
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, $legislature);

      if (empty($data['groupe'])) {
        show_404($this->functions_datan->get_404_infos());
      };

      $data = $this->get_data($data);

      // Get history data
      $data['history'] = $this->groupes_model->get_history($data['groupe']['uid']);
      foreach ($data['history'] as $key => $value) {
        if ($value != $data['groupe']['uid']) {
          $get = $this->groupes_model->get_groupe_by_id($value);
          if ($get['legislature'] >= 14) {
            $data['history_list'][] = $get;
          }
        }
      }
      $data['stats_history'] = $this->groupes_model->get_stats_history($data['history']);

      // Get monthly data
      $data['stats_monthly'] = $this->groupes_model->get_stats_monthly($data['groupe']['uid']);

      // Remove majoritaire for Majority stats
      foreach ($data['stats_history']['majority'] as $key => $value) {
        if ($value['positionPolitique'] === 'Majoritaire') {
          unset($data['stats_history']['majority'][$key]);
        }
      }

      // Get individual stats data
      $data = $this->get_data_stats($data);

      // Get overall proximity stats
      $data['accord_groupes_actifs'] = $this->groupes_model->get_stats_proximite($data['groupe']['uid']);
      $data['accord_groupes_all'] = $this->groupes_model->get_stats_proximite_all($data['groupe']['uid']);
      if ($data['groupe']['legislature'] == legislature_current()) {
        $data['accord_groupes_featured'] = $data['accord_groupes_actifs'];
      } else {
        $data['accord_groupes_featured'] = $data['accord_groupes_all'];
      }
      $data['accord_groupes_first'] = $data['accord_groupes_featured'][0];
      $data['accord_groupes_last'] = end($data['accord_groupes_featured']);

      // Get history proximity stats
      $data['proximity_history'] = $this->groupes_model->get_stat_proximity_history($data['groupe']['uid']);
      $data['proximity_history'] = json_encode($data['proximity_history']);

      function date_compare($a, $b) {
        $t1 = strtotime($a['dateDebut']);
        $t2 = strtotime($b['dateDebut']);
        return $t1 - $t2;
      }
      usort($data['history_list'], 'date_compare');

      // Get group orga stats history
      $data['orga_history'] = $this->groupes_model->get_orga_stats_history($data['history']);

      // Get membership data by group (IF == current_legislature)
      if ($data['groupe']['legislature'] === legislature_current()) {
        $data['members'] = $this->groupes_model->get_groupes_all(TRUE, $data['groupe']['legislature']);
        foreach ($data['members'] as $key => $value) {
          $data['members'][$key]['value'] = $value['effectif'];
        }
        $data['members_max'] = $data['members'][0]['value'];
      }
      $data['members_history'] = $this->groupes_model->get_effectif_history($data['history']);
      $data['members_history_labels'] = json_encode($data['members_history']['labels']);
      $data['members_history_data'] = json_encode($data['members_history']['data']);
      $data['members_max_history'] = $this->groupes_model->get_effectif_history_max($data['history']);

      // Get age data
      $data['age'] = $this->stats_model->get_groups_age();
      foreach ($data['age'] as $key => $value) {
        $data['age'][$key]['value'] = $value['age'];
      }
      $data['age_max'] = $data['age'][0]['value'];

      // Get women data
      $data['women'] = $this->stats_model->get_groups_women();
      foreach ($data['women'] as $key => $value) {
        $data['women'][$key]['value'] = round($value['pct'] / 100, 2);
      }
      $data['women_max'] = $data['women'][0]['value'];

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Statistiques | Datan";
        $data['description_meta'] = "Retrouvez toutes les statistiques des députés non inscrits (NI) : participation aux votes, cohésion, proximité avec la majorité présidentielle.";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Statistiques du groupe " . name_group($data['groupe']['libelle']) . " | Datan";
        $data['description_meta'] = "Retrouvez toutes les statistiques du groupe " . name_group($data['groupe']['libelle']) . " (".$data['groupe']['libelleAbrev'].") : participation aux votes, cohésion, proximité avec la majorité présidentielle.";
        $data['title'] = $data['groupe']['libelle'];
      }
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
        ),
        array(
          "name" => name_group($data['groupe']['libelle']), "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Statistiques", "url" => base_url()."groupes/legislature-".$data['groupe']['legislature']."/".mb_strtolower($data['groupe']['libelleAbrev'])."/stats", "active" => TRUE
        )
      );
      $data['js_to_load_up_defer'] = array('chart.min.js');
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/stats');
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }
  }
?>
