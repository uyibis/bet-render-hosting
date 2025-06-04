<?php

use Phalcon\Autoload\Loader;

$loader = new Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->setDirectories(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        $config->application->libraryDir,
        $config->application->servicesDir
    ]
);

$loader->register();

/** Manual Library Classes Registration 
$loader->setClasses(
    [
        "PHPMailer" => "library/PHPMailer/PHPMailer.php",
        "Request"   => "app/library/Http/Client/Request.php",
    ]
);
*/