<?php
  function asset_url() {
    return base_url().'assets/';
  }

  function css_url(){
    return base_url().'assets/css/';
  }

  function css_file(){
    return 'main_20210301';
    //return 'main';
  }

  function js_file(){
    return 'main_20201125.min';
    //return 'main';
  }

  function datatable_file(){
    return '202121_datatable-datan.min';
  }

  function legislature_current(){
    return 15;
  }

  function legislature_all(){
    return array(15);
  }

  function majority_group(){
    return "PO730964";
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
    $dom->encoding = 'utf-8';
    if (!isset($text)) return;
    $dom->loadHTML( utf8_decode($text) );
    $tags = $dom->getElementsByTagName('a');
    for ($i = $tags->length - 1; $i > -1 ; $i--) {
      $tag = $tags->item($i);
      if ($tag->getAttribute('target') == '_blank') {
        $href = $tag->getAttribute('href');
        $replacement = $dom->createElement('span');
        $replacement->setAttribute('class', 'url_obf');
        $replacement->setAttribute('url_obf', url_obfuscation($href));
        $a = $dom->createElement('a', $tag->nodeValue);
        $a->setAttribute('href', "#");
        $replacement->appendChild($a);
        $tag->parentNode->replaceChild($replacement, $tag);
      }
    }
    return $dom->saveHTML($dom->documentElement);
  }

?>
