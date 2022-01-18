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

  }
?>
