<?php
class Api extends CI_Controller
{
    // FORBIDDEN LIST
    private $modelAllowed = array();
    private $methodAllowed = array(
      'newsletter/create_newsletter',
      'deputes/get_deputes_last',
      'votes/get_last_votes_datan',
      'votes/request_vote_datan',
      'votes/get_vote_deputes',
      'votes/get_vote_groupes_simplified',
      'quizz/get_questions_api',
      'city/get_mps_city'
    );
    private $methodForbidden = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('votes_model');
        $this->load->model('newsletter_model');
        $this->load->model('quizz_model');
        $this->load->model('deputes_model');
        $this->load->model('city_model');
    }

    private function response($data, $code = 200)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($data));
    }

    public function index($model, $method)
    {

        if ((!in_array($model, $this->modelAllowed) && !in_array($model . '/' . $method, $this->methodAllowed)) || in_array($model . '/' . $method, $this->methodForbidden)) {
          return $this->response(array('error' => true, 'message' => 'This model is forbidden', 403));
        }

        $model = $model . '_model';
        $gets = $this->input->get();
        foreach ($gets as $key => $value) {
            switch ($value) {
              case '':
                $gets[$key] = NULL;
                break;

              case 'TRUE':
                $gets[$key] = TRUE;
                break;

              case 'FALSE':
                $gets[$key] = FALSE;
                break;

              default:
                $gets[$key] = $value;
                break;
            }

        }
        if (!$this->$model) {
            return $this->response(array('error' => true, 'message' => 'The model ' . $model . ' doesn\'t exist'), 405);
        }
        if (!is_callable(array($this->$model, $method))) {
            return $this->response(array('error' => true, 'message' => 'The method ' . $method . ' doesn\'t exist'), 405);
        }
        try {
            $data = call_user_func_array(array($this->$model, $method), $gets);
        } catch (\ArgumentCountError $e) {
            return $this->response(array('error' => true, 'message' => $e->getMessage()), 405);
        }
        catch(\Exception $e){
            return $this->response(array('error' => true, 'message' => $e->getMessage()), 500);
        }

        // Caching
        if(!in_array($_SERVER['REMOTE_ADDR'], localhost()) && !$this->session->userdata('logged_in')){
          if ($method == 'get_mps_city') {
            $this->output->cache("4320"); // Caching enable for 3 days (1440 minutes per day)
          }
        }


        // Header
        header("Access-Control-Allow-Origin: https://datan-quiz.web.app");
        header("Access-Control-Allow-Origin: https://quizz.datan.fr");
        header("Access-Control-Allow-Methods: GET");
        // Return
        return $this->response($data);
    }
}
