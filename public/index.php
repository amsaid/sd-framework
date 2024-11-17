<?php

declare(strict_types=1);

use SdFramework\Application;
use SdFramework\Http\Request;

/**
 * Define the application paths
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

/**
 * Register The Auto Loader
 */
require BASE_PATH . '/vendor/autoload.php';

/**
 * Initialize Application
 */
try {
    $app = new Application(BASE_PATH);

    /**
     * Handle The Request
     */
    $request = Request::capture();
    $response = $app->handle($request);

    /**
     * Send The Response
     */
    $response->send();

    /**
     * Perform any final cleanup
     */
    $app->terminate($request, $response);
} catch (Throwable $e) {
    /**
     * Handle fatal errors that occur during bootstrap
     */
    if ($app->isDebug()) {
        throw $e;
    }
    
    http_response_code(500);
    echo '<h1>500 Internal Server Error</h1>';
    echo '<p>The application could not be started.</p>';
}
