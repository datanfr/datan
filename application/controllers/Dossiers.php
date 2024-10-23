<?php
  class Dossiers extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('dossiers_model');
    }

    // Page = datan.fr/dossiers & datan.fr/dossiers/legislature-(:any)
    public function index($legislature = NULL){

      if ($legislature == legislature_current()) {
        redirect('dossiers');
      }

      $legislature = $legislature ?? legislature_current();

      if ($legislature < 15) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['dossiers'] = $this->dossiers_model->get_dossiers($legislature);
      $data['dossiers_last'] = array_slice($data['dossiers'], 0, 4);
      $data['legislature'] = $legislature;

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($legislature == legislature_current()) {
        $data['title_meta'] = "Dossiers législatifs - Projets et propositions de loi | Datan";
        $data['description_meta'] = "Retrouvez tous les dossiers en discussion à l'Assemblée nationale : projets et propositions de loi. Découvrez les votes des députés et groupes sur ces textes pendant la ".$legislature."ème législature.";
        $data['title'] = "Les dossiers législatifs de l'Assemblée nationale";
      } else {
        $data['title_meta'] = "Dossiers législatifs de la " . $legislature . "ème législature - Projets et propositions de loi | Datan"; 
        $data['description_meta'] = "Retrouvez tous les dossiers en discussion à l'Assemblée nationale : projets et propositions de loi. Découvrez les votes des députés et groupes sur ces textes pendant la ".$legislature."ème législature.";
        $data['title'] = "Les dossiers législatifs de la ".$legislature."ème législature";
      }
      // Breadcrumb
      if ($legislature == legislature_current()) {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Dossiers", "url" => base_url()."dossiers", "active" => TRUE
          )
        );
      } else {
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Dossiers", "url" => base_url()."dossiers", "active" => FALSE
          ),
          array(
            "name" => $legislature."ème législature", "url" => base_url()."dossiers/legislature-".$legislature, "active" => TRUE
          )
        );
      }

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

    // Page = datan.fr/dossiers/legislature-(:any)/(:any)
    public function individual($legislature, $dossierUrl){
      echo "work here";
      echo $legislature;
      echo $dossierUrl;
    }

  }
?>
