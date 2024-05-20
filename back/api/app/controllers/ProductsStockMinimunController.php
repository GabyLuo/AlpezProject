<?php

use Phalcon\Mvc\Controller;

class ProductsStockMinimunController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];



    public function getbyProduct ($p)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {

            $sql = "SELECT e.id,e.stock,wp.name,s.name as almacen,bo.name as sucursal,e.min_operation,e.max_operation, e.capacity
                    from wms_products_minimum_stock as e
                    left join wms_products as wp on e.product_id = wp.id
                    left JOIN wms_storages AS s ON s.id = e.storage_id 
                    left JOIN wms_branch_offices AS bo ON bo.id = e.branch_offices_id
                    left join wms_lines as wl on wl.id = wp.line_id
                    left join wms_categories as wc on wc.id =wl.category_id
                    left join wms_units as wu on wu.id = wp.unit_id
                    where e.product_id =$p and wp.active
                    order by e.id;";

             $data = $this->db->query($sql)->fetchAll();
            $content['equivalence'] = $data;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
  public function get ($id,$idlotw)
    {
        $sql = "SELECT e.id,e.product_id,p.name,s.name as almacen,s.id as almacen_id,bo.name as sucursal, bo.id as sucursal_id, e.stock,e.min_operation,e.max_operation, e.capacity
                from wms_products_minimum_stock as e
                left join wms_products as p on p.id = e.product_id
                left JOIN wms_storages AS s ON s.id = e.storage_id 
                left JOIN wms_branch_offices AS bo ON bo.id = e.branch_offices_id
                where e.id = $id";
                 // print_r($sql);
                 // exit();
        $info = $this->db->query($sql)->fetchAll();
        $this->content['info'] = $info;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    
    public function getOptions () {
        $sql = "";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

  


    public function create ()
    {
        try {
            if ($this->userHasPermission()) {

                $flagRegistry = true;
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                /*echo("<pre>");
                print_r($request);
                exit();*/
                $content = $this->content;

                if (is_numeric($request['id'])) {

                    $equivalence = new ProductsStocks();
                    $equivalence->setTransaction($tx);
                    $equivalence->product_id = intval(($request['id']));
                    if(is_array($request['branch_id'])){
                        $equivalence->branch_offices_id = ($request['branch_id']['value'] == '') ? null : intval($request['branch_id']['value']);
                    }else {
                        $equivalence->branch_offices_id = ($request['branch_id'] == '') ? null : intval($request['branch_id']);
                    }

                    if(is_array($request['storage'])){
                        $equivalence->storage_id = ($request['storage']['value'] == '') ? null : intval($request['storage']['value']);
                    }else {
                        $equivalence->storage_id = ($request['storage'] == '') ? null : intval($request['storage']);
                    }
                    $equivalence->stock = intval(($request['stock']));
                    $equivalence->min_operation = intval(($request['min_operation']));
                    $equivalence->max_operation = intval(($request['max_operation']));
                    $equivalence->capacity = intval(($request['capacity']));

                    if ($equivalence->create()) {
                     $this->content['equivalence'] = $equivalence;
                     $this->content['result'] = true;
                     $this->content['message'] = Message::success('El stock ha sido agregado.');
                     $tx->commit();
                 } else {
                    $this->content['error'] = Helpers::getErrors($equivalence);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar Agregar el stock.');
                    $tx->rollback();
                }
            }
            else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ()
    {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPut();
                /*echo("<pre>");
                 print_r($request);
                 exit();*/
                if (is_numeric($request['idedit'])) {
                    $tx = $this->transactions->get();
                    $equivalence = ProductsStocks::findFirst($request['idedit']);
                        if ($equivalence) {
                            $validUser = Auth::getUserData($this->config);
                            $equivalence->setTransaction($tx);
                            if(is_array($request['branch_id'])){
                        $equivalence->branch_offices_id = ($request['branch_id']['value'] == '') ? null : intval($request['branch_id']['value']);
                    }else {
                        $equivalence->branch_offices_id = ($request['branch_id'] == '') ? null : intval($request['branch_id']);
                    }

                    if(is_array($request['storage'])){
                        $equivalence->storage_id = ($request['storage']['value'] == '') ? null : intval($request['storage']['value']);
                    }else {
                        $equivalence->storage_id = ($request['storage'] == '') ? null : intval($request['storage']);
                    }
                    $equivalence->stock = intval(($request['stock']));
                    $equivalence->min_operation = intval($request['min_operation']);
                    $equivalence->max_operation = intval($request['max_operation']);
                    $equivalence->capacity = intval(($request['capacity']));
                            
                            if ($equivalence->update()) {
                                $this->content['equivalence'] = $equivalence;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El stock ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($equivalence);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el stock.');
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

                $equivalence = ProductsStocks::findFirst($id);

                if ($equivalence) {
                    $equivalence->setTransaction($tx);

                    if ($equivalence->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Equivalente ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($equivalence);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el equivalente.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El producto no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 or role_id = 20 or role_id = 25 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    private function userIsClient ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 1 OR role_id = 3 or role_id = 20 or role_id = 25 or role_id = 4 OR role_id = 22 OR role_id = 27 OR role_id = 29)
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
