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
      
      // 1. Older exposes table (before 17e legislature)
      $where = array(
        'legislature' => $legislature,
        'voteNumero' => $voteNumero
      );
      $expose = $this->db->get_where('exposes', $where)->row_array();
      if (!empty($expose)) {
        return $expose;
      }

      // 2. New table (amendements_ia) (after 17e legislature)
      $this->db->select('va.legislature, va.voteNumero, aia.resume_ia, aia.justification_ia');
      $this->db->from('votes_amendments va');
      $this->db->join('amendements_ia aia', 'aia.amendementId = va.amendmentId', 'left');
      $this->db->where('va.legislature', $legislature);
      $this->db->where('va.voteNumero', $voteNumero);
      $this->db->limit(1);

      $fallback = $this->db->get()->row_array();
      
      if (empty($fallback)) {
        return array();
      }

      $resume = isset($fallback['resume_ia']) && trim($fallback['resume_ia']) !== ''
        ? trim($fallback['resume_ia'])
        : null;
      $justification = isset($fallback['justification_ia']) && trim($fallback['justification_ia']) !== ''
        ? trim($fallback['justification_ia'])
        : null;

      if ($resume === null && $justification === null) {
        $fallback['exposeSummaryPublished'] = null;
      } elseif ($resume !== null && $justification !== null) {
        $fallback['exposeSummaryPublished'] = '<p>' . $resume . '</p><p>' . $justification . '</p>';
      } elseif ($resume !== null) {
        $fallback['exposeSummaryPublished'] = '<p>' . $resume . '</p>';
      } else {
        $fallback['exposeSummaryPublished'] = '<p>' . $justification . '</p>';
      }

      unset($fallback['resume_ia'], $fallback['justification_ia']);

      return $fallback;
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
