<?php

  include 'bdd-connexion.php';
  include "lib/simplehtmldom_1_9/simple_html_dom.php";

  // 1. Create the database if does not exist
  $bdd->query("CREATE TABLE IF NOT EXISTS `exposes` (
      `id` INT NOT NULL AUTO_INCREMENT ,
      `legislature` INT(5) NOT NULL ,
      `voteNumero` INT(5) NOT NULL ,
      `exposeOriginal` TEXT NULL DEFAULT NULL ,
      `exposeSummary` TEXT NULL DEFAULT NULL ,
      `exposeSummaryPublished` TEXT NULL DEFAULT NULL ,
      `dateMaj` datetime default current_timestamp  ,
      PRIMARY KEY (`id`) ,
      INDEX `legislature_idx` (`legislature`) ,
      INDEX `voteNumero_idx` (`voteNumero`)
    ) ENGINE = MyISAM;
  ");

  // 1. Get the votes
  $sql = $bdd->query('SELECT vi.*
    FROM votes_info vi
    LEFT JOIN exposes e ON vi.legislature = e.legislature AND vi.voteNumero = e.voteNumero
    WHERE vi.legislature = 16 AND vi.voteType = "amendement" AND e.id IS NULL
    ORDER BY RAND()
    LIMIT 1');

  while ($vote = $sql->fetch()) {
    $voteNumero = $vote['voteNumero'];
    $legislature = $vote['legislature'];

    echo $voteNumero;

    $html = file_get_html("https://datan.fr/votes/legislature-" . $legislature . "/vote_" . $voteNumero);
    $exposeMotifs = $html->find('#exposeMotifs', 0);

    if ($exposeMotifs) {
      $exposeMotifs = strip_tags($exposeMotifs);
      $exposeMotifs = str_replace('"', "", $exposeMotifs);
      $exposeMotifs = str_replace("'", "", $exposeMotifs);

      // CURL

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"model\": \"text-davinci-002\", \"prompt\": \"Réécrire ce texte en 100 mots : " . $exposeMotifs . "\", \"temperature\": 0.7, \"max_tokens\": 256, \"top_p\": 1, \"frequency_penalty\": 0.5, \"presence_penalty\": 0.55}");

      $headers = array();
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Authorization: Bearer ' . $_SERVER['OPEN_AI_KEY'];
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $result = curl_exec($ch);

      if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);

      } else {
        echo "Open AI worked \n";
        $response = json_decode($result, TRUE);

        $text = $response['choices'][0]['text'];

        // Insert into database
        $insert = $bdd->prepare('INSERT INTO exposes (legislature, voteNumero, exposeOriginal, exposeSummary) VALUES (:legislature, :voteNumero, :exposeOriginal, :expose)');
        $insert->execute(array(
          'legislature' => $legislature,
          'voteNumero' => $voteNumero,
          'exposeOriginal' => $exposeMotifs,
          'expose' => $text,
        ));

      }

      curl_close($ch);


    }

  }

?>
