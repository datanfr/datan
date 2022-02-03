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
      $this->db->select('p.*, d.dptSlug, d.nameUrl');
      $this->db->join('deputes_last d', 'd.mpId = p.mpId', 'left');
      $this->db->order_by('p.nameLast ASC, p.nameFirst ASC');
      $query = $this->db->get('parrainages p');
      return $query->result_array();
    }

    public function get_parrainage($id){
      $query = $this->db->get_where('parrainages p', array('id' => $id));
      return $query->row_array();
    }

    public function modify($id) {
      $data = array(
        'mpId' => $this->input->post('mpId'),
      );

      $this->db->set($data);
      $this->db->where('id', $id);
      $this->db->update('parrainages');
    }

  }
?>
