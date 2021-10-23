<?php
  class Organes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_organe($id){
      return $this->db->get_where('organes', array('uid' => $id), 1)->row_array();
    }

  }
