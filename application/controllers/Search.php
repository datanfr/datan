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

    public function index_api()
    {
        $results = [];
        $search = $this->input->get('q');

        $return = $this->search_model->searchInAll($search);
        foreach($return as $x){
            $results[] = [
                'text' => $x['title'],
                'description' => $x['description'],
                'url' => $x['url'],
                'source' => $x['source'],
                // 'score' => $x['score']
            ];
        }
        // Return
        return $this->response($results);
    }

    public function index($search){

      $data['results'] = $this->search_model->searchInAll($search);

      //Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "Cherchez un député | Datan";
      $data['description_meta'] = "Cherchez votre député, votre ville, un groupe politique, ou un scrutin de l'Assemblée nationale sur Datan.";
      //Open Graph
      $data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('search/index', $data);
      $this->load->view('templates/footer', $data);
    }
}