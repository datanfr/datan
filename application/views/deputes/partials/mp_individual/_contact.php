<!-- BLOC CONTACT -->
<?php if ($depute['mailAn'] !== NULL && $active) : ?>
  <div class="bloc-links p-lg-0 p-md-2 <?= !$page_history ? "mt-5" : "my-5" ?>">
    <h2 class="title-center">Contactez <?= $title ?></h2>
    <div class="row mt-4">
      <div class="col-12">
        <span class="mr-4">
          <?= file_get_contents(FCPATH . '/assets/imgs/icons/envelope-fill.svg') ?>
        </span>
        <a href="mailto:<?= $depute['mailAn'] ?>" class="no-decoration underline text-dark"><?= $depute['mailAn'] ?></a>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- END BLOC CONTACT -->