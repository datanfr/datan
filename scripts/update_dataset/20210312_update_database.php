<?php
include('../bdd-connexion.php');


try {
  $bdd->query('ALTER TABLE `votes_info` CHANGE `sortCode` `sortCode` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $bdd->query('ALTER TABLE `votes_info` CHANGE `titre` `titre` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $bdd->query('ALTER TABLE `votes_info` CHANGE `demandeur` `demandeur` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $bdd->query('ALTER TABLE `votes` ADD INDEX `idx_legislature` (`legislature`);');
  $bdd->query('ALTER TABLE `votes_groupes` CHANGE `positionMajoritaire` `positionMajoritaire` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;');
  $bdd->query('ALTER TABLE `votes_groupes` CHANGE `nonVotants` `nonVotants` INT(10) NULL;');
  $bdd->query('ALTER TABLE `votes_groupes` CHANGE `nonVotantsVolontaires` `nonVotantsVolontaires` INT(10) NULL;');
  $bdd->query('ALTER TABLE `votes_groupes` ADD INDEX `idx_legislature` (`legislature`);');
  $bdd->query('
    DROP TABLE IF EXISTS `votes_scores`;
    CREATE TABLE IF NOT EXISTS `votes_scores` (
      `id` int(5) NOT NULL AUTO_INCREMENT,
      `voteNumero` int(5) NOT NULL,
      `legislature` int(5) NOT NULL,
      `mpId` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
      `vote` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `mandatId` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
      `sortCode` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
      `positionGroup` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `scoreLoyaute` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `scoreGagnant` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `scoreParticipation` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `positionGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `scoreGvt` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
      `dateMaj` date NOT NULL,
      KEY `idx_id` (`id`),
      KEY `idx_loyaute` (`scoreLoyaute`),
      KEY `idx_deputeId_numero` (`mpId`,`voteNumero`),
      KEY `idx_mpId` (`mpId`) USING BTREE,
      KEY `idx_legislature_voteNumero` (`voteNumero`,`legislature`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ');
  $bdd->query('ALTER TABLE `groupes_cohesion` ADD INDEX `idx_legislature_voteNumero` (`voteNumero`, `legislature`);');
  $bdd->query('ALTER TABLE `datan`.`groupes_accord` ADD INDEX `idx_legislature` (`legislature`);');
  $bdd->query('ALTER TABLE `deputes_accord` ADD INDEX `idx_legislature` (`legislature`);');
  $bdd->query('ALTER TABLE `votes_participation` ADD INDEX `idx_legislature` (`legislature`);');
  $bdd->query('
    DROP TABLE IF EXISTS `votes_dossiers`;
    CREATE TABLE IF NOT EXISTS `votes_dossiers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `offset_num` int(11) NOT NULL,
      `legislature` tinyint(4) NOT NULL,
      `voteNumero` int(11) NOT NULL,
      `href` text,
      `dossier` varchar(300) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_voteNumero` (`voteNumero`) USING BTREE,
      KEY `idx_legislature` (`legislature`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
  ');
  $bdd->query('ALTER TABLE `dossiers` ADD INDEX `idx_legislature` (`legislature`);');




} catch (Exception $e) {
    echo '<pre>', var_dump($e->getMessage()), '</pre>';
}
