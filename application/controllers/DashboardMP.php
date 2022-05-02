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
      $data['depute']['gender'] = gender($data['depute']['civ']);
      $data['candidate'] = $this->elections_model->get_candidate_election($data['depute']['mpId'], 4); /* Législative-2022 */
      if ($data['candidate']) {
        $data['candidate']['district'] = $this->elections_model->get_district('Législatives', $data['candidate']['district']);
      }

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function elections($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election']) || !in_array($data['election']['id'], array(4))) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['depute']['gender'] = gender($data['depute']['civ']);
      $data['candidate'] = $this->elections_model->get_candidate_election($data['depute']['mpId'], 4); /* Législative-2022 */
      if ($data['candidate']) {
        $data['candidate']['district'] = $this->elections_model->get_district($data['election']['libelleAbrev'], $data['candidate']['district']);
      }

      $data['title'] = "Candidature pour les élections " . mb_strtolower($data['election']['libelleAbrev']). " ".$data['election']['dateYear'];

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/elections/index', $data);
      $this->load->view('dashboard/footer');

    }

    public function elections_modify($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election']) || !in_array($data['election']['id'], array(4))) {
        show_404($this->functions_datan->get_404_infos());
      }

      $firstRound = new DateTime($data['election']['dateFirstRound']);
      $today = new DateTime('now');
      $interval = $firstRound->diff($today);

      if ($interval->days <= 2) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['candidate'] = $this->elections_model->get_candidate_election($data['depute']['mpId'], 4); /* Législative-2022 */
      if ($data['candidate']) {
        $data['candidate']['district'] = $this->elections_model->get_district($data['election']['libelleAbrev'], $data['candidate']['district']);
      }
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
        $this->table_history_model->insert($data['candidate']['candidature'], $this->input->post('candidature'), 'elect_deputes_candidats', 'candidature', $this->session->userdata('user_id'));
        $this->table_history_model->insert($data['candidate']['district']['id'], $this->input->post('district'), 'elect_deputes_candidats', 'district', $this->session->userdata('user_id'));
        redirect('dashboard-mp/elections/' . $slug);
      }
    }

  }
?>
