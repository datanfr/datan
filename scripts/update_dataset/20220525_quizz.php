<?php
  include('../bdd-connexion.php');

  try {
    $bdd->query("SELECT swap FROM quizz LIMIT 1");
    echo "field 'swap' already exists<br>";
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `quizz` ADD `swap` TINYINT NOT NULL DEFAULT 0 AFTER `category`');
  }
