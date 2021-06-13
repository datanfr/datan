<?php
  class Faq extends CI_Controller {
    public function __construct() {
      parent::__construct();
      //$this->password_model->security_password(); Former login protection
    }

    public function index() {


      //Meta
      $data['title'] = "Foire aux questions";
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Datan | Comprendre l'Assemblée nationale";
      $data['description_meta'] = "L'Assemblée nationale enfin compréhensible ! Découvrez les résultats de vote de chaque député et groupe parlementaire.";
      //Open Graph
      //$data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      // JS
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('faq/index', $data);
      $this->load->view('templates/footer', $data);
    }

  }
?>
