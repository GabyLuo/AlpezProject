<?php

use Phalcon\Mvc\Controller;

class RolesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOptions () {
        $sql = "SELECT id, name FROM sys_roles ORDER BY name ASC;";
        $roles = $this->db->query($sql)->fetchAll();
        $options = [];
        foreach ($roles as $role) {
            array_push($options, array('value' => intval($role['id']), 'label' => $role['name']));
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    
    function getRol($id)
    {
        $content = $this->content;

        $sql = "
        SELECT *
        FROM sys_roles sr
        WHERE sr.id = {$id};";
        $data = $this->db->query($sql)->fetch();

        $content['roles'] = $data;
        $content['result'] = true ?? false;

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    function getAll()
    {
        $content = $this->content;

        $sql = "
        SELECT *
        FROM sys_roles
        ORDER BY name ASC;";
        $data = $this->db->query($sql)->fetchAll();

        $content['roles'] = $data;
        $content['result'] = true;

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    function create()
    {
        try
        {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();

            $rol_name = strtoupper($request['name']);


            $rol = new Roles();
            $rol->setTransaction($tx);
            $rol->name = $rol_name;
            $rol->created_at = date('Y-m-d H:i:s');
            $rol->created_by = 1;
            $rol->account_id = 1;

            if ($rol->create()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('El rol ha sido creada.');
                $tx->commit();
            } else {
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el rol.');
            }
        } catch (Exception $e)
        {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    function update($id)
    {
        try
        {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();

            $rol_name = strtoupper($request['name']);

                $line = Roles::findFirst($id);
                $line->setTransaction($tx);
                $line->name = $rol_name;

                if ($line->update() !== false) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La linea ha sido modificada.');
                    $tx->commit();
                } else {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la linea.');
                }

        } catch (Exception $e)
        {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    function delete($id)
    {
        $content = $this->content;

        try {
            $tx = $this->transactions->get();

            $line = Roles::findFirst($id);
            $line->setTransaction($tx);

            if ($line->delete()) {
                $content['result'] = true;
                $content['message'] = Message::success('Se ha elminado el rol.');
            } else {
                $content['message'] = Message::error(
                    $line->getMsgError() ?? 'No se pudo eliminar el rol.'
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
