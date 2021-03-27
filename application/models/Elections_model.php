<?php
  class Elections_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_candidate($mpId, $election){
      $whereQuery = array(
        'mpId' => $mpId,
        'election' => $election
      );

      $query = $this->db->get_where('elect_deputes_candidats', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_candidate_full($mpId, $election){
      $whereQuery = array(
        'mpId' => $mpId,
        'election' => $election
      );

      $query = $this->db->get_where('candidate_full', $whereQuery, 1);

      return $query->row_array();
    }


    public function get_all_candidate($election){
      $whereQuery = array(
        'election' => $election
      );

      $query = $this->db->get_where('candidate_full', $whereQuery);

      return $query->result_array();
    }

  }
