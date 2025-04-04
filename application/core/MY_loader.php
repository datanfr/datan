<?php

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Custom loader to dynamically load services by their name or an optional alias.

 * @param string $serviceName The name of the service to load.
 * @param string|null $alias (optional) Alias for the service. 
 *
 * Example usage:
 * $this->load->service('EmailService');       // Loads the service using its name.
 * $this->load->service('EmailService', 'mailService'); // Loads the service and assigns an alias.
 * 
 * After loading, you can use the service like this:
 * $this->EmailService->sendEmail();           // Using the service directly by its class name.
 * $this->mailService->sendEmail();            // Using the service via its alias (if provided).
 */


class MY_Loader extends CI_Loader 
{
    public function service($serviceName, $alias = null)
    {
        $servicePath = APPPATH . 'services/' . $serviceName . '.php';
    
        if (!file_exists($servicePath)) {
            throw new Exception("Service " . $serviceName . " not found");
        }
    
        require_once $servicePath;
    

        //obtient une référence à l'instance principale de CI
        $CI =& get_instance();
        $property = $alias ?? lcfirst($serviceName);
    
        if (!isset($CI->$property)) {
            $CI->$property = new $serviceName();
        }
    
        return $CI->$property;
    }
}




