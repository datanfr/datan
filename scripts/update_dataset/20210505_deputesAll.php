<?php
  include('../bdd-connexion.php');

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
    `famille` varchar(255) DEFAULT NULL,
    `population` varchar(4) DEFAULT NULL
  )');
  $bdd->query('INSERT INTO `famsocpro` (`famille`, `population`) VALUES
  ('Agriculteurs exploitants', '1,4'),
  ('Artisans, commerçants, chefs d\'entreprise', '6,8'),
  ('Cadres et professions intellectuelles supérieures', '20,4'),
  ('Professions intermédiaires', '26,0'),
  ('Employés', '25,8'),
  ('Ouvriers', '19,2'),
  ('Non déterminé', '0,4');
  ');
