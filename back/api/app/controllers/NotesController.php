<?php

use Phalcon\Mvc\Controller;

class NotesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getNotes ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT * FROM wms_movement_notes WHERE movement_id = $id";
            
            $data = $this->db->query($sql)->fetchAll();
            $this->content['notes'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $note = new Notes();
                $note->setTransaction($tx);
                $note->movement_id = $request['movement_id'];
                $note->note = $request['note'];
                $note->note_date = $request['date'];
                $note->note_time = $request['time'];
                $note->account_id = $actualAccount;

                if ($note->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El Comentrario ha sido agregado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($note);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el Comentario.');
                    // $tx->rollback();
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

                $note = Notes::findFirst($id);

                if ($note) {
                    $note->setTransaction($tx);

                    if ($note->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Comentario ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($note);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Comentario.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Comentario no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 26)
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
