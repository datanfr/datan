<?php
  function asset_url() {
    return base_url().'assets/';
  }

  function css_url(){
    return base_url().'assets/css/';
  }

  function getVersion(){
    return 26;
  }

  function legislature_current(){
    return 15;
  }

  function legislature_all(){
    return array(14, 15);
  }

  function formatNumber($x){
    return number_format($x, 0, '.', ' ');
  }

  function meanAgeFranceAll(){
    return 42.1;
  }

  function meanAgeFrance(){
    // Only more than 18 yo.
    // source: https://docs.google.com/spreadsheets/d/17pf7I0vN_yIl7lnebXhZSKDRaE6j31qzRX77Cx1SYD8/edit?usp=sharing
    return 50.52;
  }

  function groupesNI(){
    return array("PO723569");
  }

  function majority_group(){
    return "PO730964";
  }

  function groupsPositionEdited(){
    $array = array(
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
    return $array;
  }

  function get_months(){
    $months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'decembre');
    return $months;
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

?>
