<?php
  class Post_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_posts($slug, $user, $category){
      if (empty($slug)) {
        $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name
          FROM posts p
          LEFT JOIN categories c ON p.category_id = c.id
        ';
        if ($user != "admin" && $user != "writer") {
          $sql .= 'WHERE state = "published"';
        }
        $sql .= 'ORDER BY created_at DESC';
        $query = $this->db->query($sql);

        return $query->result_array();
      } else {
        $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name
          FROM posts p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.slug = ? AND c.slug = ?
        ';
        if ($user != "admin" && $user != "writer") {
          $sql .= ' AND p.state = "published"';
        }
        $query = $this->db->query($sql, array($slug, $category));

        return $query->row_array();
      }
    }

    public function get_post_edit($slug){
      $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id, p.image_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ?
        LIMIT 1
      ';
      $query = $this->db->query($sql, $slug);

      return $query->row_array();
    }

    public function get_last_posts($limit = FALSE){
      $this->db->select('p.id, DATE_FORMAT(p.created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.slug, c.slug AS category_slug, c.name AS category_name, p.image_name');
      $this->db->join('categories c', 'p.category_id = c.id', 'left');
      $this->db->where('p.state', 'published');
      $this->db->order_by('p.created_at', 'DESC');
      if ($limit){
        $this->db->limit($limit);
      }
      
      return $this->db->get('posts p')->result_array();
    }

    public function create_post(){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);
      
      // Gestion de l'image
      $image_name = '';
      if($_FILES['post_image']['name']){
        // Sanitiser le nom du fichier et ajouter un timestamp
        $filename = pathinfo($_FILES['post_image']['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename); // Supprimer les caractères spéciaux
        $extension = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $new_filename = $filename . '_' . time() . '.' . $extension;
        
        $_FILES['post_image']['name'] = $new_filename;
        $config['upload_path'] = './assets/imgs/posts/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size'] = '2048'; // 2MB
        $config['file_name'] = $new_filename; // Utiliser le nouveau nom de fichier
        
        $this->load->library('upload', $config);
        
        if(!$this->upload->do_upload('post_image')){
          return ['error' => $this->upload->display_errors()];
        }
      }
      
      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'body' => $this->input->post('body'),
        'category_id' => $this->input->post('category_id'),
        'user_id' => $this->session->userdata('user_id'),
        'state' => 'draft',
        'image_name' => $new_filename
      );
      return $this->db->insert('posts', $data);
    }

    public function delete_post($id){
      $this->db->where('id', $id);
      $this->db->delete('posts');
      return true;
    }

    public function update_post(){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);
      
      // Récupérer les données actuelles du post pour l'image
      $this->db->where('id', $this->input->post('id'));
      $current_post = $this->db->get('posts')->row_array();
      
      // Gestion de l'image
      $image_name = $current_post['image_name']; // Garder l'image existante par défaut
      
      if($_FILES['post_image']['name']){
        // Sanitiser le nom du fichier et ajouter un timestamp
        $filename = pathinfo($_FILES['post_image']['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename); // Supprimer les caractères spéciaux
        $extension = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $new_filename = $filename . '_' . time() . '.' . $extension;
        
        $_FILES['post_image']['name'] = $new_filename;
        
        $config['upload_path'] = './assets/imgs/posts/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size'] = '2048'; // 2MB
        $config['file_name'] = $new_filename; // Utiliser le nouveau nom de fichier
        
        $this->load->library('upload', $config);
        
        if(!$this->upload->do_upload('post_image')){
          $this->session->set_flashdata('error', 'Erreur lors du téléchargement de l\'image : ' . $this->upload->display_errors());
          return false;
        }
        
        // Si l'upload a réussi, supprimer l'ancienne image et mettre à jour le nom
        // Supprimer l'ancienne image si elle existe
        if(!empty($current_post['image_name']) && file_exists('./assets/imgs/posts/'.$current_post['image_name'])){
          unlink('./assets/imgs/posts/'.$current_post['image_name']);
        }
        
        $upload_data = $this->upload->data();
        $image_name = $upload_data['file_name'];
      }
      
      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'body' => $this->input->post('body'),
        'category_id' => $this->input->post('category_id'),
        'modified_at' => date('Y-m-d H:i:s'),
        'state' => $this->input->post('state'),
        'image_name' => $image_name
      );
      $this->db->where('id', $this->input->post('id'));
      return $this->db->update('posts', $data);
    }

    public function get_posts_by_category($category){
      $sql = ' SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.user_id, c.name AS category_name, c.slug AS category_slug, p.slug, p.state, p.image_name
        FROM posts p
        JOIN categories c ON p.category_id = c.id AND c.slug = ?
        WHERE p.state = "published"
        ORDER BY created_at DESC
      ';
      $query = $this->db->query($sql, $category);

      return $query->result_array();
    }

    private function skip_accents( $str, $charset='utf-8' ) {
      $str = htmlentities( $str, ENT_NOQUOTES, $charset );
      $str = preg_replace( '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
      $str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str );
      $str = preg_replace( '#&[^;]+;#', '', $str );
      return $str;
    }
  }
?>
