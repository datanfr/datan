<!-- BLOC RAPPORT -->
<!-- CHECK WITH THIS ONE: https://www.assemblee-nationale.fr/dyn/deputes/PA793174 -->
<?php if($rapports): ?>
  <div class="bloc-questions mt-5">
    <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
      <h2 class="mb-4 title-center"><?= $first_person ? 'Mes' : 'Ses' ?> rapports d'information</h2>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">

        <div class="mb-4">

          <?php foreach ($rapports as $rapport): ?>
            <div class="question">
              <div class="d-flex justify-content-between mb-2">
                <div class="date">DATE A FAIRE (dans daily first)</div>
              </div>
              <div class="title mb-2"><?= ucfirst($rapport['titre']) ?></div>
              <div class="title mb-2"><?= ucfirst($rapport['id']) ?></div>
              <div class="title mb-2"><?= ucfirst($rapport['commissionAbrev']) ?></div>
              <div class="mb-2 d-flex justify-content-end">
                <a class="underline" href="https://www.assemblee-nationale.fr/dyn/<?= $rapport['legislature'] ?>/rapports/<?= mb_strtolower($rapport['commissionAbrev']) ?>/l<?= $rapport['legislature'] ?>b<?= $rapport['numNotice'] ?>_rapport-information"  target="_blank">
                    Lire le rapport
                    <?= file_get_contents(asset_url()."imgs/icons/arrow_external_right.svg") ?>
                </a>
              </div>
            </div>   
          <?php endforeach; ?>

        </div>
      </div>

    </div>
  </div>
<?php endif; ?>
<!-- // END BLOC RAPPORTS -->