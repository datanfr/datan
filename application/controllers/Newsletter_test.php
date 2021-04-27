<?php
  class Newsletter_test extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('newsletter_model');
    }

    // Delete all cache
    public function edit(){
      redirect();
    }

  }
?>
