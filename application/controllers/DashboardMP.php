<?php
  class DashboardMP extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('admin_model');
      $this->load->model('deputes_model');
      $this->load->model('elections_model');
      $this->password_model->security_only_mp();
    }

    public function index(){
      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function elections($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['title'] = "Candidature pour les élections " . mb_strtolower($data['election']['libelleAbrev']). " ".$data['election']['dateYear'];

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/elections/index', $data);
      $this->load->view('dashboard/footer');

    }

    public function elections_modify($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['title'] = "Modifier la candidature pour les élections " . mb_strtolower($data['election']['libelleAbrev']). " ".$data['election']['dateYear'];

      $data['districts'] = $this->elections_model->get_all_districts($data['election']['id']);

      //Form valiation
      $this->form_validation->set_rules('candidature', 'Candidature', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/elections/modify', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->admin_model->modify_candidat_as_mp($data['depute']['mpId'], $data['election']['id']);
        redirect('dashboard-mp/elections/' . $slug);
      }
    }

  }
?>
