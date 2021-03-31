<?php
  class Elections_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_election($slug){
      $whereQuery = array(
        'slug' => $slug
      );
      $this->db->select('*, date_format(dateFirstRound, "%d %M %Y") as dateFirstRoundFr, date_format(dateSecondRound, "%d %M %Y") as dateSecondRoundFr');

      $query = $this->db->get_where('elect_libelle', $whereQuery, 1);

      return $query->row_array();
    }

    public function get_election_by_id($id){
      $whereQuery = array(
        'id' => $id
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
        WHERE visible = 1
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

    public function get_election_infos($type){
      if ($type == "Régionales") {
        $info = "
        <p><b>Les élections régionales ont lieu tous les six ans</b>, en même temps que les élections départementales.</p>
        <p>Les élections régionales permettent d'<b>élire les conseillers régionaux</b> qui composent le conseil régional. Ce sont les conseillers régionaux qui décident et votent sur les affaires de la région.</p>
        <p><b>Les compétences des régions sont multiples</b>. Il s'agit par exemple du développement économique et social, de l'organisation du transport, de la construction des lycées, ou encore de l'organisation de la formation professionnelle. Pour plus d'information, <a href='https://www.prefectures-regions.gouv.fr/Le-savez-vous/Quelles-sont-les-competences-d-une-region' target='_blank' rel='nofollow noreferrer noopener'>cliquez ici</a>.
        <p>Les candidats se rassemblent sur des listes politiques, en fonction de leur parti d'appartenance. Le jour de l'élection, les électeurs votent donc pour une liste.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si aucune liste obtient plus de 50% des suffrages exprimés, un second tour est organisé avec uniquement les listes ayant récolté plus de 10% des voix.</p>
        <p><b>La liste arrivant en tête se voit attribuer 25% des sièges</b> du conseil régional. Pour les sièges restants, ils sont attribués à la proportionnelle entre toutes les listes ayant obtenu au moins 5% des voix.</p>
        ";
      } else {
        $info = NULL;
      }

      return $info;
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
        $this->db->select('*, libelle AS districtLibelle, id AS districtId');
      } elseif ($election == 2/*departementales 2021*/) {
        $this->db->join('departement', 'candidate_full.district = departement.departement_code', 'left');
        $this->db->select('*, departement.departement_nom AS districtLibelle, departement.departement_code AS districtId');
      }

      $query = $this->db->get_where('candidate_full', $whereQuery, 1);

      return $query->row_array();
    }


    public function get_all_candidate($election, $visible = FALSE){
      if ($visible == FALSE) {
        $whereQuery = array(
          'election' => $election
        );
      } else {
        $whereQuery = array(
          'election' => $election,
          'visible' => 1
        );
      }


      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'candidate_full.district = regions.id', 'left');
        $this->db->select('*, regions.libelle AS districtLibelle, regions.id AS districtId');
      } elseif ($election == 2/*départementales 2021*/) {
        $this->db->join('departement', 'candidate_full.district = departement.departement_code', 'left');
        $this->db->select('*, departement.departement_nom AS districtLibelle, departement.departement_code AS districtId');
      }

      $this->db->select('DATE_FORMAT(modified_at, "%d/%m/%Y") AS modified_at');
      $query = $this->db->get_where('candidate_full', $whereQuery);

      return $query->result_array();
    }

    public function get_all_districts($election){
      if ($election == 1/*regionales 2021*/) {
        $this->db->order_by('libelle', 'ASC');
        $query = $this->db->get('regions');
      } elseif ($election == 2 /*departementales 2021*/) {
        $this->db->select('departement_code AS id, CONCAT(departement_code, " - ", departement_nom) AS libelle');
        $this->db->order_by('departement_code', 'ASC');
        $query = $this->db->get('departement');
      }

      return $query->result_array();
    }

  }
