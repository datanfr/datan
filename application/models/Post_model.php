<?php
  class Post_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_posts($slug, $user, $category){
      if (empty($slug)) {
        // QUERY FOR ALL POSTS WHEN ADMIN
        if ($user == "admin" || $user == "writer") {
          $query = $this->db->query('
            SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY created_at DESC
          ');
        } else {
          // QUERY FOR ALL POSTS WHEN NORMAL USER
          $query = $this->db->query('
            SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id, date(p.created_at) AS created_at_sitemap, date(p.modified_at) AS modified_at_sitemap
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE state = "published"
            ORDER BY created_at DESC
          ');
        }
        return $query->result_array();
      } else {
        if ($user == "admin" || $user == "writer") {
          // QUERY FOR INDIVIDUAL POST WHEN ADMIN/WRITER
          $query = $this->db->query('
            SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id AND c.slug = "'.$category.'"
            WHERE p.slug = "'.$slug.'"
            ORDER BY created_at DESC
          ');
        } else {
          // QUERY FOR INDIVIDUAL POST WHEN NORMAL USER
          $query = $this->db->query('
            SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, c.name AS category_name, c.slug AS category_slug, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = "'.$slug.'" AND p.state = "published" AND c.slug = "'.$category.'"
            ORDER BY created_at DESC
          ');
        }
        return $query->row_array();
      }
    }

    public function get_last_posts(){
      $query = $this->db->query('
        SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.slug, c.slug AS category_slug, c.name AS category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.state = "published"
        ORDER BY created_at DESC
        LIMIT 5
      ');
      return $query->result_array();
    }

    public function create_post(){
      $slug = url_title($this->input->post('title'));
      $slug = mb_strtolower($slug);
      $slug = $this->skip_accents($slug);
      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'body' => $this->input->post('body'),
        'category_id' => $this->input->post('category_id'),
        'user_id' => $this->session->userdata('user_id'),
        'state' => 'draft'
      );

      return $this->db->insert('posts', $data);
    }

    public function delete_post($id){
      $this->db->where('id', $id);
      $this->db->delete('posts');
      return true;
    }

    public function update_post(){      
      $slug = url_title($this->input->post('title'));
      $slug = mb_strtolower($slug);
      $slug = $this->skip_accents($slug);
      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'body' => $this->input->post('body'),
        'category_id' => $this->input->post('category_id'),
        'modified_at' => date('Y-m-d H:i:s'),
        'state' => $this->input->post('state')
      );
      $this->db->where('id', $this->input->post('id'));
      return $this->db->update('posts', $data);
    }

    public function get_categories(){
      $this->db->order_by('name');
      $query = $this->db->get('categories');
      return $query->result_array();
    }

    public function get_posts_by_category($category){
      $query = $this->db->query('
        SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.user_id, c.name AS category_name, c.slug AS category_slug, p.slug, p.state
        FROM posts p
        JOIN categories c ON p.category_id = c.id AND c.slug = "'.$category.'"
        WHERE p.state = "published"
        ORDER BY created_at DESC
      ');
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
