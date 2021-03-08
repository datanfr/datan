<?php

  class Posts extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->model('post_model');
      $this->load->model('breadcrumb_model');
      $this->load->model('category_model');
      $this->load->model('fields_model');
      //$this->password_model->security_password(); Former login protection
    }

    // VIEW ALL POSTS
    public function index(){
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;
      $data['posts'] = $this->post_model->get_posts(NULL, $user_type, NULL);
      $data['categories'] = $this->category_model->get_active_categories();

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Tous les articles", "url" => base_url()."blog", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Blog | Datan";
      $data['description_meta'] = "Découvrez l'actualité politique de l'Assemblée nationale, du gouvernement et des députés avec les articles de Datan.";
      $data['title'] = "Tous les articles";
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load']= array("datan/async_background");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('posts/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    // VIEW CATEGORY PAGE
    public function category($category){
      $data['category'] = $this->category_model->get_category($category);
      $data['posts'] = $this->post_model->get_posts_by_category($category);
      if (empty($data['posts'])) {
        show_404();
      }
      $data['categories'] = $this->category_model->get_active_categories();
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Tous les articles", "url" => base_url()."blog", "active" => FALSE
        ),
        array(
          "name" => $data['category']['name'], "url" => base_url()."blog/categorie/".$data['category']['slug'], "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Articles catégorie ". $data['category']['name']." | Datan";
      $data['description_meta'] = "A FAIRE ???"; // A FAIRE
      $data['title'] = "Catégorie ". $data['category']['name'];
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load']= array("datan/async_background");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('posts/index_category', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }

    // VIEW INDIVIDUAL POST
    public function view($slug = NULL, $category) {
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;
      $data['post'] = $this->post_model->get_posts($slug, $user_type, $category);
      if (empty($data['post'])) {
        show_404();
      }
      $data['categories'] = $this->category_model->get_active_categories();
      $data['last_posts'] = $this->post_model->get_last_posts();

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Tous les articles", "url" => base_url()."blog", "active" => FALSE
        ),
        array(
          "name" => $data['post']['category_name'], "url" => base_url()."blog/categorie/".$data['post']['category_slug'], "active" => FALSE
        ),
        array(
          "name" => $data['post']['title'], "url" => base_url()."blog/".$data['post']['category_slug']."/".$data['post']['slug'], "active" => FALSE
        ),
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = $data['post']['title']." | Datan";
      $data['description_meta'] = character_limiter(strip_tags($data['post']['body']), 300, "");
      $data['title'] = $data['post']['title'];
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      //$data['js_to_load']= array("datan/sort_mps");
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('templates/button_up');
      $this->load->view('posts/view', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');
    }

    public function create() {
      $this->password_model->security_admin();
      $data['username'] = $this->session->userdata('username');
      $data['title'] = 'Créer un post de blog';

      $data['categories'] = $this->category_model->get_categories();

      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('body', 'Body', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/posts/create', $data);
        $this->load->view('dashboard/footer');
      } else {

        $this->post_model->create_post();

        // Set message
        $this->session->set_flashdata('post_created', 'Votre post a été créé');
        redirect('blog');
      }

    }

    // DELETE A POST
    public function delete($id){
      $user_type = $this->session->userdata('type');

      if ($user_type != "admin") {
        redirect();
      } else {
        $this->password_model->security_only_admin();

        $this->post_model->delete_post($id);

        $this->session->set_flashdata('post_deleted', 'Votre post a été supprimé');

        redirect();
      }
    }

    // EDIT A POST (display page)
    public function edit($slug){
      $this->password_model->security_admin();
      $data['username'] = $this->session->userdata('username');
      $user_type = $this->session->userdata('type');
      $data['post'] = $this->post_model->get_posts($slug, $user_type, NULL);
      if (empty($data['post'])) {
        show_404();
      }
      $data['categories'] = $this->post_model->get_categories();
      $data['title'] = 'Editer un post';

      $this->form_validation->set_rules('title', 'Title', 'required');
      $this->form_validation->set_rules('body', 'Body', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/posts/edit');
        $this->load->view('dashboard/footer');
      }

    }

    // UPDATE A POST
    public function update(){
      $this->password_model->security_admin();
      $this->post_model->update_post();

      // Set message
      $this->session->set_flashdata('post_updated', 'Votre post a été modifié');

      redirect('blog');
    }
  }

 ?>
