<mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top">
  <mj-column vertical-align="top">
    <mj-text>
      <span class="title">Les derniers votes de l'Assemblée nationale</span>
    </mj-text>
    <mj-text padding-top="0px">
      <span class="subtitle"><?= ucfirst($month) ?> <?= $year ?></span>
    </mj-text>
    <mj-text padding-top="40px">
      Il y a eu <b><?= $votesN ?> votes</b> à l'Assemblée nationale en <?= $month.' '.$year ?>. <?= $votesInfosEdited ?>
    </mj-text>
    <mj-text>
      L'équipe de Datan a sélectionné et décrypté <?= $votesNDatan ?> votes. Retrouvez-les ci-dessous !
    </mj-text>
  </mj-column>
</mj-section>

<mj-section background-color="#ffffff">
  <!-- Left image -->
  <mj-column>
    <mj-image width="300px"
              src="https://www.assemblee-nationale.fr/12/dossiers/images/main_vote-p.jpg" />
  </mj-column>

  <!-- right paragraph -->
  <mj-column>
    <mj-text font-style="italic"
             font-size="16px"
             color="#00b794">
        Les votes décryptés par Datan
      </mj-text>
      <mj-text color="#525252">
          Connaissez-vous le contenu du vote intitulé « L'article 1er de la proposition de loi visant à renforcer le droit à l'avortement » ? Non ? Pas de problème, <b>Datan</b> décrypte pour vous les votes importants qui se déroulent à l'Assemblée nationale.
        </span>
      </mj-text>
  </mj-column>
</mj-section>

<?php foreach ($votes as $vote): ?>
  <mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top">
    <mj-column vertical-align="top">
      <mj-divider border-width="1px" border-style="dashed" border-color="lightgrey" />
      <mj-text font-size="17px">
        <span class="text-black"><b><?= $vote['voteTitre'] ?></b></span>
      </mj-text>
      <mj-text padding-top="0px" font-size="13px">
        <span class="text-grey"><?= $vote['dateScrutinFR'] ?> - <?= $vote['category_libelle'] ?></span>
      </mj-text>
      <mj-text padding-top="0px" font-size="14px">
        <span class="badge badge-<?= $vote['sortCode'] ?>"><b><?= mb_strtoupper($vote['sortCode']) ?></b></span>
      </mj-text>
      <mj-text font-size="16px" font-weight="800" padding-bottom="0px">
        <span class="primary">Résultat du vote</span>
      </mj-text>
      <mj-text padding-top="0px">
        Ce vote a été <?= $vote['sortCode'] ?> par les députés de l'Assemblée nationale. Sur les <?= $vote['nombreVotants'] ?> parlementaires ayant pris part au vote, <?= $vote['decomptePour'] ?> ont voté pour et <?= $vote['decompteContre'] ?> ont voté contre.
      </mj-text>
      <mj-text font-size="16px" font-weight="800" padding-bottom="0px">
        <span class="primary">Les groupes ayant voté pour</span>
      </mj-text>
      <mj-image width="100%" src="<?= asset_url() ?>imgs/groupes/LAREM.png" />
      <mj-text font-size="16px" font-weight="800" padding-bottom="0px">
        <span class="primary">Le contexte du vote</span>
      </mj-text>
      <mj-text padding-top="0px">
        <?= word_limiter($vote['description'], 35) ?>
        <a href="https://datan.fr/votes/legislature-15/vote_<?= $vote['voteNumero'] ?>" target="_blank">Lire plus</a>
      </mj-text>
      <mj-button href="https://datan.fr/votes/legislature-15/vote_<?= $vote['voteNumero'] ?>">
        Découvrez la position de son député
      </mj-button>
    </mj-column>
  </mj-section>
<?php endforeach; ?>

<mj-section background-color="#ffffff" background-repeat="no-repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top" border-top = "20px solid #F4F4F4">
  <mj-column vertical-align="top">
    <mj-text>
      <span><b>Section : travailler avec nous & nous suivre</b></span>
    </mj-text>
    <mj-text padding-top="0px">
      <span>AAA</span>
    </mj-text>
    <mj-text padding-top="40px">
      XXXX
    </mj-text>
  </mj-column>
</mj-section>
