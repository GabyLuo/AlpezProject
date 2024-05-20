<?php

use Phalcon\Mvc\Controller;

class ShoppingCartInBulkDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShoppingCartInBulkDetails ()
    {
        if ($this->userIsCustomer()) {
            $validUser = Auth::getUserData($this->config);
            if ($validUser && $validUser->id) {
                $sql = "SELECT scibd.id, scibd.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scibd.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scibd.qty, pp.price AS unit_price, pp.price * scibd.qty AS amount
                        FROM sls_shopping_cart_in_bulk_details AS scibd,ssc.storage_id
                        INNER JOIN wms_products AS p
                        ON p.id = scibd.product_id
                        INNER JOIN wms_lines AS l
                        ON l.id = p.line_id
                        INNER JOIN wms_categories AS c
                        ON c.id = l.category_id
                        INNER JOIN sls_shopping_cart AS ssc
                        ON ssc.id = scibd.shopping_cart_id
                        INNER JOIN sls_customers AS cus
                        ON cus.id = ssc.customer_id
                        INNER JOIN wms_products_prices AS pp
                        ON pp.product_id = p.id AND pp.price_level = cus.price_list
                        WHERE ssc.user_id = $validUser->id
                        AND ssc.status = 'NUEVO';";
                $data = $this->db->query($sql);
                // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $this->content['details'] = $data->fetchAll();
                $this->content['result'] = true;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getDate ($date) {
            $today = date('Y-m-d');
            if ($date >= $today) {
                $this->content['result'] = true;
            } else {
                $this->content['result'] = false;
            }
            
            $this->response->setJsonContent($this->content);
    }
    public function getStorage ($id) {
        $oSC = ShoppingCart::find("id = $id");
        $office = $oSC->toArray();
        /*$oId = $office[0]['branchoffice'];
        $storage = $office[0]['storage_id'];
        $sSC = Storages::findFirst("branch_office_id = $oId AND storage_type_id = $storage");
        $strg = $sSC->toArray();
        $storage = $strg[0]['id'];*/

        return $office[0]['storage_id'];
    }

    public function getinBulkDetails () {

        $request = $this->request->getPost();
        $product = $request['product'];
        $cart_id = $request['cart_id'];
        $baleQty = $request['baleQty'];
        $storage = $this->getStorage($cart_id);
        $movements = new MovementsController();
        $products = $movements->generateStorageInventoryv3(null,$storage,null,null,$product,null,null,null);
        // $sql = "SELECT l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
        //                 FROM (SELECT s1.product_id, SUM(s1.qty) AS stock
        //                       FROM (SELECT md.product_id, CASE WHEN m.type_id = 2 THEN -1 * md.qty WHEN m.type_id = 1 THEN md.qty END AS qty
        //                             FROM wms_movement_details AS md
        //                             INNER JOIN wms_movements AS m ON m.id = md.movement_id
        //                             WHERE m.status = 'EJECUTADO'
        //                             AND m.storage_id = $storage
        //                             ORDER BY m.date ASC) AS s1
        //                       GROUP BY s1.product_id) AS s2
        //                 INNER JOIN wms_products AS p ON p.id = s2.product_id
        //                 INNER JOIN wms_lines AS l ON l.id = p.line_id
        //                 INNER JOIN wms_categories AS c ON c.id = l.category_id
        //                 WHERE s2.stock > 0
        //                 AND s2.product_id = $product
        //                 ORDER BY s2.product_id ASC;";
        // $data = $this->db->query($sql);
        // $inBulks = $data->fetchAll();

        // $details = [];
        // $qtySum = 0;
        // $pQty = 0;
        // foreach ($inBulks as $bulk)
        // {
        //     if ($qtySum < $baleQty)
        //     {
        //         $data = (object)array();
        //         $data->weight = $bulk['stock'];
        //         $qtySum += $bulk['stock'];
        //         $pQty += 1;
        //         array_push($details,$data);
        //     }
        // }
        if (count($products['data']) > 0) {
            $this->content['qty'] = $products['data'][0]['stock'];
            $this->content['details'] = $products['data'];
        } else {
            $this->content['qty'] = [];
            $this->content['details'] = [];
        }
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);

    }

    public function partialization () {
        $request = $this->request->getPost();
        $newQty = intval($request['baleQtyComparator']) - intval($request['baleQty']);
        $tx = $this->transactions->get();
        $inBulkCartDetails = ShoppingCartInBulkDetails::findFirst($request['idDetail']);
        $inBulkCartDetails->setTransaction($tx);
        if($newQty == 0){
            // Esto quiere decir que la cantidad de piezas es la misma que los que se quieren postergar
            $inBulkCartDetails->status = 'POSTERGADO';
            if ($inBulkCartDetails->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('Parcialidad creada correctamente 1.');
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($inBulkCartDetails);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
            }
        } else {
            // En este caso se actualiza el valor del detalle a los que se quiere parcializar y se crea un nuevo detalle en estatus postergado
            $inBulkCartDetails->qty = intval($request['baleQty']);
            if ($inBulkCartDetails->update()) {
                $inBulCart = new ShoppingCartInBulkDetails();
                $inBulCart->setTransaction($tx);
                $inBulCart->created_by = $inBulkCartDetails->created_by;
                $inBulCart->shopping_cart_id = $inBulkCartDetails->shopping_cart_id;
                $inBulCart->price_product = $inBulkCartDetails->price_product;
                $inBulCart->product_id = $inBulkCartDetails->product_id;
                $inBulCart->ieps = $inBulkCartDetails->ieps;
                $inBulCart->status = 'POSTERGADO';
                $inBulCart->qty = $newQty;
                if ($inBulCart->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Parcialidad creada correctamente 2.');
                    $tx->commit();
                }else{
                    $this->content['error'] = Helpers::getErrors($inBulCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
                }
            }
        }
        $this->response->setJsonContent($this->content);

    }

    public function getShoppingCartInBulkDetailsByShoppingCartId ($shoppingCartId)
    {
        if ($this->userHasPermission() && !is_null($shoppingCartId) && is_numeric($shoppingCartId)) {
            $shoppingCart = ShoppingCart::findFirst($shoppingCartId);
            $storage = $this->getStorage($shoppingCartId);
            // print_r($storage);
            // exit();
            $sql = "SELECT  scibd.invoice_id, scibd.id, scibd.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scibd.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scibd.qty, scibd.price_product AS unit_price,scibd.ieps AS ieps, scibd.price_product * scibd.qty AS amount, scibd.status,CONCAT(c.code,'-',l.code,'-',p.code) AS code
                    FROM sls_shopping_cart_in_bulk_details AS scibd
                    INNER JOIN wms_products AS p ON p.id = scibd.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    INNER JOIN sls_shopping_cart AS ssc ON ssc.id = scibd.shopping_cart_id
                    INNER JOIN sls_customers AS cus ON cus.id = ssc.customer_id
                    INNER JOIN wms_products_prices AS pp ON pp.product_id = p.id AND pp.price_level = cus.price_list
                    WHERE ssc.id = $shoppingCartId
                    ORDER BY scibd.id ASC;";
            $details = $this->db->query($sql)->fetchAll();
            $sqlC= "SELECT ssc.id, si.status from sls_shopping_cart  as ssc 
            left join sls_invoices as si on si.shopping_cart_id = ssc.id
            where ssc.id = $shoppingCartId";
            $detailsC = $this->db->query($sqlC)->fetchAll();
             // echo("<pre>");
             // print_r($detailsC);
             // exit();
            $canCancel= 0;
            $aux = 0;
            if($detailsC){
                for ($ij=0; $ij < count($detailsC); $ij++) {
                     // print_r($detailsC[$ij]['status']);
                    if($detailsC[$ij]['status'] == 'CANCELADO'){
                       
                        $aux = $aux+1;
                    }
                }
                if(count($detailsC)== $aux){
                    $canCancel= 1;

                }
            }
           // print_r(($canCancel));
             //exit();
            if ($shoppingCart->status == 'AUTORIZADO' || $shoppingCart->status == 'PARCIAL') {
                $movements = new MovementsController();
                // $products = $movements->generateStorageInventoryv2(null,$storage,null,null,null,null);
                
                for ($i=0; $i < count($details); $i++) {
                    $products = $movements->generateStorageInventoryv3(null,$storage,null,null,$details[$i]['product_id'],null, null, null);
                    // echo("<pre>");
                    // print_r($products);
                    // exit();
                    $details[$i]['stock'] = false;
                        if($details[$i]['status'] == 'AUTORIZADO'){
                        for ($j=0; $j < count($products['data']); $j++) {
                            if ($products['data'][$j]['product_id'] == $details[$i]['product_id']) {
                                if($products['data'][$j]['stock'] >= $details[$i]['qty']){
                                    $details[$i]['condition'] = true;
                                } else {
                                    $details[$i]['condition'] = false;
                                }
                                $details[$i]['stock'] = $products['data'][$j]['stock'];
                                $products['data'][$j]['stock'] -= $details[$i]['qty'];
                            }
                        }
                    }
                }
            }
            $this->content['details'] = $details;
            $this->content['canCancel'] = $canCancel;
            $this->content['type'] = 1;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios o no se ha recibido el id de orden.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShoppingCartInBulkDetailsWithoutStock ()
    {
        set_time_limit(0);
        if ($this->userHasPermission()) {
            $sql = "SELECT scibd.id, scibd.shopping_cart_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, scibd.product_id, p.code AS product_code, p.name AS product_name, scibd.qty
                    FROM sls_shopping_cart_in_bulk_details AS scibd
                    INNER JOIN wms_products AS p
                    ON p.id = scibd.product_id
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE scibd.status in ('SOLICITADO','AUTORIZADO');";
            $inBulkDetails = $this->db->query($sql)->fetchAll();

            $movements = new MovementsController();
            $availableInBulkProducts = $movements->generateStorageInventory(null, 10, 13, null, null, null);

            $details = [];
            foreach ($inBulkDetails as $detail) {
                $requiredQty = $detail['qty'];
                foreach ($availableInBulkProducts as $product) {
                    if ($detail['product_id'] == $product['product_id']) {
                        $requiredQty -= $product['stock'];
                    }
                }
                if ($requiredQty > 0) {
                    array_push($details, $detail);
                }
            }
            $this->content['details'] = $details;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            $request = $this->request->getPost();
            /* $dateShopping = null;
            if ($request['dateShoppingInBulk'] == null) {
                $dateShopping = null;
            }else {
                $dateShopping = date("Y-m-d", strtotime($request['dateShoppingInBulk']));
            } */
            $dateShopping = null;
            if ($request['dateShoppingInBulk'] == null) {
                $dateShopping = null;
            } else {
                $dateShopping = date("Y-m-d", strtotime($request['dateShoppingInBulk']));
            }
            if ($this->userIsCustomer()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty'])) {
                    $tx = $this->transactions->get();
                    
                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if ($shoppingCart) {
                        $detail = new ShoppingCartInBulkDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        //$dateShopping = date("Y-m-d", strtotime($request['dateShoppingInBulk']));
                        $detail->inmediatedate = $dateShopping;
                        /* $detail->commentsshoppingproduct = $request['commentShoppingInbulk']; */
                        $product = Products::findFirst($request['productId']);
                        // $detail->ieps = $product->ieps;
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Producto agregado a carrito de compras correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el producto al carrito de compras.');
                        }
                    } else {
                        $shoppingCart = new ShoppingCart();
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->user_id = $validUser->id;
                        if ($shoppingCart->create()) {
                            $detail = new ShoppingCartInBulkDetails();
                            $detail->setTransaction($tx);
                            $detail->shopping_cart_id = $shoppingCart->id;
                            $detail->product_id = $request['productId'];
                            $detail->qty = $request['qty'];
                            $detail->price_product = $request['price'];
                            //$dateShopping = date("Y-m-d", strtotime($request['dateShoppingInBulk']));
                            $detail->inmediatedate = $dateShopping;
                            /* $detail->commentsshoppingproduct = $request['commentShoppingInbulk']; */
                            $product = Products::findFirst($request['productId']);
                            $detail->ieps = $product->ieps;
                            if ($detail->create()) {
                                $this->content['result'] = true;
                                $this->content['detail'] = $detail;
                                $this->content['message'] = Message::success('Producto agregado a carrito de compras correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($detail);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el producto al carrito de compras.');
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($shoppingCart);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el carrito de compras.');
                        }
                    }
                }
            } elseif ($this->userHasPermission()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty']) && isset($request['shoppingCartId']) && is_numeric($request['shoppingCartId'])) {
                    $tx = $this->transactions->get();

                    $shoppingCart = ShoppingCart::findFirst($request['shoppingCartId']);
                    if ($shoppingCart) {
                        $detail = new ShoppingCartInBulkDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        $detail->inmediatedate = $dateShopping;
                        /* $detail->commentsshoppingproduct = $request['commentShoppingInbulk']; */
                        // $product = Products::findFirst($request['productId']);
                        // $detail->ieps = $product->ieps;
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Producto agregado a pedido correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el producto al pedido.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha recibido el pedido.');
                    }
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function deleteInbulk ($id) {
        try {
            if ($this->userHasPermission()) {
                //$detail = ShoppingCartInBulkDetails::find("shopping_cart_id = $id");
                $sql = "DELETE FROM sls_shopping_cart_in_bulk_details where shopping_cart_id = $id";
                $query = $this->db->execute($sql);
                $tx = $this->transactions->get();
                if ($query) {
                    //$shoppingCart = ShoppingCart::findFirst($detail->shopping_cart_id);
                    /* $tx = $this->transactions->get();
                    $detail->setTransaction($tx); */
                    $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se eliminaron los detalles del pedido.');
                        $tx->commit();
                    /* if ($detail->delete()) {
                        
                    } else {
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el producto del pedido.');
                    } */
                }else{
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar los detalles del pedido.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function delete ($id)
    {
        try {
            if ($this->userIsCustomer()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id) {
                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if ($shoppingCart) {
                        $detail = ShoppingCartInBulkDetails::findFirst($id);
                        if ($detail && $detail->shopping_cart_id == $shoppingCart->id) {
                            $tx = $this->transactions->get();
                            $detail->setTransaction($tx);
                            if ($detail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El producto ha sido eliminado del carrito de compras.');
                                $tx->commit();
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el producto del carrito de compras.');
                            }
                        }
                    }
                }
            } elseif ($this->userHasPermission()) {
                $detail = ShoppingCartInBulkDetails::findFirst($id);
                if ($detail) {
                    $shoppingCart = ShoppingCart::findFirst($detail->shopping_cart_id);
                    if ($shoppingCart->status == 'NUEVO') {
                        $tx = $this->transactions->get();
                        $detail->setTransaction($tx);
                        if ($detail->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El producto ha sido eliminado del pedido.');
                            $tx->commit();
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el producto del pedido.');
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userIsCustomer ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 14 OR role_id = 16)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 or role_id = 20 or role_id = 23 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 28 OR role_id = 17)
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

//