<?php
  class Faq_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }


    public function get_articles(){
      $this->db->join('faq_categories fc', 'fc.id = fp.category', 'left');
      $this->db->join('users u_created', 'u_created.id = fp.created_by', 'left');
      $this->db->join('users u_modified', 'u_modified.id = fp.modified_by', 'left');
      $this->db->select('fp.*, fc.name AS category_name, u_created.name AS created_by_name, u_modified.name AS modified_by_name');
      $query = $this->db->get('faq_posts fp');

      return $query->result_array();
    }

    public function get_article($id){
      $this->db->join('faq_categories fc', 'fc.id = fp.category', 'left');
      $this->db->select('fp.*, fc.name AS category_name');
      $query = $this->db->get_where('faq_posts fp', array('fp.id' => $id), 1);

      return $query->row_array();
    }

    public function delete($id) {
      $this->db->where('id', $id);
      $this->db->delete('faq_posts');
    }

  }
?>
