<?php
  class Groupes_edito extends CI_Model {

    // Fonction édito
    public function edito($id, $opposition){
      $infos = [];
      // Variables
      if ($opposition == "Minoritaire") {
        $infos["opposition"] = "la majorité présidentielle";
      }
      elseif ($opposition == "Opposition") {
        $infos["opposition"] = "l'opposition à la majorité présidentielle";
      } elseif ($opposition == "Majoritaire") {
        $infos["opposition"] = "la majorité présidentielle";
      } else {
        $infos["opposition"] = "à faire";
      }

      $qqjours = ", soit dès la mise en place de la nouvelle législature";
      $left = "à gauche";
      $center_left = "au centre-gauche";
      $center = "au centre";
      $center_right = "au centre-droit";
      $right = "à droite";

      //Positionnement
      switch ($id) {
        case 'FI':
          $infos['creation'] = $qqjours;
          break;

        case 'GDR':
          $infos['creation'] = $qqjours;
          break;

        case 'LAREM':
          $infos['creation'] = $qqjours;
          break;

        case 'LR':
          $infos['creation'] = $qqjours;
          break;

        case 'LT':
          $infos['creation'] = ". Ce nouveau groupe a été créé par des élus venant d'horizons divers (radicaux, centristes, autonomistes corses, membres d'En Marche). Officiellement dans membre de la majorité présidentielle, le groupe <a href='https://www.lemonde.fr/politique/article/2018/10/17/un-huitieme-groupe-cree-a-l-assemblee-nationale_5370790_823448.html' target='_blank'>explique rester libre de s'opposer si nécessaire</a>";
          break;

        case 'MODEM':
          $infos['creation'] = $qqjours;
          break;

        case 'SOC':
          $infos['creation'] = ". Ce nouveau groupe est la continuité directe de l'ancien groupe Nouvelle Gauche (NG), <a href='https://www.lemonde.fr/politique/article/2018/09/10/les-deputes-socialistes-vont-a-nouveau-s-appeler-socialistes_5352990_823448.html' target='_blank'>les députés socialistes ayant décidé de changer de nom en septembre 2018</a>";
          break;

        case 'UDI-A-I':
          $infos['creation'] = ". Ce nouveau groupe UDI-A-I est la continuité directe de l'<a href='".base_url()."groupes/udi-i'>ancien groupe UDI-I</a>. Il acte le réchauffement des relations entre les députés UDI et AGIR, <a href='https://www.lefigaro.fr/politique/le-scan/a-l-assemblee-le-groupe-udi-agir-au-bord-du-divorce-20190611' target='_blank'>distendues depuis les élections européennes de 2019</a>. Désormais membre de la majorité présidentielle, ce nouveau groupe, UDI-A-I, <a href='https://www.lejdd.fr/Politique/a-lassemblee-jean-christophe-lagarde-et-ludi-sallient-a-la-majorite-3922197' target='_blank'>officiallise également son alliance avec le groupe La République en Marche</a>";
          break;

        case 'NI':
          $infos['creation'] = $qqjours;
          break;

        case 'NG':
          $infos['creation'] = " et a été remplacé par le groupe <a href='".base_url()."groupes/soc'>Socialistes et apparentés (SOC)</a>";
          break;

        case 'LC':
          $infos['creation'] = " et a été remplacé par le groupe <a href='".base_url()."groupes/udi-agir'>UDI, Agir et indépendants (UDI-AGIR)</a>";
          break;

        case 'UDI-AGIR':
          $infos['creation'] = " et a été remplacé par le groupe <a href='".base_url()."groupes/udi-i'>UDI et Indépendants (UDI-I)</a>";
          break;

        case 'UDI-I':
          $infos['creation'] = " et a été remplacé par le groupe <a href='".base_url()."groupes/udi-a-i'>UDI, Agir et Indépendants (UDI-A-I)</a>";
          break;

        case 'EDS':
          $infos['creation'] = " par des députés venant pour la plupart du groupe de la majorité présidentielle, La République en Marche";
          break;

        case 'AGIR-E':
          $infos['creation'] = " par des députés membres du parti politique AGIR. Ils étaient avant alliés aux députés UDI dans le groupe <a href='".base_url()."groupes/udi-a-i'>UDI-A-I</a>";

        case 'UDI_I':
          $infos['creation'] = " suite à la dissolution du groupe <a href='".base_url()."groupes/udi-a-i'>UDI-A-I</a>. Ce nouveau groupe UDI ne comporte plus que les députés membres de UDI, les députés AGIR ayant créé leur propre groupe, <a href='".base_url()."groupes/agir-e'>Agir Ensemble</a>";

        case 'DEM':
          $infos['creation'] = " suite à la dissolution du groupe <a href='".base_url()."groupes/modem'>MODEM</a>. Ce nouveau groupe centriste a accueilli de nouveaux députés anciennement membres du groupe La République en Marche, comme Sabine Thillaye ou Christophe Jerretie.";



        default:
          $infos['creation'] = NULL;
      }

      return $infos;
    }

    public function participation($groupe, $average){
      $average = $average*100;

      if ($groupe > $average) {
        $phrase = "plus";
      } elseif ($groupe < $average) {
        $phrase = "moins";
      } elseif ($groupe == $average) {
        $phrase = "autant";
      }
      return $phrase;
    }

    public function cohesion($groupe, $average){
      $groupe = round($groupe, 2);
      $average = round($average, 2);
      if ($groupe > $average) {
        $array["relative"] = "plus";
        $array["absolute"] = "très";
      } elseif ($groupe < $average) {
        $array["relative"] = "moins";
        $array["absolute"] = "peu";
      } elseif ($groupe == $average) {
        $array["relative"] = "aussi";
        $array["absolute"] = "relativement bien";
      }

      return $array;
    }

    public function majorite($groupe, $average){
      $average = $average*100;

      if ($groupe > $average) {
        $phrase = "plus";
      } elseif ($groupe < $average) {
        $phrase = "moins";
      } elseif ($groupe == $average) {
        $phrase = "aussi";
      }
      return $phrase;
    }

    public function proximite($stats, $positionnement){
      function maj_pres($positionPolitique){
        if ($positionPolitique == "Opposition") {
          $maj_pres = "un groupe d'opposition";
        } elseif ($positionPolitique == "Majoritaire") {
          $maj_pres = "le groupe de la majorité présidentielle, qui est";
        } elseif ($positionPolitique == "Minoritaire") {
          $maj_pres = "un groupe allié à la majorité présidentielle et";
        } else {
          $maj_pres = "qui regroupe les députés non affiliés à un groupe parlementaire";
        }
        return $maj_pres;
      }

      if (!empty($stats)) {
        $first1 = $stats[0];
        $first1["ideology"] = $positionnement[$first1["libelleAbrev"]]["edited"];
        $first1["maj_pres"] = maj_pres($first1["positionPolitique"]);
        $first2 = $stats[1];
        $first2["ideology"] = $positionnement[$first2["libelleAbrev"]]["edited"];
        $first2["maj_pres"] = maj_pres($first2["positionPolitique"]);

        $last1 = $stats[count($stats)-1];
        $last1["ideology"] = $positionnement[$last1["libelleAbrev"]]["edited"];
        $last1["maj_pres"] = maj_pres($last1["positionPolitique"]);
        $last2 = $stats[count($stats)-2];
        $last2["ideology"] = $positionnement[$last2["libelleAbrev"]]["edited"];
        $last2["maj_pres"] = maj_pres($last2["positionPolitique"]);

        $array = [
          "first1" => $first1,
          "first2" => $first2,
          "last1" => $last1,
          "last2" => $last2
        ];
      } else {
        $array = NULL;
      }

      return $array;
    }

    public function get_ni($legislature){
      if ($legislature == 15) {
        return "
        <p>Pendant la 15ème législature, le parti politique le plus représenté parmi les députés non-inscrits était le Rassemblement national (avec par exemple <a href='" . base_url() . "deputes/pas-de-calais-62/depute_marine-lepen' target='_blank'>Marine Le Pen</a>). Avec seulement 7 députés, les élus du Rassemblement nationale n'ont pas réussi à atteindre les 15 députés nécessaires pour former leur propre groupe politique.</p>
        <p>Pendant cette législature, on retrouvait également parmi les non-inscrits <a href='" .  base_url() . "deputes/essonne-91/depute_nicolas-dupontaignan' target='_blank'>Nicolas Dupont-Aignan</a>, du parti politique Debout La France, <a href='" .  base_url() . "deputes/deux-sevres-79/depute_delphine-batho' target='_blank'>Delphine Batho</a>, du parti écologiste Génération écologie, ainsi que d'anciens membres du groupe La République en Marche, comme <a href='" . base_url() . "deputes/indre-et-loire-37/depute_sabine-thillaye' target='_blank'>Sabine Thillaye</a> ou <a href='" . base_url() . "deputes/nord-59/depute_jennifer-detemmerman' target='_blank'>Jennifer de Temmerman</a>.</p>
        ";
      }
    }

  }
?>
