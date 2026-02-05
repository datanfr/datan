<?php
  class Deputes extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('deputes_model');
      $this->load->model('depute_edito');
      $this->load->model('votes_model');
      $this->load->model('departement_model');
      $this->load->model('groupes_model');
      $this->load->model('fields_model');
      $this->load->model('elections_model');
      $this->load->model('jobs_model');
      $this->load->model('parrainages_model');
      $this->load->library('depute_service');
      $this->load->library('election_service');
      $this->load->library('group_service');
      //$this->password_model->security_password(); Former login protection
    }


    public function index($legislature = NULL) {

      if ($legislature == legislature_current()) {
        redirect('deputes');
      }

      if ($legislature == NULL) {
        $legislature = legislature_current();
      }

      if ($legislature < 14) {
        show_404($this->functions_datan->get_404_infos());
      }

      if ($legislature == legislature_current() && dissolution() === false) {
        $data['active'] = TRUE;
        /* PRESIDENT TO UPDATE LATER
        //$data['president'] = $this->deputes_model->get_president_an(); THE OPEN DATA FROM THE AN IS NOT UPDATED!
        $data['president'] = $this->deputes_model->get_depute_by_mpId('PA721908');
        if ($data['president']) {
          $data['president']['gender'] = gender($data['president']['civ']);
        }
        */
        $data['president'] = FALSE;
      } else {
        $data['active'] = FALSE;
      }

      $data['legislature'] = $legislature;
      $data['deputes'] = $this->deputes_model->get_deputes_all($legislature, $data['active'], NULL);
      $data['groupes'] = $this->groupes_model->get_groupes_from_mp_array($data['deputes']);
      $data['groupes_mobile'] = $this->groupes_model->get_groupes_all($data['active'], $legislature);
      $number_gender = $this->deputes_model->get_deputes_gender($legislature);
      foreach ($number_gender as $gender) {
        if ($gender["gender"] == "male") {
          $data["male"]["n"] = $gender["n"];
          $data["male"]["percentage"] = $gender["percentage"];
        } elseif ($gender["gender"] == "female") {
          $data["female"]["n"] = $gender["n"];
          $data["female"]["percentage"] = $gender["percentage"];
        }
      }
      $data['number_inactive'] = $this->deputes_model->get_n_deputes_inactive($legislature);

      // Groupe_color
      foreach ($data['deputes'] as $key => $value) {
        $data['deputes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      // Breadcrumb
      if ($legislature == legislature_current()) {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Députés", "url" => base_url()."deputes", "active" => TRUE
          )
        );
      } else {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Députés", "url" => base_url()."deputes", "active" => FALSE
          ),
          array(
            "name" => $legislature."ème législature", "url" => base_url()."deputes/legislature-".$legislature, "active" => TRUE
          )
        );
      }

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($legislature == legislature_current()) {
        $data['title_meta'] = "Députés - Assemblée Nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés en activité de l'Assemblée nationale de la ".legislature_current()."ème législature. Résultats de vote et analyses pour chaque député.";
        $data['title'] = "Les députés de l'Assemblée nationale";
      } else {
        $data['title_meta'] = "Députés ".$legislature."ème législature - Assemblée nationale | Datan";
        $data['description_meta'] = "Retrouvez tous les députés en activité de l'Assemblée nationale de la ".$legislature."ème législature. Résultats de vote et analyses pour chaque député.";
        $data['title'] = "Les députés de la ".$legislature."ème législature";
      }
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_before_datan'] = array("libraries/isotope/isotope.pkgd.min");
      $data['js_to_load']= array("datan/sorting");
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('deputes/all', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    public function inactifs(){
      $data['active'] = FALSE;
      $data['legislature'] = legislature_current();
      $data['deputes'] = $this->deputes_model->get_deputes_all(legislature_current(), $data['active'], NULL);
      $data['groupes'] = $this->deputes_model->get_groupes_inactifs();
      $data['number_inactive'] = count($data['deputes']);

      // Groupe_color
      foreach ($data['deputes'] as $key => $value) {
        $data['deputes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }

      $data['title'] = "Les députés plus en activité";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Députés", "url" => base_url()."deputes", "active" => FALSE
        ),
        array(
          "name" => "Députés plus en activité", "url" => base_url()."deputes/inactifs", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Députés plus en activité - Assemblée Nationale | Datan";
      $data['description_meta'] = "Retrouvez tous les députés plus en activité de l'Assemblée nationale de la 15ème législature. Résultats de vote et analyses pour chaque député.";
      $data['title'] = "Les anciens députés de la ".legislature_current()."ème legislature";
      // Open graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_before_datan'] = array("libraries/isotope/isotope.pkgd.min");
      $data['js_to_load']= array("datan/sorting");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('deputes/all', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function individual($nameUrl, $departement)
    {
      setlocale(LC_TIME, 'french');
      // _____________________GET INFOS MP____________________
      $data['depute'] = $this->deputes_model->get_depute_individual($nameUrl, $departement);

      // ____________________CHECK IF DEPUTE EXISTS__________________
      if (empty($data['depute'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // ____________________CHECK IF LEGISLATURE > 14_______________
      if (!$data['depute']['legislature'] >= 14) {
        show_404($this->functions_datan->get_404_infos());
      }

      // ____________________CACHING_________________________________
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
        $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
      }

      // ____________________MAIN VARIABLES___________________________
      $depute = $data['depute'];
      $mp_id = $depute['mpId'];
      $name_last = $depute['nameLast'];
      $depute_dpt = $depute['dptSlug'];
      $data['active'] = $depute['active'];
      $legislature = $depute['legislature'];
      $data['infos_groupes'] = groups_position_edited();
      $depute_full_name = $depute['nameFirst'].' '.$depute['nameLast'];
      $data['no_job'] = array('autre profession','autres', 'sans profession déclarée', 'sans profession');
      $groupe_id = $depute['groupeId'];
      $data['first_person'] = false;
      $data['photo_square'] = $this->deputes_model->get_photo_square($legislature);
      $data['gender'] = gender($depute['civ']);
      $data['page_history'] = FALSE; 
    
      // ____________________GET HAVP___________________________________
      $data['depute']['hatvp'] = $this->deputes_model->get_hatvp_url($mp_id);
      $data['hatvpJobs'] = $this->deputes_model->get_last_hatvp_job($mp_id);

      // ____________________GET GROUP___________________________________
      $data = $this->group_service->get_group_infos_by_mp($data, $mp_id, $groupe_id);
    
      // ____________________GET GENERAL INFOS___________________________
      $data = $this->depute_service->get_general_infos($data, $mp_id, $legislature, $name_last);

      // ____________________GET MAJORITY GROUP___________________________
      $data['groupMajority'] = $this->groupes_model->get_majority_group($legislature);
    
      // ____________________GET PCT FAMSOCPRO___________________________
      $data['famSocPro'] = null;// $this->jobs_model->get_stats_individual($data['depute']['famSocPro'], $legislature);

      // ____________________GET COMISSION PARLEMENTAIRE_________________
      if ($data['active']) {
        $data['commission_parlementaire'] = $this->deputes_model->get_commission_parlementaire($mp_id, $legislature);
      }
    
      // ____________________GET ALL ELECTIONS___________________________
      $data['elections'] = $this->election_service->get_all_elections($mp_id, $data['gender']);

      // ____________________GET ELECTION FEATURE________________________
      $data['electionFeature'] = $this->elections_model->get_candidate_election($mp_id, 7, TRUE, FALSE);
      $data['electionFeatureDistrict'] = $this->city_model->get_city_by_insee($data['electionFeature']['district']);
      
      // ____________________GET PROFESSION DE FOI________________________
      $data['professions_foi'] = $this->deputes_model->get_professions($mp_id);

      // ____________________GET PARRAINNAGES_____________________________
      $data['parrainage'] = $this->parrainages_model->get_mp_parrainage($mp_id, 2022); /* Parrainage for presidentielle 2022 */
      if ($data['parrainage']) {
        $data['parrainage']['candidat'] = $this->parrainages_model->change_candidate_name($data['parrainage']['candidat']);
      }
      
      //____________________GET STATISTICS__________________________________
      $data = $this->depute_service->get_statistics($data, $legislature, $mp_id, $groupe_id); 

      //___________________GET OTHER MPS____________________________________
      $related_deputes = $this->depute_service->get_other_mps($legislature, $groupe_id, $name_last, $mp_id, $data['active'], $depute_dpt);
      $data['other_deputes'] = $related_deputes['other_deputes'];
      $data['other_deputes_dpt'] = $related_deputes['other_deputes_dpt'];
      $data['depute']['dateNaissanceFr'] = utf8_encode(strftime('%d %B %Y', strtotime($data['depute']['birthDate']))); // birthdate

      //___________________GET VOTES_________________________________________
      $data['votes_datan'] = $legislature >= 15 ? $this->votes_model->get_votes_datan_depute($mp_id, 5) : NULL; // Edited votes by datan
      $data['key_votes'] = $legislature >= 15 ? $this->votes_model->get_key_votes_mp($mp_id) : NULL; // Key votes 

      //__________________ Get FEATURED VOTE ________________________________
      $data['voteFeature'] = $this->votes_model->get_individual_vote_depute($mp_id, 17, 3684); // Vote suspension réforme retraite

      //__________________GET LAST EXPLICATION_______________________________
      $data['explication'] = $this->depute_service->get_explication_details($mp_id, $legislature, $data['gender']);

      // _________________Get MPs HISTORY___________________________
      $data = $this->depute_service->get_mp_history_data($data, $mp_id);
      
      // ________________ GET Depute page ressources (meta, css, js...)_______
      $data = $this->depute_service->get_mp_page_resources("individual", $data, $depute_full_name, $nameUrl);

      // ________________LOAD VIEWS_______________________
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('deputes/individual', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function historique($nameUrl, $departement, $legislature){
      setlocale(LC_TIME, 'french');
      // _____________________GET INFOS MP____________________
      $data['depute'] = $this->deputes_model->get_depute_individual_historique($nameUrl, $departement, $legislature);
      $latest_dpt = $this->deputes_model->get_mp_latest_dpt($data['depute']['mpId'], $departement);
      $data['depute_last'] = $this->deputes_model->get_depute_individual($nameUrl, $latest_dpt);

      // ____________________CHECK IF DEPUTE EXISTS__________________
      if (empty($data['depute'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // ____________________CHECK IF LEGISLATURE > 14_______________
      if (!in_array($data['depute']['legislature'], legislature_all())) {
        show_404($this->functions_datan->get_404_infos());
      }

      // ____________________REDIRECT IF URL IS LATEST MANDATE_______
      if ($legislature == $data['depute_last']['legislature']) {
        redirect("deputes/" . $data['depute']['dptSlug'] . "/depute_" . $data['depute']['nameUrl']);
      }

      // ____________________MAIN VARIABLES__________________________
      $depute = $data['depute'];
      $mp_id = $depute['mpId'];
      $name_last = $depute['nameLast'];
      $data['active'] = $depute['active'];
      $data['legislature'] = $legislature;
      $legislature = $depute['legislature'];
      $depute_full_name = $depute['nameFirst'].' '.$depute['nameLast'];
      $data["depute"]["dateNaissanceFr"] = utf8_encode(strftime('%d %B %Y', strtotime($data['depute']['birthDate']))); // birthdate
      $data['depute']['circo_abbrev'] = abbrev_n($data['depute']['circo'], TRUE); // circo number
      $data['mandats'] = $this->deputes_model->get_historique_mandats($mp_id);
      $data['mandatsReversed'] = array_reverse($data['mandats']);
      $groupe_id = $depute['groupeId'];
      $data['first_person'] = false;
      $data['photo_square'] = $this->deputes_model->get_photo_square($legislature);            
      $data['gender'] = gender($depute['civ']); 
      $data['page_history'] = TRUE;

      // ____________________GET GENERAL INFOS_______________________
      $data = $this->depute_service->get_general_infos($data, $mp_id, $legislature, $name_last);
      
      //____________________GET STATISTICS___________________________
      $data = $this->depute_service->get_statistics($data, $legislature, $mp_id, $groupe_id);

      // ____________________GET MAJORITY GROUP___________________________
      $data['groupMajority'] = $this->groupes_model->get_majority_group($legislature);

      // ________________ GET Depute page ressources (meta, css, js...)_______
      $data = $this->depute_service->get_mp_page_resources("history", $data, $depute_full_name, $nameUrl);

      // ________________ LOAD VIEWS ________________________________
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('deputes/individual', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    // Pages deputes/(:depute)/votes
    public function votes($input, $departement){
      // Query 1 = infos générales députés
      $input_depute = $input;
      $data['depute'] = $this->deputes_model->get_depute_individual($input, $departement);

      if (empty($data['depute'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      // Check if it is in legislature
      if (!$data['depute']['legislature'] >= 15) {
        show_404($this->functions_datan->get_404_infos());
      }

      $mpId = $data['depute']['mpId'];
      $nameLast = $data['depute']['nameLast'];
      $nameUrl = $input_depute;
      $data['active'] = $data['depute']['active'];
      $legislature = $data['depute']['legislature'];
      $groupe_id = $data['depute']['groupeId'];

      // Photo_square
      $data['photo_square'] = $data['depute']['legislature'] >= 17 ? TRUE : FALSE;

      // Group color
      $data['depute']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute']['libelleAbrev'], $data['depute']['couleurAssociee']));

      // Commission parlementaire
      $data['commission_parlementaire'] = $this->deputes_model->get_commission_parlementaire($mpId, $legislature);

      // Get active fields
      $data['fields'] = $this->fields_model->get_active_fields();

      // Get votes
      $data['votes'] = $this->votes_model->get_votes_datan_depute($mpId);
      $data['votes_all'] = $this->votes_model->get_votes_all_depute($mpId, FALSE);

      // Query - gender
      $data['gender'] = gender($data['depute']['civ']);

      // Historique du député
      $data['mandat_edito'] = $this->depute_edito->get_nbr_lettre($data['depute']['mandatesN']);

      // Other MPs from the same group
      $data['other_deputes'] = $this->deputes_model->get_other_deputes($groupe_id, $nameLast, $mpId, $data['active'], $legislature);
      // OTHER MPs from the same departement
      $data['other_deputes_dpt'] = $this->deputes_model->get_deputes_all($legislature, $data['active'], $departement);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $depute = $data['depute']['nameFirst'].' '.$data['depute']['nameLast'];
      $data['title_meta'] = $depute." - Votes | Datan";
      $data['description_meta'] = "Découvrez toutes les positions ".$data['gender']['du']." député".$data['gender']['e']." ".$depute." quand ".$data['gender']['pronom']." vote l'Assemblée nationale.";
      $data['title'] = $depute;
      $data['title_breadcrumb'] = mb_substr($data['depute']['nameFirst'], 0, 1).'. '.$data['depute']['nameLast'];
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Députés", "url" => base_url()."deputes", "active" => FALSE
        ),
        array(
          "name" => $data['depute']['departementNom']." (".$data['depute']['departementCode'].")", "url" => base_url()."deputes/".$data['depute']['dptSlug'], "active" => FALSE
        ),
        array(
          "name" => $data['title_breadcrumb'], "url" => base_url()."deputes/".$data['depute']['dptSlug']."/depute_".$nameUrl, "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."deputes/".$data['depute']['dptSlug']."/depute_".$nameUrl."/votes", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_before_datan'] = array("libraries/isotope/isotope.pkgd.min");
      $data['js_to_load']= array(
        "datan/sorting",
        "libraries/moment/moment.min",
        "dist/datatable-datan.min",
        "libraries/datetime/datetime-moment"
      );
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => FALSE
        )
      );
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('deputes/votes', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
?>