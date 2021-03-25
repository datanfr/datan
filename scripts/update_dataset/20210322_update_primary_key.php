<?php
  include('../bdd-connexion.php');

    $bdd->query('ALTER TABLE `deputes` ADD PRIMARY KEY(`mpId`);
    ALTER TABLE `mandat_groupe` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `mandat_principal` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `mandat_secondaire` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `organes` ADD PRIMARY KEY(`uid`);
    ALTER TABLE `deputes_last` DROP `id`;
    ALTER TABLE `deputes_last` ADD PRIMARY KEY(`mpId`);
    ALTER TABLE `deputes_all` DROP `id`;
    ALTER TABLE `deputes_all` ADD PRIMARY KEY(`mpId`, `legislature`);
    ALTER TABLE `legislature` DROP `id`;
    ALTER TABLE `legislature` ADD PRIMARY KEY(`legislatureNumber`);
  ');

  // New indexes
  $bdd->query('ALTER TABLE `votes` ADD PRIMARY KEY(`legislature`, `voteNumero`, `mpId`, `voteType`);
  ALTER TABLE `votes_info` DROP `id`;
  ALTER TABLE `votes_info` ADD PRIMARY KEY(`legislature`, `voteNumero`);
  ALTER TABLE `votes_groupes` DROP `id`;
  ALTER TABLE `votes_groupes` ADD PRIMARY KEY(`legislature`, `voteNumero`, `organeRef`);
  ALTER TABLE `votes_scores` DROP `id`;
  ALTER TABLE `votes_scores` ADD PRIMARY KEY(`legislature`, `voteNumero`, `mpId`);
  ALTER TABLE `votes_participation` DROP `id`;
  ALTER TABLE `votes_participation` ADD PRIMARY KEY(`legislature`, `voteNumero`, `mpId`);
  ALTER TABLE `votes_participation_commission` DROP `id`;
  ALTER TABLE `votes_participation_commission` ADD PRIMARY KEY(`legislature`, `voteNumero`, `mpId`); // NOT WORKING
  ALTER TABLE `groupes_accord` DROP `id`;
  ALTER TABLE `groupes_accord` ADD PRIMARY KEY(`legislature`, `voteNumero`, `organeRef`, `organeRefAccord`);
  ALTER TABLE `groupes_cohesion` DROP `id`;
  ALTER TABLE `groupes_cohesion` ADD PRIMARY KEY(`legislature`, `voteNumero`, `organeRef`);
  ALTER TABLE `deputes_accord` DROP `uid`;
  ALTER TABLE `deputes_accord` ADD PRIMARY KEY(`legislature`, `voteNumero`, `mpId`, `organeRef`);
  ');
