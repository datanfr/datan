<?php
  class Questions_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_questions_by_mp($mpId, $limit = FALSE){
      $this->db->select("
        q.*,
        date_format(q.datePublished, '%d %M %Y') as datePublishedFR,
        CASE 
          WHEN q.type = 'QE' THEN 'Question écrite'
          WHEN q.type = 'QOSD' THEN 'Question orale'
          WHEN q.type = 'QG' THEN 'Question au gouvernement'
          ELSE 'Type inconnu'
        END as type_libelle
      ");
      $this->db->where('q.mpId', $mpId);
      $this->db->order_by('q.datePublished', 'DESC');
      if ($limit) {
        $this->db->limit($limit);
      }
      $query = $this->db->get('questions q');
      return $query->result_array();
    }

  }
