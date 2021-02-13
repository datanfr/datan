<?php
class Language_sql extends CI_Model{
  function __construct() {
    // Call the Model constructor
    parent::__construct();
    $this->db->query("SET lc_time_names='fr_FR'");
  }
}
?>
