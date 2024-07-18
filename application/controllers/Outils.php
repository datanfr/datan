<?php
  class Outils extends CI_Controller{

    public function __construct() {
      parent::__construct();
    }

    // Admin homepage
    public function coalition(){

      $data['title_meta'] = 'Labo : coalition';
      $data['url'] = NULL;

      $data['groups'] = array(
        'GDR' => array('libelleAbrev' => 'GDR', 'libelle' => 'Gauche démocrate et républicaine', 'seats' => 16, 'color' => '#A41914'),
        'LFI' => array('libelleAbrev' => 'LFI', 'libelle' => 'La France insoumise', 'seats' => 75, 'color' => '#ED302C'),
        'EELV' => array('libelleAbrev' => 'EELV', 'libelle' => 'Les Ecologistes', 'seats' => 33, 'color' => '#77AA79'),
        'PS' => array('libelleAbrev' => 'PS', 'libelle' => 'Parti socialiste', 'seats' => 69, 'color' => '#e30040'),
        'LIOT' => array('libelleAbrev' => 'LIOT', 'libelle' => 'LIOT', 'seats' => 15, 'color' => '#F8D434'),
        'MODEM' => array('libelleAbrev' => 'MODEM', 'libelle' => 'MODEM', 'seats' => 33, 'color' => '#CE5215'),
        'RE' => array('libelleAbrev' => 'RE', 'libelle' => 'Renaissance', 'seats' => 97, 'color' => '#61468F'),
        'HOR' => array('libelleAbrev' => 'HOR', 'libelle' => 'Horizons', 'seats' => 25, 'color' => '#32B3CA'),
        'LR' => array('libelleAbrev' => 'LR', 'libelle' => 'Les Républicains', 'seats' => 46, 'color' => '#4565AD'),
        'AD' => array('libelleAbrev' => 'AD', 'libelle' => 'A droite!', 'seats' => 17, 'color' => '#3D5786'),
        'RN' => array('libelleAbrev' => 'RN', 'libelle' => 'Rassemblement national', 'seats' => 125, 'color' => '#35495E'),
      );

      $data['js_to_load'] = array('datan/coalition_builder');
      
      $this->load->view('templates/header', $data);
      $this->load->view('labo/coalition');
      $this->load->view('templates/footer');
    }

  }
?>
