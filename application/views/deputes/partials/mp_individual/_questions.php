<!-- BLOC QUESTIONS -->
<div class="bloc-questions mt-5">

  <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
    <h2 class="mb-4 title-center"><?= $first_person ? 'Mes' : 'Ses' ?> questions au gouvernement</h2>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">

    <div class="mb-4">

      <?php foreach ($questions as $question): ?>
        <div class="question">
          <div class="d-flex justify-content-between mb-2">
            <div class="date"><?= months_abbrev($question['datePublishedFR']) ?></div>
            <span class="type badge"><?= $question['type_libelle'] ?></span>
          </div>
          <div class="title mb-2"><?= $question['analyse'] ?></div>
          <div class="category mb-2">
            <?= file_get_contents(asset_url()."imgs/icons/folder-fill.svg") ?>
            <?= ucfirst($question['rubrique']) ?>
          </div>
          <div class="mb-2"><?= word_limiter($question['content'], 25) ?></div>
          <a href="https://www.assemblee-nationale.fr/dyn/<?= $question['legislature'] ?>/questions/<?= $question['uid'] ?>"  target="_blank">
            Lire plus
            <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
          </a>
        </div>   
      <?php endforeach; ?>

    </div>

      <div class="text-center">
        <a class="btn btn-primary" href="https://www.assemblee-nationale.fr/dyn/deputes/<?= $depute['mpId'] ?>/questions">
          Voir toutes les questions
        </a>
      </div>

    </div>
  </div>
</div>
<!-- // END BLOC QUESTIONS -->