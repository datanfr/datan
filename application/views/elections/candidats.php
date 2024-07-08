<div class="container pg-elections-candidats">
  <div class="row bloc-titre">
    <div class="col-12">
      <h1><?= $title ?></h1>
    </div>
  </div>
</div>
<div class="container-fluid pg-elections-candidats infosIndividual py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-lg-7 my-4">
        <h2>Informations</h2>
        <p>Les <?= mb_strtolower($election['libelle']) ?> <?= $election['dateYear'] ?> se déroulent en <?= $election['dateSecondRound'] ? "deux tours" : "un tour" ?>.</p>
        <?php if ($election['dateSecondRound']): ?>
          <p>Le premier tour <?= $today > $election['dateFirstRound'] ? "s'est tenu" : "se tiendra" ?> le <?= $election['dateFirstRoundFr'] ?>, tandis que le second tour <?= $today > $election['dateSecondRound'] ? "s'est déroulé" : "se déroulera" ?> le <?= $election['dateSecondRoundFr'] ?>.</p>
        <?php else: ?>
          <p>L'élection <?= $today > $election['dateFirstRound'] ? "s'est tenue" : "se tiendra" ?> le <?= $election['dateFirstRoundFr'] ?>.</p>
        <?php endif; ?>
        <?php if ($election['candidates']): ?>
          <p>
            Découvrez sur cette page les députés candidats aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.
          </p>
          <p>
            <?php if ($candidatsN): ?>
              Nous avons répertorié <b><?= $candidatsN ?> député<?= $candidatsN > 1 ? "s" : NULL ?> candidat<?= $candidatsN > 1 ? "s" : NULL ?></b>.
            <?php else: ?>
              Nous avons jusqu'à présent répertorié <span class="font-weight-bold">aucun député candidat</span>.
            <?php endif; ?>
          </p>
          <?php if ($state == 1): ?>
            <p>
              De ces candidats, <b><?= $candidatsN_second ?> député<?= $candidatsN_second > 1 ? "s se sont maintenus" : " s'est maintenu" ?> pour le second tour</b>.
            </p>
          <?php endif; ?>
          <?php if ($state == 2): ?>
            <p>Au total, <b><?= $candidatsN_elected ?></b> députés en exercice ont été élus lors des <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.</p>
          <?php endif; ?>
          </p>
        <?php endif; ?>
      </div>
      <div class="col-md-4 col-lg-5 d-flex justify-content-center align-items-center">
        <?php if (!empty($election['resultsUrl'])): ?>
          <span class="url_obf btn btn-light btn-lg" url_obf="<?= url_obfuscation($election['resultsUrl']) ?>">
            Résultats de l'élection
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="container pg-elections-candidats mt-4 mb-5">
  <div class="row">
    <?php if ($results == true): ?>
      <div class="col-md-8 col-lg-7">
        <?php $this->load->view('elections/results/'.$election['slug'].'.php') ?>
      </div>
      <?php if (!empty($electionInfos)): ?>
        <div class="col-md-8 col-lg-4 offset-lg-1 mt-5 mt-lg-0">
          <div class="mt-4 infosGeneral">
            <h2 class="title ml-md-5 ml-3">Mieux comprendre</h2>
            <div class="px-4">
              <?= $electionInfos ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <?php if (!empty($electionInfos)): ?>
        <div class="col-12">
          <div class="mt-4 infosGeneral">
            <h2 class="title ml-md-5 ml-3">Mieux comprendre</h2>
            <div class="px-4">
              <?= $electionInfos ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <?php if ($election['candidates']): ?>
    <?php $this->load->view('elections/candidatesList/'.$election['slug'].'.php') ?>
  <?php endif; ?>
</div>
