<?php
  function asset_url() {
    return base_url().'assets/';
  }

  function css_url(){
    return base_url().'assets/css/';
  }

  function css_file(){
    return 'main_2021222';
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
?>
