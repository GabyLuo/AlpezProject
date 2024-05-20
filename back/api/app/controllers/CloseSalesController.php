<?php

use Phalcon\Mvc\Controller;

class CloseSalesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCsvCloseSalesLoanPdf ($mydate) {
        date_default_timezone_set('America/Mexico_City');
        $validUser = Auth::getUserInfo($this->config);
        $content = $this->content; 
        $dateCurrent = date("Y-d-m");
        $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
            if (!is_null($mydate) && (date('Y-m-d', strtotime($mydate)) == $mydate)) {
                $where = " and to_char(slsinv.created, 'YYYY-MM-DD') = '$mydate' ";
            }
        $sql ="SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
        when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
        ELSE custom.payment_method END AS payment_method,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
        cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,
        to_char(slsinv.created, 'DD/MM/YYYY') as created,
        case when (shoppcart.tax_invoice = 0) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission
        from sls_invoices as slsinv
        inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
        inner join sls_customers as custom on custom.id = branchc.customer_id
        inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
        inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
        where  (slsinv.status = 'ENVIADO' or slsinv.status = 'REMISIONADO') and shoppcart.loan = 1 $where
        group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
        
        
        $myquery = $this->db->query($sql)->fetchAll();  
        $subtotal = 0;
        $totaliva = 0;
        $neto = 0;
        $credito = 0;
        $contado = 0; 
        $invoice = 0;
        $remission = 0; 
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['CLIENTE',utf8_decode('REMISIÓN'), utf8_decode('FECHA'),utf8_decode('NETO')], ',');
        if (count($myquery)) {
            foreach ($myquery as $value) {
                fputcsv($fp, [
                    strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]), 
                            utf8_decode($value["id"]),
                            utf8_decode($value["created"]),
                            "$ ".number_format($value["total"],2, '.', ',').""
                ], ',');

                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($value["total"]);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                $invoice += floatval($value["invoice"]);
                $remission += floatval($value["remission"]);
            }
            $content['result'] = 'success';
            fputcsv($fp, ['','','TOTAL:',"$ ".number_format($neto,2, '.', ',').""], ',');
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Pedidos por prestamo-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function closeSalesLoanPDF ($mydate) {
        date_default_timezone_set('America/Mexico_City');
        $validUser = Auth::getUserInfo($this->config);
        $pdf = new PDFCloseSales('P','mm','Letter');
                    $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
                    $pdf->AliasNbPages();
                    $pdf->setDateNow(date("d/m/Y"));
                    $pdf->SetTitlePDF("Pedidos por prestamo");
                    $pdf->SetBranchOffice("");
                    $pdf->SetSaleDate("");
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(false, 20);
                    // $pdf->SetFillColor(71, 130, 222);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetDrawColor(0);
                    $pdf->Ln();
                    $pdf->SetWidths(array(210));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetHeight(8);
                    // $pdf->SetFill(array(true));
                    $pdf->SetDrawEdge(true);
                    $pdf->SetTextColors(array([0, 0, 0]));

                    $header = array('CLIENTE',utf8_decode('REMISIÓN'),utf8_decode('FECHA'),utf8_decode('TOTAL'));
                    $pdf->SetXY(10,$pdf->GetY());
                    //25, 107, 210
                    $pdf->SetFillColor(128,179,240);
                    $pdf->SetTextColor(255,255,255);
                    $pdf->SetLineWidth(0);
                    //$pdf->SetFont('Arial','B',8);
                    $pdf->SetFont('Nunito-Regular','',8);
                    // Header
                    $x = 210;
                    $i = 0;
                    // $w=array(5,20,25,30,25,30,35,20,15);
                    $w=array(80,45,20,50);
                    foreach($header as $col) {
                        if($i<=3){
                            $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                        }
                        $x=$x+5;
                        $i++;
                    }
                    $pdf->Ln();
                    $dateCurrent = date("Y-d-m");
                    $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
                        if (!is_null($mydate) && (date('Y-m-d', strtotime($mydate)) == $mydate)) {
                            $where = " and to_char(slsinv.created, 'YYYY-MM-DD') = '$mydate' ";
                        }
                    $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
                    when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
                    ELSE custom.payment_method END AS payment_method,
                    cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
                    to_char(slsinv.created, 'DD/MM/YYYY') as created,
                    case when (shoppcart.tax_invoice = 0) then
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
                    case when (shoppcart.tax_invoice = 1) then
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission
                    from sls_invoices as slsinv
                    inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
                    inner join sls_customers as custom on custom.id = branchc.customer_id
                    inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
                    inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
                    where  (slsinv.status = 'ENVIADO' or slsinv.status = 'REMISIONADO') and shoppcart.loan = 1 $where
                    group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
                    
                    
                    $query = $this->db->query($sql)->fetchAll();
                    $fill = false;
                    //$pdf->SetFont('Nunito-Regular','',7);
                    $pdf->SetWidths(array(80,45,20,50));
                    $pdf->SetAligns(array('L', 'C','C','R')); 
                    
                    $subtotal = 0;
                    $totaliva = 0;
                    $neto = 0;
                    $credito = 0;
                    $contado = 0;
                    $invoice = 0;
                    $remission = 0;
                    foreach ($query as $value) {
                        
                        if ($pdf->getY() >= 193) {
                            $pdf->AddPage();
                            $pdf->SetXY(10,57);
                            
                        } else {
                            $pdf->SetXY(10,$pdf->GetY());
                        }
                        $pdf->Row(array(
                            strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]),
                            utf8_decode($value["id"]), 
                            utf8_decode($value["created"]), 
                            "$ ".number_format($value["total"],2, '.', ',')."",
                        ),$fill);
                        $subtotal += floatval($value["subtotal"]);
                        $totaliva += floatval($value["iva"]);
                        $neto += floatval($value["total"]);
                        $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                        $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                        $invoice += floatval($value["invoice"]);
                        $remission += floatval($value["remission"]);
                    }

                    $pdf->SetXY(135,$pdf->GetY());
                    $pdf->Cell(20,6,"Total:",1,0,'R');
                    /* $pdf->Cell(20,6,"$ ".number_format($subtotal,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($totaliva,2, '.', ',')."",1,0,'R'); */
                    $pdf->Cell(50,6,"$ ".number_format($neto,2, '.', ',')."",1,0,'R');
                    /* $pdf->Cell(20,6,"$ ".number_format($remission,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($invoice,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($credito,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($contado,2, '.', ',')."",1,0,'R'); */

                    $pdf->SetTitle(utf8_decode('Pedidos por prestamo'));
                    header("Access-Control-Allow-Origin: *");
                    header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', 'reporte_pagos.pdf', true);

        return $pdf;
    }

    public function getCloseSalesLoan ($getDate) {
        date_default_timezone_set('America/Mexico_City');
        if($this->userHasPermission()){
            $validUser = Auth::getUserInfo($this->config);
            date_default_timezone_set('America/Mexico_City');
            $dateCurrent = date("Y-d-m");
            
            $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
            if (!is_null($getDate) && (date('Y-m-d', strtotime($getDate)) == $getDate)) {
                $where = " and to_char(slsinv.created, 'YYYY-MM-DD') = '$getDate' ";
            }
            $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
            when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
            ELSE custom.payment_method END AS payment_method,
            cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
            sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
            sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,
            to_char(slsinv.created, 'DD/MM/YYYY') as created,
            concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
			case when (shoppcart.tax_invoice = 0) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission
            from sls_invoices as slsinv
            inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
            inner join sls_customers as custom on custom.id = branchc.customer_id
            inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
            inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
            where  (slsinv.status = 'ENVIADO' or slsinv.status = 'REMISIONADO') and shoppcart.loan = 1 $where
            group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
            $data = $this->db->query($sql)->fetchAll();
            $array = [];
            $subtotal = 0;
            $totaliva = 0;
            $neto = 0;
            $credito = 0;
            $contado = 0; 
            $invoice = 0;
            $remission = 0;
            foreach ($data as $value) {
                array_push($array, array('name' => $value['name'],'id' => $value["id"],'subtotal' => $value["subtotal"],'iva' => $value["iva"], 'total' => $value["total"],'payment_method' => $value["payment_method"], 'folio_fiscal' => $value["folio_fiscal"], 'invoice' => $value["invoice"],'remission' => $value["remission"], 'date' => $value['created']));
               
                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($value["total"]);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                $invoice += floatval($value["invoice"]);
                $remission += floatval($value["remission"]);
            }
            array_push($array, array('name' => '','id' => '','subtotal' => $subtotal,'total' => 'TOTAL', 'neto' => $neto, 'credit' => $credito, 'counted' => $contado, 'countremission' => $remission, 'countinvoice' => $invoice, 'iva' => $totaliva));
            
            $this->content['closeSale'] = $array;
            $this->content['result'] = true;
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getCloseSalesRemission ($getDate) {
        date_default_timezone_set('America/Mexico_City');
        if($this->userHasPermission()){
            $validUser = Auth::getUserInfo($this->config);
            $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
            date_default_timezone_set('America/Mexico_City');
            $dateCurrent = date("Y-d-m");
            $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
            when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
            ELSE custom.payment_method END AS payment_method,
            cast(sum(shinbulk.qty * shinbulk.price_product) as numeric) as subtotal,
            sum((shinbulk.qty * shinbulk.price_product) * 0.16) as iva,
            sum((shinbulk.qty * shinbulk.price_product) * 0.16 + (shinbulk.qty * shinbulk.price_product)) as total,
            slsinv.created,
            concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
			case when (shoppcart.tax_invoice = 0) then
			sum((shinbulk.qty * shinbulk.price_product) * 0.16 + (shinbulk.qty * shinbulk.price_product)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((shinbulk.qty * shinbulk.price_product) * 0.16 + (shinbulk.qty * shinbulk.price_product)) else 0 end as remission
            from sls_invoices as slsinv
            inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
            inner join sls_customers as custom on custom.id = branchc.customer_id
            inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
            inner join sls_shopping_cart_in_bulk_details as shinbulk on shinbulk.shopping_cart_id = shoppcart.id
            where to_char(slsinv.created, 'YYYY-MM-DD') = '$getDate' and slsinv.status = 'ENVIADO' and shoppcart.loan = 0 and shoppcart.tax_invoice = 1 and shinbulk.status = 'REMISIONADO' 
            $where
            group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
            $data = $this->db->query($sql)->fetchAll();
            $array = [];
            $subtotal = 0;
            $totaliva = 0;
            $neto = 0;
            $credito = 0;
            $contado = 0; 
            $invoice = 0;
            $remission = 0;
            foreach ($data as $value) {
                array_push($array, array('name' => $value['name'],'id' => $value["id"],'subtotal' => $value["subtotal"],'iva' => $value["iva"], 'total' => $value["total"],'payment_method' => $value["payment_method"], 'folio_fiscal' => $value["folio_fiscal"], 'invoice' => $value["invoice"],'remission' => $value["remission"]));
                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($value["total"]);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                $invoice += floatval($value["invoice"]);
                $remission += floatval($value["remission"]);
            }
            array_push($array, array('name' => '','id' => '','subtotal' => $subtotal,'total' => 'TOTAL', 'neto' => $neto, 'credit' => $credito, 'counted' => $contado, 'countremission' => $remission, 'countinvoice' => $invoice, 'iva' => $totaliva));
            
            $this->content['closeSale'] = $array;
            $this->content['result'] = true;
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getCsvCloseSales ($mydate, $remission) {
        date_default_timezone_set('America/Mexico_City');
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
        $content = $this->content; 
        $dateCurrent = date("Y-d-m");
        $typeremission = $remission;
        if ($remission == 'si') {
        $sql ="SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
        when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
        ELSE custom.payment_method END AS payment_method,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
        cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,
        slsinv.created,
        case when (shoppcart.tax_invoice = 0) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission
        from sls_invoices as slsinv
        inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
        inner join sls_customers as custom on custom.id = branchc.customer_id
        inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
        inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
        where to_char(slsinv.created, 'YYYY-MM-DD') = '$mydate' and slsinv.status = 'ENVIADO' and shoppcart.loan = 0 and shoppcart.tax_invoice = 1 
        $where
        group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
        }
        if ($remission == 'no') {
            $sql ="SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
        when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
        ELSE custom.payment_method END AS payment_method,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
        cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,
        slsinv.created,
        case when (shoppcart.tax_invoice = 0) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission,
            dtf.regimen_fiscal as regimen
        from sls_invoices as slsinv
        inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
        inner join sls_customers as custom on custom.id = branchc.customer_id
        inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
        inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
        inner join sls_customer_tax_companies as dtf on dtf.id =  slsinv.tax_company_id
        where to_char(slsinv.fecha_factura, 'YYYY-MM-DD') = '$mydate' and slsinv.status = 'ENVIADO' and slsinv.status_timbrado = 1 and shoppcart.loan = 0 
        $where
        group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice, dtf.regimen_fiscal ORDER BY slsinv.created ASC";
        }
        $myquery = $this->db->query($sql)->fetchAll();  
        $subtotal = 0;
        $totaliva = 0;
        $neto = 0;
        $credito = 0;
        $contado = 0; 
        $invoice = 0;
        $remission = 0; 
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        if ($typeremission == "si"){
            fputcsv($fp, ['NOMBRE DEL CLIENTE',utf8_decode('NO. REMISIÓN'),'SUBTOTAL','IVA',utf8_decode('NETO'),utf8_decode('TICKET'),utf8_decode('FACTURADO'),utf8_decode('CREDITO'),utf8_decode('CONTADO')], ',');
        if (count($myquery)) {
            foreach ($myquery as $value) {
                fputcsv($fp, [
                    strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]), 
                            utf8_decode($value["id"]),
                            "$ ".number_format($value["subtotal"],2, '.', ',')."",
                            "$ ".number_format($value["iva"],2, '.', ',')."",
                            "$ ".number_format($value["total"],2, '.', ',')."",
                            "$ ".number_format($value["remission"],2, '.', ',')."",
                            "$ ".number_format($value["invoice"],2, '.', ',')."",
                            "$ ".number_format($value["payment_method"] == "CREDITO" ? $value["total"] : '0',2, '.', ',')."",
                            "$ ".number_format($value["payment_method"] == "CONTADO" ? $value["total"] : '0',2, '.', ',').""
                ], ',');

                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($value["total"]);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                $invoice += floatval($value["invoice"]);
                $remission += floatval($value["remission"]);
            }
            $content['result'] = 'success';
            fputcsv($fp, ['','TOTAL:',"$ ".number_format($subtotal,2, '.', ',')."","$ ".number_format($totaliva,2, '.', ',')."","$ ".number_format($neto,2, '.', ',')."","$ ".number_format($remission,2, '.', ',')."","$ ".number_format($invoice,2, '.', ',')."","$ ".number_format($credito,2, '.', ',')."","$ ".number_format($contado,2, '.', ',').""], ',');
        }
        }
        if ($typeremission == "no"){
            fputcsv($fp, ['NOMBRE DEL CLIENTE',utf8_decode('NO. REMISIÓN'),utf8_decode('FOLIO FISCAL'),'SUBTOTAL','IVA',utf8_decode('NETO'),utf8_decode('TICKET'),utf8_decode('FACTURADO'),utf8_decode('CREDITO'),utf8_decode('CONTADO')], ',');
        if (count($myquery)) {
            $totalwhitRegimen = 0;
            $totalinvoice = 0;
            foreach ($myquery as $value) {
                if ($value['regimen'] == 626) {
                    // se deja asi por mientras
                    //$totalwhitRegimen = $value["total"]-($value["total"] * 0.0125);
                    $totalwhitRegimen = $value["total"];
                }else{
                    $totalwhitRegimen = $value["total"];
                }
                if ($value['regimen'] == 626) {
                    // se deja asi por mientras
                    //$totalinvoice = $value["invoice"]-($value["invoice"] * 0.0125);
                    $totalinvoice = $value["invoice"];
                }else{
                    $totalinvoice = $value["invoice"];
                }
                fputcsv($fp, [
                    strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]), 
                            utf8_decode($value["id"]),
                            utf8_decode($value["folio_fiscal"]),
                            "$ ".number_format($value["subtotal"],2, '.', ',')."",
                            "$ ".number_format($value["iva"],2, '.', ',')."",
                            "$ ".number_format($totalwhitRegimen,2, '.', ',')."",
                            "$ ".number_format($value["remission"],2, '.', ',')."",
                            "$ ".number_format($totalinvoice,2, '.', ',')."",
                            "$ ".number_format($value["payment_method"] == "CREDITO" ? $totalwhitRegimen : '0',2, '.', ',')."",
                            "$ ".number_format($value["payment_method"] == "CONTADO" ? $totalwhitRegimen : '0',2, '.', ',').""
                ], ',');

                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($totalwhitRegimen);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $totalwhitRegimen : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $totalwhitRegimen : 0);
                $invoice += floatval($totalinvoice);
                $remission += floatval($value["remission"]);
            }
            $content['result'] = 'success';
            fputcsv($fp, ['','','TOTAL:',"$ ".number_format($subtotal,2, '.', ',')."","$ ".number_format($totaliva,2, '.', ',')."","$ ".number_format($neto,2, '.', ',')."","$ ".number_format($remission,2, '.', ',')."","$ ".number_format($invoice,2, '.', ',')."","$ ".number_format($credito,2, '.', ',')."","$ ".number_format($contado,2, '.', ',').""], ',');
        }
        }
        
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Cierre-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getCloseSales ($getDate) {
        date_default_timezone_set('America/Mexico_City');
        if($this->userHasPermission()){
            $validUser = Auth::getUserInfo($this->config);
            $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
            date_default_timezone_set('America/Mexico_City');
            $dateCurrent = date("Y-d-m");
            $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
            when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
            ELSE custom.payment_method END AS payment_method,
            cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
            sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
            sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,
            slsinv.created,
            concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
			case when (shoppcart.tax_invoice = 0) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
			case when (shoppcart.tax_invoice = 1) then
			sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission,
            dtf.regimen_fiscal as regimen
            from sls_invoices as slsinv
            inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
            inner join sls_customers as custom on custom.id = branchc.customer_id
            inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
            inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
            inner join sls_customer_tax_companies as dtf on dtf.id =  slsinv.tax_company_id
            where to_char(slsinv.fecha_factura, 'YYYY-MM-DD') = '$getDate' and slsinv.status = 'ENVIADO' and slsinv.status_timbrado = 1 and shoppcart.loan = 0
            $where
            group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice, dtf.regimen_fiscal ORDER BY slsinv.created ASC";
            
            $data = $this->db->query($sql)->fetchAll();
            
            $array = [];
            $subtotal = 0;
            $totaliva = 0;
            $neto = 0;
            $credito = 0;
            $contado = 0; 
            $invoice = 0;
            $remission = 0;
            $totalwhitRegimen = 0;
            $totalinvoice = 0;
            foreach ($data as $value) {
                if ($value['regimen'] == 626) {
                    //$totalwhitRegimen = $value["total"]-($value["total"] * 0.0125);
                    // se deja asi por mientras
                    $totalwhitRegimen = $value["total"];
                }else{
                    $totalwhitRegimen = $value["total"];
                }
                if ($value['regimen'] == 626) {
                    // se deja asi por mientras
                    //$totalinvoice = $value["invoice"]-($value["invoice"] * 0.0125);
                    $totalinvoice = $value["invoice"];
                }else{
                    $totalinvoice = $value["invoice"];
                }
                array_push($array, array('name' => $value['name'],'id' => $value["id"],'subtotal' => $value["subtotal"],'iva' => $value["iva"], 'total' => $totalwhitRegimen,'payment_method' => $value["payment_method"], 'folio_fiscal' => $value["folio_fiscal"], 'invoice' => $totalinvoice,'remission' => $value["remission"]));
                $subtotal += floatval($value["subtotal"]);
                $totaliva += floatval($value["iva"]);
                $neto += floatval($totalwhitRegimen);
                $credito += floatval($value["payment_method"] == "CREDITO" ? $totalwhitRegimen : 0);
                $contado += floatval($value["payment_method"] == "CONTADO" ? $totalwhitRegimen : 0);
                $invoice += floatval($totalinvoice);
                $remission += floatval($value["remission"]);
            }
            array_push($array, array('name' => '','id' => '','subtotal' => $subtotal,'total' => 'TOTAL', 'neto' => $neto, 'credit' => $credito, 'counted' => $contado, 'countremission' => $remission, 'countinvoice' => $invoice, 'iva' => $totaliva));
            
            $this->content['closeSale'] = $array;
            $this->content['result'] = true;
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function closeSales ($mydate, $remission) {
        $typepdf = $remission;
        date_default_timezone_set('America/Mexico_City');
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : ' and shoppcart.branchoffice = '.$validUser->branch_office_id;
        $pdf = new PDFCloseSales('L','mm','Letter');
                    $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
                    $pdf->AliasNbPages();
                    $pdf->setDateNow(date("d/m/Y"));
                    $pdf->SetTitlePDF($remission == "si" ? ("Corte de caja remisión") : ("Corte de caja"));
                    $pdf->SetBranchOffice("");
                    $pdf->SetSaleDate("");
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(false, 20);
                    // $pdf->SetFillColor(71, 130, 222);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetDrawColor(0);
                    $pdf->Ln();
                    $pdf->SetWidths(array(210));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetHeight(8);
                    // $pdf->SetFill(array(true));
                    $pdf->SetDrawEdge(true);
                    $pdf->SetTextColors(array([0, 0, 0]));

                    if ($remission == 'si') {
                        $header = array('NOMBRE DEL CLIENTE',utf8_decode('REMISIÓN'),'SUBTOTAL','IVA',utf8_decode('NETO'),utf8_decode('TICKET'),utf8_decode('FACTURADO'),utf8_decode('CREDITO'),utf8_decode('CONTADO'));
                    }
                    if ($remission == 'no') {
                        $header = array('NOMBRE DEL CLIENTE',utf8_decode('REMISIÓN'),utf8_decode('FISCAL'),'SUBTOTAL','IVA',utf8_decode('NETO'),utf8_decode('TICKET'),utf8_decode('FACTURADO'),utf8_decode('CREDITO'),utf8_decode('CONTADO'));
                    }
                    
                    $pdf->SetXY(10,$pdf->GetY());
                    //25, 107, 210
                    $pdf->SetFillColor(128,179,240);
                    $pdf->SetTextColor(255,255,255);
                    $pdf->SetLineWidth(0);
                    //$pdf->SetFont('Arial','B',8);
                    $pdf->SetFont('Nunito-Regular','',8);
                    // Header
                    $x = 250;
                    $i = 0;
                    // $w=array(5,20,25,30,25,30,35,20,15);
                    if ($remission == 'si') {
                        $w=array(70,40,20,20,20,20,20,20,20);
                    }
                    if ($remission == 'no') {
                        $w=array(60,25,30,20,20,20,20,20,20,20);
                    }
                    
                    foreach($header as $col) {
                        if($i<=9){
                            $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                        }
                        $x=$x+5;
                        $i++;
                    }
                    $pdf->Ln();
                    $dateCurrent = date("Y-d-m");
                    if ($remission == 'si') {
                    $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
                    when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
                    ELSE custom.payment_method END AS payment_method,
                    cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
                    slsinv.created,
                    case when (shoppcart.tax_invoice = 0) then
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
                    case when (shoppcart.tax_invoice = 1) then
                    sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission
                    from sls_invoices as slsinv
                    inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
                    inner join sls_customers as custom on custom.id = branchc.customer_id
                    inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
                    inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
                    where to_char(slsinv.created, 'YYYY-MM-DD') = '$mydate' and slsinv.status = 'ENVIADO' and shoppcart.loan = 0 and shoppcart.tax_invoice = 1
                    $where
                    group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice ORDER BY slsinv.created ASC";
                    //var_dump($sql);
                    }
                    if ($remission == 'no') {
                        $sql = "SELECT slsinv.id, custom.name, case when slsinv.metodo_pago = 'PUE' then 'CONTADO' 
                        when slsinv.metodo_pago = 'PPD' then 'CREDITO' 
                        ELSE custom.payment_method END AS payment_method,
                        cast(sum(invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) as numeric) as subtotal,
                        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16) as iva,
                        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) as total,concat(slsinv.serie,'-',slsinv.folio_fiscal) folio_fiscal,
                        slsinv.created,
                        case when (shoppcart.tax_invoice = 0) then
                        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as invoice,
                        case when (shoppcart.tax_invoice = 1) then
                        sum((invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price) * 0.16 + (invoiceinbulkdetails.qty * invoiceinbulkdetails.unit_price)) else 0 end as remission,
                        dtf.regimen_fiscal as regimen
                        from sls_invoices as slsinv
                        inner join sls_customer_branch_offices as branchc on branchc.id = slsinv.customer_branch_office_id
                        inner join sls_customers as custom on custom.id = branchc.customer_id
                        inner join sls_shopping_cart as shoppcart on shoppcart.id = slsinv.shopping_cart_id
                        inner join sls_invoice_in_bulk_details as invoiceinbulkdetails on invoiceinbulkdetails.invoice_id = slsinv.id
                        inner join sls_customer_tax_companies as dtf on dtf.id =  slsinv.tax_company_id
                        where to_char(slsinv.fecha_factura, 'YYYY-MM-DD') = '$mydate' and slsinv.status = 'ENVIADO' and slsinv.status_timbrado = 1 and shoppcart.loan = 0
                        $where
                        group by slsinv.id, custom.name, custom.payment_method, shoppcart.tax_invoice, dtf.regimen_fiscal ORDER BY slsinv.created ASC";
                        
                        }
                    $query = $this->db->query($sql)->fetchAll();
                    $fill = false;
                    //$pdf->SetFont('Nunito-Regular','',7);
                    if ($remission == 'si') {
                        $pdf->SetWidths(array(70,40,20,20,20,20,20,20,20));
                    $pdf->SetAligns(array('C', 'C','R','R','R','R','R','R','R')); 
                    }
                    if ($remission == 'no') {
                        $pdf->SetWidths(array(60,25,30,20,20,20,20,20,20,20));
                    $pdf->SetAligns(array('C', 'C','C','R','R','R','R','R','R','R'));
                    }
                    
                    $subtotal = 0;
                    $totaliva = 0;
                    $neto = 0;
                    $credito = 0;
                    $contado = 0;
                    $invoice = 0;
                    $remission = 0;
                    if ($typepdf == 'si') {
                        foreach ($query as $value) {
                            
                            if ($pdf->getY() >= 193) {
                                $pdf->AddPage();
                                $pdf->SetXY(10,57);
                                
                            } else {
                                $pdf->SetXY(10,$pdf->GetY());
                            }
                            $pdf->Row(array(
                                strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]),
                                utf8_decode($value["id"]),
                                "$ ".number_format($value["subtotal"],2, '.', ',')."",
                                "$ ".number_format($value["iva"],2, '.', ',')."",
                                "$ ".number_format($value["total"],2, '.', ',')."",
                                "$ ".number_format($value["remission"],2, '.', ',')."",
                                "$ ".number_format($value["invoice"],2, '.', ',')."",
                                "$ ".number_format($value["payment_method"] == "CREDITO" ? $value["total"] : '0',2, '.', ',')."",
                                "$ ".number_format($value["payment_method"] == "CONTADO" ? $value["total"] : '0',2, '.', ',').""
                            ),$fill);
                            $subtotal += floatval($value["subtotal"]);
                            $totaliva += floatval($value["iva"]);
                            $neto += floatval($value["total"]);
                            $credito += floatval($value["payment_method"] == "CREDITO" ? $value["total"] : 0);
                            $contado += floatval($value["payment_method"] == "CONTADO" ? $value["total"] : 0);
                            $invoice += floatval($value["invoice"]);
                            $remission += floatval($value["remission"]);
                        }
                        $pdf->SetXY(80,$pdf->GetY());
                    $pdf->Cell(40,6,"Total:",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($subtotal,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($totaliva,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($neto,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($remission,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($invoice,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($credito,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($contado,2, '.', ',')."",1,0,'R');
                    }

                    if ($typepdf == 'no') {
                        $totalwhitRegimen = 0;
                        $totalinvoice = 0;
                        foreach ($query as $value) {
                            
                            if ($pdf->getY() >= 193) {
                                $pdf->AddPage();
                                $pdf->SetXY(10,57);
                                
                            } else {
                                $pdf->SetXY(10,$pdf->GetY());
                            }
                            if ($value['regimen'] == 626) {
                                // se deja asi por mientras
                                // $totalwhitRegimen = $value["total"]-($value["total"] * 0.0125);
                                $totalwhitRegimen = $value["total"];
                            }else{
                                $totalwhitRegimen = $value["total"];
                            }
                            if ($value['regimen'] == 626) {
                                // Se deja asi por mientras
                                //$totalinvoice = $value["invoice"]-($value["invoice"] * 0.0125);
                                $totalinvoice = $value["invoice"];
                            }else{
                                $totalinvoice = $value["invoice"];
                            }
                            $pdf->Row(array(
                                strlen(utf8_decode($value["name"])) > 28 ? substr(utf8_decode($value["name"]), 0, 28) : utf8_decode($value["name"]),
                                utf8_decode($value["id"]),
                                utf8_decode($value["folio_fiscal"]),  
                                "$ ".number_format($value["subtotal"],2, '.', ',')."",
                                "$ ".number_format($value["iva"],2, '.', ',')."",
                                "$ ".number_format($totalwhitRegimen,2, '.', ',')."",
                                "$ ".number_format($value["remission"],2, '.', ',')."",
                                "$ ".number_format($totalinvoice,2, '.', ',')."",
                                "$ ".number_format($value["payment_method"] == "CREDITO" ? $totalwhitRegimen : '0',2, '.', ',')."",
                                "$ ".number_format($value["payment_method"] == "CONTADO" ? $totalwhitRegimen : '0',2, '.', ',').""
                            ),$fill);
                            $subtotal += floatval($value["subtotal"]);
                            $totaliva += floatval($value["iva"]);
                            $neto += floatval($totalwhitRegimen);
                            $credito += floatval($value["payment_method"] == "CREDITO" ? $totalwhitRegimen : 0);
                            $contado += floatval($value["payment_method"] == "CONTADO" ? $totalwhitRegimen : 0);
                            $invoice += floatval($totalinvoice);
                            $remission += floatval($value["remission"]);
                            $totalwhitRegimen = 0;
                        }
                        $pdf->SetXY(95,$pdf->GetY());
                    $pdf->Cell(30,6,"Total:",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($subtotal,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($totaliva,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($neto,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($remission,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($invoice,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($credito,2, '.', ',')."",1,0,'R');
                    $pdf->Cell(20,6,"$ ".number_format($contado,2, '.', ',')."",1,0,'R');
                    }
                    

                    $pdf->SetTitle($typepdf == "si" ? utf8_decode("Corte de caja remisión") : "Corte de caja");
                    header("Access-Control-Allow-Origin: *");
                    header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', $typepdf == "si" ? utf8_decode("Corte_de_caja_remisión") : "Corte_de_caja", true);

        return $pdf;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 7 OR role_id = 17 OR role_id = 28 OR role_id = 20 OR role_id = 22 OR role_id = 24 OR role_id = 25 OR role_id = 27 OR role_id = 28 OR role_id = 29)
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

Class PDFCloseSales extends FPDF
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
        $this->SetFont('Arial','B',16);
        $this->Cell(0, 10, utf8_decode("$this->titlepdf: $this->dateNow"), 0, 0, 'R');
        $this->Ln();
        /* $this->Cell(0, 10, utf8_decode("SUCURSAL $this->branchOffice"), 0, 0, 'R'); */
        $this->Ln();
        $this->Cell(0, 10, $this->saleDate, 0, 0, 'R');
        $this->Ln();
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
