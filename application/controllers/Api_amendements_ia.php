<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les résumés IA des amendements (amendements_ia) — écriture depuis PoliticAnalysis uniquement.
 *
 * Le controller est volontairement hors du dossier controllers/api/ : CI3 résout
 * le segment "api" vers controllers/Api.php avant le sous-dossier (cf.
 * CI_Router::_validate_request), donc on ne peut pas y placer un controller utile.
 */
class Api_amendements_ia extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_key_model');
        $this->load->helper('url');
    }

    private function send_json($data, $code = 200)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * POST /api/amendements_ia
     * Body JSON: {legislature, voteNumero, resume_ia, simplicite_ia}
     * Crée ou met à jour le résumé IA pour cet amendement (upsert).
     */
    public function index()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->send_json(['error' => true, 'message' => 'Method not allowed'], 405);
        }

        $auth = $this->input->get_request_header('Authorization', TRUE);
        $token = (is_string($auth) && strpos($auth, 'Bearer ') === 0) ? substr($auth, 7) : '';
        if (!$token) {
            return $this->send_json(['error' => true, 'message' => 'Missing Bearer token'], 401);
        }

        $api_user = $this->api_key_model->validate_key($token);
        if (!$api_user) {
            return $this->send_json(['error' => true, 'message' => 'Invalid API key'], 401);
        }

        if (!$this->api_key_model->has_permission($api_user, '/api/amendements_ia', 'POST')) {
            return $this->send_json(['error' => true, 'message' => 'Forbidden'], 403);
        }

        $input = json_decode($this->input->raw_input_stream, true);
        if (!is_array($input)) {
            $input = $this->input->post();
        }

        $required = ['legislature', 'voteNumero', 'resume_ia', 'simplicite_ia'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                return $this->send_json(['error' => true, 'message' => "Champ requis manquant : $field"], 400);
            }
        }

        $legislature  = (int) $input['legislature'];
        $voteNumero   = (string) $input['voteNumero'];
        $resumeIa     = substr((string) $input['resume_ia'], 0, 220);
        $simpliciteIa = max(1, min(5, (int) $input['simplicite_ia']));

        $this->db->query(
            "INSERT INTO amendements_ia (legislature, voteNumero, resume_ia, simplicite_ia)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 resume_ia     = VALUES(resume_ia),
                 simplicite_ia = VALUES(simplicite_ia),
                 updated_at    = NOW()",
            array($legislature, $voteNumero, $resumeIa, $simpliciteIa)
        );

        return $this->send_json(['success' => true, 'legislature' => $legislature, 'voteNumero' => $voteNumero], 200);
    }
}
