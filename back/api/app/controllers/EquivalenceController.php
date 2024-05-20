<?php

use Phalcon\Mvc\Controller;

class EquivalenceController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];



    public function getbyProduct ($p)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {

            $sql = "SELECT e.id,e.equivalence_id,wp.name
                    from wms_equivalence as e
                    left join wms_products as wp on e.equivalence_id = wp.id
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
        $sql = "SELECT e.id,e.product_id,e.equivalence_id,p.name from wms_equivalence as e
                left join wms_products as p on p.id = e.equivalence_id where e.id = $id";
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

                if (is_numeric($request['product'])) {

                    $equivalence = new Equivalence();
                    $equivalence->setTransaction($tx);

                    $equivalence->product_id = intval(($request['id']));
                    $equivalence->equivalence_id = intval(($request['product']));;

                    if ($equivalence->create()) {
                     $this->content['equivalence'] = $equivalence;
                     $this->content['result'] = true;
                     $this->content['message'] = Message::success('El equivalente ha sido agregado.');
                     $tx->commit();
                 } else {
                    $this->content['error'] = Helpers::getErrors($equivalence);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar Agregar el equivalente.');
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
                    $equivalence = Equivalence::findFirst($request['idedit']);
                        if ($equivalence) {
                            $validUser = Auth::getUserData($this->config);
                            $equivalence->setTransaction($tx);
                            if(is_array($request['product'])){
                                $equivalence->equivalence_id = intval(($request['product']['value']));
                            }else {
                                $equivalence->equivalence_id = intval(($request['product']));
                            }
                            
                            if ($equivalence->update()) {
                                $this->content['equivalence'] = $equivalence;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El equivalente ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($equivalence);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el equivalente.');
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

                $equivalence = Equivalence::findFirst($id);

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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 5 OR role_id = 3 OR role_id = 4)
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
                    WHERE (role_id = 14 OR role_id = 16 OR role_id = 3)
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
