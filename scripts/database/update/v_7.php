<?php

  /*
    With this file, the new version of the database will be => XX
    The name of the file needs to be v_X.php

    What needs to be changed in this file:
    1. CHANGE THE $new_v value
    2. MAKE YOUR CHANGE TO THE DATABASE

  */

  include '../../bdd-connexion.php';

  /* 1. CHANGE THE new_v VALUE */
  $new_v = 2;

  $last_v_installed = $bdd->query('SELECT version FROM mysql_v ORDER BY version DESC LIMIT 1');
  $last_v_installed = $last_v_installed->fetchAll(PDO::FETCH_ASSOC);
  $last_v_installed = $last_v_installed[0]['version'];

  echo "New version to install = " . $new_v . "<br>";
  echo "Last version installed = " . $last_v_installed . "<br>";

  if ($new_v > $last_v_installed) {
    echo "Install the new version <br>";

    /* 2. MAKE YOUR CHANGE TO THE DATABASE */
    $prepare = $bdd->prepare("
        ALTER TABLE amendements ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE amendements CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE amendements_auteurs ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE amendements_auteurs CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE categories ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE categories CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE circos ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE circos CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE cities_adjacentes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE cities_adjacentes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE cities_infos ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE cities_infos CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE cities_mayors ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE cities_mayors CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_groups ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_groups CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_groups_month ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_groups_month CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_groups_proximite ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_groups_proximite CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_groups_proximite_month ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_groups_proximite_month CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_loyaute ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_loyaute CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_loyaute_six ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_loyaute_six CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_majorite ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_majorite CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_participation ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_participation CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_participation_commission ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_participation_commission CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_participation_six ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_participation_six CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE class_participation_solennels ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE class_participation_solennels CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE debats_infos ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE debats_infos CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE debats_paras ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE debats_paras CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE departement ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE departement CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_accord ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_accord CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_accord_cleaned ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_accord_cleaned CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_all ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_all CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_contacts ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_contacts CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_last ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_last CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE deputes_loyaute ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE deputes_loyaute CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE documents_legislatifs ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE documents_legislatifs CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE dossiers ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE dossiers CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE dossiers_acteurs ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE dossiers_acteurs CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE dossiers_seances ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE dossiers_seances CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE dossiers_votes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE dossiers_votes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_deputes_candidats ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_deputes_candidats CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_europe_listes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_europe_listes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_europe_results ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_europe_results CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_legislatives_cities ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_legislatives_cities CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_legislatives_infos ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_legislatives_infos CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_legislatives_results ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_legislatives_results CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_libelle ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_libelle CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE elect_pres_2 ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE elect_pres_2 CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE explications_mp ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE explications_mp CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE exposes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE exposes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE famsocpro ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE famsocpro CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE faq_categories ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE faq_categories CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE faq_posts ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE faq_posts CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE fields ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE fields CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_accord ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_accord CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_cohesion ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_cohesion CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_effectif ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_effectif CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_effectif_history ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_effectif_history CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_stats ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_stats CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE groupes_stats_history ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE groupes_stats_history CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE hatvp ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE hatvp CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE history_mps_average ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE history_mps_average CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE history_per_mps_average ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE history_per_mps_average CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE insee ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE insee CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE legislature ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE legislature CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE mandat_groupe ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE mandat_groupe CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE mandat_principal ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE mandat_principal CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE mandat_secondaire ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE mandat_secondaire CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE mysql_v ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE mysql_v CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE newsletter ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE newsletter CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE organes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE organes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE parties ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE parties CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE password_resets ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE password_resets CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE posts ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE posts CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE profession_foi ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE profession_foi CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE questions ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE questions CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE quizz ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE quizz CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE readings ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE readings CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE regions ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE regions CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE reunions_infos ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE reunions_infos CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE reunions_presences ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE reunions_presences CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE sujets ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE sujets CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE table_history ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE table_history CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE users ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE users CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE users_mp ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE users_mp CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE users_mp_link ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE users_mp_link CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_amendments ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_amendments CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_datan ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_datan CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_datan_requested ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_datan_requested CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_groupes ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_groupes CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_info ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_info CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_participation ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_participation CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_participation_commission ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_participation_commission CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
            
        ALTER TABLE votes_scores ENGINE = InnoDB, DEFAULT CHARACTER SET  utf8mb4, COLLATE utf8mb4_unicode_ci;
        ALTER TABLE votes_scores CONVERT TO CHARACTER SET  utf8mb4 COLLATE utf8mb4_unicode_ci;
    ");

    if (!$prepare->execute()) {
      echo "The update did not work <br>";
    } else {
      echo "The update worked";

      $prepare = $bdd->prepare("INSERT INTO mysql_v (version) VALUES (:new_version)");
      $prepare->execute(array('new_version' => $new_v));

    }

  } else {
    echo "The last version installed is already up to date <br>";
    die();
  }

?>
