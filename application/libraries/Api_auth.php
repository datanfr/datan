<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Bibliothèque partagée pour l'authentification API
 */
class Api_auth
{
    protected $CI;
    private $api_user = null;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('api_key_model');
    }

    /**
     * Authentifie la requête via clé API
     * @return array|null Les infos utilisateur ou null si échec
     */
    public function authenticate()
    {
        $auth_header = $this->CI->input->get_request_header('Authorization', TRUE);

        if (empty($auth_header)) {
            return array('error' => true, 'message' => 'Authorization header missing', 'code' => 401);
        }

        if (strpos($auth_header, 'Bearer ') !== 0) {
            return array('error' => true, 'message' => 'Invalid authorization format. Use: Bearer <api_key>', 'code' => 401);
        }

        $api_key = substr($auth_header, 7);
        $this->api_user = $this->CI->api_key_model->validate_key($api_key);

        if ($this->api_user === null) {
            return array('error' => true, 'message' => 'Invalid or inactive API key', 'code' => 401);
        }

        // Vérifier que l'utilisateur est admin ou writer
        if (!in_array($this->api_user['user_type'], array('admin', 'writer'))) {
            return array('error' => true, 'message' => 'Insufficient permissions', 'code' => 403);
        }

        return $this->api_user;
    }

    /**
     * Retourne l'utilisateur API authentifié
     */
    public function get_user()
    {
        return $this->api_user;
    }

    /**
     * Vérifie les permissions pour un endpoint et une méthode
     */
    public function check_permission($endpoint, $method)
    {
        if (!$this->CI->api_key_model->has_permission($this->api_user, $endpoint, $method)) {
            return array(
                'error' => true,
                'message' => "Permission denied for $method $endpoint",
                'code' => 403
            );
        }
        return true;
    }

    /**
     * Retourne une réponse JSON
     */
    public function response($data, $code = 200)
    {
        return $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($data));
    }

    /**
     * Récupère les données JSON du body de la requête
     */
    public function get_json_input()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: array();
    }
}
