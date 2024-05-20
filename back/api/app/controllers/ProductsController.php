
<?php

use Phalcon\Mvc\Controller;

class ProductsController extends Controller {
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getProducts ($pt = 0) {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT p.id, p.line_id, p.old_code, p.code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_code, p.name, f.name AS family, p.photo, p.active, p.clave_producto_id, u.name as unit, p.description,p.mark_id, wm.name as mark_name
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    LEFT JOIN wms_products AS f
                    ON p.family_id = f.id
                    INNER JOIN wms_units AS u
                    ON p.unit_id = u.id
                    LEFT JOIN wms_marks AS wm
                    ON p.mark_id = wm.id;";

             $data = $this->db->query($sql);
             $content['products'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function uploadFileProducts() {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/customers/';
            if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            }
            $fullPath = '';
            
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                
                if ( $file->moveTo($fullPath)) {
                    $csvData = Helpers::csv2array($fullPath);
                    $headerRow = array_shift($csvData);
                    $mdata = [];
                    $code = '';
                    $i = 1;
                    
                    foreach($csvData as $valuekey){
                        $idproducto = intval($valuekey[0]);
                        $productsExist = Products::findFirst("id = $idproducto");
                        if ($i < 10)  {
                            $code = '0000'.$i;
                            
                        } else if ($i >= 10 && $i < 100) {
                            $code = '000'.$i;
                            
                        }else if ($i >= 100 && $i < 1000) {
                            $code = '00'.$i;
                            
                        }else if ($i >= 1000 && $i < 10000) {
                            $code = '0'.$i;
                        }else if ($i >= 10000) {
                            $code = $i;
                        }
                        if ($productsExist) {
                            //$productEdit = new Products();
                            $productsExist->setTransaction($tx);
                            $productsExist->code = $code;
                            $productsExist->update();
                            
                        }
                        $i += 1;
                    }
                    //fclose($handle);
                    
                    $tx->commit();
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                    $tx->rollback();
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function getCsvProducts ($categoryId, $lineId) {
        $content = $this->content; 
        $where = "WHERE p.active = true";
        if ($categoryId == 'TODOS') {} else if(empty($categoryId)){}else {$where .= " AND l.category_id = $categoryId";}
        if ($lineId == 'TODOS') {} else if(empty($lineId)){}else {$where .= " AND p.line_id = $lineId";}
        $sql ="SELECT c.name as category,l.name as line, CONCAT(c.code,'-',l.code,'-',p.code) as code, p.name, p.active as status, u.name as unit,p.barcode,p.description,p.clave_producto_id as clave,
                (select pp.price as price_a from wms_products_prices as pp where price_level = 'A' and pp.product_id = p.id),
                (select pp.price as price_b from wms_products_prices as pp where price_level = 'B' and pp.product_id = p.id),
                (select pp.price as price_c from wms_products_prices as pp where price_level = 'C' and pp.product_id = p.id),
                (select pp.price as price_d from wms_products_prices as pp where price_level = 'D' and pp.product_id = p.id),
                (select pp.price as price_e from wms_products_prices as pp where price_level = 'E' and pp.product_id = p.id)
                FROM wms_products as p 
                INNER JOIN wms_lines as l
                ON l.id = p.line_id 
                INNER JOIN wms_units as u
                ON u.id = p.unit_id
                INNER JOIN wms_categories as c
                ON c.id = l.category_id
                {$where}
                ORDER BY p.id";
        $products = $this->db->query($sql)->fetchAll();       
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, [utf8_decode('CATEGORÍA'),utf8_decode('LÍNEA'),utf8_decode('CÓDIGO'),'NOMBRE DEL PRODUCTO','UNIDAD','ESTATUS',utf8_decode('CÓDIGO DE BARRAS'),utf8_decode('DESCRIPCIÓN'),'CLAVE DE PRODUCTO','PRECIO A','PRECIO B','PRECIO C','PRECIO D','PRECIO E'], ',');
        if (count($products)) {
            foreach ($products as $pr) {
                fputcsv($fp, [
                    utf8_decode($pr['category']),
                    utf8_decode($pr['line']),
                    ($pr['code']),
                    utf8_decode($pr['name']),
                    ($pr['unit']),
                    ($pr['status'] == 1 ? 'ACTIVO' : ($pr['status'] == 2 ? 'INACTIVO' : 'OTRO')),
                    ($pr['barcode']),
                    ($pr['description']),
                    ($pr['clave']),
                    ($pr['price_a']),
                    ($pr['price_b']),
                    ($pr['price_c']),
                    ($pr['price_d']),
                    ($pr['price_e']),
                ], ',');
            }
            $content['result'] = 'success';
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Productos-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getCsvProductsPrices ($categoryId, $lineId, $markId) {
        $content = $this->content; 
        $where = "WHERE p.active = true";
        if ($categoryId == 'TODOS') {} else if(empty($categoryId)){}else {$where .= " AND l.category_id = $categoryId";}
        if ($lineId == 'TODOS') {} else if(empty($lineId)){}else {$where .= " AND p.line_id = $lineId";}
        if ($markId == 'TODOS') {} else if(empty($markId)){}else {$where .= " AND p.mark_id = $markId";}
        $sql ="SELECT c.name as category,l.name as line, CONCAT(c.code,'-',l.code,'-',p.code) as code, p.name, p.active as status, u.name as unit,p.barcode,p.description,p.clave_producto_id as clave,m.name as marca, p.supplier_code as code_supplier,
                (select pp.price as price_a from wms_products_prices as pp where price_level = 'A' and pp.product_id = p.id),
                (select pp.price as price_b from wms_products_prices as pp where price_level = 'B' and pp.product_id = p.id),
                (select pp.price as price_c from wms_products_prices as pp where price_level = 'C' and pp.product_id = p.id),
                (select pp.price as price_d from wms_products_prices as pp where price_level = 'D' and pp.product_id = p.id),
                (select pp.price as price_e from wms_products_prices as pp where price_level = 'E' and pp.product_id = p.id)
                FROM wms_products as p 
                INNER JOIN wms_lines as l
                ON l.id = p.line_id 
                INNER JOIN wms_units as u
                ON u.id = p.unit_id
                INNER JOIN wms_categories as c
                ON c.id = l.category_id
                INNER JOIN wms_marks as m
                ON m.id = p.mark_id
                {$where}
                ORDER BY p.id";
                // print_r($sql);
                // exit();
        $products = $this->db->query($sql)->fetchAll();       
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, [utf8_decode('CÓDIGO'),'NOMBRE DEL PRODUCTO','PRECIO A','PRECIO B','PRECIO C','PRECIO D','PRECIO MOSTRADOR','MARCA', utf8_decode('CÓDIGO PROVEEDOR')], ',');
        if (count($products)) {
            foreach ($products as $pr) {
                fputcsv($fp, [
                    ($pr['code']),
                    utf8_decode($pr['name']),
                    utf8_decode($pr['price_a']),
                    utf8_decode($pr['price_b']),
                    utf8_decode($pr['price_c']),
                    utf8_decode($pr['price_d']),
                    utf8_decode($pr['price_e']),
                    utf8_decode($pr['marca']),
                    utf8_decode($pr['code_supplier'])
                ], ',');
            }
            $content['result'] = 'success';
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=ProductosPrecios-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getProductsByPagination () {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request['category'],$request['line'],$request['status'],$request['mark'],$request);
            $this->content['products'] = $response['data'];
            $this->content['productsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    
    public function getProductsPricesByPagination () {
        $request = $this->request->getPost();
        if ($this->userHasPermissionToGetPrices()){
            $response = $this->getGridPricesSQL($request['category'],$request['line'],$request['status'],$request['mark'],$request,$request['description']);
            $this->content['prices'] = $response['prices'];
            $this->content['productsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getReportPurchases () {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getReportPurchasesSQL($request);
            $this->content['products'] = $response['data'];
            $this->content['productsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridPricesSQL ($category,$line,$status,$mark,$request,$description) {
        $validUser = Auth::getUserData($this->config);
        $User =Users::findFirst($validUser->id);
        $where = 'WHERE p.id > 0 ';
        if ($status == false) {} else {$where .= " AND p.active = $status ";}
        if ($category == 'TODOS') {} else if($category == ''){}else {$where .= " AND l.category_id = $category";}
        if ($line == 'TODOS') {} else if($line == ''){}else {$where .= " AND l.id = $line";}
        if ($description == 'TODOS') {} else if($description == ''){}else {$where .= " AND (p.name ILIKE '%".$description."%' OR p.old_code ILIKE '%".$description."%'  OR p.description ILIKE '%".$description."%')";;}
        if(is_array($mark)){
            if ($mark['label'] == 'TODOS') {
            }  else {
                $mk= $mark['value'];
                $where .= " AND mk.id =$mk";
            }
        }
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)){
            $where .= " AND ( p.id::text ILIKE '%".$filter."%' OR p.barcode ILIKE '%".$filter."%' OR p.name ILIKE '%".$filter."%' OR p.old_code ILIKE '%".$filter."%' OR l.name ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR CONCAT(c.code,'-',l.code,'-',p.old_code) ILIKE '%".$filter."%' OR p.description ILIKE '%".$filter."%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if($pagination['sortBy'] == 'category'){
                $sortBy .= " ORDER BY c.name";
            }
            if($pagination['sortBy'] == 'line'){
                $sortBy .= " ORDER BY l.name" ;
            }
            if($pagination['sortBy'] == 'mark'){
                $sortBy .= " ORDER BY p.mark_id" ;
            }
            if($pagination['sortBy'] != 'line' && $pagination['sortBy'] != 'category'){
                $sortBy .= " ORDER BY p." . trim($pagination['sortBy']);
            }

        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(p.id) AS count
                FROM wms_products AS p
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                INNER JOIN wms_marks as mk
                ON mk.id = p.mark_id
            {$where}";
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql ="SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_code, p.name, p.active,p.description,mk.name as mark,p.rebasa_code,
        price_a,price_b,price_c,price_d,price_e,
        coalesce(existenciarebasa,0) as existenciarebasa,coalesce(existenciabodega,0) as existenciabodega,coalesce(existenciamangueras,0) as existenciamangueras,coalesce(existenciasalle,0) as existenciasalle,coalesce(existenciarodamientos,0) as existenciarodamientos
                FROM wms_products as p 
                INNER JOIN wms_lines as l ON l.id = p.line_id 
                INNER JOIN wms_units as u ON u.id = p.unit_id
                INNER JOIN wms_categories as c ON c.id = l.category_id
                INNER JOIN wms_marks as mk ON mk.id = p.mark_id
                LEFT JOIN v_product_prices pp on pp.product_id = p.id
                LEFT JOIN v_product_prices_stock ps on ps.product_id = p.id
                {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
                $products = $this->db->query($sql)->fetchAll();
        $response = array('prices' => $products, 'rowCounts' => $productsCount[0]['count']);
        return $response;  
    }

    public function getGridSQL ($category,$line,$status,$mark,$request) {
        $where = 'WHERE p.id > 0 ';
        if ($status == false) {} else {$where .= " AND p.active = $status ";}
        if ($category == 'TODOS') {} else if($category == ''){}else {$where .= " AND l.category_id = $category";}
        if ($line == 'TODOS') {} else if($line == ''){}else {$where .= " AND l.id = $line";}
        if(is_array($mark)){
            if ($mark['label'] == 'TODOS') {
            }  else {
                $mk= $mark['value'];
                $where .= " AND mk.id =$mk";
            }
        }
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( p.id::text ILIKE '%".$filter."%' OR p.name ILIKE '%".$filter."%' OR p.old_code ILIKE '%".$filter."%' OR l.name ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR CONCAT(c.code,'-',l.code,'-',p.old_code) ILIKE '%".$filter."%' OR p.description ILIKE '%".$filter."%' OR mk.name ILIKE '%".$filter."%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if($pagination['sortBy'] == 'category'){
                $sortBy .= " ORDER BY c.name";
            }
            if($pagination['sortBy'] == 'line'){
                $sortBy .= " ORDER BY l.name" ;
            }
            if($pagination['sortBy'] == 'mark'){
                $sortBy .= " ORDER BY p.mark_id" ;
            }
            if($pagination['sortBy'] != 'line' && $pagination['sortBy'] != 'category'){
                $sortBy .= " ORDER BY p." . trim($pagination['sortBy']);
            }

        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(p.id) AS count
                FROM wms_products AS p
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_marks AS mk
                ON p.mark_id = mk.id
            {$where}";
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_code, p.name, p.photo, p.active, p.clave_producto_id,p.description,p.mark_id, mk.name as mark_name,rebasa_code
                FROM wms_products AS p
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_marks AS mk
                ON p.mark_id = mk.id
                {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $productsCount[0]['count']);
        return $response;
    }
    
    public function getReportPurchasesSQL ($request) {
        $y = date('Y');
        $where = "";
        $order = "";

        $sortBy = "";
        $pagination = $request['pagination'];
        $filter = $request['filter'];

        if (!empty($filter['searchbar'])) {
            $where = "WHERE (code ILIKE '%".$filter['searchbar']."%' or old_code ILIKE '%".$filter['searchbar']."%' or p.name ILIKE '%".$filter['searchbar']."%')";
        }

        if (!empty($filter['product'])) {
            $where = empty($where) ? "WHERE" : $where." AND";
            $where .= " p.id = {$filter['product']}";
        }

        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage']." ";

        $sql = "SELECT count(*) from wms_products";
        $productsCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT DISTINCT ON (p.id) 
                    p.id, 
                    code, 
                    old_code, 
                    p.name as product, 
                    requested_date, 
                    price, 
                    s.name as supplier 
                from wms_products as p 
                left join pur_order_details as od on od.product_id = p.id
                left join pur_orders as o on o.id = od.po_id
                left join pur_suppliers as s on s.id = o.supplier_id
                {$where}
                order by p.id, requested_date desc
                {$offset} {$limit}
                ";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $productsCount[0]['count']);
        return $response;
    }
    
    public function getActiveProducts ($pt = 0) {
        if ($this->userIsClient()) {
            $validUser = Auth::getUserData($this->config);
            $customerUser = CustomerUsers::findFirst("user_id = $validUser->id");
            if ($customerUser) {
                $customer = Customers::findFirst($customerUser->customer_id);
                $sql = "SELECT p.id, p.line_id,p.code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_code, p.name, p.photo, p.active, pp.price
                        FROM wms_products AS p
                        INNER JOIN wms_lines AS l
                        ON p.line_id = l.id
                        INNER JOIN wms_categories AS c
                        ON l.category_id = c.id
                        INNER JOIN wms_products_prices AS pp
                        ON pp.product_id = p.id AND pp.price_level = '$customer->price_list'
                        WHERE p.active;";
                $data = $this->db->query($sql);
                $content['products'] = $data->fetchAll();
            }
        } else {
            $sql = "SELECT p.id, p.line_id, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-',p.code) AS code, p.name, p.photo, p.active
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    WHERE p.active;";
            $data = $this->db->query($sql);
            $content['products'] = $data->fetchAll();
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getProduct ($id) {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $product = null;
            if (is_numeric($id)) {
                $sql = "SELECT p.id, p.code, p.description, p.name, p.old_code, u.name as unit, p.unit_id, p.family_id, p.rebasa_code, p.supplier_code, p.photo, 
                p.line_id, l.name AS line, l.category_id, c.name AS category, f.name AS family, p.active, p.clave_producto_id, CONCAT(c.code,'-',l.code,'-') AS product_code, 
                p.barcode, p.mark_id, wm.name as mark_name, p.weight, p.additional_information
                        FROM wms_products AS p
                        INNER JOIN wms_lines AS l
                        ON p.line_id = l.id
                        INNER JOIN wms_categories AS c
                        ON l.category_id = c.id
                        INNER JOIN wms_units AS u
                        ON p.unit_id = u.id
                        LEFT JOIN wms_products AS f
                        ON p.family_id = f.id
                        LEFT JOIN wms_marks AS wm
                        ON p.mark_id = wm.id
                        WHERE p.id = $id;";

                $data = $this->db->query($sql);
                $product = $data->fetch();
            }
            $content['product'] = $product;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getProductsByLineId ($lineId) {
        $content = $this->content;
        $products = [];
        if (is_numeric($lineId)) {
            $sql = "SELECT p.id, p.line_id, p.code, p.name as label, p.family_id, p.photo, CONCAT(c.code,'-',l.code,'-',p.code) AS code, l.name AS line, c.name AS category, f.name AS family
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    LEFT JOIN wms_products AS f
                    ON p.family_id = f.id
                    WHERE p.line_id = $lineId
                    AND p.active;";

            $data = $this->db->query($sql);
            $products = $data->fetchAll();
        }
        $content['products'] = $products;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getAllProducts () {
        $content = $this->content;
        $products = [];
        
            $sql = "SELECT wp.id , CONCAT(wp.code,' - ',wp.name) as label 
            FROM wms_products as wp 
            LEFT JOIN wms_lines as wl 
            ON wl.id = wp.line_id
            LEFT JOIN wms_categories as wc 
            ON wc.id = wl.category_id
            WHERE wp.active
            ORDER BY wp.name";
            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $products = $data->fetchAll();
        
        $content['products'] = $products;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getProductsByCategoryId ($categoryId) {
        $content = $this->content;
        $products = [];
        if (is_numeric($categoryId)) {
            $sql = "SELECT p.id, p.line_id, p.code, p.name, p.family_id, p.photo, CONCAT(c.code,'-',l.code,'-',p.code) AS code, l.name AS line, c.name AS category, f.name AS family
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    LEFT JOIN wms_products AS f
                    ON p.family_id = f.id
                    WHERE l.category_id = $categoryId
                    AND p.active;";
            $data = $this->db->query($sql);
            $products = $data->fetchAll();
        }
        $content['products'] = $products;
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getProductsByCategoryId1 ($categoryId) {
        $content = $this->content;
        $products = [];
        if (is_numeric($categoryId)) {
            $sql = "SELECT wp.id , CONCAT(wp.code,' - ',wp.name) as label 
                    FROM wms_products as wp 
                    LEFT JOIN wms_lines as wl 
                    ON wl.id = wp.line_id
                    LEFT JOIN wms_categories as wc 
                    ON wc.id = wl.category_id
                    WHERE  wl.category_id= $categoryId
                    ORDER BY wp.name";
            $data = $this->db->query($sql);
            $products = $data->fetchAll();
        }
        $content['products'] = $products;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getProductsBy () {
        $content = $this->content;
        $products = [];
        
            $sql = "SELECT wp.id , CONCAT(wp.code,' - ',wp.name) as label 
                    FROM wms_products as wp 
                    LEFT JOIN wms_lines as wl 
                    ON wl.id = wp.line_id
                    LEFT JOIN wms_categories as wc 
                    ON wc.id = wl.category_id
                    WHERE  wc.code = 'PRT' or wc.code = 'PRI' or wc.code = 'PT'
                    ";
            $data = $this->db->query($sql);
            $products = $data->fetchAll();
        
        $content['products'] = $products;
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function productsWithPrice ($priceLevel,$type_order,$id) {
        $auxpl="";
        $auxpl=$priceLevel;

        $sql = "SELECT pp.id as price_id, p.id,p.name,pp.price_level, pp.price, p.old_code,CONCAT(c.code,'-',l.code,'-',p.code) AS code, p.name, p.photo,p.line_id, l.name AS line, l.category_id, c.name AS category,'' as photos,
            '' as PhotoInicial ,vs.stock
        from wms_products as p
        inner join wms_products_prices as pp on pp.product_id = p.id
        INNER JOIN wms_lines AS l ON p.line_id = l.id
        INNER JOIN wms_categories AS c ON l.category_id = c.id
        LEFT JOIN v_product_stock AS vs on vs.storage_id = $id and vs.product_id = p.id
        where pp.price_level = '".$auxpl."' and p.active = true limit 25";

        $data = $this->db->query($sql);
        $products = $data->fetchAll();
        for ($i=0; $i <count($products) ; $i++) {
                $p_id =$products[$i]['id'];
                $sqlP="SELECT id, photo as url FROM wms_product_photos where product_id = $p_id order by id asc limit 1;";
                $photos = $this->db->query($sqlP)->fetchAll();
                if($photos){
                    $products[$i]['photo'] = $photos[0]['url'];
                }else{
                    $products[$i]['photo'] = null;
                }
                $products[$i]['photos'] = $photos;
        }
        $content['products'] = $products;
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function productsWithPricebyFilter () {
        $request = $this->request->getPost();
        $auxpl="";
        $auxpl=$request['priceLevel'];
        $filter= $request['filter'];
        $id= $request['idStorage'];
        $f=strtoupper($filter.'%');

        $sql = "SELECT pp.id as price_id, p.id,p.name,pp.price_level, pp.price, p.old_code,CONCAT(c.code,'-',l.code,'-',p.code) AS code, p.name, p.photo,p.line_id, l.name AS line, l.category_id, c.name AS category,'' as photos,
            '' as PhotoInicial , vs.stock
        from wms_products as p
        INNER join wms_products_prices as pp on pp.product_id = p.id
        INNER JOIN wms_lines AS l 
        ON p.line_id = l.id
        INNER JOIN wms_categories AS c 
        ON l.category_id = c.id
        LEFT JOIN v_product_stock AS vs on vs.storage_id = $id and vs.product_id = p.id
        where pp.price_level = '".$auxpl."' and p.active = true and (p.name like '$f' or p.rebasa_code like '$f' or p.barcode like '$f') limit 25";
        $data = $this->db->query($sql);
        $products = $data->fetchAll();
        for ($i=0; $i <count($products) ; $i++) {
            $p_id =$products[$i]['id'];
            $sqlP="SELECT id, photo as url FROM wms_product_photos where product_id = $p_id order by id asc limit 1;";
            $photos = $this->db->query($sqlP)->fetchAll();
            if($photos){
                $products[$i]['photo'] = $photos[0]['url'];
            }else{
                $products[$i]['photo'] = null;
            }
        $products[$i]['photos'] = $photos;
        }
        
        $content['products'] = $products;
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getProductsWithStock () {
        $content = $this->content;
        $sql = "SELECT s2.storage_id, s2.product_id, s2.unit_id, s2.total, p.name AS product, u.name AS unit
                FROM (
                    SELECT storage_id, product_id, unit_id, SUM(qty) AS total
                    FROM (
                        SELECT m.storage_id, m.date, md.product_id, 1 AS unit_id, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty
                        FROM wms_movements AS m
                        INNER JOIN wms_movement_details AS md
                        ON md.movement_id = m.id
                        UNION ALL
                        SELECT m.storage_id, m.date, sd.product_id, 1 AS unit_id, CASE WHEN m.type = 1 THEN sd.qty WHEN m.type = 2 THEN (sd.qty * -1) END AS qty
                        FROM wms_movements AS m
                        INNER JOIN pur_shipments AS s
                        ON s.movement_id = m.id
                        INNER JOIN pur_shipment_details AS sd
                        ON sd.shipment_id = s.id
                    ) AS s1
                    GROUP BY storage_id, product_id, unit_id
                ) AS s2
                INNER JOIN wms_products AS p
                ON s2.product_id = p.id
                INNER JOIN wms_units AS u
                ON s2.unit_id = u.id
                WHERE total > 0
                AND p.active;";

        $data = $this->db->query($sql);
        $content['products'] = $data->fetchAll();
        $content['result'] = true;

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT p.id AS value, p.name AS label, p.family_id AS family, p.line_id AS line , p.unit_id as unit
                FROM wms_products AS p
                INNER JOIN wms_lines AS l
                ON l.id = p.line_id
                INNER JOIN wms_categories AS c
                ON c.id = l.category_id
                WHERE p.active
                ORDER BY label ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsKardex () {
        $sql = "SELECT p.id AS value, CONCAT(c.code,'-',l.code,'-',p.code,' ', p.name) AS label, p.family_id AS family, p.line_id AS line , p.unit_id as unit, CONCAT(c.code,'-',l.code,'-',p.code) as codigo
                FROM wms_products AS p
                INNER JOIN wms_lines AS l
                ON l.id = p.line_id
                INNER JOIN wms_categories AS c
                ON c.id = l.category_id
                WHERE p.active
                ORDER BY label ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsByLineId ($lineId) {
        $options = [];
        if (is_numeric($lineId)) {
            $sql = "SELECT p.id AS value, CONCAT(c.code,'-',l.code,'-',p.name) AS label, p.family_id AS family, p.line_id AS line
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE l.id = $lineId
                    AND p.active
                    ORDER BY label ASC;";
            $options = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        }
        $this->content['options'] = $options;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsByCategoryId ($categoryId) {
        $options = [];
        if (true) {
            $sql = "SELECT p.id AS value, p.name AS label FROM wms_products AS p
                    WHERE p.active
                    ORDER BY label ASC;";
            $options = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        }
        $this->content['options'] = $options;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsByPurchaseOrder ($purchaseOrderId) {
        $options = [];
        $options2 = [];
        if (is_numeric($purchaseOrderId)) {
            $sql = "SELECT p.id AS value, CONCAT(p.name) AS label, qty
                    FROM wms_products AS p
                    INNER JOIN pur_order_details AS od
                    ON od.product_id = p.id
                    WHERE od.po_id = $purchaseOrderId
                    AND p.active
                    ORDER BY label ASC;";
            $options = $this->db->query($sql)->fetchAll();
            $shipment = new ShipmentDetails();
            foreach ($options as $key => $value) {
                $product = $options[$key]['value'];
                $sql = "SELECT product_id, COALESCE(sum(qty), 0) as total from pur_shipment_details 
                join pur_shipments on pur_shipments.id = pur_shipment_details.shipment_id
                join pur_orders on pur_orders.id = pur_shipments.order_id
                where order_id = {$purchaseOrderId} and product_id = {$product}
                group by product_id";
                $qty = $this->db->query($sql)->fetch();
                $total = $options[$key]['qty'] - ($qty !== false ? $qty['total'] : 0);
                if ($total > 0) {
                    array_push($options2,$value);
                }
            }
            $this->content['result'] = true;
        }
        $this->content['options'] = $options2;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsByShipment ($shipmentId) {
        $options = [];
        if (is_numeric($shipmentId)) {
            $sql = "SELECT DISTINCT p.id AS value, CONCAT(c.code,'-',l.code,'-',p.name) AS label
                    FROM wms_products AS p
                    INNER JOIN pur_shipment_details AS sd
                    ON sd.product_id = p.id
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE sd.shipment_id = $shipmentId
                    AND p.active
                    ORDER BY label ASC;";
            $options = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        }
        $this->content['options'] = $options;
        $this->response->setJsonContent($this->content);   
    }

    public function getMainOptions () {
        $sql = "SELECT p.id AS value, CONCAT(c.code,'-',l.code,'-',p.name) AS label
                FROM wms_products AS p
                INNER JOIN wms_lines AS l
                ON l.id = p.line_id
                INNER JOIN wms_categories AS c
                ON c.id = l.category_id
                WHERE family_id IS NULL
                AND p.active
                ORDER BY label DESC;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsByFamilyId ($familyId) {
        $options = [];
        if (is_numeric($familyId)) {
            $sql = "SELECT p.id AS value, CONCAT(c.code,'-',l.code,'-',p.name) AS label
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE p.family_id = $familyId
                    AND p.active
                    OR p.id = $familyId
                    ORDER BY label DESC;";
            $options = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        }
        $this->content['options'] = $options;
        $this->response->setJsonContent($this->content);   
    }

    public function create () {
        try {
            $flagRegistry = true;
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $content = $this->content;

            if (is_numeric($request['line_id'])) {
                if ($this->userHasPermission()) {
                    $sql = "SELECT id
                            FROM wms_products
                            WHERE line_id = ".$request['line_id']."
                            AND name = '".$request['name']."';";
                    $data = $this->db->query($sql);
                    $products = $data->fetchAll();
                    if (count($products) > 0) {
                        $flagRegistry = false;
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra registrado un producto con el mismo nombre y línea.');
                    }

                    if ($flagRegistry) {
                        $auxCode ="";
                        $sqlx = "SELECT MAX(code) AS code FROM wms_products";
                        $info = $this->db->query($sqlx)->fetch();
                        if($info){
                            $auxCode =$info['code']+1;
                        }else {
                            $auxCode =1;
                        }
                        $product = new Products();
                        $product->setTransaction($tx);
                        $product->name = strtoupper($request['name']);
                        $product->code = strtoupper($auxCode);
                        $product->line_id = $request['line_id'];
                        $product->barcode = ($request['barcode'] == '') ? null : ($request['barcode']);
                        $product->rebasa_code = $request['rebasa_code'];
                        $product->supplier_code = $request['supplier_code'];
                        $product->weight = $request['weight'];
                        $product->additional_information = $request['additional_information'];
                        if(!empty($request['clave_producto_id'])){
                            $product->clave_producto_id = $request['clave_producto_id'];
                        }
                        $product->unit_id = $request['unit_id'];
                        $product->old_code = strtoupper($request['old_code']);
                        if (isset($request['family_id']) && $request['family_id'] != null) {
                            $product->family_id = $request['family_id'];
                        }
                        if (isset($request['description']) && $request['description'] != null) {
                            $product->description = $request['description'];
                        }
                        if(is_array($request['mark'])){
                                $product->mark_id = ($request['mark']['value'] == '') ? null : intval($request['mark']['value']);
                            }else {
                                $product->mark_id = ($request['mark'] == '') ? null : intval($request['mark']);
                            }
                        

                        if ($product->create()) {
                            $this->content['product'] = $product;
                            $this->content['result'] = true;
                            $this->content['id'] = $product->id;
                            $this->content['message'] = Message::success('El producto ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($product);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el producto.');
                            // $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id) {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPut();
                if (is_numeric($id) && isset($request['line_id']) && is_numeric($request['line_id'])) {
                    $tx = $this->transactions->get();
                    $product = Products::findFirst($id);
                    $flagRegistry = true;
        
                    $sql = "SELECT id
                            FROM wms_products
                            WHERE id <> $id
                            AND line_id = ".$request['line_id']."
                            AND name = '".$request['name']."';";
                    $data = $this->db->query($sql);
                    $products = $data->fetchAll();
                    if (count($products) > 0) {
                        $flagRegistry = false;
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra registrado un producto con el mismo nombre y línea.');
                    }
                    $sql = "SELECT id
                            FROM wms_products
                            WHERE id <> $id
                            AND code = '".$request['code']."';";
                    $data = $this->db->query($sql);
                    $products = $data->fetchAll();
                    if (count($products) > 0) {
                        $flagRegistry = false;
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra registrado un producto con el mismo codigo.');
                    }
        
                    if ($flagRegistry) {
                        if ($product) {
                            $validUser = Auth::getUserData($this->config);
                            $product->setTransaction($tx);
                            $product->name = strtoupper($request['name']);
                            $product->code = strtoupper($request['code']);;
                            $product->old_code = strtoupper($request['code']);
                            $product->line_id = $request['line_id'];
                            $product->barcode = ($request['barcode'] == '') ? null : ($request['barcode']);
                            $product->rebasa_code = $request['rebasa_code'];
                            $product->supplier_code = $request['supplier_code'];
                            $product->weight = $request['weight'];
                            $product->additional_information = $request['additional_information'];
                            if(!empty($request['clave_producto_id'])){
                                $product->clave_producto_id = $request['clave_producto_id'];
                            }
                            $product->family_id = null;
                            $product->unit_id = $request['unit_id']['value'];
                            $permission = UserRoles::findFirst("role_id = 1 AND user_id = $validUser->id");
                            if ($permission) {
                                $product->active = $request['active'];
                            }
                            if (isset($request['family_id']) && $request['family_id'] != null) {
                                $product->family_id = $request['family_id'];
                            }
                            if (isset($request['description']) && $request['description'] != null) {
                                $product->description = $request['description'];
                            }
                            if(is_array($request['mark'])){
                                $product->mark_id = ($request['mark']['value'] == '') ? null : intval($request['mark']['value']);
                            }else {
                                $product->mark_id = ($request['mark'] == '') ? null : intval($request['mark']);
                            }


        
                            if ($product->update()) {
                                $this->content['product'] = $product;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El producto ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($product);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el producto.');
                                $tx->rollback();
                            }
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

    public function changePhoto () {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $product = Products::findFirst(intval($request['product_id']));
            if ($product) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/products/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777);
                }
                $fullPath = '';
                foreach ($this->request->getUploadedFiles() as $file) {
                    $this->content['file'] = $file;
                    $this->content['fileExtension'] = $file->getExtension();
                    $id_p=$request['product_id'];
                    $sql="SELECT * FROM wms_product_photos where product_id = $id_p  order by id desc limit 1;";
                    $ultimo = $this->db->query($sql)->fetchAll();
                    if(count($ultimo)==1){
                        $fileName = $request['product_id']."".($ultimo[0]['id']+1).'.'.$file->getExtension();
                    }else {
                        $fileName =  $request['product_id'] . '.' . $file->getExtension();
                    }
                    $fullPath = $upload_dir . $fileName;
                    $this->content['fullPath'] = $fullPath;
                    $ProductPhotos = new ProductPhotos();
                    $ProductPhotos->setTransaction($tx);
                    $ProductPhotos->product_id = $request['product_id'];
                    $ProductPhotos->photo = $fileName;
                    if ($ProductPhotos->create()) {
                        $file->moveTo($fullPath);
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La fotografía ha sido registrada exitosamente.');
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Error al registrar la fotografía.');
                    }
                }
            } else {
                $this->content['result'] = false;
                $this->content['message'] = Message::success('No se ha encontrado el producto.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function deletePhoto () {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $product = ProductPhotos::findFirst(intval($request['id']));
                if ($product) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/products/';
                    if ($product->photo != null && file_exists($upload_dir.$product->photo )) {
                        if (@unlink($upload_dir.$product->photo)) {
                            $product->setTransaction($tx);
                            if ($product->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La imagen ha sido eliminado correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($product);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la imagen.');
                            }
                        }
                    } else {
                        $product->setTransaction($tx);
                        if ($product->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('la imagen ha sido eliminado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($product);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la imagen.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la imagen seleccionado.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        $this->response->setJsonContent($this->content);
    }

    public function getPhotos ($id) {
        try {
            if ($this->userHasPermission()) {
                $sql="SELECT id, photo as url FROM wms_product_photos where product_id = $id;";
                $photos = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
                $this->content['photos'] = $photos;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function getImagesbyCart ($id){
        try {
            if ($this->userHasPermission()) {
                $sql="SELECT id, photo as url FROM wms_product_photos where product_id = $id;";
                $photos = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
                $this->content['photos'] = $photos;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id) {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $product = Products::findFirst($id);
                if ($product) {
                    $product->setTransaction($tx);
                    $this->deleteBom($id);
                    $this->deleteWork ($id);
                    if ($product->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El producto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($product);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el producto.');
                        }
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

    private function deletePrices ($product_id) {}
    
    private function deleteBom ($product_id) {
        try {
            $boms = Bom::find("product_id =".$product_id);
            if ($boms) {    
                foreach($boms as $bom) {
                    $bom->delete();
                }   
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
    }
    
    private function deleteWork ($product_id) {
        try {
            $handiWork = HandiWorkProducts::find("product_id =".$product_id);
            if ($handiWork) {   
                foreach($handiWork as $row) {
                    $row->delete();
                }   
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
    }

    public function addPrice ($id) {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $priceError = false;
                foreach ($request['prices'] as $price) {
                    if (is_numeric($id) && isset($price['price_level']) && strlen($price['price_level']) == 1 && isset($price['price']) && is_numeric($price['price'])) {
                        $productPrice = ProductsPrices::findFirst("product_id = $id AND price_level = '".$price['price_level']."'");
                        if ($productPrice) {
                            $productPrice->price = $price['price'];
                            if (!$productPrice->update()) {
                                $priceError = true;
                            }
                        } else {
                            $productPrice = new ProductsPrices();
                            $productPrice->product_id = $id;
                            $productPrice->price_level = $price['price_level'];
                            $productPrice->price = $price['price'];
                            if (!$productPrice->create()) {
                                $priceError = true;
                            }
                        }
                    }
                }
                if ($priceError) {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar los precios.');
                } else {
                    $tx->commit();
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Los precios han sido actualizados correctamente.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission () {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 or role_id = 22 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 20 OR role_id =26)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    
    private function userHasPermissionToGetPrices () {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 or role_id = 20 or role_id = 25 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 20)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userIsClient () {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 1 OR role_id = 3 or role_id = 20 or role_id = 25 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 20)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    public function searchClave ($filter) {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT c.clave as value, concat(c.clave, ' - ', c.descripcion) as label FROM sat_clave_productos as c where c.clave ILIKE '%".$filter."%' OR c.descripcion ILIKE '%".$filter."%' LIMIT 5";
            $data = $this->db->query($sql);
            $content['claves'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function searchProducts ($filter) {
        $filter = str_replace("%20"," ", $filter);
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT p.id as value, CONCAT (p.old_code,' - ', p.name) as label FROM
                      wms_products AS p 
                      WHERE p.old_code ILIKE '%".$filter."%' OR p.name ~~* '%".$filter."%' LIMIT 20";
            $data = $this->db->query($sql);
            $content['claves'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function uploadFile () {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/documents/';
            if (!is_dir($upload_dir))  {
                mkdir($upload_dir, 0777, true);
            }
            $fullPath = '';
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileName = $file->getName();
                $fullPath = $upload_dir . $fileName;
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if ($file->moveTo($fullPath)) {
                    $csvData = array();
                    if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                            $csvData[] = $data;  
                        }
                    }
                    fclose($handle);
                    foreach ($csvData as $k) {
                        if (!empty($k[0]) ) {
                            $d= $k[0];
                            $sql="SELECT * from wms_products where id = $d";
                            $info = $this->db->query($sql)->fetch();
                            if($info) {
                                for ($ij=0; $ij <5 ; $ij++) {
                                    $productPrice = new ProductsPrices();
                                    $productPrice->product_id = $info['id'];
                                    if($ij==0){
                                        $productPrice->price_level = 'A';
                                        if($k[13]!=""){
                                            $productPrice->price = $k[13];
                                        }
                                    }
                                    if($ij==1){
                                        $productPrice->price_level = 'B';
                                        if($k[14]!=""){
                                            $productPrice->price = $k[14];
                                        }
                                    }
                                    if($ij==2){
                                        $productPrice->price_level = 'C';
                                        if($k[15]!=""){
                                            $productPrice->price = $k[15];
                                        }
                                    }
                                    if($ij==3){
                                        $productPrice->price_level = 'D';
                                        if($k[16]!=""){
                                            $productPrice->price = $k[16];
                                        }
                                    }
                                    if($ij==4){
                                        $productPrice->price_level = 'E';
                                        if($k[17]!=""){
                                            $productPrice->price = $k[17];
                                        }
                                    }
                                    if ($productPrice->create() !== false) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('precios actualizados');
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('Error al subir el archivo.');
                }
            }
            if($this->content['result'] === true ){
                $tx->commit();
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }
    
    private function maxCode() {
        $sql = "SELECT MAX(code) AS code FROM wms_products";
        $info = $this->db->query($sql)->fetch();
        if($info['code']){
            return $info['code']+1;
        }
        return 1;
    }
    
    public function generateStorageInventoryv3 ($branchOfficeId, $storageId, $categoryId, $lineId, $productId,$markId, $request, $user = null) {   
        if($user == null){
            $validUser = Auth::getUserInfo($this->config);
        }else{
            $validUser = Users::findFirst($user);
        }
        $where = 'WHERE p.id > 0 ';
        if ($categoryId != 'TODOS' && $categoryId != ''){$where .= " AND l.category_id = $categoryId";}
        if ($lineId != 'TODOS' && $lineId != ''){$where .= " AND l.id = $lineId";}
        if ($productId != 'TODOS' && $productId != ''){$where .= " AND p.id = $productId";}
        if(is_array($markId)){
            if ($markId['label'] == 'TODOS') {
            }  else {
                $mk= $markId['value'];
                $where .= " AND ma.id =$mk";
            }
        }
        if ($branchOfficeId != 'TODOS' && $branchOfficeId != '') {$where .= " AND s.branch_office_id = $branchOfficeId ";}
        if ($storageId != 'TODOS' && $storageId != '') {$where .= " AND ps.storage_id = $storageId ";}
        $sortBy = "";
        $pagination = $request['pagination'];
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            switch ($pagination['sortBy']) {
                case 'category':
                    $sortBy .= " ORDER BY c.name";
                    break;
                case 'line':
                    $sortBy .= " ORDER BY l.name";
                    break;
                case 'product':
                    $sortBy .= " ORDER BY p.name";
                    break;
                case 'code':
                    $sortBy .= " ORDER BY c.code||'-'||l.code||'-'||p.code";
                    break;
                case 'stock':
                    $sortBy .= " ORDER BY sum(ps.stock)";
                    break;
                default:
                    # code...
                    break;
            }
        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = empty($pagination['rowsPerPage'])?"":" LIMIT " . $pagination['rowsPerPage'];
        $sql = "select  l.category_id, c.code AS category_code, c.name AS category_name,ps.product_id, p.code AS product_code, p.name AS product_name,
        p.line_id, l.code AS line_code, l.name AS line_name,p.active as product_status,ma.name as marca,sum(ps.stock) as stock
        from v_product_stock ps
        JOIN wms_storages AS s ON s.id = ps.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = ps.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN wms_marks AS ma ON p.mark_id = ma.id
        $where
        group by l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name
        {$sortBy} {$desc} {$limit} {$offset} ";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data);
        return $response;
    }

    public function generateStorageInventoryv2 ($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date) {
        $movements = $this->generateKardex(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null);
        $products = [];
        $stock = [];
        $productStock =0;
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
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'],'product_status' => $movement['status_product'], 'stock' => $productStock));
            }
        }
        return $productStock;
    }

    public function generateKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId) {
        
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
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
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
