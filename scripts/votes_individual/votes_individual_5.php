<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>votes_5 individual</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
	<style>
		table,
		th,
		td {
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
	if (isset($_GET["vote"])) {
		$vote = $_GET["vote"];
	}
	 ?>
	<div class="container" style="background-color: #e9ecef;">
		<div class="row">
			<h1>votes_5 individual</h1>
		</div>
		<div class="row">
			<div class="col-4">
				<a class="btn btn-outline-success" href="votes_individual_6.php?vote=<?= $vote ?>" role="button">Next</a>
			</div>
		</div>
		<div class="row mt-3">
			<?php

			// CONNEXION SQL //
			include '../bdd-connexion.php';

			if (isset($_GET["vote"])) {
				$number_to_get = $_GET["vote"];

				//DELETE FROM TABLE
				$sql_delete = "DELETE FROM votes_scores WHERE voteNumero = :number_to_get";
				$stmt = $bdd->prepare($sql_delete);
				$stmt->execute(array('number_to_get' => $number_to_get));

			} else {
				exit("Please indicate the number of the vote in the url (?vote=xx)");
			}

			echo "VOTE TO GET = ".$number_to_get."<br>";

			$bdd->query('SET SQL_BIG_SELECTS=1');

			$reponseVote = $bdd->query('
				SELECT v.voteNumero, v.mpId, vi.dateScrutin, v.voteType, v.vote, v.voteId, sortCode, v.legislature, vi.nombreVotants,
				mg.mandatId AS mandat_uid, mg.typeOrgane, mg.dateDebut, mg.dateFin, mg.organeRef FROM votes v
				JOIN votes_info vi ON vi.voteId = v.voteId
				LEFT JOIN mandat_groupe mg ON mg.mpId = v.mpId
				AND ((vi.dateScrutin BETWEEN mg.dateDebut AND mg.dateFin ) OR (vi.dateScrutin > mg.dateDebut AND mg.dateFin IS NULL))
				AND mg.codeQualite IN ("Membre", "Député non-inscrit", "Membre apparenté")
				LEFT JOIN organes o ON o.uid = vi.organeRef
				WHERE v.voteType != "miseAuPoint" AND (v.voteNumero = "' . $number_to_get . '") AND vote != "nv"
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
						<td>mandat_uid</td>
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
        					WHERE voteId = "' . $donnees['voteId'] . '" AND organeRef = "' . $donnees['organeRef'] . '"
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
                    WHERE organeRef = "PO730964" AND voteId = "' . $donnees['voteId'] . '"
                  ');

						while ($donneesGouvernement = $reponseGouvernement->fetch()) {
							if ($donneesGouvernement['positionMajoritaire'] == 'pour') {
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
						echo '<td>' . $i . '</td>';
						echo '<td>' . $donnees['mpId'] . '</td>';
						echo '<td>' . $vote . '</td>';
						echo '<td>' . $donnees['voteType'] . '</td>';
						echo '<td>' . $donnees['voteNumero'] . '</td>';
						echo '<td>' . $donnees['voteId'] . '</td>';
						echo '<td>' . $sort . '</td>';
						echo '<td>' . $donnees['legislature'] . '</td>';
						echo '<td>' . $donnees['dateScrutin'] . '</td>';
						echo '<td>' . $donnees['dateDebut'] . '</td>';
						echo '<td>' . $donnees['dateFin'] . '</td>';
						echo '<td>' . $donnees['mandat_uid'] . '</td>';
						echo '<td>' . $voteGroupe . '</td>';
						echo '<td>' . $voteGvt . '</td>';
						echo '<td>' . $scoreLoyaute . '</td>';
						echo '<td>' . $scoreAccGvt . '</td>';
						echo '<td>' . $scoreGagnant . '</td>';
						echo '<td>' . $scoreParticipation . '</td>';
						echo '<tr>';


						$sql = $bdd->prepare("INSERT INTO votes_scores (voteId, voteNumero, legislature, mpId, vote, typeVote, organeId, dateVote, positionGroup, scoreLoyaute, scoreGagnant, scoreParticipation, positionGvt, scoreGvt) VALUES (:voteId, :voteNumero, :legislature, :mpId, :vote, :type_vote, :organe_id, :dateVote, :position_group, :scoreLoyaute, :scoreGagnant, :scoreParticipation, :voteGvt, :scoreAccGvt)");

						$sql->execute(array('voteId' => $donnees['voteId'], 'voteNumero' => $donnees['voteNumero'], 'legislature' => $donnees['legislature'], 'mpId' => $donnees['mpId'], 'vote' => $vote, 'type_vote' => $donnees['voteType'], 'organe_id' => $donnees['mandat_uid'], 'dateVote' => $donnees['dateScrutin'], 'position_group' => $voteGroupe, 'scoreLoyaute' => $scoreLoyaute, 'scoreGagnant' => $scoreGagnant, 'scoreParticipation' => $scoreLoyaute, 'voteGvt' => $voteGvt, 'scoreAccGvt' => $scoreAccGvt));


						$i++;
					}
				}

				?>

			</table>
		</div>
	</div>
</body>

</html>
