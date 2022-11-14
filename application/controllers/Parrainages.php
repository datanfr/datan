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
      $data['parrainages'] = $this->parrainages_model->get_parrainages(2022, TRUE);
      foreach ($data['parrainages'] as $key => $value) {
        $data['parrainages'][$key]['candidat'] = $this->parrainages_model->change_candidate_name($value['candidat']);
      }
      $data['candidates'] = $this->parrainages_model->get_candidates(2022, TRUE);
      foreach ($data['candidates'] as $key => $value) {
        $data['candidates'][$key]['name'] = $this->parrainages_model->change_candidate_name($value['name']);
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
      $data['title_meta'] = "Élection présidentielle 2022 : les parrainages des députés | Datan";
      $data['description_meta'] = "Les députés peuvent accorder leur parrainage à un candidat à l'élection présidentielle. Découvrez sur Datan les parrainages accordés par les députés en 2022.";
      $data['title'] = "Élection présidentielle 2022 : découvrez les parrainages des députés";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."chart.min.css",
          "async" => FALSE
        ),
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load_up_defer'] = array('chart.min.js', 'chartjs-plugin-datalabels@2.1.js');
      $data['js_to_load']= array("moment.min", "datatable-datan.min", "datetime-moment", "flickity.pkgd.min");
      // Views
      $this->load->view('templates/header', $data);
      $this->load->view('parrainages/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
?>
