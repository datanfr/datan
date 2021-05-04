<?php
  class Jobs_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_stats_famSocPro($famSocPro, $legislature){
      $where = array(
        'legislature' => $legislature,
        'famSocPro' => $famSocPro
      );
      $this->db->select('famSocPro, count(mpId) AS n, round(count(mpId) / 577 * 100, 2) AS pct');
      $this->db->from('deputes_all');
      $this->db->where($where);
      $this->db->group_by('famSocPro');
      $this->db->limit(1);
      return $this->db->get()->row_array();
    }


  }
?>
