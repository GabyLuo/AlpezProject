<?php

use Phalcon\Mvc\Controller;

class PurchaseOrderDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOrderDetails ()
    {
        if ($this->userHasPermissionToGet()) {
            $this->content['orderDetails'] = PurchaseOrderDetails::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getDataReportShopping ($request) {
        
        $dataIni = $request['dataini'];
        $dataFin = $request['datafin'];
        $supplier = $request['supplier'];
        $product = $request['product'];
        //$dataIni != null || $dataIni != '' || $dataIni != "" || 
        $where = "  ";
        if (strlen($dataIni) > 0) {
            $daateIni = date('Y-m-d' , strtotime($dataIni));

            $where .= " and pur_orders.invoice_date >=  '".$daateIni."' ";
        }
        if (strlen($dataFin) > 0) {
            $daateFin = date('Y-m-d' , strtotime($dataFin));
            $where .= " and pur_orders.invoice_date <=  '".$daateFin."' ";
        }
        if ($supplier != 'TODOS') {
            $where .= " and pur_suppliers.id = $supplier ";
        }

        if ($product != 'TODOS') {
            $where .= " and wms_products.id = $product ";
        }

        $sql = "SELECT wms_products.id, pur_orders.serial, pur_suppliers.name as supplier,
        pur_order_details.qty,
        pur_order_details.price as price_unit,
        sum(((pur_order_details.qty * pur_order_details.price) * (pur_order_details.vat_rate / 100))) + (pur_order_details.qty * pur_order_details.price) as total,
        wms_products.name as product,
		wms_products.description as description,
        wms_products.id as product_id,
		to_char(pur_orders.invoice_date, 'DD/MM/YYYY') as receive_date,
        pur_order_details.vat_rate
        FROM pur_order_details
        inner join pur_orders on pur_orders.id = pur_order_details.po_id
        inner join wms_products on wms_products.id = pur_order_details.product_id
        inner join pur_suppliers on pur_orders.supplier_id = pur_suppliers.id 
        where pur_orders.status = 'RECIBIDO' $where
        group by pur_orders.serial, pur_suppliers.name, pur_order_details.qty, 
        pur_order_details.price,wms_products.name, wms_products.id, pur_orders.invoice_date, pur_order_details.vat_rate
        order by pur_orders.serial  DESC";

            $query = $this->db->query($sql)->fetchAll();

            return $query;
    }
    public function getReportShopping () {
        if ($this->userHasPermissionToGet()) {
            $request = $this->request->getPost();
            
            $data = $this->getDataReportShopping($request);
            $arrayData = [];
            $totalWithIva = 0;
            $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
            foreach ($data as $key => $value) {
                # code...
                $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
                $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                array_push($arrayData, array('serial' => $value['serial'], 'supplier' => $value['supplier'], 'product' => $value['product'], 'description' => $value['description'],  'receive_date' => $value['receive_date'], 'qty' => $value['qty'], 'price_unit' => "$ ".number_format($value['price_unit'], 2, '.', ','), 'total' => "$ ".number_format($totalWithIva, 2, '.', ',')));
                $qtyProducts +=  $value["qty"];
                $totalUnit += $value["price_unit"];
                $total += $totalWithIva;
            }
            array_push($arrayData, array('serial' => '', 'supplier' => '', 'product' => '', 'description' => '', 'receive_date' => 'TOTAL:', 'qty' => $qtyProducts, 'price_unit' => "$ ".number_format($totalUnit, 2, '.', ','), 'total' => "$ ".number_format($total, 2, '.', ',')));
            $this->content['shopping'] = $arrayData;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getDataReportShoppingToPDF ($dataini, $datafin, $supplier, $product) {
        
        $dataIni = $dataini;
        $dataFin = $datafin;
        $supplier = $supplier;
        $product = $product;
        //$dataIni != null || $dataIni != '' || $dataIni != "" || 
        $where = "  ";
        if ($dataIni != null && $dataIni != '' && $dataIni != "" && $dataIni != "null") {
            $daateIni = date('Y-m-d' , strtotime($dataIni));

            $where .= " and pur_orders.invoice_date >=  '".$daateIni."' ";
        }
        if ($dataFin != null && $dataFin != '' && $dataFin != "" && $dataFin != "null") {
            $daateFin = date('Y-m-d' , strtotime($dataFin));
            $where .= " and pur_orders.invoice_date <=  '".$daateFin."' ";
        }
        if ($supplier != 'TODOS') {
            $where .= " and pur_suppliers.id = $supplier ";
        }
        if ($product != 'TODOS') {
            $where .= " and wms_products.id = $product ";
        }
        $sql = "SELECT wms_products.id, pur_orders.serial, pur_suppliers.name as supplier,
        pur_order_details.qty,
        pur_order_details.price as price_unit,
        sum(((pur_order_details.qty * pur_order_details.price) * (pur_order_details.vat_rate / 100))) + (pur_order_details.qty * pur_order_details.price) as total,
        wms_products.name as product,
		wms_products.description as description,
        wms_products.id as product_id,
		to_char(pur_orders.invoice_date, 'DD/MM/YYYY') as receive_date,
        pur_order_details.vat_rate
        FROM pur_order_details
        inner join pur_orders on pur_orders.id = pur_order_details.po_id
        inner join wms_products on wms_products.id = pur_order_details.product_id
        inner join pur_suppliers on pur_orders.supplier_id = pur_suppliers.id 
        where pur_orders.status = 'RECIBIDO' $where
        group by pur_orders.serial, pur_suppliers.name, pur_order_details.qty, 
        pur_order_details.price,wms_products.name, wms_products.id, pur_orders.invoice_date, pur_order_details.vat_rate
        order by pur_orders.serial  DESC";

            $query = $this->db->query($sql)->fetchAll();
            return $query;
    }
    public function getReportShoppingToPDF ($dataini, $datafin, $supplier, $product) {
            
            $data = $this->getDataReportShoppingToPDF($dataini, $datafin, $supplier, $product);
            $pdf = new ReportShoppingCartPDF('L','mm','Letter');
                    $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
                    $pdf->AliasNbPages();
                    $pdf->setDateNow(date("d/m/Y"));
                    $pdf->SetTitlePDF("Compras proveedor");
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(false, 20);
                    // $pdf->SetFillColor(71, 130, 222);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetDrawColor(0);
                    //$pdf->SetWidths(array(210));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetHeight(8);
                    // $pdf->SetFill(array(true));
                    $pdf->SetDrawEdge(true);
                    $pdf->SetTextColors(array([0, 0, 0]));
                    $pdf->Ln(40);

                    $header = array('ORDEN COMPRA','PROVEEDOR','PRODUCTO',utf8_decode('DESCIPCIÓN'),'FECHA ENTRADA','CANTIDAD','PRECIO UNI.',utf8_decode('TOTAL'));
                $pdf->SetXY(10,$pdf->GetY());
                //25, 107, 210
                $pdf->SetFillColor(128,179,240);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetLineWidth(0);
                $pdf->SetFont('Nunito-Regular','',8);
                // Header
                $x = 190;
                $i = 0;
                // $w=array(5,20,25,30,25,30,35,20,15);
                $w=array(24,70,33,34,30,20,21,27);
                foreach($header as $col) {
                    if($i<=7){
                        $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                    }
                    $x=$x+5;
                    $i++;
                }
                $fill = false;
                $pdf->SetWidths(array(24,70,33,34,30,20,21,27));
                $pdf->SetAligns(array('C', 'L','L','L','C','R','R','R')); 
                $pdf->Ln(7);
                $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
                $totalWithIva = 0;
                foreach ($data as $value) {
                    // var_dump($pdf->GetY());
                    if ($pdf->GetY() >= 170) {
                         $pdf->AddPage();
                         $pdf->SetXY(10,60);
                     }else {
                         $pdf->SetXY(10,$pdf->GetY());
                     } 
                     $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
                     $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                         $pdf->Row(array($value['serial'], utf8_decode($value["supplier"]), 
                         $value['product'], $value["description"],$value["receive_date"], $value["qty"], 
                         "$".number_format($value["price_unit"], 2, '.', ','),"$".number_format($totalWithIva, 2, '.', ',')), false);

                         $qtyProducts +=  $value["qty"];
                         $totalUnit += $value["price_unit"];
                         $total += $totalWithIva;
                 }

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(171,$pdf->GetY());
                 $pdf->Cell(30,7,"Total:",1,0,'R',true);

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(201,$pdf->GetY());
                 $pdf->Cell(20,7,$qtyProducts,1,0,'R',true);

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(221,$pdf->GetY());
                 $pdf->Cell(21,7,"$".number_format($totalUnit, 2, '.', ','),1,0,'R',true);

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(242,$pdf->GetY());
                 $pdf->Cell(27,7,"$".number_format($total, 2, '.', ','),1,0,'R',true);
                    $pdf->SetTitle(utf8_decode("Compras proveedor"));
                    header("Access-Control-Allow-Origin: *");
                    header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', utf8_decode("Compras proveedor"), true);

        return $pdf;
    }

    public function getReportShoppingToCSV ($dataini, $datafin, $supplier, $product) {
        $data = $this->getDataReportShoppingToPDF($dataini, $datafin, $supplier, $product);
        date_default_timezone_set('America/Mexico_City');
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['ORDEN COMPRA','PROVEEDOR','PRODUCTO',utf8_decode('DESCIPCIÓN'),'FECHA ENTRADA','CANTIDAD','PRECIO UNI.',utf8_decode('TOTAL')], ',');
        $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
        foreach ($data as $value) {
            $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
            $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                 fputcsv($fp, [
                    $value['serial'], utf8_decode($value["supplier"]), 
                    $value['product'], $value["description"],$value["receive_date"], $value["qty"], 
                    "$".number_format($value["price_unit"], 2, '.', ','),"$".number_format($totalWithIva, 2, '.', ',')
                ], ',');
                 $qtyProducts +=  $value["qty"];
                 $totalUnit += $value["price_unit"];
                 $total += $totalWithIva;
         }
         fputcsv($fp, ['', '', 
            '', '','', $qtyProducts, 
            "$".number_format($totalUnit, 2, '.', ','),"$".number_format($total, 2, '.', ',')
        ], ',');
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Compras proveedor.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getProducts () {
        if ($this->userHasPermissionToGet()) {

            $sql = "SELECT id as value, name as label FROM wms_products where active = true";
            $query = $this->db->query($sql)->fetchAll();
            $this->content['products'] = $query;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getDataReportShoppingSupplier ($request) {
        $supplier = $request['supplier'];
        $dateini = $request['dataini'];
        $saleDatev2 = $request['saleDatev2'];
        $branch = $request['branch'];
        //$dataIni != null || $dataIni != '' || $dataIni != "" || 
        $where = "  ";
        if ($supplier != 'TODOS') {
            $where .= " and pur_suppliers.id = $supplier ";
        }
        if (strlen($dateini) > 0) {
            $dateIni = date('Y-m-d' , strtotime($dateini));
            $where .= " and pur_orders.invoice_date >=  '".$dateIni."' ";
        }
        if (strlen($saleDatev2) > 0) {
            $dataFin = date('Y-m-d' , strtotime($saleDatev2));
            $where .= " and pur_orders.invoice_date <=  '".$dataFin."' ";
        }
        if ($branch != 'TODOS') {
            $where .= " and wbo.id =  ". $branch;
        }

        $sql = "SELECT wms_products.id, pur_orders.serial, pur_suppliers.name as supplier,
        pur_order_details.qty,
        pur_order_details.price as price_unit,
        sum(((pur_order_details.qty * pur_order_details.price) * (pur_order_details.vat_rate / 100))) + (pur_order_details.qty * pur_order_details.price) as total,
        wms_products.name as product,
		wms_products.description as description,
        wms_products.id as product_id,
		to_char(pur_orders.invoice_date, 'DD/MM/YYYY') as receive_date,
        pur_order_details.vat_rate
        FROM pur_order_details
        inner join pur_orders on pur_orders.id = pur_order_details.po_id
        inner join wms_products on wms_products.id = pur_order_details.product_id
        inner join pur_suppliers on pur_orders.supplier_id = pur_suppliers.id 
        left join wms_storages as ws on ws.id = pur_orders.storage_id
        left join wms_branch_offices as wbo on wbo.id = ws.branch_office_id
        where pur_orders.status = 'RECIBIDO' $where
        group by pur_orders.serial, pur_suppliers.name, pur_order_details.qty, 
        pur_order_details.price,wms_products.name, wms_products.id, pur_orders.invoice_date, pur_order_details.vat_rate
        order by pur_orders.serial  DESC";
        
            $query = $this->db->query($sql)->fetchAll();

            return $query;
    }

    //Compras de proveedor
    public function shoppingOfSuppliers () {
        if ($this->userHasPermissionToGet()) {
            $request = $this->request->getPost();
            
            $data = $this->getDataReportShoppingSupplier($request);

            $sqlnameSuppliers = "SELECT name from pur_suppliers";
            $querynamesuppliers = $this->db->query($sqlnameSuppliers)->fetchAll();
            $arrayData = [];
            $totalWithIva = 0;
            $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
            $add = false;
            foreach ($querynamesuppliers as $key => $valueSupp) {
                # code...
                foreach ($data as $key => $value) {
                    # code...
                    
                    if ($valueSupp['name'] == $value['supplier']) {
                        $add = true;
                        $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
                    $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                    //$qtyProducts +=  $value["qty"];
                    //$totalUnit += $value["price_unit"];
                    $total += $totalWithIva;
                    }
                }
                if ($add) {
                    array_push($arrayData, array('supplier' => $valueSupp['name'], 'total' => "$".number_format($total, 2, '.', ',')));
                }
                $total = 0;
                $add = false;
            }
            //array_push($arrayData, array('supplier' => $value['supplier'], 'total' => $total));
            $this->content['shopping'] = $arrayData;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getDataReportShoppingSupplierToPDFCSV ($supplier,  $dataini, $datafin, $officeBranch) {
        $supplier = $supplier;
        $dataini = $dataini;
        $datafin = $datafin;
        $branch = $officeBranch;
        //$dataIni != null || $dataIni != '' || $dataIni != "" || 
        $where = "  ";
        if ($supplier != 'TODOS') {
            $where .= " and pur_suppliers.id = $supplier ";
        }

        if ($dataini != null && $dataini != '' && $dataini != "" && $dataini != "null") {
            $dateIni = date('Y-m-d' , strtotime($dataini));
            $where .= " and pur_orders.invoice_date >=  '".$dateIni."' ";
        }
        if ($datafin != null && $datafin != '' && $datafin != "" && $datafin != "null") {
            $datefin = date('Y-m-d' , strtotime($datafin));
            $where .= " and pur_orders.invoice_date <=  '".$datefin."' ";
        }
        if($officeBranch != 'TODOS'){
            $where .= " and wbo.id =  ". $branch;
        }

        $sql = "SELECT wms_products.id, pur_orders.serial, pur_suppliers.name as supplier,
        pur_order_details.qty,
        pur_order_details.price as price_unit,
        sum(((pur_order_details.qty * pur_order_details.price) * (pur_order_details.vat_rate / 100))) + (pur_order_details.qty * pur_order_details.price) as total,
        wms_products.name as product,
		wms_products.description as description,
        wms_products.id as product_id,
		to_char(pur_orders.invoice_date, 'DD/MM/YYYY') as receive_date,
        pur_order_details.vat_rate
        FROM pur_order_details
        inner join pur_orders on pur_orders.id = pur_order_details.po_id
        inner join wms_products on wms_products.id = pur_order_details.product_id
        inner join pur_suppliers on pur_orders.supplier_id = pur_suppliers.id
        left join wms_storages as ws on ws.id = pur_orders.storage_id
        left join wms_branch_offices as wbo on wbo.id = ws.branch_office_id
        where pur_orders.status = 'RECIBIDO' $where
        group by pur_orders.serial, pur_suppliers.name, pur_order_details.qty, 
        pur_order_details.price,wms_products.name, wms_products.id, pur_orders.invoice_date, pur_order_details.vat_rate
        order by pur_orders.serial  DESC";
            $query = $this->db->query($sql)->fetchAll();

            return $query;
    }
    public function shoppingOfSuppliersPDF ($supplier, $dataini, $datafin, $officeBranch) {
        $data = $this->getDataReportShoppingSupplierToPDFCSV($supplier, $dataini, $datafin, $officeBranch);
        $sqlnameSuppliers = "SELECT name from pur_suppliers";
            $querynamesuppliers = $this->db->query($sqlnameSuppliers)->fetchAll();
            $pdf = new ReportShoppingCartPDF('L','mm','Letter');
                    $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
                    $pdf->AliasNbPages();
                    $pdf->setDateNow(date("d/m/Y"));
                    $pdf->SetTitlePDF("Compras proveedor");
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(false, 20);
                    // $pdf->SetFillColor(71, 130, 222);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetDrawColor(0);
                    //$pdf->SetWidths(array(210));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetHeight(8);
                    // $pdf->SetFill(array(true));
                    $pdf->SetDrawEdge(true);
                    $pdf->SetTextColors(array([0, 0, 0]));
                    $pdf->Ln(40);

                    $header = array('PROVEEDOR',utf8_decode('TOTAL'));
                $pdf->SetXY(10,$pdf->GetY());
                //25, 107, 210
                $pdf->SetFillColor(128,179,240);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetLineWidth(0);
                $pdf->SetFont('Nunito-Regular','',8);
                // Header
                $x = 190;
                $i = 0;
                // $w=array(5,20,25,30,25,30,35,20,15);
                $w=array(200,60);
                foreach($header as $col) {
                    if($i<=1){
                        $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                    }
                    $x=$x+5;
                    $i++;
                }
                $fill = false;
                $pdf->SetWidths(array(200,60));
                $pdf->SetAligns(array('L', 'R')); 
                $pdf->Ln(7);
                $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
                $totalWithIva = 0;
                $sumAll = 0;
                $add = false;
                foreach ($querynamesuppliers as $key => $valueSupp) {
                    # code...
                    foreach ($data as $key => $value) {
                        # code...
                        
                        if ($valueSupp['name'] == $value['supplier']) {
                            $add = true;
                            $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
                        $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                        //$qtyProducts +=  $value["qty"];
                        //$totalUnit += $value["price_unit"];
                        $total += $totalWithIva;
                        }
                    }
                    if ($add) {
                        if ($pdf->GetY() >= 190) {
                            $pdf->AddPage();
                            $pdf->SetXY(10,63);
                        }else {
                            $pdf->SetXY(10,$pdf->GetY());
                        } 
                        $pdf->Row(array(utf8_decode($valueSupp["name"]), "$".number_format($total, 2, '.', ','), ), false);
                    }
                    $sumAll += $total;
                    $total = 0;
                    $add = false;
                }

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(10,$pdf->GetY());
                 $pdf->Cell(200,7,"Total:",1,0,'R',true);

                 $pdf->SetY($pdf->getY());
                 $pdf->SetFillColor(255);
                 $pdf->SetXY(210,$pdf->GetY());
                 $pdf->Cell(60,7,"$".number_format($sumAll, 2, '.', ','),1,0,'R',true);
                    $pdf->SetTitle(utf8_decode("Compras proveedor"));

                    header("Access-Control-Allow-Origin: *");
                    header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', utf8_decode("Compras de proveedor"), true);

        return $pdf;
    }

    public function getReportShoppingToCSVShoppingSupplier ($supplier, $dataini, $datafin, $officeBranch) {
        $data = $this->getDataReportShoppingSupplierToPDFCSV($supplier, $dataini, $datafin, $officeBranch);
        $sqlnameSuppliers = "SELECT name from pur_suppliers";
        $querynamesuppliers = $this->db->query($sqlnameSuppliers)->fetchAll();
        date_default_timezone_set('America/Mexico_City');
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['PROVEEDOR',utf8_decode('TOTAL')], ',');
        $qtyProducts = 0;
                $totalUnit = 0;
                $total = 0;
                $sumAll = 0;
                $add = false;
         foreach ($querynamesuppliers as $key => $valueSupp) {
            # code...
            foreach ($data as $key => $value) {
                # code...
                
                if ($valueSupp['name'] == $value['supplier']) {
                    $add = true;
                    $iva = $value['vat_rate'] == 0 ? 0 : floatval($value['vat_rate'] / 100);
                $totalWithIva = floatval((($value['qty'] * $value['price_unit']) * $iva) + ($value['qty'] * $value['price_unit']));
                //$qtyProducts +=  $value["qty"];
                //$totalUnit += $value["price_unit"];
                $total += $totalWithIva;
                }
            }
            if ($add) {
                fputcsv($fp, [
                    utf8_decode($valueSupp["name"]), "$".number_format($total, 2, '.', ',')
                ], ',');
            }
            $sumAll += $total;
            $total = 0;
            $add = false;
        }
         fputcsv($fp, ['TOTAL:', "$".number_format($sumAll, 2, '.', ','),
        ], ',');
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Compras de proveedor.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getOrderDetailsByOrderId ($orderId)
    {
        if (!is_null($orderId)) {
            if ($this->userHasPermissionToGet()) {
                $sql = "SELECT DISTINCT ON (od.product_id) od.*,case when od.vat_rate is null then 0 else od.vat_rate end as vat_rate,case when od.vat is null then 0 else od.vat end as vat, p.name AS product, COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada, od.price, od.qty,
                od.last_price
                        FROM pur_order_details AS od
                        INNER JOIN wms_products AS p
                        ON od.product_id = p.id
                        LEFT JOIN pur_shipments as sh
                        ON sh.order_id = od.po_id
                        LEFT JOIN pur_shipment_details as shd
                        ON shd.shipment_id = sh.id and od.product_id = shd.product_id and sh.status = 'RECIBIDO'
                        WHERE od.po_id = $orderId 
                        GROUP BY od.id, p.name
                        ORDER BY od.product_id, product ASC;";
                $orderDetails = $this->db->query($sql)->fetchAll();
                $this->content['orderDetails'] = $orderDetails;
                $total = 0; // Subtotal
                $total_vat = 0; // IVA
                foreach ($orderDetails as $key => $od) {
                    $total += $od['price'] * $od['qty'];
                    $total_vat += $od['vat'];
                }
                $this->content['total_vat'] = $total_vat;
                $this->content['total_neto'] = $total_vat + $total;
                $this->content['total_order'] = $total;
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } else {
            $this->content['orderDetails'] = [];
            $this->content['result'] = false;
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOrderDetail ($id)
    {
        if ($this->userHasPermissionToGet()) {
            $this->content['orderDetail'] = PurchaseOrderDetails::findFirst($id);
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getLastPrice2 ($id, $idp) {
        if ($this->userHasPermission()) {
            $sql = "SELECT pur_orders.id, pur_orders.serial, pur_orders.status, pur_order_details.price, pur_order_details.qty FROM pur_orders 
            inner join pur_order_details on pur_order_details.po_id = pur_orders.id
            where pur_orders.supplier_id = $id and pur_order_details.product_id = $idp order by pur_orders.id desc limit 2 ";
            //var_dump($sql);
            $query = $this->db->query($sql)->fetchAll();
            $price = 0;
            var_dump($sql);
            if (count($query) >= 2)  {
                $price = $query[1]['price'];
            }else {
                $price = 0; 
            }
            $this->content['lastprice'] = $price;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getLastPrice ($id, $idp) {
        if ($this->userHasPermission()) {
            $sql = "SELECT pur_orders.id, pur_orders.serial, pur_orders.status, pur_order_details.price, pur_order_details.qty FROM pur_orders 
            inner join pur_order_details on pur_order_details.po_id = pur_orders.id
            where pur_orders.supplier_id = $id and pur_order_details.product_id = $idp order by pur_orders.id desc limit 1 ";
            
            $query = $this->db->query($sql)->fetchAll();
            $price = 0;
            if (count($query))  {
                $price = $query[0]['price'];
            }else {
                $price = 0; 
            }
            $this->content['lastprice'] = $price;
            $this->content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                if (isset($request['po_id']) && isset($request['product_id']) && isset($request['qty']) && isset($request['price']) && isset($request['quality']) && isset($request['color'])) {
                    $product = Products::findFirst($request['product_id']);
                    if ($product && $product->active) {
                        if (is_numeric($request['qty']) && $request['qty'] > 0 && is_numeric($request['price']) && $request['price'] > 0) {
                            $orderDetail = new PurchaseOrderDetails();
                            $orderDetail->setTransaction($tx);
                            $orderDetail->po_id = $request['po_id'];
                            $orderDetail->product_id = $request['product_id'];
                            $orderDetail->qty = $request['qty'];
                            $orderDetail->price = $request['price'];
                            $orderDetail->observation = $request['observation'];
                            $orderDetail->vat_rate = $request['vat_rate'] ? floatval($request['vat_rate']) : 0;
                            $orderDetail->vat = ((floatval($request['vat_rate']) * floatval( $request['price'])) / 100) * $request['qty'];
                            $orderDetail->last_price = floatval($request['last_price']);
                            if ($orderDetail->create()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El detalle ha sido creado.');
                                $this->content['orderDetail'] = $orderDetail;
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($orderDetail);
                                if ($this->content['error'][1]) {
                                    $this->content['message'] = Message::error($this->content['error'][1]);
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el detalle.');
                                }
                                // $tx->rollback();
                            }
                        } else {
                            $content['message'] = Message::error('La cantidad y el precio deben ser números positivos.');
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                } else {
                    $content['message'] = Message::error('No se han recibido los valores necesarios.');
                }
            } else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            if (Message::exception($e)['code'] && Message::exception($e)['code'] == '23505') {
                $this->content['message']['content'] = 'Una orden de compra no puede tener productos repetidos';
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $orderDetail = PurchaseOrderDetails::findFirst($id);

                $request = $this->request->getPut();

                if ($orderDetail) {
                    $product = Products::findFirst($request['product_id']);
                    if ($product && $product->active) {
                        $orderDetail->setTransaction($tx);
                        // $orderDetail->po_id = $request['po_id'];
                        if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                            $orderDetail->product_id = $request['product_id'];
                        }
                        if (isset($request['qty']) && is_numeric($request['qty']) && $request['qty'] > 0) {
                            $orderDetail->qty = $request['qty'];
                        }
                        if (isset($request['price']) && is_numeric($request['price']) && $request['price'] > 0) {
                            $orderDetail->price = $request['price'];
                        }
                        if (isset($request['observation'])) {
                            $orderDetail->observation = $request['observation'];
                        }
                        if (isset($request['vat_rate'])) {
                            $orderDetail->vat_rate = $request['vat_rate'] ? floatval($request['vat_rate']) : 0;
                            $orderDetail->vat = ((floatval($request['vat_rate']) * floatval( $request['price'])) / 100) * $request['qty'];
                        }
                        
                        if ($orderDetail->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El detalle ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($orderDetail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el detalle.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                }
            } else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
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

                $orderDetail = PurchaseOrderDetails::findFirst($id);

                if ($orderDetail) {
                    $orderDetail->setTransaction($tx);

                    if ($orderDetail->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($orderDetail);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El detalle no existe.');
                }
            } else {
                $content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 22 OR role_id = 5 OR role_id = 3 OR role_id = 21 or role_id = 2 or role_id = 26 or role_id = 28 or role_id = 29 or role_id = 27)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasPermissionToGet()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE  role_id in( 1, 7, 2, 22, 3, 20, 21, 2, 26, 28, 29, 27)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    public function getTable2OrdersSaleNote($id) {
        $sql = "SELECT DISTINCT ON (od.product_id) od.*,case when od.vat_rate is null then 0 else od.vat_rate end as vat_rate,case when od.vat is null then 0 else od.vat end as vat, p.name AS product, p.description,
        COALESCE((od.qty - SUM(shd.qty)), od.qty) as restante, COALESCE(SUM(shd.qty), 0) as entrada, od.price, od.qty, mark.name as marks,
        ((od.qty * od.price) * (od.vat_rate)/100) + (od.qty * od.price)  as total,
        (od.qty * od.price)  as total2,
        ((od.qty * od.price) * (od.vat_rate)/100)  as ivaa
        FROM pur_order_details AS od
        INNER JOIN wms_products AS p
        ON od.product_id = p.id
        inner join wms_marks as mark on mark.id = p.mark_id 
        LEFT JOIN pur_shipments as sh
        ON sh.order_id = od.po_id
        LEFT JOIN pur_shipment_details as shd
        ON shd.shipment_id = sh.id and od.product_id = shd.product_id and sh.status = 'RECIBIDO'
        WHERE od.po_id = $id 
        GROUP BY od.id, p.name, p.description,mark.name
        ORDER BY od.product_id, product ASC;";

        $query = $this->db->query($sql)->fetchAll();

        return $query;

    }
    public function getTotal ($id) {
        $sql = "SELECT sum((sls_shopping_cart_in_bulk_details.price_product) * (sls_shopping_cart_in_bulk_details.qty)) as price
        from sls_shopping_cart_in_bulk_details
          inner join sls_shopping_cart on sls_shopping_cart.id = sls_shopping_cart_in_bulk_details.shopping_cart_id
        where sls_shopping_cart.id = $id";

        $query = $this->db->query($sql)->fetchAll();

        return $query;
    }
    public function getNameCustomers ($id) {
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
        $query = $this->db->query($sql)->fetchAll();

        return $query;
    }
    public function getNameContactCustomers ($id) {
        $sql = "SELECT sls_customer_contacts.name, to_char(sls_shopping_cart.inmediatedate, 'DD/MM/YYYY') as dateinmediate from sls_shopping_cart 
        inner join sls_customer_contacts on sls_customer_contacts.id = sls_shopping_cart.contact_client_id
        where sls_shopping_cart.id = $id";
        $query = $this->db->query($sql)->fetchAll();

        return $query;
    }


    public function getPdfquotationNote ($id)
    {
        if (is_numeric($id)) {
            $invoice = PurchaseOrders::findFirst($id);
            if ($invoice) {
                $pdf = $this->quotationNotePDF($id);

                if (!is_null($pdf)) {
                    //$pdf->Output('I', "Remisión #$invoice->id.pdf", true);
                    $pdf->Output('I', "Cotizacion", true);

                    $response = new Phalcon\Http\Response();

                    $response->setHeader("Content-Type", "application/pdf");
                    $response->setHeader("Content-Disposition", 'inline; filename="Nota"');
                    return $response;
                }
            }
        }
        return null;
    }

    public function quotationNotePDF($id) {
        $tb2 = $this->getTable2OrdersSaleNote($id);
        //$info = ShoppingCart::findFirst("id = $id");
        $total = $this->getTotal($id);
        $nameCustomers = $this->getNameCustomers($id);
        $pdf = new SaleNotesPdfControllerPurchase('P','mm','Letter');
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        
        $pdf->SetQuote('COTIZACIÓN');
        $pdf->serial = $id;
        $pdf->AddPage();
        $pdf->CustomHeader($id);
        $pdf->SetAutoPageBreak(false, 20);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Nunito-Regular','',10);
        $pdf->SetDrawColor(21, 18, 46);
        $pdf->SetLineWidth(0);
        $pdf->Ln();
        $pdf->SetXY(40,33);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,7,utf8_decode('Proveedor: '),0,0,'L');
        $pdf->SetXY(60,33);
        $pdf->SetFont('Nunito-Regular','',9);
        $pdf->Cell(70,7,utf8_decode($nameCustomers[0]["po"]),0,0,'L');
        //
        $pdf->SetXY(40,40);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,7,utf8_decode('Folio: '),0,0,'L');
        $pdf->SetXY(60,40);
        $pdf->SetFont('Nunito-Regular','',9);
        $pdf->Cell(70,7,utf8_decode($nameCustomers[0]["serial"]),0,0,'L');
        //
        $pdf->SetXY(40,38);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetXY(170,45);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(8,7,utf8_decode("Fecha: "),0,0,'L');
        $pdf->SetXY(181,45);
        $pdf->SetFont('Nunito-Regular','',10);
        $pdf->Cell(8,7,utf8_decode("".date('d').'/'.date('m').'/'.date('Y')),0,0,'L');
        $pdf->SetFont('Nunito-Regular','',9);
        $pdf->SetY(45);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColors(array(0));
        $pdf->SetDrawColor(0,0,0);
        $pdf->SetHeight(5);
        $pdf->Ln();
        // tabla 2

        $header = array('CANT.','PRODUCTO',utf8_decode('DESCRIPCIÓN'),'MARCA',utf8_decode('PRECIO U.'),utf8_decode('TOTAL'));
                $pdf->SetXY(37,$pdf->GetY());
                //25, 107, 210
                $pdf->SetFillColor(128,179,240);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetLineWidth(0);
                $pdf->SetFont('Nunito-Regular','',8);
                // Header
                $x = 190;
                $i = 0;
                // $w=array(5,20,25,30,25,30,35,20,15);
                $w=array(10,40,50,29,17,25);
                foreach($header as $col) {
                    if($i<=5){
                        $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                    }
                    $x=$x+5;
                    $i++;
                }
                $pdf->Ln();
                $pdf->SetDrawColor(255,255,255);

                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                //$pdf->SetWidths(array(10,40,35,25,25,17,20));

                $amount = 0;
                $fill = false;
                $pdf->SetFont('Nunito-Regular','',7);

                $pdf->SetWidths(array(10,40,50,29,17,25));
                $pdf->SetAligns(array('C', 'L','L','L','R','R')); 
                $bandera=1;
                $pdf->SetXY(37,$pdf->GetY());
                $totalPurchase = 0;
                $ivaa = 0;
                foreach ($tb2 as $value) {
                    //$value["observation"],
                    $pdf->Row(array(
                        utf8_decode($value["qty"]),
                        utf8_decode($value["product"]), 
                        $value["description"],
                        utf8_decode($value["marks"]),
                        "$ ".number_format($value["price"],2, '.', ',')."",
                        "$ ".number_format($value["total"],2, '.', ',').""
                    ),
                    $fill);
                    //$bandera++;
                    $fill = !$fill;
                    $amount += intval($value["qty"]);
                    $totalPurchase += floatval($value["total2"]);
                    $ivaa += $value["ivaa"];
                    //var_dump($pdf->GetY());
                    if ($pdf->getY() >= 230) {
                        $pdf->AddPage();
                        $pdf->SetXY(37,42);
                        
                    } else {
                        $pdf->SetXY(37,$pdf->GetY());
                    }
                    
                }
                
                $pdf->SetY($pdf->getY());
                $pdf->Ln();
                $pdf->SetFillColor(255);
                $pdf->SetXY(37,$pdf->GetY());
                $pdf->Cell(10, 6, "".$amount, 0, 0, 'C', true);
                
                $pdf->SetY(215);
                $pdf->SetXY(41,$pdf->GetY());
                $pdf->SetFillColor(128,179,240);
                $pdf->Cell(165, 2,"", 0, 0, 'C', true);
                $pdf->SetTextColor(21, 18, 46);
                $pdf->Ln();
                /* $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("Condiciones Comerciales: ". $info->commercial_terms),0,0,'L'); */
                $pdf->Ln();
                $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetLineWidth(0.4);
                $pdf->SetDrawColor(0,0,0);
                $pdf->SetXY(152,$pdf->GetY());
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(25, 6, "Subtotal:", 0, 0, 'R', true);
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format($totalPurchase,2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->SetXY(152,$pdf->GetY());
                $pdf->Cell(25, 6, utf8_decode("IVA:"), 0, 0, 'R', true);
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format($ivaa,2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0);
                $pdf->SetXY(152,$pdf->GetY());
                $pdf->Cell(25, 6, "TOTAL:", 0, 0, 'R', true);
                
                $totalcost = ($total[0]["price"] * 0.16) + $total[0]["price"];
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format(floatval($totalPurchase + $ivaa),2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                /* $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("Vigencia: ". $info->validity),0,0,'L'); */
                /* $pdf->SetXY(40,$pdf->GetY()+4);
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("L.A.B: ". $info->lab),0,0,'L'); */
                $pdf->SetXY(130,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                //$pdf->Cell(77,7,utf8_decode("Agente: ".$nameCustomers[0]['nickname']),0,0,'L');
                /* foreach ($tb2 as $value){
                    if($value['iva_val'] == 'SI'){
                        $iva = (floatval($value["totaliva"]));
                        $totalIva += (floatval($iva));
                    }
                } */
                
                /* $pdf->SetXY(147,$pdf->GetY());
                $pdf->Cell(31, 6, "Neto:", 0, 0, 'R', true);
                $pdf->SetXY(178,$pdf->GetY());
                $pdf->SetTextColor(0);
                $pdf->Cell(27, 6, "$ ".number_format($totalcost,2, '.', ',')."", 0, 0, 'R', true); */
                
                //$pdf->Footer();

        $pdf->SetTitle('Cotización Orden #'.$id,true);
        /* $pdf->Output('I', "Nota", true);

            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Nota"'); */
        return $pdf;

    }
}

class SaleNotesPdfControllerPurchase extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $invoiceId;
    var $branchOffice;
    var $saleDate;
    var $textColors;
    var $drawEdge = true;
    var $fillCell = false;
    var $serial = 0;
    var $quote;
    function Header()
    {
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,10,8,50,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-105),10);
        $this->SetFont('Arial','B',12);
        /* $this->SetTextColor(255,255,255);*/
        $this->SetFillColor(255,255,255); 
        $this->Cell(77,2,utf8_decode('EMPRESA SA DE CV'),0,1,'C',1);
        //$this->SetXY(($this->GetPageWidth()),15);
        //$this->SetTextColor(81,106,53);
        $this->SetXY(($this->GetPageWidth()-105),12);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('20 DE NOVIEMBRE # 515 OTE. ZONA CENTRO'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),16);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('TELEFONO: (618)817 0585'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),20);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('ventas@empresa.mx'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),24);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('DURANGO, Dgo.'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),29);
        $this->SetFont('Arial','B',16);
        $this->Cell(98,7,utf8_decode($this->quote),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),35);
        $this->SetFont('Arial','B',16);
        $this->Cell(67,7,utf8_decode('#  '),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),35);
        $this->SetFont('Nunito-Regular','',15);
        $this->Cell(77,7,' '.$this->serial,0,0,'R'); 
        
    }
    function imgLat(){
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'Optibelt.png';
        $this->Image($img,10,32,25,0,'PNG');
        
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img2 = $path . 'Koyo.png';
        $this->Image($img2,10,60,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img3 = $path . 'Dodge.png';
        $this->Image($img3,10,90,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img4 = $path . 'Continental.png';
        $this->Image($img4,10,120,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img5 = $path . 'DIXON.png';
        $this->Image($img5,10,150,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img6 = $path . 'Loctite.png';
        $this->Image($img6,10,180,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img7 = $path . 'CAST NYLONS.png';
        $this->Image($img7,10,210,25,0,'PNG');

        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img8 = $path . 'Covalca.png';
        $this->Image($img8,10,240,25,0,'PNG');
    }
    function CustomHeader($serial)
    {
        
        //$this->SetXY(($this->GetPageWidth()),20);
        /* $this->SetTextColor(0,0,0);
        $this->SetFont('Nunito-Regular','',10);
        $this->SetXY(($this->GetPageWidth() - 66),15);
        $this->Cell(0,10,utf8_decode($serial),0,0,'L');
        $this->SetXY(($this->GetPageWidth() - 66),20);
        $this->Cell(0,10,date('d').'/'.date('m').'/'.date('Y'),0,0,'L'); */
    }

    function AutorizeHeader($serial)
    {
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,20,-5,65,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-105),10);
        $this->SetFont('Nunito-Regular','',12);
        $this->SetTextColor(255,255,255);
        $this->SetFillColor(181,226,113);
        $this->Cell(95,6,utf8_decode('PEDIDO AUTORIZADO'),0,1,'C',1);
        $this->SetXY(($this->GetPageWidth()),15);
        $this->SetTextColor(81,106,53);
        $this->Cell(-91,10,utf8_decode('FOLIO'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()),20);
        $this->Cell(-88,10,utf8_decode('FECHA '),0,0,'R');
        $this->SetTextColor(0,0,0);
        $this->SetFont('Nunito-Regular','',10);
        $this->SetXY(($this->GetPageWidth() - 66),15);
        $this->Cell(0,10,utf8_decode($serial),0,0,'L');
        $this->SetXY(($this->GetPageWidth() - 66),20);
        $this->Cell(0,10,date('d').'/'.date('m').'/'.date('Y'),0,0,'L');
    }

    function Footer()
    {   $this->imgLat();
        //$this->SetFillColor(181,226,113);
        /* $this->Rect(0,270,216,20,'F');
        $this->SetTextColor(21, 18, 46); */
        $this->SetY(240);
        // // $this->SetFont('Arial','B',9);
        // $this->SetFont('Nunito-Regular','',10);
        // $this->SetX(10);
        // $this->Cell(1, 8, "DATOS DE TRANSFERENCIA:", 0, 0, 'L', false);
        // $this->SetX(10);
        // $this->Cell(1, 18, "BBVA", 0, 0, 'L', false);
        // $this->SetX(30);
        // $this->Cell(1, 18, "Mexhaus S.A.S de C.V", 0, 0, 'L', false);
        // $this->SetX(75);
        // $this->Cell(1, 18, "Cuenta CLABE: 012 320 00115251656 2", 0, 0, 'L', false);
        // $this->SetX(150);
        // $this->Cell(10, 18, utf8_decode("Número de cuenta: 011 525 1656"), 0, 0, 'L', false);
        /* $this->SetFont('Arial','B',9);
        $this->SetX(10);
        $this->Cell(1, 28, "Aviso 1:", 0, 0, 'L', false);
        $this->SetX(10);
        $this->Cell(1, 38, "Aviso 2:", 0, 0, 'L', false); */
        $this->SetFont('Nunito-Regular','',7);
        $this->setX(10);
        $this->Cell(1, 31, utf8_decode("Transferencia: EMPRESA SA DE CV."));
        $this->setX(10);
        $this->Cell(1, 38, utf8_decode("BANCO NACIONAL DE MEXICO"));
        $this->setX(10);
        $this->Cell(1, 45, utf8_decode("CTA. 10900115B94 SUC. 109."));
        $this->setX(10);
        $this->Cell(1, 52, utf8_decode("CLABE. 002190010901158946."));
        $this->Ln();
        //$this->writeHTML('This is my disclaimer. <b>THESE WORDS NEED TO BE BOLD.</b> These words do not need to be bold.');
        //$this->SetY(260);
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
        //$this->SetFillColor(181,226,113);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('www.empresa.mx'),0,0,'L');
        $this->Ln();
    }

    /* function Footer2() {
        $this->SetFillColor(0);
        $this->Rect(0,245,216,190,'F');
        $this->SetTextColor(21, 18, 46);
        $this->SetY(245);
        // $this->SetFont('Arial','B',9);
        $this->SetFont('Nunito-Regular','',10);
        $this->SetX(10);
        $this->Cell(1, 8, "DATOS DE TRANSFERENCIA:", 0, 0, 'L', false);
        $this->SetX(10);
        $this->Cell(1, 18, "BBVA", 0, 0, 'L', false);
        $this->SetX(30);
        $this->Cell(1, 18, "Mexhaus S.A.S de C.V", 0, 0, 'L', false);
        $this->SetX(75);
        $this->Cell(1, 18, "Cuenta CLABE: 012 320 00115251656 2", 0, 0, 'L', false);
        $this->SetX(150);
        $this->Cell(10, 18, utf8_decode("Número de cuenta: 011 525 1656"), 0, 0, 'L', false);
        $this->SetFont('Arial','B',9);
        $this->SetX(10);
        $this->Cell(1, 28, "Aviso 1:", 0, 0, 'L', false);
        $this->SetX(10);
        $this->Cell(1, 38, "Aviso 2:", 0, 0, 'L', false);
        $this->SetFont('Nunito-Regular','',10);
        $this->setX(24);
        $this->Cell(1, 28, utf8_decode("Los pedidos son procesados una vez sea recibido y confirmado su pago."));
        $this->setX(24);
        $this->Cell(1, 38, utf8_decode("Todos los pedidos se surten en un lapso de 24-72 horas más el tiempo de entrega de las paqueterías"));
        $this->setX(24);
        $this->Cell(1, 45, utf8_decode("que en promedio es de 3 a 5 días hábiles."));
        $this->Ln();
        //$this->writeHTML('This is my disclaimer. <b>THESE WORDS NEED TO BE BOLD.</b> These words do not need to be bold.');
        $this->SetY(275);
        $this->SetFillColor(181,226,113);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('Mexhaus SAS de CV | 33.3687.8183 | hola@mexhaus.com | www.mexhaus.com                                   Página '.$this->PageNo().' de {nb}'),0,0,'L');
        $this->Ln();
    } */

    function SetQuote($w){
        $this->quote = $w;
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

    function SetInvoiceId($ii)
    {
        $this->invoiceId=$ii;
    }

    function SetBranchOffice($bo)
    {
        $this->branchOffice=$bo;
    }

    function SetSaleDate($sd)
    {
        $this->saleDate = $sd;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function SetTextColors($tc)
    {
        $this->textColors=$tc;
    }

    /* function Row($data)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=1*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            $this->Rect($x,$y,$w,$h);
            //Print the text
            $this->MultiCell($w,$h,$data[$i],0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    } */


    function Row($data,$fill)
    {
      $nb=0;
      for($i=0; $i<count($data); $i++)
        $nb=max($nb,$this->NbLines($this->widths[$i], $data[$i]));
      $h=10*$nb;
      $this->CheckPageBreak($h);
      for($i=0; $i<count($data); $i++)
      {
        $w=$this->widths[$i];
        $a=$this->aligns[$i];
        $x=$this->GetX();
        $y=$this->GetY();
        $this->SetFillColor(200,220,240);//135,180,223
        if ($fill)
          $this->Rect($x, $y, $w, $h, 'DF');
        else
          $this->Rect($x, $y, $w, $h);
        $this->MultiCell($w, 3, utf8_decode($data[$i]), 0, $a);
        $this->SetXY($x+$w, $y);
      }
      $this->Ln($h);
    }
//----------
/* function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        //var_dump($nb);
        
        $h=$this->height*$nb;
        //var_dump($h);
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
            if (is_array($this->textColors) && isset($this->textColors[$i])) {
                if (is_numeric($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i]);
                } elseif (is_array($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i][0], $this->textColors[$i][1], $this->textColors[$i][2]);
                } else {
                    $this->SetTextColor(0);
                }
            } else {
                $this->SetTextColor(0);
            }
            $this->MultiCell($w,6,utf8_decode($data[$i]),0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    } */

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h+20>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*10000/$this->FontSize;
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

Class ReportShoppingCartPDF extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $invoiceId;
    var $branchOffice;
    var $saleDate;
    var $textColors;
    var $drawEdge = true;
    var $fillCell = false;

    var $dateNow;

    var $titlepdf;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,15,10,65,0,'PNG');
        $this->SetTextColor(21, 18, 46);
        $this->SetFont('Arial','B',20);
        $this->SetX(40);
        $this->Cell(0, 30, utf8_decode("$this->titlepdf"), 0, 0, 'C');
        //$this->Ln();
        /* $this->Cell(0, 10, utf8_decode("SUCURSAL $this->branchOffice"), 0, 0, 'R'); */
        /* $this->Ln();
        $this->Cell(0, 10, $this->saleDate, 0, 0, 'R');
        $this->Ln(); */
    }
    function SetTitlePDF($title)
    {
        $this->titlepdf=$title;
    }
    public function setDateNow($date){
        $this->dateNow = $date;
    }

    function Footer()
    {
        $this->SetFont('Arial','B',15);
        $this->SetTextColor(21, 18, 46);
        $this->SetY(260);
        $this->SetY(257);
        //$this->Cell(195, 6, "HICIMOS LAS COSAS SIMPLES", 0, 0, 'C', false);
        $this->SetY(261);
        $this->Cell(195, 6, "www.empresa.mx", 0, 0, 'C', false);
        $this->SetY(274);
        $this->SetFont('Arial','',9);
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

    function SetInvoiceId($ii)
    {
        $this->invoiceId=$ii;
    }

    function SetBranchOffice($bo)
    {
        $this->branchOffice=$bo;
    }

    function SetSaleDate($sd)
    {
        $this->saleDate = $sd;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function SetTextColors($tc)
    {
        $this->textColors=$tc;
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
            if (is_array($this->textColors) && isset($this->textColors[$i])) {
                if (is_numeric($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i]);
                } elseif (is_array($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i][0], $this->textColors[$i][1], $this->textColors[$i][2]);
                } else {
                    $this->SetTextColor(0);
                }
            } else {
                $this->SetTextColor(0);
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