<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;

class ProductionLotsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getLots ()
    {
        if ($this->userHasPermission()) {
            $this->content['lots'] = ProductionLots::find(['lot' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getLot ($id)
    {
        if (is_numeric($id)) {
            if ($this->userHasPermission()) {
                        $sql="SELECT l.id, l.order_id,order_number, l.lot_number, l.product_id, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS scheduled_start_date,wc.id as category_id, wc.code as category_code, wc.name as category_name,
                        l.weight, p.name AS product, l.status, l.movement_id, l.raw_material_movement_id, l.raw_material_return_movement_id, o.product_id AS order_product_id, l.volume, l.strength,l.rebound, l.spring, l.line, l.customer_id, c.name AS customer ,l.qty_real
                        FROM prd_lots AS l 
                        INNER JOIN wms_products AS p ON l.product_id = p.id 
                        INNER JOIN prd_orders AS o ON l.order_id = o.id 
                        LEFT JOIN sls_customers AS c ON c.id = l.customer_id 
                        left join wms_lines as wl on wl.id = p.line_id
                        left join wms_categories as wc on wc.id= wl.category_id
                        WHERE l.id = $id   LIMIT 1;";
                        /*print_r($sql);
                        exit();*/
                        $lot = $this->db->query($sql)->fetch();
                        $bom = ProductionLots::findFirst($id);
                        $sql="SELECT prd_orders.id,prd_orders.id as order_id,prd_orders.order_number, 
                        prd_orders.production_date, prd_orders.updated, prd_orders.updated_by, 
                        wms_bom.material_id,prd_orders.product_id as bom_products_id, wms_products.name as product_name, 
                        CONCAT(wms_categories.code,'-',wms_lines.code,'-',wms_products.code)  as product_code, wms_lines.name as line_name,
                        wms_lines.code as line_code, wms_lines.id as line_id, wms_categories.name as category_name, 
                        wms_categories.code as category_code, wms_bom.amount,prd_lots.weight as qty,
                        sum(wms_bom.amount * prd_lots.weight) as stock, sum(0) as existencia,prd_lots.qty_real
                        FROM wms_bom 
                        inner join wms_products on wms_products.id = wms_bom.material_id 
                        inner join wms_lines on wms_lines.id = wms_products.line_id 
                        inner join wms_categories on wms_categories.id = wms_lines.category_id 
                        inner join prd_lots on prd_lots.product_id =wms_bom.product_id
                        inner join prd_orders on prd_lots.order_id = prd_orders.id 
                        where prd_lots.id = $id
                        group by wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id, wms_bom.amount,prd_lots.weight,prd_lots.qty_real,
                        prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code, prd_orders.id, wms_bom.id 
                        order by wms_bom.id";

                        $detalles = $this->db->query($sql)->fetchAll();
                        $aux=0;
                        $cantidades_suficientes = 'si';
                        $bom_by_producto = [];
                        $producto = "";
                        $products = [];
                        if($detalles){
                            foreach ($detalles as $bom_producto) {
                                $bomcontroller = new BomController();
                                $b=(object)array();
                                $b->id=$bom_producto['id'];
                                $b->production_date=$bom_producto['production_date'];
                                $b->updated=$bom_producto['updated'];
                                $b->updated_by=$bom_producto['updated_by'];
                                $b->order_id = $bom_producto['order_id'];
                                $b->material_id = $bom_producto['material_id'];
                                $producto_existencia = $bom_producto['material_id'];
                                $b->bom_products_id = $bom_producto['bom_products_id'];
                                $b->product_name = $bom_producto['product_name'];
                                $b->product_code = $bom_producto['product_code'];
                                $b->line_name = $bom_producto['line_name'];
                                $b->line_code = $bom_producto['line_code'];
                                $b->line_id = $bom_producto['line_id'];
                                $b->category_name = $bom_producto['category_name'];
                                $almacen = $bom_producto['category_code'];
                                $b->category_code = $bom_producto['category_code'];
                                $b->amount = $bom_producto['amount'];
                                $b->qty = $bom_producto['qty'];
                                $b->qty_real = $bom_producto['qty_real'];
                                if (!in_array($bom_producto['material_id'], $products)) {
                                    $lastPrice = 0;
                                    foreach ($detalles as $secondDetail) {
                                        if ($bom_producto['material_id'] == $secondDetail['material_id']) {
                                            $b->lastprice = $bomcontroller->getLastProductCost($bom_producto['material_id']);
                                        }
                                    }
                                    array_push($products, $bom_producto['material_id']);
                                }
                                $b->stock = $bom_producto['stock'];



                                 //$sql_existencias = "SELECT * from get_existencias(28, $producto_existencia, null, null)";
                                //$existencia = $this->db->query($sql_existencias)->fetchAll();
                                $existencia = $this->getKardex(null, null, null, 34, $producto_existencia);

                    /*echo('<pre>');
                    print_r("product ".$producto_existencia." ".$kardexAux." ");
                    print_r("product ".$producto_existencia." ".$existencia[0]['existencia']." ");
                    exit();*/
                                // $sql_existencias = "SELECT * from get_existencias(28, $producto_existencia, null, null)";

                   // $existencia = $this->db->query($sql_existencias)->fetchAll();
                    if ($existencia > 0) {
                        $b->existencia = $existencia;
                    } else {
                        $b->existencia = 0;
                        // $aux++;
                    }
                                if ($bom_producto['stock'] > $b->existencia) {
                                    $cantidades_suficientes = 'no';
                                    $b->cantidades_suficientes = $cantidades_suficientes;
                                    $product = Products::findFirst($producto_existencia)->name;
                                }
                                array_push($bom_by_producto, $b);
                            }
                           
// /**
                //$this->response->setJsonContent($this->content);
            } else {
                $this->content['message'] = Message::error('No hay bom.');
            }

             $this->content['result'] = true;
             $this->content['bom'] = $bom_by_producto;
             $this->content['lot'] = $lot;
        } else {
            $this->content['result'] = false;
            $this->response->setJsonContent($this->content);
        }
    }
    $this->response->setJsonContent($this->content);
}
public function getLotsByOrderId ($orderId)
{
    if ($this->userHasPermission()) {
        $lots = [];
        if (!is_null($orderId) && is_numeric($orderId)) {
            $sql = "SELECT (select count(id) from prd_lots where status !='CANCELADO' AND order_id = $orderId) AS tot,l.id, 
                    l.order_id, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS scheduled_start_date, l.lot_number, l.product_id, 
                    l.weight, p.name AS product, l.status, l.volume, l.strength, l.rebound, l.spring, l.line, l.customer_id, 
                    c.name AS customer,l.qty_real,s.name as shift
            FROM prd_lots AS l
            INNER JOIN wms_products AS p
            ON p.id = l.product_id
            LEFT JOIN sls_customers AS c
            ON c.id = l.customer_id
            LEFT JOIN hrs_shifts AS s
            ON s.id = l.shift_id
            WHERE order_id = $orderId
            ORDER BY id ASC;";
                        //print_r($sql);
                        //exit();
            $auxLots = $this->db->query($sql)->fetchAll();
            foreach ($auxLots as $lot) {
                $sql = "SELECT finish_date
                FROM prd_lot_processes
                WHERE lot_id = ".$lot['id']."
                AND finish_date IS NOT NULL
                ORDER BY process_id DESC, dryer_number DESC
                LIMIT 1;";
                            // print_r($sql);
                            // exit();
                $finishDate = $this->db->query($sql)->fetch();
                if ($finishDate) {
                    $lot['finish_date'] = $finishDate['finish_date'];
                }
                array_push($lots, $lot);
            }
            $this->content['result'] = true;
        }
        $this->content['lots'] = $lots;
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }
    $this->response->setJsonContent($this->content);
}

public function getLotsByScheduledStartDate ($date)
{
    if ($this->userHasPermission()) {
        $lots = [];
        if (!is_null($date)) {
            $sql = "SELECT l.id, l.order_id, TO_CHAR(l.scheduled_start_date, 'yyyy/mm/dd HH24:MI') AS scheduled_start_date, TO_CHAR(slp.start_date, 'yyyy/mm/dd HH24:MI') AS start_date, l.lot_number, l.product_id, l.weight, p.name AS product, l.status
            FROM prd_lots AS l
            INNER JOIN wms_products AS p
            ON p.id = l.product_id
            INNER JOIN prd_lot_processes AS slp
            ON slp.lot_id = l.id AND slp.process_id = 1 AND (slp.dryer_number % 3) = 1
            WHERE TO_CHAR(l.scheduled_start_date, 'yyyy-mm-dd') = '$date'
            AND l.status <> 'CANCELADO'
            ORDER BY id ASC;";
            $auxLots = $this->db->query($sql)->fetchAll();
            foreach ($auxLots as $lot) {
                $sql = "SELECT finish_date
                FROM prd_lot_processes
                WHERE lot_id = ".$lot['id']."
                AND finish_date IS NOT NULL
                ORDER BY process_id DESC, dryer_number DESC
                LIMIT 1;";
                $finishDate = $this->db->query($sql)->fetch();
                if ($finishDate) {
                    $lot['finish_date'] = $finishDate['finish_date'];
                }
                array_push($lots, $lot);
            }
            $this->content['result'] = true;
        }
        $this->content['lots'] = $lots;
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }
    $this->response->setJsonContent($this->content);
}

public function getFinishedProductsByLotId ($lotId)
{
    if ($this->userHasPermission()) {
        if (!is_null($lotId)) {
            $sql = "SELECT md.id, md.bale_id, md.product_id, md.qty AS weight, p.name AS product, TO_CHAR(md.created, 'dd/mm/yyyy HH24:MI') AS date, u.nickname AS created_by
            FROM prd_lots AS l
            INNER JOIN wms_movement_details AS md
            ON md.movement_id = l.movement_id
            INNER JOIN wms_products AS p
            ON p.id = md.product_id
            INNER JOIN sys_users AS u
            ON u.id = md.created_by
            WHERE l.id = $lotId;";
            $finishedProducts = $this->db->query($sql)->fetchAll();
            $this->content['finishedProducts'] = $finishedProducts;
            $this->content['result'] = true;
        } else {
            $this->content['finishedProducts'] = [];
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }
    $this->response->setJsonContent($this->content);
}

public function getRawMaterialsByLotId ($lotId)
{
    if ($this->userHasPermission()) {
        if (!is_null($lotId) && is_numeric($lotId)) {
            $actualRawMaterials = array();
            $sql = "SELECT md.id, md.product_id, p.name AS product, md.bag_id, sd.id AS bag_id, sd.qty AS bag_qty, s.receive_date AS bag_receive_date, TO_CHAR(md.created, 'dd/mm/yyyy HH24:MI') AS date, sd.qty AS weight, m.status, md.created AS creation_date
            FROM prd_lots AS l
            INNER JOIN wms_movements AS m
            ON m.id = l.raw_material_movement_id
            INNER JOIN wms_movement_details AS md
            ON md.movement_id = l.raw_material_movement_id
            INNER JOIN wms_products AS p
            ON p.id = md.product_id
            INNER JOIN pur_shipment_details AS sd
            ON sd.id = md.bag_id
            INNER JOIN pur_shipments AS s
            ON s.id = sd.shipment_id
            WHERE l.id = $lotId;";
            $rawMaterials = $this->db->query($sql)->fetchAll();
            foreach ($rawMaterials as $rawMaterial) {
                $sql = "SELECT md.qty AS actual_qty, md.created
                FROM prd_lots AS l
                INNER JOIN wms_movements AS m
                ON m.id = l.raw_material_return_movement_id
                INNER JOIN wms_movement_details AS md
                ON md.movement_id = m.id
                WHERE md.bag_id = ".$rawMaterial['bag_id']."
                AND l.id <> $lotId
                AND m.status = 1";
                if ($rawMaterial['status'] == 1) {
                    $sql .= " AND md.created < '".$rawMaterial['creation_date']."'";
                }
                $sql .= ' ORDER BY md.created DESC
                LIMIT 1;';
                $currentRawMaterialStock = $this->db->query($sql)->fetch();
                if ($currentRawMaterialStock && !is_null($currentRawMaterialStock['actual_qty']) && $currentRawMaterialStock['actual_qty'] >= 0) {
                    if ($rawMaterial['status'] == 1) {
                        $rawMaterial['weight'] = $currentRawMaterialStock['actual_qty'];
                    } else {
                        $sql = "SELECT md.id, md.movement_id, md.product_id, md.qty, md.bag_id, md.created
                        FROM prd_lots AS l
                        INNER JOIN wms_movements AS m
                        ON m.id = l.raw_material_movement_id
                        INNER JOIN wms_movement_details AS md
                        ON md.movement_id = m.id
                        WHERE m.status = 1
                        AND md.bag_id = ".$rawMaterial['bag_id']."
                        AND md.created > '".$currentRawMaterialStock['created']."';";
                        $returnedRawMaterialsConsumption = $this->db->query($sql)->fetchAll();
                        if (count($returnedRawMaterialsConsumption) > 0) {
                            $rawMaterial['weight'] = 0;
                        } else {
                            $rawMaterial['weight'] = $currentRawMaterialStock['actual_qty'];
                        }
                    }
                }
                $sql = "SELECT md.qty AS returned_qty, md.created
                FROM prd_lots AS l
                INNER JOIN wms_movements AS m
                ON m.id = l.raw_material_return_movement_id
                INNER JOIN wms_movement_details AS md
                ON md.movement_id = m.id
                WHERE md.bag_id = ".$rawMaterial['bag_id']."
                AND l.id = $lotId
                AND m.status = 1";
                $returnedRawMaterial = $this->db->query($sql)->fetch();
                if ($returnedRawMaterial) {
                    $rawMaterial['weight'] -= $returnedRawMaterial['returned_qty'];
                }
                $rawMaterial['bag'] = 'Saco '.$rawMaterial['bag_id'];
                array_push($actualRawMaterials, $rawMaterial);
            }
            $this->content['rawMaterials'] = $actualRawMaterials;
            $this->content['result'] = true;
            $this->response->setJsonContent($this->content);
        } else {
            $this->content['rawMaterials'] = [];
            $this->content['result'] = false;
            $this->response->setJsonContent($this->content);
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }
}

public function getReturnedRawMaterialsByLotId ($lotId)
{
    if ($this->userHasPermission()) {
        if (!is_null($lotId)) {
            $actualReturnedRawMaterials = array();
            $sql = "SELECT rmmd.id, rmmd.product_id, p.name AS product, rmmd.bag_id, sd.qty AS bag_qty, s.receive_date AS bag_receive_date, TO_CHAR(rmmd.created, 'dd/mm/yyyy HH24:MI') AS date, sd.qty AS weight, rmrmd.qty AS returned_qty, m.status, rmmd.created AS creation_date
            FROM prd_lots AS l
            INNER JOIN wms_movement_details AS rmmd
            ON rmmd.movement_id = l.raw_material_movement_id
            INNER JOIN wms_movements AS m
            ON m.id = rmmd.movement_id
            INNER JOIN wms_products AS p
            ON p.id = rmmd.product_id
            INNER JOIN pur_shipment_details AS sd
            ON sd.id = rmmd.bag_id
            INNER JOIN pur_shipments AS s
            ON s.id = sd.shipment_id
            LEFT JOIN wms_movement_details AS rmrmd
            ON rmrmd.movement_id = l.raw_material_return_movement_id
            AND rmrmd.bag_id = rmmd.bag_id
            WHERE l.id = $lotId;";
            $returnedRawMaterials = $this->db->query($sql)->fetchAll();
            foreach ($returnedRawMaterials as $returnedRawMaterial) {
                $sql = "SELECT md.id, md.bag_id, md.qty
                FROM prd_lots AS l
                INNER JOIN wms_movement_details AS md
                ON md.movement_id = l.raw_material_return_movement_id
                WHERE l.id <> $lotId";
                if ($returnedRawMaterial['status'] == 1) {
                    $sql .= " AND md.created < '".$returnedRawMaterial['creation_date']."'";
                }
                $sql .= " AND md.bag_id = ".$returnedRawMaterial['bag_id']."
                ORDER BY md.created DESC
                LIMIT 1;";
                $lastReturnedRawMaterial = $this->db->query($sql)->fetch();
                if ($lastReturnedRawMaterial && !is_null($lastReturnedRawMaterial['qty']) && $lastReturnedRawMaterial['qty'] > 0) {
                    $returnedRawMaterial['weight'] = $lastReturnedRawMaterial['qty'];
                }
                $returnedRawMaterial['bag'] = 'Saco '.$returnedRawMaterial['bag_id'].' ('.$returnedRawMaterial['weight'].' Kg.) ['.$returnedRawMaterial['date'].']';
                array_push($actualReturnedRawMaterials, $returnedRawMaterial);
            }
            $this->content['returnedRawMaterials'] = $actualReturnedRawMaterials;
            $this->content['result'] = true;
            $this->response->setJsonContent($this->content);
        } else {
            $this->content['returnedRawMaterials'] = [];
            $this->content['result'] = false;
            $this->response->setJsonContent($this->content);
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }
}
public function executelot ($id) {
    try {
        if ($this->userHasPermission()) {

            $tx = $this->transactions->get();
            $request = $this->request->getPost();

            //echo('<pre>');
            //print_r($request);
            //echo('</pre>');
            //$oid = $request['order_id'];
            //exit();
            
            //$LoteSql = "SELECT * from prd_lots where order_id = $oid AND  (status = 'NUEVO' OR status = 'INICIADO') order by id desc";
            //$ultimo = $this->db->query($LoteSql)->fetch();

            /*echo('<pre>');
            print_r($request['id']);
             print_r($ultimo['id']);
            echo('</pre>');
            exit();*/

            //if($ultimo && ($ultimo['id'] == $request['id'])){
            $sql="SELECT prd_orders.id,prd_orders.id as order_id,prd_orders.order_number, 
            prd_orders.production_date, prd_orders.updated, prd_orders.updated_by, 
            wms_bom.material_id,prd_orders.product_id as bom_products_id,prd_lots.product_id as lot_product_id, wms_products.name as product_name, 
            wms_products.code as product_code, wms_lines.name as line_name,
            wms_lines.code as line_code, wms_lines.id as line_id, wms_categories.name as category_name, 
            wms_categories.code as category_code, wms_bom.amount,prd_lots.weight as qty,
            sum(wms_bom.amount * prd_lots.weight) as stock
            FROM wms_bom 
            inner join wms_products on wms_products.id = wms_bom.material_id 
            inner join wms_lines on wms_lines.id = wms_products.line_id 
            inner join wms_categories on wms_categories.id = wms_lines.category_id 
            inner join prd_lots on prd_lots.product_id =wms_bom.product_id
            inner join prd_orders on prd_lots.order_id = prd_orders.id 
            where prd_lots.id = $id
            group by prd_lots.product_id,wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id, wms_bom.amount,prd_lots.weight,
            prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code, prd_orders.id, wms_bom.id 
            order by wms_bom.id";

            //print_r($sql);
            //exit();

            $detalles = $this->db->query($sql)->fetchAll();
            $aux=0;
            $cantidades_suficientes = 'si';
            $bom_by_producto = [];
            $producto = "";
            if($detalles){
                foreach ($detalles as $bom_producto) {
                    $b=(object)array();
                    $b->id=$bom_producto['id'];
                    $b->production_date=$bom_producto['production_date'];
                    $b->updated=$bom_producto['updated'];
                    $b->updated_by=$bom_producto['updated_by'];
                    $b->order_id = $bom_producto['order_id'];
                    $b->material_id = $bom_producto['material_id'];
                    $producto_existencia = $bom_producto['material_id'];
                    $b->bom_products_id = $bom_producto['bom_products_id'];
                    $b->product_name = $bom_producto['product_name'];
                    $b->product_code = $bom_producto['product_code'];
                    $b->line_name = $bom_producto['line_name'];
                    $b->line_code = $bom_producto['line_code'];
                    $b->line_id = $bom_producto['line_id'];
                    $b->category_name = $bom_producto['category_name'];
                    $almacen = $bom_producto['category_code'];
                    $b->category_code = $bom_producto['category_code'];
                    $b->amount = $bom_producto['amount'];
                    $b->qty = $bom_producto['qty'];
                    $b->stock = $bom_producto['stock'];
                    //$sql_existencias = "SELECT * from get_existencias(28, $producto_existencia, null, null)";
                    $existencia = $this->getKardex(null, null, null, 34, $producto_existencia);
                    //$existencia = $this->db->query($sql_existencias)->fetchAll();

                    if ($existencia > 0) {
                        $b->existencia = $existencia;
                    } else {
                        $b->existencia = 0;
                    }

                    /*$sql_existencias = "SELECT * from get_existencias(28, $producto_existencia, null, null)";

                    $existencia = $this->db->query($sql_existencias)->fetchAll();
                    if (count($existencia) > 0) {
                        $b->existencia = $existencia[0]['existencia'];
                    } else {
                        $b->existencia = 0;
                    }*/
                    if ($bom_producto['stock'] > $b->existencia) {
                        $cantidades_suficientes = 'no';
                        $b->cantidades_suficientes = $cantidades_suficientes;
                        $product = Products::findFirst($producto_existencia)->name;
                    }
                    array_push($bom_by_producto, $b);
                }


                if($cantidades_suficientes == 'si'){

                    $tx = $this->transactions->get();
                    $branchTransferX = [];
                    $transaction = new Transactions();
                    $transaction->setTransaction($tx);

                    if ($transaction->create()) {

                        $exitMovement = new Movements();
                        $exitMovement->setTransaction($tx);
                        $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $exitMovement->date = date("Y-m-d H:i:s");
                        $exitMovement->status = 'NUEVO';
                        $exitMovement->storage_id = intval(34);
                        $exitMovement->type_id = 5;
                        $exitMovement->po_id = intval($request['order_id']);
                        $exitMovement->transaction_id = $transaction->id;
                        if ($exitMovement->create()) {

                            $entryMovement = new Movements();
                            $entryMovement->setTransaction($tx);
                            $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                            $entryMovement->date = date("Y-m-d H:i:s");
                            $entryMovement->status= 'NUEVO';
                            $entryMovement->storage_id = intval(35);
                            $entryMovement->movement_id = $exitMovement->id;
                            $entryMovement->type_id = 4;
                            $entryMovement->po_id = intval($request['order_id']);
                            $entryMovement->transaction_id = $transaction->id;

                            if ($entryMovement->create()) {

                                $branchTransfer = new BranchTransfers();
                                $branchTransfer->setTransaction($tx);
                                $branchTransfer->transaction_id = $transaction->id;

                                if ($branchTransfer->create()) {

                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('Transferencia registrada correctamente.');
                                    $this->content['branchTransfer'] = $exitMovement;

                                    foreach ($bom_by_producto as $detail) {
                                        $product = Products::findFirst($detail->material_id);
                                        $movementDetailexit = new MovementDetails();
                                        $tx = $this->transactions->get();
                                        $movementDetailexit->setTransaction($tx);
                                        $movementDetailexit->movement_id = $exitMovement->id;
                                        $movementDetailexit->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                        $movementDetailexit->qty = $detail->stock;

                                        if (!$product->active || !$movementDetailexit->create()) {

                                            $this->content['error'] = Helpers::getErrors($movementDetailexit);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                            $tx->rollback();
                                        }
                                    }

                                    $tx = $this->transactions->get();
                                    $movementId= $exitMovement->id;
                                    // print_r();
                                    //exit()
                                    $movement = Movements::findFirst($movementId);
                                    $movement->setTransaction($tx);
                                    $movement->status = 'EJECUTADO';
                                    $movement->ejecute_date = date('Y-m-d H:i:s');

                                    if ($movement->update()) {

                                        $movement2 = Movements::findFirst("movement_id = $movementId");
                                        if($movement2){
                                            $movement2->setTransaction($tx);
                                            $movement2->status = 'EJECUTADO';
                                            $movement2->ejecute_date = date('Y-m-d H:i:s');
                                            $movement2->update();  
                                        }
                                        $idIn = $movementId-1;
                                        $movement3 = Movements::findFirst("id = $idIn AND type_id = 5");
                                        if($movement3){
                                            $movement3->setTransaction($tx);
                                            $movement3->status = 'EJECUTADO';
                                            $movement3->ejecute_date = date('Y-m-d H:i:s');
                                            $movement3->update(); 
                                        }
                                        $this->content['result'] = true;
                                        $this->content['movement'] = $movement;
                                        $this->content['message'] = Message::success('El movimiento ha sido ejecutado');
                                        $tx->commit();
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['error'] = Helpers::getErrors($movement);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento');
                                        $tx->rollback();
                                    }

                                    $tx = $this->transactions->get();

                        $exist_lot= ProductionLots::findFirst($id);
                        $tx = $this->transactions->get();
                        $exist_lot->setTransaction($tx);
                        $exist_lot->status = 'INICIADO';
                        $exist_lot->raw_material_movement_id = $exitMovement->id;

                        if($exist_lot->update()){
                            $order= ProductionOrders::findFirst($request['order_id']);
                            $tx = $this->transactions->get();
                            $order->setTransaction($tx);
                            $order->status = 'INICIADO';
                            if($order->update()){   
                                $tx->commit();
                            }
                           $this->content['result'] = true;
                           $this->content['movement'] = $exist_lot;
                           $this->content['message'] = Message::success('Lote Iniciado Correctamente');
                           $tx->commit();

                       } else{
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($exist_lot);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el lote');
                        $tx->rollback();
                    }


                        } else {
                            $this->content['result'] = false;
                            $this->content['error'] = Helpers::getErrors($lot);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['error'] = Helpers::getErrors($lot);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                    $tx->rollback();
                }
            } else {
                $this->content['result'] = false;
                $this->content['error'] = Helpers::getErrors($lot);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                $tx->rollback();
            }
                    // $this->content['result'] = true;
                    // $this->content['message'] = Message::success('PRODUCCIÓN ');

        } else {
            $this->content['result'] = false;
            $this->content['message'] = Message::success('Producto insuficiente: '.$product);
            $this->content['cantidad_suficiente'] = $cantidades_suficientes;
        }
    } else {
        $this->content['result'] = false;
        $this->content['message'] = Message::success('No existe el bom de este producto');
    }
            // print( "<pre>".print_r( $bom_by_producto, true)."</pre>");
            // exit();

/*}else {
            $this->content['result'] = false;
            $this->content['message'] = Message::success('Comienze la produccion por el ultimo lote');
          
}
*/
}else {
$this->content['result'] = false;
$this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
}

} catch (Exception $e) {

    $this->content['errors'] = Message::exception($e);
}

$this->response->setJsonContent($this->content);
}

public function getfinalizelot ($id) {
            $request = $this->request->getPost();
            $tx = $this->transactions->get();
            $oid = $request['order_id'];
            $LoteSql = "SELECT * from prd_lots where order_id = $oid  order by id asc";
            /*echo("<pre>");
            print_r($request);
            print_r($LoteSql);
            echo("</pre>");
            exit();*/
            $ultimo = $this->db->query($LoteSql)->fetch();
            if($ultimo && ($ultimo['id'] == $request['id']) && $ultimo['status'] == 'INICIADO'){
                $this->content['result'] = true;
                $this->content['finalizeLot'] = $ultimo;
                $this->content['message'] = Message::success('');

            }

            $this->response->setJsonContent($this->content);

}

public function finalizeLot ($id) {
    try {
        
        if ($this->userHasPermission()) {

            $request = $this->request->getPost();
            $tx = $this->transactions->get();
                //$tx = $this->transactions->get();
                // $request = $this->request->getPost();
                 //print_r($id);
                 // exit();
            //$oid = $request['order_id'];
            //$LoteSql = "SELECT * from prd_lots where order_id = $oid AND  status = 'EJECUTADO' order by id desc";
            //$ultimo = $this->db->query($LoteSql)->fetch();

           /* echo('<pre>');
            print_r($request['id']);
             print_r($ultimo['id']);
            echo('</pre>');
            exit();*/

            // if($ultimo && ($ultimo['id'] == $request['id'])){
            $sql="SELECT prd_orders.id,prd_orders.id as order_id,prd_orders.order_number, 
            prd_orders.production_date, prd_orders.updated, prd_orders.updated_by, 
            wms_bom.material_id,prd_orders.product_id as bom_products_id,prd_lots.product_id as lot_product_id, wms_products.name as product_name, 
            wms_products.code as product_code, wms_lines.name as line_name,
            wms_lines.code as line_code, wms_lines.id as line_id, wms_categories.name as category_name, 
            wms_categories.code as category_code, wms_bom.amount,prd_lots.weight as qty,
            sum(wms_bom.amount * prd_lots.weight) as stock
            FROM wms_bom 
            inner join wms_products on wms_products.id = wms_bom.material_id 
            inner join wms_lines on wms_lines.id = wms_products.line_id 
            inner join wms_categories on wms_categories.id = wms_lines.category_id 
            inner join prd_lots on prd_lots.product_id =wms_bom.product_id
            inner join prd_orders on prd_lots.order_id = prd_orders.id 
            where prd_lots.id = $id
            group by prd_lots.product_id,wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id, wms_bom.amount,prd_lots.weight,
            prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code, prd_orders.id, wms_bom.id 
            order by wms_bom.id";

                      //print_r($sql);
                      //exit();

            $detalles = $this->db->query($sql)->fetchAll();
            $aux=0;
            $cantidades_suficientes = 'si';
            $bom_by_producto = [];
            $producto = "";
            if($detalles){
                foreach ($detalles as $bom_producto) {
                    $b=(object)array();
                    $b->id=$bom_producto['id'];
                    $b->production_date=$bom_producto['production_date'];
                    $b->updated=$bom_producto['updated'];
                    $b->updated_by=$bom_producto['updated_by'];
                    $b->order_id = $bom_producto['order_id'];
                    $b->material_id = $bom_producto['material_id'];
                    $producto_existencia = $bom_producto['material_id'];
                    $b->bom_products_id = $bom_producto['bom_products_id'];
                    $b->product_name = $bom_producto['product_name'];
                    $b->product_code = $bom_producto['product_code'];
                    $b->line_name = $bom_producto['line_name'];
                    $b->line_code = $bom_producto['line_code'];
                    $b->line_id = $bom_producto['line_id'];
                    $b->category_name = $bom_producto['category_name'];
                    $almacen = $bom_producto['category_code'];
                    $b->category_code = $bom_producto['category_code'];
                    $b->amount = $bom_producto['amount'];
                    $b->qty = $bom_producto['qty'];
                    //$b->qty = $bom_producto['stock'];
                    $b->stock = $bom_producto['stock'];
                    $product_finalizado =$bom_producto['lot_product_id'];
                    $product_finalizado_qty =$bom_producto['qty'];
                    $total_necesario =$bom_producto['stock'];

                    

                    // $sql_existencias = "SELECT * from get_existencias(31, $producto_existencia, null, null)";
                    $existencia = $this->getKardex(null, null, null, 35, $producto_existencia);
                    // $existencia = $this->db->query($sql_existencias)->fetchAll();
                    if ($existencia > 0) {
                        $b->existencia = $existencia;
                    } else {
                        $b->existencia = 0;
                    }
                    /*$sql_existencias = "SELECT * from get_existencias(31, $producto_existencia, null, null)";
                    $existencia = $this->db->query($sql_existencias)->fetchAll();
                    if (count($existencia) > 0) {
                        $b->existencia = $existencia[0]['existencia'];
                    } else {
                        $b->existencia = 0;
                    }*/
                    if ($bom_producto['stock'] > $b->existencia) {
                        $cantidades_suficientes = 'no';
                        $b->cantidades_suficientes = $cantidades_suficientes;
                        // print_r($producto_existencia);
                        $product = Products::findFirst($producto_existencia)->name;
                        // print_r($product);
                        // exit();
                    }
                    if ($bom_producto['stock'] == $b->existencia) {
                        $cantidades_suficientes = 'si';
                        $b->cantidades_suficientes = $cantidades_suficientes;
                        // print_r($producto_existencia);
                        $product = Products::findFirst($producto_existencia)->name;
                        // print_r($product);
                        // exit();
                    } 
                    array_push($bom_by_producto, $b);
                }
               //echo("<pre>");
               //print_r($bom_by_producto);
               //echo("</pre>");
               //exit();
               // foreach ($bom_by_producto as $detail) {
               //     echo("<pre>");
               //     print_r($detail);
               //     echo("</pre>");
               // }

               //exit();
                if($cantidades_suficientes == 'si'){
                    // $request = $this->request->getPost();
                    $tx = $this->transactions->get();



              /*  $order = ProductionLots::findFirst($id);
                $order->setTransaction($tx);
                $order->status = 'FINALIZADO';
                if($order->update()){
                    $this->content['result'] = true;
                    $this->content['movement'] = $order;
                    $this->content['message'] = Message::success('Orden Finalizada');
*/

                    $exitMovement = new Movements();
                    $exitMovement->setTransaction($tx);
                        $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $exitMovement->date = date("Y-m-d H:i:s");
                        $exitMovement->status = 'EJECUTADO';
                        $exitMovement->ejecute_date = date('Y-m-d H:i:s');
                        $exitMovement->storage_id = intval(35);
                        $exitMovement->type_id = 2;
                        $exitMovement->po_id = intval($request['order_id']);
                        // $exitMovement->transaction_id = $transaction->id;
                        if ($exitMovement->create()) {
                            foreach ($bom_by_producto as $detail) {
                                        //$product = Products::findFirst($detail->material_id);
                                $movementDetailexit = new MovementDetails();
                                $tx = $this->transactions->get();
                                $movementDetailexit->setTransaction($tx);
                                $movementDetailexit->movement_id = $exitMovement->id;
                                $movementDetailexit->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                $movementDetailexit->qty = $detail->stock;
                                if (!$movementDetailexit->create()) {

                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($movementDetailexit);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                    $tx->rollback();
                                }else {
                                    $this->content['result'] = true;
                                    $this->content['movement'] = $movementDetailexit;
                                    $this->content['message'] = Message::success('detalle guardado correctamente');

                                    $tx->commit();
                                }
                            }
                            
                        }else{

                        }
                        $entryMovement = new Movements();
                        $entryMovement->setTransaction($tx);
                        $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $entryMovement->date = date("Y-m-d H:i:s");
                        $entryMovement->status= 'EJECUTADO';
                        $entryMovement->storage_id = intval(34);
                        // $entryMovement->movement_id = $exitMovement->id;
                        $entryMovement->type_id = 1;
                        $entryMovement->po_id = intval($request['order_id']);
                        // $entryMovement->transaction_id = $transaction->id;
                        if ($entryMovement->create()) {
                            $movementDetailentry = new MovementDetails();
                            $tx = $this->transactions->get();
                            $movementDetailentry->setTransaction($tx);
                            $movementDetailentry->movement_id = $entryMovement->id;
                            $movementDetailentry->product_id = $product_finalizado;
                            //$movementDetail->bag_id = $detail['bag_id'];
                            $movementDetailentry->qty = $product_finalizado_qty;
                            if ($movementDetailentry->create()) {
                                $tx = $this->transactions->get();
                                $order = ProductionLots::findFirst($id);
                                $order->setTransaction($tx);
                                $order->movement_id =$entryMovement->id;
                                $order->raw_material_delete_movement_id=$exitMovement->id;
                                $order->qty_real = $product_finalizado_qty ;
                                $order->status = 'FINALIZADO';
                                if($order->update()){
                                    $this->content['result'] = true;
                                    $this->content['movement'] = $order;
                                    $this->content['message'] = Message::success('lote Finalizado');
                                    $tx->commit();
                                } else {
                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar finalizar el lote');
                                    $tx->rollback();
                                }
                                $this->content['result'] = true;
                                $this->content['movement'] = $movementDetailentry;
                                $this->content['message'] = Message::success('Lote Produccion finalizada');

                            //$tx->rollback();
                                $tx->commit();
                            }else {
                                $this->content['result'] = false;
                                $this->content['error'] = Helpers::getErrors($movementDetailentry);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                $tx->rollback();
                            }
                        }

                        //TRASPASO DE MATERIAL SOBRANTE 


                        //$tx->commit();

                /*} else{
                    $this->content['error'] = Helpers::getErrors($order);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar la orden');
                    $tx->rollback();
                }*/


            } else {
                $this->content['result'] = false;
                 $this->content['message'] = Message::success('No hay suficiente existencias en producción');   
            }
        }
    /*} else {
      $this->content['result'] = false;
       $this->content['message'] = Message::success('Finalize la prodcuccion por el ultimo lote');   
    }*/
    } else {

    }

} catch (Exception $e) {

    $this->content['errors'] = Message::exception($e);
}

$this->response->setJsonContent($this->content);
}


public function finalizeLotReturMaterial ($id) {
    try {
        if ($this->userHasPermission()) {

            $request = $this->request->getPost();
            $tx = $this->transactions->get();
/*
            echo('<pre>');
            print_r($request);
            echo('</pre>');
            exit();*/
            //$oid = $request['order_id'];
            //$LoteSql = "SELECT * from prd_lots where order_id = $oid AND  status = 'EJECUTADO' order by id desc";
            //$ultimo = $this->db->query($LoteSql)->fetch();

           // if($request['returned_qty']< $request['qty'] && $request['returned_qty'] > 0){

                //if($ultimo && ($ultimo['id'] == $request['id'])){
                    $sql="SELECT prd_orders.id,prd_orders.id as order_id,prd_orders.order_number, 
                    prd_orders.production_date, prd_orders.updated, prd_orders.updated_by, 
                    wms_bom.material_id,prd_orders.product_id as bom_products_id,prd_lots.product_id as lot_product_id, wms_products.name as product_name, 
                    wms_products.code as product_code, wms_lines.name as line_name,
                    wms_lines.code as line_code, wms_lines.id as line_id, wms_categories.name as category_name, 
                    wms_categories.code as category_code, wms_bom.amount,prd_lots.weight as qty,
                    sum(wms_bom.amount * prd_lots.weight) as stock
                    FROM wms_bom 
                    inner join wms_products on wms_products.id = wms_bom.material_id 
                    inner join wms_lines on wms_lines.id = wms_products.line_id 
                    inner join wms_categories on wms_categories.id = wms_lines.category_id 
                    inner join prd_lots on prd_lots.product_id =wms_bom.product_id
                    inner join prd_orders on prd_lots.order_id = prd_orders.id 
                    where prd_lots.id = $id
                    group by prd_lots.product_id,wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id, wms_bom.amount,prd_lots.weight,
                    prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code, prd_orders.id, wms_bom.id 
                    order by wms_bom.id";

     

                    $detalles = $this->db->query($sql)->fetchAll();
                    $aux=0;
                    $cantidades_suficientes = 'si';
                    $bom_by_producto = [];
                    $producto = "";
                    if($detalles){
                        foreach ($detalles as $bom_producto) {
                            $b=(object)array();
                            $b->id=$bom_producto['id'];
                            $b->production_date=$bom_producto['production_date'];
                            $b->updated=$bom_producto['updated'];
                            $b->updated_by=$bom_producto['updated_by'];
                            $b->order_id = $bom_producto['order_id'];
                            $b->material_id = $bom_producto['material_id'];
                            $producto_existencia = $bom_producto['material_id'];
                            $b->bom_products_id = $bom_producto['bom_products_id'];
                            $b->product_name = $bom_producto['product_name'];
                            $b->product_code = $bom_producto['product_code'];
                            $b->line_name = $bom_producto['line_name'];
                            $b->line_code = $bom_producto['line_code'];
                            $b->line_id = $bom_producto['line_id'];
                            $b->category_name = $bom_producto['category_name'];
                            $almacen = $bom_producto['category_code'];
                            $b->category_code = $bom_producto['category_code'];
                            $b->amount = $bom_producto['amount'];
                            $b->qty_real = $request['qty'];
                            $b->qty = $request['returned_qty'];
                            $qtyaux = $request['returned_qty'];
                            $b->stock = $bom_producto['amount'] * $request['returned_qty'];
                            $b->qty_return = $request['qty'] - $request['returned_qty'];
                            $b->qty_return_stock = $bom_producto['amount'] * ($request['qty'] - $request['returned_qty']);
                            $product_finalizado =$bom_producto['lot_product_id'];
                            $product_finalizado_qty =$bom_producto['qty'];
                            $total_necesario =$bom_producto['stock'];





                            // $sql_existencias = "SELECT * from get_existencias(31, $producto_existencia, null, null)";
                            $existencia = $this->getKardex(null, null, null, 35, $producto_existencia);
                            // $existencia = $this->db->query($sql_existencias)->fetchAll();
                            if ($existencia > 0) {
                                $b->existencia = $existencia;
                            } else {
                                $b->existencia = 0;

                            }
                           /* $sql_existencias = "SELECT * from get_existencias(31, $producto_existencia, null, null)";

                    $existencia = $this->db->query($sql_existencias)->fetchAll();
                    if (count($existencia) > 0) {
                        $b->existencia = $existencia[0]['existencia'];
                    } else {
                        $b->existencia = 0;
                    }*/
                            if ($bom_producto['stock'] > $b->existencia) {
                                $cantidades_suficientes = 'no';
                                $b->cantidades_suficientes = $cantidades_suficientes;

                                $product = Products::findFirst($producto_existencia)->name;

                            }
                            if ($bom_producto['stock'] == $b->existencia) {
                                $cantidades_suficientes = 'si';
                                $b->cantidades_suficientes = $cantidades_suficientes;
     
                                $product = Products::findFirst($producto_existencia)->name;

                            } 
                            array_push($bom_by_producto, $b);
                        }

                    if($cantidades_suficientes == 'si'){
                        $tx = $this->transactions->get();
                        $exitMovement = new Movements();
                        $exitMovement->setTransaction($tx);
                        $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $exitMovement->date = date("Y-m-d H:i:s");
                        $exitMovement->status = 'EJECUTADO';
                        $exitMovement->ejecute_date = date("Y-m-d H:i:s");
                        $exitMovement->storage_id = intval(35);
                        $exitMovement->type_id = 2;
                        $exitMovement->po_id = intval($request['order_id']);
                            // $exitMovement->transaction_id = $transaction->id;
                        if ($exitMovement->create()) {
                            foreach ($bom_by_producto as $detail) {
                                        //$product = Products::findFirst($detail->material_id);
                                $movementDetailexit = new MovementDetails();
                                $tx = $this->transactions->get();
                                $movementDetailexit->setTransaction($tx);
                                $movementDetailexit->movement_id = $exitMovement->id;
                                $movementDetailexit->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                $movementDetailexit->qty = $detail->stock;
                                if (!$movementDetailexit->create()) {

                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($movementDetailexit);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                    $tx->rollback();
                                }else {
                                    $this->content['result'] = true;
                                    $this->content['movement'] = $movementDetailexit;
                                    $this->content['message'] = Message::success('detalle guardado correctamente');

                                    $tx->commit();
                                }
                            }
                            
                        
                        $entryMovement = new Movements();
                        $entryMovement->setTransaction($tx);
                        $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $entryMovement->date = date("Y-m-d H:i:s");
                        $entryMovement->status= 'EJECUTADO';
                        $entryMovement->storage_id = intval(34);
                        // $entryMovement->movement_id = $exitMovement->id;
                        $entryMovement->type_id = 1;
                        $entryMovement->po_id = intval($request['order_id']);
                        // $entryMovement->transaction_id = $transaction->id;
                        if ($entryMovement->create()) {
                            $movementDetailentry = new MovementDetails();
                            $tx = $this->transactions->get();
                            $movementDetailentry->setTransaction($tx);
                            $movementDetailentry->movement_id = $entryMovement->id;
                            $movementDetailentry->product_id = $product_finalizado;
                            $product_aux =$product_finalizado;
                            //$movementDetail->bag_id = $detail['bag_id'];
                            $movementDetailentry->qty = $qtyaux;
                            if ($movementDetailentry->create()) {
                                $tx = $this->transactions->get();
                                $order = ProductionLots::findFirst($id);
                                $order->setTransaction($tx);
                                $order->qty_real = $qtyaux;
                                $order->status = 'FINALIZADO';
                                $order->movement_id = $entryMovement->id;
                                $order->raw_material_delete_movement_id = $exitMovement->id;

                                if($order->update()){

                                    $this->content['result'] = true;
                                    $this->content['movement'] = $order;
                                    $this->content['message'] = Message::success('lote Finalizado');
                                    $tx->commit();
                                     //+====
                                    $tx = $this->transactions->get();
                                    $branchTransferX = [];
                                    $transaction = new Transactions();
                                    $transaction->setTransaction($tx);

                        if ($transaction->create()) {

                            $exitMovement = new Movements();
                            $exitMovement->setTransaction($tx);
                            $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                            $exitMovement->date = date("Y-m-d H:i:s");
                            $exitMovement->status = 'NUEVO';
                            $exitMovement->storage_id = intval(35);
                            $exitMovement->type_id = 5;
                            $exitMovement->po_id = intval($request['order_id']);
                            $exitMovement->transaction_id = $transaction->id;
                            if ($exitMovement->create()) {

                                $entryMovement = new Movements();
                                $entryMovement->setTransaction($tx);
                                $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                                $entryMovement->date = date("Y-m-d H:i:s");
                                $entryMovement->status= 'NUEVO';
                                $entryMovement->storage_id = intval(34);
                                $entryMovement->movement_id = $exitMovement->id;
                                $entryMovement->type_id = 4;
                                $entryMovement->po_id = intval($request['order_id']);
                                $entryMovement->transaction_id = $transaction->id;

                                if ($entryMovement->create()) {

                                    $branchTransfer = new BranchTransfers();
                                    $branchTransfer->setTransaction($tx);
                                    $branchTransfer->transaction_id = $transaction->id;

                                if ($branchTransfer->create()) {

                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('Transferencia registrada correctamente.');
                                    $this->content['branchTransfer'] = $exitMovement;

                                    foreach ($bom_by_producto as $detail) {
                                        $product = Products::findFirst($detail->material_id);
                                        $movementDetailexit = new MovementDetails();
                                        $tx = $this->transactions->get();
                                        $movementDetailexit->setTransaction($tx);
                                        $movementDetailexit->movement_id = $exitMovement->id;
                                        $movementDetailexit->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                        $movementDetailexit->qty = $detail->qty_return_stock;

                                        if (!$product->active || !$movementDetailexit->create()) {

                                            $this->content['error'] = Helpers::getErrors($movementDetailexit);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                            $tx->rollback();
                                        }
                                    }

                                    $tx = $this->transactions->get();
                                    $movementId= $exitMovement->id;
                                    $movement = Movements::findFirst($movementId);
                                    $movement->setTransaction($tx);
                                    $movement->status = 'EJECUTADO';
                                    $movement->ejecute_date = date("Y-m-d H:i:s");

                                    if ($movement->update()) {

                                        $movement2 = Movements::findFirst("movement_id = $movementId");
                                        if($movement2){
                                            $movement2->setTransaction($tx);
                                            $movement2->status = 'EJECUTADO';
                                            $movement2->ejecute_date = date("Y-m-d H:i:s");
                                            $movement2->update();  
                                        }
                                        $idIn = $movementId-1;
                                        $movement3 = Movements::findFirst("id = $idIn AND type_id = 5");
                                        if($movement3){
                                            $movement3->setTransaction($tx);
                                            $movement3->status = 'EJECUTADO';
                                            $movement3->ejecute_date = date("Y-m-d H:i:s");
                                            $movement3->update(); 
                                        }
                                        $this->content['result'] = true;
                                        $this->content['movement'] = $movement;
                                        $this->content['message'] = Message::success('El movimiento ha sido ejecutado');
                                        $tx->commit();
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['error'] = Helpers::getErrors($movement);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento');
                                        $tx->rollback();
                                    }

                                    $tx = $this->transactions->get();

                                    $exist_lot= ProductionLots::findFirst($id);
                                    $tx = $this->transactions->get();
                                    $exist_lot->setTransaction($tx);
                                    $exist_lot->status = 'FINALIZADO';
                                    $exist_lot->raw_material_return_movement_id = $exitMovement->id;

                                    if($exist_lot->update()){
                                        $sql = "SELECT whp.id,wh.name,whp.time_job, wh.price as price_hour, wh.id as work_id,(wh.price/60) as price_minute, 
                                                ((wh.price/60)*whp.time_job) as price_qty
                                                FROM wms_handiwork_products as whp
                                                left join wms_products as wp on wp.id= whp.product_id
                                                left join wms_handiwork as wh on wh.id = whp.handiwork_id
                                            WHERE wp.id = $product_aux";
                                                    
                                            $work = $this->db->query($sql)->fetchAll();
                                            if($work){

                                                for ($a=0; $a <count($work) ; $a++) { 
                                                    
                                                $l=$id;
                                                $w=$work[$a]['work_id'];

                                                $lw = HandiWorkLots::findFirst("lot_id = $l AND handiwork_id = $w");
                                                if($lw){

                                                
                                                $lotwork=HandiWorkLots::findFirst(intval($lw->id));
                                                $lotwork->setTransaction($tx);
                                                $lotwork->handiwork_id = $work[$a]['work_id'];
                                                $lotwork->qty = $qtyaux;
                                                $lotwork->amount = $work[$a]['price_qty'] * $qtyaux;


                                                 if ($lotwork->update()) {
                                        // $aux++;
    
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de producción ha sido Modificada');
                                        $this->content['order'] = $order;
                                        // $tx->commit();
                                    }else {
                                        $this->content['error'] = Helpers::getErrors($order);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de producción.');
                                        $tx->rollback();
                                    }
                                }
                                }
                            }


                                     /*$this->content['result'] = true;
                                     $this->content['movement'] = $exist_lot;
                                     $this->content['message'] = Message::success('Lote Ejecutado Correctamente');*/
                                     //$tx->commit();

                                 } else{
                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($exist_lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el lote');
                                    $tx->rollback();

                    }
                }
            }
        }
    }

                                     $tx->commit();
                                } else {
                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar finalizar el lote');
                                    $tx->rollback();
                                }
                                $this->content['result'] = true;
                                $this->content['movement'] = $movementDetailentry;
                                $this->content['message'] = Message::success('Lote Produccion finalizada');

                            //$tx->rollback();
                                // $tx->commit();
                            }else {
                                $this->content['result'] = false;
                                $this->content['error'] = Helpers::getErrors($movementDetailentry);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                $tx->rollback();
                            }
                        }
                        }
                       }

                      
                       //=====

            /*}else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('Finalize la prodcuccion por el ultimo lote');  

                }*/

            }
            if ($this->content['result']== true ){
                $tx->commit();
            }

        } else{
            $this->content['result'] = false;
            $this->content['message'] = Message::success('No tiene los permisos suficientes'); 
        }

}catch (Exception $e) {
        $this->content['errors'] = Message::exception($e);
    }

    $this->response->setJsonContent($this->content);
}

 public function getKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        if ($this->userHasPermission()) {
            $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId);
            /*echo "<pre>";
            print_r($kardexAux);
            exit();*/
            $kardex = [];
            $tot=0; 
            $stock = 0;
       
               
                for ($j=0; $j < sizeof($kardexAux); $j++) {
                        // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
                        if ($kardexAux[$j]['movement_type'] == 1) {
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 2) {
                            $stock -= $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 3) {
                            $stock = $kardexAux[$j]['qty'];
                         }  elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
                             $stock += $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
                            $stock -= $kardexAux[$j]['qty'];
                            // Obtencion de la entrada ;                       
                        }
                    // $tot+=$stock
            }
            $tot = $stock;
        } else {
            return 0;
        }
        return $tot;
    }
    public function generateKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' ";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 6 END";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }
        $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }


public function getPackingListPdf ($id)
{
    if (is_numeric($id)) {
        $lot = ProductionLots::findFirst($id);
        if ($lot && $lot->status == 'TERMINADO' && is_numeric($lot->movement_id)) {
            $order = ProductionOrders::findFirst($lot->order_id);
            $orderProduct = Products::findFirst($order->product_id);
            $lotProduct = Products::findFirst($lot->product_id);
            $firstLotProcess = ProductionLotProcesses::findFirst("lot_id = $lot->id AND process_id = 1 AND dryer_number % 3 = 1");
            $lastLotProcess = ProductionLotProcesses::findFirst("lot_id = $lot->id AND process_id = 6");
            $startDate = new DateTime($firstLotProcess->start_date);
            $finishDate = new DateTime($lastLotProcess->finish_date);
            $totalTime = date_diff($startDate, $finishDate)->format('%Y/%M/%D %H:%I:%S');

            $pdf = new PDFPackingList('P','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetOrderNumber($order->order_number);
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetTextColor(4, 26, 131);
            $pdf->SetFillColor(218, 221, 238);
            $pdf->SetFont('Arial','',8);
            $pdf->SetDrawColor(4, 26, 131);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode("PROUCTO: $orderProduct->name"),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"PESO SOLICITADO: ".number_format($order->qty, 2, '.', ',')." KG.",0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,"FECHA DE CAPTURA: $order->created",0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"FECHA PROGRAMADA: $order->production_date",0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->Ln();

            $pdf->SetFont('Arial','B',20);
            $pdf->Cell(95,10,"LOTE $lot->lot_number",0,1,'L');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(95,10,utf8_decode("PROUCTO: $lotProduct->name"),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"PESO: ".number_format($lot->weight, 2, '.', ',')." KG.",0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,"FECHA PROGRAMADA: $lot->scheduled_start_date",0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"INICIO: ".$startDate->format('Y/m/d H:i:s'),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,"FIN: ".$finishDate->format('Y/m/d H:i:s'),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"TIEMPO TOTAL: ".$totalTime,0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->Ln();

            $pdf->SetFont('Arial','',10);
            $pdf->SetWidths(array(30, 50, 80, 35));
            $pdf->SetAligns(array('C', 'C', 'C', 'C'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(false);
            $pdf->SetFill(array(true, true, true, true));
            $pdf->Row(array('PACA', utf8_decode('CÓDIGO PRODUCTO'), 'NOMBRE PRODUCTO', 'PESO'));
            $pdf->SetAligns(array('C', 'C', 'L', 'R'));
            $fill = false;
            $totalWeight = 0;

            $sql = "SELECT md.bale_id, b.product_id, c.code || '-' || l.code || '-' || p.code AS product_code, p.name AS product_name, b.qty
            FROM wms_movement_details AS md
            INNER JOIN wms_bales AS b
            ON b.id = md.bale_id
            INNER JOIN wms_products AS p
            ON p.id = b.product_id
            INNER JOIN wms_lines AS l
            ON l.id = p.line_id
            INNER JOIN wms_categories AS c
            ON c.id = l.category_id
            WHERE md.movement_id = $lot->movement_id;";
            $details = $this->db->query($sql)->fetchAll();

            foreach ($details as $detail) {
                $totalWeight += $detail['qty'];
                $pdf->SetFill(array($fill, $fill, $fill, $fill));
                $pdf->Row(array($detail['bale_id'], $detail['product_code'], utf8_decode($detail['product_name']), number_format($detail['qty'], 2, '.', ',').' KG.'));
                $fill = !$fill;
            }

            $pdf->SetWidths(array(160, 35));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->SetFill(array(false, true));
            $pdf->Row(array('TOTAL', number_format($totalWeight, 2, '.', ',').' KG.'));

            $pdf->SetTitle("Packing List Lote #$lot->lot_number",true);
            $pdf->Output('I', "Packing List Lote #$lot->lot_number.pdf", true);

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Packing List Lote #'.$lot->lot_number.'.pdf"');
            return $response;
        }
    }
    return null;
}

public function getScrapPdf ($id)
{
    if (is_numeric($id)) {
        $lot = ProductionLots::findFirst($id);
        $lotProduct = Products::findFirst($lot->product_id);
        $pdf = new PDFScrap('P','mm','Letter');
        $pdf->AliasNbPages();
        $pdf->SetLotNumber($lot->lot_number);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetTextColor(4, 26, 131);
        $pdf->SetFillColor(218, 221, 238);
        $pdf->SetFont('Arial','',8);
        $pdf->SetDrawColor(4, 26, 131);

        $pdf->Ln();

        $pdf->SetY($pdf->GetY()-3);
        $pdf->Cell(95,10,utf8_decode("PROUCTO: $lotProduct->name"),0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
        $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
        $pdf->Cell(95,10,"PESO: $lot->weight KG.",0,1,'L');
        $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

        $pdf->SetY($pdf->GetY()-3);
        $pdf->Cell(95,10,"FECHA DE CAPTURA: $lot->created",0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
        $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
        $pdf->Cell(95,10,"FECHA PROGRAMADA: $lot->scheduled_start_date",0,1,'L');
        $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        $pdf->SetWidths(array(70, 70, 55));
        $pdf->SetAligns(array('C', 'C', 'C'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->SetFill(array(true, true, true));
        $pdf->Row(array('PROCESO', 'SECADA', 'PESO'));
        $pdf->SetAligns(array('L', 'L', 'R'));
        $fill = false;
        $totalWeight = 0;

        $sql = "SELECT s.id, s.process_id, p.name AS process_name, s.dryer_number, s.qty
        FROM prd_scraps AS s
        INNER JOIN prd_processes AS p
        ON p.id = s.process_id
        WHERE s.lot_id = $lot->id
        ORDER BY id ASC;";
        $scraps = $this->db->query($sql)->fetchAll();

        foreach ($scraps as $scrap) {
            $dryerNumber = is_numeric($scrap['dryer_number']) ? 'SECADA '.$scrap['dryer_number'] : NULL;
            $totalWeight += $scrap['qty'];
            $pdf->SetFill(array($fill, $fill, $fill));
            $pdf->Row(array($scrap['process_name'], $dryerNumber, number_format($scrap['qty'], 2, '.', ',').' KG.'));
            $fill = !$fill;
        }

        $pdf->SetWidths(array(160, 35));
        $pdf->SetAligns(array('R', 'R'));
        $pdf->SetFill(array(false, true));
        $pdf->Row(array('TOTAL', number_format($totalWeight, 2, '.', ',').' KG.'));

        $pdf->SetTitle("Scraps Lote #$lot->lot_number",true);
        $pdf->Output('I', "Scraps Lote #$lot->lot_number.pdf", true);

        $response = new Phalcon\Http\Response();
        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="Scraps Lote #'.$lot->lot_number.'.pdf"');
        return $response;
    }
    return null;
}

public function getRawMaterialsPdf ($id)
{
    if (is_numeric($id)) {
        $sql = "SELECT l.id, l.order_id, l.lot_number, l.product_id, p.name AS product, l.weight, l.status
        FROM prd_lots AS l
        INNER JOIN wms_products AS p
        ON p.id = l.product_id
        WHERE l.id = $id;";
        $lot = $this->db->query($sql)->fetch();

        $sql = "SELECT p.name AS product, rmmd.bag_id, rmmd.qty AS consumed_qty, rmrmd.qty AS returned_qty
        FROM prd_lots AS l
        INNER JOIN wms_movements AS rmm
        ON rmm.id = l.raw_material_movement_id
        INNER JOIN wms_movement_details AS rmmd
        ON rmmd.movement_id = rmm.id
        INNER JOIN wms_products AS p
        ON p.id = rmmd.product_id
        LEFT JOIN wms_movements AS rmrm
        ON rmrm.id = l.raw_material_return_movement_id AND rmrm.status = 1
        LEFT JOIN wms_movement_details AS rmrmd
        ON rmrmd.movement_id = rmrm.id AND rmrmd.bag_id = rmmd.bag_id
        WHERE l.id = $id
        AND rmm.status = 1;";
        $details = $this->db->query($sql)->fetchAll();

        $pdf = new PDFRawMaterial('P','mm','Letter');
        $pdf->AliasNbPages();
        $pdf->SetLotNumber($lot['lot_number']);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTextColor(4, 26, 131);
        $pdf->SetFillColor(218, 221, 238);
        $pdf->SetFont('Arial','',10);
        $pdf->SetDrawColor(4, 26, 131);
        $pdf->SetXY($pdf->GetX(), $pdf->GetY()-3);
        $pdf->Cell(95,10,'LOTE: '.$lot['lot_number'],0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+100,$pdf->GetY()-2);
        $pdf->SetXY($pdf->GetX(), $pdf->GetY()-3);
        $pdf->Cell(95,10,utf8_decode('PRODUCTO: '.$lot['product']),0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+100,$pdf->GetY()-2);
        $pdf->SetXY($pdf->GetX(), $pdf->GetY()-3);
        $pdf->Cell(95,10,'CANTIDAD: '.$lot['weight'].' KG.',0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+100,$pdf->GetY()-2);
        $pdf->SetXY($pdf->GetX(), $pdf->GetY()-3);
        $pdf->Cell(95,10,'ESTATUS: '.$lot['status'],0,1,'L');
        $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+100,$pdf->GetY()-2);

        $pdf->Ln();

        $pdf->SetWidths(array(20, 130, 25, 25));
        $pdf->SetAligns(array('C', 'C', 'C', 'C'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->Row(array('SACO', 'PRODUCTO', 'CANTIDAD CONSUMIDA', 'CANTIDAD DEVUELTA'));
        $pdf->SetAligns(array('C', 'L', 'R', 'R'));
        $fill = true;
        $jumbosQty = 0;
        foreach ($details as $detail) {
            $pdf->SetFill(array($fill, $fill, $fill, $fill));
            $pdf->Row(array($detail['bag_id'], utf8_decode($detail['product']), number_format($detail['consumed_qty'], 2, '.', ','), number_format($detail['returned_qty'], 2, '.', ',')));
            $fill = !$fill;
            $jumbosQty++;
        }

        $pdf->SetTitle('Consumo de materia prima - Lote '.$lot['lot_number'],true);

        $pdf->Output('I', 'Consumo de materia prima - Lote '.$lot['lot_number'].'.pdf', true);

        $response = new Phalcon\Http\Response();
        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="Consumo de materia prima - Lote '.$lot['lot_number'].'.pdf"');
        return $response;
    }
    return false;
}

public function getQualityPdf ($id)
{
    if (is_numeric($id)) {
        $lot = ProductionLots::findFirst($id);

        if ($lot) {
            $pdf = new FPDF('P','in',array(4,6));
            $pdf->AddPage();
            $pdf->Image($_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/logo_fondo.png',(($pdf->GetPageWidth()/2)-1.5),0.1,3,3);
            $pdf->SetTextColor(0, 112, 255);
            $pdf->SetDrawColor(0, 112, 255);
            $pdf->SetFont('Arial','',16);
            $pdf->Text(($pdf->GetPageWidth()/2)-($pdf->GetStringWidth("technofibers")/2), 1.1, "technofibers");
            $pdf->SetFont('Arial','',7);

            $pdf->SetXY(0.9, 1.5);
            $pdf->Cell(0.7,0.2,'VOLUMEN','T',0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.7,0.2,'RESISTENCIA','T',0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.7,0.2,'REBOTE','T',0,'C');

            $pdf->SetXY(0.9, 2);
            $pdf->Cell(0.5,0.2,'RESORTE','T',0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.5,0.2,utf8_decode('LÍNEA'),'T',0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->MultiCell(0.9,0.2,'FIRMA TF LAB','T','C', false);

            $pdf->Text(($pdf->GetPageWidth()/2)-($pdf->GetStringWidth("www.technofibers.com")/2), 2.5, "www.technofibers.com");

            $pdf->SetTextColor(0);
            $pdf->SetXY(0.9, 1.3);
            $pdf->Cell(0.7,0.2,number_format($lot->volume, 2, '.', ','),0,0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.7,0.2,number_format($lot->strength, 2, '.', ','),0,0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.7,0.2,number_format($lot->rebound, 2, '.', ','),0,0,'C');

            $pdf->SetXY(0.9, 1.8);
            $pdf->Cell(0.5,0.2,number_format($lot->spring, 2, '.', ','),0,0,'C');
            $pdf->SetX($pdf->GetX()+0.1);
            $pdf->Cell(0.5,0.2,utf8_decode($lot->line),0,0,'C');

            $qrCode = new QrCode($lot->lot_number);
            $qrCode->setWriterByName('png');
            $uriData = $qrCode->writeDataUri();
            $pdf->Image($uriData,(($pdf->GetPageWidth()/2)-1.25),3.1,2.5,0,'PNG');
            $pdf->SetTextColor(0, 112, 255);
            $pdf->SetFont('Arial','B',20);
            $pdf->Text(($pdf->GetPageWidth()/2)-($pdf->GetStringWidth($lot->lot_number)/2), 5.8, $lot->lot_number);

            $pdf->SetTitle('Etiqueta de calidad de Lote '.$lot->lot_number,true);

            $pdf->Output('I', 'Etiqueta de calidad de Lote '.$lot->lot_number.'.pdf', true);

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Etiqueta de calidad de Lote '.$lot->lot_number.'.pdf"');
            return $response;
        }
    }
    return null;
}

public function getFinishedProductPdf ($finishedProductId)
{
    if (is_numeric($finishedProductId)) {
        $sql = "SELECT b.id AS bale, l.lot_number, TO_CHAR(md.created, 'dd/mm/yyyy') AS date, TO_CHAR(md.created, 'HH24:MI') AS time, md.product_id, p.name AS product, md.qty
        FROM wms_movement_details AS md
        INNER JOIN wms_movements AS m
        ON m.id = md.movement_id
        INNER JOIN wms_bales AS b
        ON b.id = md.bale_id
        INNER JOIN wms_products AS p
        ON p.id = md.product_id
        INNER JOIN prd_lots AS l
        ON l.movement_id = m.id
        WHERE md.id = $finishedProductId
        AND m.status = 1;";
        $finishedProduct = $this->db->query($sql)->fetch();

        $pdf = new FPDF('P','in',array(4,6));
        $pdf->AddPage();
        $pdf->Image($_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/logo_fondo.png',(($pdf->GetPageWidth()/2)-1.5),0.1,3,3);
        $pdf->SetTextColor(0, 112, 255);
        $pdf->SetDrawColor(0, 112, 255);
        $pdf->SetFont('Arial','',16);
        $pdf->Text(($pdf->GetPageWidth()/2)-($pdf->GetStringWidth("technofibers.com")/2), 1.1, "technofibers.com");
        $pdf->SetFont('Arial','',7);

        $pdf->SetXY(0.9, 1.5);
        $pdf->Cell(1.5,0.2,'PRODUCTO','T',0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.7,0.2,'LOTE','T',0,'C');

        $pdf->SetXY(0.9, 2);
        $pdf->Cell(0.6,0.2,'HORA','T',0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.6,0.2,'FECHA','T',0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.7,0.2,'# PACA','T',0,'C');

        $pdf->SetXY(1.5, 2.5);
        $pdf->Cell(0.8,0.2,'PESO','T',0,'C');

        $pdf->SetTextColor(0);
        $pdf->SetXY(0.9, 1.3);
        $pdf->Cell(1.5,0.2,utf8_decode($finishedProduct['product']),0,0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.7,0.2,$finishedProduct['lot_number'],0,0,'C');

        $pdf->SetXY(0.9, 1.8);
        $pdf->Cell(0.6,0.2,$finishedProduct['time'],0,0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.6,0.2,$finishedProduct['date'],0,0,'C');
        $pdf->SetX($pdf->GetX()+0.1);
        $pdf->Cell(0.7,0.2,$finishedProduct['bale'],0,0,'C');

        $pdf->SetXY(1.5, 2.3);
        $pdf->Cell(0.8,0.2,number_format($finishedProduct['qty'], 2, '.', ',').' KG.',0,0,'C');
        $qrCode = new QrCode($finishedProduct['bale']);
        $qrCode->setWriterByName('png');
        $uriData = $qrCode->writeDataUri();
        $pdf->Image($uriData,(($pdf->GetPageWidth()/2)-1.25),3.2,2.5,0,'PNG');

        $pdf->SetTitle('Etiqueta de producto terminado '.$finishedProductId,true);

        $pdf->Output('I', 'etiqueta.pdf', true);

        $response = new Phalcon\Http\Response();
        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="kardex.pdf"');
        return $response;
    }
    return null;
}

public function getScheduledCalendarPdf ($year, $month)
{
    if ($year >= 2019 && $month >= 1 && $month <= 12) {
        $sql = "SELECT l.lot_number, l.product_id, p.name AS product, l.weight, l.status, l.scheduled_start_date, l.customer_id, c.name AS customer, TO_CHAR(l.scheduled_start_date,'YYYY-MM-DD') AS date, EXTRACT(DAY FROM l.scheduled_start_date) AS day, TO_CHAR(l.scheduled_start_date, 'HH24:MI') AS time
        FROM prd_lots AS l
        INNER JOIN wms_products AS p
        ON p.id = l.product_id
        LEFT JOIN sls_customers AS c
        ON c.id = l.customer_id
        WHERE l.status <> 'CANCELADO'
        AND EXTRACT(MONTH FROM l.scheduled_start_date) = $month
        AND EXTRACT(YEAR FROM l.scheduled_start_date) = $year
        ORDER BY l.scheduled_start_date ASC;";
        $scheduledLots = $this->db->query($sql)->fetchAll();
        $pdf = new PDFCalendar("L", "Letter");
        $pdf->SetMargins(7,7);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetLotsScheduled($scheduledLots);
        $pdf->SetYear($year);
        $pdf->SetMonth($month);
        $greyValue = 190;
        $date = $pdf->MDYtoJD($month, 1, $year);
        $pdf->printMonth($date);
        $pdf->SetTitle("Calendario de lotes programados $month-$year",true);
        $pdf->Output('I', "Calendario de lotes programados $month-$year.pdf", true);
        $response = new Phalcon\Http\Response();
        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="Calendario de lotes programados '.$month.'-'.$year.'.pdf"');
        return $response;
    }
    return null;
}

public function create ()
{
    if ($this->userHasPermission()) {

                // $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $content = $this->content;
                // echo('<pre>');
                // print_r($request);
                // echo('</pre>');
                // exit();
        $qty = $request['stock'] - $request['existencia'];
        if (isset($request['order_id']) && is_numeric($request['order_id']) && isset($request['material_id']) && is_numeric($request['material_id']) && isset($qty) && is_numeric($qty)) {
            try {
                $tx = $this->transactions->get();

                    // $request = $this->request->getPost();
                $exist_lot= ProductionLots::findFirst(
                    "order_id = ".$request['order_id']." 
                    AND product_id = ".$request['material_id']." 
                    AND weight = ".$qty."
                    AND status != 'CANCELADO'");


                        // print_r(' hola '. !$exist_lot);
                        // print_r(' hola '. is_null($exist_lot));
                        // exit();
                if(!$exist_lot) {
                    $sql = "SELECT lot_number
                    FROM prd_lots
                    WHERE order_id = ".$request['order_id']."
                    ORDER BY lot_number
                    DESC LIMIT 1;";

                //echo('<pre>');
                //print_r($sql);
                //echo('</pre>');
                //exit();
                    $lastNumber = $this->db->query($sql)->fetch()['lot_number'];

                    $order = ProductionOrders::findFirst($request['order_id']);

                    if ($order) {
                        if ($order->status == 'CANCELADO') {
                            $this->content['message'] = Message::error('La orden de producción ya se encuentra cancelada.');
                        } else {
                            $product = Products::findFirst($request['material_id']);
                            if ($product->active) {
                                $lot = new ProductionLots();
                                $lot->setTransaction($tx);
                                $lot->order_id = $request['order_id'];
                                $lot->product_id = $request['material_id'];
                                $lot->weight = $qty;
                                $lot->scheduled_start_date = date("Y-m-d H:i:s");
                                $product_id =$request['material_id'];

                                if ($lastNumber) {
                                    $actualNumber = (int) substr($lastNumber, -2);
                                    $actualNumber++;
                                    if ($actualNumber > 9) {
                                        $lot->lot_number = $order->order_number.'-'.$actualNumber;
                                    } else {
                                        $lot->lot_number = $order->order_number.'-0'.$actualNumber;
                                    }
                                } else {
                                    $lot->lot_number = $order->order_number.'-01';
                                }

                                if ($lot->create()) {
                                       /*$sql = "SELECT whp.id,wh.name,whp.time_job, wh.price as price_hour, wh.id as work_id,(wh.price/60) as price_minute, 
                                                ((wh.price/60)*whp.time_job) as price_qty
                                                FROM wms_handiwork_products as whp
                                                left join wms_products as wp on wp.id= whp.product_id
                                                left join wms_handiwork as wh on wh.id = whp.handiwork_id
                                            WHERE wp.id = $product_id";
                                            $work = $this->db->query($sql)->fetchAll();
                                            if($work){

                                                for ($i=0; $i <count($work) ; $i++) { 

                                                $lotwork = new HandiWorkLots();
                                                $lotwork->setTransaction($tx);
                                                $lotwork->lot_id = $lot->id;
                                                // $lot->start_date = $order->production_date;
                                                $lotwork->handiwork_id = $work[$i]['work_id'];
                                                $lotwork->qty = $qty;
                                                $lotwork->amount = $work[$i]['price_qty'] * $qty;


                                                 if ($lotwork->create()) {
                                        // $aux++;
    
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El lote ha sido creado correctamente.');
                                        $this->content['lot'] = $lot;
                                        // $tx->commit();
                                    }else {
                                        $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el lote.');
                                    $tx->rollback();
                                    }
                                                }

                                                

                                            } else {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El lote ha sido creado correctamente.');
                                        $this->content['lot'] = $lot;
                                            }*/
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El lote ha sido creado correctamente.');
                                        $this->content['lot'] = $lot;
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el lote.');
                                    $tx->rollback();
                                }
                                if($this->content['result'] == true){
                $tx->commit();
                }
                            } else {
                                $this->content['message'] = Message::error('El producto está inactivo.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la orden de producción.');
                    }
                } else{
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Ya se encuentra un lote Registrado.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function addRawMaterialBag ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                $request = $this->request->getPost();

                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('El lote ya se encuentra cancelado.');
                    } else {
                        $product = Products::findFirst($request['product_id']);
                        if ($product->active) {
                            if (!is_null($lot->raw_material_movement_id) && $lot->raw_material_movement_id != 0 && $lot->raw_material_movement_id != '') {
                                    // Obtener los id de los movimientos de entrada y salida y registrar un nuevo detalle
                                $entryMovement = Movements::findFirst($lot->raw_material_movement_id);
                                $exitMovement = Movements::findFirst("transaction_id = ".$entryMovement->transaction_id);
                                $exitMovementDetail = new MovementDetails();
                                $exitMovementDetail->setTransaction($tx);
                                $exitMovementDetail->movement_id = $exitMovement->id;
                                $exitMovementDetail->product_id = $request['product_id'];
                                $exitMovementDetail->bag_id = $request['bag_id'];
                                $exitMovementDetail->qty = 1;
                                if ($exitMovementDetail->create()) {
                                    $entryMovementDetail = new MovementDetails();
                                    $entryMovementDetail->setTransaction($tx);
                                    $entryMovementDetail->movement_id = $entryMovement->id;
                                    $entryMovementDetail->product_id = $request['product_id'];
                                    $entryMovementDetail->bag_id = $request['bag_id'];
                                    $entryMovementDetail->qty = 1;
                                    if ($entryMovementDetail->create()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('Materia prima agregada correctamente.');
                                        $tx->commit();
                                    }
                                }
                            } else {
                                    // Registrar un nuevo movimiento de entrada y de salida, registrar el id en el lote y registrar el detalle
                                $transaction = new Transactions();
                                $transaction->setTransaction($tx);
                                if ($transaction->create()) {
                                    $exitMovement = new Movements();
                                    $exitMovement->setTransaction($tx);
                                    $exitMovement->storage_id = 2;
                                    $exitMovement->type = 2;
                                    $exitMovement->transaction_id = $transaction->id;
                                    if ($exitMovement->create()) {
                                        $entryMovement = new Movements();
                                        $entryMovement->setTransaction($tx);
                                        $entryMovement->storage_id = 6;
                                        $entryMovement->type = 1;
                                        $entryMovement->transaction_id = $transaction->id;
                                        if ($entryMovement->create()) {
                                            $lot->raw_material_movement_id = $entryMovement->id;
                                            $exitMovementDetail = new MovementDetails();
                                            $exitMovementDetail->setTransaction($tx);
                                            $exitMovementDetail->movement_id = $exitMovement->id;
                                            $exitMovementDetail->product_id = $request['product_id'];
                                            $exitMovementDetail->bag_id = $request['bag_id'];
                                            $exitMovementDetail->qty = 1;
                                            if ($exitMovementDetail->create()) {
                                                $entryMovementDetail = new MovementDetails();
                                                $entryMovementDetail->setTransaction($tx);
                                                $entryMovementDetail->movement_id = $entryMovement->id;
                                                $entryMovementDetail->product_id = $request['product_id'];
                                                $entryMovementDetail->bag_id = $request['bag_id'];
                                                $entryMovementDetail->qty = 1;
                                                if ($entryMovementDetail->create()) {
                                                    $lot->raw_material_movement_id = $entryMovement->id;
                                                    if ($lot->update()) {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('Materia prima agregada correctamente.');
                                                        $tx->commit();
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lot);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la materia prima.');
                                                        $tx->rollback();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la transacción.');
                                    $tx->rollback();
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('El producto está inactivo.');
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function addRawMaterialBagReturn ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                $request = $this->request->getPost();

                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('El lote ya se encuentra cancelado.');
                    } else {
                        if (!is_null($lot->raw_material_return_movement_id) && $lot->raw_material_return_movement_id != 0 && $lot->raw_material_return_movement_id != '') {
                                // Obtener los id de los movimientos de entrada y salida y registrar un nuevo detalle
                            $exitMovement = Movements::findFirst($lot->raw_material_return_movement_id);
                            if ($exitMovement) {
                                $entryMovement = Movements::findFirst("transaction_id = ".$exitMovement->transaction_id." AND id <> ".$exitMovement->id);
                                if ($entryMovement) {
                                    $exitMovementDetail = MovementDetails::findFirst("movement_id = ".$exitMovement->id." AND bag_id = ".$request['bag_id']);
                                    if ($exitMovementDetail) {
                                        $exitMovementDetail->qty = $request['returned_qty'];
                                        if ($exitMovementDetail->update()) {
                                                // Registrar detalle de movimiento de entrada
                                            $entryMovementDetail = MovementDetails::findFirst("movement_id = ".$entryMovement->id." AND bag_id = ".$request['bag_id']);
                                            if ($entryMovementDetail) {
                                                $entryMovementDetail->qty = $request['returned_qty'];
                                                if ($entryMovementDetail->update()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Devolución de materia prima agregada correctamente.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($entryMovementDetail);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                                    $tx->rollback();
                                                }
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($exitMovementDetail);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($exitMovementDetail);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $exitMovementDetail = new MovementDetails();
                                        $exitMovementDetail->setTransaction($tx);
                                        $exitMovementDetail->movement_id = $exitMovement->id;
                                        $exitMovementDetail->product_id = $request['product_id'];
                                        $exitMovementDetail->bag_id = $request['bag_id'];
                                        $exitMovementDetail->qty = $request['returned_qty'];
                                        if ($exitMovementDetail->create()) {
                                            $entryMovementDetail = new MovementDetails();
                                            $entryMovementDetail->setTransaction($tx);
                                            $entryMovementDetail->movement_id = $entryMovement->id;
                                            $entryMovementDetail->product_id = $request['product_id'];
                                            $entryMovementDetail->bag_id = $request['bag_id'];
                                            $entryMovementDetail->qty = $request['returned_qty'];
                                            if ($entryMovementDetail->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Devolución de materia prima agregada correctamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($lot);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lot);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                            $tx->rollback();
                                        }
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['error'] = Helpers::getErrors($lot);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                $tx->rollback();
                            }
                        } else {
                                // Registrar un nuevo movimiento de entrada y de salida, registrar el id en el lote y registrar el detalle VVVCVVDFFDFDCCDDEEXZ<<
                            $transaction = new Transactions();
                            $transaction->setTransaction($tx);
                            if ($transaction->create()) {
                                $exitMovement = new Movements();
                                $exitMovement->setTransaction($tx);
                                $exitMovement->storage_id = 6;
                                $exitMovement->type = 2;
                                $exitMovement->transaction_id = $transaction->id;
                                if ($exitMovement->create()) {
                                    $entryMovement = new Movements();
                                    $entryMovement->setTransaction($tx);
                                    $entryMovement->storage_id = 2;
                                    $entryMovement->type = 1;
                                    $entryMovement->transaction_id = $transaction->id;
                                    if ($entryMovement->create()) {
                                        $lot->raw_material_return_movement_id = $exitMovement->id;
                                        $exitMovementDetail = new MovementDetails();
                                        $exitMovementDetail->setTransaction($tx);
                                        $exitMovementDetail->movement_id = $exitMovement->id;
                                        $exitMovementDetail->product_id = $request['product_id'];
                                        $exitMovementDetail->bag_id = $request['bag_id'];
                                        $exitMovementDetail->qty = $request['returned_qty'];
                                        if ($exitMovementDetail->create()) {
                                            $entryMovementDetail = new MovementDetails();
                                            $entryMovementDetail->setTransaction($tx);
                                            $entryMovementDetail->movement_id = $entryMovement->id;
                                            $entryMovementDetail->product_id = $request['product_id'];
                                            $entryMovementDetail->bag_id = $request['bag_id'];
                                            $entryMovementDetail->qty = $request['returned_qty'];
                                            if ($entryMovementDetail->create()) {
                                                if ($lot->update()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Devolución de materia prima agregada correctamente.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                                    $tx->rollback();
                                                }
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($lot);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lot);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($lot);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['error'] = Helpers::getErrors($lot);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la devolución de materia prima.');
                                $tx->rollback();
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function update ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                $request = $this->request->getPut();

                if ($lot) {
                    $lot->setTransaction($tx);
                    if ($lot->status == 'NUEVO') {
                        $product = Products::findFirst($request['product_id']);
                        if ($product->active) {
                            $lot->order_id = $request['order_id'];
                            $lot->product_id = $request['product_id'];
                            $lot->weight = $request['weight'];
                            $lot->customer_id = $request['customer_id'];

                            if ($lot->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El lote ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($lot);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el lote.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['message'] = Message::error('El producto está inactivo.');
                        }
                    } else {
                        $lot->customer_id = $request['customer_id'];

                        if ($lot->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El lote ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lot);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el lote.');
                            $tx->rollback();
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function updateQuality ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                $request = $this->request->getPut();

                if ($lot) {
                    $lot->setTransaction($tx);
                    $lot->volume = $request['volume'];
                    $lot->strength = $request['strength'];
                    $lot->rebound = $request['rebound'];
                    $lot->spring = $request['spring'];
                    $lot->line = $request['line'];

                    if ($lot->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se ha registrado la calidad del lote.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($lot);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la calidad del lote.');
                        $tx->rollback();
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function updateRawMaterial ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $entryMovementDetail = MovementDetails::findFirst($id);

                $request = $this->request->getPut();

                if ($entryMovementDetail) {
                    $entryMovement = Movements::findFirst($entryMovementDetail->movement_id);
                    if ($entryMovement) {
                        $exitMovement = Movements::findFirst("transaction_id = ".$entryMovement->transaction_id." AND type = 2");
                        if ($exitMovement) {
                            $exitMovementDetail = MovementDetails::findFirst("movement_id = ".$exitMovement->id." AND product_id = ".$entryMovementDetail->product_id." AND bag_id = ".$entryMovementDetail->bag_id);
                            if ($exitMovementDetail) {
                                $exitMovementDetail->product_id = $request['product_id'];
                                $exitMovementDetail->bag_id = $request['bag_id'];
                                if ($exitMovementDetail->update()) {
                                    $entryMovementDetail->product_id = $request['product_id'];
                                    $entryMovementDetail->bag_id = $request['bag_id'];
                                    if ($entryMovementDetail->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('Consumo de materia prima actualizado correctamente.');
                                        $tx->commit();
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function addMovement ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                $request = $this->request->getPut();

                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('El lote ya se encuentra cancelado.');
                    } else {
                        $lot->setTransaction($tx);
                        $lot->movement_id = $request['movement_id'];

                        if ($lot->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El movimiento ha sido agregado al lote.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lot);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el movimiento al lote.');
                            $tx->rollback();
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function executeReturnRawMaterialsMovements ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);
                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('El lote ya se encuentra cancelado.');
                    } else {
                        $exitMovement = Movements::findFirst($lot->raw_material_return_movement_id);
                        if ($exitMovement && !is_null($exitMovement->id) && !is_null($exitMovement->transaction_id)) {
                            $entryMovement = Movements::findFirst("transaction_id = $exitMovement->transaction_id AND id <> $exitMovement->id");
                            if ($entryMovement) {
                                $exitMovement->status = 1;
                                $exitMovement->date = date('Y-m-d H:i:s');
                                if ($exitMovement->update()) {
                                    $entryMovement->status = 1;
                                    $entryMovement->date = date('Y-m-d H:i:s');
                                    if ($entryMovement->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El movimiento de devolución ha sido ejecutado correctamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($lot);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento de devolución.');
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento de devolución.');
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede ejecutar un movimiento de devolución sin devoluciones.');
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function delete ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('El lote ya se encuentra cancelado.');
                    } else {
                        $lot->setTransaction($tx);

                        if ($lot->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El lote ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lot);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el lote.');
                            }
                                // $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('El lote no existe.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}

public function cancel ($id)
{
    if ($this->userHasPermission()) {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();

                $lot = ProductionLots::findFirst($id);

                if ($lot) {
                    if ($lot->status == 'NUEVO' || $lot->status == 'FORMULADO') {
                        $lot->setTransaction($tx);
                        $lot->status = 'CANCELADO';
                        if ($lot->update()) {
                            $order = ProductionOrders::findFirst($lot->order_id);
                            $order->setTransaction($tx);
                            $orderLots = ProductionLots::find("order_id = $order->id AND id <> $lot->id AND status <> 'CANCELADO'");
                            $lessAdvancedLot = null;
                            if (count($orderLots) > 0) {
                                foreach ($orderLots as $lot) {
                                    if (is_null($lessAdvancedLot)) {
                                        $lessAdvancedLot = $lot;
                                    } else {
                                        switch ($lot->status) {
                                            case 'NUEVO':
                                            $lessAdvancedLot = $lot;
                                            break;
                                            case 'FORMULADO':
                                            if ($lessAdvancedLot->status != 'NUEVO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'SURTIDO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'PRODUCIENDO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'SECADO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'EXTRUDER':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'ESTIRADO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO' && $lessAdvancedLot->status != 'EXTRUDER') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'RIZADO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO' && $lessAdvancedLot->status != 'EXTRUDER' && $lessAdvancedLot->status != 'ESTIRADO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'HORNO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO' && $lessAdvancedLot->status != 'EXTRUDER' && $lessAdvancedLot->status != 'ESTIRADO' && $lessAdvancedLot->status != 'RIZADO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'EMPAQUE':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO' && $lessAdvancedLot->status != 'EXTRUDER' && $lessAdvancedLot->status != 'ESTIRADO' && $lessAdvancedLot->status != 'RIZADO' && $lessAdvancedLot->status != 'HORNO') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                            case 'TERMINADO':
                                            if ($lessAdvancedLot->status != 'NUEVO' && $lessAdvancedLot->status != 'FORMULADO' && $lessAdvancedLot->status != 'SURTIDO' && $lessAdvancedLot->status != 'PRODUCIENDO' && $lessAdvancedLot->status != 'SECADO' && $lessAdvancedLot->status != 'EXTRUDER' && $lessAdvancedLot->status != 'ESTIRADO' && $lessAdvancedLot->status != 'RIZADO' && $lessAdvancedLot->status != 'HORNO' && $lessAdvancedLot->status != 'EMPAQUE') {
                                                $lessAdvancedLot = $lot;
                                            }
                                            break;
                                        }
                                    }
                                }
                                $order->status = $lessAdvancedLot->status;
                            } else {
                                $order->status = 'CANCELADO';
                            }
                            if ($order->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El lote ha sido cancelado.');
                                $tx->commit();
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar el lote.');
                            }
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar el lote.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede cancelar el lote.');
                    }
                } else {
                    $this->content['message'] = Message::error('El lote no existe.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }
    } else {
        $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
    }

    $this->response->setJsonContent($this->content);
}
public function createLotByOrder(){
    $tx = $this->transactions->get();
    $lot = new ProductionLots();
    $lot->setTransaction($tx);

    $request = $this->request->getPost();
    $order = ProductionOrders::findFirst($request['order_id']);
    $lots = ProductionLots::find("order_id = ".$order->id);
    $logsTtoal = $lots->count()+1;
    $lot->order_id = $order->id;
    $lot->product_id = $request['product_id'];
    $lot->weight = $request['qty'];
    $lot->shift_id =  $request['shift'];
    $lot->scheduled_start_date =  $order->production_date;
    $lot->lot_number = $order->order_number.'-0'.$logsTtoal;
    if ($lot->create()) {

        $this->content['result'] = true;
        $this->content['message'] = Message::success('El lote se agregó correctamente');
        $tx->commit();
    }else {
        $this->content['error'] = Helpers::getErrors($order);
        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el lote.');
        $tx->rollback();
    }
    $this->response->setJsonContent($this->content);
}
private function userHasPermission ()
{
    $validUser = Auth::getUserData($this->config);
    if ($validUser && $validUser->id) {
        $sql = "SELECT id
        FROM sys_users
        WHERE ( role_id = 1 OR role_id = 3 OR role_id = 5 OR role_id = 6 OR role_id = 7)
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

class PDFRawMaterial extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $lotNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo_name.png';
        $this->Image($img,5,5,75,0,'PNG');
        $this->SetTextColor(4, 26, 131);
        $this->SetFont('Arial','B',20);
        $this->Cell(0,10,'CONSUMO DE MATERIA PRIMA',0,1,'R');
        $this->Cell(0,10,'LOTE '.$this->lotNumber,0,1,'R');
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "WWW.TECHNOFIBERS.COM", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(4, 26, 131);
        $this->SetTextColor(255);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetLotNumber($lotnom)
    {
        $this->lotNumber=$lotnom;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

class PDFCalendar extends FPDF
{
    protected $year;
    protected $month;
    protected $squareHeight;
    protected $squareWidth;
    protected $longestMonth;
    protected $lotsScheduled;

    function __construct($orientation="L", $format="Letter")
    {
        parent::__construct($orientation, "mm", $format);
        $this->headerFontSize = 45;
    }

    function SetLotsScheduled($ls)
    {
        $this->lotsScheduled = $ls;
    }

    function SetYear($y)
    {
        $this->year = $y;
    }

    function SetMonth($m)
    {
        $this->month = $m;
    }

    function JDtoYMD($date, &$year, &$month, &$day)
    {
        $string = JDToGregorian($date);
        $month = strtok($string, " -/");
        $day = strtok(" -/");
        $year = strtok(" -/");
    }

    function MDYtoJD($month, $day, $year)
    {
        if (! $month || ! $day || ! $year)
            return 0;
        $a = floor((14-$month)/12);
        $y = floor($year+4800-$a);
        $m = floor($month+12*$a-3);
        $jd = $day+floor((153*$m+2)/5)+$y*365;
        $jd += floor($y/4)-floor($y/100)+floor($y/400)-32045;
        return $jd;
    }

    function nextMonth($date)
    {
        $this->JDtoYMD($date, $year, $month, $day);
        if (++$month > 12) {
            $month = 1;
            ++$year;
        }
        return GregorianToJD($month, $day, $year);
    }

    function hasLotsScheduled($date)
    {
        $this->JDtoYMD($date, $year, $month, $day);
        $lots = [];
        foreach ($this->lotsScheduled as $lot) {
            if ($day == $lot['day']) {
                array_push($lots, $lot);
            }
        }
        return $lots;
    }

    function printLotsScheduled($date)
    {
        $x = $this->x;
        $y = $this->y+5;
        $height = 4;
        $fontSize = 8;
        $subStrLength = 20;
        $xDisplacement = 8;
        if ($this->squareHeight < 30) {
            $height = 3;
            $fontSize = 6;
            $subStrLength = 27;
            $xDisplacement = 7;
        }
        $widthPercent = .92;
        $lotsScheduled = $this->hasLotsScheduled($date);
        $this->SetFillColor(0,128,0);
        foreach ($lotsScheduled as $lot) {
            $fill = ($lot['status'] == 'TERMINADO' ? true : false);
            $textColor = ($lot['status'] == 'TERMINADO' ? 255 : 0);
            $this->SetXY($x, $y+4);
            $this->SetTextColor($textColor,$textColor,$textColor);
            $this->SetFont("Arial", "B", $fontSize);
            $this->Cell(9,$height,$lot['time'],0,1,'C', $fill);
            $this->SetFont("Arial", "", $fontSize);
            $this->SetXY($x+9, $y+4);
            $this->Cell(29,$height,number_format($lot['weight'], 2, '.', ',').' KG.',0,1,'R', $fill);
            $this->SetXY($x, $y+$xDisplacement);
            $this->Cell(38,$height,substr(utf8_decode($lot['product']), 0, $subStrLength),0,1,'L', $fill);
            $this->SetXY($x, $y+$xDisplacement+$height);
            if ($lot['customer_id'] && $lot['customer']) {
                $this->Cell(38,$height,substr(utf8_decode($lot['customer']), 0, $subStrLength),0,1,'L', $fill);
            } else {
                $this->Cell(38,$height,substr(utf8_decode($lot['product']), $subStrLength, $subStrLength),0,1,'L', $fill);
            }
            $y += ($height*3)+1;
        }
    }

    function printMonth($date)
    {
        $this->date = $date;
        $this->JDtoYMD($date, $year, $month, $day);
        $this->AddPage();
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = $this->h - $this->tMargin - $this->bMargin;
        $firstLine = 32 + $this->tMargin;
        $monthName = ($this->month == 1 ? 'ENERO' : ($this->month == 2 ? 'FEBRERO' : ($this->month == 3 ? 'MARZO' : ($this->month == 4 ? 'ABRIL' : ($this->month == 5 ? 'MAYO' : ($this->month == 6 ? 'JUNIO' : ($this->month == 7 ? 'JULIO' : ($this->month == 8 ? 'AGOSTO' : ($this->month == 9 ? 'SEPTIEMBRE' : ($this->month == 10 ? 'OCTUBRE' : ($this->month == 11 ? 'NOVIEMBRE' : ($this->month == 12 ? 'DICIEMBRE' : 'OTROS'))))))))))));
        $this->SetXY($this->lMargin,$this->tMargin);
        $this->SetFont("Arial", "B", $this->headerFontSize);
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo_name.png';
        $this->Image($img,5,5,110,0,'PNG');
        $this->SetXY($this->GetPageWidth()-270, $this->GetY()-5);
        $this->Cell($width, $firstLine, strtoupper($monthName.' '.$this->year), 0,0, "R");
        $wd=gmdate("w",jdtounix($date));
        $start = $date - $wd;
        $numDays = $this->nextMonth($date) - $start;
        $numWeeks = 0;
        while ($numDays > 0) {
            $numDays -= 7;
            ++$numWeeks;
        }
        $this->squareHeight = ($height - 6 - $firstLine) / $numWeeks;
        $horizontalLines = array($firstLine,6);
        for ($i = 0; $i < $numWeeks; ++$i) {
            $horizontalLines[$i + 2] = $this->squareHeight;
        }
        $this->squareWidth = $width / 7;
        $verticalLines = array($this->lMargin, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth);
        $x = 0;
        $this->SetFont("Arial", "B", 12);
        $this->SetFillColor(0,112,255);
        $this->SetTextColor(255,255,255);
        for ($i = 1; $i <= 7; ++$i) {
            $x += $verticalLines[$i-1];
            $this->SetXY($x,$firstLine);
            $day = utf8_decode($i == 1 ? 'DOMINGO' : ($i == 2 ? 'LUNES' : ($i == 3 ? 'MARTES' : ($i == 4 ? 'MIÉRCOLES' : ($i == 5 ? 'JUEVES' : ($i == 6 ? 'VIERNES' : 'SÁBADO'))))));
            $this->Cell($verticalLines[$i],6,$day,0,0,"C", true);
        }
        $this->SetTextColor(0,0,0);
        $wd=gmdate("w",jdtounix($date));
        $cur = $date - $wd;
        $this->SetFont("Arial", "B", 20);
        $x = 0;
        $y = $horizontalLines[0];
        for ($k=0;$k<$numWeeks;$k++) {
            $y += $horizontalLines[$k+1];
            for ($i=0;$i<7;$i++ ) {
                $this->JDtoYMD($cur, $curYear, $curMonth, $curDay);
                $x += $verticalLines[$i];
                $this->squareWidth = $verticalLines[$i+1];
                if ($curMonth == $month) {
                    $this->setXY($x, $y-2);
                    $this->printLotsScheduled($cur);
                    $this->SetTextColor(0,112,255);
                    $this->SetFont("Arial", "B", 15);
                    $this->SetXY($x+32,$y+2);
                    $this->Cell(5, 4, $curDay, 0, 0, 'R');
                }
                ++$cur;
            }
            $x = 0;
        }
        $ly = 0;
        $ry = 0;
        foreach ($horizontalLines as $key => $value) {
            $ly += $value;
            $ry += $value;
            $this->Line($this->lMargin,$ly,$this->lMargin+$width,$ry);
        }
        $lx = 0;
        $rx = 0;
        foreach ($verticalLines as $key => $value) {
            $lx += $value;
            $rx += $value;
            $this->Line($lx,$firstLine,$rx,$firstLine + 6 + $this->squareHeight * $numWeeks);
        }
    }
}

class PDFScrap extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $lotNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo_name.png';
        $this->Image($img,5,5,75,0,'PNG');
        $this->SetTextColor(4, 26, 131);
        $this->SetFont('Arial','B',20);
        $this->SetFillColor(218, 221, 238);
        $this->SetX($this->GetX()+99);
        $this->MultiCell(97, 12, utf8_decode("SCRAPS LOTE $this->lotNumber"), 0, 'R', false);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "WWW.TECHNOFIBERS.COM", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(4, 26, 131);
        $this->SetTextColor(255);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetLotNumber($o)
    {
        $this->lotNumber=$o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}