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
        $type = $this->input->get('type'); // optional filter

        $return = $this->search_model->searchInAll($search, 5, 10);
        foreach($return as $x){
            if ($type === 'ville' && $x['source'] !== 'ville') {
                continue; // skip anything but cities
            }
            $url = $x['url'];
            if ($type === 'ville') {
                // convert deputes/slug/ville_commune to elections/resultats/slug/commune_slug
                // original pattern: deputes/{dptSlug}/ville_{commune_slug}
                if (strpos($url, 'deputes/') === 0) {
                    $url = str_replace('deputes/', 'elections/resultats/', $url);
                    $url = str_replace('ville_', '', $url);
                }
            }
            $results[] = [
                'text' => highlight_phrase($x['title_search'], $search, '<span class="text-primary">', '</span>'),
                'url' => $url,
                'source' => $x['source'],
            ];
        }
        // Return
        return $this->response($results);
    }

    public function index($query){
      $data['query'] = urldecode($query);
      $data['results'] = $this->search_model->searchInAll($query, NULL, NULL);
      $data['count'] = count($data['results']);
      $data['max_entries'] = 10;

      $data['results'] = $this->search_model->sort($data['results'], $data['query']);

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
