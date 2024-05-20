<?php

use Phalcon\Mvc\Controller;

class ShippingsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShippings ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT s.id, s.serial, c.name AS client, s.date, s.file
                    FROM log_shippings AS s
                    INNER JOIN sls_customers AS c ON s.client = c.id
                    WHERE trip_id = $id ORDER BY s.serial ASC";

            $shippings = $this->db->query($sql)->fetchAll();
            $this->content['shippings'] = $shippings;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getShipping ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT s.id, s.trip_id, s.serial, c.name AS client_name, c.id AS client_id, s.date, t.status
            FROM log_shippings AS s
            INNER JOIN sls_customers AS c ON s.client = c.id
            INNER JOIN log_trips AS t ON s.trip_id = t.id
            WHERE s.id = $id";

            $shipping = $this->db->query($sql)->fetch();
            $this->content['shipping'] = $shipping;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getFolio ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT folio FROM log_trips WHERE id = $id";

            $folio = $this->db->query($sql)->fetch();
            $this->content['folio'] = $folio;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getFolioShipping ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT COUNT(id) AS number FROM log_shippings WHERE trip_id = $id";

            $folio = $this->db->query($sql)->fetch();
            $this->content['folioShipping'] = $folio;
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
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $shipping = new Shippings();
                $shipping->setTransaction($tx);
                $shipping->trip_id = $request['trip_id'];
                $shipping->serial = $request['folio'];
                $shipping->date = $request['date'];
                $shipping->client = $request['client'];
                $shipping->account_id = $actualAccount;
                if ($shipping->create()) {
                    $this->content['result'] = true;
                    $this->content['id'] = $shipping->id;
                    $this->content['message'] = Message::success('El Envío ha sido creado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($shipping);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Envío.');
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
                $shipping = Shippings::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                    if ($shipping) {
                        $shipping->setTransaction($tx);
                        $shipping->date = $request['date'];
                        $shipping->client = $request['client'];
                        $shipping->account_id = $actualAccount;
                        if ($shipping->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Envío ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipping);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Envío.');
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

                $shipping = Shippings::findFirst($id);

                if ($shipping) {
                    $shipping->setTransaction($tx);

                    if ($shipping->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Envío ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shipping);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Envío.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Envío no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function createFileShipping ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $shipping = Shippings::findFirst($id);
                $request = $this->request->getPut();
                if ($shipping) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/shippings/files/';
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
                        if ($shipping->file != null && file_exists($upload_dir.$shipping->file)) {
                            @unlink($upload_dir.$shipping->file);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $shipping->setTransaction($tx);
                        $shipping->file = $fileName;
                        if ($shipping->update()) {
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

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3)
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
