<?php

use Phalcon\Mvc\Controller;

class MovementDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function create ()
    {
        try {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();
 //print_r($request);
 //exit();

            $product = Products::findFirst($request['product_id']);
            if ($product->active) {
                $movementDetail = new MovementDetails();
                $movementDetail->setTransaction($tx);
                // Debo de verificar la cantidad de productos que hay antes de meterlos
                $movementDetail->movement_id = $request['movement_id'];
                $movementDetail->product_id = $request['product_id'];
                $movementDetail->qty = $request['qty'];
                $movementDetail->unit_price = $request['unit_price'];


                if ($movementDetail->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El detalle del movimiento ha sido creado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($movementDetail);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el detalle del movimiento.');
                    // $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('El producto está inactivo.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getMovementDetail ($id)
    {
        $content = $this->content;
        $movementDetail = null;
        if (is_numeric($id)) {
            $sql = "SELECT md.id, md.movement_id, md.product_id, p.name AS product, p.unit_id, u.name AS unit, md.qty, md.unit_price as cost
                    FROM wms_movement_details AS md
                    INNER JOIN wms_products AS p
                    ON md.product_id = p.id
                    INNER JOIN wms_units AS u
                    ON p.unit_id = u.id
                    WHERE md.id = $id;";

            $data = $this->db->query($sql);
            $movementDetail = $data->fetch();
        }
        $content['movement_detail'] = $movementDetail;

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function update ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $movementDetail = MovementDetails::findFirst($id);

                $productId = (isset($request['product_id']) && is_numeric($request['product_id'])) ? $request['product_id'] : $movementDetail->product_id;

                $product = Products::findFirst($productId);
                if ($product->active) {
                    $request = $this->request->getPut();

                    if ($movementDetail) {
                        // if ($movementDetail->bale_id) {
                            if ($this->userHasPermission()) {
                                $movementDetail->setTransaction($tx);
                                if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                                    $movementDetail->product_id = $request['product_id'];
                                }
                                if (isset($request['qty']) && is_numeric($request['qty'])) {
                                    $movementDetail->qty = $request['qty'];
                                }
                                if (isset($request['cost']) && is_numeric($request['cost'])) {
                                    $movementDetail->unit_price = $request['cost'];
                                }

                                if ($movementDetail->update()) {
                                    $this->content['result'] = true;
                                    $this->content['movementDetail'] = $movementDetail;
                                    $this->content['message'] = Message::success('El detalle de movimiento ha sido modificado.');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($movementDetail);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el detalle de movimiento.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['result'] = false;
                                $this->content['message'] = Message::success('No cuenta con los permisos necesarios.');
                            }
                        // } else {
                        //     $movementDetail->setTransaction($tx);
                        //     if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                        //         $movementDetail->product_id = $request['product_id'];
                        //     }
                        //     if (isset($request['qty']) && is_numeric($request['qty'])) {
                        //         $movementDetail->qty = $request['qty'];
                        //     }

                        //     if ($movementDetail->update()) {
                        //         $this->content['result'] = true;
                        //         $this->content['movementDetail'] = $movementDetail;
                        //         $this->content['message'] = Message::success('El detalle de movimiento ha sido modificado.');
                        //         $tx->commit();
                        //     } else {
                        //         $this->content['error'] = Helpers::getErrors($movementDetail);
                        //         $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el detalle de movimiento.');
                        //         $tx->rollback();
                        //     }
                        // }
                    }
                } else {
                    $this->content['message'] = Message::error('El producto está inactivo.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $movementDetail = MovementDetails::findFirst($id);

                if ($movementDetail) {
                    $movementDetail->setTransaction($tx);

                    if ($movementDetail->delete()) {
                        $this->content['result'] = true;
                        $this->content['movementDetail'] = $movementDetail;
                        $this->content['message'] = Message::success('El detalle de movimiento ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($order);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle de movimiento.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El detalle de movimiento no existe.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 20 OR role_id = 22)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    public function getCsvProducts () {
        $content = $this->content; 

        $sql ="SELECT p.name, p.id,
        concat(c.code,'-',l.code,'-',p.code) as code,
		l.name as line,
		c.name as category
        FROM wms_products AS p 
        INNER JOIN wms_lines as l
        ON l.id = p.line_id 
        INNER JOIN wms_categories as c
        ON c.id = l.category_id
		where p.active = true
        ORDER BY p.code";
        $products = $this->db->query($sql)->fetchAll();       
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, [utf8_decode('CÓDIGO'),utf8_decode('CATEGORÍA'),utf8_decode('LINEA'),utf8_decode('NOMBRE'),utf8_decode('CANTIDAD'),utf8_decode('PRECIO')], ',');
        if (count($products)) {
            foreach ($products as $pr) {
                fputcsv($fp, [
                    $pr['code'],
                    utf8_decode($pr['category']),
                    utf8_decode($pr['line']),
                    utf8_decode($pr['name']),
                    '',
                    ''
                ], ',');
            }
            $content['result'] = 'success';
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Productos-' . date('d/m/Y') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    function exportCsvv ($path, $data) {
        // $content = $this->content;
            $fp = fopen($path, 'w');
    
            fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
    
            //fputcsv($fp, array('CONCATENACION'), ',');
            //var_dump($data);
            $mydata = $data;
            
            foreach ($mydata as $key => $value) {
                /* echo "<pre>";
                var_dump($value); */
                fputcsv($fp, [$value], ',');
            }
    
            rewind($fp);
            fclose($fp);
    }

    private function getIdUser () {
        $validUser = Auth::getUserData($this->config);
        /* $sql = "SELECT user_id
                    FROM sys_user_roles
                    AND user_id = $validUser->id
                    LIMIT 1;";
        $permission = $this->db->query($sql)->fetch(); */
        return $validUser->id;
    }
    public function uploadFileop2 ($id) {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            //$upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/storage/';
            $upload_dir = dirname(__FILE__)  . '/../../public/assets/storage/';
            //var_dump($_SERVER["DOCUMENT_ROOT"]);
            /* if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            } */
            $fullPath = '';
            $line = 0;
            $mistake = true;
            $mydata = [];
            
            //Se obtienen los productos y movimientos para no consultar directamente en la bd
            $getProduct = "SELECT concat(c.code,'-',l.code,'-',wms_products.code) as code, wms_products.id from wms_products 
            INNER JOIN wms_lines as l
            ON l.id = wms_products.line_id 
            INNER JOIN wms_categories as c
            ON c.id = l.category_id
            where  active=true order by code asc";
            $getMovements = "SELECT movement_id, product_id from wms_movement_details where movement_id = $id";
            $myMovements =  $this->db->query($getMovements)->fetchAll();
            $myProducts =  $this->db->query($getProduct)->fetchAll();
            $getIdUser = $this->getIdUser();
            $created_by = $getIdUser;
            $canDoInsert= true;
            $productsRejected = 0;
            $productsRejected2 = 0;
            $contador = 0;
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    //$this->content['array'] = $csvData;
                    $it = [];
                    foreach ($csvData as $k){
                        
                        if (!empty($k[4]) || ($k[5] == "0" || empty($k[5]) || $k[4] =="0")) {
                            $contador++;
                            // La linea 1 representa el encabezado y por eso lo salta
                            if ($line != 0) {
                                if ((is_numeric($k[4]) && is_numeric($k[5])) || empty($k[5])) {
                                    //$val = preg_replace('/[A-Z\-]+/', '', $k[0]);
                                    array_push($it,$k);
                                    if (!is_null($k[0])) {
                                        
                                        foreach($myProducts as $valueProducts){
                                            
                                            //Revisa que el producto exista
                                            
                                            if ($valueProducts['code'] == $k[0]) { 
                                                
                                                //recorre los movimientos para ver si ya esta el producto en ese movimiento
                                                foreach ($myMovements as $valueMovements) {
                                                    //Revisa que el movimiento no exista en caso de existir no registra el producto
                                                    if ($valueMovements['movement_id'] == $id && $valueMovements['product_id'] == intval($valueProducts['id'])){
                                                        $canDoInsert = false;
                                                        break;
                                                    }else {
                                                        $canDoInsert = true;
                                                    }
                                                }
                                                if ($canDoInsert) {
                                                    //Se guardan los trozos en un array de los valores que se van a registrar
                                                    //ejemplo: "(99,33834,120,14,1)","(99,20335,72,8,1)","(99,20336,0,0,1)"
                                                    array_push($mydata,'('.$id.','.$valueProducts['id'].','.floatval($k[4]).','.floatval($k[5]).','.$created_by.')');
                                                }
                                            } 
                                        }
                                    }
                                }else {
                                    // revisa que no haya valores que no sean numericos
                                    $mistake = false;
                                    break;
                                }
                            }
                            $line ++;
                        }
                    }
                    //Sepra por comas el arreglo que guarda los registros del movimiento, id del producto, cantidad, etc.
                    $separarPorComas = implode(',',$mydata);
                    //La estructura quedaria asi 
                    //INSERT INTO wms_movement_details (movement_id, product_id,qty,unit_price,created_by) VALUES (99,33834,120,14,1)","(99,20335,72,8,1)","(99,20336,0,0,1)"...
                    $query = 'INSERT INTO wms_movement_details (movement_id, product_id,qty,unit_price,created_by) VALUES '.$separarPorComas;
                    
                    
                    //$this->exportCsvv($fullPath, $mydata);
                    /* $executeSql = "COPY wms_movement_details (movement_id, product_id, qty,unit_price,created,created_by)  
                    from 'localhost/htdocs/public/assets/storage/'.$fileName delimiter ',' csv header;"; */
                    $this->content['salida'] = $it;
                    if ($mistake) {
                        $this->db->query($query)->fetch();
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                    } else {
                        $this->content['message'] = Message::success('Error en la linea'.$line.' del documento CSV');
                        $this->content['result'] = false;
                    }
                } else {
                    //$this->content['line'] = Message::success('Error en la linea.'.$line.' del documento CSV2');
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    //Esta funciona
    public function uploadFile ($id)
    {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            //$upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/storage/';
            $upload_dir = dirname(__FILE__)  . '/../../public/assets/storage/';
            //var_dump($_SERVER["DOCUMENT_ROOT"]);
            /* if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            } */
            $fullPath = '';
            $line = 0;
            $mistake = true;
            $mydata = [];
            
            //Se obtienen los productos y movimientos para no consultar directamente en la bd
            $getProduct = "SELECT concat(c.code,'-',l.code,'-',wms_products.code) as code, wms_products.id from wms_products 
            INNER JOIN wms_lines as l
            ON l.id = wms_products.line_id 
            INNER JOIN wms_categories as c
            ON c.id = l.category_id
            where  active=true order by code asc";
            $getMovements = "SELECT movement_id, product_id from wms_movement_details where movement_id = $id";
            $myMovements =  $this->db->query($getMovements)->fetchAll();
            $myProducts =  $this->db->query($getProduct)->fetchAll();
            $getIdUser = $this->getIdUser();
            $created_by = $getIdUser;
            $canDoInsert= true;
            $productsRejected = 0;
            $productsRejected2 = 0;
            $contador = 0;
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    //$this->content['array'] = $csvData;
                    $it = [];
                    foreach ($csvData as $k){
                        $contador++;
                        if (!empty($k[4]) && !empty($k[5]) || ($k[5] == "0" || empty($k[5]) || $k[4] =="0")) {
                            // La linea 1 representa el encabezado y por eso lo salta
                            if ($line != 0) {
                                if ((is_numeric($k[4]) && is_numeric($k[5])) || empty($k[5])) {
                                    //$val = preg_replace('/[A-Z\-]+/', '', $k[0]);
                                    array_push($it,$k);
                                    if (!is_null($k[0])) {
                                        
                                        foreach($myProducts as $valueProducts){
                                            
                                            //Revisa que el producto exista
                                            if ($valueProducts['code'] == $k[0]) { 
                                                
                                                //recorre los movimientos para ver si ya esta el producto en ese movimiento
                                                foreach ($myMovements as $valueMovements) {
                                                    //Revisa que el movimiento no exista en caso de existir no registra el producto
                                                    if ($valueMovements['movement_id'] == $id && $valueMovements['product_id'] == intval($valueProducts['id'])){
                                                        $canDoInsert = false;
                                                        break;
                                                    }else {
                                                        $canDoInsert = true;
                                                    }
                                                }
                                                if ($canDoInsert) {
                                                    //Se guardan los trozos en un array de los valores que se van a registrar
                                                    //ejemplo: "(99,33834,120,14,1)","(99,20335,72,8,1)","(99,20336,0,0,1)"
                                                    array_push($mydata,'('.$id.','.$valueProducts['id'].','.floatval($k[4]).','.floatval($k[5]).','.$created_by.')');
                                                }
                                            } 
                                        }
                                    }
                                }else {
                                    // revisa que no haya valores que no sean numericos
                                    $mistake = false;
                                    break;
                                }
                            }
                            $line ++;
                        }
                    }
                    //Sepra por comas el arreglo que guarda los registros del movimiento, id del producto, cantidad, etc.
                    $separarPorComas = implode(',',$mydata);
                    //La estructura quedaria asi 
                    //INSERT INTO wms_movement_details (movement_id, product_id,qty,unit_price,created_by) VALUES (99,33834,120,14,1)","(99,20335,72,8,1)","(99,20336,0,0,1)"...
                    $query = 'INSERT INTO wms_movement_details (movement_id, product_id,qty,unit_price,created_by) VALUES '.$separarPorComas;
                    
                    
                    //$this->exportCsvv($fullPath, $mydata);
                    /* $executeSql = "COPY wms_movement_details (movement_id, product_id, qty,unit_price,created,created_by)  
                    from 'localhost/htdocs/public/assets/storage/'.$fileName delimiter ',' csv header;"; */
                    $this->content['salida'] = $it;
                    if ($mistake) {
                        $this->db->query($query)->fetch();
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                    } else {
                        $this->content['message'] = Message::success('Error en la linea'.$line.' del documento CSV');
                        $this->content['result'] = false;
                    }
                } else {
                    //$this->content['line'] = Message::success('Error en la linea.'.$line.' del documento CSV2');
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    /* public function uploadFile ($id)
    {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/storage/';
            //var_dump($_SERVER["DOCUMENT_ROOT"]);
            if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            }
            $fullPath = '';
            $line = 0;
            $mistake = true;
            $mydata = [];
            
            //Se obtienen los productos y movimientos para no consultar directamente en la bd
            $getProduct = "SELECT code, id from wms_products where  active=true";
            $getMovements = "SELECT movement_id, product_id from wms_movement_details where movement_id = $id";
            $myMovements =  $this->db->query($getMovements)->fetchAll();
            $myProducts =  $this->db->query($getProduct)->fetchAll();
            $getIdUser = $this->getIdUser();
            $created_by = $getIdUser;
            $canDoInsert= true;
            $productsRejected = 0;
            $productsRejected2 = 0;
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    //$this->content['array'] = $csvData;
                    $it = [];

                    foreach ($csvData as $k){
                        if ((!empty($k[4]))) {
                            $line ++;
                            // La linea 1 representa el encabezado y por eso lo salta
                            if ($line != 1) {
                                if ((is_numeric($k[4]) && is_numeric($k[5]))) {
                                    $val = preg_replace('/[A-Z\-]+/', '', $k[0]);
                                    array_push($it,$k);
                                    if (!is_null($val) && strlen($val) > 0) {
                                        //Recorre la lista de productos para ver si existen
                                        //$product = Products::findFirst("code = '".$val."'");
                                        $product = "SELECT code, id from wms_products where code = '".$val."' and active=true order by code desc";
                                        $query = $this->db->query($product)->fetch();
                                        //var_dump($query['id']);
                                        //Revisa que el producto exista
                                        if ($query['id'] != null) {
                                            $idProd = intval($query['id']);
                                            //recorre los movimientos para ver si ya esta el producto en ese movimiento
                                            //$productMovement = MovementDetails::findFirst("movement_id = $id and product_id = $idProd");
                                            $productMovement = "SELECT id from wms_movement_details where movement_id = $id and product_id = $idProd";
                                            $query2 = $this->db->query($productMovement)->fetch();
                                            //Revisa que el movimiento no exista en caso de existir no registra el producto
                                            if ($query2['id'] != null) {
                                            }else{
                                                array_push($mydata,'('.$id.','.$idProd.','.$k[4].','.$k[5].','.$created_by.')');
                                            }
                                        }
                                        
                                    }
                                }else {
                                    // revisa que no haya valores que no sean numericos
                                    $mistake = false;
                                    break;
                                }
                            }
                        }
                    }
                    $separarPorComas = implode(',',$mydata);
                    $query = 'INSERT INTO wms_movement_details (movement_id, product_id,qty,unit_price,created_by) VALUES '.$separarPorComas;
                    //$this->exportCsvv($fullPath, $productsRejected);
                    $this->content['salida'] = $it;
                    if ($mistake) {
                        $this->db->query($query)->fetch();
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                    } else {
                        $this->content['message'] = Message::success('Error en la linea'.$line.' del documento CSV');
                        $this->content['result'] = false;
                    }
                } else {
                    //$this->content['line'] = Message::success('Error en la linea.'.$line.' del documento CSV2');
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    } */
    //Forma reciente
    /* public function uploadFile ($id)
    {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/storage/';
            if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            }
            $fullPath = '';
            $line = 0;
            $mistake = true;
            $mydata = [];
            
            //Se obtienen los productos y movimientos para no consultar directamente en la bd
            $getProduct = "SELECT code, id from wms_products where  active=true";
            $getMovements = "SELECT movement_id, product_id from wms_movement_details where movement_id = $id";
            $myMovements =  $this->db->query($getMovements)->fetchAll();
            $myProducts =  $this->db->query($getProduct)->fetchAll();
            $mystring = "";
            $canDoInsert= true;
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    //$this->content['array'] = $csvData;
                    $it = [];

                    foreach ($csvData as $k){
                        if ((!empty($k[3]) || $k[3] == "0")) {
                            $line ++;
                            // La linea 1 representa el encabezado y por eso lo salta
                            if ($line != 1) {
                                if ((is_numeric($k[3]) && is_numeric($k[4]))) {
                                    $val = preg_replace('/[A-Z\-]+/', '', $k[0]);
                                    array_push($it,$k);
                                    //Recorre la lista de productos para ver si existen
                                    foreach($myProducts as $valueProducts){
                                        //Revisa que el producto exista
                                        if ($valueProducts['code'] == $val) { 
                                            //recorre los movimientos para ver si ya esta el producto en ese movimiento
                                            foreach ($myMovements as $valueMovements) {
                                                //Revisa que el movimiento no exista en caso de existir no registra el producto
                                                if ($valueMovements['movement_id'] == $id && $valueMovements['product_id'] == intval($valueProducts['id'])){
                                                    $canDoInsert = false;
                                                    break;
                                                }else {
                                                    $canDoInsert = true;
                                                }
                                            }
                                            //Verifica si ya existe el producto en el movimiento, en caso de que no exista lo registra
                                            if ($canDoInsert) {
                                                $movementDetail = new MovementDetails();
                                                $movementDetail->setTransaction($tx);
                                                $movementDetail->movement_id = $id;
                                                $movementDetail->product_id = intval($valueProducts['id']);
                                                $movementDetail->qty = $k[3];
                                                if (!empty($k[3]) || $k[3] == "0") {
                                                    $movementDetail->unit_price = $k[4];
                                                }
                                                $movementDetail->create();
                                            }
                                            
                                        }
                                    }
                                }else {
                                    // revisa que no haya valores que no sean numericos
                                    $mistake = false;
                                    break;
                                }
                            }
                        }
                    }
                    $this->content['salida'] = $it;
                    if ($mistake) {
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                    } else {
                        $this->content['message'] = Message::success('Error en la linea'.$line.' del documento CSV');
                        $this->content['result'] = false;
                    }
                } else {
                    //$this->content['line'] = Message::success('Error en la linea.'.$line.' del documento CSV2');
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    } */

    //Forma antigua
   /*  public function uploadFile ($id)
    {
        //  
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/storage/';
            if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            }
            $fullPath = '';
            $line = 0;
            $mistake = true;
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    //$this->content['array'] = $csvData;
                    $it = [];
                    foreach ($csvData as $k){
                        if ((!empty($k[3]) || $k[3] == "0")) {
                            $line ++;
                            if ($line != 1) {
                                if ((is_numeric($k[3]) && is_numeric($k[4]))) {
                                    $val = preg_replace('/[A-Z\-]+/', '', $k[0]);
                                    array_push($it,$k);
                                    $product = Products::findFirst("code = '".$val."'");
                                    if ($product) { 
                                        $productMovement = MovementDetails::findFirst("movement_id = $id and product_id = $product->id");
                                        if ($productMovement) {

                                        }else{
                                        $movementDetail = new MovementDetails();
                                        $movementDetail->setTransaction($tx);
                                        $movementDetail->movement_id = $id;
                                        $movementDetail->product_id = $product->id;
                                        $movementDetail->qty = $k[3];
                                        if (!empty($k[3]) || $k[3] == "0") {
                                            $movementDetail->unit_price = $k[4];
                                        }
                                        $movementDetail->create();
                                        }
                                    }
                                }else {
                                    $mistake = false;
                                    break;
                                }
                            }
                        }
                    }
                    $this->content['salida'] = $it;
                        
                    //fclose($handle);
                    if ($mistake) {
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                    } else {
                        $this->content['message'] = Message::success('Error en la linea.'.$line.' del documento CSV');
                        $this->content['result'] = false;
                    }
                } else {
                    $this->content['line'] = Message::success('Error en la linea.'.$line.' del documento CSV2');
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    } */
}