<?php

use Phalcon\Mvc\Controller;

class BranchOfficesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBranchOffices ($pt = 0)
    {
        $branchOffices = [];
        $branchOfficesAux = BranchOffices::find(['order' => 'code ASC']);
        foreach ($branchOfficesAux as $branchOffice) {
            $storages = '';
            $storagesAux = Storages::find("branch_office_id = ".$branchOffice->id);
            foreach ($storagesAux as $storage) {
                if ($storages == '') {
                    $storages .= $storage->name;
                } else {
                    $storages .= '; '.$storage->name;
                }
            }
            $branchOffice = $branchOffice->toArray();
            $branchOffice['storages'] = $storages;
            array_push($branchOffices, $branchOffice);
        }
        $this->content['branchOffices'] = $branchOffices;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getBranchOfficesWithCluster (){
        $sql="SELECT wbo.*, ss.name  as cluster
            FROM public.wms_branch_offices as wbo
            LEFT JOIN sys_supercluster as ss on ss.id = wbo.cluster_id
            ORDER BY wbo.code ASC ";
        $data = $this->db->query($sql)->fetchAll();
        
        $this->content['branchOfficeCluster'] = $data;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send();

    }

    public function getBranchsOffices () {
        $sql = "SELECT id as value, name as label FROM wms_branch_offices";
        $data = $this->db->query($sql)->fetchAll();
        $options = [];
        foreach ($data as $value) {
            $options[] = [
                'label' => $value['label'],
                'value' => $value['value']
            ];
        }
        $this->content['branchs'] = $data;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getBranchOfficesToReportShopping () {
        $sql = "SELECT id as value, name as label FROM wms_branch_offices";
        $branchOffice = $this->db->query($sql)->fetchAll();
        $this->content['branch'] = $branchOffice;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getBranchOffice ($id)
    {
        $content = $this->content;
        $branchOffice = null;
        if (is_numeric($id)) {
            $sql= "SELECT b.id,b.account_id,b.name,b.address,b.created,b.created_by,b.updated,b.updated_by,b.codigo_postal,
            b.postal_code_id, b.suburb_id, b.between_street, b.outdoor_number, b.indoor_number, 
        b.state_id,b.municipality_id,b.code,b.city,m.nombre as municipio, s.nombre as estado, rfc_banco, cuenta ,serie,serie_pagos, cluster_id, customer_id
        from wms_branch_offices as b
        left join sls_municipalities as m on m.id = b.municipality_id
        left join sls_states as s on s.id = m.estado_id
        where b.id = $id ";
        $branchOffice = $this->db->query($sql)->fetch();
           //  $branchOffice = BranchOffices::findFirst($id);
            $this->content['result'] = true;
        }
        $content['branchOffice'] = $branchOffice;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptionsIndexShoppingCart () {
        $validUser = Auth::getUserInfo($this->config);
        
        $where = $validUser->role_id == 1 ? "" : "WHERE id = $validUser->branch_office_id";
        
        
        $sql = "SELECT id, name FROM wms_branch_offices 
        $where
        ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();
        
        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptions () {
        $validUser = Auth::getUserInfo($this->config);
        $officesid = "";
        $where = "";
        if ($validUser->id == 77 || $validUser->id == 107) {//solo 2 usuarios van a poder hacer transferencias entre sus sucursales
            $where .= "WHERE id = 13 or id = 14";
        }else{
            if ($validUser->role_id == 26) {
                $where .= " where cluster_id = ".$validUser->cluster_id;
            } else {
                $where = $validUser->role_id == 1 ? "" : "WHERE id = $validUser->branch_office_id";
            }
        }
        
        $sql = "SELECT id, name FROM wms_branch_offices 
        $where
        ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();
        
        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getBranchOptions () {
        $this->content['options'] = BranchOffices::find(array('columns' => 'id as value, name as label', 'order' => 'name ASC'));
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getCustomerByBranchOffices ($branchId) {
        $content = $this->content;
        $customers = [];
        if (is_numeric($branchId)) {
            $sql = "SELECT sc.id as value, sc.name as label
            FROM wms_branch_offices AS wbo
            LEFT JOIN sls_customers AS sc ON sc.id = wbo.customer_id
            WHERE wbo.id = $branchId
            ORDER BY sc.name ASC;";
            $data = $this->db->query($sql);
            $customers = $data->fetchAll();
        }
        $content['customers'] = $customers;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getByCluster ($clusterId) {
        $validUser = Auth::getUserInfo($this->config);
        $officesid = "";
        $where = "";
        if ($clusterId > 0) {
            $where .= " and cluster_id = $clusterId";
        }
        if ($validUser->role_id == 2) {
            $where .= " and id = " . $validUser->branch_office_id;
        }
        $sql = "SELECT id as value, name as label FROM wms_branch_offices where id > 0 $where ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();
        $this->content['options'] = $types;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function create ()
    {
        try {
              $validUser = Auth::getUserData($this->config);
             if ($validUser) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $branchOffice = new BranchOffices();
                $branchOffice->setTransaction($tx);
                $branchOffice->account_id = Auth::getUserAccount($validUser->id);
                $branchOffice->name = strtoupper($request['name']);
                $branchOffice->code = $request['code'];
                $branchOffice->serie = $request['serie'];
                $branchOffice->serie_pagos = $request['serie_pagos'];
                $branchOffice->rfc_banco = isset($request['rfc_banco'])?strtoupper($request['rfc_banco']):null;
                $branchOffice->cuenta = isset($request['cuenta'])?strtoupper($request['cuenta']):null;

                $branchOffice->address = strtoupper($request['address']);
                $branchOffice->indoor_number = strtoupper($request['indoor_number']);
                $branchOffice->outdoor_number = isset($request['outdoor_number'])?strtoupper($request['outdoor_number']):null;
                $branchOffice->indoor_number = strtoupper($request['indoor_number']);
                $branchOffice->between_street = strtoupper($request['between_street']);
                $branchOffice->postal_code_id = $request['postal_code_id'];
                $branchOffice->suburb_id = $request['suburb_id'];
                $branchOffice->cluster_id = $request['cluster_id'];
                $branchOffice->customer_id = $request['customer_id'];

                if ($branchOffice->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La sucursal ha sido creada.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($branchOffice);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la sucursal.');
                    $tx->rollback();
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
            if (is_numeric($id)) {
                $validUser = Auth::getUserData($this->config);
                $tx = $this->transactions->get();
                $request = $this->request->getPut();

                $branchOffice = BranchOffices::findFirst($id);
                $branchOffice->setTransaction($tx);
                $branchOffice->name = strtoupper($request['name']);
                $branchOffice->address = strtoupper($request['address']);
                $branchOffice->account_id = Auth::getUserAccount($validUser->id);
                $branchOffice->serie = $request['serie'];
                $branchOffice->serie_pagos = $request['serie_pagos'];
                $branchOffice->rfc_banco = isset($request['rfc_banco'])?strtoupper($request['rfc_banco']):null;
                $branchOffice->cuenta = isset($request['cuenta'])?strtoupper($request['cuenta']):null;
                $branchOffice->code = $request['code'];

                $branchOffice->address = strtoupper($request['address']);
                $branchOffice->indoor_number = strtoupper($request['indoor_number']);
                $branchOffice->outdoor_number = isset($request['outdoor_number'])?strtoupper($request['outdoor_number']):null;
                $branchOffice->indoor_number = strtoupper($request['indoor_number']);
                $branchOffice->between_street = strtoupper($request['between_street']);
                $branchOffice->postal_code_id = $request['postal_code_id']??null;
                $branchOffice->suburb_id = $request['suburb_id']??null;
                $branchOffice->cluster_id = $request['cluster_id']?$request['cluster_id']:null;
                $branchOffice->customer_id = $request['customer_id']?$request['customer_id']:null;
                
                if ($branchOffice->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La sucursal ha sido modificada.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($branchOffice);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la sucursal.');
                    // $tx->rollback();
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            if (isset($this->content['errors']['code']) && $this->content['errors']['code'] == 23505) {
                $this->content['message'] = Message::error('Nombre o dirección registrados con anterioridad.');
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            $tx = $this->transactions->get();
            if (is_numeric($id)) {
                $branchOffice = BranchOffices::findFirst($id);

                if ($branchOffice) {
                    $branchOffice->setTransaction($tx);

                    if ($branchOffice->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La sucursal ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($branchOffice);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la sucursal.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La sucursal no existe.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function postal_codes ($code, $municipio)
    {        
        $id = $code;
        $code = $code === '0' ? '' : $code;
        $municipio??'null';
        $sql = "SELECT pc.id as value, pc.postal_code as label, UPPER(s.state) as state, UPPER(m.municipality) as municipality,
        s.id as state_id, m.id as municipality_id, l.id as city_id
        FROM sys_postal_code pc
        join sys_state s on s.state_code = pc.state_code
        join sys_municipality m on m.municipality_code = pc.municipality_code and m.state_code = s.state_code
        left join sys_location l on l.location_code = pc.location_code and l.state_code = s.state_code
        where (pc.postal_code like '%$code%' or pc.id = $id) and (m.id = $municipio or $municipio is null)
        ORDER BY pc.id ASC
        limit 10;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function suburbs ($id)
    {
        $sql = "SELECT s.id as value, UPPER(s.suburb) as label 
        FROM sys_suburbs s
        join sys_postal_code p on s.postal_code = p.postal_code
        where p.id = $id
        ORDER BY label ASC;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function states ()
    {
        $sql = "SELECT s.id as value, UPPER(s.state) as label 
        FROM sys_state s
        ORDER BY s.id ASC;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function municipalities ($state, $postal)
    {
        $sql = "SELECT distinct m.id as value, UPPER(m.municipality) as label 
        from sys_postal_code pc
        join sys_state s on s.state_code = pc.state_code
        join sys_municipality m on m.municipality_code = pc.municipality_code and m.state_code = s.state_code
        where s.id = $state and ($postal is null or pc.id = $postal)
        ORDER BY m.id ASC;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function cities ($state, $postal)
    {
        $sql = "SELECT distinct l.id as value, UPPER(l.location) as label 
        from sys_postal_code pc
        join sys_state s on s.state_code = pc.state_code
        join sys_location l on l.location_code = pc.location_code and l.state_code = s.state_code
        where s.id = $state and ($postal is null or pc.id = $postal)
        ORDER BY l.id ASC;";
        $options = $this->db->query($sql)->fetchAll();
        
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

}
