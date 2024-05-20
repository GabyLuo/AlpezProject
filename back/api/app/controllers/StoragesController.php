<?php

use Phalcon\Mvc\Controller;

class StoragesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getStorages ()
    {   
        if ($this->userHasPermission()) {
            $this->content['storages'] = Storages::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getStoragesWithAccountName ($pt = 0)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT s.*, a.name AS account, bo.name AS branch_office, st.name AS storage_type
                    FROM wms_storages AS s
                    INNER JOIN sys_accounts AS a
                    ON s.account_id = a.id
                    INNER JOIN wms_branch_offices AS bo
                    ON s.branch_office_id = bo.id
                    INNER JOIN wms_storage_types AS st
                    ON s.storage_type_id = st.id";

            $data = $this->db->query($sql);
           // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $content['storages'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getStorage ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $sql = "SELECT s.id, s.account_id, s.code, s.name, s.branch_office_id, bo.name AS branch_office, s.storage_type_id, st.name AS storage_type,
                s.zip_code as zip, s.street, s.suburb, s.city
                        FROM wms_storages AS s
                        INNER JOIN wms_branch_offices AS bo
                        ON s.branch_office_id = bo.id
                        INNER JOIN wms_storage_types AS st
                        ON s.storage_type_id = st.id
                        WHERE s.id = $id";

                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $this->content['storage'] = $data->fetch();
                $this->content['result'] = true;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getStoragesByBranchOfficeId ($branchOfficeId)
    {
        $this->content['storages'] = Storages::find("branch_office_id = $branchOfficeId");
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        if ($validUser->id == 77 || $validUser->id == 107) {//solo 2 usuarios van a poder hacer transferencias entre sus sucursales
            $where .= " where branch_office_id = 13 or branch_office_id = 14";
        }else{
            if ($validUser->role_id == 26) {
                $where = "INNER JOIN wms_branch_offices on wms_branch_offices.id = wms_storages.branch_office_id
                          INNER JOIN sys_supercluster on sys_supercluster.id = wms_branch_offices.cluster_id and sys_supercluster.id = " . $validUser->cluster_id;
            } else {
                $where = $validUser->role_id == 1 ? '' : " where branch_office_id = $validUser->branch_office_id ";
            }
        }
        
        $sql = "SELECT wms_storages.id, wms_storages.name, wms_storages.branch_office_id, wms_storages.storage_type_id FROM wms_storages $where ORDER BY wms_storages.name ASC;";
        $types = $this->db->query($sql)->fetchAll();
        
        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name'],
                'branchOffice' => $type['branch_office_id'],
                'storageType' => $type['storage_type_id']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getBagsByStorageId ($id) {
        if (is_numeric($id)) {
            $bags = array();
            $sql = "";
            if ($id == 2) {
                $sql = "SELECT sd.id AS value, sd.product_id, sd.qty, sd.product_shipment_number, m.date, CONCAT('Saco ', sd.id, ' (', sd.qty, ' Kg.) [', s.receive_date, ']') AS label
                        FROM pur_shipment_details AS sd
                        INNER JOIN pur_shipments AS s
                        ON sd.shipment_id = s.id
                        INNER JOIN wms_movements AS m
                        ON m.id = s.movement_id
                        WHERE m.status = 1
                        AND m.storage_id = 2
                        AND m.type = 1;";
            } else {
                $sql = "SELECT sd.id AS value, sd.product_id, sd.qty, sd.product_shipment_number, m.date, CONCAT('Saco ', sd.id, ' (', sd.qty, ' Kg.) [', s.receive_date, ']') AS label
                        FROM wms_movement_details AS md
                        INNER JOIN pur_shipment_details AS sd
                        ON sd.id = md.bag_id
                        INNER JOIN pur_shipments AS s
                        ON sd.shipment_id = s.id
                        INNER JOIN wms_movements AS m
                        ON m.id = s.movement_id
                        WHERE m.status = 1
                        AND m.storage_id = $id
                        AND m.type = 1;";
            }
            $totalBags = $this->db->query($sql)->fetchAll();
            foreach ($totalBags as $bag) {
                $sql = "SELECT md.id AS id, md.product_id, md.bag_id, md.qty, m.date
                        FROM wms_movement_details AS md
                        INNER JOIN wms_movements AS m
                        ON m.id = md.movement_id
                        WHERE m.status = 1
                        AND m.type = 2
                        AND m.storage_id = $id
                        AND md.bag_id = ".$bag['value']."
                        ORDER BY m.date DESC
                        LIMIT 1;";
                $lastBagExit = $this->db->query($sql)->fetch();
                if ($lastBagExit) {
                    $sql = "SELECT md.id AS id, md.product_id, md.bag_id, md.qty, m.date
                            FROM wms_movement_details AS md
                            INNER JOIN wms_movements AS m
                            ON m.id = md.movement_id
                            WHERE m.status = 1
                            AND m.type = 1
                            AND m.storage_id = $id
                            AND md.bag_id = ".$bag['value']."
                            AND m.date >= '".$lastBagExit['date']."'
                            ORDER BY m.date DESC
                            LIMIT 1;";
                    $lastBagEntry = $this->db->query($sql)->fetch();
                    if ($lastBagEntry['qty'] > 0) {
                        $bag['qty'] = $lastBagEntry['qty'];
                        $bag['label'] = 'Saco '. $bag['value'].' ('.$lastBagEntry['qty'].' Kg.) ['.$bag['date'].']';
                        array_push($bags, $bag);
                    }
                } else {
                    array_push($bags, $bag);
                }
            }
            usort($bags, function($a, $b) {
                if ($a['qty'] == $b['qty'])
                    return (0);
                return (($a['qty'] < $b['qty']) ? -1 : 1);
            });
            $this->content['bags'] = $bags;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de almacén válido.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function calculateBalesByStorageId ($id) {
        $availableBales = [];
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
            if ($storage && $storage->id && $storage->storage_type_id == 1) {
                $bales = [];
                $movements = new MovementsController();
                $baleMovements = $movements->generateKardex(null, null, null, $storage->id, 6, null, null, null, null);
                foreach ($baleMovements as $movement) {
                    if (!in_array($movement['bale_id'], $bales)) {
                        $baleStock = 0;
                        foreach ($baleMovements as $secondMovement) {
                            if ($movement['bale_id'] == $secondMovement['bale_id']) {
                                if ($secondMovement['movement_type'] == 1) {
                                    $baleStock += $secondMovement['qty'];
                                } elseif ($secondMovement['movement_type'] == 2) {
                                    $baleStock -= $secondMovement['qty'];
                                }
                            }
                        }
                        if ($baleStock > 0) {
                            array_push($availableBales, array('bale_id' => $movement['bale_id'], 'category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'stock' => $baleStock));
                        }
                        array_push($bales, $movement['bale_id']);
                    }
                }
            }
        }
        return $availableBales;
    }

    public function getBalesByStorageId ($id) {
        set_time_limit(0);
        if (is_numeric($id)) {
            $sql = "SELECT s2.bale_id, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, s2.product_id, p.code AS product_code, p.name AS product_name, s2.stock, concat (c.code,' ',l.code,' ',p.name) as pname
                    FROM (SELECT s1.bale_id, s1.product_id, SUM(s1.qty) AS stock
                          FROM (SELECT md.bale_id, md.product_id, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                FROM wms_movement_details AS md
                                INNER JOIN wms_movements AS m
                                ON m.id = md.movement_id
                                WHERE m.status = 1
                                AND md.bale_id IS NOT NULL
                                AND m.storage_id = $id
                                ORDER BY m.date ASC) AS s1
                          GROUP BY s1.bale_id, s1.product_id) AS s2
                    INNER JOIN wms_products AS p
                    ON p.id = s2.product_id
                    INNER JOIN wms_lines AS l
                    ON l.id = p.line_id
                    INNER JOIN wms_categories AS c
                    ON c.id = l.category_id
                    WHERE s2.stock > 0
                    AND l.category_id = 6
                    ORDER BY s2.bale_id ASC;";

            $data = $this->db->query($sql);
            //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $bales = $data->fetchAll();
            $this->content['bales'] = $bales;
            $this->content['result'] = true;
        }
        $this->response->setJsonContent($this->content);
    }

    public function  getStoragesbyShoppingCart ($id) {
        $oSC = ShoppingCart::find("id = $id");
        $office = $oSC->toArray();
        $oId = $office[0]['branchoffice'];
        $array = [];

        //var_dump($oId);

        $sSC = Storages::find("branch_office_id = $oId AND (storage_type_id = 16 or storage_type_id = 15 or storage_type_id = 17 or storage_type_id = 18) ORDER BY storage_type_id ASC");
        $storage = $sSC->toArray();

        $storage_bulk = $storage[0]['id'];
        array_push($array,$storage[0]['id']);
        //var_dump($array);
        $this->response->setJsonContent($array);
    }

    public function getFiberProductsByStorageId ($id) {
        $products = [];
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
            if ($storage && $storage->id && $storage->storage_type_id == 1) {
                $sql = "SELECT s2.line_id, s2.line_code, s2.line_name, s2.product_id, s2.product_code, s2.product_name, s2.stock
                        FROM (SELECT s1.line_id, s1.line_code, s1.line_name, s1.product_id, s1.product_code, s1.product_name, SUM(s1.qty) AS stock
                              FROM (SELECT p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                    INNER JOIN wms_products AS p ON p.id = md.product_id
                                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                                    WHERE m.status = 1
                                    AND m.storage_id = $id
                                    AND md.bale_id IS NOT NULL
                                    AND l.category_id = 6
                                    ORDER BY m.date ASC) AS s1
                        GROUP BY s1.line_id, s1.line_code, s1.line_name, s1.product_id, s1.product_code, s1.product_name) AS s2
                        WHERE s2.stock > 0
                        ORDER BY s2.stock ASC;";
                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $products = $data->fetchAll();
            }
            $this->content['result'] = true;
        }
        $this->content['products'] = $products;
        $this->response->setJsonContent($this->content);
    }

    public function getStoragesOfBranch ($id) {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $sql = "SELECT wms_storages.name, wms_storages.id from wms_storage_types 
                inner join wms_storages on wms_storages.storage_type_id = wms_storage_types.id  
                where branch_office_id = $id";

                $data = $this->db->query($sql)->fetchAll();
                $options = [];
                foreach($data as $value){
                    $options[] = [
                        'value' => $value["id"],
                        'label' => $value["name"]
                    ];
                }
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $this->content['storage'] = $options;
                $this->content['result'] = true;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getBulkProductsByStorageId ($id) {
        $products = [];
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
            if ($storage && $storage->id && $storage->storage_type_id) {
                $movements = new MovementsController();
                $products = $movements->generateStorageInventoryv2(null,$id,null,null,null,null);
            }
            $this->content['result'] = true;
        }
        $this->content['products'] = $products;
        $this->response->setJsonContent($this->content);
    }

    public function getBulkProductsByStorageIdByPagination ($id) {
        // $products = [];
        $auxproducts = [];
        $request = $this->request->getPost();
         // echo("<pre>");
         // print_r($request);
         //exit();
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
            if ($storage && $storage->id && $storage->storage_type_id) {
                $movements = new MovementsController();
                foreach($request as $detail){
                    //print_r();
                    $products = $movements->generateStorageInventoryv2(null, $storage->id, null, null, $detail['id'], null);
                    if($products){
                        array_push($auxproducts,$products[0]);
                    }
                    //print_r($products);
                 }
                // $products = $movements->generateStorageInventoryv2(null,$id,null,null,null,null);
            }
            $this->content['result'] = true;
        }
        $this->content['products'] = $auxproducts;
        $this->response->setJsonContent($this->content);
    }

    public function getLaminateProductsByStorageId ($id) {
        $products = [];
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
            if ($storage && $storage->id && $storage->storage_type_id == 8) {
                $sql = "SELECT s2.category_code, s2.line_id, s2.line_code, s2.line_name, s2.product_id, s2.product_code, s2.product_name, s2.stock
                        FROM (SELECT s1.category_code,s1.line_id, s1.line_code, s1.line_name, s1.product_id, s1.product_code, s1.product_name, SUM(s1.qty) AS stock
                              FROM (SELECT p.line_id, c.code AS category_code, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, CASE WHEN m.type = 2 THEN -1 * md.qty WHEN m.type = 1 THEN md.qty END AS qty
                                    FROM wms_movement_details AS md
                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                    INNER JOIN wms_products AS p ON p.id = md.product_id
                                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                                    WHERE m.status = 1 AND m.storage_id = $id AND l.category_id = 5
                                    ORDER BY m.date ASC) AS s1
                        GROUP BY s1.line_id, s1.line_code, s1.line_name, s1.product_id, s1.product_code, s1.product_name,s1.category_code) AS s2
                        WHERE s2.stock > 0
                        ORDER BY s2.stock ASC;";
                $data = $this->db->query($sql);
                //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $products = $data->fetchAll();
            }
            $this->content['result'] = true;
        }
        $this->content['products'] = $products;
        $this->response->setJsonContent($this->content);
    }

    public function getRawMaterialProductsByStorageId ($id) {
        $products = [];
        if (is_numeric($id)) {
            $storage = Storages::findFirst($id);
             if ($storage && $storage->id) {
                $movements = new MovementsController();
                 $products = $movements->generateStorageInventoryv2(null, $storage->id, null, null, null, null);
             }
             $this->content['result'] = true;
        }
        $this->content['products'] = $products;
        $this->response->setJsonContent($this->content);
    }

    public function getUnitProduct ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT wms_units.* from wms_products 
        inner join wms_units on wms_units.id = wms_products.unit_id
        where wms_products.id =  $id";
        $data = $this->db->query($sql)->fetchAll();
        $this->content['unit'] = $data;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    /* public function getRawMaterialProductsByStorageIdDataToOutProduct ($id) {
        // $products = [];
        $auxproducts = [];
        if (is_numeric($id)) {
            $request = $this->request->getPost();
            $storage = Storages::findFirst($id);
             if ($storage && $storage->id) {
                $movements = new MovementsController();
                 foreach($request as $detail){
                    $existencia = $this->db->fetchAll("SELECT * from get_existencias($storage->id, $detail, null, null)");
                    $stock = $existencia ? $existencia[0]['existencia'] : 0;
                    var_dump($stock);
                 }
             }
             $this->content['result'] = true;
        }
        $this->content['products'] = $auxproducts;
        $this->response->setJsonContent($this->content);
    } */
    public function getRawMaterialProductsByStorageIdDataToOutProduct ($storage_id) {
        $request = $this->request->getPost();
        $product_id = $request['product_id'];
        $getStock = $this->db->fetchAll("SELECT * from v_product_stock_price where product_id = $product_id and storage_id = $storage_id");
        $stock = $getStock ? $getStock[0]['stock'] : 0;
        $this->content['result'] = true;
        $this->content['stock'] = $stock;
        $this->response->setJsonContent($this->content);
    }
    public function getRawMaterialProductsByStorageIdData ($id) {
        // $products = [];
        $auxproducts = [];
        if (is_numeric($id)) {
            $request = $this->request->getPost();
            $storage = Storages::findFirst($id);
             if ($storage && $storage->id) {
                $movements = new MovementsController();
                 foreach($request as $detail){
                    //print_r();
                    $products = $movements->generateStorageInventoryv2(null, $storage->id, null, null, intval($detail['product_id']), null);
                    if($products){
                        array_push($auxproducts,$products[0]);
                    }
                 }
             }
             $this->content['result'] = true;
        }
        $this->content['products'] = $auxproducts;
        $this->response->setJsonContent($this->content);
    }


    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $storage = Storages::findFirst("account_id = $actualAccount AND code = '".$request['code']."'");
                if ($storage) {
                    $this->content['message'] = Message::error('Ya existe un almacén con el mismo código.');
                } else {
                    $storage = Storages::findFirst("account_id = $actualAccount AND name = '".$request['name']."'");
                    if ($storage) {
                        $this->content['message'] = Message::error('Ya existe un almacén con el mismo nombre.');
                    } else {
                        $storage = Storages::findFirst("branch_office_id = ".$request['branch_office_id']." AND name =  '".$request['name']."' AND storage_type_id = ".$request['storage_type_id']);
                        if ($storage) {
                            $this->content['message'] = Message::error('Ya existe un almacén del mismo tipo para la misma sucursal.');
                        } else {
                            $storage = new Storages();
                            $storage->setTransaction($tx);
                            $storage->name = strtoupper($request['name']);
                            $storage->code = strtoupper($request['code']);
                            $storage->branch_office_id = intval($request['branch_office_id']);
                            $storage->storage_type_id = intval($request['storage_type_id']);
                            $storage->account_id = $actualAccount;

                            $storage->street = $request['street'];
                            $storage->suburb = $request['suburb'];
                            $storage->zip_code = $request['zip'];
                            $storage->city = $request['city'];
                            if ($storage->create()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El almacén ha sido creado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($storage);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el almacén.');
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $storage = Storages::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($storage) {
                    $auxStorage = Storages::findFirst("branch_office_id = ".$request['branch_office_id']." AND storage_type_id = ".$request['storage_type_id']." AND name =  '".$request['name']."' AND id <> ".$request['id']);
                    if ($auxStorage) {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::error('Ya existe un almacén del mismo tipo para la misma sucursal.');
                    } else {
                        $auxStorage = Storages::findFirst("id <> $id AND account_id = $actualAccount AND code = '".$request['code']."'");
                        if ($auxStorage) {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Ya existe un almacén con el mismo código.');
                        } else {
                            $auxStorage = Storages::findFirst("id <> $id AND account_id = $actualAccount AND name = '".$request['name']."'");
                            if ($auxStorage) {
                                $this->content['result'] = false;
                                $this->content['message'] = Message::error('Ya existe un almacén con el mismo nombre.');
                            }
                        }
                        $storage->setTransaction($tx);
                        $storage->name = strtoupper($request['name']);
                        $storage->code = strtoupper($request['code']);
                        $storage->branch_office_id = intval($request['branch_office_id']);
                        $storage->storage_type_id = intval($request['storage_type_id']);
                        $storage->account_id = $actualAccount;
                        $storage->street = $request['street'];
                        $storage->suburb = $request['suburb'];
                        $storage->zip_code = $request['zip'];
                        $storage->city = $request['city'];
                        if ($storage->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El almacén ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['result'] = false;
                            $this->content['error'] = Helpers::getErrors($storage);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el almacén.');
                            $tx->rollback();
                        }
                    }
                }
            } else {
                $this->content['result'] = false;
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

                $storage = Storages::findFirst($id);

                if ($storage) {
                    $storage->setTransaction($tx);

                    if ($storage->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El almacén ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($storage);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el almacén.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El almacén no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getValidation ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['validation'] = Movements::findFirst("storage_id = $id");
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
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 4 OR role_id = 20 OR role_id = 22 OR role_id = 29 OR role_id = 27 OR role_id = 28 OR role_id = 17 OR role_id = 26)
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
