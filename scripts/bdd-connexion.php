<?php

try
            {
              $bdd = new PDO('mysql:host=localhost;dbname=datan', 'root', '', array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                )
              );
            }
          catch (Exception $e)
            {
              die('Erreur : ' . $e->getMessage());
            }



//$connexion = new PDO('mysql:host='.$PARAM_hote.';dbname='.$PARAM_nom_bd, $PARAM_utilisateur, $PARAM_mot_passe, array(PDO::ATTR_PERSISTENT => true)); // Persistent connection

?>
