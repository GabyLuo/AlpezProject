<?php

use Phalcon\Mvc\Controller;

class ActionsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getActions ()
    {
        if ($this->userHasPermission()) {
            $actions = Actions::findFirst(2);
            if ($actions) {
                $this->content['actions'] = $actions;
                $this->content['result'] = true;
            } else {
                $this->content['result'] = false;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function update ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $actions = Actions::findFirst(2);

                $request = $this->request->getPut();

                if ($actions) {
                    $actions->setTransaction($tx);
                    $actions->daily_production_time_1 = $request['daily_production_time_1'];
                    $actions->daily_production_time_2 = $request['daily_production_time_2'];
                    $actions->host = $request['host'];
                    $actions->encryption = $request['encryption'];
                    $actions->port = $request['port'];
                    $actions->username = $request['username'];
                    $actions->password = $request['password'];

                    if ($actions->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Acciones modificadas correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($actions);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar las acciones.');
                        // $tx->rollback();
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
