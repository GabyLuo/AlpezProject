<?php

use Phalcon\Mvc\View\Simple as View;
use Phalcon\Mvc\Url as UrlResolver;

use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\Manager as ModelsManager;

use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include __DIR__ . "/../config/config.php";
});

/**
 * Shared loader service
 */
$di->setShared('loader', function () {
    $config = $this->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    return $loader;
});

/**
 * Sets the view component
 */
// $di->setShared('view', function () {
//     $config = $this->getConfig();

//     $view = new View();
//     $view->setViewsDir($config->application->viewsDir);
//     return $view;
// });

/**
 * The URL component is used to generate all kind of urls in the application
 */
// $di->setShared('url', function () {
//     $config = $this->getConfig();

//     $url = new UrlResolver();
//     $url->setBaseUri($config->application->baseUri);
//     return $url;
// });

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    return new $class($dbConfig);
});

/**
 * Control columns
 */
$di->setShared(
    'modelsManager',
    function () {
        date_default_timezone_set('America/Mexico_City');
        $eventsManager = new EventsManager();
        $validUser = Auth::getUserData($this->getConfig());
        $eventsManager->attach(
            'model:beforeValidationOnCreate',
            function (Event $event, $model) use($validUser) {

                if (get_class($model) !== 'SysUsers') {

                    if ($validUser !== null) {
                        $model->created = date('Y-m-d H:i:s');
                        $model->created_by = $validUser->id;
                    }
                }
                return true;
            }
        );

        $eventsManager->attach(
            'model:beforeValidationOnUpdate',
            function (Event $event, $model) use ($validUser) {

                if ($validUser !== null) {
                    $model->updated_by = $validUser->id;
                    $model->updated = date('Y-m-d H:i:s');
                }
                return true;
            }
        );

        // Setting a default EventsManager
        $modelsManager = new ModelsManager();

        $modelsManager->setEventsManager($eventsManager);

        return $modelsManager;
    }
);

/**
 * Shared transactions service
 */
$di->setShared(
    'transactions',
    function () {
        return new TransactionManager();
    }
);