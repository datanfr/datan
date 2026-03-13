<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les votes décryptés (votes_datan) - CRUD complet
 */
class Decrypted_votes extends CI_Controller
{
    private $api_user = null;

    // Champs disponibles pour votes_datan
    private $available_fields = array(
        'id', 'legislature', 'voteNumero', 'vote_id', 'title', 'slug',
        'category', 'category_name', 'reading', 'reading_name',
        'description', 'state', 'created_at', 'modified_at',
        'created_by', 'created_by_name', 'modified_by', 'modified_by_name'
    );

    // Champs triables
    private $sortable_fields = array(
        'id', 'legislature', 'voteNumero', 'title', 'category',
        'state', 'created_at', 'modified_at'
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_key_model');
        $this->load->model('admin_model');
        $this->load->model('fields_model');
        $this->load->model('readings_model');
        $this->load->helper('url');
        $this->load->helper('text');

        $result = $this->api_auth->authenticate();
        if (isset($result['error'])) {
            $this->api_auth->response($result, $result['code']);
            exit;
        }
        $this->api_user = $result;
    }

    /**
     * Point d'entrée pour /api/decrypted_votes et /api/decrypted_votes/{id}
     */
    public function index($id = null)
    {
        $method = $this->input->method(TRUE);

        if ($id === null) {
            $permission = $this->api_auth->check_permission('/api/decrypted_votes', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }

            switch ($method) {
                case 'GET':
                    return $this->list_votes();
                case 'POST':
                    return $this->create_vote();
                default:
                    return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
            }
        } else {
            $permission = $this->api_auth->check_permission('/api/decrypted_votes/:id', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }

            switch ($method) {
                case 'GET':
                    return $this->get_vote($id);
                case 'PUT':
                    return $this->update_vote($id);
                case 'DELETE':
                    return $this->delete_vote($id);
                default:
                    return $this->api_auth->response(array('error' => true, 'message' => 'Method not allowed'), 405);
            }
        }
    }

    /**
     * GET /api/decrypted_votes
     * Liste les votes décryptés avec pagination, filtres et tri
     */
    private function list_votes()
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
        $state = $this->input->get('state');
        $legislature = $this->input->get('legislature');
        $category = $this->input->get('category');

        // Tri
        $sort = $this->input->get('sort') ?: 'created_at';
        if (!in_array($sort, $this->sortable_fields)) {
            $sort = 'created_at';
        }
        $order = strtoupper($this->input->get('order') ?: 'DESC');
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        // Compter le total (requête séparée)
        $this->db->from('votes_datan vd');
        $this->apply_filters($state, $legislature, $category);
        $total = $this->db->count_all_results();

        // Construire la sélection avec les champs demandés
        $select_parts = $this->build_select($fields);

        // Récupérer les résultats
        $this->db->select($select_parts);
        $this->db->from('votes_datan vd');
        $this->db->join('fields f', 'f.id = vd.category', 'left');
        $this->db->join('readings r', 'r.id = vd.reading', 'left');
        $this->db->join('users u1', 'u1.id = vd.created_by', 'left');
        $this->db->join('users u2', 'u2.id = vd.modified_by', 'left');
        $this->apply_filters($state, $legislature, $category);

        // Ajuster le tri pour les champs avec alias
        $sort_field = $this->get_sort_field($sort);
        $this->db->order_by($sort_field, $order);
        $this->db->limit($per_page, $offset);
        $votes = $this->db->get()->result_array();

        return $this->api_auth->response(array(
            'success' => true,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ),
            'filters' => array(
                'state' => $state,
                'legislature' => $legislature,
                'category' => $category
            ),
            'sort' => array('field' => $sort, 'order' => $order),
            'fields' => $fields,
            'count' => count($votes),
            'data' => $votes
        ));
    }

    /**
     * GET /api/decrypted_votes/{id}
     * Récupère un vote décrypté par son ID
     */
    private function get_vote($id)
    {
        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $select_parts = $this->build_select($fields);

        $this->db->select($select_parts);
        $this->db->from('votes_datan vd');
        $this->db->join('fields f', 'f.id = vd.category', 'left');
        $this->db->join('readings r', 'r.id = vd.reading', 'left');
        $this->db->join('users u1', 'u1.id = vd.created_by', 'left');
        $this->db->join('users u2', 'u2.id = vd.modified_by', 'left');
        $this->db->where('vd.id', $id);
        $vote = $this->db->get()->row_array();

        if (empty($vote)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Vote not found'), 404);
        }

        return $this->api_auth->response(array(
            'success' => true,
            'fields' => $fields,
            'data' => $vote
        ));
    }

    /**
     * POST /api/decrypted_votes
     * Crée un nouveau vote décrypté
     */
    private function create_vote()
    {
        $input = $this->api_auth->get_json_input();

        // Validation des champs requis
        $required = array('title', 'legislature', 'voteNumero', 'category');
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->api_auth->response(array(
                    'error' => true,
                    'message' => "Missing required field: $field"
                ), 400);
            }
        }

        // Préparer les données pour le modèle
        $_POST['title'] = $input['title'];
        $_POST['legislature'] = $input['legislature'];
        $_POST['vote_id'] = $input['voteNumero'];
        $_POST['category'] = $input['category'];
        $_POST['description'] = isset($input['description']) ? $input['description'] : '';
        $_POST['reading'] = isset($input['reading']) ? $input['reading'] : '';

        $result = $this->admin_model->create_vote($this->api_user['user_id']);

        if ($result === null) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Vote already exists for this legislature and vote number'
            ), 409);
        }

        // Récupérer le vote créé
        $vote_id = $this->db->insert_id();
        $vote = $this->admin_model->get_vote_datan($vote_id);

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Vote created successfully',
            'data' => $vote
        ), 201);
    }

    /**
     * PUT /api/decrypted_votes/{id}
     * Modifie un vote décrypté existant
     */
    private function update_vote($id)
    {
        $vote = $this->admin_model->get_vote_datan($id);

        if (empty($vote)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Vote not found'), 404);
        }

        // Vérifier les permissions
        if ($vote['state'] === 'published' && $this->api_user['user_type'] !== 'admin') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Only admins can modify published votes'
            ), 403);
        }

        $input = $this->api_auth->get_json_input();

        // Préparer les données
        $_POST['title'] = isset($input['title']) ? $input['title'] : $vote['title'];
        $_POST['category'] = isset($input['category']) ? $input['category'] : $vote['category'];
        $_POST['description'] = isset($input['description']) ? $input['description'] : $vote['description'];
        $_POST['state'] = isset($input['state']) ? $input['state'] : $vote['state'];
        $_POST['reading'] = isset($input['reading']) ? $input['reading'] : $vote['reading'];

        // Valider l'état
        if (!in_array($_POST['state'], array('draft', 'published'))) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Invalid state. Must be "draft" or "published"'
            ), 400);
        }

        $this->admin_model->modify_vote($id, $this->api_user['user_id']);

        $updated_vote = $this->admin_model->get_vote_datan($id);

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Vote updated successfully',
            'data' => $updated_vote
        ));
    }

    /**
     * DELETE /api/decrypted_votes/{id}
     * Supprime un vote décrypté
     */
    private function delete_vote($id)
    {
        if ($this->api_user['user_type'] !== 'admin') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Only admins can delete votes'
            ), 403);
        }

        $vote = $this->admin_model->get_vote_datan($id);

        if (empty($vote)) {
            return $this->api_auth->response(array('error' => true, 'message' => 'Vote not found'), 404);
        }

        $this->admin_model->delete_vote($id);

        return $this->api_auth->response(array(
            'success' => true,
            'message' => 'Vote deleted successfully',
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
     * Construit la clause SELECT avec les bons alias
     */
    private function build_select($fields)
    {
        $mapping = array(
            'id' => 'vd.id',
            'legislature' => 'vd.legislature',
            'voteNumero' => 'vd.voteNumero',
            'vote_id' => 'vd.vote_id',
            'title' => 'vd.title',
            'slug' => 'vd.slug',
            'category' => 'vd.category',
            'category_name' => 'f.name AS category_name',
            'reading' => 'vd.reading',
            'reading_name' => 'r.name AS reading_name',
            'description' => 'vd.description',
            'state' => 'vd.state',
            'created_at' => 'vd.created_at',
            'modified_at' => 'vd.modified_at',
            'created_by' => 'vd.created_by',
            'created_by_name' => 'u1.name AS created_by_name',
            'modified_by' => 'vd.modified_by',
            'modified_by_name' => 'u2.name AS modified_by_name'
        );

        $select_parts = array();
        foreach ($fields as $field) {
            if (isset($mapping[$field])) {
                $select_parts[] = $mapping[$field];
            }
        }

        return implode(', ', $select_parts);
    }

    /**
     * Retourne le champ de tri avec le bon préfixe
     */
    private function get_sort_field($sort)
    {
        $mapping = array(
            'id' => 'vd.id',
            'legislature' => 'vd.legislature',
            'voteNumero' => 'vd.voteNumero',
            'title' => 'vd.title',
            'category' => 'vd.category',
            'state' => 'vd.state',
            'created_at' => 'vd.created_at',
            'modified_at' => 'vd.modified_at'
        );

        return isset($mapping[$sort]) ? $mapping[$sort] : 'vd.created_at';
    }

    /**
     * Applique les filtres à la requête courante
     */
    private function apply_filters($state, $legislature, $category)
    {
        if ($state && in_array($state, array('draft', 'published'))) {
            $this->db->where('vd.state', $state);
        }
        if ($legislature) {
            $this->db->where('vd.legislature', $legislature);
        }
        if ($category) {
            $this->db->where('vd.category', $category);
        }
    }

    /**
     * Retourne les métadonnées de l'endpoint
     */
    public function meta()
    {
        $categories = $this->fields_model->get_fields();
        $readings = $this->readings_model->get();

        return $this->api_auth->response(array(
            'success' => true,
            'endpoint' => '/api/decrypted_votes',
            'description' => 'Votes décryptés par Datan (votes_datan)',
            'methods' => array(
                'GET /api/decrypted_votes' => 'Lister les votes décryptés',
                'GET /api/decrypted_votes/{id}' => 'Voir un vote décrypté',
                'POST /api/decrypted_votes' => 'Créer un vote décrypté',
                'PUT /api/decrypted_votes/{id}' => 'Modifier un vote décrypté',
                'DELETE /api/decrypted_votes/{id}' => 'Supprimer un vote décrypté (admin uniquement)'
            ),
            'post_fields' => array(
                'required' => array(
                    'title' => 'Titre du vote',
                    'legislature' => 'Numéro de législature',
                    'voteNumero' => 'Numéro du vote dans la législature',
                    'category' => 'ID de la catégorie (voir categories ci-dessous)'
                ),
                'optional' => array(
                    'description' => 'Description du vote (défaut: vide)',
                    'reading' => 'ID de la lecture (voir readings ci-dessous, défaut: null)'
                ),
                'auto_generated' => array(
                    'id', 'vote_id', 'slug', 'state (draft)', 'created_at', 'created_by', 'created_by_name'
                ),
                'example' => array(
                    'title' => 'Projet de loi de finances 2025',
                    'legislature' => '17',
                    'voteNumero' => '1470',
                    'category' => '1',
                    'description' => 'Vote sur le budget général',
                    'reading' => '1'
                )
            ),
            'put_fields' => array(
                'optional' => array(
                    'title' => 'Titre du vote',
                    'category' => 'ID de la catégorie',
                    'description' => 'Description du vote',
                    'reading' => 'ID de la lecture',
                    'state' => 'État du vote (draft, published)'
                ),
                'auto_generated' => array(
                    'slug', 'modified_at', 'modified_by', 'modified_by_name'
                ),
                'notes' => 'Seuls les admins peuvent modifier un vote publié. Seuls les champs envoyés sont modifiés.',
                'example' => array(
                    'title' => 'Titre modifié',
                    'state' => 'published'
                )
            ),
            'available_fields' => $this->available_fields,
            'sortable_fields' => $this->sortable_fields,
            'filters' => array(
                'state' => 'État du vote (draft, published)',
                'legislature' => 'Numéro de législature',
                'category' => 'ID de la catégorie'
            ),
            'pagination' => array(
                'page' => 'Numéro de page (défaut: 1)',
                'per_page' => 'Résultats par page (défaut: 50, max: 500)'
            ),
            'sorting' => array(
                'sort' => 'Champ de tri (défaut: created_at)',
                'order' => 'Ordre de tri: ASC ou DESC (défaut: DESC)'
            ),
            'categories' => $categories,
            'readings' => $readings
        ));
    }
}
