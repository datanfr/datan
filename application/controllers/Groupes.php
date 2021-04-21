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
      //$this->password_model->security_password(); Former login protection
    }

    //INDEX - Homepage with all groups//
    public function actifs(){
      $data['active'] = TRUE;
      $data['groupes'] = $this->groupes_model->get_groupes_all($data['active'], legislature_current());
      $data['number_groupes_inactive'] = $this->groupes_model->get_number_inactive_groupes();
      $data['number'] = $this->groupes_model->get_number_active_groupes();
      $data['number_in_groupes'] = $this->groupes_model->get_number_mps_groupes();
      $data['number_unattached'] = $this->groupes_model->get_number_mps_unattached();

      // Groupe color
      foreach ($data['groupes'] as $key => $value) {
        $data['groupes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Groupes Parlementaires - Assemblée Nationale | Datan";
      $data['description_meta'] = "Retrouvez tous les groupes parlementaires en activité de la 15e législature. Résultats de vote et analyses pour chaque groupe parlementaire.";
      $data['title'] = "Les groupes politiques de l'Assemblée nationale";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => TRUE
        )
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

    //INDEX - Homepage with all inactive groups //
    public function inactifs(){
      $data['active'] = FALSE;
      $data['groupes'] = $this->groupes_model->get_groupes_all($data['active'], legislature_current());
      $data['number_groupes_inactive'] = $this->groupes_model->get_number_inactive_groupes();
      $data['number_groupes_active'] = $this->groupes_model->get_number_active_groupes();

      // Groupe color
      foreach ($data['groupes'] as $key => $value) {
        $data['groupes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Groupes Parlementaires plus en activité - Assemblée Nationale | Datan";
      $data['description_meta'] = "Retrouvez tous les groupes parlementaires plus en activité de la 15e législature. Résultats de vote et analyses pour chaque groupe parlementaire.";
      $data['title'] = "Les groupes politiques plus en activité";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Groupes", "url" => base_url()."groupes", "active" => FALSE
        ),
        array(
          "name" => "Groupes plus en activité", "url" => base_url()."groupes/inactifs", "active" => TRUE
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
    public function individual($groupe_slug){
      $data['legislature'] = legislature_current();
      $legislature = $data['legislature'];
      // Query 1 Informations principales
      $groupe_slug = mb_strtoupper($groupe_slug);
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe_slug, $legislature);

      if (empty($data['groupe'])) {
        show_404();
      }

      // Caching
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
          $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
      }

      // Query active
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }

      setlocale(LC_TIME, 'french');
      $data['dateDebut'] = strftime('%d %B %Y', strtotime($data['groupe']['dateDebut']));
      $data['dateDebutMois'] = strftime('%B %Y', strtotime($data['groupe']['dateDebut']));
      $groupe_uid = $data['groupe']['uid'];
      $groupe_ab = $data['groupe']['libelleAbrev'];
      $groupe_opposition = $data['groupe']['positionPolitique'];

      // Query 2 Membres du groupe
      $data['president'] = $this->groupes_model->get_groupes_president($groupe_uid, $data['active']);
      if (!empty($data['president'])) {
        $data['president'] = array_merge($data['president'], $this->depute_edito->gender($data['president']['civ']));
      }
      $data['membres'] = $this->groupes_model->get_groupe_membres($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }

      // Query get group_color
      $data['groupe']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe']['libelleAbrev'], $data['groupe']['couleurAssociee']));

      // Query effectif inactifs
      if (!$data['active']) {
        $data['groupe']['effectif'] = $this->groupes_model->get_effectif_inactif($groupe_uid);
        $data['groupe']['effectifShare'] = round(($data['groupe']['effectif'] / 577) * 100);
      }
      // Query nbr of groups
      $data['groupesN'] = $this->groupes_model->get_number_active_groupes();
      $data['groupesN'] = $data['groupesN']['n'];
      // Get mean age in the National Assembly
      $data['ageMean'] = $this->stats_model->get_age_mean($legislature);
      $data['ageMean'] = round($data['ageMean']['mean']);
      $data['ageEdited'] = $this->functions_datan->old_young($data['groupe']['age'], $data['ageMean']);
      // Get mean of women in the National Assembly
      $data['womenPctTotal'] = $this->deputes_model->get_deputes_gender($legislature);
      $data['womenPctTotal'] = $data['womenPctTotal'][1]['percentage'];
      $data['womenEdited'] = $this->functions_datan->more_less($data['groupe']['womenPct'], $data['womenPctTotal']);

      // Social Media du groupe
      $data['groupe'] = $this->groupes_model->get_groupe_social_media($data['groupe']);

      //Query 3 Statistiques
      $data['stats'] = $this->groupes_model->get_stats($groupe_uid);
      $data['statsAverage'] = $this->groupes_model->get_stats_avg($legislature);

      if (!empty($data['stats']['cohesion'])) {
        $data['cohesionAverage'] = $data['statsAverage']['cohesion'];
        $data['edito_cohesion'] = $this->groupes_edito->cohesion($data['stats']['cohesion'], $data['cohesionAverage']);
        $data['no_cohesion'] = FALSE;
      } else {
        $data['no_cohesion'] = TRUE;
      }

      if (!empty($data['stats']['participation'])) {
        $data['participationAverage'] = $data['statsAverage']['participation'];
        $data['edito_participation'] = $this->groupes_edito->cohesion($data['stats']['participation'], $data['participationAverage']);
        $data['no_participation'] = FALSE;
      } else {
        $data['no_participation'] = TRUE;
      }

      if (!empty($data['stats']['majorite'])) {
        $data['majoriteAverage'] = $data['statsAverage']['majorite'];
        $data['edito_majorite'] = $this->groupes_edito->cohesion($data['stats']['majorite'], $data['majoriteAverage']);
        $data['no_majorite'] = FALSE;
      } else {
        $data['no_majorite'] = TRUE;
      }

      if (isset($data['stats_majorite'])) {
        $data['stats_majorite_moyenne'] = $this->groupes_model->get_stats_majorite_moyenne($data['active']);
        $data['edito_majorite'] = $this->groupes_edito->majorite($data['stats_majorite']['score'], $data['stats_majorite_moyenne']['moyenne']);
        $data['no_majorite'] = FALSE;
      } else {
        $data['no_majorite'] = TRUE;
      }
      // ACCORD AVEC GROUPES
      $data['accord_groupes_actifs'] = $this->groupes_model->get_stats_proximite($groupe_uid); // PROXIMITÉ TOUS LES GROUPES
      $data['accord_groupes_all'] = $this->groupes_model->get_stats_proximite_all($groupe_uid);
      $accord_groupes_n = count($data['accord_groupes_actifs']);
      $accord_groupes_divided = round($accord_groupes_n / 2, 0, PHP_ROUND_HALF_UP);
      $data['accord_groupes_first'] = array_slice($data['accord_groupes_actifs'], 0, $accord_groupes_divided);
      $data['accord_groupes_first'] = array_slice($data['accord_groupes_first'], 0, 3);
      $data['accord_groupes_last'] = array_slice($data['accord_groupes_actifs'], $accord_groupes_divided, $accord_groupes_n);
      $data['accord_groupes_last'] = array_slice($data['accord_groupes_last'], -3);
      $data['groupes_positionnement'] = $this->groupes_edito->get_groupes_positionnement();
      if (!empty($data['accord_groupes_actifs'])) {
        $data['edito_proximite'] = $this->groupes_edito->proximite($data['accord_groupes_actifs'], $data['groupes_positionnement']);
        $data['no_proximite'] = FALSE;
      } else {
        $data['no_proximite'] = TRUE;
      }

      // Query 4 Votes
      $data['votes_datan'] = $this->votes_model->get_votes_datan_groupe($groupe_uid, 5);

      // Query 5 Edito
      $data['edito'] = $this->groupes_edito->edito($groupe_ab, $groupe_opposition, $data['groupes_positionnement']);
      // GET ALL OTHER GROUPES
      $data['groupesActifs'] = $this->groupes_model->get_groupes_all(TRUE, $legislature);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelle'] == "Non inscrit") {
        $data['title_meta'] = 'Députés non inscrits - Asssemblée Nationale | Datan';
        $data['description_meta'] = "Découvrez les résultats de vote des députés non inscrits (NI) : taux de participation, cohésion au sein groupe, proximité avec les autres groupes parlementaires.";
        $data['title'] = 'Députés non inscrits';
      } else {
        $data['title_meta'] = $data['groupe']['libelle']." - Assemblée Nationale | Datan";
        $data['description_meta'] = "Découvrez les résultats de vote du groupe ".$data['groupe']['libelle']." (".$data['groupe']['libelleAbrev'].") : taux de participation, cohésion au sein groupe, proximité avec les autres groupes parlementaires.";
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
          "name" => $data['groupe']['libelle'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => TRUE
        ),
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['critical_css'] = "assets/css/critical/groupe_individual.css";
      $data['css_to_load']= array(
        array(
          "url" => css_url()."circle.css",
          "async" => FALSE
        ),
        array(
          "url" => css_url()."chart.min.css",
          "async" => FALSE
        ),
        array(
          "url" => "https://unpkg.com/flickity@2/dist/flickity.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load_up'] = array("Chart.min.js", "chartjs-plugin-annotation.js"); // TO BE REMOVED
      $data['js_to_load_before_bootstrap'] = array("popper.min");
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

    public function individual_membres($groupe){
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, legislature_current());

      if (empty($data['groupe'])) {
        show_404();
      }

      // Query active
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }

      $groupe_uid = $data['groupe']['uid'];
      $data['president'] = $this->groupes_model->get_groupes_president($groupe_uid, $data['active']);
      $data['membres'] = $this->groupes_model->get_groupe_membres($groupe_uid, $data['active']);
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($groupe_uid, $data['active']);

      // Query get group_color
      $data['groupe']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe']['libelleAbrev'], $data['groupe']['couleurAssociee']));

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés en activité non inscrits (NI) de la 15e législature.";
        $data['title'] = "Députés non incrits (NI)";
      } else {
        $data['title_meta'] = "Députés ".$data['groupe']['libelle']." - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés en activité du groupe parlementaire ".$data['groupe']['libelle']." (".$data['groupe']['libelleAbrev'].").";
        $data['title'] = "Députés membres du groupe ".$data['groupe']['libelle']. " (".$data['groupe']['libelleAbrev'].")";
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
          "name" => $data['groupe']['libelle'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Membres", "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/membres", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
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
    public function individual_votes_datan($groupe){
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, legislature_current());
      if (empty($data['groupe'])) {
        show_404();
      };

      $groupe_uid = $data['groupe']['uid'];
      $groupe_ab = $data['groupe']['libelleAbrev'];
      $groupe_opposition = $data['groupe']['positionPolitique'];

      // Query active
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }
      // Query effectif inactifs
      if (!$data['active']) {
        $data['effectif'] = $this->groupes_model->get_effectif_inactif($groupe_uid);
      }

      // Query get group_color
      $data['groupe']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe']['libelleAbrev'], $data['groupe']['couleurAssociee']));

      // Variables about the group (1. dateDebut / 2. membres du groupe / 3. other groups)
      setlocale(LC_TIME, 'french');
      $data['dateDebut'] = strftime('%d %B %Y', strtotime($data['groupe']['dateDebut']));
      $data['president'] = $this->groupes_model->get_groupes_president($groupe_uid, $data['active']);
      if (!empty($data['president'])) {
        $data['president'] = array_merge($data['president'], $this->depute_edito->gender($data['president']['civ']));
      }
      $data['membres'] = $this->groupes_model->get_groupe_membres($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['groupesActifs'] = $this->groupes_model->get_groupes_all(TRUE, legislature_current());

      // Query - get active fields + votes by field + check the logos
      $data['fields'] = $this->fields_model->get_active_fields();
      foreach ($data['fields'] as $key => $field) {
        // Get votes by field
        $x[$field["slug"]] = $this->votes_model->get_votes_datan_groupe_field($groupe_uid, $field['slug'], 2);
        if (!empty($x[$field["slug"]])) {
          $data['fields_voted'][] = $field;
        }
        $x[$field["slug"]] = array_slice($x[$field["slug"]], 0, 2);
        foreach ($x[$field["slug"]] as $key2 => $value) {
          $x[$field["slug"]][$key2 ]['dateScrutinFRAbbrev'] = $this->functions_datan->abbrev_months($value['dateScrutinFR']);
        }
      }
      // Check the logos
      if ($data["fields_voted"]){
        foreach ($data["fields_voted"] as $key => $value) {
          if ($this->functions_datan->get_http_response_code(base_url().'/assets/imgs/fields/'.$value["slug"].'.svg') != "200"){
            $data['fields_voted'][$key]["logo"] = FALSE;
          } else {
            $data['fields_voted'][$key]["logo"] = TRUE;
          }
        }
      }
      $data['by_field'] = $x;

      // Edito
      $data['groupes_positionnement'] = $this->groupes_edito->get_groupes_positionnement();
      $data['edito'] = $this->groupes_edito->edito($groupe_ab, $groupe_opposition, $data['groupes_positionnement']);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions des députés non inscrits (NI) quand ils votent à l'Assemblée nationale.";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Groupe ".$data['groupe']['libelle']." - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions du groupe ".$data['groupe']['libelle']." (".$data['groupe']['libelleAbrev'].") quand il vote à l'Assemblée nationale.";
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
          "name" => $data['groupe']['libelle'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/votes_datan', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    /* page: groupes/x/votes/field */
    public function individual_votes_datan_field($groupe, $field){
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, legislature_current());

      if (empty($data['groupe'])) {
        show_404();
      };

      $groupe_uid = $data['groupe']['uid'];
      $groupe_ab = $data['groupe']['libelleAbrev'];
      $groupe_opposition = $data['groupe']['positionPolitique'];

      // Query - get active votes
      $data['votes'] = $this->votes_model->get_votes_datan_groupe_field($groupe_uid, $field, NULL);

      if (empty($data['votes'])) {
        show_404();
      }

      // Change data of votes
      foreach ($data['votes'] as $key => $value) {
        $data['votes'][$key]['dateScrutinFRAbbrev'] = $this->functions_datan->abbrev_months($value['dateScrutinFR']);
      }

      // Query active
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }
      // Query effectif inactifs
      if (!$data['active']) {
        $data['effectif'] = $this->groupes_model->get_effectif_inactif($groupe_uid);
      }

      // Query get group_color
      $data['groupe']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['groupe']['libelleAbrev'], $data['groupe']['couleurAssociee']));

      // Variables about the group (1. dateDebut / 2. membres du groupe / 3. other groups)
      setlocale(LC_TIME, 'french');
      $data['dateDebut'] = strftime('%d %B %Y', strtotime($data['groupe']['dateDebut']));
      $data['president'] = $this->groupes_model->get_groupes_president($groupe_uid, $data['active']);
      if (!empty($data['president'])) {
        $data['president'] = array_merge($data['president'], $this->depute_edito->gender($data['president']['civ']));
      }
      $data['membres'] = $this->groupes_model->get_groupe_membres($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['apparentes'] = $this->groupes_model->get_groupe_apparentes($groupe_uid, $data['active']);
      if ($data['groupe']['uid'] != 'PO723569') {
        $data['membres'] = array_slice($data['membres'], 0, 20);
      }
      $data['groupesActifs'] = $this->groupes_model->get_groupes_all(TRUE, legislature_current());

      // Query fields
      $data['field'] = $this->fields_model->get_field($field);

      // Edito
      $data['groupes_positionnement'] = $this->groupes_edito->get_groupes_positionnement();
      $data['edito'] = $this->groupes_edito->edito($groupe_ab, $groupe_opposition, $data['groupes_positionnement']);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions de vote des députés non inscrits (NI) concernant ".$data['field']['libelle'].".";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Groupe ".$data['groupe']['libelle']." - Votes | Datan";
        $data['description_meta'] = "Retrouvez toutes les positions de vote du groupe ".$data['groupe']['libelle']." (".$data['groupe']['libelleAbrev'].") concernant ".$data['field']['libelle'].".";
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
          "name" => $data['groupe']['libelle'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes", "active" => FALSE
        ),
        array(
          "name" => $data['field']['name'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes".NULL."/".$data['field']['slug'], "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/votes_datan_field', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    /* page: groupes/votes/all */
    public function individual_votes_all($groupe){
      $data['groupe'] = $this->groupes_model->get_groupes_individal($groupe, legislature_current());

      if (empty($data['groupe'])) {
        show_404();
      };

      $groupe_uid = $data['groupe']['uid'];
      $groupe_ab = $data['groupe']['libelleAbrev'];
      $groupe_opposition = $data['groupe']['positionPolitique'];

      // Query active
      if ($data['groupe']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }
      // Query effectif inactifs
      if (!$data['active']) {
        $data['effectif'] = $this->groupes_model->get_effectif_inactif($groupe_uid);
      }

      // Variables about the group (1. dateDebut / 2. membres du groupe / 3. other groups)
      setlocale(LC_TIME, 'french');
      $data['dateDebut'] = strftime('%d %B %Y', strtotime($data['groupe']['dateDebut']));
      $data['president'] = $this->groupes_model->get_groupes_president($groupe_uid, $data['active']);
      // Edito
      $data['groupes_positionnement'] = $this->groupes_edito->get_groupes_positionnement();
      $data['edito'] = $this->groupes_edito->edito($groupe_ab, $groupe_opposition, $data['groupes_positionnement']);

      // Query - get all votes
      $data['votes'] = $this->votes_model->get_votes_all_groupe($groupe_uid, legislature_current());

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['groupe']['libelleAbrev'] == "NI") {
        $data['title_meta'] = "Députés non incrits - Votes | Datan";
        $data['description_meta'] = "Retrouvez tous les votes des députés non inscrits (NI) : positions politiques, cohésion interne, participation.";
        $data['title'] = "Députés non inscrits";
      } else {
        $data['title_meta'] = "Groupe ".$data['groupe']['libelle']." - Votes | Datan";
        $data['description_meta'] = "Retrouvez tous les votes du groupe ".$data['groupe']['libelle']." (".$data['groupe']['libelleAbrev'].") : ses positions politiques, sa cohésion interne, sa participation.";
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
          "name" => $data['groupe']['libelle'], "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev']), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes", "active" => FALSE
        ),
        array(
          "name" => "Tous les votes à l'Assemblée nationale", "url" => base_url()."groupes/".mb_strtolower($data['groupe']['libelleAbrev'])."/votes/all", "active" => TRUE
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
      $data['js_to_load_before_bootstrap'] = array("popper.min");
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('groupes/votes');
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }
  }
?>
