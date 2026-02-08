<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary font-weight-bold"><?= $title ?></h1>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid mb-4">
        <div class="row">
        <div class="col-12 col-lg-6">
            <?= form_open('admin/api-keys/create', ['method' => 'post']) ?>

            <?php if (validation_errors()): ?>
            <div class="alert alert-danger">
                <?= validation_errors() ?>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="name" class="form-label">Nom de la clé</label>
                <input type="text" class="form-control" id="name" name="name"
                       placeholder="Ex: Script import quotidien, Application mobile..."
                       value="<?= set_value('name') ?>" required>
                <small class="form-text text-muted">Un nom descriptif pour identifier l'usage de cette clé</small>
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">Utilisateur propriétaire</label>
                <select class="form-control" id="user_id" name="user_id" required>
                    <option value="">-- Sélectionner un utilisateur --</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= set_select('user_id', $user['id']) ?>>
                        <?= htmlspecialchars($user['name']) ?> (<?= $user['username'] ?>)
                        - <?= $user['type'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">La clé héritera des permissions de base de cet utilisateur</small>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permissions de la clé API</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Sélectionnez les endpoints et méthodes autorisés pour cette clé.</p>

                    <?php foreach ($endpoints as $endpoint => $methods): ?>
                    <div class="card mb-2">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <strong><code><?= $endpoint ?></code></strong>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input select-all-endpoint"
                                       id="select_all_<?= md5($endpoint) ?>"
                                       data-endpoint="<?= md5($endpoint) ?>">
                                <label class="form-check-label" for="select_all_<?= md5($endpoint) ?>">Tout</label>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <?php foreach ($methods as $method => $description): ?>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input endpoint-checkbox endpoint-<?= md5($endpoint) ?>"
                                               id="perm_<?= md5($endpoint . $method) ?>"
                                               name="permissions[<?= $endpoint ?>][]"
                                               value="<?= $method ?>"
                                               <?= set_checkbox('permissions[' . $endpoint . '][]', $method) ?>>
                                        <label class="form-check-label" for="perm_<?= md5($endpoint . $method) ?>">
                                            <span class="badge badge-<?= $method == 'GET' ? 'primary' : ($method == 'POST' ? 'success' : ($method == 'PUT' ? 'warning' : 'danger')) ?>">
                                                <?= $method ?>
                                            </span>
                                            <small class="text-muted d-block"><?= $description ?></small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">Tout sélectionner</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">Tout désélectionner</button>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important :</strong> La clé API sera affichée une seule fois après sa création.
                Assurez-vous de la copier et de la stocker en lieu sûr.
            </div>

            <button type="submit" class="btn btn-primary">Créer la clé</button>
            <a href="<?= base_url('admin/api-keys') ?>" class="btn btn-secondary">Annuler</a>

            <?= form_close() ?>
        </div>

        <!-- Documentation API -->
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-book"></i> Documentation API</h5>
                </div>
                <div class="card-body">

                    <!-- Authentification -->
                    <h6 class="font-weight-bold text-primary">Authentification</h6>
                    <p>Ajoutez la clé API dans le header de chaque requête :</p>
                    <pre class="bg-dark text-light p-2 rounded"><code>X-API-Key: votre_cle_api</code></pre>

                    <!-- Endpoints -->
                    <h6 class="font-weight-bold text-primary mt-4">Endpoints disponibles</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Endpoint</th>
                                <th>Méthodes</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/api/votes</code></td>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td>Votes bruts de l'Assemblée</td>
                            </tr>
                            <tr>
                                <td><code>/api/non_decrypted_votes</code></td>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td>Votes non encore décryptés</td>
                            </tr>
                            <tr>
                                <td><code>/api/decrypted_votes</code></td>
                                <td>
                                    <span class="badge badge-primary">GET</span>
                                    <span class="badge badge-success">POST</span>
                                    <span class="badge badge-warning">PUT</span>
                                    <span class="badge badge-danger">DELETE</span>
                                </td>
                                <td>Votes décryptés par Datan</td>
                            </tr>
                            <tr>
                                <td><code>/api/exposes</code></td>
                                <td>
                                    <span class="badge badge-primary">GET</span>
                                    <span class="badge badge-success">POST</span>
                                    <span class="badge badge-warning">PUT</span>
                                    <span class="badge badge-danger">DELETE</span>
                                </td>
                                <td>Exposés des motifs</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Méthodes HTTP -->
                    <h6 class="font-weight-bold text-primary mt-4">Méthodes HTTP</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-primary">GET</span> Lecture des données</li>
                        <li><span class="badge badge-success">POST</span> Création d'une ressource</li>
                        <li><span class="badge badge-warning">PUT</span> Modification d'une ressource</li>
                        <li><span class="badge badge-danger">DELETE</span> Suppression (admin uniquement)</li>
                    </ul>

                    <!-- Pagination -->
                    <h6 class="font-weight-bold text-primary mt-4">Pagination</h6>
                    <table class="table table-sm">
                        <tr><td><code>page</code></td><td>Numéro de page (défaut: 1)</td></tr>
                        <tr><td><code>per_page</code></td><td>Résultats par page (défaut: 50, max: 500)</td></tr>
                    </table>

                    <!-- Tri -->
                    <h6 class="font-weight-bold text-primary mt-4">Tri</h6>
                    <table class="table table-sm">
                        <tr><td><code>sort</code></td><td>Champ de tri (ex: <code>dateScrutin</code>, <code>created_at</code>)</td></tr>
                        <tr><td><code>order</code></td><td><code>ASC</code> (croissant) ou <code>DESC</code> (décroissant)</td></tr>
                    </table>

                    <!-- Sélection des champs -->
                    <h6 class="font-weight-bold text-primary mt-4">Sélection des champs</h6>
                    <p>Limitez les champs retournés avec le paramètre <code>fields</code> :</p>
                    <pre class="bg-dark text-light p-2 rounded"><code>GET /api/votes?fields=voteId,titre,dateScrutin</code></pre>

                    <!-- Filtres par endpoint -->
                    <h6 class="font-weight-bold text-primary mt-4">Filtres par endpoint</h6>

                    <p class="mb-1"><strong>/api/votes</strong> et <strong>/api/non_decrypted_votes</strong></p>
                    <table class="table table-sm mb-3">
                        <tr><td><code>legislature</code></td><td>Numéro de législature (ex: 17)</td></tr>
                        <tr><td><code>year</code></td><td>Année du scrutin (ex: 2024)</td></tr>
                        <tr><td><code>month</code></td><td>Mois du scrutin (1-12)</td></tr>
                        <tr><td><code>vote_type</code></td><td>Type de vote</td></tr>
                        <tr><td><code>sort_code</code></td><td>Résultat (adopté, rejeté)</td></tr>
                    </table>

                    <p class="mb-1"><strong>/api/decrypted_votes</strong></p>
                    <table class="table table-sm mb-3">
                        <tr><td><code>legislature</code></td><td>Numéro de législature</td></tr>
                        <tr><td><code>state</code></td><td><code>draft</code> ou <code>published</code></td></tr>
                        <tr><td><code>category</code></td><td>ID de la catégorie</td></tr>
                    </table>

                    <p class="mb-1"><strong>/api/exposes</strong></p>
                    <table class="table table-sm mb-3">
                        <tr><td><code>legislature</code></td><td>Numéro de législature</td></tr>
                        <tr><td><code>status</code></td><td><code>pending</code>, <code>done</code> ou <code>all</code></td></tr>
                    </table>

                    <!-- Exemples -->
                    <h6 class="font-weight-bold text-primary mt-4">Exemples de requêtes</h6>
                    <pre class="bg-dark text-light p-2 rounded" style="font-size: 12px;"><code># Liste des votes de la législature 17
curl -H "X-API-Key: VOTRE_CLE" \
  "<?= base_url() ?>api/votes?legislature=17&per_page=10"

# Votes décryptés en brouillon
curl -H "X-API-Key: VOTRE_CLE" \
  "<?= base_url() ?>api/decrypted_votes?state=draft"

# Créer un vote décrypté
curl -X POST -H "X-API-Key: VOTRE_CLE" \
  -H "Content-Type: application/json" \
  -d '{"title":"...", "legislature":17, "vote_id":"...", "category":1}' \
  "<?= base_url() ?>api/decrypted_votes"

# Métadonnées d'un endpoint
curl -H "X-API-Key: VOTRE_CLE" \
  "<?= base_url() ?>api/votes/meta"</code></pre>

                    <!-- Réponses -->
                    <h6 class="font-weight-bold text-primary mt-4">Format des réponses</h6>
                    <p>Les réponses sont en JSON et contiennent :</p>
                    <ul>
                        <li><code>success</code> : booléen</li>
                        <li><code>pagination</code> : infos de pagination</li>
                        <li><code>data</code> : les données demandées</li>
                        <li><code>error</code> / <code>message</code> : en cas d'erreur</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Astuce :</strong> Utilisez <code>/api/{endpoint}/meta</code> pour obtenir la documentation complète d'un endpoint (champs disponibles, filtres, etc.)
                    </div>

                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all for a specific endpoint
    document.querySelectorAll('.select-all-endpoint').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var endpoint = this.dataset.endpoint;
            document.querySelectorAll('.endpoint-' + endpoint).forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
        });
    });

    // Update "select all" checkbox when individual checkboxes change
    document.querySelectorAll('.endpoint-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var classes = this.className.split(' ');
            var endpointClass = classes.find(c => c.startsWith('endpoint-') && c !== 'endpoint-checkbox');
            if (endpointClass) {
                var endpoint = endpointClass.replace('endpoint-', '');
                var allChecked = document.querySelectorAll('.' + endpointClass + ':checked').length ===
                                 document.querySelectorAll('.' + endpointClass).length;
                document.getElementById('select_all_' + endpoint).checked = allChecked;
            }
        });
    });

    // Global select/deselect all
    document.getElementById('select-all').addEventListener('click', function() {
        document.querySelectorAll('.endpoint-checkbox, .select-all-endpoint').forEach(function(cb) {
            cb.checked = true;
        });
    });

    document.getElementById('deselect-all').addEventListener('click', function() {
        document.querySelectorAll('.endpoint-checkbox, .select-all-endpoint').forEach(function(cb) {
            cb.checked = false;
        });
    });
});
</script>
