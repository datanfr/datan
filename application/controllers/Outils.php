<?php
  class Outils extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('groupes_model');
      $this->load->model('faq_model');
    }

    public function coalition(){

      $data['title_meta'] = 'Labo : coalition';
      $data['url'] = NULL;

      $data['groups'] = $this->groupes_model->get_groupes_coalition_builder();

      $data["faq"] = [
        ["title" => "Peut-on gouverner sans majorité absolue ?", "text" => "Oui, mais c'est compliqué. Le gouvernement doit chercher des alliances ponctuelles, ralentissant son action et le rendant plus vulnérable."],
        ["title" => "Qu'est-ce qui se passe si le gouvernement est censuré ?", "text" => "Le gouvernement tombe et le Président de la République doit désigner un nouveau Premier ministre."],
        ["title" => "Comment empêcher la censure du gouvernement sans majorité absolue ?", "text" => "Le gouvernement cherche des soutiens explicites ou implicites. Un groupe peut par exemple décider de ne pas voter la censure en échange de concessions, ou parce qu'il ne souhaite pas prendre le risque d'une dissolution et donc de nouvelles élections législatives. C'est la logique des alliances ponctuelles."]
      ];
      $data['faq'] = array($data['faq']);

      // Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Assemblée nationale : formez votre majorité avec notre simulateur de coalition | Datan";
      $data['description_meta'] = "Créez votre propre coalition à l'Assemblée nationale. Après les élections législatives de 2024, aucun groupe politique n'a obtenu de majorité absolue. Utilisez notre simulateur pour sélectionner les députés et groupes qui voteront comme vous et composez votre majorité parlementaire.";
      // Open graph 
      $controller = $this->router->fetch_class()."/".$this->router->fetch_method();
      $data['ogp'] = $this->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // FAQ schema
      $data['schema'] = $this->faq_model->get_faq_schema($data['faq']);
      // JS
      $data['js_to_load'] = array('datan/coalition_builder.min');
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('outils/coalition');
      $this->load->view('templates/footer');
    }

  }
?>
