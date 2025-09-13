<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GeneralModalHook {

    public function check_modal()
    {
        $CI =& get_instance(); // get CodeIgniter instance

        // Check if modal was already shown
        if (!$CI->session->userdata('popup_shown')) {
            $CI->session->set_userdata('popup_shown', true);
            $CI->show_popup = true;
        } else {
            $CI->show_popup = false;
        }

        // Desactivate general modal 
        $CI->show_popup = false; // Comment if you do not want to desactivate general modal
    }
}
