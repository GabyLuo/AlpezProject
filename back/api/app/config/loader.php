<?php

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

$dirs = [
    $config->application->modelsDir,
    $config->application->libDir,
    $config->application->controllersDir,
    $config->application->middlewaresDir,
    $config->application->vendorDir
];

$prefixes = ['sys/','jbs/'];

foreach ($prefixes as $pfx) {
    $dirs[] = $config->application->modelsDir . $pfx;
    $dirs[] = $config->application->controllersDir . $pfx;
}

$loader->registerDirs(
    $dirs
)->register();
