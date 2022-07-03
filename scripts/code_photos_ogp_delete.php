<?php

  include 'bdd-connexion.php';

  $donnees = $bdd->query('SELECT mpId FROM deputes_last WHERE legislature = 16');

  while ($mp = $donnees->fetch()) {
    $id = $mp['mpId'];
    $fileOriginal = __DIR__ . "/../assets/imgs/deputes_ogp/original/ogp_deputes_" . $id . ".png";
    $file = __DIR__ . "/../assets/imgs/deputes_ogp/ogp_deputes_" . $id . ".png";

    if (file_exists($fileOriginal)) {
      if (unlink($fileOriginal)) {
        echo "Original : file was deleted \n";
      } else {
        echo "Original : could not delete file \n";
      }
    } else {
      echo "Original : file does not exists \n";
    }

    if (file_exists($file)) {
      if (unlink($file)) {
        echo "Folder : file was deleted \n";
      } else {
        echo "Folder : could not delete file \n";
      }
    } else {
      echo "Folder : file does not exists \n";
    }

    echo "\n";

  }

?>
