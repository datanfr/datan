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
      // Insert user
      return $this->db->insert('users', $data);
    }

    //LOGIN//
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

  }
?>
