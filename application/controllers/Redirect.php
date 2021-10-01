<?php
  class Redirect extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->load->model('departement_model');
    }

    // Delete all cache
    public function cities($code, $dpt){
      // New caledonie is absent
      // Polynésie française is absent
      // Wallis-et-Futuna is absent
      // Saint-Barthélemy et Saint-Martin

      $city = substr($code, 2);
      $newCity = (int)$city;

      $result = $this->city_model->get_slug$newCity, $dpt);
      if (!empty($result)) {
        $url = base_url()."deputes/".$result['dpt_slug']."/ville_".$result['commune_slug'];
        redirect($url);
      } else {
        echo "Commune could not be found";
        echo "<br>";
        echo "code => ".$code;
        echo "<br>";
        echo "dpt =>".$dpt;
        echo "<br>";
        echo "city =>".$city;
        echo "<br>";
        echo "new city =>".$newCity;
        echo "<br>";
      }

    }

  }
?>
