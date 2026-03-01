<!-- BLOC SOCIAL-MEDIA -->
<div class="bloc-links p-lg-0 p-md-2 mt-5">
  <h2 class="title-center">Suivez l'action de <?= $title ?></h2>
  <div class="row mt-4">
    <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
      <span class="url_obf btn btn-an" url_obf="<?= url_obfuscation("http://www2.assemblee-nationale.fr/deputes/fiche/OMC_" . $depute['mpId']) ?>">Profil officiel</span>
    </div>
    <?php if ($depute['website'] !== NULL) : ?>
      <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
        <span class="url_obf btn btn-website" url_obf="<?= url_obfuscation("https://" . $depute['website']) ?>">Site internet</span>
      </div>
    <?php endif; ?>
    <?php if ($depute['facebook'] !== NULL) : ?>
      <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
        <span class="url_obf btn btn-fcb" url_obf="<?= url_obfuscation("https://www.facebook.com/" . $depute['facebook']) ?>">
          <?= file_get_contents(FCPATH . '/assets/imgs/logos/facebook_svg.svg') ?>
          <span class="ml-3">Facebook</span>
        </span>
      </div>
    <?php endif; ?>
    <?php if ($depute['bluesky'] !== NULL) : ?>
      <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
        <span class="url_obf btn btn-bluesky" url_obf="<?= url_obfuscation("https://bsky.app/profile/" . $depute['bluesky']) ?>">
          <?= file_get_contents(FCPATH . '/assets/imgs/logos/bluesky_svg.svg') ?>
          <span class="ml-3">Bluesky</span>
        </span>
      </div>
    <?php endif; ?>
    <?php if ($depute['twitter'] !== NULL) : ?>
      <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
        <span class="url_obf btn btn-twitter" url_obf="<?= url_obfuscation("https://x.com/" . ltrim($depute['twitter'], '@')) ?>">
          <?= file_get_contents(FCPATH . '/assets/imgs/logos/x-no-round.svg') ?>
        </span>
      </div>
    <?php endif; ?>
  </div>
</div> <!-- END BLOC SOCIAL MEDIA -->