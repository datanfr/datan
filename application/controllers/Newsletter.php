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

        $data['newsletter'] = $this->newsletter_model->get_by_email($email);
        if (empty($data['newsletter'])) {
          redirect();
        }

        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Gestion des abonnements aux newsletters | Datan";
        $data['description_meta'] = "Mettez à jour vos abonnements aux différentes newsletters de Datan.";
        $data['title'] = 'Mettre à jour vos abonnements';

        if ($this->input->post()) {
          $data['general'] = $this->input->post('general');
          $data['general'] = $data['general'] == "on" ? 1 : 0;
          if ($data['general'] != $data['newsletter']['general']) {
            $list = array(
              'nameSQL' => 'general',
              'mailjetId' => 25834
            );
            $this->newsletter_model->update_list($email, $data, $list);

            // API
            if ($data['general'] == 1) {
              sendContactList(urldecode($email), $list['mailjetId']);
            } else {
              $response = getContactId(urldecode($email));
              if ($response->success()) {
                $emailId = $response->getData()[0]["ContactID"];
                removeContactlist($emailId, $list['mailjetId']);
              }
            }
          }

          $this->load->view('templates/header', $data);
          $this->load->view('newsletter/edit_success');
          $this->load->view('templates/footer', $data);

        } else {
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
