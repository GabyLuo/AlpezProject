<?php

use Phalcon\Cli\Task;
use Phalcon\Mvc\Dispatcher;

class MainTask extends Phalcon\CLI\Task
{
   
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function uploadFileAction()
    {
        $uploadF = new UploadF();
        $result = $uploadF->uploadFile();

    }

    public function revisarTimbradoAction()
    {
        $timbrado = new RevisaTimbrado();
        $result = $timbrado->revisarTimbrado();

    }

    public function revisarPagosAction()
    {
        $timbrado = new RevisaTimbrado();
        $result = $timbrado->revisarPagos();

    }

}
