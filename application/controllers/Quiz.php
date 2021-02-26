<?php

  class Quiz extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->model('post_model');
      $this->load->model('breadcrumb_model');
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
      var_dump($this->input->post());die;
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
