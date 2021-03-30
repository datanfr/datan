<div class="container pg-elections-index mb-5">
  <div class="row bloc-titre">
    <div class="col-md-12">
      <h1><?= $title ?></h1>
    </div>
    <div class="col-md-8 col-lg-7">
      <div class="mt-4">
        <p>
          Ipsam vero urbem Byzantiorum fuisse refertissimam atque ornatissimam signis quis ignorat? Quae illi, exhausti sumptibus bellisque maximis, cum omnis Mithridaticos impetus totumque Pontum armatum affervescentem in Asiam atque erumpentem, ore repulsum et cervicibus interclusum suis sustinerent, tum, inquam, Byzantii et postea signa illa et reliqua urbis ornanemta sanctissime custodita tenuerunt.
        </p>
        <p>
          Hinc ille commotus ut iniusta perferens et indigna praefecti custodiam protectoribus mandaverat fidis. quo conperto Montius tunc quaestor acer quidem sed ad lenitatem propensior, consulens in commune advocatos palatinarum primos scholarum adlocutus est mollius docens nec decere haec fieri nec prodesse addensque vocis obiurgatorio sonu quod si id placeret, post statuas Constantii deiectas super adimenda vita praefecto conveniet securius cogitari.
        </p>
      </div>
    </div>
    <div class="col-md-4 offset-lg-1 d-none d-md-flex align-items-center">
      <div class="px-4">
        <?= file_get_contents(asset_url()."imgs/svg/undraw_election_day_datan.svg") ?>
      </div>
    </div>
  </div>
  <div class="row my-5">
    <div class="col-12">
      <h2 class="my-4">Découvrez les députés candidats à des élections</h2>
    </div>
    <div class="col-12 d-flex flex-wrap">
      <?php foreach ($elections as $election): ?>
        <div class="card card-election">
          <div class="liseret" style="background-color: <?= $electionsColor[$election['libelleAbrev']] ?>"></div>
          <div class="card-body d-flex flex-column justify-content-center align-items-center">
            <h2 class="d-block card-title">
              <a href="<?= base_url(); ?>elections/<?= mb_strtolower($election['slug']) ?>" class="stretched-link no-decoration"><?= $election['libelleAbrev'] ?> <?= $election['dateYear'] ?></a>
            </h2>
            <span class="mt-3">1<sup>er</sup> tour : <?= $election['dateFirstRoundFr'] ?></span>
            <span>2<sup>nd</sup> tour : <?= $election['dateSecondRoundFr'] ?></span>
          </div>
          <div class="card-footer d-flex justify-content-center align-items-center">
            <?php if (empty($election['candidatsN'])): ?>
              <span class="font-weight-bold">Aucun député candidat</span>
              <?php else: ?>
              <span class="font-weight-bold"><?= $election['candidatsN'] ?> députés candidats</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
