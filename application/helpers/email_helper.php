<?php

use \Mailjet\Resources;

function sendMail($subject, $template, $to){
    $mj = new \Mailjet\Client($_SERVER['API_KEY_MAILJET'], $_SERVER['API_KEY_SECRETE_MAILJET'], true, ['version' => 'v3.1']);
    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => "no-reply@datan.fr",
                    'Name' => "Datan"
                ],
                'To' => [
                    [
                        'Email' => $to,
                    ]
                ],
                'Subject' => $subject,
                'HTMLPart' => $template
            ]
        ]
    ];
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    $response->success();
}
