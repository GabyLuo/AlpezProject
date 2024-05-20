<?php

use Phalcon\Mvc\Controller;

class TripExpensesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getExpenses ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT te.id, te.amount, te.date, et.name AS type, te.file
            FROM log_trip_expenses AS te
            INNER JOIN log_expenses_type AS et ON te.type_id = et.id
            WHERE te.trip_id = $id";

            $this->content['expenses'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getExpense ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT te.id, te.amount, te.date, et.name AS type, et.id AS type_id
            FROM log_trip_expenses AS te
            INNER JOIN log_expenses_type AS et ON te.type_id = et.id
            WHERE te.id = $id";

            $this->content['expense'] = $this->db->query($sql)->fetch();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
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

                $tripId = $request['trip_id'];
                $expense =  $request['expense_type'];
                $actualExpense = TripExpenses::findFirst(array("trip_id = $tripId AND type_id = $expense", "order" => "id DESC"));
                if ($actualExpense) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Gasto similar.');
                } else {
                    $tripExpense = new TripExpenses();
                    $tripExpense->setTransaction($tx);
                    $tripExpense->trip_id = $request['trip_id'];
                    $tripExpense->type_id = $request['expense_type'];
                    $tripExpense->amount = $request['expense_amount'];
                    $tripExpense->date = $request['expense_date'];
                    $tripExpense->account_id = $actualAccount;
                    if ($tripExpense->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Gasto ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($tripExpense);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Gasto.');
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                
                $actualAccount = Auth::getUserAccount($validUser->id);
                $tripId = $request['trip_id'];
                $expense =  $request['expense_type'];
                $actualExpense = TripExpenses::findFirst(array("trip_id = $tripId AND type_id = $expense AND id != $id", "order" => "id DESC"));
                if ($actualExpense) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Gasto similar.');
                } else {
                    $tripExpense = TripExpenses::findFirst($id);
                    if ($tripExpense) {
                        $tripExpense->setTransaction($tx);
                        $tripExpense->type_id = $request['expense_type'];
                        $tripExpense->amount = $request['expense_amount'];
                        $tripExpense->date = $request['expense_date'];
                        $tripExpense->account_id = $actualAccount;

                        if ($tripExpense->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Gasto ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($tripExpense);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Typo de gasto.');
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

    public function createFileExpense ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $expense = TripExpenses::findFirst($id);
                $request = $this->request->getPut();
                if ($expense) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/expense/files/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777, true);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $this->content['fileExtension'] = $file->getExtension();
                        $fileName = $id . '.' . $file->getExtension();
                        $fullPath = $upload_dir . $fileName;
                        $this->content['fullPath'] = $fullPath;
                        if ($expense->file != null && file_exists($upload_dir.$expense->file)) {
                            @unlink($upload_dir.$expense->file);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $expense->setTransaction($tx);
                        $expense->file = $fileName;
                        if ($expense->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo ha sido registrada exitosamente.');
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Error al registrar la archivo.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el archivo.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $tripExpense = TripExpenses::findFirst($id);

                if ($tripExpense) {
                    $tripExpense->setTransaction($tx);

                    if ($tripExpense->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Gasto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($tripExpense);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Gasto.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Gasto no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 7 OR role_id = 8)
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
