<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;
use Zxing\QrReader;

class BalesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBales ()
    {
        if ($this->userHasGetPermission()) {
            $sql = "SELECT b.id, b.product_id, p.name AS product, b.qty
                    FROM wms_bales AS b
                    INNER JOIN wms_products AS p
                    ON p.id = b.product_id
                    WHERE p.active;";
            $bales = $this->db->query($sql)->fetchAll();
            $this->content['bales'] = $bales;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getBaleOptions()
    {
        $sql = "SELECT b.id AS value, b.product_id, b.qty, CONCAT('Paca ', b.id, ' (', b.qty, ' Kg.)') AS label
                FROM wms_bales AS b
                INNER JOIN wms_products AS p
                ON p.id = b.product_id AND p.active;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getBalesByTransactionId ($transactionId)
    {
        if ($this->userHasGetPermission()) {
            if (is_numeric($transactionId)) {
                $sql = "SELECT b.id, b.product_id, p.name AS product, b.qty, concat(c.code,'-',l.code,'-',p.name) as product_name
                        FROM wms_bales AS b
                        INNER JOIN wms_products AS p ON p.id = b.product_id
                        INNER JOIN wms_movement_details AS md ON md.bale_id = b.id
                        INNER JOIN wms_movements AS m ON m.id = md.movement_id
                        INNER JOIN wms_lines AS l ON l.id = p.line_id
                        INNER JOIN wms_categories AS c ON c.id = l.category_id
                        AND m.type = 2
                        AND m.transaction_id = $transactionId;";
                $bales = $this->db->query($sql)->fetchAll();
                $this->content['bales'] = $bales;
                $this->content['result'] = true;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPost();
                $tx = $this->transactions->get();
                $bale = new Bales();
                $bale->setTransaction($tx);
                $bale->product_id = $request['product_id'];
                $bale->qty = $request['qty'];
                if ($bale->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La paca ha sido registrada exitosamente.');
                    $this->content['bale'] = $bale;
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($bale);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la paca.');
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

                $bale = Bales::findFirst($id);

                $request = $this->request->getPut();

                if ($bale) {
                    $bale->setTransaction($tx);
                    if (isset($request['product_id']) && is_numeric($request['product_id'])) {
                        $bale->product_id = $request['product_id'];
                    }
                    if (isset($request['qty']) && is_numeric($request['qty'])) {
                        $bale->qty = $request['qty'];
                    }
                    
                    if ($bale->update()) {
                        $this->content['bale'] = $bale;
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La paca ha sido modificada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($bale);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la paca.');
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

                $bale = Bales::findFirst($id);

                if ($bale) {
                    $bale->setTransaction($tx);

                    if ($bale->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La paca ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($bale);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la paca.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La paca no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 4 OR role_id = 5 OR role_id = 6 OR role_id = 7 OR role_id = 8 OR role_id = 9 OR role_id = 10)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userHasGetPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 4 OR role_id = 5 OR role_id = 6 OR role_id = 7 OR role_id = 8 OR role_id = 9 OR role_id = 10)
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
