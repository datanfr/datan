<!-- BLOC POSITIONS CLEFS -->
<?php if ($key_votes) : ?>
  <div class="bloc-key-votes mt-5">
    <div class="row">
      <div class="col-12">
        <h2 class="mb-4 title-center">Ses positions importantes</h2>
        <div class="card">
          <div class="card-body key-votes">
            <?php foreach ($key_votes as $key => $value): ?>
              <div class="row">
                <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                  <span class="sort-<?= $value["vote_libelle"] ?>"><?= mb_strtoupper($value["vote_libelle"]) ?></span>
                </div>
                <div class="col-md-9 value">
                  <?= $title ?><b>
                    <?php if ($value['vote'] === "1") : ?>
                      a voté en faveur de
                    <?php elseif ($value['vote'] === "-1") : ?>
                      a voté contre
                    <?php else : ?>
                      s'est abstenu<?= $gender['e'] ?> sur le vote concernant
                    <?php endif; ?>
                    <?= $value['text'] ?></b>.
                  <?= ucfirst($gender["pronom"]) ?> <?= $value["scoreLoyaute"] === "1" ? "a voté " : "n'a pas voté " ?>comme son groupe.
                  <a href="<?= base_url() ?>votes/legislature-16/vote_<?= $value['voteNumero'] ?>" class="font-italic">Voir le vote</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>