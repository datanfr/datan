<?php
  class City_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_communes_by_dpt($slug, $max_population = FALSE){
      if ($max_population) {
        $this->db->where('city.pop2017 >', $max_population);
      }
      $this->db->where('d.slug', $slug);
      $this->db->join('departement d', 'd.departement_code = c.dpt');
      $this->db->join('cities_infos city', 'c.insee = city.insee');
      $this->db->group_by('c.commune_nom');
      $this->db->order_by('city.pop2017', 'DESC');
      $query = $this->db->get('circos c');
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
      $sql = 'SELECT d.*, d.circo AS electionCirco, dc.mailAn
        FROM deputes_all d
        LEFT JOIN deputes_contacts dc ON d.mpId = dc.mpId
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

    public function get_results_legislatives($insee, $year){
      $this->db->select('*, ROUND(voix / exprimes * 100) AS pct');
      $this->db->where('insee', $insee);
      $this->db->where('year', $year);
      $this->db->order_by('circo', 'DESC');
      $this->db->order_by('voix', 'DESC');
      return $this->db->get('elect_legislatives_cities')->result_array();
    }

    public function get_results_presidentielle($dpt, $city, $election){
      // Correction for Mayotte
      if ($dpt == 976 && $election == 2017) {
        $city = $city - 100;
      }
      // Correction for Polynésie française
      if ($dpt == 987 && $election == 2022) {
        $city = $city + 700;
      }
      $where = array(
        'dpt' => $dpt,
        'commune' => $city,
        'election' => $election
      );
      $this->db->order_by('share','DESC');
      $query = $this->db->get_where('elect_pres_2', $where);

      $array = $query->result_array();

      foreach ($array as $key => $value) {
        $array[$key]['candidate'] = $array[$key]['candidate'] == 'Macron' ? 'Emmanuel Macron' : 'Marine Le Pen';
      }

      return $array;
    }

    public function get_results_pres_edited($ville, $results17, $results22){
      foreach ($results17 as $key => $value) {
        $results17[$key]['gender'] = $value['candidate'] == 'Emmanuel Macron' ? 'h' : 'f';
        $results17[$value['candidate']] = $results17[$key];
        unset($results17[$key]);
      }
      foreach ($results22 as $key => $value) {
        $results22[$key]['gender'] = $value['candidate'] == 'Emmanuel Macron' ? 'h' : 'f';
        $results22[$value['candidate']] = $results22[$key];
        unset($results22[$key]);
      }

      $first = key($results22);

      $text = "<b>" . $results22[$first]["candidate"] . "</b> est arrivé";
      $text .= $results22[$first]["gender"] == "f" ? "e" : "";
      $text .= " en tête à l'élection présidentielle 2022 à " . $ville["commune_nom"] . ".";
      $text .= $results22[$first]["gender"] == "f" ? " Elle" : " Il";
      $text .= " a récolté " . round($results22[$first]["share"]) . "% des voix.";
      $text .= " C'est " . $this->functions_datan->more_less(round($results22[$first]["share"]), round($results17[$first]["share"])) . " que son score en 2017, qui était de " . round($results17[$first]["share"]) . "%.";;

      return $text;
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
