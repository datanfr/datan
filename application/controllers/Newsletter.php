<?php

class Newsletter extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('newsletter_model');
        $this->load->model('votes_model');
        $this->load->model('deputes_model');
        $this->load->model('depute_edito');
    }

    public function edit($email){

        if (strpos($email, '@') !== false) {
          redirect('newsletter/edit/' . urlencode($email));
        }

        $data['newsletter'] = $this->newsletter_model->get_by_email(urldecode($email));
        if (!isset($data['newsletter'])) {
          show_404($this->functions_datan->get_404_infos());
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

    public function register(){
      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "S'inscrire à la newsletter | Datan";
      $data['description_meta'] = "Abonnez-vous à la newsletter de Datan. Recevez tous les mois des informations concernant l'Assemblée nationale, l'activité des députés et leurs positions de vote.";
      $data['title'] = 'Abonnez-vous à la newsletter';
      // Breadcrumb
      $data['breadcrumb'] = array(
        array(
          "name" => "Datan", "url" => base_url(), "active" => FALSE
        ),
        array(
          "name" => "Newsletter", "url" => base_url()."newsletter", "active" => TRUE
        )
      );
      $data['breadcrumb_json'] = $this->breadcrumb_model->breadcrumb_json($data['breadcrumb']);
      //Meta
      $data['url'] = $this->meta_model->get_url();
      //Open Graph
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);

      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('newsletter/index', $data);
      $this->load->view('templates/breadcrumb', $data);
      $this->load->view('templates/footer', $data);
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

      foreach ($lists as $list) {
        $contactsSql = $this->newsletter_model->get_all_by_list($list['sql']);
        var_dump($contactsSql);
        die();
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

    public function delete($email){
      $data['email'] = $email;
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Désinscription à la newsletter | Datan";
      $data['title'] = 'Désinscription à la newsletter';
      $response = getContactId($email);
      if ($response->success()) {
        $emailId = $response->getData()[0]["ContactID"];
        removeContactlist($emailId, 25834);
      }
      $lists = array('general', 'votes');
      $this->newsletter_model->delete(urldecode($email), $lists);
      $data['message'] = 'Vous ne recevrez plus nos newsletters, c\'est dommage mais n\'hésitez pas à revenir pour connaitre l\'activité de vos députés !';

      // Views
      $this->load->view('templates/header', $data);
      $this->load->view('newsletter/delete', $data);
      $this->load->view('templates/footer', $data);
    }

    public function votes($action){

      // Check if CLI
      if ((!is_cli() && !$this->password_model->is_admin()) || (!is_cli() && $action == "send")) { // ||
        die("Only command line access"); // Comment for testing with URL newsletter/votes
      }

      // Data for the newsletter
      setlocale(LC_TIME, "fr_FR");
      $data['month'] = utf8_encode(strftime("%B", strtotime("- 1 months")));
      $data['year'] = utf8_encode(strftime("%Y", strtotime("- 1 months")));
      $year = date("Y", strtotime("-1 months"));
      $month = date("m", strtotime("-1 months"));
      //$year = 2022; $month = 7; // For testing
      $data['votesN'] = $this->votes_model->get_n_votes(legislature_current(), $year, $month);
      $data['votesNDatan'] = $this->votes_model->get_n_votes_datan(legislature_current(), $year, $month);

      if ($data['votesNDatan'] > 0) {
        $data['votesInfos'] = $this->votes_model->get_infos_period(legislature_current(), $year, $month);

        // Edited text
        if ($data['votesInfos']['adopted'] > $data['votesInfos']['rejected']) {
          $data['votesInfosEdited'] = "Au total, " . $data['votesInfos']['adopted'] . " votes adoptés par les députés tandis que " . $data['votesInfos']['rejected'] . " votes rejetés.";
        } elseif ($data['votesInfos']['adopted'] < $data['votesInfos']['rejected']) {
          $data['votesInfosEdited'] = "Au total, " . $data['votesInfos']['rejected'] . " votes ont été rejetés par les députés tandis que  " . $data['votesInfos']['adopted'] . " votes ont été adoptés.";
        } else {
          $data['votesInfosEdited'] = NULL;
        }

        // Get votes
        if ($data['votesNDatan'] <= 3 ) {
          $data['importants'] = FALSE;
          $data['votes'] = $this->votes_model->get_votes_datan(legislature_current(), $year, $month, 3, FALSE);
        } else {
          $data['importants'] = TRUE;
          $data['votes'] = $this->votes_model->get_votes_datan(legislature_current(), $year, $month, 3, TRUE);
        }

        // Description of votes
        foreach ($data['votes'] as $key => $value) {
          $string = substr($value['description'], 0, strpos($value['description'], "</p>")+4);
          $string = strip_tags($string);
          $data['votes'][$key]['description'] = $string;
          $data['votes'][$key]['groupes'] = $this->votes_model->get_vote_groupes_simplified($value['voteNumero'], $value['legislature']);
        }

        // Get most active MP
        if ($data['votesN'] > 25) {
          $data['depute'] = $this->deputes_model->get_depute_vote_plus_month(legislature_current(), $year, $month, 3);
          $data['depute']['gender'] = gender($data['depute']['civ']);
        }

        // Metadata
        $data['title'] = "Les votes de l'Assemblée nationale - " . $data['month'] . " " . $data['year'] . " | Newsletter Datan";
        if ($action == "test") {
          $title = "[TEST] " . ucfirst($data['month']) . " " . $data['year'] . " | Les derniers votes à l'Assemblée nationale";
        } else {
          $title = ucfirst($data['month']) . " " . $data['year'] . " | Les derniers votes à l'Assemblée nationale";
        }

        // Create the MJML/HTML newsletter
        $header = $this->load->view('newsletterTemplates/templates/header', $data, TRUE);
        $body = $this->load->view('newsletterTemplates/votes/body', $data, TRUE);
        $footer = $this->load->view('newsletterTemplates/templates/footer', $data, TRUE);
        $mjml = $header." ".$body." ".$footer;
        $html = getMjmlHtml($mjml);
        $html = getHtmlMinified($html);
        if ($this->password_model->is_admin()) {
          echo $html;
        }

        // Send emails
        if (in_array($action, ['send','test'], true )) {
          if ($action == 'send') {
            $emails = $this->newsletter_model->get_emails("votes");
          } else {
            $emails[]['email'] =  'info@datan.fr';
          }

          foreach ($emails as $email) {
            sendMail($email['email'], $title, $templateHtml = $html, $templateLanguage = TRUE, $templateId = NULL, $variables = NULL);
          }

          echo date('Y-m-d H:i:s') ."\n";
          echo "Emails have been sent (" . $action . "). \n";
        }

      } else {
        echo date('Y-m-d H:i:s') ."\n";
        echo "Not enough votes in the database for this month. \n";
      }
    }
}
