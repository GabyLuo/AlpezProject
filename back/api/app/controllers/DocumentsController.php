<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class DocumentsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    public function getAll ($id)
    {
        if ($this->userHasPermission()) {
            $builder = $this->modelsManager->createBuilder();
            $builder->columns([
                'j.id',
                'j.name',
                'j.description',
                'j.ext',
                'j.type',
                'round(j.size/1000000,2) as size',
                "TO_CHAR(j.created, 'yyyy/mm/dd') as date"
            ])
            ->from(['j' => 'Files'  ])
            ->where('j.repository_id = '.$id)
            ->orderBy('j.id DESC');

            $paginator = new PaginatorQueryBuilder([
                'builder' => $builder,
                'limit'   => 100000,
                'page'    => 1,
            ]);


            $this->content['info'] = $paginator->paginate()->items;
            $this->content['result'] = true;

        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getDocumentOfPay ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT * from sys_documents where id = $id";
            
            $this->content['documents'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        }else {
            $this->content = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent( $this->content);
        $this->response->send();
    }
    public function uploadFile($id)
    {
        $request = $this->request->getPost();
        $idFile = 0;
        if ($this->userHasPermission()) {
            // Check if the user has uploaded files
            if ($this->request->hasFiles()) {
                $upload_dir = dirname(__FILE__)  . '/../../documents/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0755);
                }
                $files = $this->request->getUploadedFiles();
                // Print the real file names and sizes
                foreach ($files as $file) {
                    // Print file details
                    // Move the file into the application
    
                    $type = $file->getType();
                    $size = $file->getSize();
                    $extension = $file->getExtension();
                    $name = $file->getName();

                    $file_name = md5(date('d-m-Y h:i:s').$type.$size);

                    $fileNew = new Documents();
                    $fileNew->filename = $name;
                    $fileNew->ext = $extension;
                    $fileNew->size = $size;
                    $fileNew->mimetype = $type;
    
                    if($fileNew->create()){
                        $idFile = $fileNew->id;
                        $result = $file->moveTo(
                            dirname(__FILE__)  . '/../../documents/' . $fileNew->id
                        );
                        
                        $this->content['img_id'] = $fileNew->id;
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se ha cargado el archivo correctamente.');
                    }else{
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($fileNew);
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }
                }
                
            }else{
                $this->content['message'] = Message::error('No se guardo ningun archivo.');
            }
        } else {
            $this->content = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->updateShoppingCartWithDocumentId($id, $idFile);
        $this->response->setJsonContent( $this->content);
        $this->response->send();
    }

    public function updateShoppingCartWithDocumentId ($id, $iddocument) {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $shopping = ShoppingCart::findFirst($id);
            if ($shopping) {
                $shopping->setTransaction($tx);
                $shopping->document_id = intval($iddocument);
                if ($shopping->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Se agregó archivo a la tabla.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($shopping);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el El archivo en la tabla.');
                    $tx->rollback();
                }
            }
        }else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
    }

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $info = Documents::findFirst($id);
                /* $document = RhinoOrderDocuments::findFirst("document_id = ".intval($id));
                if ($document) {
                    $document->setTransaction($tx);
                    $document->document_id = null;
                    $document->update();
                } */
                /* $expenseFile = RhinoOrderExpenses::findFirst("document_id = ".intval($id));
                if ($expenseFile) {
                    $expenseFile->setTransaction($tx);
                    $expenseFile->document_id = null;
                    $expenseFile->update();
                } */
                /* $employeeDocument = EmployeeDocuments::findFirst("document_id = ".intval($id));
                if ($employeeDocument) {
                    $employeeDocument->setTransaction($tx);
                    $employeeDocument->document_id = null;
                    $employeeDocument->update();
                } */
                /* $employee = Employees::findFirst("photo_id = ".intval($id));
                if ($employee) {
                    $employee->setTransaction($tx);
                    $employee->photo_id = null;
                    $employee->update();
                } */
                /* $divisions = Divisions::findFirst("document_id = ".intval($id));
                if ($divisions) {
                    $divisions->setTransaction($tx);
                    $divisions->document_id = null;
                    $divisions->update();
                } */

                $shoppingCartDocId = ShoppingCart::findFirst("document_id = ".intval($id));
                if ($shoppingCartDocId) {
                    $shoppingCartDocId->setTransaction($tx);
                    $shoppingCartDocId->document_id = null;
                    $shoppingCartDocId->update();
                }
                if ($info) {
                    $info->setTransaction($tx);
                    if ($info->delete()) {
                        if(unlink(dirname(__FILE__)  . '/../../documents/'.$info->id)){
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El archivo ha sido eliminado.');
                                $tx->commit();
                        }else{
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el archivo.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['error'] = Helpers::getErrors($info);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el archivo.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El archivo no existe.');
                }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function getFile ($id) {
        try {
            if ($this->userHasPermission()) {
                $document = Documents::findFirst($id);
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: *");
                // modifiqué esto 24-01-2022 
                // /../../documents/

                readfile(dirname(__FILE__)  . '/../../public/documentspay/'.$document->id);
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
    }


    public function getFileOrderShoppingCart ($id) {
        try {
            if ($this->userHasPermission()) {
                $document = Documents::findFirst($id);
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: *");
                // modifiqué esto 24-01-2022 
                // /../../documents/

                $sql = "SELECT * from sys_documents where id = $id";
                $query = $this->db->query($sql)->fetchAll();
                $this->content['datadocument'] = $query;
                $this->response->setJsonContent($this->content);
                readfile(dirname(__FILE__)  . '/../../documents/'.$document->id);
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
        
    }
    private function userHasPermission ()
    {
        return true;
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 25)
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
