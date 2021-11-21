    <!-- NEW ELEMENT FROM HERE -->
    <div class="container-fluid pg-vote-all" id="container-always-fluid">
      <div class="row">
        <div class="container">
          <div class="row row-grid bloc-titre mb-5">
            <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
              <h1>Les votes décryptés par Datan</h1>
            </div>
            <div class="col-lg-4 col-md-7 mt-md-0">
              <p>
                L'équipe de Datan décrypte pour vous les votes les plus intéressants de la législature.
                Il s'agit des votes qui ont fait l'objet d'attention médiatique, ou sur lesquels un ou plusieurs groupes parlementaires étaient fortement divisés.
              </p>
              <p>
                Tous ces votes décryptés font l'objet d'une reformulation et d'une contextualisation, afin de les rendre plus accessibles et plus compréhensibles.
              </p>
              <p>
                Si vous voulez avoir accès à <b>tous</b> les votes de l'Assemblée nationale, qu'ils soient décryptés par nos soins ou non, <a href="<?= base_url() ?>votes/legislature-<?= legislature_current() ?>">cliquez ici</a>.
              </p>
            </div>
            <div class="col-md-4 d-none d-lg-block">
              <div class="px-4">
                <?= file_get_contents(asset_url()."imgs/svg/undraw_voting_nvu7.svg") ?>
              </div>
            </div>
            <div class="col-12 carousel-container bloc-carousel-votes-flickity">
              <?php $this->load->view('votes/partials/votes_carousel.php', array('votes' => $votes_datan)) ?>
              <div class="carousel-buttons d-flex justify-content-center">
                <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
                </button>
                <a class="btn see-all-carousel mx-2" href="<?= base_url() ?>votes/decryptes">
                  <span>VOIR TOUS LES VOTES</span>
                </a>
                <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- BY CATEGORY -->
      <div class="row bloc-category" id="pattern_background">
        <div class="container py-5">
          <div class="row pb-5">
            <div class="col-12">
              <h2>Derniers votes décryptés par catégorie</h2>
            </div>
          </div>
          <?php foreach ($by_field as $field): ?>
            <div class="row my-5">
              <div class="col-2 col-md-1 logo-field d-flex justify-content-center align-items-center my-3 my-lg-0">
                <?php if ($field["logo"]): ?>
                  <div class="logo">
                    <img src="<?= asset_url().'imgs/fields/'.$field['slug'].'.svg' ?>" alt="<?= $field['slug'] ?>" width="792" height="612">
                  </div>
                <?php endif; ?>
              </div>
              <div class="col-10 col-md-11 col-lg-2 d-flex justify-content-start align-items-end align-items-lg-center my-3 my-lg-0">
                <h3 class="ml-4 ml-lg-0 mb-0"><?= $field['name'] ?></h3>
              </div>
              <div class="col-lg-7 col-md-11 offset-md-1 offset-lg-0 d-flex justify-content-center justify-content-md-start flex-wrap my-3 my-lg-0">
                <?php foreach ($field['votes'] as $vote): ?>
                  <?php $this->load->view('votes/partials/card_vote.php', array('vote' => $vote)) ?>
                <?php endforeach; ?>
              </div>
              <div class="col-md-11 col-lg-2 offset-md-1 offset-lg-0 d-flex justify-content-center align-items-center my-3 my-lg-0">
                <a class="btn see-all-votes py-1" href="<?= base_url() ?>votes/decryptes/<?= $field['slug'] ?>">
                  <span>VOIR TOUS</span>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- ALL THE VOTES -->
      <div class="row">
        <div class="container py-5">
          <div class="row pb-5">
            <div class="col-12">
              <h2>Derniers votes non-decryptés de l'Assemblée nationale</h2>
            </div>
          </div>
          <div class="row mt-5"> <!-- CARDS -->
            <div class="col-12 carousel-container bloc-carousel-votes-flickity">
              <div class="carousel-cards">
                <?php foreach ($votes as $vote): ?>
                  <div class="card card-vote">
                    <div class="thumb d-flex align-items-center <?= $vote['sortCode'] ?>">
                      <div class="d-flex align-items-center">
                        <span><?= mb_strtoupper($vote['sortCode']) ?></span>
                      </div>
                    </div>
                    <div class="card-header d-flex flex-row justify-content-between">
                      <span class="date"><?= months_abbrev($vote['dateScrutinFR']) ?></span>
                    </div>
                    <div class="card-body d-flex align-items-center">
                      <span class="title">
                        <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link no-decoration"><?= ucfirst(word_limiter($vote['titre'], 20, " ...")) ?></a>
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="carousel-buttons d-flex justify-content-center">
                <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
                </button>
                <a class="btn see-all-carousel mx-2" href="<?= base_url() ?>votes/decryptes">
                  <span>VOIR TOUS LES VOTES</span>
                </a>
                <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ARCHIVES -->
      <div class="row" id="pattern_background">
        <div class="container py-5">
          <div class="row pb-5">
            <div class="col-12">
              <h2>Archives</h2>
            </div>
          </div>
          <div class="row pb-4">
            <div class="col-12 text-center">
              <span>
                Pour voir tous les votes,
                <a href="<?= base_url() ?>votes/legislature-<?= legislature_current() ?>">cliquez ici</a>.
              </span>
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex flex-row flex-wrap">
              <?php foreach ($years as $year): ?>
                <div class="flex-fill text-center px-1 py-2">
                  <div class="year d-flex flex-column align-items-center">
                    <div class="my-2 d-flex justify-content-center align-items-center">
                      <div class="d-flex justify-content-center align-items-center">
                        <span><a href="<?= base_url() ?>votes/legislature-<?= legislature_current() ?>/<?= $year?>" class="no-decoration underline-blue"><?= $year ?></a></span>
                      </div>
                    </div>
                  </div>
                  <div class="months mt-4 d-flex flex-column align-items-center">
                    <?php foreach ($months as $month): ?>
                      <?php if ($month['years'] == $year): ?>
                        <div class="my-2 d-flex justify-content-center align-items-center">
                          <div class="d-flex justify-content-center align-items-center">
                            <a href="<?= base_url() ?>votes/legislature-<?= legislature_current() ?>/<?= $year?>/<?= $month['index'] ?>" class="no-decoration underline-blue"><?= ucfirst($month["month"]) ?></a>
                          </div>
                        </div>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
