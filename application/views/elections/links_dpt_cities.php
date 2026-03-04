<!-- OTHER CITIES -->
<div class="container-fluid bloc-others-container">
  <div class="container bloc-others">
    <div class="row mt-5">
      <div class="col-12">
        <h2>Candidats et résultats dans les communes françaises</h2>
      </div>
    </div>
    <div class="row">
      <?php foreach ($communes as $commune): ?>
        <div class="col-6 col-md-4 py-2">
          <a class="membre no-decoration underline" href="<?= base_url() ?>elections/resultats/<?= url_election_paris($commune['slug'] . "/ville_" . $commune['commune_slug']) ?>" class="no-decoration underline-blue"><?= $commune['commune_nom'] ?></a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="row mt-5">
      <div class="col-12">
        <h2>Candidats et résultats département par département</h2>
      </div>
    </div>
    <div class="row">
      <?php foreach ($departements as $departement): ?>
        <div class="col-6 col-md-4 py-2">
          <?php if($departement['slug'] === 'paris-75'): ?>
            <a class="membre no-decoration underline url_obf" url_obf="<?= url_obfuscation(base_url() . "elections/resultats/ville_paris") ?>" class="no-decoration underline-blue"><?= $departement['departement_nom'] ?> (<?= $departement['departement_code'] ?>)</a>
          <?php else: ?>
            <a class="membre no-decoration underline" href="<?= base_url() ?>elections/resultats/<?= $departement['slug'] ?>" class="no-decoration underline-blue"><?= $departement['departement_nom'] ?> (<?= $departement['departement_code'] ?>)</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>