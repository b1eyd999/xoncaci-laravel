<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// On the server this folder is copied into public_html/xoncaci.az and the
// application lives beside public_html in xonca-laravel; locally it is the
// usual ../ . The server path is tried first: public_html has a vendor
// folder of its own (darkframe's), so "../vendor exists" proves nothing.
$app_path = is_file(__DIR__.'/../../xonca-laravel/bootstrap/app.php') ? __DIR__.'/../../xonca-laravel' : __DIR__.'/..';

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
