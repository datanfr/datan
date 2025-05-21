<?php

class Iframe extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();


    $this->load->library('depute_service');
    $this->load->library('election_service');
    $this->load->model('deputes_model');
    $this->load->model('parrainages_model');
    $this->load->model('depute_edito');
    $this->load->model('questions_model');
  }

  public function index(): void
  {
    $this->load->view('iframe/index');
  }


  public function show_depute_iframe(string $name): void
  {

    $data['main_title_visibility'] = isset($_GET['main-title']) && $_GET['main-title'] === 'hide' ? 'hidden' : '';
    $data['iframe_title_visibility'] = isset($_GET['secondary-title']) && $_GET['secondary-title'] === 'hide' ? 'hidden' : '';
    $data['secondary_title_visibility'] =  $data['iframe_title_visibility'];
    $data['title_displayed'] = false;
    $data['has_comportement_subcategories'] = false;
    $data['first_person'] = isset($_GET['first-person']) && ($_GET['first-person'] === 'true');
    $first_person =  $data['first_person'];
    $data['views_to_load'] = [];
    $data['iframe'] = TRUE;

    $categories_param = $this->input->get('categories');
    $categories = $categories_param
      ? explode(',', $categories_param)
      : ['positions-importantes', 'derniers-votes', 'election', 'explication', 'questions', 'comportement-politique'];

    $category_views = [
      'positions-importantes' => 'deputes/partials/mp_individual/_key_positions.php',
      'derniers-votes' => 'deputes/partials/mp_individual/_votes.php',
      'election' => 'deputes/partials/mp_individual/_election.php',
      'explication' => 'deputes/partials/mp_individual/_explanation.php',
      // 'questions' => 'deputes/partials/mp_individual/_questions.php', // A terminer
      'comportement-politique' => 'deputes/partials/mp_individual/statistics/_index.php',
    ];

    $subcategory_views = [
      'comportement-politique' => [
        'participation-votes' => 'deputes/partials/mp_individual/statistics/_voting_participation.php',
        'proximite-groupe' => 'deputes/partials/mp_individual/statistics/_intra_group_loyalty.php',
        'proximite-groupes' => 'deputes/partials/mp_individual/statistics/_inter_group_loyalty.php',
      ]
    ];

    $data['views_to_load'] = [];

    foreach ($categories as $category) {
      if (strpos($category, '.') === false) {
        if (isset($category_views[$category])) {
          $data['views_to_load'][] = $category_views[$category];
        }
      } else {
        list($main_category, $sub_category) = explode('.', $category);
        if (isset($subcategory_views[$main_category][$sub_category])) {
          $data['views_to_load'][] = $subcategory_views[$main_category][$sub_category];
          $data['has_comportement_subcategories'] = true;
        }
      }
    }

    $departement =  $this->deputes_model->get_dptslug_by_name_url($name);
    $data['depute'] = $this->deputes_model->get_depute_individual($name, $departement);



    setlocale(LC_TIME, 'french');

    // ____________________CHECK IF DEPUTE EXISTS__________________
    if (empty($data['depute'])) {
      show_404($this->functions_datan->get_404_infos());
    }

    // ____________________CHECK IF LEGISLATURE > 14_______________
    if (!$data['depute']['legislature'] >= 14) {
      show_404($this->functions_datan->get_404_infos());
    }

    // ____________________CACHING_________________________________
    if (!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')) {
      $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
    }

    // // ____________________MAIN VARIABLES___________________________
    $depute = $data['depute'];
    $mp_id = $depute['mpId'];
    $name_last = $depute['nameLast'];
    $data['active'] = $depute['active'];
    $legislature = $depute['legislature'];
    $depute_full_name = $depute['nameFirst'] . ' ' . $depute['nameLast'];
    $groupe_id = $depute['groupeId'];
    $data['gender'] = gender($depute['civ']);
    $data['mp_full_name'] = $depute_full_name;


    // ____________________GET GENERAL INFOS___________________________
    $data = $this->depute_service->get_general_infos($data, $mp_id, $legislature, $name_last, $depute_full_name);

    //__________________GET LAST EXPLICATION_______________________________
    if (in_array('explication', $categories)) {
      $data['explication'] = $this->depute_service->get_explication_details($mp_id, $legislature, $data['gender'], $first_person);
    }


    // ____________________GET MAJORITY GROUP___________________________
    // $data['groupMajority'] = $this->groupes_model->get_majority_group($legislature); 

    // ↳ besoin peut être pour le bloc statistiques(partial statistics/_inter_goup_loyalty) si "Proximité avec la majorité gouvernementale"


    //____________________GET STATISTICS__________________________________
    if (in_array('comportement-politique', $categories) || preg_grep('/^comportement-politique\./', $categories)) {
      $data = $this->depute_service->get_statistics($data, $legislature, $mp_id, $groupe_id);
    }

    //___________________GET VOTES_________________________________________
    if ($legislature >= 15) {
      // Get edited votes
      $data['votes_datan'] = $this->votes_model->get_votes_datan_depute($mp_id, 5);
      // Get key votes
      $data['key_votes'] = $this->votes_model->get_key_votes_mp($mp_id);
    } else {
      $data['votes_datan'] = NULL;
      $data['key_votes'] = NULL;
    }

    //___________________GET QUESTIONS______________________________________
    $data['questions'] = $this->questions_model->get_questions_by_mp($mp_id, 3);

    // ________________ GET Depute page ressources (meta, css, js...)_______

    $data = $this->depute_service->get_mp_page_resources($data, $depute_full_name, $name);


    // ________________ LOAD views_______

    $this->load->view('iframe/partials/_header_iframe', $data);
    $this->load->view('iframe/depute', $data);
    $this->load->view('iframe/partials/_footer_iframe', $data);
  }
}