<html>
	<head>
		<title>Suppression de BDD</title>
	</head>
	<body>
        <form name="update" method="post" action="supp.php"">
		  	<select name="cars">
		    	<option value="mandat_secondaire">Mandats secondaires</option>
		    	<option value="groupes_effectif">Groupes effectif</option>
		  	</select>
		  	<br><br>
		  <input type="submit" name="valider">
		</form>



		<?php
			include '../bdd-connexion.php';

			if(isset($_POST['valider'])){
				$page = $_POST['cars'];
				echo $page;

				$bdd->exec('TRUNCATE TABLE '.$page.'');

			} else {
				echo 'error';
			}
		?>
	</body>
</html>