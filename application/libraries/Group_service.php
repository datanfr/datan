<?php

class Group_service 
{

    public function __construct() 
    {
        $this->CI =& get_instance();
        $this->CI->load->model('deputes_model');

    }



    public function get_group_infos_by_mp(array $data, string $mp_id, string $groupe_id): array 
    {
        if (!empty($data['depute']['libelle'])) {
            $data['depute']['couleurAssociee'] = $this->CI->groupes_model->get_groupe_color(array($data['depute']['libelleAbrev'], $data['depute']['couleurAssociee']));
            
            $data['group_president'] = $this->CI->deputes_model->depute_group_president($mp_id, $groupe_id);
            $data['isGroupPresident'] = !empty($data['group_president']) ? TRUE : FALSE;
        } else {
            $groupe_id = NULL;
            $data['isGroupPresident'] = FALSE;  
        }

    return $data;  
    }

}












