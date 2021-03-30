<?php
  class Elections extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('elections_model');
      //$this->password_model->security_password(); Former login protection
    }

    public function index(){
      // Data
      $data['elections'] = $this->elections_model->get_election_all();
      $data['electionsColor'] = $this->elections_model->get_election_color();

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Élections", "url" => base_url()."elections", "active" => TRUE
        )
      );

      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Les élections en France - Candidats et résultats | Datan";
      $data['description_meta'] = "Retrouvez toutes les informations sur les différentes élections en France (présidentielle, législative, régionales). Découvrez les députés candidats et les résultats";
      $data['title'] = "Les élections politiques en France";
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('elections/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }
  }
?>
