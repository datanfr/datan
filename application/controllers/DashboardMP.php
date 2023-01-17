<?php
  class DashboardMP extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('admin_model');
      $this->load->model('deputes_model');
      $this->load->model('elections_model');
      $this->load->model('dashboardMP_model');
      $this->load->model('votes_model');
      $this->password_model->security_only_mp();
    }

    public function index(){
      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['depute']['gender'] = gender($data['depute']['civ']);

      // Legislatives 2022
      $data['election'] = $this->elections_model->get_election_by_id(4);
      $data['candidate'] = $this->elections_model->get_candidate_election($data['depute']['mpId'], 4); /* Législative-2022 */
      if ($data['candidate']) {
        $data['candidate']['district'] = $this->elections_model->get_district('Législatives', $data['candidate']['district']);

        $firstRound = new DateTime($data['election']['dateFirstRound']);
        $today = new DateTime('now');
        $interval = $firstRound->diff($today);
        $data['candidate']['modify'] = $interval->days >= 2 ? 0 : 1;
      }

      // Explications
      $data['votes_explained'] = $this->dashboardMP_model->get_votes_explained($data['depute']['mpId'], FALSE);

      // Meta
      $data['title_meta'] = 'Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => TRUE)
      );

      // Views
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

        $firstRound = new DateTime($data['election']['dateFirstRound']);
        $today = new DateTime('now');
        $interval = $firstRound->diff($today);
        $data['candidate']['modify'] = $interval->days >= 2 ? 0 : 1;
      }

      $data['title'] = "Candidature pour les élections " . mb_strtolower($data['election']['libelleAbrev']). " ".$data['election']['dateYear'];

      // Meta
      $data['title_meta'] = 'Élections - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
        array('name' => 'Élections ' . mb_strtolower($data['election']['libelleAbrev']) . ' ' . $data['election']['dateYear'], 'url' => base_url().'dashboard-mp/elections/' . $slug, 'active' => TRUE),
      );

      // Views
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

      if ($interval->days >= 2) {
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
        // Meta
        $data['title_meta'] = 'Modifier une élection - Dashboard | Datan';
        $data['breadcrumb'] = array(
          array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
          array('name' => 'Élections ' . mb_strtolower($data['election']['libelleAbrev']) . ' ' . $data['election']['dateYear'], 'url' => base_url().'dashboard-mp/elections/' . $slug, 'active' => FALSE),
          array('name' => 'Modifier', 'url' => base_url().'dashboard-mp/elections/'. $slug . '/modifier', 'active' => TRUE),
        );

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/elections/modify', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->admin_model->modify_candidat_as_mp($data['depute']['mpId'], $data['election']['id']);
        $this->table_history_model->insert($data['candidate']['candidature'], $this->input->post('candidature'), 'elect_deputes_candidats', 'candidature', $this->session->userdata('user_id'));
        $this->table_history_model->insert($data['candidate']['district']['id'], $this->input->post('district'), 'elect_deputes_candidats', 'district', $this->session->userdata('user_id'));
        $this->table_history_model->insert($data['candidate']['link'], $this->input->post('link'), 'elect_deputes_candidats', 'link', $this->session->userdata('user_id'));
        delete_all_cache();
        $this->db->cache_delete_all();
        redirect('dashboard-mp/elections/' . $slug);
      }
    }

    public function explications(){
      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['depute']['gender'] = gender($data['depute']['civ']);
      $data['votes_published'] = $this->dashboardMP_model->get_votes_explained($data['depute']['mpId'], TRUE);
      $data['votes_draft'] = $this->dashboardMP_model->get_votes_explained($data['depute']['mpId'], FALSE);

      $data['votes_without'] = $this->dashboardMP_model->get_votes_to_explain($data['depute']['mpId']);
      $data['votes_without_suggestion'] = $this->dashboardMP_model->get_votes_to_explain_suggestion($data['votes_without']);

      $data['title'] = "Vos explications de vote";

      // Meta
      $data['title_meta'] = 'Explications de vote - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
        array('name' => 'Explications de vote', 'url' => base_url().'dashboard-mp/explications', 'active' => TRUE),
      );

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/explications/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function explications_liste(){
      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['depute']['gender'] = gender($data['depute']['civ']);

      $data['votes_without'] = $this->dashboardMP_model->get_votes_to_explain($data['depute']['mpId']);
      $data['votes_without_suggestion'] = $this->dashboardMP_model->get_votes_to_explain_suggestion($data['votes_without']);

      $data['title'] = 'Créez une explication de vote';

      // Meta
      $data['title_meta'] = 'Liste des votes à expliquer - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
        array('name' => 'Explications de vote', 'url' => base_url().'dashboard-mp/explications', 'active' => FALSE),
        array('name' => 'Liste', 'url' => base_url().'dashboard-mp/explications/liste', 'active' => TRUE),
      );

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/explications/liste', $data);
      $this->load->view('dashboard/footer');
    }

    public function explications_create($legislature, $voteNumero){

      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;
      $data['username'] = $this->session->userdata('username');
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['depute']['gender'] = gender($data['depute']['civ']);

      $data['vote_depute'] = $this->votes_model->get_individual_vote_depute($data['depute']['mpId'], $data['vote']['legislature'], $data['vote']['voteNumero']);

      if (empty($data['vote_depute'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas pris part au vote n° " . $data["vote"]["voteNumero"] . ". Merci de choisir un scrutin dans cette liste.");
        redirect('dashboard-mp/explications/liste');
      }

      // Check if already an explanation
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);
      if ($data['explication']) {
        $this->session->set_flashdata('flash_failure', "Vous avez déjà rédigé une explication pour ce vote n° " . $data["vote"]["voteNumero"] . ". Vous pouvez le modifier en <a href='".base_url()."dashboard-mp/explications/modify/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard-mp/explications/liste');
      }

      $data['vote_depute']['vote'] = vote_edited($data['vote_depute']['vote']);
      $data['vote_depute']['positionGroup'] = vote_edited($data['vote_depute']['positionGroup']);

      $data['title'] = "Rédigez une explication de vote";
      $data['page'] = 'create';

      // Form valiation
      $this->form_validation->set_rules('explication', 'Explication', 'required|max_length[500]');

      // JS TO LOAD
      $data['js_to_load'] = array('dashboard/countChar');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Rédigez une explication de vote - Dashboard | Datan';
        $data['breadcrumb'] = array(
          array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard-mp/explications', 'active' => FALSE),
          array('name' => 'Rédiger', 'url' => base_url().'dashboard-mp/explications/create/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
        );

        $data['explication']['text'] = $this->input->post('explication');

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/explications/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->dashboardMP_model->create_explication($data);
        delete_all_cache();
        $this->db->cache_delete_all();
        redirect('dashboard-mp/explications');
      }

    }

    public function explications_modify($legislature, $voteNumero){
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);

      if (empty($data['explication'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas encore rédigé une explication pour le vote n° " . $voteNumero . ". Vous pouvez en créer une en <a href='".base_url()."dashboard-mp/explications/create/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard-mp/explications/liste');
      }

      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['explication']) || empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;
      $data['username'] = $this->session->userdata('username');

      $data['vote_depute'] = $this->votes_model->get_individual_vote_depute($data['depute']['mpId'], $data['vote']['legislature'], $data['vote']['voteNumero']);
      $data['vote_depute']['vote'] = vote_edited($data['vote_depute']['vote']);
      $data['vote_depute']['positionGroup'] = vote_edited($data['vote_depute']['positionGroup']);

      $data['title'] = 'Modifiez une explication du vote';
      $data['page'] = 'modify';

      // Form valiation
      $this->form_validation->set_rules('explication', 'Explication', 'required|max_length[500]');

      // JS TO LOAD
      $data['js_to_load'] = array('dashboard/countChar');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Modifiez de vote - Dashboard | Datan';
        $data['breadcrumb'] = array(
          array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard-mp/explications', 'active' => FALSE),
          array('name' => 'Modifier', 'url' => base_url().'dashboard-mp/explications/modify/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
        );
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/explications/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->dashboardMP_model->modify_explication($data);
        delete_all_cache();
        $this->db->cache_delete_all();
        redirect('dashboard-mp/explications');
      }

    }

    public function explications_delete($legislature, $voteNumero){
      $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);

      if (empty($data['explication'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas encore rédigé une explication pour le vote n° " . $voteNumero . ". Vous pouvez en créer une en <a href='".base_url()."dashboard-mp/explications/create/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard-mp/explications/liste');
      }

      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['explication']) || empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;
      $data['username'] = $this->session->userdata('username');

      $data['vote_depute'] = $this->votes_model->get_individual_vote_depute($data['depute']['mpId'], $data['vote']['legislature'], $data['vote']['voteNumero']);
      $data['vote_depute']['vote'] = vote_edited($data['vote_depute']['vote']);
      $data['vote_depute']['positionGroup'] = vote_edited($data['vote_depute']['positionGroup']);

      $data['title'] = 'Supprimer une explication de vote';

      //Form validation
      $this->form_validation->set_rules('delete', 'Delete', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Supprimer une explication de vote - Dashboard | Datan';
        $data['breadcrumb'] = array(
          array('name' => 'Dashboard', 'url' => base_url().'dashboard-mp', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard-mp/explications', 'active' => FALSE),
          array('name' => 'Supprimer', 'url' => base_url().'dashboard-mp/explications/delete/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
        );
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/explications/delete', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->dashboardMP_model->delete_explanation($data);
        delete_all_cache();
        $this->db->cache_delete_all();
        $this->session->set_flashdata('flash', "L'explication de vote pour le scrutin n°" . $voteNumero . " a bien été supprimée.");
        redirect('dashboard-mp/explications');
      }

    }

  }
?>
