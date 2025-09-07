<?php
class Campaign extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('campaign_model');
    }
    private function response($data, $code = 200)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($data));
    }

    public function current_active_campaigns()
    {
        return $this->response($this->campaign_model->get_current_active_campaigns());
    }

}
