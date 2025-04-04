<?php

class Iframe extends CI_Controller
{
    public function __construct() 
    {
        parent::__construct();

    }

    public function index()
    {
        $this->load->view('iframe/index');
    }

    public function showDeputeIframe($mpId) 
    {
        $section = isset($_GET['section']) ? $_GET['section'] : null;
        $title = isset($_GET['title']) ? $_GET['title'] : 'show';
        $categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];

        $this->load->view('iframe/depute', [
            'categories' => $categories, 
    
        ]);
    }

}
