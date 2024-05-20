<?php

use Phalcon\Mvc\Controller;

class BomController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBom ($p)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {

            $sql = "SELECT  wb.id,wb.product_id,wb.material_id,wb.amount,wp.code,wp.name,
                    wl.id as line_id,wl.name as line_name,
                    wc.id as category_id, wc.name as category_name,
                    wp.name as product_name, wu.name as unit
                    from wms_bom as wb 
                    left join wms_products as wp on wb.material_id = wp.id
                    left join wms_lines as wl on wl.id = wp.line_id
                    left join wms_categories as wc on wc.id =wl.category_id
                    left join wms_units as wu on wu.id = wp.unit_id
                    where product_id =$p and wp.active
                    order by id;";

             $data = $this->db->query($sql)->fetchAll();
            //  Get cost of products
            $lastPrices = [];
            $products = [];
            foreach ($data as $key => $value) {
                if (!in_array($data[$key]['material_id'], $products)) {
                    $lastPrice = 0;
                    foreach ($data as $key2 => $value2) {
                        if ($data[$key]['material_id'] == $data[$key2]['material_id']) {
                            $data[$key]['lastprice'] = $this->getLastProductCost($data[$key]['material_id']);
                        }
                    }
                    array_push($products, $data[$key]['material_id']);
                }
            }
            // foreach ($data as $key => $value) {
                
            //     $data[$key]['lastprice'] = $this->getLastProductCost($data[$key]['material_id']);
            // }
            $content['bom'] = $data;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getLastProductCost ($id) {
        if ($id != null) {
            $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date,  md.product_id, TRUNC(md.qty::numeric,2) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
            FROM wms_movement_details AS md
            JOIN wms_movements AS m ON m.movement_id = md.movement_id
            JOIN wms_products AS p ON p.id = md.product_id
            WHERE m.status = 'EJECUTADO' and p.id = $id and (m.type_id = 1 or m.type_id = 3)
UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, md.product_id, TRUNC(md.qty::numeric,2) AS qty, TRUNC((md.unit_price)::numeric,2) as unit_price
            FROM wms_movement_details AS md
            JOIN wms_movements AS m ON m.id = md.movement_id
            JOIN wms_products AS p ON p.id = md.product_id
            WHERE m.status = 'EJECUTADO' and p.id = $id and (m.type_id = 1 or m.type_id = 3)
) AS QUERY ORDER BY date DESC,CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC, foli ASC LIMIT 1";
            $movements = $this->db->query($sql)->fetchAll();
        }
        if ($movements){
            return $movements[0]['unit_price'];
        } else {
            return 0;
        }
    }

    public function productBom ($p)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            //$this->content['products'] = Products::find(['order' => 'id ASC']);
            //$this->content['result'] = true;
            ///print_r($p);
            //exit();
            $bom = ProductionOrders::findFirst($p);
            // print_r($bom->product_id);
            if($bom){

                /*$sql = "
                SELECT po.id, po.order_number, po.production_date, po.updated, po.updated_by,
                po.product_id, 
                wms_bom.bom_material_id, po.qty, po.unit_id,(po.qty*wms_bom.amount) as total, 
                po.status, wms_bom.amount,wms_bom.code_product , 
                wms_bom.name_product , wms_bom.code_line , wms_bom.name_line , wms_bom.code_category , 
                wms_bom.name_category, COALESCE(x.suma,0) as stock,x.storage
                FROM prd_orders AS po 
                left join (select wms_bom.material_id as bom_material_id, wms_bom.product_id as bom_product_id, 
                amount, wms_products.code as code_product, wms_products.name as name_product, 
                wms_lines.code as code_line, wms_lines.name as name_line, 
                wms_categories.code as code_category, 
                wms_categories.name as name_category
                from wms_bom 
                left join wms_products on wms_products.id = wms_bom.material_id 
                left join wms_lines ON wms_lines.id = wms_products.line_id 
                left join wms_categories ON wms_categories.id = wms_lines.category_id
                order by wms_bom.id) wms_bom on wms_bom.bom_product_id = po.product_id
                left join  (select product_id, suma, storage from  (SELECT product_id,
                sum(wms_movement_details.qty * 
                               case when m.type_id = 1 then 1
                                    when m.type_id = 2 then -1
                                    when m.type_id = 3 then 1
                                    when m.type_id = 4 then 1
                                   when m.type_id = 5 then -1
                               end) 
                    over(PARTITION  by product_id) as suma, s.id as storage
                FROM wms_movement_details 
                JOIN wms_movements AS m ON m.id = wms_movement_details.movement_id
                JOIN wms_storages AS s ON s.id = m.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                JOIN wms_products AS p ON p.id = wms_movement_details.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN sys_users AS u ON u.id = m.created_by
                WHERE m.status = 'EJECUTADO' ) as x
                group by x.product_id, x.suma, x.storage ) x on x.product_id = wms_bom.bom_material_id
                where po.id =$bom->id";*/
                $sql="SELECT prd_orders.id,prd_orders.id as order_id,prd_orders.order_number, prd_orders.production_date, prd_orders.updated, prd_orders.updated_by,
                    wms_bom.material_id,prd_orders.product_id as bom_products_id, wms_products.name as product_name,
                    wms_products.code as product_code, wms_lines.name as line_name,wms_lines.code as line_code, 
                    wms_lines.id as line_id, wms_categories.name as category_name, wms_categories.code as category_code,
                    wms_bom.amount,prd_orders.qty, sum(wms_bom.amount * prd_orders.qty) as stock
                    FROM wms_bom
                    inner join wms_products on wms_products.id = wms_bom.material_id
                    inner join wms_lines on wms_lines.id = wms_products.line_id
                    inner join wms_categories on wms_categories.id = wms_lines.category_id
                    inner join prd_orders on prd_orders.product_id =wms_bom.product_id
                    where prd_orders.id = $p
                    group by wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id,
                    wms_bom.amount, prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code,
                    prd_orders.id, wms_bom.id
                    order by wms_bom.id";


                $detalles = $this->db->query($sql)->fetchAll();
                $aux=0;
                $cantidades_suficientes = 'si';
                $bom_by_producto = [];
                $producto = "";
                if($detalles){
                foreach ($detalles as $bom_producto) {
                    $b=(object)array();
                    $b->id=$bom_producto['id'];
                    $b->production_date=$bom_producto['production_date'];
                    $b->updated=$bom_producto['order_id'];
                    $b->updated_by=$bom_producto['updated'];
                    $b->order_id = $bom_producto['updated_by'];
                    $b->material_id = $bom_producto['material_id'];
                    $producto_existencia = $bom_producto['material_id'];
                    $b->bom_products_id = $bom_producto['bom_products_id'];
                    $b->product_name = $bom_producto['product_name'];
                    $b->product_code = $bom_producto['product_code'];
                    $b->line_name = $bom_producto['line_name'];
                    $b->line_code = $bom_producto['line_code'];
                    $b->line_id = $bom_producto['line_id'];
                    $b->category_name = $bom_producto['category_name'];
                    $almacen = $bom_producto['category_code'];
                    $b->category_code = $bom_producto['category_code'];
                    $b->amount = $bom_producto['amount'];
                    $b->qty = $bom_producto['qty'];
                    //$b->qty = $bom_producto['stock'];
                    $b->stock = $bom_producto['stock'];

                    
 
                     //$sql_existencias = "SELECT * from get_existencias(34, $producto_existencia, null, null)";
                     //$existencia = $this->db->query($sql_existencias)->fetchAll();
                    $existencia = $this->getKardex(null, null, null, 34, $producto_existencia);
                    if ($existencia > 0) {
                        $b->existencia = $existencia;
                    } else {
                        $b->existencia = 0;
                        // $aux++;
                    }
                    if ($bom_producto['stock'] > $b->existencia) {
                        $cantidades_suficientes = 'no';
                        $b->cantidades_suficientes= $cantidades_suficientes;
                        // print_r($producto_existencia);
                        $product = Products::findFirst($producto_existencia)->name;
                        // $b->product_name=$product;
                        // print_r($product);
                        // exit();
                        }
                    array_push($bom_by_producto, $b);

                }
                //echo("<pre>");
                //print_r($bom_by_producto);
                //echo("</pre>");
                //exit();
            }
        }
            
 //  print_r($sql);
 //  exit();
             // $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
             //$content['bom'] = $data->fetchAll();
            $content['bom'] = $bom_by_producto;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
   
   
    
  

    
    public function getOptions () {
        $sql = "";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

  


    public function create ()
    {
        try {
            if ($this->userHasPermission()) {

                $flagRegistry = true;
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $content = $this->content;

                if (is_numeric($request['product']['id'])) {

                    $bom = new Bom();
                    $bom->setTransaction($tx);

                    $bom->product_id = intval(($request['id']));
                    $bom->amount = ($request['cantidad']);;
                    $bom->material_id = intval(($request['product']['id']));;

                    if ($bom->create()) {
                     $this->content['bom'] = $bom;
                     $this->content['result'] = true;
                     $this->content['message'] = Message::success('El componente ha sido agregado.');
                     $tx->commit();
                 } else {
                    $this->content['error'] = Helpers::getErrors($bom);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar Agregar el componente.');
                    $tx->rollback();
                }
            }
            else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPut();
                 // print_r($request);
                if (is_numeric($id)) {
                    $tx = $this->transactions->get();
                    $bom = Bom::findFirst($id);
                        if ($bom) {
                            $validUser = Auth::getUserData($this->config);
                            $bom->setTransaction($tx);
                            // $bom->product_id = intval(($request['id']));
                            $bom->amount =($request['cantidad']);;
                            $bom->material_id = intval(($request['product']['id']));;
                            if ($bom->update()) {
                                $this->content['bom'] = $bom;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El componente ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($bom);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el componente.');
                                $tx->rollback();
                            }
                        }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

   

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $bom = Bom::findFirst($id);

                if ($bom) {
                    $bom->setTransaction($tx);

                    if ($bom->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El componente ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($bom);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el componente.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El producto no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 5 OR role_id = 3 OR role_id = 4)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userIsClient ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 14 OR role_id = 16 OR role_id = 3)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    public function getKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        if ($this->userHasPermission()) {
            $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId);
            /*echo "<pre>";
            print_r($kardexAux);
            exit();*/
            $kardex = [];
            $tot=0; 
            $stock = 0;
       
               
                for ($j=0; $j < sizeof($kardexAux); $j++) {
                        // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
                        if ($kardexAux[$j]['movement_type'] == 1) {
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 2) {
                            $stock -= $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 3) {
                            $stock = $kardexAux[$j]['qty'];
                         }  elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
                             $stock += $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
                            $stock -= $kardexAux[$j]['qty'];
                            // Obtencion de la entrada ;                       
                        }
                    // $tot+=$stock
            }
            $tot = $stock;
        } else {
            return 0;
        }
        return $tot;
    }
    public function generateKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' ";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 6 END";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }
        $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }

}
