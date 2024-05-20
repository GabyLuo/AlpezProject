<?php

use Phalcon\Mvc\Controller;

class BranchTransfersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBranchTransfers ($pt = 0)
    {
       // $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT bt.id, bt.transaction_id, om.id AS origin_movement_id, ob.id AS origin_branch_office_id, ob.name AS origin_branch_office_name, os.id AS origin_storage_id, os.name AS origin_storage_name, dm.id AS destination_movement_id, db.id AS destination_branch_office_id, db.name AS destination_branch_office_name, dm.status, dm.created AS date, ds.id AS destination_storage_id, ds.name AS destination_storage_name
                    FROM wms_branch_transfers AS bt
                    INNER JOIN wms_transactions AS t
                    ON t.id = bt.transaction_id
                    INNER JOIN wms_movements AS om
                    ON om.transaction_id = t.id AND om.type = 5
                    INNER JOIN wms_storages AS os
                    ON os.id = om.storage_id
                    INNER JOIN wms_branch_offices AS ob
                    ON ob.id = os.branch_office_id
                    INNER JOIN wms_movements AS dm
                    ON dm.transaction_id = t.id AND dm.type = 4
                    INNER JOIN wms_storages AS ds
                    ON ds.id = dm.storage_id
                    INNER JOIN wms_branch_offices AS db
                    ON db.id = ds.branch_office_id
                    ORDER BY bt.id ASC;";
            $branchTransfers = $this->db->query($sql)->fetchAll();
            $this->content['branchTransfers'] = $branchTransfers;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getBranchTransfer ($id)
    {
        $content = $this->content;
        // bt.operator_id, o.name AS operator_name
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $sql = "SELECT bt.id, bt.transaction_id, om.id AS origin_movement_id, ob.id AS origin_branch_office_id, ob.name AS origin_branch_office_name, os.id AS origin_storage_id, os.name AS origin_storage_name, dm.id AS destination_movement_id, db.id AS destination_branch_office_id, db.name AS destination_branch_office_name, dm.status, dm.created AS date, ds.id AS destination_storage_id, ds.name AS destination_storage_name, om.folio as origin_folio, dm.folio as destination_folio,TO_CHAR(om.date :: DATE, 'dd/mm/yyyy') as date
                        FROM wms_branch_transfers AS bt
                        INNER JOIN wms_transactions AS t
                        ON t.id = bt.transaction_id
                        -- INNER JOIN wms_operators AS o
                        -- ON o.id = bt.operator_id
                        INNER JOIN wms_movements AS om
                        ON om.transaction_id = t.id AND om.type_id = 5
                        INNER JOIN wms_storages AS os
                        ON os.id = om.storage_id
                        INNER JOIN wms_branch_offices AS ob
                        ON ob.id = os.branch_office_id
                        INNER JOIN wms_movements AS dm
                        ON dm.transaction_id = t.id AND dm.type_id = 4
                        INNER JOIN wms_storages AS ds
                        ON ds.id = dm.storage_id
                        INNER JOIN wms_branch_offices AS db
                        ON db.id = ds.branch_office_id
                        WHERE om.id = $id OR dm.id = $id;";
                        // WHERE bt.id = $id;";
                $data = $this->db->query($sql);
                $content['branchTransfer'] = $data->fetch();
            } else {
                $content['message'] = Message::error('No se ha recibido un id de traspaso válido.');
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getPdf ($id)
    {
        if (is_numeric($id)) {
            $branchTransfer = BranchTransfers::findFirst($id);
            if ($branchTransfer) {
                $sql = "SELECT m.id, s.branch_office_id, bo.name AS branch_office_name, bo.address AS branch_office_address, m.storage_id, s.name AS storage, m.type, m.transaction_id, TO_CHAR(m.date, 'yyyy/mm/dd') AS date
                        FROM wms_movements AS m
                        INNER JOIN wms_storages AS s
                        ON s.id = m.storage_id
                        INNER JOIN wms_branch_offices AS bo
                        ON bo.id = s.branch_office_id
                        WHERE m.status = 1
                        AND m.type = 2
                        AND m.transaction_id = $branchTransfer->transaction_id;";
                $exitMovement = $this->db->query($sql)->fetch();
                if ($exitMovement) {
                    $sql = "SELECT m.id, s.branch_office_id, bo.name AS branch_office_name, bo.address AS branch_office_address, m.storage_id, s.name AS storage, m.type, m.transaction_id, TO_CHAR(m.date, 'yyyy/mm/dd') AS date
                            FROM wms_movements AS m
                            INNER JOIN wms_storages AS s
                            ON s.id = m.storage_id
                            INNER JOIN wms_branch_offices AS bo
                            ON bo.id = s.branch_office_id
                            WHERE m.status = 1
                            AND m.type = 1
                            AND m.transaction_id = $branchTransfer->transaction_id;";
                    $entryMovement = $this->db->query($sql)->fetch();
                    if ($entryMovement) {
                        $sql = "SELECT md.id, md.product_id, c.code AS category_code, l.code AS line_code, p.name AS product_name, md.qty, md.bale_id
                                FROM wms_movement_details AS md
                                INNER JOIN wms_products AS p
                                ON p.id = md.product_id
                                INNER JOIN wms_lines AS l
                                ON l.id = p.line_id
                                INNER JOIN wms_categories AS c
                                ON c.id = l.category_id
                                WHERE md.movement_id = ".$entryMovement['id']."
                                AND md.bale_id IS NOT NULL;";
                        $baleDetails = $this->db->query($sql)->fetchAll();
                        $sql = "SELECT md.id, md.product_id, c.code AS category_code, l.code AS line_code, p.name AS product_name, md.qty
                                FROM wms_movement_details AS md
                                INNER JOIN wms_products AS p
                                ON p.id = md.product_id
                                INNER JOIN wms_lines AS l
                                ON l.id = p.line_id
                                INNER JOIN wms_categories AS c
                                ON c.id = l.category_id
                                WHERE md.movement_id = ".$entryMovement['id']."
                                AND md.bale_id IS NULL
                                AND l.category_id = 5;";
                        $laminateDetails = $this->db->query($sql)->fetchAll();
                        $sql = "SELECT md.id, md.product_id, c.code AS category_code, l.code AS line_code, p.name AS product_name, md.qty
                                FROM wms_movement_details AS md
                                INNER JOIN wms_products AS p
                                ON p.id = md.product_id
                                INNER JOIN wms_lines AS l
                                ON l.id = p.line_id
                                INNER JOIN wms_categories AS c
                                ON c.id = l.category_id
                                WHERE md.movement_id = ".$entryMovement['id']."
                                AND md.bale_id IS NULL
                                AND l.category_id = 14;";
                        $rawMaterialDetails = $this->db->query($sql)->fetchAll();

                        $pdf = new PDFBranchTransfer('P','mm','Letter');
                        $pdf->AliasNbPages();
                        $pdf->SetBranchTransferId($branchTransfer->id);
                        $pdf->SetDate($exitMovement['date']);
                        $pdf->AddPage();
                        $pdf->SetAutoPageBreak(false);
                        $pdf->SetTextColor(4, 26, 131);
                        $pdf->SetFillColor(218, 221, 238);
                        $pdf->SetFont('Arial','B',10);
                        $pdf->SetDrawColor(4, 26, 131);
                        $pdf->Ln();
                        $pdf->SetWidths(array(95, 5, 95));
                        $pdf->SetAligns(array('C', 'C', 'C'));
                        $pdf->SetHeight(6);
                        $pdf->SetDrawEdge(false);
                        $pdf->Row(array('ORIGEN', NULL, 'DESTINO'));
                        $pdf->SetAligns(array('L', 'C', 'L'));
                        $pdf->SetFont('Arial','',10);
                        $initialY = $pdf->GetY();
                        $pdf->Row(array(utf8_decode('SUCURSAL: '.$exitMovement['branch_office_name']), NULL, utf8_decode('SUCURSAL: '.$entryMovement['branch_office_name'])));
                        $pdf->Row(array(utf8_decode('DIRECCIÓN: '.$exitMovement['branch_office_address']), NULL, utf8_decode('DIRECCIÓN: '.$entryMovement['branch_office_address'])));
                        $pdf->Row(array(utf8_decode('ALMACÉN: '.$exitMovement['storage']), NULL, utf8_decode('ALMACÉN: '.$entryMovement['storage'])));
                        $actualY = $pdf->GetY();
                        for ($i=$initialY; $i < $actualY; $i+=6) {
                            $pdf->Line($pdf->GetX(), $i+5, $pdf->GetX()+95, $i+5);
                            $pdf->Line($pdf->GetX()+100, $i+5, $pdf->GetX()+195, $i+5);
                        }

                        $pdf->Ln();

                        $pdf->SetFont('Arial','',8);
                        if (count($baleDetails) > 0) {
                            $pdf->Ln();
                            $pdf->SetWidths(array(25, 120, 50));
                            $pdf->SetAligns(array('C', 'C', 'C'));
                            $pdf->SetHeight(6);
                            $pdf->SetDrawEdge(false);
                            $pdf->Row(array('PACA', 'PRODUCTO', 'CANTIDAD'));
                            $pdf->SetAligns(array('C', 'L', 'R'));
                            $fill = true;
                            $totalQty = 0;
                            foreach ($baleDetails as $detail) {
                                $pdf->SetFill(array($fill, $fill, $fill));
                                $pdf->Row(array($detail['bale_id'],  utf8_decode($detail['category_code']).'-'.utf8_decode($detail['line_code']).'-'.utf8_decode($detail['product_name']), number_format($detail['qty'], 2, '.', ',').' KG.'));
                                $fill = !$fill;
                                $totalQty += $detail['qty'];
                            }
                            $pdf->SetWidths(array(145, 50));
                            $pdf->SetAligns(array('R', 'R'));
                            $pdf->SetFill(array(false, true));
                            $pdf->Row(array('TOTAL:', number_format($totalQty, 2, '.', ',').' KG.'));
                        }
                        if (count($laminateDetails) > 0) {
                            $pdf->Ln();
                            $pdf->SetWidths(array(145, 50));
                            $pdf->SetAligns(array('C', 'C'));
                            $pdf->SetHeight(6);
                            $pdf->SetDrawEdge(false);
                            $pdf->Row(array('PRODUCTO LAMINADO', 'CANTIDAD'));
                            $pdf->SetAligns(array('L', 'R'));
                            $fill = true;
                            $totalQty = 0;
                            foreach ($laminateDetails as $detail) {
                                $pdf->SetFill(array($fill, $fill, $fill));
                                $pdf->Row(array(utf8_decode($detail['category_code'].'-'.$detail['line_code'].'-'.$detail['product_name']), number_format($detail['qty'], 2, '.', ',').' KG.'));
                                $fill = !$fill;
                                $totalQty += $detail['qty'];
                            }
                            $pdf->SetWidths(array(145, 50));
                            $pdf->SetAligns(array('R', 'R'));
                            $pdf->SetFill(array(false, true));
                            $pdf->Row(array('TOTAL:', number_format($totalQty, 2, '.', ',').' KG.'));
                        }
                        if (count($rawMaterialDetails) > 0) {
                            $pdf->Ln();
                            $pdf->SetWidths(array(145, 50));
                            $pdf->SetAligns(array('C', 'C'));
                            $pdf->SetHeight(6);
                            $pdf->SetDrawEdge(false);
                            $pdf->Row(array('PRODUCTO MATERIA PRIMA', 'CANTIDAD'));
                            $pdf->SetAligns(array('L', 'R'));
                            $fill = true;
                            $totalQty = 0;
                            foreach ($rawMaterialDetails as $detail) {
                                $pdf->SetFill(array($fill, $fill, $fill));
                                $pdf->Row(array(utf8_decode($detail['category_code'].'-'.$detail['line_code'].'-'.$detail['product_name']), number_format($detail['qty'], 2, '.', ',').' KG.'));
                                $fill = !$fill;
                                $totalQty += $detail['qty'];
                            }
                            $pdf->SetWidths(array(145, 50));
                            $pdf->SetAligns(array('R', 'R'));
                            $pdf->SetFill(array(false, true));
                            $pdf->Row(array('TOTAL:', number_format($totalQty, 2, '.', ',').' KG.'));
                        }

                        $pdf->SetTitle("Traspaso sucursal #$branchTransfer->id",true);
                        $pdf->Output('I', "Traspaso sucursal #$branchTransfer->id.pdf", true);
                        $response = new Phalcon\Http\Response();
                        $response->setHeader("Content-Type", "application/pdf");
                        $response->setHeader("Content-Disposition", 'inline; filename="Traspaso sucursal #'.$branchTransfer->id.'.pdf"');
                        return $response;
                    }
                }
            }
        }
        return null;
    }

    public function create ($pt = 0)
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
        
            $request = $this->request->getPost();
            // print_r($request);
            // exit();
            $branchTransferX = [];
            $transaction = new Transactions();
            $transaction->setTransaction($tx);
            if ($transaction->create()) {
                $exitMovement = new Movements();
                $exitMovement->setTransaction($tx);
                $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                $exitMovement->date = $request['date'];
                $exitMovement->status = $request['status'];
                $exitMovement->storage_id = $request['originStorage'];
                $exitMovement->type_id = 5;
                $exitMovement->transaction_id = $transaction->id;
                if ($exitMovement->create()) {
                    $entryMovement = new Movements();
                    $entryMovement->setTransaction($tx);
                    $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                    $entryMovement->date = $request['date'];
                    $entryMovement->status= $request['status'];
                    $entryMovement->storage_id = $request['destinationStorage'];
                    $entryMovement->movement_id = $exitMovement->id;
                    $entryMovement->type_id = 4;
                    $entryMovement->transaction_id = $transaction->id;
                    if ($entryMovement->create()) {
                        $branchTransfer = new BranchTransfers();
                        $branchTransfer->setTransaction($tx);
                        $branchTransfer->transaction_id = $transaction->id;
                        if ($branchTransfer->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('Transferencia registrada correctamente.');
                            $this->content['branchTransfer'] = $exitMovement;
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lot);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                    }
                } else {
                    $this->content['error'] = Helpers::getErrors($lot);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                    $tx->rollback();
                }
            } else {
                $this->content['error'] = Helpers::getErrors($lot);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                $tx->rollback();
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function update ($id) {
        if(is_numeric($id)){
            try{
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $exitMovement = Movements::findFirst($id);
                if($this->userHasPermission()){
                    $exitMovement->setTransaction($tx);
                    $exitMovement->storage_id = $request['storage_exit_id'];
                    $exitMovement->date = $request['date'];
                    if($exitMovement->update()){
                        $entryMovement = Movements::findFirst($request['id_entry']);
                        if ($entryMovement){
                            $entryMovement->storage_id = $request['storage_entry_id'];
                            $entryMovement->date = $request['date'];
                            if($entryMovement->update()){
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El Movimiento ha sido actualizado');
                                $this->content['branchTransfer'] = $entryMovement;
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($lot);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar el movimiento.');
                                $tx->rollback();
                            }
                        }
                    }
                }
            }catch(Exception $e) {

            }
        }
        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 20 OR role_id = 22)
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

class PDFBranchTransfer extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $branchTransferId;
    var $date;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo_name.png';
        $this->Image($img,5,5,75,0,'PNG');
        $this->SetTextColor(4, 26, 131);
        $this->SetFont('Arial','B',20);
        $this->Cell(0, 10, utf8_decode("TRASPASO SUCURSAL #$this->branchTransferId"), 0, 0, 'R');
        $this->Ln();
        $this->Cell(0, 10, $this->date, 0, 0, 'R');
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

    function SetBranchTransferId($bti)
    {
        $this->branchTransferId=$bti;
    }

    function SetDate($d)
    {
        $this->date = $d;
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
