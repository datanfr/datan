<style>
  #table-amendements tr.row-reviewed > td,
  #table-amendements tr.row-reviewed:hover > td {
    background-color: #d4edda;
  }
</style>
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
        <div class="col-lg-8 my-4">
          <h1 class="font-weight-bold mb-1 text-dark"><?= $title ?></h1>
          <p class="text-muted mb-0">Dernière législature · <?= count($amendements) ?> amendements</p>
        </div>
      </div>

      <!-- Flashdata -->
      <?php if ($this->session->flashdata('flash')) : ?>
        <div class="alert alert-primary font-weight-bold" role="alert"><?= $this->session->flashdata('flash') ?></div>
      <?php endif; ?>

      <!-- Filtres -->
      <div class="card mb-3">
        <div class="card-body p-3">
          <form method="get" action="<?= base_url() ?>admin/amendements" class="form-row align-items-end">
            <div class="form-group col-md-3 mb-md-0">
              <label for="period" class="font-weight-bold mb-1">Période</label>
              <select id="period" name="period" class="form-control">
                <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>Toutes les dates</option>
                <option value="7"   <?= $period === '7'   ? 'selected' : '' ?>>7 derniers jours</option>
                <option value="30"  <?= $period === '30'  ? 'selected' : '' ?>>30 derniers jours</option>
                <option value="90"  <?= $period === '90'  ? 'selected' : '' ?>>90 derniers jours</option>
                <option value="180" <?= $period === '180' ? 'selected' : '' ?>>6 derniers mois</option>
                <option value="365" <?= $period === '365' ? 'selected' : '' ?>>1 an</option>
              </select>
            </div>
            <div class="form-group col-md-3 mb-md-0">
              <label for="date_start" class="font-weight-bold mb-1">Du</label>
              <input type="date" id="date_start" name="date_start" class="form-control"
                     value="<?= htmlspecialchars($date_start) ?>">
            </div>
            <div class="form-group col-md-3 mb-md-0">
              <label for="date_end" class="font-weight-bold mb-1">Au</label>
              <input type="date" id="date_end" name="date_end" class="form-control"
                     value="<?= htmlspecialchars($date_end) ?>">
            </div>
            <div class="form-group col-md-3 mb-0 d-flex">
              <button type="submit" class="btn btn-primary font-weight-bold mr-2">Filtrer</button>
              <a href="<?= base_url() ?>admin/amendements" class="btn btn-outline-secondary font-weight-bold">Réinitialiser</a>
            </div>
            <div class="form-group col-12 mb-0 mt-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="hide_reviewed" name="hide_reviewed" value="1"
                       onchange="this.form.submit()"
                       <?= !empty($hide_reviewed) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="hide_reviewed">Cacher les amendements reviewed</label>
              </div>
            </div>
          </form>
          <small class="text-muted d-block mt-2">
            Les dates personnalisées (Du / Au) ont priorité sur la période sélectionnée.
          </small>
        </div>
      </div>

      <!-- Tableau -->
      <div class="card">
        <div class="card-body p-3">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0" id="table-amendements">
              <thead>
                <tr>
                  <th style="min-width:280px">
                    Amendement
                  </th>
                  <th class="d-none d-md-table-cell" style="max-width:260px">Titre IA</th>
                  <th class="d-none d-md-table-cell" style="max-width:320px">Résumé IA</th>
                  <th class="text-center">Votants</th>
                  <th class="text-center" title="Différence absolue entre Pour et Contre, en % des votants">Disparité</th>
                  <th class="text-center" title="Score d'intérêt 0–100 : combine participation (saturation 250 votants) et contestation (1 - disparité). Privilégie les votes serrés et participés.">Intérêt</th>
                  <th class="text-center" title="Score de simplicité de compréhension (1=très technique, 5=très simple)">Simplicité</th>
                  <th class="text-center" title="Cocher pour marquer comme relu/validé">Reviewed</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($amendements)) : ?>
                  <tr>
                    <td colspan="10" class="text-center py-4 text-muted">Aucun amendement trouvé.</td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($amendements as $a) : ?>
                    <tr data-legislature="<?= $a['legislature'] ?>" data-vote="<?= htmlspecialchars($a['voteNumero']) ?>"
                        class="<?= !empty($a['reviewed']) ? 'row-reviewed' : '' ?>">

                      <!-- Titre -->
                      <td>
                        <div class="font-weight-bold" style="font-size:.9rem">
                          <?= ucfirst(htmlspecialchars(mb_strimwidth($a['titre'] ?: '—', 0, 120, '…'))) ?>
                        </div>
                        <small class="text-muted">Leg. <?= $a['legislature'] ?> · n°<?= $a['voteNumero'] ?> · <?= $a['dateScrutinFR'] ?></small>
                      </td>

                      <!-- Titre IA -->
                      <td class="d-none d-md-table-cell" style="font-size:.85rem">
                        <?= !empty($a['titre_ia']) ? htmlspecialchars($a['titre_ia']) : '<em class="text-light">—</em>' ?>
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

                      <!-- Intérêt -->
                      <td class="text-center">
                        <?php if (isset($a['interet']) && $a['nombreVotants'] > 0) :
                          $i = (float)$a['interet'];
                          $cls = $i >= 60 ? 'success' : ($i >= 30 ? 'warning' : ($i >= 15 ? 'info' : 'secondary'));
                        ?>
                          <span class="badge badge-<?= $cls ?>" title="Participation × Contestation × 100"><?= $i ?></span>
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

                      <!-- Reviewed -->
                      <td class="text-center">
                        <input type="checkbox"
                               class="chk-reviewed"
                               data-legislature="<?= $a['legislature'] ?>"
                               data-vote="<?= htmlspecialchars($a['voteNumero']) ?>"
                               <?= !empty($a['reviewed']) ? 'checked' : '' ?>>
                      </td>

                      <!-- Action -->
                      <td class="text-right" style="white-space:nowrap">
                        <?php $scrutinUid = 'VTANR5L' . $a['legislature'] . 'V' . $a['voteNumero']; ?>
                        <?php if (!empty($pa_public_url)) : ?>
                          <a class="btn btn-sm btn-primary font-weight-bold"
                             href="<?= $pa_public_url ?>/scrutins/<?= htmlspecialchars($scrutinUid) ?>/decryptage"
                             target="_blank">
                            Décrypter
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

  // Checkbox "Reviewed"
  document.querySelectorAll('.chk-reviewed').forEach(function (chk) {
    chk.addEventListener('change', function () {
      var legislature = chk.dataset.legislature;
      var voteNumero  = chk.dataset.vote;
      var reviewed    = chk.checked ? 1 : 0;
      var row         = chk.closest('tr');

      chk.disabled = true;

      fetch(BASE + 'admin/amendements/review', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'legislature=' + encodeURIComponent(legislature)
            + '&voteNumero=' + encodeURIComponent(voteNumero)
            + '&reviewed='   + reviewed
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        chk.disabled = false;
        if (data && data.success) {
          if (row) row.classList.toggle('row-reviewed', !!reviewed);
        } else {
          chk.checked = !reviewed;
          alert('Erreur lors de la mise à jour : ' + (data && data.error ? data.error : 'inconnue'));
        }
      })
      .catch(function () {
        chk.disabled = false;
        chk.checked  = !reviewed;
        alert('Erreur réseau.');
      });
    });
  });
})();
</script>
