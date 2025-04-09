<div class="card card-statistiques my-4">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex flex-row align-items-center">
        <div class="icon">
          <?= file_get_contents(base_url() . '/assets/imgs/icons/voting.svg') ?>
        </div>
        <h3 class="ml-3 text-uppercase">Participation aux votes
          <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" aria-label="Tooltip participation" class="no-decoration popover_focus" title="Taux de participation" data-content="Le taux de participation est le <b>pourcentage de votes auxquels le ou la député a participé</b>.<br><br>Attention, le taux de participation ne mesure pas toute l'activité d'un député ou d'un groupe. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='lien'>Parlement européen</a>, les votes à l'Assemblée nationale se déroulent à n'importe quel moment de la semaine. D'autres réunions ont souvent lieu en même temps, expliquant le faible taux de participation des députés et des groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#participation' target='_blank'>cliquez ici</a>."><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
        </h3>
      </div>
    </div>
    <div class="row">
      <?php if ($no_participation) : ?>
        <div class="col-12 mt-2">
          <p>Il n'y a pas encore eu suffisamment de votes solennels à l'Assemblée nationale pour afficher cette statistique. Pour consulter la participation des députés à l'ensemble des scrutins, <a href="<?= base_url() ?>statistiques/deputes-participation">cliquez ici</a>.</p>
        </div>
      <?php else : ?>
        <div class="col-lg-3 offset-lg-1 mt-2">
          <div class="d-flex justify-content-center align-items-center">
            <div class="c100 p<?= $participation['score'] ?> m-0">
              <span><?= $participation['score'] ?> %</span>
              <div class="slice">
                <div class="bar"></div>
                <div class="fill"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-8 infos mt-4 mt-lg-2">
          <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
            <?php if ($depute['legislature'] == legislature_current()): ?>
              <!-- Paragraph for MP from the current legislature -->
              <p>
                <?php if ($active) : ?>
                  Depuis sa prise de fonctions,
                <?php else : ?>
                  Quand <?= $gender['pronom'] ?> était en activité à l'Assemblée,
                <?php endif; ?>
                <?= $title ?> a participé à <?= $participation['score'] ?>% des votes solennels à l'Assemblée nationale.
              </p>
              <p>
                <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui est <?= $edito_participation['all'] == "autant" ? "également" : "" ?> de <?= $participation['all'] ?>%.
              </p>
              <?php if ($participation['group']): ?>
                <p>
                  De plus, <?= $title ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['group'] ?></b> que la moyenne des députés de son groupe politique, qui est <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $participation['group'] ?>%.
                </p>
              <?php endif; ?>
            <?php else: ?>
              <!-- Paragraph for MP from older legislatures -->
              <p>
                Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a participé à <?= $participation['score'] ?>% des votes solennels à l'Assemblée nationale.
              </p>
              <p>
                <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui était <?= $edito_participation['all'] == "autant" ? "également" : "" ?> de <?= $participation['all'] ?>%.
              </p>
              <?php if ($participation['group']): ?>
                <p>
                  De plus, <?= $title ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $participation['group'] ?>%.
                </p>
              <?php endif; ?>
            <?php endif; ?>
            <p>
              Les votes solennels sont les votes considérés comme importants pour lesquels les députés connaissent à l'avance le jour et l'heure du vote.
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>