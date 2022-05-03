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
        SELECT election,
          COUNT(mpId) AS candidatsN,
          SUM(case when secondRound = 1 then 1 else 0 end) as secondRoundN,
          SUM(case when elected = 1 then 1 else 0 end) as electedN
        FROM elect_deputes_candidats
        WHERE visible = 1
        GROUP BY election) c ON e.id = c.election
      ORDER BY e.dateYear DESC
      ');

      return $query->result_array();
    }

    public function get_election_infos($type){
      if ($type == "Régionales") {
        $info = "
        <p><b>Les élections régionales ont lieu tous les six ans</b>, en même temps que les élections départementales.</p>
        <p>Les élections régionales permettent d'<b>élire les conseillers régionaux</b> qui composent le conseil régional. Ce sont les conseillers régionaux qui décident et votent sur les affaires de la région.</p>
        <p><b>Les compétences des régions sont multiples</b>. Il s'agit par exemple du développement économique et social, de l'organisation du transport, de la construction des lycées, ou encore de l'organisation de la formation professionnelle.</p>
        <p>Les candidats se rassemblent sur des listes politiques, en fonction de leur parti d'appartenance. Le jour de l'élection, les électeurs votent donc pour une liste.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si aucune liste obtient plus de 50% des suffrages exprimés, un second tour est organisé avec uniquement les listes ayant récolté plus de 10% des voix.</p>
        <p><b>La liste arrivant en tête se voit attribuer 25% des sièges</b> du conseil régional. Pour les sièges restants, ils sont attribués à la proportionnelle entre toutes les listes ayant obtenu au moins 5% des voix.</p>
        ";
      } elseif ($type == "Départementales") {
        $info = "
        <p><b>Les élections départementales ont lieu tous les six ans</b>, en même temps que les élections régionales.</p>
        <p>Les élections départementales permettent d'<b>élire les conseillers départementaux</b> qui composent le conseil régional. Ce sont les conseillers départementaux qui décident et votent sur les affaires de la région. Avant 2013, les conseillers départementaux étaient appelés conseillers généraux.</p>
        <p><b>Les compétences des régions sont multiples</b>. La compétence la plus importante est sociale. Les conseils départementaux gèrent l'aide sociale à l'enfant, les politiques d'hebergement et d'insertion des personnes handicapées, ou encore la gestion de maisons de retraite. Les conseils départementaux ont également des compétences en matière d'éducation (construction et entretien de collèges), la voirie départementale, ou encore l'action culturelle et sportive.</p>
        <p>Les candidats doivent former un <b>binôme mixte</b> (une candidat femme et un candidat homme). Le jours de l'élection, les électeurs votent pour un binôme candidat.</p>
        <p><b>L'élection est organisée en deux tours</b>. Au premier tour, si un binôme obtient la majorité absolue des voix, alors il est automatiquement élu. Si aucun binôme ne l'emporte, un second tour est organisé.</p>
        <p>Les binômes ayant obtenu au moins 12,5% des voix des électeurs inscrits peuvent se présenter au second tour. <b>Au second tour, le binôme obtenant le plus de voix l'emporte</b>.
        ";
      } elseif ($type == "Législatives") {
        $info = "
        <p>Les élections législatives ont lieu <b>tous les cinq ans</b>, quelques semaines après l'élection présidentielle.</p>
        <p>Les élections législatives permettent <b>d'élire les 577 députés</b> qui composent l'Assemblée nationale.</p>
        <p><b>Comment les députés sont-ils élus ?</b> Lors du premier tour, un candidat peut être élu s'il obtient la majorité des suffrages exprimés (plus de 50%). Si aucun candidat ne remplit ces conditions, un second tour est organisé. Les candidats ayant reçu un nombre de suffrages supérieur à 12,5% des inscrits peuvent se présenter au second tour. Le vainqueur est le candidat qui arrive en tête au second tour.</p>
        <p>Les rôles du député sont multiples. Ils votent la loi, contrôlent le gouvernement, et représentent les intérêts de leur circonscription au niveau national. Pour plus d'informations, <a href='https://datan.fr/faq' target='_blank'>cliquez ici</a>.</p>
        ";
      } elseif ($type == "Présidentielle") {
        $info = "
        <p>L'élection présidentielle a lieu <b>tous les cinq ans</b>, quelques semaines avant les élections législatives. Elle permet d'élire le président de la République.</p>
        <p><b>Comment le président est-il élu ?</b> Pour être candidat, il faut avoir récolté le parrainage de 500 élus (maires, parlementaires, conseillers régionaux, etc.) L'élection se déroule sur deux tours. Lors du premier tour, un candidat peut être élu s'il obtient la majorité des suffrages exprimés (plus de 50%). Si aucun candidat n'y parvient, un second tour est organisé avec les deux candidats arrivés en tête au premier tour. Le vainqueur est le candidat qui arrive en tête du second tour.</p>
        <p>Les rôles du président de la République sont nombreux. Il ou elle est veille au respect de la Constitution, assure le fonctionnement des institutions, nomme le Premier ministre et préside le Conseil des ministres, promulgue les lois, etc. Pour plus d'informations, <a href='https://www.vie-publique.fr/infographie/23816-infographie-quel-est-le-role-du-president-de-la-republique' target='_blank' rel='nofollow noreferrer noopener'>cliquez ici</a>.</p>
        ";
      } else {
        $info = NULL;
      }

      return $info;
    }

    public function get_map_legend($id){
      if ($id == 1 /*regionales-2021*/) {
        $array = array(
          array("party" => "Parti socialiste (PS)", "color" => "#ff8080"),
          array("party" => "Divers gauche", "color" => "#ffc0c0"),
          array("party" => "Les Centristes", "color" => "#0FFFFF"),
          array("party" => "Divers droite", "color" => "#adc1fd"),
          array("party" => "Les Républicains", "color" => "#0066cc"),
          array("party" => "Régionalistes", "color" => "#FBCC33"),
        );
        return $array;
      }
      if ($id == 2 /*departementales-2021*/) {
        $array = array(
          array("party" => "Les Républicains (LR)", "color" => "#0066CC"),
          array("party" => "Union des démocrates et indépendants (UDI)", "color" => "#00FFFF"),
          array("party" => "Divers droite", "color" => "#ADC1FD"),
          array("party" => "La République en marche (LREM)", "color" => "#FFEB00"),
          array("party" => "Mouvement démocrate (MoDEM)", "color" => "#FF9900"),
          array("party" => "Divers", "color" => "#CCCCCC"),
          array("party" => "Divers gauche", "color" => "#FFC0C0"),
          array("party" => "Parti radical de gauche (PRG)", "color" => "#FFD1DC"),
          array("party" => "Parti socialiste (PS)", "color" => "#FF8080"),
        );
        return $array;
      }
    }

    public function get_candidate_election($mpId, $election){
      $where = array(
        'mpId' => $mpId,
        'election' => $election
      );

      $query = $this->db->get_where('elect_deputes_candidats', $where, 1);

      return $query->row_array();
    }

    public function get_candidate_elections($mpId){
      $where = array(
        'mpId' => $mpId
      );

      $this->db->join('elect_libelle', 'elect_libelle.id = elect_deputes_candidats.election', 'left');
      $this->db->order_by('dateYear', 'DESC');
      return $this->db->get_where('elect_deputes_candidats', $where)->result_array();
    }

    public function get_candidate_full($mpId, $election){
      $where = array(
        'mpId' => $mpId,
        'election' => $election
      );

      $query = $this->db->get_where('candidate_full', $where, 1);

      return $query->row_array();
    }


    public function get_all_candidate($election, $visible = FALSE, $state = FALSE){
      $where['election'] = $election;
      if ($visible != FALSE) {
        $where['visible'] = 1;
      }

      if ($state == 'second') {
        $where['secondRound'] = '1';
      }

      if ($state == 'elected') {
        $where['elected'] = '1';
      }

      $this->db->select('*, candidate_full.depute_libelle AS libelle, candidate_full.depute_libelleAbrev AS libelleAbrev');
      $this->db->select('DATE_FORMAT(modified_at, "%d/%m/%Y") AS modified_at');
      $this->db->order_by('nameLast', 'ASC');
      $this->db->order_by('nameFirst', 'ASC');
      $query = $this->db->get_where('candidate_full', $where);

      return $query->result_array();
    }

    public function count_candidats($id, $second = FALSE, $end = FALSE){
      $this->db->where('election', $id);
      $this->db->where('visible', 1);

      if ($second === TRUE) {
        $this->db->where('secondRound', 1);
      }

      if ($end === TRUE) {
        $this->db->where('elected', 1);
      }

      return $this->db->count_all_results('candidate_full');
    }

    public function get_all_districts($election){
      if ($election == 1/*regionales 2021*/) {
        $this->db->order_by('libelle', 'ASC');
        $query = $this->db->get('regions');
        return $query->result_array();
      } elseif (in_array($election, array(2, 4))/*departementales 2021 & législatives 2022*/) {
        $this->db->select('departement_code AS id, CONCAT(departement_code, " - ", departement_nom) AS libelle');
        $this->db->order_by('departement_code', 'ASC');
        $query = $this->db->get('departement');
        return $query->result_array();
      }
    }

    public function get_district($type, $district){
      if ($type == 'Régionales') {
        $result = $this->db->get_where('regions', array('id' => $district))->row_array();
        $array = array(
          'id' => $result['id'],
          'libelle' => $result['libelle']
        );
        return $array;
      }

      if ($type == 'Législatives') {
        $result = $this->db->get_where('departement', array('departement_code' => $district))->row_array();
        $array = array(
          'id' => $result['departement_code'],
          'libelle' => $result['departement_nom']
        );
        return $array;
      }

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
