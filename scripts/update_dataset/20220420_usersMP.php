<?php
  include('../bdd-connexion.php');

  // Create table usersMP
  try {
    $bdd->query("SELECT 1 FROM usersMP LIMIT 1");
  } catch (Exception $e) {
    // We got an exception (table not found)
    //return FALSE;
    $bdd->query("CREATE TABLE `usersMP` (
      `user` INT(11) NOT NULL ,
      `mpId` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      INDEX `user_idx` (`user`)
    ) ENGINE = MyISAM;");
  }

  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `district` `district` VARCHAR(5) NULL DEFAULT NULL;');

  try {
    $bdd->query("SELECT candidature FROM elect_deputes_candidats LIMIT 1");
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `candidature` INT(5) NULL DEFAULT 1 AFTER `election`;');
  }

  $bdd->query('ALTER TABLE users ADD UNIQUE (username);');

  $bdd->query('DROP VIEW IF EXISTS `candidate_full`;');

  $bdd->query('CREATE VIEW candidate_full AS SELECT
    edc.mpId as mpId, `election`, `district`, `candidature`, `position`, `nuance`, `source`, `visible`, `secondRound`, `elected`,
    `legislature`, `nameUrl`, `civ`, `nameFirst`, `nameLast`, `age`, `dptSlug`, `departementNom`, `departementCode`, `circo`, `mandatId`, dl.`libelle` as "depute_libelle", dl.`libelleAbrev` as "depute_libelleAbrev", `groupeId`, `groupeMandat`, `couleurAssociee`, `dateFin`, `datePriseFonction`, `causeFin`, `img`, `imgOgp`, `dateMaj`, `libelle_1`, `libelle_2`, `active`,
    el.`id` as "election_id", el.`libelle` as "election_libelle", el.`libelleAbrev` as "election_libelleAbrev", `dateYear`, `dateFirstRound`, `dateSecondRound`, edc.`modified_at`
    FROM elect_deputes_candidats edc
    LEFT JOIN deputes_last dl ON edc.mpId = dl.mpId
    LEFT JOIN elect_libelle el ON edc.election = el.id;');
