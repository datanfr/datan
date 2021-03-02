<?php
  class Departement_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_departement($slug){
      $this->db->where('slug', $slug);
      $query = $this->db->get('departement');

      return $query->row_array();
    }

    public function get_all_departements(){
      $query = $this->db->query('
        SELECT *
        FROM departement
        ORDER BY departement_id ASC
      ');

      return $query->result_array();
    }

    public function get_communes_population($slug){
      $query = $this->db->query('
      SELECT B.*
      FROM (
          SELECT A.*, c.commune, c.commune_nom, c.commune_slug,
          CASE
            	WHEN c.commune < 10 THEN CONCAT(A.departement_code_2, "00", c.commune)
            	WHEN c.commune < 100 THEN CONCAT(A.departement_code_2, "0", c.commune)
                ELSE CONCAT(A.departement_code_2, c.commune)
            END AS code_insee,
          CASE
            	WHEN c.commune < 10 THEN CONCAT(A.departement_code_3, "00", c.commune)
            	WHEN c.commune < 100 THEN CONCAT(A.departement_code_3, "0", c.commune)
                ELSE CONCAT(A.departement_code_3, c.commune)
            END AS code_insee_2
          FROM (
            SELECT d.slug, departement_code,
            CASE
      		WHEN departement_code = "2b" THEN "02b"
              WHEN departement_code = "2a" THEN "02a"
              WHEN departement_code = "974" THEN "0ZD"
              WHEN departement_code = "976" THEN "0ZM"
              WHEN departement_code = "972" THEN "0ZB"
              WHEN departement_code = "973" THEN "0ZC"
              WHEN departement_code = "971" THEN "0ZA"
              WHEN departement_code = "988" THEN "0ZN"
              WHEN departement_code = "987" THEN "0ZP"
              WHEN departement_code = "986" THEN "0ZW"
              WHEN departement_code = "977" THEN "0ZX"
              ELSE d.departement_code
      	  END AS departement_code_2,
            CASE
      		WHEN d.departement_code = "974" THEN "97"
              WHEN d.departement_code = "972" THEN "97"
              WHEN d.departement_code = "973" THEN "97"
              WHEN d.departement_code = "971" THEN "97"
              WHEN d.departement_code = "986" THEN "98"
              WHEN d.departement_code = "977" THEN "97"
              ELSE d.departement_code
      	  END AS departement_code_3
            FROM departement d
            WHERE d.slug = "'.$slug.'"
      	) A
          LEFT JOIN circos c ON UPPER(A.departement_code_2) = UPPER(c.dpt_edited)
          GROUP BY c.commune_nom
      ) B
      LEFT JOIN cities_infos city ON B.code_insee_2 = city.insee
      ORDER BY city.pop2017 DESC
      LIMIT 30
      ');

      return $query->result_array();
    }

    public function get_commune_individual($ville, $departement){
      $query = $this->db->query('
      SELECT B.*,
      CASE WHEN char_length(i.postal) = 4 THEN CONCAT("0", i.postal) ELSE i.postal END AS code_postal, i.region AS codeRegion, city.pop2007, city.pop2012, city.pop2017,
      ((city.pop2017 - city.pop2007) / city.pop2007) * 100 AS evol10
      FROM
      (
      SELECT A.*, d.slug AS dpt_slug, d.libelle_1, d.libelle_2, d.region as region_name,
      CASE
		WHEN A.code_insee_draft LIKE "0%" THEN SUBSTR(A.code_insee_draft, 2)
        ELSE A.code_insee_draft
	  END AS code_insee
      FROM
      (
      SELECT id, dpt, dpt_nom, commune, commune_nom, circo, canton, canton_nom, commune_slug, c.commune AS insee_city,
      CASE
      	WHEN c.commune < 10 THEN CONCAT(c.dpt_edited, "00", c.commune)
      	WHEN c.commune < 100 THEN CONCAT(c.dpt_edited, "0", c.commune)
        WHEN dpt_edited = "0ZD" THEN CONCAT("97", c.commune)
        WHEN dpt_edited = "0ZM" THEN CONCAT("976", SUBSTR(c.commune, 2))
        WHEN dpt_edited = "0ZB" THEN CONCAT("97", c.commune)
        WHEN dpt_edited = "0ZC" THEN CONCAT("97", c.commune)
        WHEN dpt_edited = "0ZA" THEN CONCAT("97", c.commune)
        WHEN dpt_edited = "0ZN" THEN CONCAT("98", c.commune)
        WHEN dpt_edited = "0ZW" THEN CONCAT("986", c.commune)
		ELSE CONCAT(c.dpt_edited, c.commune)
      END AS code_insee_draft,
      CASE
		WHEN dpt_edited = "02B" THEN "2B"
        WHEN dpt_edited = "02A" THEN "2A"
        WHEN dpt_edited = "0ZD" THEN "974"
        WHEN dpt_edited = "0ZM" THEN "976"
        WHEN dpt_edited = "0ZB" THEN "972"
        WHEN dpt_edited = "0ZC" THEN "973"
        WHEN dpt_edited = "0ZA" THEN "971"
        WHEN dpt_edited = "0ZN" THEN "988"
        WHEN dpt_edited = "0ZP" THEN "987"
        WHEN dpt_edited = "0ZW" THEN "986"
        WHEN dpt_edited = "0ZX" THEN "977"
        ELSE dpt_edited
	    END AS dpt_edited
      FROM circos c
      WHERE commune_slug = "'.$ville.'"
      ) A
      LEFT JOIN departement d ON A.dpt_edited = d.departement_code
      WHERE slug = "'.$departement.'"
      GROUP BY circo
      ) B
      LEFT JOIN cities_infos city ON B.code_insee_draft = city.insee
      LEFT JOIN insee i ON B.code_insee = i.insee
      GROUP BY B.circo
      ');

      return $query->result_array();
    }

    public function get_commune_slug($city, $dpt, $dpt_edited){
      $query = $this->db->query('
        SELECT c.commune_slug, d.slug AS dpt_slug
        FROM circos c
        LEFT JOIN departement d ON d.departement_code = "'.$dpt.'"
        WHERE c.dpt_edited = "'.$dpt_edited.'" AND c.commune = "'.$city.'"
        LIMIT 1
      ');

      return $query->row_array();

    }

    public function get_deputes_commune($departement, $circo){
      $query = $this->db->query('
        SELECT d.*, d.circo AS electionCirco
        FROM deputes_all d
        WHERE d.dptSlug = "'.$departement.'" AND d.circo IN (' . implode(',', $circo) . ') AND d.legislature = 15 AND d.dateFin IS NULL
      ');

      return $query->result_array();
    }

    public function get_deputes_commune_dpt($departement, $deputes_commune){
      $query = $this->db->query("
      SELECT d.*, d.circo AS electionCirco
      FROM deputes_all d
      WHERE d.dptSlug = '".$departement."' AND d.mpId NOT IN ('" . implode("', '", $deputes_commune) . "') AND d.legislature = 15 AND d.dateFin IS NULL
      ");

      return $query->result_array();
    }

    public function get_deputes_dpt($dpt, $circo){
      $query = $this->db->query('
        SELECT m.electionRegion, m.electionDepartement, m.electionDepartementNumero, m.electionCirco, d.civ, d.prenom, d.nom, d.nameUrl, dpt.slug AS dpt_slug
        FROM mandat_principal m
        LEFT JOIN deputes d ON d.uid = m.idDepute
        LEFT JOIN departement dpt ON m.electionDepartementNumero = dpt.departement_code
        WHERE m.legislature = 15 AND m.dateFin IS NULL AND m.electionDepartementNumero = "'.$dpt.'" AND electionCirco != "'.$circo.'"
      ');
      return $query->result_array();
    }

    public function get_commune_random(){
      $rand = rand(1, 36467);
      $query = $this->db->query('
      SELECT commune_nom
      FROM circos
      WHERE id = "'.$rand.'"
      ');

      return $query->row_array();
    }

    public function get_city_mayor($dpt, $insee_city){
      $query = $this->db->query('
        SELECT nameFirst, nameLast, gender
        FROM cities_mayors
        WHERE dpt = "'.$dpt.'" AND insee = "'.$insee_city.'"
      ');

      return $query->row_array();
    }

    public function get_results_2017_pres_2($dpt, $insee_city){
      $query = $this->db->query('
        SELECT macron_n, macron_pct, lePen_n, lePen_pct
        FROM elect_2017_pres_2
        WHERE dpt = "'.$dpt.'" AND commune = "'.$insee_city.'"
      ');

      $array = $query->row_array();
      $array['macron_n'] = number_format($array['macron_n'], 0, ',', ' ');
      $array['lePen_n'] = number_format($array['lePen_n'], 0, ',', ' ');

      return $array;
    }

    public function get_results_2019_europe($array){
      $dpt = $array["dpt"];
      $city = $array["insee_city"];
      $insee = $array["code_insee_draft"];

      if (substr($dpt, 0, 1) == "Z") {
        $dpt = $dpt;
      } elseif ($dpt < 10) {
        $dpt = "0".$dpt;
      } elseif($dpt < 100) {
        $dpt = $dpt;
      }

      if ($dpt == "ZM") {
        $city = substr($insee, 2, 3);
      } elseif ($dpt == "ZP") {
        $city = 700 + $city;
      } elseif ($city < 10) {
        $city = "00".$city;
      } elseif ($city < 100) {
        $city = "0".$city;
      } else {
        $city = $city;
      }

      $query = $this->db->query('
        SELECT res.party, res.value, l.name AS listName, l.tete AS listTete, l.parti AS partiName
        FROM elect_2019_europe_clean res
        LEFT JOIN elect_2019_europe_listes l ON res.party = l.id
        WHERE res.dpt = "'.$dpt.'" AND res.commune = "'.$city.'"
        ORDER BY res.value DESC
        LIMIT 3
      ');

      return $query->result_array();
    }

    public function get_ville_insee($region, $dpt, $city){
      //echo $region;
      //echo $dpt;
      //echo $city;

      if ($dpt == "972") {
        $new_region = "002";
      } elseif ($dpt == "987") {
        $new_region = "000";
      } elseif ($dpt == "988") {
        $new_region = "000";
      } elseif ($region < 10) {
        $new_region = "00".$region;
      } elseif ($region < 100) {
        $new_region = "0".$region;
      }

      if ($dpt < 10) {
        $new_dpt = "0".$dpt;
      } elseif($dpt < 100) {
        $new_dpt = "0".$dpt;
      } else {
        $new_dpt = $dpt;
      }

      if ($dpt == "987") {
        $new_city = 700 + $city;
      } elseif ($city < 10) {
        $new_city = "00".$city;
      } elseif ($city < 100) {
        $new_city = "0".$city;
      } else {
        $new_city = $city;
      }

      $array["region"] = $new_region;
      $array["dpt"] = $new_dpt;
      $array["city"] = $new_city;
      $array["insee"] = $new_dpt."".$new_city;


      return $array;
    }
  }
?>
