<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>code_1_deputes_noms</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script cleans the first and last names of MPs to create, in the table
  'deputes', the nameUrl variable (one slug for each MP).

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>1-2. Modification des noms de la bdd 'deputes'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <?php
          	/**
           * Supprimer les accents
           *
           * @param string $str chaîne de caractères avec caractères accentués
           * @param string $encoding encodage du texte (exemple : utf-8, ISO-8859-1 ...)
           */
          function suppr_accents($str, $encoding='utf-8')
          {
              // transformer les caractères accentués en entités HTML
              $str = htmlentities($str, ENT_NOQUOTES, $encoding);

              // remplacer les entités HTML pour avoir juste le premier caractères non accentués
              // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "à" => "a" ...
              $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);

              // Remplacer les ligatures tel que : , Æ ...
              // Exemple "œ" => "oe"
              $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
              // Supprimer tout le reste
              $str = preg_replace('#&[^;]+;#', '', $str);

              return $str;
          }
        	include 'bdd-connexion.php';
        	$lastIdSql = $bdd->query('
        		SELECT mpId, nameFirst, nameLast
        		FROM deputes
        		WHERE nameUrl is null
        		ORDER BY mpId ASC
        		LIMIT 1000
        		');

          	echo '<hr>';

          	while ($data = $lastIdSql->fetch()) {
          		$mpId = $data['mpId'];
          		echo '<p>Last id: '.$data['mpId'].'</p>';
          		echo '<p>prenom origine: '.$data['nameFirst'].'</p>';
          		echo '<p>nom origine: '.$data['nameLast'].'</p><br>';
          		$firstAccent = $data['nameFirst'];
          		$firstAccent = suppr_accents($firstAccent);
          		$firstAccent = strtolower($firstAccent);
          		$lastAccent = $data['nameLast'];
          		$lastAccent = suppr_accents($lastAccent);
          		$lastAccent = strtolower($lastAccent);
          		echo '<p>firstAccent: '.($firstAccent).'</p>';
          		echo '<p>lastAccent: '.($lastAccent).'</p>';
          		$firstOk = strtr($firstAccent, array("'" => "", "-" => "", " " => "-"));
          		echo '<p>firstOk: '.$firstOk.'</p>';
          		$lastOk = strtr($lastAccent, array('.' => '', ',' => '', "'" => "", " " => "", "-" => ""));
          		echo '<p>lastOk: '.$lastOk.'</p>';
          		$nameUrl = $firstOk.'-'.$lastOk;
          		echo '<p>nameUrl: '.$nameUrl.'</p>';
          		echo '<hr>';
              // INSERT INTO SQL //
          		$sql = $bdd->prepare('UPDATE deputes SET nameUrl = :nameUrl WHERE mpId = "'.$mpId.'"');
          		$sql -> execute(array('nameUrl' => $nameUrl));
          	}
          ?>
        </div>
      </div>
    </div>
  </body>
</html>
