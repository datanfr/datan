<?php

  class Quiz extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->model('post_model');
      $this->load->model('votes_model');
    }

    public function index(){
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;
      $data['votes'] = $this->votes_model->get_most_famous_votes(3);

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Quel député choisir ?", "url" => base_url()."questiionnaire", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Blog | Datan";
      $data['description_meta'] = "Répondez à ce test pour savoir de quel député ou quel parti vous êtes le plus proche ?";
      $data['title'] = "Quel député choisir ?";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load']= array("datan/async_background");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('quiz/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function result(){
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;
      $votes = $this->votes_model->get_votes_result_by_depute(array_keys($this->input->post()));
      $groupes = array();
      $deputes = array();
      foreach($votes as $vote){
        foreach($this->input->post() as $post){
          // if (!isset($groupes[$vote['uid']])){
          //   $groupes[$vote['uid']] = array();
          //   $groupes[$vote['uid']]['score'] = 0;
          //   $groupes[$vote['uid']]['name'] = $vote['libelle'];
          //   $groupes[$vote['uid']]['totalVote'] = 0;
          //   $groupes[$vote['uid']]['pour'] = 0;
          //   $groupes[$vote['uid']]['contre'] = 0;
          //   $groupes[$vote['uid']]['abstention'] = 0;
          // }
          // $groupes[$vote['uid']]['totalVote'] += 1;
          // $groupes[$vote['uid']]['pour'] += $vote['vote'] == "1" ? 1 :0;
          // $groupes[$vote['uid']]['contre'] += $vote['vote'] == "-1" ? 1 :0;
          // $groupes[$vote['uid']]['abstention'] += $vote['vote'] === "0" ? 1 :0;

          // if ($post['choice'] == $vote['vote']){
          //     $groupes[$vote['uid']]['score'] += $post["weigth"] * 2;
          // }
          // else if($vote['vote'] === "0"){
          //   $groupes[$vote['uid']]['score'] += $post["weigth"];
          // }
          // else {
          //   $groupes[$vote['uid']]['score'] -= $post["weigth"];
          // }
          if (!isset($deputes[$vote['mpId']])){
            $deputes[$vote['mpId']] = array();
            $deputes[$vote['mpId']]['score'] = 0;
            $deputes[$vote['mpId']]['name'] = $vote['nameFirst'] . ' '. $vote['nameLast'];
            $deputes[$vote['mpId']]['totalVote'] = 0;
            $deputes[$vote['mpId']]['pour'] = 0;
            $deputes[$vote['mpId']]['contre'] = 0;
            $deputes[$vote['mpId']]['abstention'] = 0;
          }
          if ($vote['nameLast'] == 'Ferrand'){
            echo '<pre>', var_dump($vote), '</pre>';
          }
          $deputes[$vote['mpId']]['totalVote'] += 1;
          $deputes[$vote['mpId']]['pour'] += $vote['vote'] == "1" ? 1 :0;
          $deputes[$vote['mpId']]['contre'] += $vote['vote'] == "-1" ? 1 :0;
          $deputes[$vote['mpId']]['abstention'] += $vote['vote'] === "0" ? 1 :0;

          if ($post['choice'] == $vote['vote']){
              $deputes[$vote['mpId']]['score'] += $post["weigth"] * 2;
          }
          else if($vote['vote'] === "0"){
            $deputes[$vote['mpId']]['score'] += $post["weigth"];
          }
          else {
            $deputes[$vote['mpId']]['score'] -= $post["weigth"];
          }
        }
      }
      // foreach($groupes as $key => $groupe){
      //   $groupes[$key]['averageScore'] = $groupes[$key]['score'] / $groupes[$key]['totalVote'];
      // }
      usort($deputes, function($a, $b){
        return $a > $b;
      });
      echo '<pre>', var_dump($deputes), '</pre>';die('');
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Quel député choisir ?", "url" => base_url()."questiionnaire", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Blog | Datan";
      $data['description_meta'] = "Répondez à ce test pour savoir de quel député ou quel parti vous êtes le plus proche ?";
      $data['title'] = "Quel député choisir ?";
      //Open Graph
    //   $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
    //   $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load']= array("datan/async_background");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('quiz/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }
  }
