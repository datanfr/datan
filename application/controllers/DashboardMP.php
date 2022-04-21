<?php
  class DashboardMP extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('admin_model');
      $this->load->model('deputes_model');
      $this->password_model->security_only_mp();
    }

    // Admin homepage
    public function index(){
      $data['username'] = $this->session->userdata('username');
      $user_id = $this->session->userdata('user_id');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/index_mp', $data);
      $this->load->view('dashboard/footer');
    }

  }
?>
