<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="refresh" content="5">
	<title><?= $_SERVER['REQUEST_URI'] ?></title>
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
	$url = $_SERVER['REQUEST_URI'];
	$url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
	$url_current = substr($url, 0, 1);
	$url_second = $url_current + 1;

	include "include/legislature.php";
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
				<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME'] . '' . $_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
			</div>
			<div class="col-4">
				<?php if ($legislature_to_get == 15): ?>
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">Next</a>
					<?php else: ?>
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php?legislature=<?= $legislature_to_get ?>" role="button">Next</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="row mt-3">
			<h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
			<?php

			// CONNEXION SQL //
			include 'bdd-connexion.php';

			// TEST AVEC BDD //
			$reponse_last_vote = $bdd->query('
        		SELECT voteNumero AS lastVote
        		FROM votes_scores
						WHERE legislature = "'.$legislature_to_get.'"
        		ORDER BY voteNumero DESC
        		LIMIT 1
        		');

			while ($donnees_last_vote = $reponse_last_vote->fetch()) {
				echo '<p>Last vote: ' . $donnees_last_vote['lastVote'] . '</p>';
				$lastVote = $donnees_last_vote['lastVote'] + 1;
				$untilVote = $lastVote + 49;
				echo 'last vote = ' . $lastVote . '<br>';
				echo 'until vote = ' . $untilVote . '<br>';
			}

			if (!isset($lastVote)) {
				echo 'rien dans la base<br>';
				$lastVote = 1;
				$untilVote = 1;
			}

			echo 'last vote = ' . $lastVote . '<br>';

			$bdd->query('SET SQL_BIG_SELECTS=1');

			$reponseVote = $bdd->query('
				SELECT B.voteNumero, B.legislature, B.mpId, B.vote, B.mandat_uid AS mandatId, B.sortCode, B.positionGroup, B.gvtPosition AS positionGvt,
				case when B.vote = B.positionGroup then 1 else 0 end as scoreLoyaute,
				case when B.vote = B.sortCode then 1 else 0 end as scoreGagnant,
				case when B.vote = B.gvtPosition then 1 else 0 end as scoreGvt,
				1 as scoreParticipation
				FROM
				(
				SELECT A.*,
				case
				when vg.positionMajoritaire = "pour" then 1
				when vg.positionMajoritaire = "contre" then -1
				when vg.positionMajoritaire = "abstention" then 0
				else "error" end as positionGroup,
				case
				when gvt.positionMajoritaire = "pour" then 1
				when gvt.positionMajoritaire = "contre" then -1
				when gvt.positionMajoritaire = "abstention" then 0
				else "error" end as gvtPosition
				FROM
				(
				SELECT v.voteNumero, v.mpId, v.vote,
				case
				when sortCode = "adopté" then 1
				when sortCode = "rejeté" then -1
				else 0 end as sortCode,
				v.legislature,
				mg.mandatId AS mandat_uid, mg.organeRef
				FROM votes v
				JOIN votes_info vi ON vi.voteNumero = v.voteNumero AND vi.legislature = v.legislature
				LEFT JOIN mandat_groupe mg ON mg.mpId = v.mpId
				AND ((vi.dateScrutin BETWEEN mg.dateDebut AND mg.dateFin ) OR (vi.dateScrutin > mg.dateDebut AND mg.dateFin IS NULL))
				AND mg.codeQualite IN ("Membre", "Député non-inscrit", "Membre apparenté")
				LEFT JOIN organes o ON o.uid = vi.organeRef
				WHERE v.voteType != "miseAuPoint" AND (v.voteNumero BETWEEN "'.$lastVote.'" AND "'.$untilVote.'") AND v.legislature = "'.$legislature_to_get.'" AND vote != "nv"
				) A
				LEFT JOIN votes_groupes vg ON vg.organeRef = A.organeRef AND vg.voteNumero = A.voteNumero AND vg.legislature = A.legislature
				LEFT JOIN votes_groupes gvt ON gvt.organeRef IN ("PO730964", "PO730964", "PO656002") AND gvt.voteNumero = A.voteNumero AND gvt.legislature = A.legislature
				) B
      ');

			$i = 1;


			?>

			<table>
				<thead>
					<tr>
						<td>#</td>
						<td>voteNumero</td>
						<td>legislature</td>
						<td>mpId</td>
						<td>vote</td>
						<td>mandatId</td>
						<td>sortCode</td>
						<td>positionGroup</td>
						<td>scoreLoyaute</td>
						<td>scoreGagnant</td>
						<td>scoreParticipation</td>
						<td>positionGvt</td>
						<td>scoreGvt</td>
					</tr>
				</thead>

				<?php

				$i = 1;

				while ($x = $reponseVote->fetch(PDO::FETCH_ASSOC)) {



						echo '<tr>';
						echo '<td>' . $i . '</td>';
						echo '<td>' . $x['voteNumero'] . '</td>';
						echo '<td>' . $x['legislature'] . '</td>';
						echo '<td>' . $x['mpId'] . '</td>';
						echo '<td>' . $x['vote'] . '</td>';
						echo '<td>' . $x['mandatId'] . '</td>';
						echo '<td>' . $x['sortCode'] . '</td>';
						echo '<td>' . $x['positionGroup'] . '</td>';
						echo '<td>' . $x['scoreLoyaute'] . '</td>';
						echo '<td>' . $x['scoreGagnant'] . '</td>';
						echo '<td>' . $x['scoreParticipation'] . '</td>';
						echo '<td>' . $x['positionGvt'] . '</td>';
						echo '<td>' . $x['scoreGvt'] . '</td>';
						echo '<tr>';


						$sql = $bdd->prepare("INSERT INTO votes_scores (voteNumero, legislature, mpId, vote, mandatId, sortCode, positionGroup, scoreLoyaute, scoreGagnant, scoreParticipation, positionGvt, scoreGvt, dateMaj) VALUES (:voteNumero, :legislature, :mpId, :vote, :mandatId, :sortCode, :positionGroup, :scoreLoyaute, :scoreGagnant, :scoreParticipation, :positionGvt, :scoreGvt, curdate())");

						$arraySql = array(
							'voteNumero' => $x['voteNumero'],
							'legislature' => $x['legislature'],
							'mpId' => $x['mpId'],
							'vote' => $x['vote'],
							'mandatId' => $x['mandatId'],
							'sortCode' => $x['sortCode'],
							'positionGroup' => $x['positionGroup'],
							'scoreLoyaute' => $x['scoreLoyaute'],
							'scoreGagnant' => $x['scoreGagnant'],
							'scoreParticipation' => $x['scoreParticipation'],
							'positionGvt' => $x['positionGvt'],
							'scoreGvt' => $x['scoreGvt'],
						);

						$sql->execute($arraySql);


						$i++;
					}

				?>

			</table>
		</div>
	</div>
</body>

</html>
