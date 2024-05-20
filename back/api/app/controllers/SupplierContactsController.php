<?php

use Phalcon\Mvc\Controller;

class SupplierContactsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getSupplierContacts ($supplierId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($supplierId)) {
                $sql = "SELECT scc.id, scc.supplier_id, scc.name, scc.cellphone as phone, scc.telephone as tel, scc.email
                FROM pur_supplier_contacts AS scc
                WHERE supplier_id = $supplierId
                ORDER BY ID ASC;";
                $this->content['contacts'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de cliente válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getSupplierContact ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['contacts'] = SupplierContacts::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions ()
    {
        $sql = "SELECT id AS value, name AS label, supplier_id AS supplier FROM pur_supplier_contacts ORDER BY name ASC;";
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

                $supplierContacts = new SupplierContacts();
                $supplierContacts->setTransaction($tx);
                $supplierContacts->supplier_id = intval($request['supplier_id']);
                $supplierContacts->name = $request['name'] ? strtoupper($request['name']) : null;
                $supplierContacts->cellphone = $request['phone'] ? $request['phone'] : null;
                $supplierContacts->telephone = $request['tel'] ? $request['tel'] : null;
                $supplierContacts->email = $request['email'] ? $request['email'] : null ;
                if ($supplierContacts->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Contacto registrada correctamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($supplierContacts);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el contacto.');
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

                $supplierContacts = SupplierContacts::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($supplierContacts) {
                    $supplierContacts->setTransaction($tx);
                    $supplierContacts->supplier_id = intval($request['supplier_id']);
                    $supplierContacts->name = $request['name'] ? strtoupper($request['name']) : null;
                    $supplierContacts->cellphone = $request['phone'] ? $request['phone'] : null;
                    $supplierContacts->telephone = $request['tel'] ? $request['tel'] : null;
                    $supplierContacts->email = $request['email'] ? $request['email'] : null ;

                    if ($supplierContacts->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El contacto ha sido modificado correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplierContacts);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la sucursal.');
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

                $supplierContacts = SupplierContacts::findFirst($id);

                if ($supplierContacts) {
                    $supplierContacts->setTransaction($tx);

                    if ($supplierContacts->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El contacto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplierContacts);
                        if ($this->content['error'][0]) {
                            $this->content['message'] = Message::error($this->content['error'][0]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el contacto.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 20  OR role_id = 17 OR role_id = 28)
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
