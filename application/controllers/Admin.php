<?php
  class Admin extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('admin_model');
      $this->load->model('deputes_model');
      $this->load->model('post_model');
      $this->load->model('fields_model');
      $this->load->model('groupes_model');
      $this->load->model('elections_model');
      $this->load->model('newsletter_model');
      $this->load->model('faq_model');
      $this->load->model('quizz_model');
      $this->load->model('readings_model');
      $this->load->model('votes_model');
      $this->load->model('parrainages_model');
      $this->load->model('exposes_model');
      $this->load->model('DashboardMP_model');
      $this->load->model('campaign_model');
      $this->password_model->security_only_team();

      $this->data = array(
        'type' => 'team',
        'username' => $this->session->userdata('username'),
        'usernameType' => $this->session->userdata('type'),
        'groupes' => $this->groupes_model->get_groupes_all(true, legislature_current())
      );
    }

    // Admin homepage
    public function index(){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');

      $data['votesUnpublished'] = $this->admin_model->get_votes_datan_user($user_id, false);
      $data['votesLast'] = $this->admin_model->get_votes_datan_user($user_id, true);
      $data['deputes_entrants'] = $this->deputes_model->get_deputes_entrants(5);
      $data['groupes_entrants'] = $this->deputes_model->get_groupes_entrants(5);
      $data['newsletter_total'] = $this->newsletter_model->get_number_registered("general");
      $data['newsletter_month'] = $this->newsletter_model->get_registered_month("general");
      $data['votes_requested'] = $this->votes_model->get_requested_votes();
      $data['explications'] = $this->DashboardMP_model->get_last_explanations();

      // Meta
      $data['title_meta'] = 'Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/index', $data);
      $this->load->view('dashboard/footer');
    }

    public function election_candidates($slug){
      $data = $this->data;
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['title'] = 'Liste candidats aux élections ' . $data['election']['libelleAbrev']. ' '.$data['election']['dateYear'];

      $data['candidats'] = $this->elections_model->get_all_candidates($data['election']['id']);
      foreach ($data['candidats'] as $key => $value) {
        $district = $this->elections_model->get_district($value['election_libelleAbrev'], $value['district']);
        $data['candidats'][$key]['districtLibelle'] = $district['libelle'];
      }

      // Meta
      $data['title_meta'] = 'Liste des candidats aux élections - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function election_candidates_not_done($slug){
      $data = $this->data;
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['title'] = 'Liste des députés non renseignés pour les élections ' . $data['election']['libelleAbrev']. ' '.$data['election']['dateYear'];

      $data['mps'] = $this->elections_model->get_mps_not_done($data['election']['id']);
      foreach ($data['mps'] as $key => $value) {
        $data['mps'][$key]['url'] = base_url() . 'deputes/' . $value['dptSlug'] . '/depute_' . $value['nameUrl'];
      }

      // Meta
      $data['title_meta'] = 'Liste des députés non renseignés - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/list_not_candidates', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_candidat(){
      $data = $this->data;

      if (!isset($_GET['election'])) {
        redirect('admin');
      }
      $slug = $_GET['election'];
      if (isset($_GET['mp'])) {
        $data['mp'] = $_GET['mp'];
      }
      $data['election'] = $this->elections_model->get_election($slug);
      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      if ($data['election']['libelleAbrev'] == 'Présidentielle') {
        $data['requiredFields'] = array();
      } elseif ($data['election']['libelleAbrev'] == 'Législatives') {
        $data['requiredFields'] = array('district');
      } elseif ($data['election']['libelleAbrev'] == 'Régionales') {
        $data['requiredFields'] = array('district', 'position');
      } elseif ($data['election']['libelleAbrev'] == 'Européennes') {
        $data['requiredFields'] = array('');
      }

      $user_id = $this->session->userdata('user_id');

      $data['title'] = 'Créer un nouveau candidat pour les ' . $data['election']['libelleAbrev'] . ' ' . $data['election']['dateYear'];
      $data['positions'] = array('', 'Tête de liste', 'Colistier');
      $data['districts'] = $this->elections_model->get_all_districts($data['election']['id']);

      //Form valiation
      $this->form_validation->set_rules('depute_url', 'député', 'required');
      if ($data['election']['libelleAbrev'] == 'Régionales') {
        $this->form_validation->set_rules('district', 'région de candidature', 'required');
      }
      if ($data['election']['libelleAbrev'] == 'Législatives') {
        $this->form_validation->set_rules('district', 'circonscription', 'required');
      }
      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Créer un nouveau député candidat - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/elections/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $path = parse_url($this->input->post('depute_url'), PHP_URL_PATH);
        $nameUrl = substr(explode('/', $path)[3], 7);
        $depute = $this->deputes_model->get_depute_by_nameUrl($nameUrl);
        if ($depute) {
          $this->admin_model->create_candidat($user_id, $depute);
          $election = $this->elections_model->get_election_by_id($this->input->post('election'));
          redirect('admin/elections/' . $election['slug']);
        } else {
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/elections/create', $data);
          $this->load->view('dashboard/footer');
        }
      }

    }

    public function modify_candidat($candidateMpId){
      $data = $this->data;

      if (!isset($_GET['election'])) {
        redirect('admin');
      }
      $slug = $_GET['election'];
      $data['election'] = $this->elections_model->get_election($slug);
      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['title'] = 'Modifier un candidat pour les ' . $data['election']['libelleAbrev'] . ' ' . $data['election']['dateYear'];
      $data['candidat'] = $this->elections_model->get_candidate_full($candidateMpId, $data['election']['id']);
      $district = $this->elections_model->get_district($data['election']['libelleAbrev'], $data['candidat']['district']);
      $data['candidat']['districtId'] = $district['id'];
      $data['candidat']['districtLibelle'] = $district['libelle'];

      if (empty($data['candidat'])) {
        redirect('admin/elections/' . $data['election']['slug']);
      }

      if ($data['election']['libelleAbrev'] == 'Présidentielle') {
        $data['requiredFields'] = array();
      } elseif ($data['election']['libelleAbrev'] == 'Législatives') {
        $data['requiredFields'] = array('district');
      } elseif ($data['election']['libelleAbrev'] == 'Régionales') {
        $data['requiredFields'] = array('district', 'position');
      }

      $data['positions'] = array('Tête de liste', 'Colistier');
      $data['districts'] = $this->elections_model->get_all_districts($data['election']['id']);
      //Form valiation
      $this->form_validation->set_rules('mpId', 'mpId', 'required');
      if ($data['election']['libelleAbrev'] == 'Régionales') {
        $this->form_validation->set_rules('district', 'région de candidature', 'required');
      }
      if ($data['election']['libelleAbrev'] == 'Législatives') {
        //$this->form_validation->set_rules('district', 'circonscription', 'required');
      }
      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Modifier un député candidat - Dashboard | Admin';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/elections/modify', $data);
        $this->load->view('dashboard/footer');
      } else {
          $this->admin_model->modify_candidat();
          $election = $this->elections_model->get_election_by_id($this->input->post('election'));
          redirect('admin/elections/' . $election['slug']);
      }

    }

    public function delete_candidat($candidateMpId){
      $data = $this->data;

      if (!isset($_GET['election'])) {
        redirect('admin');
      }
      $slug = $_GET['election'];
      $data['election'] = $this->elections_model->get_election($slug);
      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      if ($data['election']['libelleAbrev'] == 'Présidentielle') {
        $data['requiredFields'] = array();
      } elseif ($data['election']['libelleAbrev'] == 'Législatives') {
        $data['requiredFields'] = array('district');
      } elseif ($data['election']['libelleAbrev'] == 'Régionales') {
        $data['requiredFields'] = array('district', 'position');
      }

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['candidat'] = $this->elections_model->get_candidate_full($candidateMpId, $data['election']['id']);

        $data['title'] = 'Supprimer un candidat pour les ' . $data['election']['libelleAbrev'] . ' ' . $data['election']['dateYear'];
        //Form valiation
        $this->form_validation->set_rules('mpId', 'mpId', 'required');
        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = 'Supprimer un député candidat - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/elections/delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->admin_model->delete_candidat();
          $election = $this->elections_model->get_election_by_id($this->input->post('election'));
          redirect('admin/elections/' . $election['slug']);
        }
      }
    }

    public function election_modifications_mps(){
      $data = $this->data;
      $data['title'] = 'Modifications apportées par les députés';

      $data['modifs'] = $this->table_history_model->get_history('elect_deputes_candidats');

      // Meta
      $data['title_meta'] = 'Modifications apportées par les députés - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/modifs', $data);
      $this->load->view('dashboard/footer');
    }

    public function votes(){
      $data = $this->data;
      $data['title'] = 'Tous les votes datan (décryptés)';

      $data['votes'] = $this->admin_model->get_votes_datan();

      // Meta
      $data['title_meta'] = 'Tous les votes décryptés - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/decrypted/votes_datan', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_vote(){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer un nouveau vote décrypté';
      $data['categories'] = $this->fields_model->get_fields();
      $data['readings'] = $this->readings_model->get();

      //Form valiation
      $this->form_validation->set_rules('vote_id', 'Vote_id', 'required');
      $this->form_validation->set_rules('legislature', 'Legislature', 'required');
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('category', 'Category', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = 'Créer un vote décrypté - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/decrypted/vote_create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->admin_model->create_vote($user_id);
        redirect('admin/votes');
      }
    }

    public function modify_vote($vote){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['readings'] = $this->readings_model->get();

      $data['title'] = 'Modifier un vote décrypté';
      $data['vote'] = $this->admin_model->get_vote_datan($vote);
      if (empty($data['vote'])) {
        redirect('admin/votes');
      }

      if ($data['vote']['state'] == "published" && $data['usernameType'] != "admin") {
        redirect('admin/votes');
      } else {
        $data['categories'] = $this->fields_model->get_fields();

        //Form valiation
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('category', 'Category', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = 'Modifier un vote décrypté - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/decrypted/vote_modify', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->admin_model->modify_vote($vote,$user_id);
          redirect('admin/votes');
        }
      }
    }

    public function delete_vote($vote){
      $data = $this->data;

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['title'] = 'Supprimer un vote décrypté';
        $data['vote'] = $this->admin_model->get_vote_datan($vote);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = 'Supprimer un vote décrypté - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/decrypted/vote_delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->admin_model->delete_vote($vote);
          redirect('admin/votes');
        }
      }

    }
    
    public function socialmedia($page, $id){
      $data = $this->data;

      if ($page == "deputes_entrants") {
        $data['title'] = 'Liste des députés entrants (datePriseFonction)';
        $data['deputes'] = $this->deputes_model->get_deputes_entrants();

        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "deputes_sortants") {
        $data['title'] = 'Liste des députés sortants (dateFin)';
        $data['deputes'] = $this->deputes_model->get_deputes_sortants();

        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "postes_assemblee") {
        $data['title'] = 'Nouveaux postes Assemblée (dateDebut)';
        $data['deputes'] = $this->deputes_model->get_postes_assemblee();

        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "groupes_entrants") {
        $data['title'] = 'Groupes entrants (dateDebut)';
        $data['deputes'] = $this->deputes_model->get_groupes_entrants();

        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "historique") {
        if ($id == "NULL") {
          $data['deputes'] = $this->deputes_model->get_deputes_all(legislature_current(), NULL, NULL);
          $data['title'] = "Historique des groupes par député - ".legislature_current()."e législature";

          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/socialmedia/list', $data);
          $this->load->view('dashboard/footer');
        } else {
          $data['depute'] = $this->deputes_model->get_infos($id);
          $data['title'] = "Historique pour le député ".$data['depute']['nameFirst']." ".$data['depute']['nameLast'] . " (" . $data['depute']['mpId'] . ")";
          $data['historique'] = $this->deputes_model->get_historique($id);
          $data['deputes'] = $data['historique'];

          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/socialmedia/table', $data);
          $this->load->view('dashboard/footer');
        }
      } elseif ($page == "x") {
        $data['deputes'] = $this->deputes_model->get_twitter_accounts(legislature_current());
        $data['title'] = "Comptes X des députés";

        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/twitter', $data);
        $this->load->view('dashboard/footer');
      } else {
        show_404($this->functions_datan->get_404_infos());
      }
    }

    public function faq_list(){
      $data = $this->data;
      $data['title'] = 'Liste des articles FAQ';
      $data['articles'] = $this->faq_model->get_articles();

      // Meta
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/faq/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function delete_faq($id){
      $data = $this->data;

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['title'] = 'Supprimer un article de FAQ';
        $data['article'] = $this->faq_model->get_article($id);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/faq/delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->faq_model->delete($id);
          redirect('admin/faq');
        }
      }
    }

    public function create_faq(){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer un article de FAQ';
      $data['categories'] = $this->faq_model->get_categories();

      //Form valiation
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('article', 'Article', 'required');
      $this->form_validation->set_rules('category', 'Category', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/faq/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->faq_model->create($user_id);
        redirect('admin/faq');
      }
    }

    public function modify_faq($id){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier un article de FAQ';
      $data['article'] = $this->faq_model->get_article($id);

      if (empty($data['article'])) {
        redirect('admin/faq');
      }

      if ($data['article']['state'] == "published" && $data['usernameType'] != "admin") {
        redirect('admin/faq');
      } else {
        $data['categories'] = $this->faq_model->get_categories();

        //Form valiation
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('article', 'Article', 'required');
        $this->form_validation->set_rules('category', 'Category', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/faq/modify', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->faq_model->modify($id, $user_id);
          redirect('admin/faq');
        }
      }
    }

    public function quizz_list(){
      $data = $this->data;
      $data['title'] = 'Liste des questions pour les quizz';
      $data['questions'] = $this->quizz_model->get_questions();

      // Meta
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/quizz/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_quizz(){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer une question de quizz';
      $data['categories'] = $this->fields_model->get_fields();

      //Form valiation
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('quizzNumero', 'QuizzNumero', 'required');
      $this->form_validation->set_rules('voteNumero', 'VoteNumero', 'required');
      $this->form_validation->set_rules('legislature', 'Legislature', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/quizz/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->quizz_model->create($user_id);
        redirect('admin/quizz');
      }
    }

    public function modify_quizz($id){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier une question de quizz';
      $data['question'] = $this->quizz_model->get_question($id);

      if (empty($data['question'])) {
        redirect('admin/quizz');
      }

      if ($data['question']['state'] == "published" && $data['usernameType'] != "admin") {
        redirect('admin/quizz');
      } else {
        $data['categories'] = $this->fields_model->get_fields();

        //Form valiation
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('quizzNumero', 'QuizzNumero', 'required');
        $this->form_validation->set_rules('voteNumero', 'VoteNumero', 'required');
        $this->form_validation->set_rules('legislature', 'Legislature', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/quizz/modify', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->quizz_model->modify($id, $user_id);
          redirect('admin/quizz');
        }
      }
    }

    public function delete_quizz($id){
      $data = $this->data;

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['title'] = 'Supprimer une question de quizz';
        $data['question'] = $this->quizz_model->get_question($id);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/quizz/delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->quizz_model->delete($id);
          redirect('admin/quizz');
        }
      }
    }

    public function parrainages(){
      $data = $this->data;
      $data['title'] = 'Liste des parrainages de députés en 2022';
      $data['parrainages'] = $this->parrainages_model->get_parrainages(2022, TRUE);

      // Meta
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/parrainages/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function modify_parrainage($id){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier un parrainage';
      $data['parrainage'] = $this->parrainages_model->get_parrainage($id);

      if (empty($data['parrainage'])) {
        redirect('admin/parrainages');
      }

      //Form valiation
      $this->form_validation->set_rules('mpId', 'MpId', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/parrainages/modify', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->parrainages_model->modify($id, $user_id);
        redirect('admin/parrainages');
      }
    }

    public function exposes(){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Liste des exposés des motifs';
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
      $data['exposes'] = $this->exposes_model->get_all_exposes();
      $data['exposes_n_done'] = $this->exposes_model->get_n_done();
      $data['exposes_n_pending'] = $this->exposes_model->get_n_pending();


      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/exposes/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function exposes_modify($id){
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier un exposé des motifs';
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
      $data['expose'] = $this->exposes_model->get_expose($id);

      if (empty($data['expose'])) {
        redirect('admin/parrainages');
      }

      //Form valiation
      $this->form_validation->set_rules('exposeSummary', 'exposeSummary', 'required');

      // JS TO LOAD
      $data['js_to_load'] = array('dashboard/countChar');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/exposes/modify', $data);
        $this->load->view('dashboard/footer', $data);
      } else {
        $this->exposes_model->modify($data['expose']['legislature'], $data['expose']['voteNumero']);
        redirect('admin/exposes');
      }

    }


    // DONATIONS CAMPAIGNS
    
    public function campaigns_list() 
    {
      $data = $this->data;
      $data['title'] = 'Liste des campagnes de dons';
      $data['campaigns'] = $this->campaign_model->get_campaigns();

      // Meta
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      $data['js_to_load'] = array('dashboard/donation-campaigns');

      // Views
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/donation-campaigns/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_campaign()
    {
        $data = $this->data;
        $user_id = $this->session->userdata('user_id');
        $data['title'] = 'Créer une campagne';

        $data['js_to_load'] = array('dashboard/donation-campaigns');

        $this->form_validation->set_rules('startDate', 'Date de début', 'required');
        $this->form_validation->set_rules('endDate', 'Date de fin', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
            $this->load->view('dashboard/header', $data);
            $this->load->view('dashboard/donation-campaigns/create', $data);
            $this->load->view('dashboard/footer');
        } else {
            $this->campaign_model->create($user_id);
            redirect('admin/campagnes');
        }
    }

    public function edit_campaign(int $id)
    {
      $data = $this->data;
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier une campagne';
      $data['campaign'] = $this->campaign_model->get_campaign($id);

      $data['js_to_load'] = array('dashboard/donation-campaigns');

      if (empty($data['campaign'])) {
        redirect('admin/campagnes');
      }

      $this->form_validation->set_rules('startDate', 'Date de début', 'required');
      $this->form_validation->set_rules('endDate', 'Date de fin', 'required');
      $this->form_validation->set_rules('message', 'Message', 'required');

      if ($this->form_validation->run() === FALSE) {
        // Meta
        $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
        // Views
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/donation-campaigns/edit', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->campaign_model->update($id);
        redirect('admin/campagnes');
      }
    }

    public function delete_campaign(int $id)
    {
      $data = $this->data;
      
      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['title'] = 'Supprimer une campagne';
        $data['campaign'] = $this->campaign_model->get_campaign($id);

        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
          // Meta
          $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';
          // Views
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/donation-campaigns/delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->campaign_model->delete($id);
          redirect('admin/campagnes');
        }
      }
    }

    public function toggle_campaign_active()
    {
      $id = $this->input->post('id');
      $is_active = $this->input->post('is_active') ? 1 : 0;

      if (!$id) {
          show_error('ID manquant', 400);
      }

      $this->campaign_model->set_active_status($id, $is_active);
      redirect('admin/campagnes'); 
    } 
  }  
?>
