<?php
use Phalcon\Mvc\Model;
use Phalcon\Db\Column as Column;

class BaseModel extends Model
{
    function loadSource(string $source)
    {
        $this->setSource($source);

        $this->belongsTo('created_by', 'SysUsers', 'id', ['alias' => 'CreatedBy']);
        $this->belongsTo('modified_by', 'SysUsers', 'id', ['alias' => 'ModifiedBy']);
    }

    function afterFetch() {
        foreach ($this->getModelsMetaData()->getDataTypes($this) as $field => $type) {
            if (is_null($this->$field)) {
                continue;
            }
            switch ($type) {
                case Column::TYPE_BOOLEAN:
                    $this->$field = boolval($this->$field); // ord()
                    break;
                case Column::TYPE_BIGINTEGER:
                case Column::TYPE_INTEGER:
                    $this->$field = intval($this->$field);
                    break;
                case Column::TYPE_DECIMAL:
                case Column::TYPE_FLOAT:
                    $this->$field = floatval($this->$field);
                    break;
                case Column::TYPE_DOUBLE:
                    $this->$field = doubleval($this->$field);
                    break;
            }
        }
    }

    /**
     * Devuelve el mensaje de error del modelo
     *
     * @return mixed
     */
    function getMsgError()
    {
        foreach ($this->getMessages() as $message) {
            $type = $message->getType();
            $field = $message->getField();
            $msg = $message->getMessage();

            if ($type === 'PresenceOf') {
                if ($field === 'curp') {
                    $msg = 'El curp es requerido.';
                }
            }

            if (!empty($msg)) { return $msg; };
        }
        return null;
    }

}