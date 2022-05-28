<?php
  include('../bdd-connexion.php');

  try {
    $bdd->query("SELECT swap FROM quizz LIMIT 1");
    echo "field 'swap' already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `quizz` ADD `swap` TINYINT(1) NOT NULL DEFAULT 0 AFTER `category`');
  }

  try {
    $bdd->query("SELECT explication FROM quizz LIMIT 1");
    echo "field 'explication' already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `quizz` ADD `explication` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `title`');
  }
