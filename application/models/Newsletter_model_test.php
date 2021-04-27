<?php
  class Newsletter_model_test extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_fields(){
      $this->db->order_by('name');
      $query = $this->db->get('fields');

      return $query->result_array();
    }

    public function get_field($field){
      $query = $this->db->get_where('fields', array('slug' => $field), 1);

      return $query->row_array();
    }

    public function get_active_fields(){
      $query = $this->db->query('
        SELECT f.id, f.name, f.slug, count(v.title) AS number
        FROM votes_datan v
        JOIN fields f ON f.id = v.category
        WHERE v.state = "published"
        GROUP BY f.name
      ');
      return $query->result_array();
    }


  }
?>
