<?php
  class Password_model extends CI_Model {
    public function security_password(){
      if (!$this->session->userdata('logged_in')) {
        redirect('login');
      }
    }

    public function security(){
      if (empty($this->session->userdata('type'))) {
        redirect();
      }
    }

    public function security_only_admin(){
      if ($this->session->userdata('type') != 'admin') {
        redirect();
      }
    }

    public function security_only_team(){
      if ($this->session->userdata('type') != 'admin' && $this->session->userdata('type') != 'writer') {
        redirect();
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
  }
?>
