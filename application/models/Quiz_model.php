<?php
class Quiz_model extends CI_Model
{
    public function __construct()
    {
    }

    public function getResults($choices)
    {
        $voteNumeros = [];
        foreach($choices as $choice){
            $voteNumeros[] = $choice["voteNumero"];
        }
        $votes = $this->votes_model->get_votes_result_by_depute($voteNumeros);
        $groupes = array();
        $deputes = array();
        foreach ($votes as $vote) {
            foreach ($choices as $post) {
                if (!isset($groupes[$vote['uid']])) {
                    $groupes[$vote['uid']] = array();
                    $groupes[$vote['uid']]['score'] = 0;
                    $groupes[$vote['uid']]['name'] = $vote['libelle'];
                    $groupes[$vote['uid']]['totalVote'] = 0;
                    $groupes[$vote['uid']]['pour'] = 0;
                    $groupes[$vote['uid']]['contre'] = 0;
                    $groupes[$vote['uid']]['abstention'] = 0;
                }
                $groupes[$vote['uid']]['totalVote'] += 1;
                $groupes[$vote['uid']]['pour'] += $vote['vote'] == "1" ? 1 : 0;
                $groupes[$vote['uid']]['contre'] += $vote['vote'] == "-1" ? 1 : 0;
                $groupes[$vote['uid']]['abstention'] += $vote['vote'] === "0" ? 1 : 0;

                if ($post['choice'] == $vote['vote']) {
                    $groupes[$vote['uid']]['score'] += $post["weight"] * 2;
                } else if ($vote['vote'] === "0") {
                    $groupes[$vote['uid']]['score'] += $post["weight"];
                } else {
                    $groupes[$vote['uid']]['score'] -= $post["weight"];
                }
                if (!isset($deputes[$vote['mpId']])) {
                    $deputes[$vote['mpId']] = array();
                    $deputes[$vote['mpId']]['score'] = 0;
                    $deputes[$vote['mpId']]['name'] = $vote['nameFirst'] . ' ' . $vote['nameLast'];
                    $deputes[$vote['mpId']]['totalVote'] = 0;
                    $deputes[$vote['mpId']]['pour'] = 0;
                    $deputes[$vote['mpId']]['contre'] = 0;
                    $deputes[$vote['mpId']]['abstention'] = 0;
                }
                $deputes[$vote['mpId']]['totalVote'] += 1;
                $deputes[$vote['mpId']]['pour'] += $vote['vote'] == "1" ? 1 : 0;
                $deputes[$vote['mpId']]['contre'] += $vote['vote'] == "-1" ? 1 : 0;
                $deputes[$vote['mpId']]['abstention'] += $vote['vote'] === "0" ? 1 : 0;

                if ($post['choice'] == $vote['vote']) {
                    $deputes[$vote['mpId']]['score'] += $post["weight"] * 2;
                } else if ($vote['vote'] === "0") {
                    $deputes[$vote['mpId']]['score'] += $post["weight"];
                } else {
                    $deputes[$vote['mpId']]['score'] -= $post["weight"];
                }
            }
        }
        foreach ($groupes as $key => $groupe) {
            $groupes[$key]['averageScore'] = $groupes[$key]['score'] / $groupes[$key]['totalVote'];
        }
        usort($deputes, function ($a, $b) {
            return $a > $b;
        });
        
        $data['groupes'] = $groupes;
        $data['deputes'] = $deputes;
        return $data;
    }
}
