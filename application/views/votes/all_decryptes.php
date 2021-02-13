    <!-- NEW ELEMENT FROM HERE -->
    <div class="container-fluid pg-vote-decryptes" id="container-always-fluid">
      <div class="row pb-5">
        <div class="container">
          <div class="row bloc-titre">
            <div class="col-md-7 my-5">
              <h1 class="text-center text-md-left"><?= $title ?></h1>
            </div>
            <div class="col-md-10 mb-lg-5 mb-4">
              <p>
                L'équipe de Datan décrypte pour vous les votes les plus intéressants de la législature.
                Il s'agit des votes qui ont fait l'objet d'attention médiatique, ou sur lesquels un ou plusieurs groupes parlementaires étaient fortement divisés.
              </p>
              <p>
                Tous ces votes décryptés font l'objet d'une reformulation et d'une contextualisation, afin de les rendre plus accessibles et plus compréhensibles.
              </p>
              <p>
                Si vous voulez avoir accès à <b>tous les votes de l'Assemblée nationale</b>, qu'ils soient décryptés par nos soins ou non, <a href="<?= base_url() ?>votes/all">cliquez ici</a>.
              </p>
            </div>
            <div class="col-12 mb-lg-5 mb-4 d-lg-none">
              <h2><span><?= $number_votes['n'] ?></span> votes de l'Assemblée nationale décryptés par l'équipe de Datan</h2>
              <div class="categories mt-3">
                <?php foreach ($fields as $field): ?>
                  <a class="badge badge-primary no-decoration" href="<?= base_url() ?>votes/decryptes/<?= $field['slug'] ?>">
                    <span><?= $field['number'] ?></span> <?= $field['name'] ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="row bloc-sort">
            <div class="col-lg-3 d-none d-lg-block bloc-search">
              <div class="mt-md-4 sticky-top search-element">
                <div class="filters d-lg-block mt-md-4">
                  <input class="radio-btn" name="radio-collection" id="radio-1" type="radio" checked="" value="*">
                  <label for="radio-1" class="radio-label d-flex align-items-center">
                    <span class="d-flex align-items-center"><b>Tous les votes décryptés</b></span>
                  </label>
                  <?php $i=2 ?>
                  <?php foreach ($fields as $field): ?>
                    <input class="radio-btn" name="radio-collection" id="radio-<?= $i ?>" type="radio" value=".<?= $field['slug'] ?>">
                    <label class="radio-label d-flex align-items-center" for="radio-<?= $i ?>">
                      <span class="d-flex align-items-center"><?= $field['name'] ?></span>
                    </label>
                    <?php $i++ ?>
                  <?php endforeach; ?>
                </div>
                <div class="d-md-flex justify-content-center mt-md-5">
                  <a class="btn btn-outline-primary d-none d-md-block" href="<?= base_url() ?>votes/all">Liste de tous les votes de l'Assemblée</a>
                </div>
              </div>
            </div>
            <div class="col-lg-9 col-md-12">
              <div class="row mt-2 sorting">
                <?php foreach ($votes as $vote): ?>
                  <div class="col-lg-6 col-md-6 sorting-item <?= $vote['category_slug'] ?>">
                    <div class="card card-vote">
                      <div class="thumb d-flex align-items-center <?= $vote['sortCode'] ?>">
                        <div class="d-flex align-items-center">
                          <span><?= mb_strtoupper($vote['sortCode']) ?></span>
                        </div>
                      </div>
                      <div class="card-header d-flex flex-row justify-content-between">
                        <span class="date"><?= $vote['dateScrutinFR'] ?></span>
                      </div>
                      <div class="card-body d-flex align-items-center">
                        <span class="title">
                          <a href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>" class="stretched-link"></a>
                          <?= $vote['voteTitre'] ?></span>
                      </div>
                      <div class="card-footer">
                        <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
