<?php
  include('../bdd-connexion.php');

    $bdd->query('ALTER TABLE `deputes` ADD PRIMARY KEY(`mpId`);
    ALTER TABLE `mandat_groupe` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `mandat_principal` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `mandat_secondaire` ADD PRIMARY KEY(`mandatId`);
    ALTER TABLE `deputes_last` DROP `id`;
    ALTER TABLE `deputes_last` ADD PRIMARY KEY(`mpId`);
    ALTER TABLE `legislature` DROP `id`;
    ALTER TABLE `legislature` ADD PRIMARY KEY(`legislatureNumber`);
  ');
