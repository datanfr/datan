<?php
  class Dossiers_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_dossiers(){
      $this->db->select('dv.id, dv.dossierId, dv.legislature, d.titreChemin, d.titre, dv.voteNumero'); 
      $this->db->select('vi.dateScrutin');
      $this->db->join('dossiers d', 'dv.dossierId = d.dossierId');
      $this->db->join(
        '(SELECT dossierId, MAX(voteNumero) as voteNumero, legislature FROM dossiers_votes GROUP BY dossierId, legislature) as max_vote',
        'dv.dossierId = max_vote.dossierId AND dv.voteNumero = max_vote.voteNumero AND dv.legislature = max_vote.legislature',
      );
      $this->db->join('votes_info vi', 'vi.voteNumero = max_vote.voteNumero AND vi.legislature = max_vote.legislature', 'left');
      $this->db->group_by('dv.dossierId');
      $query = $this->db->get_where('dossiers_votes dv');
      return $query->result_array();




    }

  }
