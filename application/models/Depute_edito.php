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
      if ($depute < $average) {
        $phrase = 'moins souvent';
      } elseif ($depute > $average) {
        $phrase = 'plus souvent';
      } else {
        $phrase = 'autant';
      }
      return $phrase;
    }

    public function loyaute($depute, $average){
      if ($depute < $average) {
        $phrase = 'moins élevé';
      } elseif ($depute > $average) {
        $phrase = 'plus élevé';
      } else {
        $phrase = 'aussi élevé';
      }
      return $phrase;
    }

    public function majorite($depute, $average){
      if ($depute < $average) {
        $phrase = 'moins proche';
      } elseif ($depute > $average) {
        $phrase = 'plus proche';
      } else {
        $phrase = 'aussi proche';
      }
      return $phrase;
    }

    public function positionnement($stats, $groupe){
      $infos = groups_position_edited();

      function maj_pres($positionPolitique){
        if (!$positionPolitique) {
          $maj_pres = NULL;
        } elseif ($positionPolitique == "Opposition") {
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

    public function history($mp, $average){
      if ($mp < $average) {
        return 'moins que la moyenne';
      } elseif($mp > $average){
        return 'plus que la moyenne';
      } else {
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

    public function get_explication($vote, $gender){
      switch ($vote) {
        case "pour":
          return "a voté pour";
          break;

        case "contre":
          return "a voté contre";
          break;

        case "abstention":
          return "s'est abstenu" . $gender["e"] . " sur";
          break;

        default:
          return "";
          break;
      }
    }

    public function get_end_mandate($depute){
      if(strpos($depute['causeFin'], 'Nomination comme membre du Gouvernement') !== false){
        return "pour cause de nomination au Gouvernement";
      } elseif(strpos($depute['causeFin'], 'Décès') !== false) {
        return "pour cause de décès";
      } elseif(strpos($depute['causeFin'], "Démission d'office sur décision du Conseil constitutionnel") !== false) {
        return "pour cause de démission sur décision du Conseil constitutionnel";
      } elseif(strpos($depute['causeFin'], 'Démission') !== false) {
        "pour cause de démission";
      } elseif(strpos($depute['causeFin'], "Annulation de l'élection sur décision du Conseil constitutionnel") !== false){
        "pour cause d'annulation de l'élection sur décision du Conseil constitutionnel";
      } elseif(strpos($depute['causeFin'], "Reprise de l'exercice du mandat d'un ancien membre du Gouvernement") !== false){
        return ". Remplaçant un député nommé au Gouvernement, " . $depute['nameFirst'] . " " . $depute['nameLast'] . " a quitté l'Assemblée lorsque celui-ci est redevenu député";
      }
    }

  }
?>
