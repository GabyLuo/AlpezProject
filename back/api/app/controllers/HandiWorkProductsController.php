<?php

use Phalcon\Mvc\Controller;

class HandiWorkProductsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getHandiWorks ()
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiworks'] = HandiWorkProducts::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getHandiWork ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiwork'] = HandiWorkProducts::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getby ($id)
    {
        $sql = "SELECT whp.id,wh.name,whp.time_job, TRUNC(wh.price,2) as price_hour, 
        wh.id as work_id,TRUNC((wh.price/60),5) as price_minute, 
                TRUNC(((wh.price/60)*whp.time_job)::numeric,5) as price_qty, whp.factor, whp.minimal
                FROM wms_handiwork_products as whp
                left join wms_products as wp on wp.id= whp.product_id
                left join wms_handiwork as wh on wh.id = whp.handiwork_id
                WHERE wp.id = $id";
                 // print_r($sql);
                 // exit();
        $info = $this->db->query($sql)->fetchAll();
        /*echo('<pre>');
        print_r($info);
        echo('</pre>');
        exit();*/
        $total=0;
        for ($i=0; $i <count($info) ; $i++) { 
            # code...
            if($info){
                    $total += $info[$i]['price_qty'];
                }
        }
        
        $this->content['info'] = $info;
        $this->content['tot'] = number_format($total,5);
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getbygetbylots ($id)
    {
        $sql = "SELECT whp.id,wh.name,whp.time_job, wh.price as price_hour, wh.id,5 as work_id,TRUNC((wh.price/60),5) as price_minute, 
                TRUNC(((wh.price/60)*whp.time_job):numeric,5) as price_qty
                FROM wms_handiwork_products as whp
                left join wms_products as wp on wp.id= whp.product_id
                left join wms_handiwork as wh on wh.id = whp.handiwork_id
                WHERE wp.id = $id";
                 // print_r($sql);
                 // exit();
        $info = $this->db->query($sql)->fetchAll();
        /*echo('<pre>');
        print_r($info);
        echo('</pre>');
        exit();*/
        $total=0;
        for ($i=0; $i <count($info) ; $i++) { 
            # code...
            if($info){
                    $total += $info[$i]['price_qty'];
                }
        }
        
        $this->content['info'] = $info;
        $this->content['tot'] = number_format($total,5);
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }
    public function getOptions () {
        $sql = "SELECT id, name FROM wms_handiwork_products ORDER BY name ASC;";
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
                 //print_r($request);
                 //exit();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                        $HandiWork = new HandiWorkProducts();
                        $HandiWork->setTransaction($tx);
                        if(is_array($request['work_id'])){
                        $product_aux = $request['work_id']['id'];
                    }else{
                        $product_aux = $request['work_id'];
                    }
                        $HandiWork->product_id = strtoupper($request['id']);
                        $HandiWork->time_job = $request['time_job'];
                        $HandiWork->handiwork_id = $product_aux;
                        $HandiWork->minimal = $request['minimal'];
                        $HandiWork->factor = $request['factor'];

                        if ($HandiWork->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La Mano de Obra ha sido Agreada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($HandiWork);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la Mano de Obra.');
                            // $tx->rollback();
                        }
                    
                
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['message'] = Message::error('No Pude agregar 2 Mano de Obra Igual.');
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $HandiWork = HandiWorkProducts::findFirst($id);

                $request = $this->request->getPut();
                //echo('<pre>');
                //print_r($request);
                //echo('</pre>');
                // exit();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                        if ($HandiWork) {
                            $HandiWork->setTransaction($tx);
                            // $HandiWork->name = strtoupper($request['name']);
                            if(is_array($request['work_id'])){
                        $product_aux = $request['work_id']['id'];
                    }else{
                        $product_aux = $request['work_id'];
                    }
                            $HandiWork->time_job = $request['time_job'];
                            $HandiWork->handiwork_id = $product_aux;
                            $HandiWork->minimal = $request['minimal'];
                            $HandiWork->factor = $request['factor'];

                            if ($HandiWork->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La Mano de Obra ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($HandiWork);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la Mano de Obra.');
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

                $HandiWork = HandiWorkProducts::findFirst($id);

                if ($HandiWork) {
                    $HandiWork->setTransaction($tx);


                    if ($HandiWork->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Mano de Obra a sido Eliminada ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($HandiWork);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la Mano de Obra.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La categoría no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 4)
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
