<?php
  class Labo extends CI_Controller{

    public function __construct() {
      parent::__construct();
    }

    // Admin homepage
    public function coalition(){

      $data['title_meta'] = 'Labo : coalition';
      $data['url'] = NULL;
      
      $this->load->view('templates/header', $data);
      $this->load->view('labo/coalition');
      $this->load->view('templates/footer');
    }

  }
?>
