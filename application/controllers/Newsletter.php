<?php

class Newsletter extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('newsletter_model');
    }

    public function edit($email){

        if (strpos($email, '@') !== false) {
          redirect('newsletter/edit/' . urlencode($email));
        }

        $data['newsletter'] = $this->newsletter_model->get_by_email(urldecode($email));
        if (!isset($data['newsletter'])) {
          show_404();
        }

        $data['lists'] = array(
          array(
            'label' => 'Newsletter principale',
            'name' => 'general',
            'mailjetId' => 25834
          ),
          array(
            'label' => 'Newsletter votes',
            'name' => 'votes',
            'mailjetId' => 47010
          )
        );

        // Meta
        $data['url'] = $this->meta_model->get_url();
        $data['title_meta'] = "Gestion des abonnements aux newsletters | Datan";
        $data['description_meta'] = "Mettez à jour vos abonnements aux différentes newsletters de Datan.";
        $data['title'] = 'Mettre à jour vos abonnements';

        if ($this->input->post()) {
          foreach ($data['lists'] as $list) {
            $data[$list['name']] = $this->input->post($list['name']);
            $data[$list['name']] = $data[$list['name']] == "on" ? 1 : 0;
            if ($data[$list['name']] != $data['newsletter'][$list['name']]) {
              $this->newsletter_model->update_list(urldecode($email), $data[$list['name']], $list['name']);
              // API
              if ($data[$list['name']] == 1) {
                sendContactList(urldecode($email), $list['mailjetId']);
              } else {
                removeContactlist(urldecode($email), $list['mailjetId']);
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

    public function update(){
      $lists = array(
        array(
          "sql" => "general",
          "mailjet" => 25834
        ),
        array(
          "sql" => "votes",
          "mailjet" => 47010
        )
      );

      // REVOIR ICI LE UPDATE !

      foreach ($lists as $list) {
        $contactsSql = $this->newsletter_model->get_all_by_list($list['sql']);
        foreach ($contactsSql as $contact) {
          $response = getContactLists($contact['email']);
          if ($response->success()) {
            $responseLists = $response->getData();
            foreach ($responseLists as $responseList) {
              if (($responseList['ListID'] == $list['mailjet']) && ($responseList['IsUnsub'])) {
                $this->newsletter_model->update_list($contact['email'], NULL, $list['sql']);
              }
            }
          }
        }
      }
    }
}
