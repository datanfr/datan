<?php
  try {
    $bdd = new PDO('mysql:host='. $_SERVER['DATABASE_HOST'] . ';dbname='.$_SERVER['DATABASE_NAME'], $_SERVER['DATABASE_USERNAME'], $_SERVER['DATABASE_PASSWORD'], array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    ));
  } catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
  }
?>
