<?php

  class Posts extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->model('post_model');
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

      // Page elements 
      $data['page'] = 'index';
      $data['titleTag'] = 'h1';
      $data['subtitleTag'] = 'h2';
      $data['postTitleTag'] = 'h3';

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Blog", "url" => base_url()."blog", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Blog | Datan";
      $data['description_meta'] = "Découvrez l'actualité politique de l'Assemblée nationale, du gouvernement et des députés avec les articles de Datan.";
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
        show_404($this->functions_datan->get_404_infos());
      }
      $data['categories'] = $this->category_model->get_active_categories();
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;

      // Page elements 
      $data['page'] = $data['category']['slug'];
      $data['titleTag'] = 'h3';
      $data['subtitleTag'] = 'div';
      $data['postTitleTag'] = 'h4';

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Blog", "url" => base_url()."blog", "active" => FALSE
        ),
        array(
          "name" => $data['category']['name'], "url" => base_url()."blog/categorie/".$data['category']['slug'], "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = $data['category']['name']." - Blog | Datan";
      $data['description_meta'] = $data['category']['description_meta'];
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
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('posts/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer');

    }

    // VIEW INDIVIDUAL POST
    public function view($slug = NULL, $category) {
      $user_type = $this->session->userdata('type');
      $data['user'] = $user_type;
      $data['post'] = $this->post_model->get_posts($slug, $user_type, $category);
      if (empty($data['post'])) {
        show_404($this->functions_datan->get_404_infos());
      }
      $data['categories'] = $this->category_model->get_active_categories();
      $data['post']['image_url'] = !empty($data['post']['image_name']) 
        ? $data['post']['image_name'] 
        : 'img_post_' . $data['post']['id'];

      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Blog", "url" => base_url()."blog", "active" => FALSE
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
      // Microdata article
      $data['schema_article'] = $this->meta_model->get_schema_article($data['post']);

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
      $this->password_model->security();
      $data['type'] = 'team';
      $data['username'] = $this->session->userdata('username');
      $data['title'] = 'Créer un post de blog';
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      $data['categories'] = $this->category_model->get_categories();

      $this->form_validation->set_rules('title', 'Titre', 'required');
      $this->form_validation->set_rules('body', 'Texte', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/posts/create', $data);
        $this->load->view('dashboard/footer');
      } else {

        $success = $this->post_model->create_post();
        if (isset($success['error'])) {
          $this->session->set_flashdata('error', $success['error']);
          redirect('posts/create');
        }
        $this->session->set_flashdata('post_created', 'Votre post a été créé');
        redirect('blog');
      }

    }

    // DELETE A POST
    public function delete($id){
      $user_type = $this->session->userdata('type');
      $data['type'] = 'team';
      $post = $this->post_model->get_post_by_id($id);
      if ($user_type != 'admin') {
        redirect();
      } else {
        $this->password_model->security_only_admin();

        $this->post_model->delete_post($id, $post['image_name']);

        $this->session->set_flashdata('post_deleted', 'Votre post a été supprimé');

        redirect('blog');
      }
    }

    // EDIT A POST (display page)
    public function edit($slug){
      $this->password_model->security();
      $data['type'] = 'team';
      $data['username'] = $this->session->userdata('username');
      $user_type = $this->session->userdata('type');
      $data['post'] = $this->post_model->get_post_edit($slug, $user_type);
      if (empty($data['post'])) {
        show_404($this->functions_datan->get_404_infos());
      }
      $data['categories'] = $this->category_model->get_categories();
      $data['title'] = 'Editer un post';
      $data['title_meta'] = $data['title'] . ' - Dashboard | Datan';

      $this->form_validation->set_rules('title', 'Titre', 'required');
      $this->form_validation->set_rules('body', 'Texte', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/posts/edit');
        $this->load->view('dashboard/footer');
      }

    }

    // UPDATE A POST
    public function update(){
      $this->password_model->security();
      $data['type'] = 'team';
      
      $success = $this->post_model->update_post();
      
      if (isset($success['error'])) {
        $this->session->set_flashdata('error', $success['error']);
        redirect('posts/edit/' . $success['slug']);
      }
      // Set message
      $this->session->set_flashdata('post_updated', 'Votre post a été modifié');
      redirect('blog');
    }
  }

 ?>
