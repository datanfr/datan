<?php
  class Faq extends CI_Controller {
    public function __construct() {
      parent::__construct();
      $this->load->model('faq_model');
    }

    public function index() {
      $data['categories'] = $this->faq_model->get_categories_n();
      foreach ($data['categories'] as $category) {
        $data['articles'][$category['id']] = $this->faq_model->get_articles($category['id'], 'published');
      }

      //Meta
      $data['title'] = "Foire aux questions";
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Foire aux questions - Assemblée nationale | Datan";
      $data['description_meta'] = "Comment les députés sont-ils élus ? Quels sont leurs rôles au parlement ? Découvrez la foire aux questions (FAQ) de Datan.";
      // Open Graph
      $data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // FAQ schema
      $data['schema'] = $this->faq_model->get_faq_schema($data['articles']);
      // CSS
      // JS
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('faq/index', $data);
      $this->load->view('templates/footer', $data);
    }

  }
?>
