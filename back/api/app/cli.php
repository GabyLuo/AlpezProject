#! /usr/bin/php
<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

// Using the CLI factory default services container
$di = new CliDI();

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();


$prefixes = ['sys/', 'store/', 'adm/','prod/','vnt/','prov/','cjj/','leg/','juz/','rates/','quotes/','/tip','/zona','/quotesd','/reports','/zoneextended','mails', 'payments', 'view', 'dashboard/'];

foreach ($prefixes as $pfx) {
    $dirs[] = __DIR__ . "/models/" . $pfx;
}
$dirs[] =  __DIR__ . '/models';
$dirs[] =  __DIR__ . '/lib';
$dirs[] =  __DIR__ . '/tasks';
$dirs[] =  __DIR__ . '/documents';
$dirs[] =  __DIR__ . "/library/Importer";

$loader->registerDirs(
    $dirs
)->register();



// Load the configuration file (if any)
$configFile = __DIR__ . '/config/config.php';

if (is_readable($configFile)) {
    $config = include $configFile;

    $di->set('config', $config);
}


// Create a console application
$console = new ConsoleApp();

$console->setDI($di);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});

$di->setShared(
    'transactions',
    function () {
        return new TransactionManager();
    }
);

/**
 * Process the console arguments
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    // Do Phalcon related stuff here
    // ..
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (\Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
