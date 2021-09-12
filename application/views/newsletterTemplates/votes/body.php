<mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto" text-align="center" vertical-align="top">
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
    <mj-text padding-bottom="40px">
      <b>L'équipe de Datan a sélectionné et <span class="primary">décrypté <?= $votesNDatan ?> votes</span> pour vous</b>. Découvrez-les ci-dessous !
    </mj-text>
  </mj-column>
</mj-section>

<?php foreach ($votes as $vote): ?>
  <mj-section padding-top="0px" background-color="#ffffff" background-repeat="repeat" background-size="auto">
    <mj-column vertical-align="top">
      <mj-divider padding-bottom="40px" border-width="5px" border-style="solid" border-color="#00b794" /> <!-- lightgrey -->
      <mj-text font-size="22px" font-weight="800" line-height="1.5" padding-bottom="0px">
        <?= $vote['voteTitre'] ?>
      </mj-text>
      <mj-text font-size="14px" font-weight="400" color="#aaa">
        <?= $vote['dateScrutinFR'] ?>
      </mj-text>
      <mj-text font-size="14px">
        <span class="badge badge-<?= $vote['sortCode'] ?>"><b><?= mb_strtoupper($vote['sortCode']) ?></b></span>
      </mj-text>
      <mj-text >
        Ce vote a été <?= $vote['sortCode'] ?>. Sur les <?= $vote['nombreVotants'] ?> parlementaires ayant pris part au vote, <?= $vote['decomptePour'] ?> ont voté pour et <?= $vote['decompteContre'] ?> ont voté contre.
      </mj-text>
      <mj-text font-size="16px" font-weight="800" padding-bottom="0px">
        <span class="primary">Les groupes ayant voté pour</span>
      </mj-text>
      <mj-section padding-top="0" padding-bottom="0">
        <mj-group>
          <?php foreach ($vote['groupes'] as $group): ?>
            <?php if ($group['positionMajoritaire'] == 'pour'): ?>
              <mj-column width="20%">

                <mj-image padding="5px 10px" href="https://datan.fr/groupes/<?= mb_strtolower($group['libelleAbrev']) ?>" src="https://datan.fr/assets/imgs/groupes/<?= $group['libelleAbrev'] ?>.png" />
              </mj-column>
            <?php endif; ?>
          <?php endforeach; ?>
        </mj-group>
      </mj-section>
      <mj-text font-size="16px" font-weight="800" padding-bottom="0px" padding-top="20px">
        <span class="primary">Le contexte du vote</span>
      </mj-text>
      <mj-text padding-top="0px">
        <?= word_limiter($vote['description'], 30) ?>
        <a href="https://datan.fr/votes/legislature-15/vote_<?= $vote['voteNumero'] ?>" target="_blank">Lire plus</a>
      </mj-text>
      <mj-button padding-top="40px" padding-bottom="30px" href="https://datan.fr/votes/legislature-15/vote_<?= $vote['voteNumero'] ?>">
        Découvrez la position de son député
      </mj-button>
    </mj-column>
  </mj-section>
<?php endforeach; ?>

<mj-section background-color="#ffffff" background-repeat="no-repeat" background-size="auto" text-align="center" vertical-align="top" border-top = "20px solid #F4F4F4">
  <mj-column vertical-align="top">
    <mj-text>
      <span><b>Contribuer au projet ? 👨‍💻 🔨</b></span>
    </mj-text>
    <mj-text padding-top="0px">
      Datan est géré par une équipe de bénévoles. Notre objectif ? Rendre l'activité parlementaire plus accessible et compréhensible !
    </mj-text>
    <mj-text>
      Vous savez coder ? Vous avez un don pour le design et la création de visuels ? Vous êtes un mordu de politique et souhaitez expliquer certains votes de l'Assemblée ? Contactez-nous : <a href="mailto:info@datan.fr">info@datan.fr</a>
    </mj-text>
  </mj-column>
</mj-section>
