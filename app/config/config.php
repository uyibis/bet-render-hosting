<?php
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config\Config([
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => 'mysql',  // Changed to match docker service name
        'username'    => 'phalcon', // Changed to match docker-compose settings
        'password'    => 'secret',  // Changed to match docker-compose settings
        'dbname'      => 'boardman_app',
        'charset'     => 'utf8',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'servicesDir'    => APP_PATH . '/services/',
        'cacheDir'       => BASE_PATH . '/cache/',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpsace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => '/',
    ]
]);