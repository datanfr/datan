<?php

class Newsletter extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('newsletter_model');
    }

    public function delete($email){
        if ($this->newsletter_model->delete_newsletter($email)){
            $this->session->set_flashdata('newsletter_deleted', 'Vous ne recevrez plus nos newsletter');
        }
    }
}
