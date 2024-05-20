<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;
use Zxing\QrReader;

class ShipmentDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShipmentDetailsByShipmentId ($shipmentId)
    {
        
        if (is_numeric($shipmentId)) {
            if ($this->userHasPermission()) {
                
                $sql = "SELECT sd.*, CONCAT(c.code,'-',l.code,'-',p.code) AS code,p.name AS product, TO_CHAR(ship.receive_date :: DATE, 'dd/mm/yyyy') AS receive_date, TO_CHAR(ship.receive_time :: TIME, 'HH24:MI') AS receive_time
                        FROM pur_shipment_details AS sd
                        INNER JOIN pur_shipments AS ship
                        ON ship.id = sd.shipment_id
                        INNER JOIN wms_products AS p
                        ON sd.product_id = p.id
                        INNER JOIN wms_lines AS l 
                        ON p.line_id = l.id
                        INNER JOIN wms_categories AS c 
                        ON l.category_id = c.id
                        WHERE sd.shipment_id = $shipmentId
                        ORDER BY sd.id ASC;";
                $shipments = $this->db->query($sql)->fetchAll();
                $this->content['shipmentDetails'] = $shipments;
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } else {
            $this->content['shipmentDetails'] = [];
            $this->content['result'] = false;
        }
        $this->response->setJsonContent($this->content);
    }

    public function getAvailableBags ()
    {
        $bags = array();
        $sql = "SELECT sd.id AS value, sd.product_id, sd.qty, sd.product_shipment_number, s.receive_date, CONCAT('Saco ', sd.id, ' (', sd.qty, ' Kg.) [', s.receive_date, ']') AS label
                FROM pur_shipment_details AS sd
                INNER JOIN pur_shipments AS s
                ON sd.shipment_id = s.id
                INNER JOIN wms_movements AS m
                ON m.id = s.movement_id
                WHERE m.status = 1;";
        $totalBags = $this->db->query($sql)->fetchAll();
        foreach ($totalBags as $bag) {
            $sql = "SELECT l.id AS lot_id, l.order_id, l.raw_material_movement_id, l.raw_material_return_movement_id, md.product_id, md.bag_id
                    FROM prd_lots AS l
                    INNER JOIN wms_movements AS m
                    ON m.id = l.raw_material_movement_id
                    INNER JOIN wms_movement_details AS md
                    ON md.movement_id = m.id
                    WHERE m.status = 1
                    AND md.bag_id = ".$bag['value']."
                    ORDER BY m.created DESC
                    LIMIT 1;";
            $lastRawMaterialConsumption = $this->db->query($sql)->fetch();
            if ($lastRawMaterialConsumption) {
                if (!is_null($lastRawMaterialConsumption['raw_material_return_movement_id'])) {
                    $sql = "SELECT m.id AS movement_id, md.bag_id, md.product_id, md.qty
                            FROM wms_movements AS m
                            INNER JOIN wms_movement_details AS md
                            ON md.movement_id = m.id
                            WHERE m.id = ".$lastRawMaterialConsumption['raw_material_return_movement_id']."
                            AND md.bag_id = ".$bag['value']."
                            AND m.status = 1;";
                    $lastReturnedRawMaterial = $this->db->query($sql)->fetch();
                    if ($lastReturnedRawMaterial && $lastReturnedRawMaterial['qty'] > 0) {
                        $bag['qty'] = $lastReturnedRawMaterial['qty'];
                        $bag['label'] = 'Saco '. $bag['value'].' ('.$lastReturnedRawMaterial['qty'].' Kg.) ['.$bag['receive_date'].']';
                        array_push($bags, $bag);
                    }
                }
            } else {
                array_push($bags, $bag);
            }
        }
        usort($bags, function($a, $b) {
            if ($a['qty'] == $b['qty'])
                return (0);
            return (($a['qty'] < $b['qty']) ? -1 : 1);
        });
        $this->content['bags'] = $bags;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getAllBags ()
    {
        $sql = "SELECT sd.id AS value, sd.product_id, CONCAT('Saco ', sd.id) AS label
                FROM pur_shipment_details AS sd
                INNER JOIN pur_shipments AS s
                ON sd.shipment_id = s.id
                INNER JOIN wms_movements AS m
                ON m.id = s.movement_id
                WHERE m.status = 1;";
        $this->content['bags'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getQtyProducts ($order, $product)
    {
        $sql = "SELECT product_id, COALESCE(sum(qty), 0) as total from pur_shipment_details 
            join pur_shipments on pur_shipments.id = pur_shipment_details.shipment_id
            join pur_orders on pur_orders.id = pur_shipments.order_id
            where order_id = {$order} and product_id = {$product}
            group by product_id";
        $qty = $this->db->query($sql)->fetch();
        $this->content['qty'] = $qty;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptionsByPurchaseOrder ($purchaseOrderId) {
        $options = [];
        $options2 = [];
        if (is_numeric($purchaseOrderId)) {
            $sql = "SELECT p.id AS value, CONCAT(p.name) AS label, qty
                    FROM wms_products AS p
                    INNER JOIN pur_order_details AS od
                    ON od.product_id = p.id
                    WHERE od.po_id = $purchaseOrderId
                    AND p.active
                    ORDER BY label ASC;";
            $options = $this->db->query($sql)->fetchAll();
            $shipment = new ShipmentDetails();
            foreach ($options as $key => $value) {
                $product = $options[$key]['value'];
                $sql = "SELECT product_id, COALESCE(sum(qty), 0) as total from pur_shipment_details 
                join pur_shipments on pur_shipments.id = pur_shipment_details.shipment_id
                join pur_orders on pur_orders.id = pur_shipments.order_id
                where order_id = {$purchaseOrderId} and product_id = {$product}
                group by product_id";
            $qty = $this->db->query($sql)->fetch();
            // var_dump(($qty !== false ? $qty['total'] : 0));
            $total = $options[$key]['qty'] - ($qty !== false ? $qty['total'] : 0);
            if ($total > 0) {
                array_push($options2,$value);
            }
            // this.detail.fields.qty = this.detail.fields.product.qty - (data.qty !== false ? data.qty.total : 0)
            // this.totalQty = this.detail.fields.qty
            }
        
        }
        return $options2;
    }

    public function addAll () {
        
        $overload = false;
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPost();
                $tx = $this->transactions->get();
                //$productsToAdd = isset($request['products']) ? $request['products'] : [];
                $productsToAdd = $this->getOptionsByPurchaseOrder(intval($request['shipment']));
                /* $allAddProducts = $this->getOptionsByPurchaseOrder(intval($request['shipment']));
                var_dump($allAddProducts); */
                if (count($productsToAdd) > 0) {
                    $validUser = Auth::getUserData($this->config);
                    $getProducts = "SELECT id, name FROM wms_products where active is true ";
                    $allProducts = $this->db->query($getProducts)->fetchAll();
                    $inserts = [];
                    foreach ($allProducts as $key => $value) {
                        foreach ($productsToAdd as $key => $valueProducts) {
                            if (intval($valueProducts['value']) == intval($value['id'])) {
                                array_push($inserts,'('.$request['shipment_id'].','.intval($valueProducts['value']).','.$valueProducts['qty'].','.$valueProducts['qty'].','.$validUser->id.', now())');
                            }
                        }
                    }
                    $spellValues = implode(',',$inserts);
                    
                    $shipment = Shipments::findFirst($request['shipment_id']);
                    if ($shipment) {
                        $queryInsert = "INSERT INTO pur_shipment_details (shipment_id, product_id, qty, product_shipment_number, created_by, created) VALUES ".$spellValues.";";
                    }
                    //var_dump($queryInsert);
                    if ($this->db->query($queryInsert)) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se agregaron los productos correctamente.');
                        $tx->commit();
                    }else {
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($shipment);
                        
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error($this->content['error']['message'] ?? 'Ha ocurrido un error al intentar crear la recepción..');
                        }  
                        $this->content['error'] = Helpers::getErrors($shipmentDetail);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la recepción.');
                        $tx->rollback();
                    }
                }else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('No hay productos para agregar.');
                    $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function create ()
    {
        $overload = false;
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPost();
                if (isset($request['shipment_id']) && is_numeric($request['shipment_id']) && isset($request['product_id']) && is_numeric($request['product_id'])) {
                    $product = Products::findFirst($request['product_id']);
                    $shipment = Shipments::findFirst($request['shipment_id']);
                    if ($product->active && $shipment) {
                        $tx = $this->transactions->get();
                        $shipmentDetail = new ShipmentDetails();
                        $shipmentDetail->setTransaction($tx);
                        $shipmentDetail->shipment_id = $request['shipment_id'];
                        $shipmentDetail->product_id = $request['product_id'];
                        if (isset($request['unit_id']) && is_numeric($request['unit_id'])) {
                            $shipmentDetail->unit_id = $request['unit_id'];
                        }
                        $shipmentDetail->qty = $request['qty'];
                        $shipmentDetail->product_shipment_number = $request['qty'];
                        if (isset($request['observation'])) {
                            $shipmentDetail->observation = $request['observation'];
                        }

                        if ($shipmentDetail->create()) {
                            // $sql = "SELECT pur_shipment_details.product_id, COALESCE(sum(pur_shipment_details.qty), 0) as qty, pur_order_details.qty as total 
                            //         from pur_shipment_details 
                            //         join pur_order_details on pur_order_details.product_id =pur_shipment_details.product_id
                            //         where pur_order_details.po_id = {$shipment->order_id}
                            //         group by pur_shipment_details.product_id, pur_order_details.qty";
                            // $checking_quantity = $this->db->query($sql)->fetchAll();
                            /*foreach ($checking_quantity as $key => $product) {
                                if ($product['qty'] > $product['total']) {
                                    $this->content['message'] = Message::error('La cantidad de productos en la recepción no puede exceder el total de la orden.');
                                    $tx->rollback();
                                    $overload = true;
                                }
                            }*/
                            if (!$overload) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La recepción ha sido registrada exitosamente.');
                                $tx->commit();
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipmentDetail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la recepción.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
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

    public function getShipmentDetailByQr ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $purchaseOrder = ProductionOrders::findFirst($id);
                $request = $this->request->getPut();
                if ($purchaseOrder) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/products/';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $fileName = 'lastQr.' . $file->getExtension();
                        if (file_exists($upload_dir . $fileName)) {
                            @unlink($upload_dir . $fileName);
                        }
                        $file->moveTo($upload_dir . $fileName);
                        $qrcode = new QrReader($upload_dir . $fileName);
                        $jumboId = intval($qrcode->text());
                        if ($jumboId) {
                            $sql = "SELECT sd.id AS value, sd.product_id, p.name AS product, sd.qty, sd.product_shipment_number, CONCAT('Saco ', sd.id, ' (', sd.qty, 'Kg.) [', s.receive_date, ']') AS label, CASE WHEN s1.movements_qty > 0 THEN TRUE ELSE FALSE END AS registered
                                    FROM pur_shipment_details AS sd
                                    INNER JOIN pur_shipments AS s
                                    ON sd.shipment_id = s.id
                                    INNER JOIN wms_products AS p
                                    ON p.id = sd.product_id
                                    LEFT JOIN (
                                        SELECT md.bag_id, COUNT(*) AS movements_qty
                                        FROM wms_movement_details AS md
                                        INNER JOIN wms_movements AS m
                                        ON md.movement_id = m.id
                                        WHERE m.status = 1
                                        AND md.bag_id = ".$jumboId."
                                        GROUP BY md.bag_id
                                    ) AS s1
                                    ON s1.bag_id = sd.id
                                    WHERE sd.id = ".$jumboId.";";
                            $jumbo = $this->db->query($sql)->fetch();
                            if ($jumbo) {
                                $this->content['jumbo'] = $jumbo;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El QR ha sido decodificado exitosamente.');
                            } else {
                                $this->content['result'] = false;
                                $this->content['message'] = Message::success('La recepción no ha sido encontrada.');
                            }
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::success('El QR recibido no es válido.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el producto.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipmentDetail = ShipmentDetails::findFirst($id);

                $request = $this->request->getPut();

                $product = Products::findFirst($shipmentDetail->product_id);

                if ($product->active) {
                    if ($shipmentDetail) {
                        $shipmentDetail->setTransaction($tx);
                        if (isset($request['shipment_id']) && is_numeric($request['shipment_id'])) {
                            $shipmentDetail->shipment_id = $request['shipment_id'];
                        }
                        if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                            $shipmentDetail->product_id = $request['product_id'];
                        }
                        if (isset($request['unit_id']) && is_numeric($request['unit_id'])) {
                            $shipmentDetail->unit_id = $request['unit_id'];
                        }
                        if (isset($request['qty']) && is_numeric($request['qty'])) {
                            $shipmentDetail->qty = $request['qty'];
                        }
                        if (isset($request['observation'])) {
                            $shipmentDetail->observation = $request['observation'];
                        }
                        if ($shipmentDetail->update()) {
                            $this->content['shipmentDetail'] = $shipmentDetail;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La recepción ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipment);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la recepción.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('El producto está inactivo.');
                }
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

                $shipmentDetail = ShipmentDetails::findFirst($id);

                if ($shipmentDetail) {
                    $shipment = Shipments::findFirst($shipmentDetail->shipment_id);
                    if ($shipment->status == 'NUEVO' || $shipment->status == 'ANALIZADO') {
                        $affectedShipmentDetails = ShipmentDetails::find(["id > $id AND shipment_id = $shipment->id", 'order' => 'id ASC']);
                        $shipmentDetail->setTransaction($tx);
                        $productId = $shipmentDetail->product_id;
                        $productShipmentNumber = $shipmentDetail->product_shipment_number;

                        if ($shipmentDetail->delete()) {
                            if ($tx->commit()) {
                                foreach ($affectedShipmentDetails as $detail) {
                                    $sql = "UPDATE pur_shipment_details SET id = $id";
                                    if ($productId == $detail->product_id) {
                                        $sql .= ", product_shipment_number = $productShipmentNumber";
                                    }
                                    $sql .= " WHERE id = $detail->id;";
                                    $this->content['sql'] = $sql;
                                    $id++;
                                    $productShipmentNumber++;
                                    $this->db->query($sql)->execute();
                                }
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El detalle de recepción ha sido eliminado.');
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar confirmar la transacción.');
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipmentDetail);
                            if ($this->content['error'][0]) {
                                $this->content['message'] = Message::error($this->content['error'][0]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la recepción.');
                            }
                        }
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

    public function getPdfQr ($shipmentDetailId) {
        if (is_numeric($shipmentDetailId)) {
            $sql = "SELECT CONCAT(c.code,'-',l.code,'-',p.code) AS product_code, ship.id AS shipment_id, ship.serial AS shipment, p.name AS product, det.id AS bag, det.qty, o.serial AS purchase_order, TO_CHAR(ship.receive_date :: DATE, 'dd/mm/yyyy') AS receive_date, TO_CHAR(ship.receive_time :: TIME, 'HH24:MI') AS receive_time
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
                    WHERE det.id = $shipmentDetailId;";
            $detail = $this->db->query($sql)->fetch();

            if (!$detail) {
                return null;
            }

            $pdf = new PDFTableQr('L','in',array(4,6));
            $pdf->SetLineWidth(0.05);
            $pdf->SetDrawColor(217, 217, 217);
            $pdf->SetTextColor(4, 26, 131);
            $pdf->SetFont('Arial','',5);
            $pdf->AddPage();
            $pdf->SetTitle('QR Jumbo '.$detail['bag'],true);
            $pdf->SetAutoPageBreak(false);
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
            $pdf->MultiCell(3.5, 0.3, $detail['receive_date'].' '.$detail['receive_time'], 1, 'C', false);

            $pdf->Output('I', 'QR Jumbo '.$detail['bag'].'.pdf', true);

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="QR Jumbo '.$detail['bag'].'.pdf"');
            return $response;
        }
        return null;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 22)
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
