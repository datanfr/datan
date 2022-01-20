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
    $this->load->model('elections_model');
    $this->load->model('city_model');
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
      $past14 = $this->deputes_model->check_depute_legislature($result["mpId"], 14);
      if ($past14) {
        $urls[]["url"] = base_url()."deputes/".$dpt_slug."/depute_".$nameUrl."/legislature-14";
      }
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 3. sitemap-deputes-inactifs-1.xml */
  function deputes_inactifs(){
    $results = $this->deputes_model->get_deputes_all(legislature_current(), FALSE, NULL);
    $deputes14 = $this->deputes_model->get_deputes_last(14);
    //print_r($results);

    $urls = array();
    foreach ($results as $result) {
      $urls[]["url"] = base_url()."deputes/".$result["dptSlug"]."/depute_".$result["nameUrl"];
      $urls[]["url"] = base_url()."deputes/".$result["dptSlug"]."/depute_".$result["nameUrl"]."/votes";
      $urls[]["url"] = base_url()."deputes/".$result["dptSlug"]."/depute_".$result["nameUrl"]."/votes/all";
    }
    foreach ($deputes14 as $result) {
      $urls[]["url"] = base_url()."deputes/".$result["dptSlug"]."/depute_".$result["nameUrl"];
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
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev;
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/membres";
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/votes";
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/votes/all";
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 5. sitemap-groupes-inactifs-1.xml */
  function groupes_inactifs(){
    $results_current = $this->groupes_model->get_groupes_all(FALSE, legislature_current());
    $results14 = $this->groupes_model->get_groupes_all(TRUE, 14);
    $results = array_merge($results_current, $results14);

    $urls = array();
    foreach ($results as $result) {
      $libelleAbrev = mb_strtolower($result['libelleAbrev']);
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev;
      $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/membres";
      if ($result['legislature'] >= 15) {
        $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/votes";
        $urls[]["url"] = base_url()."groupes/legislature-".$result['legislature']."/".$libelleAbrev."/votes/all";
      }
    }

    $data['urls'] = $urls;
    $data['nbUrl'] = count($data['urls']);

    $this->load->view('sitemap/page', $data);
  }

  /* 6. sitemap-votes-1.xml */
  function votes(){
    $urls = array();

    foreach (legislature_all() as $legislature) {
      $results = $this->votes_model->get_all_votes($legislature, NULL, NULL, NULL);

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
      $cities = $this->city_model->get_communes_population($slug);
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

    // Data
    $fields = $this->fields_model->get_active_fields();
    $data['years'] = $this->votes_model->get_years_archives(legislature_current());
    $data['years'] = array_column($data['years'], 'votes_year');
    $data['months'] = $this->votes_model->get_months_archives(legislature_current());
    $data['elections'] = $this->elections_model->get_election_all();

    // Create array with urls
    $urls = array();
    $urls[]["url"] = base_url();
    $urls[]["url"] = base_url()."deputes";
    foreach (legislature_all() as $legislature) {
      if ($legislature != legislature_current()) {
        $urls[]["url"] = base_url()."deputes/legislature-".$legislature;
      }
    }
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
    $urls[]["url"] = base_url()."faq";
    $urls[]["url"] = base_url()."statistiques";
    $urls[]["url"] = base_url()."statistiques/aide";
    $urls[]["url"] = base_url()."statistiques/deputes-age";
    $urls[]["url"] = base_url()."statistiques/groupes-age";
    $urls[]["url"] = base_url()."statistiques/groupes-feminisation";
    $urls[]["url"] = base_url()."statistiques/deputes-loyaute";
    $urls[]["url"] = base_url()."statistiques/groupes-cohesion";
    $urls[]["url"] = base_url()."statistiques/deputes-participation";
    $urls[]["url"] = base_url()."statistiques/groupes-participation";
    $urls[]["url"] = base_url()."statistiques/deputes-origine-sociale";
    $urls[]["url"] = base_url()."statistiques/groupes-origine-sociale";
    $urls[]["url"] = base_url()."elections";
    foreach ($data['elections'] as $election) {
      $urls[]["url"] = base_url()."elections/".$election['slug'];
    }

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
      if ($result['modified_at'] == NULL) {
        $posts[$i]['lastmod'] = substr($result['created_at'], 0, 10);
      } else {
        $posts[$i]['lastmod'] = substr($result['modified_at'], 0, 10);
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
