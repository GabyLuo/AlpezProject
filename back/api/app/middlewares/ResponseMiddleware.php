<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
* ResponseMiddleware
*
* Manipulates the response
*/
class ResponseMiddleware implements MiddlewareInterface
{

    /**
     * After anything happens
     *
     * @param Event $event
     * @param Micro $application
     *
     * @return bool
     */
    public function afterHandleRoute(Event $event, Micro $application) {
        if (!$application->response->isSent()) {
            // $application->response->setContentType('application/json', 'UTF-8');
            $application->response->send();
        }
        return true;
    }

     /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @return bool
     */
    public function call(Micro $application) {
        return true;
    }
}