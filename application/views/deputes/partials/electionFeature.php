<!-- Election Feature -->
<?php if ($electionFeature): ?>
  <div class="card card-election-feature <?= $electionFeature['color'] ?> mb-4 border-0" style="overflow: hidden">
    <div class="card-body">
      <h2>🗳️ Législatives 2022</h2>
      <?php if ($electionFeature['elected'] == "1"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> à sa réélection. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été réélu<?= $gender['e'] ?></span>.</p>
        <p class="mb-0">Sa page Datan sera prochainement mise à jour pour son nouveau mandat.</p>
      <?php elseif ($electionFeature['elected'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> à sa réélection. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été éliminé<?= $gender['e'] ?></span>.</p>
      <?php elseif ($electionFeature['secondRound'] == "1"): ?>
        <p class="mb-0"><?= $title ?> est candidat<?= $gender['e'] ?> à sa réélection. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> a été qualifié<?= $gender['e'] ?></span> pour le second tour.</p>
      <?php elseif ($electionFeature['secondRound'] == "0"): ?>
        <p class="mb-0"><?= $title ?> était candidat<?= $gender['e'] ?> à sa réélection. <span class="font-weight-bold"><?= ucfirst($gender['pronom']) ?> n'a pas a été qualifié<?= $gender['e'] ?></span> pour le second tour.</p>
      <?php elseif ($electionFeature['candidature'] == "0") : ?>
        <p class="mb-0"><?= $title ?> <span class="font-weight-bold"> n'est pas candidat<?= $gender['e'] ?></span> à sa réélection.</p>
      <?php endif; ?>
      <?php if ($electionFeature['candidature'] == 1 && $electionFeature['link']): ?>
        <span class="mt-3 url_obf btn btn-light" url_obf="<?= url_obfuscation($electionFeature['link']) ?>">Suivre sa campagne</span>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
