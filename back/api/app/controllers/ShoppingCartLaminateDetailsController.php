<?php

use Phalcon\Mvc\Controller;

class ShoppingCartLaminateDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShoppingCartLaminateDetails ()
    {
        if ($this->userIsCustomer()) {
            $validUser = Auth::getUserData($this->config);
            if ($validUser && $validUser->id) {
                $sql = "SELECT scld.id, scld.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scld.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scld.qty, pp.price AS unit_price, pp.price * scld.qty AS amount
                        FROM sls_shopping_cart_laminate_details AS scld
                        INNER JOIN wms_products AS p
                        ON p.id = scld.product_id
                        INNER JOIN wms_lines AS l
                        ON l.id = p.line_id
                        INNER JOIN wms_categories AS c
                        ON c.id = l.category_id
                        INNER JOIN sls_shopping_cart AS ssc
                        ON ssc.id = scld.shopping_cart_id
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

    public function getStorage ($id) {
        $oSC = ShoppingCart::find("id = $id");
        $office = $oSC->toArray();
        $oId = $office[0]['branchoffice'];

        $sSC = Storages::find("branch_office_id = $oId AND storage_type_id = 8");
        $strg = $sSC->toArray();
        $storage = $strg[0]['id'];

        return $storage;
    }

    public function getShoppingCartLaminateDetailsByShoppingCartId ($shoppingCartId)
    {
        if ($this->userHasPermission() && !is_null($shoppingCartId) && is_numeric($shoppingCartId)) {
            $shoppingCart = ShoppingCart::findFirst($shoppingCartId);
            $storage = $this->getStorage($shoppingCartId);
            $sql = "SELECT scld.invoice_id,scld.id, scld.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scld.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scld.qty, scld.price_product AS unit_price, scld.price_product * scld.qty AS amount, scld.status
                    FROM sls_shopping_cart_laminate_details AS scld
                    INNER JOIN wms_products AS p ON p.id = scld.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    INNER JOIN sls_shopping_cart AS ssc ON ssc.id = scld.shopping_cart_id
                    INNER JOIN sls_customers AS cus ON cus.id = ssc.customer_id
                    INNER JOIN wms_products_prices AS pp ON pp.product_id = p.id AND pp.price_level = cus.price_list
                    WHERE ssc.id = $shoppingCartId
                    ORDER BY scld.id ASC;";
            $details = $this->db->query($sql)->fetchAll();
            if ($shoppingCart->status == 'AUTORIZADO') {
                $sql = "SELECT l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
                        FROM (SELECT s1.product_id, SUM(s1.qty) AS stock
                              FROM (SELECT md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                    WHERE m.status = 1 AND md.bale_id IS NULL AND md.bag_id IS NULL AND m.storage_id = $storage ORDER BY m.date ASC) AS s1
                              GROUP BY s1.product_id) AS s2
                        INNER JOIN wms_products AS p ON p.id = s2.product_id
                        INNER JOIN wms_lines AS l ON l.id = p.line_id
                        INNER JOIN wms_categories AS c ON c.id = l.category_id
                        WHERE s2.stock > 0 AND l.category_id = 5
                        ORDER BY s2.product_id ASC;";
                $products = $this->db->query($sql)->fetchAll();
                for ($i=0; $i < count($details); $i++) {
                    $details[$i]['stock'] = false;
                    if($details[$i]['status'] == 'AUTORIZADO'){
                        for ($j=0; $j < count($products); $j++) {
                            if ($products[$j]['product_id'] == $details[$i]['product_id']) {
                                if($products[$j]['stock'] >= $details[$i]['qty']){
                                    $details[$i]['condition'] = true;
                                } else {
                                    $details[$i]['condition'] = false;
                                }
                                $details[$i]['stock'] = $products[$j]['stock'];
                                $products[$j]['stock'] -= $details[$i]['qty'];
                            }
                        }
                    }
                }
            }
            $this->content['details'] = $details;
            $this->content['type'] = 2;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios o no se ha recibido el id de orden.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getinLaminateDetails () {

        $request = $this->request->getPost();
        $product = $request['product'];
        $cart_id = $request['cart_id'];
        $baleQty = $request['baleQty'];
        $storage = $this->getStorage($cart_id);
        $sql = "SELECT l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
                        FROM (SELECT s1.product_id, SUM(s1.qty) AS stock
                              FROM (SELECT md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m
                                    ON m.id = md.movement_id
                                    WHERE m.status = 1
                                    AND md.bale_id IS NULL
                                    AND md.bag_id IS NULL
                                    AND m.storage_id = $storage
                                    ORDER BY m.date ASC) AS s1
                              GROUP BY s1.product_id) AS s2
                        INNER JOIN wms_products AS p ON p.id = s2.product_id
                        INNER JOIN wms_lines AS l ON l.id = p.line_id
                        INNER JOIN wms_categories AS c ON c.id = l.category_id
                        WHERE s2.stock > 0
                        AND l.category_id = 5
                        AND s2.product_id = $product
                        ORDER BY s2.product_id ASC;";
        $data = $this->db->query($sql);
        //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $inLami = $data->fetchAll();

        $details = [];
        $qtySum = 0;
        $pQty = 0;
        foreach ($inLami as $lami)
        {
            if ($qtySum < $baleQty)
            {
                $data = (object)array();
                $data->weight = $lami['stock'];
                $qtySum += $lami['stock'];
                $pQty += 1;
                array_push($details,$data);
            }
        }
        $this->content['qty'] = $qtySum;
        $this->content['details'] = $details;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);

    }

    public function partialization () {
        $request = $this->request->getPost();
        $newQty = intval($request['baleQtyComparator']) - intval($request['baleQty']);
        $tx = $this->transactions->get();
        $laminateDetails = ShoppingCartLaminateDetails::findFirst($request['idDetail']);
        $laminateDetails->setTransaction($tx);
        if($newQty == 0){
            $laminateDetails->status = 'POSTERGADO';
            if ($laminateDetails->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('Parcialidad creada correctamente.');
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($laminateDetails);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
            }
        } else{
            $laminateDetails->qty = $request['baleQty'];
            if ($laminateDetails->update()) {
                $laminateCart = new ShoppingCartLaminateDetails();
                $laminateCart->setTransaction($tx);
                $laminateCart->created_by = $laminateDetails->created_by;
                $laminateCart->shopping_cart_id = $laminateDetails->shopping_cart_id;
                $laminateCart->price_product = $laminateDetails->price_product;
                $laminateCart->product_id = $laminateDetails->product_id;
                $laminateCart->status = 'POSTERGADO';
                $laminateCart->qty = $newQty;
                if ($laminateCart->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Parcialidad creada correctamente.');
                    $tx->commit();
                }else{
                    $this->content['error'] = Helpers::getErrors($laminateCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
                }
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShoppingCartLaminateDetailsWithoutStock ()
    {
        set_time_limit(0);
        if ($this->userHasPermission()) {
            $sql = "SELECT scld.id, scld.shopping_cart_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, scld.product_id, p.code AS product_code, p.name AS product_name, scld.qty
                    FROM sls_shopping_cart_laminate_details AS scld
                    INNER JOIN wms_products AS p
                    ON p.id = scld.product_id
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE scld.status in ('SOLICITADO','AUTORIZADO');";
            $laminateDetails = $this->db->query($sql)->fetchAll();

            $movements = new MovementsController();
            $availableLaminateProducts = $movements->generateStorageInventory(null, 13, 5, null, null, null);

            $details = [];
            foreach ($laminateDetails as $detail) {
                $requiredQty = $detail['qty'];
                foreach ($availableLaminateProducts as $product) {
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
            if ($this->userIsCustomer()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty'])) {
                    $tx = $this->transactions->get();

                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if ($shoppingCart) {
                        $detail = new ShoppingCartLaminateDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Laminado agregado a carrito de compras correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el laminado al carrito de compras.');
                        }
                    } else {
                        $shoppingCart = new ShoppingCart();
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->user_id = $validUser->id;
                        if ($shoppingCart->create()) {
                            $detail = new ShoppingCartLaminateDetails();
                            $detail->setTransaction($tx);
                            $detail->shopping_cart_id = $shoppingCart->id;
                            $detail->product_id = $request['productId'];
                            $detail->qty = $request['qty'];
                            $detail->price_product = $request['price'];
                            if ($detail->create()) {
                                $this->content['result'] = true;
                                $this->content['detail'] = $detail;
                                $this->content['message'] = Message::success('Laminado agregado a carrito de compras correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($detail);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el laminado al carrito de compras.');
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
                        $detail = new ShoppingCartLaminateDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Laminado agregado a pedido correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el laminado al pedido.');
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

    public function delete ($id)
    {
        try {
            if ($this->userIsCustomer()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id) {
                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if ($shoppingCart) {
                        $detail = ShoppingCartLaminateDetails::findFirst($id);
                        if ($detail && $detail->shopping_cart_id == $shoppingCart->id) {
                            $tx = $this->transactions->get();
                            $detail->setTransaction($tx);
                            if ($detail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El laminado ha sido eliminado del carrito de compras.');
                                $tx->commit();
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el laminado del carrito de compras.');
                            }
                        }
                    }
                }
            } elseif ($this->userHasPermission()) {
                $detail = ShoppingCartLaminateDetails::findFirst($id);
                if ($detail) {
                    $shoppingCart = ShoppingCart::findFirst($detail->shopping_cart_id);
                    if ($shoppingCart->status == 'NUEVO') {
                        $tx = $this->transactions->get();
                        $detail->setTransaction($tx);
                        if ($detail->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El laminado ha sido eliminado del pedido.');
                            $tx->commit();
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el laminado del pedido.');
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
                    WHERE (role_id = 14 OR role_id = 16 OR role_id = 3 OR role_id = 20 OR role_id = 22 OR role_id = 27 OR role_id = 17 OR role_id = 29 OR role_id = 28)
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 20 OR role_id = 22 OR role_id = 27 OR role_id = 17 OR role_id = 29 OR role_id = 28)
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