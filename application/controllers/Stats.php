<?php
  class Stats extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('stats_model');
      $this->load->model('breadcrumb_model');
      $this->load->model('deputes_model');
      $this->load->model('groupes_model');
      $this->load->model('depute_edito');
      $this->load->model('votes_model');
      //$this->password_model->security_password(); Former login protection
    }

    public function index(){
      $data['mps_oldest'] = $this->stats_model->get_mps_oldest();
      $data['mps_youngest'] = $this->stats_model->get_mps_youngest();
      $data['age_mean'] = $this->stats_model->get_age_mean();
      $data['groups_women_more'] = $this->stats_model->get_groups_women_more();
      $data['groups_women_less'] = $this->stats_model->get_groups_women_less();
      $data['women_mean'] = $this->deputes_model->get_deputes_gender(legislature_current());
      $data['mps_loyalty_more'] = $this->stats_model->get_mps_loyalty_more();
      $data['mps_loyalty_less'] = $this->stats_model->get_mps_loyalty_less();
      $data['loyalty_mean'] = $this->stats_model->get_loyalty_mean();
      $data['groups_age'] = $this->stats_model->get_groups_age();
      $data['groups_age_oldest'] = array_slice($data['groups_age'], 0, 1);
      $data['groups_age_oldest'] = $data['groups_age_oldest'][0];
      $data['groups_age_youngest'] = array_slice($data['groups_age'], -1);
      $data['groups_age_youngest'] = $data['groups_age_youngest'][0];
      $data['women_history'] = $this->stats_model->get_women_history();
      $data['women_history'] = array_slice($data['women_history'], -6);
      $data['groups_cohesion'] = $this->stats_model->get_groups_cohesion();
      foreach ($data['groups_cohesion'] as $key => $value) {
        if ($value['libelleAbrev'] == 'NI') {
          $keyRemoveNI = $key;
        }
      }
      unset($data['groups_cohesion'][$keyRemoveNI]);
      $data['groups_cohesion_first'] = $data['groups_cohesion'][0];
      $data['groups_cohesion_last'] = array_slice($data['groups_cohesion'], -1);
      $data['groups_cohesion_last'] = $data['groups_cohesion_last'][0];
      $data['mps_participation'] = $this->stats_model->get_mps_participation();
      $data['mps_participation_first'] = array_slice($data['mps_participation'], 0, 3);
      $data['mps_participation_last'] = array_slice($data['mps_participation'], -3);
      $data['mps_participation_mean'] = $this->stats_model->get_mps_participation_mean();
      $data['mps_participation_mean'] = $data['mps_participation_mean']['mean'];
      $data['groups_participation'] = $this->stats_model->get_groups_participation();
      $data['groups_participation_first'] = array_slice($data['groups_participation'], 0, 1);
      $data['groups_participation_first'] = $data['groups_participation_first'][0];
      $data['groups_participation_last'] = array_slice($data['groups_participation'], -1);
      $data['groups_participation_last'] = $data['groups_participation_last'][0];

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Nos statistiques", "url" => base_url()."statistiques", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Statistiques et classements - Assemblée nationale | Datan";
      $data['description_meta'] = "Classements des députés et des groupes. ";
      $data['title'] = "L'Assemblée nationale en chiffres";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_before_bootstrap'] = array("popper.min");
      // Views
      $this->load->view('templates/header', $data);
      $this->load->view('classements/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function individual($url){
      if (!file_exists(APPPATH.'views/classements/individual/'.$url.'.php')) {
        show_404();
      }

      $data['page'] = $url;

      if ($url == "deputes-age") {
        // Data
        $data['ageMean'] = $this->stats_model->get_age_mean();
        $data['ageMean'] = round($data['ageMean']['mean']);
        $data['ageMeanPop'] = 42;
        $data['ageDiff'] = $data['ageMean'] - $data['ageMeanPop'];
        $data['ageDiffStr'] = $this->functions_datan->int2str($data['ageDiff']);
        $data['mpOldest'] = $this->stats_model->get_mps_oldest(1);
        $data['mpOldest']["name"] = $data['mpOldest']["nameFirst"]." ".$data['mpOldest']["nameLast"];
        $data['mpOldestGender'] = $this->depute_edito->gender($data['mpOldest']["civ"]);
        $data['mpYoungest'] = $this->stats_model->get_mps_youngest(1);
        $data['mpYoungest']["name"] = $data['mpYoungest']["nameFirst"]." ".$data['mpYoungest']["nameLast"];
        $data['mpYoungestGender'] = $this->depute_edito->gender($data['mpYoungest']["civ"]);
        $data['deputes'] = $this->stats_model->get_ranking_age();

        // Meta
        $data['title_meta'] = "L'âge des députés - Assemblée nationale | Datan";
        $data['description_meta'] = "Qui est le député le plus âgé ? Le plus jeune ? Découvrez sur Datan le classement des députés de l'Assemblée nationale selon leur âge.";
        $data['title'] = "L'âge des députés";

      } elseif ($url == "groupes-age") {
        // Data
        $data['ageMeanPop'] = 42;
        $data['groupsAge'] = $this->stats_model->get_groups_age();
        $data['groupOldest'] = array_slice($data['groupsAge'], 0, 1);
        $data['groupOldest'] = $data['groupOldest'][0];
        $data['groupOldest']['couleurAssociee'] = $this->groupes_model->get_groupe_color($data['groupOldest']);
        $data['groupYoungest'] = array_slice($data['groupsAge'], -1);
        $data['groupYoungest'] = $data['groupYoungest'][0];

        // Meta
        $data['title_meta'] = "L'âge moyen au sein des groupes - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel est le groupe de l'Assemblée avec la moyenne d'âge la plus élevée ? Découvrez sur Datan le classement des groupes selon l'âge de leurs membres.";
        $data['title'] = "L'âge moyen au sein des groupes";

      } elseif ($url == "groupes-feminisation") {
        // Data --> REVIEW_COULEURASSOCIEE
        $women_mean = $this->deputes_model->get_deputes_gender(legislature_current());
        $data['womenMean']['n'] = $women_mean[1]['n'];
        $data['womenMean']['pct'] = $women_mean[1]['percentage'];
        $data['womenMean']['nSociety'] = 52;
        $data['womenMean']['diff'] = abs($women_mean[1]['percentage'] - $data['womenMean']['nSociety']);
        $data['groupsWomen'] = $this->stats_model->get_groups_women();
        $data['groupsWomenFirst'] = $data['groupsWomen'][0];
        $data['groupsWomenLast'] = end($data['groupsWomen']);

        // Meta
        $data['title_meta'] = "La féminisation des groupes politiques - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel est le groupe de l'Assemblée avec le plus de députées femmes ? Découvrez sur Datan le classement des groupes selon leur taux de féminisation.";
        $data['title'] = "Le taux de féminisation des groupes parlementaires";
      } elseif ($url == "deputes-loyaute") {
        // TO DO --> Data --> REVIEW_COULEURASSOCIEE
        $data['deputes'] = $this->stats_model->get_mps_loyalty();
        $data['mpLoyal'] = array_slice($data['deputes'], 0, 1);
        $data['mpLoyal'] = $data['mpLoyal'][0];
        $data['mpLoyal']['name'] = $data['mpLoyal']['nameFirst']." ".$data['mpLoyal']['nameLast'];
        $data['mpLoyalGender'] = $this->depute_edito->gender($data['mpLoyal']["civ"]);
        $data['mpRebel'] = end($data['deputes']);
        $data['mpRebel']['name'] = $data['mpRebel']['nameFirst']." ".$data['mpRebel']['nameLast'];
        $data['mpRebelGender'] = $this->depute_edito->gender($data['mpRebel']["civ"]);

        // Meta
        $data['title_meta'] = "La loyauté des députés - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel est le député de l'Assemblée nationale le plus loyal à son groupe ? Le député le plus rebelle ? Découvrez le classement sur Datan.";
        $data['title'] = "La loyauté politique des députés";
      } elseif ($url == "groupes-cohesion") {
        $data['groups'] = $this->stats_model->get_groups_cohesion();
        foreach ($data['groups'] as $key => $value) {
          if ($value['libelleAbrev'] == 'NI') {
            $keyRemoveNI = $key;
          }
        }
        $data['groupsCards'] = $data['groups'];
        unset($data['groupsCards'][$keyRemoveNI]);
        $data['groupsFirst'] = $data['groupsCards'][0];
        $data['groupsFirst']['couleurAssociee'] = $this->groupes_model->get_groupe_color($data['groupsFirst']);
        $data['groupsLast'] = end($data['groupsCards']);
        $data['groupsLast']['couleurAssociee'] = $this->groupes_model->get_groupe_color($data['groupsLast']);
        $data['cohesionMean'] = $this->groupes_model->get_stats_cohesion_moyenne(FALSE);
        $data['cohesionMean'] = $data['cohesionMean']['moyenne'];

        // Meta
        $data['title_meta'] = "La cohésion des groupes parlementaires - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel est le groupe politique le plus soudé à l'Assemblée nationale ? Celui qui vote le moins souvent ensemble ? Découvrez le classement sur Datan.";
        $data['title'] = "La cohésion des groupes parlementaires";
      } elseif ($url == "deputes-participation") {
        $data['participationMean'] = $this->stats_model->get_mps_participation_mean();
        $data['participationMean'] = $data['participationMean']['mean'];
        $data['participationCommissionMean'] = $this->stats_model->get_mps_participation_commission_mean();
        $data['participationCommissionMean'] = $data['participationCommissionMean']['mean'];
        $data['mps'] = $this->stats_model->get_mps_participation();
        $data['votesN'] = $this->votes_model->get_n_votes();
        $data['mpsCommission'] = $this->stats_model->get_mps_participation_commission();

        // Meta
        $data['title_meta'] = "La participation des députés - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel parlementaire vote le plus souvent ? Quel député vote le moins souvent ? Découvrez le classement sur Datan.";
        $data['title'] = "La participation des députés";
      } elseif ($url == "groupes-participation") {
        $data['groups'] = $this->stats_model->get_groups_participation();
        $data['groupsFirst'] = $data['groups'][0];
        $data['groupsFirst']['couleurAssociee'] = $this->groupes_model->get_groupe_color($data['groupsFirst']);
        $data['groupsLast'] = end($data['groups']);
        $data['groupsLast']['couleurAssociee'] = $this->groupes_model->get_groupe_color($data['groupsLast']);

        // Meta
        $data['title_meta'] = "La participation des groupes politiques - Assemblée nationale | Datan";
        $data['description_meta'] = "Quel groupe parlementaire est le plus actif au moment de voter ? Quel groupe a le plus faible taux de participation ? Découvrez le classement sur Datan.";
        $data['title'] = "La participation des groupes politiques";
      }

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Nos statistiques", "url" => base_url()."statistiques", "active" => FALSE
        ),
        array(
          "name" => $data['title'], "url" => base_url()."statistiques/".$url, "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $data['url'] = $this->meta_model->get_url();
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      $data['css_to_load']= array(
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => FALSE
        )
      );
      // JS
      //$data['js_to_load_before_bootstrap'] = array("");
      //$data['js_to_load_before_datan'] = array("");
      $data['js_to_load']= array("moment.min", datatable_file(), "datetime-moment", "datan/async_background");
      // Meta
      $data['url'] = $this->meta_model->get_url();
      // Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('classements/templates/header', $data);
      $this->load->view('classements/individual/'.$url, $data);
      $this->load->view('classements/templates/footer', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }
  }
?>
