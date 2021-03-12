<?php

class Quiz extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('post_model');
    $this->load->model('breadcrumb_model');
    $this->load->model('votes_model');
  }

  public function index()
  {
    $user_type = $this->session->userdata('type');
    $data['user'] = $user_type;
    $data['votes'] = $this->votes_model->get_most_famous_votes(20);

    // Breadcrumb
    $data['breadcrumb'] = array(
      array(
        "name" => "Datan", "url" => base_url(), "active" => FALSE
      ),
      array(
        "name" => "Quel député choisir ?", "url" => base_url() . "questiionnaire", "active" => TRUE
      )
    );
    $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
    // Meta
    $data['url'] = $this->meta_model->get_url();
    $data['title_meta'] = "Blog | Datan";
    $data['description_meta'] = "Répondez à ce test pour savoir de quel député ou quel parti vous êtes le plus proche ?";
    $data['title'] = "Quel député choisir ?";
    //Open Graph
    $controller = $this->router->fetch_class() . "/" . $this->router->fetch_method();
    $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
    // JS
    $data['js_to_load'] = array("datan/async_background");
    // Load views
    $this->load->view('templates/header', $data);
    $this->load->view('quiz/index', $data);
    $this->load->view('templates/breadcrumb', $data);
    $this->load->view('templates/footer');
  }

  public function result()
  {
    $user_type = $this->session->userdata('type');
    $data['user'] = $user_type;
    $votes = $this->votes_model->get_votes_result_by_depute(array_keys($this->input->post()));
    $groupes = array();
    $deputes = array();
    foreach ($votes as $vote) {
      foreach ($this->input->post() as $key => $post) {
        // group
        if ($key == $vote['voteNumero'] && $vote['libelle']) {
          if (!isset($groupes[$vote['uid']])){
            $groupes[$vote['uid']] = array();
            $groupes[$vote['uid']]['score'] = 0;
            $groupes[$vote['uid']]['name'] = $vote['libelle'];
            $groupes[$vote['uid']]['color'] = $vote['couleurAssociee'];
            $groupes[$vote['uid']]['totalVote'] = 0;
          }
          $groupes[$vote['uid']]['totalVote'] += 1;

          if ($post['choice'] == $vote['vote']){
              $groupes[$vote['uid']]['score'] += $post["weigth"] * 2;
          }
          else if($vote['vote'] === "0"){
            $groupes[$vote['uid']]['score'] += $post["weigth"];
          }
          else {
            $groupes[$vote['uid']]['score'] -= $post["weigth"];
          };

          // depute
          if (!isset($deputes[$vote['mpId']])) {
            $deputes[$vote['mpId']] = array();
            $deputes[$vote['mpId']]['score'] = 0;
            $deputes[$vote['mpId']]['name'] = $vote['nameFirst'] . ' ' . $vote['nameLast'];
            $deputes[$vote['mpId']]['color'] = $vote['couleurAssociee'];
          }

          if ($post['choice'] == $vote['vote']) {
            $deputes[$vote['mpId']]['score'] += ($post["weigth"] * 2);
          } else if ($vote['vote'] === "0") {
            $deputes[$vote['mpId']]['score'] += $post["weigth"];
          } else {
            $deputes[$vote['mpId']]['score'] -= $post["weigth"];
          }
        }
      }
    }
    foreach($groupes as $key => $groupe){
      $groupes[$key]['averageScore'] = $groupes[$key]['score'] / $groupes[$key]['totalVote']; 
    }
    usort($deputes, function ($a, $b) {
      return $a["score"] <  $b["score"];
    });
    usort($groupes, function ($a, $b) {
      return $a["averageScore"] <  $b["averageScore"];
    });
    $data['groupes'] = $groupes;
    $data['deputes'] = $deputes;
    // Breadcrumb
    $data['breadcrumb'] = array(
      array(
        "name" => "Datan", "url" => base_url(), "active" => FALSE
      ),
      array(
        "name" => "Quel député choisir ?", "url" => base_url() . "questiionnaire", "active" => TRUE
      )
    );
    $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
    // Meta
    $data['url'] = $this->meta_model->get_url();
    $data['title_meta'] = "Blog | Datan";
    $data['description_meta'] = "Répondez à ce test pour savoir de quel député ou quel parti vous êtes le plus proche ?";
    $data['title'] = "Quel député choisir ?";

      // CSS
      $data['css_to_load']= array(
        array(
          "url" => css_url()."chart.min.css",
          "async" => FALSE
        )
      );
      // JS UP
      $data['js_to_load_up'] = array("chart.min.js", "chartjs-plugin-annotation.js");

    // Load views
    $this->load->view('templates/header', $data);
    $this->load->view('quiz/result', $data);
    $this->load->view('templates/breadcrumb', $data);
    $this->load->view('templates/footer');
  }
}
