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

    public function get_mp_parrainage($mpId, $year){
      $where = array(
        'mpId' => $mpId,
        'year' => $year
      );
      $query = $this->db->get_where('parrainages', $where, 1);
      return $query->row_array();
    }

    public function change_candidate_name($name){
      $array = array(
        'ARTHAUD Nathalie' => 'Nathalie Arthaud',
        'ASSELINEAU François' => 'François Asselineau',
        'DUPONT-AIGNAN Nicolas' => 'Nicolas Dupont-Aignan',
        'HIDALGO Anne' => 'Anne Hidalgo',
        'JADOT Yannick' => 'Yannick Jadot',
        'KAZIB Anasse' => 'Anasse Kazib',
        'KUZMANOVIC Georges' => 'Georges Kuzmanovic',
        'LASSALLE Jean' => 'Jean Lassalle',
        'LE PEN Marine' => 'Marine Le Pen',
        'MACRON Emmanuel' => 'Emmanuel Macron',
        'MÉLENCHON Jean-Luc' => 'Jean-Luc Mélenchon',
        'PÉCRESSE Valérie' => 'Valérie Pécresse',
        'POUTOU Philippe' => 'Philippe Poutou',
        'ROUSSEL Fabien' => 'Fabien Roussel',
        'THOUY Hélène' => 'Hélène Thouy',
        'ZEMMOUR Éric' => 'Éric Zemmour'
      );
      return $array[$name];
    }

  }
?>