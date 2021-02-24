<?php
  class Commissions extends CI_Controller{
    public function __construct() {
      parent::__construct();
      //$this->password_model->security_password(); Former login protection
    }

    public function index(){
      $this->load->view('templates/construction');
    }
  }
?>
