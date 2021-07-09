<?php
  class Faq extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('faq_model');
    }

    public function index() {
      $data['categories'] = $this->faq_model->get_categories();
      foreach ($data['categories'] as $category) {
        $data['articles'][$category['id']] = $this->faq_model->get_articles($category['id'], 'published');
        // Additional questions
        $data['articles'][$category['id']] = $this->faq_model->get_additional_articles($category, $data['articles'][$category['id']]);
      }

      foreach ($data['categories'] as $key => $value) {
        $n = count($data['articles'][$value['id']]);
        $data['categories'][$key]['n'] = $n;
        if ($n == 0) {
          unset($data['categories'][$key]);
        }
      }

      //Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Foire aux questions - Assemblée nationale | Datan";
      $data['description_meta'] = "Comment les députés sont-ils élus ? Quels sont leur rôle au parlement ? Découvrez la foire aux questions (FAQ) de Datan.";
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Foire aux questions", "url" => base_url()."faq", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Open Graph
      $data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // FAQ schema
      $data['schema'] = $this->faq_model->get_faq_schema($data['articles']);
      // CSS
      // JS
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('faq/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
    }

  }
?>
