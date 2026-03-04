<div class="container pg-elections-candidats">
  <div class="row bloc-titre mt-5">
    <div class="col-lg-10">
      <h1><?= $title ?></h1>
      <div class="mt-4">
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
  <div class="mt-5">
    <?php $this->view('elections/partials/_search.php') ?>
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
