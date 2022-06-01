<?php
  class City_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_communes_population($slug, $limit = FALSE){
      $sql = 'SELECT d.*, c.*
        FROM circos c
        LEFT JOIN departement d ON d.departement_code = c.dpt
        LEFT JOIN cities_infos city ON c.insee = city.insee
        WHERE d.slug = ?
        GROUP BY c.commune_nom
        ORDER BY city.pop2017 DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($sql, $slug);

      return $query->result_array();
    }

    public function get_individual($ville, $departement){
      $sql = 'SELECT c.*, d.slug AS dpt_slug, d.libelle_1, d.libelle_2, d.region as region_name,
        i.region AS codeRegion,
        city.pop2007, city.pop2012, city.pop2017,
        ((city.pop2017 - city.pop2007) / city.pop2007) * 100 AS evol10,
        CASE
          WHEN char_length(i.postal) = 4 THEN CONCAT("0", i.postal)
          ELSE i.postal
        END AS code_postal
      FROM circos c
      LEFT JOIN departement d ON c.dpt = d.departement_code
      LEFT JOIN cities_infos city ON c.insee = city.insee
      LEFT JOIN insee i ON c.insee = i.insee
      WHERE c.commune_slug = ? AND d.slug = ?
      GROUP BY c.circo';
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

    public function get_results_2017_leg_2($insee){
      $sql = 'SELECT *, ROUND(voix / exprimes * 100) AS pct
        FROM elect_2017_leg_results_communes res
        WHERE insee = ?
        ORDER BY circo DESC, voix DESC
      ';
      $query = $this->db->query($sql, $insee);

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
      $city = $array["commune"];
      if ($dpt == "987") {
        $city = 700 + $city;
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

    public function get_format_interieurGouv($city){
      $array = [];

      // 1. Departement
      switch ($city['dpt']) {
        case '2a':
          $array['dpt'] = '2A';
          break;

        case '2b':
          $array['dpt'] = '2B';
          break;

        default:
          $array['dpt'] = $city['dpt'];
          break;
      }

      if ($array['dpt'] < 100) {
        $array['dpt'] = '0'.$array['dpt'];
      } else {
        $array['dpt'] = $array['dpt'];
      }

      // 2. City code
      if ($city['dpt'] == 976) {
        $array['commune'] = $city['commune'] - 100;
      } else {
        $array['commune'] = $city['commune'];
      }

      // 2. City code Européennes
      if ($city['dpt'] == 987) {
        $array['commune_europeennes'] = $city['commune'] + 700;
      } else {
        $array['commune_europeennes'] = $city['commune'];
      }

      // 3. REGION
      if (is_null($city['codeRegion'])) {
        $array['region'] = '000';
      } elseif ($city['codeRegion'] < 10) {
        $array['region'] = '00'.$city['codeRegion'];
      } else {
        $array['region'] = '0'.$city['codeRegion'];
      }

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

    public function get_mps_city($legislature){
      $sql = 'SELECT dl.mpId, c.commune_nom AS communeNom, i.postal AS codePostal
        FROM deputes_last dl
        LEFT JOIN circos c ON c.dpt = dl.departementCode AND c.circo = dl.circo
        LEFT JOIN insee i ON i.insee = c.insee
        WHERE dl.legislature = ?
        GROUP BY c.commune_nom, dl.mpId';

      $query = $this->db->query($sql, $legislature);
      $results = $query->result_array();

      foreach ($results as $key => $value) {
        if ($value['communeNom'] == 'Paris' ) {
          $results[$key]['codePostal'] = '75000/75001/75002/75003/75004/75005/75006/75007/75008/75009/75010/75011/75012/75013/75014/75015/75016/75017/75018/75019/75020';
        }
      }

      return $results;
    }

  }
