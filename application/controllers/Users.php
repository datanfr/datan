<?php
  class Users extends CI_Controller {

    public function __construct(){
      parent::__construct();
      $this->load->model('captcha_model');
    }

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
        redirect('login');
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
        echo $this->session->userdata('attempt');
        //$this->session->set_tempdata('penalty', false);
        //$this->session->set_userdata('attempt', 0);

        // If penalty
        if ($this->session->tempdata('penalty') === true) {
          $this->load->view('templates/header_no_navbar', $data);
          $this->load->view('users/blocked');
          //$this->load->view('templates/footer_no_navbar');
        } else {

          if ($this->form_validation->run() === FALSE) {

            if ($this->session->userdata('attempt') >= 3) {
              $data['captcha'] = TRUE;
              $data['captchaImg'] = $this->captcha_model->generateCaptcha();
            } else {
              $data['captcha'] = FALSE;
            }

            $this->load->view('templates/header_no_navbar', $data);
            $this->load->view('users/login', $data);
            $this->load->view('templates/footer_no_navbar');

          } elseif (!($this->session->flashdata('login_failed'))) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $user = $this->user_model->login($username);

            // Test captcha
            if ($this->input->post('captcha') !== null) {
              echo "yes1";
              $inputCaptcha = $this->input->post('captcha');
              $sessCaptcha = $this->session->userdata('captchaCode');
              if (!($inputCaptcha === $sessCaptcha)) {
                $attempt = $this->session->userdata('attempt');
                $attempt++;
                $this->session->set_userdata('attempt', $attempt);
                if ($this->session->userdata('attempt') >= 5) {
                  $this->session->set_tempdata('penalty', true, 300);
                }
                $this->session->set_flashdata("login_failed", "Le code captcha est erroné. Veuillez réessayer.");
                redirect('login');
              }
            }

            // Test password
            if (password_verify($password, $user->password)) {
              // Create session
              $user_data = array(
                'user_id' => $user->id,
                'username' => $username,
                'logged_in' => true,
                'type' => $user->type
              );

              $this->session->set_userdata($user_data);
              $this->session->set_userdata('attempt', 0);
              redirect('admin');
            } else {
              $attempt = $this->session->userdata('attempt');
              $attempt++;
              $this->session->set_userdata('attempt', $attempt);
              if ($this->session->userdata('attempt') >= 5) {
                $this->session->set_tempdata('penalty', true, 300);
              }
              $this->session->set_flashdata("login_failed", "L'identifiant ou le mot de passe sont erronés. Veuillez réessayer.");
              redirect('login');
            }


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
      $this->session->unset_userdata('attempt');

      // Set message
      redirect();
    }

  }
?>
