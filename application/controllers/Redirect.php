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

      switch ($dpt) {
        case 976:
          $dpt = "0ZM";
          $city = substr($code, 3);
          $newCity = "5".$city;
          break;

        case 974:
          $dpt = "0ZD";
          break;

        case 972:
          $dpt = "0ZB";
          break;

        case 973:
          $dpt = "0ZC";

        case 971:
          $dpt = "0ZA";
          break;

      }

      //echo "<br>";
      //echo "code => ".$code;
      //echo "<br>";
      //echo "dpt =>".$dpt;
      //echo "<br>";
      //echo "city =>".$city;
      //echo "<br>";
      //echo "new city =>".$newCity;
      //echo "<br>";



      $result = $this->departement_model->get_commune_slug($newCity, $dpt);
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
