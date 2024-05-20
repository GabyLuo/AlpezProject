<?php

class uploadF
{

    /*public function uploadFile()
    {
        try {
            // $tx = $this->transactions->get();
        $files = BatchLoads::findFirst(
                [
                    'conditions' => 'status = :status:',
                    'bind'       => [
                        'status' => 'NUEVO',
                    ],
                    'limit'      => '50',
                    'order'      => 'created asc',
                ]
            );
        
        $fullPath = '';
        $Data = null;
        $upload_dir =$_SERVER["DOCUMENT_ROOT"].'../documents/';
        $auxid=null;

        if($files){
            // print_r($files->id);
            //foreach($files as $file){
               
                $fullPath = $upload_dir . $files->document_id;
                if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                    $Data = file_get_contents($fullPath) ;
                    //while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                    //                $csvData[] = $data;  
                    //}
                    fclose($handle);
                   
                }
                 
                
             //}
             $this->uploadFileBD($Data,$files->id);
            }

        

          } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }
    public function uploadFileBD($data,$id){
         try {
                    $BatchLoadsDetails = new BatchLoadsDetails();
                    $BatchLoadsDetails->id_batch = $id;
                    $BatchLoadsDetails->status =  'NUEVO';
                    $BatchLoadsDetails->data = $data;

                    if ($BatchLoadsDetails->create()) {
                        $content['result'] = true;
                        $content['message'] = Message::success('El Documento se registro correctamente.');
                        return $content;
                        // $tx->commit();
                        //print_r("si se pudo");
                    } else {
                        $content['result'] = false;
                        $content['error'] = Helpers::getErrors($BatchLoadsDetails);
                        $content['message'] = Message::error('El Documento no se se registro correctamente.');
                        return $content;
                        // $tx->rollback();
                        //print_r("no se pudo");
                    }
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }

    */
 public function uploadFile()
    {
        try {
            // $tx = $this->transactions->get();
        $files = BatchLoads::findFirst(
                [
                    'conditions' => 'status = :status: AND type_id = :type_id:',
                    'bind'       => [
                        'status' => 'NUEVO',
                        'type_id' => 1,
                    ],
                    'limit'      => '50',
                    'order'      => 'created asc',
                ]
            );
        
        $fullPath = '';
        $csvData = array();
        $upload_dir =$_SERVER["DOCUMENT_ROOT"].'../documents/';
        $auxid=null;

        if($files){
            // print_r($files->id);
            //foreach($files as $file){
               
                $fullPath = $upload_dir . $files->document_id;
                if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                    $Data = file_get_contents($fullPath) ;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                                    $csvData[] = $data;  
                    }
                    fclose($handle);
                   
                }
                 
                
             //}
             $this->uploadFileBD($csvData,$files->id);
            }

        

          } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }
    public function uploadFileBD($data,$id){
         try {
            $count=0;
            foreach($data as $file){
                if($count>0){
                $BatchLoadsDetails = new BatchLoadsDetails();
                $BatchLoadsDetails->id_batch = $id;
                $BatchLoadsDetails->status =  'NUEVO';
                $BatchLoadsDetails->code = $file[0];
                 if($file[2]!=""){
                $BatchLoadsDetails->precio_a = $file[2];
                 }
                 if($file[3]!=""){
                    $BatchLoadsDetails->precio_b = $file[3];
                 }
                 if($file[4]!=""){
                    $BatchLoadsDetails->precio_c = $file[4];
                 }
                 if($file[5]!=""){
                    $BatchLoadsDetails->precio_d = $file[5];
                 }
                 if($file[6]!=""){
                    $BatchLoadsDetails->precio_e = $file[6];
                 }
                
                
                
                
                
                if ($BatchLoadsDetails->create()) {
                    $content['result'] = true;
                        $content['message'] = Message::success('El Documento se registro correctamente.');
                        // return $content;
                        // $tx->commit();
                        //print_r("si se pudo");
                    } else {
                        $content['result'] = false;
                        $content['error'] = Helpers::getErrors($BatchLoadsDetails);
                        $content['message'] = Message::error('El Documento no se se registro correctamente.');
                        // return $content;
                        // $tx->rollback();
                        //print_r("no se pudo");
                    }
                }

                $count++;

            }
            if($count == count($data)){
                $BatchLoads = BatchLoads::findFirst($id);

                if ($BatchLoads) {
                    $BatchLoads->status = 'CARGADO';

                    if ($BatchLoads->update()) {
                         $this->content['result'] = true;
                        // $this->content['message'] = Message::success('El precio ha sido eliminado.');
                        //$tx->commit();
                    }
                } else {
                    $this->content['message'] = Message::error('El precio no existe.');
                }

            }
            return $content;

                    /*$BatchLoadsDetails = new BatchLoadsDetails();
                    $BatchLoadsDetails->id_batch = $id;
                    $BatchLoadsDetails->status =  'NUEVO';
                    $BatchLoadsDetails->data = $data;

                    if ($BatchLoadsDetails->create()) {
                        $content['result'] = true;
                        $content['message'] = Message::success('El Documento se registro correctamente.');
                        return $content;
                        // $tx->commit();
                        //print_r("si se pudo");
                    } else {
                        $content['result'] = false;
                        $content['error'] = Helpers::getErrors($BatchLoadsDetails);
                        $content['message'] = Message::error('El Documento no se se registro correctamente.');
                        return $content;
                        // $tx->rollback();
                        //print_r("no se pudo");
                    }*/
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }


    /*private function setLog($message){
        $info = new Logs ();

        $info->account_id = 1;
        $info->level_id = 3;
        $info->message = $message;
    
        $info->create();
    }*/

}
