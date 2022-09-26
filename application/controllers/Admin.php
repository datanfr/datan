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
      $this->password_model->security_only_team();
    }

    // Admin homepage
    public function index(){
      $data['username'] = $this->session->userdata('username');
      $user_id = $this->session->userdata('user_id');

      // IF A TEAM MEMBER
      if ($this->password_model->is_team()) {
        $data['votesUnpublished'] = $this->admin_model->get_votes_datan_user($user_id, false);
        $data['votesLast'] = $this->admin_model->get_votes_datan_user($user_id, true);
        $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
        $data['deputes_entrants'] = $this->deputes_model->get_deputes_entrants(5);
        $data['groupes_entrants'] = $this->deputes_model->get_groupes_entrants(5);
        $data['newsletter_total'] = $this->newsletter_model->get_number_registered("general");
        $data['newsletter_month'] = $this->newsletter_model->get_registered_month("general");
        $data['votes_requested'] = $this->votes_model->get_requested_votes();

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('dashboard/footer');

      } else {
        // IF AN MP
        $data['depute'] = $this->deputes_model->get_depute_by_mpId($this->session->userdata('mpId'));

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/index_mp', $data);
        $this->load->view('dashboard/footer');
      }
    }

    public function election_candidates($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Liste candidats aux élections ' . $data['election']['libelleAbrev']. ' '.$data['election']['dateYear'];

      $data['candidats'] = $this->elections_model->get_all_candidates($data['election']['id']);
      foreach ($data['candidats'] as $key => $value) {
        $district = $this->elections_model->get_district($value['election_libelleAbrev'], $value['district']);
        $data['candidats'][$key]['districtLibelle'] = $district['libelle'];
      }

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function election_candidates_not_done($slug){
      $data['election'] = $this->elections_model->get_election($slug);

      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Liste députés non renseignés pour les élections ' . $data['election']['libelleAbrev']. ' '.$data['election']['dateYear'];

      $data['mps'] = $this->elections_model->get_mps_not_done($data['election']['id']);
      foreach ($data['mps'] as $key => $value) {
        $data['mps'][$key]['url'] = base_url() . 'deputes/' . $value['dptSlug'] . '/depute_' . $value['nameUrl'];
      }

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/list_not_candidates', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_candidat(){
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
      }

      $data['username'] = $this->session->userdata('username');
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
      if (!isset($_GET['election'])) {
        redirect('admin');
      }
      $slug = $_GET['election'];
      $data['election'] = $this->elections_model->get_election($slug);
      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');

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
      if (!isset($_GET['election'])) {
        redirect('admin');
      }
      $slug = $_GET['election'];
      $data['election'] = $this->elections_model->get_election($slug);
      if (empty($data['election'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata("type");

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
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Modifications apportées par les députés';

      $data['modifs'] = $this->table_history_model->get_history('elect_deputes_candidats');

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/elections/modifs', $data);
      $this->load->view('dashboard/footer');
    }

    public function votes(){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Tous les votes datan (décryptés)';

      $data['votes'] = $this->admin_model->get_votes_datan();
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/decrypted/votes_datan', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_vote(){
      $data['username'] = $this->session->userdata('username');
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer un nouveau vote décrypté';
      $data['categories'] = $this->fields_model->get_fields();
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
      $data['readings'] = $this->readings_model->get();

      //Form valiation
      $this->form_validation->set_rules('vote_id', 'Vote_id', 'required');
      $this->form_validation->set_rules('legislature', 'Legislature', 'required');
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('category', 'Category', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/decrypted/vote_create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->admin_model->create_vote($user_id);
        redirect('admin/votes');
      }
    }

    public function modify_vote($vote){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata("type");
      $user_id = $this->session->userdata('user_id');
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
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
      $data['usernameType'] = $this->session->userdata("type");

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['username'] = $this->session->userdata('username');
        $data['title'] = 'Supprimer un vote décrypté';
        $data['vote'] = $this->admin_model->get_vote_datan($vote);
        $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/decrypted/vote_delete', $data);
          $this->load->view('dashboard/footer');
        } else {
          $this->admin_model->delete_vote($vote);
          redirect('admin/votes');
        }
      }

    }

    public function votes_an_position(){
      $data['username'] = $this->session->userdata('username');
      $data['title'] = 'Tous les votes_an (position)';

      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
      $data['votes'] = $this->admin_model->get_votes_an_position();
      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/votes_an', $data);
      $this->load->view('dashboard/footer');
    }

    public function votes_an_cohesion(){
      $data['username'] = $this->session->userdata('username');
      $data['title'] = 'Tous les votes_an (cohesion)';
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
      $data['votes'] = $this->admin_model->get_votes_an_cohesion();

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/votes_an', $data);
      $this->load->view('dashboard/footer');
    }

    public function analyses($url){
      $data['username'] = $this->session->userdata('username');
      if ($url == "class_loyaute_group") {
        if (!isset($_GET["group"])) {
          echo "Indiquez le nom du groupe (lrem, fi, etc.) après ?group=lrem dans l'URL ";
        } else {
          $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);
          $libelle = $_GET["group"];
          $data['title'] = 'Classement loyauté pour le groupe '.$libelle;

          $data['table'] = $this->admin_model->get_classement_loyaute_group($libelle);

          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/analysis/table_all', $data);
          $this->load->view('dashboard/footer');
      }
      } else {
        show_404($this->functions_datan->get_404_infos());
      }
    }

    public function votes_an_em_lost(){
      $data['username'] = $this->session->userdata('username');
      $data['title'] = 'Votes où En Marche (LAREM) a perdu';
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);

      $data['votes'] = $this->admin_model->get_votes_em_lost();

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/analysis/table', $data);
      $this->load->view('dashboard/footer');
    }

    public function socialmedia($page, $id){
      $data['username'] = $this->session->userdata('username');
      $data['groupes'] = $this->groupes_model->get_groupes_all(true, 15);

      if ($page == "deputes_entrants") {
        $data['title'] = 'Liste des députés entrants (datePriseFonction)';
        $data['deputes'] = $this->deputes_model->get_deputes_entrants();

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "deputes_sortants") {
        $data['title'] = 'Liste des députés sortants (dateFin)';
        $data['deputes'] = $this->deputes_model->get_deputes_sortants();

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "postes_assemblee") {
        $data['title'] = 'Nouveaux postes Assemblée (dateDebut)';
        $data['deputes'] = $this->deputes_model->get_postes_assemblee();

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "groupes_entrants") {
        $data['title'] = 'Groupes entrants (dateDebut)';
        $data['deputes'] = $this->deputes_model->get_groupes_entrants();
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/table', $data);
        $this->load->view('dashboard/footer');
      } elseif ($page == "historique") {
        if ($id == "NULL") {
          $data['deputes'] = $this->deputes_model->get_deputes_all(legislature_current(), NULL, NULL);
          $data['title'] = "Historique des groupes par député - ".legislature_current()."e législature";
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/socialmedia/list', $data);
          $this->load->view('dashboard/footer');
        } else {
          $data['depute'] = $this->deputes_model->get_infos($id);
          $data['title'] = "Historique pour le député ".$data['depute']['nameFirst']." ".$data['depute']['nameLast'];
          $data['historique'] = $this->deputes_model->get_historique($id);
          $data['deputes'] = $data['historique'];
          $this->load->view('dashboard/header', $data);
          $this->load->view('dashboard/socialmedia/table', $data);
          $this->load->view('dashboard/footer');
        }
      } elseif ($page == "twitter") {
        $data['deputes'] = $this->deputes_model->get_twitter_accounts(legislature_current());
        $data['title'] = "Comptes Twitter des députés";
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/socialmedia/twitter', $data);
        $this->load->view('dashboard/footer');
      } else {
        show_404($this->functions_datan->get_404_infos());
      }
    }

    public function faq_list(){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Liste des articles FAQ';

      $data['articles'] = $this->faq_model->get_articles();

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/faq/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function delete_faq($id){
      $data['usernameType'] = $this->session->userdata("type");

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['username'] = $this->session->userdata('username');

        $data['title'] = 'Supprimer un article de FAQ';
        $data['article'] = $this->faq_model->get_article($id);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
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
      $data['username'] = $this->session->userdata('username');
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer un article de FAQ';
      $data['categories'] = $this->faq_model->get_categories();

      //Form valiation
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('article', 'Article', 'required');
      $this->form_validation->set_rules('category', 'Category', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/faq/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->faq_model->create($user_id);
        redirect('admin/faq');
      }
    }

    public function modify_faq($id){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
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
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Liste des questions pour les quizz';

      $data['questions'] = $this->quizz_model->get_questions();

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/quizz/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function create_quizz(){
      $data['username'] = $this->session->userdata('username');
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Créer une question de quizz';
      $data['categories'] = $this->fields_model->get_fields();

      //Form valiation
      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('quizzNumero', 'QuizzNumero', 'required');
      $this->form_validation->set_rules('voteNumero', 'VoteNumero', 'required');
      $this->form_validation->set_rules('legislature', 'Legislature', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/quizz/create', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->quizz_model->create($user_id);
        redirect('admin/quizz');
      }
    }

    public function modify_quizz($id){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
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
      $data['usernameType'] = $this->session->userdata("type");

      if ($data['usernameType'] != "admin") {
        redirect();
      } else {
        $data['username'] = $this->session->userdata('username');

        $data['title'] = 'Supprimer une question de quizz';
        $data['question'] = $this->quizz_model->get_question($id);

        //Form validation
        $this->form_validation->set_rules('delete', 'Delete', 'required');

        if ($this->form_validation->run() === FALSE) {
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
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $data['title'] = 'Liste des parrainages de députés en 2022';

      $data['parrainages'] = $this->parrainages_model->get_parrainages(2022, TRUE);
      //print_r($data['parrainages']);

      $this->load->view('dashboard/header', $data);
      $this->load->view('dashboard/parrainages/list', $data);
      $this->load->view('dashboard/footer');
    }

    public function modify_parrainage($id){
      $data['username'] = $this->session->userdata('username');
      $data['usernameType'] = $this->session->userdata('type');
      $user_id = $this->session->userdata('user_id');
      $data['title'] = 'Modifier un parrainage';
      $data['parrainage'] = $this->parrainages_model->get_parrainage($id);

      if (empty($data['parrainage'])) {
        redirect('admin/parrainages');
      }

      //Form valiation
      $this->form_validation->set_rules('mpId', 'MpId', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/parrainages/modify', $data);
        $this->load->view('dashboard/footer');
      } else {
        $this->parrainages_model->modify($id, $user_id);
        redirect('admin/parrainages');
      }
    }
  }
?>
