<?php

class IframeController extends CI_Controller
{
    public function __construct() 
    {
        parent::__construct();


        $this->load->library('DeputeService');
        $this->load->library('ElectionService');
        $this->load->model('deputes_model');
        $this->load->model('parrainages_model');
        $this->load->model('depute_edito');

    }

    public function index()
    {
        $this->load->view('iframe/index'); 
    }

    private function get_statistiques($data, $legislature, $mpId, $groupe_id)
    {
  
      if (in_array($legislature, legislature_all())) {
        // PARTICIPATION
        $data['participation'] = $this->deputes_model->get_stats_participation_solennels($mpId, $legislature);
        if ($data['participation'] && $data['participation']['votesN'] >= 10) {
          $data['no_participation'] = FALSE;
          // GET ALL DATA FOR PARTICIPATION
          $data['participation']['all'] = $this->deputes_model->get_stats_participation_solennels_all($legislature);
          $data['participation']['group'] = $this->deputes_model->get_stats_participation_solennels_group($legislature, $groupe_id);
          if (isset($data['participation']['score'])){
            $data['edito_participation']['all'] = $this->depute_edito->participation($data['participation']['score'], $data['participation']['all']); //edited for ALL
            $data['edito_participation']['group'] = $this->depute_edito->participation($data['participation']['score'], $data['participation']['group']); //edited for GROUP
          }
        } else {
          $data['no_participation'] = TRUE;
        }

        // LOYALTY
        $data['loyaute'] = $this->deputes_model->get_stats_loyaute($mpId, $legislature);
        if ($data['loyaute'] && $data['loyaute']['votesN'] >= 10) {
          $data['no_loyaute'] = FALSE;
          // GET ALL DATA FOR LOYALTY
          $data['loyaute']['all'] = $this->deputes_model->get_stats_loyaute_all($legislature);
          $data['loyaute']['group'] = $this->deputes_model->get_stats_loyaute_group($legislature, $groupe_id);
          if (isset($data['loyaute']['score'])){
            $data['edito_loyaute']['all'] = $this->depute_edito->loyaute($data['loyaute']['score'], $data['loyaute']['all']); // edited for ALL
            $data['edito_loyaute']['group'] = $this->depute_edito->loyaute($data['loyaute']['score'], $data['loyaute']['group']); //edited for GROUP
          }
          // loyalty history
          $data['loyaute_history'] = $this->deputes_model->get_stats_loyaute_history($mpId, $legislature);
        } else {
          $data['no_loyaute'] = TRUE;
        }

        // PROXIMITY WITH MAJORITY
        if (!in_array($groupe_id, $this->groupes_model->get_all_groupes_majority())) {
          $data['majorite'] = $this->deputes_model->get_stats_majorite($mpId, $legislature);
          if ($data['majorite'] && $data['majorite']['votesN'] >= 10) {
            $data['no_majorite'] = FALSE;
            // GET ALL DATA FOR PROXIMITY WITH MAJORITY
            $data['majorite']['all'] = $this->deputes_model->get_stats_majorite_all($legislature); // DOUBLE CHECK --> ONLY THOSE NOT FROM THE GROUP OF THE MAJORITY
            $data['majorite']['group'] = $this->deputes_model->get_stats_majorite_group($legislature, $groupe_id);
            $data['edito_majorite']['all'] = $this->depute_edito->majorite($data['majorite']['score'], $data['majorite']['all']); // edited for ALL
            $data['edito_majorite']['group'] = $this->depute_edito->majorite($data['majorite']['score'], $data['majorite']['group']); //edited for GROUP
          } else {
            $data['no_majorite'] = TRUE;
          }
        }

        // PROXIMITY WITH ALL GROUPS
        if ($legislature == legislature_current() && dissolution() === false) {
          $data['accord_groupes'] = $this->deputes_model->get_accord_groupes_actifs($mpId, legislature_current());
          $data['accord_groupes_all'] = $this->deputes_model->get_accord_groupes_all($mpId, legislature_current());
          // Positionnement politique
          $accord_groupes_sorted = $data['accord_groupes'];
          if (empty($accord_groupes_sorted)) {
            $data["no_votes"] = TRUE;
          } else {
            $data["no_votes"] = FALSE;
            $sort_key  = array_column($accord_groupes_sorted, 'accord');
            array_multisort($sort_key, SORT_DESC, $accord_groupes_sorted);
            $data['proximite'] = $this->depute_edito->positionnement($accord_groupes_sorted, $groupe_id);
          }
        } else /* LEGISLATURE 14 */ {
          $data['accord_groupes'] = $this->deputes_model->get_accord_groupes_all($mpId, $legislature);
          $data['accord_groupes_all'] = $data['accord_groupes'];

          if ($data['accord_groupes']) {
            $data['no_votes'] = FALSE;
          } else {
            $data['no_votes'] = TRUE;
          }
        }
        $accord_groupes_n = count($data['accord_groupes']);
        $accord_groupes_divided = round($accord_groupes_n / 2, 0, PHP_ROUND_HALF_UP);
        $data['accord_groupes_first'] = array_slice($data['accord_groupes'], 0, $accord_groupes_divided);
        $data['accord_groupes_first'] = array_slice($data['accord_groupes_first'], 0, 3);
        $data['accord_groupes_last'] = array_slice($data['accord_groupes'], $accord_groupes_divided, $accord_groupes_n);
        $data['accord_groupes_last'] = array_slice($data['accord_groupes_last'], -3);
        $data['accord_groupes_last_sorted'] = array_reverse($data['accord_groupes_last']);
      }

      return $data;
    }
    
    public function showDeputeIframe($name, $category)
    {

      // $section = isset($_GET['section']) ? $_GET['section'] : null;
      // $title = isset($_GET['title']) ? $_GET['title'] : 'show';
      // $categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
      // $name = isset($_GET['nom']) ? $_GET['nom'] : '';
      // var_dump($name);



      // $name = "nadege-abomangoli";
      //PA795228


      $data['category'] = $category;
      // $departement = "seine-saint-denis-93";
      $departement =  $this->deputes_model->get_dptslug_by_name_url($name);
    
      $data['depute'] = $this->deputes_model->get_depute_individual($name, $departement);
    

        setlocale(LC_TIME, 'french');
  
        // // ____________________CHECK IF DEPUTE EXISTS__________________
        // if (empty($data['depute'])) {
        //   show_404($this->functions_datan->get_404_infos());
        // }
  
        // // ____________________CHECK IF LEGISLATURE > 14_______________
        // if (!$data['depute']['legislature'] >= 14) {
        //   show_404($this->functions_datan->get_404_infos());
        // }
  
        // ____________________CACHING_________________________________
        // if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
        //   $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)F
        // }
  
        // // ____________________MAIN VARIABLES___________________________
        $depute = $data['depute'];
        $mp_id = $depute['mpId'];
        $name_last = $depute['nameLast'];
        $depute_dpt = $depute['dptSlug'];
        $data['active'] = $depute['active'];
        $legislature = $depute['legislature'];
        $data['infos_groupes'] = groups_position_edited();
        $depute_full_name = $depute['nameFirst'].' '.$depute['nameLast'];
        $data['no_job'] = array('autre profession','autres', 'sans profession déclarée', 'sans profession');
        $groupe_id = $depute['groupeId'];
        $data['gender'] = gender($depute['civ']); 
    


        // ____________________GET GENERAL INFOS___________________________
        $data = $this->deputeservice->get_general_infos($data, $mp_id, $legislature, $name_last, $depute_full_name);
  
  
        // ____________________GET MAJORITY GROUP___________________________
        // $data['groupMajority'] = $this->groupes_model->get_majority_group($legislature); 

        // ↳ besoin peut être pour le bloc statistiques(partial statistics/_inter_goup_loyalty) si "Proximité avec la majorité gouvernementale"
        
        
        //____________________GET STATISTICS__________________________________
        $data = $this->get_statistiques($data, $legislature, $mp_id, $groupe_id); 
  
      
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

        // ________________ GET Depute page ressources (meta, css, js...)_______

        $data = $this->deputeservice->get_mp_page_resources($data, $depute_full_name, $name);

      
        // ________________ LOAD views_______

        $this->load->view('iframe/partials/_header_iframe', $data);
        $this->load->view('iframe/depute', $data);
        $this->load->view('iframe/partials/_footer_iframe', $data);

    }

}








  // //__________________GET LAST EXPLICATION_______________________________
        // $data['explication'] = $this->deputeservice->get_explication_details($mp_id, $legislature, $data['gender']);


                // //___________________GET OTHER MPS____________________________________
        // $related_deputes = $this->deputeservice->get_other_mps($legislature, $groupe_id, $name_last, $mp_id, $data['active'], $depute_dpt);
        // $data['other_deputes'] = $related_deputes['other_deputes'];
        // $data['other_deputes_dpt'] = $related_deputes['other_deputes_dpt'];
        // $data['depute']['dateNaissanceFr'] = utf8_encode(strftime('%d %B %Y', strtotime($data['depute']['birthDate']))); // birthdate


        
        // // ____________________GET GROUP___________________________________
        // $data = $this->deputeservice->get_group_info($data, $mp_id, $groupe_id);