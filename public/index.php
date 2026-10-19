<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// On the server this folder is copied into public_html/xoncaci.az and the
// application lives beside public_html in xonca-laravel; locally it is the
// usual ../ . Whichever exists is the one in use.
$app_path = is_dir(__DIR__.'/../vendor') ? __DIR__.'/..' : __DIR__.'/../../xonca-laravel';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $app_path.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $app_path.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $app_path.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
