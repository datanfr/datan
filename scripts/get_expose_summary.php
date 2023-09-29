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


    //$html = file_get_html("https://datan.fr/votes/legislature-" . $legislature . "/vote_" . $voteNumero);
    //$exposeMotifs = $html->find('#exposeMotifs', 0);

    $exposeMotifs = "Les auteurs de cet amendement souhaitent supprimer la mise en œuvre d'une redevance de 30 euros pour les candidats, notamment ultramarins, de l’examen annuel de capacité professionnelle pour l’accès à la profession de transporteur routier de marchandises, de personnes et de commissionnaires. Instituer une nouvelle redevance dans un contexte d'inflation n'est pas envisageable, en particulier face à la cherté de la vie en outre-mer.

La ratification d'un décret ne laissant pas de marge de manœuvre aux parlementaires pour faire varier les conditions dudit décret, il est proposé de supprimer intégralement l'article autorisant la ratification.

L'objet est avant tout de protéger les potentiels candidats de cet examen de cette redevance supplémentaire.";

echo $exposeMotifs;

    if ($exposeMotifs) {
      $exposeMotifs = strip_tags($exposeMotifs);
      $exposeMotifs = str_replace('"', "", $exposeMotifs);
      $exposeMotifs = str_replace("'", "", $exposeMotifs);

      // CURL

      $request_body = [
        "messages" => [
          [
            "role" => "assistant",
            "content" => "Le texte que tu vas lire est l'exposé des motifs d'un amendement écrit par des députés. Peux-tu résumer ce texte en 150 mots maximum : " . $exposeMotifs
            ]
          ],
        "model" => "gpt-3.5-turbo",
        "max_tokens" => 100,
        "temperature" => 0.7,
        "top_p" => 1,
        "presence_penalty" => 0.75,
        "frequency_penalty"=> 0.75,
        "stream" => false,
      ];

      var_dump($request_body);

      $postfields = json_encode($request_body);
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postfields,
        CURLOPT_HTTPHEADER => [
          'Content-Type: application/json',
          'Authorization: Bearer ' . $_SERVER['OPEN_AI_KEY']
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
        echo "Error #: " . $err;
      } else {
        echo "Open AI worked \n";
        var_dump($response);
        $result = json_decode($response, TRUE);
        var_dump($result);
        $text = $result['choices'][0]['message']['content'];
        echo $text;

        // Insert into database
        $insert = $bdd->prepare('INSERT INTO exposes (legislature, voteNumero, exposeOriginal, exposeSummary) VALUES (:legislature, :voteNumero, :exposeOriginal, :expose)');
        //$insert->execute(array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'exposeOriginal' => $exposeMotifs, 'expose' => $text));

      }

    }

  }

?>
