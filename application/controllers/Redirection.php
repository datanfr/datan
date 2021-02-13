<?php
  class Redirection extends CI_Controller {
    public function __construct(){
      parent::__construct();
      $this->load->model('breadcrumb_model');
    }
    public function redir($elmt1){
      redirect($elmt1);
    }


  }
 ?>
