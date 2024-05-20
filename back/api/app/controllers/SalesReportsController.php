<?php

use Phalcon\Mvc\Controller;

class SalesReportsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShoppingCart ($id = null)
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            if ($this->userHasPermission() && !is_null($id) && is_numeric($id)) {
                $sql = $this->getShoppingCartSQL($id);
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
    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 23 OR role_id = 2 OR role_id = 3 OR role_id = 20)
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

    public function queryinBulkForGenerateInvoice ($storage, $products){
        $sql = "SELECT s1.product_id, CONCAT(c.code,'-',l.code,'-',p.name) AS product_name, s1.qty
                FROM (SELECT md.product_id, SUM(md.qty) AS qty
                    FROM (SELECT md.product_id, CASE WHEN m.type_id = 2 THEN md.qty * -1 ELSE md.qty END AS qty
                            FROM wms_movement_details AS md
                            INNER JOIN wms_movements AS m ON m.id = md.movement_id AND md.product_id in ($products)
                            WHERE m.status = 'EJECUTADO' AND m.storage_id = $storage) AS md
                    GROUP BY md.product_id) AS s1
                INNER JOIN wms_products AS p ON p.id = s1.product_id
                INNER JOIN wms_lines AS l ON l.id = p.line_id
                INNER JOIN wms_categories AS c ON c.id = l.category_id
                WHERE s1.qty > 0
                ORDER BY product_name ASC;";
        $data = $this->db->query($sql)->fetchAll();

        return $data;
    }

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

    // ZONA DE CONSULTAS

    public function getShoppingCartSQL ($id) {
        $sql = "SELECT sc.id, sibd.invoice_id as invoice,scbs.price_product,wp.name as product, cbo.name as clientbranchoffice ,c.id as id_client,to_char(sc.created,'DD/MM/YYYY HH24:MI') as date,sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,
        STRING_AGG(DISTINCT CAST(si.id AS varchar), ',') as invoices,count(si.id) as contador,bo.name as branchofficeorigin,
        (select COALESCE((SELECT sum(scb.qty) from sls_shopping_cart_bale_details AS scb where scb.shopping_cart_id = sc.id), 0)) as bale,
        (select COALESCE((SELECT sum(scb.price_product * scb.qty) from sls_shopping_cart_bale_details AS scb where scb.shopping_cart_id = sc.id), 0)) as montobale,
        (select COALESCE((SELECT sum(sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as inbulk,
        (select COALESCE((SELECT sum(sci.price_product * sci.qty) from sls_shopping_cart_in_bulk_details AS sci where sci.shopping_cart_id = sc.id), 0)) as montoinbulk,
        (select COALESCE((SELECT sum(scl.qty) from sls_shopping_cart_laminate_details AS scl where scl.shopping_cart_id = sc.id), 0)) as laminate,
        (select COALESCE((SELECT sum(scl.price_product * scl.qty) from sls_shopping_cart_laminate_details AS scl where scl.shopping_cart_id = sc.id), 0)) as montolaminate
        FROM sls_shopping_cart AS sc
        LEFT JOIN sys_users AS u ON u.id = sc.user_id
        LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
        LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
        LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
        LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
        LEFT JOIN sls_shopping_cart_in_bulk_details AS scbs ON scbs.invoice_id = si.id
        LEFT JOIN sls_invoice_in_bulk_details AS sibd ON sibd.invoice_id = si.id
        LEFT JOIN wms_products AS wp ON wp.id=sibd.product_id
         WHERE sibd.invoice_id IS NOT NULL
        GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.branchoffice, cbo.name, bo.name,sibd.invoice_id, wp.id,scbs.price_product
        ORDER BY id DESC;";
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
    public function getGridSQLReport ($customer,$seller,$branch,$saleDatev1,$saleDatev2,$sellerId) {
        if ($sellerId !== null) {
        $sql = "SELECT role_id
        FROM sys_user_roles
        WHERE user_id = $sellerId;";
        $currentRoles = $this->db->query($sql)->fetchAll();
        $roles = [];
        foreach ($currentRoles as $role) {
            array_push($roles, intval($role['role_id']));
        }
    }
    $where = 'WHERE sc.id > 0 ';
    $y = date("Y");
    if ($customer == 'TODOS') {} else if($customer == ''){}else {$where .= " AND sc.customer_id = $customer";}
    if ($seller == 'TODOS' && !in_array(1,$roles) && !in_array(3,$roles)) {
        $where .= " AND sc.user_id = $sellerId";
    } else if($seller == 'TODOS'){}else {
    $where .= " AND sc.user_id = $seller";
    }
    if ($branch == 'TODOS') {} else if($branch == ''){}else {$where .= " AND sc.branchoffice = '$branch'";}
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

    $sql = "SELECT sc.id, sibd.invoice_id as invoice,wp.name as product,sibd.qty as cantidad,sibd.unit_price,(sibd.qty * sibd.unit_price) as total, cbo.name as clientbranchoffice ,c.id as id_client,to_char(sc.created,'DD/MM/YYYY') as date,sc.user_id, u.nickname AS user_name, u.email AS user_email, sc.customer_id, c.name AS customer_name, c.price_list, sc.status,
    STRING_AGG(CAST(si.id AS varchar), ',') as invoices,count(si.id) as contador,bo.name as branchofficeorigin
        FROM sls_shopping_cart AS sc
        LEFT JOIN sys_users AS u ON u.id = sc.user_id
        LEFT JOIN wms_branch_offices AS bo ON bo.id = sc.branchoffice
        LEFT JOIN sls_customers AS c ON c.id = sc.customer_id
        LEFT JOIN sls_customer_branch_offices AS cbo ON cbo.id = sc.branchofficedestiny
        LEFT JOIN sls_invoices AS si ON si.shopping_cart_id = sc.id
        LEFT JOIN sls_invoice_in_bulk_details AS sibd ON sibd.invoice_id = si.id
        LEFT JOIN wms_products AS wp ON wp.id=sibd.product_id
        WHERE invoice_id IS NOT NULL
    GROUP BY sc.id,c.id,u.nickname,u.email,sc.customer_id,c.name,c.price_list, sc.branchoffice, cbo.name, bo.name,sibd.id, wp.id
    ORDER BY id DESC;";
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
    public function getGridReport ()
    {
        $request = $this->request->getPost();

        if ($this->userHasPermission()) {
            $sql = $this->getGridSQLReport($request['customer'],$request['seller'],$request['branch'],$request['saleDatev1'],$request['saleDatev2'],$request['sellerId']);
            $this->content['shoppingCarts'] = $sql;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
}