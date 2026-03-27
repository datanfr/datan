<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API read-only for explicitly authorized SQL tables.
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Api_auth $api_auth
 * @property Api_key_model $api_key_model
 */
class Tables extends CI_Controller
{
    private $api_user = null;

    private $authorized_tables = array(
        'amendements',
        'deputes_last',
        'dossiers',
        'dossiers_votes',
        'organes',
        'votes',
        'votes_amendments',
        'votes_datan',
        'votes_groupes',
        'votes_info',
        'votes_scores'
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_key_model');

        $result = $this->api_auth->authenticate();
        if (isset($result['error'])) {
            $this->api_auth->response($result, $result['code']);
            exit;
        }
        $this->api_user = $result;
    }

    /**
     * Point d'entree pour /api/tables et /api/tables/{table}
     */
    public function index($table = null)
    {
        $method = $this->input->method(TRUE);

        if ($method !== 'GET') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Method not allowed. Only GET is supported for tables.'
            ), 405);
        }

        if ($table === null) {
            $permission = $this->api_auth->check_permission('/api/tables', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }

            return $this->list_tables();
        }

        $permission = $this->api_auth->check_permission('/api/tables/:table', $method);
        if ($permission !== true) {
            return $this->api_auth->response($permission, $permission['code']);
        }

        return $this->get_table($table);
    }

    private function list_tables()
    {
        $tables = array();

        foreach ($this->authorized_tables as $table) {
            $tables[] = array(
                'name' => $table,
                'fields' => $this->db->list_fields($table)
            );
        }

        return $this->api_auth->response(array(
            'success' => true,
            'count' => count($tables),
            'data' => $tables
        ));
    }

    private function get_table($table)
    {
        if (!in_array($table, $this->authorized_tables, true)) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Table not authorized'
            ), 403);
        }

        if (!$this->db->table_exists($table)) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Table not found'
            ), 404);
        }

        $available_fields = $this->db->list_fields($table);
        $fields = $this->parse_fields($available_fields);
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $page = max(1, (int) $this->input->get('page') ?: 1);
        $per_page = min(100000, max(1, (int) $this->input->get('per_page') ?: 50));
        $offset = ($page - 1) * $per_page;

        $sort = $this->input->get('sort');
        if (!$sort || !in_array($sort, $available_fields, true)) {
            $sort = $available_fields[0];
        }

        $order = strtoupper($this->input->get('order') ?: 'ASC');
        if (!in_array($order, array('ASC', 'DESC'), true)) {
            $order = 'ASC';
        }

        $total = $this->db->count_all($table);

        $this->db->select(implode(', ', $fields));
        $this->db->from($table);
        $this->db->order_by($sort, $order);
        $this->db->limit($per_page, $offset);
        $rows = $this->db->get()->result_array();

        return $this->api_auth->response(array(
            'success' => true,
            'table' => $table,
            'fields' => $available_fields,
            'selected_fields' => $fields,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ),
            'sort' => array(
                'field' => $sort,
                'order' => $order
            ),
            'count' => count($rows),
            'data' => $rows
        ));
    }

    private function parse_fields($available_fields)
    {
        $fields_param = $this->input->get('fields');
        if ($fields_param) {
            $requested_fields = array_map('trim', explode(',', $fields_param));
            $fields = array_values(array_intersect($requested_fields, $available_fields));

            if (empty($fields)) {
                return array(
                    'error' => true,
                    'message' => 'Invalid fields. Available: ' . implode(', ', $available_fields)
                );
            }

            return $fields;
        }

        return $available_fields;
    }

    public function meta()
    {
        return $this->api_auth->response(array(
            'success' => true,
            'endpoint' => '/api/tables',
            'description' => 'Lecture seule des tables SQL explicitement autorisees',
            'methods' => array('GET'),
            'authorized_tables' => $this->authorized_tables,
            'usage' => array(
                'list_tables' => '/api/tables',
                'read_table' => '/api/tables/{table}'
            ),
            'pagination' => array(
                'page' => 'Numero de page (defaut: 1)',
                'per_page' => 'Resultats par page (defaut: 50, max: 100000)'
            ),
            'sorting' => array(
                'sort' => 'Champ de tri de la table',
                'order' => 'Ordre de tri: ASC ou DESC (defaut: ASC)'
            ),
            'field_selection' => array(
                'fields' => 'Liste de champs separes par des virgules'
            )
        ));
    }
}