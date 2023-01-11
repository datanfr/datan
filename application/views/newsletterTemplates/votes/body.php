<mj-section background-color="#ffffff" background-repeat="repeat" background-size="auto">
  <mj-column vertical-align="top">
    <mj-text>
      <span class="title">Les derniers votes de l'Assemblée nationale</span>
    </mj-text>
    <mj-text padding-top="0">
      <span class="subtitle"><?= ucfirst($month) ?> <?= $year ?></span>
    </mj-text>
    <mj-text padding-top="40">
      Il y a eu <b><?= $votesN ?> votes</b> à l'Assemblée nationale en <?= $month.' '.$year ?>. <?= $votesInfosEdited ?>
    </mj-text>
    <mj-text padding-bottom="40px">
      <b>L'équipe de Datan a sélectionné et <span class="primary">décrypté <?= $votesNDatan ?> vote<?= $votesNDatan > 1 ? "s" : "" ?></span></b>.
      <?php if ($importants): ?>
        Découvre dans cette newsletter les votes les plus importants !
        <?php else: ?>
        Découvre-le<?= $votesNDatan > 1 ? "s" : "" ?> ci-dessous !
      <?php endif; ?>
    </mj-text>
  </mj-column>
</mj-section>

<?php foreach ($votes as $vote): ?>
  <mj-section padding-top="0" background-color="#ffffff" background-repeat="repeat" background-size="auto">
    <mj-column vertical-align="top">
      <mj-divider padding-bottom="40px" border-width="5px" border-style="solid" border-color="lightgrey" />
      <mj-text font-size="22px" font-weight="800" line-height="1.5" padding-bottom="0">
        <?= $vote['voteTitre'] ?>
      </mj-text>
      <mj-text font-size="14px" font-weight="400" color="#aaa">
        <?= months_abbrev($vote['dateScrutinFR']) ?>
      </mj-text>
      <mj-text font-size="14px">
        <span class="badge badge-<?= $vote['sortCode'] ?>"><b><?= mb_strtoupper($vote['sortCode']) ?></b></span>
      </mj-text>
      <mj-text >
        Ce vote a été <?= $vote['sortCode'] ?>. Sur les <?= $vote['nombreVotants'] ?> parlementaires ayant pris part au vote, <?= $vote['decomptePour'] ?> ont voté pour et <?= $vote['decompteContre'] ?> ont voté contre.
      </mj-text>
      <mj-text font-size="16px" font-weight="800" padding-bottom="0">
        <span class="primary">Les groupes ayant voté pour</span>
      </mj-text>
      <mj-section padding-top="0" padding-bottom="0">
        <mj-group>
          <?php foreach ($vote['groupes'] as $group): ?>
            <?php if ($group['positionMajoritaire'] == 'pour'): ?>
              <mj-column width="20%">
                <mj-image padding="5px 10px" href="https://datan.fr/groupes/legislature-<?= $group['legislature'] ?>/<?= mb_strtolower($group['libelleAbrev']) ?>" src="https://datan.fr/assets/imgs/groupes/<?= $group['legislature'] ?>/<?= $group['libelleAbrev'] ?>.png" />
              </mj-column>
            <?php endif; ?>
          <?php endforeach; ?>
        </mj-group>
      </mj-section>
      <?php if ($vote['description'] !== ""): ?>
        <mj-text font-size="16px" font-weight="800" padding-bottom="0" padding-top="20px">
          <span class="primary">Le contexte du vote</span>
        </mj-text>
        <mj-text padding-top="0">
          <?= word_limiter($vote['description'], 30) ?>
          <a href="https://datan.fr/votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" target="_blank">Lire plus</a>
        </mj-text>
      <?php endif; ?>

      <mj-button padding-top="40px" padding-bottom="30px" href="https://datan.fr/votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>">
        Découvre la position de ton député
      </mj-button>
    </mj-column>
  </mj-section>
<?php endforeach; ?>

<mj-section padding="10px 0"></mj-section>

<?php if (isset($depute)): ?>
  <mj-section background-color="#ffffff" background-repeat="no-repeat" background-size="auto"  border="2px solid #00b794">
    <mj-column width="100%">
      <mj-text font-size="22px" font-weight="800">
        <span class="primary"><?= ucfirst($depute['gender']['le']) ?> <?= $depute['gender']['depute'] ?> qui a le plus participé</span>
      </mj-text>
    </mj-column>
    <mj-column width="30%" vertical-align="middle">
      <mj-image width="200px"  border-radius="5px" src="https://datan.fr/assets/imgs/deputes_original/depute_<?= substr($depute['mpId'], 2) ?>.png" href="https://www.datan.fr/deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>" />
    </mj-column>
    <mj-column width="70%" vertical-align="middle">
      <mj-text>
        <a href="https://www.datan.fr/deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>" target="_blank"><?= $depute['nameFirst'].' '.$depute['nameLast'] ?></a> est <?= $depute['gender']['le'] ?> <?= $depute['gender']['depute'] ?> qui a le plus participé aux scrutins en <?= $month.' '.$year ?>.
        Avec un <b>taux de participation de <?= round($depute['score']) ?> %</b>, <?= $depute['gender']['pronom'] ?> a participé au total à <?= $depute['votesN'] ?> votes dans l'hémicycle.
      </mj-text>
    </mj-column>
    <mj-column width="100%">
      <mj-button background-color="#00b794" color="#fff" font-weight="800" padding-top="20px" padding-bottom="10px" href="https://www.datan.fr/deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>">
        Découvre son activité
      </mj-button>
    </mj-column>
  </mj-section>

  <mj-section padding="10px 0"></mj-section>
<?php endif; ?>

<mj-section background-color="#ffffff" background-repeat="no-repeat" background-size="auto">
  <mj-column vertical-align="top">
    <mj-text font-size="22px" font-weight="800">
      <span class="primary">Reste informé !</span>
    </mj-text>
    <mj-text>
      Tu souhaites rester informé de nos dernières infos ? Tu veux découvrir en temps réel le vote des groupes politiques à l'Assemblée nationale ? <b>Suis-nous sur les réseaux sociaux !</b>
    </mj-text>
    <mj-section padding-top="15px" padding-bottom="15px">
      <mj-group padding="0">
        <mj-column padding="10px" background-color="#3b5998" border-radius="5px" width="40%">
          <mj-image href="https://www.facebook.com/datanFR/" width="70%" src="https://datan.fr/assets/imgs/logos/facebook_datan.png" />
        </mj-column>
        <mj-column padding="10px" background-color="#FFFFFF" width="20%"></mj-column>
        <mj-column padding="10px" background-color="#00acee" border-radius="5px" width="40%">
          <mj-image href="https://twitter.com/datanfr?lang=fr" width="70%" src="https://datan.fr/assets/imgs/logos/twitter_datan.png" />
        </mj-column>
      </mj-group>
    </mj-section>
  </mj-column>
</mj-section>

<mj-section padding="10px 0"></mj-section>

<mj-section background-color="#ffffff" background-repeat="no-repeat" background-size="auto">
  <mj-column vertical-align="top">
    <mj-text font-size="22px" font-weight="800">
      <span class="primary">Contribue au projet 👨‍💻 🔨</span>
    </mj-text>
    <mj-text>
      Datan est géré par <b>une équipe de bénévoles</b>. Notre objectif ? Rendre l'activité parlementaire plus accessible et compréhensible !
    </mj-text>
    <mj-text>
      Tu as un don pour coder ou pour le design ? Tu es un mordu de politique et souhaite expliquer certains votes de l'Assemblée ?
    </mj-text>
    <mj-text>
      Contacte-nous : <a href="mailto:info@datan.fr">info@datan.fr</a>
    </mj-text>
  </mj-column>
</mj-section>
