<?php
  class Category_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_categories(){
      $query = $this->db->query('
        SELECT *
        FROM categories
        ORDER BY name
      ');

      return $query->result_array();
    }

    public function get_category($slug){
      $query = $this->db->query('
        SELECT *
        FROM categories
        WHERE slug = "'.$slug.'"
      ');

      return $query->row_array();
    }

    public function get_active_categories(){
      $query = $this->db->query('
        SELECT p.category_id, c.name, c.slug
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.state = "published"
        GROUP BY c.name
      ');

      return $query->result_array();
    }


  }
?>
