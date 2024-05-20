<?php

use Phalcon\Mvc\Controller;

class PurchaseOrderDocumentsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getPurchaseOrderDocumentsByPurchaseOrderId ($id)
    {
        if (is_numeric($id)) {
            $this->content['documents'] = PurchaseOrderDocuments::find("order_id = $id");
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de orden de compra válido.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();

            if (isset($request['name']) && isset($request['observations']) && isset($request['order_id']) && is_numeric($request['order_id'])) {
                $purchaseOrderDocument = new PurchaseOrderDocuments();
                $purchaseOrderDocument->order_id = $request['order_id'];
                $purchaseOrderDocument->name = strtoupper($request['name']);
                $purchaseOrderDocument->observations = strtoupper($request['observations']);
                if ($purchaseOrderDocument->create()) {
                    $tx->commit();
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El documento ha sido registrado correctamente.');
                } else {
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el nuevo documento.');
                }
            } else {
                $this->content['message'] = Message::error('No se han recibido los datos necesarios para registrar el nuevo documento.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function uploadFile ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $purchaseOrderDocument = PurchaseOrderDocuments::findFirst($id);
                $request = $this->request->getPost();
                if ($purchaseOrderDocument) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/documents/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $fileName = $file->getName();
                        $extension = $file->getExtension();
                        $fullPath = $upload_dir . $purchaseOrderDocument->order_id . $purchaseOrderDocument->id .'.'.$extension;
                        $this->content['fullPath'] = $fullPath;
                        if ($purchaseOrderDocument->document_name != null && file_exists($upload_dir.$purchaseOrderDocument->document_name)) {
                            @unlink($upload_dir.$purchaseOrderDocument->document_name);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $purchaseOrderDocument->setTransaction($tx);
                        $purchaseOrderDocument->document_name = $fileName;
                        if ($purchaseOrderDocument->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo ha sido subido exitosamente.');
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Error al subir el archivo.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el documento.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        if (is_numeric($id)) {
            try {
                $request = $this->request->getPut();
                $document = PurchaseOrderDocuments::findFirst($id);
                if ($document) {
                    $tx = $this->transactions->get();
                    $document->setTransaction($tx);
                    if (isset($request['name']) && !is_null($request['name'])) {
                        $document->name = $request['name'];
                    }
                    if (isset($request['observations']) && !is_null($request['observations'])) {
                        $document->observations = $request['observations'];
                    }
                    if ($document->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El documento ha sido actualizado correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($order);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el documento.');
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function downloadDocumentFile ($id)
    {
        if (is_numeric($id)) {
            $document = PurchaseOrderDocuments::findFirst($id);
            if ($document && $document->document_name) {
                $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/documents/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0777);
                }
                $extension = explode( '.', $document->document_name );
                $fullPath = $upload_dir.$document->order_id . $document->id.'.'.$extension[1];
                if (file_exists($fullPath)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($fullPath).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($fullPath));
                    readfile($fullPath);
                    exit;
                }
            }
        }
        return null;
    }

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $document = PurchaseOrderDocuments::findFirst($id);
                if ($document) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/purchase-orders/documents/';
                    if ($document->document_name != null && file_exists($upload_dir.$document->document_name)) {
                        if (@unlink($upload_dir.$document->document_name)) {
                            $document->setTransaction($tx);
                            if ($document->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El documento ha sido eliminado correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($document);
                                if ($this->content['error'][1]) {
                                    $this->content['message'] = Message::error($this->content['error'][1]);
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el documento.');
                                }
                            }
                        }
                    } else {
                        $document->setTransaction($tx);
                        if ($document->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El documento ha sido eliminado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($document);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el documento.');
                            }
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el documento seleccionado.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id de documento válida.');
        }

        $this->response->setJsonContent($this->content);
    }
}
