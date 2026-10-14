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
          'value_old' => $old == NULL ? '' : $old,
          'value_new' => $new,
          'user' => $user
        );
        $this->db->insert('table_history', $data);
      }
    }

    public function get_history($table){
      $this->db->order_by('modified_at', 'desc');
      $this->db->join('users', 'table_history.user = users.id');
      $this->db->join('users_mp', 'users.id = users_mp.user');
      $this->db->join('deputes_last', 'deputes_last.mpId = users_mp.mpId');
      return $this->db->get_where('table_history', array('table' => $table))->result_array();
    }

  }
?>
