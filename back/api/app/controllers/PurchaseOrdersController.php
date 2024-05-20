<?php

use Phalcon\Mvc\Controller;
require_once __DIR__ . '/../..//vendor/autoload.php';

class PurchaseOrdersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOrders ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser !== null) {
            $sql = "SELECT role_id
                    FROM sys_users
                    WHERE id = $validUser->id;";
            $currentRoles = $this->db->query($sql)->fetchAll();
            $roles = [];

            foreach ($currentRoles as $role) {
                array_push($roles, intval($role['role_id']));
            }

            if (in_array(1, $roles) || in_array(5, $roles) || in_array(10, $roles) || in_array(19, $roles) || in_array(20, $roles) || in_array(28, $roles)) {
                $this->content['orders'] = PurchaseOrders::find(['order' => 'id ASC']);
                $this->content['result'] = true;
            } elseif (in_array(2, $roles)) {
                $sql = "SELECT id, serial, status, supplier_id, producer, TO_CHAR(requested_date::DATE, 'dd/mm/yyyy') as requested_date
                        FROM pur_orders
                        WHERE status = 'APROBADO'";
                $data = $this->db->query($sql);
                // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $this->content['orders'] = $data->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
            
            $this->response->setJsonContent($this->content);
        }
    }

    public function getGrid () {
        $request = $this->request->getPost();
        $suppliers = null;
        if (is_array($request['suppliers'])) {
            $suppliers = $request['suppliers']['value'];
        }else {
            $suppliers = $request['suppliers'];
        }
        $date1 = $request['saleDatev1'];
        $date2 = $request['saleDatev2'];
        $status = null;
        if (is_array($request['status'])) {
            $status = $request['status']['value'];
        }else {
            $status = $request['status'];
        }
        $where2 = null;
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1?' WHERE o.id > 0 ':" WHERE e.branch_office_id = $validUser->branch_office_id ";

        if ($validUser !== null) {
            $content = $this->content;
            $sql = "SELECT role_id
                    FROM sys_users
                    WHERE id = $validUser->id;";
            $currentRoles = $this->db->query($sql)->fetchAll();
            $roles = [];
            foreach ($currentRoles as $role) {
                array_push($roles, intval($role['role_id']));
            }
            if (in_array(1, $roles) || in_array(22, $roles) || in_array(26, $roles) || in_array(20, $roles) || in_array(28, $roles)) {
                
                
                if ($suppliers == 0 && is_numeric($suppliers)) {
                    $where .= "";
                }else if (strlen($suppliers) > 0 && is_numeric($suppliers)) {
                    $where .= " and s.id = $suppliers ";
                }
                if ($status == "TODOS") {
                    $where .= "";
                }else if (strlen($status) > 0) {
                    $where .= " and o.status =  '".$status."' ";
                }
                if (strlen($date1) > 0) {
                    $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
                    
                    $where .= " and to_char(ph.created,'YYYY-MM-DD H:i:s') >=  '".$dateIni."' ";
                }
                if (strlen($date2) > 0) {
                    $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 00:00:00.000000'));
                    $where .= " and to_char(ph.created,'YYYY-MM-DD H:i:s') <=  '".$dateFin."' ";
                }

                //$where .= " and to_char(ph.created,'YYYY-MM-DD H:i:s')  between '".$dateIni."' and '".$dateFin."'";
                $sql = "SELECT o.id, o.serial, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date,TO_CHAR(ph.created :: DATE, 'dd/mm/yyyy') AS order_date, s.name AS supplier, COUNT(ship_inv.id) AS invoices_qty, COUNT(ship.id) AS shipments_qty
                FROM pur_orders AS o
                INNER JOIN pur_suppliers AS s
                ON o.supplier_id = s.id
                LEFT JOIN pur_order_history AS ph
                ON ph.order_id = o.id  AND ph.status = 'PEDIDO'
                LEFT JOIN pur_shipments AS ship
                ON ship.order_id = o.id
                LEFT JOIN pur_shipments AS ship_inv
                ON ship_inv.order_id = o.id AND ship_inv.invoice IS NOT NULL AND ship_inv.invoice <> ''
                JOIN wms_storages e on e.id = o.storage_id
                $where
                GROUP BY o.id, o.serial, o.status, o.supplier_id, o.producer, requested_date, supplier,order_date ORDER BY serial DESC;";
                //var_dump($sql);
                $data = $this->db->query($sql);
                $content['orders'] = $data->fetchAll();
                $content['result'] = true;
            } elseif (in_array(2, $roles)) {
                $ordenes = [];
                $data = $this->getOrderStatus('PEDIDO','PARCIAL');
                foreach ($data as $key => $shipment) {
                    $sql = "SELECT DISTINCT ON (od.product_id) od.*, p.name AS product, COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada
                    FROM pur_order_details AS od
                    INNER JOIN wms_products AS p
                    ON od.product_id = p.id
                    LEFT JOIN pur_shipments as sh
                    ON sh.order_id = od.po_id
                    LEFT JOIN pur_shipment_details as shd
                    ON shd.shipment_id = sh.id and od.product_id = shd.product_id and  sh.status = 'RECIBIDO'
                    $where AND od.po_id = ".intval($shipment['id'])."
                    GROUP BY od.id, p.name
                    ORDER BY od.product_id, product ASC;";
                $orderDetails = $this->db->query($sql)->fetchAll();
                foreach ($orderDetails as $key => $detail) {
                    if ($detail['restante'] > 0) {
                        $shipment['hayrestante'] = true;
                    }
                }
                array_push($ordenes,$shipment);
                }
                $resultado = [];
                foreach($ordenes as $key => $detail) {
                    if ($detail['hayrestante']){
                        $sql = "SELECT o.id, o.serial, ship.id as shipment_id, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date,TO_CHAR(ph.created :: DATE, 'dd/mm/yyyy') AS order_date, s.name AS supplier, COUNT(ship_inv.id) AS invoices_qty, COUNT(ship.id) AS shipments_qty
                        FROM pur_orders AS o
                        INNER JOIN pur_suppliers AS s
                        ON o.supplier_id = s.id
                        LEFT JOIN pur_order_history AS ph
                        ON ph.order_id = o.id  AND ph.status = 'PEDIDO'
                        FULL JOIN pur_shipments AS ship
                        ON  ship.order_id = o.id
                        FULL JOIN pur_shipments AS ship_inv
                        ON ship_inv.order_id = o.id AND ship_inv.invoice IS NOT NULL AND ship_inv.invoice <> ''
                        WHERE (o.status = 'PEDIDO' OR o.status = 'PARCIAL') and (ship.order_id = ".intval($detail['id'])." and ship.movement_id is null)
                        GROUP BY o.id, o.serial, o.status, o.supplier_id, o.producer, requested_date, supplier,order_date,shipment_id ORDER BY serial ASC;";
                        $data2 = $this->db->query($sql)->fetchAll();
                        if ($data2) {
                            array_push($resultado,$data2[0]);
                        } else {
                            array_push($resultado,$detail);
                        }
                    } else {
    
                    }
                }
                $content['orders'] = $resultado;
                //$content['orders'] = $this->getOrderStatus('PEDIDO','PARCIAL');
                $content['result'] = true;
            } elseif (in_array(21,$roles)) {
                $content['orders'] = $this->getOrderStatus('COTIZADO',NULL);;
                $content['result'] = true;
            } elseif (in_array(22,$roles)) {
                $content['orders'] = $this->getOrderStatus('NUEVO',NULL);
                $content['result'] = true;
            }else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
            $this->response->setJsonContent($content);
            $this->response->send();
        }
        

    }
    public function searchClave ($filter)
    {
            $content = $this->content;
            $sql = "SELECT p.id as value, p.serial, CONCAT(p.serial,' - ',TO_CHAR(p.requested_date :: DATE, 'dd/mm/yyyy'),' - ',substring(s.name,0,20)) AS label
            FROM pur_orders as p
            INNER JOIN pur_suppliers as s
            ON p.supplier_id = s.id
            WHERE CAST(p.serial as text) ILIKE '%".$filter."%' OR s.name ILIKE '%".$filter."%' LIMIT 5";
            $data = $this->db->query($sql);
            $content['claves'] = $data->fetchAll();
       
        $this->response->setJsonContent($content);
        $this->response->send();
        
    }
    // comienza debts
    #Purchase payments code 
    public function getPurchasesPaymentsGridPagination ()
    {
        $request = $this->request->getPost();
        $status = [];
        if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()){
            if(!isset($request['status'])){
                $status = [];
            }else{
                $status = $request['status'];
            }
            $response = $this->getGridPaymentsSQL($request['supplier'],$request['saleDatev1'],$request['saleDatev2'],$status,$request['type'],$request);
            $this->content['purchases'] = $response['data'];
            $this->content['purchasesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getGridPaymentsSQL ($id,$date1,$date2,$status,$type,$request) {
        $validUser = Auth::getUserInfo($this->config);        
        $y = date('Y');
        $where = "";
        $order = "";
        if (count($status) > 0) {
            $numbers = [];
                foreach ($status as $detail){
                    $numbers[] =  $detail;
                }
            $numbers = implode(',',$numbers);
            $where = " WHERE po.status IN ('PEDIDO','PARCIAL', 'RECIBIDO') AND po.status_payment in ($numbers) ";
        } else {
            $where = "WHERE (po.status IN ('PEDIDO','PARCIAL', 'RECIBIDO') OR po.status_timbrado = 1) ";
        }
        $where .= $validUser->role_id == 1 ? ' ' : " AND e.branch_office_id = $validUser->branch_office_id ";

        if ($id == 'TODOS') {} else if($id == ''){}else {$where .= " AND po.supplier_id = $id";}
        if ($date1 === '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-10 year', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === '') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        $where .= " AND (TO_CHAR(cast(po.invoice_date as DATE) + CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD 00:00:00.000000') BETWEEN '".$dateIni."' AND '".$dateFin."' or po.invoice_date is null)  ";
//        if($type == 0){
//            $order = " ORDER BY id DESC;";
//        }else{
//            $order = " ORDER BY id ASC;";
//        }
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)){
            $where .= " AND ( po.serial::text ILIKE '%".$filter."%' OR s.name ILIKE '%".$filter."%' OR TO_CHAR(ph.created, 'dd/mm/yyyy') ILIKE '%".$filter."%' OR TO_CHAR(po.requested_date, 'dd/mm/yyyy') ILIKE '%".$filter."%')";
        }

        if (!empty($pagination['sortBy'])) {
            if (trim($pagination['sortBy']) == 'order_date') {
                $sortBy .= " ORDER BY ph.created";
            } else {
                $sortBy .= " ORDER BY " . trim($pagination['sortBy']);
            }
        } else {
            $sortBy .= " ORDER BY po.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        // $limit = " LIMIT " . $pagination['rowsPerPage']." ";
        if (intval($pagination['rowsPerPage']) != 0) {
            $limit = " LIMIT " . $pagination['rowsPerPage'];
        } else  {
            $limit = " ";
        }
        $sql = "SELECT count(a.*) AS count FROM ( SELECT po.serial
                FROM pur_orders AS po
                INNER JOIN pur_order_details as pod on pod.po_id = po.id
                INNER JOIN pur_suppliers AS s
                ON po.supplier_id = s.id
                LEFT JOIN pur_order_history AS ph
                ON ph.order_id = po.id  AND ph.status = 'PEDIDO'
                JOIN wms_storages e on e.id = po.storage_id
                {$where}
                group by po.id, ph.created,s.name)a";
        $invoicesCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT po.id,  po.reference, po.status_payment, po.serial, po.status, po.invoice, TO_CHAR(po.invoice_date, 'DD/MM/YYYY') as invoice_date,TO_CHAR(cast(po.invoice_date as DATE) +CAST(s.credit_days||' days' AS INTERVAL),'DD/MM/YYYY') as expiration,
                (select COALESCE((SELECT sum(pop.amount) from pur_order_payments as pop where pop.po_id = po.id), 0)) as abonado,
                (select COALESCE((SELECT sum(pod.qty * trunc(pod.price::numeric,5)) + COALESCE(sum(pod.ieps),0) + COALESCE(sum(pod.vat),0) + COALESCE(po.shipping_price,0) from pur_order_details as pod where pod.po_id = po.id), 0)) as totalamount,
                (select COALESCE((SELECT sum(pod.qty * pod.price) from pur_order_details as pod where pod.po_id = po.id), 0)) as restante,
                TO_CHAR(ph.created, 'dd/mm/yyyy') AS order_date, to_char(po.requested_date,'DD/MM/YYYY') AS requested_date, po.status_timbrado, s.name as supplier
                FROM pur_orders AS po
                LEFT join pur_order_details as pd on pd.po_id = po.id
                INNER JOIN pur_suppliers AS s
                ON po.supplier_id = s.id
                LEFT JOIN pur_order_history AS ph
                ON ph.order_id = po.id  AND ph.status = 'PEDIDO'
                JOIN wms_storages e on e.id = po.storage_id
                {$where}
                group by po.id, ph.created,s.name, s.credit_days
                {$sortBy} {$desc} {$offset} {$limit}
                ";
        $data = $this->db->query($sql)->fetchAll();
        $sqlQuerypurdetailsproducts = "SELECT pursh.order_id, coalesce(po.qty-purshd.qty,po.qty) as restante, ((po.price * po.qty) + po.vat) as total  FROM pur_order_details as po
        left join pur_shipments as pursh on pursh.order_id = po.po_id
        left join pur_shipment_details as purshd on 
        purshd.shipment_id = pursh.id and po.product_id = purshd.product_id  
        where pursh.status = 'RECIBIDO';";
                $Querypurdetailsproducts = $this->db->query($sqlQuerypurdetailsproducts)->fetchAll();
                //var_dump($Querypurdetailsproducts);
        
       $sumAll = 0;
       $arrayResult = [];
       $addResult = false;
       $restante = 0;
       foreach ($data as $key => $d){
            foreach ($Querypurdetailsproducts as $valuee) {
                if ($d['id'] == $valuee['order_id'] && $valuee['restante'] == 0) {
                    $sumAll += floatval($valuee['total']);
                    $addResult = true;
                }
            }
            if ($addResult) {
                $restante = floatval($sumAll - $d['abonado']);
                array_push($arrayResult, 
            array('id' => $d['id'], 'serial' => $d['serial'], 'status' => $d['status'], 'reference' => $d['reference'],'status_payment' => $d['status_payment'], 'invoice_date' => $d['invoice_date'], 'requested_date' => $d['requested_date'],'expiration' => $d['expiration'], 'supplier' => $d['supplier'], 'totalamount' => $sumAll, 'abonado' =>  $d['abonado'], 'restante' => $restante));
            }
            $addResult = false;
            $sumAll = 0;
           $id = $d['id'];
           $totales = floatval($d['totalamount']);
           $resta = $totales - floatval($d['abonado']);
           $data[$key]['restante'] = floatval($resta); 
       }
        $response = array('data' => $arrayResult, 'rowCounts' => $invoicesCount[0]['count']);
        return $response;
    }

    public function dataFromPurchaseOrder () {
        $request = $this->request->getPost();
        $id = $request['id'];
        // Obtener cuanto se tiene que pagar
        // $tA = $this->getAmountsFromInvoiceSQL($id);
        // po.fin_account_id as account, 
        $sql = "SELECT COALESCE((SELECT sum(pod.qty * trunc(pod.price::numeric,5)) + COALESCE(sum(pod.ieps),0) + COALESCE(sum(pod.vat),0) + COALESCE(po.shipping_price,0) from pur_order_details as pod where pod.po_id = po.id), 0) as totalamount
                FROM pur_orders as po
                where po.id = $id";
                $tA = $this->db->query($sql)->fetch();

        //Obtener total solo de los productos que se han recibido
        $sqlTotalProductsRecived = "SELECT pursh.order_id, coalesce(po.qty-purshd.qty,po.qty) as restante, ((po.price * po.qty) + po.vat) as total  FROM pur_order_details as po
        left join pur_shipments as pursh on pursh.order_id = po.po_id
        left join pur_shipment_details as purshd on 
        purshd.shipment_id = pursh.id and po.product_id = purshd.product_id  
        where pursh.status = 'RECIBIDO' and pursh.order_id = $id";
        $queryProductsRecibed = $this->db->query($sqlTotalProductsRecived)->fetchAll();
        $suammAll = 0;

        
        foreach ($queryProductsRecibed as $value) {
            # code...
            if ($value['restante'] == 0) {
                $suammAll += floatval($value['total']);
            }
        }
        // Obtener los pagos que se han hecho
        $payments = $this->getDataFromPayments($id);

        $this->content['result'] = true;
        //$this->content['total_amount'] = $tA ? $tA['totalamount'] : 0;
        $this->content['total_amount'] = $suammAll;
        // comente este 12-01-2022 
        // $this->content['account'] = $tA ? $tA['account'] : null;
        $this->content['payments'] = $payments;

        $this->response->setJsonContent($this->content);
    }
    public function getDataFromPayments ($id) {
        // comente este 12-01-2022 
        // LEFT join fin_bank_accounts as ba on ba.id = sp.bank_account_id
        // ba.name as bank,
        // LEFT JOIN fin_movements as fm on fm.pur_order_payment_id = sp.id
        // fm.id as movement_id,fa.name as account, fo.name as output,
        // LEFT JOIN fin_accounts as fa on fa.id = fm.account_type_id
        // LEFT JOIN fin_outputs_types as fo on fo.id = fm.output_id
        $sql = "SELECT sp.*, to_char(sp.payment_date,'DD/MM/YYYY') as payment_date, sfp.descripcion as method_label, sysd.filename
        FROM pur_order_payments AS sp
        LEFT JOIN sat_formas_pagos AS sfp ON sfp.id = sp.method
        LEFT JOIN sys_documents as sysd on sysd.id = sp.document_id
            WHERE sp.po_id = $id
            ORDER BY sp.id
            ;";
        $data = $this->db->query($sql)->fetchAll();

        return $data;
    }
    public function getGridPayments ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
            if(!isset($request['status'])){
                $status = [];
            }else{
                $status = $request['status'];
            }
            if(!isset($request['term_status'])){
                $term_status = [];
            }else{
                $term_status = $request['term_status'];
            }
            if(!isset($request['timbrado_status'])){
                $request['timbrado_status'] = [];
            }else{
                $request['timbrado_status'] = $request['timbrado_status'];
            }
            if(!isset($request['supplier'])){
                $request['supplier'] = '';
            }else{
                $request['supplier'] = $request['supplier'];
            }
            $response = $this->getGridPaymentsSQL($request['supplier'],$request['saleDatev1'],$request['saleDatev2'],$status,$request['type'],$request);
            $this->content['purchases'] = $response['data'];
            $this->content['purchasesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function addPayment () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $id = $request['id'];
        $validUser = Auth::getUserData($this->config);

        $purOrder = PurchaseOrders::findFirst("id = $id");
        if ($purOrder) {
            $payment = new PurchaseOrderPayments();
            $payment->setTransaction($tx);
            $payment->created_by = $validUser->id;
            $payment->po_id = intval($purOrder->id);
            $payment->amount = floatval($request['qty']);
            $payment->payment_date = $request['date'];
            $payment->reference = $request['ref'];
            $payment->method = $request['method'];
            //$payment->bank_account_id = $request['bank_account_id'] ? $request['bank_account_id'] : null;

            if ($payment->create()) {
                $purOrder->setTransaction($tx);
                $purOrder->status_payment = $request['status'] === 'PAGADO' ? 2 : 1;
                $purOrder->payment_date = $request['status'] === 'PAGADO' ? $request['date'] : null;

                if (!$purOrder->update()) {
                    $this->content['error'] = Helpers::getErrors($purOrder);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago (2).');
                    $tx->rollback();
                }
                // Se debe de agregar el abono a los movimientos 
                $payment_method = "SELECT * FROM sat_formas_pagos where id = ". intval($request['method']);
                $method = $this->db->query($payment_method)->fetch();
                $order = PurchaseOrders::findFirst(intval($payment->po_id));
                $actualAccount = Auth::getUserAccount($validUser->id);
                #Validar si quiero o no registar el abono en la tabla de movimientos
                if (isset($request['save_payment']) && intval($request['save_payment']) == 1) {
                    $account = new MovementsTrade();
                    $account->setTransaction($tx);
                    // se comentó 12-01-2022
                    // $account->account_type_id = intval($request['account']); // El 8 hace referencia a VENTA
                    // se comentó 12-01-2022
                    // $account->output_id = intval($request['output']); // 29 es Ventas de outputs
                    $account->amount = floatval($request['qty']);
                    $account->expense_date = $request['date'];
                    $account->movement = 'CARGO';
                    $account->description = 'Pago OC #'.$order->serial. ' , forma de pago: '. $method['descripcion'];
                    $account->pur_order_payment_id = $payment->id;
                    
                    $account->account_id = $actualAccount;
    
                    if ($account->create()) {
                        //var_dump($payment->id);
                        // $this->content['message_bank'] = Message::success('Movimiento de banco creado exitosamente.');
                        $this->content['result'] = true;
                        $this->content['id'] = $id;
                        $this->content['message'] = Message::success('Pago agregado correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($account);
                        $this->content['message_bank'] = Message::error('Ha ocurrido un error al intentar agregar el pago.');
                    }
                } else {
                    $this->content['result'] = true;
                    $this->content['id'] = $id;
                    $this->content['message'] = Message::success('Pago agregado correctamente.');
                    $tx->commit();
                }
            } else {
                $this->content['error'] = Helpers::getErrors($payment);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago.');
                $tx->rollback();
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function uploadPaymentFile($id)
    {
        $request = $this->request->getPost();
        $idFile = 0;
        if ($this->userHasPermission()) {
            // Check if the user has uploaded files
            if ($this->request->hasFiles()) {
                $upload_dir = dirname(__FILE__)  . '/../../public/documentspay/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0755);
                }
                $files = $this->request->getUploadedFiles();
                // Print the real file names and sizes
                foreach ($files as $file) {
                    // Print file details
                    // Move the file into the application
    
                    $type = $file->getType();
                    $size = $file->getSize();
                    $extension = $file->getExtension();
                    $name = $file->getName();

                    $file_name = md5(date('d-m-Y h:i:s').$type.$size);

                    $fileNew = new Documents();
                    $fileNew->filename = $name;
                    $fileNew->ext = $extension;
                    $fileNew->size = $size;
                    $fileNew->mimetype = $type;
    
                    if($fileNew->create()){
                        $idFile = $fileNew->id;
                        $result = $file->moveTo(
                            dirname(__FILE__)  . '/../../public/documentspay/' . $fileNew->id
                        );
                        
                        $this->content['img_id'] = $fileNew->id;
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se ha cargado el archivo correctamente.');
                    }else{
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($fileNew);
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }
                }
                
            }else{
                $this->content['message'] = Message::error('No se guardo ningun archivo.');
            }
        } else {
            $this->content = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->updatePurOrderPayment($id, $idFile);
        $this->response->setJsonContent( $this->content);
        $this->response->send();
    }

    public function updatePurOrderPayment ($id, $iddocument) {
        /* var_dump($id);
        var_dump($iddocument); */
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $payment = PurchaseOrderPayments::findFirst($id);
            if ($payment) {
                $payment->setTransaction($tx);
                $payment->document_id = intval($iddocument);
                if ($payment->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Se agregó archivo a la tabla.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($payment);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el El archivo en la tabla.');
                    $tx->rollback();
                }
            }
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
    }

    public function deleteFilePayment ($id) {
        $tx = $this->transactions->get();
        try {
            if ($this->userHasPermission()) {
                $order = PurchaseOrderPayments::findFirst($id);
                $myDocuments = Documents::findFirst("id = ". $order->document_id);
                
                if ($order){
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/documentspay/'.$order->document_id.'';
                            
                    unlink($upload_dir);
                    $order->setTransaction($tx);
                    $order->document_id = null;
                    if ($order->update()){
                        
                        if ($myDocuments->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo ha sido eliminado correctamente.');
                            $tx->commit();
                        }
                    } else {
                        $this->content['result'] = true;
                            $this->content['message'] = Message::success('El id de la tabla no se ha modificado.');
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al eliminar el archivo.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function deletePayment () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $id = intval($request['id']);
        $po_id = $request['po_id'];
        $flag = false;
        $order = PurchaseOrderPayments::findFirst($id);
        $order->setTransaction($tx);
        $account = MovementsTrade::findFirst("pur_order_payment_id = ". intval($id));
        
        if ($order->document_id == null) {
            $flag = false;
        }else {
            $flag = true;
            $myDocuments = Documents::findFirst("id = ". $order->document_id);
        } 
        if ($account) {
            if ($account->delete()) {
                if ($order->delete()) {
                    // quite esto 13-01-2022 $order->po_id
                    // /public/assets/purchase-orders/payments/'.$order->po_id.'/
                    if ($flag) {
                        $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/documentspay/'.$order->document_id.'';
                        
                        // quite esto 13-01-2022 $upload_dir.$order->po_id.$order->id.'.'.$order->file_type
                        unlink($upload_dir);
                        $myDocuments->delete();
                    }
                    if($request['status'] === 'ABONADO'){
                        $sql = "SELECT * FROM pur_order_payments AS p  WHERE p.po_id = $po_id ORDER BY p.id DESC;";
                        $data = $this->db->query($sql)->fetchAll();
                        $purOrder = PurchaseOrders::findFirst("id = $po_id");
                        $purOrder->setTransaction($tx);
                        $purOrder->status_payment = $data ? 1 : 0;
                        $purOrder->payment_date = null;
                        if (!$purOrder->update()) {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de pago.');
                            $tx->rollback();
                        }
                    }
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El pago ha sido eliminado.');
                    $tx->commit();
                } else {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el pago.');
                    $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el pago.'); 
            }
        } else {
            if ($order->delete()) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/payments/'.$order->po_id.'/';
                if($order->file != ''){
                    unlink($upload_dir.$order->po_id.$order->id.'.'.$order->file_type);
                }
                if($request['status'] === 'ABONADO'){
                    $sql = "SELECT * FROM pur_order_payments AS p  WHERE p.po_id = $po_id ORDER BY p.id DESC;";
                    $data = $this->db->query($sql)->fetchAll();
                    $purOrder = PurchaseOrders::findFirst("id = $po_id");
                    $purOrder->setTransaction($tx);
                    $purOrder->status_payment = $data ? 1 : 0;
                    $purOrder->payment_date = null;
                    if (!$purOrder->update()) {
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de pago.');
                        $tx->rollback();
                    }
                }
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El pago ha sido eliminado.');
                $tx->commit();
            } else {
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el pago.');
                $tx->rollback();
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function getPdfFromPurchasePayments ($supplier,$status,$date1,$date2)
    {
        $pdf = $this->createPdfFromPurchasePayments($supplier,$status,$date1,$date2);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Cobranza_Ordenes_Pago.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="ReporteOrdenesPorPagar.pdf"');
            return $response;
        }
    }

    public function createPdfFromPurchasePayments ($supplier,$status,$date1,$date2)
    {   
        $validUser = Auth::getUserInfo($this->config);
        $y = date('Y');
        $where = "";
        $order = "";
        if ($status != 99) {
            $where = " WHERE po.status IN ('PEDIDO','PARCIAL', 'RECIBIDO') AND po.status_payment in ($status) ";
        } else {
            $where = "WHERE (po.status IN ('PEDIDO','PARCIAL', 'RECIBIDO') OR po.status_timbrado = 1) ";
        }
        $where .= $validUser->role_id == 1?'':" AND e.branch_office_id = $validUser->branch_office_id ";
        if ($supplier == 'TODOS') {} else if($supplier == ''){}else {$where .= " AND po.supplier_id = $supplier";}
        if ($date1 === 'null') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-10 year', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        $where .= " AND TO_CHAR(cast(po.invoice_date as DATE) + CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD 00:00:00.000000') BETWEEN '".$dateIni."' AND '".$dateFin."'";
        // if (!empty($filter)){
        //     $where .= " AND ( po.serial::text ILIKE '%".$filter."%' OR s.name ILIKE '%".$filter."%' OR TO_CHAR(ph.created, 'dd/mm/yyyy') ILIKE '%".$filter."%' OR TO_CHAR(po.requested_date, 'dd/mm/yyyy') ILIKE '%".$filter."%')";
        // }
        
        $sql = "SELECT po.id, po.status_payment, po.serial, po.status,po.invoice,po.invoice_date, TO_CHAR(po.invoice_date, 'DD/MM/YYYY') as invoice_date,TO_CHAR(po.invoice_date, 'DD/MM/YYYY') as invoice_date,TO_CHAR(cast(po.invoice_date as DATE) +CAST(s.credit_days||' days' AS INTERVAL),'DD/MM/YYYY') as expiration,
                (select COALESCE((SELECT sum(pop.amount) from pur_order_payments as pop where pop.po_id = po.id), 0)) as abonado,
                (select COALESCE((SELECT sum(pod.qty * trunc(pod.price::numeric,5)) + COALESCE(sum(pod.ieps),0) + COALESCE(sum(pod.vat),0) + COALESCE(po.shipping_price,0) from pur_order_details as pod where pod.po_id = po.id), 0)) as totalamount,
                (select COALESCE((SELECT sum(pod.qty * pod.price) from pur_order_details as pod where pod.po_id = po.id), 0)) as restante,
                TO_CHAR(ph.created, 'dd/mm/yyyy') AS order_date, to_char(po.requested_date,'DD/MM/YYYY') AS requested_date, po.status_timbrado, s.name as supplier
                FROM pur_orders AS po
                LEFT join pur_order_details as pd on pd.po_id = po.id
                INNER JOIN pur_suppliers AS s
                ON po.supplier_id = s.id
                LEFT JOIN pur_order_history AS ph
                ON ph.order_id = po.id  AND ph.status = 'PEDIDO'
                JOIN wms_storages e on e.id = po.storage_id
                {$where}
                group by po.id, ph.created,s.name, s.credit_days
                order by po.serial DESC
                ";
        $data = $this->db->query($sql)->fetchAll();
        if ($date1 === 'null') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-10 year', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDFPurchaseOrder2();
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(0.1);
        $pdf->encabezadoCP();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(190, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA INICIO: '.$fechaIni);

        $pdf->SetXY(235, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA FIN: '.$fechaFin);

        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);


        $pdf->SetXY(5, 45);
        $pdf->SetFont('', '', 7);

        $pdf->SetWidths(array(30,100,30,50,30,30));
        $pdf->SetAligns(array('C','L','C','C','C','R'));
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.4);

        $i = 1;
        $totalesAbonado = 0;
        $totalesRestante = 0;
        $totalesTotales = 0;
        foreach ($data as $row) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 45) {
                $pdf->AddPage('L', 'Letter');
                $pdf->encabezadoCP();
                $pdf->SetXY(0, 45);
                $pdf->SetFont('', '', 7);
            }
            $pdf->SetX(5);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(255,255,255);
            $status = '';
            if ($row['status_payment'] == 0) {
                $status = 'PENDIENTE';
            }elseif($row['status_payment'] == 1){
                $status = 'ABONADO';
            }elseif($row['status_payment'] == 2){
                $status = 'PAGADO';
            }
            $pdf->Row2(array($row['serial'], utf8_decode($row['supplier']),$row['invoice_date'],$row['requested_date'],$row['expiration'],'$'.number_format(floatval($row['totalamount']), 2, '.', ',')));
            $i++;
            // $totalesAbonado += $row['cantidad_restante'];
            // $totalesRestante += $row['abonado'];
            // $totalesTotales += $row['cantidad_total'];
        }
        $pdf->SetTitle(utf8_decode('Reporte de Pagos'));
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', 'reporte_pagos.pdf', true);

        return $pdf;
    }

    public function uploadPaymentFile4 ($id)
    {
        if (is_numeric($id)) {
            try {
                $msg = "";
                $tx = $this->transactions->get();
                $order = PurchaseOrders::findFirst($id);
                $request = $this->request->getPost();
                if ($order) {
                    $sql  = "SELECT id FROM pur_order_payments where po_id = $id ORDER BY id  DESC LIMIT 1";
                    $lasId = $this->db->query($sql)->fetch()['id'];
                    $payment = PurchaseOrderPayments::findFirst($lasId);
                    $payment->setTransaction($tx);
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/payments/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    /* $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/payments/'.$order->id.'/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    } */
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $order->id . $lasId.'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($payment->file != null && file_exists($upload_dir.$order->id . $lasId.'.'.$extension)) {
                            @unlink($upload_dir.$order->id . $lasId.'.'.$extension);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $payment->file = $fileName;
                        $payment->file_type = $extension;
                        if ($payment->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo se ha subido exitosamente.');
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Error al subir el archivo.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el documento.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function getOrderStatus ($status1 = null, $status2 = null) {
        $sql = "SELECT o.id, o.serial, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date,TO_CHAR(ph.created :: DATE, 'DD/MM/YYYY') AS order_date, s.name AS supplier, COUNT(ship_inv.id) AS invoices_qty, COUNT(ship.id) AS shipments_qty
        FROM pur_orders AS o
        INNER JOIN pur_suppliers AS s
        ON o.supplier_id = s.id
        LEFT JOIN pur_order_history AS ph
        ON ph.order_id = o.id  AND ph.status = 'PEDIDO'
        LEFT JOIN pur_shipments AS ship
        ON ship.order_id = o.id
        LEFT JOIN pur_shipments AS ship_inv
        ON ship_inv.order_id = o.id AND ship_inv.invoice IS NOT NULL AND ship_inv.invoice <> ''
        WHERE o.status = '".$status1."' or o.status = '".$status2."'
        GROUP BY o.id, o.serial, o.status, o.supplier_id, o.producer, requested_date, supplier,order_date ORDER BY serial DESC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function getOrdersWithSupplierName ($pt = 0)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1?' WHERE o.id > 0 ':" WHERE e.branch_office_id = $validUser->branch_office_id ";
        if ($validUser !== null) {
            $content = $this->content;
            $sql = "SELECT role_id
                    FROM sys_users
                    WHERE id = $validUser->id;";
            $currentRoles = $this->db->query($sql)->fetchAll();
            $roles = [];
            foreach ($currentRoles as $role) {
                array_push($roles, intval($role['role_id']));
            }
            if (in_array(1, $roles) || in_array(22, $roles) || in_array(26, $roles) || in_array(20, $roles) || in_array(28, $roles)) {
                $sql = "SELECT o.id, o.serial, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date,TO_CHAR(ph.created :: DATE, 'dd/mm/yyyy') AS order_date, s.name AS supplier, COUNT(ship_inv.id) AS invoices_qty, COUNT(ship.id) AS shipments_qty
                FROM pur_orders AS o
                INNER JOIN pur_suppliers AS s
                ON o.supplier_id = s.id
                LEFT JOIN pur_order_history AS ph
                ON ph.order_id = o.id  AND ph.status = 'PEDIDO'
                LEFT JOIN pur_shipments AS ship
                ON ship.order_id = o.id
                LEFT JOIN pur_shipments AS ship_inv
                ON ship_inv.order_id = o.id AND ship_inv.invoice IS NOT NULL AND ship_inv.invoice <> ''
                JOIN wms_storages e on e.id = o.storage_id
                $where
                GROUP BY o.id, o.serial, o.status, o.supplier_id, o.producer, requested_date, supplier,order_date ORDER BY serial DESC;";
                $data = $this->db->query($sql);
                $content['orders'] = $data->fetchAll();
                $content['result'] = true;
            } elseif (in_array(2, $roles)) {
                $ordenes = [];
                $data = $this->getOrderStatus('PEDIDO','PARCIAL');
                foreach ($data as $key => $shipment) {
                    $sql = "SELECT DISTINCT ON (od.product_id) od.*, p.name AS product, COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada
                    FROM pur_order_details AS od
                    INNER JOIN wms_products AS p
                    ON od.product_id = p.id
                    LEFT JOIN pur_shipments as sh
                    ON sh.order_id = od.po_id
                    LEFT JOIN pur_shipment_details as shd
                    ON shd.shipment_id = sh.id and od.product_id = shd.product_id and  sh.status = 'RECIBIDO'
                    $where AND od.po_id = ".intval($shipment['id'])."
                    GROUP BY od.id, p.name
                    ORDER BY od.product_id, product ASC;";
                $orderDetails = $this->db->query($sql)->fetchAll();
                foreach ($orderDetails as $key => $detail) {
                    if ($detail['restante'] > 0) {
                        $shipment['hayrestante'] = true;
                    }
                }
                array_push($ordenes,$shipment);
                }
                $resultado = [];
                foreach($ordenes as $key => $detail) {
                    if ($detail['hayrestante']){
                        $sql = "SELECT o.id, o.serial, ship.id as shipment_id, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date,TO_CHAR(ph.created :: DATE, 'dd/mm/yyyy') AS order_date, s.name AS supplier, COUNT(ship_inv.id) AS invoices_qty, COUNT(ship.id) AS shipments_qty
                        FROM pur_orders AS o
                        INNER JOIN pur_suppliers AS s
                        ON o.supplier_id = s.id
                        LEFT JOIN pur_order_history AS ph
                        ON ph.order_id = o.id  AND ph.status = 'PEDIDO'
                        FULL JOIN pur_shipments AS ship
                        ON  ship.order_id = o.id
                        FULL JOIN pur_shipments AS ship_inv
                        ON ship_inv.order_id = o.id AND ship_inv.invoice IS NOT NULL AND ship_inv.invoice <> ''
                        WHERE (o.status = 'PEDIDO' OR o.status = 'PARCIAL') and (ship.order_id = ".intval($detail['id'])." and ship.movement_id is null)
                        GROUP BY o.id, o.serial, o.status, o.supplier_id, o.producer, requested_date, supplier,order_date,shipment_id ORDER BY serial ASC;";
                        $data2 = $this->db->query($sql)->fetchAll();
                        if ($data2) {
                            array_push($resultado,$data2[0]);
                        } else {
                            array_push($resultado,$detail);
                        }
                    } else {
    
                    }
                }
                $content['orders'] = $resultado;
                //$content['orders'] = $this->getOrderStatus('PEDIDO','PARCIAL');
                $content['result'] = true;
            } elseif (in_array(21,$roles)) {
                $content['orders'] = $this->getOrderStatus('COTIZADO',NULL);;
                $content['result'] = true;
            } elseif (in_array(22,$roles)) {
                $content['orders'] = $this->getOrderStatus('NUEVO',NULL);
                $content['result'] = true;
            }else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
            $this->response->setJsonContent($content);
            $this->response->send();
        }
    }

    public function getOrder ($id)
    {
        $content = $this->content;
        $order = null;
        $ready = true;
        if (is_numeric($id)) {
            if ($this->userHasPermissionToGetOrderOrSendPdfToSupplier()) {
                $sql = "SELECT case when ph.created is not null then TO_CHAR(ph.created, 'dd/mm/yyyy') end as order_date,o.id, o.serial, o.status, o.supplier_id, o.producer, TO_CHAR(o.requested_date :: DATE, 'dd/mm/yyyy') AS requested_date, o.proform, TO_CHAR(o.embargo_date :: DATE, 'dd/mm/yyyy') AS embargo_date, o.petition_number, o.shipping_price, o.tax_price, o.duty_price, s.name AS po, o.acc_currency_type_id, ct.name as currency_name,o.storage_id as storage,ws.name as name_storage,wbo.id as id_branch, wbo.name as name_branch,o.reference, TO_CHAR(o.invoice_date, 'dd/mm/yyyy') as date_invoicee
                FROM pur_orders AS o
                LEFT JOIN pur_order_history as ph
                ON ph.order_id = o.id AND ph.status = 'PEDIDO'
                INNER JOIN pur_suppliers AS s
                ON o.supplier_id = s.id
                LEFT JOIN acc_currency_types AS ct
                ON ct.id = o.acc_currency_type_id
                left join wms_storages as ws on ws.id = o.storage_id
                left join wms_branch_offices as wbo on wbo.id = ws.branch_office_id
                        WHERE o.id = $id;";
                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $order = $data->fetch();
                $sql = "SELECT *, TO_CHAR(pur_order_history.created, 'dd/mm/yyyy HH24:MI:SS') as created,TO_CHAR(pur_order_history.created, 'dd/mm/yyyy') as order_date
                        FROM pur_order_history
                        WHERE order_id = $id
                        group by status,id
                        ORDER BY 
                        CASE status WHEN 'NUEVO' then 1 WHEN 'COTIZADO' then 2 WHEN 'PEDIDO' then 3 
                        WHEN 'PARCIAL' then 4 WHEN 'RECIBIDO' then 5 
                        WHEN 'CANCELADO' then 6 else 7 END";
                $data = $this->db->query($sql);
                $history = $data->fetchAll();
                $sql = "SELECT pur_shipment_details.product_id, COALESCE(sum(pur_shipment_details.qty), 0) as qty, pur_order_details.qty as total 
                        from pur_shipment_details 
                        join pur_order_details on pur_order_details.product_id =pur_shipment_details.product_id
                        where pur_order_details.po_id = {$id}
                        group by pur_shipment_details.product_id, pur_order_details.qty";
                $data = $this->db->query($sql);
                $isReady = $data->fetchAll();
                foreach ($isReady as $key => $product) {
                    if ($product['qty'] < $product['total']) {
                        $ready = false;
                    }
                }
            } else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }
        $content['order'] = $order;
        $content['history'] = $history;
        $content['ready'] = $ready;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id, serial FROM pur_orders ORDER BY serial ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['serial']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }
    public function getOptionsWithSupplier () {
        $sql = "SELECT p.id, CONCAT(p.serial,' - ',TO_CHAR(p.requested_date :: DATE, 'dd/mm/yyyy'),' - ',substring(s.name,0,20)) AS serial
        FROM pur_orders as p
        INNER JOIN pur_suppliers as s
        ON p.supplier_id = s.id
        ORDER BY serial ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['serial'],
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function create ()
    {
        date_default_timezone_set('America/Mexico_City');
        try {
            if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                /*echo("<pre>");
                print_r($request);
                exit();*/

                $sql = "SELECT serial FROM pur_orders ORDER BY serial DESC LIMIT 1;";
                $lastSerial = $this->db->query($sql)->fetch()['serial'];

                $supplier = Suppliers::findFirst('id = '.$request['supplier_id'].' AND active');

                if ($supplier) {
                    $order = new PurchaseOrders();
                    $order->setTransaction($tx);
                    $order->status = 'NUEVO';
                    $order->supplier_id = $request['supplier_id'];
                    $order->requested_date = $request['requested_date'];
                    $order->storage_id = $request['storage']['value'];
                    if (isset($request['reference'])) {
                            $order->reference = empty($request['reference']) ? null : $request['reference'];
                        }
                    // $order->requested_date = $request['requested_date'];
                    
                    if ($lastSerial < (date("Y").'0001')) {
                        $order->serial = (date("Y").'0001');
                    } else {
                        $order->serial = ++$lastSerial;
                    }

                    if ($order->create()) {
                        $poh = new PurchaseOrderHistory();
                        $poh->order_id = $order->id;
                        $poh->status = 'NUEVO';
                        $poh->created = date('Y-m-d H:i:s');
                        if ($poh->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La orden de compra ha sido creada con el folio: '.$order->serial);
                            $this->content['order'] = $order;
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de compra.');
                        }
                    } else {
                        $this->content['error'] = Helpers::getErrors($order);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de compra.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el proveedor.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function updateDateInvoices ($id) {

        try {
            if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                $tx = $this->transactions->get();

                $order = PurchaseOrders::findFirst($id);
                $request = $this->request->getPut();
                if ($order) {
                    $order->setTransaction($tx);
                    $order->invoice_date = $request['date_invoicee'] == null ? null : $request['date_invoicee'];
                    $order->shipping_price = $request['shipping_price'] == null ? null : $request['shipping_price'];
                    $order->reference = $request['reference'] == null ? null : $request['reference'];
                    if ($order->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La orden de compra ha sido modificada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($order);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de compra.');
                        $tx->rollback();
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
    public function update ($id)
    {
        date_default_timezone_set('America/Mexico_City');
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                    $tx = $this->transactions->get();

                    $order = PurchaseOrders::findFirst($id);
                    $request = $this->request->getPut();
                    if ($order) {
                        $order->setTransaction($tx);
                        if (isset($request['supplier_id']) && is_numeric($request['supplier_id'])) {
                            $supplier = Suppliers::findFirst('id = '.$request['supplier_id'].' AND active');
                            if ($supplier) {
                                $order->supplier_id = $request['supplier_id'];
                            }
                        }
                        if (isset($request['producer']) && !is_null($request['producer'])) {
                            $order->producer = strtoupper($request['producer']);
                        }
                        if (isset($request['requested_date'])) {
                            $order->requested_date = $request['requested_date'];
                        }
                        if (isset($request['proform'])) {
                            $order->proform = empty($request['proform']) ? null : $request['proform'];
                        }
                        if (isset($request['embargo_date'])) {
                            $order->embargo_date = empty($request['embargo_date']) ? null : $request['embargo_date'];
                        }
                        if (isset($request['petition_number'])) {
                            $order->petition_number = empty($request['petition_number']) ? null : $request['petition_number'];
                        }
                        if (isset($request['shipping_price'])) {
                            $order->shipping_price = empty($request['shipping_price']) ? 0 : $request['shipping_price'];
                        }
                        if (isset($request['tax_price'])) {
                            $order->tax_price = empty($request['tax_price']) ? null : $request['tax_price'];
                        }
                        if (isset($request['duty_price'])) {
                            $order->duty_price = empty($request['duty_price']) ? null : $request['duty_price'];
                        }
                        if (isset($request['acc_currency_type_id'])) {
                            $order->acc_currency_type_id = empty($request['acc_currency_type_id']) ? null : $request['acc_currency_type_id'];
                        }
                        if (isset($request['reference'])) {
                            $order->reference = empty($request['reference']) ? null : $request['reference'];
                        }
                        if (isset($request['date_invoicee'])) {
                            $order->invoice_date = empty($request['date_invoicee']) ? null : $request['date_invoicee'];
                        }

                        if ($order->update()) {
                            // $history = PurchaseOrderHistory::find("order_id = $id");
                            // $history->setTransaction($tx);
                            if (isset($request['order_date'])) {
                                //foreach ($history as $value) {
                                    $tx = $this->transactions->get();
                                    $bD = PurchaseOrderHistory::findFirst("order_id = $id AND status = 'PEDIDO'");
                                    $bD->setTransaction($tx);
                                    $bD->created = $request['order_date'];
                                    if ($bD->update()) {}
                                //}
                            }
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La orden de compra ha sido modificada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de compra.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function request ($id)
    {
        date_default_timezone_set('America/Mexico_City');
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                    $tx = $this->transactions->get();
        
                    $order = PurchaseOrders::findFirst($id);
        
                    if ($order) {
                        if ($order->status == 'NUEVO') {
                            $details = PurchaseOrderDetails::find("po_id = $order->id");
                            if (count($details) > 0) {
                                $inactiveProducts = [];
                                foreach ($details as $detail) {
                                    $product = Products::findFirst($detail->product_id);
                                    if (!$product->active) {
                                        array_push($inactiveProducts, $product->name);
                                    }
                                }
                                if (count($inactiveProducts) == 0) {
                                    $this->content['message'] = Message::success('La orden de compra ha sido solicitada exitosamente.');
                                    $order->setTransaction($tx);
                                    $order->status = 'SOLICITADO';
                                    if ($order->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de compra ha sido solicitada exitosamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($order);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar solicitar la orden de compra.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No hay detalles en la orden por lo que no se puede solicitar.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El estatus de la orden de compra no es NUEVO por lo que no se puede solicitar.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function approve ($id)
    {
        date_default_timezone_set('America/Mexico_City');
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToApprove()) {
                    $tx = $this->transactions->get();
        
                    $order = PurchaseOrders::findFirst($id);
        
                    if ($order) {
                        if ($order->status == 'SOLICITADO') {
                            $details = PurchaseOrderDetails::find("po_id = $order->id");
                            if (count($details) > 0) {
                                $inactiveProducts = [];
                                foreach ($details as $detail) {
                                    $product = Products::findFirst($detail->product_id);
                                    if (!$product->active) {
                                        array_push($inactiveProducts, $product->name);
                                    }
                                }
                                if (count($inactiveProducts) == 0) {
                                    $order->setTransaction($tx);
                                    $order->status = 'APROBADO';
                                    if ($order->update()) {
                                        if ($tx->commit()) {
                                            $msg = 'La orden de compra ha sido aprobada exitosamente';
                                            $actions = Actions::findFirst(1);
                                            if ($actions->host && $actions->port && $actions->username && $actions->password) {
                                                $supplier = Suppliers::findFirst($order->supplier_id);
                                                if ($supplier->email) {
                                                    $htmlBody = '
                                                    <!DOCTYPE html>
                                                        <html>
                                                        <head>
                                                            <style>
                                                            #logo-container {
                                                                text-align: center;
                                                            }

                                                            #logo {
                                                                max-width: 300px;
                                                            }

                                                            p {
                                                                text-align: justify;
                                                                color: #00295E;
                                                                font-family: verdana;
                                                                font-size: 15px;
                                                            }
                                                            </style>
                                                        </head>
                                                        <body>
                                                            <div id="logo-container">
                                                                <img id="logo" src="http://api.tf.beta.antfarm.mx/assets/images/logo_name.png" alt="Technofibers">
                                                            </div>
                                                            <p>
                                                                Estimado proveedor <strong>'.$supplier->tradename.'</strong>.
                                                                <br>
                                                                <br>
                                                                Adjunto encontrará la O.C. #'.$order->serial.', contenido en la misma encontrará las especificaciones.
                                                                <br>
                                                                <br>
                                                                Es importante recordar que las condiciones para recepción son las siguientes:
                                                                <br>
                                                                <br>
                                                                Jumbo: Nuevo
                                                                <br>
                                                                Medidas: 1.80 alto × 1.00 ancho
                                                                <br>
                                                                Tarima: Nuevo
                                                                <br>
                                                                Medidas: 1.20 alto × 1.00 ancho
                                                                <br>
                                                                Horario: Lunes 7:30 a 1:00 PM
                                                                <br>
                                                                <br>
                                                                Se requieren los siguientes documentos: Factura o remisión, orden de compra y lista de empaque.
                                                                <br>
                                                                En caso de no traer la documetación completa, no se recibirá el material.
                                                            </p>
                                                        </body>
                                                    </html>';
                                                    $mailer = new Mailer();
                                                    $mailer->htmlBody = $htmlBody;
                                                    $mailer->attachedFile = $this->savePdf($id);
                                                    $mailer->host = $actions->host;
                                                    $mailer->port = $actions->port;
                                                    $mailer->username = $actions->username;
                                                    $mailer->password = $actions->password;
                                                    $mailer->subject = "Technofibers O.C. #$order->serial";
                                                    $mailer->from = $actions->username;
                                                    $mailer->to = $supplier->email;
                                                    $result_message = null;
                                                    try {
                                                        $result_message = $mailer->sendEmail();
                                                    } catch (Throwable $e) {
                                                        $result_message = (object) array(
                                                            'status' => false,
                                                            'message' => $e->getMessage()
                                                        );
                                                    }
                                                    $msg .= '; '.$result_message->message;
                                                } else {
                                                    $msg .= '; No se ha enviado el correo debido a que el proveedor no tiene correo registrado.';
                                                }
                                            } else {
                                                $msg .= '; No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
                                            }
                                            $this->content['result'] = true;
                                            $this->content['message'] = Message::success($msg);
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($order);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar aprobar la orden de compra.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No hay detalles en la orden por lo que no se puede aprobar.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El estatus de la orden de compra no es SOLICITADO por lo que no se puede aprobar.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function close ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToCloseOrCancel()) {
                    $tx = $this->transactions->get();
        
                    $order = PurchaseOrders::findFirst($id);
        
                    if ($order) {
                        if ($order->status == 'APROBADO') {
                            $newShipments = Shipments::find("order_id = $id AND (status = 'ANALIZADO' OR status = 'NUEVO')");
                            if (count($newShipments) > 0) {
                                $this->content['message'] = Message::error('Existen recepciones sin recibir en la orden de compra.');
                            } else {
                                $shipmentsWithoutInvoice = Shipments::find("order_id = $id AND (invoice IS NULL OR invoice = '')");
                                if (count($shipmentsWithoutInvoice) > 0) {
                                    $this->content['message'] = Message::error('Existen recepciones sin factura subida.');
                                } else {
                                    $order->setTransaction($tx);
                                    $order->status = 'CERRADO';
                                    if ($order->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de compra ha sido cerrada exitosamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($order);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar cerrar la orden de compra.');
                                    }
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('El estatus de la orden de compra no es APROBADO por lo que no se puede cerrar.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function cancel ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToCloseOrCancel()) {
                    $tx = $this->transactions->get();
        
                    $order = PurchaseOrders::findFirst($id);
        
                    if ($order) {
                        if ($order->status != 'CERRADO') {
                            $sql = "SELECT id
                                    FROM pur_shipments
                                    WHERE order_id = $id
                                    AND status = 'RECIBIDO';";
                            $data = $this->db->query($sql);
                            //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                            $shipmentsReceived = $data->fetchAll();
                            if (count($shipmentsReceived) == 0) {
                                $order->setTransaction($tx);
                                $order->status = 'CANCELADO';
                                if ($order->update()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('La orden de compra ha sido cancelada exitosamente.');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar la orden de compra.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se puede cancelar la orden de compra debido a que contiene recepciones recibidas.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede cancelar una orden de compra cerrada.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function open ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermissionToCloseOrCancel()) {
                    $tx = $this->transactions->get();
        
                    $order = PurchaseOrders::findFirst($id);
        
                    if ($order) {
                        if ($order->status == 'CANCELADO') {
                            $sql = "SELECT status
                                    FROM pur_order_history
                                    WHERE order_id = $id
                                    order by created desc;";
                            $data = $this->db->query($sql);
                            //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                            $oldStatus = $data->fetch();
                            if ($oldStatus) {
                                $order->setTransaction($tx);
                                $order->status = $oldStatus['status'];
                                if ($order->update()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('La orden de compra ha sido restaurada exitosamente.');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar la orden de compra.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se han encontrado registros de viejos status.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede restaurar una orden de compra cerrada.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
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
                if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                    $tx = $this->transactions->get();
                    $order = PurchaseOrders::findFirst($id);
                    if ($order) {
                        $order->setTransaction($tx);
                        if ($order->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La orden de compra ha sido eliminada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la orden de compra.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('La orden de compra no existe.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function generatePdf($id)
    {
        if (is_numeric($id)) {
            $sql = "SELECT o.id, o.serial, TO_CHAR(o.requested_date, 'yyyy/mm/dd') AS requested_date, s.name AS supplier_name, s.tradename AS supplier_tradename, INITCAP (CONCAT(s.street, ' ', s.outdoor_number,' ',s.suburb)) as street, s.outdoor_number, s.indoor_number, s.suburb, s.municipality, s.state, u.nickname, s.term, s.rfc,s.contact_phone,s.email,o.reference,
                    str.zip_code as storagezip, str.street as storagestreet, str.suburb as storagesuburb, str.city as storagecity
                    FROM pur_orders AS o
                    INNER JOIN pur_suppliers AS s
                    ON s.id = o.supplier_id
                    INNER JOIN sys_users AS u
                    ON u.id = o.created_by
                    INNER JOIN wms_storages as str on str.id = o.storage_id
                    WHERE (o.status = 'RECIBIDO' or o.status ='PARCIAL' or o.status = 'PEDIDO')
                    AND o.id = $id;";
            $order = $this->db->query($sql)->fetch();
            $sql = "SELECT case when od.vat_rate is null then 0 else od.vat_rate end as vat_rate,case when od.vat is null then 0 else od.vat end as vat,od.product_id, od.qty, od.price AS unit_price, (od.qty * od.price) AS price, p.name AS product, CONCAT(c.code,'-',l.code,'-',p.code) AS product_code, od.quality, od.color, AVG(sam.pvc) AS pvc, AVG(sam.dirty) AS dirty, AVG(sam.metals) AS metals, AVG(sam.recicled) AS recicled, AVG(sam.humidity) AS humidity, AVG(sam.sifting) AS sifting, od.observation as observations, u.code as unidad,p.description
                    FROM pur_order_details AS od
                    INNER JOIN wms_products AS p
                    ON p.id = od.product_id
                    LEFT JOIN wms_units AS u
                    ON u.id = p.unit_id
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    LEFT JOIN pur_shipments AS ship
                    ON ship.order_id = od.po_id
                    LEFT JOIN pur_samplings AS sam
                    ON sam.shipment_id = ship.id AND od.product_id = sam.product_id
                    WHERE od.po_id = $id
                    GROUP BY od.product_id, od.qty,od.price, p.name, c.code, l.code, p.code, od.quality, od.color,od.vat,od.vat_rate, od.observation, u.code,p.description;";
            $details = $this->db->query($sql)->fetchAll();
           
           $sqlbo="SELECT po.id, bo.id as sucursal_id from pur_orders as po 
                    left join wms_storages as s on po.storage_id = s.id
                    left join wms_branch_offices as bo on bo.id = s.branch_office_id
                    where po.id = $id";
            $bo = $this->db->query($sqlbo)->fetch();
            
            $supplierAddress = '';

            if (isset($order['street']) && strlen($order['street']) > 0) {
                $supplierAddress = $order['street'];
                if (isset($order['outdoor_number']) && strlen($order['outdoor_number']) > 0) {
                    $supplierAddress .= ' '.$order['outdoor_number'];
                }
                if (isset($order['indoor_number']) && strlen($order['indoor_number']) > 0) {
                    $supplierAddress .= ' '.$order['indoor_number'];
                }
            }

            if (isset($order['suburb']) && strlen($order['suburb']) > 0) {
                if (strlen($supplierAddress) > 0) {
                    $supplierAddress .= ', ';
                }
                $supplierAddress .= $order['suburb'];
            }

            if (isset($order['municipality']) && strlen($order['municipality']) > 0) {
                if (strlen($supplierAddress) > 0) {
                    $supplierAddress .= ', ';
                }
                $supplierAddress .= $order['municipality'];
            }

            if (isset($order['state']) && strlen($order['state']) > 0) {
                if (strlen($supplierAddress) > 0) {
                    $supplierAddress .= ', ';
                }
                $supplierAddress .= $order['state'];
            }
            $company = '';
            $companyAddress = '';
            $currentDate = date('Y').'/'.date('m').'/'.date('d');
            $currentTime = date('H').':'.date('i');
            $pvcAverageA = 0;
            $pvcAverageB = 0;
            $pvcAverageC = 0;
            $dirtyAverageA = 0;
            $dirtyAverageB = 0;
            $dirtyAverageC = 0;
            $metalsAverageA = 0;
            $metalsAverageB = 0;
            $metalsAverageC = 0;
            $recicledAverageA = 0;
            $recicledAverageB = 0;
            $recicledAverageC = 0;
            $humidityAverageA = 0;
            $humidityAverageB = 0;
            $humidityAverageC = 0;
            $siftingAverageA = 0;
            $siftingAverageB = 0;
            $siftingAverageC = 0;
            $serial = ['serial'=>$order['serial'],'reference'=>$order['reference']];
            $rfc = $order['rfc'] ? $order['rfc'] : "";
            $supplier = $order['supplier_name'] ? $order['supplier_name'] : "";
            $domicilio = $order['street'] ? $order['street'] : "";
            $cellphone = $order['contact_phone'] ? $order['contact_phone'] : "";
            $email = $order['email'] ? $order['email'] : "";
            //$bussinessName = $order['supplier_name'];
            $pdf = new PDFPurchaseOrder('P','mm','Letter');
            $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(false);
            $pdf->Encabezado($serial);

            $pdf->SetFillColor(128,179,240);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetDrawColor(21, 18, 46);
            $pdf->Ln();
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('C'));
            $pdf->SetHeight(8);
            $pdf->SetFill(array(true));
            $pdf->SetDrawEdge(true);
            $pdf->SetTextColor(255, 255, 255);
            if($bo['sucursal_id']==9){
            $pdf->Row(array('COMPRADOR: '));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->MultiCell(90,4, utf8_decode("EMPRESA SA DE CV.\n20 DE NOVIEMBRE #515 OTE.\nZONA CENTRO.\nC.P. 34000.\nRFC BRB780222GD3.\nDURANGO, DGO.\nCORREO compras@empresa.com\n\n\n"),1, 'L','','');
            } elseif($bo['sucursal_id']==12){
            $pdf->Row(array('COMPRADOR: '));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->MultiCell(90,4, utf8_decode("LOPEZ DE LARA TINAJERO GUILLERMO.\nFRANCISCA ESCARZAGA # 500.\nCOL. SANTA FE.\nC.P. 34240.\nRFC LOTG541005G9A.\nDURANGO, DGO.\nCORREO correo@empresa.com\nTELEFONO: 618 810 2521\n\n"),1, 'L','','');
            } else if ($bo['sucursal_id'] == 14) {
                // REBASA RODAMIENTOS Y MANGUERAS - RODAMIENTOS
                $pdf->Row(array('COMPRADOR: '));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->MultiCell(90,4, utf8_decode("EMPRESA SA DE CV\nALUMINIO SN.\nFIDEICOMISO CIUDAD INDUSTRIAL.\nC.P. 34229.\nRFC RRM010601UV1.\nDURANGO, DGO.\nCORREO correo@empresa.mx\nTELEFONO: 618 814 7148\n\n"),1, 'L','','');
            }else if ($bo['sucursal_id'] == 13) {
                // EMPRESA SA DE CV
                $pdf->Row(array('COMPRADOR: '));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->MultiCell(90,4, utf8_decode("EMPRESA SA DE CV\nBLVD. GUADIANA # 410.\nFRACC. LA ESMERALDA.\nC.P. 34139.\nRFC RRM010601UV1.\nDURANGO, DGO.\nCORREO correo@empresa.mx\nTELEFONO: 618 130 3555\n\n"),1, 'L','','');
            }

            $pdf->SetFillColor(128,179,240);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetDrawColor(21, 18, 46);
            $pdf->Ln();
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('C'));
            $pdf->SetHeight(8);
            $pdf->SetFill(array(true));
            $pdf->SetDrawEdge(true);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Row(array(utf8_decode('DIRECCIÓN DE RECEPCIÓN DE MATERIAL BODEGA: ')));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->MultiCell(90,4, utf8_decode($order['storagestreet'].".\n".$order['storagesuburb']."\nC.P.".$order['storagezip'].".\n".$order['storagecity'].".\n"),1, 'L','','');
            
            $pdf->setXY(120,32);
            $pdf->SetFillColor(128,179,240);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetDrawColor(21, 18, 46);
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('C'));
            $pdf->SetHeight(8);
            $pdf->SetFill(array(true));
            $pdf->SetDrawEdge(true);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Row(array('PROVEEDOR: '));
            $pdf->SetWidths(array(90));
            $pdf->SetAligns(array('L'));
            $pdf->SetFill(array(false));
            $pdf->SetTextColor(0);
            $pdf->SetDrawEdge(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetHeight(12);
            $pdf->setXY(120,40);
            $pdf->Cell(90, 5, utf8_decode("EMPRESA: ".utf8_decode($supplier)),1, 'L','','');
            /* $pdf->setXY(120,45);
            $pdf->Cell(90, 5, utf8_decode("RAZÓN SOCIAL: ".$bussinessName),1, 'L','',''); */
            $pdf->setXY(120,45);
            $pdf->Cell(90, 5, utf8_decode("RFC: ".$rfc),1, 'L','','');
            $pdf->setXY(120,50);
            $pdf->SetFillColor(255);
            $pdf->Cell(90, 5, utf8_decode("DOMICILIO: ".$domicilio),1, 0,'L',0);
            $pdf->setXY(120,55);
            $pdf->Cell(90, 5, utf8_decode("TELÉFONO: ".$cellphone),1, 'L','','');
            $pdf->setXY(120,60);
            $pdf->Cell(90, 5, utf8_decode("EMAIL: ".$email),1, 'L','','');
            $pdf->ln(40);

            $initialY = $pdf->GetY();
            $actualY = $pdf->GetY();
            for ($i=$initialY; $i < $actualY; $i+=6) {
                $pdf->Line($pdf->GetX(), $i+5, $pdf->GetX()+95, $i+5);
                $pdf->Line($pdf->GetX()+100, $i+5, $pdf->GetX()+195, $i+5);
            }

            $pdf->Ln();
            $pdf->Ln();

            $pdf->SetFillColor(128,179,240);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetWidths(array( 43,44,30, 25, 15, 20, 20));
            $pdf->SetAligns(array('C', 'C','C', 'C', 'C','C', 'C', 'C'));
            $pdf->SetFill(array(true, true, true,true, true, true, true, true));
            $pdf->SetHeight(7);
            $pdf->SetDrawEdge(false);
            $pdf->SetAutomaticFontColor(false);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetTextColor(255);
            $pdf->Row(array('PRODUCTO',utf8_decode('DESCRIPCIÓN'), 'COMENTARIOS', 'CANTIDAD', 'UNIDAD', 'UNITARIO', 'IMPORTE'));
            $pdf->SetHeight(4);
            $pdf->SetHeight(6);
            $pdf->SetFillColor(232, 232, 234);
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetTextColor(0);
            $pdf->SetAligns(array( 'L','L','L', 'C', 'L', 'R', 'R'));
            $fill = true;
            $totalPrice = 0;
            $qtyA = 0;
            $qtyB = 0;
            $qtyC = 0;
            $totalTax = 0;
            foreach ($details as $detail) {
                //$detail['product_code']
                $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill , $fill));
                $pdf->Row(array(utf8_decode($detail['product']),utf8_decode($detail['description']),utf8_decode($detail['observations']),number_format($detail['qty'], 2, '.', ','), utf8_decode($detail['unidad']),  '$'.number_format($detail['unit_price'], 2, '.', ','), '$'.number_format($detail['price'], 2, '.', ',')));
                $fill = !$fill;
                $totalPrice += $detail['price'];
                $totalTax += $detail['vat'];
            }
            $pdf->Ln();
            $pdf->SetWidths(array(170, 25));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(false);
            $pdf->SetFill(array(false, true));
            // $totalTax = $totalPrice * 0.16;
            $finalPrice = $totalPrice + $totalTax;
            $pdf->SetAutomaticFontColor(false);
            $pdf->SetFillColor(128,179,240);
            $pdf->Row(array('SUBTOTAL', '$'.number_format($totalPrice, 2, '.', ',')));
            $pdf->Row(array('IVA', '$'.number_format($totalTax, 2, '.', ',')));
            $pdf->Row(array('TOTAL', '$'.number_format($finalPrice, 2, '.', ',')));

            $pdf->Ln();

            $pdf->SetTitle('Orden de compra '.$order['serial'],true);
            return $pdf;
        }
        return null;
    }

    public function getPdf ($id)
    {
        // $order->status == 'APROBADO' || $order->status == 'CERRADO'
        if (is_numeric($id)) {
            $order = PurchaseOrders::findFirst($id);
            if ($order && ($order->status == 'PARCIAL' || $order->status == 'RECIBIDO' || $order->status == 'PEDIDO')) {
                $pdf = $this->generatePdf($id);
                if (!is_null($pdf)) {
                    $pdf->Output('I', 'Orden de compra '.$order->serial.'.pdf', true);
                    $response = new Phalcon\Http\Response();
                    $response->setHeader("Content-Type", "application/pdf");
                    $response->setHeader("Content-Disposition", 'inline; filename="Orden de compra '.$order->serial.'.pdf"');
                    return $response;
                }
            }
        }
        return null;
    }

    private function savePdf ($id)
    {
        if (is_numeric($id)) {
            $order = PurchaseOrders::findFirst($id);
            if ($order && ($order->status == 'PEDIDO')) {
                $pdf = $this->generatePdf($id);
                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/purchase-orders/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Orden de compra $order->serial.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
            }
        }
        return null;
    }

    public function sendPdfToProvider ($id)
    {
        $content = $this->content;
        $order = null;
        if (is_numeric($id)) {
            if ($this->userHasPermissionToGetOrderOrSendPdfToSupplier()) {
                $order = PurchaseOrders::findFirst($id);
                if ($order && $order->id) {
                    $supplier = Suppliers::findFirst($order->supplier_id);
                    if ($supplier && $supplier->id) {
                        if ($supplier->email) {
                            $actions = Actions::findFirst(2);
                            if ($actions->host && $actions->port && $actions->username && $actions->password) {
                                $htmlBody = '
                                <!DOCTYPE html>
                                    <html>
                                    <head>
                                        <style>
                                        #logo-container {
                                            text-align: center;
                                        }

                                        #logo {
                                            max-width: 300px;
                                        }

                                        p {
                                            text-align: justify;
                                            color: #00295E;
                                            font-family: verdana;
                                            font-size: 15px;
                                        }
                                        </style>
                                    </head>
                                    <body>
                                        <div id="logo-container">
                                            <img id="logo" src="http://api.tf.antfarm.mx/assets/images/logo_name.png" alt="Technofibers">
                                        </div>
                                        <p>
                                            Estimado proveedor <strong>'.$supplier->tradename.'</strong>.
                                            <br>
                                            <br>
                                            Adjunto encontrará la O.C. #'.$order->serial.', contenido en la misma encontrará la fecha de entrega requerida y las especificaciones.
                                            <br>
                                            <br>
                                            Es importante recordar que las condiciones para recepción son las siguientes:
                                            <br>
                                            <br>
                                            Jumbo: Nuevo
                                            <br>
                                            Medidas: 1.80 alto × 1.00 ancho
                                            <br>
                                            Tarima: Nuevo
                                            <br>
                                            Medidas: 1.20 alto × 1.00 ancho
                                            <br>
                                            Horario: Lunes 7:30 a 1:00 PM
                                            <br>
                                            <br>
                                            Se requieren los siguientes documentos: Factura o remisión, orden de compra y lista de empaque.
                                            <br>
                                            En caso de no traer la documetación completa, no se recibirá el material.
                                        </p>
                                    </body>
                                </html>';

                                $transport = (new Swift_SmtpTransport($actions->host, $actions->port))
                                ->setUsername($actions->username)
                                ->setPassword($actions->password);
                                // Create the Mailer using your created Transport
                                $mailer = new Swift_Mailer($transport);
                                // Create a message
                                $message = (new Swift_Message('Estimado proveedor.'))
                                ->setFrom([$actions->username => 'Support'])
                                ->setTo([$customer->email])
                                ->setBody($htmlBody,'text/html')
                                ->attach(Swift_Attachment::fromPath($fileName.$order->serial.'.pdf')->setFilename('OC.'.$order->serial.'.pdf'));
                                // Send the message
                                $mailer->send($message);

                                // $mailer = new Mailer();
                                // $mailer->htmlBody = $htmlBody;
                                // $mailer->attachedFile = $this->savePdf($id);
                                // $mailer->host = $actions->host;
                                // $mailer->encryption = $actions->encryption;
                                // $mailer->port = $actions->port;
                                // $mailer->username = $actions->username;
                                // $mailer->password = $actions->password;
                                // $mailer->subject = "Technofibers O.C. #$order->serial";
                                // $mailer->from = $actions->username;
                                // $mailer->to = $supplier->email;
                                // $mailer->Cc = ['edgart@tf.mx', 'daniel@tf.mx'];
                                // $result_message = null;
                                // try {
                                //     $result_message = $mailer->sendEmail();
                                // } catch (Throwable $e) {
                                //     $result_message = (object) array(
                                //         'status' => false,
                                //         'message' => $e->getMessage()
                                //     );
                                // }
                                // $content['result'] = $result_message->status;
                                // $content['message'] = $result_message->message;
                            } else {
                                $content['result'] = false;
                                $content['message'] = 'No se pueden enviar correos debido a que faltan datos de la cuenta de correo';
                            }
                        } else {
                            $content['result'] = false;
                            $content['message'] = 'No se pueden enviar correos debido a que el proveedor no tiene correo registrado';
                        }
                    }
                }
            } else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function sendEmailToSupplier($order,$supplierId) {
        $msg = null;
        $actions = Actions::findFirst(2);
        if ($actions->host && $actions->port && $actions->username && $actions->password) {
            $supplier = Suppliers::findFirst($supplierId);

            /*print_r($order);
            exit();*/
            // Buscar correo 
            if ($supplier->email) {
                $htmlBody = '
                <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                        #logo-container {
                            text-align: center;
                        }

                        #logo {
                            max-width: 300px;
                        }

                        p {
                            text-align: justify;
                            color: #00295E;
                            font-family: verdana;
                            font-size: 15px;
                        }
                        </style>
                    </head>
                    <body>
                        <div id="logo-container">
                            <img id="logo" src="http://alpez.beta.wasp.mx/img/logo.f0ffa143.png" alt="Alpez">
                        </div>
                        <p>
                            Estimado proveedor <strong>'.$supplier->tradename.'</strong>.
                            <br>
                            <br>
                            Adjunto encontrará la O.C. #'.$order->serial.', contenido en la misma encontrará la fecha de entrega requerida y las especificaciones.
                            <br>
                            <br>
                            Es importante recordar que las condiciones para recepción son las siguientes:
                            <br>
                            <br>
                            Se requieren los siguientes documentos: Factura o remisión, orden de compra y lista de empaque.
                            <br>
                            En caso de no traer la documetación completa, no se recibirá el material.
                        </p>
                    </body>
                </html>';
                $this->savePdf($order->id);
                $fileName = __DIR__.'./../../public/assets/purchase-orders/';
                $transport = (new Swift_SmtpTransport($actions->host, $actions->port, $actions->encryption))
                ->setUsername($actions->username)
                ->setPassword($actions->password);
                // Create the Mailer using your created Transport
                $mailer = new Swift_Mailer($transport);
                // Create a message
                $message = (new Swift_Message('Estimado proveedor.'))
                ->setFrom([$actions->username => 'REBASA'])
                ->setTo([$supplier->email])
                ->setBody($htmlBody,'text/html')
                ->attach(Swift_Attachment::fromPath($fileName."Orden de compra ".$order->serial.'.pdf')->setFilename('OC.'.$order->serial.'.pdf'));
                // Send the message
                $mailer->send($message);
                $msg.= "Correo enviado correctamente al Proveedor";
            } else {
                $msg .= '; No se ha enviado el correo debido a que el proveedor no tiene correo registrado.';
            }
        } else {
            $msg .= '; No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
        }
        return $msg;
    }

    public function sendEmail ($id) {
        $order = PurchaseOrders::findFirst($id);
        if ($order) {
            $supplierId = intval($order->supplier_id);
            if ($supplierId != null) {
                // We'll send the email to store people
                $msg = $this->sendEmailToSupplier($order,$supplierId);
                $this->content['result'] = true;
                $this->content['message'] = Message::error($msg);
            }
        }
        $this->response->setJsonContent($this->content);

    }
    public function changeStatus ()
    {
        $request = $this->request->getPut();
        if (is_numeric($request['order_id'])) {
            try {
                if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                    $tx = $this->transactions->get();

                    $order = PurchaseOrders::findFirst($request['order_id']);
                    if ($order) {
                        $supplierId = intval($order->supplier_id);
                        $order->setTransaction($tx);
                        $order->status = $request['status'];
                        // if (isset($request['status']) && $request['status'] == 'PEDIDO') {
                        //     // We'll send the email to store people
                        //     $msg = $this->sendEmailToSupplier($order,$supplierId);
                        //     $this->content['message_email'] = Message::error($msg);
                        // }
                        if ($order->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('Se ha cambiado el status a '.$request['status'].'.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de compra.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function autorizar ()
    {
        $request = $this->request->getPut();
        if (is_numeric($request['id'])) {
            try {
                if ($this->userHasPermissionToCreateOrUpdateOrRequestOrDelete()) {
                    $tx = $this->transactions->get();

                    $order = PurchaseOrders::findFirst($request['id']);

                    if ($order) {
                        $order->setTransaction($tx);
                        $order->shipment_parcial_status = 'AUTORIZADO';

                        if ($order->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('Se ha cambiado el status a AUTORIZADO');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de compra.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    private function userHasPermissionToGetOrderOrSendPdfToSupplier ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 21 OR role_id = 22 OR role_id = 3 or role_id = 26 or role_id = 20 or role_id = 17 or role_id = 28)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermissionToCreateOrUpdateOrRequestOrDelete ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 21 OR role_id = 22 OR role_id = 3 or role_id = 2 or role_id = 26 or role_id = 20 or role_id = 17 or role_id = 28)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermissionToApprove ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 or role_id = 26 or role_id = 20 or role_id = 17 or role_id = 28)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermissionToCloseOrCancel ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 or role_id = 26 or role_id = 20 or role_id = 17 or role_id = 28)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermission () {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 or role_id = 26 or role_id = 20 or role_id = 17  or role_id = 28)
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

class PDFPurchaseOrder extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderSerial;
    var $drawEdge = true;
    var $fillCell = false;
    var $automaticFontColor = false;

    function Encabezado($serial)
    {
        //print_r($serial['serial']);
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,5,55,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-96),12);
        $this->SetFont('Nunito-Regular','',8);
        $this->SetTextColor(255);
        $this->SetFillColor(128,179,240); //135, 180, 223
        $this->Cell(90,6,utf8_decode('ORDEN DE COMPRA'),0,1,'C',1);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(($this->GetPageWidth()),17);
        $this->Cell(-86,6,utf8_decode('FOLIO:'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()),22);
        $this->Cell(-77,6,utf8_decode('REFERENCIA:'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()),27);
        $this->Cell(-84,6,utf8_decode('FECHA: '),0,0,'R');
        $this->SetFont('Nunito-Regular','',8);
        $this->SetXY(($this->GetPageWidth() - 78),17);
        $this->Cell(0,6,utf8_decode($serial['serial']),0,0,'L');
        $this->SetXY(($this->GetPageWidth() - 78),22);
        $this->Cell(0,6,utf8_decode($serial['reference']),0,0,'L');
        $this->SetXY(($this->GetPageWidth() - 78),27);
        $this->Cell(0,6,date('d').'/'.date('m').'/'.date('Y'),0,0,'L');


    }

    function Footer()
    {
        $this->SetFont('Nunito-Regular','',10);
        $this->SetTextColor(0);
        $this->SetY(261);
        $this->Cell(195, 6, "WWW.empresa.mx", 0, 0, 'C', false);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetY(274);
        $this->SetFillColor(135, 180, 223);
        $this->SetTextColor(0);
        $this->Rect(0,268,216,190,'F');
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

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function SetAutomaticFontColor($afc)
    {
        $this->automaticFontColor=$afc;
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
            if ($this->automaticFontColor) {
                if (isset($this->fill[$i]) && $this->fill[$i] == true) {
                    $this->SetFont('Arial','B',7);
                    $this->SetTextColor(255);
                } else {
                    $this->SetFont('Arial','',7);
                    $this->SetTextColor(0);
                }
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
class PDFPurchaseOrder2 extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderSerial;
    var $drawEdge = true;
    var $fillCell = false;
    var $automaticFontColor = false;
    function encabezadoCP()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,10,10,50,0,'PNG');
        }
        $this->SetXY(242, 10);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->Cell(10, 5, utf8_decode('FECHA: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 40, 12);
        $this->SetFont('Nunito-Regular', '', 18);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Nunito-Regular', '', 16);
        $this->SetXY(($this->GetPageWidth() / 2 - 50) , 18);
        $this->Cell(0, 0, 'REPORTE DE CUENTAS POR PAGAR');

        $header = array(
            utf8_decode('FOLIO'),
            'PROVEEDOR',
            'FACTURA',
            'ARRIBO',
            'VENCIMIENTO',
            'MONTO'
        );
        $this->SetXY(5, 40);
        //30,136,229
        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.3);
        $this->SetFont('', '', 9);
        // Header
        $x = 143;
        $i = 0;
        $w = array(30,100,30,50,30,30);
        foreach ($header as $col) {
            if ($i <= 11) {
                $this->Cell($w[$i], 5, $col, 1, 0, 'C', true);
            } else {
                $this->RotatedText(10 + $x, 56, $col, 85);
            }
            $x = $x + 5;
            $i++;
        }

    }

    function Encabezado($serial)
    {
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,20,5,65,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-96),12);
        $this->SetFont('Arial','B',12);
        $this->SetTextColor(0);
        $this->SetFillColor(235,217,178);
        $this->Cell(90,6,utf8_decode('ORDEN DE COMPRA'),0,1,'C',1);
        $this->SetXY(($this->GetPageWidth()),17);
        $this->Cell(-70,10,utf8_decode('FOLIO:'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()),22);
        $this->Cell(-69,10,utf8_decode('FECHA: '),0,0,'R');

        $this->SetFont('Arial','',10);
        $this->SetXY(($this->GetPageWidth() - 68),17);
        $this->Cell(0,10,utf8_decode($serial),0,0,'L');
        $this->SetXY(($this->GetPageWidth() - 68),22);
        $this->Cell(0,10,date('d').'/'.date('m').'/'.date('Y'),0,0,'L');


    }

    function Footer()
    {
        $this->SetFont('Nunito-Regular','',10);
        $this->SetTextColor(0);
        $this->SetY(257);
        $this->Cell(195, 6, "HICIMOS LAS COSAS SIMPLES", 0, 0, 'C', false);
        $this->SetY(261);
        $this->Cell(195, 6, "WWW.EMPRESA.COM", 0, 0, 'C', false);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetY(274);
        $this->SetFillColor(235,217,178);
        $this->SetTextColor(0);
        $this->Rect(0,268,216,190,'F');
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

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function SetAutomaticFontColor($afc)
    {
        $this->automaticFontColor=$afc;
    }

    function Row2($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $b = isset($this->border[$i]) ? $this->border[$i] : 0;
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], $b, $a,1);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
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
            if ($this->automaticFontColor) {
                if (isset($this->fill[$i]) && $this->fill[$i] == true) {
                    $this->SetFont('Arial','B',8);
                    $this->SetTextColor(255);
                } else {
                    $this->SetFont('Arial','',8);
                    $this->SetTextColor(0);
                }
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