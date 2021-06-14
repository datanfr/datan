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

    public function create($user_id){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);

      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'category' => $this->input->post('category'),
        'text' => $this->input->post('article'),
        'created_at' => date("Y-m-d H:i:s"),
        'state' => 'draft',
        'created_by' => $user_id
      );

      return $this->db->insert('faq_posts', $data);
    }

    public function modify($id, $user_id) {
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);

      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'category' => $this->input->post('category'),
        'text' => $this->input->post('article'),
        'state' => $this->input->post('state'),
        'modified_at' => date("Y-m-d H:i:s"),
        'modified_by' => $user_id
      );

      $this->db->set($data);
      $this->db->where('id', $id);
      $this->db->update('faq_posts');
    }

    public function get_categories(){
      return $this->db->get('faq_categories')->result_array();
    }

  }
?>
