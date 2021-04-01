<?php

class Newsletter extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('newsletter_model');
    }

    public function edit($email){
        $data = [];
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('body', 'Body', 'required');

        $data['newsletter'] = $this->newsletter_model->get_by_email($email);
        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Les élections en France - Candidats et résultats | Datan";
        $data['description_meta'] = "Retrouvez toutes les informations sur les différentes élections en France (présidentielle, législative, régionales). Découvrez les députés candidats et les résultats";
        $data['title'] = 'Mettre à jour vos abonnements';

        // TODO if post


        if ($this->form_validation->run() === FALSE) {
          $this->load->view('templates/header', $data);
          $this->load->view('newsletter/edit');
          $this->load->view('templates/footer', $data);
        }
  
      }

    public function delete($email){
        if ($this->newsletter_model->delete_newsletter($email)){
            $this->session->set_flashdata('newsletter_deleted', 'Vous ne recevrez plus nos newsletter');
        }
    }
}
