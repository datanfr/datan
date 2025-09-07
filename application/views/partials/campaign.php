<?php
  $wrapperClasses = isset($wrapper_classes) ? implode(' ', $wrapper_classes) : '';
?>

<section id="campaign" class="container-fluid d-none <?= $wrapperClasses ?>" >
  <div class="container d-flex justify-content-center flex-column align-items-center py-4 ">
      <div class="icon"><?= file_get_contents(asset_url() . 'imgs/icons/heart-fill.svg') ?></div>
      <div id="campaign-paragraph" class="text-center my-4"></div>
      <a class="btn btn-primary" href="https://www.helloasso.com/associations/datan/formulaires/1" target="_blank" rel="noopener">Faire un don</a>
  </div>
</section>

<script src="<?= asset_url() ?>js/datan/campaigns.js" type="text/javascript"></script>
