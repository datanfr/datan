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

  function localhost(){
    $array = array(
      '127.0.0.1',
      '::1'
    );

    return $array;
  }
?>
