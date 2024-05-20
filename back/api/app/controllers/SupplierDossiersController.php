<?php

use Phalcon\Mvc\Controller;

class SupplierDossiersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getDossiers ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sp.id, sp.name, sp.description, sp.file
            FROM pur_suppliers_dossiers AS sp
            WHERE sp.supplier_id = $id";

            $this->content['dossiers'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getDossier ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sp.id, sp.name, sp.description, sp.file
            FROM pur_suppliers_dossiers AS sp
            WHERE sp.supplier_id = $id";

            $this->content['dossier'] = $this->db->query($sql)->fetch();
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

                $supplierDossiers = new SupplierDossiers();
                $supplierDossiers->setTransaction($tx);
                $supplierDossiers->supplier_id = intval($request['supplier_id']);
                $supplierDossiers->name = $request['name'] ? strtoupper($request['name']) : null;
                $supplierDossiers->description = $request['description'] ?strtoupper($request['description']) : null ;
                if ($supplierDossiers->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Expediente registrado correctamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($supplierDossiers);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el Expediente.');
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

    public function createFileExpense ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $expense = TripExpenses::findFirst($id);
                $request = $this->request->getPut();
                if ($expense) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/expense/files/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777, true);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $this->content['fileExtension'] = $file->getExtension();
                        $fileName = $id . '.' . $file->getExtension();
                        $fullPath = $upload_dir . $fileName;
                        $this->content['fullPath'] = $fullPath;
                        if ($expense->file != null && file_exists($upload_dir.$expense->file)) {
                            @unlink($upload_dir.$expense->file);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $expense->setTransaction($tx);
                        $expense->file = $fileName;
                        if ($expense->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo ha sido registrada exitosamente.');
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Error al registrar la archivo.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el archivo.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }
    
    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $supplierDossiers = SupplierDossiers::findFirst($id);

                if ($supplierDossiers) {
                    $supplierDossiers->setTransaction($tx);

                    if ($supplierDossiers->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El expediente ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplierDossiers);
                        if ($this->content['error'][0]) {
                            $this->content['message'] = Message::error($this->content['error'][0]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el expediente.');
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
    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $supplierDossiers = SupplierDossiers::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($supplierDossiers) {
                    $supplierDossiers->setTransaction($tx);
                    $supplierDossiers->supplier_id = intval($request['supplier_id']);
                    $supplierDossiers->name = $request['name'] ? strtoupper($request['name']) : null;
                    $supplierDossiers->description = $request['description'] ? strtoupper($request['description']) : null;

                    if ($supplierDossiers->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El expediente ha sido modificado correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplierDossiers);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la expediente.');
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
    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 20  OR role_id = 17 OR role_id = 22 OR role_id = 28)
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
