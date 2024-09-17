<?php
  class Dossiers extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('dossiers_model');
    }

    // Page = datan.fr/votes
    public function index(){
      $data['dossiers'] = $this->dossiers_model->get_dossiers();

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Dossiers Assemblée Nationale - Projets et propositions de loi | Datan";
      $data['title'] = "Les dossiers à l'Assemblée nationale";
      $data['description_meta'] = "Retrouvez tous les dossiers en discussion à l'Assemblée nationale : projets de loi et propositions de loi. Découvrez les votes des députés et groupes sur ces textes.";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Dossiers", "url" => base_url()."dossiers", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['css_to_load']= array();
      // JS
      $data['js_to_load']= array();
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('dossiers/all');
      $this->load->view('templates/breadcrumb');
      $this->load->view('templates/footer');
    }    

  }
?>
