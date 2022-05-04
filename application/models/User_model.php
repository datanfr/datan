<?php
  class User_model extends CI_Model{
    public function __construct() {
      //$this->db_admin = $this->load->database('admin', TRUE);
    }

    //REGISTER//
    public function register($enc_password, $type){
      // User data array
      $data = array(
        'name' => $this->input->post('name'),
        'email' => $this->input->post('email'),
        'username' => $this->input->post('username'),
        'password' => $enc_password,
        'zipcode' => $this->input->post('zipcode'),
        'type' => $type
      );
      return $this->db->insert('users', $data);
    }

    public function modify_personal_data($id){
      $data = array(
        'email' => $this->input->post('email'),
        'username' => $this->input->post('pseudo'),
        'name' => $this->input->post('name'),
        'zipcode' => $this->input->post('zipcode')
      );
      $this->db->where('id', $id);
      $this->db->update('users', $data);
    }

    public function update_password($id, $password){
      $this->db->where('id', $id);
      $this->db->update('users', array('password' => $password));
    }

    public function login($username){
      $this->db->where('username', $username);
      $this->db->join('users_mp', 'users.id = users_mp.user', 'left');
      $result_user = $this->db->get('users');

      if ($result_user->num_rows() == 1) {
        return $result_user->row(0);
      } else {
        $this->db->where('email', $username);
        $result_email = $this->db->get('users');
        if ($result_email->num_rows() == 1) {
          return $result_email->row(0);
        } else {
          return false;
        }
      }
    }

    public function check_username_exists($username){
      $query = $this->db->get_where('users', array('username' => $username));
      return empty($query->row_array()) ? false : true;
    }

    public function check_email_exists($email){
      $query = $this->db->get_where('users', array('email' => $email));
      return empty($query->row_array()) ? false : true;
    }

    public function check_mp_email($email){
      $query = $this->db->get_where('deputes_contacts', array('mailAn' => $email));
      return empty($query->row_array()) ? false : true;
    }

    public function check_mp_has_account($mpId){
      $query = $this->db->get_where('users_mp', array('mpId' => $mpId));
      return $query->row_array() ? true : false;
    }

    public function get_user($user_id){
      return $this->db->get_where('users', array('id' => $user_id))->row_array();
    }

    public function delete_account($id){
      $this->db->where('id', $id);
      $this->db->delete('users');
    }

    public function delete_account_mp($id){
      $this->db->where('user', $id);
      $this->db->delete('users_mp');
    }

    public function inset_mp_demand_link($mpId, $token){
      $data = array(
        'mpId' => $mpId,
        'token' => $token
      );
      $this->db->insert('users_mp_link', $data);
    }

    public function get_mpId_by_token($token){
      $this->db->where('token', $token);
      $this->db->where('created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)');
      $query = $this->db->get('users_mp_link', 1);
      return $query->row() ? $query->row()->mpId : NULL;
    }

    public function insert_users_mp($mpId, $user){
      $data = array(
        'mpId' => $mpId,
        'user' => $user
      );
      return $this->db->insert('users_mp', $data);
    }

  }
?>
