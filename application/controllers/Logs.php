<?php
  class Logs extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->password_model->security_only_admin();
    }

    public function index($name);
      $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/' . $name . '.log';
      $output = file_get_contents($file);
      echo '<pre>' . $file . '</pre>';

    }

  }
?>
