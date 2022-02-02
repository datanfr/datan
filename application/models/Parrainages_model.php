<?php
  class Parrainages_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_parrainages($year, $onlyMps = FALSE){
      $this->db->where('year', $year);
      if ($onlyMps) {
        $this->db->where_in('mandat', array('député', 'députée'));
      }
      $query = $this->db->get('parrainages');
      return $query->result_array();
    }



  }
?>
