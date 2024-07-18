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

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Assemblée nationale : formez votre majorité avec notre simulateur de coalition | Datan";
      $data['description_meta'] = "Créez votre propre coalition à l'Assemblée nationale. Après les élections législatives de 2024, aucun groupe politique n'a obtenu de majorité absolue. Utilisez notre simulateur pour sélectionner les députés et groupes qui voteront comme vous et composez votre majorité parlementaire.";
      // Open graph 
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // JS
      $data['js_to_load'] = array('datan/coalition_builder');
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('outils/coalition');
      $this->load->view('templates/footer');
    }

  }
?>
