<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <?php $this->load->view('dashboard-mp/partials/breadcrumb.php', $breadcrumb) ?>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">

      <!-- En-tête -->
      <div class="row mb-3">
        <div class="col-12">
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard">
            <?= file_get_contents(FCPATH . "assets/imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
        </div>
        <div class="col-lg-8 my-4">
          <h1 class="font-weight-bold mb-1 text-dark"><?= $title ?></h1>
          <p class="text-muted mb-0">Dernière législature · <?= count($amendements) ?> amendements</p>
        </div>
        <div class="col-lg-4 my-4 d-flex align-items-center justify-content-lg-end">
          <button id="btn-batch-summaries" class="btn btn-outline-primary font-weight-bold">
            Générer tous les résumés IA
          </button>
        </div>
      </div>

      <!-- Flashdata -->
      <?php if ($this->session->flashdata('flash')) : ?>
        <div class="alert alert-primary font-weight-bold" role="alert"><?= $this->session->flashdata('flash') ?></div>
      <?php endif; ?>

      <!-- Résultat batch -->
      <div id="batch-result" class="alert d-none" role="alert"></div>

      <!-- Tri -->
      <?php
        $next_dir = ($direction === 'DESC') ? 'ASC' : 'DESC';
        $arrow    = $direction === 'DESC' ? '↓' : '↑';
        function sort_url($col, $current_sort, $current_dir) {
          $dir = ($current_sort === $col && $current_dir === 'DESC') ? 'ASC' : 'DESC';
          return base_url() . 'dashboard/amendements?sort=' . $col . '&direction=' . $dir;
        }
      ?>

      <!-- Tableau -->
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" id="table-amendements">
              <thead class="thead-dark">
                <tr>
                  <th style="min-width:280px">
                    Amendement
                  </th>
                  <th class="d-none d-md-table-cell" style="max-width:320px">Résumé IA</th>
                  <th class="text-center">
                    <a href="<?= sort_url('votants', $sort, $direction) ?>" class="text-white text-decoration-none">
                      Votants<?= $sort === 'votants' ? ' ' . $arrow : '' ?>
                    </a>
                  </th>
                  <th class="text-center">
                    <a href="<?= sort_url('disparite', $sort, $direction) ?>" class="text-white text-decoration-none" title="Différence absolue entre Pour et Contre, en % des votants">
                      Disparité<?= $sort === 'disparite' ? ' ' . $arrow : '' ?>
                    </a>
                  </th>
                  <th class="text-center">
                    <a href="<?= sort_url('simplicite', $sort, $direction) ?>" class="text-white text-decoration-none" title="Score de simplicité de compréhension (1=très technique, 5=très simple)">
                      Simplicité<?= $sort === 'simplicite' ? ' ' . $arrow : '' ?>
                    </a>
                  </th>
                  <th class="text-center">
                    <a href="<?= sort_url('decrypte', $sort, $direction) ?>" class="text-white text-decoration-none">
                      Décrypté<?= $sort === 'decrypte' ? ' ' . $arrow : '' ?>
                    </a>
                  </th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($amendements)) : ?>
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Aucun amendement trouvé.</td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($amendements as $a) : ?>
                    <tr data-legislature="<?= $a['legislature'] ?>" data-vote="<?= htmlspecialchars($a['voteNumero']) ?>">

                      <!-- Titre -->
                      <td>
                        <div class="font-weight-bold" style="font-size:.9rem">
                          <?= htmlspecialchars(mb_strimwidth($a['titre'] ?: '—', 0, 120, '…')) ?>
                        </div>
                        <small class="text-muted">Leg. <?= $a['legislature'] ?> · n°<?= $a['voteNumero'] ?> · <?= $a['dateScrutinFR'] ?></small>
                      </td>

                      <!-- Résumé IA -->
                      <td class="d-none d-md-table-cell text-muted" style="font-size:.85rem">
                        <?= $a['resume_ia'] ? htmlspecialchars($a['resume_ia']) : '<em class="text-light">—</em>' ?>
                      </td>

                      <!-- Votants -->
                      <td class="text-center">
                        <?= $a['nombreVotants'] ? number_format($a['nombreVotants'], 0, ',', '\u{202F}') : '—' ?>
                      </td>

                      <!-- Disparité -->
                      <td class="text-center">
                        <?php if ($a['disparite'] !== null && $a['nombreVotants'] > 0) :
                          $d = (float)$a['disparite'];
                          $cls = $d >= 60 ? 'danger' : ($d >= 30 ? 'warning' : 'success');
                        ?>
                          <span class="badge badge-<?= $cls ?>"><?= $d ?>%</span>
                        <?php else : ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>

                      <!-- Simplicité -->
                      <td class="text-center">
                        <?php if ($a['simplicite_ia']) :
                          $stars = str_repeat('★', $a['simplicite_ia']) . str_repeat('☆', 5 - $a['simplicite_ia']);
                          $cls   = $a['simplicite_ia'] >= 4 ? 'success' : ($a['simplicite_ia'] >= 3 ? 'warning' : 'danger');
                        ?>
                          <span class="text-<?= $cls ?>" title="<?= $a['simplicite_ia'] ?>/5"><?= $stars ?></span>
                        <?php else : ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>

                      <!-- Décrypté -->
                      <td class="text-center">
                        <?php if ($a['decrypte']) : ?>
                          <span class="badge badge-<?= $a['decryptage_state'] === 'published' ? 'success' : 'secondary' ?>">
                            <?= $a['decryptage_state'] === 'published' ? 'Publié' : 'Brouillon' ?>
                          </span>
                        <?php else : ?>
                          <span class="badge badge-light text-muted">Non</span>
                        <?php endif; ?>
                      </td>

                      <!-- Action -->
                      <td class="text-right" style="white-space:nowrap">
                        <?php if (!$a['decrypte']) : ?>
                          <button
                            class="btn btn-sm btn-primary btn-decrypt font-weight-bold"
                            data-legislature="<?= $a['legislature'] ?>"
                            data-vote="<?= htmlspecialchars($a['voteNumero']) ?>">
                            Décrypter
                          </button>
                        <?php else : ?>
                          <a class="btn btn-sm btn-outline-secondary"
                             href="<?= base_url() ?>votes/legislature-<?= $a['legislature'] ?>/vote_<?= $a['voteNumero'] ?>"
                             target="_blank">
                            Voir
                          </a>
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

    </div>
  </div>
</div>

<script>
(function () {
  var BASE = '<?= base_url() ?>';

  // Bouton "Décrypter" par amendement
  document.querySelectorAll('.btn-decrypt').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var legislature = btn.dataset.legislature;
      var voteNumero  = btn.dataset.vote;
      var row         = btn.closest('tr');

      btn.disabled    = true;
      btn.textContent = 'En cours…';

      fetch(BASE + 'admin/amendements/decrypt', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'legislature=' + encodeURIComponent(legislature) + '&voteNumero=' + encodeURIComponent(voteNumero)
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.error) {
          btn.textContent = 'Erreur';
          btn.classList.replace('btn-primary', 'btn-danger');
          btn.disabled = false;
          alert('Erreur : ' + data.error);
        } else {
          // Résumé IA
          if (data.resume_ia) {
            var cellResume = row.querySelector('td:nth-child(2)');
            if (cellResume) cellResume.textContent = data.resume_ia;
          }
          // Simplicité
          if (data.simplicite_score) {
            var score = data.simplicite_score;
            var stars = '★'.repeat(score) + '☆'.repeat(5 - score);
            var cls = score >= 4 ? 'success' : (score >= 3 ? 'warning' : 'danger');
            var cellSimpl = row.querySelector('td:nth-child(5)');
            if (cellSimpl) cellSimpl.innerHTML = '<span class="text-' + cls + '">' + stars + '</span>';
          }
          // Badge décrypté
          var cellDecrypte = row.querySelector('td:nth-child(6)');
          if (cellDecrypte) cellDecrypte.innerHTML = '<span class="badge badge-secondary">Brouillon</span>';
          // Bouton
          btn.textContent = 'Décrypté ✓';
          btn.classList.replace('btn-primary', 'btn-success');
        }
      })
      .catch(function () {
        btn.textContent = 'Erreur réseau';
        btn.classList.replace('btn-primary', 'btn-danger');
        btn.disabled = false;
      });
    });
  });

  // Bouton "Générer tous les résumés IA"
  var batchBtn    = document.getElementById('btn-batch-summaries');
  var batchResult = document.getElementById('batch-result');

  batchBtn.addEventListener('click', function () {
    if (!confirm('Lancer la génération des résumés IA pour tous les amendements sans résumé ? Cela peut prendre plusieurs minutes.')) {
      return;
    }
    batchBtn.disabled    = true;
    batchBtn.textContent = 'Génération en cours…';
    batchResult.className = 'alert alert-info';
    batchResult.textContent = 'Requête envoyée à PoliticAnalysis…';

    fetch(BASE + 'admin/amendements/batch-summaries', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: ''
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      batchBtn.disabled    = false;
      batchBtn.textContent = 'Générer tous les résumés IA';
      if (data.error) {
        batchResult.className   = 'alert alert-danger';
        batchResult.textContent = 'Erreur : ' + data.error;
      } else {
        batchResult.className   = 'alert alert-success';
        batchResult.textContent =
          'Terminé · ' + data.generated + ' générés, ' + data.skipped + ' déjà faits'
          + (data.errors && data.errors.length ? ' · ' + data.errors.length + ' erreur(s)' : '');
        setTimeout(function () { location.reload(); }, 2000);
      }
    })
    .catch(function () {
      batchBtn.disabled    = false;
      batchBtn.textContent = 'Générer tous les résumés IA';
      batchResult.className   = 'alert alert-danger';
      batchResult.textContent = 'Erreur réseau.';
    });
  });
})();
</script>
