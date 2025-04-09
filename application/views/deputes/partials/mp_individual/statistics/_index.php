<!-- BLOC STATISTIQUES -->
<?php if (in_array($depute['legislature'], legislature_all())) : ?>
  <div class="bloc-statistiques mt-5">
    <h2 class="mb-3 title-center">
      Son comportement politique
      <?php if ($depute['legislature'] != legislature_current()): ?>
        (<?= $depute['legislature'] ?><sup>e</sup> législature)
      <?php endif; ?>
    </h2>
    <?php $this->view('deputes/partials/mp_individual/statistics/_voting_participation.php') ?>
    <?php $this->view('deputes/partials/mp_individual/statistics/_intra_group_loyalty.php') ?>
    <?php $this->view('deputes/partials/mp_individual/statistics/_inter_group_loyalty.php') ?>
  </div> <!-- // END BLOC STATISTIQUES -->
<?php endif; ?>