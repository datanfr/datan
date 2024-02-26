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

        $votes = $this->search_model->searchInAll($search);
        foreach($votes as $vote){
            $results[] = [
                'text' => $vote['title'],
                'description' => $vote['description'],
                'url' => $vote['url'],
                'source' => $vote['source'],
                'date' => $vote['date_modified']
            ];
        }
        // Return
        return $this->response($results);
    }
}
