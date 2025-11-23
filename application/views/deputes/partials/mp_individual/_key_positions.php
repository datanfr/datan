<!-- BLOC POSITIONS CLEFS -->
<?php if ($key_votes) : ?>
  <div class="bloc-key-votes mt-5">
    <div class="row">
      <div class="col-12">
        <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
          <h2 class="mb-4 title-center"><?= $first_person ? 'Mes' : 'Ses' ?> positions importantes</h2>
        <?php endif; ?>
        <div class="card">
          <div class="card-body key-votes">
            <?php foreach ($key_votes as $key => $value): ?>
              <div class="row">
                <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                  <span class="sort-<?= $value["vote_libelle"] ?>"><?= mb_strtoupper($value["vote_libelle"]) ?></span>
                </div>
                <div class="col-md-9 value">
                  <?= !$first_person ? $title : '' ?>
                  <b>
                    <?php if ($value['vote'] === "1") : ?>
                      <?= $first_person ? "J'ai voté en faveur de" : "a voté en faveur de" ?>
                    <?php elseif ($value['vote'] === "-1") : ?>
                      <?= $first_person ? "J'ai voté contre" : "a voté contre" ?>
                    <?php else : ?>
                      <?= $first_person ? "Je me suis abstenu{$gender['e']} sur le vote concernant" : "s'est abstenu{$gender['e']} sur le vote concernant" ?>
                    <?php endif; ?>
                    <?= $value['text'] ?></b>.
                  <?= $first_person
                    ? ($value["scoreLoyaute"] === "1" ? "J'ai voté" : "Je n'ai pas voté") . " comme mon groupe."
                    : ucfirst($gender["pronom"]) . " " . ($value["scoreLoyaute"] === "1" ? "a voté" : "n'a pas voté") . " comme son groupe." ?>
                  <a href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" class="font-italic">Voir le vote</a>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>