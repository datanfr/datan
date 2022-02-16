<?php
  class Parrainages extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('parrainages_model');
      //$this->password_model->security_password(); Former login protection
    }

    public function index(){
      $data['candidates_selected'] = $this->parrainages_model->get_500(2022);
      foreach ($data['candidates_selected'] as $key => $value) {
        $data['candidates_selected'][$key]['candidat'] = $this->parrainages_model->change_candidate_name($value['candidat']);
      }
      

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Parrainages 2022", "url" => base_url()."parrainages", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Statistiques et classements - Assemblée nationale | Datan";
      $data['description_meta'] = "Classements des députés et des groupes. ";
      $data['title'] = "Élection présidentielle 2022 : découvrez les parrainages des députés";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load_up'] = array("chart.min.js");
      // Views
      $this->load->view('templates/header', $data);
      $this->load->view('parrainages/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
?>
