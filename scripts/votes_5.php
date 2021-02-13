<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="15">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
    <style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
  </style>
  </head>
  <!--

  This script updates the table 'votes_scores'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_second = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1><?= $_SERVER['REQUEST_URI'] ?></h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <?php

        // CONNEXION SQL //
        	include 'bdd-connexion.php';

        // TEST AVEC BDD //
        		$reponse_last_vote = $bdd->query('
        		SELECT voteNumero AS lastVote
        		FROM votes_scores
        		ORDER BY voteNumero DESC
        		LIMIT 1
        		');

        		while ($donnees_last_vote = $reponse_last_vote->fetch()) {
        			echo '<p>Last vote: '.$donnees_last_vote['lastVote'].'</p>';
        			$lastVote = $donnees_last_vote['lastVote'] + 1;
                    $untilVote = $donnees_last_vote['lastVote'] + 10;
         			echo 'last vote = '.$lastVote.'<br>';
         			echo 'until vote = '.$untilVote.'<br>';
        		}

        	  if (!isset($lastVote)) {
        		  	echo 'rien dans la base<br>';
        		  	$lastVote = 1;
                $untilVote = 1;
        		  }

              //$lastVote = 1;
              //$untilVote = 1;

            echo 'last vote = '.$lastVote.'<br>';

        		$bdd->query('SET SQL_BIG_SELECTS=1');

            $reponseVote = $bdd->query('
            SELECT B.*, o.libelle
            FROM
            (
              SELECT A.*,  mg.mandatId AS mandat_uid, mg.typeOrgane, mg.dateDebut, mg.dateFin, mg.organeRef
              FROM (
                SELECT v.voteNumero, v.mpId, vi.dateScrutin, v.voteType, v.vote, v.voteId, sortCode, v.legislature, vi.nombreVotants
                FROM votes v
                JOIN votes_info vi ON vi.voteId = v.voteId
                WHERE v.voteType != "miseAuPoint" AND (v.voteNumero BETWEEN "'.$lastVote.'" AND "'.$untilVote.'") AND vote != "nv"
              ) A
              LEFT JOIN mandat_groupe mg ON mg.mpId = A.mpId AND ((A.dateScrutin BETWEEN mg.dateDebut AND mg.dateFin ) OR (A.dateScrutin > mg.dateDebut AND mg.dateFin IS NULL)) AND mg.codeQualite IN ("Membre", "Député non-inscrit", "Membre apparenté")
              ) B
            LEFT JOIN organes o ON o.uid = B.organeRef
            ');

            $i = 1;

            /*
            while ($row = $reponseVote->fetch(PDO::FETCH_ASSOC)) {
              echo $i.'-'.$row['num'].' '.$row['idDepute'].' '.$row['vote'].' '.$row['dateScrutin'].' '.$row['mandat_uid'].' '.$row['typeOrgane'].' '.$row['dateDebut'].' '.$row['dateFin'].' '.$row['organe'].' '.$row['libelle'];
              echo '<br>';
              $i++;
            }
            */
        	?>

        	<table>
        		<thead>
              <tr>
          			<td>#</td>
          			<td>mpId</td>
          			<td>vote</td>
          			<td>typeVote</td>
          			<td>numero</td>
          			<td>voteId</td>
          			<td>sortCode</td>
          			<td>legislature</td>
          			<td>date</td>
          			<td>dateDebut</td>
          			<td>dateFin</td>
          			<td>organe</td>
          			<td>organeRef</td>
          			<td>voteGroupe</td>
                <td>voteEmMarche</td>
          			<td>scoreLoyaute</td>
                <td>scoreAccGouvernement</td>
          			<td>scoreGagnant</td>
          			<td>scoreParticipation</td>
              </tr>
        		</thead>

        	<?php

        	$i = 1;

        			while ($donnees = $reponseVote->fetch(PDO::FETCH_ASSOC)) {
                switch ($donnees["vote"]) {
                  case '1':
                    $vote = 1;
                    break;
                  case '-1':
                    $vote = -1;
                    break;
                  case "0":
                    $vote = 0;
                    break;
                  case "nv":
                    $vote = "nv";
                    break;
                  default:
                    $vote = "nv";
                    break;
                }

        				switch ($donnees['sortCode']) {
        					case 'adopté':
        						$sort = 1;
        						break;
        					case 'rejeté':
        						$sort = -1;
        						break;
        					default:
        						$sort = 0;
        						break;
        				}

        				$reponseGroupe = $bdd->query('
        					SELECT positionMajoritaire
        					FROM votes_groupes
        					WHERE voteId = "'.$donnees['voteId'].'" AND organeRef = "'.$donnees['organeRef'].'"
        					');

        				while ($donneesGroupe = $reponseGroupe->fetch()) {
        					if ($donneesGroupe['positionMajoritaire'] == 'pour') {
        						$voteGroupe = 1;
        					} elseif ($donneesGroupe['positionMajoritaire'] ==  'contre') {
        						$voteGroupe = -1;
        					} elseif ($donneesGroupe['positionMajoritaire'] == 'abstention') {
        						$voteGroupe = 0;
        					} else {
        						$voteGroupe = 'error';
        					}

                  //voteMajorité
                  $reponseGouvernement = $bdd->query('
                    SELECT positionMajoritaire
                    FROM votes_groupes
                    WHERE organeRef = "PO730964" AND voteId = "'.$donnees['voteId'].'"
                  ');

                  while ($donneesGouvernement = $reponseGouvernement->fetch()) {
                    if($donneesGouvernement['positionMajoritaire'] == 'pour'){
                      $voteGvt = 1;
                    } elseif ($donneesGouvernement['positionMajoritaire'] == 'contre') {
                      $voteGvt = -1;
                    } elseif ($donneesGouvernement['positionMajoritaire'] == 'abstention') {
                      $voteGvt = 0;
                    } else {
                      $voteGvt = 'error';
                    }
                  }

        					// calcul scoreLoyaute

        					if ($voteGroupe == $vote or $voteGroupe === $vote) {
        						$scoreLoyaute = 1;
        					} else {
        						$scoreLoyaute = 0;
        					}

                  // calcul accord gouvernement
                  if ($voteGvt == $vote or $voteGvt === $vote) {
                    $scoreAccGvt = 1;
                  } else {
                    $scoreAccGvt = 0;
                  }

        					// calcul scoreGagnant

        					if ($vote === $sort) {
        						$scoreGagnant = 1;
        					} else {
        						$scoreGagnant = 0;
        					}

        					// Apply scoreParticipation

        					$scoreParticipation = 1;

        					// Apply nv

        					if ($vote === 'nv') {
        						$scoreLoyaute = 'nv';
        						$scoreGagnant = 'nv';
        						$scoreParticipation = 'nv';
        					}

        					if ($vote === 'error') {
        						$scoreLoyaute = 'error';
        						$scoreGagnant = 'error';
        						$scoreParticipation = 'error';
        					}

        			echo '<tr>';
        			echo '<td>'.$i.'</td>';
        			echo '<td>'.$donnees['mpId'].'</td>';
              echo '<td>'.$vote.'</td>';
        			echo '<td>'.$donnees['voteType'].'</td>';
        			echo '<td>'.$donnees['voteNumero'].'</td>';
        			echo '<td>'.$donnees['voteId'].'</td>';
        			echo '<td>'.$sort.'</td>';
        			echo '<td>'.$donnees['legislature'].'</td>';
        			echo '<td>'.$donnees['dateScrutin'].'</td>';
        			echo '<td>'.$donnees['dateDebut'].'</td>';
        			echo '<td>'.$donnees['dateFin'].'</td>';
        			echo '<td>'.$donnees['libelle'].'</td>';
        			echo '<td>'.$donnees['mandat_uid'].'</td>';
        			echo '<td>'.$voteGroupe.'</td>';
              echo '<td>'.$voteGvt.'</td>';
        			echo '<td>'.$scoreLoyaute.'</td>';
              echo '<td>'.$scoreAccGvt.'</td>';
        			echo '<td>'.$scoreGagnant.'</td>';
        			echo '<td>'.$scoreParticipation.'</td>';
        			echo '<tr>';


              $sql = $bdd->prepare("INSERT INTO votes_scores (voteId, voteNumero, legislature, mpId, vote, typeVote, organeId, dateVote, positionGroup, scoreLoyaute, scoreGagnant, scoreParticipation, positionGvt, scoreGvt) VALUES (:voteId, :voteNumero, :legislature, :mpId, :vote, :type_vote, :organe_id, :dateVote, :position_group, :scoreLoyaute, :scoreGagnant, :scoreParticipation, :voteGvt, :scoreAccGvt)");

              $sql->execute(array('voteId' => $donnees['voteId'], 'voteNumero' => $donnees['voteNumero'], 'legislature' => $donnees['legislature'] ,'mpId' => $donnees['mpId'], 'vote' => $vote, 'type_vote' => $donnees['voteType'], 'organe_id' => $donnees['mandat_uid'], 'dateVote' => $donnees['dateScrutin'], 'position_group' => $voteGroupe, 'scoreLoyaute' => $scoreLoyaute, 'scoreGagnant' => $scoreGagnant, 'scoreParticipation' => $scoreLoyaute, 'voteGvt' => $voteGvt, 'scoreAccGvt' => $scoreAccGvt));


        		$i++;
        	}
        }

        	?>

	     </table>
      </div>
    </div>
  </body>
</html>
