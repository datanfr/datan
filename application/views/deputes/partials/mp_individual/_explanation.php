<!-- BLOC EXPLICATION -->
<?php if ($explication): ?>
  <div class="bloc-explication mt-5">
    <h2 class="mb-4 title-center"><?= $first_person ? 'Ma' : 'Sa' ?> dernière explication de vote</h2>
    <div class="card border-primary">
      <div class="card-body">
        <p class="title mb-1">
          <a class="no-decoration underline" href="<?= base_url() ?>votes/legislature-<?= $explication['legislature'] ?>/vote_<?= is_congress_numero($explication['voteNumero']) ?>"><?= $explication['title'] ?></a>
        </p>
        <p class="date mb-4">Scrutin du <?= $explication['dateScrutinFR'] ?></p>
        <p class="mb-2">
          <span class="badge badge-<?= mb_strtolower($explication['vote_depute']) ?>"><?= mb_strtoupper($explication['vote_depute']) ?></span>
        </p>
        <p>
          <?= $first_person
            ? "Je " . $explication['vote_depute_edito'] . " ce vote. Découvrez mon explication."
            : ucfirst($gender['le']) . " " . $gender['depute'] . " <span class='font-weight-bold'>" . $title . "</span> " . $explication['vote_depute_edito'] . " ce vote. Découvrez son explication."
          ?>
        </p>
        <blockquote>
          <p class="quoted"><?= $explication['text'] ?></p>
        </blockquote>
      </div>
    </div>
  </div>
<?php endif; ?>