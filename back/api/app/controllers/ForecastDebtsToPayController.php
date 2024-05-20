<?php

use Phalcon\Mvc\Controller;

class ForecastDebtsToPayController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    

    public function getDebtsToPay () {
        $info = [];
        $request = $this->request->getPost();
        $date = $request['date'];
        $sum_total = 0;
        $where = "where pur_orders.status in ('RECIBIDO', 'PEDIDO') and pur_orders.status_payment IN (0,1) and pur_orders.invoice_date is not null  ";
        $where .= $request['type'] === 'CORRIENTE' ? " and TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(pur_suppliers.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$date'" : " and TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(pur_suppliers.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$date'";
        
        if ($request['branch_id'] != 0) {
            $where .= " AND e.branch_office_id = ".$request['branch_id'];
        }
        
        $sql = "SELECT pur_orders.serial, pur_suppliers.name  as name_suppliers, pur_suppliers.id  as supplier_id,
        pur_orders.id as po_id,
        (select COALESCE((SELECT sum(pod.qty * trunc(pod.price::numeric,5)) + COALESCE(sum(pod.ieps),0) + COALESCE(sum(pod.vat),0) + COALESCE(pur_orders.shipping_price,0) from pur_order_details as pod where pod.po_id = pur_orders.id), 0)) as totalamount,
        (select COALESCE((SELECT sum(pop.amount) from pur_order_payments as pop where pop.po_id = pur_orders.id), 0)) as abonado,
        (select COALESCE((SELECT sum(pod.qty * pod.price) from pur_order_details as pod where pod.po_id = pur_orders.id), 0)) as restante
        FROM pur_orders
        INNER JOIN pur_suppliers on pur_suppliers.id = pur_orders.supplier_id
        LEFT join pur_order_details as pd on pd.po_id = pur_orders.id
        left join wms_storages e on e.id = pur_orders.storage_id
        $where
        group by pur_suppliers.name, pur_orders.shipping_price, pur_orders.id, pur_suppliers.id";
        $data = $this->db->query($sql)->fetchAll();
        
        $sqlsup = "SELECT distinct pur_suppliers.id  as supplier_id
        FROM pur_orders
        INNER JOIN pur_suppliers on pur_suppliers.id = pur_orders.supplier_id
        LEFT join pur_order_details as pd on pd.po_id = pur_orders.id
        left join wms_storages e on e.id = pur_orders.storage_id
        $where
        group by pur_suppliers.name, pur_orders.shipping_price, pur_orders.id, pur_suppliers.id";
        
        $dataidssup = $this->db->query($sqlsup)->fetchAll();
        $resultdebts = [];
        $qtyor = 0;
        $namesup = null;
        $total = 0;
        $abonado = 0;
        $remaining = 0;
        $totales = 0;
        $summAllTotal = 0;
        $versuma = 0;
        if (count($data)) {
            foreach ($dataidssup as $key => $d){

                foreach($data as $value){
                    if ($value['supplier_id'] == $d['supplier_id']) {
                        $qtyor +=1;
                        $namesup = $value['name_suppliers'];
                        $total += $value['totalamount'];
                        $versuma  += $value['totalamount'];
                        $abonado = $value['abonado'];
                        $totales = floatval($value['totalamount']);
                        $remaining += $totales - floatval($value['abonado']);
                        $summAllTotal += $remaining;
                    }
                }
                array_push($resultdebts, array('name_sup' => $namesup, 'qtyorders' =>  $qtyor, 'total' => $total, 'abonado' => $abonado, 'remaining' => $remaining));
            
            $namesup = "";
            $qtyor = 0;
            $total = 0;
            $abonado = 0;
            $totales = 0;
            $remaining = 0;
        }
            
        }
        $this->content['data'] = $resultdebts;
        $this->content['sm'] = number_format($versuma, 2, '.', ',');
        $this->response->setJsonContent($this->content);
        
    }

    public function getDebtsToPayForSuppliers () {
        $info = [];
        $request = $this->request->getPost();
        $date = $request['date'];
        $sum_total = 0;
        
        $where = "where pur_orders.status in ('RECIBIDO', 'PEDIDO') and pur_orders.status_payment IN (0,1) and pur_orders.invoice_date is not null ";
        $where .= $request['type'] === 'CORRIENTE' ? " and TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(pur_suppliers.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$date'" : " and TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(pur_suppliers.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$date'";
        if ($request['branch_id'] != 0) {
            $where .= " AND e.branch_office_id = ".$request['branch_id'];
        }
        $sql = "SELECT pur_orders.serial, pur_orders.serial, pur_suppliers.name  as name_suppliers, pur_suppliers.id  as supplier_id,
        pur_orders.id as po_id,
        (select COALESCE((SELECT sum(pod.qty * trunc(pod.price::numeric,5)) + COALESCE(sum(pod.ieps),0) + COALESCE(sum(pod.vat),0) + COALESCE(pur_orders.shipping_price,0) from pur_order_details as pod where pod.po_id = pur_orders.id), 0)) as totalamount,
        (select COALESCE((SELECT sum(pop.amount) from pur_order_payments as pop where pop.po_id = pur_orders.id), 0)) as abonado,
        (select COALESCE((SELECT sum(pod.qty * pod.price) from pur_order_details as pod where pod.po_id = pur_orders.id), 0)) as restante
        FROM pur_orders
        INNER JOIN pur_suppliers on pur_suppliers.id = pur_orders.supplier_id
        LEFT join pur_order_details as pd on pd.po_id = pur_orders.id
        left join wms_storages e on e.id = pur_orders.storage_id
        $where
        group by pur_suppliers.name, pur_orders.shipping_price, pur_orders.id, pur_suppliers.id";
        $data = $this->db->query($sql)->fetchAll();
        $summAllTotal = 0;
        foreach ($data as $key => $d){
            //$id = $d['id'];
            $totales = floatval($d['totalamount']);
            $resta = $totales - floatval($d['abonado']);
            $data[$key]['restante'] = floatval($resta);
            $summAllTotal += $resta;
        }
        // 'rowCounts' => $invoicesCount[0]['count']
        $this->content['data'] = $data;
        $this->content['sm'] = number_format($summAllTotal, 2, '.', ',');
        $this->response->setJsonContent($this->content);
        
    }

    public function getDataCalendar($idBranch)
    {
        $today = $hoy = date("Y-m-d");
        $days = $this->getDaysForWeek();
        $daysv2 = $this->getAllPendingDays($idBranch);
        
        $info = [];
        $canttt = 0;
        foreach ($daysv2 as $key => $d) {
            if ($today <= $d['invoice_date']) {
                $data = $this->getcustomerBalancetoBeat($d['invoice_date'], 2, $idBranch);
                $toPay = $this->getToPayToBeatv2($data, $idBranch);
                if ($toPay['remaining'] > 0) {
                    $corrientes = array("title" => $toPay['remaining'], "details" => 'CORRIENTE', "date" => $d['invoice_date'], "bgcolor" => 'bg-green');
                    array_push($info, $corrientes);
                    $canttt += $toPay['remaining'];
                }
            }
        }
        $datav2 = $this->getcustomerBalancetoBeat($today, 1, $idBranch);
        $toPay = $this->getToPayToBeatv2($datav2, $idBranch);
        $vencido = array("title" => $toPay['remaining'], "details" => 'VENCIDO', "date" => $today, "bgcolor" => 'bg-red');
        array_push($info, $vencido);
        
        $this->content['data'] = $info;
        $this->response->setJsonContent($this->content);
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

    public function getAllPendingDays($branch)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1?' ':" AND e.branch_office_id = $validUser->branch_office_id ";
        $days = [];
        $date = date('Y-m-d');
        if ($branch != 0){
            $where .= " AND e.branch_office_id = $branch";
        }
        $sql = "SELECT  distinct TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(s.credit_days||' days' AS INTERVAL),'DD-MM-YYYY') as invoice_date 
        FROM pur_orders 
        left JOIN pur_suppliers AS s
        ON pur_orders.supplier_id = s.id
        left join wms_storages e on e.id = pur_orders.storage_id
        where  '$date' >= TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(s.credit_days||' days' AS INTERVAL),'DD/MM/YYYY') 
        AND status_payment IN (0,1) 
        AND pur_orders.status = 'PEDIDO' or pur_orders.status = 'RECIBIDO' $where";
        
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $d) {
            $data[$key]['invoice_date'] = date("Y-m-d", strtotime($d['invoice_date']));
        }
        return $data;
    }

    public function getcustomerBalancetoBeat($date, $type, $branch)
    {
        $date1 = date('Y-m-d', strtotime($date));
        $validUser = Auth::getUserInfo($this->config);
        //and i.id in (3423,3509,3260)
        $where = "WHERE status in ('RECIBIDO', 'PEDIDO') and invoice_date is not null and status_payment IN (0,1)  ";
        $where .= $validUser->role_id == 1?' ':" AND e.branch_office_id = $validUser->branch_office_id ";
        if ($branch != 0){
            $where .= " AND e.branch_office_id = $branch";
        }
        if ($type == 1) {
            $where .= " AND TO_CHAR(cast(pur_orders.invoice_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') < '$date1'";
        } else if ($type == 2) {
            $where .= " AND TO_CHAR(cast(pur_orders.invoice_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') = '$date1'";
        } else if ($type == 3) {
            $where .= " AND TO_CHAR(cast(pur_orders.invoice_date as DATE) + 
            CAST(s.credit_days||' days' AS INTERVAL),'YYYY-MM-DD') >= '$date1'";
        }
        $sql = "SELECT pur_orders.id, TO_CHAR(cast(pur_orders.invoice_date as DATE) +CAST(s.credit_days||' days' AS INTERVAL),'DD/MM/YYYY') as invoice_date, pur_orders.status_payment 
        from pur_orders 
        left JOIN pur_suppliers AS s ON pur_orders.supplier_id = s.id
        left join wms_storages e on e.id = pur_orders.storage_id
        {$where}";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

    public function getToPayToBeatv2($data, $branch)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1?' ':" AND e.branch_office_id = $validUser->branch_office_id ";
        $abonado = 0;
        $acumulado = 0;
        $restante = 0;
        if ($branch != 0){
            $where .= " AND e.branch_office_id = $branch";
        }
        $sumDetaills = 0;
        foreach ($data as $i) {
            // $bulk = $i['ib'] !== null ? $i['ib'] : 0;
            $total = 0;
            // if ($bulk) {
                $sql = "SELECT sum(pur_order_details.qty * pur_order_details.price) + COALESCE(sum(pur_order_details.vat),0) as sum
                FROM pur_orders
                INNER JOIN pur_order_details on pur_order_details.po_id = pur_orders.id
                left join wms_storages e on e.id = pur_orders.storage_id
                 WHERE pur_orders.status in ('RECIBIDO', 'PEDIDO') 
                 and pur_orders.invoice_date is not null and pur_orders.status_payment IN (0,1) 
                 and pur_orders.id = {$i['id']} $where group by pur_order_details.vat";
                 
                $data = $this->db->query($sql)->fetchAll();
                foreach ($data as $key => $value) {
                    # code...
                    $sumDetaills += (($value['sum']));
                }
                
                //IMPUESTO
                $total += (($sumDetaills));
                $sumDetaills = 0;
            // }
            $a = $this->getCantidadesAbonadas($i['id'], 1) !== null ? $this->getCantidadesAbonadas($i['id'], 1) : 0;
            $abonado += floatval($a);
            $acumulado += floatval($total);
            
        }
        $r['remaining'] = floatval($acumulado) - floatval($abonado);
        return $r;
    }

    public function getCantidadesAbonadas($id, $type)
    {
        $y = date("Y");
        $date = date("$y-01-01 00:00:00.000000");

        $where = $type == 1 ?  "WHERE p.po_id = $id" : "WHERE p.payment_date >= '$date'";

        $sqlAbonado = "SELECT sum(p.amount) as abonado FROM pur_order_payments AS p {$where}";
        $dataAbonado = $this->db->query($sqlAbonado)->fetch();

        return  $dataAbonado['abonado'];
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 7 OR role_id = 17 OR role_id = 28 OR role_id = 20 OR role_id = 22)
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