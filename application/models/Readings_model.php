<?php
  class Readings_model extends CI_Model{
    public function __construct(){
    }

    public function get(){
      return $this->db->get('readings')->result_array();
    }
  }
?>
