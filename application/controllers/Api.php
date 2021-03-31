<?php
class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('departement_model');
        $this->load->model('deputes_model');
        $this->load->model('groupes_model');
        $this->load->model('votes_model');
        $this->load->model('breadcrumb_model');
        $this->load->model('newsletter_model');
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
        $model = $model . '_model';
        $gets = $this->input->get();
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

        return $this->response($data);
    }
}
