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
      $this->data = array(
        'type' => 'mp',
        'username' => $this->session->userdata('username'),
        'depute' => $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId')),
      );
      $this->data['depute']['gender'] = gender($this->data['depute']['civ']);
    }

    public function index(){
      $data = $this->data;
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
        array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => TRUE)
      );

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function elections($slug){
      $data = $this->data;
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election']) || !in_array($data['election']['id'], array(4))) {
        show_404($this->functions_datan->get_404_infos());
      }

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
        array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
        array('name' => 'Élections ' . mb_strtolower($data['election']['libelleAbrev']) . ' ' . $data['election']['dateYear'], 'url' => base_url().'dashboard/elections/' . $slug, 'active' => TRUE),
      );

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/elections/index', $data);
      $this->load->view('dashboard/footer');

    }

    public function elections_modify($slug){
      $data = $this->data;
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
          array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
          array('name' => 'Élections ' . mb_strtolower($data['election']['libelleAbrev']) . ' ' . $data['election']['dateYear'], 'url' => base_url().'dashboard/elections/' . $slug, 'active' => FALSE),
          array('name' => 'Modifier', 'url' => base_url().'dashboard/elections/'. $slug . '/modifier', 'active' => TRUE),
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
        redirect('dashboard/elections/' . $slug);
      }
    }

    public function explications(){
      $data = $this->data;
      $data['votes_published'] = $this->dashboardMP_model->get_votes_explained($data['depute']['mpId'], TRUE);
      $data['votes_draft'] = $this->dashboardMP_model->get_votes_explained($data['depute']['mpId'], FALSE);

      foreach ($data['votes_published'] as $key => $value) {
        $data['votes_published'][$key]['socialMediaUrl'] = base_url() . "votes/legislature-" . $value['legislature'] . "/vote_" . $value['voteNumero'] . "/explication_" . $data['depute']['mpId'];
      }

      $data['votes_without'] = $this->dashboardMP_model->get_votes_to_explain($data['depute']['mpId']);
      $data['votes_without_suggestion'] = $this->dashboardMP_model->get_votes_to_explain_suggestion($data['votes_without']);

      $data['title'] = "Mes explications de vote";

      // Meta
      $data['title_meta'] = 'Explications de vote - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
        array('name' => 'Explications de vote', 'url' => base_url().'dashboard/explications', 'active' => TRUE),
      );

      $data['js_to_load'] = array('datan/dashboard-mp-social-share');

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/explications/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function explications_liste(){
      $data = $this->data;

      $data['votes_without'] = $this->dashboardMP_model->get_votes_to_explain($data['depute']['mpId']);
      $data['votes_without_suggestion'] = $this->dashboardMP_model->get_votes_to_explain_suggestion($data['votes_without']);

      $data['title'] = 'Je crée une nouvelle explication de vote';

      // Meta
      $data['title_meta'] = 'Liste des votes à expliquer - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
        array('name' => 'Explications de vote', 'url' => base_url().'dashboard/explications', 'active' => FALSE),
        array('name' => 'Liste', 'url' => base_url().'dashboard/explications/liste', 'active' => TRUE),
      );

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/explications/liste', $data);
      $this->load->view('dashboard/footer');
    }

    public function explications_create($legislature, $voteNumero){
      $data = $this->data;
      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;

      $data['vote_depute'] = $this->votes_model->get_individual_vote_depute($data['depute']['mpId'], $data['vote']['legislature'], $data['vote']['voteNumero']);

      if (empty($data['vote_depute'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas pris part au vote n° " . $data["vote"]["voteNumero"] . ". Merci de choisir un scrutin dans cette liste.");
        redirect('dashboard/explications/liste');
      }

      // Check if already an explanation
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);
      if ($data['explication']) {
        $this->session->set_flashdata('flash_failure', "Vous avez déjà rédigé une explication pour ce vote n° " . $data["vote"]["voteNumero"] . ". Vous pouvez le modifier en <a href='".base_url()."dashboard/explications/modify/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard/explications/liste');
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
          array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard/explications', 'active' => FALSE),
          array('name' => 'Rédiger', 'url' => base_url().'dashboard/explications/create/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
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
        redirect('dashboard/explications');
      }

    }

    public function explications_modify($legislature, $voteNumero){
      $data = $this->data;
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);

      if (empty($data['explication'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas encore rédigé une explication pour le vote n° " . $voteNumero . ". Vous pouvez en créer une en <a href='".base_url()."dashboard/explications/create/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard/explications/liste');
      }

      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['explication']) || empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;

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
          array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard/explications', 'active' => FALSE),
          array('name' => 'Modifier', 'url' => base_url().'dashboard/explications/modify/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
        );
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard-mp/explications/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->dashboardMP_model->modify_explication($data);
        delete_all_cache();
        $this->db->cache_delete_all();
        redirect('dashboard/explications');
      }

    }

    public function explications_delete($legislature, $voteNumero){
      $data = $this->data;
      $data['explication'] = $this->votes_model->get_explication($data['depute']['mpId'], $legislature, $voteNumero);

      if (empty($data['explication'])) {
        $this->session->set_flashdata('flash_failure', "Vous n'avez pas encore rédigé une explication pour le vote n° " . $voteNumero . ". Vous pouvez en créer une en <a href='".base_url()."dashboard/explications/create/l".$legislature."v".$voteNumero."'>cliquant ici</a>.");
        redirect('dashboard/explications/liste');
      }

      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $voteNumero);

      if (empty($data['explication']) || empty($data['vote'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['legislature'] = $legislature;
      $data['voteNumero'] = $voteNumero;

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
          array('name' => 'Dashboard', 'url' => base_url().'dashboard', 'active' => FALSE),
          array('name' => 'Explications de vote', 'url' => base_url().'dashboard/explications', 'active' => FALSE),
          array('name' => 'Supprimer', 'url' => base_url().'dashboard/explications/delete/l' . $legislature . 'v' . $voteNumero, 'active' => TRUE),
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
        redirect('dashboard/explications');
      }

    }

    public function generate_iframe()
    {
      show_404(); // Temporary disallow iframe for MPs (until ready)
      $data = $this->data;
      $mp_id = $data['depute']['mpId'];
      $explanations = $this->dashboardMP_model->get_explanations_by_mp($mp_id);
      $has_published = false;
      foreach ($explanations as $exp) {
        if ($exp['state'] == 1) {
          $has_published = true;
          break;
        }
      }
      $data['explanations'] = $explanations;
      $data['has_published'] = $has_published;
      $data['name_url'] = $data['depute']['nameUrl'];
  
      // Meta
      $data['title_meta'] = 'Générateur d\'iframe - Dashboard | Datan';
      $data['breadcrumb'] = array(
        array('name' => 'Dashboard', 'url' => base_url() . 'dashboard', 'active' => FALSE),
        array('name' => 'Générer un iframe', 'url' => base_url() . 'dashboard/iframe', 'active' => TRUE),
      );
  
      $data['title'] = "Générer un iframe";

      $data['js_to_load'] = array('dashboard/iframe');
  
  
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard-mp/iframe/index', $data);
      $this->load->view('dashboard/footer', $data);
    }

  }
?>