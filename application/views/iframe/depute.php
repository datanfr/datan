<div class="pg-depute-individual iframe-container <?= $main_title_visibility === 'hidden' ? 'no-title' : '' ?> <?= $secondary_title_visibility === 'hidden' ? 'no-subtitle' : '' ?>">
  <?php if ($main_title_visibility !== 'hidden'): ?>
    <h1>
  <?= $first_person
    ? 'Mon activité de ' . $gender['depute']
    : 'L\'activité de ' . $gender['depute'] . ' de ' . $mp_full_name; ?>
</h1>

  <?php endif; ?>

  <?php
  foreach ($views_to_load as $view): ?>
    <?php if (strpos($view, 'statistics') !== false && !$title_displayed && $has_comportement_subcategories): ?>
      <div class="bloc-statistiques mt-5">
        <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
          <h2 class="mb-3 title-center">
            <?= $first_person ? 'Mon comportement politique' : 'Son comportement politique' ?>
            <?php if ($depute['legislature'] != legislature_current()): ?>
              (<?= $depute['legislature'] ?><sup>e</sup> législature)
            <?php endif; ?>
          </h2>
        <?php endif; ?>
        <?php $title_displayed = true; ?>
      <?php endif; ?>
      <?php $this->view($view); ?>
      <?php if (strpos($view, 'statistics') !== false && !$title_displayed && $has_comportement_subcategories): ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <p class="datan-credit">
    <img src="/assets/imgs/favicon/datan_favicon.svg" alt="logo de Datan" class="datan-logo">
    Ce service est proposé par le site <a href="https://datan.fr" target="_blank">datan.fr</a>
  </p>
</div>