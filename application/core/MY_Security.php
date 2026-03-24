<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Security
 *
 * Extends CI_Security to allow late CSRF initialisation.
 * When csrf_protection is disabled globally (for LSCache), Auth_Controller
 * can call csrf_init() to set up token names, hash and cookie on demand.
 */
class MY_Security extends CI_Security {

    /**
     * Initialise CSRF properties that were skipped because
     * csrf_protection was FALSE at construct time.
     *
     * @return CI_Security
     */
    public function csrf_init()
    {
        foreach (array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key)
        {
            if (NULL !== ($val = config_item($key)))
            {
                $this->{'_'.$key} = $val;
            }
        }

        if ($cookie_prefix = config_item('cookie_prefix'))
        {
            $this->_csrf_cookie_name = $cookie_prefix . $this->_csrf_cookie_name;
        }

        $this->_csrf_set_hash();

        return $this;
    }
}
