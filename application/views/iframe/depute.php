<div class="pg-depute-individual iframe-container <?= $main_title_visibility === 'hidden' ? 'no-title' : '' ?> <?= $secondary_title_visibility === 'hidden' ? 'no-subtitle' : '' ?>">
  <?php if ($main_title_visibility !== 'hidden'): ?>
    <h1>
      Les données parlementaires et électorales de <?= $mp_full_name; ?>
    </h1>
  <?php endif; ?>
  <?php
  $allViews = [
    'positions-importantes' => 'deputes/partials/mp_individual/_key_positions.php',
    'derniers-votes' => 'deputes/partials/mp_individual/_votes.php',
    'election' => 'deputes/partials/mp_individual/_election.php',
    'comportement-politique' => 'deputes/partials/mp_individual/statistics/_index.php'
  ];
  foreach ($categories as $category) {
    if (isset($allViews[$category])) {
      $this->view($allViews[$category]);
    }
  }
  ?>
  <p class="datan-credit">
    <img src="/assets/imgs/favicon/datan_favicon.svg" alt="logo de Datan" class="datan-logo">
    Ce service est proposé par le site <a href="https://datan.fr" target="_blank">datan.fr</a>
  </p>
</div>