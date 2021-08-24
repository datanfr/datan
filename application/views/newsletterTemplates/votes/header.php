<mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top">
  <mj-column vertical-align="top">
    <mj-text>
      <span class="title"><b>Les derniers votes de l'Assemblée nationale</b></span>
    </mj-text>
    <mj-text padding-top="0px">
      <span class="subtitle"><?= ucfirst($month) ?> <?= $year ?></span>
    </mj-text>
    <mj-text padding-top="40px">
      <span class="para">Il y a eu <b><?= $votesN ?> votes</b> à l'Assemblée nationale en <?= $month.' '.$year ?>. <?= $votesInfosEdited ?></span>
    </mj-text>
    <mj-text>
      <span class="para">L'équipe de Datan a sélectionné et décrypté <?= $votesNDatan ?> votes. Retrouvez-les ci-dessous !</span>
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
             font-size="20px"
             font-family="Helvetica Neue"
             color="#00b794">
        Les votes décryptés par Datan
      </mj-text>

      <mj-text color="#525252">
        <span class="para">
          Connaissez-vous le contenu de "L'article 1er de la proposition de loi visant à renforcer le droit à l'avortement" ? Non ? Pas de problème, <b>Datan</b> décrypte pour vous les votes importants qui se déroulent à l'Assemblée nationale.
        </span>
      </mj-text>
  </mj-column>
</mj-section>

<?php foreach ($votes as $vote): ?>

  <mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" padding="20px 15px" text-align="center" vertical-align="top">
    <mj-column vertical-align="top">
      <mj-divider border-width="1px" border-style="dashed" border-color="lightgrey" />
      <mj-text>
        <span><b><?= $vote['voteTitre'] ?></b></span>
      </mj-text>
      <mj-text padding-top="0px">
        <span><?= $vote['dateScrutinFR'] ?> -- <?= $vote['category_libelle'] ?> -- <?= $vote['sortCode'] ?> -- vote n° <?= $vote['voteNumero'] ?></span>
      </mj-text>
      <mj-text padding-top="40px">
        <span class="para">XXXX</span>
      </mj-text>
    </mj-column>
  </mj-section>
<?php endforeach; ?>
