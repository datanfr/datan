<?php

use \Mailjet\Resources;

function sendMail($to, $subject, $templateHtml, $templateLanguage, $templateId, $variables){
    $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3.1']);
    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => "info@datan.fr",
                    'Name' => "Datan"
                ],
                'To' => [
                    [
                        'Email' => $to,
                    ]
                ],
                'Subject' => $subject,
                'HTMLPart' => $templateHtml,
                "TemplateLanguage" => $templateLanguage,
                "TemplateId" => $templateId,
                "Variables" => $variables
            ]
        ]
    ];
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    $response->success();
}

function sendContactList($email, $list){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3']);
  $body = [
    'ContactAlt' => $email,
    'ListID' => $list
  ];
  $response = $mj->post(Resources::$Listrecipient, ['body' => $body]);
  $response->success() && var_dump($response->getData());
}
