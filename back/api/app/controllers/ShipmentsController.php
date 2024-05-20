<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;

class ShipmentsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShipments ()
    {
        if ($this->userHasPermission()) {
            $this->content['shipments'] = Shipments::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getShipmentsStatus () {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        $and = "";
        if ($validUser->role_id == 26) {
            $and = "INNER JOIN sys_supercluster on sys_supercluster.id = b.cluster_id and sys_supercluster.id = " . $validUser->cluster_id;
        } else {
            $where = $validUser->role_id == 1 ? '' : " and st.branch_office_id = $validUser->branch_office_id ";
        }
        if ($this->userHasPermission() ) {
            $ordenes = [];
            $sql = "SELECT o.id as order_id, o.serial as serial, s.name as supplier, b.name as branch_office, b.id as branch_office_id, st.name as storage, st.id as storage_id,o.status as status
            FROM pur_orders AS o
            inner JOIN pur_suppliers AS s
            ON  s.id = o.supplier_id
            inner JOIN wms_storages AS st
            ON  st.id = o.storage_id
            inner JOIN wms_branch_offices as b $and
            ON st.branch_office_id  = b.id
            WHERE (o.status = 'PEDIDO' OR o.status = 'PARCIAL')
            $where
			order by serial ASC";
            $data = $this->db->query($sql)->fetchAll();
            foreach ($data as $key => $shipment) {
                $sql = "SELECT DISTINCT ON (od.product_id) od.*, p.name AS product, COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada
                FROM pur_order_details AS od
                INNER JOIN wms_products AS p
                ON od.product_id = p.id
                LEFT JOIN pur_shipments as sh
                ON sh.order_id = od.po_id
                LEFT JOIN pur_shipment_details as shd
                ON shd.shipment_id = sh.id and od.product_id = shd.product_id and  sh.status = 'RECIBIDO'
                WHERE od.po_id = ".intval($shipment['order_id'])."
                GROUP BY od.id, p.name
                ORDER BY od.product_id, product ASC;";
            $orderDetails = $this->db->query($sql)->fetchAll();
            // var_dump($orderDetails);
            // $sendBool = false;
            foreach ($orderDetails as $key => $detail) {
                if ($detail['restante'] > 0) {
                    $shipment['hayrestante'] = true;
                    // $sendBool = true;
                }
            }
            array_push($ordenes,$shipment);
            }
            $resultado = [];
            foreach($ordenes as $key => $detail) {
                if ($detail['hayrestante']){
                    $sql = "SELECT o.id as order_id,sh.id as shipment_id, sh.movement_id, o.serial as serial, s.name as supplier, b.name as branch_office, b.id as branch_office_id, st.name as storage, st.id as storage_id,o.status as status
                    FROM pur_orders AS o
                    inner JOIN pur_suppliers AS s
                    ON  s.id = o.supplier_id
                    inner JOIN wms_storages AS st
                    ON  st.id = 34
                    inner JOIN wms_branch_offices as b
                    ON st.branch_office_id  = b.id
                    FULL JOIN pur_shipments AS sh
                    ON  sh.order_id = o.id
                    WHERE (o.status = 'PEDIDO' OR o.status = 'PARCIAL') and (sh.order_id = ".intval($detail['order_id'])." and sh.movement_id is null)
                    order by serial ASC";
                    $data2 = $this->db->query($sql)->fetchAll();
                    if ($data2) {
                        array_push($resultado,$data2[0]);
                    } else {
                        array_push($resultado,$detail);
                    }
                } else {

                }
            }
            $this->content['shipments'] = $resultado;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getShipmentsWithOrderSerial ($pt = 0)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sh.*, o.serial AS order
                    FROM pur_shipments AS sh
                    INNER JOIN pur_orders AS o
                    ON sh.order_id = o.id";

            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $this->content['shipments'] = $data->fetchAll();
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShipmentsReport ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request);
            $this->content['shipments'] = $response['data'];
            $this->content['shipmentsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShipmentsByOrderId ($orderId)
    {
        if (is_numeric($orderId)) {
            if ($this->userHasPermission()) {
                $sql = "SELECT s.id, s.serial, s.status, s.order_id, TO_CHAR(s.receive_date :: DATE, 'dd/mm/yyyy') as receive_date, s.total_weight, SUM(sd.qty) AS real_weight, s.invoice
                        FROM pur_shipments AS s
                        LEFT JOIN pur_shipment_details AS sd
                        ON sd.shipment_id = s.id
                        WHERE order_id = $orderId
                        GROUP BY s.id, s.serial, s.status, s.order_id, s.receive_date, s.total_weight
                        ORDER BY s.serial ASC;";
                $shipmentsAux = $this->db->query($sql)->fetchAll();
                $shipments = [];
                foreach ($shipmentsAux as $shipment) {
                    $shipment['canExecute'] = false;
                    if ($shipment['status'] == 'ANALIZADO') {
                        $shipment['canExecute'] = true;
                        $sql = "SELECT d.id
                                FROM pur_shipment_details AS d
                                INNER JOIN pur_shipments AS s
                                ON s.id = d.shipment_id
                                WHERE s.id = ".$shipment['id']."
                                AND (d.qty IS NULL OR d.qty < 1);";
                        $detailsWithoutQty = $this->db->query($sql)->fetchAll();
                        if (count($detailsWithoutQty) > 0) {
                            $shipment['canExecute'] = false;
                        }
                        $sql = "SELECT d.id
                                FROM pur_shipment_details AS d
                                INNER JOIN pur_shipments AS s
                                ON s.id = d.shipment_id
                                WHERE s.id = ".$shipment['id']."
                                AND (d.qty IS NOT NULL AND d.qty >= 1);";
                        $detailsWithQty = $this->db->query($sql)->fetchAll();
                        if (count($detailsWithQty) < 1) {
                            $shipment['canExecute'] = false;
                        }
                    }
                    if (is_null($shipment['real_weight'])) {
                        $shipment['real_weight'] = 0;
                    }
                    array_push($shipments, $shipment);
                }
                $this->content['shipments'] = $shipments;
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } else {
            $this->content['shipments'] = [];
            $this->content['result'] = false;
        }
        $this->response->setJsonContent($this->content);
        return $shipments;
    }

    public function getAnalyzedShipments ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT s.id, s.serial, s.status, s.order_id, po.serial AS purchase_order_serial, s.receive_date, s.total_weight, po.supplier_id, sup.name AS supplier, SUM(sd.qty) AS real_weight
                    FROM pur_shipments AS s
                    INNER JOIN pur_orders AS po
                    ON po.id = s.order_id
                    INNER JOIN pur_suppliers AS sup
                    ON sup.id = po.supplier_id
                    LEFT JOIN pur_shipment_details AS sd
                    ON sd.shipment_id = s.id
                    WHERE s.status = 'ANALIZADO'
                    GROUP BY s.id, s.serial, s.status, s.order_id, purchase_order_serial, s.receive_date, s.total_weight, po.supplier_id, supplier
                    ORDER BY id DESC;";
            $this->content['shipments'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getShipment ($id)
    {
        if ($this->userHasPermission()) {
            $shipment = null;
            if (is_numeric($id)) {
                $sql = "SELECT s.id, s.serial, s.status, s.order_id, TO_CHAR(s.receive_date :: DATE, 'dd/mm/yyyy') AS receive_date, TO_CHAR(s.receive_time :: TIME, 'HH24:MI') AS receive_time, s.total_weight
                        FROM pur_shipments AS s
                        WHERE s.id = $id;";
                $shipment = $this->db->query($sql)->fetch();
                $this->content['result'] = true;
            }
            $this->content['shipment'] = $shipment;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, serial FROM pur_shipments ORDER BY serial ASC;";
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

    private function generatePdf($id)
    {
        if (is_numeric($id)) {
            $sql = "SELECT m.id AS movement_id, ship.id AS shipment_id, ship.serial, TO_CHAR(ship.receive_date :: DATE, 'yyyy/mm/dd') AS receive_date, TO_CHAR(ship.receive_time :: TIME, 'HH24:MI') AS receive_time, o.serial AS order_serial, sup.name AS supplier, sto.name AS storage, COUNT(sam.id) AS sampling_quantity, u.nickname AS user_name
                    FROM pur_shipments AS ship
                    INNER JOIN pur_orders AS o
                    ON o.id = order_id
                    INNER JOIN wms_movements AS m
                    ON m.id = ship.movement_id
                    INNER JOIN wms_storages AS sto
                    ON sto.id = m.storage_id
                    INNER JOIN pur_suppliers AS sup
                    ON sup.id = o.supplier_id
                    INNER JOIN sys_users AS u
                    ON u.id = ship.created_by
                    LEFT JOIN pur_samplings AS sam
                    ON sam.shipment_id = ship.id
                    WHERE ship.id = $id
                    GROUP BY m.id, ship.id, o.serial, sup.name, sto.name, u.nickname;";
            $shipment = $this->db->query($sql)->fetch();

            $sql = "SELECT sd.qty, sd.id AS jumbo_id, o.serial AS order_serial, CONCAT(c.code,'-',l.code,'-',p.code) AS product_code, p.name AS product, od.price, sam.humidity
                    FROM pur_shipment_details AS sd
                    INNER JOIN pur_shipments AS s
                    ON s.id = sd.shipment_id
                    INNER JOIN pur_orders AS o
                    ON o.id = s.order_id
                    INNER JOIN wms_products AS p
                    ON p.id = sd.product_id
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    INNER JOIN pur_order_details AS od
                    ON od.po_id = o.id AND od.product_id = sd.product_id
                    LEFT JOIN pur_samplings AS sam
                    ON sam.shipment_id = s.id AND sam.product_id = p.id
                    WHERE sd.shipment_id = $id
                    ORDER BY sd.id;";
            $details = $this->db->query($sql)->fetchAll();

            $sql = "SELECT s.*, p.name AS product
                    FROM pur_samplings AS s
                    INNER JOIN wms_products AS p
                    ON p.id = s.product_id
                    WHERE s.shipment_id = $id
                    ORDER BY serial ASC;";
            $samplings = $this->db->query($sql)->fetchAll();

            $classifiedDetails = [];
            foreach ($details as $detail) {
                $detailRegistered = false;
                for ($i=0; $i < count($classifiedDetails); $i++) { 
                    if ($classifiedDetails[$i][0]['product_code'] == $detail['product_code']) {
                        array_push($classifiedDetails[$i], $detail);
                        $detailRegistered = true;
                    }
                }
                if (!$detailRegistered) {
                    array_push($classifiedDetails, [$detail]);
                }
            }

            $pdf = new PDFShipment('P','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetShipmentId($shipment['shipment_id']);
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetTextColor(4, 26, 131);
            $pdf->SetFillColor(218, 221, 238);
            $pdf->SetFont('Arial','',8);
            $pdf->SetDrawColor(4, 26, 131);
            $pdf->Ln();

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode('ALMACÉN DE ENTRADA: '.$shipment['storage']),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,utf8_decode('FECHA: '.$shipment['receive_date'].' '.$shipment['receive_time']),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode('PROVEEDOR: '.$shipment['supplier']),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,utf8_decode('ORDEN DE COMPRA: '.$shipment['order_serial']),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode('No. RECIBO / FACTURA: '),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,utf8_decode('RECEPCIÓN: '.$shipment['serial']),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode('RECIBE: '.$shipment['user_name']),0,1,'L');
            $pdf->Ln();

            $tW = 0;
            $tRW = 0;
            $tA = 0;
            $oP = 0;
            $oP2 = 0;

            foreach ($classifiedDetails as $cdetails) {
                $pdf->SetWidths(array(15, 30, 70, 23, 30, 30));
                $pdf->SetAligns(array( 'C', 'C', 'C', 'C', 'C', 'C'));
                $pdf->SetHeight(6);
                $pdf->SetDrawEdge(false);
                $pdf->Row(array('FOLIO', utf8_decode('CÓDIGO'), 'PRODUCTO', 'PIEZAS', 'PRECIO UNITARIO', 'IMPORTE'));
                $pdf->SetAligns(array('C', 'C', 'L', 'R', 'R', 'R'));
                $fill = true;
                $totalWeight = 0;
                $totalRealWeight = 0;
                $totalAmount = 0;
                foreach ($cdetails as $detail) {
                    $realQty = $detail['qty'];
                    $amount = $realQty * $detail['price'];
                    $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill));
                    $pdf->Row(array($detail['jumbo_id'], $detail['product_code'], utf8_decode($detail['product']), number_format($detail['qty'], 2, '.', ','), '$'.number_format($detail['price'], 2, '.', ','), '$'.number_format($amount, 2, '.', ',')));
                    $fill = !$fill;
                    $totalWeight += $detail['qty'];
                    $totalRealWeight += $realQty;
                    $totalAmount += $amount;
                    $tW += $detail['qty'];
                    $tRW += $realQty;
                    $tA += $amount;
                }
//                $originalPrice = $totalAmount / 1.16;
                $pdf->SetWidths(array(115, 23, 30, 30));
                $pdf->SetAligns(array('R', 'R', 'R', 'R'));
                $pdf->SetHeight(6);
                $pdf->SetDrawEdge(false);
                $pdf->SetFill(array(false, false, false, false));
                $pdf->Row(array('SUBTOTAL', number_format($totalWeight, 2, '.', ','),NULL, '$'.number_format($totalAmount, 2, '.', ',')));
                $pdf->Ln();
            }

            $subtotal = $tA;
            $iva = ($subtotal * 16)/100;
            $totalIVA = $subtotal + $iva;
            $total = $subtotal + $totalIVA;
            $pdf->SetWidths(array(115, 23, 30, 30));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(false);
            $pdf->SetFill(array(false, true, false, true));
            $pdf->Row(array('TOTAL', number_format($tW, 2, '.', ','),  'SUBTOTAL', '$'.number_format($subtotal, 2, '.', ',')));
            $pdf->SetWidths(array(168, 30));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->SetFill(array(false, true));
            $pdf->Row(array('IVA', '$'.number_format($iva, 2, '.', ',')));
            $pdf->Row(array('PRECIO NETO', '$'.number_format($totalIVA, 2, '.', ',')));

            $pdf->SetTitle('Ticket de entrada '.$shipment['shipment_id'],true);

            return $pdf;
        }
        return null;
    }

    public function getPdf ($id)
    {
        if (is_numeric($id)) {
            $shipment = Shipments::findFirst($id);

            if ($shipment) {
                $pdf = $this->generatePdf($id);
                
                if (!is_null($pdf)) {
                    $pdf->Output('I', "Ticket de entrada $shipment->id.pdf", true);

                    $response = new Phalcon\Http\Response();
                    $response->setHeader("Content-Type", "application/pdf");
                    $response->setHeader("Content-Disposition", 'inline; filename="Ticket de entrada '.$shipment->id.'.pdf"');
                    return $response;
                }
            }
        }
        return null;
    }

    private function savePdf ($id)
    {
        if (is_numeric($id)) {
            $shipment = Shipments::findFirst($id);

            if ($shipment) {
                $pdf = $this->generatePdf($id);

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/shipments/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Ticket de entrada $shipment->id.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
            }
        }
        return null;
    }

    public function getPdfQr ($shipmentId) {
        if (is_numeric($shipmentId)) {
            $sql = "SELECT CONCAT(c.code,'-',l.code,'-',p.code) AS product_code, ship.serial AS shipment, p.name AS product, det.id AS bag, det.qty, o.serial AS purchase_order, TO_CHAR(ship.receive_date :: DATE, 'dd/mm/yyyy') AS receive_date
                    FROM pur_shipment_details AS det
                    INNER JOIN pur_shipments AS ship
                    ON det.shipment_id = ship.id
                    INNER JOIN pur_orders AS o
                    ON ship.order_id = o.id
                    INNER JOIN wms_products AS p
                    ON det.product_id = p.id
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    WHERE det.shipment_id = $shipmentId;";
            $details = $this->db->query($sql)->fetchAll();

            if (count($details) == 0) {
                return false;
            }
            $pdf = new PDFTableQr('L','in',array(4,6));
            $pdf->SetLineWidth(0.05);
            $pdf->SetDrawColor(217, 217, 217);
            $pdf->SetTextColor(4, 26, 131);
            $pdf->SetFont('Arial','',5);
            $pdf->SetAutoPageBreak(false);
            if (count($details) > 0) {
                $pdf->SetTitle('QR Jumbos Recepción '.$details[0]['shipment'],true);
            }
            foreach ($details as $detail) {
                $pdf->AddPage();
                $pdf->SetY($pdf->GetY() - 5);
                $pdf->SetFont('Arial','B',30);
                $pdf->MultiCell(1.6, 2.5, utf8_decode($detail['bag']), 0, 'C', false);
                $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
                $img = $path . 'logo.png';
                $qrCode = new QrCode($detail['bag']);
                $qrCode->setWriterByName('png');
                $uriData = $qrCode->writeDataUri();
                $pdf->Image($uriData,0.2,1.3,2,0,'PNG');
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetY(3.6);
                $pdf->Cell(5.5, 0, number_format($detail['qty'], 2, '.', ',').' Kg.', 0, 0, 'C', false);
                $pdf->SetXY($x, $y);
                $pdf->Image($img,3.5,0.1,1,0,'PNG');
                $pdf->SetFont('Arial','',20);
                $pdf->SetXY($pdf->GetX()+1.9, $pdf->GetY()-0.5);
                $pdf->SetWidths(array(1.1,2.4));
                $pdf->SetAligns(array('L','R'));
                $pdf->SetHeight(0.3);
                $pdf->MultiCell(3.5, 0.3, utf8_decode($detail['product_code']), 1, 'C', false);
                $pdf->SetFont('Arial','',15);
                $pdf->SetX($pdf->GetX() + 1.9);
                $pdf->Row(array(utf8_decode('Recepción'), utf8_decode($detail['shipment'])));
                $pdf->SetX($pdf->GetX() + 1.9);
                $pdf->Row(array('OC', utf8_decode($detail['purchase_order'])));
                $pdf->SetX($pdf->GetX() + 1.9);
                $pdf->Row(array('Producto', utf8_decode($detail['product'])));
                $pdf->SetX($pdf->GetX() + 1.9);
                $pdf->MultiCell(3.5, 0.3, utf8_decode($detail['receive_date']), 1, 'C', false);
            }

            if (count($details) > 0) {
                $pdf->Output('I', 'QR Jumbos Recepción '.$details[0]['shipment'].'.pdf', true);
            }

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="QR Jumbos Recepción '.$det['shipment'].'.pdf"');
            return $response;
        }
        return false;
    }

    public function create ()
    {
        date_default_timezone_set('America/Mexico_City');
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPost();
                if (isset($request['order_id']) && is_numeric($request['order_id']) && isset($request['receive_date']) && isset($request['receive_time'])) {
                    $shipmentWithoutReceiving = Shipments::findFirst("order_id = ".$request['order_id']." AND status = 'NUEVO'");

                    if ($shipmentWithoutReceiving) {
                        $this->content['message'] = Message::error('No se puede crear una nueva recepción debido a que aún hay recepciones anteriores pendientes.');
                    } else {
                        $tx = $this->transactions->get();
                        $sql = "SELECT serial FROM pur_shipments ORDER BY serial DESC LIMIT 1;";
                        $serial = $this->db->query($sql)->fetch();
                        $serial = isset($serial['serial']) ? ($serial['serial'] + 1) : 1;
            
                        $shipment = new Shipments();
                        $shipment->setTransaction($tx);
                        $shipment->status = 'NUEVO';
                        $shipment->order_id = $request['order_id'];
                        $shipment->serial = $serial;
                        $shipment->receive_date = $request['receive_date'];
                        $shipment->receive_time = $request['receive_time'];
            
                        if ($shipment->create()) {
                            if (isset($request['details']) && !is_null($request['details']) && sizeof($request['details']) > 0) {
                                foreach ($request['details'] as $detail) {
                                    $sql = "SELECT product_shipment_number
                                            FROM pur_shipment_details
                                            WHERE shipment_id = $shipment->id
                                            AND product_id = ".$detail['product_id']."
                                            ORDER BY product_shipment_number DESC
                                            LIMIT 1;";
                                    $lastConsecutiveNumber = $this->db->query($sql)->fetch()['product_shipment_number'];
            
                                    $shipmentDetail = new ShipmentDetails();
                                    $shipmentDetail->setTransaction($tx);
                                    $shipmentDetail->shipment_id = $shipment->id;
                                    $shipmentDetail->product_id = $detail['product_id'];
                                    $shipmentDetail->unit_id = $detail['unit_id'];
                                    $shipmentDetail->qty = $detail['qty'];
                                    $shipmentDetail->product_shipment_number = ++$lastConsecutiveNumber;
                                    $shipmentDetail->create();
                                }
                            }
                            if (isset($request['samplings']) && !is_null($request['samplings']) && sizeof($request['samplings']) > 0) {
                                foreach ($request['samplings'] as $samp) {
                                    $sql = "SELECT serial FROM pur_samplings ORDER BY serial DESC LIMIT 1;";
                                    $serial = $this->db->query($sql)->fetch();
                                    $serial = isset($serial['serial']) ? ($serial['serial'] + 1) : 1;
                                    $sampling = new Samplings();
                                    $sampling->setTransaction($tx);
                                    $sampling->shipment_id = $shipment->id;
                                    $sampling->status = 'NUEVO';
                                    $sampling->product_id = $samp['product_id'];
                                    $sampling->humidity = $samp['humidity'];
                                    $sampling->dirty = $samp['dirty'];
                                    $sampling->metals = $samp['metals'];
                                    $sampling->recicled = $samp['recicled'];
                                    $sampling->pvc = $samp['pvc'];
                                    $sampling->serial = $serial;
                                    $sampling->create();
                                }
                            }
                            $this->content['shipment'] = $shipment;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La recepción ha sido creada con el serial: '.$shipment->serial);
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipment);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la recepción.');
                            $tx->rollback();
                        }
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

    public function uploadInvoiceFile4($id)
    {
        $request = $this->request->getPost();
        $idFile = 0;
        //var_dump($this->userHasPermission());
        //if ($this->userHasPermission()) {
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
        /* } else {
            $this->content = Message::error('No cuenta con los permisos necesarios.');
        } */
        // $this->updateInvoice($id, $idFile);
        $this->response->setJsonContent( $this->content);
        $this->response->send();
    }

    public function updateInvoice ($id, $iddocument) {
        /* var_dump($id);
        var_dump($iddocument); */
        /* if ($this->userHasPermission()) { */
            $tx = $this->transactions->get();
            $payment = Shipments::findFirst($id);
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
        /* }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        } */
    }

    public function uploadInvoiceFile ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $shipment = Shipments::findFirst($id);
                if ($shipment) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shipments/';
                    
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0755);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $fullPath = $upload_dir . $fileName;
                        if ($shipment->invoice != null && file_exists($upload_dir.$shipment->invoice)) {
                            @unlink($upload_dir.$shipment->invoice);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $shipment->setTransaction($tx);
                        $shipment->invoice = $fileName;
                        if ($shipment->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La factura ha sido subida exitosamente.');
                        } else {
                            $this->content['message'] = Message::error('Error al subir factura.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la recepción.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de recepción válido.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = Shipments::findFirst($id);

                $request = $this->request->getPut();

                if ($shipment) {
                    $shipment->setTransaction($tx);
                    if (isset($request['status'])) {
                        $shipment->status = $request['status'];
                    }
                    if (isset($request['order_id']) && is_numeric($request['order_id'])) {
                        $shipment->order_id = $request['order_id'];
                    }
                    if (isset($request['receive_date'])) {
                        $shipment->receive_date = $request['receive_date'];
                    }
                    if (isset($request['receive_time'])) {
                        $shipment->receive_time = $request['receive_time'];
                    }
                    if (isset($request['total_weight']) && is_numeric($request['total_weight']) && $request['total_weight'] > 0) {
                        $shipment->total_weight = $request['total_weight'];
                    }

                    if ($shipment->update()) {
                        $this->content['shipment'] = $shipment;
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La recepción ha sido modificada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shipment);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la recepción.');
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

    public function analyzed ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = Shipments::findFirst($id);

                $request = $this->request->getPut();

                if ($shipment) {
                    if ($shipment->status == 'MUESTREADO') {
                        $samplings = Samplings::find("shipment_id = $shipment->id");
                        $inactiveProducts = [];
                        foreach ($samplings as $sampling) {
                            $product = Products::findFirst($sampling->product_id);
                            if (!$product->active) {
                                array_push($inactiveProducts, $product->name);
                            }
                        }
                        if (count($inactiveProducts) == 0) {
                            $shipment->setTransaction($tx);
                            $shipment->status = 'ANALIZADO';

                            if ($shipment->update()) {
                                $this->content['shipment'] = $shipment;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El estatus de la recepción ha cambiado a ANALIZADO.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($shipment);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de la recepción.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede cambiar el estatus de la recepción.');
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

    public function reject ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = Shipments::findFirst($id);

                $request = $this->request->getPut();

                if ($shipment) {
                    if ($shipment->status == 'MUESTREADO') {
                        $shipment->setTransaction($tx);
                        $shipment->status = 'RECHAZADO';

                        if ($shipment->update()) {
                            $this->content['shipment'] = $shipment;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El estatus de la recepción ha cambiado a RECHAZADO.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipment);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de la recepción.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede cambiar el estatus de la recepción.');
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

    public function entry ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = Shipments::findFirst($id);

                $request = $this->request->getPut();
                /*print_r($request);
                exit();*/

                if ($shipment) {
                    if ($shipment->status == 'NUEVO' && is_null($shipment->movement_id)) {
                        $sql = "SELECT sd.id, sd.shipment_id, sd.product_id, p.name AS product, sd.qty, sd.product_shipment_number, sam.humidity
                                FROM pur_shipment_details AS sd
                                INNER JOIN wms_products AS p
                                ON p.id = sd.product_id
                                LEFT JOIN pur_samplings AS sam
                                ON sam.shipment_id = sd.shipment_id AND sam.product_id = sd.product_id
                                WHERE sd.shipment_id = $shipment->id;";
                        $shipmentDetails = $this->db->query($sql)->fetchAll();
                        if (count($shipmentDetails) > 0) {
                            $shipmentDetailsWithoutQty = ShipmentDetails::find("shipment_id = $shipment->id AND qty IS NULL");
                            
                            if (count($shipmentDetailsWithoutQty) == 0) {
                                $inactiveProducts = [];
                                foreach ($shipmentDetails as $detail) {
                                    $product = Products::findFirst($detail['product_id']);
                                    if (!$product->active) {
                                        array_push($inactiveProducts, $product->name);
                                    }
                                }
                                
                                if (count($inactiveProducts) == 0) {
                                    $movement = new Movements();
                                    $movement->setTransaction($tx);
                                    $orderStorage = PurchaseOrders::findFirst($shipment->order_id);
                                    //$movement->storage_id = 36; // Es 34 porque es el almacén al que entra la merca por defecto
                                    $movement->storage_id = $orderStorage->storage_id;
                                    $movement->type_id = 1;
                                    $movement->status = 'EJECUTADO';
                                    $movement->date = date('Y-m-d H:i:s');
                                    $movement->ejecute_date = date("Y-m-d H:i:s");
                                    $movement->folio = 0;
                                    // $movement->po_id = $shipment->id;
                                    // var_dump($movement->date);
                                    if ($movement->create()) {
                                        $shipment->setTransaction($tx);
                                        $shipment->movement_id = $movement->id;
                                        $shipment->status = 'RECIBIDO';

                                        if ($shipment->update()) {
                                            $movementDetailError = false;
                                            $order = PurchaseOrders::findFirst($shipment->order_id);
                                            foreach ($shipmentDetails as $shipmentDetail) {
                                                $orderDetail = PurchaseOrderDetails::findFirst("po_id = $order->id AND product_id = ".$shipmentDetail['product_id']);
                                                $movementDetail = new MovementDetails();
                                                $movementDetail->setTransaction($tx);
                                                $movementDetail->movement_id = $movement->id;
                                                $movementDetail->product_id = $shipmentDetail['product_id'];
                                                $movementDetail->qty = $shipmentDetail['qty'];
                                                $movementDetail->bag_id = $shipmentDetail['id'];
                                                $movementDetail->unit_price = ($orderDetail ? $orderDetail->price : 0);
                                                if (!$movementDetail->create()) {
                                                    $movementDetailError = true;
                                                }
                                            }
                                            if ($movementDetailError) {
                                                $this->content['error'] = Helpers::getErrors($movementDetail);
                                                $this->content['message'] = Message::error('Ha ocurrido un error con un detalle al intentar ejecutar la entrada a almacén.');
                                                $tx->rollback();
                                            } else {
                                                $order->status = 'RECIBIDO';
                                                if (!$order->update()) {
                                                    $this->content['error'] = Helpers::getErrors($movementDetail);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al actualizar la orden.');
                                                    $tx->rollback();
                                                }
                                                $this->content['shipment'] = $shipment;
                                                $tx->commit();
                                                $msg = 'El estatus de la recepción ha cambiado a RECIBIDO';
                                                $order = PurchaseOrders::findFirst($shipment->order_id);
                                                $sql = "SELECT DISTINCT ON (od.product_id) od.*, p.name AS product, COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada
                                                    FROM pur_order_details AS od
                                                    INNER JOIN wms_products AS p
                                                    ON od.product_id = p.id
                                                    LEFT JOIN pur_shipments as sh ON sh.order_id = od.po_id
                                                    LEFT JOIN pur_shipment_details as shd ON shd.shipment_id = sh.id and od.product_id = shd.product_id
                                                    WHERE od.po_id = $order->id
                                                    GROUP BY od.id, p.name
                                                    ORDER BY od.product_id, product ASC;";
                                                $orderDetails = $this->db->query($sql)->fetchAll();
                                                $sendBool = false;
                                                foreach ($orderDetails as $key => $detail) {
                                                    if ($detail['restante'] > 0) {
                                                        $sendBool = true;
                                                    }
                                                }
                                                if ($sendBool) {
                                                    $order->status = 'PARCIAL';
                                                    if (!$order->update()) {
                                                        $this->content['error'] = Helpers::getErrors($movementDetail);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al actualizar la orden.');
                                                        $tx->rollback();
                                                    }
                                                }
                                                $msg = 'Movimiento creado con exito';
                                                $this->content['message'] = Message::success($msg);
                                                $this->content['result'] = true;
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($shipment);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar la entrada a almacén.');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($shipment);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar la entrada a almacén.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.');
                                }
                            } else {
                                $this->content['message'] = Message::error('La recepción cuenta con jumbos sin peso.');
                            }
                        } else {
                            $this->content['message'] = Message::error('La recepción no cuenta con jumbos registrados.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede ejecutar la entrada a almacén.');
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

    public function sendPdfToProvider ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $shipment = Shipments::findFirst($id);
                if ($shipment) {
                    if ($shipment->status == 'RECIBIDO') {
                        $order = PurchaseOrders::findFirst($shipment->order_id);
                        if ($order->status == 'APROBADO') {
                            $supplier = Suppliers::findFirst($order->supplier_id);
                            $actions = Actions::findFirst(1);
                            if ($actions->host && $actions->port && $actions->username && $actions->password) {
                                if ($supplier->email) {
                                    $sql = "SELECT sd.id, sd.shipment_id, sd.product_id, p.name AS product, sd.qty, sd.product_shipment_number, sam.humidity
                                            FROM pur_shipment_details AS sd
                                            INNER JOIN wms_products AS p
                                            ON p.id = sd.product_id
                                            LEFT JOIN pur_samplings AS sam
                                            ON sam.shipment_id = sd.shipment_id AND sam.product_id = sd.product_id
                                            WHERE sd.shipment_id = $shipment->id;";
                                    $shipmentDetails = $this->db->query($sql)->fetchAll();
                                    if (count($shipmentDetails) > 0) {
                                        $htmlBody = '
                                        <!DOCTYPE html>
                                            <html>
                                            <head>
                                                <style>
                                                #table-container, #logo-container {
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

                                                #table-container table {
                                                    margin: 0 auto;
                                                    text-align: left;
                                                    font-family: arial, sans-serif;
                                                    border-collapse: collapse;
                                                    width: 100%;
                                                    min-width: 500px;
                                                    max-width: 750px;
                                                }

                                                #table-container table td, #table-container table th {
                                                    padding: 8px;
                                                    border: 1px solid hsla(214, 100%, 50%, 0.5);
                                                }

                                                #table-container table th {
                                                    text-align: center;
                                                    background-color: #0070FF;
                                                    color: #FFFFFF;
                                                }

                                                #code-header {
                                                    width: 20%;
                                                }

                                                #product-header {
                                                    width: 45%;
                                                }

                                                #qty-header {
                                                    width: 15%;
                                                }

                                                #h2o-header {
                                                    width: 10%;
                                                }

                                                #real-qty-header {
                                                    width: 15%;
                                                }

                                                .code-data {
                                                    text-align: right;
                                                    width: 20%;
                                                }

                                                .product-data {
                                                    text-align: left;
                                                    width: 45%;
                                                }

                                                .qty-data {
                                                    text-align: right;
                                                    width: 15%;
                                                }

                                                .h2o-data {
                                                    text-align: right;
                                                    width: 10%;
                                                }

                                                .real-qty-data {
                                                    text-align: right;
                                                    width: 15%;
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
                                                    Adjunto encontrará el recibo de entrada a nuestro almacén.
                                                    <br>
                                                    <br>
                                                    Es importante recordar que lo autorizado a documentar o facturar debe ser idéntico a lo recibido en este documento.
                                                    <br>
                                                    <br>
                                                    Muchas gracias!!
                                                </p>
                                                <div id="table-container">
                                                <table>
                                                    <tr>
                                                    <th id="code-header">Código</th>
                                                    <th id="product-header">Producto</th>
                                                    <th id="qty-header">Peso bruto</th>
                                                    <th id="h2o-header">%H2O</th>
                                                    <th id="real-qty-header">Peso neto</th>
                                                    </tr>';
                                        $rowBackgroundColor = 'hsla(214, 100%, 50%, 0.5)';
                                        foreach ($shipmentDetails as $detail) {
                                            $realQty = $detail['humidity'] > 0.5 ? ($detail['qty']*(100-($detail['humidity']-0.5))/100) : $detail['qty'];
                                            $htmlBody .= '
                                                    <tr style="background-color: '.$rowBackgroundColor.'">
                                                    <td class="code-data">'.$detail['id'].'</td>
                                                    <td class="product-data">'.$detail['product'].'</td>
                                                    <td class="qty-data">'.number_format($detail['qty'], 2, '.', ',').' Kg.</td>
                                                    <td class="h2o-data">'.number_format($detail['humidity'], 2, '.', ',').' %</td>
                                                    <td class="real-qty-data">'.number_format($realQty, 2, '.', ',').'</td>
                                                    </tr>
                                            ';
                                            if ($rowBackgroundColor == 'hsla(214, 100%, 50%, 0.5)') {
                                                $rowBackgroundColor = 'hsla(214, 100%, 50%, 0.25)';
                                            } else {
                                                $rowBackgroundColor = 'hsla(214, 100%, 50%, 0.5)';
                                            }
                                        }
                                        $htmlBody .= '
                                                </table>
                                                </div>
                                            </body>
                                        </html>';
                                        $mailer = new Mailer();
                                        $mailer->htmlBody = $htmlBody;
                                        $mailer->attachedFile = $this->savePdf($id);
                                        $mailer->host = $actions->host;
                                        $mailer->port = $actions->port;
                                        $mailer->username = $actions->username;
                                        $mailer->password = $actions->password;
                                        $mailer->subject = "Aviso de Recepción de MP #$shipment->serial OC #$order->serial";
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
                                        $content['message'] = Message::success($result_message->message);
                                        $content['result'] = $result_message->status;
                                    }
                                } else {
                                    $content['message'] = Message::error('No se ha enviado el correo debido a que el proveedor no tiene correo registrado.');
                                }
                            } else {
                                $content['message'] = Message::error('No se pueden enviar correos debido a que faltan datos de la cuenta de correo');
                            }
                        } else {
                            $content['message'] = Message::error("Estatus de orden: $order->status. No se puede enviar email para la recepción seleccionada.");
                        }
                    } else {
                        $content['message'] = Message::error("Estatus de recepción: $shipment->status. No se puede enviar email para la recepción seleccionada.");
                    }
                } else {
                    $content['message'] = Message::error('No se ha encontrado la recepción.');
                }
            } else {
                $content['message'] = Message::error('No se ha recibido una recepción válida.');
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function downloadInvoiceFile ($id)
    {
        if (is_numeric($id)) {
            $shipment = Shipments::findFirst($id);
            if ($shipment && $shipment->invoice) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shipments/';
                $fullPath = $upload_dir.$shipment->invoice;
                if (file_exists($fullPath)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($fullPath).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($fullPath));
                    readfile($fullPath);
                    exit;
                } else {
                    $this->flash->notice('No se ha encontrado el archivo de factura.');
                }
            }
        }
        return null;
    }

    public function delete ($id)
    {
         try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = Shipments::findFirst($id);

                if ($shipment) {
                    $shipment->setTransaction($tx);

                    if ($shipment->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La recepción ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shipment);
                        if ($this->content['error'][0]) {
                            $this->content['message'] = Message::error($this->content['error'][0]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la recepción.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La recepción no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getGridSQL ($request) {
        $y = date('Y');
        $where = "";
        $order = "";

        $sortBy = "";
        $pagination = $request['pagination'];

        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage']." ";

        $sql = "SELECT count(o.serial)
                FROM pur_orders AS o
                WHERE o.status = 'PARCIAL'";
        $shipmentsCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT DISTINCT ON (o.id) o.serial, o.id, o.shipment_parcial_status as status,
                SUM(shd.qty) as entrada,
                (SUM(od.qty) - SUM(shd.qty)) as deuda,
                sum(od.qty) as productos
                FROM pur_orders AS o
                JOIN pur_order_details as od ON o.id = od.po_id
                JOIN wms_products AS p ON od.product_id = p.id
                JOIN pur_shipments as sh ON sh.order_id = od.po_id
                JOIN pur_shipment_details as shd ON shd.shipment_id = sh.id and od.product_id = shd.product_id
                WHERE o.status = 'PARCIAL'
                GROUP BY o.id, sh.id
                ORDER BY o.id, sh.id DESC
                {$offset} {$limit}
                ";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $shipmentsCount[0]['count']);
        return $response;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 22 OR role_id = 21 OR role_id = 7 or role_id = 2 or role_id = 26 or role_id = 20 or role_id = 25 or role_id = 27 or role_id = 28 or role_id = 29)
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


class PDFTableQr extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $drawEdge = true;
    var $fillCell = false;

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

class PDFShipment extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $shipmentId;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,10,60,0,'PNG');
        $this->SetTextColor(4, 26, 131);
        $this->SetFont('Arial','B',20);
        $this->SetFillColor(218, 221, 238);
        $this->SetX($this->GetX()+100);
        $this->MultiCell(100, 10, "TICKET DE ENTRADA $this->shipmentId", 0, 'C', false);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        //$this->Cell(195, 6, "WWW.empresa.mx", 0, 0, 'C', false);
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

    function SetShipmentId($m)
    {
        $this->shipmentId=$m;
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