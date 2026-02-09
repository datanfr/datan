<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary font-weight-bold"><?= $title ?></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('admin/api-keys/create') ?>" class="btn btn-primary">
                        Nouvelle clé API
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('new_api_key')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-check"></i> Clé API créée avec succès</h5>
                <p><strong>Copiez cette clé maintenant, elle ne sera plus affichée :</strong></p>
                <code class="d-block p-3 bg-dark text-white rounded" style="font-size: 1.1em; word-break: break-all;">
                    <?= $this->session->flashdata('new_api_key') ?>
                </code>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $this->session->flashdata('success') ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Préfixe</th>
                                    <th>Utilisateur</th>
                                    <th>Permissions</th>
                                    <th>Créée le</th>
                                    <th>Dernière utilisation</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($keys)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Aucune clé API</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($keys as $key): ?>
                                <tr class="<?= !$key['is_active'] ? 'table-secondary' : '' ?>">
                                    <td><?= $key['id'] ?></td>
                                    <td><?= htmlspecialchars($key['name']) ?></td>
                                    <td><code><?= $key['key_prefix'] ?>...</code></td>
                                    <td>
                                        <?= htmlspecialchars($key['user_name']) ?>
                                        <span class="badge badge-<?= $key['user_type'] == 'admin' ? 'danger' : 'info' ?>">
                                            <?= $key['user_type'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (empty($key['permissions'])): ?>
                                        <span class="badge badge-success">Toutes</span>
                                        <?php else: ?>
                                        <?php foreach ($key['permissions'] as $endpoint => $methods): ?>
                                        <div class="mb-1">
                                            <small class="text-muted"><?= str_replace('/api/', '', $endpoint) ?>:</small>
                                            <?php foreach ($methods as $method): ?>
                                            <span class="badge badge-<?= $method == 'GET' ? 'primary' : ($method == 'POST' ? 'success' : ($method == 'PUT' ? 'warning' : 'danger')) ?>" style="font-size: 0.7em;">
                                                <?= $method ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($key['created_at'])) ?></td>
                                    <td>
                                        <?= $key['last_used_at'] ? date('d/m/Y H:i', strtotime($key['last_used_at'])) : '<span class="text-muted">Jamais</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($key['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge badge-secondary">Révoquée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($key['is_active']): ?>
                                        <a href="<?= base_url('admin/api-keys/revoke/' . $key['id']) ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Êtes-vous sûr de vouloir révoquer cette clé ?')">
                                            Révoquer
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Documentation API</h5>
                </div>
                <div class="card-body">
                    <p>Utilisez le header <code>Authorization: Bearer &lt;api_key&gt;</code> pour authentifier vos requêtes.</p>

                    <h6 class="mt-4">Votes bruts (votes_info) - Lecture seule</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Méthode</th>
                                <th>URL</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/votes</code></td>
                                <td>Liste les votes avec pagination</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/votes/{id}</code></td>
                                <td>Détail d'un vote</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-0">
                        <strong>Paramètres :</strong> page, per_page (max 500), fields, legislature, year, month, vote_type, sort_code, sort, order
                    </p>
                    <p class="small text-muted">
                        <strong>Champs :</strong> voteId, legislature, voteNumero, organeRef, dateScrutin, sessionREF, seanceRef, titre, sortCode, codeTypeVote, libelleTypeVote, nombreVotants, decomptePour, decompteContre, decompteAbs, decompteNv, voteType, amdt, article
                    </p>

                    <h6 class="mt-4">Votes décryptés (votes_datan) - CRUD</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Méthode</th>
                                <th>URL</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/decrypted_votes</code></td>
                                <td>Liste les votes décryptés</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/decrypted_votes/{id}</code></td>
                                <td>Détail d'un vote décrypté</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-success">POST</span></td>
                                <td><code>/api/decrypted_votes</code></td>
                                <td>Créer un vote décrypté</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">PUT</span></td>
                                <td><code>/api/decrypted_votes/{id}</code></td>
                                <td>Modifier un vote décrypté</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-danger">DELETE</span></td>
                                <td><code>/api/decrypted_votes/{id}</code></td>
                                <td>Supprimer un vote décrypté (admin only)</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-0">
                        <strong>Paramètres GET :</strong> page, per_page (max 500), fields, state (draft/published), legislature, category, sort, order
                    </p>
                    <p class="small text-muted">
                        <strong>Champs :</strong> id, legislature, voteNumero, vote_id, title, slug, category, category_name, reading, reading_name, description, state, created_at, modified_at, created_by, created_by_name, modified_by, modified_by_name
                    </p>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="small mb-1"><strong>POST</strong> — Champs obligatoires : <code>title</code>, <code>legislature</code>, <code>voteNumero</code>, <code>category</code></p>
                            <p class="small text-muted mb-1">Optionnels : <code>description</code>, <code>reading</code></p>
                            <p class="small text-muted mb-1">Auto-générés : id, vote_id, slug, state (draft), created_at, created_by, created_by_name</p>
                            <pre class="bg-dark text-white p-2 rounded small">{
    "title": "Projet de loi de finances 2025",
    "legislature": "17",
    "voteNumero": "1470",
    "category": "1",
    "description": "Vote sur le budget général",
    "reading": "1"
}</pre>
                        </div>
                        <div class="col-md-6">
                            <p class="small mb-1"><strong>PUT</strong> — Tous les champs sont optionnels, seuls ceux envoyés sont modifiés</p>
                            <p class="small text-muted mb-1">Modifiables : <code>title</code>, <code>category</code>, <code>description</code>, <code>reading</code>, <code>state</code> (draft/published)</p>
                            <p class="small text-muted mb-1">Auto-générés : slug, modified_at, modified_by, modified_by_name</p>
                            <pre class="bg-dark text-white p-2 rounded small">{
    "title": "Titre modifié",
    "state": "published"
}</pre>
                        </div>
                    </div>

                    <h6 class="mt-4">Votes non décryptés - Lecture seule</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Méthode</th>
                                <th>URL</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/non_decrypted_votes</code></td>
                                <td>Liste les votes non encore décryptés</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/non_decrypted_votes/{id}</code></td>
                                <td>Détail d'un vote non décrypté</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="small text-muted">
                        <strong>Paramètres :</strong> Mêmes que /api/votes (votes bruts)
                    </p>

                    <h6 class="mt-4">Exposés des motifs - CRUD</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Méthode</th>
                                <th>URL</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/exposes</code></td>
                                <td>Liste les exposés des motifs</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/exposes/{id}</code></td>
                                <td>Détail d'un exposé</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/exposes/by_vote/{legislature}/{voteNumero}</code></td>
                                <td>Récupérer un exposé par vote</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">GET</span></td>
                                <td><code>/api/exposes/stats</code></td>
                                <td>Statistiques des exposés</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-success">POST</span></td>
                                <td><code>/api/exposes</code></td>
                                <td>Créer un exposé</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">PUT</span></td>
                                <td><code>/api/exposes/{id}</code></td>
                                <td>Modifier un exposé</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-danger">DELETE</span></td>
                                <td><code>/api/exposes/{id}</code></td>
                                <td>Supprimer un exposé (admin only)</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-0">
                        <strong>Paramètres GET :</strong> page, per_page (max 500), fields, legislature, status (pending/done/all), sort, order
                    </p>
                    <p class="small text-muted">
                        <strong>Champs :</strong> id, legislature, voteNumero, exposeOriginal, exposeSummary, exposeSummaryPublished, dateMaj
                    </p>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="small mb-1"><strong>POST</strong> — Champs obligatoires : <code>legislature</code>, <code>voteNumero</code></p>
                            <p class="small text-muted mb-1">Optionnels : <code>exposeOriginal</code>, <code>exposeSummary</code>, <code>exposeSummaryPublished</code></p>
                            <p class="small text-muted mb-1">Auto-générés : id, dateMaj</p>
                            <pre class="bg-dark text-white p-2 rounded small">{
    "legislature": "17",
    "voteNumero": "1470",
    "exposeOriginal": "Texte original...",
    "exposeSummary": "Résumé..."
}</pre>
                        </div>
                        <div class="col-md-6">
                            <p class="small mb-1"><strong>PUT</strong> — Tous les champs sont optionnels, seuls ceux envoyés sont modifiés</p>
                            <p class="small text-muted mb-1">Modifiables : <code>exposeOriginal</code>, <code>exposeSummary</code>, <code>exposeSummaryPublished</code></p>
                            <p class="small text-muted mb-1">Auto-générés : dateMaj</p>
                            <pre class="bg-dark text-white p-2 rounded small">{
    "exposeSummary": "Résumé modifié",
    "exposeSummaryPublished": "1"
}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
