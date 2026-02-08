<?php
class Api_key_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retourne la liste des endpoints disponibles avec leurs méthodes
     *
     * @return array
     */
    public function get_available_endpoints()
    {
        return array(
            // Votes bruts (votes_info)
            '/api/votes' => array(
                'GET' => 'Lister les votes (votes_info)'
            ),
            '/api/votes/{id}' => array(
                'GET' => 'Voir un vote (votes_info)'
            ),
            // Votes décryptés (votes_datan)
            '/api/decrypted_votes' => array(
                'GET' => 'Lister les votes décryptés',
                'POST' => 'Créer un vote décrypté'
            ),
            '/api/decrypted_votes/{id}' => array(
                'GET' => 'Voir un vote décrypté',
                'PUT' => 'Modifier un vote décrypté',
                'DELETE' => 'Supprimer un vote décrypté'
            ),
            // Votes non décryptés
            '/api/non_decrypted_votes' => array(
                'GET' => 'Lister les votes non décryptés'
            ),
            // Exposés des motifs
            '/api/exposes' => array(
                'GET' => 'Lister les exposés',
                'POST' => 'Créer un exposé'
            ),
            '/api/exposes/{id}' => array(
                'GET' => 'Voir un exposé',
                'PUT' => 'Modifier un exposé',
                'DELETE' => 'Supprimer un exposé'
            )
        );
    }

    /**
     * Valide une clé API et retourne les infos de l'utilisateur associé
     *
     * @param string $api_key La clé API en clair
     * @return array|null Les infos utilisateur ou null si invalide
     */
    public function validate_key($api_key)
    {
        $key_hash = hash('sha256', $api_key);
        $key_prefix = substr($api_key, 0, 8);

        $this->db->select('ak.*, u.id as user_id, u.name as user_name, u.type as user_type');
        $this->db->from('api_keys ak');
        $this->db->join('users u', 'u.id = ak.user_id');
        $this->db->where('ak.key_hash', $key_hash);
        $this->db->where('ak.key_prefix', $key_prefix);
        $this->db->where('ak.is_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() === 1) {
            $result = $query->row_array();
            $result['permissions'] = json_decode($result['permissions'], true);
            $this->update_last_used($result['id']);
            return $result;
        }

        return null;
    }

    /**
     * Vérifie si une clé a la permission pour un endpoint et une méthode
     *
     * @param array $api_user Les infos de la clé API (retournées par validate_key)
     * @param string $endpoint L'endpoint demandé (ex: /api/admin/votes)
     * @param string $method La méthode HTTP (GET, POST, PUT, DELETE)
     * @return bool
     */
    public function has_permission($api_user, $endpoint, $method)
    {
        $permissions = $api_user['permissions'];

        // Si pas de permissions définies, tout est autorisé (rétrocompatibilité)
        if (empty($permissions)) {
            return true;
        }

        // Normaliser l'endpoint (remplacer les IDs par {id})
        $normalized_endpoint = preg_replace('/\/\d+$/', '/{id}', $endpoint);

        // Vérifier la permission exacte
        if (isset($permissions[$normalized_endpoint])) {
            return in_array($method, $permissions[$normalized_endpoint]);
        }

        // Vérifier aussi l'endpoint de base pour /api/admin/votes
        if (isset($permissions[$endpoint])) {
            return in_array($method, $permissions[$endpoint]);
        }

        return false;
    }

    /**
     * Met à jour la date de dernière utilisation
     *
     * @param int $key_id ID de la clé
     */
    public function update_last_used($key_id)
    {
        $this->db->set('last_used_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $key_id);
        $this->db->update('api_keys');
    }

    /**
     * Génère une nouvelle clé API
     *
     * @param int $user_id ID de l'utilisateur
     * @param string $name Nom descriptif de la clé
     * @param array|null $permissions Permissions (endpoint => [methods])
     * @return array ['key' => clé en clair (à afficher une seule fois), 'id' => id en BDD]
     */
    public function create_key($user_id, $name, $permissions = null)
    {
        // Génère une clé aléatoire de 32 caractères avec préfixe
        $random_bytes = bin2hex(random_bytes(24));
        $api_key = 'datan_' . $random_bytes;

        $key_hash = hash('sha256', $api_key);
        $key_prefix = substr($api_key, 0, 8);

        // Nettoyer les permissions vides
        if (is_array($permissions)) {
            $permissions = array_filter($permissions, function($methods) {
                return !empty($methods);
            });
        }

        $data = array(
            'name' => $name,
            'key_hash' => $key_hash,
            'key_prefix' => $key_prefix,
            'user_id' => $user_id,
            'permissions' => !empty($permissions) ? json_encode($permissions) : null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('api_keys', $data);

        return array(
            'key' => $api_key,
            'id' => $this->db->insert_id()
        );
    }

    /**
     * Désactive une clé API
     *
     * @param int $key_id ID de la clé
     * @return bool
     */
    public function revoke_key($key_id)
    {
        $this->db->set('is_active', 0);
        $this->db->where('id', $key_id);
        return $this->db->update('api_keys');
    }

    /**
     * Liste les clés d'un utilisateur
     *
     * @param int $user_id ID de l'utilisateur
     * @return array
     */
    public function get_keys_by_user($user_id)
    {
        $this->db->select('id, name, key_prefix, permissions, is_active, created_at, last_used_at');
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('api_keys');
        return $query->result_array();
    }

    /**
     * Liste toutes les clés API avec infos utilisateur
     *
     * @return array
     */
    public function get_all_keys()
    {
        $this->db->select('ak.id, ak.name, ak.key_prefix, ak.permissions, ak.is_active, ak.created_at, ak.last_used_at, u.name as user_name, u.type as user_type');
        $this->db->from('api_keys ak');
        $this->db->join('users u', 'u.id = ak.user_id');
        $this->db->order_by('ak.created_at', 'DESC');
        $query = $this->db->get();
        $results = $query->result_array();

        // Décoder les permissions JSON
        foreach ($results as &$row) {
            $row['permissions'] = json_decode($row['permissions'], true);
        }

        return $results;
    }

    /**
     * Formate les permissions pour affichage
     *
     * @param array|null $permissions
     * @return string
     */
    public function format_permissions_for_display($permissions)
    {
        if (empty($permissions)) {
            return 'Toutes';
        }

        $parts = array();
        foreach ($permissions as $endpoint => $methods) {
            $short_endpoint = str_replace('/api/', '', $endpoint);
            $parts[] = $short_endpoint . ': ' . implode(', ', $methods);
        }

        return implode(' | ', $parts);
    }
}
