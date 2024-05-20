<?php

use Phalcon\Mvc\Controller;

class SamplingsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getSamplings ()
    {
        if ($this->userHasPermission()) {
            $this->content['samplings'] = Samplings::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getSamplingsByShipmentId ($shipmentId)
    {
        if (!is_null($shipmentId)) {
            if ($this->userHasPermission()) {
                $sql = "SELECT s.*, p.name AS product
                        FROM pur_samplings AS s
                        INNER JOIN wms_products AS p
                        ON p.id = s.product_id
                        WHERE s.shipment_id = $shipmentId
                        ORDER BY serial ASC;";
                $samplings = $this->db->query($sql)->fetchAll();
                $this->content['samplings'] = $samplings;
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
            $this->response->setJsonContent($this->content);
        } else {
            $this->content['samplings'] = [];
            $this->content['result'] = false;
            $this->response->setJsonContent($this->content);
        }
    }

    public function getSampling ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['sampling'] = Samplings::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, serial FROM pur_samplings ORDER BY serial ASC;";
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

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();
                
                $product = Products::findFirst($request['product_id']);
                if ($product->active) {
                    $sql = "SELECT serial FROM pur_samplings ORDER BY serial DESC LIMIT 1;";
                    $serial = $this->db->query($sql)->fetch();
                    $serial = isset($serial['serial']) ? ($serial['serial'] + 1) : 1;

                    /* if ((isset($request['pvc']) && $request['pvc'] > 100) || (isset($request['recicled']) && $request['recicled'] > 5000) || (isset($request['dirty']) && $request['dirty'] > 200) || (isset($request['humidity']) && $request['humidity'] > 1) || (isset($request['metals']) && $request['metals'] > 20) || (isset($request['sifting']) && $request['sifting'] > 2)) {
                        $this->content['message'] = Message::error('Los valores sobrepasan el valor máximo.');
                    } else { */
                        $sampling = new Samplings();
                        $sampling->setTransaction($tx);
                        $sampling->shipment_id = $request['shipment_id'];
                        $sampling->serial = $serial;
                        $sampling->status = 'NUEVO';
                        $sampling->product_id = $request['product_id'];
                        $sampling->humidity = $request['humidity'];
                        $sampling->dirty = $request['dirty'];
                        $sampling->metals = $request['metals'];
                        $sampling->recicled = $request['recicled'];
                        $sampling->pvc = $request['pvc'];
                        $sampling->recommendations = strtoupper($request['recommendations']);
                        $sampling->sifting = $request['sifting'];

                        if ($sampling->create()) {
                            $shipment = Shipments::findFirst($sampling->shipment_id);
                            if ($shipment->status == 'NUEVO') {
                                $shipment->status = 'MUESTREADO';
                                if ($shipment->update()) {
                                    $this->content['sampling'] = $sampling;
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('La muestra ha sido creada.');
                                    $tx->commit();
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la muestra.');
                                    $tx->rollback();
                                }
                            } elseif ($shipment->status == 'MUESTREADO') {
                                $this->content['sampling'] = $sampling;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La muestra ha sido creada.');
                                $tx->commit();
                            } else {
                                $this->content['result'] = false;
                                $this->content['message'] = Message::success('Ya no se pueden agregar muestras al embarque.');
                            }
                            
                        } else {
                            $this->content['error'] = Helpers::getErrors($sampling);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la muestra.');
                            $tx->rollback();
                        }
                    // }
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $sampling = Samplings::findFirst($id);

                $request = $this->request->getPut();

                if ($sampling) {
                    $product = Products::findFirst($request['product_id']);
                    if ($product->active) {
                        /* if ((isset($request['pvc']) && $request['pvc'] > 100) || (isset($request['recicled']) && $request['recicled'] > 5000) || (isset($request['dirty']) && $request['dirty'] > 200) || (isset($request['humidity']) && $request['humidity'] > 1) || (isset($request['metals']) && $request['metals'] > 20) || (isset($request['sifting']) && $request['sifting'] > 2)) {
                            $this->content['message'] = Message::error('Los valores sobrepasan el valor máximo.');
                        } else { */
                            $sampling->setTransaction($tx);
                            $sampling->shipment_id = $request['shipment_id'];
                            $sampling->product_id = $request['product_id'];
                            $sampling->humidity = $request['humidity'];
                            $sampling->dirty = $request['dirty'];
                            $sampling->metals = $request['metals'];
                            $sampling->recicled = $request['recicled'];
                            $sampling->pvc = $request['pvc'];
                            $sampling->recommendations = strtoupper($request['recommendations']);
                            $sampling->sifting = $request['sifting'];

                            if ($sampling->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La muestra ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($sampling);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la muestra.');
                                $tx->rollback();
                            }
                        // }
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

    public function delete ($id)
    {
         try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $sampling = Samplings::findFirst($id);

                if ($sampling) {
                    $sampling->setTransaction($tx);

                    if ($sampling->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La muestra ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($sampling);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la muestra.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La muestra no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getPdf ($id)
    {
        if (is_numeric($id)) {
            $sql = "SELECT s.id, s.shipment_id, s.serial, s.status, TO_CHAR(s.created, 'dd/mm/yyyy') AS date, sup.name AS supplier, p.name AS product, ship.total_weight, s.humidity, s.dirty, s.metals, s.recicled, s.pvc, p.photo, s.recommendations, s.sifting, o.serial AS order_serial
                    FROM pur_samplings AS s
                    INNER JOIN wms_products AS p
                    ON s.product_id = p.id
                    INNER JOIN pur_shipments AS ship
                    ON s.shipment_id = ship.id
                    INNER JOIN pur_orders AS o
                    ON ship.order_id = o.id
                    INNER JOIN pur_suppliers AS sup
                    ON o.supplier_id = sup.id
                    WHERE s.id = $id;";
            $sampling = $this->db->query($sql)->fetch();
            $standardHumidity = 0.6;
            $standardMetals = 50;
            $standardRecicled = 5000;
            $standardPvc = 100;
            $standardDirty = 250;
            $standardSifting = 0.5;

            $pdf = new FPDF('P','mm','Letter');
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(false);
            $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
            $img = $path . 'logo.png';
            $pdf->Image($img,95,8,35,0,'PNG');
            $pdf->SetTextColor(0, 108, 137);
            $pdf->SetFont('Arial','',16);
            $pdf->SetY($pdf->GetY()+40);
            $pdf->Cell(0,0,utf8_decode('RESULTADO DE LABORATORIO'));
            $pdf->SetX($pdf->GetX()-53);
            $pdf->Cell(0,0,'FECHA: '.$sampling['date']);
            $pdf->Line(10, 55, 205, 55);
            if ($sampling['photo'] && file_exists($path . 'products/' . $sampling['photo'])) {
                $pdf->Image($path . 'products/' . $sampling['photo'],10,60,60,60);
                $pdf->SetY($pdf->GetY()+60);
            }
            $pdf->SetY($pdf->GetY()+15);
            $pdf->SetTextColor(0);
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(0,0,utf8_decode('OC: '.$sampling['order_serial']));
            $pdf->SetY($pdf->GetY()+6);
            $pdf->Cell(0,0,utf8_decode('PROVEEDOR: '.$sampling['supplier']));
            $pdf->SetY($pdf->GetY()+6);
            $pdf->Cell(0,0,utf8_decode('PRODUCTO: '.$sampling['product']));
            $pdf->SetY($pdf->GetY()+6);
            $pdf->Cell(0,0,'PESO: '.number_format($sampling['total_weight']).' KG.');

            $pdf->SetY($pdf->GetY()+6);
            $pdf->SetFillColor(255);
            $pdf->SetTextColor(0);
            $pdf->Cell(90, 6, utf8_decode('Descripción'), '0', 0, 'L', true);
            $pdf->Cell(35, 6, 'Resultado', '0', 0, 'C', true);
            $pdf->Cell(35, 6, 'Objetivo', '0', 0, 'C', true);
            $pdf->Cell(35, 6, 'Diferencia', '0', 0, 'C', true);
            $pdf->SetFillColor(244,244,244);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Humedad', 'T', 0, 'J', false);
            $pdf->Cell(35, 6, ($sampling['humidity'] ? number_format($sampling['humidity']).'%' : '0%'), 'TLR', 0, 'C', false);
            $pdf->Cell(35, 6, ($standardHumidity != null ? number_format($standardHumidity).'%' : '0%'), 'TLR', 0, 'C', false);
            if (($sampling['humidity']-$standardHumidity) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['humidity']-$standardHumidity).'%', 'TL', 0, 'C', false);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Metales', '0', 0, 'J', false);
            $pdf->Cell(35, 6, number_format($sampling['metals']), 'LR', 0, 'C', true);
            $pdf->Cell(35, 6, number_format($standardMetals), 'LR', 0, 'C', true);
            if (($sampling['metals']-$standardMetals) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['metals']-$standardMetals), 'L', 0, 'C', true);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Degradado', '0', 0, 'J', false);
            $pdf->Cell(35, 6, number_format($sampling['recicled']), 'LR', 0, 'C', false);
            $pdf->Cell(35, 6, number_format($standardRecicled), 'LR', 0, 'C', false);
            if (($sampling['recicled']-$standardRecicled) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['recicled']-$standardRecicled), 'L', 0, 'C', false);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'PVC', '0', 0, 'J', false);
            $pdf->Cell(35, 6, number_format($sampling['pvc']), 'LR', 0, 'C', true);
            $pdf->Cell(35, 6, number_format($standardPvc), 'LR', 0, 'C', true);
            if (($sampling['pvc']-$standardPvc) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['pvc']-$standardPvc), 'L', 0, 'C', true);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Otros', '0', 0, 'J', false);
            $pdf->Cell(35, 6, number_format($sampling['dirty']), 'LR', 0, 'C', false);
            $pdf->Cell(35, 6, number_format($standardDirty), 'LR', 0, 'C', false);
            if (($sampling['dirty']-$standardDirty) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['dirty']-$standardDirty), 'L', 0, 'C', false);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Tamizado', '0', 0, 'J', false);
            $pdf->Cell(35, 6, ($sampling['sifting'] ? number_format($sampling['sifting']).'%' : '0%'), 'LR', 0, 'C', true);
            $pdf->Cell(35, 6, number_format($standardSifting).'%', 'LR', 0, 'C', true);
            if (($sampling['sifting']-$standardSifting) > 0) {
                $pdf->SetTextColor(255, 0, 0);
            } else {
                $pdf->SetTextColor(0, 255, 0);
            }
            $pdf->Cell(35, 6, number_format($sampling['sifting']-$standardSifting).'%', 'L', 0, 'C', true);
            $pdf->SetTextColor(0);
            $pdf->Ln();
            $pdf->Cell(90, 6, 'Total', 'T', 0, 'J', false);
            $pdf->Cell(35, 6, '', 'T', 0, 'C', false);
            $pdf->Cell(35, 6, '', 'T', 0, 'C', false);
            $pdf->Cell(35, 6, '', 'T', 0, 'C', false);

            $pdf->SetY($pdf->GetY()+10);
            $pdf->Cell(0,6,'FECHA: '.$sampling['date']);
            $pdf->Ln();
            $pdf->Cell(0,6,'Resultados de '.'JEFE DE LABORATORIO');
            $pdf->Ln(12);
            $pdf->Cell(0,6,'RECOMENDACIONES:');
            $pdf->Ln();
            $pdf->MultiCell(0, 6, $sampling['recommendations'], 0, 'L', false);

            $pdf->Ln(25);
            $pdf->Cell(0,6,utf8_decode('Daniel Aguilar García'),0,0,'C');
            $pdf->Ln();
            $pdf->Cell(0,6,utf8_decode('Jefe de Laboratorio'),0,0,'C');
            $pdf->Ln();
            $pdf->Cell(0,6,utf8_decode('TECHNOFIBERS'),0,0,'C');

            $pdf->SetTitle('Muestra Technofibers',true);

            $pdf->Output('I', 'muestra.pdf', true);

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="muestra.pdf"');
            return $response;
        }
        return false;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 5 OR role_id = 3)
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
