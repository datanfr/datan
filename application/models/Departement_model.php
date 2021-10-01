<?php
  class Departement_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_departement($slug){
      $this->db->where('slug', $slug);
      $query = $this->db->get('departement');

      return $query->row_array();
    }

    public function get_all_departements(){
      $this->db->order_by('departement_code');
      $query = $this->db->get('departement');

      return $query->result_array();
    }

  }
