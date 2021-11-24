<div class="container pg-elections-index mb-5">
  <div class="row bloc-titre">
    <div class="col-md-12">
      <h1><?= $title ?></h1>
    </div>
    <div class="col-md-8 col-lg-7 mt-4">
      <p>
        L'élection politique est un <b>outil central de notre vie démocratique</b>. Elle permet aux citoyens de choisir ses représentants et ses gouvernants, qui auront pour rôle de rédiger et voter la loi.
      </p>
      <p>
        Il existe, en France, <a href="https://www.diplomatie.gouv.fr/fr/services-aux-francais/voter-a-l-etranger/les-differentes-elections/" target="_blank" rel="nofollow noreferrer noopener">plusieurs élections</a>. La plus importante est <b>l'élection présidentielle</b>. Se tenant tous les cinq ans, elle permet d'élire le Président de la République. Les <b>élections législatives</b>, se tenant quelques mois après la présidentielle, permettent d'élire les <a href="<?= base_url() ?>deputes">577 députés de l'Assemblée nationale</a>.
      </p>
      <p>
        Les autres élections en France sont les sénatoriales, les européennes, les régionales, les départementales et les municipales.
      </p>
        Découvrez sur Datan les députés ou anciens députés qui se sont portés candidats à des élections politiques.
      </p>
      <p>
        Être député et candidat à une autre élection peut être critiquable. Pendant la campagne, le député est moins présent à l'Assemblée nationale et ne s'engage pas de la même manière dans le travail parlementaire.
      </p>
      <p>
        Il existe en France des <a href="https://www.interieur.gouv.fr/Elections/Les-elections-en-France/Le-cumul-des-mandats-electoraux" target="_blank" rel="nofollow noreferrer noopener">règles interdisant le non-cumul des mandats</a>. Par exemple, on ne peut pas être député et en même temps sénateur ou député européen. Par contre, si le député est élu au Conseil régional ou départemental, il pourra garder son mandat de député, sauf s'il en est président ou vice-président.
      </p>
    </div>
    <div class="col-md-4 d-none d-lg-flex align-items-center mt-4">
      <div class="px-4">
        <?= file_get_contents(asset_url()."imgs/svg/undraw_election_day_datan.svg") ?>
      </div>
    </div>
  </div>
  <div class="row my-5">
    <div class="col-12">
      <h2 class="my-4">Découvrez les députés candidats aux élections</h2>
    </div>
    <div class="col-12 d-flex flex-wrap">
      <?php foreach ($elections as $election): ?>
        <div class="card card-election">
          <div class="liseret" style="background-color: <?= $electionsColor[$election['libelleAbrev']] ?>"></div>
          <div class="card-body d-flex flex-column justify-content-center align-items-center">
            <h2 class="d-block card-title">
              <a href="<?= base_url(); ?>elections/<?= mb_strtolower($election['slug']) ?>" class="stretched-link no-decoration"><?= $election['libelleAbrev'] ?><br><?= $election['dateYear'] ?></a>
            </h2>
            <span class="mt-3">1<sup>er</sup> tour : <?= $election['dateFirstRoundFr'] ?></span>
            <span>2<sup>nd</sup> tour : <?= $election['dateSecondRoundFr'] ?></span>
          </div>
          <?php if ($election['candidates']): ?>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <?php if (empty($election['candidatsN'])): ?>
                <span class="font-weight-bold">Aucun député candidat</span>
                <?php else: ?>
                <span class="font-weight-bold"><?= $election['candidatsN'] ?> député<?= $election['candidatsN'] > 1 ? "s" : "" ?> candidat<?= $election['candidatsN'] > 1 ? "s" : "" ?></span>
              <?php endif; ?>
            </div>
            <?php else: ?>
              <div class="card-footer bg-transparent"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
