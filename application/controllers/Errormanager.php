<?php
class Errormanager extends CI_Controller
{
    public function error404()
    {
        show_404($this->functions_datan->get_404_infos());;
    }
}
