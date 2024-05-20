<?php

class RevisaTimbrado
{

    public function revisarTimbrado ()
    {   
        $batuta_url = 'batuta.wasp.mx';
            $invoices = Invoices::find("status_timbrado in (3,4,5)");
            foreach($invoices as $invoice){
                if ($invoice && $invoice->status_timbrado == 4) {
                    $service_url = 'https://'.$batuta_url.'/api/info_factura';
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
                            $service_url = 'https://'.$batuta_url.'/api/get_uuid';
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
                                    $payment->created_by = $invoice->created_by;
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
                                $invoice->uuid = $uuid_factura;
                                $invoice->message = $response->message;
                                if ($invoice->update()) {
                                    echo('succes');
                                } else {
                                    echo('error');
                                }
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $invoice->message = $response->message;
                            if ($invoice->update()) {
                                echo('succes');
                            } else {
                                echo('error');
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error')  {
                            $invoice->status_timbrado = 6;
                            $invoice->message = $response->message;
                            if ($invoice->update()) {
                                echo('succes');
                            } else {
                                echo('error');
                            }
                        }
                    }
                } else if ($invoice && $invoice->status_timbrado == 3) {
                    $service_url = 'https://'.$batuta_url.'/api/info_general';
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
                                echo ('success');
                            } else {
                                echo('error');
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                echo ('success');
                            } else {
                                echo('error');
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error')  {
                            $invoice->status_timbrado = 7;
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                echo ('success');
                            } else {
                                echo('error');
                            }
                        }
                    }
                } else if ($invoice && $invoice->status_timbrado == 5) {
                    $service_url = 'https://'.$batuta_url.'/api/get_status_cancelacion';
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
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            } else {
                                $status = explode('|', str_replace(' ', '', $response->message))[0];
                                if ($status == 211) {
                                    $invoice->message_cancelacion = $response->message;
                                    if ($invoice->update()) {
                                        echo ('success');
                                    } else {
                                        echo('error');
                                    }
                                } else {
                                    $invoice->status_timbrado = 7;
                                    $invoice->message_cancelacion = $response->message;
                                    $invoice->acusesat_cancelacion = $response->acuseSat;
                                    if ($invoice->update()) {
                                        echo ('success');
                                    } else {
                                        echo('error');
                                    }
                                }
                            }
                        } else if ($response->ret_status == 211) {
                            $invoice->message_cancelacion = $response->message;
                            if ($invoice->update()) {
                                echo ('success');
                            } else {
                                echo('error');
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
                                echo ('success');
                            } else {
                                echo('error');
                            }
                        }
                    }
                }
            }
        return true;
    }

    public function revisarPagos ()
    {   
        $batuta_url = 'batuta.wasp.mx';
        
        $invoices = InvoicePayments::find(['status_timbrado in (3,4,5)','columns' => 'distinct(invoice_id)']);

        foreach($invoices as $invoice){ 
            $pagos = InvoicePayments::find('status_timbrado in (3,4,5) and invoice_id = '.$invoice['0']);
            if (count($pagos)>0) {
                foreach ($pagos as $key => $pago) {
                    $pagosFolio = InvoicePayments::find("status_timbrado != 1 and folio = $pago->folio and id_request = '$pago->id_request'");
                    if ($pago && $pago->status_timbrado == 4) {
                        $service_url = 'https://'.$batuta_url.'/api/info_factura';
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
                                $service_url = 'https://'.$batuta_url.'/api/get_uuid';
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
                                            $pagoFolio->status_timbrado = 1;
                                            $pagoFolio->uuid = $uuid_factura;
                                            $pagoFolio->message = $response->message;
                                            $pagoFolio->update();
                                        }
                                    if ($pago->update()) {
                                        echo ('success');
                                    } else {
                                        echo('error');
                                    }
                                }
                            } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                                $pago->message = $response->message;
                                if(count($pagosFolio)>1)
                                    foreach($pagosFolio as $pagoFolio){
                                        $pagoFolio->message = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            } else  if ($response->status == 'Error' || $response->status == 'error')  {
                                $pago->status_timbrado = 6;
                                $pago->message = $response->message;
                                if(count($pagosFolio)>1)
                                    foreach($pagosFolio as $pagoFolio){
                                        $pagoFolio->status_timbrado = 6;
                                        $pagoFolio->message = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            }
                        }
                        if ($this->content['result'] == 'success') {
                        }
                    } else if ($pago && $pago->status_timbrado == 3) {
                        $service_url = 'https://'.$batuta_url.'/api/info_general';
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
                                        $pagoFolio->status_timbrado = 5;
                                        $pagoFolio->id_cancelacion_asc = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                                $pago->message_cancelacion = $response->message;
                                if(count($pagosFolio)>1)
                                    foreach($pagosFolio as $pagoFolio){
                                        $pagoFolio->message_cancelacion = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            } else  if ($response->status == 'Error' || $response->status == 'error')  {
                                $pago->status_timbrado = 7;
                                $pago->message_cancelacion = $response->message;
                                if(count($pagosFolio)>1)
                                    foreach($pagosFolio as $pagoFolio){
                                        $pagoFolio->status_timbrado = 7;
                                        $pagoFolio->message_cancelacion = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
                                }
                            }
                        }
                        if ($this->content['result'] == 'success') {
                        }
                    } else if ($pago && $pago->status_timbrado == 5) {
                        $service_url = 'https://'.$batuta_url.'/api/get_status_cancelacion';
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
                                        $pagoFolio->status_timbrado = 2;
                                        $pagoFolio->message_cancelacion = $response->message;
                                        $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                        $pagoFolio->fecha_cancelacion_recibido = date('Y-m-d H:i:s');
                                        $pagoFolio->update();
                                    }
                                    if ($pago->update()) {
                                        echo ('success');
                                    } else {
                                        echo('error');
                                    }
                                } else {
                                    $status = explode('|', str_replace(' ', '', $response->message))[0];
                                    if ($status == 211) {
                                        $pago->message_cancelacion = $response->message;
                                        if(count($pagosFolio)>1)
                                            foreach($pagosFolio as $pagoFolio){
                                                $pagoFolio->message_cancelacion = $response->message;
                                                $pagoFolio->update();
                                            }
                                        if ($pago->update()) {
                                            echo ('success');
                                        } else {
                                            echo('error');
                                        }
                                    } else {
                                        $pago->status_timbrado = 7;
                                        $pago->message_cancelacion = $response->message;
                                        $pago->acusesat_cancelacion = $response->acuseSat;
                                        if(count($pagosFolio)>1)
                                            foreach($pagosFolio as $pagoFolio){
                                                $pagoFolio->status_timbrado = 7;
                                                $pagoFolio->message_cancelacion = $response->message;
                                                $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                                $pagoFolio->update();
                                            }
                                        if ($pago->update()) {
                                            echo ('success');
                                        } else {
                                            echo('error');
                                        }
                                    }
                                }
                            } else if ($response->ret_status == 211) {
                                $pago->message_cancelacion = $response->message;
                                if(count($pagosFolio)>1)
                                    foreach($pagosFolio as $pagoFolio){
                                        $pagoFolio->message_cancelacion = $response->message;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                    echo ('success');
                                } else {
                                    echo('error');
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
                                        $pagoFolio->status_timbrado = 7;
                                        $pagoFolio->message_cancelacion = $response->message;
                                        $pagoFolio->acusesat_cancelacion = $response->acuseSat;
                                        $pagoFolio->update();
                                    }
                                if ($pago->update()) {
                                } else {
                                    echo('error');
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}
