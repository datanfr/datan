<?php
  class Elections_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_election($slug){
      $where = array(
        'slug' => $slug
      );
      $this->db->select('*, date_format(dateFirstRound, "%d %M %Y") as dateFirstRoundFr, date_format(dateSecondRound, "%d %M %Y") as dateSecondRoundFr');

      $query = $this->db->get_where('elect_libelle', $where, 1);

      return $query->row_array();
    }

    public function get_election_by_id($id){
      $where = array(
        'id' => $id
      );

      $query = $this->db->get_where('elect_libelle', $where, 1);

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
      } elseif ($type == "Départementales") {
        $info = "
        <p><b>Les élections départementales ont lieu tous les six ans</b>, en même temps que les élections régionales.</p>
        <p>Les élections départementales permettent d'<b>élire les conseillers départementaux</b> qui composent le conseil régional. Ce sont les conseillers départementaux qui décident et votent sur les affaires de la région. Avant 2013, les conseillers départementaux étaient appelés conseillers généraux.</p>
        <p><b>Les compétences des régions sont multiples</b>. La compétence la plus importante est sociale. Les conseils départementaux gèrent l'aide sociale à l'enfant, les politiques d'hebergement et d'insertion des personnes handicapées, ou encore la gestion de maisons de retraite. Les conseils départementaux ont également des compétences en matière d'éducation (construction et entretien de collèges), la voirie départementale, ou encore l'action culturelle et sportive. Pour plus d'information, <a href='https://www.vie-publique.fr/fiches/19620-quelles-sont-les-competences-exercees-par-les-departements' target='_blank' rel='nofollow noreferrer noopener'>cliquez ici</a>.
        <p>Les candidats doivent former un <b>binôme mixte</b> (une candidat femme et un candidat homme). Le jours de l'élection, les électeurs votent pour un binôme candidat.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si un binôme obtient la majorité absolue des voix, alors il est automatiquement élu. Si aucun binôme ne l'emporte, un second tour est organisé.</p>
        <p>Les binômes ayant obtenu au moins 12,5% des voix des électeurs inscrits peuvent se présenter au second tour. <b>Au second tour, le binôme obtenant le plus de voix l'emporte</b>.
        ";
      } else {
        $info = NULL;
      }

      return $info;
    }

    public function get_map_legend($id){
      if ($id == 1 /*regionales-2021*/) {
        $array = array(
          array("party" => "Parti socialiste (PS)", "color" => "#F78080"),
          array("party" => "Les Centrists (LC)", "color" => "#54FEFF"),
          array("party" => "Les Républicains (LR)", "color" => "#1A66CC"),
          array("party" => "Divers droite", "color" => "#ADC1FD"),
          array("party" => "Régionalistes", "color" => "#FBCC33"),
          array("party" => "Divers gauche", "color" => "#fac0c0")
        );
      }
      if ($id == 2 /*regionales-2021*/) {
        $array = array(
          array("party" => "Les Républicains (LR)", "color" => "#0066CC"),
          array("party" => "Parti socialistes (PS)", "color" => "#FF8080"),
          array("party" => "Union des démocrates et indépendants (UDI)", "color" => "#00FFFF"),
          array("party" => "Mouvement démocrate (MoDEM)", "color" => "#FF9900"),
          array("party" => "Divers droite", "color" => "#ADC1FD"),
          array("party" => "Parti radical de gauche (PRG)", "color" => "#FFD1DC"),
          array("party" => "Parti communiste français (PCF)", "color" => "#DD0000"),
          array("party" => "Sans étiquette", "color" => "#DDDDDD")
        );
      }

      return $array;
    }

    public function get_candidate($mpId, $election){
      $where = array(
        'mpId' => $mpId,
        'election' => $election
      );

      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'elect_deputes_candidats.district = regions.id', 'left');
        $this->db->select('*, libelle AS regionLibelle');
      }

      $query = $this->db->get_where('elect_deputes_candidats', $where, 1);

      return $query->row_array();
    }

    public function get_candidate_full($mpId, $election){
      $where = array(
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

      $query = $this->db->get_where('candidate_full', $where, 1);

      return $query->row_array();
    }


    public function get_all_candidate($election, $visible = FALSE, $state = FALSE){
      if ($visible == FALSE) {
        $where = array(
          'election' => $election
        );
      } else {
        $where = array(
          'election' => $election,
          'visible' => 1
        );
      }

      if ($state == 'second') {
        $where['secondRound'] = '1';
      }

      if ($election == 1/*regionales 2021*/) {
        $this->db->join('regions', 'candidate_full.district = regions.id', 'left');
        $this->db->select('*, regions.libelle AS districtLibelle, regions.id AS districtId');
        $this->db->select('regions.libelle AS cardCenter'); // Central information on card
      } elseif ($election == 2/*départementales 2021*/) {
        $this->db->join('departement', 'candidate_full.district = departement.departement_code', 'left');
        $this->db->select('*, departement.departement_nom AS districtLibelle, departement.departement_code AS districtId');
        $this->db->select('CONCAT(departement.departement_nom, " (", departement.departement_code, ")") AS cardCenter'); // Central information on card
      }

      $this->db->select('candidate_full.depute_libelle AS groupLibelle, candidate_full.depute_libelleAbrev AS groupLibelleAbrev');
      $this->db->select('DATE_FORMAT(modified_at, "%d/%m/%Y") AS modified_at');
      $this->db->order_by('nameLast', 'ASC');
      $this->db->order_by('nameFirst', 'ASC');
      $query = $this->db->get_where('candidate_full', $where);

      return $query->result_array();
    }

    public function count_candidats($id, $second = FALSE, $end = FALSE){
      $this->db->where('election', $id);

      if ($second === TRUE) {
        $this->db->where('secondRound', 1);
      }

      return $this->db->count_all_results('candidate_full');
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

    public function get_state($second, $elected){
      if ($second === "1" & $elected == NULL) {
        return "second";
      } elseif ($second === "0" & $elected == NULL) {
        return "lost";
      } elseif ($elected === "1") {
        return "elected";
      } elseif ($elected === "0") {
        return "lost";
      }
    }

  }
