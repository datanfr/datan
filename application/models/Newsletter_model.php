<?php
  class Newsletter_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function create_newsletter(){
      $data = array(
        'email' => $this->input->post('email'),
        'general' => true,
      );
      return $this->db->insert('newsletter', $data);
    }
  }
?>
