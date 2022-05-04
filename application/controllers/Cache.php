<?php
  class Cache extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->password_model->security();
    }

    // Delete all cache
    public function delete_all(){
      delete_all_cache();
      $this->db->cache_delete_all(); // For database cache
      redirect();
    }

  }
?>
