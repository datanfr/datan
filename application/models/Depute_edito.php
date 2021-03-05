<?php
  class Depute_edito extends CI_Model {

    public function votes_datan($array){
      // FONCTION EDITORIALISE POUR VOTES DATAN - DEPUTES --> HAS BEEN DELETED
      foreach ($array as $key => $row) {

        // Variable loyal_rebel / loyaute_edito_1
        switch ($row['depute_loyaute']) {
          case '1':
            $array[$key]['loyal_rebel'] = 'loyal';
            $array[$key]['loyaute_edito_1'] = "a été";
            $array[$key]['loyaute_edito_3'] = "également";
            break;

          case '0':
            $array[$key]['loyal_rebel'] = 'rebelle';
            $array[$key]['loyaute_edito_1'] = "n'a pas été";
            $array[$key]['loyaute_edito_3'] = "";
            break;

          default:
            $array[$key]['loyal_rebel'] = NULL;
            $array[$key]['loyaute_edito_1'] = NULL;
            break;
        }

        // Variable loyaute_edito_2 & loyaute_edito_4
        switch ($row['vote_groupe']) {
          case ("pour"):
            $array[$key]['loyaute_edito_2'] = "qui a";
            $array[$key]['loyaute_edito_4'] = "voté en faveur de";
            break;

          case ("contre"):
            $array[$key]['loyaute_edito_2'] = "qui a";
            $array[$key]['loyaute_edito_4'] = "voté contre";
            break;

          case ("abstention"):
            $array[$key]['loyaute_edito_2'] = "qui s'est";
            $array[$key]['loyaute_edito_4'] = "abstenu sur";

          default:
            $array[$key]['loyaute_edito_2'] = "qui s'est";
            break;
        }

      }

      //print_r($array);
      return $array;
    }

    public function participation($depute, $average){
      $bottom = $average-1;
      $top = $average+1;
      if ($depute < $bottom) {
        $phrase = 'moins souvent';
      } elseif($depute >= $bottom && $depute <= $top) {
        $phrase = 'autant';
      } elseif($depute > $top){
        $phrase = 'plus souvent';
      }
      $result = array(
        'phrase' => $phrase
      );
      return $result;
    }

    public function loyaute($depute, $average){
      $bottom = $average-1;
      $top = $average+1;
      if ($depute < $bottom) {
        $phrase = 'moins loyal';
        $circle = 'red';
      } elseif($depute >= $bottom && $depute <= $top) {
        $phrase = 'aussi loyal';
        $circle = 'orange';
      } elseif($depute > $top){
        $phrase = 'plus loyal';
        $circle = 'green';
      }
      $result = array(
        'phrase' => $phrase,
        'circle' => $circle,
      );
      return $result;
    }

    public function majorite($depute, $average){
      $bottom = $average-1;
      $top = $average+1;
      if ($depute < $bottom) {
        $phrase = 'moins proche';
      } elseif($depute >= $bottom && $depute <= $top) {
        $phrase = 'aussi proche';
      } elseif($depute > $top){
        $phrase = 'plus proche';
      }
      return $phrase;
    }

    public function positionnement($stats, $groupe){
      $infos = array(
        "FI" => "à l'extrême gauche",
        "GDR" => "à l'extrême gauche",
        "SOC" => "à gauche",
        "MODEM" => "au centre-gauche",
        "LAREM" => "au centre",
        "UDI-AGIR" => "au centre-droit",
        "LR" => "à droite",
        "UDI-A-I" => "au centre-droit",
        "NI" => "qui regroupe les députés non affiliés à un groupe parlementaire",
        "LC" => "au centre-droit",
        "LT" => "au centre",
        "EDS" => "au centre",
        "AGIR-E" => "au centre-droit",
        "UDI_I" => "au centre-droit",
        "NG" => "à la gauche",
        "DEM" => "au centre-gauche"
      );

      function maj_pres($positionPolitique){
        if ($positionPolitique == "Opposition") {
          $maj_pres = "un groupe d'opposition";
        } elseif ($positionPolitique == "Majoritaire") {
          $maj_pres = "le groupe de la majorité présidentielle, qui est";
        } elseif ($positionPolitique == "Minoritaire") {
          $maj_pres = "un groupe allié à la majorité présidentielle et";
        } else {
          $maj_pres = "qui regroupe tous les députés non affiliés à un groupe parlementaire.";
        }
        return $maj_pres;
      }

      foreach ($stats as $key => $value) {
        if ($value["organeRef"] == $groupe) {
          unset($stats[$key]);
        }
      }

      $x = 101;
      foreach ($stats as $key => $value) {
        $stats[$x] = $stats[$key];
        unset($stats[$key]);
        $x++;
      }

      $first1 = $stats[101];
      $first1["ideologiePolitique"] = $infos[$first1["libelleAbrev"]];
      $first1["maj_pres"] = maj_pres($first1["positionPolitique"]);
      $first2 = $stats[102];
      $first2["ideologiePolitique"] = $infos[$first2["libelleAbrev"]];
      //$first2["maj_pres"] = maj_pres($first2["positionPolitique"]);

      $last1 = $stats[100+count($stats)];
      $last1["ideologiePolitique"] = $infos[$last1["libelleAbrev"]];
      $last1["maj_pres"] = maj_pres($last1["positionPolitique"]);
      $last2 = $stats[100+count($stats)-1];
      $last2["ideologiePolitique"] = $infos[$last2["libelleAbrev"]];
      //$last2["maj_pres"] = maj_pres($last1["positionPolitique"]);

      $array = [
        "first1" => $first1,
        "first2" => $first2,
        "last1" => $last1,
        "last2" => $last2
      ];
      return $array;

    }

    public function infos_groupes(){
      $infos = array(
        "FI" => "à l'extrême gauche",
        "GDR" => "à l'extrême gauche",
        "SOC" => "à gauche",
        "MODEM" => "au centre-droit",
        "LAREM" => "au centre",
        "UDI-AGIR" => "au centre-droit",
        "LR" => "à droite",
        "UDI-A-I" => "au centre-droit",
        "NI" => "XX",
        "LT" => "au centre",
        "EDS" => "au centre",
        "AGIR-E" => "au centre-droit",
        "UDI_I" => "au centre-droit",
        "NG" => "à la gauche",
        "DEM" => "au centre-gauche"
      );
      return $infos;
    }

    public function gender($gender){
      if ($gender == 'Mme') {
        return array(
          'depute' => 'députée',
          'pronom' => 'elle',
          'e' => 'e',
          'le' => 'la',
          'son' => 'sa',
          'du' => 'de la'
        );
      } elseif ($gender == 'M.') {
        return array(
          'depute' => 'député',
          'pronom' => 'il',
          'e' => '',
          'le' => 'le',
          'son' => 'son',
          'du' => 'du'
        );
      }
    }

    public function history($depute, $average){
      if ($depute < $average) {
        return 'moins que la moyenne';
      } elseif($depute > $average){
        return 'plus que la moyenne';
      } elseif($depute == $average){
        return 'autant que la moyenne';
      }
    }

    public function get_nbr_lettre($nbr){
      switch ($nbr) {
        case 1:
          return 'premier';
          break;
        case 2:
          return 'deuxième';
          break;
        case 3:
          return 'troisième';
          break;
        case 4:
          return 'quatrième';
          break;
        case 5:
          return 'cinquième';
          break;
        default:
          return $nbr;
          break;
      }
    }

  }
?>
