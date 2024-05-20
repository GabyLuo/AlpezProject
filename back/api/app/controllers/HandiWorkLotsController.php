<?php

use Phalcon\Mvc\Controller;

class HandiWorkLotsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getHandiWorksLots ()
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiworkslots'] = HandiWorkLots::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getHandiWorkLot ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiworklot'] = HandiWorkLots::findFirst();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    /*public function getbyLots ($id)
    {
        $sql = "SELECT whp.id,wh.name,whp.time_job, wh.price as price_hour, wh.id as work_id,(wh.price/60) as price_minute, 
                ((wh.price/60)*whp.time_job) as price_qty
                FROM wms_handiwork_products as whp
                left join wms_products as wp on wp.id= whp.product_id
                left join wms_handiwork as wh on wh.id = whp.handiwork_id
                WHERE wp.id = $id";
                 // print_r($sql);
                 // exit();
        $info = $this->db->query($sql)->fetchAll();
        $total=0;
        for ($i=0; $i <count($info) ; $i++) { 
            # code...
            if($info){
                    $total += $info[$i]['price_qty'];
                }
        }
        
        $this->content['info'] = $info;
        $this->content['tot'] = $total;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }*/

    public function getbylots ($id)
    {


         /*$sql="SELECT pl.id as pl_id,pl.order_id as pl_order_id,pl.product_id as pl_product_id, pl.weight as qty , 
            pl.status as pl_status, wp.code, wp.name, wh.name as name_work , wh.price  as price_hour, whp.time_job,
            whp.handiwork_id,TRUNC((wh.price/60),3) as price_minute,TRUNC(((wh.price/60)*whp.time_job),3) as price_qty,
            TRUNC((((wh.price/60)*whp.time_job)* pl.weight)::numeric,3) as amount,plw.employee_id
            from prd_lots as pl 
            left join wms_products as wp on wp.id= pl.product_id
            left join wms_handiwork_products as whp on  wp.id= whp.product_id
            left join wms_handiwork as wh on wh.id = whp.handiwork_id
            left join prd_lot_works as plw on plw.lot_id = pl.id
            where pl.id = $id";  */ 
            $sql="SELECT pl.id as pl_id,pl.order_id as pl_order_id,pl.product_id as pl_product_id, pl.weight as qty , 
            pl.status as pl_status, wp.code, wp.name, wh.name as name_work , wh.price  as price_hour, whp.time_job,
            whp.handiwork_id,TRUNC((wh.price/60),5) as price_minute,TRUNC(((wh.price/60)*whp.time_job),5) as price_qty,
            TRUNC((((wh.price/60)*whp.time_job)* pl.weight)::numeric,5) as amount,prd_lot_works.employee_id,
            CONCAT(he.name,' ', he.paternal,' ', he.mathers) AS employee_name,prd_lot_works.idw as idw,
            prd_lot_works.qtyw,prd_lot_works.amountw
            from prd_lots as pl 
            left join wms_handiwork_products as whp on whp.product_id =pl.product_id
            left join wms_handiwork as wh on wh.id = whp.handiwork_id
            left join wms_products as wp on wp.id= pl.product_id
            FULL OUTER JOIN (select qty as qtyw, amount as amountw ,prd_lot_works.id as idw, lot_id, employee_id,handiwork_id from prd_lot_works  
                  left join prd_lots on prd_lot_works.lot_id = prd_lots.id
                 where prd_lot_works.lot_id = $id) prd_lot_works  
                 on prd_lot_works.lot_id = pl.id and prd_lot_works.handiwork_id = whp.handiwork_id
            left join hrs_employees as he on he.id = prd_lot_works.employee_id
            where pl.id = $id 
            
            ";

        $lot = $this->db->query($sql)->fetchAll();

        /*echo("<pre>");
        print_r($lot);
        echo("</pre>");
        exit();*/
     

        $total=0;
        $totalw=0;
        $emp=false;
    
        for ($j=0; $j <count($lot) ; $j++) { 
                    $total += $lot[$j]['amount'];
                    $totalw += $lot[$j]['amountw'];
                    if($lot[$j]['employee_id'] != null){
                            $emp =$emp+1;
                    }
                    
        }


       
       
        $this->content['info'] =$lot;
        $this->content['tot'] = number_format($total,5);
        $this->content['totw'] = number_format($totalw,5);
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
                /*echo("<pre>");
                 print_r($request);
                 echo("</pre>");
                 exit();*/
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                        $HandiWork = new HandiWorkLots();
                        $HandiWork->setTransaction($tx);
                        $HandiWork->lot_id = $request['row']['pl_id'];
                        $HandiWork->handiwork_id = $request['row']['handiwork_id'];
                        $HandiWork->employee_id = $request['employee_id'];
                        $HandiWork->qty = $request['row']['qty'];
                        $HandiWork->amount = $request['row']['amount'];
                        $HandiWork->price_hour = $request['row']['price_hour'];
                        $HandiWork->time_job = $request['row']['time_job'];

                        $id= $request['row']['pl_id'];

                        if ($HandiWork->create()) {

                             $lote = ProductionLots::findFirst("id = $id");
                             if($lote->status == 'NUEVO' || $lote->status='OPERADOR' || $lote->status=='ASIGNADO')
                             {
                                $lote->status ="ASIGNADO";
                             }
                             
                              if ($lote->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El Operador Agregado Correctamente.');
                               // $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($lote);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar Agregar Operador');
                                $tx->rollback();
                            }

                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('Operador Agregado Correctamente.');
                           /* $tx->commit();*/
                        } else {
                            $this->content['error'] = Helpers::getErrors($HandiWork);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar Agregar operador.');
                            // $tx->rollback();
                        }
                    
                if($this->content['result'] == true){
                    $tx->commit();
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

                

               

                $request = $this->request->getPut();
               /*echo('<pre>');
                 print_r($request);
                 print_r($id);
                 exit();*/

                $HandiWork = HandiWorkLots::findFirst($id);
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
               

           
            
                        if ($HandiWork) {
                            $HandiWork->setTransaction($tx);
                            // $HandiWork->name = strtoupper($request['name']);
                           if(is_array($request['employee_id'])){
                        $employee_id = intval($request['employee_id']['value']);
                    }else{
                        $employee_id = intval($request['employee_id']);
                    }
                            $HandiWork->employee_id = $employee_id;

                            if ($HandiWork->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El Operador Modificado Correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($HandiWork);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar Modificar Operador');
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
