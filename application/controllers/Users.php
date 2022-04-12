<?php
  class Users extends CI_Controller {

    public function __construct(){
      parent::__construct();
      $this->load->model('captcha_model');
    }

    public function register(){
      $this->password_model->security();
      $data['title'] = 'Créez votre compte';
      $data['title_meta'] = "Datan: S'inscrire";
      $data['no_offset'] = TRUE;
      $data['seoNoFollow'] = TRUE;

      $this->form_validation->set_rules('name', 'Name', 'required');
      $this->form_validation->set_rules('username', 'Pseudo', 'required|callback_check_username_exists');
      $this->form_validation->set_rules('email', 'Email', 'required|callback_check_email_exists|valid_email');
      $this->form_validation->set_rules('password', 'Mot de passe', 'required');
      $this->form_validation->set_rules('password2', 'Confirmation du mot de passe', 'matches[password]');
      $this->form_validation->set_rules('zipcode', 'Code postal', 'required|is_natural');

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

    function check_username_exists($username){
      $this->form_validation->set_message('check_username_exists', "Ce pseudo est déjà pris. Merci d'en choisir un autre.");

      if ($this->user_model->check_username_exists($username)) {
        return true;
      } else {
        return false;
      }
    }

    public function check_email_exists($email){
      $this->form_validation->set_message('check_email_exists', "Cet email est déjà pris. Merci d'en choisir un autre.");

      if ($this->user_model->check_email_exists($email)) {
        return true;
      } else {
        return false;
      }
    }

    public function password_lost_request(){
      if ($this->session->userdata('logged_in')) {
        redirect();
      } else {
        $data['title'] = 'Réinitialisez votre mot de passe';
        $data['title_meta'] = 'Mot de passe oublié | Datan';

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
          $this->load->view('templates/header_no_navbar', $data);
          $this->load->view('users/password-lost', $data);
          $this->load->view('templates/footer_no_navbar');
        } else {
          $email = $this->input->post('email');
          $noEmail = $this->user_model->check_email_exists($email);
          if (!$noEmail) {
            // Get user infos
            $user = $this->user_model->get_user_by_email($email);
            // Create token in password_resets table
            $token = bin2hex(random_bytes(50));
            $this->user_model->create_token_password_lost($email, $token);
            // Send an email
            $templateId = 3862755; /* Template password_forgot */
            $variables = array(
              'name' => $user['name'],
              'token' => $token
            );
            sendMail($email, 'Changez votre mot de passe Datan', NULL, TRUE, $templateId, $variables);
            $this->session->set_flashdata('success', 'true');
          } else {
            $this->session->set_flashdata('failure', 'true');
          }
          redirect(base_url().'password');
        }
      }
    }

    public function password_lost_change($token){
      if ($this->session->userdata('logged_in')) {
        redirect();
      } else {
        $user = $this->user_model->get_token_password_lost($token);

        if (empty($user)) {
          show_404();
        } else {
          $data['title'] = 'Créez un nouveau mot de passe';
          $data['title_meta'] = 'Créez un nouveau mot de passe | Datan';

          // ICI ! 

          $this->load->view('templates/header_no_navbar', $data);
          $this->load->view('users/password-change', $data);
          $this->load->view('templates/footer_no_navbar');
        }
      }
    }

    public function login(){
      if ($this->session->userdata('logged_in')) {
        redirect();
      } else {
        $data['title'] = 'Connectez-vous à votre compte';
        $data['title_meta'] = 'Se connecter | Datan';
        $data['seoNoFollow'] = TRUE;
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        // If penalty
        if ($this->session->tempdata('penalty') === true) {
          $this->load->view('templates/header_no_navbar', $data);
          $this->load->view('users/blocked');
          $this->load->view('templates/footer_no_navbar');
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

    public function compte(){
      $this->password_model->is_logged_in();
      $data['userdata'] = $this->session->userdata();
      $data['user'] = $this->user_model->get_user($data['userdata']['user_id']);

      $data['title'] = 'Gérer mon compte - ' . $data['userdata']['username'];
      $data['title_meta'] = 'Gérer mon compte | Datan';
      $data['url'] = $this->meta_model->get_url();
      $data['seoNoFollow'] = TRUE;

      $this->load->view('templates/header', $data);
      $this->load-> view('users/mon-compte', $data);
      $this->load->view('templates/footer', $data);
    }

    public function modify_personal_data(){
      $this->password_model->is_logged_in();
      $data['userdata'] = $this->session->userdata();
      $data['user'] = $this->user_model->get_user($data['userdata']['user_id']);

      $data['title'] = 'Modifier mes données personnelles';
      $data['title_meta'] = 'Modifier mes données personnelles | Datan';
      $data['url'] = $this->meta_model->get_url();
      $data['seoNoFollow'] = TRUE;

      $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
      $this->form_validation->set_rules('pseudo', 'Pseudo', 'required|alpha_dash');
      $this->form_validation->set_rules('name', 'Nom', 'required');
      $this->form_validation->set_rules('zipcode', 'Code postal', 'required|is_natural');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('templates/header', $data);
        $this->load-> view('users/modify-personal-data', $data);
        $this->load->view('templates/footer', $data);
      } else {
        $this->user_model->modify_personal_data($data['user']['id']);
        $this->session->set_flashdata('change_success', "Vos données personnelles ont été changées.");
        redirect(base_url().'mon-compte');
      }
    }

    public function modify_password(){
      $this->password_model->is_logged_in();
      $data['userdata'] = $this->session->userdata();
      $data['user'] = $this->user_model->get_user($data['userdata']['user_id']);

      $data['title'] = 'Modifier mon mot de passe';
      $data['title_meta'] = 'Modifier mon mot de passe | Datan';
      $data['url'] = $this->meta_model->get_url();
      $data['seoNoFollow'] = TRUE;

      $this->form_validation->set_rules('current', 'Mot de passe actuel', 'required');
      $this->form_validation->set_rules('new', 'Nouveau mot de passe', 'required');
      $this->form_validation->set_rules('new_confirmation', 'Confirmation du nouveau de mot de passe', 'required|matches[new]');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('templates/header', $data);
        $this->load-> view('users/modify-password', $data);
        $this->load->view('templates/footer', $data);
      } else {
        $current = $this->input->post('current');
        $new = $this->input->post('new');
        $new_confirmation = $this->input->post('new_confirmation');

        // Test current password
        if (password_verify($current, $data['user']['password'])) {
          $enc_password = password_hash($new, PASSWORD_DEFAULT);
          $this->user_model->update_password($data['user']['id'], $enc_password);
          $this->session->set_flashdata('change_success', "Le mot de passe a été changé.");
          redirect('mon-compte');
        } else {
          $this->session->set_flashdata('login_failed', "Votre mot de passe actuel n'est pas le bon.");
          redirect('mon-compte/modifier-password');
        }
      }
    }

    public function delete_account(){
      $this->password_model->is_logged_in();
      $data['userdata'] = $this->session->userdata();
      $data['user'] = $this->user_model->get_user($data['userdata']['user_id']);

      $data['title'] = 'Supprimer mon compte Datan';
      $data['title_meta'] = 'Supprimer mon compte | Datan';
      $data['url'] = $this->meta_model->get_url();
      $data['seoNoFollow'] = TRUE;

      $this->load->view('templates/header', $data);
      $this->load-> view('users/delete-account', $data);
      $this->load->view('templates/footer', $data);
    }

    public function delete_account_confirmed(){
      $this->password_model->is_logged_in();
      $data['userdata'] = $this->session->userdata();
      $data['user'] = $this->user_model->get_user($data['userdata']['user_id']);
      $this->user_model->delete_account($data['user']['id']);
      redirect('logout');
    }

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
