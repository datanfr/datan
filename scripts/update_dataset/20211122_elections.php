<?php
  // Add a 'reading' field to the votes_datan database

  include('../bdd-connexion.php');

  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `district` `district` INT(5) NULL DEFAULT NULL');
  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `source` `source` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `position` `position` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');

  $bdd->query('DROP TABLE IF EXISTS elect_libelle');

  $bdd->query('CREATE TABLE IF NOT EXISTS `elect_libelle` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `libelle` varchar(255) NOT NULL,
    `libelleAbrev` varchar(255) NOT NULL,
    `dateYear` year(4) NOT NULL,
    `slug` varchar(50) NOT NULL,
    `dateFirstRound` date NOT NULL,
    `dateSecondRound` date DEFAULT NULL,
    `candidates` BOOLEAN NULL DEFAULT NULL,
    `resultsUrl` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8');

  $bdd->query("INSERT INTO `elect_libelle` (`id`, `libelle`, `libelleAbrev`, `dateYear`, `slug`, `dateFirstRound`, `dateSecondRound`,  `candidates`, `resultsUrl`) VALUES
  (1, 'Élections régionales', 'Régionales', 2021, 'regionales-2021', '2021-06-20', '2021-06-27', TRUE, 'https://www.interieur.gouv.fr/Elections/Les-resultats/Regionales/elecresult__regionales-2021/(path)/regionales-2021/index.html'),
  (2, 'Élections départementales', 'Départementales', 2021, 'departementales-2021', '2021-06-20', '2021-06-27', FALSE, 'https://www.interieur.gouv.fr/Elections/Les-resultats/Departementales/elecresult__departementales-2021/(path)/departementales-2021/index.html'),
  (3, 'Élection présidentielle', 'Présidentielle', 2022, 'presidentielle-2022', '2022-04-10', '2022-04-24', TRUE, NULL),
  (4, 'Élections législatives', 'Législatives', 2022, 'legislatives-2022', '2022-06-12', '2022-06-19', FALSE, NULL)
  ");

  $bdd->query('DROP VIEW IF EXISTS `candidate_full`;');

  $bdd->query('CREATE VIEW candidate_full AS SELECT
    edc.mpId as mpId, `election`, `district`, `position`, `nuance`, `source`, `visible`, `secondRound`, `elected`,
    `legislature`, `nameUrl`, `civ`, `nameFirst`, `nameLast`, `age`, `dptSlug`, `departementNom`, `departementCode`, `circo`, `mandatId`, dl.`libelle` as "depute_libelle", dl.`libelleAbrev` as "depute_libelleAbrev", `groupeId`, `groupeMandat`, `couleurAssociee`, `dateFin`, `datePriseFonction`, `causeFin`, `img`, `imgOgp`, `dateMaj`, `libelle_1`, `libelle_2`, `active`,
    el.`id` as "election_id", el.`libelle` as "election_libelle", el.`libelleAbrev` as "election_libelleAbrev", `dateYear`, `dateFirstRound`, `dateSecondRound`, edc.`modified_at`
    FROM elect_deputes_candidats edc
    LEFT JOIN deputes_last dl ON edc.mpId = dl.mpId
    LEFT JOIN elect_libelle el ON edc.election = el.id;');
