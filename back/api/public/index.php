<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Events\Manager;

error_reporting(E_ALL ^ E_DEPRECATED);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include Services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    require BASE_PATH . '/vendor/autoload.php';
    /**
     * Starting the application
     * Assign service locator to the application
     */
    $eventsManager = new Manager();

    $app = new Micro($di);

    $eventsManager->attach('micro', new CORSMiddleware());
    $app->before(new CORSMiddleware());
    
    $eventsManager->attach('micro', new ResponseMiddleware());
    $app->before(new ResponseMiddleware());

    $app->setEventsManager($eventsManager);

    /**
     * Include Application
     */
    include APP_PATH . '/app.php';

    /**
     * Handle the request
     */
    $app->handle($_SERVER['REQUEST_URI']);

} catch (\PDOException $e) {
    $response = new Phalcon\Http\Response();
    $response
        ->setStatusCode(500, 'Internal Server Error')
        ->setJsonContent([
            'result' => false,
            'message' => 'Internal Server Error',
            'errors' => [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                // 'trace' => $e->getTraceAsString() // Para que no muestra datos de la BD
            ]
        ])
        ->send();
} catch (\Exception $e) {
    $response = new Phalcon\Http\Response();
    $response
        ->setStatusCode(500, 'Internal Server Error')
        ->setJsonContent([
            'result' => false,
            'message' => 'Internal Server Error',
            'errors' => Message::exception($e)
        ])
        ->send();
}
