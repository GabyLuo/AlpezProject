<?php

use Phalcon\Config\Adapter\Ini as ConfigIni;

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

$cfg = new ConfigIni( BASE_PATH . '/config.ini');

return new \Phalcon\Config([
    'database' => [
        'adapter'    => $cfg->database->adapter,
        'host'       => $cfg->database->host,
        'username'   => $cfg->database->username,
        'password'   => $cfg->database->password,
        'dbname'     => $cfg->database->dbname,
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_PERSISTENT => FALSE,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // FETCH_OBJ
        ]
    ],

    'application' => [
        'modelsDir'      => APP_PATH . '/models/',
        // 'migrationsDir'  => APP_PATH . '/migrations/',
        // 'viewsDir'       => APP_PATH . '/views/',
        'libDir'      => APP_PATH . '/lib/',
        'vendorDir'      => APP_PATH . '/vendor/',
        'controllersDir'      => APP_PATH . '/controllers/',
        'middlewaresDir' => APP_PATH . '/middlewares/',
        'baseUri'        => '/',
    ],
    'jwtkey' => $cfg->jwt->key,
]);
