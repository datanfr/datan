<div class="container pg-elections-index">
  <div class="row bloc-titre mt-5">
    <div class="col-md-12">
      <h1><?= $title ?></h1>
    </div>
    <div class="col-lg-8 mt-4">
      <p>
        Les <b>élections</b> permettent aux citoyens de choisir leurs représentants, qui ont pour rôle de rédiger et voter la loi.
      </p>
      <p>
        En France, il existe <a href="https://www.diplomatie.gouv.fr/fr/services-aux-francais/voter-a-l-etranger/les-differentes-elections/" target="_blank" rel="nofollow noreferrer noopener">plusieurs types d'élections</a>. La plus importante est <b>l'élection présidentielle</b>, qui se tient tous les cinq ans pour élire le Pésident de la République. Les <b>élections législatives</b> permettent d'élire les <a href="<?= base_url() ?>deputes">577 députés de l'Assemblée nationale</a>.
      </p>
      <p>
        Les autres élections en France sont les sénatoriales, les européennes, les régionales, les départementales et les municipales.
      </p>
      <p class="mb-0">
        Découvrez sur Datan les députés ou anciens députés qui se sont portés candidats à des élections politiques.
      </p>
    </div>
    <div class="col-lg-4 d-none d-lg-flex align-items-center mt-4">
      <div class="px-4">
        <?= file_get_contents(FCPATH . "assets/imgs/svg/undraw_election_day_datan.svg") ?>
      </div>
    </div>
  </div>
</div>
<?php if($election_future): ?>
  <div class="container-fluid pg-elections-index py-5 mt-5" id="pattern_background">
    <div class="container">
      <div class="row">
        <div class="col-md-6 d-flex align-items-center">
          <div>
            <h2 class="text-info">Les prochaines élections en France</h2>
            <p class="mt-3">Les prochaines élections en France sont les <b>élections municipales</b>. Elles auront lieu les dimanche 15 et 22 mars 2026.</p>
            <p>Les élections municipales ont lieu tous les six ans et permettent d'élire les conseillers municipaux de chaque commune, ainsi que le maire et ses adjoints.</p>
            <p>Découvrez les <b>résultats des élections</b> commune par commune sur Datan.</p>
            <div class="d-flex justify-content-center justify-content-md-start">
              <a href="<?= base_url() ?>elections/municipales-2026" type="button" class="btn btn-info mt-3">Suivre les municipales 2026</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 mt-md-0 mt-5 d-flex flex-wrap">
          <?php foreach ($elections as $election): ?>
            <?php if (strtotime($election['dateFirstRound']) >= strtotime('-20 days')): ?>
              <?php $this->load->view('elections/partials/card.php', array('election' => $election)) ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php $this->view('elections/partials/_search.php') ?>
<div class="container pg-elections-index my-5">
  <div class="row">
    <div class="col-12">
      <h2 class="text-center">Les élections passées</h2>
    </div>
    <div class="col-12 mt-3 d-flex flex-wrap">
      <?php foreach ($elections as $election): ?>
        <?php if (strtotime($election['dateFirstRound']) < strtotime('-20 days')): ?>
          <?php $this->load->view('elections/partials/card.php', array('election' => $election)) ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>