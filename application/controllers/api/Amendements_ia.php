<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les résumés IA des amendements (amendements_ia) — écriture depuis PoliticAnalysis uniquement.
 */
class Amendements_ia extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_key_model');
        $this->load->helper('url');
    }

    /**
     * POST /api/amendements_ia
     * Body JSON: {legislature, voteNumero, resume_ia, simplicite_ia}
     * Crée ou met à jour le résumé IA pour cet amendement (upsert).
     */
    public function index()
    {
        $method = $this->input->method(TRUE);

        $result = $this->api_auth->authenticate();
        if (isset($result['error'])) {
            $this->api_auth->response($result, $result['code']);
            return;
        }

        $permission = $this->api_auth->check_permission('/api/amendements_ia', $method);
        if ($permission !== true) {
            $this->api_auth->response($permission, $permission['code']);
            return;
        }

        if ($method !== 'POST') {
            $this->api_auth->response(['error' => true, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true);
        if (!is_array($input)) {
            $input = $this->input->post();
        }

        $required = ['legislature', 'voteNumero', 'resume_ia', 'simplicite_ia'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                $this->api_auth->response(['error' => true, 'message' => "Champ requis manquant : $field"], 400);
                return;
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

        $this->api_auth->response(['success' => true, 'legislature' => $legislature, 'voteNumero' => $voteNumero], 200);
    }
}
