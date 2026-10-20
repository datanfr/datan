<?php
  class Redirection extends MY_Controller {
    public function __construct(){
      parent::__construct();
    }
    public function redir($elmt1){
      // Only allow internal redirects to prevent open redirect attacks
      $elmt1 = ltrim($elmt1, '/');
      if (preg_match('#^https?://#i', $elmt1) || strpos($elmt1, '//') === 0) {
        show_404();
        return;
      }
      redirect($elmt1);
    }


  }
 ?>
