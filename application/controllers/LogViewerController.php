<?php
  class LogViewerController extends CI_Controller{

    private $logViewer;

    public function __construct() {
        parent::__construct();
        $this->password_model->security_only_admin();
        $this->logViewer = new \CILogViewer\CILogViewer();
    }

    public function index() {
        echo $this->logViewer->showLogs();
        return;
    }

  }
?>
