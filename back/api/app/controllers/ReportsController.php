<?php

use Phalcon\Mvc\Controller;

class ReportsController extends Controller
{
public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

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
public function pagbySellers (){
    $request = $this->request->getPost();

        if ($this->userHasPermission()){
            $response = $this->getGridSQLbyPaginationSellers($request['sucursal'],$request['marca'],$request['product'],$request['linea'],$request['seller'],$request['DateOf'],$request['DateUntil'],$request['cliente'],$request);
            $this->content['info'] = $response['data'];
            $this->content['infoCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
}
public function getGridSQLbyPaginationSellers ($sucursalID,$marcaID,$productID,$lineaID,$sellerId,$DateOf,$DateUntil,$clientId,$request) {
    // print_r($sucursal);
    // exit();
  $sucursal = "TODAS";
  $marca = "TODAS";
  $product = "TODOS";
  $linea = "TODAS";
  $seller = "TODOS";
  $client = "TODOS";
    if(is_array($sucursalID)){
      if ($sucursalID['label'] == 'TODAS') {
          $sucursal="null";
      }  else {
          $sucursal= $sucursalID['value'];
      }
  }
  if(is_array($marcaID)){
      if ($marcaID['label'] == 'TODAS') {
         $marca='null';
      }  else {
          $marca= $marcaID['value'];
      }
  }
  if(is_array($productID)){
      if ($productID['label'] == 'TODOS') {
          $product="null";
      }  else {
          $product= $productID['value'];
      }
  }
  if(is_array($lineaID)){
      if ($lineaID['label'] == 'TODAS') {
          $linea="null";
      }  else {
          $linea= $lineaID['value'];
      }
  }
  if(is_array($sellerId)){
      if ($sellerId['label'] == 'TODOS') {
          $seller="null";
      }  else {
          $seller= $sellerId['value'];
      }
  }
  if(is_array($clientId)){
    if ($clientId['label'] == 'TODOS') {
        $client="null";
    }  else {
        $client= $clientId['value'];
    }
}
  // print_r($cliente);
  // exit();
  $rolesusers = "SELECT id from sys_users where role_id = 1";
  $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
  
  $sizeuserrolesspadmin = count($rolesuserquery);
  $auxiforroles = 1;
  
  //-----------------
 /* $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
  inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
  where sys_users.id = $cliente";*/

  
  // $searchrole = "SELECT * from sys_users where id = $cliente";
  // $datarole = $this->db->query($searchrole)->fetchAll();
  $where = 'where ssc.loan = 0  ';
  /*foreach($datarole as $value){
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
  }*/
 /* $validUser = Auth::getUserInfo($this->config);
  $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";
  if ($cliente !== null || $cliente !== '' || $cliente !== 'null') {
  $sql = "SELECT role_id
  FROM sys_users
  WHERE id = $cliente;";
  $currentRoles = $this->db->query($sql)->fetchAll();
  $roles = [];
  foreach ($currentRoles as $role) {
      array_push($roles, intval($role['role_id']));
  }
  }*/
  $y = date("Y");

  if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
  if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
  if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
  if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
  if ($seller == 'TODOS') {} else if($seller == ''){}else {$where .= " AND (su.id = {$seller} or  {$seller} IS NULL)";}
  if ($client == 'TODOS') {} else if($client == ''){}else {$where .= " AND (scus.id = {$client} or  {$client} IS NULL)";}
  
  if ($DateOf == '') {
      $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
  }else{
      $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
  }
  if ($DateUntil == '') {
      $dateFin = date("Y-12-31 00:00:00.000000");
  }else{
      $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
  }
  $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

  $sortBy = "";
  $filter = $request['filter'];
  $pagination = $request['pagination'];        
  if (!empty($filter)){
      $where .= " AND ( wbo.name::text ILIKE '%".$filter."%' OR scus.name ILIKE '%".$filter."%' OR wc.name::text ILIKE '%".$filter."%' OR wl.name::text ILIKE '%".$filter."%' OR wm.name::text ILIKE '%".$filter."%' OR wp.name::text ILIKE '%".$filter."%' OR scus.name::text ILIKE '%".$filter."%')";
      // print_r($where);
      // exit();
  }
  if (!empty($pagination['sortBy'])) {
      $sortBy .= "";
      if($pagination['sortBy'] == 'sucursal'){
          $sortBy .= " ORDER BY ssc.branchoffice";
      }
      if($pagination['sortBy'] == 'marca'){
          $sortBy .= " ORDER BY wm.id";
      }
      if($pagination['sortBy'] == 'product'){
          $sortBy .= " ORDER BY wp.id" ;
      }
      if($pagination['sortBy'] == 'linea'){
          $sortBy .= " ORDER BY pwl.id" ;
      }
      if($pagination['sortBy'] == 'cliente'){
          $sortBy .= " ORDER BY scus.id" ;
      }
      if($pagination['sortBy'] != 'sucursal' && $pagination['sortBy'] != 'marca' && $pagination['sortBy'] != 'product' && $pagination['sortBy'] != 'linea' && $pagination['sortBy'] != 'cliente'){
          $sortBy .= " ORDER BY scus." . trim($pagination['sortBy']);
      }

  } else {
      $sortBy .= " ORDER BY scus.id ";
  }
  $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
  $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
  $limit = " LIMIT " . $pagination['rowsPerPage'];
  $sqla = "SELECT count(wp.id) AS count
  from wms_products as wp
  left join wms_marks as wm
  on wp.mark_id = wm.id
  left join wms_lines as wl
  on wp.line_id = wl.id
  left join wms_categories as wc
  on wl.category_id = wc.id
  left join sls_invoice_in_bulk_details as sibd 
  on wp.id = sibd.product_id
  left join sls_invoices as si 
  on si.id = sibd.invoice_id
  left join sys_users as su
  on su.id = si.seller_id
  left join sls_shopping_cart as ssc
  on ssc.id = si.shopping_cart_id
  left join sls_customers as scus
  on scus.id = ssc.customer_id
  left join wms_branch_offices as wbo
  on wbo.id = ssc.branchoffice
  left join sls_customer_branch_offices as scbo
  on scus.id = scbo.customer_id
  {$where} 
  group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
  ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id
  {$sortBy} {$desc} {$offset};
      ";
  // print_r($sqla);
  // exit();
  
  $productsCount = $this->db->query($sqla)->fetchAll();

  $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
  ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
  TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
  round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
  round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
  (round((sibd.qty*sibd.unit_price)::numeric,2) +
  round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
  CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
  su.nickname as vendedor,si.seller_id,si.id as factura
  from wms_products as wp
  left join wms_marks as wm
  on wp.mark_id = wm.id
  left join wms_lines as wl
  on wp.line_id = wl.id
  left join wms_categories as wc
  on wl.category_id = wc.id
  left join sls_invoice_in_bulk_details as sibd 
  on wp.id = sibd.product_id
  left join sls_invoices as si 
  on si.id = sibd.invoice_id
  left join sys_users as su
  on su.id = si.seller_id
  left join sls_shopping_cart as ssc
  on ssc.id = si.shopping_cart_id
  left join sls_customers as scus
  on scus.id = ssc.customer_id
  left join wms_branch_offices as wbo
  on wbo.id = ssc.branchoffice
  left join sls_customer_branch_offices as scbo
  on scus.id = scbo.customer_id
  {$where}  
  group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
  ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
  {$sortBy} {$desc} {$offset} {$limit}";
    // print_r($sql);
    // exit();
  $data = $this->db->query($sql)->fetchAll();
  
  /*foreach ($data as $key => $inv) {
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
  }*/
  $response = array('data' => $data, 'rowCounts' => count($productsCount));
  return $response;
}
public function pagbyclients (){
    $request = $this->request->getPost();

        if ($this->userHasPermission()){
            $response = $this->getGridSQLbyPaginationClients($request['sucursal'],$request['marca'],$request['product'],$request['linea'],$request['cliente'],$request['DateOf'],$request['DateUntil'],$request);
            $this->content['info'] = $response['data'];
            $this->content['infoCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
}
public function getGridSQLbyPaginationClients ($sucursalID,$marcaID,$productID,$lineaID,$clienteID,$DateOf,$DateUntil,$request) {
          // print_r($sucursal);
          // exit();
        $sucursal = "TODAS";
        $marca = "TODAS";
        $product = "TODOS";
        $linea = "TODAS";
        $cliente = "TODOS";
          if(is_array($sucursalID)){
            if ($sucursalID['label'] == 'TODAS') {
                $sucursal="null";
            }  else {
                $sucursal= $sucursalID['value'];
            }
        }
        if(is_array($marcaID)){
            if ($marcaID['label'] == 'TODAS') {
               $marca='null';
            }  else {
                $marca= $marcaID['value'];
            }
        }
        if(is_array($productID)){
            if ($productID['label'] == 'TODOS') {
                $product="null";
            }  else {
                $product= $productID['value'];
            }
        }
        if(is_array($lineaID)){
            if ($lineaID['label'] == 'TODAS') {
                $linea="null";
            }  else {
                $linea= $lineaID['value'];
            }
        }
        if(is_array($clienteID)){
            if ($clienteID['label'] == 'TODOS') {
                $cliente="null";
            }  else {
                $cliente= $clienteID['value'];
            }
        }
        // print_r($cliente);
        // exit();
        $rolesusers = "SELECT id from sys_users where role_id = 1";
        $rolesuserquery = $this->db->query($rolesusers)->fetchAll();
        
        $sizeuserrolesspadmin = count($rolesuserquery);
        $auxiforroles = 1;
        
        //-----------------
       /* $searchbranchofficeforuser = "SELECT wms_branch_offices.id from sys_users
        inner join wms_branch_offices on wms_branch_offices.id =  sys_users.branch_office_id
        where sys_users.id = $cliente";*/

        
        // $searchrole = "SELECT * from sys_users where id = $cliente";
        // $datarole = $this->db->query($searchrole)->fetchAll();
        $where = 'where ssc.loan = 0  and si.status = '."'ENVIADO' ";
        /*foreach($datarole as $value){
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
        }*/
       /* $validUser = Auth::getUserInfo($this->config);
        $where .= $validUser->role_id == 1 ? '' : " AND sc.branchoffice = $validUser->branch_office_id ";
        if ($cliente !== null || $cliente !== '' || $cliente !== 'null') {
        $sql = "SELECT role_id
        FROM sys_users
        WHERE id = $cliente;";
        $currentRoles = $this->db->query($sql)->fetchAll();
        $roles = [];
        foreach ($currentRoles as $role) {
            array_push($roles, intval($role['role_id']));
        }
        }*/
        $y = date("Y");

        if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
        if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
        if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
        if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
        if ($cliente == 'TODOS') {} else if($cliente == ''){}else {$where .= " AND (scus.id = {$cliente} or  {$cliente} IS NULL)";}

        
        if ($DateOf == '') {
            $dateIni = date("Y-m-d H:i:s",strtotime('-30 day', strtotime($y."-01-01 00:00:00.000000")));
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
        }
        if ($DateUntil == '') {
            $dateFin = date("Y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
        }
        $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( wbo.name::text ILIKE '%".$filter."%' OR scus.name ILIKE '%".$filter."%' OR wc.name::text ILIKE '%".$filter."%' OR wl.name::text ILIKE '%".$filter."%' OR wm.name::text ILIKE '%".$filter."%' OR wp.name::text ILIKE '%".$filter."%' OR scus.name::text ILIKE '%".$filter."%')";
            // print_r($where);
            // exit();
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if($pagination['sortBy'] == 'sucursal'){
                $sortBy .= " ORDER BY ssc.branchoffice";
            }
            if($pagination['sortBy'] == 'marca'){
                $sortBy .= " ORDER BY wm.id";
            }
            if($pagination['sortBy'] == 'product'){
                $sortBy .= " ORDER BY wp.id" ;
            }
            if($pagination['sortBy'] == 'linea'){
                $sortBy .= " ORDER BY pwl.id" ;
            }
            if($pagination['sortBy'] == 'cliente'){
                $sortBy .= " ORDER BY scus.id" ;
            }
            if($pagination['sortBy'] != 'sucursal' && $pagination['sortBy'] != 'marca' && $pagination['sortBy'] != 'product' && $pagination['sortBy'] != 'linea' && $pagination['sortBy'] != 'cliente'){
                $sortBy .= " ORDER BY scus." . trim($pagination['sortBy']);
            }

        } else {
            $sortBy .= " ORDER BY scus.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sqla = "SELECT count(wp.id) AS count
        from wms_products as wp
        left join wms_marks as wm
        on wp.mark_id = wm.id
        left join wms_lines as wl
        on wp.line_id = wl.id
        left join wms_categories as wc
        on wl.category_id = wc.id
        left join sls_invoice_in_bulk_details as sibd 
        on wp.id = sibd.product_id
        left join sls_invoices as si 
        on si.id = sibd.invoice_id
        left join sys_users as su
        on su.id = si.seller_id
        left join sls_shopping_cart as ssc
        on ssc.id = si.shopping_cart_id
        left join sls_customers as scus
        on scus.id = ssc.customer_id
        left join wms_branch_offices as wbo
        on wbo.id = ssc.branchoffice
        left join sls_customer_branch_offices as scbo
        on scus.id = scbo.customer_id
        {$where} 
        group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
        ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id
        {$sortBy} {$desc} {$offset};
            ";

        // print_r($sqla);
        // exit();
        $productsCount = $this->db->query($sqla)->fetchAll();

        $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
        ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
        TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
        round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
        (round((sibd.qty*sibd.unit_price)::numeric,2) +
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
        CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
        su.nickname as vendedor,si.seller_id,si.id as factura
        from wms_products as wp
        left join wms_marks as wm
        on wp.mark_id = wm.id
        left join wms_lines as wl
        on wp.line_id = wl.id
        left join wms_categories as wc
        on wl.category_id = wc.id
        left join sls_invoice_in_bulk_details as sibd 
        on wp.id = sibd.product_id
        left join sls_invoices as si 
        on si.id = sibd.invoice_id
        left join sys_users as su
        on su.id = si.seller_id
        left join sls_shopping_cart as ssc
        on ssc.id = si.shopping_cart_id
        left join sls_customers as scus
        on scus.id = ssc.customer_id
        left join wms_branch_offices as wbo
        on wbo.id = ssc.branchoffice
        left join sls_customer_branch_offices as scbo
        on scus.id = scbo.customer_id
        {$where}  
        group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
        ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
        {$sortBy} {$desc} {$offset} {$limit}";
          print_r($sql);
          exit();
        $data = $this->db->query($sql)->fetchAll();
        
        /*foreach ($data as $key => $inv) {
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
        }*/
        $response = array('data' => $data, 'rowCounts' => count($productsCount));
        return $response;
    }


    public function getCsvpagbyclients ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$clienteID)
    {
        

        $content = $this->content;
        $where = 'where ssc.loan = 0  and si.status = '."'ENVIADO' ";
        $sucursal = "TODAS";
        $marca = "TODAS";
        $product = "TODOS";
        $linea = "TODAS";
        $cliente = "TODOS";
          if(!empty($sucursalID)){
            if ($sucursalID == 'TODAS') {
                $sucursal="NULL";
            }  else {
                $sucursal= $sucursalID;
            }
        }
        if(!empty($marcaID)){
            if ($marcaID == 'TODAS') {
               $marca='NULL';
            }  else {
                $marca= $marcaID;
            }
        }
        if(!empty($productID)){
            if ($productID == 'TODOS') {
                $product="NULL";
            }  else {
                $product= $productID;
            }
        }
        if(!empty($lineaID)){
            if ($lineaID == 'TODAS') {
                $linea="NULL";
            }  else {
                $linea= $lineaID;
            }
        }
        if(!empty($clienteID)){
            if ($clienteID == 'TODOS') {
                $cliente="NULL";
            }  else {
                $cliente= $clienteID;
            }
        }
        // var_dump($clienteID);
        // exit();
        $y = date("Y");

        if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
        if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
        if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
        if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
        if ($cliente == 'TODOS') {} else if($cliente == ''){}else {$where .= " AND (scus.id = {$cliente} or  {$cliente} IS NULL)";}

        
        if ($DateOf === 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
        }
        if ($DateUntil === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
        }
        $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
        ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
        TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
        round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
        (round((sibd.qty*sibd.unit_price)::numeric,2) +
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
        CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
        su.nickname as vendedor,si.seller_id,si.id as factura
        from wms_products as wp
        left join wms_marks as wm
        on wp.mark_id = wm.id
        left join wms_lines as wl
        on wp.line_id = wl.id
        left join wms_categories as wc
        on wl.category_id = wc.id
        left join sls_invoice_in_bulk_details as sibd 
        on wp.id = sibd.product_id
        left join sls_invoices as si 
        on si.id = sibd.invoice_id
        left join sys_users as su
        on su.id = si.seller_id
        left join sls_shopping_cart as ssc
        on ssc.id = si.shopping_cart_id
        left join sls_customers as scus
        on scus.id = ssc.customer_id
        left join wms_branch_offices as wbo
        on wbo.id = ssc.branchoffice
        left join sls_customer_branch_offices as scbo
        on scus.id = scbo.customer_id
        {$where}  
        group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
        ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
        ";
     //print_r($sql);
     // exit();
        $data = $this->db->query($sql)->fetchAll();


        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('REMISIÓN', 'VENTA', 'SUCURSAL', 'LINEA','MARCA', 'CLIENTE', 'PRODUCTO', 'CANTIDAD', 'IMPORTE', 'IVA', 'TOTAL'), ',');

        if (count($data) > 0) {
            $totalesqty = 0;
            $totalesprice = 0;
            $totalesiva = 0;
            $totales = 0;

            foreach ($data as $d) {
                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $d['factura'],
                        $d['sale_date'],
                        $d['sucursal'],
                        $d['line'],
                        $d['marca'],
                        $d['cliente'],
                        $d['producto'],
                        $d['qty'],
                        $d['qty_price'],
                        $d['qty_iva'],
                        $d['total']
                    ], ',');
                }
                $totalesqty += $d['qty'];
                $totalesprice += $d['qty_price'];
                $totalesiva += $d['qty_iva'];
                $totales += $d['total'];
            }
            fputcsv($fp, array('', '', '','','', '', '',$totalesqty,$totalesprice,$totalesiva,$totales));
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

    public function getCsvpagbySeller ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$sellerId, $clientId)
    {
        

        $content = $this->content;
        $where = 'where ssc.loan = 0  ';
        $sucursal = "TODAS";
        $marca = "TODAS";
        $product = "TODOS";
        $linea = "TODAS";
        $seller = "TODOS";
        $client = "TODOS";
          if(!empty($sucursalID)){
            if ($sucursalID == 'TODAS') {
                $sucursal="NULL";
            }  else {
                $sucursal= $sucursalID;
            }
        }
        if(!empty($marcaID)){
            if ($marcaID == 'TODAS') {
               $marca='NULL';
            }  else {
                $marca= $marcaID;
            }
        }
        if(!empty($productID)){
            if ($productID == 'TODOS') {
                $product="NULL";
            }  else {
                $product= $productID;
            }
        }
        if(!empty($lineaID)){
            if ($lineaID == 'TODAS') {
                $linea="NULL";
            }  else {
                $linea= $lineaID;
            }
        }
        if(!empty($sellerId)){
            if ($sellerId == 'TODOS') {
                $seller="NULL";
            }  else {
                $seller= $sellerId;
            }
        }
        if(!empty($clientId)){
            if ($clientId == 'TODOS') {
                $client="NULL";
            }  else {
                $client= $clientId;
            }
        }
        
        // var_dump($clienteID);
        // exit();
        $y = date("Y");

        if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
        if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
        if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
        if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
        if ($seller == 'TODOS') {} else if($seller == ''){}else {$where .= " AND (su.id = {$seller} or  {$seller} IS NULL)";}
        if ($client == 'TODOS') {} else if($client == ''){}else {$where .= " AND (scus.id = {$client} or  {$client} IS NULL)";}
        
        if ($DateOf === 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
        }
        if ($DateUntil === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
        }
        $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
        ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
        TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
        round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
        (round((sibd.qty*sibd.unit_price)::numeric,2) +
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
        CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
        su.nickname as vendedor,si.seller_id,si.id as factura
        from wms_products as wp
        left join wms_marks as wm
        on wp.mark_id = wm.id
        left join wms_lines as wl
        on wp.line_id = wl.id
        left join wms_categories as wc
        on wl.category_id = wc.id
        left join sls_invoice_in_bulk_details as sibd 
        on wp.id = sibd.product_id
        left join sls_invoices as si 
        on si.id = sibd.invoice_id
        left join sys_users as su
        on su.id = si.seller_id
        left join sls_shopping_cart as ssc
        on ssc.id = si.shopping_cart_id
        left join sls_customers as scus
        on scus.id = ssc.customer_id
        left join wms_branch_offices as wbo
        on wbo.id = ssc.branchoffice
        left join sls_customer_branch_offices as scbo
        on scus.id = scbo.customer_id
        {$where}  
        group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
        ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
        ";
     //print_r($sql);
     // exit();
        $data = $this->db->query($sql)->fetchAll();


        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($fp, array('REMISIÓN', 'VENTA', 'SUCURSAL', 'LINEA','MARCA', 'VENDEDOR', 'PRODUCTO', 'CANTIDAD', 'IMPORTE', 'IVA', 'TOTAL'), ',');

        if (count($data) > 0) {
            $totalesqty = 0;
            $totalesprice = 0;
            $totalesiva = 0;
            $totales = 0;

            foreach ($data as $d) {
                if ($d['id'] > 0) {
                    fputcsv($fp, [
                        $d['factura'],
                        $d['sale_date'],
                        $d['sucursal'],
                        $d['line'],
                        $d['marca'],
                        $d['vendedor'],
                        $d['producto'],
                        $d['qty'],
                        $d['qty_price'],
                        $d['qty_iva'],
                        $d['total']
                    ], ',');
                }
                $totalesqty += $d['qty'];
                $totalesprice += $d['qty_price'];
                $totalesiva += $d['qty_iva'];
                $totales += $d['total'];
            }
            fputcsv($fp, array('', '', '','','', '', '',$totalesqty,$totalesprice,$totalesiva,$totales));
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

    public function getPdfpagbySellers ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$sellerID,$clientID)
    {
        $pdf = $this->generatePdfSellers($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$sellerID, $clientID);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Ventas.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="ReporteDeVentas.pdf"');
            return $response;
        }


    }
    public function generatePdfSellers ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$sellerId, $clientID){
        $content = $this->content;
       $where = 'where ssc.loan = 0  ';
       $sucursal = "TODAS";
       $marca = "TODAS";
       $product = "TODOS";
       $linea = "TODAS";
       $seller = "TODOS";
       $client = "TODOS";
       $nameClient = "TODOS";
         if(!empty($sucursalID)){
           if ($sucursalID == 'TODAS') {
               $sucursal="NULL";
           }  else {
               $sucursal= $sucursalID;
           }
       }
       if(!empty($marcaID)){
           if ($marcaID == 'TODAS') {
              $marca='NULL';
           }  else {
               $marca= $marcaID;
           }
       }
       if(!empty($productID)){
           if ($productID == 'TODOS') {
               $product="NULL";
           }  else {
               $product= $productID;
           }
       }
       if(!empty($lineaID)){
           if ($lineaID == 'TODAS') {
               $linea="NULL";
           }  else {
               $linea= $lineaID;
           }
       }
       if(!empty($sellerId)){
           if ($sellerId == 'TODOS') {
               $seller="NULL";
           }  else {
               $seller= $sellerId;
           }
       }

       if(!empty($clientID) && $clientID != 'null'){
        if ($clientID == 'TODOS') {
            $client="NULL";
            $nameClient = "TODOS";
        }  else {
            $client= $clientID;
            $sqlName = "SELECT name FROM sls_customers WHERE id = $clientID";
            $queryName = $this->db->query($sqlName)->fetchAll();
            $nameClient = $queryName[0]["name"];
        }
    }
       // var_dump($clienteID);
       // exit();
       $y = date("Y");

       if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
       if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
       if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
       if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
       if ($seller == 'TODOS') {} else if($seller == ''){}else {$where .= " AND (su.id = {$seller} or  {$seller} IS NULL)";}
       if ($client == 'TODOS') {} else if($client == ''){}else {$where .= " AND (scus.id = {$client} or  {$client} IS NULL)";}
       
       
       if ($DateOf === 'null') {
           $dateIni = date("$y-01-01 00:00:00.000000");
       }else{
           $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
       }
       if ($DateUntil === 'null') {
           $dateFin = date("$y-12-31 00:00:00.000000");
       }else{
           $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
       }
       $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

       $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
       ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
       TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
       round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
       round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
       (round((sibd.qty*sibd.unit_price)::numeric,2) +
       round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
       CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
       su.nickname as vendedor,si.seller_id,si.id as factura
       from wms_products as wp
       left join wms_marks as wm
       on wp.mark_id = wm.id
       left join wms_lines as wl
       on wp.line_id = wl.id
       left join wms_categories as wc
       on wl.category_id = wc.id
       left join sls_invoice_in_bulk_details as sibd 
       on wp.id = sibd.product_id
       left join sls_invoices as si 
       on si.id = sibd.invoice_id
       left join sys_users as su
       on su.id = si.seller_id
       left join sls_shopping_cart as ssc
       on ssc.id = si.shopping_cart_id
       left join sls_customers as scus
       on scus.id = ssc.customer_id
       left join wms_branch_offices as wbo
       on wbo.id = ssc.branchoffice
       left join sls_customer_branch_offices as scbo
       on scus.id = scbo.customer_id
       {$where}  
       group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
       ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
       ";
    //print_r($sql);
    // exit();
       $data = $this->db->query($sql)->fetchAll();
       $pdf = new PDFSeller();
       $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
       $pdf->AliasNbPages();
       $pdf->AddPage('L', 'Letter');
       $pdf->SetLineWidth(.3);
       $pdf->encabezado();
       $pdf->SetTextColor(0);
       $pdf->SetXY(190, 25);
       $pdf->SetFont('Nunito-Regular', '', 10);
       /*if($DateOf != null && $DateOf != 'null' && $DateOf != ''){
           $pdf->Cell(0, 0, 'FECHA INICIO: '.$DateOf);
       }
       

       $pdf->SetXY(235, 25);
       $pdf->SetFont('Nunito-Regular', '', 10);
       if($DateUntil != null && $DateUntil != 'null' && $DateUntil != ''){
           $pdf->Cell(0, 0, 'FECHA FIN: '.$DateUntil);
       }*/
        $pdf->SetFont('Nunito-Regular', '', 8);
       $pdf->SetXY(60, 25);
       if($sucursal != 'null'){
           $sucursal = BranchOffices::findFirst(intval($sucursal));
           $pdf->Cell(0, 0, 'sucursal: '.$sucursal->name);
       }else {
           $pdf->Cell(0, 0, 'sucursal: TODAS');          
       }
       
       $pdf->SetXY(190, 25);
       if($seller !='null'){
           $seller = Users::findFirst(intval($seller));
           $pdf->Cell(0, 0, 'vendedor: '.$seller->nickname);
       }else {
               $pdf->Cell(0, 0, 'vendedor: TODOS');
       }
      

       $pdf->SetXY(60, 30);
       
       
       if($marca != 'null'){
           $marca = Marks::findFirst(intval($marca));
           $pdf->Cell(0, 0, 'marca: '.$marca->name);
       }else {
           $pdf->Cell(0, 0, 'marca: TODAS');
       }

       $pdf->SetXY(125, 30);
       if($linea != 'null'){
           $linea = Lines::findFirst(intval($linea));
           $pdf->Cell(0, 0, 'linea: '.$linea->name);
       }else {
           $pdf->Cell(0, 0, 'linea: TODAS');
       }
       $pdf->SetXY(190, 30);
       if($product != 'null') {
           $product = Products::findFirst(intval($product));
           $pdf->Cell(0, 0, 'producto: '.$product->name);

       } else {
           $pdf->Cell(0, 0, 'producto: TODOS');
 
       }
       $pdf->SetXY(60, 35);
       $pdf->Cell(0, 0, 'Cliente: '.$nameClient);

       $pdf->Ln();
       $pdf->SetFont('Nunito-Regular', '', 9);
       $pdf->SetTextColor(0);


       $pdf->SetXY(5, 45);
       $pdf->SetFont('', '', 7);

       $pdf->SetWidths(array(20,24,35,24,24,35,24,24,20,20,20));
       $pdf->SetAligns(array('C','L','L','L','L','L','L','L','R','R','R'));
       $pdf->SetDrawColor(0, 0, 0);

       $i = 1;
       $totalesqty = 0;
       $totalesprice = 0;
       $totalesiva = 0;
       $totales = 0;
       foreach ($data as $row) {
           if ($pdf->getY() >= $pdf->GetPageHeight() - 40) {
               $pdf->AddPage('L', 'Letter');
               $pdf->encabezado();
               $pdf->SetXY(0, 45);
               $pdf->SetFont('', '', 7);
           }
           $pdf->SetX(5);
           $pdf->SetTextColor(0,0,0);
           $pdf->SetDrawColor(0, 0, 0);
           $pdf->Row(array(
               $row['factura'],
               $row['sale_date'],
               $row['sucursal'],
               $row['line'],
               $row['marca'],
               $row['vendedor'],
               $row['producto'],
               $row['qty'],
               $row['qty_price'],
               $row['qty_iva'],
               $row['total']
               ));
           $i++;
           $totalesqty += $row['qty'];
           $totalesprice += $row['qty_price'];
           $totalesiva += $row['qty_iva'];
           $totales += $row['total'];
       }
       $pdf->SetXY(191, $pdf->getY());
       $aux=$pdf->getY();
       $pdf->SetDrawColor(0, 0, 0);
       $pdf->Cell(24, 5, $totalesqty,1,'','R');
       // $aux=$pdf->getY();
       $pdf->SetXY(215,$aux);
       $pdf->Cell(20, 5,'$'.number_format($totalesprice,2),1,'','R');
       $pdf->SetXY(235,$aux);
       $pdf->Cell(20, 5,'$'.number_format($totalesiva,2),1,'','R');
       $pdf->SetXY(255,$aux);
       $pdf->Cell(20, 5,'$'.number_format($totales,2),1,'','R');
       // $pdf->Cell(40, 5, '$'.number_format(floatval($totalesMonto), 2, '.', ','),1,'','R');


      $pdf->SetTitle(utf8_decode('Reporte de Pedidos'));
       header('Access-Control-Allow-Origin: *');
       header("Access-Control-Allow-Headers: *");
       $pdf->Output('I', 'reporte_ventas.pdf', true);

       return $pdf;
   }
    public function getPdfpagbyclients ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$clienteID)
    {
        $pdf = $this->generatePdf($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$clienteID);
        if (!is_null($pdf)) {
            $pdf->Output('I', "Reporte_de_Ventas.pdf", true);
            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="ReporteDeVentas.pdf"');
            return $response;
        }


    }

    public function generatePdf ($DateOf,$DateUntil,$sucursalID,$marcaID,$productID,$lineaID,$clienteID){
         $content = $this->content; 
        $where = 'where ssc.loan = 0 and si.status =  '."'ENVIADO' ";
        $sucursal = "TODAS";
        $marca = "TODAS";
        $product = "TODOS";
        $linea = "TODAS";
        $cliente = "TODOS";
          if(!empty($sucursalID)){
            if ($sucursalID == 'TODAS') {
                $sucursal="NULL";
            }  else {
                $sucursal= $sucursalID;
            }
        }
        if(!empty($marcaID)){
            if ($marcaID == 'TODAS') {
               $marca='NULL';
            }  else {
                $marca= $marcaID;
            }
        }
        if(!empty($productID)){
            if ($productID == 'TODOS') {
                $product="NULL";
            }  else {
                $product= $productID;
            }
        }
        if(!empty($lineaID)){
            if ($lineaID == 'TODAS') {
                $linea="NULL";
            }  else {
                $linea= $lineaID;
            }
        }
        if(!empty($clienteID)){
            if ($clienteID == 'TODOS') {
                $cliente="NULL";
            }  else {
                $cliente= $clienteID;
            }
        }
        // var_dump($clienteID);
        // exit();
        $y = date("Y");

        if ($sucursal == 'TODAS') {} else if($sucursal == ''){}else {$where .= " AND (ssc.branchoffice = {$sucursal} or  {$sucursal} IS NULL)";}
        if ($marca == 'TODAS') {} else if($marca == ''){}else {$where .= " AND (wm.id = {$marca} or  {$marca} IS NULL)";}
        if ($product == 'TODOS') {} else if($product == ''){}else {$where .= " AND (wp.id = {$product} or  {$product} IS NULL)";}
        if ($linea == 'TODAS') {} else if($linea == ''){}else {$where .= " AND (wl.id = {$linea} or  {$linea} IS NULL)";}
        if ($cliente == 'TODOS') {} else if($cliente == ''){}else {$where .= " AND (scus.id = {$cliente} or  {$cliente} IS NULL)";}

        
        if ($DateOf === 'null') {
            $dateIni = date("$y-01-01 00:00:00.000000");
        }else{
            $dateIni = date("Y-m-d H:i:s", strtotime($DateOf.' 00:00:00.000000'));
        }
        if ($DateUntil === 'null') {
            $dateFin = date("$y-12-31 00:00:00.000000");
        }else{
            $dateFin = date("Y-m-d H:i:s", strtotime($DateUntil.' 23:59:59.000000'));
        }
        $where .= " AND si.sale_date BETWEEN '".$dateIni."' AND '".$dateFin."'";

        $sql = "SELECT wbo.name as sucursal,scus.id as client_id,scus.name as cliente,ssc.id as venta, wp.id
        ,wc.name as categoria,wl.name as line,wm.name as marca,wp.name as producto,
        TO_CHAR(si.sale_date,'DD/MM/YYYY') as sale_date,ssc.branchoffice,sibd.qty,sibd.unit_price,round((((sibd.unit_price)*.16)::numeric),2)as unit_iva,
        round((sibd.qty*sibd.unit_price)::numeric,2) as qty_price,
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)as qty_iva,
        (round((sibd.qty*sibd.unit_price)::numeric,2) +
        round((((round((sibd.qty*sibd.unit_price)::numeric,2))*.16)::numeric),2)) as total,
        CONCAT(si.serie,'-',si.folio_fiscal) AS no_factura, si.id as factura,
        su.nickname as vendedor,si.seller_id,si.id as factura
        from wms_products as wp
        left join wms_marks as wm
        on wp.mark_id = wm.id
        left join wms_lines as wl
        on wp.line_id = wl.id
        left join wms_categories as wc
        on wl.category_id = wc.id
        left join sls_invoice_in_bulk_details as sibd 
        on wp.id = sibd.product_id
        left join sls_invoices as si 
        on si.id = sibd.invoice_id
        left join sys_users as su
        on su.id = si.seller_id
        left join sls_shopping_cart as ssc
        on ssc.id = si.shopping_cart_id
        left join sls_customers as scus
        on scus.id = ssc.customer_id
        left join wms_branch_offices as wbo
        on wbo.id = ssc.branchoffice
        left join sls_customer_branch_offices as scbo
        on scus.id = scbo.customer_id
        {$where}  
        group by ssc.id,wbo.name,scus.id,wp.id,wp.name,wm.name,wl.name,wc.name,si.sale_date,
        ssc.branchoffice,sibd.qty,sibd.unit_price,si.serie,si.folio_fiscal,si.id, su.nickname,si.seller_id,si.id
        ";
     //print_r($sql);
     // exit();
        $data = $this->db->query($sql)->fetchAll();

        $pdf = new PDFCliente();
        $pdf->AddFont('Nunito-Regular','','Nunito-Regular.php');
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'Letter');
        $pdf->SetLineWidth(.3);
        $pdf->encabezado();
        $pdf->SetTextColor(0);
        $pdf->SetXY(190, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        /*if($DateOf != null && $DateOf != 'null' && $DateOf != ''){
            $pdf->Cell(0, 0, 'FECHA INICIO: '.$DateOf);
        }
        

        $pdf->SetXY(235, 25);
        $pdf->SetFont('Nunito-Regular', '', 10);
        if($DateUntil != null && $DateUntil != 'null' && $DateUntil != ''){
            $pdf->Cell(0, 0, 'FECHA FIN: '.$DateUntil);
        }*/
         $pdf->SetFont('Nunito-Regular', '', 8);
        $pdf->SetXY(60, 25);
        if($sucursal != 'null'){
            $sucursal = BranchOffices::findFirst(intval($sucursal));
            $pdf->Cell(0, 0, 'sucursal: '.$sucursal->name);
        }else {
            $pdf->Cell(0, 0, 'sucursal: TODAS');          
        }
        
        $pdf->SetXY(160, 25);
        if($cliente !='null'){
            $cliente = Customers::findFirst(intval($cliente));
            $pdf->Cell(0, 0, 'cliente: '.$cliente->name);
        }else {
                $pdf->Cell(0, 0, 'cliente: TODOS');
        }
        $pdf->SetXY(60, 30);
        
        
        if($marca != 'null'){
            $marca = Marks::findFirst(intval($marca));
            $pdf->Cell(0, 0, 'marca: '.$marca->name);
        }else {
            $pdf->Cell(0, 0, 'marca: TODAS');
        }

        $pdf->SetXY(125, 30);
        if($linea != 'null'){
            $linea = Lines::findFirst(intval($linea));
            $pdf->Cell(0, 0, 'linea: '.$linea->name);
        }else {
            $pdf->Cell(0, 0, 'linea: TODAS');
        }
        $pdf->SetXY(190, 30);
        if($product != 'null') {
            $product = Products::findFirst(intval($product));
            $pdf->Cell(0, 0, 'producto: '.$product->name);

        } else {
            $pdf->Cell(0, 0, 'producto: TODOS');
  
        }
        
        $pdf->SetFont('Nunito-Regular', '', 9);
        $pdf->SetTextColor(0);


        $pdf->SetXY(5, 40);
        $pdf->SetFont('', '', 7);

        $pdf->SetWidths(array(20,24,35,24,24,35,24,24,20,20,20));
        $pdf->SetAligns(array('C','L','L','L','L','L','L','L','R','R','R'));
        $pdf->SetDrawColor(0, 0, 0);

        $i = 1;
        $totalesqty = 0;
        $totalesprice = 0;
        $totalesiva = 0;
        $totales = 0;
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
            $pdf->Row(array(
                $row['factura'],
                $row['sale_date'],
                $row['sucursal'],
                $row['line'],
                $row['marca'],
                $row['cliente'],
                $row['producto'],
                $row['qty'],
                $row['qty_price'],
                $row['qty_iva'],
                $row['total']
                ));
            $i++;
            $totalesqty += $row['qty'];
            $totalesprice += $row['qty_price'];
            $totalesiva += $row['qty_iva'];
            $totales += $row['total'];
        }
        $pdf->SetXY(191, $pdf->getY());
        $aux=$pdf->getY();
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Cell(24, 5, $totalesqty,1,'','R');
        // $aux=$pdf->getY();
        $pdf->SetXY(215,$aux);
        $pdf->Cell(20, 5,'$'.number_format($totalesprice,2),1,'','R');
        $pdf->SetXY(235,$aux);
        $pdf->Cell(20, 5,'$'.number_format($totalesiva,2),1,'','R');
        $pdf->SetXY(255,$aux);
        $pdf->Cell(20, 5,'$'.number_format($totales,2),1,'','R');
        // $pdf->Cell(40, 5, '$'.number_format(floatval($totalesMonto), 2, '.', ','),1,'','R');


       $pdf->SetTitle(utf8_decode('Reporte de Pedidos'));
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        $pdf->Output('I', 'reporte_ventas.pdf', true);

        return $pdf;
    }

}


class PDFSeller extends FPDF
{
    function encabezado()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,10,45,0,'PNG');
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
        $this->Cell(0, 0, 'REPORTE CLIENTES Y PRODUCTOS');

        $header = array(
            utf8_decode('REMISIÓN'), 'VENTA', 'SUCURSAL', 'LINEA','MARCA', 'VENDEDOR', 'PRODUCTO', 'CANTIDAD', 'IMPORTE', 'IVA', 'TOTAL'
        );
        $this->SetXY(5, 40);
        // 30,136,229
        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 10);
        // Header
        $x = 143;
        $i = 0;
        $w = array(20,24,35,24,24,35,24,24,20,20,20);
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


class PDFCliente extends FPDF
{
    function encabezado()
    {
        $this->SetFont('Nunito-Regular', '', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        if (file_exists($logo)) {
            $this->Image($logo,5,10,45,0,'PNG');
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
        $this->Cell(0, 0, 'REPORTE CLIENTES Y PRODUCTOS');

        $header = array(
            utf8_decode('REMISIÓN'), 'VENTA', 'SUCURSAL', 'LINEA','MARCA', 'CLIENTE', 'PRODUCTO', 'CANTIDAD', 'IMPORTE', 'IVA', 'TOTAL'
        );
        $this->SetXY(5, 35);
        // 30,136,229
        $this->SetFillColor(128,179,240);
        $this->SetTextColor(255,255,255);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.1);
        $this->SetFont('', '', 10);
        // Header
        $x = 143;
        $i = 0;
        $w = array(20,24,35,24,24,35,24,24,20,20,20);
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
