<?php
  include('../bdd-connexion.php');

  $bdd->query('ALTER TABLE `elect_libelle` ADD `candidates` BOOLEAN NULL DEFAULT NULL AFTER `dateSecondRound`;');
  $bdd->query('UPDATE `elect_libelle` SET `candidates` = true WHERE `elect_libelle`.`id` = 1');
  $bdd->query('UPDATE `elect_libelle` SET `candidates` = false WHERE `elect_libelle`.`id` = 2');

  $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `secondRound` BOOLEAN NULL AFTER `visible`, ADD `elected` BOOLEAN NULL AFTER `secondRound`');

  $bdd->query('DROP VIEW IF EXISTS `candidate_full`');
  $bdd->query('CREATE VIEW candidate_full AS SELECT
    edc.mpId as mpId, `election`, `district`, `position`, `nuance`, `source`, `visible`, `secondRound`, `elected`,
    `legislature`, `nameUrl`, `civ`, `nameFirst`, `nameLast`, `age`, `dptSlug`, `departementNom`, `departementCode`, `circo`, `mandatId`, dl.`libelle` as "depute_libelle", dl.`libelleAbrev` as "depute_libelleAbrev", `groupeId`, `groupeMandat`, `couleurAssociee`, `dateFin`, `datePriseFonction`, `causeFin`, `img`, `imgOgp`, `dateMaj`, `libelle_1`, `libelle_2`, `active`,
    el.`id` as "election_id", el.`libelle` as "election_libelle", el.`libelleAbrev` as "election_libelleAbrev", `dateYear`, `dateFirstRound`, `dateSecondRound`, edc.`modified_at`
    FROM elect_deputes_candidats edc
    LEFT JOIN deputes_last dl ON edc.mpId = dl.mpId
    LEFT JOIN elect_libelle el ON edc.election = el.id;
  ');
