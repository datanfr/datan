<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * Base controller for all public pages.
 * Extends CI_Controller and serves as the parent for Auth_Controller.
 * Tracking logic is handled client-side (JS) to preserve LSCache compatibility.
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

}