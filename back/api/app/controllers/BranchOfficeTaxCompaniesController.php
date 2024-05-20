<?php

use Phalcon\Mvc\Controller;

class BranchOfficeTaxCompaniesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBranchOfficesTaxCompanies ($branchOfficeId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($branchOfficeId)) {
                $sql = "SELECT wbt.id, wbt.branch_office_id, wbt.razon_social, wbt.rfc, wbt.lugar_expedicion, wbt.metodo_pago, wbt.forma_pago,
                wbt.uso_cfdi, email, serie, sat_formas_pagos.descripcion as forma_pago_label, sat_uso_cfdi.descripcion as uso_cfdi_label
                FROM wms_branch_office_tax_companies as wbt
                    JOIN sat_formas_pagos ON sat_formas_pagos.id = wbt.forma_pago
                    JOIN sat_uso_cfdi ON sat_uso_cfdi.id = wbt.uso_cfdi
                    WHERE branch_office_id = $branchOfficeId ORDER BY ID ASC;";
                $this->content['branchOfficeTaxCompanies'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de estación válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getBranchOfficeTaxCompany ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT wbt.id, wbt.branch_office_id, wbt.razon_social, wbt.rfc, wbt.lugar_expedicion, wbt.metodo_pago, wbt.forma_pago,
                    wbt.uso_cfdi, email, serie, sat_formas_pagos.descripcion as forma_pago_label, sat_uso_cfdi.descripcion as uso_cfdi_label
                FROM wms_branch_office_tax_companies as wbt
                JOIN sat_formas_pagos ON sat_formas_pagos.id = wbt.forma_pago
                JOIN sat_uso_cfdi ON sat_uso_cfdi.id = wbt.uso_cfdi
                WHERE wbt.id = $id;";
            $this->content['branchOfficeTaxCompanies'] = $this->db->query($sql)->fetchAll()[0];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $branchOfficeTaxCompany = new BranchOfficesTaxCompanies();
                $branchOfficeTaxCompany->setTransaction($tx);
                $branchOfficeTaxCompany->branch_office_id = strtoupper($request['branch_office_id']);
                $branchOfficeTaxCompany->razon_social = trim(strtoupper($request['razon_social']));
                $branchOfficeTaxCompany->rfc = strtoupper($request['rfc']);
                $branchOfficeTaxCompany->lugar_expedicion = strtoupper($request['lugar_expedicion']);
                $branchOfficeTaxCompany->metodo_pago = $request['metodo_pago'];
                $branchOfficeTaxCompany->forma_pago = $request['forma_pago'];
                $branchOfficeTaxCompany->uso_cfdi = $request['uso_cfdi'];
                $branchOfficeTaxCompany->email = !empty($request['email']) ? $request['email'] : NULL;
                $branchOfficeTaxCompany->serie = $request['serie'];
                if ($branchOfficeTaxCompany->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Información fiscal registrada correctamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($branchOfficeTaxCompany);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la información fiscal.');
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

                $branchOfficeTaxCompany = BranchOfficesTaxCompanies::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($branchOfficeTaxCompany) {
                    $branchOfficeTaxCompany->setTransaction($tx);
                    $branchOfficeTaxCompany->branch_office_id = strtoupper($request['branch_office_id']);
                    $branchOfficeTaxCompany->razon_social = trim(strtoupper($request['razon_social']));
                    $branchOfficeTaxCompany->rfc = strtoupper($request['rfc']);
                    $branchOfficeTaxCompany->lugar_expedicion = strtoupper($request['lugar_expedicion']);
                    $branchOfficeTaxCompany->metodo_pago = $request['metodo_pago'];
                    $branchOfficeTaxCompany->forma_pago = $request['forma_pago'];
                    $branchOfficeTaxCompany->uso_cfdi = $request['uso_cfdi'];
                    $branchOfficeTaxCompany->email = !empty($request['email']) ? $request['email'] : NULL;
                    $branchOfficeTaxCompany->serie = $request['serie'];

                    if ($branchOfficeTaxCompany->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La información fiscal ha sido modificada correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($branchOfficeTaxCompany);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la información fiscal.');
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

                $branchOfficeTaxCompany = BranchOfficesTaxCompanies::findFirst($id);

                if ($branchOfficeTaxCompany) {
                    $branchOfficeTaxCompany->setTransaction($tx);

                    if ($branchOfficeTaxCompany->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La información fiscal ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($branchOfficeTaxCompany);
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
