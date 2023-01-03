<?php
  class Legislature_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_legislature($legislature){
      return $this->db->get_where('legislature', array('legislatureNumber' => $legislature))->row_array();
    }

    public function get_legislatures($legislatures){
      $this->db->where_in('legislatureNumber', $legislatures);
      return $this->db->get('legislature')->result_array();
    }

  }
?>
