<?php

use Phalcon\Mvc\Controller;

class ProductsPricesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getProductsPrices ($pt = 0)
    {
        $content = $this->content;
        if ($this->userHasCompleteGetPermission()) {
            $content['result'] = true;
            $content['productsPrices'] = ProductsPrices::find();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getProductPrices ($id)
    {
        $content = $this->content;
        if ($this->userHasCompleteGetPermission()) {
            $content['result'] = true;
            $content['productsPrices'] = ProductsPrices::find("product_id = $id");
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getLevelProductsPrices ($level = null)
    {
        $content = $this->content;
        if ($this->userHasCompleteGetPermission()) {
            if (!is_null($level)) {
                $content['result'] = true;
                $content['productsPrices'] = ProductsPrices::find("price_level = '$level'");
            } else {
                $content['message'] = Message::error('No se ha recibido el nivel o el nivel recibido es inválido.');
            }
        } elseif ($this->userIsClient()) {
            $validUser = Auth::getUserData($this->config);
            $customerUser = CustomerUsers::findFirst("user_id = $validUser->id");
            if ($customerUser) {
                $customer = Customers::findFirst($customerUser->customer_id);
                $content['result'] = true;
                $content['productsPrices'] = ProductsPrices::find("price_level = '$customer->price_list'");
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ()
    {
        try {
            $request = $this->request->getPost();

            if (is_numeric($request['product_id']) && is_numeric($request['price']) && strlen($request['price_level']) == 1) {
                if ($this->userHasPermission()) {
                    if (isset($request['product_id']) && is_numeric($request['product_id']) && isset($request['price_level']) && strlen($request['price_level']) == 1 && isset($request['price']) && is_numeric($request['price'])) {
                        $productPrice = ProductsPrices::find("product_id = ".$request['product_id']." AND price_level = ".$request['price_level']);
                        if ($productPrice) {
                            $this->content['message'] = Message::error('Ya se encuentra registrado un precio de nivel '.$request['price_level'].' para el producto.');
                        } else {
                            $productPrice = new ProductsPrices();
                            $productPrice->product_id = $request['product_id'];
                            $productPrice->price_level = $request['price_level'];
                            $productPrice->price = $request['price'];
                            if ($productPrice->create()) {
                                $this->content['product-price'] = $productPrice;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El precio ha sido registrado correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($productPrice);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el precio.');
                                // $tx->rollback();
                            }
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPut();
                if (is_numeric($id)) {
                    $tx = $this->transactions->get();
                    $productPrice = ProductsPrices::findFirst($id);

                    if ($productPrice) {
                        if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                            $productPrice->product_id = $request['product_id'];
                        }
                        if (isset($request['price_level']) && strlen($request['price_level']) == 1) {
                            $productPrice->price_level = $request['price_level'];
                        }
                        if (isset($request['price']) && is_numeric($request['price'])) {
                            $productPrice->price = $request['price'];
                        }
                        if ($productPrice->update()) {
                            $this->content['product-price'] = $productPrice;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El precio ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($productPrice);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el precio.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el precio a modificar.');
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function updateAllPricesIntento ()
    {
        try {
            if ($this->userHasPermission()) {
                //$tx = $this->transactions->get();
                $request = $this->request->getPost();
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/documents/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $fullPath = '';
                $sql="SELECT code, id from wms_products where active = true";
                $productsList = $this->db->query($sql)->fetchAll();
                $sql2="SELECT * from wms_products_prices where price_level = 'A'";
                $productPrice = $this->db->query($sql2)->fetchAll();
                $concatStrPriceList = "";
                $mydata = [];
                $updateMultiPriceA = "UPDATE wms_products_prices set price = case ";
                $updateMultiPriceB = "UPDATE wms_products_prices set price = case ";
                $updateMultiPriceC = "UPDATE wms_products_prices set price = case ";
                $updateMultiPriceD = "UPDATE wms_products_prices set price = case ";
                $updateMultiPriceE = "UPDATE wms_products_prices set price = case ";
                $productPriceCobcatA = "";
                $productPriceCobcatB = "";
                $productPriceCobcatC = "";
                $productPriceCobcatD = "";
                $productPriceCobcatE = "";
                $myIdsPriceProductsA = [];
                $myIdsPriceProductsB = [];
                $myIdsPriceProductsC = [];
                $myIdsPriceProductsD = [];
                $myIdsPriceProductsE = [];
                $csvData = [];
                $noExistProduct = false;
                $existProduct = false;
               
                $insertPriceAArray = [];
                $validUser = Auth::getUserData($this->config);
                /* $sqlProductosId = "SELECT product_id from wms_products_prices where price_level = 'A'";
                $queryIdProducts = $this->db->query($sqlProductosId)->fetchAll(); */
                $arrayIdsPriceProducts = [];
                //$separrarIds = implode(',',$queryIdProducts);
                
                foreach($productPrice as $idsProducts){
                    array_push($arrayIdsPriceProducts, $idsProducts['product_id']);
                }
                foreach ($this->request->getUploadedFiles() as $file) {
                    $fileName = $file->getName();
                    $fullPath = $upload_dir . $fileName;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    if ($file->moveTo($fullPath)) {
                        // $tx->commit();

                        /* $csvData = array();
                        if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                           while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                                    $csvData[] = $data;  
                            }
                            
                        } */
                        $csvData = Helpers::csv2array($fullPath);
                         //fclose($handle);
                         // echo("<pre>");
                         // print_r($csvData);
                         // exit();

                        $aux_products = [];
                        
                        $count=0;
                        foreach ($csvData as $k){
                            
                            if($count>0){
                                
                                if (!empty($k[0]) ) {
                                    $d= substr($k[0],-5);
                                    
                                    
                                    foreach($productsList as $valueProductList){
                                        
                                        if ($d == $valueProductList['code']) {
                                            //var_dump(array_search($valueProductList['id'], $arrayIdsPriceProducts));
                                            //var_dump(array_search($valueProductList['id'], $productPrice));
                                            if (in_array($valueProductList['id'], $arrayIdsPriceProducts)) {
                                                $existProduct = true;
                                            } else {
                                                $noExistProduct = true;
                                                $typePrice = "'A'";
                                                //var_dump(array_search($valueProductList['id'], $productPrice));
                                                //die();
                                                array_push($insertPriceAArray,'('.$valueProductList['id'].','.$typePrice.','.$k[2].','.$validUser->id.')');
                                            }
                                            foreach($productPrice as $valueProductPrices){
                                                if ($valueProductPrices['price_level'] == "A" && $valueProductList['id'] == $valueProductPrices['product_id']) {
                                                    //$concatStrPriceList .= "UPDATE wms_products_prices SET price = $k[2] WHERE id = ".$valueProductPrices['id'].";";
                                                    //$productPrice->price = $k[2];
                                                    //array_push($mydata, "UPDATE wms_products_prices SET price = $k[2] WHERE id = ".$valueProductPrices['id'].";");
                                                    $productPriceCobcatA .= ' WHEN id = '.$valueProductPrices['id'].' THEN '.$k[2].'';
                                                    array_push($myIdsPriceProductsA, $valueProductPrices['id']);
                                                }
                                                
                                            }
                                        }
                                    }
                                }

                            }
                            $count++;
                                //array_push($aux_products, $b);
                        }
                                
                                 // exit();
                        
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }
                    
                }
                $separarPorComasA = implode(',',$myIdsPriceProductsA);
                $separarPorComasC = implode(',',$myIdsPriceProductsC);
                $separarPorComasD = implode(',',$myIdsPriceProductsD);
                $separarPorComasE = implode(',',$myIdsPriceProductsE);
                $updateMultiPriceA .= $productPriceCobcatA. ' END WHERE id IN ('.$separarPorComasA.');';
                $updateMultiPriceC .= $productPriceCobcatC. ' END WHERE id IN ('.$separarPorComasC.');';
                $updateMultiPriceD .= $productPriceCobcatD. ' END WHERE id IN ('.$separarPorComasD.');';
                $updateMultiPriceE .= $productPriceCobcatE. ' END WHERE id IN ('.$separarPorComasE.');';
                
                
                
                /* 
                
                $tx = $this->transactions->get();
                $this->db->query($updateMultiPriceC);
                $tx->commit();
                $tx = $this->transactions->get();
                $this->db->query($updateMultiPriceD);
                $tx->commit();
                $tx = $this->transactions->get();
                $this->db->query($updateMultiPriceE);
                $tx->commit(); */
                $tx = $this->transactions->get();
                // Actualizar
                if ($existProduct) {
                if ($this->db->query($updateMultiPriceA)) {
                    
                    $this->updatePricesLevelB($csvData,$productsList);
                    $this->updatePricesLevelC($csvData,$productsList);
                    $this->updatePricesLevelD($csvData,$productsList);
                    $this->updatePricesLevelE($csvData,$productsList);
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('precios actualizados');
                    $tx->commit();
                }
                }
                // Insertar
                if ($noExistProduct) {
                    $separarPorComas = implode(',',$insertPriceAArray);
                    $insertPriceA = "INSERT INTO wms_products_prices (product_id, price_level, price, created_by) VALUES".$separarPorComas;
                    /* var_dump($insertPriceA);
                    die(); */
                    if ($this->db->query($insertPriceA)) {
                        
                        
                        $this->updatePricesLevelB($csvData,$productsList);
                        $this->updatePricesLevelC($csvData,$productsList);
                        $this->updatePricesLevelD($csvData,$productsList);
                        $this->updatePricesLevelE($csvData,$productsList);
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('precios actualizados');
                        $tx->commit();
                    }
                    }
                
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function updatePricesLevelB($dataCSV,$productsList){
        $sql2="SELECT * from wms_products_prices where price_level = 'B'";
        $productPrice = $this->db->query($sql2)->fetchAll();
        $productPriceCobcatB = "";
        $myIdsPriceProductsB = [];
        $updateMultiPriceB = "UPDATE wms_products_prices set price = case ";

        $count=0;
        $noExistProduct = false;
        $existProduct = false;
        
        $insertPriceBArray = [];
        $validUser = Auth::getUserData($this->config);
        /* $sqlProductosId = "SELECT product_id from wms_products_prices where price_level = 'B'";
                $queryIdProducts = $this->db->query($sqlProductosId)->fetchAll(); */
                $arrayIdsPriceProducts = [];
                //$separrarIds = implode(',',$queryIdProducts);
                
                foreach($productPrice as $idsProducts){
                    array_push($arrayIdsPriceProducts, $idsProducts['product_id']);
                }
        foreach ($dataCSV as $k){
            
            if($count>0){
                
                if (!empty($k[0]) ) {
                    $d= substr($k[0],-5);
                    
                    
                    foreach($productsList as $valueProductList){
                        
                        if ($d == $valueProductList['code']) {
                            if (in_array($valueProductList['id'], $arrayIdsPriceProducts)) {
                                $existProduct = true;
                            } else {
                                $noExistProduct = true;
                                $typePrice = "'B'";
                                array_push($insertPriceBArray,'('.$valueProductList['id'].','.$typePrice.','.$k[3].','.$validUser->id.')');
                            }
                            foreach($productPrice as $valueProductPrices){
                                if ($valueProductPrices['price_level'] == "B" && $valueProductList['id'] == $valueProductPrices['product_id']) {
                                    //$concatStrPriceList .= "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";";
                                    //$productPrice->price = $k[3];
                                    //array_push($mydata, "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";");
                                    $productPriceCobcatB .= ' WHEN id = '.$valueProductPrices['id'].' THEN '.$k[3].'';
                                    array_push($myIdsPriceProductsB, $valueProductPrices['id']);
                                }
                                
                            }
                        }
                    }
                }

            }
            $count++;
                //array_push($aux_products, $b);
        }
        $separarPorComasB = implode(',',$myIdsPriceProductsB);
        $updateMultiPriceB .= $productPriceCobcatB. ' END WHERE id IN ('.$separarPorComasB.');';
        $tx = $this->transactions->get();
        if ($existProduct) {
        if ($this->db->query($updateMultiPriceB)) {
            //$tx->commit();
        }
        }

        // Insertar
        if ($noExistProduct) {
            $separarPorComas = implode(',',$insertPriceBArray);
            $insertPriceB = "INSERT INTO wms_products_prices (product_id, price_level, price, created_by) VALUES".$separarPorComas;
            
            if ($this->db->query($insertPriceB)) {
                //$tx->commit();
            }
        }
        
    }
    public function updatePricesLevelC($dataCSV,$productsList){
        $sql2="SELECT * from wms_products_prices where price_level = 'C'";
        $productPrice = $this->db->query($sql2)->fetchAll();
        $productPriceCobcatC = "";
        $myIdsPriceProductsC = [];
        $updateMultiPriceC = "UPDATE wms_products_prices set price = case ";

        $count=0;
        $noExistProduct = false;
        $existProduct = false;
        
        $insertPriceCArray = [];
        $validUser = Auth::getUserData($this->config);
        /* $sqlProductosId = "SELECT product_id from wms_products_prices where price_level = 'C'";
                $queryIdProducts = $this->db->query($sqlProductosId)->fetchAll(); */
                $arrayIdsPriceProducts = [];
                //$separrarIds = implode(',',$queryIdProducts);
                
                foreach($productPrice as $idsProducts){
                    array_push($arrayIdsPriceProducts, $idsProducts['product_id']);
                }
        foreach ($dataCSV as $k){
            if($count>0){
                
                if (!empty($k[0]) ) {
                    
                    $d= substr($k[0],-5);
                    
                    
                    foreach($productsList as $valueProductList){
                        
                        if ($d == $valueProductList['code']) {
                            if (in_array($valueProductList['id'], $arrayIdsPriceProducts)) {
                                $existProduct = true;
                            } else {
                                $noExistProduct = true;
                                $typePrice = "'C'";
                                array_push($insertPriceCArray,'('.$valueProductList['id'].','.$typePrice.','.$k[4].','.$validUser->id.')');
                            }
                            foreach($productPrice as $valueProductPrices){
                                if ($valueProductPrices['price_level'] == "C" && $valueProductList['id'] == $valueProductPrices['product_id']) {
                                    //$concatStrPriceList .= "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";";
                                    //$productPrice->price = $k[3];
                                    //array_push($mydata, "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";");
                                    $productPriceCobcatC .= ' WHEN id = '.$valueProductPrices['id'].' THEN '.$k[4].'';
                                    array_push($myIdsPriceProductsC, $valueProductPrices['id']);
                                }
                                
                            }
                        }
                    }
                }

            }
            $count++;
                //array_push($aux_products, $b);
        }
        $separarPorComasC = implode(',',$myIdsPriceProductsC);
        $updateMultiPriceC .= $productPriceCobcatC. ' END WHERE id IN ('.$separarPorComasC.');';
        $tx = $this->transactions->get();
        if ($existProduct) {
        if ($this->db->query($updateMultiPriceC)) {
            //$tx->commit();
        }
        }
        if ($noExistProduct) {
            $separarPorComas = implode(',',$insertPriceCArray);
            $insertPriceC = "INSERT INTO wms_products_prices (product_id, price_level, price, created_by) VALUES".$separarPorComas;
            //var_dump($insertPriceC);
            if ($this->db->query($insertPriceC)) {
                //$tx->commit();
            }
        }
        
    }

    public function updatePricesLevelD($dataCSV,$productsList){
        $sql2="SELECT * from wms_products_prices where price_level = 'D'";
        $productPrice = $this->db->query($sql2)->fetchAll();
        $productPriceCobcatD = "";
        $myIdsPriceProductsD = [];
        $updateMultiPriceD = "UPDATE wms_products_prices set price = case ";

        $count=0;
        $noExistProduct = false;
        $existProduct = false;
        
        $insertPriceDArray = [];
        $validUser = Auth::getUserData($this->config);
        /* $sqlProductosId = "SELECT product_id from wms_products_prices where price_level = 'D'";
                $queryIdProducts = $this->db->query($sqlProductosId)->fetchAll(); */
                $arrayIdsPriceProducts = [];
                //$separrarIds = implode(',',$queryIdProducts);
                
                foreach($productPrice as $idsProducts){
                    array_push($arrayIdsPriceProducts, $idsProducts['product_id']);
                }
        foreach ($dataCSV as $k){
            
            if($count>0){
                
                if (!empty($k[0]) ) {
                    
                    $d= substr($k[0],-5);
                    
                    
                    foreach($productsList as $valueProductList){
                        
                        if ($d == $valueProductList['code']) {
                            if (in_array($valueProductList['id'], $arrayIdsPriceProducts)) {
                                $existProduct = true;
                            } else {
                                $noExistProduct = true;
                                $typePrice = "'D'";
                                array_push($insertPriceDArray,'('.$valueProductList['id'].','.$typePrice.','.$k[5].','.$validUser->id.')');
                            }
                            foreach($productPrice as $valueProductPrices){
                                if ($valueProductPrices['price_level'] == "D" && $valueProductList['id'] == $valueProductPrices['product_id']) {
                                    //$concatStrPriceList .= "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";";
                                    //$productPrice->price = $k[3];
                                    //array_push($mydata, "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";");
                                    $productPriceCobcatD .= ' WHEN id = '.$valueProductPrices['id'].' THEN '.$k[5].'';
                                    array_push($myIdsPriceProductsD, $valueProductPrices['id']);
                                }
                                
                            }
                        }
                    }
                }

            }
            $count++;
                //array_push($aux_products, $b);
        }
        $separarPorComasD = implode(',',$myIdsPriceProductsD);
        $updateMultiPriceD .= $productPriceCobcatD. ' END WHERE id IN ('.$separarPorComasD.');';
        $tx = $this->transactions->get();
        if ($existProduct) {
        if ($this->db->query($updateMultiPriceD)) {
            //$tx->commit();
        }
        }

        if ($noExistProduct) {
            $separarPorComas = implode(',',$insertPriceDArray);
            $insertPriceD = "INSERT INTO wms_products_prices (product_id, price_level, price, created_by) VALUES".$separarPorComas;
            if ($this->db->query($insertPriceD)) {
                //$tx->commit();
            }
        }
        
    }

    public function updatePricesLevelE($dataCSV,$productsList){
        $sql2="SELECT * from wms_products_prices where price_level = 'E'";
        $productPrice = $this->db->query($sql2)->fetchAll();
        $productPriceCobcatD = "";
        $myIdsPriceProductsD = [];
        $updateMultiPriceD = "UPDATE wms_products_prices set price = case ";

        $count=0;
        $noExistProduct = false;
        $existProduct = false;
        
        $insertPriceEArray = [];
        $validUser = Auth::getUserData($this->config);
        /* $sqlProductosId = "SELECT product_id from wms_products_prices where price_level = 'E'";
                $queryIdProducts = $this->db->query($sqlProductosId)->fetchAll(); */
                $arrayIdsPriceProducts = [];
                //$separrarIds = implode(',',$queryIdProducts);
                
                foreach($productPrice as $idsProducts){
                    array_push($arrayIdsPriceProducts, $idsProducts['product_id']);
                }
        foreach ($dataCSV as $k){
            
            if($count>0){
                
                if (!empty($k[0]) ) {
                    
                    $d= substr($k[0],-5);
                    
                    
                    foreach($productsList as $valueProductList){
                        
                        if ($d == $valueProductList['code']) {
                            if (in_array($valueProductList['id'], $arrayIdsPriceProducts)) {
                                $existProduct = true;
                            } else {
                                $noExistProduct = true;
                                $typePrice = "'E'";
                                array_push($insertPriceEArray,'('.$valueProductList['id'].','.$typePrice.','.$k[6].','.$validUser->id.')');
                            }
                            foreach($productPrice as $valueProductPrices){
                                if ($valueProductPrices['price_level'] == "E" && $valueProductList['id'] == $valueProductPrices['product_id']) {
                                    //$concatStrPriceList .= "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";";
                                    //$productPrice->price = $k[3];
                                    //array_push($mydata, "UPDATE wms_products_prices SET price = $k[3] WHERE id = ".$valueProductPrices['id'].";");
                                    $productPriceCobcatD .= ' WHEN id = '.$valueProductPrices['id'].' THEN '.$k[6].'';
                                    array_push($myIdsPriceProductsD, $valueProductPrices['id']);
                                }
                                
                            }
                        }
                    }
                }

            }
            $count++;
                //array_push($aux_products, $b);
        }
        $separarPorComasD = implode(',',$myIdsPriceProductsD);
        $updateMultiPriceD .= $productPriceCobcatD. ' END WHERE id IN ('.$separarPorComasD.');';
        $tx = $this->transactions->get();
        if ($existProduct) {
        if ($this->db->query($updateMultiPriceD)) {
            //$tx->commit();
        }
         }

        if ($noExistProduct) {
            $separarPorComas = implode(',',$insertPriceEArray);
            $insertPriceE = "INSERT INTO wms_products_prices (product_id, price_level, price, created_by) VALUES".$separarPorComas;
            if ($this->db->query($insertPriceE)) {
                //$tx->commit();
            }
        }
        
    }

   /* public function updateAllPrices ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/documents/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $fullPath = '';
                foreach ($this->request->getUploadedFiles() as $file) {
                    $fileName = $file->getName();
                    $fullPath = $upload_dir . $fileName;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    if ($file->moveTo($fullPath)) {
                        // $tx->commit();

                        $csvData = array();
                        if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                           while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                                    $csvData[] = $data;  
                            }
                            
                        }

                         fclose($handle);
                        $count=0;
                        foreach ($csvData as $k){
         
                            if($count>0){
                                    if (!empty($k[0]) ) {
                                 
                                        $d= substr($k[0],-5);
                                        $sql="SELECT * from wms_products where code = '$d'";
                                        $info = $this->db->query($sql)->fetch();
                                        $auxId =$info['id'];
                                        $sql2="SELECT * from wms_products_prices where  product_id = $auxId ";
                                        $info2 = $this->db->query($sql2)->fetchAll();
                                        if($info2){
                                        for ($ij=0; $ij <count($info2) ; $ij++) {
                                                $auxId=$info2[$ij]['id'];
                                                $sqlPP="SELECT * FROM wms_products_prices WHERE id=$auxId ";
                                                $infoPP = $this->db->query($sqlPP)->fetch();
                                                // $productPrice = ProductsPrices::findFirst($info2[$ij]['id']);
                                                 // $tx = $this->transactions->get();
                                                 // $productPrice->setTransaction($tx);
                                                $id=$infoPP['id'];
                                                if($infoPP['price_level'] =="A"){
                                                    if($k[2]!=""){
                                                        $sqla="UPDATE public.wms_products_prices
                                                                 SET price=$k[2]
                                                                 WHERE id = $id;";
                                                                  $infoa = $this->db->query($sqla);
                                                       //  $productPrice->price = $k[2];
                                                    }
                                                }
                                                if($infoPP['price_level'] =="B"){
                                                    if($k[3]!=""){
                                                         $sqlb="UPDATE public.wms_products_prices
                                                                 SET price=$k[3]
                                                                 WHERE id = $id;";
                                                                  $infob = $this->db->query($sqlb);
                                                        // $productPrice->price = $k[3];
                                                        
                                                    }
                                                }
                                                if($infoPP['price_level'] =="C"){
                                                    if($k[4]!=""){
                                                         $sqlc="UPDATE public.wms_products_prices
                                                                 SET price=$k[4]
                                                                 WHERE id = $id;";
                                                                  $infoc = $this->db->query($sqlc);
                                                        // $productPrice->price = $k[4];
                                                       
                                                    }
                                                }
                                                if($infoPP['price_level'] =="D"){
                                                    if($k[5]!=""){
                                                         $sqld="UPDATE public.wms_products_prices
                                                                 SET price=$k[5]
                                                                 WHERE id = $id;";
                                                                  $infod = $this->db->query($sqld);
                                                        // $productPrice->price = $k[5];
                                                       
                                                    }
                                                }
                                                if($infoPP['price_level'] =="E"){
                                                    if($k[6]!=""){
                                                        $sqle="UPDATE public.wms_products_prices
                                                                 SET price=$k[6]
                                                                 WHERE id = $id;";
                                                                  $infoe = $this->db->query($sqle);
                                                        // $productPrice->price = $k[6];
                                                        
                                                    }
                                                }
                                                $this->content['result'] = true;
                                                /*if ($productPrice->update() !== false) {
                                                    $tx->commit();
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('precios actualizados');
                                                }
                                            // $tx->commit();
                                            }
                                            // exit();
                                        }
                                    }

                                }
                                $count++;
                                 }
                                 // exit();
                        
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }
                    
                }

                /*  if($this->content['result'] === true ){
                                    $tx->commit();
                } 


            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }


        $this->response->setJsonContent($this->content);
    }
*/
     public function updateAllPrices ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $documen_id =  DocumentsWasp::uploadFile($this->request);
                if($documen_id){
                $BatchLoads = new BatchLoads();
                $BatchLoads->document_id = $documen_id;
                $BatchLoads->type_id = 1;
                $BatchLoads->status =  'NUEVO';
                if ($BatchLoads->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El Documento se registro correctamente.');
                    $tx->commit();
                } else {
                    $this->content['result'] = false;
                    $this->content['error'] = Helpers::getErrors($BatchLoads);
                    $this->content['message'] = Message::error('El Documento no se se registro correctamente.');
                                // $tx->rollback();
                }
                }else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
                


                // $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/documentsTasks/';
                /* if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $fullPath = '';
                foreach ($this->request->getUploadedFiles() as $file) {
                    $fileName = $file->getName();
                    $fullPath = $upload_dir . $fileName;
                    
                    $time = time();
                   
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    if ($file->moveTo($fullPath)) {
                        
                         $this->content['result'] = true;
                         $this->content['message'] = Message::success('Archivo cargado correctamente');

                        
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }

                    
                } */




            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }


        $this->response->setJsonContent($this->content);
    }
    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $productPrice = ProductsPrices::findFirst($id);

                if ($productPrice) {
                    $productPrice->setTransaction($tx);

                    if ($productPrice->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El precio ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($product);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el precio.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El precio no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 10 OR role_id = 3 OR role_id = 4 OR role_id = 20 OR role_id = 27 OR role_id = 22)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasCompleteGetPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 22)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userIsClient ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 1 OR role_id = 7 OR role_id = 10 OR role_id = 3 OR role_id = 4 OR role_id = 20 OR role_id = 27 OR role_id = 22)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}
