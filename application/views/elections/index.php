<div class="container pg-stats-index mb-5">
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
    <?php foreach ($elections as $election): ?>
      <div class="col-lg-6 col-md-6 py-3">
        <div class="card card-groupe">
          <div class="liseret"></div>
          <div class="card-body d-flex flex-column justify-content-center align-items-center">
            <h2 class="d-block card-title">
              <a href="<?= base_url(); ?>elections/<?= mb_strtolower($election['slug']) ?>" class="stretched-link no-decoration"><?= $election['libelle'] ?></a>
            </h2>
          </div>
          <div class="card-footer d-flex justify-content-center align-items-center">
            <span>X députés candidats</span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
