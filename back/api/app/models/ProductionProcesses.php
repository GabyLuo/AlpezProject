<?php

use Phalcon\Mvc\Model;

class ProductionProcesses extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_processes');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'ProductionMeasurements',
            'process_id',
            [
                'foreignKey' => [
                    'message' => 'Hay mediciones que dependen de este proceso de producción.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLotProcesses',
            'process_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este proceso de producción.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionScraps',
            'process_id',
            [
                'foreignKey' => [
                    'message' => 'Hay scraps que dependen de este proceso de producción.',
                ]
            ]
        );
    }
}
