<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--<meta http-equiv="refresh" content="10">-->
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script updates the table 'votes_info'

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
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
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
        <div class="col-12">
          <h2 class="bg-success">Run this script only once.</h2>
          <table class="table">
            <thead>
              <tr>
                <th>id</th>
                <th>num</th>
                <th>titre</th>
                <th>type_vote</th>
                <th>amdt_n</th>
                <th>article_n</th>
                <th>BIS/TER article</th>
                <th>pos_articld</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include 'bdd-connexion.php';
              $results = $bdd->query('
                SELECT *
                FROM votes_info
                WHERE legislature = 15
                ORDER BY voteNumero DESC
              ');

              while($data = $results->fetch()) {
                $uid = $data["id"];
                $num = $data["voteNumero"];
                $titre = $data["titre"];

                //variable type_vote
                if (strpos($titre, "ensemble d")) {
                  $type_vote = "final";
                } elseif (strpos($titre, "sous-amendement") || strpos($titre, "sous-amendment")){
                  $type_vote = "sous-amendement";
                } elseif (strpos($titre, "'amendement")) {
                  $type_vote = "amendement";
                } elseif(substr($titre, 0, 8) == "l'articl" || substr($titre, 0, 8) == " l'artic") {
                  $type_vote = "article";
                } elseif (strpos($titre, "a motion de rejet prealable") || strpos($titre, "a motion de rejet préalable")) {
                  $type_vote = "motion de rejet préalable";
                } elseif (strpos($titre, "a motion de renvoi en commi")) {
                  $type_vote = "motion de renvoi en commission";
                } elseif (strpos($titre, "a motion de censure")) {
                  $type_vote = "motion de censure";
                } elseif (strpos($titre, "motion référendaire")) {
                  $type_vote = "motion référendaire";
                } elseif (strpos($titre, "a declaration de politique generale")) {
                  $type_vote = "declaration de politique generale";
                } elseif (strpos($titre, "es crédits de la mission") || strpos($titre, "es credits de")) {
                  $type_vote = "crédits de mission";
                } elseif (strpos($titre, "a déclaration du Gouvernement")) {
                  $type_vote = "déclaration du gouvernement";
                } elseif (strpos($titre, "partie du projet de loi de finances")) {
                  $type_vote = "partie du projet de loi de finances";
                } elseif (strpos($titre, "demande de constitution de commission speciale") | strpos($titre, "demande de constitution de la commission speciale")) {
                  $type_vote = "demande de constitution de commission speciale";
                } elseif (strpos($titre, "demande de suspension de séance")) {
                  $type_vote = "demande de suspension de séance";
                } elseif (strpos($titre, "motion d'ajournement")) {
                  $type_vote = "motion d'ajournement";
                } elseif (strpos($titre, "conclusions de rejet de la commission")) {
                  $type_vote = "conclusions de rejet de la commission";
                } else {
                  $type_vote = substr($titre, 0, 8);
                  //$type_vote = "REVOIR";
                }

                //variable amdt_n
                if ($type_vote == "amendement") {
                  $amdt_n = substr($titre, 0, 25);
                  $amdt_n = preg_replace("/[^0-9]/", "", $amdt_n);
                } else {
                  $amdt_n = NULL;
                }

                //varible article_n
                if ($type_vote == "article") {
                  $pos_article = NULL;
                  if (strpos($titre, "article premier")) {
                    $article_n = 1;
                  } else {
                    $article_n = substr($titre, 0, 20);
                    $article_n = preg_replace("/[^0-9]/", "", $article_n);
                  }
                } elseif (strpos($titre, "a l'article")) {
                  // "a l'article"
                  $a_article = substr($titre, strpos($titre, "a l'article") + 1, 20);
                  $pos_article = "a";
                  if (strpos($a_article, "premier")) {
                    $article_n = 1;
                  } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                  }
                } elseif (strpos($titre, "apres l'article")) {
                  // "apres l'article"
                  $pos_article = "après";
                  $a_article = substr($titre, strpos($titre, "apres l'article") + 1, 25);
                  if (strpos($a_article, "premier")) {
                    $article_n = 1;
                  } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                  }
                } elseif (strpos($titre, "avant l'article")) {
                  // "avant l'article"
                  $pos_article = "avant";
                  $a_article = substr($titre, strpos($titre, "avant l'article") + 1, 25);
                  if (strpos($a_article, "premier")) {
                    $article_n = 1;
                  } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                  }
                } else {
                  $article_n = NULL;
                  $pos_article = NULL;
                }

                //variable "bister"
                if (strpos($titre, "bis")) {
                  // BIS
                  $b = substr($titre, strpos($titre, "bis") + -1, 9);
                  if (strpos($b, "bis AA")) {
                    $bister = "bis AA";
                  } elseif (strpos($b, "bis A ")) {
                    $bister = "bis A";
                  } elseif (strpos($b, "bis B ")) {
                    $bister = "bis B";
                  } elseif (strpos($b, "bis F ")) {
                    $bister = "bis F";
                  } elseif (strpos($b, "bis D ")) {
                    $bister = "bis D";
                  } elseif (strpos($b, "bis C")){
                    $bister = "bis C";
                  } elseif (strpos($b, "bis E")) {
                    $bister = "bis E";
                  } elseif (strpos($b, "bis")) {
                    $bister = "bis";
                  } else {
                    //$bister = "error".$b;
                    $bister = "error";
                  }
                } elseif (strpos($titre, " ter ")) {
                  // TER
                  $ter = substr($titre, strpos($titre, " ter ") + -1, 9);
                  if (strpos($ter, "ter B ")) {
                    $bister = "ter B";
                  } elseif (strpos($ter, "ter C ")) {
                    $bister = "ter C";
                  } elseif (strpos($ter, "ter A ")) {
                    $bister = "ter A";
                  } elseif (strpos($ter, "ter D ")) {
                    $bister = "ter D";
                  } elseif (strpos($ter, "ter B")) {
                    $bister = "ter B";
                  } elseif (strpos($ter, "ter")) {
                    $bister = "ter";
                  } else {
                    //$bister = "error".$ter;
                    $bister = "error";
                  }
                } else {
                  $bister = NULL;
                }

                ?>
                <tr>
                  <td><?= $uid ?></td>
                  <td><?= $num ?></td>
                  <td><?= $titre ?></td>
                  <td><?= $type_vote ?></td>
                  <td><?= $amdt_n ?></td>
                  <td><?= $article_n ?></td>
                  <td><?= $bister ?></td>
                  <td><?= $pos_article ?></td>
                </tr>
                <?php

                $type_vote = str_replace("'", "''", $type_vote);

                // INSER INTO DATABASE.
                try {
                  $sql = ("UPDATE votes_info SET
                    voteType = '$type_vote',
                    amdt = '$amdt_n',
                    article = '$article_n',
                    bister = '$bister',
                    posArticle = '$pos_article'
                    WHERE voteNumero = $num");
                  $stmt = $bdd->prepare($sql);
                  $stmt->execute();
                }
                catch(PDOException $e)
                {
                  echo $sql . "<br>" . $e->getMessage();
                }

              }
               ?>
            </tbody>
          </table>
        </div>
			</div>
		</div>
	</body>
</html>
