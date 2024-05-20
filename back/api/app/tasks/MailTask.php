<?php

use Phalcon\Cli\Task;
use Phalcon\Mvc\Dispatcher;

class MailTask extends Phalcon\Cli\Task {
    public $content = ['result' => false, 'message' =>['title' => 'Error!', 'content' => 'Internal Server Error']];

        /* @param array */

    public function mainAction()
    {
        echo 'Frijolees' . PHP_EOL;
    }
    
    public function sendAction () {
        echo "Me ejecute chido";
        $mail = new AuthoSendMail;
        $result = $mail->sendPdfToProvider();
    }
}