<?php

use Phalcon\Mvc\Controller;

class ShoppingCartBaleDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShoppingCartBaleDetails ()
    {
        if ($this->userIsCustomer()) {
            $validUser = Auth::getUserData($this->config);
            if ($validUser && $validUser->id) {
                $sql = "SELECT scbd.id, scbd.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scbd.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scbd.qty, pp.price AS unit_price, pp.price * scbd.qty AS amount
                        FROM sls_shopping_cart_bale_details AS scbd
                        INNER JOIN wms_products AS p
                        ON p.id = scbd.product_id
                        INNER JOIN wms_lines AS l
                        ON l.id = p.line_id
                        INNER JOIN wms_categories AS c
                        ON c.id = l.category_id
                        INNER JOIN sls_shopping_cart AS ssc
                        ON ssc.id = scbd.shopping_cart_id
                        INNER JOIN sls_customers AS cus
                        ON cus.id = ssc.customer_id
                        INNER JOIN wms_products_prices AS pp
                        ON pp.product_id = p.id AND pp.price_level = cus.price_list
                        WHERE ssc.user_id = $validUser->id
                        AND ssc.status = 'NUEVO';";
                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
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

        $sSC = Storages::find("branch_office_id = $oId AND storage_type_id = 1");
        $strg = $sSC->toArray();
        $storage = $strg[0]['id'];

        return $storage;
    }

    public function getBaleDetails () {

        $request = $this->request->getPost();
        $product = $request['product'];
        $cart_id = $request['cart_id'];
        $baleQty = $request['baleQty'];
        $storage = $this->getStorage($cart_id);
        $sql = "SELECT s2.bale_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
                        FROM (SELECT s1.bale_id, s1.product_id, SUM(s1.qty) AS stock
                              FROM (SELECT md.bale_id, md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                    WHERE m.status = 1
                                    AND md.bale_id IS NOT NULL
                                    AND md.product_id = $product
                                    AND m.storage_id = $storage
                                    ORDER BY m.date ASC) AS s1
                              GROUP BY s1.bale_id, s1.product_id) AS s2
                        INNER JOIN wms_products AS p ON p.id = s2.product_id
                        INNER JOIN wms_lines AS l ON l.id = p.line_id
                        INNER JOIN wms_categories AS c ON c.id = l.category_id
                        WHERE s2.stock > 0
                        AND l.category_id = 6
                        ORDER BY s2.bale_id ASC;";
        $data = $this->db->query($sql);
        //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $bales = $data->fetchAll();

        $details = [];
        $qtySum = 0;
        $pQty = 0;
        foreach ($bales as $bale)
        {
            if ($qtySum < $baleQty)
            {
                $data = (object)array();
                $data->id = $pQty;
                $data->bale_id = $bale['bale_id'];
                $data->weight = $bale['stock'];
                $qtySum += $bale['stock'];
                $pQty += 1;
                array_push($details,$data);
            }
        }
        $this->content['qty'] = $qtySum;
        $this->content['qtyBales'] = $pQty;
        $this->content['details'] = $details;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);

    }

    public function partialization () {
        $request = $this->request->getPost();
        $newQty = intval($request['baleQtyComparator']) - intval($request['baleQty']);
        $tx = $this->transactions->get();
        $baleCartDetails = ShoppingCartBaleDetails::findFirst($request['idDetail']);
        $baleCartDetails->setTransaction($tx);
        if($newQty == 0){
            $baleCartDetails->status = 'POSTERGADO';
            if ($baleCartDetails->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('Parcialidad creada correctamente.');
                $tx->commit();
            }else{
                $this->content['error'] = Helpers::getErrors($baleCartDetails);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
            }
        }else{
            $baleCartDetails->qty = $request['baleQty'];
            if ($baleCartDetails->update()) {
                $baleCart = new ShoppingCartBaleDetails();
                $baleCart->setTransaction($tx);
                $baleCart->created_by = $baleCartDetails->created_by;
                $baleCart->shopping_cart_id = $baleCartDetails->shopping_cart_id;
                $baleCart->price_product = $baleCartDetails->price_product;
                $baleCart->product_id = $baleCartDetails->product_id;
                $baleCart->status = 'POSTERGADO';
                $baleCart->qty = $newQty;
                if ($baleCart->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Parcialidad creada correctamente.');
                    $tx->commit();
                }else{
                    $this->content['error'] = Helpers::getErrors($baleCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la parcialidad.');
                }
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShoppingCartBaleDetailsByShoppingCartId ($shoppingCartId)
    {
        set_time_limit(0);
        if ($this->userHasPermission() && !is_null($shoppingCartId) && is_numeric($shoppingCartId)) {
            $shoppingCart = ShoppingCart::findFirst($shoppingCartId);
            $storage = $this->getStorage($shoppingCartId);
            $sql = "SELECT scbd.invoice_id,scbd.id, scbd.shopping_cart_id, l.category_id, c.name AS category_name, p.line_id, l.name AS line_name, scbd.product_id, p.code AS product_code, p.name AS product_name, p.photo AS product_photo, scbd.qty, scbd.price_product AS unit_price, scbd.price_product * scbd.qty AS amount, scbd.status
                    FROM sls_shopping_cart_bale_details AS scbd
                    INNER JOIN wms_products AS p ON p.id = scbd.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    INNER JOIN sls_shopping_cart AS ssc ON ssc.id = scbd.shopping_cart_id
                    INNER JOIN sls_customers AS cus ON cus.id = ssc.customer_id
                    INNER JOIN wms_products_prices AS pp ON pp.product_id = p.id AND pp.price_level = cus.price_list
                    WHERE ssc.id = $shoppingCart->id
                    ORDER BY scbd.id ASC;";
            $details = $this->db->query($sql)->fetchAll();
            if ($shoppingCart->status == 'AUTORIZADO' || $shoppingCart->status == 'REMISIONADO') {
                $sql = "SELECT s2.bale_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
                        FROM (SELECT s1.bale_id, s1.product_id, SUM(s1.qty) AS stock
                              FROM (SELECT md.bale_id, md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                    WHERE m.status = 1
                                    AND md.bale_id IS NOT NULL
                                    AND m.storage_id = $storage
                                    ORDER BY m.date ASC) AS s1
                              GROUP BY s1.bale_id, s1.product_id) AS s2
                        INNER JOIN wms_products AS p ON p.id = s2.product_id
                        INNER JOIN wms_lines AS l ON l.id = p.line_id
                        INNER JOIN wms_categories AS c ON c.id = l.category_id
                        WHERE s2.stock > 0
                        AND l.category_id = 6
                        ORDER BY s2.bale_id ASC;";
                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $bales = $data->fetchAll();
                $selectedBales = [];
                for ($i=0; $i < count($details); $i++) {
                    $selectedBale = null;
                    if($details[$i]['status'] == 'AUTORIZADO'){
                        foreach ($bales as $bale) {
                            if ($bale['product_id'] == $details[$i]['product_id'] && $bale['stock'] >= $details[$i]['qty'] && !in_array($bale['bale_id'], $selectedBales)) {
                                if (is_null($selectedBale)) {
                                    $selectedBale = $bale;
                                } elseif ($bale['stock'] < $selectedBale['stock']) {
                                    $selectedBale = $bale;
                                }
                            }
                        }
                        if (is_null($selectedBale)) {
                            $selectedBale = [];
                            $selectedBaleId = [];
                            $selectedBaleQty = [];
                            $qtySum = 0;
                            $pQty = 0;
                            foreach ($bales as $bale) {
                                if ($bale['product_id'] == $details[$i]['product_id'] && !in_array($bale['bale_id'], $selectedBaleId) && !in_array($bale['bale_id'], $selectedBales)) {
                                    if (count($selectedBale) == 0 || $qtySum < $details[$i]['qty']) {
                                        array_push($selectedBale, $bale);
                                        array_push($selectedBaleId, $bale['bale_id']);
                                        array_push($selectedBaleQty, $bale['stock']);
                                        $qtySum += $bale['stock'];
                                        $pQty += 1;
                                    }
                                }
                            }
                            if ($details[$i]['qty']) {
                                $details[$i]['bales_ids'] = $selectedBaleId;
                                $details[$i]['bales_qty'] = $qtySum;
                                $details[$i]['total'] = $pQty;
                                foreach ($selectedBale as $bale) {
                                    array_push($selectedBales, $bale['bale_id']);
                                }
                            }
                        } else {
                            $details[$i]['bale_id'] = $selectedBale['bale_id'];
                            $details[$i]['bale_qty'] = $selectedBale['stock'];
                            array_push($selectedBales, $selectedBale['bale_id']);
                        }
                    }
                }
            }
            $this->content['details'] = $details;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios o no se ha recibido el id de orden.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShoppingCartBaleDetailsWithoutStock ()
    {
        set_time_limit(0);
        if ($this->userHasPermission()) {
            $sql = "SELECT scbd.id, scbd.shopping_cart_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, scbd.product_id, p.code AS product_code, p.name AS product_name, scbd.qty
                    FROM sls_shopping_cart_bale_details AS scbd
                    INNER JOIN wms_products AS p ON p.id = scbd.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    WHERE scbd.status in ('AUTORIZADO') ;";
            $baleDetails = $this->db->query($sql)->fetchAll();

            $productsfromCart = ShoppingCartBaleDetails::find("status = 'AUTORIZADO'");

            $p = [];

            foreach($productsfromCart as $pr){
                $p1 = $pr->product_id;
                foreach ($productsfromCart as $secondMovement) {
                    if ($p1 != $secondMovement->product_id) {
                        array_push($p, $secondMovement->product_id);
                    }
                }
            }

            $addedBalesIds = [];
            $availableBales = [];
            $movements = new MovementsController();
            $baleMovements = $movements->generateMultiKardex(null, null, null, 5, 6, null, $productsfromCart, null, null);
                foreach ($baleMovements as $movement) {
                if (!in_array($movement['bale_id'], $addedBalesIds)) {
                    $baleStock = 0;
                    foreach ($baleMovements as $secondMovement) {
                        if ($movement['bale_id'] == $secondMovement['bale_id']) {
                            if ($secondMovement['movement_type'] == 1) {
                                $baleStock += $secondMovement['qty'];
                            } elseif ($secondMovement['movement_type'] == 2) {
                                $baleStock -= $secondMovement['qty'];
                                break;
                            }
                        }
                    }
                    if ($baleStock > 0) {
                        array_push($availableBales, array('bale_id' => $movement['bale_id'], 'category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'qty' => $baleStock));
                    }
                    array_push($addedBalesIds, $movement['bale_id']);
                }
            }

            $details = [];
            for ($i=0; $i < count($baleDetails); $i++) {
                $selectedBale = null;
                foreach ($availableBales as $bale) {
                    if ($bale['product_id'] == $baleDetails[$i]['product_id'] && $bale['qty'] >= $baleDetails[$i]['qty']) {
                        if (is_null($selectedBale)) {
                            $selectedBale = $bale;
                        } elseif ($bale['qty'] < $selectedBale['qty']) {
                            $selectedBale = $bale;
                        }
                    }
                }
                if (is_null($selectedBale)) {
                    $selectedBale = [];
                    $qtySum = 0;
                    foreach ($availableBales as $bale) {
                        if ($bale['product_id'] == $baleDetails[$i]['product_id']) {
                            if (count($selectedBale) == 0 || $qtySum < $baleDetails[$i]['qty']) {
                                array_push($selectedBale, $bale);
                                $qtySum += $bale['qty'];
                            }
                        }
                    }
                    if ($qtySum < $baleDetails[$i]['qty']) {
                        array_push($details, $baleDetails[$i]);
                    }
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
        var_dump("que hay");
        try {
            $request = $this->request->getPost();
            if ($this->userIsCustomer()) {
                $validUser = Auth::getUserData($this->config);
                if ($validUser && $validUser->id && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty'])) {
                    $tx = $this->transactions->get();

                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if ($shoppingCart) {
                        $detail = new ShoppingCartBaleDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Fibra agregada a carrito de compras correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la fibra al carrito de compras.');
                        }
                    }
                    else {
                        $shoppingCart = new ShoppingCart();
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->user_id = $validUser->id;
                        if ($shoppingCart->create()) {
                            $detail = new ShoppingCartBaleDetails();
                            $detail->setTransaction($tx);
                            $detail->shopping_cart_id = $shoppingCart->id;
                            $detail->product_id = $request['productId'];
                            $detail->qty = $request['qty'];
                            $detail->price_product = $request['price'];
                            if ($detail->create()) {
                                $this->content['result'] = true;
                                $this->content['detail'] = $detail;
                                $this->content['message'] = Message::success('Fibra agregada a carrito de compras correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($detail);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la fibra al carrito de compras.');
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
                        $detail = new ShoppingCartBaleDetails();
                        $detail->setTransaction($tx);
                        $detail->shopping_cart_id = $shoppingCart->id;
                        $detail->product_id = $request['productId'];
                        $detail->qty = $request['qty'];
                        $detail->price_product = $request['price'];
                        if ($detail->create()) {
                            $this->content['result'] = true;
                            $this->content['detail'] = $detail;
                            $this->content['message'] = Message::success('Fibra agregada a pedido correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la fibra al pedido.');
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

    public function generateProductionOrder ()
    {
        try {
            $request = $this->request->getPost();
            $validUser = Auth::getUserData($this->config);
            if ($validUser && $validUser->id && isset($request['productionDate']) && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty']) && isset($request['lotsQty']) && is_numeric($request['lotsQty'])) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                $product = Products::findFirst($request['productId']);
                if ($product && $product->active) {
                    $initialScheduledDate = date("Y-m-d",strtotime($request['productionDate']));
                    $sql = "SELECT id
                            FROM prd_lots
                            WHERE DATE(scheduled_start_date) = '".$initialScheduledDate."'
                            AND status <> 'CANCELADO';";
                    $lotsScheduledForTheDay = $this->db->query($sql)->fetchAll();

                    $lotsScheduledForTheDayFree = true;
                    $startsFirstTime = true;

                    if (sizeof($lotsScheduledForTheDay) == 0) {
                        // Empezar el día desde la primera hora
                        $requiredDays = ceil(intval($request['lotsQty']) / 2);
                        $finalScheduledDate = date("Y-m-d",strtotime($request['productionDate']." + ".($requiredDays-1)." days"));
                        $sql = "SELECT id
                                FROM prd_lots
                                WHERE DATE(scheduled_start_date) BETWEEN '".$initialScheduledDate."' AND '".$finalScheduledDate."'
                                AND status <> 'CANCELADO';";
                        $lotsScheduledForTheDays = $this->db->query($sql)->fetchAll();
                        if (sizeof($lotsScheduledForTheDays) > 0) {
                            $lotsScheduledForTheDayFree = false;
                        }
                    } elseif (sizeof($lotsScheduledForTheDay) == 1) {
                        // Empezar el día desde la última hora
                        $requiredDays = ceil(intval($request['lotsQty'] - 1) / 2);
                        $initialScheduledDate = date("Y-m-d",strtotime($request['productionDate']." + 1 days"));
                        $finalScheduledDate = date("Y-m-d",strtotime($request['productionDate']." + ".$requiredDays." days"));
                        $sql = "SELECT id
                                FROM prd_lots
                                WHERE DATE(scheduled_start_date) BETWEEN '".$initialScheduledDate."' AND '".$finalScheduledDate."'
                                AND status <> 'CANCELADO';";
                        $lotsScheduledForTheDays = $this->db->query($sql)->fetchAll();
                        if (sizeof($lotsScheduledForTheDays) > 0) {
                            $lotsScheduledForTheDayFree = false;
                        }
                        $startsFirstTime = false;
                    } else {
                        // El día seleccionado está completamente ocupado
                        $lotsScheduledForTheDayFree = false;
                    }

                    if (!$lotsScheduledForTheDayFree) {
                        $this->content['message'] = Message::error('La producción para esas fechas ya está programada, reagende por favor');
                        $successLots = false;
                    } else {
                        $sql = "SELECT order_number FROM prd_orders ORDER BY order_number DESC LIMIT 1;";
                        $lastNumber = $this->db->query($sql)->fetch()['order_number'];

                        $order = new ProductionOrders();
                        $order->setTransaction($tx);
                        $order->production_date = $request['productionDate'];
                        $order->product_id = $request['productId'];
                        $order->qty = $request['qty'];
                        $order->unit_id = 1;
                        if ($lastNumber < (date("Y").'0001')) {
                            $order->order_number = (date("Y").'0001');
                        } else {
                            $order->order_number = ++$lastNumber;
                        }
                        if ($order->create()) {
                            $successLots = true;
                            if (isset($request['lotsQty']) && is_numeric($request['lotsQty'])) {
                                $actions = Actions::findFirst(1);
                                $weight = $order->qty / $request['lotsQty'];
                                $dryerNumber = 1;
                                $actualTime = $startsFirstTime ? $actions->daily_production_time_1 : $actions->daily_production_time_2;
                                $actualDate = date("Y-m-d",strtotime($request['productionDate']));
                                for ($i = 1; $i <= intval($request['lotsQty']); $i++) {
                                    $lot = new ProductionLots();
                                    $lot->setTransaction($tx);
                                    $lot->order_id = $order->id;
                                    $lot->start_date = $order->production_date;
                                    $lot->product_id = $order->product_id;
                                    $lot->weight = $weight;
                                    $lot->scheduled_start_date = $actualDate.' '.$actualTime;
                                    if ($i > 9) {
                                        $lot->lot_number = $order->order_number.'-'.$i;
                                    } else {
                                        $lot->lot_number = $order->order_number.'-0'.$i;
                                    }
                                    if ($lot->create()) {
                                        if ($actualTime == $actions->daily_production_time_1) {
                                            $actualTime = $actions->daily_production_time_2;
                                        } else {
                                            $actualTime = $actions->daily_production_time_1;
                                            $actualDate = date("Y-m-d",strtotime($actualDate." + 1 days"));
                                        }
                                        $lotProcess = new ProductionLotProcesses();
                                        $lotProcess->setTransaction($tx);
                                        $lotProcess->process_id = 1;
                                        $lotProcess->lot_id = $lot->id;
                                        $lotProcess->dryer_number = $dryerNumber;
                                        $dryerNumber++;
                                        if ($lotProcess->create()) {
                                            $lotProcess = new ProductionLotProcesses();
                                            $lotProcess->setTransaction($tx);
                                            $lotProcess->process_id = 1;
                                            $lotProcess->lot_id = $lot->id;
                                            $lotProcess->dryer_number = $dryerNumber;
                                            $dryerNumber++;
                                            if ($lotProcess->create()) {
                                                $lotProcess = new ProductionLotProcesses();
                                                $lotProcess->setTransaction($tx);
                                                $lotProcess->process_id = 1;
                                                $lotProcess->lot_id = $lot->id;
                                                $lotProcess->dryer_number = $dryerNumber;
                                                $dryerNumber++;
                                                if ($lotProcess->create()) {
                                                    for ($j = 2; $j <= 6; $j++) {
                                                        $lotProcess = new ProductionLotProcesses();
                                                        $lotProcess->setTransaction($tx);
                                                        $lotProcess->process_id = $j;
                                                        $lotProcess->lot_id = $lot->id;
                                                        $lotProcess->dryer_number = null;
                                                        if (!$lotProcess->create()) {
                                                            $this->content['error'] = Helpers::getErrors($lot);
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear un proceso en un lote.');
                                                            $successLots = false;
                                                        }
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear un proceso en un lote.');
                                                    $successLots = false;
                                                }
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($lot);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear un proceso en un lote.');
                                                $successLots = false;
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lot);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear un proceso en un lote.');
                                            $successLots = false;
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($lot);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear un lote.');
                                        $successLots = false;
                                    }
                                }
                            }
                            if ($successLots) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La orden de producción ha sido creada con el número de orden: '.$order->order_number);
                                $shoppingCartBaleDetails = ShoppingCartBaleDetails::find("product_id = ".$request['productId']." AND status in ('SOLICITADO','AUTORIZADO')");
                                $succesShoppingCartBaleDetails = true;
                                foreach ($shoppingCartBaleDetails as $shoppingCartBaleDetail) {
                                    $shoppingCartBaleDetail->setTransaction($tx);
                                    $shoppingCartBaleDetail->status = 'ORDENADO';
                                    if (!$shoppingCartBaleDetail->update()) {
                                        $succesShoppingCartBaleDetails = false;
                                    }
                                }
                                if ($succesShoppingCartBaleDetails) {
                                    $tx->commit();
                                }
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de producción.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('El producto está inactivo.');
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
                        $detail = ShoppingCartBaleDetails::findFirst($id);
                        if ($detail && $detail->shopping_cart_id == $shoppingCart->id) {
                            $tx = $this->transactions->get();
                            $detail->setTransaction($tx);
                            if ($detail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La fibra ha sido eliminada del carrito de compras.');
                                $tx->commit();
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la fibra del carrito de compras.');
                            }
                        }
                    }
                }
            } elseif ($this->userHasPermission()) {
                $detail = ShoppingCartBaleDetails::findFirst($id);
                if ($detail) {
                    $shoppingCart = ShoppingCart::findFirst($detail->shopping_cart_id);
                    if ($shoppingCart->status == 'NUEVO') {
                        $tx = $this->transactions->get();
                        $detail->setTransaction($tx);
                        if ($detail->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La fibra ha sido eliminada del pedido.');
                            $tx->commit();
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la fibra del pedido.');
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
                    WHERE (role_id = 14 OR role_id = 16 OR role_id = 29)
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 4 OR role_id = 27 OR role_id = 22 OR role_id = 20 OR role_id = 17 OR role_id = 29)
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