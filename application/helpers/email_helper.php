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

function createContact($email){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3.1']);
  $body = [
    'IsExcludedFromCampaigns' => "true",
    'Name' => $email,
    'Email' => $email
  ];
  $response = $mj->post(Resources::$Contact, ['body' => $body]);
  var_dump($response->getStatus());
  $response->success() && var_dump($response->getData());
}

function getContactId(){

}

function sendContactList($contactId, $listId){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3.1']);
  $body = [
    'ContactID' => $contactId,
    'ListID' => $listId
  ];
  $response = $mj->post(Resources::$Listrecipient, ['body' => $body]);
  var_dump($response->getStatus());
  $response->success() && var_dump($response->getData());
}
