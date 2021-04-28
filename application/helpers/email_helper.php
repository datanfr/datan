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
    'ContactsLists' => [
      [
        'Action' => "addforce",
        'ListID' => $list
      ]
    ]
  ];
  $mj->post(Resources::$ContactManagecontactslists, ['id' => $email, 'body' => $body]);
}

function createContact($email){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'],true,['version' => 'v3']);
  $body = [
    'Email' => $email
  ];
  $mj->post(Resources::$Contact, ['body' => $body]);
}

function getContactId($id){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3']);
  return $mj->get(Resources::$Contactdata, ['id' => $id]);
}

function getContactLists($id){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3']);
  return $mj->get(Resources::$ContactGetcontactslists, ['id' => $id]);
}

function removeContactlist($id, $list){
  $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3']);
  $body = [
    'ContactsLists' => [
      [
        'Action' => "remove",
        'ListID' => $list
      ]
    ]
  ];
  $response = $mj->post(Resources::$ContactManagecontactslists, ['id' => $id, 'body' => $body]);
}
