<?php
  class Logs extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->password_model->security_only_admin();
    }

    public function index(){
      $file = file_get_contents( "C:/wamp64/www/files_datan/logs/daily.log" );
      echo $_SERVER['DOCUMENT_ROOT'];
      //echo '<pre>' . $file . '</pre>';

    }

  }
?>
