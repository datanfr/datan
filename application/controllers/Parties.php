<?php
  class Parties extends CI_Controller{
    public function __construct() {
      parent::__construct();
      $this->load->model('breadcrumb_model');
      $this->load->model('functions_datan');
      $this->load->model('parties_model');
      //$this->password_model->security_password(); Former login protection
    }

    //INDEX - Homepage with all groups//
    public function index(){
      // Get parties
      $data['partiesActive'] = $this->parties_model->get_parties_active();
      // Color
      foreach ($data['partiesActive'] as $key => $value) {
        $data['partiesActive'][$key]['couleurAssociee'] = $this->parties_model->get_party_color($value);
      }
      // Get other parties
      $data['partiesOther'] = $this->parties_model->get_parties_other();

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Partis politiques | Datan";
      $data['description_meta'] = "Retrouvez tous les partis politiques représentés à l'Assemblée nationale. Quels sont les députés rattachés aux différents partis ?";
      $data['title'] = "Les partis politiques en France";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Partis politiques", "url" => base_url()."partis-politiques", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('parties/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    //INDIVIDUAL - individual page by group //
    public function individual($partySlug){
      $partySlug = mb_strtoupper($partySlug);
      // Get info about party
      $data['party'] = $this->parties_model->get_party_individual($partySlug);
      $organeRef = $data['party']['uid'];

      // If empty
      if (empty($data['party'])) {
        show_404();
      }

      // Get color
      $data['party']['couleurAssociee'] = $this->parties_model->get_party_color($data['party']);

      // Query active
      if ($data['party']['dateFin'] == NULL) {
        $data['active'] = TRUE;
      } else {
        $data['active'] = FALSE;
      }

      // Is there an image ?
      if ($this->functions_datan->get_http_response_code(asset_url().'imgs/parties/'.mb_strtolower($data['party']['libelleAbrev']).'.png') != "200") {
        $data['party']['img'] = FALSE;
      } else {
        $data['party']['img'] = TRUE;
      }

      // Get other parties
      $data['partiesActive'] = $this->parties_model->get_parties_active();
      $data['partiesOther'] = $this->parties_model->get_parties_other();

      // Get MPs
      $data['deputesActive'] = $this->parties_model->get_mps_active($organeRef);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      if ($data['party']['libelle'] == "Non rattaché(s)") {
        $data['title_meta'] = 'Députés non rattachés à un parti politique | Datan';
        $data['description_meta'] = "Découvrez les députés de l'Assemblée nationale qui déclarent ne pas être rattachés financièrement à un parti politique.";
        $data['title'] = 'Députés non rattachés à un parti politique';
      } elseif ($data['party']['libelle'] == "Non déclaré(s)") {
        $data['title_meta'] = 'Députés sans rattachement à un parti politique | Datan';
        $data['description_meta'] = "Découvrez les députés de l'Assemblée nationale qui ne déclarent aucun rattachement financier à un parti politique.";
        $data['title'] = 'Députés sans rattachement à un parti politique';
      } else {
        $data['title_meta'] = $data['party']['libelle'] . ' - Parti politique | Datan';
        $data['description_meta'] = "Découvrez les députés de l'Assemblée nationale rattachés au parti politique " . $data['party']['libelle'] . " (" . $data['party']['libelleAbrev'] . ").";
        $data['title'] = $data['party']['libelle']." (" . $data['party']['libelleAbrev'] . ")";
      }
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Partis politiques", "url" => base_url()."partis-politiques", "active" => FALSE
        ),
        array(
          "name" => $data['party']['libelle'], "url" => base_url()."partis-politiques/".mb_strtolower($data['party']['libelleAbrev']), "active" => TRUE
        ),
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // CSS
      // JS
      $data['js_to_load']= array(
        "datan/async_background"
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('parties/individual', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }


  }
?>
