<?php
  class Votes extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('votes_model');
      $this->load->model('fields_model');
      $this->load->model('groupes_model');
      $this->load->model('deputes_model');
      $this->load->model('organes_model');
      //$this->password_model->security_password(); Former login protection
    }

    // Page = datan.fr/votes
    public function index(){
      // FUNCTION
      function number_zero($x){
        if ($x < 10) {
          return "0".$x;
        } else {
          return $x;
        }
      }
      function number($x){
        if ($x < 10) {
          return substr($x, 1);
        } else {
          return $x;
        }
      }

      // Get datan_votes
      $data['votes_datan'] = $this->votes_model->get_last_votes_datan(7);
      foreach ($data['votes_datan'] as $key => $value) {
        $data['votes_datan'][$key]['dateScrutinFRAbbrev'] = $this->functions_datan->abbrev_months($value['dateScrutinFR']);
      }
      // Get by category
      $data['fields'] = $this->fields_model->get_active_fields();
      $fields = $data['fields'];
      foreach ($fields as $field) {
        $x[$field["slug"]]["votes"] = array_slice($this->votes_model->get_votes_datan_category($field['id']), 0, 2);
        $x[$field["slug"]]["name"] = $field["name"];
        $x[$field["slug"]]["slug"] = $field["slug"];
        if ($this->functions_datan->get_http_response_code(base_url().'/assets/imgs/fields/'.$field["slug"].'.svg') != "200"){
          $x[$field["slug"]]["logo"] = FALSE;
        } else {
          $x[$field["slug"]]["logo"] = TRUE;
        }
      }
      $data['by_field'] = $x;

      // Get all votes
      $data['votes'] = $this->votes_model->get_all_votes(legislature_current(), NULL, NULL, 10);
      foreach ($data['votes'] as $key => $value) {
        $data['votes'][$key]['dateScrutinFRAbbrev'] = $this->functions_datan->abbrev_months($value['dateScrutinFR']);
      }
      // Archives
      $data['years'] = $this->votes_model->get_years_archives(legislature_current());
      $data['years'] = array_column($data['years'], 'votes_year');
      $data['months'] = $this->votes_model->get_months_archives(legislature_current());

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Votes Assemblée Nationale - Résultat et Analyse | Datan";
      $data['title'] = "Votes de la 15<sup>e</sup> législature";
      $data['description_meta'] = "Retrouvez tous les votes de l'Assemblée nationale de la 15e législature décryptés par Datan. Détails des votes, résultats des groupes et des députés, statistiques de participation, de loyauté et de cohésion.";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."votes", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => "https://unpkg.com/flickity@2/dist/flickity.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load']= array(
        "flickity.pkgd.min"
      );
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('votes/all', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

    // Page = datan.fr/votes/decryptes
    public function decryptes(){
      // Get Datan votes
      $data['votes'] = $this->votes_model->get_last_votes_datan();
      // Get active policy fields
      $data['fields'] = $this->fields_model->get_active_fields();
      // Get number of votes
      $data['number_votes'] = $this->votes_model->get_n_votes_datan(legislature_current());

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Votes Décryptés Assemblée Nationale | Datan";
      $data['description_meta'] = "Découvrez les votes de l'Assemblée national décryptés par Datan. Il s'agit des votes faisant l'objet d'une attention médiatique, ou sur lesquels les députés était fortement divisés.";
      $data['title'] = "Tous les votes décryptés de l'Assemblée Nationale";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
        ),
        array(
          "name" => "Votes décryptés", "url" => base_url()."votes/decryptes", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load']= array("datan/sorting");
      $data['js_to_load_before_datan'] = array("isotope.pkgd.min");
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('votes/all_decryptes', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    // Page categories = datan.fr/votes/[field]
    public function field($field){
      // Get infos about field
      $data['field'] = $this->fields_model->get_field($field);
      $f_id = $data['field']['id'];
      $f_slug = $data['field']['slug'];
      $f_name = $data['field']['name'];
      $f_libelle = $data['field']['libelle'];
      // Get votes from the field
      $data['votes'] = $this->votes_model->get_votes_datan_category($data['field']['id']);
      if (empty($data['votes'])) {
        show_404();
      }

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Votes Décryptés - ".$f_name." | Datan";
      $data['description_meta'] = "Retrouvez tous les votes de l'Assemblée nationale décrypté par Datan sur la thématique ".$f_name.". Détails des votes, résultats des groupes et des députés, statistiques de participation, de loyauté et de cohésion.";
      $data['title'] = "Les votes décryptés sur ".$f_libelle;
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
        ),
        array(
          "name" => "Votes décryptés", "url" => base_url()."votes/decryptes", "active" => FALSE
        ),
        array(
          "name" => $f_name, "url" => base_url()."votes/decryptes/".$f_slug, "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      //$data['js_to_load']= array();
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('votes/field_decryptes', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }

    // Pages = data.fr/votes/legislature-(:legislature)/(:year)/(:month)
    public function all($legislature, $year = NULL, $month = NULL) {
      // Check if legislature is a number
      if (!is_numeric($legislature)) {
        show_404();
      }
      $data['legislature'] = $legislature;
      // Get month array
      $months = get_months();
      // Create some functions
      function number_zero($x){
        if ($x < 10) {
          return "0".$x;
        } else {
          return $x;
        }
      }
      function number($x){
        if ($x < 10) {
          return substr($x, 1);
        } else {
          return $x;
        }
      }

      // Check if votes
      $data['votes'] = $this->votes_model->get_all_votes($legislature, $year, $month, FALSE);

      if (empty($data['votes'])) {
        show_404();
      }

      // ALL OR ALL/YEAR OR ALL/YEAR/MONTH ?
      if ($year == NULL && $month == NULL) {
        $data['h2'] = "Liste des votes de la ".$legislature."<sup>e</sup> législature";
        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Votes ".$legislature."e Législature - Assemblée Nationale | Datan";
        $data['title'] = "Votes de la ".$legislature."<sup>e</sup> législature";
        $data['description_meta'] = "Retrouvez tous les votes de l'Assemblée nationale de la ".$legislature."e législature. Détails des votes, résultats des groupes et des députés, statistiques de participation, de loyauté et de cohésion.";
        // Breadcrumb
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
          ),
          array(
            "name" => $legislature."e législature", "url" => base_url()."votes/legislature-".$legislature, "active" => TRUE
          )
        );
        $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
        //Open Graph
        $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
        $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
        // Indexes
        $data['y_index'] = NULL;
        $data['m_index'] = NULL;
        $data['archive'] = FALSE;
      } elseif ($year != NULL && $month == NULL) {
        $data['h2'] = "Liste des votes de la ".$legislature."<sup>e</sup> législature - ".$year;
        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Votes ".$year." Assemblée Nationale - Résultat et Analyse | Datan";
        $data['title'] = "Votes à l'Assemblée nationale en ".$year." - ".$legislature."<sup>e</sup> législature";
        $data['description_meta'] = "Retrouvez tous les votes de l'Assemblée nationale en ".$year.". Détails des votes, résultats de vote des groupes et des députés, statistiques de loyauté et de cohésion.";
        // Breadcrumb
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
          ),
          array(
            "name" => $legislature."e législature", "url" => base_url()."votes/legislature-".$legislature, "active" => FALSE
          ),
          array(
            "name" => $year, "url" => base_url()."votes/legislature-".$legislature."/".$year, "active" => TRUE
          )
        );
        $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
        //Open Graph
        $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
        $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
        // Indexes
        $data['y_index'] = $year;
        $data['m_index'] = NULL;
        $data['archive'] = TRUE;
      } elseif ($year != NULL && $month != NULL) {
        $month = number($month);
        $data['h2'] = "Liste des votes de la ".$legislature."<sup>e</sup> législature - ".$months[$month-1]." ".$year;
        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Votes ".ucfirst($months[$month-1])." ".$year." Assemblée Nationale - Résultat et Analyse | Datan";
        $data['title'] = "Votes à l'Assemblée nationale en ".$months[$month-1]." ".$year." - ".$legislature."<sup>e</sup> législature";
        $data['description_meta'] = "Retrouvez tous les votes de l'Assemblée nationale en ".$months[$month-1]." ".$year.". Détails des votes, résultats de vote des groupes et des députés, statistiques de loyauté et de cohésion.";
        // Breadcrumb
        if ($month < 10 ) {
          $month_breadcrumb = "0".$month;
        } else {
          $month_breadcrumb = $month;
        }
        $data['breadcrumb'] = array(
          array(
            "name" => "Datan", "url" => base_url(), "active" => FALSE
          ),
          array(
            "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
          ),
          array(
            "name" => $legislature."e législature", "url" => base_url()."votes/legislature-".$legislature, "active" => FALSE
          ),
          array(
            "name" => $year, "url" => base_url()."votes/legislature-".$legislature."/".$year, "active" => FALSE
          ),
          array(
            "name" => ucfirst($months[$month-1]), "url" => base_url()."votes/legislature-".$legislature."/".$year."/".$month_breadcrumb, "active" => TRUE
          )
        );
        $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
        //Open Graph
        $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
        $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
        // Indexes
        $data['y_index'] = $year;
        $data['m_index'] = $month;
        $data['archive'] = TRUE;
      }

      /* Get year */
      $data['years'] = $this->votes_model->get_years_archives(legislature_current());
      $data['years'] = array_column($data['years'], 'votes_year');
      /* Get months */
      $data['months'] = $this->votes_model->get_months_archives(legislature_current());

      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => TRUE
        )
      );
      // JS
      $data['js_to_load']= array("moment.min", "datatable-datan.min", "datetime-moment");
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('votes/all_an', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);

    }

    public function individual($legislature, $num){
      // Check if legislature is numeric
      if (!is_numeric($legislature)) {
        show_404();
      }
      $data['legislature'] = $legislature;
      if ($legislature != legislature_current()) {
        //show_404();
      }
      // Check if vote number is numeric
      if (!is_numeric($num)) {
        show_404();
      }

      // Get vote
      $data['vote'] = $this->votes_model->get_individual_vote($legislature, $num);
      if ($data['vote']['title']) {
        $voteDatan = TRUE;
      } else {
        $voteDatan = FALSE;
      }

      if (empty($data['vote'])) {
        show_404();
      }

      // Caching
      if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
          $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
      }

      // Vote edited
      $data['vote'] = $this->votes_model->get_individual_vote_edited($data['vote']);
      $data['vote']['description'] = text_url_obfuscation($data['vote']['description']);
      $data['description'] = preg_replace('/\s+/', '', strip_tags($data['vote']['description'])) === "" ? FALSE : TRUE;

      $data['vote']['edited'] = FALSE;
      if (!empty($data['vote']['title'])) {
        // Check whether published
        if ($data['vote']['state'] == 'published') {
          $data['vote']['edited'] = TRUE;
        }
        // Get logo
        if ($this->functions_datan->get_http_response_code(asset_url().'imgs/fields_white/'.$data['vote']['category_slug'].'.svg') != '200'){
          $data['vote']['logo'] = FALSE;
        } else {
          $data['vote']['logo'] = TRUE;
        }
      }

      // Info about the author --> WORKING ICI !
      if ($data['vote']['dossier'] && $legislature >= 15) {
        if ($data['vote']['voteType'] == 'amendement' || $data['vote']['voteType'] == 'sous-amendement') { // If the vote is an amendment
          $data['authorTitle'] = "L'auteur de l'amendement";
          $data['amdt'] = $this->votes_model->get_amendement($legislature, $data['vote']['dossierId'], $data['vote']['seanceRef'], $data['vote']['amdt']);
          $data['amdt'];
          if ($data['amdt']) { // If amendment is working properly :)
            $data['amdt']['author'] = $this->votes_model->get_amendement_author($data['amdt']['id']);
            if (in_array($data['amdt']['author']['type'], array('Député', 'Rapporteur'))) {
              $data['author'] = $this->deputes_model->get_depute_by_legislature($data['amdt']['author']['acteurRef'], $legislature);
              $data['author']['cardCenter'] = $data['author']['departementNom'] . ' (' . $data['author']['departementCode'] . ')';
              $data['authorType'] = "mp";
            } elseif ($data['amdt']['author']['type'] == 'Gouvernement') {
              $data['author'] = $this->organes_model->get_organe($data['amdt']['author']['acteurRef']);
              $data['authorType'] = "gvt";
            }
          } else {
            $newDossierIds = $this->votes_model->get_another_dossierId($data['vote']['dossier']);
            foreach ($newDossierIds as $newDossierId) {
              $newSeances = $this->votes_model->get_amendement_all_seanceRef($legislature, $newDossierId['dossierId'], $data['vote']['amdt']);
              foreach ($newSeances as $newSeance) {
                $data['amdt']['author'] = $this->votes_model->get_amendement_author($newSeance['id']);
                if (in_array($data['amdt']['author']['type'], array('Député', 'Rapporteur'))) {
                  $author = $this->deputes_model->get_depute_by_legislature($data['amdt']['author']['acteurRef'], $legislature);
                  if (strpos($data['vote']['titre'], $author['nameLast']) !== false) {
                    $data['author'] = $author;
                    $data['author']['cardCenter'] = $data['author']['departementNom'] . ' (' . $data['author']['departementCode'] . ')';
                    $data['authorType'] = "mp";
                    break 2;
                  }
                  if ($data['amdt']['author']['type'] == 'Rapporteur' || strpos($data['vote']['titre'], 'commission') !== false) {
                    $data['author'] = $author;
                    $data['author']['cardCenter'] = $data['author']['departementNom'] . ' (' . $data['author']['departementCode'] . ')';
                    $data['authorType'] = "mp";
                    break 2;
                  }
                } elseif ($data['amdt']['author']['type'] == 'Gouvernement') {
                  $author = $this->organes_model->get_organe($data['amdt']['author']['acteurRef']);
                  if (strpos($data['vote']['titre'], 'Gouvernement') !== false) {
                    $data['author'] = $author;
                    $data['authorType'] = "gvt";
                    break 2;
                  }
                }
              }
            }
          }
        } elseif (in_array($data['vote']['procedureParlementaireLibelle'], array('Proposition de loi ordinaire', 'Résolution Article 34-1', 'Résolution'))) {
          $data['authorType'] = "mps";
          $data['author'] = $this->votes_model->get_dossier_mp_authors($data['vote']['dossierId'], $legislature);
          if ($data['author']) {
            if (count($data['author']) == 1) {
              $data['authorTitle'] = "L'auteur de la proposition de loi";
            } else {
              $data['authorTitle'] = "Les auteurs de la proposition de loi";
            }
          } else {
            $data['author'] = $this->votes_model->get_dossier_mp_rapporteurs($data['vote']['dossierId'], $legislature);
            if (count($data['author']) == 1) {
              $data['authorTitle'] = "Le rapporteur";
            } else {
              $data['authorTitle'] = "Les rapporteurs";
            }
          }
        } elseif (in_array($data['vote']['procedureParlementaireLibelle'], array('Projet de ratification des traités et conventions'))) {
          $data['authorType'] = "mps";
          $data['author'] = $this->votes_model->get_dossier_mp_rapporteurs($data['vote']['dossierId'], $legislature);
          if (count($data['author']) == 1) {
            $data['authorTitle'] = "Le rapporteur";
          } else {
            $data['authorTitle'] = "Les rapporteurs";
          }
        }
      }

      // Votes - groupes
      $data['groupes'] = $this->votes_model->get_vote_groupes($data['vote']['voteNumero'], $data['vote']['dateScrutin'], $legislature);

      // Votes - députés
      $data['deputes'] = $this->votes_model->get_vote_deputes($data['vote']['voteNumero'], $legislature);
      // OTHER VOTES
      if ($num != 1) {
        $previous = $num - 1;
        $previous_result = TRUE;
        while ($previous_result) {
          $query = $this->votes_model->get_individual_vote($legislature, $previous);
          if (empty($query)) {
            $previous_result = TRUE;
            $previous = $previous - 1;
          } else {
            $previous_result = FALSE;
            $data['vote_previous'] = $previous;
            break;
          }
        }
      } else {
        $data['vote_previous'] = FALSE;
      }

      $last_vote = $this->votes_model->get_last_vote();
      if ($num != $last_vote['voteNumero']) {
        $next = $num + 1;
        $next_result = TRUE;
        while ($next_result) {
          $query = $this->votes_model->get_individual_vote($legislature, $next);
          if (empty($query)) {
            $next_result = TRUE;
            $next = $next + 1;
          } else {
            $next_result = FALSE;
            $data['vote_next'] = $next;
            break;
          }
        }
      } else {
        $data['vote_next'] = FALSE;
      }

      $data['votes_datan'] = $this->votes_model->get_last_votes_datan(7);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Vote n°".$data['vote']['voteNumero']." - ".ucfirst($data['vote']['title_meta']). " - ".$legislature."e législature | Datan";
      $data['description_meta'] = "Découvrez le vote n° ".$data['vote']['voteNumero']." : ".ucfirst($data['vote']['title_meta'])." - ".$legislature."e législature. Détails et analyses des résultats du vote.";
      $data['title'] = $data['vote']['titre'];
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Votes", "url" => base_url()."votes", "active" => FALSE
        ),
        array(
          "name" => $legislature."e législature", "url" => base_url()."votes/legislature-".$legislature, "active" => FALSE
        ),
        array(
          "name" => "Vote n° ".$data['vote']['voteNumero'], "url" => base_url()."votes/legislature-".$legislature."/vote_".$data['vote']['voteNumero'], "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      if ($voteDatan) {
        $title_ogp = "Vote Assemblée nationale : " . $data['vote']['title'] . " | Datan";
        $title_og_img = str_replace(' ', '%20', $data['vote']['title']);
        $date_og_img = str_replace(' ', '%20', $data['vote']['date_edited']);
        $data['vote']['og_image'] = 'https://og-image-datan.vercel.app/'.$title_og_img.'?voteN='.$data['vote']['voteNumero'].'&legislature='.$data['vote']['legislature'].'&date='.$date_og_img.'&pour='.$data['vote']['pour'].'&abs='.$data['vote']['abstention'].'&sort='.$data['vote']['sortCode'];
      } elseif($data['vote']['voteType'] == "final") {
        $title_ogp = "Assemblée nationale : " . $data['vote']['dossier_titre'] . " - Vote final";
        $title_og_img = str_replace(' ', '%20', ucfirst($data['vote']['titre']));
        $date_og_img = str_replace(' ', '%20', $data['vote']['date_edited']);
        $data['vote']['og_image'] = 'https://og-image-datan.vercel.app/'.$title_og_img.'?voteN='.$data['vote']['voteNumero'].'&legislature='.$data['vote']['legislature'].'&date='.$date_og_img.'&pour='.$data['vote']['pour'].'&abs='.$data['vote']['abstention'].'&sort='.$data['vote']['sortCode'];
      } else {
        $title_ogp = $data['title_meta'];
      }
      $data['ogp'] = $this->meta_model->get_ogp($controller, $title_ogp, $data['description_meta'], $data['url'], $data);
      // Microdata Person
      if ($voteDatan) {
        $data['vote_schema'] = $this->votes_model->get_vote_schema($data['vote']);
      }
      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."chart.min.css",
          "async" => FALSE
        ),
        array(
          "url" => "https://unpkg.com/flickity@2/dist/flickity.min.css",
          "async" => TRUE
        ),
        array(
          "url" => css_url()."datatables.bootstrap4.min.css",
          "async" => TRUE
        )
      );
      // JS UP
      $data['js_to_load_up'] = array("chart.min.js", "chartjs-plugin-annotation.js");
      // JS
      $data['js_to_load']= array("moment.min", "datatable-datan.min", "datetime-moment", "flickity.pkgd.min");
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('votes/individual', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }

  }
?>
