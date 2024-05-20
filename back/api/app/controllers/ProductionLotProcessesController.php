<?php

use Phalcon\Mvc\Controller;

class ProductionLotProcessesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getProcess ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['process'] = ProductionLotProcesses::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getProcessesByLotId ($lotId)
    {
        if (is_numeric($lotId)) {
            $finalProcessesInfo = [];
            if ($this->userHasPermission()) {
                $sql = "SELECT lp.id, lp.process_id, p.name AS process, lp.lot_id, lp.dryer_number, TO_CHAR(lp.start_date, 'dd/mm/yyyy HH24:MI') AS start_date, TO_CHAR(lp.finish_date, 'dd/mm/yyyy HH24:MI') AS finish_date, CASE WHEN lp.process_id = 1 THEN CONCAT(lp.process_id, '-', lp.dryer_number) ELSE CONCAT(lp.process_id) END AS process_order
                        FROM prd_lot_processes AS lp
                        INNER JOIN prd_processes AS p
                        ON p.id = lp.process_id
                        WHERE lot_id = $lotId
                        ORDER BY lp.id ASC;";
                $processes = $this->db->query($sql)->fetchAll();
                foreach ($processes as $process) {
                    $canStart = false;
                    if (is_null($process['start_date'])) {
                        $processId = intval($process['process_id']);
                        switch ($processId) {
                            case 1:
                                $dryerNumber = intval($process['dryer_number']) % 3;
                                if ($dryerNumber == 1) {
                                    if ($process['dryer_number'] == 1) {
                                        $canStart = true;
                                    } else {
                                        $previousDryerNumber = intval($process['dryer_number']) - 3;
                                        $previousLotId = intval($process['lot_id']) - 1;
                                        $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                        if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                            $canStart = true;
                                        }
                                    }
                                } elseif ($dryerNumber == 2) {
                                    $previousDryerNumber = intval($process['dryer_number']) - 1;
                                    $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                    if ($previousLotProcess && !is_null($previousLotProcess->start_date)) {
                                        if ($process['dryer_number'] == 2) {
                                            $canStart = true;
                                        } else {
                                            $previousDryerNumber = intval($process['dryer_number']) - 3;
                                            $previousLotId = intval($process['lot_id']) - 1;
                                            $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                            if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                                $canStart = true;
                                            }
                                        }
                                    }
                                } else {
                                    $previousDryerNumber = intval($process['dryer_number']) - 1;
                                    $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                    if ($previousLotProcess && !is_null($previousLotProcess->start_date)) {
                                        if ($process['dryer_number'] == 3) {
                                            $canStart = true;
                                        } else {
                                            $previousDryerNumber = intval($process['dryer_number']) - 3;
                                            $previousLotId = intval($process['lot_id']) - 1;
                                            $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                            if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                                $canStart = true;
                                            }
                                        }
                                    }
                                }
                                break;
        
                            case 2:
                                $firstDriedUpLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 1 AND dryer_number % 3 = 1");
                                if ($firstDriedUpLotProcess && !is_null($firstDriedUpLotProcess->finish_date)) {
                                    $canStart = true;
                                }
                                break;
        
                            case 3:
                                $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = ".($processId - 1));
                                if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                    $lot = ProductionLots::findFirst($process['lot_id']);
                                    if ($lot) {
                                        $firstOrderLot = ProductionLots::findFirst([
                                            "order_id = ".$lot->order_id,
                                            'order' => 'id ASC',
                                            'limit' => 1
                                        ]);
                                        if ($firstOrderLot) {
                                            $canStart = true;
                                            /* if ($firstOrderLot->id == $lot->id) {
                                                $canStart = true;
                                            } else {
                                                $previousLotPackingProcess = ProductionLotProcesses::findFirst("lot_id = ".$firstOrderLot->id." AND process_id = 6");
                                                if ($previousLotPackingProcess) {
                                                    if (!is_null($previousLotPackingProcess->finish_date)) {
                                                        $canStart = true;
                                                    }
                                                }
                                            } */
                                        }
                                    }
                                }
                                break;
        
                            case 4:
                                $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 2");
                                if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                    $canStart = true;
                                }
                                break;
        
                            case 5:
                                $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 2");
                                if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                    $canStart = true;
                                }
                                break;
        
                            case 6:
                                $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$process['lot_id']." AND process_id = 2");
                                if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                    $canStart = true;
                                }
                                break;
                        }
                    }
                    $process['can_start'] = $canStart;
                    array_push($finalProcessesInfo, $process);
                }
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        }
        $this->content['processes'] = $finalProcessesInfo;
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                if (isset($request['process_id']) && is_numeric($request['process_id']) && isset($request['lot_id']) && is_numeric($request['lot_id'])) {
                    $canCreate = true;
                    $lotProcess = new ProductionLotProcesses();
                    $lotProcess->setTransaction($tx);
                    $lotProcess->process_id = $request['process_id'];
                    $lotProcess->lot_id = $request['lot_id'];
                    if ($lotProcess->process_id == 1) {
                        if (isset($request['dryer_number']) && is_numeric($request['dryer_number'])) {
                            $lotProcess->dryer_number = $request['dryer_number'];
                        } else {
                            $canCreate = false;
                        }
                    } else {
                        $lotProcess->dryer_number = null;
                    }
                    if ($canCreate) {
                        if ($lotProcess->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El proceso ha sido registrado correctamente.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lotProcess);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el proceso.');
                            // $tx->rollback();
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

    public function update ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $lotProcess = ProductionLotProcesses::findFirst($id);

                    $request = $this->request->getPut();

                    if ($lotProcess && isset($request['process_id']) && is_numeric($request['process_id']) && isset($request['lot_id']) && is_numeric($request['lot_id'])) {
                        $lotProcess->setTransaction($tx);
                        $lotProcess->process_id = $request['process_id'];
                        $lotProcess->lot_id = $request['lot_id'];
                        if ($lotProcess->process_id == 1 && isset($request['dryer_number']) && is_numeric($request['dryer_number'])) {
                            $lotProcess->dryer_number = $request['dryer_number'];
                        } else {
                            $lotProcess->dryer_number = null;
                        }

                        if ($lotProcess->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El proceso ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lotProcess);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el proceso.');
                            $tx->rollback();
                        }
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

    public function start ($id)
    {
        if (!is_null($id) && is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $lotProcess = ProductionLotProcesses::findFirst($id);

                    $request = $this->request->getPut();

                    if ($lotProcess) {
                        if (is_null($lotProcess->start_date)) {
                            $this->content['lotProcess'] = $lotProcess;
                            $processId = intval($lotProcess->process_id);
                            switch ($processId) {
                                case 1:
                                    $dryerNumber = intval($lotProcess->dryer_number) % 3;
                                    if ($dryerNumber == 1) {
                                        if ($lotProcess->dryer_number == 1) {
                                            $lotProcess->start_date = date('Y-m-d H:i:s');
                                            if ($lotProcess->update()) {
                                                $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                                if ($lot && $lot->status='SURTIDO') {
                                                    $lot->status = 'SECADO';
                                                    if ($lot->update()) {
                                                        $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO')");
                                                        if (!$previousStatusLot) {
                                                            $order = ProductionOrders::findFirst($lot->order_id);
                                                            $order->status = 'SECADO';
                                                            if ($order->update()) {
                                                                $this->content['result'] = true;
                                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                                $tx->commit();
                                                            } else {
                                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                            }
                                                        } else {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        }
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lot);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                    }
                                                } else {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                    $tx->commit();
                                                }
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($lotProcess);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                // $tx->rollback();
                                            }
                                        } else {
                                            $previousDryerNumber = intval($lotProcess->dryer_number) - 3;
                                            $previousLotId = intval($lotProcess->lot_id) - 1;
                                            $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                            if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                                $lotProcess->start_date = date('Y-m-d H:i:s');
                                                if ($lotProcess->update()) {
                                                    $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                                    if ($lot && $lot->status='NUEVO') {
                                                        $lot->status = 'SECADO';
                                                        if ($lot->update()) {
                                                            $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO')");
                                                            if (!$previousStatusLot) {
                                                                $order = ProductionOrders::findFirst($lot->order_id);
                                                                $order->status = 'SECADO';
                                                                if ($order->update()) {
                                                                    $this->content['result'] = true;
                                                                    $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                                    $tx->commit();
                                                                } else {
                                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                                }
                                                            } else {
                                                                $this->content['result'] = true;
                                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                                $tx->commit();
                                                            }
                                                        } else {
                                                            $this->content['error'] = Helpers::getErrors($lot);
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lotProcess);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = false;
                                                $this->content['message'] = Message::error("No se puede iniciar el proceso sin terminar el proceso de secado en Secada $previousDryerNumber.");
                                            }
                                        }
                                    } elseif ($dryerNumber == 2) {
                                        $previousDryerNumber = intval($lotProcess->dryer_number) - 1;
                                        $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                        if ($previousLotProcess && !is_null($previousLotProcess->start_date)) {
                                            if ($lotProcess->dryer_number == 2) {
                                                $lotProcess->start_date = date('Y-m-d H:i:s');
                                                if ($lotProcess->update()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lotProcess);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $previousDryerNumber = intval($lotProcess->dryer_number) - 3;
                                                $previousLotId = intval($lotProcess->lot_id) - 1;
                                                $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                                if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                                    $lotProcess->start_date = date('Y-m-d H:i:s');
                                                    if ($lotProcess->update()) {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lotProcess);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                    }
                                                } else {
                                                    $this->content['result'] = false;
                                                    $this->content['message'] = Message::error("No se puede iniciar el proceso sin terminar el proceso de secado en Secada $previousDryerNumber.");
                                                }
                                            }
                                        } else {
                                            $this->content['result'] = false;
                                            $this->content['message'] = Message::error('No se puede iniciar el proceso sin iniciar el anterior.');
                                        }
                                    } else {
                                        $previousDryerNumber = intval($lotProcess->dryer_number) - 1;
                                        $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                        if ($previousLotProcess && !is_null($previousLotProcess->start_date)) {
                                            if ($lotProcess->dryer_number == 3) {
                                                $lotProcess->start_date = date('Y-m-d H:i:s');
                                                if ($lotProcess->update()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lotProcess);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $previousDryerNumber = intval($lotProcess->dryer_number) - 3;
                                                $previousLotId = intval($lotProcess->lot_id) - 1;
                                                $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$previousLotId." AND process_id = 1 AND dryer_number = ".$previousDryerNumber);
                                                if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                                    $lotProcess->start_date = date('Y-m-d H:i:s');
                                                    if ($lotProcess->update()) {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lotProcess);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                    }
                                                } else {
                                                    $this->content['result'] = false;
                                                    $this->content['message'] = Message::error("No se puede iniciar el proceso sin terminar el proceso de secado en Secada $previousDryerNumber.");
                                                }
                                            }
                                        } else {
                                            $this->content['result'] = false;
                                            $this->content['message'] = Message::error('No se puede iniciar el proceso sin terminar el anterior.');
                                        }
                                    }
                                    break;
                                
                                case 2:
                                    $firstDriedUpLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 1 AND dryer_number % 3 = 1");
                                    if ($firstDriedUpLotProcess && !is_null($firstDriedUpLotProcess->finish_date)) {
                                        $lotProcess->start_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                            if ($lot && ($lot->status == 'SECADO' || $lot->status == 'NUEVO')) {
                                                $lot->status = 'EXTRUDER';
                                                if ($lot->update()) {
                                                    $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO' OR status = 'SECADO')");
                                                    if (!$previousStatusLot) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $order->status = 'EXTRUDER';
                                                        if ($order->update()) {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        } else {
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                        }
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['message'] = Message::error('No se puede iniciar el proceso sin terminar el primer proceso de secado del lote.');
                                    }
                                    break;
                                
                                case 3:
                                    $canStart = false;
                                    $previousLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = ".($processId - 1));
                                    if ($previousLotProcess && !is_null($previousLotProcess->finish_date)) {
                                        $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                        if ($lot) {
                                            $firstOrderLot = ProductionLots::findFirst([
                                                "order_id = ".$lot->order_id,
                                                'order' => 'id ASC',
                                                'limit' => 1
                                            ]);
                                            if ($firstOrderLot) {
                                                $canStart = true;
                                                /* if ($firstOrderLot->id == $lot->id) {
                                                    $canStart = true;
                                                } else {
                                                    $previousLotPackingProcess = ProductionLotProcesses::findFirst("lot_id = ".$firstOrderLot->id." AND process_id = 6");
                                                    if ($previousLotPackingProcess) {
                                                        if (!is_null($previousLotPackingProcess->finish_date)) {
                                                            $canStart = true;
                                                        } else {
                                                            $this->content['result'] = false;
                                                            $this->content['message'] = Message::error('No se puede iniciar el proceso sin haber terminado el proceso EMPAQUE del lote anterior.');
                                                        }
                                                    }
                                                } */
                                            } else {
                                                $this->content['result'] = false;
                                                $this->content['message'] = Message::error('Ha ocurrido un error al consultar el lote anterior.');
                                            }
                                        } else {
                                            $this->content['result'] = false;
                                            $this->content['message'] = Message::error('Ha ocurrido un error al consultar el lote anterior.');
                                        }
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['message'] = Message::error('No se puede iniciar el proceso sin haber terminado el proceso EXTRUDER.');
                                    }
                                    if ($canStart) {
                                        $lotProcess->start_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                            if ($lot && ($lot->status == 'EXTRUDER' || $lot->status == 'SECADO' || $lot->status == 'NUEVO')) {
                                                $lot->status = 'ESTIRADO';
                                                if ($lot->update()) {
                                                    $lot->status = 'ESTIRADO';
                                                    if ($lot->update()) {
                                                        $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO' OR status = 'SECADO' OR status = 'EXTRUDER')");
                                                        if (!$previousStatusLot) {
                                                            $order = ProductionOrders::findFirst($lot->order_id);
                                                            $order->status = 'ESTIRADO';
                                                            if ($order->update()) {
                                                                $this->content['result'] = true;
                                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                                $tx->commit();
                                                            } else {
                                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                            }
                                                        } else {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        }
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lot);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                            // $tx->rollback();
                                        }
                                    }
                                    break;
                                
                                case 4:
                                    $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 2");
                                    if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                        $lotProcess->start_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                            if ($lot && ($lot->status == 'ESTIRADO' || $lot->status == 'EXTRUDER' || $lot->status == 'SECADO' || $lot->status == 'NUEVO')) {
                                                $lot->status = 'RIZADO';
                                                if ($lot->update()) {
                                                    $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO' OR status = 'SECADO' OR status = 'EXTRUDER' OR status = 'ESTIRADO')");
                                                    if (!$previousStatusLot) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $order->status = 'RIZADO';
                                                        if ($order->update()) {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        } else {
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                            // $tx->rollback();
                                        }
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['message'] = Message::error('No se puede iniciar el proceso sin haber terminado el proceso EXTRUDER.');
                                    }
                                    break;
                                
                                case 5:
                                    $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 2");
                                    if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                        $lotProcess->start_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                            if ($lot && ($lot->status == 'RIZADO' || $lot->status == 'ESTIRADO' || $lot->status == 'EXTRUDER' || $lot->status == 'SECADO' || $lot->status == 'NUEVO')) {
                                                $lot->status = 'HORNO';
                                                if ($lot->update()) {
                                                    $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO' OR status = 'SECADO' OR status = 'EXTRUDER' OR status = 'ESTIRADO' OR status = 'RIZADO')");
                                                    if (!$previousStatusLot) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $order->status = 'HORNO';
                                                        if ($order->update()) {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        } else {
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                            // $tx->rollback();
                                        }
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['message'] = Message::error('No se puede iniciar el proceso sin haber terminado el proceso EXTRUDER.');
                                    }
                                    break;
                                
                                case 6:
                                    $extruderLotProcess = ProductionLotProcesses::findFirst("lot_id = ".$lotProcess->lot_id." AND process_id = 2");
                                    if ($extruderLotProcess && !is_null($extruderLotProcess->finish_date)) {
                                        $lotProcess->start_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                            if ($lot && ($lot->status == 'HORNO' || $lot->status == 'RIZADO' || $lot->status == 'ESTIRADO' || $lot->status == 'EXTRUDER' || $lot->status == 'SECADO' || $lot->status == 'NUEVO')) {
                                                $lot->status = 'EMPAQUE';
                                                if ($lot->update()) {
                                                    $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND (status = 'NUEVO' OR status = 'FORMULADO' OR status = 'SURTIDO' OR status = 'SECADO' OR status = 'EXTRUDER' OR status = 'ESTIRADO' OR status = 'RIZADO' OR status = 'HORNO')");
                                                    if (!$previousStatusLot) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $order->status = 'EMPAQUE';
                                                        if ($order->update()) {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                            $tx->commit();
                                                        } else {
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($lot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El proceso ha sido iniciado.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                            // $tx->rollback();
                                        }
                                    } else {
                                        $this->content['result'] = false;
                                        $this->content['message'] = Message::error('No se puede iniciar el proceso sin haber terminado el proceso EXTRUDER.');
                                    }
                                    break;
                            }
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('El proceso ya se encuentra iniciado.');
                        }
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

    public function finish ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $lotProcess = ProductionLotProcesses::findFirst($id);

                    $request = $this->request->getPut();

                    if ($lotProcess) {
                        if (!is_null($lotProcess->start_date)) {
                            if (is_null($lotProcess->finish_date)) {
                                $scrapQuery = "lot_id = $lotProcess->lot_id AND process_id = $lotProcess->process_id";
                                if ($lotProcess->dryer_number) {
                                    $scrapQuery .= " AND dryer_number = $lotProcess->dryer_number";
                                }
                                $scrap = ProductionScraps::findFirst($scrapQuery);
                                if ($scrap || $lotProcess->process_id == 6) {
                                    if ($lotProcess->process_id == 6) {
                                        $unfinishedPreviousLotProcesses = ProductionLotProcesses::find("lot_id = $lotProcess->lot_id AND finish_date IS NULL AND process_id <> 6");
                                        if (count($unfinishedPreviousLotProcesses) == 0) {
                                            $lotProcess->finish_date = date('Y-m-d H:i:s');
                                            if ($lotProcess->update()) {
                                                $lot = ProductionLots::findFirst($lotProcess->lot_id);
                                                if ($lot) {
                                                    $lot->status = 'TERMINADO';
                                                    if ($lot->update()) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $sql = "SELECT id
                                                                FROM prd_lots
                                                                WHERE order_id = $order->id
                                                                AND status <> 'TERMINADO'
                                                                LIMIT 1;";
                                                        $unfinishedLot = $this->db->query($sql)->fetch();
                                                        if (!$unfinishedLot) {
                                                            $order->status = 'TERMINADO';
                                                            $order->update();
                                                        }
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El proceso ha sido finalizado.');
                                                        $tx->commit();
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($lot);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar iniciar el proceso.');
                                                    }
                                                }
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($lotProcess);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar finalizar el proceso.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('El proceso no se puede finalizar debido a que hay procesos anteriores sin finalizar.');
                                        }
                                    } else {
                                        $lotProcess->finish_date = date('Y-m-d H:i:s');
                                        if ($lotProcess->update()) {
                                            $this->content['result'] = true;
                                            $this->content['message'] = Message::success('El proceso ha sido finalizado.');
                                            $tx->commit();
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($lotProcess);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar finalizar el proceso.');
                                        }
                                    }
                                } else {
                                    $this->content['message'] = Message::error('El proceso no se puede finalizar debido a que no cuenta con scrap registrado.');
                                }
                            } else {
                                $this->content['message'] = Message::error('El proceso ya se encuentra finalizado.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El proceso no se puede finalizar debido a que no se ha iniciado.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el proceso.');
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

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();
            
                    $lotProcess = ProductionLotProcesses::findFirst($id);
            
                    if ($lotProcess) {
                        $lotProcess->setTransaction($tx);
                
                        if ($lotProcess->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El proceso ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($lotProcess);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el proceso.');
                            }
                            // $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El proceso no existe.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 5 OR role_id = 6 OR role_id = 7)
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
