<?php
  class Questions_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_questions_by_mp($mpId, $limit = FALSE){
      $this->db->where('q.mpId', $mpId);
      $this->db->order_by('q.datePublished', 'DESC');
      if ($limit) {
        $this->db->limit($limit);
      }
      $query = $this->db->get('questions q');
      return $query->result_array();
    }

  }
