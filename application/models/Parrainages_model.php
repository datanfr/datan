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
      $this->db->join('deputes_last', 'deputes_last.mpId = parrainages.mpId');
      $this->db->order_by('parrainages.nameLast ASC, parrainages.nameFirst ASC');
      $query = $this->db->get('parrainages');
      return $query->result_array();
    }



  }
?>
