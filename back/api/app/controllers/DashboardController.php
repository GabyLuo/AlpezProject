<?php

use Phalcon\Mvc\Controller;

class DashboardController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    public function generateStorageInventory () {
        $movements = $this->generateKardex();
        // var_dump($movements);
        $products = [];
        $stock = [];
        $inventory_cost = 0;
        foreach ($movements as $movement) {
            if (!in_array($movement['product_id'], $products)) {
                $lastPrice = 0;
                $productStock = 0;
                $avgPrice = 0;
                $num = 0;
                $sum =0;
                foreach ($movements as $secondMovement) {
                    if ($movement['product_id'] == $secondMovement['product_id']) {
                        if ($secondMovement['movement_type'] == 1) {
                            $productStock += $secondMovement['qty'];
                            $lastPrice = $secondMovement['unit_price'];
                            $sum += $secondMovement['unit_price'];
                            $num++;
                        } elseif ($secondMovement['movement_type'] == 2) {
                            $productStock -= $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 3){
                            $productStock = $secondMovement['qty'];
                            $lastPrice = $secondMovement['unit_price'];
                            $sum = $secondMovement['unit_price'];
                            $num = 1;
                        }
                    }
                }
                $avgPrice = $sum / $num;
                array_push($products, $movement['product_id']);
                array_push($stock, array('product_id' => $movement['product_id'],'product_status' => $movement['status_product'], 'stock' => $productStock,'last_price' => $lastPrice,$productStock,'avg_price' => $avgPrice));
            }
        }
        return $stock;
    }
    public function dayProductionCharts () {
        $datos = [];
        $sql = "SELECT distinct id,name from hrs_shifts";
        $times = $this->db->query($sql)->fetchAll();
        foreach ($times as $key => $value) {
            $production = $this->dayProductionChartsDaily($times[$key]['id']);
            array_push($datos, array('time' => $times[$key]['name'],'production' => $production ? $production : null ));
        }
        $this->content['dayProductionCharts'] = $datos;
        $this->response->setJsonContent($this->content);
        $this->response->send();  
    }
    public function dayProductionChartsDaily ($time) {
        $datos = array();
        $dia= date("Y-m-d");
        $dias=0;
        $mod_date = strtotime($dia."- $dias days");
        $fecha= date("Y-m-d",$mod_date);
        for ($i=0; $i <=7 ; $i++) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) pm
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = ".intval($time)." AND to_char(pl.scheduled_start_date, 'YYYY-MM-DD') = '$fecha'";
            $data = $this->db->query($sql)->fetchAll()[0]['pm'];

            array_push($datos, $data);
        }
        return $datos;
    }
    public function weekProductionCharts () {
        $datos = [];
        $sql = "SELECT distinct id,name from hrs_shifts";
        $times = $this->db->query($sql)->fetchAll();
        foreach ($times as $key => $value) {
            $production = $this->weekProductionChartsWeekly($times[$key]['id']);
            array_push($datos, array('time' => $times[$key]['name'],'production' => $production ? $production : null ));
        }
        $this->content['weekProductionCharts'] = $datos;
        $this->response->setJsonContent($this->content);
        $this->response->send();  
    }
    public function weekProductionChartsWeekly ($time) {
        $fechaNormal= date("Y-m-d");
        $datos = array();
        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);
    
            $fecha= date("Y",$mod_date);
            $where = "";
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) pm
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = ".intval($time)." AND date_part('week', pl.scheduled_start_date) = '$sem' AND date_part('year',pl.scheduled_start_date)='$fecha';";
            $data = $this->db->query($sql)->fetchAll()[0]['pm'];
            array_push($datos, $data ? $data : 0);
        }
        return $datos;
    }

    public function stockProducts () {
        
        $sql = "SELECT id, name from wms_products";
        $data = $this->db->query($sql)->fetchAll();

        $data_number=array();
        $data_label=array();
        foreach($data as $element){
            array_push($data_number, $this->getStock($element['id']));
            array_push($data_label, $element['name']);
        }
        
        $this->content['label_stock'] = $data_label;
        $this->content['series_stock'] = $data_number;
        $this->content['result'] = 'success';
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    private function getStock ($product_id) {
        $sql = "SELECT stock from getkardex(null, null, null, null, $product_id)";
        $data = $this->db->query($sql)->fetchAll();
        if (count($data) > 0) {
            return $data[0]['stock'];
        } else {
            return 0;
        }
    }

    public function monthProductionCharts () {
        $sql = "SELECT distinct id,name from hrs_shifts";
        $times = $this->db->query($sql)->fetchAll();
        foreach ($times as $index => $time) {
            $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
            $data[$index]['name'] = $time['name'];
            $timeId = $time['id'];

            $sql = "SELECT to_char(pl.scheduled_start_date, 'MM') as created, sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) mp
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = {$timeId} and pl.scheduled_start_date >
            date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
             GROUP BY pl.shift_id,to_char(pl.scheduled_start_date, 'MM')";
            $dataInfo = $this->db->query($sql)->fetchAll();
            foreach( $dataInfo as $row){
                if (( (int)$row['created'] - date('n')) <= 0) {
                $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['mp'];
                } else {
                    $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['mp'];
                }
            }
    }
    $this->content['monthProductionCharts'] = $data;
    $this->response->setJsonContent($this->content);
    $this->response->send();
}
    public function getGpiOne () {
        // Cajas de produccion
            $fechaNormal= date("Y-m-d");
            $mod_date = strtotime($fechaNormal."- 0 month");
            $month= date("m",$mod_date);
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) gp
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO'";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['globalProduction'] = $data[0]['gp'] ? (int) $data[0]['gp'] : 0;
            // Matutino
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) mp
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = 8 and date_part('month',pl.scheduled_start_date) = '$month'";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['morningProduction'] = $data[0]['mp'] ? (int) $data[0]['mp'] : 0;

            //Vespertino
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) ap
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = 9 and date_part('month',pl.scheduled_start_date) = '$month'";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['afternonProduction'] = $data[0]['ap'] ? (int) $data[0]['ap'] : 0;

            //Nocturno
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) np
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and shift_id = 10 and date_part('month',pl.scheduled_start_date) = '$month'";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['nightProduction'] = $data[0]['np'] ? (int) $data[0]['np'] : 0;
            //Total mes
            $sql = "SELECT sum (pl.qty_real / case when p.pieces is null then 1 else p.pieces end) tm
            from prd_orders as po
            inner join prd_lots as pl on pl.order_id = po.id
            inner join wms_products as p on p.id = pl.product_id
            where pl.status = 'FINALIZADO' and date_part('month',pl.scheduled_start_date) = '$month'";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['monthTotalProduction'] = $data[0]['tm'] ? (int) $data[0]['tm'] : 0;
            
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    // Dash de cobranza, cuentas pendientes por pagar
    public function accountsReceivable () {
        $resultado = 0;
        $sql = "SELECT i.id, i.status_payment,
        (select COALESCE((SELECT sum(sls_payments.amount) from sls_payments where sls_payments.remision_id = i.id), 0)) as abonado,
        (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)) as bulktotal
        FROM sls_invoices AS i";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $d){
            $id = $d['id'];
            $totales = $this->getImpuestos($id);
 
            $resta = $totales - $d['abonado'];
            $resultado += $resta;
        }
        $this->content['accountsReceivable'] = $resultado;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getImpuestos ($id) {
        $invoiceDetail = InvoiceDetails::find("invoice_id = $id");
        $invoiceInBulkDetail = InvoiceInBulkDetails::find("invoice_id = $id");
        $subtotal = 0;
        // $totalImpuestosTrasladados = 0;
        $totalFactura = 0;
        $totalPagos = 0;
        if ($invoiceInBulkDetail) {
            foreach ($invoiceInBulkDetail as $key => $detalle) {
                $importe = number_format($detalle->unit_price * $detalle->qty,2,'.','');
                $subtotal += $importe;
                // $totalImpuestosTrasladados += number_format($importe,2,'.','');
            }
        }
        $totalFactura = $subtotal; // + $totalImpuestosTrasladados;

        return $totalFactura;
    }
    public function monthAmounts () {

        $data[0]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
        // $data[$index]['name'] = $customer['name'];

        $sql = "SELECT to_char(a.created, 'MM') as created,sum((select COALESCE((SELECT sum(sci.price_product * sci.qty)
        from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = a.id), 0))) as montoinbulk from (
        SELECT sc.created,status,sc.id
        FROM sls_shopping_cart sc
        where sc.created >
        date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
        ) a
        WHERE a.status != 'NUEVO'
        GROUP BY to_char(a.created, 'MM')";
        $dataInfo = $this->db->query($sql)->fetchAll();
        foreach( $dataInfo as $key => $row){
            if (( (int)$row['created'] - date('n')) <= 0) {
                $data[0]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['montoinbulk'];
            } else {
                $data[0]['series'][( (int)$row['created'] - date('n'))] = (int)$row['montoinbulk'];
            }
        }
        $this->content['monthAmounts'] = $data;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function salesByCustomer () {
        $sql = "SELECT DISTINCT sc.customer_id, c.name
        from sls_shopping_cart as sc left join
        sls_customers as c on c.id = sc.customer_id  where sc.status != 'NUEVO';";
        $customers = $this->db->query($sql)->fetchAll();
        foreach ($customers as $index => $customer) {
            $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
            $data[$index]['name'] = $customer['name'];
            $customerId = $customer['customer_id'];

            $sql = "SELECT to_char(a.created, 'MM') as created,count(*),a.customer_id from (
                SELECT sc.created,c.id as customer_id,status
                FROM sls_shopping_cart sc
                left join sls_customers as c on c.id = sc.customer_id
                where sc.created >
                date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
                ) a
                WHERE a.status != 'NUEVO' AND a.customer_id = {$customerId}
                GROUP BY a.customer_id,to_char(a.created, 'MM')";
            $dataInfo = $this->db->query($sql)->fetchAll();
            foreach( $dataInfo as $row){
                if (( (int)$row['created'] - date('n')) <= 0) {
                $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['count'];
                } else {
                    $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['count'];
                }
            }
    }
    $this->content['salesByCustomer'] = $data;
    $this->response->setJsonContent($this->content);
    $this->response->send();
}
    public function salesBySeller () {
        $sql = "SELECT DISTINCT sc.user_id, c.nickname
        from sls_shopping_cart as sc left join
        sys_users as c on c.id = sc.user_id  where sc.status != 'NUEVO';";
        $sellers = $this->db->query($sql)->fetchAll();
        foreach ($sellers as $index => $seller) {
            // array_push($datos, array('customer' => $customer[$key]['name'],'counts' => $this->getCountSalesByCustomer($customer[$key]['customer_id'])));
            $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
            $data[$index]['name'] = $seller['nickname'];
            $sellerId = $seller['user_id'];

            $sql = "SELECT to_char(a.created, 'MM') as created,sum((select COALESCE((SELECT sum(sci.price_product * sci.qty)
            from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = a.id), 0))) as montoinbulk,a.user_id from (
                            SELECT sc.created,sc.user_id,status,sc.id
                            FROM sls_shopping_cart sc
                            left join sys_users AS u ON u.id = sc.user_id
                            where sc.created >
                            date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
                            ) a
                            WHERE a.status != 'NUEVO' AND a.user_id = {$sellerId}
                            GROUP BY a.user_id,to_char(a.created, 'MM')";
            $dataInfo = $this->db->query($sql)->fetchAll();
            foreach( $dataInfo as $row){
                if (( (int)$row['created'] - date('n')) <= 0) {
                $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['montoinbulk'];
                } else {
                    $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['montoinbulk'];
                }
                // var_dump(( (int)$row['created'] - date('n'))+11);
            }
        }
        $this->content['salesBySeller'] = $data;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    // public function salesBySeller () {
    //     $datos = [];
    //     $sql = "SELECT DISTINCT sc.user_id, c.nickname
    //     from sls_shopping_cart as sc left join
    //     sys_users as c on c.id = sc.user_id  where sc.status != 'NUEVO';";
    //     $sellers = $this->db->query($sql)->fetchAll();
    //     foreach ($sellers as $key => $value) {
    //         array_push($datos, array('seller' => $sellers[$key]['nickname'],'counts' => $this->getCountSalesBySeller($sellers[$key]['user_id']) ? $this->getCountSalesBySeller($sellers[$key]['user_id']):null ));
    //     }
    //     $this->content['salesBySeller'] = $datos;
    //     $this->response->setJsonContent($this->content);
    //     $this->response->send();  
    // }
    public function salesBySellerWeekly () {
        $datos = [];
        $sql = "SELECT DISTINCT sc.user_id, c.nickname
        from sls_shopping_cart as sc left join
        sys_users as c on c.id = sc.user_id  where sc.status != 'NUEVO';";
        $sellers = $this->db->query($sql)->fetchAll();
        foreach ($sellers as $key => $value) {
            array_push($datos, array('seller' => $sellers[$key]['nickname'],'counts' => $this->getCountSalesBySellerWeekly($sellers[$key]['user_id']) ? $this->getCountSalesBySellerWeekly($sellers[$key]['user_id']):null ));
        }
        $this->content['salesBySellerWeekly'] = $datos;
        $this->response->setJsonContent($this->content);
        $this->response->send();  
    }
    public function salesBySellerDaily () {
        $datos = [];
        $sql = "SELECT DISTINCT sc.user_id, c.nickname
        from sls_shopping_cart as sc left join
        sys_users as c on c.id = sc.user_id  where sc.status != 'NUEVO';";
        $sellers = $this->db->query($sql)->fetchAll();
        foreach ($sellers as $key => $value) {
            array_push($datos, array('seller' => $sellers[$key]['nickname'],'counts' => $this->getCountSalesBySellerDaily($sellers[$key]['user_id']) ? $this->getCountSalesBySellerDaily($sellers[$key]['user_id']):null ));
        }
        $this->content['salesBySellerDaily'] = $datos;
        $this->response->setJsonContent($this->content);
        $this->response->send();  
    }
    public function getCountSalesBySeller ($sellerId) {
        $fechaNormal= date("Y-m-d");
        $datos = array();
        for ($i=0; $i <=12 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fecha= date("Y",$mod_date);
            $mes= date("m",$mod_date);
    
            $fecha= date("Y",$mod_date);
            $where = "";
            $sql = "SELECT sum((select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0))) as montoinbulk
            FROM sls_shopping_cart AS sc
            inner JOIN sys_users AS u ON u.id = sc.user_id
            WHERE status != 'NUEVO' AND sc.user_id = $sellerId AND date_part('month', sc.created) = '$mes' AND date_part('year',sc.created)='$fecha';";
            $data = $this->db->query($sql)->fetchAll()[0]['montoinbulk'];
            array_push($datos, $data);
        }
        return $datos;
    }
    public function getCountSalesBySellerWeekly ($sellerId) {
        $fechaNormal= date("Y-m-d");
        $datos = array();
        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);
    
            $fecha= date("Y",$mod_date);
            $where = "";
            $sql = "SELECT sum((select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0))) as montoinbulk
            FROM sls_shopping_cart AS sc
            inner JOIN sys_users AS u ON u.id = sc.user_id
            WHERE status != 'NUEVO' AND sc.user_id = $sellerId AND sc.user_id = $sellerId AND date_part('week', sc.created) = '$sem' AND date_part('year',sc.created)='$fecha';";
            $data = $this->db->query($sql)->fetchAll()[0]['montoinbulk'];
            array_push($datos, $data);
        }
        return $datos;
    }
    public function getCountSalesBySellerDaily ($sellerId) {
        $datos = array();
        $dia= date("Y-m-d");
        $dias=0;
        $mod_date = strtotime($dia."- $dias days");
        $fecha= date("Y-m-d",$mod_date);
        for ($i=0; $i <=7 ; $i++) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $where = "";
            $sql = "SELECT sum((select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0))) as montoinbulk
            FROM sls_shopping_cart AS sc
            inner JOIN sys_users AS u ON u.id = sc.user_id
            WHERE status != 'NUEVO' AND sc.user_id = $sellerId AND sc.user_id = $sellerId AND to_char(sc.created, 'YYYY-MM-DD') = '$fecha'";
            $data = $this->db->query($sql)->fetchAll()[0]['montoinbulk'];
            array_push($datos, $data);
        }
        return $datos;
    }
    public function getQtyByStatusShoppingCart () {
        $array1 = array();
        $datos = [];
        $estatus = ['SOLICITADO','AUTORIZADO','REMISIONADO'];
        foreach ($estatus as $value) {
            array_push($datos, array('status' => $value,'counts' => $this->getCountByStatus($value) ? $this->getCountByStatus($value)['data']:null ));
        }
        $this->content['monthCountByStatusShoppingCart'] = $datos;
        $this->response->setJsonContent($this->content);
        $this->response->send(); 
    }

    public function getCountByStatus ($status) {
        $fechaNormal= date("Y-m-d");
        $datos = array();
        for ($i=0; $i <=12 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fecha= date("Y",$mod_date);
            $mes= date("m",$mod_date);
    
            $fecha= date("Y",$mod_date);
            $where = "";
            $sql = "select count(id) as count
            from sls_shopping_cart as sc where status = '$status' AND date_part('month', sc.created) = '$mes' AND date_part('year',sc.created)='$fecha';";
            $data = $this->db->query($sql)->fetchAll()[0]['count'];
            array_push($datos, $data);
        }
        $array['data'] = $datos;
        return $array;
    }
    public function getShoppingCartKpis () {
        $content = $this->content;
        $pendientes = 0;
        $invoices = 0;
        $actualmonth = 0;
        $sql = "SELECT count(id) as pendientes,
        (select count(id) as enviados from sls_shopping_cart where status = 'REMISIONADO'),
        (select count(id) as autorizados from sls_shopping_cart where status = 'AUTORIZADO'),
        (select count(id) as nuevos from sls_shopping_cart where status = 'NUEVO'),
        (select count(id) as solicitados from sls_shopping_cart where status = 'SOLICITADO'),
        (select count(id) as parciales from sls_shopping_cart where status = 'PARCIAL'),
        (select count(id) as entregados from sls_shopping_cart where status = 'ENTREGADO')
        from sls_shopping_cart";
        $data = $this->db->query($sql)->fetchAll();
        if (count($data) > 0) {
            $pendientes = $data[0]['pendientes'];
            $enviados = $data[0]['enviados'];
            $autorizados = $data[0]['autorizados'];
            $nuevos = $data[0]['nuevos'];
            $solicitados = $data[0]['solicitados'];
            $parciales = $data[0]['parciales'];
            $entregados = $data[0]['entregados'];
        }

        $fechaNormal= date("Y-m-d");
        $mod_date = strtotime($fechaNormal);
        $fecha= date("Y",$mod_date);
        $mes= date("m",$mod_date);
        $sql2 = "SELECT count(id) as actual_month from sls_shopping_cart as s
        WHERE date_part('month', created) = '$mes' AND date_part('year',created) ='$fecha'";
        $data2 = $this->db->query($sql2)->fetchAll();

        if (count($data2) > 0) {
            $actualmonth = $data2[0]['actual_month'];
        }
        $content['pendientes'] = $pendientes;
        $content['enviados'] = $enviados;
        $content['actualmonth'] = $actualmonth;
        $content['autorizados'] = $autorizados;
        $content['nuevos'] = $nuevos;
        $content['solicitados'] = $solicitados;
        $content['parciales'] = $parciales;
        $content['entregados'] = $entregados;
       $this->response->setJsonContent($content);
       $this->response->send();
    }
    public function generateKardex ()
    {
        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product,m.date as date2, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_products AS p ON p.id = md.product_id
        WHERE m.status = 'EJECUTADO' ";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, m.date as date2, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_products AS p ON p.id = md.product_id
        WHERE m.status = 'EJECUTADO'";
        $sql .= ") AS QUERY ORDER BY date2 ASC, $caseOrder, foli ASC";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }
    public function getOCs () {
        $content = $this->content;

        // $sql = 
        //$weeklyArribos = $this->db->query($sql)->fetchAll();

        $content['embarco'] = $embarco;
        $content['weeklyArribos'] = $weeklyArribos;
       $this->response->setJsonContent($content);
       $this->response->send();   
    }
    public function getPurchases($startDate) {
        $content = $this->content;
        $sql= "SELECT count(po.id) as ocs
        FROM pur_orders as po
        WHERE po.status != 'RECIBIDO'; ";
        $ocs = $this->db->query($sql)->fetchAll();
        $sql = "SELECT count(*) FROM wms_products where id not in (SELECT product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_products AS p ON p.id = md.product_id and p.active = true
        WHERE m.status = 'EJECUTADO' 
UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_products AS p ON p.id = md.product_id
        WHERE m.status = 'EJECUTADO'
) AS QUERY ORDER BY date ASC, CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC, foli ASC)";
        $noStock = $this->db->query($sql)->fetchAll();
        $noStock2 = $noStock[0]['count'];
        $details = $this->generateStorageInventory();
        $inventory_cost =0;
        $inventory_cost_avg =0;
        foreach ($details as $key => $value) {
            // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
            $inventory_cost += $details[$key]['stock'] * $details[$key]['last_price'];
            $inventory_cost_avg += $details[$key]['stock'] * $details[$key]['avg_price'];
            if ($details[$key]['stock'] <= 0) {
                $noStock2++;
            }
        }
        
        $sql = "SELECT count(m.id) as entrydays
        FROM wms_movements as m
        WHERE m.type_id = 1 AND m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.created::timestamp::date = '".$sDate."'";
        } else {
            $sql .= "AND m.created::timestamp::date = CURRENT_DATE;";
        }
        $entryDays = $this->db->query($sql)->fetchAll();
        $sql= "select count(id) as embarcos from pur_orders where status = 'EMBARCADO';";
        $embarco = $this->db->query($sql)->fetchAll();

        $fechaNormal= date("Y-m-d");
        $fr = strtotime($fechaNormal."- 0 week");
        $sem= date("W",$fr);
        $year= date("Y",$fr);
        $sql= "SELECT count(po.id) as embarcados
        from pur_order_history as poh
        inner join pur_orders as po on po.id = poh.order_id
        where poh.status = 'ARRIBO' and po.status = 'ARRIBO' AND date_part('week', poh.created) = '$sem' AND date_part('year',poh.created)='$year'";
        $weeklyArribos = $this->db->query($sql)->fetchAll();

        
        $content['embarco'] = $embarco[0]['embarcos'];
        $content['weeklyArribos'] = $weeklyArribos[0]['embarcados'];
        $content['ocs'] = $ocs[0]['ocs'];
        $content['noStock'] = $noStock2;
        $content['entryDays'] = $entryDays[0]['entrydays'];
        $content['inventaryCost'] = $inventory_cost;
        $content['inventaryCostAVG'] = $inventory_cost_avg;
       $this->response->setJsonContent($content);
       $this->response->send();
    }
    public function getInventoryCostDaily(){
        $content = $this->content;
        $content['inventory_daily'] = $this->getInventoryCost();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getStockCostDaily(){
        $content = $this->content;
        $content['inventory_daily'] = $this->getStockCost();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getStockCostWeekly(){
        $content = $this->content;
        $content['inventory_daily'] = $this->getStockCostWeek();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getStockCostAnual(){
        $content = $this->content;
        $content['inventory_daily'] = $this->getStockCostA();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getInventoryCostWeekly(){
        $content = $this->content;
        $content['inventory_weekly'] = $this->getInventoryCostWeekly2();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getInventoryCostAnual(){
        $content = $this->content;
        $content['inventory_anual'] = $this->getInventoryCostAnual2();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getFacturedAnual(){
         $content = $this->content;
         $content['facturado'] = $this->getFacturedAnualFacturado();
        $this->response->setJsonContent($content);
        $this->response->send();

    }
    public function getInventoryCostAnual2 ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;

         $array = array();
         $array1 = array();
         $inventoryAmounts = array();

         $fechaNormal= date("Y-m-d");

         for ($i=0; $i <=12 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fecha= date("Y",$mod_date);
            $mes= date("m",$mod_date);
         $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.movement_id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$mes' AND date_part('year',m.date)='$fecha'";
         $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
 
         $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$mes' AND date_part('year',m.date)='$fecha'";
         $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
         $movements = $this->db->query($sql)->fetchAll();
         $products = [];
         $stock = [];
         foreach ($movements as $movement) {
             if (!in_array($movement['product_id'], $products)) {
                 $lastPrice = 0;
                 $productStock = 0;
                 $avgcost = 0;
                 $num = 0;
                 foreach ($movements as $secondMovement) {
                     if ($movement['product_id'] == $secondMovement['product_id']) {
                         if ($secondMovement['movement_type'] == 1) {
                             $productStock += $secondMovement['qty'];
                             $lastPrice += $secondMovement['unit_price'];
                             $num++;
                         } elseif ($secondMovement['movement_type'] == 2) {
                             $productStock -= $secondMovement['qty'];
                         } elseif ($secondMovement['movement_type'] == 3){
                             $productStock = $secondMovement['qty'];
                             $lastPrice = $secondMovement['unit_price'];
                             $num=1;
                         }
                     }
                 }
                 if ($num > 0) {
                    $avgcost = $lastPrice / $num;
                    // $avgcost = number_format(floatval($avgcost / 1000),2,'.',',');
                 }
                 $avgcost = 0;
                 array_push($products, $movement['product_id']);
                 array_push($stock, array('stock' => $productStock,'last_price' => $avgcost));
             }
         }
         $inventory_cost = 0;
         foreach ($stock as $key => $value) {
            // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
            $inventory_cost += $stock[$key]['stock'] * $stock[$key]['last_price'];
        }
        $inventoryAmounts[$i] = $inventory_cost;
        }
        // $maximo = max($inventoryAmounts);
        $dias = array();
        $dias['inventory_amounts']=$inventoryAmounts;
        $dias['maximo'] = 20;

        return $dias;
    }
    public function getInventoryCostWeekly2 ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;

         $array = array();
         $inventoryAmounts = array();

         $fechaNormal= date("Y-m-d");

         for ($i=0; $i <=5 ; $i++) {
        $mod_date = strtotime($fechaNormal."- $i week");
        $fr = strtotime($fechaNormal."- $i week");
        $sem= date("W",$fr);

        $fecha= date("Y",$mod_date);
         $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.movement_id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
         $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
 
         $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
         $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
         $movements = $this->db->query($sql)->fetchAll();
         $products = [];
         $stock = [];
         foreach ($movements as $movement) {
             if (!in_array($movement['product_id'], $products)) {
                 $lastPrice = 0;
                 $productStock = 0;
                 $avgcost = 0;
                 $num = 0;
                 foreach ($movements as $secondMovement) {
                     if ($movement['product_id'] == $secondMovement['product_id']) {
                         if ($secondMovement['movement_type'] == 1) {
                             $productStock += $secondMovement['qty'];
                             $lastPrice += $secondMovement['unit_price'];
                             $num++;
                         } elseif ($secondMovement['movement_type'] == 2) {
                             $productStock -= $secondMovement['qty'];
                         } elseif ($secondMovement['movement_type'] == 3){
                             $productStock = $secondMovement['qty'];
                             $lastPrice = $secondMovement['unit_price'];
                             $num = 1;
                         }
                     }
                 }
                 $avgcost = $lastPrice / $num;
                 array_push($products, $movement['product_id']);
                 array_push($stock, array('stock' => $productStock,'last_price' => $avgcost));
             }
         }
         $inventory_cost = 0;
         foreach ($stock as $key => $value) {
            // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
            $inventory_cost += $stock[$key]['stock'] * $stock[$key]['last_price'];
        }
        $inventoryAmounts[$i] = $inventory_cost;
        $array1[$i]='Sem'.$sem;
        }
        $maximo = max($inventoryAmounts);
        $dias = array();
        $dias['inventory_amounts']=$inventoryAmounts;
        $dias['week']=$array1;
        $dias['maximo'] = $maximo;

        return $dias;
    }
    public function getInventoryCost ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;

         $array = array();
         $inventoryAmounts = array();

         $dia= date("Y-m-d");
         $dias=0;
         $mod_date = strtotime($dia."- $dias days");
         $fecha= date("Y-m-d",$mod_date);

         for ($i=0; $i <=7 ; $i++) {
        $mod_date = strtotime($dia."- $i days");
         $fecha= date("Y-m-d",$mod_date);
         $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.movement_id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
         $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
 
         $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
         FROM wms_movement_details AS md
         JOIN wms_movements AS m ON m.id = md.movement_id
         JOIN wms_products AS p ON p.id = md.product_id
         WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
         $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
         $movements = $this->db->query($sql)->fetchAll();
         $products = [];
         $stock = [];
         foreach ($movements as $movement) {
             if (!in_array($movement['product_id'], $products)) {
                 $avgcost = 0;
                 $num = 0;
                 $lastPrice = 0;
                 $productStock = 0;
                 foreach ($movements as $secondMovement) {
                     if ($movement['product_id'] == $secondMovement['product_id']) {
                         if ($secondMovement['movement_type'] == 1) {
                             $productStock += $secondMovement['qty'];
                             // echo $secondMovement['unit_price'].'-'.$secondMovement['product_id'].'-';
                             $lastPrice += $secondMovement['unit_price'];
                             $num++;
                         } elseif ($secondMovement['movement_type'] == 2) {
                             $productStock -= $secondMovement['qty'];
                         } elseif ($secondMovement['movement_type'] == 3){
                             $productStock = $secondMovement['qty'];
                             $lastPrice = $secondMovement['unit_price'];
                             $num = 1;
                         }
                     }
                 }
                 // echo ' NUEVO ';
                 $avgcost = $lastPrice / $num;
                 array_push($products, $movement['product_id']);
                 array_push($stock, array('stock' => $productStock,'last_price' => $avgcost));
             }
         }
         $inventory_cost = 0;
         foreach ($stock as $key => $value) {
            // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
            $inventory_cost += $stock[$key]['stock'] * $stock[$key]['last_price'];
        }
        $inventoryAmounts[$i] = $inventory_cost;
        }
        // var_dump($inventoryAmounts);
        $maximo = max($inventoryAmounts);
        $dias = array();

        $dias['inventory_amounts']=$inventoryAmounts;
        $dias['maximo'] = $maximo;

        return $dias;
    }

    public function getStockCost () {
        // Necesito obtener la existencia de inventario diario

        $array = array();
        $inventoryAmounts = array();

        $dia= date("Y-m-d");
        $dias=0;
        $mod_date = strtotime($dia."- $dias days");
        $fecha= date("Y-m-d",$mod_date);

        $array_stock = [];
        $array_stock2 = [];
        $array_stock3 = [];
        for ($i=7; $i >=0 ; $i--) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$fecha', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock, $productStock);
        }
        $dias = array();
        $dias['inventory_amountsD']=$array_stock;

        for ($i=7; $i >=0 ; $i--) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$fecha', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock2, $productStock);
        }
        $dias['inventory_amountsP']=$array_stock2;

        for ($i=7; $i >=0 ; $i--) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND to_char(date, 'YYYY-MM-DD') <= '$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$fecha', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock3, $productStock);
        }
        $dias['inventory_amountsR']=$array_stock3;
        // $maximo = max($array_stock);
        
        // $dias['maximo'] = $maximo;

        return $dias;
    }

    public function getStockCostWeek () {
        $content = $this->content;
        $array = array();
        $array1 = array();
        $inventoryAmounts = array();
        $fechaNormal= date("Y-m-d");
        $array_stock = [];
        $array_stock2 = [];
        $array_stock3 = [];

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock, $productStock);
            $array1[$i]='Sem'.$sem;
        }
        $maximo = max($array_stock);
        $dias = array();

        $dias['inventory_amountsD']=$array_stock;

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock2, $productStock);
            // $array1[$i]='Sem'.$sem;
        }
        $dias['inventory_amountsP']=$array_stock2;

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND date_part('week', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock3, $productStock);
            // $array1[$i]='Sem'.$sem;
        }
        $dias['inventory_amountsR']=$array_stock3;
        // $dias['maximo'] = $maximo;
        $dias['week']=$array1;

        return $dias;
    }

    public function getStockCostA () {
        $content = $this->content;
        $array = array();
        $array1 = array();
        $inventoryAmounts = array();
        $fechaNormal= date("Y-m-d");
        $array_stock = [];
        $array_stock2 = [];
        $array_stock3 = [];

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fr = strtotime($fechaNormal."- $i month");
            $sem= date("m",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 222
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock, $productStock);
            $array1[$i]='Sem'.$sem;
        }
        $maximo = max($array_stock);
        $dias = array();

        $dias['inventory_amountsD']=$array_stock;

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fr = strtotime($fechaNormal."- $i month");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 223
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock2, $productStock);
            // $array1[$i]='Sem'.$sem;
        }
        $dias['inventory_amountsP']=$array_stock2;

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fr = strtotime($fechaNormal."- $i month");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            //
            $dd = date('Y-m-d', $mod_date);

            $sql = "SELECT distinct product_id FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.movement_id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
     
            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,1) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
             FROM wms_movement_details AS md
             JOIN wms_movements AS m ON m.id = md.movement_id
             JOIN wms_products AS p ON p.id = md.product_id join wms_lines on wms_lines.id = p.line_id and p.line_id = 224
             WHERE m.status = 'EJECUTADO' AND date_part('month', m.date) <= '$sem' AND date_part('year',m.date)='$fecha'";
            $sql .= ") AS QUERY";
            $products = $this->db->query($sql)->fetchAll();
            $storages = Storages::find();
            $productStock = 0;
            foreach ($products as $product) {
                $prd = $product['product_id'];
                foreach ($storages as $storage) {
                    $sql_stock = "SELECT product_id, stock from getkardex(null, '$dd', null, $storage->id, $prd) order by idx desc limit 1";
                    $data_stock = $this->db->query($sql_stock)->fetchAll();
                    if (count($data_stock) > 0) {
                        $st = $data_stock[0]['stock'];
                        $productStock = $productStock + $st;
                    }
                }
            }
            $productStock = number_format(floatval($productStock/1000),2,'.','');
            array_push($array_stock3, $productStock);
            // $array1[$i]='Sem'.$sem;
        }
        $dias['inventory_amountsR']=$array_stock3;
        // $dias['maximo'] = $maximo;
        $dias['week']=$array1;

        return $dias;
    }

    public function suma($arre,$value){
        $sum=0;
        if (sizeof($arre)>0) {
            # code...
            for ($i=0; $i <= sizeof($arre) -1; $i++) {
            # code...
                $sum=$sum +$arre[$i];
            }
        }
        return round($sum,0);
    }
    public function sumaAnual($arre,$value){
        $sum=0;
        if (sizeof($arre)>0) {
            # code...
            for ($i=0; $i <= sizeof($arre) -1; $i++) {
            # code...
                $sum=$sum +$arre[$i];
            }
        }
        return round($sum,0);
    }
    public function suma2($arre){
        $suma=0;
        if (sizeof($arre)>0) {
            # code...
            for ($i=0; $i <= sizeof($arre) -1; $i++) {
            # code...
                $suma= $suma + $arre[$i];
            }
        }
        return round(($suma / 1000),1);
    }
    public function suma3($arre){
        $suma=0;
        if (sizeof($arre)>0) {
            # code...
            for ($i=0; $i <= sizeof($arre) -1; $i++) {
            # code...
                $suma= $suma + $arre[$i];
            }
        }
        return round(($suma / 1000000),1);
    }
    public function getFacturedSemFacturado ()
    {
        $content = $this->content;

         $array = array();
         $array0 = array();
          $array1 = array();
           $array2 = array();
            $array3 = array();
             $array4 = array();
              $array5 = array();
               $array6 = array();
                $array7 = array();

                $fechaNormal= date("Y-m-d");
                for ($i=0; $i <=4 ; $i++) {
                    # code...
                 //   $l=$i +1;
                 $mod_date = strtotime($fechaNormal."- $i week");
                 $fr = strtotime($fechaNormal."- $i week");
                 $sem= date("W",$fr);
                 //var_dump($sem);

                // $mod_date2 = strtotime($fechaNormal."- $l month");
                $fecha= date("Y",$mod_date);
                /// DOCUMENTADO Y TIMBRADO
                $sql1 = "select week, (sum(cash) * 1.16) as amount from
                        (
                        select date_part('week', i.invoice_date) as week, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                        from sls_invoices i
                        left join sls_invoice_laminate_details l
                        on i.id = l.invoice_id
                        left join sls_invoice_in_bulk_details b
                        on i.id = b.invoice_id
                        left join sls_invoice_details d
                        on i.id = d.invoice_id
                        left join wms_bales bl
                        on d.bale_id = bl.id
                        where i.status='DOCUMENTADO' and i.status_timbrado = 1 and  date_part('week',i.invoice_date)='$sem' 
                        and  date_part('year',i.invoice_date)='$fecha' 
                        ) as data
                        group by week
                        order by week desc";
                       $exe1 = $this->db->query($sql1)->fetchAll();


                            # code...
                               # code...
                               if ($exe1 and sizeof($exe1)>0) {
                            # code...
                               $array[$i]=round(($exe1[0]['amount']/1000),3);

                               }else{
                                    $array[$i]=0.0;

                               }
                               $array1[$i]="Sem".$sem;
                // DOCUMENTADO PERO NO TIMBRADO
                 $sql2 = "select week, (sum(cash) *1.16) as amount from
                            (
                            select date_part('week', i.documentation_date) as week, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                            from sls_invoices i
                            left join sls_invoice_laminate_details l
                            on i.id = l.invoice_id
                            left join sls_invoice_in_bulk_details b
                            on i.id = b.invoice_id
                            left join sls_invoice_details d
                            on i.id = d.invoice_id
                            left join wms_bales bl
                            on d.bale_id = bl.id
                            where i.status='DOCUMENTADO' and i.status_timbrado = 0 and  date_part('week',i.documentation_date)='$sem' 
                            and  date_part('year',i.documentation_date)='$fecha' 
                            ) as data
                            group by week
                            order by week desc";
                    $exe2 = $this->db->query($sql2)->fetchAll();
                       if ($exe2 and sizeof($exe2)>0) {
                            $array2[$i]=round(($exe2[0]['amount']/1000),3);

                       }else{
                            $array2[$i]=0.0;

                       }
                       /// TOTAL SIN IIMPORTAR STATUS TIMBRADO
                     $sql3 = "select week, (sum(cash) * 1.16) as amount from
                            (
                            select date_part('week', i.documentation_date) as week, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                            from sls_invoices i
                            left join sls_invoice_laminate_details l
                            on i.id = l.invoice_id
                            left join sls_invoice_in_bulk_details b
                            on i.id = b.invoice_id
                            left join sls_invoice_details d
                            on i.id = d.invoice_id
                            left join wms_bales bl
                            on d.bale_id = bl.id
                            where i.status='DOCUMENTADO'  and  date_part('week',i.documentation_date)='$sem' 
                            and  date_part('year',i.documentation_date)='$fecha' 
                            ) as data
                            group by week
                            order by week desc";
                           $exe3 = $this->db->query($sql3)->fetchAll();

                           if ($exe3 and sizeof($exe3)>0) {
                                # code...
                                   $array3[$i]=round(($exe3[0]['amount']/1000),3);

                                   }else{
                                     $array3[$i]=0.0;

                                   }

                     $sql4= "select sum(qty) as tot from  (
                                    select sum( pa.amount) as qty 
                                    from sls_invoices sin 
                                    inner join sls_payments pa 
                                    on sin.id= pa.remision_id
                                    where sin.status_payment  in (1,2)
                                    and date_part('week',pa.payment_date) = '$sem' 
                                    and date_part('year',pa.payment_date)='$fecha'
                                    group by pa.remision_id, date_part('month',pa.payment_date)) as tab;";

                                    $exe4 = $this->db->query($sql4)->fetchAll();

                                       if ($exe4 and sizeof($exe4)>0) {
                                            $array4[$i]=round(($exe4[0]['tot']/1000),3);

                                       }else{
                                            $array4[$i]=0.0;

                                       }


                }
                $maximo = max(max($array),max($array2),max($array3),max($array4));



                 $dias = array();

                $dias['week']=$array1;
                 $dias['amountTimbrado']=$array;
                 $dias['amountDocumentado']=$array2;
                 $dias['amountTotal']=$array3;
                 $dias['amountPagado']=$array4;
                $dias['maximo'] = $maximo;
                 //var_dump($dias);
                 //die();

                return $dias;
    }
    function in_array_field($field,$array) {
        if (sizeof($array)>0) {
            # code...
                if ($array['week']== $field) {
                    # code...
                    return true;
                }else{
                    return false;
                }
        } else{
            return false;
        }

    }
    public function getFacturedAnualPagado(){
        $content = $this->content;

        $array = array();
         $array0 = array();
          $array1 = array();

         $fechaNormal= date("Y-m-d");
         for ($i=0; $i <=12 ; $i++) {
             # code...
            $l=$i +1;
          $mod_date = strtotime($fechaNormal."- $i month");
         $fecha= date("Y",$mod_date);
         $mes= date("m",$mod_date);
         $j=$i;
          $sql= "select sum(qty) as tot from    (
            select sum( pa.amount) as qty 
            from sls_invoices sin 
            inner join sls_payments pa 
            on sin.id= pa.remision_id
            where sin.status_payment  in (1,2)
            and date_part('month',pa.payment_date) = '$mes' 
            and date_part('year',pa.payment_date)='$fecha'
            group by pa.remision_id, date_part('month',pa.payment_date)) as tab;";

                 $exe1 = $this->db->query($sql)->fetchAll();
                 //var_dump($exe1):

                 if ($exe1) {
                     # code...
                   //array_push($array, $exe1[$i]['qty']);
                    $array[$i]= round(($exe1[0]['tot']/1000000),1);


                        //array_push($array, $exe1[$j]['qty']);

                }else{
                     $array[$i]=0.0;

                }

             }

        return $array;

    }
    public function getFacturedAnualFacturado ()
    {
        $content = $this->content;

         $array = array();
         $array0 = array();
          $array1 = array();
           $array2 = array();
            $array3 = array();
             $array4 = array();
              $array5 = array();
               $array6 = array();
                $array7 = array();


         $fechaNormal= date("Y-m-d");
         $fechaX= date("Y");
         for ($i=0; $i <=12 ; $i++) {
             # code...
            $l=$i +1;
          $mod_date = strtotime($fechaNormal."- $i month");
         // $mod_date2 = strtotime($fechaNormal."- $l month");
         $fecha= date("Y",$mod_date);
         $mes= date("m",$mod_date);
         // var_dump($fecha);
         // LAMINADO
         $j=$i;

         //TIMBRADO
                 $sql1 = "select month, (sum(cash) * 1.16) as amount from
                 (
                         select date_part('month', i.invoice_date) as month, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                         from sls_invoices i
                         left join sls_invoice_laminate_details l
                         on i.id = l.invoice_id
                         left join sls_invoice_in_bulk_details b
                         on i.id = b.invoice_id
                         left join sls_invoice_details d
                         on i.id = d.invoice_id
                         left join wms_bales bl
                         on d.bale_id = bl.id
                         where i.status='DOCUMENTADO' and i.status_timbrado = 1 and  date_part('month',i.invoice_date)='$mes' 
                         and  date_part('year',i.invoice_date)='$fecha' 
                         ) as data
                         group by month
                          order by month desc";
                         $exe1 = $this->db->query($sql1)->fetchAll();

                        if ($exe1 and sizeof($exe1)>0) {
                             $array[$i]= round(($exe1[0]['amount']/1000000),1);

                        }else{
                             $array[$i]=0.0;
                        }

                /// PAGADO
                $sql2= "select sum(qty) as tot from  (
                            select sum( pa.amount) as qty 
                            from sls_invoices sin 
                            inner join sls_payments pa 
                            on sin.id= pa.remision_id
                            where sin.status_payment  in (1,2)
                            and date_part('month',pa.payment_date) = '$mes' 
                            and date_part('year',pa.payment_date)='$fecha'
                            group by pa.remision_id, date_part('month',pa.payment_date)) as tab;";
                            $exe2 = $this->db->query($sql2)->fetchAll();

                         if ($exe2 and sizeof($exe2)>0) {
                             $array2[$i]= round(($exe2[0]['tot']/1000000),1);

                            }else{
                                 $array2[$i]=0.0;
                            }

                // SOLO DOCUMENTADO
                  $sql3 = "select month, (sum(cash) * 1.16) as amount from
                         (
                         select date_part('month', i.documentation_date) as month, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                         from sls_invoices i
                         left join sls_invoice_laminate_details l
                         on i.id = l.invoice_id
                         left join sls_invoice_in_bulk_details b
                         on i.id = b.invoice_id
                         left join sls_invoice_details d
                         on i.id = d.invoice_id
                         left join wms_bales bl
                         on d.bale_id = bl.id
                         where i.status='DOCUMENTADO' and i.status_timbrado = 0 and  date_part('month',i.documentation_date)='$mes' 
                         and  date_part('year',i.documentation_date)='$fecha' 
                         ) as data
                         group by month
                         order by month desc";
                         $exe3 = $this->db->query($sql3)->fetchAll();

                        if ($exe3 and sizeof($exe3)>0) {
                             $array3[$i]= round(($exe3[0]['amount']/1000000),1);

                        }else{
                             $array3[$i]=0.0;
                        }

                    /// TOTAL SIN IMPORTAR SI ES TIMB O NO
                        $sql4 = "select month, (sum(cash) * 1.16) as amount from
                         (
                         select date_part('month', i.documentation_date) as month, COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                         from sls_invoices i
                         left join sls_invoice_laminate_details l
                         on i.id = l.invoice_id
                         left join sls_invoice_in_bulk_details b
                         on i.id = b.invoice_id
                         left join sls_invoice_details d
                         on i.id = d.invoice_id
                         left join wms_bales bl
                         on d.bale_id = bl.id
                         where i.status='DOCUMENTADO'  and  date_part('month',i.documentation_date)='$mes' 
                         and  date_part('year',i.documentation_date)='$fecha' 
                         ) as data
                         group by month
                         order by month desc";
                       $exe4 = $this->db->query($sql4)->fetchAll();

                        if ($exe4 and sizeof($exe4)>0) {
                             $array4[$i]= round(($exe4[0]['amount']/1000000),1);

                        }else{
                             $array4[$i]=0.0;
                        }

                        // este año


         }

           $sqlt= "select sum(qty) as tot from  (
                            select sum( pa.amount) as qty 
                            from sls_invoices sin 
                            inner join sls_payments pa 
                            on sin.id= pa.remision_id
                            where sin.status_payment  in (1,2)
                            and date_part('year',pa.payment_date)='$fechaX') as tab;";
                            $exe6 = $this->db->query($sqlt)->fetchAll();

                         // if ($exe6 and sizeof($exe6)>0) {
                         //     $array6[$i]= round(($exe6[0]['tot']/1000000),1);

                         //    }else{
                         //         $array6[$i]=0.0;
                         //    }
                            // var_dump($exe6[0]['tot']);

                $sqltv = "select (sum(cash) * 1.16) as amount from
                         (
                         select  COALESCE(l.qty*l.unit_price, 0) + COALESCE(b.qty*b.unit_price, 0) + COALESCE(bl.qty*d.unit_price, 0) as cash
                         from sls_invoices i
                         left join sls_invoice_laminate_details l
                         on i.id = l.invoice_id
                         left join sls_invoice_in_bulk_details b
                         on i.id = b.invoice_id
                         left join sls_invoice_details d
                         on i.id = d.invoice_id
                         left join wms_bales bl
                         on d.bale_id = bl.id
                         where i.status='DOCUMENTADO'
                         and  date_part('year',i.documentation_date)='$fechaX' 
                         ) as data";
                         $exe7 = $this->db->query($sqltv)->fetchAll();


                         // if ($exe7 and sizeof($exe7)>0) {
                         //     $array7[$i]= round(($exe7[0]['amount']/1000000),1);

                         //    }else{
                         //         $array7[$i]=0.0;
                         //    }




       $maximo = max(max($array),max($array2),max($array3),max($array4));

       $cobradoAnual =  round(($exe6[0]['tot']/1000000),1);
       $pagadoAnual =  round(($exe7[0]['amount']/1000000),1);


                 $dias = array();

                 $dias['week']=$array1;
                 $dias['amountTimbrado']=$array;
                 $dias['amountPagado']=$array2;
                 $dias['amountDocumentado']=$array3;
                 $dias['amountTotal']=$array4;
                 $dias['maximo'] = $maximo;
                 $dias['cobradoAnual'] = $cobradoAnual;
                 $dias['pagadoAnual'] = $pagadoAnual;

         return $dias;
    }

    public function getDataStation ($clusterId, $stationId) {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        if ($validUser->role_id == 26) {
            $where .= " and cluster_id = ".$validUser->cluster_id;
        }
        if ($validUser->role_id == 2) {
            $where .= " and id = " . $validUser->branch_office_id;
        }
        $and_station = "";
        $and_cluster = "";
        if ($stationId != 0) {
            $and_station = " and id = $stationId";
        }
        if ($clusterId != 0) {
            $and_cluster = " and cluster_id = $clusterId";
        }
        $sql = "SELECT id, name FROM wms_branch_offices where id > 0 $and_station $and_cluster $where order by name";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $dat) {
            $station_id = $dat['id'];
            $sql_productos = "SELECT p.id, l.name AS product_name,sum(ps.stock) as stock, ms.stock as pstock, ms.min_operation, ms.capacity as max_operation
                from v_product_stock_price ps
                JOIN wms_storages AS s ON s.id = ps.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id and bo.id = $station_id
                JOIN wms_products AS p ON p.id = ps.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN wms_marks AS ma ON p.mark_id = ma.id
                join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = $station_id
                group by p.id, l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, 
                l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ms.stock,ms.min_operation, ms.capacity order by product_name";
            $data_products = $this->db->query($sql_productos)->fetchAll();
            $products = [];
            $stock = [];
            $colors = [];
            $total_stock = 0;
            $count_product = 0;
            $percent_values = [];
            $products_name = [];
            $array_lines = [];
            $array_lines_stock = [];
            $array_lines_cat = [];
            $productId = 0;
            $products_name_array = [];
            $array_lines_merma = [];
            foreach ($data_products as $dp) {
                $productId = $dp['id'];
                $count_product++;
                $qty = 0;
                if ($dp['stock'] != 0) {
                    $qty = $dp['stock']/1000;
                }
                $qty_operation = 0;
                if ($dp['max_operation'] != 0) {
                    $qty_operation = $dp['max_operation']/1000;
                }
                array_push($products, ($dp['product_name'] . ' ' . number_format(floatval($qty),0,'.',',') . ' K'));
                array_push($products_name_array, ($dp['product_name'] . ' ' . number_format(floatval($qty),0,'.',',') . 'K' . '/'. number_format(floatval($qty_operation),0,'.',',') . 'K'));
                array_push($products_name, $dp['product_name']);
                $multi_value = $dp['stock'] * 100;
                $percent = 0;
                if ($multi_value != 0 && $dp['max_operation'] != 0) {
                    $percent = $multi_value / $dp['max_operation'];
                    $percent = number_format(floatval($percent),2,'.','');
                }
                $total_stock = $total_stock + $percent;
                array_push($stock, $percent);
                
                if ($dp['product_name'] == 'DIESEL') {
                    array_push($colors, '#212121');
                }
                if ($dp['product_name'] == 'REGULAR') {
                    array_push($colors, '#4caf50');
                }
                if ($dp['product_name'] == 'PREMIUM') {
                    array_push($colors, '#f44336');
                }
                array_push($percent_values, number_format(floatval($dp['stock']),2,'.',','));
                // existencias de los últimos 10 dias
                $fecha_consulta = date('Y-m-d');
                $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'-10 day'));
                $obj_product=(object)array();
                $obj_product->name = $dp['product_name'];
                $array_lines_stock = [];
                $label_lines = [];
                for ($i=0; $i<10; $i++) {
                    $sql_stock = "SELECT coalesce(stock,0) as stock 
                        from public.getkardex(null, '$fecha_consulta', $station_id, null, $productId) order by idx desc limit 1";
                    $val = $this->db->query($sql_stock)->fetchAll();
                    if ($val[0]['stock'] > 0) {
                        $v = $val[0]['stock']/1000;
                    } else {
                        $v = $val[0]['stock'];
                    }
                    array_push($array_lines_stock, number_format(floatval($v),2,'.',','));
                    $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'+1 day'));
                    $fechaComoEntero = strtotime($fecha_consulta);
                    $dia = date("d", $fechaComoEntero);
                    array_push($label_lines, $i == 9 ? 'HOY' : $dia);
                }
                $obj_product->data = $array_lines_stock;
                array_push($array_lines, $obj_product);
                //
                //
                //
                $fecha_consulta = date('Y-m-d');
                $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'-5 month'));
                $obj_product_merma=(object)array();
                $obj_product_merma->name = $dp['product_name'];
                $label_lines_merma = [];
                $array_lines_stock_merma = [];
                for ($i=0; $i<6; $i++) {
                    $fechaComoEntero2 = strtotime($fecha_consulta);
                    $mes = date("m", $fechaComoEntero2);
                    $sql_productos_merma = "SELECT p.id, l.name AS product_name,sum(md.qty) as stock, ms.stock as pstock, ms.min_operation, ms.capacity as max_operation
                        from wms_movement_details as md
                        join wms_movements as ps on ps.id = md.movement_id and ps.type_id = 6 and date_part('month', ps.date) = $mes
                        JOIN wms_storages AS s ON s.id = ps.storage_id 
                        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id and bo.id = $station_id
                        JOIN wms_products AS p ON p.id = md.product_id and p.id = $productId
                        JOIN wms_lines AS l ON l.id = p.line_id
                        JOIN wms_categories AS c ON c.id = l.category_id
                        JOIN wms_marks AS ma ON p.mark_id = ma.id
                        join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = $station_id
                        group by p.id, l.category_id, c.code, c.name,md.product_id, p.code, p.name, p.line_id, 
                        l.code, l.name,p.active,p.old_code,ma.name,s.name,ms.stock,ms.min_operation, ms.capacity order by product_name";
                    $data_products_merma = $this->db->query($sql_productos_merma)->fetchAll();
                    if (sizeof($data_products_merma) > 0) {
                        if ($data_products_merma[0]['stock'] > 0) {
                            $v = $data_products_merma[0]['stock']/1000;
                        } else {
                            $v = $data_products_merma[0]['stock'];
                        }
                        $value = $data_products_merma[0]['stock'];
                    } else {
                        $v = 0;
                        $value = 0;
                    }
                    array_push($array_lines_stock_merma, number_format(floatval($v),2,'.',','));
                    $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'+1 month'));
                    $fechaComoEntero = strtotime($fecha_consulta);
                    array_push($label_lines_merma, $this->getMes($mes));
                }
                $obj_product_merma->data = $array_lines_stock_merma;
                array_push($array_lines_merma, $obj_product_merma);
            }
            if ($total_stock != 0 && $count_product != 0) {
                $total_stock = $total_stock/$count_product;
            }
            $data[$key]['total'] = number_format(floatval($total_stock),2,'.',',');
            $data[$key]['colors'] = $colors;
            $data[$key]['products'] = $products;
            $data[$key]['products_name'] = $products_name;
            $data[$key]['products_name_array'] = $products_name_array;
            $data[$key]['stock'] = $stock;
            $data[$key]['gal'] = $percent_values;
            $data[$key]['graphic_lines'] = $array_lines;
            $data[$key]['label_lines'] = $label_lines;
            $data[$key]['graphic_lines_merma'] = $array_lines_merma;
            $data[$key]['label_lines_merma'] = $label_lines_merma;
        }

        //
        $sql_s = "SELECT sys_supercluster.id, sys_supercluster.code, sys_supercluster.name
                FROM sys_supercluster";
        $data_s = $this->db->query($sql_s)->fetchAll();
        foreach ($data_s as $key => $dat) {
            $cluster_id = $dat['id'];
            $sql_products = "SELECT product_name, sum(stock) as stock, sum(pstock) as pstock, sum(min_operation) as min_operation, sum(capacity) as capacity from (SELECT p.id, l.name AS product_name,sum(ps.stock) as stock, ms.stock as pstock, ms.min_operation, ms.capacity
                from v_product_stock_price ps
                JOIN wms_storages AS s ON s.id = ps.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id and bo.cluster_id = $cluster_id
                JOIN wms_products AS p ON p.id = ps.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN wms_marks AS ma ON p.mark_id = ma.id
                join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = bo.id
                group by p.id, l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, 
                l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ms.stock,ms.min_operation, ms.capacity order by product_name) as w
                group by product_name";
            $data_products = $this->db->query($sql_products)->fetchAll();
            $products = [];
            $stock = [];
            $colors = [];
            $total_stock = 0;
            $percent_values = [];
            $products_name = [];
            foreach ($data_products as $dp) {
                $qty = 0;
                if ($dp['stock'] != 0) {
                    $qty = $dp['stock']/1000;
                }
                $qty_capacity = 0;
                if ($dp['capacity'] != 0) {
                    $qty_capacity = $dp['capacity']/1000;
                }
                array_push($products, ($dp['product_name'] . ' ' . number_format(floatval($qty),0,'.',',') . ' K ' . '/' . ' ' . number_format(floatval($qty_capacity),0,'.',',') . ' K'));
                array_push($products_name, $dp['product_name']);
                $multi_value = $dp['stock'] * 100;
                $percent = 0;
                if ($multi_value != 0 && $dp['capacity'] != 0) {
                    $percent = $multi_value / $dp['capacity'];
                    $percent = number_format(floatval($percent),2,'.','');
                }
                $total_stock = $total_stock + $percent;
                array_push($stock, $percent);
                
                if ($dp['product_name'] == 'DIESEL') {
                    array_push($colors, '#212121');
                }
                if ($dp['product_name'] == 'REGULAR') {
                    array_push($colors, '#4caf50');
                }
                if ($dp['product_name'] == 'PREMIUM') {
                    array_push($colors, '#f44336');
                }
                array_push($percent_values, number_format(floatval($dp['stock']),2,'.',','));
            }
            $data_s[$key]['colors'] = $colors;
            $data_s[$key]['products'] = $products;
            $data_s[$key]['products_name'] = $products_name;
            $data_s[$key]['stock'] = $stock;
        }

        $this->content['data_s'] = $data_s;
        $this->content['data'] = $data;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getDataMermas ($clusterId, $stationId) {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        if ($validUser->role_id == 26) {
            $where .= " and cluster_id = ".$validUser->cluster_id;
        }
        if ($validUser->role_id == 2) {
            $where .= " and id = " . $validUser->branch_office_id;
        }
        $and_station = "";
        $and_cluster = "";
        if ($stationId != 0) {
            $and_station = " and id = $stationId";
        }
        if ($clusterId != 0) {
            $and_cluster = " and cluster_id = $clusterId";
        }
        $sql = "SELECT id, name FROM wms_branch_offices where id > 0 $and_station $and_cluster $where order by name";
        $data = $this->db->query($sql)->fetchAll();
        foreach ($data as $key => $dat) {
            $station_id = $dat['id'];
            $sql_productos = "SELECT p.id, l.name AS product_name,sum(ps.stock) as stock, ms.stock as pstock, ms.min_operation, ms.capacity as max_operation
                from v_product_stock_price ps
                JOIN wms_storages AS s ON s.id = ps.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id and bo.id = $station_id
                JOIN wms_products AS p ON p.id = ps.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN wms_marks AS ma ON p.mark_id = ma.id
                join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = $station_id
                group by p.id, l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, 
                l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ms.stock,ms.min_operation, ms.capacity order by product_name";
            $data_products = $this->db->query($sql_productos)->fetchAll();
            $products = [];
            $stock = [];
            $colors = [];
            $total_stock = 0;
            $percent_values = [];
            $products_name = [];
            $array_lines = [];
            $array_lines_stock = [];
            $array_lines_cat = [];
            $productId = 0;
            $products_name_array = [];
            foreach ($data_products as $dp) {
                $productId = $dp['id'];
                $fecha_consulta = date('Y-m-d');
                $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'-5 month'));
                $obj_product=(object)array();
                $obj_product->name = $dp['product_name'];
                $array_lines_stock_merma = [];
                $label_lines = [];
                for ($i=0; $i<6; $i++) {
                    $fechaComoEntero2 = strtotime($fecha_consulta);
                    $mes = date("m", $fechaComoEntero2);
                    $sql_productos_merma = "SELECT p.id, l.name AS product_name,sum(md.qty) as stock, ms.stock as pstock, ms.min_operation, ms.capacity as max_operation
                        from wms_movement_details as md
                        join wms_movements as ps on ps.id = md.movement_id and ps.type_id = 6 and date_part('month', ps.date) = $mes
                        JOIN wms_storages AS s ON s.id = ps.storage_id 
                        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id and bo.id = $station_id
                        JOIN wms_products AS p ON p.id = md.product_id and p.id = $productId
                        JOIN wms_lines AS l ON l.id = p.line_id
                        JOIN wms_categories AS c ON c.id = l.category_id
                        JOIN wms_marks AS ma ON p.mark_id = ma.id
                        join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = $station_id
                        group by p.id, l.category_id, c.code, c.name,md.product_id, p.code, p.name, p.line_id, 
                        l.code, l.name,p.active,p.old_code,ma.name,s.name,ms.stock,ms.min_operation, ms.capacity order by product_name";
                    $data_products_merma = $this->db->query($sql_productos_merma)->fetchAll();
                    if (sizeof($data_products_merma) > 0) {
                        if ($data_products_merma[0]['stock'] > 0) {
                            $v = $data_products_merma[0]['stock']/1000;
                        } else {
                            $v = $data_products_merma[0]['stock'];
                        }
                        $value = $data_products_merma[0]['stock'];
                    } else {
                        $v = 0;
                        $value = 0;
                    }
                    array_push($array_lines_stock_merma, number_format(floatval($v),2,'.',','));
                    $fecha_consulta = date('Y-m-d', strtotime($fecha_consulta.'+1 month'));
                    $fechaComoEntero = strtotime($fecha_consulta);
                    array_push($label_lines, $this->getMes($mes));
                }
                $obj_product->data = $array_lines_stock_merma;
                array_push($array_lines, $obj_product);
            }
            
            $data[$key]['products_name'] = $products_name;
            $data[$key]['graphic_lines'] = $array_lines;
            $data[$key]['label_lines'] = $label_lines;
        }

        // $this->content['data_s'] = $data_s;
        $this->content['data'] = $data;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    private function getMes ($monthnum) {
        $mes = $monthnum;
        $monthNameSpanish = '';
        switch($mes) {   
            case '01':
            $monthNameSpanish = "ENERO";
            break;

            case '02':
            $monthNameSpanish = "FEBRERO";
            break;

            case '03':
            $monthNameSpanish = "MARZO";
            break;

            case '04':
            $monthNameSpanish = "ABRIL";
            break;

            case '05':
            $monthNameSpanish = "MAYO";
            break;

            case '06':
            $monthNameSpanish = "JUNIO";
            break;

            case '07':
            $monthNameSpanish = "JULIO";
            break;

            case '08':
            $monthNameSpanish = "AGOSTO";
            break;

            case '09':
            $monthNameSpanish = "SEPTIEMBRE";
            break;
            case '10':
            $monthNameSpanish = "OCTUBRE";
            break;

            case '11':
            $monthNameSpanish = "NOVIEMBRE";
            break;

            case '12':
            $monthNameSpanish = "DICIEMBRE";
            break;
        }
        return $monthNameSpanish;
    }

}

