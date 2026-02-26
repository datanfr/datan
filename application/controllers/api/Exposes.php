<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les exposés de motifs (exposes) - CRUD
 */
class Exposes extends CI_Controller
{
    private $api_user = null;

    // Champs disponibles pour exposes
    private $available_fields = array(
        'id', 'legislature', 'voteNumero', 'exposeOriginal',
        'exposeSummary', 'exposeSummaryPublished', 'dateMaj'
    );

    // Champs triables
    private $sortable_fields = array(
        'id', 'legislature', 'voteNumero', 'dateMaj'
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_key_model');
        $this->load->model('exposes_model');

        $result = $this->api_auth->authenticate();
        if (isset($result['error'])) {
            $this->api_auth->response($result, $result['code']);
            exit;
        }
        $this->api_user = $result;
    }

    /**
     * Point d'entrée pour /api/exposes et /api/exposes/{id}
     */
    public function index($id = null)
    {
        $method = $this->input->method(TRUE);

        if ($id === null) {
            $permission = $this->api_auth->check_permission('/api/exposes', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }

            switch ($method) {
                case 'GET':
                    return $this->list_exposes();
                case 'POST':
                    return $this->create_expose();
                default:
                    return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
            }
        } else {
            $permission = $this->api_auth->check_permission('/api/exposes/:id', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }

            switch ($method) {
                case 'GET':
                    return $this->get_expose($id);
                case 'PUT':
                    return $this->update_expose($id);
                case 'DELETE':
                    return $this->delete_expose($id);
                default:
                    return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
            }
        }
    }

    /**
     * GET /api/exposes
     * Liste les exposés avec pagination et filtres
     */
    private function list_exposes()
    {
        // Pagination
        $page = max(1, (int)$this->input->get('page') ?: 1);
        $per_page = min(500, max(1, (int)$this->input->get('per_page') ?: 50));
        $offset = ($page - 1) * $per_page;

        // Sélection des champs
        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        // Filtres
        $legislature = $this->input->get('legislature');
        $status = $this->input->get('status'); // 'pending', 'done', 'all'

        // Tri
        $sort = $this->input->get('sort') ?: 'dateMaj';
        if (!in_array($sort, $this->sortable_fields)) {
            $sort = 'dateMaj';
        }
        $order = strtoupper($this->input->get('order') ?: 'DESC');
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        // Compter le total (requête séparée)
        $this->db->from('exposes');
        $this->apply_filters($legislature, $status);
        $total = $this->db->count_all_results();

        // Récupérer les résultats avec pagination
        $this->db->select(implode(', ', $fields));
        $this->db->from('exposes');
        $this->apply_filters($legislature, $status);
        $this->db->order_by($sort, $order);
        $this->db->limit($per_page, $offset);
        $exposes = $this->db->get()->result_array();

        return $this->api_auth->response(array(
            'success' => true,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ),
            'filters' => array(
                'legislature' => $legislature,
                'status' => $status
            ),
            'sort' => array('field' => $sort, 'order' => $order),
            'fields' => $fields,
            'count' => count($exposes),
            'data' => $exposes
        ));
    }

    /**
     * GET /api/exposes/{id}
     * Récupère un exposé par son ID
     */
    private function get_expose($id)
    {
        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $this->db->select(implode(', ', $fields));
        $expose = $this->db->get_where('exposes', array('id' => $id))->row_array();

        if (empty($expose)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Expose not found'), 404);
        }

        return $this->api_auth->response(array(
            'success' => true,
            'fields' => $fields,
            'data' => $expose
        ));
    }

    /**
     * GET /api/exposes/by_vote/{legislature}/{voteNumero}
     * Récupère un exposé par legislature et voteNumero
     */
    public function by_vote($legislature, $voteNumero)
    {
        $method = $this->input->method(TRUE);

        if ($method !== 'GET') {
            return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
        }

        $permission = $this->api_auth->check_permission('/api/exposes/{id}', $method);
        if ($permission !== true) {
            return $this->api_auth->response($permission, $permission['code']);
        }

        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $this->db->select(implode(', ', $fields));
        $expose = $this->db->get_where('exposes', array(
            'legislature' => $legislature,
            'voteNumero' => $voteNumero
        ))->row_array();

        if (empty($expose)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Expose not found'), 404);
        }

        return $this->api_auth->response(array(
            'success' => true,
            'fields' => $fields,
            'data' => $expose
        ));
    }

    /**
     * POST /api/exposes
     * Crée un nouvel exposé
     */
    private function create_expose()
    {
        $input = $this->api_auth->get_json_input();

        // Validation des champs requis
        $required = array('legislature', 'voteNumero');
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                return $this->api_auth->response(array(
                    'error' => true,
                    'message' => "Missing required field: $field"
                ), 400);
            }
        }

        // Vérifier si l'exposé existe déjà
        $existing = $this->exposes_model->get_expose_by_vote($input['legislature'], $input['voteNumero']);
        if (!empty($existing)) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Expose already exists for this legislature and voteNumero'
            ), 409);
        }

        $data = array(
            'legislature' => $input['legislature'],
            'voteNumero' => $input['voteNumero'],
            'exposeOriginal' => isset($input['exposeOriginal']) ? $input['exposeOriginal'] : null,
            'exposeSummary' => isset($input['exposeSummary']) ? $input['exposeSummary'] : null,
            'exposeSummaryPublished' => isset($input['exposeSummaryPublished']) ? $input['exposeSummaryPublished'] : null,
            'dateMaj' => date('Y-m-d H:i:s')
        );

        $this->db->insert('exposes', $data);
        $expose_id = $this->db->insert_id();

        $expose = $this->exposes_model->get_expose($expose_id);

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Expose created successfully',
            'data' => $expose
        ), 201);
    }

    /**
     * PUT /api/exposes/{id}
     * Modifie un exposé existant
     */
    private function update_expose($id)
    {
        $expose = $this->exposes_model->get_expose($id);

        if (empty($expose)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Expose not found'), 404);
        }

        $input = $this->api_auth->get_json_input();

        $data = array(
            'dateMaj' => date('Y-m-d H:i:s')
        );

        // Champs modifiables
        if (isset($input['exposeOriginal'])) {
            $data['exposeOriginal'] = $input['exposeOriginal'];
        }
        if (isset($input['exposeSummary'])) {
            $data['exposeSummary'] = $input['exposeSummary'];
        }
        if (isset($input['exposeSummaryPublished'])) {
            $data['exposeSummaryPublished'] = $input['exposeSummaryPublished'];
        }

        $this->db->where('id', $id);
        $this->db->update('exposes', $data);

        $updated_expose = $this->exposes_model->get_expose($id);

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Expose updated successfully',
            'data' => $updated_expose
        ));
    }

    /**
     * DELETE /api/exposes/{id}
     * Supprime un exposé
     */
    private function delete_expose($id)
    {
        if ($this->api_user['user_type'] !== 'admin') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Only admins can delete exposes'
            ), 403);
        }

        $expose = $this->exposes_model->get_expose($id);

        if (empty($expose)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Expose not found'), 404);
        }

        $this->db->delete('exposes', array('id' => $id));

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Expose deleted successfully',
            'deleted_id' => (int)$id
        ));
    }

    /**
     * Parse et valide les champs demandés
     */
    private function parse_fields()
    {
        $fields_param = $this->input->get('fields');
        if ($fields_param) {
            $requested_fields = array_map('trim', explode(',', $fields_param));
            $fields = array_intersect($requested_fields, $this->available_fields);
            if (empty($fields)) {
                return array(
                    'error' => true,
                    'message' => 'Invalid fields. Available: ' . implode(', ', $this->available_fields)
                );
            }
            return $fields;
        }
        return $this->available_fields;
    }

    /**
     * Applique les filtres à la requête courante
     */
    private function apply_filters($legislature, $status)
    {
        if ($legislature) {
            $this->db->where('legislature', $legislature);
        }
        if ($status === 'pending') {
            $this->db->where('exposeSummaryPublished IS NULL', null, false);
        } elseif ($status === 'done') {
            $this->db->where('exposeSummaryPublished IS NOT NULL', null, false);
        }
    }

    /**
     * Retourne les métadonnées de l'endpoint
     */
    public function meta()
    {
        return $this->api_auth->response(array(
            'success' => true,
            'endpoint' => '/api/exposes',
            'description' => 'Exposés des motifs des amendements avec résumés IA',
            'methods' => array('GET', 'POST', 'PUT', 'DELETE'),
            'available_fields' => $this->available_fields,
            'sortable_fields' => $this->sortable_fields,
            'filters' => array(
                'legislature' => 'Numéro de législature',
                'status' => 'Statut: pending (non publié), done (publié), all (tous)'
            ),
            'pagination' => array(
                'page' => 'Numéro de page (défaut: 1)',
                'per_page' => 'Résultats par page (défaut: 50, max: 500)'
            ),
            'sorting' => array(
                'sort' => 'Champ de tri (défaut: dateMaj)',
                'order' => 'Ordre de tri: ASC ou DESC (défaut: DESC)'
            ),
            'special_routes' => array(
                '/api/exposes/by_vote/{legislature}/{voteNumero}' => 'Récupérer un exposé par legislature et voteNumero'
            )
        ));
    }

    /**
     * Statistiques des exposés
     */
    public function stats()
    {
        $method = $this->input->method(TRUE);

        if ($method !== 'GET') {
            return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
        }

        $permission = $this->api_auth->check_permission('/api/exposes', $method);
        if ($permission !== true) {
            return $this->api_auth->response($permission, $permission['code']);
        }

        $total = $this->db->count_all('exposes');
        $done = $this->exposes_model->get_n_done();
        $pending = $this->exposes_model->get_n_pending();

        return $this->api_auth->response(array(
            'success' => true,
            'data' => array(
                'total' => $total,
                'done' => $done,
                'pending' => $pending,
                'percentage_done' => $total > 0 ? round(($done / $total) * 100, 2) : 0
            )
        ));
    }
}
