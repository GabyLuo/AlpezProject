<?php

use Phalcon\Mvc\Controller;

class OldBalanceController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getClients () {
        if ($this->userHasPermission()) {
            $sql = "SELECT 
            distinct c.name AS customer, c.serial, c.id
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
            AND (  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '2022-03-17' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '2022-03-17' and i.status_payment = 0) OR   (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '2022-03-17' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '2022-03-17' and i.status_payment = 1)) AND i.created BETWEEN '2021-12-02 00:00:00' AND '2022-12-31 00:00:00.000000'  and loan = 0
            GROUP BY c.id,c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days,c.serial
            order by c.name  ASC";
            $options = [];
            $data = $this->db->query($sql)->fetchAll();
            foreach ($data as $value) {
                $options[] = [
                    'label' => $value['customer'],
                    'value' => $value['id']
                ];
            }
            $this->content['options'] = $options;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOldbalance () {
        $request = $this->request->getPost();
        $id = $request['customer']; 
        $date1 = $request['saleDatev1']; 
        $date2 = $request['saleDatev2'];
        $status= $request['status'];
        $type= $request['type'];
        $request= $request;
        $branch = $request['branches'];
        if ($this->userHasPermission()) {
            
            $validUser = Auth::getUserInfo($this->config);
        $y = date('Y');
        $where = " ";
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
                    $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$f' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                } else if ($ts == 3) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$f' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
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
        if(!empty($request['branchid'])){
            if(is_array($request['branchid'])){
                $branch_office_id = $request['branchid']['value'];
            } else {
                $branch_office_id = $request['branchid'];
            }
        } else {
            $branch_office_id = null;
        }
        
        //$where .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id ";
        //$where2 .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id ";
        
        if ($branch == 'TODOS') {} else {
            $where .= " AND sc.branchoffice = $branch";
        }
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
                {$where} and loan = 0 and i.status != 'CANCELADO' and i.status != 'REMISIONADO' 
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days,c.serial
                {$sortBy} {$desc} 
                ";
                
                $sqlCustomers = "SELECT 
                distinct c.name AS customer, c.serial
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
                {$where} and loan = 0 and c.payment_method = 'CREDITO'
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days,c.serial
                order by c.name {$desc}  $limit
                ";
                //$sqlCustomers ="SELECT name as customer from sls_customers";
                //print_r($sql);
                //exit();
        $data = $this->db->query($sql)->fetchAll();
        $datacustomers = $this->db->query($sqlCustomers)->fetchAll();
        foreach ($data as $key => $d) {
            $id = $d['id'];
            //$totales = $this->getImpuestos($id);
            $resta = $d['bulktotal'] - $d['abonado'];
            $data[$key]['cantidad_total'] = $d['bulktotal'];
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

        $response = array('data' => $data, 'rowCounts' => $invoicesCount[0]['count']);
        $getResult = [];
        $thirty = 0;
        $sixty = 0;
        $ninety = 0;
        $overninety = 0;
        $index = 0;
        $register = false;
        $fechacurrent = date('Y-m-d');
        $pastDueBalance = 0;
        $currentBalance = 0;
        $sumAll = 0;

        $allCurrent = 0;
        $allLessThanThirty = 0;
        $allLessThanSixty = 0;
        $allLessThanNinety = 0;
        $allOverNinety = 0;
        $allDefeater = 0;
        $allTotal = 0;
        $oldbalanceCount = 0;
        /* var_dump($datacustomers);
        var_dump($response['data']); */
        /* var_dump(date('Y-m-d', strtotime(date('Y-m-d').' - 30 days')));
        var_dump(date('Y-m-d', strtotime(date('Y-m-d').' - 60 days'))); */
       foreach ($datacustomers as  $valueCustomers) {
            foreach ($response['data'] as  $value) {
                if ($value['customer'] == $valueCustomers['customer']) {
                    
                    if ((strval($value['vencimiento']) == 'PENDIENTE DE PAGO' || strval($value['vencimiento']) == 'ABONADO' || strval($value['vencimiento']) == 'POR VENCER' ||  strval($value['vencimiento']) == 'POR VENCER ABONADO') && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d')))) {
                        $currentBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allCurrent += floatval($value['cantidad_restante']);
                        $register = true;
                        if (date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' + 90 days'))) {
                            // && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO'
                            //Fecha de hoy a 90 dias
                            //var_dump($valueCustomers['customer'] . "  " . date('Y-m-d',strtotime($value['expired_date'])) ." >= " . date('Y-m-d', strtotime(date('Y-m-d').' + 90 days')));
                            $overninety += floatval($value['cantidad_restante']);
                            $allOverNinety += floatval($value['cantidad_restante']);
                        }
                    }else
                    //Fecha de hoy a 30 dias atras
                    if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d')))  && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' - 30 days')) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        $thirty += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanThirty += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                        
                    }else if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d').' - 31 days')) && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' - 60 days')) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        //Fecha de hoy a 60 dias
                        $sixty += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanSixty += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                        
                    }else if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d').' - 61 days')) && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d'.' - 90 days'))) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        //Fecha de hoy a 90 dias
                        $ninety += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanNinety += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                    }/* else {
                        $register = false;
                    } */
                }
            }
            if ($register) {
                $oldbalanceCount ++;
                array_push($getResult,array('customer' => $valueCustomers['customer'], 'thirty' => "$ ".number_format($thirty, 2, '.', ','), 'sixty' => "$ ".number_format($sixty, 2, '.', ','), 'ninety' => "$ ".number_format($ninety, 2, '.', ','), 'overninety' => "$ ".number_format($overninety, 2, '.', ','), 'pastduebalance' => "$ ".number_format($pastDueBalance, 2, '.', ','), 'currentBalance' => "$ ".number_format($currentBalance, 2, '.', ','),'sumAll' => "$ ".number_format($sumAll,2,'.',',')));
            }
            $register = false;
            $thirty = 0;
            $sixty = 0;
            $ninety = 0;
            $overninety = 0;
            $index += 1;
            $pastDueBalance = 0;
            $currentBalance = 0;
            $allTotal += floatval($sumAll);
            $sumAll = 0;
            
        }
        array_push($getResult,array('customer' => false, 'thirty' => "$ ".number_format($allLessThanThirty, 2, '.', ','), 'sixty' => "$ ".number_format($allLessThanSixty, 2, '.', ','), 'ninety' => "$ ".number_format($allLessThanNinety, 2, '.', ','), 'overninety' => "$ ".number_format($allOverNinety, 2, '.', ','), 'pastduebalance' => "$ ".number_format($allDefeater, 2, '.', ','), 'currentBalance' => "$ ".number_format($allCurrent, 2, '.', ','),'sumAll' => "$ ".number_format($allTotal,2,'.',',')));
            $this->content['oldbalance'] = $getResult;
            $this->content['oldbalanceCount'] = $oldbalanceCount;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getDataToPdf ($customer, $status, $branch, $filter) {
        $request = $this->request->getPost();
        $id = $customer; 
        $date1 = ''; 
        $date2 = '';
        $status= $status;
        $type= 0;
        $request= $request;
            /* $validUser = Auth::getUserInfo($this->config); */
        $y = date('Y');
        $where = "";
        $where2 = "";
        $order = "";
        $contador = 0;
        $contadorv2 = 0;
        $arrayStatus = explode(',',$status);
        $arrayStatus2 = [];
        foreach ($arrayStatus as $value) {
            # code...
            array_push($arrayStatus2,intval($value));
        }
        //var_dump($arrayStatus2);
        if (count($arrayStatus2) > 0) {
            $f = date('Y-m-d');
            $where .= "AND (";
            foreach ($arrayStatus2 as $ts) {
                if ($ts == 1) {
                    $where .= "  (i.status_payment = 2)";
                } else if ($ts == 2) {
                    $where .= "  ( TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$f' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                } else if ($ts == 3) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$f' OR TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                } else if ($ts == 4) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 1)";
                } else if ($ts == 5) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$f' and i.status_payment = 0)";
                } else if ($ts == 6) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 1)";
                } else if ($ts == 7) {
                    $where .= "  (TO_CHAR(cast(i.sale_date as DATE) +CAST(c.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$f' and i.status_payment = 0)";
                }
                if (count($arrayStatus2) - 1 > $contador) {
                    $where .= ' OR ';
                }
                $contador++;
            }
            $where .= ")";
        } else {
            $where .= "";
        }
        /* if(!empty($request['branchid'])){
            if(is_array($request['branchid'])){
                $branch_office_id = $request['branchid']['value'];
            } else {
                $branch_office_id = $request['branchid'];
            }
        } else {
            $branch_office_id = null;
        } */
        
        /* $where .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id ";
        $where2 .= $validUser->role_id == 1 ? "" : " AND sc.branchoffice = $validUser->branch_office_id "; */
        if ($branch == 'TODOS') {} else {
            $where .= " AND sc.branchoffice = $branch";
        }
        if ($id == 'TODOS') {} else if($id == ''){}else {$where .= " AND cbo.customer_id = $id";$where2 .= " AND cbo.customer_id = $id";}
        // if ($branch_office_id == 'TODAS') {} else if($branch_office_id == ''){}else {$where .= " AND sc.branchoffice = $branch_office_id";$where2 .= " AND sc.branchoffice = $branch_office_id";}
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
        /* $filter = $request['filter'];
        $pagination = $request['pagination'];*/
        if ($filter != 'TODOS'){
            $where .= " AND ( i.id::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR i.serie||'-'||i.folio_fiscal ILIKE '%".$filter."%')";
            $where2 .= " AND ( i.id::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR i.serie||'-'||i.folio_fiscal ILIKE '%".$filter."%')";
        } 

        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY " . trim($pagination['sortBy']." ");
        } else {
            $sortBy .= " ORDER BY i.id ";
        }
        //$desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $desc =' ASC ';
        //$offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        //$limit = " LIMIT " . $pagination['rowsPerPage']." ";

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
                {$where} and loan = 0 and i.status != 'CANCELADO' and i.status != 'REMISIONADO' 
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days,c.serial
                {$sortBy} {$desc} 
                ";
                $sqlCustomers = "SELECT 
                distinct c.name AS customer, c.serial
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
                {$where} and loan = 0 and c.payment_method = 'CREDITO'
                GROUP BY c.term,c.credit_days, c.term,i.id, a.nickname, bs.branch_office_id, bb.name, ibs.branch_office_id, ibb.name, ls.branch_office_id, lb.name, bm.storage_id,ibs.name,lm.storage_id,ls.name,i.customer_branch_office_id,cbo.name,cbo.customer_id,c.name, bs.name, ibm.storage_id,c.price_list,d.name,bm.date,ibm.date,lm.date,c.credit_days,c.serial
                order by c.name {$desc} 
                ";
                //$sqlCustomers ="SELECT name as customer from sls_customers";
                //print_r($sql);
                //exit();
        $data = $this->db->query($sql)->fetchAll();
        $datacustomers = $this->db->query($sqlCustomers)->fetchAll();
        foreach ($data as $key => $d) {
            $id = $d['id'];
            //$totales = $this->getImpuestos($id);
            $resta = $d['bulktotal'] - $d['abonado'];
            $data[$key]['cantidad_total'] = $d['bulktotal'];
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

        $response = array('data' => $data, 'rowCounts' => $invoicesCount[0]['count']);
        $getResult = [];
        $totalbalance = 0;
        $thirty = 0;
        $sixty = 0;
        $ninety = 0;
        $overninety = 0;
        $index = 0;
        $register = false;
        $fechacurrent = date('Y-m-d');
        $pastDueBalance = 0;
        $currentBalance = 0;
        $sumAll = 0;
       
        $allCurrent = 0;
        $allLessThanThirty = 0;
        $allLessThanSixty = 0;
        $allLessThanNinety = 0;
        $allOverNinety = 0;
        $allDefeater = 0;
        $allTotal = 0;
       foreach ($datacustomers as  $valueCustomers) {
            foreach ($response['data'] as  $value) {
                if ($value['customer'] == $valueCustomers['customer']) {
                    
                    if ((strval($value['vencimiento']) == 'PENDIENTE DE PAGO' || strval($value['vencimiento']) == 'ABONADO' || strval($value['vencimiento']) == 'POR VENCER' ||  strval($value['vencimiento']) == 'POR VENCER ABONADO') && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d')))) {
                        $currentBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allCurrent += floatval($value['cantidad_restante']);
                        $register = true;
                        if (date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' + 90 days'))) {
                            // && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO'
                            //Fecha de hoy a 90 dias
                            //var_dump($valueCustomers['customer'] . "  " . date('Y-m-d',strtotime($value['expired_date'])) ." >= " . date('Y-m-d', strtotime(date('Y-m-d').' + 90 days')));
                            $overninety += floatval($value['cantidad_restante']);
                            $allOverNinety += floatval($value['cantidad_restante']);
                        }
                    }else
                    //Fecha de hoy a 30 dias atras
                    if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d')))  && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' - 30 days')) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        $thirty += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanThirty += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                        
                    }else if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d').' - 31 days')) && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d').' - 60 days')) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        //Fecha de hoy a 60 dias
                        $sixty += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanSixty += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                        
                    }else if (date('Y-m-d',strtotime($value['expired_date'])) <= date('Y-m-d', strtotime(date('Y-m-d').' - 61 days')) && date('Y-m-d',strtotime($value['expired_date'])) >= date('Y-m-d', strtotime(date('Y-m-d'.' - 90 days'))) && strval($value['vencimiento']) != 'PAGADO' && strval($value['vencimiento']) != 'POR VENCER ABONADO' && strval($value['vencimiento']) != 'POR VENCER' && strval($value['vencimiento']) != 'PENDIENTE DE PAGO' && strval($value['vencimiento']) != 'ABONADO') {
                        //Fecha de hoy a 90 dias
                        $ninety += floatval($value['cantidad_restante']);
                        $pastDueBalance += floatval($value['cantidad_restante']);
                        $sumAll += floatval($value['cantidad_restante']);
                        $allLessThanNinety += floatval($value['cantidad_restante']);
                        $allDefeater += floatval($value['cantidad_restante']);
                        $register = true;
                    }/* else {
                        $register = false;
                    } */
                }
            }
            if ($register) {
                array_push($getResult,array('customer' => $valueCustomers['customer'], 'thirty' => "$ ".number_format($thirty, 2, '.', ','), 'sixty' => "$ ".number_format($sixty, 2, '.', ','), 'ninety' => "$ ".number_format($ninety, 2, '.', ','), 'overninety' => "$ ".number_format($overninety, 2, '.', ','), 'pastduebalance' => "$ ".number_format($pastDueBalance, 2, '.', ','), 'currentBalance' => "$ ".number_format($currentBalance, 2, '.', ','),'sumAll' => "$ ".number_format($sumAll,2,'.',',')));
            }
            $register = false;
            $thirty = 0;
            $sixty = 0;
            $ninety = 0;
            $overninety = 0;
            $index += 1;
            $pastDueBalance = 0;
            $currentBalance = 0;
            $allTotal += floatval($sumAll);
            $sumAll = 0;
            
        }
        array_push($getResult,array('customer' => false, 'thirty' => "$ ".number_format($allLessThanThirty, 2, '.', ','), 'sixty' => "$ ".number_format($allLessThanSixty, 2, '.', ','), 'ninety' => "$ ".number_format($allLessThanNinety, 2, '.', ','), 'overninety' => "$ ".number_format($allOverNinety, 2, '.', ','), 'pastduebalance' => "$ ".number_format($allDefeater, 2, '.', ','), 'currentBalance' => "$ ".number_format($allCurrent, 2, '.', ','),'sumAll' => "$ ".number_format($allTotal,2,'.',',')));
        return $getResult;
    }

    public function getCSV ($customer, $sataus, $branch, $filter) {
        $dataOldBalance = $this->getDataToPdf($customer, $sataus, $branch, $filter);
        date_default_timezone_set('America/Mexico_City');
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['CLIENTE',utf8_decode('SALDO CORRIENTE'), '0 - 30', '31 - 60', '61 - 90', '90+',utf8_decode('VENCIDO'),'TOTAL'], ',');
        foreach ($dataOldBalance as $value) {
            if ($value["customer"] == false) {
                fputcsv($fp, [
                    "TOTAL:", $value["currentBalance"], 
                            $value['thirty'], $value["sixty"], $value["ninety"], 
                            $value["overninety"], $value["pastduebalance"],$value["sumAll"],
                ], ',');
            }else {
                fputcsv($fp, [
                    $value["customer"], $value["currentBalance"], 
                            $value['thirty'], $value["sixty"], $value["ninety"], 
                            $value["overninety"], $value["pastduebalance"],$value["sumAll"],
                ], ',');
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
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Antigüedad de saldos.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getPdf ($customer, $sataus,$branch, $filter) {
        $dataOldBalance = $this->getDataToPdf($customer, $sataus, $branch,  $filter);
        
        $pdf = new PDFOldBalance('L','mm','Letter');
                    $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
                    $pdf->AliasNbPages();
                    $pdf->setDateNow(date("d/m/Y"));
                    $pdf->SetTitlePDF("Antigüedad de saldos");
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

                    $header = array('CLIENTE.','CORRIENTE','0 - 30','31 - 60','61 - 90','90+',utf8_decode('VENCIDO'),'TOTAL');
                $pdf->SetXY(10,$pdf->GetY());
                $pdf->SetFillColor(128,179,240);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetLineWidth(0);
                $pdf->SetFont('Nunito-Regular','',8);
                // Header
                $x = 190;
                $i = 0;
                // $w=array(5,20,25,30,25,30,35,20,15);
                $w=array(77,30,30,20,20,20,30,30);
                foreach($header as $col) {
                    if($i<=7){
                        $pdf->Cell($w[$i],7,$col,0,0,'C',true);
                    }
                    $x=$x+5;
                    $i++;
                }
                $fill = false;
                $pdf->SetWidths(array(77,30,30,20,20,20,30,30));
                $pdf->SetAligns(array('L', 'R','R','R','R','R','R','R')); 
                $pdf->Ln(7);
                foreach ($dataOldBalance as $value) {
                   // var_dump($pdf->GetY());
                    if ($pdf->GetY() >= 190) {
                        $pdf->AddPage();
                        $pdf->SetXY(10,47);
                    }else {
                        $pdf->SetXY(10,$pdf->GetY());
                    }
                    if ($value["customer"] == false) {
                        $pdf->SetAligns(array('R', 'R','R','R','R','R','R','R')); 
                        $pdf->Row(array("TOTAL: ", utf8_decode($value["currentBalance"]), 
                        $value['thirty'], $value["sixty"], $value["ninety"], 
                        $value["overninety"],$value["pastduebalance"],$value["sumAll"]), false);
                    } else {
                        $pdf->Row(array(substr(utf8_decode($value["customer"]),0, 40), utf8_decode($value["currentBalance"]), 
                        $value['thirty'], $value["sixty"], $value["ninety"], 
                        $value["overninety"],$value["pastduebalance"],$value["sumAll"]), false);
                    }
                        
                }

                    $pdf->SetTitle(utf8_decode("Antigüedad de saldos"));
                    header("Access-Control-Allow-Origin: *");
                    header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', utf8_decode("Antigüedad de saldos"), true);

        return $pdf;
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 28 OR role_id = 7 OR role_id = 8)
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
Class PDFOldBalance extends FPDF
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