<?php
  class Users extends CI_Controller{

    // REGISTER //
    public function register(){
      $this->password_model->security_admin();
      $data['title'] = 'Créez votre compte';
      $data['title_meta'] = "Datan: S'inscrire";
      $data['no_offset'] = TRUE;

      $this->form_validation->set_rules('name', 'Name', 'required');
      $this->form_validation->set_rules('username', 'Username', 'required|callback_check_username_exists');
      $this->form_validation->set_rules('email', 'Email', 'required|callback_check_email_exists');
      $this->form_validation->set_rules('password', 'Password', 'required');
      $this->form_validation->set_rules('password2', 'Confirm Password', 'matches[password]');

      if ($this->form_validation->run() === FALSE) {

        $this->load->view('templates/header_no_navbar', $data);
        $this->load->view('users/register', $data);
        $this->load->view('templates/footer_no_navbar');
      } else {
        // Encrypt password
        $enc_password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        $this->user_model->register($enc_password);
        // Set message
        $this->session->set_flashdata('user_registered', 'Vous êtes maintenant inscrit et pouvez vous connecter');
        redirect('/login');
      }
    }

    // CHECK IF USERNAME EXISTS //
    function check_username_exists($username){
      $this->form_validation->set_message('check_username_exists', "Ce pseudo est déjà pris. Merci d'en choisir un autre.");

      if ($this->user_model->check_username_exists($username)) {
        return true;
      } else {
        return false;
      }
    }

    // CHECK IF EMAILS EXISTS //
    public function check_email_exists($email){
      $this->form_validation->set_message('check_email_exists', "Cet email est déjà pris. Merci d'en choisir un autre.");

      if ($this->user_model->check_email_exists($email)) {
        return true;
      } else {
        return false;
      }
    }


    // LOGIN //
    public function login(){
      if ($this->session->userdata('logged_in')) {
      redirect();
      } else {
        $data['title'] = 'Connectez-vous à votre compte';
        $data['title_meta'] = 'Datan : Se connecter';
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
          $this->load->view('templates/header_no_navbar', $data);
          $this->load->view('users/login', $data);
          $this->load->view('templates/footer_no_navbar');
        } else {
          // Get the Username
          $username = $this->input->post('username');
          // Get the password
          $password = $this->input->post('password');
          // Login user
          $user = $this->user_model->login($username);

          if (password_verify($password, $user->password)) {
            // Create session
            $user_data = array(
              'user_id' => $user->id,
              'username' => $username,
              'logged_in' => true,
              'type' => $user->type
            );

            $this->session->set_userdata($user_data);
            //$this->session->set_flashdata('user_loggedin', 'Vous êtes maintenant connecté');
            redirect('/admin');
          } else {
            //$this->session->set_flashdata('login_failed', 'La connexion a échoué');
            redirect('/login');
          }
        }
      }
    }

    // LOGOUT //
    public function logout(){
      // Unset user data
      $this->session->unset_userdata('logged_in');
      $this->session->unset_userdata('user_id');
      $this->session->unset_userdata('username');
      $this->session->unset_userdata('type');

      // Set message
      redirect();
    }

  }
?>
