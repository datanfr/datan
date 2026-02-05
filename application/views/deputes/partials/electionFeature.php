<!-- Election Feature -->
<?php if ($electionFeature): ?>
  <div class="card card-election-feature <?= $electionFeature['color'] ?> mt-5 mb-4 border-0" style="overflow: hidden">
    <div class="card-body">
      <h2>🗳️ Municipales 2026</h2>
      <?php if ($electionFeature['elected'] == "1"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été réélu<?= $gender['e'] ?></span>.</p>
        <p class="mb-0">Sa page Datan sera prochainement mise à jour pour son nouveau mandat.</p>
      <?php elseif ($electionFeature['elected'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été éliminé<?= $gender['e'] ?></span>.</p>
      <?php elseif ($electionFeature['secondRound'] == "1"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été qualifié<?= $gender['e'] ?></span> pour le second tour.</p>
      <?php elseif ($electionFeature['secondRound'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> aux élections municipales. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> ne sera pas présent<?= $gender['e'] ?></span> au second tour.</p>
      <?php elseif ($electionFeature['candidature'] == "0") : ?>
        <p class="mb-0"><?= $title ?> <span class="font-weight-bold"> n'est pas candidat<?= $gender['e'] ?></span> aux élections municipales.</p>
      <?php else: ?>
        <p class="mb-0"><?= $title ?> <span class="font-weight-bold"> est candidat<?= $gender['e'] ?></span> aux élections municipales.</p>
      <?php endif; ?>
      <?php if ($electionFeature['candidature'] == 1 && $electionFeature['link']): ?>
        <span class="mt-3 url_obf btn btn-light" url_obf="<?= url_obfuscation($electionFeature['link']) ?>">Suivre sa campagne</span>
      <?php endif; ?>
      <a href="<?= base_url() ?>elections/municipales-2026" class="mt-3 btn btn-light">En savoir plus sur l'élection</a>
    </div>
  </div>
<?php endif; ?>
