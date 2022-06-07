<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Export extends CI_Controller {

    public function set_session($value)
    {
        $this->session->set_userdata('already_closed', $value);
    }
}
