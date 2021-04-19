<?php
  require 'C:/wamp64/www/datan/vendor/autoload.php';
  use \Mailjet\Resources;
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'],true,['version' => 'v3.1']);
  $body = [
    'Messages' => [
      [
        'From' => [
          'Email' => "awenig@datan.fr",
          'Name' => "Awenig"
        ],
        'To' => [
          [
            'Email' => "awenig@datan.fr",
            'Name' => "Awenig"
          ]
        ],
        'Subject' => "Greetings from Mailjet.",
        'TextPart' => "My first Mailjet email",
        'HTMLPart' => "<h3>Dear passenger 1, welcome to <a href='https://www.mailjet.com/'>Mailjet</a>!</h3><br />May the delivery force be with you!",
        'CustomID' => "AppGettingStartedTest"
      ]
    ]
  ];
  $response = $mj->post(Resources::$Email, ['body' => $body]);
  $response->success() && var_dump($response->getData());
?>
