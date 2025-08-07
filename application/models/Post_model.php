<?php
  class Post_model extends CI_Model{
    public function __construct(){
      $this->load->database();
      $this->load->library('blog');
    }

    public function get_posts($slug, $user, $category_slug){
      if (empty($slug)) {
        $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name
          FROM posts p
        ';
        if ($user != "admin" && $user != "writer") {
          $sql .= 'WHERE state = "published"';
        }
        $sql .= 'ORDER BY created_at DESC';
        $query = $this->db->query($sql);
        $posts = $query->result_array();

        foreach ($posts as &$post) {
          $cat = $this->blog->get_category_by_id($post['category_id']);
          $post['category_name'] = $cat['name'] ?? '';
          $post['category_slug'] = $cat['slug'] ?? '';
        }

        return $posts;
      } else {
        $category = $this->blog->get_category_by_slug($category_slug);
        $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name
          FROM posts p
          WHERE p.slug = ? AND p.category_id = ?
        ';
        if ($user != "admin" && $user != "writer") {
          $sql .= ' AND p.state = "published"';
        }
        $query = $this->db->query($sql, array($slug, $category['id']));
        $post = $query->row_array();

        if ($post) {
          $cat = $this->blog->get_category_by_id($post['category_id']);

          if (!$cat || $cat['slug'] !== $category_slug) {
              return null;
          }

          $post['category_name'] = $cat['name'];
          $post['category_slug'] = $cat['slug'];
      }

        return $post;
      }
    }

    public function get_post_by_id($id){
      return $this->db->get_where('posts', array('id' => $id))->row_array();
    }

    public function get_post_edit($slug){
      $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id, p.image_name
        FROM posts p
        WHERE p.slug = ?
        LIMIT 1
      ';
      $query = $this->db->query($sql, $slug);
      $post = $query->row_array();

      if ($post) {
        $cat = $this->blog->get_category_by_id($post['category_id']);
        $post['category_name'] = $cat['name'] ?? 'Inconnue';
        $post['category_slug'] = $cat['slug'] ?? '';
    }

      return $post;
    }

    public function get_last_posts($limit = FALSE){
      $this->db->select('p.id, p.category_id, DATE_FORMAT(p.created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.slug, p.state, p.image_name');
      $this->db->where('p.state', 'published');
      $this->db->order_by('p.created_at', 'DESC');
      if ($limit){
        $this->db->limit($limit);
      }
      $posts = $this->db->get('posts p')->result_array();

      foreach ($posts as &$post) {
        $category = $this->blog->get_category_by_id($post['category_id']);
        $post['category_name'] = $category['name'] ?? 'Inconnue';
        $post['category_slug'] = $category['slug'] ?? 'inconnue';
      }

      return $posts;
    }

    public function create_post(){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);
      
      // Gestion de l'image
      $image_name = '';
      // Check that both PNG and WEBP are uploaded
      if (!$_FILES['post_image_png']['name']) {
        return ['error' => 'Un fichier PNG est requis.'];
      } else {
        // --- Handle PNG upload ---

        // Sanitiser le nom du fichier et ajouter un timestamp
        $filename = pathinfo($_FILES['post_image_png']['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename); // Supprimer les caractères spéciaux
        $extension_png = pathinfo($_FILES['post_image_png']['name'], PATHINFO_EXTENSION);
        $base_name = $filename . '_' . time(); // Final base name for both files
        $new_png_name = $base_name . '-source.png';

        $_FILES['post_image_png']['name'] = $new_png_name;
        $config_png['upload_path'] = './assets/imgs/posts/';
        $config_png['allowed_types'] = 'png';
        $config_png['max_size'] = '2048'; // 2MB
        $config_png['file_name'] = $new_png_name; // Utiliser le nouveau nom de fichier

        $this->load->library('upload', $config_png);

        if(!$this->upload->do_upload('post_image_png')){
          return ['error' => $this->upload->display_errors() . " (image png) "];
        }

        // Path to original image
        $original_path = './assets/imgs/posts/' . $new_png_name;

        // Chargement de la bibliothèque image_service
        $this->load->library('image_service');

        // Conversion de l'image originale en WebP
        $this->image_service->convert_to_webp(
          $original_path,
          './assets/imgs/posts/webp/' . $base_name . '.webp'
        );

        // Définir les tailles à générer
        $target_widths = [360, 730];

        foreach($target_widths as $width){
          $resized_png_path = './assets/imgs/posts/' . $base_name . '-' . $width . '.png';
          $resized_webp_path = './assets/imgs/posts/webp/' . $base_name . '-' . $width . '.webp';

          // Remissionner en PNG
          $this->image_service->resize_image(
            $original_path,
            $resized_png_path,
            $width
          );

          // Convertir en WebP
          $this->image_service->convert_to_webp(
            $resized_png_path,
            $resized_webp_path
          );

        }

      }
      
      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'body' => $this->input->post('body'),
        'category_id' => $this->input->post('category_id'),
        'user_id' => $this->session->userdata('user_id'),
        'state' => 'draft',
        'image_name' => $base_name
      );
      return $this->db->insert('posts', $data);
    }

    public function delete_post($id, $image_name){
      // Delete post
      $this->db->where('id', $id);
      $this->db->delete('posts');

      // Delete image

      if($image_name){
        $png_path = './assets/imgs/posts/' . $image_name . '.png';
        $webp_path = './assets/imgs/posts/webp/' . $image_name . '.webp';

        if (file_exists($png_path)) {
          unlink($png_path);
        }

        if (file_exists($webp_path)) {
          unlink($webp_path);
        }
      }
    }

    public function update_post(){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);
      
      // Récupérer les données actuelles du post pour l'image
      $this->db->where('id', $this->input->post('id'));
      $current_post = $this->db->get('posts')->row_array();
      
      // Gestion de l'image
      $image_name = $current_post['image_name']; // Garder l'image existante par défaut
      if ($_FILES['post_image_png']['name']) {

        // Validate PNG file extension
        $extension_png = pathinfo($_FILES['post_image_png']['name'], PATHINFO_EXTENSION);
        if (strtolower($extension_png) !== 'png') {
            return [
                'slug' => $slug,
                'error' => 'Le fichier n\' pas l\'extension correcte. Veuillez télécharger un fichier PNG valide.'
            ];
        }

        // --- Handle PNG upload ---

        // Sanitiser le nom du fichier et ajouter un timestamp
        $filename = pathinfo($_FILES['post_image_png']['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename); // Supprimer les caractères spéciaux
        $extension_png = pathinfo($_FILES['post_image_png']['name'], PATHINFO_EXTENSION);
        $base_name = $filename . '_' . time(); // Final base name for both files
        $new_png_name = $base_name . '.png';

        $_FILES['post_image_png']['name'] = $new_png_name;
        $config_png['upload_path'] = './assets/imgs/posts/';
        $config_png['allowed_types'] = 'png';
        $config_png['max_size'] = '2048'; // 2MB
        $config_png['file_name'] = $new_png_name; // Utiliser le nouveau nom de fichier

        $this->load->library('upload', $config_png);

        if(!$this->upload->do_upload('post_image_png')){
          return [
            'slug' => $slug,
            'error' => $this->upload->display_errors() . ' (image png) '
          ];
          return false;
        }

        if(!empty($current_post['image_name']) && file_exists('./assets/imgs/posts/'.$current_post['image_name'].'.png')){
          unlink('./assets/imgs/posts/'.$current_post['image_name'].'.png');
        }

        // --- Handle WEBP upload ---
        $this->load->library('image_service');
        // Conversion du PNG en WebP
        $this->image_service->convert_to_webp_with('./assets/imgs/posts/' . $new_png_name, './assets/imgs/posts/webp/' . $base_name . '.webp');

        if(!empty($current_post['image_name']) && file_exists('./assets/imgs/posts/webp/' . $current_post['image_name'] . '.webp')){
          unlink('./assets/imgs/posts/webp/' . $current_post['image_name'] . '.webp');
        }
        
        $image_name = $base_name;

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
      $sql = ' SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.user_id, p.slug, p.state, p.image_name
        FROM posts p
        WHERE p.state = "published" AND p.category_id = ?
        ORDER BY created_at DESC
      ';
      $query = $this->db->query($sql, $category['id']);
      $posts = $query->result_array();

      foreach ($posts as &$post) {
        $post['category_name'] = $category['name'];
        $post['category_slug'] = $category['slug'];
      }

      return $posts;
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
