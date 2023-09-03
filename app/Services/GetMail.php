<?php

namespace App\Services;

use Exception;
use Webklex\IMAP\Facades\Client;

class GetMail
{

    public function __construct(){
        $client = Client::account('default');
        $client->connect();
        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->cc('chamadosti@unimednet.com.br')->on('2023-07-25')->get();

        foreach ($messages as $message) {
           echo $title = "<h1>" . $message->getSubject() . '</h1><br />';

            echo $bodyMail = preg_replace('/<img[^>]*>/', '', $message->getHTMLBody());
        }
    }

}
