<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les votes non décryptés (votes_info NOT IN votes_datan) - Lecture seule
 */
class Non_decrypted_votes extends CI_Controller
{
    private $api_user = null;

    // Champs disponibles pour votes_info
    private $available_fields = array(
        'voteId', 'legislature', 'voteNumero', 'organeRef', 'dateScrutin',
        'sessionREF', 'seanceRef',
        'titre', 'sortCode', 'codeTypeVote', 'libelleTypeVote',
        'nombreVotants', 'decomptePour', 'decompteContre', 'decompteAbs', 'decompteNv',
        'voteType', 'amdt', 'article'
    );

    // Champs triables
    private $sortable_fields = array(
        'voteId', 'legislature', 'voteNumero', 'dateScrutin', 'sortCode',
        'nombreVotants', 'decomptePour', 'decompteContre'
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
     * Point d'entrée pour /api/non_decrypted_votes
     */
    public function index($id = null)
    {
        $method = $this->input->method(TRUE);

        if ($method !== 'GET') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Method not allowed. Only GET is supported.'
            ), 405);
        }

        $permission = $this->api_auth->check_permission('/api/non_decrypted_votes', $method);
        if ($permission !== true) {
            return $this->api_auth->response($permission, $permission['code']);
        }

        if ($id === null) {
            return $this->list_votes();
        } else {
            return $this->get_vote($id);
        }
    }

    /**
     * GET /api/non_decrypted_votes
     * Liste les votes qui ne sont pas encore décryptés
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
        $legislature = $this->input->get('legislature');
        $year = $this->input->get('year');
        $month = $this->input->get('month');
        $vote_type = $this->input->get('vote_type');
        $sort_code = $this->input->get('sort_code');

        // Tri
        $sort = $this->input->get('sort') ?: 'dateScrutin';
        if (!in_array($sort, $this->sortable_fields)) {
            $sort = 'dateScrutin';
        }
        $order = strtoupper($this->input->get('order') ?: 'DESC');
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        // Compter le total (requête séparée)
        $this->db->from('votes_info vi');
        $this->db->join('votes_datan vd', 'vi.voteId = vd.vote_id', 'left');
        $this->db->where('vd.id IS NULL', null, false);
        $this->apply_filters($legislature, $year, $month, $vote_type, $sort_code);
        $total = $this->db->count_all_results();

        // Construire la sélection avec les champs demandés
        $select_parts = array();
        foreach ($fields as $field) {
            $select_parts[] = 'vi.' . $field;
        }

        // Récupérer les résultats avec pagination
        $this->db->select(implode(', ', $select_parts));
        $this->db->from('votes_info vi');
        $this->db->join('votes_datan vd', 'vi.voteId = vd.vote_id', 'left');
        $this->db->where('vd.id IS NULL', null, false);
        $this->apply_filters($legislature, $year, $month, $vote_type, $sort_code);
        $this->db->order_by('vi.' . $sort, $order);
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
                'legislature' => $legislature,
                'year' => $year,
                'month' => $month,
                'vote_type' => $vote_type,
                'sort_code' => $sort_code
            ),
            'sort' => array('field' => $sort, 'order' => $order),
            'fields' => $fields,
            'count' => count($votes),
            'data' => $votes
        ));
    }

    /**
     * GET /api/non_decrypted_votes/{id}
     * Récupère un vote non décrypté par son voteId
     */
    private function get_vote($id)
    {
        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $select_parts = array();
        foreach ($fields as $field) {
            $select_parts[] = 'vi.' . $field;
        }

        $this->db->select(implode(', ', $select_parts));
        $this->db->from('votes_info vi');
        $this->db->join('votes_datan vd', 'vi.voteId = vd.vote_id', 'left');
        $this->db->where('vd.id IS NULL', null, false);
        $this->db->where('vi.voteId', $id);
        $vote = $this->db->get()->row_array();

        if (empty($vote) && is_numeric($id)) {
            $this->db->select(implode(', ', $select_parts));
            $this->db->from('votes_info vi');
            $this->db->join('votes_datan vd', 'vi.voteId = vd.vote_id', 'left');
            $this->db->where('vd.id IS NULL', null, false);
            $this->db->where('vi.voteNumero', $id);
            $vote = $this->db->get()->row_array();
        }

        if (empty($vote)) {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Vote not found or already decrypted'
            ), 404);
        }

        return $this->api_auth->response(array(
            'success' => true,
            'fields' => $fields,
            'data' => $vote
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
    private function apply_filters($legislature, $year, $month, $vote_type, $sort_code)
    {
        if ($legislature) {
            $this->db->where('vi.legislature', $legislature);
        }
        if ($year) {
            $this->db->where('YEAR(vi.dateScrutin)', $year);
        }
        if ($month) {
            $this->db->where('MONTH(vi.dateScrutin)', $month);
        }
        if ($vote_type) {
            $this->db->where('vi.voteType', $vote_type);
        }
        if ($sort_code) {
            $this->db->where('vi.sortCode', $sort_code);
        }
    }

    /**
     * Retourne les métadonnées de l'endpoint
     */
    public function meta()
    {
        return $this->api_auth->response(array(
            'success' => true,
            'endpoint' => '/api/non_decrypted_votes',
            'description' => 'Votes de l\'Assemblée nationale pas encore décryptés par Datan',
            'methods' => array('GET'),
            'available_fields' => $this->available_fields,
            'sortable_fields' => $this->sortable_fields,
            'filters' => array(
                'legislature' => 'Numéro de législature (ex: 17)',
                'year' => 'Année du scrutin (ex: 2024)',
                'month' => 'Mois du scrutin (1-12)',
                'vote_type' => 'Type de vote (final, amendement, article, etc.)',
                'sort_code' => 'Résultat du vote (adopté, rejeté)'
            ),
            'pagination' => array(
                'page' => 'Numéro de page (défaut: 1)',
                'per_page' => 'Résultats par page (défaut: 50, max: 500)'
            ),
            'sorting' => array(
                'sort' => 'Champ de tri (défaut: dateScrutin)',
                'order' => 'Ordre de tri: ASC ou DESC (défaut: DESC)'
            )
        ));
    }
}
