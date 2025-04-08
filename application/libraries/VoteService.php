<?php

class VoteService
{
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('votes_model');
    }


    public function get_votes_by_legislature(string $mp_id, string $legislature): array
    {
        if ($legislature >= 15) {
    
            return [
                'votes_datan' => $this->CI->votes_model->get_votes_datan_depute($mp_id, 5),
                'key_votes'   => $this->CI->votes_model->get_key_votes_mp($mp_id),
            ];
        } else {
        
            return [
                'votes_datan' => null,
                'key_votes'   => null,
            ];
        }
    }
}
