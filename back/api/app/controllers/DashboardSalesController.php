<?php

use Phalcon\Mvc\Controller;

class DashboardSalesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function weekSellerSalesCharts () {
        if ($this->userHasPermission()) {
            $sold = [];
            // Requiero obtener el id de todos los vendedores
            $sql = "SELECT DISTINCT sc.user_id as value, c.nickname as label
            from sls_shopping_cart as sc left join
            sys_users as c on c.id = sc.user_id  where sc.status = 'REMISIONADO';";
            $sellers = $this->db->query($sql)->fetchAll();
            $salesBySeller = [];
            $week = [];
            foreach($sellers as $key=>$seller){
                $week =  $this->getSalesBySeller(null,intval($seller['value']),0)['week'];
                array_push($salesBySeller, array('seller' => $seller['label'],'totalSold' => $this->getSalesBySeller(null,intval($seller['value']),0) ? $this->getSalesBySeller(null,intval($seller['value']),0)['data'] : null));
            }
            // var_dump($salesBySeller);     
            $this->content['weekSellerSalesCharts'] = $salesBySeller;
            $this->content['week'] = $week;
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function top10BestCustomers () {
        $sql = "SELECT sc.customer_id,c.name, sum(bd.qty * bd.price_product)::numeric as total from 
        sls_shopping_cart as sc
        inner join sls_shopping_cart_in_bulk_details as bd on bd.shopping_cart_id = sc.id
        inner join sls_customers as c on c.id = sc.customer_id
        group by sc.customer_id,c.name
        order by total desc
        limit 10";
        $customers = $this->db->query($sql)->fetchAll();
        foreach ($customers as $index => $customer) {
            $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
            $data[$index]['name'] = $customer['name'];
            $customerId = $customer['customer_id'];

            $sql = "SELECT sc.customer_id, sum(bd.qty * bd.price_product)::numeric as total, to_char(sc.created, 'MM') as created
            from sls_shopping_cart as sc
            inner join sls_shopping_cart_in_bulk_details as bd on bd.shopping_cart_id = sc.id 
            where sc.created > date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' and sc.customer_id = {$customerId}
            group by sc.customer_id,to_char(sc.created, 'MM')
            order by total desc
            limit 10";
            $dataInfo = $this->db->query($sql)->fetchAll();
            foreach( $dataInfo as $row){
                if (( (int)$row['created'] - date('n')) <= 0) {
                    $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['total'];
                    } else {
                        $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['total'];
                    }
            }
        }
        $this->content['top10BestCustomers'] = $data;
    
    $this->response->setJsonContent($this->content);
    $this->response->send();
    }
    public function monthSellerSalesCharts () {
        // El metodo actua sobre las remisiones en estatus ENVIADO unicamente
        if ($this->userHasPermission()) {
            $sql = "SELECT DISTINCT i.seller_id as value, c.nickname as label
            from sls_invoices as i left join
            sys_users as c on c.id = i.seller_id  where i.status = 'ENVIADO' or i.status = 'NUEVO';";
            $sellers = $this->db->query($sql)->fetchAll();
            foreach ($sellers as $index => $seller) {
                // array_push($datos, array('customer' => $customer[$key]['name'],'counts' => $this->getCountSalesByCustomer($customer[$key]['customer_id'])));
                $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
                $data[$index]['name'] = $seller['label'];
                $sellerId = $seller['value'];
    
                $sql = "SELECT to_char(a.sale_date, 'MM') as created,sum (
                    (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = a.id), 0))) as bulktotal,a.seller_id from (
                                    SELECT sc.sale_date,sc.seller_id,status,sc.id
                                    FROM sls_invoices as sc
                                    left join sys_users AS u ON u.id = sc.seller_id
                                    where sc.sale_date >
                                    date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
                                    ) a
                                    WHERE (a.status = 'ENVIADO' or a.status = 'NUEVO') AND a.seller_id = {$sellerId}
                                    GROUP BY a.seller_id,to_char(a.sale_date, 'MM')";
                $dataInfo = $this->db->query($sql)->fetchAll();
                foreach( $dataInfo as $row){
                    if (( (int)$row['created'] - date('n')) <= 0) {
                        $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['bulktotal'];
                        } else {
                            $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['bulktotal'];
                        }
                    // $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['bulktotal'];
                }
            }
            $this->content['monthSellerSalesCharts'] = $data;
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function monthSellerBoxesCharts () {
        // El metodo actua sobre las remisiones en estatus ENVIADO unicamente
        if ($this->userHasPermission()) {
            $sql = "SELECT DISTINCT i.seller_id as value, c.nickname as label
            from sls_invoices as i left join
            sys_users as c on c.id = i.seller_id  where i.status = 'ENVIADO';";
            $sellers = $this->db->query($sql)->fetchAll();
            foreach ($sellers as $index => $seller) {
                // array_push($datos, array('customer' => $customer[$key]['name'],'counts' => $this->getCountSalesByCustomer($customer[$key]['customer_id'])));
                $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0,0];
                $data[$index]['name'] = $seller['label'];
                $sellerId = $seller['value'];
    
                $sql = "select to_char(sc.sale_date, 'MM') as created, sum(sib.qty / case when p.pieces is null then 1 else p.pieces end) as boxes ,sc.seller_id 
                FROM sls_invoices as sc
                inner join sls_invoice_in_bulk_details as sib on sib.invoice_id = sc.id
                inner join wms_products as p on p.id = sib.product_id
                left join sys_users AS u ON u.id = sc.seller_id
                where sc.sale_date >
                date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' and status = 'ENVIADO' AND sc.seller_id = {$sellerId}
                GROUP BY sc.seller_id,to_char(sc.sale_date, 'MM')";
                $dataInfo = $this->db->query($sql)->fetchAll();
                foreach( $dataInfo as $row){
                    if (( (int)$row['created'] - date('n')) <= 0) {
                        $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['boxes'];
                        } else {
                            $data[$index]['series'][( (int)$row['created'] - date('n'))] = (int)$row['boxes'];
                        }
                    // $data[$index]['series'][( (int)$row['created'] - date('n'))+12] = (int)$row['bulktotal'];
                }
            }
            $this->content['monthSellerBoxesCharts'] = $data;
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getSalesBySeller ($customerId,$sellerId,$typeChart) {
        $y = date('Y');
        $where = "";
        $order = "";
        $array1 = array();
        $datos = array();
        $fechaNormal= date("Y-m-d");
        if ($typeChart == 0) {
        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);
    
            $fecha= date("Y",$mod_date);
            // if (isset($sellerId) && $sellerId != null) {
            //     $where .= "WHERE i.seller_id = $sellerId AND i.status = 'ENVIADO' AND date_part('week', i.sale_date) = '$sem' AND date_part('year',i.sale_date)='$fecha'";
            // }
            $sql = "SELECT (sum (
                (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)))) as bulktotal
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.seller_id
                WHERE i.seller_id = $sellerId AND i.status = 'ENVIADO' AND date_part('week', i.sale_date) = '$sem' AND date_part('year',i.sale_date)='$fecha'";
            $data = $this->db->query($sql)->fetchAll()[0]['bulktotal'] ? $this->db->query($sql)->fetchAll()[0]['bulktotal'] : 0;
            array_push($datos, $data);
            $array1[$i]='Sem'.$sem;
        }
        $array['week'] = $array1;
        $array['data'] = $datos;
    } else if ($typeChart == 1) {
        for ($i=0; $i <=12 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fecha= date("Y",$mod_date);
            $mes= date("m",$mod_date);
    
            $fecha= date("Y",$mod_date);
            $sql = "SELECT (sum (
                (select COALESCE((SELECT sum(sib.unit_price * sib.qty) from sls_invoice_in_bulk_details as sib where sib.invoice_id = i.id), 0)))) as bulktotal
                FROM sls_invoices AS i
                LEFT JOIN sys_users AS a ON a.id = i.seller_id
                WHERE i.seller_id = $sellerId AND i.status = 'ENVIADO' AND date_part('month', i.sale_date) = '$mes' AND date_part('year',i.sale_date)='$fecha'";
            $data = $this->db->query($sql)->fetchAll()[0]['bulktotal'];
            array_push($datos, $data);
        }
        $array['data'] = $datos;
    }
        return $array;
    }
    public function daySales ()
    {
        if ($this->userHasPermission()) {
            $nowDate = new DateTime();
            $date = $nowDate->format('Y-m-d');
            $sql = "SELECT SUM(ib.qty*ib.unit_price) AS daySale
            FROM sls_invoices AS i 
            INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
            WHERE i.sale_date = '$date'";

            $this->content['daySales'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function weekSales ()
    {
        if ($this->userHasPermission()) {
            $nowDate = new DateTime();
            $date = $nowDate->format('m');
            $sql = "SELECT SUM(ib.qty*ib.unit_price) AS weekSale
            FROM sls_invoices AS i 
            INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
            WHERE EXTRACT(week FROM cast(i.sale_date AS date)) = EXTRACT(week FROM cast(current_date AS date))";

            $this->content['weekSales'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function monthSales ()
    {
        if ($this->userHasPermission()) {
            $nowDate = new DateTime();
            $date = $nowDate->format('m');
            $sql = "SELECT SUM(ib.qty*ib.unit_price) AS monthsale
            FROM sls_invoices AS i 
            INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
            WHERE EXTRACT(month FROM cast(i.sale_date AS date)) = EXTRACT(month FROM cast(current_date AS date))";

            $this->content['monthSales'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function dayPays ()
    {
        if ($this->userHasPermission()) {
            $nowDate = new DateTime();
            $date = $nowDate->format('Y-m-d');
            $sql = "SELECT SUM(p.amount) AS daypay
            FROM sls_payments AS p
            WHERE p.payment_date = '$date'";

            $this->content['dayPays'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function weekPays ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT SUM(p.amount) AS weekpay
            FROM sls_payments AS p
            WHERE EXTRACT(week FROM cast(p.payment_date AS date)) = EXTRACT(week FROM cast(current_date AS date))";

            $this->content['weekPays'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function monthPays ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT SUM(p.amount) AS monthpay
            FROM sls_payments AS p
            WHERE EXTRACT(month FROM cast(p.payment_date AS date)) = EXTRACT(month FROM cast(current_date AS date))";

            $this->content['monthPays'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function yearPays ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT SUM(p.amount) AS yearpay
            FROM sls_payments AS p
            WHERE EXTRACT(year FROM cast(p.payment_date AS date)) = EXTRACT(year FROM cast(current_date AS date))";

            $this->content['yearPays'] = $this->db->query($sql)->fetch();
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function daySalesCharts(){
        $content = $this->content;
        $content['daySalesCharts'] = $this->getSalesDay();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function weekSalesCharts(){
        $content = $this->content;
        $content['weekSalesCharts'] = $this->getWeekSalesCharts();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function yearSalesCharts(){
        $content = $this->content;
        $content['yearSalesCharts'] = $this->getYearSalesCharts();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getSalesDay ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;

        $array = array();
        $daySale = array();
        $weekDays = array();
        $daysLabel = array(
            "DO",
            "LU",
            "MA",
            "MI",
            "JU",
            "VI",
            "SA"
        );

        $dia= date("Y-m-d");
        $dias=0;
        $mod_date = strtotime($dia."- $dias days");
        $fecha= date("Y-m-d",$mod_date);

        for ($i=0; $i <=7 ; $i++) {
            $mod_date = strtotime($dia."- $i days");
            $fecha= date("Y-m-d",$mod_date);
            $weekDay= date("w",$mod_date);
            $sql = "SELECT ib.qty, ib.unit_price
                    FROM sls_invoices AS i 
                    INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
                    WHERE to_char(i.sale_date, 'YYYY-MM-DD') = '$fecha'";

            $sales = $this->db->query($sql)->fetchAll();

            $inventory_cost = 0;
            foreach ($sales as $key => $value) {
                // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
                $inventory_cost += $sales[$key]['qty'] * $sales[$key]['unit_price'];
            }
            $daySale[$i] = $inventory_cost;
            if ($i == 0) {
                $weekDays[$i] = "HOY";
            } else {
                $weekDays[$i] = $daysLabel[intval($weekDay)];
            }
        }
        $dias = array();

        $dias['salesDayChart']=$daySale;
        $dias['day']=$weekDays;

        return $dias;
    }
    public function getWeekSalesCharts ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;
        $array = array();
        $salesWeek = array();
        $fechaNormal= date("Y-m-d");

        for ($i=0; $i <=5 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i week");
            $fr = strtotime($fechaNormal."- $i week");
            $sem= date("W",$fr);

            $fecha= date("Y",$mod_date);
            $sql = "SELECT ib.qty, ib.unit_price
            FROM sls_invoices AS i 
            INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
            WHERE date_part('week', i.sale_date) = '$sem' AND date_part('year',i.sale_date)='$fecha'";
    
            $sales = $this->db->query($sql)->fetchAll();
            $inventory_cost = 0;
            foreach ($sales as $key => $value) {
                // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
                $inventory_cost += $sales[$key]['qty'] * $sales[$key]['unit_price'];
            }
            $salesWeek[$i] = $inventory_cost;
            $array1[$i]='Sem'.$sem;
        }
        $dias = array();
        $dias['salesWeekChart']=$salesWeek;
        $dias['week']=$array1;

        return $dias;
    }
    public function getYearSalesCharts ()
    {
        // Necesito obtener el costo de inventario diario, de acuerdo al precio de los productos en ese día
        $content = $this->content;

         $array = array();
         $array1 = array();
         $yearSales = array();

         $fechaNormal= date("Y-m-d");

         for ($i=0; $i <=12 ; $i++) {
            $mod_date = strtotime($fechaNormal."- $i month");
            $fecha= date("Y",$mod_date);
            $mes= date("m",$mod_date);
            $sql = "SELECT ib.qty, ib.unit_price
            FROM sls_invoices AS i 
            INNER JOIN sls_invoice_in_bulk_details AS ib ON ib.invoice_id = i.id
            WHERE date_part('month', i.sale_date) = '$mes' AND date_part('year',i.sale_date)='$fecha'";

            $sales = $this->db->query($sql)->fetchAll();
            $inventory_cost = 0;
            foreach ($sales as $key => $value) {
                // De acuerdo al ultimo precio encontrado se multiplica este por el stock total del producto
                $inventory_cost += $sales[$key]['qty'] * $sales[$key]['unit_price'];
            }
            $yearSales[$i] = $inventory_cost;
        }
        $dias = array();
        $dias['yearSale']=$yearSales;

        return $dias;
    }
    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3)
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
