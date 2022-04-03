<?php
  class User_model extends CI_Model{
    public function __construct() {
      //$this->db_admin = $this->load->database('admin', TRUE);
    }

    //REGISTER//
    public function register($enc_password){
      // User data array
      $data = array(
        'name' => $this->input->post('name'),
        'email' => $this->input->post('email'),
        'username' => $this->input->post('username'),
        'password' => $enc_password,
        'zipcode' => $this->input->post('zipcode')
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

    //CHECK USERNAME EXISTS//
    public function check_username_exists($username){
      $query = $this->db->get_where('users', array('username' => $username));

      if (empty($query->row_array())) {
        return true;
      } else {
        return false;
      }
    }

    //CHECK EMAIL EXISTS//
    public function check_email_exists($email){
      $query = $this->db->get_where('users', array('email' => $email));

      if (empty($query->row_array())) {
        return true;
      } else {
        return false;
      }
    }

    public function get_user($user_id){
      return $this->db->get_where('users', array('id' => $user_id))->row_array();
    }

  }
?>
