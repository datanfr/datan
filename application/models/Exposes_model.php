<?php
  class Exposes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_all_exposes(){
      $this->db->order_by('exposeSummaryPublished');
      return $this->db->get('exposes')->result_array();
    }

    public function get_n_done(){
      $this->db->where('exposeSummaryPublished IS NOT NULL', NULL, FALSE);
      return $this->db->count_all_results('exposes'); 
    }

    public function get_n_pending(){
      $this->db->where('exposeSummaryPublished IS NULL', null, false);
      return $this->db->count_all_results('exposes');
    }

    public function get_expose($id){
      return $this->db->get_where('exposes', array('id' => $id))->row_array();
    }

    public function get_expose_by_vote($legislature, $voteNumero){
      $where = array(
        'legislature' => $legislature,
        'voteNumero' => $voteNumero
      );
      return $this->db->get_where('exposes', $where)->row_array();
    }

    public function modify($legislature, $voteNumero){
      $data = array(
        'exposeSummaryPublished' => $this->input->post('exposeSummary'),
      );

      $this->db->set($data);
      $this->db->where('legislature', $legislature);
      $this->db->where('voteNumero', $voteNumero);
      $this->db->update('exposes');
    }

  }
