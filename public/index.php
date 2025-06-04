<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Read services
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

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    //Show and Send Response Content to View
    //$request = new Request();  //Phalcon 3
    //echo $application->handle($request->getURI())->getContent(); //Phalcon 3
    echo $application->handle($_SERVER["REQUEST_URI"])->getContent();

} catch (\Exception $e) {
    // Enhanced error display
    $errorMessage = sprintf(
        "<h1>Application Error</h1>
        <h3>%s</h3>
        <hr/>
        <pre>
        File: %s
        Line: %d
        
        Stack Trace:
        %s
        </pre>",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    
    // Log the error
    error_log(sprintf(
        "[%s] %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    echo $errorMessage;
}
