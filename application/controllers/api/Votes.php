<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API pour les votes bruts (votes_info) - Lecture seule
 */
class Votes extends CI_Controller
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
     * Point d'entrée pour /api/votes et /api/votes/{id}
     */
    public function index($id = null)
    {
        $method = $this->input->method(TRUE);

        if ($method !== 'GET') {
            return $this->api_auth->response(array(
                'error' => true,
                'message' => 'Method not allowed. Only GET is supported for votes_info.'
            ), 405);
        }

        if ($id === null) {
            $permission = $this->api_auth->check_permission('/api/votes', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }
            return $this->list_votes();
        } else {
            $permission = $this->api_auth->check_permission('/api/votes/:id', $method);
            if ($permission !== true) {
                return $this->api_auth->response($permission, $permission['code']);
            }
            return $this->get_vote($id);
        }
    }

    /**
     * GET /api/votes
     * Liste les votes avec pagination et sélection de champs
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
        $this->db->from('votes_info');
        $this->apply_filters($legislature, $year, $month, $vote_type, $sort_code);
        $total = $this->db->count_all_results();

        // Récupérer les résultats avec pagination (nouvelle requête)
        $this->db->select(implode(', ', $fields));
        $this->db->from('votes_info');
        $this->apply_filters($legislature, $year, $month, $vote_type, $sort_code);
        $this->db->order_by($sort, $order);
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
     * GET /api/votes/{id}
     * Récupère un vote par son voteId ou voteNumero
     */
    private function get_vote($id)
    {
        $fields = $this->parse_fields();
        if (isset($fields['error'])) {
            return $this->api_auth->response($fields, 400);
        }

        $this->db->select(implode(', ', $fields));
        $vote = $this->db->get_where('votes_info', array('voteId' => $id))->row_array();

        if (empty($vote) && is_numeric($id)) {
            $this->db->select(implode(', ', $fields));
            $vote = $this->db->get_where('votes_info', array('voteNumero' => $id))->row_array();
        }

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
            $this->db->where('legislature', $legislature);
        }
        if ($year) {
            $this->db->where('YEAR(dateScrutin)', $year);
        }
        if ($month) {
            $this->db->where('MONTH(dateScrutin)', $month);
        }
        if ($vote_type) {
            $this->db->where('voteType', $vote_type);
        }
        if ($sort_code) {
            $this->db->where('sortCode', $sort_code);
        }
    }

    /**
     * Retourne les métadonnées de l'endpoint
     */
    public function meta()
    {
        return $this->api_auth->response(array(
            'success' => true,
            'endpoint' => '/api/votes',
            'description' => 'Votes bruts de l\'Assemblée nationale (votes_info)',
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
