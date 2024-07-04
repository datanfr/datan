<div class="container pg-elections-index">
  <div class="row bloc-titre">
    <div class="col-md-12">
      <h1><?= $title ?></h1>
    </div>
    <div class="col-md-8 col-lg-7 mt-4">
      <p>
        Les <b>élections</b> sont essentielles. Elles permettent aux citoyens de choisir leurs représentants, qui aurant pour rôle de rédiger et voter la loi.
      </p>
      <p>
        En France, il existe <a href="https://www.diplomatie.gouv.fr/fr/services-aux-francais/voter-a-l-etranger/les-differentes-elections/" target="_blank" rel="nofollow noreferrer noopener">plusieurs types d'élections</a>. La plus importante est <b>l'élection présidentielle</b>, qui se tient tous les cinq ans pour élire le Pésident de la République. Les <b>élections législatives</b> permettent d'élire les <a href="<?= base_url() ?>deputes">577 députés de l'Assemblée nationale</a>.
      </p>
      <p>
        Les autres élections en France sont les sénatoriales, les européennes, les régionales, les départementales et les municipales.
      </p>
      <p>
        Découvrez sur Datan les députés ou anciens députés qui se sont portés candidats à des élections politiques.
      </p>
    </div>
    <div class="col-md-4 d-none d-lg-flex align-items-center mt-4">
      <div class="px-4">
        <?= file_get_contents(asset_url()."imgs/svg/undraw_election_day_datan.svg") ?>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid pg-elections-index py-5 mt-5 bg-info">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h2 class="text-white text-center">Députés candidats aux prochaines élections</h2>
      </div>
      <div class="col-12 mt-3 d-flex flex-wrap">
        <?php foreach ($elections as $election): ?>
          <?php if (strtotime($election['dateFirstRound']) >= strtotime('-20 days')): ?>
            <?php $this->load->view('elections/partials/card.php', array('election' => $election)) ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
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
