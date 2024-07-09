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

        $return = $this->search_model->searchInAll($search, FALSE, 5, 10);
        foreach($return as $x){
            $results[] = [
                'text' => highlight_phrase($x['title_search'], $search, '<span class="text-primary">', '</span>'),
                'url' => $x['url'],
                'source' => $x['source'],
            ];
        }
        // Return
        return $this->response($results);
    }

    public function index($query){
      $data['query'] = urldecode($query);
      $data['results'] = $this->search_model->searchInAll($query, TRUE, NULL, NULL);

      echo "this one is working<br><br>";
      echo json_encode($data['results'], JSON_UNESCAPED_UNICODE);
      
      $data['count'] = count($data['results']);
      $data['max_entries'] = 10;

      $data['results'] = $this->search_model->sort($data['results'], $data['query']);

      // BUG HERE TO SOLVE!
      echo "<br><br>this one is not working";
      echo json_encode($data['results'], JSON_UNESCAPED_UNICODE);

      //Meta
      $data['url'] = $this->meta_model->get_url();
      $data['title_meta'] = "« " . $data['query'] . " » - Cherchez un député, une ville, un groupe | Datan";
      $data['description_meta'] = "Cherchez votre député, votre ville, un groupe politique, ou un scrutin de l'Assemblée nationale sur Datan.";
      $data['seoNoFollow'] = TRUE;
      //Open Graph
      $data['ogp'] = $this->meta_model->get_ogp('home', $data['title_meta'], $data['description_meta'], $data['url'], $data);
      // Load views
      $this->load->view('templates/header', $data);
      $this->load->view('search/index', $data);
      $this->load->view('templates/footer', $data);
    }
}
