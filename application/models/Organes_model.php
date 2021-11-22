<?php
  class Organes_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_organe($id){
      $this->db->select('*, date_format(dateDebut, "%d %M %Y") as dateDebutFR, date_format(dateFin, "%d %M %Y") as dateFinFR ');
      return $this->db->get_where('organes', array('uid' => $id), 1)->row_array();
    }

  }
