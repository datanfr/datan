<?php
  include('../bdd-connexion.php');

  /*

  $bdd->query('ALTER TABLE `deputes_all`
    ADD `job` VARCHAR(255) NULL DEFAULT NULL AFTER `age`,
    ADD `catSocPro` VARCHAR(255) NULL DEFAULT NULL AFTER `job`,
    ADD `famSocPro` VARCHAR(255) NULL DEFAULT NULL AFTER `catSocPro`;
    ');

  $bdd->query('ALTER TABLE `deputes`
    ADD `famSocPro` VARCHAR(255) NULL DEFAULT NULL AFTER `catSocPro`;
    ');

  $bdd->query('DROP TABLE IF EXISTS `famsocpro`');
  $bdd->query('CREATE TABLE IF NOT EXISTS `famsocpro` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `famille` varchar(49) DEFAULT NULL,
    `population` varchar(10) DEFAULT NULL,
    PRIMARY KEY (`id`)
  )');
  $bdd->query('INSERT INTO `famsocpro` (`id`, `famille`, `population`) VALUES
  (1, 'Agriculteurs exploitants', '0.7'),
  (2, 'Artisans, commerçants et chefs d\'entreprise', '3.5'),
  (3, 'Cadres et professions intellectuelles supérieures', '10.6'),
  (4, 'Professions intermédiaires', '13.7'),
  (5, 'Employés', '14.2'),
  (6, 'Ouvriers', '10.9'),
  (7, 'Retraités', '33.4'),
  (8, 'Personnes n’ayant jamais travaillé (ex étudiants)', '12.7')
  '); */
