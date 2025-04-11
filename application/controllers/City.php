<?php
  class City extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('departement_model');
      $this->load->model('groupes_model');
      $this->load->model('depute_edito');
      $this->load->model('city_model');
    }

    public function index($input, $departement){
      $input_ville = $input;
      $data['ville'] = $this->city_model->get_individual($input_ville, $departement);

      if (empty($data['ville'])) {
        show_404($this->functions_datan->get_404_infos());
      }

      $n_circos = count($data['ville']);

      //Variables
      $v = $data['ville'][0];
      $commune_nom = $v['commune_nom'];
      $dpt_code = $v['dpt'];
      $departement = $v['dpt_slug'];
      $code_postal = $v['code_postal'];
      $insee = $v['insee'];

      // GET THE MPs
      // If only one district
      if ($n_circos == 1) {
        $circo[] = $v['circo'];
        $data['n_circos'] = $n_circos;
        $data['deputes_commune'] = $this->city_model->get_mps($departement, $circo, legislature_current());

        if (empty($data['deputes_commune'])) {
          $data['noMP'] = TRUE;
        } else {
          $data['noMP'] = FALSE;
          $data['depute_commune'] = $data['deputes_commune'][0];
          $data['depute_commune']['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($data['depute_commune']['libelleAbrev'], $data['depute_commune']['couleurAssociee']));
          $data['depute_commune']['electionCircoAbbrev'] = abbrev_n($data['depute_commune']['electionCirco'], TRUE);
          $data['depute_commune']['cardCenter'] = $data['depute_commune']['electionCirco']."<sup>".$data['depute_commune']['electionCircoAbbrev']."</sup> circonscription";
          $data['gender'] = gender($data['depute_commune']['civ']);
        }

      // IF more than one district
      } else {
        $data['noMP'] = FALSE;
        $circo = array();
        $circo_edited = array();
        foreach ($data['ville'] as $key => $value) {
          $circo[] = $value['circo'];
          $circo_edited[$key]["number"] = $value['circo'];
          $circo_edited[$key]["abbrev"] = abbrev_n($value['circo'], TRUE);
        }
        $data['circos'] = $circo_edited;
        $data['n_circos'] = $n_circos;
        $data['deputes_commune'] = $this->city_model->get_mps($departement, $circo, legislature_current());
        foreach ($data['deputes_commune'] as $key => $value) {
          $data['deputes_commune'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
          $data['deputes_commune'][$key]['electionCircoAbbrev'] = abbrev_n($value['electionCirco'], TRUE);
          $data['deputes_commune'][$key]['cardCenter'] = $data['deputes_commune'][$key]['electionCirco']."<sup>".$data['deputes_commune'][$key]['electionCircoAbbrev']."</sup> circonscription";
        }
      }

      // Get other MPs from the same department
      $deputes_commune = array();
      foreach ($data['deputes_commune'] as $n) {
        $deputes_commune[] = $n['mpId'];
      }
      $data['deputes_dpt'] = $this->city_model->get_mps_dpt($departement, $deputes_commune, legislature_current());
      foreach ($data['deputes_dpt'] as $key => $value) {
        $data['deputes_dpt'][$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
        $data['deputes_dpt'][$key]['electionCircoAbbrev'] = abbrev_n($value['electionCirco'], TRUE);
        $data['deputes_dpt'][$key]['cardCenter'] = $data['deputes_dpt'][$key]['electionCirco']."<sup>".$data['deputes_dpt'][$key]['electionCircoAbbrev']."</sup> circonscription";
      }

      // Some editing depending on the number of MPs
      if (!$data['noMP']) {
        if ($n_circos > 1) {
          $s = "s";
          $ses = "leurs";
          $son = "leur";
          $le = "les";
          $depute_writing = "députés";
          $elu_writing = "élus";
        } else {
          $s = NULL;
          $ses = "ses";
          $son = "son";
          $le = $data["gender"]["le"];
          $depute_writing = "député".$data["gender"]["e"];
          $elu_writing = "élu".$data["gender"]["e"];
        }
      }
      // Some editing for city's name
      $vocals = array('A','E','I','O','U');
      if (ctype_alpha($commune_nom) && in_array($commune_nom[0], $vocals)) {
        $de = "d'";
      } else {
        $de = "de ";
      }

      // Get all big cities from the department
      $data['communes_dpt'] = $this->city_model->get_communes_by_dpt($departement, 4000);

      // Clean infos on the city
      $data['ville'] = $data['ville'][0];
      $data['ville']['circo_abbrev'] = abbrev_n($data['ville']['circo'], TRUE);
      $data['ville']['pop2017'] = $this->functions_datan->dec_round($data['ville']['pop2017'], mb_strlen($data['ville']['pop2017']) - 4);
      $data['ville']['pop2017_format'] = number_format($data['ville']['pop2017'], 0, ',', ' ');
      if ($data['ville']['evol10'] > 0) {
        $data['ville']['evol10_text'] = 'augmenté';
      } else {
        $data['ville']['evol10_text'] = 'diminué';
      }
      $data['ville']['evol10_edited'] = str_replace("-", "", $data['ville']['evol10']);

      // Get city adjacentes
      $data['adjacentes'] = $this->city_model->get_adjacentes($insee, 4);

      // Get city mayor
      $data['mayor'] = $this->city_model->get_mayor($data['ville']['dpt'], $insee, $data['ville']['commune']);
      $data['mayor']['gender'] = gender($data['mayor']['gender']);

      // Get last election (2024 legislatives)
      $data['results_legislatives_last'] = $this->city_model->get_results_legislatives($insee, 2024);
      $array = array();
      foreach ($data['results_legislatives_last'] as $key => $item) {
         $array[$item['circo']][$key] = $item;
      }
      ksort($array, SORT_NUMERIC);
      $data['results_legislatives_last'] = $array;
      $data['results_legislatives_last_first'] = reset($data['results_legislatives_last']);
      if ($data['results_legislatives_last_first']) {
        $data['results_legislatives_last_first'] = reset($data['results_legislatives_last_first']);
      }

      // Get all other elections 
      $data['elections'] = $this->city_model->get_results_elections($n_circos, $data['ville']['dpt'], $data['ville']['commune'], $insee);

      // Edited presidentielle
      if ($data['elections']['pres_2017']['results'][0]['votants'] > 0 && $data['elections']['pres_2022']['results'][0]['votants'] > 0) {
        $data['results_pres_edited'] = $this->city_model->get_results_pres_edited($data['ville'], $data['elections']['pres_2017']['results'], $data['elections']['pres_2022']['results']);
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Députés par circonscription : ".$commune_nom." ".$code_postal." | Datan";
      if (!$data['noMP']) {
        $data['description_meta'] = "Découvrez le".$s." député".$s." élu".$s." dans la ville ".$de."".$commune_nom." (".$dpt_code.") et tous ".$ses." résultats de vote : taux de participation, loyauté avec ".$son." groupe, proximité avec la majorité présidentielle.";
        $data['title'] = ucfirst($depute_writing)." ".$elu_writing." dans la ville ".$de."".$commune_nom;
      } else {
        $data['description_meta'] = "Découvrez les députés élus dans la ville ".$de."".$commune_nom." (".$dpt_code.") et tous ses résultats de vote : taux de participation, loyauté avec leur groupe, proximité avec la majorité présidentielle.";
        $data['title'] = "Députés élus dans la ville ".$de."".$commune_nom;
      }

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Députés", "url" => base_url()."deputes", "active" => FALSE
        ),
        array(
          "name" => $data['ville']['dpt_nom']." (".$data['ville']['dpt'].")", "url" => base_url()."deputes/".$data['ville']['dpt_slug'], "active" => FALSE
        ),
        array(
          "name" => $data['ville']['commune_nom'], "url" => base_url()."deputes/".$data['ville']['dpt_slug']."/ville_".$input_ville, "active" => TRUE
        ),
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS TO LOAD
      $data['critical_css'] = "city";
      // JS UP
      // JS
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('departement/commune', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
?>
