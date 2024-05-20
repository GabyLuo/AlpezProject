<?php

use Phalcon\Mvc\Model;

class Message
{
	function __construct() {}

    /**
     * Create default success array message used in front-end
     *
     * @param string $content
     * @return array
     */
    static function success(string $content): array
    {
        return ['title' => 'Operación exitosa', 'content' => $content];
    }

    /**
     * Create default warning array message used in front-end
     *
     * @param string $content
     * @return array
     */
    static function warning(string $content): array
    {
        return ['title' => 'Warning!', 'content' => $content];
    }

    /**
     * Create default error array message used in front-end
     *
     * @param string $content
     * @return array
     */
    static function error(string $content): array
    {
        return ['title' => 'Error!', 'content' => $content];
    }

    /**
     * Create default error array message used in front-end
     *
     * @param string $content
     * @param Model $model
     * @return array
     */
    static function err(Model $model, string $content): array
    {
        return [
            'title' => 'Error!',
            'content' => $model->getErrMsg() ?? $content
        ];

    }

    /**
     * Create default exception array messages
     *
     * @param object $e
     * @return array
     */
    static function exception(object $e): array
    {
        return [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ];
    }
}
