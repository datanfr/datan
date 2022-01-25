<?php
  class City_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_communes_population($slug){
      $sql = 'SELECT B.*
        FROM (
            SELECT A.*, c.commune, c.commune_nom, c.commune_slug,
            CASE
              	WHEN c.commune < 10 THEN CONCAT(LEFT(A.departement_code,2), "00", c.commune)
              	WHEN c.commune < 100 THEN CONCAT(LEFT(A.departement_code,2), "0", c.commune)
                  ELSE CONCAT(LEFT(A.departement_code,2), c.commune)
              END AS code_insee,
            CASE
              	WHEN c.commune < 10 THEN CONCAT(LEFT(A.departement_code,2), "00", c.commune)
              	WHEN c.commune < 100 THEN CONCAT(LEFT(A.departement_code,2), "0", c.commune)
                  ELSE CONCAT(LEFT(A.departement_code,2), c.commune)
              END AS code_insee_2
            FROM (
              SELECT d.slug, departement_code
              FROM departement d
              WHERE d.slug = ?
        	) A
            LEFT JOIN circos c ON A.departement_code = c.dpt
            GROUP BY c.commune_nom
        ) B
        LEFT JOIN cities_infos city ON B.code_insee_2 = city.insee
        ORDER BY city.pop2017 DESC
        LIMIT 30
      ';
      $query = $this->db->query($sql, $slug);

      return $query->result_array();
    }

    public function get_individual($ville, $departement){
      $sql = 'SELECT
            B.*,
            CASE
                WHEN char_length(i.postal) = 4 THEN CONCAT("0", i.postal)
                ELSE i.postal
            END AS code_postal,
            i.region AS codeRegion,
            city.pop2007,
            city.pop2012,
            city.pop2017,
            ((city.pop2017 - city.pop2007) / city.pop2007) * 100 AS evol10
        FROM
            (
                SELECT
                    A.*,
                    d.slug AS dpt_slug,
                    d.libelle_1,
                    d.libelle_2,
                    d.region as region_name,
                    CASE
                        WHEN A.code_insee_draft LIKE "0%" THEN SUBSTR(A.code_insee_draft, 2)
                        ELSE A.code_insee_draft
                    END AS code_insee
                FROM
                    (
                        SELECT
                            id,
                            dpt,
                            dpt_nom,
                            commune,
                            commune_nom,
                            circo,
                            canton,
                            canton_nom,
                            commune_slug,
                            c.commune AS insee_city,
                            CASE
                                WHEN c.commune < 10 THEN CONCAT(LEFT(dpt, 2), "00", c.commune)
                                WHEN c.commune < 100 THEN CONCAT(LEFT(dpt, 2), "0", c.commune)
                                ELSE CONCAT(LEFT(dpt, 2), c.commune)
                            END AS code_insee_draft
                        FROM
                            circos c
                        WHERE
                            commune_slug = ?
                    ) A
                    LEFT JOIN departement d ON A.dpt = d.departement_code
                WHERE
                    slug = ?
                GROUP BY
                    circo
            ) B
            LEFT JOIN cities_infos city ON B.code_insee_draft = city.insee
            LEFT JOIN insee i ON B.code_insee = i.insee
        GROUP BY
            B.circo
      ';
      $query = $this->db->query($sql, array($ville, $departement));

      return $query->result_array();
    }

    public function get_slug($city, $dpt){
      $sql = 'SELECT c.commune_slug, d.slug AS dpt_slug
        FROM circos c
        LEFT JOIN departement d ON d.departement_code = ?
        WHERE c.dpt = ? AND c.commune = ?
        LIMIT 1
      ';
      $query = $this->db->query($sql, array($dpt, $dpt, $city));

      return $query->row_array();

    }

    public function get_mps($departement, $circos, $legislature){
      $sql = 'SELECT d.*, d.circo AS electionCirco,
        d.libelle, d.libelleAbrev
        FROM deputes_all d
        WHERE d.dptSlug = ? AND d.circo IN ? AND d.legislature = ? AND d.dateFin IS NULL
      ';
      $query = $this->db->query($sql, array($departement, $circos, $legislature));

      return $query->result_array();
    }

    public function get_mps_dpt($departement, $deputes_commune, $legislature){
      if (empty($deputes_commune)) {
        $deputes_commune[] = "";
      }
      $sql = 'SELECT d.*, d.circo AS electionCirco,
        d.libelle, d.libelleAbrev
        FROM deputes_all d
        WHERE d.dptSlug = ? AND d.mpId NOT IN ? AND d.legislature = ? AND d.dateFin IS NULL
      ';
      $query = $this->db->query($sql, array($departement, $deputes_commune, $legislature));

      return $query->result_array();
    }

    public function get_random(){
      $this->db->select('commune_nom');
      $this->db->order_by('id', 'RANDOM');
      $query = $this->db->get_where('circos', array('commune_nom != ' => '0'), 1);

      return $query->row_array();
    }

    public function get_mayor($dpt, $insee, $commune){
      if ($dpt === "987") {
        $insee = $dpt . "" . $commune;
      }
      $where = array(
        'insee' => $insee
      );
      $this->db->select('nameFirst, nameLast, gender');
      $query = $this->db->get_where('cities_mayors', $where, 1);

      return $query->row_array();
    }

    public function get_results_2017_leg_2($dpt, $insee){
      $sql = 'SELECT *,
          ROUND(voix / exprimes * 100) AS pct
        FROM elect_2017_leg_results_communes res
        WHERE dpt = ? AND commune = ?
        ORDER BY voix DESC
      ';
      $query = $this->db->query($sql, array($dpt, $insee));

      return $query->result_array();
    }

    public function get_results_2017_pres_2($dpt, $insee){
      $where = array(
        'dpt' => $dpt,
        'commune' => $insee
      );
      $this->db->select('macron_n, macron_pct, lePen_n, lePen_pct');
      $query = $this->db->get('elect_2017_pres_2', $where, 1);

      $array = $query->row_array();
      $array['macron_n'] = number_format($array['macron_n'], 0, ',', ' ');
      $array['lePen_n'] = number_format($array['lePen_n'], 0, ',', ' ');

      return $array;
    }

    public function get_results_2019_europe($array){
      $dpt = $array["dpt"];
      $city = $array["insee_city"];
      if ($dpt == "987") {
        $city = 700 + $city;
      } elseif ($city < 10) {
        $city = "00".$city;
      } elseif ($city < 100) {
        $city = "0".$city;
      } else {
        $city = $city;
      }
      $sql = 'SELECT res.party, res.value, l.name AS listName, l.tete AS listTete, l.parti AS partiName
        FROM elect_2019_europe_clean res
        LEFT JOIN elect_2019_europe_listes l ON res.party = l.id
        WHERE res.dpt = ? AND res.commune = ?
        ORDER BY res.value DESC
        LIMIT 3
      ';
      $query = $this->db->query($sql, array($dpt, $city));

      return $query->result_array();
    }

    public function get_insee($region, $dpt, $city){
      /*
      if ($dpt < 10) {
        $new_dpt = "0".$dpt;
      } elseif($dpt < 100) {
        $new_dpt = "0".$dpt;
      } else {
        $new_dpt = $dpt;
      }
      */
      $new_dpt = $dpt; // BE CAREFUL HERE

      if ($dpt == "987") {
        $new_city = "0" . $city;
      } elseif ($dpt == "971") {
        $new_city = $city - 100;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($dpt == "972") {
        $new_city = $city - 200;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($dpt == "973") {
        $new_city = $city - 300;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($dpt == "974") {
        $new_city = $city - 400;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($dpt == "976") {
        $new_city = $city - 600;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($dpt == "988") {
        $new_city = $city - 800;
        if ($new_city < 10) {
          $new_city = "0".$new_city;
        }
      } elseif ($city < 10) {
        $new_city = "00".$city;
      } elseif ($city < 100) {
        $new_city = "0".$city;
      } else {
        $new_city = $city;
      }
      $old_city = $new_city;
      if ($dpt == "972") {
        $new_region = "002";
      } elseif ($dpt == "987") {
        $new_region = "000";
      } elseif ($dpt == "976") {
        $new_region = "006";
        $old_city = $city - 100;
      } elseif ($dpt == "988") {
        $new_region = "000";
      } elseif ($dpt == "977") {
        $new_region = "000";
      } elseif ($dpt == "986") {
        $new_region = "000";
      } elseif ($region < 10) {
        $new_region = "00".$region;
      } elseif ($region < 100) {
        $new_region = "0".$region;
      }
      $array["region"] = $new_region;
      $array["dpt"] = strtoupper($new_dpt);
      $array["city"] = $new_city;
      $array["insee"] = strtoupper($new_dpt)."".$new_city;
      //before 2019 Mayotte had different insee code
      $array["old_insee"] = strtoupper($new_dpt)."".$old_city;

      return $array;
    }

    public function get_adjacentes($insee, $limit = FALSE){
      $sql = 'SELECT a.* , c.*, d.*
        FROM cities_adjacentes a
        LEFT JOIN circos c ON a.adjacente = c.insee
        LEFT JOIN cities_infos ci ON ci.insee = c.insee
        LEFT JOIN departement d ON c.dpt = d.departement_code
        WHERE a.insee = ?
        GROUP BY c.commune_slug
        ORDER BY ci.pop2017 DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($sql, $insee);
      return $query->result_array();
    }

  }
