<?php
  class Pages extends CI_Controller {
    public function __construct(){
      parent::__construct();
    }
    public function view($page){

      if (!file_exists(APPPATH.'views/pages/'.$page.'.php')) {
        show_404($this->functions_datan->get_404_infos());
      }

      if ($page == "a-propos") {
        $data['title_meta'] = "À propos | Datan";
        $data['description_meta'] = "Datan est un outil indépendant visant à mieux rendre compte de l’activité parlementaire des députés.";
        $data['title'] = "À propos";
      } elseif ($page == "mentions-legales") {
        $data['title_meta'] = "Mentions légales";
        $data['description_meta'] = "Mentions légales du site Datan.";
        $data['title'] = "Mentions légales";
        $data['mentions'] = "pg-mentions";
      } elseif ($page == "contact") {
        $data['title_meta'] = "Contactez-nous | Datan";
        $data['description_meta'] = "Contactez les membres du site Datan.";
        $data['title'] = "Contactez-nous";
      } elseif ($page == "statistiques") {
        $data['title_meta'] = "Les statistiques expliquées | Datan";
        $data['description_meta'] = "Datan est un outil indépendant rendant compte de l'activité parlementaire des députés français. Découvrez les statistiques développées par l'équipe de Datan.";
        $data['title'] = "Les statistiques de Datan expliquées";
      } else {
        $data['title'] = ucfirst($page);
      }

      if ($page == "statistiques") {
        $page_breadcrumb = "statistiques/aide";
      } else {
        $page_breadcrumb = $page;
      }

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => $data['title'], "url" => base_url().$page_breadcrumb, "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Meta
      $data['url'] = $this->meta_model->get_url();
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      // Preloads
      $data['preloads'] = array(
        array("href" => asset_url()."imgs/cover/hemicycle-front-375.jpg", "as" => "image", "media" => "(max-width: 575.98px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front-768.jpg", "as" => "image", "media" => "(min-width: 576px) and (max-width: 970px)"),
        array("href" => asset_url()."imgs/cover/hemicycle-front.jpg", "as" => "image", "media" => "(min-width: 970.1px)"),
      );
      // Load Views
      $this->load->view('templates/header', $data);
      $this->load->view('pages/'.$page, $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

  }
 ?>
