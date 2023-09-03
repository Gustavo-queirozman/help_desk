<?php

namespace App\Services;

use Exception;
use Webklex\IMAP\Facades\Client;

class GetMail
{

    public function __construct()
    {
        $client = Client::account('default');
        $client->connect();
        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->cc('chamadosti@unimednet.com.br')->on('2023-07-25')->get();

        foreach ($messages as $message) {
            echo $title = "<h1>" . $message->getSubject() . '</h1><br />';

            echo $bodyMail = preg_replace('/<img[^>]*>/', '', $message->getHTMLBody());


            $hostname = '{imap-upmail.sighost.com.br:993/imap/ssl}INBOX';
            $username = env('IMAP_USERNAME');
            $password = env('IMAP_PASSWORD');
            $mailbox = imap_open($hostname, $username, $password);
            
            $msgno = imap_msgno($mailbox, $message->uid);
            // Obtém a estrutura da mensagem
            $structure = imap_fetchstructure($mailbox, $msgno);

            // Verifica se há anexos na mensagem


            #pegar arquivos
            $attachments = array();
            if (isset($structure->parts) && count($structure->parts)) {

                for ($i = 0; $i < count($structure->parts); $i++) {

                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );


                    if ($structure->parts[$i]->ifdparameters) {

                        foreach ($structure->parts[$i]->dparameters as $object) {
                            if (strtolower($object->attribute) == 'filename') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }


                    if ($structure->parts[$i]->ifparameters) {
                        foreach ($structure->parts[$i]->parameters as $object) {

                            if (strtolower($object->attribute) == 'name') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }

                    #extração de imagens
                    if (!empty($attachments[$i]['name'])) {
                        $attachments[$i]['attachment'] = imap_fetchbody($mailbox, $msgno, $i + 1);

                        if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        } elseif ($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                    if (isset($attachments[$i]['name'])) {

                        $path = storage_path('/app/public/files');
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $imgPath = $path . '/' .  time() . '_' . $attachments[$i]['name'];

                        try {
                            file_put_contents($imgPath, $attachments[$i]['attachment']);
                        } catch (Exception $e) {
                            //echo $e;
                        }
                    }
                }
            }


            /*
            //Move the current Message to 'INBOX.read'
            if ($message->move('INBOX.read') == true) {
                echo 'Message has ben moved';
            } else {
                echo 'Message could not be moved';
            }*/
        }
    }
}
