<div class="container pg-elections-candidats">
  <div class="row bloc-titre">
    <div class="col-10">
      <h1><?= $title ?></h1>
      <div class="mt-5">
        <p>
          Découvrez les résultats et candidats aux <?= mb_strtolower($election['libelle']) ?> <?= $election['dateYear'] ?>. Ces élections se déroulent en <?= $election['dateSecondRound'] ? "deux tours" : "un tour" ?>.
          <?php if ($election['dateSecondRound']): ?>
            Le premier tour <?= $today > $election['dateFirstRound'] ? "s'est tenu" : "se tiendra" ?> le <?= $election['dateFirstRoundFr'] ?>, tandis que le second tour <?= $today > $election['dateSecondRound'] ? "s'est déroulé" : "se déroulera" ?> le <?= $election['dateSecondRoundFr'] ?>.
          <?php else: ?>
            L'élection <?= $today > $election['dateFirstRound'] ? "s'est tenue" : "se tiendra" ?> le <?= $election['dateFirstRoundFr'] ?>.
          <?php endif; ?>
          Découvrez les résultats des élections commune par commune sur Datan.
        </p>
        <?php if($election['slug'] == 'municipales-2026'): ?>
          <div class="alert alert-primary mt-4" role="alert">
            Le premier tour des élections municipales se tiendra le dimanche 15 mars 2026. Les résultats seront diffusés le lendemain sur Datan.
          </div>
        <?php endif; ?>
        <div class="mt-5 infosGeneral">
          <h2 class="title ml-md-5 ml-3">Mieux comprendre</h2>
          <div class="px-4">
            <?= $electionInfos ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if($election['slug'] == 'municipales-2026'): ?>
  <div class="container-fluid pg-elections-candidats search py-5 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="city-search-block">
            <h2 class="mb-1">Recherchez votre commune</h2>
            <p class="text-muted mb-3" style="font-size: 0.9rem;">Accédez aux listes candidates et aux résultats dans votre commune</p>
          <div class="city-search-wrapper d-flex">
              <div class="city-search-input-wrapper flex-grow-1">
                  <svg class="city-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                  </svg>
                  <input type="text" id="citySearch" class="city-search-input" placeholder="Nom de commune (ex: Rennes, Lyon, Bordeaux...)">
              </div>
          </div>
        </div>
        <!-- TOP 10 CITIES -->
        <div class="top-cities mt-5">
          <p class="top-cities-label">Grandes villes</p>
          <div class="d-flex flex-wrap">
            <?php foreach(array_slice($communes, 0, 15) as $commune): ?>
              <a href="<?= base_url() ?>elections/resultats/<?= $commune['slug'] ?>/<?= $commune['commune_slug'] ?>" class="top-city-pill"><?= $commune['commune_nom'] ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="container pg-elections-candidats mt-4 mb-5">
  <div class="row">
    <?php if ($results): ?>
      <div class="col-10">
        <?php $this->load->view('elections/results/'.$election['slug'].'.php') ?>
      </div>
    <?php endif; ?>
  </div>
  <?php if ($election['candidates']): ?>
    <?php $this->load->view('elections/candidatesList/'.$election['slug'].'.php') ?>
  <?php endif; ?>
</div>
