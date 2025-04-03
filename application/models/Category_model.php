<?php
  class Category_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_categories(){
      $this->db->order_by('name');
      $query = $this->db->get('categories');

      return $query->result_array();
    }

    public function get_category($slug){
      $query = $this->db->get_where('categories', array('slug' => $slug), 1);

      $result = $query->row_array();

      $legend = [
        "datan" => [
            "subtitle" => "Les nouvelles du projet Datan",
            "description_meta" => "Vous voulez tout savoir sur le projet Datan ? Découvrez nos dernières nouvelles dans ce blog."
        ],
        "actualite-politique" => [
            "subtitle" => "Nos analyses sur l'actualité de l'Assemblée nationale",
            "description_meta" => "Découvrez nos analyses approfondies sur l'actualité des députés et de l'Assemblée nationale."
        ],
        "rapports" => [
            "subtitle" => "Nos études sur l'Assemblée nationale et les députés",
            "description_meta" => "Retrouvez nos études et analyses sur le travail parlementaire."
        ]
      ];

      return array_merge($result, $legend[$result['slug']]);
    }

    public function get_active_categories(){
      $query = $this->db->query('
        SELECT p.category_id, c.name, c.slug
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.state = "published"
        GROUP BY c.name
      ');

      return $query->result_array();
    }


  }
?>
