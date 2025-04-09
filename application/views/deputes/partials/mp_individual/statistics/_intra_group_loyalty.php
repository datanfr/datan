<div class="card card-statistiques my-4">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex flex-row align-items-center">
        <div class="icon">
          <?= file_get_contents(base_url() . 'assets/imgs/icons/loyalty.svg') ?>
        </div>
        <h3 class="ml-3 text-uppercase">Proximité avec son groupe
          <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip loyauté" title="Proximité ou loyauté envers le groupe politique" data-content="Le taux de proximité est le <b>pourcentage de votes où le ou la député a voté sur la même ligne que son groupe</b>.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les taux de loyauté des autres parlementaires.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#loyalty' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
        </h3>
      </div>
    </div>
    <div class="row">
      <?php if ($no_loyaute) : ?>
        <div class="col-12 mt-2">
          <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
        </div>
      <?php else : ?>
        <div class="col-lg-3 offset-lg-1 mt-2">
          <div class="d-flex justify-content-center align-items-center">
            <div class="c100 p<?= $loyaute['score'] ?> m-0">
              <span><?= $loyaute['score'] ?> %</span>
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
              <p>
                <?php if (!$active) : ?>Quand <?= $gender['pronom'] ?> était en activité, <?php endif; ?><?= $title ?> a voté sur la même ligne que son groupe politique dans <?= $loyaute['score'] ?>% des cas.
              </p>
              <p>
                <?= ucfirst($gender['pronom']) ?> <?= $active ? "a" : "avait" ?> un taux de proximité avec son groupe <b><?= $edito_loyaute['all'] ?></b> que la moyenne des députés, qui est <?= $edito_loyaute['all'] == "aussi élevé" ? "également" : "" ?> de <?= $loyaute['all'] ?>%.
              </p>
              <?php if ($loyaute['group']): ?>
                <p>
                  De plus, son taux de proximité <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe, qui est de <?= $loyaute['group'] ?>%.
                </p>
              <?php endif; ?>
            <?php else: ?>
              <p>
                Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a voté sur la même ligne que son groupe politique dans <?= $loyaute['score'] ?>% des cas.
              </p>
              <p>
                <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['all'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui était <?= $edito_loyaute['all'] == "aussi loyal" ? "également" : "" ?> de <?= $loyaute['all'] ?>.
              </p>
              <?php if ($loyaute['group']): ?>
                <p>
                  De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $loyaute['group'] ?>%.
                </p>
              <?php endif; ?>
            <?php endif; ?>
            <?php if (isset($loyaute_history)) : ?>
              <p>
                Ce score ne prend en compte que le dernier groupe politique auquel <?= $title ?> a appartenu. Pour voir les taux de loyauté <?= $gender['du'] ?> <?= $gender['depute'] ?> avec ses précédents groupes, <a data-toggle="collapse" href="#collapseLoyaute" role="button" aria-expanded="false" aria-controls="collapseLoyaute">cliquez ici</a>.
              </p>
              <div class="collapse my-4" id="collapseLoyaute">
                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">Groupe</th>
                      <th scope="col" class="text-center">Loyauté</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($loyaute_history as $y) : ?>
                      <tr>
                        <td>
                          <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($y['libelleAbrev']) ?>" class="no-decoration underline"><?= name_group($y['libelle']) ?></a>
                        </td>
                        <td class="text-center"><?= $y['score'] ?>%</td>
                      </tr>
                    <?php endforeach; ?>
                    <?php $i = 1; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div> <!-- END CARD LOYAUTE -->