<?php

use Phalcon\Mvc\Controller;

class CustomerTaxCompaniesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCustomerTaxCompanies ($customerId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($customerId)) {
                $sql = "SELECT ctc.id, ctc.customer_id, ctc.razon_social, ctc.rfc, ctc.metodo_pago, ctc.immex,
                ctc.forma_pago, ctc.uso_cfdi, email, serie, sat_formas_pagos.descripcion as forma_pago_label, 
                sat_uso_cfdi.descripcion as uso_cfdi_label,ctc.rfc_banco,ctc.cuenta,ctc.banco ,ctc.lugar_expedicion, ctc.regimen_fiscal
                FROM sls_customer_tax_companies AS ctc 
                    JOIN sat_formas_pagos ON sat_formas_pagos.id = ctc.forma_pago
                    JOIN sat_uso_cfdi ON sat_uso_cfdi.id = ctc.uso_cfdi
                    WHERE customer_id = $customerId ORDER BY ID ASC;";
                $this->content['customerTaxCompanies'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de cliente válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getcustomerTaxCompany ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT ctc.id, ctc.customer_id, ctc.razon_social, ctc.rfc, ctc.lugar_expedicion, ctc.metodo_pago, ctc.forma_pago, ctc.uso_cfdi, email, 
            serie, sat_formas_pagos.descripcion as forma_pago_label, sat_uso_cfdi.descripcion as uso_cfdi_label, ctc.rfc_banco,ctc.cuenta,ctc.banco,
            ctc.regimen_fiscal,ctc.immex
            FROM sls_customer_tax_companies AS ctc 
                    JOIN sat_formas_pagos ON sat_formas_pagos.id = ctc.forma_pago
                    JOIN sat_uso_cfdi ON sat_uso_cfdi.id = ctc.uso_cfdi
                    WHERE ctc.id = $id;";
            $this->content['customerTaxCompanies'] = $this->db->query($sql)->fetchAll()[0];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getcustomerTaxCompanyByClient ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT c.*, b.name as branch_name,
                case when 
                (data_fs.razon_social is null or data_fs.rfc is null or data_fs.lugar_expedicion is null or data_fs.metodo_pago is null or data_fs.forma_pago is null or data_fs.uso_cfdi is null or data_fs.serie is null or data_fs.email = '' or  data_fs.email is null)
                then 'NO'
                else 'SI' end as yesornotdatafs
                FROM sls_customers AS c
                INNER JOIN wms_branch_offices AS b 
                ON c.branch_id = b.id
                left join sls_customer_tax_companies as data_fs on data_fs.customer_id = c.id
                WHERE data_fs.customer_id = $id;";
                    //print_r($sql);
                    //exit();
            $this->content['customerTaxCompanies'] = $this->db->query($sql)->fetchAll()[0];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions ()
    {
        $sql = "SELECT id AS value, name AS label, customer_id AS customer FROM sls_customer_branch_offices ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();

                $customerTaxCompany = new CustomerTaxCompanies();
                $customerTaxCompany->setTransaction($tx);
                $customerTaxCompany->customer_id = strtoupper($request['customer_id']);
                $customerTaxCompany->razon_social = trim(strtoupper($request['razon_social']));
                $customerTaxCompany->rfc = strtoupper($request['rfc']);
                $customerTaxCompany->lugar_expedicion = strtoupper($request['lugar_expedicion']);
                $customerTaxCompany->metodo_pago = $request['metodo_pago'];
                $customerTaxCompany->forma_pago = $request['forma_pago'];
                $customerTaxCompany->uso_cfdi = $request['uso_cfdi'];
                $customerTaxCompany->email = !empty($request['email']) ? $request['email'] : NULL;
                $customerTaxCompany->serie = $request['serie'];
                $customerTaxCompany->rfc_banco = $request['rfc_banco'];
                $customerTaxCompany->cuenta = $request['cuenta'];
                $customerTaxCompany->banco = $request['banco'];
                $customerTaxCompany->regimen_fiscal = $request['regimen_fiscal'];
                $customerTaxCompany->immex = $request['immex'];
                if ($customerTaxCompany->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Información fiscal registrada correctamente.');
                    $tx->commit();
                } else {
                    $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($customerTaxCompany);
                        
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error($this->content['error']['message'] ?? 'Ha ocurrido un error al intentar modificar la información fiscal.');
                        }  

                   /*  $this->content['error'] = Helpers::getErrors($customerTaxCompany);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la información fiscal.'); */
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customerTaxCompany = CustomerTaxCompanies::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($customerTaxCompany) {
                    $customerTaxCompany->setTransaction($tx);
                    $customerTaxCompany->customer_id = strtoupper($request['customer_id']);
                    $customerTaxCompany->razon_social = trim(strtoupper($request['razon_social']));
                    $customerTaxCompany->rfc = strtoupper($request['rfc']);
                    $customerTaxCompany->lugar_expedicion = strtoupper($request['lugar_expedicion']);
                    $customerTaxCompany->metodo_pago = $request['metodo_pago'];
                    $customerTaxCompany->forma_pago = $request['forma_pago'];
                    $customerTaxCompany->uso_cfdi = $request['uso_cfdi'];
                    $customerTaxCompany->email = !empty($request['email']) ? $request['email'] : NULL;
                    $customerTaxCompany->serie = $request['serie'];
                    $customerTaxCompany->rfc_banco = $request['rfc_banco'];
                    $customerTaxCompany->cuenta = $request['cuenta'];
                    $customerTaxCompany->banco = $request['banco'];
                    $customerTaxCompany->regimen_fiscal = $request['regimen_fiscal'];
                    $customerTaxCompany->immex = $request['immex'];
                    if ($customerTaxCompany->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La información fiscal ha sido modificada correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($customerTaxCompany);
                        
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error($this->content['error']['message'] ?? 'Ha ocurrido un error al intentar modificar la información fiscal.');
                        }  
                        /* $this->content['error'] = Helpers::getErrors($customerTaxCompany);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la información fiscal.'); */
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

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customerTaxCompany = CustomerTaxCompanies::findFirst($id);
                $sql = "SELECT * FROM sls_invoices where tax_company_id = $id";
                $query = $this->db->query($sql)->fetchAll();
                if (count($query)){
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se puede eliminar este registro debido a que se esta usando en remisiones.');
                }else {
                    if ($customerTaxCompany) {
                        $customerTaxCompany->setTransaction($tx);
    
                        if ($customerTaxCompany->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La información fiscal ha sido eliminada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($customerTaxCompany);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la información fiscal.');
                            }
                            // $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El cliente no existe.');
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

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 20 OR role_id = 4  OR role_id = 17 OR role_id = 20 OR role_id = 22 OR role_id = 27 OR role_id = 28 OR role_id = 29)
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
