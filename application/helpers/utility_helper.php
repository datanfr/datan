<?php
  function asset_url() {
    return base_url().'assets/';
  }

  function css_url(){
    return base_url().'assets/css/';
  }

  function get_version(){
    return 76;
  }

  function legislature_current(){
    return 16;
  }

  function legislature_all(){
    return array(14, 15, 16);
  }

  function formatNumber($x){
    return number_format($x, 0, '.', ' ');
  }

  function mean_age_france_all(){
    return 42.1;
  }

  function mean_age_france(){
    // Only more than 18 yo.
    // source: https://docs.google.com/spreadsheets/d/17pf7I0vN_yIl7lnebXhZSKDRaE6j31qzRX77Cx1SYD8/edit?usp=sharing
    return 50.52;
  }

  function groupes_NI(){
    return array("PO266900", "PO387155", "PO645633", "PO723569", "PO793087");
  }

  function remove_nupes($group){
    switch ($group) {
      case 'LFI-NUPES':
        return 'LFI';
        break;

      case 'GDR-NUPES':
        return 'GDR';
        break;

      default:
        return $group;
        break;
    }
  }

  function groups_position_edited(){
    $left = array("libelle" => "gauche", "edited" => "à gauche");
    $center = array("libelle" => "centre", "edited" => "au centre");
    $right = array("libelle" => "droite", "edited" => "à droite");

    $array = array(
      "FI" => $left,
      "GDR" => $left,
      "SOC" => $left,
      "MODEM" => $center,
      "LAREM" => $center,
      "UDI-AGIR" => $center,
      "LR" => $right,
      "UDI-A-I" => $right,
      "UDI-I" => $right,
      "LC" => $right,
      "NI" => NULL,
      "LT" => $center,
      "EDS" => $center,
      "AGIR-E" => $center,
      "UDI_I" => $center,
      "NG" => $left,
      "DEM" => $center,
      "ECOLO" => $left,
      "LES-REP" => $right,
      "RRDP" => $left,
      "R-UMP" => $right,
      "SER" => $left,
      "SRC" => $left,
      "UDI" => $right,
      "UMP" => $right,
      "RENAIS" => $center,
      "RN" => $right,
      "LFI" => $left,
      "HORIZONS" => $right,
      "LIOT" => $center,
      "RE" => $center,
      "LFI-NUPES" => $left,
      "HOR" => $right,
      "GDR-NUPES" => $left,
      "SOC-A" => $left
    );
    return $array;
  }

  function get_months(){
    $months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'decembre');
    return $months;
  }

  function months_abbrev($x){
    if (strpos($x, "janvier") !== false) {
      return str_replace("janvier", "janv.", $x);
    } elseif (strpos($x, "février") !== false) {
      return str_replace("février", "févr.", $x);
    } elseif (strpos($x, "avril") !== false) {
      return str_replace("avril", "avr.", $x);
    } elseif (strpos($x, "juillet") !== false) {
      return str_replace("juillet", "juill.", $x);
    } elseif (strpos($x, "septembre") !== false) {
      return str_replace("septembre", "sept.", $x);
    } elseif (strpos($x, "octobre") !== false) {
      return str_replace("octobre", "oct.", $x);
    } elseif (strpos($x, "novembre") !== false) {
      return str_replace("novembre", "nov.", $x);
    } elseif (strpos($x, "décembre") !== false) {
      return str_replace("décembre", "déc.", $x);
    } else {
      return $x;
    }
  }

  function localhost(){
    $array = array(
      '127.0.0.1',
      '::1'
    );

    return $array;
  }

  //Obfuscation for SEO purpose
  function url_obfuscation($x){
    $url = "sdfghj".str_rot13($x);
    return $url;
  }

  function text_url_obfuscation($text){
    $dom = new DOMDocument();
    if (!isset($text)) return;
    $dom->loadHTML('<?xml encoding="UTF-8">' . $text);
    $tags = $dom->getElementsByTagName('a');
    for ($i = $tags->length - 1; $i > -1 ; $i--) {
      $tag = $tags->item($i);
      if ($tag->getAttribute('target') == '_blank' && strpos($tag->getAttribute('href'), 'datan.fr') === false) {
        $href = $tag->getAttribute('href');
        $replacement = $dom->createElement('span', $tag->nodeValue);
        $replacement->setAttribute('class', 'url_obf');
        $replacement->setAttribute('url_obf', url_obfuscation($href));
        $tag->parentNode->replaceChild($replacement, $tag);
      }
    }
    return $dom->saveHTML($dom->documentElement);
  }

  // Function for searching in multidimensio
  function in_array_r($needle, $haystack, $strict = false) {
      foreach ($haystack as $item) {
          if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
              return true;
          }
      }

      return false;
  }

  // Gender of actors
  function gender($gender){
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

  // Abbreviation of numbers (1er / 2e )
  function abbrev_n($n, $fem){
    if ($fem) {
      switch ($n) {
        case 1:
          return "re";
          break;

        case 2:
          return "de";
          break;

          default:
            return "e";
            break;
      }
    } else {
      switch ($n) {
        case 1:
          return "er";
          break;

        case 1:
          return "d";
          break;

          default:
            return "e";
            break;

      }
    }
  }

  function vote_edited($input){
    if ($input == 1) {
      $output = "pour";
    } elseif ($input == -1) {
      $output = "contre";
    } elseif ($input == 0) {
      $output = "abstention";
    } else {
      $output = NULL;
    }
    return $output;
  }

  function name_group($name){
    switch ($name) {
      case 'La France insoumise - Nouvelle Union Populaire écologique et sociale':
        return 'La France insoumise - NUPES';
        break;

      case 'Socialistes et apparentés (membre de l’intergroupe NUPES)':
        return 'Socialistes et apparentés - NUPES';
        break;

      default:
        return $name;
        break;
    }
  }

  function ordinaux($x){
    $array = array(
      1 => 'premier',
      2 => 'deuxième',
      3 => 'troisième',
      4 => 'quatrième',
      5 => 'cinquième',
      6 => 'sixième',
      7 => 'septième',
      8 => 'huitième',
      9 => 'neuvième',
      10 => 'dixième',
      11 => 'onzième'
    );
    return $array[$x];
  }

  function is_congress_numero($data){
    if ($data > 0) {
      return $data;
    } else {
      return "c" . abs($data);
    }
  }

?>
