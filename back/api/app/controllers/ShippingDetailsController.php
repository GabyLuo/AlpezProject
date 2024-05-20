<?php

use Phalcon\Mvc\Controller;

class ShippingDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShippingDetails ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sd.id, p.code, p.name AS product, c.name AS category, l.name AS line, sd.qty
            FROM log_shippings_details AS sd
            INNER JOIN wms_products AS p ON p.id = sd.product_id
            INNER JOIN wms_lines AS l ON l.id = p.line_id
            INNER JOIN wms_categories AS c ON c.id = l.category_id
            WHERE sd.shipping_id = $id ORDER BY sd.id ASC";

            $products = $this->db->query($sql)->fetchAll();
            $this->content['products'] = $products;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getShippingProduct ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT P.id, CONCAT(p.code,'-',p.name) AS product, sd.qty
            FROM log_shippings_details AS sd
            INNER JOIN wms_products AS p ON p.id = sd.product_id
            WHERE sd.id = $id";

            $shippingDetail = $this->db->query($sql)->fetch();
            $this->content['product'] = $shippingDetail;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, CONCAT(CODE,'-',name) AS name FROM wms_products ORDER BY name ASC;";
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

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualProduct = ShippingDetails::findFirst("product_id = '".$request['product']."' AND shipping_id = '".$request['shipping_id']."'");
                if ($actualProduct) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado el producto en este envío.');
                } else {
                    $shippingDetail = new ShippingDetails();
                    $shippingDetail->setTransaction($tx);
                    $shippingDetail->shipping_id = $request['shipping_id'];
                    $shippingDetail->product_id = $request['product'];
                    $shippingDetail->qty = $request['qty'];
                    $shippingDetail->account_id = $actualAccount;
                    if ($shippingDetail->create()) {
                        $this->content['result'] = true;
                        $this->content['id'] = $shippingDetail->id;
                        $this->content['message'] = Message::success('El Producto ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shippingDetail);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Producto.');
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
                $shippingDetail = ShippingDetails::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualProduct = ShippingDetails::findFirst("product_id = '".$request['product']."' AND shipping_id = '".$request['shipping_id']."' AND id != '".$id."'");
                if ($actualProduct) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado el producto en este envío.');
                } else {
                    if ($shippingDetail) {
                        $shippingDetail->setTransaction($tx);
                        $shippingDetail->product_id = $request['product'];
                        $shippingDetail->qty = $request['qty'];
                        $shippingDetail->account_id = $actualAccount;
                        if ($shippingDetail->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Producto ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shippingDetail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Producto.');
                            $tx->rollback();
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

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shippingDetail = ShippingDetails::findFirst($id);

                if ($shippingDetail) {
                    $shippingDetail->setTransaction($tx);

                    if ($shippingDetail->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Producto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shippingDetail);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Producto.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Producto no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getProductInventory ($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $stock = $this->generateStorageInventory($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date);
            $content['stock'] = $stock;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function generateStorageInventory ($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date)
    {
        $movements = $this->generateKardex(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null);
        $products = [];
        $stock = [];
        foreach ($movements as $movement) {
            if (!in_array($movement['product_id'], $products)) {
                $productStock = 0;
                foreach ($movements as $secondMovement) {
                    if ($movement['product_id'] == $secondMovement['product_id']) {
                        if ($secondMovement['movement_type'] == 1) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 2) {
                            $productStock -= $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 3){
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4){
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5){
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array( 'stock' => $productStock, 'value' => $movement['value'], 'label' => $movement['label']));
            }
        }
        return $stock;
    }

    public function generateKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $sql = "SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, m.date as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, TRUNC(md.qty,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, CONCAT(p.code,'-',p.name) AS label, p.id AS value
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }
        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, m.date as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, TRUNC(md.qty,5) AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, CONCAT(p.code,'-',p.name) AS label, p.id AS value
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }
        $sql .= " ORDER BY date ASC, movement_type DESC;";

        $data = $this->db->query($sql)->fetchAll();
        return $data;
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
