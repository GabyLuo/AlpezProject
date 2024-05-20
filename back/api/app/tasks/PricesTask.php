<?php

use Phalcon\Cli\Task;
use Phalcon\Mvc\Dispatcher;

class PricesTask extends Phalcon\CLI\Task
{
   
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    /*public function mainAction()
    {
        echo 'This is the default task and the default action' . PHP_EOL;
    }*/

    /*public function cargaAction()
    {
        $upload = new Uploadcsv();
        $result = $upload->uploadFile();
    }
    */
    public function uploadPricesAction()
    {
        // echo("havsja ");
        $uploadF = new prices();
        $result = $uploadF->uploadPrices();
        print_r($result['message']);
        /*if($files){
            print_r($files[0]);
        }else {
            echo('no hay archivos ');
        }*/
       //  echo('no hay archivos ');
    }



}
