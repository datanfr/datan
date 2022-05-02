<?php
  class Table_history_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function insert($old, $new, $table, $col, $user) {
      if ($old != $new) {
        $data = array(
          'table' => $table,
          'col' => $col,
          'value_old' => $old,
          'value_new' => $new,
          'user' => $user
        );
        $this->db->insert('table_history', $data);
      }
    }

  }
?>
