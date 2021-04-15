<?php
  class Departement extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('departement_model');
      $this->load->model('deputes_model');
      $this->load->model('groupes_model');
      //$this->load->helper('url');
      //$this->password_model->security_password(); Former login protection
    }

    public function view($slug){
      $data['departement'] = $this->departement_model->get_departement($slug);

      if (empty($data['departement'])) {
        show_404();
      }

      $data['deputes'] = $this->deputes_model->get_deputes_all(legislature_current(), TRUE, $slug);

      foreach ($data['deputes'] as $key => $value) {
        $data['deputes'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['groupLibelleAbrev'], $value['couleurAssociee']));
        $data['deputes'][$key]['circoAbbrev'] = $this->functions_datan->abbrev_n($value['circo'], TRUE);
        $data['deputes'][$key]['cardCenter'] = $data['deputes'][$key]['circo']."<sup>".$data['deputes'][$key]['circoAbbrev']."</sup> circonscription";
      }

      $data['communes'] = $this->departement_model->get_communes_population($slug);

      if (empty($data['deputes'])) {
        show_404();
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Députés ".$data['departement']['departement_nom']." ".$data['departement']['departement_code']." | Datan";
      $data['description_meta'] = "Découvrez la liste des députés élus dans le département ".$data['departement']['libelle_2']."".$data['departement']['departement_nom']." (".$data['departement']['departement_code']."). Tous les députés des circonscriptions ".$data['departement']['libelle_2']."".$data['departement']['departement_nom'].".";
      if ($data['departement']['departement_code'] == '099') {
        $data['title'] = "Députés ".$data['departement']['libelle_2'];
      } else {
        $data['title'] = "Députés ".$data['departement']['libelle_2']." ".$data['departement']['departement_nom']." (".$data['departement']['departement_code'].")";
      }
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Députés", "url" => base_url()."deputes", "active" => FALSE
        ),
        array(
          "name" => $data['departement']['departement_nom']." (".$data['departement']['departement_code'].")", "url" => base_url()."deputes/".$slug, "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('departement/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function liste(){
      $data['departements'] = $this->departement_model->get_all_departements();

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Départements et territoires d'outre-mer | Datan";
      $data['description_meta'] = "Découvrez la liste des départements et territoires d'outre mer de la France.";
      $data['title'] = "Départements et territoires d'outre mer de la France";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Tous les départements", "url" => base_url()."index_departements", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('departement/liste', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
?>
