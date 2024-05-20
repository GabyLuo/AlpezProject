<?php

use Phalcon\Mvc\Controller;

class LaminateAdditivesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getLaminateAdditivesByLaminate ($laminateId)
    {
        $content = $this->content;
        $sql = "SELECT md.id, md.product_id, p.name AS product, md.qty
                FROM wms_movement_details AS md
                INNER JOIN wms_movements AS m
                ON m.id = md.movement_id
                INNER JOIN wms_products AS p
                ON p.id = md.product_id
                INNER JOIN prd_laminates AS l
                ON l.additive_movement_id = m.id
                WHERE l.id = $laminateId
                ORDER BY md.id ASC;";
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['additives'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }
}