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

  function url_obfuscation($x){
    $url = "sdfghj".str_rot13($x);
    return $url;
  }

  function text_url_obfuscation($text){
    $dom = new DOMDocument();
    $dom->encoding = 'utf-8';
    $dom->loadHTML( utf8_decode($text) );
    $tags = $dom->getElementsByTagName('a');
    for ($i = $tags->length - 1; $i > -1 ; $i--) {
      $tag = $tags->item($i);
      //print_r($tag);

      if ($tag->getAttribute('target') == '_blank') {
        
        echo $tag->getAttribute('href');
        $replacement = $dom->createTextNode($tag->nodeValue);
        $tag->parentNode->replaceChild($replacement, $tag);

      }

      $dom->saveHTML();

    }


  }

?>
