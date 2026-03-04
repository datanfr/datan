<!-- Election Feature -->
<?php if ($electionFeature): ?>
  <div class="card card-election-feature <?= $electionFeature['color'] ?> mt-5 mb-4 border-0" style="overflow: hidden">
    <div class="card-body">
      <h2>🗳️ Municipales 2026</h2>
      <?php if ($electionFeature['elected'] == "1"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales dans la commune <a class="text-white" href="<?= base_url() ?>deputes/<?= $electionFeatureDistrict['dpt_slug'] ?>/ville_<?= $electionFeatureDistrict['commune_slug'] ?>"><?= $electionFeatureDistrict['nom_de'] ?></a>. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été réélu<?= $gender['e'] ?></span>.</p>
      <?php elseif ($electionFeature['elected'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales dans la commune <a class="text-white" href="<?= base_url() ?>deputes/<?= $electionFeatureDistrict['dpt_slug'] ?>/ville_<?= $electionFeatureDistrict['commune_slug'] ?>"><?= $electionFeatureDistrict['nom_de'] ?></a>. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été éliminé<?= $gender['e'] ?></span>.</p>
      <?php elseif ($electionFeature['secondRound'] == "1"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales dans la commune <a class="text-white" href="<?= base_url() ?>deputes/<?= $electionFeatureDistrict['dpt_slug'] ?>/ville_<?= $electionFeatureDistrict['commune_slug'] ?>"><?= $electionFeatureDistrict['nom_de'] ?></a>. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été qualifié<?= $gender['e'] ?></span> pour le second tour.</p>
      <?php elseif ($electionFeature['secondRound'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales dans la commune <a class="text-white" href="<?= base_url() ?>deputes/<?= $electionFeatureDistrict['dpt_slug'] ?>/ville_<?= $electionFeatureDistrict['commune_slug'] ?>"><?= $electionFeatureDistrict['nom_de'] ?></a>. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> ne sera pas présent<?= $gender['e'] ?></span> au second tour.</p>
      <?php elseif ($electionFeature['candidature'] == "0") : ?>
        <p class="mb-0"><?= $title ?> <span class="font-weight-bold"> n'est pas candidat<?= $gender['e'] ?></span> aux élections municipales.</p>
      <?php else: ?>
        <p class="mb-0">
          <?= $title ?> <span class="font-weight-bold"> est candidat<?= $gender['e'] ?></span> aux élections municipales dans la commune <a class="text-white" href="<?= base_url() ?>deputes/<?= $electionFeatureDistrict['dpt_slug'] ?>/ville_<?= $electionFeatureDistrict['commune_slug'] ?>"><?= $electionFeatureDistrict['nom_de'] ?></a>.
          <?php if ($electionFeature['position'] === 'Tête de liste'): ?>
            <?= ucfirst($gender['pronom']) ?> est tête de liste.
          <?php endif; ?>
        </p>
      <?php endif; ?>
      <?php if ($electionFeature['candidature'] == 1 && $electionFeature['link']): ?>
        <span class="mt-3 url_obf btn btn-light" url_obf="<?= url_obfuscation($electionFeature['link']) ?>">Suivre sa campagne</span>
      <?php endif; ?>
      <a href="<?= base_url() ?>elections/municipales-2026" class="mt-3 btn btn-light">En savoir plus sur ces élections</a>
      <?php if($electionFeatureDistrict['population'] > url_obf_cities_election()): ?>
        <a href="<?= base_url() ?>elections/resultats/<?= url_election_paris($electionFeatureDistrict['dpt_slug'] . "/ville_" . $electionFeatureDistrict['commune_slug']) ?>" class="mt-3 btn btn-light">Résultats des élections <?= $electionFeatureDistrict['nom_a'] ?></a>
      <?php else: ?>
        <a url_obf="<?= url_obfuscation(base_url() . "elections/resultats/" . url_election_paris($electionFeatureDistrict['dpt_slug'] . "/ville_" . $electionFeatureDistrict['commune_slug'])) ?>" class="mt-3 btn btn-light url_obf">Résultats des élections <?= $electionFeatureDistrict['nom_a'] ?></a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
