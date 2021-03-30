<?php
  class Elections_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_election($slug){
      $whereQuery = array(
        'slug' => $slug
      );

      $query = $this->db->get_where('elect_libelle', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_election_all(){
      $query = $this->db->query('
      SELECT *, date_format(e.dateFirstRound, "%d %M") as dateFirstRoundFr, date_format(e.dateSecondRound, "%d %M") as dateSecondRoundFr, candidatsN
      FROM elect_libelle e
      LEFT JOIN (
        SELECT election, COUNT(mpId) AS candidatsN
        FROM elect_deputes_candidats
        GROUP BY election) c ON e.id = c.election
      ORDER BY e.dateYear DESC
      ');

      return $query->result_array();
    }

    public function get_election_color(){
      $array = array(
        "Régionales" => '#097AB8',
        "Départementales" => "#C14330"
      );

      return $array;
    }

    public function get_election_by_id($id){
      $whereQuery = array(
        'id' => $id
      );

      $query = $this->db->get_where('elect_libelle', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_candidate($mpId, $election){
      $whereQuery = array(
        'mpId' => $mpId,
        'election' => $election
      );

      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'elect_deputes_candidats.district = regions.id', 'left');
        $this->db->select('*, libelle AS regionLibelle');
      }

      $query = $this->db->get_where('elect_deputes_candidats', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_candidate_full($mpId, $election){
      $whereQuery = array(
        'mpId' => $mpId,
        'election' => $election
      );

      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'candidate_full.district = regions.id', 'left');
        $this->db->select('*, libelle AS regionLibelle, id AS regionId');
      }

      $query = $this->db->get_where('candidate_full', $whereQuery, 1);

      return $query->row_array();
    }


    public function get_all_candidate($election){
      $whereQuery = array(
        'election' => $election
      );

      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'candidate_full.district = regions.id', 'left');
        $this->db->select('*, libelle AS regionLibelle');
      }

      $this->db->select('DATE_FORMAT(modified_at, "%m/%d/%Y") AS modified_at');
      $query = $this->db->get_where('candidate_full', $whereQuery);

      return $query->result_array();
    }

    public function get_all_regions(){
      $this->db->order_by('libelle', 'ASC');
      $query = $this->db->get('regions');

      return $query->result_array();
    }

  }
