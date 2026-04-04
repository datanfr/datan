<?php
  class Logs extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->password_model->security_only_admin();
    }

    public function index($name) {
      // Prevent path traversal: allow only alphanumeric, hyphens, and underscores
      if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
        show_404();
        return;
      }
      $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/' . $name . '.log';
      if (!file_exists($file)) {
        show_404();
        return;
      }
      $output = file_get_contents($file);
      echo '<pre>' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</pre>';
    }

  }
?>
