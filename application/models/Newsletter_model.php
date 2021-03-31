<?php
  class Post_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function create_newsletter(){
      $slug = url_title($this->input->post('title'));
      $slug = mb_strtolower($slug);
      $slug = $this->skip_accents($slug);
      $data = array(
        'email' => $this->input->post('email'),
        'general' => true,
      );

      return $this->db->insert('newsletter', $data);
    }
  }
?>
