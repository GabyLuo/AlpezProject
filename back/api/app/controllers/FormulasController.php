<?php

use Phalcon\Mvc\Controller;

class FormulasController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getFormulas ($pt = 0)
    {
        $content = $this->content;
        $sql = "SELECT f.id, f.lot_id, f.product_id, p.name AS product, f.qty, f.quality
                FROM prd_lots_formulas AS f
                INNER JOIN wms_products AS p
                ON p.id = f.product_id;";

        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['formulas'] = $data->fetchAll();
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getFormula ($id)
    {
        $content = $this->content;
        $formula = null;
        if (is_numeric($id)) {
            $sql = "SELECT f.id, f.lot_id, f.product_id, p.name AS product, f.qty, f.quality
                    FROM prd_lots_formulas AS f
                    INNER JOIN wms_products AS p
                    ON p.id = f.product_id
                    WHERE f.id = $id;";

            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $formula = $data->fetch();
        }
        $content['formula'] = $formula;
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getFormulasByLotId ($lotId)
    {
        $content = $this->content;
        $formulas = [];
        if (is_numeric($lotId)) {
            $sql = "SELECT f.id, f.lot_id, f.product_id, p.name AS product, f.qty, f.quality
                    FROM prd_lots_formulas AS f
                    INNER JOIN wms_products AS p
                    ON p.id = f.product_id
                    WHERE f.lot_id = $lotId;";

            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $formulasAux = $data->fetchAll();

            foreach ($formulasAux as $formula) {
                $sql = "SELECT product_id, SUM(qty) AS stock
                        FROM (SELECT m.type AS movement_type, ship_det.product_id, ship_det.qty
                            FROM wms_movements AS m
                            INNER JOIN pur_shipments AS ship
                            ON ship.movement_id = m.id
                            INNER JOIN pur_shipment_details AS ship_det
                            ON ship_det.shipment_id = ship.id
                            WHERE m.status = 1
                            AND m.storage_id = 2
                            AND ship_det.product_id = ".$formula['product_id']."
                            UNION ALL
                            SELECT m.type AS movement_type, ship_det.product_id, CASE WHEN m.type = 1 THEN (md.qty * 1) WHEN m.type = 2 THEN (md.qty * -1) END AS qty
                            FROM wms_movements AS m
                            INNER JOIN wms_movement_details AS md
                            ON md.movement_id = m.id
                            INNER JOIN pur_shipment_details AS ship_det
                            ON ship_det.id = md.bag_id
                            WHERE m.status = 1
                            AND m.storage_id = 2
                            AND ship_det.product_id = ".$formula['product_id']."
                            ) AS s1
                        GROUP BY product_id;";
                $data = $this->db->query($sql);
               //  $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $stock = $data->fetch();
                $formula['currentStock'] = 0;
                if ($stock && $stock['stock'] > 0) {
                    $sql = "SELECT pod.price
                            FROM pur_order_details AS pod
                            WHERE pod.product_id = ".$formula['product_id']."
                            ORDER BY created DESC
                            LIMIT 1;";
                    $prodPrice = $this->db->query($sql);
                    $prodPrice->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                    $price = $prodPrice->fetch();
                    $formula['currentStock'] = $stock['stock'];
                    if ($price && !is_null($price['price'])) {
                        $formula['lastPrice'] = $price['price'];
                    }
                }
                array_push($formulas, $formula);
            }
        }

        $content['formulas'] = $formulas;
       
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ()
    {
        try {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();

            $product = Products::findFirst($request['product_id']);
            if ($product && $product->active) {
                $formula = new Formulas();
                $formula->setTransaction($tx);
                $formula->lot_id = $request['lot_id'];
                $formula->product_id = $request['product_id'];
                $formula->qty = $request['qty'];
                $formula->quality = strtoupper($request['quality']);

                if ($formula->create()) {
                    $lot = ProductionLots::findFirst($formula->lot_id);
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('No se puede registrar la fórmula debido a que el lote ya se encuentra cancelado.');
                    } else {
                        if ($lot->status == 'NUEVO') {
                            $lot->status = 'FORMULADO';
                            if ($lot->update()) {
                                $previousStatusLot = ProductionLots::findFirst("id <> ".$lot->id." AND order_id = ".$lot->order_id." AND status = 'NUEVO'");
                                if (!$previousStatusLot) {
                                    $order = ProductionOrders::findFirst($lot->order_id);
                                    $order->status = 'FORMULADO';
                                    if ($order->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La fórmula ha sido modificada.');
                                        $tx->commit();
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la fórmula.');
                                    }
                                } else {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('La fórmula ha sido modificada.');
                                    $tx->commit();
                                }
                            } else {
                                $this->content['error'] = Helpers::getErrors($lot);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la fórmula.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La fórmula ha sido modificada.');
                            $tx->commit();
                        }
                    }
                } else {
                    $this->content['error'] = Helpers::getErrors($formula);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la fórmula.');
                    $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('El producto está inactivo.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            $tx = $this->transactions->get();

            $formula = Formulas::findFirst($id);

            $request = $this->request->getPut();

            if ($formula) {
                $lot = ProductionLots::findFirst($formula->lot_id);
                if ($lot->status == 'CANCELADO') {
                    $this->content['message'] = Message::error('No se puede actualizar la fórmula debido a que el lote ya se encuentra cancelado.');
                } else {
                    $formula->setTransaction($tx);
                    // $formula->lot_id = $request['lot_id'];
                    $product = Products::findFirst($request['product_id']);
                    if ($product && $product->active) {
                        $formula->product_id = $product->id;
                        $formula->qty = $request['qty'];
                        $formula->quality = strtoupper($request['quality']);

                        if ($formula->update()) {
                            $this->content['formula'] = $formula;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La fórmula ha sido modificada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($formula);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la fórmula.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
         try {
            $tx = $this->transactions->get();

            $formula = Formulas::findFirst($id);

            if ($formula) {
                $lot = ProductionLots::findFirst($formula->lot_id);
                if ($lot) {
                    if ($lot->status == 'CANCELADO') {
                        $this->content['message'] = Message::error('No se puede eliminar la fórmula debido a que el lote ya se encuentra cancelado.');
                    } else {
                        $formula->setTransaction($tx);

                        if ($formula->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La fórmula ha sido eliminada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($formula);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la fórmula.');
                            }
                            // $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('El lote no ha sido encontrado.');
                }
            } else {
                $this->content['message'] = Message::error('La fórmula no existe.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}
