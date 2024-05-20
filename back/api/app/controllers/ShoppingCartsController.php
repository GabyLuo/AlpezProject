<?php

use Phalcon\Mvc\Controller;

class ShoppingCartsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    //  inicio cancelar pedido
    public function cancelShopping()
    {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        // echo("<pre>");
        //print_r($request['id']);
        // exit();


        $id = $request['id'];
        $shoppingCart = ShoppingCart::findFirst(intval($id));
        $shoppingCart->setTransaction($tx);
        $shoppingCart->status = 'CANCELADO';
        if ($shoppingCart->update()) {
            $invoiceInBulkDe = Invoices::find("shopping_cart_id = $id");
            foreach ($invoiceInBulkDe as $details) {
                //print_r($details->in_bulk_movement_id);
                $mov = Movements::findFirst($details->in_bulk_movement_id);
                $mov->setTransaction($tx);
                $mov->status = 'CANCELADO';
                if ($mov->update()) {                   
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El movimiento  y remision han sido cancelado.');
                    //$tx->commit();
                } else {
                    $this->content['result'] = false;
                $this->content['error'] = Helpers::getErrors($mov);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar el Movimiento y remision.');
                //$tx->rollback();
                }

            }
            // exit();
            $this->content['result'] = true;
            $this->content['message'] = Message::success('El pedido y remision a sido cancelado.');
            //$tx->commit();
        } else {
            $this->content['result'] = false;
            $this->content['error'] = Helpers::getErrors($shoppingCart);
            $this->content['message'] = Message::error('Ha ocurrido un error al intentar CANCELAR.');
            // $tx->rollback();
        }
        if($this->content['result'] == true){
            $tx->commit();
        }else {
            $tx->rollback();
        }
        $this->response->setJsonContent($this->content);

    }
    // fin cancelar pedido
    public function getShoppingCart ($id = null)
    {
        $validUser = Auth::getUserData($this->config);

        if ($validUser && $validUser->id) {
            if ($this->userHasPermission() && !is_null($id) && is_numeric($id)) {

                $sql = $this->getShoppingCartSQL($id);
                //echo("<pre>");
                //print_r($sql);
                //exit();
                $this->content['shoppingCart'] = $sql;
                $this->content['result'] = true;
            } else {
                $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                $this->content['shoppingCart'] = $shoppingCart;
                $this->content['result'] = true;
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function getRequestedShoppingCarts ()
    {
        if ($this->userHasPermission()) {
            $sql = $this->getRequestedShoppingCartsSQL();
            $this->content['orders'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getApprovedShoppingCarts ()
    {
        if ($this->userHasPermission()) {
            $sql = $this->getApprovedShoppingCartsSQL();
            $this->content['orders'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function updateCart () {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
       /*  echo("<pre>");
        print_r($request);
        exit();*/
        $cartId = $request['shoppingCartId'];
        $shoppingCart = ShoppingCart::findFirst("id = $cartId");
        if ($shoppingCart) {
            $shoppingCart->setTransaction($tx);
            $shoppingCart->customer_id = intval($request['customer']);
            $shoppingCart->branchoffice = intval($request['originbranch']);
            $shoppingCart->user_id = intval($request['seller']);
            $shoppingCart->branchofficedestiny = intval($request['officecustomer']);
            $shoppingCart->storage_id = intval($request['storage_id']);
            $shoppingCart->tax_invoice = intval($request['tax_invoice']);
            $shoppingCart->commercial_terms = $request['commercial_terms'] == null ? null : ($request['commercial_terms']);
            $shoppingCart->validity = $request['validity'] == null ? null : ($request['validity']);
            $shoppingCart->lab = $request['lab'] == null ? null : ($request['lab']);
            $shoppingCart->special_order = $request['special_order'];
            //$shoppingCart->contact_client_id = $request['contact_client_id'] == null ? null : intval($request['contact_client_id']);
            if (is_array($request['contact_client_id'])) {
                if ($request['contact_client_id']['value'] != null && $request['contact_client_id']['label']) {
                    $shoppingCart->contact_client_id = $request['contact_client_id']['value'];
                }
            }else if ($request['contact_client_id'] == null) {
                $shoppingCart->contact_client_id = null;
            } else {
                $shoppingCart->contact_client_id = $request['contact_client_id'];
            }
            if(is_array($request['type_order'])){
                $shoppingCart->type_order = intval($request['type_order']['value']);
            }else {
                $shoppingCart->type_order = intval($request['type_order']); 
            }
            if(is_array($request['loan'])){
                $shoppingCart->loan = $request['loan']['value'];
            }else {
                $shoppingCart->loan = $request['loan']; 
            }
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El cliente ha sido modificado.');
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($shoppingCart);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el cliente.');
                $tx->rollback();
            }
        } else {
            $this->content['message'] = Message::success('Error al editar el pedido.');
        }
        $this->response->setJsonContent($this->content);
    }

    // public function getMail ($cartId, $hash) {
    //     $shoppingCart =  ShoppingCart::findFirst($cartId);
    //     $customer = Customers::findFirst($shoppingCart->customer_id);
    //     $emailHashes = [];

    //     $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
    //     while (strstr($billingEmailHash, '/') || strstr($billingEmailHash, '.')) {
    //         $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
    //     }
    //     array_push($emailHashes, array('email' => 'facturacion@eturelab.com', 'hash' => $billingEmailHash));
    //     if (!is_null($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
    //         $emailHash = password_hash($customer->email, PASSWORD_BCRYPT);
    //         while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //             $emailHash = password_hash($customer->email, PASSWORD_BCRYPT);
    //         }
    //         array_push($emailHashes, array('email' => $customer->email, 'hash' => $emailHash));
    //     }
    //     if (!is_null($customer->email2) && filter_var($customer->email2, FILTER_VALIDATE_EMAIL)) {
    //         $emailHash = password_hash($customer->email2, PASSWORD_BCRYPT);
    //         while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //             $emailHash = password_hash($customer->email2, PASSWORD_BCRYPT);
    //         }
    //         array_push($emailHashes, array('email' => $customer->email2, 'hash' => $emailHash));
    //     }
    //     if (!is_null($customer->email3) && filter_var($customer->email3, FILTER_VALIDATE_EMAIL)) {
    //         $emailHash = password_hash($customer->email3, PASSWORD_BCRYPT);
    //         while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //             $emailHash = password_hash($customer->email3, PASSWORD_BCRYPT);
    //         }
    //         array_push($emailHashes, array('email' => $customer->email3, 'hash' => $emailHash));
    //     }
    //     if (!is_null($customer->email4) && filter_var($customer->email4, FILTER_VALIDATE_EMAIL)) {
    //         $emailHash = password_hash($customer->email4, PASSWORD_BCRYPT);
    //         while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //             $emailHash = password_hash($customer->email4, PASSWORD_BCRYPT);
    //         }
    //         array_push($emailHashes, array('email' => $customer->email4, 'hash' => $emailHash));
    //     }

    //     $mail = '
    //     <!DOCTYPE html>
    //                     <html>
    //                     <head>
    //                         <style>
    //                         #logo-container {
    //                             text-align: center;
    //                         }

    //                         #logo {
    //                             max-width: 300px;
    //                         }

    //                         p {
    //                             text-align: justify;
    //                             color: #00295E;
    //                             font-family: verdana;
    //                             font-size: 15px;
    //                         }
    //                         </style>
    //                     </head>
    //                     <body>
    //                         <div id="logo-container">
    //                             <img id="logo" src="http://api.tf.beta.antfarm.mx/assets/images/logo_name.png" alt="Technofibers">
    //                         </div>
    //                         <p>
    //                             Estimado cliente <strong>'.$customer->tradename.'</strong> Gracias por su compra, le informamos que su pedido ha sido AUTORIZADO!.
    //                             <br>
    //                             <br>
    //                             Adjunto encontrará la orden del pedido con folio: #'.$shoppingCart->id.'.
    //                             <br>
    //                             <br>
    //                             Muchas gracias!!
    //                         </p>
    //                     </body>
    //                 </html>
    //                 ';
    //     return $mail;
    // }
    private function savePdf ($id)
    {
        if (is_numeric($id)) {
            $order = ShoppingCart::findFirst($id);
            if ($order && ($order->status == 'COTIZADO' || $order->status == 'CERRADO')) {
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

    public function getFile ($id) {
        try {
            if ($this->userHasPermission()) {
                $shoppingCart = ShoppingCart::findFirst($id);
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: *");
                readfile($_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/'.$shoppingCart->id);
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
    }
    public function uploadFile ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $shoppingCart = ShoppingCart::findFirst($id);
                $request = $this->request->getPost();
                if ($shoppingCart) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/ocs/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $shoppingCart->id .'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($shoppingCart->oc_document != null && file_exists($upload_dir.$shoppingCart->oc_document)) {
                            @unlink($upload_dir.$shoppingCart->oc_document);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->oc_document = $fileName;
                        if ($shoppingCart->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo Orden de Compra se ha subido exitosamente.');
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
    public function downloadDocumentFileOC ($id,$fileType)
    {
        if (is_numeric($id)) {
            $document = ShoppingCart::findFirst($id);
            if (isset($fileType) && intval($fileType) == 1) {
                if ($document && $document->oc_document) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/ocs/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $extension = explode( '.', $document->oc_document );
                    $fullPath = $upload_dir . $document->id.'.'.$extension[1];
                }
            }
            if (isset($fileType) && intval($fileType) == 2) {
                if ($document && $document->payment_document) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/tickets/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $extension = explode( '.', $document->payment_document );
                    $fullPath = $upload_dir . $document->id.'.'.$extension[1];
                }
            }
            if (isset($fileType) && intval($fileType) == 3) {
                if ($document && $document->citation_document) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/citations/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $extension = explode( '.', $document->citation_document );
                    $fullPath = $upload_dir . $document->id.'.'.$extension[1];
                }
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
    public function uploadFile2 ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $shoppingCart = ShoppingCart::findFirst($id);
                $request = $this->request->getPost();
                if ($shoppingCart) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/tickets/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $shoppingCart->id .'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($shoppingCart->payment_document != null && file_exists($upload_dir.$shoppingCart->payment_document)) {
                            @unlink($upload_dir.$shoppingCart->payment_document);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->payment_document = $fileName;
                        if ($shoppingCart->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo Ticket de Pago se ha subido exitosamente.');
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

    public function uploadFile3 ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $shoppingCart = ShoppingCart::findFirst($id);
                $request = $this->request->getPost();
                if ($shoppingCart) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shopping-carts/citations/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $shoppingCart->id .'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($shoppingCart->citation_document != null && file_exists($upload_dir.$shoppingCart->citation_document)) {
                            @unlink($upload_dir.$shoppingCart->citation_document);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->citation_document = $fileName;
                        if ($shoppingCart->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo Fromato de Cita se ha subido exitosamente.');
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
    public function sendPDF () {
        $msg = null;
        $request = $this->request->getPost();
        $actions = Actions::findFirst(2);
        $cartId = intval($request['cartId']);
        $shoppingCart =  ShoppingCart::findFirst($cartId);
        //$emailWithAttention = isset($request['emailWithAttention']) ? $request['emailWithAttention'] : '';
        if ($shoppingCart) {
            if ($actions->host && $actions->port && $actions->username && $actions->password) {
                $customerId = intval($shoppingCart->customer_id);
                $customer = Customers::findFirst($customerId);
                $getEmails = "SELECT email from sls_customer_contacts where customer_id = $customer->id";
                $querygetEmails = $this->db->query($getEmails)->fetchAll();
                
                
                $arrayemail = [];

                foreach($querygetEmails as $value){
                    array_push($arrayemail, "".$value['email']."");
                }
                


                array_push($arrayemail, "".$customer->email."");
                /* if ($request['emailWithAttention'] != null) {
                    array_push($arrayemail, array($customer->email, $request['emailWithAttention']));
                }else {
                    array_push($arrayemail, array($customer->email));
                } */
                //var_dump($arrayemail[0]);
                if ($customer->email) {
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
                                Estimado Cliente <strong>'.$customer->name.'</strong>.
                                <br>
                                <br>
                                Por medio del presente le informamos que su cotización ha sido generada correctamente, adjunto encontrará el archivo de su cotización. Quedamos a sus ordenes. Saludos del equipo ALPEZ.
                                <br>
                                <br>
                            </p>
                        </body>
                    </html>';
                    $this->savePDFQuotation($cartId);
                    $fileName = __DIR__.'./../../public/assets/orders/';
                    $transport = (new Swift_SmtpTransport($actions->host, $actions->port, $actions->encryption))
                    //$transport = (new Swift_SmtpTransport($actions->host, $actions->port))
                    ->setUsername($actions->username)
                    ->setPassword($actions->password);
                    // Create the Mailer using your created Transport
                    $mailer = new Swift_Mailer($transport);
                    // Create a message
                    $message = (new Swift_Message('Estimado Cliente.'))
                    ->setFrom([$actions->username => 'REBASA'])
                    ->setTo($arrayemail)
                    ->setBody($htmlBody,'text/html')
                    ->attach(Swift_Attachment::fromPath($fileName.'Cotización #'.$shoppingCart->id.'.pdf')->setFilename('Cotización #'.$shoppingCart->id.'.pdf'));
                    // Send the message
                    $mailer->send($message);
                    $msg.= "Correo enviado correctamente al Cliente";
                } else {
                    $msg .= '; No se ha enviado el correo debido a que el Cliente no tiene correo registrado.';
                }
            } else {
                $msg .= '; No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
            }
        }
        $this->content['result'] = true;
        $this->content['message'] = Message::success($msg);
        $this->response->setJsonContent($this->content);
    }
    // public function sendPDF () {
    //     $request = $this->request->getPost();
    //     $cartId = $request['cartId'];
    //     $msg = '';
    //     //----------------------------------//
    //     $actions = Actions::findFirst(1);
    //     $shoppingCart =  ShoppingCart::findFirst($cartId);

    //     if ($actions->host && $actions->encryption && $actions->port && $actions->username && $actions->password) {
    //         $customer = Customers::findFirst($shoppingCart->customer_id);
    //         if (is_null($customer->email) && !filter_var($customer->email, FILTER_VALIDATE_EMAIL) && is_null($customer->email2) && !filter_var($customer->email2, FILTER_VALIDATE_EMAIL) && is_null($customer->email3) && !filter_var($customer->email3, FILTER_VALIDATE_EMAIL) && is_null($customer->email4) && !filter_var($customer->email4, FILTER_VALIDATE_EMAIL)) {
    //             $msg .= 'No se ha enviado el correo debido a que el cliente no tiene correo registrado.';
    //         } else {
    //             $emailHashes = [];
    //             $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
    //             while (strstr($billingEmailHash, '/') || strstr($billingEmailHash, '.')) {
    //                 $billingEmailHash = password_hash('facturacion@eturelab.com', PASSWORD_BCRYPT);
    //             }
    //             array_push($emailHashes, array('email' => 'facturacion@eturelab.com', 'hash' => $billingEmailHash));
    //             if (!is_null($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
    //                 $emailHash = password_hash($customer->email, PASSWORD_BCRYPT);
    //                 while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //                     $emailHash = password_hash($customer->email, PASSWORD_BCRYPT);
    //                 }
    //                 array_push($emailHashes, array('email' => $customer->email, 'hash' => $emailHash));
    //             }
    //             if (!is_null($customer->email2) && filter_var($customer->email2, FILTER_VALIDATE_EMAIL)) {
    //                 $emailHash = password_hash($customer->email2, PASSWORD_BCRYPT);
    //                 while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //                     $emailHash = password_hash($customer->email2, PASSWORD_BCRYPT);
    //                 }
    //                 array_push($emailHashes, array('email' => $customer->email2, 'hash' => $emailHash));
    //             }
    //             if (!is_null($customer->email3) && filter_var($customer->email3, FILTER_VALIDATE_EMAIL)) {
    //                 $emailHash = password_hash($customer->email3, PASSWORD_BCRYPT);
    //                 while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //                     $emailHash = password_hash($customer->email3, PASSWORD_BCRYPT);
    //                 }
    //                 array_push($emailHashes, array('email' => $customer->email3, 'hash' => $emailHash));
    //             }
    //             if (!is_null($customer->email4) && filter_var($customer->email4, FILTER_VALIDATE_EMAIL)) {
    //                 $emailHash = password_hash($customer->email4, PASSWORD_BCRYPT);
    //                 while (strstr($emailHash, '/') || strstr($emailHash, '.')) {
    //                     $emailHash = password_hash($customer->email4, PASSWORD_BCRYPT);
    //                 }
    //                 array_push($emailHashes, array('email' => $customer->email4, 'hash' => $emailHash));
    //             }

    //             foreach ($emailHashes as $emailHash) {
    //                 $hash = $emailHash['hash'];
    //                 $htmlBody = $this->getMail($cartId, $hash);
    //                 $mailer = new Mailer();
    //                 $mailer->htmlBody = $htmlBody;
    //                 $mailer->attachedFile = $this->saveCartPdf($cartId);
    //                 $mailer->host = $actions->host;
    //                 $mailer->encryption = $actions->encryption;
    //                 $mailer->port = $actions->port;
    //                 $mailer->username = $actions->username;
    //                 $mailer->password = $actions->password;
    //                 $mailer->subject = "Pedido #$customer->id";
    //                 $mailer->from = $actions->username;
    //                 $mailer->to = $emailHash['email'];
    //                 $result_message = null;
    //                 try {
    //                     $result_message = $mailer->sendEmail();
    //                 } catch (Throwable $e) {
    //                     $result_message = (object) array(
    //                         'status' => false,
    //                         'message' => $e->getMessage()
    //                     );
    //                 }
    //                 $msg .= $result_message->message;
    //             }
    //         }
    //     } else {
    //         $msg .= 'No se ha enviado el correo debido a que no hay ninguna cuenta de correo configurada.';
    //     }
    //     $this->content['result'] = true;
    //     $this->content['message'] = Message::success($msg);
    //     $this->response->setJsonContent($this->content);
    // }

    public function saveCartPdf ($id)
    {
        if (is_numeric($id)) {
            $shoppingCart = ShoppingCart::findFirst($id);

            if ($shoppingCart) {
                $pdf = $this->generatePdf($id);

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/orders/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Pedido #$shoppingCart->id.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
            }
        }
        return null;
    }

    public function savePDFQuotation ($id)
    {
        if (is_numeric($id)) {
            $shoppingCart = ShoppingCart::findFirst($id);

            if ($shoppingCart) {
                $pdf = $this->quotationNotePDF($id, 'si');

                if (!is_null($pdf)) {
                    $fileName = __DIR__.'/../../public/assets/orders/';
                    if (!is_dir($fileName)) {
                        if (!mkdir($fileName)) {
                            mkdir($fileName, 0777);
                        }
                    }
                    $fileName .= "Cotización #$shoppingCart->id.pdf";
                    $pdf->Output('F', $fileName, true);
                    return $fileName;
                }
            }
        }
        return null;
    }

    public function getPdf ($id)
    {
        if (is_numeric($id)) {
            $invoice = ShoppingCart::findFirst($id);
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

    public function getPdfFromShoppingCarts ($customer,$status,$seller,$date1,$date2, $idrole, $specialorder)
    {
        $pdf = $this->generatePdfFromShoppinCart($customer,$status,$seller,$date1,$date2, $idrole, $specialorder);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Ventas.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="ReporteDeVentas.pdf"');
            return $response;
        }
    }

    public function generatePdf ($id)
    {
        $info = ShoppingCart::findFirst("id = $id");
        //$info = ShoppingCart::findFirst("id = $id");
        if (is_numeric($id)) {
            $shoppingCartDetails = $this->queryBulkForDetailShoppingCart($id);
            $inBulkDetails = $this->queryBulkForDetailShoppingCartinBulk($id);
        }
        if($shoppingCartDetails){
            $customerAddress = '';

            if (isset($shoppingCartDetails[0]['street']) && strlen($shoppingCartDetails[0]['street']) > 0) {
                $customerAddress = $shoppingCartDetails[0]['street'];
                if (isset($shoppingCartDetails[0]['outdoor_number']) && strlen($shoppingCartDetails[0]['outdoor_number']) > 0) {
                    $customerAddress .= ' '.$shoppingCartDetails[0]['outdoor_number'];
                }
                if (isset($shoppingCartDetails[0]['indoor_number']) && strlen($shoppingCartDetails[0]['indoor_number']) > 0) {
                    $customerAddress .= ' '.$shoppingCartDetails[0]['indoor_number'];
                }
            }

            if (isset($shoppingCartDetails[0]['suburb']) && strlen($shoppingCartDetails[0]['suburb']) > 0) {
                if (strlen($customerAddress) > 0) {
                    $customerAddress .= ', ';
                }
                $customerAddress .= $shoppingCartDetails[0]['suburb'];
            }

            if (isset($shoppingCartDetails[0]['municipality']) && strlen($shoppingCartDetails[0]['municipality']) > 0) {
                if (strlen($customerAddress) > 0) {
                    $customerAddress .= ', ';
                }
                $customerAddress .= $shoppingCartDetails[0]['municipality'];
            }

            if (isset($shoppingCartDetails[0]['state']) && strlen($shoppingCartDetails[0]['state']) > 0) {
                if (strlen($customerAddress) > 0) {
                    $customerAddress .= ', ';
                }
                $customerAddress .= $shoppingCartDetails[0]['state'];
            }

            $customerBranchOfficeAddress = '';

            if (isset($shoppingCartDetails[0]['customer_branch_office_street']) && strlen($shoppingCartDetails[0]['customer_branch_office_street']) > 0) {
                $customerBranchOfficeAddress = $shoppingCartDetails[0]['customer_branch_office_street'];
                if (isset($shoppingCartDetails[0]['customer_branch_office_outdoor_number']) && strlen($shoppingCartDetails[0]['customer_branch_office_outdoor_number']) > 0) {
                    $customerBranchOfficeAddress .= ' '.$shoppingCartDetails[0]['customer_branch_office_outdoor_number'];
                }
            }

            if (isset($shoppingCartDetails[0]['customer_branch_office_zip_code']) && strlen($shoppingCartDetails[0]['customer_branch_office_zip_code']) > 0) {
                if (strlen($customerBranchOfficeAddress) > 0) {
                    $customerBranchOfficeAddress .= ', C.P. '.$shoppingCartDetails[0]['customer_branch_office_zip_code'];
                } else {
                    $customerBranchOfficeAddress .= 'C.P. '.$shoppingCartDetails[0]['customer_branch_office_zip_code'];
                }
            }

            if (isset($shoppingCartDetails[0]['customer_branch_office_phone_number']) && strlen($shoppingCartDetails[0]['customer_branch_office_phone_number']) > 0) {
                if (strlen($customerBranchOfficeAddress) > 0) {
                    $customerBranchOfficeAddress .= ', Tel. '.$shoppingCartDetails[0]['customer_branch_office_phone_number'];
                } else{
                    $customerBranchOfficeAddress .= 'Tel. '.$shoppingCartDetails[0]['customer_branch_office_phone_number'];

                }
            }

        }

        $pdf = new PDFShoppingcart('L','mm','Letter');
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->SetInvoiceId($shoppingCartDetails[0]['id']);
        $pdf->SetBranchOffice($shoppingCartDetails[0]['origin_branchoffice']);
        $pdf->SetSaleDate($shoppingCartDetails[0]['sale_date']);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false, 20);
        $pdf->SetFillColor(135, 180, 223);
        $pdf->SetFont('Nunito-Regular','',10);
        $pdf->SetDrawColor(21, 18, 46);
        $pdf->SetLineWidth(0.2);
        $pdf->Ln();
        $pdf->SetWidths(array(260));
        $pdf->SetAligns(array('C'));
        $pdf->SetHeight(8);
        $pdf->SetLineWidth(0.4);
        $pdf->SetFill(array(true));
        $pdf->SetTextColors(array([255,255,255]));
        $pdf->SetFillColor(128,179,240);
        $pdf->Row(array('PEDIDO'));

        $pdf->SetHeight(6);
        $pdf->SetFillColor(200,220,240);
        $pdf->SetLineWidth(0.2);
        $pdf->SetWidths(array(60, 200));
        $pdf->SetAligns(array('R', 'L'));
        $pdf->SetFill(array(true, false));
        $pdf->SetTextColors(array(255, 0));
        $pdf->SetFont('Nunito-Regular','',8);
        $pdf->Row(array(utf8_decode('FECHA:'), utf8_decode($shoppingCartDetails[0]['sale_date'])));
        $pdf->Row(array(utf8_decode('SUCURSAL DE ORIGEN:'), utf8_decode($shoppingCartDetails[0]['origin_branchoffice'])));
        $pdf->Row(array(utf8_decode('NOMBRE DEL VENDEDOR:'), utf8_decode($shoppingCartDetails[0]['agent_name'])));
        $pdf->Row(array(utf8_decode('ESTATUS:'), utf8_decode($shoppingCartDetails[0]['cart_status'])));
        $pdf->Row(array(utf8_decode('CLIENTE:'), utf8_decode($shoppingCartDetails[0]['customer_name'])));
        $pdf->Row(array(utf8_decode('SUCURSAL DEL CLIENTE:'), utf8_decode($shoppingCartDetails[0]['client_branchoffice'])));
        $pdf->Row(array(utf8_decode('PRECIO DE LISTA:'), $shoppingCartDetails[0]['price_list']));
        $pdf->Row(array(utf8_decode('Comentarios:'), $info->comments));


        // $pdf->SetWidths(array(60, 200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('SUCURSAL DE ORIGEN:'), utf8_decode($shoppingCartDetails[0]['origin_branchoffice'])));

        // $pdf->SetWidths(array(60, 200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('NOMBRE DE USUARIO:'), utf8_decode($shoppingCartDetails[0]['agent_name'])));

        // $pdf->SetWidths(array(60, 200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('ESTATUS:'), utf8_decode($shoppingCartDetails[0]['cart_status'])));

        // $pdf->SetWidths(array(60, 200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('CLIENTE:'), utf8_decode($shoppingCartDetails[0]['customer_name'])));

        // $pdf->SetWidths(array(60, 200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('SUCURSAL DEL CLIENTE:'), utf8_decode($shoppingCartDetails[0]['client_branchoffice'])));

        // $pdf->SetWidths(array(60,200));
        // $pdf->SetAligns(array('R', 'L'));
        // $pdf->SetFill(array(true, false));
        // $pdf->SetTextColors(array(255, 0));
        // $pdf->SetFont('Nunito-Regular','',8);
        // $pdf->Row(array(utf8_decode('PRECIO DE LISTA:'), $shoppingCartDetails[0]['price_list']));

        $sb = 0;
        $iv = 0;
        $ttl = 0;

        if($inBulkDetails){
            $pdf->Ln();
            $pdf->SetWidths(array(196));
            $pdf->SetAligns(array('L'));
            $pdf->SetHeight(8);
            // $pdf->SetFill(array(true));
            $pdf->SetDrawEdge(false);
            $pdf->SetFillColor(255);
            $pdf->SetTextColors(array(0));
            $pdf->Row(array('PRODUCTOS'));
            $no = 1;
            $pdf->SetFont('Nunito-Regular','',8);
            $pdf->SetWidths(array(85,40,30,30,15,30,30));
            $pdf->SetAligns(array('C','C','C','C','C','C','C'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(true);
            $pdf->SetFill(array(true,true,true,true,true,true,true));
            $pdf->SetTextColors(array(255,255,255,255,255,255,255));
            $pdf->SetFillColor(128,179,240);
            $pdf->SetLineWidth(0.4);
            $pdf->Row(array('PRODUCTO', 'LINEA ', 'CATEGORIA','CANTIDAD','UNIDAD','UNITARIO', 'IMPORTE'));
            $sutotal_total = 0;
            $iva_total = 0;
            $total_total = 0;
            $cantidad_total =0;
            foreach ($inBulkDetails as $details) {
                $qty = (float)$details['qty'];
                $price_list = (float)$details['price_product'];
                $subtotal = $details['price_product'] * $details['qty'];
                $iva = (float)$subtotal * .16;
                $total = $subtotal + $iva;
                $pdf->SetFont('Nunito-Regular','',8);
                $pdf->SetWidths(array(85,40,30,30,15,30,30));
                $pdf->SetAligns(array('L','L','L','R','R','R','R'));
                $pdf->SetHeight(4);
                $pdf->SetLineWidth(0);
                $pdf->SetFill(array(false,false,false,false,false,false,false));
                $pdf->SetTextColors(array(0,0,0,0,0,0,0));
                $pdf->SetFillColor(255);
                $pdf->Row(array(utf8_decode($details['product']), utf8_decode($details['line']),utf8_decode($details['category']), number_format($qty, 2, '.', ','),$details['unit_code'], '$ '.number_format($price_list, 2, '.', ',').' MXN', '$ '.number_format($subtotal, 2, '.', ',').' MXN'));
                $no += 1;
                $cantidad_total += $details['qty'];
                $sutotal_total += $subtotal;
                $iva_total += $iva;
                $total_total += $total;
            }
            $sb += $sutotal_total;
            $iv += $iva_total;
            $ttl += $total_total;
        }

        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();

        $pdf->SetFont('Nunito-Regular','',8);
        
        $pdf->SetWidths(array(200,30,30));
        $pdf->SetAligns(array('L','R','R'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->SetFill(array(false,true,true));
        $pdf->SetTextColors(array(255,255,255));
        $pdf->SetFillColor(128,179,240);
        $pdf->Row(array('', 'SUBTOTAL:', '$ '.number_format($sb, 2, '.', ',').' MXN'));

        $pdf->SetFont('Nunito-Regular','',8);
        $pdf->SetWidths(array(200,30,30));
        $pdf->SetAligns(array('L','R','R'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->SetFill(array(false,true,true));
        $pdf->SetTextColors(array(255,255,255));
        $pdf->SetFillColor(128,179,240);
        $pdf->Row(array('', 'IVA:', '$ '.number_format($iv, 2, '.', ',').' MXN'));

        $pdf->SetFont('Nunito-Regular','',8);
        $pdf->SetWidths(array(200,30,30));
        $pdf->SetAligns(array('L','R','R'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->SetFill(array(false,true,true));
        $pdf->SetTextColors(array(255,255,255));
        $pdf->SetFillColor(128,179,240);
        $pdf->Row(array('', 'TOTAL:', '$ '.number_format($ttl, 2, '.', ',').' MXN'));

        $pdf->SetTitle('Pedido #'.$shoppingCartDetails[0]['id'],true);

        return $pdf;
    }

    public function generatePdfFromShoppinCart ($customer,$status,$seller,$date1,$date2, $idrole, $specialorder)
    {
        $rolesusers = "SELECT id from sys_users where role_id = 1";
        $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
        
        $sizeuserrolesspadmin = count($rolesuserquery);
        $auxiforroles = 1;
        
        //-----------------
        $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $idrole";

        
        $searchrole = "SELECT * from sys_users where id = $idrole";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 20 || $value["role_id"] == 29) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where = "WHERE sc.id > 0 and sc.created_by = $idrole and (";
                $or = " or";
                foreach($rolesuserquery as $valuerole){
                    if ($auxiforroles == $sizeuserrolesspadmin) {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $idrole))";
                    } else {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $idrole) $or ";
                    }
                    $auxiforroles += 1;
                    
                }
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        }
        $validUser = Auth::getUserInfo($this->config);
        $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";

        /* $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $idrole";

        
        $searchrole = "SELECT * from sys_user_roles where user_id = $idrole";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 20 || $value["role_id"] == 4) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where .= "WHERE sc.id > 0 and (sc.created_by = $idrole or bo.id = $branchname) ";
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        } */

        $y = date('Y');
        if ($specialorder == 'TODOS') {

        }else if ($specialorder == 0) {
            $where .= " AND (sc.special_order = $specialorder or sc.special_order is null) ";
        }else if ($specialorder == 1){
            $where .= " AND sc.special_order = $specialorder ";
        }
        if ($customer == 'TODOS') {} else if($customer == 'null'){}else {$where .= " AND sc.customer_id = $customer";}
        if ($seller == 'TODOS') {} else if($seller == 'null'){}else {$where .= " AND sc.user_id = $seller";}
        if ($status == 'TODOS') {} else if($status == 'null'){}else {$where .= " AND sc.status = '$status'";}
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
        $where .= " AND sc.created BETWEEN '".$dateIni."' AND '".$dateFin."'";
        $sql = "SELECT sc.id, cbo.name as clientbranchoffice ,c.id as id_client,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date,sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,
                STRING_AGG(CAST(si.id AS varchar), ',') as invoices,count(si.id) as contador,bo.name as branchofficeorigin,
                (select COALESCE((SELECT sum(sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as inbulk,
                (select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as montoinbulk,
                sc.special_order as special_order
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
                LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                {$where}
                GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, cbo.name, bo.name
                ORDER BY id DESC;";
        $data = $this->db->query($sql);
        $data = $data->fetchAll();
        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $pdf = new PDF();
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(.3);
        $pdf->encabezado();
        $pdf->SetTextColor(0);
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

        $pdf->SetWidths(array(15,25,30,20,60,60,15,40));
        $pdf->SetAligns(array('C','C','C','C','L','L','R','R'));
        $pdf->SetDrawColor(0, 0, 0);

        $i = 1;
        $totalesPzas = 0;
        $totalesMonto = 0;
        foreach ($data as $row) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
                $pdf->AddPage('L', 'Letter');
                $pdf->encabezado();
                $pdf->SetXY(0, 40);
                $pdf->SetFont('', '', 7);
            }
            $pdf->SetX(5);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetDrawColor(0, 0, 0);
            $pzas = $row['inbulk'] ;
            $monto = $row['montoinbulk'];
            $date = $row['date'];
            $pdf->Row(array($row['id'], $row['status'], $date,$row['special_order'] == 1 ? 'ESPECIAL' : 'NORMAL',utf8_decode($row['user_name']),utf8_decode($row['customer_name']),number_format(floatval($pzas), 2, '.', ','),'$ '.number_format(floatval($monto), 2, '.', ',')));
            $i++;
            $totalesPzas += $pzas;
            $totalesMonto += $monto;
        }
        $pdf->SetXY(145, $pdf->getY());
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Nunito-Regular', '', 7);
        $pdf->Cell(70, 5, 'TOTAL :',0,'','R');
        $pdf->SetXY(215, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 7);
        $pdf->Cell(15, 5,'',1,'','R');
        $pdf->SetXY(230, $pdf->getY());
        $pdf->SetFont('Nunito-Regular', '', 7);
        $pdf->Cell(40, 5, '$'.number_format(floatval($totalesMonto), 2, '.', ','),1,'','R');
        
        // $pdf->SetXY(145, $pdf->getY()+5);
        // $pdf->SetDrawColor(0, 0, 0);
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(70, 5, 'IVA :',0,'','R');
        // $pdf->SetXY(215, $pdf->getY());
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(15, 5,'',1,'','R');
        // $pdf->SetXY(230, $pdf->getY());
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(40, 5, '$'.number_format(floatval($totalesMonto)*0.16, 2, '.', ','),1,'','R');

        // $pdf->SetXY(145, $pdf->getY()+5);
        // $pdf->SetDrawColor(0, 0, 0);
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(70, 5, 'TOTAL :',0,'','R');
        // $pdf->SetXY(215, $pdf->getY());
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(15, 5, number_format(floatval($totalesPzas), 2, '.', ',').'',1,'','R');
        // $pdf->SetXY(230, $pdf->getY());
        // $pdf->SetFont('Nunito-Regular', '', 7);
        // $pdf->Cell(40, 5, '$'.number_format(floatval($totalesMonto)*1.16, 2, '.', ','),1,'','R');


        $pdf->SetTitle(utf8_decode('Reporte de Pedidos'));
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', 'reporte_ventas.pdf', true);

        return $pdf;
    }

    public function getCSVFromShoppingCarts ($customer,$status,$seller,$date1,$date2, $idrole, $specialorder)
    {

        $rolesusers = "SELECT id from sys_users where role_id = 1";
        $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
        
        $sizeuserrolesspadmin = count($rolesuserquery);
        $auxiforroles = 1;
        
        //-----------------
        $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $idrole";

        
        $searchrole = "SELECT * from sys_users where id = $idrole";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 20 || $value["role_id"] == 29) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where = "WHERE sc.id > 0 and sc.created_by = $idrole and (";
                $or = " or";
                foreach($rolesuserquery as $valuerole){
                    if ($auxiforroles == $sizeuserrolesspadmin) {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $idrole))";
                    } else {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $idrole) $or ";
                    }
                    $auxiforroles += 1;
                    
                }
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        }
        $validUser = Auth::getUserInfo($this->config);
        $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";

        /* $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $idrole";

        
        $searchrole = "SELECT * from sys_user_roles where user_id = $idrole";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 20 || $value["role_id"] == 4) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where .= "WHERE sc.id > 0 and (sc.created_by = $idrole or bo.id = $branchname) ";
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        } */


        $y = date('Y');
        if ($specialorder == 'TODOS') {

        }else if ($specialorder == 0) {
            $where .= " AND (sc.special_order = $specialorder or sc.special_order is null) ";
        }else if ($specialorder == 1){
            $where .= " AND sc.special_order = $specialorder ";
        }
        if ($customer == 'TODOS') {} else if($customer == 'null'){}else {$where .= " AND sc.customer_id = $customer";}
        if ($seller == 'TODOS') {} else if($seller == 'null'){}else {$where .= " AND sc.user_id = $seller";}
        if ($status == 'TODOS') {} else if($status == 'null'){}else {$where .= " AND sc.status = '$status'";}
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
        $where .= " AND sc.created BETWEEN '".$dateIni."' AND '".$dateFin."'";
        $sql = "SELECT sc.id, cbo.name as clientbranchoffice ,c.id as id_client,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date,sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,
                STRING_AGG(CAST(si.id AS varchar), ',') as invoices,count(si.id) as contador,bo.name as branchofficeorigin,
                (select COALESCE((SELECT sum(scb.qty) from sls_shopping_cart_bale_details AS scb where scb.shopping_cart_id = sc.id), 0)) as bale,
                (select COALESCE((SELECT sum(scb.price_product * scb.qty) from sls_shopping_cart_bale_details AS scb where scb.shopping_cart_id = sc.id), 0)) as montobale,
                (select COALESCE((SELECT sum(sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as inbulk,
                (select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as montoinbulk,
                (select COALESCE((SELECT sum(scl.qty) from sls_shopping_cart_laminate_details AS scl where scl.shopping_cart_id = sc.id), 0)) as laminate,
                (select COALESCE((SELECT sum(scl.price_product * scl.qty) from sls_shopping_cart_laminate_details AS scl where scl.shopping_cart_id = sc.id), 0)) as montolaminate,
                sc.special_order as special_order
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
                LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                {$where}
                GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, cbo.name, bo.name
                ORDER BY id DESC;";
        $data = $this->db->query($sql);
        $data = $data->fetchAll();
        $fechaImpresion = date("d/m/Y");
        $fechaIni = date("d/m/Y", strtotime($dateIni));
        $fechaFin = date("d/m/Y", strtotime($dateFin));

        $content = $this->content;
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('PEDIDO', 'ESTATUS', 'FECHA','PEDIDO', 'SUC. ORIGEN', 'REMISION', 'CLIENTE', 'SUC. CLIENTE', 'PIEZAS', 'TOTAL (IVA)','AGENTE'), ',');

        if (count($data) > 0) {
            $totales = 0;
            $totalesPzas = 0;
            $iva = 0;
            $total = 0;
            foreach ($data as $d) {
                $pzas = $d['inbulk'];
                $total = $d['montoinbulk'];
                // $iva = (float)$monto * .16;
                // $total = $iva + $monto;
                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $d['id'],
                        $d['status'],
                        $d['date'],
                        $d['special_order'] == 1 ? 'ESPECIAL' : 'NORMAL',
                        $d['branchofficeorigin'],
                        $d['invoices'],
                        $d['customer_name'],
                        $d['clientbranchoffice'],
                        $pzas,
                        $total,
                        $d['user_name']
                    ], ',');
                }
                $totales += $total;
                $totalesPzas += $pzas;
            }
            fputcsv($fp, array('', '', '','', '','', '','Total General', $totalesPzas, $totales, ''), ',');

        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->setHeader("Access-Control-Allow-Headers","*");
        $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Reporte_Ventas' . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getAllShoppingCarts ()
    {
        if ($this->userHasPermission()) {
            $sql = $this->getAllShoppingCartsSQL();
            $this->content['shoppingCarts'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function changeStatus (){
        $request = $this->request->getPost();
        $id = intval($request['cartId']);
        $tx = $this->transactions->get();
        $shoppingCart = ShoppingCart::findFirst($id);

        if($shoppingCart){
            $shoppingCart->setTransaction($tx);
            $shoppingCart->status = 'NUEVO';
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El pedido ha sido modificado.');
                $tx->commit();
            } else{
                $this->content['error'] = Helpers::getErrors($shoppingCart);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar editar el pedido.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function changeComments (){
        $request = $this->request->getPost();
        $id = intval($request['cartId']);
        $tx = $this->transactions->get();
        $shoppingCart = ShoppingCart::findFirst($id);

        if($shoppingCart){
            $shoppingCart->setTransaction($tx);
            $shoppingCart->comments = $request['comment'];
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El pedido ha sido modificado.');
                $tx->commit();
            } else{
                $this->content['error'] = Helpers::getErrors($shoppingCart);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar editar el pedido.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function memoryPrices () {
        $request = $this->request->getPost();
        $category = $request['categoryId'];
        $customer = $request['customerId'];
        $product = $request['productId'];
        $prices = $this->memoryPricesSQL($category,$customer,$product);
        if($prices) {
            $this->content['result'] = true;
            $this->content['price'] = $price = $prices[0]['price_product'];
        } else {
            $this->content['result'] = false;
            $this->content['price'] = 0;
            }
        $this->response->setJsonContent($this->content);
    }

    public function getGrid ()
    {
        $request = $this->request->getPost();

        if ($this->userHasPermission()) {
            $sql = $this->getGridSQL($request['customer'],$request['seller'],$request['status'],$request['saleDatev1'],$request['saleDatev2'],$request['sellerId']);
            $this->content['shoppingCarts'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQLbyPagination($request['customer'],$request['seller'],$request['status'],$request['saleDatev1'],$request['saleDatev2'],$request['sellerId'],$request);
            $this->content['shoppingCarts'] = $response['data'];
            $this->content['productsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {   
        try {
            if (false) {
                $validUser = Auth::getUserData($this->config);
                $customerUser = CustomerUsers::findFirst("user_id = $validUser->id");
                if ($validUser && $validUser->id && $customerUser && $customerUser->customer_id) {
                    $tx = $this->transactions->get();
                    $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                    if (!$shoppingCart) {
                        $shoppingCart = new ShoppingCart();
                        $shoppingCart->setTransaction($tx);
                        $shoppingCart->user_id = $validUser->id;
                        $shoppingCart->customer_id = $customerUser->customer_id;
                        if ($shoppingCart->create()) {
                            $this->content['result'] = true;
                            $this->content['shoppingCart'] = $shoppingCart;
                            $this->content['message'] = Message::success('Carrito de compras registrado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shoppingCart);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el carrito de compras.');
                        }
                    }
                }
            } elseif ($this->userHasPermission()) {
                $request = $this->request->getPost();
                $dateShopping = null;
                    /* if ($request['inmediatedate'] == null) {
                        $dateShopping = null;
                    }else {
                        $dateShopping = date("Y-m-d", strtotime($request['inmediatedate']));
                    } */
                if (isset($request['customerId'])) {
                        $tx = $this->transactions->get();
                        $validUser = Auth::getUserData($this->config);
                        $shoppingCart = new ShoppingCart();
                        $shoppingCart->setTransaction($tx);
                        $data = $this->verifySalesBySeller(intval($request['sellerId']));
                        $cansale = $data['cansale'];
                        if ($cansale) {
                        $shoppingCart->user_id = $request['sellerId'];
                        $shoppingCart->customer_id = $request['customerId']['value'];
                        $shoppingCart->branchoffice = intval($request['branchOfficeId']);
                        $shoppingCart->branchofficedestiny = intval($request['station']);
                        $shoppingCart->supplier_id = intval($request['supplier']);
                        // $shoppingCart->tax_invoice = $request['taxInvoice'] ? intval($request['taxInvoice']) : 0;
                        $shoppingCart->storage_id = $request['storage_id'];
                        $shoppingCart->inmediatedate = $dateShopping;
                        // $shoppingCart->contact_client_id = $request['contact_client_id'] == null ? null : intval($request['contact_client_id']);
                        $shoppingCart->commercial_terms = $request['commercial_terms'] == null ? null : ($request['commercial_terms']);
                        $shoppingCart->validity = $request['validity'] == null ? null : ($request['validity']);
                        $shoppingCart->lab = $request['lab'] == null ? null : ($request['lab']);
                        $shoppingCart->special_order = $request['special_order'];
                        $shoppingCart->order_date = $request['orderDate'];
                        $shoppingCart->pledge_date = $request['pledgeDate'];
                        if(is_array($request['type_order'])){
                            $shoppingCart->type_order = $request['type_order']['value'];
                        }else {
                            $shoppingCart->type_order = $request['type_order']; 
                        }
                        if(is_array($request['loan'])){
                            $shoppingCart->loan = $request['loan']['value'];
                        }else {
                            $shoppingCart->loan = $request['loan']; 
                        }
                        
                        if ($shoppingCart->create()) {
                            $customer = Customers::findFirst(intval($request['customerId']['value']));
                            $customer->setTransaction($tx);
                            $customer->email = $request['email'];

                            if ($customer->update()) {

                            }else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::success('No se puedo actualizar el email.');
                            }
                            $this->content['result'] = true;
                            $this->content['shoppingCart'] = $shoppingCart;
                            $this->content['message'] = Message::success('Carrito de compras registrado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shoppingCart);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el carrito de compras.');
                        }
                    } else {
                        $seller = $data['nameseller'];
                        $numsale = $data['numsale'];
                        $this->content['error'] = Helpers::getErrors($shoppingCart);
                        $this->content['message'] = Message::error('Vendedor '.$seller.' tiene el pedido '.$numsale.' en estatus NUEVO, favor de solicitarlo.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha recibido el cliente.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function verifySalesBySeller ($sellerId) {
        $canDoASale = true;
        $result = array();
        $sql = "SELECT DISTINCT sr.id AS value, su.nickname AS label
                FROM sys_users AS su
                JOIN sys_roles AS sr ON su.role_id = sr.id
                WHERE su.id  = $sellerId and su.role_id in (1,4,20,29,27,28,22)
                ORDER BY label ASC;";
        $isSeller = $this->db->query($sql)->fetchAll();
        //print_r($sql);
            //exit();
        $roles = [];
        foreach ($isSeller as $role) {
            array_push($roles, intval($role['value']));
        }
        if (!in_array(1,$roles)  && !in_array(3,$roles) && !in_array(25,$roles) && !in_array(4,$roles) && !in_array(29,$roles) && !in_array(27,$roles) && !in_array(28,$roles) && !in_array(22,$roles)) {
            $sql = "SELECT id from sls_shopping_cart
            where user_id = $sellerId";
            $data = $this->db->query($sql)->fetchAll();
            if ($data) {
                $canDoASale = true;
                $result['numsale'] = $data[0]['id'];
                $result['nameseller'] = $isSeller[0]['label'];
            }else {
                $canDoASale = true;
            }
        }
        $result['cansale'] = $canDoASale;
        return $result;
    }
    public function getImpuestos ($id) {
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
    public function getGridPaymentsSQL ($id,$date1,$date2,$status,$type) {
        $y = date('Y');
        $where = "";
        $order = "";

        if (count($status) > 0) {
            $numbers = [];
                foreach ($status as $detail){
                    $numbers[] =  $detail;
                }
            $numbers = implode(',',$numbers);
            $where = " WHERE i.status = 'ENVIADO' AND i.status_payment in ($numbers) ";
        } else {
            $where = "WHERE (i.status = 'ENVIADO' OR i.status = 'PAGADO' OR i.status_timbrado = 1) ";
        }
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
        $where .= " AND i.created BETWEEN '".$dateIni."' AND '".$dateFin."' ";

        $sql = "SELECT count(i.id) AS count
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.agent_id
                LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
                LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
                LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
                LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
                LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                {$where}";
        $invoicesCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT i.id, i.status_payment,
        (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
        (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal,
        TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'dd/mm/yyyy') AS expired_date,i.shopping_cart_id, to_char(i.sale_date,'DD/MM/YYYY') AS sale_date, c.credit_limit
        ,CASE when to_char(NOW(), 'yyyy-mm-dd') <= TO_CHAR((CAST(sale_date AS DATE) + CAST(CONCAT(case  when c.credit_days is null OR c.term = 'CONTADO' then 0 else c.credit_days end,' days') as INTERVAL)) :: DATE, 'yyyy-mm-dd') then true else false END as canbuy,c.term
        FROM sls_invoices AS i
        LEFT JOIN sys_users AS a ON a.id = i.agent_id
        LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = i.customer_branch_office_id
        LEFT JOIN sls_customers AS c ON c.id = cbo.customer_id
        LEFT JOIN wms_drivers AS d ON d.id = i.driver_id
        LEFT JOIN wms_movements AS ibm ON ibm.id = i.in_bulk_movement_id
        LEFT JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
        LEFT JOIN wms_branch_offices AS ibb ON ibb.id = ibs.branch_office_id
                {$where}
                GROUP BY c.credit_days, c.term, i.sale_date, i.id,c.credit_limit,c.term";
        $data = $this->db->query($sql)->fetchAll();
       foreach ($data as $key => $d){
           $id = $d['id'];
           $totales = $this->getImpuestos($id);

           $resta = $totales - $d['abonado'];

           $data[$key]['cantidad_total'] = $totales;
           $data[$key]['cantidad_restante'] = $resta;
       }
        return $data;
    }
    private function verifyCreditLimitForCustomer ($id) {
    $restante = null;
    if (isset($id) && $id != null) {
        $restante = $this->getGridPaymentsSQL($id,'','',[],null,null,null);
    }
    return $restante;
    }
    private  function verifyExpiredInvoicesForCustomer () {

    }
    public function cancelarPedido ($id) {
        try {
            $tx = $this->transactions->get();
            $shoppingCart = ShoppingCart::findFirst($id);
            $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND (status = 'SOLICITADO' OR status = 'NUEVO' or status = 'AUTORIZADO')");
            if ($shoppingCart && $shoppingCart->status == 'AUTORIZADO' || $shoppingCart->status == 'SOLICITADO' || $shoppingCart->status == 'NUEVO' || $shoppingCart->status == 'COTIZADO') {
                $shoppingCart->setTransaction($tx);
                $shoppingCart->status = 'CANCELADO';
                if ($shoppingCart->update()) {
                    if($bulkDetails){
                        foreach ($bulkDetails as $detail) {
                            $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                            $tx = $this->transactions->get();
                            $bD = ShoppingCartInBulkDetails::findFirst("id = $idb AND (status = 'SOLICITADO' OR status = 'NUEVO' or status = 'AUTORIZADO')");
                            $bD->setTransaction($tx);
                            $bD->status = 'CANCELADO';
                            if ($bD->update()) {}
                        }
                    }
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El pedido ha sido cancelado exitosamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($shoppingCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar el pedido.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha encontrado el pedido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);

    } 
    public function applyDiscount ($id, $discount) {
        $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'SOLICITADO'");
        if ($bulkDetails) {
            foreach ($bulkDetails as $detail) {
                $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                $tx = $this->transactions->get();
                $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'SOLICITADO'");
                $bD->setTransaction($tx);
                // $bD->status = 'AUTORIZADO';
                $changePrice = floatval($bD->price_product) - (floatval($bD->price_product) * intval($discount)) / 100;
                $bD->price_product = $changePrice;
                if ($bD->update()) {}
            }
        }
    }
    public function request ($id = null)
    {
        try {
            if ($this->userHasPermission()) {
                if (!is_null($id) && is_numeric($id)) {
                    $shoppingCart = ShoppingCart::findFirst($id);
                    if ($shoppingCart && $shoppingCart->status == 'NUEVO') {
                        $shoppingCartBaleDetails = ShoppingCartBaleDetails::find("shopping_cart_id = $shoppingCart->id");
                        $shoppingCartInBulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id");
                        $shoppingCartLaminateDetails = ShoppingCartLaminateDetails::find("shopping_cart_id = $shoppingCart->id");
                        if (count($shoppingCartBaleDetails) > 0 || count($shoppingCartInBulkDetails) > 0 || count($shoppingCartLaminateDetails) > 0) {
                            $tx = $this->transactions->get();
                            $shoppingCart->setTransaction($tx);
                            $request = $this->request->getPut();

                            $customer = Customers::findFirst($shoppingCart->customer_id);
                            if ($customer->term == 'CREDITO') {
                                if (floatval($request['total_cost']) > floatval($customer->credit_limit)) {
                                    $shoppingCart->status = 'AUTORIZADO';
                                    // $shoppingCart->status = 'COTIZADO'; cambio   para slatar un paso de nuevo a autorizadp   
                                    $this->applyDiscount($id, $request['discount']);
                                    $this->content['limit_message'] = Message::error('El pedido #'.$shoppingCart->id.' del cliente '.$customer->name.' supera el límite de crédito');
                                    $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                } else {
                                    $restante = $this->verifyCreditLimitForCustomer($shoppingCart->customer_id);
                                    if (count($restante) > 0 && $restante != null){
                                        $totalcost = $restante[0]['cantidad_restante'] + floatval($request['total_cost']);
                                        $fechaNormal= date("Y-m-d");
                                        if ($restante[0]['term'] == 'CREDITO'){
                                            // Se validan dos cosas
                                            if ($totalcost > $restante[0]['credit_limit']) {
                                                // Se autoriza automaticamente
                                                $shoppingCart->status = 'COTIZADO';
                                                // $shoppingCart->status = 'COTIZADO'; cambio para slatar un paso de nuevo a autorizadp         
                                                $this->applyDiscount($id,$request['discount']);
                                                $this->content['limit_message'] = Message::error('El pedido #'.$shoppingCart->id.' del cliente '.$customer->name.' supera el límite de crédito');
                                                $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                            } else if (!$restante[0]['canbuy']) {
                                                $shoppingCart->status = 'COTIZADO';
                                                // $shoppingCart->status = 'COTIZADO'; cambio para slatar un paso de nuevo a autorizadp    
                                                $this->applyDiscount($id,$request['discount']);
                                                $this->content['expired_message'] = Message::error('Tiene Facturas Pendientes De Pagar');
                                                $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                            } else {
                                                $shoppingCart->status = 'AUTORIZADO';
                                                $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'COTIZADO'");
                                                if ($bulkDetails) {
                                                    foreach ($bulkDetails as $detail) {
                                                        $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                                                        $tx = $this->transactions->get();
                                                        $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'COTIZADO'");
                                                        $bD->setTransaction($tx);
                                                        $bD->status = 'AUTORIZADO';
                                                        // Change the price with a discount applied
                                                        $changePrice = floatval($bd->price_product) - (floatval($bd->price_product) * intval($request['discount'])) / 100;
                                                        $bD->price_product = $changePrice;
                                                        if ($bD->update()) {}
                                                    }
                                                }
                                                $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                                            }
                                        }
                                    } else {
                                        $shoppingCart->status = 'AUTORIZADO';
                                        $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'COTIZADO'");
                                        if ($bulkDetails) {
                                            foreach ($bulkDetails as $detail) {
                                                $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                                                $tx = $this->transactions->get();
                                                $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'COTIZADO'");
                                                $bD->setTransaction($tx);
                                                $bD->status = 'AUTORIZADO';
                                                $changePrice = floatval($bd->price_product) - (floatval($bd->price_product) * intval($request['discount'])) / 100;
                                                $bD->price_product = $changePrice;                                                if ($bD->update()) {}
                                            }
                                        }
                                        $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                                    }
                                }
                            } else {
                                $shoppingCart->status = 'COTIZADO';
                                // COTIZADO
                                $this->applyDiscount($id, $request['discount']);
                                $this->content['message'] = Message::success('El pedido ha sido Cotizado exitosamente.');
                            }
                            $shoppingCart->payment_method = $request['payment_method'] ? $request['payment_method'] : null;
                            $shoppingCart->payment_date = $request['payment_date'] ? $request['payment_date'] : null;
                            $shoppingCart->payment_reference = $request['payment_reference'] ? $request['payment_reference'] : null;
                            // $shoppingCart->oc_date = $request['oc_date'] ? $request['oc_date'] : null;
                            $shoppingCart->oc_reference = $request['oc_reference'] ? $request['oc_reference'] : null;
                            $shoppingCart->oc_term = $request['oc_term'] ? $request['oc_term'] : null;
                            $shoppingCart->applied_discount = $request['discount'] ? intval($request['discount']) : 0;
                            if ($shoppingCart->update()) {
                                $this->content['result'] = true;
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($shoppingCart);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar Cotizar el pedido.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El pedido no cuenta con detalles registrados.');
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function getDataDocument ($id){
        if ($this->userHasPermission()) {
            $sql = "SELECT * from sys_documents where id = $id";
            $query = $this->db->query($sql)->fetchAll();
            $this->content['datadocument'] = $query;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function editReeference ($id){
        $request = $this->request->getPut();
        $tx = $this->transactions->get();
        $shoppingCart = ShoppingCart::findFirst($id);

        if($shoppingCart){
            $shoppingCart->setTransaction($tx);
            $shoppingCart->oc_reference = $request['oc_referenceshp'];
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('No. referencia ha sido modificado correctamente.');
                $tx->commit();
            } else{
                $this->content['error'] = Helpers::getErrors($shoppingCart);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar la referencia.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function addCommentsToCart (){
        $request = $this->request->getPost();
        $id = intval($request['cartId']);
        $comments = $request['cartComments'];
        $tx = $this->transactions->get();
        $shoppingCart = ShoppingCart::findFirst($id);

        if($shoppingCart){
            $shoppingCart->setTransaction($tx);
            $shoppingCart->comments = $comments;
            if ($shoppingCart->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El comentario ha sido agregado exitosamente.');
                $tx->commit();
            } else{
                $this->content['error'] = Helpers::getErrors($shoppingCart);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar comentario.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function approve ($id)
    {
        try {
            $tx = $this->transactions->get();
            $shoppingCart = ShoppingCart::findFirst($id);
            $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'SOLICITADO'");
            if ($shoppingCart && $shoppingCart->status == 'COTIZADO') {
                $shoppingCart->setTransaction($tx);
                $shoppingCart->status = 'AUTORIZADO';
                if ($shoppingCart->update()) {
                    if($bulkDetails){
                        foreach ($bulkDetails as $detail) {
                            $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                            $tx = $this->transactions->get();
                            $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'SOLICITADO'");
                            $bD->setTransaction($tx);
                            $bD->status = 'AUTORIZADO';
                            if ($bD->update()) {}
                        }
                    }
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($shoppingCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar autorizar el pedido.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha encontrado el pedido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function makeParcialidad ($createdby,$shoppingCart,$priceproduct,$product_id,$parcialidad,$sum,$id,$invoice_id) {
    }
    public function getFilterExistences ($storageId, $productId) {
        
        $content = $this->content;
        $mistock = [];
        if ($this->userHasPermission()) {
            //var_dump($branchOfficeId);
            $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
            FROM wms_movement_details AS md
            JOIN wms_movements AS m ON m.movement_id = md.movement_id
            JOIN wms_storages AS s ON s.id = m.storage_id 
            JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
            JOIN wms_products AS p ON p.id = md.product_id
            JOIN wms_lines AS l ON l.id = p.line_id
            JOIN wms_categories AS c ON c.id = l.category_id
            JOIN sys_users AS u ON u.id = m.created_by
            WHERE m.status = 'EJECUTADO' ";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";

            if (!is_null($storageId) && is_numeric($storageId)) {
                $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
            }
            if (!is_null($productId)) {
                $sql .= " AND md.product_id in ($productId)";
            }

            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
                    FROM wms_movement_details AS md
                    JOIN wms_movements AS m ON m.id = md.movement_id
                    JOIN wms_storages AS s ON s.id = m.storage_id 
                    JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                    JOIN wms_products AS p ON p.id = md.product_id
                    JOIN wms_lines AS l ON l.id = p.line_id
                    JOIN wms_categories AS c ON c.id = l.category_id
                    JOIN sys_users AS u ON u.id = m.created_by
                    WHERE m.status = 'EJECUTADO'";
                    if (!is_null($storageId) && is_numeric($storageId)) {
                        $sql .= " AND m.storage_id = $storageId";
                    }
                    
                        $sql .= " AND md.product_id in ($productId)";
                    

            $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
            // print($sql);
            $data = $this->db->query($sql)->fetchAll();

            $movements = $data;
            $products = [];
        $stock = [];
        foreach ($movements as $movement) {
            if (!in_array($movement['product_id'], $products)) {
                $productStock = 0;
                foreach ($movements as $secondMovement) {
                    if ($movement['product_id'] == $secondMovement['product_id']) {
                        if ($secondMovement['movement_type'] == 1) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 2) {
                            $productStock -= $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 3){
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4){
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5){
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('qty' => $productStock, 'product_id' => $movement['product_id']));
            }
        }
            $mistock = $stock;
            /* $content['stock'] = $stock;
            $content['result'] = true; */
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        return $mistock;
    }
    public function generateInvoice ($id)
    {
        set_time_limit(0);
        if ($this->userHasPermission() && !is_null($id) && is_numeric($id)) {
            try {
                $request = $this->request->getPut();
                // echo "<pre>";
                 // print_r($request);
                 // exit();
                $validUser = Auth::getUserData($this->config);
                $storage = [];
                if (isset($request['saleDate']) && isset($request['customerBranchOfficeId']) && is_numeric($request['customerBranchOfficeId'])) {
                    $shoppingCart = ShoppingCart::findFirst($id);
                    if ($shoppingCart) {
                        $shoppingCartInBulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND status = 'AUTORIZADO' order by id asc");

                        //AQUI CONSULTA EL ALMACEN
                        $storage = $this->getStoragesbyShoppingCart($id);
                         $movements = new MovementsController();
                        if (count($shoppingCartInBulkDetails) > 0) {
                            $tx = $this->transactions->get();
                            $someError = false;
                            if (count($shoppingCartInBulkDetails) > 0) {
                                $commit = false;
                                // Este foreach crea un movimiento nueo de salida por cada producto del pedido
                                //for ($i=0; $i < count($shoppingCartInBulkDetails); $i++) {
                                    $inBulkMovement = new Movements();
                                    $inBulkMovement->folio = 0;
                                    $inBulkMovement->storage_id = $storage;
                                    $inBulkMovement->type_id = 2;
                                    $inBulkMovement->status = 'NUEVO';
                                    $customerBranchOffice = CustomerBranchOffices::findFirst($request['customerBranchOfficeId']);
                                    if ($inBulkMovement->create()) {
                                        // $someError = true;
                                        if ($customerBranchOffice) {
                                            $customer = Customers::findFirst("id = $customerBranchOffice->customer_id AND active");
                                            if ($customer) {
                                                // Debo crear una remision por cada producto del pedido
                                                // foreach ($shoppingCartInBulkDetails as $value) {
                                                    $saleDate = date("Y-m-d", strtotime($request['saleDate']));
                                                    $invoice = Invoices::findFirst("shopping_cart_id = $id and status_timbrado = 0 and status = 'REMISIONADO'");
                                                    $invoicecreate = true;
                                                    if (!$invoice) {
                                                        $invoice = new Invoices();
                                                        $invoice->setTransaction($tx);
                                                    $invoice->sale_date =  $saleDate;
                                                    $invoice->agent_id = $validUser->id;
                                                    // $invoice->branch_office_id = 1;
                                                    $invoice->customer_branch_office_id = $request['customerBranchOfficeId'];
                                                    //$invoice->driver_id = $request['driverId']?$request['driverId']: null;
                                                    $invoice->shopping_cart_id = $shoppingCart->id;
                                                    $invoice->seller_id = $shoppingCart->user_id; // Id del usuario que creo la venta
                                                    $invoice->in_bulk_movement_id = $inBulkMovement->id;
                                                    if (isset($request['comments']) && $request['comments'] && strlen($request['comments']) > 0) {
                                                        $invoice->comments = strtoupper($request['comments']);
                                                    }
                                                    $invoicecreate = $invoice->create();
                                                    }
                                                    
                                                    if ($invoicecreate) {
                                                        
                                                        $correctDetails = true;
                                                        if ($shoppingCart->update()) {
                                                            $inBulkDetails = [];
                                                            $parciales = [];
                                                            $totales = [];
                                                            // In bulk details
                                                            if (count($shoppingCartInBulkDetails) > 0) {
                                                                $inBulkProducts =[];
                                                                if($shoppingCartInBulkDetails){
                                                                    foreach ($shoppingCartInBulkDetails as $detail){
                                                                        if(!isset($inBulkProducts[$detail->product_id]) ){
                                                                            $aux = $movements->generateStorageInventoryv3(null,$storage,null,null,$detail->product_id,null,null,null);
                                                                            $inBulkProducts[$aux['data'][0]['product_id']] = $aux['data'][0];
                                                                        }
                                                                    }
                                                                }
                                                                if($inBulkProducts) {
                                                                    for ($i=0; $i < count($shoppingCartInBulkDetails); $i++) {
                                                                        if ($inBulkProducts[$shoppingCartInBulkDetails[$i]->product_id]['stock'] >= $shoppingCartInBulkDetails[$i]->qty) {
                                                                            $inBulkProducts[$shoppingCartInBulkDetails[$i]->product_id]['stock'] -= $shoppingCartInBulkDetails[$i]->qty;
                                                                            $shoppingCartInBulkDetail = (array) $shoppingCartInBulkDetails[$i];
                                                                            $shoppingCartInBulkDetail['stock'] = true;
                                                                            array_push($inBulkDetails, $shoppingCartInBulkDetail);
                                                                        }
                                                                    }
                                                                    foreach ($inBulkDetails as $detail) {
                                                                        $invoiceDetail = new InvoiceInBulkDetails();
                                                                        $invoiceDetail->setTransaction($tx);
                                                                        $invoiceDetail->invoice_id = $invoice->id;
                                                                        $invoiceDetail->product_id = $detail['product_id'];
                                                                        $invoiceDetail->ieps = $detail['ieps'];
                                                                        $invoiceDetail->qty = $detail['qty'];
                                                                        if ($detail['price_product'] && is_numeric($detail['price_product'])) {
                                                                            $invoiceDetail->unit_price = $detail['price_product'];
                                                                        }
                                                                        else {
                                                                            $invoiceDetail->unit_price = 0;
                                                                        }
                                                                        if ($invoiceDetail->create()) {
                                                                            $cnd = false;
                                                                            // Los productos cambian a estatus REMISIONADO
                                                                            $id = intval($detail['id']);
                                                                            $shoppingCartInBulkDetail = ShoppingCartInBulkDetails::findFirst($id);
                                                                            $shoppingCartInBulkDetail->setTransaction($tx);
                                                                            $shoppingCartInBulkDetail->status = 'REMISIONADO';
                                                                            $shoppingCartInBulkDetail->invoice_id = $invoice->id;
                                                                            if (!$shoppingCartInBulkDetail->update()) {
                                                                                $cnd = true;
                                                                                $correctDetails = false;
                                                                            }
                                                                        } else {
                                                                            $correctDetails = false;
                                                                        }
                                                                        if(isset($cnd) === true){
                                                                            // ENTRA a cambiar los postergados a autorizados
                                                                            $changeinBulk = $this->updateDetailsWhenPostergadoChanges($detail['shopping_cart_id']);
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ((count($inBulkDetails) > 0)) {
                                                                if (count($inBulkDetails) == count($shoppingCartInBulkDetails)) {
                                                                    $shoppingCart->setTransaction($tx);
                                                                    if (isset($parcialidad)){
                                                                        if(intval($parcialidad) > 0){
                                                                            $shoppingCart->status = 'AUTORIZADO';
                                                                        } else {
                                                                            $condicion_inbulk = 0;
                                                                            $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
                                                                            if($scibd){
                                                                                $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                                                            }
                                                                            if($condicion_inbulk > 0){
                                                                                $shoppingCart->status = 'AUTORIZADO';
                                                                            } else {
                                                                                $shoppingCart->status = 'REMISIONADO';
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $condicion_inbulk = 0;
                                                                        $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
            
                                                                        if($scibd){
                                                                            $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                                                        }
            
                                                                        if($condicion_inbulk > 0){
                                                                            $shoppingCart->status = 'AUTORIZADO';
                                                                        } else {
                                                                            $shoppingCart->status = 'REMISIONADO';
                                                                        }
                                                                    }
                                                                    if ($shoppingCart->update()) {
                                                                        $this->content['result'] = true;
                                                                        $this->content['invoice'] = $invoice;
                                                                        $this->content['status'] = 'REMISIONADO';
                                                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                                                        $commit = true;
                                                                        // $tx->commit();
                                                                    } else {
                                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos1.');
                                                                        $commit = false;
                                                                        // $tx->rollback();
                                                                    }
                                                                }
                                                            }
                                                            // Debo de hacer la actualizacion del pedido una sola vez
                                                            else {
                                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos3.');
                                                                $tx->rollback();
                                                            }
                                                        } else {
                                                            $this->content['error'] = Helpers::getErrors($shoppingCart);
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos4.');
                                                            $tx->rollback();
                                                        }
                                                        $this->content['invoice'] = $invoice;
                                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($invoice);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos5.');
                                                        $tx->rollback();
                                                    }


                                                // }
                                            } else {
                                                $this->content['message'] = Message::error('No se ha encontrado el cliente seleccionado.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se ha encontrado la sucursal de cliente seleccionada.');
                                            $tx->rollback();
                                        }
                                    }
                                //}
                                if ($correctDetails && (count($inBulkDetails) > 0)) {
                                    // Si es 1 quiere decir que si remiosiono productos del pedido
                                    if (count($inBulkDetails) == 1) {
                                        $shoppingCart->setTransaction($tx);
                                            $condicion_inbulk = 0;
                                            $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
                                            if($scibd){
                                                $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                            }
                                            // Que condicion_inbulk sea mayor que 0 quiere decir que hay pedidos postergados
                                            if($condicion_inbulk > 0){
                                                $shoppingCart->status = 'PARCIAL';
                                            } else {
                                                $shoppingCart->status = 'REMISIONADO';
                                            }
                                            /* echo("<pre>");
                                            print_r($request);
                                            exit(); */
                                            if(isset($request['oc_reference'])){
                                                $shoppingCart->oc_reference = $request['oc_reference'] ? $request['oc_reference'] : null;
                                            }
                                             
                                        if ($shoppingCart->update()) {
                                            $this->content['result'] = true;
                                            $this->content['invoice'] = $invoice;
                                            $this->content['status'] = 'REMISIONADO';
                                            $commit = true;
                                            // $tx->commit();
                                        } else {
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos .');
                                            $commit = false;
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['result'] = true;
                                        $this->content['invoice'] = $invoice;
                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                        $commit = true;
                                        //$tx->commit();
                                    }
                                }

                                if($commit){
                                    $tx->commit();
                                }
                            }
                            if ($someError) {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos 1.');
                            } else {

                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el carrito de compras.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido los parámetros necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function updateDetailsWhenPostergadoChanges ($id) {
        $ibCD = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'POSTERGADO'");
        $data = false;
        $datainBulk = $ibCD->toArray();
        if(isset($datainBulk)) {
            foreach ($datainBulk as $d) {
                $tx = $this->transactions->get();
                $inBulkCartDetails = ShoppingCartInBulkDetails::findFirst($d['id']);
                $inBulkCartDetails->setTransaction($tx);
                $inBulkCartDetails->status = 'AUTORIZADO';
                if ($inBulkCartDetails->update()) {
                    $data = true;
                }
            }
        }
        return $data;
    }

    /*public function getStoragesbyShoppingCart ($id) {
        $oSC = ShoppingCart::find("id = $id");
        $office = $oSC->toArray();
        $oId = $office[0]['branchoffice'];
        $array = [];
        $sSC = Storages::find("branch_office_id = $oId AND (storage_type_id = 13 or storage_type_id = 14) ORDER BY storage_type_id ASC");
        $storage = $sSC->toArray();
        $storage_bulk = $storage[1]['id'];
        array_push($array,$storage[0]['id']);

        return $array;
    }*/
    // Aqui esta el harcodeo de los almacenes
    public function getStoragesbyShoppingCart ($id) {
        $oSC = ShoppingCart::find("id = $id");
        $office = $oSC->toArray();
        /*$oId = $office[0]['branchoffice'];
        $array = [];
        $sSC = Storages::find("branch_office_id = $oId");
        $storage = $sSC->toArray();
        $storage_bulk = $storage[1]['id'];
        array_push($array,$storage[0]['id']);*/

        return $office[0]['storage_id'];
    }

    public function delete ()
    {
        try {
            $validUser = Auth::getUserData($this->config);
            if ($validUser && $validUser->id) {
                $shoppingCart = ShoppingCart::findFirst("user_id = $validUser->id AND status = 'NUEVO'");
                if ($shoppingCart) {
                    $tx = $this->transactions->get();
                    $shoppingCartBaleDetails = ShoppingCartBaleDetails::find("shopping_cart_id = $shoppingCart->id");
                    $shoppingCartInBulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id");
                    $shoppingCartLaminateDetails = ShoppingCartLaminateDetails::find("shopping_cart_id = $shoppingCart->id");
                    $deleteDetailsError = false;
                    foreach ($shoppingCartBaleDetails as $detail) {
                        $detail->setTransaction($tx);
                        if (!$detail->delete()) {
                            $deleteDetailsError = true;
                        }
                    }
                    foreach ($shoppingCartInBulkDetails as $detail) {
                        $detail->setTransaction($tx);
                        if (!$detail->delete()) {
                            $deleteDetailsError = true;
                        }
                    }
                    foreach ($shoppingCartLaminateDetails as $detail) {
                        $detail->setTransaction($tx);
                        if (!$detail->delete()) {
                            $deleteDetailsError = true;
                        }
                    }
                    if ($deleteDetailsError) {
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el carrito de compras.');
                        $tx->rollback();
                    } else {
                        $shoppingCart->setTransaction($tx);
                        if ($shoppingCart->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El carrito de compras ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el carrito de compras.');
                            $tx->rollback();
                        }
                    }
                }
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
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 4 OR role_id = 20 OR role_id = 23 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 28 OR role_id = 17)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userIsCustomer ()
    {
        // $validUser = Auth::getUserData($this->config);
        // if ($validUser && $validUser->id) {
        //     $sql = "SELECT id FROM sys_user_roles WHERE (role_id = 3) AND user_id = $validUser->id LIMIT 1;";
        //     $permission = $this->db->query($sql)->fetch();
        //     if ($permission) {
        //         return true;
        //     }
        // }
        return false;
    }

    /////////// ZONA DE CONSULTAS DE GENERATE INVOICE
    public function queryBulkForGenerateInvoice ($storage, $products){
        $sql = "SELECT s2.bale_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock
                FROM (SELECT s1.bale_id, s1.product_id, SUM(s1.qty) AS stock
                    FROM (SELECT md.bale_id, md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                            FROM wms_movement_details AS md
                            INNER JOIN wms_movements AS m ON m.id = md.movement_id
                            WHERE m.status = 1 AND md.bale_id IS NOT NULL AND m.storage_id = $storage AND md.product_id in ($products) 
                            ORDER BY m.date ASC) AS s1
                    GROUP BY s1.bale_id, s1.product_id) AS s2
                INNER JOIN wms_products AS p ON p.id = s2.product_id
                INNER JOIN wms_lines AS l ON l.id = p.line_id
                INNER JOIN wms_categories AS c ON c.id = l.category_id
                WHERE s2.stock > 0 AND l.category_id = 6
            ORDER BY s2.bale_id ASC;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function generateKardex ($storageId, $products)
    {
        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id AND md.product_id in ($products)
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' ";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 6 END";
        }

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id AND md.product_id in ($products)
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id 
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";

        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        // if (!is_null($productId) && is_numeric($productId)) {
        //     $sql .= " AND md.product_id = $productId";
        // }
        $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function queryinBulkForGenerateInvoice ($storageId, $products){
        $movements = $this->generateKardex($storageId, $products);
        $products = [];
        $stock = [];
        foreach ($movements as $movement) {
            if (!in_array($movement['product_id'], $products)) {
                $productStock = 0;
                foreach ($movements as $secondMovement) {
                    if ($movement['product_id'] == $secondMovement['product_id']) {
                        if ($secondMovement['movement_type'] == 1) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 2) {
                            $productStock -= $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 3){
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4){
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5){
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'],'product_status' => $movement['status_product'], 'qty' => $productStock,'storage_name' => $movement['storage_name'],'product_old_code' => $movement['old_code']));
            }
        }
        return $stock;
    }
    // public function queryinBulkForGenerateInvoice ($storage, $products){
    //     $sql = "SELECT s1.product_id, CONCAT(c.code,'-',l.code,'-',p.name) AS product_name, s1.qty
    //             FROM (SELECT md.product_id, SUM(md.qty) AS qty
    //                 FROM (SELECT md.product_id, CASE WHEN m.type_id = 2 THEN md.qty * -1 ELSE md.qty END AS qty
    //                         FROM wms_movement_details AS md
    //                         INNER JOIN wms_movements AS m ON m.id = md.movement_id AND md.product_id in ($products)
    //                         WHERE m.status = 'EJECUTADO' AND m.storage_id = $storage) AS md
    //                 GROUP BY md.product_id) AS s1
    //             INNER JOIN wms_products AS p ON p.id = s1.product_id
    //             INNER JOIN wms_lines AS l ON l.id = p.line_id
    //             INNER JOIN wms_categories AS c ON c.id = l.category_id
    //             WHERE s1.qty > 0
    //             ORDER BY product_name ASC;";
    //     $data = $this->db->query($sql)->fetchAll();

    //     return $data;
    // }

    public function queryLaminateForGenerateInvoice ($storage, $products){
        $sql = "SELECT s1.product_id, s1.product_name, s1.line_id, s1.line_code, s1.line, s1.category_id, s1.category_code, s1.category, SUM(s1.qty) AS qty
                FROM (
                    SELECT md.id, md.product_id, p.name AS product_name, p.line_id, l.code AS line_code, l.name AS line, l.category_id, c.code AS category_code, c.name AS category, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty, m.date
                    FROM wms_movement_details AS md
                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                    INNER JOIN wms_products AS p ON p.id = md.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    WHERE c.id = 5 AND m.status = 1 AND p.active AND m.storage_id = $storage AND p.id in ($products)
                    ORDER BY date ASC
                ) AS s1
                GROUP BY product_id, product_name, s1.line_id, s1.line_code, s1.line, s1.category_id, s1.category_code, s1.category
                ORDER BY product_name ASC;";

        $data = $this->db->query($sql)->fetchAll();

        return $data;
    }

    ////////// CONSULTA PARA PDF, CUANDO SE MANDAN LOS CORREOS

    public function queryBulkForDetailShoppingCart ($id) {
        $sql = "SELECT sc.id,TO_CHAR(sc.created, 'dd/mm/yyyy') AS sale_date, bo.name as origin_branchoffice, cbo.name as client_branchoffice, c.name as customer_name,
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

    public function queryBulkForDetailShoppingCartBale ($id) {
        $sql = "SELECT sscbd.status, sscbd.qty ,sscbd.price_product, wp.name as product, wl.name as line, wc.name as category
                FROM sls_shopping_cart_bale_details AS sscbd
                LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
                LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
                LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                WHERE sscbd.shopping_cart_id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function queryBulkForDetailShoppingCartinBulk ($id) {
        $sql = "SELECT sscbd.status, sscbd.qty ,sscbd.price_product, wp.name as product, wl.name as line, wc.name as category,wu.name as unit_name,wu.code as unit_code
                FROM sls_shopping_cart_in_bulk_details AS sscbd
                LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
                LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
                LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                LEFT JOIN wms_units AS wu ON wu.id = wp.unit_id
                WHERE sscbd.shopping_cart_id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function queryBulkForDetailShoppingCartLaminate ($id) {
        $sql = "SELECT sscbd.status, sscbd.qty ,sscbd.price_product, wp.name as product, wl.name as line, wc.name as category
                FROM sls_shopping_cart_laminate_details AS sscbd
                LEFT JOIN wms_products AS wp ON wp.id = sscbd.product_id
                LEFT JOIN wms_lines AS wl ON wl.id = wp.line_id
                LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                WHERE sscbd.shopping_cart_id = $id;";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getDataOfOrderShoppingcart ($id, $request) {
        try {
            if ($this->userHasPermission()) {
                if ($request == 'No') {
                    $document = Documents::findFirst($id);
                    $this->content['datadocument'] = $document;
                }
                if ($request == 'Si') {
                    $sql = "SELECT sls_shopping_cart.document_id from sls_invoices
                    inner join sls_shopping_cart on sls_shopping_cart.id = sls_invoices.shopping_cart_id
                    where sls_invoices.id = $id";
                    $data = $this->db->query($sql)->fetchAll();
                    $document = Documents::findFirst(intval($data[0]['document_id']));
                    $this->content['datadocument'] = $document;
                }
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
        $this->response->setJsonContent($this->content);
    }
    // ZONA DE CONSULTAS

    public function getShoppingCartSQL ($id) {
        $sql = "SELECT sc.applied_discount,case when c.discount is null then 0 else c.discount end as authorized_discount,sc.oc_document,sc.payment_document,sc.citation_document, sc.oc_term,sc.payment_method,TO_CHAR(sc.payment_date :: DATE, 'dd/mm/yyyy') as payment_date,sc.payment_reference,TO_CHAR(sc.oc_date :: DATE, 'dd/mm/yyyy') as oc_date,sc.oc_reference,c.term,sc.id, bo.id as idBo, bo.name as origen, sc.branchofficedestiny ,TO_CHAR(sc.created, 'DD/MM/YYYY HH24:MI') AS created,sc.comments, sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,STRING_AGG(CAST(si.id AS varchar), ',') as invoices, old_folio,commercial_terms,validity,lab,loan,
        wms_storages.name as name_storage, wms_storages.id as id_storage, sc.tax_invoice as tax_invoice,
        sc.type_order as type_order,
        sc.contact_client_id,
		sls_customer_contacts.name as name_contact,
        sls_customer_contacts.email as email_contact,
		case  when (sc.type_order = 1) then 'MOSTRADOR'
		when (sc.type_order = 2) then 'CONSIGNACIÓN'
		when (sc.type_order = 3) then 'MAYOREO' end as name_type_pay,
        sc.document_id as document_id,
        sc.special_order as special_order
        FROM sls_shopping_cart AS sc
        LEFT JOIN sys_users AS u ON u.id = sc.user_id
        LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
        LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
        LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
        left JOIN sls_customer_contacts on sls_customer_contacts.id = sc.contact_client_id
        left JOIN wms_storages on wms_storages.id = sc.storage_id
        WHERE sc.id = $id
        GROUP BY sc.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, bo.id, old_folio,c.term,authorized_discount,sc.applied_discount,loan, name_storage, id_storage, tax_invoice, sls_customer_contacts.name,sls_customer_contacts.email
        ORDER BY id DESC;";
        // print_r($sql);
        // exit();
        $data = $this->db->query($sql)->fetch();
        return $data;
    }

    public function getRequestedShoppingCartsSQL () {
        $sql = "SELECT sc.id, sc.comments, sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status
                FROM sls_shopping_cart AS sc
                INNER JOIN sys_users AS u ON u.id = sc.user_id
                INNER JOIN sls_customers AS c ON c.id = sc.customer_id
                WHERE sc.status in ('SOLICITADO');";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getApprovedShoppingCartsSQL () {
        $sql = "SELECT sc.id, sc.comments, sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status
                    FROM sls_shopping_cart AS sc
                    INNER JOIN sys_users AS u ON u.id = sc.user_id
                    INNER JOIN sls_customers AS c ON c.id = sc.customer_id
                    WHERE sc.status = 'AUTORIZADO';";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getAllShoppingCartsSQL () {
        $sql = "SELECT sc.id, cbo.name as clientbranchoffice ,c.id as id_client,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date,sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,
                    STRING_AGG(CAST(si.id AS varchar), ',') as invoices, count(si.id) as contador
                    FROM sls_shopping_cart AS sc
                    LEFT JOIN sys_users AS u ON u.id = sc.user_id
                    LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                    LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
                    LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                    GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, cbo.name
                    ORDER BY id DESC;";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $inv) {
//            var_dump($data[$key]['invoices'] != null);
            if($data[$key]['invoices'] != null){
                if($data[$key]['contador'] > 1){
                    $invoices = $data[$key]['invoices'];
                    $data[$key]['array_invoices'] = explode(',',$invoices);
                }else{
                    $data[$key]['array_invoices'] = [$data[$key]['invoices']];
                }
            }else{
                $data[$key]['array_invoices'] = [];
            }
        }
        return $data;
    }

    public function memoryPricesSQL ($category,$customer,$product) {
        $join = '';
        $price = 0;

        // if(intval($category) == 6) {
        //     $join .= 'INNER JOIN sls_shopping_cart_bale_details AS cd ON cd.shopping_cart_id = sc.id';
        // } else if (intval($category) == 13) {
        //     $join .= 'INNER JOIN sls_shopping_cart_in_bulk_details AS cd ON cd.shopping_cart_id = sc.id';
        // } else if (intval($category) == 5) {
        //     $join .= 'INNER JOIN sls_shopping_cart_laminate_details AS cd ON cd.shopping_cart_id = sc.id';
        // }

        $sql = "SELECT cd.price_product
                FROM sls_shopping_cart AS sc
                INNER JOIN sls_shopping_cart_in_bulk_details AS cd
                ON cd.shopping_cart_id = sc.id
                WHERE sc.customer_id = {$customer} AND cd.product_id = {$product} and cd.price_product != 0
                ORDER BY cd.created DESC LIMIT 1;";
        // print_r($sql);
        // exit();
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function getGridSQLbyPagination ($customer,$seller,$status,$saleDatev1,$saleDatev2,$sellerId,$request) {
        // print_r($request);
        // exit();
        $special_order = $request['filter_special_order'];
        $rolesusers = "SELECT id from sys_users where role_id = 1";
        $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
        
        $sizeuserrolesspadmin = count($rolesuserquery);
        $auxiforroles = 1;
        
        //-----------------
        $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $sellerId";

        
        $searchrole = "SELECT * from sys_users where id = $sellerId";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 29) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where = "WHERE sc.id > 0 and sc.created_by = $sellerId and (";
                $or = " or";
                foreach($rolesuserquery as $valuerole){
                    if ($auxiforroles == $sizeuserrolesspadmin) {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $sellerId))";
                    } else {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $sellerId) $or ";
                    }
                    $auxiforroles += 1;
                    
                }
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        }
        $validUser = Auth::getUserInfo($this->config);
        $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";
        if ($sellerId !== null) {
        $sql = "SELECT role_id
        FROM sys_users
        WHERE id = $sellerId;";
        $currentRoles = $this->db->query($sql)->fetchAll();
        $roles = [];
        foreach ($currentRoles as $role) {
            array_push($roles, intval($role['role_id']));
        }
        }
        $y = date("Y");
        if ($special_order == 'TODOS') {

        }else if ($special_order == 0) {
            $where .= " AND (sc.special_order = $special_order or sc.special_order is null) ";
        }else if ($special_order == 1){
            $where .= " AND sc.special_order = $special_order ";
        }
        if ($customer == 'TODOS') {} else if($customer == ''){}else {$where .= " AND sc.customer_id = $customer";}

        if ($seller == 'TODOS') {} else if($seller == ''){}else {$where .= " AND sc.user_id = $seller";}

        if ($status == 'TODOS' && !in_array(2,$roles)) {} else if($status == ''){
        } else if ($status == 'TODOS' && in_array(2,$roles)) {
            $where .= " AND sc.status != 'NUEVO' AND sc.status != 'SOLICITADO' AND sc.status != 'CANCELADO'";
        } else if (in_array(2,$roles) && ($status != 'NUEVO' && $status != 'SOLICITADO' && $status != 'CANCELADO')) {
            $where .= " AND sc.status = '$status'";
        } else if (in_array(2,$roles)) {
           $where .= " AND sc.status != 'NUEVO' AND sc.status != 'SOLICITADO' AND sc.status != 'CANCELADO'";

        } else {
            $where .= " AND sc.status = '$status'";
        }
        
        if ($saleDatev1 == '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($saleDatev1.' 00:00:00.000000'));
        }
        if ($saleDatev2 == '') {
            $dateFin = date("Y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($saleDatev2.' 23:59:59.000000'));
        }
        $where .= " AND sc.created BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( sc.id::text ILIKE '%".$filter."%' OR sc.old_folio ILIKE '%".$filter."%' OR sc.created::text ILIKE '%".$filter."%' OR u.nickname::text ILIKE '%".$filter."%' OR c.name::text ILIKE '%".$filter."%' OR sc.status::text ILIKE '%".$filter."%' OR bo.name::text ILIKE '%".$filter."%')";
            // print_r($where);
            // exit();
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if($pagination['sortBy'] == 'id'){
                $sortBy .= " ORDER BY sc.id";
            }
            if($pagination['sortBy'] == 'old_folio'){
                $sortBy .= " ORDER BY old_folio" ;
            }
            if($pagination['sortBy'] == 'status'){
                $sortBy .= " ORDER BY sc.status" ;
            }
            if($pagination['sortBy'] == 'date'){
                $sortBy .= " ORDER BY date" ;
            }
            if($pagination['sortBy'] == 'branchofficeorigin'){
                $sortBy .= " ORDER BY branchofficeorigin" ;
            }
            if($pagination['sortBy'] == 'invoices'){
                $sortBy .= " ORDER BY invoices" ;
            }
            if($pagination['sortBy'] == 'customer_name'){
                $sortBy .= " ORDER BY c.name" ;
            }
            if($pagination['sortBy'] == 'total'){
                $sortBy .= " ORDER BY montoinbulk" ;
            }
            if($pagination['sortBy'] == 'user_name'){
                $sortBy .= " ORDER BY u.nickname" ;
            }
            if($pagination['sortBy'] != 'id' && $pagination['sortBy'] != 'old_folio' && $pagination['sortBy'] != 'status' && $pagination['sortBy'] != 'date' && $pagination['sortBy'] != 'branchofficeorigin' && $pagination['sortBy'] != 'invoices' && $pagination['sortBy'] != 'customer_name' && $pagination['sortBy'] != 'total' && $pagination['sortBy'] != 'user_name'){
                $sortBy .= " ORDER BY sc.id";
            }

        } else {
            $sortBy .= " ORDER BY sc.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sqla = "SELECT count(sc.id) as count
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                {$where}  
            ";

          // print_r($sqla);
          // exit();  
        $productsCount = $this->db->query($sqla)->fetchAll();

        $sql = "SELECT sc.branchofficedestiny,sc.id,sc.old_folio ,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date, u.nickname AS user_name, c.name AS customer_name, sc.status, case when sc.status = 'NUEVO' then 1 when sc.status = 'SOLICITADO' then 2 when sc.status = 'AUTORIZADO' then 3  when sc.status = 'REMISIONADO' or sc.status = 'PARCIAL' then 4  when sc.status = 'ENTREGADO' then 5 else 0 end rater,
                STRING_AGG(distinct CAST(si.id AS varchar), ',') as invoices,count(DISTINCT si.id) as contador,bo.name as branchofficeorigin,
                (select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as montoinbulk,
                sc.contact_client_id,c.id as id_client, c.email,
                sc.special_order as special_order
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                {$where}  
                GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, bo.name,old_folio,rater {$sortBy} {$desc} {$offset} {$limit}";
         // print_r($sql);
         // exit();
        $data = $this->db->query($sql)->fetchAll();
        
        foreach ($data as $key => $inv) {
            if($data[$key]['invoices'] != null){
                if($data[$key]['contador'] > 1){
                    $invoices = $data[$key]['invoices'];
                    $data[$key]['array_invoices'] = explode(',',$invoices);
                }else{
                    $data[$key]['array_invoices'] = [$data[$key]['invoices']];
                }
            }else{
                $data[$key]['array_invoices'] = [];
            }
        }
        $response = array('data' => $data, 'rowCounts' => $productsCount[0]['count']);
        return $response;
    }

    public function getGridSQL ($customer,$seller,$status,$saleDatev1,$saleDatev2,$sellerId) {

        $rolesusers = "SELECT id from sys_users where role_id = 1";
        $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
        
        $sizeuserrolesspadmin = count($rolesuserquery);
        $auxiforroles = 1;
        
        //-----------------
        $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $sellerId";

        
        $searchrole = "SELECT * from sys_users where id = $sellerId";
        $datarole = $this->db->query($searchrole)->fetchAll();
        $where = '';
        foreach($datarole as $value){
            if ($value["role_id"] == 29) {
                $branchoffice = $this->db->query($searchbranchofficeforuser)->fetchAll();
                //var_dump($branchoffice[0]["id"]);
                $branchname = intval($branchoffice[0]["id"]);
                $where = "WHERE sc.id > 0 and sc.created_by = $sellerId and (";
                $or = " or";
                foreach($rolesuserquery as $valuerole){
                    if ($auxiforroles == $sizeuserrolesspadmin) {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $sellerId))";
                    } else {
                        $where .= "(sc.user_id = ".$valuerole["id"]." or sc.user_id = $sellerId) $or ";
                    }
                    $auxiforroles += 1;
                    
                }
            } else {
                $where = 'WHERE sc.id > 0 ';
            }
        }
        $validUser = Auth::getUserInfo($this->config);
        $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";
        if ($sellerId !== null) {
        $sql = "SELECT role_id
        FROM sys_users
        WHERE id = $sellerId;";
        $currentRoles = $this->db->query($sql)->fetchAll();
        $roles = [];
        foreach ($currentRoles as $role) {
            array_push($roles, intval($role['role_id']));
        }
        }
        $y = date("Y");
        if ($customer == 'TODOS') {} else if($customer == ''){}else {$where .= " AND sc.customer_id = $customer";}

        if ($seller == 'TODOS') {} else if($seller == ''){}else {$where .= " AND sc.user_id = $seller";}

        if ($status == 'TODOS' && !in_array(2,$roles)) {} else if($status == ''){
        } else if ($status == 'TODOS' && in_array(2,$roles)) {
            $where .= " AND sc.status != 'NUEVO' AND sc.status != 'SOLICITADO' AND sc.status != 'CANCELADO'";
        } else if (in_array(2,$roles) && ($status != 'NUEVO' && $status != 'SOLICITADO' && $status != 'CANCELADO')) {
            $where .= " AND sc.status = '$status'";
        } else if (in_array(2,$roles)) {
           $where .= " AND sc.status != 'NUEVO' AND sc.status != 'SOLICITADO' AND sc.status != 'CANCELADO'";

        } else {
            $where .= " AND sc.status = '$status'";
        }
        
        if ($saleDatev1 == '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($saleDatev1.' 00:00:00.000000'));
        }
        if ($saleDatev2 == '') {
            $dateFin = date("Y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($saleDatev2.' 23:59:59.000000'));
        }
        $where .= " AND sc.created BETWEEN '".$dateIni."' AND '".$dateFin."'";


        $sql = "SELECT sc.id,old_folio ,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date, u.nickname AS user_name, c.name AS customer_name, sc.status, case when sc.status = 'NUEVO' then 1 when sc.status = 'SOLICITADO' then 2 when sc.status = 'AUTORIZADO' then 3  when sc.status = 'REMISIONADO' or sc.status = 'PARCIAL' then 4  when sc.status = 'ENTREGADO' then 5 else 0 end rater,
                STRING_AGG(distinct CAST(si.id AS varchar), ',') as invoices,count(DISTINCT si.id) as contador,bo.name as branchofficeorigin,
                (select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as montoinbulk,
                sc.contact_client_id
                FROM sls_shopping_cart AS sc
                LEFT JOIN sys_users AS u ON u.id = sc.user_id
                LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
                LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
                LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
                {$where}
                GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.status, bo.name,old_folio,rater";
        
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $inv) {
            if($data[$key]['invoices'] != null){
                if($data[$key]['contador'] > 1){
                    $invoices = $data[$key]['invoices'];
                    $data[$key]['array_invoices'] = explode(',',$invoices);
                }else{
                    $data[$key]['array_invoices'] = [$data[$key]['invoices']];
                }
            }else{
                $data[$key]['array_invoices'] = [];
            }
        }
        return $data;
    }
    public function getTable1OrdersSaleNOte($id) {
        $sql = "SELECT sls_customers.name as customer,
        sls_customers.price_list as price_list,
        wms_branch_offices.name as branch_of_origin,
        sls_customer_branch_offices.name as branch_recive,
        sls_shopping_cart.status as status,
        sys_users.nickname as name_user,
        sls_shopping_cart.shipping_cost,
		extract(day from sls_shopping_cart.created),
        case when (extract(month from sls_shopping_cart.created) =1)
		then ''||extract(day from sls_shopping_cart.created)||'/Ene/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =2)
		then ''||extract(day from sls_shopping_cart.created)||'/Feb/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =3)
		then ''||extract(day from sls_shopping_cart.created)||'/Mar/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =4)
		then ''||extract(day from sls_shopping_cart.created)||'/Abr/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =5)
		then ''||extract(day from sls_shopping_cart.created)||'/May/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =6)
		then ''||extract(day from sls_shopping_cart.created)||'/Jun/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =7)
		then ''||extract(day from sls_shopping_cart.created)||'/Jul/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =8)
		then ''||extract(day from sls_shopping_cart.created)||'/Ago/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =9)
		then ''||extract(day from sls_shopping_cart.created)||'/Sep/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =10)
		then ''||extract(day from sls_shopping_cart.created)||'/Oct/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =11)
		then ''||extract(day from sls_shopping_cart.created)||'/Nov/'||extract(year from sls_shopping_cart.created)
		when (extract(month from sls_shopping_cart.created) =12)
		then ''||extract(day from sls_shopping_cart.created)||'/Dic/'||extract(year from sls_shopping_cart.created)
		end as datee
        from sls_shopping_cart
        inner join sls_customers on sls_customers.id = sls_shopping_cart.customer_id
        inner join wms_branch_offices on wms_branch_offices.id = sls_shopping_cart.branchoffice
        inner join sls_customer_branch_offices on sls_customer_branch_offices.id = sls_shopping_cart.branchofficedestiny
        inner join sys_users on sys_users.id = sls_shopping_cart.user_id
        where sls_shopping_cart.id = $id";

        $query = $this->db->query($sql)->fetchAll();

        return $query;
    }

    public function getTable2OrdersSaleNote($id) {
        $sql = "SELECT sls_shopping_cart_in_bulk_details.qty, wms_products.name as name_product, wms_products.description as description_product, wms_marks.name as mark, sls_shopping_cart.comments as comments,
        wms_units.code as name_unit, sls_shopping_cart_in_bulk_details.price_product as price,
        (sls_shopping_cart_in_bulk_details.qty * sls_shopping_cart_in_bulk_details.price_product) as total,
        to_char(sls_shopping_cart_in_bulk_details.inmediatedate, 'DD/MM/YYYY') as inmediatedate
        from sls_shopping_cart_in_bulk_details
        inner join sls_shopping_cart on sls_shopping_cart.id = sls_shopping_cart_in_bulk_details.shopping_cart_id
        inner join sls_customers on sls_customers.id = sls_shopping_cart.customer_id
        inner join wms_products on wms_products.id = sls_shopping_cart_in_bulk_details.product_id
        inner join wms_units on wms_units.id = wms_products.unit_id
        inner join wms_lines ON wms_lines.id = wms_products.line_id
        left join wms_marks on wms_marks.id = wms_products.mark_id
        where sls_shopping_cart.id = $id
        order by sls_shopping_cart_in_bulk_details.id asc";

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

    public function getPdfquotationNote ($id, $order)
    {
        if (is_numeric($id)) {
            $invoice = ShoppingCart::findFirst($id);
            if ($invoice) {
                $pdf = $this->quotationNotePDF($id, $order);

                if (!is_null($pdf)) {
                    //$pdf->Output('I', "Remisión #$invoice->id.pdf", true);
                    $pdf->Output('I', "Nota", true);

                    $response = new Phalcon\Http\Response();

                    $response->setHeader("Content-Type", "application/pdf");
                    $response->setHeader("Content-Disposition", 'inline; filename="Nota"');
                    return $response;
                }
            }
        }
        return null;
    }

    public function quotationNotePDF($id, $order) {
        // print_r($id);
        // exit();
        //var_dump("PDF SALES NOTE");
        //$tb1 = $this->getTable1OrdersSaleNOte($id);
        $isOrder = $order;
        $tb2 = $this->getTable2OrdersSaleNote($id);
        $info = ShoppingCart::findFirst("id = $id");
        $total = $this->getTotal($id);
        $nameCustomers = $this->getNameCustomers($id);
        $contactCustomer = $this->getNameContactCustomers($id);
        if ($contactCustomer) {
            $myContact = $contactCustomer[0]['name'];
        }else {
            $myContact = null;
        }
        $pdf = new SaleNotesPdfController('P','mm','Letter');
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();

        // $pdf->SetInvoiceId("Nota de Venta");
        // $pdf->SetBranchOffice("PEDIDO #".$id);
        // $pdf->SetSaleDate($tb1[0]["datee"]);
        
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
        

        

        /* $pdf->SetXY(10,$pdf->GetY());
        $pdf->Cell(50, 6, "FECHA:", 0, 0, 'L', false);
        $pdf->SetXY(70,$pdf->GetY());
        $pdf->Cell(50, 6, $tb1[0]["datee"], 0, 0, 'L', false);

        $pdf->SetXY(170,$pdf->GetY());
        $pdf->Cell(50, 6, "CLIENTE:", 0, 0, 'L', false);
        $pdf->SetXY(220,$pdf->GetY());
        $pdf->Cell(50, 6, "".utf8_decode($tb1[0]["customer"]), 0, 0, 'L', false); */
        $pdf->Ln();
        //var_dump($pdf->GetPageWidth());
        // tabla 2

        $header = array('CANT.','PRODUCTO',utf8_decode('DESCRIPCIÓN'),'ENTREGA','MARCA',utf8_decode('PRECIO U.'),utf8_decode('TOTAL'));
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
                $w=array(10,40,35,25,25,17,20);
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

                $pdf->SetWidths(array(10,40,35,25,25,17,20));
                $pdf->SetAligns(array('C', 'L','L','C','L','R','R')); 
                $bandera=1;
                $pdf->SetXY(37,$pdf->GetY());
                foreach ($tb2 as $value) {
                    
                    $pdf->Row(array(
                        utf8_decode($value["qty"]),
                        utf8_decode($value["name_product"]), 
                        $value["description_product"],
                        $value['inmediatedate'],
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

    /* $pdf->SetAligns(array('C', 'L','L','C','L','R','R'));
    $pdf->SetLineWidth(0);
                foreach ($tb2 as $value) {
                    # code...
                    
                    $pdf->SetDrawColor(255,255,255);
                    
                    
                    if ($pdf->getY() == 204) {
                        $pdf->AddPage();
                        $pdf->SetXY(37,50);
                        
                    } else {
                        $pdf->SetXY(37,$pdf->GetY());
                    }//var_dump($pdf->getY());
                    $aux="";
                    if($contactCustomer){
                        $aux =$contactCustomer[0]['dateinmediate'];
                    }
                    $pdf->SetAligns(array('C', 'L','L','C','R','R','R'));
                    $pdf->Row(array(utf8_decode($value["qty"]),utf8_decode($value["name_product"]), utf8_decode($value["description_product"]),''.$aux,utf8_decode($value["mark"]),"$ ".number_format($value["price"],2, '.', ',')."","$ ".number_format($value["total"],2, '.', ',').""));
                    
                    $pdf->Row(array(utf8_decode($value["qty"]),utf8_decode($value["name_product"]), ($value["description_product"]."  yy78"),$value['inmediatedate'],utf8_decode($value["mark"]),"$ ".number_format($value["price"],2, '.', ',')."","$ ".number_format($value["total"],2, '.', ',').""));
                    
                    
                    //$pdf->SetDrawColor(255,255,255);
                    $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill , $fill, $fill));
                    $pdf->SetFillColor(135,180,223);
                    $fill = !$fill;
                    $amount += intval($value["qty"]);
                    //var_dump($pdf->getY());
                    
                } */
                
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
                //$pdf->Cell(100,7,utf8_decode("Comentarios: ". $info->comments."tenia un errito que se llamaba bili y un dia nomas no apareció"),0,0,'L');
                
                //
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

        $pdf->SetTitle($isOrder == 'si' ? 'Cotización Pedido #'.$id : 'Pedido #'.$id,true);
        /* $pdf->Output('I', "Nota", true);

            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Nota"'); */
        return $pdf;

    }

}

class SaleNotesPdfController extends FPDF
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

class PDFShoppingcart extends FPDF
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
        $this->Image($img,10,10,65,0,'PNG');
        $this->SetTextColor(21, 18, 46);
        $this->SetFont('Nunito-Regular','',20);
        $this->Cell(0, 10, utf8_decode("PEDIDO #$this->invoiceId"), 0, 0, 'R');
        $this->Ln();
        $this->Cell(0, 10, utf8_decode("SUCURSAL $this->branchOffice"), 0, 0, 'R');
        $this->Ln();
        $this->Cell(0, 10, $this->saleDate, 0, 0, 'R');
        $this->Ln();
    }

    function Footer()
    {
        $this->SetFont('Nunito-Regular','',10);
        $this->SetTextColor(21, 18, 46);
        $this->SetY(260);
        $this->SetY(257);
        $this->Cell(195, 6, "REINVENTAMOS EL FUTURO DE LAS FIBRAS", 0, 0, 'C', false);
        $this->SetY(261);
        $this->Cell(195, 6, "WWW.TECHNOFIBERS.COM", 0, 0, 'C', false);
        $this->SetY(274);
        $this->SetFillColor(21, 18, 46);
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

class PDF extends FPDF
{
    function encabezado()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,10,55,0,'PNG');
        }
        $this->SetXY(215, 10);
        $this->SetFont('Nunito-Regular', '', 10);
        $this->Cell(10, 5, utf8_decode('FECHA DE IMPRESIÓN: ') . date('d') . '/' . date('m') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 30, 12);
        $this->SetFont('Nunito-Regular', '', 18);
        $this->SetTextColor(0);
     // $this->Cell(0, 0, utf8_decode('REBASA'));
        $this->SetFont('Nunito-Regular', '', 15);
        $this->SetXY(($this->GetPageWidth() / 2) - 23, 18);
        $this->Cell(0, 0, 'REPORTE DE PEDIDOS');

        $header = array(
            'PEDIDO',
            'ESTATUS',
            'FECHA',
            'PEDIDO',
            'VENDEDOR',
            'CLIENTE',
            'PIEZAS',
            'MONTO (IVA)'
        );
        $this->SetXY(5, 35);

        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 10);
        // Header
        $x = 143;
        $i = 0;
        $w = array(15,25,30,20,60,60,15,40);
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
    function SetHeight($h)
    {
        $this->height=$h;
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
