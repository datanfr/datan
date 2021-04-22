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
        $email = urldecode($email);
        $data['email'] = $email;
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Désinscription à la newsletter | Datan";
        $data['title'] = 'Désinscription à la newsletter';
        $response = getContactId($email);
        if ($response->success()) {
          $emailId = $response->getData()[0]["ContactID"];
          $list = 25834;
          removeContactlist($emailId, $list);
        }

        if ($this->newsletter_model->get_by_email($email)){
            $this->newsletter_model->delete_newsletter($email);
            $data['message'] = 'Vous ne recevrez plus nos newsletters, c\'est dommage mais n\'hésitez pas à revenir pour connaitre l\'activité de vos députés !';
        }
        else {
            $data['message'] = 'Vous ne semblez pas être actuellement inscrit à la newsletter, si malgré tout vous continuez de recevoir des e-mails de notre part merci de nous contacter.';
        }
        $this->load->view('templates/header', $data);
        $this->load->view('newsletter/delete', $data);
        $this->load->view('templates/footer', $data);
    }
}
