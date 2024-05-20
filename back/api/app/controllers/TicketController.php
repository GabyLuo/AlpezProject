<?php
use Luecano\NumeroALetras\NumeroALetras;
use Phalcon\Mvc\Controller;

class TicketController extends Controller
{
    public function crearTicket($id,$user) {

        $pdfFile = new FPDF('P','mm',array(80,150)); // Tamaño tickt 80mm x 150 mm (largo aprox)
        $pdfFile = $this->getTicket($pdfFile,$id,$user);
        $pdfFile->Output('I', 'ticket.pdf', true);

    }
    private function getTicket($pdf,$id,$user)
    {
        define('PESO',chr(36)); 
        $u = Users::findFirst($user);
        $getIdShoppingCart = "SELECT shopping_cart_id FROM  sls_invoices WHERE id = $id";
        $queryIdShoppingCart = $this->db->query($getIdShoppingCart)->fetchAll();
        $shoppingCartDetails = $this->queryBulkForDetailShoppingCart($queryIdShoppingCart[0]["shopping_cart_id"]);
        $inBulkDetails = $this->queryBulkForDetailShoppingCartinBulk($queryIdShoppingCart[0]["shopping_cart_id"]);
        $slsInvoicesInbulk = $this->querySlsInvoicesInbulkDetails($id);
        /*echo("<pre>");
        print_r($shoppingCartDetails);
        exit();*/


        // print_r($id);
        // exit();
        /*$client = Clients::findFirst($user_id);

        $shopping = Shopping::findFirst($id);
        $shoppingDetail = ShoppingDetail::find(
            [
                'conditions' => 'shopping_cart_id = ?1',
                'bind'       => [
                    1 => $id
                ]
            ]
        );*/
        //$user = Users::findFirst($shopping->user_id);

        //$branchoffice = Offices::findFirst( $user->branchoffice_id);

        //$line = $shoppingDetail->count()*8;
        //$height =  $line + 110;
        
        $pdf->AddPage('P',array(80,150));
        // $pdf->AddFont('Arial','','Arial.php');
        $pdf->SetLeftMargin(5);
        $pdf->SetFont('Arial', 'B', 7);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, ($pdf->GetPageWidth() / 2)-18, 5, 40,20);
        }
        
        // CABECERA

        $pdf->SetY(25);
        $pdf->SetFont('Arial', 'B', 7);
        if($shoppingCartDetails[0]['sucursal_id'] == 9){
        $pdf->Cell(70,4,'BALEROS RETENES  Y BANDAS DE DURANGO SA DE CV',0,1,'C');
        $pdf->Cell(70,4,'20 DE NOVIEMBRE #515 OTE',0,1,'C');
        $pdf->Cell(70,4,'ZONA CENTRO C.P 34000 DURANGO, DGO',0,1,'C');
        $pdf->Cell(70,4,'RFC: BRB780222GD',0,1,'C');
        $pdf->Cell(70,4,'TICKET DE VENTA  #'.$inBulkDetails[0]['invoice_id'],0,1,'C');
        }elseif($shoppingCartDetails[0]['sucursal_id'] == 12){
        $pdf->Cell(70,4,'LOPEZ DE LARA TINAJERO GUILLERMO',0,1,'C');
        $pdf->Cell(70,4,'FRANCISCA ESCARZAGA # 500',0,1,'C');
        $pdf->Cell(70,4,'COL. SANTA FE C.P 34240 DURANGO, DGO',0,1,'C');
        $pdf->Cell(70,4,'RFC: LOTG541005G9A',0,1,'C');
        $pdf->Cell(70,4,'TICKET DE VENTA  #'.$inBulkDetails[0]['invoice_id'],0,1,'C');
        }elseif($shoppingCartDetails[0]['sucursal_id'] == 13){
        $pdf->Cell(70,4,'EMPRESA SA DE CV.',0,1,'C');
        $pdf->Cell(70,4,'BLVD GUADIANA #410',0,1,'C');
        $pdf->Cell(70,4,'FRACC. LA ESMERALDA',0,1,'C');
        $pdf->Cell(70,4,'C.P 34139 DURANGO, DGO',0,1,'C');
        $pdf->Cell(70,4,'RFC: RRM010601UV1',0,1,'C');
        $pdf->Cell(70,4,'',0,1,'C');
        $pdf->Cell(70,4,'TICKET DE VENTA  #'.$inBulkDetails[0]['invoice_id'],0,1,'C');
        }elseif($shoppingCartDetails[0]['sucursal_id'] == 14){
        $pdf->Cell(70,4,'EMPRESA SA DE CV.',0,1,'C');
        $pdf->Cell(70,4,'ALUMINIO S/N',0,1,'C');
        $pdf->Cell(70,4,'FIDEICOMISO CIUDAD INDUSTRIAL C.P 34240 DURANGO, DGO',0,1,'C');
        $pdf->Cell(70,4,'RFC: RRM010601UV1',0,1,'C');
        $pdf->Cell(70,4,'TICKET DE VENTA  #'.$inBulkDetails[0]['invoice_id'],0,1,'C');
        }
        // DATOS FACTURA

        $fecha_actual = date("d/m/Y H:i:s");
        $folio = 'F';
        $pdf->Cell(16,4,'FECHA: ',0,0,'');
        $pdf->Cell(28,4,$inBulkDetails[0]['fechaactual'],0,0,'');
        $pdf->Cell(28,4,'VENTA #'.$shoppingCartDetails[0]['id'],0,1,'');
        $pdf->Cell(16,4,utf8_decode('CLIENTE: '),0,0,'');
        $pdf->Cell(60,4,utf8_decode($shoppingCartDetails[0]['customer_name']),0,1,'');
        $pdf->Cell(16,4,utf8_decode('VENDEDOR: '),0,0,'');
        $pdf->Cell(60,4,utf8_decode($shoppingCartDetails[0]['agent_name']),0,1,'');
        
        // COLUMNAS
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(20, 3, utf8_decode('ARTÍCULO'), 1);
        $pdf->Cell(20, 3, 'CANTIDAD',1,0,'C');
        $pdf->Cell(12, 3, 'PRECIO',1,0,'R');
        $pdf->Cell(17, 3, 'TOTAL',1,0,'R');
        $pdf->Ln(4);
        // $pdf->Cell(70,0,'','T');
        // $pdf->Ln(1);
        //

  
            /*foreach ($inBulkDetails as $details) {
                $qty = (float)$details['qty'];
                $price_list = (float)$details['price_product'];
                $subtotal = $details['price_product'] * $details['qty'];
                $iva = (float)$subtotal * .16;
                $total = $subtotal + $iva;
                $pdf->SetFont('Arial','',8);
                $pdf->SetTextColors(array(0,0,0,0,0,0,0));
                $pdf->SetFillColor(255);
                $pdf->Row(array(
                    utf8_decode($details['product']), 
                    utf8_decode($details['line']),
                    utf8_decode($details['category']), 
                    number_format($qty, 2, '.', ','),
                    $details['unit_code'], '$ '.number_format($price_list, 2, '.', ',').' MXN', '$ '.number_format($subtotal, 2, '.', ',').' MXN'));
                $no += 1;
                $cantidad_total += $details['qty'];
                $sutotal_total += $subtotal;
                $iva_total += $iva;
                $total_total += $total;
            }
            $sb += $sutotal_total;
            $iv += $iva_total;
            $ttl += $total_total;*/
        
        
        // PRODUCTOS
        $sutotal_total = 0;
        $iva_total = 0;
        $total_total = 0;
        $cantidad_total =0;
        $total = 0;
        $total_products = 0;
        $no=1;
        $sb = 0;
        $iv = 0;
        $ttl = 0;
        foreach ($slsInvoicesInbulk as $row) {
            $qty = (float)$row['qty'];
            $price_list = (float)$row['price_product'];
            $subtotal = $row['price_product'] * $row['qty'];
            $iva = (float)$subtotal * .16;
            $total = $subtotal + $iva;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(20, 2,$row['product'],0,0,'L');
            $pdf->Cell(20, 2,$row['qty'],0,0,'R');
            $pdf->Cell(12, 2,PESO.' '.number_format($row['price_product'], 2, '.', ' '),0,0,'R');
            $pdf->Cell(17, 2,PESO.' '.number_format($row['price_product'] * $row['qty'], 2, '.', ','),0,1,'R');
            $no += 1;
            $cantidad_total += $row['qty'];
            $sutotal_total += $subtotal;
            $iva_total += $iva;
            $total_total += $total;
        }   
        $sb += $sutotal_total;
        $iv += $iva_total;
        $ttl += $total_total;
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 7);
         // COLUMNAS
        // var_dump($ttl);
        // echo("<pre>");
        $formatter = new NumeroALetras();
        // echo $formatter->toMoney($ttl, 2,'pesos', 'centavos');
        $letra =$formatter->toMoney($ttl, 2,'pesos', 'centavos');
        // print_r($letra);
        // var_dump($formatter);
         // exit();
        //$formatterES = new NumberFormatter("es", NumberFormatter::SPELLOUT);
         // echo $formatterES->format(123.45);
         // $pdf->Cell(46, 5,utf8_decode($formatterES->format($ttl)) ,0,1,'l');
         $pdf->MultiCell(70, 3,utf8_decode($letra),0,'l',false);
         $pdf->Cell(35, 5, "SUB TOTAL:",0,0,'R');
         $pdf->Cell(15, 5, PESO. number_format($sb, 2 ,'.', ' '),0,1,'C');
         $pdf->Cell(35, 5, "IVA:",0,0,'R');
         $pdf->Cell(15, 5, PESO. number_format($iv, 2 ,'.', ' '),0,1,'C');
         $pdf->Cell(35, 5, "TOTAL:",0,0,'R');
         $pdf->Cell(15, 5, PESO. number_format($ttl, 2 ,'.', ' '),0,1,'C');
        // PIE DE PAGINA
        $pdf->Ln(10);
                $pdf->SetFont('Arial', 'B', 7);
        if($shoppingCartDetails[0]['sucursal_id'] == 9){
        $pdf->Cell(35,5,'TEL 618 8170585',0,0,'C');
        $pdf->Cell(35,5,'618 8170108',0,1,'C');
        } elseif ($shoppingCartDetails[0]['sucursal_id'] == 12) {
            // code...
            $pdf->Cell(35,5,'TEL 618 810 2521',0,0,'C');
            $pdf->Cell(35,5,'correo@empresa.mx',0,1,'C');
        }elseif($shoppingCartDetails[0]['sucursal_id'] == 13) {
            $pdf->Cell(35,5,'TEL 618 1303555',0,0,'C');
            $pdf->Cell(35,5,'correo@empresa.mx',0,1,'C');
        }elseif($shoppingCartDetails[0]['sucursal_id'] == 14){
            $pdf->Cell(35,5,'TEL 618 814 7148',0,0,'C');
            $pdf->Cell(35,5,'correo@empresa.mx',0,1,'C');
        }else {

        }
        $pdf->Cell(70,5,'www.empresa.mx',0,1,'C');
        $pdf->Cell(70,5,'**CONSERVE SU TICKET PARA CUALQUIER ACLARACION**',0,1,'C');
        return $pdf;
    }
        public function queryBulkForDetailShoppingCart ($id) {
        $sql = "SELECT sc.id,TO_CHAR(sc.created, 'dd/mm/yyyy') AS sale_date, bo.name as origin_branchoffice, cbo.name as client_branchoffice, c.name as customer_name,bo.id as sucursal_id,
                u.nickname as agent_name, c.price_list, sc.status as cart_status
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                WHERE sc.id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function queryBulkForDetailShoppingCartinBulk ($id) {
        $sql = "SELECT sscbd.status, sscbd.qty ,sscbd.price_product, wp.name as product, wl.name as line, wc.name as category,wu.name as unit_name,wu.code as unit_code, to_char(sscbd.created,'DD/MM/YYYY HH24:MI') as fechaactual, invoice_id
                FROM sls_shopping_cart_in_bulk_details AS sscbd
                LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
                LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
                LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                LEFT JOIN wms_units AS wu ON wu.id = wp.unit_id
                WHERE sscbd.shopping_cart_id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function querySlsInvoicesInbulkDetails ($id) {
        $sql = "SELECT sscbd.qty ,sscbd.unit_price as price_product , wp.name as product, wl.name as line, wc.name as category,wu.name as unit_name,wu.code as unit_code, to_char(sscbd.created,'DD/MM/YYYY HH24:MI') as fechaactual, invoice_id
        FROM sls_invoice_in_bulk_details AS sscbd
        LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
        LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
        LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
        LEFT JOIN wms_units AS wu ON wu.id = wp.unit_id
        WHERE sscbd.invoice_id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
}
