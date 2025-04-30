<!-- BLOC QUESTIONS -->
<div class="bloc-election mt-5">

  <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
    <h2 class="mb-4 title-center"><?= $first_person ? 'Mes' : 'Ses' ?> questions au gouvernement</h2>
  <?php endif; ?>


  <div class="card">
    <div class="card-body">
      
      <?php foreach ($questions as $question): ?>
        <p>date=<?= $question['datePublished'] ?></p>
        <p>titre=<?= $question['analyse'] ?></p>
        <p>type=<?= $question['type'] ?></p>
        <p>category=<?= $question['rubrique'] ?></p>
        <p>text=<?= word_limiter($question['content'], 50) ?></p>
        <p>uid=<?= $question['uid'] ?></p>
      <?php endforeach; ?>

    </div>
  </div>
</div>
<!-- // END BLOC QUESTIONS -->