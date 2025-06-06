<!-- BLOC VOTES -->
<?php if (!empty($votes_datan)) : ?>
  <div class="bloc-votes carousel-container mt-5">
    <div class="row">
      <div class="col-12">
        <div class="d-flex <?= (isset($iframe_title_visibility) && $iframe_title_visibility === 'hidden') ? 'justify-content-end' : 'justify-content-between' ?> mb-4">
          <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
            <h2 class="title-center"><?= $first_person ? 'Mes' : 'Ses' ?> derniers votes</h2>
          <?php endif; ?>
          <div class="bloc-carousel-votes">
            <a class="btn see-all-votes mx-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes">
              <span>VOIR TOUS</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="row bloc-carousel-votes-flickity">
      <div class="col-12 carousel-cards">
        <?php foreach ($votes_datan as $vote) : ?>
          <?php $this->load->view('deputes/partials/card_vote.php', array('vote' => $vote)) ?>
        <?php endforeach; ?>
        <div class="card card-vote see-all">
          <div class="card-body d-flex align-items-center justify-content-center">
            <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes" class="stretched-link no-decoration">VOIR TOUS</a>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <!-- BUTTONS BELOW -->
      <div class="carousel-buttons col-12 d-flex justify-content-center">
        <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
          <?= file_get_contents(asset_url() . "imgs/icons/arrow_left.svg") ?>
        </button>
        <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
          <?= file_get_contents(asset_url() . "imgs/icons/arrow_right.svg") ?>
        </button>
      </div>
    </div>
  </div> <!-- // END BLOC VOTES -->
<?php endif; ?>