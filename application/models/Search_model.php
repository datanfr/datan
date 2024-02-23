<?php
class Search_model extends CI_Model
{
    public function __construct()
    {
    }

    public function searchInVotes($search)
    {      
        $sql = '
        SELECT vd.title, SUBSTRING(
            vd.description, 
            GREATEST(1, LOCATE("'.$search.'", vd.description) - 100), 
            LEAST(LENGTH(vd.description), 200 + LENGTH("'.$search.'"))
        ) AS description, vd.legislature, vd.voteNumero, vd.modified_at
        FROM votes_datan vd
        WHERE vd.title LIKE "%'.$search.'%" OR vd.description LIKE "%'.$search.'%"
        ORDER BY vd.modified_at DESC LIMIT 10'
        ;
        $data = $this->db->query($sql)->result_array();

        return $data;
    }
    public function searchInDeputes($search)
    {      
        $sql = '
        SELECT d.nameFirst, d.nameLast, d.nameUrl, d.dptSlug, d.libelle
        FROM deputes_all d
        WHERE d.nameFirst LIKE "%'.$search.'%" OR d.nameLast LIKE "%'.$search.'%"
        ORDER BY d.datePriseFonction DESC LIMIT 10'
        ;
        $data = $this->db->query($sql)->result_array();

        return $data;
    }
}
