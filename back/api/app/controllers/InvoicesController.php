<?php

use Phalcon\Mvc\Controller;

class InvoicesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    public $batuta_url = '';
    


    public function idRemmision (){
        if ($this->userHasPermission()) {
            $sql = "SELECT id as value FROM sls_invoices";
            $options = [];

            $response = $this->db->query($sql)->fetchAll();

            foreach ($response as $value) {
                # code...
                $options[] = [
                    'label' => strval($value['value']),
                    'value' => $value['value']
                ];
            }

            $this->content['idremision'] = $options;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getTable2OrdersSaleNote($id) {
        //to_char(sls_invoice_in_bulk_details.inmediatedate, 'DD/MM/YYYY') as inmediatedate
        $sql = "SELECT sls_invoice_in_bulk_details.qty, wms_products.name as name_product, wms_products.description as description_product, wms_marks.name as mark, sls_invoices.comments as comments,
        wms_units.code as name_unit, sls_invoice_in_bulk_details.unit_price as price,
        (sls_invoice_in_bulk_details.qty * sls_invoice_in_bulk_details.unit_price) as total
        from sls_invoice_in_bulk_details
        inner join sls_invoices on sls_invoices.id = sls_invoice_in_bulk_details.invoice_id
        inner join wms_products on wms_products.id = sls_invoice_in_bulk_details.product_id
        inner join wms_units on wms_units.id = wms_products.unit_id
        inner join wms_lines ON wms_lines.id = wms_products.line_id
        left join wms_marks on wms_marks.id = wms_products.mark_id
        where sls_invoices.id = $id
        order by sls_invoice_in_bulk_details.id asc";

        $query = $this->db->query($sql)->fetchAll();

        return $query;

    }
    public function getTotal ($id) {
        $sql = "SELECT sum((sls_invoice_in_bulk_details.unit_price) * (sls_invoice_in_bulk_details.qty)) as price
        from sls_invoice_in_bulk_details
          inner join sls_invoices on sls_invoices.id = sls_invoice_in_bulk_details.invoice_id
        where sls_invoices.id = $id";

        $query = $this->db->query($sql)->fetchAll();

        return $query;
    }

    public function getNameCustomers ($id) {
        $sql = "SELECT sls_customers.name, sys_users.nickname from sls_shopping_cart
        inner join sls_customers on sls_customers.id = sls_shopping_cart.customer_id
        inner join sys_users on sys_users.id = sls_shopping_cart.user_id
        where sls_shopping_cart.id = $id";
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

    //Esta funcion consulta los detalles de las remisiones
    public function quotationNotePDF($id, $order) {
        $isOrder = $order;
        
        $tb2 = $this->getTable2OrdersSaleNote($id);
        
        $total = $this->getTotal($id);
        $getIdShoppingCart = "SELECT shopping_cart_id FROM  sls_invoices WHERE id = $id";
        $queryIdShoppingCart = $this->db->query($getIdShoppingCart)->fetchAll();
        $info = ShoppingCart::findFirst("id = ".$queryIdShoppingCart[0]["shopping_cart_id"]);
        $nameCustomers = $this->getNameCustomers($queryIdShoppingCart[0]["shopping_cart_id"]);
        $contactCustomer = $this->getNameContactCustomers($queryIdShoppingCart[0]["shopping_cart_id"]);
        if ($contactCustomer) {
            $myContact = $contactCustomer[0]['name'];
        }else {
            $myContact = null;
        }
        
        $pdf = new SaleNotesPdfControllerInv('P','mm','Letter');
        
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();

        
        $pdf->SetQuote($order == 'si' ? 'COTIZACIÓN' : 'PEDIDO');
        $pdf->serial = $id;
        $pdf->AddPage();
        if($info->branchoffice ==9){
        $pdf->cabezera1();
        } elseif($info->branchoffice ==12){
        $pdf->cabezera2();
        } elseif($info->branchoffice ==13) {
            $pdf->cabezera3();
        } elseif($info->branchoffice ==14){
            $pdf->cabezera4();
        }
        $pdf->CustomHeader($id);
        $pdf->SetAutoPageBreak(false, 20);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Nunito-Regular','',10);
        $pdf->SetDrawColor(21, 18, 46);
        $pdf->SetLineWidth(0);
        $pdf->Ln();
        $pdf->SetXY(40,32);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,7,utf8_decode('Cliente: '),0,0,'L');
        $pdf->SetXY(53,32);
        $pdf->SetFont('Nunito-Regular','',9);
        $pdf->Cell(70,7,utf8_decode($nameCustomers[0]["name"]),0,0,'L');
        $pdf->SetXY(40,38);
        $pdf->SetFillColor(71, 130, 222);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,7,'Con Atencion a:' ,0,0,'L');
        $pdf->SetXY(67,38);
        $pdf->SetFont('Nunito-Regular','',9);
        $pdf->Cell(70,7,utf8_decode($myContact),0,0,'L');
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
        //var_dump($pdf->GetPageWidth());
        // tabla 2

        $header = array('CANT.','PRODUCTO',utf8_decode('DESCRIPCIÓN'),'MARCA',utf8_decode('PRECIO U.'),utf8_decode('TOTAL'));
                $pdf->SetXY(37,$pdf->GetY());
                //60,137,232
                //82,151,235
                $pdf->SetFillColor(128,179,240);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetLineWidth(0);
                $pdf->SetFont('Nunito-Regular','',8);
                // Header
                $x = 190;
                $i = 0;
                // $w=array(5,20,25,30,25,30,35,20,15);
                $w=array(10,52,47,25,17,20);
                foreach($header as $col) {
                    if($i<=6){
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

                $pdf->SetWidths(array(10,52,47,25,17,20));
                $pdf->SetAligns(array('C', 'L','L','C','L','R','R')); 
                $bandera=1;
                $pdf->SetXY(37,$pdf->GetY());
                foreach ($tb2 as $value) {
                    
                    $pdf->Row(array(
                        utf8_decode($value["qty"]),
                        utf8_decode($value["name_product"]), 
                        $value["description_product"],
                        utf8_decode($value["mark"]),
                        "$ ".number_format($value["price"],2, '.', ',')."",
                        "$ ".number_format($value["total"],2, '.', ',').""
                    ),
                    $fill);
                    //$bandera++;
                    $fill = !$fill;
                    $amount += intval($value["qty"]);
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
                //$info->comments
                $pdf->SetY(215);
                $pdf->SetXY(41,$pdf->GetY());
                $pdf->SetFillColor(25, 107, 210);
                $pdf->Cell(165, 2,"", 0, 0, 'C', true);
                $pdf->SetTextColor(21, 18, 46);
                $pdf->Ln();
                $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("Condiciones Comerciales: ". $info->commercial_terms),0,0,'L');
                $pdf->Ln();
                $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetLineWidth(0.4);
                $pdf->SetDrawColor(0,0,0);
                $GetY = $pdf->GetY();
                //
                if ($info->special_order == 1) {
                $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->MultiCell(100, 7, "Comentarios: ".utf8_decode($info->comments), 0, 'L');
                }
                $pdf->SetXY(152,$GetY);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(25, 6, "Subtotal:", 0, 0, 'R', true);
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format($total[0]["price"],2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->SetXY(152,$pdf->GetY());
                $pdf->Cell(25, 6, utf8_decode("IVA:"), 0, 0, 'R', true);
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format($total[0]["price"] * 0.16,2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0);
                $pdf->SetXY(152,$pdf->GetY());
                $pdf->Cell(25, 6, "TOTAL:", 0, 0, 'R', true);
                
                $totalcost = ($total[0]["price"] * 0.16) + $total[0]["price"];
                $pdf->SetXY(177,$pdf->GetY());
                $pdf->Cell(30, 6, "$ ".number_format($totalcost,2, '.', ',')."", 0, 0, 'R', true);
                $pdf->Ln();
                $pdf->SetXY(40,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("Vigencia: ". $info->validity),0,0,'L');
                $pdf->SetXY(40,$pdf->GetY()+4);
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(70,7,utf8_decode("L.A.B: ". $info->lab),0,0,'L');
                $pdf->SetXY(130,$pdf->GetY());
                $pdf->SetFont('Nunito-Regular','',9);
                $pdf->Cell(77,7,utf8_decode("Agente: ".$nameCustomers[0]['nickname']),0,0,'L');
                if($info->branchoffice ==9){
                    $pdf->footer1();
                } elseif($info->branchoffice ==12){
                    $pdf->footer2();
                } elseif($info->branchoffice ==13) {
                    $pdf->footer3();
                } elseif($info->branchoffice ==14){
                    $pdf->footer4();
                }
        $pdf->SetTitle($isOrder == 'si' ? 'Cotización Pedido #'.$id : 'Pedido #'.$id,true);
        $fileName = "PEDIDO #.pdf";
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', $fileName, true);
        return $pdf;
    }




    //inicio calcen remision
    public function cancelRemision()
    {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        // echo("<pre>");
        // print_r($request);
        // exit();
        $invoice = Invoices::findFirst(intval($request['id']));
        $shoppingcartid = $invoice->shopping_cart_id;
        $invoice->setTransaction($tx);
        $invoice->status = 'CANCELADO';
        if ($invoice->update()) {
            $shoppingCart = ShoppingCart::findFirst($shoppingcartid);
            $shoppingCart->status = 'CANCELADO';
            $shoppingCart->setTransaction($tx);
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('La remisión ha sido CANCELADA.');
                $tx->commit();
            }
        } else {
            $this->content['error'] = Helpers::getErrors($invoice);
            $this->content['message'] = Message::error('Ha ocurrido un error al intentar CANCELAR.');
            $tx->rollback();
        }
        $this->response->setJsonContent($this->content);

    }
    // fin cancel remison
    public function getPdfI($id)
    {
         // print_r($id);
         // exit();
        $sql = "SELECT bo.street AS client_street, bo.colony AS client_colonia, bo.municipality AS client_municipio, bo.city AS client_ciudad,bo.name as sucursal,bo.zip_code,
        u.nickname AS agente,TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'dd/mm/yyyy') AS expired_date,
        sc.oc_reference AS orden,i.id_request,case when i.metodo_pago = 'PUE' then 'CONTADO'
        when i.metodo_pago = 'PPD' then 'CREDITO' END AS condiciones,bo.outdoor_number, bo.int_number, sc.branchoffice,
        coalesce((select case when f.status_timbrado = 2 then true else false end from sls_invoices_folios f where f.id_request = '$id' ),false) as status,
        tx.rfc, tx.razon_social,tx.lugar_expedicion,tx.immex,i.import,i.export
        FROM sls_invoices i
        JOIN sls_customer_branch_offices bo ON bo.id = i.customer_branch_office_id
        JOIN sls_customers c ON c.id = bo.customer_id
        JOIN sys_users u ON u.id = i.seller_id
        join sls_shopping_cart sc on sc.id = i.shopping_cart_id
        LEFT JOIN sls_invoice_payments ip ON ip.invoice_id = i.id
        LEFT JOIN sls_invoices_folios f ON f.invoice_id = i.id
        LEFT JOIN log_trips lt ON lt.invoice_id = i.id
        left join sls_customer_tax_companies tx on tx.id = i.tax_company_id
        WHERE i.id_request = '$id'
        OR ip.id_request = '$id' OR f.id_request = '$id' OR lt.id_request = '$id';";
        
        $client = $this->db->query($sql)->fetch();
        $xml = file_get_contents($this->batuta_url . '/api/download_xml/' . $id );

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");

        $pdf = InvoicePDF::nomina_pdf($xml,$client['status'],$client);
        $pdf->Output('I',$id."_factura.pdf",true);
    }
    // save the new pdf of invoice for send later 
        private function saveNewPdfInvoice ($id)
    {
        if (is_numeric($id)) {
            $invoice = Invoices::findFirst($id);
            $id_request = $invoice->id_request;

             $sql = "SELECT bo.street AS client_street, bo.colony AS client_colonia, bo.municipality AS client_municipio, bo.city AS client_ciudad,bo.name as sucursal,bo.zip_code,
             u.nickname AS agente,TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'dd/mm/yyyy') AS expired_date,
             sc.oc_reference AS orden,i.id_request,case when i.metodo_pago = 'PUE' then 'CONTADO'
             when i.metodo_pago = 'PPD' then 'CREDITO' END AS condiciones,bo.outdoor_number, bo.int_number, sc.branchoffice,
             coalesce((select case when f.status_timbrado = 2 then true else false end from sls_invoices_folios f where f.id_request = '$id_request' ),false) as status,
             tx.rfc, tx.razon_social,tx.lugar_expedicion,tx.immex,i.import,i.export
             FROM sls_invoices i
             JOIN sls_customer_branch_offices bo ON bo.id = i.customer_branch_office_id
             JOIN sls_customers c ON c.id = bo.customer_id
             JOIN sys_users u ON u.id = i.seller_id
             join sls_shopping_cart sc on sc.id = i.shopping_cart_id
             LEFT JOIN sls_invoice_payments ip ON ip.invoice_id = i.id
             LEFT JOIN sls_invoices_folios f ON f.invoice_id = i.id
             LEFT JOIN log_trips lt ON lt.invoice_id = i.id
             left join sls_customer_tax_companies tx on tx.id = i.tax_company_id
             WHERE i.id_request = '$id_request'
             OR ip.id_request = '$id_request' OR f.id_request = '$id_request' OR lt.id_request = '$id_request';";
            $client = $this->db->query($sql)->fetch();
            $xml = file_get_contents($this->batuta_url . '/api/download_xml/' . $id_request );
            $pdf = InvoicePDF::nomina_pdf($xml,$client['status'],$client);


            // if ($invoice) {
                // $pdf = $this->generatePdf($id);

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/invoices/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Factura #$invoice->id_request.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
                if (!is_null($xml)) {
                    $fileName = __DIR__.'/../../public/assets/invoices/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "XML #$invoice->id_request";
                    $xml->Output('F', $fileName, true);
                    return $fileName;
                }
            // }
        }
        return null;
    }
    public function sendNewPdfInvoiceToCustomer ()
    {
        $request = $this->request->getPost();
        $tx = $this->transactions->get();
        
        if (is_numeric($request['id'])) {
            $invoice = Invoices::findFirst($request['id']);
            if ($invoice && $invoice->status == 'ENVIADO') {
                $shoppingCart = ShoppingCart::findFirst($invoice->shopping_cart_id);
                if ($shoppingCart) {
                    $customerId = intval($shoppingCart->customer_id);
                    if(isset($request['email'])){
                        $msg = $this->sendEmailInvoiceToCustomer($invoice,$customerId,$request['email']);
                    }else {
                        $msg = $this->sendEmailInvoiceToCustomer($invoice,$customerId,null);
                    }
                    
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success($msg);
                    if($invoice->status_email == 'NUEVO'){
                    $invoice->setTransaction($tx);
                    $invoice->status_email = 'ENVIADO';
                    if ($invoice->update()) {
                    $this->content['result'] = true;
                    $tx->commit();
                    } else {
                    $this->content['error'] = Helpers::getErrors($invoice);
                    $tx->rollback();
                    }
                }

                }
            } else {
                $this->content['message'] = Message::error('No se ha encontrado la venta o no se encuentra Remisonada.');
                $this->content['result'] = false;
            }
        } else {
            $this->content['message'] = Message::error('No se ha recibido una id de venta válida.');
            $this->content['result'] = false;
        }
        $this->response->setJsonContent($this->content);
    }

    public function sendEmailInvoiceToCustomer($invoice,$customerId,$email) {
        $msg = null;
        /* echo '<pre>';
        print_r($invoice->id);
        print_r($customerId);
        print_r($email);
        exit(); */
        $actions = Actions::findFirst(2);
        if ($actions->host && $actions->port && $actions->username && $actions->password) {
            $customer = Customers::findFirst($customerId);
            if ($customer->email) {
                $arrayemail = [];
                $getEmails = "SELECT email from sls_customer_contacts where customer_id = $customer->id";
                $querygetEmails = $this->db->query($getEmails)->fetchAll();
                
                
                $arrayemail = [];

                foreach($querygetEmails as $value){
                        array_push($arrayemail, "".$value['email']."");
                    
                }

                if($email != null || $email != '' ){
                        array_push($arrayemail, "".$email."");
                    
                }
                /* array_push($arrayemail, "".$customer->email."");
                echo '<pre>';
                print_r($arrayemail);
                exit();  */
                // echo("<pre>");
        // print_r($arrayemail);
        //exit();
                /* $getEmailContact = "SELECT * FROM sls_customer_contacts where customer_id = $customer->id order by id desc limit 1";
                $emailcontact = $this->db->query($getEmailContact)->fetchAll();
                if (count($emailcontact)) {
                    array_push($arrayemail, $customer->email, $emailcontact[0]['email']);
                } else {
                    array_push($arrayemail, $customer->email);
                } */
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
                            <img id="logo" src="http://alpez.beta.wasp.mx/img/logo.f0ffa143.png" alt="ALPEZ">
                        </div>
                        <p>
                            Estimado Cliente <strong>'.$customer->tradename.'</strong>.
                            <br>
                            <br>
                            Adjunto encontrará la Factura en Pdf y Xml, en la misma encontrará las especificaciones.
                            <br>
                            <br>
                            <br>
                            <br>
                        </p>
                    </body>
                </html>';
                /* echo '<pre>';
                var_dump($customer->tradename);
                var_dump($htmlBody);
                var_dump($actions->host);
                var_dump($actions->port);
                var_dump($actions->username);
                var_dump($actions->password); */
                $this->saveNewPdfInvoice($invoice->id);
                // 'xml' => 'http://'.$this->batuta_url.'/public/files/cfdi_done/'.$invoice->id_request.'.xml',
                $fileName = __DIR__.'./../../public/assets/invoices/';
                $transport = (new Swift_SmtpTransport($actions->host, $actions->port, $actions->encryption))
                
                ->setUsername($actions->username)
                ->setPassword($actions->password);
                // Create the Mailer using your created Transport
                $mailer = new Swift_Mailer($transport);
                //echo '<pre>';
                //var_dump($mailer);

                // Create a message
                $message = (new Swift_Message('Estimado Cliente.'))
                ->setFrom([$actions->username => 'REBASA'])
                ->setTo($arrayemail)
                ->setBody($htmlBody,'text/html')
                ->attach(Swift_Attachment::fromPath($fileName.'Factura #'.$invoice->id_request.'.pdf')->setFilename('Factura #'.$invoice->id_request.'.pdf'))
                ->attach(Swift_Attachment::fromPath($this->batuta_url.'/public/files/cfdi_done/'.$invoice->id_request.'.xml'));
                // ->attach($xml);
                // Send the message
                //var_dump('PDF',file_exists($fileName.'Factura #'.$invoice->id_request.'.pdf'));
                
                //var_dump('XML', file_exists($this->batuta_url.'/public/files/cfdi_done/'.$invoice->id_request.'.xml'));
                //var_dump($message);
                $mailer->send($message);
                //var_dump($mailer->send($message));
                //var_dump("entre despues de mensaje");
                $msg.= "Correo enviado correctamente al Cliente";
            } else {
                $msg .= '; No se ha enviado el correo debido a que el Cliente no tiene correo registrado.';
            }
        } else {
            $msg .= '; No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
        }
        // var_dump();
        return $msg;
    }

    //end
    public function getHistory($id){
        

        if ($this->userHasPermission()) {
            $sql = "select folio_fiscal, serie, uuid, status_timbrado,
            case when id_cancelacion is null  then message else message_cancelacion end as message, id_cancelacion, fecha_cancelacion_envio, fecha_cancelacion_recibido, id_request,
            motivo_cancelacion, folio_sustituye
            from sls_invoices_folios where invoice_id = '$id' order by id desc;";
            $response = $this->db->query($sql)->fetchAll();

            $this->content['history'] = $response;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getPdfISenEmail($id)
    {
        // print_r($id);
        // exit();
        $invoice = Invoices::findFirst($id);
        $i = $invoice->id_request;
        // print_r($i);
        // exit();
        $sql = "SELECT c.street AS client_street, c.suburb AS client_colonia, c.municipality AS client_municipio, c.city AS client_ciudad,
        u.nickname AS agente,TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'dd/mm/yyyy') AS expired_date,
        sc.oc_reference AS orden,i.id_request,case when i.metodo_pago = 'PUE' then 'CONTADO' 
        when i.metodo_pago = 'PPD' then 'CREDITO' END AS condiciones,c.outdoor_number, c.indoor_number
        FROM sls_invoices i
        JOIN sls_customer_branch_offices bo ON bo.id = i.customer_branch_office_id
        JOIN sls_customers c ON c.id = bo.customer_id
        JOIN sys_users u ON u.id = i.agent_id
        join sls_shopping_cart sc on sc.id = i.shopping_cart_id
        LEFT JOIN sls_invoice_payments ip ON ip.invoice_id = i.id
        WHERE i.id_request = '$i'
        OR ip.id_request = '$i';";
        $client = $this->db->query($sql)->fetch();
        $xml = file_get_contents($this->batuta_url . '/api/download_xml/' . $i );

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        $pdf = InvoicePDF::nomina_pdf($xml,$client['status'],$client);
        // return $pdf;
    }
    // EMPIEZA EL CALENDARIO DE FORECAST
    public function getDataCalendar()
    {
        $today = $hoy = date("Y-m-d");
        $days = $this->getDaysForWeek();
        $daysv2 = $this->getAllPendingDays();

        $info = [];
        $canttt = 0;
        foreach ($daysv2 as $key => $d) {
            if ($today <= $d['sale_date']) {
                $data = $this->getcustomerBalancetoBeat($d['sale_date'], 2);
                $toPay = $this->getToPayToBeatv2($data);
                if ($toPay['remaining'] > 0) {
                    $corrientes = array("title" => $toPay['remaining'], "details" => 'CORRIENTE', "date" => $d['sale_date'], "bgcolor" => 'bg-green');
                    array_push($info, $corrientes);
                    $canttt += $toPay['remaining'];
                }
            }
        }
        $datav2 = $this->getcustomerBalancetoBeat($today, 1);
        $toPay = $this->getToPayToBeatv2($datav2);
        $vencido = array("title" => $toPay['remaining'], "details" => 'VENCIDO', "date" => $today, "bgcolor" => 'bg-red');
        array_push($info, $vencido);

        $this->content['data'] = $info;
        $this->response->setJsonContent($this->content);
    }

     public function getToPayToBeatv2($data)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : ' and sc.branchoffice = '.$validUser->branch_office_id;
        $abonado = 0;
        $acumulado = 0;
        $restante = 0;
        foreach ($data as $i) {
            $bulk = $i['ib'] !== null ? $i['ib'] : 0;
            $total = 0;
            if ($i['discount'] == 0) {
                $number = 1;
            } else {
                $n = 100 - $i['discount'];
                $number = (float)('.' . $n);
            }
            if ($bulk) {
                $sql = "SELECT sum(sb.qty * sb.unit_price) as bulk_qty
                        FROM sls_invoices AS i
                        INNER JOIN sls_invoice_in_bulk_details AS sb ON sb.invoice_id = i.id
                        join sls_shopping_cart sc on sc.id = i.shopping_cart_id
                        WHERE i.id = {$i['id']} $where";
                $data = $this->db->query($sql)->fetch();
                //IMPUESTO
                $total += (($data['bulk_qty']) * $number) * 1.16;
            }
            $a = $this->getCantidadesAbonadas($i['id'], 1) !== null ? $this->getCantidadesAbonadas($i['id'], 1) : 0;
            $abonado += floatval($a);
            $acumulado += floatval($total);
        }
        $r['remaining'] = floatval($acumulado) - floatval($abonado);
        return $r;
    }

    public function getDaysForWeek()
    {
        $days = [];
        $monday = date('Y-m-d', strtotime('Monday this week'));
        $tuesday = date('Y-m-d', strtotime('Tuesday this week'));
        $wednesday = date('Y-m-d', strtotime('Wednesday this week'));
        $thursday = date('Y-m-d', strtotime('Thursday this week'));
        $friday = date('Y-m-d', strtotime('Friday this week'));
        array_push($days, $monday, $tuesday, $wednesday, $thursday, $friday);
        return $days;
    }

    public function getAllPendingDays()
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : ' and sc.branchoffice = '.$validUser->branch_office_id;
        $days = [];
        $date = date('Y-m-d');
        $sql = "SELECT DISTINCT(
                TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when cus.credit_days is null OR cus.term = 'CONTADO' then 0 else cus.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD'))   AS sale_date
        FROM sls_invoices i
        join sls_shopping_cart sc on sc.id = i.shopping_cart_id
        left join sls_customers as cus on cus.id = sc.customer_id 
        where '$date' >= TO_CHAR(cast(i.sale_date as DATE) +CAST(cus.credit_days||' days' AS INTERVAL),'DD/MM/YYYY') AND i.status_payment IN (0,1) AND i.status in ('ENVIADO', 'ENTREGADO') $where";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $d) {
            $data[$key]['sale_date'] = date("Y-m-d", strtotime($d['sale_date']));
        }
        return $data;
    }

    public function getcustomerBalancetoBeat($date, $type)
    {
        //and i.id in (3423,3509,3260)
        $date1 = date('Y-m-d', strtotime($date));
        $validUser = Auth::getUserInfo($this->config);
        $where = "WHERE i.status_payment IN (0,1) AND i.status in ('ENVIADO', 'ENTREGADO')  ";
        $where .= $validUser->role_id == 1 ? '' : ' and sc.branchoffice = '.$validUser->branch_office_id.' ';
        if ($type == 1) {
            // $where .= " AND i.sale_date < '$date'";
            $where .= " AND TO_CHAR(cast(i.sale_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$date1'";
        } else if ($type == 2) {
            // $where .= " AND i.sale_date = '$date'";
            $where .= " AND TO_CHAR(cast(i.sale_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$date1'";

        } else if ($type == 3) {
            // $where .= " AND i.sale_date >= '$date'";
            $where .= " AND TO_CHAR(cast(i.sale_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$date1'";

        }
        $sql = "SELECT i.id,
                        i.discount,
                        TO_CHAR(i.sale_date, 'dd/mm/yyyy') AS sale_date,
                        i.status_payment,
                        i.in_bulk_movement_id as ib
                FROM sls_invoices AS i
                join sls_shopping_cart sc on sc.id = i.shopping_cart_id
                join sls_customers as s on s.id = sc.customer_id
                {$where}";
        // print_r($sql);
        // exit();
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function getCantidadesAbonadas($id, $type)
    {
        $y = date("Y");
        $date = date("$y-01-01 00:00:00.000000");

        $where = $type == 1 ?  "WHERE p.remision_id = $id" : "WHERE p.payment_date >= '$date'";

        $sqlAbonado = "SELECT sum(p.amount) as abonado FROM sls_payments AS p {$where}";
        $dataAbonado = $this->db->query($sqlAbonado)->fetch();

        return  $dataAbonado['abonado'];
    }

     public function getDetailsForecastperClient()
    {
        $info = [];
        $request = $this->request->getPost();
        //print_r($request);
        //exit();
        $date = $request['date'];
        $sum_total = 0;
        $where = "WHERE i.status_payment IN (0,1) and i.status in ('ENVIADO', 'ENTREGADO')  ";
        $where .= $request['type'] === 'CORRIENTE' ? " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') = '$date'" : " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') < '$date'";
        $sql = "SELECT count(i.customer_branch_office_id) as contador, t.customer_id
                FROM sls_invoices AS i 
                INNER JOIN sls_customer_branch_offices as t ON t.id = i.customer_branch_office_id
                INNER JOIN sls_customers c ON c.id = t.customer_id
                {$where}
                GROUP BY t.customer_id ORDER BY contador DESC";
        //print_r($sql);
        //exit();
        $customers = $this->db->query($sql)->fetchAll();
        $type = $request['type'] == 'CORRIENTE' ? 1 : 2;
        foreach ($customers as $c) {
            $qty = $this->getRem($c['customer_id'], $request['date'], $type);
            $name = $this->getName($c['customer_id']);
            $sum_total += floatval($qty['remaining']);
            $valores = array("customer_name" => $name, "contador" => $c['contador'], "total" => $qty['total'], "paid" => $qty['paid'], "remaining" => $qty['remaining']);
            array_push($info, $valores);
        }

        $this->content['data'] = $info;
        $this->content['sm'] = $sum_total ? number_format($sum_total, 2, '.', ',') : 0;
        $this->response->setJsonContent($this->content);
    }

    public function getDetailsForecastperRem()
    {
        $info = [];
        $request = $this->request->getPost();
        $date = $request['date'];
        $sum_total = 0;

        $where = "WHERE i.status_payment IN (0,1) and i.status in ('ENVIADO', 'ENTREGADO')  ";
        $where .= $request['type'] === 'CORRIENTE' ? " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') = '$date'" : " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') < '$date'";
        $sql = "SELECT i.id, t.customer_id
                FROM sls_invoices AS i 
                INNER JOIN sls_customer_branch_offices as t ON t.id = i.customer_branch_office_id
                INNER JOIN sls_customers c ON c.id = t.customer_id
                {$where}
                ORDER BY t.customer_id DESC";
                
        $customers = $this->db->query($sql)->fetchAll();
        $type = $request['type'] == 'CORRIENTE' ? 1 : 2;
        foreach ($customers as $c) {
            $qty = $this->getRemv2($c['id'], $request['date'], $type);
            $name = $this->getName($c['customer_id']);
            $sum_total += floatval($qty['remaining']);
            $valores = array("rem" => $c['id'], "customer_name" => $name, "total" => $qty['total'], "paid" => $qty['paid'], "remaining" => $qty['remaining']);
            array_push($info, $valores);
        }

        $this->content['data'] = $info;
        $this->content['sm'] = $sum_total ? number_format($sum_total, 2, '.', ',') : 0;
        $this->response->setJsonContent($this->content);
    }

    public function getRem ($customer,$date,$type) {
        $where = "WHERE i.status_payment IN (0,1) and i.status in ('ENVIADO', 'ENTREGADO')  and t.customer_id in ($customer) ";
        $where .= $type == 1 ? " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') = '$date'" : " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') < '$date'";
        $sql = "SELECT  i.id,
                        i.discount,
                        i.in_bulk_movement_id
                FROM sls_invoices AS i
                INNER JOIN sls_customer_branch_offices as t ON t.id = i.customer_branch_office_id
                INNER JOIN sls_customers c ON c.id = t.customer_id
                {$where}";
        $invoices = $this->db->query($sql)->fetchAll();

        $abonado = 0;
        $acumulado = 0;
        $restante = 0;
        foreach ($invoices as $i) {
            $total = 0;
            if ($i['discount'] == 0) {
                $number = 1;
            } else {
                $n = 100 - $i['discount'];
                $number = (float)('.' . $n);
            }
            if ($i['in_bulk_movement_id']) {
                $sql = "SELECT sb.qty, sb.unit_price
                        FROM sls_invoice_in_bulk_details AS sb
                        WHERE sb.invoice_id = {$i['id']}";
                $data = $this->db->query($sql)->fetchAll();
                foreach ($data as $d) {
                    //IMPUESTOS
                    $total += (($d['qty'] * $d['unit_price']) * $number) * 1.16;
                }
            }
            $a = $this->getCantidadesAbonadas($i['id'], 1) !== null ? $this->getCantidadesAbonadas($i['id'], 1) : 0;
            $abonado += floatval($a);
            $acumulado += floatval($total);
        }
        $r['total'] = $acumulado;
        $r['paid'] = $abonado;
        $r['remaining'] = floatval($acumulado) - floatval($abonado);
        return $r;
    }

    public function getRemv2 ($id,$date,$type) {
        $where = "WHERE status_payment IN (0,1) and status in ('ENVIADO', 'ENTREGADO') and i.id in ($id) ";
        $where .= $type == 1 ? " and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') = '$date'" : "and TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' 
then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') < '$date'";
        $sql = "SELECT i.id, i.discount, i.in_bulk_movement_id 
        FROM sls_invoices AS i
        INNER JOIN sls_customer_branch_offices as t ON t.id = i.customer_branch_office_id
        INNER JOIN sls_customers c ON c.id = t.customer_id
        {$where}";
        $invoices = $this->db->query($sql)->fetchAll();
        $abonado = 0;
        $acumulado = 0;
        $restante = 0;
        foreach ($invoices as $i) {
            if ($i['discount'] == 0) {
                $number = 1;
            } else {
                $n = 100 - $i['discount'];
                $number = (float)('.' . $n);
            }
            $total = 0;
            if ($i['in_bulk_movement_id']) {
                $sql = "SELECT sb.qty, sb.unit_price
                        FROM sls_invoice_in_bulk_details AS sb
                        WHERE sb.invoice_id = {$i['id']}";
                $data = $this->db->query($sql)->fetchAll();
                foreach ($data as $d) {
                    //IMPUESTOS
                    $total += (($d['qty'] * $d['unit_price']) * $number) * 1.16;
                }
            }
            $a = $this->getCantidadesAbonadas($i['id'], 1) !== null ? $this->getCantidadesAbonadas($i['id'], 1) : 0;
            if ($i['discount'] == 0) {
                $number = 1;
            } else {
                $n = 100 - $i['discount'];
                $number = (float)('.' . $n);
            }
            $abonado += floatval($a);
            $acumulado += floatval($total);
        }
        $r['total'] = $acumulado;
        $r['paid'] = $abonado;
        $r['remaining'] = floatval($acumulado) - floatval($abonado);
        return $r;
    }
    // fin forecast
    public function getName($customer)
    {
        $sql = "SELECT SUBSTRING(c.name,0,30) as name FROM sls_customers AS c WHERE c.id = $customer";
        $data = $this->db->query($sql)->fetch();
        return $data['name'];
    }

    // inicio auxiliar contable
    public function invoicesOptions()
    {
        $sql = "SELECT id AS value, concat(serie,'-',folio_fiscal) AS label FROM sls_invoices WHERE folio_fiscal is not null ORDER BY label ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function invoicesTripOptions()
    {
        $sql = "SELECT id AS value, concat(id, ' / ',serie,'-',folio_fiscal) AS label FROM sls_invoices WHERE folio_fiscal is not null ORDER BY label ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }


    public function getInvoicesByPagination_Payments_Auxiliar()
    {
        $request = $this->request->getPost();
        $status = [];
        if ($this->userHasPermission()) {
            $response = $this->getGridPaymentsAuxiliarSQL($request);
            $this->content['payments'] = $response['data'];
            $this->content['paymentsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridPaymentsAuxiliarSQL($request)
    {   
        
        $user = Auth::getUserInfo($this->config);
        $y = date('Y');
        $where = "WHERE ";
        $order = "";
        $date1 = $request['saleDatev1'];
        $date2 = $request['saleDatev2'];
        if ($date1 === '') {
            $dateIni = date("2020-01-01 00:00:00.000000");
        } else {
            list($day, $mouth, $year) = explode('/', $date1);
            $aux_date = $year . "/" . $mouth . "/" . $day;
            $theDate = new DateTime($aux_date . ' 00:00:00.000000');
            $dateIni = $theDate->format('Y-m-d H:i:s');
        }
        if ($date2 === '') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        } else {
            list($dayb, $mouthb, $yearb) = explode('/', $date2);
            $aux_date2 = $yearb . "/" . $mouthb . "/" . $dayb;
            $theDate2 = new DateTime($aux_date2 . ' 23:59:59.000000');
            $dateFin = $theDate2->format('Y-m-d H:i:s');
        }
        $where .= "p.payment_date BETWEEN '" . $dateIni . "' AND '" . $dateFin . "' ";
        if ($request['invoices'] == 'TODOS') {
        } else if ($request['invoices'] == '') {
        } else {
            $where .= " AND p.remision_id = {$request['invoices']} ";
        }
        if ($request['customer'] == 'TODOS') {
        } else if ($request['customer'] == '') {
        } else {
            $where .= " AND cbo.customer_id = {$request['customer']}";
        }
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)) {
            $where .= " AND (p.remision_id::text ILIKE '%" . $filter . "%' OR p.payment_date::text ILIKE '%" . $filter . "%' OR p.amount::text ILIKE '%" . $filter . "%' OR i.serie::text ILIKE '%" . $filter . "%' OR i.folio_fiscal::text ILIKE '%" . $filter . "%' OR c.name::text ILIKE '%" . $filter . "%' )";
        }

        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY " . trim($pagination['sortBy'] . " ");
        } else {
            $sortBy .= " ORDER BY p.id ";
        }

        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'] . " ";

        $sql = "SELECT count(p.id) AS count
                FROM sls_payments AS p
                INNER JOIN sls_invoices AS i ON i.id = p.remision_id
                INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                {$where}";
        $invoicesCount = $this->db->query($sql)->fetchAll();

        $sql2 = "SELECT p.id, p.remision_id as remision,to_char(p.payment_date,'DD/MM/YYYY') as fecha_pago, p.amount as cantidad, coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura, substring(c.name,0,35)  as cliente
                FROM sls_payments AS p
                INNER JOIN sls_invoices AS i ON i.id = p.remision_id
                INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                {$where}
                GROUP BY p.id, p.remision_id, i.serie, i.folio_fiscal, c.name
                {$sortBy} {$desc} {$offset} {$limit}
                ";
        $data = $this->db->query($sql2)->fetchAll();

        $response = array('data' => $data, 'rowCounts' => $invoicesCount[0]['count']);
        return $response;
    }

    public function getPdfFromAuxiliarPayments($customer, $date1, $date2, $invoices)
    {
        $pdf = $this->createPdfFromAuxiliarPayments($customer, $date1, $date2, $invoices);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Historial_de_Cobros.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Historial-de-Cobros.pdf"');
            return $response;
        }
    }

    public function createPdfFromAuxiliarPayments($customer, $date1, $date2, $invoices)
    {
        $y = date('Y');
        $where = "";
        $contador = 0;
        if ($date1 == 'null') {
            $dateIni = date("2020-01-01 00:00:00.000000");
            //            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        } else {
            $dateIni = date("Y-m-d H:i:s", strtotime($date1 . ' 00:00:00.000000'));
        }
        if ($date2 == 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        } else {
            $dateFin = date("Y-m-d H:i:s", strtotime($date2 . ' 23:59:59.000000'));
        }
        $where .= "WHERE p.payment_date BETWEEN '" . $dateIni . "' AND '" . $dateFin . "'";
        if ($customer == 'TODOS') {
        } else if ($customer == '') {
        } else {
            $where .= " AND cbo.customer_id = $customer";
        }
        if ($invoices == 'TODOS') {
        } else if ($invoices == '') {
        } else {
            $where .= " AND i.id = $invoices";
        }

        $sql = "SELECT p.id, p.remision_id as remision,to_char(p.payment_date,'DD/MM/YYYY') as fecha_pago, p.amount as cantidad, coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura, substring(c.name,0,35)  as cliente
                FROM sls_payments AS p
                INNER JOIN sls_invoices AS i ON i.id = p.remision_id
                INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                {$where}
                GROUP BY p.id, p.remision_id, i.serie, i.folio_fiscal, c.name
                ORDER BY p.payment_date DESC";
        $data = $this->db->query($sql)->fetchAll();

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDFAuxiliarPayments();
        $pdf->AddFont('Nunito-Regular', '', 'Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(0.1);
        $pdf->encabezado();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(190, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA INICIO: ' . $fechaIni);

        $pdf->SetXY(235, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA FIN: ' . $fechaFin);

        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);


        $pdf->SetXY(5, 40);
        $pdf->SetFont('', '', 7);

        $pdf->SetWidths(array(30, 30, 33, 140, 35));
        $pdf->SetAligns(array('C', 'C', 'C', 'L', 'R'));
        $pdf->SetDrawColor(0, 0, 0);

        $i = 1;
        foreach ($data as $row) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                $pdf->AddPage('L', 'Letter');
                $pdf->encabezado();
                $pdf->SetXY(0, 40);
                $pdf->SetFont('', '', 7);
            }
            $pdf->SetX(5);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->Row(array($row['remision'], $row['factura'], $row['fecha_pago'], utf8_decode($row['cliente']), '$ ' . number_format(floatval($row['cantidad']), 2, '.', ',')));
            $i++;
        }

        $pdf->SetTitle(utf8_decode('Reporte de historial de cobros'));
        $pdf->Output('I', 'historial_cobros.pdf', true);

        return $pdf;
    }

    public function getCSVFromAuxiliarPayments($customer, $date1, $date2, $invoices)
    {
        $y = date('Y');
        $where = "";
        $contador = 0;
        if ($date1 == 'null') {
            $dateIni = date("2020-01-01 00:00:00.000000");
            //            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        } else {
            $dateIni = date("Y-m-d H:i:s", strtotime($date1 . ' 00:00:00.000000'));
        }
        if ($date2 == 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        } else {
            $dateFin = date("Y-m-d H:i:s", strtotime($date2 . ' 23:59:59.000000'));
        }
        $where .= "WHERE p.payment_date BETWEEN '" . $dateIni . "' AND '" . $dateFin . "'";
        if ($customer == 'TODOS') {
        } else if ($customer == '') {
        } else {
            $where .= " AND cbo.customer_id = $customer";
        }
        if ($invoices == 'TODOS') {
        } else if ($invoices == '') {
        } else {
            $where .= " AND i.id = $invoices";
        }

        $sql = "SELECT p.id, p.remision_id as remision,to_char(p.payment_date,'DD/MM/YYYY') as fecha_pago, p.amount as cantidad, coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura, substring(c.name,0,35)  as cliente
                FROM sls_payments AS p
                INNER JOIN sls_invoices AS i ON i.id = p.remision_id
                INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                {$where}
                GROUP BY p.id, p.remision_id, i.serie, i.folio_fiscal, c.name
                ORDER BY p.payment_date DESC";
        $data = $this->db->query($sql)->fetchAll();

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $content = $this->content;
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('REMISION', 'FACTURA', 'FECHA', 'CLIENTE', 'CANTIDAD'), ',');

        if (count($data) > 0) {
            foreach ($data as $d) {
                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $d['remision'],
                        $d['factura'],
                        $d['fecha_pago'],
                        $d['cliente'],
                        number_format($d['cantidad'], 2, '.', '')
                    ], ',');
                }
            }
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Historial_Cobross' . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    // fin auxiliar contable


    public function onConstruct()
    {
        $this->batuta_url = ($_SERVER['SERVER_NAME'] === 'api_alpez.wasp.mx' ? 'https://batuta.wasp.mx' : 'http://batuta.beta.antfarm.mx');
    }

    public function getInvoices ()
    {
        if ($this->userHasPermission()){
            $sql = $this->getInvoicesSQL();
            $this->content['invoices'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getpendingPayments()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()) {
            $response = $this->getpendingPaymentsSQL($request['customer']);
            if ($response) {
                $this->content['pendingPayments'] = $response;
                $this->content['result'] = true;
            } else {
                $this->content['result'] = false;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getpendingPaymentsSQL($customer)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : ' and sc.branchoffice = '.$validUser->branch_office_id;

        $sql = "SELECT i.id,i.sale_date,i.status_payment,to_char(i.sale_date,'DD/MM/YYYY') AS sale_date,i.shopping_cart_id,CONCAT(i.serie,'-',i.folio_fiscal) AS no_factura,
                (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
                (select COALESCE((SELECT sum(round((sib.unit_price * sib.qty)::numeric,2) + round((sib.unit_price * sib.qty * .16)::numeric,2)) 
                from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as total,
                TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') AS expired_date
                FROM sls_invoices AS i
                INNER JOIN sls_customer_branch_offices AS cbo ON i.customer_branch_office_id = cbo.id
                JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                WHERE cbo.customer_id = {$customer} and i.status_payment in (0,1) and i.status in ('ENVIADO', 'ENTREGADO') $where
                ORDER BY i.id ASC;";
                // print_r($sql);
                // exit();
        $data = $this->db->query($sql)->fetchAll();
        $maximoAbono = 0;
        foreach ($data as $key => $d) {
            $id = $d['id'];
            $totales = $this->getImpuestos($id);
            $resta = $totales - $d['abonado'];
            $data[$key]['cantidad_total'] = number_format($totales, 2, '.', '');
            $data[$key]['abonado'] = number_format($d['abonado'], 2, '.', '');
            $data[$key]['cantidad_restante'] = number_format($resta, 2, '.', '');
            $data[$key]['nuevo_abono'] = 0;
            $data[$key]['nuevo_saldo'] = number_format($resta, 2, '.', '');
            $data[$key]['bandera_abono'] = false;
            //
            if ($d['sale_date']) {
                $fecha = date('Y-m-d 00:00:00');
                if (strtotime($d['expired_date']) > strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'CORRIENTE';
                    $data[$key]['color_label'] = 'green-14';
                } else if (strtotime($d['expired_date']) > strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'CORRIENTE ABONADO';
                    $data[$key]['color_label'] = 'green-14';
                } else if (strtotime($d['expired_date']) == strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'POR VENCER';
                    $data[$key]['color_label'] = 'amber';
                } else if (strtotime($d['expired_date']) == strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'POR VENCER ABONADO';
                    $data[$key]['color_label'] = 'amber';
                } else if (strtotime($d['expired_date']) < strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'VENCIDO';
                    $data[$key]['color_label'] = 'red-14';
                } else if (strtotime($d['expired_date']) < strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'VENCIDO ABONADO';
                    $data[$key]['color_label'] = 'red-14';
                } else if ($d['status_payment'] == 2) {
                    $data[$key]['vencimiento'] = 'PAGADO';
                    $data[$key]['color_label'] = 'green-10';
                }
            } else {
                $data[$key]['vencimiento'] = '-';
            }
            $maximoAbono += $resta;
        }
        $response = array('data' => $data, 'maximo' => $maximoAbono);
        return $response;
    }


    public function getInvoicesByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request['customer'],$request['saleDatev1'],$request['saleDatev2'],$request['status'],$request['statusT'],$request, $request['remision'], $request['factura']);
            $this->content['invoices'] = $response['data'];
            $this->content['invoicesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getInvoicesByPagination_Payments ()
    {
        $request = $this->request->getPost();
        $status = [];
        if ($this->userHasPermission()){
            if(!isset($request['status'])){
                $status = [];
            }else{
                $status = $request['status'];
            }
            $response = $this->getGridPaymentsSQL($request['customer'],$request['saleDatev1'],$request['saleDatev2'],$status,$request['type'],$request);
            
            $this->content['paymentDate'] = date('Y-m-d 12:00:00');
            $this->content['invoices'] = $response['data'];
            $this->content['invoicesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function dateDeliveredUpdate ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $invoice = Invoices::findFirst($id);
                $request = $this->request->getPut();
                if ($invoice) {
                    if ($invoice->status == 'ENVIADO') {
                        $invoice->setTransaction($tx);
                        /* if ($invoice->carrier_id == null) {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::success('No se encontro registrada ninguna paqueteria.');
                        } else { */
                            if (isset($request['date_delivered'])) {
                                $newdate = date('Y-m-d', strtotime($request['date_delivered']));
                                $invoice->date_delivered = $request['date_delivered'];
                                $invoice->status = "ENTREGADO";
                            }

                            if ($invoice->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La remisión ha sido actualizada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus a entregado.');
                                $tx->rollback();
                            }
                        //}
                    } else {
                        $this->content['message'] = Message::error('La venta de fibra no se puede modificar debido a que ya fue entregada.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function getInvoice ($id)
    {
        if ($this->userHasPermission()) {
            $invoice = null;
            if (is_numeric($id)) {
                $invoice = $this->getInvoicebyIdSQL($id);
            }
            $invoiceDetail = InvoiceDetails::findFirst("invoice_id = $id");
            $invoiceInBulkDetail = InvoiceInBulkDetails::findFirst("invoice_id = $id");
            $invoiceLaminateDetail = InvoiceLaminateDetails::findFirst("invoice_id = $id");
            $qtyfromSCBales = ShoppingCartBaleDetails::findFirst("invoice_id = $id");
            $qtyfromSCinBulk = ShoppingCartInBulkDetails::findFirst("invoice_id = $id");
            $qtyfromSCLaminates = ShoppingCartLaminateDetails::findFirst("invoice_id = $id");
            if($qtyfromSCBales){
                $this->content['qtysFromBales'] = $this->getqtysFromBalesSC($id);
            }
            if($qtyfromSCinBulk){
                $this->content['QtyinBulk'] = $qtyfromSCinBulk->qty;
            }
            if($qtyfromSCLaminates){
                $this->content['QtyLaminates'] = $qtyfromSCLaminates->qty;
            }
            //
            $invoice['canRemisionate'] = ($invoiceDetail || $invoiceInBulkDetail || $invoiceLaminateDetail);
            $this->content['invoice'] = $invoice;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getPayments ()
    {
        if ($this->userHasPermission()) {
            $sql = $this->getPaymentsSQL();
            $this->content['invoices'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getqtysFromBalesSC ($id) {
        $sql = $this->getqtysFromBalesScSQL($id);
        return $sql;
    }

    public function getGrid ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()) {
            $response = $this->getGridSQL($request['customer'],$request['saleDatev1'],$request['saleDatev2'],$request['status'],$request['statusT'],$request, $request['remision'],$request['factura']);
            $this->content['invoices'] = $response['data'];
            $this->content['invoicesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridPayments ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()) {
            if(!isset($request['status'])){
                $status = [];
            }else{
                $status = $request['status'];
            }
            $response = $this->getGridPaymentsSQL($request['customer'],$request['saleDatev1'],$request['saleDatev2'],$status,$request['type'],$request);
            $this->content['invoices'] = $response['data'];
            $this->content['invoicesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function dataFromInvoice () {
        $request = $this->request->getPost();
        $id = $request['id'];
        $tA = $this->getAmountsFromInvoiceSQL($id);
        $payments = $this->getDataFromPayments($id);

        $this->content['result'] = true;
        $this->content['total_invoice'] = $tA;
        $this->content['payments'] = $payments;

        $this->response->setJsonContent($this->content);
    }
     public function AddFile () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        // print_r($request);
        // exit();
        $id = $request['id'];
        $remision_id = $request['remision_id'];
        $validUser = Auth::getUserData($this->config);
        $invoices = Invoices::findFirst("id = $remision_id");
        $payments = Payments::findFirst("remision_id = $remision_id");
        // print_r($payments->id);
        // exit();
        $files = $this->request->getUploadedFiles();
        $type = null;
        $file = null;
        // print_r($files[0]->getType());
        // exit();
        if ($invoices) {
            if ($this->request->hasFiles()) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$remision_id;
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $files = $this->request->getUploadedFiles();
                $type = $files[0]->getType();
                $file = $files[0]->getName();
                $payments->setTransaction($tx);
                $payments->file = $file;
                $payments->file_type = $type;
                if($payments->update()){
                if ($this->request->hasFiles()) {
                    $files[0]->moveTo(
                        $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$remision_id.'/' . $id
                    );
                    $this->content['result'] = true;
                }
                $this->content['result'] = true;
                $this->content['message'] = Message::success('Archivo agregado correctamente.');
                $tx->commit();
                } else {
                     $this->content['result'] = false;
                     $this->content['message'] = Message::success('Archivo no se agrego.');
                     $tx->rollback();

                }

                 
    }else {
            $this->content['result'] = false;
        }
}
$this->response->setJsonContent($this->content);
}
    public function addPayment () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $id = $request['id'];
        $validUser = Auth::getUserData($this->config);

        $invoices = Invoices::findFirst("id = $id");
        if ($invoices) {
            $office = BranchOffices::findFirst($invoices->ShoppingCart->branchoffice);
            $payment = new Payments();
            $payment->setTransaction($tx);
            $payment->created_by = $validUser->id;
            $payment->remision_id = $id;
            $payment->amount = floatval($request['qty']);
            $payment->payment_date = $request['date'];
            $payment->reference = $request['ref'];
            $payment->method = $request['method'];
            $type = null;
            $file = null;
            // print_r(dirname(__FILE__)  . '/../../files/payment');
            // print_r( $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/';);
            // exit();

           /* if ($this->request->hasFiles()) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$id;
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                $files = $this->request->getUploadedFiles();
                $type = $files[0]->getType();
                $file = $files[0]->getName();
                   
            }*/

            $payment->file = $file;
            $payment->file_type = $type;

            if ($payment->create()) {
                /* if ($this->request->hasFiles()) {
                    $files[0]->moveTo(
                        $_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$id.'/' . $payment->id
                    );
                } */
                if ($invoices->status_timbrado === 1 && $invoices->metodo_pago === 'PPD') {
                    $pagos = InvoicePayments::find("invoice_id = {$id} and (status_timbrado != 4 and status_timbrado != 5 and status_timbrado != 3 and status_timbrado != 2) and payment_id is not null order by num_parcialidad desc");
                    $pago = new InvoicePayments();
                    $pago->setTransaction($tx);
                    $pago->invoice_id = intval($id);
                    $pago->fecha_pago = $request['date'];
                    $pago->forma_pago = $request['method'];
                    $pago->num_parcialidad = !empty($pagos[0]->num_parcialidad) ? $pagos[0]->num_parcialidad + 1 : 1;
                    $pago->total = floatval($request['qty']);
                    $pago->payment_id = $payment->id;
                    $pago->folio = 0;
                    $pago->serie = $office->serie_pagos;
                    if ($pago->create()) {}
                }
                if($request['status'] === 'PAGADO'){
                    $invoices->setTransaction($tx);
                    $invoices->status_payment = 2;
                    if ($invoices->update()) {}
                } else {
                    $invoices->setTransaction($tx);
                    $invoices->status_payment = 1;
                    if ($invoices->update()) {}
                }
                $this->content['result'] = true;
                $this->content['id'] = $id;
                $this->content['message'] = Message::success('Pago agregado correctamente.');
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($payment);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago.');
                $tx->rollback();
            }
        }
        $this->response->setJsonContent($this->content);
    }
    public function getFile ($id) {
        try {
            if ($this->userHasPermission()) {
                $info = Payments::findFirst($id);
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: *");
                readfile($_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$info->remision_id.'/'.$info->id);
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
    }

    public function deletePayment () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $id = intval($request['id']);
        $remision = $request['remision'];

        $invoice = Payments::findFirst($id);
        $invoice->setTransaction($tx);

        if ($invoice->delete()) {
            if($invoice->file != ''){
                unlink($_SERVER["DOCUMENT_ROOT"] . '/public/files/payment/'.$invoice->remision_id.'/'.$id);
            }
            $pago = InvoicePayments::findFirst("payment_id = $id");
            if ($pago) {
                $pago->setTransaction($tx);
                if (!$pago->delete()) {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el pago.');
                    $tx->rollback();
                }
            }
            if($request['status'] === 'ABONADO'){
                $sql = "SELECT * FROM sls_payments AS p  WHERE p.remision_id = $remision ORDER BY p.id DESC;";
                $data = $this->db->query($sql)->fetchAll();
                $invoices = Invoices::findFirst("id = $remision");
                $invoices->setTransaction($tx);
                if ($data) {
                    $invoices->status_payment = 1;
                } else {
                    $invoices->status_payment = 0;
                }
                if (!$invoices->update()) {
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

        $this->response->setJsonContent($this->content);
    }


      public function addMultiPayment()
    {
        $validUser = Auth::getUserData($this->config);
        $request = $this->request->getPost();
        $folioMulti = 0;
        $idRequest = null;
        $taxCompany = null;
        $sucursal = null;
        $serie = null;
        try {
            foreach ($request['dataPayments'] as $idx => $r) {
                $tx = $this->transactions->get();
                $saleDate = str_replace('/', '-', $request['paymentDate']);
                if ($r['nuevo_abono'] > 0) {
                    $invoices = Invoices::findFirst("id = " . $r['id']);
                    $office = BranchOffices::findFirst($invoices->ShoppingCart->branchoffice);
                    $serie = $office->serie_pagos;
                    if ($invoices) {
                        $payment = new Payments();
                        $payment->setTransaction($tx);
                        $payment->created_by = $validUser->id;
                        $payment->remision_id = $r['id'];
                        $payment->amount = floatval($r['nuevo_abono']);
                        $payment->payment_date = date("Y-m-d  H:i:s", strtotime($saleDate));
                        $payment->reference = $request['reference'];
                        $payment->method = $request['paymentMethod'];
                        $taxCompany = $taxCompany??($invoices->tax_company_id==null?0:$invoices->tax_company_id);
                        $sucursal = $sucursal??$invoices->ShoppingCart->branchoffice;
                        if ($payment->create()) {
                            if ($invoices->status_timbrado === 1 && $invoices->metodo_pago === 'PPD') {
                                if($taxCompany != $invoices->tax_company_id && $request['invoiceComp'] == 1){
                                    $tx->rollback();
                                    $this->content['data'] = ['result' => false,'x' => 1];
                                    $this->content['error'] = "diferente tax-company";
                                    $this->content['message'] = Message::error('No puedes hacer un multi-abono de un cliente con diferente razon social.');
                                    $this->content['result'] = false;
                                    break;
                                }
                                if($sucursal != $invoices->ShoppingCart->branchoffice && $request['invoiceComp'] == 1){
                                    $tx->rollback();
                                    $this->content['data'] = ['result' => false,'x' => 1];
                                    $this->content['error'] = "diferente sucursal";
                                    $this->content['message'] = Message::error('No puedes hacer un multi-abono de diferentes sucursales.');
                                    $this->content['result'] = false;
                                    break;
                                }
                                $pagos = InvoicePayments::find("invoice_id = {$r['id']} and (status_timbrado != 4 and status_timbrado != 5 and status_timbrado != 3 and status_timbrado != 2) and payment_id is not null order by num_parcialidad desc");
                                $pago = new InvoicePayments();
                                $pago->setTransaction($tx);
                                $pago->invoice_id = intval($r['id']);
                                $pago->fecha_pago = date("Y-m-d  H:i:s", strtotime($saleDate));
                                $pago->forma_pago = $request['paymentMethod'];
                                $pago->num_parcialidad = !empty($pagos[0]->num_parcialidad) ? $pagos[0]->num_parcialidad + 1 : 1;
                                $pago->total = floatval($r['nuevo_abono']);
                                $pago->payment_id = $payment->id;
                                $pago->folio = $folioMulti;
                                $pago->serie = $serie;
                                if ($pago->create()) {
                                    $pagoC = InvoicePayments::findFirst($pago->id);
                                    if($request['invoiceComp'] == 1) $folioMulti =  $pagoC->folio;
                                    if ($request['invoiceComp'] == 1 && count($request['dataPayments']) == 1) {
                                        $this->timbrarPago($pago->id, 2);
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($pago);
                                    $this->content['customer'] = $request['customer'];
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago (1).');
                                    $tx->rollback();
                                }
                            }
                            $invoices->setTransaction($tx);
                            floatval($r['nuevo_abono']) === floatval($r['cantidad_restante']) ? $invoices->status_payment = 2 : $invoices->status_payment = 1;
                            if ($invoices->update()) {
                                $this->content['result'] = true;
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoices);
                                $this->content['customer'] = $request['customer'];
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago (2).');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($payment);
                            $this->content['customer'] = $request['customer'];
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el pago (3).');
                            $tx->rollback();
                        }
                    }
                }
            }
            if(count($request['dataPayments'])>1)
                $this->timbrarMultiPago($folioMulti, $serie);
            if ($this->content['result'] == true) {
                $this->content['customer'] = $request['customer'];
                $this->content['message'] = Message::success('Abonos realizados correctamente.');
                $tx->commit();
            }
        } catch (Throwable $e) {
            $result_message = (object) array(
                'status' => false,
                'message' => $e->getMessage()
            );
        }
        $this->response->setJsonContent($this->content);
    }
    public function generatePdf ($id)
    {
        if (is_numeric($id)) {
            $invoice = $this->getinvoicePDFSQL($id);
            // var_dump($invoice);
            if ($invoice) {
                $baleMovement = Movements::findFirst($invoice['in_bulk_movement_id']);
                if ($baleMovement && $baleMovement->status == 'EJECUTADO') {
                    $inBulkDetails = $this->getinBulkDetailsPDFSQL($id);

                    $customerAddress = '';

                    if (isset($invoice['street']) && strlen($invoice['street']) > 0) {
                        $customerAddress = $invoice['street'];
                        if (isset($invoice['outdoor_number']) && strlen($invoice['outdoor_number']) > 0) {
                            $customerAddress .= ' '.$invoice['outdoor_number'];
                        }
                        if (isset($invoice['indoor_number']) && strlen($invoice['indoor_number']) > 0) {
                            $customerAddress .= ' '.$invoice['indoor_number'];
                        }
                    }

                    if (isset($invoice['suburb']) && strlen($invoice['suburb']) > 0) {
                        if (strlen($customerAddress) > 0) {
                            $customerAddress .= ', ';
                        }
                        $customerAddress .= $invoice['suburb'];
                    }

                    if (isset($invoice['municipality']) && strlen($invoice['municipality']) > 0) {
                        if (strlen($customerAddress) > 0) {
                            $customerAddress .= ', ';
                        }
                        $customerAddress .= $invoice['municipality'];
                    }

                    if (isset($invoice['state']) && strlen($invoice['state']) > 0) {
                        if (strlen($customerAddress) > 0) {
                            $customerAddress .= ', ';
                        }else {

                        }
                        $customerAddress .= $invoice['state'];
                    }

                    $customerBranchOfficeAddress = '';

                    if (isset($invoice['customer_branch_office_street']) && strlen($invoice['customer_branch_office_street']) > 0) {
                        $customerBranchOfficeAddress = $invoice['customer_branch_office_street'];
                        if (isset($invoice['customer_branch_office_outdoor_number']) && strlen($invoice['customer_branch_office_outdoor_number']) > 0) {
                            $customerBranchOfficeAddress .= ' '.$invoice['customer_branch_office_outdoor_number'];
                        }
                    }

                    if (isset($invoice['customer_branch_office_zip_code']) && strlen($invoice['customer_branch_office_zip_code']) > 0) {
                        if (strlen($customerBranchOfficeAddress) > 0) {
                            $customerBranchOfficeAddress .= ', C.P. '.$invoice['customer_branch_office_zip_code'];
                        } else {
                            $customerBranchOfficeAddress .= 'C.P. '.$invoice['customer_branch_office_zip_code'];
                        }
                    }

                    if (isset($invoice['customer_branch_office_phone_number']) && strlen($invoice['customer_branch_office_phone_number']) > 0) {
                        if (strlen($customerBranchOfficeAddress) > 0) {
                            $customerBranchOfficeAddress .= ', Tel. '.$invoice['customer_branch_office_phone_number'];
                        }  else {
                            $customerBranchOfficeAddress .= 'Tel. '.$invoice['customer_branch_office_phone_number'];

                        }
                    }
                    $pdf = new PDFInvoice('P','mm','Letter');
                    $pdf->AliasNbPages();
                    $pdf->SetInvoiceId($invoice['id']);
                    $pdf->SetBranchOffice($invoice['branch_office']);
                    $pdf->SetSaleDate($invoice['sale_date']);
                    $pdf->AddPage();
                    $pdf->SetAutoPageBreak(false, 20);
                    $pdf->SetFillColor(71, 130, 222);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetDrawColor(0);
                    $pdf->Ln();
                    $pdf->SetWidths(array(196));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetHeight(8);
                    $pdf->SetFill(array(true));
                    $pdf->SetDrawEdge(true);
                    $pdf->SetTextColors(array([255, 255, 255]));
                    $pdf->Row(array(utf8_decode('DATOS DE LA REMISIÓN')));

                    $pdf->SetHeight(7);
                    $pdf->SetFillColor(135, 180, 223);
                    $pdf->SetWidths(array(36, 62, 36, 62));
                    $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                    $pdf->SetFill(array(true, false, true, false));
                    $pdf->SetTextColors(array(0, 0, 0, 0));
                    $pdf->SetFont('Arial','B',8);
                    $pdf->Row(array(utf8_decode('CÓDIGO DE CLIENTE:'), utf8_decode($invoice['customer_serial']), 'EMPRESA EMISORA:', utf8_decode('REBASA')));
                    $pdf->SetWidths(array(98, 98));
                    $pdf->SetAligns(array('L', 'L'));
                    $pdf->SetFill(array(false, false));
                    $pdf->SetTextColors(array(0, 0));
                    $pdf->Row(array(NULL, utf8_decode('MÉXICO SA DE CV')));
                    $pdf->SetWidths(array(36, 62, 36, 62));
                    $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                    $pdf->SetFill(array(true, false, true, false));
                    $pdf->SetTextColors(array(0, 0, 0, 0));
                    $pdf->Row(array('CLIENTE:', utf8_decode(substr($invoice['customer_name'], 0, 36)), utf8_decode('FECHA DE REMISIÓN:'), $invoice['sale_date']));
                    if (strlen(utf8_decode(substr($invoice['customer_name'], 36, 60))) > 0) {
                        $pdf->SetWidths(array(98, 98));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(false, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array(utf8_decode(substr($invoice['customer_name'], 36, 60)), NULL));
                        $pdf->SetWidths(array(36, 62, 36, 62));
                        $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                        $pdf->SetFill(array(true, false, true, false));
                        $pdf->SetTextColors(array(255, 0, 255, 0));
                    }
                    $pdf->Row(array(utf8_decode('DIRECCIÓN FISCAL:'), utf8_decode(substr($customerAddress ? $customerAddress : '-', 0, 60)), utf8_decode('DIRECCIÓN FISCAL:'), utf8_decode(strtoupper('Colli Urbano Zapopan Jalisco'))));
                    // $pdf->SetWidths(array(98, 98));
                    // $pdf->SetAligns(array('L', 'L'));
                    // $pdf->SetFill(array(false, false));
                    // $pdf->SetTextColors(array(0, 0));
                    // $pdf->Row(array(utf8_decode(substr($customerAddress, 36, 60)), utf8_decode('')));
                    $pdf->SetWidths(array(36, 62, 36, 62));
                    $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                    $pdf->SetFill(array(true, false, true, false));
                    $pdf->SetTextColors(array(0, 0, 0, 0));
                    $pdf->Row(array('SUCURSAL:', utf8_decode($invoice['customer_branch_office']), 'SUCURSAL:', utf8_decode($invoice['branch_office'])));
                    if (strlen(utf8_decode(substr($invoice['customer_branch_office'], 36, 60))) > 0 || strlen(utf8_decode(substr($invoice['branch_office'], 36, 60))) > 0) {
                        $pdf->SetWidths(array(98, 98));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(false, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array(utf8_decode(substr($invoice['customer_branch_office'], 36, 60)), utf8_decode(substr($invoice['branch_office'], 36, 60))));
                        $pdf->SetWidths(array(36, 62, 36, 62));
                        $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                        $pdf->SetFill(array(true, false, true, false));
                        $pdf->SetTextColors(array(255, 0, 255, 0));
                    }
                    $pdf->Row(array(utf8_decode('DIRECCIÓN SUCURSAL:'), utf8_decode(substr($customerBranchOfficeAddress, 0, 60)), utf8_decode('DIRECCIÓN SUCURSAL:'), utf8_decode(substr($invoice['branch_office_address'], 0, 60))));
                    if (strlen(utf8_decode(substr($customerBranchOfficeAddress, 60, 60))) > 0 || strlen(utf8_decode(substr($invoice['branch_office_address'], 60, 60))) > 0) {
                        $pdf->SetWidths(array(98, 98));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(false, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array(utf8_decode(substr($customerBranchOfficeAddress, 60, 60)), utf8_decode(substr($invoice['branch_office_address'], 60, 60))));
                        $pdf->SetWidths(array(36, 62, 36, 62));
                        $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                        $pdf->SetFill(array(true, false, true, false));
                        $pdf->SetTextColors(array(0, 0, 0, 0));
                    }
                    $pdf->Row(array('PLAZO PAGO:', utf8_decode(is_numeric($invoice['term']) ? $invoice['term'].' DÍAS' : $invoice['term']), 'CHOFER:', utf8_decode(substr($invoice['driver'], 0, 36))));
                    if (strlen(utf8_decode(substr($invoice['driver'], 36, 60))) > 0) {
                        $pdf->SetWidths(array(98, 98));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(false, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array(NULL, utf8_decode(substr($invoice['driver'], 36, 60))));
                        $pdf->SetWidths(array(36, 62, 36, 62));
                        $pdf->SetAligns(array('L', 'L', 'L', 'L'));
                        $pdf->SetFill(array(true, false, true, false));
                        $pdf->SetTextColors(array(0, 0, 0, 0));
                    }
                    $pdf->Row(array(utf8_decode('HORARIO RECEPCIÓN:'), $invoice['open_horary'].' - '.$invoice['close_horary'], 'UNIDAD:', utf8_decode('')));

                    if (strlen(utf8_decode($invoice['comments'])) > 0) {
                        $pdf->SetWidths(array(36, 160));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(true, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array('COMENTARIOS DE LA REMISION:', utf8_decode(substr($invoice['comments'], 0, 90))));
                        if (strlen(utf8_decode(substr($invoice['comments'], 90, 180))) > 0) {
                            $pdf->SetWidths(array(196));
                            $pdf->SetAligns(array('L'));
                            $pdf->SetFill(array(false));
                            $pdf->SetTextColors(array(0));
                            $pdf->Row(array(utf8_decode(substr($invoice['comments'], 90))));
                        }
                    }
                    if (strlen(utf8_decode($invoice['cart_comment'])) > 0) {
                        $pdf->SetWidths(array(36, 160));
                        $pdf->SetAligns(array('L', 'L'));
                        $pdf->SetFill(array(true, false));
                        $pdf->SetTextColors(array(0, 0));
                        $pdf->Row(array('COMENTARIOS DEL PEDIDO:', utf8_decode(substr($invoice['cart_comment'], 0, 90))));
                        if (strlen(utf8_decode(substr($invoice['cart_comment'], 90, 180))) > 0) {
                            $pdf->SetWidths(array(196));
                            $pdf->SetAligns(array('L'));
                            $pdf->SetFill(array(false));
                            $pdf->SetTextColors(array(0));
                            $pdf->Row(array(utf8_decode(substr($invoice['cart_comment'], 90))));
                        }
                    }

                    $totalBales = 0;
                    $totalQty = 0;
                    $totalPrice = 0;
                    $totalAmount = 0;
                    $totalInBulkQty = 0;
                    foreach ($inBulkDetails as $detail) {
                        $totalQty += $detail['qty'];
                        $totalPrice += $detail['unit_price'];
                        $totalAmount += $detail['amount'];
                        $totalInBulkQty += $detail['qty'];
                    }

                    $groupedInBulkDetails = [];
                    foreach ($inBulkDetails as $detail) {
                        $recordedDetail = false;
                        for ($i=0; $i < count($groupedInBulkDetails); $i++) { 
                            if ($groupedInBulkDetails[$i]['product_id'] == $detail['product_id']) {
                                $groupedInBulkDetails[$i]['qty'] += $detail['qty'];
                                $groupedInBulkDetails[$i]['amount'] += $detail['amount'];
                                $recordedDetail = true;
                            }
                        }
                        if (!$recordedDetail) {
                            array_push($groupedInBulkDetails, $detail);
                        }
                    }

                    $classifiedInBulkDetails = [];
                    foreach ($inBulkDetails as $detail) {
                        $recordedDetail = false;
                        for ($i=0; $i < count($classifiedInBulkDetails); $i++) { 
                            if ($classifiedInBulkDetails[$i][0]['product_id'] == $detail['product_id']) {
                                array_push($classifiedInBulkDetails[$i], $detail);
                                $recordedDetail = true;
                            }
                        }
                        if (!$recordedDetail) {
                            array_push($classifiedInBulkDetails, [$detail]);
                        }
                    }

                    $pdf->Ln();
                    $pdf->Ln();
                    $tax = $totalAmount * 0.16;
                    $finalAmount = $totalAmount + $tax;
                    list($finalAmountWhole, $finalAmountDecimal) = explode('.', number_format($finalAmount, 2, '.', ''));
                    $finalAmountDecimal = ($finalAmountDecimal == 0) ? '00' : (($finalAmountDecimal < 10) ? ($finalAmountDecimal * 10) : $finalAmountDecimal);
                    $pdf->SetWidths(array(25, 35, 5, 130));
                    $pdf->SetAligns(array('R', 'R', 'C', 'L'));
                    $pdf->SetHeight(6);
                    $pdf->SetDrawEdge(false);
                    $pdf->SetFill(array(false, true, false, false));
                    $pdf->SetY($pdf->GetY()-6);
                    $pdf->SetFillColor(135, 180, 223);

                    foreach ($groupedInBulkDetails as $detail) {
                        if($detail['qty'] > 0){
                            $pdf->Cell(195, 6, utf8_decode('TOTAL DE PRODUCTOS ENTREGADOS: '.$detail['product'].' CANTIDAD: '.number_format($detail['qty'], 2, '.', ',').' PZAS.'), 0, 0, 'L');
                            $pdf->Ln();
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetTextColors(array(0, 0, 0, 0));
                    $pdf->Row(array('PESO TOTAL', number_format($totalQty, 2, '.', ',').' PZAS.', NULL, NULL));
                    $pdf->Row(array('SUBTOTAL', '$'.number_format($totalAmount, 2, '.', ','), NULL, NULL));
                    $pdf->Row(array('IVA', '$'.number_format($tax, 2, '.', ','), NULL, NULL));
                    $pdf->SetFill(array(false, true, false, true));
                    $pdf->Row(array('TOTAL', '$'.number_format($finalAmount, 2, '.', ','), NULL, ($finalAmountWhole == 0 ? 'CERO ' : NumeroALetras::convertir($finalAmountWhole))."PESOS $finalAmountDecimal/100 M.N."));
                    $pdf->Ln();
                    $pdf->SetTextColor(0);
                    $pdf->SetFillColor(135, 180, 223);
                    $pdf->SetFont('Arial','B',8);
                    $yAux = $pdf->GetY();
                    $pdf->Rect($pdf->GetX(),$pdf->GetY(),195,30,'DF');
                    $pdf->MultiCell(195, 6, utf8_decode('DEBO Y PAGARÉ INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE ALPEZ LA CANTIDAD DE $'.number_format($finalAmount, 2, '.', ',').' ('.NumeroALetras::convertir($finalAmountWhole)."PESOS $finalAmountDecimal/100 M.N.".')'), 0, 'J', false);
                    $pdf->SetY($yAux+24);
                    $pdf->Line($pdf->GetX()+70, $yAux+24, 205-70, $yAux+24);
                    $pdf->Cell(195, 6, 'FIRMA DE CLIENTE', 0, 0, 'C', false);

                    $pdf->AddPage();
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(0, 10, 'DETALLES', 0, 0, 'L');
                    $pdf->Ln();
                    foreach ($classifiedInBulkDetails as $cinBulkDetails) {
                        $pdf->SetFont('Arial','B',8);
                        $pdf->SetWidths(array(120, 25, 25, 25));
                        $pdf->SetAligns(array('C', 'C', 'C', 'C'));
                        $pdf->SetHeight(6);
                        $pdf->SetDrawEdge(false);
                        $pdf->SetFill(array(true, true, true, true));
                        $pdf->SetTextColors(array(0, 0, 0, 0));
                        $pdf->SetFillColor(135, 180, 223);
                        $pdf->Row(array('PRODUCTOS', 'CANTIDAD', 'UNITARIO', 'IMPORTE'));
                        $pdf->SetAligns(array('L', 'R', 'R', 'R'));
                        $pdf->SetFillColor(71, 130, 222);
                        $pdf->SetTextColors(array(0, 0, 0, 0));
                        $fill = false;
                        $tqty = 0;
                        foreach ($cinBulkDetails as $detail) {
                            $pdf->SetFill(array($fill, $fill, $fill, $fill));
                            $pdf->Row(array(utf8_decode($detail['product']), number_format($detail['qty'], 2, '.', ',').' PZAS.', '$'.number_format($detail['unit_price'], 2, '.', ','), '$'.number_format($detail['amount'], 2, '.', ',')));
                            $fill = !$fill;
                            $totalQty += $detail['qty'];
                            $tqty += $detail['qty'];
                            $totalPrice += $detail['unit_price'];
                            $totalAmount += $detail['amount'];
                        }
                        $pdf->SetFillColor(135, 180, 223);
                        $pdf->SetAligns(array('R', 'R', 'R', 'C'));
                        $pdf->SetFill(array(false,true, false, false));
                        $pdf->SetTextColors(array(0));
                        $pdf->Row(array('TOTAL PRODUCTOS', number_format($tqty, 2, '.', ',').' PZAS.', NULL,NULL));
                        $tqty = 0;
                        $pdf->Ln();
                    }

                    $pdf->SetTitle('Remisión #'.$invoice['id'],true);

                    return $pdf;
                }
            }
        }
        return null;
    }


    public function getPdf ($id)
    {
        if (is_numeric($id)) {
            $invoice = Invoices::findFirst($id);

            if ($invoice) {
                $pdf = $this->generatePdf($id);
                
                if (!is_null($pdf)) {
                    $pdf->Output('I', "Remisión #$invoice->id.pdf", true);

                    $response = new Phalcon\Http\Response();
                    $response->setHeader("Content-Type", "application/pdf");
                    $response->setHeader("Content-Disposition", 'inline; filename="Remisión #'.$invoice->id.'.pdf"');
                    return $response;
                }
            }
        }
        return null;
    }

    public function sendPdfToCustomer ($id)
    {
        if (is_numeric($id)) {
            $invoice = Invoices::findFirst($id);
            if ($invoice && $invoice->status == 'ENVIADO') {
                $shoppingCart = ShoppingCart::findFirst($invoice->shopping_cart_id);
                if ($shoppingCart) {
                    // Enviar email al Cliente
                    $customerId = intval($shoppingCart->customer_id);
                    $msg = $this->sendEmailToCustomer($invoice,$customerId);
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success($msg);
                }
            } else {
                $this->content['message'] = Message::error('No se ha encontrado la venta o no se encuentra Remisonada.');
                $this->content['result'] = false;
            }
        } else {
            $this->content['message'] = Message::error('No se ha recibido una id de venta válida.');
            $this->content['result'] = false;
        }
        $this->response->setJsonContent($this->content);
    }

    private function savePdf ($id)
    {
        if (is_numeric($id)) {
            $invoice = Invoices::findFirst($id);

            if ($invoice) {
                $pdf = $this->generatePdf($id);

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/invoices/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Remisión #$invoice->id.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
            }
        }
        return null;
    }


    public function create ()
    {
        if ($this->userHasPermission()) {
            try {
                $validUser = Auth::getUserData($this->config);
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $someError = false;

                $baleStorage = Storages::findFirst("branch_office_id = ".$request['branch_office_id']." AND storage_type_id = 1");
                $inBulkStorage = Storages::findFirst("branch_office_id = ".$request['branch_office_id']." AND storage_type_id = 13");
                $laminateStorage = Storages::findFirst("branch_office_id = ".$request['branch_office_id']." AND storage_type_id = 8");
                if ($baleStorage) {
                    $baleMovement = new Movements();
                    $baleMovement->storage_id = $baleStorage->id;
                    $baleMovement->type = 2;
                    if (!$baleMovement->create()) {
                        $someError = true;
                    }
                }
                if ($inBulkStorage) {
                    $inBulkMovement = new Movements();
                    $inBulkMovement->storage_id = $inBulkStorage->id;
                    $inBulkMovement->type = 2;
                    if (!$inBulkMovement->create()) {
                        $someError = true;
                    }
                }
                if ($laminateStorage) {
                    $laminateMovement = new Movements();
                    $laminateMovement->storage_id = $laminateStorage->id;
                    $laminateMovement->type = 2;
                    if (!$laminateMovement->create()) {
                        $someError = true;
                    }
                }
                if ($someError) {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos.');
                } else {
                    $customerBranchOffice = CustomerBranchOffices::findFirst($request['customer_branch_office_id']);
                    if ($customerBranchOffice) {
                        $customer = Customers::findFirst("id = $customerBranchOffice->customer_id AND active");
                        if ($customer) {
                            $invoice = new Invoices();
                            $invoice->setTransaction($tx);
                            $invoice->sale_date = $request['sale_date'];
                            $invoice->agent_id = $validUser->id;
                            $invoice->branch_office_id = intval($request['branch_office_id']);
                            $invoice->customer_branch_office_id = intval($request['customer_branch_office_id']);
                            $invoice->driver_id = intval($request['driver_id']);
                            if ($baleStorage) {
                                $invoice->bale_movement_id = $baleMovement->id;
                            }
                            if ($inBulkStorage) {
                                $invoice->in_bulk_movement_id = $inBulkMovement->id;
                            }
                            if ($laminateStorage) {
                                $invoice->laminate_movement_id = $laminateMovement->id;
                            }
                            if (isset($request['comments']) && $request['comments'] && strlen($request['comments']) > 0) {
                                $invoice->comments = strtoupper($request['comments']);
                            }

                            if ($invoice->create()) {
                                $this->content['result'] = true;
                                $this->content['invoice'] = $invoice;
                                $this->content['message'] = Message::success('Venta de fibra registrada correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de fibra.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado el cliente seleccionado.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la sucursal de cliente seleccionada.');
                        $tx->rollback();
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function uploadDocumentFile ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $invoice = Invoices::findFirst($id);
                if ($invoice) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/documents/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $fullPath = $upload_dir . $fileName;
                        if ($invoice->document_file != null && file_exists($upload_dir.$invoice->document_file)) {
                            @unlink($upload_dir.$invoice->document_file);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $invoice->setTransaction($tx);
                        $invoice->document_file = $fileName;
                        if ($invoice->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El documento ha sido subido exitosamente.');
                        } else {
                            $this->content['message'] = Message::error('Error al subir documento.');
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
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $invoice = Invoices::findFirst($id);
                $request = $this->request->getPut();
                if ($invoice) {
                    if ($invoice->status == 'REMISIONADO') {
                        $invoice->setTransaction($tx);
                        // if (isset($request['sale_date'])) {
                        //     $invoice->sale_date = $request['sale_date'];
                        // }
                        // if (isset($request['customer_branch_office_id']) && is_numeric($request['customer_branch_office_id'])) {
                        //     $customerBranchOffice = CustomerBranchOffices::findFirst($request['customer_branch_office_id']);
                        //     if ($customerBranchOffice) {
                        //         $customer = Customers::findFirst("id = $customerBranchOffice->customer_id AND active");
                        //         if ($customer) {
                        //             $invoice->customer_branch_office_id = $request['customer_branch_office_id'];
                        //         }
                        //     }
                        // }
                        // if (isset($request['driver_id']) && is_numeric($request['driver_id'])) {
                        //     $invoice->driver_id = $request['driver_id'];
                        // }
                        if (isset($request['comments']) && $request['comments'] && strlen($request['comments']) > 0) {
                            $invoice->comments = strtoupper($request['comments']);
                        }
                        if (isset($request['carrier_id']) && $request['carrier_id'] && strlen($request['carrier_id']) > 0) {
                            $invoice->carrier_id = $request['carrier_id'] ? intval($request['carrier_id']) : null;
                        }
                        if (isset($request['carrier_name']) && $request['carrier_name'] && strlen($request['carrier_name']) > 0) {
                            $invoice->carrier_name = $request['carrier_name'] ? strtoupper($request['carrier_name']) : null;
                        }
                        if (isset($request['guide_number']) && $request['guide_number'] && strlen($request['guide_number']) > 0) {
                            $invoice->guide_number = $request['guide_number'] ? $request['guide_number'] : null;
                        }
                        if ($invoice->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('Remisión modificada correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($invoice);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la venta de fibra.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('La venta de fibra no se puede modificar debido a que ya fue entregada.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function changeDocumentsReturnedByDriver ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $invoice = Invoices::findFirst($id);
                if ($invoice) {
                    $invoice->setTransaction($tx);
                    $invoice->documents_returned_by_driver = !$invoice->documents_returned_by_driver;
                    if ($invoice->update()) {
                        $this->content['result'] = true;
                        if ($invoice->documents_returned_by_driver) {
                            $this->content['message'] = Message::success('Documentos validados.');
                        } else {
                            $this->content['message'] = Message::success('Documentos no regresados.');
                        }
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($invoice);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la venta de fibra.');
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }
    public function sendEmailToCustomer($invoice,$customerId) {
        $msg = null;
        $actions = Actions::findFirst(2);
        if ($actions->host && $actions->port && $actions->username && $actions->password) {
            $customer = Customers::findFirst($customerId);
            if ($customer->email) {
                $arrayemail = [];
                $getEmails = "SELECT email from sls_customer_contacts where customer_id = $customer->id";
                $querygetEmails = $this->db->query($getEmails)->fetchAll();
                
                
                $arrayemail = [];

                foreach($querygetEmails as $value){
                    array_push($arrayemail, "".$value['email']."");
                }
                
                array_push($arrayemail, "".$customer->email."");
                /* $getEmailContact = "SELECT * FROM sls_customer_contacts where customer_id = $customer->id order by id desc limit 1";
                $emailcontact = $this->db->query($getEmailContact)->fetchAll();
                if (count($emailcontact)) {
                    array_push($arrayemail, $customer->email, $emailcontact[0]['email']);
                } else {
                    array_push($arrayemail, $customer->email);
                } */
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
                            Estimado Cliente <strong>'.$customer->tradename.'</strong>.
                            <br>
                            <br>
                            Adjunto encontrará la Remisión #'.$invoice->id.', contenido en la misma encontrará la fecha de entrega requerida y las especificaciones.
                            <br>
                            <br>
                            Es importante recordar que las condiciones para recepción son las siguientes:
                            <br>
                            <br>
                        </p>
                    </body>
                </html>';
                $this->savePdf($invoice->id);
                $fileName = __DIR__.'./../../public/assets/invoices/';
                $transport = (new Swift_SmtpTransport($actions->host, $actions->port, $actions->encryption))
                ->setUsername($actions->username)
                ->setPassword($actions->password);
                // Create the Mailer using your created Transport
                $mailer = new Swift_Mailer($transport);
                // Create a message
                $message = (new Swift_Message('Estimado Cliente.'))
                ->setFrom([$actions->username => 'REBASA'])
                ->setTo($arrayemail)
                ->setBody($htmlBody,'text/html')
                ->attach(Swift_Attachment::fromPath($fileName.'Remisión #'.$invoice->id.'.pdf')->setFilename('Remisión #'.$invoice->id.'.pdf'));
                // Send the message
                $mailer->send($message);
                $msg.= "Correo enviado correctamente al Cliente";
            } else {
                $msg .= '; No se ha enviado el correo debido a que el Cliente no tiene correo registrado.';
            }
        } else {
            $msg .= '; No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
        }
        return $msg;
    }
    public function uploadFile ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $invoice = Invoices::findFirst($id);
                $request = $this->request->getPost();
                if ($invoice) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/pallets/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $invoice->id .'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($invoice->pallet_document != null && file_exists($upload_dir.$invoice->pallet_document)) {
                            @unlink($upload_dir.$invoice->pallet_document);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $invoice->setTransaction($tx);
                        $invoice->pallet_document = $fileName;
                        if ($invoice->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo Foto de Tarima se ha subido exitosamente.');
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
    public function deleteDocument ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $document = Invoices::findFirst($id);
                $extension = explode(".", $document->pallet_document);
                // var_dump($extension[count($extension) - 1]);
                if ($document) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/pallets/';
                    $extension = explode(".", $document->pallet_document);
                    if ($document->pallet_document != null && file_exists($upload_dir.$document->id .'.'.$extension[count($extension) - 1])) {
                        if (@unlink($upload_dir.$document->id.'.'.$extension[count($extension) - 1])) {
                            $document->setTransaction($tx);
                            $document->pallet_document = null;
                            if ($document->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El documento ha sido eliminado correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($document);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el documento.');
                            }
                        }
                    } else {
                        $document->setTransaction($tx);
                        $document->pallet_document = null;
                        if ($document->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El documento ha sido eliminado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($document);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el documento.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el documento seleccionado.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id de documento válida.');
        }

        $this->response->setJsonContent($this->content);
    }
    public function downloadDocumentFilePallet ($id)
    {
        if (is_numeric($id)) {
            $document = Invoices::findFirst($id);
            if ($document && $document->pallet_document) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/pallets/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777);
                }
                $extension = explode( '.', $document->pallet_document );
                $fullPath = $upload_dir . $document->id.'.'.$extension[1];
            }
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
            }
        }
        return null;
    }
    public function remission ($id)
    {
        date_default_timezone_set('America/Mexico_City');
        if ($this->userHasPermission()) {
            if (!is_null($id) && is_numeric($id)) {
                try {
                    $invoice = Invoices::findFirst($id);
                    $request = $this->request->getPut();
                    $shoppingCart =  ShoppingCart::findFirst($invoice->shopping_cart_id);
                    if ($invoice && $invoice->status == 'REMISIONADO') {
                        $invoiceInBulkDetails = InvoiceInBulkDetails::find("invoice_id = $id");
                        if (count($invoiceInBulkDetails) > 0) {
                            $canExecuteMovement = true;
                            $inBulkProductsNotAvailable = [];
                            $inBulkMovement = Movements::findFirst($invoice->in_bulk_movement_id);

                            $availableBales = [];
                            $bales = [];
                            $movements = new MovementsController();
                            if($invoiceInBulkDetails->count() > 0){
                                $this->content['mensaje2'] = '2';
                                $idStorage = intval($inBulkMovement->storage_id);
                                $availableInBulkProducts =[];
                                foreach ($invoiceInBulkDetails as $detail){
                                    $aux = $movements->generateStorageInventoryv3(null,$idStorage,null,null,$detail->product_id,null,null,null);
                                    array_push($availableInBulkProducts, $aux['data'][0]);
                                }
                                $total = 0;
                                foreach ($invoiceInBulkDetails as $detail) {
                                    $productStock = null;
                                    foreach ($availableInBulkProducts as $availableInBulkProduct) {
                                        if ($detail->product_id == $availableInBulkProduct['product_id']) {
                                            $productStock = $availableInBulkProduct;
                                            if (intval($detail->qty) > intval($availableInBulkProduct['stock'])) {
                                                $canExecuteMovement = false;
                                                $product = Products::findFirst($detail->product_id);
                                                array_push($inBulkProductsNotAvailable, $product->name);
                                            }
                                        }
                                    }
                                    if ($canExecuteMovement) {
                                        if (is_null($productStock)) {
                                            /* $product = Products::findFirst($detail->product_id);
                                            array_push($inBulkProductsNotAvailable, $product->name); */
                                            // $canExecuteMovement = false;
                                        } else {
                                            if($detail->qty > 0){
                                                $movementDetail = new MovementDetails();
                                                $movementDetail->movement_id = $inBulkMovement->id;
                                                $movementDetail->product_id = $detail->product_id;
                                                $movementDetail->qty = $detail->qty;
                                                $movementDetail->unit_price = $detail->unit_price;
                                                $movementDetail->create();
                                            }
                                        }
                                    }
                                    $total += $detail->qty * $detail->unit_price * 1.16;
                                }
                                if($shoppingCart->tax_invoice == 1){
                                    $invoice->status_payment = 2;
                                    $validUser = Auth::getUserData($this->config);
                                    $payment = new Payments();
                                    $payment->created_by = $validUser->id;
                                    $payment->remision_id = $invoice->id;
                                    $payment->amount = $total;
                                    $payment->payment_date = $invoice->created;
                                    $payment->reference = 'Pago de contado';
                                    $payment->method = 1;
                                    $payment->create();
                                }
                            }
                            if ($canExecuteMovement) {
                                $tx = $this->transactions->get();
                                if ($invoice->status == 'REMISIONADO') {
                                    $invoice->setTransaction($tx);
                                    $invoice->status = 'ENVIADO';
                                    // $invoice->carrier_id = $request['carrier_id'] ? intval($request['carrier_id']) : null;
                                    // $invoice->carrier_name = $request['carrier_name'] ? strtoupper($request['carrier_name']) : null;
                                    // $invoice->guide_number = $request['guide_number'] ? $request['guide_number'] : null;
                                    if ($invoice->update()) {
                                        $success = true;
                                        $movementDate = $request['isHistoric'] == "true" ? $invoice->sale_date : date('Y-m-d H:i:s');
                                        if ($inBulkMovement && $inBulkMovement->status == 'NUEVO') {
                                            $inBulkMovement->setTransaction($tx);
                                            $inBulkMovement->status = 'EJECUTADO';
                                            $inBulkMovement->date = $movementDate;
                                            if (!$inBulkMovement->update()) {
                                                $success = false;
                                                $this->content['error'] = Helpers::getErrors($inBulkMovement);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar remisionar la venta de fibra.');
                                            }
                                        }
                                        if ($success) {
                                            $tx->commit();
                                            $msg = 'Venta de productos remisionada correctamente';

                                            $this->content['result'] = true;
                                            $this->content['message'] = Message::success($msg);
                                        } else {
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar remisionar la venta de fibra.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('La venta de productos no se puede remisionar.');
                                }
                            }else {
                                $errorMsg = "La venta de productos no se puede remisionar.";
                                if (count($inBulkProductsNotAvailable) > 0) {
                                    $errorMsg .= " Productos no disponibles: ".implode(", ", $inBulkProductsNotAvailable).".";
                                }
                                $this->content['message'] = Message::error($errorMsg);
                            }
                        } else {
                            $this->content['message'] = Message::error('La venta de productos no cuenta con detalles.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la venta de productos.');
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

    public function deliver ($id, $token)
    {
        if (is_numeric($id) && !is_null($token)) {
            $invoice = Invoices::findFirst($id);
            if ($invoice) {
                $customerBranchOffice = CustomerBranchOffices::findFirst($invoice->customer_branch_office_id);
                if ($customerBranchOffice) {
                    $customer = Customers::findFirst($customerBranchOffice->customer_id);
                    $email = null;
                    if (!is_null($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL) && password_verify($customer->email, $token)) {
                        $email = $customer->email;
                    } elseif (!is_null($customer->email2) && filter_var($customer->email2, FILTER_VALIDATE_EMAIL) && password_verify($customer->email2, $token)) {
                        $email = $customer->email2;
                    } elseif (!is_null($customer->email3) && filter_var($customer->email3, FILTER_VALIDATE_EMAIL) && password_verify($customer->email3, $token)) {
                        $email = $customer->email3;
                    } elseif (!is_null($customer->email4) && filter_var($customer->email4, FILTER_VALIDATE_EMAIL) && password_verify($customer->email4, $token)) {
                        $email = $customer->email4;
                    }
                    if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        switch ($invoice->status) {
                            case 'ENVIADO':
                                $tx = $this->transactions->get();
                                $invoice->setTransaction($tx);
                                $invoice->status = 'ENTREGADO';
                                $invoice->deliver_status_by = $email;
                                $invoice->deliver_status_at = date('Y-m-d H:i:s');
                                if ($invoice->update()) {
                                    $tx->commit();
                                    $this->flash->notice('Gracias, la venta ha sido marcada como entregada');
                                } else {
                                    $this->flash->notice('Ha ocurrido un error al intentar cambiar el estatus de la venta');
                                }
                                break;

                            case 'ENTREGADO':
                                $this->flash->notice('La venta ya ha sido marcada como entregada anteriormente');
                                break;
                            
                            default:
                                $this->flash->notice('La venta no puede ser marcada como entregada');
                                break;
                        }
                    } else {
                        $this->flash->notice('El token no es válido.');
                    }
                }
            }
        }
    }

    public function downloadDocumentFile ($id)
    {
        if (is_numeric($id)) {
            $invoice = Invoices::findFirst($id);
            if ($invoice && $invoice->document_file) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/invoices/documents/';
                $fullPath = $upload_dir.$invoice->document_file;
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
                    $this->flash->notice('No se ha encontrado el archivo de documento.');
                }
            }
        }
        return null;
    }

    public function delete ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $invoice = Invoices::findFirst($id);
                if ($invoice) {
                    $movement = Movements::findFirst($invoice->movement_id);
                    if ($movement) {
                        if ($movement->status == 0) {
                            $invoice->setTransaction($tx);

                            if ($invoice->delete()) {
                                if ($movement->delete()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('La venta de fibra ha sido eliminada.');
                                    $tx->commit();
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                                }
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                            }
                        } else {
                            $this->content['message'] = Message::error('La venta de fibra no se puede eliminar debido a que ya fue entregada.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 4 OR role_id = 2 OR role_id = 3 OR role_id = 20 OR role_id = 17 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 28 )
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    public function getFiscalData ($id)
    {
        if ($this->userHasPermission()) {
            $invoice = null;
            if (is_numeric($id)) {
                $invoice = Invoices::findFirst($id);
                $sql = "SELECT fecha_factura, tipo_comprobante, i.metodo_pago, i.forma_pago as forma_pago_id, i.uso_cfdi as uso_cfdi_id, 
                case when tax_company_id = 0 then 'PUBLICO EN GENERAL' when ctc.razon_social is null then c.name else ctc.razon_social end as razon_social, 
                case when tax_company_id = 0 then 'XAXX010101000' when ctc.rfc is null then c.rfc else ctc.rfc end as rfc, sfp.descripcion as forma_pago, 
                suc.descripcion as uso_cfdi, c.email as email_cliente, tipo_cliente, 
                case when i.lugar_expedicion is null then bb.codigo_postal else i.lugar_expedicion end as lugar_expedicion, 
                c.email as email_cliente, i.tax_company_id, i.status_timbrado, i.folio_fiscal,bb.serie,i.regimen_fiscal,
                ctc.immex,i.import,i.export
                FROM sls_invoices AS i
                JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN sat_formas_pagos AS sfp ON sfp.id = i.forma_pago
                LEFT JOIN sat_uso_cfdi AS suc ON suc.id = i.uso_cfdi
                LEFT JOIN sls_shopping_cart AS sc ON sc.id = i.shopping_cart_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = sc.branchoffice
                LEFT JOIN sls_customer_tax_companies as ctc ON ctc.id = i.tax_company_id
                WHERE i.id = {$id}
                LIMIT 1";
                $fiscal = $this->db->query($sql)->fetch();
                if (empty($fiscal['folio_fiscal'])) {
                    $sql = "SELECT folio_fiscal
                    FROM sls_invoices AS i
                    JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                    JOIN sls_customers AS c ON c.id = cbo.customer_id
                    WHERE i.id != {$id} and i.serie = '".$fiscal['serie']."' and folio_fiscal is not null order by i.folio_fiscal desc limit 1";
                    $busqueda = $this->db->query($sql)->fetch();
                    if(!isset($busqueda['folio_fiscal']) && empty($busqueda['folio_fiscal'])){
                        $fiscal['folio_fiscal'] = null;
                    }else{
                        $fiscal['folio_fiscal'] =  $busqueda['folio_fiscal'] + 1;
                    }
                }
                if (empty($fiscal['fecha_factura'])) {
                    $fiscal['fecha_factura'] = date('Y-m-d H:i:s');
                    //$fiscal['fecha_factura'] = date('Y-m-d 12:00:00');
                }
            }
            $this->content['fiscal'] = $fiscal;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getDateCurrent () {
        date_default_timezone_set('America/Mexico_City');
        if ($this->userHasPermission()) {
            $this->content['dateCurrent'] = date('Y-m-d 12:00:00');
            $this->content['result'] = true;
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getFormaPagoOptions () {
        $sql = "SELECT id AS value, descripcion AS label FROM sat_formas_pagos ORDER BY descripcion ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getUsoCFDIOptions () {
        $sql = "SELECT id AS value, descripcion AS label FROM sat_uso_cfdi ORDER BY descripcion ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

   /* public function getNextFolio ($id)
    {
        $folio = 1;
        $invoice = Invoices::findFirst($id);
        if ($invoice) {
            $sql = "SELECT folio_fiscal
                FROM sls_invoices AS i
                JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                JOIN sls_customers AS c ON c.id = cbo.customer_id
                WHERE i.id != {$id}  order by i.folio_fiscal desc";
            $nextFolio = $this->db->query($sql)->fetchAll();
            if ($nextFolio) {
                $folio = intval($nextFolio[0]['folio_fiscal']) + 1;
            }
        }
        $this->content['folio_fiscal'] = $folio;
        $this->content['result'] = 'success';
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }*/

    /*public function getNextFolio($id)
    {
        $folio = 1;
        $invoice = Invoices::findFirst($id);
        $serie =$invoice->serie;
            //            $sql = "SELECT folio_fiscal FROM sls_invoices AS i JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id JOIN sls_customers AS c ON c.id = cbo.customer_id WHERE i.serie = '".$serie."' order by i.folio_fiscal desc";
            $nextFolio = $this->db->query("SELECT folio_fiscal FROM sls_invoices AS i WHERE i.serie = '" . $serie . "' AND i.folio_fiscal IS NOT NULL ORDER BY i.folio_fiscal DESC")->fetch();
            print_r($nextFolio);
            exit();
            $nextFolioInvoices = $this->db->query("SELECT folio_fiscal FROM sls_invoices AS i WHERE i.serie = '" . $serie . "' AND i.folio_fiscal IS NOT NULL ORDER BY i.folio_fiscal DESC")->fetch();

            $folio = $nextFolio['folio_fiscal'] ? intval($nextFolio['folio_fiscal']) + 1 : intval($nextFolioInvoices['folio_fiscal']) + 1;
        $this->content['folio_fiscal'] = $folio;
        $this->content['result'] = 'success';
        $this->response->setJsonContent($this->content);
        $this->response->send();
        // return $folio;
    }*/
     public function getNextFolio($id, $serie)
    {
        $folio = 217095;
        switch ($serie) {
            case 'A':
                $folio = 217095;
                break;
            case 'M':
                $folio = 212864;
                break;
            case 'L':
                $folio = 201937;
                break;
            default:
                $folio = 1;
                break;
        }
        $invoice = Invoices::findFirst($id);

        $nextFolio = $this->db->query("SELECT max(folio_fiscal) as folio_fiscal from sls_invoices as i WHERE i.serie = '$serie' AND i.folio_fiscal IS NOT NULL ")->fetch();
        if($nextFolio){
            if($nextFolio['folio_fiscal'] !== null ){
                $folio = $nextFolio['folio_fiscal'] + 1;
            }
        }
        $this->content['folio_fiscal'] = $folio;
        $this->content['result'] = 'success';
        $this->response->setJsonContent($this->content);
        $this->response->send();
        // return $folio;
    }
    public function updateFiscal ($id)
    {   

        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $fiscal = Invoices::findFirst($id);
                if ($fiscal) {
                    $fiscal->setTransaction($tx);
                    $fiscal->tipo_cliente = !empty($request['tipo_cliente']) ? $request['tipo_cliente'] : 'cliente';
                    // $Folio_F = $this->getNextFolio($id,$request['serie']);

                    /* if($request['folio_fiscal'] == null || $request['folio_fiscal'] == ''){
                    $Folio_F = $this->getNextFolio($id,$request['serie']);
                    $fiscal->folio_fiscal = $Folio_F;
                    } */
                    $fiscal->folio_fiscal = !empty($request['folio_fiscal']) ? $request['folio_fiscal'] : NULL;
                    $fiscal->serie =  !empty($request['serie']) ? $request['serie'] : NULL;
                    $fiscal->fecha_factura = !empty($request['fecha_factura']) ? $request['fecha_factura'] : NULL;
                    $fiscal->lugar_expedicion = !empty($request['lugar_expedicion']) ? $request['lugar_expedicion'] : NULL;
                    $fiscal->tipo_comprobante = !empty($request['tipo_comprobante']) ? $request['tipo_comprobante'] : NULL;
                    $fiscal->metodo_pago = !empty($request['metodo_pago']) ? $request['metodo_pago'] : NULL;
                    $fiscal->forma_pago = !empty($request['forma_pago']) ? $request['forma_pago'] : NULL;
                    $fiscal->uso_cfdi = !empty($request['uso_cfdi']) ? $request['uso_cfdi'] : NULL;
                    $fiscal->folio_relacionado = !empty($request['folio_relacionado']) ? $request['folio_relacionado'] : NULL;
                    $fiscal->tax_company_id = !is_null($request['tax_company_id']) ? $request['tax_company_id'] : NULL;
                    $fiscal->regimen_fiscal = !empty($request['regimen_fiscal']) ? $request['regimen_fiscal'] : NULL;
                    $fiscal->import = !empty($request['import']) ? $request['import'] : NULL;
                    $fiscal->export = !empty($request['export']) ? $request['export'] : NULL;

                    if($fiscal->folio_relacionado != null && $fiscal->folio_relacionado != ''){
                        $invoiceSustituye = InvoicesFolios::findFirst(
                            "folio_fiscal = $fiscal->folio_relacionado 
                            and serie = '$fiscal->serie' 
                            and status_timbrado = 2 
                            and tax_company_id = $fiscal->tax_company_id");
                        if(!$invoiceSustituye){
                            $this->content['errors'] = "Verifiacar que el folio corresponda a una factura del mismo cliente y este cancelada.";
                            $this->content['message'] = Message::error('Folio que sustituye no es valido.');
                            $response = ['error' => 'No se encontro la Factura.'];
                            $this->response->setJsonContent($this->content);
                            $this->response->send();
                            return;
                        }
                    }

                    if ($fiscal->update()) {
                        $this->content['result'] = 'success';
                        $this->content['message'] = ['title' => '¡Exito!', 'content' => 'Se han actualizado los datos fiscales.'];
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($fiscal);
                        $this->content['message'] = ['title' => '¡Error!', 'content' => 'No se han actualizado los datos fiscales.'];
                        $tx->rollback();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }   
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

     public function timbrar ($id)
    {   
        date_default_timezone_set('America/Mexico_City');
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $invoice = Invoices::findFirst($id);
                $invoice->setTransaction($tx);
                if ($invoice) {
                    $invoice->tax_company_id = isset($request['tax_company_id']) ? $request['tax_company_id'] : null;
                    $invoice->tipo_cliente = !empty($request['tipo_cliente']) ? $request['tipo_cliente'] : 'cliente';
                    $invoice->folio_fiscal = $invoice->status_timbrado == 2 ? 1 : $request['folio_fiscal'];
                    // $invoice->serie =  NULL;
                    $invoice->serie = !empty($request['serie']) ? $request['serie'] : NULL;
                    //$invoice->fecha_factura = date("Y-m-d H:i:s");
                    $invoice->fecha_factura = !empty($request['fecha_factura']) ? $request['fecha_factura'] : NULL;
                
                    $invoice->lugar_expedicion = !empty($request['lugar_expedicion']) ? $request['lugar_expedicion'] : NULL;
                    $invoice->tipo_comprobante = 'I';
                    $invoice->metodo_pago = !empty($request['metodo_pago']) ? $request['metodo_pago'] : NULL;
                    $invoice->forma_pago = !empty($request['forma_pago']) ? $request['forma_pago'] : NULL;
                    $invoice->uso_cfdi = !empty($request['uso_cfdi']) ? $request['uso_cfdi'] : NULL;
                    $invoice->folio_relacionado = !empty($request['folio_relacionado']) ? $request['folio_relacionado'] : NULL;
                    $invoice->regimen_fiscal = !empty($request['regimen_fiscal']) ? $request['regimen_fiscal'] : NULL;
                    $invoice->import = !empty($request['import']) ? $request['import'] : NULL;
                    $invoice->export = !empty($request['export']) ? $request['export'] : NULL;

                    $invoiceSustituye = null;
                    if($invoice->folio_relacionado != null && $invoice->folio_relacionado != ''){
                        $invoiceSustituye = InvoicesFolios::findFirst(
                            "folio_fiscal = $invoice->folio_relacionado 
                            and serie = '$invoice->serie' 
                            and status_timbrado = 2 
                            and tax_company_id = $invoice->tax_company_id");
                        if(!$invoiceSustituye){
                            $this->content['errors'] = "Verifiacar que el folio corresponda a una factura del mismo cliente y este cancelada.";
                            $this->content['message'] = Message::error('Folio que sustituye no es valido.');
                            $this->content['result'] = 'error';
                            $response = ['error' => 'No se encontro la Factura.'];
                            $this->response->setJsonContent($this->content);
                            $this->response->send();
                            return;
                        }
                    }
                    if (!$invoice->update()) {
                        $this->content['error'] = Helpers::getErrors($invoice);
                        $this->content['message'] = ['title' => '¡Error!', 'content' => 'No se han actualizado los datos fiscales.'];
                        $tx->rollback();
                    }else {
                        $shoppingcart = ShoppingCart::findFirst($invoice->shopping_cart_id);
                        if ($shoppingcart) {
                            $shoppingcart->setTransaction($tx);
                            // se cambia pedido a no prestamo
                            $shoppingcart->loan = 0;
                            if(!$shoppingcart->update()){
                                $tx->rollback();
                            }
                        }
                    }
                }
                $regimenFiscal = null;
                $immex = null;
                $banderaRegimenFiscal = false;
                $domicilioFiscal = $invoice->lugar_expedicion;
                $invoice = Invoices::findFirst($id);
                $invoiceDetail = InvoiceDetails::find("invoice_id = $id");
                $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $id");
                $invoiceLaminateDetail = InvoiceLaminateDetails::find("invoice_id = $id");
                $cliente = Customers::findFirst($invoice->CustomerBranchOffices->customer_id);
                if (!empty($invoice->tax_company_id)) {
                    $cliente = CustomerTaxCompanies::findFirst($invoice->tax_company_id);
                    $cliente->name = $cliente->razon_social;
                    $regimenFiscal = $cliente->regimen_fiscal;
                    $domicilioFiscal = $cliente->lugar_expedicion;
                    $immex = $cliente->immex;
                }
                $email = '';
                if (!empty($cliente->email)) {
                    $email = '<email>'.$cliente->email.'</email>';
                }
                if ($invoice->tipo_cliente != 'publico' && $invoice->tax_company_id != 0) {
                    $rfc = $cliente->rfc;
                    $razon_social = str_replace("&", "_AMP_", $cliente->name);
                } else {
                    $rfc = 'XAXX010101000';
                    $razon_social = 'Publico en general';
                }
                /*if (is_null($empresa->batuta_id)) {
                    $this->content['message'] = ['title' => '¡Error!', 'content' => 'La empresa no cuenta con un ID para timbrar.'];
                    $tx->rollback();
                }*/
                //Regimen fiscal Régimen Simplificado de confianza codigo 626
                $regimenFiscalRetenciones = 0;
                $impuestosRetenidos = 0;
                if($regimenFiscal != null){
                    if ($regimenFiscal == 626) {
                        $regimenFiscalRetenciones = 0.0125;
                        $banderaRegimenFiscal = true; 
                    }
                }

                $subtotal = 0;
                $total = 0;
                $totalImpuestosTrasladados = 0;
                $totalImpuestosTrasladadosIva = 0;
                $cabezera = $cuerpo = '';
                $fecha = str_replace(' ', 'T', $invoice->fecha_factura);
                $productList = array();
                if ($invoiceDetail) {
                    foreach ($invoiceDetail as $key => $detalle) {
                        if (isset($productList[$detalle->Bales->Products->id])) {
                            $productList[$detalle->Bales->Products->id]->Bales->qty += $detalle->Bales->qty;
                        } else {
                            $productList[$detalle->Bales->Products->id] = $detalle;
                        }
                    }
                    foreach ($productList as $key => $detalle) {
                        $importe = number_format($detalle->unit_price * $detalle->Bales->qty,2,'.','');
                        $cuerpo =  $cuerpo.'
                                        <concepto>
                                            <cantidad>'.$detalle->Bales->qty.'</cantidad>
                                            <unidad>PIEZAS</unidad>
                                            <claveUnidad>H87</claveUnidad>
                                            <claveProdServ>'.$detalle->Bales->Products->clave_producto_id.'</claveProdServ>
                                            <noIdentificacion>'.$detalle->Bales->Products->code.'</noIdentificacion>
                                            <descripcion>'.$detalle->Bales->Products->name.'</descripcion>
                                            <valorUnitario>'.number_format($detalle->unit_price,2,'.','').'</valorUnitario>
                                            <importe>'.$importe.'</importe>';
                        $subtotal += $importe; 
                        if($immex === false){
                            $impuesto_importe = number_format($importe * 0.16,2,'.','');
                            $totalImpuestosTrasladados += $impuesto_importe;
                            $cuerpo .= '<impuestos><traslados><traslado>
                                            <base>'.$importe.'</base>
                                            <impuesto>002</impuesto>
                                            <tipofactor>Tasa</tipofactor>
                                            <tasa>0.160000</tasa>
                                            <importe>'.$impuesto_importe.'</importe>
                                        </traslado></traslados></impuestos>';
                            $cuerpo = $cuerpo . '</concepto>';
                         }
                    }
                }
                $productList = array();
                $totalImpuestosTrasladadosIeps = false;
                if ($invoiceInBulkDetail) {
                    // foreach ($invoiceInBulkDetail as $key => $detalle) {
                    //     if (isset($productList[$detalle->product_id])) {
                    //         $productList[$detalle->product_id]->qty += $detalle->qty;
                    //     } else {
                    //         $productList[$detalle->product_id] = $detalle;
                    //     }
                    // }
                    //Regimen fiscal codigo 626
                    $totalImpuestosRetenidos = 0;
                    $totalregimen = 0;
                    foreach ($invoiceInBulkDetail as $key => $detalle) {
                        $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                        $subtotal += $importe;
                        
                        $ieps = '';
                        $impuesto_importe_ieps= 0;
                        if($detalle->ieps > 0){
                            $impuesto_importe_ieps = number_format(($importe / 1.16) - ($importe / 1.16 / (($detalle->ieps/100) +1) ) ,2,'.','');
                            $iepsTasa = $detalle->ieps/100;
                            if(!isset($totalImpuestosTrasladadosIeps["{$iepsTasa}"] )){$totalImpuestosTrasladadosIeps["{$iepsTasa}"] = 0;}
                            $totalImpuestosTrasladadosIeps["{$iepsTasa}"] += $impuesto_importe_ieps;
                            $totalImpuestosTrasladados  += $impuesto_importe_ieps;
                            $ieps = '<traslado>
                                <base>##baseImpuesto##</base>
                                <impuesto>003</impuesto>
                                <tipofactor>Tasa</tipofactor>
                                <tasa>'.$iepsTasa.'</tasa>
                                <importe>'.$impuesto_importe_ieps.'</importe>
                            </traslado>';
                        }
                        $impuesto_importe = number_format(($importe * 0.16),2,'.','');
                        $totalImpuestosTrasladadosIva += $immex === false? $impuesto_importe : 0;
                        $totalImpuestosTrasladados  += $immex === false? $impuesto_importe : 0;
                        $totalProducto = $importe-$impuesto_importe_ieps;
                        
                        /* var_dump($importe);
                        var_dump($subtotal);
                        var_dump($impuesto_importe);
                        var_dump($totalImpuestosTrasladadosIva);
                        var_dump($totalImpuestosTrasladados);
                        var_dump($totalProducto); */
                        //Regimen fiscal codigo 626
                        $totalregimen = $importe + $impuesto_importe;
                        $impuestosRetenidos = number_format($totalregimen * $regimenFiscalRetenciones,2,'.','');
                        $totalImpuestosRetenidos += $impuestosRetenidos;
                        /* var_dump($impuestosRetenidos);
                        var_dump($totalImpuestosRetenidos);
                        var_dump($totalregimen); */
                        // $totalProducto = $importe-$impuesto_importe-$impuesto_importe_ieps;
                        // $base = $importe+$impuesto_importe;
                        $base = $importe;
                        $cuerpo  .='
                        <concepto>
                            <cantidad>'.$detalle->qty.'</cantidad>
                            <unidad>PIEZAS</unidad>
                            <claveUnidad>H87</claveUnidad>
                            <claveProdServ>'.$detalle->Products->clave_producto_id.'</claveProdServ>
                            <noIdentificacion>'.$detalle->Products->code.'</noIdentificacion>
                            <descripcion>'.$detalle->Products->name.', '.$detalle->Products->description.'</descripcion>
                            <valorUnitario>'.number_format(($totalProducto)/$detalle->qty,2,'.','').'</valorUnitario>
                            <importe>'.($totalProducto).'</importe>
                            <objetoImp>'.($immex === false?'02':'01').'</objetoImp>';

                            $ieps = str_replace("##baseImpuesto##", number_format($totalProducto,2,'.',''),$ieps);
                            if($immex === false){
                                $cuerpo .= '<impuestos><traslados><traslado>
                                        <base>'.$base.'</base>
                                        <impuesto>002</impuesto>
                                        <tipofactor>Tasa</tipofactor>
                                        <tasa>0.160000</tasa>
                                        <importe>'.$impuesto_importe.'</importe>
                                    </traslado>
                                    '.$ieps.'
                                    </traslados>
                                    </impuestos>';
                            }
                            $cuerpo = $cuerpo . '</concepto>';
                    }
                }
                $productList = array();
                if ($invoiceLaminateDetail) {
                    foreach ($invoiceLaminateDetail as $key => $detalle) {
                        if (isset($productList[$detalle->Products->id])) {
                            $productList[$detalle->Products->id]->qty += $detalle->qty;
                        } else {
                            $productList[$detalle->Products->id] = $detalle;
                        }
                    }
                    foreach ($productList as $key => $detalle) {
                        //print_r($detalle);
                        $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                        $cuerpo =  $cuerpo.'
                                        <concepto>
                                            <cantidad>'.$detalle->qty.'</cantidad>
                                            <unidad>PIEZAS</unidad>
                                            <claveUnidad>H87</claveUnidad>
                                            <claveProdServ>'.$detalle->Products->clave_producto_id.'</claveProdServ>
                                            <noIdentificacion>'.$detalle->Products->code.'</noIdentificacion>
                                            <descripcion>'.$detalle->Products->name.'</descripcion>
                                            <valorUnitario>'.number_format($detalle->unit_price,2,'.','').'</valorUnitario>
                                            <importe>'.$importe.'</importe>';
                        $subtotal += $importe; 
                        $impuesto_importe = number_format($importe * 0.16,2,'.','');
                        $totalImpuestosTrasladados += $impuesto_importe;
                        $cuerpo .= '<impuestos><traslados><traslado>
                                        <base>'.$importe.'</base>
                                        <impuesto>002</impuesto>
                                        <tipofactor>Tasa</tipofactor>
                                        <tasa>0.160000</tasa>
                                        <importe>'.$impuesto_importe.'</importe>
                                    </traslado></traslados></impuestos>';
                        $cuerpo = $cuerpo . '</concepto>';
                    }
                }
                $cuerpo = $cuerpo.'</conceptos>';
                $cuerpoIva = '';
                if ($totalImpuestosTrasladadosIva > 0) {
                    $cuerpoIva .= '
                                        <traslado>
                                            <base>'.number_format($subtotal,2,'.','').'</base>
                                            <impuesto>002</impuesto>
                                            <tipofactor>Tasa</tipofactor>
                                            <tasa>0.160000</tasa>
                                            <importe>'.$totalImpuestosTrasladadosIva.'</importe>
                                        </traslado>
                                   ';
                }
                $cuerpoIeps = '';
                if ($totalImpuestosTrasladadosIeps) {
                    foreach ($totalImpuestosTrasladadosIeps as $key => $rowIeps) {
                    $cuerpoIeps .= '
                                        <traslado>
                                            <impuesto>003</impuesto>
                                            <tipofactor>Tasa</tipofactor>
                                            <tasa>'.$key.'</tasa>
                                            <importe>'.$rowIeps.'</importe>
                                        </traslado>
                                    ';
                    }
                }
                //Regimen fiscal codigo 626
                $cuerpoRetenidos = '';
                    $retencionesRegimen = '';
                    $impuestosretenidos = '';
                /* if ($totalImpuestosRetenidos > 0 && $banderaRegimenFiscal == true) {
                    $cuerpoRetenidos .= '
                                        <retencion>
                                            <impuesto>001</impuesto>
                                            <importe>'.$totalImpuestosRetenidos.'</importe>
                                        </retencion>
                                ';
                    $retencionesRegimen = '<retenciones>
                    '.$cuerpoRetenidos.'
                    </retenciones>';
                    $impuestosretenidos = '<totalImpuestosRetenidos>'.$totalImpuestosRetenidos.'</totalImpuestosRetenidos>';
                } */
                if ($totalImpuestosTrasladados) {
                    $cuerpo .= '<impuestos>
                                    <traslados>
                                     '.$cuerpoIva.'
                                     '.$cuerpoIeps.'
                                    </traslados>
                                    '.$retencionesRegimen.'
                                    <totalImpuestosTrasladados>'.$totalImpuestosTrasladados.'</totalImpuestosTrasladados>
                                    '.$impuestosretenidos.'
                                    </impuestos>';
                }
                if($immex){
                    $cuerpo .= '<complemento>
                        <leyendasFiscales>
                            <textoLeyenda>LA TRANSFERENCIA SE EFECTUA DE CONFORMIDAD CON EL ART.29 FRACCION I DE LA LEY DEL IVA, ARTICULO112 1ER PARRAFO DE LA LEY ADUANERA Y LAS REGLAS 4.3.21, 5.2.5 FRACCION II Y 5.2.6 DE LAS RGCE VIGENTES, ENTREGA A JOHNSON CONTROLS BE OPERATIONS MEXICO S DE RL DE CV IMMEX 397 – 2015 RFC JCB100702TQ1</textoLeyenda>
                            <norma>5.2.4</norma>
                            <disposicionFiscal>IMX</disposicionFiscal>
                        </leyendasFiscales>
                    </complemento>';
                }
                $cuerpo = $cuerpo.'</factura></emision>';
                $total = $subtotal + $totalImpuestosTrasladados;
                /* var_dump($total);
                die(); */
                if($regimenFiscal != null){
                    if ($regimenFiscal == 626) {
                        //$total -= $totalImpuestosRetenidos;
                        $banderaRegimenFiscal = true; 
                    }
                }
                $subtotal =  $subtotal;
                $clienteBatuta = 80;
                switch ($invoice->ShoppingCart->branchoffice) {
                    case 9:
                        $clienteBatuta = 80;
                        break;
                    case 13:
                        $clienteBatuta = 82;
                        break;
                    case 12:
                        $clienteBatuta = 81;
                        break;
                    case 14:
                        $clienteBatuta = 82;
                        break;
                    default:
                        $clienteBatuta = 80;
                        break;
                }
                
                $cabezera = '<emision>
                    <cliente>'.$clienteBatuta.'</cliente>
                    <factura>
                        <data>
                            <serie>'.$invoice->serie.'</serie>
                            <folio>'.$invoice->folio_fiscal.'</folio>
                            <fecha>'.$fecha.'</fecha>
                            <formaDePago>'.$invoice->SatPaymentForms->clave.'</formaDePago>
                            <subtotal>'.number_format($subtotal,2,'.','').'</subtotal>
                            <total>'.number_format($total,2,'.','').'</total>
                            <metodoDePago>'.$invoice->metodo_pago.'</metodoDePago>
                            <tipoDeComprobante>'.$invoice->tipo_comprobante.'</tipoDeComprobante>
                            <exportacion>01</exportacion>
                            <lugarDeExpedicion>'.str_pad($invoice->lugar_expedicion, 5, "0", STR_PAD_LEFT).'</lugarDeExpedicion>
                            <moneda>MXN</moneda>'.
                            ($invoiceSustituye != null
                            ?'<tiporelacion>04</tiporelacion>
                            <uuid_relacion>'.$invoiceSustituye->uuid.'</uuid_relacion>'
                            :'')
                            .'
                        </data>
                        <receptor>
                                <rfc>'.$rfc.'</rfc>
                                <nombre>'.$razon_social.'</nombre>
                                <domicilioFiscalReceptor>'.str_pad($domicilioFiscal, 5, "0", STR_PAD_LEFT).'</domicilioFiscalReceptor>
                                <usoCFDI>'.$invoice->SatUsoCFDI->clave.'</usoCFDI>
                                <regimenFiscalReceptor>'.$invoice->regimen_fiscal.'</regimenFiscalReceptor>
                        </receptor>
                        <conceptos>';
                $emision = $cabezera . $cuerpo;
                /* var_dump($emision);
                 echo "<pre>";
                 print_r($emision);
                 exit(); */
                $serviceURL = $this->batuta_url.'/api/get_emision';
                $curl = curl_init($serviceURL);
                if(!isset($invoice->cliente_id)){
                    $invoice->cliente_id = 0;
                }
                $curlPostData = [
                    'data' => $emision,
                    'filename' => $invoice->folio_fiscal . '_rebasa-' . $invoice->cliente_id . '.xml',
                    'flavour' => 'Facturacion4',
                    'scheduled' => ''
                ];
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostData);
                $curlResponse = curl_exec($curl);
                
                $data = json_decode($curlResponse);
                curl_close($curl);
                if ($data->result) {
                    $invoice->status_timbrado = 4;
                    $invoice->id_request = $data->uuid;
                    $invoice->message = $data->message;
                    $invoice->update();
                    
                    $this->content['result'] = 'success';
                } else {
                    $invoice->message = $data->message;
                    $invoice->update();
                    
                }
                $tx->commit();
                $this->content['emision'] = $emision;
                $this->content['data'] = $data;
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
               
            }   
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
        $this->response->send();
    }



    public function cancelar ($id)
    {   
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();
            try {
                $invoice = Invoices::findFirst($id);
                $payments =  InvoicePayments::find("invoice_id = $id and status_timbrado = 1");
                $this->content['errors'] = "Factura con pagos relacionados \n";
                foreach($payments as $payment){
                    $relacionados = InvoicePayments::find("folio = $payment->folio and serie = '$payment->serie' and id != $payment->id and status_timbrado = 1");
                    if(count($relacionados)>0){
                        $this->content['errors'] .= "Factura $payment->serie-$payment->folio.\n";
                        $this->content['message'] = Message::error('No se puede cancelar una factura con pagos relacionados.');
                        $invoice = false;
                        $this->content['result'] = 'error';
                        $response = ['error' => 'Factura con pagos relacionados.'];
                    }
                }
                $clienteBatuta = 80;
                if ($invoice) {
                    /* if($invoice->status_timbrado != 1 && $invoice->status_timbrado != 7){
                        $this->content['result'] == 'success';
                        return;
                    } */
                    $invoice->motivo_cancelacion = $request['motivo_cancelacion'];
                    $invoice->folio_sustituye = $request['folio_sustituye']!=""?$request['folio_sustituye']:null;
                    switch ($invoice->ShoppingCart->branchoffice) {
                        case 9:
                            $clienteBatuta = 80;
                            break;
                        case 13:
                            $clienteBatuta = 82;
                            break;
                        case 12:
                            $clienteBatuta = 81;
                            break;
                        case 14:
                            $clienteBatuta = 82;
                            break;
                        default:
                            $clienteBatuta = 80;
                            break;
                    }
                    $folio_sustituye = '';
                    if($invoice->folio_sustituye != null && $invoice->motivo_cancelacion == '01'){
                        $invoiceSustituye = Invoices::findFirst(
                            "folio_fiscal = $invoice->folio_sustituye 
                            and serie = '$invoice->serie' 
                            and status_timbrado = 1 
                            and tax_company_id = $invoice->tax_company_id
                            and id != $invoice->id");
                        if(!$invoiceSustituye){
                            $this->content['errors'] = "Verifiacar que el folio corresponda a una factura del mismo cliente y no este cancelada.";
                            $this->content['message'] = Message::error('Folio que sustituye no es valido.');
                            $this->content['result'] = 'error';
                            $response = ['error' => 'Factura con pagos relacionados.'];
                            $this->response->setJsonContent($this->content);
                            $this->response->send();
                            return;
                        }

                        $folio_sustituye = "|$invoiceSustituye->uuid";
                    }
                    $CancelTimbrado = "<cancelarTimbrado>
                        <client>".$clienteBatuta."</client>
                        <uuid>{$invoice->uuid}|{$invoice->motivo_cancelacion}{$folio_sustituye}</uuid>
                        <type>cancelarTimbrado</type>
                        </cancelarTimbrado>";

                    $service_url = $this->batuta_url.'/api/get_emision';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'data' => $CancelTimbrado,
                        'filename' => 'ctimbrado_' . $invoice->uuid . '.xml',
                        'flavour' => 'CancelarFacturaNew',
                        'scheduled' => '',
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response !== false) {
                        $response = json_decode($curl_response);
                        $invoice->setTransaction($tx);
                        if ($invoice) {
                            if ($response->result) {
                                $invoice->status_timbrado = 3;
                                $invoice->id_cancelacion = $response->uuid;
                                $invoice->message_cancelacion = $response->message;
                                $invoice->fecha_cancelacion_envio = date('Y-m-d H:i:s');
                                $invoice->update();
                                $this->content['result'] = 'success';
                            } else {
                                $invoice->message_cancelacion = $response->message;
                                $invoice->update();
                            }
                        }
                    }

                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
            $this->content['response'] = $response;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function revisarTimbrado ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $invoice = Invoices::findFirst($id);
                if ($invoice && $invoice->status_timbrado == 4) {
                    $invoice->setTransaction($tx);
                    $service_url = $this->batuta_url.'/api/info_factura';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $invoice->id_request,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->status == 'done') {
                            $service_url = $this->batuta_url.'/api/get_uuid';
                            $curl = curl_init($service_url);
                            $curl_post_data = array(
                                'uuid' => $invoice->id_request,
                            );
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                            $curl_response2 = curl_exec($curl);
                            if ($curl_response2 === false) {
                                $info = curl_getinfo($curl);
                                curl_close($curl);
                                die('error occured during curl exec. Additioanl info: ' . var_export($info));
                            } else {
                                // insert pago  PUE
                                $invoiceInBulkDetails = InvoiceInBulkDetails::find("invoice_id = $invoice->id");
                                $total = 0;
                                foreach ($invoiceInBulkDetails as $detail) {
                                    $total += $detail->qty * $detail->unit_price * 1.16;
                                }
                                if($invoice->metodo_pago ==  'PUE' && $invoice->status_payment == 0){
                                    $payment = new Payments();
                                    $validUser = Auth::getUserData($this->config);
                                    $payment->created_by = $validUser->id;
                                    $payment->remision_id = $invoice->id;
                                    $payment->amount = $total;
                                    $payment->payment_date = $invoice->fecha_factura;
                                    $payment->reference = 'Pago de contado';
                                    $payment->method = $invoice->forma_pago;
                                    if ($payment->create()) {
                                        $invoice->status_payment = 2;
                                    }
                                }
                                // insert pago  PUE
                                $uuid_factura = $curl_response2;
                                $invoice->status_timbrado = 1;
                                //$invoice->fecha_factura = !empty($request['fecha_factura']) ? $request['fecha_factura'] : NULL;
                                $invoice->uuid = $uuid_factura;
                                $invoice->message = $response->message;
                                if ($invoice->update()) {
                                    $this->content['result'] = 'success';
                                } else {
                                    $this->content['error'] = Helpers::getErrors($invoice);
                                    $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                    $tx->rollback();
                                }
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $invoice->message = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error')  {
                            $invoice->status_timbrado = 6;
                            $invoice->message = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                } else if ($invoice && $invoice->status_timbrado == 3) {
                    $invoice->setTransaction($tx);
                    $service_url = $this->batuta_url.'/api/info_general';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $invoice->id_cancelacion,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->status == 'done') {
                            $invoice->status_timbrado = 5;
                            $invoice->id_cancelacion_asc = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error')  {
                            $invoice->status_timbrado = 7;
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                } else if ($invoice && $invoice->status_timbrado == 5) {
                    $service_url = $this->batuta_url.'/api/get_status_cancelacion';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $invoice->id_cancelacion_asc,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->ret_status == 200) {
                            if ($response->status) {
                                $invoice->status_timbrado = 2;
                                $invoice->message_cancelacion = $response->message;
                                $invoice->acusesat_cancelacion = $response->acuseSat;
                                $invoice->fecha_cancelacion_recibido = date('Y-m-d H:i:s');
                                if ($invoice->update()) {
                                    $this->content['result'] = 'success';
                                } else {
                                    $this->content['error'] = Helpers::getErrors($invoice);
                                    $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                    $tx->rollback();
                                }
                            } else {
                                $status = explode('|', str_replace(' ', '', $response->message))[0];
                                if ($status == 211) {
                                    $invoice->message_cancelacion = $response->message;
                                    if ($invoice->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($invoice);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                } else {
                                    $invoice->status_timbrado = 7;
                                    $invoice->message_cancelacion = $response->message;
                                    $invoice->acusesat_cancelacion = $response->acuseSat;
                                    if ($invoice->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($invoice);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }
                            }
                        } else if ($response->ret_status == 211) {
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }else if ($response->ret_status == 101){
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }else {
                            $invoice->status_timbrado = 7;
                            $invoice->message_cancelacion = $response->message;
                            $invoice->acusesat_cancelacion = $response->acuseSat;
                            if ($invoice->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($invoice);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
            }
            $this->content['response'] = $response;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function sendEmailInvoice ()
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $invoice = Invoices::findFirst($request['id']);
            $cliente = Customers::findFirst($invoice->CustomerBranchOffices->customer_id);
            $shoppingCart =  ShoppingCart::findFirst($invoice->shopping_cart_id);
            try {
                $service_url = $this->batuta_url.'/api/generate_email';
                $curl = curl_init($service_url);
                $curl_post_data = array(
                      'to' => $request['email'],
                      'subject' => 'Facturacion pedido #'.$shoppingCart->id,
                      'type' => 'tfFacturacion',
                      'xml' => $this->batuta_url.'/public/files/cfdi_done/'.$invoice->id_request.'.xml',
                      'pdf' => $this->batuta_url.'/api/get_pdf/'.$invoice->id_request.'/0', 
                      'cliente' => $cliente->name,
                      'pedido' => $shoppingCart->id
                );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                $curl_response = curl_exec($curl);
                if ($curl_response === false) {
                    $this->content['message'] = Message::error('No se pudo enviar el email.');
                } else {
                    $response = json_decode($curl_response);
                    $cliente = Customers::findFirst($invoice->CustomerBranchOffices->customer_id);
                    $this->content['result'] = true;
                    $this->content['email_cliente'] = $cliente->email;
                    $this->content['id_request_email'] = $response->uuid;
                    $this->content['message_response'] = $response->message;
                    $this->content['message'] = Message::success('Se ha enviado el email correctamente.');
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
         $this->response->setJsonContent($this->content);
         $this->response->send();
    }

    public function sendEmailsPagos ()
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            try {
                foreach ($request['ids'] as $key => $id) {
                    $invoice = InvoicePayments::findFirst($id);
                        $service_url = $this->batuta_url.'/api/generate_email';
                        $curl = curl_init($service_url);
                        $curl_post_data = array(
                              'to' => $request['email'],
                              'subject' => 'Facturacion',
                              'type' => 'facturacion',
                              'id_request' => $invoice->id_request,
                              'cliente' => 300,
                        );
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                        $curl_response = curl_exec($curl);
                        if ($curl_response === false) {
                            $this->content['message'] = Message::error('No se pudo enviar el email.');
                            $tx->rollback();
                        }
                }
                $this->content['result'] = true;
                $this->content['message'] = Message::success('Se han enviado los emails correctamente.');
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function sendEmailsReport ()
    {
        $request = $this->request->getPost();
        $msg = '';
        //----------------------------------//
        $actions = Actions::findFirst(1);

        if ($actions->host && $actions->port && $actions->username && $actions->password) {
            if (is_null($request['email']) && !filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                $msg .= 'No se ha enviado el correo debido a que el cliente no tiene correo registrado.';
            } else {
                $emailHashes = [];
                $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
                while (strstr($billingEmailHash, '/') || strstr($billingEmailHash, '.')) {
                    $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
                }
                array_push($emailHashes, array('email' => 'facturacion@eturelab.com', 'hash' => $billingEmailHash));
                if (!is_null($request['email']) && filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                    $emailHash = password_hash($request['email'], PASSWORD_BCRYPT);
                    while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
                        $emailHash = password_hash($request['email'], PASSWORD_BCRYPT);
                    }
                    array_push($emailHashes, array('email' => $request['email'], 'hash' => $emailHash));
                }

                foreach ($emailHashes as $emailHash) {
                    $hash = $emailHash['hash'];
                    $htmlBody = $this->getMail($request['email'],$request['customer'], $hash);
                    $mailer = new Mailer();
                    $mailer->htmlBody = $htmlBody;
                    $mailer->attachedFile = $this->saveCartPdf($request['customer'],$request['status'],$request['saleDatev1'],$request['saleDatev2']);
                    $mailer->host = $actions->host;
                    //$mailer->encryption = $actions->encryption;
                    $mailer->port = $actions->port;
                    $mailer->username = $actions->username;
                    $mailer->password = $actions->password;
                    $mailer->subject = "Reporte de Pagos";
                    $mailer->from = $actions->username;
                    $mailer->to = $emailHash['email'];
                    $result_message = null;
                    try {
                        $result_message = $mailer->sendEmail();
                    } catch (Throwable $e) {
                        $result_message = (object) array(
                            'status' => false,
                            'message' => $e->getMessage()
                        );
                    }
                    $msg .= $result_message->message;
                }
            }
        } else {
            $msg .= 'No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
        }
        $this->content['result'] = true;
        $this->content['message'] = Message::success($msg);
        $this->response->setJsonContent($this->content);
    }

    public function getMail ($mail,$customerid, $hash) {
        $customer = Customers::findFirst($customerid);
        $emailHashes = [];

        $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
        while (strstr($billingEmailHash, '/') || strstr($billingEmailHash, '.')) {
            $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
        }
        array_push($emailHashes, array('email' => 'facturacion@eturelab.com', 'hash' => $billingEmailHash));
        if (!is_null($mail) && filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $emailHash = password_hash($mail, PASSWORD_BCRYPT);
            while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
                $emailHash = password_hash($mail, PASSWORD_BCRYPT);
            }
            array_push($emailHashes, array('email' => $mail, 'hash' => $emailHash));
        }

        $mail = '
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
                                <img id="logo" src="http://alpez.beta.wasp.mx/img/logo.png" alt="Alpez">
                            </div>
                            <p>
                                Estimado cliente <strong>'.$customer->tradename.'</strong>.
                                <br>
                                <br>
                                Adjunto encontrará su estado de cuenta.
                                <br>
                                <br>
                                Muchas gracias!!
                            </p>
                        </body>
                    </html>
                    ';
        return $mail;
    }

    public function saveCartPdf ($customer,$status,$date1,$date2)
    {
        $type = 2;
        if (is_numeric($customer)) {
                $pdf = $this->getPdfFromPaymentsDetails($type,$customer,$status,$date1,$date2);

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/orders/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Reporte_Pagos.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
        }
        return null;
    }

    public function getPdfFromPayments ($customer,$status,$date1,$date2)
    {
        $pdf = $this->createPdfFromPayments($customer,$status,$date1,$date2);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Cobranza.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="ReporteDeVentas.pdf"');
            return $response;
        }
    }

    public function createPdfFromPayments ($customer,$status,$date1,$date2)
    {
        $y = date('Y');
        $where = "";
        // print_r($status);
   // var_dump($status);
   // exit();

        

if ($status != 99) {
            $f = date('Y-m-d');
            $where .= "AND (";
            // foreach ($status as $ts) {
                if ($status == 1) {
                    $where .= "  (i.status_payment = 2)";
                } else if ($status == 2) {
                    $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 0)";
                } else if ($status == 3) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 1)";
                } else if ($status == 4) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 1)";
                } else if ($status == 5) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 0)";
                } else if ($status == 6) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                } else if ($status == 7) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                }
                /*if (count($status) - 1 > $contador) {
                    $where .= ' OR ';
                }
                $contador++;*/
            // }
            $where .= ")";
        } else {
            $where .= "";
        }
        /*if ($status == 99) {
            $where = "WHERE (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) ";
        } else {
            $where = " WHERE i.status = 'ENVIADO' AND i.status_payment in ($status) ";
        }*/
        if ($customer == 'TODOS') {} else if($customer == ''){}else {$where .= " AND cbo.customer_id = $customer";}
        if ($date1 === 'null') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."'";
        $sql = "SELECT i.id, i.status_payment,i.serie||'-'||i.folio_fiscal as factura,
                (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'DD/MM/YYYY')  as fecha_vencimiento,
                (select COALESCE((SELECT sum(sid.unit_price * wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotal,
                (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                (select COALESCE((SELECT sum(sil.unit_price * sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotal,
                i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office, bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'DD/MM/YYYYY HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.status_timbrado, i.metodo_pago
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                {$where}
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days
                ORDER BY id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $detalle){
            $sum = ($detalle['baletotal'] * 1.16) + ($detalle['bulktotal'] * 1.16) + ($detalle['lamitotal'] * 1.16);
            $resta = $sum - $detalle['abonado'];
            $data[$key]['baletotal'] = number_format($detalle['baletotal'], 2, '.', '');
            $data[$key]['bulktotal'] = number_format($detalle['bulktotal'], 2, '.', '');
            $data[$key]['lamitotal'] = number_format($detalle['lamitotal'], 2, '.', '');
            $data[$key]['cantidad_total'] = number_format($sum, 2, '.', '');
            $data[$key]['cantidad_restante'] = number_format($resta, 2, '.', '');
        }

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDFPayments();
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(0.1);
        $pdf->encabezado();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(190, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA INICIO: '.$fechaIni);

        $pdf->SetXY(235, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA FIN: '.$fechaFin);

        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);


        $pdf->SetXY(5, 40);
        $pdf->SetFont('', '', 7);

        $pdf->SetWidths(array(20,20,20,24,90,30,30,28));
        $pdf->SetAligns(array('C','C','C','C','L','R','R','R'));
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.4);

        $i = 1;
        $totalesAbonado = 0;
        $totalesRestante = 0;
        $totalesTotales = 0;
        foreach ($data as $row) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                $pdf->AddPage('L', 'Letter');
                $pdf->encabezado();
                $pdf->SetXY(0, 50);
                $pdf->SetFont('', '', 7);
            }
            $pdf->SetX(5);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetLineWidth(.3);
            $total = $row['baletotal'] + $row['bulktotal'] + $row['lamitotal'];
            $restante = $total - $row['abonado'];
            $status = '';
            if ($row['status_payment'] == 0) {
                $status = 'PENDIENTE';
            }elseif($row['status_payment'] == 1){
                $status = 'ABONADO';
            }elseif($row['status_payment'] == 2){
                $status = 'PAGADO';
            }
            $pdf->Row(array( $status, $row['sale_date'],$row['factura'],$row['fecha_vencimiento'],utf8_decode($row['customer']),'$ '.number_format(floatval($row['cantidad_total']), 2, '.', ','),'$ '.number_format(floatval($row['abonado']), 2, '.', ','),'$ '.number_format(floatval($row['cantidad_restante']), 2, '.', ',')));
            $i++;
            $totalesAbonado += $row['cantidad_restante'];
            $totalesRestante += $row['abonado'];
            $totalesTotales += $row['cantidad_total'];
        }
        $pdf->SetXY(89, $pdf->getY());
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(90, 5, 'TOTAL GENERAL: ',1,'','R');
        $pdf->SetXY(179, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(30, 5, '$'.number_format(floatval($totalesTotales), 2, '.', ','),1,'','R');
        $pdf->SetXY(209, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(30, 5, '$'.number_format(floatval($totalesRestante), 2, '.', ','),1,'','R');
        $pdf->SetXY(239, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(28, 5, '$'.number_format(floatval($totalesAbonado), 2, '.', ','),1,'','R');

        $pdf->SetTitle(utf8_decode('Reporte de Pagos'));
        $pdf->Output('I', 'reporte_pagos.pdf', true);

        return $pdf;
    }
    
    public function createPdfFromRemission ($customer,$status,$date1,$date2,$statusT,$remision, $factura)
    {
        $validUser = Auth::getUserInfo($this->config);        
        $y = date('Y');
        $where = 'WHERE i.id > 0 ';
        $where .= $validUser->role_id == 1 ? "" : "AND (bs.branch_office_id = $validUser->branch_office_id or ibs.branch_office_id = $validUser->branch_office_id or ls.branch_office_id = $validUser->branch_office_id) ";
        if ($status == 'TODOS') {} else if($status == ''){}else {$where .= " AND i.status = '$status' ";}
        if ($statusT == 'TODOS') {} else if($statusT == ''){}else {$where .= " AND i.status_timbrado = {$statusT} ";}
        if ($customer == 'TODOS') {} else if($customer == ''){}else if($customer == 'null'){}else {$where .= " AND cbo.customer_id = $customer";}
        if ($date1 === '' || $date1 == 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === '' || $date2 == 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        if ($remision != null && $remision != 'null' && $remision != '') {
            $where .= " AND i.id = $remision ";
        }
        if ($factura != null && $factura != 'null' && $factura != '') {
            $where .= " AND i.folio_fiscal = $factura ";
        }
        $where .= " AND i.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."' ";
        $sql = "SELECT i.id,a.nickname as seller,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office,
                       bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id,
                       d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'YYYY/MM/DD HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.id_request, i.status_timbrado,
                       coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura,
                       (select COALESCE((SELECT sum(wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotal,
                       (select COALESCE((SELECT sum(sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                       (select COALESCE((SELECT sum(sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotal,
                       (select COALESCE((SELECT sum(sid.unit_price * wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotalm,
                       (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotalm,
                       (select COALESCE((SELECT sum(sil.unit_price * sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotalm
               
                FROM sls_invoices AS i
                LEFT JOIN sls_shopping_cart AS ssc ON ssc.id = i.shopping_cart_id
                LEFT JOIN sys_users AS a ON a.id = ssc.user_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                 {$where} 
                ORDER BY i.id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $invoice) {
            $sum = ($invoice['baletotalm'] * 1.16) + ($invoice['bulktotalm'] * 1.16) + ($invoice['lamitotalm'] * 1.16);
            $data[$key]['totalacumulado'] = number_format($sum, 2, '.', '');
        }

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDFPayments();
        // Reporte de remisiones general
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(0.1);
        $pdf->encabezadov3();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(190, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA INICIO: '.$fechaIni);

        $pdf->SetXY(235, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        $pdf->Cell(0, 0, 'FECHA FIN: '.$fechaFin);

        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);


        $pdf->SetXY(5, 40);
        $pdf->SetFont('', '', 7);

        $pdf->SetWidths(array(15,20,20,35,50,80,25,25));
        $pdf->SetAligns(array('C','C','C','C','L','L','R','R'));
        $pdf->SetDrawColor(0);
        $pdf->SetLineWidth(0.4);

        $i = 1;
        $totalesPaca = 0;
        $totalesAbierta = 0;
        $totalesLaminado = 0;
        $totalesDinero = 0;
        foreach ($data as $row) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                $pdf->AddPage('L', 'Letter');
                $pdf->encabezadov3();
                $pdf->SetXY(0, 40);
                $pdf->SetFont('', '', 7);
            }
            $pdf->SetX(5);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            $bulktotalkg = ($row['bulktotal'] > 0) ? number_format(floatval($row['bulktotal']), 2, '.', ',') : '-';

            $status = $this->getStatusTimbrado($row['status_timbrado']);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Row(array($row['id'],$status, $row['sale_date'],$row['factura'],utf8_decode($row['seller']),utf8_decode($row['customer']),$bulktotalkg ,'$ '.number_format(floatval($row['totalacumulado']), 2, '.', ',')));
            $i++;
            $totalesAbierta += $row['bulktotal'];
            $totalesDinero += $row['totalacumulado'];
        }
        $pdf->SetLineWidth(0.1);
        $pdf->SetXY(124, $pdf->getY());
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(100, 5, 'TOTAL GENERAL: ',0,'','R');
        $pdf->SetXY(225, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(25, 5, number_format(floatval($totalesAbierta), 2, '.', ','),1,'','R');
        $pdf->SetXY(250, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(25, 5, '$'.number_format(floatval($totalesDinero), 2, '.', ','),1,'','R');


        $pdf->SetTitle(utf8_decode('Reporte de Ventas'));
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', 'reporte_ventas.pdf', true);

        return $pdf;
    }

    public function getStatusTimbrado ($status) {
        $st = '';
        if ($status == 0) {
            $st = 'NUEVO';
        }elseif($status == 1){
            $st = 'TIMBRADO';
        }elseif($status == 2){
            $st = 'CANCELADO';
        }elseif($status == 4){
            $st = 'TIMBRANDO';
        }elseif($status == 5){
            $st = 'CANCELANDO';
        }elseif($status == 6){
            $st = 'ERROR';
        }elseif($status == 7){
            $st = 'ERROR DE CANCELACION';
        }
        return $st;
    }

    public function getPdfFromPaymentsDetails ($type,$customer,$status,$date1,$date2,$branchoffice)
    {
        // print_r($branchoffice);
        // exit();
        $y = date('Y');
        $data = $this->getClients(0,$customer,$status,$date1,$date2,$branchoffice);
        $dataClients = $this->getClients(1,$customer,$status,$date1,$date2,$branchoffice);
        /* echo "<pre>";
        print_r($dataClients);
        exit(); */
        $fechaImpresion = date("d/m/Y");
        $dateIni = ($date1 === 'null' || $date1 == '') ? date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000"))) : date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        $dateFin = ($date2 === 'null' || $date2 == '') ? date("$y-12-31 00:00:00.000000") : date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));

        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDFPayments();
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'Letter');
        $pdf->SetLineWidth(0.1);
        if($branchoffice==9){
                        $pdf->encabezadov2();
                    } elseif($branchoffice==12){
                        $pdf->encabezadov2_1();
                    }elseif($branchoffice==13) {
                    $pdf->encabezadov2_2();
                    }elseif($branchoffice==14){
                    $pdf->encabezadov2_3();
                    }else{
                    }
        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(140, 22);
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(0, 0, 'FECHA INICIO: '.$fechaIni);

        $pdf->SetXY(177, 22);
        $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->Cell(0, 0, '  FECHA FIN: '.$fechaFin);

        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);

        $pdf->SetXY(5, 30);
        $pdf->SetFont('', '', 7);

        $i = 1;
        $totalesAbonado = 0;
        $totalesRestante = 0;
        $totalesTotales = 0;
        $a = 0; $b =0; $c=0;
        $fecha = date('Y-m-d');
        // print_r($fecha);
        // print("  ");
        foreach($dataClients as $dC){
            $data = $this->getClients(0,$dC['id'],$status,$date1,$date2,$branchoffice);
            $ta = 0;
            $vencido = 0;
            // echo("<pre>");
                // print_r($row);
            foreach($data as $row){
                // echo("<pre>");
                // print_r($row);
                $ta += $row['cantidad_restante'];
                if(strtotime($row['expired_date'])<strtotime($fecha)){
                $vencido += $row['cantidad_restante'];
                }

            }
            //exit();
            if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                $pdf->AddPage('P', 'Letter');
                $pdf->encabezadov2();
                $pdf->SetXY(0, 30);
                $pdf->SetFont('', '', 7);
            }
            $pdf->setFillColor(30,136,229);
            $pdf->SetXY(5, $pdf->getY()+2);

            $pdf->SetWidths(array(160));
            $pdf->SetAligns(array('C'));
            $pdf->SetTextColor(255,255,255);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFills(array(1));
            $pdf->SetBorders(array(1));
            $pdf->Rowv2(array(utf8_decode($dC['name'])));
            //
            $pdf->SetXY(157, $pdf->getY() - 5);
            $pdf->SetWidths(array(27));
            $pdf->SetAligns(array('R'));
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            if($vencido > 0){
                $pdf->SetTextColor(255,255,255);
                $pdf->SetFillColor(255,0,0);
            }else{
                $pdf->SetFillColor(255,255,255);
            }
            $pdf->SetBorders(array(1));
            $pdf->Rowv2(array('Vencido: $ '.number_format($vencido,2)));
            //

            $pdf->SetXY(184, $pdf->getY() - 5);
            $pdf->SetWidths(array(27));
            $pdf->SetAligns(array('R'));
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            if($ta > 0){
                $pdf->SetTextColor(255,255,255);
                $pdf->SetFillColor(255,0,0);
            }else{
                $pdf->SetFillColor(0,255,0);
            }
            $pdf->SetBorders(array(1));
            $pdf->Rowv2(array('$ '.number_format($ta,2)));

            foreach ($data as $row) {
                if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                    $pdf->AddPage('P', 'Letter');
                    if($branchoffice==9){
                        $pdf->encabezadov2();
                    } elseif($branchoffice==12){
                        $pdf->encabezadov2_1();
                    }elseif($branchoffice==13) {
                    $pdf->encabezadov2_2();
                    }elseif($branchoffice==14){
                    $pdf->encabezadov2_3();
                    }else{
                    }
                    
                    $pdf->SetXY(0, 30);
                    $pdf->SetFont('', '', 7);
                }

                $pdf->SetXY(5,$pdf->getY() + 3);
                $pdf->SetWidths(array(25,25,75,27,27));
                $pdf->SetAligns(array('C','C','L','R','R'));
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetTextColor(0,0,0);
                // color de cabezera
                //$pdf->SetFillColor(135,180,223);
                if($vencido > 0 && (strtotime($row['expired_date'])<strtotime($fecha))){
                    $pdf->SetFillColor(251,202,197);
                }else{
                    $pdf->SetFillColor(135,180,223);
                }
                
                $pdf->Row(array(utf8_decode($row['fecha_factura']),utf8_decode($row['fecha_vencimiento']),'FACTURA: '.$row['folio_fiscal'],'$ '.$row['cantidad_total'],'$ '.$row['abonado']));

                $pdf->SetXY(184,$pdf->getY() - 5);
                $pdf->SetWidths(array(27));
                $pdf->SetAligns(array('R'));
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetTextColor(0,0,0);
                if($row['cantidad_restante'] > 0){
                    $pdf->SetFillColor(251,202,197);
                }else{
                    $pdf->SetFillColor(202,251,197);
                }
                $pdf->Row(array('$ '.$row['cantidad_restante']));

                $totalesAbonado += $row['cantidad_restante'];
                $totalesRestante += $row['abonado'];
                $totalesTotales += $row['cantidad_total'];
                $id = $row['id'];
                $datas = $this->getClients(2,$id,null,null,null,$branchoffice);

                if($datas){
                    $pdf->SetX(5);
                    $pdf->SetTextColor(255,255,255);
                    $i = 0;
                    $newAbono = 0;
                    foreach ($datas as $rowV2) {
                        if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                            $pdf->AddPage('P', 'Letter');
                            if($branchoffice==9){
                        $pdf->encabezadov2();
                    } elseif($branchoffice==12){
                        $pdf->encabezadov2_1();
                    }elseif($branchoffice==13) {
                    $pdf->encabezadov2_2();
                    }elseif($branchoffice==14){
                    $pdf->encabezadov2_3();
                    }else{
                    }
                            $pdf->SetXY(0, 30);
                            $pdf->SetFont('', '', 7);
                        }
                        $resta = $row['cantidad_total'] - $newAbono - $rowV2['amount'];
                        $pdf->SetX(5);
                        $pdf->SetWidths(array(25,100,27,27,27));
                        $pdf->SetAligns(array('C','L','R','R','R'));
                        $pdf->SetBorders(array(1,1,1,1,1,1,1));
                        $pdf->SetFills(array(0,0,0,0,0,0,0));
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetDrawColor(0, 0, 0);
                        $pdf->Rowv2(array($rowV2['payment_date'],'ABONO REF:  '.utf8_decode($rowV2['reference']),'','$ '.number_format(floatval($rowV2['amount']), 2, '.', ','),''));
//                        $pdf->Rowv2(array($rowV2['payment_date'],'ABONO REF:  '.utf8_decode($rowV2['reference']),'','$ '.number_format(floatval($rowV2['amount']), 2, '.', ','),'$ '.number_format(floatval($resta), 2, '.', ',')));
                        $newAbono += $rowV2['amount'];
                        $i++;
                        $c++;
                    }
                }
                $b++;
            }
            $a++;
        }

        if($type == 1){
            $pdf->SetTitle(utf8_decode('Reporte Auxiliar contable'));
            $pdf->Output('I', 'reporte_pagos.pdf', true);
            return $pdf;
        }else{
            return $pdf;

        }
    }

    public function getClients ($type,$customer,$status,$date1,$date2,$branchoffice) {
        $y = date('Y');
        $where = " WHERE ";
        // print_r($branchoffice);
        if(is_array($branchoffice)){
                $branchoffice = $branchoffice['value'];
        }else {
            $branchoffice = $branchoffice;
        }
            
        $data = [];
        if($type == 0) {
            $y = date('Y');
            if ($status != 99) {
                $f = date('Y-m-d');
                $where .= "";
                
                //foreach ($status as $ts) {
                    /* if ($status == 1) {
                        $where .= "  ( i.status_payment = 2)";
                    } else if ($status == 2) {
                        $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 0)";
                    } else if ($status == 3) {
                        $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 1)";
                    } else if ($status == 4) {
                        $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 1)";
                    } else if ($status == 5) {
                        $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 0)";
                    } else if ($status == 6) {
                        $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                    } else if ($status == 7) {
                        $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                    } */
                    /*if (count($status) - 1 > $contador) {
                        $where .= ' OR ';
                    }
                    $contador++;*/
                //}
                $where .= "";
            } else {
                $where .= "";
            }
            //$where = ($status == 99) ? "WHERE (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) " : " WHERE i.status_payment in ($status) ";
            if ($status == 99) {
                $where .= " (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) ";
               } else {
                $arrayStatus = explode(',',$status);
            $arrayStatus2 = [];
            
            foreach ($arrayStatus as $value) {
                # code...
                if (intval($value) == 1) {
                    array_push($arrayStatus2,intval(2));
                }else if (intval($value) == 2) {
                    array_push($arrayStatus2,intval(0));
                }else if (intval($value) == 3) {
                    array_push($arrayStatus2,intval(1));
                }
                
            }
            $spellStatus = implode(',',$arrayStatus2);
                //$where .= "  ( i.status_payment = 1) and ";
                $where .= "  ( i.status_payment in ($spellStatus))  ";
               }
            if ($customer == 'TODOS'){}else if($customer == ''){}else{$where .= " AND cbo.customer_id = $customer";}
            if ($branchoffice == 'TODAS') {} else if($branchoffice == 'null'){}else {$where .= " AND sc.branchoffice = $branchoffice";$where .= " AND sc.branchoffice = $branchoffice";}
            $dateIni = ($date1 === 'null' || $date1 == '') ? $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000"))) : $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
            $dateFin = ($date2 === 'null' || $date2 == '') ? $dateFin = date("$y-12-31 00:00:00.000000") : $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));

            $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."'";

            $sql = "SELECT i.id, i.status_payment,i.folio_fiscal,
                (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
                (select COALESCE((SELECT sum(round((sib.unit_price * sib.qty)::numeric,2) + round((sib.unit_price * sib.qty * .16)::numeric,2)) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                TO_CHAR((CAST(i.sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR 
                c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') AS expired_date,
                i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent,   
                ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, 
                i.customer_branch_office_id, cbo.name AS customer_branch_office, 
                cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, 
                i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file,
                to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, 
                i.status_timbrado, i.metodo_pago,to_char(i.fecha_factura,'DD/MM/YYYY') AS fecha_factura,
                TO_CHAR(cast(i.fecha_factura as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'DD/MM/YYYY')  as fecha_vencimiento
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
                {$where} and loan = 0 and i.status != 'CANCELADO' and i.status != 'REMISIONADO'
                GROUP BY i.id, a.nickname, ibs.branch_office_id, ibb.name, ibm.storage_id,c.price_list,d.name,c.credit_days,c.term,ibm.storage_id ,ibs.name,
                cbo.name,c.price_list,cbo.customer_id,c.name,ibm.date
                ORDER BY  expired_date;";
           // print_r($sql);
            //exit();
            $data = $this->db->query($sql)->fetchAll();
            foreach ($data as $key => $detalle){
                $sum = $detalle['bulktotal'];
                $resta = $sum - $detalle['abonado'];
                $data[$key]['cantidad_total'] =$sum;
                $data[$key]['cantidad_restante'] =$resta;
            }
            /* echo '<pre>';
        print_r($data);
        exit(); */
        
        }
        else if($type == 1) {
            $arrayStatus = explode(',',$status);
            $arrayStatus2 = [];
            
            
            /* if ($status == 1) {
                $where .= "  ( i.status_payment = 2)";
            } else if ($status == 2) {
                $where .= "  (  i.status_payment = 0)";
            } else if ($status == 3) {
                $where .= "  (  i.status_payment = 1)";
            } */
             if ($status == 99) {
                 $where .= " (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) ";
            }else{
                foreach ($arrayStatus as $value) {
                    # code...
                    if (intval($value) == 1) {
                        array_push($arrayStatus2,intval(2));
                    }else if (intval($value) == 2) {
                        array_push($arrayStatus2,intval(0));
                    }else if (intval($value) == 3) {
                        array_push($arrayStatus2,intval(1));
                    }
                    
                }
                $spellStatus = implode(',',$arrayStatus2);
                $where .= "  ( i.status_payment in ($spellStatus))  ";
            } //: "WHERE i.status_payment in ($status) ";
            if ($customer == 'TODOS'){}else if($customer == ''){}else{$where .= " AND cbo.customer_id = $customer";}
            if ($branchoffice == 'TODAS') {} else if($branchoffice == 'null'){}else {$where .= " AND sc.branchoffice = $branchoffice";$where .= " AND sc.branchoffice = $branchoffice";}
            $dateIni = ($date1 === 'null' || $date1 == '') ? $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000"))) : $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
            $dateFin = ($date2 === 'null' || $date2 == '') ? $dateFin = date("$y-12-31 00:00:00.000000") : $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
            $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."'";
            /* $sql = "SELECT distinct c.id, c.name FROM sls_invoices AS i 
            LEFT JOIN sys_users AS a ON a.id = i.agent_id 
            LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id  
            LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
            JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
             {$where} and loan = 0"; */
             $sql ="SELECT distinct c.id,c.name
             FROM sls_invoices AS i
             LEFT JOIN sys_users AS a ON a.id = i.agent_id
             LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
             LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
             LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
             LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
             LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
             LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
             LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
             LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
             LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
             LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
             LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
             LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
             JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
             $where  and loan = 0 
             GROUP BY c.id, c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days
             ";
            /* echo "<pre>";
        print_r($sql);
        exit(); */
            $data = $this->db->query($sql)->fetchAll();
        }
        else if($type == 2){
            $sql = "SELECT sp.id, sp.amount, sp.reference, sfp.descripcion, TO_CHAR(sp.payment_date, 'dd/mm/yyyy') AS payment_date
                    FROM sls_payments as sp
                    LEFT JOIN sat_formas_pagos as sfp ON sfp.id = sp.method
                    WHERE sp.remision_id = $customer
                    ORDER BY sp.id DESC;";
                     // print_r($sql);
            // exit();
            $data = $this->db->query($sql)->fetchAll();
        }
        return $data;
        
    }

    public function getCSVFromPayments ($customer,$status,$date1,$date2)
    {
        $y = date('Y');
        $where = "";

if ($status != 99) {
            $f = date('Y-m-d');
            $where .= "AND (";
            // foreach ($status as $ts) {
                if ($status == 1) {
                    $where .= "  (i.status_payment = 2)";
                } else if ($status == 2) {
                    $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 0)";
                } else if ($status == 3) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 1)";
                } else if ($status == 4) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 1)";
                } else if ($status == 5) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 0)";
                } else if ($status == 6) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                } else if ($status == 7) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                }
                /*if (count($status) - 1 > $contador) {
                    $where .= ' OR ';
                }
                $contador++;*/
            // }
            $where .= ")";
        } else {
            $where .= "";
        }
        /*if ($status == 99) {
            $where = "WHERE (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) ";
        } else {
            $where = " WHERE i.status = 'ENVIADO' AND i.status_payment = '$status' ";
        }*/
        if ($customer == 'TODOS') {} else if($customer == ''){}else {$where .= " AND cbo.customer_id = $customer";}
        if ($date1 === 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sql = "SELECT i.id, i.status_payment,coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura,
                (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
                (select COALESCE((SELECT sum(sid.unit_price * wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotal,
                (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                (select COALESCE((SELECT sum(sil.unit_price * sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotal,
                TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') AS expired_date,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office, bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'DD/MM/YYYYY HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.status_timbrado, i.metodo_pago,CONCAT(i.serie,'-',i.folio_fiscal) as factura,
                TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'DD/MM/YYYY') AS fecha_vencimiento
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                {$where}
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days
                ORDER BY id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $detalle){
            $sum = ($detalle['baletotal'] * 1.16) + ($detalle['bulktotal'] * 1.16) + ($detalle['lamitotal'] * 1.16);
            $resta = $sum - $detalle['abonado'];
            $data[$key]['baletotal'] = number_format($detalle['baletotal'], 2, '.', '');
            $data[$key]['bulktotal'] = number_format($detalle['bulktotal'], 2, '.', '');
            $data[$key]['lamitotal'] = number_format($detalle['lamitotal'], 2, '.', '');
            $data[$key]['cantidad_total'] = number_format($sum, 2, '.', '');
            $data[$key]['cantidad_restante'] = number_format($resta, 2, '.', '');
        }

        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $content = $this->content;
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array( 'ESTATUS', 'FECHA','VENCIMIENTO' ,'FACTURA', 'CLIENTE', 'TOTAL', 'ABONADO', 'RESTANTE'), ',');

        if (count($data) > 0) {
            $totales = 0;
            $abonados = 0;
            $restantes = 0;
            foreach ($data as $d) {
                $total = $d['cantidad_total'];
                $restante = $d['cantidad_restante'];
                $status = '';
                if ($d['status_payment'] == 0) {
                    $status = 'PENDIENTE';
                }elseif($d['status_payment'] == 1){
                    $status = 'ABONADO';
                }elseif($d['status_payment'] == 2){
                    $status = 'PAGADO';
                }

                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $status,
                        $d['sale_date'],
                        $d['fecha_vencimiento'],
                        $d['factura'],
                        $d['customer'],
                        number_format($d['cantidad_total'], 2, '.', ''),
                        number_format($d['abonado'], 2, '.', ''),
                        number_format($d['cantidad_restante'], 2, '.', '')
                    ], ',');
                }
                $totales += $d['cantidad_total'];
                $abonados += $d['abonado'];
                $restantes += $d['cantidad_restante'];
            }
            fputcsv($fp, array('', '', '', '', 'TOTALES GENERALES',  number_format($totales, 2, '.', ''),  number_format($abonados, 2, '.', ''),  number_format($restantes, 2, '.', '')), ',');
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Reporte_Pagos' . '.csv');
        $this->response->setContent($output);
        $this->response->send();

    }

    public function getCSVFromPaymentsDetails ($customer,$status,$date1,$date2,$branchoffice)
    {
        $y = date('Y');
        $data = $this->getClients(0,$customer,$status,$date1,$date2,$branchoffice);
        $dataClients = $this->getClients(1,$customer,$status,$date1,$date2,$branchoffice);

        $content = $this->content;
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('FECHA', 'CONCEPTO', 'DEBE', 'HABER', 'SALDO'), ',');

        if (count($dataClients) > 0) {
            foreach($dataClients as $dc){
                $ta = 0;
                foreach($data as $row){
                    $ta += $row['cantidad_restante'];
                }
                fputcsv($fp, [utf8_decode($dc['name']),'','','',''], ',');
                $data = $this->getClients(0,$dc['id'],$status,$date1,$date2,$branchoffice);
                foreach ($data as $row) {
                    fputcsv($fp, [$row['sale_date'],'REMISION: '.$row['id'].'        FACT: '.$row['folio_fiscal'].'      PEDIDO: '.$row['shopping_cart_id'],'$ '.number_format(floatval($row['cantidad_total']), 2, '.', ','),'$ '.number_format(floatval($row['abonado']), 2, '.', ',')], ',');
                    $id = $row['id'];
                    $datas = $this->getClients(2,$id,null,null,null,$branchoffice);
                    if($datas){
                        foreach ($datas as $rowV2) {
                            fputcsv($fp, [$rowV2['payment_date'],'ABONO REF:  '.utf8_decode($rowV2['reference']),'','$ '.number_format(floatval($rowV2['amount']), 2, '.', ','),''], ',');
                        }
                    }
                }
            }
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Reporte_Pagos' . '.csv');
        $this->response->setContent($output);
        $this->response->send();

    }

    public function getCSVFromRemission ($customer,$status,$date1,$date2,$statusT, $remision, $factura)
    {
//        var_dump($customer,$status,$date1,$date2,$statusT);die();
        $validUser = Auth::getUserInfo($this->config);
        $y = date('Y');
        $where = 'WHERE i.id > 0 ';
        $where .= $validUser->role_id == 1 ? "" : "AND (bs.branch_office_id = $validUser->branch_office_id or ibs.branch_office_id = $validUser->branch_office_id or ls.branch_office_id = $validUser->branch_office_id) ";
        if ($status == 'TODOS') {} else if($status == ''){}else {$where .= " AND i.status = '$status' ";}
        if ($statusT == 'TODOS') {} else if($statusT == ''){}else {$where .= " AND i.status_timbrado = {$statusT} ";}
        if ($customer == 'TODOS') {} else if($customer == ''){}else if($customer == 'null'){}else {$where .= " AND cbo.customer_id = $customer";}
        if ($date1 === '' || $date1 == 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === '' || $date2 == 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        if ($remision != null && $remision != 'null' && $remision != '') {
            $where .= " AND i.id = $remision ";
        }
        if ($factura != null && $factura != 'null' && $factura != '') {
            $where .= " AND i.folio_fiscal = $factura ";
        }
        $where .= " AND i.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."' ";
        $sql = "SELECT i.id,i.shopping_cart_id, to_char(i.sale_date,'YYYY/MM/DD') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office,
                       bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id,
                       d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'YYYY/MM/DD HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.id_request, i.status_timbrado,
                       (select COALESCE((SELECT sum(wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotal,
                       coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(', '||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),','),'') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura,
                       (select COALESCE((SELECT sum(sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                       (select COALESCE((SELECT sum(sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotal,
                       (select COALESCE((SELECT sum(sid.unit_price * wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotalm,
                       (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotalm,
                       (select COALESCE((SELECT sum(sil.unit_price * sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotalm
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                 {$where} 
                ORDER BY i.id;";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $invoice) {
            $sum = ($invoice['baletotalm'] * 1.16) + ($invoice['bulktotalm'] * 1.16) + ($invoice['lamitotalm'] * 1.16);
            $data[$key]['totalacumulado'] = number_format($sum, 2, '.', '');
        }
        foreach ($data as $key => $invoice) {
            $totales = $this->getTotalesGrid($invoice['id']);
            $data[$key]['total'] = $totales['total'];
            $data[$key]['saldo_insoluto'] = $totales['saldo_insoluto'];
        }
        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $content = $this->content;
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('REMISIÓN', 'ESTATUS', 'FECHA', 'PEDIDO', 'FACTURA', 'CLIENTE', 'PIEZAS', 'IMPORTE TOTAL'), ',');

        if (count($data) > 0) {
            $totalesAbierta = 0;
            $totalesDinero = 0;

            foreach ($data as $d) {
                $status = $this->getStatusTimbrado($d['status_timbrado']);
                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $d['id'],
                        $status,
                        $d['sale_date'],
                        $d['shopping_cart_id'],
                        $d['factura'],
                        $d['customer'],
                        number_format($d['bulktotal'], 2, '.', ''),
                        number_format($d['totalacumulado'], 2, '.', '')
                    ], ',');
                }
                $totalesAbierta += $d['bulktotal'];
                $totalesDinero += $d['totalacumulado'];
            }
            fputcsv($fp, array('', '', '','', 'TOTALES GENERALES',number_format($totalesAbierta, 2, '.', '') ,  number_format($totalesDinero, 2, '.', '')), ',');
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Reporte_Remisiones' . '.csv');
        $this->response->setContent($output);
        $this->response->send();

    }

    // CONSULTAS
    public function getAmountsFromInvoiceSQL ($id) {
        $totalFactura = $this->getImpuestos($id);
        return $totalFactura;
    }

    public function getDataFromPayments ($id) {

        $sql = "SELECT sp.*,to_char(sp.payment_date,'DD/MM/YYYY') as payment_date, sip.id as invoice_payment_id, 
        sip.status_timbrado, sip.id_request, sip.message, sip.num_parcialidad, sfp.descripcion as method_label,
        sip.folio||'_'||(case when ssc.branchoffice = 9 then 'BRB780222GD3' when ssc.branchoffice = 12 then 'LOTG541005G9A' else 'RRM010601UV1' end)||'.pdf' as pdf
            FROM sls_payments AS sp
            LEFT JOIN sls_invoice_payments AS sip ON sip.payment_id = sp.id
            LEFT JOIN sat_formas_pagos AS sfp ON sfp.id = sp.method
            LEFT JOIN sls_invoices i on i.id = sp.remision_id
            LEFT JOIN sls_shopping_cart ssc on ssc.id = i.shopping_cart_id
            WHERE sp.remision_id = $id
            ORDER BY sp.id
            ;";
        $data = $this->db->query($sql)->fetchAll();

        return $data;
    }

    public function getPagos ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT * from sls_invoice_payments where invoice_id = {$id} order by num_parcialidad";
            $this->content['pagos'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = 'success';
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function keepCheckingPayments ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT * from sls_invoice_payments where invoice_id = {$id} and (status_timbrado = 3 or status_timbrado = 4 or status_timbrado = 5) order by num_parcialidad";
            $check = $this->db->query($sql)->fetchAll();
            if ($check) {
                $this->content['result'] = true;
            } else {
                $this->content['result'] = false;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function revisarPagos ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $pagos = InvoicePayments::find('status_timbrado != 1 and invoice_id = '.$id);
                if (count($pagos)>0) {
                    foreach ($pagos as $key => $pago) {
                        $pagosFolio = InvoicePayments::find("status_timbrado != 1 and folio = $pago->folio and id_request = '$pago->id_request'");
                        if ($pago && $pago->status_timbrado == 4) {
                            $pago->setTransaction($tx);
                            $service_url = $this->batuta_url.'/api/info_factura';
                            $curl = curl_init($service_url);
                            $curl_post_data = array(
                                'uuid' => $pago->id_request,
                            );
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                            $curl_response = curl_exec($curl);
                            if ($curl_response === false) {
                                $info = curl_getinfo($curl);
                                curl_close($curl);
                                die('error occured during curl exec. Additioanl info: ' . var_export($info));
                            } else {
                                $response = json_decode($curl_response);
                                if ($response->status == 'done') {
                                    $service_url = $this->batuta_url.'/api/get_uuid';
                                    $curl = curl_init($service_url);
                                    $curl_post_data = array(
                                        'uuid' => $pago->id_request,
                                    );
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($curl, CURLOPT_POST, true);
                                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                                    $curl_response2 = curl_exec($curl);
                                    if ($curl_response2 === false) {
                                        $info = curl_getinfo($curl);
                                        curl_close($curl);
                                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                                    } else {
                                        $uuid_factura = $curl_response2;
                                        $pago->status_timbrado = 1;
                                        $pago->uuid = $uuid_factura;
                                        $pago->message = $response->message;
                                        if(count($pagosFolio)>1)
                                            foreach($pagosFolio as $pagoFolio){
                                                $pagoFolio->setTransaction($tx);
                                                $pagoFolio->status_timbrado = 1;
                                                $pagoFolio->uuid = $uuid_factura;
                                                $pagoFolio->message = $response->message;
                                                $pagoFolio->update();
                                            }
                                        if ($pago->update()) {
                                            $this->content['result'] = 'success';
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($pago);
                                            $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                            $tx->rollback();
                                        }
                                    }
                                } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                                    $pago->message = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->message = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                } else  if ($response->status == 'Error' || $response->status == 'error')  {
                                    $pago->status_timbrado = 6;
                                    $pago->message = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->status_timbrado = 6;
                                            $pagoFolio->message = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }
                            }
                            if ($this->content['result'] == 'success') {
                                $tx->commit();
                            }
                        } else if ($pago && $pago->status_timbrado == 3) {
                            $pago->setTransaction($tx);
                            $service_url = $this->batuta_url.'/api/info_general';
                            $curl = curl_init($service_url);
                            $curl_post_data = array(
                                'uuid' => $pago->id_cancelacion,
                            );
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                            $curl_response = curl_exec($curl);
                            if ($curl_response === false) {
                                $info = curl_getinfo($curl);
                                curl_close($curl);
                                die('error occured during curl exec. Additioanl info: ' . var_export($info));
                            } else {
                                $response = json_decode($curl_response);
                                if ($response->status == 'done') {
                                    $pago->status_timbrado = 5;
                                    $pago->id_cancelacion_asc = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->status_timbrado = 5;
                                            $pagoFolio->id_cancelacion_asc = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                                    $pago->message_cancelacion = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                } else  if ($response->status == 'Error' || $response->status == 'error')  {
                                    $pago->status_timbrado = 7;
                                    $pago->message_cancelacion = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->status_timbrado = 7;
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }
                            }
                            if ($this->content['result'] == 'success') {
                                $tx->commit();
                            }
                        } else if ($pago && $pago->status_timbrado == 5) {
                            $service_url = $this->batuta_url.'/api/get_status_cancelacion';
                            $curl = curl_init($service_url);
                            $curl_post_data = array(
                                'uuid' => $pago->id_cancelacion_asc,
                            );
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                            $curl_response = curl_exec($curl);
                            if ($curl_response === false) {
                                $info = curl_getinfo($curl);
                                curl_close($curl);
                                die('error occured during curl exec. Additioanl info: ' . var_export($info));
                            } else {
                                $response = json_decode($curl_response);
                                if ($response->ret_status == 200) {
                                    if ($response->status) {
                                        $pago->status_timbrado = 2;
                                        $pago->message_cancelacion = $response->message;
                                        $pago->acusesat_cancelacion = $response->acuseSat;
                                        $pago->fecha_cancelacion_recibido = date('Y-m-d H:i:s');
                                        if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->status_timbrado = 2;
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                            $pagoFolio->fecha_cancelacion_recibido = date('Y-m-d H:i:s');
                                            $pagoFolio->update();
                                        }
                                        if ($pago->update()) {
                                            $this->content['result'] = 'success';
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($pago);
                                            $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                            $tx->rollback();
                                        }
                                    } else {
                                        $status = explode('|', str_replace(' ', '', $response->message))[0];
                                        if ($status == 211) {
                                            $pago->message_cancelacion = $response->message;
                                            if(count($pagosFolio)>1)
                                                foreach($pagosFolio as $pagoFolio){
                                                    $pagoFolio->setTransaction($tx);
                                                    $pagoFolio->message_cancelacion = $response->message;
                                                    $pagoFolio->update();
                                                }
                                            if ($pago->update()) {
                                                $this->content['result'] = 'success';
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($pago);
                                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                                $tx->rollback();
                                            }
                                        } else {
                                            $pago->status_timbrado = 7;
                                            $pago->message_cancelacion = $response->message;
                                            $pago->acusesat_cancelacion = $response->acuseSat;
                                            if(count($pagosFolio)>1)
                                                foreach($pagosFolio as $pagoFolio){
                                                    $pagoFolio->setTransaction($tx);
                                                    $pagoFolio->status_timbrado = 7;
                                                    $pagoFolio->message_cancelacion = $response->message;
                                                    $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                                    $pagoFolio->update();
                                                }
                                            if ($pago->update()) {
                                                $this->content['result'] = 'success';
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($pago);
                                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                                $tx->rollback();
                                            }
                                        }
                                    }
                                } else if ($response->ret_status == 211) {
                                    $pago->message_cancelacion = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }else if ($response->ret_status == 101){
                                    $pago->message_cancelacion = $response->message;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }else {
                                    $pago->status_timbrado = 7;
                                    $pago->message_cancelacion = $response->message;
                                    $pago->acusesat_cancelacion = $response->acuseSat;
                                    if(count($pagosFolio)>1)
                                        foreach($pagosFolio as $pagoFolio){
                                            $pagoFolio->setTransaction($tx);
                                            $pagoFolio->status_timbrado = 7;
                                            $pagoFolio->message_cancelacion = $response->message;
                                            $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($pago);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }
                            }
                            if ($this->content['result'] == 'success') {
                                $tx->commit();
                            }
                        }else{
                            $response = ['status' => 'error', 'msj' => 'status:'.$pago->status_timbrado];
                        }
                    }
                }else{
                    $response = ['status' => 'error'];
                }
                $this->content['result'] = 'success';
                $this->content['response'] = $response;
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function agregarPago ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $pagos = InvoicePayments::find("invoice_id = {$id} and (status_timbrado != 4 and status_timbrado != 5 and status_timbrado != 3 and status_timbrado != 2) order by num_parcialidad desc");
                $invoiceDetail = InvoiceDetails::find("invoice_id = $id");
                $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $id");
                $invoiceLaminateDetail = InvoiceLaminateDetails::find("invoice_id = $id");
                $subtotal = 0;
                $totalImpuestosTrasladados = 0;
                $totalFactura = 0;
                $totalPagos = 0;
                if ($invoiceDetail) {
                    foreach ($invoiceDetail as $key => $detalle) {
                        $importe = $detalle->unit_price * $detalle->Bales->qty;
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += ($importe * 0.16);
                    }
                }
                if ($invoiceInBulkDetail) {
                    foreach ($invoiceInBulkDetail as $key => $detalle) {
                        $importe += $detalle->unit_price * $detalle->qty;
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += ($importe * 0.16);
                    }
                }
                if ($invoiceLaminateDetail) {
                    foreach ($invoiceLaminateDetail as $key => $detalle) {
                        $importe += $detalle->unit_price * $detalle->qty;
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += ($importe * 0.16);
                    }
                }
                foreach ($pagos as $key => $p) {
                    $totalPagos += $p->total;
                }
                $totalFactura = $subtotal + $totalImpuestosTrasladados;
                $totalActual = $totalFactura - $totalPagos;
                if (number_format($totalActual,2,'.','') >= $request['total']) {
                    $pago = new InvoicePayments();
                    $pago->setTransaction($tx);
                    $pago->invoice_id = intval($id);
                    $pago->fecha_pago = $request['fecha_pago'];
                    $pago->forma_pago = $request['forma_pago'];
                    $pago->num_parcialidad = !empty($pagos[0]->num_parcialidad) ? $pagos[0]->num_parcialidad + 1 : 1;
                    $pago->total = floatval($request['total']);
                    if ($pago->create()) {
                        $this->content['result'] = 'success';
                        $this->content['message'] = ['title' => '¡Exito!', 'content' => 'Se ha creado el pago.'];
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($pago);
                        $this->content['message'] = ['title' => '¡Error!', 'content' => 'No se pudo crear el pago.'];
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = ['title' => '¡Error!', 'content' => 'El valor del pago es mayor al saldo restante de la factura'];
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function borrarPago ($id) {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();

            $pago = InvoicePayments::findFirst($id);
            $pago->setTransaction($tx);

            if ($pago->delete()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El pago ha sido eliminado.');
                $tx->commit();
            } else {
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el pago.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function timbrarPago ($id)
    {
        date_default_timezone_set('America/Mexico_City');
        
        if ($this->userHasPermission()) {
            
            $tx = $this->transactions->get();
            $request = $this->request->getPut();
            $rp = InvoicePayments::findFirst($id);
            $rps = InvoicePayments::find("folio = $rp->folio and serie = '$rp->serie' and status_timbrado != 1");
            if(count($rps)>1){
                $this->timbrarMultiPago ($rp->folio, $rp->serie);
                return $this->content;
            }
            $rp->setTransaction($tx);
            $allRemisiones = InvoicePayments::find('invoice_id = ' . $rp->invoice_id . ' and status_timbrado = 1');
            $saldoPagado = 0;
            if ($allRemisiones) {
                foreach ($allRemisiones as $key => $r) {
                    $saldoPagado += $r->total;
                }
            }
            $remision = Invoices::findFirst($rp->invoice_id);
            $cliente = Customers::findFirst($remision->CustomerBranchOffices->customer_id);
            $rfcBancoCliente = '';
            $cuentaCliente = '';
            $rfcBancoEmpresa = '';
            $cuentaEmpresa = '';
            $domicilioFiscal = $remision->lugar_expedicion;
            $regimenFiscal = $remision->regimen_fiscal;
            if (!empty($remision->tax_company_id)) {
                $cliente = CustomerTaxCompanies::findFirst($remision->tax_company_id);
                $cliente->name = $cliente->razon_social;
                $rfcBancoCliente = $cliente->rfc_banco;
                $cuentaCliente = $cliente->cuenta;
                $bancoCliente = $cliente->banco;
                $domicilioFiscal = $cliente->lugar_expedicion;
                $regimenFiscal = $regimenFiscal == null || $regimenFiscal == ''?$cliente->regimen_fiscal:$regimenFiscal;
            }
            $email = '';
            if (!empty($cliente->email)) {
                $email = '<email>'.$cliente->email.'</email>';
            }
            if ($remision->tipo_cliente != 'publico' && $remision->tax_company_id != 0) {
                $rfc = $cliente->rfc;
                $razon_social = str_replace("&", "_AMP_", $cliente->name);
            } else {
                $rfc = 'XAXX010101000';
                $razon_social = 'Publico en general';
            }
            $cabezera = $cuerpo = '';
            $fecha = str_replace(' ', 'T', $rp->created);
            $fecha_pago = str_replace(' ', 'T', $rp->fecha_pago);
            $invoiceDetail = InvoiceDetails::find("invoice_id = $rp->invoice_id");
            $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $rp->invoice_id");
            $invoiceLaminateDetail = InvoiceLaminateDetails::find("invoice_id = $rp->invoice_id");
            $office = BranchOffices::findFirst($remision->ShoppingCart->branchoffice);
            $subtotal = 0;
            $totalImpuestosTrasladados = 0;
            $totalFactura = 0;
            $totalPagos = 0;
            if ($invoiceDetail) {
                foreach ($invoiceDetail as $key => $detalle) {
                    $importe = number_format($detalle->unit_price * $detalle->Bales->qty,2,'.','');
                    $subtotal += $importe;
                    $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                }
            }
            if ($invoiceInBulkDetail) {
                foreach ($invoiceInBulkDetail as $key => $detalle) {
                    $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                    $subtotal += $importe;
                    $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                    //var_dump($importe,number_format($importe * 0.16,2,'.',''));
                }
            }
            if ($invoiceLaminateDetail) {
                foreach ($invoiceLaminateDetail as $key => $detalle) {
                    $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                    $subtotal += $importe;
                    $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                }
            }
            $totalFactura = $subtotal + $totalImpuestosTrasladados;
            $saldoAnterior = abs($totalFactura - $saldoPagado);
            $saldoInsoluto = number_format($saldoAnterior -  $rp->total,2,'.','');
            $complemento = '<complemento>';
            $complemento = $complemento . '
                <pago20>';
            $complemento .= '<totales>
                                <montoTotalPagos>'.number_format($rp->total, 2, '.', '').'</montoTotalPagos>
                                <totalTrasladosBaseIVA16>'.number_format($rp->total*.86206897, 2, '.', '').'</totalTrasladosBaseIVA16>
                                <totalTrasladosImpuestoIVA16>'.number_format($rp->total*.13793103, 2, '.', '').'</totalTrasladosImpuestoIVA16>
                            </totales>';
            
            $complemento = $complemento . '
                    <pago>
                        <fechaPago>' . $fecha_pago. '</fechaPago>
                        <monedaP>MXN</monedaP>
                        <tipoCambioP>1</tipoCambioP>' .
                        '<formaDePagoP>' . $rp->SatPaymentForms->clave . '</formaDePagoP>';

            $complemento = $complemento . '<monto>' . number_format($rp->total, 2, '.', '') . '</monto>';
            
            if($rp->SatPaymentForms->clave == '03' || $rp->SatPaymentForms->clave == '04' ||
            $rp->SatPaymentForms->clave == '05' || $rp->SatPaymentForms->clave == '28' || $rp->SatPaymentForms->clave == '29'){
                if($rfcBancoCliente != '' && $cuentaCliente != '' && $rfcBancoCliente != null && $cuentaCliente != null){
                    $complemento .= '<rfcCtaOrd>' . $rfcBancoCliente . '</rfcCtaOrd>';
                    if($bancoCliente != null || $bancoCliente != ''){
                        $complemento .= "<nomBancoOrdExt>$bancoCliente</nomBancoOrdExt>";
                    }
                    $complemento .= '<ctaOrd>' . $cuentaCliente . '</ctaOrd>';
                }
                if($office->rfc_banco != '' && $office->cuenta != '' && $office->rfc_banco != null && $office->cuenta != null){
                    $complemento .= '<rfcCtaBen>' . $office->rfc_banco . '</rfcCtaBen>
                    <ctaBen>' . $office->cuenta . '</ctaBen>';
                }
            }
            $complemento .= '</pago>';
            $complemento = $complemento . '
                <relaciones>
                    <relacion>
                        <uuid>' . $remision->uuid . '</uuid>' .
                        '<serie>' . $remision->serie . '</serie>
                        <folio>' . $remision->folio_fiscal . '</folio>
                        <metodoPago>PPD</metodoPago>
                        <numParcialidad>' . $rp->num_parcialidad . '</numParcialidad>    
                        <moneda>MXN</moneda>

                        <saldoAnterior>' . number_format($saldoAnterior, 2, '.', '') . '</saldoAnterior>
                        <impPagado>'.number_format($rp->total, 2, '.', '') .'</impPagado>
                        <saldoinsoluto>' . number_format($saldoInsoluto, 2, '.', '') . '</saldoinsoluto>
                        <objetoImpDR>02</objetoImpDR> 
                        <equivalenciaDR>1</equivalenciaDR>
                        <impuestosDR>
                            <trasladosDR>
                                <trasladoDR>
                                    <baseDR>'.number_format($rp->total*.86206897, 2, '.', '') .'</baseDR>
                                    <impuestoDR>002</impuestoDR>                
                                    <tipoFactorDR>Tasa</tipoFactorDR>
                                    <tasaOCuotaDR>0.160000</tasaOCuotaDR>
                                    <importeDR>'.number_format($rp->total*.13793103, 2, '.', '') .'</importeDR>
                                </trasladoDR> 
                            </trasladosDR>
                        </impuestosDR>
                    </relacion>
                </relaciones>
                <impuestosP>
                    <trasladosP>
                        <trasladoP>
                            <baseP>'.number_format($rp->total*.86206897, 2, '.', '') .'</baseP>
                            <impuestoP>002</impuestoP>
                            <tipoFactorP>Tasa</tipoFactorP>
                            <tasaOCuotaP>0.160000</tasaOCuotaP>
                            <importeP>'.number_format($rp->total*.13793103, 2, '.', '') .'</importeP>
                        </trasladoP>
                    </trasladosP>
                </impuestosP>
            </pago20></complemento></factura></emision>';
            $clienteBatuta = 80;
                switch ($remision->ShoppingCart->branchoffice) {
                    case 9:
                        $clienteBatuta = 80;
                        break;
                    case 13:
                        $clienteBatuta = 82;
                        break;
                    case 12:
                        $clienteBatuta = 81;
                        break;
                    case 14:
                        $clienteBatuta = 82;
                        break;
                    default:
                        $clienteBatuta = 80;
                        break;
                }
            $cabezera = '<emision>
                <cliente>'.$clienteBatuta.'</cliente>
                <factura>
                    <data>
                        <serie>'.$rp->serie.'</serie>
                        <folio>'.$rp->folio.'</folio>
                        <fecha>'.$fecha.'</fecha>
                        <subtotal>0</subtotal>
                        <total>0</total>
                        <tipoDeComprobante>P</tipoDeComprobante>
                        <exportacion>01</exportacion>
                        <lugarDeExpedicion>'.str_pad($remision->lugar_expedicion, 5, "0", STR_PAD_LEFT).'</lugarDeExpedicion>
               
                        <moneda>XXX</moneda>
                    </data>
                    <receptor>
                            <rfc>'.$rfc.'</rfc>
                            <nombre>'.$razon_social.'</nombre>
                            <domicilioFiscalReceptor>'.str_pad($domicilioFiscal, 5, "0", STR_PAD_LEFT).'</domicilioFiscalReceptor>
                            <usoCFDI>CP01</usoCFDI>
                            <regimenFiscalReceptor>'.$regimenFiscal.'</regimenFiscalReceptor>
                    </receptor>';
            $cuerpo =  '<conceptos>
                            <concepto>
                                <cantidad>1</cantidad>
                                <claveUnidad>ACT</claveUnidad>
                                <claveProdServ>84111506</claveProdServ>
                                <descripcion>Pago</descripcion>
                                <valorUnitario>0</valorUnitario>
                                <importe>0</importe>
                                <objetoImp>01</objetoImp>
                            </concepto>
                        </conceptos>';
            $emision = $cabezera . $cuerpo . $complemento;
            
            $serviceURL = $this->batuta_url.'/api/get_emision';
            $curl = curl_init($serviceURL);
            $curlPostData = [
                'data' => $emision,
                'filename' => $remision->folio_fiscal . '_rebasa-P' . $rp->num_parcialidad .'.xml',
                'flavour' => 'Facturacion4',
                'scheduled' => ''
            ];
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostData);
            $curlResponse = curl_exec($curl);

            $data = json_decode($curlResponse);
            curl_close($curl);
            if ($data->result) {
                $rp->status_timbrado = 4;
                $rp->id_request = $data->uuid;
                $rp->message = $data->message;
                $rp->update();
                $this->content['result'] = 'success';
            } else {
                $rp->message = $data->message;
                $rp->update();
            }
            $tx->commit();
            $this->content['data'] = $data;
            $this->content['emision'] = $emision;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        // $this->response->setJsonContent($this->content);
        // $this->response->send();
        return $this->content;
    }

    public function timbrarMultiPago ($folio)
    {
        date_default_timezone_set('America/Mexico_City');
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();
            $rps = InvoicePayments::find("folio = $folio and status_timbrado != 1");
            if(count($rps)==0){
                $this->content['data'] = ['result'=>false];
                return $this->content;
            }
            $rps0 = InvoicePayments::findFirst("folio = $folio and status_timbrado != 1");
            $remisionF = Invoices::findFirst($rps0->invoice_id);
            $cliente = Customers::findFirst($remisionF->CustomerBranchOffices->customer_id);
            $office = BranchOffices::findFirst($remisionF->ShoppingCart->branchoffice);
            $rfcBancoCliente = '';
            $cuentaCliente = '';
            $bancoCliente = '';
            $rfcBancoEmpresa = '';
            $cuentaEmpresa = '';
            $domicilioFiscal = $remisionF->lugar_expedicion;
            $regimenFiscal = $remisionF->regimen_fiscal;
            if (!empty($remisionF->tax_company_id)) {
                $cliente = CustomerTaxCompanies::findFirst($remisionF->tax_company_id);
                //quité razon social
                $cliente->name = $cliente->razon_social;
                $rfcBancoCliente = $cliente->rfc_banco;
                $cuentaCliente = $cliente->cuenta;
                $bancoCliente = $cliente->banco;
                $domicilioFiscal = $cliente->lugar_expedicion;
                $regimenFiscal = $regimenFiscal == null || $regimenFiscal == ''?$cliente->regimen_fiscal:$regimenFiscal;
            }
            $montoTotal = 0;

            $email = '';
            if (!empty($cliente->email)) {
                $email = '<email>'.$cliente->email.'</email>';
            }
            if ($remisionF->tipo_cliente != 'publico' && $remisionF->tax_company_id != 0) {
                $rfc = $cliente->rfc;
                $razon_social = str_replace("&", "_AMP_", $cliente->name);
            } else {
                $rfc = 'XAXX010101000';
                $razon_social = 'Publico en general';
            }
            $cabezera = $cuerpo = '';
            $fecha = str_replace(' ', 'T', $rps0->created);
            $fecha_pago = str_replace(' ', 'T', $rps0->fecha_pago);
            $relaciones = '';
            $totalImporte = 0;
            $totalImpuestos = 0;
            foreach($rps as $rp){
                $rp->setTransaction($tx);
                $sql = "select coalesce(sum(total),0) as total from sls_invoice_payments
                where invoice_id = $rp->invoice_id and status_timbrado = 1;";

                $saldoPagado = $this->db->query($sql)->fetch()['total'];
                $montoTotal += $rp->total;
                $remision = Invoices::findFirst($rp->invoice_id);

                $invoiceDetail = InvoiceDetails::find("invoice_id = $rp->invoice_id");
                $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $rp->invoice_id");
                $invoiceLaminateDetail = InvoiceLaminateDetails::find("invoice_id = $rp->invoice_id");
                $subtotal = 0;
                $totalImpuestosTrasladados = 0;
                $totalFactura = 0;
                $totalPagos = 0;
                if ($invoiceDetail) {
                    foreach ($invoiceDetail as $key => $detalle) {
                        $importe = number_format($detalle->unit_price * $detalle->Bales->qty,2,'.','');
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                    }
                }
                if ($invoiceInBulkDetail) {
                    foreach ($invoiceInBulkDetail as $key => $detalle) {
                        $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                    }
                }
                if ($invoiceLaminateDetail) {
                    foreach ($invoiceLaminateDetail as $key => $detalle) {
                        $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                        $subtotal += $importe;
                        $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                    }
                }
                $totalImporte += number_format($rp->total*.86206897,2,'.','');
                $totalImpuestos += number_format($rp->total*.13793103,2,'.','');
                $totalFactura = $subtotal + $totalImpuestosTrasladados;
                $saldoAnterior = abs($totalFactura - $saldoPagado);
                $saldoInsoluto = number_format($saldoAnterior -  $rp->total,2,'.','');
                $relaciones .=   '<relacion>
                                    <uuid>' . $remision->uuid . '</uuid>' .
                                    '<serie>' . $remision->serie . '</serie>
                                    <folio>' . $remision->folio_fiscal . '</folio>
                                    <metodoPago>PPD</metodoPago>
                                    <numParcialidad>' . $rp->num_parcialidad . '</numParcialidad>    
                                    <moneda>MXN</moneda>
                                    <saldoAnterior>' . number_format($saldoAnterior, 2, '.', '') . '</saldoAnterior>
                                    <impPagado>'.number_format($rp->total, 2, '.', '') .'</impPagado>
                                    <saldoinsoluto>' . number_format($saldoInsoluto, 2, '.', '') . '</saldoinsoluto>
                                    <objetoImpDR>02</objetoImpDR> 
                                    <equivalenciaDR>1</equivalenciaDR>
                                    <impuestosDR>
                                        <trasladosDR>
                                            <trasladoDR>
                                                <baseDR>'.number_format($rp->total*.86206897, 2, '.', '') .'</baseDR>
                                                <impuestoDR>002</impuestoDR>                
                                                <tipoFactorDR>Tasa</tipoFactorDR>
                                                <tasaOCuotaDR>0.160000</tasaOCuotaDR>
                                                <importeDR>'.number_format($rp->total*.13793103, 2, '.', '') .'</importeDR>
                                            </trasladoDR> 
                                        </trasladosDR>
                                    </impuestosDR>
                                </relacion>';
            }
            
            $complemento = '<complemento>
                        <pago20>';
            $complemento .= '<totales>
                                <montoTotalPagos>'.number_format($montoTotal, 2, '.', '').'</montoTotalPagos>
                                <totalTrasladosBaseIVA16>'.number_format($totalImporte, 2, '.', '').'</totalTrasladosBaseIVA16>
                                <totalTrasladosImpuestoIVA16>'.number_format($totalImpuestos, 2, '.', '').'</totalTrasladosImpuestoIVA16>
                            </totales>';
            
            $complemento = $complemento . '
                    <pago>
                        <fechaPago>' . $fecha_pago. '</fechaPago>
                        <monedaP>MXN</monedaP>
                        <tipoCambioP>1</tipoCambioP>' .
                '<formaDePagoP>' . $rps0->SatPaymentForms->clave . '</formaDePagoP>';

            $complemento = $complemento . '<monto>' . number_format($montoTotal, 2, '.', '') . '</monto>';
            if($rp->SatPaymentForms->clave == '03' || $rp->SatPaymentForms->clave == '04' ||
            $rp->SatPaymentForms->clave == '05' || $rp->SatPaymentForms->clave == '28' || $rp->SatPaymentForms->clave == '29'){
                if($rfcBancoCliente != '' && $cuentaCliente != '' && $rfcBancoCliente != null && $cuentaCliente != null){
                    $complemento .= '<rfcCtaOrd>' . $rfcBancoCliente . '</rfcCtaOrd>';
                    if($bancoCliente != null || $bancoCliente != ''){
                        $complemento .= "<nomBancoOrdExt>$bancoCliente</nomBancoOrdExt>";
                    }
                    $complemento .= '<ctaOrd>' . $cuentaCliente . '</ctaOrd>';
                }
                if($office->rfc_banco != '' && $office->cuenta != '' && $office->rfc_banco != null && $office->cuenta != null){
                    $complemento .= '<rfcCtaBen>' . $office->rfc_banco . '</rfcCtaBen>
                    <ctaBen>' . $office->cuenta . '</ctaBen>';
                }
            }
            $complemento .= '</pago>';
            $complemento = $complemento . '
                <relaciones>
                   '.$relaciones.'
                </relaciones>
                <impuestosP>
                    <trasladosP>
                        <trasladoP>
                            <baseP>'.number_format($totalImporte, 2, '.', '') .'</baseP>
                            <impuestoP>002</impuestoP>
                            <tipoFactorP>Tasa</tipoFactorP>
                            <tasaOCuotaP>0.160000</tasaOCuotaP>
                            <importeP>'.number_format($totalImpuestos, 2, '.', '') .'</importeP>
                        </trasladoP>
                    </trasladosP>
                </impuestosP>
            </pago20></complemento></factura></emision>';
            $clienteBatuta = 80;
                switch ($remisionF->ShoppingCart->branchoffice) {
                    case 9:
                        $clienteBatuta = 80;
                        break;
                    case 13:
                        $clienteBatuta = 82;
                        break;
                    case 12:
                        $clienteBatuta = 81;
                        break;
                    case 14:
                        $clienteBatuta = 82;
                        break;
                    default:
                        $clienteBatuta = 80;
                        break;
                }
            $cabezera = '<emision>
                <cliente>'.$clienteBatuta.'</cliente>
                <factura>
                    <data>
                        <serie>'.$rps0->serie.'</serie>
                        <folio>'.$rps0->folio.'</folio>
                        <fecha>'.$fecha.'</fecha>
                        <subtotal>0</subtotal>
                        <total>0</total>
                        <tipoDeComprobante>P</tipoDeComprobante>
                        <exportacion>01</exportacion>
                        <lugarDeExpedicion>'.str_pad($remisionF->lugar_expedicion, 5, "0", STR_PAD_LEFT).'</lugarDeExpedicion>
                        
                        <moneda>XXX</moneda>
                    </data>
                    <receptor>
                            <rfc>'.$rfc.'</rfc>
                            <nombre>'.$razon_social.'</nombre>
                            <domicilioFiscalReceptor>'.str_pad($domicilioFiscal, 5, "0", STR_PAD_LEFT).'</domicilioFiscalReceptor>
                            <usoCFDI>CP01</usoCFDI>
                            <regimenFiscalReceptor>'.$regimenFiscal.'</regimenFiscalReceptor>
                    </receptor>';
            $cuerpo =  '<conceptos>
                            <concepto>
                                <cantidad>1</cantidad>
                                <claveUnidad>ACT</claveUnidad>
                                <claveProdServ>84111506</claveProdServ>
                                <descripcion>Pago</descripcion>
                                <valorUnitario>0</valorUnitario>
                                <importe>0</importe>
                                <objetoImp>01</objetoImp>
                            </concepto>
                        </conceptos>';
            $emision = $cabezera . $cuerpo . $complemento;
            $serviceURL = $this->batuta_url.'/api/get_emision';
            $curl = curl_init($serviceURL);
            $curlPostData = [
                'data' => $emision,
                'filename' => $rps0->folio . '_rebasa-P0.xml',
                'flavour' => 'Facturacion4',
                'scheduled' => ''
            ];
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostData);
            $curlResponse = curl_exec($curl);

            $data = json_decode($curlResponse);
            curl_close($curl);
            foreach($rps as $rp){
                if ($data->result) {
                    $rp->status_timbrado = 4;
                    $rp->id_request = $data->uuid;
                    $rp->message = $data->message;
                    $rp->update();
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Abonos registrados.');
                } else {
                    $rp->message = $data->message;
                    $rp->update();
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('No se pudo timbrar');
                }
            }
            $tx->commit();
            $this->content['data'] = $data;
            $this->content['emision'] = $emision;
            
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        // $this->response->setJsonContent($this->content);
        // $this->response->send();
        return $this->content;
    }

    public function cancelarPago ($id)
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();
            try {
                $invoice = InvoicePayments::findFirst($id);
                $pagosFolio = InvoicePayments::find(
                    "status_timbrado = 1 and folio = $invoice->folio and id_request = '$invoice->id_request' 
                    and id != $invoice->id"
                );
                $factura = Invoices::findFirst($invoice->invoice_id);
                $clienteBatuta = 80;
                $invoice->motivo_cancelacion = $request['motivo_cancelacion'];
                $invoice->folio_sustituye = $request['folio_sustituye']!=""?$request['folio_sustituye']:null;
                switch ($factura->ShoppingCart->branchoffice) {
                    case 9:
                        $clienteBatuta = 80;
                        break;
                    case 13:
                        $clienteBatuta = 82;
                        break;
                    case 12:
                        $clienteBatuta = 81;
                        break;
                    case 14:
                        $clienteBatuta = 82;
                        break;
                    default:
                        $clienteBatuta = 80;
                        break;
                }
                if ($invoice) {
                    $folio_sustituye = '';
                    if($invoice->folio_sustituye != null && $invoice->motivo_cancelacion == '01'){
                        $invoiceSustituye = InvoicePayments::findFirst("
                        folio = $invoice->folio_sustituye 
                        and serie = '$invoice->serie' 
                        and status_timbrado = 1
                        and folio != $invoice->folio");
                        if(!$invoiceSustituye || $invoice->Invoices->tax_company_id != $invoiceSustituye->Invoices->tax_company_id){
                            $this->content['errors'] = "Verifiacar que el folio corresponda a una factura del mismo cliente y no este cancelada.";
                            $this->content['message'] = Message::error('Folio que sustituye no es valido.');
                            $this->content['result'] = 'error';
                            $response = ['error' => 'Factura con pagos relacionados.'];
                            $this->response->setJsonContent($this->content);
                            $this->response->send();
                            return;
                        }
                        $folio_sustituye = "|$invoiceSustituye->uuid";
                    }
                    $CancelTimbrado = "<cancelarTimbrado>
                        <client>".$clienteBatuta."</client>
                        <uuid>{$invoice->uuid}|{$invoice->motivo_cancelacion}{$folio_sustituye}</uuid>
                        <type>cancelarTimbrado</type>
                        </cancelarTimbrado>";

                    $service_url = $this->batuta_url.'/api/get_emision';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'data' => $CancelTimbrado,
                        'filename' => 'ctimbrado_' . $invoice->uuid . '.xml',
                        'flavour' => 'CancelarFacturaNew',
                        'scheduled' => '',
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response !== false) {
                        $response = json_decode($curl_response);
                        $invoice->setTransaction($tx);
                        if ($invoice) {
                            if ($response->result) {
                                $invoice->status_timbrado = 3;
                                $invoice->id_cancelacion = $response->uuid;
                                $invoice->message_cancelacion = $response->message;
                                $invoice->fecha_cancelacion_envio = date('Y-m-d H:i:s');
                                foreach($pagosFolio as $pagoFolio){
                                    $pagoFolio->setTransaction($tx);
                                    $pagoFolio->status_timbrado = 3;
                                    $pagoFolio->id_cancelacion = $response->uuid;
                                    $pagoFolio->message_cancelacion = $response->message;
                                    $pagoFolio->fecha_cancelacion_envio = date('Y-m-d H:i:s');
                                    $pagoFolio->motivo_cancelacion = $invoice->motivo_cancelacion;
                                    $pagoFolio->update();
                                }
                                $invoice->update();
                                $this->content['result'] = 'success';
                            } else {
                                $invoice->message_cancelacion = $response->message;
                                $invoice->update();
                            }
                        }
                    }

                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
            $this->content['response'] = $response;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getInvoicesSQL () {
        $sql = "SELECT i.id,i.shopping_cart_id, to_char(i.sale_date,'YYYY/MM/DD') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office, bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'YYYY/MM/DD HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.id_request, i.status_timbrado, ssc.tax_invoice,i.status_email
                FROM sls_invoices AS i
                left join sls_shopping_cart as ssc on ssc.id = i.shopping_cart_id
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                ORDER BY id DESC;";
        $data = $this->db->query($sql)->fetchAll();

        foreach ($data as $key => $invoice) {
            $totales = $this->getTotalesGrid($invoice['id']);
            $data[$key]['total'] = $totales['total'];
            $data[$key]['saldo_insoluto'] = $totales['saldo_insoluto'];
        }
        return $data;
    }

    public function getPaymentsSQL () {
        $sql = "SELECT i.id,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, ssc.tax_invoice
            ,i.status_email
                FROM sls_invoices AS i
                left join sls_shopping_cart as ssc on ssc.id = i.shopping_cart_id
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                WHERE i.status = 'ENVIADO' OR i.status = 'FACTURADO'
                ORDER BY id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getInvoicebyIdSQL ($id) {
        $sql = "SELECT i.id, to_char(i.sale_date:: DATE,'dd/mm/yyyy') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office, bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.documents_returned_by_driver, i.comments, i.document_file, i.status_timbrado, i.message, i.message_cancelacion, i.id_request, i.uuid, i.carrier_id,i.carrier_name,i.guide_number,i.pallet_document, to_char(i.date_delivered,'DD/MM/YYYY') as date_delivered,ssc.tax_invoice, i.shopping_cart_id as shopping_cart_id,ssc.loan,
        ssc.document_id, ssc.oc_reference as oc_referenceshp, i.status_email,i.folio_relacionado,
        i.folio_fiscal||'_'||(case when ssc.branchoffice = 9 then 'BRB780222GD3' when ssc.branchoffice = 12 then 'LOTG541005G9A' else 'RRM010601UV1' end)||'.pdf' as pdf,
        ssc.special_order as special_order
                FROM sls_invoices AS i
                left join sls_shopping_cart as ssc on ssc.id = i.shopping_cart_id
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                WHERE i.id = $id;";
        $data = $this->db->query($sql)->fetch();
        return $data;
    }

    public function getqtysFromBalesScSQL ($id) {
        $sql = "SELECT sscbd.id, sscbd.product_id, sscbd.qty, sscbd.price_product, wp.name as product_name, wl.code as product_line, wc.code as product_cat
                FROM sls_shopping_cart_bale_details AS sscbd 
                LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
                LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
                LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                WHERE sscbd.invoice_id = $id
                ORDER BY sscbd.id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getGridSQL ($id,$date1,$date2,$status,$statusT,$request, $remision,$factura) {
        
        $user = Auth::getUserInfo($this->config);
        // echo("<pre>");
        // print_r($user->role_id);
        $created_by= $user->id;
        // exit();
        $y = date('Y');
        $where = 'WHERE i.id > 0 '. ($user->role_id == 1 ? "" : " AND sc.branchoffice = $user->branch_office_id ");
        if ($status == 'TODOS') {} else if($status == ''){}else {$where .= " AND i.status = '$status' ";}
        if ($statusT == 'TODOS') {} else if($statusT == ''){}else {$where .= " AND i.status_timbrado = {$statusT} ";}
        if ($id == 'TODOS') {} else if($id == ''){}else {$where .= " AND cbo.customer_id = $id";}
        if ($date1 === '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === '') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        if ($remision != '' && $remision != null && $remision != 'null') {
            $where .= " AND i.id = $remision ";
        }
        if ($factura != '' && $factura != null && $factura != 'null') {
            $where .= " AND i.folio_fiscal = $factura ";
        }

        $where .= " AND i.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."' ";
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( i.id::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(', '||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),','),'') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) ILIKE '%".$filter."%')";
        }

        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY " . trim($pagination['sortBy']);
        } else {
            $sortBy .= " ORDER BY i.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT 50";

        $sql = "
            SELECT
                count(i.id) AS count
            FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
            {$where}";
        /*$sql = "
            SELECT
                count(i.id) AS count
            FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
            {$where}";*/

        $invoicesCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT i.id,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent,  bm.storage_id AS bale_storage_id,   i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id,ibs.name as exit_branch_office, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'DD/MM/YYYY HH24:MI:SS') AS bale_movement_date,  i.id_request, i.status_timbrado,
                coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||folio),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura,
                i.folio_fiscal||'_'||(case when sc.branchoffice = 9 then 'BRB780222GD3' when sc.branchoffice = 12 then 'LOTG541005G9A' else 'RRM010601UV1' end)||'.pdf' as pdf
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = bm.storage_id
                LEFT JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
                 {$where} GROUP BY i.id,a.nickname,bm.storage_id,cbo.name,cbo.customer_id,ibs.name,c.name,
                 c.price_list,i.status, i.driver_id, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file,bm.date,sc.branchoffice {$sortBy} {$desc} {$offset} {$limit} ;";
        
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $invoice) {
             $totales = $this->getTotalesGrid($invoice['id']);
             $data[$key]['total'] = $totales['total'];
             $data[$key]['saldo_insoluto'] = $totales['saldo_insoluto'];
        }
        // exit();
        $response = array('data' => $data, 'rowCounts' => $invoicesCount[0]['count']);
        return $response;
    }

    public function getGridPaymentsSQL ($id,$date1,$date2,$status,$type,$request) {
        // print_r($request);
        // exit();
        $validUser = Auth::getUserInfo($this->config);
        $y = date('Y');
        $where = "";
        $where2 = "";
        $order = "";
        $contador = 0;
        $contadorv2 = 0;
        if (count($status) > 0) {
            $f = date('Y-m-d');
            $where .= "AND (";
            foreach ($status as $ts) {
                if ($ts == 1) {
                    $where .= "  (i.status_payment = 2)";
                } else if ($ts == 2) {
                    $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 0)";
                } else if ($ts == 3) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') > '$f' and i.status_payment = 1)";
                } else if ($ts == 4) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 1)";
                } else if ($ts == 5) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 0)";
                } else if ($ts == 6) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                } else if ($ts == 7) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                }
                if (count($status) - 1 > $contador) {
                    $where .= ' OR ';
                }
                $contador++;
            }
            $where .= ")";
        } else {
            $where .= "";
        }
        if(!empty($request['branch'])){
            if(is_array($request['branch'])){
                $branch_office_id = $request['branch']['value'];
            } else {
                $branch_office_id = $request['branch'];
            }
        } else {
            $branch_office_id = null;
        }
        
        $where .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id ";
        $where2 .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id ";
        if ($id == 'TODOS') {} else if($id == ''){}else {$where .= " AND cbo.customer_id = $id";$where2 .= " AND cbo.customer_id = $id";}
        if ($branch_office_id == 'TODAS') {} else if($branch_office_id == ''){}else {$where .= " AND sc.branchoffice = $branch_office_id";$where2 .= " AND sc.branchoffice = $branch_office_id";}
        if ($date1 === '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($date1.' 00:00:00.000000'));
        }
        if ($date2 === '') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($date2.' 23:59:59.000000'));
        }
        $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."' ";
        $where2 .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."' ";

        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)){
            $where .= " AND ( i.id::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR i.serie||'-'||i.folio_fiscal ILIKE '%".$filter."%')";
            $where2 .= " AND ( i.id::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR i.serie||'-'||i.folio_fiscal ILIKE '%".$filter."%')";
        }

        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY " . trim($pagination['sortBy']." ");
        } else {
            $sortBy .= " ORDER BY i.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage']." ";

        $sql = "SELECT count(i.id)
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
                {$where2} and loan = 0";
        $invoicesCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT i.id, i.status_payment,coalesce(i.serie,'')||'-'||i.folio_fiscal || (SELECT coalesce(' ('||array_to_string(array_agg(coalesce(serie,'')||'-'||i.folio_fiscal),', ')||')','') AS x from sls_invoice_payments where status_timbrado = 1 and invoice_id = i.id) as factura,
                (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
                (select COALESCE((SELECT sum(sid.unit_price * wms_bales.qty) from sls_invoice_details as sid inner join wms_bales on sid.bale_id = wms_bales.id and sid.invoice_id = i.id), 0)) as baletotal,
                (select COALESCE((SELECT sum(round((sib.unit_price * sib.qty)::numeric,2) + round((sib.unit_price * sib.qty * .16)::numeric,2)) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
                (select COALESCE((SELECT sum(sil.unit_price * sil.qty) from sls_invoice_laminate_details as sil where sil.invoice_id = i.id), 0)) as lamitotal,
                TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'YYYY-MM-DD') AS expired_date,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id AS bale_branch_office_id, bb.name AS bale_branch_office, ibs.branch_office_id AS in_bulk_branch_office_id, ibb.name AS in_bulk_branch_office, ls.branch_office_id AS laminate_branch_office_id, lb.name AS laminate_branch_office, bm.storage_id AS bale_storage_id, bs.name AS bale_storage, ibm.storage_id AS in_bulk_storage_id, ibs.name AS in_bulk_storage, lm.storage_id AS laminate_storage_id, ls.name AS laminate_storage, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.customer_id, c.name AS customer, c.price_list AS customer_price_list, i.status, i.driver_id, d.name AS driver, i.deliver_status_by, i.deliver_status_at, i.documents_returned_by_driver, i.comments, i.document_file, to_char(bm.date,'DD/MM/YYYYY HH24:MI:SS') AS bale_movement_date, to_char(ibm.date,'YYYY/MM/DD HH24:MI:SS') AS in_bulk_movement_date, to_char(lm.date,'YYYY/MM/DD HH24:MI:SS') AS laminate_movement_date, i.status_timbrado, i.metodo_pago,CONCAT(i.serie,'-',i.folio_fiscal) as factura,
                TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'DD/MM/YYYY') AS fecha_vencimiento
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.bale_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bb ON bb.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                LEFT JOIN wms_movements AS lm ON lm.id = i.laminate_movement_id
                LEFT JOIN wms_storages AS ls ON ls.id = lm.storage_id
                LEFT JOIN wms_branch_offices AS lb ON lb.id = ls.branch_office_id
                JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
                {$where} and loan = 0
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days
                {$sortBy} {$desc} {$offset} {$limit}
                ";
                //print_r($sql);
                //exit();
        $data = $this->db->query($sql)->fetchAll();

        foreach ($data as $key => $d) {
            $id = $d['id'];
            $totales = $this->getImpuestos($id);
            $resta = $totales - $d['abonado'];
            $data[$key]['cantidad_total'] = $totales;
            $data[$key]['cantidad_restante'] = $resta;
             if ($d['expired_date']) {
                $fecha = date('Y-m-d');
                /*var_dump($d['expired_date']);
                print_r(" %%% ");
                var_dump($fecha);
                print_r(" %%% ");*/
                if (strtotime($d['expired_date']) > strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'PENDIENTE DE PAGO';
                    $data[$key]['color_label'] = 'blue-6';
                 } else if (strtotime($d['expired_date']) > strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'ABONADO';
                    $data[$key]['color_label'] = 'warning';
                } else if (strtotime($d['expired_date']) == strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'POR VENCER';
                    $data[$key]['color_label'] = 'amber'; 
                } else if (strtotime($d['expired_date']) == strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'POR VENCER ABONADO';
                    $data[$key]['color_label'] = 'amber';
                } else if (strtotime($d['expired_date']) < strtotime($fecha) && $d['status_payment'] == 0) {
                    $data[$key]['vencimiento'] = 'VENCIDO';
                    $data[$key]['color_label'] = 'red-14';
                } else if (strtotime($d['expired_date']) < strtotime($fecha) && $d['status_payment'] == 1) {
                    $data[$key]['vencimiento'] = 'VENCIDO ABONADO';
                    $data[$key]['color_label'] = 'red-14';
                } else if ($d['status_payment'] == 2) {
                    $data[$key]['vencimiento'] = 'PAGADO';
                    $data[$key]['color_label'] = 'green-6';
                }
            } else {
                $data[$key]['vencimiento'] = '-';
            }

        }

       /* foreach ($data as $key => $d){
           $id = $d['id'];
           $resta = $d['bulktotal'] - $d['abonado'];
           $data[$key]['cantidad_total'] = $d['bulktotal'];
           $data[$key]['cantidad_restante'] = $resta;
       } */
        $response = array('data' => $data, 'rowCounts' => $invoicesCount[0]['count']);
        return $response;
    }

    public function getImpuestos ($id) {
        $invoiceDetail = InvoiceDetails::find("invoice_id = $id");
        $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $id");
        $subtotal = 0;
        $totalImpuestosTrasladados = 0;
        $totalFactura = 0;
        $totalPagos = 0;
        if ($invoiceInBulkDetail) {
            foreach ($invoiceInBulkDetail as $key => $detalle) {
                $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                $subtotal += $importe;
                $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
            }
        }
        $totalFactura = $subtotal + $totalImpuestosTrasladados;

        return $totalFactura;
    }
    public function getinvoicePDFSQL ($id) {
        $sql = "SELECT i.id, ssc.comments as cart_comment, TO_CHAR(i.sale_date, 'dd/mm/yyyy') AS sale_date, i.agent_id, a.nickname AS agent, bs.branch_office_id, bo.name AS branch_office, bo.address AS branch_office_address, i.customer_branch_office_id, cbo.name AS customer_branch_office, cbo.street AS customer_branch_office_street, cbo.outdoor_number AS customer_branch_office_outdoor_number, cbo.zip_code AS customer_branch_office_zip_code, cbo.phone_number AS customer_branch_office_phone_number, cbo.customer_id, TO_CHAR(cbo.open_horary, 'HH24:MI') AS open_horary, TO_CHAR(cbo.close_horary, 'HH24:MI') AS close_horary, c.serial AS customer_serial, c.name AS customer_name, c.tradename AS customer_tradename, c.street, c.outdoor_number, c.indoor_number, c.suburb, c.municipality, c.state, c.term, i.bale_movement_id, i.in_bulk_movement_id, i.driver_id, d.name AS driver, i.comments
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_shopping_cart AS ssc ON ssc.id = i.shopping_cart_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_movements AS bm ON bm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS bs ON bs.id = bm.storage_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = bs.branch_office_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                WHERE i.id = $id;";
        $data = $this->db->query($sql)->fetch();
        return $data;
    }

    public function getbaleDetailsPDFSQL ($id) {
        $sql = "SELECT d.id, d.invoice_id, d.bale_id, b.product_id, p.name AS product, b.qty, d.unit_price, b.qty * d.unit_price AS amount
                            FROM sls_invoice_details AS d
                            INNER JOIN sls_invoices AS i ON i.id = d.invoice_id
                            INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                            INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                            INNER JOIN wms_bales AS b ON b.id = d.bale_id
                            INNER JOIN wms_products AS p ON p.id = b.product_id
                            WHERE d.invoice_id = $id
                            ORDER BY product ASC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getinBulkDetailsPDFSQL ($id) {
        $sql = "SELECT d.id, d.invoice_id, d.product_id, p.name AS product, d.qty, d.unit_price, d.qty * d.unit_price AS amount, d.packages_qty
                            FROM sls_invoice_in_bulk_details AS d
                            INNER JOIN sls_invoices AS i ON i.id = d.invoice_id
                            INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                            INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                            INNER JOIN wms_products AS p ON p.id = d.product_id
                            WHERE d.invoice_id = $id and d.qty > 0
                            ORDER BY product ASC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getLaminateDetailsPDFSQL ($id) {
        $sql = "SELECT d.id, d.invoice_id, d.product_id, p.name AS product, d.qty, d.unit_price, d.qty * d.unit_price AS amount
                            FROM sls_invoice_laminate_details AS d
                            INNER JOIN sls_invoices AS i ON i.id = d.invoice_id
                            INNER JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                            INNER JOIN sls_customers AS c ON c.id = cbo.customer_id
                            INNER JOIN wms_products AS p ON p.id = d.product_id
                            WHERE d.invoice_id = $id and d.qty > 0
                            ORDER BY product ASC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getTotalesGrid ($id) {
        $sqlInvoice ="SELECT * from sls_invoices where id = $id";
        $invoice = $this->db->query($sqlInvoice)->fetch();
        $invoice_id = $invoice['id'];
        // $invoice = Invoices::findFirst($id);
        // $sqlInvoiceDetail ="SELECT * from sls_invoice_details where invoice_id = $id";
        // $invoiceDetail = $this->db->query($sqlInvoiceDetail)->fetchAll();
        // $invoiceDetail = InvoiceDetails::find("invoice_id = $invoice->id");

        $sqlInvoiceInBulkDetail ="SELECT * from sls_invoice_in_bulk_details where invoice_id = $id";
        $invoiceInBulkDetail = $this->db->query($sqlInvoiceInBulkDetail)->fetchAll();
        // $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $invoice->id");
        
        // $invoiceLaminateDetail = InvoiceLaminateDetails::find("invoice_id = $invoice->id");
        $subtotal = 0;
        $totalImpuestosTrasladados = 0;
        $totalFactura = 0;
        if ($invoice['status_timbrado'] === 1) {
            /* if ($invoiceDetail) {
                foreach ($invoiceDetail as $key => $detalle) {
                     // print_r($detalle);
                     $importe = number_format($detalle['unit_price'] * $detalle['qty'],2,'.','');
                     $subtotal += $importe;
                     $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                }
            } */
            // exit();
            if ($invoiceInBulkDetail) {
                foreach ($invoiceInBulkDetail as $key => $detalle) {
                    // print_r($detalle);
                    $importe = number_format($detalle['unit_price'] * $detalle['qty'],2,'.','');
                    $subtotal += $importe;
                    $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                }
            }
            /* if ($invoiceLaminateDetail) {
                foreach ($invoiceLaminateDetail as $key => $detalle) {
                    $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                    $subtotal += $importe;
                    $totalImpuestosTrasladados += number_format($importe * 0.16,2,'.','');
                }
            } */
            $totalFactura = $subtotal + $totalImpuestosTrasladados;
            if ($invoice['metodo_pago'] === 'PPD') {
                $sqlallRemisiones ="SELECT * from sls_invoice_payments where invoice_id = $invoice_id and status_timbrado = 1";
                $allRemisiones = $this->db->query($sqlallRemisiones)->fetchAll();
                // $allRemisiones = InvoicePayments::find('invoice_id = ' . $invoice['id'] . ' and status_timbrado = 1');
                $saldoPagado = 0;
                if ($allRemisiones) {
                    foreach ($allRemisiones as $key => $r) {
                        $saldoPagado += $r['total'];
                    }
                }
                $saldoInsoluto = abs($totalFactura - $saldoPagado);
            } else {
                $saldoInsoluto = 0.00; 
            }
        } else {
            $totalFactura = 0.00; 
            $saldoInsoluto = 0.00; 
        }
        return array('total' => number_format($totalFactura,2,'.',''), 'saldo_insoluto' => number_format($saldoInsoluto,2,'.',''));
    }
}
class SaleNotesPdfControllerInv extends FPDF
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
    /*function Header()
    {
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,10,8,50,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-105),10);
        $this->SetFont('Arial','B',12);

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
        
    }*/
    function cabezera1()
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
        $this->SetXY(($this->GetPageWidth()-100),35);
        $this->SetFont('Arial','B',16);
        $this->Cell(67,7,utf8_decode('#'.$this->serial),0,0,'R');
        // $this->SetXY(($this->GetPageWidth()-105),35);
        // $this->SetFont('Nunito-Regular','',15);
        // $this->Cell(77,7,' '.$this->serial,0,0,'R'); 
        
    }
    function cabezera2()
    {
        $this->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,10,8,50,0,'PNG');

        $this->SetXY(($this->GetPageWidth()-105),10);
        $this->SetFont('Arial','B',12);
        /* $this->SetTextColor(255,255,255);*/
        $this->SetFillColor(255,255,255); 
        $this->Cell(77,2,utf8_decode('LOPEZ DE LARA TINAJERO GUILLERMO'),0,1,'C',1);
        //$this->SetXY(($this->GetPageWidth()),15);
        //$this->SetTextColor(81,106,53);
        $this->SetXY(($this->GetPageWidth()-105),12);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('FRANCISCA ESCARZAGA # 500 COL. SANTA FE'),0,0,'R');

        $this->SetXY(($this->GetPageWidth()-105),16);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('C.P. 34240'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),20);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('TELEFONO: (618)810 2521'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),24);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('correo@empresa.mx'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),28);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('DURANGO, Dgo.'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),34);
        $this->SetFont('Arial','B',16);
        $this->Cell(98,7,utf8_decode($this->quote),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-100),39);
        $this->SetFont('Arial','B',16);
        $this->Cell(67,7,utf8_decode('#'.$this->serial),0,0,'R');
        //$this->SetXY(($this->GetPageWidth()-105),39);
        //$this->SetFont('Nunito-Regular','',15);
        //$this->Cell(77,7,' '.$this->serial,0,0,'R'); 
        
    }
    function cabezera3()
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
        $this->Cell(98,7,utf8_decode('BLVD GUADIANA #410'),0,0,'R');

        $this->SetXY(($this->GetPageWidth()-105),16);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('C.P. 34139'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),20);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('TELEFONO: 618 1303555'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),24);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode(''),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),28);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('DURANGO, Dgo.'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),34);
        $this->SetFont('Arial','B',16);
        $this->Cell(98,7,utf8_decode($this->quote),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-100),39);
        $this->SetFont('Arial','B',16);
        $this->Cell(67,7,utf8_decode('#'.$this->serial),0,0,'R');
        //$this->SetXY(($this->GetPageWidth()-105),39);
        //$this->SetFont('Nunito-Regular','',15);
        // $this->Cell(77,7,' '.$this->serial,0,0,'R'); 
        
    }
    function cabezera4()
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
        $this->Cell(98,7,utf8_decode('ALUMINIO S/N FIDEICOMISO CIUDAD INDUSTRIAL'),0,0,'R');

        $this->SetXY(($this->GetPageWidth()-105),16);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('C.P. 34229'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),20);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('TELEFONO: (618)814 7148'),0,0,'R');
        
        $this->SetXY(($this->GetPageWidth()-105),24);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('correo@empresa.mx'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),28);
        $this->SetFont('Nunito-Regular','',10);
        $this->Cell(98,7,utf8_decode('DURANGO, Dgo.'),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-105),34);
        $this->SetFont('Arial','B',16);
        $this->Cell(98,7,utf8_decode($this->quote),0,0,'R');
        $this->SetXY(($this->GetPageWidth()-100),39);
        $this->SetFont('Arial','B',16);
        $this->Cell(67,7,utf8_decode('#'.$this->serial),0,0,'R');
        // $this->SetXY(($this->GetPageWidth()-105),39);
        //$this->SetFont('Nunito-Regular','',15);
        // $this->Cell(77,7,' '.$this->serial,0,0,'R'); 
        
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

    /*function Footer()
    {   $this->imgLat();
        $this->SetY(240);
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
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('www.empresa.mx'),0,0,'L');
        $this->Ln();
    }*/

    function Footer1()
    {   $this->imgLat();
        $this->SetY(240);
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
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('www.empresa.mx'),0,0,'L');
        $this->Ln();
    }

    function Footer2()
    {   $this->imgLat();
        $this->SetY(240);
        $this->SetFont('Nunito-Regular','',7);
        $this->setX(10);
        $this->Cell(1, 31, utf8_decode("Transferencia: GUILLERMO LOPEZ DE LARA TINAJERO."));
        $this->setX(10);
        $this->Cell(1, 38, utf8_decode("BANBAJIO"));
        $this->setX(10);
        $this->Cell(1, 45, utf8_decode("CTA. 311512100201"));
        $this->setX(10);
        $this->Cell(1, 52, utf8_decode("CLABE. 030190900025199675."));
        $this->setX(10);
        $this->Ln();
        // this->Cell(1, 52, utf8_decode("correo@empresa.mx"));
        // $this->Ln();
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('www.empresa.mx'),0,0,'L');
        $this->Ln();
    }
    function Footer3()
    {   $this->imgLat();
        $this->SetY(240);
        $this->SetFont('Nunito-Regular','',7);
        $this->setX(10);
        $this->Cell(1, 31, utf8_decode("Transferencia: EMPRESA SA DE CV."));
        $this->setX(10);
        $this->Cell(1, 38, utf8_decode("SANTANDER"));
        $this->setX(10);
        $this->Cell(1, 45, utf8_decode("CTA. 92001263624 SUC. 0335"));
        $this->setX(10);
        $this->Cell(1, 52, utf8_decode("CLABE. 014190920012636244."));
        $this->setX(10);
        $this->Ln();
        $this->Cell(1, 52, utf8_decode("correo@empresa.mx"));
        $this->Ln();
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
        $this->SetTextColor(0);
        $this->Cell(0,0,utf8_decode('www.empresa.mx'),0,0,'L');
        $this->Ln();
    }
    function Footer4()
    {   $this->imgLat();
        $this->SetY(240);
        $this->SetFont('Nunito-Regular','',7);
        $this->setX(10);
        $this->Cell(1, 31, utf8_decode(""));
        $this->setX(10);
        $this->Cell(1, 38, utf8_decode(""));
        $this->Cell(1, 45, utf8_decode(""));
        $this->setX(10);
        $this->Cell(1, 52, utf8_decode(""));
        $this->setX(10);
        $this->setX(10);
        $this->Ln();
        // this->Cell(1, 52, utf8_decode("correo@empresa.mx"));
        // $this->Ln();
        $this->SetFont('Arial','B',15);
        $this->SetXY(($this->GetPageWidth()-135),265);
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
        //135,180,223

        $this->SetFillColor(200,220,240);//Fondo verde de celda
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
class PDFInvoice extends FPDF
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

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo2.png';
        $this->Image($img,15,10,65,0,'PNG');
        $this->SetTextColor(21, 18, 46);
        $this->SetFont('Arial','B',10);
        $this->Cell(0, 10, utf8_decode("SALIDA DE ALMACÉN #$this->invoiceId"), 0, 0, 'R');
        $this->Ln();
        $this->Cell(0, 10, utf8_decode("SUCURSAL $this->branchOffice"), 0, 0, 'R');
        $this->Ln();
        $this->Cell(0, 10, $this->saleDate, 0, 0, 'R');
        $this->Ln();
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

    /* function Row($data)
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
    } */

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

class PDFPayments extends FPDF
{
    function encabezado()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,6,8,45,0,'PNG');
        }
        $this->SetXY(215, 10);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 55, 12);
        $this->SetFont('Nunito-Regular', '', 12);
        $this->SetTextColor(22, 18, 10);
        $this->Cell(0, 0, utf8_decode('EMPRESA SA DE CV'));
        $this->SetFont('Nunito-Regular', '', 12);
        $this->SetXY(($this->GetPageWidth() / 2 - 37.8) , 18);
        $this->Cell(0, 0, 'ESTADO DE CUENTA');

        $header = array(
            'ESTATUS',
            'FECHA',
            'FACTURA',
            'VENCIMIENTO',
            'CLIENTE',
            'MONTO TOTAL',
            'ABONADO',
            'RESTANTE'
        );
        $this->SetXY(5, 35);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.3);
        $this->SetFont('', '', 9);
        // Header
        $x = 143;
        $i = 0;
        $w = array(20,20,20,24,90,30,30,28);
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

    function encabezadov2()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,5,35,0,'PNG');
        }
        $this->SetXY(163, 10);
        $this->SetFont('Nunito-Regular', '', 8);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 50, 12);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetFillColor(30,136,229);
        // $this->SetTextColor(255,255,255);
        $this->Cell(0, 0, 'EMPRESA SA DE CV');
        $this->SetFont('Nunito-Regular', '', 9);
        $this->SetXY(75 , 16);
        $this->Cell(0, 0, 'REPORTE DE COBRANZA DETALLADO');

        $header = array(
            'FECHA FACTURA',
            'VENCIMIENTO',
            'CONCEPTO',
            'DEBE',
            'HABER',
            'SALDO'
        );
        $this->SetXY(5, 25);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 8);
        // Header
        $x = 143;
        $i = 0;
        $w = array(25,25,75,27,27,27);
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
    function encabezadov2_1()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,5,35,0,'PNG');
        }
        $this->SetXY(163, 10);
        $this->SetFont('Nunito-Regular', '', 8);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 50, 12);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetFillColor(30,136,229);
        // $this->SetTextColor(255,255,255);
        $this->Cell(0, 0, 'LOPEZ DE LARA TINAJERO GUILLERMO');
        $this->SetFont('Nunito-Regular', '', 9);
        $this->SetXY(75 , 16);
        $this->Cell(0, 0, 'REPORTE DE COBRANZA DETALLADO');

        $header = array(
            'FECHA FACTURA',
            'VENCIMIENTO',
            'CONCEPTO',
            'DEBE',
            'HABER',
            'SALDO'
        );
        $this->SetXY(5, 25);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 8);
        // Header
        $x = 143;
        $i = 0;
        $w = array(25,25,75,27,27,27);
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
    function encabezadov2_2()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,5,35,0,'PNG');
        }
        $this->SetXY(163, 10);
        $this->SetFont('Nunito-Regular', '', 8);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 50, 12);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetFillColor(30,136,229);
        // $this->SetTextColor(255,255,255);
        $this->Cell(0, 0, 'EMPRESA SA DE CV');
        $this->SetFont('Nunito-Regular', '', 9);
        $this->SetXY(75 , 16);
        $this->Cell(0, 0, 'REPORTE DE COBRANZA DETALLADO');

        $header = array(
            'FECHA FACTURA',
            'VENCIMIENTO',
            'CONCEPTO',
            'DEBE',
            'HABER',
            'SALDO'
        );
        $this->SetXY(5, 25);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 8);
        // Header
        $x = 143;
        $i = 0;
        $w = array(25,25,75,27,27,27);
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
    function encabezadov2_3()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,5,35,0,'PNG');
        }
        $this->SetXY(163, 10);
        $this->SetFont('Nunito-Regular', '', 8);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 50, 12);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->SetFillColor(30,136,229);
        // $this->SetTextColor(255,255,255);
        $this->Cell(0, 0, 'EMPRESA SA DE CV');
        $this->SetFont('Nunito-Regular', '', 9);
        $this->SetXY(75 , 16);
        $this->Cell(0, 0, 'REPORTE DE COBRANZA DETALLADO');

        $header = array(
            'FECHA FACTURA',
            'VENCIMIENTO',
            'CONCEPTO',
            'DEBE',
            'HABER',
            'SALDO'
        );
        $this->SetXY(5, 25);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 8);
        // Header
        $x = 143;
        $i = 0;
        $w = array(25,25,75,27,27,27);
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


    function encabezadov3()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $logo = $image_path . 'logo.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,5,65,0,'PNG');
        }
        $this->SetXY(215, 10);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 15, 12);
        $this->SetFont('Nunito-Regular', '', 18);
        $this->SetTextColor(22, 18, 47);
        $this->Cell(0, 0, 'REBASA');
        $this->SetFont('Nunito-Regular', '', 14);
        $this->SetXY(($this->GetPageWidth() / 2) - 30, 20);
        $this->Cell(0, 0, 'REPORTE DE VENTAS');

        $header = array(
            utf8_decode('REMISIÓN'),
            'ESTATUS',
            'FECHA',
            'FACTURA',
            'VENDEDOR',
            'CLIENTE',
            'PIEZAS',
            'IMPORTE TOTAL'
        );
        $this->SetXY(5, 35);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0);
        $this->SetFont('', '', 9);
        // Header
        $x = 143;
        $i = 0;
        $w = array(15,20,20,35,50,80,25,25);
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

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Nunito-Regular italic 8
        $this->SetFont('Nunito-Regular', '', 8);
        // Page number
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    //Rotar texto
    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    //Multicell
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function SetBorders($b)
    {
        //Set the array of column borders
        $this->border = $b;
    }

    function SetFills($f)
    {
        //Set the array of column borders
        $this->fill = $f;
    }


    function Row($data)
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

    function Rowv2($data)
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
            $f = isset($this->fill[$i]) ? $this->fill[$i] : 0;
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            //Print the text
            $this->MultiCell($w, 5, $data[$i], $b, $a, $f);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function RowFactura($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        $longitud = count($data) - 1;
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            if ($i === $longitud) {
                $a = 'R';
            } else {
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            }
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

class NumeroALetras
{
    private static $UNIDADES = [
        '',
        'UN ',
        'DOS ',
        'TRES ',
        'CUATRO ',
        'CINCO ',
        'SEIS ',
        'SIETE ',
        'OCHO ',
        'NUEVE ',
        'DIEZ ',
        'ONCE ',
        'DOCE ',
        'TRECE ',
        'CATORCE ',
        'QUINCE ',
        'DIECISEIS ',
        'DIECISIETE ',
        'DIECIOCHO ',
        'DIECINUEVE ',
        'VEINTE '
    ];

    private static $DECENAS = [
        'VENTI',
        'TREINTA ',
        'CUARENTA ',
        'CINCUENTA ',
        'SESENTA ',
        'SETENTA ',
        'OCHENTA ',
        'NOVENTA ',
        'CIEN '
    ];

    private static $CENTENAS = [
        'CIENTO ',
        'DOSCIENTOS ',
        'TRESCIENTOS ',
        'CUATROCIENTOS ',
        'QUINIENTOS ',
        'SEISCIENTOS ',
        'SETECIENTOS ',
        'OCHOCIENTOS ',
        'NOVECIENTOS '
    ];

    public static function convertir($number, $moneda = '', $centimos = '', $forzarCentimos = false)
    {
        $converted = '';
        $decimales = '';

        if (($number < 0) || ($number > 999999999)) {
            return 'No es posible convertir el numero a letras';
        }

        $div_decimales = explode('.',$number);

        if(count($div_decimales) > 1){
            $number = $div_decimales[0];
            $decNumberStr = (string) $div_decimales[1];
            if(strlen($decNumberStr) == 2){
                $decNumberStrFill = str_pad($decNumberStr, 9, '0', STR_PAD_LEFT);
                $decCientos = substr($decNumberStrFill, 6);
                $decimales = self::convertGroup($decCientos);
            }
        }
        else if (count($div_decimales) == 1 && $forzarCentimos){
            $decimales = 'CERO ';
        }

        $numberStr = (string) $number;
        $numberStrFill = str_pad($numberStr, 9, '0', STR_PAD_LEFT);
        $millones = substr($numberStrFill, 0, 3);
        $miles = substr($numberStrFill, 3, 3);
        $cientos = substr($numberStrFill, 6);

        if (intval($millones) > 0) {
            if ($millones == '001') {
                $converted .= 'UN MILLON ';
            } else if (intval($millones) > 0) {
                $converted .= sprintf('%sMILLONES ', self::convertGroup($millones));
            }
        }

        if (intval($miles) > 0) {
            if ($miles == '001') {
                $converted .= 'MIL ';
            } else if (intval($miles) > 0) {
                $converted .= sprintf('%sMIL ', self::convertGroup($miles));
            }
        }

        if (intval($cientos) > 0) {
            if ($cientos == '001') {
                $converted .= 'UN ';
            } else if (intval($cientos) > 0) {
                $converted .= sprintf('%s ', self::convertGroup($cientos));
            }
        }

        if(empty($decimales)){
            $valor_convertido = $converted . strtoupper($moneda);
        } else {
            $valor_convertido = $converted . strtoupper($moneda) . ' CON ' . $decimales . ' ' . strtoupper($centimos);
        }

        return $valor_convertido;
    }

    private static function convertGroup($n)
    {
        $output = '';

        if ($n == '100') {
            $output = "CIEN ";
        } else if ($n[0] !== '0') {
            $output = self::$CENTENAS[$n[0] - 1];
        }

        $k = intval(substr($n,1));

        if ($k <= 20) {
            $output .= self::$UNIDADES[$k];
        } else {
            if(($k > 30) && ($n[2] !== '0')) {
                $output .= sprintf('%sY %s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
            } else {
                $output .= sprintf('%s%s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
            }
        }

        return $output;
    }

}
class PDFAuxiliarPayments extends FPDF
{
    function encabezado()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $logo = $image_path . 'logo_name_bn.png';

        if (file_exists($logo)) {
            $this->Image($logo, 5, 5, 75, 0, 'PNG');
        }
        $this->SetXY(215, 10);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 40, 12);
        $this->SetFont('Nunito-Regular', '', 12);
        $this->SetTextColor(22, 18, 47);
        $this->Cell(0, 0, 'FIBRAS RECICLAPET DE MEXICO SA DE CV');
        $this->SetFont('Nunito-Regular', '', 11);
        $this->SetXY(($this->GetPageWidth() / 2) - 20, 18);
        $this->Cell(0, 0, 'HISTORIAL DE COBROS');

        $header = array(
            '# REMISION',
            '# FACTURA',
            'FECHA',
            'CLIENTE',
            'CANTIDAD'
        );
        $this->SetXY(5, 35);

        $this->SetFillColor(30,136,229);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 8);
        // Header
        $x = 143;
        $i = 0;
        $w = array(30, 30, 33, 140, 35);
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

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Nunito-Regular italic 8
        $this->SetFont('Nunito-Regular', '', 8);
        // Page number
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    //Rotar texto
    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    //Multicell
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function SetBorders($b)
    {
        //Set the array of column borders
        $this->border = $b;
    }

    function SetFills($f)
    {
        //Set the array of column borders
        $this->fill = $f;
    }

    function Row($data)
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
            $this->MultiCell($w, 5, $data[$i], $b, $a, 1);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}
//
