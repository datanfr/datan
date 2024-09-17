<?php
  class Dossiers_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_dossiers(){
      $this->db->select('d.*');
      //$this->db->join('dossiers_votes dv', 'dv.dossierId = d.dossierId');
      $query = $this->db->get_where('dossiers d');
      return $query->result_array();
    }

  }
