<?php
class Search extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('search_model');
    }
    private function response($data, $code = 200)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($data));
    }

    public function index()
    {
        $results = [];
        $search = $this->input->get('q');

        $votes = $this->search_model->searchInVotes($search);
        foreach($votes as $vote){
            $results[] = [
                'text' => $vote['title'],
                'description' => '...' . strip_tags($vote['description']) . '...',
                'url' => 'votes/legislature-' . $vote['legislature'] .'/vote_'. $vote['voteNumero']
            ];
        }

        $deputes = $this->search_model->searchInDeputes($search);
        foreach($deputes as $depute){
            $results[] = [
                'text' => $depute['nameFirst'] . ' ' . $depute['nameLast'],
                'description' => $depute['libelle'],
                'url' => 'deputes/' . $depute['dptSlug'] .'/'. $depute['nameUrl']
            ];
        }
        // Return
        return $this->response($results);
    }
}
