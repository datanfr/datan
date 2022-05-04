<?php
  include('../bdd-connexion.php');

  // Create table usersMP
  try {
    $bdd->query('SELECT 1 FROM users_mp LIMIT 1');
    echo "table users_mp already exists<br>";
  } catch (Exception $e) {
    $bdd->query("CREATE TABLE `users_mp` (
      `user` INT(11) NOT NULL ,
      `mpId` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      INDEX `user_idx` (`user`)
    ) ENGINE = MyISAM;");
  }

  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `district` `district` VARCHAR(5) NULL DEFAULT NULL;');

  try {
    $bdd->query("SELECT candidature FROM elect_deputes_candidats LIMIT 1");
    echo "field candidature already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `candidature` INT(5) NULL DEFAULT 1 AFTER `election`;');
  }

  $bdd->query('ALTER TABLE users ADD UNIQUE (username);');

  $bdd->query('DROP VIEW IF EXISTS `candidate_full`;');

  $bdd->query('CREATE VIEW candidate_full AS SELECT
    edc.mpId as mpId, `election`, `district`, `candidature`, `position`, `nuance`, `source`, `link`, `visible`, `secondRound`, `elected`,
    `legislature`, `nameUrl`, `civ`, `nameFirst`, `nameLast`, `age`, `dptSlug`, `departementNom`, `departementCode`, `circo`, `mandatId`, dl.`libelle` as "depute_libelle", dl.`libelleAbrev` as "depute_libelleAbrev", `groupeId`, `groupeMandat`, `couleurAssociee`, `dateFin`, `datePriseFonction`, `causeFin`, `img`, `imgOgp`, `dateMaj`, `libelle_1`, `libelle_2`, `active`,
    el.`id` as "election_id", el.`libelle` as "election_libelle", el.`libelleAbrev` as "election_libelleAbrev", `dateYear`, `dateFirstRound`, `dateSecondRound`, edc.`modified_at`
    FROM elect_deputes_candidats edc
    LEFT JOIN deputes_last dl ON edc.mpId = dl.mpId
    LEFT JOIN elect_libelle el ON edc.election = el.id;');

  try {
    $bdd->query('SELECT 1 FROM table_history LIMIT 1');
    echo "table table_changes already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('CREATE TABLE `table_history` (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `table` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `col` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `value_old` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `value_new` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `user` INT NOT NULL ,
      `modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
      PRIMARY KEY (`id`)) ENGINE = MyISAM;'
    );
  }

  $bdd->query('UPDATE `elect_libelle` SET `candidates` = 1 WHERE `elect_libelle`.`id` = 4');

  try {
    $bdd->query('SELECT link FROM elect_deputes_candidats LIMIT 1');
    echo "column link already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `link` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `elected`;');
  }

  try {
    $bdd->query('SELECT 1 FROM users_mp_link LIMIT 1');
    echo "table users_mp_link already exists <br>";
  } catch (\Exception $e) {
    $bdd->query('CREATE TABLE `users_mp_link` (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `mpId` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `token` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
      PRIMARY KEY (`id`), UNIQUE `token` (`token`)) ENGINE = MyISAM;');
  }