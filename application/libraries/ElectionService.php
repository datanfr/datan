<?php

class ElectionService 
{
    public function __construct() 
    {
        $this->CI =& get_instance();
        $this->CI->load->model('elections_model');
    }

    public function get_all_elections(string $mp_id, array $gender): array
    {
        $elections = $this->CI->elections_model->get_candidate_elections($mp_id, TRUE, TRUE);
        
        foreach ($elections as $key => $value) {
    
            $elections[$key]['district'] = $this->CI->elections_model->get_district($value['libelleAbrev'], $value['district']);
            
    
            if ($value['elected'] === "1") {
                $elections[$key]['electedLibelle'] = 'Élu' . $gender['e'];
                $elections[$key]['electedColor'] = 'adopté';
            } elseif ($value['elected'] === "0") {
                $elections[$key]['electedLibelle'] = 'Éliminé' . $gender['e'];
                $elections[$key]['electedColor'] = 'rejeté';
            } else {
                $elections[$key]['electedLibelle'] = '';
                $elections[$key]['electedColor'] = '';
            }
        }

        return $elections;
    }
}
