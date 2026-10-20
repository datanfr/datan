<?php
  class Breadcrumb_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function breadcrumb_json($array){
      $itemListElement = [];

      foreach ($array as $key => $value) {
        $itemListElement[] = [
          '@type' => 'listItem',
          'position' => $key+1,
          'name' => $value['name'],
          'item' => $value['url']
        ];
      }

      $schema = [
        "@context" => "http://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $itemListElement
      ];

      // echo "<br>ICI ==> <br>";
      //print_r($schema);
      return($schema);
    }

  }
?>
