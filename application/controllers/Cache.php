<?php
  class Cache extends CI_Controller{

    public function __construct() {
      parent::__construct();
      $this->password_model->security_only_team();
    }

    // Delete all cache
    public function delete_all(){
      delete_all_cache();
      $this->db->cache_delete_all(); // For database cache
      redirect();
    }

    // Cache clear via CLI only (for Github runner)
    public function clear_cli() {
      if (!is_cli()) {
          show_error('Access forbidden', 403);
      }
        delete_all_cache();
        $this->db->cache_delete_all();

        echo "Cache successfully cleared via CLI\n";
    }

  }
?>
