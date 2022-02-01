<?php
  class Quizz_model extends CI_Model{
    public function __construct(){
      $this->load->database();
      $this->load->model('stats_model');
    }

    public function get_questions($state = NULL) {
      $this->db->join('fields', 'quizz.category = fields.id', 'left');
      $this->db->join('users u_created', 'u_created.id = quizz.created_by', 'left');
      $this->db->join('users u_modified', 'u_modified.id = quizz.modified_by', 'left');
      $this->db->select('quizz.*, fields.name AS category_name, u_created.name AS created_by_name, u_modified.name AS modified_by_name');

      if ($state) {
        $this->db->where('quizz.state', $state);
      }

      $query = $this->db->get('quizz');
      return $query->result_array();
    }

    public function get_question($id){
      $this->db->join('fields', 'quizz.category = fields.id', 'left');
      $this->db->select('quizz.*, fields.name AS category_name');
      $query = $this->db->get_where('quizz', array('quizz.id' => $id), 1);

      return $query->row_array();
    }

    public function delete($id) {
      $this->db->where('id', $id);
      $this->db->delete('quizz');
    }

    public function create($user_id){
      $data = array(
        'quizz' => $this->input->post('quizzNumero'),
        'voteNumero' => $this->input->post('voteNumero'),
        'legislature' => $this->input->post('legislature'),
        'title' => $this->input->post('title'),
        'for1' => $this->input->post('for1'),
        'for2' => $this->input->post('for2'),
        'for3' => $this->input->post('for3'),
        'against1' => $this->input->post('against1'),
        'against2' => $this->input->post('against2'),
        'against3' => $this->input->post('against3'),
        'category' => $this->input->post('category'),
        'created_by' => $user_id,
        'created_at' => date("Y-m-d H:i:s"),
        'state' => 'draft',
      );

      return $this->db->insert('quizz', $data);
    }

    public function modify($id, $user_id) {
      $data = array(
        'quizz' => $this->input->post('quizzNumero'),
        'voteNumero' => $this->input->post('voteNumero'),
        'legislature' => $this->input->post('legislature'),
        'title' => $this->input->post('title'),
        'for1' => $this->input->post('for1'),
        'for2' => $this->input->post('for2'),
        'for3' => $this->input->post('for3'),
        'against1' => $this->input->post('against1'),
        'against2' => $this->input->post('against2'),
        'against3' => $this->input->post('against3'),
        'category' => $this->input->post('category'),
        'created_by' => $user_id,
        'created_at' => date("Y-m-d H:i:s"),
        'state' => $this->input->post('state'),
        'modified_at' => date("Y-m-d H:i:s"),
        'modified_by' => $user_id
      );

      $this->db->set($data);
      $this->db->where('id', $id);
      $this->db->update('quizz');
    }

    public function get_questions_api($quizz){
      $select = array(
        'quizz.title AS voteTitre',
        'vi.dateScrutin AS dateScrutin',
        'date_format(vi.dateScrutin, "%d %M %Y") AS dateScrutinFR',
        'quizz.voteNumero',
        'quizz.legislature',
        'fields.name AS category_name',
        'fields.slug AS category_slug',
        'vi.sortCode AS sortCode',
        'vi.voteId AS vote_id',
        'quizz.for1',
        'quizz.for2',
        'quizz.for3',
        'quizz.against1',
        'quizz.against2',
        'quizz.against3'
      );
      $this->db->select($select);
      $this->db->join('fields', 'quizz.category = fields.id', 'left');
      $this->db->join('votes_info vi', 'quizz.legislature = vi.legislature AND quizz.voteNumero = vi.voteNumero', 'left');
      $query = $this->db->get_where('quizz', array('quizz' => $quizz, 'state' => 'published'));

      $questions = $query->result_array();

      $arguments = array(
        array('type' => 'POUR', 'name' => 'for1'),
        array('type' => 'POUR', 'name' => 'for2'),
        array('type' => 'POUR', 'name' => 'for3'),
        array('type' => 'CONTRE', 'name' => 'against1'),
        array('type' => 'CONTRE', 'name' => 'against2'),
        array('type' => 'CONTRE', 'name' => 'against3')
      );

      foreach ($questions as $key => $value) {
        $array = array();
        foreach ($arguments as $argument) {
          array_push($array, array("opinion" => $argument['type'], "texte" => $value[$argument['name']]));
        }
        $questions[$key]['arguments'] = $array;

        foreach ($arguments as $argument) {
          unset($questions[$key][$argument['name']]);
        }

      }

      return $questions;
    }

  }
?>
