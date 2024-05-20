<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StatesMunicipalityController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAll()
    {
        //$user = Auth::getUserInfo($this->config);

        $builder = $this->modelsManager->createBuilder();
        $builder->columns([
            'm.id',
            'm.nombre',
        ])
        ->from(['m' => 'Municipios'])
        ->orderBy('m.id DESC');
        /*if($user->role_id != 1) {
            $conditions = ['account_id'=>$user->account_id];
            $builder->where('u.account_id = :account_id: ',$conditions);
        }*/

        $paginator = new PaginatorQueryBuilder([
            'builder' => $builder,
            'limit'   => 100000,
            'page'    => 1,
        ]);


        $this->content['municipios'] = $paginator->paginate()->items;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content); 
        $this->response->send(); 
    }
    public function get ($id)
    {
        $sql = "SELECT m.id,m.nombre
                FROM sls_municipality m
                WHERE m.id = $id ";
        $municipio = $this->db->query($sql)->fetch();
        $this->content['municipio'] = $municipio;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send(); 
    }

    public function getOptions () {

        $sql = "SELECT id, nombre as name FROM sls_municipality  ORDER BY nombre ASC;";
       
        $municipios = $this->db->query($sql)->fetchAll();
        $options = [];
        foreach ($municipios as $municipio) {
            array_push($options, array('value' => intval($municipio['id']), 'label' => $municipio['name']));
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content); 
        $this->response->send();   
    }


    public function getEstadosOptions () {

        $sql = "SELECT id, nombre as name FROM sls_states  ORDER BY nombre ASC;";
       
        $estados = $this->db->query($sql)->fetchAll();
        $options = [];
        foreach ($estados as $estado) {
            array_push($options, array('value' => intval($estado['id']), 'label' => $estado['name']));
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content); 
        $this->response->send();   
    }

    public function getMunicipiosbyEstadosOptions ($id) {

        $sql = "SELECT id, nombre as name FROM sls_municipalities where estado_id= $id  ORDER BY nombre ASC;";
       
        $estados = $this->db->query($sql)->fetchAll();
        $options = [];
        foreach ($estados as $estado) {
            array_push($options, array('value' => intval($estado['id']), 'label' => $estado['name']));
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content); 
        $this->response->send();   
    }

    public function create ()
    {

        try {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();
            $municipio = new Municipios();
            $municipio->setTransaction($tx);
            $municipio->nombre = strtoupper($request['nombre']);
        
            if ($municipio->create() !== false) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El municipio ha sido registrado.');
                $this->content['municipioId'] = $municipio->id;
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($municipio);
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el municipio.');
                $tx->rollback();
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
        $this->response->send(); 
    }

    public function update ($id)
    {

        try {
            $tx = $this->transactions->get();

            $municipio = Municipios::findFirst($id);

            $request = $this->request->getPut();
            if ($municipio) {
                $municipio->setTransaction($tx);
                $municipio->nombre = strtoupper($request['nombre']);
                
            	if ($municipio->update() !== false) {
            		$this->content['result'] = true;
            		$this->content['message'] = Message::success('El municipio ha sido modificado.');
                    $tx->commit();
            	} else {
                    $this->content['error'] = Helpers::getErrors($municipio);
            		$this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el municipio.');
                    $tx->rollback();
            	}
            }
        } catch (Exception $e) {
            if (Message::exception($e)['code'] == 23505) {
                $this->content['error'] = Helpers::getErrors($user);
                $this->content['message'] = Message::error('El correo electrónico ingresado ya cuenta con una cuenta registrada.');
            }
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
        $this->response->send(); 
    }


    public function delete($id)
    {
        $content = $this->content;

        try {
            $tx = $this->transactions->get();

            $line = Municipios::findFirst($id);
            $line->setTransaction($tx);

            if ($line->delete()) {
                $content['result'] = true;
                $content['message'] = Message::success('Se ha elminado el municipio.');
            } else {
                $content['message'] = Message::error(
                    $line->getMsgError() ?? 'No se pudo eliminar el municipio.'
                );
                $tx->rollback();
            }

            if ($content['result']) {
                $tx->commit();
            }
        } catch (Exception $e) {
            $content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($content);
        $this->response->send();
    }

}
