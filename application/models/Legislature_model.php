<?php
  class Legislature_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_legislature($legislature){
      return $this->db->get_where('legislature', array('legislatureNumber' => $legislature))->row_array();
    }

  }
?>
