<div class="pg-depute-individual iframe-container <?= $title_visibility === 'hidden' ? 'no-title' : '' ?>">   

  <h1 class="<?= $title_visibility ?>">
    Les données parlementaires et électorales de <?= $mp_full_name; ?>
  </h1>

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
  <img src="/assets/imgs/favicon/datan_favicon.ico" alt="logo de Datan" class="datan-logo">
  Ce service est proposé par le site <a href="https://datan.fr" target="_blank">datan.fr</a>.
</p>


</div>



<style>
.iframe-container {
padding : 15px;

}

.no-title .mt-5:first-of-type {
  margin-top: 0 !important;
}

.hidden {
    display: none;
}

.datan-credit {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px; 
  margin-bottom: 0 !important;
}

.datan-credit .datan-logo {
  max-width: 100%;
  max-height: 16px;
  height: auto;
}


@media (max-width: 768px) {
  .datan-credit {
    justify-content: center; 
  }
}


@media (max-width: 480px) {
  .datan-credit {
    font-size: 14px; 
  }

  .datan-credit .datan-logo {
    height: 14px; 
  }
}


</style>
