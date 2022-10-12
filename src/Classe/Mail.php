<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;


class Mail 
{
    private $api_key = 'c0d13ee8991370efb95c6fdef63fb48e';
    private $api_key_secrete = '3b40d66d56c3e0d8af6e1954f4b0cbce';

    public function send ($to_email, $to_name, $subject, $content) 
    {
        $mj = new \Mailjet\Client($this->api_key, $this->api_key_secrete,true,['version' => 'v3.1']);
        
        $body = [
            'Messages' => [
                [   
                    'From' => [
                        'Email' => "mariaetlamode@hotmail.com",
                        'Name' => "Maria et la Mode"
                    ],
                    'To' => [
                    [
                        'Email' => $to_email,
                        'Name' => $to_name
                    ]
                    ],
                    'TemplateID' => 3529157,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
    ];
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    $response->success();
    }
}
