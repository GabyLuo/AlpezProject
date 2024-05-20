<?php

use Phalcon\Mvc\Controller;

class CustomersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAll ()
    {
        $sql = "SELECT s_c.*, w_bo.name as branch_name
                FROM sls_customers AS s_c
                INNER JOIN wms_branch_offices AS w_bo
                ON s_c.branch_id = w_bo.id
                ORDER BY s_c.id DESC";
        $customers = $this->db->query($sql)->fetchAll();

        $this->content['customers'] = $customers;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }
    public function getCustomers ()
    {
        if ($this->userHasPermission()) {
            $this->content['customers'] = Customers::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getCustomersByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request['branch'],$request);
            $this->content['customers'] = $response['data'];
            $this->content['customersCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getGridSQL ($branch,$request) {
        $where = 'WHERE c.id > 0 ';
        if ($branch == 'TODOS') {} else if($branch == ''){}else {$where .= " AND c.branch_id = $branch";}
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( c.serial::text ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR c.rfc ILIKE '%".$filter."%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY c." . trim($pagination['sortBy']);
        } else {
            $sortBy .= " ORDER BY c.id ";
        }
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];

        $sql = "SELECT count(c.id) AS count
                FROM sls_customers AS c
            {$where}";
         $customersCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT c.*,
                case when 
                (select id from sls_customer_tax_companies where customer_id = c.id limit 1) is null
                then 'No'
                else 'Si' end as yesornotdatafs
                FROM sls_customers AS c
                 {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $customersCount[0]['count']);
        return $response;
    }
    public function getCustomer ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['customer'] = Customers::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label FROM sls_customers WHERE active ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }
    public function getLastCode () {
        // $sql = "SELECT CONCAT('RE',(split_part(serial,'RE',2)::int) +1) as nextserial from sls_customers where serial like 'RE%' order by serial desc LIMIT 1";
        $sql = "SELECT MAX(serial) +1 as nextserial from sls_customers";
        $this->content['data'] = $this->db->query($sql)->fetch();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }
    public function getLastCodebyFileUpload () {
        // $sql = "SELECT CONCAT('RE',(split_part(serial,'RE',2)::int) +1) as nextserial from sls_customers where serial like 'RE%' order by serial desc LIMIT 1";
        $sql = "SELECT MAX(serial) +1 as nextserial from sls_customers";
        $data = $this->db->query($sql)->fetch();
        return $data;
    }
    public function getCustomersBySeller ($sellerId) {
        $options = [];
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
        // if (in_array(1,$roles) || in_array(3,$roles)) {
            $sql = "SELECT id AS value, name AS label FROM sls_customers WHERE active ORDER BY name ASC;";
        // } else {
        //     $sql = "SELECT id AS value, name AS label FROM sls_customers WHERE active and seller_id = $sellerId ORDER BY name ASC;";
        // }
        $options = $this->db->query($sql)->fetchAll();
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptionsOffices () {
        $request = $this->request->getPost();
        $customer = intval($request['customer']);
        $sql = "SELECT id AS value, name AS label FROM sls_customer_branch_offices as scbo WHERE scbo.customer_id = {$customer} ORDER BY scbo.name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getDataClient () {
        $request = $this->request->getPost();
        $customer = intval($request['id']);
        // $branch = intval($request['branch']);
        // echo("<pre>");
        // print_r($request);
        /// exit();
        if(is_array($request['branch'])){
            $branch = $request['branch']['value'];
        }else {
            $branch = $request['branch'];
        }
        $sql = "SELECT  scbo.customer_id as id,scbo.id as branch,scbo.name as branch_name,scbo.street as branch_street,scbo.outdoor_number as branch_outdoor_number,scbo.int_number as branch_int_number,scbo.colony as branch_colony,scbo.municipality as branch_municipality,scbo.zip_code as branch_zip_code,scbo.phone_number as branch_phone_number,
		sc.email,sc.name as name_client, concat(sc.street, ' ', sc.outdoor_number,' ',sc.indoor_number,' ',sc.suburb,' ',sc.municipality,' ',sc.state) as address,sc.contact_phone, sc.term
                FROM sls_customer_branch_offices as scbo 
				LEFT JOIN sls_customers AS sc ON sc.id = scbo.customer_id
				WHERE scbo.customer_id = {$customer} AND scbo.id={$branch};";
        // var_dump($sql);
        // exit();
        $this->content['data'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);

    }
    public function getDataClientBranch () {
        $request = $this->request->getPost();
        $customer = intval($request['id']);
        $sql = "SELECT  id,name,street,outdoor_number,int_number,colony,municipality,zip_code,phone_number
                FROM sls_customer_branch_offices as sc WHERE sc.id = {$customer};";
        $this->content['data'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);

    }
    public function getCsvCustomers ($branchOfficeId) {
        $content = $this->content;
        $where = " WHERE c.active = true";
        $sql ="SELECT c.serial, c.active as status, c.name as customer_name,c.contact_name, c.tradename, c.contact_phone as phone,c.contact_phone_res as phone2, c.street, c.suburb,c.municipality,c.state,c.city,c.country,c.zip_code,c.indoor_number,c.outdoor_number,c.rfc,c.email,c.email2,c.payment_method,acc_currency_types.name as currency,c.price_list,c.term,c.credit_days,c.credit_limit,u.nickname as seller,ch.name as channel,c.admission_date as date,c.requirements,c.documents    
                FROM sls_customers AS c
                LEFT JOIN sys_users as u ON c.seller_id = u.id
                LEFT JOIN wms_channels as ch ON c.channel_id = ch.id
                INNER JOIN acc_currency_types ON acc_currency_types.id = c.currency
                {$where}
                ORDER BY c.serial";
        $customers = $this->db->query($sql)->fetchAll();
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, [utf8_decode('CÓDIGO'),'FECHA ALTA',utf8_decode('RAZÓN SOCIAL'),'NOMBRE DE CONTACTO','RFC','EMAIL','EMAIL 2','TELEFONO','TELEFONO 2',utf8_decode('DÍAS CREDITO'),'LIMITE DE CREDITO','FORMA DE PAGO','MONEDA','PRECIO DE LISTA','VENDEDOR','NOMBRE COMERCIAL',utf8_decode('PAÍS'),'ESTADO','CIUDAD','MUNICIPIO','COLONIA','CALLE','N.Exterior','N.Interior',utf8_decode('CÓDIGO POSTAL'),'PLAZO','ESTATUS','CANAL','REQUERIMIENTOS ESPECIALES','DOCUMENTOS REQUERIDOS',], ',');
        if (count($customers)) {
            foreach ($customers as $sp) {
                fputcsv($fp, [
                    $sp['serial'],
                    ($sp['date']),
                    utf8_decode($sp['customer_name']),
                    utf8_decode($sp['contact_name']),
                    ($sp['rfc']),
                    ($sp['email']),
                    ($sp['email2']),
                    $sp['phone'],
                    $sp['phone2'],
                    ($sp['credit_days']),
                    ($sp['credit_limit']),
                    ($sp['payment_method']),
                    ($sp['currency']),
                    ($sp['price_list']),
                    utf8_decode($sp['seller']),
                    utf8_decode($sp['tradename']),
                    utf8_decode($sp['country']),
                    utf8_decode($sp['state']),
                    utf8_decode($sp['city']),
                    utf8_decode($sp['municipality']),
                    utf8_decode($sp['suburb']),
                    utf8_decode($sp['street']),
                    utf8_decode($sp['outdoor_number']),
                    utf8_decode($sp['indoor_number']),
                    ($sp['zip_code']),
                    ($sp['term']),
                    ($sp['status'] == 1 ? 'ACTIVO' : ($sp['status'] == 2 ? 'INACTIVO' : 'OTRO')),
                    utf8_decode($sp['channel']),
                    utf8_decode($sp['requirements']),
                    utf8_decode($sp['documents']),
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
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Clientes-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }
    
    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                    $customer = new Customers();
                    $customer->setTransaction($tx);
                    $customer->name = strtoupper($request['name']);
                    //$customer->serial = strtoupper($request['serial']);
                    $customer->serial = strtoupper($request['serial']);
                    $customer->contact_name = strtoupper($request['contact_name']);
                    if (isset($request['contact_phone']) && is_numeric($request['contact_phone'])) {
                        $customer->contact_phone = intval($request['contact_phone']);
                    }
                    if (isset($request['discount']) && is_numeric($request['discount'])) {
                        $customer->discount = intval($request['discount']);
                    }
                    if (isset($request['contact_phone_res']) && is_numeric($request['contact_phone_res'])) {
                        $customer->contact_phone_res = intval($request['contact_phone_res']);
                    }
                    $customer->tradename = strtoupper($request['tradename']);
                    $customer->street = strtoupper($request['street']);
                    $customer->outdoor_number = strtoupper($request['outdoor_number']);
                    $customer->indoor_number = strtoupper($request['indoor_number']);
                    $customer->admission_date = strtoupper($request['admission_date']);
                    // $customer->postal_code_id = strtoupper($request['postal_code_id']);
                    // $customer->between_street = strtoupper($request['between_street']);
                    // $customer->suburb_id = $request['suburb_id'] == 0? null : $request['suburb_id'];
                    $customer->suburb = $request['suburb'];
                    $customer->municipality = strtoupper($request['municipality']);
                    $customer->country = strtoupper($request['country']);
                    $customer->state = strtoupper($request['state']);
                    $customer->city= strtoupper($request['city']);
                    if (isset($request['zip_code']) && is_numeric($request['zip_code'])) {
                        $customer->zip_code = intval($request['zip_code']);
                    }


                    $customer->rfc = strtoupper($request['rfc']);
                    $customer->term = strtoupper($request['term']);
                    $customer->payment_method = strtoupper($request['payment_method']);
                    $customer->currency = ($request['currency']);
                    $customer->active = $request['active'];
                    $customer->account_id = $actualAccount;
                    $customer->price_list = strtoupper($request['price_list']);
                    $customer->email = strtolower($request['email']);
                    $customer->email2 = strtolower($request['email2']);
                    $customer->email3 = strtolower($request['email3']);
                    $customer->email4 = strtolower($request['email4']);
                    $customer->branch_id = intval($request['branch_id']);
                    $customer->credit_days = $request['credit_days'] ? ($request['credit_days']) : null;
                    $customer->credit_limit = $request['credit_limit'] ? ($request['credit_limit']) : null;
                    $customer->seller_id = intval($request['seller']);
                    $customer->channel_id = $request['channel_id'] ? ($request['channel_id']) : null;
                    $customer->requirements = strtoupper($request['requirements']);
                    $customer->documents = strtoupper($request['documents']);

                    if ($customer->create()) {
                        $this->content['result'] = true;
                        $this->content['id'] = $customer->id;
                        $this->content['name'] = $customer->name;
                        $this->content['message'] = Message::success('El cliente ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customer);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el cliente.');
                        $tx->rollback();
                    }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function updateRequirement ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customer = Customers::findFirst($id);

                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                if ($customer) {
                    $customer->setTransaction($tx);
                    $customer->requirements = strtoupper($request['requirements']);
                    $customer->documents = strtoupper($request['documents']);
                    $customer->account_id = $actualAccount;
                    if ($customer->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El cliente ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customer);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el cliente.');
                        $tx->rollback();
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customer = Customers::findFirst($id);

                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($customer) {
                    $customer->setTransaction($tx);
                    $customer->name = strtoupper($request['name']);
                    $customer->serial = strtoupper($request['serial']);
                    $customer->contact_name = strtoupper($request['contact_name']);
                    if (isset($request['contact_phone']) && is_numeric($request['contact_phone'])) {
                        $customer->contact_phone = intval($request['contact_phone']);
                    }
                    if (isset($request['discount']) && is_numeric($request['discount'])) {
                        $customer->discount = intval($request['discount']);
                    }
                    if (isset($request['contact_phone_res']) && is_numeric($request['contact_phone_res'])) {
                        $customer->contact_phone_res = intval($request['contact_phone_res']);
                    }
                    $customer->tradename = strtoupper($request['tradename']);
                    $customer->street = strtoupper($request['street']);
                    $customer->outdoor_number = strtoupper($request['outdoor_number']);
                    $customer->indoor_number = strtoupper($request['indoor_number']);
                    $customer->admission_date = strtoupper($request['admission_date']);
                    $customer->city = strtoupper($request['city']);
                    // $customer->postal_code_id = strtoupper($request['postal_code_id']);
                    // $customer->between_street = strtoupper($request['between_street']);
                    // $customer->suburb_id = $request['suburb_id'] == 0? null : $request['suburb_id'];
                    $customer->suburb = $request['suburb'];
                    $customer->municipality = strtoupper($request['municipality']);
                    $customer->country = strtoupper($request['country']);
                    $customer->state = strtoupper($request['state']);
                    if (isset($request['zip_code']) && is_numeric($request['zip_code'])) {
                        $customer->zip_code = intval($request['zip_code']);
                    }
                    
                    $customer->seller_id =$request['seller_id'] ? (intval($request['seller_id'])) : null;
                    $customer->rfc = strtoupper($request['rfc']);
                    $customer->term = strtoupper($request['term']);
                    $customer->payment_method = strtoupper($request['payment_method']);
                    $customer->currency = strtoupper($request['currency']);
                    $customer->active = $request['active'];
                    $customer->account_id = $actualAccount;
                    $customer->price_list = ($request['price_list']);
                    $customer->email = strtolower($request['email']);
                    $customer->email2 = strtolower($request['email2']);
                    $customer->email3 = strtolower($request['email3']);
                    $customer->email4 = strtolower($request['email4']);
                    $customer->branch_id = $request['branch_id'];
                    $customer->credit_days = $request['credit_days'] ? ($request['credit_days']) : null;
                    $customer->credit_limit = $request['credit_limit'] ? ($request['credit_limit']) : null;
                    $customer->channel_id = $request['channel_id'] ? (intval($request['channel_id'])) : null;

                    if ($customer->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El cliente ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customer);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el cliente.');
                        $tx->rollback();
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

    /* public function uploadFile ()
    {
        //  
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
        
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                                $this->content['array'] = $csvData;
                                // $it = [];
                                foreach ($csvData as  $key => $k){
                                    #We'll verify that the customer exists with the RFC
                                    
                                    $rfc = strtoupper($k[5]);
                                    $customerExists = Customers::findFirst("rfc = '".$rfc."'");
                                    # We to verify that the nickname exists in DB
                                    $user  = Users::findFirst("nickname = '".$k[12]."'");
                                    if (!$user) {
                                        $this->content['errors'][] = array('message' => Message::error('Linea '.($key + 1).', no se ha encontrado un Vendedor con ese nombre'));
                                        continue;
                                    }
                                    if (strtoupper($k[10]) != "A" && strtoupper($k[10]) != 'B' && strtoupper($k[10]) != 'C' && strtoupper($k[10]) != 'D' && strtoupper($k[10]) != 'E') {
                                        $this->content['errors'][] = array('message' => Message::error('Linea '.($key + 1).', Precio de Lista Inválido, favor elija un valor entre A, B, C, D, E'));
                                        continue;
                                    } 
                                    if (strtoupper($k[13]) != "CONTADO" && strtoupper($k[13]) != "CREDITO") {
                                        $this->content['errors'][] = array('message' => Message::error('Linea '.($key + 1).', Forma de pago inválida, favor escriba CREDITO o CONTADO'));
                                        continue;
                                    } 
                                    if (!is_numeric(($k[11])) && $k[11] != null) {
                                        $this->content['errors'][] = array('message' => Message::error('Linea '.($key + 1).', El descuento debe ser númerico'));
                                        continue;
                                    } else {
                                        if (!$customerExists) {
                                            $customer = new Customers();
                                        } else {
                                            $customer = Customers::findFirst(intval($customerExists->id));
                                        }
                                        $customer->setTransaction($tx);
                                        $customer->name = strtoupper($k[0]);
                                        // $customer->serial = 0;
                                        $customer->contact_name = strtoupper($k[1]);
                                        $customer->contact_phone = ($k[7]);
                                        $customer->discount = floatval($k[11]);
                                        $customer->contact_phone_res = ($k[8]);
                                        $customer->tradename = strtoupper($k[6]);
                                        $customer->street = strtoupper($k[17]);
                                        $customer->outdoor_number = strtoupper($k[19]);
                                        $customer->indoor_number = strtoupper($k[18]);
                                        $customer->suburb = strtoupper($k[22]);
                                        $customer->admission_date = date('Y-m-d');
                                        $customer->municipality = strtoupper($k[21]);
                                        $customer->country = strtoupper($k[25]);
                                        $customer->state = strtoupper($k[24]);
                                        $customer->city= strtoupper($k[20]);
                                        $customer->zip_code = strtoupper($k[23]);
                                        $customer->rfc = strtoupper($k[3]);
                                        $customer->term = strtoupper($k[13]);
                                        $customer->payment_method = null;
                                        $customer->currency = strtoupper($k[9]);
                                        $customer->active = strtoupper($k[2]) == 'ACTIVO' ? true : false;
                                        $customer->account_id = $actualAccount;
                                        $customer->price_list = strtoupper($k[10]);
                                        $customer->email = strtolower($k[4]);
                                        $customer->email2 = strtolower($k[5]);
                                        $customer->branch_id = intval(5);
                                        $customer->credit_days = intval($k[14]);
                                        $customer->credit_limit = floatval($k[15]);
                                        $customer->seller_id = $user->id;//$k[12];
                                        $customer->channel_id = null;//$k[16];
                                        $customer->requirements = strtoupper($k[26]);
                                        $customer->documents = strtoupper($k[27]);
                                        if ($customerExists) {
                                            #Make an update
                                            $customer->update();
                                        } else {
                                            #Create new customer
                                            $customer->create();
                                        }
                                    }
                                }
                            // fclose($handle);
                        $tx->commit();
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Los clientes han sido subido exitosamente.');
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
    } */

    public function uploadFileCustomers ()
    {
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
                        foreach($csvData as $key => $valuekey){
                            sleep(1);
                            $rfc = strtoupper($valuekey[4]);
                            $userseller_id = 0;
                            $customerExists = Customers::findFirst("rfc = '".$rfc."'");
                            $user  = Users::findFirst("nickname = '".$valuekey[14]."'");
                            
                            
                            $date = "";
                            $currency = "";
                            $channel = "";
                            
                            $type_chanel = "";

                            if (strlen($valuekey[12])) {
                                $type_currency = Currencies::findFirst("name = '".$valuekey[12]."'");
                                $currency = $type_currency->id;
                            } else {
                                $currency = null;
                            }
                            /*if (strlen($valuekey[27])) {
                                $type_chanel = Channels::findFirst("name = '".$valuekey[27]."'");
                                $channel = $type_chanel->id;
                            } else {
                                $channel = null;
                            }*/
                            
                            if (!$user) {
                                $this->content['errors'][] = array('message' => Message::error('Linea '.($key + 1).', no se ha encontrado un Vendedor '.$valuekey[14].' con ese nombre'));
                                continue;
                            }
                            if (!$customerExists) {
                                $customer = new Customers();
                            } else {
                                $customer = Customers::findFirst(intval($customerExists->id));
                            }
                            if (strlen($valuekey[1])) {
                                $date = $valuekey[1];
                            } else {
                                $date = null;
                            }

                            
                            $customer->setTransaction($tx);
                            // $customer->serial = 0;
                            $customer->admission_date = $date;
                            $customer->name = strtoupper($valuekey[2]);
                            $customer->contact_name = strtoupper($valuekey[3]);
                            $customer->rfc = strtoupper($valuekey[4]);
                            $customer->email = strtolower($valuekey[5]);
                            $customer->email2 = strtolower($valuekey[6]);
                            $customer->contact_phone = ($valuekey[7]);
                            $customer->contact_phone_res = ($valuekey[8]);
                            $customer->credit_days = intval($valuekey[9]);
                            $customer->credit_limit = floatval($valuekey[10]);
                            $customer->payment_method = strtoupper($valuekey[11]);
                            $customer->currency = $currency;
                            $customer->price_list = strtoupper($valuekey[13]);
                            $customer->seller_id = $user->id;//$k[12];
                            $customer->tradename = strtoupper($valuekey[15]);
                            $customer->country = strtoupper($valuekey[16]);
                            $customer->state = strtoupper($valuekey[17]);
                            $customer->city= strtoupper($valuekey[18]);
                            $customer->municipality = strtoupper($valuekey[19]);
                            $customer->suburb = strtoupper($valuekey[20]);
                            $customer->street = strtoupper($valuekey[21]);
                            $customer->outdoor_number = strtoupper($valuekey[22]);
                            $customer->indoor_number = strtoupper($valuekey[23]);
                            $customer->zip_code = strtoupper($valuekey[24]);
                            $customer->term = strtoupper($valuekey[25]);
                            $customer->active = strtoupper($valuekey[26]) == 'ACTIVO' ? true : false;
                            //$customer->channel_id = $channel;//$k[16];
                            //$customer->requirements = strtoupper($valuekey[28]);
                            //$customer->documents = strtoupper($valuekey[29]);
                            $customer->account_id = $actualAccount;
                            //$customer->branch_id = intval(9);
                            //$customer->discount = floatval($k[11]);

                            if ($customerExists) {
                            

                                #Make an update
                                $customer->update();

                            } else {
                                #Create new customer
                                $ser=$this->getLastCodebyFileUpload();
                                if($ser){
                                $customer->serial = $ser['nextserial'];
                                }
                                $customer->create();
                            }

                            //array_push($mdata, array('rfc' => $valuekey[4], 'vendedor' => $valuekey[14]));
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

    

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customer = Customers::findFirst($id);

                if ($customer) {
                    $customer->setTransaction($tx);

                    if ($customer->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El cliente ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customer);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el cliente.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El cliente no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function changePhoto ()
    {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $customers = Customers::findFirst($request['id']);
                if ($customers) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/customers/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $this->content['fileExtension'] = $file->getExtension();
                        $fileName = $request['id'] . '.' . $file->getExtension();
                        $fullPath = $upload_dir . $fileName;
                        $this->content['fullPath'] = $fullPath;
                        if ($customers->photo != null && file_exists($upload_dir.$customers->photo)) {
                            @unlink($upload_dir.$customers->photo);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $customers->setTransaction($tx);
                        $customers->photo = $fileName;
                        if ($customers->update()) {
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
                    $this->content['message'] = Message::success('No se ha encontrado el cliente.');
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
                    WHERE ( role_id = 1 OR role_id = 4 OR role_id = 27 OR role_id = 3 OR role_id = 20  OR role_id = 17 OR role_id = 22 OR role_id = 28 OR role_id = 29 OR role_id = 2)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    public function uploadFile ()
    {
        //  
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                //print_r($request);
                
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/clientes/update/';
                // print_r($upload_dir);
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777, true);
                }
                // exit();
                $fullPath = '';
              
                foreach ($this->request->getUploadedFiles() as $file) {
                    $fileName = $file->getName();
                    $fullPath = $upload_dir . $fileName;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    if ($file->moveTo($fullPath)) {
                        // $tx->commit();

                        $csvData = array();
                        if (($handle = fopen($fullPath, 'r')) !== FALSE) { // Check the resource is valid
                           while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                             
                                
                                    $csvData[] = $data;
                                
                            }
                           
                               
                                 
                                
                         //   }
                            
                            
                            
                        }

                        fclose($handle);
                      
                        foreach ($csvData as $k){
                        //echo "<pre>";
                        //print_r($k);
                        
                                    /*if (!empty($k[2]) ) {
                                        
                                      
                                        $porciones = explode('-',$k[0]);
                                        $numero=$porciones[0];
                                        $materia=$porciones[1];
                                        $ejercicio=$porciones[2];
                                        $folio=$porciones[3];
                     
                                        $sql="SELECT j.id,d.numero,m.clave,j.ejercicio,j.folio from juz_promociones as j 
                                        left join cjj_distritos as d  on j.distrito_id = d.id
                                        left join cjj_partidos  as p  on j.partido_id = p.id
                                        left join leg_materias as m  on j.materia_id = m.id
                                        left join cjj_jueces    as jc on j.juez_id = jc.id
                                        left join cjj_juzgados  as jz on j.juzgado_id = jz.id
                                        where d.numero =$numero and m.clave='$materia' and j.ejercicio =$ejercicio and j.folio =$folio 
                                        order by j.id desc";
                                        $info = $this->db->query($sql)->fetch();
                                        //print_r($info);
                                        //exit();
                                        if($info){
                                        $sqlj = "SELECT  * from cjj_juzgados";
                                        $ju = $this->db->query($sqlj)->fetchAll();
                                        for ($ij=0; $ij <count($ju) ; $ij++) {
                                            $porciones1 = explode(' ',$ju[$ij]['nombre']);
                                   
                                            if(intval($porciones1[0]) == intval($k[2])){
                                       
                                                $promocion = Promociones::findFirst($info['id']);
                                                $tx = $this->transactions->get();   
                                                $promocion->setTransaction($tx);
                                                $promocion->juzgado_id =  $ju[$ij]['id'];
                                                if ($promocion->update() !== false) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('juzgados actualizados correctamente.');
                    
                                                }
                                            }
                                  
                                        }
                                    }
                                        
                                
                                    }*/
                                 }
                         
                            echo("<pre>");
                            print_r(count($csvData));
                        
                exit();
                        
                        // exit();
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
}
