<?php

use Phalcon\Mvc\Controller;

class LaminatesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getLaminates ($pt = 0)
    {
        // $content = $this->content;
        $sql = "SELECT l.id, TO_CHAR(l.scheduled_date, 'yyyy/mm/dd') AS scheduled_date, l.product_id, p.name AS product, md.qty AS weight, s.branch_office_id, bo.name AS branch_office, m.storage_id, s.name AS storage, l.operator_id, o.name AS operator, l.status
                FROM prd_laminates AS l
                INNER JOIN wms_products AS p
                ON p.id = l.product_id
                INNER JOIN wms_movements AS m
                ON m.id = l.laminate_movement_id
                LEFT JOIN wms_movement_details AS md
                ON md.movement_id = m.id
                INNER JOIN wms_storages AS s
                ON s.id = m.storage_id
                INNER JOIN wms_branch_offices AS bo
                ON bo.id = s.branch_office_id
                LEFT JOIN wms_operators AS o
                ON o.id = l.operator_id
                ORDER BY l.scheduled_date DESC;";
        // $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        // $content['laminates'] = $data->fetchAll();
        $laminates = $this->db->query($sql)->fetchAll();
        $this->content['laminates'] = $laminates;
        $this->content['result'] = true;

        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getLaminate ($id)
    {
        $content = $this->content;
        $transaction = null;
        if (is_numeric($id)) {
            $sql = "SELECT l.id, TO_CHAR(l.scheduled_date, 'yyyy/mm/dd') AS scheduled_date, l.product_id, p.name AS product, s.branch_office_id, bo.name AS branch_office, m.storage_id, s.name AS storage, l.operator_id, o.name AS operator, l.status
                    FROM prd_laminates AS l
                    INNER JOIN wms_products AS p
                    ON p.id = l.product_id
                    INNER JOIN wms_movements AS m
                    ON m.id = l.laminate_movement_id
                    INNER JOIN wms_storages AS s
                    ON s.id = m.storage_id
                    INNER JOIN wms_branch_offices AS bo
                    ON bo.id = s.branch_office_id
                    LEFT JOIN wms_operators AS o
                    ON o.id = l.operator_id
                    WHERE l.id = $id;";
            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $transaction = $data->fetch();
        }
        $content['laminate'] = $transaction;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getLaminatesByOperator ($operatorId = null, $startDate = null, $endingDate = null, $status = null)
    {
        $laminates = [];
        $sql = "SELECT l.id, TO_CHAR(l.scheduled_date, 'yyyy/mm/dd') AS scheduled_date, l.product_id, p.name AS product_name, SUM(mmd.qty + amd.qty) AS qty, l.operator_id, o.name AS operator_name, l.status
                FROM prd_laminates AS l
                INNER JOIN wms_products AS p
                ON p.id = l.product_id
                INNER JOIN wms_movement_details AS mmd
                ON mmd.movement_id = l.material_movement_id
                INNER JOIN wms_movement_details AS amd
                ON amd.movement_id = l.additive_movement_id
                INNER JOIN wms_operators AS o
                ON o.id = l.operator_id
                WHERE l.id IS NOT NULL";
        if (!is_null($operatorId) && strcasecmp($operatorId, 'NULL') != 0) {
            $sql .= " AND l.operator_id = $operatorId";
        }
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND l.scheduled_date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND l.scheduled_date <= '".$eDate."'";
        }
        if (!is_null($status) && strcasecmp($status, 'NULL') != 0) {
            $sql .= " AND l.status = '".$status."'";
        }
        $sql .= ' GROUP BY (l.id, l.scheduled_date, l.product_id, p.name, l.operator_id, o.name, l.status) ORDER BY l.id ASC;';
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $laminates = $data->fetchAll();
        $this->content['result'] = true;
        $this->content['laminates'] = $laminates;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getPdfLaminatesByOperator ($operatorId = null, $startDate = null, $endingDate = null, $status = null)
    {
        $laminates = [];
        if (is_numeric($operatorId)) {
            $operator = Operators::findFirst($operatorId);
        }
        $sql = "SELECT l.id, TO_CHAR(l.scheduled_date, 'yyyy/mm/dd') AS scheduled_date, l.product_id, p.name AS product_name, SUM(mmd.qty + amd.qty) AS qty, l.operator_id, o.name AS operator_name, l.status
                FROM prd_laminates AS l
                INNER JOIN wms_products AS p
                ON p.id = l.product_id
                INNER JOIN wms_movement_details AS mmd
                ON mmd.movement_id = l.material_movement_id
                INNER JOIN wms_movement_details AS amd
                ON amd.movement_id = l.additive_movement_id
                INNER JOIN wms_operators AS o
                ON o.id = l.operator_id
                WHERE l.id IS NOT NULL";
        if (!is_null($operatorId) && strcasecmp($operatorId, 'NULL') != 0) {
            $sql .= " AND l.operator_id = $operatorId";
        }
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND l.scheduled_date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND l.scheduled_date <= '".$eDate."'";
        }
        if (!is_null($status) && strcasecmp($status, 'NULL') != 0) {
            $sql .= " AND l.status = '".$status."'";
        }
        $sql .= ' GROUP BY (l.id, l.scheduled_date, l.product_id, p.name, l.operator_id, o.name, l.status) ORDER BY l.id ASC;';
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $laminates = $data->fetchAll();
        $operatorName = isset($operator) ? $operator->name : 'TODOS';
        $pdf = new PDFLaminatesByOperator('P','mm','Letter');
        $pdf->AliasNbPages();
        $pdf->SetOperator($operatorName);
        $pdf->SetStatus($status);
        $pdf->SetStartDate($startDate);
        $pdf->SetEndingDate($endingDate);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTextColor(4, 26, 131);
        $pdf->SetFillColor(218, 221, 238);
        $pdf->SetFont('Arial','',10);
        $pdf->SetDrawColor(4, 26, 131);
        $pdf->Ln();
        if (count($laminates) > 0) {
            $pdf->SetFont('Arial','',8);
            $pdf->SetWidths(array(20, 35, 45, 45, 25, 25));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(false);
            $pdf->SetFill(array(true, true, true, true, true, true));
            $pdf->Row(array('# LAMINADO', 'FECHA PROGRAMADA', 'OPERADOR', 'PRODUCTO', 'CANTIDAD', 'ESTATUS'));
            $pdf->SetAligns(array('C', 'C', 'L', 'L', 'R', 'C'));
            $fill = false;
            $totalQty = 0;
            foreach ($laminates as $laminate) {
                $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill));
                $pdf->Row(array($laminate['id'], $laminate['scheduled_date'], utf8_decode($laminate['operator_name']), utf8_decode($laminate['product_name']), number_format($laminate['qty'], 2, '.', ',').' KG.', $laminate['status']));
                $fill = !$fill;
                $totalQty += $laminate['qty'];
            }
            $pdf->SetAligns(array('C', 'C', 'C', 'R', 'R', 'C'));
            $pdf->SetFill(array(false, false, false, true, true, false));
            $pdf->Row(array(NULL, NULL, NULL, 'TOTAL', number_format($totalQty, 2, '.', ',').' KG.', NULL));
        }

        $pdf->SetTitle("Reporte de laminados del operador $operatorName",true);
        $pdf->Output('I', "Reporte de laminados del operador $operatorName.pdf", true);
        $response = new Phalcon\Http\Response();
        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="Reporte de laminados del operador '.$operatorName.'.pdf"');
        return $response;
    }

    public function getLaminatesByTransactionId ($transactionId)
    {
        if (is_numeric($transactionId)) {
            $sql = "SELECT md.id, md.product_id, p.name AS product, md.qty,l.code as line_code,c.code as category_code, concat(c.code,'-',l.code,'-',p.name) as name_product
                    FROM wms_movement_details AS md
                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                    INNER JOIN wms_products AS p ON p.id = md.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    WHERE l.category_id = 5
                    AND m.type = 2
                    AND m.transaction_id = $transactionId
                    ORDER BY md.id ASC;";
            $laminates = $this->db->query($sql)->fetchAll();
            $this->content['laminates'] = $laminates;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de transacción válido.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ($pt = 0)
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser) {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();

            if (isset($request['scheduledDate']) && isset($request['productId']) && is_numeric($request['productId']) && isset($request['branchOfficeId']) && is_numeric($request['branchOfficeId'])) {
                $product = Products::findFirst($request['productId']);
                if ($product && $product->active) {
                    $laminateStorage = Storages::findFirst('branch_office_id = '.$request['branchOfficeId'].' AND storage_type_id = 8');
                    if ($laminateStorage) {
                        $materialStorage = Storages::findFirst('branch_office_id = '.$request['branchOfficeId'].' AND storage_type_id = 1');
                        if ($materialStorage) {
                            $additiveStorage = Storages::findFirst('branch_office_id = '.$request['branchOfficeId'].' AND storage_type_id = 9');
                            if ($additiveStorage) {
                                $materialMovement = new Movements();
                                $materialMovement->storage_id = $materialStorage->id;
                                $materialMovement->type = 2;
                                if ($materialMovement->create()) {
                                    $additiveMovement = new Movements();
                                    $additiveMovement->storage_id = $additiveStorage->id;
                                    $additiveMovement->type = 2;
                                    if ($additiveMovement->create()) {
                                        $laminateMovement = new Movements();
                                        $laminateMovement->storage_id = $laminateStorage->id;
                                        $laminateMovement->type = 1;
                                        if ($laminateMovement->create()) {
                                            $laminate = new Laminates();
                                            $laminate->setTransaction($tx);
                                            $laminate->account_id = Auth::getUserAccount($validUser->id);
                                            $laminate->scheduled_date = $request['scheduledDate'];
                                            $laminate->product_id = $request['productId'];
                                            $laminate->material_movement_id = $materialMovement->id;
                                            $laminate->additive_movement_id = $additiveMovement->id;
                                            $laminate->laminate_movement_id = $laminateMovement->id;
                                            if ($laminate->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Laminado registrado correctamente.');
                                                $this->content['laminate'] = $laminate;
                                                $tx->commit();
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($laminate);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el laminado.');
                                                $tx->rollback();
                                            }
                                        }
                                    }
                                }
                            } else {
                                $this->content['message'] = Message::error('La sucursal seleccionada no cuenta con un almacén de Materia prima.');
                            }
                        } else {
                            $this->content['message'] = Message::error('La sucursal seleccionada no cuenta con un almacén de Fibra Paca.');
                        }
                    } else {
                        $this->content['message'] = Message::error('La sucursal seleccionada no cuenta con un almacén de Laminado.');
                    }
                } else {
                    $this->content['message'] = Message::error('El producto está inactivo.');
                }
            } else {
                $this->content['message'] = Message::error('No se han recibido los datos necesarios para el registro del laminado.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function produce ($id)
    {
        try {
            $request = $this->request->getPut();
            $product = Products::findFirst(121);
            if ($product && $product->active) {
                if (isset($request['operatorId']) && is_numeric($request['operatorId'])) {
                    if (is_numeric($id)) {
                        $request = $this->request->getPut();
                        $tx = $this->transactions->get();
                        $laminate = Laminates::findFirst($id);
                        if ($laminate) {
                            if ($laminate->status == 'NUEVO') {
                                $materialMovement = Movements::findFirst($laminate->material_movement_id);
                                if ($materialMovement) {
                                    if ($materialMovement->status == 0) {
                                        $materialMovementDetails = MovementDetails::find("movement_id = $materialMovement->id");
                                        $totalQty = 0;
                                        if (count($materialMovementDetails) > 0) {
                                            $balesNotAvailable = '';
                                            foreach ($materialMovementDetails as $detail) {
                                                $totalQty += $detail->qty;
                                                $sql = "SELECT md.bale_id, md.product_id, md.qty, m.date
                                                        FROM wms_movement_details AS md
                                                        INNER JOIN wms_movements AS m
                                                        ON m.id = md.movement_id
                                                        WHERE m.status = 1
                                                        AND m.type = 1
                                                        AND md.bale_id = $detail->bale_id
                                                        AND m.storage_id = $materialMovement->storage_id
                                                        ORDER BY m.date DESC
                                                        LIMIT 1;";
                                                $baleEntryMovement = $this->db->query($sql)->fetch();
                                                if ($baleEntryMovement) {
                                                    $sql = "SELECT bale_id, product_id, qty, date
                                                            FROM (
                                                                SELECT md.bale_id, md.product_id, md.qty, m.date
                                                                FROM wms_movement_details AS md
                                                                INNER JOIN wms_movements AS m
                                                                ON m.id = md.movement_id
                                                                WHERE m.status = 1
                                                                AND m.type = 2
                                                                AND md.bale_id = $detail->bale_id
                                                                AND m.storage_id = $materialMovement->storage_id
                                                                AND m.date >= '".$baleEntryMovement['date']."'
                                                            ) AS sub
                                                            ORDER BY date DESC
                                                            LIMIT 1;";
                                                    $baleExitMovement = $this->db->query($sql)->fetch();
                                                    if ($baleExitMovement) {
                                                        $balesNotAvailable .= (strlen($balesNotAvailable) == 0 ? $detail->bale_id : "; $detail->bale_id");
                                                    }
                                                } else {
                                                    $balesNotAvailable .= (strlen($balesNotAvailable) == 0 ? $detail->bale_id : "; $detail->bale_id");
                                                }
                                            }
                                            if (strlen($balesNotAvailable) == 0) {
                                                $additiveMovement = Movements::findFirst($laminate->additive_movement_id);
                                                if ($additiveMovement && $additiveMovement->storage_id) {
                                                    $sql = "SELECT sum(s1.qty) AS stock
                                                            FROM (SELECT md.id, md.product_id, m.date, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN md.qty * -1 END AS qty
                                                            FROM wms_movements AS m
                                                            INNER JOIN wms_movement_details AS md
                                                            ON m.id = md.movement_id
                                                            WHERE m.status = 1
                                                            AND md.product_id = 121
                                                            AND m.storage_id = $additiveMovement->storage_id
                                                            ORDER BY m.date ASC) AS s1;";
                                                    $additiveStock = $this->db->query($sql)->fetch();
                                                    if ($additiveStock) {
                                                        if ($additiveStock['stock'] > ($totalQty * 0.1)) {
                                                            $additiveMovementDetail = new MovementDetails();
                                                            $additiveMovementDetail->movement_id = $additiveMovement->id;
                                                            $additiveMovementDetail->product_id = 121;
                                                            $additiveMovementDetail->qty = ($totalQty * 0.1);
                                                            if ($additiveMovementDetail->create()) {
                                                                $materialMovement->setTransaction($tx);
                                                                $materialMovement->status = 1;
                                                                if ($materialMovement->update()) {
                                                                    $additiveMovement->setTransaction($tx);
                                                                    $additiveMovement->status = 1;
                                                                    if ($additiveMovement->update()) {
                                                                        $laminate->setTransaction($tx);
                                                                        $laminate->status = 'PRODUCIENDO';
                                                                        $laminate->operator_id = $request['operatorId'];
                                                                        if ($laminate->update()) {
                                                                            $this->content['result'] = true;
                                                                            $this->content['message'] = Message::success('El laminado ha sido cambiado su estatus a Produciendo.');
                                                                            $tx->commit();
                                                                        } else {
                                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar cambiar el estatus del laminado.');
                                                                            $tx->rollback();
                                                                        }
                                                                    } else {
                                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                                                        $tx->rollback();
                                                                    }
                                                                } else {
                                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                                                    $tx->rollback();
                                                                }
                                                            } else {
                                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el aditivo.');
                                                            }
                                                        } else {
                                                            $this->content['message'] = Message::error('No se ha cuenta con la cantidad necesaria de aditivo disponible.');
                                                        }
                                                    } else {
                                                        $this->content['message'] = Message::error('No se ha cuenta con la cantidad necesaria de aditivo disponible.');
                                                    }
                                                } else {
                                                    $this->content['message'] = Message::error('No se ha encontrado el almacén de Aditivos para la sucursal.');
                                                }
                                            } else {
                                                $this->content['message'] = Message::error("No se puede producir el laminado debido a que las siguientes pacas no están disponibles: $balesNotAvailable.");
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se puede producir el laminado porque no tiene materiales.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se puede producir el laminado.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se puede producir el laminado.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se puede producir el laminado.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado el laminado.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha recibido un id válido.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha recibido un el id de operador.');
                }
            } else {
                $this->content['message'] = Message::error('El aditivo Bicomponente está inactivo.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function finish ($id)
    {
        try {
            if (is_numeric($id)) {
                $request = $this->request->getPut();
                $tx = $this->transactions->get();
                $laminate = Laminates::findFirst($id);
                if ($laminate) {
                    if ($laminate->status == 'PRODUCIENDO') {
                        $laminateMovement = Movements::findFirst($laminate->laminate_movement_id);
                        if ($laminateMovement->status == 0) {
                            $totalQty = 0;
                            $materialMovementDetails = MovementDetails::find("movement_id = $laminate->material_movement_id");
                            $additiveMovementDetails = MovementDetails::find("movement_id = $laminate->additive_movement_id");
                            foreach ($materialMovementDetails as $detail) {
                                $totalQty += $detail->qty;
                            }
                            foreach ($additiveMovementDetails as $detail) {
                                $totalQty += $detail->qty;
                            }
                            $laminateMovementDetail = new MovementDetails();
                            $laminateMovementDetail->movement_id = $laminateMovement->id;
                            $laminateMovementDetail->product_id = $laminate->product_id;
                            $laminateMovementDetail->qty = $totalQty;
                            if ($laminateMovementDetail->create()) {
                                $laminateMovement->setTransaction($tx);
                                $laminateMovement->status = 1;
                                if ($laminateMovement->update()) {
                                    $laminate->setTransaction($tx);
                                    $laminate->status = 'TERMINADO';
                                    if ($laminate->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El laminado ha sido cambiado su estatus a Terminado.');
                                        $tx->commit();
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar terminar el laminado.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar terminar el laminado.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar terminar el laminado.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede terminar el laminado.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede terminar el laminado.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el laminado.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id válido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            $tx = $this->transactions->get();
            if (is_numeric($id)) {
                $laminate = Laminates::findFirst($id);
                if ($laminate) {
                    $laminate->setTransaction($tx);
                    if ($laminate->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El laminado ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($laminate);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el laminado.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el laminado.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id válido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}

class PDFLaminatesByOperator extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $operator;
    var $status;
    var $startDate;
    var $endingDate;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo_name.png';
        $this->Image($img,5,5,75,0,'PNG');
        $this->SetTextColor(4, 26, 131);
        $this->SetFont('Arial','',12);
        $this->SetY($this->GetY()+20);
        $this->MultiCell(195, 6, utf8_decode("Reporte de Laminados del operador $this->operator con estatus $this->status desde $this->startDate hasta $this->endingDate"), 0, 'J', false);
        $this->SetY($this->GetY());
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

    function SetOperator($o)
    {
        $this->operator = $o;
    }

    function SetStatus($s)
    {
        $this->status = (!is_null($s) && strcasecmp($s, 'NULL') != 0) ? $s : 'PRODUCIENDO Y TERMINADO';
    }

    function SetStartDate($sd)
    {
        $this->startDate = (!is_null($sd) && strcasecmp($sd, 'NULL') != 0) ? $sd : 'EL PRIMER LAMINADO';
    }

    function SetEndingDate($ed)
    {
        $this->endingDate = (!is_null($ed) && strcasecmp($ed, 'NULL') != 0) ? $ed : 'EL ÚLTIMO LAMINADO';
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