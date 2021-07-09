<?php
  class Faq_model extends CI_Model{
    public function __construct(){
      $this->load->database();
      $this->load->model('stats_model');
    }


    public function get_articles($id = NULL, $state = NULL) {
      $this->db->join('faq_categories fc', 'fc.id = fp.category', 'left');
      $this->db->join('users u_created', 'u_created.id = fp.created_by', 'left');
      $this->db->join('users u_modified', 'u_modified.id = fp.modified_by', 'left');
      $this->db->select('fp.*, fc.name AS category_name, u_created.name AS created_by_name, u_modified.name AS modified_by_name');
      if ($id) {
        $this->db->where('fp.category', $id);
      }
      if ($state) {
        $this->db->where('fp.state', $state);
      }

      $query = $this->db->get('faq_posts fp');
      return $query->result_array();
    }

    public function get_additional_articles($category, $array){
      if ($category['slug'] == 'deputes') {
        // Question Quel est l'âge moyen des députés ?
        $age = $this->stats_model->get_age_mean(legislature_current());
        $question = array(
          "title" => "Quel est l'âge moyen des députés ?",
          "text" => "
            <p>L'âge moyen des députés est de ".round($age)." ans.</p>
            <p>Découvrez sur Datan le <a href='" . base_url() ."statistiques/deputes-age' target='_blank'>classement des députés en fonction de leur âge</a>.
          "
        );
        array_push($array, $question);
      }
      return $array;
    }

    public function get_article($id){
      $this->db->join('faq_categories fc', 'fc.id = fp.category', 'left');
      $this->db->select('fp.*, fc.name AS category_name');
      $query = $this->db->get_where('faq_posts fp', array('fp.id' => $id), 1);

      return $query->row_array();
    }

    public function delete($id) {
      $this->db->where('id', $id);
      $this->db->delete('faq_posts');
    }

    public function create($user_id){
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);

      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'category' => $this->input->post('category'),
        'text' => $this->input->post('article'),
        'created_at' => date("Y-m-d H:i:s"),
        'state' => 'draft',
        'created_by' => $user_id
      );

      return $this->db->insert('faq_posts', $data);
    }

    public function modify($id, $user_id) {
      $slug = convert_accented_characters($this->input->post('title'));
      $slug = url_title($slug, 'dash', TRUE);

      $data = array(
        'title' => $this->input->post('title'),
        'slug' => $slug,
        'category' => $this->input->post('category'),
        'text' => $this->input->post('article'),
        'state' => $this->input->post('state'),
        'modified_at' => date("Y-m-d H:i:s"),
        'modified_by' => $user_id
      );

      $this->db->set($data);
      $this->db->where('id', $id);
      $this->db->update('faq_posts');
    }

    public function get_categories(){
      return $this->db->get('faq_categories')->result_array();
    }

    public function get_faq_schema($data){
      $schema = [
        "@context" => "http://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => array()
      ];
      foreach ($data as $category) {
        foreach ($category as $key => $question) {
          $array = array(
            "@type" => "Question",
            "name" => $question['title'],
            "acceptedAnswer" => array(
              "@type" => "Answer",
              "text" => $question['text']
            )
          );
          array_push($schema["mainEntity"], $array);
        }
      }
      return $schema;
    }

  }
?>
