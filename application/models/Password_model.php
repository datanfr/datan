<?php
  class Password_model extends CI_Model {
    public function security_password(){
      if (!$this->session->userdata('logged_in')) {
        redirect('login');
      }
    }

    public function security(){
      if (empty($this->session->userdata('type'))) {
        show_404();
      }
    }

    public function security_only_admin(){
      if ($this->session->userdata('type') != 'admin') {
        show_404();
      }
    }

    public function security_only_team(){
      if ($this->session->userdata('type') != 'admin' && $this->session->userdata('type') != 'writer') {
        show_404();
      }
    }

    public function security_only_mp(){
      if ($this->session->userdata('type') != 'mp') {
        redirect('login');
      }
    }

    public function is_admin(){
      if (($this->session->userdata('type') == 'admin')) {
        return TRUE;
      }
    }

    public function is_team(){
      if (($this->session->userdata('type') == 'admin') || $this->session->userdata('type') == 'writer') {
        return TRUE;
      }
    }

    public function is_mp(){
      if (($this->session->userdata('type') == 'mp')) {
        return TRUE;
      }
    }

    public function is_logged_in(){
      if (!$this->session->userdata('logged_in')) {
        redirect('login');
      }
    }

    public function security_captcha($redirect){
      $inputCaptcha = $this->input->post('captcha');
      $sessCaptcha = $this->session->userdata('captchaCode');
      if (!($inputCaptcha === $sessCaptcha)) {
        $attempt = $this->session->userdata('attempt');
        $attempt++;
        $this->session->set_userdata('attempt', $attempt);
        if ($this->session->userdata('attempt') >= 5) {
          $this->session->set_tempdata('penalty', true, 300);
          $this->session->set_userdata('attempt', 0);
        }
        $this->session->set_flashdata('error', "Le code captcha est erroné. Veuillez réessayer.");
        redirect($redirect);
      } else {
        $this->session->set_userdata('attempt', 0);
      }
    }
  }
?>
