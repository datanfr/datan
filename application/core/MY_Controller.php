<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_Controller
 * Base controller for pages requiring session (logged-in users, admin, etc.)
 * Re-enables CSRF protection so that public pages (which extend CI_Controller directly)
 * don't send a Set-Cookie header, allowing LSCache to cache them.
 */
class Auth_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');

        // Re-enable CSRF for auth pages (was disabled globally in config for LSCache compatibility)
        // This makes form_open() inject the hidden CSRF field, and verifies POST submissions
        $this->config->set_item('csrf_protection', TRUE);
        if ($this->input->method() === 'post') {
            $this->security->csrf_verify();
        }
        $this->security->csrf_set_cookie();
    }
}
