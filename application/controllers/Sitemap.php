<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sitemap extends CI_Controller {

  function __construct() {
    parent::__construct();
    $this->load->model('deputes_model');
    $this->load->model('groupes_model');
    $this->load->model('votes_model');
    $this->load->model('departement_model');
    $this->load->model('category_model');
    $this->load->model('post_model');
    $this->load->model('fields_model');
    $this->load->model('parties_model');
  }

  /* 1. Index */
  function index() {
    $this->load->view('sitemap/index');
  }

  /* 2. datan/sitemap-deputes-1.xml */
  function deputes(){
    $results = $this->deputes_model->get_deputes_all(legislature_current(), TRUE, NULL);
    //print_r($results);

    $urls = array();
    foreach ($results as $result) {
      $dpt_slug = $result['dptSlug'];
      $nameUrl = $result['nameUrl'];
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl;
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl."/votes";
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl."/votes/all";
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 3. sitemap-deputes-inactifs-1.xml */
  function deputes_inactifs(){
    $results = $this->deputes_model->get_deputes_all(legislature_current(), FALSE, NULL);
    //print_r($results);

    $urls = array();
    foreach ($results as $result) {
      $dpt_slug = $result['dptSlug'];
      $nameUrl = $result['nameUrl'];
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl;
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl."/votes";
      $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl."/votes/all";
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 4. sitemap-groupes-1.xml */
  function groupes(){
    $results = $this->groupes_model->get_groupes_all(TRUE, legislature_current());
    //print_r($results);

    $urls = array();
    foreach ($results as $result) {
      $libelleAbrev = mb_strtolower($result['libelleAbrev']);
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev;
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/membres";
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/votes";
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/votes/all";
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 5. sitemap-groupes-inactifs-1.xml */
  function groupes_inactifs(){
    $results = $this->groupes_model->get_groupes_all(FALSE, legislature_current());

    $urls = array();
    foreach ($results as $result) {
      $libelleAbrev = mb_strtolower($result['libelleAbrev']);
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev;
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/membres";
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/votes";
      $urls[]["url"] = base_url()."groupes/".$libelleAbrev."/votes/all";
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 6. sitemap-votes-1.xml */
  function votes(){
    $urls = array();

    foreach (legislature_all() as $legislature) {
      $results = $this->votes_model->get_all_votes($legislature, NULL, NULL);

      foreach ($results as $result) {
        $n = $result['voteNumero'];
        $urls[]["url"] = base_url()."votes/legislature-".$legislature."/vote_".$n;
      }

    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 7. sitemap-localites-d-1.xml */
  function departements(){
    $results = $this->departement_model->get_all_departements();
    //print_r($results);

    $urls = array();
    foreach ($results as $result) {
      $slug = $result['slug'];
      $urls[]["url"] = base_url()."deputes/".$slug;
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 8. sitemap-localites-v-1.xml */
  function communes(){
    $departements = $this->departement_model->get_all_departements();
    //print_r($results);

    $urls = array();
    foreach ($departements as $dpt) {
      $slug = $dpt['slug'];
      $cities = $this->departement_model->get_communes_population($slug);
      $dpt_slug = $dpt['slug'];
      foreach ($cities as $city) {
        if ($city['commune_slug'] != NULL) {
          $city_slug = $city['commune_slug'];
          $urls[]['url'] = base_url()."deputes/".$dpt_slug."/ville_".$city_slug;
        }
      }
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 9. sitemap-structure-1.xml */
  function structure(){
    function number_zero($x){
      if ($x < 10) {
        return "0".$x;
      } else {
        return $x;
      }
    }

    $fields = $this->fields_model->get_active_fields();
    $data['years'] = $this->votes_model->get_years_archives();
    $data['years'] = array_column($data['years'], 'votes_year');
    $data['months'] = $this->votes_model->get_months_archives();


    // Create array with urls
    $urls = array();
    $urls[]["url"] = base_url();
    $urls[]["url"] = base_url()."deputes";
    $urls[]["url"] = base_url()."groupes";
    $urls[]["url"] = base_url()."votes";
    $urls[]["url"] = base_url()."partis-politiques";
    $urls[]["url"] = base_url()."votes/decryptes";
    foreach ($fields as $field) {
      $urls[]["url"] = base_url()."votes/decryptes/".$field["slug"];
    }
    $urls[]["url"] = base_url()."votes/legislature-15";
    // YEARS
    foreach ($data["years"] as $year) {
      $urls[]["url"] = base_url()."votes/legislature-15/".$year;
    }
    // MONTHS
    foreach ($data["years"] as $year) {
      foreach ($data["months"] as $month) {
        if ($month["years"] == $year) {
          $urls[]["url"] = base_url()."votes/legislature-15/".$year."/".$month['index'];
        }
      }
    }
    $urls[]["url"] = base_url()."mentions-legales";
    $urls[]["url"] = base_url()."a-propos";
    $urls[]["url"] = base_url()."blog";
    $urls[]["url"] = base_url()."statistiques";
    $urls[]["url"] = base_url()."statistiques/aide";
    $urls[]["url"] = base_url()."statistiques/deputes-age";
    $urls[]["url"] = base_url()."statistiques/groupes-age";
    $urls[]["url"] = base_url()."statistiques/groupes-feminisation";
    $urls[]["url"] = base_url()."statistiques/deputes-loyaute";
    $urls[]["url"] = base_url()."statistiques/groupes-cohesion";
    $urls[]["url"] = base_url()."statistiques/deputes-participation";
    $urls[]["url"] = base_url()."statistiques/groupes-participation";

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);
    $this->load->view('sitemap/page', $data);
  }

  /* 10. sitemap-categories-1.xml */
  function categories(){
    $results = $this->category_model->get_active_categories();

    $urls = array();
    foreach ($results as $result) {
      $slug = $result['slug'];
      $urls[]["url"] = base_url()."blog/categorie/".$slug;
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 11. sitemap-posts-1.xml */
  function posts(){
    $results = $this->post_model->get_posts(NULL, NULL, NULL);

    $i = 1;
    foreach ($results as $result) {
      $slug = $result['slug'];
      $slug_category = $result['category_slug'];
      $posts[$i]["url"] = base_url()."blog/".$slug_category."/".$slug;

      //modified_at
      if ($result['modified_at_sitemap'] == NULL) {
        $posts[$i]["lastmod"] = $result['created_at_sitemap'];
      } else {
        $posts[$i]["lastmod"] = $result["modified_at_sitemap"];
      }

      $i++;
    }

    $data['nbUrl'] = count($posts);
    $data['posts'] = $posts;

    //print_r($data['posts']);

    $this->load->view('sitemap/posts', $data);

  }

  /* 12. sitemap-partis-politiques-1.xml */
  function parties(){
    $resultsActive = $this->parties_model->get_parties_active();
    $resultsOther = $this->parties_model->get_parties_other();
    //print_r($results);

    $urls = array();
    foreach ($resultsActive as $result) {
      $libelleAbrev = mb_strtolower($result['libelleAbrev']);
      $urls[]["url"] = base_url()."partis-politiques/".$libelleAbrev;
    }

    foreach ($resultsOther as $result) {
      $libelleAbrev = mb_strtolower($result['libelleAbrev']);
      $urls[]["url"] = base_url()."partis-politiques/".$libelleAbrev;
    }

    $urls[]["url"] = base_url()."partis-politiques/nd"; // Add non déclarés
    $urls[]["url"] = base_url()."partis-politiques/nr"; // Add non ratachés

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

}
?>
