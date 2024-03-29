    <!-- NEW ELEMENT FROM HERE -->
    <div class="container-fluid pg-vote-decryptes" id="container-always-fluid">
      <div class="row pb-5">
        <div class="container">
          <div class="row bloc-titre">
            <div class="col-md-7 my-5">
              <h1 class="text-center text-md-left">
                <?= $title ?>
              </h1>
            </div>
            <div class="col-md-10 mb-lg-5 mb-4">
              <p>
                L'équipe de Datan décrypte pour vous les votes les plus intéressants de la législature.
                Il s'agit des votes qui ont fait l'objet d'attention médiatique, ou sur lesquels un ou plusieurs groupes parlementaires étaient fortement divisés.
                Cette page présente les votes sur <b><?= $field['libelle'] ?></b>.
              </p>
              <p>
                Tous ces votes décryptés font l'objet d'une reformulation et d'une contextualisation, afin de les rendre plus accessibles et plus compréhensibles.
                Pour en savoir plus, <a href="#">cliquez ici</a>.
              </p>
              <p>
                Si vous voulez avoir accès à <b>tous</b> les votes de l'Assemblée nationale, qu'ils soient décryptés par nos soins ou non, <a href="<?= base_url() ?>votes/legislature-<?= legislature_current() ?>">cliquez ici</a>.
              </p>
            </div>
            <div class="col-12 mb-lg-5 mb-4 d-lg-none">
              <a class="no-decoration btn btn-primary" href="<?= base_url() ?>votes/decryptes"><i class="fas fa-arrow-left"></i> Tous les votes décryptés</a>
            </div>
          </div>
          <div class="row bloc-sort">
            <div class="col-12">
              <div class="row mt-2 votes-sort">
                <?php foreach ($votes as $vote): ?>
                  <div class="col-lg-4 col-md-6 votes-sort-item <?= $vote['category_slug'] ?>">
                    <?php $this->load->view('votes/partials/card_vote.php', array('vote' => $vote)) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
